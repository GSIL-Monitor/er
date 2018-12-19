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

    <div id="<?php echo ($id_list["print_dialog"]); ?>"></div>
    <div id="<?php echo ($id_list["file_dialog"]); ?>" class="easyui-panel" style="padding:25px 50px 25px 50px">
        <form id="<?php echo ($id_list["file_form"]); ?>" method="post" enctype="multipart/form-data">
            <div style="margin-bottom:25px">
                <input class="easyui-filebox" name="file" data-options="prompt:'请选择文件...','buttonText':'请选择文件'" style="width:100%;">
            </div>
            <div align="center">
                <a href="javascript:void(0)" class="easyui-linkbutton" style="width:50%" onclick="goodsBarcodePrint.upload()">上传</a>
            </div>
        </form>
    </div>

<!-- toolbar -->

    <div id="<?php echo ($id_list["tool_bar"]); ?>" style="padding:5px;height:auto">
        <div class="form-div">
            <a href="javascript:void(0)" class="easyui-linkbutton" name="add_spec" data-options="iconCls:'icon-add',plain:true">添加单品</a>
            <a href="javascript:void(0)" class="easyui-linkbutton" name="add_suite" data-options="iconCls:'icon-add',plain:true">添加组合装</a>
            <a href="javascript:void(0)" class="easyui-linkbutton" name="add_stockin_order" data-options="iconCls:'icon-add',plain:true">添加入库单</a>
            <a href="javascript:void(0)" class="easyui-linkbutton" name="clear_info" data-options="iconCls:'icon-redo',plain:true">清空</a>
            <a 		class="easyui-linkbutton"	name="delete_goods"	href="javascript:void(0)"  	data-options="iconCls:'icon-remove',plain:true">删除</a>
            <a href="javascript:void(0)" class="easyui-menubutton" data-options="iconCls:'icon-excel',plain:true,menu:'#goods_barcode_print'" >条码信息导入</a>
            <div style="display: inline-block;border: 1px solid #999999;padding: 1px;margin-left: 20px"><label >打印次数：</label><input style="width: 50px;" name="print_num"  class="easyui-numberbox" value="1" data-options="min:1,precision:0,required:true"></input>
            <a href="javascript:void(0)" class="easyui-linkbutton" name="apply_all_row" data-options="iconCls:'icon-forward',plain:true" >应用到所有行</a></div>
            <a href="javascript:void(0)" class="easyui-linkbutton" name="goods_barcode_print" data-options="iconCls:'icon-print'" >打印条码</a>
            <a href="<?php echo ($faq_url); ?>" target="_blank" class="easyui-linkbutton" title="点击查看常见问题" data-options="iconCls:'icon-help',plain:true">常见问题</a>
            <div id="goods_barcode_print">
                <a href="javascript:void(0)" class="easyui-linkbutton"data-options="iconCls:'icon-down_tmp',plain:true" name="gb_print_upload">导入条码货品</a>
                <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-excel',plain:true" name="gb_print_template" >下载导入模板</a>
            </div>
        </div>
    </div>
    <script type="text/javascript">
//# sourceURL=goodsbarcodeprint.js
var goodsbarcodeWs;
        (function(){
            var select_id = "<?php echo ($id_list["tool_bar"]); ?>";
            var select_box = {
                'add_spec'                      : $('#'+select_id+" a[name='add_spec']"),
                'add_suite'                     : $('#'+select_id+" a[name='add_suite']"),
                'add_stockin_order'             : $('#'+select_id+" a[name='add_stockin_order']"),
                'clear_info'                    : $('#'+select_id+" a[name='clear_info']"),
                'delete_goods'                  : $('#'+select_id+" a[name='delete_goods']"),
                'gb_print_upload'               : $('#'+select_id+" a[name='gb_print_upload']"),
                'gb_print_template'             : $('#'+select_id+" a[name='gb_print_template']"),
                'print_num'                     : $('#'+select_id+" input[name='print_num']"),
                'apply_all_row'                 : $('#'+select_id+" a[name='apply_all_row']"),
                'goods_barcode_print'           : $('#'+select_id+" a[name='goods_barcode_print']"),
            };
            goodsBarcodePrint = {
                select_box :select_box,
                'id_list' : JSON.parse('<?php echo ($params); ?>'),
                addSpec: function(){
                    $('#' + goodsBarcodePrint.id_list.add_spec.id).richDialog('goodsSpec', goodsBarcodePrint.submitGoodsSpecDialog,{
                        'prefix':'gb_print',
                        'model':'goodsSpecBarcode',
                    },goodsBarcodePrint);
                },
                addSuite:function(){
                    $('#'+goodsBarcodePrint.id_list.add_suite.id).richDialog('goodsSuite', goodsBarcodePrint.submitGoodsSuiteDialog, {pre:'gb_print',model:'goodssuitbarcode'}, goodsBarcodePrint);
                },
                addStockinOrder:function(){
                    $('#'+goodsBarcodePrint.id_list.add_stockin_order.id).richDialog('stockinOrder', goodsBarcodePrint.submitStockinOrderDialog, {pre:'gb_print',model:'stockinorderbarcode'}, goodsBarcodePrint);
                },
                importInfo:function(){
                    var dialog = $("#<?php echo ($id_list["file_dialog"]); ?>");
                    dialog.dialog({
                        title: "条码导入",
                        width: "350px",
                        height: "160px",
                        modal: true,
                        closed: false,
                        inline: true,
                        iconCls: 'icon-save',
                    });
                },
                upload : function(){
                    var form = $("#<?php echo ($id_list["file_form"]); ?>");
                    var url = "<?php echo U('GoodsBarcodePrint/uploadExcel');?>";
                    var dg = $("#<?php echo ($id_list["datagrid"]); ?>");
                    var dialog = $("#<?php echo ($id_list["file_dialog"]); ?>");
                    $.messager.progress({
                        title: "请稍候",
                        msg: "该操作可能需要几分钟，请稍等...",
                        text: "",
                        interval: 100
                    });
                    form.form("submit", {
                        url: url,
                        success: function (res) {
                            $.messager.progress('close');
                            res = JSON.parse(res);
                            if (res.status==1) {
                                messager.alert(res.msg);
                            } else if (res.status == 0) {
                                for(var i in res.data.rows){
                                    dg.datagrid("appendRow",res.data.rows[i]);
                                }
                                dialog.dialog("close");
                            }else if(res.status == 2){
                                for(var i in res.data.rows){
                                    dg.datagrid("appendRow",res.data.rows[i]);
                                }
                                $.fn.richDialog("response", res.fail, "importResponse");
                                dialog.dialog("close");
                            }else if(res.status == 3){
                                for(var i in res.data.rows){
                                    dg.datagrid("appendRow",res.data.rows[i]);
                                }
                                $.fn.richDialog("response", res.repeat, "importResponse");
                                dialog.dialog("close");
                            }
                            form.form("load", {"file": ""});
                        }
                    });
                },
                downloadTemplate:function(){
                    var url= "<?php echo U('GoodsBarcodePrint/downloadTemplet');?>";
                    if (!!window.ActiveXObject || "ActiveXObject" in window){
                        messager.confirm('IE浏览器下文件名会中文乱码，确定下载模板吗？',function(r){
                            if(!r){return false;}
                            window.open(url);
                        })
                    }else{
                        messager.confirm('确定下载模板吗？',function(r){
                            if(!r){return false;}
                            window.open(url);
                        })
                    }
                },
                deleteGoods:function(){
                    var delete_rows = $('#'+goodsBarcodePrint.id_list.datagrid).datagrid('getSelections');
                    if($.isEmptyObject(delete_rows)){
                        messager.alert('请选择操作的行');
                        return;
                    }
                    messager.confirm('确认要清除选择的行吗',function(r){
                        if(r){
                            for(var i in delete_rows){
                                var delete_index = $('#'+goodsBarcodePrint.id_list.datagrid).datagrid('getRowIndex',delete_rows[i]);
                                $('#'+goodsBarcodePrint.id_list.datagrid).datagrid('deleteRow',delete_index);
                            }
                            $('#'+goodsBarcodePrint.id_list.datagrid).datagrid('loadData',$('#'+goodsBarcodePrint.id_list.datagrid).datagrid('getData'))

                        }
                    });
                                    },
                clearInfo:function(){
                    messager.confirm('确认要清空全部数据吗',function(r){
                        if(r){
                            $('#'+goodsBarcodePrint.id_list.datagrid).datagrid('loadData',{'total':0,'rows':[]});
                        }
                    });
                },
                submitGoodsSpecDialog :function(up_datagrid,down_datagrid,gb_print_object){

                    var merge_result_new = [];
                    var error_list = [];
                    //获取对话框中的添加的数据
                    var new_rows = $("#"+down_datagrid).datagrid("getRows");
                    var formated_new_rows = utilTool.array2dict(new_rows,['spec_no','barcode'],'');
                    //获取入库开单中原有数据
                    var print_datagrid = gb_print_object.id_list.datagrid;

                    var old_rows = $('#' + print_datagrid).datagrid('getRows');
                    var old_rows_spec = [];
                    for(var s in old_rows){
                        if(old_rows[s].is_suite == '否'){
                            old_rows_spec.push(old_rows[s]);
                        }
                    }
                    var formated_old_rows = utilTool.array2dict(old_rows_spec,['merchant_no','barcode'],'');
                    //过滤重复的单品列表
                    for (var j in formated_new_rows)
                    {
                        if (formated_old_rows[j] == undefined   || $.isEmptyObject(formated_old_rows))
                        {
                            if($.trim(formated_new_rows[j].barcode) == ''){
                                error_list.push({'spec_no':formated_new_rows[j].spec_no,'info':'不存在条码'});
                                continue;
                            }
                            var map_suite = {
                                'spec_id'            :formated_new_rows[j].spec_id,
                                'merchant_no'   :formated_new_rows[j].spec_no,
                                'goods_name'    :formated_new_rows[j].goods_name,
                                'goods_no'    :formated_new_rows[j].goods_no,
                                'spec_code'    :formated_new_rows[j].spec_code,
                                'spec_name'    :formated_new_rows[j].spec_name,
                                'short_name'    :formated_new_rows[j].short_name,
                                'barcode'       :formated_new_rows[j].barcode,
                                'is_suite'      :'否',
                                'is_master'      :formated_new_rows[j].is_master,
                                'print_num'      :formated_new_rows[j].num,
                                'prop1'      :formated_new_rows[j].prop1,
                                'prop2'      :formated_new_rows[j].prop2,
                                'prop3'      :formated_new_rows[j].prop3,
                                'prop4'      :formated_new_rows[j].prop4,
                            };
                            merge_result_new.push($.extend({},map_suite));
                        }
                    }
                    for(var new_key in merge_result_new){
                        $('#' + print_datagrid).datagrid('appendRow', merge_result_new[new_key]);
                    }
                    $('#' + goodsBarcodePrint.id_list.add_spec.id).dialog('close');
                    if(!$.isEmptyObject(error_list)){
                        $.fn.richDialog("response", error_list, "goods_spec");
                    }

                },
                submitGoodsSuiteDialog :function (up_datagrid,gb_print_object,down_datagrid) {
                    var merge_result_new = [];
                    var error_list = [];
                    //获取对话框中的添加的数据
                    var new_rows = $("#"+up_datagrid).datagrid("getSelections");
                    var formated_new_rows = utilTool.array2dict(new_rows,['suite_no','barcode'],'');
                    //获取入库开单中原有数据
                    var print_datagrid = gb_print_object.id_list.datagrid;
                    var old_rows = $('#' + print_datagrid).datagrid('getRows');
                    var old_rows_suite =[];
                    for(var s in old_rows){
                        if(old_rows[s].is_suite == '是'){
                            old_rows_suite.push(old_rows[s]);
                        }
                    }
                    var formated_old_rows = utilTool.array2dict(old_rows_suite,['merchant_no','barcode'],'');
                    //过滤重复的组合装列表
                    for (var j in formated_new_rows)
                    {
                        if (formated_old_rows[j] == undefined || $.isEmptyObject(formated_old_rows)  )
                        {
                            if($.trim(formated_new_rows[j].barcode) == ''){
                                error_list.push({'spec_no':formated_new_rows[j].suite_no,'info':'不存在条码'});
                                continue;
                            }
                            var map_suite = {
                                'merchant_no'   :formated_new_rows[j].suite_no,
                                'goods_name'    :formated_new_rows[j].suite_name,
                                'barcode'       :formated_new_rows[j].barcode,
                                'is_master'      :formated_new_rows[j].is_master,
                                'is_suite'      :'是',
                                'print_num'      :1,
                            };

                            merge_result_new.push($.extend({},map_suite));
                        }
                    }
                    for(var new_key in merge_result_new){
                        $('#' + print_datagrid).datagrid('appendRow', merge_result_new[new_key]);
                    }
                    $('#' + goodsBarcodePrint.id_list.add_suite.id).dialog('close');
                    if(!$.isEmptyObject(error_list)){
                        $.fn.richDialog("response", error_list, "goods_spec");
                    }
                },
                submitStockinOrderDialog :function (up_datagrid,gb_print_object) {

                    var merge_result_new = [];
                    var error_list = [];
                    //获取对话框中的添加的数据
                    var new_rows = $("#"+up_datagrid).datagrid("getRows");
                    var formated_new_rows = utilTool.array2dict(new_rows,['spec_no','barcode'],'');
                    //获取入库开单中原有数据
                    var print_datagrid = gb_print_object.id_list.datagrid;
                    $('#' + print_datagrid).datagrid('loadData',{'total':0,'rows':[]});
                    var old_rows = $('#' + print_datagrid).datagrid('getRows');
                    var old_rows_spec = [];
                    for(var s in old_rows){
                        if(old_rows[s].is_suite == '否'){
                            old_rows_spec.push(old_rows[s]);
                        }
                    }
                    var formated_old_rows = utilTool.array2dict(old_rows_spec,['merchant_no','barcode'],'');
                    //过滤重复的入库单详情单品列表
                    for (var j in formated_new_rows)
                    {
                        if (formated_old_rows[j] == undefined || $.isEmptyObject(formated_old_rows)  )
                        {
                            if($.trim(formated_new_rows[j].barcode) == ''){
                                error_list.push({'spec_no':formated_new_rows[j].spec_no,'info':'不存在条码'});
                                continue;
                            }
                            var map_suite = {
                                'spec_id'            :formated_new_rows[j].spec_id,
                                'merchant_no'   :formated_new_rows[j].spec_no,
                                'goods_name'    :formated_new_rows[j].goods_name,
                                'goods_no'    :formated_new_rows[j].goods_no,
                                'spec_code'    :formated_new_rows[j].spec_code,
                                'spec_name'    :formated_new_rows[j].spec_name,
                                'short_name'    :formated_new_rows[j].short_name,
                                'barcode'       :formated_new_rows[j].barcode,
                                'is_suite'      :'否',
                                'is_master'      :formated_new_rows[j].is_master,
                                'print_num'      :formated_new_rows[j].num,
                            };
                            merge_result_new.push($.extend({},map_suite));
                        }
                    }
                    for(var new_key in merge_result_new){
                        $('#' + print_datagrid).datagrid('appendRow', merge_result_new[new_key]);
                    }
                    $('#' + goodsBarcodePrint.id_list.add_suite.id).dialog('close');
                    if(!$.isEmptyObject(error_list)){
                        $.fn.richDialog("response", error_list, "goods_spec");
                    }
                },
                applyPrintNum:function(){
                    var num = select_box.print_num.numberbox('getValue');
                    if(isNaN(parseInt(num))){
                        messager.alert("请填写打印次数")
                    }else{
                        var rows = $('#'+goodsBarcodePrint.id_list.datagrid).datagrid('getRows');
                        for(var i in rows){
                            var index = $('#'+this.id_list.datagrid).datagrid('getRowIndex',rows[i]);
                            $('#'+this.id_list.datagrid).datagrid('updateRow',{index:index,row:{print_num:parseInt(num)}});
                        }
                    }


                },
                printGoodsBarcodeDialog:function(){
                    var rows = $('#'+goodsBarcodePrint.id_list.datagrid).datagrid('getSelections');
                    if($.isEmptyObject(rows)){
                        messager.alert('请先选择需要打印的行!');
                        return false;
                    }
                    var print_dialog = '<?php echo ($id_list["print_dialog"]); ?>';
                    Dialog.show('<?php echo ($id_list["print_dialog"]); ?>','打印条形码',"<?php echo U('GoodsBarcodePrint/printBarcode');?>",240,350,[{text:"取消",handler:function(){$('#'+print_dialog).dialog('close');}}]);

                },
                changeTemplatePage:function(){
                    open_menu('打印模板(新)','<?php echo U("Setting/NewPrintTemplate/getNewPrintTemplate");?>');
                    $('#<?php echo ($id_list["print_dialog"]); ?>').dialog('close');
                },
                newSelectPrinter:function(){
//                    this.connectStockWS();
                    var request = {
                        "cmd":"getPrinters",
                        "requestID":"123458976"+"99",
                        "version":"1.0",
                    }
                    goodsbarcodeWs.send(JSON.stringify(request));
                },
                printerSetting:function(){
                    this.connectStockWS();
                    var request = {
                        "cmd":"printerConfig",
                        "requestID":"123458976",
                        "version":"1.0",}
                    goodsbarcodeWs.send(JSON.stringify(request));
                },
                connectStockWS:function(){
                    if(goodsbarcodeWs == undefined){
                        goodsbarcodeWs = new WebSocket("ws://127.0.0.1:13528");
                        goodsbarcodeWs.onmessage = function(event){goodsBarcodePrint.onStockMessage(event);};//this.onStockMessage;
                        goodsbarcodeWs.onerror = function(){goodsBarcodePrint.onStockError();};//this.onStockError;
                    }
                    return ;
                },
                onStockMessage:function(event){
                    var response_result =JSON.parse(event.data);
                    if(!$.isEmptyObject(response_result.status) && response_result.status != 'success'){
                        messager.alert(response_result.msg);
                        return;
                    }
                    if(!$.isEmptyObject(response_result))
                    {
                        switch(response_result.cmd){
                            case 'getPrinters':/*打印机列表命令*/
                            {
                                var type = response_result.requestID;
                                type = type.substr(type.length-2,type.length);
                                if(type == 99){
                                    goodsBarcodePrint.select_box.printer_list.combobox({
                                        valueField: 'name',
                                        textField: 'name',
                                        data: response_result.printers,
                                        value: response_result.defaultPrinter
                                    });
                                    goodsBarcodePrint.select_box.printer_list.combobox('reload');
                                }
                                break;
                            }
                            case 'print':
                            {
                                var taskID = response_result.taskID+"";
                                taskID = taskID.substr(taskID.length-3,taskID.length);
                                if(taskID==231)
                                {
                                    var preview;
                                    preview = response_result.previewURL;
                                    if(!$.isEmptyObject(preview))
                                        window.open(response_result.previewURL);
                                    preview = response_result.previewImage;
                                    if(!$.isEmptyObject(preview)&&(preview.length != 0))
                                        window.open(response_result.previewImage[0]);
                                }
                                break;
                            }
                            case 'notifyPrintResult':
                            {
                                if(response_result.taskStatus == "printed"){
                                    var type = response_result.taskID;
                                    type = type.substr(type.length-2,type.length);
                                    if(type==13){
                                        messager.alert("打印条码完成");
                                        $('#print_barcode').linkbutton({text:'打印',disabled:false});
                                    }
                                    $("#<?php echo ($id_list["print_dialog"]); ?>").dialog('close');
                                }else if(response_result.taskStatus == "failed"){
                                    messager.alert("打印失败");
                                    $('#print_barcode').linkbutton({text:'打印',disabled:false});
                                }
								//$.messager.progress('close');
                                break;
                            }
                        }

                    }
                },
                onStockError:function(){
                    //$('#print_barcode').linkbutton({text:'打印',disabled:false});
                    goodsbarcodeWs = null;
                    var print_dialog = '<?php echo ($id_list["print_dialog"]); ?>';
                    $('#'+print_dialog).dialog({
                        title: '打印组件错误',
                        width: 400,
                        height: 200,
                        closed: false,
                        cache: false,
                        href:  "<?php echo U('Stock/StockSalesPrint/onWSError');?>",
                        modal: true
                    });
                },
                previewGoodsBarcode:function(){
                    var that = this;
                    var templateId = goodsBarcodePrint.select_box.template_list.combobox('getValue');
                    if(templateId == ""){
                        messager.alert("预览错误：没有选择模板，请到模板列表页面下载模板");
                        $("#<?php echo ($id_list["print_dialog"]); ?>").dialog('close');
                        return ;
                    }
                    var contents = goodsBarcodePrint.template_contents;
                    var rows = $('#'+goodsBarcodePrint.id_list.datagrid).datagrid('getSelections');
                    var barcode_print_warehouse = $('#barcode_print_warehouse').combobox('getValues');
                    var rows_id_list = '';
                    for(var i=0; i<rows.length; i++){
                        if(rows[i].is_suite == '否'){rows_id_list += rows[i].spec_id + ',';}
                    }
                    rows_id_list = rows_id_list.substr(0,rows_id_list.length-1);
                    $.post("<?php echo U('Goods/GoodsBarcodePrint/getPrintWarehousePosition');?>",{spec_ids:rows_id_list,warehouse_id:barcode_print_warehouse},function(ret) {
                        var datas = that.getGoodsBarcodeData(contents, templateId,ret);
                        if (datas === false) {
                            return;
                        }
                        that.connectStockWS();
                        var requestID = parseInt(1000 * Math.random());
                        var request = {
                            'cmd': 'print',
                            'version': '1.0',
                            'requestID': requestID,
                            'task': {
                                'taskID': requestID + '' + '231',
                                'printer': "",
                                'preview': true,
                                'previewType': 'image',
                                'documents': datas
                            }
                        };
                        goodsbarcodeWs.send(JSON.stringify(request));
                    });
                },
                getGoodsBarcodeData:function(contents,templateId,print_warehouse_position){
                    contents = JSON.parse(contents[templateId]);
                    var templateURL = contents.custom_area_url;
                    var rows = $('#'+goodsBarcodePrint.id_list.datagrid).datagrid('getSelections');
                    if($.isEmptyObject(rows)){
                        messager.alert('请先选择需要打印的行!');
                        return false;
                    }
                    var datas = [],row;
                    var now_date = new Date();
                    var now_millisecond = now_date.getTime();
                    var ID = 0;
                    var barcode_print_warehouse = $('#barcode_print_warehouse').combobox('getText');
                    for (var j = 0; j < rows.length; ++j){
                        row = rows[j];
                        if(print_warehouse_position == null){
                            print_warehouse_position = [];
                            barcode_print_warehouse='';
                        }
                        for (var k = 0; k < row.print_num; ++k)
                        {
                            ID++;
                            datas.push({
                                'documentID' : now_millisecond.toString().concat(ID.toString()),
                                'contents' : [
                                    {
                                        'templateURL' : templateURL,
                                        'data' : {
                                            goodsbarcode :{
                                                merchant_no : $.isEmptyObject(row.merchant_no)?'无':row.merchant_no,
                                                goods_no   : $.isEmptyObject(row.goods_no)?'无':row.goods_no,
                                                goods_name : $.isEmptyObject(row.goods_name)?'无':row.goods_name,
                                                spec_name : $.isEmptyObject(row.spec_name)?'无':row.spec_name,
                                                spec_code : $.isEmptyObject(row.spec_code)?'无':row.spec_code,
                                                short_name : $.isEmptyObject(row.short_name)?'无':row.short_name,
                                                barcode : $.isEmptyObject(row.barcode)?'无':row.barcode,
                                                is_suite : $.isEmptyObject(row.is_suite)?'无':row.is_suite,
                                                prop1 : $.isEmptyObject(row.prop1)?'无':row.prop1,
                                                prop2 : $.isEmptyObject(row.prop2)?'无':row.prop2,
                                                prop3 : $.isEmptyObject(row.prop3)?'无':row.prop3,
                                                prop4 : $.isEmptyObject(row.prop4)?'无':row.prop4,
                                                position_no : row['is_suite']=='是'?'':print_warehouse_position[row.spec_id],
                                                warehouse : row['is_suite']=='是'?'':barcode_print_warehouse
                                            }
                                        }
                                    }
                                ]
                            });
                        }
                    }
                    return datas;
                },
                printGoodsBarcode:function(){
                    var that = this;
                    var rows = $('#'+goodsBarcodePrint.id_list.datagrid).datagrid('getSelections');
                    if($.isEmptyObject(rows)){
                        messager.alert('请先选择需要打印的行!');
                        return false;
                    }
                    var printer = goodsBarcodePrint.select_box.printer_list.combobox('getValue');
                    var templateId = goodsBarcodePrint.select_box.template_list.combobox('getValue');
                    if(templateId == ""){
                        messager.alert("预览错误：没有选择模板，请到模板列表页面下载模板");
                        $("#<?php echo ($id_list["print_dialog"]); ?>").dialog('close');
                        return ;
                    }
                    $('#print_barcode').linkbutton({text:'打印中...',disabled:true});
                    var barcode_print_warehouse = $('#barcode_print_warehouse').combobox('getValues');
                    var rows_id_list = '';
                    for(var i=0; i<rows.length; i++){
                        if(rows[i].is_suite == '否'){rows_id_list += rows[i].spec_id + ',';}
                    }
                    rows_id_list = rows_id_list.substr(0,rows_id_list.length-1);
                    $.post("<?php echo U('Goods/GoodsBarcodePrint/getPrintWarehousePosition');?>",{spec_ids:rows_id_list,warehouse_id:barcode_print_warehouse},function(ret){
                        var print_warehouse_position = ret;
                        var contents = goodsBarcodePrint.template_contents;
                        var datas = that.getGoodsBarcodeData(contents,templateId,print_warehouse_position);
                        if(datas === false){
                            return;
                        }
                        that.connectStockWS();
                        var requestID =  parseInt(1000*Math.random());
                        var request = {
                            'cmd' : 'print',
                            'version' : '1.0',
                            'requestID' : requestID,
                            'task' : {
                                'taskID' : requestID +''+'13',
                                'printer' : printer,//'',
                                'preview' : false,
                                'notifyMode':'allInOne',
                                'documents' : datas
                            }
                        };
                        goodsbarcodeWs.send(JSON.stringify(request));
                    });

                },
                onPrinterSelect:function(printer_name){
                    var templateId = goodsBarcodePrint.select_box.template_list.combobox('getValue');
                    var contents = goodsBarcodePrint.template_contents;
                    var content = contents[templateId];
                    if(content.defaultPrinter != undefined && content.default_printer == printer_name)
                        return;
                    else
                        messager.confirm("您确定把\""+printer_name+"\"设置为此模板的打印机么？",function(r){
                            if(r){
                                goodsBarcodePrint.setDefaultPrinter(content,printer_name,templateId);
                            }
                        });
                },
                setDefaultPrinter:function(content,printor,templateId){
                    content = JSON.parse(content);
                    content.default_printer = printor;
                    $.post("<?php echo U('Goods/GoodsBarcodePrint/setDefaultPrinter');?>",{content:JSON.stringify(content),templateId:templateId},function(ret){
                        if(1 == ret.status){
                            messager.alert(ret.msg);
                        }else {
                            goodsBarcodePrint.template_contents[templateId] = JSON.stringify(content);
                        }
                    });
                },
                templateOnSelect:function () {
                    if(goodsBarcodePrint.select_box.template_list.combobox('getData').length == 0)
                    {
                        return;
                    }
                    var print_list = goodsBarcodePrint.select_box.printer_list.combobox('getData');
                    var content = JSON.parse(goodsBarcodePrint.template_contents[goodsBarcodePrint.select_box.template_list.combobox('getValue')]);
                    if(undefined != content.default_printer && JSON.stringify(print_list).indexOf(content.default_printer) != -1){
                        goodsBarcodePrint.select_box.printer_list.combobox('setValue',content.default_printer);
                    }
                }
            };
            select_box.add_spec.linkbutton({onClick:function(){
                goodsBarcodePrint.addSpec();
            }});
            select_box.add_suite.linkbutton({onClick:function(){
                goodsBarcodePrint.addSuite();
            }});
            select_box.add_stockin_order.linkbutton({onClick:function(){
                goodsBarcodePrint.addStockinOrder();
            }});
            select_box.gb_print_upload.linkbutton({onClick:function(){
                goodsBarcodePrint.importInfo();
            }});
            select_box.gb_print_template.linkbutton({onClick:function(){
                goodsBarcodePrint.downloadTemplate();
            }});
            select_box.delete_goods.linkbutton({onClick:function(){
                goodsBarcodePrint.deleteGoods();
            }});
            select_box.clear_info.linkbutton({onClick:function(){
                goodsBarcodePrint.clearInfo();
            }});

            select_box.apply_all_row.linkbutton({onClick:function(){
                goodsBarcodePrint.applyPrintNum();
            }});
            select_box.goods_barcode_print.linkbutton({onClick:function(){
                goodsBarcodePrint.printGoodsBarcodeDialog();
            }});
            setTimeout(function(){
                $('#'+'<?php echo ($id_list["datagrid"]); ?>').datagrid('enableCellEditing');
            },0)
        })();

    </script>

<!-- layout-south-tabs, call add_tabs js function to add tabs -->


</body>
</html>