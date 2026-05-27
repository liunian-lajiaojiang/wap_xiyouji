<?php
require_once 'db.php';

// 如果已登录,直接跳转到用户页面
if (isLoggedIn()) {
    redirect('user_now.php');
}

$error = '';
$username = '';

// 处理登录请求
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if (empty($username) || empty($password)) {
        $error = '用户名和密码不能为空';
    } else {
        try {
            $pdo = getDbConnection();
            $stmt = $pdo->prepare('SELECT id, username, password FROM users WHERE username = ?');
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if (!$user) {
                $error = '用户名不存在，请先注册';
            } elseif (!password_verify($password, $user['password'])) {
                $error = '密码错误，请重新输入';
            } else {
                // 创建新的登录 Token (7天免登录)
                $token = createLoginToken($user['id']);

                // 设置 Token Cookie
                setcookie(TOKEN_COOKIE_NAME, $token, time() + TOKEN_EXPIRY, '/');

                // 保存基本信息到 session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];

                // 跳转到用户页面
                redirect('user_now.php');
            }
        } catch (PDOException $e) {
            $error = '系统错误：' . htmlspecialchars($e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-cn"><!-- style="filter: grayscale(100%);" nighteye="disabled"--此代码可置于html标签内、且呈灰黑（思路1）-->

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1,user-scalable=no">
  <meta name="description" content="西游记mud是源自Mud西游记2000的经典还原H5网页文字游戏。">
  <meta name="keywords" content="西游记mud,西游记怀旧mud，西游记h5">
  <meta name="theme-color" content="#226997">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <title>登录结果 - 西游记怀旧mud(内测中beta)</title>
  <link rel="manifest" href="manifest.json">
  <link rel="shortcut icon" href="pic/favicon.ico">
  <link rel="apple-touch-icon" href="pic/dao.png">
  <script src="https://libs.baidu.com/jquery/1.9.1/jquery.min.js"></script>
  <link rel="stylesheet" href="css/index.css">
</head>

<body>
  <div class="bg-layer"><img src="pic/huyanlv.jpg" width="100%" height="100%" alt="护眼绿"></div>
  <div class="container">
    <!-- 《button onclick="changeTheme()" value="切换主题"》日/夜模式《/button》 -->
    <!--在body处添加theme class、以期实现夜间模式（思路2）-->
    <h1>登录结果</h1>
    <hr>
    <br>
    <p class="poem">峨眉山月半轮秋，影入平羌江水流。</p>
    <br>
    <?php if ($error): ?>
      <div class="error">
        <?php echo htmlspecialchars($error); ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="">
      <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRFToken()); ?>">
      <div class="form-group">
        <label for="username">用户名</label>
        <input type="text" name="username"
          value="<?php echo htmlspecialchars($username); ?>" required id="username">
      </div>
      <div class="form-group password-group">
        <label for="password">密码</label>
        <input type="password" id="password" name="password" placeholder="请输入密码" required>
        <button type="button" class="toggle-btn" onclick="togglePassword()">显示密码</button>
      </div>
      <a href="zhuce.php">还没有账号？点此注册</a>
      <button type="submit" class="submit-btn">登录</button>
    </form>
    <div class="back-link">
      <p>当前时间:
        <script src="js/time.js"></script>
      </p>
      <p><a href="about_us.html">关于我们</a> | <a href="javascript:window.location.reload();">刷新此页面</a></p>
    </div>
  </div>


  <script>
    function togglePassword() {
      const passwordInput = document.getElementById('password');
      const toggleBtn = document.querySelector('.toggle-btn');

      if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleBtn.textContent = '隐藏密码';
      } else {
        passwordInput.type = 'password';
        toggleBtn.textContent = '显示密码';
      }
    }
  </script>
</body>

</html>