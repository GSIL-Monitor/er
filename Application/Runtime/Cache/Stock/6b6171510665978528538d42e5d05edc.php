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

<script>
</script>

</div>
<!-- dialog -->

<!-- toolbar -->

	<div id="<?php echo ($id_list["toolbar"]); ?>">
		<form id = <?php echo ($id_list["form"]); ?>>
			<a href="javascript:void(0)" class="easyui-linkbutton" data-options="size:'small',iconCls:'icon-add'" onclick="sspMultiLogistics.addE()">添加</a>
			<a href="javascript:void(0)" class="easyui-linkbutton" data-options="size:'small',iconCls:'icon-edit'" onclick="sspMultiLogistics.edit()">编辑</a>
			<a href="javascript:void(0)" class="easyui-linkbutton" data-options="size:'small',iconCls:'icon-remove'" onclick="sspMultiLogistics.delete()">删除</a>
			<a href="javascript:void(0)" class="easyui-linkbutton" data-options="size:'small',iconCls:'icon-print'" onclick="newPrintLogisticsDialog(1)">打印</a>
		</form>
	</div>
	<script type="text/javascript">
//# sourceURL=add_ml_l.js
		$(function(){
			setTimeout(function(){
				sspMultiLogistics = new RichDatagrid(JSON.parse('<?php echo ($params); ?>'));
				$('#' + sspMultiLogistics.params.datagrid.id).datagrid('getColumnOption','print_status').styler = function(value,row,index){
					return sspMultiLogistics.setColor(value);
				};
				sspMultiLogistics.setColor = function(value){
					if(value == 2){
						return 'background-color:#E37162;color:#000;';
					}else if(value == 1){
						return 'background-color:#55B527;color:#000;';
					}else if(value == 3){
						return 'background-color:#f00;color:#000;';
					}
				}
				sspMultiLogistics.addE = function(){
					var rows = $('#'+stockSalesPrint.params.datagrid.id).datagrid('getSelections');
					if ($.isEmptyObject(rows)){
						messager.alert('请选择订单');
						return;
					}
                    else if(rows.length>1){
                        messager.alert('只能选择一个订单');
                        return;
                    }
					else if(rows[0].logistics_no.length == 0){
						messager.alert('请先填写物流单号');
						return;
					}
					var stockout_id = rows[0].id;
                    var logistics_id = rows[0].logistics_id;
                    var bill_type = $('#'+stockSalesPrint.params.datagrid.id).datagrid('getSelections')[0].bill_type;
                    this.params.add.height = 260;
					this.params.add.width = 360;
					if(this.params.add.url.indexOf('?')!=-1)
					 {
					 	var url = this.params.add.url;
					 	url = url.substr(0,url.indexOf('?'));
					 	this.params.add.url = url;
					 }
                    this.params.add.url +='?stockout_id='+stockout_id+"&bill_type="+bill_type+'&logistics_id='+logistics_id;
                    connectStockWS();
                    this.add();
				};
				sspMultiLogistics.edit = function(){
					var rows = $('#'+stockSalesPrint.params.datagrid.id).datagrid('getSelections');
					var that = this;
					if ($.isEmptyObject(rows)){
						messager.alert('请选择订单');
						return;
					}

					var row = $('#'+this.params.datagrid.id).datagrid('getSelections');
					if ($.isEmptyObject(row)){
						messager.alert('请选择物流单号');
						return;
					}
                    if (row.length>1){
                        messager.alert('不能同时编辑多个订单');
                        return;
                    }
                    // 判断物流单类型
                    $.post("/index.php/Stock/StockSalesPrint/getBillType", {logistics_id:row[0].logistics_id}, function(r){
                        if(r.status ===1){
                            if(r.msg != 0){
                                messager.alert('目前只支持编辑普通物流单');
                                return;
							}else{
                                if(this.params.edit.url.indexOf('?')!=-1)
                                {
                                    var url = this.params.edit.url;
                                    this.params.edit.url = url.substr(0,url.indexOf("?"));
                                }
								this.params.edit.url += "?src_order_no="+rows[0].src_order_no+'&logistics_id='+$('#'+this.params.datagrid.id).datagrid('getSelected').logistics_id+'&logistics_no='+$('#'+this.params.datagrid.id).datagrid('getSelected').logistics_no+'&id='+$('#'+this.params.datagrid.id).datagrid('getSelected').id+'&weight='+$('#'+this.params.datagrid.id).datagrid('getSelected').weight+'&stockout_id='+$('#'+this.params.datagrid.id).datagrid('getSelected').stockout_id;
                                this.params.edit.width = 320;
                                this.params.edit.height = 220;
                                var buttons=[ {text:'确定',handler:function(){ that.submitEditDialog(); }}, {text:'取消',handler:function(){that.cancelDialog(that.params.edit.id)}} ];
                                this.showDialog(this.params.edit.id,this.params.edit.title,this.params.edit.url,this.params.edit.height,this.params.edit.width,buttons,this.params.edit.toolbar,this.params.edit.ismax);
                            }
                        }else{
                            messager.alert('获取物流单类型失败，请重试');
                            return;
						}
                    }.bind(this));
				};
				sspMultiLogistics.delete = function(){
					var rows = $('#'+stockSalesPrint.params.datagrid.id).datagrid('getSelections');
					if ($.isEmptyObject(rows)){
						messager.alert('请选择订单');
						return;
					}
					var row = $('#'+this.params.datagrid.id).datagrid('getSelections');
					if ($.isEmptyObject(row)){
						messager.alert('请选择物流单号');
						return;
					}
					var trade_id = $('#stocksalesprint_datagrid').datagrid('getSelections')[0].src_order_id;
					var ids = [];
					var logistics_no = [];
					var logistics_id = [];
					for(var k in row){
						if(row[k].bill_type == 2){
							messager.alert('菜鸟电子面单不能删除');
							return ;
						}
						ids[k] = row[k].id;
						logistics_no[k]= row[k].logistics_no;	
						logistics_id[k]= row[k].logistics_id;
					}

                    messager.confirm("您确定要删除该多物流单吗?",function(r){
                        if(r){
                            $.post("<?php echo U('Stock/StockSalesPrint/deleteMultiLogisticsNo');?>",{logistics_id:logistics_id,ids:ids,logistics_no:logistics_no,trade_id:trade_id},function(r){
                                if(r.status == 0){
                                    sspMultiLogistics.refresh($('#'+sspMultiLogistics.params.datagrid.id));
                                }else{
                                    messager.alert(r.info);
                                }
                            },'json');
                        }
                    });
				};
				sspMultiLogistics.writeWeight = function(){
					var pagenumber = $('#CountInput').val();
					var that = this;
					if(pagenumber == '' || pagenumber <1){
						messager.alert('请先填写包裹数或包裹数要大于0');
						return;
					}
					
					pagenumber = parseInt(pagenumber);
	
					if(isNaN(pagenumber)){
						messager.alert('请填写数字');
						return ;
					}
					var buttons=[ {text:'确定',handler:function(){ that.submitWeight(); }}, {text:'取消',handler:function(){that.cancelDialog(that.params.writeWeight.id)}} ];
					Dialog.show(this.params.writeWeight.id,this.params.writeWeight.title,this.params.writeWeight.url+"?pagenumber="+pagenumber,'auto',this.params.writeWeight.width,buttons,null,false);
				};
				sspMultiLogistics.submitWeight = function(){
					var weights = '';
					$('input[name="weights"]').each(function(){
						var weight_val = $(this).val();
						var k = 1;
						weight_val = parseInt(weight_val);
						if(isNaN(weight_val)){
							messager.alert("第"+k+"个包裹重量要填写数字");
							return;
						}
						if(weight_val < 0){
							messager.alert("第"+k+"个包裹重量要大于0");
							return;
						}
						weights += weight_val+',';
					});
					weights = weights.substr(0,weights.length-1);
					sspMultiLogistics.weights = weights;
					$('#'+sspMultiLogistics.params.writeWeight.id).dialog('close');
				};
				
			},0);
		});
	</script>

<!-- layout-south-tabs, call add_tabs js function to add tabs -->


</body>
</html>