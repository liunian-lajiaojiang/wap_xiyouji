<?php
require_once 'db.php';

header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');

// 使用 Token 验证登录
$user = getCurrentUser();

if ($user) {
    echo json_encode([
        'loggedIn' => true,
        'userId' => $user['id'],
        'username' => $user['username']
    ]);
} else {
    echo json_encode([
        'loggedIn' => false
    ]);
}
?>
