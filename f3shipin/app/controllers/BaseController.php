<?php

class BaseController
{
    protected $f3;
    protected $videoModel;

    public function __construct()
    {
        $this->f3 = Base::instance();
        $this->videoModel = new VideoModel();
    }

    /**
     * 渲染视图
     */
    protected function render($template, $data = [])
    {
        $data['config'] = [
            'site_title' => '影视视频网站',
            'base_url' => $this->f3->get('BASE'),
            'player_url' => $this->f3->get('PLAYER_URL')
        ];
        
        foreach ($data as $key => $value) {
            $this->f3->set($key, $value);
        }
        
        echo Template::instance()->render($template);
    }

    /**
     * 返回JSON响应
     */
    protected function jsonResponse($data)
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * 错误页面
     */
    protected function showError($message = '页面不存在', $code = 404)
    {
        http_response_code($code);
        $this->render('error.htm', ['message' => $message, 'code' => $code]);
    }

    /**
     * 获取分页参数
     */
    protected function getPaginationParams()
    {
        $page = max(1, (int)$this->f3->get('GET.pg', 1));
        $itemsPerPage = (int)$this->f3->get('ITEMS_PER_PAGE', 20);
        
        return [
            'page' => $page,
            'itemsPerPage' => $itemsPerPage,
            'offset' => ($page - 1) * $itemsPerPage
        ];
    }
}