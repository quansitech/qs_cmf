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
<title>跳转提示</title>
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
	<div>
		<div class="system-message">
		<?php if(isset($message)) {?>
		<h1 class="success"><?php echo($message); ?></h1>
		<?php }else{?>
		<h1 class="error"><?php echo($error); ?></h1>
		<?php }?>
		<p class="detail"></p>
		<p class="jump">
		页面将在<b id="wait"><?php echo($waitSecond); ?></b>后自动<a id="href" href="<?php echo($jumpUrl); ?>">跳转</a>。
		</p>
		</div>
	</div>
	<script type="text/javascript">
	(function(){
	var wait = document.getElementById('wait'),href = document.getElementById('href').href;
	var interval = setInterval(function(){
		var time = --wait.innerHTML;
		if(time <= 0) {
			location.href = href;
			clearInterval(interval);
		};
	}, 1000);
	})();
	</script>
</body>
</html>