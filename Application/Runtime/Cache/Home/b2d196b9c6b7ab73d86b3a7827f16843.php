<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head lang="en">
<meta charset="UTF-8">
<meta name="keywords " content="E快帮,E快帮ERP,旺店通,ERP,店铺管理,店铺管理ERP"/>
<title><?php echo C('SYSTEM_NAME');?>-欢迎登录</title>
<link rel="stylesheet" type="text/css" href="/Public/Css/easyui.css"/>
<link rel="stylesheet" type="text/css" href="/Public/Css/icon.css"/>
<link rel="stylesheet" type="text/css" href="/Public/Css/login.css?v=<?php echo ($version_number); ?>">
<link rel="icon" href="/Public/Image/favicon.ico"  type="image/x-icon">
<script type="text/javascript" src="/Public/Js/jquery.min.js"></script>
<script type="text/javascript" src="/Public/Js/jquery.md5.js"></script>
<script type="text/javascript" src="/Public/Js/jquery.cookie.js"></script>
<!--[if lt IE 9]>
<link rel="stylesheet" type="text/css" href="/Public/Css/login.2.css">
<script type="text/javascript" src="/Public/Js/json.2.js"></script>
<![endif]-->
<script type="text/javascript" src="/Public/Js/login.util.js"></script>
<script type="text/javascript" src="/Public/Js/jquery.easyui.min.js"></script>
<script type="text/javascript" src="/Public/Js/easyui.extends.js"></script>
<script type="text/javascript" src="/Public/Js/easyui-lang-zh_CN.js"></script>
</head>
<style>
/*#reset-content .reset-body .cell1 {*/
	/*position: relative;*/
	/*float: right;*/
	/*right: 10px;*/
	/*top: 10px;*/
/*}*/
/*#reset-content .reset-body .cell1>a{*/
	/*font-size:14px;*/
	/*font-weight: bold;*/
	/*color:#babec2;*/
/*}*/
/*#reset-content .reset-body .cell1>a:focus,#reset-content .reset-body .cell1>a:hover{*/
	/*color:blue;*/
	/*text-decoration: underline;*/
/*}*/
#ret-top{width: 894px;margin-left: auto;margin-right: auto;margin-top: 20px;}
#ret-logo{overflow: hidden;}
#ret-logo a:first-of-type {float: left; width: 100px; height: 30px}
#ret-logo a:nth-of-type(2) {float: left;font-family: SourceHanSansCN-Normal;font-size: 14px;font-weight: normal;font-stretch: normal;line-height: 26px;letter-spacing: 0px;color: #999999; margin-top:14px;margin-left:12px;}
#ret-logo a:nth-of-type(3) {float: right;
	font-family: AdobeHeitiStd-Regular;
	font-size: 14px;
	font-weight: normal;
	font-stretch: normal;
	line-height: 26px;
	letter-spacing: 0px;
	color: #31ac7f;margin-top:14px;}
#reset-content{width: 900px;height: 530px;background-color: #ffffff;box-shadow: 0px 5px 19px 1px rgba(177, 187, 184, 0.28);margin-left: auto;margin-right: auto;margin-top: 20px;}
.ret-content-top{width: 900px;
	height: 60px;
	background-image: linear-gradient(87deg,
	#2ac38a 11%,
	#2eb4a7 43%,
	#2d9db5 100%),
	linear-gradient(
			#31ac7f,
			#31ac7f);
	background-blend-mode: normal,
	normal;text-align: center;}
.ret-content-top span{position: relative;top:18px;width: 79px;
	height: 19px;
	font-family: SourceHanSansCN-Regular;
	font-size: 20px;
	font-weight: normal;
	font-stretch: normal;
	line-height: 26px;
	letter-spacing: 0px;
	color: #ffffff;}

	.ret-cell{
		margin-left: auto;
		margin-right: auto;
		width: 301px;
		height:40px;
	}
	/*.ret-cell:nth-of-type(3) i{*/
		/*background-image: url("/Public/Image/Login/login_icon_Cell-phonenumber.png");*/
	/*}*/
	/*.ret-cell:nth-of-type(4) i{*/
		/*background-image: url("/Public/Image/Login/login_icon_VerificationCode.png");*/
	/*}*/
	.ret-login-bottom {
		position: relative;
		bottom:50px;
		left:0;
		right:0;
		margin:auto;
	}
	/*.ret-cell:nth-of-type(3) a{*/
		/*position: absolute;*/
		/*top:0;*/
		/*right: 0;*/
		/*border-radius: 0 6px 6px 0;*/
		/*width: 89px;*/
		/*height: 38px;*/
		/*border: solid 1px #dddddd;*/
		/*font-family: SourceHanSansCN-Normal;*/
		/*font-size: 14px;*/
		/*font-weight: normal;*/
		/*font-stretch: normal;*/
		/*line-height: 38px;*/
		/*letter-spacing: 0px;*/
		/*color: #31ac7f;*/
		/*text-align: center;*/
	/*}*/
	/*.ret-cell:nth-of-type(3) input{*/
		/*padding:6px 95px 6px 45px;*/
	/*}*/
	.ret-login-form div:nth-of-type(1) {position: relative;margin-left: 35%;margin-right: auto;margin-top: 160px;width: 301px;height: 38px;line-height: 38px;text-align: center;font-size: 18px;}
	.ret-login-form div i{position: absolute;
		top:0;
		left: 0;
		border-radius: 6px 0 0 6px;
		background-image: url('/Public/Image/Login/complete.png');
		background-repeat: no-repeat;
		background-position: center;
		width: 38px;
		height: 38px;
		}
</style>
<body>
<div id="ret-top">
	<div id="ret-logo">
		<a href="/index.php/home/login/login.html">
		<img alt="E快帮" src="/Public/Image/Login/login_logo_green.png" >
		</a>
		<a href="/index.php/home/login/login.html" >网上开店 找E快帮</a>
		<a href="<?php echo U('Home/Login/login');?>?type=logout">登录</a>
	</div>
</div>
<div id="reset-content">
	<div class="ret-content-top">
		<span>设置新密码</span>
	</div>
	<div class="ret-login-form">
		<div><i></i>修改成功，请牢记登录密码</div>
		<div class="cell2 ret-cell"><a id="submit" href="#" class="button">登录</a></div>
	</div>
</div>
<div class="ret-login-bottom"><div class="copyright">©版权所有E快帮  服务热线:400-010-1039</div></div>
<script type="text/javascript">
$(function(){
	//检测浏览器版本
	check_navigator();
	function check_navigator(){
		var userAgent=navigator.userAgent.toLowerCase();
		var appVersion=navigator.appVersion.toLowerCase();
		var status=1;
		if (!!window.ActiveXObject || "ActiveXObject" in window){
			if(appVersion.match(/msie 8./i)=="msie 8."){
				status=0;
			}else if(appVersion.match(/msie 7./i)=="msie 7."){
				status=0;
			}else if(appVersion.match(/msie 5./i)=="msie 5."){
				status=0;
			}
		}
		if(status==0){
			alert('当前浏览器版本无法正常使用本系统，请更换谷歌浏览器！');
		}
	}
});
function checkResetInfo(){
	var form=$("#resetform");
	if(!form.form('validate')){return;}
	var data=form.form('get');
	$('.ret-msg').html('<span style="color:orange;">修改密码中...</span>');
	$.post("<?php echo U('Home/Login/checkResetInfo');?>",{data:JSON.stringify(data)},function(res){
		if(res.status==1){
			$('.ret-msg').html(res.info);return false;
		}else{
			$('.ret-msg').html('<span style="color:green;">修改成功，正在登录...</span>');
			window.location.href = res.info;
		}
	});
}
function sendCode(){
	var form=$("#resetform");
	var data=form.form('get');
	if((!data.account)||(!data.sid)||(!data.mobile_no)){$('.ret-msg').html('用户名、卖家账号、手机号都不能为空！');return;}
	if(!/^[a-zA-Z0-9_]{1,}$/.test(data.account)){$('.ret-msg').html('用户名格式不正确！');return;}
	if(!/^[a-zA-Z0-9_]{1,}$/.test(data.sid)){$('.ret-msg').html('卖家账号格式不正确！');return;}
	if(!/^(?:13\d|15\d|18\d|17\d|14\d)-?\d{5}(\d{3}|\*{3})$/.test(data.mobile_no)){$('.ret-msg').html('手机号格式不正确！');return;}

	$.post("<?php echo U('Home/Login/sendResetCode');?>",{data:JSON.stringify(data)},function(res){
		if(res.status==1){
			$('.ret-msg').html(res.info);return false;
		}else{
			setTime.send=true;
			$('.ret-msg').html('<span style="color:green;">'+res.info+'</span>');
			setTime.init($('.sendCode a'),$('.sendCode .l-btn-text'));
		}
	});

}
var setTime=function(){
	return{
		node_but:null,
		node_txt:null,
		count:60,
		send:false,
		start:function(){
			if(this.count>0){
				this.count--;
				this.node_txt.text(this.count+'秒后可重发');
				var that = this;
	            setTimeout(function(){ that.start(); },1000);
			}else{
				 this.count = 60;
				 this.node_txt.text('发送验证码');
				 $('.ret-msg').html('');
				 this.node_but.linkbutton('enable');
			}
		},
		init:function(but,txt){
			this.node_but=but;
			this.node_txt=txt;
			this.node_but.linkbutton('disable');
			this.start();
		}
	};
}();
</script>
</body>
</html>