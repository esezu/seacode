<section class="content-box">
  <ol class="breadcrumb">
    <a href="<?= ($BASE) ?>">首页</a>&nbsp;&nbsp;&raquo;&nbsp;&nbsp;<a href="<?= ($BASE) ?>?sort=<?= ($VIDEO_INFO['tid']) ?>"><?= ($VIDEO_INFO['type'] ?: '未知') ?></a>&nbsp;&nbsp;&raquo;&nbsp;&nbsp;<?= ($VIDEO_INFO['name'] ?: '未知')."
" ?>
  </ol>
  <div class="content-row">
    <div class="cont-l">
      <div class="con-pic">  <img class="img-thumbnail" alt="<?= ($VIDEO_INFO['name'] ?: '未知') ?>" src="<?= ($VIDEO_INFO['pic'] ?: 'https://placehold.co/200x280?text=No+Image') ?>">

      </div>
      <div class="con-dete">
        <div class="con-detail">
          <ul>
            <li class="li_l"><span class="info-label">片名</span></li>
            <li class="li_r"><?= ($VIDEO_INFO['name'] ?: '未知') ?></li>
          </ul>
          <ul>
            <li class="li_l"><span class="info-label">导演</span></li>
            <li class="li_r"><?= ($VIDEO_INFO['director'] ?: '未知') ?></li>
          </ul>
          <ul>
            <li class="li_l"><span class="info-label">主演</span></li>
            <li class="li_r"><?= ($VIDEO_INFO['actor'] ?: '未知') ?></li>
          </ul>
		  
          <ul>
            <li class="li_l"><span class="info-label">类型</span></li>
            <li class="li_r"><?= ($VIDEO_INFO['type'] ?: '未知') ?></li>
          </ul>
		  
          <ul>
            <li class="li_l"><span class="info-label">地区</span></li>
            <li class="li_r"><?= ($VIDEO_INFO['area'] ?: '未知') ?></li>
          </ul>
          <ul>
            <li class="li_l"><span class="info-label">更新时间</span></li>
            <li class="li_r"><?= ($VIDEO_INFO['last'] ?: '未知') ?></li>
          </ul>
        </div>
      </div>
      <div class="con-des">
       <p> <strong>剧情介绍：</strong></p>
       <p class="summary"><?= ($VIDEO_INFO['des'] ?: '暂无描述') ?></p>
      </div>
    </div>
  </div>
  <div class="panel" id="iframe" style="display:none;"> 
<iframe src="" width="100%" height="400px" frameborder="no" border="0" marginwidth="0" marginheight="0" scrolling="no" allowfullscreen="true" id="frame"></iframe>
     </div>
  <div class="play-list" id="playlist">
    <div class="panel"> 
      <div class="panel-heading"><strong>
        <mb class="mbnone">《<?= ($VIDEO_INFO['name'] ?: '未知') ?>》 - </mb>
       资源加载中：</strong></div>
      <ul class="dslist-group" id="zylx资源加载中">
        <li><a href="#iframe" target="_self" onclick="bf('剧集地址加载中')">剧集加载中</a></li>
      </ul>
      <div class="panel-footer"> <strong>《<?= ($VIDEO_INFO['name'] ?: '未知') ?>》 - 资源加载中资源观看帮助：</strong><br/>
        1、有个别电影打开后播放需要等待。<br/>
        2、有的播放不了请多刷新几下，试试。 <br/>
	  </div>
     </div>

 </div>


    
</section>
<?= ($this->raw($PLAYER_SCRIPT)) ?>