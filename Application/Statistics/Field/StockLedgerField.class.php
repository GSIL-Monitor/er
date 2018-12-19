<?php

namespace Statistics\Field;

use Common\Common\Field;

class StockLedgerField extends Field
{
    /**
     * @param $key string
     * @return array
     */
    protected function get($key)
    {
        $fields = array(
            "stockledger" => array(
                'checkbox'=>array('field'=>'ck','hidden'=>false,'checkbox'=>true,'frozen'=>true),
                '商家编码' => array('field' => 'spec_no', 'width' => 150),
                '货品编码' => array('field' => 'goods_no', 'width' => 150),
                '货品名称' => array('field' => 'goods_name', 'width' => 150),
                '货品简称' => array('field' => 'short_name', 'width' => 150),
                '规格码' => array('field' => 'spec_code', 'width' => 150),
                '规格名称' => array('field' => 'spec_name', 'width' => 150),
                '分类' => array('field' => 'class_id', 'width' => 100),
                '品牌' => array('field' => 'brand_id', 'width' => 100),
                '仓库名称' => array('field' => 'warehouse_name', 'width' => 150),
                /*'期初库存' => array('field' => 'begin_stock', 'width' => 150),
                '期初成本价' => array('field' => 'begin_cost', 'width' => 150),
                '期初总额' => array('field' => 'begin_costtotal', 'width' => 150),
                '期末库存' => array('field' => 'end_stock', 'width' => 150),
                '期末成本价' => array('field' => 'end_cost', 'width' => 150),
                '期末总额' => array('field' => 'end_costtotal', 'width' => 150),*/
                '出库总数' => array('field' => 'stockout_count', 'width' => 100),
                '出库总成本' => array('field' => 'stockout_cost', 'width' => 100),
                '出库总金额' => array('field' => 'stockout_money', 'width' => 100),
                '入库总数' => array('field' => 'stockin_count', 'width' => 100),
                '入库总金额' => array('field' => 'stockin_money', 'width' => 100),
                '销售出库数量' => array('field' => 'sell_count', 'width' => 100),
                '销售出库成本' => array('field' => 'sell_cost', 'width' => 100),
                '销售出库总额' => array('field' => 'sell_money', 'width' => 100),
                '调拨出库数量' => array('field' => 'tra_out_count', 'width' => 100),
                '调拨出库成本' => array('field' => 'tra_out_cost', 'width' => 100),
                '调拨出库总额' => array('field' => 'tra_out_money', 'width' => 100),
                '盘点出库数量' => array('field' => 'pd_out_count', 'width' => 100),
                '盘点出库成本' => array('field' => 'pd_out_cost', 'width' => 100),
                '盘点出库总额' => array('field' => 'pd_out_money', 'width' => 100),
                '其他出库数量' => array('field' => 'man_out_count', 'width' => 100),
                '其他出库成本' => array('field' => 'man_out_cost', 'width' => 100),
                '其他出库总额' => array('field' => 'man_out_money', 'width' => 100),
                '初始化出库数量' => array('field' => 'int_out_count', 'width' => 100),
                '初始化出库成本' => array('field' => 'int_out_cost', 'width' => 100),
                '初始化出库总额' => array('field' => 'int_out_money', 'width' => 100),
                '采购入库数量' => array('field' => 'pur_in_count', 'width' => 100),
                '采购入库总额' => array('field' => 'pur_in_money', 'width' => 100),
                '调拨入库数量' => array('field' => 'tra_in_count', 'width' => 100),
                '调拨入库总额' => array('field' => 'tra_in_money', 'width' => 100),
                '销售退货数量' => array('field' => 'sellback_count', 'width' => 100),
                '销售退货总额' => array('field' => 'sellback_money', 'width' => 100),
                '盘点入库数量' => array('field' => 'pd_in_count', 'width' => 100),
                '盘点入库总额' => array('field' => 'pd_in_money', 'width' => 100),
                '其他入库数量' => array('field' => 'man_in_count', 'width' => 100),
                '其他入库总额' => array('field' => 'man_in_money', 'width' => 100),
                '初始化入库数量' => array('field' => 'int_in_count', 'width' => 100),
                '初始化入库总额' => array('field' => 'int_in_money', 'width' => 100)
            ),
            'stockledgerdetial'=>array(
                '出入库单号' => array('field' => 'stock_no', 'width' => 120),
                '来源单号' => array('field' => 'src_order_no', 'width' => 120),
                '操作类型' => array('field' => 'src_order_type', 'width' => 120,'formatter' => 'formatter.stock_ledger_type'),
                '本次操作库存' => array('field' => 'num', 'width' => 120),
                '本次操作价格' => array('field' => 'price', 'width' => 120),
                '前库存' => array('field' => 'stock_num_old', 'width' => 120),
                '前成本价' => array('field' => 'cost_price_old', 'width' => 120),
                '后库存' => array('field' => 'stock_num_new', 'width' => 120),
                '后成本价' => array('field' => 'cost_price_new', 'width' => 120),
                '总金额' => array('field' => 'total_cost', 'width' => 120),
                '操作人' => array('field' => 'operator_id', 'width' => 120),
                '备注' => array('field' => 'remark', 'width' => 120),
                '审核时间' => array('field' => 'modified', 'width' => 120),
            )
        );
        return $fields[$key];
    }
}
