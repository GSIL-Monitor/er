<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/12/2
 * Time: 17:11
 */

namespace Purchase\Field;
use Common\Common\Field;

class AlarmPerDayField extends Field
{
    protected function get($key){
        $fields = array(
            'alarmperday'=>array(
                '品牌'=>array('field'=>'brand_name','width'=>'100'),
                '分类'=>array('field'=>'class_name','width'=>'100'),
                '商家编码'=>array('field'=>'spec_no','width'=>'100'),
                '货品名称'=>array('field'=>'goods_name','width'=>'100'),
                '货品编号'=>array('field'=>'goods_no','width'=>'100'),
                '规格名称'=>array('field'=>'spec_name','width'=>'100'),
                '规格码'=>array('field'=>'spec_code','width'=>'100'),
                '条码'=>array('field'=>'barcode','width'=>'100'),
                '库存'=>array('field'=>'stock_num','width'=>'100'),
                '警戒库存'=>array('field'=>'safe_stock','width'=>'100'),
//                '警戒天数'=>array('field'=>'alarm_days','width'=>'100'),
                '可发货库存'=>array('field'=>'avaliable_num','width'=>'100'),
                '采购量'=>array('field'=>'need_purchase_num','width'=>'100'),
                '待审核量'=>array('field'=>'order_num','width'=>'100'),
                '待发货量'=>array('field'=>'sending_num','width'=>'100'),
                '未付款量'=>array('field'=>'unpay_num','width'=>'100'),
                '预订单量'=>array('field'=>'subscribe_num','width'=>'100'),
                '采购在途量'=>array('field'=>'purchase_num','width'=>'100'),
                '采购到货量'=>array('field'=>'purchase_arrive_num','width'=>'100'),
//                '锁定量'=>array('field'=>'lock_num','width'=>'100'),
//                '7天销量'=>array('field'=>'num_7days','width'=>'100'),
//                '14天销量'=>array('field'=>'num_14days','width'=>'100'),
//                '月销量'=>array('field'=>'num_month','width'=>'100'),
//                '总销量'=>array('field'=>'num_all','width'=>'100'),
//                '需采购量'=>array('field'=>'num','width'=>'100'),
            ),
        );
        return $fields[$key];
    }
}