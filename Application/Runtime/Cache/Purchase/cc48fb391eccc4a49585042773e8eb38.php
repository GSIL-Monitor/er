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


<!-- toolbar -->

    <div id="<?php echo ($id_list["toolbar"]); ?>" style="padding:5px;height:auto">
        <form id="<?php echo ($id_list["form"]); ?>" class="easyui-form" method="post">
            <div class="form-div">
				　<label style="display: inline-block;">供应商：</label><select class="easyui-combobox sel" name="search[provider_id]" > <?php if(is_array($provider_array)): $i = 0; $__LIST__ = $provider_array;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?> </select>
				<label style="display: inline-block;">仓库：</label><select class="easyui-combobox sel" name="search[warehouse_id]" > <?php if(is_array($warehouse_array)): $i = 0; $__LIST__ = $warehouse_array;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?> </select>
                <label style="display: inline-block;">商家编码：</label><input class="easyui-textbox txt" type="text" name="search[spec_no]" style="width: 130px;"/>
                <label style="display: inline-block;">货品名称：</label><input class="easyui-textbox txt" type="text" name="search[goods_name]" style="width: 130px;"/>
				<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-search'" onclick="alarmPerDay.submitSearchForm(this);">搜索</a>
                <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-redo'" onclick="alarmPerDay.loadFormData();">重置</a>
                <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-purchase'" onclick="alarmPerDay.generatePurchase();">采购</a>
			
			</div>
			<div class="form-div">
			   <label style="display: inline-block;">货品编号：</label><input class="easyui-textbox txt" type="text" name="search[goods_no]" style="width: 130px;"/>
                <!--<a href="javascript:void(0)" onclick="alarmPerDay.clickMore(this);">更多</a>-->
                <label style="display: inline-block;">品牌：</label><select class="easyui-combobox sel" name="search[brand_id]"><?php if(is_array($brand_array)): $i = 0; $__LIST__ = $brand_array;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?></select>
                　　<label style="display: inline-block;">分类：</label><input id="<?php echo ($id_list["goods_class"]); ?>" class="txt" value="-1" name="search[class_id]" data-options="url:'<?php echo U('Goods/GoodsClass/getTreeClass');?>?type=all',method:'post',required:true"/>
             </div>
            <!--<div id="<?php echo ($id_list["more_content"]); ?>">
                <div class="form-div">
                    <label style="width: 80px;display: inline-block;">仓库:</label><select class="easyui-combobox sel" name="search[warehouse_id]" data-options="panelHeight:'auto'"> <?php if(is_array($warehouse_array)): $i = 0; $__LIST__ = $warehouse_array;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?> </select>
                    <label style="width: 80px;display: inline-block;">销量周期:</label><input class="easyui-textbox txt" type="text" name="search[sales_cycle_days]" style="width: 130px;"/>
                </div>
            </div>-->
        </form>
        <!--<input type="hidden" id="<?php echo ($id_list["hidden_flag"]); ?>" value="1">-->
    </div>
    <script>
        //# sourceURL=alarmstock_per_day.js
        (function(){
            var warehouse_elem = $('#<?php echo ($id_list["form"]); ?>'+' select[name="search[warehouse_id]"]');
			var provider = $('#<?php echo ($id_list["form"]); ?>'+' select[name="search[provider_id]"]');
            $(function(){setTimeout(function(){
                $('#<?php echo ($id_list["goods_class"]); ?>').changStyleTreeCombo('<?php echo ($id_list["goods_class"]); ?>');
                alarmPerDay = new RichDatagrid(JSON.parse('<?php echo ($params); ?>'));
                alarmPerDay.setFormData();
                alarmPerDay.generatePurchase = function()
                {
                    var sel_rows = $('#'+this.params.datagrid.id).datagrid('getSelections');
                    if($.isEmptyObject(sel_rows))
                    {
                        messager.alert('请选择采购的货品');
                        return;
                    }
					if(parseInt(sel_rows[0]['is_provider']) == 1){
						var provider_id = '-1';
					}else{
						provider_id = parseInt(sel_rows[0]['provider_id']);
					}	
                    var warehouse_id =  sel_rows[0]['warehouse_id'];
                    var sel_warehouse_id =  warehouse_elem.combobox('getValue');
                    sel_warehouse_id = isNaN(parseInt(sel_warehouse_id))?0:parseInt(sel_warehouse_id);
                    Dialog.show(this.params.purchase.id,this.params.purchase.title,this.params.purchase.url+'?datagrid_id='+this.params.datagrid.id+'&provider_id='+provider_id+'&dialog_id='+this.params.purchase.id+'&warehouse_id='+warehouse_id+'&sel_warehouse_id='+sel_warehouse_id,this.params.purchase.height,this.params.purchase.width,[]);

                }
            },0);});
        })();


    </script>

<!-- layout-south-tabs, call add_tabs js function to add tabs -->


</body>
</html>