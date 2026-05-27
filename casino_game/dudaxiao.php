<?php

/**
 * 赌大小游戏主页面
 */

require_once __DIR__ . '/../db.php';

// 需要登录才能访问
if (!isLoggedIn()) {
    redirect('/../login.php');
}

$user = getCurrentUser();
$error = '';
$success = '';
$result = null;

// 处理下注请求
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bet'])) {
    // 验证CSRF Token
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = '无效的请求,请重试';
    } else {
        $betAmount = intval($_POST['bet_amount'] ?? 0);
        $betChoice = $_POST['bet_choice'] ?? '';

        // 验证下注金额
        if ($betAmount <= 0) {
            $error = '请输入有效的下注金额';
        } elseif ($betAmount > $user['coin']) {
            $error = '余额不足,当前余额: ' . formatMoney($user['coin']) . ' 铜板';
        } elseif (!in_array($betChoice, ['大', '小'])) {
            $error = '请选择大或小';
        } else {
            // 执行掷骰子
            $diceResult = rollDice();
            $diceCategory = getDiceCategory($diceResult);
            $isWin = ($betChoice === $diceCategory);

            // 计算输赢和手续费
            if ($isWin) {
                $winAmount = $betAmount * ODDS;
                $commission = (int)($winAmount * COMMISSION_RATE);
                $netWin = $winAmount - $commission;
                $coinChange = $netWin;
            } else {
                $winAmount = 0;
                $commission = 0;
                $netWin = 0;
                $coinChange = -$betAmount;
            }

            // 更新余额
            updateCoin($user['id'], $coinChange);

            // 获取更新后的余额
            $newUser = getUserById($user['id']);
            $newCoin = $newUser['coin'];

            // 记录下注历史
            recordBet(
                $user['id'],
                $betAmount,
                $betChoice,
                $diceResult,
                $isWin,
                $winAmount,
                $commission,
                $newCoin
            );

            // 更新本地用户数据
            $user['coin'] = $newCoin;

            // 设置结果消息
            if ($isWin) {
                $success = "🎲 掷出: <strong>{$diceResult}点({$diceCategory})</strong><br>" .
                    "✅ 恭喜! 您押了【{$betChoice}】,中了!<br>" .
                    "赢得: " . formatMoney($winAmount) . " 铜板 | 手续费: " . formatMoney($commission) . " 铜板<br>" .
                    "实际获得: <strong>+" . intval($netWin) . " 铜板</strong>";
            } else {
                $error = "🎲 掷出: <strong>{$diceResult}点({$diceCategory})</strong><br>" .
                    "❌ 可惜! 您押了【{$betChoice}】,输了<br>" .
                    "-" . formatMoney($betAmount) . " 铜板";
            }

            $result = [
                'dice' => $diceResult,
                'category' => $diceCategory,
                'choice' => $betChoice,
                'is_win' => $isWin
            ];
        }
    }
}

// 获取下注历史
$history = getBetHistory($user['id'], 10);

// 检查余额是否耗尽
if ($user['coin'] <= 0) {
    $error = "💸 铜板不足! 请前往 <a href='../shop/shop.php'>商城</a>  兑换";
}

// 获取闪光消息
$flash = getFlash();
if ($flash && empty($error) && empty($success)) {
    if ($flash['type'] === 'success') {
        $success = $flash['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="zh-cn">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="keywords" content="西游记mud,西游记怀旧mud，西游记h5">
    <meta name="description" content="西游记mud是源自Mud西游记2000的经典还原H5网页文字游戏。">
    <link rel="shortcut icon" href="pic/favicon.ico">
    <link rel="stylesheet" href="css/footer.css">
    <title>赌大小_西游记mud</title>
</head>

<body>
    <p>
        <a href="javascript:location.reload();">🎲赌大小</a>&ensp;
        <a href="../casino.php">返回</a>&ensp;
        <a href="./dudaxiao_rule.php">规则</a>&ensp;
        <a href="./dudaxiao_history.php">下注历史</a>
    </p>
    <p>💰还有铜板: <span id="coin"><?= formatMoney($user['coin']) ?></span></p>
    <p id="countdown" style="color:#dc3545;font-weight:bold;display:none;"></p>

    <?php if ($success): ?>
        <p><?= $success ?></p>
    <?php endif; ?>

    <?php if ($error): ?>
        <p><?= $error ?></p>
    <?php endif; ?>

    <form method="POST" id="betForm">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCSRFToken()) ?>">
        <input type="hidden" name="bet" value="1">
        <br>
        <span style="font-weight: bold;">选择押注</span>
        <br>
            <label><input type="radio" name="bet_choice" value="大" required>大(4-6点)</label>
            <label><input type="radio" name="bet_choice" value="小" required>小(1-3点)</label>
            <br>
            <br>
            <span style="font-weight: bold;">下注金额</span>
            <br>
            <button type="button" onclick="setAmount(10)">10</button>
            <button type="button" onclick="setAmount(50)">50</button>
            <button type="button" onclick="setAmount(100)">100</button>
            <button type="button" onclick="setAmount(<?= (int)$user['coin'] ?>)">全押</button>
            <br><br>
            <input type="number" name="bet_amount" id="betAmount"
                placeholder="输入金额" min="1" max="<?= $user['coin'] ?>" required>

            <button type="submit" id="betBtn" <?= $user['coin'] <= 0 ? 'disabled' : '' ?>>
                🎲 开始下注
            </button>

            <p>赔率 1:1 | 手续费 <?= (COMMISSION_RATE * 100) ?>%</p>
    </form>
    <br>
    <script>
        const WAIT_TIME = 10; // 等待时间（秒）

        function setAmount(amount) {
            document.getElementById('betAmount').value = amount;
        }

        document.getElementById('betForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const betBtn = document.getElementById('betBtn');
            betBtn.disabled = true;
            betBtn.textContent = '🎲 等待开结果...';

            // 开始倒计时
            startCountdown(WAIT_TIME);
        });

        function startCountdown(seconds) {
            const countdownEl = document.getElementById('countdown');
            countdownEl.style.display = 'block';
            updateCountdown(seconds);
        }

        function updateCountdown(remaining) {
            const countdownEl = document.getElementById('countdown');
            const betBtn = document.getElementById('betBtn');
            if (remaining > 0) {
                countdownEl.innerHTML = '🎲 <strong style="font-size:1.2em;">' + remaining + '</strong> 秒后开骰子...';
                setTimeout(() => updateCountdown(remaining - 1), 1000);
            } else {
                countdownEl.textContent = '🎲 结果揭晓中...';
                setTimeout(() => {
                    document.getElementById('betForm').submit();
                }, 500);
            }
        }
    </script>
    <div class="back-link">
        当前时间:
        <script src="../js/time.js"></script>
        <br>
        <a href="../logout.php" class="logout-btn">退出登录</a>
    </div>


</body>

</html>