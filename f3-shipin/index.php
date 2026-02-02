<?php
/**
 * Fat-Free Framework 影视视频程序
 * 入口文件
 */

// 引入 Fat-Free Framework 核心文件
require __DIR__ . '/../fatfree-core-master/base.php';

$f3 = Base::instance();

// 框架基础配置
$f3->set('DEBUG', 3);                    // 调试级别 (0-3, 生产环境设为0)
$f3->set('AUTOLOAD', __DIR__ . '/app/'); // 框架自动加载路径 - 使用绝对路径
$f3->set('UI', __DIR__ . '/views/');      // 模板目录 - 使用绝对路径
$f3->set('CACHE', false);                 // 开发阶段关闭缓存
$f3->set('SESSION_DURATION', 86400);     // 会话持续时间（秒） - 24小时
$f3->set('LOGS', __DIR__ . '/tmp/logs/');

// 配置 API 地址
$f3->set('API_BASE', 'https://hhzyapi.com/api.php/provide/vod/from/hhm3u8/at/xmlsea');
$f3->set('PLAY_BASE', 'https://hhjiexi.com/play/?url=');

// 加载配置文件
$configFile = __DIR__ . '/app/config.ini';
if (file_exists($configFile)) {
    $f3->config($configFile);
}

// 主页 - 显示最新视频
$f3->route('GET /', 'HomeController->index');
$f3->route('GET /@page', 'HomeController->index');

// 视频列表页 - 支持分页和分类
$f3->route('GET /list/@type_id', 'HomeController->list');        // 分类视频
$f3->route('GET /list/@type_id/@page', 'HomeController->list');  // 分类视频的分页

// 搜索
$f3->route('GET /search', 'HomeController->search');
$f3->route('GET /search/@page', 'HomeController->search');

// 视频详情页
$f3->route('GET /info/@id', 'HomeController->info');

// 视频播放页
$f3->route('GET /play/@id/@nid', 'HomeController->play');

// 运行应用
$f3->run();
