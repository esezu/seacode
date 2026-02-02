<?php
namespace App;

/**
 * VideoController
 * 负责将外部 API 数据整理为前端所需的结构，提供列表、详情、剧集等接口。
 */
class VideoController {
  protected $api;
  public function __construct() {
    $this->api = new VodApi();
  }

  /**
   * 获取视频列表（或带简要信息的详细模式）。
   * @param array $params 请求参数
   * @return array 结果数据，包含 videos 与 categories
   */
  public function listVideos($params) {
    // Fetch list or detailed info depending on ac flag
    $xml = $this->api->fetch($params);
    $data = $this->api->xmlToArray($xml);
    $videos = [];
    if (isset($data['list']['video'])) {
      $list = $data['list']['video'];
      if (!isset($list[0])) $list = [$list];
      foreach ($list as $v) {
        $videos[] = [
          'id' => isset($v['id']) ? $v['id'] : '',
          'title' => isset($v['name']) ? (string)$v['name'] : '',
          'type' => isset($v['type']) ? $v['type'] : '',
          'last' => isset($v['last']) ? $v['last'] : '',
          'pic' => isset($v['pic']) ? $v['pic'] : '',
          'note' => isset($v['note']) ? $v['note'] : '',
        ];
      }
    }
    $categories = [];
    if (isset($data['class']['ty'])) {
      $tys = $data['class']['ty'];
      if (!isset($tys[0])) $tys = [$tys];
      foreach ($tys as $t) {
        $name = isset($t[0]['#text']) ? $t[0]['#text'] : (isset($t['#text']) ? $t['#text'] : '');
        $id = isset($t['@attributes']['id']) ? $t['@attributes']['id'] : (isset($t['id']) ? $t['id'] : '');
        if ($id !== '') $categories[] = ['id'=>$id, 'name'=>$name];
      }
    }
    return ['videos'=>$videos, 'categories'=>$categories];
  }

  /**
   * 获取指定影片的详细信息（含分集信息）。
   * @param string $ids 影片ID，逗号分隔
   * @return array 影片详情
   */
  public function getVideoDetail($ids) {
    if (!$ids) return ['error'=>'ids required'];
    $xml = $this->api->fetch(['ac'=>'videolist','ids'=>$ids]);
    $data = $this->api->xmlToArray($xml);
    $video = [];
    if (isset($data['list']['video'])) {
      $v = $data['list']['video'];
      $video = [
        'id'=>$v['id'] ?? '',
        'title'=>$v['name'] ?? '',
        'type'=>$v['type'] ?? '',
        'pic'=>$v['pic'] ?? '',
        'des'=> isset($data['list']['des']) ? $data['list']['des'] : (isset($v['note']) ? $v['note'] : ''),
      ];
      $episodes = [];
      $dl = $data['list']['video']['dl'] ?? [];
      $dd = isset($dl['dd']) ? $dl['dd'] : null;
      if ($dd !== null) {
        if (is_string($dd)) {
          $episodes = $this->parseEpisodesFromString($dd);
        } elseif (is_array($dd)) {
          foreach ($dd as $ep) {
            if (isset($ep['#text'])) {
              $parts = explode('$', (string)$ep['#text']);
              if (count($parts) >= 2) {
                $episodes[] = ['name'=>$parts[0], 'url'=>$parts[1]];
              }
            }
          }
        }
      }
      $video['episodes'] = $episodes;
    }
    return $video;
  }

  /**
   * 获取指定影片的分集及播放地址。
   * @param string $id 影片ID
   * @return array
   */
  public function getEpisodes($id) {
    if (!$id) return ['error'=>'id required'];
    $xml = $this->api->fetch(['ac'=>'videolist','ids'=>$id]);
    $data = $this->api->xmlToArray($xml);
    $episodes = [];
    $dl = $data['list']['video']['dl'] ?? [];
    $dd = isset($dl['dd']) ? $dl['dd'] : null;
    if ($dd !== null) {
      if (is_string($dd)) {
        $episodes = $this->parseEpisodesFromString($dd);
      } elseif (is_array($dd)) {
        foreach ($dd as $ep) {
          if (isset($ep['#text'])) {
            $text = (string)$ep['#text'];
            $parts = explode('$', $text);
            if (count($parts) >= 2) {
              $episodes[] = ['name'=>$parts[0], 'url'=>$parts[1]];
            }
          }
        }
      }
    }
    return ['id'=>$id, 'episodes'=>$episodes];
  }

  /**
   * 获取分类列表。
   * @return array 分类列表
   */
  public function listCategories() {
    $xml = $this->api->fetch(['ac'=>'list']);
    $data = $this->api->xmlToArray($xml);
    $categories = [];
    if (isset($data['class']['ty'])) {
      $tys = $data['class']['ty'];
      if (!isset($tys[0])) $tys = [$tys];
      foreach ($tys as $t) {
        $id = isset($t['@attributes']['id']) ? $t['@attributes']['id'] : (isset($t['id']) ? $t['id'] : '');
        $name = isset($t['#text']) ? $t['#text'] : '';
        if ($id !== '') $categories[] = ['id'=>$id, 'name'=>$name];
      }
    }
    return $categories;
  }

  protected function parseEpisodesFromString($dd) {
    // dd format: 第1集$URL$hhm3u8#第2集$URL$hhm3u8
    $episodes = [];
    $parts = explode('#', $dd);
    foreach ($parts as $p) {
      if (empty($p)) continue;
      $items = explode('$', $p);
      if (count($items) >= 2) {
        $episodes[] = ['name'=>$items[0], 'url'=>$items[1]];
      }
    }
    return $episodes;
  }
}
