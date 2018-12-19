<?php if (!defined('THINK_PATH')) exit();?><div style="margin-top: 5px;">
<a href="javascript:void(0)" id="printing_select" class="easyui-linkbutton" data-options="iconCls:'icon-print',plain:true" onclick="newDealBeforePrint('logistics',<?php echo ($isMulti); ?>,'','')">打印</a>
<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-search',plain:true" onclick="newPreviewLogistics(<?php echo ($isMulti); ?>)">预览</a>
 <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-edit',plain:true" onclick="printerSetting()">设置</a>
<!-- <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-cancel',plain:true" onclick="cancelPrint()">取消</a> -->
 <!-- <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-search',plain:true" onclick="selectPrinter()">选择打印机</a> -->
</div>
<div style="margin-top:10px; margin-left:5px;"><div><span><label for="stockSalesPrint_printer_list">打&nbsp印&nbsp机：&nbsp</label></span>
<input id="stockSalesPrint_logistics_printer_list" class="easyui-combobox" data-options="width:161,onSelect:function(res){onPrinterSelect(res.name,'logistics');}"/></div><div style="margin-top:10px">
<label>选择模板：</label>
<select class="easyui-combobox sel" data-options="width:161,onSelect:function(res){newTemplateOnSelect('logistics');}" id="stock_sales_print_logistics_template">
<?php if(is_array($templates)): $i = 0; $__LIST__ = $templates;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["title"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
<!-- <select class="easyui-combobox sel" id="stock_sales_print_logistics_template">
<?php if(is_array($result)): $i = 0; $__LIST__ = $result;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo->standard_template_url); ?>?template_id=<?php echo ($vo->standard_template_id); ?>" ><?php echo ($vo->standard_template_name); ?></option><?php endforeach; endif; else: echo "" ;endif; ?> -->
</select>
 <div style="margin-top:10px;display:none" id="packagesId">
  <label>包裹数量：</label>
  <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-edit',plain:true" onclick="writePackage()">填写数量（数量默认1）</a>
 </div>
</div>

<div id="logisticsGuidetemplateDiv" style="margin-top:7px;margin-left:57px;display:none"><span style="color:red">没有模板？去</span><a href="javascript:changeTemplatePage()">打印模板</a><span style="color:red">界面下载模板</span></div>
<input type="text" style="display:none" value="" name="stdTemplates"/>
</div>
<script type="text/javascript">
var default_template_id = <?php echo ($template_id); ?>;
$(function(){
	stockSalesPrint.logistics_contents = <?php echo ($contents); ?>;
	stockSalesPrint.printing ='printing_select';
	stockSalesPrint.logistics_print = 'stockSalesPrint_logistics_printer_list';
	stockSalesPrint.logistics_print_template ='stock_sales_print_logistics_template';
	var templates = '<?php echo ($stdTemplates); ?>';
	if(templates !== ''){
		templates = JSON.parse(templates);
		if(templates.status == 1){
			messager.alert(templates.msg);
			$('#stocksalesprint_dialog').dialog('close');
			//$.fn.richDialog("response", templates.fail, "stockout");
		}else if(templates.status == 9){
			$('input[name=stdTemplates]').val('jos');
		}else {
			$('input[name=stdTemplates]').val(templates.success[0].standard_template_url);
		}
	}

	setTimeout(function(){
		if($('#stock_sales_print_logistics_template').combobox('getData').length==0)
			$('#logisticsGuidetemplateDiv').show();
		newSelectPrinter('logistics');
		/*templateOnSelect("logistics");*/
		if(default_template_id != '-1')
		$('#stock_sales_print_logistics_template').combobox('select',<?php echo ($template_id); ?>);
	},0);
});
if('<?php echo ($isMulti==3); ?>'){
   $('#packagesId').show();
}
function getLogisticsTemplatesContents(){
	var contents = <?php echo ($contents); ?>;
	return contents;
}
function getLogisticsGoodsDetail(){
	var goods = <?php echo ($goods); ?>;
	return goods;
}
function getPreviewFormat(){
	var format = "<?php echo ($preview_format); ?>";
	return format;
}

</script>
</div>