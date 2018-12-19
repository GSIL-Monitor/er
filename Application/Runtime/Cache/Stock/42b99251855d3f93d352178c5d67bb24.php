<?php if (!defined('THINK_PATH')) exit();?>
<div>
 <a href="javascript:void(0)" id="printing_data" class="easyui-linkbutton" data-options="iconCls:'icon-print',plain:true" onclick="newDealBeforePrint('goods',0,'','')">打印</a>
<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-search',plain:true" onclick="newPreviewGoods()">预览</a>
<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-edit',plain:true" onclick="printerSetting()">设置</a></div>
<!-- <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-search',plain:true" onclick="selectPrinter()">选择打印机</a> -->
<div style="margin-top:10px;margin-left:5px">
<div><span><label for="stockSalesPrint_printer_list">打&nbsp印&nbsp机：&nbsp</label></span>
<input id="stockSalesPrint_goods_printer_list" class="easyui-combobox" data-options="width:161,onSelect:function(res){onPrinterSelect(res.name,'goods');}"/>
</div><div style="margin-top:10px">
<label>选择模板：</label>
<select class='easyui-combobox sel' data-options="width:161,onSelect:function(res){newTemplateOnSelect('goods');}" id='stock_sales_print_goods_template'>
<?php if(is_array($goods_template)): $i = 0; $__LIST__ = $goods_template;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
</select>
	<div id="goodsGuideTemplateDiv" style="margin-top:7px;margin-left:57px;display:none"><span style="color:red">没有模板？去</span><a href="javascript:changeTemplatePage()">打印模板</a><span style="color:red">界面下载模板</span></div>
</div>
</div>
	<script>
var default_template_id = <?php echo ($template_id); ?>;
$(function(){
	stockSalesPrint.goods_contents = <?php echo ($contents); ?>;
	stockSalesPrint.printing ='printing_data';
	stockSalesPrint.goods_print = 'stockSalesPrint_goods_printer_list';
	stockSalesPrint.goods_print_template ='stock_sales_print_goods_template';
	setTimeout(function(){
		newSelectPrinter('goods');
	/*	templateOnSelect("goods");*/
	if($('#stock_sales_print_goods_template').combobox('getData').length == 0)
		$('#goodsGuideTemplateDiv').show();
		if(default_template_id != '-1')
		$('#stock_sales_print_goods_template').combobox('select',<?php echo ($template_id); ?>);
	},10);
});
function getGoodsTemplatesContents(){
	var contents = <?php echo ($contents); ?>;
	return contents;
}
function getGoodsGoodsDetail(){
	var goods = <?php echo ($goods); ?>;
	return goods;
}
function getPreviewFormat(){
	var format = "<?php echo ($preview_format); ?>";
	return format;
}
</script>
</div>