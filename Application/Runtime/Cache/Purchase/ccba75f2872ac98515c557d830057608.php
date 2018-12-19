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

	<div id="<?php echo ($id_list["box_goods_trans"]); ?>"></div>
	<div id="<?php echo ($id_list["dialog"]); ?>"></div>
	<div id="<?php echo ($id_list["trade_edit"]); ?>"></div>

<!-- toolbar -->

<div id="<?php echo ($id_list["toolbar"]); ?>" style="padding:5px;height:auto">
	<form id="<?php echo ($id_list["form"]); ?>" class="easyui-form" method="post">
		<div class="form-div">
			<label style="width: 80px;">分拣框编号：</label><input class="easyui-textbox txt" type="text" name="search[box_no]" style="width: 110px;"/>
			<label style="width: 80px;">　商家编码：</label><input class="easyui-textbox txt" type="text" name="search[spec_no]" style="width: 110px;"/>
			<label style="width: 80px;">　出库单编号：</label><input class="easyui-textbox txt" type="text" name="search[stockout_no]" style="width: 110px;"/>
			<label style="width: 80px;">　订单编号：</label><input class="easyui-textbox txt" type="text" name="search[trade_no]" style="width: 110px;"/>
			<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-search'" onclick="sortingBox.submitSearchForm();">搜索</a>
			<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-redo'" onclick="sortingBox.loadFormData();">重置</a>
		</div>
		<div class="form-div">
			<label  style="width: 80px;">分拣墙编号：</label><select class="easyui-combobox sel" name="search[wall_id]" data-options="panelHeight:200,width:110,editable:false " >
			<option value="all">全部</option><?php if(is_array($list)): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?></select>
			<label style="width: 80px;">　使用状态：</label><input class="easyui-combobox txt" text="txt" name="search[use_status]" data-options="panelHeight:110,width:110,valueField:'id',textField:'name',data:[{'id':'all','name':'全部'},{'id':'2','name':'预占用'},{'id':'1','name':'已占用'},{'id':'0','name':'未占用'}],editable:false,value:'all'">
			<!--<label style="width: 80px;">　分拣墙编号：</label><input class="easyui-textbox txt" type="text" name="search[wall_no]" style="width: 110px;"/>-->
			<label>　分拣墙属性：</label><input class="easyui-combobox txt" text="txt" name="search[wall_type]" data-options="panelHeight:110,width:110,valueField:'id',textField:'name',data:[{'id':'all','name':'全部'},{'id':'1','name':'分拣墙'},{'id':'0','name':'缺货墙'}],editable:false,value:'all'">
		</div>
		<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-match',plain:true" onclick = "sortingBox.goods_transposing()";>货品移位</a>
		<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-edit',plain:true" onclick = "sortingBox.trade_edit()";>订单编辑</a>
		<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-ok',plain:true" 	 onclick="sortingBox.consignStockoutOrder()">确认发货</a>
		<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-print',plain:true" onclick = "sortingBox.print()";>打印缺货明细</a>
		<a href="javascript:void(0)" class="easyui-linkbutton" title='框中未分拣的货品拆分到了订单审核页面重新生成订单，框中分拣过的货品可以直接发货' data-options="iconCls:'icon-split',plain:true" onclick = "sortingBox.split()";>一键拆分未分拣货品</a>
		<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-reload',plain:true" onclick = "sortingBox.boxRelease()";>缺货框释放</a>
	</form>
</div>
	<script>
//# sourceURL=sorting_box_goods.js
	var boxWs;
	$(function(){
		var dynamic_allocation_box = '<?php echo ($dynamic_allocation_box); ?>';
		setTimeout(function(){
			sortingBox = new RichDatagrid(JSON.parse('<?php echo ($params); ?>'));
			sortingBox.setFormData();
			sortingBox.select_box = {};
			sortingBox.goods_transposing = function(){
				var that = this;
				var box_nos = '';
				if(dynamic_allocation_box==1){messager.alert('动态分配分拣框目前不支持货品移位!');return;}
				var dg_id = that.params.datagrid.id;
				var sel_row = $('#'+dg_id).datagrid('getSelections');
				if($.isEmptyObject(sel_row)){messager.alert('请先选择需要移位的分拣框!');return;}
				var wall_type = sel_row[0]['wall_type'];
				var wall_no = sel_row[0]['wall_no'];
				var sort_flag = false;
				sel_row.forEach((value,index)=>{if(value.wall_type!=wall_type){sort_flag = true;}});
				if(sort_flag){messager.alert('不支持分拣墙和缺货墙同时移位!');return;}
				for(var k in sel_row){
					box_nos += sel_row[k]['box_no'] + ',';
				}
				box_nos = box_nos.substr(0,box_nos.length-1);
				var url = that.params.box_goods_trans.url + '?box_nos=' + box_nos + '&wall_no=' + wall_no;
				var button = [ {text:'确定',handler:function(){ that.submitBoxGoodsTrans(); }}, {text:'取消',handler:function(){that.cancelDialog(that.params.box_goods_trans.id)}} ];
				that.showDialog(that.params.box_goods_trans.id,that.params.box_goods_trans.title,url,that.params.box_goods_trans.height,that.params.box_goods_trans.width,button,null,that.params.box_goods_trans.ismax);
			}
			sortingBox.trade_edit = function(){
				var that = this;
				var dg_id = that.params.datagrid.id;
				var sel_row = $('#'+dg_id).datagrid('getSelected');
				if($.isEmptyObject(sel_row)){messager.alert('请先选择需要编辑的分拣框!');return;}
				if(sel_row['trade_no']=='无'||sel_row['trade_no']==''){messager.alert('该分拣框没有订单信息!');return;}
				var url = that.params.trade_edit.url + '&trade_no=' + sel_row['trade_no'];
				var button = [ {text:'确定',handler:function(){ that.submitTradeEditDialog(that.params.trade_edit.id); }}, {text:'取消',handler:function(){that.cancelDialog(that.params.trade_edit.id)}} ];
				that.showDialog(that.params.trade_edit.id,that.params.trade_edit.title,url,that.params.trade_edit.height,that.params.trade_edit.width,button,null,that.params.trade_edit.ismax);
			}
			sortingBox.boxRelease = function(){
				var that = this;
				if(dynamic_allocation_box==1){messager.alert('动态分配分拣框目前不支持释放!');return;}
				var box_ids = '',error_list = {total:0,rows:[]},rows_list = [];
				var dg_id = that.params.datagrid.id;
				var sel_row = $('#'+dg_id).datagrid('getSelections');
				if($.isEmptyObject(sel_row)){messager.alert('请先选择需要释放的分拣框!');return;}
				for(var k in sel_row){
					if(sel_row[k]['wall_type'] != '缺货墙'){
						error_list.total += 1;
						error_list.rows.push({box_no:sel_row[k]['box_no'],trade_no:sel_row[k]['trade_no'],info:'目前只支持释放缺货框！'});
					}else{
						if(sel_row[k]['use_status'] == '未占用'){
							error_list.total += 1;
							error_list.rows.push({box_no:sel_row[k]['box_no'],trade_no:sel_row[k]['trade_no'],info:'该缺货框未被占用，无需释放！'});
						}else{
							box_ids += sel_row[k]['id'] + ',';
							rows_list.push(sel_row[k]);
						}
					}
				}
				box_ids = box_ids.substr(0,box_ids.length-1);
				if (box_ids == '') {$.fn.richDialog("response", error_list, 'box_goods_detail_result');return;}
				messager.confirm('该缺货框内还有订单货品为处理完，确定要释放缺货框吗？',function(r){
					if(r){
						$.post("<?php echo U('Purchase/SortingWall/boxRelease');?>",{box_ids:box_ids},function(ret){
							switch (ret.status){
								case 1:
									$.fn.richDialog("response", error_list, 'box_goods_detail_result');
									break;
								case 0:
									if(error_list.total == 0){
										messager.alert(ret.info);
									}else{
										$.fn.richDialog("response", error_list, 'box_goods_detail_result');
									}
									var index;
									for(var i=0; i<rows_list.length; i++){
										index = $('#' + that.params.datagrid.id).datagrid('getRowIndex', rows_list[i]);
										$('#' + that.params.datagrid.id).datagrid('updateRow', {
											index: index,
											row: {
												use_status: '未占用',
												stockout_no: '无',
												trade_no: '无',
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
			sortingBox.submitBoxGoodsTrans = function () {
				var that = this;
				var row = $('#'+sortingBox.params.box_goods_trans.trans_id).datagrid('getSelected');
				var row_index = $('#'+sortingBox.params.box_goods_trans.trans_id).datagrid('getRowIndex',row);
				$('#'+sortingBox.params.box_goods_trans.trans_id).datagrid('endEdit',row_index);
				var rows = $('#'+sortingBox.params.box_goods_trans.trans_id).datagrid('getRows');
				var is_submit = false;
				for(var i in rows){if(rows[i]['new_box_no']!='') is_submit = true;}
				if(is_submit){
					$.post("<?php echo U('Purchase/SortingWall/submitBoxGoodsTrans');?>", {data:rows}, function(r){
						messager.alert(r.info);
						$('#'+that.params.box_goods_trans.id).dialog('close');
						$('#'+that.params.datagrid.id).datagrid('reload');
					});
				}else{
					messager.alert('请选择要移动的目标分拣框！');
				}
			}
			sortingBox.consignStockoutOrder = function (stock_id,is_force) {
				var ids = '',msg = '确定完成发货吗？';
				var that = this;
				var resultBeforeCheck = [];
				is_force = is_force==undefined?'0':is_force;
				var sel_row = $('#'+that.params.datagrid.id).datagrid('getSelections');
				for(var i in sel_row){
					ids += sel_row[i]['id']+',';
				}
				var ids_len = ids.length;
				ids = ids.substr(0,ids_len-1);
				if(is_force==1){
					ids = stock_id;
					msg = '订单信息可能有误，确定强制出库吗？';
				};
				if(ids==''){messager.alert('没有获取到分拣框货品信息，请刷新后重试');return;}
				messager.confirm(msg, function(r){
					if(r){
						$('#'+that.params.datagrid.id).datagrid('loading');
						Post("<?php echo U('Purchase/SortingWall/consignStockoutOrder');?>", {ids:ids,is_force:is_force}, function(result){
							$('#'+that.params.datagrid.id).datagrid('loaded');
							if(!$.isEmptyObject(resultBeforeCheck) && (result.status == 0 || result.status == 2)){
								result.status = 2;
								result.data.fail = resultBeforeCheck.concat(result.data.fail);
							}
							if(parseInt(result.status)==0) {
								var row_index;
								for(var i in result.data.success){
									if($.isEmptyObject(result.data.success[i])){
										continue;
									}
									row_index = parseInt(i);
								}
								var return_rows= $("#response_dialog_datagrid").datagrid('getRows');
								for (var i=0;i<return_rows.length;i++){
									if(return_rows[i]['stock_id']==result.data.success[row_index].id){
										index=$("#response_dialog_datagrid").datagrid('getRowIndex',return_rows[i]);
										$("#response_dialog_datagrid").datagrid('deleteRow',index);
										i--;
									}
								}
								if(return_rows.length==undefined||return_rows.length==0){
									$("#response_dialog").dialog('close');
								}
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
									for(var k in result.data.fail){
										if(result.data.fail[k].msg == '物流单号不能为空'){
											result.data.fail[k]['solve_way'] = '<a href="javascript:void(0)" onClick="sortingBox.jump_url(\'单据打印\',\'index.php/Stock/StockSalesPrint/getPrintList?stockout_no='+result.data.fail[k]['stock_no']+'\',\''+result.data.fail[k]['stock_no']+'\')">跳转到单据打印界面填写物流单号</a>';
										}else if(result.data.fail[k].msg.search('拦截出库')!=-1){
											result.data.fail[k]['solve_way'] = '<a href="javascript:void(0)" onClick="sortingBox.jump_url(\'单据打印\',\'index.php/Stock/StockSalesPrint/getPrintList?stockout_no='+result.data.fail[k]['stock_no']+'\',\''+result.data.fail[k]['stock_no']+'\')">跳转到单据打印界面进行处理</a>';
											result.data.fail[k]['solve_way'] +=' 或 <a href="javascript:void(0)"  name = "button_save" class="easyui-linkbutton" data-options="iconCls:\'icon-save\'" onclick="sortingBox.consignStockoutOrder('+result.data.fail[k]['stock_id']+','+'1'+')">强制发货</a>';
										}else if(result.data.fail[k].msg == '发货前必须验货' || result.data.fail[k].msg == '发货前必须称重'){
											result.data.fail[k]['solve_way'] ='<a href="javascript:void(0)"  name = "button_save" class="easyui-linkbutton" data-options="iconCls:\'icon-save\'" onclick="sortingBox.consignStockoutOrder('+result.data.fail[k]['stock_id']+','+'1'+')">强制发货</a>';
										}else if(result.data.fail[k].msg == '预物流同步成功之后才可确认发货'){
											result.data.fail[k]['solve_way'] ='<a href="javascript:void(0)"  name = "button_save" class="easyui-linkbutton" data-options="iconCls:\'icon-save\'" onclick="sortingBox.consignStockoutOrder('+result.data.fail[k]['stock_id']+','+'1'+')">强制发货</a>';
										}else if(result.data.fail[k].msg.search(/不允许负库存出库/i) != -1){
											result.data.fail[k]['solve_way'] ='<a href="javascript:void(0)"  name = "button_save" class="easyui-linkbutton" data-options="iconCls:\'icon-save\'" onclick="sortingBox.consignStockoutOrder('+result.data.fail[k]['stock_id']+','+'1'+')">强制发货</a>';
										}else if(result.data.fail[k].msg.search(/存在未分拣的货品/i) != -1){
											result.data.fail[k]['solve_way'] ='<a href="javascript:void(0)"  name = "button_save" class="easyui-linkbutton" data-options="iconCls:\'icon-save\'" onclick="sortingBox.continueSort()">继续分拣</a>' + ' 或 ' + '<a href="javascript:void(0)"  name = "button_save" class="easyui-linkbutton" data-options="iconCls:\'icon-save\'" onclick="sortingBox.consignStockoutOrder('+result.data.fail[k]['stock_id']+','+'1'+')">强制发货</a>' + ' 或 ' + '<a href="javascript:void(0)"  name = "button_save" class="easyui-linkbutton" data-options="iconCls:\'icon-save\'" onclick="sortingBox.consignAllStockoutOrder(1)">全部强制发货</a>';
										}
									}
									$.fn.richDialog("response", result.data.fail, "stockout");
								}
								return true;
							}
							return;
						},'json');
					}else{return;}
				});
			}
			sortingBox.consignAllStockoutOrder = function (is_force) {
				var ids = '';
				var that = this;
				var resultBeforeCheck = [];
				is_force = is_force==undefined?'1':is_force;
				var return_rows= $("#response_dialog_datagrid").datagrid('getRows');
				for(var i in return_rows){
					if(return_rows[i]['msg'].search(/存在未分拣的货品/i) != -1){
						ids += return_rows[i]['stock_id']+',';
					}
				}
				var ids_len = ids.length;
				ids = ids.substr(0,ids_len-1);
				if(ids==''){messager.alert('没有获取到分拣框货品信息，请刷新后重试');return;}
				messager.confirm('订单信息可能有误，确定要全部强制出库吗？', function(r){
					if(r){
						$('#'+that.params.datagrid.id).datagrid('loading');
						Post("<?php echo U('Purchase/SortingWall/consignStockoutOrder');?>", {ids:ids,is_force:is_force}, function(result){
							$('#'+that.params.datagrid.id).datagrid('loaded');
							if(!$.isEmptyObject(resultBeforeCheck) && (result.status == 0 || result.status == 2)){
								result.status = 2;
								result.data.fail = resultBeforeCheck.concat(result.data.fail);
							}
							if(parseInt(result.status)==0) {
								var return_rows= $("#response_dialog_datagrid").datagrid('getRows');
								for (var i=0;i<return_rows.length;i++){
									if(return_rows[i]['msg'].search(/存在未分拣的货品/i) != -1){
										index=$("#response_dialog_datagrid").datagrid('getRowIndex',return_rows[i]);
										$("#response_dialog_datagrid").datagrid('deleteRow',index);
										i--;
									}
								}
								if(return_rows.length==undefined||return_rows.length==0){
									$("#response_dialog").dialog('close');
								}
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
									$.fn.richDialog("response", result.data.fail, "stockout");
								}
								return true;
							}
							return;
						},'json');
					}else{return;}
				});
			}
			sortingBox.jump_url = function(title,url,value){
				var param = {};
				$('#response_dialog').dialog('close');
				param['stockout_no'] = value;
				open_menu(title, url);
				switch(title){
					case '单据打印' :
						if($('#container').tabs('exists',title)){
							$.get("<?php echo U('Stock/StockSalesPrint/search');?>",param,function(res){
								$('#stocksalesprint_datagrid').datagrid('loadData',res);
							});
						}
						break;
				}
			}
			sortingBox.continueSort = function(){
				open_menu('档口采购分拣', '<?php echo U('Stock/StallsPickList/show');?>');
				$("#response_dialog").dialog('close');
			}
			sortingBox.print = function(){
				var that = this;
				var data = $('#'+that.params.datagrid.id).datagrid('getSelections');
				if($.isEmptyObject(data)){
					messager.alert('请选择需要打印的分拣框');
					return;
				}
				that.showDialog(that.params.id_list.dialog,'打印缺货明细',"<?php echo U('Purchase/SortingWall/printGoods');?>",190,350,[{text:"取消",handler:function(){$("#"+
				that.params.id_list.dialog).dialog('close');}}]);
			}
			sortingBox.printCode = function(){
                    var that = this;
                    var rows = $('#'+sortingBox.params.datagrid.id).datagrid('getSelections');
                    if($.isEmptyObject(rows)){
                        messager.alert('请先选择需要打印的行!');
                        return false;
                    }
					
                    var printer = sortingBox.select_box.printer_list.combobox('getValue');
                    var templateId = sortingBox.select_box.template_list.combobox('getValue');
                    if(templateId == ""){
                        messager.alert("预览错误：没有选择模板，请到模板列表页面下载模板");
                        $("#<?php echo ($id_list["dialog"]); ?>").dialog('close');
                        return ;
                    }
                    $('#print_code').linkbutton({text:'打印中...',disabled:true});               
                    var rows_id_list = '';
                    for(var i=0; i<rows.length; i++){
                        rows_id_list += rows[i].box_no + ',';
                    }
                    rows_id_list = rows_id_list.substr(0,rows_id_list.length-1);
                    $.post("<?php echo U('Purchase/StallsOrderManagement/getPrintSortingGoods');?>",{ids:rows_id_list},function(ret){
                        if(ret.status == 1){
							messager.alert(ret.info);
							return;
						}
						var contents = sortingBox.template_contents;
                        var datas = that.getCodeData(contents,templateId,ret.info);
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
                        boxWs.send(JSON.stringify(request));
                    });
				}
				sortingBox.printerSetting = function(){
                    this.connectStockWS();
                    var request = {
                        "cmd":"printerConfig",
                        "requestID":"123458976",
                        "version":"1.0",}
                    boxWs.send(JSON.stringify(request));
                }
				sortingBox.previewPrintcode= function(){
                    var that = this;
                    var templateId = sortingBox.select_box.template_list.combobox('getValue');
                    if(templateId == ""){
                        messager.alert("预览错误：没有选择模板，请到模板列表页面下载模板");
                        $("#<?php echo ($id_list["dialog"]); ?>").dialog('close');
                        return ;
                    }
                    var contents = sortingBox.template_contents;
                    var rows = $('#'+sortingBox.params.datagrid.id).datagrid('getSelections');
                    var rows_id_list = '';
                    for(var i=0; i<rows.length; i++){
                       rows_id_list += rows[i].box_no + ',';
                    }
                    rows_id_list = rows_id_list.substr(0,rows_id_list.length-1);
                    $.post("<?php echo U('Purchase/StallsOrderManagement/getPrintSortingGoods');?>",{ids:rows_id_list},function(ret){
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
                        boxWs.send(JSON.stringify(request));
                    });
                }
				 sortingBox.getCodeData = function(contents,templateId,lost_data){
                    contents = JSON.parse(contents[templateId]);
                    var templateURL = contents.custom_area_url;
                    var rows = $('#'+sortingBox.params.datagrid.id).datagrid('getSelections');
                    if($.isEmptyObject(rows)){
                        messager.alert('请先选择需要打印的行!');
                        return false;
                    }
                    var datas = [],row;
                    var now_date = new Date();
                    var now_millisecond = now_date.getTime();
                    var ID = 0;  
					if(lost_data == null){
						lost_data = [];                        
					}                  
						datas.push({
							'documentID' : now_millisecond.toString().concat(ID.toString()),
							'contents' : [
								{
									'templateURL' : templateURL,
									'data' : lost_data
								}
							]
						});
					
				
                    return datas;
                }
				
				sortingBox.onPrinterSelect = function(printer_name){
                    var templateId = sortingBox.select_box.template_list.combobox('getValue');
                    var contents = sortingBox.template_contents;
                    var content = contents[templateId];
                    if(content.defaultPrinter != undefined && content.default_printer == printer_name)
                        return;
                    else
                        messager.confirm("您确定把\""+printer_name+"\"设置为此模板的打印机么？",function(r){
                            if(r){
                                sortingBox.setDefaultPrinter(content,printer_name,templateId);
                            }
                        });
                }
                sortingBox.setDefaultPrinter=function(content,printor,templateId){
                    content = JSON.parse(content);
                    content.default_printer = printor;
                    $.post("<?php echo U('Purchase/StallsOrderManagement/setDefaultPrinter');?>",{content:JSON.stringify(content),templateId:templateId},function(ret){
                        if(1 == ret.status){
                            messager.alert(ret.msg);
                        }else {
                            sortingBox.template_contents[templateId] = JSON.stringify(content);
                        }
                    });
                }
				sortingBox.templateOnSelect=function () {
                    if(sortingBox.select_box.template_list.combobox('getData').length == 0)
                    {
                        return;
                    }
                    var print_list = sortingBox.select_box.printer_list.combobox('getData');
                    var content = JSON.parse(sortingBox.template_contents[sortingBox.select_box.template_list.combobox('getValue')]);
                    if(undefined != content.default_printer && JSON.stringify(print_list).indexOf(content.default_printer) != -1){
                        sortingBox.select_box.printer_list.combobox('setValue',content.default_printer);
                    }
                }
				sortingBox.newSelectPrinter = function(){
                    var request = {
                        "cmd":"getPrinters",
                        "requestID":"123458976"+"99",
                        "version":"1.0",
                    }
                    boxWs.send(JSON.stringify(request));
                }
			sortingBox.connectStockWS = function(){
				if(boxWs == undefined){
					boxWs = new WebSocket("ws://127.0.0.1:13528");
					boxWs.onmessage = function(event){sortingBox.onStockMessage(event);};
					boxWs.onerror = function(){sortingBox.onStockError();};
				}
				return ;
			}
			sortingBox.onStockMessage = function(event){
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
                                    sortingBox.select_box.printer_list.combobox({
                                        valueField: 'name',
                                        textField: 'name',
                                        data: response_result.printers,
                                        value: response_result.defaultPrinter
                                    });
                                    sortingBox.select_box.printer_list.combobox('reload');
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
										//messager.alert("打印唯一码完成");
										sortingBox.refresh();
										$('#print_code').linkbutton({text:'打印',disabled:false});
		  
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
				sortingBox.split = function(){
					var that = this;
					var resultBeforeCheck = [];
					var selects_info = {};
					if(sortingBox.selectRows==undefined) {messager.alert('请选择拆分的订单!'); return false;}
					var selected_rows=sortingBox.selectRows;
					var ids = '';
					for(var item in selected_rows){
						var temp_index = $('#'+that.params.datagrid.id).datagrid('getRowIndex',selected_rows[item]);
						selects_info[temp_index] = selected_rows[item].id;
					}
					messager.confirm('确定拆分订单吗？', function(r){
						if(r){
							$.post("<?php echo U('SortingWall/oneSplit');?>", {ids:JSON.stringify(selects_info)}, function(result){
								if(parseInt(result.status) == 0){
									messager.confirm('拆分完成，是否发货',function(res){
										if(res){
											$.post("<?php echo U('Stock/StockSalesPrint/consignStockoutOrder');?>", {ids:JSON.stringify(result.stock_id),is_force:0}, function(info){
												if(parseInt(info.status) == 1){
													messager.alert(info.info);
													that.refresh();
													return false;
												}
												if(parseInt(info.status) == 2){
													if(!$.isEmptyObject(info.data.fail)){
														//调用dialog显示处理结果
														$.fn.richDialog("response", info.data.fail, "stockout",{close:function(){if(sortingBox){sortingBox.refresh();}}});
													}
													return true;
												}
												that.refresh();
												return true;
											});
			
										}else{r
											that.refresh();
											return;
										}
									});
								}
								if(parseInt(result.status) == 1){
									messager.alert(result.info);
									that.refresh();
									return false;
								}
								if(parseInt(result.status) == 2){
									if(!$.isEmptyObject(result.data.fail)){
										//调用dialog显示处理结果
										$.fn.richDialog("response", result.data.fail, "box",{close:function(){if(sortingBox){sortingBox.refresh();}}});
									}
									return true;
								}
								return;
							},'json');
						}else{return;}
					});
				}
				sortingBox.onStockError = function(){
                    boxWs = null;
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
				sortingBox.changeTemplatePage=function(){
                    open_menu('打印模板','<?php echo U("Setting/NewPrintTemplate/getNewPrintTemplate");?>');
                    $('#<?php echo ($id_list["dialog"]); ?>').dialog('close');
                }
				sortingBox.setColor = function(value){
					if(value == '未占用'){
						return 'background-color:#55B527;color:#000;';
					}else if(value == '已占用'){
						return 'background-color:#FF9900;color:#000;';
					}else if(value == '预占用'){
						return 'background-color:#1E90FF;color:#000;';
					}
				}
				$('#'+sortingBox.params.datagrid.id).datagrid({
					onDblClickRow : function () {
						sortingBox.trade_edit();
					}
				});
				$('#'+sortingBox.params.datagrid.id).datagrid('getColumnOption','use_status').styler = function(value){
					return sortingBox.setColor(value);
				};
		},0);
	});
	</script>
	
<!-- layout-south-tabs, call add_tabs js function to add tabs -->


</body>
</html>