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

    <div id="<?php echo ($id_list["dialog_stock_spec"]); ?>"></div>

<!-- toolbar -->

<div id="<?php echo ($id_list["toolbar"]); ?>" style="padding:5px;height:auto">
<!--提交-->
<div class="form-div" style="border-bottom:  1px solid #7CAAB1">
    <a href="javascript:void(0)" class="easyui-linkbutton" name="button_submit" data-options="iconCls:'icon-save',plain:true,onClick:function(){}">保存</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" name="button_reset" data-options="iconCls:'icon-redo',plain:true,onClick:function(){}">重置</a>
<a href="javascript:void(0)" class="easyui-linkbutton" name="transfermanage" data-options="iconCls:'icon-next',plain:true" style="float: right;margin-right:10px;" onClick="open_menu('调拨单管理', '<?php echo U('Stock/StockTransManagement/getTransferList');?>')">调拨单管理</a>
	</div>
<!--form-->
<div>
<form id="<?php echo ($id_list["form"]); ?>" method="post">
<div>
<div style="display: inline-block;vertical-align:middle">
<fieldset style="border:  1px solid #7CAAB1;height: 80px"><legend>调拨形式</legend>
<div class="form-div"><label>调拨类型：</label><input class="easyui-combobox txt" name="search[type]" data-options="editable:false, required:true,panelHeight:'auto',valueField:'id',textField:'name',data:formatter.get_data('stock_transfer_type')"/></div>
<div class="form-div"><label>调拨方案：</label><input class="easyui-combobox txt" name="search[mode]" data-options="editable:false, required:true,panelHeight:'auto',valueField:'id',textField:'name',data:formatter.get_data('stock_transfer_mode')"/></div>
</fieldset>
</div>

<div style="display: inline-block;vertical-align:middle">
<fieldset style="border:  1px solid #7CAAB1;height: 80px"><legend>仓库信息</legend>
<div class="form-div"><label>调出仓库：</label><select class="sel sel-disabled" name="search[from_warehouse_id]"  ><?php if(is_array($list["warehouse"])): $i = 0; $__LIST__ = $list["warehouse"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?></select></div>
<div class="form-div"><label>目标仓库：</label><select class="sel sel-disabled" name="search[to_warehouse_id]" ><option value="all">无</option><?php if(is_array($list["warehouse"])): $i = 0; $__LIST__ = $list["warehouse"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?></select></div>
</fieldset>
</div>

<div style="display: inline-block;vertical-align:middle">
<fieldset style="border:  1px solid #7CAAB1;height: 80px"><legend>其他信息</legend>
<div class="form-div">
<label>　联系人：</label><input class="easyui-textbox sel" type="text" name="search[contact]" />
<label>　联系电话：</label><input class="easyui-textbox sel" type="text" name="search[telno]" data-options="validType:'mobileAndTel'" />
<label>　目标仓地址：</label><input class="easyui-textbox sel" style="width:200px" type="text" name="search[address]"/>
</div>
<div class="form-div">
<label>物流公司：</label><select class="easyui-combobox sel" name="search[logistics_id]" data-options="editable:false"> <option value="0">无</option><?php if(is_array($list["logistics"])): $i = 0; $__LIST__ = $list["logistics"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?></select>
<label>　　　备注：</label><input class="easyui-textbox sel" type="text" style="width:418px" name="search[remark]"/>
</div>
</fieldset>
</div>

</div><!--form-div-->
</form>
</div>
<!-- operater datagrid-->
<div class="form-div" style="border-top:  1px solid #7CAAB1;padding-top: 2px;margin-top: 5px;">

<a href="javascript:void(0)" class="easyui-linkbutton" name="button_add_goods" data-options="iconCls:'icon-add',plain:true">添加货品</a>
<a href="javascript:void(0)" class="easyui-linkbutton" name="button_del_goods" data-options="iconCls:'icon-remove',plain:true">删除</a>
<a href="javascript:void(0)" name="apply_to_all_lines" class="easyui-linkbutton" data-options="plain:false">应用到所有行</a>
<label style="color:red;margin-left: 5px;">#应用到所有行支持[调拨数量]列#</label>
<label style="margin-left: 30px">当前调拨总量：</label><strong name='html_total_num' style="vertical-align:middle">0</strong>
<label style="color:red;margin-left:80px;font-size:30px;display:none;" class="font_stoTrans">保存成功</label>
<label style="color:red;margin-left:80px;font-size:30px;display:none;" class="font_stoTrans_auto_commit">保存成功,并自动提交成功</label>
</div><!--toolbar-->
<script type="text/javascript">
//# sourceURL=<?php echo ($js_name); ?>.js
(function(){
    var toolbar_id = '<?php echo ($id_list["toolbar"]); ?>';
    var element_selectors ={
        'type'		        :$('#'+toolbar_id+" :input[name='search[type]']"),
        'mode'		        :$('#'+toolbar_id+" :input[name='search[mode]']"),
        'from_warehouse_id'	:$('#'+toolbar_id+" select[name='search[from_warehouse_id]']"),
        'to_warehouse_id'	:$('#'+toolbar_id+" select[name='search[to_warehouse_id]']"),
        'contact'		    :$('#'+toolbar_id+" :input[name='search[contact]']"),
        'remark'		    :$('#'+toolbar_id+" :input[name='search[remark]']"),
        'telno'		        :$('#'+toolbar_id+" :input[name='search[telno]']"),
        'address'			:$('#'+toolbar_id+" :input[name='search[address]']"),
        'logistics_id'		:$('#'+toolbar_id+" select[name='search[logistics_id]']"),
        'button_submit'		:$('#'+toolbar_id+" a[name='button_submit']"),
		'transfermanage'	:$('#'+toolbar_id+" a[name='transfermanage']"),
        'button_reset'		:$('#'+toolbar_id+" a[name='button_reset']"),
        'button_add_goods'	:$('#'+toolbar_id+" a[name='button_add_goods']"),
        'button_del_goods'	:$('#'+toolbar_id+" a[name='button_del_goods']"),
        'apply_to_all_lines':$('#'+toolbar_id+" a[name='apply_to_all_lines']"),
        'html_total_num'	:$('#'+toolbar_id+" strong[name='html_total_num']"),
    };
    var StockTransferTool = function(params,element_selectors){
        var tool_self = this;
        this.params = params;
        this.datagrid_id = this.params.datagrid.id;
        this.form_id = this.params.form.id;
        this.toolbar_id = this.params.id_list.toolbar;
        this.element_selectors = element_selectors;
        this.order_id = this.params.order_id;
        this.change_column  = '';
        //初始化表单数据

        this.init_form_data = $('#'+this.form_id).form('get');
        this.init_other_data = {
            'html_total_num':0
        };
        this.init_form_type ={
            '0':$.extend(true,{},this.init_form_data),
            '1':$.extend(true,{},this.init_form_data),
        } ;

        //表单easyui类型映射
        this.form_type_map = {
            'transfermanage':'linkbutton','type':'combobox','mode':'combobox','from_warehouse_id':'combobox', 'to_warehouse_id':'combobox', 'contact':'textbox', 'telno':'textbox', 'address':'textbox', 'logistics_id':'combobox', 'remark':'textbox','html_total_num':'html','button_submit':'linkbutton','button_reset':'linkbutton','button_add_goods':'linkbutton','button_del_goods':'linkbutton'
        };
        //记录需要启用的表单映射
        this.disabled_input_map ={
            'type':1,'mode':1,'from_warehouse_id':1, 'to_warehouse_id':1, 'contact':1, 'telno':1, 'address':1, 'logistics_id':1, 'remark':1,'button_submit':1,'button_submit':1,'button_reset':1,'button_add_goods':1,'button_del_goods':1
        };
        //记录需要禁用的表单映射
        this.enable_input_map ={
            'type':1,'mode':1,'from_warehouse_id':1, 'to_warehouse_id':1, 'contact':1, 'telno':1, 'address':1, 'logistics_id':1, 'remark':1,'button_submit':1,'button_submit':1,'button_reset':1,'button_add_goods':1,'button_del_goods':1
        };
        //注册事件
        //保存按钮
        this.element_selectors.button_submit.linkbutton({onClick:function(){
            tool_self.submitTransferOrder();
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
        //应用到所有行
        this.element_selectors.apply_to_all_lines.linkbutton({onClick:function(){
            tool_self.applyToAllLines();
        }});
        //调拨类型下拉菜单
        this.element_selectors.type.combobox('options').onChange=function(newValue,oldValue){
            tool_self.TransferTypeOnChange(newValue,oldValue);
        };
        //调拨方案下拉菜单
        this.element_selectors.mode.combobox({onChange:function(newValue,oldValue){
            tool_self.TransferModeOnChange(newValue,oldValue,this);
        }});
        //datagrid监听事件
        //添加field字段事件
        var options = $('#'+this.datagrid_id).datagrid('options');
        for(var i in options.columns[0])
        {
            if(options.columns[0][i].field == 'to_position_no')
            {
                options.columns[0][i].editor.options.onClickButton = function(){tool_self.addPosition(this);};
            }
        }
        $('#'+this.datagrid_id).datagrid('options').onAfterEdit = function(index, row, changes){tool_self.endEditRow(index, row, changes,this);};
        $('#'+this.datagrid_id).datagrid('options').onClickCell = function(index,field,value){tool_self.onClickCell(index,field,value)};

        //添加格式化
        $('#'+this.datagrid_id).datagrid('options').rowStyler = function(index,row){ if(parseInt(row.stock_num)<parseInt(row.num)){return 'color:red';}};
        //单元格编辑模式 先后顺序必须保持
        $('#'+this.datagrid_id).datagrid().datagrid('enableCellEditing');
        this.keepClickCell = $('#'+this.datagrid_id).datagrid('options').onClickCell;
       //根据调拨方案初始化仓库信息
        var to_warehouse_list = tool_self.element_selectors.to_warehouse_id.combobox('getData');
        var from_warehouse_value = tool_self.element_selectors.from_warehouse_id.combobox('getValue');
        for(var i in to_warehouse_list)
        {
            if(to_warehouse_list[i].value != from_warehouse_value &&to_warehouse_list[i].value != 'all'){
                this.init_form_type[0]['search[to_warehouse_id]'] = to_warehouse_list[i].value;
                break;
            }
        }
        this.init_form_type[1]['search[mode]'] = 1;
        this.init_form_type[1]['search[to_warehouse_id]'] = this.init_form_type[1]['search[from_warehouse_id]'];
    }
    StockTransferTool.prototype = {
        /**
         *根据选择的调拨类型和调拨方案初始化界面
         */
        initShowByTypeAndMode:function(type,mode){
            this.mode = mode;
            $('#'+this.datagrid_id).datagrid('options').onClickCell = this.keepClickCell;
            $('#'+this.form_id).form('filterLoad',this.init_form_type[mode]);
            $('#'+this.datagrid_id).datagrid('loadData',{'total':0,rows:[]});
            this.element_selectors.html_total_num.html(0);
            switch(parseInt(mode)){
                case 0:{
                    this.element_selectors.to_warehouse_id.combobox('readonly',false);
                    break;
                }
                case 1:{
                    this.element_selectors.to_warehouse_id.combobox('readonly');
                    break;
                }
            }
            this.record_from_id = this.init_form_type[mode]['search[from_warehouse_id]'];
        },
        submitTransferOrder : function(){
            var that = this;
            var from_warehouse_id = this.element_selectors.from_warehouse_id.combobox('getValue');
            var mode = this.element_selectors.mode.combobox('getValue');
            if(from_warehouse_id == 'all' || !from_warehouse_id){ messager.alert('请选择调出库仓库'); return; }
            var to_warehouse_id = this.element_selectors.to_warehouse_id.combobox('getValue');
            if(to_warehouse_id == 'all' || !to_warehouse_id){ messager.alert('请选择调入库仓库'); return; }
            if(to_warehouse_id == from_warehouse_id &&　mode !=1){ messager.alert('调出仓库和调入仓库不能相同'); return; }
            if(!$('#'+this.form_id).form('validate')){ return; }
            var rows = $('#'+this.datagrid_id).datagrid('getRows');
            if($.isEmptyObject(rows)){
                link_a = '货品信息为空，是否添加货品信息';
                messager.confirm(link_a,function(r){
                    if(r){
                        that.addGoods();
                    }
                });
                return;
            }
            var detail_info = [];
            for(var i in rows){
                var index = $('#'+this.datagrid_id).datagrid('getRowIndex',rows[i]);
                if(!$('#'+this.datagrid_id).datagrid('validateRow',index)) { messager.alert('请填写完整货品信息!'); return; }else{$('#'+this.datagrid_id).datagrid('endEdit',index);}
                if(parseInt(rows[i].stock_num)<parseInt(rows[i].num)){
                    detail_info.push({spec_no:rows[i].spec_no,info:'调拨数量不能大于库存数量'})
                }
            }
            if(!$.isEmptyObject(detail_info)){ $.fn.richDialog("response", detail_info, "goods_spec"); return; }

            this.isDisableformInput(0);
            var data={},json_data= {},datas;
            var form_data = $('#'+this.form_id).form('get');
            if(!!this.order_id){
                form_data['search[id]'] = this.order_id;
            }
            //data = $.extend(data,form_data);
            data['rows'] = {};
            data['rows']['update'] = $('#'+this.datagrid_id).datagrid('getChanges','updated');
            data['rows']['delete'] = $('#'+this.datagrid_id).datagrid('getChanges','deleted');
            //data['rows']['insert'] = $('#'+this.datagrid_id).datagrid('getChanges','inserted');
            var data_insert = $('#'+this.datagrid_id).datagrid('getChanges','inserted');
            data_insert.forEach((value,index)=>{
                delete value.spec_name;delete value.market_price;delete value.spec_code;delete value.barcode;delete value.lowest_price;delete value.wholesale_price;delete value.tax_rate;delete value.retail_price;delete value.weight;delete value.base_unit_id;delete value.short_name;delete value.spec_count;delete value.goods_no;delete value.goods_name;delete value.goods_id;delete value.brand_id;delete value.lock_num;delete value.subscribe_num;delete value.order_num;delete value.sending_num;delete value.purchase_arrive_num;delete value.transfer_num;delete value.unpay_num;delete value.orderable_num;delete value.status;delete value.price;delete value.total_amount;delete value.brand_name;delete value.from_position_no;delete value.to_position_no;delete value.is_allocated;delete value.unit_name;delete value.src_price;
            });
            data['rows']['insert'] = data_insert;
            datas = JSON.stringify(data);
            json_data['data'] = datas;
            var ret_data = $.extend(json_data,form_data);
            //显示载入状态
            $('#'+this.datagrid_id).datagrid('loading');
            $.post(this.params.submit_url,ret_data,function(res){
                $('#'+that.datagrid_id).datagrid('loaded');
                if(res.status == 1){
                    if(!$.isEmptyObject(res.data)){
                        $.fn.richDialog("response", res.data, "goods_spec");
                    }else{
                        messager.alert(res.info);
                    }
                } else if(res.status == 2){
                    $('.font_stoTrans').show();
                    that.snapshot(that.toolbar_id,true);
                    $('#'+that.datagrid_id).datagrid('options').onClickCell = function(){return;};
                    if(!!that.params.parent_info){
                        $('#'+that.params.parent_info.dialog_id).dialog('close');
                        $('#'+that.params.parent_info.datagrid_id).datagrid('reload');
                    }
                    messager.alert(res.info);
                } else {
                   // messager.info(res.info);
                    if(res.stocktransfer_auto_commit == 1){$('.font_stoTrans_auto_commit').show();}else{$('.font_stoTrans').show();}
					that.snapshot(that.toolbar_id,true);
                    $('#'+that.datagrid_id).datagrid('options').onClickCell = function(){return;};
                    if(!!that.params.parent_info){
                        $('#'+that.params.parent_info.dialog_id).dialog('close');
                        $('#'+that.params.parent_info.datagrid_id).datagrid('reload');
                    }
                }
            },'json');
        },
        /**
         *  保存快照的生成和回复
         */
        snapshot : function(parent_id,is_disabled){
            $('#'+parent_id+' :input').attr('disabled',is_disabled);
            var a_dom_ar = $('#'+parent_id+' a[class~="easyui-linkbutton"]');
            $.each(a_dom_ar,function(i,a_dom){
                $(a_dom).linkbutton({'disabled':is_disabled});
            });
            this.element_selectors.button_reset.linkbutton({'disabled':false});
			this.element_selectors.transfermanage.linkbutton({'disabled':false});
        },
        resetDisplay : function(){
			$('.font_stoTrans').hide();
			$('.font_stoTrans_auto_commit').hide();
            this.snapshot(this.toolbar_id,false);
            var mode = this.element_selectors.mode.combobox('getValue');
            this.initShowByTypeAndMode(0,mode);
            $('#'+this.datagrid_id).datagrid('options').onClickCell = this.keepClickCell;
        },
        addGoods : function(){
            var that = this;
            var from_warehouse_id = this.element_selectors.from_warehouse_id.combobox('getValue');
            if(from_warehouse_id == 'all' || !from_warehouse_id){
                messager.alert('请选择调出库仓库');
                return;
            }
            var to_warehouse_id = this.element_selectors.to_warehouse_id.combobox('getValue');
            if(to_warehouse_id == 'all' || !to_warehouse_id){
                messager.alert('请选择调入库仓库');
                return;
            }

            $('#' + that.params.dialog.stock_spec).richDialog('goodsSpec', that.submitGoodsSpecDialog,{
                'prefix':that.params.prefix,
                'type' : true,
                'warehouse_id':from_warehouse_id,
                'to_warehouse_id':to_warehouse_id,
                'model':'transfer'
            },that);
        },
        submitGoodsSpecDialog : function(uid,did,transfer_obj){
            var rows ;
            rows = $('#'+did).datagrid('getRows');
            var now_rows = $('#'+transfer_obj.datagrid_id).datagrid('getRows');
            var now_formatter_rows = transfer_obj.formatterRows(now_rows);
            var mode = transfer_obj.element_selectors.mode.combobox('getValue');
            var deal_info = [];
            var append_rows = [];
            for(var i in rows){
                if(now_formatter_rows[rows[i].spec_id]){continue;}
                //过滤负库存
                if(parseInt(rows[i]['stock_num']) <=0 ){
                    deal_info.push({'spec_no':rows[i].spec_no,'info':'不能调拨库存为0的货品'});
                    continue;
                }
                append_rows.push(rows[i]);
            }
            if(!$.isEmptyObject(deal_info)){
                $.fn.richDialog("response", deal_info, "goods_spec");
            }
            for(var j in append_rows)
            {
                var append_row = $.extend({},append_rows[j]);
                if(mode == 1){
                    append_row.num = append_row.stock_num;
                    append_row.is_allocated = 0;
                }
                $('#'+transfer_obj.datagrid_id).datagrid('appendRow',append_row);
            }
            transfer_obj.calcTransferGoodsNum();
            $('#'+transfer_obj.params.dialog.stock_spec).dialog('close');
        },
        delGoods : function(){
            var sel_rows = $('#'+this.datagrid_id).datagrid('getSelections');
            for(var i in sel_rows){
                var index;
                index = $('#'+this.datagrid_id).datagrid('getRowIndex',sel_rows[i]);
                $('#'+this.datagrid_id).datagrid('deleteRow',index);
            }
            this.calcTransferGoodsNum();
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
            }
            //show_dg.datagrid('loadData',data);
        },
        endEditRow : function(index, row, changes,datagrid_obj){
            this.change_column=changes;
            if(changes['num']!=undefined){
                if(parseInt(row.stock_num) < parseInt(row.num))
                {
                    messager.alert('调拨数量不能大于库存数量');
                }
            }

            this.calcTransferGoodsNum();
        },
        onClickCell : function(index,field,value){
            var mode = this.element_selectors.mode.combobox('getValue');
            var rows = $('#'+this.datagrid_id).datagrid('getRows');
            if(field=='to_position_no'){
                var ed = $('#'+this.datagrid_id).datagrid('getEditor',{index:parseInt(index),field:field});
                if((rows[index]['is_allocated'] == 1 || rows[index]['position_id'] == 0 ) ){
                    $(ed.target).textbox('readonly');
                    $(ed.target).textbox('releaseFocus');
                    $(ed.target).textbox('textbox').css({'background-color':'#C5C5C5'});
                    $(ed.target).textbox('textbox').siblings('a').unbind('mousedown');
                }

            }
            if(mode == 1){
                if(field=='num'){
                    var ed = $('#'+this.datagrid_id).datagrid('getEditor',{index:parseInt(index),field:field});
                    $(ed.target).textbox('readonly');
                    $(ed.target).textbox('textbox').css({'background-color':'#C5C5C5'});
                }
            }
        },
        addPosition : function(editor_p){
            var that = this;

            var stockin_warehouse = this.element_selectors.to_warehouse_id.combobox('getValue');
            if(stockin_warehouse == null ||stockin_warehouse ==0 ){
                messager.alert('请选择入库仓库');
                return;
            }

            $('#' + that.params.dialog.position).richDialog('warehousePosition', this.submitDialogWarehousePosition,{
                'prefix':'transfer',
                'warehouse_id':stockin_warehouse,
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
                        row.to_position =up_sel_row['id'];
//						$('#' + params.m_p.params.datagrid.id).datagrid('updateRow',{index:index,});
                        $('#' + params.m_p.params.dialog.position).dialog('close');
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
                row.to_position =up_sel_row['id'];
//				$('#' + params.m_p.params.datagrid.id).datagrid('updateRow',{index:index,row:{position_id:up_sel_row['id']}});
                $('#' + params.m_p.params.dialog.position).dialog('close');
                $(params.editor_p).textbox('releaseFocus');
            }


        },
        fromWhOnChange : function(newValue,oldValue,combox_obj){
            var that = this;
            if(!!this.params.order_id){
                return;
            }
            var to_wh_id = this.element_selectors.to_warehouse_id.combobox('getValue');
            var warehouse_list = this.element_selectors.to_warehouse_id.combobox('getData');
            var mode = this.element_selectors.mode.combobox('getValue');
            if(this.record_from_id == newValue){
                    return;
            }
           /* if( warehouse_list.length ==2){
                if(warehouse_list[0].value == newValue){
                    that.element_selectors.to_warehouse_id.combobox('setValue',warehouse_list[1].value);
                }else{
                    that.element_selectors.to_warehouse_id.combobox('setValue',warehouse_list[0].value);
                }
                return;
            }*/
            if(to_wh_id == newValue &&mode != 1 ){
                messager.alert('调拨仓库相同!请重新选择');
                $(combox_obj).combobox('setValue',oldValue);
                return;
            }
            messager.confirm('更改调出仓库之后,需要重新选择调拨的货品,是否继续',function(r){
                if(r){
                    if(mode == 1){
                        that.element_selectors.to_warehouse_id.combobox('setValue',newValue);
                    }
                    $('#'+that.datagrid_id).datagrid('loadData',{'total':0,rows:[]});
                    that.element_selectors.html_total_num.html(0);
                    that.from_warehouse_id = oldValue;
                }else{
                    $(combox_obj).combobox('setValue',oldValue);
                }
            });

        },
        toWhOnChange : function(newValue,oldValue,combox_obj){
            var that = this;
            if(!!this.params.order_id){
                return;
            }
            if(newValue == 'all'){
                $('#'+that.form_id).form('filterLoad',{'search[address]':'','search[telno]':'','search[contact]':'',});
                $('#'+that.datagrid_id).datagrid('loadData',{'total':0,rows:[]});
                that.element_selectors.html_total_num.html(0);
                return;
            }
            var from_wh_id = this.element_selectors.from_warehouse_id.combobox('getValue');
            var mode = this.element_selectors.mode.combobox('getValue');

            if(from_wh_id == newValue && mode != 1 ){
                messager.alert('调拨仓库相同!请重新选择');
                $(combox_obj).combobox('setValue',oldValue);
                return;
            }
            var detail_rows = $('#'+this.datagrid_id).datagrid('getRows');

            var update_r = $.data($('#'+that.datagrid_id)[0],'datagrid').updatedRows;
            var insert_r = $.data($('#'+that.datagrid_id)[0],'datagrid').insertedRows;

            var spec_info = {};
            $.each(detail_rows,function(i,row){spec_info[i]=row['spec_id']});
            $.post("<?php echo U('Stock/StockTransfer/getOtherInfo');?>",{warehouse_id:newValue,detail:spec_info},function(res){
                if(res.status){
                    messager.alert(res.info);
                }else{
                    for(var i in detail_rows)
                    {
                        if(that.isset(insert_r,detail_rows[i])==-1)
                        {
                            if(that.isset(update_r,detail_rows[i])==-1)
                            {
                                update_r.push(detail_rows[i]);
                            }
                        }
                        $('#'+that.datagrid_id).datagrid('updateRow',{index:parseInt(i),row:{is_allocated:mode ==1?0:res.data.detail_infp[i].is_allocated,to_position:res.data.detail_infp[i].position_id,to_position_no:res.data.goods_info[i].position_no}});
                    }
                    $('#'+that.form_id).form('filterLoad',res.data.warehouse_info);
                    if(that.init_form_type[mode]['search[to_warehouse_id]'] == newValue){
                        that.init_form_type[mode] = $.extend(true,{},that.init_form_type[mode],res.data.warehouse_info);
                    }
                }

            },'json');
        },
        isset:function(rows,t){
            for(var i=0,len=rows.length;i<len;i++){
                if(rows[i]==t){
                    return i;
                }
            }
            return -1;
        },
        TransferTypeOnChange : function(newValue,oldValue){},
        TransferModeOnChange : function(newValue,oldValue,component_obj){
            var that = this;
            if(!!this.params.order_id){
                return;
            }
            if(oldValue =='' || oldValue == undefined )
            {
                return;
            }
            if(this.mode == newValue){
                return ;
            }
            messager.confirm('更改调拨方案之后,需要重新选择调拨的货品,是否继续',function(r){
                if(r){
                    that.initShowByTypeAndMode(0,newValue);
                }else{
                    $(component_obj).combobox('setValue',oldValue);
                    return;
                }
            });
        },
        /*
        * 统计调拨总数量
        * */
        calcTransferGoodsNum : function(){
            var rows = $('#'+this.datagrid_id).datagrid('getRows');
            var total = 0;
            for(var i in rows){
                total +=parseInt(rows[i].num);
            }

            this.element_selectors.html_total_num.html(total);
        },
        /**
         * 把rows中的id值作为key值映射rows对应行
         * @param array rows 格式化目标数组
         * return {}
         */
        formatterRows : function(rows) {
            var format_row = {};
            var before_row = rows;
            for (var i in before_row) {
                if (before_row.length == 0)
                    break;
                var key = before_row[i]['spec_id'];
                format_row[key] = before_row[i];
            }
            return format_row;
        },
        isDisableformInput : function(is_disabled,map){
            var map = !map?(is_disabled?this.disabled_input_map:this.enable_input_map):map
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
        loadEditInfo : function()
        {
            var that = this;

            $.post('<?php echo U("StockTransfer/getEditInfo");?>',{id:this.order_id},function(res){
                if(res.status== 1){
                    messager.alert(res.info);
                }else{
                    that.temp = res.form_data;
                    $('#'+that.form_id).form('filterLoad',res.form_data);
                    $('#'+that.datagrid_id).datagrid('loadData',res.detail_data);
                    that.calcTransferGoodsNum();
                }

            },'json');
        }
    };
    $(function(){
        var stock_transfer_obj = undefined;
        //注册下拉菜单事件
        element_selectors.from_warehouse_id.combobox({editable:false, required:true,onChange:function(newValue,oldValue){stock_transfer_obj.fromWhOnChange(newValue,oldValue,this);}});
        element_selectors.to_warehouse_id.combobox({editable:false, required:true,onChange:function(newValue,oldValue){stock_transfer_obj.toWhOnChange(newValue,oldValue,this);}});
        setTimeout(function(){

           stock_transfer_obj = new StockTransferTool(JSON.parse('<?php echo ($params); ?>'),element_selectors);
            stock_transfer_obj.initShowByTypeAndMode(0,0);
            if(!$.isEmptyObject(stock_transfer_obj.params.order_id)){
                stock_transfer_obj.disabled_input_map  ={
                    'type':1,'mode':1,'from_warehouse_id':1, 'to_warehouse_id':1,'button_reset':1
                };
                stock_transfer_obj.enable_input_map = {
                    'type':1,'mode':1,'from_warehouse_id':1, 'to_warehouse_id':1
                };
                //关闭注册的发货仓库选择事件
                stock_transfer_obj.toWhOnChange = function(){};
                stock_transfer_obj.fromWhOnChange = function(){};
                stock_transfer_obj.isDisableformInput(1);
                stock_transfer_obj.loadEditInfo();
				stock_transfer_obj.element_selectors.transfermanage.linkbutton({'disabled':true});
				

            }
        });
    });
})();
</script>

<!-- layout-south-tabs, call add_tabs js function to add tabs -->


</body>
</html>