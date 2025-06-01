<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redirecting...</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            padding: 50px;
            background-color: #f4f4f4;
        }
        .message {
            font-size: 18px;
            color: #333;
        }
        .loading {
            margin-top: 20px;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 5px solid #ccc;
            border-top-color: #007bff;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
    <script>
        // 判断是否为移动设备
        function isMobile() {
            return /Android|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
        }

        // 获取当前 URL 的路径
        var currentPath = window.location.pathname;

        // 目标跳转路径
        var targetPath = isMobile() ? "/h5/" : "/pc/";

        // 如果当前路径不是目标路径，则跳转
        if (!currentPath.startsWith(targetPath)) {
            window.location.href = "/h5/";
        }
    </script>
</head>
<body>
    <h1 class="message">Redirecting to the appropriate version...</h1>
    <div class="loading"></div>
</body>
</html>