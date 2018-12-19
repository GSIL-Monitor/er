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

<!-- toolbar -->

    <div id="<?php echo ($id_list["toolbar"]); ?>" style="padding:5px;height:auto">
        <form id="<?php echo ($id_list["form"]); ?>">
            <div class="form-div">
                <label>订单编号：</label><input class="easyui-textbox txt" type="text" name="search[trade_no]" />
                <label>客户网名：</label><input class="easyui-textbox txt" type="text" name="search[buyer_nick]" />
                <label>店铺：</label><select class="easyui-combobox sel" name="search[shop_id]"><option value="all">全部</option><?php if(is_array($list["shop"])): $i = 0; $__LIST__ = $list["shop"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?></select>
                <label>下单时间：</label><input class="easyui-datebox txt" type="text" name="search[start_time]" data-options="editable:false"/>
                <label>　　　至：</label><input class="easyui-datebox txt" type="text"    name="search[end_time]" data-options="editable:false"/>
                <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-search'" onclick="historysalestrade.submitSearchForm(this);">搜索</a>
                <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-redo'" onclick="historysalestrade.loadFormData();">重置</a>
                <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-excel',plain:true" onclick="historysalestrade.exportToExcel()">导出到Excel</a>
                <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-edit',plain:true" onclick="setDatagridField('Trade/Trade','trade_manage','<?php echo ($datagrid["id"]); ?>',1)">设置表头</a>
            </div>

        </form>

    </div>
    <script type="text/javascript">
        $(function () {
            setTimeout(function () {
                historysalestrade = new RichDatagrid(JSON.parse('<?php echo ($params); ?>'));
                historysalestrade.setFormData();
                historysalestrade.exportToExcel = function(){
                    var dg = $('#'+historysalestrade.params.datagrid.id);
                    var queryParams = dg.datagrid('options').queryParams;
                    var search=JSON.stringify(queryParams);
                    var url= "<?php echo U('HistorySalesTrade/exportToExcel');?>";
                    var id_list=[];
                    for(i in this.selectRows){id_list.push(this.selectRows[i].id);}
                    var form=JSON.stringify(historysalestrade.params.search.form_id);
                    var search_form=JSON.stringify($('#<?php echo ($id_list["form"]); ?>').form('get'));
                    var rows = $("#<?php echo ($id_list["datagrid_id"]); ?>").datagrid("getRows");
                    if(rows==''){
                        messager.confirm('导出不能为空！');
                    }
                    else if(id_list!=''){
                        messager.confirm('确定导出选中的订单吗？',function(r){
                            if(!r){return false;}
                            window.open(url+'?id_list='+id_list);
                        })
                    }else if(search=='{}'||(search_form==form&&search.length==675)){
                        messager.confirm('确定导出所有的订单吗？',function(r){
                            if(!r){return false;}
                            window.open(url+'?id_list='+id_list);
                        })
                    }
                    else{
                        messager.confirm('确定导出搜索的订单吗？',function(r){
                            if(!r){return false;}
                            window.open(url+'?id_list='+id_list+'&'+'search='+search);
                        })
                    }
                }
            }, 0);
        });
    </script>


<!-- layout-south-tabs, call add_tabs js function to add tabs -->


</body>
</html>