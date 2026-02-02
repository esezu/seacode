@echo off
chcp 65001 > nul
echo =====================================
echo M3U8视频播放器 - 快速安装脚本
echo =====================================
echo.

REM 检查PHP是否安装
php -v > nul 2>&1
if %errorlevel% neq 0 (
    echo 错误: 未检测到PHP，请先安装PHP 7.4或更高版本
    pause
    exit /b 1
)

echo PHP版本:
php -v
echo.

REM 检查Composer是否安装
composer --version > nul 2>&1
if %errorlevel% neq 0 (
    echo 错误: 未检测到Composer，请先安装Composer
    pause
    exit /b 1
)

echo Composer版本:
composer --version
echo.

REM 安装依赖
echo 正在安装依赖...
composer install

echo.
echo =====================================
echo 安装完成！
echo =====================================
echo.
echo 请配置Web服务器指向 public 目录
echo 然后访问: http://localhost/demo/public/
echo.
echo 详细说明请查看 README.md
pause
