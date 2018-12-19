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
<div id="<?php echo ($id_list["edit"]); ?>"></div><div id="<?php echo ($id_list["add"]); ?>"></div>
<!-- toolbar -->

    <div id="<?php echo ($id_list['toolbar']); ?>" style="padding:5px; height:auto">
    <style type="text/css">
        #RefundManage_next_link {float: right; margin-right: 10px;}
    </style>
        <form id="<?php echo ($id_list['form']); ?>" class="easyui-form" method="post">
            <div class="form-div">
                <label >类　型：</label><input class="easyui-combobox txt" name="search[type]" data-options="panelHeight:'auto',valueField:'id',textField:'name',data:formatter.get_data('api_refund_type')"/>
                <label class="five-character-width">　系统状态：</label><input class="easyui-combobox txt" name="search[process_status]" data-options="panelHeight:'auto',valueField:'id',textField:'name',data:formatter.get_data('api_refund_process_status')"/>
                <label class="five-character-width">　退款单号：</label><input class="easyui-textbox txt" type="text" name="search[refund_no]"/>
                <label>　　原始单号：</label><input class="easyui-textbox txt" type="text" name="search[tid]"/>
                <label class="five-character-width">　客户网名：</label><input class="easyui-textbox txt" type="text" name="search[buyer_nick]"/>
                <a href="javascript:void(0)" onclick="originalRefund.clickMore(this)">更多</a>
                <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-search'" onclick="originalRefund.submitSearchForm(this)">搜索</a>
                <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-redo'" onclick="originalRefund.loadFormData()">重置</a>
                <a href="javascript:void(0)" id="RefundManage_next_link" class="easyui-linkbutton" data-options="iconCls:'icon-next',plain:true" onClick="open_menu('退换管理', '<?php echo U('Trade/RefundManage/getSalesRefundList');?>')">退换管理</a>
            </div>
            <div id="<?php echo ($id_list["more_content"]); ?>">
                <div class="form-div">
                	<label >店　铺：</label><select class="easyui-combobox sel" name="search[shop_id]"><?php if(is_array($list["shop"])): $i = 0; $__LIST__ = $list["shop"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?></select>
                    <label class="five-character-width">　物流单号：</label><input class="easyui-textbox txt" type="text" name="search[logistics_no]"/>
                    <label class="five-character-width">　平台状态：</label><input class="easyui-combobox txt" name="search[status]" data-options="panelHeight:'auto',valueField:'id',textField:'name',data:formatter.get_data('api_refund_status')"/>
                    <label>　　订单状态：</label><input class="easyui-combobox txt" name="search[trade_status]" data-options="valueField:'id',textField:'name',data:formatter.get_data('trade_status')"/>
                    <label class="five-character-width">　退款原因：</label><input class="easyui-combobox txt" name="search[reason]" data-options="panelHeight:200,valueField:'name',textField:'name',data:formatter.get_data('api_refund_reason')"/>
                </div>
                <div class="form-div">
                	<label >操作人：</label><select class="easyui-combobox sel" name="search[operator_id]"><?php if(is_array($list["employee"])): $i = 0; $__LIST__ = $list["employee"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?></select>
                    <label class="five-character-width">　退款时间：</label><input class="easyui-datebox txt" type="text" name="search[start_time]" data-options="editable:false"/>
					<label class="five-character-width">　　　　至：</label><input class="easyui-datebox txt" type="text"    name="search[end_time]" data-options="editable:false"/> 
					<label>申请退款金额：</label><input class="easyui-numberbox txt " type="text" name="search[start_amount]" />
					<label class="five-character-width">　　　　至：</label><input class="easyui-numberbox txt" type="text"    name="search[end_amount]" /> 
                </div>
            </div>
        </form>
        <div style="display: none;"><input hidden="true" id="<?php echo ($id_list["hidden_flag"]); ?>" value="1"/> </div>
        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-submit',plain:true" onclick="originalRefund.submitOriginalRefund()">递交</a>
    </div>

<!-- layout-south-tabs, call add_tabs js function to add tabs -->

    <script type="text/javascript">
        //# sourceURL=OriginalTrade.js
        $(function () {
            setTimeout(function () {
                originalRefund = new RichDatagrid(JSON.parse('<?php echo ($params); ?>'));
                originalRefund.setFormData();
                //查看号码
                originalRefund.checkNumber = function () {
                    var rows = originalRefund.selectRows;
                    if (rows == undefined) {
                        messager.info('请选择操作的行');
                        return false;
                    }
                    var ids = [];
                    for (var i in rows) {
                        if (rows[i]['receiver_mobile'] == '' && rows[i]['receiver_telno'] == '') {
                            continue;
                        }
                        ids.push(rows[i]['id']);
                    }
                    if (ids.length > 0) {
                        $.post("<?php echo U('Trade/TradeCommon/checkNumber');?>", {
                            ids: JSON.stringify(ids),
                            key: 'api_trade'
                        }, function (res) {
                            originalRefund.dealDatagridReasonRows(res);
                        }, 'JSON');
                    }
                }
                originalRefund.dealDatagridReasonRows = function (result) {
                    if (result.status == 1) {
                        messager.alert(result.message);
                        return;
                    }
                    if ((result.status == 0 || result.status == 2) && result.data != undefined) {
                        var rows = originalRefund.selectRows;
                        var index;
                        var trade_dg = $('#' + originalRefund.params.datagrid.id);
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
                originalRefund.submitOriginalRefund = function () {
                    var row = $("#" + "<?php echo ($id_list[id_datagrid]); ?>").datagrid("getSelections");
                    var ids = {};
                    var url = "<?php echo U('OriginalRefund/submitOriginalRefund');?>";
                    if(row.length==undefined||row.length==0){
                    	messager.confirm("确定递交所有类型为“退款”的原始单吗？（最多100条）",function(r){
                			if(!r){return false;}
                			$.post(url, {"id": ids}, function (res) {
                                if (res.status == 1) {
                                    $.fn.richDialog("response", res.info, "trade");
                                    originalRefund.refresh();
                                } else if (res.status == 0) {
                                	originalRefund.refresh();
                                } else if (res.status == 2) {
                                    messager.alert(res.info);
                                }
                            });
                		});
                    }else{
                    	var type = row[0]['type'];
                        for (var x in row) {
                            ids[x] = row[x]["id"];
                            if(row[x]['type']!=type){messager.alert("请选择相同类型的原始退款单进行递交。");return false;}
                        }
                        if((type==2||type==3)&&row.length>1){
                        	messager.alert("退货、换货单只支持单条递交，请不要选择多条。");return false;
                        }
                        if(type==2||type==3){
                        	var url = "<?php echo U('RefundManage/editRefund');?>";
                        	var is_api=1;
                        	url += url.indexOf('?') != -1 ? '&id='+ids[0]+'&is_api='+is_api : '?id='+ids[0]+'&is_api='+is_api ;
                        	var buttons=[ {text:'确定',handler:function(){addEditRefund.submitEditDialog();}}, {text:'取消',handler:function(){originalRefund.cancelDialog(0);}} ];
                        	if(row[0].process_status>20||row[0].process_status==10){messager.alert("原始退款单状态不正确，不需要递交。");return false;}
                        	$.post('<?php echo U('OriginalRefund/checkIsSubmit');?>',{id:ids[0]},function(res){
                        		if(res==1){messager.alert("未找到该退款单对应的系统订单。");return false;}
                        		else if(res==2){messager.alert("该退款单对应的系统订单未发货。");return false;}
                        		else if(row[0].process_status==20){
                        			messager.confirm("该原始退换单已递交，是否继续？",function(r){
                            			if(!r){return false;}
                            			originalRefund.showDialog(0,'新建退换单',url,510,1000,buttons);
                            		});
                        		}else{
                        			originalRefund.showDialog(0,'新建退换单',url,510,1000,buttons);
                        		}
                        	});
                        }else{
                        	$.post(url, {"id": ids}, function (res) {
                                if (res.status == 1) {
                                    $.fn.richDialog("response", res.info, "trade");
                                    originalRefund.refresh();
                                } else if (res.status == 0) {
                                	originalRefund.refresh();
                                } else if (res.status == 2) {
                                    messager.alert(res.info);
                                }
                            });
                        }
                    }
                }
            }, 0);
        });

    </script>

</body>
</html>