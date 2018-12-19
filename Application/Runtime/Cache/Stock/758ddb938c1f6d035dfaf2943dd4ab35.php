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
	<div id="<?php echo ($id_list["position_import_dialog"]); ?>" class="easyui-panel" style="padding:25px 50px 25px 50px">
		<form id="<?php echo ($id_list["position_import_form"]); ?>" method="post" enctype="multipart/form-data">
			<div style="margin-bottom:25px">
				<input class="easyui-filebox" name="file" data-options="prompt:'请选择文件...','buttonText':'请选择文件'" style="width:100%;">
			</div>
			<div align="center">
				<a href="javascript:void(0)" class="easyui-linkbutton" style="width:50%" onclick="stockTransManagement.upload('position_import')">上传</a>
			</div>
		</form>
	</div>
	<div id="<?php echo ($id_list["spec_import_dialog"]); ?>" class="easyui-panel" style="padding:25px 50px 25px 50px">
		<form id="<?php echo ($id_list["spec_import_form"]); ?>" method="post" enctype="multipart/form-data">
			<div style="margin-bottom:25px">
				<input class="easyui-filebox" name="file" data-options="prompt:'请选择文件...','buttonText':'请选择文件'" style="width:100%;">
			</div>
			<div align="center">
				<a href="javascript:void(0)" class="easyui-linkbutton" style="width:50%" onclick="stockTransManagement.upload('spec_import')">上传</a>
			</div>
		</form>
	</div>


<!-- toolbar -->

 <div id="<?php echo ($id_list["toolbar"]); ?>" style="padding:5px;height:auto">
        <form id="<?php echo ($id_list["form"]); ?>" class="easyui-form" method="post">
            <div class="form-div">
                <label style="display: inline-block;">调拨方案：</label><input class="easyui-combobox txt" name="search[mode]" data-options="valueField:'id',textField:'name',data:formatter.get_data('stocktrans_mode')"/>
                <label style="display: inline-block;">　　原仓库：</label><select class="easyui-combobox sel" name="search[from_warehouse_id]" data-options="panelHeight:'100px',editable:false " >
						<?php if(is_array($warehouse_array)): $i = 0; $__LIST__ = $warehouse_array;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?></select>
				<label style="display: inline-block;">　目标仓库：</label><select class="easyui-combobox sel" name="search[to_warehouse_id]" data-options="panelHeight:'100px',editable:false " >
						<?php if(is_array($warehouse_array)): $i = 0; $__LIST__ = $warehouse_array;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?></select> 
				<label style="display: inline-block;">　　　经办人：</label><select class="easyui-combobox sel" name="search[creator_id]" data-options="panelHeight:'100px',editable:false " >
						<?php if(is_array($employee_array)): $i = 0; $__LIST__ = $employee_array;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?></select> 
				<label style="display: inline-block;">　调拨单号：</label><input class="easyui-textbox txt" type="text" name="search[transfer_no]" />
				<a href="javascript:void(0)" onclick="stockTransManagement.clickMore(this);">更多</a>
			    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-search'" onclick="stockTransManagement.submitSearchForm();">搜索</a>
			    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-redo'" onclick="stockTransManagement.loadFormData();">重置</a>
            </div>
			<div id="<?php echo ($id_list["more_content"]); ?>">
				<div class="form-div">
					<label style="display: inline-block;">商家编码：</label><input  class="easyui-textbox txt" type="text"  name="search[spec_no]"/>
					<label style="display: inline-block;">　货品编号：</label><input  class="easyui-textbox txt" type="text"  name="search[goods_no]"/>
					<label style="display: inline-block;">　货品名称：</label><input  class="easyui-textbox txt" type="text"  name="search[goods_name]"/>
					<label style="display: inline-block;">　　货品简称：</label><input  class="easyui-textbox txt" type="text"  name="search[short_name]"/>
				</div>
				<div class="form-div">
					<label style="display: inline-block;">品　　牌：</label><select class="easyui-combobox sel" name="search[brand_id]" data-options="panelHeight:'100px',editable:false " >
						<?php if(is_array($brand_array)): $i = 0; $__LIST__ = $brand_array;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?></select> <label style="display: inline-block;">　　联系人：</label><input  class="easyui-textbox txt" type="text"  name="search[contact]"/>
					<label style="display: inline-block;">　联系电话：</label><input  class="easyui-textbox txt" type="text"  name="search[telno]"/>
					<label style="display: inline-block;">　调拨单状态：</label><input class="easyui-combobox txt" name="search[status]" data-options="valueField:'id',textField:'name',data:formatter.get_data('stocktrans_status')"/>
				</div>
            </div>
        </form>
        <input type="hidden" id="<?php echo ($id_list["hidden_flag"]); ?>" value="1">
        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-edit',plain:true" onclick = "stockTransManagement.edit()">编辑</a>
       <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-submit',plain:true" onclick = "stockTransManagement.submitStockTransOrder()">提交</a>
	   <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-redo',plain:true" onclick = "stockTransManagement.cancelTransOrder()">取消</a>
		<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-save',plain:true" onclick = "stockTransManagement.send()";>推送</a>
		<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-remove',plain:true" onclick = "stockTransManagement.cancel_st()">取消委外单</a>
	 	<a href="javascript:void(0)" class="easyui-menubutton" data-options="iconCls:'icon-excel',plain:true,menu:'#stock_spec_transfer_download'" onclick="stockTransManagement.uploadDialog('spec_import')">单品调拨导入</a>
	 	<a href="javascript:void(0)" class="easyui-menubutton" data-options="iconCls:'icon-excel',plain:true,menu:'#stock_position_transfer_download'" onclick="stockTransManagement.uploadDialog('position_import')">货位调拨导入</a>
	 	<a href="javascript:void(0)" class="easyui-menubutton" data-options="iconCls:'icon-excel',plain:true,menu:'#stock_trans_export'" >导出功能</a>
		 <div id="stock_trans_export">
			 <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-excel',plain:true" name="exportToExcel" onclick="stockTransManagement.exportToExcel('csv')">导出csv(推荐)</a>
			 <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-excel',plain:true" name="exportToExcel" onclick="stockTransManagement.exportToExcel('excel')">导出到Excel</a>
		 </div>
	 	<div id="stock_position_transfer_download" style="width: 100px;">
			<div data-options="iconCls:'icon-down_tmp'" onclick="stockTransManagement.downloadTemplet('position_transfer')">下载模板</div>
		</div>
	 	<div id="stock_spec_transfer_download">
			<div data-options="iconCls:'icon-down_tmp'" onclick="stockTransManagement.downloadTemplet('spec_transfer')">下载模板</div>
		</div>
 </div>
    <script>
        //# sourceURL=stocktrans_management.js
	   $(function(){
			setTimeout(function(){
				stockTransManagement = new RichDatagrid(JSON.parse('<?php echo ($params); ?>'));
				stockTransManagement.setFormData();
				stockTransManagement.cancelTransOrder=function(){
					var that=this;
					var rows=$('#'+this.params.datagrid.id).datagrid('getSelections');
					if(rows[0]==null){messager.alert('请选择要操作的行!');return;}

					var id_list = '';
					var error_list = {total:0,rows:[]};
					var rows_list = [];
					for(var i=0; i<rows.length;i++){
						if(rows[i].status == 20){
							id_list += rows[i].id + ',';
							rows_list.push(rows[i]);
						}else{
							error_list.total += 1;
							error_list.rows.push({"transfer_no":rows[i].transfer_no,"info":'调拨单类型不正确，只能取消编辑中的调拨单！'});
						}
					}
					id_list = id_list.substr(0,id_list.length-1);
					if (id_list == '') {$.fn.richDialog("response", error_list, 'transfer_management_result');return;}
					messager.confirm('确定取消调拨单吗？',function(r){
						if(r){
							$.post("<?php echo U('Stock/StockTransManagement/cancelTransOrder');?>",{'ids':id_list},function(r){
								switch (r.status){
									 case 1:
										 //messager.alert(r.info);
										 $.fn.richDialog("response", error_list, 'transfer_management_result');
										 break;
									case 0:
										if(error_list.total == 0){
											messager.alert(r.info);
										}else{
											$.fn.richDialog("response", error_list, 'transfer_management_result');
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
				stockTransManagement.submitStockTransOrder=function(is_force,force_id){
					var that=this;
					var rows=$('#'+that.params.datagrid.id).datagrid('getSelections');
					if(rows[0]==null){messager.alert('请选择要操作的行!');return;}
					var id_list = '';
					var transfer_no_list = '';
					var error_list = {total:0,rows:[]};
					var rows_list = [];
					var force_list;
					for(var i=0; i<rows.length;i++){
						if(rows[i].status == 20){
							id_list += rows[i].id + ',';
							transfer_no_list += rows[i].transfer_no + ',';
							rows_list.push(rows[i]);
							if(rows[i].id == force_id){force_list = rows[i];}
						}else{
							error_list.total += 1;
							error_list.rows.push({"transfer_no":rows[i].transfer_no,"info":'调拨单类型不正确，只能提交编辑中的调拨单！'});
						}
					}
					id_list = id_list.substr(0,id_list.length-1);
					transfer_no_list = transfer_no_list.substr(0,transfer_no_list.length-1);
					if (id_list == '') {$.fn.richDialog("response", error_list, 'transfer_management_result');return;}

					var data = {'ids':id_list,transfer_nos:transfer_no_list};
					var post_data = {'ids':force_id,is_force:!is_force?0:1,transfer_nos:transfer_no_list};
					if(!!is_force){
						$('#'+that.params.datagrid.id).datagrid('loading');
						$.post("<?php echo U('Stock/StockTransManagement/submitStockTransOrder');?>",post_data,function(r){
							$('#'+that.params.datagrid.id).datagrid('loaded');
							switch(r[0].status){
								case 1:
								{
//									if(r.data == false){
//										messager.confirm(r.info,function(res){
//											if(res){
//												that.submitStockTransOrder(1);
//											}else{
//												return;
//											}
//										});
//										return;
//									}
									messager.alert(r[0].info);
									break;
								}
								case 0:
									var index = $('#'+that.params.datagrid.id).datagrid('getRowIndex', force_list);
									var goods_in_count=r[0].goods_in_count;
									var goods_out_count=r[0].goods_out_count;
									$("#"+that.params.datagrid.id).datagrid('updateRow',{index:index,row:{status:'90',goods_in_count:goods_in_count,goods_out_count:goods_out_count}});
									var force_success_row = $("#response_dialog_datagrid").datagrid('getSelected');
									var force_seccess_index = $("#response_dialog_datagrid").datagrid('getRowIndex',force_success_row);
									$("#response_dialog_datagrid").datagrid('updateRow',{index:force_seccess_index,row:{info:'强制调拨成功！'}});
									that.refresh();
									break;
								default :
									messager.alert("系统发生错误，请与管理员联系！");
							}
						});
					}else{
						messager.confirm('确定提交调拨单吗？',function(r){
							if(r){
								$('#'+that.params.datagrid.id).datagrid('loading');
								$.post("<?php echo U('Stock/StockTransManagement/submitStockTransOrder');?>",data,function(r){
									$('#'+that.params.datagrid.id).datagrid('loaded');
									switch(r[0].status){
										case 1:
										{
//											if(r.data == false){
//												messager.confirm(r.info,function(res){
//													if(res){
//														that.submitStockTransOrder(1);
//													}else{
//														return;
//													}
//												});
//												return;
//											}
											var r_length = r.length;
											for(var i=0; i< r_length;++i){
												error_list.rows.push({"transfer_no":r[i]['transfer_no'],"info":r[i]['info']});
											}
											error_list.total += r_length;
											$.fn.richDialog("response", error_list, 'transfer_management_result');
											//messager.alert(r.info);
											break;
										}
										case 0:
//											var index = $('#'+that.params.datagrid.id).datagrid('getRowIndex', data);
//											var goods_in_count=r.goods_in_count;
//											var goods_out_count=r.goods_out_count;
//											$("#"+that.params.datagrid.id).datagrid('updateRow',{index:index,row:{status:'90',goods_in_count:goods_in_count,goods_out_count:goods_out_count}});
											if(error_list.total == 0){
												messager.alert('提交成功');
											}else{
												$.fn.richDialog("response", error_list, 'transfer_management_result');
											}
											var index;
											for(var i=0; i<rows_list.length; i++){
												index = $('#' + that.params.datagrid.id).datagrid('getRowIndex', rows_list[i]);
												$('#' + that.params.datagrid.id).datagrid('updateRow', {
													index: index,
													row: {
														status: '90',
														goods_in_count:r[i]['goods_in_count'],
														goods_out_count:r[i]['goods_out_count']
													}
												});
											}
											break;
										default :
											messager.alert("系统发生错误，请与管理员联系！");

									}
								});
							}
						});
					}

				};
			
				stockTransManagement.edit = function(){
					var row = $('#'+this.params.datagrid.id).datagrid('getSelected');
					var rows = $('#'+this.params.datagrid.id).datagrid('getSelections');
					if(row == null){ messager.alert("请选择操作的行!"); return; }
					if (rows.length > 1) {messager.alert("请选择单行编辑!");return false;}
					if(row.status != 20 && row.status != 42){ messager.alert("调拨单状态不正确！"); return; }
					stockTransManagement.showDialog(this.params.edit.id,this.params.edit.title,this.params.edit.url+"?id="+row.id+'&management_info='+'{"dialog_id":"'+this.params.edit.id+'","datagrid_id":"'+stockTransManagement.params.datagrid.id+'"}',550,1200,[])
				};
				
				
				stockTransManagement.send = function(){
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
					if(data[item]['status']!=42 && data[item]['status']!=44 && data[item]['status']!=50 && data[item]['status']!=62 && data[item]['status']!=64){
						temp_result['message'] = "单子状态不正确";
						resultBeforeCheck.push(temp_result);
						continue;
					}
					var temp_index = $('#'+this.params.datagrid.id).datagrid('getRowIndex',data[item]);
					selects_info[temp_index] = data[item].id;
				}
				if($.isEmptyObject(selects_info)){
					$.fn.richDialog("response", resultBeforeCheck, "importResponse_suite",{close:function(){if(stockTransManagement){stockTransManagement.refresh();}}});
					return;
				}
                messager.confirm('确定推送调拨单？',function(r){
                	if(r){
	                	$.post("<?php echo U('Stock/StockTransManagement/send');?>",{ids:JSON.stringify(selects_info)},function(r){
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
							stockTransManagement.refresh();
	                	});
	                }
                });
			}
			
			stockTransManagement.cancel_st = function(){
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
					if(data[item]['status']!=46 && data[item]['status']!=66 && data[item]['status']!=42 && data[item]['status']!=62){
						temp_result['message'] = "不是已推送的订单";
						resultBeforeCheck.push(temp_result);
						continue;
					}
					var temp_index = $('#'+this.params.datagrid.id).datagrid('getRowIndex',data[item]);
					selects_info[temp_index] = data[item].id;
				}
				if($.isEmptyObject(selects_info)){
					$.fn.richDialog("response", resultBeforeCheck, "importResponse_suite",{close:function(){if(stockTransManagement){stockTransManagement.refresh();}}});
					return;
				}
                messager.confirm('确定取消调拨单吗？',function(r){
                	if(r){
	                	$.post("<?php echo U('Stock/StockTransManagement/cancel_st');?>",{ids:JSON.stringify(selects_info)},function(r){
		                    /*switch (r.status){
		                        case 0:
		                            messager.alert(r.info);
		                            break;
		                        case 1:
		                            messager.alert(r.info);
		                            break;
		                        default :
		                            messager.alert("系统错误，请与管理员联系！");
		                    }*/
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
						stockTransManagement.refresh();
	                }
                });
			}
				stockTransManagement.exportToExcel = function(type){
				var url = "<?php echo U('Stock/StockTransManagement/exportToExcel');?>";
				var id_list = [];
				for(var i in this.selectRows){id_list.push(this.selectRows[i].id);}
				var forms = $('#<?php echo ($id_list["form"]); ?>').form('get');
				var search = JSON.stringify(forms);
				var form=JSON.stringify(stockTransManagement.params.search.form_data);
				var rows = $("#<?php echo ($datagrid["id"]); ?>").datagrid("getRows");
				if(rows == ''){
					messager.alert('导出不能为空');
				}
				else if(id_list != ''){
					messager.confirm('确定导出选中的调拨单？',function(r){
						if(!r){
							return false;
						}
						window.open(url+'?id_list='+id_list+'&search='+search+'&type='+type);
					});

				}else if(form == search){
                    var total = $("#<?php echo ($datagrid["id"]); ?>").datagrid("getData").total;
                    var num = workTime.getWorkTimeNum();
                    if(total>num){
                        messager.alert('8:00-19:00可以导出1000条，其余时间可以导出4000条!');
                        return;
                    }
					messager.confirm('确定导出所有的调拨单？',function(r){
						if(!r){
							return false;
						}
						window.open(url+'?id_list='+id_list+'&search='+search+'&type='+type);
					});
				}else{
					messager.confirm('确定导出搜索的调拨单？',function(r){
						if(!r){
							return false;
						}
						window.open(url+'?id_list='+id_list+'&search='+search+'&type='+type);
					});
				}
			};
			stockTransManagement.uploadDialog = function(type){
				var dialog,title;
				if(type == 'spec_import'){
					dialog = $("#<?php echo ($id_list["spec_import_dialog"]); ?>");
					title = '单品调拨导入';
				}else if(type == 'position_import'){
					dialog = $("#<?php echo ($id_list["position_import_dialog"]); ?>");
					title = '货位调拨导入';
				}
				dialog.dialog({
					title:title,
					width:'350px',
					height:'160px',
					modal:true,
					closed:false,
					inline:true,
					iconCls: 'icon-save',
				});
			};
			stockTransManagement.upload = function(type){
				var dg = $("#<?php echo ($id_list["datagrid"]); ?>");
				var form,dialog,url;
				if(type == 'position_import'){
					form = $("#<?php echo ($id_list["position_import_form"]); ?>");
					dialog = $("#<?php echo ($id_list["position_import_dialog"]); ?>");
					url = "<?php echo U('Stock/StockTransManagement/position_import_upload');?>";
				}else if(type == 'spec_import'){
					form = $("#<?php echo ($id_list["spec_import_form"]); ?>");
					dialog = $("#<?php echo ($id_list["spec_import_dialog"]); ?>");
					url = "<?php echo U('Stock/StockTransManagement/spec_import_upload');?>";
				}
				$.messager.progress({
					title:"请稍等",
					msg:"该操作可能需要几分钟，请稍候",
					text :'',
					interval:100

				});
				form.form("submit",{
					url:url,
					success:function(res){
						$.messager.progress("close");
						res =JSON.parse(res);

						if(res.status == 1){
							messager.alert(res.msg);
						}else if(res.status == 0){
							dg.datagrid("reload");
							dialog.dialog("close");
						}else{
							$.fn.richDialog("response", res.data, "importResponse");
							dg.datagrid("reload");
						}
						form.form("load", {"file": ""});
					},
				});
			};
			stockTransManagement.downloadTemplet = function(type){
				var url= "<?php echo U('Stock/StockTransManagement/downloadTemplet');?>?type="+type;
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
				},0);
			});
	</script>

<!-- layout-south-tabs, call add_tabs js function to add tabs -->


</body>
</html>