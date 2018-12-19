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
 <div data-options="region:'south',split:true" style="height:30%;background:#eee;overflow:hidden;"> <div class="easyui-tabs" data-options="fit:true,border:false,plain:true" id="<?php echo ($id_list["tab_container"]); ?>"> </div> </div> 
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
 <div id="<?php echo ($id_list["add"]); ?>"></div> <div id="<?php echo ($id_list["edit"]); ?>"></div> 
<!-- toolbar -->

    <div id="<?php echo ($id_list["tool_bar"]); ?>" style="padding:5px;height:auto">
        <form id="history_original_stockout_search_form" class="easyui-form" method="post">
            <div class="form-div">
                <label >出库单号：</label><input class="easyui-textbox txt" type="text" name="search[stockout_no]" style="width:110px;"/>
                <label >来源单号：</label><input class="easyui-textbox txt" type="text" name="search[src_order_no]" style="width:110px;"/>
                <label >出库单状态：</label>
                <input style="width:110px;" class="easyui-combobox txt" name="search[status]" data-options="valueField:'id',editable:false ,textField:'name',data:formatter.get_data('history_stockout_status')"/>
                <label >出库单类别：</label>
                <input style="width:110px;" class="easyui-combobox txt" name="search[src_order_type]" data-options="valueField:'id',editable:false ,textField:'name',data:formatter.get_data('stockout_type')"/>
                <label>仓&nbsp;&nbsp;库：</label>
                <select style="width:110px;" class="easyui-combobox sel" name="search[warehouse_id]" data-options="panelHeight:'100px;',editable:false ">
                    <?php if(is_array($warehouse_array)): $i = 0; $__LIST__ = $warehouse_array;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                </select>
                <label>经办人：</label>
                <select style="width:110px;" class="easyui-combobox sel" name="search[operator_id]" data-options="panelHeight:'100px',editable:false ">
                    <?php if(is_array($employee_array)): $i = 0; $__LIST__ = $employee_array;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                </select>
                <a class="easyui-linkbutton" data-options="iconCls:'icon-search'" accesskey="" onclick="HistoryOriginalStockout.submitSearchForm();">搜索</a>
                <a class="easyui-linkbutton" data-options="iconCls:'icon-redo'" onclick="HistoryOriginalStockout.loadFormData();">重置</a>
                <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-table-edit',plain:true" onclick="setDatagridField('Stock/HistoryOriginalStockout','history_original_stockout','<?php echo ($datagrid["id"]); ?>')">设置表头</a>
            </div>
        </form>
        <input type="hidden" id="<?php echo ($id_list["hidden_flag"]); ?>" value="1">
    </div>
    <script>
        $(function () {
            setTimeout(function () {
                HistoryOriginalStockout = new RichDatagrid(JSON.parse('<?php echo ($params); ?>'));
                HistoryOriginalStockout.setFormData();
            }, 0);
        });
    </script>

<!-- layout-south-tabs, call add_tabs js function to add tabs -->


</body>
</html>