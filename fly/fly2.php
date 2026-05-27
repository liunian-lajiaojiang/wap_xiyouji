<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Language" content="zh-cn">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no">
    <meta name="keywords" content="西游记mud,西游记怀旧mud，西游记h5" />
    <meta name="description" content="西游记mud是源自Mud西游记2000的经典还原H5网页文字游戏。" />
    <link rel="shortcut icon" href="pic/favicon.ico">
    <link rel="stylesheet" href="css/footer.css">
    <link rel="stylesheet" href="css/fly.css">
    <title>飞行_西游记mud</title>
</head>

<body>
    <p>
        <a href="javascript:location.reload();">飞行</a>&ensp;
        <a href="map/maptext/maptext-all-1.html">地图</a>&ensp;
        <a href="#" onclick="javascript:history.back(-1);">返回</a>
    </p>
    <hr>

    <br>当前你在：<a href="javascript:location.reload();">荒郊野外</a>
    <p>你想飞去哪里？</p>
    <div id="fly-to">
        <table border="0" cellpadding="5" cellspacing="0" style="text-align:left;">
            <tr>
                <td>【隐雾山】</td><td>【竹节山】</td><td>【盘丝洞】</td>
            </tr>
            <tr>
                <td>【毛颖山】</td><td>【黑风洞】</td><td>【云栈】</td>
            </tr>
            <tr>
                <td>【麒麟山】</td><td>【流沙河】</td><td>【鹰愁涧】</td>
            </tr>
            <tr>
                <td>【毒敌山】</td><td>【罗汉塔】</td><td>【青龙山】</td>
            </tr>
            <tr>
                <td>【豹头山】</td><td>【黄风洞】</td><td>【峨嵋山】</td>
            </tr>
            <tr>
                <td>【荆棘岭】</td><td>【五台山】</td><td>【双叉岭】</td>
            </tr>
        </table>
        <br>
        <a href="fly.php">首页</a> <a href="fly.php">上一页</a> <a href="fly2.php" class="disabled">下一页</a> <a href="fly2.php" class="disabled">尾页</a> 第2/2页
    </div>




    <style type="text/css">
        #shibing {
            font-size: 16px;
            color: #F00;
            font-weight: 900;
        }

        #demo {
            color: rgba(255, 114, 0, 1.00)
        }
        
        .disabled {
            color: gray;
            pointer-events: none;
            text-decoration: none;
        }
    </style>
    <div class="back-link">
        当前时间:
        <script src="js/time.js"></script>
        <br>
        <a href="logout.php" class="logout-btn">退出登录</a> | <a href="javascript:window.location.reload();">刷新页面</a> | <a href="about_us.html">关于我们</a>
    </div>
    <script src="js/npc_event.js" type="text/javascript"></script>
    <script src="js/auth.js"></script>
</body>

</html>
