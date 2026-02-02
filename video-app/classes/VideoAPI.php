<?php

class VideoAPI {

    private $apiUrl;

    public function __construct() {
        $f3 = Base::instance();
        $this->apiUrl = $f3->get('API_URL');
    }

    /**
     * 获取XML数据
     */
    private function fetchXML($params = []) {
        $url = $this->apiUrl;

        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');

        $response = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);

        if ($error) {
            throw new Exception('API请求失败: ' . $error);
        }

        // 解析XML
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($response, 'SimpleXMLElement', LIBXML_NOCDATA);

        if ($xml === false) {
            $errors = libxml_get_errors();
            throw new Exception('XML解析失败: ' . $errors[0]->message);
        }

        return $xml;
    }

    /**
     * 获取视频列表
     */
    public function getVideoList($params = []) {
        $defaultParams = ['ac' => 'list'];
        $params = array_merge($defaultParams, $params);

        $xml = $this->fetchXML($params);
        $list = $xml->list;
        $class = $xml->class;

        $videos = [];
        if ($list && $list->video) {
            foreach ($list->video as $video) {
                $videos[] = [
                    'id' => (string)$video->id,
                    'tid' => (string)$video->tid,
                    'name' => (string)$video->name,
                    'type' => (string)$video->type,
                    'last' => (string)$video->last,
                    'note' => (string)$video->note,
                    'dt' => (string)$video->dt
                ];
            }
        }

        $pagination = [];
        if ($list) {
            $pagination = [
                'page' => (int)$list['page'],
                'pagecount' => (int)$list['pagecount'],
                'pagesize' => (int)$list['pagesize'],
                'recordcount' => (int)$list['recordcount']
            ];
        }

        // 解析分类
        $categories = [];
        if ($class && $class->ty) {
            foreach ($class->ty as $ty) {
                $categories[(string)$ty['id']] = (string)$ty;
            }
        }

        return [
            'videos' => $videos,
            'pagination' => $pagination,
            'categories' => $categories
        ];
    }

    /**
     * 获取视频详情
     */
    public function getVideoDetail($id) {
        $xml = $this->fetchXML(['ac' => 'videolist', 'ids' => $id]);
        $list = $xml->list;

        if (!$list || !$list->video) {
            return null;
        }

        $video = $list->video[0];
        $data = [
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
            'episodes' => []
        ];

        // 解析播放链接
        if ($video->dl && $video->dl->dd) {
            $playData = (string)$video->dl->dd;
            $flag = (string)$video->dl->dd['flag'];

            if ($playData) {
                // 按播放线路分隔
                $playLines = explode('#', $playData);

                foreach ($playLines as $line) {
                    if (strpos($line, '$') !== false) {
                        $parts = explode('$', $line);
                        if (count($parts) >= 2) {
                            $episodeName = $parts[0];
                            $url = $parts[1];

                            // 移除播放线路标记
                            if (strpos($episodeName, $flag) !== false) {
                                $episodeName = str_replace($flag, '', $episodeName);
                                $episodeName = trim($episodeName);
                            }

                            $data['episodes'][] = [
                                'name' => $episodeName,
                                'url' => $url,
                                'flag' => $flag
                            ];
                        }
                    }
                }
            }
        }

        return $data;
    }

    /**
     * 获取分类列表
     */
    public function getCategories() {
        $xml = $this->fetchXML(['ac' => 'list']);
        $class = $xml->class;

        $categories = [];
        if ($class && $class->ty) {
            foreach ($class->ty as $ty) {
                $categories[(string)$ty['id']] = (string)$ty;
            }
        }

        return $categories;
    }

    /**
     * 搜索视频
     */
    public function search($keyword, $page = 1) {
        return $this->getVideoList([
            'ac' => 'videolist',
            'wd' => $keyword,
            'pg' => $page
        ]);
    }

    /**
     * 获取最新视频
     */
    public function getLatest($hours = 24, $page = 1) {
        return $this->getVideoList([
            'ac' => 'videolist',
            'h' => $hours,
            'pg' => $page
        ]);
    }
}
