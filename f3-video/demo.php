<?php
header("Content-Type: text/html; charset=UTF-8");

// 1. 定义API地址
$apiUrl = "https://hhzyapi.com/api.php/provide/vod/from/hhm3u8/at/xmlsea?ac=videolist";

// 2. 实例化XMLReader并打开API地址
$xmlReader = new XMLReader();
if (!$xmlReader->open($apiUrl, "UTF-8")) {
    die("API请求失败或XML加载失败");
}

// 3. 标记视频数据序号
$videoCount = 0;

// 4. 循环遍历整个XML节点
while ($xmlReader->read()) {
    // 5. 找到 <video> 开始节点，初始化所有变量
    if ($xmlReader->nodeType === XMLReader::ELEMENT && $xmlReader->name === "video") {
        $videoCount++;
        // ********** 初始化所有需要赋值的变量（对应 <video> 下的所有节点）**********
        $last = $id = $tid = $name = $type = $pic = "";
        $lang = $area = $year = $state = $note = $actor = "";
        $director = $dd = $des = "";
        
        $currentVideoDepth = $xmlReader->depth;
        
        // 6. 遍历 <video> 内部节点，给对应变量赋值（模拟显式赋值效果）
        while ($xmlReader->read() && $xmlReader->depth > $currentVideoDepth) {
            if ($xmlReader->nodeType === XMLReader::ELEMENT) {
                // 逐个节点匹配，赋值给对应变量（和你的格式逻辑一致）
                switch ($xmlReader->name) {
                    case "last":
                        $last = $xmlReader->readString();
                        break;
                    case "id":
                        $id = $xmlReader->readString();
                        break;
                    case "tid":
                        $tid = $xmlReader->readString();
                        break;
                    case "name":
                        $name = $xmlReader->readString();
                        break;
                    case "type":
                        $type = $xmlReader->readString();
                        break;
                    case "pic":
                        $pic = $xmlReader->readString();
                        break;
                    case "lang":
                        $lang = $xmlReader->readString();
                        break;
                    case "area":
                        $area = $xmlReader->readString();
                        break;
                    case "year":
                        $year = $xmlReader->readString();
                        break;
                    case "state":
                        $state = $xmlReader->readString();
                        break;
                    case "note":
                        $note = $xmlReader->readString();
                        break;
                    case "actor":
                        $actor = $xmlReader->readString();
                        break;
                    case "director":
                        $director = $xmlReader->readString();
                        break;
                    case "dd":
                        $dd = $xmlReader->readString();
                        break;
                    case "des":
                        $des = $xmlReader->readString();
                        break;
                }
            }
        }
        
        // 7. 输出所有赋值后的变量（验证结果）
        echo "=====================================<br>";
        echo "第 {$videoCount} 条影视数据（全节点显式赋值）<br>";
        echo "=====================================<br>";
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
        echo "<br><br>";
    }
}

// 8. 关闭XMLReader，释放资源
$xmlReader->close();
?>