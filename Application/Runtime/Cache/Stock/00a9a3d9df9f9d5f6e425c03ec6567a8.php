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
 <div data-options="region:'south',split:true" style="height:30%;background:#eee;overflow:hidden;"> <div class="easyui-tabs" data-options="fit:true,border:false,plain:true" id="<?php echo ($id_list["tab_container"]); ?>"> </div> </div> 
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

 <div id="<?php echo ($id_list["file_dialog"]); ?>" class="easyui-panel" style="padding:25px 50px 25px 50px">
        <form id="<?php echo ($id_list["file_form"]); ?>" method="post" enctype="multipart/form-data">
            <div style="margin-bottom:25px">
                <input class="easyui-filebox" name="file" data-options="prompt:'请选择文件...','buttonText':'请选择文件'" style="width:100%;">
            </div>
            <div align="center">
                <a href="javascript:void(0)" class="easyui-linkbutton" style="width:50%" onclick="stockInManagement.upload()">上传</a>
            </div>
        </form>
    </div>
 
 
<!-- toolbar -->

    <div id="<?php echo ($id_list["tool_bar"]); ?>" style="padding:5px;height:auto">
        <form id="stockin_search_form" class="easyui-form" method="post">
            <div class="form-div">
                <label>　入库单号：</label><input class="easyui-textbox txt" type="text" name="search[stockin_no]" style="width: 130px;"/>
                <label>　　商家编码：</label><input class="easyui-textbox txt" type="text" name="search[spec_no]" style="width: 130px;"/>
                <label>　货品编号：</label><input class="easyui-textbox txt" type="text" name="search[goods_no]" style="width: 130px;"/>
                <label>　条形码：</label><input class="easyui-textbox txt" type="text" name="search[barcode]" style="width: 130px;"/>
				<label>　来源单号：</label><input class="easyui-textbox txt" type="text" name="search[src_order_no]" style="width: 130px;"/>
				<!-- <a href="javascript:void(0)" onclick="stockInManagement.clickMore(this);">更多</a> -->
			   <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-search'" onclick="stockInManagement.submitSearchForm();">搜索</a>
			   <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-redo'" onclick="stockInManagement.loadFormData();">重置</a>
            </div>
			<!-- <div id="<?php echo ($id_list["more_content"]); ?>"> -->
				<div class="form-div">
					<label>入库单状态：</label><input class="easyui-combobox txt" name="search[status]" data-options="valueField:'id',textField:'name',data:formatter.get_data('stockin_status')"/>
					<label>　入库单类别：</label><input class="easyui-combobox txt" name="search[src_order_type]" data-options="valueField:'id',textField:'name',data:formatter.get_data('stockin_type')"/>
					<label>　物流公司：</label><select class="easyui-combobox sel" name="search[logistics_id]" data-options="panelHeight:'100px',editable:false " style="width: 130px;">
                    <?php if(is_array($logistics_array)): $i = 0; $__LIST__ = $logistics_array;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
					</select>
					<label>　经办人：</label><select class="easyui-combobox sel" name="search[operator_id]" data-options="panelHeight:'100px',editable:false " style="width: 130px;">
						<?php if(is_array($employee_array)): $i = 0; $__LIST__ = $employee_array;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
					</select>
					<label>　　　仓库：</label><select class="easyui-combobox sel" name="search[warehouse_id]" data-options="panelHeight:'auto'"> <?php if(is_array($warehouse_array)): $i = 0; $__LIST__ = $warehouse_array;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?> </select>
				</div>
            <!-- </div> -->
        </form>
        <input type="hidden" id="<?php echo ($id_list["hidden_flag"]); ?>" value="1">
        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-edit',plain:true" onclick = "stockInManagement.edit()";>编辑</a>
        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-submit',plain:true" onclick = "stockInManagement.submitStockinOrder()";>提交</a>
        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-redo',plain:true" onclick = "stockInManagement.deleteStockInOrder()";>取消</a>
		<!--<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-save',plain:true" onclick = "stockInManagement.send()";>推送</a>
		<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-remove',plain:true" onclick = "stockInManagement.cancel_other_in()";>取消委外单</a>-->
		<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-excel',plain:true" onclick="stockInManagement.uploadDialog()">入库导入</a>
		<a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-down_tmp',plain:true" onclick="stockInManagement.downloadTemplet()">下载模板</a>
        <a href="javascript:void(0)" class="easyui-menubutton" data-options="iconCls:'icon-excel',plain:true,menu:'#stock_in_export'" >导出功能</a>
        <div id="stock_in_export">
            <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-excel',plain:true" name="exportToExcel" onclick="stockInManagement.exportToExcel('csv')">导出csv(推荐)</a>
            <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-excel',plain:true" name="exportToExcel" onclick="stockInManagement.exportToExcel('excel')">导出到Excel</a>
        </div>

    </div>
    <script>
        //# sourceURL=stockin_management.js
        $(function(){setTimeout(function(){
            stockInManagement = new RichDatagrid(JSON.parse('<?php echo ($params); ?>'));
            stockInManagement.setFormData();
            stockInManagement.edit = function(){
                var data = $('#'+this.params.datagrid.id).datagrid('getSelected');
                var rows = $('#' + this.params.datagrid.id).datagrid('getSelections');
                if(data == null){
                    messager.alert("请选择操作的行!");
                    return;
                }
                if(rows.length>1){
                    messager.alert("请选择单行编辑!");
                    return;
                }
                var status = data.status;
                if(status != 20){
                    messager.alert("入库单类型不正确，只能返回编辑编辑中的入库单！");
                    return;
                }
                var src_order_type = data.src_order_type;
                if(src_order_type != "1" && src_order_type != "3" && src_order_type != "6" && src_order_type != "11"){
                    messager.alert("入库类型不正确，请重新选择！");
                    return;
                }
                Dialog.show('reason_show_dialog','入库单编辑',"<?php echo U('Stock/StockInOrder/show');?>"+"?id="+data.id+'&parent_win='+'{"dialog_id":"reason_show_dialog","datagrid_id":"'+stockInManagement.params.datagrid.id+'"}',600,1200,[]);

            };
            stockInManagement.deleteStockInOrder = function(){
            	var that = this;
                var data = $('#'+this.params.datagrid.id).datagrid('getSelected');
                var rows = $('#' + this.params.datagrid.id).datagrid('getSelections');
                if(data == null){
                    messager.alert("请选择操作的行!");
                    return;
                }
                if(rows.length>1){
                    messager.alert("请选择单行取消!");
                    return;
                }
                var status = data.status;
                var id = data.id;
                if(status != 20){
                    messager.alert("入库单类型不正确，只能取消编辑中的入库单！");
                    return;
                }
                messager.confirm('确定取消入库单吗？',function(r){
                	if(r){ 
	                	$.post("<?php echo U('Stock/StockInManagement/deleteStockInOrder');?>",{'id':id}, function(r){
		                    switch (r.status){
		                        case 1:
		                            messager.alert(r.info);
		                            break;
		                        case 0:
		                            var index = $('#'+that.params.datagrid.id).datagrid('getRowIndex', data);
		                            $('#'+that.params.datagrid.id).datagrid('updateRow',{
		                                index: index,
		                                row:{
		                                    status: '10',
		                                }
		                            })
		                            break;
		                        default :
		                            messager.alert("系统发生错误，请与管理员联系！");
		                    }
	                	});
                	}
                });
               
            };
			stockInManagement.send = function(){
				var that = this;
                var data = $('#'+this.params.datagrid.id).datagrid('getSelected');
                if(data == null){
                    messager.alert("请选择操作的行!");
                    return;
                }
                var status = data.status;
                var id = data.id;
            /*   if(status != 32){
                    messager.alert("入库单类型不正确，只能推送待推送的入库单！");
                    return;
                }
				 var src_order_type = data.src_order_type;
                if(src_order_type != "1" && src_order_type != "3" && src_order_type != "6" && src_order_type != "11"){
                    messager.alert("入库类型不正确，请重新选择！");
                    return;
                }*/
                messager.confirm('确定推送入库单吗？',function(r){
                	if(r){
	                	$.post("index.php/Stock/StockInManagement/send",{'id':id},function(r){
		                    /*switch (r.status){
		                        case 0:
		                            messager.alert(r.info);
		                            break;
		                        case 1:
		                            messager.alert(r.info);
		                            break;
		                        default :
		                            messager.alert("系统错误，请与管理员联系！");
		                    }*/
							r = JSON.parse(r);
							for(var k in r){
							if(k == 'updated'){messager.alert('推送成功');}
							else if(k == 'error'){messager.alert(r[k]);}
							else{
							    var resultBeforeCheck =  r[1];
								$.fn.richDialog("response", resultBeforeCheck, "wms",'');
								break;
							}
							}
	                	});
	                }
                });
			};
			
			stockInManagement.cancel_other_in = function(){
				var that = this;
                var data = $('#'+this.params.datagrid.id).datagrid('getSelected');
                if(data == null){
                    messager.alert("请选择操作的行!");
                    return;
                }
                var status = data.status;
                var id = data.id;
                if(status != 35){
                    messager.alert("入库单类型不正确，只能取消委外待入库的入库单！");
                    return;
                }
                messager.confirm('确定推送入库单吗？',function(r){
                	if(r){
	                	$.post("index.php/Stock/StockInManagement/cancel_other_in",{'id':id},function(r){
		                    switch (r.status){
		                        case 0:
		                             messager.alert(r.info); break;
		                        case 1:
		                            messager.alert(r.info);
		                            break;
		                        default :
		                            messager.alert("系统错误，请与管理员联系！");
		                    }
	                	});
	                }
                });
			};
			
            stockInManagement.submitStockinOrder = function(){
            	var that = this;
                var data = $('#'+this.params.datagrid.id).datagrid('getSelected');
                var rows = $('#' + this.params.datagrid.id).datagrid('getSelections');
                if(data == null){
                    messager.alert("请选择操作的行!");
                    return;
                }
                if(rows.length>1){
                    messager.alert("请选择单行提交!");
                    return;
                }
                var status = data.status;
                var id = data.id;
                if(status != 20){
                    messager.alert("入库单类型不正确，只能提交编辑中的入库单！");
                    return;
                }
                var src_order_type = data.src_order_type;
                if(src_order_type != "1" && src_order_type != "3" && src_order_type != "6" && src_order_type != "11"){
                    messager.alert("入库类型不正确，请重新选择！");
                    return;
                }
                messager.confirm('确定提交入库单吗？',function(r){
                	if(r){
	                	$.post("index.php/Stock/StockInManagement/submit",{'id':id },function(r){
		                    switch (r.status){
		                        case 0:
		                            var index = $('#'+that.params.datagrid.id).datagrid('getRowIndex', r.data);
		                            that.updateRowByReturnData(r.data);
		                            break;
		                        case 1:
		                            messager.alert(r.info);
		                            break;
		                        default :
		                            messager.alert("系统错误，请与管理员联系！");
		                    }
	                	});

	                }
                });
                
            };
            stockInManagement.updateRowByReturnData = function(updateData){
            	var page_rows = $('#'+this.params.datagrid.id).datagrid('getRows');
            	for(var row_index in page_rows){
            		if(page_rows[row_index].id == updateData.id){
            			$('#'+this.params.datagrid.id).datagrid('updateRow',{
                            index: parseInt(row_index),
                            row:updateData
                        });
            		}
            	}
            };
			
			stockInManagement.uploadDialog = function(){
			var dialog = $("#<?php echo ($id_list["file_dialog"]); ?>");
			dialog.dialog({
				title:'入库导入',
				width:'350px',
				height:'160px',
				modal:true,
				closed:false,
				inline:true,
				iconCls: 'icon-save',
			});
		};
		stockInManagement.upload = function(){
			var form = $("#<?php echo ($id_list["file_form"]); ?>");
			var dg = $("#<?php echo ($id_list["datagrid"]); ?>");
			var dialog = $("#<?php echo ($id_list["file_dialog"]); ?>");
			var url = "<?php echo U('Stock/StockInManagement/upload');?>";
			$.messager.progress({
				title:"请稍等",
				msg:"该操作可能需要几分钟，请稍候",
				text :'',
				interval:100
			
			});
			form.form("submit",{
				url:url,
				success:function(res){
					$.messager.progress("close");
					res =JSON.parse(res);
					
					if(res.status == 1){
						messager.alert(res.msg);
					}else if(res.status == 0){
						dg.datagrid("reload");
                        dialog.dialog("close");
					}else{
						 $.fn.richDialog("response", res.data, "importResponse");
                         dg.datagrid("reload");
					}
				form.form("load", {"file": ""});
				},
			});
		};

		stockInManagement.downloadTemplet = function(){
			var url= "<?php echo U('Stock/StockInManagement/downloadTemplet');?>";
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
        stockInManagement.exportToExcel = function(type){
            var url = "<?php echo U('Stock/StockInManagement/exportToExcel');?>";
            var id_list = [];
            for(var i in this.selectRows){id_list.push(this.selectRows[i].id);}
            var forms = $('#stockin_search_form').form('get');
            var search = JSON.stringify(forms);
            var form=JSON.stringify(stockInManagement.params.search.form_data);
            var rows = $("#<?php echo ($datagrid["id"]); ?>").datagrid("getRows");
            if(rows == ''){
                messager.alert('导出不能为空');
            }
            else if(id_list != ''){
                messager.confirm('确定导出选中的入库单？',function(r){
                    if(!r){
                        return false;
                    }
                    window.open(url+'?id_list='+id_list+'&search='+search+'&type='+type);
                });

            }else if(form == search){
                var total = $("#<?php echo ($datagrid["id"]); ?>").datagrid("getData").total;
                var num = workTime.getWorkTimeNum();
                if(total>num){
                    messager.alert('8:00-19:00可以导出1000条，其余时间可以导出4000条!');
                    return;
                }
                messager.confirm('确定导出所有的入库单？',function(r){
                    if(!r){
                        return false;
                    }
                    window.open(url+'?id_list='+id_list+'&search='+search+'&type='+type);
                });
            }else{
                messager.confirm('确定导出搜索的入库单？',function(r){
                    if(!r){
                        return false;
                    }
                    window.open(url+'?id_list='+id_list+'&search='+search+'&type='+type);
                });
            }
        };

        },0);});

        
    </script>

<!-- layout-south-tabs, call add_tabs js function to add tabs -->


</body>
</html>