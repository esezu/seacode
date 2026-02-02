# M3U8视频播放器 - Fat-Free Framework

基于Fat-Free Framework开发的M3U8视频播放器程序，支持视频列表展示、分类筛选、搜索、详情查看和在线播放功能。

## 功能特性

- **首页展示**: 显示最新视频列表，支持分页
- **分类筛选**: 支持44种视频分类筛选（电视剧、电影、动漫、综艺等）
- **搜索功能**: 支持关键词搜索视频
- **视频详情**: 显示视频详细信息（演员、导演、剧情简介等）
- **在线播放**: 集成M3U8播放器，支持多集播放
- **响应式设计**: 适配PC和移动端设备
- **深色主题**: 现代化的深色UI设计

## 技术栈

- **后端框架**: Fat-Free Framework 3.8+
- **前端**: Bootstrap 5 + Bootstrap Icons
- **数据源**: hhzyapi.com XML API
- **播放器**: hhjiexi.com M3U8播放器接口

## 项目结构

```
demo/
├── app/
│   ├── Controllers/
│   │   └── VideoController.php      # 视频控制器
│   ├── Services/
│   │   └── VideoApiService.php      # API服务类
│   ├── views/
│   │   ├── layout.html              # 基础布局模板
│   │   ├── home.html                # 首页/分类页模板
│   │   ├── detail.html              # 视频详情页模板
│   │   ├── player.html              # 播放器页模板
│   │   ├── search.html              # 搜索页模板
│   │   └── error.html               # 错误页模板
│   └── routes.php                   # 路由配置
├── public/
│   └── index.php                    # 应用入口文件
├── vendor/                          # Composer依赖
├── .htaccess                        # Apache重写规则
├── composer.json                    # Composer配置
└── README.md                        # 项目说明文档
```

## 安装步骤

### 1. 环境要求

- PHP >= 7.4
- Apache/Nginx 服务器
- Composer（PHP包管理器）
- 启用扩展: curl, SimpleXML, mbstring

### 2. 安装依赖

```bash
# 进入项目目录
cd demo

# 安装Fat-Free Framework
composer install
```

### 3. 配置Web服务器

#### Apache配置

Apache服务器已经配置了`.htaccess`文件，确保：

1. 启用mod_rewrite模块
2. AllowOverride设置为All

#### Nginx配置

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/demo/public;

    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### 4. 访问应用

在浏览器中访问：

```
http://localhost/demo/public/
```

或配置虚拟主机后直接访问：

```
http://your-domain.com/
```

## 路由说明

| 路由 | 方法 | 说明 |
|------|------|------|
| `/` | GET | 首页 - 显示最新视频列表 |
| `/category/@typeid` | GET | 分类页面 - 显示指定分类的视频 |
| `/video/@id` | GET | 视频详情页 - 显示视频详细信息 |
| `/play/@id` | GET | 播放器页面 - 播放视频第一集 |
| `/play/@id/@episode` | GET | 播放器页面 - 播放指定集数 |
| `/search` | GET | 搜索页面 - 根据关键词搜索 |

## API配置

### 视频数据API

- **地址**: `https://hhzyapi.com/api.php/provide/vod/from/hhm3u8/at/xmlsea`
- **格式**: XML

### 播放器API

- **地址**: `https://hhjiexi.com/play/?url=`
- **格式**: 需要传入M3U8视频URL

## API参数说明

### 获取视频列表

```
?ac=list                    # 获取最新列表（带分类）
?ac=videolist               # 获取详细列表（不带分类）
?ac=videolist&t=分类ID      # 获取指定分类的视频
?ac=videolist&ids=视频ID    # 获取指定视频详情
?ac=videolist&wd=关键词     # 搜索视频
```

### 分页参数

```
pg=页数                     # 页码（从1开始）
pagesize=每页数量           # 每页显示数量（默认20）
```

### 其他参数

```
h=小时数                    # 最近多少小时内更新的视频
```

## 视频分类

| ID | 分类名称 | ID | 分类名称 |
|----|----------|----|----------|
| 1  | 电视剧 | 23 | 动画片 |
| 2  | 电影 | 24 | 中国动漫 |
| 3  | 欧美剧 | 25 | 日本动漫 |
| 4  | 香港剧 | 26 | 欧美动漫 |
| 5  | 韩剧 | 27 | 综艺 |
| 6  | 日剧 | 28 | 台湾剧 |
| 7  | 马泰剧 | 30 | 大陆综艺 |
| 8  | 伦理片 | 31 | 日韩综艺 |
| 9  | 动作片 | 32 | 港台综艺 |
| 10 | 爱情片 | 33 | 欧美综艺 |
| 11 | 喜剧片 | 34 | 灾难片 |
| 12 | 科幻片 | 35 | 悬疑片 |
| 13 | 恐怖片 | 36 | 犯罪片 |
| 14 | 剧情片 | 37 | 奇幻片 |
| 15 | 战争片 | 38 | 短剧 |
| 16 | 记录片 | 39 | 预告片 |
| 17 | 动漫 | 40 | 体育赛事 |
| 20 | 内地剧 | 41 | 足球 |
|    | | 42 | 篮球 |
|    | | 43 | 台球 |
|    | | 44 | 其他赛事 |

## 开发说明

### 添加新功能

1. **控制器**: 在`app/Controllers/`目录添加新的控制器类
2. **路由**: 在`app/routes.php`中添加新的路由配置
3. **视图**: 在`app/views/`目录添加新的模板文件

### 自定义样式

编辑`app/views/layout.html`中的`<style>`标签来自定义样式。

### 修改API配置

在`public/index.php`中修改API配置：

```php
$f3->set('API_URL', 'your-api-url');
$f3->set('PLAYER_URL', 'your-player-url');
```

## 常见问题

### 1. 页面显示404错误

- 检查`.htaccess`文件是否存在
- 确认Apache的mod_rewrite模块已启用
- 检查AllowOverride是否设置为All

### 2. 无法加载视频数据

- 检查服务器是否支持curl扩展
- 确认网络可以访问API地址
- 检查PHP错误日志

### 3. 播放器无法播放

- 确认播放器API地址正确
- 检查视频M3U8地址是否有效
- 某些浏览器可能需要H.264/HEVC编解码器支持

## 许可证

本项目仅供学习交流使用，视频资源来源于网络。

## 免责声明

本站视频内容来自第三方API，本站不存储任何视频文件，仅供学习研究使用。请勿用于商业用途。

## 更新日志

### v1.0.0 (2026-01-31)

- 初始版本发布
- 实现视频列表展示
- 实现分类筛选功能
- 实现搜索功能
- 实现视频详情页面
- 实现在线播放功能
- 实现分页功能
- 响应式UI设计

## 联系方式

如有问题或建议，请提交Issue。
