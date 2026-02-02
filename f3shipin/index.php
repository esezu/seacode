<?php
// 引入 Fat-Free Framework 核心文件
require __DIR__ . '/../fatfree-core-master/base.php';

$f3 = Base::instance();

// 加载配置文件
$f3->config('config/config.ini');

// 设置时区
date_default_timezone_set('Asia/Shanghai');

// 设置BASE URL，适配不同的部署环境
define('BASE_URL', $f3->get('SCHEME') . '://' . $f3->get('HOST') . $f3->get('BASE'));

// 加载路由配置
if (file_exists('config/routes.ini')) {
    $f3->config('config/routes.ini');
}

// 加载安全配置
if (file_exists('config/security.ini')) {
    $f3->config('config/security.ini');
}

// 初始化安全模块
$security = new SecurityModel();
if ($f3->get('security.enable_security', TRUE)) {
    $security->checkSecurity();
}

// 设置模板引擎
$template = Template::instance();
$template->filter('format_actor', function($str) {
    return str_replace(',', ' / ', $str);
});

$template->filter('truncate', function($str, $length = 100) {
    if (mb_strlen($str, 'UTF-8') > $length) {
        return mb_substr($str, 0, $length, 'UTF-8') . '...';
    }
    return $str;
});

// 新增安全过滤器
$template->filter('escape', function($str) {
    return htmlspecialchars($str, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
});

$template->filter('highlight', function($str, $keyword) {
    if (empty($keyword)) return $str;
    $escapedKeyword = preg_quote($keyword, '/');
    return preg_replace('/(' . $escapedKeyword . ')/iu', '<mark>$1</mark>', $str);
});

$template->filter('nl2br', function($str) {
    return nl2br(htmlspecialchars($str), false);
});

// 生成CSRF Token
$csrfToken = bin2hex(random_bytes(32));
$f3->set('CSRF_TOKEN', $csrfToken);
$f3->set('SESSION.csrf_token', $csrfToken);

// 错误处理
$f3->set('ONERROR', function($f3) {
    if ($f3->get('ERROR.code') == 404) {
        $controller = new VideoController();
        $controller->notFound();
    } else {
        // 生产环境下记录错误日志
        if ($f3->get('DEBUG') < 3) {
            error_log(sprintf(
                "Error %s: %s in %s on line %d\n",
                $f3->get('ERROR.code'),
                $f3->get('ERROR.text'),
                $f3->get('ERROR.file'),
                $f3->get('ERROR.line')
            ));
        }
        
        $controller = new VideoController();
        $controller->showError($f3->get('ERROR.text'), $f3->get('ERROR.code'));
    }
});

// 运行应用
try {
    $f3->run();
} catch (Exception $e) {
    error_log('Fatal error: ' . $e->getMessage());
    header('HTTP/1.1 500 Internal Server Error');
    echo '系统错误，请稍后重试。';
}