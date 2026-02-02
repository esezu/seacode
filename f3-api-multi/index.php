<?php
/**
 * 基于Fat-Free Framework的多API切换实现
 * 单文件index.php，不缓存数据
 */

// 1. 引入F3框架
require __DIR__ . '/vendor/autoload.php';

// 2. 初始化F3实例
$f3 = \Base::instance();

// 3. 框架基础配置
$f3->set('DEBUG', 3);
$f3->set('UI', 'ui/');
$f3->set('CACHE', false);
$f3->set('TEMP', 'tmp/');
$f3->set('ESCAPE', false);

// 4. 全局配置
$f3->set('SITE_NAME', '多API切换示例');
$f3->set('SITE_DOMAIN', 'localhost');

// 5. API配置
$f3->set('API_URL_1', 'API 1#https://api.example.com/v1');
$f3->set('API_URL_2', 'API 2#https://api.example.com/v2');
$f3->set('API_URL_3', 'API 3#https://api.example.com/v3');

// 6. 核心工具方法

/**
 * API请求函数
 * 使用F3的Web类发送请求
 */
function fetchAPI($url, $params = []) {
    $f3 = \Base::instance();
    
    try {
        $web = \Web::instance();
        $options = [
            'method' => 'GET',
            'header' => [
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
            ],
            'timeout' => 30
        ];
        
        // 添加参数
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        
        $response = $web->request($url, $options);
        return $response['body'];
    } catch (Exception $e) {
        // 记录错误
        error_log("API请求失败: " . $e->getMessage() . " - URL: " . $url);
        return false;
    }
}

/**
 * 处理API数据
 */
function handleApiData($apiUrl, $endpoint, $params = []) {
    $fullUrl = $apiUrl . '/' . $endpoint;
    $response = fetchAPI($fullUrl, $params);
    
    if ($response) {
        // 尝试解析JSON响应
        $data = json_decode($response, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $data;
        }
        // 如果不是JSON，返回原始响应
        return $response;
    }
    
    return false;
}

/**
 * 构建页面URL
 */
function buildUrl($params = []) {
    $f3 = \Base::instance();
    return $f3->get('BASE') . '?' . http_build_query($params);
}

// 7. 定义F3路由
$f3->route('GET /', function($f3) {
    // 获取请求参数
    $apiSelect = $_GET['api'] ?? $_COOKIE['api_select'] ?? '1';
    $templateSelect = $_GET['template'] ?? $_COOKIE['template_select'] ?? 'default';
    $endpoint = $_GET['endpoint'] ?? 'home';
    $params = $_GET;
    unset($params['api'], $params['template'], $params['endpoint']);
    
    // 保存选择到Cookie
    setcookie('api_select', $apiSelect, time() + 86400 * 30, '/');
    setcookie('template_select', $templateSelect, time() + 86400 * 30, '/');
    
    // 获取当前API配置
    $apiConfig = $f3->get('API_URL_' . $apiSelect);
    if ($apiConfig) {
        list($apiName, $apiUrl) = explode('#', $apiConfig);
    } else {
        // 默认使用第一个API
        $defaultApiConfig = $f3->get('API_URL_1');
        list($apiName, $apiUrl) = explode('#', $defaultApiConfig);
        $apiSelect = '1';
    }
    
    // 获取所有API配置
    $apiList = [];
    $i = 1;
    while (true) {
        $config = $f3->get('API_URL_' . $i);
        if (!$config) {
            break;
        }
        list($name, $url) = explode('#', $config);
        $apiList[] = ['id' => $i, 'name' => $name, 'url' => $url];
        $i++;
    }
    
    // 生成API选择选项的HTML代码
    $apiOptions = '';
    foreach ($apiList as $api) {
        $selected = $api['id'] == $apiSelect ? 'selected' : '';
        $apiOptions .= "<option value='{$api['id']}' {$selected}>{$api['name']}</option>";
    }
    
    // 处理API数据
    $apiData = handleApiData($apiUrl, $endpoint, $params);
    
    // 格式化API数据为可读形式
    if ($apiData) {
        if (is_array($apiData)) {
            $apiDataPretty = json_encode($apiData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        } else {
            $apiDataPretty = $apiData;
        }
    } else {
        $apiDataPretty = '无数据或API请求失败';
    }
    
    // 设置基础URL
    $baseUrl = $f3->get('SCHEME') . '://' . $f3->get('HOST') . ':' . $f3->get('PORT');
    
    // 使用F3的set方法设置模板变量
    $f3->set('SITE_NAME', $f3->get('SITE_NAME'));
    $f3->set('CURRENT_API_NAME', $apiName);
    $f3->set('CURRENT_API_URL', $apiUrl);
    $f3->set('CURRENT_API', $apiSelect);
    $f3->set('CURRENT_TEMPLATE', $templateSelect);
    $f3->set('ENDPOINT', $endpoint);
    $f3->set('API_OPTIONS', $apiOptions);
    $f3->set('API_DATA', $apiData);
    $f3->set('API_DATA_PRETTY', $apiDataPretty);
    $f3->set('BASE', $baseUrl);
    
    // 使用F3模板系统渲染模板
    echo \Template::instance()->render($templateSelect . '/index.html');
});

// 8. 运行F3应用
$f3->run();