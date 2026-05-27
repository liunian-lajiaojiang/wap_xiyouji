<?php
require_once __DIR__ . '/../config.php';

$method = $_SERVER['REQUEST_METHOD'];
$db = getDB();
$userId = getCurrentUserId();

switch ($method) {
    case 'GET':
        // 获取交易历史
        $limit = intval($_GET['limit'] ?? 50);
        $offset = intval($_GET['offset'] ?? 0);
        
        $stmt = $db->prepare("
            SELECT 
                t.id,
                t.type,
                t.quantity,
                t.price,
                t.total_amount,
                t.profit_loss,
                t.created_at,
                s.symbol,
                s.name
            FROM transactions t
            JOIN stocks s ON t.stock_id = s.id
            WHERE t.user_id = ?
            ORDER BY t.created_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$userId, $limit, $offset]);
        $history = $stmt->fetchAll();
        
        // 获取总记录数
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM transactions WHERE user_id = ?");
        $stmt->execute([$userId]);
        $total = $stmt->fetch()['total'];
        
        success([
            'history' => $history,
            'total' => intval($total),
            'limit' => $limit,
            'offset' => $offset
        ]);
        break;
        
    default:
        error('不支持的请求方法', 405);
}
