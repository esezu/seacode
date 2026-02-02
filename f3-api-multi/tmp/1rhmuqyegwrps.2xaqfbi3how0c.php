<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= ($SITE_NAME) ?></title>
    <style>
        body {
            font-family: 'Microsoft YaHei', sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f0f2f5;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #1890ff;
            text-align: center;
        }
        .api-selector {
            margin: 20px 0;
            padding: 20px;
            background-color: #f7f7f7;
            border-radius: 8px;
            border: 1px solid #e8e8e8;
        }
        .api-selector select {
            padding: 10px;
            font-size: 16px;
            border: 1px solid #d9d9d9;
            border-radius: 4px;
            margin-right: 10px;
        }
        .api-selector button {
            padding: 10px 20px;
            font-size: 16px;
            background-color: #1890ff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .api-selector button:hover {
            background-color: #40a9ff;
        }
        .endpoint-selector {
            margin: 20px 0;
            text-align: center;
        }
        .endpoint-selector a {
            display: inline-block;
            margin: 5px 10px;
            padding: 10px 20px;
            background-color: #52c41a;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        .endpoint-selector a:hover {
            background-color: #73d13d;
        }
        .api-data {
            margin: 20px 0;
            padding: 20px;
            background-color: #f7f7f7;
            border-radius: 8px;
            border: 1px solid #e8e8e8;
        }
        .api-info {
            margin: 10px 0;
            padding: 15px;
            background-color: #e6f7ff;
            border-radius: 6px;
            border-left: 4px solid #1890ff;
        }
        pre {
            white-space: pre-wrap;
            word-wrap: break-word;
            background-color: #fafafa;
            padding: 15px;
            border-radius: 6px;
            border: 1px solid #e8e8e8;
        }
        .template-info {
            margin: 10px 0;
            padding: 15px;
            background-color: #fff7e6;
            border-radius: 6px;
            border-left: 4px solid #faad14;
        }
        .form-group {
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><?= ($SITE_NAME) ?></h1>
        
        <div class="template-info">
            <p><strong>当前模板:</strong> default2</p>
        </div>
        
        <div class="api-info">
            <p><strong>当前API:</strong> <?= ($CURRENT_API_NAME) ?></p>
            <p><strong>API URL:</strong> <?= ($CURRENT_API_URL) ?></p>
        </div>
        
        <div class="api-selector">
            <form method="GET">
                <div class="form-group">
                    <label for="api">选择API:</label>
                    <select name="api" id="api">
                        <?= ($API_OPTIONS)."
" ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="template">选择模板:</label>
                    <select name="template" id="template">
                        <option value="default" <?= ($CURRENT_TEMPLATE == 'default' ? 'selected' : '') ?>>Default</option>
                        <option value="default2" <?= ($CURRENT_TEMPLATE == 'default2' ? 'selected' : '') ?>>Default2</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="endpoint">选择端点:</label>
                    <select name="endpoint" id="endpoint">
                        <option value="home" <?= ($ENDPOINT == 'home' ? 'selected' : '') ?>>首页</option>
                        <option value="list" <?= ($ENDPOINT == 'list' ? 'selected' : '') ?>>列表</option>
                        <option value="detail" <?= ($ENDPOINT == 'detail' ? 'selected' : '') ?>>详情</option>
                        <option value="search" <?= ($ENDPOINT == 'search' ? 'selected' : '') ?>>搜索</option>
                    </select>
                </div>
                
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