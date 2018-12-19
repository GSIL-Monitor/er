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

    <div id="<?php echo ($id_list["tool_bar"]); ?>" style="padding-top:10px;margin-top: 0;height:auto">
        <form id="logistics-trace-form" class="easyui-form" method="post"
              style="background-color: #f3f3f3;margin: 0;display: inline;">
            <div class="form-div" style="padding: 10px;display: inline;">
                <label>物流公司：</label>
                <select class="easyui-combobox sel" name="search[logistics_id]">
                    <option value="all">全部</option>
                    <?php if(is_array($list["logistics"])): $i = 0; $__LIST__ = $list["logistics"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                </select>
                <label class="five-character-width" style="text-align: right;">　物流状态：</label>
                <input class="easyui-combobox sel" type="text" name="search[logistics_trace_type]" required="true" data-options="valueField:'id',textField:'name',data:formatter.get_data('logistics_trace_type','sel')"/>
                <label >　物流单号：</label><input class="easyui-textbox txt" type="text" name="search[logistics_no]" />
                <label>　发货时间：</label><input class="easyui-datetimebox txt" type="text" name="search[trade_consign_start_time]" data-options="editable:false"/>
                <label>&nbsp;　至：</label><input class="easyui-datetimebox txt" type="text"    name="search[trade_consign_end_time]" data-options="editable:false"/>


                <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-search'"
                   onclick="logistics_trace_obj.submitSearchForm(this);">搜索</a>
                <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-redo'"
                   onclick="logistics_trace_obj.loadFormData();">重置</a>
                <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-edit',plain:true" onclick="setDatagridField('Statistics/SalesLogisticsTrace','sales_logistics_trace','<?php echo ($datagrid["id"]); ?>')">设置表头</a>

            </div>
            <div class="form-div" style="padding: 10px;">
                <label> 店　　铺：</label>
                <select class="easyui-combobox sel" name="search[shop_id]"><option value="all">全部</option><?php if(is_array($list["shop"])): $i = 0; $__LIST__ = $list["shop"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?></select>
                <label>　仓　　库：</label>
                <select class="easyui-combobox sel" name="search[warehouse_id]" data-options="editable:false">
                    <option value="all">全部</option>
                    <?php if(is_array($list["warehouse"])): $i = 0; $__LIST__ = $list["warehouse"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?><option value="<?php echo ($v["id"]); ?>"><?php echo ($v["name"]); ?></option>z<?php endforeach; endif; else: echo "" ;endif; ?>
                </select>
                <label >　出库单号：</label><input class="easyui-textbox txt" type="text" name="search[stockout_no]" />
                <label>　揽件时间：</label><input class="easyui-datetimebox txt" type="text" name="search[trade_get_start_time]" data-options="editable:false"/>
                <label>&nbsp;　至：</label><input class="easyui-datetimebox txt" type="text"    name="search[trade_get_end_time]" data-options="editable:false"/>
                <label >　订单编号：</label><input class="easyui-textbox txt" type="text" name="search[trade_no]" />
            </div>

        </form>
    </div>
    <script type="text/javascript">
        //# sourceURL=sales_logistics_trace.js
        $(function () {
            setTimeout(function () {
                logistics_trace_obj = new RichDatagrid(JSON.parse('<?php echo ($params); ?>'));
                logistics_trace_obj.setFormData();





            });
        });


    </script>

<!-- layout-south-tabs, call add_tabs js function to add tabs -->


</body>
</html>