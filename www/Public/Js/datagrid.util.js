var editIndex = undefined;
function addDatagrid(id, data){
	$('#'+id).datagrid(data);
}
function endEditing(checkSubmmit){
	if (editIndex == undefined){return true;}
	if ($('#shop_dg').datagrid('validateRow', editIndex)){
		$('#shop_dg').datagrid('endEdit', editIndex);
		if (checkSubmmit && !$.isEmptyObject($('#shop_dg').datagrid('getChanges'))){
			$.messager.alert('警告信息','请将已经填写的信息保存或者撤销!');
			$('#shop_dg').datagrid('selectRow', editIndex).datagrid('beginEdit', editIndex);
			return false;
		}else{
			return editIndex;
		}
	} else {
		$.messager.alert('警告信息','请将数据填写完整!');
		return false;
	}
}
function onClickRow(index){
	if (editIndex != index && false !== endEditing(true)){
		editIndex = index;
		$('#shop_dg').datagrid('selectRow', index)
				.datagrid('beginEdit', index);
	}
}
function append(){
	if (false !== endEditing(true)){
		$('#shop_dg').datagrid('appendRow',{is_disabled:0});
		editIndex = $('#shop_dg').datagrid('getRows').length-1;
		$('#shop_dg').datagrid('selectRow', editIndex)
				.datagrid('beginEdit', editIndex);
	}
}
function accept(){
	if (false !== endEditing(false)){
		var data = $('#shop_dg').datagrid("getChanges");
		$('#shop_dg').datagrid('acceptChanges');
		if (data.length != 0){
			submitData(data);
		}
	}
}
function reject(){
	$('#shop_dg').datagrid('rejectChanges');
	editIndex = undefined;
}