<?php if (!defined('THINK_PATH')) exit();?>
<table id="<?php echo ($datagrid["id"]); ?>" class="easyui-datagrid" data-options='<?php $dataOptions = array_merge(array ( 'border' => false, 'fit' => true, 'fitColumns' => true, 'rownumbers' => true, 'singleSelect' => true, 'pagination' => true, 'pageList' => array ( 0 => 20, 1 => 50, 2 => 100, ), 'pageSize' => 20, ), $datagrid["options"]);if(isset($dataOptions['toolbar']) && substr($dataOptions['toolbar'],0,1) != '#'): unset($dataOptions['toolbar']); endif;if(isset($dataOptions['methods'])): unset($dataOptions['methods']);endif; echo trim(json_encode($dataOptions), '{}[]').((isset($datagrid["options"]['toolbar']) && substr($datagrid["options"]['toolbar'],0,1) != '#')?',"toolbar":'.$datagrid["options"]['toolbar']:null).(isset($datagrid["options"]['methods'])? ','.$datagrid["options"]['methods']:null); ?>' style="<?php echo ($datagrid["style"]); ?>" ><thead><tr><?php if(is_array($datagrid["fields"])):foreach ($datagrid["fields"] as $key=>$arr):if(isset($arr['formatter'])):unset($arr['formatter']);endif;if(isset($arr['methods'])):unset($arr['methods']);endif;if(isset($arr['editor'])):unset($arr['editor']);endif;echo "<th data-options='".trim(json_encode($arr), '{}[]').(isset($datagrid["fields"][$key]['formatter'])?",\"formatter\":".$datagrid["fields"][$key]['formatter']:null).(isset($datagrid["fields"][$key]['editor'])?",\"editor\":".$datagrid["fields"][$key]['editor']:null).(isset($datagrid["fields"][$key]['methods'])?",".$datagrid["fields"][$key]['methods']:null)."'>".$key."</th>";endforeach;endif; ?></tr></thead></table>
<div id="<?php echo ($id_list["toolbar"]); ?>">
<form  id="<?php echo ($id_list["form_id"]); ?>">
<div class="form-div">
<label>　订单号：</label><input class="easyui-textbox txt" type="text" name="trade_no" value="<?php echo ($trade["trade_no"]); ?>" data-options="disabled:true"/>
<label>　原始单号：</label><input class="easyui-textbox txt" type="text" name="src_tids" value="<?php echo ($trade["src_tids"]); ?>" data-options="disabled:true,required:true"/>
<label>　交易店铺：</label><select class="easyui-combobox sel" name="shop_id" data-options="disabled:true,editable:false,required:true"><?php if(is_array($list["shop"])): $i = 0; $__LIST__ = $list["shop"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i; if($vo['id'] == $trade['shop_id']): ?><option value="<?php echo ($vo["id"]); ?>" selected><?php echo ($vo["name"]); ?></option><?php else: ?> <option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["name"]); ?></option><?php endif; endforeach; endif; else: echo "" ;endif; ?></select>
<label>　　　仓库：</label><select class="easyui-combobox sel" name="warehouse_id" data-options="editable:false,required:true"><?php if(is_array($list["warehouse"])): $i = 0; $__LIST__ = $list["warehouse"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i; if($vo['id'] == $trade['warehouse_id']): ?><option value="<?php echo ($vo["id"]); ?>" selected><?php echo ($vo["name"]); ?></option><?php else: ?> <option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["name"]); ?></option><?php endif; endforeach; endif; else: echo "" ;endif; ?></select>
</div>
<div class="form-div">
<label>物流公司：</label><select class="easyui-combobox sel" type="text" name="logistics_id"  data-options="editable:false,required:true"><?php if($trade['delivery_term'] == 2): if(is_array($logistics["cod_logistics"])): $i = 0; $__LIST__ = $logistics["cod_logistics"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i; if($vo['id'] == $trade['logistics_id']): ?><option value="<?php echo ($vo["id"]); ?>" selected><?php echo ($vo["name"]); ?></option><?php else: ?> <option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["name"]); ?></option><?php endif; endforeach; endif; else: echo "" ;endif; else: if(is_array($logistics["logistics"])): $i = 0; $__LIST__ = $logistics["logistics"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i; if($vo['id'] == $trade['logistics_id']): ?><option value="<?php echo ($vo["id"]); ?>" selected><?php echo ($vo["name"]); ?></option><?php else: ?> <option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["name"]); ?></option><?php endif; endforeach; endif; else: echo "" ;endif; endif; ?></select>
<label>　　业务员：</label><select class="easyui-combobox sel" name="salesman_id"  data-options="editable:false,required:true"><?php if(is_array($list["employee"])): $i = 0; $__LIST__ = $list["employee"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i; if($vo['id'] == $trade['salesman_id']): ?><option value="<?php echo ($vo["id"]); ?>" selected><?php echo ($vo["name"]); ?></option><?php else: ?> <option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["name"]); ?></option><?php endif; endforeach; endif; else: echo "" ;endif; ?></select>
<label>　订单类型：</label><input class="easyui-combobox txt" name="trade_type" data-options="disabled:true,panelHeight:'auto',valueField:'id',textField:'name',data:formatter.get_data('trade_type','def','<?php echo ($trade["trade_type"]); ?>')"/>
<label>　开具发票：</label><input class="easyui-combobox txt" name="invoice_type" data-options="panelHeight:'auto',valueField:'id',textField:'name',data:formatter.get_data('invoice_type','def','<?php echo ($trade["invoice_type"]); ?>'),onSelect:selectInvoiceType"/>
</div>
<div class="form-div">
<label>发票抬头：</label><input class="easyui-textbox txt" name="invoice_title" value="<?php echo ($trade["invoice_title"]); ?>" data-options="disabled:true"/>
<label>　发票内容：</label><input class="easyui-textbox txt" name="invoice_content" value="<?php echo ($trade["invoice_content"]); ?>" data-options="disabled:true"/>
<label>　发货条件：</label><input class="easyui-combobox txt" name="delivery_term" data-options="disabled:true,panelHeight:'auto',valueField:'id',textField:'name',data:formatter.get_data('delivery_term','def','<?php echo ($trade["delivery_term"]); ?>')"/>
<label>　客户网名：</label><input class="easyui-textbox txt" name="buyer_nick" value="<?php echo ($trade["buyer_nick"]); ?>" type="text" data-options="disabled:true"/>
</div>
<div class="form-div">
<label>预估邮费：</label><input class="easyui-numberbox txt" name="post_cost" value="<?php echo ($trade["post_cost"]); ?>" type="text" data-options="min:0,precision:4"/>
<label>　收货人名：</label><input class="easyui-textbox txt" name="receiver_name" value="<?php echo ($trade["receiver_name"]); ?>" type="text" data-options="required:true"/> 
<?php if($id_list["right_flag"] == 0): ?><label>　　　手机：</label><input class="easyui-textbox txt" name="receiver_mobile" value="<?php echo ($trade["receiver_mobile"]); ?>" type="text" data-options="disabled:true"/>
<label>　　　固话：</label><input class="easyui-textbox txt" name="receiver_telno" value="<?php echo ($trade["receiver_telno"]); ?>" type="text" data-options="disabled:true"/>
<?php else: ?>
<label>　　　手机：</label><input class="easyui-textbox txt" name="receiver_mobile" value="<?php echo ($trade["receiver_mobile"]); ?>" type="text" data-options="validType:'mobile'"/>
<label>　　　固话：</label><input class="easyui-textbox txt" name="receiver_telno" value="<?php echo ($trade["receiver_telno"]); ?>" type="text" data-options="validType:'mobileAndTel'"/><?php endif; ?>
</div>
<div class="form-div">
<label>　　　省：</label><input id="editTradeProvince" class="easyui-combobox txt" name="receiver_province"/>
<label>　　　　市：</label><input id="editTradeCity" class="easyui-combobox txt" name="receiver_city"/>
<label>　　　　区：</label><input id="editTradeDistrict" class="easyui-combobox txt" name="receiver_district"/>
<label>　区县别名：</label><input class="easyui-textbox txt" name="receiver_dtb" value="<?php echo ($trade["receiver_dtb"]); ?>" type="text"/>
</div>
<div class="form-div">
<label>　　地址：</label><input class="easyui-textbox" style="width:336px;" name="receiver_address" value="<?php echo ($trade["receiver_address"]); ?>" type="text" data-options="required:true"/> 
<label>　　　邮编：</label><input class="easyui-textbox txt" name="receiver_zip" value="<?php echo ($trade["receiver_zip"]); ?>" type="text" data-options="validType:'zip'"/> 
<label>　支付账户：</label><input class="easyui-textbox txt" name="pay_account" value="<?php echo ($trade["pay_account"]); ?>" type="text" data-options="disabled:true"/>
</div>
<div class="form-div">
<label>　总货款：</label><input class="easyui-numberbox txt" name="goods_amount" value="<?php echo ($trade["goods_amount"]); ?>" type="text" data-options="min:0,precision:4,disabled:true"/>
<label>　　　邮费：</label><input class="easyui-numberbox txt" name="post_amount" value="<?php echo ($trade["post_amount"]); ?>" type="text" data-options="min:0,precision:4,disabled:true"/>
<label>　　　优惠：</label><input class="easyui-numberbox txt" name="discount" value="<?php echo ($trade["discount"]); ?>" type="text" data-options="precision:4,disabled:true"/>
<label>　　　应收：</label><input class="easyui-numberbox txt" name="receivable" value="<?php echo ($trade["receivable"]); ?>" type="text" data-options="min:0,precision:4,disabled:true"/> 
</div>
<div class="form-div">
<label>　　已付：</label><input class="easyui-numberbox txt" name="paid" value="<?php echo ($trade["paid"]); ?>" type="text" data-options="min:0,precision:4,disabled:true"/>
<label>　COD金额：</label><input class="easyui-numberbox txt" name="cod_amount" value="<?php echo ($trade["cod_amount"]); ?>" type="text" data-options="min:0,precision:4,disabled:true"/>
<label>　买家留言：</label><input class="easyui-textbox" style="width:336px;" name="buyer_message" value="<?php echo ($trade["buyer_message"]); ?>" type="text" data-options="disabled:true"/> 
</div>
<!-- <div class="form-div">
<label>货品包装：</label><select class="easyui-combobox sel" name="package_id" value="<?php echo ($trade["package_id"]); ?>"  data-options="panelHeight:'auto',editable:false"><?php if(is_array($list["package"])): $i = 0; $__LIST__ = $list["package"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?></select>
<label>买家COD：</label><input class="easyui-numberbox txt" name="cod_amount" value="<?php echo ($trade["cod_amount"]); ?>" type="text" data-options="min:0,precision:4,disabled:true"/>
<label>佣　　金：</label><input class="easyui-numberbox txt" name="commission" value="<?php echo ($trade["commission"]); ?>" type="text"  data-options="min:0,precision:4,disabled:true"/> 
</div> -->
<div class="form-div">
<label>客服备注：</label><input class="easyui-textbox" style="width:336px;" name="cs_remark" value="<?php echo ($trade["cs_remark"]); ?>" type="text" />
<label>　打印备注：</label><input class="easyui-textbox" style="width:336px;" name="print_remark" value="<?php echo ($trade["print_remark"]); ?>" type="text"/>
</div>
</form>
<a href="#" name="menu-link-order" class="easyui-menubutton" data-options="menu:'#mbut-link-order'">添加货品</a>
<a href="#" name="menu-link-gift" class="easyui-menubutton" data-options="menu:'#mbut-link-gift'">添加赠品</a>
<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-remove',plain:true" onclick="editTrade.remove()">删除</a>
<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-redo',plain:true" onclick="editTrade.refund()">退款</a>
<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-undo',plain:true" onclick="editTrade.restore()">恢复</a>
<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-reload',plain:true" onclick="editTrade.exchangeOrder()">换货</a> 
</div>
<div id="mbut-link-order"><div>添加单品</div><div>添加组合装</div></div>
<div id="mbut-link-gift"><div>添加单品</div><div>添加组合装</div></div>
<script type="text/javascript">
//# sourceURL=edit.trade.js
var editIndex = undefined;
var spec={};
function f_spec(value, rowData, rowIndex) {  
    if (value == 0) {return "";}  
    for (var i = 0; i < spec.length; i++) {  
        if (spec[i].id == value) { return spec[i].name; }  
    }  
    return value;  
}
function sel_spec(record){
	$('#<?php echo ($id_list["id_datagrid"]); ?>').datagrid('endEdit', editIndex);
	rows=$('#<?php echo ($id_list["id_datagrid"]); ?>').datagrid('getSelected');
	$('#<?php echo ($id_list["id_datagrid"]); ?>').datagrid('updateRow',{
		index: editIndex,
		row: {
			spec_no:record['spec_no'],
			spec_code:record['spec_code'],
			price:record['price'],
			weight:record['weight']*rows['actual_num'],
			discount:(record['price']-rows['order_price'])*rows['actual_num'],
			spec_id:record['spec_id'],
		}
	});
}
function trade_edit_onClickRow(index,feild,value) {
	if(editIndex==undefined||editIndex!=index){
        $('#<?php echo ($id_list["id_datagrid"]); ?>').datagrid('endEdit', editIndex);
	}
    $('#<?php echo ($id_list["id_datagrid"]); ?>').datagrid('selectRow', index).datagrid('beginEdit', index);
	var rows=$('#<?php echo ($id_list["id_datagrid"]); ?>').datagrid('getRows');
    editIndex = index;
    	var url='<?php echo ($id_list["url_get_spec"]); ?>';
    	var ed = $('#<?php echo ($id_list["id_datagrid"]); ?>').datagrid('getEditor', {index:editIndex,field:'spec_name'});
    	Post(url,{goods_id:rows[index]['goods_id']},function(res){
    		//for(i=0;i<res.num;i++){if(res.spec[i]['name']==rows[index]['spec_name']){res.spec[i]['selected']=true;}}
    		spec=res.spec;
    		$(ed.target).combobox('loadData', spec);
    		if(res.num==1){$(ed.target).combobox({disabled:true});$(ed.target).combobox('setValue',rows[index]['spec_name'])}
    	},'JSON');
}
$(function () { 
//	$('#<?php echo ($id_list["id_datagrid"]); ?>').datagrid().datagrid('enableCellEditing');
	$('#<?php echo ($id_list["id_datagrid"]); ?>').datagrid({ onClickCell: trade_edit_onClickRow,});
	edit_trade_element=initEditTradeElement();
	setTimeout(function () { 
		setShopAndDelivery();
		selectInvoiceType({id:parseInt(edit_trade_element[4].combobox().combobox('getValue'))});
		$('#<?php echo ($id_list["id_datagrid"]); ?>').datagrid('loadData',<?php echo ($json_orders); ?>);
		editTrade = new ThinDatagrid($('#<?php echo ($id_list["id_datagrid"]); ?>'),undefined,false);
		editTradeArea = new area("editTradeProvince", "editTradeCity", "editTradeDistrict",{province:'<?php echo ($trade["receiver_province"]); ?>',city:'<?php echo ($trade["receiver_city"]); ?>',district:'<?php echo ($trade["receiver_district"]); ?>'}); 
		//总货款-优惠-应收-邮费-计算
		editTrade.calculate=function(row,type){
			if(type==undefined||type==1||type==2){
				//+  总货款-优惠-应收-邮费
				edit_trade_element[7].numberbox('setValue',parseFloat(parseFloat(edit_trade_element[7].numberbox().numberbox('getValue'))+parseFloat(row.price*row.actual_num)).toFixed(4));
				edit_trade_element[9].numberbox('setValue',parseFloat(parseFloat(edit_trade_element[9].numberbox().numberbox('getValue'))+(row.price-row.share_price)*row.actual_num).toFixed(4));
				if(type==undefined||type==1){
					edit_trade_element[8].numberbox('setValue',parseFloat(parseFloat(edit_trade_element[8].numberbox().numberbox('getValue'))+parseFloat(row.share_post)).toFixed(4));
					edit_trade_element[10].numberbox('setValue',parseFloat(parseFloat(edit_trade_element[10].numberbox().numberbox('getValue'))+parseFloat(row.share_amount)+parseFloat(row.share_post)).toFixed(4));
				}else if(type==2){
					edit_trade_element[10].numberbox('setValue',parseFloat(parseFloat(edit_trade_element[10].numberbox().numberbox('getValue'))+row.share_price*row.actual_num).toFixed(4));
				}
			}else if(type==3){
				//-  总货款-优惠-应收-邮费
				edit_trade_element[7].numberbox('setValue',parseFloat(parseFloat(edit_trade_element[7].numberbox().numberbox('getValue'))-row.price*row.actual_num).toFixed(4));
				edit_trade_element[8].numberbox('setValue',parseFloat(parseFloat(edit_trade_element[8].numberbox().numberbox('getValue'))-row.share_post).toFixed(4));
				edit_trade_element[9].numberbox('setValue',parseFloat(parseFloat(edit_trade_element[9].numberbox().numberbox('getValue'))-(row.price-row.share_price)*row.actual_num).toFixed(4));
				edit_trade_element[10].numberbox('setValue',parseFloat(parseFloat(edit_trade_element[10].numberbox().numberbox('getValue'))-row.share_amount-row.share_post).toFixed(4));
			}
		}
		//删除
		editTrade.remove=function(type){
			if (editTrade.editIndex == undefined){messager.alert('请选择操作的行');return;}
			editTrade.selector.datagrid('selectRow',editTrade.editIndex);
			var row=editTrade.selector.datagrid('getSelected');
			if(type==undefined){
			messager.confirm('确定删除此货品？',function(r){
				if(!r){return;}
				if(row.platform_id>0){messager.alert('在线订单不可删除！');return;}
				else if(row.paid>0){messager.alert('已付款订单不可删除，请按退款处理！');return;}
				//总货款-优惠-应收-邮费-计算
				editTrade.calculate(row,3);
				editTrade.selector.datagrid('cancelEdit', editTrade.editIndex) .datagrid('deleteRow', editTrade.editIndex);
				editTrade.editIndex = undefined;
			});}else{
				if(type==1){
					if(row.platform_id>0){messager.alert('在线订单不可删除！');return;}
					else if(row.paid>0){messager.alert('已付款订单不可删除，请按退款处理！');return;}
				}
				//总货款-优惠-应收-邮费-计算
				editTrade.calculate(row,3);
				editTrade.selector.datagrid('cancelEdit', editTrade.editIndex) .datagrid('deleteRow', editTrade.editIndex);
				editTrade.editIndex = undefined;
			}
		}
		//退款
		editTrade.refund=function(){
			if (editTrade.editIndex == undefined){messager.alert('请选择操作的行');return;}
			editTrade.selector.datagrid('selectRow',editTrade.editIndex);
			var row=editTrade.selector.datagrid('getSelected');
			if(row.platform_id==0&&row.paid==0){messager.alert('线下未付款订单可直接删除，无需退款');return;}
			if(row.refund_status>1){messager.alert('子订单：'+row.src_oid+'已取消');return;}
			messager.confirm('确定退款？',function(r){
				if(!r){return;}
				var trade_row=edit_trade_element['show_dg'].datagrid('getSelected');
				var url='<?php echo ($id_list["url_refund"]); ?>';
				Post(url,{id:trade_row.id,stoId:row.id,oid:row.src_oid,shop_id:row.shop_id},function(res){
					if(!res.status){messager.alert(res.info);return;}
					$('#<?php echo ($id_list["form_id"]); ?>').form('load',res.trade);
					if(row.suite_id==0){
						//editTrade.calculate(row,3);
						//edit_trade_element[11].numberbox('setValue',parseFloat(parseFloat(edit_trade_element[11].numberbox().numberbox('getValue'))-parseFloat(row.paid)).toFixed(4));
						row.actual_num=res.actual_num;
						row.stock_reserved=res.stock_reserved;
						row.refund_status=res.refund_status;
						row.remark=res.remark;
						editTrade.selector.datagrid('refreshRow',editTrade.editIndex);
					}else{
						var rows=editTrade.selector.datagrid('getRows');
						var actual_num=0;
						var index=0;
						for(var i in rows){
							if(row.src_oid==rows[i].src_oid){
								index=editTrade.selector.datagrid('getRowIndex',rows[i]);
								//editTrade.calculate(rows[i],3);
								//edit_trade_element[11].numberbox('setValue',parseFloat(parseFloat(edit_trade_element[11].numberbox().numberbox('getValue'))-rows[i].paid).toFixed(4));
								rows[i].actual_num=res.actual_num;
								rows[i].stock_reserved=res.stock_reserved;
								rows[i].refund_status=res.refund_status;
								rows[i].remark=res.remark;
								editTrade.selector.datagrid('refreshRow',index);
							}
						}
					}
					trade_row.version_id=res.trade.version_id;
					trade_row.refund_status=res.trade.refund_status;
				},'JSON');
			});
		}
		//恢复
		editTrade.restore=function(){
			if (editTrade.editIndex == undefined){messager.alert('请选择操作的行');return;}
			editTrade.selector.datagrid('selectRow',editTrade.editIndex);
			var row=editTrade.selector.datagrid('getSelected');
			if(row.refund_status<2){messager.alert('子订单'+(!row.src_oid?'':':'+row.src_oid)+'未退款');return;}
			var trade_row=edit_trade_element['show_dg'].datagrid('getSelected');
			var url='<?php echo ($id_list["url_restore"]); ?>';
			Post(url,{id:trade_row.id,oid:row.id},function(res){
				if(!res.status){messager.alert(res.info);return;}
				$('#<?php echo ($id_list["form_id"]); ?>').form('load',res.trade);
				if(row.suite_id==0){
					row.actual_num=res.actual_num;
					//editTrade.calculate(row,1);
					//edit_trade_element[11].numberbox('setValue',parseFloat(parseFloat(edit_trade_element[11].numberbox().numberbox('getValue'))+parseFloat(row.paid)).toFixed(4));
					row.actual_num=res.actual_num;
					row.stock_reserved=res.stock_reserved;
					row.refund_status=res.refund_status;
					row.remark=res.remark;
					editTrade.selector.datagrid('refreshRow',editTrade.editIndex);
				}else{
					var rows=editTrade.selector.datagrid('getRows');
					var index=0;
					for(var i in rows){
						if(row.src_oid==rows[i].src_oid){
							index=editTrade.selector.datagrid('getRowIndex',rows[i]);
							//rows[i].actual_num=res.actual_num;
							//editTrade.calculate(rows[i],1);
							edit_trade_element[11].numberbox('setValue',parseFloat(parseFloat(edit_trade_element[11].numberbox().numberbox('getValue'))+parseFloat(rows[i].paid)).toFixed(4));
							rows[i].stock_reserved=res.stock_reserved;
							rows[i].refund_status=res.refund_status;
							rows[i].remark=res.remark;
							editTrade.selector.datagrid('refreshRow',index);
						}
					}
				}
				trade_row.version_id=res.trade.version_id;
				trade_row.refund_status=res.trade.refund_status;
			},'JSON');
		}
		//换货
		editTrade.exchangeOrder=function(){
			if (editTrade.editIndex==undefined){messager.info('请选择操作的行');return;}
			editTrade.selector.datagrid('selectRow',editTrade.editIndex);
			var row=editTrade.selector.datagrid('getSelected');
			if (!row.sto_id) {messager.info('无效子订单');return;};
			if (row.refund_status>1) {messager.info('不能替换退款货品');return;};
			var url='<?php echo ($id_list["url_exchange"]); ?>';
			url += url.indexOf('?') != -1 ? '&id='+row.sto_id : '?id='+row.sto_id;
			var buttons=[ {text:'确定',handler:function(){submitEditExchangeOrderDialog();}}, {text:'取消',handler:function(){Dialog.cancel(edit_trade_element['exchange']);}} ];
			Dialog.show(edit_trade_element['exchange'],'订单换货',url,560,764,buttons);
		}
	}, 0); 
});
//dialog--submit
// tradeCheck.submitEditDialog=function(){
function submitTradeEditDialog(){
	var trade_form=$('#<?php echo ($id_list["form_id"]); ?>'); 
	if(!editTrade.endEdit(true)) { return false; }
	if(!trade_form.form('validate')){ return false;}
	var rows=editTrade.selector.datagrid('getRows');
	if (rows.length==0){messager.info('订单至少保留一件货品');return false;}
	else{
		var count_refund=0;var count_gift=0; 
		for(var i=0;i<rows.length;i++){
			if(rows[i]['refund_status']>1){count_refund++;}
			if(rows[i]['gift_type']>0){count_gift++;}
		} 
		if(count_refund>0&&(count_refund+count_gift==rows.length)){
			$('#flag_set_dialog').dialog('close');edit_trade_element['show_dg'].datagrid('reload');return false;
		}
	}
	if(edit_trade_element['mobile'].textbox('getValue')==''&&edit_trade_element['telno'].textbox('getValue')==''){ messager.alert('手机和固话至少有一个不能为空');return; }
	var data={};
	for(var i=7;i<11;i++){ edit_trade_element[i].numberbox('enable');}
	for(var i=0;i<3;i++){ edit_trade_element[i].combobox('enable');}
	var trade_form_data=trade_form.form('get');
	var province=$('#editTradeProvince').combobox('getText');
    var city=$('#editTradeCity').combobox('getText');
    var district=$('#editTradeDistrict').combobox('getText');
	if (province!=undefined && province!=0 && city!=undefined && city!=0 && district!=undefined) {trade_form_data['receiver_area']=province+' '+city+' '+district;
	}	
	var trade_row=edit_trade_element['show_dg'].datagrid('getSelected');
	trade_form_data['version_id']=trade_row.version_id;//tradeCheck.selectRows[0].version_id;
	//trade_form_data['goods_type_count']=editTrade.selector.datagrid('getRows').length;//数量
	data['id']=<?php echo ($trade["id"]); ?>;
	data['info']=JSON.stringify(trade_form_data);
	var orders={};
	orders['add']=editTrade.selector.datagrid('getChanges','inserted');
	orders['delete']=editTrade.selector.datagrid('getChanges','deleted');
	orders['update']=editTrade.selector.datagrid('getChanges','updated');
	data['orders']=JSON.stringify(orders);
	for(var i=0;i<3;i++){ if(i!==1){edit_trade_element[i].combobox('disable');}}
	for(var i=7;i<11;i++){ edit_trade_element[i].numberbox('disable');}
	Post('<?php echo U('TradeCheck/editTrade');?>',data,function(res){
		if(!res.status){
			messager.alert(res.info);
			return false;
		}else{
			$('#flag_set_dialog').dialog('close');
			edit_trade_element['show_dg'].datagrid('reload');// tradeCheck.refresh();//添加完后刷新表格
		}
	},"JSON");
}
//元素--初始化
function initEditTradeElement(){
	var form_id='<?php echo ($id_list["form_id"]); ?>';
	var element={
			0:$('#'+form_id+" :input[name='trade_type']"),
			1:$('#'+form_id+" :input[name='shop_id']"),
			2:$('#'+form_id+" :input[name='delivery_term']"),
			3:$('#'+form_id+" :input[name='warehouse_id']"),
			4:$('#'+form_id+" :input[name='invoice_type']"),
			5:$('#'+form_id+" :input[name='invoice_title']"),
			6:$('#'+form_id+" :input[name='invoice_content']"),
			7:$('#'+form_id+" :input[name='goods_amount']"),
			8:$('#'+form_id+" :input[name='post_amount']"),
			9:$('#'+form_id+" :input[name='discount']"),
			10:$('#'+form_id+" :input[name='receivable']"),
			11:$('#'+form_id+" :input[name='paid']"),
			'mobile':$('#'+form_id+" :input[name='receiver_mobile']"),
			'telno':$('#'+form_id+" :input[name='receiver_telno']"),
			'exchange':'trade_exchange',
			'dialog_id':'reason_show_dialog',
			'show_dg':$('#<?php echo ($id_list["datagrid_id"]); ?>'),
	};
	return element;
}
//设置可编辑(店铺和发货条件)
function setShopAndDelivery(){
	var trade_row=edit_trade_element['show_dg'].datagrid('getSelected');
	if(trade_row.trade_from==2){
		edit_trade_element[1].combobox('enable');
		//edit_trade_element[2].combobox('enable');
	}
}
//invoice--选择事件
function selectInvoiceType(record){
	if(record.id==0){edit_trade_element[5].textbox('disable');edit_trade_element[6].textbox('disable');}
	else{edit_trade_element[5].textbox('enable');edit_trade_element[6].textbox('enable');}
}
//datagrid--事件
function editTradeRowStyler(i,row){
	var refund_bg_color='<?php echo ($trade["bg_color"]); ?>';
	if(row.refund_status>1){return refund_bg_color;}
	return;
}
function beginEditTrade(index,row){
	edit_trade_element.actual_num=parseFloat(row.actual_num).toFixed("<?php echo ($point_number); ?>");
	edit_trade_element.share_price=parseFloat(row.share_price).toFixed(4);
	edit_trade_element.share_post=parseFloat(row.share_post).toFixed(4);
	edit_trade_element.share_amount=parseFloat(row.share_amount).toFixed(4);
	edit_trade_element.discount=parseFloat(row.discount).toFixed(4);
}
function endEditTrade(index, row, changes){
	var flag=false;
	$.each(changes,function(key,val){
		if(key=='remark'){return;}
		if(key=='spec_name'){return;}
		flag=true;
		switch(key){
		case 'actual_num':
			if(row.platform_id>0||row.paid>0){row.actual_num=edit_trade_element.actual_num;}
			else if(row.actual_num==0){editTrade.remove(1);}
			else{row.actual_num=row.actual_num<0?-row.actual_num:row.actual_num;
			row.share_amount=parseFloat(row.share_price*row.actual_num).toFixed(4);
			row.discount=parseFloat((row.price-row.share_price)*row.actual_num).toFixed(4);}
			break;
		case 'share_price':
			if(row.gift_type==2){row.share_price=edit_trade_element.share_price;}
			else if(row.platform_id>0||row.paid>0){row.share_price=edit_trade_element.share_price;}//避免货款分摊不正确
			else{row.share_price=row.share_price<0?-row.share_price:row.share_price; 
			row.share_amount=parseFloat(row.share_price*row.actual_num).toFixed(4);
			row.discount=parseFloat((row.price-row.share_price)*row.actual_num).toFixed(4);}
			break;
		case 'share_amount':
			if(row.gift_type==2){row.share_amount=edit_trade_element.share_amount;}
			else if(row.platform_id>0||row.paid>0){row.share_amount=edit_trade_element.share_amount;}//避免货款分摊不正确
			else{row.share_amount=row.share_amount<0?-row.share_amount:row.share_amount;
			row.share_price=parseFloat(row.share_amount/row.actual_num).toFixed(4);
			row.discount=parseFloat(row.price*row.actual_num-row.share_amount).toFixed(4);}
			break;
		case 'discount':
			if(row.gift_type==2){row.discount=edit_trade_element.discount;}
			else if(row.platform_id>0||row.paid>0){row.discount=edit_trade_element.discount;}//避免货款分摊不正确
			else{row.share_amount=parseFloat(row.price*row.actual_num-row.discount).toFixed(4);
			row.share_price=parseFloat(row.share_amount/row.actual_num).toFixed(4);}
			break;
		case 'share_post':
			if(row.platform_id>0||row.paid>0){row.share_post=edit_trade_element.share_post;}
			else{row.share_post=row.share_post<0?-row.share_post:row.share_post;}
			break;
		}
		if(row.platform_id>0){ messager.alert('线上订单不可修改！');}else if(row.paid>0){messager.alert('不能修改已付款订单，请按退款处理！');}
	});
	if(!flag){return;}
	//总货款-优惠-应收-邮费-计算
	edit_trade_element[7].numberbox('setValue',parseFloat(parseFloat(edit_trade_element[7].numberbox().numberbox('getValue'))-edit_trade_element.actual_num*row.price).toFixed(4));
	edit_trade_element[8].numberbox('setValue',parseFloat(parseFloat(edit_trade_element[8].numberbox().numberbox('getValue'))-edit_trade_element.share_post).toFixed(4));
	edit_trade_element[9].numberbox('setValue',parseFloat(parseFloat(edit_trade_element[9].numberbox().numberbox('getValue'))-(row.price-edit_trade_element.share_price)*edit_trade_element.actual_num).toFixed(4));
	edit_trade_element[10].numberbox('setValue',parseFloat(parseFloat(edit_trade_element[10].numberbox().numberbox('getValue'))-edit_trade_element.share_amount-edit_trade_element.share_post).toFixed(4));
	
	//editTrade.calculate(row,1);
	edit_trade_element[7].numberbox('setValue',parseFloat(parseFloat(edit_trade_element[7].numberbox().numberbox('getValue'))+parseFloat(row.price*row.actual_num)).toFixed(4));
	edit_trade_element[8].numberbox('setValue',parseFloat(parseFloat(edit_trade_element[8].numberbox().numberbox('getValue'))+parseFloat(row.share_post)).toFixed(4));
	edit_trade_element[9].numberbox('setValue',parseFloat(parseFloat(edit_trade_element[9].numberbox().numberbox('getValue'))+(row.price-row.share_price)*row.actual_num).toFixed(4));
	edit_trade_element[10].numberbox('setValue',parseFloat(parseFloat(edit_trade_element[10].numberbox().numberbox('getValue'))+parseFloat(row.share_amount)+parseFloat(row.share_post)).toFixed(4));
}
//添加--物品
$($(".easyui-menubutton[name='menu-link-order']").menubutton().menubutton('options').menu).menu({
	onClick:function(item){
	 switch(item.text){
	 case '添加单品':
		 var params={'prefix':'edit_trade','type':true};
		 $('#' + edit_trade_element['dialog_id']).richDialog('goodsSpec', addSpecToSalesTrade, params, false);
		 break;
	 case '添加组合装':
		 $('#' + edit_trade_element['dialog_id']).richDialog('goodsSuite', addSuiteToSalesTrade, 'edit_trade', false);
		 break;
	 }}
});
//添加--赠品
$($(".easyui-menubutton[name='menu-link-gift']").menubutton().menubutton('options').menu).menu({
	onClick:function(item){
	 switch(item.text){
	 case '添加单品':
		 var params={'prefix':'edit_trade','type':true};
		 $('#' + edit_trade_element['dialog_id']).richDialog('goodsSpec', addSpecToSalesTrade, params, true);
		 break;
	 case '添加组合装':
		 $('#' + edit_trade_element['dialog_id']).richDialog('goodsSuite', addSuiteToSalesTrade, 'edit_trade', true);
		 break;
	 }}
});
function addSpecToSalesTrade(spec_dg_id,sub_dg_id,is_gift){
	var spec_dg=$('#'+sub_dg_id);
    var spec_rows=spec_dg.datagrid('getRows');
    var show_dg=$('#'+'<?php echo ($id_list["id_datagrid"]); ?>');
    $('#'+edit_trade_element['dialog_id']).dialog('close');
    var show_rows=show_dg.datagrid('getRows');
    var flag=false;
    var show_index=0;
    for(var i in spec_rows){
    	spec_rows[i].refund_status=0;
    	spec_rows[i].paid=0.0000;
    	if(is_gift){
    		spec_rows[i].gift_type=2;
    		spec_rows[i].share_price=0.0000;
    	}else{
    		spec_rows[i].gift_type=0;
    		spec_rows[i].share_price=spec_rows[i].retail_price;
    	}
    	spec_rows[i].num=parseFloat(spec_rows[i].num).toFixed(4);
    	flag=false;
    	for(var x in show_rows){
    		if(show_rows[x].paid==0&&spec_rows[i].spec_no==show_rows[x].spec_no&&spec_rows[i].gift_type==show_rows[x].gift_type){
    			//messager.confirm('货品名称：'+spec_rows[i].goods_name+'<br>货品编码：'+spec_rows[i].goods_no+'<br>商家编码'+spec_rows[i].spec_no+'<br>已存在此货品，是否继续添加？',function(r){ if(!r){break;} else{} });
				show_index=show_dg.datagrid('getRowIndex',show_rows[x]);
				//show_rows[x].num=parseFloat((parseFloat(show_rows[x].num)+spec_rows[i].num)).toFixed(4);
				show_rows[x].actual_num=show_rows[x].num;
				//show_rows[x].share_amount=parseFloat(show_rows[x].share_price*show_rows[x].actual_num).toFixed(4);
				show_rows[x].discount=parseFloat((show_rows[x].price-show_rows[x].share_price)*show_rows[x].actual_num).toFixed(4);
    			show_dg.datagrid('refreshRow',show_index);
    			flag=true;
    			//总货款-优惠-应收-计算
    			edit_trade_element[7].numberbox('setValue',parseFloat(parseFloat(edit_trade_element[7].numberbox().numberbox('getValue'))+show_rows[x].price*show_rows[x].num).toFixed(4));
    			edit_trade_element[9].numberbox('setValue',parseFloat(parseFloat(edit_trade_element[9].numberbox().numberbox('getValue'))+(show_rows[x].price-show_rows[x].share_price)*spec_rows[i].num).toFixed(4));
    			edit_trade_element[10].numberbox('setValue',parseFloat(parseFloat(edit_trade_element[10].numberbox().numberbox('getValue'))+show_rows[x].share_price*spec_rows[i].num).toFixed(4));
    		}
    	}
    	if(!flag){
    		spec_rows[i].is_suite=0;
        	spec_rows[i].platform_id=0;
        	spec_rows[i].price=spec_rows[i].retail_price;
        	spec_rows[i].order_price=spec_rows[i].price;
        	spec_rows[i].actual_num=spec_rows[i].num;
        	spec_rows[i].share_amount=parseFloat(spec_rows[i].share_price*spec_rows[i].actual_num).toFixed(4);
        	spec_rows[i].share_post=0.0000;
        	spec_rows[i].discount=parseFloat((spec_rows[i].price-spec_rows[i].share_price)*spec_rows[i].actual_num).toFixed(4);
    	    show_dg.datagrid('appendRow',spec_rows[i]);
    	  	//总货款-优惠-应收-计算
    	  	editTrade.calculate(spec_rows[i],2);
			//edit_trade_element[7].numberbox('setValue',parseFloat(parseFloat(edit_trade_element[7].numberbox().numberbox('getValue'))+spec_rows[i].price*spec_rows[i].actual_num).toFixed(4));
			//edit_trade_element[9].numberbox('setValue',parseFloat(parseFloat(edit_trade_element[9].numberbox().numberbox('getValue'))+(spec_rows[i].price-spec_rows[i].share_price)*spec_rows[i].actual_num).toFixed(4));
			//edit_trade_element[10].numberbox('setValue',parseFloat(parseFloat(edit_trade_element[10].numberbox().numberbox('getValue'))+spec_rows[i].share_price*spec_rows[i].actual_num).toFixed(4));
    	}
    }
}
function addSuiteToSalesTrade(suite_dg_id,is_gift){
    var suite_dg=$('#'+suite_dg_id);
    var suite_row=suite_dg.datagrid('getSelected');
    var show_dg=$('#'+'<?php echo ($id_list["id_datagrid"]); ?>');
    $('#'+edit_trade_element['dialog_id']).dialog('close');
    var show_rows=show_dg.datagrid('getRows');
    var flag=false;
    var show_index=0;
    suite_row.refund_status=0;
    suite_row.paid=0.0000;
    if(is_gift){
    	suite_row.gift_type=2;
    	suite_row.share_price=0.0000;
	}else{
    	suite_row.gift_type=0;
		suite_row.share_price=suite_row.retail_price;
	}
    suite_row.num=1.0000;
    suite_row.is_suite=1;
    for(var x in show_rows){
    	if(suite_row.suite_no==show_rows[x].suite_no&&suite_row.gift_type==show_rows[x].gift_type){
    		//messager.confirm('货品名称：'+suite_row.suite_name+'<br>商家编码'+suite_row.suite_no+'<br>已存在此货品，是否继续添加？','warning',function(r){ if(!r){break;} else{ } });
    		show_index=show_dg.datagrid('getRowIndex',show_rows[x]);
			//show_rows[x].num=parseFloat((parseFloat(show_rows[x].num)+suite_row.num)).toFixed(4);
			show_rows[x].actual_num=show_rows[x].num;
			//show_rows[x].share_amount=parseFloat(show_rows[x].share_price*show_rows[x].actual_num).toFixed(4);
			show_rows[x].discount=parseFloat((show_rows[x].price-show_rows[x].share_price)*show_rows[x].actual_num).toFixed(4);
			show_dg.datagrid('refreshRow',show_index);
			flag=true;
			//总货款-优惠-应收-计算
			edit_trade_element[7].numberbox('setValue',parseFloat(parseFloat(edit_trade_element[7].numberbox().numberbox('getValue'))+show_rows[x].price*show_rows[x].num).toFixed(4));
			edit_trade_element[9].numberbox('setValue',parseFloat(parseFloat(edit_trade_element[9].numberbox().numberbox('getValue'))+(show_rows[x].price-show_rows[x].share_price)*suite_row.num).toFixed(4));
			edit_trade_element[10].numberbox('setValue',parseFloat(parseFloat(edit_trade_element[10].numberbox().numberbox('getValue'))+show_rows[x].share_price*suite_row.num).toFixed(4));
    	}
    }
    if(!flag){
    	suite_row.platform_id=0;
    	suite_row.goods_name=suite_row.suite_name;
    	suite_row.spec_no=suite_row.suite_no;
    	suite_row.price=suite_row.retail_price;
    	suite_row.order_price=suite_row.price;
    	suite_row.actual_num=suite_row.num;
    	suite_row.share_amount=parseFloat(suite_row.share_price*suite_row.actual_num).toFixed(4);
    	suite_row.share_post=0.0000;
    	suite_row.discount=parseFloat((suite_row.price-suite_row.share_price)*suite_row.actual_num).toFixed(4);
    	show_dg.datagrid('appendRow',suite_row);
    	//总货款-优惠-应收-计算
    	editTrade.calculate(suite_row,2);
		//edit_trade_element[7].numberbox('setValue',parseFloat(parseFloat(edit_trade_element[7].numberbox().numberbox('getValue'))+suite_row.price*suite_row.actual_num).toFixed(4));
		//edit_trade_element[9].numberbox('setValue',parseFloat(parseFloat(edit_trade_element[9].numberbox().numberbox('getValue'))+(suite_row.price-suite_row.share_price)*suite_row.actual_num).toFixed(4));
		//edit_trade_element[10].numberbox('setValue',parseFloat(parseFloat(edit_trade_element[10].numberbox().numberbox('getValue'))+suite_row.share_price*suite_row.actual_num).toFixed(4));
    }
}
</script>