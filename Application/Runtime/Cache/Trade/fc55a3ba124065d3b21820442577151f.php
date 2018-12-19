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

	<div id="<?php echo ($id_list["toolbar"]); ?>" style="padding:5px;height:auto">
		<div class="form-div">
			<a href="javascript:void(0)" class="easyui-linkbutton" name="add_spec" data-options="iconCls:'icon-add',plain:true">添加单品</a>
			<a href="javascript:void(0)" class="easyui-linkbutton" name="add_suite" data-options="iconCls:'icon-add',plain:true">添加组合装</a>
			<a href="javascript:void(0)" class="easyui-linkbutton" name="clear_info" data-options="iconCls:'icon-redo',plain:true">清空</a>
			<a href="javascript:void(0)" class="easyui-linkbutton" name="delete_goods" data-options="iconCls:'icon-remove',plain:true">删除</a>
			<label style="margin-left: 5px;">包含关系：</label><input class="easyui-combobox txt" text="txt" id="include_relation" name="include_relation" data-options="panelHeight:110,width:90,valueField:'id',textField:'name',onChange:function(newValue,oldValue){tradeCheckIncludeGoods.setIncludeRelationDescribe(newValue,oldValue);},data:[{'id':'0','name':'包含全部'},{'id':'1','name':'包含部分'},{'id':'2','name':'仅包含'}],editable:false,value:'0'">
			<label class="include_describe" style="color: red;margin-left: 5px;"></label>
			<div style="display: inline-block;padding: 1px;float: right;margin-right: 5px;"><label ></label><input style="width: 50px;" name="goods_num"  class="easyui-numberbox" value="1" data-options="min:1,precision:0,required:true" />
				<a href="javascript:void(0)" class="easyui-linkbutton" name="apply_all_row" data-options="iconCls:'icon-forward',plain:true" >应用到所有行</a>
			</div>
		</div>
	</div>
	<script type="text/javascript">
		(function(){
            var editIndex = undefined;
			var select_id = "<?php echo ($id_list["toolbar"]); ?>";
			var include_type = "<?php echo ($type); ?>";
			var select_box = {
				'add_spec'                      : $('#'+select_id+" a[name='add_spec']"),
				'add_suite'                      : $('#'+select_id+" a[name='add_suite']"),
				'clear_info'                    : $('#'+select_id+" a[name='clear_info']"),
				'delete_goods'                  : $('#'+select_id+" a[name='delete_goods']"),
				'goods_num'                     : $('#'+select_id+" input[name='goods_num']"),
				'apply_all_row'                 : $('#'+select_id+" a[name='apply_all_row']"),
				'include_relation'              : $('#'+select_id+" input[name='include_relation']"),
				'include_describe'              : $('#'+select_id+" .include_describe"),
			};
			tradeCheckIncludeGoods = {
				select_box :select_box,
				'id_list' : JSON.parse('<?php echo ($params); ?>'),
				getIncludeRelation: function () {
					return select_box.include_relation.combobox('getValue');
				},
				setIncludeRelation: function (value) {
					select_box.include_relation.combobox('setValue',value);
				},
				addSpec: function(){
					var prefix = tradeCheckIncludeGoods.id_list.add_spec == 'check_include_show_dialog' ? 'include_goods' : 'not_include_goods';
					$('#' + tradeCheckIncludeGoods.id_list.add_spec).richDialog('goodsSpec', tradeCheckIncludeGoods.submitGoodsSpecDialog,{
						'prefix':prefix,
					},tradeCheckIncludeGoods);
				},
				addSuite: function(){
					var prefix = tradeCheckIncludeGoods.id_list.add_suite == 'check_include_add_suite_dialog' ? 'include_goods' : 'not_include_goods';
					$('#'+tradeCheckIncludeGoods.id_list.add_suite).richDialog('goodsSuite', tradeCheckIncludeGoods.submitGoodsSuiteDialog,prefix,false);
				},
				deleteGoods:function(){
					var delete_rows = $('#'+tradeCheckIncludeGoods.id_list.datagrid).datagrid('getSelections');
					if($.isEmptyObject(delete_rows)){
						messager.alert('请选择操作的行');
						return;
					}
					messager.confirm('确认要清除选择的行吗',function(r){
						if(r){
							for(var i in delete_rows){
								var delete_index = $('#'+tradeCheckIncludeGoods.id_list.datagrid).datagrid('getRowIndex',delete_rows[i]);
								$('#'+tradeCheckIncludeGoods.id_list.datagrid).datagrid('deleteRow',delete_index);
							}
							$('#'+tradeCheckIncludeGoods.id_list.datagrid).datagrid('loadData',$('#'+tradeCheckIncludeGoods.id_list.datagrid).datagrid('getData'))

						}
					});
				},
				clearInfo:function(){
					messager.confirm('确认要清空全部数据吗',function(r){
						if(r){
							$('#'+tradeCheckIncludeGoods.id_list.datagrid).datagrid('loadData',{'total':0,'rows':[]});
						}
					});
				},
				submitGoodsSpecDialog :function(up_datagrid,down_datagrid,include_goods_object){
					var merge_result_new = [];
					var update_result_new = [];
					//获取对话框中的添加的数据
					var new_rows = $("#"+down_datagrid).datagrid("getRows");
					var formated_new_rows = utilTool.array2dict(new_rows,['id'],'');
					//获取原有数据
					var include_goods_datagrid = include_goods_object.id_list.datagrid;
					var old_rows = $('#' + include_goods_datagrid).datagrid('getRows');
					var formated_old_rows = utilTool.array2dict(old_rows,['id'],'');
					//过滤重复的单品
					for (var j in formated_new_rows)
					{
						if (formated_old_rows[j] == undefined   || $.isEmptyObject(formated_old_rows))
						{
							var map_suite = {
								'id'           		:formated_new_rows[j].id,
								'spec_id'           :formated_new_rows[j].spec_id,
								'spec_no'   		:formated_new_rows[j].spec_no,
								'goods_name'    	:formated_new_rows[j].goods_name,
								'goods_no'    		:formated_new_rows[j].goods_no,
								'spec_code'   		:formated_new_rows[j].spec_code,
								'spec_name'    		:formated_new_rows[j].spec_name,
								'condition'    		:'等于',
								'num'      			:formated_new_rows[j].num,
								'is_suite'          :0,
							};
							merge_result_new.push($.extend({},map_suite));
						}
						else if(formated_old_rows[formated_new_rows[j].id].is_suite==0){
							update_result_new[formated_new_rows[j].id] = Math.floor(formated_old_rows[formated_new_rows[j].id].num) + Math.floor(formated_new_rows[formated_new_rows[j].id].num);
						}
					}
					for(var j = 0; j < old_rows.length; ++j){
						index = $('#' + include_goods_datagrid).datagrid('getRowIndex', old_rows[j]);
						$('#' + include_goods_datagrid).datagrid('updateRow', {
							index: index,
							row: {
								index: index,
								num: update_result_new[old_rows[j].id],
							}
						});
					}
					for(var new_key in merge_result_new){
						$('#' + include_goods_datagrid).datagrid('appendRow', merge_result_new[new_key]);
					}
					$('#' + tradeCheckIncludeGoods.id_list.add_spec).dialog('close');
					//tradeCheckIncludeGoods.beginEdit(tradeCheckIncludeGoods.id_list);
				},
				submitGoodsSuiteDialog:function(up_datagrid,down_datagrid){
					var merge_result_new = [];
					var update_result_new = [];
					//获取对话框中的添加的数据
					var new_rows=[];
					new_rows[0]=$("#"+up_datagrid).datagrid('getSelected');
					var formated_new_rows = utilTool.array2dict(new_rows,['id'],'');
//					var formated_new_rows[0] = utilTool.array2dict(new_rows,['id'],'');
					//获取原有数据
					var include_goods_datagrid = tradeCheckIncludeGoods.id_list.datagrid;
					var old_rows = $('#' + include_goods_datagrid).datagrid('getRows');
					var formated_old_rows = utilTool.array2dict(old_rows,['id'],'');
					//过滤重复的组合装
					for (var j in formated_new_rows)
					{
						if (formated_old_rows[j] == undefined   || $.isEmptyObject(formated_old_rows))
						{
							var map_suite = {
								'id'           		:formated_new_rows[j].id,
								'spec_id'           :formated_new_rows[j].id,
								'spec_no'   		:formated_new_rows[j].suite_no,
								'goods_name'    	:formated_new_rows[j].suite_name,
								'goods_no'    		:formated_new_rows[j].suite_no,
								'spec_code'   		:'',
								'spec_name'    		:formated_new_rows[j].suite_name,
								'condition'    		:'等于',
								'num'      			:1,
								'is_suite'          :1,
							};
							merge_result_new.push($.extend({},map_suite));
						}
//						else if(formated_old_rows[formated_new_rows[j].id].is_suite==1){
//							update_result_new[formated_new_rows[j].id] = Math.floor(formated_old_rows[formated_new_rows[j].id].num) + Math.floor(formated_new_rows[formated_new_rows[j].id].num);
//						}
					}
					for(var j = 0; j < old_rows.length; ++j){
						index = $('#' + include_goods_datagrid).datagrid('getRowIndex', old_rows[j]);
						$('#' + include_goods_datagrid).datagrid('updateRow', {
							index: index,
							row: {
								index: index,
								num: update_result_new[old_rows[j].id],
							}
						});
					}
					for(var new_key in merge_result_new){
						$('#' + include_goods_datagrid).datagrid('appendRow', merge_result_new[new_key]);
					}
					$('#'+tradeCheckIncludeGoods.id_list.add_suite).dialog('close');
				},
				applyGoodsNum:function(){
					var num = select_box.goods_num.numberbox('getValue');
					if(isNaN(parseInt(num))){
						messager.alert("请填写货品数量")
					}else{
						var rows = $('#'+tradeCheckIncludeGoods.id_list.datagrid).datagrid('getRows');
						for(var i in rows){
							var index = $('#'+tradeCheckIncludeGoods.id_list.datagrid).datagrid('getRowIndex',rows[i]);
							$('#'+tradeCheckIncludeGoods.id_list.datagrid).datagrid('updateRow',{index:index,row:{num:parseInt(num)}});
						}
					}
				},
				setIncludeRelationDescribe:function(newValue, oldValue){
					var describe_str = '';
					var include_relation = tradeCheckIncludeGoods.getIncludeRelation();
					switch (include_relation){
						case '0' :
							if(include_type=='include'){describe_str = '#订单中包含以下全部货品的能显示#';}else{describe_str = '#订单中包含以下全部货品的不显示#';}
							break;
						case '1' :
							if(include_type=='include'){describe_str = '#订单中包含以下一个或者多个货品的能显示#';}else{describe_str = '#订单中包含以下一个或者多个货品的不显示#';}
							break;
						case '2' :
							if(include_type=='include'){describe_str = '#订单中只包含以下货品的能显示#';}else{describe_str = '#订单中只包含以下货品的不显示#';}
							break;
					}
					select_box.include_describe.eq(0).text(describe_str);
				},

			};
			select_box.add_spec.linkbutton({onClick:function(){
				tradeCheckIncludeGoods.addSpec();
			}});
			select_box.add_suite.linkbutton({onClick:function(){
				tradeCheckIncludeGoods.addSuite();
			}});
			select_box.delete_goods.linkbutton({onClick:function(){
				tradeCheckIncludeGoods.deleteGoods();
			}});
			select_box.clear_info.linkbutton({onClick:function(){
				tradeCheckIncludeGoods.clearInfo();
			}});
			select_box.apply_all_row.linkbutton({onClick:function(){
				tradeCheckIncludeGoods.applyGoodsNum();
			}});
            $.extend($.fn.datagrid.methods, {
                editCell: function (jq, param) {
                    return jq.each(function () {
                        var opts = $(this).datagrid('options');
                        var fields = $(this).datagrid('getColumnFields', true).concat($(this).datagrid('getColumnFields'));
                        for (var i = 0; i < fields.length; i++) {
                            var col = $(this).datagrid('getColumnOption', fields[i]);
                            col.editor1 = col.editor;
                            if (fields[i] != param.field) {
                                col.editor = null;
                            }
                        }
                        $(this).datagrid('beginEdit', param.index);
                        for (var i = 0; i < fields.length; i++) {
                            var col = $(this).datagrid('getColumnOption', fields[i]);
                            col.editor = col.editor1;
                        }
                    });
                }
            });
            var editIndex = undefined;
            var editField = undefined;
            var editFields = ['小于','等于','大于'];
            //判断是否编辑结束
            function endEditing() {
                if (editIndex == undefined) { return true }
                if ($('#'+tradeCheckIncludeGoods.id_list.datagrid).datagrid('validateRow', editIndex)) {
                    $('#'+tradeCheckIncludeGoods.id_list.datagrid).datagrid('endEdit', editIndex);
                    editIndex = undefined;
                    return true;
                } else {
                    return false;
                }
            }
            //点击单元格触发的事件
            function onClickCell(index, field) {
                if (endEditing()) {
                    $('#'+tradeCheckIncludeGoods.id_list.datagrid).datagrid('selectRow', index)
                            .datagrid('editCell', { index: index, field: field });
                    editIndex = index;

                }
            }
            //编辑完单元格之后触发的事件
            function onAfterEdit(index, row, changes) {
               // $('#'+tradeCheckIncludeGoods.id_list.datagrid).datagrid('endEdit', editIndex);
                $('#'+tradeCheckIncludeGoods.id_list.datagrid).datagrid('updateRow',{index:index,row:{condition:editFields[changes["condition"]]}});
                editIndex = undefined;
                $('#'+tradeCheckIncludeGoods.id_list.datagrid).datagrid('endEdit', editIndex);
                //stockSalesPrint.selectIndex
                //editField = undefined;
                //…执行编辑单元格后需要执行的操作…
                //var row = $('#'+tradeCheckIncludeGoods.id_list.datagrid).datagrid('getData').rows[index];
                //row["condition"] = changes["condition"]; //refreshRow
                $('#'+tradeCheckIncludeGoods.id_list.datagrid).datagrid('refreshRow', index);
            }
            //编辑完单元格之前触发的事件
            function onBeginEdit(index, row) {
                editField = row.condition;
                $('#'+tradeCheckIncludeGoods.id_list.datagrid).datagrid('endEdit', editIndex);
                editIndex = undefined;
                //…执行编辑单元格后需要执行的操作…
            }
            setTimeout(function(){
				var edit_datagrid_id = tradeCheckIncludeGoods.id_list;
				//tradeCheckIncludeGoods.beginEdit(edit_datagrid_id);
				//$('#'+edit_datagrid_id.datagrid).datagrid('options').erpTabObject = tradeCheckIncludeGoods;
				//$('#'+edit_datagrid_id.datagrid).datagrid('options').onEndEdit = function(index, row, changes){tradeCheckIncludeGoods.endEditRow(index, row, changes, this);};
				//$('#'+edit_datagrid_id.datagrid).datagrid('options').onDblClickCell = function(index, row, value){tradeCheckIncludeGoods.clickRowCondition(index, row, value, this);};
				$('#'+edit_datagrid_id.datagrid).datagrid('options').onAfterEdit = function(index, row, changes){onAfterEdit(index, row, changes);};
				//$('#'+edit_datagrid_id.datagrid).datagrid('options').onBeginEdit = function(index, row){onBeginEdit(index, row);};
				$('#'+edit_datagrid_id.datagrid).datagrid('options').onClickCell = function(index,field,value){onClickCell(index,field,value)};
				//this.keepClickCell = $('#'+edit_datagrid_id.datagrid).datagrid('options').onClickCell;
				//单元格编辑模式 先后顺序必须保持
				$('#'+edit_datagrid_id.datagrid).datagrid('enableCellEditing');
				tradeCheckIncludeGoods.setIncludeRelationDescribe();
			},0)
		})();

	</script>

<!-- layout-south-tabs, call add_tabs js function to add tabs -->


</body>
</html>