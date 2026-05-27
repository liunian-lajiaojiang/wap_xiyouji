<!DOCTYPE html>
<html lang="zh-cn">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="keywords" content="西游记mud,西游记怀旧mud，西游记h5">
    <meta name="description" content="西游记mud是源自Mud西游记2000的经典还原H5网页文字游戏。">
    <link rel="shortcut icon" href="pic/favicon.ico">
    <link rel="stylesheet" href="css/footer.css">
    <title>赌场_西游记mud</title>
</head>

<body>
    <p>
        <a href="javascript:location.reload();">赌场</a>&ensp;
        <a href="map/maptext/maptext-all-1.html">地图</a>
    </p>
    <hr>
    <br>你来到了：<a href="javascript:location.reload();">赌场</a>
    <p>赌场里面非常宽敞，空气中弥漫着沉重的烟雾气味。赌场里除了轮盘和发牌机，还有各种赌场游戏。这里多是精神紧张、眼神炯炯、神情沉重的赌客。有一些比较高档的赌客，他们通常会下更大的赌注。</p>
    <span>你可以：<br>
        <span>玩：<a href="./casino_game/dudaxiao.php">赌大小</a>&ensp;<a href="./casino_game/stock/index.html">股票大亨</a></span>
        <br>
        <br><a href="casino_out.html">往外走：赌场外</a>
    </span>
    <hr>
    这里有：<a href="humman.html" id="auth-username">加载中...</a>
    <br>
    <p id="demo">&ensp;</p>
    <br>
    <hr>
    <a href="liangong.html">练功</a>
    <a href="#" onclick="Dazuo()">打坐</a>
    <a href="work/work.html">任务</a>
    <a href="chat/chat.html">聊天</a>
    <br>
    <a href="paihang.html">排行</a>
    <a href="bag.html">背包</a>
    <a href="./shop/shop.php">商城</a>
    <a href="bbs/bbs.html">论坛</a>
    <br>
    <a href="friend.html">好友</a>
    <a href="help.html">帮助</a>
    <a href="news.html">公告</a>
    <a href="chongzhi.html">充值</a>

    <style>
        #shibing {
            font-size: 16px;
            color: #F00;
            font-weight: 900;
        }

        #demo {
            color: rgba(255, 114, 0, 1.00)
        }
    </style>
    <div class="back-link">
        当前时间:
        <script src="js/time.js"></script>
        <br>
        <a href="logout.php" class="logout-btn">退出登录</a> | <a href="javascript:window.location.reload();">刷新页面</a> | <a href="about_us.html">关于我们</a>
    </div>
    <script src="js/npc_event.js"></script>
    <script src="js/auth.js"></script>
</body>

</html>