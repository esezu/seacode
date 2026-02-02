<?php

// 加载Fat-Free Framework
require_once __DIR__ . '/lib/fatfree/base.php';

// 创建F3实例
$f3 = Base::instance();

// 配置设置
$f3->set('DEBUG', 3);
$f3->set('AUTOLOAD', __DIR__ . '/classes/');
$f3->set('UI', __DIR__ . '/templates/');
$f3->set('BASEURL', 'http://localhost:8000');
$f3->set('PUBLIC', 'public/');

// API配置
$f3->set('API_URL', 'https://hhzyapi.com/api.php/provide/vod/from/hhm3u8/at/xml');
$f3->set('PLAYER_URL', 'https://hhjiexi.com/play/?url=');

// 全局变量
$f3->set('site_name', '在线影院');
$f3->set('site_description', '最新影视资源在线观看');

// 路由定义

// 首页 - 最新视频列表
$f3->route('GET /', function($f3) {
    // 获取视频列表
    $api = new VideoAPI();
    $data = $api->getVideoList([
        'pg' => $f3->get('GET.pg') ?: 1,
        't' => $f3->get('GET.t') ?: ''
    ]);

    $f3->set('videos', $data['videos']);
    $f3->set('pagination', $data['pagination']);
    $f3->set('page', $f3->get('GET.pg') ?: 1);
    $f3->set('type_id', $f3->get('GET.t'));

    echo View::instance()->render('layout.html');
});

// 分类列表
$f3->route('GET /category', function($f3) {
    $api = new VideoAPI();
    $categories = $api->getCategories();

    $f3->set('categories', $categories);
    $f3->set('title', '全部分类');

    echo View::instance()->render('category.html');
});

// 视频详情
$f3->route('GET /detail/@id', function($f3) {
    $api = new VideoAPI();
    $video = $api->getVideoDetail($f3->get('PARAMS.id'));

    if (!$video) {
        $f3->error(404, '视频不存在');
    }

    $f3->set('video', $video);
    $f3->set('title', $video['name']);

    echo View::instance()->render('detail.html');
});

// 视频播放
$f3->route('GET /play/@id', function($f3) {
    $api = new VideoAPI();
    $video = $api->getVideoDetail($f3->get('PARAMS.id'));

    if (!$video) {
        $f3->error(404, '视频不存在');
    }

    // 获取播放链接
    $playUrl = $f3->get('GET.url');

    if (!$playUrl && isset($video['episodes'][0]['url'])) {
        $playUrl = $video['episodes'][0]['url'];
    }

    $f3->set('video', $video);
    $f3->set('play_url', $playUrl);
    $f3->set('player_url', $f3->get('PLAYER_URL') . $playUrl);
    $f3->set('current_episode', $f3->get('GET.episode') ?: 1);

    echo View::instance()->render('play.html');
});

// 搜索
$f3->route('GET /search', function($f3) {
    $keyword = $f3->get('GET.wd');

    if (empty($keyword)) {
        $f3->reroute('/');
    }

    $api = new VideoAPI();
    $data = $api->search($keyword, $f3->get('GET.pg') ?: 1);

    $f3->set('videos', $data['videos']);
    $f3->set('pagination', $data['pagination']);
    $f3->set('page', $f3->get('GET.pg') ?: 1);
    $f3->set('keyword', $keyword);
    $f3->set('title', '搜索: ' . $keyword);

    echo View::instance()->render('search.html');
});

// 按分类浏览
$f3->route('GET /type/@id', function($f3) {
    $api = new VideoAPI();
    $data = $api->getVideoList([
        'pg' => $f3->get('GET.pg') ?: 1,
        't' => $f3->get('PARAMS.id')
    ]);

    $f3->set('videos', $data['videos']);
    $f3->set('pagination', $data['pagination']);
    $f3->set('page', $f3->get('GET.pg') ?: 1);
    $f3->set('type_id', $f3->get('PARAMS.id'));

    // 获取分类名称
    $categories = $api->getCategories();
    $typeName = isset($categories[$f3->get('PARAMS.id')]) ? $categories[$f3->get('PARAMS.id')] : '未知分类';

    $f3->set('title', $typeName);

    echo View::instance()->render('layout.html');
});

// 静态资源
$f3->route('GET /public/@filename', function($f3) {
    $filename = $f3->get('PARAMS.filename');
    $file = __DIR__ . '/public/' . $filename;

    if (!file_exists($file)) {
        $f3->error(404);
    }

    $mime = Web::instance()->mime($filename);
    header('Content-Type: ' . $mime);
    readfile($file);
});

// 404错误处理
$f3->set('ONERROR', function($f3) {
    $f3->set('error_code', $f3->get('ERROR.code'));
    $f3->set('error_text', $f3->get('ERROR.text'));
    $f3->set('title', '页面未找到');
    echo View::instance()->render('error.html');
});

// 运行应用
$f3->run();
