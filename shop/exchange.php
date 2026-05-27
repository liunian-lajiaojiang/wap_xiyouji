<?php
require_once __DIR__ . '/../db.php';

$user = getCurrentUser();
if (!$user) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        header('Content-Type: application/json');
        echo json_encode(['ok' => false, 'msg' => '请先登录']);
        exit;
    }
    redirect('login.php');
}

// 处理 AJAX 请求
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    $data = json_decode(file_get_contents('php://input'), true);
    $action = $data['action'] ?? '';
    $amount = intval($data['amount'] ?? 0);

    if ($amount <= 0) {
        echo json_encode(['ok' => false, 'msg' => '请输入有效数量']);
        exit;
    }

    $pdo = getDbConnection();

    if ($action === 'c2s') {
        // 铜板换银两
        $cost = $amount * 100;
        if ($user['coin'] < $cost) {
            echo json_encode(['ok' => false, 'msg' => '铜板不足']);
            exit;
        }
        $stmt = $pdo->prepare('UPDATE users SET coin = coin - ?, silver = silver + ? WHERE id = ?');
        $stmt->execute([$cost, $amount, $user['id']]);
        echo json_encode(['ok' => true, 'msg' => '兑换成功', 'coin' => $user['coin'] - $cost, 'silver' => $user['silver'] + $amount]);
    } elseif ($action === 's2c') {
        // 银两换铜板
        if ($user['silver'] < $amount) {
            echo json_encode(['ok' => false, 'msg' => '银两不足']);
            exit;
        }
        $gain = $amount * 100;
        $stmt = $pdo->prepare('UPDATE users SET silver = silver - ?, coin = coin + ? WHERE id = ?');
        $stmt->execute([$amount, $gain, $user['id']]);
        echo json_encode(['ok' => true, 'msg' => '兑换成功', 'coin' => $user['coin'] + $gain, 'silver' => $user['silver'] - $amount]);
    } else {
        echo json_encode(['ok' => false, 'msg' => '未知操作']);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>兑换中心</title>
</head>

<body>
    <h1>兑换中心</h1>
    <p>铜板: <strong id="coin"><?= formatMoney($user['coin']) ?></strong></p>
    <p>银两: <strong id="silver"><?= formatMoney($user['silver'] ?? 0) ?></strong></p>

    <p id="msg"></p>
    <span>输入银两数量</span>
    <h2>铜板 → 银两 (100铜板=1银两)</h2>
    <input type="number" id="amount-c2s" min="1" placeholder="输入银两数量">
    <button id="btn-c2s">兑换</button>
    <button id="btn-c2s-max">全部兑换</button>

    <h2>银两 → 铜板 (1银两=100铜板)</h2>
    <input type="number" id="amount-s2c" min="1" placeholder="输入银两数量">
    <button id="btn-s2c">兑换</button>
    <button id="btn-s2c-max">全部兑换</button>

    <hr>
    <a href="shop.php">返回商城</a>

    <script>
        const coinEl = document.getElementById('coin');
        const silverEl = document.getElementById('silver');
        const msgEl = document.getElementById('msg');

        function doExchange(action, amount) {
            msgEl.textContent = '处理中...';
            fetch('exchange.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action,
                        amount
                    })
                })
                .then(r => r.json())
                .then(d => {
                    if (d.ok) {
                        coinEl.textContent = d.coin;
                        silverEl.textContent = d.silver;
                        msgEl.textContent = d.msg;
                    } else {
                        msgEl.textContent = d.msg;
                    }
                })
                .catch(() => msgEl.textContent = '网络错误');
        }

        document.getElementById('btn-c2s').onclick = function() {
            const amt = document.getElementById('amount-c2s').value;
            if (amt > 0) doExchange('c2s', amt);
        };

        // 铜板全部兑换银两
        document.getElementById('btn-c2s-max').onclick = function() {
            const coin = <?= intval($user['coin']) ?>;
            const amt = Math.floor(coin / 100);
            if (amt > 0) {
                document.getElementById('amount-c2s').value = amt;
                doExchange('c2s', amt);
            } else {
                msgEl.textContent = '铜板不足,无法兑换';
            }
        };

        document.getElementById('btn-s2c').onclick = function() {
            const amt = document.getElementById('amount-s2c').value;
            if (amt > 0) doExchange('s2c', amt);
        };

        // 银两全部兑换铜板
        document.getElementById('btn-s2c-max').onclick = function() {
            const silver = <?= intval($user['silver'] ?? 0) ?>;
            if (silver > 0) {
                document.getElementById('amount-s2c').value = silver;
                doExchange('s2c', silver);
            } else {
                msgEl.textContent = '银两不足,无法兑换';
            }
        };
    </script>
</body>

</html>