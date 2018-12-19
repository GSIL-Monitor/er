//# sourceURL=rich.datagrid.js
function RichDatagrid(params)
{
    var that = this;
	this.params = params;
	this.index = undefined;// the datagrid selected index
	//this.controller = params.controller;
	this.rowId = undefined; // selected row in datagird
	this.datagridId = undefined; // the datagrid in selected tab
	this.RowIdList = {}; // the row id in each datagrid of the tab
	this.tabIndex = undefined; // the tab selected index
	this.selectRows=undefined;//selected rows

	if (typeof(this.params.search) != "undefined"){
		this.clickMore();
	}
	$('#'+this.params.datagrid.id).datagrid('copyCell');
	$('#'+this.params.datagrid.id).datagrid('options').onClickRow = this.clickRow;
	$('#'+this.params.datagrid.id).datagrid('options').onCheck = this.clickCheckbox;
	$('#'+this.params.datagrid.id).datagrid('options').onUncheck = this.uncheck;
	$('#'+this.params.datagrid.id).datagrid('options').onCheckAll = this.checkAll;
	$('#'+this.params.datagrid.id).datagrid('options').onUncheckAll = this.uncheckAll;
	$('#'+this.params.datagrid.id).datagrid('options').onDblClickRow = this.doubleClick;
	$('#'+this.params.datagrid.id).datagrid('options').onLoadSuccess=this.onLoadSuccess;
	$('#'+this.params.datagrid.id).datagrid('options').that = this;
	if(typeof(this.params.flag) != "undefined"){
		this.selectRows = undefined;
		$('#'+this.params.datagrid.id).datagrid('options').rowStyler = this.showFlag;
		$('#'+this.params.flag.set_flag).combobox({
			valueField:'id',    
		    textField:'name',
		    data:this.params.flag.list_flag,
		    formatter:this.formatFlag,
		    onSelect:this.selectFlag,
		    that:this
		});
		if(this.params.flag.search_flag!=undefined)
		{
			var flags_data=$.extend(true, [], this.params.flag.list_flag);
			flags_data[0].id='all';
			flags_data[0].name='全部';
			this.params.flag.list_search=flags_data;
			$('#'+this.params.flag.search_flag).combobox({
				valueField:'id',    
			    textField:'name',
			    data:flags_data,
			    formatter:this.formatFlag,
			    that:this
			});
		}
	}
	
	if (typeof(this.params.tabs) != "undefined"){
		$('#'+this.params.tabs.id).tabs('options').onSelect=this.clickTab;
		$('#'+this.params.tabs.id).tabs('options').onLoad=this.loadTab;
		$('#'+this.params.tabs.id).tabs('options').that = this;
	}
}

RichDatagrid.prototype = {
constructor:RichDatagrid,
doubleClick:function(i, row){
	getDatagridId = $(this).datagrid('options').id;
	if(getDatagridId == 'tradecheck_gettradelist_datagrid'){
		$(this).datagrid('options').that.edit(1);
	}else{
		$(this).datagrid('options').that.edit();
	}
},
clickCheckbox:function(i,row){	
// 	$(this).datagrid('options').that.checked(i,row);
 	$(this).datagrid('options').that.click(i,row);
},
uncheck:function(i,row){
	$(this).datagrid('options').that.checked();
},
checkAll:function(i,rows){
	$(this).datagrid('options').that.checked(i,rows);
},
uncheckAll:function(rows){
	$(this).datagrid('options').that.checked();
},
clickRow:function(i,row){
	$(this).datagrid('options').that.click(i,row);
},
onLoadSuccess:function(data){
	var that=$(this).datagrid('options').that;
	if (!!data.info){messager.info(data.info);return;}
	messager.tip(that.params.datagrid.id);
	that.selectRows=undefined;
	$(this).datagrid('options').index=undefined;
	that.RowIdList={};
	that.loadSuccess(data);
},
loadSuccess:function(data){
	return;
},
checked:function(i,row){
	$('#'+this.params.datagrid.id).datagrid('options').index=i;
	var rows=$('#'+this.params.datagrid.id).datagrid('getChecked');
	this.selectRows=rows.length==0?undefined:rows;
},
click:function(i,row){ 
	$('#'+this.params.datagrid.id).datagrid('options').index=i;
	var rows=$('#'+this.params.datagrid.id).datagrid('getSelections');
	this.selectRows=rows.length==0?undefined:rows;
	if(this.params.flag!=undefined){var flag_id=row.flag_id;flag_id=(this.params.flag.dict_flag!=undefined&&this.params.flag.dict_flag[flag_id]!=undefined)?flag_id:0;$('#'+this.params.flag.set_flag).combobox('setValue',flag_id);}
	if(!(!this.params.tabs)){ this.rowId = row.id; this.reloadTab(); }
},
reloadTab:function(){
	if(!this.datagridId){ this.datagridId = $('#'+this.params.tabs.id).tabs('getTab',this.tabIndex).find('.easyui-datagrid').attr('id');  }
	if(this.rowId!=undefined && this.RowIdList[this.datagridId]!=this.rowId){
		this.RowIdList[this.datagridId] = this.rowId;
		if(!(!this.params.tabs.url)){$('#'+this.datagridId).datagrid('options').url = this.params.tabs.url;}
		$('#'+this.datagridId).datagrid('reload', {'id':this.rowId,'datagridId':this.datagridId});
	}
},
clickTab:function(title,index){
	var that = $(this).tabs('options').that;
	that.tabIndex = index;
	that.datagridId = $('#'+that.params.tabs.id).tabs('getTab',index).find('.easyui-datagrid').attr('id');
	if (!(!that.datagridId)){ that.reloadTab(); }
},
loadTab:function(tab){
	var that = $(this).tabs('options').that;
	that.datagridId = tab.find('.easyui-datagrid').attr('id');
	that.tabIndex = !that.tabIndex ? 0 : that.tabIndex;
	if (!(!that.datagridId)){ that.reloadTab(); }
},
showDialog: function (id,title,url,height,width,buttons,toolbar,ismax){
	if(id==undefined||id==0){id=this.params.edit.id;}
	if(!height)height=510;
	$('#'+id).dialog({
		title:title,
		iconCls:'icon-save',
		width:!width?764:width,
		height:height>510?510:height,
		//height:!height?560:height,
		minimizable: false,
		maximizable: ismax==null?true:ismax,
		resizable: ismax==null?true:ismax,
		closed:false,
		inline:true,
		modal:true,
		href:url,
		toolbar:!toolbar?null:toolbar,
		buttons:buttons,
		onLoadError:function(){
			$('#'+id).dialog('close');
			messager.alert('您的链接中断或超时，请重试！');
		}
	});
},
cancelDialog: function(id){
	if(id==undefined||id==0){id=this.params.edit.id;}
	messager.confirm('您确定要关闭吗？', function(r){ if (r){$('#'+id).dialog('close');}});
},
//ct
submitReasonDialog: function(params,type,ids,list,solve_tye,is_force,is_force_all){
	var that = undefined;
	if(this instanceof RichDatagrid){
		that = this;
	}
	var select_rows = that.selectRows;
	if(ids==undefined){
	    ids = '';
		for(var i =0;i<select_rows.length;i++){ ids = ids+select_rows[i].id+",";}
		ids = ids.substr(0,ids.length-1);
	}
	var reason_form_id = that.params[type].form.id;
	var select_value = $('#'+that.params[type].form.list_id).combobox('getValue');
	if(String(select_value) == '0' || String(select_value)=='' || String(select_value)==undefined){
		messager.alert("无效的原因,请先添加原因"); return false;
	}
	//修改
	var form_params = {};
	form_params = JSON.stringify($('#' + reason_form_id).form('get'));
	var reason_params = {};
	reason_params['ids'] = ids;
	reason_params['form'] = form_params;
	reason_params['is_force'] = is_force;
	$.post(that.params[type].form.url,reason_params,function (result) {
		$('#'+that.params[type].id).dialog('close');
		//添加返回值含有三种情况的代码
	    /*if(!$.isEmptyObject(result.fail)){
			//调用dialog显示处理结果
			$.fn.richDialog("response", result.fail, that.params[type].form.dialog_type);
		}*/
		if(is_force){
			$("#response_dialog").dialog('close');
		}
		if(!$.isEmptyObject(result)){
			that.dealDatagridReasonRows(result,list,solve_tye);
		}
    },'json');
},
//add  deal with reason reasult function  of oneself 
dealDatagridReasonRows: function(data){
	return;
},
add: function(){
	var that=this;
	var buttons=[ {id:'confirmId', text:'确定',handler:function(){ that.submitAddDialog(); }}, {text:'取消',handler:function(){that.cancelDialog(that.params.add.id)}} ];
	this.showDialog(that.params.add.id,that.params.add.title,that.params.add.url,that.params.add.height,that.params.add.width,buttons,that.params.add.toolbar,that.params.add.ismax);
},
setFlag: function(){
	var that=this;
	if(!that.params.flag){return;}
	var buttons=[ {text:'确定',handler:function(){ submitFlagsDialog(that.params.flag); }}, {text:'取消',handler:function(){that.cancelDialog(that.params.flag.dialog.id)}} ];
	this.showDialog(that.params.flag.dialog.id,that.params.flag.dialog.title,that.params.flag.dialog.url,that.params.flag.dialog.height,that.params.flag.dialog.width,buttons,that.params.flag.toolbar,that.params.flag.ismax);
},
setReason: function(type,ids,list,solve_type,is_force,is_force_all){
	var that=this;
	is_force=is_force==undefined?0:is_force;
	is_force_all=is_force_all==undefined?0:is_force_all;
	var  now_selects = $("#"+that.params.datagrid.id).datagrid('getSelections');
	$.isEmptyObject(now_selects)?that.selectRows=undefined:that.selectRows = now_selects;
	if(that.selectRows==undefined){messager.alert('请选择操作的订单');return;}
	if(typeof type == "string" && type != undefined){
		var reason = type;
	}else{
		messager.alert('操作原因类型未指明');return;
	}
	
	if(that.params[type]==undefined){return;}
	var sub_position = that.params[type].url.indexOf('?');
	var sub_url = that.params[type].url.substring(0,sub_position);
	var regexp=RegExp('model_type' + "=([^&]*)(&|$)");
	var keys=that.params[type].url.substring(sub_position).match(regexp);
	var key=keys[1];
	if(key=='salesstockout'){
		that.params[type].height =150;
	}else{
		that.params[type].height =120;
	}
	var buttons=[ {text:'确定',handler:function(){ that.submitReasonDialog(that.params,type,ids,list,solve_type,is_force,is_force_all); }}, {text:'取消',handler:function(){$('#'+that.params[type].id).dialog('close');}} ];
	this.showDialog(that.params[type].id,that.params[type].title,that.params[type].url,that.params[type].height,that.params[type].width,buttons,that.params[type].toolbar,that.params[type].ismax);
},
edit: function(type){
	var that;
	if(this instanceof RichDatagrid){
		that = this;
	}else{
		that = $(this).datagrid('options').that;
	}
	if(that.params.edit==undefined||that.params.edit.url==undefined){return false;}
	var url=that.params.edit.url;
	//var row=$('#'+that.params.datagrid.id).datagrid('getSelected');
	if(that.selectRows==undefined) { messager.alert('请选择操作的行!'); return false; }
	if(that.selectRows.length > 1){ messager.alert('请选择单行编辑!'); return false; }
	if(!that.checkEdit()){return false;}
	var row=that.selectRows[0];
	url += (url.indexOf('?') != -1) ? '&id='+row.id : '?id='+row.id;
    var buttons=[ {text:'确定',handler:function(){ that.submitEditDialog(); }}, {text:'取消',handler:function(){that.cancelDialog(that.params.edit.id)}} ];
	if(type==1){
		buttons.unshift({text:'确定并审核',handler:function(){ that.submitEditCheckDialog(); }});
	}
	this.showDialog(that.params.edit.id,that.params.edit.title,url,that.params.edit.height,that.params.edit.width,buttons,that.params.edit.toolbar,that.params.edit.ismax);
},
checkEdit:function(){
	return true;
},
remove: function(type){

	var url = this.params.delete.url;//delete js本身就有该操作符 ->remove
	var tb = this.params.datagrid.id;
	var tb_jq = $('#' + tb);
	var index = tb_jq.datagrid('options').index;
	if ( this.selectRows == undefined || this.selectRows.length == 0 || this.selectRows == null) { messager.alert('请选择操作的行!'); return false; }
	var row=this.selectRows[0];
	var id;
	if(!(!type)){
		id=[]; for(i in this.selectRows){ id.push(this.selectRows[i].id); }
	}else{
		id=this.selectRows[0].id;
	}	
	var that=this;
	messager.confirm('确定要删除吗？', function(r) {
	    if (!r) { tb_jq.datagrid('loaded'); return false; }
	    $.post(url, { id: id }, function(res) {
	    	if(!(!type)){
	    		if(res.status==0){that.refresh();}
	    		else if(res.status==1){messager.alert( res.info, 'error');}
	    		else if(res.status==2){$.fn.richDialog("response", res.info, type);that.refresh();}
	    	}else{
	    		if (!res.status) { messager.alert( res.info, 'error'); } 
		        else { tb_jq.datagrid('deleteRow', index); }
		        tb_jq.datagrid('loaded');
	    	}
	    }, 'json')
	});
	tb_jq.datagrid('options').index = undefined;
	//this.selectRows=undefined;
},
refresh: function(id){
	if(id==undefined){id=this.params.datagrid.id;}
	$('#'+this.params.datagrid.id).datagrid('reload');
},
clickMore: function(that) { 
	that=(that==undefined?this.params.search.more_button:that);
	var content = this.params.search.more_content;
	var flag = this.params.search.hidden_flag;
	if ($("#"+flag).val()==0) {
		$("#"+content).show(); 
		$("#"+flag).attr("value","1");
		$(that).html("收起");
	}else{
		$("#"+content).hide();
		$("#"+flag).attr("value","0");
		$(that).html("更多");
	} 
},
submitSearchForm: function(that,id){
	if(id==undefined||id==0){id=this.params.datagrid.id}
	var dg = $('#'+id);
	var queryParams = dg.datagrid('options').queryParams;
	var getform;
	if(that==undefined){getform=$('#'+this.params.search.form_id);dg.datagrid('options').sortName=null;$(dg.datagrid('getPager')).pagination({pageSize:20,pageNumber:1});dg.datagrid('options').pageNumber=1;dg.datagrid('options').pageSize=20;}
	else{getform = $(that).parent('div').parent('form').length != 0? $(that).parent('div').parent('form'):$('#'+this.params.search.form_id);}
	var search_num=[];
	$.each(getform.serializeArray(), function() {
		if(search_num[this['name']]==undefined){
			search_num[this['name']]=1;
			queryParams[this['name']] = this['value'];
		}else{//处理多选下拉框
			search_num[this['name']]++;
			queryParams[this['name']] +=','+ this['value'];
		}
	});
//	$.each(getform.serializeArray(), function() {
//		queryParams[this['name']] = this['value'];
//	});
	$.each(getform.find('input[type=checkbox]'), function() {
		queryParams[this['name']] = this['value'];
	});
	if(this.params.search.form_main_id!=undefined){
		var getfrommain;
		if(that==undefined){getfrommain=$('#'+this.params.search.form_main_id);dg.datagrid('options').sortName=null;$(dg.datagrid('getPager')).pagination({pageSize:20,pageNumber:1});dg.datagrid('options').pageNumber=1;dg.datagrid('options').pageSize=20;}
		else{getfrommain =$(that).parent('div').parent('form').length != 0? $(that).parent('div').parent('form'):$('#'+this.params.search.form_main_id);}
		$.each(getfrommain.serializeArray(), function() {
			queryParams[this['name']] = this['value'];
		});
		$.each(getfrommain.find('input[type=checkbox]'), function() {
			queryParams[this['name']] = this['value'];
		});
	}
	dg.datagrid('reload');
},
bindSearchForm:function(id,that){
	$('input',$('#'+id)).bind('keydown',function(e){if(e.keyCode==13){that.submitSearchForm(that)}});
},
setFormData:function(id,data){
	if(id==undefined){
		id=this.params.search.form_id;
		if(this.params.flag!=undefined){ var dict_flag={}; var tmp=this.params.flag.list_flag; $.each(tmp,function(){dict_flag[this.id]=this.name;}); this.params.flag.dict_flag=dict_flag;}
	}
	if(data==undefined){data=$('#'+id).form('get');}
	this.params.search.form_data=data;
	$('#'+id+' :input[extend_type="complex-check"]').each(function(){$(this).triStateCheckbox('init');});
	this.bindSearchForm(id,this);
},
loadFormData:function(id,data){
	if(this.params.search.form_main_id!=undefined){
		main_id=this.params.search.form_main_id;
		$('#'+main_id).form('reset');
	}
	if(id==undefined){id=this.params.search.form_id;}
	if(data!=undefined){$('#'+id).form('load',data);}
	else if(this.params.search.form_data!=undefined){$('#'+id).form('load',this.params.search.form_data);$('#'+id+' :input[extend_type="complex-check"]').each(function(){$(this).triStateCheckbox('init');});this.submitSearchForm();}
	else{$('#'+id).form('reset');}
},
showFlag:function(i,row){
	//datagrid加载过程中显示已标记的行
	var that;
	if(this instanceof RichDatagrid){
		that = this;
	}else{ 
		that = $(this).datagrid('options').that;
	}
	var flag_class=that.params.flag.json_flag[row['flag_id']];
	if(flag_class==undefined){return;}
	return flag_class;
},
//flag datagrid
selectFlag:function(record){
	if(this instanceof RichDatagrid){
		that = this;
	}else{ 
		that = $(this).combobox('options').that;
	}
	if(that.selectRows==undefined){messager.alert('请选择标记的订单');return;}
	var data={};
	data['flag_id']=record.id;
	var local_data={};
	var dg=$('#'+that.params.datagrid.id);
	var rows=dg.datagrid('getRows');
	local_data['total']=rows.length; 
	local_data['rows']=rows;
	if(that.selectRows.length>1) { var ids={}; for(var i=0;i<that.selectRows.length;++i){ids[i]=that.selectRows[i]['id'];that.selectRows[i]['flag_id']=record.id;} data['id']=ids;}else { data['id']=that.selectRows[0]['id'];that.selectRows[0]['flag_id']=record.id; }
	var url=that.params.flag.url;
	messager.confirm('您确定标记选中的行？', function(r){ if(!r){dg.datagrid('reload');return;} $.post(url,data,function(res){
		if(!res.status){$.messager.alert('ERP',res.info,'error');} 
		else{that.refresh();}
	}); });


},
//formatter flag combobox style
formatFlag:function(row){
	return '<span style="'+row.clazz+'">' + row.name + '</span>';
},
//get selected rows
getSelectRows: function(){
	if(typeof this.selectRows != 'undefined') {
		return this.selectRows;
	}else{
		return $('#'+this.params.datagrid.id).datagrid('getSelections');
	}
},

downloadTemplet: function(url){
	if (!!window.ActiveXObject || "ActiveXObject" in window){
		messager.confirm('IE浏览器下文件名会中文乱码，确定下载模板吗？',function(r){
			if(!r){return false;}
			window.open(url);
		})
	}else{
		messager.confirm('确定下载模板吗？',function(r){
			if(!r){return false;}
			window.open(url);
		})
	}
},
readHelp: function () {
	var that = this;
	this.showDialog(
			that.params.help.id,
			'查看帮助',
			that.params.help.url,
			600,
			700,
			null,
			null,
			true
	);
},
};


/*
  解藕封装
*/
var Dialog=function(){
	return{
		show:function(id,title,url,height,width,buttons,toolbar){
			if(!height)height=510;
			$('#'+id).dialog({
				title:title,
                iconCls:'icon-save',
                width:!width?764:width,
                height:height>510?510:height,
                maximizable: true,
                resizable: true,
                closed:false,
                inline:true,
                modal:true,
                href:url,
                toolbar:!toolbar?null:toolbar,
                buttons:buttons
			});
		},
		cancel:function(id){
			messager.confirm('您确定要关闭吗？', function(r){ if (r){$('#'+id).dialog('close');}});
		}
	}
}();

