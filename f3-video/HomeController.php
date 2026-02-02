<?php
/**
 * 主控制器
 */

class HomeController
{
    private $apiBase;
    private $playBase;

    public function __construct()
    {
        $f3 = Base::instance();
        $this->apiBase = $f3->get('API_BASE');
        $this->playBase = $f3->get('PLAY_BASE');
    }

    /**
     * 主页 - 显示最新视频
     */
    public function index($f3)
    {
        $f3->set('GET.page', $f3->get('GET.page', 1));
        $f3->set('GET.ac', 'videolist');

        $this->loadVideoList($f3);

        $f3->set('title', '最新视频');
        $f3->set('template', 'list.html');
        echo Template::instance()->render('layout.html');
    }

    /**
     * 视频列表页
     */
    public function list($f3)
    {
        $page = $f3->get('PARAMS.page', $f3->get('GET.page', 1));
        $f3->set('GET.page', $page);
        $f3->set('GET.ac', 'videolist');

        $this->loadVideoList($f3);

        $f3->set('title', '视频列表');
        $f3->set('template', 'list.html');
        echo Template::instance()->render('layout.html');
    }

    /**
     * 分类视频列表
     */
    public function category($f3)
    {
        $typeId = $f3->get('PARAMS.type_id', $f3->get('GET.type_id', 0));
        $page = $f3->get('PARAMS.page', $f3->get('GET.page', 1));

        $f3->set('GET.ac', 'videolist');
        $f3->set('GET.t', $typeId);
        $f3->set('GET.page', $page);

        $this->loadVideoList($f3);

        // 获取分类名称
        $categories = $this->getCategories($f3);
        $typeName = isset($categories[$typeId]) ? $categories[$typeId] : '分类视频';

        $f3->set('title', $typeName);
        $f3->set('template', 'list.html');
        echo Template::instance()->render('layout.html');
    }

    /**
     * 搜索
     */
    public function search($f3)
    {
        $keyword = $f3->get('GET.wd', '');

        if (empty($keyword)) {
            $f3->reroute('/');
        }

        $f3->set('GET.ac', 'videolist');
        $f3->set('GET.wd', $keyword);
        $f3->set('GET.page', $f3->get('GET.page', 1));

        $this->loadVideoList($f3);

        $f3->set('title', '搜索: ' . $keyword);
        $f3->set('template', 'list.html');
        echo Template::instance()->render('layout.html');
    }

    /**
     * 视频详情页
     */
    public function info($f3)
    {
        $id = $f3->get('PARAMS.id', 0);

        $f3->set('GET.ac', 'videolist');
        $f3->set('GET.ids', $id);

        $apiUrl = $this->buildApiUrl($f3);
        $xmlData = $this->fetchApi($apiUrl);

        if ($xmlData === false) {
            $f3->error(404, '视频信息获取失败');
            return;
        }

        $video = $this->parseVideoDetail($xmlData);
        if (empty($video)) {
            $f3->error(404, '视频不存在');
            return;
        }

        // 解析播放列表
        $playList = $this->parsePlayListItems($video['dl']);
        $video['playList'] = $playList;

        $f3->set('video', $video);
        $f3->set('title', $video['name']);
        $f3->set('template', 'info.html');
        echo Template::instance()->render('layout.html');
    }

    /**
     * 视频播放页
     */
    public function play($f3)
    {
        $id = $f3->get('PARAMS.id', 0);
        $nid = $f3->get('PARAMS.nid', 0);

        $f3->set('GET.ac', 'videolist');
        $f3->set('GET.ids', $id);

        $apiUrl = $this->buildApiUrl($f3);
        $xmlData = $this->fetchApi($apiUrl);

        if ($xmlData === false) {
            $f3->error(404, '视频信息获取失败');
            return;
        }

        $video = $this->parseVideoDetail($xmlData);
        if (empty($video)) {
            $f3->error(404, '视频不存在');
            return;
        }

        // 解析播放列表
        $playListItems = $this->parsePlayListItems($video['dl']);

        // 获取当前播放项
        $playInfo = [];
        if (isset($playListItems[$nid])) {
            $playInfo = $playListItems[$nid];
            $playInfo['index'] = $nid;
        } elseif (!empty($playListItems)) {
            $playInfo = $playListItems[0];
            $playInfo['index'] = 0;
        }

        if (empty($playInfo)) {
            $f3->error(404, '播放链接不存在');
            return;
        }

        // 构建播放地址
        $playUrl = $this->playBase . $playInfo['url'];

        $video['playList'] = $playListItems;

        $f3->set('video', $video);
        $f3->set('playInfo', $playInfo);
        $f3->set('playUrl', $playUrl);
        $f3->set('title', '播放: ' . $video['name']);
        $f3->set('template', 'play.html');
        echo Template::instance()->render('layout.html');
    }

    /**
     * 分类列表
     */
    public function typelist($f3)
    {
        $categories = $this->getCategories($f3);

        $f3->set('categories', $categories);
        $f3->set('title', '分类列表');
        $f3->set('template', 'typelist.html');
        echo Template::instance()->render('layout.html');
    }

    /**
     * 加载视频列表
     */
    private function loadVideoList($f3)
    {
        $apiUrl = $this->buildApiUrl($f3);
        $xmlData = $this->fetchApi($apiUrl);

        $videos = [];
        $pagination = [
            'page' => 1,
            'pagecount' => 1,
            'pagesize' => 20,
            'recordcount' => 0
        ];

        if ($xmlData !== false) {
            $data = $this->parseVideoList($xmlData);
            $videos = $data['videos'];
            $pagination = $data['pagination'];
        }

        // 加载分类
        $categories = $this->getCategories($f3);

        $f3->set('videos', $videos);
        $f3->set('pagination', $pagination);
        $f3->set('categories', $categories);
        $f3->set('currentType', $f3->get('GET.t', 0));
    }

    /**
     * 公开方法 - 用于测试,加载视频列表数据但不渲染
     */
    public function loadVideoListData($f3)
    {
        $apiUrl = $this->buildApiUrl($f3);
        $xmlData = $this->fetchApi($apiUrl);

        $videos = [];
        $pagination = [
            'page' => 1,
            'pagecount' => 1,
            'pagesize' => 20,
            'recordcount' => 0
        ];

        if ($xmlData !== false) {
            $data = $this->parseVideoList($xmlData);
            $videos = $data['videos'];
            $pagination = $data['pagination'];
        }

        return [
            'videos' => $videos,
            'pagination' => $pagination
        ];
    }

    /**
     * 构建 API URL
     */
    private function buildApiUrl($f3)
    {
        $params = [
            'ac' => $f3->get('GET.ac', 'videolist')
        ];

        // 处理 page 参数 (支持 page 或 pg)
        if ($f3->exists('GET.page')) {
            $params['pg'] = $f3->get('GET.page');
        } elseif ($f3->exists('GET.pg')) {
            $params['pg'] = $f3->get('GET.pg');
        }

        if ($f3->exists('GET.ids')) {
            $params['ids'] = $f3->get('GET.ids');
        }
        if ($f3->exists('GET.t')) {
            $params['t'] = $f3->get('GET.t');
        }
        if ($f3->exists('GET.h')) {
            $params['h'] = $f3->get('GET.h');
        }
        if ($f3->exists('GET.wd')) {
            $params['wd'] = $f3->get('GET.wd');
        }

        return $this->apiBase . '?' . http_build_query($params);
    }

    /**
     * 获取 API 数据
     */
    private function fetchApi($url)
    {
        $web = Web::instance();
        $response = $web->request($url);

        // 检查响应
        if ($response === false || !isset($response['body']) || empty($response['body'])) {
            return false;
        }

        return $response['body'];
    }

    /**
     * 解析视频列表 XML
     */
    private function parseVideoList($xmlData)
    {
        $videos = [];
        $pagination = [
            'page' => 1,
            'pagecount' => 1,
            'pagesize' => 20,
            'recordcount' => 0
        ];

        try {
            $xml = simplexml_load_string($xmlData);

            if ($xml === false) {
                return ['videos' => [], 'pagination' => $pagination];
            }

            // 获取分页信息
            if (isset($xml->list)) {
                $list = $xml->list;
                $pagination['page'] = (int)$list['page'];
                $pagination['pagecount'] = (int)$list['pagecount'];
                $pagination['pagesize'] = (int)$list['pagesize'];
                $pagination['recordcount'] = (int)$list['recordcount'];
            }

            // 获取视频列表
            if (isset($xml->list->video)) {
                foreach ($xml->list->video as $video) {
                    $dlXml = isset($video->dl) ? $video->dl->asXML() : '';
                    $playList = $this->parsePlayListItems($dlXml);

                    $videos[] = [
                        'id' => (string)$video->id,
                        'tid' => (string)$video->tid,
                        'name' => (string)$video->name,
                        'type' => (string)$video->type,
                        'pic' => (string)$video->pic,
                        'lang' => (string)$video->lang,
                        'area' => (string)$video->area,
                        'year' => (string)$video->year,
                        'state' => (string)$video->state,
                        'note' => (string)$video->note,
                        'actor' => (string)$video->actor,
                        'director' => (string)$video->director,
                        'des' => (string)$video->des,
                        'last' => (string)$video->last,
                        'dl' => $dlXml,
                        'playList' => $playList
                    ];
                }
            }
        } catch (Exception $e) {
            // 解析失败，返回空数组
        }

        return ['videos' => $videos, 'pagination' => $pagination];
    }

    /**
     * 解析单个视频详情
     */
    private function parseVideoDetail($xmlData)
    {
        $data = $this->parseVideoList($xmlData);

        if (empty($data['videos'])) {
            return [];
        }

        return $data['videos'][0];
    }

    /**
     * 解析播放列表 - 返回所有播放项
     */
    private function parsePlayListItems($dlXml)
    {
        $playList = [];

        if (empty($dlXml)) {
            return [];
        }

        try {
            $xml = simplexml_load_string($dlXml);

            if ($xml === false) {
                return [];
            }

            $index = 0;
            foreach ($xml->dd as $dd) {
                $flag = (string)$dd['flag'];
                $text = (string)$dd;

                // 格式: 正片$url$flag
                $parts = explode('$', $text);

                if (count($parts) >= 2) {
                    $playList[] = [
                        'index' => $index,
                        'name' => $parts[0],
                        'url' => $parts[1],
                        'flag' => $flag
                    ];

                    $index++;
                }
            }

        } catch (Exception $e) {
            // 解析失败,返回空数组
        }

        return $playList;
    }

    /**
     * 获取所有分类 (公开方法,用于测试)
     * 使用 ac=list 参数获取分类列表
     */
    public function getCategories($f3)
    {
        $categories = [];

        // 使用 ac=list 参数获取分类数据
        $apiUrl = $this->apiBase . '?ac=list';
        $xmlData = $this->fetchApi($apiUrl);

        if ($xmlData !== false) {
            try {
                $xml = simplexml_load_string($xmlData);

                if ($xml !== false && isset($xml->class->ty)) {
                    foreach ($xml->class->ty as $type) {
                        $id = (string)$type['id'];
                        $name = (string)$type;
                        if (!empty($id) && !empty($name)) {
                            $categories[$id] = $name;
                        }
                    }
                }
            } catch (Exception $e) {
                // 解析失败
            }
        }

        return $categories;
    }
}
