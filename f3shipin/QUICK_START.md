
# 🚀 快速开始指南

## 📝 一句话说明

一个基于PHP Fat-Free Framework的影视视频网站，集成M3U8播放器，支持分类浏览、搜索等功能。

## ⚡ 3分钟快速部署

### 第1步：检查环境
```bash
# 检查PHP版本 (需要7.2+)
php -v

# 检查必要扩展
php -m | grep -E "curl|simplexml|session"
```

### 第2步：设置权限
```bash
# Linux/Unix系统
chmod 755 tmp/cache tmp/logs
chown www-data:www-data tmp/cache tmp/logs

# Windows系统确保IIS/Apache用户有读写权限
```

### 第3步：配置Web服务器

**Apache: 确保启用mod_rewrite并使用.htaccess文件**

**Nginx: 参考nginx.conf配置**

### 第4步：访问网站
打开浏览器访问：
```
http://你的域名或IP/
```

## 🔧 配置调整

### 修改API源 (config/config.ini)
```ini
[app]
API_BASE_URL = "https://hhzyapi.com/api.php/provide/vod/from/hhm3u8/at/xmlsea"
PLAYER_URL = "https://hhjiexi.com/play/?url="
```

### 生产环境设置
```ini
[f3]
DEBUG = 0          ; 关闭调试
CACHE = TRUE       ; 开启缓存
```

## 🎯 主要功能

| 功能 | 路径 | 说明 |
|------|------|------|
| 首页 | `/` | 最新视频展示 |
| 视频列表 | `/list` | 所有视频 |
| 视频详情 | `/video/ID` | 视频播放页 |
| 分类浏览 | `/category/ID` | 按分类筛选 |
| 搜索 | `/search?q=关键词` | 搜索视频 |

## 🛠️ 故障排查

### 页面显示404
- 检查URL重写规则是否启用
- 确认.htaccess文件存在
- 检查F3核心文件路径

### 无法加载数据
- 检查API_URL配置
- 测试网络连接
- 查看tmp/logs/error.log

### 样式显示异常
- 检查CDN是否可用
- 确认public目录权限
- 清除浏览器缓存

## 📞 需要帮助？

1. 查看 test.php 运行系统诊断
2. 阅读 README.md 完整说明
3. 检查错误日志 tmp/logs/
4. 参考 PROJECT_SUMMARY.md 技术细节

## 🎉 即使是新手也能成功！

按照这个快速指南，95%的安装问题都能解决。

如果遇到任何问题，请查看详细文档寻求帮助。

---
**开始搭建您的影视网站吧！** 🎬
