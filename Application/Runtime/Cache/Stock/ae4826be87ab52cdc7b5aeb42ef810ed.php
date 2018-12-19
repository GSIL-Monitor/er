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

<div id="<?php echo ($id_list["add"]); ?>"></div>
<div id="<?php echo ($id_list["edit"]); ?>"></div>

<!-- toolbar -->

<div id="<?php echo ($id_list["toolbar"]); ?>" style="padding:5px;height:auto">
<div class="form-div" style="border-bottom:  1px solid #7CAAB1">
<a href="javascript:void(0)" class="easyui-linkbutton" name="save" data-options="iconCls:'icon-save',plain:true">保存</a>
<a href="javascript:void(0)" class="easyui-linkbutton" name="reset" data-options="iconCls:'icon-redo',plain:true">重置</a>
<a href="javascript:void(0)" class="easyui-linkbutton" id="inmanage" name="inmanage" data-options="iconCls:'icon-next',plain:true" style="float: right;margin-right:10px;" onClick="open_menu('入库单管理', '<?php echo U('Stock/StockInManagement/getStockInSpec');?>')">入库单管理</a>
</div>
<form id="<?php echo ($id_list["form"]); ?>" class="easyui-form" method="post">
<div class="form-div">
<label>　入库原因：</label><input	class="easyui-combobox 	txt" 	name="src_order_type" 	data-options="panelHeight:'auto',valueField:'id',textField:'name',data:formatter.get_splice_list('stockin_reason','no',1,1)"/>
<label>　入库单号：</label><input 	class="easyui-textbox 	txt"  	name="stockin_no" 		data-options="editable:false,readonly:false,value:'',"/>
<label name = 'src_order_title'>　　原始单：</label><input  	class="easyui-textbox 	txt" 	name="src_order_no" 	data-options="editable:false,buttonText: '...'"  />
<label>　仓库：</label><select 	class="easyui-combobox 	sel" 	name="warehouse_id" 	data-options="panelHeight:'200px',editable:false,"><?php if(is_array($warehouse_list)): $i = 0; $__LIST__ = $warehouse_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?> </select>
<label>　物流单号：</label><input 	class="easyui-textbox 	txt" 	name="logistics_no"/>
</div>
<div class="form-div">
<label>　　原货款：</label><input		class="easyui-numberbox txt"	name="src_price" 		data-options="editable:false,readonly:true,value:0,precision:4"/>
<label>　入库总价：</label><input 	class="easyui-numberbox txt"	name="total_price" 		data-options="editable:false,readonly:true,value:0,precision:4"/>
<label>　　　优惠：</label><input 	class="easyui-numberbox txt"	name="discount" 		data-options="editable:false,readonly:true,value:0,precision:4"/>
<label>　邮费：</label><input 	class="easyui-numberbox txt"	name="post_fee" 		data-options="value:0.00,min:0,precision:4"/>
<label>　其他费用：</label><input 	class="easyui-numberbox txt"	name="other_fee" 		data-options="value:0.00,min:0,precision:4"/>
</div>
<div class="form-div">
<label>　　供货商：</label><input 	class="easyui-textbox txt"	name="provider" 		data-options="editable:false,readonly:true"/>
<label>　物流公司：</label><select 	class="easyui-combobox 	sel" 	name="logistics_id" 	data-options="editable:false,"> <option value="0">无</option> <?php if(is_array($list_logistics)): $i = 0; $__LIST__ = $list_logistics;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?> </select>
<label>　备注信息：</label><input 	class="easyui-textbox 	txt"	name="remark"　			data-options="width: 313"/>
</div>
</form>
<div class="form-div" style="border-top:  1px solid #7CAAB1;padding-top: 2px">
<a href="javascript:void(0)" class="easyui-menubutton" name="add_goods_info" data-options="iconCls:'icon-add',plain:true,menu:'#add_goods_info'" >添加货品</a>		
<a href="javascript:void(0)" class="easyui-linkbutton" name='copy_num'  data-options="plain:false">复制预期到货量</a>
<label>入库价类型</label>
<input	class="easyui-combobox txt" name="exercise_price" 		data-options="panelHeight:'auto',valueField:'id',textField:'name',data:formatter.get_list('list_price')"/>
<a 		class="easyui-linkbutton"	name="delete"	href="javascript:void(0)"  	data-options="iconCls:'icon-remove',plain:true">删除</a>
<label style="color:red;margin-left:80px;font-size:30px;display:none;" class="font_stoIn">保存成功</label>
<label style="color:red;margin-left:80px;font-size:30px;display:none;" class="font_stoIn_auto_commit">保存成功,并自动提交成功</label>
</div>
<div class="form-div" style="border-top:  1px solid #7CAAB1;padding-top: 2px">
<label >扫描条码：</label><input class="easyui-textbox txt" type="text" name="barcode" /><label style="color:red;margin-right: 5px;">(扫描条码添加货品)</label>
	<a href="javascript:void(0)" name="apply_to_all_lines" class="easyui-linkbutton" data-options="plain:false">应用到所有行</a>
	<label style="color:red;margin-left: 5px;">#应用到所有行支持[入库数量]、[原价]、[入库价]、[入库总价]列#</label>
</div>

<div id="add_goods_info"><a href="javascript:void(0)" class="easyui-linkbutton" name="add_goods" data-options="iconCls:'icon-add',plain:true">添加单品</a>
<a href="javascript:void(0)" class="easyui-linkbutton" name="add_suite" data-options="iconCls:'icon-add',plain:true">添加组合装</a>
</div>
</div>
	<audio id="stockin_barcode_notexist_sound">
		<source src="/Public/Image/barcode_notexist.wav" >
	</audio>
<script type="text/javascript">
//# sourceURL=<?php echo ($prefix); ?>stockin.js
(function(){
	var toolbar_id = '<?php echo ($id_list["toolbar"]); ?>';
	var element_selectors ={
		'barcode'			:$('#'+toolbar_id+" :input[name='barcode']"),
		'provider'			:$('#'+toolbar_id+" :input[name='provider']"),
		'save'		        :$('#'+toolbar_id+" a[name='save']"),
		'reset'		        :$('#'+toolbar_id+" a[name='reset']"),
		'apply_to_all_lines':$('#'+toolbar_id+" a[name='apply_to_all_lines']"),
		'inmanage'		    :$('#'+toolbar_id+" a[name='inmanage']"),
		'src_order_type'	:$('#'+toolbar_id+" :input[name='src_order_type']"),
		'stockin_no'		:$('#'+toolbar_id+" :input[name='stockin_no']"),
		'src_order_no'		:$('#'+toolbar_id+" :input[name='src_order_no']"),
		'warehouse_id'		:$('#'+toolbar_id+" select[name='warehouse_id']"),
		'src_price'			:$('#'+toolbar_id+" :input[name='src_price']"),
		'total_price'		:$('#'+toolbar_id+" :input[name='total_price']"),
		'discount'			:$('#'+toolbar_id+" :input[name='discount']"),
		'post_fee'			:$('#'+toolbar_id+" :input[name='post_fee']"),
		'other_fee'			:$('#'+toolbar_id+" :input[name='other_fee']"),
		'logistics_id'		:$('#'+toolbar_id+" select[name='logistics_id']"),
		'remark'			:$('#'+toolbar_id+" :input[name='remark']"),
		'exercise_price'	:$('#'+toolbar_id+" :input[name='exercise_price']"),
		'delete'		    :$('#'+toolbar_id+" a[name='delete']"),
		'add_goods'		    :$('#'+toolbar_id+" a[name='add_goods']"),
		'add_suite'		    :$('#'+toolbar_id+" a[name='add_suite']"),
		'copy_num'		    :$('#'+toolbar_id+" a[name='copy_num']"),
		'src_order_title'	:$('#'+toolbar_id+" label[name='src_order_title']"),
	};
	var productStockin = function(params,selectors){
		var self = this;
		this.params 		= params;
		this.datagrid_id 	= params.datagrid.id;
		this.form_id 		= params.form.id;
		this.toolbar_id 	= params.id_list.toolbar;
		this.selectors 		= selectors;
		this.order_id 		= params.id;
		this.change_column  = '';
		//初始化表单数据
		this.init_form_data = $('#'+this.form_id).form('get');
		this.stockin_type = this.init_form_data['src_order_type'];
		//toolbar中name list
		this.name_list = ['inmanage','barcode','provider','save','reset','src_order_type','stockin_no','src_order_no','warehouse_id','src_price','total_price','discount','post_fee','other_fee','logistics_id','remark','exercise_price','delete','add_goods','copy_num','add_suite'];
		this.name_type = ['linkbutton','textbox','textbox','linkbutton','linkbutton','combobox','textbox','textbox','combobox','numberbox','numberbox','numberbox','numberbox','numberbox','combobox','textbox','combobox','linkbutton','linkbutton','linkbutton','linkbutton'];
		//使用在重置上
		this.enable_type ={'1': ['add_goods','warehouse_id','add_suite'],
							'11': ['src_order_no','copy_num'],
							'3': ['src_order_no','copy_num','warehouse_id'],
							'6': ['add_goods','warehouse_id','add_suite']
							};
		//使用在编辑界面
		this.disable_type ={'1': [],
							'3': ['src_order_no','copy_num'],
							'6': []
							};
		this.disable_list = ['warehouse_id','add_goods','add_suite','stockin_no','src_price','provider','total_price','discount','src_order_no','copy_num'];
		//保存
		this.selectors.save.linkbutton({onClick:function(){
			self.save();
		}});
		//应用到所有行
		this.selectors.apply_to_all_lines.linkbutton({onClick:function(){
			self.applyToAllLines();
		}});
		//原始单号
		this.selectors.src_order_no.textbox({onClickButton:function(){
			self.addSrcOrder();
		}});
		//重置按钮
		this.selectors.reset.linkbutton({onClick:function(){
			self.resetDisplay();
		}});
		//添加货品按钮
		this.selectors.add_goods.linkbutton({onClick:function(){
			self.addGoods();
		}});
		this.selectors.add_suite.linkbutton({onClick:function(){
			self.add_suite();
		}});
		//复制到货量
		this.selectors.copy_num.linkbutton({onClick:function(){
			self.CopyNum();
		}});
		//扫描条码
		this.selectors.barcode.textbox('textbox').bind('keydown',function(e){
			if(e.keyCode==13){
				self.saomiao();
			}
		}); 
		
		//复制价格
		this.selectors.exercise_price.combobox({
			onSelect:function(record){var that = this;self.PriceType(record,that);}
		});
		//删除货品按钮
		this.selectors.delete.linkbutton({onClick:function(){
			self.deleteGoods();
		}});
		//选择入库类型
		this.selectors.src_order_type.combobox({
			onSelect:function(record){self.selectType(record);}
		});
		//添加field字段事件
		var options = $('#'+this.datagrid_id).datagrid('options');
		for(var i in options.columns[0])
		{
			if(options.columns[0][i].field == 'position_no')
			{
				options.columns[0][i].editor.options.onClickButton = function(){self.addPosition(this);};
			}
		}
//		$('#'+this.datagrid_id).datagrid(options);
		//datagrid监听事件
		$('#'+this.datagrid_id).datagrid('options').onAfterEdit = function(index, row, changes){self.afterEditRow(index, row, changes);};
		$('#'+this.datagrid_id).datagrid('options').onClickCell = function(index,field,value){self.onClickCell(index,field,value)};
		//单元格编辑模式 先后顺序必须保持
		$('#'+this.datagrid_id).datagrid().datagrid('enableCellEditing');
		this.keepClickCell = $('#'+this.datagrid_id).datagrid('options').onClickCell;
		$('#'+this.params.datagrid.id).datagrid('options').erpTabObject = this;
		$('#'+this.form_id).form('filterLoad',this.init_form_data);
		if(!this.order_id){self.initByType();}

	}
	productStockin.prototype = {
		/**
		 * [initStoInOrdForm ]
		 * @param  {[str]} type ['1':快速采购入库，'3':退换入库，'6':其他退货入库]
		 * @param  {[str]} toolbar_form_id   [toolbar中的from的id]
		 * @return {[str]}   father_datagrid_id   [添加到父窗口的datagrid的id]
		 */
		initByType : function(type) {
			this.permitOperate();
			if(!type){type = this.stockin_type;}
			var init_data = $.extend(true,{},this.init_form_data);
			var	form_data = $('#'+this.form_id).form('get');

			init_data['src_order_type'] = type;
			init_data['warehouse_id'] = form_data['warehouse_id'];
			init_data['logistics_id'] = form_data['logistics_id'];
			this.stockin_type = type;
			this.disableForm();
			switch (type) {
				case '1':
					this.enableForm(1);
                    this.selectors.src_order_title.html('　　原始单：');
                    this.selectors.src_order_no.textbox({'prompt':'',required:false});
                    break;
				case '11':
					this.enableForm(11);
                    this.selectors.src_order_title.html('　　采购单：');
                    this.selectors.src_order_no.textbox({'prompt':'点击右侧按钮',required:true,missingMessage:'单击按钮添加采购单'});
                    break;
				case '3':
					this.enableForm(3);
                    this.selectors.src_order_title.html('　　退换单：');
                    this.selectors.src_order_no.textbox({'prompt':'点击右侧按钮',required:true,missingMessage:'单击按钮添加退换单'});
                    break;
				case '6':
					this.enableForm(6);
                    this.selectors.src_order_title.html('　　原始单：');
                    this.selectors.src_order_no.textbox({'prompt':'',required:false});

                    break;
			}
			$('#' + this.datagrid_id).datagrid('loadData', {'total': 0, 'rows': []});
			$('#' + this.form_id).form('filterLoad', init_data);

		},
		
		//扫描入库
		saomiao : function(){
			
			var that = this;
			var stockin_warehouse = that.selectors.warehouse_id.combobox('getValue');
			var order_status = that.selectors.src_order_type.combobox('getValue');
			var barcode_value = that.selectors.barcode.textbox('getValue');
			if(barcode_value == ''){
				messager.alert('条形码不能为空',undefined,function(){
					that.selectors.barcode.textbox('textbox').focus();
				});
				return;
			}
			var barcode_list = $('#' + that.datagrid_id).datagrid('getRows');
			if((order_status == 11 || order_status == 3)&& barcode_list == ''){
				messager.alert('请先选择原始单',undefined,function(){
					that.selectors.barcode.textbox('textbox').focus();
				});
				that.selectors.barcode.textbox('setValue','');
				return;
			}
			
			$.post('<?php echo U("StockInOrder/getBarcodeInfo");?>',{barcode:barcode_value,warehouse_id:stockin_warehouse},function(res){
				if(res.status == 1){
                    if(res.info == '没有该条形码'){
				        $("#stockin_barcode_notexist_sound")[0].play();
				    }
                    messager.alert(res.info,undefined,function(){
						that.selectors.barcode.textbox('textbox').focus();
					});
				}else{
					that.goods_list = res.info;
					if(res.info.length > 1){
						$('#flag_set_dialog').dialog({
							title:that.params.select.title,
							iconCls:'icon-save',
							width:that.params.select.width==undefined?764:that.params.select.width,
							height:that.params.select.height==undefined?560:that.params.select.height,
							closed:false,
							inline:true,
							modal:true,
							href:that.params.select.url+'?parent_datagrid_id='+that.params.datagrid.id+'&parent_object=stockin&goods_list_dialog=flag_set_dialog',
							buttons:[]
						});
					}else{
						that.updateScanGoodsNum(res.info[0]);
					}
					
				}
			});
			that.selectors.barcode.textbox('setValue','');
		},
		/*
		 * Object {value: "1", text: "采购入库", selected: false, disabled: false}
		 */
		 updateScanGoodsNum : function(row){
			var that = this;
			var is_suite = row.is_suite;
			var target_id = row.target_id;
			var stockin_warehouse_id = that.selectors.warehouse_id.combobox('getValue');
			$.post("<?php echo U('Stock/StockInOrder/getGoodsInfo');?>",{is_suite:is_suite,target_id:target_id,warehouse_id:stockin_warehouse_id},function(r){
				if(r.status == 1){
					messager.alert(r.info);
					return;
				}
				for(var goods_info in r.info){
					that.updateGoodsNum(r.info[goods_info]);
				}
			});
		 },
		 
		 updateGoodsNum : function(row){
			var that = this;
			var order_status = that.selectors.src_order_type.combobox('getValue');
			var barcode_list = $('#' + that.datagrid_id).datagrid('getRows');
			var options = $('#'+this.datagrid_id).datagrid('options');
			for(var i in options.columns[0])
			{
				if(options.columns[0][i].field == 'num')
				{
					var decimal_amount  = options.columns[0][i].editor.options.precision;
				}
			}
			if(order_status == 11 || order_status == 3){
				var is_exist = 0;
				for(var i in barcode_list){
					if(barcode_list[i].spec_id == row.spec_id){
						$('#'+that.datagrid_id).datagrid('updateRow',{
							index:parseInt(i),
							row:{num:(parseFloat(barcode_list[i].num)+1).toFixed(decimal_amount)}
						});
						$('#'+that.datagrid_id).datagrid('updateRow',{
							index:parseInt(i),
							row:{total_cost:(parseFloat(barcode_list[i].num))*(parseFloat(barcode_list[i].cost_price))}
						});
						that.calcPrice();
						is_exist = 1;
						break;
					}
				}
				if(is_exist == 0){
					messager.alert('你所扫描的货品不在该订单中',undefined,function(){
						that.selectors.barcode.textbox('textbox').focus();
					});
				}
			}else{
				var is_null = 0;//判断是否已经添加该数据
				if(barcode_list != ''){
					for(var i in barcode_list){
						if(barcode_list[i].spec_id == row.spec_id){
							$('#'+that.datagrid_id).datagrid('updateRow',{
								index:parseInt(i),
								row:{num:(parseFloat(barcode_list[i].num)+1).toFixed(decimal_amount)}
							});
							$('#'+that.datagrid_id).datagrid('updateRow',{
								index:parseInt(i),
								row:{total_cost:(parseFloat(barcode_list[i].num))*(parseFloat(barcode_list[i].cost_price))}
							});
							that.calcPrice();
							is_null = 1;
							break;
						}
					}
				}
				if(is_null == 0){
					$('#' + that.datagrid_id).datagrid('appendRow', row);
					that.calcPrice();
				}
			}
		 },
		applyToAllLines : function(){
			var that = this;
			var show_dg=$('#'+this.datagrid_id);
			var data=show_dg.datagrid('getData');
			var rows=data.rows;
			if(rows.length==0){return;}
			for(var i in rows){
				if (that.change_column.num!=undefined) {
					rows[i].num=that.change_column.num;
					show_dg.datagrid('updateRow',{index:parseInt(i), row:{num:that.change_column.num}});
				}
				if (that.change_column.src_price!=undefined) {
					rows[i].src_price=that.change_column.src_price;
					show_dg.datagrid('updateRow',{index:parseInt(i), row:{src_price:that.change_column.src_price}});
				}
				if (that.change_column.cost_price!=undefined) {
					rows[i].cost_price=that.change_column.cost_price;
					show_dg.datagrid('updateRow',{index:parseInt(i), row:{cost_price:that.change_column.cost_price}});
				}
				if (that.change_column.total_cost!=undefined) {
					rows[i].total_cost=that.change_column.total_cost;
					show_dg.datagrid('updateRow',{index:parseInt(i), row:{total_cost:that.change_column.total_cost}});
				}
			}
			//show_dg.datagrid('loadData',data);
		},

		selectType : function(record) {
			this.stockin_type = record['id'];
			this.initByType(this.stockin_type);
		},
		save : function(){
			var that = this;
            if(!$('#'+this.form_id).form('validate')){ return; }else{$('#'+this.datagrid_id).datagrid('endEdit',index);}
            var stockin_warehouse = that.selectors.warehouse_id.combobox('getValue');
			if(stockin_warehouse == null ||stockin_warehouse ==0 ){
				messager.alert('请选择入库仓库');
				return;
			}
			var checkRows = $('#' + this.datagrid_id).datagrid('getRows');
			if (this.stockin_type == undefined) {
				messager.alert('请选择入库单类别');
				return;
			}
			var link_a = '';
			if (checkRows.length == 0) {
			    if(this.stockin_type == 11){
			        link_a = '货品信息为空，是否重新引用采购单';
                }else if(this.stockin_type == 3){
                    link_a = '货品信息为空，是否重新引用退换单';
                }else{
                    link_a = '货品信息为空，是否添加货品信息';
                }
				messager.confirm(link_a,function(r){
				    if(r){
                        if(that.stockin_type == 11 || that.stockin_type == 3){
                            that.addSrcOrder();
                        }else{
                            that.addGoods();
                        }
                    }
                });
				return;
			}
			var rows = $('#'+this.datagrid_id).datagrid('getRows');
			var detail_info = [];
			var total_num = 0;
			for(var i in rows){
				var index = $('#'+this.datagrid_id).datagrid('getRowIndex',rows[i]);
				if(!$('#'+this.datagrid_id).datagrid('validateRow',index)) { messager.alert('请填写完整货品信息!'); return; }

				if(rows[i].num <=0){
					detail_info.push({spec_no:rows[i].spec_no,info:'入库数量不能为无!'})
				}
				 total_num += parseInt(rows[i].num);  
			}
			if(!$.isEmptyObject(detail_info)){ $.fn.richDialog("response", detail_info, "goods_spec"); return; }
			this.enableForm();
			var data = {};
			var form_data = $('#'+this.form_id).form('get');
			if(!!this.order_id){
				form_data['id'] = this.order_id;
			}
			form_data['total_num'] = total_num;
			data['form'] = $.extend(true,{},form_data);
			data['rows'] = {};
			data['rows']['update'] = $('#'+this.datagrid_id).datagrid('getChanges','updated');
			data['rows']['delete'] = $('#'+this.datagrid_id).datagrid('getChanges','deleted');
			data['rows']['insert'] = $('#'+this.datagrid_id).datagrid('getChanges','inserted');
			$('#'+this.datagrid_id).datagrid('loading');
			$.post(this.params.form.url,{data:JSON.stringify(data)},function(res){
				$('#'+that.datagrid_id).datagrid('loaded');
				if(res.status == 1){
					if(!$.isEmptyObject(res.data)){
						$.fn.richDialog("response", res.data, "goods_spec");
					}else{
						messager.alert(res.info+'或重置重试!');
						that.forbidOperate();
					}
				} else if (res.status == 2){
					$('.font_stoIn').show();
					that.forbidOperate();
					$('#'+that.form_id).form('filterLoad',res.data);
					$('#'+that.datagrid_id).datagrid('options').onClickCell = function(){return;};
					if(!!that.params.parent_win){
						$('#'+that.params.parent_win.dialog_id).dialog('close');
						$('#'+that.params.parent_win.datagrid_id).datagrid('reload');
					}
					messager.alert(res.info);
				}else {
					//messager.info(res.info);
					if(res.stockin_auto_commit_cfg == 1){$('.font_stoIn_auto_commit').show();}else{$('.font_stoIn').show();}
					that.forbidOperate();
					$('#'+that.form_id).form('filterLoad',res.data);
					$('#'+that.datagrid_id).datagrid('options').onClickCell = function(){return;};
					if(!!that.params.parent_win){
						$('#'+that.params.parent_win.dialog_id).dialog('close');
						$('#'+that.params.parent_win.datagrid_id).datagrid('reload');
					}
				}
			},'json');
		},
		resetDisplay : function()
		{
			this.initByType();
			$('.font_stoIn').hide();
			$('.font_stoIn_auto_commit').hide();
			this.selectors.barcode.textbox('setValue','');
			$('#'+this.datagrid_id).datagrid('options').onClickCell = this.keepClickCell;
		},
		addSrcOrder : function () {
			var that = this;
			if (this.stockin_type == undefined || (this.stockin_type != '3' && this.stockin_type != '11')) {
				messager.alert("入库单类型不正确");
				return;
			}
			var buttons=[ {text:'确定',handler:function(){ that.submitAddDialog(); }}, {text:'取消',handler:function(){that.cancelDialog(that.params.add[that.stockin_type].id)}} ];
			Dialog.show(this.params.add[this.stockin_type].id,this.params.add[this.stockin_type].title,this.params.add[this.stockin_type].url,this.params.add[this.stockin_type].height,this.params.add[this.stockin_type].width,buttons);
		},
		cancelDialog: function(id){
			messager.confirm('您确定要关闭吗？', function(r){ if (r){$('#'+id).dialog('close');}});
		},
		submitAddDialog : function () {
			switch (this.stockin_type)
			{
				case '3':{
					stockinSubmitAddTradeRefund(this);
					break;
				}
				case '11':{
					stockinSubmitAddPurchaseOrder(this);
					break;
				}
			}

		},
		calcPrice : function()
		{
			var rows = $('#'+this.datagrid_id).datagrid('getRows');
			var form_price = {src_price:0,total_price:0,discount:0};
			for(var i in rows)
			{
				form_price.src_price += parseFloat(rows[i].num)*parseFloat(rows[i].src_price);
				form_price.total_price += parseFloat(rows[i].total_cost);
			}
			form_price.discount = form_price.src_price-form_price.total_price;

			form_price.src_price = parseFloat(form_price.src_price).toFixed(4);
			form_price.total_price = parseFloat(form_price.total_price).toFixed(4);
			form_price.discount = parseFloat(form_price.discount).toFixed(4);
			$('#'+this.form_id).form('filterLoad',form_price);
		},
		afterEditRow : function (index, row, changes) {
			this.change_column=changes;
			if(changes.total_cost!=undefined)
			{
				if(row.num > 0)
				{
					row.cost_price = parseFloat(changes.total_cost/row.num).toFixed(4);
				}
			}
			if(changes.num != undefined || changes.cost_price !=undefined)
			{
				row.total_cost = parseFloat(row.cost_price*row.num).toFixed(4);
			}
			$('#'+this.datagrid_id).datagrid('updateRow',{index:parseInt(index),row:row});
			this.calcPrice();
		},
		WhOnChange :function(newValue,oldValue,editor_p){
			var that =this;
			if(this.sel_warehouse == newValue){
				return;
			}
			var wh_id = this.selectors.warehouse_id.combobox('getValue');
			var detail_rows = $('#'+this.datagrid_id).datagrid('getRows');
			if($.isEmptyObject(detail_rows)){
				that.sel_warehouse = newValue;
				return;
			}
			var update_r = $.data($('#'+that.datagrid_id)[0],'datagrid').updatedRows;
			var insert_r = $.data($('#'+that.datagrid_id)[0],'datagrid').insertedRows;

			var spec_info = {};
			$.each(detail_rows,function(i,row){spec_info[i]=row['spec_id']});
			$.post(that.params.warehouse.url,{warehouse_id:wh_id,detail:spec_info},function(res){
				if(res.status){
					$(editor_p).combobox('setValue',oldValue);
					messager.alert(res.info);
				}else{
					that.sel_warehouse = newValue;
					for(var i in detail_rows)
					{
						if(that.isset(insert_r,detail_rows[i])==-1)
						{
							if(that.isset(update_r,detail_rows[i])==-1)
							{
								update_r.push(detail_rows[i]);
							}
						}
						$('#'+that.datagrid_id).datagrid('updateRow',{index:parseInt(i),row:{is_allocated:res.data[i].is_allocated,src_price:res.data[i].src_price,position_id:res.data[i].position_id,position_no:res.data[i].position_no}});
					}
					that.calcPrice();
				}
			},'json');
		},

		addGoods : function()
		{
			if ( this.stockin_type == undefined || this.stockin_type == "3") {
				messager.alert('当前入库原因不符合添加条件');
				return;
			}
			var stockin_warehouse = this.selectors.warehouse_id.combobox('getValue');
			if(stockin_warehouse == null ||stockin_warehouse ==0 ){
				messager.alert('请选择入库仓库');
				return;
			}else{
				this.sel_warehouse = stockin_warehouse;
			}
			if($.isEmptyObject($.trim(this.sel_warehouse)) ||  $.trim(this.sel_warehouse) == '' || this.sel_warehouse ==0){
				messager.alert('获取仓库失败,请先新建仓库');
				return;
			}

			$('#' + this.params.select.id).richDialog('goodsSpec', this.submitGoodsSpecDialog,{
				'prefix':'stockin',
				'type' : true,
				'warehouse_id':this.sel_warehouse,
				'model':'stock'
			},this);
		},
		add_suite : function()
		{
			if ( this.stockin_type == undefined || this.stockin_type == "3") {
				messager.alert('当前入库原因不符合添加条件');
				return;
			}
			var stockin_warehouse = this.selectors.warehouse_id.combobox('getValue');
			if(stockin_warehouse == null ||stockin_warehouse ==0 ){
				messager.alert('请选择入库仓库');
				return;
			}else{
				this.sel_warehouse = stockin_warehouse;
			}
			if($.isEmptyObject($.trim(this.sel_warehouse)) ||  $.trim(this.sel_warehouse) == '' || this.sel_warehouse ==0){
				messager.alert('获取仓库失败,请先新建仓库');
				return;
			}
			$('#'+this.params.id_list.edit).richDialog('goodsSuite', this.addSuiteinfo, 'add_stockin', this);
		},
		
		CopyNum : function() {
			var datagrid_id = this.datagrid_id;
			var rows = $('#' + datagrid_id).datagrid('getRows');

			var update_r = $.data($('#'+this.datagrid_id)[0],'datagrid').updatedRows;
			var insert_r = $.data($('#'+this.datagrid_id)[0],'datagrid').insertedRows;
			var options = $('#'+this.datagrid_id).datagrid('options');
			for(var i in options.columns[0])
			{
				if(options.columns[0][i].field == 'num')
				{
					var decimal_amount  = options.columns[0][i].editor.options.precision;
				}
			}
			for (var i in rows) {
				var index = $('#' + datagrid_id).datagrid('getRowIndex', rows[i]);
				var expect_num = parseFloat(rows[i]['expect_num']);
				var update_num = {};
				if(expect_num == undefined || typeof(expect_num) != 'number' || expect_num == 0)
				{
					update_num = {num:parseFloat(0).toFixed(decimal_amount),total_cost:0.0000};
				}else{
					update_num = {num:parseFloat(rows[i]['expect_num']).toFixed(decimal_amount),total_cost:parseFloat(rows[i]['expect_num']*rows[i]['cost_price']).toFixed(4)};
				}
				if(rows[i].num != update_num.num)
				{
					if(this.isset(insert_r,rows[i])==-1)
					{
						if(this.isset(update_r,rows[i])==-1)
						{
							update_r.push(rows[i]);
						}
					}
				}
				$('#' + datagrid_id).datagrid('updateRow', {index:parseInt(i),row:update_num});

			}
			this.calcPrice();
		},
		PriceType : function(record,that)
		{
			var value = $(that).combobox('getValue');
			var text = $(that).combobox('getText');
			var datagridId = this.datagrid_id;
			if (value == "temp") {
				return;
			}
			else {
				var update_r = $.data($('#'+this.datagrid_id)[0],'datagrid').updatedRows;
				var insert_r = $.data($('#'+this.datagrid_id)[0],'datagrid').insertedRows;

				var rows = $('#' + datagridId).datagrid('getRows');
				var update_price = {};
				for (var i in rows) {
					var index = $('#' + datagridId).datagrid('getRowIndex', rows[i]);
					if(text == '0.0000' || value == 'all') {
						update_price = {cost_price:0.0000.toFixed(4),total_cost:0.0000.toFixed(4)};
					}else{
						update_price = {cost_price:parseFloat(rows[i][value]).toFixed(4),total_cost:parseFloat(rows[i]['num']*rows[i][value]).toFixed(4)};
					}
					if(rows[i].cost_price != update_price.cost_price)
					{
						if(this.isset(insert_r,rows[i])==-1)
						{
							if(this.isset(update_r,rows[i])==-1)
							{
								update_r.push(rows[i]);
							}
						}
					}
					$('#' + datagridId).datagrid('updateRow', {index:parseInt(i),row:update_price});

				}
			}
			$(that).combobox('setValue', "temp");
			$(that).combobox('setText', text);
			this.calcPrice();
		},
		isset:function(rows,t){
		for(var i=0,len=rows.length;i<len;i++){
			if(rows[i]==t){
				return i;
			}
		}
		return -1;
		},
		deleteGoods : function() {
			var check_rows = $('#' + this.datagrid_id).datagrid('getRows');
			var selected_row = $('#' + this.datagrid_id).datagrid('getSelected');
			var selected_index 	= $('#' + this.datagrid_id).datagrid("getRowIndex", selected_row);

			if (this.stockin_type == undefined) {
				messager.alert('请选择入库单类别');
				return;
			}
			if (check_rows.length == 0) {
				messager.alert('暂无货品信息');
				return;
			}
			if ($.isEmptyObject(selected_row) || selected_row.length == 0) {
				messager.alert('请选择行');
				return;
			}
			$('#' + this.datagrid_id).datagrid("deleteRow", selected_index);
			this.calcPrice();
		},
		
		
		addSuiteinfo : function(uId,stockinObject,dId){
			var that = this;
			var add_rows = $("#"+uId).datagrid("getSelected");
			var row = {};
			row.is_suite = 2;
			row.target_id = add_rows.id;
			stockinObject.updateScanGoodsNum(row);
			$('#'+stockinObject.params.id_list.edit).dialog('close');
			
		}, 
		
		submitGoodsSpecDialog : function(uId,dId,stockinObject)
		{
			var merge_result_new = [];

			//获取对话框中的添加的数据
			var add_rows = $("#"+dId).datagrid("getRows");
			var gs_format_rows = utilTool.array2dict(add_rows,'id','');
			//获取入库开单中原有数据
			var sio_datagrid_id = stockinObject.datagrid_id;

			var sio_rows = $('#' + sio_datagrid_id).datagrid('getRows');
			var sio_format_rows = utilTool.array2dict(sio_rows,'id','');
			//过滤重复的单品列表
			for (var j in gs_format_rows) {
				if (sio_format_rows[j] == undefined) {
//					this.formatterNum(gs_format_rows[j],['num','src_price','total_price','expect_num','retail_price','cost_price','lowest_price','market_price']);
					merge_result_new.push(gs_format_rows[j]);
				}
			}
			for(var new_key in merge_result_new){
				$('#' + sio_datagrid_id).datagrid('appendRow', merge_result_new[new_key]);
			}
			$('#' + stockinObject.params.select.id).dialog('close');
			stockinObject.calcPrice();
		},
		onClickCell:function(index,field,value){
			if(field=='position_no'){
				var rows = $('#'+this.datagrid_id).datagrid('getRows');
				var ed = $('#'+this.datagrid_id).datagrid('getEditor',{index:parseInt(index),field:field});
				if((rows[index]['is_allocated'] == 1 || rows[index]['position_id'] == 0 ) ){
					$(ed.target).textbox('readonly');
					$(ed.target).textbox('releaseFocus');
					$(ed.target).textbox('textbox').css({'background-color':'#C5C5C5'});
					$(ed.target).textbox('textbox').siblings('a').unbind('mousedown');
				}

			}
		},
		addPosition : function(editor_p){
			var that = this;
			if (this.stockin_type == "all" || this.stockin_type == undefined ) {
				messager.alert('当前入库原因不符合添加条件');
				return;
			}
			var stockin_warehouse = this.selectors.warehouse_id.combobox('getValue');
			if(stockin_warehouse == null ||stockin_warehouse ==0 ){
				this.submit_switch = 1;
				messager.alert('请选择入库仓库');
				return;
			}else{
				this.sel_warehouse = stockin_warehouse;
			}
			if($.isEmptyObject($.trim(this.sel_warehouse)) ||  $.trim(this.sel_warehouse) == '' || this.sel_warehouse ==0){
				messager.alert('获取仓库失败,请先新建仓库');
				return;
			}

			$('#' + this.params.select.id).richDialog('warehousePosition', this.submitDialogWarehousePosition,{
				'prefix':'stockin',
				'warehouse_id':this.sel_warehouse,
			},{m_p:that,editor_p:editor_p});
		},

		submitDialogWarehousePosition : function(up_d,d_d,form_id,params){
			var up_sel_row = $('#'+up_d).datagrid('getSelected');
			var down_rows = $('#'+d_d).datagrid('getRows');
			var form_info = $('#'+form_id).form('get');
			var row = $('#' + params.m_p.params.datagrid.id).datagrid('getSelected');
			var index = $('#' + params.m_p.params.datagrid.id).datagrid('getRowIndex',row);
			var update_r = $.data($('#'+params.m_p.params.datagrid.id)[0],'datagrid').updatedRows;
			var insert_r = $.data($('#'+params.m_p.params.datagrid.id)[0],'datagrid').insertedRows;
			if(form_info.has_other && down_rows.length!=0){
				messager.confirm('该货位不存在该货品,存在其他货品!是否确定选择',function(r){
					if(r){
						$(params.editor_p).textbox('setValue',up_sel_row['position_no']);
						if(row.position_id != up_sel_row['position_id'])
						{
							if(params.m_p.isset(insert_r,row)==-1)
							{
								if(params.m_p.isset(update_r,row)==-1)
								{
									update_r.push(row);
								}
							}
						}
						row.position_id =up_sel_row['id'];
//						$('#' + params.m_p.params.datagrid.id).datagrid('updateRow',{index:index,});
						$('#' + params.m_p.params.select.id).dialog('close');
                        $(params.editor_p).textbox('releaseFocus');
					}else{
						return;
					}
				});
			}else{
				$(params.editor_p).textbox('setValue',up_sel_row['position_no']);
				if(row.position_id != up_sel_row['position_id'])
				{
					if(params.m_p.isset(insert_r,row)==-1)
					{
						if(params.m_p.isset(update_r,row)==-1)
						{
							update_r.push(row);
						}
					}
				}
				row.position_id =up_sel_row['id'];
//				$('#' + params.m_p.params.datagrid.id).datagrid('updateRow',{index:index,row:{position_id:up_sel_row['id']}});
				$('#' + params.m_p.params.select.id).dialog('close');
                $(params.editor_p).textbox('releaseFocus');
			}


		},
		forbidOperate:function(){
			$('#'+this.toolbar_id+" :input").attr('disabled',true);
			this.disableForm(undefined,['warehouse_id','logistics_id','src_order_type','exercise_price']);
			var a_dom_ar = $('#'+this.toolbar_id+' a[class~="easyui-linkbutton"]');
			$.each(a_dom_ar,function(i,a_dom){
				$(a_dom).linkbutton({'disabled':true});
			});
			this.selectors.add_suite.linkbutton({'disabled':true});
			this.selectors.add_goods.linkbutton({'disabled':true});
			this.selectors.reset.linkbutton({'disabled':false});
			this.selectors.inmanage.linkbutton({'disabled':false});
		},
		permitOperate:function(){
			$('#'+this.toolbar_id+" :input").attr('disabled',false);
			this.enableForm(undefined,['warehouse_id','logistics_id','src_order_type','exercise_price']);
//			this.selectors.warehouse_id.combobox('enable');
			var a_dom_ar = $('#'+this.toolbar_id+' a[class~="easyui-linkbutton"]');
			$.each(a_dom_ar,function(i,a_dom){
				$(a_dom).linkbutton({'disabled':false});
			});
		},
		disableForm : function(type,list){
			var disable_list = (!type)?this.disable_list:this.enable_type[type];
			(!!list)?disable_list = $.extend(true,[],list):'';
			for(var i in disable_list){
				var form_i = $.inArray(disable_list[i],this.name_list)
				var form_type = this.name_type[form_i];
				if(form_type == 'combobox'){
					this.selectors[disable_list[i]][form_type]('disable');
				}else{
					this.selectors[disable_list[i]][form_type]({disabled:true});
				}
			}
		},
		enableForm	: function(type,list){
			var enable_list = (!type)?this.disable_list:this.enable_type[type];
			(!!list)?enable_list = $.extend(true,[],list):'';
			for(var i in enable_list){
				var form_i = $.inArray(enable_list[i],this.name_list)
				var form_type = this.name_type[form_i];
				if(form_type == 'combobox'){
					this.selectors[enable_list[i]][form_type]('enable');
				}else{
					this.selectors[enable_list[i]][form_type]({disabled:false});
				}

			}
		},
		loadEditInfo : function () {
			var that = this;
			$.post('<?php echo U("StockInManagement/getEditInfo");?>',{id:this.order_id},function(res){
				if(res.status== 1){
					messager.alert(res.info);
				}else{
					that.stockin_type = res.data.form_data.src_order_type;
					that.temp = res.data.form_data;
					that.initByType();
					$('#'+that.form_id).form('filterLoad',res.data.form_data);
					$('#'+that.datagrid_id).datagrid('loadData',res.data.detail_data);
				}
			},'json');
		},
	}
	$(function(){
		
		element_selectors.warehouse_id.combobox({editable:false, required:true,onChange:function(newValue,oldValue){stockin.WhOnChange(newValue,oldValue,this);}});
		setTimeout(function(){
			stockin = new productStockin(JSON.parse('<?php echo ($params); ?>'),element_selectors)
			if(!!stockin.order_id){
				stockin.disable_list = ['inmanage','reset','src_order_type','stockin_no','src_price','total_price','provider','discount','src_order_no','copy_num','delete','add_goods','add_suite'];
				stockin.enable_type ={
					'1': ['add_goods','delete','add_suite'],
					'3': ['copy_num','delete'],
					'6': ['add_goods','delete','add_suite']
				};
				//关闭注册的发货仓库选择事件
				stockin.loadEditInfo();
			}
			stockin.selectors.barcode.textbox('textbox').focus();
		},0);
	});
})()
var stockin = {};
</script>

<!-- layout-south-tabs, call add_tabs js function to add tabs -->


</body>
</html>