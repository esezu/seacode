<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>影视资源首页 - <?= ($site['name']) ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Microsoft YaHei", sans-serif;
        }
        .container {
            width: 1200px;
            margin: 20px auto;
        }
        .page-title {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
        }
        .video-list {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 40px;
        }
        .video-item {
            border: 1px solid #eee;
            border-radius: 8px;
            padding: 15px;
            transition: box-shadow 0.3s;
        }
        .video-item:hover {
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .video-pic {
            width: 100%;
            height: 250px;
            object-fit: cover;
            border-radius: 4px;
            margin-bottom: 10px;
        }
        .video-title {
            font-size: 16px;
            color: #333;
            margin-bottom: 8px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .video-meta {
            font-size: 12px;
            color: #666;
            margin-bottom: 10px;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        .video-tag {
            background-color: #e9f5ff;
            color: #007bff;
            padding: 2px 6px;
            border-radius: 4px;
        }
        .video-desc {
            font-size: 12px;
            color: #666;
            margin-bottom: 10px;
            height: 40px;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }
        .video-link {
            display: inline-block;
            padding: 6px 12px;
            background-color: #007bff;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
            font-size: 12px;
            cursor: pointer;
        }
        .video-link:hover {
            background-color: #0056b3;
        }
        .video-link.disabled {
            background-color: #6c757d;
            cursor: not-allowed;
            pointer-events: none;
        }
        .empty-tip {
            text-align: center;
            color: #999;
            font-size: 18px;
            padding: 50px 0;
        }

        /* 弹窗样式 */
        .play-modal-mask {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            z-index: 9998;
            display: none;
        }
        .play-modal {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 90%;
            max-width: 1200px;
            background-color: #000;
            border-radius: 8px;
            z-index: 9999;
            display: none;
            overflow: hidden;
        }
        .modal-header {
            padding: 12px 20px;
            background-color: #222;
            color: #fff;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .modal-title {
            font-size: 16px;
            font-weight: normal;
        }
        .modal-close {
            color: #fff;
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            width: 30px;
            height: 30px;
            line-height: 30px;
            text-align: center;
            padding: 0;
        }
        /* 集数选择栏样式 */
        .modal-episode-select {
            padding: 10px 20px;
            background-color: #333;
            overflow-x: auto;
            white-space: nowrap;
        }
        .episode-btn {
            display: inline-block;
            padding: 6px 12px;
            margin-right: 8px;
            background-color: #444;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
        }
        .episode-btn.active {
            background-color: #007bff;
        }
        .episode-btn:hover {
            background-color: #555;
        }
        .modal-body {
            padding: 0;
            margin: 0;
        }
        .modal-iframe {
            width: 100%;
            height: 600px;
            border: none;
        }
        @media (max-width: 768px) {
            .modal-iframe {
                height: 300px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="page-title"><?= ($site['name']) ?> - 最新影视资源</h1>

        <?php if (!empty($videos)): ?>
            
                <div class="video-list">
                    <?php $ctr=0; foreach (($videos?:[]) as $video): $ctr++; ?>
                        <div class="video-item <?= ($ctr%2=='0'?'even-item':'odd-item') ?>">
                            <?php if (!empty($video['pic'])): ?>
                                <img class="video-pic" src="<?= ($video['pic']) ?>" alt="<?= ($video['name']) ?>" onerror="this.src='https://placeholder.pics/svg/300x250/暂无封面/No Cover'">
                                <?php else: ?>
                                    <img class="video-pic" src="https://placeholder.pics/svg/300x250/暂无封面/No Cover" alt="<?= ($video['name']) ?>">
                                
                            <?php endif; ?>

                            <h3 class="video-title"><?= ($video['name']) ?></h3>
                            <div class="video-meta">
                                <span class="video-tag"><?= ($video['type'] ?: '未知类型') ?></span>
                                <span class="video-tag"><?= ($video['year']) ?></span>
                                <span class="video-tag"><?= ($video['area'] ?: '未知地区') ?></span>
                                <span class="video-tag"><?= ($video['note']) ?></span>
                            </div>
                            <p class="video-desc"><?= ($video['des'] ?: '暂无简介') ?></p>

                            <!-- 播放按钮：传递视频名和集数数组 -->
                            <?php if (!empty($video['episodes'])): ?>
                                <div class="video-link play-btn" 
                                     data-video-name="<?= ($video['name']) ?>" 
                                     data-episodes="<?= (json_encode($video['episodes'])) ?>">
                                    立即播放
                                </div>
                            <?php endif; ?>
                            <?php if (empty($video['episodes'])): ?>
                                <div class="video-link disabled">暂无播放地址</div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            
            <?php else: ?>
                <div class="empty-tip">暂无影视资源，请稍后再试~</div>
            
        <?php endif; ?>
    </div>

    <!-- 播放弹窗 -->
    <div class="play-modal-mask" id="playModalMask"></div>
    <div class="play-modal" id="playModal">
        <div class="modal-header">
            <h3 class="modal-title" id="modalTitle">正在播放：影视名称</h3>
            <button class="modal-close" id="modalClose">&times;</button>
        </div>
        <div class="modal-episode-select" id="modalEpisodeSelect"></div>
        <div class="modal-body">
            <iframe class="modal-iframe" id="playIframe" src="" allowfullscreen></iframe>
        </div>
    </div>

    <script>
        // 解析接口前缀
        const parseApiPrefix = 'https://hhjiexi.com/play/?url=';

        // 确保DOM完全加载后再执行所有操作（修复点击无响应的核心）
        document.addEventListener('DOMContentLoaded', function() {
            // 获取所有DOM元素
            const playBtns = document.querySelectorAll('.play-btn');
            const modalMask = document.getElementById('playModalMask');
            const playModal = document.getElementById('playModal');
            const modalTitle = document.getElementById('modalTitle');
            const modalEpisodeSelect = document.getElementById('modalEpisodeSelect');
            const playIframe = document.getElementById('playIframe');
            const modalClose = document.getElementById('modalClose');

            // 播放按钮点击事件
            playBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const videoName = this.getAttribute('data-video-name');
                    const episodes = JSON.parse(this.getAttribute('data-episodes'));
                    
                    // 设置弹窗标题
                    modalTitle.textContent = `正在播放：${videoName}`;
                    // 渲染集数选择按钮
                    renderEpisodes(episodes);
                    // 默认选中第一集并播放
                    if (episodes.length > 0) {
                        selectEpisode(episodes[0].url);
                        document.querySelector('.episode-btn')?.classList.add('active');
                    }
                    // 显示弹窗
                    modalMask.style.display = 'block';
                    playModal.style.display = 'block';
                });
            });

            // 关闭弹窗事件（按钮）
            modalClose.addEventListener('click', closeModal);
            // 关闭弹窗事件（遮罩层）
            modalMask.addEventListener('click', closeModal);
            // 关闭弹窗事件（ESC键）
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') closeModal();
            });

            // 渲染集数选择按钮
            function renderEpisodes(episodes) {
                modalEpisodeSelect.innerHTML = ''; // 清空原有内容
                episodes.forEach(ep => {
                    const btn = document.createElement('button');
                    btn.className = 'episode-btn';
                    btn.textContent = ep.name;
                    btn.dataset.url = ep.url;
                    
                    // 集数切换点击事件
                    btn.addEventListener('click', function() {
                        document.querySelectorAll('.episode-btn').forEach(b => b.classList.remove('active'));
                        this.classList.add('active');
                        selectEpisode(this.dataset.url);
                    });

                    modalEpisodeSelect.appendChild(btn);
                });
            }

            // 切换集数并输出调试链接
            function selectEpisode(episodeUrl) {
                const finalUrl = parseApiPrefix + encodeURIComponent(episodeUrl);
                console.log('当前播放链接（可复制调试）：', finalUrl); // 调试用
                playIframe.src = finalUrl;
            }

            // 关闭弹窗函数
            function closeModal() {
                modalMask.style.display = 'none';
                playModal.style.display = 'none';
                playIframe.src = ''; // 停止播放，节省资源
            }
        });
    </script>
</body>
</html>