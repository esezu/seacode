<!DOCTYPE html>
<html lang="zh-cn">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<meta name="viewport" content="width=device-width,initial-scale=1.0,user-scalable=no" />
<meta name="applicable-device" content="pc,mobile">
<title><?= ($CURRENT_CATEGORY) ?> - <?= ($SITE_NAME) ?></title>
<meta name="keywords" content="<?= ($CURRENT_CATEGORY) ?>,最新电影,最新电视,最新综艺,最新动漫" />
<meta name="description" content="<?= ($SITE_NAME) ?>提供最新的电影、电视、综艺、动漫在线播放服务">
<script src="https://apps.bdimg.com/libs/jquery/2.1.4/jquery.min.js"></script>
<script src="https://lib.baomitu.com/jquery.lazyload/1.9.1/jquery.lazyload.min.js"></script>
<script>
function changeApi() {
  var apiId = document.getElementById('api-selector').value;
  document.cookie = 'api_select=' + apiId + '; path=/; max-age=' + (86400 * 30);
  var url = new URL(window.location.href);
  url.searchParams.delete('api');
  url.searchParams.delete('page');
  window.location.href = url.toString();
}

document.addEventListener('DOMContentLoaded', function() {
  var selector = document.getElementById('api-selector');
  if (selector) {
    var currentApi = getCookie('api_select') || '1';
    selector.value = currentApi;
  }
});

function getCookie(name) {
  var value = '; ' + document.cookie;
  var parts = value.split('; ' + name + '=');
  if (parts.length === 2) {
    return parts.pop().split(';').shift();
  }
  return '';
}
</script>
<link rel="stylesheet" href="<?= ($TEMPLATE_PATH) ?>/css/home.css">
<link rel="stylesheet" rev="stylesheet" type="text/css" media="all" href="<?= ($TEMPLATE_PATH) ?>/css/style.css">
</head>
<body id="body">
<header>
  <div class="head">
    <div class="logo"><a href="<?= ($BASE) ?>"><?= ($SITE_NAME) ?></a></div>
    
    <div class="api-select">
      <select id="api-selector" onchange="changeApi()">
        <?php foreach (($API_LIST?:[]) as $api): ?>
        <option value="<?= ($api['id']) ?>" <?= ($api['id'] == $CURRENT_API ? 'selected' : '') ?>><?= ($api['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="search-box">
      <form name="formsearch" id="formsearch" action="<?= ($BASE) ?>" method="get" autocomplete="off">
        <input type="text" id="searchword" name="key" class="search-input"  placeholder="搜索视频"  />
        <input type="submit" id="searchbutton" class="search-button" value="搜索">
      </form>
    </div>
  </div>
  <nav class="navbar">
    <div class="nav-container"> 
      <ul class="menu">
        <?php foreach (($CATEGORIES?:[]) as $category): ?>
        <li><a href="<?= ($BASE) ?>?sort=<?= ($category['分类号']) ?>"><?= ($category['分类名']) ?></a></li>
        <?php endforeach; ?>
      </ul>
    </div>
  </nav>
</header>

<?php echo $this->render($TEMPLATE_TO_INCLUDE,NULL,get_defined_vars(),0); ?>



<footer class="footer">
  <p>本网站提供的最新电视剧和电影资源均系收集于各大视频网站本网站只提供web页面服务并不提供影片资源存储也不参与录制上传</p>
  <p>若本站收录的节目无意侵犯了贵司版权，请给邮箱<?= ($SITE_EMAIL) ?>，我们会在3个工作日内删除侵权内容，谢谢。</p>
  <p>友情提示：请勿长时间观看影视，注意保护视力并预防近视，合理安排时间，享受健康生活。</p>
</footer>
</body>
</html>