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


        <div data-options="region:'center'" style="width:100%;height: 60%">
            <table id="<?php echo ($datagrid["id"]); ?>" class="easyui-datagrid" data-options='<?php $dataOptions = array_merge(array ( 'border' => false, 'fit' => true, 'fitColumns' => true, 'rownumbers' => true, 'singleSelect' => true, 'pagination' => true, 'pageList' => array ( 0 => 20, 1 => 50, 2 => 100, ), 'pageSize' => 20, ), $datagrid["options"]);if(isset($dataOptions['toolbar']) && substr($dataOptions['toolbar'],0,1) != '#'): unset($dataOptions['toolbar']); endif;if(isset($dataOptions['methods'])): unset($dataOptions['methods']);endif; echo trim(json_encode($dataOptions), '{}[]').((isset($datagrid["options"]['toolbar']) && substr($datagrid["options"]['toolbar'],0,1) != '#')?',"toolbar":'.$datagrid["options"]['toolbar']:null).(isset($datagrid["options"]['methods'])? ','.$datagrid["options"]['methods']:null); ?>' style="<?php echo ($datagrid["style"]); ?>" ><thead><tr><?php if(is_array($datagrid["fields"])):foreach ($datagrid["fields"] as $key=>$arr):if(isset($arr['formatter'])):unset($arr['formatter']);endif;if(isset($arr['methods'])):unset($arr['methods']);endif;if(isset($arr['editor'])):unset($arr['editor']);endif;echo "<th data-options='".trim(json_encode($arr), '{}[]').(isset($datagrid["fields"][$key]['formatter'])?",\"formatter\":".$datagrid["fields"][$key]['formatter']:null).(isset($datagrid["fields"][$key]['editor'])?",\"editor\":".$datagrid["fields"][$key]['editor']:null).(isset($datagrid["fields"][$key]['methods'])?",".$datagrid["fields"][$key]['methods']:null)."'>".$key."</th>";endforeach;endif; ?></tr></thead></table>
        </div>
        <?php switch($type): case "true": ?><div data-options="region:'south'" style="width:100%;height: 40%">
                    <table id="<?php echo ($sub_datagrid["id"]); ?>" class="easyui-datagrid" data-options='<?php $dataOptions = array_merge(array ( 'border' => false, 'fit' => true, 'fitColumns' => true, 'rownumbers' => true, 'singleSelect' => true, 'pagination' => true, 'pageList' => array ( 0 => 20, 1 => 50, 2 => 100, ), 'pageSize' => 20, ), $sub_datagrid["options"]);if(isset($dataOptions['toolbar']) && substr($dataOptions['toolbar'],0,1) != '#'): unset($dataOptions['toolbar']); endif;if(isset($dataOptions['methods'])): unset($dataOptions['methods']);endif; echo trim(json_encode($dataOptions), '{}[]').((isset($sub_datagrid["options"]['toolbar']) && substr($sub_datagrid["options"]['toolbar'],0,1) != '#')?',"toolbar":'.$sub_datagrid["options"]['toolbar']:null).(isset($sub_datagrid["options"]['methods'])? ','.$sub_datagrid["options"]['methods']:null); ?>' style="<?php echo ($sub_datagrid["style"]); ?>" ><thead><tr><?php if(is_array($sub_datagrid["fields"])):foreach ($sub_datagrid["fields"] as $key=>$arr):if(isset($arr['formatter'])):unset($arr['formatter']);endif;if(isset($arr['methods'])):unset($arr['methods']);endif;if(isset($arr['editor'])):unset($arr['editor']);endif;echo "<th data-options='".trim(json_encode($arr), '{}[]').(isset($sub_datagrid["fields"][$key]['formatter'])?",\"formatter\":".$sub_datagrid["fields"][$key]['formatter']:null).(isset($sub_datagrid["fields"][$key]['editor'])?",\"editor\":".$sub_datagrid["fields"][$key]['editor']:null).(isset($sub_datagrid["fields"][$key]['methods'])?",".$sub_datagrid["fields"][$key]['methods']:null)."'>".$key."</th>";endforeach;endif; ?></tr></thead></table>
                </div><?php break;?>
            <?php default: endswitch;?>


<!-- layout-south-tabs -->

</div>
<!-- dialog -->

<!-- toolbar -->

    <?php if($model == 'rule' ): ?><div id="<?php echo ($id_list["toolbar"]); ?>">
            <form id="<?php echo ($id_list["form"]); ?>">
                <div style="display: none;"><input hidden="true" name="search[model]" value="<?php echo ($value["model"]); ?>"/></div>
                <div class="form-div">
                    <label class="four-character-width">仓库</label><select class="easyui-combobox sel" name="search[warehouse_id]" data-options="editable:false">
                        <option value="all">全部</option>
                            <?php if(is_array($warehouse)): $i = 0; $__LIST__ = $warehouse;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?><option value="<?php echo ($v["id"]); ?>"><?php echo ($v["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                     </select>
                    <label class="four-character-width">品牌</label><select class="easyui-combobox sel" name="search[brand_id]" data-options="editable:false">
                    <option value="all">全部</option>
                    <?php if(is_array($goods_brand)): $i = 0; $__LIST__ = $goods_brand;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                </select>
                    <label class="four-character-width">货品编码</label><input class="easyui-textbox txt" type="txt" name="search[goods_no]"/>
                    <label class="four-character-width">条形码</label><input class="easyui-textbox txt" type="txt" name="search[barcode]"/>
                </div>
                    <div class="form-div">
                        <label class="four-character-width">货品名称</label><input class="easyui-textbox txt" type="txt" name="search[goods_name]"/>
                        <label class="four-character-width">商家编码</label><input class="easyui-textbox txt" type="txt" name="search[spec_no]"/>
                        <label class="four-character-width">规格名称</label><input class="easyui-textbox txt" type="txt" name="search[spec_name]"/>
                        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-search'" onclick="<?php echo ($prefix); ?>goods_spec_select.submitSearchForm(this)">搜索</a>
                        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-redo'" onclick="<?php echo ($prefix); ?>goods_spec_select.loadFormData()">重置</a>
                        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-ok'" onclick="<?php echo ($prefix); ?>goods_spec_select.selectChoose()">多选</a>
                    </div>
            </form>
            <input type="hidden" id="<?php echo ($id_list["hidden_flag"]); ?>" value="1">
        </div>
        <?php else: ?>
        <div id="<?php echo ($id_list['toolbar']); ?>">
            <form id="<?php echo ($id_list['form']); ?>">
                <div style="display: none;">
                    <input hidden="true" name="search[warehouse_id]" value="<?php echo ($value["warehouse_id"]); ?>"/>
                    <input hidden="true" name="search[model]" value="<?php echo ($value["model"]); ?>"/>
                </div>
                <div class="form-div">
                    <label class="">　　品牌</label><select class="easyui-combobox sel" name="search[brand_id]" data-options="editable:false">
                    <option value="all">全部</option>
                    <?php if(is_array($goods_brand)): $i = 0; $__LIST__ = $goods_brand;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                </select>
                    <label class="">　　货品编码</label><input class="easyui-textbox txt" type="txt" name="search[goods_no]"/>
                    <label class="">　　货品名称</label><input class="easyui-textbox txt" type="txt" name="search[goods_name]"/>
                    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-search'" onclick="<?php echo ($prefix); ?>goods_spec_select.submitSearchForm(this)">搜索</a>
                    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-redo'" onclick="<?php echo ($prefix); ?>goods_spec_select.loadFormData()">重置</a>
                    <?php if($type != 0): ?><a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-ok'" onclick="<?php echo ($prefix); ?>goods_spec_select.selectChoose()">多选</a><?php endif; ?>
                </div>
                <div class="form-div">
                    <label class="">商家编码</label><input class="easyui-textbox txt" type="txt" name="search[spec_no]"/>
                    <label class="">　　规格名称</label><input class="easyui-textbox txt" type="txt" name="search[spec_name]"/>
                    <label class="">　　条形码</label>　<input class="easyui-textbox txt" type="txt" name="search[barcode]"/>
                    <?php if($model != 'goodsSpecBarcode' ): ?><label>是否允许负库存出库：</label><input extend_type="complex-check" onclick="$(this).triStateCheckbox('click')" name="search[is_allow_neg_stock]" value="" type="checkbox" /><?php endif; ?>
                </div>
            </form>
        </div><?php endif; ?>

<!-- layout-south-tabs, call add_tabs js function to add tabs -->

    <script type="text/javascript">
        //# sorceURL=select_temp.js
        $(function () {
            setTimeout(function () {
                <?php echo ($prefix); ?>goods_spec_select = new RichDatagrid(JSON.parse('<?php echo ($params); ?>'));
                <?php echo ($prefix); ?>goods_spec_select.setFormData();
                if ("<?php echo ($type); ?>") {
                    var dg = $("#" + "<?php echo ($id_list["datagrid"]); ?>");
                    var sub_dg = $("#" + "<?php echo ($id_list["sub_datagrid"]); ?>").datagrid().datagrid('enableCellEditing');
                    dg.datagrid("options").onDblClickRow = function (index, row) {
                        var rows = sub_dg.datagrid("getRows");
                        for (var x in rows) {
                            if (rows[x].id == row.id) {
                                messager.alert("该数据已添加");
                                return false;
                            }
                        }
                        row.num = 1;
                        sub_dg.datagrid("appendRow", row);
                    }
                }
                <?php echo ($prefix); ?>goods_spec_select.selectChoose = function () {
                    var dg = $("#" + "<?php echo ($id_list["datagrid"]); ?>");
                    var dg_rows = dg.datagrid("getSelections");
                    if (dg_rows == undefined || dg_rows.length == 0 || dg_rows == null) {
                        messager.alert("请选择需要添加的货品再点击！");
                    }
                    var sub_dg = $("#" + "<?php echo ($id_list["sub_datagrid"]); ?>");
                    var is_repet = false;
                    for(i in dg_rows) {
                        var rows = sub_dg.datagrid("getRows");
                        for (var x in rows) {
                            if (rows[x].id == dg_rows[i].id) {
                                var repet = i;
                                is_repet = true;
                                continue;
                            }
                        }
                        if(repet != i){
                            dg_rows[i].num = 1;
                            sub_dg.datagrid("appendRow", dg_rows[i]);
                        }
                    }
                    if(is_repet){
                        messager.alert("有重复数据,未重复数据已添加");
                    }
                }
            }, 0);
        });
    </script>

</body>
</html>