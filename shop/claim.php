<?php
require_once __DIR__ . '/../db.php';

header('Content-Type: application/json');

$user = getCurrentUser();
if (!$user) {
    echo json_encode(['ok' => false, 'msg' => '请先登录']);
    exit;
}

$pdo = getDbConnection();
$stmt = $pdo->prepare('UPDATE users SET silver = silver + 10 WHERE id = ?');
$stmt->execute([$user['id']]);

$stmt = $pdo->prepare('SELECT coin, silver FROM users WHERE id = ?');
$stmt->execute([$user['id']]);
$row = $stmt->fetch();

echo json_encode([
    'ok' => true,
    'coin' => $row['coin'],
    'silver' => $row['silver']
]);
