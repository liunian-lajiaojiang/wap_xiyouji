<?php
require_once __DIR__ . '/../config.php';

// 持仓费率（每分钟万分之一）
define('POSITION_FEE_RATE', 0.0001);

$method = $_SERVER['REQUEST_METHOD'];
$db = getDB();

switch ($method) {
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        $action = $data['action'] ?? '';
        
        if ($action === 'charge_position_fees') {
            // 收取持仓手续费
            $result = chargePositionFees($db);
            success($result);
        } else {
            error('无效的操作');
        }
        break;
        
    case 'GET':
        // 获取用户的持仓费用记录
        $userId = getCurrentUserId();
        $limit = intval($_GET['limit'] ?? 50);
        
        $stmt = $db->prepare("
            SELECT 
                pf.*,
                s.symbol,
                s.name
            FROM position_fees pf
            JOIN stocks s ON pf.stock_id = s.id
            WHERE pf.user_id = ?
            ORDER BY pf.created_at DESC
            LIMIT ?
        ");
        $stmt->execute([$userId, $limit]);
        $fees = $stmt->fetchAll();
        
        // 获取今日持仓费
        $stmt = $db->prepare("
            SELECT COALESCE(SUM(fee_amount), 0) as total_fees
            FROM position_fees
            WHERE user_id = ? AND DATE(created_at) = CURDATE()
        ");
        $stmt->execute([$userId]);
        $todayPositionFees = $stmt->fetch()['total_fees'];
        
        // 获取今日交易手续费
        $stmt = $db->prepare("
            SELECT COALESCE(SUM(fee), 0) as total_fees
            FROM transactions
            WHERE user_id = ? AND DATE(created_at) = CURDATE()
        ");
        $stmt->execute([$userId]);
        $todayTradeFees = $stmt->fetch()['total_fees'];
        
        // 获取累计持仓费
        $stmt = $db->prepare("
            SELECT COALESCE(SUM(fee_amount), 0) as total_fees
            FROM position_fees
            WHERE user_id = ?
        ");
        $stmt->execute([$userId]);
        $totalPositionFees = $stmt->fetch()['total_fees'];
        
        // 获取累计交易手续费
        $stmt = $db->prepare("
            SELECT COALESCE(SUM(fee), 0) as total_fees
            FROM transactions
            WHERE user_id = ?
        ");
        $stmt->execute([$userId]);
        $totalTradeFees = $stmt->fetch()['total_fees'];
        
        success([
            'fees' => $fees,
            'today_position_fees' => round($todayPositionFees, 2),
            'today_trade_fees' => round($todayTradeFees, 2),
            'today_total_fees' => round($todayPositionFees + $todayTradeFees, 2),
            'total_position_fees' => round($totalPositionFees, 2),
            'total_trade_fees' => round($totalTradeFees, 2),
            'total_fees' => round($totalPositionFees + $totalTradeFees, 2)
        ]);
        break;
        
    default:
        error('不支持的请求方法', 405);
}

// 收取持仓手续费
function chargePositionFees($db) {
    $totalCharged = 0;
    $affectedPositions = 0;
    
    $db->beginTransaction();
    
    try {
        // 获取所有持仓
        $stmt = $db->query("
            SELECT p.id, p.user_id, p.stock_id, p.type, p.quantity, s.price
            FROM positions p
            JOIN stocks s ON p.stock_id = s.id
            FOR UPDATE
        ");
        $positions = $stmt->fetchAll();
        
        foreach ($positions as $position) {
            $positionValue = $position['quantity'] * $position['price'];
            $fee = round($positionValue * POSITION_FEE_RATE, 2);
            
            // 检查用户银两余额
            $stmt = $db->prepare("SELECT silver FROM users WHERE id = ?");
            $stmt->execute([$position['user_id']]);
            $silver = $stmt->fetch()['silver'];

            // 如果余额足够则扣除手续费
            if ($silver >= $fee) {
                // 扣除手续费
                $stmt = $db->prepare("UPDATE users SET silver = silver - ? WHERE id = ?");
                $stmt->execute([$fee, $position['user_id']]);
                
                // 记录手续费
                $stmt = $db->prepare("
                    INSERT INTO position_fees (user_id, stock_id, position_type, position_value, fee_amount, fee_rate)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $position['user_id'],
                    $position['stock_id'],
                    $position['type'],
                    $positionValue,
                    $fee,
                    POSITION_FEE_RATE
                ]);
                
                $totalCharged += $fee;
                $affectedPositions++;
            }
        }
        
        $db->commit();
        
        return [
            'message' => '持仓手续费收取完成',
            'positions_count' => $affectedPositions,
            'total_fees' => round($totalCharged, 2)
        ];
        
    } catch (Exception $e) {
        $db->rollBack();
        error('收取手续费失败: ' . $e->getMessage());
    }
}
