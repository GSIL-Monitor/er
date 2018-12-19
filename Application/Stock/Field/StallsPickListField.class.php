<?php
namespace Stock\Field;
use Common\Common\Field;
use Common\Common\UtilDB;

class StallsPickListField extends Field{

    protected function get($key){
        $fields = array(

            'set_logistics_templates' =>array(
                'id' 		=> array('field' => 'logistics_id', 'hidden' => true),
                '物流公司'	=>array('field'=>'logistics_name','width'=>'150'),
                '打印机(可编辑)'=> array('field' => 'name', 'width' => '180','editor'=>'{type:"combobox",options:{valueField:"name",textField:"name",editable:false}}'),
                '模板(可编辑)'=> array('field' => 'title', 'width' => '150','editor'=>'{type:"combobox",options:{valueField:"title",textField:"title",editable:false,validType:"new_box_no_unique"}}'),
            ),
            'purchasepick' =>array(
                'id' => array('field' => 'id', 'hidden' => true),
                '货品名称' => array('field' => 'goods_name', 'width' => 100),
                '规格名称' => array('field' => 'spec_name', 'width' => 100),
                '商家编码'=>array('field'=>'spec_no','width'=>100),
                '货品编号'=>array('field'=>'goods_no','width'=>100),
                '实发数量'=>array('field'=>'actual_num','width'=>80),
                '已分拣数量'=>array('field'=>'sorted_num','width'=>80),
                '成交价'=>array('field'=>'order_price','width'=>80),
                '分摊后总价'=>array('field'=>'share_amount','width'=>80),
                '分摊邮费'=>array('field'=>'share_post','width'=>80),
                '已付'  => array('field'=>'paid','width'=>80),
                '估重'  => array('field'=>'weight','width'=>80,),
            ),
        );
        return $fields[$key];
    }
}