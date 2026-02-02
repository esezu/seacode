<?php
header("Content-Type: text/html; charset=UTF-8");

// 1. 定义API地址
$apiUrl = "https://hhzyapi.com/api.php/provide/vod/from/hhm3u8/at/xmlsea?ac=videolist";

// 2. 获取API返回的XML内容
$xmlContent = file_get_contents($apiUrl);
if (!$xmlContent) {
    die("API请求失败，请检查网络或接口有效性");
}

// 3. 加载XML字符串
$xml = simplexml_load_string($xmlContent);
if (!$xml) {
    die("XML格式非法，解析失败");
}

// 4. 遍历外层list下的所有video节点
foreach ($xml->list->video as $videoIndex => $video) {
    
    // ********** 核心：所有 <video> 节点 逐一显式赋值（和你的格式完全一致）**********
    // 基础信息节点
    $last = (string)$video->last;
    $id = (string)$video->id;
    $tid = (string)$video->tid;
    $name = (string)$video->name;
    $type = (string)$video->type;
    $pic = (string)$video->pic;
    $lang = (string)$video->lang;
    $area = (string)$video->area;
    $year = (string)$video->year;
    $state = (string)$video->state;
    $note = (string)$video->note;
    $actor = (string)$video->actor;
    $director = (string)$video->director;
    $des = (string)$video->des;
    
    // 嵌套节点：dl 下的 dd（同样显式赋值）
    $dd = (string)$video->dl->dd; // 提取 dl 节点下的 dd 节点值
    
    // ********** 输出所有赋值后的变量（验证结果）**********
    echo "last：{$last}<br>";
    echo "id：{$id}<br>";
    echo "tid：{$tid}<br>";
    echo "name：{$name}<br>";
    echo "type：{$type}<br>";
    echo "pic：{$pic}<br>";
    echo "lang：{$lang}<br>";
    echo "area：{$area}<br>";
    echo "year：{$year}<br>";
    echo "state：{$state}<br>";
    echo "note：{$note}<br>";
    echo "actor：{$actor}<br>";
    echo "director：{$director}<br>";
    echo "dd（播放资源）：{$dd}<br>";
    echo "des：{$des}<br>";
    
    echo "<br>----------------------------<br>";
}
?>