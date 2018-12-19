<?php if (!defined('THINK_PATH')) exit();?><div class="easyui-layout" data-options="fit:true,border:false" style="height:540px;width:740px;overflow:hidden;">
	<div data-options="region:'west',split:true" style="width:250px;background: #f4f4f4;">
		<form  id="<?php echo ($id_list["form"]); ?>">
			<div class="form-div" style="margin-left: 5px;">
				<a href="javascript:void(0)" class="easyui-linkbutton" style="border-color: #95B8E7;width: 95%;height :25px;background: #fff;" onclick="intelligenceReturnStockIn.reset();">清　　空</a>
			</div>
			<hr style="border:none;border-top:1px solid #95B8E7;">
			<div class="form-div" style="margin-left: 5px;">
				<input class="easyui-combobox txt" text="txt" name="intelligence_return_scan_type" style="width: 95%;height :25px;" data-options="panelHeight:170,valueField:'id',textField:'name',data:[{'id':'0','name':'物流单号'},{'id':'1','name':'发出物流单号'},{'id':'2','name':'手机号'},{'id':'3','name':'客户网名'},{'id':'4','name':'原始单号'},{'id':'5','name':'条码'}],editable:false,value:'0'">
			</div>
			<div class="form-div" style="margin-left: 5px;">
				<input name="intelligence_return_scan_search" class="easyui-textbox txt" style="width: 95%;height :25px;background: #fff;" type="text" data-options="buttonText: '搜索'" />
			</div>
			<div class="form-div" style="margin-left: 5px;">
				<fieldset style="border:1px solid #95B8E7;padding: 2px 2px 12px 2px;width: 91%;">
					<legend style="margin-left: 5px;font-weight: bold;">退换信息</legend>
					<div class="form-div"><label>物流单号：</label><input style="width: 68%;" class="easyui-textbox txt" type="text" name="refund_info_logistics" /></div>
					<div class="form-div"><label>网　　名：</label><input style="width: 68%;" class="easyui-textbox txt" type="text" name="refund_info_buyer_nick" /></div>
					<div class="form-div"><label>手 &nbsp;机&nbsp;号：</label><input style="width: 68%;" class="easyui-textbox txt" type="text" name="refund_info_receiver_mobile" /></div>
					<div class="form-div"><label>物流公司：</label><input style="width: 68%;" class="easyui-textbox txt" type="text" name="refund_info_logistics_name" /></div>
					<div class="form-div"><label>退换原因：</label><input style="width: 68%;" class="easyui-textbox txt" editable="false" type="text" name="refund_info_reason" /></div>
					<div class="form-div"><label>订单备注：</label><input class="easyui-textbox txt" editable="false" multiline="true" style="width: 68%;height: 50px;" type="text" name="refund_info_remark" /></div>
					<input type="hidden"  name='refund_id' value="">
					<input type="hidden"  name='refund_type' value="">
				</fieldset>
				<fieldset style="border:1px solid #95B8E7;padding: 2px 2px 12px 2px;width: 91%;margin-top: 5px;">
					<legend style="margin-left: 5px;font-weight: bold;">备注信息</legend>
					<div class="form-div"><label>标　　旗：</label>
						<label style="padding: 2px 0px 1px 0px;background: #ccc;display: inline-block;"><input name="singal_flag" type="radio" value="1" style="margin: 2px;" /></label>
						<label style="padding: 2px 0px 1px 0px;background: red;display: inline-block;"><input name="singal_flag" type="radio" value="1" style="margin: 2px;" /></label>
						<label style="padding: 2px 0px 1px 0px;background: yellow;display: inline-block;"><input name="singal_flag" type="radio" value="1" style="margin: 2px;" /></label>
						<label style="padding: 2px 0px 1px 0px;background: green;display: inline-block;"><input name="singal_flag" type="radio" value="1" style="margin: 2px;" /></label>
						<label style="padding: 2px 0px 1px 0px;background: blue;display: inline-block;"><input name="singal_flag" type="radio" value="1" style="margin: 2px;" /></label>
						<label style="padding: 2px 0px 1px 0px;background: #660066;display: inline-block;"><input name="singal_flag" type="radio" value="1" style="margin: 2px;" /></label>
					</div>
					<div class="form-div"><label>入库备注：</label><input class="easyui-textbox txt" multiline="true" style="width: 68%;height: 50px;" type="text" name="refund_in_remark" /></div>
				</fieldset>
				<fieldset style="border:1px solid #95B8E7;padding: 0px 2px 8px 2px;width: 91%;margin-top: 5px;">
					<legend style="margin-left: 5px;font-weight: bold;">提示信息</legend>
					<div class="form-div"><input class="easyui-textbox txt" editable="false" multiline="true" style="width: 98%;height: 50px;" value="" type="text" name="prompt_info" /></div>
				</fieldset>
			</div>
		</form>
	</div>
	<div data-options="region:'center'" style="height:100%;">
		<div data-options="region:'center',fit:true" style="height:90%;">
			<table id="<?php echo ($datagrid["id"]); ?>" class="easyui-datagrid" data-options='<?php $dataOptions = array_merge(array ( 'border' => false, 'fit' => true, 'fitColumns' => true, 'rownumbers' => true, 'singleSelect' => true, 'pagination' => true, 'pageList' => array ( 0 => 20, 1 => 50, 2 => 100, ), 'pageSize' => 20, ), $datagrid["options"]);if(isset($dataOptions['toolbar']) && substr($dataOptions['toolbar'],0,1) != '#'): unset($dataOptions['toolbar']); endif;if(isset($dataOptions['methods'])): unset($dataOptions['methods']);endif; echo trim(json_encode($dataOptions), '{}[]').((isset($datagrid["options"]['toolbar']) && substr($datagrid["options"]['toolbar'],0,1) != '#')?',"toolbar":'.$datagrid["options"]['toolbar']:null).(isset($datagrid["options"]['methods'])? ','.$datagrid["options"]['methods']:null); ?>' style="<?php echo ($datagrid["style"]); ?>" ><thead><tr><?php if(is_array($datagrid["fields"])):foreach ($datagrid["fields"] as $key=>$arr):if(isset($arr['formatter'])):unset($arr['formatter']);endif;if(isset($arr['methods'])):unset($arr['methods']);endif;if(isset($arr['editor'])):unset($arr['editor']);endif;echo "<th data-options='".trim(json_encode($arr), '{}[]').(isset($datagrid["fields"][$key]['formatter'])?",\"formatter\":".$datagrid["fields"][$key]['formatter']:null).(isset($datagrid["fields"][$key]['editor'])?",\"editor\":".$datagrid["fields"][$key]['editor']:null).(isset($datagrid["fields"][$key]['methods'])?",".$datagrid["fields"][$key]['methods']:null)."'>".$key."</th>";endforeach;endif; ?></tr></thead></table>
			<div id="<?php echo ($id_list["toolbar"]); ?>">
				<form  id="<?php echo ($id_list["form_id"]); ?>">
					<div class="form-div" style="margin-bottom: 5px;">
						<label style="margin-left: 20px;">入库仓库：</label><select id="intelligence_return_stock_in_warehouse" class="easyui-combobox sel" name="stock_in_warehouse" data-options="panelHeight:'230'"> <?php if(is_array($list["warehouse"])): $i = 0; $__LIST__ = $list["warehouse"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?> </select>
						<label style="margin-left: 20px;">打印模板：</label>
							<select name="template_list" class='easyui-combobox sel' data-options="width:161,onSelect:function(res){intelligenceReturnStockIn.templateOnSelect();}" >
								<?php if(is_array($goods_template)): $i = 0; $__LIST__ = $goods_template;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
							</select>
							<div name="template_guide" style="margin-top:7px;margin-left:57px;display:none"><span style="color:red">没有模板？去</span><a href="javascript:void(0)" onclick="(function(){intelligenceReturnStockIn.changeTemplatePage();})()">打印模板</a><span style="color:red">界面下载模板</span></div><!---->
						<label style="margin-left: 20px;">打印机：</label><input name="printer_list" class="easyui-combobox" data-options="width:161,onSelect:function(res){intelligenceReturnStockIn.onPrinterSelect(res.name);}"/>
						<a href="javascript:void(0)" class="easyui-linkbutton" style="border-color: #95B8E7;width: 10%;height :25px;background: #fff;margin-right: 10px;float: right;" onclick="intelligenceReturnStockIn.setting();">设置</a>
					</div>
				</form>
			</div>
		</div>
		<div data-options="region:'south',split:true" style="border-top:1px solid #95B8E7;height: 8%;background:#eee;overflow:hidden;">
			<div id="<?php echo ($id_list["toolbar_bottom"]); ?>">
				<form  id="<?php echo ($id_list["form_bottom_id"]); ?>">
					<div class="form-div" style="padding-top: 5px;">
						<a href="javascript:void(0)" class="easyui-linkbutton" style="border-color: #95B8E7;width: 10%;height :30px;background: #fff;margin-left: 10px;" onclick="intelligenceReturnStockIn.addSpec();">添加单品</a>
						<a href="javascript:void(0)" class="easyui-linkbutton" style="border-color: #95B8E7;width: 10%;height :30px;background: #fff;margin-left: 10px;" onclick="intelligenceReturnStockIn.printGoodsWarehouseInfo();">打印货品</a>
						<a href="javascript:void(0)" class="easyui-linkbutton" style="border-color: #95B8E7;width: 10%;height :30px;background: #fff;margin-right: 10px;float: right;" onclick="intelligenceReturnStockIn.returnStockIn('exchange');">换货</a>
						<a href="javascript:void(0)" class="easyui-linkbutton" style="border-color: #95B8E7;width: 10%;height :30px;background: #fff;margin-right: 10px;float: right;" onclick="intelligenceReturnStockIn.returnStockIn('return');">退货</a>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
<div id="<?php echo ($id_list["add_spec"]); ?>"></div>
<div id="<?php echo ($id_list["print_dialog"]); ?>"></div>
<div id="intelligence_exchange_add"></div>
<div id="intelligence_return_stock_in_setting"></div>
<div id="intelligence_return_stock_in_exchange"></div>
<script type="text/javascript">
//# sourceURL=intelligence_return_stock_in.js
var intelligenceinWS;
(function(){
	function IntelligenceReturnStockIn(params,settings,form_selector_list){
		this.params = params;
		this.settings = settings;
		this.scan_status = 0;//扫描状态
		this.form_selector_list  = form_selector_list;
		this.template_contents = <?php echo ($contents); ?>;
		this.init_form = {
			'scan_type':'0','scan_search':'','refund_info_logistics':'','refund_info_buyer_nick':'','refund_info_receiver_mobile':'','refund_info_logistics_name':'','refund_info_reason':'','refund_info_remark':'',refund_id:'',refund_type:''
		};
		this.input_type_list = {
			'scan_type':'combobox','scan_search':'textbox','refund_info_logistics':'textbox','refund_info_buyer_nick':'textbox','refund_info_receiver_mobile':'textbox','refund_info_logistics_name':'textbox','refund_info_reason':'textbox','refund_info_remark':'textbox','refund_id':'input',refund_type:'input'
		};
		this.init_form_data = $.extend({},this.init_form);
	}
	IntelligenceReturnStockIn.prototype = {
		//初始化页面
		initShow : function(){
			var that = this;
			that.scan_status = 0;
			that.initFormData(0);
			that.form_selector_list.refund_info_reason.textbox('textbox').css({'background-color':'#ddd'});
			that.form_selector_list.refund_info_remark.textbox('textbox').css({'background-color':'#ddd'});
			that.form_selector_list.scan_search.textbox('textbox').bind('keydown',function(e){if(e.keyCode==13){that.submitScanSearch(e);}});
			$('#'+that.params.datagrid).datagrid('options').rowStyler = that.flagRowStatusByRowStyle;
			that.form_selector_list.prompt_info.textbox('textbox').css('color','red');
			that.form_selector_list.prompt_info.textbox('textbox').css('font-size','18px');
		},
		//重置页面
		reset : function(){
			this.initShow();
			$('#'+this.params.datagrid).datagrid('loadData',{total:0,rows:[]});
		},
		//设置
		setting : function(){
			var that = this;
			Dialog.show('intelligence_return_stock_in_setting','智能退货入库设置',"<?php echo U('Stock/StockInOrder/returnStockInSetting');?>",200,400,[{text:"确定",handler:function(){
				var data = {};
				//var setting_form = $('#intelligence_return_setting_stockinorder_form').form('get');
				$('#intelligence_return_setting_stockinorder_form input').each(function(){
					var input_that = this;
					if (typeof(input_that.name) != "undefined" && input_that.name != "" && input_that.name != 0)
					{
						data[input_that.name] = input_that.value;
						that.settings[input_that.name] = input_that.value;
					}
				});
				var url = '<?php echo U("Stock/StockInOrder/updateSetting");?>';
				$.post(url, {"data": data}, function (res) {
					if (res.status)
					{
						$("#intelligence_return_stock_in_setting").dialog('close');
						that.messagerAlert(res.info);
					}
					else
					{
						that.messagerAlert(res.info);
					}
				});
			}},{text:"取消",handler:function(){$("#intelligence_return_stock_in_setting").dialog('close');}}]);
		},
		//提示方式
		messagerAlert : function(info){
			var that = this;
			var multiple_way_prompt = that.settings.intelligence_return_multiple_way_prompt == undefined ? '0' : that.settings.intelligence_return_multiple_way_prompt;
			if(multiple_way_prompt == 1)
			{
				messager.alert(info);
				that.form_selector_list.prompt_info.textbox('setValue',info);
			}
			else
			{
				that.form_selector_list.prompt_info.textbox('setValue',info);
			}
		},
		//订单备注“换邮”匹配
		regRefundRemark : function(){
			var reg_h = /换/g;
			var reg_y = /邮/g;
			var remark = this.form_selector_list.refund_info_remark.textbox('getValue');
			if(this.settings.intelligence_return_font_match_prompt == 1)
			{
				return reg_h.test(remark) || reg_y.test(remark);
			}
			else
			{
				return false;
			}
		},
		//添加单品
		addSpec : function(){
			var that = this;
			var prefix = 'intelligence_in_add_spec';
			$('#' + that.params.add_spec).richDialog('goodsSpec', that.submitGoodsSpecDialog,{
				'prefix':prefix,
			},that);
		},
		//退换货
		returnStockIn : function(return_type){
			var that = this;
			//var trade_order_ids = [];
			var form_bottom_data = $('#<?php echo ($id_list["form_id"]); ?>').form('get');
			if(form_bottom_data.stock_in_warehouse==''||form_bottom_data.template_list==''||form_bottom_data.printer_list=='')
			{
				that.messagerAlert('入库仓库、打印模板、打印机选项不能为空！');return;
			}
			var select_rows = $('#'+that.params.datagrid).datagrid('getRows');
			if($.isEmptyObject(select_rows))
			{
				that.messagerAlert('退货列表为空，请核对后重试!');return;
			}
			var refund_type = that.form_selector_list.refund_type.val();
			switch(refund_type){
				case '2' :
					if(return_type != 'return')
					{
						that.messagerAlert('该退换单为退货单，请点击退货!');return;
					}
					break;
				case '3' :
					if(return_type != 'exchange')
					{
						that.messagerAlert('该退换单为换货单，请点击换货!');return;
					}
					break;
			}
			if(that.regRefundRemark())
			{
				messager.confirm('该订单的客服备注中存在“换”、“邮”，是否继续？',function(r){
					if(r){
						that.checkSubmitReturnStockIn(select_rows,refund_type);
					}else{return;}
				});
			}
			else
			{
				that.checkSubmitReturnStockIn(select_rows,refund_type);
			}
		},
		checkSubmitReturnStockIn : function(select_rows,refund_type){
			var that = this;
			var trade_order_ids = [];
			for(var i in select_rows){ trade_order_ids.push(select_rows[i]['rec_id']); }
			$.post('<?php echo U("Trade/RefundManage/checkIsRefund");?>',{ids:JSON.stringify(trade_order_ids)},function(res){
				if(!res.status)
				{
					messager.confirm('该订单下已存在退换单，是否继续？',function(r){
						if(r)
						{
							switch(refund_type){
								case '2' :
									that.submitReturnStockIn(0,2,0);
									break;
								case '3' :
									that.submitExchangeStockIn(0,2,0);
									break;
							}
						}
					});
				}else
				{
					switch(refund_type){
						case '2' :
							that.submitReturnStockIn(0,2,0);
							break;
						case '3' :
							that.submitExchangeStockIn(0,2,0);
							break;
					}
				}
			},'JSON');
		},
		//提交换货
		submitExchangeStockIn : function(){
			var that = this;
			var refund_id = that.form_selector_list.refund_id.val();
			var url = "<?php echo U('Stock/StockInOrder/exchangeRefund');?>" + "?id="+refund_id+"&is_api=1";
			Dialog.show('intelligence_return_stock_in_exchange','选择换出货品',url,510,1000,[{text:"确定",handler:function(){
				intelligenceExchange.submitEditDialog();
			}},{text:"取消",handler:function(){$("#intelligence_return_stock_in_exchange").dialog('close');}}]);
		},
		//提交退货
		submitReturnStockIn : function(refund_id,refund_type,test){
			var that = this;
			var data={};
			var refund_rows = $('#'+that.params.datagrid).datagrid('getRows');
			for(var i=0;i<refund_rows.length;i++){
				var row_index = $('#'+that.params.datagrid).datagrid('getRowIndex',refund_rows[i]);
				$('#'+that.params.datagrid).datagrid('endEdit',row_index);
				refund_rows[i]['refund_num'] = refund_rows[i]['stockin_num'];
				if(refund_rows[i]['gift_type']>0&&test==0)
				{
					messager.confirm('退回货品中存在赠品，是否继续？',function(r){
						if(r){
							that.submitReturnStockIn(refund_id,refund_type,1);
						}else{return;}
					});
					return;
				}
				if(parseInt(refund_rows[i]['expect_num']) > parseInt(refund_rows[i]['stockin_num']))
				{
					that.messagerAlert('退换入库数量不能小于预期数量，请核对后在进行退换货!');return;
				}
				if(parseInt(refund_rows[i]['expect_num']) < parseInt(refund_rows[i]['stockin_num']) && test==0)
				{
					messager.confirm('退换入库数量大于预期数量，是否要继续进行退换？',function(r){
						if(r){
							that.submitReturnStockIn(refund_id,refund_type,1);
						}else{return;}
					});
					return;
				}
			}
			var refund_form = $('#<?php echo ($id_list["form"]); ?>').form('get');

//			for(var i in refund_rows){
//				refund_rows[i]['refund_num'] = refund_rows[i]['stockin_num'];
//			}
			refund_form['goods_refund_count']=refund_rows.length;
			refund_form['warehouse_id']=that.form_selector_list.stock_in_warehouse.combobox('getValue');
			data['refund_order']=JSON.stringify(refund_rows);
			data['id']='0';
			data['is_api']='1';
			data['info'] = refund_form;
			$.post('<?php echo U("Stock/StockInOrder/submitReturnStockIn");?>',data,function(r){
				if(r.status == 0 && that.settings.intelligence_return_print_way == 0)
				{
					that.messagerAlert(r.msg);
					that.printGoodsWarehouseInfo();
				}
				else
				{
					that.messagerAlert(r.msg);
				}
			},'json');
		},
		//提交选择单品对话框
		submitGoodsSpecDialog :function(up_datagrid,down_datagrid,intelligence_in_object){
			var merge_result_new = [];
			var update_result_new = [];
			//获取对话框中的添加的数据
			var new_rows = $("#"+down_datagrid).datagrid("getRows");
			var formated_new_rows = utilTool.array2dict(new_rows,['spec_id'],'');
			//获取原有数据
			var intelligence_in_datagrid = intelligence_in_object.params.datagrid;
			var old_rows = $('#' + intelligence_in_datagrid).datagrid('getRows');
			var formated_old_rows = utilTool.array2dict(old_rows,['spec_id'],'');
			for (var j in formated_new_rows)
			{
				if (formated_old_rows[j] == undefined   || $.isEmptyObject(formated_old_rows))
				{
					var map_suite = {
						'id'           		:formated_new_rows[j].id,
						'pic_name'          :formated_new_rows[j].pic_name,
						'barcode'           :formated_new_rows[j].barcode,
						'spec_id'           :formated_new_rows[j].spec_id,
						'spec_no'   		:formated_new_rows[j].spec_no,
						'trade_no'   		:formated_new_rows[j].trade_no,
						'shop_name'   		:formated_new_rows[j].shop_name,
						'expect_num'   		:'0.0000',
						'stockin_num'   	:formated_new_rows[j].num,
						'remark'    		:formated_new_rows[j].remark,
						'goods_name'    	:formated_new_rows[j].goods_name,
						'goods_no'    		:formated_new_rows[j].goods_no,
						'short_name'    	:formated_new_rows[j].short_name,
						'spec_code'   		:formated_new_rows[j].spec_code,
						'spec_name'    		:formated_new_rows[j].spec_name,
					};
					merge_result_new.push($.extend({},map_suite));
				}
				else
				{
					update_result_new[formated_new_rows[j].spec_id] = Math.floor(formated_old_rows[formated_new_rows[j].spec_id].stockin_num) + Math.floor(formated_new_rows[formated_new_rows[j].spec_id].num);
				}
			}
			for(var j = 0; j < old_rows.length; ++j){
				index = $('#' + intelligence_in_datagrid).datagrid('getRowIndex', old_rows[j]);
				$('#' + intelligence_in_datagrid).datagrid('updateRow', {
					index: index,
					row: {
						index: index,
						//expect_num: update_result_new[old_rows[j].id],
						stockin_num: update_result_new[old_rows[j].spec_id],
					}
				});
			}
			for(var new_key in merge_result_new){
				$('#' + intelligence_in_datagrid).datagrid('appendRow', merge_result_new[new_key]);
			}
			$('#' + intelligence_in_object.params.add_spec).dialog('close');
		},
		//提交扫描
		submitScanSearch : function(){
			var that = this;
			var scan_type = that.form_selector_list.scan_type.combobox('getValue');
			var scan_value = that.form_selector_list.scan_search.textbox('getValue');
			scan_value = $.trim(scan_value);
			if((scan_type == 0 || scan_type == 1 || scan_type == 2 || scan_type == 4) && that.scan_status == 0)
			{
				that.getReturnInfo(scan_value,scan_type);
			}
			else if((scan_type == 0 || scan_type == 1 || scan_type == 2 || scan_type == 4) && that.scan_status == 1)
			{
				messager.confirm('当前订单退换还没有完成，需要替换新订单吗？',function(r){
					if(r)
					{
						try{
							that.getReturnInfo(scan_value,scan_type);
						}catch(e){
							//that.playWarnSound();
							that.messagerAlert('异常情况，请联系管理员！');
						}
					}
					else
					{
						that.form_selector_list.scan_type.combobox('setValue','5');
						that.form_selector_list.scan_search.textbox('setValue','');
						that.form_selector_list.scan_search.textbox('textbox').focus();
					}
				});
			}
			else if(scan_type == 5 && that.scan_status == 0)
			{
				messager.confirm('还未扫描订单信息，请先扫描订单信息',function(r){
					if(r)
					{
						that.form_selector_list.scan_type.combobox('setValue','0');
						that.form_selector_list.scan_search.textbox('setValue','');
						that.form_selector_list.scan_search.textbox('textbox').focus();
					}
				});
			}
			else if(scan_type == 5 && that.scan_status == 1)
			{
				that.getScanGoodsInfo(scan_value,scan_type);
			}
		},
		//获取退换信息
		getReturnInfo : function(scan_value,scan_type){
			var that = this;
			$.post('<?php echo U("Stock/StockInOrder/getReturnInfo");?>',{scan_value:scan_value,scan_type:scan_type},function(result){
				if(result.status == 0)
				{
					that.suite_no = result.suite_no;
//					if(global_is_quick_examine){
//						//var resule_detail_rows = result.stockout_order_detial_goods_info.rows;
//						for(var i=0; i<result.stockout_order_detial_goods_info.rows.length; ++i){
//							result.stockout_order_detial_goods_info.rows[i]['check_num'] = result.stockout_order_detial_goods_info.rows[i]['num'];
//							result.stockout_order_detial_goods_info.rows[i]['num_status'] = 1;
//						}
//						var init_data = {};
//						init_data = $.extend({},that.init_form_data,result.stockout_order_info);
//						for(var item_name in init_data){
//							if(that.init_form_data[item_name] != undefined){
//								var component_type = that.input_type_list[item_name];
//								var component_value = init_data[item_name];
//								if(component_type == 'html'){
//									that.form_selector_list[item_name].html(component_value);
//								}else if(component_type == 'input'){
//									that.form_selector_list[item_name].val(component_value);
//								}else{
//									that.form_selector_list[item_name][component_type]('setValue',component_value);
//								}
//							}
//						}
//						//var datagrid_id =$(that).datagrid('options').erpTabObject.params.datagrid.id;
//						var rows = $('#'+datagrid_id).datagrid('getRows');
//						var scan_goods_num=result.stockout_order_info.order_goods_num;
//						that.form_selector_list['order_goods_num'].html(scan_goods_num);
//						that.form_selector_list['scan_goods_num'].html(scan_goods_num);
//						$('#'+datagrid_id).datagrid('loadData',result.stockout_order_detial_goods_info);
//
//						//that.form_selector_list['scan_class'].combobox('setValue','barcode');
//						that.form_selector_list.barcode_or_trade_no.textbox('setValue','');
//						that.form_selector_list.barcode_or_trade_no.textbox('textbox').focus();
//						that.scan_status = 1;//标识订单是否扫描过了
//						stockoutExamine.consignCheck(1);
//						stockoutExamine.playSuccessSound();
//					}else{
					that.initFormData(1,result.refund_info);
					$('#'+that.params.datagrid).datagrid('loadData',result.refund_detail_goods_info);
//					var datagrid_data = $('#'+that.params.datagrid).datagrid('getRows');
//					for(var row_index in datagrid_data){
//						var now_row = datagrid_data[row_index];
//						var now_index = $('#'+that.params.datagrid).datagrid('getRowIndex',now_row);
//						now_row['num_status'] = 2;
//						$('#'+that.params.datagrid).datagrid('updateRow',{index:now_index,row:now_row});
//
//					}
//					var scan_goods_num = 0;
//					for(var row_index in datagrid_data){
//						var now_row = datagrid_data[row_index];
//						var now_index = $('#'+that.params.datagrid.id).datagrid('getRowIndex',now_row);
//						if(now_row['is_not_need_examine']==1){
//							now_row['scan_type'] = parseInt(now_row['scan_type']) == 2?2:1;
//							now_row['num_status'] = 1;
//							now_row['check_num'] = parseInt(now_row['num']);
//							$('#'+that.params.datagrid.id).datagrid('updateRow',{index:now_index,row:now_row});
//						}
//						scan_goods_num = scan_goods_num+parseInt(now_row['check_num']);
//					}
//					that.form_selector_list['scan_goods_num'].html(scan_goods_num);
//					}
				}
				else if(result.status == 1)
				{
					//that.playWarnSound();
					that.messagerAlert(result.info,undefined,function(){
						that.form_selector_list.scan_type.combobox('setValue','0');
						that.form_selector_list.scan_search.textbox('setValue','');
						that.form_selector_list.scan_search.textbox('textbox').focus();
					});

				}
				else if(result.status == 2)
				{
					//that.playWarnSound();
					/*result.stock_no = result.stock['stockout_no'];
					var info = [];
					info.push(result);
					$.fn.richDialog("response", info, "stockout");*/
					that.messagerAlert(result.msg);
				}
			},'json');
		},
		//获取条码信息
		getScanGoodsInfo : function(scan_value,scan_type){
			var that = this;
			$.post('<?php echo U("Stock/StockInOrder/getReturnInfo");?>',{scan_value:scan_value,scan_type:scan_type},function(result){
				if(result.status == 0)
				{
					var goods_list = result.match_goods_list.rows;
					that.goods_list = result.match_goods_list.rows;
					if($.isEmptyObject(goods_list))
					{
						that.messagerAlert('条码：'+scan_value+' 不存在');
						//that.playWarnSound();
					}
					else if(goods_list.length == 1)
					{
						that.updateScanGoodsNum(goods_list[0]);
					}
					else if(goods_list.length > 1)
					{
						//主页的默认的对话框
						$('#flag_set_dialog').dialog({
							title:that.params.select.title,
							iconCls:'icon-save',
							width:that.params.select.width==undefined?764:that.params.select.width,
							height:that.params.select.height==undefined?560:that.params.select.height,
							closed:false,
							inline:true,
							modal:true,
							href:that.params.select.url+'?parent_datagrid_id='+that.params.datagrid.id+'&parent_object=StockoutExamine&goods_list_dialog=flag_set_dialog',
							buttons:[]
						});
					}
					else
					{
						//that.playWarnSound();
						that.messagerAlert('未获取到任何数据请联系管理员');
					}

				}
				else if(result.status == 1)
				{
					//that.playWarnSound();
					that.messagerAlert(result.msg);
				}
			},'json');
			//that.form_selector_list['scan_class'].combobox('setValue','barcode');
			that.form_selector_list.scan_search.textbox('setValue','');
			that.form_selector_list.scan_search.textbox('textbox').focus();
		},
		//扫描条码更新行
		updateScanGoodsNum : function(scan_goods){
			var that = this;
			var datagrid_data = $('#'+that.params.datagrid).datagrid('getRows');
			var suite_no = that.suite_no;
			var scan_goods_num = 0;
			var is_match = 0;
			var in_suite = 0;
			if(scan_goods['is_suite'] == '1')
			{
				for(var k in suite_no){
					if(suite_no[k]['suite_no'] == scan_goods['spec_no'])
					{
						in_suite = 1;
						break;
					}
				}
				if(in_suite == 1)
				{
					$.post("<?php echo U('Stock/SalesStockoutExamine/getSuiteInfo');?>",{no:scan_goods['spec_no']},function(r){
						if(r.status == 0)
						{
							var info = r.info;
							for(var datagrid_index in datagrid_data){
								var now_row = datagrid_data[datagrid_index];
								var now_index = $('#'+that.params.datagrid.id).datagrid('getRowIndex',now_row);
								for(var suite_index in info ){
									if(now_row['spec_no'] == info[suite_index]['spec_no'])
									{
										now_row['scan_type'] = parseInt(now_row['scan_type']) == 2?2:1;
										if(parseInt(now_row['num'])- parseInt(now_row['check_num'])== parseInt(info[suite_index]['num']))
										{
											now_row['num_status'] = 1;
											//that.playSuccessSound();
										}
										else if(parseInt(now_row['num'])- parseInt(now_row['check_num'])< parseInt(info[suite_index]['num']))
										{
											now_row['num_status'] = 2;
											//that.playWarnSound();
											that.messagerAlert('校验量超过货品实际数量!');
										}
										else
										{
											now_row['num_status'] = 0;
										}
										now_row['check_num'] = parseInt(now_row['check_num'])+parseInt(info[suite_index]['num']);
										$('#'+that.params.datagrid.id).datagrid('updateRow',{index:now_index,row:now_row});
									}
								}
								scan_goods_num = scan_goods_num+parseInt(now_row['check_num']);
							}
							that.form_selector_list['message_info'].html('');
						}
						else
						{
							that.messagerAlert(r.info);
						}
						that.form_selector_list['scan_goods_num'].html(scan_goods_num);
						return;
						if(that.judgeSuccessAboutCheck())
						{
							that.consignCheck();
						}
					});
				}else{
					that.form_selector_list['scan_goods_num'].html(scan_goods_num);
					return;
					if(that.judgeSuccessAboutCheck())
					{
						that.consignCheck();
					}
					that.form_selector_list['message_info'].html('订单不包含该组合装');
					that.playWarnSound();
				}
			}
			else
			{
				for(var row_index in datagrid_data){
					var now_row = datagrid_data[row_index];
					var now_index = $('#'+that.params.datagrid).datagrid('getRowIndex',now_row);
					if(now_row['spec_no']==scan_goods['spec_no'])
					{
						is_match = 1;
						/*now_row['scan_type'] = parseInt(now_row['scan_type']) == 2?2:1;
						if(parseInt(now_row['num'])- parseInt(now_row['check_num'])==1){
							now_row['num_status'] = 1;
							that.playSuccessSound();
						}else if(parseInt(now_row['num'])- parseInt(now_row['check_num'])<1){
							now_row['num_status'] = 2;
							that.playWarnSound();
							that.messagerAlert('校验量超过货品实际数量!');
						}else{
							now_row['num_status'] = 0;
						}*/
						now_row['stockin_num'] = parseInt(now_row['stockin_num'])+ 1;
						$('#'+that.params.datagrid).datagrid('updateRow',{index:now_index,row:now_row});
					}
//					scan_goods_num = scan_goods_num+parseInt(now_row['check_num']);

				}
				if(is_match == 0)
				{
					//that.playWarnSound();
					that.messagerAlert('订单不包含该货品');
				}
				return;
				if(that.judgeSuccessAboutCheck())
				{
					that.consignCheck();
				}
			}
		},
		//初始化表单数据
		initFormData : function(type,update_data_obj){
			var that = this;
			var init_data = {};
			//this.is_manual_scan = 0;
			if(type == 0)
			{
				init_data = $.extend({},that.init_form_data);
			}
			else if(type == 1 && update_data_obj != undefined && $.isPlainObject(update_data_obj))
			{
				init_data = $.extend({},that.init_form_data,update_data_obj);
			}
//			else if(type == 3)
//			{
//				this.form_selector_list['scan_class'].combobox('setValue','trade_no');
//				this.form_selector_list.barcode_or_trade_no.textbox('setValue','');
//				this.form_selector_list.barcode_or_trade_no.textbox('textbox').focus();
//				this.scan_status = 0;//标识订单是否扫描过了
//				return;
//			}
			for(var item_name in init_data){
				if(that.init_form_data[item_name] != undefined)
				{
					var load_type = that.input_type_list[item_name];
					var load_value = init_data[item_name];
					if(load_type == 'input')
					{
						that.form_selector_list[item_name].val(load_value);
					}
					else
					{
						that.form_selector_list[item_name][load_type]('setValue',load_value);
					}
				}
			}
			if(type == 0)
			{
				var scan_type_default = that.settings.intelligence_return_search_way==undefined ? '0' : that.settings.intelligence_return_search_way;
				that.form_selector_list.prompt_info.textbox('setValue','');
				that.form_selector_list.scan_type.combobox('setValue',scan_type_default);
				that.form_selector_list.scan_search.textbox('setValue','');
				that.form_selector_list.scan_search.textbox('textbox').focus();
				that.scan_status = 0;
			}
			else if(type == 1)
			{
				that.form_selector_list.scan_type.combobox('setValue','5');
				that.form_selector_list.scan_search.textbox('setValue','');
				that.form_selector_list.scan_search.textbox('textbox').focus();
				that.scan_status = 1;
			}
		},
		//更改datagrid行样式
		flagRowStatusByRowStyle : function(index,row){
			/*if(parseInt(row.num_status)==1)
			{
				return 'background-color:#1E90FF';
			}
			else if(parseInt(row.num_status) ==2)
			{
				return 'background-color:#008000';
			}
			else if(parseInt(row.num_status) ==3)
			{
				return 'background-color:#ff0000';
			}*/
			if(parseInt(row.expect_num)>parseInt(row.stockin_num)&&parseInt(row.stockin_num)!=0)
			{
				return 'background-color:#1E90FF';
			}
			else if(parseInt(row.expect_num)==parseInt(row.stockin_num))
			{
				return 'background-color:#008000';
			}
			else if(parseInt(row.expect_num)<parseInt(row.stockin_num))
			{
				return 'background-color:#ff0000';
			}
		},
		/***************************打印相关*****************************/
		changeTemplatePage : function(){
			open_menu('打印模板','<?php echo U("Setting/NewPrintTemplate/getNewPrintTemplate");?>');
		},
		newSelectPrinter:function(){
			//intelligenceReturnStockIn.connectStockWS();
			var request = {
				"cmd":"getPrinters",
				"requestID":"123458976"+"99",
				"version":"1.0",
			}
			intelligenceinWS.send(JSON.stringify(request));
		},
		connectStockWS:function(){
			if(intelligenceinWS == undefined){
				intelligenceinWS = new WebSocket("ws://127.0.0.1:13528");
				intelligenceinWS.onmessage = function(event){intelligenceReturnStockIn.onStockMessage(event);};
				intelligenceinWS.onerror = function(){intelligenceReturnStockIn.onStockError();};
			}
			return ;
		},
		onStockMessage:function(event){
			var response_result =JSON.parse(event.data);
			if(!$.isEmptyObject(response_result.status) && response_result.status != 'success')
			{
				that.messagerAlert(response_result.msg);
				return;
			}
			if(!$.isEmptyObject(response_result))
			{
				switch(response_result.cmd)
				{
					case 'getPrinters':/*打印机列表命令*/
					{
						var type = response_result.requestID;
						type = type.substr(type.length-2,type.length);
						if(type == 99)
						{
							intelligenceReturnStockIn.form_selector_list.printer_list.combobox({
								valueField: 'name',
								textField: 'name',
								data: response_result.printers,
								value: response_result.defaultPrinter
							});
							intelligenceReturnStockIn.form_selector_list.printer_list.combobox('reload');
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
						if(response_result.taskStatus == "printed")
						{
							var type = response_result.taskID;
							type = type.substr(type.length-2,type.length);
							if(type==13)
							{
								intelligenceReturnStockIn.messagerAlert("打印完成");
							}
						}
						else if(response_result.taskStatus == "failed")
						{
							intelligenceReturnStockIn.messagerAlert("打印失败");
						}
						break;
					}
				}

			}
		},
		onStockError:function(){
			intelligenceinWS = null;
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
		},
		getGoodsData:function(contents,templateId,print_warehouse_position){
			var rows;
			var that = this;
			contents = JSON.parse(contents[templateId]);
			var templateURL = contents.custom_area_url;
			if(that.settings.intelligence_return_print_way == 1)
			{
				rows = $('#'+that.params.datagrid).datagrid('getSelections');
			}
			else
			{
				rows = $('#'+that.params.datagrid).datagrid('getRows');
			}
			if($.isEmptyObject(rows))
			{
				that.messagerAlert('请先选择需要打印的行!');
				return false;
			}
			var datas = [],row;
			var now_date = new Date();
			var now_millisecond = now_date.getTime();
			var ID = 0;
			var stock_in_warehouse = that.form_selector_list.stock_in_warehouse.combobox('getText');
			for (var j = 0; j < rows.length; ++j){
				row = rows[j];
				if(print_warehouse_position == null)
				{
					print_warehouse_position = [];
					stock_in_warehouse='';
				}
//				for (var k = 0; k < row.print_num; ++k)
//				{
					ID++;
					datas.push({
						'documentID' : now_millisecond.toString().concat(ID.toString()),
						'contents' : [
							{
								'templateURL' : templateURL,
								'data' : {
									goodsbarcode :{
										merchant_no : $.isEmptyObject(row.merchant_no)?'无':row.merchant_no,
										goods_no   : $.isEmptyObject(row.goods_no)?'无':row.goods_no,
										goods_name : $.isEmptyObject(row.goods_name)?'无':row.goods_name,
										spec_name : $.isEmptyObject(row.spec_name)?'无':row.spec_name,
										spec_code : $.isEmptyObject(row.spec_code)?'无':row.spec_code,
										short_name : $.isEmptyObject(row.short_name)?'无':row.short_name,
										barcode : $.isEmptyObject(row.barcode)?'无':row.barcode,
										is_suite : $.isEmptyObject(row.is_suite)?'无':row.is_suite,
										prop1 : $.isEmptyObject(row.prop1)?'无':row.prop1,
										prop2 : $.isEmptyObject(row.prop2)?'无':row.prop2,
										prop3 : $.isEmptyObject(row.prop3)?'无':row.prop3,
										prop4 : $.isEmptyObject(row.prop4)?'无':row.prop4,
										position_no : row['is_suite']=='是'?'':print_warehouse_position[row.spec_id],
										warehouse : row['is_suite']=='是'?'':stock_in_warehouse
									}
								}
							}
						]
					});
//				}
			}
			return datas;
		},
		printGoodsWarehouseInfo:function(){
			var rows;
			var that = this;
			if(that.settings.intelligence_return_print_way == 1)
			{
				rows = $('#'+that.params.datagrid).datagrid('getSelections');
			}
			else
			{
				rows = $('#'+that.params.datagrid).datagrid('getRows');
			}
			if($.isEmptyObject(rows))
			{
				that.messagerAlert('请先选择需要打印的行!');
				return false;
			}
			var printer = that.form_selector_list.printer_list.combobox('getValue');
			var templateId = that.form_selector_list.template_list.combobox('getValue');
			if(templateId == "")
			{
				that.messagerAlert("没有选择模板，请到模板列表页面下载模板");
				return ;
			}
			var stock_in_warehouse = that.form_selector_list.stock_in_warehouse.combobox('getValues');
			var rows_id_list = '';
			for(var i=0; i<rows.length; i++){
//				if(rows[i].is_suite == '否'){
					rows_id_list += rows[i].spec_id + ',';
//				}
			}
			rows_id_list = rows_id_list.substr(0,rows_id_list.length-1);
			$.post("<?php echo U('Goods/GoodsBarcodePrint/getPrintWarehousePosition');?>",{spec_ids:rows_id_list,warehouse_id:stock_in_warehouse},function(ret){
				var print_warehouse_position = ret;
				var contents = that.template_contents;
				var datas = that.getGoodsData(contents,templateId,print_warehouse_position);
				if(datas === false)
				{
					return;
				}
				that.connectStockWS();
				var requestID =  parseInt(1000*Math.random());
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
				intelligenceinWS.send(JSON.stringify(request));
			});

		},
		onPrinterSelect:function(printer_name){
			var that = this;
			var templateId = that.form_selector_list.template_list.combobox('getValue');
			var contents = that.template_contents;
			var content = contents[templateId];
			if(content.defaultPrinter != undefined && content.default_printer == printer_name)
				return;
			else
				messager.confirm("您确定把\""+printer_name+"\"设置为此模板的打印机么？",function(r){
					if(r){
						that.setDefaultPrinter(content,printer_name,templateId);
					}
				});
		},
		setDefaultPrinter:function(content,printor,templateId){
			var that = this;
			content = JSON.parse(content);
			content.default_printer = printor;
			$.post("<?php echo U('Goods/GoodsBarcodePrint/setDefaultPrinter');?>",{content:JSON.stringify(content),templateId:templateId},function(ret){
				if(1 == ret.status)
				{
					that.messagerAlert(ret.msg);
				}
				else
				{
					that.template_contents[templateId] = JSON.stringify(content);
				}
			});
		},
		templateOnSelect:function () {
			var that = this;
			if(that.form_selector_list.template_list.combobox('getData').length == 0)
			{
				return;
			}
			var print_list = that.form_selector_list.printer_list.combobox('getData');
			var content = JSON.parse(that.template_contents[that.form_selector_list.template_list.combobox('getValue')]);
			if(undefined != content.default_printer && JSON.stringify(print_list).indexOf(content.default_printer) != -1)
			{
				that.form_selector_list.printer_list.combobox('setValue',content.default_printer);
			}
		}
	}
	$(function(){
		var form = '<?php echo ($id_list["form"]); ?>';
		var form_id = '<?php echo ($id_list["form_id"]); ?>';
		IntelligenceReturnStockIn.form_selector_list = {
			'scan_search' 					: $('#'+form+' :input[name="intelligence_return_scan_search"]'),
			'scan_type' 					: $('#'+form+' :input[name="intelligence_return_scan_type"]'),
			'refund_info_logistics' 		: $('#'+form+' :input[name="refund_info_logistics"]'),
			'refund_info_buyer_nick' 		: $('#'+form+' :input[name="refund_info_buyer_nick"]'),
			'refund_info_receiver_mobile'	: $('#'+form+' :input[name="refund_info_receiver_mobile"]'),
			'refund_info_logistics_name'	: $('#'+form+' :input[name="refund_info_logistics_name"]'),
			'refund_info_reason'			: $('#'+form+' :input[name="refund_info_reason"]'),
			'refund_info_remark'			: $('#'+form+' :input[name="refund_info_remark"]'),
			'prompt_info'					: $('#'+form+' :input[name="prompt_info"]'),
			'refund_id'						: $('#'+form+' :input[name="refund_id"]'),
			'refund_type'					: $('#'+form+' :input[name="refund_type"]'),
			'printer_list'					: $('#'+form_id+' :input[name="printer_list"]'),
			'template_list'					: $('#'+form_id+' :input[name="template_list"]'),
			'stock_in_warehouse'			: $('#'+form_id+' :input[name="stock_in_warehouse"]'),
//			'refund_info_logistics_name' 				: $('#'+form_id+' a[name="consign_examine"]'),
//			'order_goods_num' 			: $('#'+form_id+' strong[name="order_goods_num"]'),
//			'scan_goods_num' 			: $('#'+form_id+' strong[name="scan_goods_num"]'),
//			'message_info' 				: $('#'+form_id+' strong[name="message_info"]'),
//			'print_examine_num'     	: $('#'+form_id+' strong[name="print_examine_num"]'),
		};
		setTimeout(function(){
			intelligenceReturnStockIn = new IntelligenceReturnStockIn(JSON.parse('<?php echo ($params); ?>'),JSON.parse('<?php echo ($setting); ?>'),IntelligenceReturnStockIn.form_selector_list);
			intelligenceReturnStockIn.connectStockWS();
			intelligenceReturnStockIn.form_selector_list.scan_search.textbox({onClickButton:function(){
				intelligenceReturnStockIn.submitScanSearch();
			}});
			intelligenceReturnStockIn.initShow();
			$('#'+intelligenceReturnStockIn.params.datagrid).datagrid('enableCellEditing');
			var interval = setInterval(function(){
				if(intelligenceinWS.readyState === 1)
				{
					intelligenceReturnStockIn.newSelectPrinter();
					clearInterval(interval);
				}
			}, 100);
		},0);
	});
})();
</script>