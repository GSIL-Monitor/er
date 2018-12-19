<?php if (!defined('THINK_PATH')) exit();?>
<style>
.txt-split{width:100px;}
.footer-split{padding-left:5px;height:5%;background-color: #f4f4f4;}
.red-txt{color:red;}
</style>
<script>
</script>
<div style="height: 50%;">
<table id="<?php echo ($datagrid["id"]); ?>" class="<?php echo ($datagrid["class"]); ?>" data-options='<?php $dataOptions = array_merge(array ( 'border' => false, 'fit' => true, 'fitColumns' => true, 'rownumbers' => true, 'singleSelect' => true, 'pagination' => true, 'pageList' => array ( 0 => 20, 1 => 50, 2 => 100, ), 'pageSize' => 20, ), $datagrid["options"]);if(isset($dataOptions['toolbar']) && substr($dataOptions['toolbar'],0,1) != '#'): unset($dataOptions['toolbar']); endif;if(isset($dataOptions['methods'])): unset($dataOptions['methods']);endif; echo trim(json_encode($dataOptions), '{}[]').((isset($datagrid["options"]['toolbar']) && substr($datagrid["options"]['toolbar'],0,1) != '#')?',"toolbar":'.$datagrid["options"]['toolbar']:null).(isset($datagrid["options"]['methods'])? ','.$datagrid["options"]['methods']:null); ?>' style="<?php echo ($datagrid["style"]); ?>" ><thead><tr><?php if(is_array($datagrid["fields"])):foreach ($datagrid["fields"] as $key=>$arr):if(isset($arr['formatter'])):unset($arr['formatter']);endif;if(isset($arr['methods'])):unset($arr['methods']);endif;if(isset($arr['editor'])):unset($arr['editor']);endif;echo "<th data-options='".trim(json_encode($arr), '{}[]').(isset($datagrid["fields"][$key]['formatter'])?",\"formatter\":".$datagrid["fields"][$key]['formatter']:null).(isset($datagrid["fields"][$key]['editor'])?",\"editor\":".$datagrid["fields"][$key]['editor']:null).(isset($datagrid["fields"][$key]['methods'])?",".$datagrid["fields"][$key]['methods']:null)."'>".$key."</th>";endforeach;endif; ?></tr></thead></table>
<div id="split_main_stalls_datagrid_toolbar">
<div class="form-div">
<label>档口单号：</label><input class="easyui-textbox" style="width:200px;" type="text" name="src_no" value="<?php echo ($stalls_no["stalls_no"]); ?>" data-options="disabled:true"/>
<label style="color: blue;margin-left: 30px;" >#编辑对应货品的<span class='red-txt'>拆分数</span>->点击<span class='red-txt'>预览新档口单</span>->确认无误，点击<span class='red-txt'>确定</span>#</label>
</div>
</div>
</div>
<div style="height: 50%;">
<table id="<?php echo ($datagrid2["id"]); ?>" class="<?php echo ($datagrid2["class"]); ?>" data-options='<?php $dataOptions = array_merge(array ( 'border' => false, 'fit' => true, 'fitColumns' => true, 'rownumbers' => true, 'singleSelect' => true, 'pagination' => true, 'pageList' => array ( 0 => 20, 1 => 50, 2 => 100, ), 'pageSize' => 20, ), $datagrid2["options"]);if(isset($dataOptions['toolbar']) && substr($dataOptions['toolbar'],0,1) != '#'): unset($dataOptions['toolbar']); endif;if(isset($dataOptions['methods'])): unset($dataOptions['methods']);endif; echo trim(json_encode($dataOptions), '{}[]').((isset($datagrid2["options"]['toolbar']) && substr($datagrid2["options"]['toolbar'],0,1) != '#')?',"toolbar":'.$datagrid2["options"]['toolbar']:null).(isset($datagrid2["options"]['methods'])? ','.$datagrid2["options"]['methods']:null); ?>' style="<?php echo ($datagrid2["style"]); ?>" ><thead><tr><?php if(is_array($datagrid2["fields"])):foreach ($datagrid2["fields"] as $key=>$arr):if(isset($arr['formatter'])):unset($arr['formatter']);endif;if(isset($arr['methods'])):unset($arr['methods']);endif;if(isset($arr['editor'])):unset($arr['editor']);endif;echo "<th data-options='".trim(json_encode($arr), '{}[]').(isset($datagrid2["fields"][$key]['formatter'])?",\"formatter\":".$datagrid2["fields"][$key]['formatter']:null).(isset($datagrid2["fields"][$key]['editor'])?",\"editor\":".$datagrid2["fields"][$key]['editor']:null).(isset($datagrid2["fields"][$key]['methods'])?",".$datagrid2["fields"][$key]['methods']:null)."'>".$key."</th>";endforeach;endif; ?></tr></thead></table>
<div id="new_sub_stalls_datagrid_toolbar">
<hr style="border:none;border-top:2px dotted #95B8E7;">
<div class="form-div">
<!--<label>档口单号：</label><input class="easyui-textbox" style="width:200px;" type="text" name="src_tids" data-options="disabled:true"/>-->
<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-add'" onclick="splitMainStallsToSubOrder()">预览新档口单</a>
<label style="color: blue;margin-left: 30px;" >#上方为原档口单所剩货品信息，下方为拆分单货品信息#</label>
</div>
</div>
</div>
<script type="text/javascript">
//# sourceURL=stalls.split.js
stallsManagement.datagrid_id='<?php echo ($datagrid["id"]); ?>';
stallsManagement.datagrid_id_new='<?php echo ($datagrid2["id"]); ?>';
stallsManagement.split_flag_arr={};
stallsManagement.split_flag_arr.tip_type=0;
stallsManagement.split_element_arr;
stallsManagement.split_value_arr={};
function endSplitStallsEdit(index,row,changes){
	stallsManagement.split_flag_arr.tip_type=0;
	if(parseFloat(row.split_num)>parseFloat(row.num)){
		stallsManagement.split_flag_arr.tip_type=1;
		messager.alert('拆分的数量不能大于货品数量');
		row.split_num=0;//大于剩余数量，置为0
		return;
	}else if(parseFloat(row.split_num)<0){
		stallsManagement.split_flag_arr.tip_type=2;
		messager.alert('拆分的数量不能小于0');
		row.split_num=0;//小于0，置为0
		return;
	}else if(row.split_num==undefined){
		row.split_num=0;//为空，置为0
		return;
	}
	var point_number = <?php echo ($point_number); ?>;
	row.left_num=parseFloat(row.num-row.split_num).toFixed(point_number);
	row.amount=(parseFloat(row.price)*row.left_num).toFixed(4);
	return;
}
function getSplitStallsSelect(index, row){
	stallsManagement.split_flag_arr.index=index;
}
function splitMainStallsToSubOrder(){
	var dg=$('#'+stallsManagement.datagrid_id);
	dg.datagrid('endEdit',stallsManagement.split_flag_arr.index);
	switch(stallsManagement.split_flag_arr.tip_type){
	case 1:stallsManagement.split_flag_arr.tip_type=0;return;
	case 2:stallsManagement.split_flag_arr.tip_type=0;return;
	}
	var rows=dg.datagrid('getRows');
	var rows_new=[];
	var set_val=[0];//设置默认 set_val[0]=0;set_val[1]=0;set_val[2]=0;set_val[3]=0;
	var sum_goods=0;
	var j=0;//计数-拆分出来的订单数量
	var row_new=[];
	$.each(rows,function(key,val){
		sum_goods+=parseFloat(val.left_num);
		if(val.split_num>0){
			if(parseFloat(val.split_num)>parseFloat(val.num)||parseFloat(val.split_num)<0||val.split_num==undefined){messager.alert('拆分的数量不能大于货品数量并且不能少于0');return;}
			row_new=$.extend(true, [], val);
			row_new.num=row_new.split_num;
			row_new.actual_num=row_new.num;
			rows_new[j]=row_new;
			set_val[0]+=parseFloat(rows_new[j].price)*rows_new[j].num; 
			j++;
		}
	});
	var new_dg=$('#'+stallsManagement.datagrid_id_new);
	var new_rows=new_dg.datagrid('getRows');
	if(j==0&&new_rows.length==0){messager.alert('至少有一件货品被拆分，请编辑对应货品的拆分数');return;}
	if(sum_goods==0){messager.alert('原档口单至少保留一个货品，请不要都拆出');return;}
	stallsManagement.split_element_arr[0].textbox().textbox('setValue','<?php echo ($stalls_no["stalls_no"]); ?>');
	var data={total:j,rows:rows_new};
	new_dg.datagrid('loadData',data);
}
stallsManagement.submitTradeCheckDialog=function(){
	//var row=$('#'+tb).datagrid('getSelected');
	var row=stallsManagement.selectRows[0];
	var data={};
	var rows=$('#'+stallsManagement.datagrid_id).datagrid('getRows');
	var rows_arr=[];
	for(var i=0,j=0;i<rows.length;i++){if(parseFloat(rows[i].split_num)>0){ rows_arr[j]=rows[i];j++;}}
	var rows2=$('#'+stallsManagement.datagrid_id_new).datagrid('getRows');
	if(rows2.length==0){messager.info('请先点击“生成新档口单”');return;}
	if(rows_arr.length==0||rows2.length==0){$.messager.confirm('提示','未拆分档口单，是否关闭',function(r){if(!r){return;} else{$('#'+stallsManagement.params.id_list.split).dialog('close');return;}})}
	data['main_orders']=JSON.stringify(rows_arr);
	$.post('<?php echo U("StallsOrderManagement/splitStalls");?>?id='+row.id,data,function(res){
		if(res.status){
			messager.alert(res.info);
			return false;
		}else{
			messager.alert(res.info);
			$('#'+stallsManagement.params.id_list.split).dialog('close');
			stallsManagement.refresh();
		}
	},"JSON");
}

function loadOrdersData(){	
	var json_orders=<?php echo ($split_stalls_order_data); ?>;
	$('#'+stallsManagement.datagrid_id).datagrid('loadData',json_orders);
}
function initSplitElement(){
	stallsManagement.split_element_arr={0:$("#new_sub_stalls_datagrid_toolbar input[name='src_tids']")};
}
$(function(){
	setTimeout(loadOrdersData,0);
	$('#'+stallsManagement.datagrid_id).datagrid().datagrid('enableCellEditing');
	initSplitElement();
});
</script>