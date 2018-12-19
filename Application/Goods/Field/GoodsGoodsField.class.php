<?php
/**
 * Created by PhpStorm.
 * User: yanfeng
 * Date: 2015/11/24
 * Time: 17:10
 */
namespace Goods\Field;

use Common\Common\Field;
use Common\Common\UtilDB;
use Common\Common\UtilTool;


class GoodsGoodsField extends Field {

    /**
     * 获取货品档案页面的fields
     * author:luyanfeng
     * @param $key
     * @return mixed
     */
    protected function get($key) {
        $list           = UtilDB::getCfgList(array('unit'));
        $unit           = $list['unit'];
        $formatter_unit = UtilTool::array2dict($unit);
        $formatter_unit = json_encode($formatter_unit);
        $large_type = array(array("id"=>"0","name"=>"非大件",'selected'=>'true'),array("id"=>"1","name"=>"普通大件"),array("id"=>"2","name"=>"独立大件"));
        $fields = array(
            "goods_goods" => array(
                'id'      => array('field' => 'id', 'hidden' => true, 'sortable' => true),
                'flag_id' => array('field' => 'flag_id', 'hidden' => true, 'sortable' => true),
                '货品编码'    => array('field' => 'goods_no', 'width' => 100, 'sortable' => true),
                '货品名称'    => array('field' => 'goods_name', 'width' => 150, 'sortable' => true),
                '货品简称'    => array('field' => 'short_name', 'width' => 100, 'sortable' => true),
                '货品别称'    => array('field' => 'alias', 'width' => 100, 'sortable' => true),
                '分类'      => array('field' => 'class_id', 'width' => 100,'sortable' => true),
                '品牌'      => array('field' => 'brand_id', 'width' => 100,'sortable' => true),
                '货品类别'    => array('field' => 'goods_type', 'width' => 100, 'sortable' => true, 'formatter' => 'formatter.goods_type'),
                '规格数'     => array('field' => 'spec_count', 'width' => 100, 'sortable' => true),
                '单位'      => array('field' => 'unit', 'width' => 60, 'sortable' => true),
                '产地'      => array('field' => 'origin', 'width' => 120, 'sortable' => true)
            ),
            "add_goods" => array(
            'spec_id'      => array('field' => 'spec_id', 'hidden' => true),
            '图片' => array('field' => 'img_url',"width" => 80,'editor'=>'{type:"textbox"} ','formatter'=>'formatter.print_img'),
            '商家编码'    => array('field' => 'spec_no', 'width' => 100, 'editor'=>'{type:"validatebox",options:{required:true,validType:["spec_no_unique","check_merchant_no"]}}'),
            "规格名称"  => array("field" => "spec_name", "width" => 100,'editor'=>'{type:"textbox"} ',),
            "规格码"   => array("field" => "spec_code", "width" => 80,'editor'=>'{type:"textbox"} ',),
            '主条码'  => array('field' => 'barcode', 'width' => 80, 'editor'=>'{type:"validatebox"} '),
            "零售价"   => array("field" => "retail_price", "width" => 60,'align'=>'right','methods'=>'editor:{type:"numberbox",options:{precision:4,min:0}}'),
            "市场价"   => array("field" => "market_price", "width" => 60,'align'=>'right','methods'=>'editor:{type:"numberbox",options:{precision:4,min:0}}'),
            "最低价"   => array("field" => "lowest_price", "width" => 60,'align'=>'right','methods'=>'editor:{type:"numberbox",options:{precision:4,min:0}}'),
            "有效期"   => array("field" => "validity_days", "width" => 60,'editor' => '{type:"numberbox"}'),
            '是否爆款'    => array('field' => 'is_hotcake','align'=>'center', 'width' => 60, 'methods'=>'editor:{type:"checkbox",options:{on:1,off:0}}','formatter'=>'formatter.boolen'),
            '允许负库存出库'    => array('field' => 'is_allow_neg_stock','align'=>'center', 'width' => 130, 'methods'=>'editor:{type:"checkbox",options:{on:1,off:0}}','formatter'=>'formatter.boolen'),
            "长(CM)" => array("field" => "length", "width" => 60,'editor'=>'{type:"numberbox",options:{precision:4,min:0}}'),
            "宽(CM)" => array("field" => "width", "width" => 60,'editor'=>'{type:"numberbox",options:{precision:4,min:0}}'),
            "高(CM)" => array("field" => "height", "width" => 60,'editor'=>'{type:"numberbox",options:{precision:4,min:0}}'),
            "重量(kg)" => array("field" => 'weight',"width" => 60,'editor'=>'{type:"numberbox",options:{precision:4,min:0}}'),
            '单位' => array("field" => 'unit',"width" => 80,'formatter'=> "function(value,row){var data=$formatter_unit; return data[row.unit]; }",'editor' => '{type:"combobox",options:{valueField:"id",textField:"name",required:true,editable:false,data:'. json_encode($unit) .'}}'),
            '大件类别' => array("field" => 'large_type',"width" => 60,'editor' => '{type:"combobox",options:{valueField:"id",textField:"name",required:true,editable:false,data:'. json_encode($large_type) .'}}','formatter'=>'formatter.large_type'),
            '无需验货'    => array('field' => 'is_not_need_examine','align'=>'center', 'width' => 60, 'editor' => '{type:"checkbox",options:{on:1,off:0}}','formatter'=>'formatter.boolen'),
            '自定义1' => array("field" => 'prop1',"width" => 100,'editor' => '{type:"textbox"}'),
            '自定义2' => array("field" => 'prop2',"width" => 100,'editor' => '{type:"textbox"}'),
            '自定义3' => array("field" => 'prop3',"width" => 100,'editor' => '{type:"textbox"}'),
            '自定义4' => array("field" => 'prop4',"width" => 100,'editor' => '{type:"textbox"}'),
            '备注' => array("field" => 'remark',"width" => 60,'editor' => '{type:"textbox"}'),
        )

        );
        return $fields[ $key ];
    }

}