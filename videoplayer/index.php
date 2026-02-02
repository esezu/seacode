<?php
// 基于 Fat-Free Framework 的影视数据接口入口，同时提供简单回退路由。
// 目标：在具备 F3 的环境下使用路由能力；若无 F3，则提供最小化的纯 PHP 路由，便于本地调试。

// 引入自动加载器，若未安装依赖则走回退路径
// 说明：你已手动下载 Fat-Free Framework Core（fatfree-core-master），这里尝试直接加载本地核心库，确保 Base 类可用
require __DIR__ . '/vendor/autoload.php';
if (!class_exists('\\Base')) {
  $ffPaths = [
    __DIR__ . '/fatfree-core-master/base.php',
    __DIR__ . '/fatfree-core-master/Base.php',
  ];
  foreach ($ffPaths as $pf) {
    if (file_exists($pf)) {
      require $pf;
      if (class_exists('\\Base')) break;
    }
  }
}

use App\VideoController;

// 尝试获取 Fat-Free 路由器实例
$f3 = null;
if (class_exists('\Base')) {
  $f3 = \Base::instance();
}

// 如果 Fat-Free 可用，使用其路由；否则走简易路由
if ($f3) {
  // Fat-Free 路由：获取视频列表（带筛选参数）
  $f3->route('GET /api/vod', function(){
    $ac = $_GET['ac'] ?? '';
    $ids = $_GET['ids'] ?? '';
    $t = $_GET['t'] ?? '';
    $h = $_GET['h'] ?? '';
    $pg = $_GET['pg'] ?? '';
    $wd = $_GET['wd'] ?? '';

    $vc = new VideoController();
    if ($ac === '' || $ac === 'videolist') {
      $out = $vc->listVideos(['ac'=>$ac, 'ids'=>$ids, 't'=>$t, 'h'=>$h, 'pg'=>$pg, 'wd'=>$wd]);
    } else {
      // 详情模式，按 ids 获取详细信息
      $out = $vc->getVideoDetail($ids);
    }
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($out);
  });

  // 路由：获取分类信息
  $f3->route('GET /api/vod/categories', function(){
    $vc = new VideoController();
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($vc->listCategories());
  });

  // 路由：获取指定影片的剧集及播放地址
  $f3->route('GET /api/vod/episodes', function(){
    $id = $_GET['id'] ?? '';
    $vc = new VideoController();
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($vc->getEpisodes($id));
  });

  $f3->run();
} else {
  // 简易路由，便于无 F3 环境下的测试
  $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
  $vc = new VideoController();
  if ($path === '/api/vod' || $path === '/api/vod/') {
    $ac = $_GET['ac'] ?? '';
    $ids = $_GET['ids'] ?? '';
    if ($ac === '' || $ac === 'videolist') {
      $out = $vc->listVideos(['ac'=>$ac, 'ids'=>$ids]);
    } else {
      $out = $vc->getVideoDetail($ids);
    }
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($out);
  } elseif ($path === '/api/vod/categories') {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($vc->listCategories());
  } elseif ($path === '/api/vod/episodes') {
    $id = $_GET['id'] ?? '';
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($vc->getEpisodes($id));
  } else {
    header('HTTP/1.0 404 Not Found');
    echo json_encode(['error'=>'Not Found']);
  }
}
?>
