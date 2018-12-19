<?php if (!defined('THINK_PATH')) exit();?>
    <div style="padding: 20px; height: 100%; background-color: #F4F4F4">
        <div style="float: left;padding-top: 20px;width: 48%;height: 80%;background-color: white;border: 1px solid darkgray">

            <div style="margin-left: 20px;display: inline">
                <input type="checkbox" id="voice_alert" onclick="purchasePickList.saveBaseSetting(this)"/><label class="seven-character-width">开启声音提示</label>
				 <input type="checkbox" id="stalls_mode" style = "margin-left:20px;" onclick="purchasePickList.saveBaseSetting(this)"/><label class="seven-character-width">档口模式</label>
           
		   </div>

		   <div style="margin-left: 25px;margin-top: 20px">
              <label>　　　　  分拣单号: </label><input class="easyui-textbox txt" type="text" style="width: 200px" name="sorting_list" id = "sorting_list"/>
			</div>
            <div style="margin-left: 20px;margin-top: 20px">
                <label>唯一码(或者条形码): </label><input class="easyui-textbox txt" style="width: 200px" type="text" name="uniqueCode" id="uniqueCodeId"/>
            </div>

            <div style="margin-left: 20px;margin-top: 20px">
                <input type="checkbox" id="print_logistics" onclick="purchasePickList.saveBaseSetting(this)"/><label>自动打印物流单</label>
                <a href="javascript:void(0)"
                   style="font-size: 20px;" class="easyui-linkbutton"
                   data-options="iconCls:'icon-edit'"
                   onclick="purchasePickList.setLogisticsPrintersDialog()";>设置物流单打印机和模板</a>
			<div style="height: 30px;margin-left:20px;" id = "print_logistics_select">
				 <input type="checkbox" id="stalls_print_logistics" onclick="purchasePickList.saveBaseSetting(this)"/><label>档口货品分拣完自动打印物流单</label>                            
                    <label style="font-size: 15px">(注：没勾选则订单分拣完成将自动打印物流单)</label>
            </div>
            </div>
            <div style="margin-left: 20px;margin-top: 20px">
                <input type="checkbox" id="print_tag" onclick="purchasePickList.saveBaseSetting(this)"/><label>自动打印吊牌</label>
                <a href="javascript:void(0)"
                style="font-size: 20px;" class="easyui-linkbutton"
                data-options="iconCls:'icon-edit'"
                onclick="purchasePickList.setPrintersDialog()";>设置吊牌打印机和模板</a>

                <div style="height: 30px">
                    <label style="font-size: 15px">注：扫描完成将自动打印吊牌</label>
                </div>
            </div>
            <div style="height: 50px;margin-top: 40px">
                <label style="display: inline-block;width: 100%;font-size: 60px; color:limegreen;text-align:center" id="sort_finish"></label>
            </div>
        </div>
        <div style="float: right;padding-top: 20px;padding-left: 20px;padding-right: 20px;width: 48%;height: 80%;background-color: white;border: 1px solid darkgray">
            <div>
                <label class="four-character-width">商品信息：</label>
                <label class="seven-character-width" id="goods_info"></label>
            </div>
            <div style="margin-top: 10px">
                <label class="four-character-width">订单号：</label>
                <label class="seven-character-width" id="order_no"></label>
            </div>
            <div style="margin-top: 10px">
                <label class="four-character-width">买家留言：</label>
                <label style="width: 20px;" id="buyer_message"></label>
            </div>
            <div style="margin-top: 10px">
                <label class="four-character-width">商家编码：</label>
                <label class="seven-character-width" id="spec_no"></label>
            </div>
            <div style="margin-top: 10px">
                <label class="four-character-width">物流单号：</label>
                <label class="seven-character-width" id="logistics_no"></label>
            </div>
            <div style="margin-top: 10px">
                <label class="four-character-width">客服备注：</label>
                <label style="width: 20px;" id="cs_remark"></label>
            </div>
            <div style="margin-top: 10px">
                <label class="four-character-width">付款时间：</label>
                <label style="width: 20px" id="pay_time"></label>
            </div>
            <hr>
            <div style="margin-top: 10px">
                <label class="seven-character-width">分拣墙格子号：</label>
            </div>
            <div style="margin-top: 40px">
                <label style="font-size: 80px;color:orange;" id="sort_wall_no">&nbsp</label>
            </div>
            <div style="margin-top: -300px">
                <img border=hidden style="margin-left: 300px;width: 200px;height: 200px" id="img_url" src="">
            </div>

        </div>
    </div>
    </div><div id="setPrintersDialog"></div>
    </div><div id="setLogisticsPrintersDialog"></div>
    <div id="audioBox"></div>
    <audio id="stalls_pick_success_sound">
        <source src="/Public/Image/sort_success.wav" >
    </audio>
    <audio id="stalls_pick_unstalls_sound">
        <source src="/Public/Image/unstalls.wav" >
    </audio>
    <audio id="stalls_pick_warn_sound">
        <source src="/Public/Image/warn.mp3" >
    </audio>

    <script>
//# sourceURL=picklist.js
	var sorting = 0;
	var sortingCode = '';
        $(function () {
            var base_set = <?php echo ($base_set); ?>;
            var show_dialog = <?php echo ($show_dialog); ?>;
            $("#voice_alert").prop('checked',base_set.voice_alert =='1'?true:false);
			$("#stalls_mode").prop('checked',base_set.stalls_mode =='1'?true:false);
            $("#print_logistics").prop('checked',base_set.print_logistics =='1'?true:false);
            $("#print_tag").prop('checked',base_set.print_tag =='1'?true:false);
			$("#stalls_print_logistics").prop('checked',base_set.stalls_print_logistics =='1'?true:false);
			if(base_set.print_logistics =='1'){
				$('#print_logistics_select').show();
			}else{
				$('#print_logistics_select').hide();
			}
//            var picklistWs,order_no,data,printerInfo,printData,orderData,sortInfo,goods_info,orderPrintData,baseSet,customTempUrl,row,detail;
            var picklistWs,orderData;
            var unique_code;
            var paramsObj;

            purchasePickList = {
                order_no:'',
                thatObj:{},
                paramsObj:{},
                uniqueCode:'',
                setPrintersDialog : function () {
                    $("#setPrintersDialog").dialog({
                        href:"<?php echo U('StallsPickList/getTemplates');?>",
                        width:330,
                        height:180,
                        inline: true,
                        modal: true,
                        title:"设置吊牌打印机和模板",
                        iconCls: 'icon-save',
                        buttons:[{
                            text:'保存',
                            handler:function(){purchasePickList.savePrintSetting();}
                        }]
                    });
                },
                setLogisticsPrintersDialog : function () {
                    $("#setLogisticsPrintersDialog").dialog({
                        href:"<?php echo U('StallsPickList/setLogisticsAndTemplatesDialog');?>",
                        width:540,
                        height:300,
                        inline: true,
                        modal: true,
                        title:"设置物流单打印机和模板",
                        iconCls: 'icon-save',
                        buttons:[{
                            text:'保存',
                            handler:function(){purchasePickList.savePrintersAndTemplatesSetting();}
                        }]
                    });
                },
                selectPrinter:function(type){

//                    type = type == "goods"?"99":"88";
                    if(type == "goods"){
                        type = "99";
                    }else if(type =="setting"){
                        type = "77";
                    }else {
                        type = "88";
                    }
                    var request = {
                        "cmd":"getPrinters",
                        "requestID":"123458976"+type,
                        "version":"1.0"
                    };
                    picklistWs.send(JSON.stringify(request));
                },
                connectStockWS:function(){
                    if($.isEmptyObject(picklistWs)){
                        picklistWs = new WebSocket("ws://127.0.0.1:13528");
                        picklistWs.onmessage = this.onStockMessage;
                        picklistWs.onerror = this.onStockError;
                    }
                    return ;
                },

                onStockError:function(){
                    $.messager.progress('close');
                    $('#printing').linkbutton({text:'打印',disabled:false});
                    stockWs = null;
                    $('#setPrintersDialog').dialog({
                        title: '打印组件错误',
                        width: 400,
                        height: 200,
                        closed: false,
                        cache: false,
                        href:  "<?php echo U('Stock/StockSalesPrint/onWSError');?>",
                        modal: true
                    });
                    $('#setPrintersDialog').dialog('refresh', "<?php echo U('Stock/StockSalesPrint/onWSError');?>");
                },
                changeTemplatePage:function(){
                    open_menu('打印模板','<?php echo U("Setting/NewPrintTemplate/getNewPrintTemplate");?>');
                    $('#setPrintersDialog').dialog('close');
                },
                onStockMessage:function (event){
                    var ret = $.parseJSON(event.data);
                    var msg = ret.msg;
                    var task_id = '';

                    var taskID = ret.taskID+"";
                    taskID = taskID.substr(taskID.length-2,taskID.length);

                    if((!$.isEmptyObject(msg))&&msg!="成功")
                    {
                        $('#printing').linkbutton({text:'打印',disabled:false});
                        messager.alert(msg);
                        return;
                    }
                    if(!$.isEmptyObject(ret)){
                        switch(ret.cmd){

                            case 'getPrinters':
                            {
                                var type = ret.requestID;
                                type = type.substr(type.length-2,type.length);
                                if(type == 99){
//                                    $('#logistic_printer_list').combobox({
//                                        valueField: 'name',
//                                        textField: 'name',
//                                        data: ret.printers,
//                                        value: ret.defaultPrinter
//                                    });
                                    $('#tag_printer_list').combobox({
                                        valueField: 'name',
                                        textField: 'name',
                                        data: ret.printers,
                                        value: ret.defaultPrinter
                                    });

//                                    $('#logistic_printer_list').combobox('reload');
                                    $('#tag_printer_list').combobox('reload');

                                }else if(type == 77){

                                    $('#printer_data').combobox({
                                        valueField: 'name',
                                        textField: 'name',
                                        data: ret.printers,
                                        value: ret.defaultPrinter
                                    });
                                    $('#printer_data').combobox('reload');
                                }
                            }
                            break;
                            case 'notifyPrintResult':
                            {
                                if(ret.taskStatus == "printed"){

                                    task_id = ret.taskID+"";
                                    task_id = task_id.substr(task_id.length-2,task_id.length);

                                    $.post("/index.php/Stock/StallsPickList/chgPrintStatus", {uniqueCode:unique_code,orderNo:purchasePickList.order_no,taskId:task_id}, function(r){
                                        if(r.status != 0){
                                            messager.alert(r.info);
                                        }
                                    });

                                }else if(ret.taskStatus == "failed"){

                                    messager.alert("打印失败");
                                }
                            }
                            break;
                        }
                    }
                },
                saveBaseSetting:function (o){

                    var checkV = $("#"+o.id).prop('checked') == true?'1':'0';
					if(o.id == 'stalls_mode'){
						if(checkV == '1'){
							$('#sorting_list').textbox('setValue','');
							$('#sorting_list').textbox({disabled:true});
							$('#uniqueCodeId').textbox('textbox').focus();
							sorting = 0;
						}else{
							$('#sorting_list').textbox({disabled:false});
							$('#sorting_list').textbox('textbox').keydown(function (e) {
								if (e.keyCode == 13) {
									sorting_list = $('#sorting_list').textbox('getValue');
									purchasePickList.sorting_list = sorting_list;
									pickSorting(sorting_list);
								}
							});
							$('#sorting_list').textbox('textbox').focus();
						}
					}
					if(o.id == 'print_logistics'){
						if(checkV == '0'){
							$('#print_logistics_select').hide();
						}else{
							$('#print_logistics_select').show();
						}
					}
                    $.post("/index.php/Stock/StallsPickList/saveBaseSetting", {type:o.id,checkVal:checkV}, function(r){
                        if(r.status != 0){
                            messager.alert(r.info);
                        }
                    });
                },
                savePrintSetting:function (){

                    if(getDefTagTemp() == -1){
                        messager.alert('请先去下载吊牌模板');
                        return;
                    }
                    if(getSysDefTemp() == '-1'){
                        messager.alert('请先去下载"系统默认自定义区"模板');
                        return;
                    }

//                    var logistic_printer = $('#logistic_printer_list').combobox('getValue');
                    var tag_printer = $('#tag_printer_list').combobox('getValue');
                    var tag_template = $('#tag_template_list').combobox('getValue');
                    var templatesAndLogisticsObj = {
//                        'logistic_printer':logistic_printer,
                        'tag_printer':tag_printer,
                        'tag_template':tag_template
                    };

                    var jsonStr = JSON.stringify(templatesAndLogisticsObj);
                    $.post("/index.php/Stock/StallsPickList/savePrintersAndTemplates", {templatesAndLogisticsInfo:jsonStr}, function(r){
                        $('#setPrintersDialog').dialog('close');
                        if(r.status != 0){
                            messager.alert(r.info);
                        }
                    });
                },
                savePrintersAndTemplatesSetting:function(){

                    var that = this;
                    var row = $('#'+set_logistics_templates.params.datagrid.id).datagrid('getSelected');
                    var row_index = $('#'+set_logistics_templates.params.datagrid.id).datagrid('getRowIndex',row);
                    $('#'+set_logistics_templates.params.datagrid.id).datagrid('endEdit',row_index);
                    var rows = $('#'+set_logistics_templates.params.datagrid.id).datagrid('getRows');
                    var is_submit = true;
                    for(var i in rows){
                        if((rows[i]['name'] != '无') && (rows[i]['title'] == '无'))
                            is_submit = false;
                    }

                    if(is_submit){
                        $.post("<?php echo U('Stock/StallsPickList/submitPrintersAndTemplatesSet');?>", {printer_template_data:rows}, function(r){
                            messager.alert(r.info);
                            $('#setLogisticsPrintersDialog').dialog('close');
                        });
                    }else{
                        messager.alert('请将模板和打印机一起设置');
                    }

                },
                newPrint:function(datas,printer,taskID){
                    purchasePickList.connectStockWS();
                    var requestID =  (new Date()).valueOf();
                    var request = {
                        'cmd' : 'print',
                        'version' : '1.0',
                        'requestID' : requestID,
                        'task' : {
                            'taskID' : requestID +''+taskID,
                            'printer' : printer,//'',
                            'preview' : false,
                            'notifyMode':'allInOne',
                            'documents' : datas
                        }
                    };
                    picklistWs.send(JSON.stringify(request));
                },
                getLogisticsPrintData:function (data,templateUrl,row,detail,logistics_no){
                    var customObj = this.getLogisticsCustomData(data,templateUrl,row,detail,logistics_no);
                    print_data = [{
                        'documentID' : data.waybill_info.waybillCode,
                        'contents' : [{
                            'templateURL' : data.templateURL,
                            'signature' : data.signature,
                            'data' : data.waybill_info
                        },customObj]
                    }];
                    return print_data;
                },
                getLogisticsCustomData:function (data,templateUrl,row,detail,logistics_no){
                    if(templateUrl == ''){
                        return {};
                    }
                    custom_data = {
                        'templateURL' : templateUrl,
                        'data' : this.composeCustomData(data,row,logistics_no,detail)
                    };
                    return custom_data;
                },
                getTagPrintData:function (documentId,url,goodsInfo) {
                    orderData = [{
                        'documentID' : documentId,
                        'contents' : [
                            {
                                'templateURL' : url,
                                'data' : {
                                    'tag':{
                                        'goods_name':goodsInfo.goods_name,
                                        'spec_name':goodsInfo.spec_name,
                                        'goods_brand':goodsInfo.brand_name
                                    }
                                }
                            }
                        ]
                    }];
                    return orderData;
                },
                composeCustomData:function(data,row,logistics_no_temp,detail){
                var customArea_data = data;
                customArea_data.trade = {
                    'trade_no' : row.src_order_no,//订单标号
                    'src_tids' : row.src_tids,//原始单号
                    'logistics_no' : logistics_no_temp,
                    'package_code' : logistics_no_temp+"-1-1-",
                    'package_id' : row.id,
                    'goods_amount' : row.goods_total_cost,
                    'cargo_total_weight' : row.weight,
                    'calc_weight' : row.calc_weight,
                    'receivable' : row.receivable,
                    'goods_count' : row.goods_count,
                    'goods_type_count' : row.goods_type_count,
                    'print_date' : (new Date()).getFullYear()+"-"+((new Date()).getMonth()+1)+"-"+(new Date()).getDate(),//(new Date()).toLocaleString().replace(///g,"-").substring(0,9),
                    'cs_remark' : row.cs_remark,
                    'print_remark' : row.print_remark,
                    'buyer_nick' : row.buyer_nick,
                    'buyer_message' : row.buyer_message,
                    'invoice_content' : row.invoice_content,
                    'cod_amount' : row.cod_amount,
                    'receiver_dtb' : row.receiver_dtb,
                    'invoice_title' : row.invoice_title,
                    'trade_time' : row.pay_time,
                    'post_amount' : row.post_amount
                };
                customArea_data.shop = {
                    'name' : row.shop_name,
                    'website' : row.website
                };
                customArea_data.recipient = {
                    'name' : row.receiver_name,
                    'mobile': !(row.receiver_mobile=="")?row.receiver_mobile:row.receiver_telno,
                    'phone': !(row.receiver_telno=="")?row.receiver_telno:row.receiver_mobile
                };
                customArea_data.sender = {
                    "address": {
                        "city": row.city,
                        "detail": row.address,
                        "district": row.district,
                        "province": row.province,
                        "town": row.town
                    },
                    "mobile": row.mobile,
                    "name": row.contact,
                    "phone": row.telno
                };
                customArea_data.goods = goodsInfoToArray(detail[row.id]);
                return customArea_data;
            }
            };

            setTimeout(function () {
                purchasePickList.paramsObj = JSON.parse('<?php echo ($params); ?>');
                purchasePickList.thatObj = this;
                $('#uniqueCodeId').textbox('textbox').focus();
				if(base_set.stalls_mode =='1'){
					$('#sorting_list').textbox({disabled:true});
				}else{
					$('#sorting_list').textbox({disabled:false});
					$('#sorting_list').textbox('textbox').focus();
				}
                purchasePickList.connectStockWS();
                if(show_dialog == 1){
                    purchasePickList.setPrintersDialog();
                }
                $('#uniqueCodeId').textbox('textbox').keydown(function (e) {
                    if (e.keyCode == 13) {
                        unique_code = $('#uniqueCodeId').textbox('getValue');
                        purchasePickList.uniqueCode = unique_code;
                        pickListGoods(unique_code,'');
                    }
                });
				 $('#sorting_list').textbox('textbox').keydown(function (e) {
                    if (e.keyCode == 13) {
                        sorting_list = $('#sorting_list').textbox('getValue');
                        purchasePickList.sorting_list = sorting_list;
                        pickSorting(sorting_list);
                    }
                });
            },0);
        });

        function goodsInfoToArray(goods_info){
            var goods = {'detail':[]};
            goods.suite_ids = goods_info.suite_ids;
            goods.suite_info = goods_info.suite_info;
            delete goods_info.suite_ids;
            delete goods_info.suite_info;
            for(var i=0;;i++){
                if(goods_info[i] == undefined)
                    break;
                goods.detail[i] = goods_info[i];
            }
            goods.suite_info = goods.suite_info == undefined?"":goods.suite_info.substr(0,goods.suite_info.length-1);
            return goods;
        }
        function bandDataToHtml(sortInfo,order_no,goods_info,row){
            $("#sort_finish").html(sortInfo.sort_finish);
            $("#sort_wall_no").html(sortInfo.box_no);
            $("#order_no").html(order_no);
            $("#goods_info").html(goods_info.goods_name);
            $("#spec_no").html(goods_info.spec_no);

            $("#buyer_message").html(row.buyer_message);
            $("#cs_remark").html(row.cs_remark);
            $("#pay_time").html(row.pay_time);
            $("#img_url").attr("src",goods_info.url);
        }
		function pickSorting(sorting_list){
            var is_voice = $('#voice_alert').prop('checked');
            if(sorting_list == ''){
                if(is_voice){$("#stalls_pick_warn_sound")[0].play();}
                messager.alert('请输入分拣单号',undefined,function (){
                    $('#sorting_list').textbox('textbox').focus();
                });
				return;
			}
			$.post("/index.php/Stock/StallsPickList/sorting_list",{sorting_list:sorting_list},function(r){
				if(r.status == 0){
					sorting = 1;
					sortingCode = sorting_list;
					$('#uniqueCodeId').textbox('setValue','');
					$('#uniqueCodeId').textbox('textbox').focus();
				}else{
                    if(is_voice){$("#stalls_pick_warn_sound")[0].play();}
                    messager.alert(r.info,undefined,function (){
                        $('#sorting_list').textbox('textbox').focus();
                    });
					$('#sorting_list').textbox('setValue','');
					$('#sorting_list').textbox('textbox').focus();
				}
			});
		}
        function pickListGoods(unique_code,good_info){
            var order_no,data,printerInfo,printData,sortInfo,goods_info,orderPrintData,baseSet,customTempUrl,row,detail;

            var is_voice = $('#voice_alert').prop('checked');

			if($("#stalls_mode").prop('checked') == false && sorting != 1){
				$('#sorting_list').textbox('textbox').focus();
				messager.alert('非档口模式请先扫描分拣单号',undefined,function (){
                    $('#sorting_list').textbox('textbox').focus();
                });
				return;
			}
			var new_sortingCode = $('#sorting_list').textbox('getValue');
			if($("#stalls_mode").prop('checked') == false && (new_sortingCode == '' || new_sortingCode != sortingCode)){
				$('#sorting_list').textbox('setValue','');
				$('#sorting_list').textbox('textbox').focus();
				messager.alert('分拣单号不一致，请重新扫描分拣单',undefined,function (){
                    $('#sorting_list').textbox('textbox').focus();
                });
				return;
			}
            $.post("/index.php/Stock/StallsPickList/pickList", {uniqueCode:unique_code,goodInfo:good_info,sorting:sorting,sortingCode:sortingCode}, function(r){
//                console.log(r);
                // 清空输入框，用于下次分拣。
                $('#uniqueCodeId').textbox('setValue','');

                if(r.status ==1){
                    if(is_voice){$("#stalls_pick_warn_sound")[0].play();}
                    if(r.msg.indexOf('blockReason') != -1){
                        var block_reason_num = r.msg.split('_').pop();
                        var block_reason_name = formatter.stockout_block_reason(block_reason_num);
                        messager.alert(block_reason_name,undefined,function (){
                            $('#uniqueCodeId').textbox('textbox').focus();
                        });
                    }else{
                        messager.alert(r.msg,undefined,function (){
                            $('#uniqueCodeId').textbox('textbox').focus();
                        });
                    }
                }else if(r.status ==3){  //条码对应多个货品
                    purchasePickList.thatObj.goods_list = r.data;
                    var paramsObj = purchasePickList.paramsObj;
                    $('#flag_set_dialog').dialog({
                        title:paramsObj.select.title,
                        iconCls:'icon-save',
                        width:paramsObj.select.width==undefined?764:paramsObj.select.width,
                        height:paramsObj.select.height==undefined?560:paramsObj.select.height,
                        closed:false,
                        inline:true,
                        modal:true,
                        href:paramsObj.select.url+'?parent_datagrid_id='+paramsObj.datagrid.id+'&parent_object=stockin&goods_list_dialog=flag_set_dialog',
                        buttons:[]
                    });

                }else{
                    data = r.data.waybill_print_info;
                    printerInfo = r.data.print_data;
                    sortInfo = r.data.sort_data;
                    goods_info = r.data.goods_data;
                    baseSet = r.data.stalls_base_set;
                    customTempUrl = r.data.custom_template_url;
                    row = r.data.row;
                    detail = r.data.goods;

                    purchasePickList.order_no = sortInfo.trade_no;
                    order_no = sortInfo.trade_no;

                    if(printerInfo.logistic_printer == '' || printerInfo.tag_printer ==''){
                        messager.alert('请先设置打印机',undefined,function (){
                            $('#uniqueCodeId').textbox('textbox').focus();
                        });
                        return;
                    }
                    if(data != -1)
                    {
                        printData = purchasePickList.getLogisticsPrintData(data,customTempUrl,row,detail,data.waybill_info.waybillCode);
                        $("#logistics_no").html(data.waybill_info.waybillCode);
                        if(baseSet.print_logistics == 1){
                            purchasePickList.newPrint(printData,printerInfo.logistic_printer,22);
                        }
                    }
                    orderPrintData = purchasePickList.getTagPrintData(order_no,printerInfo.tag_template,goods_info);
                    if(baseSet.print_tag ==1){
                        purchasePickList.newPrint(orderPrintData,printerInfo.tag_printer,32);
                    }

					if(sortInfo.sort_finish=='分拣完成' && $("#stalls_mode").prop('checked') == false){
					//	$('#sorting_list').textbox('setValue','');
						$('#uniqueCodeId').textbox('textbox').focus();
					}
					if(sortInfo.sort_finish=='订单全部分拣完成' && $("#stalls_mode").prop('checked') == false){
						$('#sorting_list').textbox('setValue','');
						$('#sorting_list').textbox('textbox').focus();
					}
//                    if(sortInfo.sort_finish=='分拣完成'&&is_voice){$("#stalls_pick_success_sound")[0].play();}
//                    if(sortInfo.sort_finish=='还有非档口货品未分拣'&&is_voice){$("#stalls_pick_unstalls_sound")[0].play();}
//
//                    if(is_voice){play_sortNum(sortInfo.box_no);}

                    var root_path = "/Public/Image/";
					var sound_info = '';
                    if(is_voice){
                        if(sortInfo.sort_finish=='分拣完成'){
                            sound_info = root_path + 'sort_success.wav';
                        }else if(sortInfo.sort_finish=='还有非档口货品未分拣'){
                            sound_info = root_path + 'unstalls.wav';
                        }
                        if(sortInfo.box_no == ''){
                            if(sortInfo.sort_finish=='分拣完成'){$("#stalls_pick_success_sound")[0].play();}
                            if(sortInfo.sort_finish=='还有非档口货品未分拣'){$("#stalls_pick_unstalls_sound")[0].play();}
                        }else {
                            play_sortNum(sortInfo.box_no,sound_info);
                        }
                    }

                    bandDataToHtml(sortInfo,order_no,goods_info,row);
                    if(r.status ==2){ //非电子面单，分拣流程正常执行，提示去单据打印界面打印。
                        if(baseSet.print_logistics == 1){
                            messager.alert(r.msg,undefined,function (){
                                $('#uniqueCodeId').textbox('textbox').focus();
                            });
                            if(is_voice){$("#stalls_pick_warn_sound")[0].play();}
                        }
                    }
                }
            });
        }
        function play_sortNum(str,sound_info){

            if((str == '')&&(sound_info ==''))
                return;
            var root_path = "/Public/Image/sound/";
            var stack = [];//生成一个栈
            var url = '';
            if(str.length !=0){
                for(var i=str.length-1;i>=0;i--){
                    url = root_path+str.charAt(i)+'.wav';
                    stack.push(url);
                }
            }
            if(sound_info.length !=0){stack.push(sound_info);}
            var myAudio = new Audio();
            myAudio.preload = true;
            myAudio.controls = true;
            myAudio.src = stack.pop();//每次读数组最后一个元素
            myAudio.addEventListener('ended', playEndedHandler, false);
            myAudio.play();
//            document.getElementById("audioBox").appendChild(myAudio);
            myAudio.loop = false;//禁止循环，否则无法触发ended事件
            function playEndedHandler(){
                myAudio.src = stack.pop();
                myAudio.play();
                !stack.length && myAudio.removeEventListener('ended',playEndedHandler,false);//只有一个元素时解除绑定
            }
        }
        function selectGoodInBarCode(row){
            pickListGoods(purchasePickList.uniqueCode,row);
        }
    </script>