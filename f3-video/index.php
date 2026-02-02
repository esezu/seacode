<?php
// 获取Fat-Free Framework实例
require __DIR__ . '/vendor/autoload.php';
$f3 = Base::instance();

// 框架基础配置
$f3->set('DEBUG', 3);                    // 调试级别 (0-3, 生产环境设为0)
$f3->set('AUTOLOAD', 'app/');            // 框架自动加载路径
$f3->set('UI', 'ui/');                   // 模板目录
$f3->set('SESSION_DURATION', 86400);      // 会话持续时间（秒） - 24小时
$f3->set('LOGS', 'tmp/logs/');

// 加载配置
$f3->config('app/config.ini');

// 首页路由
$f3->route('GET /', 'Home->index');
$f3->route('GET /@page', 'Home->index');

// 分类列表
$f3->route('GET /list', 'Home->index');
$f3->route('GET /list/@tid', 'Home->list');
$f3->route('GET /list/@tid/@page', 'Home->list');

// 视频详情
$f3->route('GET /info/@id', 'Home->info');

// 搜索
$f3->route('GET /search|/search/@page', 'Home->search');


// 运行应用
$f3->run();