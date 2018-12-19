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

    <div id="<?php echo ($id_list["fileDialog"]); ?>" class="easyui-panel" style="padding:25px 50px 25px 50px">
        <form id="<?php echo ($id_list["fileForm"]); ?>" method="post" enctype="multipart/form-data">
            <div style="margin-bottom:25px">
                <input class="easyui-filebox" name="file" data-options="prompt:'请选择文件...','buttonText':'请选择文件'" style="width:100%;">
            </div>
            <div align="center">
                <a href="javascript:void(0)" class="easyui-linkbutton" style="width:50%" onclick="history_original_trade.upload()">上传</a>
            </div>
        </form>
    </div>

<!-- toolbar -->

    <div id="<?php echo ($id_list['toolbar']); ?>" style="padding:5px; height:auto">
        <form id="<?php echo ($id_list['form']); ?>" class="easyui-form" method="post">
            <div class="form-div">
                <label class="four-character-width">系统状态：</label><input id="history_original_trade_process_status" class="easyui-combobox txt" text="txt" name="search[process_status]" value="all" data-options="valueField:'id',textField:'name',onSelect:selectValue"/>
                <label class="four-character-width">原始单号：</label><input id="history_original_trade_tid" class="easyui-textbox txt" text="txt" name="search[tid]"/>
                <label class="four-character-width">客户网名：</label><input id="history_original_trade_buyer_nick" class="easyui-textbox txt" text="txt" name="search[buyer_nick]"/>
                <label >手机：</label><input id="history_original_trade_receiver_mobile" class="easyui-numberbox txt" text="txt" name="search[receiver_mobile]"/>
                <label >店铺：</label><input id="history_original_trade_shop_id" class="easyui-combobox sel" text="txt" name="search[shop_id]" value="all" data-options="valueField:'id',textField:'name',onSelect:selectValue"/>
                <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-search'" onclick="history_original_trade.submitSearchForm(this)">搜索</a>
                <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-redo'" onclick="history_original_trade.loadFormData()">重置</a>
                <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-search'" onclick="history_original_trade.checkNumber()">查看号码</a>
                <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-excel',plain:true" onclick="history_original_trade.exportToExcel()">导出到Excel</a>
                <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-edit',plain:true" onclick="setDatagridField('Trade/Trade','originalorder','<?php echo ($datagrid["id"]); ?>',1)">设置表头</a>
            </div>
            <div id="<?php echo ($id_list["more_content"]); ?>">
                <div class="form-div">
                    <label class="four-character-width">发货条件：</label><input id="history_original_trade_delivery_term" class="easyui-combobox txt" text="txt" name="search[delivery_term]" value="all" data-options="valueField:'id',textField:'name'"/>
                    <label class="four-character-width">平台状态：</label><input id="history_original_trade_trade_status" class="easyui-combobox txt" text="txt" name="search[trade_status]" value="all" data-options="valueField:'id',textField:'name'"/>
                    <label class="four-character-width">支付状态：</label><input id="history_original_trade_pay_status" class="easyui-combobox txt" text="txt" name="search[pay_status]" value="all" data-options="valueField:'id',textField:'name'"/>
                    <label class="four-character-width">退款状态：</label><input id="history_original_trade_refund_status" class="easyui-combobox txt" text="txt" name="search[refund_status]" value="all" data-options="valueField:'id',textField:'name'"/>
                </div>
            </div>
        </form>
        <div style="display: none;"><input hidden="true" id="<?php echo ($id_list["hidden_flag"]); ?>" value="1"/> </div>
    </div>

<!-- layout-south-tabs, call add_tabs js function to add tabs -->

    <script type="text/javascript">
        //# sourceURL=OriginalTrade.js
        $(function () {
            setTimeout(function () {
                history_original_trade = new RichDatagrid(<?php echo ($params); ?>);
                history_original_trade.setFormData();
                //查看号码
                history_original_trade.checkNumber = function () {
                    var rows = history_original_trade.selectRows;
                    if (rows == undefined) {
                        messager.info('请选择操作的行');
                        return false;
                    }
                    var ids = [];
                    var list = [];
                    for (var i in rows) {
                        if (rows[i]['receiver_mobile'] == '' && rows[i]['receiver_telno'] == '') {
                            list.push({trade_no:rows[i]['tid'],result_info:'手机和固话均为空！'});
                            continue;
                        }
                        ids.push(rows[i]['id']);
                    }
                    if (ids.length > 0) {
                        $.post("<?php echo U('Trade/TradeCommon/checkNumber');?>", {
                            ids: JSON.stringify(ids),
                            key: 'api_trade_history'
                        }, function (res) {
                            history_original_trade.dealDatagridReasonRows(res,list);
                        }, 'JSON');
                    }else{
                        var res={};res.status=2;res.info={};res.info.rows=list;res.info.total=list.length;
                        history_original_trade.dealDatagridReasonRows(res,undefined);
                    }
                }
                history_original_trade.dealDatagridReasonRows = function (result,list) {
                    if (result.status == 1) {
                        messager.alert(result.message);
                        return;
                    }
                    if(list!=undefined&&list.length>0){
                        var fail= (typeof result.info.rows=='object')?$.makeArray(result.info.rows):result.info.rows;
                        result.info.rows=$.merge(list,fail);
                        result.info.total+=list.length;
                        result.status=2;
                    }
                    if(result.status==2){
                        result.info.title='原始单号';
                        $.fn.richDialog("response", result.info, 'checknumber');
                    }
                    if ((result.status == 0 || result.status == 2) && result.data != undefined) {
                        var rows = history_original_trade.selectRows;
                        var index;
                        var trade_dg = $('#' + history_original_trade.params.datagrid.id);
                        for (var i in rows) {
                            for (var x in result.data.rows) {
                                if (rows[i].id == result.data.rows[x].id) {
                                    index = trade_dg.datagrid('getRowIndex', rows[i]);
                                    if (result.check_number) {
                                        rows[i].receiver_mobile = result.data.rows[x].receiver_mobile;
                                        rows[i].receiver_telno = result.data.rows[x].receiver_telno;
                                        trade_dg.datagrid('refreshRow', index);
                                    }
                                }
                            }
                        }
                    }
                }
                history_original_trade.exportToExcel = function(){
                    var dg = $('#'+history_original_trade.params.datagrid.id);
                    var queryParams = dg.datagrid('options').queryParams;
                    var search=JSON.stringify(queryParams);
                    var url= "<?php echo U('HistoryOriginalTrade/exportToExcel');?>";
                    var id_list=[];
                    for(i in this.selectRows){id_list.push(this.selectRows[i].id);}
                    var form=JSON.stringify(history_original_trade.params.search.form_id);
                    var search_form=JSON.stringify($('#<?php echo ($id_list["form"]); ?>').form('get'));
                    var rows = $("#<?php echo ($id_list["datagrid"]); ?>").datagrid("getRows");
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
                // history_original_trade.submitOriginalTrade = function () {
                //     var row = $("#" + "<?php echo ($id_list[datagrid]); ?>").datagrid("getSelections");
                //     var ids = {};
                //     for (var x in row) {
                //         ids[x] = row[x]["id"];
                //     }
                //     ids = JSON.stringify(ids);
                //     var url = "<?php echo U('HistoryOriginalTrade/submitHistoryOriginalTrade');?>";
                //     $.post(url, {"id": ids}, function (res) {
                //         if (res.status == 1) {
                //             $.fn.richDialog("response", res.info, "trade");
                //             history_original_trade.refresh();
                //         } else if (res.status == 0) {
                //             history_original_trade.refresh();
                //         } else if (res.status == 2) {
                //             messager.alert(res.info);
                //         }
                //     })
                // };
                // history_original_trade.upload = function () {
                //     var form = $("#<?php echo ($id_list["fileForm"]); ?>");
                //     var url = "<?php echo U('HistoryOriginalTrade/importTrade');?>";
                //     var dg = $("#<?php echo ($id_list["datagrid"]); ?>");
                //     var dialog = $("#<?php echo ($id_list["fileDialog"]); ?>");
                //     $.messager.progress({
                //         title: "请稍后",
                //         msg: "该操作可能需要几分钟，请稍等...",
                //         text: "",
                //         interval: 100
                //     });
                //     form.form("submit", {
                //         url: url,
                //         success: function (res) {
                //             $.messager.progress('close');
                //             res = JSON.parse(res);
                //             if (!res.status) {
                //                 messager.alert(res.info);
                //             } else if (res.status == 1) {
                //                 dialog.dialog("close");
                //             } else if (res.status == 2) {
                //                 $.fn.richDialog("response", res.info, "importTrade");
                //             }
                //             dg.datagrid("reload");
                //             form.form("load", {"file": ""});
                //         }
                //     })
                // }
                $("#history_original_trade_process_status").combobox("loadData", <?php echo ($process_status); ?>);
                $("#history_original_trade_pay_status").combobox("loadData", formatter.get_data("pay_status"));
                $("#history_original_trade_delivery_term").combobox("loadData", formatter.get_data("delivery_term"));
                $("#history_original_trade_trade_status").combobox("loadData", formatter.get_data("api_trade_status"));
                $("#history_original_trade_refund_status").combobox("loadData", formatter.get_data("refund_status"));
                $("#history_original_trade_shop_id").combobox("loadData", <?php echo ($shop_list); ?>);
                // history_original_trade.uploadDialog = function () {
                //     var dialog = $("#<?php echo ($id_list["fileDialog"]); ?>")
                //     dialog.dialog({
                //         title: "导入原始订单",
                //         width: "350px",
                //         height: "160px",
                //         modal: true,
                //         closed: false,
                //         inline: true,
                //         iconCls: 'icon-save',
                //     });
                // }
                // history_original_trade.downloadTemplet = function(){
                //     var url= "<?php echo U('HistoryOriginalTrade/downloadTemplet');?>";
                //     if (!!window.ActiveXObject || "ActiveXObject" in window){
                //         messager.confirm('IE浏览器下文件名会中文乱码，确定下载模板吗？',function(r){
                //             if(!r){return false;}
                //             window.open(url);
                //         })
                //     }else{
                //         messager.confirm('确定下载模板吗？',function(r){
                //             if(!r){return false;}
                //             window.open(url);
                //         })
                //     }
                // }
                history_original_trade.loadFormData=function(id,data){
                    if(id==undefined){id=this.params.search.form_id;}
                    if(data!=undefined){$('#'+id).form('load',data);}
                    else if(this.params.search.form_data!=undefined){$('#'+id).form('load',this.params.search.form_data);$('#'+id+' :input[extend_type="complex-check"]').each(function(){$(this).triStateCheckbox('init');});this.submitSearchForm();}
                    else{$('#'+id).form('reset');}
                    $('#<?php echo ($id_list["more_content"]); ?>').hide();
                }
                $('#history_original_trade_tid').textbox('textbox').bind('keyup', function() {  
                    if(this.value) {
                        $('#<?php echo ($id_list["more_content"]); ?>').show();
                    }
                    else {
                        history_original_trade.hideSearchBox();
                    }
                });
                $('#history_original_trade_buyer_nick').textbox('textbox').bind('keyup', function() {  
                    if(this.value) {
                        $('#<?php echo ($id_list["more_content"]); ?>').show();
                    }
                    else {
                        history_original_trade.hideSearchBox();
                    }
                });
                $('#history_original_trade_receiver_mobile').textbox('textbox').bind('keyup', function() {  
                    if(this.value) {
                        $('#<?php echo ($id_list["more_content"]); ?>').show();
                    }
                    else {
                        history_original_trade.hideSearchBox();
                    }
                });
                history_original_trade.hideSearchBox=function(){
                    var search_value=new Array();
                    search_value[1]=$('#history_original_trade_tid').textbox('getText');
                    search_value[2]=$('#history_original_trade_buyer_nick').textbox('getText');
                    search_value[3]=$('#history_original_trade_receiver_mobile').textbox('getText');
                    search_value[4]=$('#history_original_trade_process_status').combobox('getValue');
                    search_value[5]=$('#history_original_trade_shop_id').combobox('getValue');
                    if(search_value[1]==''&&search_value[2]==''&&search_value[3]==''&&search_value[4]=='all'&&search_value[5]=='all'){
                        $('#<?php echo ($id_list["more_content"]); ?>').hide();
                        var id=this.params.search.form_id;
                        $('#'+id).form('reset');
                    }
                }
            }, 0);
        });

function selectValue(){
    var varSelect = $(this).combobox('getValue');
    if(varSelect!='all'){
        $('#<?php echo ($id_list["more_content"]); ?>').show();
    }
    else history_original_trade.hideSearchBox();
}
    </script>

</body>
</html>