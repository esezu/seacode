<?php
namespace App;

/**
 * VodApi
 * 封装对外部 XML API 的请求与简单解析，供视频数据控制器调用。
 */
class VodApi {
  // 外部接口的基础地址
  protected $base = 'https://hhzyapi.com/api.php/provide/vod/from/hhm3u8/at/xmlsea';

  public function fetch($params = []) {
    $query = http_build_query($params);
    $url = $this->base . '?' . $query;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 12);
    $data = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($code >= 200 && $code < 300) {
      return $data;
    }
    return false;
  }

  public function xmlToArray($xml) {
    if (!$xml) return [];
    $obj = simplexml_load_string($xml, "SimpleXMLElement", LIBXML_NOCDATA);
    $json = json_decode(json_encode($obj), true);
    return $json;
  }
}
