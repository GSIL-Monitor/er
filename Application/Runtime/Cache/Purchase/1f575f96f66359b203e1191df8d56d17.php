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


</div>
<!-- dialog -->

     <div id="<?php echo ($id_list["add"]); ?>">
     </div>
	<div id="<?php echo ($id_list["edit"]); ?>">
     </div>

<!-- toolbar -->

 <div id="<?php echo ($id_list["toolbar"]); ?>" style="padding:5px;height:auto">
 <div class="form-div" style="border-bottom:  1px solid #7CAAB1">
    <a href="javascript:void(0)" class="easyui-linkbutton" name="button_submit" onclick = "addStallsorder.saveStalls();" data-options="iconCls:'icon-save',plain:true">生成档口单</a>
    </div>
<div style="display: inline-block;vertical-align:middle">
	<form id="<?php echo ($id_list["form"]); ?>" class="easyui-form" method="post">
		<div class="form-div">
			<label style="margin-left: 12px;"> 唯一码：</label><input class="easyui-textbox txt" type="text" name="search[unique_code]" />
			<label style="margin-left: 10px;">　　订单：</label><input class="easyui-textbox txt" type="text" name="search[trade_no]" />
			</div>
		<div class="form-div">
			<label style="margin-left: -12px;">　商家编码：</label><input class="easyui-textbox txt" type="text" name="search[spec_no]" />
			<label style="margin-left: 10px;">　供应商：</label><select class="easyui-combobox sel" name="search[provider_id]" data-options="panelHeight:'200px',editable:false " >
			<option value="all">全部</option><?php if(is_array($list["provider"])): $i = 0; $__LIST__ = $list["provider"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?></select>
			<label style="margin-left: 12px;">　仓库：</label><select class="easyui-combobox sel" name="search[warehouse_id]" data-options="panelHeight:'200px',editable:false " >
			<option value="all">全部</option><?php if(is_array($list["warehouse"])): $i = 0; $__LIST__ = $list["warehouse"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?></select>
			<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-search'" onclick="addStallsorder.submitSearchForm();">搜索</a>
			<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-redo'" onclick="addStallsorder.loadFormData();">重置</a>
		
		</div>
	</form>
	</div>
</div>

<script>
//# sourceURL=addstallsManagement.js
	$(function(){
		setTimeout(function(){
			addStallsorder = new RichDatagrid(JSON.parse('<?php echo ($params); ?>'));
			addStallsorder.setFormData();
			addStallsorder.saveStalls = function(){
				var rows = $('#'+this.params.id_list.datagrid).datagrid('getSelections');
				var form_data = $('#'+this.params.id_list.form).form('get');
				if(($.isEmptyObject(rows) || rows == '') && !$.isEmptyObject(form_data)){
					messager.confirm('按搜索条件生成档口单',function(r){
						if(r){
							var type = 'search';
							var data = {};
							data['provider_id'] = form_data['search[provider_id]'];
							data['spec_no'] = form_data['search[spec_no]'];
							data['trade_no'] = form_data['search[trade_no]'];
							data['unique_code'] = form_data['search[unique_code]'];
							data['warehouse_id'] = form_data['search[warehouse_id]'];
							$.post('<?php echo U("Purchase/StallsOrderManagement/postAddOrder");?>',{data:data,type:type},function(r){
								messager.alert(r.info);
								if(r.status == 0){
									stallsManagement.refresh();
									$('#'+stallsManagement.params.add.id).dialog('close');
								}
							});
						}
					});
				}else{
					messager.confirm('选中的货品生成档口单',function(r){
						if(r){
							var type = 'selected';
							var rows_id_list = '';
							for(var i=0; i<rows.length; i++){
								rows_id_list += rows[i].id + ',';
							}
							rows_id_list = rows_id_list.substr(0,rows_id_list.length-1);
							var data = rows_id_list;
							addStallsorder.postAddOrder(data,type);
						}
					});
				}
			}
			addStallsorder.postAddOrder = function(data,type){
				$.post('<?php echo U("Purchase/StallsOrderManagement/postAddOrder");?>',{data:data,type:type},function(r){
					messager.alert(r.info);
					if(r.status == 0){
						stallsManagement.refresh();
						$('#'+stallsManagement.params.add.id).dialog('close');
					}
				});
			}
			
		},0);
	});
</script>

<!-- layout-south-tabs, call add_tabs js function to add tabs -->


</body>
</html>