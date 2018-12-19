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
 <div data-options="region:'south',split:true" style="height:30%;background:#eee;overflow:hidden;">
    <!--<?php if($datagrid["setTabs"] == 1): ?>-->
        <!--<a href="javascript:void(0)" class="easyui-menubutton" style="position: absolute;margin-left: 150px;z-index:10000;" data-options="iconCls:'icon-excel',plain:true,menu:'#common_datagrid_tabs_export'" >导出功能</a>-->
        <!--<div id="common_datagrid_tabs_export">-->
            <!--<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-excel',plain:true" name="exportToExcel" onclick="<?php echo ($datagrid["setTabsClick"]); ?>('csv')">导出csv(推荐)</a>-->
            <!--<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-excel',plain:true" name="exportToExcel" onclick="<?php echo ($datagrid["setTabsClick"]); ?>('excel')">导出到Excel</a>-->
        <!--</div>-->
    <!--<?php endif; ?>-->
    <div class="easyui-tabs" data-options="fit:true,border:false,plain:true" id="<?php echo ($id_list["tab_container"]); ?>"> </div>
</div>
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
	<div id="<?php echo ($id_list["print_dialog"]); ?>"></div>
	 <div id="<?php echo ($id_list["file_dialog"]); ?>" class="easyui-panel" style="padding:25px 50px 25px 50px">
        <form id="<?php echo ($id_list["file_form"]); ?>" method="post" enctype="multipart/form-data">
            <div style="margin-bottom:25px">
                <input class="easyui-filebox" name="file" data-options="prompt:'请选择文件...','buttonText':'请选择文件'" style="width:100%;">
            </div>
            <div align="center">
                <a href="javascript:void(0)" class="easyui-linkbutton" style="width:50%" onclick="purchaseManagement.upload()">上传</a>
            </div>
        </form>
    </div>

<!-- toolbar -->

 <div id="<?php echo ($id_list["toolbar"]); ?>" style="padding:5px;height:auto">
        <form id="<?php echo ($id_list["form"]); ?>" class="easyui-form" method="post">
            <div class="form-div">
                <label style="width: 80px;">采购单号：</label><input class="easyui-textbox txt" type="text" name="search[purchase_no]" style="width: 130px;"/>
				<label style="width: 80px;">　入库单号：</label><input class="easyui-textbox txt" type="text" name="search[stockin_no]" style="width: 130px;"/>
				<label style="width: 80px;">　供应商：</label><select class="easyui-combobox sel" name="search[provider_id]" data-options="panelHeight:'100px',editable:false " style="width: 130px;">
						<?php if(is_array($provider_array)): $i = 0; $__LIST__ = $provider_array;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?></select>
				<label style="width: 80px;">　修改时间：</label><input class="easyui-datebox txt" type="text" name="search[start_time]"  data-options="editable:false"/>
				<label>　至：</label><input class="easyui-datebox txt" type="text"   name="search[end_time]" data-options="editable:false"/>
			</div>
			<div class="form-div">
				<label style="width: 80px;">采购状态：</label><input class="easyui-combobox txt" name="search[status]" data-options="valueField:'id',textField:'name',data:formatter.get_data('purchase_status')" />
				<label style="width: 80px;">　收货仓库：</label><select class="easyui-combobox sel" name="search[warehouse_id]" data-options="panelHeight:'100px',editable:false " >
						<?php if(is_array($warehouse_array)): $i = 0; $__LIST__ = $warehouse_array;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?></select> 
				<label style="width: 80px;">　采购人：</label><select class="easyui-combobox sel" name="search[purchaser_id]" data-options="panelHeight:'100px',editable:false " >
						<?php if(is_array($employee_array)): $i = 0; $__LIST__ = $employee_array;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?></select>
				<label style="margin-left: 12px;"><a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-search'" onclick="purchaseManagement.searchData();">搜索</a></label>
				<label style="width: 80px;"><a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-redo'" onclick="purchaseManagement.loadData();">　重置</a></label>
			    </div>
        </form>
        <input type="hidden" id="<?php echo ($id_list["hidden_flag"]); ?>" value="1">
       　 <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-edit',plain:true" onclick = "purchaseManagement.edit()";>编辑</a>
       <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-search',plain:true" onclick = "purchaseManagement.submitPurchaseOrder()";>审核</a>
	 <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-back',plain:true" onclick="purchaseManagement.revertCheck()">驳回审核</a>
	 <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-redo',plain:true" onclick="purchaseManagement.stopWaiting()">停止采购</a>
	 <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-redo',plain:true" onclick = "purchaseManagement.cancelPurchaseOrder()";>取消</a>
	 <a href="javascript:void(0)" class="easyui-linkbutton" name="purchase_order_print" data-options="iconCls:'icon-print',plain:true" >打印</a>
		<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-save',plain:true" onclick = "purchaseManagement.send()";>推送</a>
		<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-remove',plain:true" onclick = "purchaseManagement.cancel_po()";>取消委外单</a>
		<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-excel',plain:true" onclick="purchaseManagement.uploadDialog()">采购导入</a>
		<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-down_tmp',plain:true" onclick="purchaseManagement.downloadTemplet()">下载模板</a>
		 <a href="javascript:void(0)" class="easyui-menubutton" data-options="iconCls:'icon-excel',plain:true,menu:'#purchase_management_export'" >导出功能</a>
		 <div id="purchase_management_export">
			 <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-excel',plain:true" name="exportToExcel" onclick="purchaseManagement.exportToExcel('csv')">导出csv(推荐)</a>
			 <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-excel',plain:true" name="exportToExcel" onclick="purchaseManagement.exportToExcel('excel')">导出到Excel</a>
		 </div>
		<span style="color:red;margin-left:10px;"><label>合计采购金额：</label><label id="purchase-management-goods-amount" style="margin-right: 10px;">0.0000</label>
	</div>
	<script>
	//# sourceURL = purchaseManagement.js
	var purchaseorderWs;
	$(function(){
		setTimeout(function(){
			var toolbar_id = '<?php echo ($id_list["toolbar"]); ?>';
			var element_selectors ={
				'purchase_order_print'           : $('#'+toolbar_id+" a[name='purchase_order_print']"),
			};
			purchaseManagement = new RichDatagrid(JSON.parse('<?php echo ($params); ?>'));
			$('input',$('#<?php echo ($id_list["form"]); ?>')).bind('keydown',function(e){
				if(e.keyCode==13){
					purchaseManagement.submitSearchForm();
					purchaseManagement.getGoodsAmount();
				}
			});
			purchaseManagement.setFormData();
			purchaseManagement.searchData=function(){
				purchaseManagement.submitSearchForm();
				purchaseManagement.getGoodsAmount();
			}
			purchaseManagement.loadData=function(){
				$("#<?php echo ($id_list["form"]); ?>").form('reset');
				purchaseManagement.loadFormData();
				purchaseManagement.getGoodsAmount();
			}
			purchaseManagement.element_selectors = element_selectors;
			// 合计采购价
			purchaseManagement.setGoodsAmount=function(num){
				num = parseFloat(num).toFixed(4);
				$('#purchase-management-goods-amount').text(num).css('color','red');
			}
			purchaseManagement.params.goods_amount_sum=<?php echo ($id_list["goods_amount_sum"]); ?>;
			purchaseManagement.setGoodsAmount(purchaseManagement.params.goods_amount_sum);
			purchaseManagement.getGoodsAmount=function(){
				var url= "<?php echo U('PurchaseManagement/getGoodsAmount');?>";
				var forms = $('#<?php echo ($id_list["form"]); ?>').form('get');
				var search=JSON.stringify(forms);
				$.post(url+'?search='+search,'',function(res){
					purchaseManagement.setGoodsAmount(res);
					return true;
				});
			}
			purchaseManagement.edit = function(){
				var data = $('#'+this.params.datagrid.id).datagrid('getSelected');
				var rows = $('#' + this.params.datagrid.id).datagrid('getSelections');
				if(data == null){
					messager.alert('请先选择行！');
					return ;
				}
				if (rows.length > 1) {messager.alert("请选择单行编辑!");return false;}
				var status = data.status;
				if(status != 20 && status != 43){
					messager.alert('采购单状态不正确,只能编辑采购状态是编辑中的订单！');
					return ;
				}
				var form_id = "<?php echo ($id_list["form"]); ?>";
				purchaseManagement.showDialog(this.params.edit.id,this.params.edit.title,this.params.edit.url+"?id="+data.id+'&management_info='+'{"form_id":"'+form_id+'","dialog_id":"'+this.params.edit.id+'","datagrid_id":"'+purchaseManagement.params.datagrid.id+'"}',550,1250,[])
				
				
			}
			purchaseManagement.cancelPurchaseOrder = function(){
				var that = this;
				var rows = $('#' + this.params.datagrid.id).datagrid('getSelections');
				if (rows[0] == null) {messager.alert("请选择操作的行!");return;}
				var id_list = '';
				var error_list = {total:0,rows:[]};
				var rows_list = [];
				for(var i=0; i<rows.length;i++){
					if(rows[i].status == 20){
						id_list += rows[i].id + ',';
						rows_list.push(rows[i]);
					}else{
						error_list.total += 1;
						error_list.rows.push({"purchase_no":rows[i].purchase_no,"info":'采购单类型不正确，只能取消编辑中的采购单！'});
					}
				}
				id_list = id_list.substr(0,id_list.length-1);
				if (id_list == '') {$.fn.richDialog("response", error_list, 'purchase_management_result');return;}

				messager.confirm('确定取消采购单',function(r){
					if(r){
						$.post("<?php echo U('Purchase/PurchaseManagement/cancelPurchaseOrder');?>",{'ids':id_list},function(r){
							switch(r.status){
								case 1:
									$.fn.richDialog("response", error_list, 'purchase_management_result');
									break;
								case 0:
									if(error_list.total == 0){
										messager.alert(r.info);
									}else{
										$.fn.richDialog("response", error_list, 'purchase_management_result');
									}
									var index;
									for(var i=0; i<rows_list.length; i++){
										index = $('#' + that.params.datagrid.id).datagrid('getRowIndex', rows_list[i]);
										$('#' + that.params.datagrid.id).datagrid('updateRow', {
											index: index,
											row: {
												status: '10',
											}
										});
									}
									break;
								default :
									messager.alert('系统错误,请联系管理员');
							}
						});
					}
				});
			}
			purchaseManagement.submitPurchaseOrder = function(){
				var that = this;
				var rows = $('#' + that.params.datagrid.id).datagrid('getSelections');
				if (rows[0] == null) {messager.alert("请选择操作的行!");return;}
				var id_list = '';
				var purchase_no_list = '';
				var error_list = {total:0,rows:[]};
				var rows_list = [];
				for(var i=0; i<rows.length;i++){
					if(rows[i].status == 20){
						id_list += rows[i].id + ',';
						purchase_no_list += rows[i].purchase_no + ',';
						rows_list.push(rows[i]);
					}else{
						error_list.total += 1;
						error_list.rows.push({"purchase_no":rows[i].purchase_no,"info":'采购单状态不正确,只能审核采购状态是编辑中的订单！'});
					}
				}
				id_list = id_list.substr(0,id_list.length-1);
				purchase_no_list = purchase_no_list.substr(0,purchase_no_list.length-1);
				if (id_list == '') {$.fn.richDialog("response", error_list, 'purchase_management_result');return;}
				messager.confirm('确定审核采购单?',function(r){
					if(r){
						$.post('<?php echo U("Purchase/PurchaseManagement/submitPurchaseOrder");?>',{'ids':id_list,'purchase_nos':purchase_no_list},function(r){
							if(r == '' ||r == null){
								if(error_list.total == 0){
									messager.alert('审核成功');
								}else{
									$.fn.richDialog("response", error_list, 'purchase_management_result');
								}
								var index;
								for(var i=0; i<rows_list.length; i++){
									index = $('#' + that.params.datagrid.id).datagrid('getRowIndex', rows_list[i]);
									$('#' + that.params.datagrid.id).datagrid('updateRow', {
										index: index,
										row: {
											status: '40',
										}
									});
								}
							}else{
								var r_length = r.length;
								for(var i=0; i< r_length;++i){
									error_list.rows.push({"purchase_no":r[i]['purchase_no'],"info":r[i]['info']});
								}
								error_list.total += r_length;
								$.fn.richDialog("response", error_list, 'purchase_management_result');
								var index;
								var rows_list_err = rows_list;
								for(var i=0; i<rows_list.length; i++){
									for(var j=0; j< r_length;++j){
										if(rows_list[i]['purchase_no'] == r[j]['purchase_no']){
											rows_list_err.splice(i,1);
											continue;
										}
									}
								}
								for(var i=0; i<rows_list_err.length; i++){
									index = $('#' + that.params.datagrid.id).datagrid('getRowIndex', rows_list_err[i]);
									$('#' + that.params.datagrid.id).datagrid('updateRow', {
										index: index,
										row: {
											status: '40',
										}
									});
								}
							}
						});
					}
				});
			}
			
			purchaseManagement.send = function(){
				var that = this;
                var data = $('#'+this.params.datagrid.id).datagrid('getSelections');
				var selects_info = {};
				var resultBeforeCheck = [];
                if($.isEmptyObject(data)){
                    messager.alert("请选择操作的行!");
                    return;
                }
				for(var item in data){	
					 var temp_result = {'result':'推送失败'};
					if(data[item]['status']!=43 && data[item]['status']!=45){
						temp_result['message'] = "不是待推送或推送失败的单子";
						resultBeforeCheck.push(temp_result);
						continue;
					}
					var temp_index = $('#'+this.params.datagrid.id).datagrid('getRowIndex',data[item]);
					selects_info[temp_index] = data[item].id;
				}
				if($.isEmptyObject(selects_info)){
					$.fn.richDialog("response", resultBeforeCheck, "importResponse_suite",{close:function(){if(purchaseManagement){purchaseManagement.refresh();}}});
					return;
				}
                messager.confirm('确定推送采购单吗？',function(r){
                	if(r){
	                	$.post("<?php echo U('Purchase/PurchaseManagement/send');?>",{ids:JSON.stringify(selects_info)},function(r){
							r = JSON.parse(r);
							for(var k in r){
							if(k == 'updated'){messager.alert('推送成功');}
							else if(k == 'error'){messager.alert(r[k]);}
							else{
							    var resultBeforeCheck =  r[1];
								$.fn.richDialog("response", resultBeforeCheck, "wms",'');
								break;
							}
							}
							purchaseManagement.refresh();
	                	});
	                }
                });
			}
			
			purchaseManagement.cancel_po = function(){
				var that = this;
                var data = $('#'+this.params.datagrid.id).datagrid('getSelections');
				var resultBeforeCheck = [];
				var selects_info = {};
                if($.isEmptyObject(data)){
                    messager.alert("请选择操作的行!");
                    return;
                }
				for(var item in data){	
					 var temp_result = {'result':'取消失败'};
					if(data[item]['status']!=48 && data[item]['status']!=43){
						temp_result['message'] = "不是已推送或待推送的订单";
						resultBeforeCheck.push(temp_result);
						continue;
					}
					var temp_index = $('#'+this.params.datagrid.id).datagrid('getRowIndex',data[item]);
					selects_info[temp_index] = data[item].id;
				}
				if($.isEmptyObject(selects_info)){
					$.fn.richDialog("response", resultBeforeCheck, "importResponse_suite",{close:function(){if(purchaseManagement){purchaseManagement.refresh();}}});
					return;
				}
                messager.confirm('确定取消采购单吗？',function(r){
                	if(r){
	                	$.post("<?php echo U('Purchase/PurchaseManagement/cancel_po');?>",{ids:JSON.stringify(selects_info)},function(r){
							r = JSON.parse(r);
							for(var k in r){
							if(k == 'updated'){messager.alert('取消成功');}
							else if(k == 'error'){messager.alert(r[k]);}
							else{
							    var resultBeforeCheck =  r[1];
								$.fn.richDialog("response", resultBeforeCheck, "wms",'');
								break;
							}
							}
	                	});
						purchaseManagement.refresh();
	                }
                });
			}
			
			purchaseManagement.uploadDialog = function(){
				var dialog = $("#<?php echo ($id_list["file_dialog"]); ?>");
				dialog.dialog({
					title : '采购导入',
					width :'350px',
					height : '160px',
					modal : true,
					closed : false,
					inline : true,
					iconCls: 'icon-save',
				});
			}
			
			purchaseManagement.upload = function(){
				var form = $("#<?php echo ($id_list["file_form"]); ?>");
				var dg = $("#<?php echo ($id_list["datagrid"]); ?>");
				var dialog = $("#<?php echo ($id_list["file_dialog"]); ?>");
				var url = "<?php echo U('Purchase/PurchaseManagement/upload');?>";
				$.messager.progress({
					title : "请稍等",
					msg : '该操作可能需要几分钟，请稍等',
					text :'',
					interval:100
				});
				form.form("submit",{
					url:url,
					success:function(r){
						$.messager.progress('close');
						r = JSON.parse(r);
						if(r.status == 1){
							messager.alert(r.msg);
						}else if(r.status == 0){
							dg.datagrid('reload');
							dialog.dialog('close');
						}else{
							$.fn.richDialog('response',r.data,"importResponse");
						}
						form.form("load", {"file": ""});
					},
					
				});
			}
			
			purchaseManagement.downloadTemplet = function(){
                    var url= "<?php echo U('Purchase/PurchaseManagement/downloadTemplet');?>";
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
                };
			purchaseManagement.revertCheck = function(){
				var that = this;
				var rows = $('#' + that.params.datagrid.id).datagrid('getSelections');
				if (rows[0] == null) {messager.alert("请选择操作的行!");return;}
				var id_list = '';
				var purchase_no_list = '';
				var error_list = {total:0,rows:[]};
				var rows_list = [];
				for(var i=0; i<rows.length;i++){
					if(rows[i].status == 40){
						id_list += rows[i].id + ',';
						purchase_no_list += rows[i].purchase_no + ',';
						rows_list.push(rows[i]);
					}else{
						error_list.total += 1;
						error_list.rows.push({"purchase_no":rows[i].purchase_no,"info":'采购单状态不正确,只能驳回采购状态是已审核的订单！'});
					}
				}
				id_list = id_list.substr(0,id_list.length-1);
				purchase_no_list = purchase_no_list.substr(0,purchase_no_list.length-1);
				if (id_list == '') {$.fn.richDialog("response", error_list, 'purchase_management_result');return;}

				messager.confirm('确定驳回采购单?',function(r){
					if(r){
						$.post('<?php echo U("Purchase/PurchaseManagement/revertCheck");?>',{'ids':id_list,'purchase_nos':purchase_no_list},function(r){
							if(r == '' ||r == null){
								if(error_list.total == 0){
									messager.alert('驳回成功');
								}else{
									$.fn.richDialog("response", error_list, 'purchase_management_result');
								}
								var index;
								for(var i=0; i<rows_list.length; i++){
									index = $('#' + that.params.datagrid.id).datagrid('getRowIndex', rows_list[i]);
									$('#' + that.params.datagrid.id).datagrid('updateRow', {
										index: index,
										row: {
											status: '20',
										}
									});
								}
							}else{
								var r_length = r.length;
								for(var i=0; i< r_length;++i){
									error_list.rows.push({"purchase_no":r[i]['purchase_no'],"info":r[i]['info']});
								}
								error_list.total += r_length;
								$.fn.richDialog("response", error_list, 'purchase_management_result');
								var index;
								var rows_list_err = rows_list;
								for(var i=0; i<rows_list.length; i++){
									for(var j=0; j< r_length;++j){
										if(rows_list[i]['purchase_no'] == r[j]['purchase_no']){
											rows_list_err.splice(i,1);
											continue;
										}
									}
								}
								for(var i=0; i<rows_list_err.length; i++){
									index = $('#' + that.params.datagrid.id).datagrid('getRowIndex', rows_list_err[i]);
									$('#' + that.params.datagrid.id).datagrid('updateRow', {
										index: index,
										row: {
											status: '20',
										}
									});
								}
							}
						});
					}
				});
			}
			purchaseManagement.stopWaiting = function(){
				var that = this;
				var rows = $('#' + that.params.datagrid.id).datagrid('getSelections');
				if (rows[0] == null) {messager.alert("请选择操作的行!");return;}
				var id_list = '';
				var purchase_no_list = '';
				var error_list = {total:0,rows:[]};
				var rows_list = [];
				for(var i=0; i<rows.length;i++){
					if(rows[i].status != 50){
						error_list.total += 1;
						error_list.rows.push({"purchase_no":rows[i].purchase_no,"info":'采购单状态不正确,只能停止采购状态是部分到货的订单！'});
					}else if(rows[i].warehouse_type == 11&&rows[i].status == 50 || rows[i].warehouse_type == 15&&rows[i].status == 50){
						error_list.total += 1;
						error_list.rows.push({"purchase_no":rows[i].purchase_no,"info":'采购单仓库类型不正确，不能停止委外仓的订单！'});
					}else{
						id_list += rows[i].id + ',';
						purchase_no_list += rows[i].purchase_no + ',';
						rows_list.push(rows[i]);
					}
				}
				id_list = id_list.substr(0,id_list.length-1);
				purchase_no_list = purchase_no_list.substr(0,purchase_no_list.length-1);
				if (id_list == '') {$.fn.richDialog("response", error_list, 'purchase_management_result');return;}
				messager.confirm('停止采购之后未采购的货品将不再继续采购！确定停止吗？',function(r){
					if(r){
						$.post('<?php echo U("Purchase/PurchaseManagement/stopPurchaseOrder");?>',{'ids':id_list,'purchase_nos':purchase_no_list},function(r){
							if(r == '' ||r == null){
								if(error_list.total == 0){
									messager.alert('停止成功');
								}else{
									$.fn.richDialog("response", error_list, 'purchase_management_result');
								}
								var index;
								for(var i=0; i<rows_list.length; i++){
									index = $('#' + that.params.datagrid.id).datagrid('getRowIndex', rows_list[i]);
									$('#' + that.params.datagrid.id).datagrid('updateRow', {
										index: index,
										row: {
											status: '90',
										}
									});
								}
							}else{
								var r_length = r.length;
								for(var i=0; i< r_length;++i){
									error_list.rows.push({"purchase_no":r[i]['purchase_no'],"info":r[i]['info']});
								}
								error_list.total += r_length;
								$.fn.richDialog("response", error_list, 'purchase_management_result');
								var index;
								var rows_list_err = rows_list;
								for(var i=0; i<rows_list.length; i++){
									for(var j=0; j< r_length;++j){
										if(rows_list[i]['purchase_no'] == r[j]['purchase_no']){
											rows_list_err.splice(i,1);
											continue;
										}
									}
								}
								for(var i=0; i<rows_list_err.length; i++){
									index = $('#' + that.params.datagrid.id).datagrid('getRowIndex', rows_list_err[i]);
									$('#' + that.params.datagrid.id).datagrid('updateRow', {
										index: index,
										row: {
											status: '90',
										}
									});
								}
							}
						});
					}
				});
			}
			purchaseManagement.printPurchaseOrderDialog = function(){
				var that = this;
				var rows = $('#'+that.params.datagrid.id).datagrid('getSelections');
				if($.isEmptyObject(rows)){
					messager.alert('请先选择需要打印的行!');
					return false;
				}
				var ids = "";
				for(var i in rows){
					ids += rows[i].id + ",";
				}
				ids = ids.substr(0,ids.length-1);
				var print_dialog = '<?php echo ($id_list["print_dialog"]); ?>';
				Dialog.show('<?php echo ($id_list["print_dialog"]); ?>','打印采购单',"<?php echo U('Purchase/PurchaseManagement/printPurchase');?>?ids="+ids,190,350,[{text:"取消",handler:function(){$('#'+print_dialog).dialog('close');}}]);

			}
			purchaseManagement.newSelectPrinter = function(){
//                    this.connectStockWS();
				var request = {
					"cmd":"getPrinters",
					"requestID":"123458976"+"99",
					"version":"1.0",
				}
				purchaseorderWs.send(JSON.stringify(request));
			}
			purchaseManagement.printerSetting = function(){
				this.connectStockWS();
				var request = {
					"cmd":"printerConfig",
					"requestID":"123458976",
					"version":"1.0",}
				purchaseorderWs.send(JSON.stringify(request));
			}
			purchaseManagement.connectStockWS = function(){
				if(purchaseorderWs == undefined){
					purchaseorderWs = new WebSocket("ws://127.0.0.1:13528");
					purchaseorderWs.onmessage = function(event){purchaseManagement.onStockMessage(event);};//this.onStockMessage;
					purchaseorderWs.onerror = function(){purchaseManagement.onStockError();};//this.onStockError;
				}
				return ;
			}
			purchaseManagement.onStockMessage = function(event){
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
								purchaseManagement.element_selectors.printer_list.combobox({
									valueField: 'name',
									textField: 'name',
									data: response_result.printers,
									value: response_result.defaultPrinter
								});
								purchaseManagement.element_selectors.printer_list.combobox('reload');
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
									messager.alert("采购单打印完成");
									$('#print_purchase_order').linkbutton({text:'打印',disabled:false});
								}
								$("#<?php echo ($id_list["print_dialog"]); ?>").dialog('close');
							}else if(response_result.taskStatus == "failed"){
								messager.alert("打印失败");
								$('#print_purchase_order').linkbutton({text:'打印',disabled:false});
							}
							//$.messager.progress('close');
							break;
						}
					}

				}
			}
			purchaseManagement.onStockError = function(){
				$('#print_purchase_order').linkbutton({text:'打印',disabled:false});
				purchaseorderWs = null;
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
			}
			purchaseManagement.onPrinterSelect = function(printer_name){
				var templateId = purchaseManagement.element_selectors.template_list.combobox('getValue');
				var contents = purchaseManagement.template_contents;
				var content = contents[templateId];
				if(content.defaultPrinter != undefined && content.default_printer == printer_name)
					return;
				else
					messager.confirm("您确定把\""+printer_name+"\"设置为此模板的打印机么？",function(r){
						if(r){
							purchaseManagement.setDefaultPrinter(content,printer_name,templateId);
						}
					});
			}
			purchaseManagement.setDefaultPrinter = function(content,printor,templateId){
				content = JSON.parse(content);
				content.default_printer = printor;
				$.post("<?php echo U('Goods/GoodsBarcodePrint/setDefaultPrinter');?>",{content:JSON.stringify(content),templateId:templateId},function(ret){
					if(1 == ret.status){
						messager.alert(ret.msg);
					}else {
						purchaseManagement.template_contents[templateId] = JSON.stringify(content);
					}
				});
			}
			purchaseManagement.changeTemplatePage = function(){
				open_menu('打印模板','<?php echo U("Setting/NewPrintTemplate/getNewPrintTemplate");?>');
				$('#<?php echo ($id_list["print_dialog"]); ?>').dialog('close');
			}
			purchaseManagement.previewPurchaseOrder = function(){
				//var that = this;
				var templateId = purchaseManagement.element_selectors.template_list.combobox('getValue');
				if(templateId == ""){
					messager.alert("预览错误：没有选择模板，请到模板列表页面下载模板");
					$("#<?php echo ($id_list["print_dialog"]); ?>").dialog('close');
					return ;
				}
				var contents = purchaseManagement.template_contents;
				var datas = this.getPurchaseOrderData(contents,templateId);
				if(datas === false){
					return;
				}
				this.connectStockWS();
				//var requestID =  parseInt(1000*Math.random());
				var requestID =  (new Date()).valueOf();
				var request = {
					'cmd' : 'print',
					'version' : '1.0',
					'requestID' : requestID,
					'task' : {
						'taskID' : requestID+''+'231',
						'printer' : "",
						'preview' : true,
						'previewType' : 'pdf',
						'documents' : datas
					}
				};
				purchaseorderWs.send(JSON.stringify(request));
			}
			purchaseManagement.printPurchaseOrder = function(){
				var rows = $('#'+purchaseManagement.params.datagrid.id).datagrid('getSelections');
				if($.isEmptyObject(rows)){
					messager.alert('请先选择需要打印的行!');
					return false;
				}
				var printer = purchaseManagement.element_selectors.printer_list.combobox('getValue');
				var templateId = purchaseManagement.element_selectors.template_list.combobox('getValue');
				if(templateId == ""){
					messager.alert("没有选择模板，请到模板列表页面下载模板");
					$("#<?php echo ($id_list["print_dialog"]); ?>").dialog('close');
					return ;
				}
				var contents = purchaseManagement.template_contents;
				var datas = this.getPurchaseOrderData(contents,templateId);
				if(datas === false){
					return;
				}
				var id_list = '';
				for(var i=0; i<rows.length;i++){
						id_list += rows[i].id + ',';
				}
				id_list = id_list.substr(0,id_list.length-1);
				$('#print_purchase_order').linkbutton({text:'打印中...',disabled:true});
				$.post('<?php echo U("Purchase/PurchaseManagement/printPurchaseOrderLog");?>',{'ids':id_list});
				this.connectStockWS();
				//var requestID =  parseInt(1000*Math.random());
				var requestID =  (new Date()).valueOf();
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
				purchaseorderWs.send(JSON.stringify(request));
			}
			purchaseManagement.getPurchaseOrderData = function(contents,templateId){
				contents = JSON.parse(contents[templateId]);
				var templateURL = contents.custom_area_url;
				var rows = $('#'+purchaseManagement.params.datagrid.id).datagrid('getSelections');
				var purchase_derail =  getPurchaseGoodsDetail();
				if($.isEmptyObject(rows)){
					messager.alert('请先选择需要打印的行!');
					return false;
				}
				var datas = [],row;
				var now_date = new Date();
				var now_millisecond = now_date.getTime();
				var ID = 0;
				for (var j = 0; j < rows.length; ++j){
					row = rows[j];
					ID++;
					datas.push({
						'documentID' : now_millisecond.toString().concat(ID.toString()),
						'contents' : [
							{
								'templateURL' : templateURL,
								'data' : {
									purchaseorder :{
										purchase_no : $.isEmptyObject(row.purchase_no)?'无':row.purchase_no,
										creator_name   : $.isEmptyObject(row.creator_name)?'无':row.creator_name,
										purchaser_name : $.isEmptyObject(row.purchaser_name)?'无':row.purchaser_name,
										provider_name : $.isEmptyObject(row.provider_name)?'无':row.provider_name,
										provider_mobile : $.isEmptyObject(row.provider_mobile)?'无':row.provider_mobile,
										provider_address : $.isEmptyObject(row.provider_address)?'无':row.provider_address,
										warehouse_name : $.isEmptyObject(row.warehouse_name)?'无':row.warehouse_name,
										warehouse_contact : $.isEmptyObject(row.warehouse_contact)?'无':row.warehouse_contact,
										warehouse_telno : $.isEmptyObject(row.warehouse_telno)?'无':row.warehouse_telno,
										warehouse_address : $.isEmptyObject(row.warehouse_address)?'无':row.warehouse_address,
										goods_count : $.isEmptyObject(row.goods_count)?'无':row.goods_count,
										goods_fee : $.isEmptyObject(row.goods_fee)?'无':row.goods_fee,
										goods_type_count : $.isEmptyObject(row.goods_type_count)?'无':row.goods_type_count,
										logistics_type : $.isEmptyObject(row.logistics_type)?'无':row.logistics_type,
										post_fee : $.isEmptyObject(row.post_fee)?'无':row.post_fee,
										other_fee : $.isEmptyObject(row.other_fee)?'无':row.other_fee,
										remark : $.isEmptyObject(row.remark)?'无':row.remark,
										tax_fee : $.isEmptyObject(row.tax_fee)?'无':row.tax_fee,
										check_name : $.isEmptyObject(row.check_name)?'无':row.check_name,
										check_time : $.isEmptyObject(row.check_time)?'无':row.check_time,
										created : $.isEmptyObject(row.created)?'无':row.created,
										modified : $.isEmptyObject(row.modified)?'无':row.modified,
										print_date : now_date.getFullYear()+"-"+(now_date.getMonth()+1)+"-"+now_date.getDate()+" "+now_date.getHours()+":"+now_date.getMinutes()+":"+now_date.getSeconds(),
									},
									purchasedetail:purchase_derail[row.id]
								}
							}
						]
					});
				}
				return datas;
			}
			purchaseManagement.exportToExcel = function(type){
				var url = "<?php echo U('Purchase/PurchaseManagement/exportToExcel');?>";
				var id_list = [];
				for(var i in this.selectRows){id_list.push(this.selectRows[i].id);}
				var forms = $("#<?php echo ($id_list["form"]); ?>").form('get');
				var search = JSON.stringify(forms);
				var form=JSON.stringify(purchaseManagement.params.search.form_data);
				var rows = $("#<?php echo ($datagrid["id"]); ?>").datagrid("getRows");
				if(rows == ''){
					messager.alert('导出不能为空');
				}
				else if(id_list != ''){
					messager.confirm('确定导出选中的采购单？',function(r){
						if(!r){
							return false;
						}
						window.open(url+'?id_list='+id_list+'&search='+search+'&type='+type);
					});

				}else if(form == search){
					var total = $("#<?php echo ($datagrid["id"]); ?>").datagrid("getData").total;
					var num = workTime.getWorkTimeNum(type);
					if(total>num){
						if(type == 'csv'){
							messager.alert('8:00-19:00可以导出10000条，其余时间可以导出20000条!');
						}else {
							messager.alert('8:00-19:00可以导出1000条，其余时间可以导出4000条!');
						}
						return;
					}
					messager.confirm('确定导出所有的采购单？',function(r){
						if(!r){
							return false;
						}
						window.open(url+'?id_list='+id_list+'&search='+search+'&type='+type);
					});
				}else{
					messager.confirm('确定导出搜索的采购单？',function(r){
						if(!r){
							return false;
						}
						window.open(url+'?id_list='+id_list+'&search='+search+'&type='+type);
					});
				}
			};
			element_selectors.purchase_order_print.linkbutton({onClick:function(){
				purchaseManagement.printPurchaseOrderDialog();
			}});
		},0);
	});
	
	</script>
	
<!-- layout-south-tabs, call add_tabs js function to add tabs -->


</body>
</html>