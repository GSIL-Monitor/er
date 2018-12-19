<?php if (!defined('THINK_PATH')) exit();?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- <link rel="stylesheet" type="text/css" href="/Public/Css/easyui.css">
<link rel="stylesheet" type="text/css" href="/Public/Css/icon.css">
<link rel="stylesheet" type="text/css" href="/Public/Css/table.css">
<script type="text/javascript" src="/Public/Js/jquery.min.js"></script>
<script type="text/javascript" src="/Public/Js/jquery.easyui.min.js"></script>
<script type="text/javascript" src="/Public/Js/easyui-lang-zh_CN.js"></script>
<script type="text/javascript" src="/Public/Js/datagrid.extends.js"></script>
<script type="text/javascript" src="/Public/Js/jquery.extends.js"></script>
<script type="text/javascript" src="/Public/Js/tabs.util.js"></script>
<script type="text/javascript" src="/Public/Js/erp.util.js"></script>
<script type="text/javascript" src="/Public/Js/rich-datagrid.util.js"></script>
<script type="text/javascript" src="/Public/Js/thin-datagrid.util.js"></script>
<script type="text/javascript" src="/Public/Js/datalist.util.js"></script>
<script type="text/javascript" src="/Public/Js/area.js"></script>
-->
</head>
<body>
<!-- layout-datagrid -->
<div class="easyui-layout" data-options="fit:true" style="width:100%;height:100%;overflow:hidden;" id="panel_layout">
<div class="easyui-panel" title="搜索列表" data-options="iconCls:'icon-search',region:'west',split:true,collapsed:false" style="width:230px;background: #eee;" id="panel_search">
	
	<div id="sales_stockout_search" class="easyui-tabs" border="false" style="overflow: hidden" data-options="fit:true">
		<div title="快捷查询" style="background: #eee;" data-options="tools:[{iconCls:'icon-mini-refresh', handler:function(){stockSalesPrint.loadData(1);}}]">
			<div class="form-div" id="<?php echo ($id_list["fast_div"]); ?>">
				<fieldset style="border:1px solid #95B8E7;padding: 2px;margin-top: 2px;margin-right: 8px;"><legend>打印情况</legend>
					　　物流单<a href="javascript:void(0)" data-options="plain:true" onclick="stockSalesPrint.fastSearch(this,'search[fast_logis_printed]')"	>　<label fast_name="1">已打印</label></a><a href="javascript:void(0)" data-options="plain:true" onclick="stockSalesPrint.fastSearch(this,'search[fast_no_logis_printed]')"		>　<label fast_name="1">未打印</label></a></br>
					　　发货单<a href="javascript:void(0)" data-options="plain:true" onclick="stockSalesPrint.fastSearch(this,'search[fast_goods_printed]')"	>　<label fast_name="1">已打印</label></a><a href="javascript:void(0)" data-options="plain:true" onclick="stockSalesPrint.fastSearch(this,'search[fast_no_goods_printed]')"		>　<label fast_name="1">未打印</label></a></br>
					<a href="javascript:void(0)" fast_num_type = 'fast_printed_not_stockout' data-options="plain:true" onclick="stockSalesPrint.fastSearch(this,'search[fast_printed_not_stockout]')"	>　　<label fast_name="1">已打印物流单待发货</label><label fast_num="1" style="color:red" ></label></a></br>
					<a href="javascript:void(0)" fast_num_type = 'fast_stockout_not_printed' data-options="plain:true" onclick="stockSalesPrint.fastSearch(this,'search[fast_stockout_not_printed]')"	>　　<label fast_name="1">已发货未打印物流单</label><label fast_num="1" style="color:red" ></label></a></br>
				</fieldset>
				<fieldset style="border:1px solid #95B8E7;padding: 2px;margin-top: 2px;margin-right: 8px;"><legend>出库情况</legend>
					<a href="javascript:void(0)" data-options="plain:true" onclick="stockSalesPrint.fastSearch(this,'search[fast_no_stockout]')"			>　　<label fast_name="1">待发货</label></a></br>
					<a href="javascript:void(0)" data-options="plain:true" onclick="stockSalesPrint.fastSearch(this,'search[fast_is_stockout]')"			>　　<label fast_name="1">已发货未完成</label></a></br>
					<a href="javascript:void(0)" data-options="plain:true" onclick="stockSalesPrint.fastSearch(this,'search[fast_is_finish]')"			>　　<label fast_name="1">已完成</label></a></br>
					<a href="javascript:void(0)" data-options="plain:true" onclick="stockSalesPrint.fastSearch(this,'search[fast_is_checked]')"			>　　<label fast_name="1">已验货</label></a></br>
					<a href="javascript:void(0)" data-options="plain:true" onclick="stockSalesPrint.fastSearch(this,'search[fast_no_checked]')"			>　　<label fast_name="1">未验货</label></a></br>
					<a href="javascript:void(0)" data-options="plain:true" onclick="stockSalesPrint.fastSearch(this,'search[fast_is_weighted]')"			>　　<label fast_name="1">已称重</label></a></br>
					<a href="javascript:void(0)" data-options="plain:true" onclick="stockSalesPrint.fastSearch(this,'search[fast_no_weighted]')"			>　　<label fast_name="1">未称重</label></a></br>
				</fieldset>
				<fieldset style="border:1px solid #95B8E7;padding: 2px;margin-top: 2px;margin-right: 8px;"><legend>特殊标识</legend>
					<a href="javascript:void(0)" fast_num_type = 'fast_is_blocked' data-options="plain:true" onclick="stockSalesPrint.fastSearch(this,'search[fast_is_blocked]')"			>　　<label fast_name="1">已拦截订单</label><label fast_num="1" style="color:red" ></label></a></br>
					<a href="javascript:void(0)" data-options="plain:true" onclick="stockSalesPrint.fastSearch(this,'search[fast_no_blocked]')"			>　　<label fast_name="1">未拦截订单</label></a></br>
					<a href="javascript:void(0)" data-options="plain:true" onclick="stockSalesPrint.fastSearch(this,'search[fast_has_cliented]')"		>　　<label fast_name="1">有客户备注</label></a></br>
					<a href="javascript:void(0)" data-options="plain:true" onclick="stockSalesPrint.fastSearch(this,'search[fast_no_cliented]')"			>　　<label fast_name="1">无客户备注</label></a></br>
				</fieldset>
				<fieldset style="border:1px solid #95B8E7;padding: 2px;margin-top: 2px;margin-right: 8px;"><legend>下单时间</legend>
					<a href="javascript:void(0)" data-options="plain:true" onclick="stockSalesPrint.fastSearch(this,'search[fast_one_day]')"				>　　<label fast_name="1">一天内</label></a></br>
					<a href="javascript:void(0)" data-options="plain:true" onclick="stockSalesPrint.fastSearch(this,'search[fast_tow_day]')"				>　　<label fast_name="1">两天内</label></a></br>
					<a href="javascript:void(0)" data-options="plain:true" onclick="stockSalesPrint.fastSearch(this,'search[fast_one_week]')"			>　　<label fast_name="1">一周内</label></a></br>
					<a href="javascript:void(0)" data-options="plain:true" onclick="stockSalesPrint.fastSearch(this,'search[fast_one_month]')"			>　　<label fast_name="1">一月内</label></a></br>
				</fieldset>
			</div>
		</div>
		<div title="筛选条件" style="background: #eee;" >
			<form id="<?php echo ($id_list["form"]); ?>">
				<div class="form-div" style="background: #eee;margin-bottom: 2px;margin-left: 100px;">
					<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-search'" onclick="stockSalesPrint.searchData(this);">搜索</a>
					<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-redo'" onclick="stockSalesPrint.loadData();">重置</a>
				</div>
				<hr style="border:none;border-top:2px dotted #95B8E7;width: 220px;background: #eee;">
				<div  style="position:absolute;height:80%; width: 100%;overflow:auto;background: #eee;">
					<div class="form-div"><label>　　　仓库：</label><select class="easyui-combobox sel" name="search[warehouse_id]" data-options="panelHeight:'230'"> <?php if(is_array($warehouse_array)): $i = 0; $__LIST__ = $warehouse_array;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?> </select></div>
					<div class="form-div"><label>　出库单号：</label><input class="easyui-textbox txt" type="text" name="search[stockout_no]" /></div>
					<div class="form-div"><label>　订单编号：</label><input class="easyui-textbox txt" type="text" name="search[src_order_no]" /></div>
					<div class="form-div"><label>　打印批次：</label><input class="easyui-textbox txt" type="text" name="search[batch_no]" /></div>
					<div class="form-div"><label>发货单打印：</label><input class="easyui-combobox txt" name="search[sendbill_print_status]" data-options="valueField:'id',textField:'name',data:formatter.get_data('print_status','def',<?php echo ($print_status_list["stockout_sendbill_print_status"]); ?>==3?'all':<?php echo ($print_status_list["stockout_sendbill_print_status"]); ?>,true)"/></div>
					<div class="form-div"><label>物流单打印：</label><input class="easyui-combobox txt" name="search[logistics_print_status]" data-options="valueField:'id',textField:'name',data:formatter.get_data('print_status','def',<?php echo ($print_status_list["stockout_logistics_print_status"]); ?>==3?'all':<?php echo ($print_status_list["stockout_logistics_print_status"]); ?>,true)"/></div>
					<hr style="border:none;border-top:2px dotted #95B8E7;">
					<div class="form-div"><label>出库单状态：</label><input class="easyui-combobox txt" name="search[status]" data-options="valueField:'id',textField:'name',data:formatter.get_data('salesstockout_status')"/></div>
					<div class="form-div"><label>　发货状态：</label><input class="easyui-combobox txt" name="search[consign_status]" data-options="valueField:'id',textField:'name',data:formatter.get_data('consign_status')"/></div>
					<div class="form-div"><label>　订单来源：</label><input class="easyui-combobox txt" name="search[trade_from]" data-options="valueField:'id',textField:'name',data:formatter.get_data('trade_from')"/></div>
					<div class="form-div"><label>　财审状态：</label><input class="easyui-combobox txt" name="search[trade_fc_status]" data-options="valueField:'id',textField:'name',data:formatter.get_data('trade_fc_status')" /></div>
					<div class="form-div"><label>　拦截原因：</label><input class="easyui-combobox txt" name="search[block_reason]" data-options="valueField:'id',textField:'name',data:formatter.get_data('stockout_block_reason')"/></div>
					<div class="form-div"><label>出库单标记：</label><input id="<?php echo ($id_list["search_flag"]); ?>" class="easyui-combobox txt" name="search[flag_id]" data-options="valueField:'id',textField:'name'"/></div>
					<hr style="border:none;border-top:2px dotted #95B8E7;">
					<div class="form-div"><label>　物流单号：</label><input class="easyui-textbox txt" type="text" name="search[logistics_no]" /></div>
					<div class="form-div"><label>多物流单号：</label><input class="easyui-textbox txt" type="text" name="search[multi_logistics_no]" /></div>
					<div class="form-div"><label>档口唯一码：</label><input class="easyui-textbox txt" type="text" name="search[unique_code]" /></div>
					<div class="form-div"><label>　客户网名：</label><input class="easyui-textbox txt" type="text" name="search[buyer_nick]" /></div>
					<div class="form-div"><label>　　收件人：</label><input class="easyui-textbox txt" type="text" name="search[receiver_name]" /></div>
					<div class="form-div"><label>　　　备注：</label><input class="easyui-combobox txt" name="search[remark_id]" data-options="panelHeight:'auto',valueField:'id',textField:'name',data:formatter.get_data('remark')"/> </div>
					<div class="form-div"><label>　客服备注：</label><input class="easyui-textbox txt" type="text" name="search[cs_remark]" /></div>
					<div class="form-div"><label>　买家留言：</label><input class="easyui-textbox txt" type="text" name="search[buyer_message]" /></div>
					<hr style="border:none;border-top:2px dotted #95B8E7;">
					<div class="form-div"><label>　发货时间：</label><input class="easyui-datetimebox txt" type="text" name="search[consign_time_start]" data-options="editable:false"/></div>
					<div class="form-div"><label>　　　　至：</label><input class="easyui-datetimebox txt" type="text"    name="search[consign_time_end]" data-options="editable:false"/></div>
					<div class="form-div"><label>　商家编码：</label><input class="easyui-textbox txt" type="text" name="search[spec_no]" /></div>
					<div class="form-div"><label>组合装编码：</label><input class="easyui-textbox txt" type="text" name="search[suite_no]" /></div>
					<div class="form-div"><label>组合装名称：</label><input class="easyui-textbox txt" type="text" name="search[suite_name]" /></div>
					<div class="form-div"><label>　货品品牌：</label><select class="easyui-combobox sel" name="search[brand_id]">	<option value="all">全部</option>	<?php if(is_array($goods_brand)): $i = 0; $__LIST__ = $goods_brand;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?></select></div>
					<div class="form-div"><label>　　　省份：</label><input id="shop_add_province" class="easyui-combobox txt" type="text" name="search[receiver_province]"  data-options="multiple:true,onChange:function(newValue, oldValue){province_select(newValue, oldValue);}"/></div>
					<div class="form-div"><label>　　　城市：</label><input id="shop_add_city" class="easyui-combobox txt" type="text" name="search[receiver_city]"/></div>
					<div class="form-div"><label>　　　区县：</label><input id="shop_add_country" class="easyui-combobox txt" type="text" name="search[receiver_country]"/></div>
					<hr style="border:none;border-top:2px dotted #95B8E7;">
					<div class="form-div"><label>　品名包含：</label><input class="easyui-textbox txt" type="text" name="search[goods_name_include]" /></div>
					<div class="form-div"><label>品名不包含：</label><input class="easyui-textbox txt" type="text" name="search[goods_name_not_include]" /></div>
					<div class="form-div"><label>　包含货品：</label><input id="include_goods_type_count" class="easyui-textbox txt" type="text" data-options="editable:false,buttonText: '...'" /></div>
					<div class="form-div"><label></label><input id="include_goods_type_count_hidden" type="hidden" value="" name="search[include_goods_type_count]" /></div>
					<div class="form-div"><label>不包含货品：</label><input id="not_include_goods_type_count" class="easyui-textbox txt" type="text" data-options="editable:false,buttonText: '...'" /></div>
					<div class="form-div"><label></label><input id="not_include_goods_type_count_hidden" type="hidden" value="" name="search[not_include_goods_type_count]" /></div>

					<div class="form-div">
						<label>　实付金额：</label>
						<input type="text" class="easyui-numberbox" data-options="min:0,precision:4" style="width:52px;" name="search[small_paid]"/> 到 <input type="text" class="easyui-numberbox" data-options="min:0,precision:4" style="width:52px;" name="search[big_paid]"/>
					</div>
					<div class="form-div">
						<label>　预估重量：</label>
						<input type="text" class="easyui-numberbox" data-options="min:0,precision:4" style="width:52px;" name="search[small_calc_weight]"/> 到 <input type="text" class="easyui-numberbox" data-options="min:0,precision:4" style="width:52px;" name="search[big_calc_weight]"/>
					</div>
					<div class="form-div">
						<label>　货品数量：</label>
						<input type="text" class="easyui-numberbox" style="width:52px;" name="search[small_number]"/> 到 <input type="text" class="easyui-numberbox" style="width:52px;" name="search[big_number]"/>
					</div>
					<div class="form-div">
						<label>　货品种类：</label>
						<input type="text" class="easyui-numberbox" style="width:52px;" name="search[small_type]"/> 到 <input type="text" class="easyui-numberbox" style="width:52px;" name="search[big_type]"/>
					</div>
					<hr style="border:none;border-top:2px dotted #95B8E7;">
					<div class="form-div"><label style="margin-left: 12px;">一单一货：</label><input extend_type="complex-check" onclick="$(this).triStateCheckbox('click')" name="search[one_order_one_good]" value="" type="checkbox" /><label style="margin-left: 12px;">是否要发票：</label><input extend_type="complex-check" onclick="$(this).triStateCheckbox('click')" name="search[has_invoice]" value="" type="checkbox" /></div>
					<div class="form-div"><label>被拦截订单：</label><input extend_type="complex-check" onclick="$(this).triStateCheckbox('click')" name="search[is_block]" value="" type="checkbox" /><label style="margin-left: 12px;">搜索档口单：</label><input extend_type="complex-check" onclick="$(this).triStateCheckbox('click')" name="search[is_stalls]" value="" type="checkbox" /></div>
					<div class="form-div"><label>搜索多物流：</label><input name="search[multi_logistics]" onclick="stockSalesPrint.isSeachMultiLogistis($(this))" value="0" search="multiLogistics" type="checkbox" /></div>
					<hr style="border:none;border-top:2px dotted #95B8E7;">
					<div class="form-div"><label>按商家编码排序：</label><input name="search[radio]" value="1" search="multiLogistics" type="radio" onclick="stockSalesPrint.select_radio($(this))" /></div>
					<div class="form-div"><label>　　按货位排序：</label><input name="search[radio]" value="2" search="multiLogistics" type="radio" onclick="stockSalesPrint.select_radio($(this))" /></div>
					<div class="form-div"><label></label><input id="print_batch_search" name="search[print_batch]" value="all"  type="hidden" /></div>
					<div class="form-div"><label></label><input type="hidden" name="search[passel_logistics_nos]" value="" id="passel_logistics_nos" /></div>
				</div>
			</form>
		</div>
	</div>

</div>
<!-- layout-center-datagrid -->
 
<div data-options="region:'center'" style="height:100%;">
<div data-options="region:'center',fit:true" style="width:100%;height:70%;background:#eee;"><table id="<?php echo ($datagrid["id"]); ?>" class="easyui-datagrid" data-options='<?php $dataOptions = array_merge(array ( 'border' => false, 'fit' => true, 'fitColumns' => true, 'rownumbers' => true, 'singleSelect' => true, 'pagination' => true, 'pageList' => array ( 0 => 20, 1 => 50, 2 => 100, ), 'pageSize' => 20, ), $datagrid["options"]);if(isset($dataOptions['toolbar']) && substr($dataOptions['toolbar'],0,1) != '#'): unset($dataOptions['toolbar']); endif;if(isset($dataOptions['methods'])): unset($dataOptions['methods']);endif; echo trim(json_encode($dataOptions), '{}[]').((isset($datagrid["options"]['toolbar']) && substr($datagrid["options"]['toolbar'],0,1) != '#')?',"toolbar":'.$datagrid["options"]['toolbar']:null).(isset($datagrid["options"]['methods'])? ','.$datagrid["options"]['methods']:null); ?>' style="<?php echo ($datagrid["style"]); ?>" ><thead><tr><?php if(is_array($datagrid["fields"])):foreach ($datagrid["fields"] as $key=>$arr):if(isset($arr['formatter'])):unset($arr['formatter']);endif;if(isset($arr['methods'])):unset($arr['methods']);endif;if(isset($arr['editor'])):unset($arr['editor']);endif;echo "<th data-options='".trim(json_encode($arr), '{}[]').(isset($datagrid["fields"][$key]['formatter'])?",\"formatter\":".$datagrid["fields"][$key]['formatter']:null).(isset($datagrid["fields"][$key]['editor'])?",\"editor\":".$datagrid["fields"][$key]['editor']:null).(isset($datagrid["fields"][$key]['methods'])?",".$datagrid["fields"][$key]['methods']:null)."'>".$key."</th>";endforeach;endif; ?></tr></thead></table></div>
<!-- layout-south-tabs -->
<div data-options="region:'south',split:true" style="height:30%;background:#eee;overflow:hidden;">
<?php if($datagrid["setTabs"] == 1): ?><a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-edit'" onclick="<?php echo ($datagrid["setTabsClick"]); ?>" style="position: absolute;margin-left: 650px;z-index:10000;">设置表头</a><?php endif; ?>
 <div class="easyui-tabs" data-options="fit:true,border:false,plain:true" id="<?php echo ($id_list["tab_container"]); ?>"> 
</div> </div> 
</div>
<script type="text/javascript"> 
$(function(){
setTimeout('add_tabs(JSON.parse(\'<?php echo ($arr_tabs); ?>\'))',0);
}); 
/*
$(function(){ add_tabs(JSON.parse('<?php echo ($arr_tabs); ?>')); 
$('body').show();
$('#panel_layout').layout('resize',{height:$('#panel_layout').parent().height()});
}); 
*/
</script>

</div>
<!-- dialog -->

<div id="<?php echo ($id_list["file_dialog"]); ?>" class="easyui-panel" style="padding:25px 50px 25px 50px">
	<form id="<?php echo ($id_list["file_form"]); ?>" method="post" enctype="multipart/form-data">
		<div style="margin-bottom:25px">
			<input class="easyui-filebox" name="file" data-options="prompt:'请选择文件...','buttonText':'请选择文件'" style="width:100%;">
		</div>
		<div align="center">
			<a href="javascript:void(0)" class="easyui-linkbutton" style="width:50%" onclick="stockSalesPrint.import()">上传</a>
		</div>
	</form>
</div>
<div id="messager"></div>
<div id="<?php echo ($id_list["dialog"]); ?>"></div>
<div id="<?php echo ($id_list["print_dialog"]); ?>"></div>
<div id="<?php echo ($id_list["print_batch"]); ?>"></div>
<div id="<?php echo ($id_list["include_goods"]); ?>"></div>
<div id="include_show_dialog"></div>
<div id="<?php echo ($id_list["continue_print_result"]); ?>"></div>
<div id="<?php echo ($id_list["logistics_dialog"]); ?>">
	<div id="<?php echo ($id_list["logistics_dialog_datagrid_toolbar"]); ?>" style="padding:5px;height:auto">
		<a href="javascript:void(0)"  name = "button_save" class="easyui-linkbutton" data-options="iconCls:'icon-save'" onclick="saveWaybillByType()">继续保存</a>
	</div>
	<table id="<?php echo ($id_list["logistics_dialog_datagrid"]); ?>"></table>
</div>
<div id="editwarehouse"></div>
<div id="printerSetting"></div>
<div id="setPrinter"></div>
<div id="ssp_add_multi_dialog"></div>
<div id="stocksalesprint_revertreason_id"></div>
<div id="stocksalesprint_chgprintstatus_id"></div>
<div id = 'writeWeight'></div>
<div id = 'writePackage'></div>
<div id="<?php echo ($id_list["fileDialog"]); ?>" class="easyui-panel" style="padding:25px 50px 25px 50px">
	<form id="<?php echo ($id_list["fileForm"]); ?>" method="post" enctype="multipart/form-data">
		<div style="margin-bottom:25px">
			<input class="easyui-filebox" name="file" data-options="prompt:'请选择文件...','buttonText':'请选择文件'" style="width:100%;">
		</div>
		<div align="center">
			<a href="javascript:void(0)" class="easyui-linkbutton" style="width:50%" onclick="stockSalesPrint.upload()">上传</a>
		</div>
	</form>
</div>

<!-- toolbar -->

	<div id="<?php echo ($id_list["tool_bar"]); ?>" style="padding:5px;height:auto">
		<form id="<?php echo ($id_list["form_main"]); ?>">
			<div class="form-div">
				<label>店铺：</label><select style="width: 120px;" class="easyui-combobox sel" name="search[shop_id]" data-options=""> <option value="all">全部</option><?php if(is_array($list["shop"])): $i = 0; $__LIST__ = $list["shop"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?></select>
				<label>物流公司：</label><select style="width: 120px;" class="easyui-combobox sel" name="search[logistics_id]" data-options=""> <option value="all">全部</option><?php if(is_array($list["logistics"])): $i = 0; $__LIST__ = $list["logistics"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?></select>
				<label>原始单号：</label><input style="width: 120px;" class="easyui-textbox txt" type="text" name="search[src_tids]" />
				<label>手机号：</label><input style="width: 120px;" class="easyui-textbox txt" type="text" name="search[receiver_mobile]" data-options="valueField:'id',textField:'name'"/>
				<label>货品分类：</label><input style="width: 120px;" id="seach_goods_class" class="txt" value="-1" name="search[class_id]" data-options="url:'<?php echo U('Goods/GoodsClass/getTreeClass');?>?type=all',method:'post',required:true"/>
				<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-search'" onclick="stockSalesPrint.searchData(this);">搜索</a>
				<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-redo'" onclick="stockSalesPrint.loadData();">重置</a>
			<label class="form-div">
				<a href="<?php echo ($faq_url); ?>" target="_blank" class="easyui-linkbutton" title="点击查看常见问题" data-options="iconCls:'icon-help',plain:true">常见问题</a>
			</label>
			</div>
		</form>
		<a href="javascript:void(0)" class="easyui-menubutton" data-options="iconCls:'icon-print',menu:'#stocksalesprint_print'" >打印</a>
		<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-edit',plain:true" onClick="stockSalesPrint.chgPrintStatus()" >修改打印状态</a>
		<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-ok',plain:true" 	 onclick="stockSalesPrint.message()">确认发货</a>
		<a href="javascript:void(0)" class="easyui-menubutton" data-options="iconCls:'icon-back',plain:true,menu:'#stocksalesprint_revert'" onclick="stockSalesPrint.revertCheck()">驳回审核</a>

		<a href="javascript:void(0)" class="easyui-menubutton" data-options="iconCls:'icon-truck',plain:true,menu:'#stockoutPrint-logistics'">修改物流</a>
		<a href="javascript:void(0)" class="easyui-menubutton" data-options="iconCls:'icon-page-edit',plain:true,menu:'#stocksalesprint_add_logistics_no'"  onclick="addWaybill()">填写物流单号</a>
		<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-sign',plain:true" onclick="stockSalesPrint.setFlag()">标记管理</a>
		<label class="one-character-width">标记单据</label>
		<input id="<?php echo ($id_list["set_flag"]); ?>" class="easyui-combobox" style="width:100px;"/>
		<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-search',plain:true" onclick="stockSalesPrint.checkNumber()">查看号码</a>
		<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-search',plain:true" onclick="stockSalesPrint.showGoodsPic()">显示货品图片</a>
		<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-table-edit',plain:true" onclick="setDatagridField('Stock/StockSalesPrint','salesstockout_order','<?php echo ($datagrid["id"]); ?>',1)">设置表头</a>
		<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-back',plain:true" onClick="open_menu('打印模板', '<?php echo U('Setting/NewPrintTemplate/getNewPrintTemplate');?>')">打印模板</a>
		<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-printer',plain:true" 	 onclick="stockSalesPrint.printBatch()">打印批次</a>
		<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-ok',plain:true" 	 onclick="stockSalesPrint.synchronousLogistics()">预物流同步</a>
		<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-search',plain:true" onclick="stockSalesPrint.searchLogisticsNos()">根据物流单号筛选订单</a>

		<div id="stocksalesprint_revert" style="width:100px;">
			<div data-options="iconCls:'icon-back'" onclick="stockSalesPrint.revertConsignStatus(1)">驳回验货</div>
			<div data-options="iconCls:'icon-back'" onclick="stockSalesPrint.revertConsignStatus(2)">驳回称重</div>
			<div data-options="iconCls:'icon-back'" onclick="stockSalesPrint.cancelBlock()">取消拦截</div>
			<div data-options="iconCls:'icon-back'" onclick="stockSalesPrint.revertStockout()">撤销出库</div>
		</div>
		<div id="stocksalesprint_add_logistics_no" style="width:100px;">
			<div data-options="iconCls:'icon-excel'" onclick="stockSalesPrint.importDialog()">导入物流单号</div>
			<div data-options="iconCls:'icon-down_tmp'" onclick="stockSalesPrint.downloadTemplet('import_logistics')">下载模板</div>
		</div>
		<div id="stocksalesprint_print">
			<div data-options="iconCls:'icon-print'" onclick="block_order('newPrintLogis',0)">打印物流单</div>
            <div data-options="iconCls:'icon-print'" onclick="block_order('newPrintGoods')">打印发货单</div>
			<div data-options="iconCls:'icon-print'" onclick="block_order('printPickList')">打印分拣单</div>
			<div data-options="iconCls:'icon-print'" onclick="block_order('newPrintOrder',0)">打印发货单和物流单</div>
			<div data-options="iconCls:'icon-print'" onclick="block_order('newPrintLogisticsAndPickList',0)">打印分拣单和物流单</div>
		</div>
		<!--<div id="stocksalesprint_link_template">-->
			<!--<div data-options="iconCls:'icon-print'" onClick="open_menu('打印模板(新)', '<?php echo U('Setting/NewPrintTemplate/getNewPrintTemplate');?>')">打印模板(新)</div>-->
		<!--</div>-->
		<div id="print_menu" class="easyui-menu" style="width:120px;" noline="true">
			<div data-options="iconCls:'icon-print'" onclick="block_order('newPrintLogis',3)">批量打印多物流</div>
			<div data-options="iconCls:'icon-print'" onclick="block_order('printSfOrder')">打印线下多物流</div>
			<div data-options="iconCls:'icon-ok',plain:true" onclick="stockSalesPrint.synchronousLogistics()">预物流同步</div>
		</div>
	<!--	<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-ok',plain:true" 	 onclick="stockSalesPrint.cancel()">取消委外单</a>
		<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-ok',plain:true" 	 onclick="stockSalesPrint.hand()">手动推送订单</a>
	-->
		<div id="stockoutPrint-logistics" style="width:100px;height:300px;" noline="true">
			<?php if(is_array($list["chg_logistics_list"])): $i = 0; $__LIST__ = $list["chg_logistics_list"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><div onclick="stockSalesPrint.chgLogistics(<?php echo ($vo["id"]); ?>)"><span style="margin-left: -23px;"><?php echo ($vo["name"]); ?></span></div><?php endforeach; endif; else: echo "" ;endif; ?>
		</div>

	</div>
	<div>
		<style> #goods_print_data_html, #goods_print_data_html td{
			/*font-size: 12px;*/
			border-width: 1px;
			border-style: solid;
			border-color: #000;
			border-collapse: collapse
		}</style>
		<table id="goods_print_data_html" style="font-size: 12px;" ></table>
	</div>
	<object id="trade_print_cn_obj" classid="clsid:09896DB8-1189-44B5-BADC-D6DB5286AC57" width=0 height=0>
		<embed id="trade_print_cn_em" TYPE="application/x-cainiaoprint" width=0 height=0  ></embed>
	</object>
<script>
//# sourceURL=print.js
var stockWs;
var is_print_setting = false;
var price_configs = ['price','goods_amount'];
$(function(){
	$(function () {
		setTimeout(function () {
			var shopArea = new area("shop_add_province","shop_add_city","shop_add_country");
		}, 0);
	});
	setTimeout(function(){
		var nead_update_fast = ['fast_printed_not_stockout','fast_stockout_not_printed','fast_is_blocked'];
		var fast_div = '#<?php echo ($id_list["fast_div"]); ?>';
		function setFastClickColor(that){
			var a_elems = $(that).parent('fieldset').parent('div').find('a');
			a_elems.each(function(i){
				$(this).find('label[fast_name]').css({'color':'#174B73'});
			});
			$(that).find('label[fast_name]').css({'color':'red'});
		}
		function resetFast(){
			var dg = $('#'+stockSalesPrint.params.datagrid.id);
			var queryParams = dg.datagrid('options').queryParams;
			if(queryParams['search[fast_logis_printed]']){queryParams['search[fast_logis_printed]']='';}
			if(queryParams['search[fast_no_logis_printed]']){queryParams['search[fast_no_logis_printed]']='';}
			if(queryParams['search[fast_goods_printed]']){queryParams['search[fast_goods_printed]']='';}
			if(queryParams['search[fast_no_goods_printed]']){queryParams['search[fast_no_goods_printed]']='';}
			if(queryParams['search[fast_printed_not_stockout]']){queryParams['search[fast_printed_not_stockout]']='';}
			if(queryParams['search[fast_stockout_not_printed]']){queryParams['search[fast_stockout_not_printed]']='';}
			if(queryParams['search[fast_no_stockout]']){queryParams['search[fast_no_stockout]']='';}
			if(queryParams['search[fast_is_stockout]']){queryParams['search[fast_is_stockout]']='';}
			if(queryParams['search[fast_is_finish]']){queryParams['search[fast_is_finish]']='';}
			if(queryParams['search[fast_is_checked]']){queryParams['search[fast_is_checked]']='';}
			if(queryParams['search[fast_no_checked]']){queryParams['search[fast_no_checked]']='';}
			if(queryParams['search[fast_is_weighted]']){queryParams['search[fast_is_weighted]']='';}
			if(queryParams['search[fast_no_weighted]']){queryParams['search[fast_no_weighted]']='';}
			if(queryParams['search[fast_is_blocked]']){queryParams['search[fast_is_blocked]']='';}
			if(queryParams['search[fast_no_blocked]']){queryParams['search[fast_no_blocked]']='';}
			if(queryParams['search[fast_has_cliented]']){queryParams['search[fast_has_cliented]']='';}
			if(queryParams['search[fast_no_cliented]']){queryParams['search[fast_no_cliented]']='';}
			if(queryParams['search[fast_one_day]']){queryParams['search[fast_one_day]']='';}
			if(queryParams['search[fast_tow_day]']){queryParams['search[fast_tow_day]']='';}
			if(queryParams['search[fast_one_week]']){queryParams['search[fast_one_week]']='';}
			if(queryParams['search[fast_one_month]']){queryParams['search[fast_one_month]']='';}
			if(queryParams['search[radio]']){queryParams['search[radio]']='';}
		}
		$('#seach_goods_class').changStyleTreeCombo('seach_goods_class');
		stockSalesPrint = new RichDatagrid(JSON.parse('<?php echo ($params); ?>'));
		stockSalesPrint.print_list = {};
		stockSalesPrint.tmp_map_obj = {
			"include" 		: {
				"datagrid" 					: '#include_goods_list_datagrid',
				"include_search_data" 		: '#include_goods_type_count_hidden',
				"type"						: 'include_goods',
				"tmp_include_relation" 		: '',
				"tmp_include_search_data" 	: '',
			},
			"not_include" 	: {
				"datagrid" 					: '#not_include_goods_list_datagrid',
				"include_search_data" 		: '#not_include_goods_type_count_hidden',
				"type"						: 'not_include_goods',
				"tmp_include_relation" 		: '',
				"tmp_include_search_data" 	: '',
			}
		};
		//$('#'+stockSalesPrint.params.datagrid.id).datagrid('options').rowStyler = function(index,row){

	//		return stockSalesPrint.flagRowStatusByRowStyle(index,row);
		//};
		$('#include_goods_type_count').textbox({onClickButton:function(){
			stockSalesPrint.include_goods_select($(this),'include');
		}});
		$('#include_goods_type_count').textbox({'prompt':'点击右侧按钮',missingMessage:'单击按钮添加货品'});
		$('#include_goods_type_count').textbox('textbox').css({'background-color':'#ddd'});
		$('#not_include_goods_type_count').textbox({onClickButton:function(){
			stockSalesPrint.include_goods_select($(this),'not_include');
		}});
		$('#not_include_goods_type_count').textbox({'prompt':'点击右侧按钮',missingMessage:'单击按钮添加货品'});
		$('#not_include_goods_type_count').textbox('textbox').css({'background-color':'#ddd'});
		//右键
		$('#'+stockSalesPrint.params.datagrid.id).datagrid({
			onRowContextMenu: function (e, rowIndex, rowData) { //右键时触发事件
				//三个参数：e，rowIndex当前点击时所在行的索引，rowData当前行的数据
				e.preventDefault(); //阻止浏览器捕获右键事件
				var rows=$('#'+stockSalesPrint.params.datagrid.id).datagrid('getSelections');
				var chose=false;
				for(var i=0;i<rows.length;i++){
					if($('#'+stockSalesPrint.params.datagrid.id).datagrid('getRowIndex',rows[i])==rowIndex){chose=true;}
				}
				if(chose==false){
					$(this).datagrid("clearSelections"); //取消所有选中项
					$(this).datagrid("selectRow", rowIndex); //根据索引选中该行
				}
				$(this).datagrid('options').that.click(rowIndex,rowData);
				$('#print_menu').menu('show', {
					hideOnUnhover: false,
					left: e.pageX,//在鼠标点击处显示菜单
					top: e.pageY
				});
			}
		});

		stockSalesPrint.setFormData();//重置
		//针对普通搜索关联回车键
		stockSalesPrint.bindSearchForm(stockSalesPrint.params.search.form_main_id,stockSalesPrint);
		$logistics_status = $('#'+stockSalesPrint.params.datagrid.id).datagrid('getColumnOption','logistics_print_status').styler = function(value,row,index){
			return stockSalesPrint.setColor(value);
		};
		$logistics_status = $('#'+stockSalesPrint.params.datagrid.id).datagrid('getColumnOption','sendbill_print_status').styler = function(value,row,index){
			return stockSalesPrint.setColor(value);
		};
		$logistics_status = $('#'+stockSalesPrint.params.datagrid.id).datagrid('getColumnOption','picklist_print_status').styler = function(value,row,index){
			return stockSalesPrint.setColor(value);
		};
		stockSalesPrint.setColor = function(value){
			if(value == 0){
				return 'background-color:#E37162;color:#000;';
			}else if(value == 1){
				return 'background-color:#55B527;color:#000;';
			}else if(value == 3){
				return 'background-color:#f00;color:#000;';
			}
		}
		//新版搜索
		stockSalesPrint.searchData=function(){
			resetFast();
			stockSalesPrint.submitSearchForm(this);
		}
		stockSalesPrint.loadData=function(set_fast){
			resetFast();
			$('input[name="search[multi_logistics]"]').val('0').prop('checked','');
			$("#<?php echo ($id_list["form"]); ?>").form('reset');
			stockSalesPrint.tmp_map_obj.include.tmp_include_search_data = '';
			stockSalesPrint.tmp_map_obj.not_include.tmp_include_search_data = '';
			stockSalesPrint.loadFormData();
            if(set_fast != undefined){
				$('#<?php echo ($id_list["fast_div"]); ?>').find('a').each(function(){
					$(this).find('label[fast_name]').css({'color':'#174B73'});
				});
			}
		}
        stockSalesPrint.loadFormData=function(id,data){
            var dg = $('#'+stockSalesPrint.params.datagrid.id);
            dg.datagrid('options').pageNumber=1;
            $(dg.datagrid('getPager')).pagination("refresh",{pageNumber:1});
            if(this.params.search.form_main_id!=undefined){
                main_id=this.params.search.form_main_id;
                $('#'+main_id).form('reset');
            }
            if(id==undefined){id=this.params.search.form_id;}
            if(data!=undefined){$('#'+id).form('load',data);}
            else if(this.params.search.form_data!=undefined){$('#'+id).form('load',this.params.search.form_data);$('#'+id+' :input[extend_type="complex-check"]').each(function(){$(this).triStateCheckbox('init');});this.submitSearchForm(this);}
            else{$('#'+id).form('reset');}
        }
		stockSalesPrint.loadSuccess = function(){
			stockSalesPrint.updateFastNum(nead_update_fast);
		};
		stockSalesPrint.updateFastNum = function(nead_update_fast){
			Post("<?php echo U('Stock/StockSalesPrint/getFastSearchNum');?>",{list:nead_update_fast},function(r){
				if(r.status == 0){
					$('#<?php echo ($id_list["fast_div"]); ?>'+" a[fast_num_type]").each(function(){
						var type = $(this).attr('fast_num_type');
						$(this).find('label[fast_num]').each(function(){
							$(this).html('('+(r.data[type]==undefined?'?':r.data[type])+')')
						});
					});
				}else{
					$('#<?php echo ($id_list["fast_div"]); ?>'+" a[fast_num_type]").each(function(){
						var type = $(this).attr('fast_num_type');
						$(this).find('label[fast_num]').each(function(){
							$(this).html('(?)')
						});
						messager.alert(r.info);
					});
				}

			},'json')
		}
		stockSalesPrint.fastSearch=function(that,key){
			setFastClickColor(that);
			resetFast();
			var dg = $('#'+stockSalesPrint.params.datagrid.id);
			var queryParams ={};
			queryParams[key]=1;
			dg.datagrid('options').queryParams = queryParams;
			dg.datagrid('reload');
		}
		stockSalesPrint.reset = function(){
			var multiLogistics = $('input[search=multiLogistics]');
			multiLogistics.attr('checked',false);
			multiLogistics.attr('value',0);
			this.loadFormData();
		};
//包含不包含货品选择
stockSalesPrint.include_goods_select = function(obj,type){
	var that = this;
	var buttons=[ {text:'确定',handler:function(){
		var rows = $(stockSalesPrint.tmp_map_obj[type].datagrid).datagrid('getRows');
		var include_relation = salesStockIncludeGoods.getIncludeRelation();
		var rows_len = rows.length;
		if(rows_len>0){
			var include_val = '';
			var search_data = {};
			obj.textbox('setValue','货品种类:'+rows_len);
			//obj.textbox('textbox').css({'background-color':'#ddd'});
			var conditionFieldsMap = {'小于':0,'等于':1,'大于':2};
			for(var i=0; i<rows_len; ++i){
				var row_index = $(stockSalesPrint.tmp_map_obj[type].datagrid).datagrid('getRowIndex',rows[i]);
				$(stockSalesPrint.tmp_map_obj[type].datagrid).datagrid('endEdit',row_index);
				include_val += rows[i]['spec_id']+'-'+conditionFieldsMap[rows[i]['condition']]+'-'+rows[i]['num']+',';
			}
			include_val = include_val.substr(0, include_val.length - 1);
			search_data['include_val'] = include_val;
			search_data['include_relation'] = include_relation;
			$(stockSalesPrint.tmp_map_obj[type].include_search_data).val(JSON.stringify(search_data));
			stockSalesPrint.tmp_map_obj[type].tmp_include_relation = include_relation;
			stockSalesPrint.tmp_map_obj[type].tmp_include_search_data = {'total':rows_len,'rows':rows};
			$('#'+that.params[stockSalesPrint.tmp_map_obj[type].type].id).dialog('close');
		}else{
			obj.textbox('setValue','');
			$(stockSalesPrint.tmp_map_obj[type].include_search_data).val('');
			stockSalesPrint.tmp_map_obj[type].tmp_include_relation = '';
			stockSalesPrint.tmp_map_obj[type].tmp_include_search_data = '';
			//messager.alert('没有选择货品！');
			$('#'+that.params[stockSalesPrint.tmp_map_obj[type].type].id).dialog('close');
		}
	}}, /*{text:'取消',handler:function(){$('#'+that.params.include_goods.id).dialog('close');}} */];
	Dialog.show(that.params[stockSalesPrint.tmp_map_obj[type].type].id,that.params[stockSalesPrint.tmp_map_obj[type].type].title,that.params[stockSalesPrint.tmp_map_obj[type].type].url,that.params[stockSalesPrint.tmp_map_obj[type].type].height,that.params[stockSalesPrint.tmp_map_obj[type].type].width,buttons,null,that.params[stockSalesPrint.tmp_map_obj[type].type].ismax);
	//stockSalesPrint.showDialog(that.params[stockSalesPrint.tmp_map_obj[type].type].id,that.params[stockSalesPrint.tmp_map_obj[type].type].title,that.params[stockSalesPrint.tmp_map_obj[type].type].url,that.params[stockSalesPrint.tmp_map_obj[type].type].height,that.params[stockSalesPrint.tmp_map_obj[type].type].width,buttons,null,that.params[stockSalesPrint.tmp_map_obj[type].type].ismax);
	if(stockSalesPrint.tmp_map_obj[type].tmp_include_search_data!=''){
		var interval = setInterval(function(){
			var rows = $(stockSalesPrint.tmp_map_obj[type].datagrid).length;
			if(rows > 0){
				salesStockIncludeGoods.setIncludeRelation(stockSalesPrint.tmp_map_obj[type].tmp_include_relation);
				$(stockSalesPrint.tmp_map_obj[type].datagrid).datagrid('loadData',stockSalesPrint.tmp_map_obj[type].tmp_include_search_data);
				clearInterval(interval);
			}
		}, 300);
	}
}
//打印批次
stockSalesPrint.printBatch = function(){
	var that = this;
	var buttons=[ {text:'确定',handler:function(){
		var row = $('#'+that.params.print_batch.datagrid).datagrid('getSelected');
		if(row == null || row == undefined){messager.alert('请选择打印批次');return;}
		//先清空表单搜索，避免已存在搜索条件导致按照批次搜索不到订单
		resetFast();
		$('input[name="search[multi_logistics]"]').val('0').prop('checked','');
		$('#'+that.params.search.form_main_id).form('reset');
		$('#'+that.params.search.form_id).form('reset');
		$('#'+that.params.search.form_id).form('load',that.params.search.form_data);
		$('#'+that.params.search.form_id+' :input[extend_type="complex-check"]').each(
			function(){$(this).triStateCheckbox('init');}
		);
		//搜索批次订单
		$('#print_batch_search').val(row.queue);
		$('#'+that.params.print_batch.id).dialog('close');
		$('#'+that.params.datagrid.id).datagrid('options').sortName=null;
		that.submitSearchForm(this);
		$('#print_batch_search').val('');
	}}, {text:'取消',handler:function(){$('#'+that.params.print_batch.id).dialog('close');}} ];
	Dialog.show(that.params.print_batch.id,that.params.print_batch.title,that.params.print_batch.url,that.params.print_batch.height,that.params.print_batch.width,buttons,null,that.params.print_batch.ismax);
}
//预物流同步
stockSalesPrint.synchronousLogistics = function(){
    $('#print_menu').menu('hide');
	var that = this;
	var rows = this.getSelectRows();
	var selects_info = {};
	var resultBeforeCheck = [];
	if($.isEmptyObject(rows)){
		messager.alert('请选择操作的行！');
		return ;
	}
	for(var k in rows){
		var temp_result = {'stock_id':rows[k]['id'],'stock_no':rows[k]['stockout_no']};
		if(rows[k].status != 55){
			temp_result['msg'] = '只能预同步已审核的订单';
			resultBeforeCheck.push(temp_result);
			continue;
		}
		if( parseInt(rows[k].consign_status) & 8){
			temp_result['msg'] = '已经预物流同步了';
			resultBeforeCheck.push(temp_result);
			continue;
		}
		
		if(rows[k].status > 55){
			temp_result['msg'] = '只能预同步未发货的订单';
			resultBeforeCheck.push(temp_result);
			continue;
		}
		if(rows[k].platform_id == 0){
			temp_result['msg'] = '线下订单无需物流同步';
			resultBeforeCheck.push(temp_result);
			continue;
		}
		
		var temp_index = $('#'+this.params.datagrid.id).datagrid('getRowIndex',rows[k]);
		selects_info[temp_index] = rows[k].id;
	}
	if(!$.isEmptyObject(resultBeforeCheck)){
		$.fn.richDialog("response", resultBeforeCheck, "stockout",{close:function(){if(stockSalesPrint){stockSalesPrint.refresh();}}});
		return;
	}
	Post('<?php echo U("StockSalesPrint/synchronousLogistics");?>',{ids:JSON.stringify(selects_info)},function(r){
		if(r.status != 2){
			messager.alert(r.info);
			that.refresh();
		}else{
			var sel_rows_map = utilTool.array2dict(rows,'id','');
			for(var i in r.data){
				if(r.data[i].msg == '物流单号不能为空'){
					stockSalesPrint.addWaybillType = 'synchronousLogistics';
					r.data[i]['solve_way'] = '物流公司：'+sel_rows_map[r.data[i]['stock_id']]['logistics_name']+'，<a href="javascript:void(0)" onClick="addWaybill(undefined,'+sel_rows_map[r.data[i]['stock_id']].logistics_id+')">填写物流单号</a>';
				}else if(r.data[i].msg.search('拦截出库')!=-1){
					if(sel_rows_map[r.data[i]['stock_id']].block_reason & (1|2|4|32|128|256)){
						r.data[i]['solve_way'] = '<a href="javascript:void(0)" onClick="stockSalesPrint.revertCheck({orignal_type:\'consign_stockout\',solve_type:\'revert_check\'},'+r.data[i]['stock_id']+')">驳回到订单审核重新审核</a>';
					}else{
						if(sel_rows_map[r.data[i]['stock_id']].block_reason & 4096){
							r.data[i]['solve_way'] = '<a href="javascript:void(0)" onClick="stockSalesPrint.cancelBlock({orignal_type:\'consign_stockout\',solve_type:\'revert_check\'},'+r.data[i]['stock_id']+')">取消拦截</a>';
						}else{
							r.data[i]['solve_way'] = '<a href="javascript:void(0)" onClick="stockSalesPrint.revertCheck({orignal_type:\'consign_stockout\',solve_type:\'revert_check\'},'+r.data[i]['stock_id']+')">驳回到订单审核重新审核</a>';
							r.data[i]['solve_way'] += ' 或 ';
							r.data[i]['solve_way'] += '<a href="javascript:void(0)" onClick="stockSalesPrint.cancelBlock({orignal_type:\'consign_stockout\',solve_type:\'revert_check\'},'+r.data[i]['stock_id']+')">取消拦截</a>';
						}
					}
				}else if(r.data[i].msg == '物流同步前必须验货'){
					r.data[i]['solve_way'] = '<a href="javascript:void(0)" onClick="stockSalesPrint.open(\'出库验货\', \'<?php echo U('Stock/SalesStockoutExamine/initStockoutExamine');?>?src_order_no='+sel_rows_map[r.data[i]['stock_id']]['src_order_no']+'\',\''+sel_rows_map[r.data[i]['stock_id']]['src_order_no']+'\')">出库验货</a>';
				}else if(r.data[i].msg == '物流同步前必须称重'){
					r.data[i]['solve_way'] = '<a href="javascript:void(0)" onClick="stockSalesPrint.open(\'称重\', \'<?php echo U('Stock/StockWeight/getWeightList');?>?src_order_no='+sel_rows_map[r.data[i]['stock_id']]['src_order_no']+'\',\''+sel_rows_map[r.data[i]['stock_id']]['src_order_no']+'\')">称重</a>';
				}else if(r.data[i].msg == '在当前配置(物流同步时间):"全部子订单发货才可发货"下，不能对拆分单进行预同步'){
					r.data[i]['solve_way'] = '《设置》->《系统设置》->《基本设置》->"物流同步时间"设置为：只发一个子订单即可发货';
				}
			}
			$.fn.richDialog("response", r.data, "stockout");
		}
	});
};
// 根据物流单号搜索订单
stockSalesPrint.searchLogisticsNos=function(){
	var buttons=[ {text:'确定',handler:function(){stockSalesPrint.submitSearchLogisticsNosDialog();}}, {text:'取消',handler:function(){stockSalesPrint.cancelDialog();}} ];
	stockSalesPrint.showDialog(0,'根据物流单号搜索订单',"<?php echo U('StockSalesPrint/searchLogisticsNos');?>",460,650,buttons);
}
stockSalesPrint.uploadDialog = function () {
	var dialog = $("#<?php echo ($id_list["fileDialog"]); ?>");
	dialog.dialog({
		title: "导入物流单号",
		width: "350px",
		height: "160px",
		modal: true,
		closed: false,
		inline: true,
		iconCls: 'icon-save',
	});
}
stockSalesPrint.upload = function(){
	var form = $("#<?php echo ($id_list["fileForm"]); ?>");
	var url = "<?php echo U('StockSalesPrint/importLogisticsNos');?>";
	//var dg = $("#<?php echo ($id_list["datagrid"]); ?>");
	var dialog = $("#<?php echo ($id_list["fileDialog"]); ?>");
	var separator=$("#search_logistics_nos_separator").combobox('getValue');
	$.messager.progress({
		title: "请稍后",
		msg: "该操作可能需要几分钟，请稍等...",
		text: "",
		interval: 100
	});
	form.form("submit", {
		url: url,
		success: function (res) {
			$.messager.progress('close');
			res = JSON.parse(res);
			if (!res.status) {
				var data=res.info;
				switch(separator){
					case '0':
						data=data.replace(/,/g,' \n ');
						break;
					case '1':
						data=data.replace(/,/g,' ');
						break;
					case '2':
						data=data.replace(/,/g,',');
						break;
					case '3':
						data=data.replace(/,/g,';');
						break;
				}
				$("#passel_logistics_nos").val(data);
				$("#search_logistics_nos").textbox('setValue',data);
				dialog.dialog("close");
			} else if (res.status == 1) {
				messager.alert(res.info);
			}
			form.form("load", {"file": ""});
		}
	})
}
stockSalesPrint.message = function(type,solve_id){
var that = this;
Post('<?php echo U("Stock/StockSalesPrint/getMessageRight");?>','',function(r){
		if(r.status == 1){
			Dialog.show(that.params.message.id,that.params.message.title,that.params.message.url,that.params.message.height,that.params.message.width,[]);
		}else{ 
			stockSalesPrint.consignStockoutOrder(type,solve_id);
		}
	});
}				
//确认发货
stockSalesPrint.consignStockoutOrder = function(type,solve_id)
{
	var that = this;
	var selected_rows = this.getSelectRows();
	var selects_info ={};
	var resultBeforeCheck = [];
	if(type != undefined )
	{
		var row_info = that.getSolveRow(solve_id);
		if(row_info == undefined){
			messager.alert('该订单信息已经更新,请关闭后重新操作');
			$('#response_dialog').dialog('close');
			return;
		}else{
			selected_rows.push(row_info.row);
			selects_info[row_info.index] = solve_id;
		}
	}
	if($.isEmptyObject(selected_rows)){
		messager.alert('请选择操作的行');
		return;
	}
	if(type ==undefined){
		for(var item in selected_rows){
			var temp_result = {'stock_id':selected_rows[item]['id'],'stock_no':selected_rows[item]['stockout_no']};
			if(selected_rows[item]['src_order_type'] != 1){
				temp_result['msg'] = "不是销售出库单";
				resultBeforeCheck.push(temp_result);
				continue;
			}
			if(selected_rows[item]['status']<55){
				temp_result['msg'] = "出库单状态不正确";
				resultBeforeCheck.push(temp_result);
				continue;
			}
			if(selected_rows[item]['status']>=95){
				temp_result['msg'] = "订单已发货";
				resultBeforeCheck.push(temp_result);
				continue;
			}
			if(selected_rows[item]['consign_status']&4){
				temp_result['msg'] = "订单已出库";
				resultBeforeCheck.push(temp_result);
				continue;
			}
			if(selected_rows[item]['warehouse_type']!=1 && selected_rows[item]['warehouse_type']!=127){
				temp_result['msg'] = "委外订单不能验货出库";
				resultBeforeCheck.push(temp_result);
				continue;
			}
			if(parseInt(selected_rows[item]['platform_id']) !=0){
				if($.isEmptyObject(selected_rows[item]['logistics_id']) || selected_rows[item]['logistics_id']==0){
					temp_result['msg'] = "物流公司未设置";
					temp_result['solve_way'] = '【修改物流公司】';
					resultBeforeCheck.push(temp_result);
					continue;
				}
				if(selected_rows[item]['logistics_type'] > 1 &&  $.trim(selected_rows[item]['logistics_no']) == ''){
					temp_result['msg'] = "物流单号不能为空";
					temp_result['solve_way'] = '物流公司：'+selected_rows[item]['logistics_name']+'，<a href="javascript:void(0)" onClick="addWaybill(undefined,'+selected_rows[item]['logistics_id']+')">填写物流单号</a>';
					resultBeforeCheck.push(temp_result);
					continue;
				}
			}
			if(parseInt(selected_rows[item]['consign_status'])&8 && !( (parseInt(selected_rows[item]['consign_status']) & 128) || (parseInt(selected_rows[item]['consign_status']) & 1024))){
				temp_result['msg'] = "预物流同步成功后才可确认发货";
				temp_result['solve_way'] = '<a href="javascript:void(0)"  name = "button_save" class="easyui-linkbutton" data-options="iconCls:\'icon-save\'" onclick="stockSalesPrint.mandatory_delivery('+selected_rows[item]['id']+','+type+')">强制发货</a>' + ' 或 ' + '<a href="javascript:void(0)"  name = "button_save" class="easyui-linkbutton" data-options="iconCls:\'icon-save\'" onclick="stockSalesPrint.consignAllStockoutOrder(1,\'预物流同步成功后才可确认发货\')">全部强制发货</a> 或 等待预物流同步成功';
				resultBeforeCheck.push(temp_result);
				continue;
			}
			var temp_index = $('#'+this.params.datagrid.id).datagrid('getRowIndex',selected_rows[item]);
			selects_info[temp_index] = selected_rows[item].id;
		}
	}
	if($.isEmptyObject(selects_info)){
		$.fn.richDialog("response", resultBeforeCheck, "stockout");
		return;
	}
	messager.confirm('确定完成发货吗？', function(r){
		if(r){
			$('#'+that.params.datagrid.id).datagrid('loading');
			Post("<?php echo U('StockSalesPrint/consignStockoutOrder');?>", {ids:JSON.stringify(selects_info),is_force:0}, function(result){
				$('#'+that.params.datagrid.id).datagrid('loaded');
				if(!$.isEmptyObject(resultBeforeCheck) && (result.status == 0 || result.status == 2)){
					result.status = 2;
					result.data.fail = resultBeforeCheck.concat(result.data.fail);
				}
				if(parseInt(result.status)==0) {
					for(var i in result.data.success){
						if($.isEmptyObject(result.data.success[i])){
							continue;
						}
						$('#'+that.params.datagrid.id).datagrid('updateRow',{index:parseInt(i), row:{status:result.data.success[i].status,consign_status:result.data.success[i].consign_status,weight:result.data.success[i].weight,consign_time:result.data.success[i].consign_time,post_cost:result.data.success[i].post_cost}});
						that.updateResponSuccess(result.data.success,type);
					}
					return true;
				}
				if(parseInt(result.status) == 1){
					messager.alert(result.info);
					that.refresh();
					return false;
				}
				if(parseInt(result.status) == 2){
					if(!$.isEmptyObject(result.data.fail)){
						//调用dialog显示处理结果
						if(type == undefined){
							var sel_rows_map = utilTool.array2dict(selected_rows,'id','');
							for(var k in result.data.fail){
								if(result.data.fail[k].msg == '物流单号不能为空'){
									result.data.fail[k]['solve_way'] = '物流公司：'+sel_rows_map[result.data.fail[k]['stock_id']]['logistics_name']+'，<a href="javascript:void(0)" onClick="addWaybill(undefined,'+sel_rows_map[result.data.fail[k]['stock_id']].logistics_id+')">填写物流单号</a>';
								}else if(result.data.fail[k].msg.search('拦截出库')!=-1){
									if(sel_rows_map[result.data.fail[k]['stock_id']].block_reason & (1|2|4|32|128|256)){
										result.data.fail[k]['solve_way'] = '<a href="javascript:void(0)" onClick="stockSalesPrint.revertCheck({orignal_type:\'consign_stockout\',solve_type:\'revert_check\'},'+result.data.fail[k]['stock_id']+')">驳回到订单审核重新审核</a>';
										result.data.fail[k]['solve_way'] +=' 或 <a href="javascript:void(0)"  name = "button_save" class="easyui-linkbutton" data-options="iconCls:\'icon-save\'" onclick="stockSalesPrint.mandatory_delivery('+result.data.fail[k]['stock_id']+','+type+')">强制发货</a>';
									}else{
										if(sel_rows_map[result.data.fail[k]['stock_id']].block_reason & 4096){
											if('<?php echo ($online_consign_block); ?>' != 1 || ('<?php echo ($online_consign_block); ?>' == 1 && '<?php echo ($prevent_online_consign_block_stockou); ?>'==0)){
												result.data.fail[k]['solve_way'] = '<a href="javascript:void(0)" onClick="stockSalesPrint.cancelBlock({orignal_type:\'consign_stockout\',solve_type:\'cancel_block\'},'+result.data.fail[k]['stock_id']+')">取消拦截</a>';
												result.data.fail[k]['solve_way'] +=' 或 <a href="javascript:void(0)"  name = "button_save" class="easyui-linkbutton" data-options="iconCls:\'icon-save\'" onclick="stockSalesPrint.mandatory_delivery('+result.data.fail[k]['stock_id']+','+type+')">强制发货</a>';
											}
										}else{
											result.data.fail[k]['solve_way'] = '<a href="javascript:void(0)" onClick="stockSalesPrint.revertCheck({orignal_type:\'consign_stockout\',solve_type:\'revert_check\'},'+result.data.fail[k]['stock_id']+')">驳回到订单审核重新审核</a>';
											result.data.fail[k]['solve_way'] += ' 或 ';
											result.data.fail[k]['solve_way'] += '<a href="javascript:void(0)" onClick="stockSalesPrint.cancelBlock({orignal_type:\'consign_stockout\',solve_type:\'cancel_block\'},'+result.data.fail[k]['stock_id']+')">取消拦截</a>';
											result.data.fail[k]['solve_way'] +=' 或 <a href="javascript:void(0)"  name = "button_save" class="easyui-linkbutton" data-options="iconCls:\'icon-save\'" onclick="stockSalesPrint.mandatory_delivery('+result.data.fail[k]['stock_id']+','+type+')">强制发货</a>';
										}
									}
								}else if(result.data.fail[k].msg == '发货前必须验货' || result.data.fail[k].msg == '发货前必须称重'){
									result.data.fail[k]['solve_way'] ='<a href="javascript:void(0)"  name = "button_save" class="easyui-linkbutton" data-options="iconCls:\'icon-save\'" onclick="stockSalesPrint.mandatory_delivery('+result.data.fail[k]['stock_id']+','+type+')">强制发货</a>';
								}else if(result.data.fail[k].msg == '预物流同步成功后才可确认发货'){
									result.data.fail[k]['solve_way'] ='<a href="javascript:void(0)"  name = "button_save" class="easyui-linkbutton" data-options="iconCls:\'icon-save\'" onclick="stockSalesPrint.mandatory_delivery('+result.data.fail[k]['stock_id']+','+type+')">强制发货</a>' + ' 或 ' + '<a href="javascript:void(0)"  name = "button_save" class="easyui-linkbutton" data-options="iconCls:\'icon-save\'" onclick="stockSalesPrint.consignAllStockoutOrder(1,\'预物流同步成功后才可确认发货\')">全部强制发货</a> 或 等待预物流同步成功';
								}else if(result.data.fail[k].msg.search(/不允许负库存出库/i) != -1){
									result.data.fail[k]['solve_way'] ='<a href="javascript:void(0)"  name = "button_save" class="easyui-linkbutton" data-options="iconCls:\'icon-save\'" onclick="stockSalesPrint.mandatory_delivery('+result.data.fail[k]['stock_id']+','+type+')">强制发货</a>';
								}else if(result.data.fail[k].msg.search(/存在未分拣的货品/i) != -1){
									result.data.fail[k]['solve_way'] ='<a href="javascript:void(0)"  name = "button_save" class="easyui-linkbutton" data-options="iconCls:\'icon-save\'" onclick="stockSalesPrint.continueSort(0)">继续分拣</a>' + ' 或 ' + '<a href="javascript:void(0)"  name = "button_save" class="easyui-linkbutton" data-options="iconCls:\'icon-save\'" onclick="stockSalesPrint.mandatory_delivery('+result.data.fail[k]['stock_id']+','+type+')">强制发货</a>' + ' 或 ' + '<a href="javascript:void(0)"  name = "button_save" class="easyui-linkbutton" data-options="iconCls:\'icon-save\'" onclick="stockSalesPrint.consignAllStockoutOrder(1,\'存在未分拣的货品\')">全部强制发货</a>';
								}else if(result.data.fail[k].msg.search(/存在未分拣的爆款货品/i) != -1){
                                    result.data.fail[k]['solve_way'] ='<a href="javascript:void(0)"  name = "button_save" class="easyui-linkbutton" data-options="iconCls:\'icon-save\'" onclick="stockSalesPrint.continueSort(1)">继续分拣</a>' + ' 或 ' + '<a href="javascript:void(0)"  name = "button_save" class="easyui-linkbutton" data-options="iconCls:\'icon-save\'" onclick="stockSalesPrint.mandatory_delivery('+result.data.fail[k]['stock_id']+','+type+')">强制发货</a>' + ' 或 ' + '<a href="javascript:void(0)"  name = "button_save" class="easyui-linkbutton" data-options="iconCls:\'icon-save\'" onclick="stockSalesPrint.consignAllStockoutOrder(1,\'存在未分拣的货品\')">全部强制发货</a>';
                                }else if(result.data.fail[k].msg == '出库单对应的子订单已发货'){
									result.data.fail[k]['solve_way'] ='<a href="javascript:void(0)"  name = "button_save" class="easyui-linkbutton" data-options="iconCls:\'icon-save\'" onclick="stockSalesPrint.mandatory_delivery('+result.data.fail[k]['stock_id']+','+type+')">强制发货</a>';
								}
							}
						}
						$.fn.richDialog("response", result.data.fail, "stockout");
					}
					for(var i in result.data.success){
						if($.isEmptyObject(result.data.success[i])){continue;}
						$('#'+that.params.datagrid.id).datagrid('updateRow',{index:parseInt(i), row:{status:result.data.success[i].status,consign_status:result.data.success[i].consign_status,weight:result.data.success[i].weight,consign_time:result.data.success[i].consign_time,post_cost:result.data.success[i].post_cost}});
						that.updateResponSuccess(result.data.success,type);

					}
					return true;
				}
				return;
			},'json');
		}else{return;}
	});

};
stockSalesPrint.consignAllStockoutOrder = function (is_force,search_str) {
	var ids = '';
	var that = this;
	var resultBeforeCheck = [];
	is_force = is_force==undefined?'1':is_force;
	search_str = search_str==undefined?'存在未分拣的货品':search_str;
	var return_rows= $("#response_dialog_datagrid").datagrid('getRows');
	var reg = new RegExp(search_str,"i");
	for(var i in return_rows){
		if(return_rows[i]['msg'].search(reg) != -1){
			ids += return_rows[i]['stock_id']+',';
		}
	}
	var ids_len = ids.length;
	ids = ids.substr(0,ids_len-1);
	if(ids==''){messager.alert('没有获取到出库单信息，请刷新后重试');return;}
	messager.confirm('订单信息可能有误，确定要全部强制出库吗？', function(r){
		if(r){
			$('#'+that.params.datagrid.id).datagrid('loading');
			Post("<?php echo U('Purchase/SortingWall/consignStockoutOrder');?>", {ids:ids,is_force:is_force}, function(result){
				$('#'+that.params.datagrid.id).datagrid('loaded');
				if(!$.isEmptyObject(resultBeforeCheck) && (result.status == 0 || result.status == 2)){
					result.status = 2;
					result.data.fail = resultBeforeCheck.concat(result.data.fail);
				}
				if(parseInt(result.status)==0) {
					var return_rows= $("#response_dialog_datagrid").datagrid('getRows');
					for (var i=0;i<return_rows.length;i++){
						if(return_rows[i]['msg'].search(reg) != -1){
							index=$("#response_dialog_datagrid").datagrid('getRowIndex',return_rows[i]);
							$("#response_dialog_datagrid").datagrid('deleteRow',index);
							i--;
						}
					}
					if(return_rows.length==undefined||return_rows.length==0){
						$("#response_dialog").dialog('close');
					}
					that.refresh();
					return true;
				}
				if(parseInt(result.status) == 1){
					messager.alert(result.info);
					that.refresh();
					return false;
				}
				if(parseInt(result.status) == 2){
					if(!$.isEmptyObject(result.data.fail)){
						//调用dialog显示处理结果
						$.fn.richDialog("response", result.data.fail, "stockout",{close:function(){if(stockSalesPrint){stockSalesPrint.refresh();}}});
					}
					return true;
				}
				return;
			},'json');
		}else{return;}
	});
}
stockSalesPrint.continueSort = function(isHot){
    if(isHot==1){
        open_menu('爆款采购分拣', '<?php echo U('Stock/HotGoods/show');?>');
    }else{
        open_menu('档口采购分拣', '<?php echo U('Stock/StallsPickList/show');?>');
    }
	$("#response_dialog").dialog('close');
}
stockSalesPrint.jump_url = function(title,url,value){
	var param = {};
	$('#response_dialog').dialog('close');
	param['stockout_no'] = value;
	open_menu(title, url);
	switch(title){
		case '分拣框货品明细' :
			if($('#container').tabs('exists',title)){
				$.get("<?php echo U('Purchase/SortingWall/getSortingBoxGoodsDetail');?>",param,function(res){
					$('#sortingwall_datagrid_box').datagrid('loadData',res);
				});
			}
			break;
	}
}
stockSalesPrint.cancel = function () {
        	var that = this;
			var selected_rows = this.getSelectRows();
			var selects_info ={};
			var resultBeforeCheck = [];
            if($.isEmptyObject(selected_rows)){
				messager.alert('请选择操作的行');
				return;
			}
           for(var item in selected_rows){
            var temp_result = {'stock_id':selected_rows[item]['id'],'stock_no':selected_rows[item]['stockout_no']};
            if(selected_rows[item]['src_order_type'] != 1){
                temp_result['msg'] = "不是销售出库单";
                resultBeforeCheck.push(temp_result);
                continue;
            }
            if(selected_rows[item]['status']!=57 && selected_rows[item]['status']!=60){
                temp_result['msg'] = "出库单状态不正确";
                resultBeforeCheck.push(temp_result);
                continue;
            }
			if(selected_rows[item]['warehouse_type']!=11){
                temp_result['msg'] = "出库单仓库不正确";
                resultBeforeCheck.push(temp_result);
                continue;
            }
            var temp_index = $('#'+this.params.datagrid.id).datagrid('getRowIndex',selected_rows[item]);
            selects_info[temp_index] = selected_rows[item].id;
        }
		if($.isEmptyObject(selects_info)){
			$.fn.richDialog("response", resultBeforeCheck, "stockout",{close:function(){if(stockSalesout){stockSalesout.refresh();}}});
			return;
		}
		 messager.confirm('确定取消该订单吗？', function(r){
        if(r){
        	$('#'+that.params.datagrid.id).datagrid('loading');
            Post("<?php echo U('SalesStockOut/cancel');?>", {ids:JSON.stringify(selects_info)}, function(result){
               for(var k in result){
					if(k == 'updated'){messager.alert('取消成功'); that.refresh();return true;break;}
                    else if(k == 'error'){messager.alert(result[k]);that.refresh();return false;break;}
                   else{
                        var Check =  result[0];
						$.fn.richDialog("response", Check, "stockout",'');
						that.refresh();return false;
                        break;
                  }
                }
        },'json');
      }else{
	  return;}
    });
};

	
	stockSalesPrint.hand = function () {
        	var that = this;
			var selected_rows = this.getSelectRows();
			var selects_info ={};
			var resultBeforeCheck = [];
            if($.isEmptyObject(selected_rows)){
				messager.alert('请选择操作的行');
				return;
			}
           for(var item in selected_rows){
            var temp_result = {'stock_id':selected_rows[item]['id'],'stock_no':selected_rows[item]['stockout_no']};
            if(selected_rows[item]['src_order_type'] != 1){
                temp_result['msg'] = "不是销售出库单";
                resultBeforeCheck.push(temp_result);
                continue;
            }
           if(selected_rows[item]['status']!=56){
                temp_result['msg'] = "不是推送失败的单子";
                resultBeforeCheck.push(temp_result);
                continue;
            }
			if(selected_rows[item]['warehouse_type']!=11){
                temp_result['msg'] = "出库单仓库不正确";
                resultBeforeCheck.push(temp_result);
                continue;
            }
            var temp_index = $('#'+this.params.datagrid.id).datagrid('getRowIndex',selected_rows[item]);
            selects_info[temp_index] = selected_rows[item].id;
        }
		if($.isEmptyObject(selects_info)){
			$.fn.richDialog("response", resultBeforeCheck, "stockout",{close:function(){if(stockSalesout){stockSalesout.refresh();}}});
			return;
		}
		 messager.confirm('确定重新推送该订单吗？', function(r){
        if(r){
        	$('#'+that.params.datagrid.id).datagrid('loading');
            Post("<?php echo U('SalesStockOut/hand');?>", {ids:JSON.stringify(selects_info)}, function(result){
            	if(parseInt(result.status)==0) {
            			 messager.alert('推送成功！');
						 that.refresh();
                         return true;
				}
            	if(parseInt(result.status) == 1){
                    messager.alert(result.info);
					that.refresh();
                    return false;
                }
        },'json');
      }else{return;}
    });
		
    };
	


//强制发货
stockSalesPrint.mandatory_delivery = function(stockout_id,type){
	var that = this;
	var selected_rows = this.getSelectRows();
	var selects_info ={};
	//selects_info[0] = stockout_id;
	for(var item in selected_rows){
		var temp_index = $('#'+this.params.datagrid.id).datagrid('getRowIndex',selected_rows[item]);
		if(selected_rows[item].id == stockout_id) selects_info[temp_index] = selected_rows[item].id;
	}
	messager.confirm('订单信息可能有误，确定强制出库吗？', function(r){
		if(r){
			Post("<?php echo U('StockSalesPrint/consignStockoutOrder');?>", {ids:JSON.stringify(selects_info),is_force:1}, function(result){
					if(parseInt(result.status) == 2){
						if(!$.isEmptyObject(result.data.fail)){
							//调用dialog显示处理结果
							$.fn.richDialog("response", result.data.fail, "stockout",{close:function(){if(stockSalesPrint){stockSalesPrint.refresh();}}});
						}
						return true;
					}
					if(parseInt(result.status) == 1){
						messager.alert(result.info);
						that.refresh();
						return false;
					}
					if(parseInt(result.status) == 0){
						//$('#response_dialog').dialog('close');
						var row_index;
						for(var i in result.data.success){
							if($.isEmptyObject(result.data.success[i])){
								continue;
							}
							$('#'+that.params.datagrid.id).datagrid('updateRow',{index:parseInt(i), row:{status:result.data.success[i].status,consign_status:result.data.success[i].consign_status,weight:result.data.success[i].weight,consign_time:result.data.success[i].consign_time,post_cost:result.data.success[i].post_cost}});
							row_index = parseInt(i);
							that.updateResponSuccess(result.data.success,type);
						}
						var return_rows= $("#response_dialog_datagrid").datagrid('getRows');
						for (var i=0;i<return_rows.length;i++){
							if(return_rows[i]['stock_id']==result.data.success[row_index].id){
								index=$("#response_dialog_datagrid").datagrid('getRowIndex',return_rows[i]);
								$("#response_dialog_datagrid").datagrid('deleteRow',index);
								i--;
							}
						}
						//return_rows= $("#response_dialog_datagrid").datagrid('getRows');
						if(return_rows.length==undefined||return_rows.length==0){
							$("#response_dialog").dialog('close');
						}
						return true;
					}
			});
		}else{
			return ;
		}
	});
	
};
//为物流单打印和发货单打印添加标记
stockSalesPrint.flagRowStatusByRowStyle = function(index,row){
	if(row.sendbill_print_status==0 && row.logistics_print_status == 1){
		return 'background-color:#E37162'; //w物流单打印
	}else if(row.sendbill_print_status==1 && row.logistics_print_status == 0){
		return 'background-color:#DFE31E';//发货单打印
	}else if(row.sendbill_print_status==1 && row.logistics_print_status == 1){
		return 'background-color:#55B527';//全部打印
	}else{
		return this.showFlag(index,row);
	}
};

//html 标签的解码
stockSalesPrint.jsonObjectHtmlDecode = function(params){
	$.each(params,function(key,val){
	    if(typeof val == 'string'){
	    	params[key]=val.html_decode();
	    }
	});
}
//单据打印修改物流
stockSalesPrint.chgLogistics = function(logistics_id)
{
    var that = this;
    var selected_rows = this.getSelectRows();
    if($.isEmptyObject(selected_rows)){
       messager.alert('请选择操作的行');
       return;
    }
    var selects_info ={};
    var resultBeforeCheck = [];
    for(var item in selected_rows){
    	var temp_result = {'stock_id':selected_rows[item]['stockout_id'],'stock_no':selected_rows[item]['stockout_no']};
		if(parseInt(selected_rows[item]['block_reason']) & (1|2|4|32|128|256)){
			var block_reason_id = parseInt(selected_rows[item]['block_reason']);
			var block_reason_name = formatter.stockout_block_reason(block_reason_id);
			temp_result['msg'] = '出库单已经截停:'+block_reason_name;
			resultBeforeCheck.push(temp_result);
			continue;
		}
    	if(selected_rows[item]['status'] != 55){
			if(selected_rows[item]['status'] == 110){
				temp_result['msg'] = "出库单状态不正确";
				temp_result['solve_way'] ='已完成的订单不支持修改物流';
				resultBeforeCheck.push(temp_result);
				continue;
			}else if(selected_rows[item]['status'] == 95){
				temp_result['msg'] = "出库单状态不正确";
				temp_result['solve_way'] ='已发货的订单不支持修改物流，<div></div>如果非要修改请点击<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:\'icon-search\'" onClick="stockSalesPrint.jump()">撤销出库</a>后再修改物流'
				resultBeforeCheck.push(temp_result);
				continue;
			}else{
				temp_result['msg'] = "出库单状态不正确";
				temp_result['solve_way'] ='该出库单状态不支持修改物流';
				resultBeforeCheck.push(temp_result);
				continue;
			}
    	}
		if(selected_rows[item]['logistics_id'] == logistics_id){
			temp_result['msg'] = "物流方式没变";
			resultBeforeCheck.push(temp_result);
			continue;
		}
   	    var temp_index = $('#'+this.params.datagrid.id).datagrid('getRowIndex',selected_rows[item]);
   	 	selects_info[temp_index] = {id:selected_rows[item].id,logistics_id:logistics_id};
    }
    if($.isEmptyObject(selects_info)){
        $.fn.richDialog("response", resultBeforeCheck, "stockout");
        return;
    }
    messager.confirm('确定修改物流吗？', function(r){
        if(r){
        	$('#'+that.params.datagrid.id).datagrid('loading');
            Post("<?php echo U('StockSalesPrint/chgLogistics');?>", {ids:JSON.stringify(selects_info)}, function(result){
            	$('#'+that.params.datagrid.id).datagrid('loaded');
            	if(!$.isEmptyObject(resultBeforeCheck) && (result.status == 0 || result.status == 2)){
            		result.status = 2;
            		result.data.fail = resultBeforeCheck.concat(result.data.fail);
            	}
            	if(parseInt(result.status)==0) {
            			 for(var i in result.data.success){
                         	if($.isEmptyObject(result.data.success[i])){
                                	continue;
                             }
                             $('#'+that.params.datagrid.id).datagrid('updateRow',{index:parseInt(i), row:{logistics_name:result.data.success[i].logistics_name,logistics_no:'',logistics_id:result.data.success[i].logistics_id,bill_type:result.data.success[i].bill_type,logistics_type:result.data.success[i].logistics_type}});
                         }
						if($('#add_package_datagrid').length>0){
							var packages = '',ids = '';
							var package_rows = $('#add_package_datagrid').datagrid('getRows');
							var rows = $('#' + stockSalesPrint.params.datagrid.id).datagrid('getSelections');
							for(var i=0; i<package_rows.length; ++i){
								ids += package_rows[i]['id'] + ',';
								packages += '1,';
							}
							ids = ids.substr(0,ids.length-1);
							packages = packages.substr(0,packages.length-1);
							$.post("/index.php/Stock/StockSalesPrint/updatePackageCount", {stockout_ids:ids, packages:packages}, function(r){
								if(r.status == 0){
									var packageCounts = r.data[0].split(',');
									for (var k = 0; k < rows.length; ++k) {
										index = $('#' + stockSalesPrint.params.datagrid.id).datagrid('getRowIndex', rows[k]);
										$('#'+stockSalesPrint.params.datagrid.id).datagrid('updateRow',{index:index,row:{package_count:packageCounts[k]}});
									}
									for(var j in package_rows){
										if(parseInt(package_rows[j].bill_type) != 0){
											$('#add_package_datagrid').datagrid('updateRow',{index:parseInt(j),row:{package_count:1}});
										}
									}
								}else{
									messager.alert(r.msg);
								}
							});
						}
						if(typeof(sspMultiLogistics) != 'undefined'){
							 sspMultiLogistics.refresh($('#'+sspMultiLogistics.params.datagrid.id));
						 }
                         return true;
            		}
            	if(parseInt(result.status) == 1){
                    messager.alert(result.info);
                    return false;
                }
                if(parseInt(result.status) == 2){
                    if(!$.isEmptyObject(result.data.fail)){
                            //调用dialog显示处理结果
                        $.fn.richDialog("response", result.data.fail, "stockout");
                    }
                    for(var i in result.data.success){
                        if($.isEmptyObject(result.data.success[i])){continue;}
                        $('#'+that.params.datagrid.id).datagrid('updateRow',{index:parseInt(i), row:{logistics_name:result.data.success[i].logistics_name,logistics_no:'',logistics_id:result.data.success[i].logistics_id,bill_type:result.data.success[i].bill_type,logistics_type:result.data.success[i].logistics_type}});
                    }
                    return true;
                }
            	return;
        },'json');
      }else{return;}
    });
};
stockSalesPrint.jump = function(){
	$('#response_dialog').dialog('close');
	stockSalesPrint.revertStockout();
};
//修改打印状态
stockSalesPrint.chgPrintStatus = function()
{
    var that = this;
    var selected_rows = this.getSelectRows();
    if($.isEmptyObject(selected_rows)){
       messager.alert('请选择操作的行');
       return;
    }
    var selects_info ={};
	var selects_map = {};
    for(var item in selected_rows){
   	    var temp_index = $('#'+this.params.datagrid.id).datagrid('getRowIndex',selected_rows[item]);
   	 	selects_info[temp_index] = {id:selected_rows[item].id};
		selects_map["'"+selected_rows[item].id+"'"] = {index:temp_index};
    }
	$('#'+that.params.chg_print_status.id).dialog({
		title:that.params.chg_print_status.title,
		iconCls:'icon-save',
		width:300,
		height:200,
		closed:false,
		inline:true,
		modal:true,
		href:that.params.chg_print_status.url,
		buttons:[ {text:'确定',handler:function(){
			var end_edit_row = $('#'+that.params.chg_print_status.datagrid_id).datagrid('getSelected');
			var end_edit_index = $('#'+that.params.chg_print_status.datagrid_id).datagrid('getRowIndex',end_edit_row);
			$('#'+that.params.chg_print_status.datagrid_id).datagrid('endEdit',end_edit_index);
			var chg_status_info = $('#'+that.params.chg_print_status.datagrid_id).datagrid('getChanges');
			$('#'+that.params.chg_print_status.id).dialog('close');
			if($.isEmptyObject(chg_status_info)){
				return;
			}
//			$('#'+that.params.datagrid.id).datagrid('loading');
			Post("<?php echo U('StockSalesPrint/chgPrintStatus');?>", {ids:JSON.stringify(selects_info),status_info:chg_status_info}, function(result){
//				$('#'+that.params.datagrid.id).datagrid('loaded');
				if(parseInt(result.status)==0) {
					that.refresh();
					messager.alert('修改成功');
					/*for(var i in result.data.success){
						if($.isEmptyObject(result.data.success[i])){
							continue;
						}
						$('#'+that.params.datagrid.id).datagrid('updateRow',{index:selects_map[result.data.success[i].id].index, row:{sendbill_print_status:result.data.success[i].sendbill_print_status,logistics_print_status:result.data.success[i].logistics_print_status}});
					}
					return true;*/
				}
				if(parseInt(result.status) == 1){
					messager.alert(result.info);
					return false;
				}
				return;
			},'json');
		}}, {text:'取消',handler:function(){that.cancelDialog(that.params.chg_print_status.id)}} ]
	});
};
//取消截停
stockSalesPrint.cancelBlock = function(type,solve_id)
{
	var that = this;
	var selected_rows = this.getSelectRows();
	if($.isEmptyObject(selected_rows)){
		messager.alert('请选择操作的行');
		return;
	}
	var selects_info ={};
	var resultBeforeCheck = [];
	if(type != undefined )
	{
		var row_info = that.getSolveRow(solve_id);
		if(row_info == undefined){
			messager.alert('该订单信息已经更新,请关闭后重新操作');
			$('#response_dialog').dialog('close');
			return;
		}else{
			selected_rows.push(row_info.row);
			selects_info[row_info.index] = solve_id;
		}
	}
	var block_reason_map = formatter.get_data('stockout_block_reason');
	if(type == undefined){
		for(var item in selected_rows){
			var temp_result = {'stock_id':selected_rows[item]['id'],'stock_no':selected_rows[item]['stockout_no']};
			if(parseInt(selected_rows[item]['block_reason'])==0){
				temp_result['msg'] = "出库单未拦截";
				temp_result['solve_way'] ='刷新后重试';
				resultBeforeCheck.push(temp_result);
				continue;
			}
			if(parseInt(selected_rows[item]['block_reason']) & (2|4|32|128|256)){
				var block_reason_id = parseInt(selected_rows[item]['block_reason']);
				var block_reason_name = formatter.stockout_block_reason(block_reason_id);
				temp_result['msg'] = block_reason_name+"不能清除";
				temp_result['solve_way'] = '<a href="javascript:void(0)" onClick="stockSalesPrint.revertCheck({orignal_type:\'cancel_block\',solve_type:\'revert_check\'},'+selected_rows[item].id+')">驳回到审核界面重新审核</a>';
				resultBeforeCheck.push(temp_result);
				continue;
			}
			var temp_index = $('#'+this.params.datagrid.id).datagrid('getRowIndex',selected_rows[item]);
			selects_info[temp_index] = selected_rows[item].id;
		}
	}
	if($.isEmptyObject(selects_info))
	{
		$.fn.richDialog("response", resultBeforeCheck, "stockout",{close:function(){if(stockSalesPrint){stockSalesPrint.refresh();}}});
		return;
	}
	messager.confirm('确认取消拦截吗？', function(r){
		if(r){
			Post("<?php echo U('StockSalesPrint/unblockStockout');?>", {ids:JSON.stringify(selects_info)}, function(result){
				if(!$.isEmptyObject(resultBeforeCheck) && (result.status == 0 || result.status == 2)){
					result.status = 2;
					result.data.fail = resultBeforeCheck.concat(result.data.fail);
				}
				if(parseInt(result.status)==0) {
					for(var i in result.data.success){
						if($.isEmptyObject(result.data.success[i])){
							continue;
						}
						$('#'+that.params.datagrid.id).datagrid('updateRow',{index:parseInt(i), row:{block_reason:result.data.success[i].block_reason}});
						that.updateResponSuccess(result.data.success,type);
					}
					return true;
				}
				if(parseInt(result.status) == 1){
					messager.alert(result.info);
					that.refresh();
					return false;
				}
				if(parseInt(result.status) == 2){
					if(!$.isEmptyObject(result.data.fail)){
						//调用dialog显示处理结果
						if(type == undefined){
							var sel_rows_map = utilTool.array2dict(selected_rows,'id','');
							for(var k in result.data.fail){
								if(result.data.fail[k].msg.search('不能清除')!=-1){
									result.data.fail[k]['solve_way'] = '<a href="javascript:void(0)" onClick="stockSalesPrint.revertStockout('+result.data.fail[k]['stock_id']+')">驳回到审核界面再次审核</a>';
								}
							}
						}
						$.fn.richDialog("response", result.data.fail, "stockout",{close:function(){if(stockSalesPrint){stockSalesPrint.refresh();}}});
					}
					for(var i in result.data.success){
						if($.isEmptyObject(result.data.success[i])){continue;}
						$('#'+that.params.datagrid.id).datagrid('updateRow',{index:parseInt(i), row:{consign_status:result.data.success[i].consign_status}});
						that.updateResponSuccess(result.data.success,type);
					}
					return true;
				}
				return;
			},'json');
		}else{return;}
	});
};

stockSalesPrint.revertStockout = function(type,solve_id,is_force)
{
	var that = this;
	is_force = is_force == undefined ? 1 : is_force;
	var selected_rows = this.getSelectRows();
	var selects_info ={};
	var resultBeforeCheck = [];
	if(type != undefined )
	{
		var row_info = that.getSolveRow(solve_id);
		if(row_info == undefined){
			messager.alert('该订单信息已经更新,请关闭后重新操作');
			$('#response_dialog').dialog('close');
			return;
		}else{
			selected_rows.push(row_info.row);
			selects_info[row_info.index] = solve_id;
		}
	}
	if($.isEmptyObject(selected_rows)){
		messager.alert('请选择操作的行');
		return;
	}
	var block_reason_map = formatter.get_data('stockout_block_reason');
	if(type == undefined){
		for(var item in selected_rows){
			var temp_result = {'stock_id':selected_rows[item]['id'],'stock_no':selected_rows[item]['stockout_no']};
			if(parseInt(selected_rows[item]['status'])<95){
				temp_result['msg'] = "出库单还没有出库";
				temp_result['solve_way'] = "不需要撤销出库";
				resultBeforeCheck.push(temp_result);
				continue;
			}
			var temp_index = $('#'+this.params.datagrid.id).datagrid('getRowIndex',selected_rows[item]);
			selects_info[temp_index] = selected_rows[item].id;
		}
	}
	if($.isEmptyObject(selects_info))
	{
		$.fn.richDialog("response", resultBeforeCheck, "stockout",{close:function(){if(stockSalesPrint){stockSalesPrint.refresh();}}});
		return;
	}
	messager.confirm('是否强制撤销出库吗？', function(r){
		if(r){
			$('#'+that.params.datagrid.id).datagrid('loading');
			Post("<?php echo U('StockSalesPrint/revertStockout');?>", {ids:JSON.stringify(selects_info),is_force:is_force}, function(result){
				$('#'+that.params.datagrid.id).datagrid('loaded');
				if(!$.isEmptyObject(resultBeforeCheck) && (result.status == 0 || result.status == 2)){
					result.status = 2;
					result.data.fail = resultBeforeCheck.concat(result.data.fail);
				}
				if(parseInt(result.status)==0) {
					var row_index;
					for(var i in result.data.success){
						if($.isEmptyObject(result.data.success[i])){
							continue;
						}
						row_index = parseInt(i);
						$('#'+that.params.datagrid.id).datagrid('updateRow',{index:parseInt(i), row:{consign_status:result.data.success[i].consign_status,consign_time:result.data.success[i].consign_time,status:result.data.success[i].status}});
						that.updateResponSuccess(result.data.success,type);
					}
					var return_rows= $("#response_dialog_datagrid").datagrid('getRows');
					for (var i=0;i<return_rows.length;i++){
						if(return_rows[i]['stock_id']==result.data.success[row_index].id){
							index=$("#response_dialog_datagrid").datagrid('getRowIndex',return_rows[i]);
							$("#response_dialog_datagrid").datagrid('deleteRow',index);
							i--;
						}
					}
					if(return_rows.length==undefined||return_rows.length==0){
						$("#response_dialog").dialog('close');
					}
					return true;
				}
				if(parseInt(result.status) == 1){
					messager.alert(result.info);
					that.refresh();
					return false;
				}
				if(parseInt(result.status) == 2){
					if(!$.isEmptyObject(result.data.fail)){
						for(var k in result.data.fail){
							if(result.data.fail[k].msg.search(/订单已签收/i) != -1){
								result.data.fail[k]['solve_way'] ='<a href="javascript:void(0)"  name = "button_save" class="easyui-linkbutton" data-options="iconCls:\'icon-save\'" onclick="stockSalesPrint.revertStockout('+type+','+solve_id+',2'+')">强制撤销出库</a>';
							}
						}
						//调用dialog显示处理结果
						$.fn.richDialog("response", result.data.fail, "stockout",{close:function(){if(stockSalesPrint){stockSalesPrint.refresh();}}});
					}
					for(var i in result.data.success){
						if($.isEmptyObject(result.data.success[i])){continue;}
						$('#'+that.params.datagrid.id).datagrid('updateRow',{index:parseInt(i), row:{consign_status:result.data.success[i].consign_status,consign_time:result.data.success[i].consign_time,status:result.data.success[i].status}});
						that.updateResponSuccess(result.data.success,type);
					}
					return true;
				}
				return;
			},'json');
		}else{return;}
	});
};
stockSalesPrint.open = function(title,url,stock_no){
$('#response_dialog').dialog('close');
if($("#container").tabs('exists', title)){
open_menu(title, url);
switch(title){
	case '出库验货' :
		if($('#container').tabs('exists',title)){
			StockoutExamine.form_selector_list.scan_class.combobox('setValue','trade_no');
			StockoutExamine.form_selector_list.barcode_or_trade_no.textbox('setValue',stock_no);
			StockoutExamine.form_selector_list.barcode_or_trade_no.textbox('textbox').focus();
			stockoutExamine.submitNo();
		}
		break;
	case '称重' :
		if($('#container').tabs('exists',title)){
			stockWeight.form_selector_list.trade_no_or_logistics_no.textbox('setValue',stock_no);
			stockWeight.form_selector_list.trade_no_or_logistics_no.textbox('textbox').focus();
			stockWeight.getTradeInfo();
		}
		break;
	}
}else{
	open_menu(title, url);
}
};

//订单驳回验货
stockSalesPrint.revertConsignStatus = function(type,solve_type,solve_id)
{
	var that = this;
	var selected_rows = this.getSelectRows();
	var selects_info ={};
	var resultBeforeCheck = [];
	if(solve_type != undefined )
	{
		var row_info = that.getSolveRow(solve_id);
		if(row_info == undefined){
			messager.alert('该订单信息已经更新,请关闭后重新操作');
			$('#response_dialog').dialog('close');
			return;
		}else{
			selected_rows.push(row_info.row);
			selects_info[row_info.index] = solve_id;
		}
	}
	if($.isEmptyObject(selected_rows)){
		messager.alert('请选择操作的行');
		return;
	}
	var tip_info_map = {"1":'验货',"2":"称重"};
	if(solve_type == undefined)
	{
		for(var item in selected_rows){
			var temp_result = {'stock_id':selected_rows[item]['id'],'stock_no':selected_rows[item]['stockout_no']};
			if(parseInt(selected_rows[item]['status'])<55){
				temp_result['msg'] = "出库单状态不正确";
				resultBeforeCheck.push(temp_result);
				continue;
			}
			if(parseInt(selected_rows[item]['status'])==95){
				temp_result['msg'] = "请先撤销出库";
				temp_result['solve_way'] = '<a href="javascript:void(0)" onClick="stockSalesPrint.revertStockout({orignal_type:\'revert_consignstatus\',solve_type:\'revert_stockout\',operator_type:'+type+'},'+selected_rows[item]['id']+')">撤销出库</a>';
				resultBeforeCheck.push(temp_result);
				continue;
			}
			if(parseInt(selected_rows[item]['status'])>95 && parseInt(selected_rows[item]['platform_id']) !=0 ){
				temp_result['msg'] = "线上订单已完成，系统禁止驳回"+tip_info_map[type];
				resultBeforeCheck.push(temp_result);
				continue;
			}
			if(parseInt(selected_rows[item]['status'])>95 && parseInt(selected_rows[item]['platform_id']) ==0 ){
				temp_result['msg'] = "请先撤销出库";
				temp_result['solve_way'] = '<a href="javascript:void(0)" onClick="stockSalesPrint.revertStockout({orignal_type:\'revert_consignstatus\',solve_type:\'revert_stockout\',operator_type:'+type+'},'+selected_rows[item]['id']+')">撤销出库</a>';
				resultBeforeCheck.push(temp_result);
				continue;
			}
			if(parseInt(selected_rows[item]['src_order_type'])!=1){
				temp_result['msg'] = "不是销售出库单";
				resultBeforeCheck.push(temp_result);
				continue;
			}
			if(!(parseInt(selected_rows[item]['consign_status'])&1) && parseInt(type)&1){
				temp_result['msg'] = "出库单未验货";
				temp_result['solve_way'] = '<a href="javascript:void(0)" onClick="stockSalesPrint.open(\'出库验货\', \'<?php echo U('Stock/SalesStockoutExamine/initStockoutExamine');?>?src_order_no='+selected_rows[item]['src_order_no']+'\',\''+selected_rows[item]['src_order_no']+'\')">出库验货</a>';
              
				resultBeforeCheck.push(temp_result);
				continue;
			}
			if(!(parseInt(selected_rows[item]['consign_status'])&2) && parseInt(type)&2){
				temp_result['msg'] = "出库单未称重";
			 	temp_result['solve_way'] = '<a href="javascript:void(0)" onClick="stockSalesPrint.open(\'称重\', \'<?php echo U('Stock/StockWeight/getWeightList');?>?src_order_no='+selected_rows[item]['src_order_no']+'\',\''+selected_rows[item]['src_order_no']+'\')">称重</a>';
              
				resultBeforeCheck.push(temp_result);
				continue;
			}
			var temp_index = $('#'+this.params.datagrid.id).datagrid('getRowIndex',selected_rows[item]);
			selects_info[temp_index] = selected_rows[item].id;

		}
	}
	if($.isEmptyObject(selects_info))
	{
		$.fn.richDialog("response", resultBeforeCheck, "stockout",{close:function(){if(stockSalesPrint){stockSalesPrint.refresh();}}});
		return;
	}
	messager.confirm('确定驳回'+tip_info_map[type]+'吗？', function(r){
		if(r){
			Post("<?php echo U('StockSalesPrint/revertConsignStatus');?>", {type:type,ids:JSON.stringify(selects_info)}, function(result){
				if(!$.isEmptyObject(resultBeforeCheck) && (result.status == 0 || result.status == 2)){
					result.status = 2;
					result.data.fail = resultBeforeCheck.concat(result.data.fail);
				}
				if(parseInt(result.status)==0) {
					for(var i in result.data.success){
						if($.isEmptyObject(result.data.success[i])){
							continue;
						}
						$('#'+that.params.datagrid.id).datagrid('updateRow',{index:parseInt(i), row:{status:result.data.success[i].status,consign_status:result.data.success[i].consign_status,weight:result.data.success[i].weight,consign_time:result.data.success[i].consign_time}});
						that.updateResponSuccess(result.data.success,solve_type);
					}
					return true;
				}
				if(parseInt(result.status) == 1){
					messager.alert(result.info);
					that.refresh();
					return false;
				}
				if(parseInt(result.status) == 2){
					if(!$.isEmptyObject(result.data.fail)){
						//调用dialog显示处理结果
						var sel_rows_map = utilTool.array2dict(selected_rows,'id','');
						for(var k in result.data.fail){
							if(result.data.fail[k].msg.search('请先撤销出库')!=-1){
								result.data.fail[k]['solve_way'] = '<a href="javascript:void(0)" onClick="stockSalesPrint.revertStockout({orignal_type:\'revert_consignstatus\',solve_type:\'revert_stockout\',operator_type:'+type+'},'+result.data.fail[k]['stock_id']+')">撤销出库</a>';
							}
						}
						$.fn.richDialog("response", result.data.fail, "stockout",{close:function(){if(stockSalesPrint){stockSalesPrint.refresh();}}});
					}
					for(var i in result.data.success){
						if($.isEmptyObject(result.data.success[i])){continue;}
						$('#'+that.params.datagrid.id).datagrid('updateRow',{index:parseInt(i), row:{consign_status:result.data.success[i].consign_status,weight:result.data.success[i].weight}});
						that.updateResponSuccess(result.data.success,solve_type);
					}
					return true;
				}
				return;
			},'json');
		}else{return;}
	});

};
//驳回审核
stockSalesPrint.revertCheck = function(type,solve_id)
{
	var that = this;
	var selected_rows ;
	var selects_info =[];
	var resultBeforeCheck = [];
	var selected_rows = this.getSelectRows();
	if(type != undefined )
	{
		var row_info = that.getSolveRow(solve_id);
		if(row_info == undefined){
			messager.alert('该订单信息已经更新,请关闭后重新操作');
			$('#response_dialog').dialog('close');
			return;
		}else{
			selected_rows.push(row_info.row);
			selects_info.push(solve_id);
		}
	}
	if($.isEmptyObject(selected_rows)){
		messager.alert('请选择操作的行！');
		return;
	}
	if(type == undefined){
		for(var item in selected_rows){
			var temp_result = {'stock_id':selected_rows[item]['id'],'stock_no':selected_rows[item]['stockout_no']};
			if(selected_rows[item]['status']<55){
				temp_result['msg'] = "订单状态不是已审核，禁止驳回";
				resultBeforeCheck.push(temp_result);
				continue;
			}
			if(parseInt(selected_rows[item]['status'])==95){
				temp_result['msg'] = "请先撤销出库";
				temp_result['solve_way'] = '<a href="javascript:void(0)" onClick="stockSalesPrint.revertStockout({orignal_type:\'revert_check\',solve_type:\'revert_stockout\'},'+selected_rows[item]['id']+')">撤销出库</a>';
				resultBeforeCheck.push(temp_result);
				continue;
			}
			if(parseInt(selected_rows[item]['status'])>95 && parseInt(selected_rows[item]['platform_id']) !=0 ){
				temp_result['msg'] = "线上订单已完成，系统禁止驳回审核";
				resultBeforeCheck.push(temp_result);
				continue;
			}
			if(parseInt(selected_rows[item]['status'])>95 && parseInt(selected_rows[item]['platform_id']) ==0 ){
				temp_result['msg'] = "请先撤销出库";
				temp_result['solve_way'] = '<a href="javascript:void(0)" onClick="stockSalesPrint.revertStockout({orignal_type:\'revert_check\',solve_type:\'revert_stockout\'},'+selected_rows[item]['id']+')">撤销出库</a>';
				resultBeforeCheck.push(temp_result);
				continue;
			}
			if(selected_rows[item]['status']==5){
				temp_result['msg'] = "出库单已经取消";
				resultBeforeCheck.push(temp_result);
				continue;
			}
			if(selected_rows[item]['status']==54){
				temp_result['msg'] = "正在获取面单号,请稍后";
				resultBeforeCheck.push(temp_result);
				continue;
			}
			if(selected_rows[item]['src_order_type']!=1){
				temp_result['msg'] = "不是销售出库单";
				temp_result['solve_way'] ='刷新后重试或者联系管理员';
				resultBeforeCheck.push(temp_result);
				continue;
			}
			selects_info.push(selected_rows[item].id);
		}
	}
	if($.isEmptyObject(selects_info))
	{
		$.fn.richDialog("response", resultBeforeCheck, "stockout",{close:function(){if(stockSalesPrint){stockSalesPrint.refresh();}}});
		return;
	}
	var ids = selects_info.toString();
	this.setReason('revert_reason',ids,resultBeforeCheck,type);
    // 监听驳回审核对话框消失，然后刷新多物流数据
    $("#stocksalesprint_revertreason_id").dialog({
        onClose: function () {
            if(typeof(sspMultiLogistics) != 'undefined'){
                sspMultiLogistics.refresh($('#'+sspMultiLogistics.params.datagrid.id));
            }
        }
    });
};
stockSalesPrint.forceRevertCheck = function(id)
{
	var that = this;
	var resultBeforeCheck = [];
	this.setReason('revert_reason',id,resultBeforeCheck,undefined,1);
    // 监听驳回审核对话框消失，然后刷新多物流数据
    $("#stocksalesprint_revertreason_id").dialog({
        onClose: function () {
            if(typeof(sspMultiLogistics) != 'undefined'){
                sspMultiLogistics.refresh($('#'+sspMultiLogistics.params.datagrid.id));
            }
        }
    });
};
stockSalesPrint.forceRevertAllCheck = function()
{
	var that = this;
	var resultBeforeCheck = [];
	this.setReason('revert_reason','',resultBeforeCheck,undefined,1,1);
};
stockSalesPrint.submitReasonDialog = function(params,type,ids,list,solve_tye,is_force,is_force_all){
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
	if(is_force_all==1){
		var return_rows= $("#response_dialog_datagrid").datagrid('getRows');
		for(var i in return_rows){if(return_rows[i]['msg'].search(/存在未分拣的货品/i) != -1){ids += return_rows[i]['stock_id']+',';}}
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
};

//查看号码
stockSalesPrint.checkNumber=function(){
	var rows=stockSalesPrint.selectRows;
	if(rows==undefined){messager.info('请选择操作的行');return false;}
	var ids=[];
	var list=[];
	for(var i in rows){
		if(rows[i]['receiver_mobile']==''&&rows[i]['receiver_telno']==''){
			list.push({trade_no:rows[i]['stockout_no'],result_info:'手机和固话均为空！'});
			continue;
		}
		ids.push(rows[i]['id']);
	}
	if(ids.length>0){
		Post('<?php echo U('Trade/TradeCommon/checkNumber');?>',{ids:JSON.stringify(ids),key:'stockout_order'},function(res){
			stockSalesPrint.dealDatagridReasonRows(res,list);
		});
	}else{
        var res={};res.status=2;res.info={};res.info.rows=list;res.info.total=list.length;res.check_number=false;
        stockSalesPrint.dealDatagridReasonRows(res,undefined);
    }
};
stockSalesPrint.getSolveRow = function(id)
{
	var sel_rows = $('#'+this.params.datagrid.id).datagrid('getSelections');
	for(var r_j in sel_rows)
	{
		if(sel_rows[r_j].id == id){
			var index = $('#'+this.params.datagrid.id).datagrid('getRowIndex',sel_rows[r_j]);
			return {index:index,row:sel_rows[r_j]};
		}
	}
	return undefined;
};
stockSalesPrint.updateResponSuccess = function(rows,type){
	if(type != undefined){
		var res_rows =  $('#response_dialog_datagrid').datagrid('getSelections');
		for(var i in rows){
			for(var j in res_rows){
				if(rows[i].id == res_rows[j].stock_id){
					if((type.solve_type == 'revert_check') ||((type!=undefined&&(type.orignal_type == type.solve_type))&&( type.solve_type== 'consign_stockout'|| type.solve_type== 'revert_consignstatus'|| type.solve_type== 'revert_check')) ){
						var index = $('#response_dialog_datagrid').datagrid('getRowIndex',res_rows[j]);
						$('#response_dialog_datagrid').datagrid('deleteRow',index);
						if($.isEmptyObject($('#response_dialog_datagrid').datagrid('getRows'))){
							$('#response_dialog').dialog('close');
						};
					}else{

						var index = $('#response_dialog_datagrid').datagrid('getRowIndex',res_rows[j]);
						//{orignal_type:\'revert_consignstatus\',solve_type:\'revert_stockout\'},
						if (type.orignal_type == 'revert_consignstatus' && type.solve_type=='revert_stockout')
						{
							tip_info_map = {"1":'验货',"2":"称重"};
							type.solve_type ='revert_consignstatus';
							rows[i].solve_way = '<a href="javascript:void(0)" onClick="stockSalesPrint.revertConsignStatus('+type.operator_type+','+JSON.stringify(type).replace(/\"/g,'\'')+','+rows[i].id+')">再次驳回'+tip_info_map[type.operator_type]+'</a>';
						}else if (type.orignal_type == 'consign_stockout'){
							type.solve_type ='consign_stockout';
							rows[i].solve_way = '<a href="javascript:void(0)" onClick="stockSalesPrint.consignStockoutOrder('+JSON.stringify(type).replace(/\"/g,'\'')+','+rows[i].id+')">再次确认发货</a>';
						}else if (type.orignal_type == 'revert_check'){
							type.solve_type ='revert_check';
							rows[i].solve_way = '<a href="javascript:void(0)" onClick="stockSalesPrint.revertCheck('+JSON.stringify(type).replace(/\"/g,'\'')+','+rows[i].id+')">再次驳回审核</a>';
						}
						$('#response_dialog_datagrid').datagrid('updateRow',{index:index,row:rows[i]});
					}
				}
			}
		}
	}
};
stockSalesPrint.dealDatagridReasonRows = function(result,list,type){
	var that = this;
	var select_rows = $('#'+that.params.datagrid.id).datagrid('getSelections');
	var index;
	var stockout_dg=$('#'+that.params.datagrid.id);
	var success = result;
	if(result.type != undefined)
	{
		if(!$.isEmptyObject(list) && (result.status == 0 || result.status == 2) ){
			result.status =2;
			result.data.fail = list.concat(result.data.fail);
		}
		if(result.status!=undefined && result.status == 1){
			messager.alert(result.info);
			return;
		}else if(result.status!=undefined && result.status == 0){
			if($.isEmptyObject(result.data) && $.isEmptyObject(result.data.success))
			{
				$.messager.alert(result.info);
				return;
			}
			success = result.data.success;
		}else if(result.status!=undefined && result.status == 2){
			if(!$.isEmptyObject(result.data.fail)){
				//调用dialog显示处理结果
				if(type == undefined){
					var sel_rows_map = utilTool.array2dict(select_rows,'id','');
					for(var k in result.data.fail){
						if(result.data.fail[k].msg == '请先撤销出库'){
							result.data.fail[k]['solve_way'] = '<a href="javascript:void(0)" onClick="stockSalesPrint.revertStockout({orignal_type:\'revert_check\',solve_type:\'revert_stockout\'},'+result.data.fail[k]['stock_id']+')">撤销出库</a>';
						}else if(result.data.fail[k].msg == '驳回原因不存在'){
							result.data.fail[k]['solve_way'] = '重新选择原因，或者'+'<a href="javascript:void(0)" onClick="'+"open_menu('原因列表', '<?php echo U('Setting/CfgOperReason/showReasonList');?>')"+'">原因列表添加原因</a>';
						}
					}
				}
				$.fn.richDialog("response", result.data.fail, that.params[result.type].form.dialog_type,{close:function(){if(stockSalesPrint){stockSalesPrint.refresh();}}});
			}
			if(!$.isEmptyObject(result.data.success)){
				success = result.data.success;
			}
		}
	}
	if(success.status==1){ messager.alert(success.message); return;}
	if(list!=undefined&&list.length>0&&success.check_number){
        var fail= (typeof result.info.rows=='object')?$.makeArray(result.info.rows):result.info.rows;
        result.info.rows=$.merge(list,fail);
        result.info.total+=list.length;
        result.status=2;
    }
    if(result.status==2&&success.check_number!=undefined){
        result.info.title='出库单号';
        $.fn.richDialog("response", result.info, 'checknumber');
    }
	if(success.check_number){
		for(var i in select_rows)
		{
			for(var x in success.data.rows)
			{
				if(select_rows[i].id==success.data.rows[x].id)
				{
					index=stockout_dg.datagrid('getRowIndex',select_rows[i]);
					if(success.check_number){select_rows[i].receiver_mobile=success.data.rows[x].receiver_mobile;select_rows[i].receiver_telno=success.data.rows[x].receiver_telno;stockout_dg.datagrid('refreshRow',index);}
				}
			}

		}
	}else{
		for(var i in select_rows)
		{
			for(var item in success)
			{
				if(select_rows[i].id == success[item].id)
				{
					index = stockout_dg.datagrid('getRowIndex',select_rows[i]);
					stockout_dg.datagrid('deleteRow',index);
				}

			}
		}
		that.updateResponSuccess(success,type)
	}
};
stockSalesPrint.importDialog = function () {
	stockSalesPrint.add_logistics_type = 'import';
	var dialog = $("#<?php echo ($id_list["file_dialog"]); ?>");
	dialog.dialog({
		title: "导入物流单号",
		width: "350px",
		height: "160px",
		modal: true,
		closed: false,
		inline: true,
		iconCls: 'icon-save',
		onClose:function(){
			$("#<?php echo ($id_list["file_form"]); ?>").form("load", {"file": ""});
		}
	});
}
stockSalesPrint.showGoodsPic = function()
{
	var that = this;
	var buttons=[ {text:'确定',handler:function(){ that.submitPrintGoodsPicSetting(that.params.set_field_pic.id); }}, {text:'取消',handler:function(){that.cancelDialog(that.params.set_field_pic.id)}} ];
	Dialog.show(this.params.set_field_pic.id,this.params.set_field_pic.title,this.params.set_field_pic.url,this.params.set_field_pic.height,this.params.set_field_pic.width,buttons,null,false);

}

stockSalesPrint.editWarehouse = function(waybill_warehouse_id){
	var that = this;
	var buttons=[ {text:'确定',handler:function(){ that.submitEditDialog();$('#response_dialog').dialog('close'); }}, {text:'取消',handler:function(){that.cancelDialog('editwarehouse')}} ];
	Dialog.show('editwarehouse','编辑仓库',"<?php echo U('Stock/StockSalesPrint/dialogEditWarehouse');?>?id="+waybill_warehouse_id,250,620,buttons,null,false);
}
stockSalesPrint.import = function (is_force) {
	var form = $("#<?php echo ($id_list["file_form"]); ?>");
	var dialog = $("#<?php echo ($id_list["file_dialog"]); ?>");
	var datagrid = $("#<?php echo ($id_list["datagrid"]); ?>");
	$.messager.progress({
		title: "请稍后",
		msg: "该操作可能需要几分钟，请稍等...",
		text: "",
		interval: 100
	});
	is_force = is_force==undefined?0:is_force;
	form.form("submit", {
		url: "<?php echo U('StockSalesPrint/importLogisticsNo');?>?is_force="+is_force,
		success: function (res) {
			$.messager.progress('close');
			res = JSON.parse(res);
			if (!res.status) {
				datagrid.datagrid("reload");
				dialog.dialog("close");
			} else if (res.status == 1) {
				messager.alert(res.msg);
			}else if (res.status == 2) {
				if(!is_force){
					forceSaveLogisticsNo(res.data);
				}else{
					datagrid.datagrid("reload");
					dialog.dialog("close");
				}
			}
		}
	})
}
stockSalesPrint.isSeachMultiLogistis = function(that){
	var selector = that;
	if(that.val()==1)
		that.val('0');
	else that.val('1');
}
stockSalesPrint.select_radio = function(that){
	var i = 1;
	$('input[name="search[radio]"]').each(function(){
		$(this).val(i);
		i++;
	});
}
stockSalesPrint.checkSubmitSearchForm = function(that){
	if($('input[search=multiLogistics]').val()==1&&$('input[search=logistics_no]').val().length==0)
		messager.alert('请填写要搜索的物流单号');
	else this.submitSearchForm(that);
}
stockSalesPrint.downloadTemplet = function(type){
	var url= "<?php echo U('StockSalesPrint/downloadTemplet');?>?type="+type;
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
}



$('.form-div table td:not(td:has(label))').css('width','135px');

$('.form-div table td:has(label)').css('width','80px');
// $('#print_function').attr('src', '/Public/Js/CaiNiaoPrintFuncs.js');
//var element = document.createElement("script");
//element.src = "/Public/Js/CaiNiaoPrintFuncs.js";
//document.body.appendChild(element);
$.lazyLoadJs("/Public/Js/CaiNiaoPrintFuncs.js");
},0);});

function addWaybill(res_rows,logistics_id)
{
	//记录是否开启错误对话框
	stockSalesPrint.is_open_response = false;
	if(res_rows != undefined ){
        var rows = res_rows;
		stockSalesPrint.add_logistics_id = undefined;
	}else if(logistics_id !=undefined){	
		var rows = [];
		stockSalesPrint.add_logistics_id = logistics_id;
		stockSalesPrint.is_open_response = true;
		var sel_rows = $('#'+stockSalesPrint.params.datagrid.id).datagrid('getSelections');
		for(var l_i in sel_rows){
			if(sel_rows[l_i].logistics_id == logistics_id){
				rows.push(sel_rows[l_i]);
			}
		}
    }else{
		stockSalesPrint.add_logistics_id = undefined;
		var rows = $('#'+stockSalesPrint.params.datagrid.id).datagrid('getSelections');
    }
	if ($.isEmptyObject(rows)){
		messager.alert('请选择订单');
		return;
	}
	var logisticsId = rows[0].logistics_id;
	for (var i = 1; i < rows.length; ++i){
		if (rows[i].logistics_id != logisticsId){
			messager.alert('物流公司不一致,请重新选择');
			return;
		}
	}
//	if(rows[0].bill_type == 2){
	//	messager.alert('菜鸟电子面单打印时会自动获取，不需要填写！');
	//	return;
//	}
	// set the logistics name to dialog
	//var buttons=[ {text:'确定',handler:confirmWaybill}, {text:'取消',handler:cancelWaybill} ];
	stockSalesPrint.showDialog('<?php echo ($id_list["dialog"]); ?>','填写物流单号',"<?php echo U('StockSalesPrint/addWaybill');?>?logisticsId="+logisticsId,400,700,[],null,false);
}

function saveWaybill(datagridId,isForce){
	stockSalesPrint.add_logistics_type = 'add';
	// if e-waybill, generateWaybill and backend will handle the logic
	if (0 == $('#'+datagridId).datagrid('getRows')[0].bill_type){
		$('#'+datagridId).datagrid('acceptChanges');
		var rows = $('#'+datagridId).datagrid('getRows');
		var selections = $('#'+stockSalesPrint.params.datagrid.id).datagrid('getSelections');
		var selections_map = utilTool.array2dict(selections,'id','');
		if(!isForce){
			var rec_repeat = [];
			var repeat_error = [];
			for(var i in rows){
				var pos_i = $.inArray(rows[i].id,rec_repeat);
				if(pos_i != -1){
					continue;
				}
				var is_equal = false;
				for(var j= parseInt(i)+1;j<rows.length;j++){

					var pos_j = $.inArray(rows[j].id,rec_repeat);
					if(pos_j != -1){
						continue;
					}
					if(rows[i].logistics_no == rows[j].logistics_no){
						rec_repeat.push(rows[j].id);
						repeat_error.push({'stock_id':selections_map[rows[j].id].id,'stock_no':selections_map[rows[j].id].src_order_no,'msg':'单号重复'+rows[j].logistics_no});
						is_equal = true;
					}
				}
				if(is_equal ==true){
					rec_repeat.push(rows[i].id);
					repeat_error.push({'stock_id':selections_map[rows[i].id].id,'stock_no':selections_map[rows[i].id].src_order_no,'msg':'单号重复'+rows[i].logistics_no});
				}
			}
			if(!$.isEmptyObject(rec_repeat)){
				forceSaveLogisticsNo(repeat_error);
				return;
			}
		}
		var data = [];
		var log = [];
		var operatorId = <?php echo ((null !== (session('operator_id')) && (session('operator_id') !== ""))?(session('operator_id')):1); ?>;
		for (var i = 0; i < rows.length; ++i){
			data[i] = {};
			data[i].stockout_id = selections_map[rows[i].id].id;
			if ($.isEmptyObject(rows[i].logistics_no) || $.trim(rows[i].logistics_no) == ''){
				messager.alert("第"+ (i+1) +"行没有填写物流单号");
				return;
			}
			data[i].logistics_id = selections_map[rows[i].id].logistics_id;
			data[i].logistics_no = rows[i].logistics_no.trim();
			data[i].logistics_type = selections_map[rows[i].id].logistics_type;
			data[i].src_order_type = selections_map[rows[i].id].src_order_type;
			data[i].src_order_no = selections_map[rows[i].id].src_order_no;
			data[i].src_order_id = selections_map[rows[i].id].src_order_id;
			data[i].warehouse_id = selections_map[rows[i].id].warehouse_id;
			data[i].stockout_id = selections_map[rows[i].id].id;
			data[i].stockout_no = selections_map[rows[i].id].stockout_no;
			log[i] = {};
			log[i].trade_id = selections_map[rows[i].id].src_order_id;
			log[i].operator_id = operatorId;
			log[i].type = 21;
			if (!$.isEmptyObject(selections_map[rows[i].id].logistics_no) && $.trim(selections_map[rows[i].id].logistics_no) != ''){
				log[i].message = '修改物流单号:'+selections_map[rows[i].id].logistics_no+'到:'+rows[i].logistics_no.trim();
			}else{
				log[i].message = '添加物流单号:'+rows[i].logistics_no;
			}
		}
		// add waybill to the main page and save to db
		Post("/index.php/Stock/StockSalesPrint/saveWaybill", {logistics_info:JSON.stringify({data:data,log:log,is_force:isForce})}, function(r){
			if(r.status == 1){
				messager.alert(r.msg);
			}else if(r.status == 2){
				forceSaveLogisticsNo(r.data);
			}else{
				if(stockSalesPrint.is_open_response)
				{
					var respons_rows = $('#response_dialog_datagrid').datagrid('getRows');
					var respons_rows_map = utilTool.array2dict(respons_rows,'stock_id','');
				}
				var rows_map = utilTool.array2dict(rows,'id','');
				for (var j in selections_map){
					var index=$('#'+stockSalesPrint.params.datagrid.id).datagrid('getRowIndex',selections_map[j]);
					if(rows_map[selections_map[j].id]){
						$('#'+stockSalesPrint.params.datagrid.id).datagrid('updateRow',{index:index,row:{logistics_no:rows_map[selections_map[j].id].logistics_no}});
						if(stockSalesPrint.addWaybillType == 'synchronousLogistics'){
							$('#response_dialog').dialog('close');
							continue;
						}
						if(stockSalesPrint.is_open_response)
						{
							var response_index = $('#response_dialog_datagrid').datagrid('getRowIndex',respons_rows_map[selections_map[j].id]); 
							var data = {};
							data.orignal_type = 'consign_stockout';
							data.solve_type = 'consign_stockout';							
							solve_way = '<a href="javascript:void(0)" onClick="stockSalesPrint.consignStockoutOrder('+JSON.stringify(data).replace(/\"/g,'\'')+','+respons_rows_map[selections_map[j].id].stock_id+')">再次确认发货</a>';
				
							$('#response_dialog_datagrid').datagrid('updateRow',{index:response_index,row:{solve_way:solve_way}});
							//stockSalesPrint.updateResponSuccess(rows,data);
							//$('#response_dialog_datagrid').datagrid('deleteRow',response_index);
							if($.isEmptyObject($('#response_dialog_datagrid').datagrid('getRows')))
							{
								$('#response_dialog').dialog('close');
							}
						}
					}
				}
				stockSalesPrint.addWaybillType = '';
				if(isForce){
					$('#<?php echo ($id_list["logistics_dialog"]); ?>').dialog('close');
				}
				$('#<?php echo ($id_list["dialog"]); ?>').dialog('close');
			}
		});
	}else{
		$('#<?php echo ($id_list["dialog"]); ?>').dialog('close');
	}/*
	else if (1 == $('#'+stockSalesPrint.params.datagrid.id).datagrid('getSelections')[0].bill_type ||
			  2 == $('#'+stockSalesPrint.params.datagrid.id).datagrid('getSelections')[0].bill_type){
		Post("/index.php/Stock/WayBill/getWayBill", {stockout_ids:data,log:log}, function(r){
			if(r.status != 0){
				messager.alert(r.msg);
			}else{
				var rows = $('#'+datagridId).datagrid('getRows');
				for (var j = 0; j < selections.length; ++j){
					var index=$('#'+stockSalesPrint.params.datagrid.id).datagrid('getRowIndex',selections[j]);
					$('#'+stockSalesPrint.params.datagrid.id).datagrid('updateRow',{index:index,row:{logistics_no:rows[j].logistics_no}});
				}
			}
		});
	}
	*/
}
//saveWaybill(stockSalesPrint.params.id_list.logistics_no_datagrid_id,1)
function saveWaybillByType(){
	var type = stockSalesPrint.add_logistics_type;
	switch(type){
		case 'add':
			saveWaybill(stockSalesPrint.params.id_list.logistics_no_datagrid_id,1)
			break;
		case 'import':
			$('#<?php echo ($id_list["logistics_dialog"]); ?>').dialog('close');
			stockSalesPrint.import(1);
			break;
	}
}
function cancelWaybill(type){
	// if normal waybill, should give a hint to user
}
//获取物流单号是加载数据的函数
function getWaybillList(param,success,error)
{
	var sel_rows = $('#'+stockSalesPrint.params.datagrid.id).datagrid('getSelections');
	var selections = [];
	if(stockSalesPrint.add_logistics_id !=undefined){
		for(var l_i in sel_rows){
			if(sel_rows[l_i].logistics_id == stockSalesPrint.add_logistics_id && $.trim(sel_rows[l_i].logistics_no) =='')
			{
				selections.push(sel_rows[l_i]);
			}
		}
	}else{
		selections = sel_rows;
	}
	$('#sales_print_logistics_name').textbox('setText',selections[0].logistics_name);
	var data = {};
	var rows = [];
	var index = 0;
	for (var i = 0; i < selections.length; ++i){
		index=$('#'+stockSalesPrint.params.datagrid.id).datagrid('getRowIndex',selections[i]);
		rows[i] = {src_order_no:selections[i].src_order_no,shop_name:selections[i].shop_name,
					receiver_name:selections[i].receiver_name,logistics_no:selections[i].logistics_no,logistics_id:selections[i].logistics_id,bill_type:selections[i].bill_type,
					id:selections[i].id,index:index,package_count:selections[i].package_count};
	}
	data.rows = rows;
	data.total = rows.length;
	success(data);
}
//普通单号生成
function generateOrdinaryNo(datagridId,element_selector)
{
	if(!element_selector.form.form('validate')){
		return;
	}
	var rows = $('#'+datagridId).datagrid('getRows');
	var data = {};
	var ids = '';
	var index;
	if ($.isEmptyObject(rows) ){
		messager.alert('不存在订单信息');
		return;
	}
	if (rows.length == 1){
		messager.alert('至少包含两条订单信息');
		return;
	}
	if ($.isEmptyObject(rows[0].logistics_no) || $.trim(rows[0].logistics_no) == ''){
		messager.alert('请填第一条记录的物流单号');
		return;
	}else{
		data.template_no = rows[0].logistics_no;
	}
	element_selector.button_generate.linkbutton({text:'正在生成...',disabled:true});
	data.logistics_id = $('#'+datagridId).datagrid('getRows')[0].logistics_id;
	data.increment = element_selector.increment.numberbox('getValue');
	data.rule = element_selector.rule.combobox('getValue');
	data.need_num = rows.length - 1;
	Post("<?php echo U('StockSalesPrint/getOrdinaryNo');?>", data, function(r){
		if(0 == r.status ){
			element_selector.button_generate.linkbutton({text:'生成物流单号',disabled:false});
			if (!$.isEmptyObject(r.data.success)){
				for (var j = 0; j < r.data.success.length; ++j){
					$('#'+datagridId).datagrid('updateRow',{index:j+1,row:{logistics_no:r.data.success[j]}});
				}
			}
		}else{
			element_selector.button_generate.linkbutton({text:'生成物流单号',disabled:false});
			messager.alert(r.info);
		}
	},'json');

}
//生成订单号函数
function generateWaybill(datagridId,element_selector){
	var bill_type = $('#'+datagridId).datagrid('getRows')[0].bill_type;
	if(bill_type == 0){
		generateOrdinaryNo(datagridId,element_selector);
		return;
	}
    var rows = $('#'+datagridId).datagrid('getRows');
	var ids = '';
	var index;
	var indexStr = '';
	var indexArr = new Array();
	for (var i = 0; i < rows.length; ++i){
		if ($.isEmptyObject(rows[i].logistics_no) || $.trim(rows[i].logistics_no) == ''){
			ids = ids + rows[i].id + ',';
            indexStr =indexStr + rows[i].index+ ',';
		}
	}
    ids = ids.substr(0, ids.length - 1);
	indexStr = indexStr.substr(0, indexStr.length - 1);
	indexArr = indexStr == ''?[]:indexStr.split(',');
    //京东电子面单先存入包裹数
    if(bill_type == 1){
	    var packages = '';
        var reg = /^\+?[1-9][0-9]*$/;　　//正整数
		var packageFlag = 0;
        for (var j = 0; j < rows.length; ++j){
            if ($.isEmptyObject(rows[j].logistics_no) || $.trim(rows[j].logistics_no) == '') {
                if((rows[j].package_count == undefined)||(rows[j].package_count == '')){
                    rows[j].package_count = 1;
                }
                var pc = rows[j].package_count;
                if(!reg.test(pc) || pc > 20){
                    messager.alert('包裹数要求小于20的正整数');
                    packageFlag = 1;
                    return;
                }
                packages = packages + pc + ',';
            }
        }
        if(packageFlag) return;
        packages = packages.substr(0, packages.length - 1);
        Post("/index.php/Stock/StockSalesPrint/updatePackageCount", {stockout_ids:ids, packages:packages}, function(r){
            if(r.status == 0){
                var packageCounts = r.data[0].split(',');
                for (var k = 0; k < indexArr.length; ++k) {
                    $('#'+stockSalesPrint.params.datagrid.id).datagrid('updateRow',{index:parseInt(indexArr[k]),row:{package_count:packageCounts[k]}});
                }
			}else{
                messager.alert(r.msg);
            }
		});
    }
	if ($.isEmptyObject(ids) || $.trim(ids) == ''){
		messager.alert("没有需要生成物流单号的订单!");
		return;
	}
	element_selector.button_generate.linkbutton({text:'正在申请...',disabled:true});
	var logisticsId = $('#'+datagridId).datagrid('getRows')[0].logistics_id;
	var templateUrl = $('input[name=defaultStdTemplates]').val();
	var getWayBillUrl = '';
	var params = {};
	if(bill_type == 2 || bill_type == 9){
        getWayBillUrl = "/index.php/Stock/WayBill/newGetWayBill";
        params = {stockout_ids:ids, logistics_id:logisticsId,templateURL:templateUrl};
	}else{
        getWayBillUrl = "/index.php/Stock/WayBill/getWayBill";
        params = {stockout_ids:ids, logistics_id:logisticsId};
	}
    Post(getWayBillUrl, params, function(r){
		if(0 == r.status || 2==r.status){
			element_selector.button_generate.linkbutton({text:'申请电子面单号',disabled:false});
			if (!$.isEmptyObject(r.data.success)){
				for (var j = 0; j < rows.length; ++j){
					if (!$.isEmptyObject(r.data.success[rows[j].id])){
						$('#'+stockSalesPrint.params.datagrid.id).datagrid('updateRow',{index:rows[j].index,row:{logistics_no:r.data.success[rows[j].id].logistics_no,receiver_dtb:r.data.success[rows[j].id].receiver_dtb,waybill_info:r.data.success[rows[j].id].waybill_info}});
						$('#'+datagridId).datagrid('updateRow',{index:j,row:{logistics_no:r.data.success[rows[j].id].logistics_no}});
					}
				}
				//刷新底下的多物流
                if(typeof(sspMultiLogistics) != 'undefined'){
                    sspMultiLogistics.refresh($('#'+sspMultiLogistics.params.datagrid.id));
                }
			}
			if (r.data.fail.length > 0){
				$.fn.richDialog("response", r.data.fail, "stockout");
			}
		}else{
			element_selector.button_generate.linkbutton({text:'申请电子面单号',disabled:false});
			messager.alert(r.msg);
		}
		});
}

function getHasPrintedInfo(param,success,error)
{
	var data = {};
	data.rows = stockSalesPrint.hasPrintedInfo;
	data.total = stockSalesPrint.hasPrintedInfo.length;
	success(data);
}

function forceSaveLogisticsNo(data){
	$("#<?php echo ($id_list["logistics_dialog"]); ?>").dialog({
		title: "返回信息",
		width: 764,
		height: 500,
		modal: true,
		closed: false,
		inline: true,
		iconCls: 'icon-save',
	});
	$("#<?php echo ($id_list["logistics_dialog_datagrid"]); ?>").datagrid({
		rownumbers: true,
		singleSelect: true,
		data: data,
		toolbar:'#<?php echo ($id_list["logistics_dialog_datagrid_toolbar"]); ?>',
		columns: [[
			{field: "stock_id", hidden: true},
			{field: "stock_no", title: "订单编号", width: 200},
			{field: "msg", title: "错误原因", width: 400}
		]]
	});
}








function connectStockWS(){	
	if($.isEmptyObject(stockWs)){
		stockWs = new WebSocket("ws://127.0.0.1:13528");
		stockWs.onmessage = onStockMessage;
		stockWs.onerror = onStockError;
	}
	return ;
}


function onStockError(){
	$.messager.progress('close');
	$('#'+stockSalesPrint.printing).linkbutton({text:'打印',disabled:false});
	stockWs = null;
	$('#stocksalesprint_dialog').dialog({    
    title: '打印组件错误',    
    width: 400,    
    height: 200,    
    closed: false,    
    cache: false,    
    href:  "<?php echo U('Stock/StockSalesPrint/onWSError');?>",    
    modal: true   
	});    
	$('#stocksalesprint_dialog').dialog('refresh', "<?php echo U('Stock/StockSalesPrint/onWSError');?>");  
}

function onStockMessage(event){
	var ret = $.parseJSON(event.data);
	var msg = ret.msg;
	if((!$.isEmptyObject(msg))&&msg!="成功")
	{
		$('#'+stockSalesPrint.printing).linkbutton({text:'打印',disabled:false});
		messager.alert(msg);
		return;
	}
	if(!$.isEmptyObject(ret)){
		switch(ret.cmd){
			case 'print':
				var taskID = ret.taskID+"";
				taskID = taskID.substr(taskID.length-3,taskID.length);
				if(taskID==211||taskID==221)
				{
					var preview;
					preview = ret.previewURL;
					if(!$.isEmptyObject(preview)){
						window.open(ret.previewURL);
					}else{
						preview = ret.previewImage;
						if(!$.isEmptyObject(preview)&&(preview.length != 0))
							window.open(ret.previewImage[0]);
					}
					if(!$.isEmptyObject(preview) || (!$.isEmptyObject(preview)&&(preview.length != 0)))
					{
						var type = taskID;
						var rows = stockSalesPrint.selectRows;
						var stockout_ids = "";
						var print_type;
						for(var i in rows){
							if(type==221)
							{
								stockout_ids += rows[i].id + ",";
								print_type = "logistics";
							}
							else if(type==211){
								stockout_ids += rows[i].id + ",";
								print_type = "goods";
							}
						}
						stockout_ids = stockout_ids.substr(0,stockout_ids.length-1);
						$.post("/index.php/Stock/StockSalesPrint/previewPrintTemplateData",{stockout_ids:stockout_ids,print_type:print_type,is_print:0},function(r){
							if(r.status != 0){
								$.messager.progress('close');
								$('#'+stockSalesPrint.printing).linkbutton({text:'打印',disabled:false});
								messager.alert("日志记录失败");
								$('#'+stockSalesPrint.params.id_list.dialog).dialog('close');
							}
						});
					}
				}
			break;
			case 'notifyPrintResult':
			{
				if(ret.taskStatus == "printed"){
					var logistics_nos = "";
					var status = ret.printStatus;
					for(var i in status){
						logistics_nos += status[i].documentID + ",";
					}
					var type = ret.taskID;
                    var isMulti = 0;  // 判断是否多物流单号
					var tmp_isMulti;
                    isMulti = type.substr(type.length-3,1);
					if(isMulti==3){isMulti=0;tmp_isMulti=3;}
					type = type.substr(type.length-2,type.length);
					var rows = stockSalesPrint.print_list[ret.taskID.toString()];
					var stockout_ids = "";
					var print_type;
					var all_rows = $('#'+stockSalesPrint.params.datagrid.id).datagrid('getRows');
					var format_all_rows = utilTool.array2dict(all_rows,'id','');
					for(var i in rows){
						if(type==22&&(logistics_nos.indexOf(rows[i].logistics_no) != -1||logistics_nos.indexOf(rows[i].id) != -1))
						{
							stockout_ids += rows[i].id + ",";
							print_type = "logistics";
						}
						else if(type==22&&(logistics_nos.indexOf(rows[i].logistics_no) == -1||logistics_nos.indexOf(rows[i].id) == -1)){
							stockout_ids += rows[i].id + ",";
						}
						else if(type==12&&logistics_nos.indexOf(rows[i].id) != -1){
							stockout_ids += rows[i].id + ",";
							print_type = "goods";
						}
                        else if(type==32){
                            stockout_ids += rows[i].id + ",";
                            print_type = "sorting";
                        }
					}
					stockout_ids = stockout_ids.substr(0,stockout_ids.length-1);
					var parameter = {stockout_ids:stockout_ids,print_type:print_type,is_print:1,value:1};
					if(isMulti == 1){
						print_type = "multipleLogistics";

						var mulRows = $('#' + sspMultiLogistics.params.datagrid.id).datagrid('getSelections');
						var multiIds = '';
						for(var j = 0; j < mulRows.length; ++j){
							multiIds = multiIds + mulRows[j].id + ',';
						}
						var multiIds_length = multiIds.length;
						var multiId_list = multiIds.substr(0,multiIds_length-1);
						parameter = {stockout_ids:stockout_ids,print_type:print_type,is_print:1,multiIds:multiId_list,value:1};
					}else if(isMulti ==2 || tmp_isMulti ==3){
                        print_type = "multiplePrintLogistics";
                        if(logistics_nos != ''){
                            logistics_nos = logistics_nos.substr(0,logistics_nos.length-1);
						}
                        var parameters = {stockout_ids:'',print_type:print_type,is_print:1,multiIds:logistics_nos};
						if(tmp_isMulti ==3){
							Post("/index.php/Stock/StockSalesPrint/changePrintStatus",parameters,function(r){
							var sel_rows = $('#'+stockSalesPrint.params.datagrid.id).datagrid('getSelections');
							for (var k = 0; k < sel_rows.length; ++k) {
								index = $('#' + stockSalesPrint.params.datagrid.id).datagrid('getRowIndex', sel_rows[k]);
								$('#'+stockSalesPrint.params.datagrid.id).datagrid('updateRow',{index:index,row:{package_count:1}});
							}
							});
						}else{
							parameter = parameters;
						}
                    }else if(isMulti == 4){
                        print_type = "multiplePrintSfOrderLogistics";
                        multiIds = getMultiIdsList();
                        parameter = {stockout_ids:stockout_ids,print_type:print_type,is_print:1,multiIds:multiIds,value:1};
					}
					if(print_type!='goods'&&print_type!='logistics'&&print_type!='sorting'&&tmp_isMulti!=3){
						Post("/index.php/Stock/StockSalesPrint/changePrintStatus",parameter,function(r){
							if(isMulti == 1){ // 多物流单号
								if(r.status == 0){
									$('#'+stockSalesPrint.printing).linkbutton({text:'打印',disabled:false});
								}
								else{
									$('#'+stockSalesPrint.printing).linkbutton({text:'打印',disabled:false});
									messager.alert("打印成功但数据更新失败，请手动更新");
								}
								$('#' + sspMultiLogistics.params.datagrid.id).datagrid('reload');
								$('#'+stockSalesPrint.printing).linkbutton({text:'打印',disabled:false});
								$('#'+stockSalesPrint.params.id_list.dialog).dialog('close');
							}else{
								$.messager.progress('close');
								$('#'+stockSalesPrint.printing).linkbutton({text:'打印',disabled:false});
								if(isMulti == 2){
									$('#' + sspMultiLogistics.params.add.id).dialog('close');
									//刷新底下的多物流
									if(typeof(sspMultiLogistics) != 'undefined'){
										sspMultiLogistics.refresh($('#'+sspMultiLogistics.params.datagrid.id));
									}
								}else{

									if(stockSalesPrint.goods_print == 'stockSalesPrint_printer_goods'){
										$('#'+stockSalesPrint.params.id_list.print_dialog).dialog('close');
									}else{
										$('#'+stockSalesPrint.params.id_list.dialog).dialog('close');
									}
									//刷新底下的多物流
									if(typeof(sspMultiLogistics) != 'undefined'){
										sspMultiLogistics.refresh($('#'+sspMultiLogistics.params.datagrid.id));
									}
								}
							}
						});
					}

				}else if(ret.taskStatus == "failed"){
					$.messager.progress('close');
					$('#'+stockSalesPrint.printing).linkbutton({text:'打印',disabled:false});
                    //刷新底下的多物流
                    if(typeof(sspMultiLogistics) != 'undefined'){
                        sspMultiLogistics.refresh($('#'+sspMultiLogistics.params.datagrid.id));
                    }
					messager.alert("打印失败");
				}
			}
			break;
			case 'printerConfig':
			break;
			case 'getPrinterConfig':
			{
                $('#paper_width').textbox('setValue',ret.printer.paperSize.width);
                $('#paper_height').textbox('setValue',ret.printer.paperSize.height);
			}
			break;
			case 'setPrinterConfig':
			{
			    messager.alert('设置打印机纸张大小成功');
                $("#setPrinter").dialog('close');
			}
			break;
			case 'getPrinters':
			var type = ret.requestID;
			var dwl = type.substr(type.length-3,1);
			type = type.substr(type.length-2,type.length);
			var printerId = dwl==2?'stockSalesPrint_logistics_printer_lists':stockSalesPrint.logistics_print;
			var logisticsType = dwl==2?'multiLogistics':'logistics';
			if(type == 88){
				$('#'+printerId).combobox({
					valueField: 'name',
					textField: 'name',
					data: ret.printers,
					value: ret.defaultPrinter
				});
				$('#'+printerId).combobox('reload');
				newTemplateOnSelect(logisticsType);
			}else if(type == 99){
				$('#'+stockSalesPrint.goods_print).combobox({
					valueField: 'name',
					textField: 'name',
					data: ret.printers,
					value: ret.defaultPrinter
				});
				$('#'+stockSalesPrint.goods_print).combobox('reload');
				newTemplateOnSelect('goods');
			}
			break;
		}
		
	}
}

function isExistInObj(obj,str){
	if(JSON.stringify(obj).indexOf(str) == -1)
		return false;
	else return true;
}
function checkBeforeDialogShow(rows,isMulti){
    if ($.isEmptyObject(rows)){
        messager.alert('请选择订单');
        return false;
    }
    // 判断是否来自多物流单号
    if(isMulti===1){
        var mulRows = $('#'+sspMultiLogistics.params.datagrid.id).datagrid('getSelections');
        if ($.isEmptyObject(mulRows)){
            messager.alert('请选择一个多物流单');
            return false;
        }
        if(rows[0].id != mulRows[0].stockout_id){
            messager.alert('主订单与多物流单不一致，请重新选择');
            return false;
        }
    }
    if(isMulti == 3){
        var billType = rows[0].bill_type;
        var logisticsNo = rows[0].logistics_no;
        if(billType !=2){
            messager.alert('只有菜鸟物流支持批量打印多物流');
            return false;
		}
	}
    return true;
}
function newPrintGoodsDialog(){
	is_print_setting = false;
	var rows = $('#'+stockSalesPrint.params.datagrid.id).datagrid('getSelections');
	if ($.isEmptyObject(rows)){
		messager.alert('请选择订单');
		return;
	}
	connectStockWS();
	var ids = "";
	for(var i in rows){
		ids += rows[i].id + ",";
	}
	ids = ids.substr(0,ids.length-1);
	stockSalesPrint.showDialog('<?php echo ($id_list["dialog"]); ?>','打印发货单',"<?php echo U('StockSalesPrint/newPrintGoods');?>?ids="+ids,190,350,[{text:"取消",handler:function(){$("#stocksalesprint_dialog").dialog('close');}}]);
}
function printPickListDialog(){
    var rows = $('#'+stockSalesPrint.params.datagrid.id).datagrid('getSelections');
    if ($.isEmptyObject(rows)){
        messager.alert('请选择订单');
        return;
    }
    connectStockWS();
    var ids = "";
	var num = 0;
    for(var i in rows){
        ids += rows[i].id + ",";
		num++;
    }
    ids = ids.substr(0,ids.length-1);
	$.post("/index.php/Stock/StockSalesPrint/validatePick",{num:num},function(res){
		if(res.status == 1){
			messager.alert(res.info);
			return;
		}
		stockSalesPrint.showDialog('<?php echo ($id_list["dialog"]); ?>','打印分拣单',"<?php echo U('StockSalesPrint/printPickList');?>?ids="+ids,190,350,[{text:"取消",handler:function(){$("#stocksalesprint_dialog").dialog('close');}}]);
	});
}
// 打印顺丰子母单
function printSfDialog(){
    var rows = $('#'+stockSalesPrint.params.datagrid.id).datagrid('getSelections');
    if ($.isEmptyObject(rows)){
        messager.alert('请选择订单');
        return false;
    }
    //rows[i].logistics_id 选中订单的物流公司的id，可以选择多个订单，当物流公司不同时弹出提示信息
    var logisticsId = rows[0].logistics_id;
    var logisticsType = rows[0].logistics_type;
    var billType = rows[0].bill_type;
    for (var i = 1; i < rows.length; ++i) {
        if (rows[i].logistics_id != logisticsId) {
            messager.alert('物流公司不一致,请重新选择');
            return;
        }
    }
    if(billType != 1){
        messager.alert('请选择线下热敏类型的物流（京邦达，顺丰热敏）');
        return;
	}
    connectStockWS();
    var ids = "";
    for(var i in rows){
        ids += rows[i].id + ",";
    }
    ids = ids.substr(0,ids.length-1);
    stockSalesPrint.showDialog('<?php echo ($id_list["dialog"]); ?>','打印线下多物流',"<?php echo U('StockSalesPrint/printSfOrder');?>?logisticsId="+logisticsId+"&ids="+ids,200,300,[{text:"取消",handler:function(){$("#stocksalesprint_dialog").dialog('close');}}]);
}
function newPrintDialog(isMulti){
	is_print_setting = false;
	var rows = $('#'+stockSalesPrint.params.datagrid.id).datagrid('getSelections');
	if ($.isEmptyObject(rows)){
        messager.alert('请选择订单');
        return false;
    }
    //rows[i].logistics_id 选中订单的物流公司的id，可以选择多个订单，当物流公司不同时弹出提示信息
    var logisticsId = rows[0].logistics_id;
    var logisticsType = rows[0].logistics_type;
    for (var i = 1; i < rows.length; ++i) {
        if (rows[i].logistics_id != logisticsId) {
            messager.alert('物流公司不一致,请重新选择');
            return;
        }
    }
    connectStockWS();
	var ids = "";
    for(var i in rows){
        ids += rows[i].id + ",";
    }
    ids = ids.substr(0,ids.length-1);
  	stockSalesPrint.showDialog('<?php echo ($id_list["print_dialog"]); ?>','打印发货单和物流单',"<?php echo U('WayBill/newPrintDialog');?>?logisticsId="+logisticsId+"&ids="+ids+"&isMulti=0",240,520,[{text:"取消",handler:function(){$("#stocksalesprint_print_dialog").dialog('close');}}]);
}
function newPrintLogAndPickDialog(){
    var rows = $('#'+stockSalesPrint.params.datagrid.id).datagrid('getSelections');
    if ($.isEmptyObject(rows)){
        messager.alert('请选择订单');
        return false;
    }
    //rows[i].logistics_id 选中订单的物流公司的id，可以选择多个订单，当物流公司不同时弹出提示信息
    var logisticsId = rows[0].logistics_id;
    var logisticsType = rows[0].logistics_type;
    for (var i = 1; i < rows.length; ++i) {
        if (rows[i].logistics_id != logisticsId) {
            messager.alert('物流公司不一致,请重新选择');
            return;
        }
    }
    connectStockWS();
    var ids = "";
    for(var i in rows){
        ids += rows[i].id + ",";
    }
    ids = ids.substr(0,ids.length-1);
    stockSalesPrint.showDialog('<?php echo ($id_list["print_dialog"]); ?>','打印分拣单和物流单',"<?php echo U('WayBill/newPrintLogAndPickDialog');?>?logisticsId="+logisticsId+"&ids="+ids+"&isMulti=0",240,520,[{text:"取消",handler:function(){$("#stocksalesprint_print_dialog").dialog('close');}}]);
}
function addPrintBatch(id_list,type,is_log,is_logAndpick){
	if(type == 'sorting'){
		var pick_data = getBatchData();
		var pick_list_no = pick_data.pick_list_no;
	}else{
		var pick_list_no = '';
	}
	$.post("/index.php/Stock/StockSalesPrint/addPrintBatch",{stockout_ids:id_list,print_type:type,is_log:is_log,pick_list_no:pick_list_no},function(r){
		if(r.status==0){
			var sel_rows = $('#'+stockSalesPrint.params.datagrid.id).datagrid('getSelections');
			for (var k = 0; k < sel_rows.length; ++k) {
				index = $('#' + stockSalesPrint.params.datagrid.id).datagrid('getRowIndex', sel_rows[k]);
				if(type=='goods'){
					$('#'+stockSalesPrint.params.datagrid.id).datagrid('updateRow',{index:index,row:{sendbill_print_status:1}});
				}else if(type=='logistics'){
					$('#'+stockSalesPrint.params.datagrid.id).datagrid('updateRow',{index:index,row:{logistics_print_status:1}});
					if(is_logAndpick == 1){
                        $('#'+stockSalesPrint.params.datagrid.id).datagrid('updateRow',{index:index,row:{picklist_print_status:1}});
                    }
				}else if(type == 'sorting'){
					$('#'+stockSalesPrint.params.datagrid.id).datagrid('updateRow',{index:index,row:{picklist_print_status:1}});
				}
				if(r.batch_no!=''){
					$('#'+stockSalesPrint.params.datagrid.id).datagrid('updateRow',{index:index,row:{batch_no: r.batch_no}});
				}
				if(r.picklist_no!=''){
					$('#'+stockSalesPrint.params.datagrid.id).datagrid('updateRow',{index:index,row:{picklist_no: r.picklist_no}});
				}
			}
			if(stockSalesPrint.printing == 'printing_dialog'){
				$('#'+stockSalesPrint.params.id_list.print_dialog).dialog('close');
			}else{
				$('#'+stockSalesPrint.params.id_list.dialog).dialog('close');
			}
		}else{
			$('#'+stockSalesPrint.printing).linkbutton({text:'打印',disabled:false});
			messager.alert("打印成功但数据更新失败，请手动更新");
		}
	});
}
function newDealBeforePrint(type,isMulti,is_continue,print_dialog_type){
    var reg = /^\+?[1-9][0-9]*$/;　　//正整数
    if(isMulti == 2){
        var packageNo = $('#CountInput').val();
        if(!reg.test(packageNo)){
            messager.alert('请输入正确的包裹数');
            return;
        }
    }
	$('#'+stockSalesPrint.printing).linkbutton({text:'打印中...',disabled:true});
	if(is_continue == 'continue'){
		$('#<?php echo ($id_list["continue_print_result"]); ?>').dialog('close');
	}
	if(type == "goods"){
		var templateId = $('#'+stockSalesPrint.goods_print_template).combobox('getValue');
		var template_type = 5;
	}else if(type == "sorting"){
        var templateId = $('#'+stockSalesPrint.goods_print_template).combobox('getValue');
        var template_type = 5;
    }else if(type == "sfOrder"){
        var templateId = $('#'+stockSalesPrint.goods_print_template).combobox('getValue');
        var template_type = 5;
    }else if(type == "logistics"){
		var template_type = 6;
		var templateId = '';
		if(isMulti ==2){
		    templateId = $('#stock_sales_print_logistics_templates').combobox('getValue');
		}else{
            templateId = $('#'+stockSalesPrint.logistics_print_template).combobox('getValue');
        }
	}
	if($.isEmptyObject(templateId) || $.trim(templateId) == '' ){
		$('#'+stockSalesPrint.printing).linkbutton({text:'打印',disabled:false});
		messager.alert('没有匹配的模板,请到模板管理界面下载模板!');
		return;
	}
    var rows = $('#' + stockSalesPrint.params.datagrid.id).datagrid('getSelections');
	var logisticsId = rows[0].logistics_id;
	if(default_template_id != templateId){
		Post("<?php echo U('Stock/StockSalesPrint/saveDefaultTemplate');?>",{template_id:templateId,template_type:template_type,logistics_id:logisticsId},function(res){
			if(res.status == 1){
				messager.alert(res.msg);
			}
		});
		if(print_dialog_type == 'together'){
			var goods_templateId = $('#'+stockSalesPrint.goods_print_template).combobox('getValue');
			var goods_template_type = 5;
			if(default_goods_template_id != goods_templateId){
				Post("<?php echo U('Stock/StockSalesPrint/saveDefaultTemplate');?>",{template_id:goods_templateId,template_type:goods_template_type,logistics_id:logisticsId},function(res){
				if(res.status == 1){
					messager.alert(res.msg);
				}
		});
			}
		}
	}
	stockSalesPrint.hasPrintedInfo = [];
	var ids = '';
	var rows = $('#'+stockSalesPrint.params.datagrid.id).datagrid('getSelections');
	for (var i = 0; i < rows.length; ++i){
		ids = ids + rows[i].id + ',';
	}
	var ids_length = ids.length;
	var id_list = ids.substr(0,ids_length-1);
	var row_ret;// = getRowsIds('','logistics',isMulti,is_continue);
	if(is_continue != 'continue'){
		var multiIds = '';
		var parameter;
		if(print_dialog_type == 'together') {
			parameter = {ids: id_list, type: 'together',all_ids:id_list};
		}else if(print_dialog_type == 'logAndPick'){
            parameter = {ids: id_list, type: 'logAndPick',all_ids:id_list};
        }else{
			parameter = {ids: id_list, type: type,all_ids:id_list};
		}
		if(isMulti == 1){
			type = 'multipleLogistics';
			var mulRows = $('#' + sspMultiLogistics.params.datagrid.id).datagrid('getSelections');
			for(var j = 0; j < mulRows.length; ++j){
				multiIds = multiIds + mulRows[j].id + ',';
			}
			var multiIds_length = multiIds.length;
			var multiId_list = multiIds.substr(0,multiIds_length-1);
			parameter = {ids:id_list,type:type,multiIds:multiId_list};
		}
		if(id_list == ''){
			messager.alert('没有获取到订单信息，请刷新后重试！');
			$('#'+stockSalesPrint.printing).linkbutton({text:'打印',disabled:false});
			return;
		}
	}else{
		if(type=='logistics'){
			row_ret = getRowsIds('','logistics',isMulti,is_continue);
		}else if(type=='sfOrder'){
            row_ret = getRowsIds('','logistics',isMulti,is_continue);
        }else if(type == 'goods'){
			row_ret = getRowsIds('','goods',isMulti,is_continue);
		}else if(type == 'sorting'){
            row_ret = getRowsIds('','sorting',isMulti,is_continue);
        }else if(print_dialog_type == 'together'){
			row_ret_logistics = getRowsIds('','logistics',isMulti,is_continue);
			row_ret_goods = getRowsIds('','goods',isMulti,is_continue);
			row_ret = $.extend(row_ret_goods,row_ret_logistics);
		}else{
			row_ret = getRowsIds('','multipleLogistics',isMulti,is_continue);
		}
		var parameter;
		if(print_dialog_type == 'together'){
			parameter = {ids:row_ret.id_list,type:'together',multiIds:row_ret.multiId_list,all_ids:id_list};
		}else if(print_dialog_type == 'logAndPick'){
            parameter = {ids:row_ret.id_list,type:'logAndPick',multiIds:row_ret.multiId_list,all_ids:id_list};
        }else{
			parameter = {ids:row_ret.id_list,type:row_ret.print_type,multiIds:row_ret.multiId_list,all_ids:id_list};
		}
	}
	var is_log=0;
	var is_logAndpick=0;
	if(is_continue == 'continue' && (type == 'goods'||type == 'logistics'||type == 'sorting'||print_dialog_type == 'together')){
		if(row_ret.id_list == ''){is_log=1;}
		if(print_dialog_type == 'together'){
			addPrintBatch(id_list,'goods',is_log,is_logAndpick);
			addPrintBatch(id_list,'logistics',is_log,is_logAndpick);
		}else{
            if(print_dialog_type == 'logAndPick'){
                is_logAndpick = 1;
                addPrintBatch(id_list,'sorting',is_log,is_logAndpick);
            }
			addPrintBatch(id_list,'logistics',is_log,is_logAndpick);
		}
	}
	if(is_continue != 'continue'  || (is_continue == 'continue' && (row_ret.isMulti == 0&&row_ret.id_list != ''&&row_ret.id_list != undefined || row_ret.isMulti == 1&&row_ret.multiId_list != ''&&row_ret.multiId_list != undefined))){
		Post("<?php echo U('Stock/StockSalesPrint/getHasPrintedInfo');?>",parameter,function(r){
			var concat_has_printed = [];
			if(print_dialog_type=='together'){concat_has_printed = r.data.has_printed.together;}
			else if(print_dialog_type=='logAndPick'){
                concat_has_printed = r.data.has_printed.logAndPick;
			}else{
				if(type=='goods'){concat_has_printed = r.data.has_printed.goods;}
				if(type=='sorting'){concat_has_printed = r.data.has_printed.sorting;}
				if(type=='logistics'){concat_has_printed = r.data.has_printed.logistics;}
				if(type=='sfOrder'){concat_has_printed = r.data.has_printed.logistics;}
				if(type=='multipleLogistics'){concat_has_printed = r.data.has_printed.multipleLogistics;}
			}
			if(is_continue != 'continue'&&(type == 'goods'||type == 'logistics'||type == 'sorting')&&($.isEmptyObject(r.data.has_printed[type])||r.data.has_printed[type].length==0)&&print_dialog_type != 'together'||is_continue != 'continue'&&print_dialog_type == 'together'&&($.isEmptyObject(r.data.has_printed['together'])||r.data.has_printed['together'].length==0)){
				if(print_dialog_type == 'together'){
					addPrintBatch(id_list,'goods',is_log,is_logAndpick);
					addPrintBatch(id_list,'logistics',is_log,is_logAndpick);
				}else{
                    if(print_dialog_type == 'logAndPick'){is_logAndpick = 1};
                    addPrintBatch(id_list,type,is_log,is_logAndpick);
				}
			}
			if(r.status == 1)
			{
				$('#'+stockSalesPrint.printing).linkbutton({text:'打印',disabled:false});
				messager.alert(r.msg);
			}else if(r.status == 2){
				$('#'+stockSalesPrint.printing).linkbutton({text:'打印',disabled:false});
				stockSalesPrint.hasPrintedInfo = stockSalesPrint.hasPrintedInfo.concat(r.data.fail,concat_has_printed);
				$.fn.richDialog("response", stockSalesPrint.hasPrintedInfo, "stockout");
			}else{
                if(!(isMulti == 2)){
					if((type=='goods'&&(!$.isEmptyObject(r.data.has_printed.goods)||r.data.has_printed.goods.length!=0))||(type=='sorting'&&(!$.isEmptyObject(r.data.has_printed.sorting)||r.data.has_printed.sorting.length!=0))||(type=='sfOrder'&&(!$.isEmptyObject(r.data.has_printed.logistics)||r.data.has_printed.logistics.length!=0))||(type=='logistics'&&(!$.isEmptyObject(r.data.has_printed.logistics)||r.data.has_printed.logistics.length!=0))||(type=='multipleLogistics'&&(!$.isEmptyObject(r.data.has_printed.multipleLogistics)||r.data.has_printed.multipleLogistics.length!=0))||(print_dialog_type=='together'&&(!$.isEmptyObject(r.data.has_printed.together)||r.data.has_printed.together.length!=0))||(print_dialog_type=='logAndPick'&&(!$.isEmptyObject(r.data.has_printed.logAndPick)||r.data.has_printed.logAndPick.length!=0))){
						$('#'+stockSalesPrint.printing).linkbutton({text:'打印',disabled:false});
						stockSalesPrint.hasPrintedInfo = stockSalesPrint.hasPrintedInfo.concat(concat_has_printed);
						$('#<?php echo ($id_list["continue_print_result"]); ?>').dialog({
							href:"<?php echo U('Stock/StockSalesPrint/showHasPrintedInfo');?>?type="+type+"&isMulti="+isMulti+"&flag=new&print_dialog_type="+print_dialog_type,
							title: "问题订单列表",
							width: 660,
							height: 450,
							modal: true,
							closed: false,
							inline: true,
							iconCls: 'icon-save',
						});
					}else{
						choosePrint(type,isMulti,print_dialog_type);
					}
				}else{
					choosePrint(type,isMulti,print_dialog_type);
				}
			}
		},'json');
		
	}else{
		choosePrint(type,isMulti,print_dialog_type);
	}
}
//选择执行流程
//1.发货单，普通物流单，批量多物流（普通和电子）  2.电子面单，批量添加并打印电子多物流
function choosePrint(type,isMulti,print_dialog_type){
	var is_waybill = false;
	if(type=='sorting'){
        printPickList();
    }else if(type == 'sfOrder'){
	    printSfOrder();
	}else if(type!='goods'){
		var contents = getLogisticsTemplatesContents();
		var templateSelectId = isMulti == 2?'stock_sales_print_logistics_templates':stockSalesPrint.logistics_print_template;
		var templateId = $('#'+templateSelectId).combobox('getValue');
		var content = JSON.parse(contents[templateId]);
		var templateURL = content.user_std_template_url;
		is_waybill = !$.isEmptyObject(templateURL)||$('input[name=stdTemplates]').val()!="";
	}
	if(type=='goods'||type=='logistics'&&isMulti==0&&!is_waybill||type=='multipleLogistics'&&isMulti==1){
		if(print_dialog_type == 'together'){
			var goods_printer = $('#'+stockSalesPrint.goods_print).combobox('getValue');
			var logistics_printer = $('#'+stockSalesPrint.logistics_print).combobox('getValue');
			if(goods_printer == logistics_printer){
				printGoodsLogisticsMultiple(type,isMulti,print_dialog_type);
			}else{
				printGoodsLogisticsMultiple(type,isMulti,'');
				if(print_dialog_type == 'together'){
					printGoodsLogisticsMultiple('goods',isMulti,'');
				}else if(print_dialog_type == 'logAndPick'){
					printPickList();
				}
			}
		}else{
			printGoodsLogisticsMultiple(type,isMulti,'');
			if(print_dialog_type == 'together'){
				printGoodsLogisticsMultiple('goods',isMulti,'');
			}else if(print_dialog_type == 'logAndPick'){
				printPickList();
			}
		}
		
	}else if(type=='logistics'&&isMulti==2||type=='logistics'&&isMulti==0&&is_waybill||type=='logistics'&&isMulti==3&&is_waybill){
		getWaybillCheck(templateId,isMulti,type,print_dialog_type);
	}
}

function getWaybillCheck(templateId,isMulti,type,print_dialog_type){
		getWaybill(templateId,isMulti,print_dialog_type);
}
//打印发货单，普通物流单，批量多物流
function printGoodsLogisticsMultiple(type,isMulti,print_dialog_type){
	//获取打印数据
	var printData = getPrintData(type,isMulti,print_dialog_type);
	var printer = printData['printer'];
	var printed_ids = printData['printed_ids'];
	var datas = printData['datas'];
	if(type=='goods'){
        newPrint(datas,printer,12,printed_ids,isMulti);
	}else if(type=='logistics' || type=='multipleLogistics'){
        newPrint(datas,printer,22,printed_ids,isMulti);
	}
}	
function printSfOrder(){
    var printer = $('#'+stockSalesPrint.goods_print).combobox('getValue');
    var templateId = $('#'+stockSalesPrint.goods_print_template).combobox('getValue');
    var rows = $('#'+stockSalesPrint.params.id_list.datagrid).datagrid('getSelections');
    var contents = getGoodsTemplatesContents();
    var goodsDetail = getGoodsGoodsDetail();
    var logisticsNos = getMultiLogisticsNo();
    var logisticsArr = [];
    logisticsArr = composeLogisticsNo(rows,logisticsNos);
    //setPricePointNum('new_old_print_point_number',goodsDetail,price_configs);
    var isMulti = 4;
    var res = getSfOrderPrintData(contents,templateId,goodsDetail,logisticsArr,1);
    var datas = res[0];
    var printed_ids = res[1];
    newPrint(datas,printer,22,printed_ids,isMulti);  // 4:批量打印顺丰子母单
}
function previewSfOrder(isMulti){
    var contents = getGoodsTemplatesContents();
    var rows = $('#'+stockSalesPrint.params.id_list.datagrid).datagrid('getSelections');
    var logisticsNos = getMultiLogisticsNo();
    var templateSelectId = stockSalesPrint.goods_print_template;
    var templateId = $('#'+templateSelectId).combobox('getValue');
    var logisticsArr = [];
    if(templateId == ""){
        messager.alert("预览错误：没有选择模板，请到模板列表页面下载模板");
        $("#<?php echo ($id_list["dialog"]); ?>").dialog('close');
        return ;
    }
    logisticsArr = composeLogisticsNo(rows,logisticsNos);
    var goodsDetail = getGoodsGoodsDetail();
    setPricePointNum('new_old_print_point_number',goodsDetail,price_configs);
    var datas = getSfOrderPrintData(contents,templateId,goodsDetail,logisticsArr,isMulti);
    newPreview(datas[0],221);
}
function composeLogisticsNo(rows,logisticsNos){
    var logisticsArr = [];
    for(var i=0;i<rows.length;i++){
        var sid = rows[i].id;
        var arr = [];
        var logisticsStr = '';
        logisticsStr = logisticsStr+rows[i].logistics_no+",";
        for(var j=0;j<logisticsNos.length;j++){
            if(logisticsNos[j].stockout_id == sid){
                logisticsStr = logisticsStr + logisticsNos[j].logistics_no+",";
            }
        }
        var logisticsStr_length = logisticsStr.length;
        var logisticsNo_list = logisticsStr.substr(0,logisticsStr_length-1);
        arr[sid] = logisticsNo_list;
        logisticsArr.push(arr);
    }
    return logisticsArr;
}
function getSfOrderPrintData(contents,templateID,goodsDetail,logisticsNos,isMulti){
    contents = JSON.parse(contents[templateID]);
    var templateURL = contents.custom_area_url;
    var rows =  $('#stocksalesprint_datagrid').datagrid('getSelections');
    var datas = [],print_data,row;
    var printed_ids =[];
	var logisticsArr = [];
	var obj = {},mulRow = {};
    var k=0;
    for (var j = 0; j < rows.length; ++j){
        row = rows[j];
        var logisticsStr = logisticsNos[j][row.id];
        logisticsArr=logisticsStr.split(",");
        var sf_app = parseSfAppKey(row,['寄方付','收方付','第三方付']);
        for(var i=0;i<logisticsArr.length;i++){
            var parentLogisticsNo = logisticsArr[0].indexOf('-') == -1?logisticsArr[0]:'';
            var logisticsNo = logisticsArr[i];
            mulRow.logistics_no = logisticsNo;
            mulRow.id = row.id;
            var package_data = getPackageCode(isMulti,logisticsNo,row.package_count,row.id,mulRow);
            print_data = composeNormalData(row,parentLogisticsNo,sf_app,package_data,goodsDetail);
            if(logisticsNo.indexOf('-') != -1){
                print_data.trade.logistics_no = logisticsNo.split('-')[0];
                print_data.trade.child_logistics_no = '子单号：'+logisticsNo.split('-')[0];
			}else{
                print_data.jos.package_seq = '';
                print_data.trade.parent_logistics_no = "运单号"+parentLogisticsNo;
                if(row.logistics_type == 1311 && row.bill_type == 1){
                    print_data.trade.package_code = logisticsNo+'-1-'+row.package_count+'-';
                    print_data.jos.package_seq = '1/'+row.package_count;
                }
			}
            obj = {
                'documentID' : package_data[2],
                'contents' : [
                    {
                        'templateURL' : templateURL,
                        'data' : print_data,
                    }
                ]
            };
            datas[k] = obj;
            k++;
        }
        printed_ids.push({id:rows[j].id,logistics_no:rows[j].logistics_no});
    }
    return [datas,printed_ids];
}
function printPickList(){
    var printer = $('#'+stockSalesPrint.goods_print).combobox('getValue');
    var templateId = $('#'+stockSalesPrint.goods_print_template).combobox('getValue');
    var contents = getGoodsTemplatesContents();
    var goodsDetail = getSortingOrderDetail();
    var batchData = getBatchData();
    //setPricePointNum('new_old_print_point_number',goodsDetail,price_configs);
    var isMulti = 0;
    var res = getSortingPrintData(contents,templateId,goodsDetail,batchData,isMulti);
    var datas = res[0];
    var printed_ids = res[1];
    newPrint(datas,printer,32,printed_ids,isMulti);
}
function getStockOutIds(rows){
    var ids = '';
    for (var i = 0; i < rows.length; ++i){
        ids = ids + rows[i].id + ',';
    }
    var ids_length = ids.length;
    var id_list = ids.substr(0,ids_length-1);
    return id_list;
}
function getPrintData(type,isMulti,print_dialog_type){
	 var printer = {},printed_ids = {},datas = {};
	 if(print_dialog_type == 'together'){
		printer[type] = $('#'+stockSalesPrint.goods_print).combobox('getValue');
		
		var goods_templateId = $('#'+stockSalesPrint.goods_print_template).combobox('getValue');
		var logistics_templateId = $('#'+stockSalesPrint.logistics_print_template).combobox('getValue');
		
		var goods_contents = getGoodsTemplatesContents();
		var logistics_contents = getLogisticsTemplatesContents();
		
		var goodsDetail = getGoodsGoodsDetail();
		setPricePointNum('new_old_print_point_number',goodsDetail,price_configs);
		var res = getTogetherPrintData(logistics_contents,logistics_templateId,goods_contents,goods_templateId,goodsDetail,isMulti);
		
		printed_ids[type] = res[1];
		datas[type] = res[0];

	}else if(type=='goods'){
		printer[type] = $('#'+stockSalesPrint.goods_print).combobox('getValue');
		var templateId = $('#'+stockSalesPrint.goods_print_template).combobox('getValue');
		var contents = getGoodsTemplatesContents();
		var goodsDetail = getGoodsGoodsDetail();
		setPricePointNum('new_old_print_point_number',goodsDetail,price_configs);
		var res = getGoodsPrintData(contents,templateId,goodsDetail,isMulti);
		printed_ids[type] = res[1];
		datas[type] = res[0];
	}else if(type=='logistics' || type=='multipleLogistics'){
		var contents = getLogisticsTemplatesContents();
		var templateSelectId = isMulti == 2?'stock_sales_print_logistics_templates':stockSalesPrint.logistics_print_template;
		var templateId = $('#'+templateSelectId).combobox('getValue');
		var content = JSON.parse(contents[templateId]);
		var templateURL = content.user_std_template_url;
		//getwaybill
		var goodsDetail = getLogisticsGoodsDetail();
		setPricePointNum('new_old_print_point_number',goodsDetail,price_configs);
		var res = getGoodsPrintData(contents,templateId,goodsDetail,isMulti);
		if(!$.isEmptyObject(templateURL)||$('input[name=stdTemplates]').val()!=""){
			var logisticsTemplateId = isMulti==2?'stock_sales_print_logistics_templates':stockSalesPrint.logistics_print_template;
			var oldTemplateId = $('#'+logisticsTemplateId).combobox('getData')[0].value;
			templateId = templateId == oldTemplateId?"":templateId;
			var contents = getLogisticsTemplatesContents();
			contents = JSON.parse(contents[$('#'+logisticsTemplateId).combobox('getValue')]);
			var templateURL = !!contents.user_std_template_url?contents.user_std_template_url:$('input[name=stdTemplates]').val();
			//var templateId = contents.user_std_template_id;//templateURL.substr(templateURL.indexOf('=')+1,templateURL.length);
			//templateURL = templateURL.substr(0,templateURL.indexOf('?'));
			var rows = $('#'+stockSalesPrint.params.id_list.datagrid).datagrid('getSelections');
			var stockout_ids = "";
			var package_counts = "";
			for (var i in rows){
				stockout_ids += rows[i].id + ",";
				package_counts += rows[i].package_count+",";
			}
			stockout_ids = stockout_ids.substr(0,stockout_ids.length-1);
			package_counts = package_counts.substr(0,package_counts.length-1);
			var logistics_id = rows[0].logistics_id;
			var detail = getLogisticsGoodsDetail();
			setPricePointNum('new_old_print_point_number',detail,price_configs);
			setRowPricePointNum(rows);
			var multiId = 0;
			var mul_printer = '';
			var packageCount = 0;   //用来区分
			if(isMulti == 1) {
				var mulRow = $('#' + sspMultiLogistics.params.datagrid.id).datagrid('getSelections')[0];
				multiId = mulRow.id;
				logistics_id = mulRow.logistics_id;
				mul_printer = $('#'+stockSalesPrint.logistics_print).combobox('getValue');
				var row = rows[0];
				var mulRow_data = $('#' + sspMultiLogistics.params.datagrid.id).datagrid('getSelections');
				//sspMultiLogistics.refresh($('#'+sspMultiLogistics.params.datagrid.id));
				var data = [];
				var printed_id = [];
				for (var k = 0; k < mulRow_data.length; ++k) {
					if (!$.isEmptyObject(row)) {
						//data = r.data.success[row.id];
						//var logistics_no_temp = getLogisticsNo(isMulti,data.logistics_no);
						var customArea_data = {};
						var waybill_info = composeCaiNiaoData(row, mulRow_data[k].logistics_no);
						waybill_info.cpCode = row.logistics_name;
						waybill_info.needEncrypt = false;
						waybill_info.parent = false;
						customArea_data = composeCustomData({}, row, mulRow_data[k].logistics_no, detail);

						customArea_data.waybill_info = waybill_info;
						customArea_data.sender = waybill_info.sender;
						data[k] = {
							'documentID': waybill_info.waybillCode,
							'contents': [{
								'templateURL': templateURL,
								'signature': 'MD:hVV4aUaGFnbvVkDwXEx4Ng==',
								'data': waybill_info
							}, {
								'templateURL': contents.custom_area_url,
								'data': customArea_data
							}]
						};
						printed_id.push({id: row.id, logistics_no: row.logistics_no});
					}
				}
				printer[type] = mul_printer;
				printed_ids[type] = printed_id;
				datas[type] = data;
			}
		}else{
			datas[type] = res[0];
			printed_ids[type] = res[1];
			printer[type] = $('#'+stockSalesPrint.logistics_print).combobox('getValue');
		}
	}
	return {printer:printer[type],printed_ids:printed_ids[type],datas:datas[type]};
}
function getTogetherPrintData(logistics_contents,logistics_templateID,goods_contents,goods_templateID,goodsDetail,isMulti){
	goods_contents = JSON.parse(goods_contents[goods_templateID]);
	var goods_templateURL = goods_contents.custom_area_url;
	logistics_contents = JSON.parse(logistics_contents[logistics_templateID]);
	var logistics_templateURL = logistics_contents.custom_area_url;
	var rows =  $('#stocksalesprint_datagrid').datagrid('getSelections');
	var datas = [],print_data,row;
	var printed_ids =[];
	setRowPricePointNum(rows);
	var k = 0;
	for (var j = 0; j < (rows.length); ++j){
		row = rows[j];
		var sf_app = parseSfAppKey(row,['寄方付','收方付','第三方付']);
		var package_data = getPackageCode(isMulti,row.logistics_no,row.package_count,row.id);
		print_data = composeNormalData(row,row.logistics_no,sf_app,package_data,goodsDetail);
		for(var f = 0;f<2;++f){
			templateURL = f?goods_templateURL:logistics_templateURL;
			datas[k] = {
				'documentID' : package_data[2],
				'contents' : [
				{
					'templateURL' : templateURL,                                         
					'data' : print_data,
				}
				]
			};
			printed_ids.push({id:rows[j].id,logistics_no:rows[j].logistics_no});
			k++;
		}	
	}
	
	return [datas,printed_ids];
}

function getGoodsPrintData(contents,templateID,goodsDetail,isMulti){
	contents = JSON.parse(contents[templateID]);
	var templateURL = contents.custom_area_url;
	var rows =  $('#stocksalesprint_datagrid').datagrid('getSelections');
	var datas = [],print_data,row;
	var printed_ids =[];
	setRowPricePointNum(rows);
	if(isMulti == 1){
		var mulRow = $('#'+sspMultiLogistics.params.datagrid.id).datagrid('getSelections');
		var multiLogisticsNo = '';
		for(var f = 0;f < mulRow.length;f++){
			row = rows[0];
            var sf_app = parseSfAppKey(row,['寄方付','收方付','第三方付']);
			var package_data = getPackageCode(isMulti,row.logistics_no,row.package_count,row.id,mulRow[f]);
            multiLogisticsNo = mulRow[f].logistics_no;
            // 顺丰热敏
            if(mulRow[f].logistics_type == 8){
                multiLogisticsNo = multiLogisticsNo.split('-')[0];
			}
			print_data = composeNormalData(row,row.logistics_no,sf_app,package_data,goodsDetail);
            print_data.trade.logistics_no = multiLogisticsNo;
            print_data.trade.child_logistics_no = '子单号：'+multiLogisticsNo;
			datas[f] = {
				'documentID' : package_data[2],
				'contents' : [
				{
					'templateURL' : templateURL,
					'data' : print_data,
				}
				]
			};
			printed_ids.push({id:row.id,logistics_no:row.logistics_no});
		}
	}else{
		for (var j = 0; j < rows.length; ++j){
			row = rows[j];
            var sf_app = parseSfAppKey(row,['寄方付','收方付','第三方付']);
			var package_data = getPackageCode(isMulti,row.logistics_no,row.package_count,row.id);
			print_data = composeNormalData(row,row.logistics_no,sf_app,package_data,goodsDetail);
			datas[j] = {
				'documentID' : package_data[2],
				'contents' : [
				{
					'templateURL' : templateURL,
					'data' : print_data,
				}
				]
			};
			printed_ids.push({id:rows[j].id,logistics_no:rows[j].logistics_no});
		}
	}
	return [datas,printed_ids];
}
function getSortingPrintData(contents,templateID,goodsDetail,batchInfo,isMulti){
    var rows =  $('#stocksalesprint_datagrid').datagrid('getSelections');
    contents = JSON.parse(contents[templateID]);
    var templateURL = contents.custom_area_url;
    var goods = goodsInfoToArray(goodsDetail[0]);
    var batch = {
        'batch_no':batchInfo.batch_no,
        'pick_list_no':batchInfo.pick_list_no,
        'order_num':batchInfo.order_num
	};
    var print_data = {
        'goods' : goods,
		'batch' :batch
    };
    var datas = [],printed_ids = [];
    datas[0] = {
        'documentID' : rows[0].id,
        'contents' : [
            {
                'templateURL' : templateURL,
                'data' : print_data
            }
        ]
    };
    for (var j = 0; j < rows.length; ++j){
        printed_ids.push({id:rows[j].id,logistics_no:rows[j].logistics_no});
    }
    return [datas,printed_ids];
}
function parseSfAppKey(row,payType){
    if(!((row.bill_type == 1)&&(row.logistics_type==8))){
        var app_key = {
            'pay_type':'',
            'dshk':'',
            'code_num':'',
            'cod_amount':'',
            'flag_e':'',
            'flag_cod':'',
            'ensure_amount':'',
            'type_sf':''
        };
        return app_key;
    }
    var app_key = JSON.parse(row.app_key);
    var waybill_info = {};
    var pay_time;
    if(row.waybill_info == null || row.waybill_info == ''){
        waybill_info = { insure_amount:'',cod_amount: ''}
    }else if($.isPlainObject(row.waybill_info)){
        waybill_info = { insure_amount:'',cod_amount: ''}
    }else{
        waybill_info = JSON.parse(row.waybill_info);
	}
	pay_time = (app_key.banded_type == '')?'现结':'月结';
    app_key.pay_type = payType[parseInt(app_key.pay_type)]+pay_time;
    app_key.dshk = '';
    app_key.code_num = '';
    app_key.cod_amount = '';
    app_key.flag_e = '';
    app_key.flag_cod = '';
    app_key.ensure_amount = waybill_info.insure_amount;
    switch(app_key.type_sf){
		case '0':
            app_key.type_sf = '标准快递';
            break;
        case '1':
            app_key.type_sf = '顺丰特惠';
            app_key.flag_e = 'E';
            break;
        case '7':
            app_key.type_sf = '顺丰次晨';
            break;
        case '6':
            app_key.type_sf = '顺丰即日';
            break;
        case '4':
            app_key.type_sf = '云仓专配次日';
            break;
        case '5':
            app_key.type_sf = '云仓专配隔日';
            app_key.flag_e = 'E';
            break;
        case '10':
            app_key.type_sf = '顺丰干配';
            break;
		default:
		    break;
	}
	// 代收货款（货到付款）
	if(row.delivery_term == 2){
        app_key.dshk = '代收货款';
        app_key.code_num = '卡号:'+app_key.banded_type;
        app_key.cod_amount = '￥'+waybill_info.cod_amount+'元';
        app_key.flag_cod = 'COD';
    }
    return app_key;
}
function newPrint(datas,printer,taskID,printed_ids,isMulti){
	connectStockWS();
    var requestID =  (new Date()).valueOf();
	var request = {
		'cmd' : 'print',
		'version' : '1.0',
		'requestID' : requestID,
		'task' : {
            'taskID' : requestID +''+isMulti+''+taskID,
			'printer' : printer,//'',
			'preview' : false,
			'notifyMode':'allInOne',
//			'adaptPageSize':true,
			'documents' : datas/*[{
				'documentID' : data.waybill_info.waybillCode,
				'contents' : [{
					'templateURL' : data.templateURL,
                    'signature' : data.signature,
					'data' : data.waybill_info
				},{}] 
			}
			]*/
		}
	};
	stockSalesPrint.print_list[request.task.taskID.toString()] = printed_ids;
	stockWs.send(JSON.stringify(request));
}

function newPreviewGoods(){
	var templateId = $('#'+stockSalesPrint.goods_print_template).combobox('getValue');
	if(templateId == ""){
		messager.alert("预览错误：没有选择模板，请到模板列表页面下载模板");
		$("#<?php echo ($id_list["dialog"]); ?>").dialog('close');
		return ;
	}
	var contents = getGoodsTemplatesContents();
	var goodsDetail = getGoodsGoodsDetail();
	setPricePointNum('new_old_print_point_number',goodsDetail,price_configs);
	var isMulti = 0;
	var datas = getGoodsPrintData(contents,templateId,goodsDetail,isMulti);
	newPreview(datas[0],211);
}
function previewSorting(){
    var templateId = $('#'+stockSalesPrint.goods_print_template).combobox('getValue');
    if(templateId == ""){
        messager.alert("预览错误：没有选择模板，请到模板列表页面下载模板");
        $("#<?php echo ($id_list["dialog"]); ?>").dialog('close');
        return ;
    }
    var contents = getGoodsTemplatesContents();
    var sortingDetail = getSortingOrderDetail();
    var batchData = getBatchData();
    //setPricePointNum('new_old_print_point_number',goodsDetail,price_configs);
    var isMulti = 0;
    var datas = getSortingPrintData(contents,templateId,sortingDetail,batchData,isMulti);
    newPreview(datas[0],211);
}
function newPreview(datas,taskID){
	connectStockWS();
	var requestID =  parseInt(1000*Math.random());
	var previewFormat = getPreviewFormat();
	if(is_print_setting){
		previewFormat = $('#preview_format').combobox('getValue');
	}
	var request = {
		'cmd' : 'print',
		'version' : '1.0',
		'requestID' : requestID,
		'task' : {
			'taskID' : requestID+''+taskID,
			'printer' : "",//'',
			'preview' : true,
			'previewType' : previewFormat,
			'documents' : datas
		}
	};
	stockWs.send(JSON.stringify(request));
}

function printerSetting(isMulti){
	stockSalesPrint.showDialog('printerSetting','打印设置',"<?php echo U('StockSalesPrint/printerSetting');?>",220,300,[{text:"确定",handler:function(){
		is_print_setting = true;
		var point_num = $('#new_old_print_point_number').combobox('getValue');
		var preview_format = $('#preview_format').combobox('getValue');
		var goods_order = $('#goods_show_order').combobox('getValue');
		$.post("<?php echo U('Stock/StockSalesPrint/printPointNum');?>",{point_num:point_num,preview_format:preview_format,goods_order:goods_order},function(r){},'json');
		$("#printerSetting").dialog('close');
		if(isMulti == 2){
            $('#' + sspMultiLogistics.params.add.id).dialog('close');
        }else{
            $('#'+stockSalesPrint.params.id_list.dialog).dialog('close');
        }
    }},{text:"取消",handler:function(){$("#printerSetting").dialog('close');}}]);
}
function writePackage() {
	var goodsDetail = getLogisticsGoodsDetail();
	var ids = '';
	var srcOrderNo = '';
	var packageCounts = '';
	var logistics_name = '';
	for(var key in goodsDetail){
		if(logistics_name==''){
			logistics_name = goodsDetail[key][0]['logistics_name'];
		}
		ids += key+',';
		srcOrderNo += goodsDetail[key][0]['src_order_no']+',';
		packageCounts += goodsDetail[key][0]['package_count']+',';
	}
	ids = ids.substr(0,ids.length-1);
	srcOrderNo = srcOrderNo.substr(0,srcOrderNo.length-1);
	packageCounts = packageCounts.substr(0,packageCounts.length-1);
	var buttons=[ {text:'确定',handler:function(){ submitPackage(); }}, {text:'取消',handler:function(){stockSalesPrint.cancelDialog('writePackage')}} ];
	stockSalesPrint.showDialog('writePackage','填写包裹数',"<?php echo U('StockSalesPrint/writePackages');?>"+"?ids="+ids+"&src="+srcOrderNo+"&pc="+packageCounts+"&logistics_name="+logistics_name,'400','600',buttons,null,false);
}
function getPackageList(param,success,error)
{
	var sel_rows = $('#'+stockSalesPrint.params.datagrid.id).datagrid('getSelections');
	$('#add_package_logistics_name').textbox('setText',sel_rows[0].logistics_name);
	var data = {};
	var rows = [];
	var index = 0;
	for (var i = 0; i < sel_rows.length; ++i){
		index=$('#'+stockSalesPrint.params.datagrid.id).datagrid('getRowIndex',sel_rows[i]);
		rows[i] = {src_order_no:sel_rows[i].src_order_no,shop_name:sel_rows[i].shop_name,
			receiver_name:sel_rows[i].receiver_name,logistics_no:sel_rows[i].logistics_no,logistics_id:sel_rows[i].logistics_id,bill_type:sel_rows[i].bill_type,
			id:sel_rows[i].id,index:index,package_count:sel_rows[i].package_count};
	}
	data.rows = rows;
	data.total = rows.length;
	success(data);
}
function submitPackage(){
    var packages = '',ids = '';
    var reg = /^\+?[1-9][0-9]*$/;　　//正整数
	var flag = 0;
    var rows = $('#' + stockSalesPrint.params.datagrid.id).datagrid('getSelections');
    var index = '';
	var package_rows = $('#add_package_datagrid').datagrid('getRows');
	for(var i=0; i<package_rows.length; ++i){
		ids += package_rows[i]['id'] + ',';
		if(!reg.test(package_rows[i]['package_count']) || parseInt(package_rows[i]['package_count']) > 10){
			flag = 1;
            messager.alert('包裹数要求是1-10的正整数');
            return;
		}
		packages += package_rows[i]['package_count'] + ',';
	}
	if(flag) return;
	ids = ids.substr(0,ids.length-1);
	packages = packages.substr(0,packages.length-1);
    $.post("/index.php/Stock/StockSalesPrint/updatePackageCount", {stockout_ids:ids, packages:packages}, function(r){
        if(r.status == 0){
            var packageCounts = r.data[0].split(',');
            for (var k = 0; k < rows.length; ++k) {
                index = $('#' + stockSalesPrint.params.datagrid.id).datagrid('getRowIndex', rows[k]);
                $('#'+stockSalesPrint.params.datagrid.id).datagrid('updateRow',{index:index,row:{package_count:packageCounts[k]}});
            }
        }else{
            messager.alert(r.msg);
        }
    });
    $('#writePackage').dialog('close');
}
function cainiaoPrintSetting(){
	connectStockWS();
	var request = {
		"cmd":"printerConfig",
		"requestID":"123458976",
		"version":"1.0",}
	stockWs.send(JSON.stringify(request));
}
function setPrinterConfig() {
    connectStockWS();
    var requestID =  parseInt(1000*Math.random());
    var printer = $('#printer_list').combobox('getValue');
    var paperW = $('#paper_width').textbox('getValue');
    var paperH = $('#paper_height').textbox('getValue');
	var w = parseInt(paperW);
	var h = parseInt(paperH);
    var reg = /^\+?[1-9][0-9]*$/;　　//正整数
	if(!reg.test(w) || !reg.test(h)){
        messager.alert('纸张尺寸不合法，请输入正整数');
        return false;
	}else if(h>300 || w>220){
	    messager.alert('纸张尺寸宽度不能超过220，高度不能超过300');
	    return false;
	}
    var request = {
        "cmd":"setPrinterConfig",
        "requestID":requestID,
        "version":"1.0",
        "printer":
        {
            "name":printer,
            "needTopLogo":true,
            "needBottomLogo":false,
            "horizontalOffset":0,
            "verticalOffset":0,
//			"apaptPageSize":true,
            "forceNoPageMargins":false,// v0.2.8.3 新增字段
            "paperSize":{"width":w, "height":h}
        }
    }
    stockWs.send(JSON.stringify(request));
}
function getPrinterConfig(){
    connectStockWS();
    var requestID =  parseInt(1000*Math.random());
    var printer = '';
    if($("#"+stockSalesPrint.logistics_print).length == 1){
        printer = $("#"+stockSalesPrint.logistics_print).combobox('getValue');
	}else if($("#"+stockSalesPrint.goods_print).length == 1){
        printer = $("#"+stockSalesPrint.goods_print).combobox('getValue');
    }else if($("#printer_list").length == 1){
        printer = $('#printer_list').combobox('getValue');
	}
    var request = {
        "cmd":"getPrinterConfig",
        "printer":printer,
        "version":"1.0",
        "requestID":requestID
    }
    stockWs.send(JSON.stringify(request));
}
function setPrinter(){
    stockSalesPrint.showDialog('setPrinter','打印机纸张大小设置',"<?php echo U('StockSalesPrint/setPrinter');?>",220,300,[{text:"确定",handler:function(){
        if(setPrinterConfig())
        	$("#setPrinter").dialog('close');
    }},{text:"取消",handler:function(){$("#setPrinter").dialog('close');}}]);
}
function onPrinterSelect(printer,type){
	if(type == "goods")
	{
		var templateId = $('#'+stockSalesPrint.goods_print_template).combobox('getValue');
		var contents = getGoodsTemplatesContents();
	}else if(type == "logistics"){
		var templateId = $('#'+stockSalesPrint.logistics_print_template).combobox('getValue');
		var contents = getLogisticsTemplatesContents();
	}else if(type == "multiLogistics"){
        var templateId = $('#stock_sales_print_logistics_templates').combobox('getValue');
        var contents = getLogisticsTemplatesContents();
    }
	var content = contents[templateId];
	if(content.defaultPrinter != undefined && content.default_printer == printer)
		return;
	else
	messager.confirm("您确定把\""+printer+"\"设置为此模板的默认打印机么？",function(r){
		if(r){
            getPrinterConfig();
			newSetDefaultPrinter(content,printer,templateId);
		}
	});
}

function newSetDefaultPrinter(content,printor,templateId){
	content = JSON.parse(content);
	content.default_printer = printor;
	Post("<?php echo U('Stock/StockSalesPrint/setDefaultPrinter');?>",{content:JSON.stringify(content),templateId:templateId},function(ret){
		if(1 == ret.status){
			messager.alert(ret.msg);
		}else {
            var operator_id = stockSalesPrint.params.operator_id.toString();

			if(content.operator_to_printer == undefined){
				content.operator_to_printer={
                    operator_id : printor
				};
			}else{
				content.operator_to_printer[operator_id] = printor
			}
			if(undefined != stockSalesPrint.goods_contents){
				stockSalesPrint.goods_contents[templateId] = JSON.stringify(content);
			}else if(undefined != stockSalesPrint.logistics_contents){
				stockSalesPrint.logistics_contents[templateId] = JSON.stringify(content);
			}
		}
	});
}

function newTemplateOnSelect(type){
    var operator_id= stockSalesPrint.params.operator_id;
	if(type == "goods"){
		if($('#'+stockSalesPrint.goods_print_template).combobox('getData').length == 0)
		{
			return;
		}
		var content = JSON.parse(stockSalesPrint.goods_contents[$('#'+stockSalesPrint.goods_print_template).combobox('getValue')]);
		if(undefined != content.default_printer && isExistInObj($('#'+stockSalesPrint.goods_print).combobox('getData'),content.default_printer)){
			if(content.operator_to_printer != undefined){
			    if(content.operator_to_printer[operator_id] != undefined){
                    $('#'+stockSalesPrint.goods_print).combobox('setValue',content.operator_to_printer[operator_id]);
                }else{
                    $('#'+stockSalesPrint.goods_print).combobox('setValue',content.default_printer);
                }
            }else{
                $('#'+stockSalesPrint.goods_print).combobox('setValue',content.default_printer);
            }

		}
	}else if(type == "logistics"){
		if($('#'+stockSalesPrint.logistics_print_template).combobox('getData').length == 0)
			return ;
		var content = JSON.parse(stockSalesPrint.logistics_contents[$('#'+stockSalesPrint.logistics_print_template).combobox('getValue')]);
        if(content.default_printer != undefined && isExistInObj($('#'+stockSalesPrint.logistics_print).combobox('getData'),content.default_printer))
            if(content.operator_to_printer != undefined){
                if(content.operator_to_printer[operator_id] != undefined){
                    $('#'+stockSalesPrint.logistics_print).combobox('setValue',content.operator_to_printer[operator_id]);
                }else{
                    $('#'+stockSalesPrint.logistics_print).combobox('setValue',content.default_printer);
                }
            }else{
                $('#'+stockSalesPrint.logistics_print).combobox('setValue',content.default_printer);
            }
	} else if(type == "multiLogistics"){
        if($('#stock_sales_print_logistics_templates').combobox('getData').length == 0)
            return ;
        var content = JSON.parse(stockSalesPrint.logistics_contents[$('#stock_sales_print_logistics_templates').combobox('getValue')]);
        if(content.default_printer != undefined && isExistInObj($('#stockSalesPrint_logistics_printer_lists').combobox('getData'),content.default_printer))
            if(content.operator_to_printer != undefined){
                if(content.operator_to_printer[operator_id] != undefined){
                    $('#stockSalesPrint_logistics_printer_lists').combobox('setValue',content.operator_to_printer[operator_id]);
                }else{
                    $('#stockSalesPrint_logistics_printer_lists').combobox('setValue',content.default_printer);
                }
            }else{
                $('#stockSalesPrint_logistics_printer_lists').combobox('setValue',content.default_printer);
            }
    }
}

function changeTemplatePage(){
	open_menu('打印模板','<?php echo U("Setting/NewPrintTemplate/getNewPrintTemplate");?>');
	$('#stocksalesprint_dialog').dialog('close');
}

function newSelectPrinter(type,isMulti){
	type = type == "goods"?"99":"88";
    isMulti = isMulti==2?2:0;
	var request = { 
	    "cmd":"getPrinters",
		"requestID":"123458976"+isMulti+type,
	   	"version":"1.0",
	   	/*"cmd":"getPrinters", //请求打印机列表命令
    	"requestID":"123458976",
    	"version":"1.0",*/
	}
	stockWs.send(JSON.stringify(request));
}

function newPrintLogisticsDialog(isMulti) {
    var rows = $('#' + stockSalesPrint.params.datagrid.id).datagrid('getSelections');
    if (!checkBeforeDialogShow(rows, isMulti)) return;
    //rows[i].logistics_id 选中订单的物流公司的id，可以选择多个订单，当物流公司不同时弹出提示信息
    var logisticsId = rows[0].logistics_id;
    var logisticsType = rows[0].logistics_type;
    for (var i = 1; i < rows.length; ++i) {
        if (rows[i].logistics_id != logisticsId) {
            messager.alert('物流公司不一致,请重新选择');
            return;
        }
        // 批量打印多物流时需检查是否菜鸟多物流
        if(isMulti == 3){
            if (rows[i].bill_type != 2) {
                messager.alert('只有菜鸟物流支持批量打印多物流');
                return;
            }
		}
    }
    connectStockWS();
    var ids = "";
    for(var i in rows){
        ids += rows[i].id + ",";
    }
    ids = ids.substr(0,ids.length-1);
    if (isMulti == 1) {
        var mulRows = $('#' + sspMultiLogistics.params.datagrid.id).datagrid('getSelections');
        var logisticId = mulRows[0].logistics_id;
        stockSalesPrint.showDialog('<?php echo ($id_list["dialog"]); ?>','打印物流单',"<?php echo U('WayBill/getTemplates');?>"+"?logisticsId="+logisticId+"&ids="+ids+"&isMulti=1",200,323,[{text:'取消',handler:function(){$("#stocksalesprint_dialog").dialog('close');}}]);
    }else if(isMulti ==3){
        stockSalesPrint.showDialog('<?php echo ($id_list["dialog"]); ?>','批量打印多物流单',"<?php echo U('WayBill/getTemplates');?>"+"?logisticsId="+logisticsId+"&ids="+ids+"&isMulti=3",220,323,[{text:'取消',handler:function(){$("#stocksalesprint_dialog").dialog('close');}}]);
    }
    else{
        stockSalesPrint.showDialog('<?php echo ($id_list["dialog"]); ?>','打印物流单',"<?php echo U('WayBill/getTemplates');?>"+"?logisticsId="+logisticsId+"&ids="+ids+"&isMulti=0",200,323,[{text:'取消',handler:function(){$("#stocksalesprint_dialog").dialog('close');}}]);
    }
}

function composeCaiNiaoData(row,logisticsNo) {
    var address = row.receiver_area.split(" ");
    var data = {
        "recipient": {
            "address": {
                "city": address[1],
                "detail": row.receiver_address,
                "district": (area.getDistrict($.trim(address[0]),$.trim(address[1])).length == 1)?'':address[2],
                "province": address[0],
                "town": row.receiver_town
            },
            "mobile": !(row.receiver_mobile=="")?row.receiver_mobile:row.receiver_telno,
            "name": row.receiver_name,
            "phone": !(row.receiver_telno=="")?row.receiver_telno:row.receiver_mobile
        },
        "routingInfo": {
            "consolidation": {
                "name": row.warehouse_name,
                "code": row.warehouse_id
            },
            "origin": {
                "code": row.logistics_name
            },
            "sortation": {
                "name": ""
            },
            "routeCode": ""
        },
        "sender": {
            "address": {
                "city": row.city,
                "detail": row.address,
                "district": row.district,
                "province": row.province,
                "town": row.town
            },
            "mobile": row.mobile,
            "name": row.contact,
            "phone": row.telno
        },
        "shippingOption": {
        },
        waybillCode: logisticsNo
    };
    return data;
}
function composeCustomData(data,row,logistics_no_temp,detail){
    var customArea_data = data;
    customArea_data.trade = {
        'trade_no' : row.src_order_no,//订单标号
        'src_tids' : row.src_tids,//原始单号
        'logistics_no' : logistics_no_temp,
        'package_code' : logistics_no_temp+"-1-1-",
        'package_id' : row.id,
        'goods_amount' : row.goods_total_cost,
        'cargo_total_weight' : row.weight,
        'calc_weight' : row.calc_weight,
        'receivable' : row.receivable,
        'goods_count' : row.goods_count,
        'goods_type_count' : row.goods_type_count,
        'print_date' : (new Date()).getFullYear()+"-"+((new Date()).getMonth()+1)+"-"+(new Date()).getDate(),//(new Date()).toLocaleString().replace(/\//g,"-").substring(0,9),
        'cs_remark' : row.cs_remark,
        'print_remark' : row.print_remark,
        'buyer_nick' : row.buyer_nick,
        'buyer_message' : row.buyer_message,
        'invoice_content' : row.invoice_content,
        'cod_amount' : row.cod_amount,
        'receiver_dtb' : row.receiver_dtb,
        'invoice_title' : row.invoice_title,
        'trade_time' : row.pay_time,
        'post_amount' : row.post_amount
    };
    customArea_data.shop = {
        'name' : row.shop_name,
        'website' : row.website
    };
	customArea_data.recipient = {
        'name' : row.receiver_name,
        'mobile': !(row.receiver_mobile=="")?row.receiver_mobile:row.receiver_telno,
        'phone': !(row.receiver_telno=="")?row.receiver_telno:row.receiver_mobile,
	};
    customArea_data.sender = {
        "address": {
            "city": row.city,
            "detail": row.address,
            "district": row.district,
            "province": row.province,
            "town": row.town,
			'address':row.address,
        },
        "mobile": row.mobile,
        "name": row.contact,
        "phone": row.telno
	};
    customArea_data.goods = goodsInfoToArray(detail[row.id]);
    return customArea_data;
}
function composeNormalData(row,logistics_no_temp,sf_app,package_data,goodsDetail){
    var print_data,sender,recipient,goods,shop,trade,jos,address = [];
    address = row.receiver_area.split(' ');
    sender = {
        "address": {
            "city": row.city,
            "detail": row.address,
            "district": row.district,
            "province": row.province,
            "town": row.town
        },
        "mobile": row.mobile,
        "name": row.contact,
        "phone": row.telno
    };
    recipient = {
        "address": {
            "city": address[1],//row.receiver_city,
            "detail": row.receiver_address,
            "district": (area.getDistrict($.trim(address[0]),$.trim(address[1])).length == 1)?'':address[2],//row.receiver_district,
            "province": address[0],//row.receiver_province,
            "town": row.receiver_town
        },
        "mobile": !(row.receiver_mobile=="")?row.receiver_mobile:row.receiver_telno,
        "name": row.receiver_name,
        "phone": !(row.receiver_telno=="")?row.receiver_telno:row.receiver_mobile,
        "destination_code":row.receiver_dtb
    };
    trade = {
        'trade_no' : row.src_order_no,//订单标号
        'src_tids' : row.src_tids,//原始单号
        'logistics_no' :logistics_no_temp,
        'parent_logistics_no' :'母单号：'+logistics_no_temp,
        'package_code' : package_data[0],
        'package_id' : row.id,
        'goods_amount' : row.goods_total_cost,
        'receivable' : row.receivable,
        'goods_count' : row.goods_count,
        'goods_type_count' : row.goods_type_count,
        'print_date' : (new Date()).getFullYear()+"-"+((new Date()).getMonth()+1)+"-"+(new Date()).getDate(),//(new Date()).toLocaleString().replace(/\//g,"-").substring(0,9),
        'cs_remark' : row.cs_remark,
        'print_remark' : row.print_remark,
        'buyer_nick' : row.buyer_nick,
        'buyer_message' : row.buyer_message,
        'invoice_content' : row.invoice_content,
        'cod_amount' : row.cod_amount,
        'receiver_dtb' : row.receiver_dtb,
        'invoice_title' : row.invoice_title,
        'trade_time' : row.pay_time,
        'cargo_total_weight' : row.weight,
        'calc_weight' : row.calc_weight,
        'post_amount' : row.post_amount,
        'pay_type' : sf_app.pay_type,
        'month_card' : sf_app.banded_type,
        'service_type' : sf_app.type_sf,
        'dshk' : sf_app.dshk,
        'code_num' : sf_app.code_num,
        'sf_cod_amount' : sf_app.cod_amount,
        'ensure_amount' : sf_app.ensure_amount,
        'flag_e' : sf_app.flag_e,
        'flag_cod' : sf_app.flag_cod
    };
    shop = {
        'name' : row.shop_name,
        'website' : row.website
    };
    jos = {
        'package_seq': package_data[1]
    };
    goods = goodsInfoToArray(goodsDetail[row.id]);
    print_data = {
        'sender' : sender,
        'recipient' : recipient,
        'trade' : trade,
        'shop' : shop,
        'goods' : goods,
        'jos':jos
    };
    return print_data;
}
function getRowsIds(ret,print_type,isMulti,is_continue){
	var ids = '';
	var multiIds = '';
	if(!$.isEmptyObject(ret) || ret != ''){
		var status = ret.printStatus;
		var type = ret.taskID;
		//isMulti = 0;  // 判断是否多物流单号
		if(type != undefined){
			isMulti = type.substr(type.length-3,1);
			type = type.substr(type.length-2,type.length);
		}
		if(type==22) {
			print_type = "logistics";
		}else if(type==12){
			print_type = "goods";
		}else if(type==32){
            print_type = "sorting";
        }
	}
	var rows = $('#'+stockSalesPrint.params.datagrid.id).datagrid('getSelections');
	for (var i = 0; i < rows.length; ++i){
		if(print_type == 'goods' && rows[i]['sendbill_print_status'] == 1 || print_type == 'logistics' && rows[i]['logistics_print_status'] == 1 || print_type == 'sorting' && rows[i]['picklist_print_status'] == 1){
			continue;
		}
		ids = ids + rows[i].id + ',';
	}
	var ids_length = ids.length;
	var id_list = ids.substr(0,ids_length-1);
	var value = 0;
	var multiId_list = '';
	if(isMulti == 1){
		var mulRows = $('#' + sspMultiLogistics.params.datagrid.id).datagrid('getSelections');
		for(var j = 0; j < mulRows.length; ++j){
			if(mulRows[j]['print_status'] == 1){
				continue;
			}
			multiIds = multiIds + mulRows[j].id + ',';
		}
		var multiIds_length = multiIds.length;
		multiId_list = multiIds.substr(0,multiIds_length-1);
		value = 2;
		print_type = 'multipleLogistics';
	}
	if(is_continue == 'continue'){value = 3;}
	return {id_list:id_list,multiId_list:multiId_list,isMulti:isMulti,print_type:print_type,value:value};
}
function getWaybill(templateId,isMulti,print_dialog_type){
    var logisticsTemplateId = isMulti==2?'stock_sales_print_logistics_templates':stockSalesPrint.logistics_print_template;
	var oldTemplateId = $('#'+logisticsTemplateId).combobox('getData')[0].value;
	templateId = templateId == oldTemplateId?"":templateId;
	var contents = getLogisticsTemplatesContents();
	contents = JSON.parse(contents[$('#'+logisticsTemplateId).combobox('getValue')]);
	var templateURL = !!contents.user_std_template_url?contents.user_std_template_url:$('input[name=stdTemplates]').val();
	//var templateId = contents.user_std_template_id;//templateURL.substr(templateURL.indexOf('=')+1,templateURL.length);
	//templateURL = templateURL.substr(0,templateURL.indexOf('?'));
	var rows = $('#'+stockSalesPrint.params.datagrid.id).datagrid('getSelections');
	var stockout_ids = "";
	var package_counts = "";
	for (var i in rows){
		stockout_ids += rows[i].id + ",";
	}
	stockout_ids = stockout_ids.substr(0,stockout_ids.length-1);
	var logistics_id = rows[0].logistics_id;
	var detail = getLogisticsGoodsDetail();
	setPricePointNum('new_old_print_point_number',detail,price_configs);
	setRowPricePointNum(rows);
    var multiId = 0;
    var printer = '';
    var packageCount = 0;   //用来区分
    if(isMulti == 2){
        logistics_id = $('#logisticsSelect').combobox('getValue');
        printer = $('#stockSalesPrint_logistics_printer_lists').combobox('getValue');
        packageCount = $('#CountInput').val(); // 已经校验过
    } else{
        printer = $('#'+stockSalesPrint.logistics_print).combobox('getValue');
	}
	if($('#add_package_datagrid').length>0){
		var package_rows = $('#add_package_datagrid').datagrid('getRows');
		for(var i=0; i<package_rows.length; ++i){
			package_counts += package_rows[i]['package_count'] + ',';
		}
	}else{
		for(var i=0; i<rows.length; ++i){package_counts += '1,';}
	}
	package_counts = package_counts.substr(0,package_counts.length-1);
	var parameter = {logistics_id:logistics_id,stockout_ids:stockout_ids,templateURL:templateURL,templateId:templateId,oldTemplateId:oldTemplateId,packageCount:packageCount};
	newGetWayBillPost(parameter,package_counts,isMulti,rows,contents,detail,stockout_ids,logistics_id,printer,print_dialog_type);
}
function newGetWayBillPost(parameter,package_counts,isMulti,rows,contents,detail,stockout_ids,logistics_id,printer,print_dialog_type){
	var multiLogis,multiParameter;
	if(isMulti == 3){isMulti = 0;multiLogis = 3;multiParameter = parameter;}
	Post("/index.php/Stock/WayBill/newGetWayBill",parameter, function(r){
		if(0 == r.status || 2==r.status){
			$('#'+stockSalesPrint.printing).linkbutton({text:'打印中...',disabled:true});
			var index;
			var datas = [] ,data ,customArea_data;
			var logistics_nos = '';
			var packageNosArr = package_counts.split(',');
			var rowData = {};
			var f=0;
			if (!$.isEmptyObject(r.data.success)){
				var printed_ids = [];
				if(isMulti == 0){				
					for (var j = 0; j < rows.length; ++j){
						if (!$.isEmptyObject(r.data.success[rows[j].id])){
							if(isMulti !=1) {
								index = $('#' + stockSalesPrint.params.datagrid.id).datagrid('getRowIndex', rows[j]);
								$('#' + stockSalesPrint.params.datagrid.id).datagrid('updateRow', {
									index: index,
									row: {
										index: index,
										logistics_no: r.data.success[rows[j].id].logistics_no,
										waybill_info: r.data.success[rows[j].id].waybill_info
									}
								});
							}
							data = r.data.success[rows[j].id];
							if(print_dialog_type == 'together'){
								var goods_printer = $('#'+stockSalesPrint.goods_print).combobox('getValue');
								if(goods_printer == printer){
									for(k=0;k<2;++k){
										if(k==0){
											set_print_data(f,j,data,contents,rows,isMulti,detail,logistics_nos,datas,printed_ids);
										}else{
											set_goodsPrint_data(f,j,rows,isMulti,datas,printed_ids)
										}
										f++;
									}
								}else{
									set_print_data(j,j,data,contents,rows,isMulti,detail,logistics_nos,datas,printed_ids);
								}	
							}else{
								set_print_data(j,j,data,contents,rows,isMulti,detail,logistics_nos,datas,printed_ids);
							}	
						}
					}
				}else{
					var pid = 0;
					for(var j = 0; j < rows.length; ++j){
						rowData = r.data.success[rows[j].id];
						for (var k in rowData){
							k = parseInt(k);
							if (!$.isEmptyObject(rowData[k])){
								data =rowData[k];
								set_print_data(pid,j,data,contents,rows,isMulti,detail,logistics_nos,datas,printed_ids);
							}
							pid++;
						}
					}
					if(isMulti == 2){
						logistics_nos = logistics_nos.substr(0,logistics_nos.length-1);
						$.post('<?php echo U("Stock/StockSalesPrint/saveWeight");?>',{weights:sspMultiLogistics.weights,logisticsno:logistics_nos,stockout_ids:stockout_ids,logistics_id:logistics_id},function(res){
							if(res.status == 1){
								messager.alert('重量保存失败');
							}

						});
					}
				}
				var temp_datas = datas;
				if(multiLogis == 3){
					isMulti = 3;
					multiParameter.packageCount = package_counts;
					Post("/index.php/Stock/WayBill/newGetWayBill",multiParameter, function(r){
						if(0 == r.status || 2==r.status){
							$('#'+stockSalesPrint.printing).linkbutton({text:'打印中...',disabled:true});
							var index;
							var datas = [] ,data ,customArea_data;
							var logistics_nos = '';
							var packageNosArr = package_counts.split(',');
							var rowData = {};
							if (!$.isEmptyObject(r.data.success)){
								var printed_ids = [];
								var pid = 0;
								for(var j = 0; j < rows.length; ++j){
									rowData = r.data.success[rows[j].id];
									for (var k in rowData){
										k = parseInt(k);
										if (!$.isEmptyObject(rowData[k])){
											data =rowData[k];
											set_print_data(pid,j,data,contents,rows,isMulti,detail,logistics_nos,datas,printed_ids);
										}
										pid++;
									}
								}
								$all_datas = temp_datas.concat(datas);
								newPrint($all_datas,printer,22,printed_ids,isMulti);
							}
							if (r.data.fail.length > 0){
								var ids = '';
								var print_type = "logistics";
								for (var i = 0; i < rows.length; ++i){
									if(print_type == 'logistics' && rows[i]['logistics_print_status'] == 1){
										continue;
									}
									ids = ids + rows[i].id + ',';
								}
								var ids_length = ids.length;
								var id_list = ids.substr(0,ids_length-1);
								var parameter = {stockout_ids:id_list,print_type:print_type,is_print:1,value:0};
								$.messager.progress('close');
								$('#'+stockSalesPrint.printing).linkbutton({text:'打印',disabled:false});
								$.fn.richDialog("response", r.data.fail, "stockout");
							}
						}else{
							$.messager.progress('close');
							$('#'+stockSalesPrint.printing).linkbutton({text:'打印',disabled:false});
							messager.alert(r.msg);
						}
					});
				}else{
					if(print_dialog_type == 'together' && goods_printer != printer){
						printGoodsLogisticsMultiple('goods',isMulti,'');
					}else if(print_dialog_type == 'logAndPick'){
					    printPickList();
					}
					newPrint(datas,printer,22,printed_ids,isMulti);
				}
			}
			if (r.data.fail.length > 0){
				if(isMulti == 0){
					var ids = '';
					var print_type = "logistics";
					for (var i = 0; i < rows.length; ++i){
						if(print_type == 'logistics' && rows[i]['logistics_print_status'] == 1){
							continue;
						}
						ids = ids + rows[i].id + ',';
					}
					var ids_length = ids.length;
					var id_list = ids.substr(0,ids_length-1);
					var parameter = {stockout_ids:id_list,print_type:print_type,is_print:1,value:0};
				}
				$.messager.progress('close');
				$('#'+stockSalesPrint.printing).linkbutton({text:'打印',disabled:false});
				$.fn.richDialog("response", r.data.fail, "stockout");
			}
			if(isMulti == 0){
				if(r.data.success.length>0){
					$.messager.progress('close');
					$('#'+stockSalesPrint.printing).linkbutton({text:'打印',disabled:false});
					$('#<?php echo ($id_list["dialog"]); ?>').dialog('close');
					$('#'+stockSalesPrint.params.id_list.datagrid).datagrid('reload');
				}
			}
		}else{
			$.messager.progress('close');
			$('#'+stockSalesPrint.printing).linkbutton({text:'打印',disabled:false});
			messager.alert(r.msg);
		}
	});
}
function set_print_data(pid,j,data,contents,rows,isMulti,detail,logistics_nos,datas,printed_ids){
	var logistics_no_temp = getLogisticsNo(isMulti,data.logistics_no);
	customArea_data = composeCustomData(data,rows[j],logistics_no_temp,detail);
	if(isMulti != 0){	
		logistics_nos += logistics_no_temp+',';
		data.waybill_info.sender = {
			"address": {
				"city": rows[j].city,
				"detail": rows[j].address,
				"district": rows[j].district,
				"province": rows[j].province,
				"town": rows[j].town
			},
			"mobile": rows[j].mobile,
			"name": rows[j].contact,
			"phone": rows[j].telno
		};
	}
	datas[pid] = {
		'documentID' : data.waybill_info.waybillCode,
		'contents' : [{
		'templateURL' : data.templateURL,
		'signature' : data.signature,
		'data' : data.waybill_info
		},{
		'templateURL' : contents.custom_area_url,
		'data' : customArea_data
		}] 
	};
	printed_ids.push({id:rows[j].id,logistics_no:rows[j].logistics_no});
}

function set_goodsPrint_data(pid,j,rows,isMulti,datas,printed_ids){

	var templateId = $('#'+stockSalesPrint.goods_print_template).combobox('getValue');
	var contents = getGoodsTemplatesContents();
	var goodsDetail = getGoodsGoodsDetail();
	setPricePointNum('new_old_print_point_number',goodsDetail,price_configs);
	contents = JSON.parse(contents[templateId]);
	var templateURL = contents.custom_area_url;
	var print_data,row;
	setRowPricePointNum(rows);
	row = rows[j];
	var sf_app = parseSfAppKey(row,['寄方付','收方付','第三方付']);
	var package_data = getPackageCode(isMulti,row.logistics_no,row.package_count,row.id);
	print_data = composeNormalData(row,row.logistics_no,sf_app,package_data,goodsDetail);
	datas[pid] = {
		'documentID' : package_data[2],
		'contents' : [
		{
			'templateURL' : templateURL,
			'data' : print_data,
		}
		]
	};
	printed_ids.push({id:rows[j].id,logistics_no:rows[j].logistics_no});
	
}

function newPreviewLogistics(isMulti){
    var contents = getLogisticsTemplatesContents();
    var templateSelectId = isMulti == 2?'stock_sales_print_logistics_templates':stockSalesPrint.logistics_print_template;
    var templateId = $('#'+templateSelectId).combobox('getValue');
    if(templateId == ""){
        messager.alert("预览错误：没有选择模板，请到模板列表页面下载模板");
        $("#<?php echo ($id_list["dialog"]); ?>").dialog('close');
        return ;
    }
    var content = JSON.parse(contents[templateId]);
    var templateURL = content.user_std_template_url;
    if(!$.isEmptyObject(templateURL)|| ($('input[name=stdTemplates]').val()!="" && $('input[name=stdTemplates]').val()!="jos"))
        previewWaybillLogistics(content,isMulti);
    else
        previewNormalLogistics(contents,templateId,isMulti);
}
function getLogisticsNo(isMulti,logisticsNo) {
    var logistics_no = '';
    if(isMulti ===1){
        var mulRow = $('#'+sspMultiLogistics.params.datagrid.id).datagrid('getSelections')[0];
        logistics_no = mulRow.logistics_no;
	}else{
        logistics_no = logisticsNo;
	}
    return logistics_no;
}
function getPackageCode(isMulti,logisticsNo,packageCount,documentId,row) {
    var package_code = '';
    var package_seq = '';
    var arr = new Array();
    if(isMulti ==1){
        //var mulRow = $('#'+sspMultiLogistics.params.datagrid.id).datagrid('getSelections')[0];
		var mulRow = row;
        package_code = mulRow.logistics_no;
        if(package_code.indexOf('-') != -1){
            var strArr = package_code.split('-');
            var seq = strArr.slice(1,3);
            arr[0] = package_code;
            arr[1] = seq.join('/');
		}else{
            arr[0] = package_code+'-1-1-';
            arr[1] = '1/1';
		}
		arr[2] = mulRow.id;
        return arr;
    }else{
        var pc = packageCount==0?1:packageCount;
        package_code = logisticsNo+'-1-'+pc+'-';
        arr[0] = package_code;
        arr[1] = '1/'+pc;
        arr[2] = documentId;
        return arr;
    }
}

function previewWaybillLogistics(content,isMulti){
	var waybillURL = $('input[name=stdTemplates]').val(); 
	if(!!content.user_std_template_url){
		waybillURL = content.user_std_template_url;
	}
	var rows =  $('#stocksalesprint_datagrid').datagrid('getSelections');
	var datas = [],customarea_Data,row,data,logistics_no_temp;
    //var logistics_no_temp = getLogisticsNo(isMulti,row.logistics_no);
    var printTaskId = parseInt(1000*Math.random());
	//var address = row.receiver_area.split(" ");
	var goodsDetail = getLogisticsGoodsDetail();
	setPricePointNum('new_old_print_point_number',goodsDetail,price_configs);
	setRowPricePointNum(rows);
	if(isMulti == 1){
		var mulRow = $('#'+sspMultiLogistics.params.datagrid.id).datagrid('getSelections');
		for(var k = 0; k < mulRow.length; ++k){
			row = rows[0];
			data = composeCaiNiaoData(row,mulRow[k].logistics_no);
			customarea_Data = composeCustomData({},row,mulRow[k].logistics_no,goodsDetail);
			datas[k] = {
				'documentID' : ""+printTaskId,
				'contents' : [{
					"templateURL" : waybillURL,
					"data" : data
				},{
					"templateURL" : content.custom_area_url,
					"data" : customarea_Data
				}
				]
			};
		}
	}else if(isMulti == 3){
	    var previewPackageNo = '';
	    var previewCount = 0;
        for(var m = 0; m < rows.length; ++m){
            row = rows[m];
            previewPackageNo = row.package_count;
            logistics_no_temp = '';
            for(var n=0;n<parseInt(previewPackageNo);n++){
                data = composeCaiNiaoData(row,logistics_no_temp);
                customarea_Data = composeCustomData({},row,logistics_no_temp,goodsDetail);
                datas[previewCount] = {
                    'documentID' : ""+printTaskId,
                    'contents' : [{
                        "templateURL" : waybillURL,
                        "data" : data
                    },{
                        "templateURL" : content.custom_area_url,
                        "data" : customarea_Data
                    }
                    ]
                };
                previewCount ++;
			}
        }
	}else{
		for(var j = 0; j < rows.length; ++j){
			row = rows[j];
			logistics_no_temp = getLogisticsNo(isMulti,row.logistics_no);
			if(isMulti ==2){ logistics_no_temp = ''; }
			data = composeCaiNiaoData(row,logistics_no_temp);
			customarea_Data = composeCustomData({},row,logistics_no_temp,goodsDetail);
			datas[j] = {
				'documentID' : ""+printTaskId,
				'contents' : [{
					"templateURL" : waybillURL,
					"data" : data
				},{
					"templateURL" : content.custom_area_url,
					"data" : customarea_Data
				}
				]
			};
		}
	}
	newPreview(datas,221);
}

function previewNormalLogistics(contents,templateId,isMulti){
	var goodsDetail = getLogisticsGoodsDetail();
	setPricePointNum('new_old_print_point_number',goodsDetail,price_configs);
	var datas = getGoodsPrintData(contents,templateId,goodsDetail,isMulti);
	newPreview(datas[0],221);
}

function block_order(type,number){
	var rows = $('#'+stockSalesPrint.params.datagrid.id).datagrid('getSelections');
	var selects_info = {};
	var resultBeforeCheck = [];
	for(var k in rows){
		var temp_result = {'stock_id':rows[k]['id'],'stock_no':rows[k]['stockout_no']};
		if(rows[k].block_reason!= 0 && !(parseInt(rows[k].block_reason) & 4096) ){
			var msg = '';
			var solve_way = '';
			switch(parseInt(rows[k].block_reason)){
					case 1 :
						msg = '出库单已经被拦截，拦截原因:申请退款';
						break;
					case 2 :
						msg = '出库单已经被拦截，拦截原因:已退款';
						break;
					case 4 :
						msg = '出库单已经被拦截，拦截原因:地址被修改';
						break;
					case 8 :
						msg = '出库单已经被拦截，拦截原因:发票被修改';
						break;
					case 16 :
						msg = '出库单已经被拦截，拦截原因:物流被修改';
						break;
					case 32 :
						msg = '出库单已经被拦截，拦截原因:仓库变化';
						break;
					case 64 :
						msg = '出库单已经被拦截，拦截原因:备注修改';
						break;
					case 128 :
						msg = '出库单已经被拦截，拦截原因:更换货品';
						break;
					case 256 :
						msg = '出库单已经被拦截，拦截原因:取消退款';
						break;	
					case 4100 :
						msg = '出库单已经被拦截，拦截原因:地址被修改,平台已发货';
						break;
					case 66:
						msg = '出库单已经被拦截，拦截原因:已退款,备注修改';
						break;
					case 3:
						msg = '出库单已经被拦截，拦截原因:申请退款,已退款';
						break;
					case 4101:
						msg = '出库单已经被拦截，拦截原因:申请退款,地址被修改,平台已发货';
						break;	
					default :
						msg = '出库单已经被拦截，拦截原因:多种拦截原因，请到页面查看原因';
						break;
				}
				if(parseInt(rows[k].block_reason) & (1|2|4|32|128|256)){
					solve_way = '<a href="javascript:void(0)" onClick="stockSalesPrint.revertCheck({orignal_type:\'consign_stockout\',solve_type:\'revert_check\'},'+rows[k]['id']+')">驳回到订单审核重新审核</a>';
				}else{
					if(!(parseInt(rows[k].block_reason) & 4096)){
						solve_way = '<a href="javascript:void(0)" onClick="stockSalesPrint.revertCheck({orignal_type:\'consign_stockout\',solve_type:\'revert_check\'},'+rows[k]['id']+')">驳回到订单审核重新审核</a>';
						solve_way += ' 或 ';
						solve_way += '<a href="javascript:void(0)" onClick="stockSalesPrint.cancelBlock({orignal_type:\'consign_stockout\',solve_type:\'revert_check\'},'+rows[k]['id']+')">取消拦截</a>';
					}
				}
			temp_result['msg'] = msg;
			temp_result['solve_way'] = solve_way;
			resultBeforeCheck.push(temp_result);
			continue;
		}
		var temp_index = $('#'+stockSalesPrint.params.datagrid.id).datagrid('getRowIndex',rows[k]);
		selects_info[temp_index] = rows[k].id;
	}
	if(!$.isEmptyObject(resultBeforeCheck)){
		$.fn.richDialog("response", resultBeforeCheck, "stockout",{close:function(){if(stockSalesPrint){stockSalesPrint.refresh();}}});
		return;
	}
	Post('<?php echo U("StockSalesPrint/checkBlock");?>',{ids:JSON.stringify(selects_info)},function(r){
		if(r.status == 2){
			$.fn.richDialog("response", r.data.fail, "stockout",{close:function(){if(stockSalesPrint){stockSalesPrint.refresh();}return;}});
		}else{
			switch(type){

				case 'newPrintLogis':
					newPrintLogisticsDialog(number)
					break;
				case 'newPrintGoods':
					newPrintGoodsDialog()
					break;
                case 'printPickList':
                    printPickListDialog()
                    break;
				case 'newPrintOrder':
					newPrintDialog(number)
					break;
				case 'newPrintLogisticsAndPickList':
				    newPrintLogAndPickDialog()
				    break;
                case 'printSfOrder':
                    printSfDialog()
                    break;
			}
		}
	});
}

function newContinuePrintSalesStockout(type,isMulti){
	$('#'+stockSalesPrint.printing).linkbutton({text:'打印中...',disabled:true});
	$('#<?php echo ($id_list["continue_print_result"]); ?>').dialog('close');
	choosePrint(type,isMulti,'continue');
}

function goodsInfoToArray(goods_info){
	var goods = {'detail':[]};
//	if(goods_info.suite_ids != undefined&&goods_info.suite_ids != ""){
		goods.suite_ids = goods_info.suite_ids;
		goods.suite_info = goods_info.suite_info;
//	}
	delete goods_info.suite_ids;
	delete goods_info.suite_info;
	for(var i=0;;i++){
		if(goods_info[i] == undefined)
			break;
		goods.detail[i] = goods_info[i];
		goods.detail[i]['num'] = '【'+goods.detail[i]['num']+'】';
	}
	goods.suite_info = goods.suite_info == undefined?"":goods.suite_info.substr(0,goods.suite_info.length-1);
	return goods;
}
//动态设置打印和预览单价位数
function setPricePointNum(id,goodsDetail,value){
	if(is_print_setting){
		var point_num = $('#'+id).combobox('getValue');
		point_num = parseInt(point_num);
 		for(var key in goodsDetail){
			for(var k in goodsDetail[key]){
				for(var j in value){
					if(goodsDetail[key][k][value[j]]!=undefined){
						goodsDetail[key][k][value[j]] = parseFloat(goodsDetail[key][k][value[j]]).toFixed(point_num);
					}
				}
			}
		}
	}
}
function setRowPricePointNum(rows){
	if(is_print_setting){
		var point_num = parseInt($('#new_old_print_point_number').combobox('getValue'));
		for(var i=0;i<rows.length;++i){
			rows[i].goods_total_cost = parseFloat(rows[i].goods_total_cost).toFixed(point_num);
			rows[i].receivable = parseFloat(rows[i].receivable).toFixed(point_num);
			rows[i].cod_amount = parseFloat(rows[i].cod_amount).toFixed(point_num);
			rows[i].post_amount = parseFloat(rows[i].post_amount).toFixed(point_num);
		}
	}
}

function province_select(newValue, oldValue){
	var that = this;
	var ids = newValue;
	if(ids.length >2){
		$('#shop_add_city').combobox('disable');
		$('#shop_add_country').combobox('disable');
	}else{
		$('#shop_add_city').combobox('enable');
		$('#shop_add_country').combobox('enable');
	}
}
</script>

<!-- layout-south-tabs, call add_tabs js function to add tabs -->


</body>
</html>