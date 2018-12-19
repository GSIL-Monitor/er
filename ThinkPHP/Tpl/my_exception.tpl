<?php
    if(C('LAYOUT_ON')) {
        echo '{__NOLAYOUT__}';
    }
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="utf-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" href="__ROOT__/Public/Css/error.css">
<title>500 Not Found</title>
</head>
<body class="err-body">
<div id="err-page">
	<div class="err-h1">抱歉，找不到此页面~500</div>
	<div class="err-h2">Sorry, the site now can not be accessed. </div>
	<font color="#666666"><?php echo strip_tags($e['message']);?></font><br/><br/>
	<div class="err-but">
		<a href="<?php echo __ROOT__.'/index.php'?>" title="E快帮" target="_top">点击进入主页面吧</a>
	</div>
</div>
</body>
</html>