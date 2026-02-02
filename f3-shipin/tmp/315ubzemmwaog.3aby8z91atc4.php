<h2><?= ($title) ?></h2>

<?php if ($videos): ?>
    <?php if (count($videos) > 0): ?>
        <div class="video-grid">
            <?php foreach (($videos?:[]) as $video): ?>
                <div class="video-card" onclick="location.href='/info/<?= ($video['id']) ?>'">
                    <img class="pic" src="<?= ($video['pic']) ?>" alt="<?= ($video['name']) ?>" onerror="this.src='https://via.placeholder.com/300x400/f5f5f5/999999?text=暂无封面'">
                    <div class="info">
                        <div class="name"><?= ($video['name']) ?></div>
                        <div class="meta">
                            <span><?= ($video['year']) ?></span> | <span><?= ($video['area']) ?></span>
                        </div>
                        <?php if ($video['note']): ?>
                            <div class="note"><?= ($video['note']) ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- 分页 -->
        <?php if ($pagination['pagecount'] > 1): ?>
            <div class="pagination">
                <?php if ($pagination['page'] > 1): ?>
                    <a href="/list<?= ($PARAMS['type_id'] ? '/'.$PARAMS['type_id'] : '') ?>/1">首页</a>
                    <a href="/list<?= ($PARAMS['type_id'] ? '/'.$PARAMS['type_id'] : '') ?>/<?= ($pagination['page'] - 1) ?>">上一页</a>
                <?php endif; ?>

                <span class="current">第 <?= ($pagination['page']) ?> 页</span>
                <span>/</span>
                <span>共 <?= ($pagination['pagecount']) ?> 页</span>

                <?php if ($pagination['page'] < $pagination['pagecount']): ?>
                    <a href="/list<?= ($PARAMS['type_id'] ? '/'.$PARAMS['type_id'] : '') ?>/<?= ($pagination['page'] + 1) ?>">下一页</a>
                    <a href="/list<?= ($PARAMS['type_id'] ? '/'.$PARAMS['type_id'] : '') ?>/<?= ($pagination['pagecount']) ?>">尾页</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="empty">
            <h3>暂无视频数据</h3>
            <p>请尝试其他分类或搜索</p>
        </div>
    <?php endif; ?>
<?php else: ?>
    <div class="loading">加载中...</div>
<?php endif; ?>
