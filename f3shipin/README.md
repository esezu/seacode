
# 影视视频网站

基于Fat-Free Framework（F3）开发的影视视频网站，支持M3U8视频播放器，提供视频列表、详情、分类、搜索等功能。

## 🚀 概述

这是一个简洁安全的PHP影视视频网站程序，采用Fat-Free Framework 3.9最新版本开发，具有以下特点：

- ✅ 响应式设计，支持手机和电脑
- ✅ M3U8视频播放器集成  
- ✅ XML数据API接口对接
- ✅ 分类浏览和搜索功能
- ✅ 安全的防护机制
- ✅ SEO优化

## 📋 系统要求

- PHP 7.2+ （推荐 7.4+）
- Web服务器（Apache/Nginx/IIS）
- 支持URL重写
- 允许文件读写权限

## 🗂️ 目录结构

```
f3shipin/
├── index.php              # 入口文件
├── config/
│   ├── config.ini        # 主配置文件
│   └── routes.ini        # 路由配置
├── app/
│   ├── controllers/
│   │   ├── BaseController.php
│   │   └── VideoController.php
│   ├── models/
│   │   └── VideoModel.php
│   └── views/
│       ├── layout.htm
│       ├── index.htm
│       ├── video_list.htm
│       ├── video_detail.htm
│       ├── search.htm
│       ├── category.htm
│       ├── pagination.htm
│       └── error.htm
├── public/
│   ├── js/
│   │   └── main.js
│   ├── css/
│   │   └── style.css
│   └── images/
├── tmp/
│   ├── cache/
│   └── logs/
└── README.md
```

## 🔧 安装步骤

### 1. 环境准备

确保服务器环境满足要求，并配置URL重写：

**Apache (.htaccess)**
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^(.*)$ index.php [L,QSA]
```

**Nginx**
```nginx
location / {
    try_files $uri $uri/ /index.php?$args;
}
```

### 2. 权限设置

```bash
# 创建缓存和日志目录（如果不存在）
mkdir -p tmp/cache tmp/logs

# 设置权限
chmod 755 tmp/cache tmp/logs
chown www-data:www-data tmp/cache tmp/logs  # 根据实际用户调整
```

### 3. 配置文件

配置文件 `config/config.ini` 内容如下，已包含默认设置：

```ini
[f3]
DEBUG = 3
AUTOLOAD = app/;app/controllers/;app/models/
UI = app/views/
CACHE = TRUE
CACHE_TIMEOUT = 3600
LOGS = tmp/logs/

[app]
API_BASE_URL = "https://hhzyapi.com/api.php/provide/vod/from/hhm3u8/at/xmlsea"
PLAYER_URL = "https://hhjiexi.com/play/?url="
ITEMS_PER_PAGE = 20
```

**生产环境建议：**
```ini
DEBUG = 0  ; 关闭调试模式
CACHE = TRUE  ; 开启缓存
```

### 4. 核心文件

确保已下载 Fat-Free Framework 最新版：
- 主框架文件：`fatfree-core-master/base.php`
- 路径：`d:\CodeBuddy\fatfree-core-master\base.php`

## 🌐 API接口说明

### 视频API调用参数

#### 1. 视频列表
- **URL**: `API_BASE_URL?ac=videolist`
- **参数**:
  - `pg`: 页码（默认1）
  - `t`: 分类ID（可选）
  - `wd`: 搜索关键词（可选）
  - `h`: 最近N小时内（可选）

#### 2. 视频详情 
- **URL**: `API_BASE_URL?ac=videolist&ids=视频ID`

#### 3. 分类列表
- **URL**: `API_BASE_URL?ac=list`
- 返回所有分类ID和名称

#### 4. 分类视频
- **URL**: `API_BASE_URL?ac=videolist&t=分类ID`

### M3U8播放器
- **播放器URL**: `PLAYER_URL + 编码后的M3U8地址`
- **主要接口**: `https://hhjiexi.com/play/?url=`
- 传递M3U8地址进行播放

## 🎨 功能介绍

### 主要页面

1. **首页** (`/`)
   - 最新视频展示
   - 快速分类导航
   - 响应式轮播

2. **视频列表** (`/list`)
   - 分类筛选
   - 网格/列表视图
   - 分页导航

3. **视频详情** (`/video/ID`)
   - 视频信息展示
   - 播放列表
   - M3U8播放器集成
   - 分享功能

4. **分类浏览** (`/category/ID`)
   - 特定分类视频
   - 分类快速切换

5. **搜索** (`/search`)
   - 关键词搜索
   - 热词推荐
   - 搜索结果高亮

### API接口

- `GET /api/videos`: 视频列表API
- `GET /api/video`: 视频详情API  
- `GET /api/categories`: 分类列表API

所有API均返回JSON格式数据。

## 🔒 安全性说明

### 已实现的安全机制

1. **输入验证**
   - 搜索关键词长度限制（2-50字符）
   - 页码数值验证
   - 视频ID整数验证

2. **输出防护**
   - XML实体转义
   - HTML特殊字符转义
   - XSS防护

3. **访问控制**
   - 防止点击劫持
   - CSRF防护准备
   - AJAX请求识别

4. **错误处理**
   - 生产环境错误日志记录
   - 用户友好错误提示
   - 异常捕获处理

### 推荐配置

**生产环境设置：**

1. 在 `config.ini` 中设置 `DEBUG = 0`
2. 禁用PHP错误显示：
   ```ini
   display_errors = Off
   log_errors = On
   error_log = /path/to/php_errors.log
   ```
3. 设置正确的文件权限
4. 定期备份重要数据

## 🚀 部署指南

### 1. 开发环境

目录结构确认：

```
d:\CodeBuddy\f3shipin\
├── index.php
├── fatfree-core-master/  # F3框架
│   └── base.php
└── config/
    ├── config.ini
    └── routes.ini
```

### 2. 生产环境部署

1. **上传文件** 到服务器
2. **配置Web服务器** URL重写规则
3. **设置权限** 确保缓存和日志目录可写
4. **修改配置** 更新数据库和生产参数
5. **测试功能** 确保所有功能正常

### 3. 性能优化

- 启用OPcache (\(PHP)
- 配置Nginx/Apache缓存
- 启用应用程序缓存
- 考虑使用CDN

## 🛠️ 常见问题

### 1. 页面显示错误

**检查**: 
- PHP版本是否 >= 7.2
- Fat-Free Framework是否正确引入
- 配置文件语法是否正确
- 文件权限是否设置

### 2. API无法获取数据

**解决方法**:
- 检查 `API_BASE_URL` 是否正确
- 确认服务器允许外部HTTP请求
- 查看 `tmp/logs/` 中的错误日志
- 确认API服务是否可达

### 3. 播放器无法工作

**排查**:
- 确认 M3U8 链接是否有效
- 检查 `PLAYER_URL` 配置
- 浏览器是否支持HTML5视频
- 跨域问题（CORS）

### 4. 样式显示异常

**检查**:
- CDN网络是否通畅
- CSS文件路径是否正确  
- 浏览器缓存问题

## 📈 性能监控

### 内置监控
- 访问日志（tmp/logs/）
- 调试模式性能日志
- 请求执行时间

### 外部分析
- Google Analytics集成支持
- SEO元数据
- 页面加载监控

## 🔄 更新维护

### 常规维护

1. **定期清理** 临时文件和缓存
2. **监控** API接口稳定性  
3. **备份** 配置和重要数据
4. **更新** 安全补丁和框架版本

### 升级步骤

1. 备份当前配置和数据
2. 下载最新 Fat-Free Framework
3. 替换核心文件
4. 测试所有功能
5. 监控错误日志

## 📄 许可说明

本项目仅供学习交流使用，遵守相关法律法规。
- Fat-Free Framework: GPL v3.0
- 第三方库：遵循各自许可协议

## 📞 支持与反馈

如遇问题或需要帮助：

1. 查看本README的完整指南
2. 检查错误日志 `tmp/logs/`
3. 确认配置文件正确性
4. 验证服务器环境要求

---

**祝您使用愉快！** 🎬

