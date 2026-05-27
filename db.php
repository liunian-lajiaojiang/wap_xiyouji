<?php
/**
 * 数据库连接配置
 * 合并版：赌大小 + 西游记
 */

// 数据库配置
define('DB_HOST', 'localhost');
define('DB_USER', 'xyj');
define('DB_PASS', '123');
define('DB_NAME', 'xyj');
define('DB_CHARSET', 'utf8mb4');

// Token 配置 (免登录)
define('TOKEN_COOKIE_NAME', 'xyj_token');
define('TOKEN_EXPIRY', 86400 * 7); // 7天过期

// 游戏配置 (赌大小)
define('INITIAL_COIN', 100);        // 初始铜板
define('ODDS', 2);                  // 赔率 1:1
define('COMMISSION_RATE', 0.25);     // 手续费 25%

// 时区设置
date_default_timezone_set('Asia/Shanghai');

// 启动会话
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * 获取数据库连接
 */
function getDbConnection() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die("数据库连接失败: " . $e->getMessage());
        }
    }
    return $pdo;
}

/**
 * 生成安全随机 token
 */
function generateToken() {
    if (function_exists('random_bytes')) {
        return bin2hex(random_bytes(32));
    } elseif (function_exists('openssl_random_pseudo_bytes')) {
        return bin2hex(openssl_random_pseudo_bytes(32));
    } else {
        return md5(uniqid(rand(), true) . microtime());
    }
}

/**
 * 创建登录 Token
 */
function createLoginToken($userId) {
    $pdo = getDbConnection();
    $token = generateToken();
    $expiresAt = date('Y-m-d H:i:s', time() + TOKEN_EXPIRY);
    $stmt = $pdo->prepare("UPDATE users SET login_token = ?, token_expires_at = ? WHERE id = ?");
    $stmt->execute([$token, $expiresAt, $userId]);
    return $token;
}

/**
 * 验证 Token 并返回用户信息
 */
function verifyToken($token) {
    if (empty($token)) return null;
    $pdo = getDbConnection();
    $stmt = $pdo->prepare("
        SELECT id, username, coin, silver
        FROM users
        WHERE login_token = ? AND token_expires_at > NOW()
    ");
    $stmt->execute([$token]);
    return $stmt->fetch();
}

/**
 * 删除 Token（退出登录）
 */
function deleteToken($token) {
    $pdo = getDbConnection();
    $stmt = $pdo->prepare("UPDATE users SET login_token = NULL, token_expires_at = NULL WHERE login_token = ?");
    $stmt->execute([$token]);
}

/**
 * 检查用户是否已登录（通过 Token 或 Session）
 */
function isLoggedIn(): bool {
    // 先检查 Session
    if (isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0) {
        return true;
    }
    // 再检查 Cookie Token
    if (isset($_COOKIE[TOKEN_COOKIE_NAME])) {
        $user = verifyToken($_COOKIE[TOKEN_COOKIE_NAME]);
        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            return true;
        }
    }
    return false;
}

/**
 * 获取当前登录用户信息
 */
function getCurrentUser(): ?array {
    if (!isLoggedIn()) {
        return null;
    }
    $pdo = getDbConnection();
    $stmt = $pdo->prepare('SELECT id, username, coin, silver FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch() ?: null;
}

/**
 * 获取用户信息
 */
function getUserById(int $id): ?array {
    $pdo = getDbConnection();
    $stmt = $pdo->prepare('SELECT id, username, coin, silver FROM users WHERE id = ?');
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}

/**
 * 更新用户铜板
 */
function updateCoin(int $userId, float $amount): bool {
    $pdo = getDbConnection();
    $stmt = $pdo->prepare('UPDATE users SET coin = coin + ? WHERE id = ?');
    return $stmt->execute([$amount, $userId]);
}

/**
 * 更新用户银两
 */
function updateSilver(int $userId, float $amount): bool {
    $pdo = getDbConnection();
    $stmt = $pdo->prepare('UPDATE users SET silver = silver + ? WHERE id = ?');
    return $stmt->execute([$amount, $userId]);
}

/**
 * 铜板兑换银两 (100铜板 = 1银两)
 */
function exchangeCoinToSilver(int $userId, float $silverAmount): array {
    $pdo = getDbConnection();
    $coinNeeded = $silverAmount * 100; // 需要的铜板

    // 检查铜板是否足够
    $stmt = $pdo->prepare('SELECT coin, silver FROM users WHERE id = ? FOR UPDATE');
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if (!$user) {
        return [false, '用户不存在'];
    }

    if ($user['coin'] < $coinNeeded) {
        return [false, '铜板不足，需要 ' . formatMoney($coinNeeded) . ' 铜板'];
    }

    // 执行兑换
    $stmt = $pdo->prepare('UPDATE users SET coin = coin - ?, silver = silver + ? WHERE id = ?');
    $stmt->execute([$coinNeeded, $silverAmount, $userId]);

    return [true, '兑换成功，获得 ' . formatMoney($silverAmount) . ' 银两'];
}

/**
 * 银两兑换铜板 (1银两 = 100铜板)
 */
function exchangeSilverToCoin(int $userId, float $silverAmount): array {
    $pdo = getDbConnection();
    $coinGained = $silverAmount * 100; // 获得的铜板

    // 检查银两是否足够
    $stmt = $pdo->prepare('SELECT coin, silver FROM users WHERE id = ? FOR UPDATE');
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if (!$user) {
        return [false, '用户不存在'];
    }

    if ($user['silver'] < $silverAmount) {
        return [false, '银两不足，需要 ' . formatMoney($silverAmount) . ' 银两'];
    }

    // 执行兑换
    $stmt = $pdo->prepare('UPDATE users SET silver = silver - ?, coin = coin + ? WHERE id = ?');
    $stmt->execute([$silverAmount, $coinGained, $userId]);

    return [true, '兑换成功，获得 ' . formatMoney($coinGained) . ' 铜板'];
}

/**
 * 记录下注历史
 */
function recordBet(int $userId, float $betAmount, string $betChoice, int $diceResult, bool $isWin, float $winAmount, float $commission, float $coinAfter): bool {
    $pdo = getDbConnection();
    $betChoiceEn = ($betChoice === '大') ? 'big' : 'small';
    $isWinInt = $isWin ? 1 : 0;  // 确保转换为整数
    $stmt = $pdo->prepare('
        INSERT INTO `bet_history` (`user_id`, `bet_amount`, `bet_choice`, `dice_result`, `is_win`, `win_amount`, `commission`, `coin_after`)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ');
    $result = $stmt->execute([$userId, $betAmount, $betChoiceEn, $diceResult, $isWinInt, $winAmount, $commission, $coinAfter]);
    
    // 清理超过20条的旧记录（保留最新的20条）
    $pdo->exec("
        DELETE FROM `bet_history`
        WHERE `user_id` = $userId
          AND `id` NOT IN (
              SELECT `id` FROM (
                  SELECT `id` FROM `bet_history`
                  WHERE `user_id` = $userId
                  ORDER BY `created_at` DESC
                  LIMIT 20
              ) AS `keep_ids`
          )
    ");
    
    return $result;
}

/**
 * 获取用户下注历史
 */
function getBetHistory(int $userId, int $limit = 20): array {
    $pdo = getDbConnection();
    $stmt = $pdo->prepare('
        SELECT * FROM bet_history 
        WHERE user_id = ? 
        ORDER BY created_at DESC 
        LIMIT ?
    ');
    $stmt->execute([$userId, $limit]);
    return $stmt->fetchAll();
}

/**
 * 掷骰子 (返回 1-6)
 */
function rollDice(): int {
    return random_int(1, 6);
}

/**
 * 判断大小
 */
function getDiceCategory(int $points): string {
    return $points <= 3 ? '小' : '大';
}

/**
 * 验证用户名格式
 */
function validateUsername(string $username): array {
    if (strlen($username) < 1 || strlen($username) > 20) {
        return [false, '用户名长度必须在1-20个字符之间'];
    }
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        return [false, '用户名只能包含字母、数字和下划线'];
    }
    return [true, ''];
}

/**
 * 验证密码格式
 */
function validatePassword(string $password): array {
    if (strlen($password) < 1) {
        return [false, '密码至少需要1个字符'];
    }
    return [true, ''];
}

/**
 * 设置闪光消息
 */
function setFlash(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/**
 * 获取闪光消息
 */
function getFlash(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * 格式化金额
 */
function formatMoney(int $amount): string {
    return number_format($amount);
}

/**
 * 重定向
 */
function redirect(string $url): never {
    header('Location: ' . $url);
    exit;
}

/**
 * 生成 CSRF Token
 */
function generateCSRFToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * 验证 CSRF Token
 */
function validateCSRFToken(?string $token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token ?? '');
}
?>
