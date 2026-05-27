<?php
require_once __DIR__ . '/../config.php';

$method = $_SERVER['REQUEST_METHOD'];
$db = getDB();

switch ($method) {
    case 'GET':
        // 获取所有股票列表
        $stmt = $db->query("
            SELECT 
                id,
                symbol,
                name,
                price,
                previous_price,
                ROUND((price - previous_price) / previous_price * 100, 2) as change_percent,
                volatility
            FROM stocks
            ORDER BY symbol
        ");
        $stocks = $stmt->fetchAll();
        success(['stocks' => $stocks]);
        break;
        
    case 'POST':
        // 更新股票价格（模拟市场波动）
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (isset($data['update_prices'])) {
            try {
                // 尝试调用存储过程
                $db->exec("CALL UpdateStockPrices()");
            } catch (PDOException $e) {
                // 存储过程不存在，使用PHP代码更新
                updatePricesWithPHP($db);
            }
            success(['message' => '价格已更新']);
        } else {
            error('无效的操作');
        }
        break;
        
    default:
        error('不支持的请求方法', 405);
}

// 使用PHP代码更新股票价格（备用方案）
function updatePricesWithPHP($db) {
    // 获取所有股票
    $stmt = $db->query("SELECT id, price, volatility FROM stocks");
    $stocks = $stmt->fetchAll();
    
    foreach ($stocks as $stock) {
        $s_id = $stock['id'];
        $s_price = $stock['price'];
        $s_volatility = $stock['volatility'];
        
        // 生成随机波动 (-1 到 1 之间)
        $change_rate = (mt_rand() / mt_getrandmax() * 2 - 1) * $s_volatility;
        $new_price = $s_price * (1 + $change_rate);
        
        // 确保价格不低于1
        if ($new_price < 1) {
            $new_price = 1;
        }
        
        // 计算高低价
        $s_open = $s_price;
        $s_high = max($s_open, $new_price) * (1 + mt_rand() / mt_getrandmax() * 0.01);
        $s_low = min($s_open, $new_price) * (1 - mt_rand() / mt_getrandmax() * 0.01);
        if ($s_low < 1) {
            $s_low = 1;
        }
        
        // 更新股票价格
        $stmt = $db->prepare("UPDATE stocks SET previous_price = price, price = ? WHERE id = ?");
        $stmt->execute([round($new_price, 2), $s_id]);
        
        // 插入K线数据
        $stmt = $db->prepare("INSERT INTO kline_data (stock_id, period, open_price, high_price, low_price, close_price, volume) VALUES (?, '1min', ?, ?, ?, ?, ?)");
        $stmt->execute([
            $s_id,
            round($s_open, 2),
            round($s_high, 2),
            round($s_low, 2),
            round($new_price, 2),
            mt_rand(1000, 10000)
        ]);
    }
    
    // 收取持仓手续费（每分钟万分之一）
    chargePositionFeesPHP($db);
}

// PHP版本收取持仓手续费
function chargePositionFeesPHP($db) {
    $positionFeeRate = 0.0001; // 万分之一/分钟
    
    // 获取所有持仓
    $stmt = $db->query("
        SELECT p.id, p.user_id, p.stock_id, p.type, p.quantity, s.price
        FROM positions p
        JOIN stocks s ON p.stock_id = s.id
    ");
    $positions = $stmt->fetchAll();
    
    foreach ($positions as $position) {
        $positionValue = $position['quantity'] * $position['price'];
        $fee = round($positionValue * $positionFeeRate, 2);
        
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
                $positionFeeRate
            ]);
        }
    }
}
