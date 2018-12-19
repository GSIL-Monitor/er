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

    <div id="<?php echo ($id_list["help_id"]); ?>"></div>
    <div id="<?php echo ($id_list["edit"]); ?>"></div>

<!-- toolbar -->

    <div id="<?php echo ($id_list["tool_bar"]); ?>" style="padding:5px 0;margin-top: 0;height:auto">
        <form id="statistics-form" class="easyui-form" method="post"
              style="background-color: #f3f3f3;margin: 0;display: inline;">
            <div class="form-div" style="padding: 10px;display: inline;">
                <label>　商家编码： </label><input class="easyui-textbox txt" type="text" name="search[spec_no]"/>
                <label>　货品编码： </label><input class="easyui-textbox txt" type="text" name="search[goods_no]"/>
                <label>　货品名称： </label><input class="easyui-textbox txt" type="text" name="search[goods_name]"/>
                <label>&emsp;时间区间：</label><input id="<?php echo ($id_list["dailystat_start"]); ?>" class="easyui-datebox txt" type="text" name="search[day_start]" value=<?php echo ($current_date); ?> />
                <label>　　   &nbsp;&emsp; 到：</label><input id="<?php echo ($id_list["dailystat_end"]); ?>" class="easyui-datebox txt" type="text" name="search[day_end]" value=<?php echo ($current_date); ?> />
                <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-search'"
                   onclick="stock_ledger_grid.submitSearchForm(this);">搜索</a>
                <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-redo'"
                   onclick="stock_ledger_grid.loadFormData();">重置</a>
                <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-edit',plain:true" onclick="setDatagridField('Statistics/StockLedger','stockledger','<?php echo ($datagrid["id"]); ?>',1)">设置表头</a>
            </div>
            <div class="form-div">
                <label>　 货品简称： </label><input class="easyui-textbox txt" type="text" name="search[short_name]"/>
                <label>　　    &nbsp;&nbsp;仓库： </label>
                <select class="easyui-combobox sel"	name="search[warehouse_id]" data-options="panelHeight:'200px',editable:false,">
                    <option value="0">全部</option>
                    <?php if(is_array($list["warehouse"])): $i = 0; $__LIST__ = $list["warehouse"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                </select>
                <label>　货品分类：</label><input class="txt" id="stock_ledger_goods_class" value="-1" name="search[class_id]" data-options="url:'<?php echo U('Goods/GoodsClass/getTreeClass');?>?type=all',method:'post',required:true"/>
                <label>　货品品牌：</label><select class="easyui-combobox sel" name="search[brand_id]"><option value="all">全部</option><?php if(is_array($list["brand"])): $i = 0; $__LIST__ = $list["brand"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?></select>
                <span style="color: red;font-size: 16px;margin-left: 10px;">#该功能耗费较大资源，请不要在工作时间(8:00~19:00)执行#</span>
            </div>
        </form>
    </div>
    <script type="text/javascript">
        var stock_ledger = JSON.parse('<?php echo ($params); ?>');
        $('#stock_ledger_goods_class').changStyleTreeCombo('stock_ledger_goods_class');
        $(function(){
            setTimeout(function () {
                stock_ledger_grid = new RichDatagrid(stock_ledger);
                stock_ledger_grid.setFormData();
                $('#stockledger_datagrid').datagrid( {
                    onDblClickRow :function(rowIndex,rowData){
                        var data=[];
                        data['spec_id']=rowData.spec_id;
                        var url='<?php echo U('StockLedger/showLedgerDetial');?>?id='+data['spec_id'];
                        var buttons=[ {text:'关闭',handler:function(){stock_ledger_grid.cancelDialog();}} ];
                        stock_ledger_grid.showDialog(0,'出入库明细',url,500,900,buttons,null,false);
                    }
                });
//                $('#<?php echo ($id_list["dailystat_start"]); ?>').datebox('calendar').calendar({
//                    validator: function(date){
//                        var now = new Date();
//                        var data_start = new Date(now.getFullYear(), now.getMonth()-3, now.getDate());
//                        var data_end = new Date(now.getFullYear(), now.getMonth(), now.getDate());
//                        return data_start<=date && date<=data_end;
//                    }
//                });
//                $('#<?php echo ($id_list["dailystat_end"]); ?>').datebox('calendar').calendar({
//                    validator: function(date){
//                        var now = new Date();
//                        var data_start = new Date(now.getFullYear(), now.getMonth()-3, now.getDate());
//                        var data_end = new Date(now.getFullYear(), now.getMonth(), now.getDate());
//                        return data_start<=date && date<=data_end;
//                    }
//                });
                stock_ledger_grid.date_start = function(start,end,type){
                    var start_date,end_date,month_range = 3;//时间范围
                    var start_arr = start.split('-');
                    var end_arr = end.split('-');
                    switch (type){
                        case 'change_end' :
                            start_date = new Date(start_arr[0],parseInt(start_arr[1])+month_range,start_arr[2]);
                            end_date = new Date(end_arr[0],end_arr[1],end_arr[2]);
                            if(start_date.getTime()<end_date.getTime()){
                                var new_date = start_date.getFullYear()+'-'+start_date.getMonth()+'-'+start_date.getDate();
                                $('#<?php echo ($id_list["dailystat_end"]); ?>').datebox('setValue',new_date);
                            }
                            break;
                        case 'change_start' :
                            start_date = new Date(start_arr[0],start_arr[1],start_arr[2]);
                            end_date = new Date(end_arr[0],parseInt(end_arr[1])-month_range,end_arr[2]);
                            if(start_date.getTime()<end_date.getTime()){
                                var new_date = end_date.getFullYear()+'-'+end_date.getMonth()+'-'+end_date.getDate();
                                $('#<?php echo ($id_list["dailystat_start"]); ?>').datebox('setValue',new_date);
                            }
                            break;
                    }
                };
                $('#<?php echo ($id_list["dailystat_start"]); ?>').datebox({
                    onSelect : function(){
                        var start = $('#<?php echo ($id_list["dailystat_start"]); ?>').datebox('getValue');
                        var end = $('#<?php echo ($id_list["dailystat_end"]); ?>').datebox('getValue');
                        stock_ledger_grid.date_start(start,end,'change_end');
                    }
                });
                $('#<?php echo ($id_list["dailystat_end"]); ?>').datebox({
                    onSelect : function(){
                        var start = $('#<?php echo ($id_list["dailystat_start"]); ?>').datebox('getValue');
                        var end = $('#<?php echo ($id_list["dailystat_end"]); ?>').datebox('getValue');
                        stock_ledger_grid.date_start(start,end,'change_start');
                    }
                });
               /* var datagrid_id = stock_ledger_grid.params.datagrid.id;
                $('#'+datagrid_id).datagrid({rowStyler:function(index,row){
                    var page_size = $(this).datagrid('options').pageSize ;
                    if (row.spec_no == '合计'){
                        return 'background-color:#F4F4F4;color:#000;font-weight:bolder';
                    }
                }});
                $('#'+datagrid_id).datagrid({onLoadSuccess:function(){
                    var rows = $(this).datagrid('getRows');
                    var stat_fields = [
                        'num',
                        'total_cost',
                        'price'
                    ];
                    var append_row = {};
                    append_row['spec_no'] = '合计';
                    StockinCollectTotalCompute(rows,stat_fields,append_row);
                    $(this).datagrid('appendRow',append_row);
                }});*/
            });
        });
        /*function StockinCollectTotalCompute(page_rows,arr_fields,append_row){
            var rows = page_rows;
            for(var index in arr_fields){
                var field = arr_fields[index];
                append_row[field] = 0;
                for (var i = 0; i < rows.length; i++) {
                    append_row[field] += parseFloat(rows[i][field]);
                }
                append_row[field] = append_row[field].toFixed(4);
            }
        }*/
    </script>

<!-- layout-south-tabs, call add_tabs js function to add tabs -->


</body>
</html>