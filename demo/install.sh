#!/bin/bash

echo "====================================="
echo "M3U8视频播放器 - 快速安装脚本"
echo "====================================="
echo ""

# 检查PHP是否安装
if ! command -v php &> /dev/null; then
    echo "错误: 未检测到PHP，请先安装PHP 7.4或更高版本"
    exit 1
fi

# 检查Composer是否安装
if ! command -v composer &> /dev/null; then
    echo "错误: 未检测到Composer，请先安装Composer"
    exit 1
fi

echo "PHP版本:"
php -v
echo ""

echo "Composer版本:"
composer --version
echo ""

# 安装依赖
echo "正在安装依赖..."
composer install

echo ""
echo "====================================="
echo "安装完成！"
echo "====================================="
echo ""
echo "请配置Web服务器指向 public 目录"
echo "然后访问: http://localhost/demo/public/"
echo ""
echo "详细说明请查看 README.md"
