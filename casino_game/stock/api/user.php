<?php
require_once __DIR__ . '/../config.php';

$method = $_SERVER['REQUEST_METHOD'];
$db = getDB();

switch ($method) {
    case 'GET':
        // 获取用户信息（使用主项目用户表）
        $userId = getCurrentUserId();
        $stmt = $db->prepare("SELECT id, username, silver, created_at FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        if (!$user) {
            error('用户不存在', 401);
        }

        // 计算总资产
        $stmt = $db->prepare("
            SELECT SUM(p.quantity * s.price) as long_value
            FROM positions p
            JOIN stocks s ON p.stock_id = s.id
            WHERE p.user_id = ? AND p.type = 'long'
        ");
        $stmt->execute([$userId]);
        $longValue = $stmt->fetch()['long_value'] ?: 0;

        // 做空盈亏计算
        $stmt = $db->prepare("
            SELECT p.*, s.price as current_price
            FROM positions p
            JOIN stocks s ON p.stock_id = s.id
            WHERE p.user_id = ? AND p.type = 'short'
        ");
        $stmt->execute([$userId]);
        $shortPositions = $stmt->fetchAll();

        $shortValue = 0;
        foreach ($shortPositions as $pos) {
            $shortValue += ($pos['avg_price'] - $pos['current_price']) * $pos['quantity'];
        }

        $user['balance'] = $user['silver'];
        $user['total_assets'] = $user['silver'] + $longValue + $shortValue;
        success(['user' => $user]);
        break;
        
    default:
        error('不支持的请求方法', 405);
}
