<?php if (!defined('THINK_PATH')) exit();?>
<div style="height: 40%;">
<table id="<?php echo ($datagrid["order"]["id"]); ?>" class="easyui-datagrid" data-options='<?php $dataOptions = array_merge(array ( 'border' => false, 'fit' => true, 'fitColumns' => true, 'rownumbers' => true, 'singleSelect' => true, 'pagination' => true, 'pageList' => array ( 0 => 20, 1 => 50, 2 => 100, ), 'pageSize' => 20, ), $datagrid["order"]["options"]);if(isset($dataOptions['toolbar']) && substr($dataOptions['toolbar'],0,1) != '#'): unset($dataOptions['toolbar']); endif;if(isset($dataOptions['methods'])): unset($dataOptions['methods']);endif; echo trim(json_encode($dataOptions), '{}[]').((isset($datagrid["order"]["options"]['toolbar']) && substr($datagrid["order"]["options"]['toolbar'],0,1) != '#')?',"toolbar":'.$datagrid["order"]["options"]['toolbar']:null).(isset($datagrid["order"]["options"]['methods'])? ','.$datagrid["order"]["options"]['methods']:null); ?>' style="<?php echo ($datagrid["order"]["style"]); ?>" ><thead><tr><?php if(is_array($datagrid["order"]["fields"])):foreach ($datagrid["order"]["fields"] as $key=>$arr):if(isset($arr['formatter'])):unset($arr['formatter']);endif;if(isset($arr['methods'])):unset($arr['methods']);endif;if(isset($arr['editor'])):unset($arr['editor']);endif;echo "<th data-options='".trim(json_encode($arr), '{}[]').(isset($datagrid["order"]["fields"][$key]['formatter'])?",\"formatter\":".$datagrid["order"]["fields"][$key]['formatter']:null).(isset($datagrid["order"]["fields"][$key]['editor'])?",\"editor\":".$datagrid["order"]["fields"][$key]['editor']:null).(isset($datagrid["order"]["fields"][$key]['methods'])?",".$datagrid["order"]["fields"][$key]['methods']:null)."'>".$key."</th>";endforeach;endif; ?></tr></thead></table>
<?php if($is_passel == 1): ?><div id="<?php echo ($id_list["toolbar_order"]); ?>">
<a href="#" name="menu-select-exchange-order" class="easyui-menubutton" data-options="iconCls:'icon-add',menu:'#mbut-select-exchange-order'">添加货品</a>
<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-remove',plain:true" onclick="exchangeOrder.remove()">删除</a>
<label><input type="checkbox" id="exchange_by_scale"/>按比例循环替换</label></br>
<div id="mbut-select-exchange-order"><div>添加单品</div><div>添加组合装</div></div>
</div><?php endif; ?>
</div>
<div style="height: 60%;">
<table id="<?php echo ($datagrid["spec"]["id"]); ?>" class="easyui-datagrid" data-options='<?php $dataOptions = array_merge(array ( 'border' => false, 'fit' => true, 'fitColumns' => true, 'rownumbers' => true, 'singleSelect' => true, 'pagination' => true, 'pageList' => array ( 0 => 20, 1 => 50, 2 => 100, ), 'pageSize' => 20, ), $datagrid["spec"]["options"]);if(isset($dataOptions['toolbar']) && substr($dataOptions['toolbar'],0,1) != '#'): unset($dataOptions['toolbar']); endif;if(isset($dataOptions['methods'])): unset($dataOptions['methods']);endif; echo trim(json_encode($dataOptions), '{}[]').((isset($datagrid["spec"]["options"]['toolbar']) && substr($datagrid["spec"]["options"]['toolbar'],0,1) != '#')?',"toolbar":'.$datagrid["spec"]["options"]['toolbar']:null).(isset($datagrid["spec"]["options"]['methods'])? ','.$datagrid["spec"]["options"]['methods']:null); ?>' style="<?php echo ($datagrid["spec"]["style"]); ?>" ><thead><tr><?php if(is_array($datagrid["spec"]["fields"])):foreach ($datagrid["spec"]["fields"] as $key=>$arr):if(isset($arr['formatter'])):unset($arr['formatter']);endif;if(isset($arr['methods'])):unset($arr['methods']);endif;if(isset($arr['editor'])):unset($arr['editor']);endif;echo "<th data-options='".trim(json_encode($arr), '{}[]').(isset($datagrid["spec"]["fields"][$key]['formatter'])?",\"formatter\":".$datagrid["spec"]["fields"][$key]['formatter']:null).(isset($datagrid["spec"]["fields"][$key]['editor'])?",\"editor\":".$datagrid["spec"]["fields"][$key]['editor']:null).(isset($datagrid["spec"]["fields"][$key]['methods'])?",".$datagrid["spec"]["fields"][$key]['methods']:null)."'>".$key."</th>";endforeach;endif; ?></tr></thead></table>
<div id="<?php echo ($id_list["toolbar"]); ?>">
<a href="#" name="menu-select-exchange-spec" class="easyui-menubutton" data-options="iconCls:'icon-add',menu:'#mbut-select-exchange-spec'">添加货品</a>
<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-remove',plain:true" onclick="exchangeSpec.remove()">删除</a>
<label style="margin-left: 150px;">将以上货品换成以下货品</label>
<div id="mbut-select-exchange-spec"><div>添加单品</div><div>添加组合装</div></div>
</div>
</div>
<script type="text/javascript">
//# sourceURL=exchange_order.js
var is_passel='<?php echo ($is_passel); ?>';
var warehouse;
var dialog;
$(function(){
	$('#<?php echo ($datagrid["spec"]["id"]); ?>').datagrid().datagrid('enableCellEditing');
	setTimeout(function(){
		if(!(is_passel)){
			initOrderDatagrid();
			warehouse=edit_trade_element[3].combobox('getValue');
			dialog=edit_trade_element['dialog_id'];
		}else{
			$('#<?php echo ($datagrid["order"]["id"]); ?>').datagrid().datagrid('enableCellEditing');
			warehouse='<?php echo ($warehouse); ?>';
			dialog='<?php echo ($id_list["exchange_dialog"]); ?>';
		}
		exchangeOrder = new ThinDatagrid($('#<?php echo ($datagrid["order"]["id"]); ?>'),undefined,false);
		exchangeSpec = new ThinDatagrid($('#<?php echo ($datagrid["spec"]["id"]); ?>'),undefined,false);
	},0);
});
//添加--需要替换的货品，订单编辑打开的换货不需添加
if(is_passel){
$($(".easyui-menubutton[name='menu-select-exchange-order']").menubutton().menubutton('options').menu).menu({
	onClick:function(item){
	 switch(item.text){
	 case '添加单品':
		 var params={'prefix':'exchange_order','type':true,'warehouse_id':warehouse,'model':'stock',};
		 $('#' + dialog).richDialog('goodsSpec', addSpecToOrder, params, false);
		 break;
	 case '添加组合装':
		 $('#' + dialog).richDialog('goodsSuite', addSuiteToOrder, 'exchange_order', false);
		 break;
	 }}
});
}
//添加--换成的货品
$($(".easyui-menubutton[name='menu-select-exchange-spec']").menubutton().menubutton('options').menu).menu({
	onClick:function(item){
	var spec_prefix='';var suite_prefix='';
	 if(is_passel){spec_prefix='passel_exchange_spec';suite_prefix='passel_exchange_suite';}else{spec_prefix='edit_exchange_spec';suite_prefix='edit_exchange_suite';}
	 switch(item.text){
	 case '添加单品':
		 var params={'prefix':spec_prefix,'type':true,'warehouse_id':warehouse};
		 $('#' + dialog).richDialog('goodsSpec', addSpecToOrder, params, false);
		 break;
	 case '添加组合装':
		 $('#' + dialog).richDialog('goodsSuite', addSuiteToOrder, suite_prefix , false);
		 break;
	 }}
});
//编辑换货--提交
submitEditExchangeOrderDialog=function(){
	var rows=$('#<?php echo ($datagrid["spec"]["id"]); ?>').datagrid('getRows');
	if(rows.length==0){messager.info('请选择换货货品');return;};
	var row=editTrade.selector.datagrid('getSelected');
	if (!row.sto_id) {messager.info('无效子订单');return;};
	var trade_row=edit_trade_element['show_dg'].datagrid('getSelected');
	Post("<?php echo U('TradeCheck/exchangeOrder');?>", {id:row.sto_id,order:rows,version_id:trade_row.version_id}, function(res){
		if (res.status) {messager.info(res.info);return;};
		editTrade.selector.datagrid('loadData',res.data);
		$('#<?php echo ($id_list["form_id"]); ?>').form('load',res.trade);
		trade_row.goods_amount=res.trade.goods_amount;
		trade_row.discount=res.trade.discount;
		trade_row.receivable=res.trade.receivable;
		trade_row.version_id=res.trade.version_id;
		$('#'+edit_trade_element['exchange']).dialog('close');
	});
}
//批量换货--提交
submitPasselExchangeDialog=function(){
	var orders=$('#<?php echo ($datagrid["order"]["id"]); ?>').datagrid('getRows');
	if(orders.length==0){messager.alert('原货品不能为空');return false;}
	var specs=$('#<?php echo ($datagrid["spec"]["id"]); ?>').datagrid('getRows');
	if(specs.length==0){messager.alert('更换货品不能为空');return false;}
	is_scale=document.getElementById("exchange_by_scale").checked;
	var data=JSON.parse('<?php echo ($ids); ?>');
	ids=JSON.stringify(data.id);
	version=data.version;
	Post("<?php echo U('TradeCheck/passelExchange');?>", {ids:ids,order:orders,spec:specs,version_id:version,scale:is_scale}, function(res){
		if(res.status){
			$.fn.richDialog("response", res.info, 'tradecheck');
		}else{
			messager.alert('换货成功');
			tradeCheck.refresh();
		}
		$('#'+tradeCheck.params.edit.id).dialog('close');
	});
}
function initOrderDatagrid(){
	var row=editTrade.selector.datagrid('getSelected');
	$('#<?php echo ($datagrid["order"]["id"]); ?>').datagrid('loadData',{total:1,rows:[row]});
}
addSpecToOrder=function(spec_dg_id,sub_dg_id){
	addExchangeOrder(sub_dg_id,0);
};
addSuiteToOrder=function(suite_dg_id,cb_params,sub_dg_id){
	addExchangeOrder(sub_dg_id,1);
};
addExchangeOrder=function(sub_dg_id,is_suite){
	var spec_dg=$('#'+sub_dg_id);
    var spec_rows=spec_dg.datagrid('getRows');
    if(sub_dg_id=='passel_exchange_spec_sub_goods_spec_select_datagrid'||sub_dg_id=='passel_exchange_suite_tabs_detail_datagrid'||sub_dg_id=='edit_exchange_spec_sub_goods_spec_select_datagrid'||sub_dg_id=='edit_exchange_suite_tabs_detail_datagrid'){
    	var show_dg=$('#<?php echo ($datagrid["spec"]["id"]); ?>');
    }else{
    	var show_dg=$('#<?php echo ($datagrid["order"]["id"]); ?>');
    }
    $('#'+dialog).dialog('close');
    var show_rows=show_dg.datagrid('getRows');
    var flag=false;
    var show_index=0;
    for(var i in spec_rows){
    	spec_rows[i].num= !spec_rows[i].num?1:parseFloat(spec_rows[i].num); flag=false;
    	for(var x in show_rows){
    		if(spec_rows[i].spec_id==show_rows[x].spec_id){
    			show_index=show_dg.datagrid('getRowIndex',show_rows[x]);
    			show_rows[x].num=parseFloat(show_rows[x].num)+parseFloat(spec_rows[i].num);
    			show_dg.datagrid('refreshRow',show_index);
    			flag=true;
    		}
    	}
    	if(!flag){
    		spec_rows[i].is_suite=is_suite;
    		spec_rows[i].price=spec_rows[i].retail_price;
    		show_dg.datagrid('appendRow',spec_rows[i]);
    	}
	}
};
</script>