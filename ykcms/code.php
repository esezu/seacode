<?php
/**
 * 基于F3框架的影视资源聚合脚本
 * 所有功能整合到单个index.php文件中
 * 详细注释版本，用于学习F3框架的使用
 */

// 1. 引入F3框架
// 注意：这里使用的是相对路径，指向项目外部的fatfree-core-master目录
require __DIR__ . '/../fatfree-core-master/base.php';

// 2. 初始化F3实例
// Base::instance()是F3框架的核心方法，用于获取或创建框架实例
$f3 = \Base::instance();

// 3. 框架基础配置
// DEBUG: 设置调试级别，3表示最高级别的调试信息
$f3->set('DEBUG', 3);
// UI: 设置模板文件目录
$f3->set('UI', 'ui/');
// CACHE: 禁用缓存，确保每次请求都获取最新数据
$f3->set('CACHE', false);
// TEMP: 设置临时文件目录
$f3->set('TEMP', 'tmp/');
// ESCAPE: 禁用自动转义，以便直接输出HTML内容
$f3->set('ESCAPE', false);

// 4. 全局配置（使用F3配置管理）
// 站点基本信息
$f3->set('SITE_NAME', '影视资源');
$f3->set('SITE_DOMAIN', 'demo.test');
$f3->set('SITE_EMAIL', 'admin@admin.com');

// API配置，格式为：API名称#API地址
$f3->set('API_URL_1', '豪华资源#https://hhzyapi.com/api.php/provide/vod/at/xml');
$f3->set('API_URL_2', '无尽资源#https://api.wujinapi.me/api.php/provide/vod/from/wjm3u8/at/xml/');
$f3->set('API_URL_3', '红牛资源#https://www.hongniuzy2.com/api.php/provide/vod/at/xml/');
$f3->set('API_URL_4', '如意资源#https://cj.rycjapi.com/api.php/provide/vod/at/xml/');

// 视频解析器配置
$f3->set('VIDEO_PARSER', 'https://hhjiexi.com/play/?url=');
// 模板名称配置
$f3->set('TEMPLATE_NAME', 'default');
// 排序方式配置
$f3->set('SORT_DESC', 'yes');
// 时间限制配置
$f3->set('SHOW_TIME_LIMIT', '');

// 5. SEO模板配置
// 配置不同页面类型的SEO标题、关键词和描述
$f3->set('SEO_TITLE', [
    'list' => '{{@CURRENT_CATEGORY}} - {{@SITE_NAME}}',
    'search' => '{{@SEARCH_KEYWORD}}的搜索结果 - {{@SITE_NAME}}',
    'info' => '{{@VIDEO_NAME}} - {{@SITE_NAME}}'
]);
$f3->set('SEO_KEYWORDS', [
    'list' => '{{@CURRENT_CATEGORY}},最新电影,最新电视,最新综艺,最新动漫',
    'search' => '{{@SEARCH_KEYWORD}},最新电影,最新电视,最新综艺,最新动漫',
    'info' => '{{@VIDEO_NAME}},最新电影,最新电视,最新综艺,最新动漫'
]);
$f3->set('SEO_DESCRIPTION', [
    'list' => '{{@SITE_NAME}}提供最新的电影、电视、综艺、动漫在线播放服务',
    'search' => '{{@SITE_NAME}}提供{{@SEARCH_KEYWORD}}的在线播放服务',
    'info' => '{{@SITE_NAME}}提供{{@VIDEO_NAME}}的在线播放服务'
]);

// 6. 核心工具方法

/**
 * 模拟国内IP生成X-Forwarded-For请求头
 * 使用F3的\Web::request()发送GET请求（替换原生cURL所有逻辑）
 * 
 * @param string $url API请求地址
 * @return string|false API响应内容或失败时返回false
 */
function fetchAPI($url) {
    // 定义国内IP段范围
    $ip_long = array(
        array('607649792', '608174079'), // 36.102.0.0-36.103.255.255
        array('1038614528', '1039007743'), // 61.232.0.0-61.233.255.255
        array('1783627776', '1784676351'), // 106.80.0.0-106.95.255.255
        array('2035023872', '2035154943'), // 121.76.0.0-121.77.255.255
        array('2078801920', '2079064063'), // 123.232.0.0-123.235.255.255
        array('-1950089216', '-1948778497'), // 139.196.0.0-139.215.255.255
        array('-1425539072', '-1425014785'), // 171.8.0.0-171.15.255.255
        array('-1236271104', '-1235419137'), // 182.80.0.0-182.92.255.255
        array('-770113536', '-768606209'), // 210.25.0.0-210.47.255.255
        array('-569376768', '-564133889') // 222.16.0.0-222.95.255.255
    );
    
    // 随机选择一个IP段，并生成该段内的随机IP
    $rand_key = mt_rand(0, 9);
    $ips = long2ip(mt_rand($ip_long[$rand_key][0], $ip_long[$rand_key][1]));
    
    // 构建请求头，包括模拟的X-Forwarded-For和用户代理
    $headers = [
        'X-Forwarded-For: ' . $ips,
        'User-Agent: ' . ($_SERVER['HTTP_USER_AGENT'] ?? 'Mozilla/5.0 (Unknown; Linux x86_64) AppleWebKit/537.36')
    ];
    
    // 构建请求选项
    $options = [
        'method' => 'GET', // 请求方法
        'header' => $headers, // 请求头
        'encoding' => 'gzip', // 支持gzip压缩
        'timeout' => 30 // 请求超时时间
    ];
    
    try {
        // 获取F3的Web实例
        $web = \Web::instance();
        // 发送HTTP请求
        $response = $web->request($url, $options);
        // 返回响应体
        return $response['body'];
    } catch (Exception $e) {
        // 记录错误但不终止脚本执行
        error_log("API请求失败: " . $e->getMessage() . " - URL: " . $url);
        return false;
    }

}

/**
 * 处理API数据获取和缓存
 * 
 * @param string $sort 数据类型：category(分类)、info(详情)、search(搜索)、list(列表)
 * @param string $id 数据ID，如分类ID、视频ID、搜索关键词
 * @param int $page 页码
 * @return array 处理后的数据数组
 */
function handleDataCache($sort, $id, $page) {
    // 获取F3实例
    $f3 = \Base::instance();
    
    // 获取默认API配置
    $defaultApiConfig = $f3->get('API_URL_1');
    // 解析API配置，获取API名称和URL
    list($defaultApiName, $defaultApiUrl) = explode('#', $defaultApiConfig);
    // 获取当前API URL，如果没有设置则使用默认值
    $apiUrl = $f3->get('CURRENT_API_URL', $defaultApiUrl);
    // 获取时间限制配置
    $showTimeLimit = $f3->get('SHOW_TIME_LIMIT');
    // 获取排序方式配置
    $sortDesc = $f3->get('SORT_DESC');
    
    $actualPage = $page;
    $requestUrl = '';
    
    // 根据数据类型构建请求URL
    switch ($sort) {
        case 'category':
            // 分类列表请求
            $requestUrl = $apiUrl . '?ac=list';
            break;
        case 'info':
            // 视频详情请求
            $requestUrl = $apiUrl . '?ac=videolist&ids=' . $id . $showTimeLimit;
            break;
        case 'search':
            // 搜索结果请求
            $requestUrl = $apiUrl . '?ac=videolist&wd=' . urlencode($id) . '&pg=' . $page . $showTimeLimit;
            break;
        case 'list':
            // 视频列表请求
            // 实时获取分页信息
            $pageUrl = $apiUrl . '?ac=videolist&&t=' . $id . '&pg=' . $page . $showTimeLimit;
            $pageContent = fetchAPI($pageUrl);
            // 提取总页数
            preg_match('/pagecount="(\d+)"/', $pageContent, $matches);
            $pageCount = $matches[1] ?? 1;
            
            // 如果需要倒序排序，则计算实际页码
            if ($sortDesc === 'yes') {
                $actualPage = $pageCount - $page + 1;
            }
            // 构建实际请求URL
            $requestUrl = $apiUrl . '?ac=videolist&&t=' . $id . '&pg=' . $actualPage . $showTimeLimit;
            break;
    }
    
    // 发送API请求获取数据
    $remoteData = fetchAPI($requestUrl);
    // 如果数据为空，返回空数组
    if (empty($remoteData)) {
        return [];
    }
    
    // 分类数据特殊处理
    if ($sort === 'category') {
        // 解析XML数据
        $xml = simplexml_load_string($remoteData);
        // 如果解析失败，返回空数组
        if ($xml === false) {
            return [];
        }
        
        // 构建分类数组
        $categories = [];
        // 添加"最近更新"分类
        $categories[] = ['分类号' => '', '分类名' => '最近更新'];
        
        // 遍历XML中的分类数据
        if (isset($xml->class->ty)) {
            foreach ($xml->class->ty as $ty) {
                $categories[] = [
                    '分类号' => (string)$ty['id'],
                    '分类名' => (string)$ty
                ];
            }
        }
        
        return $categories;
    } else {
        // 清理数据，移除不必要的标签
        $cleanData = preg_replace('/<script(.*?)<\/script>|<span[^>]*?>|<\/span>|<p\s[^>]*?>|<p>|<\/p>/i', '', $remoteData);
        
        // 解析XML数据，使用LIBXML_NOCDATA选项保留CDATA内容
        $xml = simplexml_load_string($cleanData, 'SimpleXMLElement', LIBXML_NOCDATA);
        // 如果解析失败，返回空数组
        if ($xml === false) {
            return [];
        }
        
        // 将XML转换为数组
        $xmlArray = json_decode(json_encode($xml), true);
        
        // 清理数组，保留flag属性，删除其他不必要的@attributes和空对象
        $cleanJson = json_encode($xmlArray);
        $cleanJson = preg_replace('/(?:,\{"@attributes":\{(?!.*"flag")[^}]*\}\}|\{"@attributes":\{(?!.*"flag")[^}]*\}\},)/', '', $cleanJson);
        $cleanJson = preg_replace('/,"([^"]*?)":\{(?:\{"0":""\}|\{\})/', '', $cleanJson);
        $cleanJson = str_replace(['" "'], ['""'], $cleanJson);
        
        // 将清理后的JSON转换回数组并返回
        return json_decode($cleanJson, true);
    }
}

/**
 * 构建页面URL
 * 
 * @param string $sort 页面类型：list(列表)、search(搜索)、info(详情)
 * @param string $id 数据ID
 * @param int $page 页码
 * @return string 构建好的URL
 */
function buildPageUrl($sort, $id = '', $page = 1)
{
    // 构建URL参数
    $params = [];
    switch ($sort) {
        case 'list':
            $params['sort'] = $id;
            $params['page'] = $page;
            break;
        case 'search':
            $params['key'] = $id;
            $params['page'] = $page;
            break;
        case 'info':
            $params['info'] = $id;
            break;
    }
    
    // 获取F3实例
    $f3 = \Base::instance();
    // 构建完整URL并返回
    return $f3->get('BASE') . '?' . http_build_query($params);
}

// 7. 定义F3路由
// F3的route方法用于定义路由，这里定义了根路径的GET请求处理
$f3->route('GET /', function($f3) {
    // 获取请求参数
    $infoId = $_GET['info'] ?? null; // 视频详情ID
    $searchKey = $_GET['key'] ?? null; // 搜索关键词
    $sortId = $_GET['sort'] ?? null; // 分类ID
    $page = max(1, (int)($_GET['page'] ?? 1)); // 页码，确保至少为1
    $apiSelect = $_COOKIE['api_select'] ?? '1'; // API选择，从Cookie获取，默认为1

    // 确定页面类型
    $pageType = 'list'; // 默认页面类型为列表
    $uniqueId = $sortId ?? ''; // 唯一标识符
    
    // 根据请求参数确定页面类型
    if (!empty($infoId)) {
        $pageType = 'info'; // 详情页面
        $uniqueId = $infoId;
    } elseif (!empty($searchKey)) {
        $pageType = 'search'; // 搜索页面
        $uniqueId = urldecode($searchKey); // 解码搜索关键词
    }
    
    // 保存API选择到Cookie，有效期30天
    setcookie('api_select', $apiSelect, time() + 86400 * 30, '/');
    
    // 获取默认API配置
    $defaultApiConfig = $f3->get('API_URL_1');
    // 解析API配置
    list($defaultApiName, $defaultApiUrl) = explode('#', $defaultApiConfig);
    
    // 获取当前API配置
    $apiConfig = $f3->get('API_URL_' . $apiSelect);
    if ($apiConfig) {
        // 解析API配置
        list($apiName, $apiUrl) = explode('#', $apiConfig);
        // 设置当前API名称和URL
        $f3->set('CURRENT_API_NAME', $apiName);
        $f3->set('CURRENT_API_URL', $apiUrl);
    } else {
        // 如果没有找到API配置，使用默认值
        $f3->set('CURRENT_API_NAME', $defaultApiName);
        $f3->set('CURRENT_API_URL', $defaultApiUrl);
    }
    
    // 设置当前API选择
    $f3->set('CURRENT_API', $apiSelect);
    
    // 构建API列表
    $apiList = [];
    $i = 1;
    while (true) {
        // 获取API配置
        $config = $f3->get('API_URL_' . $i);
        // 如果没有更多API配置，退出循环
        if (!$config) {
            break;
        }
        // 解析API配置
        list($name, $url) = explode('#', $config);
        // 添加到API列表
        $apiList[] = ['id' => $i, 'name' => $name, 'url' => $url];
        $i++;
    }
    // 设置API列表
    $f3->set('API_LIST', $apiList);
    
    // 获取分类数据
    $categories = handleDataCache('category', '', 1);
    // 设置分类数据
    $f3->set('CATEGORIES', $categories);
    
    // 获取视频数据
    $videoData = handleDataCache($pageType, $uniqueId, $page);
    
    // 处理详情页面
    if ($pageType === 'info') {
        // 设置视频数据为空数组
        $f3->set('VIDEO_DATA', []);
        $videoInfo = [];
        
        // 安全处理视频信息数据结构
        if (isset($videoData['list']) && isset($videoData['list']['video'])) {
            $videoInfo = $videoData['list']['video'];
            // 确保videoInfo是数组
            if (!is_array($videoInfo)) {
                $videoInfo = [];
            }
        }
        
        // 保留原始的dl数据结构，供JS使用
        if (!isset($videoInfo['dl'])) {
            $videoInfo['dl'] = ['dd' => ''];
        }
        
        // 在PHP端处理播放源，优先使用m3u8的播放源
        $dlData = $videoInfo['dl']['dd'] ?? [];
        $selectedDd = null;
        
        if (is_array($dlData)) {
            foreach ($dlData as $ddItem) {
                if (is_string($ddItem)) {
                    // 检查是否包含m3u8
                    if (stripos($ddItem, 'm3u8') !== false) {
                        $selectedDd = $ddItem;
                        break;
                    }
                } elseif (is_array($ddItem) && isset($ddItem['@attributes']['flag'])) {
                    // 检查flag是否包含m3u8
                    if (stripos($ddItem['@attributes']['flag'], 'm3u8') !== false) {
                        $selectedDd = $ddItem;
                        break;
                    }
                }
            }
            // 如果没有找到m3u8，使用第一个播放源
            if ($selectedDd === null && !empty($dlData)) {
                $selectedDd = $dlData[0];
            }
        }
        
        // 使用选中的播放源
        if ($selectedDd !== null) {
            $videoInfo['dl']['dd'] = is_array($selectedDd) ? [$selectedDd] : $selectedDd;
        }
        
        // 生成JavaScript代码（压缩成一行）
        $videoParser = $f3->get('VIDEO_PARSER');
        $jsCode = "<script>var bflist=" . json_encode($videoInfo['dl']) . ";var jx='" . $videoParser . "';function bf(str){document.getElementById(\"iframe\").style.display=\"block\";document.getElementById(\"frame\").src=jx+str;}if(Array.isArray(bflist['dd'])){var viddz=bflist['dd'];}else{var viddz=new Array(bflist['dd']);}var bfmoban=document.getElementById(\"playlist\").innerHTML;document.getElementById(\"playlist\").innerHTML='';for(var i in viddz){viddz[i]=viddz[i].split(\"#\");for(var k=0;k<viddz[i].length;k++){viddz[i][k]=viddz[i][k].split(\"$\");if(viddz[i][k][2]==undefined){var zyname='yun';}else{var zyname=viddz[i][k][2];}if(k=='0'){document.getElementById(\"playlist\").innerHTML+=bfmoban.replace(/资源加载中/g,zyname);var jjmoban=document.getElementById(\"zylx\"+zyname).innerHTML;var bfnr='';}bfnr=bfnr+jjmoban.replace(/剧集加载中/g,viddz[i][k][0]).replace(/剧集地址加载中/g,viddz[i][k][1]);}document.getElementById(\"zylx\"+zyname).innerHTML=bfnr;}</script>";
        $f3->set('PLAYER_SCRIPT', $jsCode);

        // 设置视频信息
        $f3->set('VIDEO_INFO', $videoInfo);
    } else {
        // 处理列表和搜索页面
        $videoList = [];
        
        // 安全处理视频列表数据结构
        if (isset($videoData['list']) && isset($videoData['list']['video'])) {
            $videoList = $videoData['list']['video'];
            // 确保videoList是数组
            if (!is_array($videoList)) {
                $videoList = [];
            }
        }
        
        // 设置视频列表数据
        $f3->set('VIDEO_DATA', $videoList);
    }
    
    // 初始化分页数据
    $pagination = [
        'current' => $page, // 当前页码
        'prev' => 1, // 上一页
        'next' => 1, // 下一页
        'last' => 1, // 最后一页
        'first' => '' // 第一页
    ];
    
    // 从API响应中获取分页信息
    if (!empty($videoData['list']['@attributes']['pagecount'])) {
        $pageCount = (int)$videoData['list']['@attributes']['pagecount'];
        $pagination['last'] = $pageCount;
        $pagination['prev'] = max(1, $page - 1);
        $pagination['next'] = min($pageCount, $page + 1);
    }
    
    // 构建分页URL
    $pagination['firstUrl'] = buildPageUrl($pageType, $uniqueId, 1);
    $pagination['prevUrl'] = buildPageUrl($pageType, $uniqueId, $pagination['prev']);
    $pagination['nextUrl'] = buildPageUrl($pageType, $uniqueId, $pagination['next']);
    $pagination['lastUrl'] = buildPageUrl($pageType, $uniqueId, $pagination['last']);
    // 设置分页数据
    $f3->set('PAGINATION', $pagination);
    
    // 初始化SEO数据
    $seoData = [
        'CURRENT_CATEGORY' => '最近更新',
        'SEARCH_KEYWORD' => '',
        'VIDEO_NAME' => ''
    ];
    
    // 根据页面类型设置SEO数据
    if ($pageType === 'search') {
        $seoData['SEARCH_KEYWORD'] = $uniqueId;
    }
    
    if ($pageType === 'list') {
        // 查找当前分类名称
        foreach ($categories as $cate) {
            if ($cate['分类号'] == $uniqueId) {
                $seoData['CURRENT_CATEGORY'] = $cate['分类名'];
                break;
            }
        }
    }
    
    if ($pageType === 'info') {
        // 从视频信息中获取视频名称
        $videoInfo = $f3->get('VIDEO_INFO') ?? [];
        $seoData['VIDEO_NAME'] = $videoInfo['name'] ?? '';
    }
    
    // 设置SEO数据到F3配置
    foreach ($seoData as $key => $value) {
        $f3->set($key, $value);
    }
    
    // 设置视频解析器
    $f3->set('VIDEO_PARSER', $f3->get('VIDEO_PARSER'));
    // 设置基础URL
    $f3->set('BASE', $f3->get('SCHEME') . '://' . $f3->get('HOST') . ':' . $f3->get('PORT'));
    
    // 获取模板名称
    $templateName = $f3->get('TEMPLATE_NAME');
    // 获取UI路径
    $uiPath = $f3->get('UI');
    // 构建模板路径
    $templatePath = $uiPath . $templateName;
    // 设置模板路径
    $f3->set('TEMPLATE_PATH', $templatePath);
    
    // 根据页面类型设置要包含的模板
    if ($pageType === 'info') {
        $f3->set('TEMPLATE_TO_INCLUDE', $templateName . '/info.html');
    } elseif ($pageType === 'search') {
        $f3->set('TEMPLATE_TO_INCLUDE', $templateName . '/search.html');
    } else {
        $f3->set('TEMPLATE_TO_INCLUDE', $templateName . '/list.html');
    }
    
    // 渲染模板
    // F3的Template::instance()->render()方法用于渲染模板
    echo \Template::instance()->render($templateName . '/indexs.html');
});

// 8. 运行F3应用
// F3的run方法用于启动应用，处理请求
$f3->run();