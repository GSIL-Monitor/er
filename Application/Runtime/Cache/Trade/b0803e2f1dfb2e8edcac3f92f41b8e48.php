<?php if (!defined('THINK_PATH')) exit();?><div id="passel_split_dialog"></div>
<div id="passel_split_point" style="margin: 10px">
	<label>提示：您选择了</label><label style="color:red;"><?php echo ($trade_count); ?></label><label>条订单进行批量拆分，请选择拆分类型。</label>
</div>
<form id="passel_split_form">
<div id="passel_split" style="margin: 10px">
	<label>拆分类型：</label><input id='type' class="easyui-combobox txt" style="width: 220px" name="type" data-options="panelHeight:'auto',required:true,valueField:'id',textField:'name',data:formatter.get_data('passel_split_type'),onSelect:tradeCheck.selectType"/> 
</div>
<div id="split_by_weight" style="margin: 10px">
	<label>每单重量不超过：</label><input id='weight' class="easyui-numberbox txt" name="max_weight" data-options="required:true"/><label>　千克</label>
</div>
<div class="form-div" id="split_by_goods_note">
	<label style="color: blue;" >#选<span style="color:red">按照相同货品批量拆分</span>，会先筛选出所选订单的<span style="color:red">重复货品</span>#<br></label>
	<label style="color: blue;">#然后对<span style="color:red">重复货品</span>进行拆分，所有的订单都会<span style="color:red">按此方式</span>拆分#</label>
</div>
<div class="form-div" id="split_by_weight_note">
	<label style="color: blue;" >#选<span style="color:red">按照订单重量拆分</span>，填写每单的<span style="color:red">限制重量</span>#<br></label>
	<label style="color: blue;">#然后就会将所选订单拆成<span style="color:red">不超过限重</span>的多个订单#</label>
</div>
</form>
<script>
$(function(){
	setTimeout(function(){
		$('#split_by_weight').hide();
		$('#split_by_weight_note').hide();
		$('#split_by_goods_note').hide();
	},0);
});
tradeCheck.selectType=function (record){
	if (record.id==0){
		$('#split_by_weight').hide();
		$('#split_by_weight_note').hide();
		$('#split_by_goods_note').show();
	}
	else if(record.id==1){
		$('#split_by_weight').show();
		$('#split_by_weight_note').show();
		$('#split_by_goods_note').hide();
	}
}
tradeCheck.submitTradeCheckDialog=function(){
	var data={};
	var form_data=$('#passel_split_form').form('get');
	var type=form_data['type'];
	if(type==''||type==undefined){messager.alert('请选择拆分类型'); return false;}
	if(type==1&&(form_data['max_weight']==''||form_data['max_weight']==0)){
		messager.alert('拆分订单的重量上限需大于0'); return false;
	}
	data['ids']=JSON.parse('<?php echo ($ids); ?>');
	var url='<?php echo ($url); ?>';
	data['type']=type;
	data['max_weight']=form_data['max_weight'];
	data=JSON.stringify(data);
	if(type==1){
		Post('<?php echo U('TradeCheck/passelSplit');?>?split='+'',{data:data},function(res){
			if(res.status==0){
				//$('#'+tradeCheck.params.edit.id).richDialog('passel_split', continue_split);
				$.fn.richDialog("response", res , 'passel_split');
		    }else{
				messager.alert(res.info);
				$('#'+tradeCheck.params.edit.id).dialog('close');
				tradeCheck.refresh();
			}
		},"JSON");
	}else{
		url += url.indexOf('?') != -1 ? '&split='+data : '?split='+data;
		var buttons=[ {text:'确定',handler:function(){tradeCheck.submitPasselSplitDialog();}}, {text:'取消',handler:function(){tradeCheck.cancelDialog(0);tradeCheck.cancelDialog('passel_split');}} ];
		tradeCheck.showDialog('passel_split','订单批量拆分',url,560,760,buttons);
	}
	$('#'+tradeCheck.params.edit.id).dialog('close');
}
function continue_split(data){
	$("#response_dialog").dialog('close');
	data['type']=1;
	data['continue']=1;
	Post('<?php echo U('TradeCheck/passelSplit');?>?split='+'',{data:data},function(res){
			messager.alert(res.info);
			$('#'+tradeCheck.params.edit.id).dialog('close');
			tradeCheck.refresh();
	},"JSON");

}
</script>