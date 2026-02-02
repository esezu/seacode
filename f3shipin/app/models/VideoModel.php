<?php

class VideoModel
{
    private $f3;
    private $apiBaseUrl;

    public function __construct()
    {
        $this->f3 = Base::instance();
        $this->apiBaseUrl = $this->f3->get('API_BASE_URL');
    }

    /**
     * 获取视频列表
     */
    public function getVideoList($params = [])
    {
        $queryParams = array_merge([
            'ac' => 'videolist',
            'pg' => 1,
            't' => '',
            'wd' => '',
            'h' => ''
        ], $params);

        $url = $this->apiBaseUrl . '?' . http_build_query($queryParams);
        
        $xmlData = $this->fetchXmlData($url);
        
        if (!$xmlData) {
            return ['error' => '无法获取数据'];
        }

        return $this->parseVideoList($xmlData);
    }

    /**
     * 获取视频详情
     */
    public function getVideoDetail($id)
    {
        $url = $this->apiBaseUrl . '?ac=videolist&ids=' . intval($id);
        $xmlData = $this->fetchXmlData($url);
        
        if (!$xmlData) {
            return ['error' => '无法获取详情数据'];
        }

        return $this->parseVideoDetail($xmlData);
    }

    /**
     * 获取分类列表
     */
    public function getCategories()
    {
        $url = $this->apiBaseUrl . '?ac=list';
        $xmlData = $this->fetchXmlData($url);
        
        if (!$xmlData) {
            return ['error' => '无法获取分类数据'];
        }

        return $this->parseCategories($xmlData);
    }

    /**
     * 获取最新视频列表
     */
    public function getLatestVideos($hours = 24)
    {
        return $this->getVideoList(['h' => $hours]);
    }

    /**
     * 按分类获取视频
     */
    public function getVideosByCategory($categoryId, $page = 1)
    {
        return $this->getVideoList(['t' => $categoryId, 'pg' => $page]);
    }

    /**
     * 搜索视频
     */
    public function searchVideos($keyword, $page = 1)
    {
        return $this->getVideoList(['wd' => $keyword, 'pg' => $page]);
    }

    /**
     * 获取XML数据
     */
    private function fetchXmlData($url)
    {
        $this->f3->log("获取数据: " . $url);
        
        try {
            $response = $this->f3->request($url);
            
            if ($response['status'] !== 200) {
                $this->f3->log("请求失败: " . $response['status']);
                return false;
            }
            
            return $response['body'];
        } catch (Exception $e) {
            $this->f3->log("请求异常: " . $e->getMessage());
            return false;
        }
    }

    /**
     * 解析视频列表XML
     */
    private function parseVideoList($xmlString)
    {
        try {
            $xml = simplexml_load_string($xmlString);
            
            if (!$xml) {
                throw new Exception('XML解析失败');
            }

            $result = [
                'page' => (int)$xml->list['page'],
                'pagecount' => (int)$xml->list['pagecount'],
                'pagesize' => (int)$xml->list['pagesize'],
                'recordcount' => (int)$xml->list['recordcount'],
                'videos' => []
            ];

            foreach ($xml->list->video as $video) {
                $result['videos'][] = [
                    'id' => (int)$video->id,
                    'tid' => (int)$video->tid,
                    'name' => (string)$video->name,
                    'type' => (string)$video->type,
                    'pic' => (string)$video->pic,
                    'note' => (string)$video->note,
                    'last' => (string)$video->last,
                    'actor' => (string)$video->actor,
                    'director' => (string)$video->director,
                    'year' => (string)$video->year,
                    'area' => (string)$video->area
                ];
            }

            return $result;
        } catch (Exception $e) {
            $this->f3->log("解析视频列表失败: " . $e->getMessage());
            return ['error' => '数据解析失败'];
        }
    }

    /**
     * 解析视频详情XML
     */
    private function parseVideoDetail($xmlString)
    {
        try {
            $xml = simplexml_load_string($xmlString);
            
            if (!$xml || count($xml->list->video) === 0) {
                return ['error' => '视频不存在'];
            }

            $video = $xml->list->video[0];
            
            $episodes = [];
            if (isset($video->dl->dd)) {
                $ddContent = (string)$video->dl->dd;
                $episodeList = explode('#', $ddContent);
                
                foreach ($episodeList as $episode) {
                    if (empty(trim($episode))) continue;
                    
                    $parts = explode('$', $episode, 3);
                    if (count($parts) >= 2) {
                        $episodes[] = [
                            'title' => $parts[0],
                            'url' => $parts[1],
                            'type' => isset($parts[2]) ? $parts[2] : 'hhm3u8'
                        ];
                    }
                }
            }

            return [
                'id' => (int)$video->id,
                'tid' => (int)$video->tid,
                'name' => (string)$video->name,
                'type' => (string)$video->type,
                'pic' => (string)$video->pic,
                'note' => (string)$video->note,
                'actor' => (string)$video->actor,
                'director' => (string)$video->director,
                'year' => (string)$video->year,
                'area' => (string)$video->area,
                'lang' => (string)$video->lang,
                'state' => (string)$video->state,
                'des' => (string)$video->des,
                'last' => (string)$video->last,
                'episodes' => $episodes
            ];
        } catch (Exception $e) {
            $this->f3->log("解析视频详情失败: " . $e->getMessage());
            return ['error' => '详情解析失败'];
        }
    }

    /**
     * 解析分类XML
     */
    private function parseCategories($xmlString)
    {
        try {
            $xml = simplexml_load_string($xmlString);
            
            if (!$xml) {
                throw new Exception('XML解析失败');
            }

            $categories = [];
            foreach ($xml->class->ty as $category) {
                $categories[] = [
                    'id' => (int)$category['id'],
                    'name' => (string)$category
                ];
            }

            return $categories;
        } catch (Exception $e) {
            $this->f3->log("解析分类失败: " . $e->getMessage());
            return ['error' => '分类解析失败'];
        }
    }
}