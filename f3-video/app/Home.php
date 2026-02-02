<?php
/**
 * 首页控制器
 */
class Home extends Controller {
    public function index($f3) {
        // 初始化响应数据，避免未定义
        $responseData = [
            'code' => 0,
            'msg' => 'success',
            'data' => []
        ];

        try {
            $url = $this->apiUrl;
            // 1. 获取XML字符串
            $xmlString = $this->getHtml($url);
            if ($xmlString === false) {
                throw new \Exception('接口请求失败');
            }
            if (empty($xmlString)) {
                throw new \Exception('接口返回空数据');
            }

            // 2. 解析XML为数组
            $xmlArray = $this->parseXml($xmlString);
            if ($xmlArray === false) {
                throw new \Exception('XML解析失败');
            }

            $responseData['data'] = $xmlArray;

        } catch (\Exception $e) {
            $this->f3->log('Home/index异常：' . $e->getMessage(), 'ERROR');
            $responseData['code'] = -1;
            $responseData['msg'] = $e->getMessage();
        }

        // 3. 输出JSON格式数据（替代echo数组）
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($responseData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    public function indexs($f3) {
        $apiUrl = 'https://hhzyapi.com/api.php/provide/vod/from/hhm3u8/at/xmlsea?ac=videolist';
        $xmlString = $this->fetch($apiUrl);
        // $xmlArray = $this->fenleiXml($xmlString);
        $xmlArray = $this->fenleiXml($xmlString);
        //把数据转json
        // $json = json_encode($xmlArray, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        // 调试信息：打印获取到的数据格式
        // echo $json;
        print_r($xmlArray);

        exit;
    }

    // 测试方法
    public function test($f3) {

        $apiUrl = 'https://hhzyapi.com/api.php/provide/vod/from/hhm3u8/at/xmlsea?ac=list';
        $xml_response = $this->fenleiXml($apiUrl);

        echo $xml_response;
        // print_r($xml_response);
    }

    public function curl($f3) {
        $apiUrl = 'https://hhzyapi.com/api.php/provide/vod/from/hhm3u8/at/xmlsea?ac=videolist';
        $result = $this->web->request($apiUrl);
        if ($result['error']) {
            throw new \Exception('API请求失败: ' . $result['error']);
        }
        $results = $result['body'];

        $xml = simplexml_load_string($results, 'SimpleXMLElement', LIBXML_NOCDATA);
        if ($xml === false) {
            throw new \Exception('XML解析失败');
        }

        echo json_encode($xml, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    public function cs($f3) {
        // 1. 定义目标API地址
        $apiUrl = 'https://hhzyapi.com/api.php/provide/vod/from/hhm3u8/at/xmlsea?ac=videolist';

        // 2. 调用本类的fetchAPI方法，获取纯HTTP响应数组（无解析）
        $response = $this->fetchAPI($apiUrl);

        // 3. 处理XML数据并打印到页面
        // 先设置页面编码，防止中文乱码（关键）
        header('Content-Type: text/html; charset=utf-8');
        echo "<h3>API数据打印（调试用）</h3>";
        
        // 第一步：判断响应是否有效（是数组且包含非空body）
        if (is_array($response) && !empty($response['body'])) {
            // 提取API返回的XML原始字符串
            $xmlRaw = $response['body'];
            
            // 可选：gzip解码（若body是压缩乱码，需开启zlib扩展）
            if (function_exists('gzdecode') && substr($xmlRaw, 0, 2) === "\x1f\x8b") {
                $decodedXml = gzdecode($xmlRaw);
                $xmlRaw = $decodedXml ?: $xmlRaw;
            }

            // 第二步：解析XML字符串为SimpleXMLElement对象
            $xmlObj = simplexml_load_string($xmlRaw, 'SimpleXMLElement', LIBXML_NOERROR | LIBXML_NOWARNING);
            
            // 第三步：判断XML解析是否成功
            if ($xmlObj instanceof \SimpleXMLElement) {
                echo "<p><strong>✅ XML解析成功，数据结构如下：</strong></p>";
                
                // 方式1：格式化打印XML对象（清晰展示节点结构）
                echo "<pre>";
                var_dump($xmlObj);
                echo "</pre>";

                // 方式2：打印完整的XML原始字符串（还原API返回格式）
                echo "<p><strong>📄 完整XML原始字符串：</strong></p>";
                echo "<pre>";
                echo htmlspecialchars($xmlObj->asXML());
                echo "</pre>";

                // 方式3：提取核心节点示例（适配实际XML结构：分页信息+首个影视）
                echo "<p><strong>🔍 提取核心节点示例（适配实际XML结构）：</strong></p>";
                echo "<pre>";
                // 提取list节点的分页属性（强制转换为数值，解决非数值错误）
                $page = intval($xmlObj->list['page'] ?? 0);
                $pagecount = intval($xmlObj->list['pagecount'] ?? 0);
                $pagesize = intval($xmlObj->list['pagesize'] ?? 0); // 关键：intval强制转为数值
                $recordcount = intval($xmlObj->list['recordcount'] ?? 0);
                echo "当前页码：{$page}\n";
                echo "总页数：{$pagecount}\n";
                echo "每页条数：{$pagesize}\n";
                echo "总记录数：{$recordcount}\n\n";

                // 提取第一个影视的核心信息（处理CDATA内容，强制转字符串，补充默认值）
                $firstVideo = $xmlObj->list->video[0] ?? null;
                if (!empty($firstVideo)) {
                    $videoId = (string)($firstVideo->id ?? '');
                    $videoName = (string)($firstVideo->name ?? '未知名称'); // 获取CDATA中的影视名称
                    $videoType = (string)($firstVideo->type ?? '未知类型');
                    $videoLang = (string)($firstVideo->lang ?? '未知语言');
                    $videoPic = (string)($firstVideo->pic ?? '无封面地址');
                    echo "首个影视详情：\n";
                    echo "  - ID：{$videoId}\n";
                    echo "  - 名称：{$videoName}\n";
                    echo "  - 类型：{$videoType}\n";
                    echo "  - 语言：{$videoLang}\n";
                    echo "  - 封面地址：{$videoPic}\n";
                } else {
                    echo "首个影视详情：无有效影视数据\n";
                }
                echo "</pre>";

                // 方式4：遍历所有影视列表，打印简要信息（修复遍历空白+非数值错误）
                echo "<p><strong>📋 遍历所有影视简要信息（共{$pagesize}条）：</strong></p>";
                echo "<pre>";
                // 容错处理：先判断video节点是否存在，再转换为数组确保可遍历
                $videoList = $xmlObj->list->video ?? [];
                if (!empty($videoList)) {
                    // 遍历数组，强制转换索引为数值，避免非数值错误
                    foreach ($videoList as $index => $video) {
                        $indexNum = intval($index); // 关键：将索引转为纯数值
                        $serialNum = $indexNum + 1; // 序号从1开始
                        // 所有节点补充默认值，避免空值报错
                        $videoId = (string)($video->id ?? '');
                        $videoName = (string)($video->name ?? '未知名称');
                        $videoType = (string)($video->type ?? '未知类型');
                        $videoLastUpdate = (string)($video->last ?? '未知更新时间');
                        
                        // 打印每条影视信息，避免换行符导致页面格式错乱
                        echo "{$serialNum}. ID：{$videoId} | 名称：{$videoName} | 类型：{$videoType} | 最后更新：{$videoLastUpdate}\n";
                    }
                } else {
                    echo "暂无可用的影视列表数据\n";
                }
                echo "</pre>";

            } else {
                // XML解析失败（body非合法XML）
                echo "<p><strong>❌ XML格式非法，解析失败</strong></p>";
                echo "<p><strong>📄 原始响应体：</strong></p>";
                echo "<pre>";
                echo htmlspecialchars($xmlRaw);
                echo "</pre>";
            }
        } else {
            // 响应无效（请求失败/无有效body）
            $errorMsg = $f3->get('errorMsg') ?? 'API请求失败或未获取到有效响应体';
            echo "<p><strong>❌ {$errorMsg}</strong></p>";
            echo "<p>请查看PHP错误日志，确认API是否可访问</p>";
        }

        // 4. 注释掉模板渲染（无需模板，直接打印到页面）
        // echo \Template::instance()->render('home/cs.htm');
    }
}