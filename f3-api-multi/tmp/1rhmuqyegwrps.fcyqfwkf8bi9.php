<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= ($SITE_NAME) ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
        }
        .api-selector {
            margin: 20px 0;
            padding: 10px;
            background-color: #f0f0f0;
            border-radius: 4px;
        }
        .api-selector select {
            padding: 8px;
            font-size: 16px;
        }
        .api-selector button {
            padding: 8px 16px;
            font-size: 16px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .api-selector button:hover {
            background-color: #45a049;
        }
        .endpoint-selector {
            margin: 20px 0;
        }
        .endpoint-selector a {
            display: inline-block;
            margin: 5px;
            padding: 8px 16px;
            background-color: #2196F3;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }
        .endpoint-selector a:hover {
            background-color: #0b7dda;
        }
        .api-data {
            margin: 20px 0;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 4px;
            border: 1px solid #ddd;
        }
        .api-info {
            margin: 10px 0;
            padding: 10px;
            background-color: #e3f2fd;
            border-radius: 4px;
        }
        pre {
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        .template-info {
            margin: 10px 0;
            padding: 10px;
            background-color: #fff3e0;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><?= ($SITE_NAME) ?></h1>
        
        <div class="template-info">
            <p><strong>当前模板:</strong> default</p>
        </div>
        
        <div class="api-info">
            <p><strong>当前API:</strong> <?= ($CURRENT_API_NAME) ?></p>
            <p><strong>API URL:</strong> <?= ($CURRENT_API_URL) ?></p>
        </div>
        
        <div class="api-selector">
            <form method="GET">
                <label for="api">选择API:</label>
                <select name="api" id="api">
                    <?= ($API_OPTIONS)."
" ?>
                </select>
                <label for="template">选择模板:</label>
                <select name="template" id="template">
                    <option value="default" <?= ($CURRENT_TEMPLATE == 'default' ? 'selected' : '') ?>>Default</option>
                    <option value="default2" <?= ($CURRENT_TEMPLATE == 'default2' ? 'selected' : '') ?>>Default2</option>
                </select>
                <label for="endpoint">选择端点:</label>
                <select name="endpoint" id="endpoint">
                    <option value="home" <?= ($ENDPOINT == 'home' ? 'selected' : '') ?>>首页</option>
                    <option value="list" <?= ($ENDPOINT == 'list' ? 'selected' : '') ?>>列表</option>
                    <option value="detail" <?= ($ENDPOINT == 'detail' ? 'selected' : '') ?>>详情</option>
                    <option value="search" <?= ($ENDPOINT == 'search' ? 'selected' : '') ?>>搜索</option>
                </select>
                <button type="submit">切换</button>
            </form>
        </div>
        
        <div class="endpoint-selector">
            <h3>快速端点访问:</h3>
            <a href="<?= ($BASE) ?>?api=<?= ($CURRENT_API) ?>&template=<?= ($CURRENT_TEMPLATE) ?>&endpoint=home">首页</a>
            <a href="<?= ($BASE) ?>?api=<?= ($CURRENT_API) ?>&template=<?= ($CURRENT_TEMPLATE) ?>&endpoint=list">列表</a>
            <a href="<?= ($BASE) ?>?api=<?= ($CURRENT_API) ?>&template=<?= ($CURRENT_TEMPLATE) ?>&endpoint=detail&id=1">详情</a>
            <a href="<?= ($BASE) ?>?api=<?= ($CURRENT_API) ?>&template=<?= ($CURRENT_TEMPLATE) ?>&endpoint=search&q=测试">搜索</a>
        </div>
        
        <div class="api-data">
            <h3>API响应数据:</h3>
            <pre><?= ($API_DATA_PRETTY) ?></pre>
        </div>
    </div>
</body>
</html>