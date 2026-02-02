<?php
/**
 * 基础控制器类
 * 为所有控制器提供公共方法和属性
 */
class Controller {
    protected $f3;
    protected $web;
    protected $apiUrl;

    public function __construct() {
        $this->f3 = \Base::instance();
        $this->web = \Web::instance();
        $this->apiUrl = 'https://hhzyapi.com/api.php/provide/vod/from/hhm3u8/at/xmlsea';
    }

    /**
     * 路由前处理
     */
    public function beforeRoute() {
        // 例如：权限检查、参数验证
    }

    /**
     * 路由后处理
     */
    public function afterroute() {
        // 模板渲染由各控制器方法处理
    }

    /**
     * 发送HTTP请求
     * @param string $url 请求的URL
     * @return string
     * @throws Exception
     */
    public function fetchAPI($url) {
        $ip_long = array(
            array('607649792', '608174079'),
            array('1038614528', '1039007743'),
            array('1783627776', '1784676351'),
            array('2035023872', '2035154943'),
            array('2078801920', '2079064063'),
            array('-1950089216', '-1948778497'),
            array('-1425539072', '-1425014785'),
            array('-1236271104', '-1235419137'),
            array('-770113536', '-768606209'),
            array('-569376768', '-564133889'),
        );

        $rand_key = mt_rand(0, 9);
        $ips = long2ip(mt_rand($ip_long[$rand_key][0], $ip_long[$rand_key][1]));
        $headers = [
            'X-forwarded-for' => $ips,
            'User-Agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Mozilla/5.0 (Unknown; Linux x86_64) AppleWebKit/537.36',
            'Accept-Encoding' => ''
        ];

        $options = [
            'method' => 'GET',
            'headers' => $headers, // 携带组装好的请求头
            'ssl_verify_peer' => false, // 对应 CURLOPT_SSL_VERIFYPEER FALSE（忽略SSL证书验证）
            'ssl_verify_host' => false, // 对应 CURLOPT_SSL_VERIFYHOST FALSE（忽略主机名与证书匹配）
            'follow_location' => true, // 对应 CURLOPT_FOLLOWLOCATION 1（跟随重定向）
            'encoding' => 'gzip', // 对应 CURLOPT_ENCODING "gzip"（支持gzip编码）
            'timeout' => 30 // 可选：添加默认超时时间（原函数未设置，F3有默认值，可自定义）
        ];
    
        try {
            // 调用F3的\Web::request()发送GET请求（替换原生cURL所有逻辑）
            $response = $this->web->request($url, $options);
            return $response;
        } catch (Exception $e) {
            // 可选：添加异常捕获（原函数无异常处理，F3推荐添加，避免脚本直接报错）
            error_log("F3 curlget请求失败：" . $e->getMessage());
            return false;
        }
    }

    /**
     * 解析XML响应为数组
     * 
     * @param string $xml XML字符串
     * @return array|null 解析后的数组或失败时返回null
     */
    protected function parseXML($xml) {
        try {
            // 移除潜在的有害脚本
            $xml = preg_replace('#<script(.*?)</script>#is', '', $xml);
            $xml = preg_replace('#<\?php(.*?)\?>#is', '', $xml);
            $xml = preg_replace('#<iframe(.*?)</iframe>#is', '', $xml);
            $xml = preg_replace('#on\w+="(.*?)"#is', '', $xml);

            // 重置XML解析错误缓冲区（避免残留之前的错误信息）
            libxml_clear_errors();
            // 加载XML（增加错误抑制，避免PHP8警告）
            $xmlObj = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA | LIBXML_ERR_NONE);
            
            if ($xmlObj === false) {
                // 获取XML解析具体错误信息，方便排障
                $xmlErrors = libxml_get_errors();
                $errorMsg = 'XML解析失败：';
                foreach ($xmlErrors as $error) {
                    $errorMsg .= "行{$error->line}列{$error->column}：{$error->message}；";
                }
                // 记录错误（使用F3 error方法，500状态码）
                $this->f3->error(rtrim($errorMsg, '；'), 500);
                // 清空错误缓冲区
                libxml_clear_errors();
                return null;
            }
            
            // 转换为数组（兼容PHP8 JSON编码）
            $json = json_encode($xmlObj, JSON_PARTIAL_OUTPUT_ON_ERROR);
            // 检测JSON编码是否存在隐性错误
            if (json_last_error() !== JSON_ERROR_NONE) {
                $jsonErrorMsg = "JSON编码存在隐性错误：" . json_last_error_msg();
                // 记录警告（不中断流程，仅提醒，因为已使用PARTIAL_OUTPUT）
                $this->f3->log($jsonErrorMsg, 'WARNING');
            }
            $data = json_decode($json, true);
            
            return $data;
        } catch (\Throwable $e) {
            
            error_log("XML解析失败：" . $e->getMessage());
            return null;
        }
    }
    
    protected function fenleiXml($xmlString) {
        // $xml_response = $this->fetchAPI($xmlString);
        $xml = simplexml_load_string($xmlString);
        $categories = [];
        
        if ($xml && isset($xml->class) && isset($xml->class->ty)) {
            foreach ($xml->class->ty as $ty) {
                $categories[] = [
                    'id' => (string)$ty['id'],
                    'name' => (string)$ty
                ];
            }
        }
        $json = json_encode($categories, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        return $json;
    }

    protected function render($view) {
        $template = $this->f3->get('template.moban');
        $view = basename($view);
        $template = basename($template);
        $this->f3->set('inc', $template . '/' . $view);
        echo Template::instance()->render($template . '/layout.htm');
    }
    
    /**
     * 从XML数据中提取视频信息并标准化
     * @param array $data 从XML解析得到的数据
     * @return array 标准化的视频信息数组
     */
    protected function normalizeVideoData($data) {
        $videos = [];
        
        if (isset($data['list']['video'])) {
            $videoList = $data['list']['video'];
            
            // 如果只有一个视频，将其转换为数组格式
            if (isset($videoList['vod_id'])) {
                $videoList = [$videoList];
            }
            
            foreach ($videoList as $video) {
                $normalized = [
                    'last' => $video['vod_last'] ?? $video['last'] ?? '未知时间',
                    'id' => $video['vod_id'] ?? $video['id'] ?? '0',
                    'tid' => $video['type_id'] ?? $video['tid'] ?? '0',
                    'name' => $video['vod_name'] ?? $video['name'] ?? '未知名称',
                    'type' => $video['type_name'] ?? $video['type'] ?? '未知类型',
                    'pic' => $video['vod_pic'] ?? $video['pic'] ?? '/ui/images/no-pic.jpg',
                    'lang' => $video['vod_lang'] ?? $video['lang'] ?? '未知语言',
                    'area' => $video['vod_area'] ?? $video['area'] ?? '未知地区',
                    'year' => $video['vod_year'] ?? $video['year'] ?? '未知年份',
                    'state' => $video['vod_state'] ?? $video['state'] ?? '0',
                    'note' => $video['vod_remarks'] ?? $video['note'] ?? '暂无备注',
                    'actor' => $video['vod_actor'] ?? $video['actor'] ?? '未知演员',
                    'director' => $video['vod_director'] ?? $video['director'] ?? '未知导演',
                    'des' => $video['vod_content'] ?? $video['vod_blurb'] ?? $video['des'] ?? '暂无简介',
                    'play_url' => $video['vod_play_url'] ?? '',
                ];
                
                // 处理 dl 和 dd 数据
                if (isset($video['vod_dl']) && isset($video['vod_dl']['dd'])) {
                    $normalized['dl'] = ['dd' => $video['vod_dl']['dd']];
                } elseif (isset($video['dl']) && isset($video['dl']['dd'])) {
                    $normalized['dl'] = ['dd' => $video['dl']['dd']];
                } else {
                    $normalized['dl'] = ['dd' => '暂无播放地址'];
                }
                
                $videos[] = $normalized;
            }
        }
        
        return $videos;
    }
    
    /**
     * 加载分类列表用于导航
     * @param object $f3 Fat-Free框架实例
     */
    protected function loadCategories($f3) {
        $apiUrl = $this->apiUrl;
        $xml_response = $this->fetchAPI($apiUrl);
        
        if ($xml_response && isset($xml_response['body'])) {
            $categories = $this->fenleiXml($xml_response['body']);
            $f3->set('categories', json_decode($categories, true));
        } else {
            // 如果API不可用，设置空数组
            $f3->set('categories', []);
        }
    }

}