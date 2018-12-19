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

     <div id="<?php echo ($id_list["edit"]); ?>">
     </div>
	 <div id = "<?php echo ($id_list["split"]); ?>"></div>
	 <div id = "<?php echo ($id_list["add"]); ?>"></div>
	  <div id = "<?php echo ($id_list["dialog"]); ?>"></div>

<!-- toolbar -->

 <div id="<?php echo ($id_list["toolbar"]); ?>" style="padding:5px;height:auto">
        <form id="<?php echo ($id_list["form"]); ?>" class="easyui-form" method="post">
            <div class="form-div">
                <label style="width: 80px;">　档口单号：</label><input class="easyui-textbox txt" type="text" name="search[stalls_no]" style="width: 130px;"/>
				<label style="width: 80px;">　　供应商：</label><select class="easyui-combobox sel" name="search[provider_id]" data-options="panelHeight:'200px',editable:false " style="width: 130px;">
						<?php if(is_array($provider_array)): $i = 0; $__LIST__ = $provider_array;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?></select> 
				<label style="width: 80px;">唯一码状态：</label><input class="easyui-combobox txt" name="search[unique_print_status]" data-options="valueField:'id',textField:'name',data:formatter.get_data('unique_print_status')" />
				<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-search'" onclick="stallsManagement.submitSearchForm();">搜索</a>
			    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-redo'" onclick="stallsManagement.loadFormData();">重置</a>
            
			</div>	
			<div class="form-div">
				<label style="width: 80px;">档口单状态：</label><input class="easyui-combobox txt" name="search[status]" data-options="valueField:'id',textField:'name',data:formatter.get_data('stalls_status')" />
				<label style="width: 80px;">　收货仓库：</label><select class="easyui-combobox sel" name="search[warehouse_id]" data-options="panelHeight:'200px',editable:false " >
						<?php if(is_array($warehouse_array)): $i = 0; $__LIST__ = $warehouse_array;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?></select> 
				<label style="width: 80px;">　　采购人：</label><select class="easyui-combobox sel" name="search[purchaser_id]" data-options="panelHeight:'200px',editable:false " >
						<?php if(is_array($employee_array)): $i = 0; $__LIST__ = $employee_array;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?></select> 
			    </div>
        </form>
        <input type="hidden" id="<?php echo ($id_list["hidden_flag"]); ?>" value="1">
       <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-add',plain:true" onclick = "stallsManagement.addStallsOrder()";>生成档口单</a>　
		<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-edit',plain:true" onclick = "stallsManagement.edit()";>编辑</a>
        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-redo',plain:true" onclick = "stallsManagement.cancelStallsOrder()";>取消</a>
		<a href="javascript:void(0)" class="easyui-menubutton" data-options="iconCls:'icon-save',plain:true,menu:'#stallsmanagement_split'" onclick = "stallsManagement.split_order()";>拆单</a>
		<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-print',plain:true" onclick = "stallsManagement.print()";>打印唯一码</a>
		<div id="stallsmanagement_split" >
			<div data-options="iconCls:'icon-split'" onclick="stallsManagement.oneSplit()">按入库状态拆分</div>
		</div>
	
	</div>
	<script>
	//# sourceURL=stallsManagement.js
	var codeWs;
	$(function(){
		setTimeout(function(){
			stallsManagement = new RichDatagrid(JSON.parse('<?php echo ($params); ?>'));
			stallsManagement.setFormData();
			stallsManagement.select_box = {};
			stallsManagement.edit = function(){
				var that = this;
				var data = $('#'+this.params.datagrid.id).datagrid('getSelected');
				if(data == null){
					messager.alert('请先选择行！');
					return ;
				}
				var status = data.status;
				if(status != 20){
					messager.alert('档口单状态不正确,只能编辑档口单状态是编辑中的订单！');
					return ;
				}
				stallsManagement.showDialog(this.params.edit.id,this.params.edit.title,this.params.edit.url+"?id="+data.id+'&parent_info='+'{"dialog_id":"'+that.params.edit.id+'","datagrid_id":"'+that.params.datagrid.id+'"}',500,1000,[]);
				
				
			},
			
			stallsManagement.cancelStallsOrder = function(){
				var that = this;
				var data = $('#'+this.params.datagrid.id).datagrid('getSelected');
				if(data == null){
					messager.alert('请先选择行！');
					return ;
				}
				if(data.status!=20){
					messager.alert('只能取消编辑中的档口单！');
					return ;
				}
				var id = data.id;
				messager.confirm('确定取消档口单',function(r){
					if(r){
						$.post("<?php echo U('Purchase/StallsOrderManagement/cancelStallsOrder');?>",{'id':id},function(r){
							switch(r.status){
								case 1:
									messager.alert(r.info);
									break;
								case 0:
									var index = $('#'+that.params.datagrid.id).datagrid('getRowIndex',data);
									$('#'+that.params.datagrid.id).datagrid('updateRow',{index:index,row:{status:10,}});
									break;
								default :
									messager.alert('系统错误,请联系管理员');
							}
						});
					}
				});
			}
			stallsManagement.split_order = function(){
				var that = this;
				if(stallsManagement.selectRows==undefined) {messager.alert('请选择拆分的订单!'); return false;}
				var row=stallsManagement.selectRows[0];
				if(row.goods_count!=undefined&&row.goods_count<2){messager.alert('档口单只有一个货品，不可拆分'); return false;}
				if(row.status!=undefined&&row.status!=20){messager.alert('档口单状态不正确，只能拆分编辑中的单子'); return false;}
				if(row.id == 0){messager.alert('单子不存在'); return false;}
				var buttons=[ {text:'确定',handler:function(){stallsManagement.submitTradeCheckDialog();}}, {text:'取消',handler:function(){$('#'+that.params.id_list.split).dialog('close');}} ];
				stallsManagement.showDialog(that.params.id_list.split,'拆分',that.params.dialog.url+'?id='+row.id,560,764,buttons);

			}
			stallsManagement.oneSplit = function(){
				var that = this;
				var resultBeforeCheck = [];
				var selects_info = {};
				if(stallsManagement.selectRows==undefined) {messager.alert('请选择拆分的订单!'); return false;}
				var selected_rows=stallsManagement.selectRows;
				var ids = '';
				for(var item in selected_rows){
					var temp_result = {'stalls_id':selected_rows[item]['id'],'stalls_no':selected_rows[item]['stalls_no']};
					if(parseInt(selected_rows[item]['status'])!=20){
						temp_result['msg'] = "档口单状态不正确，只能拆分编辑中的单子";
						resultBeforeCheck.push(temp_result);
						continue;
					}
					if(parseInt(selected_rows[item]['goods_count'])<2){
						temp_result['msg'] = "档口单只有一个货品，不可拆分";
						resultBeforeCheck.push(temp_result);
						continue;
					}
					var temp_index = $('#'+that.params.datagrid.id).datagrid('getRowIndex',selected_rows[item]);
					selects_info[temp_index] = selected_rows[item].id;
				}
				if($.isEmptyObject(selects_info))
				{
					$.fn.richDialog("response", resultBeforeCheck, "stalls",{close:function(){if(stallsManagement){stallsManagement.refresh();}}});
					return;
				}
				messager.confirm('确定拆分吗？', function(r){
					if(r){
						$.post("<?php echo U('StallsOrderManagement/oneSplit');?>", {ids:JSON.stringify(selects_info)}, function(result){
							if(parseInt(result.status) == 0){
								that.refresh();
								return true;
							}
							if(parseInt(result.status) == 1){
								messager.alert(result.info);
								that.refresh();
								return false;
							}
							if(parseInt(result.status) == 2){
								if(!$.isEmptyObject(result.data.fail)){
									//调用dialog显示处理结果
									$.fn.richDialog("response", result.data.fail, "stalls",{close:function(){if(stallsManagement){stallsManagement.refresh();}}});
								}
								return true;
							}
							return;
						},'json');
					}else{return;}
				});
			}
			
			stallsManagement.print = function(){
				var that = this;
                var data = $('#'+this.params.datagrid.id).datagrid('getSelections');
				var selects_info = {};
                if($.isEmptyObject(data)){
                    messager.alert("请选择操作的行!");
                    return;
                }
				var ids = "";
				for(var item in data){	
					 var temp_result = {'message':''};
					if(data[item]['status']!=20){
						temp_result['message'] = "不是编辑中的单子";
					}
					if(data[item]['unique_print_status']!=0){
						temp_result['message'] = "单子已打印唯一码";
					}
					var temp_index = $('#'+this.params.datagrid.id).datagrid('getRowIndex',data[item]);
					ids += data[item].id + ",";
				}
				if(temp_result['message'] != ''){
					 messager.confirm(temp_result['message']+'确定继续打印吗？',function(r){
						if(r){
							that.postPrint(ids);
						}
					 });
				}else{
					that.postPrint(ids);
				}
			}
			
			stallsManagement.postPrint = function(info){
				stallsManagement.showDialog('<?php echo ($id_list["dialog"]); ?>','打印唯一码',"<?php echo U('StallsOrderManagement/PrintCode');?>?ids="+info,190,350,[{text:"取消",handler:function(){$("#"+stallsManagement.params.id_list.dialog).dialog('close');}}]);
			}
			
			stallsManagement.newSelectPrinter = function(){
                    var request = {
                        "cmd":"getPrinters",
                        "requestID":"123458976"+"99",
                        "version":"1.0",
                    }
                    codeWs.send(JSON.stringify(request));
                }
			stallsManagement.connectStockWS = function(){
				if(codeWs == undefined){
					codeWs = new WebSocket("ws://127.0.0.1:13528");
					codeWs.onmessage = function(event){stallsManagement.onStockMessage(event);};
					codeWs.onerror = function(){stallsManagement.onStockError();};
				}
				return ;
			}
			stallsManagement.onStockMessage = function(event){
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
                                    stallsManagement.select_box.printer_list.combobox({
                                        valueField: 'name',
                                        textField: 'name',
                                        data: response_result.printers,
                                        value: response_result.defaultPrinter
                                    });
                                    stallsManagement.select_box.printer_list.combobox('reload');
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
										var rows = $('#'+stallsManagement.params.datagrid.id).datagrid('getSelections');
										var rows_id_list = '';
										for(var i=0; i<rows.length; i++){
										   rows_id_list += rows[i].id + ',';
										}
										rows_id_list = rows_id_list.substr(0,rows_id_list.length-1);
										$.post("<?php echo U('Purchase/StallsOrderManagement/update_unique_status');?>",{ids:rows_id_list},function(ret){
											 if(ret.status == 1){
												messager.alert(ret.info);
												return;
											 }
											  messager.alert("打印唯一码完成");
											  stallsManagement.refresh();
											$('#print_code').linkbutton({text:'打印',disabled:false});
										});
              
                                    }
                                    $("#<?php echo ($id_list["dialog"]); ?>").dialog('close');
                                }else if(response_result.taskStatus == "failed"){
                                    messager.alert("打印失败");
                                    $('#print_code').linkbutton({text:'打印',disabled:false});
                                }
	
                                break;
                            }
                        }

                    }
                }
				stallsManagement.onStockError = function(){
                    codeWs = null;
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
				stallsManagement.changeTemplatePage=function(){
                    open_menu('打印模板','<?php echo U("Setting/NewPrintTemplate/getNewPrintTemplate");?>');
                    $('#<?php echo ($id_list["dialog"]); ?>').dialog('close');
                }
			stallsManagement.printCode = function(){
                    var that = this;
                    var rows = $('#'+stallsManagement.params.datagrid.id).datagrid('getSelections');
                    if($.isEmptyObject(rows)){
                        messager.alert('请先选择需要打印的行!');
                        return false;
                    }
					
                    var printer = stallsManagement.select_box.printer_list.combobox('getValue');
                    var templateId = stallsManagement.select_box.template_list.combobox('getValue');
                    if(templateId == ""){
                        messager.alert("预览错误：没有选择模板，请到模板列表页面下载模板");
                        $("#<?php echo ($id_list["dialog"]); ?>").dialog('close');
                        return ;
                    }
                    $('#print_code').linkbutton({text:'打印中...',disabled:true});               
                    var rows_id_list = '';
                    for(var i=0; i<rows.length; i++){
                        rows_id_list += rows[i].id + ',';
                    }
                    rows_id_list = rows_id_list.substr(0,rows_id_list.length-1);
                    $.post("<?php echo U('Purchase/StallsOrderManagement/getPrintCode');?>",{ids:rows_id_list},function(ret){
                        if(ret.status == 1){
							messager.alert(ret.info);
							return;
						}
						var contents = stallsManagement.template_contents;
                        var datas = that.getCodeData(contents,templateId,ret.info);
                        console.log(datas);
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
                        codeWs.send(JSON.stringify(request));
                    });
				}
				stallsManagement.printerSetting = function(){
                    this.connectStockWS();
                    var request = {
                        "cmd":"printerConfig",
                        "requestID":"123458976",
                        "version":"1.0",}
                    codeWs.send(JSON.stringify(request));
                }
				stallsManagement.previewPrintcode= function(){
                    var that = this;
                    var templateId = stallsManagement.select_box.template_list.combobox('getValue');
                    if(templateId == ""){
                        messager.alert("预览错误：没有选择模板，请到模板列表页面下载模板");
                        $("#<?php echo ($id_list["dialog"]); ?>").dialog('close');
                        return ;
                    }
                    var contents = stallsManagement.template_contents;
                    var rows = $('#'+stallsManagement.params.datagrid.id).datagrid('getSelections');
                    var rows_id_list = '';
                    for(var i=0; i<rows.length; i++){
                       rows_id_list += rows[i].id + ',';
                    }
                    rows_id_list = rows_id_list.substr(0,rows_id_list.length-1);
                    $.post("<?php echo U('Purchase/StallsOrderManagement/getPrintCode');?>",{ids:rows_id_list},function(ret){
						if(ret.status == 1){
							messager.alert(ret.info);
							return;
						}
						var datas = that.getCodeData(contents, templateId,ret.info);
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
                        codeWs.send(JSON.stringify(request));
                    });
                }
				
				 stallsManagement.getCodeData = function(contents,templateId,unique_code_data){
                    contents = JSON.parse(contents[templateId]);
                    var templateURL = contents.custom_area_url;
                    var rows = $('#'+stallsManagement.params.datagrid.id).datagrid('getSelections');
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

										
										}
									}
								}
							]
						});
					}
				
                    return datas;
                }
				
				stallsManagement.onPrinterSelect = function(printer_name){
                    var templateId = stallsManagement.select_box.template_list.combobox('getValue');
                    var contents = stallsManagement.template_contents;
                    var content = contents[templateId];
                    if(content.defaultPrinter != undefined && content.default_printer == printer_name)
                        return;
                    else
                        messager.confirm("您确定把\""+printer_name+"\"设置为此模板的打印机么？",function(r){
                            if(r){
                                stallsManagement.setDefaultPrinter(content,printer_name,templateId);
                            }
                        });
                }
                stallsManagement.setDefaultPrinter=function(content,printor,templateId){
                    content = JSON.parse(content);
                    content.default_printer = printor;
                    $.post("<?php echo U('Purchase/StallsOrderManagement/setDefaultPrinter');?>",{content:JSON.stringify(content),templateId:templateId},function(ret){
                        if(1 == ret.status){
                            messager.alert(ret.msg);
                        }else {
                            stallsManagement.template_contents[templateId] = JSON.stringify(content);
                        }
                    });
                }
				stallsManagement.templateOnSelect=function () {
                    if(stallsManagement.select_box.template_list.combobox('getData').length == 0)
                    {
                        return;
                    }
                    var print_list = stallsManagement.select_box.printer_list.combobox('getData');
                    var content = JSON.parse(stallsManagement.template_contents[stallsManagement.select_box.template_list.combobox('getValue')]);
                    if(undefined != content.default_printer && JSON.stringify(print_list).indexOf(content.default_printer) != -1){
                        stallsManagement.select_box.printer_list.combobox('setValue',content.default_printer);
                    }
                }
				stallsManagement.addStallsOrder = function(){
					stallsManagement.showDialog(this.params.add.id,this.params.add.title,this.params.add.url,500,1000,[]);
				}
			
		},0);
	});
	
	</script>
	
<!-- layout-south-tabs, call add_tabs js function to add tabs -->


</body>
</html>