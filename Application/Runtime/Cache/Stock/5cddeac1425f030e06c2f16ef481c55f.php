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
 <div id="<?php echo ($id_list["add"]); ?>"></div> <div id="<?php echo ($id_list["edit"]); ?>"></div> 
<!-- toolbar -->

    <div id="<?php echo ($id_list["tool_bar"]); ?>" style="padding-top:10px;margin-top: 0;height:auto">
        <form id="<?php echo ($id_list["form"]); ?>" class="easyui-form" method="post"
              style="background-color: #f3f3f3;margin: 0;display: inline;">
            <div class="form-div" style="padding: 10px;display: inline;">
                <label>物流单号：</label><input class="easyui-textbox txt" type="text" name="search[logistics_no]"/>
                <label   style="width: 60px;display: inline-block;">热敏类型</label>
                <select class="easyui-combobox sel"
                        name="search[bill_type]"
                        data-options="panelHeight:'auto',editable:false, required:true"
                        style="width:100px;">
                    <option value="all">全部</option>
                    <option value="1">线下热敏</option>
                    <option value="2">云栈热敏</option>
                </select>
                <label>单号状态：</label><input class="easyui-combobox sel" name="search[status]" id="status" style="width:100px;  "data-options="valueField:'id',textField:'name',data:formatter.get_data('history_stock_logistics_no_status')"/>
                <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-search'"
                   onclick="historystocklogno_obj.submitSearchForm(this);">搜索</a>
                <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-redo'"
                   onclick="historystocklogno_obj.loadFormData();">重置</a>
            </div>
        </form>
    </div>
    <script type="text/javascript">
        //# sourceURL=historystocklogno_obj.js
        $(function () {
            setTimeout(function () {
                historystocklogno_obj = new RichDatagrid(JSON.parse('<?php echo ($params); ?>'));
                historystocklogno_obj.setFormData();
            }, 0);
        });
    </script>

<!-- layout-south-tabs, call add_tabs js function to add tabs -->


</body>
</html>