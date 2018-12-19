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

     <div id="<?php echo ($id_list["add"]); ?>">
     </div>
	  <div id="<?php echo ($id_list["dialog"]); ?>">
     </div>
	<div id="<?php echo ($id_list["edit"]); ?>">
     </div>

<!-- toolbar -->

 <div id="<?php echo ($id_list["toolbar"]); ?>" style="padding:5px;height:auto">
	<form id="<?php echo ($id_list["form"]); ?>" class="easyui-form" method="post">
		<div class="form-div">
			<label>唯一码：</label><input class="easyui-textbox txt" type="text" name="search[unique_code]" />
			<label>订单：</label><input class="easyui-textbox txt" type="text" name="search[trade_no]" />
			<label>商家编码：</label><input class="easyui-textbox txt" type="text" name="search[spec_no]" />
            <label>取货状态：</label><input class="easyui-combobox txt" text="txt" name="search[pickup_status]" data-options="panelHeight:110,valueField:'id',textField:'name',data:[{'id':'all','name':'全部'},{'id':'0','name':'未取货'},{'id':'1','name':'已取货'}],editable:false,value:'all'">
            <label >分拣框编号：</label><input class="easyui-textbox txt" type="text" name="search[box_no]" />
            <label>生成档口单：</label><input class="easyui-combobox txt" text="txt" name="search[generate_status]" data-options="panelHeight:110,valueField:'id',textField:'name',data:[{'id':'all','name':'全部'},{'id':'0','name':'未生成'},{'id':'1','name':'已生成'}],editable:false,value:'all'">
            <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-next',plain:true" onClick="open_menu('档口单管理', '<?php echo U('Purchase/StallsOrderManagement/show');?>')">档口单管理</a>
		</div>
		<div class="form-div">
			<!--<label>订单状态：</label><input class="easyui-combobox txt" text="txt" name="search[trade_status]" data-options="panelHeight:110,valueField:'id',textField:'name',data:[{'id':'all','name':'全部'},{'id':'0','name':'未驳回'},{'id':'1','name':'已驳回'}],editable:false,value:'all'">-->
            <label>供应商：</label><select class="easyui-combobox sel" name="search[provider_id]" data-options="panelHeight:'200px',editable:false " >
            <option value="all">全部</option><?php if(is_array($list["provider"])): $i = 0; $__LIST__ = $list["provider"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?></select>
            <label>仓库：</label><select class="easyui-combobox sel" name="search[warehouse_id]" data-options="panelHeight:'200px',editable:false " >
            <option value="all">全部</option><?php if(is_array($list["warehouse"])): $i = 0; $__LIST__ = $list["warehouse"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?></select>
            <label>入库状态：</label><input class="easyui-combobox txt" text="txt" name="search[stockin_status]" data-options="panelHeight:110,valueField:'id',textField:'name',data:[{'id':'all','name':'全部'},{'id':'0','name':'未入库'},{'id':'1','name':'已入库'}],editable:false,value:'all'">
            <label>吊牌打印：</label><input class="easyui-combobox txt" text="txt" name="search[tag_print_status]" data-options="panelHeight:110,valueField:'id',textField:'name',data:[{'id':'all','name':'全部'},{'id':'0','name':'未打印'},{'id':'1','name':'已打印'}],editable:false,value:'all'">
            <label>物流单打印：</label><input class="easyui-combobox txt" text="txt" name="search[logistics_print_status]" data-options="panelHeight:110,valueField:'id',textField:'name',data:[{'id':'all','name':'全部'},{'id':'0','name':'未打印'},{'id':'1','name':'已打印'}],editable:false,value:'all'">
            <label>唯一码打印：</label><input class="easyui-combobox txt" text="txt" name="search[unique_print_status]" data-options="panelHeight:110,valueField:'id',textField:'name',data:[{'id':'all','name':'全部'},{'id':'0','name':'未打印'},{'id':'1','name':'已打印'}],editable:false,value:'all'">
            <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-search'" onclick="stalls_less_goods.submitSearchForm();">搜索</a>
            <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-redo'" onclick="stalls_less_goods.loadFormData();">重置</a>
        </div>
        <!--<div class="form-div">-->
        <!--</div>-->
		<div class="form-div">
		<label>爆款码：</label><input class="easyui-combobox txt" text="txt" name="search[hot_print_status]" data-options="panelHeight:110,valueField:'id',textField:'name',data:[{'id':'all','name':'全部'},{'id':'1','name':'未打印'},{'id':'2','name':'已打印'},{'id':'0','name':'非爆款货品'}],editable:false,value:'all'">
		</div>
        <div class="form-div">
			<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-remove',plain:true" onclick = "stalls_less_goods.remove('stalls_less_goods')";>删除</a>
			<a href="javascript:void(0)" class="easyui-menubutton" data-options="iconCls:'icon-print',plain:true,menu:'#uniquecode_print'" onclick = "stalls_less_goods.print(0)";>打印唯一码</a>
            <div id="uniquecode_print">
                <div data-options="iconCls:'icon-print'" onclick="stalls_less_goods.print(1)">打印多排唯一码</div>
            </div>
            <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-edit',plain:true" onclick="setDatagridField('Purchase/StallsOrder','stalls_less_goods_detail','<?php echo ($datagrid["id"]); ?>',1)">设置表头</a>
		</div>
	</form>
</div>

<script>
//# sourceURL=stalls_less_goods.js
var onlycodeWs;
	$(function(){
		setTimeout(function(){
			stalls_less_goods = new RichDatagrid(JSON.parse('<?php echo ($params); ?>'));
			stalls_less_goods.setFormData();
			stalls_less_goods.select_box = {};
			stalls_less_goods.print = function(isMulti){
				var that = this;
                var data = $('#'+this.params.datagrid.id).datagrid('getSelections');
				var selects_info = {};
                if($.isEmptyObject(data)){
                    messager.alert("请选择操作的行!");
                    return;
                }
				var is_hot = 0;
				for(var k in data){
					if(data[k].hot_status == 1){
						is_hot = 1;
						break;
					}
				}
				if(is_hot == 1){
					messager.confirm('货品已生成爆款单,打印唯一码可能导致重复分拣时是否继续打印?',function(r){
						if(r){
							that.postPrint(isMulti);
						}
					});
				}else{
					that.postPrint(isMulti);
				}
			}
			stalls_less_goods.postPrint = function(isMulti){
				stalls_less_goods.showDialog('<?php echo ($id_list["dialog"]); ?>','打印唯一码',"<?php echo U('StallsOrderManagement/PrintOnlyCode');?>"+"?isMulti="+isMulti,190,350,[{text:"取消",handler:function(){$("#"+stalls_less_goods.params.id_list.dialog).dialog('close');}}]);
			}
			stalls_less_goods.printCode = function(isMulti){
                    var that = this;
                    var rows = $('#'+stalls_less_goods.params.datagrid.id).datagrid('getSelections');
                    if($.isEmptyObject(rows)){
                        messager.alert('请先选择需要打印的行!');
                        return false;
                    }
					
                    var printer = stalls_less_goods.select_box.printer_list.combobox('getValue');
                    var templateId = stalls_less_goods.select_box.template_list.combobox('getValue');
                    if(templateId == ""){
                        messager.alert("预览错误：没有选择模板，请到模板列表页面下载模板");
                        $("#<?php echo ($id_list["dialog"]); ?>").dialog('close');
                        return ;
                    }
                    $('#print_onlycode').linkbutton({text:'打印中...',disabled:true});
                    var rows_id_list = '';
                    for(var i=0; i<rows.length; i++){
                        rows_id_list += rows[i].id + ',';
                    }
                    rows_id_list = rows_id_list.substr(0,rows_id_list.length-1);
                    $.post("<?php echo U('Purchase/StallsOrderManagement/getPrintOnlyCode');?>",{ids:rows_id_list},function(ret){
                        if(ret.status == 1){
							messager.alert(ret.info);
							return;
						}
						var contents = stalls_less_goods.template_contents;
                        var datas = that.getCodeData(contents,templateId,ret.info);
                        if(isMulti ==1){
                            var multiData = stalls_less_goods.getMultiArrangeData(datas);
                            datas = multiData;
                        }
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
                                'taskID' : requestID +''+'16',
                                'printer' : printer,//'',
                                'preview' : false,
                                'notifyMode':'allInOne',
                                'documents' : datas
                            }
                        };
                        onlycodeWs.send(JSON.stringify(request));
                    });
				}
				stalls_less_goods.getMultiArrangeData = function (datas){
                    var data_arr = [];
                    var finish_arr = [];
                    var flag = 0;
			        for(var i=0;i<datas.length;i++){
                        var temp_arr = [];
                        flag = i%2;
                        if(flag ==1){
                            temp_arr.push(datas[i],datas[i-1]);
                            data_arr.push(temp_arr);
                        }
                    }
                    if(datas.length%2 !=0){
                        var temp_array = [];
                        temp_array.push(datas[datas.length-1]);
                        data_arr.push(temp_array);
                    }
                    var obj = datas[0].contents[0].data.unique_code_block;
                    var keys = Object.keys(obj);
                    var obj_key;
                    for(var j=0;j<data_arr.length;j++){
                        if(data_arr[j].length ==2){
                            for(var k=0;k<keys.length;k++){
                                obj_key = keys[k]+'_r';
                                data_arr[j][0].contents[0].data.unique_code_block[obj_key]=data_arr[j][1].contents[0].data.unique_code_block[keys[k]];
                            }
                            finish_arr.push(data_arr[j][0]);
                        }else if(data_arr[j].length ==1){
                            finish_arr.push(data_arr[j][0]);
                        }
                    }

                    return finish_arr;
                }
				stalls_less_goods.printerSetting = function(){
                    this.connectStockWS();
                    var request = {
                        "cmd":"printerConfig",
                        "requestID":"123458976",
                        "version":"1.0",}
                    onlycodeWs.send(JSON.stringify(request));
                }
				stalls_less_goods.previewPrintcode= function(isMulti){
                    var that = this;
                    var templateId = stalls_less_goods.select_box.template_list.combobox('getValue');
                    if(templateId == ""){
                        messager.alert("预览错误：没有选择模板，请到模板列表页面下载模板");
                        $("#<?php echo ($id_list["dialog"]); ?>").dialog('close');
                        return ;
                    }
                    var contents = stalls_less_goods.template_contents;
                    var rows = $('#'+stalls_less_goods.params.datagrid.id).datagrid('getSelections');
                    var rows_id_list = '';
                    for(var i=0; i<rows.length; i++){
                       rows_id_list += rows[i].id + ',';
                    }
                    rows_id_list = rows_id_list.substr(0,rows_id_list.length-1);
                    $.post("<?php echo U('Purchase/StallsOrderManagement/getPrintOnlyCode');?>",{ids:rows_id_list},function(ret){
						if(ret.status == 1){
							messager.alert(ret.info);
							return;
						}
						var datas = that.getCodeData(contents, templateId,ret.info);
                        if(isMulti ==1){
                            var multiData = stalls_less_goods.getMultiArrangeData(datas);
                            datas = multiData;
                        }
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
                                'taskID': requestID + '' + '666',
                                'printer': "",
                                'preview': true,
                                'previewType': 'pdf',
                                'documents': datas
                            }
                        };
                        onlycodeWs.send(JSON.stringify(request));
                    });
                }
				 stalls_less_goods.getCodeData = function(contents,templateId,unique_code_data){
                    contents = JSON.parse(contents[templateId]);
                    var templateURL = contents.custom_area_url;
                    var rows = $('#'+stalls_less_goods.params.datagrid.id).datagrid('getSelections');
                    if($.isEmptyObject(rows)){
                        messager.alert('请先选择需要打印的行!');
                        return false;
                    }
                    var datas = [],row;
                    var now_date = new Date();
                    var now_millisecond = now_date.getTime();
                    var ID = 0;  
					if(unique_code_data == null){
						unique_code_data = [];                        
					}                  
                                       
					for (var k in unique_code_data)
					{
						ID++;
						datas.push({
							'documentID' : now_millisecond.toString().concat(ID.toString()),
							'contents' : [
								{
									'templateURL' : templateURL,
									'data' : {
										unique_code_block :{
											unique_code : $.isEmptyObject(unique_code_data[k]['unique_code'])?'无':unique_code_data[k]['unique_code'],
											provider_name   : $.isEmptyObject(unique_code_data[k]['provider_name'])?'无':unique_code_data[k]['provider_name'],
											warehouse_name   : $.isEmptyObject(unique_code_data[k]['name'])?'无':unique_code_data[k]['name'],
											contact   : $.isEmptyObject(unique_code_data[k]['contact'])?'无':unique_code_data[k]['contact'],
											mobile   : $.isEmptyObject(unique_code_data[k]['mobile'])?'无':unique_code_data[k]['mobile'],
											address   : $.isEmptyObject(unique_code_data[k]['address'])?'无':unique_code_data[k]['address'],
											province   : $.isEmptyObject(unique_code_data[k]['province'])?'无':unique_code_data[k]['province'],
											city   : $.isEmptyObject(unique_code_data[k]['city'])?'无':unique_code_data[k]['city'],
											district   : $.isEmptyObject(unique_code_data[k]['district'])?'无':unique_code_data[k]['district'],
											spec_no   : $.isEmptyObject(unique_code_data[k]['spec_no'])?'无':unique_code_data[k]['spec_no'],
											spec_code   : $.isEmptyObject(unique_code_data[k]['spec_code'])?'无':unique_code_data[k]['spec_code'],
											spec_name   : $.isEmptyObject(unique_code_data[k]['spec_name'])?'无':unique_code_data[k]['spec_name'],
											price   : $.isEmptyObject(unique_code_data[k]['price'])?'无':unique_code_data[k]['price'],
											barcode   : $.isEmptyObject(unique_code_data[k]['barcode'])?'无':unique_code_data[k]['barcode'],
											goods_no   : $.isEmptyObject(unique_code_data[k]['goods_no'])?'无':unique_code_data[k]['goods_no'],
											goods_name   : $.isEmptyObject(unique_code_data[k]['goods_name'])?'无':unique_code_data[k]['goods_name'],
											short_name   : $.isEmptyObject(unique_code_data[k]['short_name'])?'无':unique_code_data[k]['short_name'],
											brand_name   : $.isEmptyObject(unique_code_data[k]['brand_name'])?'无':unique_code_data[k]['brand_name'],
											class_name   : $.isEmptyObject(unique_code_data[k]['class_name'])?'无':unique_code_data[k]['class_name'],
											package_num  : $.isEmptyObject(unique_code_data[k]['package_num'])?'无':unique_code_data[k]['package_num'],
											provider_group_name  : $.isEmptyObject(unique_code_data[k]['provider_group_name'])?'无':unique_code_data[k]['provider_group_name'],
											package_num_int  : $.isEmptyObject(unique_code_data[k]['package_num_int'])?'无':unique_code_data[k]['package_num_int'],
											days  : $.isEmptyObject(unique_code_data[k]['days'])?'无':unique_code_data[k]['days'],
											box_no  : $.isEmptyObject(unique_code_data[k]['box_no'])?'无':unique_code_data[k]['box_no'],
                                            pay_time  : $.isEmptyObject(unique_code_data[k]['pay_time'])?'无':unique_code_data[k]['pay_time'],
											logistics_name  : $.isEmptyObject(unique_code_data[k]['logistics_name'])?'无':unique_code_data[k]['logistics_name'],

                                        }
									}
								}
							]
						});
					}
				
                    return datas;
                }
				
				stalls_less_goods.onPrinterSelect = function(printer_name){
                    var templateId = stalls_less_goods.select_box.template_list.combobox('getValue');
                    var contents = stalls_less_goods.template_contents;
                    var content = contents[templateId];
                    if(content.defaultPrinter != undefined && content.default_printer == printer_name)
                        return;
                    else
                        messager.confirm("您确定把\""+printer_name+"\"设置为此模板的打印机么？",function(r){
                            if(r){
                                stalls_less_goods.setDefaultPrinter(content,printer_name,templateId);
                            }
                        });
                }
                stalls_less_goods.setDefaultPrinter=function(content,printor,templateId){
                    content = JSON.parse(content);
                    content.default_printer = printor;
                    $.post("<?php echo U('Purchase/StallsOrderManagement/setDefaultPrinter');?>",{content:JSON.stringify(content),templateId:templateId},function(ret){
                        if(1 == ret.status){
                            messager.alert(ret.msg);
                        }else {
                            stalls_less_goods.template_contents[templateId] = JSON.stringify(content);
                        }
                    });
                }
				stalls_less_goods.templateOnSelect=function () {
                    if(stalls_less_goods.select_box.template_list.combobox('getData').length == 0)
                    {
                        return;
                    }
                    var print_list = stalls_less_goods.select_box.printer_list.combobox('getData');
                    var content = JSON.parse(stalls_less_goods.template_contents[stalls_less_goods.select_box.template_list.combobox('getValue')]);
                    if(undefined != content.default_printer && JSON.stringify(print_list).indexOf(content.default_printer) != -1){
                        stalls_less_goods.select_box.printer_list.combobox('setValue',content.default_printer);
                    }
                }
				stalls_less_goods.newSelectPrinter = function(){
                    var request = {
                        "cmd":"getPrinters",
                        "requestID":"123458976"+"99",
                        "version":"1.0",
                    }
                    onlycodeWs.send(JSON.stringify(request));
                }
			stalls_less_goods.connectStockWS = function(){
				if(onlycodeWs == undefined){
					onlycodeWs = new WebSocket("ws://127.0.0.1:13528");
					onlycodeWs.onmessage = function(event){stalls_less_goods.onStockMessage(event);};
					onlycodeWs.onerror = function(){stalls_less_goods.onStockError();};
				}
				return ;
			}
			stalls_less_goods.onStockMessage = function(event){
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
                                    stalls_less_goods.select_box.printer_list.combobox({
                                        valueField: 'name',
                                        textField: 'name',
                                        data: response_result.printers,
                                        value: response_result.defaultPrinter
                                    });
                                    stalls_less_goods.select_box.printer_list.combobox('reload');
                                }
                                break;
                            }
                            case 'print':
                            {
                                var taskID = response_result.taskID+"";
                                taskID = taskID.substr(taskID.length-3,taskID.length);
                                if(taskID==666)
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
                                    if(type==16){
                                        var rows = $('#'+stalls_less_goods.params.datagrid.id).datagrid('getSelections');
                                        var rows_id_list = '';
                                        for(var i=0; i<rows.length; i++){
                                            rows_id_list += rows[i].id + ',';
                                        }
                                        rows_id_list = rows_id_list.substr(0,rows_id_list.length-1);
                                        $.post("<?php echo U('Purchase/StallsOrderManagement/update_unique_status');?>",{ids:rows_id_list,type:'less'},function(ret){
                                            if(ret.status == 1){
                                                messager.alert(ret.info);
                                                return;
                                            }
                                            //messager.alert("打印唯一码完成");
                                            //stalls_less_goods.refresh();
                                            for (var k = 0; k < rows.length; ++k) {
                                                var index = $('#' + stalls_less_goods.params.datagrid.id).datagrid('getRowIndex', rows[k]);
                                                $('#'+stalls_less_goods.params.datagrid.id).datagrid('updateRow',{index:index,row:{unique_print_status:'已打印'}});
                                            }
                                            $('#print_onlycode').linkbutton({text:'打印',disabled:false});
                                        });
                                    }
                                    $("#<?php echo ($id_list["dialog"]); ?>").dialog('close');
                                }else if(response_result.taskStatus == "failed"){
                                    messager.alert("打印失败");
                                    $('#print_onlycode').linkbutton({text:'打印',disabled:false});
                                }
	
                                break;
                            }
                        }

                    }
                }
				stalls_less_goods.onStockError = function(){
                    onlycodeWs = null;
                    var print_dialog = '<?php echo ($id_list["dialog"]); ?>';
                    $('#'+print_dialog).dialog({
                        title: '打印组件错误',
                        width: 400,
                        height: 200,
                        closed: false,
                        cache: false,
                        href:  "<?php echo U('Stock/StockSalesPrint/onWSError');?>",
                        modal: true
                    });
                }
				stalls_less_goods.changeTemplatePage=function(){
                    open_menu('打印模板','<?php echo U("Setting/NewPrintTemplate/getNewPrintTemplate");?>');
                    $('#<?php echo ($id_list["dialog"]); ?>').dialog('close');
                }
			
		},0);
	});
</script>

<!-- layout-south-tabs, call add_tabs js function to add tabs -->


</body>
</html>