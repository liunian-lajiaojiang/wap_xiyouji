<!DOCTYPE html>
<html lang="zh-cn">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="keywords" content="西游记mud,西游记怀旧mud，西游记h5">
    <meta name="description" content="西游记mud是源自Mud西游记2000的经典还原H5网页文字游戏。">
    <link rel="shortcut icon" href="pic/favicon.ico">
    <link rel="stylesheet" href="css/footer.css">
    <title>傲来国北城门_西游记mud</title>
</head>

<body>
    <p><a href="javascript:location.reload();">北城门</a>&ensp;
        <a href="map/maptext/maptext-all-1.html">地图</a>&ensp;
        <a href="./fly/fly.php">飞行</a>
    </p>
    <hr>
    <p id="shibing">"嘿！叫你呢，天黑了快进城，夜魈要出来啦！"士兵对你大声喝道。</p>
    <br>你来到了：<a href="javascript:location.reload();">北城门</a>
    <p>傲来国的北城门，这里有几个威风凌凌的士兵在站岗。快进城吧！</p>
    <span>你可以：<br>
        <br><a href="HJYW.php">↑：荒郊野外</a>
        <br><a href="beiyuanjie.html">↓：北苑街</a>
    </span>
    <hr>
    这里有：<a href="shibing.html">士兵</a> <a href="shibing.html">士兵</a>&ensp;<a href="humman.html" id="auth-username">加载中...</a>
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