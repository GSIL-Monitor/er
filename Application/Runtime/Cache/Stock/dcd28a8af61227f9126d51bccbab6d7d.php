<?php if (!defined('THINK_PATH')) exit();?><script type="text/javascript">
    //# sourceURL=stockmanagement_check.js

stockManagement.nowCheckSpecId = <?php echo ($spec_id); ?>;
/*stockManagement.stockCheckSelectorList = {
    warehouse_id:$('#'+stockmanagement_check_datagrid_toolbar+" select[name='warehouse_id']"),
}*/
var dailog_tb='stockmanagement_check_datagrid';
$(function(){
	var warehouse_id = "<?php echo ($warehouse_id); ?>"; 
	$('#'+dailog_tb).datagrid({ 
	    data:<?php echo ($data); ?>,
        toolbar:'#stockmanagement_check_datagrid_toolbar',
		border:false,
		collapsible:true,
		singleSelect:true,
		fitColumns:false,  
		fit:true,
		onDblClickCell: check_onClickRow,
	    columns:[[ <?php echo ($datagrid_checkStock); ?> ]]
	});
    $('#'+dailog_tb).datagrid('options').url='<?php echo U("StockManagement/getCheckData");?>';
	if(warehouse_id != 0){
		$('#'+dailog_tb).datagrid('reload', {'warehouse_id':warehouse_id,'spec_id':stockManagement.nowCheckSpecId});
	}
});

function check_onClickRow(index) {
    $('#'+dailog_tb).datagrid('selectRow', index).datagrid('beginEdit', index);
}
stockManagement.submitEditDialog = function(){
    var id = $('#stockmanagement_check_datagrid').datagrid('acceptChanges');
    var updata = $("#stockmanagement_check_datagrid").datagrid("getData");
//    var warehouse_id = stockManagement.stockCheckSelectorList.warehouse_id.combobox('getValue');
    updata = updata.rows[0];
//    updata.warehouse_id = warehouse_id;
    if( undefined ==updata.new_stock_num ||0 == updata.new_stock_num.length ||""==updata.new_stock_num||null ==updata.new_stock_num){
    	messager.alert('请填写实际库存!');
    	return false;
    }else if(0 > updata.new_stock_num){
        messager.alert('填写库存数量请大于零!');
        return false;
    }
	if($.isEmptyObject(updata.warehouse_id) || updata.warehouse_id == null){
		var warehouse_id = $("#warehouse").val();
		updata.warehouse_id = warehouse_id;
	}
    $.post("<?php echo U('Stock/stockManagement/checkStock');?>",updata,function(r){
        switch(r.status)
        {
            case 0:
                messager.alert('盘点成功！');
                $("#stockmanagement_edit").dialog('close');
                stockManagement.refresh();
                break;
            case 1:
                messager.alert(r.info);
                break;
            default:
                messager.alert('操作异常，请联系管理员！');
                break;
        }
    });
}
</script>
<table id="stockmanagement_check_datagrid" ></table>
<div id="stockmanagement_check_datagrid_toolbar" style="padding:5px;height:auto">
    <label class="four-character-width">仓库：</label><select id="warehouse" class="easyui-combobox sel" name="warehouse_id" data-options="panelHeight:'200',onSelect:function(record){
        if(stockManagement.nowCheckSpecId==undefined || $.isEmptyObject(stockManagement.nowCheckSpecId) || stockManagement.nowCheckSpecId == ''){
                var rows = $('#'+dailog_tb).datagrid('getRows');
                if(!$.isEmptyObject(rows)){
                    stockManagement.nowCheckSpecId = rows[0]['id'];
                }
            }
            $('#'+dailog_tb).datagrid('reload', {'warehouse_id':record.value,'spec_id':stockManagement.nowCheckSpecId});

    }
"><?php if(is_array($warehouse_list)): $i = 0; $__LIST__ = $warehouse_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?> </select>
<label id="stock_pd_goods" style="color:red;margin-left:100px;font-size:20px;">盘点货品不能批量修改</label></div>