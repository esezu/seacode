<?php
/**
 * HomeController - F3 影视视频程序控制器
 * 处理首页、列表、分类、搜索、详情、播放等核心功能
 */

class HomeController {
    
    private $apiBase;
    
    public function __construct() {
        $f3 = Base::instance();
        $this->apiBase = $f3->get('API_BASE');
    }
    
    /**
     * 获取分类列表 - 实时从API获取
     */
    private function getCategories() {
        try {
            $apiUrl = $this->apiBase . '?ac=list';
            $response = file_get_contents($apiUrl);
            
            if ($response === false) {
                return [];
            }
            
            $xml = simplexml_load_string($response);
            if ($xml === false) {
                return [];
            }
            
            $categories = [];
            if (isset($xml->class)) {
                foreach ($xml->class->ty as $type) {
                    $typeId = (int)$type->attributes()->id;
                    $typeName = (string)$type;
                    if ($typeId > 0 && !empty($typeName)) {
                        $categories[$typeId] = $typeName;
                    }
                }
            }
            
            return $categories;
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * 首页 - 显示最新视频
     */
    public function index($f3) {
        $f3->set('title', '最新视频 - F3 影视');
        $f3->set('template', 'list.html');
        
        // 获取分类
        $categories = $this->getCategories();
        $f3->set('categories', $categories);
        
        // 获取最新视频
        try {
            $page = $f3->get('GET.page') ?: 1;
            $apiUrl = $this->apiBase . '?ac=videolist&pg=' . $page;
            $response = file_get_contents($apiUrl);
            
            if ($response !== false) {
                $xml = simplexml_load_string($response);
                if ($xml !== false && isset($xml->list->video)) {
                    $videos = [];
                    foreach ($xml->list->video as $video) {
                        $videos[] = [
                            'id' => (string)$video->id,
                            'name' => (string)$video->name,
                            'pic' => (string)$video->pic,
                            'year' => (string)$video->year,
                            'area' => (string)$video->area,
                            'note' => (string)$video->note
                        ];
                    }
                    $f3->set('videos', $videos);
                    
                    // 分页信息
                    $total = (int)$xml->list->attributes()->pagecount;
                    $f3->set('pagination', [
                        'page' => (int)$page,
                        'pagecount' => $total
                    ]);
                }
            }
        } catch (Exception $e) {
            // API调用失败，设置空数据
            $f3->set('videos', []);
        }
        
        echo Template::instance()->render('layout.html');
    }
    
    /**
     * 视频列表页 - 支持分页
     */
    public function list($f3, $params) {
        $f3->set('title', '视频列表 - F3 影视');
        $f3->set('template', 'list.html');
        
        // 获取分类
        $categories = $this->getCategories();
        $f3->set('categories', $categories);
        
        // 获取视频列表
        try {
            $page = isset($params['page']) ? (int)$params['page'] : ($f3->get('GET.page') ?: 1);
            $apiUrl = $this->apiBase . '?ac=videolist&pg=' . $page;
            $response = file_get_contents($apiUrl);
            
            if ($response !== false) {
                $xml = simplexml_load_string($response);
                if ($xml !== false && isset($xml->list->video)) {
                    $videos = [];
                    foreach ($xml->list->video as $video) {
                        $videos[] = [
                            'id' => (string)$video->id,
                            'name' => (string)$video->name,
                            'pic' => (string)$video->pic,
                            'year' => (string)$video->year,
                            'area' => (string)$video->area,
                            'note' => (string)$video->note
                        ];
                    }
                    $f3->set('videos', $videos);
                    
                    // 分页信息
                    $total = (int)$xml->list->attributes()->pagecount;
                    $f3->set('pagination', [
                        'page' => $page,
                        'pagecount' => $total
                    ]);
                }
            }
        } catch (Exception $e) {
            // API调用失败，设置空数据
            $f3->set('videos', []);
        }
        
        echo Template::instance()->render('layout.html');
    }
    
    /**
     * 分类视频列表
     */
    public function category($f3, $params) {
        $typeId = $params['type_id'];
        $page = isset($params['page']) ? (int)$params['page'] : ($f3->get('GET.page') ?: 1);
        
        // 获取分类
        $categories = $this->getCategories();
        $f3->set('categories', $categories);
        
        // 设置标题
        $categoryName = isset($categories[$typeId]) ? $categories[$typeId] : '未知分类';
        $f3->set('title', $categoryName . ' - F3 影视');
        $f3->set('template', 'list.html');
        
        // 获取分类视频
        try {
            $apiUrl = $this->apiBase . '?ac=videolist&t=' . $typeId . '&pg=' . $page;
            $response = file_get_contents($apiUrl);
            
            if ($response !== false) {
                $xml = simplexml_load_string($response);
                if ($xml !== false && isset($xml->list->video)) {
                    $videos = [];
                    foreach ($xml->list->video as $video) {
                        $videos[] = [
                            'id' => (string)$video->id,
                            'name' => (string)$video->name,
                            'pic' => (string)$video->pic,
                            'year' => (string)$video->year,
                            'area' => (string)$video->area,
                            'note' => (string)$video->note
                        ];
                    }
                    $f3->set('videos', $videos);
                    
                    // 分页信息
                    $total = (int)$xml->list->attributes()->pagecount;
                    $f3->set('pagination', [
                        'page' => $page,
                        'pagecount' => $total
                    ]);
                }
            }
        } catch (Exception $e) {
            // API调用失败，设置空数据
            $f3->set('videos', []);
        }
        
        echo Template::instance()->render('layout.html');
    }
    
    /**
     * 搜索功能
     */
    public function search($f3) {
        $keyword = $f3->get('GET.wd');
        
        if (empty($keyword)) {
            $f3->reroute('/');
            return;
        }
        
        $f3->set('title', '搜索: ' . $keyword . ' - F3 影视');
        $f3->set('template', 'search.htm');
        
        // 获取分类
        $categories = $this->getCategories();
        $f3->set('categories', $categories);
        
        // 搜索视频
        try {
            $page = $f3->get('GET.page') ?: 1;
            $apiUrl = $this->apiBase . '?ac=videolist&wd=' . urlencode($keyword) . '&pg=' . $page;
            $response = file_get_contents($apiUrl);
            
            if ($response !== false) {
                $xml = simplexml_load_string($response);
                if ($xml !== false && isset($xml->list->video)) {
                    $videos = [];
                    foreach ($xml->list->video as $video) {
                        $videos[] = [
                            'id' => (string)$video->id,
                            'name' => (string)$video->name,
                            'pic' => (string)$video->pic,
                            'year' => (string)$video->year,
                            'area' => (string)$video->area,
                            'note' => (string)$video->note
                        ];
                    }
                    $f3->set('videos', $videos);
                    
                    // 分页信息
                    $total = (int)$xml->list->attributes()->pagecount;
                    $records = (int)$xml->list->attributes()->recordcount;
                    $f3->set('total', $records);
                    $f3->set('pagination', [
                        'page' => $page,
                        'pagecount' => $total
                    ]);
                }
            }
        } catch (Exception $e) {
            // API调用失败，设置空数据
            $f3->set('videos', []);
        }
        
        echo Template::instance()->render('layout.html');
    }
    
    /**
     * 视频详情页
     */
    public function info($f3, $params) {
        $videoId = $params['id'];
        
        $f3->set('title', '视频详情 - F3 影视');
        $f3->set('template', 'info.html');
        
        // 获取分类
        $categories = $this->getCategories();
        $f3->set('categories', $categories);
        
        // 获取视频详情
        try {
            $apiUrl = $this->apiBase . '?ac=videolist&ids=' . $videoId;
            $response = file_get_contents($apiUrl);
            
            if ($response !== false) {
                $xml = simplexml_load_string($response);
                if ($xml !== false && isset($xml->list->video)) {
                    $videoData = $xml->list->video;
                    
                    // 解析播放列表
                    $playList = [];
                    if (isset($videoData->dl->dd)) {
                        $playUrl = (string)$videoData->dl->dd;
                        // 解析播放列表格式：第01集$https://...|第02集$https://...
                        $episodes = explode('#', $playUrl);
                        foreach ($episodes as $episode) {
                            if (!empty($episode)) {
                                $parts = explode('$', $episode);
                                if (count($parts) >= 2) {
                                    $playList[] = [
                                        'name' => $parts[0],
                                        'url' => $parts[1]
                                    ];
                                }
                            }
                        }
                    }
                    
                    $video = [
                        'id' => (string)$videoData->id,
                        'name' => (string)$videoData->name,
                        'pic' => (string)$videoData->pic,
                        'year' => (string)$videoData->year,
                        'area' => (string)$videoData->area,
                        'type' => (string)$videoData->type,
                        'lang' => (string)$videoData->lang,
                        'note' => (string)$videoData->note,
                        'director' => (string)$videoData->director,
                        'actor' => (string)$videoData->actor,
                        'last' => (string)$videoData->last,
                        'des' => (string)$videoData->des,
                        'playList' => $playList
                    ];
                    
                    $f3->set('video', $video);
                    $f3->set('title', $video['name'] . ' - F3 影视');
                }
            }
        } catch (Exception $e) {
            // API调用失败，设置空数据
            $f3->set('video', null);
        }
        
        echo Template::instance()->render('layout.html');
    }
    
    /**
     * 视频播放页
     */
    public function play($f3, $params) {
        $videoId = $params['id'];
        $episodeIndex = $params['nid'];
        
        $f3->set('title', '视频播放 - F3 影视');
        $f3->set('template', 'play.html');
        
        // 获取分类
        $categories = $this->getCategories();
        $f3->set('categories', $categories);
        
        // 获取视频详情和播放地址
        try {
            $apiUrl = $this->apiBase . '?ac=videolist&ids=' . $videoId;
            $response = file_get_contents($apiUrl);
            
            if ($response !== false) {
                $xml = simplexml_load_string($response);
                if ($xml !== false && isset($xml->list->video)) {
                    $videoData = $xml->list->video;
                    
                    // 解析播放列表
                    $playList = [];
                    $currentPlayUrl = '';
                    if (isset($videoData->dl->dd)) {
                        $playUrl = (string)$videoData->dl->dd;
                        // 解析播放列表格式：第01集$https://...|第02集$https://...
                        $episodes = explode('#', $playUrl);
                        foreach ($episodes as $index => $episode) {
                            if (!empty($episode)) {
                                $parts = explode('$', $episode);
                                if (count($parts) >= 2) {
                                    $playList[] = [
                                        'name' => $parts[0],
                                        'url' => $parts[1]
                                    ];
                                    
                                    // 设置当前播放集数
                                    if ($index == $episodeIndex) {
                                        $currentPlayUrl = $parts[1];
                                    }
                                }
                            }
                        }
                    }
                    
                    // 生成播放器URL
                    $playBase = $f3->get('PLAY_BASE');
                    $playerUrl = $currentPlayUrl ? $playBase . urlencode($currentPlayUrl) : '';
                    
                    $video = [
                        'id' => (string)$videoData->id,
                        'name' => (string)$videoData->name,
                        'playList' => $playList,
                        'currentEpisode' => $episodeIndex,
                        'playerUrl' => $playerUrl
                    ];
                    
                    $f3->set('video', $video);
                    $f3->set('title', $video['name'] . ' - 第' . ($episodeIndex + 1) . '集 - F3 影视');
                }
            }
        } catch (Exception $e) {
            // API调用失败，设置空数据
            $f3->set('video', null);
        }
        
        echo Template::instance()->render('layout.html');
    }
}