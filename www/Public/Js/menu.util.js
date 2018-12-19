//# sourceURL=menu-util.js

function open_menu(title, url){
	if(title=='系统设置'){
		$("<div class='setting_cover'></div>").css({backgroundColor:"#ccc",opacity:.2, display: "block", width: "100%", height: $(window).height() ,"z-index":"9999" ,position:"absolute"}).appendTo("body");
	}
	if(/dialog=/.test(url)){
		// open dialog
		var sub_position = url.indexOf('?');
		var sub_url = url.substring(0,sub_position);
		var regexp=RegExp('dialog' + "=([^&]*)(&|$)");
		var keys=url.substring(sub_position).match(regexp);
		var key=keys[1];
		var match_length = keys[0].length;
		if(url.length != sub_position+1+match_length){
			sub_url = sub_url+'?'+url.substr(sub_position+match_length+1);
		}
		var dialog={"accountmanagement":{"width":500,"height":260,"title":"账户管理"},"alarmstock":{"width":600,"height":400,"title":"库存预警"},"implementclean":{"width":800,"height":450,"title":"系统清理"}};//set dialog width and height
		if(!dialog[key]){dialog[key]={};dialog[key].title=title;}
		$('#reason_show_dialog').dialog({
			title:dialog[key].title,
			iconCls:'icon-save',
			width:!dialog[key].width?764:dialog[key].width,
			height:!dialog[key].height?560:dialog[key].height,
			closed:false,
			inline:true,
			modal:true,
			href:encodeURI(sub_url),
			onClose:function(){if(typeof onCloseMenuDialog == 'function'){onCloseMenuDialog();}},
			buttons: [
			    {text:'确定',handler:function(){var is_close=submitMenuDialog();is_close=is_close==undefined?true:false;if(is_close){$('#reason_show_dialog').dialog('close');}}},
			    {text:'取消',handler:function(){messager.confirm('您确定要关闭吗？', function(r){ if (r){$('#reason_show_dialog').dialog('close');}});}}
			]
		});
	}else if(!!url){
		var brower=get_brower();
		if((url.toLowerCase().indexOf('printtemplate')>-1||url.toLowerCase().indexOf('print_template')>-1)&&!(brower=='ie')&&(url.toLowerCase().indexOf('newprinttemplate') == -1&&url.toLowerCase().indexOf('new_print_template') == -1)){//||brower=='firefox'||brower=='lb'
			messager.info('由于菜鸟打印组件暂不支持'+brower+'浏览器，打印操作推荐使用IE!');return;
		}
		add_tab("container", title, url, true, true, false);
	}
}
function add_tab(id, title, url, closable,selected,menu){
	if($("#"+id).tabs('exists', title)){
		$("#"+id).tabs('select', title);
	}else{
		if(menu){
			var iframeId = 'container' + new Date().getTime();
			var content = '<iframe scrolling="auto" frameborder="0" id="'+iframeId+'" src="'+url+
			'" style="width:100%; height:100%;"></iframe>';
			$("#"+id).tabs('add', {title:title, content:content, closable:closable,selected:selected});
			$("#"+id).tabs("loading");

			$("#"+iframeId).on("load", function(){
				$("#container").tabs("loaded");
				//var panel = $('#container').tabs("getSelected");
				});

		}else{
			$("#"+id).tabs('add', {title:title, href:url, closable:closable,selected:selected});
		}
	}
}

function add_tabs(arr){
	if ($.isArray(arr)){
		for (var i = 0; i < arr.length; ++i){
			if(i == 0){
			    add_tab(arr[i].id, arr[i].title, arr[i].url, false,true,false);
			}else{
				add_tab(arr[i].id, arr[i].title, arr[i].url, false,false,false);
			}

		}
	}
}
function get_brower(){
	var userAgent = navigator.userAgent.toLowerCase();
	var brower='ie';//default is ie ; 360ee is Chrome 360se is IE;
	var isOpera=/opera/.test(userAgent);
	var isEdge=/edge/.test(userAgent);
	if (!!window.ActiveXObject || "ActiveXObject" in window)
	{
		return brower;
	}else{
		if(isOpera){
			brower='opera';
		}else if(isEdge){
			brower='edge';
		}else if(/firefox/.test(userAgent)){
			brower='firefox';
		}else if(/chrome/.test(userAgent) && !isEdge){
			brower='chrome(及其内核)';
		}else if(/safari/.test(userAgent)){
			brower='safari';
		}else if(/lbbrowser/.test(userAgent)){
			brower='lb';
		}
		return brower;
	}
	//else if(/msie/.test(userAgent) && !isOpera){ brower='ie'; }

}
function beforeunload(){return '刷新和关闭会导致已打开的Tab界面全部关闭，是否继续？';}
function bindunbeforeunload(){
	var appName=navigator.appName;
	var appVersion=navigator.appVersion.toLowerCase();
	if(!(appName=='Microsoft Internet Explorer' && (appVersion.match(/msie 10./i)=='msie 10.' || appVersion.match(/msie 9./i)=='msie 9.' || appVersion.match(/msie 8./i)=='msie 8.' || appVersion.match(/msie 7./i)=='msie 7.' || appVersion.match(/msie 6./i)=='msie 6.'))){
		window.onbeforeunload=beforeunload;
	}
}