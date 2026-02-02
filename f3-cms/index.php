<?php
require __DIR__ . '/../fatfree-core-master/base.php';
$f3 = Base::instance();

// 框架基础配置
$f3->set('DEBUG', 3);                    // 调试级别 (0-3, 生产环境设为0)
$f3->set('AUTOLOAD', 'app/');            // 框架自动加载路径
$f3->set('UI', 'ui/');                   // 模板目录
$f3->set('CACHE', true);                  // 启用缓存
$f3->set('SESSION_DURATION', 86400);      // 会话持续时间（秒） - 24小时
$f3->set('LOGS', 'tmp/logs/');

// 加载配置
$f3->config('app/config.ini');

// 主页
$f3->route('GET /', 'Home->index');

// 视频列表页 - 支持分页和分类
$f3->route('GET /list', 'Home->list');
$f3->route('GET /list/@page', 'Home->list');
$f3->route('GET /category/@type_id', 'Home->category');
$f3->route('GET /category/@type_id/@page', 'Home->category');

// 搜索
$f3->route('GET /search', 'Home->search');

// 视频详情页
$f3->route('GET /info/@id', 'Home->info');

// 视频播放页
$f3->route('GET /play/@id/@nid', 'Home->play');

// 分类列表
$f3->route('GET /typelist', 'Home->typelist');

// 测试路由
$f3->route('GET /test', 'Home->test');

$f3->run();