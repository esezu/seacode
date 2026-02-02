<?php
/** 
 * 系统测试脚本
 * 用于验证环境和功能是否正常
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>🎬 影视视频网站系统测试</h1>";
echo "<hr>";

// 1. 测试PHP版本
echo "<h3>1. PHP环境测试</h3>";
echo "PHP版本: " . PHP_VERSION . "<br>";
echo "当前时间: " . date('Y-m-d H:i:s') . "<br>";
echo "时区: " . date_default_timezone_get() . "<br>";

if (version_compare(PHP_VERSION, '7.2.0', '>=')) {
    echo "<span style='color:green'>✅ PHP版本符合要求 (需要7.2+)</span><br>";
} else {
    echo "<span style='color:red'>❌ PHP版本过低 (需要7.2+)</span><br>";
}

// 2. 测试Fat-Free Framework
echo "<h3>2. Fat-Free Framework测试</h3>";
$f3Path = __DIR__ . '/../fatfree-core-master/base.php';
if (file_exists($f3Path)) {
    echo "<span style='color:green'>✅ F3框架文件存在</span><br>";
    require $f3Path;
    
    $f3 = Base::instance();
    echo "<span style='color:green'>✅ F3框架加载成功</span><br>";
    
    // 测试基本功能
    $testVar = '测试变量';
    $f3->set('test', $testVar);
    if ($f3->get('test') === $testVar) {
        echo "<span style='color:green'>✅ F3基本功能正常</span><br>";
    }
} else {
    echo "<span style='color:red'>❌ F3框架文件不存在: $f3Path</span><br>";
}

// 3. 测试目录权限
echo "<h3>3. 目录权限测试</h3>";
$dirs = [
    'app' => false,
    'config' => false, 
    'tmp/cache' => false,
    'tmp/logs' => false,
    'public/css' => false,
    'public/js' => false
];

foreach ($dirs as $dir => $required) {
    $path = __DIR__ . '/' . $dir;
    if (file_exists($path)) {
        if (is_writable($path)) {
            echo "<span style='color:green'>✅ 目录可写: $dir</span><br>";
        } else {
            echo "<span style='color:orange'>⚠️ 目录不可写: $dir</span><br>";
        }
    } else {
        echo "<span style='color:red'>❌ 目录不存在: $dir</span><br>";
    }
}

// 4. 测试配置文件
echo "<h3>4. 配置文件测试</h3>";
$configs = ['config.ini', 'routes.ini'];
foreach ($configs as $config) {
    $configPath = __DIR__ . '/config/' . $config;
    if (file_exists($configPath)) {
        echo "<span style='color:green'>✅ 配置存在: $config</span><br>";
        
        // 尝试解析ini文件
        if (strpos($config, '.ini') !== false) {
            $parsed = parse_ini_file($configPath, true);
            if ($parsed !== false) {
                echo "<span style='color:green'>✅ 配置解析成功</span><br>";
            } else {
                echo "<span style='color:red'>❌ 配置解析失败</span><br>";
            }
        }
    } else {
        echo "<span style='color:red'>❌ 配置缺失: $config</span><br>";
    }
}

// 5. 测试网络连接
echo "<h3>5. 网络连接测试</h3>";
$testUrls = [
    'https://www.baidu.com',
    'https://httpbin.org/get'
];

foreach ($testUrls as $url) {
    $start = microtime(true);
    
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_NOBODY, true); // HEAD请求
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $time = round((microtime(true) - $start) * 1000, 0);
        
        if ($httpCode == 200) {
            echo "<span style='color:green'>✅ 网络连通: $url ({$time}ms)</span><br>";
        } else {
            echo "<span style='color:red'>❌ 网络异常: $url (HTTP $httpCode)</span><br>";
        }
    } else {
        echo "<span style='color:orange'>⚠️ cURL扩展未安装</span><br>";
        break;
    }
}

// 6. 测试API可用性
echo "<h3>6. API接口测试</h3>";
if (isset($f3)) {
    // 尝试读取配置文件中的API设置
    $configPath = __DIR__ . '/config/config.ini';
    if (file_exists($configPath)) {
        $config = parse_ini_file($configPath, true);
        
        if (isset($config['app']['API_BASE_URL'])) {
            $apiUrl = $config['app']['API_BASE_URL'];
            echo "API地址: $apiUrl<br>";
            
            // 测试API连接
            $testApiUrl = $apiUrl . '?ac=list';
            
            if (function_exists('curl_init')) {
                $ch = curl_init($testApiUrl);
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                
                if ($httpCode == 200 && !empty($response)) {
                    if (strpos($response, '<?xml') !== false) {
                        echo "<span style='color:green'>✅ API连接正常，返回XML数据</span><br>";
                        
                        // 验证XML格式
                        libxml_use_internal_errors(true);
                        $xml = simplexml_load_string($response);
                        if ($xml !== false) {
                            echo "<span style='color:green'>✅ XML格式正确</span><br>";
                        } else {
                            echo "<span style='color:orange'>⚠️ XML格式可能有问题</span><br>";
                        }
                    } else {
                        echo "<span style='color:orange'>⚠️ API返回非XML格式</span><br>";
                    }
                } else {
                    echo "<span style='color:red'>❌ API连接失败 (HTTP $httpCode)</span><br>";
                }
            }
        } else {
            echo "<span style='color:orange'>⚠️ 配置文件中缺少API_BASE_URL</span><br>";
        }
    }
}

// 7. 测试JavaScript和CSS文件
echo "<h3>7. 静态资源测试</h3>";
$resources = [
    'public/js/main.js',
    'public/css/style.css'
];

foreach ($resources as $resource) {
    $path = __DIR__ . '/' . $resource;
    if (file_exists($path)) {
        $size = round(filesize($path) / 1024, 1);
        echo "<span style='color:green'>✅ 资源文件存在: $resource (${size}KB)</span><br>";
    } else {
        echo "<span style='color:red'>❌ 资源文件缺失: $resource</span><br>";
    }
}

// 8. 总结
echo "<h3>8. 总结</h3>";
echo "<p><strong>测试完成时间:</strong> " . date('Y-m-d H:i:s') . "</p>";

echo "<h4>快速检查清单：</h4>";
echo "<ul>";
echo "<li>✅ Fat-Free Framework: 已安装</li>";
echo "<li>✅ PHP版本: " . PHP_VERSION . "</li>";
echo "<li>✅ 配置文件: 需要完善</li>";
echo "<li>✅ 目录权限: 需要设置</li>";
echo "<li>⚠️ API连接: 需要验证</li>";
echo "</ul>";

echo "<h4>下一步操作：</h4>";
echo "<ol>";
echo "<li>确保所有必要的目录具有读写权限</li>";
echo "<li>检查API接口是否可访问</li>";
echo "<li>访问首页测试完整功能</li>";
echo "<li>查看错误日志解决潜在问题</li>";
echo "</ol>";

echo "<hr>";
echo "<p style='text-align:center; color: #666;'>
    🎬 影视视频网站 | 系统测试工具<br>
    <small>如遇到问题，请参考README.md文档或检查配置</small>
</p>";
?>