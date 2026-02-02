# 影视视频程序

基于 Fat-Free Framework 开发的在线影视视频平台。

## 功能特性

- 视频列表浏览
- 分类筛选
- 视频搜索
- 视频详情查看
- M3U8视频播放
- 响应式设计
- 深色主题

## 项目结构

```
video-app/
├── classes/
│   └── VideoAPI.php          # API数据获取服务
├── lib/
│   └── fatfree/              # Fat-Free Framework核心
├── public/
│   ├── css/
│   │   └── style.css         # 样式文件
│   └── js/
│       └── main.js           # 前端脚本
├── templates/
│   ├── layout.html           # 主布局模板
│   ├── list-content.html     # 列表内容模板
│   ├── detail.html           # 详情页模板
│   ├── play.html             # 播放页模板
│   ├── search.html           # 搜索页模板
│   ├── category.html         # 分类页模板
│   ├── categories.html       # 分类栏模板
│   └── error.html            # 错误页模板
├── fatfree-core-master/      # 原始框架文件
└── index.php                 # 入口文件
```

## 安装步骤

### 环境要求

- PHP 7.4 或更高版本
- 启用 PHP 扩展：curl, libxml

### 安装

1. 确保已下载 `fatfree-core-master` 目录到项目根目录

2. 将 `fatfree-core-master` 的所有文件复制到 `lib/fatfree/` 目录

3. 配置 `index.php` 中的基础设置：
   ```php
   $f3->set('DEBUG', 3);           // 调试模式
   $f3->set('BASEURL', 'http://localhost:8000'); // 修改为你的域名
   ```

4. 启动 PHP 内置服务器：
   ```bash
   cd video-app
   php -S localhost:8000
   ```

5. 访问 `http://localhost:8000` 查看网站

## API 配置

程序使用第三方 API 获取视频数据：

- API地址：https://hhzyapi.com/api.php/provide/vod/from/hhm3u8/at/xml
- 播放器：https://hhjiexi.com/play/?url=

### API 参数说明

| 参数 | 说明 | 默认值 |
|------|------|--------|
| ac | 模式（list/videolist/detail） | list |
| t | 分类ID | 空 |
| pg | 页码 | 1 |
| wd | 搜索关键词 | 空 |
| ids | 视频ID（多个用逗号分隔） | 空 |
| h | 最近多少小时内 | 空 |

## 路由说明

| 路由 | 说明 |
|------|------|
| `/` | 首页 - 最新视频列表 |
| `/category` | 全部分类 |
| `/type/{id}` | 按分类浏览 |
| `/detail/{id}` | 视频详情 |
| `/play/{id}` | 视频播放 |
| `/search` | 视频搜索 |
| `/public/{filename}` | 静态资源 |

## 模板语法

程序使用 Fat-Free Framework 的模板语法：

- 变量输出：`{{ @variable }}`
- 条件判断：`<check if="condition">...</check>`
- 循环：`<repeat group="@array" value="@item">...</repeat>`
- 包含：`<include href="template.html"/>`

## 数据格式

### 视频列表数据

```xml
<list page="1" pagecount="100" pagesize="20" recordcount="2000">
    <video>
        <id>12345</id>
        <tid>1</tid>
        <name><![CDATA[视频名称]]></name>
        <type>视频类型</type>
        <last>2026-01-30 12:00:00</last>
        <note><![CDATA[第1集]]></note>
    </video>
</list>
```

### 视频详情数据

```xml
<video>
    <id>12345</id>
    <name><![CDATA[视频名称]]></name>
    <pic>封面图片URL</pic>
    <actor><![CDATA[演员列表]]></actor>
    <director><![CDATA[导演]]></director>
    <des><![CDATA[剧情简介]]></des>
    <dl>
        <dd flag="hhm3u8">
            <![CDATA[第1集$播放URL$hhm3u8#第2集$播放URL$hhm3u8]]>
        </dd>
    </dl>
</video>
```

## 自定义配置

### 修改站点名称和描述

在 `index.php` 中修改：

```php
$f3->set('site_name', '你的站点名称');
$f3->set('site_description', '你的站点描述');
```

### 修改主题颜色

编辑 `public/css/style.css` 文件，修改相关的颜色变量：

```css
/* 主色调 */
--primary-color: #58a6ff;
/* 成功色 */
--success-color: #238636;
/* 错误色 */
--error-color: #f85149;
```

## 注意事项

1. 确保服务器允许跨域请求
2. 视频播放器需要支持 M3U8 格式
3. API 数据来源于第三方，请确保合法使用
4. 建议在生产环境关闭调试模式：`$f3->set('DEBUG', 0);`

## 浏览器支持

- Chrome (最新版本)
- Firefox (最新版本)
- Safari (最新版本)
- Edge (最新版本)
- 移动端浏览器

## 许可证

本项目仅供学习交流使用。

## 技术支持

如有问题，请检查：
1. PHP 版本是否符合要求
2. 必要扩展是否启用
3. 网络连接是否正常
4. API 服务是否可用
