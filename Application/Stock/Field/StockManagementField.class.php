<?php
/**
 * Created by PhpStorm.
 * User: gaosong
 * Date: 11/20/15
 * Time: 10:56
 */
namespace Stock\Field;

use Common\Common\Field;

class StockManagementField extends Field
{
    protected function get($key)
    {
        $fields = array(
            'stockmanagement'=>array(
                'id'=>array('field'=>'id','hidden'=>true,),
                'checkbox'=>array('field'=>'ck','hidden'=>false,'checkbox'=>true,'frozen'=>true),
                '商家编码'=>array('field'=>'spec_no','width'=>'100','align'=>'center','sortable'=>true),
                '货品编号'=>array('field'=>'goods_no','width'=>'100','align'=>'center','sortable'=>true),
                '货品名称'=>array('field'=>'goods_name','width'=>'100','align'=>'center','sortable'=>true),
                '货品简称'=>array('field'=>'short_name','width'=>'100','align'=>'center','sortable'=>true),
                '规格码'=>array('field'=>'spec_code','width'=>'100','align'=>'center','sortable'=>true),
                '规格名称'=>array('field'=>'spec_name','width'=>'100','align'=>'center','sortable'=>true),
                '条形码'=>array('field'=>'barcode','width'=>'100','align'=>'center','sortable'=>true),
                '品牌'=>array('field'=>'brand_id','width'=>'100','align'=>'center','sortable'=>true),
                '分类'=>array('field'=>'class_id','width'=>'100','align'=>'center','sortable'=>true),
                '库存'=>array('field'=>'stock_num','width'=>'100','align'=>'center','sortable'=>true),
                '警戒库存'=>array('field'=>'safe_stock','width'=>'100','align'=>'center','sortable'=>true),
//                '警戒天数'=>array('field'=>'alarm_days','width'=>'100','align'=>'center'),
				'默认货位'=>array('field'=>'position_no','width'=>'100','align'=>'center'),
                '成本价'=>array('field'=>'cost_price','width'=>'100','align'=>'center','sortable'=>true),
                '总成本'=>array('field'=>'all_cost_price','width'=>'100','align'=>'center','sortable'=>true),
                '可发库存'=>array('field'=>'avaliable_num','width'=>'100','align'=>'center','sortable'=>true),
//				'锁定量'=>array('field'=>'lock_num','width'=>'100','align'=>'center'),
//                 '警戒库存'=>array('field'=>'safe_stock','width'=>'100','align'=>'center'),
//                 '未付款量'=>array('field'=>'unpay_num','width'=>'100','align'=>'center'),
//                 '预订单量'=>array('field'=>'subscribe_num','width'=>'100','align'=>'center'),
                '待审核量'=>array('field'=>'order_num','width'=>'100','align'=>'center','sortable'=>true),
                '待发货量'=>array('field'=>'sending_num','width'=>'100','align'=>'center','sortable'=>true),
                '未付款量'=>array('field'=>'unpay_num','width'=>'100','align'=>'center','sortable'=>true),
				'采购在途量'=>array('field'=>'purchase_num','width'=>'100','align'=>'center','sortable'=>true),
				'采购到货量'=>array('field'=>'purchase_arrive_num','width'=>'100','align'=>'center','sortable'=>true),
                '预订单量'=>array('field'=>'subscribe_num','width'=>'100','align'=>'center','sortable'=>true),
				'7天销量'=>array('field'=>'seven_outnum','width'=>'100','align'=>'center','sortable'=>true),
				'14天销量'=>array('field'=>'fourteen_outnum','width'=>'100','align'=>'center','sortable'=>true),
				'近期销量'=>array('field'=>'recent_outnum','width'=>'100','align'=>'center','sortable'=>true),
//                 '待采购量'=>array('field'=>'to_purchase_num','width'=>'100','align'=>'center'),
//                 '采购在途'=>array('field'=>'purchase_num','width'=>'100','align'=>'center'),
//                 '采购到货量'=>array('field'=>'purchase_arrive_num','width'=>'100','align'=>'center'),
//				'待调拨量'=>array('field'=>'to_transfer_num','width'=>'100','align'=>'center'),
//                 '调拨在途'=>array('field'=>'transfer_num','width'=>'100','align'=>'center'),
//                 '采购退货'=>array('field'=>'return_num','width'=>'100','align'=>'center'),
                '零售价'=>array('field'=>'retail_price','width'=>'100','align'=>'center','sortable'=>true),
                '市场价'=>array('field'=>'market_price','width'=>'100','align'=>'center','sortable'=>true),
//                 '备注'=>array('field'=>'remark','width'=>'100','align'=>'center'),
                '外部编码'=>array('field'=>'spec_wh_no','width'=>'100','align'=>'center','sortable'=>true),
                '仓储编码'=>array('field'=>'spec_wh_no2','width'=>'100','align'=>'center','sortable'=>true),
//				'仓储库存'=>array('field'=>'wms_sync_stock','width'=>'100','align'=>'center'),
//				'库存差异'=>array('field'=>'wms_stock_diff','width'=>'100','align'=>'center'),
//				'同步时间'=>array('field'=>'wms_sync_time','width'=>'100','align'=>'center'),
            ),
            'adjustprice'=>array(
                '仓库'=>array('field'=>'warehouse_name','width'=>'100'),
                '库存数量'=>array('field'=>'stock_num','width'=>'100'),
                '成本价'=>array('field'=>'cost_price','width'=>'100'),
                '调整价'=>array('field'=>'adjust_price','width'=>'100','methods'=>'editor:{type:"numberbox",options:{precision:4,min:0}}'),
                '备注'=>array('field'=>'remark','width'=>'150','methods'=>'editor:{type:"textbox"}'),
            ),
			'show_total_price'=>array(
				'id'=>array('field'=>'id','hidden'=>true),
				'仓库'=>array('field'=>'warehouse_name','width'=>'100'),
				'总成本'=>array('field'=>'cost_price','width'=>'100'),
				'总库存'=>array('field'=>'stock_num','width'=>'100'),
				'总货品种类'=>array('field'=>'spec_num','width'=>'100'),
				'总销售退货量'=>array('field'=>'refund_num','width'=>'100'),
				'总警戒库存'=>array('field'=>'safe_stock','width'=>'100'),
			),
        );
        return $fields[$key];
    }
}