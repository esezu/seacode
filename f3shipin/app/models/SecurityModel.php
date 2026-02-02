
<?php

/**
 * 安全模型类
 * 提供网站安全防护功能
 */
class SecurityModel
{
    private $f3;
    private $config;
    private $ip;
    private $userAgent;
    
    public function __construct()
    {
        $this->f3 = Base::instance();
        $this->ip = $this->getClientIP();
        $this->userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        
        // 加载安全配置
        if (file_exists('config/security.ini')) {
            $this->config = parse_ini_file('config/security.ini', true);
        } else {
            $this->config = $this->getDefaultConfig();
        }
    }
    
    /**
     * 加载安全配置
     */
    private function getDefaultConfig()
    {
        return [
            'security' => [
                'enable_security' => true,
                'block_bots' => true,
                'max_requests_per_minute' => 100,
                'ban_attempts' => 10
            ],
            'blacklist' => [
                'ip' => [],
                'user_agents' => ['bot', 'crawler', 'spider']
            ]
        ];
    }
    
    /**
     * 获取客户端真实IP
     */
    private function getClientIP()
    {
        $ip = '127.0.0.1';
        
        $headers = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];
        
        foreach ($headers as $header) {
            if (isset($_SERVER[$header])) {
                $ipList = explode(',', $_SERVER[$header]);
                foreach ($ipList as $ipAddress) {
                    $ipAddress = trim($ipAddress);
                    if ($this->isValidIP($ipAddress)) {
                        return $ipAddress;
                    }
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? $ip;
    }
    
    /**
     * 验证IP地址格式
     */
    private function isValidIP($ip)
    {
        return filter_var($ip, FILTER_VALIDATE_IP, 
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false;
    }
    
    /**
     * 主安全检查方法
     */
    public function checkSecurity()
    {
        if (!$this->config['security']['enable_security']) {
            return true;
        }
        
        // 1. 检查IP黑名单
        $this->checkIPBlacklist();
        
        // 2. 检查User-Agent黑名单
        $this->checkUserAgentBlock();
        
        // 3. 速率限制检查
        $this->checkRateLimit();
        
        // 4. 检查恶意请求
        $this->checkMaliciousRequests();
        
        // 5. 记录访问日志
        $this->logAccess();
        
        return true;
    }
    
    /**
     * 检查IP黑名单
     */
    private function checkIPBlacklist()
    {
        if (!isset($this->config['blacklist']['ip'])) {
            return;
        }
        
        foreach ($this->config['blacklist']['ip'] as $bannedIP) {
            if ($this->ip === $bannedIP || 
                $this->isIPInRange($this->ip, $bannedIP)) {
                $this->blockAccess('IP黑名单匹配');
            }
        }
    }
    
    /**
     * 检查IP范围
     */
    private function isIPInRange($ip, $range)
    {
        if (strpos($range, '/') !== false) {
            // CIDR格式
            list($range, $netmask) = explode('/', $range, 2);
            if (strpos($netmask, '.') !== false) {
                $netmask = str_replace($netmask, $this->netmask2cidr($netmask));
            }
            $decIP = ip2long($ip);
            $decRange = ip2long($range);
            $wildcardDec = pow(2, (32 - $netmask)) - 1;
            $netmaskDec = ~$wildcardDec;
            return (($decIP & $netmaskDec) == ($decRange & $netmaskDec));
        } else {
            return $ip === $range;
        }
    }
    
    /***
     * 子网掩码转CIDR
     */
    private function netmask2cidr($netmask)
    {
        $cidr = 0;
        $parts = explode('.', $netmask);
        foreach ($parts as $oct) {
            $cidr += floor(log((256 - $oct), 2));
        }
        return $cidr;
    }
    
    /**
     * 检查User-Agent黑名单
     */
    private function checkUserAgentBlock()
    {
        if (!$this->config['security']['block_bots']) {
            return;
        }
        
        if (!isset($this->config['blacklist']['user_agents'])) {
            return;
        }
        
        foreach ($this->config['blacklist']['user_agents'] as $bot) {
            if (stripos($this->userAgent, $bot) !== false) {
                $this->blockAccess('疑似恶意User-Agent');
            }
        }
    }
    
    /**
     * 速率限制检查
     */
    private function checkRateLimit()
    {
        $limit = $this->config['security']['max_requests_per_minute'] ?? 100;
        $currentTime = time();
        $windowStart = $currentTime - 60; // 1分钟窗口
        
        // 获取当前IP的请求计数
        $cacheKey = 'rate_limit_' . md5($this->ip);
        $requests = (int) $this->f3->get($cacheKey, 0);
        
        if ($requests >= $limit) {
            $this->banIP('请求频率超过限制');
        }
        
        // 更新请求计数（使用缓存模拟）
        $this->f3->set($cacheKey, $requests + 1, 60); // 1分钟过期
    }
    
    /**
     * 检查恶意请求
     */
    private function checkMaliciousRequests()
    {
        $requestData = array_merge($_GET, $_POST, $_REQUEST);
        
        foreach ($requestData as $key => $value) {
            // 检查SQL注入
            if ($this->config['firewall']['enable_sql_injection_check'] && 
                $this->containsSQLInjection($key . $value)) {
                $this->banIP('检测到SQL注入尝试');
            }
            
            // 检查XSS尝试
            if ($this->config['firewall']['enable_xss_check'] && 
                $this->containsXSS($key . $value)) {
                $this->banIP('检测到XSS攻击尝试');
            }
        }
        
        // 检查可疑的文件访问
        $requestUri = $_SERVER['REQUEST_URI'] ?? '';
        if ($this->config['firewall']['enable_file_inclusion_check'] && 
            $this->isSuspiciousFileAccess($requestUri)) {
            $this->banIP('可疑文件访问');
        }
    }
    
    /**
     * 检测SQL注入
     */
    private function containsSQLInjection($input)
    {
        $patterns = [
            '/\b(SELECT|INSERT|UPDATE|DELETE|DROP|UNION|EXEC|ALTER)\b/i',
            '/\bor\b\s+\d+\s*=\s*\d+/i',
            '/\bor\b\s+\'.*\'\s*=\s*\'.*/i',
            '/;/',
            '/--/',
            '/\/\\*/',
            '/\\*\//'
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $input)) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * 检测XSS尝试
     */
    private function containsXSS($input)
    {
        $patterns = [
            '/<script.*?>.*?<\/script>/is',
            '/on\w+\s*=/i',
            '/javascript:/i',
            '/vbscript:/i',
            '/<iframe.*?>/i',
            '/<object.*?>/i',
            '/<embed.*?>/i',
            '/expression\s*\(/i'
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $input)) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * 检测可疑文件访问
     */
    private function isSuspiciousFileAccess($uri)
    {
        $suspicious = [
            '../',
            '..\\',
            'config.php',
            '.env',
            '.htaccess',
            'wp-admin',
            'admin.php',
            'login.php'
        ];
        
        foreach ($suspicious as $pattern) {
            if (strpos($uri, $pattern) !== false) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * 记录访问日志
     */
    private function logAccess()
    {
        if (!$this->config['security']['log_requests']) {
            return;
        }
        
        $logData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'ip' => $this->ip,
            'user_agent' => $this->userAgent,
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'GET',
            'uri' => $_SERVER['REQUEST_URI'] ?? '',
            'query' => $_SERVER['QUERY_STRING'] ?? '',
            'referer' => $_SERVER['HTTP_REFERER'] ?? ''
        ];
        
        $logLine = implode(' | ', $logData) . "\n";
        
        // 写入日志文件
        $logFile = 'tmp/logs/access_' . date('Y-m-d') . '.log';
        file_put_contents($logFile, $logLine, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * 阻止访问并终止执行
     */
    private function blockAccess($reason)
    {
        $this->logSecurityEvent('BLOCK', $reason);
        
        http_response_code(403);
        header('Content-Type: text/plain; charset=utf-8');
        echo "Access Denied - $reason\n";
        echo "Your IP: {$this->ip}\n";
        echo "Timestamp: " . date('Y-m-d H:i:s') . "\n";
        
        if ($this->config['privacy']['hide_server_info']) {
            header_remove('Server');
            header_remove('X-Powered-By');
        }
        
        exit;
    }
    
    /**
     * 禁止IP地址
     */
    private function banIP($reason)
    {
        $this->logSecurityEvent('BAN', $reason);
        
        // 记录被禁IP
        $banFile = 'tmp/logs/banned_ips.log';
        $banEntry = date('Y-m-d H:i:s') . " | {$this->ip} | $reason\n";
        file_put_contents($banFile, $banEntry, FILE_APPEND | LOCK_EX);
        
        // 可选：将IP添加到黑名单配置中（需要文件写入权限）
        $this->addToBlacklist($this->ip);
        
        $this->blockAccess($reason);
    }
    
    /**
     * 添加到黑名单
     */
    private function addToBlacklist($ip)
    {
        $configPath = 'config/security.ini';
        if (!file_exists($configPath)) {
            return;
        }
        
        $configContent = file_get_contents($configPath);
        if (strpos($configContent, $ip) === false) {
            // 在blacklist部分添加IP
            $newEntry = "ip[] = \"$ip\"\n";
            $configContent = str_replace(
                '[blacklist]',
                "[blacklist]\n$newEntry",
                $configContent
            );
            
            // 谨慎写入，先备份
            if (is_writable($configPath)) {
                copy($configPath, $configPath . '.backup');
                file_put_contents($configPath, $configContent);
            }
        }
    }
    
    /**
     * 记录安全事件
     */
    private function logSecurityEvent($type, $reason)
    {
        $logEntry = sprintf(
            "%s | %s | %s | %s | %s\n",
            date('Y-m-d H:i:s'),
            $type,
            $this->ip,
            $reason,
            $this->userAgent
        );
        
        $logFile = 'tmp/logs/security.log';
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * 验证CSRF Token
     */
    public function validateCSRF($token)
    {
        $sessionToken = $this->f3->get('SESSION.csrf_token');
        
        if (empty($token) || empty($sessionToken)) {
            return false;
        }
        
        return hash_equals($sessionToken, $token);
    }
    
    /**
     * 生成CSRF Token
     */
    public function generateCSRFToken()
    {
        return bin2hex(random_bytes(32));
    }
}