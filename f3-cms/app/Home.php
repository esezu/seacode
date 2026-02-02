<?php
/**
 * 首页控制器
 */
class Home extends Controller {
    // 首页
    public function index($f3) {
        // 获取首页数据
        $apiUrl = $this->apiUrl;
        $homeApiUrl = $apiUrl . '?ac=videolist';
        $response = $this->fetchAPI($homeApiUrl);
        
        if ($response && isset($response['body'])) {
            $data = $this->parseXML($response['body']);
            
            if ($data && isset($data['list']['video'])) {
                $videos = $this->normalizeVideoData($data);
                
                $f3->set('list', $videos);
            }
        }
        
        $f3->set('hometitle', '首页');
        
        // 如果没有列表数据，设置默认值
        if (!$f3->exists('list')) {
            $f3->set('list', []);
        }
        $f3->set('page', 1);
        $f3->set('total', 0);
        $f3->set('start_page', 1);
        $f3->set('end_page', 1);
        
        // 获取分类列表用于导航
        $this->loadCategories($f3);
        
        $this->render('list.htm');
    }

    // 列表
    public function list($f3) {
        $page = $f3->get('GET.page') ?: $f3->get('PARAMS.page') ?: 1;
        $type_id = $f3->get('GET.t') ?: $f3->get('PARAMS.type_id') ?: '';
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $apiUrl = $this->apiUrl;
        if (!empty($type_id)) {
            $apiUrl .= '&t=' . $type_id;
        }
        $apiUrl .= '&pg=' . $page;
        $response = $this->fetchAPI($apiUrl);
        
        if ($response && isset($response['body'])) {
            $data = $this->parseXML($response['body']);
            
            if ($data && isset($data['list']['video'])) {
                $videos = $this->normalizeVideoData($data);
                
                $f3->set('list', $videos);
                $f3->set('page', $page);
                $f3->set('type_id', $type_id);
                $total = intval($data['list']['@attributes']['pagecount'] ?? 0);
                $f3->set('total', $total);
                $f3->set('pagesize', intval($data['list']['@attributes']['pagesize'] ?? 0));
                
                // 计算分页范围
                $start_page = $page > 5 ? $page - 5 : 1;
                $end_page = $page + 5 < $total ? $page + 5 : $total;
                $f3->set('start_page', $start_page);
                $f3->set('end_page', $end_page);
            }
        }
        
        $f3->set('hometitle', '视频列表');
        
        // 获取分类列表用于导航
        $this->loadCategories($f3);
        
        $this->render('list.htm');
    }

    // 搜索
    public function search($f3) {
        $wd = $f3->get('GET.wd') ?: '';
        $page = $f3->get('GET.page') ?: 1;
        
        if (!empty($wd)) {
            $apiUrl = $this->apiUrl;
            $searchApiUrl = $apiUrl . '?ac=videolist&wd=' . urlencode($wd) . '&pg=' . $page;
            $response = $this->fetchAPI($searchApiUrl);
            
            if ($response && isset($response['body'])) {
                $data = $this->parseXML($response['body']);
                
                if ($data && isset($data['list']['video'])) {
                    $videos = $this->normalizeVideoData($data);
                    
                    $f3->set('list', $videos);
                    $f3->set('wd', $wd);
                    $f3->set('page', $page);
                    $total = intval($data['list']['@attributes']['pagecount'] ?? 0);
                    $f3->set('total', $total);
                    $f3->set('pagesize', intval($data['list']['@attributes']['pagesize'] ?? 0));
                    
                    // 计算分页范围（用于搜索结果分页）
                    $start_page = $page > 5 ? $page - 5 : 1;
                    $end_page = $page + 5 < $total ? $page + 5 : $total;
                    $f3->set('start_page', $start_page);
                    $f3->set('end_page', $end_page);
                }
            }
        }
        
        $f3->set('hometitle', '搜索结果');
        
        // 获取分类列表用于导航
        $this->loadCategories($f3);
        
        $this->render('search.htm');
    }

    // 详情
    public function info($f3) {
        $id = $f3->get('PARAMS.id');
        
        if (!empty($id)) {
            $apiUrl = $this->apiUrl;
            $detailApiUrl = $apiUrl . '&ac=videolist&ids=' . $id;
            $response = $this->fetchAPI($detailApiUrl);
            
            if ($response && isset($response['body'])) {
                $data = $this->parseXML($response['body']);
                
                if ($data && isset($data['list']['video'])) {
                    $videos = $this->normalizeVideoData($data);
                    $video = $videos[0] ?? [];
                                
                    $f3->set('video', $video);
                                
                    // 解析播放地址
                    $playUrls = [];
                    if (isset($video['play_url']) && !empty($video['play_url'])) {
                        $playUrlStr = $video['play_url'];
                        $playGroups = explode('$$$',$playUrlStr);
                                    
                        foreach ($playGroups as $group) {
                            $groupName = explode('$', $group)[0];
                            $urls = substr($group, strpos($group, '$') + 1);
                            $playUrls[] = [
                                'name' => $groupName,
                                'urls' => $urls
                            ];
                        }
                    }
                    $f3->set('play_urls', $playUrls);
                }
            }
        }
        
        $f3->set('hometitle', '视频详情');
        
        // 获取分类列表用于导航
        $this->loadCategories($f3);
        
        $this->render('info.htm');
    }

    // 播放
    public function play($f3) {
        $id = $f3->get('PARAMS.id');
        $nid = $f3->get('PARAMS.nid');
        $playertype = $f3->get('GET.playertype') ?: 'dplayer';
        
        if (!empty($id) && !empty($nid)) {
            $apiUrl = $this->apiUrl;
            $detailApiUrl = $apiUrl . '&ac=videolist&ids=' . $id;
            $response = $this->fetchAPI($detailApiUrl);
            
            if ($response && isset($response['body'])) {
                $data = $this->parseXML($response['body']);
                
                if ($data && isset($data['list']['video'])) {
                    $videos = $this->normalizeVideoData($data);
                    $video = $videos[0] ?? [];
                                
                    $playUrl = '';
                    if (isset($video['play_url']) && !empty($video['play_url'])) {
                        $playUrlStr = $video['play_url'];
                        $playGroups = explode('$$$',$playUrlStr);
                                    
                        foreach ($playGroups as $group) {
                            $groupName = explode('$', $group)[0];
                            $urls = substr($group, strpos($group, '$') + 1);
                            $urlPairs = explode('#', $urls);
                                        
                            foreach ($urlPairs as $pair) {
                                list($name, $url) = explode('$', $pair);
                                if ($name == $nid) {
                                    $playUrl = $url;
                                    break 2;
                                }
                            }
                        }
                    }
                                
                    $f3->set('video', $video);
                    $f3->set('play_url', htmlspecialchars(urldecode($playUrl), ENT_QUOTES, 'UTF-8'));
                    $f3->set('playertype', $playertype);
                }
            }
        }
        
        $f3->set('hometitle', '视频播放');
        
        // 获取分类列表用于导航
        $this->loadCategories($f3);
        
        // 根据playertype参数选择播放器模板
        if ($playertype === 'videojs') {
            $this->render('playervideojs.htm');
        } else {
            // 默认使用DPlayer
            $this->render('play.htm');
        }
    }

    // 分类
    public function category($f3) {
        $type_id = $f3->get('PARAMS.type_id');
        $page = $f3->get('PARAMS.page') ?: 1;
        
        $apiUrl = $this->apiUrl;
        $catApiUrl = $apiUrl . '&t=' . $type_id . '&pg=' . $page;
        $response = $this->fetchAPI($catApiUrl);
        
        if ($response && isset($response['body'])) {
            $data = $this->parseXML($response['body']);
            
            if ($data && isset($data['list']['video'])) {
                $videos = $this->normalizeVideoData($data);
                
                $f3->set('list', $videos);
                $f3->set('page', $page);
                $total = intval($data['list']['@attributes']['pagecount'] ?? 0);
                $f3->set('total', $total);
                $f3->set('pagesize', intval($data['list']['@attributes']['pagesize'] ?? 0));
                
                // 计算分页范围
                $start_page = $page > 5 ? $page - 5 : 1;
                $end_page = $page + 5 < $total ? $page + 5 : $total;
                $f3->set('start_page', $start_page);
                $f3->set('end_page', $end_page);
            }
        }
        
        $f3->set('hometitle', '分类列表');
        
        // 获取分类列表用于导航
        $this->loadCategories($f3);
        
        $this->render('list.htm');
    }

    // 分类列表
    public function typelist($f3) {
        $apiUrl = $this->apiUrl;
        $xml_response = $this->fetchAPI($apiUrl);
        
        if ($xml_response && isset($xml_response['body'])) {
            $categories = $this->fenleiXml($xml_response['body']);
            $f3->set('categories', json_decode($categories, true));
        } else {
            // 如果API不可用，设置空数组
            $f3->set('categories', []);
        }
        
        $f3->set('hometitle', '分类列表');
        
        // 获取分类列表用于导航
        $this->loadCategories($f3);
        
        $this->render('typelist.htm');
    }
}