<?php if (!defined('THINK_PATH')) exit();?>
<div class="easyui-layout" data-options="fit:true,border:false" style="height:350px;width:850px;overflow:hidden;">
	<div data-options="region:'center'" style="padding:1px;background:#eee;overflow:hidden;">
		<form   id="<?php echo ($id_list["form_id"]); ?>">
			<!--<div class="form-div">-->
				<!--<label>原始单号：</label><input class="easyui-textbox txt" type="text" name="tid" style="width:324px;" data-options="editable:false,buttonText:'...'"/>-->
				<!--<label>系统订单：</label><input class="easyui-textbox txt" type="text" name="trade_no"  data-options="required:true,disabled:true"/>-->
				<!--<label>退换单号：</label><input class="easyui-textbox txt" type="text" name="refund_no" data-options="disabled:true,required:true"/>-->
				<!--<label>退货金额：</label><input class="easyui-numberbox txt" type="text" name="goods_amount" value="0.0000" data-options="disabled:true,min:0,precision:4,required:true"/>-->
			<!--</div>-->
			<!--<div class="form-div">-->
				<!--<label>买家昵称：</label><input class="easyui-textbox txt" type="text" name="buyer_nick"  data-options="required:true,disabled:true"/>-->
				<!--<label>退换类别：</label><input class="easyui-combobox txt" type="text" name="type" data-options="editable:false,required:true,panelHeight:'auto',valueField:'id',textField:'name',data:formatter.get_data('refund_type_select','def','3'),onSelect:addEditRefund.selectRefundType"/>-->
				<!--<label>退换原因：</label><select class="easyui-combobox sel" type="text" name="reason_id"  data-options="editable:false,required:true"><option value="0">无</option><?php if(is_array($list["reason"])): $i = 0; $__LIST__ = $list["reason"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?></select>-->
				<!--<label>原始退款单号：</label><input class="easyui-textbox txt" type="text" name="src_no" style="width:105px;" data-options="disabled:true"/>-->
				<!--<label>退款金额：</label><input class="easyui-numberbox txt" type="text" name="refund_amount" value="0.0000" data-options="disabled:true,precision:4,required:true"/>-->
			<!--</div>-->
			<!--<div class="form-div">-->
				<!--<label>买家账号：</label><input class="easyui-textbox txt" type="text" name="pay_account" data-options=""/>-->
				<!--<label>金额流向：</label><input class="easyui-combobox txt" type="text" name="flow_type"  data-options="editable:false,required:true,panelHeight:'auto',valueField:'id',textField:'name',data:formatter.get_data('flow_type'),onSelect:addEditRefund.selectFlowTo"/>-->
				<!--<label>平台退款：</label><input class="easyui-numberbox txt" type="text" name="guarante_refund_amount" value="0.0000" data-options="precision:4,required:true"/>-->
				<!--<label>线下退款：</label><input class="easyui-numberbox txt" type="text" name="direct_refund_amount" value="0.0000" data-options="precision:4,required:true"/>-->
				<!--<label>付款方式：</label><input class="easyui-combobox txt" name="pay_method" data-options="panelHeight:'auto',valueField:'id',textField:'name',data:formatter.get_data('pay_method','def')"/>-->
			<!--</div>-->
			<!--<div class="form-div">-->
				<!--<label>退货入库：</label><select class="easyui-combobox sel sel-disabled" name="warehouse_id" data-options="editable:false,required:true"><?php if(is_array($list["warehouse"])): $i = 0; $__LIST__ = $list["warehouse"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?></select>-->
				<!--<label>关联预入库单：</label><input  	class="easyui-textbox 	txt" 	name="stockin_pre_no" 	data-options="buttonText: '...'"style="width: 300px"  />-->
				<!--<label>备　　注：</label><input class="easyui-textbox txt" type="text" name="remark" style="width:324px;" data-options=""/>-->
				<!--&lt;!&ndash; <label>换出金额：</label><input class="easyui-numberbox txt" type="text" name="refund_amount" value="0.00" data-options="min:0,precision:4,disabled:true,required:true"/> &ndash;&gt;-->
			<!--</div>-->
			<div  id="<?php echo ($id_list["more_content_info"]); ?>">
				<hr style="margin-top: 2px;border:none;border-top:2px dotted #95B8E7;">
				<span style="color:#0E2D5F;"><label  id="more_info_title">收货信息：</label></span>
				<a href="javascript:void(0)" class="easyui-linkbutton" onclick="returnOrder.useCustomer()">使用原地址信息</a>
				<span style="color:red;margin-left:60px;"  id="more_info_goods-amount"><label>换出货品总售价：</label><label  id="goods-amount" style="margin-right: 10px;">0.0000</label></span>
				<span style="color:red;margin-left:40px;display: none;"  id="is_checknumber_info">*暂无查看和修改手机、固话的权限！</span>
				<div class="form-div">
					<label>物流公司：</label><input class="easyui-combobox txt" type="text" name="logistics_name"  data-options="editable:false,required:true,valueField:'name',textField:'name',data:formatter.get_data('logistics_type','def')"/>
					<label>姓　　名：</label><input class="easyui-textbox txt" name="swap_receiver" type="text" />
					<label>手　　机：</label><input class="easyui-textbox txt" name="swap_mobile" type="text" data-options="validType:'mobile'"/>
					<label>固　　话：</label><input class="easyui-textbox txt" name="swap_telno" type="text" data-options="validType:'mobileAndTel'"/>
				 	<label>换出金额：</label><input class="easyui-numberbox txt" type="text" name="exchange_amount" value="0.00" data-options="disabled:true,precision:4,required:true"/>
				</div>
				<div class="form-div">
					<label>省　　份：</label><input  id="<?php echo ($id_list["province"]); ?>" class="easyui-combobox txt" name="swap_province"/>
					<label>城　　市：</label><input  id="<?php echo ($id_list["city"]); ?>" class="easyui-combobox txt" name="swap_city"/>
					<label>区　　县：</label><input  id="<?php echo ($id_list["district"]); ?>" class="easyui-combobox txt" name="swap_district"/>
					<label>详细地址：</label><input class="easyui-textbox" style="width:324px;" name="swap_address"  type="text"/>
				</div>
			</div>
		</form>
	</div>
	<div data-options="region:'south',split:true" style="height:260px;background:#eee;overflow:hidden;">
		<div class="easyui-tabs" data-options="fit:true,border:false,plain:true"  id="<?php echo ($id_list["tab_container"]); ?>">
			<!--<div title="<?php echo ($datagrid["refund_order"]["title"]); ?>">-->
				<!--<div  id="<?php echo ($id_list["toolbar_refund"]); ?>">-->
					<!--<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-reload',plain:true" onclick="refundOrder.exchangeRefundGoods()">更换货品</a>-->
					<!--<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-remove',plain:true" onclick="refundOrder.remove()">删除</a>-->
				<!--</div>-->
				<!--<table id="<?php echo ($datagrid["refund_order"]["id"]); ?>" class="easyui-datagrid" data-options='<?php $dataOptions = array_merge(array ( 'border' => false, 'fit' => true, 'fitColumns' => true, 'rownumbers' => true, 'singleSelect' => true, 'pagination' => true, 'pageList' => array ( 0 => 20, 1 => 50, 2 => 100, ), 'pageSize' => 20, ), $datagrid["refund_order"]["options"]);if(isset($dataOptions['toolbar']) && substr($dataOptions['toolbar'],0,1) != '#'): unset($dataOptions['toolbar']); endif;if(isset($dataOptions['methods'])): unset($dataOptions['methods']);endif; echo trim(json_encode($dataOptions), '{}[]').((isset($datagrid["refund_order"]["options"]['toolbar']) && substr($datagrid["refund_order"]["options"]['toolbar'],0,1) != '#')?',"toolbar":'.$datagrid["refund_order"]["options"]['toolbar']:null).(isset($datagrid["refund_order"]["options"]['methods'])? ','.$datagrid["refund_order"]["options"]['methods']:null); ?>' style="<?php echo ($datagrid["refund_order"]["style"]); ?>" ><thead><tr><?php if(is_array($datagrid["refund_order"]["fields"])):foreach ($datagrid["refund_order"]["fields"] as $key=>$arr):if(isset($arr['formatter'])):unset($arr['formatter']);endif;if(isset($arr['methods'])):unset($arr['methods']);endif;if(isset($arr['editor'])):unset($arr['editor']);endif;echo "<th data-options='".trim(json_encode($arr), '{}[]').(isset($datagrid["refund_order"]["fields"][$key]['formatter'])?",\"formatter\":".$datagrid["refund_order"]["fields"][$key]['formatter']:null).(isset($datagrid["refund_order"]["fields"][$key]['editor'])?",\"editor\":".$datagrid["refund_order"]["fields"][$key]['editor']:null).(isset($datagrid["refund_order"]["fields"][$key]['methods'])?",".$datagrid["refund_order"]["fields"][$key]['methods']:null)."'>".$key."</th>";endforeach;endif; ?></tr></thead></table>-->
			<!--</div>-->
			<div title="<?php echo ($datagrid["return_order"]["title"]); ?>">
				<div  id="<?php echo ($id_list["toolbar_return"]); ?>">
					<form   id="<?php echo ($id_list["return_form"]); ?>">
						<span style="margin-right: 10px;"><label>店铺：</label><select class="easyui-combobox sel sel-disabled" name="shop_id" data-options="editable:false,required:true"><?php if(is_array($list["shop"])): $i = 0; $__LIST__ = $list["shop"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?></select></span>
						<span style="margin-right: 10px;"><label>仓库：</label><select class="easyui-combobox sel sel-disabled" name="swap_warehouse_id" data-options="editable:false,required:true"><?php if(is_array($list["warehouse"])): $i = 0; $__LIST__ = $list["warehouse"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?></select></span>
						<a href="#" name="menu-select-return-order" class="easyui-menubutton" data-options="menu:'#mbut-select-return-order'">添加货品</a>
						<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-remove',plain:true" onclick="returnOrder.remove()">删除</a>
					</form>
				</div>
				<div  id="mbut-select-return-order"><div>添加单品</div><div>添加组合装</div></div>
				<table id="<?php echo ($datagrid["return_order"]["id"]); ?>" class="easyui-datagrid" data-options='<?php $dataOptions = array_merge(array ( 'border' => false, 'fit' => true, 'fitColumns' => true, 'rownumbers' => true, 'singleSelect' => true, 'pagination' => true, 'pageList' => array ( 0 => 20, 1 => 50, 2 => 100, ), 'pageSize' => 20, ), $datagrid["return_order"]["options"]);if(isset($dataOptions['toolbar']) && substr($dataOptions['toolbar'],0,1) != '#'): unset($dataOptions['toolbar']); endif;if(isset($dataOptions['methods'])): unset($dataOptions['methods']);endif; echo trim(json_encode($dataOptions), '{}[]').((isset($datagrid["return_order"]["options"]['toolbar']) && substr($datagrid["return_order"]["options"]['toolbar'],0,1) != '#')?',"toolbar":'.$datagrid["return_order"]["options"]['toolbar']:null).(isset($datagrid["return_order"]["options"]['methods'])? ','.$datagrid["return_order"]["options"]['methods']:null); ?>' style="<?php echo ($datagrid["return_order"]["style"]); ?>" ><thead><tr><?php if(is_array($datagrid["return_order"]["fields"])):foreach ($datagrid["return_order"]["fields"] as $key=>$arr):if(isset($arr['formatter'])):unset($arr['formatter']);endif;if(isset($arr['methods'])):unset($arr['methods']);endif;if(isset($arr['editor'])):unset($arr['editor']);endif;echo "<th data-options='".trim(json_encode($arr), '{}[]').(isset($datagrid["return_order"]["fields"][$key]['formatter'])?",\"formatter\":".$datagrid["return_order"]["fields"][$key]['formatter']:null).(isset($datagrid["return_order"]["fields"][$key]['editor'])?",\"editor\":".$datagrid["return_order"]["fields"][$key]['editor']:null).(isset($datagrid["return_order"]["fields"][$key]['methods'])?",".$datagrid["return_order"]["fields"][$key]['methods']:null)."'>".$key."</th>";endforeach;endif; ?></tr></thead></table>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
//# sourceURL=intelligence_exchange_refund_edit.js
var is_api=('<?php echo ($is_api); ?>');
$(function(){
	$('#<?php echo ($datagrid["return_order"]["id"]); ?>').datagrid().datagrid('enableCellEditing');
	intelligenceExchange = new ThinDatagrid($('#<?php echo ($id_list["id_datagrid_return"]); ?>'),undefined,false);
	initRefundElement();
	setTimeout(function () {
		var refund_data=JSON.parse('<?php echo ($refund_data); ?>');
		//intelligenceExchange.selectRefundType({id:refund_data.refund.type});
		//加载数据
		if(refund_data.refund.refund_id!=0||is_api==1){
			intelligenceExchange.refund_element_arr['trade_id']=parseInt(refund_data.refund.trade_id);
//			var res_refund_type=refund_data.refund.type;
//			if(res_refund_type!=4){intelligenceExchange.refund_element_arr['tmp_goods_amount']=parseFloat(refund_data.refund.goods_amount).toFixed(4);}
//			else{
//				for(var i in refund_data.refund_data.rows){intelligenceExchange.refund_element_arr['tmp_goods_amount']+=parseFloat(refund_data.refund_data.rows[i].share_price*refund_data.refund_data.rows[i].refund_num);}
//			}
//			intelligenceExchange.refund_element_arr['tmp_direct_amount']=parseFloat(refund_data.refund.direct_refund_amount).toFixed(4);
			//$('#<?php echo ($id_list["form_id"]); ?>').form('filterLoad',refund_data.refund);
			refundArea = new area("<?php echo ($id_list["province"]); ?>", "<?php echo ($id_list["city"]); ?>", "<?php echo ($id_list["district"]); ?>",{province:refund_data.refund.swap_province,city:refund_data.refund.swap_city,district:refund_data.refund.swap_district});
			//$('#<?php echo ($datagrid["refund_order"]["id"]); ?>').datagrid('loadData',refund_data.refund_data);
			var swap_warehouse_id=(refund_data.refund.swap_warehouse_id==0?1:refund_data.refund.swap_warehouse_id);
			var return_form={shop_id:refund_data.refund.shop_id,swap_warehouse_id:swap_warehouse_id};
			//$('#<?php echo ($id_list["return_form"]); ?>').form('filterLoad',return_form);
		}else{
			refundArea = new area("<?php echo ($id_list["province"]); ?>", "<?php echo ($id_list["city"]); ?>", "<?php echo ($id_list["district"]); ?>");
		}
		if(refund_data.return_data!=undefined){
			var return_goods_amount=0;
			for(var i in refund_data.return_data.rows){
				return_goods_amount+=parseFloat(refund_data.return_data.rows[i].retail_price*refund_data.return_data.rows[i].num);
			}
			$('#<?php echo ($datagrid["return_order"]["id"]); ?>').datagrid('loadData',refund_data.return_data);
			intelligenceExchange.refund_element_arr[24].html(parseFloat(return_goods_amount).toFixed(4));
		}
		returnOrder = new ThinDatagrid($('#<?php echo ($datagrid["return_order"]["id"]); ?>'),undefined,false);

		//换出--删除
		returnOrder.remove=function(){
			if (returnOrder.editIndex == undefined){messager.alert('请选择删除的行');return;}
			messager.confirm('确定删除此货品？',function(r){
				if(!r){return;}
				returnOrder.selector.datagrid('selectRow',returnOrder.editIndex);
				var row=returnOrder.selector.datagrid('getSelected');
				intelligenceExchange.refund_element_arr[24].html(parseFloat(parseFloat(intelligenceExchange.refund_element_arr[24].text())-row.retail_price*row.num).toFixed(4));
				returnOrder.selector.datagrid('cancelEdit', returnOrder.editIndex) .datagrid('deleteRow', returnOrder.editIndex);
				returnOrder.editIndex = undefined;
			});
		}
		returnOrder.useCustomer=function(){
			var id=intelligenceExchange.refund_element_arr.trade_id;
			if(id==0){messager.alert('请选择订单');return;}
			$.post('<?php echo U("Trade/RefundManage/getCustomerAddress");?>',{id:id},function(res){
				if(res.status!=undefined&&res.status==1){
					messager.info(res.message);
				}else{
					if(res.receiver_district!=undefined){
						refundArea.selfP.combobox("setValue", res.receiver_province);
						refundArea.selfC.combobox("loadData", refundArea.getArea("city", res.receiver_province, 1));
						refundArea.selfC.combobox("setValue", res.receiver_city);
						refundArea.selfD.combobox("loadData", refundArea.getArea("district", res.receiver_city, 1));
						refundArea.selfD.combobox("setValue", res.receiver_district);
					}
					intelligenceExchange.refund_element_arr[14].textbox('setValue',res.receiver_name);
					if(res.right_flag==0){
						intelligenceExchange.refund_element_arr[15].textbox('setValue',res.receiver_mobile);
						intelligenceExchange.refund_element_arr[16].textbox('setValue',res.receiver_telno);
					}
					intelligenceExchange.refund_element_arr[17].textbox('setValue',res.receiver_address);
				}
			},'JSON')
		}

	},0);
});
//初始化
function initRefundElement(){
	var form_id='<?php echo ($id_list["form_id"]); ?>';
	var dialog_id='<?php echo ($id_list["dialog_id"]); ?>';
	var return_form_id='<?php echo ($id_list["return_form"]); ?>';
	intelligenceExchange.refund_element_arr={

		10:$('#'+form_id+" :input[name='logistics_name']"),

		14:$('#'+form_id+" :input[name='swap_receiver']"),
		15:$('#'+form_id+" :input[name='swap_mobile']"),
		16:$('#'+form_id+" :input[name='swap_telno']"),
		17:$('#'+form_id+" :input[name='swap_address']"),
		18:$('#<?php echo ($id_list["province"]); ?>'),
		19:$('#<?php echo ($id_list["city"]); ?>'),
		20:$('#<?php echo ($id_list["district"]); ?>'),
		21:$('#'+form_id+" :input[name='exchange_amount']"),
//		22:$('#'+form_id+" :input[name='type']"),
		'tmp_goods_amount':0.0000,//临时储存退货金额
		'tmp_direct_amount':0.0000,//临时储存线下金额
		'trade_id':0,
		'flag':false,//标记选择添加了退货
		//换出货品23,24
		23:$('#'+return_form_id+" :input[name='swap_warehouse_id']"),
		24:$('#goods-amount'),
		25:$('#'+form_id+" :input[name='warehouse_id']"),
		26:$('#'+form_id+" :input[name='stockin_pre_no']"),
		'dialog_id':dialog_id,
	};

}
//添加--换出货品
$($(".easyui-menubutton[name='menu-select-return-order']").menubutton().menubutton('options').menu).menu({
	onClick:function(item){
		if (is_api==1) {
			prefix='api_add_return_order';
		}
		switch(item.text){
			case '添加单品':
				// is_api==1?prefix='api_add_return_order':prefix='add_return_order';
				var params={'prefix':prefix,'type':true,'warehouse_id':intelligenceExchange.refund_element_arr[23].combobox('getValue')};
				$('#'+intelligenceExchange.refund_element_arr['dialog_id']).richDialog('goodsSpec', intelligenceExchange.addSpecOrder, params, false);
				break;
			case '添加组合装':
				$('#'+intelligenceExchange.refund_element_arr['dialog_id']).richDialog('goodsSuite', intelligenceExchange.addSuiteOrder, prefix, false);
				break;
		}}
});
intelligenceExchange.addSpecOrder=function(spec_dg_id,sub_dg_id){
	var spec_dg=$('#'+sub_dg_id);
	var spec_rows=spec_dg.datagrid('getRows');
	var show_dg=$('#'+'<?php echo ($datagrid["return_order"]["id"]); ?>');
	$('#'+intelligenceExchange.refund_element_arr['dialog_id']).dialog('close');
	var show_rows=show_dg.datagrid('getRows');
	var sel_row=show_dg.datagrid('getSelected');
	var sel_index=!sel_row?-1:show_dg.datagrid('getRowIndex',sel_row);
	var flag=false; var show_index=0; var return_goods_amount=0;
	for(var i in spec_rows){
//    	spec_rows[i].num=1.0000;
		flag=false;
		for(var x in show_rows){
			if(spec_rows[i].spec_no==show_rows[x].spec_no){
				show_index=show_dg.datagrid('getRowIndex',show_rows[x]);
				show_rows[x].num=parseFloat((parseFloat(show_rows[x].num)+spec_rows[i].num)).toFixed(4);
				show_dg.datagrid('refreshRow',show_index);
				flag=true;
				return_goods_amount+=show_rows[x].retail_price*spec_rows[i].num;
				if(sel_index==show_index){beginEditReturnOrder(sel_index,show_rows[x]);}
			}
		}
		if(!flag){
			spec_rows[i].merchant_no=spec_rows[i].spec_no;
			spec_rows[i].is_suite=0;
			show_dg.datagrid('appendRow',spec_rows[i]);
			return_goods_amount+=spec_rows[i].retail_price*spec_rows[i].num;
		}
	}
	//换出货品实际总售价
	intelligenceExchange.refund_element_arr[24].html(parseFloat(parseFloat(intelligenceExchange.refund_element_arr[24].text())+return_goods_amount).toFixed(4));
}
intelligenceExchange.addSuiteOrder=function(suite_dg_id){
	var suite_dg=$('#'+suite_dg_id);
	var suite_row=suite_dg.datagrid('getSelected');
	var show_dg=$('#'+'<?php echo ($datagrid["return_order"]["id"]); ?>');
	$('#'+intelligenceExchange.refund_element_arr['dialog_id']).dialog('close');
	var show_rows=show_dg.datagrid('getRows');
	var sel_row=show_dg.datagrid('getSelected');
	var sel_index=!sel_row?-1:show_dg.datagrid('getRowIndex',sel_row);
	var flag=false; var show_index=0; var return_goods_amount=0;
	suite_row.num=1.0000; suite_row.is_suite=1;
	for(var x in show_rows){
		if(suite_row.suite_no==show_rows[x].suite_no){
			show_index=show_dg.datagrid('getRowIndex',show_rows[x]);
			show_rows[x].num=parseFloat((parseFloat(show_rows[x].num)+suite_row.num)).toFixed(4);
			show_dg.datagrid('refreshRow',show_index);
			flag=true;
			return_goods_amount+=show_rows[x].retail_price*suite_row.num;
			if(sel_index==show_index){beginEditReturnOrder(sel_index,show_rows[x]);}
			break;
		}
	}
	if(!flag){
		suite_row.goods_name=suite_row.suite_name;
		suite_row.merchant_no=suite_row.suite_no;
		show_dg.datagrid('appendRow',suite_row);
		return_goods_amount+=suite_row.retail_price*suite_row.num;
	}
	//换出货品实际总售价
	intelligenceExchange.refund_element_arr[24].html(parseFloat(parseFloat(intelligenceExchange.refund_element_arr[24].text())+return_goods_amount).toFixed(4));
}
intelligenceExchange.submitEditDialog=function(){
	var form_dg=$('#<?php echo ($id_list["form_id"]); ?>');
	if(!form_dg.form('validate')){ return false;}
	var refund_type=3;
	var refund_id='<?php echo ($id_list["refund_id"]); ?>';
	if((refund_type==3||refund_type==2)&&($('#intelligence_return_stock_in_warehouse').combobox('getValue')==0)){messager.alert('请选择退货入库的仓库');return;}
//	var refund_dg=$('#'+'<?php echo ($datagrid["refund_order"]["id"]); ?>');
	var refund_dg = $('#intelligence_return_stockinorder_datagrid')
//	var refund_rows=refund_dg.datagrid('getRows');
	var refund_rows = refund_dg.datagrid('getRows');
	if(refund_rows.length==0){messager.alert('请选择退款货品');return;}
	if(refund_id==0||intelligenceExchange.refund_element_arr['flag']){
		var trade_order_ids=[];
		for(var i in refund_rows){ trade_order_ids.push(refund_rows[i]['rec_id']); }
		$.post('<?php echo U("Trade/RefundManage/checkIsRefund");?>',{ids:JSON.stringify(trade_order_ids)},function(res){
			if(!res.status){
				messager.confirm('该订单下已存在退换单，是否继续？',function(r){
					if(!r){
//						$('#'+intelligenceExchange.params.search.form_id).form('filterLoad',{'search[trade_no]':intelligenceExchange.refund_element_arr[4].textbox('getValue')});
//						intelligenceExchange.submitSearchForm();
						//$('#'+intelligenceExchange.refund_element_arr['dialog_id']).dialog('close');
						return;
					}else{
						intelligenceExchange.submitSalesRefund(refund_id,refund_type,form_dg,refund_dg,refund_rows,0);
					}
				});
			}else{
				//intelligenceExchange.submitSalesRefund(refund_id,refund_type,form_dg,refund_dg,refund_rows,0);
			}
		},'JSON')
	}else{
		//intelligenceExchange.submitSalesRefund(refund_id,refund_type,form_dg,refund_dg,refund_rows,0);
	}
}
//退换单--submit
intelligenceExchange.submitSalesRefund=function(refund_id,refund_type,form_dg,refund_dg,refund_rows,test){
	var data={};
	var refund_data=JSON.parse('<?php echo ($refund_data); ?>');
	var res_refund_type=refund_data.refund.type;
	if(refund_type==3){
		if(intelligenceExchange.refund_element_arr[15].textbox('getValue')==''&&intelligenceExchange.refund_element_arr[16].textbox('getValue')==''){ messager.alert('手机和固话至少有一个不能为空');return; }
		var return_dg=$('#'+'<?php echo ($datagrid["return_order"]["id"]); ?>');
		var return_rows=return_dg.datagrid('getRows');
		if(return_rows.length==0){messager.alert('请选择换出货品');return;}
		var return_form=$('#<?php echo ($id_list["return_form"]); ?>').form('get');
		return_form['goods_return_count']=return_rows.length;
		data['return_info'] =JSON.stringify(return_form);
		if(refund_id==0){
			data['return_order']=JSON.stringify(return_rows);
		}else{
			var return_edit={};
			return_edit['add']=return_dg.datagrid('getChanges','inserted');
			return_edit['update']=return_dg.datagrid('getChanges','updated');
			return_edit['delete']=return_dg.datagrid('getChanges','deleted');
			data['return_order']=JSON.stringify(return_edit);
		}
	}else if(res_refund_type==3){
		var return_dg=$('#'+'<?php echo ($datagrid["return_order"]["id"]); ?>');
		var return_edit={};
		return_edit['delete']=return_dg.datagrid('getRows');
		data['return_order']=JSON.stringify(return_edit);
	}
	//var refund_dg=$('#'+'<?php echo ($datagrid["refund_order"]["id"]); ?>');
	var refund_rows=refund_dg.datagrid('getRows');
	if(refund_rows.length==0){messager.alert('请选择退款货品');return;}
	for(var i=0;i<refund_rows.length;i++){
		if(refund_rows[i]['gift_type']>0&&test==0){
			messager.confirm('退回货品中存在赠品，是否继续？',function(r){
				if(r){
					intelligenceExchange.submitSalesRefund(refund_id,refund_type,form_dg,refund_dg,refund_rows,1);
				}else{return;}
			});
			return;
		}
	}
	if(refund_id==0||intelligenceExchange.refund_element_arr['flag']){
		data['refund_order']=JSON.stringify(refund_rows);
	}else{
		var refund_edit={};
		//refund_edit['add']=refund_dg.datagrid('getChanges','inserted');
		refund_edit['update']=refund_dg.datagrid('getChanges','updated');
		refund_edit['delete']=refund_dg.datagrid('getChanges','deleted');
		data['refund_order']=JSON.stringify(refund_edit);
	}
	//intelligenceExchange.disableControl(4,9,'enable');
	var refund_form=form_dg.form('get');
	var refund_form_page = $('#intelligence_return_stockinorder_form').form('get');
	console.log(refund_form);
	console.log(refund_form_page);
	return;
	if(refund_type==3){refund_form['exchange_amount']=intelligenceExchange.refund_element_arr[21].numberbox('getValue'); var area=refundArea.getText(); refund_form['swap_area']=area['province']+' '+area['city']+' '+area['district']; }
	if(refund_id!=0&&intelligenceExchange.refund_element_arr['flag']){ refund_form['flag']=true; }
	refund_form['goods_refund_count']=refund_rows.length;
	refund_form['warehouse_id']=$('#intelligence_return_stock_in_warehouse').combobox('getValue');
	if(refund_type!=4&&refund_type!=5){refund_form['logistics_name']=intelligenceExchange.refund_element_arr[10].combobox('getText');}
	refund_form['trade_id']=intelligenceExchange.refund_element_arr['trade_id'];
	data['info']=JSON.stringify(refund_form);
	//intelligenceExchange.disableControl(4,9,'disable');
	data['id']=refund_id;
	data['is_api']=is_api;
	$.post('<?php echo U("Stock/StockInOrder/exchangeRefund");?>',data,function(res){
		if(res.status==0){
			messager.alert(res.info);
		}else if(res.status==2){
			if (is_api==1) {
				$('#'+originalRefund.params.edit.id).dialog('close');
				$.fn.richDialog("response", res.fail, 'refundmanage');
				originalRefund.refresh();
			}else if(is_api==2){
				$('#add_refund').dialog('close');
				$.fn.richDialog("response", res.fail, 'refundmanage');
				messager.confirm('是否查看该退换单?',function(r){
					if (r) {
						$("#response_dialog").dialog('close');
						open_menu('退换管理','<?php echo U("Trade/RefundManage/getSalesRefundList");?>');
					}
				});
			}else{
				$('#'+salesRefund.params.edit.id).dialog('close');
				$.fn.richDialog("response", res.fail, 'refundmanage');
				salesRefund.refresh();
			}

		}else{
			if(is_api==1){
				$('#'+originalRefund.params.edit.id).dialog('close');
				originalRefund.refresh();
			}else if(is_api==2){
				$('#add_refund').dialog('close');
				messager.confirm('保存成功!是否查看该退换单?',function(r){
					if (r) {
						$('#add_refund').dialog('close');
						open_menu('退换管理','<?php echo U("Trade/RefundManage/getSalesRefundList");?>');
					}
				});
			}else{
				$('#'+salesRefund.params.edit.id).dialog('close');
				salesRefund.refresh();
			}
		}
	},"JSON");
}
</script>