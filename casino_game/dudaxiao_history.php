<?php

/**
 * 赌大小下注历史页面
 */

require_once __DIR__ . '/../db.php';

// 需要登录才能访问
if (!isLoggedIn()) {
    redirect('/../login.php');
}

$user = getCurrentUser();

// 获取下注历史
$history = getBetHistory($user['id'], 50);
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
    <title>下注历史_西游记mud</title>
</head>

<body>
    <p>
        <a href="./dudaxiao.php">返回</a>
    </p>
    <p>💰还有铜板: <?= formatMoney($user['coin']) ?></p>

    <h3>📊 下注记录</h3>
    <?php if (!empty($history)): ?>
        <table border="1">
            <thead>
                <tr>
                    <th>时间</th>
                    <th>下注</th>
                    <th>骰子</th>
                    <th>结果</th>
                    <th>赢得</th>
                    <th>手续费</th>
                    <th>净得</th>
                    <th>余额</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($history as $record): ?>
                    <tr>
                        <td><?= date('m-d H:i', strtotime($record['created_at'])) ?></td>
                        <td><?= $record['bet_choice'] === 'big' ? '大' : '小' ?></td>
                        <td><?= $record['dice_result'] ?>点</td>
                        <td><?= $record['is_win'] ? '赢' : '输' ?></td>
                        <td><?= $record['is_win'] ? '+' . formatMoney($record['win_amount']) : '-' . formatMoney($record['bet_amount']) ?></td>
                        <td><?= formatMoney($record['commission']) ?></td>
                        <td><?= $record['is_win'] ? '+' . formatMoney($record['win_amount'] - $record['commission']) : '-' . formatMoney($record['bet_amount']) ?></td>
                        <td><?= formatMoney($record['coin_after']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>暂无下注记录</p>
    <?php endif; ?>
</body>

</html>
