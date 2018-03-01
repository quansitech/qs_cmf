<?php
    if(C('LAYOUT_ON')) {
        echo '{__NOLAYOUT__}';
    }
?>
<!DOCTYPE HTML>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
<!-- 百度禁止转码 -->
<meta http-equiv="Cache-Control" content="no-siteapp" />
<!-- 是否开启webapp全屏模式 -->
<meta name="apple-mobile-web-app-capable" content="yes">
<!-- 设置状态栏背景颜色 -->
<meta name="apple-mobile-web-app-status-bar-style" content="black">
<!-- 忽略页面中的数字识别为电话 -->
<meta name="format-detection" content="telephone=no">
<title><?php echo $e['message'] ?></title>
<style>
*{
	margin: 0;
	padding: 0;
}
html{
	height: 100%;
}
body{
	font: 14px/1.4286 arial,"Microsoft Yahei";
  	color: #333;
  	background-color: #fff;
	display: -webkit-box;  /* 老版本语法: Safari,  iOS, Android browser, older WebKit browsers.  */
	  display: -moz-box;    /* 老版本语法: Firefox (buggy) */ 
	  display: -ms-flexbox;  /* 混合版本语法: IE 10 */
	  display: -webkit-flex;  /* 新版本语法： Chrome 21+ */
	  display: flex;       /* 新版本语法： Opera 12.1, Firefox 22+ */

	  /*垂直居中*/	
	  /*老版本语法*/
	  -webkit-box-align: center; 
	  -moz-box-align: center;
	  /*混合版本语法*/
	  -ms-flex-align: center; 
	  /*新版本语法*/
	  -webkit-align-items: center;
	  align-items: center;

	  /*水平居中*/
	  /*老版本语法*/
	  -webkit-box-pack: center; 
	  -moz-box-pack: center; 
	  /*混合版本语法*/
	  -ms-flex-pack: center; 
	  /*新版本语法*/
	  -webkit-justify-content: center;
	  justify-content: center;

	  margin: 0;
	  height: 100%;
	  width: 100%; /* needed for Firefox */
}
body>div{
	text-align: center;
}
h1{
	font-size: 20px;
	margin-bottom: 20px;
	margin-top: 20px;
}
img{
	width: 180px;
	height: auto;
}
</style>
</head>
<body>
<?php if(APP_DEBUG): //如果启用debug模式，则显示默认错误信息 ?>
<div class="error">
<p class="face">:(</p>
<h1><?php echo strip_tags($e['message']);?></h1>
<div class="content">
<?php if(isset($e['file'])) {?>
	<div class="info">
		<div class="title">
			<h3>错误位置</h3>
		</div>
		<div class="text">
			<p>FILE: <?php echo $e['file'] ;?> &#12288;LINE: <?php echo $e['line'];?></p>
		</div>
	</div>
<?php }?>
<?php if(isset($e['trace'])) {?>
	<div class="info">
		<div class="title">
			<h3>TRACE</h3>
		</div>
		<div class="text">
			<p><?php echo nl2br($e['trace']);?></p>
		</div>
	</div>
<?php }?>
</div>
</div>
<?php else: //如果不启用debug模式，则显示个性化的错误页面 ?>
<?php \Think\Log::write($e['message']); ?>
	<div>
		<div class="system-message">
		<h1><?php echo $e['message'] ?></h1>
		<p><a href="/">返回首页</a> | <a href="javascript:history.go(-1)">返回上一页</a></p>
		</div>
	</div>
<?php endif; ?>
</body>
</html>
