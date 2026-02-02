<?php

namespace App\Services;

use SimpleXMLElement;

class VideoApiService
{
    private $apiUrl;
    private $playerUrl;

    public function __construct($apiUrl, $playerUrl)
    {
        $this->apiUrl = $apiUrl;
        $this->playerUrl = $playerUrl;
    }

    /**
     * 获取视频列表（带分类）
     */
    public function getVideoListWithCategories($page = 1, $pageSize = 20)
    {
        $url = $this->apiUrl . '?ac=list&pg=' . $page . '&pagesize=' . $pageSize;
        return $this->fetchAndParseXml($url);
    }

    /**
     * 获取视频列表（不带分类，详细信息）
     */
    public function getVideoList($page = 1, $pageSize = 20, $typeId = null, $hours = null)
    {
        $params = [
            'ac' => 'videolist',
            'pg' => $page,
            'pagesize' => $pageSize
        ];

        if ($typeId !== null) {
            $params['t'] = $typeId;
        }

        if ($hours !== null) {
            $params['h'] = $hours;
        }

        $url = $this->apiUrl . '?' . http_build_query($params);
        return $this->fetchAndParseXml($url);
    }

    /**
     * 获取特定视频详情
     */
    public function getVideoDetail($id)
    {
        $url = $this->apiUrl . '?ac=videolist&ids=' . $id;
        return $this->fetchAndParseXml($url);
    }

    /**
     * 搜索视频
     */
    public function searchVideos($keyword, $page = 1, $pageSize = 20)
    {
        $url = $this->apiUrl . '?ac=videolist&wd=' . urlencode($keyword) . '&pg=' . $page . '&pagesize=' . $pageSize;
        return $this->fetchAndParseXml($url);
    }

    /**
     * 获取播放器URL
     */
    public function getPlayerUrl($videoUrl)
    {
        return $this->playerUrl . $videoUrl;
    }

    /**
     * 获取并解析XML数据
     */
    private function fetchAndParseXml($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || empty($response)) {
            return [
                'success' => false,
                'message' => 'Failed to fetch data from API',
                'data' => []
            ];
        }

        return $this->parseXmlResponse($response);
    }

    /**
     * 解析XML响应
     */
    private function parseXmlResponse($xmlString)
    {
        try {
            $xml = new SimpleXMLElement($xmlString, LIBXML_NOCDATA);
            $result = [
                'success' => true,
                'data' => [],
                'categories' => [],
                'pagination' => []
            ];

            // 解析视频列表
            if (isset($xml->list->video)) {
                foreach ($xml->list->video as $video) {
                    $videoData = [
                        'id' => (string)$video->id,
                        'tid' => (string)$video->tid,
                        'name' => (string)$video->name,
                        'type' => (string)$video->type,
                        'dt' => (string)$video->dt,
                        'note' => (string)$video->note,
                        'last' => (string)$video->last ?? '',
                    ];

                    // 详细信息
                    if (isset($video->pic)) {
                        $videoData['pic'] = (string)$video->pic;
                    }
                    if (isset($video->lang)) {
                        $videoData['lang'] = (string)$video->lang;
                    }
                    if (isset($video->area)) {
                        $videoData['area'] = (string)$video->area;
                    }
                    if (isset($video->year)) {
                        $videoData['year'] = (string)$video->year;
                    }
                    if (isset($video->actor)) {
                        $videoData['actor'] = (string)$video->actor;
                    }
                    if (isset($video->director)) {
                        $videoData['director'] = (string)$video->director;
                    }
                    if (isset($video->des)) {
                        $videoData['des'] = (string)$video->des;
                    }

                    // 解析播放链接
                    if (isset($video->dl->dd)) {
                        $videoData['episodes'] = $this->parseEpisodes($video->dl->dd);
                    }

                    $result['data'][] = $videoData;
                }
            }

            // 解析分类
            if (isset($xml->class->ty)) {
                foreach ($xml->class->ty as $category) {
                    $result['categories'][] = [
                        'id' => (string)$category['id'],
                        'name' => (string)$category
                    ];
                }
            }

            // 解析分页信息
            if (isset($xml->list)) {
                $list = $xml->list;
                $result['pagination'] = [
                    'page' => (int)$list['page'] ?? 1,
                    'pagecount' => (int)$list['pagecount'] ?? 1,
                    'pagesize' => (int)$list['pagesize'] ?? 20,
                    'recordcount' => (int)$list['recordcount'] ?? 0
                ];
            }

            return $result;

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'XML parsing error: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }

    /**
     * 解析剧集信息
     */
    private function parseEpisodes($dd)
    {
        $episodes = [];
        $ddContent = (string)$dd;

        // 格式: 第1集$url$hhm3u8#第2集$url$hhm3u8
        $parts = explode('#', $ddContent);

        foreach ($parts as $part) {
            if (empty(trim($part))) {
                continue;
            }

            // 格式: 第1集$url$hhm3u8
            $episodeParts = explode('$', $part);
            if (count($episodeParts) >= 2) {
                $episodes[] = [
                    'name' => $episodeParts[0],
                    'url' => $episodeParts[1],
                    'flag' => $episodeParts[2] ?? 'hhm3u8'
                ];
            }
        }

        return $episodes;
    }
}
