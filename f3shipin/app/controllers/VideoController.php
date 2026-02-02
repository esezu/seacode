<?php

class VideoController extends BaseController
{
    /**
     * 首页 - 最新视频列表
     */
    public function index()
    {
        $params = $this->getPaginationParams();
        $videos = $this->videoModel->getLatestVideos();
        
        if (isset($videos['error'])) {
            $this->showError($videos['error']);
            return;
        }

        $categories = $this->videoModel->getCategories();
        
        $this->render('index.htm', [
            'videos' => $videos,
            'categories' => $categories,
            'current_page' => $params['page'],
            'title' => '最新影视'
        ]);
    }

    /**
     * 视频列表页
     */
    public function list()
    {
        $params = $this->getPaginationParams();
        $categoryId = $this->f3->get('PARAMS.category', 0);
        
        $searchParams = ['pg' => $params['page']];
        if ($categoryId > 0) {
            $searchParams['t'] = $categoryId;
        }

        $videos = $this->videoModel->getVideoList($searchParams);
        $categories = $this->videoModel->getCategories();
        
        if (isset($videos['error'])) {
            $this->showError($videos['error']);
            return;
        }

        $currentCategory = '';
        if ($categoryId > 0 && isset($categories['error']) === false) {
            foreach ($categories as $cat) {
                if ($cat['id'] == $categoryId) {
                    $currentCategory = $cat['name'];
                    break;
                }
            }
        }

        $this->render('video_list.htm', [
            'videos' => $videos,
            'categories' => $categories,
            'current_category' => $currentCategory,
            'current_category_id' => $categoryId,
            'current_page' => $params['page'],
            'title' => $currentCategory ?: '视频列表'
        ]);
    }

    /**
     * 视频详情页
     */
    public function detail()
    {
        $id = (int)$this->f3->get('PARAMS.id', 0);
        
        if ($id <= 0) {
            $this->showError('视频ID无效');
            return;
        }

        $video = $this->videoModel->getVideoDetail($id);
        
        if (isset($video['error'])) {
            $this->showError($video['error']);
            return;
        }

        $categories = $this->videoModel->getCategories();

        $this->render('video_detail.htm', [
            'video' => $video,
            'categories' => $categories,
            'title' => $video['name']
        ]);
    }

    /**
     * 搜索功能
     */
    public function search()
    {
        $keyword = trim($this->f3->get('GET.q', ''));
        $params = $this->getPaginationParams();
        
        if (empty($keyword)) {
            $this->f3->reroute('/search?msg=' . urlencode('请输入搜索关键词'));
            return;
        }

        $videos = $this->videoModel->searchVideos($keyword, $params['page']);
        $categories = $this->videoModel->getCategories();
        
        if (isset($videos['error'])) {
            $this->showError($videos['error']);
            return;
        }

        $this->render('search.htm', [
            'videos' => $videos,
            'categories' => $categories,
            'keyword' => $keyword,
            'current_page' => $params['page'],
            'title' => '搜索: ' . $keyword
        ]);
    }

    /**
     * 分类页面
     */
    public function category()
    {
        $categoryId = (int)$this->f3->get('PARAMS.id', 0);
        
        if ($categoryId <= 0) {
            $this->showError('分类ID无效');
            return;
        }

        $params = $this->getPaginationParams();
        $videos = $this->videoModel->getVideosByCategory($categoryId, $params['page']);
        $categories = $this->videoModel->getCategories();
        
        if (isset($videos['error'])) {
            $this->showError($videos['error']);
            return;
        }

        $categoryName = '';
        if (isset($categories['error']) === false) {
            foreach ($categories as $cat) {
                if ($cat['id'] == $categoryId) {
                    $categoryName = $cat['name'];
                    break;
                }
            }
        }

        $this->render('category.htm', [
            'videos' => $videos,
            'categories' => $categories,
            'category_id' => $categoryId,
            'category_name' => $categoryName,
            'current_page' => $params['page'],
            'title' => $categoryName
        ]);
    }

    /**
     * 分类API
     */
    public function categories()
    {
        $categories = $this->videoModel->getCategories();
        $this->jsonResponse($categories);
    }

    /**
     * API接口 - 视频列表
     */
    public function apiList()
    {
        $params = [
            'pg' => (int)$this->f3->get('GET.page', 1),
            't' => $this->f3->get('GET.category', ''),
            'wd' => $this->f3->get('GET.keyword', ''),
            'h' => $this->f3->get('GET.hours', '')
        ];

        $videos = $this->videoModel->getVideoList($params);
        $this->jsonResponse($videos);
    }

    /**
     * API接口 - 视频详情
     */
    public function apiDetail()
    {
        $id = (int)$this->f3->get('GET.id', 0);
        $video = $this->videoModel->getVideoDetail($id);
        $this->jsonResponse($video);
    }

    /**
     * 404页面处理
     */
    public function notFound()
    {
        $this->render('error.htm', [
            'message' => '页面不存在',
            'code' => 404,
            'title' => '404 - 页面不存在'
        ]);
    }
}