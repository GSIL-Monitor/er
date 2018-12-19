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
<!-- layout-center-datagrid -->
 
<div data-options="region:'center'" style="width:100%;background:#eee;"><table id="<?php echo ($datagrid["id"]); ?>" class="easyui-datagrid" data-options='<?php $dataOptions = array_merge(array ( 'border' => false, 'fit' => true, 'fitColumns' => true, 'rownumbers' => true, 'singleSelect' => true, 'pagination' => true, 'pageList' => array ( 0 => 20, 1 => 50, 2 => 100, ), 'pageSize' => 20, ), $datagrid["options"]);if(isset($dataOptions['toolbar']) && substr($dataOptions['toolbar'],0,1) != '#'): unset($dataOptions['toolbar']); endif;if(isset($dataOptions['methods'])): unset($dataOptions['methods']);endif; echo trim(json_encode($dataOptions), '{}[]').((isset($datagrid["options"]['toolbar']) && substr($datagrid["options"]['toolbar'],0,1) != '#')?',"toolbar":'.$datagrid["options"]['toolbar']:null).(isset($datagrid["options"]['methods'])? ','.$datagrid["options"]['methods']:null); ?>' style="<?php echo ($datagrid["style"]); ?>" ><thead><tr><?php if(is_array($datagrid["fields"])):foreach ($datagrid["fields"] as $key=>$arr):if(isset($arr['formatter'])):unset($arr['formatter']);endif;if(isset($arr['methods'])):unset($arr['methods']);endif;if(isset($arr['editor'])):unset($arr['editor']);endif;echo "<th data-options='".trim(json_encode($arr), '{}[]').(isset($datagrid["fields"][$key]['formatter'])?",\"formatter\":".$datagrid["fields"][$key]['formatter']:null).(isset($datagrid["fields"][$key]['editor'])?",\"editor\":".$datagrid["fields"][$key]['editor']:null).(isset($datagrid["fields"][$key]['methods'])?",".$datagrid["fields"][$key]['methods']:null)."'>".$key."</th>";endforeach;endif; ?></tr></thead></table></div> 
<!-- layout-south-tabs -->
 <div data-options="region:'south',split:true" style="height:40%;background:#eee;overflow:hidden;"> <div class="easyui-tabs" data-options="fit:true,border:false,plain:true" id="<?php echo ($id_list["tab_container"]); ?>"> </div> </div>
<block name="dialog">
</div>
<!-- dialog -->
 <div id="<?php echo ($id_list["add"]); ?>"></div> <div id="<?php echo ($id_list["edit"]); ?>"></div> 
<!-- toolbar -->

	<div id="<?php echo ($id_list['toolbar']); ?>" style="padding:5px;height:auto">
		<form id="<?php echo ($id_list['form']); ?>" class="easyui-form" method="post">
			<div class="form-div">
				<label>打印批次：</label><input class="easyui-textbox txt" type="text" name="search[batch_no]"/>
				<label>单据类型：</label><input class="easyui-combobox txt" text="txt" name="search[order_mask]" data-options="panelHeight:110,valueField:'id',textField:'name',data:[{'id':'all','name':'全部'},{'id':'1','name':'发货单'},{'id':'2','name':'物流单'},{'id':'3','name':'分拣单'}],editable:false,value:'all'">
				<label>订单数量：</label><input class="easyui-textbox txt" type="text" name="search[order_num]"/>
				<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-search'" onclick="print_batch_list.submitSearchForm(this)">搜索</a>
				<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-redo'" onclick="print_batch_list.loadFormData()">重置</a><br>
				<label>订单编号：</label><input class="easyui-textbox txt" type="text" name="search[src_order_no]"/>
				<label>创建时间：</label><input class="easyui-datetimebox txt" type="text" name="search[create_time_start]" data-options="editable:false"/>
				<label style="margin-left: 36px;">至：</label><input class="easyui-datetimebox txt" type="text" name="search[create_time_end]" data-options="editable:false"/><br>
				<label>原始单号：</label><input class="easyui-textbox txt" type="text" name="search[src_tid]"/>
				<label>物流单号：</label><input class="easyui-textbox txt" type="text" name="search[logistics_no]"/>
			</div>
		</form>
	</div>

<!-- layout-south-tabs, call add_tabs js function to add tabs -->

	<script type="text/javascript">
		$(function () {
			setTimeout(function () {
				print_batch_list = new RichDatagrid(JSON.parse('<?php echo ($params); ?>'));
				print_batch_list.setFormData();
				add_tabs(JSON.parse('<?php echo ($arr_tabs); ?>'));
			}, 0);
		});
	</script>

</body>
</html>