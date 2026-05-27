<?php
require_once 'db.php';

// 禁止缓存
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: -1');

// 获取当前 token
$token = isset($_COOKIE[TOKEN_COOKIE_NAME]) ? $_COOKIE[TOKEN_COOKIE_NAME] : '';

// 删除数据库中的 token（关键！）
if (!empty($token)) {
    deleteToken($token);
}

// 清除 Cookie
setcookie(TOKEN_COOKIE_NAME, '', time() - 3600, '/');

// 清除 Session
$_SESSION = array();
session_destroy();

// 生成随机参数绕过缓存
$random = time() . '_' . rand(1000, 9999);

// 重定向到登录页面
header('Location: login.php?r=' . $random);
exit;
?>
