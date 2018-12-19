//# sourceURL=jquery.extends.js

//0:unchecked, 1:checked, all:indeterminate
(function ($) {
    $.fn.triStateCheckbox = function (method) {
        if (typeof(method) == "string") {
            var func = $.fn.triStateCheckbox.methods[method];
        }
        if (func) {
            return func(this);
        }
    };
    $.fn.triStateCheckbox.methods = {
        init: function (jq) {
            $(jq).val('all').prop({indeterminate: true});
        },
        click: function (that) {
            var value = $(that).val();
            if ('all' == value) {
                $(that).val(1).prop({indeterminate: false, checked: true});
            } else if (1 == value) {
                $(that).val(0).prop({indeterminate: false, checked: false});
            } else if (0 == value) {
                $(that).val('all').prop({indeterminate: true});
            }
        }
    };

    /*
     * method:调用的页面，单品列表：goods_spec 组合装：goods_suite
     * callback:回掉函数，确定按钮完成的操作
     * prefix：元素前缀，为了防止出现ID重复的问题，在元素ID前面加上preifix前缀。比如说单品列表的datagrid元素id为"goods_spec_select_datagrid"，prefix为
     * "prefix_name"，则生成的单品列表的datagrid元素的id为"prefix_name_goods_spec_select_datagrid"
     * flag:选择单品列表的样式，传入1则显示的单品列表分为上下两个datagrid，传入0为单独一个显示的datagrid。默认值为1
     */
    $.fn.richDialog = function (method, callback, prefix, flag) {
        if (typeof(method) == "string") {
            var func = $.fn.richDialog.methods[method];
        }
        if (func) {
            return func(this, callback, prefix, flag);
        }
    };
    $.fn.richDialog.methods = {
        goodsSuite: function (jq, cb, prefix, cbParams) {
            var model = '';
            if(prefix){
                if(prefix['model']!= undefined){
                    model = prefix.model;
                    prefix = prefix.pre;

                }else {
                    prefix = prefix ? prefix : "goods_suite";
                    model = '';
                }
            }else{
                prefix = prefix ? prefix : "goods_suite";
                model = '';
            }

            var url = "index.php/Goods/GoodsSuite/getDialogGoodsSuiteList?prefix=" + prefix+"&model="+model;
            $(jq).dialog({
                title: "组合装",
                width: 764,
                height: 450,
                modal: true,
                maximizable: true,
                resizable: true,
                closed: false,
                href: url,
                inline: true,
                iconCls: 'icon-save',
                buttons: [
                    {
                        text: '确定', handler: function () {
                        //第一个参数为组合装列表datagrid的ID,第三个参数为组合装列表Tabs下datagrid的ID
                        cb(prefix + "_goods_suite_list_datagrid", cbParams, prefix + "_tabs_detail_datagrid");
                    }
                    },
                    {
                        text: '取消', handler: function () {
                        $.messager.confirm('提示', '您确定要关闭吗？', function (r) {
                            if (r) {
                                $(jq).dialog('close');
                            }
                        });
                    }
                    },
                ]
            });
        },
        /*
         * jq：打开的dialog
         * cb：回调函数
         * cbParams：回调函数的参数
         * params：区分不同单品列表的参数
         * params["prefix"]：命名前缀 必需 String
         * params["type"]：是否包含选择结果的表格 Bool
         * params["warehouse_id"]：传入的仓库ID String
         * */
        goodsSpec: function (jq, cb, params, cbParams) {
            var prefix = params["prefix"];
            //params = JSON.stringify(params);
            params = $.param(params);
            var url = "index.php/Goods/GoodsSpec/selectGoodsSpec?" + params;
            $(jq).dialog({
                title: "单品列表",
                width: 800,
                height: 450,
                modal: true,
                minimizable: false,
                maximizable: true,
                resizable: true,
                closed: false,
                href: url,
                inline: true,
                iconCls: 'icon-save',
                buttons: [
                    {
                        text: '确定', handler: function () {
                        //第一个参数为单品选择列表主表的datagrid的ID，第二个参数为单品选择列表子表的datagrid的ID
                        cb(prefix + "_goods_spec_select_datagrid", prefix + "_sub_goods_spec_select_datagrid", cbParams);
                    }
                    },
                    {
                        text: '取消', handler: function () {
                        $.messager.confirm('提示', '您确定要关闭吗？', function (r) {
                            if (r) {
                                $(jq).dialog('close');
                            }
                        });
                    }
                    },
                    {
                        text: '删除', handler: function () {
                        var sub_dg = $("#" + prefix + "_sub_goods_spec_select_datagrid");
                        var row = sub_dg.datagrid("getSelected");
                        var index = sub_dg.datagrid("getRowIndex", row);
                        if (index != undefined) {
                            sub_dg.datagrid("deleteRow", index);
                        } else {
                            messager.alert("请选择操作的行！");
                        }
                    }
                    }
                ]
            });
        },
        customer: function (jq, cb, params, cbParams) {
            var prefix = params["prefix"];
            params = $.param(params);
            var url = "index.php/Customer/CustomerFile/selectCustomer?" + params;
            $(jq).dialog({
                title: "客户信息",
                width: 800,
                height: 450,
                modal: true,
                minimizable: false,
                maximizable: true,
                resizable: true,
                closed: false,
                href: url,
                inline: true,
                iconCls: 'icon-save',
                buttons: [
                    /*{
                        text: '确定', handler: function () {
                        //第一个参数为单品选择列表主表的datagrid的ID，第二个参数为单品选择列表子表的datagrid的ID
                        cb(prefix + "_customer_select_datagrid", prefix + "_sub_customer_select_datagrid", cbParams);
                    }
                    },*/
                    {
                        text: '取消', handler: function () {
                        $.messager.confirm('提示', '您确定要关闭吗？', function (r) {
                            if (r) {
                                $(jq).dialog('close');
                            }
                        });
                    }
                    },
                    /*{
                        text: '删除', handler: function () {
                        var sub_dg = $("#" + prefix + "_sub_customer_select_datagrid");
                        var row = sub_dg.datagrid("getSelected");
                        var index = sub_dg.datagrid("getRowIndex", row);
                        if (index != undefined) {
                            sub_dg.datagrid("deleteRow", index);
                        } else {
                            messager.alert("请选择操作的行！");
                        }
                    }
                    }*/
                ]
            });
        },
        /*
         * jq：无效参数，不用管它
         * data：需要显示的数据
         * type：显示数据所有的datagrid的fields
         * 调用示例：$.fn.richDialog("response", res.info, "trade");
         * */
        response: function (jq, data, type,eventFun) {
            var fields = [[]];
            var width = 764;
            var height = 450;
            var rownummbers = true;
            var button=false;
            if (type == "trade") {
                fields = [[
                    {field: "tid", title: "原始单号", width: 150},
                    {field: "buyer_nick", title: "客户网名", width: 150},
                    {field: "error_info", title: "错误信息", width: 300}
                ]];
            } else if (type == "stockout") {
                width = 1070;
                fields = [[
                    {field: "stock_id", hidden: true},
                    {field: "stock_no", title: "出库单号", width: 120},
                    {field: "msg", title: "错误原因", width: 450},
                    {field: 'solve_way', title: '处理方法', width: 450},
                ]];
            } else if (type == "platformgoods") {
                fields = [[
                    {field: "rec_id", hidden: true},
                    {field: "goods_id", title: "货品ID", width: 150},
                    {field: "spec_id", title: "规格ID", width: 150},
                    {field: "msg", title: "错误原因", width: 300}
                ]];
            } else if (type == "checktrade") {
                width = 780;
                fields = [[
                    {field: 'trade_no', title: '订单号', width: 120},
                    {field: 'result_info', title: '处理信息', width: 300},
                    {field: 'solve_way', title: '处理方法', width: 200},
                    {field: 'force_check', title: '是否可强制审核', width: 100}
                ]];
            } else if (type == "tradecheck") {
                width = 600;
                fields = [[
                    {field: 'trade_no', title: '订单号', width: 170},
                    {field: 'result_info', title: '处理信息', width: 380}
                ]];
            } else if (type == "checknumber") {
                width = 500;
                fields = [[
                    {field: 'trade_no', title: data.title, width: 150},
                    {field: 'result_info', title: '处理信息', width: 300}
                ]];
            } else if (type == "refundmanage") {
                width = 600;
                fields = [[
                    {field: 'refund_no', title: '退换单号', width: 170},
                    {field: 'result_info', title: '处理信息', width: 380}
                ]];
            } else if (type == "agreeRefund") {
                width = 600;
                fields = [[
                    {field: 'refund_no', title: '退换单号', width: 170},
                    {field: 'info', title: '错误信息', width: 380}
                ]];
            } else if (type == "importResponse") {
                rownummbers = false;
                fields = [[
                    {field: "id", title: "文件行号", width: "10%", align: "center"},
                    {field: "result", title: "结果", width: "20%"},
                    {field: "message", title: "失败原因", width: "72%"}
                ]];
            } else if (type == "importResponse_suite") {
                rownummbers = false;
                fields = [[
                    {field: "result", title: "结果", width: "20%"},
                    {field: "message", title: "失败原因", width: "72%"}
                ]];
            } else if (type == "GoodsBarcode") {
                width = 600;
                fields = [[
                    {field: 'barcode', title: '条形码', width: 170},
                    {field: 'info', title: '失败原因', width: 380}
                ]];
            } else if (type == "goods_spec") {
            	width = 600;
                fields = [[
                    {field: 'spec_no', title: '商家编码', width: 170},
                    {field: 'info', title: '失败原因', width: 380}
                ]];
            } else if (type == "stock_goods") {
                width = 700;
                fields = [[
                    {field: 'location', title: '删除失败位置', width: 100},
                    {field: 'spec_no', title: '商家编码', width: 170},
                    {field: 'info', title: '失败原因', width: 380}
                ]];
            }else if (type == "importApiGoods") {
                fields = [[
                    {field: 'goods_name', title: '货品名称', width: 170},
                    {field: 'spec_no', title: '商家编码', width: 170},
                    {field: 'info', title: '失败原因', width: 380}
                ]];
            }else if (type == "goods_goods") {
            	width = 600;
                fields = [[
                    {field: 'goods_no', title: '货品编码', width: 170},
                    {field: 'info', title: '失败原因', width: 380}
                ]];
            }else if (type == "sms_msg") {
            	width = 600;
                fields = [[
                    {field: 'mobile', title: '手机号', width: 170},
                    {field: 'message', title: '失败原因', width: 380}
                ]];
            }else if (type == "importTrade") {
                fields = [[
                    {field: 'tid', title: '原始订单号', width: 170},
                    {field: 'result', title: '结果', width: 170},
                    {field: 'message', title: '失败原因', width: 380}
                ]]
            }else if (type == "importSalesTrade") {
                fields = [[
                    {field: 'trade_no', title: '订单编号', width: 170},
                    {field: 'result', title: '结果', width: 170},
                    {field: 'message', title: '失败原因', width: 380}
                ]]
            }else if (type == "waybill") {
                width = 600;
                fields = [[
                    {field: 'id', title: '序列号',hidden:true},
                    {field: 'logistics_no', title: '物流单号', width: 170},
                    {field: 'msg', title: '原因', width: 380}
                ]]
            }else if (type == "stalls") {
                 width = 650;
                fields = [[
                    {field: "stalls_id", hidden: true},
                    {field: "stalls_no", title: "档口单号", width: 120},
                    {field: "msg", title: "错误原因", width: 450},
                ]];
            }else if (type == "box") {
                 width = 650;
                fields = [[
                    {field: "box_id", hidden: true},
                    {field: "box_no", title: "档口单号", width: 120},
                    {field: "msg", title: "错误原因", width: 450},
				]];
			}else if (type == "wms") {
                width = 600;
                fields = [[
                    {field: 'spec_no', title: '商家编码',width:160},
                    //{field: 'name', title: '仓库名', width: 100},
                    {field: 'msg', title: '错误原因', width: 380}
                ]]
            }else if(type=='direct_consign'){
            	width = 600;
                fields = [[
                    {field: 'trade_no', title: '订单号', width: 170},
                    {field: 'result_info', title: '处理信息', width: 380}
                ]];
                button= [
                          {
                              text: '继续保存', handler: function () {
                           	   continue_save_waybill(data);
                          }
                          },
                          {
                              text: '取消', handler: function () {
                           	   not_continue(data);
                              }
                          }
                      ];
            }else if(type=='passel_split'){
            	width = 450;
                fields = [[
                    {field: 'trade_no', title: '订单号', width: 170},
                    {field: 'result_info', title: '处理信息', width: 380}
                ]];
                button= [
                          {
                              text: '继续拆分', handler: function () {
                           	   continue_split(data);
                          }
                          },
                          {
                              text: '取消', handler: function () {
                                  $("#response_dialog").dialog('close');
                              }
                          }
                      ];
			}else if(type=='hot_code'){
            	width = 800;
                fields = [[
                    {field: 'trade_no', title: '订单号', width: 170},
                    {field: 'refund_status', title: '情况说明', width: 380},
					{field: 'ck',checkbox:true,title:'是否继续打印爆款单',width:170},
					{field:'info',title:'是否继续打印爆款单',width:170},
                ]];
                button= [
                          {
                              text: '确定', handler: function () {
                           	   checkPrintBox();
							   $("#response_dialog").dialog('close');
                          }
                          },
                          {
                              text: '取消', handler: function () {
                                  $("#response_dialog").dialog('close');
                              }
                          }
                      ];		  
					  
            }else if (type == "update_goods_name") {
                fields = [[
                    {field: "spec_no", title: "商家编码", width: "50%"},
                    {field: "status", title: "处理结果&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(*&nbsp;无需更新的不显示处理结果)", width: "40%"},
                ]];

            }else if (type == "uponLogistics") {
                fields = [[
                    {field: 'shop_name', title: '店铺名称', width: '35%'},
                    {field: 'info', title: '失败原因', width: '65%'}
                ]]
            }else if (type == "inventory_management_result") {
                width = 550;
                height = 400;
                fields = [[
                    {field: 'pd_no', title: '盘点单号', width: 145},
                    {field: 'info', title: '失败原因', width: 345}
                ]];
            }else if (type == "transfer_management_result") {
                width = 650;
                height = 400;
                fields = [[
                    {field: 'transfer_no', title: '调拨单号', width: 145},
                    {field: 'info', title: '失败原因', width: 445}
                ]];
            }else if (type == "purchase_management_result") {
                width = 550;
                height = 400;
                fields = [[
                    {field: 'purchase_no', title: '采购单号', width: 145},
                    {field: 'info', title: '失败原因', width: 345}
                ]];
            }else if (type == "stallsGoodsAccountResult") {
                width = 600;
                height = 400;
                fields = [[
                    {field: "spec_no", title: "商家编码", width: "30%"},
                    {field: "provider_name", title: "供货商名称", width: "20%"},
                    {field: "sales_date", title: "入库日期", width: "15%", align: "center"},
                    {field: "message", title: "失败原因", width: "33%"}
                ]];
            }else if(type == 'box_goods_detail_result'){
                width = 600;
                height = 400;
                fields = [[
                    {field: "box_no", title: "分拣框编号", width: "20%"},
                    {field: "trade_no", title: "订单编号", width: "30%"},
                    {field: "info", title: "失败原因", width: "45%"}
                ]];
            }else if(type == 'picklist'){
                width = 880;
                height = 300;
                fields = [[
                    {field: "unique_code", title: "唯一码", width: 140},
                    {field: "msg", title: "错误原因", width: 100},
                    {field: "remark", title: "修改详情", width: 440},
                    {field: 'solve_way', title: '处理方法', width: 140},
                ]];
            }else if(type == 'hot_print_result'){
                width = 920;
                height = 400;
                fields = [[
                    {field: 'ck',checkbox:true},
                    {field: "trade_no", title: "订单号", width: 140},
                    {field: "msg", title: "错误原因", width: 120},
                    {field: "remark", title: "修改详情", width: 440},
                    {field: "solve_way", title: "处理方法", width: 140},
                ]];
                button= [
                    {
                        text: '继续分拣', handler: function () {
                        continue_sort();
                        $("#response_dialog").dialog('close');
                    }
                    }
                ];
            }
            $("#response_dialog").dialog({
                title: "返回信息",
                width: width,
                height: height,
                modal: true,
                closed: false,
                inline: true,
                iconCls: 'icon-save',
                buttons:button,
                onClose:eventFun ==undefined?function(){}:function(){if(eventFun.close){eventFun.close()};}
            });
            if(type=='direct_consign'){
            	 $("#response_dialog_datagrid").datagrid({
                     rownumbers: rownummbers,
                     singleSelect: true,
                     data: data.waybill_error,
                     columns: fields
                 });
            }else if(type == 'hot_code'){
				$("#response_dialog_datagrid").datagrid({
                    rownumbers: rownummbers,
                    singleSelect: false,
                    data: data,
                    columns: fields
                });
			}else if(type == 'hot_print_result'){
                $("#response_dialog_datagrid").datagrid({
                    rownumbers: rownummbers,
                    singleSelect: false,
                    data: data,
                    columns: fields
                });
            }else{
            	$("#response_dialog_datagrid").datagrid({
                    rownumbers: rownummbers,
                    singleSelect: true,
                    data: data,
                    columns: fields
                });
            }
            
        },
        /*
         * jq：无视即可
         * cb：回调函数
         * cbParams：回调函数的参数
         * params：预留参数
         * 调用示例：$.fn.richDialog('customerFile')
         * */
        customerFile: function (jq, cb, cbParams, params) {
            var url = "index.php/Customer/CustomerFile/getCustomerListDialog";
            $("#trade_select_customer").dialog({
                title: "客户信息",
                width: 800,
                height: 450,
                modal: true,
                closed: false,
                inline: true,
                href: url,
                buttons: [
                    {
                        text: '确定', handler: function () {
                        //第一个参数为客户信息列表的datagrid的ID
                        cb("customer_file_dialog_datagrid", "trade_select_customer", cbParams);
                    }
                    },
                    {
                        text: '取消', handler: function () {
                        $.messager.confirm('ERP', '您确定要关闭吗？', function (r) {
                            if (r) {
                                $("#trade_select_customer").dialog('close');
                            }
                        });
                    }
                    }
                ]
            });
        },
        salesTrade: function (jq, cb, cbParams, params) {
            var url = "index.php/Trade/TradeManage/getTradeListDialog";
            $("#trade_select_customer").dialog({
                title: "选择退货货品",
                width: 940,
                height: 510,
                modal: true,
                closed: false,
                inline: true,
                href: url,
                buttons: [
                    {
                        text: '确定', handler: function () {
                        cb("sales_trade_dialog_datagrid", "tradeSelectdatagrid_order_list", "trade_select_customer", cbParams);
                    }
                    },
                    {
                        text: '取消', handler: function () {
                        $.messager.confirm('ERP', '您确定要关闭吗？', function (r) {
                            if (r) {
                                $("#trade_select_customer").dialog('close');
                            }
                        });
                    }
                    }
                ]
            });
        },
        warehousePosition: function (jq, cb, params, cbParams) {
            var url = "index.php/Setting/WarehousePosition/getWarehousePositioninfo?warehouse_id="+params.warehouse_id+'&prefix='+params.prefix;
            $(jq).dialog({
                title: "选择货位",
                width: 600,
                height: 450,
                modal: true,
                closed: false,
                inline: true,
                href: url,
                buttons: [
                    {
                        text: '确定', handler: function () {
                        cb(params.prefix+"_warehouseposition_datagrid", params.prefix+"datagrid_position_spec", params.prefix+"_warehouseposition_form",cbParams);
                    }
                    },
                    {
                        text: '取消', handler: function () {
                        $.messager.confirm('ERP', '您确定要关闭吗？', function (r) {
                            if (r) {
                                $(jq).dialog('close');
                            }
                        });
                    }
                    }
                ]
            });
        },
		provider: function (jq, cb, params, cbParams) {
            var url = "index.php/Setting/PurchaseProvider/getProviderinfo?prefix="+params.prefix;
            $(jq).dialog({
                title: "选择供应商",
                width: 900,
                height: 450,
                modal: true,
                closed: false,
                inline: true,
                href: url,
                buttons: [
                    {
                        text: '确定', handler: function () {
                        cb(params.prefix+"_purchaseprovider_datagrid", "StallsOrderManagement_stallsordermanagement_datagrid_edit",cbParams);
                    }
                    },
                    {
                        text: '取消', handler: function () {
                        $.messager.confirm('ERP', '您确定要关闭吗？', function (r) {
                            if (r) {
                                $("#stalls_select_provider").dialog('close');
                            }
                        });
                    }
                    }
                ]
            });
        },
        /*
         * jq：打开的dialog
         * cb：回调函数
         * params：区分不同单品列表的参数
         * params["prefix"]：命名前缀 必需 String
         * params["mode"]：区分引入类型 String
         * cbParams：回调函数的参数
         */
        stockinOrder: function (jq, cb, params, cbParams) {
            var prefix = params["pre"];
            params = $.param(params);
            var url = "index.php/Stock/StockInOrder/getDialogStockInOrderList?" + params;
            $(jq).dialog({
                title: "入库单",
                width: 764,
                height: 450,
                modal: true,
                maximizable: true,
                resizable: true,
                closed: false,
                href: url,
                inline: true,
                iconCls: 'icon-save',
                buttons: [
                    {
                        text: '确定', handler: function () {
                        //第一个参数为入库单列表Tabs下datagrid的ID
                        cb(prefix + "_tabs_detail_datagrid", cbParams);
                    }
                    },
                    {
                        text: '取消', handler: function () {
                        $.messager.confirm('提示', '您确定要关闭吗？', function (r) {
                            if (r) {
                                $(jq).dialog('close');
                            }
                        });
                    }
                    },
                ]
            });
        }
    };


    $.fn.changStyleTreeCombo = function (treeId) {
        $('#' + treeId).combotree({
            onLoadSuccess: function (node, data) {
                var childrens = $(this).tree('getChildren');
                for (var key in childrens) {
                    var child = childrens[key];
                    if ($(this).tree('isLeaf', child.target) && child.attributes == 0) {
                        var span_dom_arr = $(child.target).children();//转化为jquery类型 并获取最近子节点
                        var span_dom_length = span_dom_arr.length;
                        for (var i = 0; i < span_dom_length; i++) {
                            var span_dom = span_dom_arr.get(i);
                            var span_obj = $(span_dom);
                            var span_class_str = span_obj.attr('class');
                            span_class_str = $.trim(span_class_str);//去除字符串两头的  空白  字符  不是   空字符
                            var span_class_split = span_class_str.split(/^[\s]{0,0}|[\s]{0,0}$|[\s]+/);//{0,0}去除空字符如""   [\s]+去除空白字符     应注意顺序

                            if ($.inArray('tree-join', span_class_split) != -1)  //处于中间位置的分组节点
                            {
                                span_obj.removeClass();
                                span_obj.addClass('tree-hit tree-collapsed');
                            }
                            else if ($.inArray('tree-joinbottom', span_class_split) != -1) //处于末尾的分组节点
                            {
                                span_obj.removeClass();
                                span_obj.addClass('tree-hit tree-collapsed');
                            }
                            else if ($.inArray('tree-file', span_class_split) != -1) //把文件icon改为文件夹icon
                            {
                                span_obj.removeClass("tree-file");
                                span_obj.addClass('tree-folder');
                            }
                        }
                    }
                }
            },
        });
    };

    /**
     * GRB值转换成16进制
     */
    $.fn.getHexBackgroundColor = function () {
        var rgb = $(this).css('background-color');
        if (rgb >= 0) {
            return rgb;
        }//如果是一个hex值则直接返回
        else {
            rgb = rgb.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);
            function hex(x) {
                return ("0" + parseInt(x).toString(16)).slice(-2);
            }

            rgb = "#" + hex(rgb[1]) + hex(rgb[2]) + hex(rgb[3]);
        }
        return rgb;
    };
    /**
     * 聚焦移动选中内容
     */
    $.fn.selectRange = function(start, end) {  
	    return this.each(function() {  
	        if (this.setSelectionRange) {  
	            this.focus();  
	            this.setSelectionRange(start, end);  
	        } else if (this.createTextRange) {  
	            var range = this.createTextRange();  
	            range.collapse(true);  
	            range.moveEnd('character', end);  
	            range.moveStart('character', start);  
	            range.select();  
	        }  
	    });  
	};
    /**
     * combobox停用默认显示
     */
    $.fn.showComboboxDisabled = function () {
        for(var j=0; j<$(this).length; ++j){
            var valueField = $(this[j]).combobox("options").valueField;
            var val = $(this[j]).combobox("getValue");
            var allData = $(this[j]).combobox("getData");
            var result = true;      //为true说明当前值在下拉框数据中不存在  
            for(var i = 0; i < allData.length; ++i){
                if(val == allData[i][valueField]){
                    result = false;
                    break;
                }
            }
            if(result){
                $(this[j]).combobox('setText','已停用');
            }
        }
    };
    /*
     $.fn.lazyLoadJs = function(file){
     var element = document.createElement("script");
     element.src = file;
     document.body.appendChild(element);
     }
     */
})(jQuery);

$.extend({
    lazyLoadJs: function (file) {
        var element = document.createElement("script");
        element.src = file;
        document.body.appendChild(element);
    }
});
//jQuery 字符串拓展
var __entityMap ={};
__entityMap['encode']= { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': '&quot;', "'": '&#039;', "/": '&#x2F;' };
__entityMap['decode']= { "/&amp;/g" : "&", "/&lt;/g" : "<", "/&gt;/g" : ">", "/&quot;/g" : '"', "/&#039;/g" : "'", "/&#x2F;/g" : "/" };
$.extend(String.prototype,{
    html_encode:function(){
        return String(this).replace(/[&<>"'\/]/g, function (s) { return __entityMap['encode'][s]; });
    },
    html_decode:function(){
        var s = "";
        if (this.length == 0) return s;
        s=String(this);
        $.each(__entityMap['decode'],function(key,val){ s=s.replace(eval(key), val); });
        /* s=String(this); s = s.replace(/&amp;/g,"&"); s = s.replace(/&lt;/g,"<"); s = s.replace(/&gt;/g,">"); s = s.replace(/&#39;/g,"'"); s = s.replace(/&quot;/g,'"'); s = s.replace(/&#x2F;/g,"/"); */
        return s;
    }
});