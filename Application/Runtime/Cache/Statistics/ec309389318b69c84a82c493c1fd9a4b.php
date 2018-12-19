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

<!-- toolbar -->

    <div id="<?php echo ($id_list["tool_bar"]); ?>" style="padding:5px 0;margin-top: 0;height:auto">
        <form id="statistics-form-stock-out" class="easyui-form" method="post"
              style="background-color: #f3f3f3;margin: 0;display: inline;">
            <div class="form-div" style="padding: 10px;display: inline;">
                <label>　　   &nbsp;仓库：</label>
                <select class="easyui-combobox sel"	name="search[warehouse_id]" data-options="panelHeight:'200px',editable:false,">
                    <option value="0">全部</option>
                    <?php if(is_array($list["warehouse"])): $i = 0; $__LIST__ = $list["warehouse"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                </select>
                <label>　出库类别：</label>
                <input class="easyui-combobox txt" name="search[src_order_type]" data-options="valueField:'id',textField:'name',data:formatter.get_data('stockout_type_all')"/>
                <label>　商家编码： </label><input class="easyui-textbox txt" type="text" name="search[spec_no]"/>
                <label>&emsp;审核时间：</label><input id="<?php echo ($id_list["dailystat_start"]); ?>" class="easyui-datebox txt" type="text" name="search[day_start]" value=<?php echo ($current_date); ?> />
                <label>　　   &nbsp;&emsp; 到：</label><input id="<?php echo ($id_list["dailystat_end"]); ?>" class="easyui-datebox txt" type="text" name="search[day_end]" value=<?php echo ($current_date); ?> />
                <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-search'"
                   onclick="stockout_collect_grid.submitSearchForm(this);">搜索</a>
                <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-redo'"
                   onclick="stockout_collect_grid.loadFormData();">重置</a>
                <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-edit',plain:true" onclick="setDatagridField('Statistics/StockoutCollect','stockoutcollect','<?php echo ($datagrid["id"]); ?>',1)">设置表头</a>
            </div>
            <div class="form-div">
                <label>　货品编码： </label><input class="easyui-textbox txt" type="text" name="search[goods_no]"/>
                <label>　货品名称： </label><input class="easyui-textbox txt" type="text" name="search[goods_name]"/>
                <label>　规格名称： </label><input class="easyui-textbox txt" type="text" name="search[spec_name]"/>
                <label>　货品分类：</label><input class="txt" id="stockout_collect_goods_class" value="-1" name="search[class_id]" data-options="url:'<?php echo U('Goods/GoodsClass/getTreeClass');?>?type=all',method:'post',required:true"/>
                <label>　货品品牌：</label><select class="easyui-combobox sel" name="search[brand_id]"><option value="all">全部</option><?php if(is_array($list["brand"])): $i = 0; $__LIST__ = $list["brand"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?></select>
                <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-save',plain:true"
                   onclick="stockout_collect_grid.exportToExcel()">导出到Excel</a>
            </div>
        </form>
    </div>
    <script type="text/javascript">
        var stockout_collect = JSON.parse('<?php echo ($params); ?>');
        $('#stockout_collect_goods_class').changStyleTreeCombo('stockout_collect_goods_class');
        $(function(){
            setTimeout(function () {
                stockout_collect_grid = new RichDatagrid(stockout_collect);
                stockout_collect_grid.setFormData();
                stockout_collect_grid.exportToExcel = function(){
                    var id_list=[];
                    for(i in this.selectRows){
                        if(this.selectRows[i].id){
                            id_list.push(this.selectRows[i].id);
                        }
                    }
                    var url= "<?php echo U('StockoutCollect/exportToExcel');?>";
                    var search=JSON.stringify($('#statistics-form-stock-out').form('get'));
                    var data = $("#<?php echo ($datagrid["id"]); ?>").datagrid("getData");
                    if(data.total==1){
                        messager.confirm('导出不能为空！');
                    }else if(id_list.length > 0) {
                        messager.confirm('确定导出选中的订单吗？', function (res) {
                            if (!res) {return false;}
                            window.open(url+'?search='+search+'&id_list='+id_list);
                        })
                    }else{
                        messager.confirm('确定导出搜索的订单吗？',function(r){
                            if(!r){return false;}
                            window.open(url+'?search='+search+'&id_list='+id_list);
                        })
                    }
                };
                var datagrid_id = stockout_collect_grid.params.datagrid.id;
                $('#'+datagrid_id).datagrid({rowStyler:function(index,row){
                    var page_size = $(this).datagrid('options').pageSize ;
                    if (row.spec_no == '合计'){
                        return 'background-color:#F4F4F4;color:#000;font-weight:bolder';
                    }
                }});
                $('#'+datagrid_id).datagrid({onLoadSuccess:function(){
                    $(this).datagrid('clearSelections'); //更新表格选中行
                    var rows = $(this).datagrid('getRows');
                    var stat_fields = [
                        'num',
                        'total_price',
                        'cost_price'
                    ];
                    var append_row = {};
                    append_row['spec_no'] = '合计';
                    StockinCollectTotalCompute(rows,stat_fields,append_row);
                    $(this).datagrid('appendRow',append_row);
                }});
            });
        });
        function StockinCollectTotalCompute(page_rows,arr_fields,append_row){
            var rows = page_rows;
            for(var index in arr_fields){
                var field = arr_fields[index];
                append_row[field] = 0;
                for (var i = 0; i < rows.length; i++) {
                    append_row[field] += parseFloat(rows[i][field]);
                }
                append_row[field] = append_row[field].toFixed(4);
            }
        }
    </script>

<!-- layout-south-tabs, call add_tabs js function to add tabs -->


</body>
</html>