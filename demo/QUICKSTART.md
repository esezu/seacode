# M3U8视频播放器 - 快速启动指南

## 🚀 5分钟快速开始

### 步骤1: 安装依赖

**Windows用户:**
```bash
install.bat
```

**Linux/Mac用户:**
```bash
chmod +x install.sh
./install.sh
```

**手动安装:**
```bash
composer install
```

### 步骤2: 配置Web服务器

#### 使用PHP内置服务器（开发环境）

```bash
php -S localhost:8000 -t public
```

然后访问: http://localhost:8000

#### 使用Apache服务器

确保Apache已启用mod_rewrite，然后指向`public`目录。

#### 使用Nginx服务器

参考README.md中的Nginx配置。

### 步骤3: 测试API

```bash
php test_api.php
```

确认API连接正常。

### 步骤4: 访问应用

打开浏览器访问: http://localhost:8000

## 📱 功能演示

### 1. 浏览视频
- 首页显示最新视频列表
- 点击视频卡片查看详情

### 2. 分类筛选
- 点击分类按钮筛选视频
- 支持电视剧、电影、动漫、综艺等44种分类

### 3. 搜索视频
- 在顶部搜索框输入关键词
- 点击搜索按钮查看结果

### 4. 播放视频
- 进入详情页点击"立即播放"
- 选择集数进行播放
- 使用上一集/下一集快捷切换

## 🎨 界面预览

### 首页
- 深色主题设计
- 响应式网格布局
- 视频卡片悬停效果

### 详情页
- 视频封面展示
- 详细信息列表
- 剧集选择器

### 播放器
- 全屏播放支持
- 剧集快速切换
- 播放信息展示

## 🔧 自定义配置

### 修改API地址

编辑 `public/index.php`:
```php
$f3->set('API_URL', 'your-api-url');
$f3->set('PLAYER_URL', 'your-player-url');
```

### 修改样式

编辑 `app/views/layout.html` 中的 `<style>` 标签。

### 修改每页显示数量

编辑 `app/Controllers/VideoController.php`:
```php
$result = $this->apiService->getVideoList($page, 20, $typeId);
// 将20改为你想要的数值
```

## 📚 更多信息

- 详细文档: [README.md](README.md)
- 项目概览: [PROJECT_OVERVIEW.md](PROJECT_OVERVIEW.md)

## ❓ 常见问题

### Q: 页面显示404？
A: 检查`.htaccess`文件是否存在，确认mod_rewrite已启用。

### Q: 无法加载视频？
A: 运行`php test_api.php`测试API连接，检查网络设置。

### Q: 播放器无法播放？
A: 确认浏览器支持H.264/HEVC编解码器，尝试Chrome或Firefox。

## 🎯 下一步

1. 部署到生产环境
2. 添加用户系统
3. 实现收藏功能
4. 添加评论系统
5. 优化性能

## 💡 提示

- 开发环境使用PHP内置服务器
- 生产环境建议使用Apache或Nginx
- 定期更新依赖包
- 关注API服务稳定性

祝使用愉快！🎉
