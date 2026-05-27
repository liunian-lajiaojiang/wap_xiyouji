/**
 * 登录验证通用脚本
 * 用法：在需要验证登录的页面引入此脚本
 * 需要页面有 <span id="auth-username">加载中...</span> 用于显示用户名
 */

(function() {
    // 页面加载时验证登录
    fetch('check_user.php?t=' + Date.now(), {
        method: 'GET',
        credentials: 'same-origin'
    })
    .then(function(response) {
        return response.json();
    })
    .then(function(data) {
        if (!data.loggedIn) {
            window.location.href = 'index.html';
            return;
        }
        // 更新用户名
        var usernameSpan = document.getElementById('auth-username');
        if (usernameSpan) {
            usernameSpan.textContent = data.username;
        }
    })
    .catch(function(err) {
        console.log('加载用户失败');
    });

    // 处理浏览器后退缓存
    window.addEventListener('pageshow', function(event) {
        if (event.persisted) {
            window.location.reload();
        }
    });
})();
