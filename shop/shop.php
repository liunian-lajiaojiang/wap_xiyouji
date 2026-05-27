<?php
require_once __DIR__ . '/../db.php';

$user = getCurrentUser();
if (!$user) {
    redirect('../login.php');
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>商城</title>
</head>
<body>
    <h1>商城</h1>
    <p>铜板: <strong id="coin"><?= formatMoney($user['coin']) ?></strong></p>
    <p>银两: <strong id="silver"><?= formatMoney($user['silver'] ?? 0) ?></strong></p>
    
    <a href="exchange.php">兑换银两</a> | 
    <a href="../HJYW.php">回到傲来国</a>
    
    <hr>
    <a href="javascript:void(0)" id="claim-btn">免费领取10银两</a>
    <p id="msg"></p>
    
    <script>
    const coinEl = document.getElementById('coin');
    const silverEl = document.getElementById('silver');
    const msgEl = document.getElementById('msg');
    const claimBtn = document.getElementById('claim-btn');
    
    claimBtn.onclick = function() {
        claimBtn.disabled = true;
        msgEl.textContent = '领取中...';
        
        fetch('claim.php', { method: 'POST' })
        .then(r => r.json())
        .then(d => {
            if (d.ok) {
                coinEl.textContent = d.coin;
                silverEl.textContent = d.silver;
                msgEl.textContent = '领取成功！';
            } else {
                msgEl.textContent = d.msg;
            }
            claimBtn.disabled = false;
        })
        .catch(() => {
            msgEl.textContent = '网络错误';
            claimBtn.disabled = false;
        });
    };
    </script>
</body>
</html>
