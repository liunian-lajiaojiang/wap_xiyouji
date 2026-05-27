<?php
// 数据库配置 - 使用主项目 xyj 数据库
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

define('DB_HOST', 'localhost');
define('DB_USER', 'xyj');
define('DB_PASS', '123');
define('DB_NAME', 'xyj');   // 使用主项目数据库

// Token 配置 (必须与主项目一致)
define('TOKEN_COOKIE_NAME', 'xyj_token');

// 连接数据库
function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => '数据库连接失败: ' . $e->getMessage()]);
            exit;
        }
    }
    return $pdo;
}

// 启动Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 验证 Token 并恢复 Session（从主项目 db.php 复制）
function verifyToken($token) {
    if (empty($token)) return null;
    $pdo = getDB();
    $stmt = $pdo->prepare("
        SELECT id, username, coin, silver
        FROM users
        WHERE login_token = ? AND token_expires_at > NOW()
    ");
    $stmt->execute([$token]);
    return $stmt->fetch();
}

// 获取当前登录用户ID（支持 Session 和 Cookie Token）
function getCurrentUserId() {
    // 先检查 Session
    if (isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0) {
        return intval($_SESSION['user_id']);
    }
    // 再检查 Cookie Token
    if (isset($_COOKIE[TOKEN_COOKIE_NAME])) {
        $user = verifyToken($_COOKIE[TOKEN_COOKIE_NAME]);
        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            return intval($user['id']);
        }
    }
    error('请先登录', 401);
}

// 检查是否已登录（支持 Session 和 Cookie Token）
function isLoggedIn() {
    if (isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0) {
        return true;
    }
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

// 获取用户名
function getCurrentUsername() {
    if (isset($_SESSION['username'])) {
        return $_SESSION['username'];
    }
    // 通过 Token 获取
    if (isset($_COOKIE[TOKEN_COOKIE_NAME])) {
        $user = verifyToken($_COOKIE[TOKEN_COOKIE_NAME]);
        if ($user) {
            $_SESSION['username'] = $user['username'];
            return $user['username'];
        }
    }
    return '';
}

// 统一响应格式
function response($data, $code = 200) {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// 错误响应
function error($message, $code = 400) {
    response(['success' => false, 'error' => $message], $code);
}

// 成功响应
function success($data = []) {
    response(array_merge(['success' => true], $data));
}
