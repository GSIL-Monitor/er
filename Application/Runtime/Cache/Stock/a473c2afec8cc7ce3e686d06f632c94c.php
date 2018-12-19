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

    <div id="examine_printer_data" class="easyui-combobox"></div>
    <script type="text/javascript">
        $(function(){
            examine_set_logistics_templates = new  Object();
            examine_set_logistics_templates.params = JSON.parse('<?php echo ($params); ?>');
            var templateData = JSON.parse('<?php echo ($templates_info); ?>');
            var printerData = [];
            stockoutExamine.selectPrinter('setting');

            $.extend($.fn.datagrid.methods, {
                editCell: function (jq, param) {
                    return jq.each(function () {
                        var opts = $(this).datagrid('options');
                        var fields = $(this).datagrid('getColumnFields', true).concat($(this).datagrid('getColumnFields'));
                        for (var i = 0; i < fields.length; i++) {
                            var col = $(this).datagrid('getColumnOption', fields[i]);
                            col.editor1 = col.editor;
                            if (fields[i] != param.field) {
                                col.editor = null;
                            }
                        }
                        $(this).datagrid('beginEdit', param.index);
                        for (var i = 0; i < fields.length; i++) {
                            var col = $(this).datagrid('getColumnOption', fields[i]);
                            col.editor = col.editor1;
                        }
                    });
                }
            });
            $.extend($.fn.validatebox.defaults.rules, {
                new_box_no_unique: {
                    validator: function (value) {
                        if(value == undefined){
                            return false;
                        }
                        return true;
                    },
                    message: "未知错误!"
                }
            });

            var editIndex = undefined;
            //点击单元格触发的事件
            function onClickCell(index, field) {
                if (endEditing()) {
                    $('#'+examine_set_logistics_templates.params.datagrid.id).datagrid('selectRow', index)
                        .datagrid('editCell', { index: index, field: field });
                    editIndex = index;
                    if(field == 'title'){
                        var ed = $('#'+examine_set_logistics_templates.params.datagrid.id).datagrid('getEditor', {index: index, field: 'title'});
                        $(ed.target).combobox('loadData', templateData[index]);
                    }
                    if(field == 'name'){
                        var ed = $('#'+examine_set_logistics_templates.params.datagrid.id).datagrid('getEditor', {index: index, field: 'name'});
                        $(ed.target).combobox('loadData', printerData);
                    }
                }
            }
            //判断是否编辑结束
            function endEditing() {
                if (editIndex == undefined) { return true }
                if ($('#'+examine_set_logistics_templates.params.datagrid.id).datagrid('validateRow', editIndex)) {
                    $('#'+examine_set_logistics_templates.params.datagrid.id).datagrid('endEdit', editIndex);
                    editIndex = undefined;
                    return true;
                } else {
                    return false;
                }
            }
            //编辑完单元格之前触发的事件
            function onBeginEdit(index, row, changes) {
                var new_box_no_val = $('#'+examine_set_logistics_templates.params.datagrid.id).datagrid('getSelected');
//                if(new_box_no_val.new_box_no!=''){dict_map_box_no[new_box_no_val.new_box_no]=false;}
            }
            //编辑完单元格之后触发的事件
            function onAfterEdit(index, row, changes) {
                var new_box_no_val = $('#'+examine_set_logistics_templates.params.datagrid.id).datagrid('getSelected');
//                if(new_box_no_val.new_box_no!=''){dict_map_box_no[new_box_no_val.new_box_no]=true;}
                $('#'+examine_set_logistics_templates.params.datagrid.id).datagrid('endEdit', editIndex);
                $('#'+examine_set_logistics_templates.params.datagrid.id).datagrid('refreshRow', index);
                editIndex = undefined;
            }
            setTimeout(function(){

                $('#'+examine_set_logistics_templates.params.datagrid.id).datagrid('options').onAfterEdit = function(index, row, changes){onAfterEdit(index, row, changes);};
                $('#'+examine_set_logistics_templates.params.datagrid.id).datagrid('options').onBeginEdit = function(index, row, changes){onBeginEdit(index, row, changes);};
                $('#'+examine_set_logistics_templates.params.datagrid.id).datagrid('options').onClickCell = function(index,field,value){onClickCell(index,field,value)};
                $('#'+examine_set_logistics_templates.params.datagrid.id).datagrid('enableCellEditing');

                printerData = $("#examine_printer_data").combobox('getData');
            },0);
        });

    </script>

<!-- layout-south-tabs, call add_tabs js function to add tabs -->


</body>
</html>