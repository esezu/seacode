# M3U8视频播放器项目概览

## 项目简介

这是一个基于Fat-Free Framework 3.8+开发的M3U8视频播放器程序，提供完整的视频浏览、搜索和播放功能。

## 核心功能

### 1. 首页视频列表
- 显示最新更新的视频
- 响应式网格布局
- 视频卡片展示（封面、标题、类型、更新时间）
- 分页导航

### 2. 分类筛选
- 支持44种视频分类
- 分类按钮快速切换
- 当前分类高亮显示

### 3. 视频详情
- 完整的视频信息展示
- 演员阵容、导演信息
- 剧情简介
- 剧集列表
- 快速播放入口

### 4. 在线播放
- 集成M3U8播放器
- 支持多集切换
- 上一集/下一集快捷操作
- 全屏播放支持

### 5. 搜索功能
- 关键词搜索
- 搜索结果展示
- 搜索结果分页

## 技术架构

### 后端技术
- **框架**: Fat-Free Framework 3.8+
- **语言**: PHP 7.4+
- **扩展**: curl, SimpleXML, mbstring

### 前端技术
- **CSS框架**: Bootstrap 5.3.0
- **图标**: Bootstrap Icons 1.10.0
- **模板引擎**: Fat-Free Framework内置模板引擎

### 数据源
- **视频数据API**: https://hhzyapi.com/api.php/provide/vod/from/hhm3u8/at/xmlsea
- **播放器API**: https://hhjiexi.com/play/?url=

## 文件结构说明

```
demo/
├── app/                           # 应用目录
│   ├── Controllers/               # 控制器
│   │   └── VideoController.php    # 视频控制器（处理所有视频相关请求）
│   ├── Services/                  # 服务类
│   │   └── VideoApiService.php    # API服务类（处理API请求和XML解析）
│   ├── views/                     # 视图模板
│   │   ├── layout.html            # 基础布局（包含HTML头部、导航栏、页脚）
│   │   ├── home.html              # 首页模板（视频列表）
│   │   ├── detail.html            # 详情页模板（视频信息）
│   │   ├── player.html            # 播放器页模板（视频播放）
│   │   ├── search.html            # 搜索页模板（搜索结果）
│   │   └── error.html             # 错误页模板
│   └── routes.php                 # 路由配置（URL映射）
├── public/                        # 公共目录
│   └── index.php                  # 应用入口文件
├── composer.json                  # Composer配置文件
├── .htaccess                      # Apache重写规则
├── .gitignore                     # Git忽略文件
├── README.md                      # 项目说明文档
├── PROJECT_OVERVIEW.md            # 项目概览文档（本文件）
├── install.sh                     # Linux/Mac安装脚本
├── install.bat                    # Windows安装脚本
└── test_api.php                   # API测试脚本
```

## 核心类说明

### VideoController（控制器）
处理所有HTTP请求，协调视图和服务类。

**方法列表**:
- `index($f3)` - 首页，显示最新视频列表
- `category($f3)` - 分类页面，显示指定分类的视频
- `detail($f3)` - 视频详情页面
- `play($f3)` - 视频播放页面
- `search($f3)` - 搜索页面

### VideoApiService（服务类）
处理API请求和XML数据解析。

**方法列表**:
- `getVideoListWithCategories($page, $pageSize)` - 获取视频列表（带分类）
- `getVideoList($page, $pageSize, $typeId, $hours)` - 获取视频列表（详细信息）
- `getVideoDetail($id)` - 获取特定视频详情
- `searchVideos($keyword, $page, $pageSize)` - 搜索视频
- `getPlayerUrl($videoUrl)` - 获���播放器URL
- `fetchAndParseXml($url)` - 获取并解析XML数据
- `parseXmlResponse($xmlString)` - 解析XML响应
- `parseEpisodes($dd)` - 解析剧集信息

## 路由配置

| 路由 | 方法 | 控制器方法 | 说明 |
|------|------|------------|------|
| `/` | GET | `index()` | 首页 |
| `/category/@typeid` | GET | `category()` | 分类页面 |
| `/video/@id` | GET | `detail()` | 视频详情 |
| `/play/@id` | GET | `play()` | 播放第一集 |
| `/play/@id/@episode` | GET | `play()` | 播放指定集 |
| `/search` | GET | `search()` | 搜索页面 |

## 数据流程

### 1. 视频列表流程
```
用户访问首页
    ↓
VideoController->index()
    ↓
VideoApiService->getVideoListWithCategories()
    ↓
请求API: ?ac=list
    ↓
解析XML响应
    ↓
渲染home.html模板
    ↓
返回HTML给用户
```

### 2. 视频播放流程
```
用户点击播放
    ↓
VideoController->play()
    ↓
VideoApiService->getVideoDetail()
    ↓
请求API: ?ac=videolist&ids=视频ID
    ↓
解析XML，提取播放链接
    ↓
生成播放器URL
    ↓
渲染player.html模板
    ↓
返回HTML（含iframe播放器）
```

## API响应格式

### 视频列表响应（带分类）
```xml
<rss version="5.1">
  <list page="1" pagecount="4657" pagesize="20" recordcount="93128">
    <video>
      <last>2026-01-30 13:06:04</last>
      <id>117782</id>
      <tid>25</tid>
      <name><![CDATA[终究，与你相恋第二季]]></name>
      <type>日本动漫</type>
      <dt>hhm3u8</dt>
      <note><![CDATA[第4集]]></note>
    </video>
    <!-- 更多视频... -->
  </list>
  <class>
    <ty id="1">电视剧</ty>
    <ty id="2">电影</ty>
    <!-- 更多分类... -->
  </class>
</rss>
```

### 视频详情响应
```xml
<rss version="5.1">
  <list page="1" pagecount="1" pagesize="20" recordcount="1">
    <video>
      <last>2026-01-30 21:48:02</last>
      <id>119184</id>
      <tid>20</tid>
      <name><![CDATA[太平年]]></name>
      <type>内地剧</type>
      <pic>https://hhmage.com/cover/xxx.jpg</pic>
      <lang></lang>
      <area></area>
      <year>2026</year>
      <state>0</state>
      <note><![CDATA[第20集]]></note>
      <actor><![CDATA[白宇,周雨彤,朱亚文...]]></actor>
      <director><![CDATA[杨磊,陆贝珂]]></director>
      <dl>
        <dd flag="hhm3u8">
          <![CDATA[第1集$url$hhm3u8#第2集$url$hhm3u8...]]>
        </dd>
      </dl>
      <des><![CDATA[剧集聚焦钱弘俶...]]></des>
    </video>
  </list>
</rss>
```

## 前端特性

### 响应式设计
- 移动优先设计
- 断点: 576px, 768px, 992px, 1200px
- 网格系统自适应

### 深色主题
- 黑色背景 (#000000)
- 红色主色调 (#e50914)
- 卡片深灰色 (#1a1a1a)
- 导航栏深灰色 (#141414)

### 交互效果
- 卡片悬停上浮效果
- 按钮悬停变色
- 平滑过渡动画

## 性能优化

### 后端优化
- cURL请求超时控制（30秒）
- XML解析错误处理
- 分页减少数据传输

### 前端优化
- CDN加载Bootstrap资源
- 图片懒加载（可扩展）
- 最小化CSS/JS（可扩展）

## 安全考虑

- XSS防护（模板自动转义）
- SQL注入防护（无数据库操作）
- CSRF保护（可扩展）
- API请求超时控制

## 扩展建议

### 功能扩展
1. 用户收藏功能
2. 观看历史记录
3. 视频评论系统
4. 用户评分系统
5. 推荐算法

### 性能扩展
1. Redis缓存
2. 数据库缓存
3. CDN加速
4. 图片压缩

### 管理扩展
1. 后台管理系统
2. 数据统计面板
3. 用户管理
4. 内容审核

## 测试

运行API测试脚本：
```bash
php test_api.php
```

## 部署建议

### 生产环境
1. 关闭调试模式 (`DEBUG = 0`)
2. 启用HTTPS
3. 配置CDN
4. 启用缓存
5. 监控日志

### 服务器要求
- PHP 7.4+
- 内存: 128MB+
- 磁盘: 100MB+
- 带宽: 根据访问量调整

## 常见问题

**Q: 视频加载慢怎么办？**
A: 检查API服务器响应速度，考虑添加缓存机制。

**Q: 播放器无法播放？**
A: 确认M3U8链接有效，检查浏览器编解码器支持。

**Q: 如何自定义样式？**
A: 编辑 `app/views/layout.html` 中的 `<style>` 标签。

**Q: 如何更换API？**
A: 修改 `public/index.php` 中的 `API_URL` 和 `PLAYER_URL` 配置。

## 许可证

本项目仅供学习交流使用。

## 联系方式

如有问题或建议，请提交Issue。
