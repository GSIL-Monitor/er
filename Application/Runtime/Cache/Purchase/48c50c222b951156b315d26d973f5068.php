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
	<form id="<?php echo ($id_list["form"]); ?>" class="easyui-form" method="post">
		<div class="form-div">
			<label style="width: 80px;"> 编号：</label><input class="easyui-textbox txt" type="text" name="search[wall_no]" style="width: 130px;"/>
			<label style="width: 80px;">　排数：</label><input class="easyui-textbox txt" type="text" name="search[row_num]" style="width: 130px;"/>
			<label style="width: 80px;">　列数：</label><input class="easyui-textbox txt" type="text" name="search[column_num]" style="width: 130px;"/>
			<!--<label style="width: 80px;">　属性：</label><select class="easyui-combobox sel" name="search[type]" data-options="panelHeight:'100px',editable:false " style="width: 130px;">-->
					<!--<?php if(is_array($sorting_wall_type)): $i = 0; $__LIST__ = $sorting_wall_type;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?></select>-->
			<label>是否停用：</label><input class="easyui-combobox txt" text="txt" name="search[is_disabled]" data-options="panelHeight:110,valueField:'id',textField:'name',data:[{'id':'all','name':'全部'},{'id':'0','name':'否'},{'id':'1','name':'是'}],editable:false,value:'all'">
			<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-search'" onclick="sortingWall.submitSearchForm();">搜索</a>
			<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-redo'" onclick="sortingWall.loadFormData();">重置</a>

		</div>
	</form>
	<!--<input type="hidden" id="<?php echo ($id_list["hidden_flag"]); ?>" value="1">-->
	<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-add',plain:true" onclick = "sortingWall.add()";>新建</a>
	<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-edit',plain:true" onclick = "sortingWall.edit()";>编辑</a>
	<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-remove',plain:true" onclick = "sortingWall.remove('sorting_wall')";>删除</a>
</div>

<script>
	$(function(){
		setTimeout(function(){
//			var toolbar_id = '<?php echo ($id_list["toolbar"]); ?>';
//			var element_selectors ={
//				'wall_no'           	: $('#'+toolbar_id+" a[name='wall_no']"),
//				'row_num'           	: $('#'+toolbar_id+" a[name='row_num']"),
//				'column_num'           	: $('#'+toolbar_id+" a[name='column_num']"),
//			};
			sortingWall = new RichDatagrid(JSON.parse('<?php echo ($params); ?>'));
			sortingWall.setFormData();
//			sortingWall.element_selectors = element_selectors;
			sortingWall.add = function(){
				var that=this;
//				var rows = $('#'+that.params.datagrid.id).datagrid('getRows');
//				if(rows.length !=0){messager.alert('目前只支持新建一个分拣墙');return;}
				var buttons=[ {id:'confirmId', text:'确定',handler:function(){ that.submitAddDialog(); }}, {text:'取消',handler:function(){that.cancelDialog(that.params.add.id)}} ];
				this.showDialog(that.params.add.id,that.params.add.title,that.params.add.url,that.params.add.height,that.params.add.width,buttons,that.params.add.toolbar,that.params.add.ismax);
			}
		},0);
	});
</script>

<!-- layout-south-tabs, call add_tabs js function to add tabs -->


</body>
</html>