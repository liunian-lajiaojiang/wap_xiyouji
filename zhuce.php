<?php
require_once 'db.php';

$error = '';
$success = false;
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 验证CSRF Token
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = '无效的请求,请重试';
    } else {
        $username = isset($_POST['username']) ? trim($_POST['username']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        $confirmPassword = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

        // 验证用户名
        [$valid, $msg] = validateUsername($username);
        if (!$valid) {
            $error = $msg;
        }
        // 验证密码确认
        elseif ($password !== $confirmPassword) {
            $error = '两次输入的密码不一致';
        } else {
            [$valid, $msg] = validatePassword($password);
            if (!$valid) {
                $error = $msg;
            } else {
                try {
                    $pdo = getDbConnection();

                    // 检查用户名是否已存在
                    $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ?');
                    $stmt->execute([$username]);

                    if ($stmt->fetch()) {
                        $error = '用户名已存在';
                    } else {
                        // 密码哈希加密
                        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                        // 插入新用户（带初始铜板）
                        $stmt = $pdo->prepare('INSERT INTO users (username, password, coin) VALUES (?, ?, ?)');
                        $stmt->execute([$username, $hashedPassword, INITIAL_COIN]);

                        $success = true;
                    }
                } catch (PDOException $e) {
                    $error = '系统错误，请稍后再试';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-cn">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1,user-scalable=no">
  <meta name="description" content="西游记mud是源自Mud西游记2000的经典还原H5网页文字游戏。">
  <meta name="keywords" content="西游记mud,西游记怀旧mud，西游记h5">
  <meta name="theme-color" content="#226997">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <title><?php echo $success ? '注册成功' : '注册'; ?> - 西游记怀旧mud</title>
  <link rel="manifest" href="manifest.json">
  <link rel="shortcut icon" href="pic/favicon.ico">
  <link rel="apple-touch-icon" href="pic/dao.png">
  <link rel="stylesheet" href="css/index.css">
  <link rel="stylesheet" href="css/footer.css">
  <script src="https://libs.baidu.com/jquery/1.9.1/jquery.min.js"></script>
</head>

<body>
  <div class="bg-layer"><img src="pic/huyanlv.jpg" width="100%" height="100%" alt="护眼绿"></div>
  <div class="container">
    <?php if ($success): ?>
      <h1>注册成功！</h1>
      <hr>
      <br>
      <p class="poem">春风得意马蹄疾，一日看尽长安花。</p>
      <br>
      <p>恭喜您已成功注册账号！</p>
      <p>用户名：<?php echo htmlspecialchars($username); ?></p>
      <p>获得初始资金：<?php echo formatMoney(INITIAL_COIN); ?> 铜板+<?php echo formatMoney(100); ?> 银两</p>
      <br>
      <a href="login.php" class="submit-btn">立即登录</a>
    <?php else: ?>
      <h1>西游记怀旧mud上线测试中。。。</h1>
      <a href="gengxinsilu.html" target="_blank">近期更新思路</a>&ensp;<a href="news.html">公告</a><br>
      <hr><br>
      <p class="poem">清秋幕府井梧寒，独宿江城蜡炬残。</p><br>

      <?php if ($error): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>

      <form action="zhuce.php" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRFToken()); ?>">
        <div class="form-group">
          <p class="login-title">注册新账号</p>
          <img width="100" height="50" src="pic/dao.png" alt="道">
          <br>
          <label for="username">用户名</label>
          <input type="text" name="username" value="<?php echo htmlspecialchars($username); ?>" placeholder="请输入用户名（3-20字符，字母数字下划线）" required autofocus id="username">
        </div>
        <div class="form-group password-group">
          <label for="password">密码</label>
          <input type="password" id="password" name="password" placeholder="请输入密码（至少1字符）" required>
          <button type="button" class="toggle-btn" onclick="togglePassword()">显示密码</button>
        </div>
        <div class="form-group">
          <label for="confirm_password">确认密码</label>
          <input type="password" id="confirm_password" name="confirm_password" placeholder="再次输入密码" required>
        </div>
        <a href="login.php">已有账号？去登录吧！</a>
        <button type="submit" class="submit-btn">注册</button>
      </form>
    <?php endif; ?>

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