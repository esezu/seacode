<section class="main-container">
  
  <div class="row-five">
    <div class="box-title"><b><?= ($SEARCH_KEYWORD) ?>的搜索结果</b></div>
    <div class="box-body"> 
      <?php foreach (($VIDEO_DATA?:[]) as $video): ?>
      <div class="box-item"> <a class="item-link" href="<?= ($BASE) ?>?info=<?= ($video['id']) ?>" title="<?= ($video['name']) ?>"> <img src="<?= ($video['pic'] ?: 'https://placehold.co/200x280?text=No+Image') ?>" alt="<?= ($video['name']) ?>">
        <button class="hdtag"><?= ($video['year'] ?: '未知') ?></button>
         </a>
        <div class="meta">
          <div class="item-name"><a class="movie-name" title="<?= ($video['name']) ?>" href="<?= ($BASE) ?>?info=<?= ($video['id']) ?>"><?= ($video['name'] ?: '未知') ?></a></div>
          <em>更新：<strong><span><?= ($video['last'] ?: '未知') ?></span></strong></em> </div>
      </div>
      <?php endforeach; ?>
       </div>
  </div>
  <div class="pagenav">
    <ul class="pagination">
      <a target="_self" href="<?= ($PAGINATION['firstUrl']) ?>" class="pagelink_a">首页</a>&nbsp;<a target="_self" href="<?= ($PAGINATION['prevUrl']) ?>" class="pagelink_a">上一页</a>&nbsp;<span class="page-info"><?= ($PAGINATION['current']) ?>/<?= ($PAGINATION['last']) ?></span>&nbsp;<a target="_self" href="<?= ($PAGINATION['nextUrl']) ?>" class="pagelink_a">下一页</a>&nbsp;<a target="_self" href="<?= ($PAGINATION['lastUrl']) ?>" class="pagelink_a">尾页</a>
    </ul>
  </div>
</section>