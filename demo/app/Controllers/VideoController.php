<?php

namespace App\Controllers;

use App\Services\VideoApiService;

class VideoController
{
    private $apiService;

    public function __construct()
    {
        $f3 = \Base::instance();
        $this->apiService = new VideoApiService(
            $f3->get('API_URL'),
            $f3->get('PLAYER_URL')
        );
    }

    /**
     * 首页 - 显示最新视频列表
     */
    public function index($f3)
    {
        $page = $f3->get('GET.pg') ?? 1;
        $result = $this->apiService->getVideoListWithCategories($page);

        if (!$result['success']) {
            $f3->set('error', $result['message']);
        }

        $f3->set('videos', $result['data']);
        $f3->set('categories', $result['categories']);
        $f3->set('pagination', $result['pagination']);
        $f3->set('title', '最新视频');

        echo \Template::instance()->render('home.html');
    }

    /**
     * 分类页面
     */
    public function category($f3)
    {
        $typeId = $f3->get('PARAMS.typeid');
        $page = $f3->get('GET.pg') ?? 1;

        // 获取分类名称
        $listResult = $this->apiService->getVideoListWithCategories(1, 1);
        $categoryName = '视频列表';
        foreach ($listResult['categories'] as $category) {
            if ($category['id'] == $typeId) {
                $categoryName = $category['name'];
                break;
            }
        }

        $result = $this->apiService->getVideoList($page, 20, $typeId);

        if (!$result['success']) {
            $f3->set('error', $result['message']);
        }

        $f3->set('videos', $result['data']);
        $f3->set('categories', $listResult['categories']);
        $f3->set('pagination', $result['pagination']);
        $f3->set('currentCategory', $typeId);
        $f3->set('title', $categoryName);

        echo \Template::instance()->render('home.html');
    }

    /**
     * 视频详情页面
     */
    public function detail($f3)
    {
        $id = $f3->get('PARAMS.id');
        $result = $this->apiService->getVideoDetail($id);

        if (!$result['success'] || empty($result['data'])) {
            $f3->set('error', 'Video not found');
            $f3->set('title', '视频未找到');
            echo \Template::instance()->render('error.html');
            return;
        }

        $video = $result['data'][0];

        // 获取播放器URL
        if (!empty($video['episodes'])) {
            $firstEpisode = $video['episodes'][0];
            $video['playerUrl'] = $this->apiService->getPlayerUrl($firstEpisode['url']);
        }

        // 获取分类列表用于导航
        $listResult = $this->apiService->getVideoListWithCategories(1, 1);

        $f3->set('video', $video);
        $f3->set('categories', $listResult['categories']);
        $f3->set('title', $video['name']);

        echo \Template::instance()->render('detail.html');
    }

    /**
     * 视频播放页面
     */
    public function play($f3)
    {
        $id = $f3->get('PARAMS.id');
        $episodeIndex = $f3->get('PARAMS.episode') ?? 0;

        $result = $this->apiService->getVideoDetail($id);

        if (!$result['success'] || empty($result['data'])) {
            $f3->set('error', 'Video not found');
            $f3->set('title', '视频未找到');
            echo \Template::instance()->render('error.html');
            return;
        }

        $video = $result['data'][0];

        if (empty($video['episodes'])) {
            $f3->set('error', 'No episodes available');
            $f3->set('title', '无播放资源');
            echo \Template::instance()->render('error.html');
            return;
        }

        $episodeIndex = (int)$episodeIndex;
        if ($episodeIndex >= count($video['episodes'])) {
            $episodeIndex = 0;
        }

        $currentEpisode = $video['episodes'][$episodeIndex];
        $playerUrl = $this->apiService->getPlayerUrl($currentEpisode['url']);

        // 获取分类列表
        $listResult = $this->apiService->getVideoListWithCategories(1, 1);

        $f3->set('video', $video);
        $f3->set('currentEpisode', $currentEpisode);
        $f3->set('episodeIndex', $episodeIndex);
        $f3->set('playerUrl', $playerUrl);
        $f3->set('categories', $listResult['categories']);
        $f3->set('title', $video['name'] . ' - ' . $currentEpisode['name']);

        echo \Template::instance()->render('player.html');
    }

    /**
     * 搜索功能
     */
    public function search($f3)
    {
        $keyword = $f3->get('GET.wd');
        $page = $f3->get('GET.pg') ?? 1;

        if (empty($keyword)) {
            $f3->reroute('/');
            return;
        }

        $result = $this->apiService->searchVideos($keyword, $page);

        // 获取分类列表
        $listResult = $this->apiService->getVideoListWithCategories(1, 1);

        if (!$result['success']) {
            $f3->set('error', $result['message']);
        }

        $f3->set('videos', $result['data']);
        $f3->set('categories', $listResult['categories']);
        $f3->set('pagination', $result['pagination']);
        $f3->set('searchKeyword', $keyword);
        $f3->set('title', '搜索: ' . $keyword);

        echo \Template::instance()->render('search.html');
    }
}
