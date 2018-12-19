<?php if (!defined('THINK_PATH')) exit();?>
<div class="easyui-layout" data-options="fit:true,border:false" style="height:350px;width:850px;overflow:hidden;">
<div data-options="region:'center'" style="padding:1px;background:#eee;overflow:hidden;">
<form  id="<?php echo ($id_list["form_id"]); ?>">
	<div class="form-div">
		<label>原始单号：</label><input class="easyui-textbox txt" type="text" name="tid" style="width:324px;" data-options="editable:false,buttonText:'...'"/>
		<label>系统订单：</label><input class="easyui-textbox txt" type="text" name="trade_no"  data-options="required:true,disabled:true"/>
		<label>退换单号：</label><input class="easyui-textbox txt" type="text" name="refund_no" data-options="disabled:true,required:true"/>
		<label>退货金额：</label><input class="easyui-numberbox txt" type="text" name="goods_amount" value="0.0000" data-options="disabled:true,min:0,precision:4,required:true"/>
	</div>
	<div class="form-div">
		<label>买家昵称：</label><input class="easyui-textbox txt" type="text" name="buyer_nick"  data-options="required:true,disabled:true"/>
		<label>退换类别：</label><input class="easyui-combobox txt" type="text" name="type" data-options="editable:false,required:true,panelHeight:'auto',valueField:'id',textField:'name',data:formatter.get_data('refund_type_select','def','3'),onSelect:addEditRefund.selectRefundType"/>
		<label>退换原因：</label><select class="easyui-combobox sel" type="text" name="reason_id"  data-options="editable:false,required:true"><option value="0">无</option><?php if(is_array($list["reason"])): $i = 0; $__LIST__ = $list["reason"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?></select>
		<label>原始退款单号：</label><input class="easyui-textbox txt" type="text" name="src_no" style="width:105px;" data-options="disabled:true"/>
		<label>退款金额：</label><input class="easyui-numberbox txt" type="text" name="refund_amount" value="0.0000" data-options="disabled:true,precision:4,required:true"/>
	</div>
	<div class="form-div">
		<label>买家账号：</label><input class="easyui-textbox txt" type="text" name="pay_account" data-options=""/>
		<label>金额流向：</label><input class="easyui-combobox txt" type="text" name="flow_type"  data-options="editable:false,required:true,panelHeight:'auto',valueField:'id',textField:'name',data:formatter.get_data('flow_type'),onSelect:addEditRefund.selectFlowTo"/>
		<label>平台退款：</label><input class="easyui-numberbox txt" type="text" name="guarante_refund_amount" value="0.0000" data-options="precision:4,required:true"/>
		<label>线下退款：</label><input class="easyui-numberbox txt" type="text" name="direct_refund_amount" value="0.0000" data-options="precision:4,required:true"/>
		<label>付款方式：</label><input class="easyui-combobox txt" name="pay_method" data-options="panelHeight:'auto',valueField:'id',textField:'name',data:formatter.get_data('pay_method','def')"/>
	</div>
	<div class="form-div">
		<label>退货入库：</label><select class="easyui-combobox sel sel-disabled" name="warehouse_id" data-options="editable:false,required:true"><?php if(is_array($list["warehouse"])): $i = 0; $__LIST__ = $list["warehouse"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?></select>
		<label>关联预入库单：</label><input  	class="easyui-textbox 	txt" 	name="stockin_pre_no" 	data-options="buttonText: '...'"style="width: 300px"  />
		<label>备　　注：</label><input class="easyui-textbox txt" type="text" name="remark" style="width:324px;" data-options=""/>
		<!-- <label>换出金额：</label><input class="easyui-numberbox txt" type="text" name="refund_amount" value="0.00" data-options="min:0,precision:4,disabled:true,required:true"/> -->
	</div>

<div id="<?php echo ($id_list["more_content_logistics"]); ?>">
<hr style="margin-top: 2px;border:none;border-top:2px dotted #95B8E7;">
<span style="color:#0E2D5F;"><label>退回货品物流信息：</label></span>
<div class="form-div">
<label>物流公司：</label><input class="easyui-combobox txt" type="text" name="logistics_name"  data-options="editable:false,required:true,valueField:'name',textField:'name',data:formatter.get_data('logistics_type','def')"/>
<label>物流单号：</label><input class="easyui-textbox txt" type="text" name="logistics_no"/>
<label>标　　记：</label><select class="easyui-combobox sel" name="flag_id" data-options="editable:false,required:true"><option value="0">无</option><?php if(is_array($list["flags"])): $i = 0; $__LIST__ = $list["flags"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?></select>
<label>邮　　费：</label><input class="easyui-numberbox txt" name="post_amount" type="text" value="0.00" data-options="min:0,precision:4,required:true"/>
</div>
</div>
<div id="<?php echo ($id_list["more_content_info"]); ?>">
<hr style="margin-top: 2px;border:none;border-top:2px dotted #95B8E7;">
<span style="color:#0E2D5F;"><label id="more_info_title">换出货品物流信息：</label></span>
<a href="javascript:void(0)" class="easyui-linkbutton" onclick="addEditRefund.useCustomer()">使用原地址信息</a>
<span style="color:red;margin-left:60px;" id="more_info_goods-amount"><label>换出货品总售价：</label><label id="goods-amount" style="margin-right: 10px;">0.0000</label></span>
<span style="color:red;margin-left:40px;display: none;" id="is_checknumber_info">*暂无查看和修改手机、固话的权限！</span>
<div class="form-div">
<label>姓　　名：</label><input class="easyui-textbox txt" name="swap_receiver" type="text" />
<label>手　　机：</label><input class="easyui-textbox txt" name="swap_mobile" type="text" data-options="validType:'mobile'"/>
<label>固　　话：</label><input class="easyui-textbox txt" name="swap_telno" type="text" data-options="validType:'mobileAndTel'"/>
 <label>换出金额：</label><input class="easyui-numberbox txt" type="text" name="exchange_amount" value="0.00" data-options="disabled:true,precision:4,required:true"/>
</div>
<div class="form-div">
<label>省　　份：</label><input id="<?php echo ($id_list["province"]); ?>" class="easyui-combobox txt" name="swap_province"/>
<label>城　　市：</label><input id="<?php echo ($id_list["city"]); ?>" class="easyui-combobox txt" name="swap_city"/>
<label>区　　县：</label><input id="<?php echo ($id_list["district"]); ?>" class="easyui-combobox txt" name="swap_district"/>
<label>详细地址：</label><input class="easyui-textbox" style="width:324px;" name="swap_address"  type="text"/>
</div>
</div>
</form>
</div>
<div data-options="region:'south',split:true" style="height:160px;background:#eee;overflow:hidden;">
<div class="easyui-tabs" data-options="fit:true,border:false,plain:true" id="<?php echo ($id_list["tab_container"]); ?>"> 
<div title="<?php echo ($datagrid["refund_order"]["title"]); ?>">
<div id="<?php echo ($id_list["toolbar_refund"]); ?>">
<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-reload',plain:true" onclick="refundOrder.exchangeRefundGoods()">更换货品</a>
<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-remove',plain:true" onclick="refundOrder.remove()">删除</a>
</div>
<table id="<?php echo ($datagrid["refund_order"]["id"]); ?>" class="easyui-datagrid" data-options='<?php $dataOptions = array_merge(array ( 'border' => false, 'fit' => true, 'fitColumns' => true, 'rownumbers' => true, 'singleSelect' => true, 'pagination' => true, 'pageList' => array ( 0 => 20, 1 => 50, 2 => 100, ), 'pageSize' => 20, ), $datagrid["refund_order"]["options"]);if(isset($dataOptions['toolbar']) && substr($dataOptions['toolbar'],0,1) != '#'): unset($dataOptions['toolbar']); endif;if(isset($dataOptions['methods'])): unset($dataOptions['methods']);endif; echo trim(json_encode($dataOptions), '{}[]').((isset($datagrid["refund_order"]["options"]['toolbar']) && substr($datagrid["refund_order"]["options"]['toolbar'],0,1) != '#')?',"toolbar":'.$datagrid["refund_order"]["options"]['toolbar']:null).(isset($datagrid["refund_order"]["options"]['methods'])? ','.$datagrid["refund_order"]["options"]['methods']:null); ?>' style="<?php echo ($datagrid["refund_order"]["style"]); ?>" ><thead><tr><?php if(is_array($datagrid["refund_order"]["fields"])):foreach ($datagrid["refund_order"]["fields"] as $key=>$arr):if(isset($arr['formatter'])):unset($arr['formatter']);endif;if(isset($arr['methods'])):unset($arr['methods']);endif;if(isset($arr['editor'])):unset($arr['editor']);endif;echo "<th data-options='".trim(json_encode($arr), '{}[]').(isset($datagrid["refund_order"]["fields"][$key]['formatter'])?",\"formatter\":".$datagrid["refund_order"]["fields"][$key]['formatter']:null).(isset($datagrid["refund_order"]["fields"][$key]['editor'])?",\"editor\":".$datagrid["refund_order"]["fields"][$key]['editor']:null).(isset($datagrid["refund_order"]["fields"][$key]['methods'])?",".$datagrid["refund_order"]["fields"][$key]['methods']:null)."'>".$key."</th>";endforeach;endif; ?></tr></thead></table>
</div>
<div title="<?php echo ($datagrid["return_order"]["title"]); ?>">
<div id="<?php echo ($id_list["toolbar_return"]); ?>">
<form  id="<?php echo ($id_list["return_form"]); ?>">
<span style="margin-right: 10px;"><label>店铺：</label><select class="easyui-combobox sel sel-disabled" name="shop_id" data-options="editable:false,required:true"><?php if(is_array($list["shop"])): $i = 0; $__LIST__ = $list["shop"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?></select></span>
<span style="margin-right: 10px;"><label>仓库：</label><select class="easyui-combobox sel sel-disabled" name="swap_warehouse_id" data-options="editable:false,required:true"><?php if(is_array($list["warehouse"])): $i = 0; $__LIST__ = $list["warehouse"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?></select></span>
<a href="#" name="menu-select-return-order" class="easyui-menubutton" data-options="menu:'#mbut-select-return-order'">添加货品</a>
<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-remove',plain:true" onclick="returnOrder.remove()">删除</a>
</form>
</div>
<div id="mbut-select-return-order"><div>添加单品</div><div>添加组合装</div></div>
<table id="<?php echo ($datagrid["return_order"]["id"]); ?>" class="easyui-datagrid" data-options='<?php $dataOptions = array_merge(array ( 'border' => false, 'fit' => true, 'fitColumns' => true, 'rownumbers' => true, 'singleSelect' => true, 'pagination' => true, 'pageList' => array ( 0 => 20, 1 => 50, 2 => 100, ), 'pageSize' => 20, ), $datagrid["return_order"]["options"]);if(isset($dataOptions['toolbar']) && substr($dataOptions['toolbar'],0,1) != '#'): unset($dataOptions['toolbar']); endif;if(isset($dataOptions['methods'])): unset($dataOptions['methods']);endif; echo trim(json_encode($dataOptions), '{}[]').((isset($datagrid["return_order"]["options"]['toolbar']) && substr($datagrid["return_order"]["options"]['toolbar'],0,1) != '#')?',"toolbar":'.$datagrid["return_order"]["options"]['toolbar']:null).(isset($datagrid["return_order"]["options"]['methods'])? ','.$datagrid["return_order"]["options"]['methods']:null); ?>' style="<?php echo ($datagrid["return_order"]["style"]); ?>" ><thead><tr><?php if(is_array($datagrid["return_order"]["fields"])):foreach ($datagrid["return_order"]["fields"] as $key=>$arr):if(isset($arr['formatter'])):unset($arr['formatter']);endif;if(isset($arr['methods'])):unset($arr['methods']);endif;if(isset($arr['editor'])):unset($arr['editor']);endif;echo "<th data-options='".trim(json_encode($arr), '{}[]').(isset($datagrid["return_order"]["fields"][$key]['formatter'])?",\"formatter\":".$datagrid["return_order"]["fields"][$key]['formatter']:null).(isset($datagrid["return_order"]["fields"][$key]['editor'])?",\"editor\":".$datagrid["return_order"]["fields"][$key]['editor']:null).(isset($datagrid["return_order"]["fields"][$key]['methods'])?",".$datagrid["return_order"]["fields"][$key]['methods']:null)."'>".$key."</th>";endforeach;endif; ?></tr></thead></table>
</div>
</div>
</div>
</div>
<script type="text/javascript">
//# sourceURL=edit.refund.js
var is_api=('<?php echo ($is_api); ?>');
$(function(){
	$('#<?php echo ($datagrid["refund_order"]["id"]); ?>').datagrid().datagrid('enableCellEditing');
	$('#<?php echo ($datagrid["return_order"]["id"]); ?>').datagrid().datagrid('enableCellEditing');
	addEditRefund = new ThinDatagrid($('#<?php echo ($id_list["id_datagrid_refund"]); ?>'),undefined,false);
	initRefundElement();
	addEditRefund.refund_element_arr[26].textbox().textbox('options').onClickButton=function(){addEditRefund.addSpOrder();};
	addEditRefund.refund_element_arr[3].textbox().textbox('options').onClickButton=function(){$.fn.richDialog('salesTrade', addEditRefund.selectRefundOrder); }
	addEditRefund.refund_element_arr[1].numberbox().numberbox('options').onChange=function(newValue,oldValue){
		addEditRefund.refund_element_arr[8].numberbox('setValue',parseFloat(newValue)+parseFloat(addEditRefund.refund_element_arr[2].numberbox('getValue')));
		var refund_type=addEditRefund.refund_element_arr[22].combobox('getValue');
		var flow_type=addEditRefund.refund_element_arr[0].combobox('getValue');
		if(refund_type==3){
			if(flow_type==1){addEditRefund.refund_element_arr[21].numberbox('setValue',parseFloat(addEditRefund.refund_element_arr['tmp_goods_amount'])-parseFloat(newValue)-parseFloat(addEditRefund.refund_element_arr[2].numberbox('getValue'))-parseFloat(addEditRefund.refund_element_arr[13].numberbox('getValue')));}
			else{addEditRefund.refund_element_arr[21].numberbox('setValue',parseFloat(addEditRefund.refund_element_arr['tmp_goods_amount'])+parseFloat(newValue)+parseFloat(addEditRefund.refund_element_arr[2].numberbox('getValue'))-parseFloat(addEditRefund.refund_element_arr[13].numberbox('getValue')));}
		}
	}
	addEditRefund.refund_element_arr[2].numberbox().numberbox('options').onChange=function(newValue,oldValue){
		addEditRefund.refund_element_arr[8].numberbox('setValue',parseFloat(newValue)+parseFloat(addEditRefund.refund_element_arr[1].numberbox('getValue')));
		var refund_type=addEditRefund.refund_element_arr[22].combobox('getValue');
		var flow_type=addEditRefund.refund_element_arr[0].combobox('getValue');
		if(refund_type==3){
			if(flow_type==1){addEditRefund.refund_element_arr[21].numberbox('setValue',parseFloat(addEditRefund.refund_element_arr['tmp_goods_amount'])-parseFloat(newValue)-parseFloat(addEditRefund.refund_element_arr[1].numberbox('getValue'))-parseFloat(addEditRefund.refund_element_arr[13].numberbox('getValue')));}
			else{addEditRefund.refund_element_arr[21].numberbox('setValue',parseFloat(addEditRefund.refund_element_arr['tmp_goods_amount'])+parseFloat(newValue)+parseFloat(addEditRefund.refund_element_arr[1].numberbox('getValue'))-parseFloat(addEditRefund.refund_element_arr[13].numberbox('getValue')));}
		}
	}
	addEditRefund.refund_element_arr[13].numberbox().numberbox('options').onChange=function(newValue,oldValue){
		var refund_type=addEditRefund.refund_element_arr[22].combobox('getValue');
		var flow_type=addEditRefund.refund_element_arr[0].combobox('getValue');
		if(refund_type==3){
			if(flow_type==1){addEditRefund.refund_element_arr[21].numberbox('setValue',parseFloat(addEditRefund.refund_element_arr['tmp_goods_amount'])-parseFloat(newValue)-parseFloat(addEditRefund.refund_element_arr[1].numberbox('getValue'))-parseFloat(addEditRefund.refund_element_arr[2].numberbox('getValue')));}
			else{addEditRefund.refund_element_arr[21].numberbox('setValue',parseFloat(addEditRefund.refund_element_arr['tmp_goods_amount'])-parseFloat(newValue)+parseFloat(addEditRefund.refund_element_arr[1].numberbox('getValue'))+parseFloat(addEditRefund.refund_element_arr[2].numberbox('getValue')));}
		}
	}
	setTimeout(function () {
		var refund_data=JSON.parse('<?php echo ($refund_data); ?>');
		addEditRefund.selectRefundType({id:refund_data.refund.type});
		//编辑--新建 加载数据
		if(refund_data.refund.refund_id!=0||is_api==1||is_api==2){
			addEditRefund.refund_element_arr['trade_id']=parseInt(refund_data.refund.trade_id);
			var res_refund_type=refund_data.refund.type;
			if(res_refund_type!=4){addEditRefund.refund_element_arr['tmp_goods_amount']=parseFloat(refund_data.refund.goods_amount).toFixed(4);}
			else{
				for(var i in refund_data.refund_data.rows){addEditRefund.refund_element_arr['tmp_goods_amount']+=parseFloat(refund_data.refund_data.rows[i].share_price*refund_data.refund_data.rows[i].refund_num);}
			}
			addEditRefund.refund_element_arr['tmp_direct_amount']=parseFloat(refund_data.refund.direct_refund_amount).toFixed(4);
			$('#<?php echo ($id_list["form_id"]); ?>').form('filterLoad',refund_data.refund);
			refundArea = new area("<?php echo ($id_list["province"]); ?>", "<?php echo ($id_list["city"]); ?>", "<?php echo ($id_list["district"]); ?>",{province:refund_data.refund.swap_province,city:refund_data.refund.swap_city,district:refund_data.refund.swap_district});
			$('#<?php echo ($datagrid["refund_order"]["id"]); ?>').datagrid('loadData',refund_data.refund_data);
			var swap_warehouse_id=(refund_data.refund.swap_warehouse_id==0?1:refund_data.refund.swap_warehouse_id);
			var return_form={shop_id:refund_data.refund.shop_id,swap_warehouse_id:swap_warehouse_id};
			$('#<?php echo ($id_list["return_form"]); ?>').form('filterLoad',return_form);
		}else{
			refundArea = new area("<?php echo ($id_list["province"]); ?>", "<?php echo ($id_list["city"]); ?>", "<?php echo ($id_list["district"]); ?>");
		}
		if(refund_data.return_data!=undefined){
			var return_goods_amount=0;
			for(var i in refund_data.return_data.rows){
				return_goods_amount+=parseFloat(refund_data.return_data.rows[i].retail_price*refund_data.return_data.rows[i].num);
			}
			$('#<?php echo ($datagrid["return_order"]["id"]); ?>').datagrid('loadData',refund_data.return_data);
			addEditRefund.refund_element_arr[24].html(parseFloat(return_goods_amount).toFixed(4));
		}
		refundOrder = new ThinDatagrid($('#<?php echo ($datagrid["refund_order"]["id"]); ?>'),undefined,false);
		returnOrder = new ThinDatagrid($('#<?php echo ($datagrid["return_order"]["id"]); ?>'),undefined,false);
		//退货--删除
		refundOrder.remove=function(){
			if (refundOrder.editIndex == undefined){messager.alert('请选择删除的行');return;}
			messager.confirm('确定删除此货品？',function(r){
				if(!r){return;}
				refundOrder.selector.datagrid('selectRow',refundOrder.editIndex);
				var row=refundOrder.selector.datagrid('getSelected');
				var goods_amount=parseFloat(addEditRefund.refund_element_arr['tmp_goods_amount']-row.original_price*row.refund_num).toFixed(4);
				addEditRefund.refund_element_arr['tmp_goods_amount']=parseFloat(goods_amount).toFixed(4);
				if(row.platform_id==0){addEditRefund.refund_element_arr['tmp_direct_amount']=parseFloat(addEditRefund.refund_element_arr['tmp_direct_amount']-row.original_price*row.refund_num).toFixed(4);}
				var refund_type=addEditRefund.refund_element_arr[22].combobox('getValue');
				if(refund_type==2){addEditRefund.refund_element_arr[7].numberbox('setValue',goods_amount);}
				else if(refund_type==3){addEditRefund.refund_element_arr[7].numberbox('setValue',goods_amount);addEditRefund.refund_element_arr[21].numberbox('setValue',goods_amount);}
				refundOrder.selector.datagrid('cancelEdit', refundOrder.editIndex) .datagrid('deleteRow', refundOrder.editIndex);
				refundOrder.editIndex = undefined;
			});
		}
		// 更换退回货品
		refundOrder.exchangeRefundGoods=function(){
			if (refundOrder.editIndex == undefined){messager.alert('请选择需要更换的货品');return;}
			messager.confirm('确定更换退回货品吗？',function(r){
				if (!r) {return;};	
				refundOrder.selector.datagrid('selectRow',refundOrder.editIndex);
				var row=refundOrder.selector.datagrid('getSelected');		
				var dg = $('#exchangeRefundGoods'); 
				var url='<?php echo ($id_list["url_exchange"]); ?>';
				url += url.indexOf('?') != -1 ? '&id='+row.rec_id : '?id='+row.rec_id;
				var buttons=[ {text:'确定',handler:function(){submitExchangeRefundDialog();}}, {text:'取消',handler:function(){dg.dialog('close')}} ];
                dg.dialog({
                        title:'更换退回货品',
                        iconCls:'icon-save',
                        width:764,
                        height:560,
                        href:url,
                        closed:false,
                        inline:true,
                        modal:true,
                        buttons:buttons
                    });				
			});
		}
		//换出--删除
		returnOrder.remove=function(){
			if (returnOrder.editIndex == undefined){messager.alert('请选择删除的行');return;}
			messager.confirm('确定删除此货品？',function(r){
				if(!r){return;}
				returnOrder.selector.datagrid('selectRow',returnOrder.editIndex);
				var row=returnOrder.selector.datagrid('getSelected');
				addEditRefund.refund_element_arr[24].html(parseFloat(parseFloat(addEditRefund.refund_element_arr[24].text())-row.retail_price*row.num).toFixed(4));
				returnOrder.selector.datagrid('cancelEdit', returnOrder.editIndex) .datagrid('deleteRow', returnOrder.editIndex);
				returnOrder.editIndex = undefined;
			});
		}
		
	},0);
});
//元素--初始化
function initRefundElement(){
	var form_id='<?php echo ($id_list["form_id"]); ?>';
	var dialog_id='<?php echo ($id_list["dialog_id"]); ?>';
	var return_form_id='<?php echo ($id_list["return_form"]); ?>';
	addEditRefund.refund_element_arr={
			0:$('#'+form_id+" :input[name='flow_type']"),
			1:$('#'+form_id+" :input[name='guarante_refund_amount']"),
			2:$('#'+form_id+" :input[name='direct_refund_amount']"),
			3:$('#'+form_id+" :input[name='tid']"),
			
			4:$('#'+form_id+" :input[name='trade_no']"),
			5:$('#'+form_id+" :input[name='buyer_nick']"),
			6:$('#'+form_id+" :input[name='refund_no']"),
			7:$('#'+form_id+" :input[name='goods_amount']"),
			8:$('#'+form_id+" :input[name='refund_amount']"),
			9:$('#'+form_id+" :input[name='src_no']"),
			
			10:$('#'+form_id+" :input[name='logistics_name']"),
			11:$('#'+form_id+" :input[name='logistics_no']"),
			12:$('#'+form_id+" :input[name='flag_id']"),
			13:$('#'+form_id+" :input[name='post_amount']"),
			
			14:$('#'+form_id+" :input[name='swap_receiver']"),
			15:$('#'+form_id+" :input[name='swap_mobile']"),
			16:$('#'+form_id+" :input[name='swap_telno']"),
			17:$('#'+form_id+" :input[name='swap_address']"),
			18:$('#<?php echo ($id_list["province"]); ?>'),
			19:$('#<?php echo ($id_list["city"]); ?>'),
			20:$('#<?php echo ($id_list["district"]); ?>'),
			21:$('#'+form_id+" :input[name='exchange_amount']"),
			22:$('#'+form_id+" :input[name='type']"),
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
//refund_type选择事件
addEditRefund.selectRefundType=function(record){
	var content_logistics='<?php echo ($id_list["more_content_logistics"]); ?>';
	var content_info='<?php echo ($id_list["more_content_info"]); ?>';
	var refund_id='<?php echo ($id_list["refund_id"]); ?>';
	var right_flag='<?php echo ($id_list["right_flag"]); ?>';
	var type='<?php echo ($id_list["type"]); ?>';
	switch(record.id){
	case '2'://退货
		$("#"+content_logistics).show();
		$("#"+content_info).hide();
		addEditRefund.refund_element_arr[0].combobox('disable');
		addEditRefund.refund_element_arr[7].numberbox('setValue',addEditRefund.refund_element_arr['tmp_goods_amount']);
		addEditRefund.refund_element_arr[8].numberbox('setValue',addEditRefund.refund_element_arr['tmp_goods_amount']);
		addEditRefund.refund_element_arr[2].numberbox('setValue',addEditRefund.refund_element_arr['tmp_direct_amount']);
		addEditRefund.refund_element_arr[1].numberbox('setValue',addEditRefund.refund_element_arr['tmp_goods_amount']-addEditRefund.refund_element_arr['tmp_direct_amount']);
		addEditRefund.disableControl(10,13,'enable');
		addEditRefund.disableControl(25,25,'enable');
		addEditRefund.disableControl(14,20,'disable');
		addEditRefund.refund_element_arr[26].textbox('setValue','');
		addEditRefund.refund_element_arr[26].textbox('enable');

		addEditRefund.refund_element_arr[15].textbox({disabled:true,validType:''});
		addEditRefund.refund_element_arr[16].textbox({disabled:true,validType:''});
		$(".easyui-menubutton[name='menu-select-return-order']").menubutton('disable');
		break;
	case '3'://换货
		$("#"+content_logistics).show();
		$("#"+content_info).show();
		addEditRefund.refund_element_arr[0].combobox('enable');
		addEditRefund.refund_element_arr[7].numberbox('setValue',addEditRefund.refund_element_arr['tmp_goods_amount']);
		addEditRefund.refund_element_arr[8].numberbox('setValue',0.0000);
		addEditRefund.refund_element_arr[21].numberbox('setValue',addEditRefund.refund_element_arr['tmp_goods_amount']);
		addEditRefund.refund_element_arr[2].numberbox('setValue',0.0000);
		addEditRefund.refund_element_arr[1].numberbox('setValue',0.0000);
		addEditRefund.disableControl(10,20,'enable');
		addEditRefund.disableControl(25,25,'enable');
		addEditRefund.refund_element_arr[26].textbox('setValue','');
		addEditRefund.refund_element_arr[26].textbox('enable');
		if(right_flag==0){
			addEditRefund.refund_element_arr[15].textbox({disabled:true,validType:'mobile'});
			addEditRefund.refund_element_arr[16].textbox({disabled:true,validType:'mobileAndTel'});
			$("#is_checknumber_info").show();
			//if(type==3){ $("#is_checknumber_info").show();}
		}else{
			addEditRefund.refund_element_arr[15].textbox({disabled:false,validType:'mobile'});
			addEditRefund.refund_element_arr[16].textbox({disabled:false,validType:'mobileAndTel'});
			$("#is_checknumber_info").hide();
		}
		$(".easyui-menubutton[name='menu-select-return-order']").menubutton('enable');
		$("#more_info_title").html("换出货品物流信息");$("#more_info_goods-amount").show();
		break;
	case '4'://退款不退货
		$("#"+content_logistics).hide();
		$("#"+content_info).hide();
		addEditRefund.refund_element_arr[0].combobox('disable');
		addEditRefund.refund_element_arr[7].numberbox('setValue',0.0000);
		addEditRefund.refund_element_arr[8].numberbox('setValue',addEditRefund.refund_element_arr['tmp_goods_amount']);
		addEditRefund.refund_element_arr[2].numberbox('setValue',addEditRefund.refund_element_arr['tmp_direct_amount']);
		addEditRefund.refund_element_arr[1].numberbox('setValue',addEditRefund.refund_element_arr['tmp_goods_amount']-addEditRefund.refund_element_arr['tmp_direct_amount']);
		addEditRefund.refund_element_arr[26].textbox('setValue','');
		addEditRefund.refund_element_arr[26].textbox('disable');
		addEditRefund.disableControl(10,20,'disable');
		addEditRefund.disableControl(25,25,'disable');
		addEditRefund.refund_element_arr[15].textbox({disabled:true,validType:''});
		addEditRefund.refund_element_arr[16].textbox({disabled:true,validType:''});
		$(".easyui-menubutton[name='menu-select-return-order']").menubutton('disable');
		break;
	case '5'://破损补发
		$("#"+content_logistics).hide();
		$("#"+content_info).show();
		addEditRefund.refund_element_arr[0].combobox('disable');
		addEditRefund.refund_element_arr[7].numberbox('setValue',0.0000);
		addEditRefund.refund_element_arr[8].numberbox('setValue',0.0000);
		addEditRefund.refund_element_arr[2].numberbox('setValue',0.0000);
		addEditRefund.refund_element_arr[1].numberbox('setValue',0.0000);
		addEditRefund.refund_element_arr[26].textbox('setValue','');
		addEditRefund.refund_element_arr[26].textbox('disable');
		addEditRefund.disableControl(10,13,'disable');
		addEditRefund.refund_element_arr[21].numberbox('setValue',0.0000);
		addEditRefund.disableControl(25,25,'disable');
		addEditRefund.disableControl(14,20,'enable');

		if(right_flag==0){
			addEditRefund.refund_element_arr[15].textbox({disabled:true,validType:'mobile'});
			addEditRefund.refund_element_arr[16].textbox({disabled:true,validType:'mobileAndTel'});
			$("#is_checknumber_info").show();
			//if(type==3){ $("#is_checknumber_info").show();}
		}else{
			addEditRefund.refund_element_arr[15].textbox({disabled:false,validType:'mobile'});
			addEditRefund.refund_element_arr[16].textbox({disabled:false,validType:'mobileAndTel'});
			$("#is_checknumber_info").hide();
		}
		$(".easyui-menubutton[name='menu-select-return-order']").menubutton('enable');
		$("#more_info_title").html("补发货品物流信息");$("#more_info_goods-amount").hide();
	    break;

	}
}
addEditRefund.selectFlowTo=function(record){
	switch(record.id){
	case '1':addEditRefund.disableControl(1,1,'enable');
		addEditRefund.refund_element_arr[21].numberbox('setValue',parseFloat(addEditRefund.refund_element_arr['tmp_goods_amount'])-parseFloat(addEditRefund.refund_element_arr[1].numberbox('getValue'))-parseFloat(addEditRefund.refund_element_arr[2].numberbox('getValue'))-parseFloat(addEditRefund.refund_element_arr[13].numberbox('getValue')));
		break;
	case '2':addEditRefund.disableControl(1,1,'disable');
		addEditRefund.refund_element_arr[21].numberbox('setValue',parseFloat(addEditRefund.refund_element_arr['tmp_goods_amount'])+parseFloat(addEditRefund.refund_element_arr[1].numberbox('getValue'))+parseFloat(addEditRefund.refund_element_arr[2].numberbox('getValue'))-parseFloat(addEditRefund.refund_element_arr[13].numberbox('getValue')));
		break;
	}
}
//禁用事件
addEditRefund.disableControl=function(start,end,oper){
	for(var i=start;i<=end;i++){ addEditRefund.refund_element_arr[i].textbox(oper); }
}
//退货datagrid--事件
function beginEditRefundOrder(index,row){
	addEditRefund.refund_element_arr['goods_amount']=addEditRefund.refund_element_arr['tmp_goods_amount']-row.share_price*row.refund_num;
	if(row.platform_id==0){addEditRefund.refund_element_arr['direct_amount']=addEditRefund.refund_element_arr['tmp_direct_amount']-row.share_price*row.refund_num;}
}
function endEditRefundOrder(index,row,changes){
	$.each(changes,function(key,val){
		switch(key){
		case 'refund_num':row.refund_num=row.refund_num<0?-row.refund_num:row.refund_num;if(parseFloat(row.order_num)!=0&&parseFloat(row.refund_num)>parseFloat(row.order_num)){row.refund_num=row.order_num;messager.alert('退货数量不能大于下单数量');}break;
		}
	});
	var goods_amount=parseFloat(addEditRefund.refund_element_arr['goods_amount']+row.share_price*row.refund_num).toFixed(4);
	addEditRefund.refund_element_arr['tmp_goods_amount']=parseFloat(goods_amount).toFixed(4);
	if(row.platform_id==0){addEditRefund.refund_element_arr['tmp_direct_amount']=parseFloat(addEditRefund.refund_element_arr['direct_amount']+row.share_price*row.refund_num).toFixed(4);}
	var refund_type=addEditRefund.refund_element_arr[22].combobox('getValue');
	if(refund_type==2){
		addEditRefund.refund_element_arr[7].numberbox('setValue',goods_amount);
		if(row.platform_id==0){addEditRefund.refund_element_arr[2].numberbox('setValue',goods_amount);}else{addEditRefund.refund_element_arr[1].numberbox('setValue',goods_amount);}
	}
	else if(refund_type==3){addEditRefund.refund_element_arr[7].numberbox('setValue',goods_amount);addEditRefund.refund_element_arr[21].numberbox('setValue',goods_amount);}
	else if(refund_type==4){
		addEditRefund.refund_element_arr[8].numberbox('setValue',goods_amount);
		if(row.platform_id==0){addEditRefund.refund_element_arr[2].numberbox('setValue',goods_amount);}else{addEditRefund.refund_element_arr[1].numberbox('setValue',goods_amount);}
	}else if (refund_type==5) {
		addEditRefund.refund_element_arr[7].numberbox('setValue',0.0000);
		addEditRefund.refund_element_arr[8].numberbox('setValue',0.0000);
		addEditRefund.refund_element_arr[21].numberbox('setValue',0.0000);
	}
}
//退货货品选择事件
addEditRefund.selectRefundOrder=function(trade_dg_id,order_dg_id,dialog_id){
	var trade_dg=$('#'+trade_dg_id);
	var order_dg=$('#'+order_dg_id);
	var trade_row=trade_dg.datagrid('getSelected');
	var order_rows=order_dg.datagrid('getSelections');
	if(order_rows.length==0){messager.alert('请选择货品！');return;}
	var refund_dg=$('#<?php echo ($datagrid["refund_order"]["id"]); ?>');
	$('#'+dialog_id).dialog('close');
	var form_map={'trade_no':'trade_no','buyer_nick':'buyer_nick','receiver_name':'swap_receiver','receiver_mobile':'swap_mobile','receiver_telno':'swap_telno','receiver_address':'swap_address'};//'receiver_province':'swap_province','receiver_city':'swap_city','receiver_district':'swap_district',
	var former=$('#<?php echo ($id_list["form_id"]); ?>');
	var form_data=former.form('get');
	$.each(trade_row,function(key,val){
		if(form_map[key]!=undefined){form_data[form_map[key]]=val;}
	});
	var goods_amount=0; var direct_refund_amount=0;var tids={}; var count=0;
	for(var i in order_rows){
		order_rows[i].original_price=order_rows[i].price;
		order_rows[i].tid=order_rows[i].src_tid;
		order_rows[i].order_num=order_rows[i].actual_num;
		order_rows[i].refund_num=order_rows[i].actual_num;
		if(order_rows[i].gift_type==0){if(order_rows[i].refund_num==order_rows[i].num){goods_amount+=order_rows[i].paid}else{goods_amount+=(order_rows[i].order_price*order_rows[i].refund_num);}}
		if(order_rows[i].platform_id==0){if(order_rows[i].refund_num==order_rows[i].num){direct_refund_amount+=order_rows[i].paid}else{direct_refund_amount+=(order_rows[i].order_price*order_rows[i].refund_num);}}
		if(tids[order_rows[i].tid]==undefined){tids[order_rows[i].tid]=order_rows[i].tid;count++;}
	}
	var tid=''; var i=1;
	$.each(tids,function(key,val){
		tid+=(i==count?tids[key]:(tids[key]+',')); i++;
	});
	var refund_type=addEditRefund.refund_element_arr[22].combobox('getValue');
	if(refund_type==3){
		form_data.exchange_amount=parseFloat(goods_amount).toFixed(4);
		form_data.goods_amount=form_data.exchange_amount;
		form_data.refund_amount=0.0000;
		form_data.direct_refund_amount=0.0000;
		form_data.guarante_refund_amount=0.0000;
	}else{
		form_data.refund_amount=refund_type==5?0.0000:parseFloat(goods_amount).toFixed(4);
		form_data.goods_amount=(refund_type==4||refund_type==5)?0.0000:form_data.refund_amount;
		form_data.direct_refund_amount=parseFloat(direct_refund_amount).toFixed(4);
		form_data.guarante_refund_amount=parseFloat(form_data.refund_amount-form_data.direct_refund_amount).toFixed(4);
	}
	form_data.tid=tid;
	if(form_data['buyer_nick'].indexOf("http://www.taobao.com/webww")==25){
		//旺旺联系的<a>标签中首次出现"http://www.taobao.com/webww"的位置是25
		form_data['buyer_nick']=$(form_data['buyer_nick']).text();
	}
	former.form('filterLoad',form_data);
	var return_form={shop_id:trade_row.shop_id,swap_warehouse_id:1};
	$('#<?php echo ($id_list["return_form"]); ?>').form('filterLoad',return_form);
	if(trade_row.receiver_district!=undefined){
		refundArea.selfP.combobox("setValue", trade_row.receiver_province);
		refundArea.selfC.combobox("loadData", refundArea.getArea("city", trade_row.receiver_province, 1));
		refundArea.selfC.combobox("setValue", trade_row.receiver_city);
		refundArea.selfD.combobox("loadData", refundArea.getArea("district", trade_row.receiver_city, 1));
		refundArea.selfD.combobox("setValue", trade_row.receiver_district);
	}
	refund_dg.datagrid('loadData',{total:order_rows.length,rows:order_rows});
	addEditRefund.refund_element_arr['trade_id']=parseInt(trade_row.id);
	addEditRefund.refund_element_arr['tmp_goods_amount']=parseFloat(goods_amount).toFixed(4);
	addEditRefund.refund_element_arr['tmp_direct_amount']=parseFloat(direct_refund_amount).toFixed(4);
	addEditRefund.refund_element_arr['flag']=true;
}
//换出datagrid--事件
function beginEditReturnOrder(index,row){
	addEditRefund.refund_element_arr['return_amount']=parseFloat(row.retail_price*row.num);
}
function endEditReturnOrder(index,row,changes){
	$.each(changes,function(key,val){
		switch(key){
		case 'num':row.num=row.num<0?-row.num:row.num;break;
		case 'retail_price':row.retail_price=row.retail_price<0?-row.retail_price:row.retail_price;break;
		}
	});
	addEditRefund.refund_element_arr[24].html(parseFloat(parseFloat(addEditRefund.refund_element_arr[24].text())-addEditRefund.refund_element_arr['return_amount']+row.retail_price*row.num).toFixed(4));
}
//添加--换出货品
$($(".easyui-menubutton[name='menu-select-return-order']").menubutton().menubutton('options').menu).menu({
	onClick:function(item){
	 if (is_api==1) {
 	 	prefix='api_add_return_order';
 	 }else if (is_api==2) {
 	 	prefix='trade_manage_add_return_order';
 	 }else{
 	 	prefix='add_return_order';
 	 }
	 switch(item.text){
	 case '添加单品':
		 // is_api==1?prefix='api_add_return_order':prefix='add_return_order';
		 var params={'prefix':prefix,'type':true,'warehouse_id':addEditRefund.refund_element_arr[23].combobox('getValue')};
		 $('#'+addEditRefund.refund_element_arr['dialog_id']).richDialog('goodsSpec', addEditRefund.addSpecOrder, params, false);
		 break;
	 case '添加组合装':
		 $('#'+addEditRefund.refund_element_arr['dialog_id']).richDialog('goodsSuite', addEditRefund.addSuiteOrder, prefix, false);
		 break;
	 }}
});
addEditRefund.addSpecOrder=function(spec_dg_id,sub_dg_id){
	var spec_dg=$('#'+sub_dg_id);
    var spec_rows=spec_dg.datagrid('getRows');
    var show_dg=$('#'+'<?php echo ($datagrid["return_order"]["id"]); ?>');
    $('#'+addEditRefund.refund_element_arr['dialog_id']).dialog('close');
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
    addEditRefund.refund_element_arr[24].html(parseFloat(parseFloat(addEditRefund.refund_element_arr[24].text())+return_goods_amount).toFixed(4));
}
addEditRefund.addSuiteOrder=function(suite_dg_id){
	var suite_dg=$('#'+suite_dg_id);
    var suite_row=suite_dg.datagrid('getSelected');
    var show_dg=$('#'+'<?php echo ($datagrid["return_order"]["id"]); ?>');
    $('#'+addEditRefund.refund_element_arr['dialog_id']).dialog('close');
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
    addEditRefund.refund_element_arr[24].html(parseFloat(parseFloat(addEditRefund.refund_element_arr[24].text())+return_goods_amount).toFixed(4));
}
//退换单--submit
addEditRefund.submitSalesRefund=function(refund_id,refund_type,form_dg,refund_dg,refund_rows,test){
	var data={};
	var refund_data=JSON.parse('<?php echo ($refund_data); ?>');
	var res_refund_type=refund_data.refund.type;

	if(refund_type==3||refund_type==5){
		if(addEditRefund.refund_element_arr[15].textbox('getValue')==''&&addEditRefund.refund_element_arr[16].textbox('getValue')==''){ messager.alert('手机和固话至少有一个不能为空');return; }
		var return_dg=$('#'+'<?php echo ($datagrid["return_order"]["id"]); ?>');
		var return_rows=return_dg.datagrid('getRows');
		if(return_rows.length==0){messager.alert('请选择换出或补发货品');return;}
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
	var refund_dg=$('#'+'<?php echo ($datagrid["refund_order"]["id"]); ?>');
	var refund_rows=refund_dg.datagrid('getRows');
	if(refund_rows.length==0){messager.alert('请选择退款货品');return;}
	for(var i=0;i<refund_rows.length;i++){
		if(refund_rows[i]['gift_type']>0&&test==0){
			messager.confirm('退回货品中存在赠品，是否继续？',function(r){
				if(r){
					addEditRefund.submitSalesRefund(refund_id,refund_type,form_dg,refund_dg,refund_rows,1);
				}else{return;}
			});
			return;
		}
	}
	if(refund_id==0||addEditRefund.refund_element_arr['flag']){
		data['refund_order']=JSON.stringify(refund_rows);
	}else{
		var refund_edit={};
		//refund_edit['add']=refund_dg.datagrid('getChanges','inserted');
		refund_edit['update']=refund_dg.datagrid('getChanges','updated');
		refund_edit['delete']=refund_dg.datagrid('getChanges','deleted');
		data['refund_order']=JSON.stringify(refund_edit);
	}
	addEditRefund.disableControl(4,9,'enable');
	var refund_form=form_dg.form('get');
	//console.log(refund_form);return;
	if(refund_type==3){refund_form['exchange_amount']=addEditRefund.refund_element_arr[21].numberbox('getValue'); var area=refundArea.getText(); refund_form['swap_area']=area['province']+' '+area['city']+' '+area['district']; }
	if (refund_type==5) {refund_form['exchange_amount']=0.0000; var area=refundArea.getText(); refund_form['swap_area']=area['province']+' '+area['city']+' '+area['district']; }
	if(refund_id!=0&&addEditRefund.refund_element_arr['flag']){ refund_form['flag']=true; }
	refund_form['goods_refund_count']=refund_rows.length;
	if(refund_type!=4&&refund_type!=5){refund_form['logistics_name']=addEditRefund.refund_element_arr[10].combobox('getText');}
	refund_form['trade_id']=addEditRefund.refund_element_arr['trade_id'];
	data['info']=JSON.stringify(refund_form);
	addEditRefund.disableControl(4,9,'disable');
	data['id']=refund_id;
	data['is_api']=is_api;
	$.post('<?php echo U('RefundManage/editRefund');?>',data,function(res){
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
addEditRefund.submitEditDialog=function(){
	var form_dg=$('#<?php echo ($id_list["form_id"]); ?>');
	if(!form_dg.form('validate')){ return false;}
	var refund_type=addEditRefund.refund_element_arr[22].combobox('getValue');
	var refund_id='<?php echo ($id_list["refund_id"]); ?>';
	if((refund_type==3||refund_type==2)&&(addEditRefund.refund_element_arr[25].combobox('getValue')==0)){messager.alert('请选择退货入库的仓库');return;}
	var refund_dg=$('#'+'<?php echo ($datagrid["refund_order"]["id"]); ?>');
	var refund_rows=refund_dg.datagrid('getRows');
	if(refund_rows.length==0){messager.alert('请选择退款货品');return;}
	if(refund_id==0||addEditRefund.refund_element_arr['flag']){
		var trade_order_ids=[];
		for(var i in refund_rows){ trade_order_ids.push(refund_rows[i]['rec_id']); }
		$.post('<?php echo U('RefundManage/checkIsRefund');?>',{ids:JSON.stringify(trade_order_ids)},function(res){
			if(!res.status){
				messager.confirm('该订单下已存在退换单，是否继续？',function(r){
					if(!r){
						$('#'+addEditRefund.params.search.form_id).form('filterLoad',{'search[trade_no]':addEditRefund.refund_element_arr[4].textbox('getValue')});
						addEditRefund.submitSearchForm();
						$('#'+addEditRefund.params.edit.id).dialog('close');
						return;
					}else{
						addEditRefund.submitSalesRefund(refund_id,refund_type,form_dg,refund_dg,refund_rows,0);
					}
				});
			}else{
				//addEditRefund.submitSalesRefund(refund_id,refund_type,form_dg,refund_dg,refund_rows,0);
			}
		},'JSON')
	}else{
		//addEditRefund.submitSalesRefund(refund_id,refund_type,form_dg,refund_dg,refund_rows,0);
	}
}

addEditRefund.useCustomer=function(){
	var id=addEditRefund.refund_element_arr.trade_id;
	if(id==0){messager.alert('请选择订单');return;}
	$.post('<?php echo U('RefundManage/getCustomerAddress');?>',{id:id},function(res){
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
			addEditRefund.refund_element_arr[14].textbox('setValue',res.receiver_name);
			if(res.right_flag==0){
				addEditRefund.refund_element_arr[15].textbox('setValue',res.receiver_mobile);
				addEditRefund.refund_element_arr[16].textbox('setValue',res.receiver_telno);
			}
			addEditRefund.refund_element_arr[17].textbox('setValue',res.receiver_address);
		}
	},'JSON')
}
addEditRefund.addSpOrder = function(){
	var refund_dg=$('#'+'<?php echo ($datagrid["refund_order"]["id"]); ?>');
	var refund_rows=refund_dg.datagrid('getRows');
	if(refund_rows.length==0){messager.alert('请先添加退货货品');return;}
	var ids = [];
	for(var i in refund_rows){
		ids.push(refund_rows[i].spec_id);
	}
	ids_str = JSON.stringify(ids);
	var buttons=[ {text:'确定',handler:function(){ addEditRefund.submitAddSpOrderDialog();$('#'+'<?php echo ($add_sp_order_dialog["id"]); ?>').dialog('close'); }}, {text:'取消',handler:function(){addEditRefund.cancelDialog('<?php echo ($add_sp_order_dialog["id"]); ?>')}} ];
	Dialog.show('<?php echo ($add_sp_order_dialog["id"]); ?>','<?php echo ($add_sp_order_dialog["title"]); ?>','<?php echo ($add_sp_order_dialog["url"]); ?>?ids='+ids_str,'<?php echo ($add_sp_order_dialog["height"]); ?>','<?php echo ($add_sp_order_dialog["width"]); ?>',buttons);
}
</script>