<?php
/**
 * API测试脚本
 * 用于测试视频API是否正常工作
 */

// API配置
$apiUrl = 'https://hhzyapi.com/api.php/provide/vod/from/hhm3u8/at/xmlsea';

echo "=====================================\n";
echo "视频API测试脚本\n";
echo "=====================================\n\n";

// 测试1: 获取最新列表（带分类）
echo "测试1: 获取最新列表（带分类）\n";
echo "URL: {$apiUrl}?ac=list\n\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl . '?ac=list&pagesize=5');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    echo "✓ 请求成功\n";
    echo "响应长度: " . strlen($response) . " 字节\n\n";

    // 解析XML
    $xml = simplexml_load_string($response);
    if ($xml) {
        echo "视频列表:\n";
        foreach ($xml->list->video as $video) {
            echo "  - ID: {$video->id}, 名称: {$video->name}, 类型: {$video->type}\n";
        }

        echo "\n分类列表:\n";
        foreach ($xml->class->ty as $category) {
            $attrs = $category->attributes();
            echo "  - ID: {$attrs['id']}, 名称: {$category}\n";
        }
    }
} else {
    echo "✗ 请求失败，HTTP状态码: {$httpCode}\n";
}

echo "\n";

// 测试2: 获取特定视频详情
echo "测试2: 获取特定视频详情\n";
echo "URL: {$apiUrl}?ac=videolist&ids=119184\n\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl . '?ac=videolist&ids=119184');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    echo "✓ 请求成功\n";

    $xml = simplexml_load_string($response);
    if ($xml && isset($xml->list->video)) {
        $video = $xml->list->video;
        echo "视频名称: {$video->name}\n";
        echo "视频类型: {$video->type}\n";
        echo "导演: {$video->director}\n";
        echo "主演: {$video->actor}\n";
        echo "年份: {$video->year}\n";
        echo "简介: {$video->des}\n";

        if (isset($video->dl->dd)) {
            echo "\n播放链接数量: " . substr_count((string)$video->dl->dd, '#') + 1 . "\n";
        }
    }
} else {
    echo "✗ 请求失败，HTTP状态码: {$httpCode}\n";
}

echo "\n";

// 测试3: 搜索功能
echo "测试3: 搜索功能\n";
echo "URL: {$apiUrl}?ac=videolist&wd=太平年\n\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl . '?ac=videolist&wd=太平年&pagesize=3');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    echo "✓ 请求成功\n";

    $xml = simplexml_load_string($response);
    if ($xml && isset($xml->list->video)) {
        echo "搜索结果:\n";
        foreach ($xml->list->video as $video) {
            echo "  - ID: {$video->id}, 名称: {$video->name}, 类型: {$video->type}\n";
        }
    }
} else {
    echo "✗ 请求失败，HTTP状态码: {$httpCode}\n";
}

echo "\n";
echo "=====================================\n";
echo "测试完成\n";
echo "=====================================\n";
