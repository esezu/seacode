
@echo off
@echo off
setlocal enabledelayedexpansion

echo =========================================
echo   影视视频网站部署脚本
echo =========================================
echo.

echo [1/7] 检查环境...
php -v >nul 2>&1
if errorlevel 1 (
    echo ❌ 错误: PHP未安装或未添加到PATH环境变量
    pause
    exit /b 1
)

echo ✅ PHP环境检测通过

:: 检查PHP版本
for /f "tokens=2 delims==" %%G in ('php -r "echo PHP_VERSION;"') do set PHP_VERSION=%%G
for /f "tokens=1-3 delims=." %%a in ("%PHP_VERSION%") do (
    set MAJOR=%%a
    set MINOR=%%b
)

if %MAJOR%%MINOR% LSS 72 (
    echo ❌ 错误: PHP版本过低 (当前: %PHP_VERSION%，需要: 7.2+)
    pause
    exit /b 1
)

echo ✅ PHP版本.%PHP_VERSION%

:: [2] 检查目录权限
echo.
echo [2/7] 检查目录权限...

:: 关键目录列表
set DIRS=tmp\cache tmp\logs public\\js public\\css

for %%d in (%DIRS%) do (
    if not exist "%%d" (
        echo 创建目录: %%d
        mkdir "%%d" 2>nul
    )
    
    :: 检查写权限
    >"%%d\test.tmp" echo test
    if errorlevel 1 (
        echo ❌ 目录无写权限: %%d
    ) else (
        del "%%d\test.tmp"
        echo ✅ 目录可写: %%d
    )
)

:: [3] 检查核心文件
echo.
echo [3/7] 检查核心文件...

if not exist "..\fatfree-core-master\base.php" (
    echo ❌ 错误: Fat-Free Framework核心文件不存在
    echo     路径: ..\fatfree-core-master\base.php
    pause
    exit /b 1
)

echo ✅ F3框架文件存在

:: [4] 检查配置文件
echo.
echo [4/7] 检查配置文件...

if not exist "config\config.ini" (
    echo ⚠️  配置文件不存在，创建默认配置...
    type > "config\config.ini" << 'EOF'
; 影视视频网站配置
[f3]
DEBUG = 3
AUTOLOAD = app/;app/controllers/;app/models/
UI = app/views/
CACHE = TRUE
CACHE_TIMEOUT = 3600
LOGS = tmp/logs/
[fase]

[app]
API_BASE_URL = "https://hhzyapi.com/api.php/provide/vod/from/hhm3u8/at/xmlsea"
PLAYER_URL = "https://hhjiexi.com/play/?url="
ITEMS_PER_PAGE = 20
[fase]
EOF
    echo ✅ 已创建默认配置文件
) else (
    echo ✅ 配置文件存在
)

:: [5] 运行测试脚本
echo.
echo [5/7] 运行系统检测...
php test.php > test_result.html
if errorlevel 1 (
    echo ⚠️  测试执行有问题，建议检查
) else (
    echo ✅ 系统检测完成，结果保存到test_result.html
)

:: [6] 初始化日志
echo.
echo [6/7] 初始化日志系统...

if not exist "tmp\logs" mkdir "tmp\logs" 2>nul

:: 创建示例日志文件
echo [%date% %time%] 网站初始化部署 >> "tmp\\logs\\access_%date:~0,10%.log"
echo [%date% %time%] 系统部署完成 >> "tmp\\logs\\security.log"

echo ✅ 日志系统就绪

:: [7] 部署总结
echo.
echo [7/7] 部署总结...
echo.
echo =========================================
echo   部署结果
echo =========================================

if exist "index.php" (
    echo ✅ 主程序文件: 存在
) else (
    echo ❌ 主程序文件: 缺失
)

if exist "config\config.ini" (
    echo ✅ 配置文件: 存在
) else (
    echo ❌ 配置文件: 缺失
)
ifnt exist "app\\controllers" (
    echo ❌ 控制器目录: 缺失
) else strn!

echo ✅ 应用目录: 存在

if exist "app\\views" echo ✅ 视图目录: 存在
if exist "public\\js\\main.js" echo ✅ JS文件: 存在
if exist "public\\css\\style.css" echo ✅ CSS文件: 存在

echo.
echo =========================================
echo   使用指南
echo =========================================
echo.
echo 1. 访问 http://localhost/f3shipin/ 测试网站

echo 2. 查看 test_result.html 查看详细检测结果

echo 3. 编辑 config\\config.ini 调整配置

echo 4. 如需生产环境运行，请设置 DEBUG = 0

echo 5. 配置Web服务器重写规则 (参考README.md)

echo.
echo [完成!] 部署时间: %date% %time%

pause

