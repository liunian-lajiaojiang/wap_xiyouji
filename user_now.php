<?php
require_once 'db.php';

// 禁止浏览器缓存
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>加载中...</title>
</head>
<body>
    <p>正在加载... <span id="status"></span></p>
    
    <script>
    // 使用原生 JavaScript，避免 jQuery 加载问题
    (function() {
        document.getElementById('status').textContent = '请求中...';
        
        fetch('check_user.php?t=' + Date.now(), {
            method: 'GET',
            credentials: 'same-origin'
        })
        .then(function(response) {
            return response.json();
        })
        .then(function(data) {
            document.getElementById('status').textContent = '加载完成';
            
            if (!data.loggedIn) {
                window.location.href = 'index.html';
                return;
            }
            
            document.title = '用户: ' + data.username;
            document.body.innerHTML = 
                '<div class="user-info">' +
                '<span class="user-name">你好，玩家： ' + escapeHtml(data.username) + '</span>' +
                '<br><a href="logout.php" class="logout-btn">退出登录</a>' +
                '<br><br><a href="HJYW.php">进入傲来国</a>' +
                '</div>' +
                '<div style="background:#f0f0f0;padding:10px;margin-top:20px;font-size:12px;">' +
                '<b>登录状态：</b><br>' +
                'User ID: ' + data.userId + '<br>' +
                'Username: ' + escapeHtml(data.username) + '<br>' +
                '加载时间: ' + new Date().toLocaleString() +
                '</div>';
        })
        .catch(function(err) {
            document.getElementById('status').textContent = '错误: ' + err.message;
            document.body.innerHTML += '<br><button onclick="location.reload()">刷新</button>';
        });
        
        function escapeHtml(text) {
            var div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    })();
    </script>
</body>
</html>

