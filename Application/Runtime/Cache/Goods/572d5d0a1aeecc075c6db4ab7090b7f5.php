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

    <div id="<?php echo ($id_list["add"]); ?>"></div>
    <div id="<?php echo ($id_list["edit"]); ?>"></div>
    <div id="<?php echo ($id_list["fileDialog"]); ?>" class="easyui-panel" style="padding:25px 50px 25px 50px">
        <form id="<?php echo ($id_list["fileForm"]); ?>" method="post" enctype="multipart/form-data">
            <div style="margin-bottom:25px">
                <input class="easyui-filebox" name="file" data-options="prompt:'请选择文件...','buttonText':'请选择文件'" style="width:100%;">
            </div>
            <div align="center">
                <a href="javascript:void(0)" class="easyui-linkbutton" style="width:50%" onclick="goodsbarcode_obj.upload()">上传</a>
            </div>
        </form>
    </div>

<!-- toolbar -->

    <div id="<?php echo ($id_list["tool_bar"]); ?>" style="padding:5px;height:auto">
        <form id="goods-form" class="easyui-form" method="post">
            <div class="form-div">
                <label>类型：</label><select class="easyui-combobox sel" id="type" name="search[type]" data-options="panelHeight:'100px',editable:false " style="width: 140px;">
                <option value="3">全部</option>
                <option value="1">单品</option>
                <option value="2">组合装</option>
            </select>
                <label>条码：</label><input class="easyui-textbox txt" type="text" name="search[barcode]"/>
                <label>商家编码：</label><input id="spec_no" class="easyui-textbox txt" type="text" name="search[spec_no]"/>
                <label>是否是主条码：</label><select class="easyui-combobox sel" id="is_master" name="search[is_master]" data-options="panelHeight:'100px',editable:false " style="width: 140px;">
                <option value="all">全部</option>
                <option value="1">是</option>
                <option value="0">否</option>
            </select>
                <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-search'" onclick="goodsbarcode_obj.submitSearchForm(this);">搜索</a>
                <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-redo'" onclick="goodsbarcode_obj.loadFormData();">重置</a>
            </div>
        </form>
        <input type="hidden" id="hidden-flag" value="1">
        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-add',plain:true" onclick="goodsbarcode_obj.add()">新建条码</a>
        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-edit',plain:true" onclick=goodsbarcode_obj.edit()>编辑条码</a>
        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-remove',plain:true" onclick="goodsbarcode_obj.remove('GoodsBarcode')">删除条码</a>
        <a href="javascript:void(0)" class="easyui-menubutton" data-options="iconCls:'icon-database-go',plain:true,menu:'#goods_barcode_export'" >导出功能</a>
        <div id="goods_barcode_export">
            <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-excel',plain:true" onclick="goodsbarcode_obj.exportToExcel('csv')">导出Csv(推荐)</a>
            <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-excel',plain:true" onclick="goodsbarcode_obj.exportToExcel('excel')">导出到Excel</a>

        </div>
        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-excel',plain:true" onclick="goodsbarcode_obj.uploadDialog()">导入条码</a>
        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-down_tmp',plain:true" onclick="goodsbarcode_obj.downloadTemplet()">下载条码模板</a>
    </div>
    </div>
    <script type="text/javascript">
        //# sourceURL=goodsbarcode.js
        $(function () {
            setTimeout(function () {
                goodsbarcode_obj = new RichDatagrid(JSON.parse('<?php echo ($params); ?>'));
                goodsbarcode_obj.setFormData();
                goodsbarcode_obj.uploadDialog = function () {
                    var dialog = $("#<?php echo ($id_list["fileDialog"]); ?>");
                    dialog.dialog({
                        title: "导入条码",
                        width: "350px",
                        height: "160px",
                        modal: true,
                        closed: false,
                        inline: true,
                        iconCls: 'icon-save',
                    });
                }
                goodsbarcode_obj.downloadTemplet = function(){
                    var url= "<?php echo U('GoodsBarcode/downloadBarcodeTemplet');?>";
                    if (!!window.ActiveXObject || "ActiveXObject" in window){
                        messager.confirm('IE浏览器下文件名会中文乱码，确定下载模板吗？',function(r){
                            if(!r){return false;}
                            window.open(url);
                        })
                    }else{
                        messager.confirm('确定下载条码模板吗？',function(r){
                            if(!r){return false;}
                            window.open(url);
                        })
                    }
                }
                goodsbarcode_obj.upload = function () {
                    var form = $("#<?php echo ($id_list["fileForm"]); ?>");
                    var url = "<?php echo U('GoodsBarcode/uploadBarcodeExcel');?>";
                    var dg = $("#<?php echo ($id_list["datagrid"]); ?>");
                    var dialog = $("#<?php echo ($id_list["fileDialog"]); ?>");
                    $.messager.progress({
                        title: "请稍后",
                        msg: "该操作可能需要几分钟，请稍等...",
                        text: "",
                        interval: 100
                    });
                    form.form("submit", {
                        url: url,
                        success: function (res) {
                            $.messager.progress('close');
                            res = JSON.parse(res);
                            if (!res.status) {
                                dg.datagrid("reload");
                                messager.alert(res.info);
                            } else if (res.status == 1) {
                                messager.alert(res.info);
                                dialog.dialog("close");
                            } else if (res.status == 2) {
                                dg.datagrid("reload");
                                $.fn.richDialog("response", res.info, "importResponse");
                            }
                            form.form("load", {"file": ""});
                        }
                    })
                }

                goodsbarcode_obj.exportToExcel = function(type){
                    var url= "<?php echo U('GoodsBarcode/exportToExcel');?>";
                    var id_list=[];
                    for(i in this.selectRows){id_list.push(this.selectRows[i].id);}
                    var search=JSON.stringify($('#goods-form').form('get'));
                    var form=JSON.stringify(goodsbarcode_obj.params.search.form_data);
                    var rows = $("#<?php echo ($id_list["datagrid"]); ?>").datagrid("getRows");
                    if(rows==''){
                        messager.confirm('导出不能为空！');
                    }
                    else if(id_list!=''){
                        messager.confirm('确定导出选中的条形码吗？',function(r){
                            if(!r){return false;}
                            window.open(url+'?id_list='+id_list+'&type='+type);
                        })
                    }
                    else if(search==form){
                        messager.confirm('确定导出所有的条形码吗？',function(r){
                            if(!r){return false;}
                            window.open(url+'?id_list='+id_list+'&type='+type);
                        })
                    }
                    else{
                        messager.confirm('确定导出搜索的条形码吗？',function(r){
                            if(!r){return false;}
                            window.open(url+'?id_list='+id_list+'&'+'search='+search+'&type='+type);
                        })
                    }
                }
            }, 0);
        });
    </script>

<!-- layout-south-tabs, call add_tabs js function to add tabs -->


</body>
</html>