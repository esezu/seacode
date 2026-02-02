<?php
/**
 * 基础控制器类
 * 为所有控制器提供公共方法和属性
 */
class Controller
{
    protected $f3;
    protected $web;
    protected $apiUrl;

    public function __construct()
    {
        $this->f3 = \Base::instance();
        $this->web = \Web::instance();
        $this->apiUrl = 'https://hhzyapi.com/api.php/provide/vod/from/hhm3u8/at/xmlsea?ac=videolist';
    }

    /**
     * 路由前处理
     */
    public function beforeRoute()
    {
    }

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
            error_log("F3 curlgets请求失败：" . $e->getMessage());
            return false;
        }
    }


    /**
     * 发送HTTP请求
     * @param string $url 请求的URL
     * @return string
     * @throws Exception
     */
    protected function fetch($url) {
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
        
        $head[] = 'X-forwarded-for:' . $ips;
        $head[] = 'User-Agent:' . ($_SERVER['HTTP_USER_AGENT'] ?? 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
        $head[] = 'Accept-Encoding:';
        $head[] = 'Connection: close';
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $head);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
        curl_setopt($ch, CURLOPT_SSL_CIPHER_LIST, 'DEFAULT@SECLEVEL=1');
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
        curl_setopt($ch, CURLOPT_TIMEOUT, 45);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_ENCODING, "gzip");
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
        
        $response = curl_exec($ch);
        
        if(curl_errno($ch)){
            $error = 'CURL请求错误：' . curl_error($ch);
            if (version_compare(PHP_VERSION, '8.5.0', '<')) {
                curl_close($ch);
            }
            throw new Exception($error);
        }
        if (version_compare(PHP_VERSION, '8.5.0', '<')) {
            curl_close($ch);
        }
        if (empty($response)) {
            throw new Exception('API 返回空数据');
        }
        
        return $response;
    }

    /**
     * 封装curl请求（增加错误处理和超时配置）
     * @param string $url 请求地址
     * @return string|false 成功返回响应内容，失败返回false
     */
    protected function getHtml($url)
    {
        $ch = curl_init();
        // 基础配置
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_REFERER, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // 超时配置（避免卡死）
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        // 模拟浏览器请求（避免接口拒绝）
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
        // 忽略SSL证书错误（如果接口是HTTPS且证书有问题）
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $result = curl_exec($ch);

        // 检查curl请求是否失败
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            $this->f3->log('CURL请求失败：' . $error, 'ERROR');
            curl_close($ch);
            return false;
        }

        curl_close($ch);
        return $result;
    }

    /**
     * 解析XML字符串为数组（处理嵌套XML结构）
     * @param string $xml XML字符串
     * @return array|false 成功返回数组，失败返回false
     */
    protected function parseXml($xml)
    {
        // 抑制XML解析错误，避免页面报错
        libxml_use_internal_errors(true);
        
        // 解析XML
        $simpleXml = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        
        // 检查解析是否失败
        if ($simpleXml === false) {
            $errors = libxml_get_errors();
            $errorMsg = '';
            foreach ($errors as $error) {
                $errorMsg .= "XML解析错误：{$error->message} (行{$error->line})；";
            }
            libxml_clear_errors();
            $this->f3->log($errorMsg, 'ERROR');
            return false;
        }

        // 递归将SimpleXMLElement转为数组（处理嵌套结构）
        $array = $this->simpleXmlToArray($simpleXml);
        return $array;
    }

    /**
     * 递归转换SimpleXMLElement对象为数组
     * @param SimpleXMLElement $simpleXml
     * @return array
     */
    private function simpleXmlToArray($simpleXml)
    {
        $array = (array)$simpleXml;
        foreach ($array as $key => $value) {
            if ($value instanceof SimpleXMLElement) {
                $array[$key] = $this->simpleXmlToArray($value);
            }
        }
        return $array;
    }

    protected function parseXmls($xmlString) {
        if(empty($xmlString)){
            return [];
        }
        $xmlString = preg_replace([
        '#<script(.*?)</script>#i',
        '#<span([^>]*?)>#i',
        '#</span>#i',
        '#<p ([^>]*?)>#i',
        '#<p>#i',
        '#</p>#i',
    ], '', $xmlString);

        $json = json_encode(
        simplexml_load_string($xmlString, 'SimpleXMLElement', LIBXML_NOCDATA),
        JSON_UNESCAPED_UNICODE
    );

        $json = preg_replace('#,\{"@attributes":\{"flag":"([^"]*?)"\}\}#', '', $json);
        $json = preg_replace('#\{"@attributes":\{"flag":"([^"]*?)"\}\},#', '', $json);
        $json = str_replace('@attributes', 'attributes', $json);
        $json = str_replace('" "', '""', $json);
        $json = preg_replace('#,"([^"]*?)":\{"0":""\}#', '', $json);
        $json = preg_replace('#,"([^"]*?)":\{\}#', '', $json);

        return json_decode($json, true);
    }

    protected function fenleiXml($xmlString) {
        $xml_response = $this->fetch($xmlString);
        $xml = simplexml_load_string($xml_response);
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

}