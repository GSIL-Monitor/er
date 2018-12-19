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
	.ret-cell:nth-of-type(3) i{
		background-image: url("/Public/Image/Login/login_icon_Cell-phonenumber.png");
	}
	.ret-cell:nth-of-type(4) i{
		background-image: url("/Public/Image/Login/login_icon_VerificationCode.png");
	}
	.ret-login-bottom {
		position: relative;
		bottom:50px;
		left:0;
		right:0;
		margin:auto;
	}
	.ret-cell:nth-of-type(3) a{
		position: absolute;
		top:0;
		right: 0;
		border-radius: 0 6px 6px 0;
		width: 89px;
		height: 38px;
		border: solid 1px #dddddd;
		font-family: SourceHanSansCN-Normal;
		font-size: 14px;
		font-weight: normal;
		font-stretch: normal;
		line-height: 38px;
		letter-spacing: 0px;
		color: #31ac7f;
		text-align: center;
	}
	.ret-cell:nth-of-type(3) input{
		padding:6px 95px 6px 45px;
	}
	/*.ret-login-form{position: relative;}*/
	/*.ret-msg{position: absolute;*/
		/*!*background-color: #0a8ebb;*!*/
		/*width: 300px;*/
		/*height: 60px;*/
		/*top: 30px;*/
		/*left:630px;*/
		/*z-index: 10;*/
		/*border: 1px;*/
	/*}*/
	/*.ret-msg i{*/
		/*position: absolute;*/
		/*top:0;*/
		/*left: 0;*/
		/*background-image: url('/Public/Image/Login/promptbox.png');*/
		/*background-repeat: no-repeat;*/
		/*background-position: center;*/
	/*}*/
	.ret-msg{display:block;position: relative;top: -40px;color:#ff0000;
		left:320px;width: 309px;height: 49px;background-image: url('/Public/Image/Login/promptbox.png');background-repeat: no-repeat;background-position: center;overflow: hidden;background-size: 309px 49px;box-sizing: border-box;padding: 5px 15px 5px 20px;word-break: break-all;    white-space: nowrap;
		text-overflow: ellipsis;}
		input:focus {outline:none;border: 1px solid red;}
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
	<!--<div id="logo"><a href="/index.php/home/login/login.html"><img alt="E快帮" src="/Public/Image/Login/login-logo.png" border="0"></a></div>-->
</div>
<div id="reset-content">
	<div class="ret-content-top">
		<span>输入账号</span>
	</div>
	<div class="ret-login-form" id="ret-login-form">
		<div class="cell ret-cell"><i class="input-icon"></i><input class="text" type="text" id="sid" name="sid" maxlength="25" placeholder="卖家账号"/><p class="ret-msg">ssssss啊撒啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊死死死死死死死死死死死死死死死死死</p></div>
		<div class="cell ret-cell"><i class="input-icon"></i><input class="text" type="text" id="account" name="account" maxlength="30" placeholder="用户名"/><p class="ret-msg">sss</p></div>
		<div class="cell ret-cell"><i class="input-icon"></i><input class="text" type="text" id="mobile_no" name="mobile_no" maxlength="30" placeholder="手机号"/><a href="javascript:void(0)" onclick="sendCode();" >获取验证码</a><p class="ret-msg">sss</p></div>
		<div class="cell ret-cell"><i class="input-icon"></i><input class="text" type="text" id="code" name="code" maxlength="30" placeholder="验证码"/><p class="ret-msg">sss</p></div>
		<div class="cell2 ret-cell"><a id="submit" href="<?php echo U('Home/Login/reset_pwd');?>" class="button">下一步</a></div>
		<!--<div class="ret-msg"><i></i>哈哈</div>-->
	</div>

	<!--<div class="reset-body">-->
		<!--<div class="cell1"><a href="<?php echo U('Home/Login/login');?>?type=logout">立即登录</a></div>-->
		<!--<h3 class="reset-title">重置密码</h3>-->
		<!--<p>（未绑定手机号的用户，请联系管理员进行密码重置）</p>-->
		<!--<form id="resetform" method="post">-->
			<!--<div class="reset-form">-->
				<!--<div class="ret-form-cell">-->
					<!--<label class="ret-cell1" for="account">用户名：</label>-->
					<!--<input class="easyui-textbox txt"-->
							<!--type="text" name="account" id="account"-->
							<!--style="width:300px;height:30px;"-->
							<!--data-options="required:true,validType:'englishOrNum'"-->
							<!--missingMessage="不能为空" />-->
				<!--</div>-->
				<!--<div class="ret-form-cell">-->
					<!--<label class="ret-cell1" for="password">新密码：</label>-->
					<!--<input class="easyui-textbox"-->
							<!--type="password" name="password" id="password"-->
							<!--style="width:300px;height:30px;"-->
							<!--data-options="required:true,validType:'password'"-->
							<!--missingMessage="不能为空" />-->
				<!--</div>-->
				<!--<div class="ret-form-cell">-->
					<!--<label class="ret-cell1" for="repassword">确认密码：</label>-->
					<!--<input class="easyui-textbox"-->
							<!--type="password" name="repassword" id="repassword"-->
							<!--style="width:300px;height:30px;"-->
							<!--data-options="required:true,validType:'equalTo[\'#password\']'"-->
							<!--missingMessage="两次输入需一致" />-->
				<!--</div>-->
				<!--<div class="ret-form-cell">-->
					<!--<label class="ret-cell1" for="sid">卖家账号：</label>-->
					<!--<input class="easyui-textbox txt"-->
							<!--type="text" name="sid" id="sid"-->
							<!--style="width:300px;height:30px;"-->
							<!--data-options="required:true,validType:'englishOrNum'"-->
							<!--missingMessage="不能为空" />-->
				<!--</div>-->
				<!--<div class="ret-form-cell">-->
					<!--<label class="ret-cell1" for="mobile_no">手机号：</label>-->
					<!--<input class="easyui-textbox"-->
							<!--type="text" name="mobile_no" id="mobile_no"-->
							<!--style="width:300px;height:30px;"-->
							<!--data-options="required:true,validType:'mobile'"-->
							<!--missingMessage="不能为空" />-->
				<!--</div>-->
				<!--<div class="ret-form-cell">-->
					<!--<label class="ret-cell1" for="code">验证码：</label>-->
					<!--<input class="easyui-textbox"-->
							<!--type="text" name="code" id="code"-->
							<!--style="width:150px;height:30px;"-->
							<!--data-options="required:true,validType:'code'"-->
							<!--missingMessage="验证码为六位整数" />-->
					<!--<div class="ret-form-btn sendCode"><a href="javascript:void(0)" class="easyui-linkbutton" style="width:100px;height:30px;background: #5296e3;color:#fff;"  onclick="sendCode();">发送验证码</a></div>-->
				<!--</div>-->
				<!--<div class="ret-form-btn"><a href="javascript:void(0)" class="easyui-linkbutton" style="width:200px;height:30px;background: #5296e3;color:#fff;" id="resetsubmit" onclick="checkResetInfo();"><span>提交</span></a></div>-->
				<!--<div class="ret-msg" style="font-size: 14px;color: red;"></div>-->
			<!--</div>-->
		<!--</form>-->
	<!--</div>-->

</div>
<div class="ret-login-bottom"><div class="copyright">©版权所有E快帮  服务热线:400-010-1039</div></div>

<!--<div class="w">-->
	<!--<div id="footer">-->
		<!--&lt;!&ndash; <div class="link">-->
			<!--<ul>-->
				<!--<li><a href="http://www.wangdian.cn/">联系我们</a></li>-->
				<!--<li><a href="http://www.wangdian.cn/">友情链接</a></li>-->
				<!--<li class="last"><a>关于我们</a></li>-->
			<!--</ul>-->
		<!--</div> &ndash;&gt;-->
		<!--<div class="copyright">Copyright©2015-2017  E快帮 版权所有</div>-->
	<!--</div>-->
<!--</div>-->
<script type="text/javascript">
$(function(){
//	$("#ret-login-form :input[name='mobile_no']").tooltip({
//		position:'right',
//		deltaX:0,
//		content: '<span style="color:#222">点击这里,设置</span>',
//		showEvent:'',
//		hideEvent:'mousedown',
//		onShow: function(){
//			$(this).tooltip('tip').css({
//				backgroundColor: '#FFFF66',
//				// borderColor: '#666'
//			});
//			if(typeof onCloseMenuDialog == 'function'){
//				var dialog_old_onclose =  onCloseMenuDialog;
//			}else{
//				var dialog_old_onclose = function(){};
//			}
//
//			onCloseMenuDialog = function(){
//				dialog_old_onclose.apply(this);
//				if($(".ret-login-form :input[name='mobile_no']").length>0){
//					$(".ret-login-form :input[name='mobile_no']").tooltip('hide');
//				}
//			};
//		},
//	});
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