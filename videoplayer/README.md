# Video Player with Fat-Free Framework (FF3)

- Purpose: 提供一个后端 API，聚合外部 XML API 的影视数据，暴露给前端的 M3U8 播放器和应用。
- Endpoints:
  - GET /api/vod?ac=videolist&...  -> 列表
  - GET /api/vod?ac=videolist&ids=... -> 详情
  - GET /api/vod/categories -> 分类列表
  - GET /api/vod/episodes?id=... -> 指定剧集的可用剧集/播放地址

运行方式（示例）:
- 安装依赖: 在 videoplayer 目录执行 `composer install`，确保 PHP 安装有 curl 支持。
- 启动开发服务器: 在 videoplayer 目录执行 `php -S localhost:8000 index.php`，或在根目录通过路由器映射。
- 你需要确保外部接口可访问，可能会有跨域或限速问题。

后续建议:
- 增加缓存层（APCu/Redis）来缓解外部接口压力。
- 增强错误处理与日志。
- 为前端提供更完善的查询参数校验和分页。 
