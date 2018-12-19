//# sourceURL=login.js

var fancyForm=function(){
	return{
		inputs:".login-form .cell input",
		setup:function(){
			var a=this;
			this.inputs=$(this.inputs);
			a.inputs.each(function(){
				var c=$(this);
				a.checkVal(c)
			});
			a.inputs.on("keyup blur",function(){
				var c=$(this);
				a.checkVal(c);
			});
		},
		checkVal:function(a){
			a.val().length>0?a.parent("div").addClass("val"):a.parent("div").removeClass("val")
		}
	}
}();
function checkLogin(jq_v,obj,msg){
	var val = obj[jq_v];
	var jq=$('#'+jq_v);
	//var obj_len=Object.keys(obj).length;
	val=$.trim(val);
	for(var i in obj){
		if(!$.trim(obj[i])){$('#'+i).addClass('err');}
		else{$('#'+i).removeClass('err');$('.msg-err-'+i).html('');}
	}
	if(!val){jq.addClass('err');$('.msg-err-'+jq_v).html('<i></i>'+msg.info);return false;}
	else if(msg.err!=undefined&&!/^[a-zA-Z0-9_]{1,}$/.test(val)){jq.addClass('err');$('.msg-err-'+jq_v).html('<i></i>'+msg.err);return false;}
	else if(jq.hasClass('err')){jq.removeClass('err');$('.msg-err-'+jq_v).html('');}
	return true;
}
function showResultInfo(info){
	if(info.indexOf('卖家账号')>-1){$('#sid').addClass('err');$('.msg-err-sid').html('<i></i>'+info);}
	else if(info.indexOf('密码')>-1){$('#password').addClass('err');$('.msg-err-password').html('<i></i>'+info);}
	else if(info.indexOf('用户')>-1){$('#username').addClass('err');$('.msg-err-username').html('<i></i>'+info);}
	else{$('#login_msg-err').html('<i></i>'+info);}
}

(function($) {

	$.alerts = {
		alert: function(title, message, callback) {
			if( title == null ) title = '提示';
			$.alerts._show(title, message, function(result) {
				if( callback ) callback(result);
			});
		},
		_show: function(title, msg, callback) {

			var _html = "";
			_html += '<div id="mb_box"></div><div id="mb_con"><span id="mb_tit">' + title + '</span>';
			_html += '<div id="mb_msg">' + msg + '</div><div id="mb_btnbox">';
			_html += '<input id="mb_btn_ok" type="button" value="确定" />';
			_html += '</div></div>';
			if($("#mb_box").length == 0) {
				$("body").append(_html);
				GenerateCss();
			}
			$("#mb_btn_ok").click( function() {
				$.alerts._hide();
				callback(true);
			});
			$("#mb_btn_ok").focus().keypress( function(e) {
				if( e.keyCode == 13 || e.keyCode == 27 ) $("#mb_btn_ok").trigger('click');
			});
		},
		_hide: function() {
			$("#mb_box,#mb_con").remove();
		}
	}

	dl_alert = function(title, message, callback) {
		$.alerts.alert(title, message, callback);
	}

	//生成Css
	var GenerateCss = function () {

		var _widht = document.documentElement.clientWidth;
		var _height = document.documentElement.clientHeight;

		var boxWidth = $("#mb_con").width();
		var boxHeight = $("#mb_con").height();

		//让提示框居中
		$("#mb_con").css({ top: (_height - boxHeight) / 2 + "px", left: (_widht - boxWidth) / 2 + "px" });
	}
})(jQuery);