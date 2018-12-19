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

<div id='stalls_select_provider'><div>

<!-- toolbar -->

<div id="<?php echo ($id_list["tool_bar"]); ?>">
<div class="form-div">
 <a href="javascript:void(0)" class="easyui-linkbutton" name="button_submit" onclick = "editStalls.sumbitProvider()" data-options="iconCls:'icon-save',plain:true">保存</a>
<label style="color:red;margin-left:200px;font-size:20px;">修改货品对应的供应商和采购价</label>

 <div class="form-div" style="border-top:  1px solid #7CAAB1;padding-top: 2px"></div>
  <label style="width: 80px;">　　采购人：</label>
  <select class="easyui-combobox sel" name="search[purchaser_id]" id="employee_select" data-options="panelHeight:'200px',editable:false " >
   <?php if(is_array($employee_array)): $i = 0; $__LIST__ = $employee_array;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
  </select>
  <div class="form-div" style="border-top:  1px solid #7CAAB1;padding-top: 2px"></div>

</div>
</div>
<script>
//# sourceURL=editStalls.js

$(function(){
	var productStalls = function(params){
		var self = this;
		this.params 		= params;
		this.datagrid_id 	= params.datagrid.id;
		var options = $('#'+this.datagrid_id).datagrid('options');
		for(var i in options.columns[0])
		{
			if(options.columns[0][i].field == 'provider_name')
			{
				options.columns[0][i].editor.options.onClickButton = function(){self.selectProvider(this);};
			}
		}
		$('#'+this.datagrid_id).datagrid().datagrid('enableCellEditing');
		this.keepClickCell = $('#'+this.datagrid_id).datagrid('options').onClickCell;

	}
	productStalls.prototype = {
		selectProvider : function(editor_p){
			var that = this;
			$('#stalls_select_provider').richDialog('provider', this.submitDialogProvider,{
				'prefix':'Stalls'
			},{m_p:that,editor_p:editor_p});
		},

		submitDialogProvider : function(up_d,d_d,params){
			var up_sel_row = $('#'+up_d).datagrid('getSelected');
			var row = $('#' + params.m_p.params.datagrid.id).datagrid('getSelected');
			var index = $('#' + params.m_p.params.datagrid.id).datagrid('getRowIndex',row);		
			$(params.editor_p).textbox('setValue',up_sel_row['provider_name']);
			row.provider_id =up_sel_row['id'];
			$('#stalls_select_provider').dialog('close');
			$(params.editor_p).textbox('releaseFocus');

		},
		sumbitProvider:function(){
			var that = this;
			var rows = $('#'+this.datagrid_id).datagrid('getRows');
			var deal_info = []; 
			for(i in rows){
				var temp_result = {'spec_no':rows[i].spec_no,};
				var number = $('#'+this.datagrid_id).datagrid('getRowIndex',rows[i])+1;
				if(rows[i].provider_name == ''|| rows[i].provider_name == null){
					temp_result['info'] = '第'+number+'行的供应商不能为空';
					deal_info.push(temp_result);
				}
				if(rows[i].price == ''){
					temp_result['info'] = '第'+number+'行的采购价不能为空';
					deal_info.push(temp_result);
				}
			}
			if(!$.isEmptyObject(deal_info)){
				$.fn.richDialog("response",deal_info,"goods_spec");
				return;
			}
			
			var data = {};
			data['order_id'] = that.params.order_id;
			data['purchaser_id'] = $('#employee_select').combobox('getValue');
            data['rows'] = {};
            data['rows']['update'] = $('#'+this.datagrid_id).datagrid('getChanges','updated');
            data['rows']['delete'] = $('#'+this.datagrid_id).datagrid('getChanges','deleted');
            data['rows']['insert'] = $('#'+this.datagrid_id).datagrid('getChanges','inserted');
			//显示载入状态
            $('#'+this.datagrid_id).datagrid('loading');
            $.post("<?php echo U('Purchase/StallsOrderManagement/saveProvider');?>",data,function(res){
                $('#'+that.datagrid_id).datagrid('loaded');
                if(res.status == 1){                  
                     messager.alert(res.info);
                    
                } else {					                 					
					$('#'+that.datagrid_id).datagrid('options').onClickCell = function(){return;};
					$('#'+that.params.dialog_id).dialog('close');
					$('#'+that.params.datagrid_id).datagrid('reload');
			  
                }
            },'json');
		}
	}
		
	setTimeout(function(){
		editStalls = new productStalls(JSON.parse('<?php echo ($params); ?>'));
        $('#employee_select').combobox('select',<?php echo ($purchaser_id); ?>);
//        $.post('<?php echo U("Purchase/StallsOrderManagement/getStallsinfo");?>',{'id':editStalls.params.order_id},function(data){
//			switch(data.status){
//				case 1:
//					messager.alert(data.info);
//					break;
//				case 0:
//					$('#<?php echo ($id_list["datagrid"]); ?>').datagrid('loadData',data);
//					break;
//				default :
//					messager.alert('系统错误,请联系管理员');
//			}
//
//        });
	},0);
});
var editStalls = {};
			
</script>

<!-- layout-south-tabs, call add_tabs js function to add tabs -->


</body>
</html>