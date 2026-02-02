<?php
// Routes configuration

$f3 = \Base::instance();

// 首页 - 最新视频列表
$f3->route('GET /', 'App\Controllers\VideoController->index');

// 分类页面
$f3->route('GET /category/@typeid', 'App\Controllers\VideoController->category');

// 视频详情页面
$f3->route('GET /video/@id', 'App\Controllers\VideoController->detail');

// 视频播放页面
$f3->route('GET /play/@id/@episode', 'App\Controllers\VideoController->play');
$f3->route('GET /play/@id', 'App\Controllers\VideoController->play');

// 搜索功能
$f3->route('GET /search', 'App\Controllers\VideoController->search');

// 静态资源路由
$f3->route('GET /css/@file', function($f3, $params) {
    $file = 'public/css/' . $params['file'];
    if (file_exists($file)) {
        header('Content-Type: text/css');
        readfile($file);
    } else {
        $f3->error(404);
    }
});

$f3->route('GET /js/@file', function($f3, $params) {
    $file = 'public/js/' . $params['file'];
    if (file_exists($file)) {
        header('Content-Type: application/javascript');
        readfile($file);
    } else {
        $f3->error(404);
    }
});
