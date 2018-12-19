<?php if (!defined('THINK_PATH')) exit();?>
<style>
.txt-split{width:100px;}
.footer-split{padding-left:5px;height:5%;background-color: #f4f4f4;}
.red-txt{color:red;}
</style>
<table id="<?php echo ($datagrid["id"]); ?>" class="easyui-datagrid" data-options='<?php $dataOptions = array_merge(array ( 'border' => false, 'fit' => true, 'fitColumns' => true, 'rownumbers' => true, 'singleSelect' => true, 'pagination' => true, 'pageList' => array ( 0 => 20, 1 => 50, 2 => 100, ), 'pageSize' => 20, ), $datagrid["options"]);if(isset($dataOptions['toolbar']) && substr($dataOptions['toolbar'],0,1) != '#'): unset($dataOptions['toolbar']); endif;if(isset($dataOptions['methods'])): unset($dataOptions['methods']);endif; echo trim(json_encode($dataOptions), '{}[]').((isset($datagrid["options"]['toolbar']) && substr($datagrid["options"]['toolbar'],0,1) != '#')?',"toolbar":'.$datagrid["options"]['toolbar']:null).(isset($datagrid["options"]['methods'])? ','.$datagrid["options"]['methods']:null); ?>' style="<?php echo ($datagrid["style"]); ?>" ><thead><tr><?php if(is_array($datagrid["fields"])):foreach ($datagrid["fields"] as $key=>$arr):if(isset($arr['formatter'])):unset($arr['formatter']);endif;if(isset($arr['methods'])):unset($arr['methods']);endif;if(isset($arr['editor'])):unset($arr['editor']);endif;echo "<th data-options='".trim(json_encode($arr), '{}[]').(isset($datagrid["fields"][$key]['formatter'])?",\"formatter\":".$datagrid["fields"][$key]['formatter']:null).(isset($datagrid["fields"][$key]['editor'])?",\"editor\":".$datagrid["fields"][$key]['editor']:null).(isset($datagrid["fields"][$key]['methods'])?",".$datagrid["fields"][$key]['methods']:null)."'>".$key."</th>";endforeach;endif; ?></tr></thead></table>
<div id="<?php echo ($id_list["toolbar"]); ?>">
<div class="form-div">
<label style="color: blue;" >#按组合装拆分暂时不支持批量操作 #编辑对应组合装<span class='red-txt'>拆分数</span>->确认无误后->点击<span class='red-txt'>确定</span>#</label>
<!-- <label>拆出订单数量：</label>
<select class="easyui-combobox sel" name="suite_split_num" id="suite_split_num" data-options="panelHeight:'auto',editable:false, required:true" style="width:70px;">
    <option value="0"></option><option value="1">一条</option><option value="2">多条</option>
</select> -->
</div>
</div>
<script type="text/javascript">
//# sourceURL=trade.split.js
var dialog;
tradeCheck.split_flag_arr={};
tradeCheck.split_flag_arr.tip_type=0;
$(function(){
	$('#<?php echo ($datagrid["id"]); ?>').datagrid().datagrid('enableCellEditing');
	setTimeout(function(){
		dialog='<?php echo ($id_list["add_suite_dialog"]); ?>';
		suiteSplitOrder = new ThinDatagrid($('#<?php echo ($datagrid["id"]); ?>'),undefined,false);
		loadSuiteOrdersData();		
	},0);
});
function loadSuiteOrdersData(){	
	var json_orders=<?php echo ($suite_split_common_order); ?>;
	$('#<?php echo ($datagrid["id"]); ?>').datagrid('loadData',json_orders);
}
function endSplitTradeEdit(index,row,changes){
	tradeCheck.split_flag_arr.tip_type=0;
	if(parseFloat(row.split_num)>parseFloat(row.num)){
		tradeCheck.split_flag_arr.tip_type=1;
		messager.alert('拆分的数量不能大于货品数量');
		row.split_num=0;//大于剩余数量，置为0
		return;
	}else if(parseFloat(row.split_num)<0){
		tradeCheck.split_flag_arr.tip_type=2;
		messager.alert('拆分的数量不能小于0');
		row.split_num=0;//小于0，置为0
		return;
	}else if(row.split_num==undefined){
		row.split_num=0;//为空，置为0
		return;
	}
	row.left_num=parseFloat(row.num-row.split_num).toFixed(4);
	return;
}
function getSplitTradeSelect(index, row){
	tradeCheck.split_flag_arr.index=index;
}
tradeCheck.submitSuiteSplitDialog=function(){
	var dg=$('#'+'<?php echo ($datagrid["id"]); ?>');
	var rows=dg.datagrid('getRows');
	var split_sum=0;
	if (rows.length==0) {messager.alert('列表中没有组合装');return;}
	for (var i = 0; i < rows.length; i++) {
		split_sum+=rows[i].split_num;
	}
	if (split_sum==0) {messager.alert('至少有一件货品被拆分，请编辑对应货品的拆分数');return;}
	var data={};
	var id=rows[0]['trade_id'];
	data['info']=rows;
	data=JSON.stringify(data);
	Post('<?php echo U('TradeCheck/suiteSplit');?>?id='+id,{data:data},function(res){
		if(!res.status){
			$('#'+tradeCheck.params.edit.id).dialog('close');
			messager.alert(res.info);
			return false;
		}else{
			messager.alert(res.info);
			$('#'+tradeCheck.params.edit.id).dialog('close');
			tradeCheck.refresh();
		}
	},"JSON");
}
</script>