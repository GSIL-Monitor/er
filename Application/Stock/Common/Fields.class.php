<?php
namespace Stock\Common;
/**
 * Created by PhpStorm.
 * User: gaosong
 * Date: 15/9/22
 * Time: 下午12:52
 */
class Fields
{
    static public function getStockFields($field_id = '')
    {
        $field_id = strtolower($field_id);
        if (!empty($field_id)) {
            switch ($field_id) {
                case 'stockoutorder':
                    $fields = array(
                        '货品名称' => array('field' => 'goods_name', 'width' => '100'),
                        '规格名称' => array('field' => 'spec_name', 'width' => '100'),
                        '规格码' => array('field' => 'spec_code', 'width' => '100'),
                        '商家编码' => array('field' => 'spec_no', 'width' => '100'),
                        '条形码' => array('field' => 'barcode', 'width' => '100'),
//                        '品牌' => array('field' => 'brand_id', 'width' => '100' ,"formatter" => 'brandFormatter'),
                        '总货款' => array('field' => 'total_amount', 'width' => '100', 'editor' => '{type:"numberbox",options:{precision:2,readonly:true}}'),
                        '单价' => array('field' => 'market_price', 'width' => '100', 'formatter' => 'function(value,row,index){ var value = parseFloat(value); return value.toFixed(2);}', 'editor' => '{type:"numberbox",options:{precision:2,onChange:setPrice}}'),
//            '库存数量' => array('field' => 'stock_num', 'width' => '100',),
                        '出库数量' => array('field' => 'num', 'width' => '100', 'editor' => '{type:"numberbox",options:{precision:0,onChange:setNum}}'),
                        '单位' => array('field' => 'base_unit_id', 'width' => '100'),
                        '备注' => array('field' => 'remark', 'width' => '100', 'editor' => '{type:"textbox"}'),
                        'id' => array('field' => 'id', 'hidden' => true)
                    );
                    return $fields;
                case 'stockinorder':
                    $fields = array(
                        '预期数量' => array('field' => 'expect_num', 'width' => '100'),
                        '商家编码' => array('field' => 'spec_no', 'width' => '100'),
                        '货品名称' => array('field' => 'goods_name', 'width' => '100'),
                        '规格名称' => array('field' => 'spec_name', 'width' => '100'),
                        '规格码' => array('field' => 'spec_code', 'width' => '100'),
                        '条形码' => array('field' => 'barcode', 'width' => '100'),
                        '品牌' => array('field' => 'brand_id', 'width' => '100'),
                        '入库数量' => array('field' => 'num', 'width' => '100', 'editor' => '{type:"numberbox",options:{precision:2,onChange:setNum,value:0}}', 'formatter' => 'function(value,row,index){ var value = parseFloat(value); return value.toFixed(2);}'),
                        '售价' => array('field' => 'retail_price', 'width' => '100', 'formatter' => 'function(value,row,index){ var value = parseFloat(value); return value.toFixed(2);}'),
                        '原价' => array('field' => 'src_price', 'width' => '100', 'formatter' => 'function(value,row,index){ var value = parseFloat(value); return value.toFixed(2);}'),
                        '入库价' => array('field' => 'cost_price', 'width' => '100', 'editor' => '{type:"numberbox",options:{precision:2,onChange:setCostPrice,value:0}}', 'formatter' => 'function(value,row,index){ var value = parseFloat(value); return value.toFixed(2);}'),
                        '总成本' => array('field' => 'total_cost', 'width' => '100', 'editor' => '{type:"numberbox",options:{precision:2,onChange:setTotalCost,value:0}}', 'formatter' => 'function(value,row,index){ var num = parseFloat(row["num"]);var cost_price = parseFloat(row["cost_price"]); var total_cost = num*cost_price; return row["total_cost"] = total_cost.toFixed(2); }'),
                        '折扣' => array('field' => 'rebate', 'width' => '100', 'formatter' => 'function(value,row,index){ var value = parseFloat(value); return value.toFixed(2);}'),
                        '税率' => array('field' => 'tax_rate', 'width' => '100', 'editor' => '{type:"numberbox",options:{precision:2,onChange:setTaxRate,value:0}}', 'formatter' => 'function(value,row,index){ var value = parseFloat(value); return value.toFixed(2);}'),
                        '税后价' => array('field' => 'tax_price', 'width' => '100', 'formatter' => 'function(value,row,index){ var cost_price = parseFloat(row["cost_price"]);var tax_rate = parseFloat(row["tax_rate"]); var tax_price = cost_price*(tax_rate+1); return row["tax_price"]=tax_price.toFixed(2);}', 'editor' => '{type:"numberbox",options:{value:0.00,precision:2,readonly:true}}'),
                        '税后金额' => array('field' => 'tax_amount', 'width' => '100', 'formatter' => 'function(value,row,index){var cost_price = parseFloat(row["cost_price"]);var tax_rate = parseFloat(row["tax_rate"]); var tax_price = cost_price*(tax_rate+1);var num = parseFloat(row["num"]);var tax_amount = num*tax_price; return row["tax_amount"]=tax_amount.toFixed(2);}', 'editor' => '{type:"numberbox",options:{value:0.00,onChange:setTaxAmount,precision:2,readonly:true}}'),
                        '辅助数量' => array('field' => 'num2', 'width' => '100', 'hidden' => true, 'editor' => '{type:"numberbox",options:{precision:2}}'),
                        '辅助单价' => array('field' => 'cost_price2', 'hidden' => true, 'width' => '100'),
                        '单位' => array('field' => 'base_unit_id', 'width' => '100'),
                        '辅助单位' => array('field' => 'unit_id', 'width' => '100', 'hidden' => true),
                        '换算系数' => array('field' => 'unit_ratio', 'width' => '100', 'editor' => '{type:"numberbox",options:{precision:2}}', 'hidden' => true),
                        '单位邮费分摊' => array('field' => 'share_post_cost', 'width' => '100', 'hidden' => true),
                        '邮费总分摊' => array('field' => 'share_post_total', 'width' => '100', 'hidden' => true),
                        '最低价' => array('field' => 'lowest_price', 'width' => '100', 'hidden' => false),
                        '批发价' => array('field' => 'wholesale_price', 'width' => '100', 'hidden' => false),
                        '市场价' => array('field' => 'market_price', 'width' => '100', 'hidden' => false),
                        '会员价' => array('field' => 'member_price', 'width' => '100', 'hidden' => false),
                        'id' => array('field' => 'id', 'hidden' => true)
                    );
                    return $fields;
                case 'stockoutmanagement':
                    $fields = array(
                        '出库单号' => array('field' => 'stockout_no', 'width' => '100', 'align' => 'center'),
                        '状态' => array('field' => 'status', 'width' => '100', 'align' => 'center', 'formatter' => 'formatter.stockout_status',),
                        '类别' => array('field' => 'src_order_type', 'width' => '150', 'align' => 'center', 'formatter' => 'formatter.stockout_type',),
                        '源单号' => array('field' => 'src_order_no', 'width' => '100', 'align' => 'center'),
                        '经办人' => array('field' => 'operator_id', 'width' => '100', 'align' => 'center'),
                        '物流公司' => array('field' => 'logistics_id', 'width' => '100', 'align' => 'center'),
                        '物流单号' => array('field' => 'logistics_no', 'width' => '100', 'align' => 'center'),
//                        '出库仓库' => array('field' => 'warehouse_id', 'width' => '100', 'align' => 'center'),
                        '邮资' => array('field' => 'post_cost', 'width' => '100', 'align' => 'center'),
                        '货品数量' => array('field' => 'goods_count', 'width' => '100', 'align' => 'center'),
                        '货品种类数' => array('field' => 'goods_type_count', 'width' => '100', 'align' => 'center'),
                        '备注' => array('field' => 'remark', 'width' => '100', 'align' => 'center'),
                        '制单时间' => array('field' => 'created', 'width' => '100', 'align' => 'center'),
                        '发货时间' => array('field' => 'consign_time', 'width' => '100', 'align' => 'center'),
                        'id' => array('field' => 'id', 'hidden' => true,),
                    );
                    return $fields;

                case 'stocksync':
                    $fields = array(
                        '店铺'  => array('field'=>'shop_id','width'=>100,'sortable'=>true),
                        '平台货品商家编码'  => array('field'=>'outer_id','width'=>100,'sortable'=>true),
                        '平台货品ID'  => array('field'=>'goods_id','width'=>100,'sortable'=>true),
                        '平台规格ID'  => array('field'=>'spec_id','width'=>100,'sortable'=>true),
                        '平台货品名称'  => array('field'=>'api_goods_name','width'=>100,'sortable'=>true),
                        '平台规格商家编码'  => array('field'=>'api_spec_no','width'=>100,'sortable'=>true),
                        '平台规格名称'  => array('field'=>'api_spec_name','width'=>100,'sortable'=>true),
                        '货品商家编码'  => array('field'=>'spec_no','width'=>100,'sortable'=>true),
                        '货品类型'  => array('field'=>'goods_type','width'=>100,'sortable'=>true),
                        '同步数量'  => array('field'=>'syn_stock','width'=>100,'sortable'=>true),
                        '同步规则编号'  => array('field'=>'stock_syn_rule_no','width'=>100,'sortable'=>true),
                        '同步仓库'  => array('field'=>'stock_syn_warehouses','width'=>100,'sortable'=>true,'formatter'=>'warehouseFormatter'),
                        '同步数量计算方式'  => array('field'=>'stock_syn_mask','width'=>100,'sortable'=>true),
                        '同步百分比'  => array('field'=>'stock_syn_percent','width'=>100,'sortable'=>true),
                        '同步附加量'  => array('field'=>'stock_syn_plus','width'=>100,'sortable'=>true),
                        '最小同步量'  => array('field'=>'stock_syn_min','width'=>100,'sortable'=>true),
                        '自动上架'  => array('field'=>'is_auto_listing','width'=>100,'sortable'=>true),
                        '自动下架'  => array('field'=>'is_auto_delisting','width'=>100,'sortable'=>true),
                        '是否同步成功'  => array('field'=>'is_syn_sucess','width'=>100,'sortable'=>true),
                        '同步结果'  => array('field'=>'syn_result','width'=>100,'sortable'=>true),
                        '同步方式'  => array('field'=>'syn_type','width'=>100,'sortable'=>true),
                        '同步时间'  => array('field'=>'created','width'=>100,'sortable'=>true),
                    );
                    return $fields;
                case 'operation_log':
                    $fields = array(
                        'id' => array('field' => 'id', 'hidden' => true),
                        '操作员' => array('field' => 'operator_id', 'width' => '100',),
                        '操作描述' => array('field' => 'message', 'width' => '900'),
                        '操作时间' => array('field' => 'created', 'width' => '150'),
                    );
                    return $fields;
                case 'stockoutdetail':
                    $fields = array(
                        'id' => array('field' => 'id', 'hidden' => true),
                        '商家编码' => array('field' => 'spec_no', 'width' => '100'),
                        '货品编号' => array('field' => 'goods_no', 'width' => '100'),
                        '货品名称' => array('field' => 'goods_name', 'width' => '150'),
                        '规格码' => array('field' => 'spec_code', 'width' => '100'),
                        '规格名称' => array('field' => 'spec_name', 'width' => '100'),
//                        '品牌' => array('field' => 'brand_id', 'width' => '100', "formatter" => 'brandFormatter'),
                        '条形码' => array('field' => 'barcode', 'width' => '100'),
                        '单价' => array('field' => 'cost_price', 'width' => '100'),
                        '数量' => array('field' => 'num', 'width' => '100'),
                        '估重' => array('field' => 'weight', 'width' => '100'),
                        '备注' => array('field' => 'remark', 'width' => '200'),
                    );
                    return $fields;
                case 'stockmanagement':
                    $fields = array(
                        'id'=>array('field'=>'id','hidden'=>true,),
                        '商家编码'=>array('field'=>'spec_no','width'=>'100','align'=>'center'),
                        '货品编号'=>array('field'=>'goods_no','width'=>'100','align'=>'center'),
                        '货品名称'=>array('field'=>'goods_name','width'=>'150','align'=>'center'),
                        '货品简介'=>array('field'=>'short_name','width'=>'100','align'=>'center'),
                        '规格码'=>array('field'=>'spec_code','width'=>'100','align'=>'center'),
                        '规格名称'=>array('field'=>'spec_name','width'=>'100','align'=>'center'),
                        '条形码'=>array('field'=>'barcode','width'=>'100','align'=>'center'),
//                        '品牌'=>array('field'=>'brand_id','width'=>'100','align'=>'center','formatter'=>'brandFormatter'),
//                        '分类'=>array('field'=>'class_id','width'=>'100','align'=>'center','formatter'=>'classFormatter'),
                        '库存'=>array('field'=>'stock_num','width'=>'100','align'=>'center'),
                        '默认货位'=>array('field'=>'position_no','width'=>'100','align'=>'center'),
                        '成本价'=>array('field'=>'cost_price','width'=>'100','align'=>'center'),
                        '总成本'=>array('field'=>'all_cost_price','width'=>'100','align'=>'center'),
                        '可发库存'=>array('field'=>'avaliable_num','width'=>'100','align'=>'center'),
                        '锁定量'=>array('field'=>'lock_num','width'=>'100','align'=>'center'),
                        '警戒库存'=>array('field'=>'safe_stock','width'=>'100','align'=>'center'),
                        '未付款量'=>array('field'=>'unpay_num','width'=>'100','align'=>'center'),
                        '预订单量'=>array('field'=>'subscribe_num','width'=>'100','align'=>'center'),
                        '待审核量'=>array('field'=>'order_num','width'=>'100','align'=>'center'),
                        '待发货量'=>array('field'=>'sending_num','width'=>'100','align'=>'center'),
                        '待采购量'=>array('field'=>'to_purchase_num','width'=>'100','align'=>'center'),
                        '采购在途'=>array('field'=>'purchase_num','width'=>'100','align'=>'center'),
                        '采购到货量'=>array('field'=>'purchase_arrive_num','width'=>'100','align'=>'center'),
                        '待调拨量'=>array('field'=>'to_transfer_num','width'=>'100','align'=>'center'),
                        '调拨在途'=>array('field'=>'transfer_num','width'=>'100','align'=>'center'),
                        '采购退货'=>array('field'=>'return_num','width'=>'100','align'=>'center'),
                        '零售价'=>array('field'=>'retail_price','width'=>'100','align'=>'center'),
                        '市场价'=>array('field'=>'market_price','width'=>'100','align'=>'center'),
                        '备注'=>array('field'=>'remark','width'=>'100','align'=>'center'),
                        '仓储编码'=>array('field'=>'spec_wh_no','width'=>'100','align'=>'center'),
                        '仓储库存'=>array('field'=>'wms_sync_stock','width'=>'100','align'=>'center'),
                        '库存差异'=>array('field'=>'wms_stock_diff','width'=>'100','align'=>'center'),
                        '同步时间'=>array('field'=>'wms_sync_time','width'=>'100','align'=>'center'),
                    );
                    return $fields;
                case 'salestradedetail':
                    $fields=array(
                        'id'=>array('field'=>'id','hidden'=>true),
                        '订单编号'=>array('field'=>'trade_no','width'=>'100'),
                        '订单状态'=>array('field'=>'trade_status','width'=>'100','formatter'=>'statusFormatter',),
                        '交易时间'=>array('field'=>'trade_time','width'=>'150'),
                        '商家编码'=>array('field'=>'spec_no','width'=>'100'),
                        '货品编号'=>array('field'=>'goods_no','width'=>'100'),
                        '货品名称'=>array('field'=>'goods_name','width'=>'100'),
                        '规格码'=>array('field'=>'spec_code','width'=>'100'),
                        '规格名称'=>array('field'=>'spec_name','width'=>'100'),
                        '数量'=>array('field'=>'actual_num','width'=>'100'),
                    );
                    return $fields;
                case 'stockinmanagement':
                    $fields = array(
                        '入库单号' => array('field' => 'stockin_no', 'width' => '100', 'align' => 'center'),
                        '状态' => array('field' => 'status', 'width' => '100', 'align' => 'center','formatter' => 'formatter.stockin_status',),
                        '类别' => array('field' => 'src_order_type', 'width' => '150', 'align' => 'center','formatter' => 'formatter.stockin_type',),
                        '源单号' => array('field' => 'src_order_no', 'width' => '100', 'align' => 'center'),
                        '经办人' => array('field' => 'operator_id', 'width' => '100', 'align' => 'center',),
                        '物流公司' => array('field' => 'logistics_id', 'width' => '100', 'align' => 'center',),
                        '物流单号' => array('field' => 'logistics_no', 'width' => '100', 'align' => 'center'),
                        '货款总额' => array('field' => 'goods_amount', 'width' => '100', 'align' => 'center'),
                        '总成本' => array('field' => 'tax_amount', 'width' => '100', 'align' => 'center'),
                        '优惠' => array('field' => 'discount', 'width' => '100', 'align' => 'center'),
                        '邮资' => array('field' => 'post_fee', 'width' => '100', 'align' => 'center'),
                        '其他金额' => array('field' => 'other_fee', 'width' => '100', 'align' => 'center'),
                        '调整后总金额' => array('field' => 'right_fee', 'width' => '100', 'align' => 'center'),
                        '货品数量' => array('field' => 'goods_count', 'width' => '100', 'align' => 'center'),
                        '货品种类数' => array('field' => 'goods_type_count', 'width' => '100', 'align' => 'center'),
                        '调整后总数量' => array('field' => 'right_num', 'width' => '100', 'align' => 'center'),
                        '备注' => array('field' => 'remark', 'width' => '100', 'align' => 'center'),
                        '制单时间' => array('field' => 'created', 'width' => '100', 'align' => 'center'),
                        '修改时间' => array('field' => 'modified', 'width' => '100', 'align' => 'center'),
                        '审核时间' => array('field' => 'check_time', 'width' => '100', 'align' => 'center'),
                        'id' => array('field' => 'id', 'hidden' => true,),
                    );
                    return $fields;
                case 'stockindetail':
                    $fields=array(
                        'id'=>array('field'=>'id','hidden'=>true),
                        '商家编码'=>array('field'=>'spec_no','width'=>'100'),
                        '货品编号'=>array('field'=>'goods_no','width'=>'100'),
                        '货品名称'=>array('field'=>'goods_name','width'=>'150'),
                        '规格码'=>array('field'=>'spec_code','width'=>'100'),
                        '规格名称'=>array('field'=>'spec_name','width'=>'100'),
                        '品牌'=>array('field'=>'brand_id','width'=>'100',),
                        '条形码'=>array('field'=>'barcode','width'=>'100'),
                        '单价'=>array('field'=>'cost_price','width'=>'100'),
                        '数量'=>array('field'=>'num','width'=>'100'),
//                        '调整后单价'=>array('field'=>'right_price','width'=>'100'),
//                        '调整后数量'=>array('field'=>'right_num','width'=>'100'),
                        '税率'=>array('field'=>'tax','width'=>'100'),
                        '税后价'=>array('field'=>'tax_price','width'=>'100'),
                        '单位'=>array('field'=>'base_unit_id','width'=>'100'),
                        '调整后总金额'=>array('field'=>'right_cost','width'=>'100'),
                    );
                    return $fields;
                default:
                    $fields = array();
                    return $fields;
            }
        }
    }

    static public function getTabDatagrid($DatagridId,$fields){
        $datagrid = array(
            'id'         =>$DatagridId,
            'options'    => array(
                'title'      => '',
                'url'        => null,
                'fitColumns' => false,
                'pagination' => false,
                'rownumbers' => false,

            ),
            'fields'=>$fields,
            'class'=>'easyui-datagrid',
            'style'=>'padding:5px;'
        );
        return $datagrid;
    }

}