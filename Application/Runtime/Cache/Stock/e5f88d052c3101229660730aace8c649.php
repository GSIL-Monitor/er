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


<!-- toolbar -->

	<div id="<?php echo ($tool_bar); ?>" style="padding:5px;height:auto">
		<form id="<?php echo ($form); ?>" method="post" style="display: inline-block;vertical-align:middle">
			<label>物流公司：</label><input id='sales_print_logistics_name' name="logistics_name" class="easyui-textbox txt" type="text" disabled=true value=""/>
			<label>生成规则：</label><input class="easyui-combobox txt" name="rule" data-options="width:100,valueField:'id',textField:'name',data:formatter.get_data('logistics_generate_rule'),editable:false,required:true"/>
			<label>递增量：</label><input  class="easyui-numberbox" style = "width:50px" name="increment" type="text" value="1" data-options="precision:0,required:true"/>
		</form>
		<div style="display: inline-block;vertical-align:middle">
			<a href="javascript:void(0)" id="<?php echo ($generate); ?>" name = "button_generate" class="easyui-linkbutton" data-options="iconCls:'icon-add'" >生成单号</a>
			<a href="javascript:void(0)" id="<?php echo ($save); ?>" name = "button_save" class="easyui-linkbutton" data-options="iconCls:'icon-save'" onclick="saveWaybill('<?php echo ($datagrid["id"]); ?>',0)">保存</a>
		</div>
		<div class="form-div">
			<strong name='message_info' style="vertical-align:middle;font-size:10px;color:red;margin-left:20px"></strong>
		</div>
		<div class="form-div">
			<strong name='message_info2' style="vertical-align:middle;font-size:10px;color:red;margin-left:20px"></strong>
		</div>
		<div class="form-div" style="margin-left:530px;">
			<label>批量修改包裹数</label><input  class="easyui-numberbox" style = "width:50px" name="bulk_editing" type="text" value="1" data-options="precision:0,required:true"/>

		</div>
	</div>
	<input type="text" style="display:none" value="" name="defaultStdTemplates"/>

<!-- layout-south-tabs, call add_tabs js function to add tabs -->

	<script>
        //# sourceURL=addWaybill.js
        (function(){
            var toolbar_id = '<?php echo ($tool_bar); ?>';
            var form_id = '<?php echo ($form); ?>';
            var element_selectors ={
                'form'				:$('#'+form_id),
                'logistics_name'	:$('#'+toolbar_id+" :input[name='logistics_name']"),
                'rule'		        :$('#'+toolbar_id+" :input[name='rule']"),
                'increment'			:$('#'+toolbar_id+" :input[name='increment']"),
                'button_generate'	:$('#'+toolbar_id+" a[name='button_generate']"),
                'button_save'		:$('#'+toolbar_id+" a[name='button_save']"),
                'message_info'		:$('#'+toolbar_id+" strong[name='message_info']"),
                'message_info2'		:$('#'+toolbar_id+" strong[name='message_info2']"),
				 'bulk_editing'			:$('#'+toolbar_id+" :input[name='bulk_editing']"),
            };

            //解析出默认标准模板,用来生成
            var templates = '<?php echo ($stdTemplates); ?>';
            if(templates !== ''){
                templates = JSON.parse(templates);
                if(templates.status == 1){
                    messager.alert(templates.msg);
                    $('#stocksalesprint_dialog').dialog('close');
                }else {
                    $('input[name=defaultStdTemplates]').val(templates.success[0].standard_template_url);
                }
            }

            // 可编辑cell的点击事件
            function onClick(index,field,value,bill_type){
                if((field=='logistics_no')&&(bill_type==1)){
                    var ed = $('#<?php echo ($datagrid["id"]); ?>').datagrid('getEditor',{index:parseInt(index),field:field});
					$(ed.target).textbox('readonly');
                }else if((field=='package_count')&&(bill_type==0)){
                    var ed = $('#<?php echo ($datagrid["id"]); ?>').datagrid('getEditor',{index:parseInt(index),field:field});
                    $(ed.target).textbox('readonly');
                }else if((field=='package_count')&&(bill_type==1)){
                    var ed = $('#<?php echo ($datagrid["id"]); ?>').datagrid('getEditor',{index:parseInt(index),field:field});
                    var row = $('#<?php echo ($datagrid["id"]); ?>').datagrid('getRows')[index];
                    if(row.logistics_no != ''){
                        $(ed.target).textbox('readonly');
                    }
				}
            }
            $( function(){
                stockSalesPrint.params.id_list.logistics_no_datagrid_id = '<?php echo ($datagrid["id"]); ?>';
                element_selectors.button_generate.linkbutton({onClick:function(){
                    generateWaybill('<?php echo ($datagrid["id"]); ?>',element_selectors);
                }});
				element_selectors.bulk_editing.numberbox({onChange:function(newValue,oldValue){
				  var waybill_info = $('#<?php echo ($datagrid["id"]); ?>').datagrid('getRows');
				  for(var j in waybill_info){
					if(parseInt(waybill_info[j].bill_type) != 0 && waybill_info[j].logistics_no == ''){
						$('#<?php echo ($datagrid["id"]); ?>').datagrid('updateRow',{index:parseInt(j),row:{package_count:newValue}});
					}
					
				  }
                }});
                setTimeout(function(){
                    var sel_rows = $('#'+stockSalesPrint.params.datagrid.id).datagrid('getSelections');
                    var bill_type=undefined;
                    for(var i in sel_rows){
                        if(sel_rows[i].logistics_id == stockSalesPrint.add_logistics_id){
                            bill_type = sel_rows[i].bill_type;
                            break;
                        }
                    }
                    if(bill_type==undefined)
                    {
                        bill_type = $('#'+stockSalesPrint.params.datagrid.id).datagrid('getSelections')[0].bill_type;
                    }
                    // 绑定cell点击事件
                    $('#<?php echo ($datagrid["id"]); ?>').datagrid('options').onClickCell = function(index,field,value){
                        onClick(index,field,value,bill_type);
                    };
                    if (2 == bill_type){
                        element_selectors.button_save.linkbutton('disable');
                        element_selectors.rule.combobox('disable');
                        element_selectors.increment.numberbox('disable');
                        element_selectors.button_generate.linkbutton({text:'申请电子面单号'});
                        element_selectors.message_info.html('*菜鸟物流请点击"申请电子面单号"申请面单号,申请成功后会自动保存*');
                        element_selectors.message_info2.html('*菜鸟电子面单会在打印时自动获取单号*');
                        element_selectors.bulk_editing.numberbox('disable');
                    }else if (0 == bill_type){
                        $('#<?php echo ($datagrid["id"]); ?>').datagrid().datagrid('enableCellEditing');
                        if(sel_rows.length == 1){element_selectors.button_generate.linkbutton({text:'生成物流单号',disabled:true});}
                        element_selectors.button_generate.linkbutton({text:'生成物流单号'});
                        element_selectors.message_info.html('*1.单击"物流单号"列手动添加物流单号;2.或者填写第一个物流单后点击按钮"生成物流单号"批量生成*');
                        element_selectors.bulk_editing.numberbox('disable');
                    } else{
                        $('#<?php echo ($datagrid["id"]); ?>').datagrid().datagrid('enableCellEditing');
                        element_selectors.button_generate.linkbutton({text:'申请电子面单号'});
                        element_selectors.message_info.html('*京邦达物流或顺丰线下电子面单请点击"申请电子面单号"申请面单号,申请成功后会自动保存* "包裹数"列需要手动输入，如果面单号已经获取，包裹数编辑无效');
                        element_selectors.button_save.linkbutton('disable');
                        element_selectors.rule.combobox('disable');
                        element_selectors.increment.numberbox('disable');
                    }
                }, 0);
            });
        })();


	</script>

</body>
</html>