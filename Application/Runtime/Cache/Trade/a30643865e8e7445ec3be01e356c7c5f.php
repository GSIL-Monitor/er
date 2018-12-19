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
                <a href="javascript:void(0)" class="easyui-linkbutton" style="width:50%" onclick="original_trade.upload()">上传</a>
            </div>
        </form>
    </div>
    <div id="<?php echo ($id_list["invalid"]); ?>"></div>

<!-- toolbar -->

    <div id="<?php echo ($id_list['toolbar']); ?>" style="padding:5px; height:auto">
    <style type="text/css">
    #OriginalTrade_next_link {
        float: right;
        margin-right: 10px;
    }
    .set-icon{position:relative;left:-25px;color: #5881B1;text-align: center;}
    .set-text{position:relative;left:-16px;}
    </style>
        <form id="<?php echo ($id_list['form']); ?>" class="easyui-form" method="post">
            <div class="form-div">
                <label class ="five-character-width">平台状态：</label><input id="original_trade_trade_status" class="easyui-combobox txt" text="txt" name="search[trade_status]" value="all" data-options="valueField:'id',textField:'name'"/>
                <label class="five-character-width">　系统状态：</label><input id="original_trade_process_status" class="easyui-combobox txt" text="txt" name="search[process_status]" value="all" data-options="valueField:'id',textField:'name'"/>
                <label class="five-character-width">　原始单号：</label><input class="easyui-textbox txt" text="txt" name="search[tid]"/>
                <label >　店铺：</label><input id="original_trade_shop_id" class="easyui-combobox sel" text="txt" name="search[shop_id]" value="all" data-options="valueField:'id',textField:'name'"/>
                <label class="five-character-width">　客户网名：</label><input class="easyui-textbox txt" text="txt" name="search[buyer_nick]"/>
                <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-search'" onclick="original_trade.submitSearchForm(this)">搜索</a>
                <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-redo'" onclick="original_trade.loadFormData()">重置</a>
                <a href="javascript:void(0)" id="OriginalTrade_next_link" class="easyui-linkbutton" data-options="iconCls:'icon-next',plain:true" onClick="open_menu('订单审核', '<?php echo U('Trade/TradeCheck/getTradeList');?>')">订单审核</a>
            </div>
            <div class="form-div" style="margin-bottom: 3px;">
                <label class="five-character-width">发货条件：</label><input id="original_trade_delivery_term" class="easyui-combobox txt" text="txt" name="search[delivery_term]" value="all" data-options="valueField:'id',textField:'name'"/>
                <label class="five-character-width">　支付状态：</label><input id="original_trade_pay_status" class="easyui-combobox txt" text="txt" name="search[pay_status]" value="all" data-options="valueField:'id',textField:'name'"/>
                <label class="five-character-width">　退款状态：</label><input id="original_trade_refund_status" class="easyui-combobox txt" text="txt" name="search[refund_status]" value="all" data-options="valueField:'id',textField:'name'"/>
                <label>　手机：</label><input class="easyui-numberbox txt" text="txt" name="search[receiver_mobile]"/>
                <label>　　　标旗：</label><input class="easyui-combobox txt" name="search[remark_flag]" data-options="panelHeight:'auto',valueField:'id',textField:'name',data:formatter.get_data('remark_flag')"/>
            </div>
            <div class="form-div" style="margin-bottom: 3px;">
                <label class="five-character-width">下单时间：</label><input class="easyui-datetimebox txt" type="text" name="search[trade_start_time]" data-options="editable:false"/>
                <label>　　　　至：</label><input class="easyui-datetimebox txt" type="text"    name="search[trade_end_time]" data-options="editable:false"/> 
                <label>　付款时间：</label><input class="easyui-datetimebox txt" type="text" name="search[pay_start_time]" data-options="editable:false"/>
                <label>　　至：</label><input class="easyui-datetimebox txt" type="text" name="search[pay_end_time]" data-options="editable:false"/>
            </div>
        </form>
        <a href="javascript:void(0)" class="easyui-linkbutton" title="若不勾选任何订单，默认一次递交一百条" data-options="iconCls:'icon-submit',plain:true" onclick="original_trade.submitOriginalTrade()">递交</a>
        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-search',plain:true" onclick="original_trade.checkNumber()">查看号码</a>
        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-excel',plain:true" onclick="original_trade.uploadDialog()">导入原始订单</a>
        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-down_tmp',plain:true" onclick="original_trade.downloadTemplet()">下载模板</a>       
        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-cancel',plain:true" onclick="original_trade.deleteUpload()">删除导入单</a>
        <a href="javascript:void(0)" class="easyui-menubutton" data-options="iconCls:'icon-setting',plain:true,menu:'#mbut-original-rule'" >设置策略</a>
        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-no_match',plain:true" id="<?php echo ($id_list["invalid_goods"]); ?>" onclick="original_trade.invalidGoods()">未匹配货品</a>
        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-edit',plain:true" onclick="setDatagridField('Trade/Trade','originalorder','<?php echo ($datagrid["id"]); ?>',1)">设置表头</a>
        <a href="javascript:void(0)" class="easyui-menubutton" name="original_trade_out" data-options="iconCls:'icon-database-go',plain:true,menu:'#original_trade_out'">导出功能</a>
        <label class="form-div">
			<a href="<?php echo ($faq_url); ?>" target="_blank" class="easyui-linkbutton" title="点击查看递交常见问题" data-options="iconCls:'icon-help',plain:true">递交常见问题</a>
		</label>
        <div id="mbut-original-rule" style="width:100px;">
			<div onclick="open_menu('<?php echo ($rule["warehouse_r"]["text"]); ?>', '<?php echo ($rule["warehouse_r"]["href"]); ?>')"><span class="set-icon"><i class='fa fa-home' style="font-size: 15px;width: 16px;"></i></span><span class="set-text">选仓策略</span></div>
			<div onclick="open_menu('<?php echo ($rule["logistics_m"]["text"]); ?>', '<?php echo ($rule["logistics_m"]["href"]); ?>')"><span class="set-icon"><i class='fa fa-truck' style="font-size: 15px;width: 16px;"></i></span><span class="set-text">物流匹配</span></div>
			<div onclick="open_menu('<?php echo ($rule["logistics_f"]["text"]); ?>', '<?php echo ($rule["logistics_f"]["href"]); ?>')"><span class="set-icon"><i class='fa fa-money' style="font-size: 15px;width: 16px;"></i></span><span class="set-text">物流邮资</span></div>
			<div onclick="open_menu('<?php echo ($rule["area_alias"]["text"]); ?>', '<?php echo ($rule["area_alias"]["href"]); ?>')"><span class="set-icon"><i class='fa fa-tag' style="font-size: 15px;width: 16px;"></i></span><span class="set-text">地区别名</span></div>
			<div onclick="open_menu('<?php echo ($rule["gift"]["text"]); ?>', '<?php echo ($rule["gift"]["href"]); ?>')"><span class="set-icon"><i class='fa fa-gift' style="font-size: 15px;width: 16px;"></i></span><span class="set-text">赠品策略</span></div>
			<div onclick="open_menu('<?php echo ($rule["remark_e"]["text"]); ?>', '<?php echo ($rule["remark_e"]["href"]); ?>')"><span class="set-icon"><i class='fa fa-crosshairs' style="font-size: 15px;width: 16px;"></i></span><span class="set-text">备注提取</span></div>
		</div>
        <div id="original_trade_out">
            <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-excel',plain:true" onclick="original_trade.exportToExcel('csv')">导出Csv(推荐)</a>
            <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-excel',plain:true" onclick="original_trade.exportToExcel('excel')">导出到Excel</a>
        </div>
    </div>

<!-- layout-south-tabs, call add_tabs js function to add tabs -->

    <script type="text/javascript">
        //# sourceURL=OriginalTrade.js
        $(function () {
            setTimeout(function () {
                original_trade = new RichDatagrid(<?php echo ($params); ?>);
                original_trade.setFormData();
                //查看号码
                original_trade.checkNumber = function () {
                    var rows = original_trade.selectRows;
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
                            key: 'api_trade'
                        }, function (res) {
                            original_trade.dealDatagridReasonRows(res,list);
                        }, 'JSON');
                    }else{
                        var res={};res.status=2;res.info={};res.info.rows=list;res.info.total=list.length;
                        original_trade.dealDatagridReasonRows(res,undefined);
                    }
                }
                original_trade.dealDatagridReasonRows = function (result,list) {
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
                        var rows = original_trade.selectRows;
                        var index;
                        var trade_dg = $('#' + original_trade.params.datagrid.id);
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
                original_trade.exportToExcel = function(type){
                    var dg = $('#'+original_trade.params.datagrid.id);
                    var queryParams = dg.datagrid('options').queryParams;
                    var search=JSON.stringify(queryParams);
                    var url= "<?php echo U('OriginalTrade/exportToExcel');?>";
                    var id_list=[];
                    for(var i in this.selectRows){id_list.push(this.selectRows[i].id);}
                    var rows = $("#<?php echo ($id_list["datagrid"]); ?>").datagrid("getRows");
                    if(rows==''){
                        messager.confirm('导出不能为空！');
                    }
                    else if(id_list!=''){
                        messager.confirm('确定导出选中的订单吗？',function(r){
                            if(!r){return false;}
                            window.open(url+'?id_list='+id_list+'&type='+type);
                        })
                    }
                    else{
                        messager.confirm('确定导出搜索的订单吗？',function(r){
                            if(!r){return false;}
                            window.open(url+'?id_list='+id_list+'&search='+search+'&type='+type);
                        })
                    }
                }
                original_trade.submitOriginalTrade = function () {
                    var row = $("#" + "<?php echo ($id_list[datagrid]); ?>").datagrid("getSelections");
                    var ids = {};
                    for (var x in row) {
                        ids[x] = row[x]["id"];
                    }
                    ids = JSON.stringify(ids);
                    var url = "<?php echo U('OriginalTrade/submitOriginalTrade');?>";
                    $.post(url, {"id": ids}, function (res) {
                        if (res.status == 1) {
                            $.fn.richDialog("response", res.info, "trade");
                            original_trade.refresh();
                        } else if (res.status == 0) {
                            original_trade.refresh();
                        } else if (res.status == 2) {
                            messager.alert(res.info);
                        }
                    })
                };
                original_trade.upload = function () {
                    var form = $("#<?php echo ($id_list["fileForm"]); ?>");
                    var url = "<?php echo U('OriginalTrade/importTrade');?>";
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
                                dialog.dialog("close");
                            } else if (res.status == 1) {
                                messager.alert(res.info);
                            } else if (res.status == 2) {
                                var converter = document.createElement("DIV");
                                converter.innerHTML = res.info[0]['message'];
                                res.info[0]['message'] = converter.innerText;
								converter = null;
                                $.fn.richDialog("response", res.info, "importTrade");
                            }
                            dg.datagrid("reload");
                            form.form("load", {"file": ""});
                        }
                    })
                }
                $("#original_trade_process_status").combobox("loadData", formatter.get_data("process_status"));
                $("#original_trade_pay_status").combobox("loadData", formatter.get_data("pay_status"));
                $("#original_trade_delivery_term").combobox("loadData", formatter.get_data("delivery_term"));
                $("#original_trade_trade_status").combobox("loadData", formatter.get_data("api_trade_status"));
                $("#original_trade_refund_status").combobox("loadData", formatter.get_data("refund_status"));
                $("#original_trade_shop_id").combobox("loadData", <?php echo ($shop_list); ?>);
                original_trade.uploadDialog = function () {
                    var dialog = $("#<?php echo ($id_list["fileDialog"]); ?>")
                    dialog.dialog({
                        title: "导入原始订单",
                        width: "350px",
                        height: "160px",
                        modal: true,
                        closed: false,
                        inline: true,
                        iconCls: 'icon-save',
                    });
                }
                original_trade.downloadTemplet = function(){
                    var url= "<?php echo U('OriginalTrade/downloadTemplet');?>";
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
                }
                original_trade.deleteUpload = function(){
                	var rows = $("#" + "<?php echo ($id_list[datagrid]); ?>").datagrid("getSelections");
                	if(rows.length==0){messager.alert('请选择需要删除的导入原始单。');return false;}
                    var ids = [];
                    var list= [];
                    for (var x in rows) {
                    	if(rows[x]['trade_from']!=3){
                    		list.push({tid:rows[x]['tid'],buyer_nick:rows[x]['buyer_nick'],error_info:"只能删除导入的原始单。"});continue;
                    	}
                        ids[x] = rows[x]["id"];
                    }
                    var url= "<?php echo U('OriginalTrade/deleteUpload');?>";
                	if(ids.length>0){
                    	ids = JSON.stringify(ids);
                		$.post(url, {"ids": ids}, function (res) {
                            if (res.status != 0) {
                            	if(list.length>0){
                        			var fail= (typeof res.info.rows=='object')?$.makeArray(res.info.rows):res.info.rows;
                            		res.info.rows=$.merge(list,fail);
                            		res.info.total+=list.length;
                            	}
                                $.fn.richDialog("response", res.info, "trade");
                                original_trade.refresh();
                            } else {
                                original_trade.refresh();
                            } 
                        })
                	}else{
        				var res={};res.status=2;res.info={};res.info.rows=list;res.info.total=list.length;
                		$.fn.richDialog("response", res.info, "trade");
                	}
                	
                }
                //未匹配货品->动态数量
                original_trade.setInvalidGoodsNum=function(num){
                    var invalid_goods='<?php echo ($id_list["invalid_goods"]); ?>';
                    if(num>0){$('#'+invalid_goods+' .l-btn-text').text('未匹配货品('+num+')').css('color','red');}
                    else if(num==0){$('#'+invalid_goods+' .l-btn-text').text('未匹配货品').css('color','#000000');}
                }
                original_trade.params.invalid_num=<?php echo ($id_list["invalid_goods_total"]); ?>;
                original_trade.setInvalidGoodsNum(original_trade.params.invalid_num);
                original_trade.getInvalidGoodsNum=function(){
                   var url= "<?php echo U('Trade/TradeCheck/getInvalidGoodsNum');?>";
                    $.post(url,'',function(res){
                        original_trade.setInvalidGoodsNum(res);
                        return true;
                    }); 
                }
                original_trade.invalidGoods = function(){
                    var url='<?php echo U('Trade/TradeCheck/getInvalidGoods?type=original');?>';
                    var invalid = $("#<?php echo ($id_list["invalid"]); ?>");
                    var buttons=[
                                    {text:'确定',handler:function(){invalid.dialog('close');}},
                                ];
                    invalid.dialog({ title:'未匹配货品', iconCls:'icon-no_match', width:764, height:550, closed:false, inline:true, modal:true, href:url, onBeforeClose:original_trade.getInvalidGoodsNum, buttons:buttons });
                }
            }, 0);
        });

    </script>

</body>
</html>