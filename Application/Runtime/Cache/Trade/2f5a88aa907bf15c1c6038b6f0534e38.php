<?php if (!defined('THINK_PATH')) exit();?><div id="search_src_tids_dialog">
<form method="post" id="<?php echo ($src_tids_params["form_id"]); ?>" style="padding:10px 0 0 10px;">
	<div class="form-div">
		<label> 请选择分隔符: </label><input class="easyui-combobox txt" type="text" id="separator"  name="separator" value="0" data-options="valueField:'id',textField:'name',data:[{'id':'0','name':'回车换行'},{'id':'1','name':'空格'},{'id':'2','name':'逗号(英文状态“,”)'},{'id':'3','name':'分号(英文状态“;”)'}]">
		<label style="color:red">#按原始单号搜索限制一次只能查200个单号#</label>
		 <a href="javascript:void(0)" class="easyui-menubutton" style="margin-left: 10px;" data-options="iconCls:'icon-excel',plain:true,menu:'#tradecheck_upload_src_tids'" onclick="tradeCheck.uploadDialog()">导入原始单号</a>
	</div>	
	<hr style="border:none;border-top:2px dotted #95B8E7;">
	<div class="form-div">
		<input class="easyui-textbox txt" type="text" data-options="multiline:true" id="search_src_tids" style="width:600px;height:320px;" name="passel_src_tids" type="text" />
	</div>
	<div id="tradecheck_upload_src_tids" style="width:100px;">
		<div data-options="iconCls:'icon-down_tmp'" onclick="tradeCheck.downloadTemplet()">下载模板</div>
	</div>
</form>
</div>
<script>
$(function () { 
    setTimeout(function () {
        $("#search_src_tids").textbox('textbox').css("font-size", "18px");
    }, 0); 
});
tradeCheck.submitSearchSrcTidsDialog=function(){
	var form = $("#<?php echo ($src_tids_params["form_id"]); ?>");
	var url= "<?php echo ($src_tids_params["form_url"]); ?>";
	var form_data = form.form('get');
	if (form_data.passel_src_tids=='') { messager.alert('请填写原始单号');return false;}
	var src_tids_str=form_data.passel_src_tids;
	if(src_tids_str==undefined||src_tids_str==null){src_tids_str=''};
	Post(url,{data:form_data},function(res){
		if(res.status==1){
			messager.alert(res.info);
			return false;
		}else{
			$('#passel_src_tids').val(res.info);
			tradeCheck.searchData();
		}		
		$('#flag_set_dialog').dialog('close');
	},'JSON');	
}
</script>