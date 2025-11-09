<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>CSV File upload system</title>
    <style scoped>
        * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        }

        body {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
        padding: 20px;
        }

        .container {
        max-width: 1200px;
        margin: 0 auto;
        }

        .header {
        text-align: center;
        color: white;
        margin-bottom: 30px;
        }

        .header h1 {
        font-size: 2.5rem;
        margin-bottom: 10px;
        }
        .upload-section { background: white; border-radius: 12px; padding: 30px; margin-bottom: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.2);}
        .upload-area { border: 3px dashed #667eea; border-radius: 8px; padding: 40px; text-align: center; cursor: pointer; transition: all 0.3s; background: #f8f9fa;}
        .upload-area:hover { border-color: #764ba2; background: #f0f0f0;}
        .upload-area.dragover { border-color: #764ba2; background: #e8e8e8;}
        .upload-icon { font-size: 48px; margin-bottom: 15px;}
        .upload-text { font-size: 18px; color: #333; margin-bottom: 10px;}
        .upload-hint { font-size: 14px; color: #666;}
        input[type="file"] { display: none; }
        .btn { background: #667eea; color: white; border: none; padding: 12px 24px; border-radius: 6px; cursor: pointer; font-size: 16px; transition: all 0.3s;}
        .btn:hover { background: #764ba2;}
        .btn:disabled { background: #ccc; cursor: not-allowed;}
        .uploads-list { background: white; border-radius: 12px; padding: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.2);}
        .uploads-list h2 { margin-bottom: 20px; color: #333;}
        .upload-item {
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 12px 18px;
            margin-bottom: 15px;
            background: #fff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            transition: box-shadow 0.2s;
        }
        .upload-item:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.1);}
        .upload-row {
            display: flex;
            justify-content: space-between;
            align-items: center; 
        }
        .upload-info-left {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .upload-time {
            font-size: 0.9rem;
            color: #666;
        }
        .upload-status { 
            padding: 6px 14px;
            border-radius: 6px;
            font-weight: 600;
            text-align: center;
            min-width: 100px;
        }
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        .status-processing {
            background-color: #cce5ff;
            color: #004085;
        }

        .status-completed {
            background-color: #d4edda;
            color: #155724;
        }

        .status-failed {
            background-color: #f8d7da;
            color: #721c24;
        }

        .upload-error {
            margin-top: 8px;
            padding: 8px;
            border-radius: 5px;
            background-color: #f8d7da;
            color: #721c24;
            text-align: center;
        }
        .empty-state { text-align: center; padding: 40px; color: #999;}
        </style>

    @vite(['resources/js/app.js'])
</head>
<body>
    <div id="app"></div>
</body>
</html>
