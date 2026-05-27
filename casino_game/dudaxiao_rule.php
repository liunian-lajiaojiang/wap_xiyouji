<?php
require_once __DIR__ . '/../db.php';
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
    <a href="./dudaxiao.php">返回</a>
    <h3>📜 游戏规则</h3>
    <ul>
        <li>玩家初始资金为 <?= formatMoney(INITIAL_COIN) ?> 铜板</li>
        <li>骰子点数 1-3 为小, 4-6 为大</li>
        <li>猜对获得下注金额的 <?= ODDS ?> 倍 (本金+利润)</li>
        <li>猜错损失全部下注金额, <strong>不收取任何费用</strong></li>
        <li>猜对赢钱后收取 <strong><?= (COMMISSION_RATE * 100) ?>%</strong> 手续费</li>
        <li>余额耗尽后可联系管理员充值</li>
    </ul>
</body>

</html>