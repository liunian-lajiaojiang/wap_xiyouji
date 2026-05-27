<?php
require_once __DIR__ . '/../config.php';

// 手续费配置
define('TRADE_FEE_RATE', 0.001); // 交易手续费率 0.1%

$method = $_SERVER['REQUEST_METHOD'];
$db = getDB();
$userId = getCurrentUserId();

switch ($method) {
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        $action = $data['action'] ?? '';
        $stockId = $data['stock_id'] ?? 0;
        $quantity = intval($data['quantity'] ?? 0);
        
        if ($stockId <= 0 || $quantity <= 0) {
            error('无效的股票ID或数量');
        }
        
        // 获取股票信息
        $stmt = $db->prepare("SELECT * FROM stocks WHERE id = ?");
        $stmt->execute([$stockId]);
        $stock = $stmt->fetch();
        
        if (!$stock) {
            error('股票不存在');
        }
        
        $price = $stock['price'];
        $totalAmount = $price * $quantity;
        
        $db->beginTransaction();
        
        try {
            switch ($action) {
                case 'buy':
                    // 做多买入
                    $fee = round($totalAmount * TRADE_FEE_RATE, 2); // 计算手续费
                    $totalCost = $totalAmount + $fee;
                    
                    $stmt = $db->prepare("SELECT silver FROM users WHERE id = ? FOR UPDATE");
                    $stmt->execute([$userId]);
                    $silver = $stmt->fetch()['silver'];
                    
                    if ($silver < $totalCost) {
                        throw new Exception('💸 银两不足! 请前往 <a href="../../shop/shop.php">商城</a> 兑换（含手续费 $' . $fee . '）');
                    }
                    
                    // 扣除银两（含手续费）
                    $stmt = $db->prepare("UPDATE users SET silver = silver - ? WHERE id = ?");
                    $stmt->execute([$totalCost, $userId]);
                    
                    // 更新或创建持仓
                    $stmt = $db->prepare("
                        INSERT INTO positions (user_id, stock_id, type, quantity, avg_price)
                        VALUES (?, ?, 'long', ?, ?)
                        ON DUPLICATE KEY UPDATE
                        avg_price = (avg_price * quantity + ? * ?) / (quantity + ?),
                        quantity = quantity + ?
                    ");
                    $stmt->execute([$userId, $stockId, $quantity, $price, $price, $quantity, $quantity, $quantity]);
                    
                    // 记录交易（含手续费）
                    $stmt = $db->prepare("
                        INSERT INTO transactions (user_id, stock_id, type, quantity, price, total_amount, fee)
                        VALUES (?, ?, 'buy', ?, ?, ?, ?)
                    ");
                    $stmt->execute([$userId, $stockId, $quantity, $price, $totalAmount, $fee]);
                    
                    $db->commit();
                    success(['message' => '买入成功（手续费 $' . $fee . '）', 'type' => '做多', 'fee' => $fee]);
                    break;
                    
                case 'sell':
                    // 卖出做多持仓
                    $stmt = $db->prepare("
                        SELECT * FROM positions 
                        WHERE user_id = ? AND stock_id = ? AND type = 'long' FOR UPDATE
                    ");
                    $stmt->execute([$userId, $stockId]);
                    $position = $stmt->fetch();
                    
                    if (!$position || $position['quantity'] < $quantity) {
                        throw new Exception('持仓不足');
                    }
                    
                    // 计算盈亏和手续费
                    $profitLoss = ($price - $position['avg_price']) * $quantity;
                    $totalAmount = $price * $quantity;
                    $fee = round($totalAmount * TRADE_FEE_RATE, 2);
                    $netAmount = $totalAmount - $fee;
                    
                    // 增加银两（扣除手续费）
                    $stmt = $db->prepare("UPDATE users SET silver = silver + ? WHERE id = ?");
                    $stmt->execute([$netAmount, $userId]);
                    
                    // 更新持仓
                    if ($position['quantity'] == $quantity) {
                        $stmt = $db->prepare("DELETE FROM positions WHERE id = ?");
                        $stmt->execute([$position['id']]);
                    } else {
                        $stmt = $db->prepare("UPDATE positions SET quantity = quantity - ? WHERE id = ?");
                        $stmt->execute([$quantity, $position['id']]);
                    }
                    
                    // 记录交易（含手续费）
                    $stmt = $db->prepare("
                        INSERT INTO transactions (user_id, stock_id, type, quantity, price, total_amount, profit_loss, fee)
                        VALUES (?, ?, 'sell', ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([$userId, $stockId, $quantity, $price, $totalAmount, $profitLoss, $fee]);
                    
                    $db->commit();
                    success(['message' => '卖出成功（手续费 $' . $fee . '）', 'profit_loss' => $profitLoss, 'fee' => $fee]);
                    break;
                    
                case 'short':
                    // 做空（借股票卖出，价格下跌后买回获利）
                    $margin = $totalAmount * 0.5; // 50%保证金
                    $fee = round($totalAmount * TRADE_FEE_RATE, 2); // 手续费
                    $totalCost = $margin + $fee;
                    
                    $stmt = $db->prepare("SELECT silver FROM users WHERE id = ? FOR UPDATE");
                    $stmt->execute([$userId]);
                    $silver = $stmt->fetch()['silver'];
                    
                    if ($silver < $totalCost) {
                        throw new Exception('💸 银两不足! 请前往 <a href="../../shop/shop.php">商城</a> 兑换（需要50%保证金 + 手续费 $' . $fee . '）');
                    }
                    
                    // 扣除保证金和手续费
                    $stmt = $db->prepare("UPDATE users SET silver = silver - ? WHERE id = ?");
                    $stmt->execute([$totalCost, $userId]);
                    
                    // 创建做空持仓
                    $stmt = $db->prepare("
                        INSERT INTO positions (user_id, stock_id, type, quantity, avg_price)
                        VALUES (?, ?, 'short', ?, ?)
                        ON DUPLICATE KEY UPDATE
                        avg_price = (avg_price * quantity + ? * ?) / (quantity + ?),
                        quantity = quantity + ?
                    ");
                    $stmt->execute([$userId, $stockId, $quantity, $price, $price, $quantity, $quantity, $quantity]);
                    
                    // 记录交易（含手续费）
                    $stmt = $db->prepare("
                        INSERT INTO transactions (user_id, stock_id, type, quantity, price, total_amount, fee)
                        VALUES (?, ?, 'short', ?, ?, ?, ?)
                    ");
                    $stmt->execute([$userId, $stockId, $quantity, $price, $totalAmount, $fee]);
                    
                    $db->commit();
                    success(['message' => '做空成功（手续费 $' . $fee . '）', 'type' => '做空', 'margin' => $margin, 'fee' => $fee]);
                    break;
                    
                case 'cover':
                    // 平仓（买回股票归还）
                    $stmt = $db->prepare("
                        SELECT * FROM positions 
                        WHERE user_id = ? AND stock_id = ? AND type = 'short' FOR UPDATE
                    ");
                    $stmt->execute([$userId, $stockId]);
                    $position = $stmt->fetch();
                    
                    if (!$position || $position['quantity'] < $quantity) {
                        throw new Exception('做空持仓不足');
                    }
                    
                    // 计算盈亏和手续费
                    $profitLoss = ($position['avg_price'] - $price) * $quantity;
                    $coverAmount = $price * $quantity;
                    $fee = round($coverAmount * TRADE_FEE_RATE, 2);
                    $originalMargin = $position['avg_price'] * $quantity * 0.5;
                    
                    // 返还保证金 + 盈亏 - 手续费
                    $returnAmount = $originalMargin + $profitLoss - $fee;
                    $stmt = $db->prepare("UPDATE users SET silver = silver + ? WHERE id = ?");
                    $stmt->execute([$returnAmount, $userId]);
                    
                    // 更新持仓
                    if ($position['quantity'] == $quantity) {
                        $stmt = $db->prepare("DELETE FROM positions WHERE id = ?");
                        $stmt->execute([$position['id']]);
                    } else {
                        $stmt = $db->prepare("UPDATE positions SET quantity = quantity - ? WHERE id = ?");
                        $stmt->execute([$quantity, $position['id']]);
                    }
                    
                    // 记录交易（含手续费）
                    $stmt = $db->prepare("
                        INSERT INTO transactions (user_id, stock_id, type, quantity, price, total_amount, profit_loss, fee)
                        VALUES (?, ?, 'cover', ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([$userId, $stockId, $quantity, $price, $coverAmount, $profitLoss, $fee]);
                    
                    $db->commit();
                    success(['message' => '平仓成功（手续费 $' . $fee . '）', 'profit_loss' => $profitLoss, 'fee' => $fee]);
                    break;
                    
                default:
                    throw new Exception('未知的交易类型');
            }
        } catch (Exception $e) {
            $db->rollBack();
            error($e->getMessage());
        }
        break;
        
    default:
        error('不支持的请求方法', 405);
}
