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

    <div id="<?php echo ($id_list["dialog_purchase_spec"]); ?>"></div>
	

<!-- toolbar -->

<div id="<?php echo ($id_list["toolbar"]); ?>" style="padding:5px;height:auto">
<!--提交-->
<div class="form-div" style="border-bottom:  1px solid #7CAAB1">
    <a href="javascript:void(0)" class="easyui-linkbutton" name="button_submit" data-options="iconCls:'icon-save',plain:true">保存</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" name="button_reset" data-options="iconCls:'icon-redo',plain:true">重置</a>
<a href="javascript:void(0)" class="easyui-linkbutton" name="purchasemanage" data-options="iconCls:'icon-next',plain:true" style="float: right;margin-right:10px;" >采购单管理</a>
</div>
<!--form-->
<div>
<form id="<?php echo ($id_list["form"]); ?>" method="post">
<div>

<div style="display: inline-block;vertical-align:middle">

<div class="form-div">
<label>供应商：</label><select class="easyui-combobox sel" name="search[provider_id]"  ><?php if(is_array($list["provider"])): $i = 0; $__LIST__ = $list["provider"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?></select>
<label>　收货仓库：</label><select class="easyui-combobox sel" name="search[warehouse_id]"  ><?php if(is_array($list["warehouse"])): $i = 0; $__LIST__ = $list["warehouse"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?></select>
<label>　联系人：</label><input class="easyui-textbox sel" type="text" name="search[contact]" />
<label>　联系电话：</label><input class="easyui-textbox sel" type="text" name="search[telno]" data-options="validType:'mobileAndTel'" />
<label>　　收货地址：</label><input class="easyui-textbox sel" type="text" name="search[address]"  style="width:300px" />
</div>
<div class="form-div">
<label>采购员：</label><select class="easyui-combobox sel" type="text" name="search[purchaser_id]" ><?php if(is_array($list["employee"])): $i = 0; $__LIST__ = $list["employee"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?></select>
<label>　物流方式：</label><select class="easyui-combobox sel" name="search[logistics_type]"  ><option value="0">无</option><?php if(is_array($list["logistics"])): $i = 0; $__LIST__ = $list["logistics"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?></select>
<label>　　邮资：</label><input class="easyui-numberbox txt" type="text" name="search[post_fee]"  data-options="value:0.00,min:0,precision:4"/>
<label>　其他金额：</label><input class="easyui-numberbox txt" type="text"  name="search[other_fee]" data-options="value:0.00,min:0,precision:4"/>
<label>　采购总金额：</label><input class="easyui-numberbox txt"  type="text" name="search[amount]" data-options="editable:false,min:0,readonly:true,value:0,precision:4"/>
</div>
<div class="form-div">
<label>　备注：</label><input class="easyui-textbox sel" type="text" style="width:338px" name="search[remark]"/>
</div>
</div>

</div><!--form-div-->
</form>
</div>
<!-- operater datagrid-->
<div class="form-div" style="border-top:  1px solid #7CAAB1;padding-top: 2px;margin-top: 5px;">

<a href="javascript:void(0)" class="easyui-linkbutton" name="button_add_goods" data-options="iconCls:'icon-add',plain:true">添加货品</a>
<a href="javascript:void(0)" class="easyui-linkbutton" name="button_del_goods" data-options="iconCls:'icon-remove',plain:true">删除</a>
<label style="color:red;margin-left:80px;font-size:30px;display:none;" class="font_purchase">保存成功</label>
</div><!--toolbar-->
</div>
<script type="text/javascript">
//# sourceURL=purchase.js
	var toolbar_id = '<?php echo ($id_list["toolbar"]); ?>';
	var supplier_provider_id = "<?php echo ($list['provider'][0]['id']); ?>";
	var is_dialog = "<?php echo ($is_dialog); ?>";
    var element_selectors ={
		'purchasemanage'			:$('#'+toolbar_id+" a[name='purchasemanage']"),
        'provider_id'		        :$('#'+toolbar_id+" select[name='search[provider_id]']"),
        'warehouse_id'		        :$('#'+toolbar_id+" select[name='search[warehouse_id]']"),
         'contact'		   		    :$('#'+toolbar_id+" :input[name='search[contact]']"),
        'remark'		            :$('#'+toolbar_id+" :input[name='search[remark]']"),
        'telno'		                :$('#'+toolbar_id+" :input[name='search[telno]']"),
        'address'			        :$('#'+toolbar_id+" :input[name='search[address]']"),
        'purchaser_id'		        :$('#'+toolbar_id+" select[name='search[purchaser_id]']"),
        'logistics_type'		    :$('#'+toolbar_id+" select[name='logistics_type']"),
        'post_fee'		            :$('#'+toolbar_id+" :input[name='search[post_fee]']"),
        'other_fee'	            :$('#'+toolbar_id+" :input[name='search[other_fee]']"),
		'amount'	                :$('#'+toolbar_id+" :input[name='search[amount]']"),
		'button_add_goods'	    :$('#'+toolbar_id+" a[name='button_add_goods']"),
        'button_del_goods'	    :$('#'+toolbar_id+" a[name='button_del_goods']"),
		'button_submit'		    :$('#'+toolbar_id+" a[name='button_submit']"),
        'button_reset'		        :$('#'+toolbar_id+" a[name='button_reset']"),
       
    };
	var PurchaseOrderTool = function(params,element_selectors){
		var tool_self = this;
		this.params = params;
		this.datagrid_id = this.params.datagrid;
		
		this.form_id = this.params.form;
		this.element_selectors = element_selectors;
		this.toolbar_id = this.params.id_list.toolbar;
		this.order_id = this.params.order_id;
		this.init_form_data = $('#'+this.form_id).form('get');
		this.element_selectors.amount.numberbox({'disabled':true});
	
		this.form_type_map = {
			'purchasemanage':'linkbutton','post_fee':'numberbox','other_fee':'numberbox','provider_id':'combobox','purchaser_id':'combobox','logistics_type':'combobox','amount':'numberbox','warehouse_id':'combobox', 'contact':'textbox', 'telno':'textbox', 'address':'textbox','remark':'textbox','button_submit':'linkbutton','button_reset':'linkbutton','button_add_goods':'linkbutton','button_del_goods':'linkbutton'
		};	
		this.disabled_input_map ={
           'post_fee':1,'other_fee':1,'provider_id':1,'purchaser_id':1,'logistics_type':1,'amount':1,'warehouse_id':1, 'contact':1, 'telno':1, 'address':1,'remark':1,'button_submit':1,'button_reset':1,'button_add_goods':1,'button_del_goods':1
		};
        //记录需要禁用的表单映射
        this.enable_input_map ={
           'post_fee':1,'other_fee':1,'provider_id':1,'purchaser_id':1,'logistics_type':1,'amount':1,'warehouse_id':1, 'contact':1, 'telno':1, 'address':1,'remark':1,'button_submit':1,'button_reset':1,'button_add_goods':1,'button_del_goods':1
		 };
		 //注册事件
        //保存按钮
        this.element_selectors.button_submit.linkbutton({onClick:function(){
            tool_self.submitPurchaseOrder();
        }});
		
        //重置按钮
        this.element_selectors.button_reset.linkbutton({onClick:function(){
            tool_self.resetDisplay();
        }});
        //添加货品按钮
        this.element_selectors.button_add_goods.linkbutton({onClick:function(){
            tool_self.addGoods();
        }});
        //删除货品按钮
        this.element_selectors.button_del_goods.linkbutton({onClick:function(){
            tool_self.delGoods();
        }});
		//邮费变动函数
		this.element_selectors.post_fee.numberbox({onChange:function(newValue,oldValue){
            var that = this;
			tool_self.setPostFee(newValue,oldValue,that);
        }});
		//其他费用变动函数
		this.element_selectors.other_fee.numberbox({onChange:function(newValue,oldValue){
            var that = this;
			tool_self.setOtherFee(newValue,oldValue,that);
        }});
		this.element_selectors.purchasemanage.linkbutton({onClick:function(){
			open_menu('采购单管理', '<?php echo U('Purchase/PurchaseManagement/show');?>');
			if(is_dialog=='alarmPurchaseDialog'){
				$('#reason_show_dialog').dialog('close');
			}
		}});
		$('#'+this.datagrid_id).datagrid('options').onEndEdit = function(index, row, changes){tool_self.endEditRow(index, row, changes,this);};
		//单元格编辑模式 先后顺序必须保持
        $('#'+this.datagrid_id).datagrid().datagrid('enableCellEditing');
        this.keepClickCell = $('#'+this.datagrid_id).datagrid('options').onClickCell;
        if(!! this.params.order_type)
        {
            if(this.params.order_type == 'alarm_purchase'){
                this.init_form_data["search['warehouse_id']"] = this.params.parent_info.sel_warehouse_id;
				this.element_selectors.provider_id.combobox({'disabled':true});
            }
        }
        $('#'+this.form_id).form('filterLoad',this.init_form_data);
		var warehouse_id = tool_self.element_selectors.warehouse_id.combobox('getValue');
		tool_self.toWhOnChange(warehouse_id,'',tool_self);
	}
	
	PurchaseOrderTool.prototype = {
		isDisableformInput : function(is_disabled,map){
			var map = !map?(is_disabled?this.disabled_input_map:this.enable_input_map):map;
			var is_disabled = parseInt(is_disabled);
			for(var item in map){
				if(map[item]){
					var type = this.form_type_map[item];
					if(type == 'combobox'){
						this.element_selectors[item][type](is_disabled?'disable':'enable');
					}else if(type != 'html'){
						this.element_selectors[item][type]({disabled:is_disabled?true:false});
					}
				}
			}
		},
		provider_change : function(newValue,oldValue,that){
			var th = this;
			var info = $('#'+th.datagrid_id).datagrid('getRows');
			var value = th.element_selectors.provider_id.combobox('getValue');
			if(info == ''){
				supplier_provider_id = value;
				return;
			}
			if(newValue == supplier_provider_id){
				return;
			}else{
				messager.confirm('是否修改供应商,修改供应商会清除货品信息',function(r){
					if(r){
						$('#'+th.datagrid_id).datagrid('loadData',{'total':0,'rows':[]});
						supplier_provider_id = value;
					}else{
						th.element_selectors.provider_id.combobox('setValue',supplier_provider_id);
					}
				});
			}
			
		},
		snapshot : function(parent_id,is_disabled){
            $('#'+parent_id+' :input').attr('disabled',is_disabled);
            var a_dom_ar = $('#'+parent_id+' a[class~="easyui-linkbutton"]');
            $.each(a_dom_ar,function(i,a_dom){
                $(a_dom).linkbutton({'disabled':is_disabled});
            });
            this.element_selectors.button_reset.linkbutton({'disabled':false});
			this.element_selectors.purchasemanage.linkbutton({'disabled':false});
        },
        resetDisplay : function(){
            this.snapshot(this.toolbar_id,false);
            this.initShowByTypeAndMode();
            $('#'+this.datagrid_id).datagrid('options').onClickCell = this.keepClickCell;
			this.element_selectors.amount.numberbox({'disabled':true});
			var warehouse_id = this.element_selectors.warehouse_id.combobox('getValue');
			this.toWhOnChange(warehouse_id,'',this);
			this.loadEmployee();
			$('.font_purchase').hide();
        },
		initShowByTypeAndMode:function(type,mode){
            var form_data = this.init_form_data;
            $('#'+this.datagrid_id).datagrid('options').onClickCell = this.keepClickCell;
            
            $('#'+this.datagrid_id).datagrid('loadData',{'total':0,rows:[]});
			$('#'+this.form_id).form('filterLoad',form_data);
            
        },
		delGoods : function(){
            var selected_row 	= $('#' + this.datagrid_id).datagrid("getSelected");
			if ($.isEmptyObject(selected_row) || selected_row.length == 0) {
                messager.alert('请选择行');
                return;
            }
            var selected_index 	= $('#' + this.datagrid_id).datagrid("getRowIndex", selected_row);
            $('#' + this.datagrid_id).datagrid("deleteRow", selected_index);
            this.changeMoney();
        },
		changeMoney : function(){
			 var rows = $('#'+this.datagrid_id).datagrid('getRows');
			 var tax_fee = 0;
			 var total = 0;
			 for(var i in rows){	
				if(rows[i].amount == null || rows[i].amount == undefined){
					 total = 0;
				}
				else{
					total = parseFloat(rows[i].amount);
				}
				tax_fee+=total;
			 }
			 var post_fee = this.element_selectors.post_fee.numberbox('getValue');
			 var other_fee = this.element_selectors.other_fee.numberbox('getValue');
			 var amount = tax_fee+parseFloat(post_fee)+parseFloat(other_fee);
			 $('#' + this.form_id).form('load', {
                'search[amount]': amount
            });
		},
		setPostFee : function(newValue,oldValue,that) {
			if( parseFloat(newValue)!=0 && newValue == ''){
                $(that).numberbox('setValue',parseFloat(oldValue));
				return ;
            }else{
                newValue =parseFloat(newValue);
            }
            if( parseFloat(oldValue)!=0 && $.isEmptyObject(oldValue)){
                oldValue = parseFloat(newValue);
            }else{
                oldValue = parseFloat(oldValue);
            }
			$('#' + this.form_id).form('load', {
                'search[post_fee]': newValue,
            });
			this.changeMoney();	
		},
		setOtherFee : function(newValue, oldValue,that) {
			if( parseFloat(newValue)!=0 && newValue == ''){
                $(that).numberbox('setValue',parseFloat(oldValue));
               return;
            }else{
                newValue =parseFloat(newValue);
            }
            if( parseFloat(oldValue)!=0 && $.isEmptyObject(oldValue)){
                oldValue = parseFloat(newValue);
            }else{
                oldValue = parseFloat(oldValue);
            }
			$('#' + this.form_id).form('load', {
                'search[other_fee]': newValue,
            });
			this.changeMoney();	
		},
		addGoods : function(){
		
		
			var that =this;
			var provider = that.element_selectors.provider_id.combobox('getValue');
			if(!provider || provider == null){
				messager.alert('请选择供应商！');
				return ;
			}
			var warehouse_id = that.element_selectors.warehouse_id.combobox('getValue');
			if(!warehouse_id || warehouse_id == null){
				messager.alert('请选择收货仓库！');
				return ;
			}
			var purchaser_id = that.element_selectors.purchaser_id.combobox('getValue');
			if(!purchaser_id || purchaser_id == null){
				messager.alert('请选择采购员！');
				return ;
			}
			
			if(provider == '0'){
				$('#'+that.params.dialog.purchase_spec).richDialog('goodsSpec',that.submitGoodsSpecDialog,{
					'prefix':that.params.prefix,
					'type':true,
					'warehouse_id':warehouse_id,
					'model':'stock',
				},that);
			}else{	
				$('#'+that.params.dialog.purchase_spec).richDialog('goodsSpec',that.submitGoodsSpecDialog,{
					'prefix':that.params.prefix,
					'type':true,
					'warehouse_id':warehouse_id,
					'model':'purchase',
					'provider_id':provider
				},that);
			}
			
		},
		submitGoodsSpecDialog : function(uid,did,purchase_obj){
			var rows ;
            rows = $('#'+did).datagrid('getRows');
            var now_rows = $('#'+purchase_obj.datagrid_id).datagrid('getRows');
            var now_formatter_rows = utilTool.array2dict(now_rows,'spec_id','');
            var append_rows = [];
            for(var i in rows){
                if(now_formatter_rows[rows[i].spec_id]){continue;}
                append_rows.push(rows[i]);
				
			}
            for(var j in append_rows)
            {
                var append_row = $.extend({},append_rows[j]);
                $('#'+purchase_obj.datagrid_id).datagrid('appendRow',append_rows[j]);
				var index = $('#'+purchase_obj.datagrid_id).datagrid('getRowIndex',append_rows[j]);
				var amount = append_rows[j]['num'] * append_rows[j]['cost_price'];
				var discount_rate;// = append_rows[j]['cost_price']/append_rows[j]['retail_price'];
				if(append_rows[j]['retail_price']==0){
					discount_rate = 1.0000;
				}else{
					discount_rate = append_rows[j]['cost_price']/append_rows[j]['retail_price'];
				}
				amount = amount.toFixed(4);
				discount_rate = discount_rate.toFixed(4);
				$('#'+purchase_obj.datagrid_id).datagrid('updateRow',{
					index: index,
					row: {
							'amount': amount,
							'price':append_rows[j]['retail_price'],
							//'discount_rate':'1.0000',
							'discount_rate':discount_rate
						}
					});

            }
			
            purchase_obj.changeMoney();
            $('#'+purchase_obj.params.dialog.purchase_spec).dialog('close');
        },
		
		endEditRow : function(index, row, changes,datagrid_obj){
			var cost_price,amount,price,discount_rate;
			if(changes.price!=undefined){
				cost_price = (changes.price * row.discount_rate).toFixed(4);
				amount = (row.num * cost_price).toFixed(4);
			}else if(changes.discount_rate!=undefined){
				cost_price = (row.price * changes.discount_rate).toFixed(4);
				amount = (row.num * cost_price).toFixed(4);
			}else if(changes.cost_price!=undefined&&row.discount_rate!=0){
				price = (changes.cost_price / row.discount_rate).toFixed(4);
				amount = (row.num * changes.cost_price).toFixed(4);
			}else if(changes.cost_price!=undefined&&row.discount_rate==0){
				discount_rate = (changes.cost_price / row.price).toFixed(4);
				amount = (row.num * changes.cost_price).toFixed(4);
			}else{
				amount = (row.num * row.cost_price).toFixed(4);
			}
			$('#'+this.datagrid_id).datagrid('updateRow',{
					index: index,
					row: {
							'amount': amount,
							'cost_price':cost_price,
							'price':price,
							'discount_rate':discount_rate,
						}
					});

            this.changeMoney();
        },
		submitPurchaseOrder : function(){
			var that = this;
			var rows = $('#'+this.datagrid_id).datagrid('getRows');
			var good_count = 0;
			var goods_type_count = 0;
			var good_amount = 0;
			if(!$('#'+that.form_id).form('validate')){return; }
			if($.isEmptyObject(rows)){
				messager.alert('请先添加货品!');
				return ;
			}
			var deal_info = []; 
			for(i in rows){
				var temp_result = {'spec_no':rows[i].spec_no,};
				var number = $('#'+this.datagrid_id).datagrid('getRowIndex',rows[i])+1;
				if(parseFloat(rows[i].num) == 0){
					temp_result['info'] = '第'+number+'行的采购量不能为空';
					deal_info.push(temp_result);
				}
				if(rows[i].cost_price == ''){
					temp_result['info'] = '第'+number+'行的采购价不能为空';
					deal_info.push(temp_result);
				}
				var index = $('#'+this.datagrid_id).datagrid('getRowIndex',rows[i]);
                if(!$('#'+this.datagrid_id).datagrid('validateRow',index)) { 
					messager.alert("请将货品信息填写完整！");
					return ;  
				}
				good_count = parseFloat(rows[i].num)+good_count;
				goods_type_count = goods_type_count+1;
				good_amount = good_amount+parseFloat(rows[i].amount);
			}
			if(!$.isEmptyObject(deal_info)){
				$.fn.richDialog("response",deal_info,"goods_spec");
				return;
			}
			this.isDisableformInput(0);
			this.element_selectors.amount.numberbox({'disabled':false});
			var data = {};
			var form_data = $('#'+that.form_id).form('get');
			if(!!this.order_id){
                form_data['search[id]'] = this.order_id;
            }
			form_data['search[goods_count]'] = good_count;
			form_data['search[goods_type_count]'] = goods_type_count;
			form_data['search[goods_fee]'] = good_amount;
			data = $.extend(data,form_data);
            data['rows'] = {};
            data['rows']['update'] = $('#'+this.datagrid_id).datagrid('getChanges','updated');
            data['rows']['delete'] = $('#'+this.datagrid_id).datagrid('getChanges','deleted');
            //data['rows']['insert'] = $('#'+this.datagrid_id).datagrid('getChanges','inserted');
            var data_insert = $('#'+this.datagrid_id).datagrid('getChanges','inserted');
			data_insert.forEach((value,index)=>{
				delete value.wholesale_price;delete value.weight;delete value.unit_name;delete value.tax_rate;delete value.tax_price;delete value.tax_amount;delete value.tax;delete value.stock_num;delete value.src_price;delete value.spec_name;delete value.spec_count;delete value.spec_code;delete value.short_name;delete value.retail_price;delete value.orderable_num;delete value.member_price;delete value.market_price;delete value.lowest_price;delete value.goods_no;delete value.goods_name;delete value.goods_id;delete value.expect_num;delete value.brand_name;delete value.brand_id;delete value.barcode;
			});
			data['rows']['insert'] = data_insert;
			//显示载入状态
            $('#'+this.datagrid_id).datagrid('loading');
            $.post(this.params.submit_url,data,function(res){
                $('#'+that.datagrid_id).datagrid('loaded');
                if(res.status == 1){
                    if(!$.isEmptyObject(res.data)){
                        $.fn.richDialog("response", res.data, "goods_spec");
                    }else{
                        messager.alert(res.info);
                    }
                } else {
                    $('.font_purchase').show();
					
                    that.snapshot(that.toolbar_id,true);
					
					$('#'+that.datagrid_id).datagrid('options').onClickCell = function(){return;};
                    if(!!that.params.parent_info){
                        $('#'+that.params.parent_info.dialog_id).dialog('close');
                        $('#'+that.params.parent_info.datagrid_id).datagrid('reload');
						purchaseManagement.getGoodsAmount();
                    }
                }
            },'json');
		},
		toWhOnChange : function(newValue,oldValue,combox_obj){
			var that = this;		
			var warehouse_id = this.element_selectors.warehouse_id.combobox('getValue');
			if(newValue == oldValue){
				return ;
			}
			var data = $('#'+that.datagrid_id).datagrid('getRows');
			var info = {};
			var id = newValue;
			info = $.extend(info,data);
			$.post("<?php echo U('Purchase/PurchaseOrder/getOtherInfo');?>",{info:info,id:id},function(res){
				if(res.status == 0){
					$('#'+that.form_id).form('filterLoad',res.data);
					if(res.load_data != ''){
						var change_data = res.load_data;
						for(var i in change_data){
							$('#'+that.datagrid_id).datagrid('updateRow',{
								index: parseInt(i),
								row: {
									'stock_num': change_data[i].stock_num,
									'cost_price': change_data[i].cost_price,
								}
							});
						}
						var append_rows = $('#'+that.datagrid_id).datagrid('getRows');
						for(var j in append_rows){
							append_row = $.extend({},append_rows[j]);
							var index = $('#'+that.datagrid_id).datagrid('getRowIndex',append_rows[j]);
							var amount = append_rows[j]['num'] * append_rows[j]['cost_price'];
							$('#'+that.datagrid_id).datagrid('updateRow',{
								index: index,
								row: {
									'amount': amount,
							
								}
							});

						}
						that.changeMoney();
					}
				}else{
					messager.alert(res.info);
				}
			},'json');
		},
		loadEditInfo : function (){
			var that = this;
			$.post('<?php echo U("Purchase/PurchaseOrder/getEditinfo");?>',{id:that.order_id},function(r){
				if(r.status == 1){
					messager.alert(r.info);
				}else{
					that.temp = r.form_data;
                    $('#'+that.form_id).form('filterLoad',r.form_data);
                    $('#'+that.datagrid_id).datagrid('loadData',r.detail_data);
					supplier_provider_id = that.element_selectors.provider_id.combobox('getValue');
					that.changeMoney();
				}
			});
		},
		loadEmployee:function(){
			var that = this;
			$.post('<?php echo U("Purchase/PurchaseOrder/loadEmployee");?>','',function(r){
                if(r.status == 1){
					messager.alert(r.info);
				}else{
                    $('#'+that.form_id).form('filterLoad',r.info);
				}
			});
		},
        loadAlarmInfo : function (){
			var that = this;
            var is_all = 0;


//            var warehouse_id = that.element_selectors.warehouse_id.combobox('getValue');
            var sel_rows = $('#'+that.params.parent_info.datagrid_id).datagrid('getSelections');
            var spec_ids = [];
            for(var i in sel_rows)
            {
                spec_ids.push(sel_rows[i].id);
            }

			$.post('<?php echo U("Purchase/PurchaseOrder/getAlarmInfo");?>',{ids:spec_ids,warehouse_id:that.params.parent_info.warehouse_id},function(r){
				if(r.status == 1){
					messager.alert(r.info);
				}else{
					that.temp = r.form_data;
                    for(var j in r.detail_data.rows)
                    {
                        $('#'+that.datagrid_id).datagrid('appendRow',r.detail_data.rows[j]);
                    }
					that.changeMoney();
                    if(that.params.parent_info.sel_warehouse_id!=0){
                        that.toWhOnChange(that.params.parent_info.sel_warehouse_id,'',that);
                        that.element_selectors.warehouse_id.combobox('setValue',that.params.parent_info.sel_warehouse_id);
                    }else{
                      var sel_warehouse_id = that.element_selectors.warehouse_id.combobox('getValue');
                        that.toWhOnChange(sel_warehouse_id,'',that);
                        that.element_selectors.warehouse_id.combobox('setValue',sel_warehouse_id);
                    }
				}
			});
		}
	};
	 var PurchaseOrder = undefined;
	$(function(){
		element_selectors.provider_id.combobox({onChange:function(newValue,oldValue){var that = this;PurchaseOrder.provider_change(newValue,oldValue,that);}});
        element_selectors.warehouse_id.combobox({editable:false, required:true,onChange:function(newValue,oldValue){PurchaseOrder.toWhOnChange(newValue,oldValue,this);}});
      //var PurchaseOrder = undefined;
       setTimeout(function(){
        PurchaseOrder = <?php echo ($purchase_order_tool); ?> = new PurchaseOrderTool(JSON.parse('<?php echo ($params); ?>'),element_selectors);
		//PurchaseOrder.toWhOnChange = function('','',this);
		//purchase_obj.isDisableformInput(0);
            if(!$.isEmptyObject(PurchaseOrder.params.order_id)){
                PurchaseOrder.disabled_input_map  ={
					'post_fee':0,'other_fee':0,'provider_id':1,'purchaser_id':0,'logistics_type':0,'amount':1,'warehouse_id':1, 'contact':1, 'telno':1, 'address':1,'remark':0,'button_submit':0,'button_reset':1,'button_add_goods':0,'button_del_goods':0
				};
				PurchaseOrder.enable_input_map = {
					'post_fee':0,'other_fee':0,'provider_id':0,'purchaser_id':0,'logistics_type':0,'amount':1,'warehouse_id':0, 'contact':0, 'telno':0, 'address':0,'remark':0,'button_submit':0,'button_reset':1,'button_add_goods':0,'button_del_goods':0
				};
				PurchaseOrder.element_selectors.purchasemanage.linkbutton({'disabled':true});
                //关闭注册的发货仓库选择事件
                //PurchaseOrder.toWhOnChange = function(){};
                PurchaseOrder.isDisableformInput(1);
                PurchaseOrder.loadEditInfo();
				

            }else{
				PurchaseOrder.loadEmployee();
			}
            if(!! PurchaseOrder.params.order_type)
            {
                if(PurchaseOrder.params.order_type == 'alarm_purchase')
                {
                    PurchaseOrder.disabled_input_map  ={
                        'post_fee':0,'other_fee':0,'provider_id':0,'purchaser_id':0,'logistics_type':0,'amount':1,'warehouse_id':0, 'contact':0, 'telno':0, 'address':0,'remark':0,'button_submit':0,'button_reset':1,'button_add_goods':0,'button_del_goods':0
                    };
                    PurchaseOrder.enable_input_map = {
                        'post_fee':0,'other_fee':0,'provider_id':0,'purchaser_id':0,'logistics_type':0,'amount':1,'warehouse_id':0, 'contact':0, 'telno':0, 'address':0,'remark':0,'button_submit':0,'button_reset':1,'button_add_goods':0,'button_del_goods':0
                    };
					
                    PurchaseOrder.isDisableformInput(1);

                    PurchaseOrder.loadAlarmInfo();
					

                }
            }
        });
    });
	

</script>

<!-- layout-south-tabs, call add_tabs js function to add tabs -->


</body>
</html>