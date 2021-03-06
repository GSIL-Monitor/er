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

	<div id='show_total_price'></div>
    <div id="<?php echo ($id_list["del_stock_goods"]); ?>"></div>
    <div id="<?php echo ($id_list["add"]); ?>"></div>
    <div id="<?php echo ($id_list["adjust"]); ?>"></div>
    <div id="<?php echo ($id_list["edit"]); ?>"></div>
    <div id="<?php echo ($id_list["select_shop"]); ?>"></div>
    <div id="<?php echo ($id_list["file_dialog"]); ?>" class="easyui-panel" style="padding:25px 50px 25px 50px">
        <form id="<?php echo ($id_list["file_form"]); ?>" method="post" enctype="multipart/form-data">
            <div style="margin-bottom:25px">
                <input class="easyui-filebox" name="file" data-options="prompt:'请选择文件...','buttonText':'请选择文件'" style="width:100%;">
            </div>
            <div align="center">
                <a href="javascript:void(0)" class="easyui-linkbutton" style="width:50%" onclick="stockManagement.upload()">上传</a>
            </div>
        </form>
    </div>
    <div id="<?php echo ($id_list["import_price"]); ?>" class="easyui-panel" style="padding:25px 50px 25px 50px">
        <form id="<?php echo ($id_list["form_price"]); ?>" method="post" enctype="multipart/form-data">
            <div style="margin-bottom:25px">
                <input class="easyui-filebox" name="file" data-options="prompt:'请选择文件...','buttonText':'请选择文件'" style="width:100%;">
            </div>
            <div align="center">
                <a href="javascript:void(0)" class="easyui-linkbutton" style="width:50%" onclick="stockManagement.uploadprice()">上传</a>
            </div>
        </form>
    </div>
    <div id="<?php echo ($id_list["import_stock"]); ?>" >
        <label>请选择初始化导入的仓库：</label><select id="warehouse_id" class="easyui-combobox sel" name="warehouse_id" data-options="panelHeight:'auto',panelHeight:120"> <?php if(is_array($warehouse_list)): $i = 0; $__LIST__ = $warehouse_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?> </select></br>
        <table style="color: red">
            <tr>
                <td>1.此操作会将所有的系统单品导入一个仓库中</td>
            </tr>
            <tr>
                <td>2.此操作所导入的库存量为该系统商品对应的平台库存，如果没有则库存数默认为0</td>
            </tr>
            <tr>
                <td>3.此操作导入的成本价为平台货品的价格</td>
            </tr>
            <tr>
                <td>4.此操作保存的货位为默认货位</td>
            </tr>
            <tr>
                <td>*******使用此操作前请确保您要导入的数据准确性</td>
            </tr>
        </table>
        <a href="javascript:void(0)" class="easyui-linkbutton" style="font-size: 22px" data-options="plain:false" onclick="stockManagement.init_import_stock()">确定导入</a>
    </div>



<!-- toolbar -->

    <div id="<?php echo ($id_list["tool_bar"]); ?>" style="padding:5px;height:auto;">
        <form id="stock_search_form" class="easyui-form" method="post">
            <div class="form-div">
                <label>商家编码：</label><input class="easyui-textbox txt" type="text" name="search[spec_no]" />
                　 <label>货品编码：</label><input class="easyui-textbox txt" type="text" name="search[goods_no]"/>
                　  <label>　　分类：</label><input class="txt" id="stock_tree" value="-1" name="search[class_id]" data-options="url:'<?php echo U('Goods/GoodsClass/getTreeClass');?>?type=all',method:'post',required:true,panelHeight:200"/>
                　 <label>品牌：</label><select class="easyui-combobox sel" name="search[brand_id]" data-options="panelHeight:'auto',panelHeight:200"> <?php if(is_array($brand_array)): $i = 0; $__LIST__ = $brand_array;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?> </select>
                <!-- <a href="javascript:void(0)" onclick="stockManagement.clickMore(this);">更多</a> -->
                <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-search'" onclick="stockManagement.submitSearchForm(this)">搜索</a>
                <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-redo'" onclick="stockManagement.loadFormData();">重置</a>
				<a href="<?php echo ($faq_url); ?>" target="_blank" class="easyui-linkbutton" title="点击查看常见问题" data-options="iconCls:'icon-help',plain:true">常见问题</a>
			</div>
            <div  class="form-div">
                <label>　　仓库：</label><select class="easyui-combobox sel ware" name="search[warehouse_id]" data-options="panelHeight:'auto',panelHeight:200,multiple:true,editable:false"><option value="all">全部</option> <?php if(is_array($warehouse_list)): $i = 0; $__LIST__ = $warehouse_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?> </select>
                　 <label>货品名称：</label><input class="easyui-textbox txt" type="text" name="search[goods_name]"/>
                　 <label>单品条码：</label><input class="easyui-textbox txt" type="text" name="search[barcode]"/>
            </div>
            <div id="<?php echo ($id_list["more_content"]); ?>">

            </div>
        </form>
        <input type="hidden" id="<?php echo ($id_list["hidden_flag"]); ?>" value="1">
        <a href="javascript:void(0)" class="easyui-linkbutton" name="pd_goods" data-options="iconCls:'icon-database-edit',plain:true" onclick="stockManagement.edit();">盘点货品</a>
        <a href="javascript:void(0)" class="easyui-linkbutton" name="adjustprice" data-options="iconCls:'icon-database-edit',plain:true" onclick="stockManagement.adjustCostPrice();">调整成本价</a>
		<a href="javascript:void(0)" class="easyui-menubutton" name="stock_load" data-options="iconCls:'icon-database-refresh',plain:true,menu:'#stock_load'" >初始化导入库存</a>
		<a href="javascript:void(0)" class="easyui-menubutton" name="stock_price" data-options="iconCls:'icon-database-save',plain:true,menu:'#stock_price'" >成本价导入</a>
		<a href="javascript:void(0)" class="easyui-menubutton" name="stock_out" data-options="iconCls:'icon-database-go',plain:true,menu:'#stock_out'" >导出功能</a>
        <?php if($stock_num == 0): ?><a href="javascript:void(0)" class="easyui-linkbutton" name="importStockSpec" data-options="iconCls:'icon-save',plain:true" onclick="stockManagement.importStockSpec('importResponse')">初始化库存</a><?php endif; ?>
        <a href="javascript:void(0)" class="easyui-linkbutton" name="setStockAlarm" data-options="iconCls:'icon-setting',plain:true" onclick="stockManagement.setStockAlarm()">设置预警库存</a>
        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-sign',plain:true" onclick="stockManagement.setFlag()">标记管理</a>
		<a href="javascript:void(0)" class="easyui-linkbutton" name="putOne" data-options="iconCls:'icon-edit',plain:true" onclick="stockManagement.put(this)">上传货品信息</a>
       <!--   <a href="javascript:void(0)" class="easyui-linkbutton" name="putAll" data-options="iconCls:'icon-edit',plain:true" onclick="stockManagement.put(this)">批量上传货品信息</a>-->
		<a href="javascript:void(0)" class="easyui-linkbutton" name="refreshPlatformStock" data-options="iconCls:'icon-reload',plain:true" onclick="stockManagement.refreshPlatformStockDialog()" title="该功能是将平台上的货品库存量更新到系统库存管理中">刷新为平台库存</a>
        <a href="javascript:void(0)" class="easyui-linkbutton" name="delStockGoods" data-options="iconCls:'icon-remove',plain:true" onclick="stockManagement.removes('stock_goods')">删除</a>
        <a href="javascript:void(0)" class="easyui-linkbutton" name="setField" data-options="iconCls:'icon-table-edit',plain:true" onclick="setDatagridField('Stock/StockManagement','stockmanagement','<?php echo ($datagrid["id"]); ?>',1)">设置表头</a>
		<a href="javascript:void(0)" class="easyui-linkbutton" name="show_warehouse" data-options="iconCls:'icon-search',plain:true" onclick="stockManagement.show_total_price()">查看仓库详情</a>
	   <div id="stock_load">
			<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-excel',plain:true" name="upload" onclick="stockManagement.uploadDialog()">Excel导入库存</a>
			<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-down_tmp',plain:true" name="downloadTemplet" onclick="stockManagement.downloadTemplet()">下载导入模板</a>
		</div>
		<div id="stock_price">
			<a href="javascript:void(0)" class="easyui-linkbutton"data-options="iconCls:'icon-excel',plain:true" name="pricedialog" onclick="stockManagement.pricedialog()">导入成本价</a>
			<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-down_tmp',plain:true" name="downloadPriceTemplet" onclick="stockManagement.downloadPriceTemplet()">下载导入模板</a>
		</div>
		<div id="stock_out">
            <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-excel',plain:true" name="exportToExcel" onclick="stockManagement.exportToExcel('csv')">导出csv(推荐)</a>
            <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-excel',plain:true" name="exportToExcel" onclick="stockManagement.exportToExcel('excel')">导出库存Excel</a>
        </div>
   </div>
    <div style="color: rgb(255, 40, 125)"></div>
    <script>
        //# sourceURL=stockmanagement.js
        $(function () {
            $("#stock_tree").changStyleTreeCombo("stock_tree");
			var select_id = "<?php echo ($id_list["tool_bar"]); ?>";
			var select_box = {
				'checkstock' :  $('#'+select_id+" a[name='pd_goods']"),
				'adjustprice' : $('#'+select_id+" a[name='adjustprice']"),
			//	'StockOutData'   : $('#'+select_id+" a[name='stock_out']"),
				'importGoodsStock' : $('#'+select_id+" a[name='importStockSpec']"),
				'settingstockalarm'   : $('#'+select_id+" a[name='setStockAlarm']"),
				'SetField'   :  $('#'+select_id+" a[name='setField']"),
				'uploadexcel'    :  $('#'+select_id+" a[name='upload']"),
				'downloadtemplet' : $('#'+select_id+" a[name='downloadTemplet']"),
				'importprice'  : $('#'+select_id+" a[name='pricedialog']"),
				'downloadpricetemplet' : $('#'+select_id+ " a[name='downloadPriceTemplet']"),
				'exporttoexcel' : $('#'+select_id+" a[name='exportToExcel']"),
                'putOne' : $('#'+select_id+" a[name='putOne']"),
                'putAll' : $('#'+select_id+" a[name='putAll']"),
                'delStockGoods' : $('#'+select_id+" a[name='delStockGoods']"),
                'refreshPlatformStock' : $('#'+select_id+" a[name='refreshPlatformStock']"),
            };
            var name = ['checkstock','adjustprice','importGoodsStock','settingstockalarm','SetField','uploadexcel','downloadtemplet','importprice','downloadpricetemplet','exporttoexcel','delStockGoods','refreshPlatformStock'];
            var button_type = ['linkbutton','linkbutton','linkbutton','linkbutton','linkbutton','linkbutton','linkbutton','linkbutton','linkbutton','linkbutton','linkbutton','linkbutton'];
            var right = JSON.parse('<?php echo ($right); ?>');
            var operator_id = '<?php echo ($operator_id); ?>';
            setTimeout(function () {
                stockManagement = new RichDatagrid(JSON.parse('<?php echo ($params); ?>'));
                $('#'+stockManagement.params.datagrid.id).datagrid('options').rowStyler = function(index,row){

                    return stockManagement.flagRowStatusByRowStyle(index,row);
                };
                stockManagement.flagRowStatusByRowStyle = function(index,row){
                    if(parseInt(row.alarm_flag)){
                        var stock_alarm_color = '<?php echo ($stock_alarm_color); ?>';
                        //return 'background-color:#ee1d24;color:#fff'; //警戒库存
                        return stock_alarm_color; //警戒库存
                    }

                };
				stockManagement.edit = function(){
					var that = this;
					if(that.params.edit==undefined||that.params.edit.url==undefined){return false;}
					var url=that.params.edit.url;
					//var row=$('#'+that.params.datagrid.id).datagrid('getSelected');
					if(that.selectRows==undefined) { messager.alert('请选择操作的行!'); return false; }
					if(that.selectRows.length > 1){ messager.alert('请选择单行编辑!'); return false; }
					if(!that.checkEdit()){return false;}
					var row=that.selectRows[0];
					url += (url.indexOf('?') != -1) ? '&id='+row.id : '?id='+row.id;
					url += '&warehouse_id='+row.warehouse_id; 
					var buttons=[ {text:'确定',handler:function(){ that.submitEditDialog(); }}, {text:'取消',handler:function(){that.cancelDialog(that.params.edit.id)}} ];
					this.showDialog(that.params.edit.id,that.params.edit.title,url,that.params.edit.height,that.params.edit.width,buttons,that.params.edit.toolbar,that.params.edit.ismax);
				}
                stockManagement.setFormData();

                /*function disabled(list){
                    for(var i in list){
                        var list_index = $.inArray(list[i],name);
                        if(button_type[list_index] == 'combobox'){
                            select_box[list[i]][button_type[list_index]]('disable');
                        }else{
                            select_box[list[i]][button_type[list_index]]({disabled:true});
                        }
                    }
                }
                function enable(){
                    for(var i in name){
                        for(var j in right){
                            if(name[i] == right[j]['action']){
                                if(button_type[i] == 'combobox'){
                                    select_box[name[i]][button_type[i]]('enable');
                                }else{
                                    select_box[name[i]][button_type[i]]({disabled:false});
                                }
                            }
                        }
                    }
                }
                if(operator_id < 2){
                    //disabled();
                }else{
                    disabled(name);
                    enable();
                }
                //enable();*/

                stockManagement.uploadDialog = function () {
                    var dialog = $("#<?php echo ($id_list["file_dialog"]); ?>");
                    dialog.dialog({
                        title: "库存导入",
                        width: "350px",
                        height: "160px",
                        modal: true,
                        closed: false,
                        inline: true,
                        iconCls: 'icon-save',
                    });
                };
                stockManagement.upload = function () {
                    var form = $("#<?php echo ($id_list["file_form"]); ?>");
                    var url = "<?php echo U('StockManagement/uploadExcel');?>";
                    var dg = $("#<?php echo ($id_list["datagrid"]); ?>");
                    var dialog = $("#<?php echo ($id_list["file_dialog"]); ?>");
                    $.messager.progress({
                        title: "请稍候",
                        msg: "该操作可能需要几分钟，请稍等...",
                        text: "",
                        interval: 100
                    });
                    form.form("submit", {
                        url: url,
                        success: function (res) {
                            $.messager.progress('close');
                            res = JSON.parse(res);
                            if (res.status==1) {
                                messager.alert(res.msg);
                            } else if (res.status == 0) {
                                dg.datagrid("reload");
                                dialog.dialog("close");
                            } else if (res.status == 2) {
                                $.fn.richDialog("response", res.data, "importResponse");
                                dg.datagrid("reload");
                            }
                            form.form("load", {"file": ""});
                        }
                    });
                };
				stockManagement.show_total_price = function(){
					var that = this;
					var rows = $('#'+that.params.datagrid.id).datagrid('getRows');
					if($.isEmptyObject(rows)){
						messager.alert('仓库中没有货品');
						return;
					}
					var button = [{text:'确定',handler:function(){$('#show_total_price').dialog('close');}}];
					Dialog.show('show_total_price','仓库详情',"<?php echo U('Stock/StockManagement/showTotalPrice');?>",360,760,button);
				};
				stockManagement.pricedialog = function(){
					var dialog = $("#<?php echo ($id_list["import_price"]); ?>");
					dialog.dialog({
						title:"成本价导入",
						width:"350px",
						height:"160px",
						modal:true,
						closed:false,
						inline:true,
						iconCls:'icon-save'
					});
				};
				stockManagement.uploadprice = function(){
					var url = "<?php echo U('Stock/StockManagement/importPrice');?>";
					var Dialog = $("#<?php echo ($id_list["import_price"]); ?>");
					var form = $("#<?php echo ($id_list["form_price"]); ?>");
					var dg = $("#<?php echo ($id_list["datagrid"]); ?>");
					$.messager.progress({
						title:"请稍候",
						msg: "该操作可能需要几分钟，请稍等...",
						text:"",
						interval:100
					});
					form.form("submit",{
						url:url,
						success:function(res){
							$.messager.progress('close');
							res = JSON.parse(res);
							if(res.status == 1){
								messager.alert(res.msg);
							}else if(res.status == 0){
								dg.datagrid("reload");
								Dialog.dialog("close");
							}else{
								$.fn.richDialog("response",res.data,"importResponse");
								dg.datagrid("reload");
							}
							form.form('load',{'file':''});
						}
					});
				};
                stockManagement.removes = function(type){
                    var tb = this.params.datagrid.id;
                    var tb_jq = $('#' + tb);
                    var index = tb_jq.datagrid('options').index;
                    if ( this.selectRows == undefined || this.selectRows.length == 0 || this.selectRows == null) { messager.alert('请选择操作的行!'); return false; }
//                    var sel_rows = tb_jq.datagrid('getSelections');
//                    if($.isEmptyObject(sel_rows)){messager.alert('请选择操作的行!');return false;}
                    var row=this.selectRows[0];
                    var id;
                    if(!(!type)){
                        id=[]; for(i in this.selectRows){ id.push(this.selectRows[i].id); }
                    }else{
                        id=this.selectRows[0].id;
                    }
                    var that = this;
                    var buttons=[ {text:'确定',handler:function(){ that.submitDelStockGoodsSetting(id,type,tb_jq,index,that.params.del_stock_goods.id,that); }}, {text:'取消',handler:function(){$('#'+that.params.del_stock_goods.id).dialog('close');}} ];
                    Dialog.show(this.params.del_stock_goods.id,this.params.del_stock_goods.title,this.params.del_stock_goods.url,this.params.del_stock_goods.height,this.params.del_stock_goods.width,buttons,null,this.params.del_stock_goods.ismax);
                    tb_jq.datagrid('options').index = undefined;
                    //this.selectRows=undefined;
                }
                stockManagement.submitDelStockGoodsSetting = function(id,type,tb_jq,index,div_id,that){
                    if(id==''||id==undefined){messager.alert('没有查询到订单数据，请刷新后重试！');return;}
                    var set_data = $('#'+'<?php echo ($form_id); ?>').form('get');
                    var form_id = $('#'+div_id);
                    var url;
                    if(set_data.is_del_goods_spec==1){
                        url = "<?php echo U('Stock/StockManagement/saveDelStockGoods');?>?is_delete=1&type=2";
                    }else{
                        url = "<?php echo U('Stock/StockManagement/saveDelStockGoods');?>?is_delete=0&type=2";
                    }
                    form_id.dialog('close');
                    $.post(url, { id: id }, function(res) {
                        if(!(!type)){
                            if(res.status==0){that.refresh();}
                            else if(res.status==1){messager.alert( res.info, 'error');}
                            else if(res.status==2){$.fn.richDialog("response", res.info, type);that.refresh();}
                        }else{
                            if (!res.status) { messager.alert( res.info, 'error'); }
                            else { tb_jq.datagrid('deleteRow', index); }
                            tb_jq.datagrid('loaded');
                        }
                    }, 'json')
                }
                stockManagement.exportToExcel = function(type){
                    var url = "<?php echo U('StockManagement/exportToExcel');?>";
                    var id_list = [];
                    for(i in this.selectRows){id_list.push(this.selectRows[i].id);}
                    var forms = $('#stock_search_form').form('get');
                    var values = $('.ware').combobox('getValues');
                    forms["search[warehouse_id]"] = values;
                    var search = JSON.stringify(forms);
                    var form=JSON.stringify(stockManagement.params.search.form_data);
                    var rows = $("#<?php echo ($id_list["datagrid"]); ?>").datagrid("getRows");
                    if(rows == ''){
                        messager.alert('导出不能为空');
                    }
                    else if(id_list != ''){
                        messager.confirm('确定导出选中的库存？',function(r){
                            if(!r){
                                return false;
                            }
                            window.open(url+'?id_list='+id_list+'&search='+search+'&type='+type);
                        });

                    }else if(form == search){
                        messager.confirm('确定导出所有的库存？',function(r){
                            if(!r){
                                return false;
                            }
                            window.open(url+'?id_list='+id_list+'&search='+search+'&type='+type);
                        });
                    }else{
                        messager.confirm('确定导出搜索的库存？',function(r){
                            if(!r){
                                return false;
                            }
                            window.open(url+'?id_list='+id_list+'&search='+search+'&type='+type);
                        });
                    }
                };


                stockManagement.importStockSpec = function(type){
                    var Dialog = $('#<?php echo ($id_list["import_stock"]); ?>');
                    var url = "<?php echo U('Goods/GoodsGoods/importGoodsStock');?>";
                    var tb = this.params.datagrid.id;
                    var tb_jq = $('#' + tb);
                    Dialog.dialog({
                        title: "初始化库存",
                        width: "400px",
                        height: "240px",
                        modal: true,
                        closed: false,
                        inline: true,
                        iconCls: 'icon-save',
                    });
                    stockManagement.init_import_stock = function(){
                        messager.confirm('该操作会导入全部单品库存,确定导入吗？',function(r){
                            if(!r){return false;}
                            $.messager.progress({
                                title: "请稍候",
                                msg: "正在导入，请稍等...",
                                text: "",
                                interval: 100
                            });
                            var data = $('#warehouse_id').combobox('getValue');
                            $.post(url,{warehouse_id: data},function(res){
                                $.messager.progress('close');
                                if(res.status==0){this.refresh();Dialog.dialog('close');messager.alert('操作成功')}
                                else if(res.status==1){messager.alert(res.msg);}
                                else if(res.status==2){$.fn.richDialog("response", res.data, type);this.refresh();}
                            },'json')
                        })
                    }

                }

                stockManagement.downloadTemplet = function(){
                    var url= "<?php echo U('StockManagement/downloadTemplet');?>";
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
                stockManagement.downloadPriceTemplet = function(){
                    var url = "<?php echo U('Stock/StockManagement/downloadPriceTemplet');?>";
                    if(!!window.ActiveXObject || "ActiveXObject" in window){
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
				
				stockManagement.put = function(e){
					var that = this;
					var dg_id = that.params.datagrid.id;
                    var sel_row = $('#'+dg_id).datagrid('getSelections');
					var ids = '';
                    var put_type = $(e)[0]['name'];
					if($.isEmptyObject(sel_row)){
					/*	 messager.confirm('当前未选择货品，将推送全部货品，执行时间较长，是否上传？', function(r){
							if(r){
								$.post("<?php echo U('Stock/StockManagement/put');?>",{'warehouse_id':warehouse_id,'type':1},function(res){
									messager.alert(res.info);
								});
							}else{
								return ;
							}
						 });
						*/
						messager.alert('请选择要上传的货品！');
					}else{
					
						if(sel_row[0]['warehouse_id'] == 0){
							messager.alert('请选择指定仓库!');
							return;
						}
//						if(sel_row[0]['warehouse_type'] != 11){
//							messager.alert('请选择委外仓库!');
//							return;
//						}
						var warehouse_id = sel_row[0]['warehouse_id'];
						for(var item in sel_row){
							ids += sel_row[item]['id']+',';
						}
						$.post("<?php echo U('Stock/StockManagement/put');?>",{'warehouse_id':warehouse_id,'type':2,'id':ids,'put_type':put_type},function(res){
									//messager.alert(res['error_msg']);
                            var r = JSON.parse(res);
                            for(var k in r){
                                if(k == 'updated'){messager.alert('推送成功');dg.datagrid("reload");}
                                else if(k == 'error'){messager.alert(r[k]);}
                                else{
                                    var resultBeforeCheck =  r[1];
                                    $.fn.richDialog("response", resultBeforeCheck, "wms",'');
                                    break;
                                }
                            }
						});
					}
				};
                stockManagement.refreshPlatformStockDialog = function() {
                    var that = this;
                    var select_rows = this.selectRows;
                    var id_list = '';
                    if (select_rows == undefined || select_rows.length == 0) {
                        messager.confirm('确定将全部的库存更改为平台上的库存吗？', function (r) {
                            if (!r) {
                                return false;
                            }
                            var params_url = "?recid_str="+id_list;
                            var url = that.params.select_shop.url+params_url;
                            var button = [ {text:'确定',handler:function(){ that.submitSelectShopDialog(id_list); }}, {text:'取消',handler:function(){that.cancelDialog(that.params.select_shop.id)}} ];
                            that.showDialog(that.params.select_shop.id,that.params.select_shop.title,url,that.params.select_shop.height,that.params.select_shop.width,button,null,that.params.select_shop.ismax);

                        });
                    }else {
                        for (var row in select_rows) {
                            var id = select_rows[row].id;
                            id_list = id_list + id + ",";
                        }
                        id_list = id_list.substr(0, id_list.length - 1);
                        var params_url = "?recid_str="+id_list;
                        var url = that.params.select_shop.url+params_url;
                        var button = [ {text:'确定',handler:function(){ that.submitSelectShopDialog(id_list); }}, {text:'取消',handler:function(){that.cancelDialog(that.params.select_shop.id)}} ];
                        that.showDialog(that.params.select_shop.id,that.params.select_shop.title,url,that.params.select_shop.height,that.params.select_shop.width,button,null,that.params.select_shop.ismax);
                    }
                }

                stockManagement.refreshPlatformStock = function(id_list, shop_id){
                    var dg = $("#<?php echo ($id_list["datagrid"]); ?>");
                    var dialog = $("#<?php echo ($id_list["select_shop"]); ?>");
                    dialog.dialog("close");
                    if(shop_id == ''){
                        messager.alert('刷新失败，选中商品无对应平台货品或无相应店铺权限！');
                        return false;
                    }
                    $.messager.progress({
                     title: "请稍候",
                     msg: "正在更新，请稍等...",
                     text: "",
                     interval: 100
                     });
                    $.post("<?php echo U('StockManagement/refreshPlatformStock');?>", {'recid_str': id_list, 'shop_id': shop_id}, function (res) {
                        $.messager.progress('close');
                        if (res["status"] == 1) {
                            messager.alert(res["msg"]);
                            dg.datagrid("reload");
                        }
                        else if (res["status"] == 2) {
                            //调用dialog显示处理结果
                            //这个窗口可以跟导入的共用
                            $.fn.richDialog("response", res.data, "goods_spec");
                            dg.datagrid("reload");
                        } else if (!res["status"]) {
                            messager.alert(res["msg"]);
                            dg.datagrid("reload");
                        }
                    }, 'json');
                }

                stockManagement.adjustCostPrice = function()
                {
                    //---------判断是否选中
                    var that = this;
                    var dg_id = that.params.datagrid.id;
                    var sel_row = $('#'+dg_id).datagrid('getSelections');
                    if($.isEmptyObject(sel_row)){
                        messager.alert('请选择调价信息!');
                        return;
                    }
                    var params_url = '';
                    var spec_ids = '';
                    if(sel_row.length > 1 && sel_row[0]['ware_num'] == 1){
                        for(var k in sel_row){
                            spec_ids += sel_row[k]['spec_id']+',';
                        }
                        spec_ids = spec_ids.substring(0,spec_ids.length-1);
                        params_url = '?spec_id='+spec_ids;

                        var warehouse_id = sel_row[0]['warehouse_id'];
                        if(!!warehouse_id)
                        {
                            params_url += '&warehouse_id='+warehouse_id;

                        }
                    }else{
                        params_url = '?spec_id='+sel_row[0]['spec_id'];
                        if(!!sel_row.warehouse_id)
                        {
                            params_url += '&warehouse_id='+sel_row[0]['warehouse_id'];
                        }
                    }
                    var url = that.params.adjust.url+params_url;
                    // --------调用调价对话框
                    var button = [ {text:'确定',handler:function(){ that.submitAdjustDialog(); }}, {text:'取消',handler:function(){that.cancelDialog(that.params.adjust.id)}} ];
                    that.showDialog(that.params.adjust.id,that.params.adjust.title,url,that.params.adjust.height,that.params.adjust.width,button,null,that.params.adjust.ismax);
                };
                stockManagement.oldReloadTab = stockManagement.reloadTab;
                stockManagement.reloadTab = function(){
                    var select_rows = $('#'+this.params.datagrid.id).datagrid('getSelections');
                    if(!this.datagridId){ this.datagridId = $('#'+this.params.tabs.id).tabs('getTab',this.tabIndex).find('.easyui-datagrid').attr('id');  }
                    if(this.datagridId.search('stock_spec_log'))
                    {
                        var old_url = this.params.tabs.url;
                        var index = $('#'+this.params.datagrid.id).datagrid('options').index;
                        for(var i in select_rows)
                        {
                            if(index == $('#'+this.params.datagrid.id).datagrid('getRowIndex',select_rows[i]))
                            {
                                if(select_rows[i]['warehouse_id']!= 0) {
                                    this.params.tabs.url = this.params.tabs.url+'?warehouse_id='+select_rows[i].warehouse_id;
                                    break;
                                }
                            }
                        }
                    }
                    this.oldReloadTab();
                    this.params.tabs.url = old_url;
                }
                stockManagement.setStockAlarm = function()
                {
                    //---------判断是否选中
                    var that = this;
                    var dg_id = that.params.datagrid.id;
                    var sel_rows = $('#'+dg_id).datagrid('getSelections');
                    if($.isEmptyObject(sel_rows)){
                        messager.confirm('确定对所有货品进行库存预警？(只针对你选择的仓库的所有货品)',function(r){
                            if(r){
                                var rows = $('#'+dg_id).datagrid('getRows');
                                var info =[];
                                for(var j in rows){
                                    info.push({spec_id:0,warehouse_id:rows[j].warehouse_id,multiple_warehouse:rows[j].multiple_warehouse});
                                    break;
                                }
                                var info_url = '';
                                info_url = '?ids='+ JSON.stringify(info);
                                var url = that.params.stock_alarm.url+info_url;
                                // --------调用调价对话框
                                var button = [ {text:'确定',handler:function(){ that.submitStockAlarmDialog(that.params.stock_alarm.id);}}, {text:'取消',handler:function(){that.cancelDialog(that.params.stock_alarm.id)}} ];
                                that.showDialog(that.params.stock_alarm.id,that.params.stock_alarm.title,url,that.params.stock_alarm.height,that.params.stock_alarm.width,button,null,that.params.stock_alarm.ismax);


                            }else{
                                return ;
                            }
                        });

                    }else{
                        var ids =[];
                            ids.push({num:sel_rows.length,spec_id:sel_rows[0].spec_id,warehouse_id:sel_rows[0].warehouse_id,multiple_warehouse:sel_rows[0].multiple_warehouse});
              
                        var params_url = '';
                        params_url = '?ids='+ JSON.stringify(ids);
                        var url = that.params.stock_alarm.url+params_url;
                        // --------调用调价对话框
                        var button = [ {text:'确定',handler:function(){ that.submitStockAlarmDialog(that.params.stock_alarm.id);}}, {text:'取消',handler:function(){that.cancelDialog(that.params.stock_alarm.id)}} ];
                        that.showDialog(that.params.stock_alarm.id,that.params.stock_alarm.title,url,that.params.stock_alarm.height,that.params.stock_alarm.width,button,null,that.params.stock_alarm.ismax);
                    }
                }
            }, 0);
        });
    </script>

<!-- layout-south-tabs, call add_tabs js function to add tabs -->


</body>
</html>