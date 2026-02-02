<div class="video-detail">
    <?php if ($video): ?>
        <div class="detail-content">
            <img class="pic" src="<?= ($video['pic']) ?>" alt="<?= ($video['name']) ?>" onerror="this.src='https://via.placeholder.com/300x400/f5f5f5/999999?text=暂无封面'">
            <div class="info">
                <h2><?= ($video['name']) ?></h2>
                <div class="meta">
                    <p><span>类型:</span> <?= ($video['type']) ?></p>
                    <p><span>地区:</span> <?= ($video['area']) ?></p>
                    <p><span>年份:</span> <?= ($video['year']) ?></p>
                    <p><span>语言:</span> <?= ($video['lang']) ?></p>
                    <p><span>状态:</span> <?= ($video['note']) ?></p>
                    <p><span>导演:</span> <?= ($video['director']) ?></p>
                    <p><span>主演:</span> <?= ($video['actor']) ?></p>
                    <p><span>更新时间:</span> <?= ($video['last']) ?></p>
                </div>
                <div class="description">
                    <h3>简介</h3>
                    <p><?= ($video['des'] ?: '暂无简介') ?></p>
                </div>
            </div>
        </div>

        <!-- 播放列表 -->
        <div class="play-list">
            <h3>播放列表</h3>
            <div class="items">
                <?php if ($video['playList']): ?>
                    <?php foreach (($video['playList']?:[]) as $index=>$item): ?>
                        <a href="/play/<?= ($video['id']) ?>/<?= ($index) ?>" class="item">
                            <?= ($item['name'])."
" ?>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>暂无播放列表</p>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="empty">
            <h3>视频信息加载失败</h3>
            <p>请稍后再试</p>
        </div>
    <?php endif; ?>
</div>
