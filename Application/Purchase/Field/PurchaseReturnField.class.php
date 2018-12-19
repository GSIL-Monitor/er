<?php
namespace Purchase\Field;
use Common\Common\Field;
use Common\Common\UtilDB;

class PurchaseReturnField extends Field{
	protected function get($key){
		$fields = array(
			'purchase_reurn_order'=>array(
				'id' => array('field' => 'id', 'hidden' => true),
				'商家编码'=>array('field'=>'spec_no','width'=>'100'),
				'货品编号'=>array('field'=>'goods_no','width'=>'100'),
				'货品名称' => array('field' => 'goods_name', 'width' => '100'),
				'货品图片'=>array('field' => 'img_url', 'width' => '100','formatter'=>'formatter.print_img'),
                '规格名称' => array('field' => 'spec_name', 'width' => '100'),
                '规格码' => array('field' => 'spec_code', 'width' => '100'),
                '条形码' => array('field' => 'barcode', 'width' => '100'),
				'品牌id' => array('field' => 'brand_id', 'width' => '100','hidden'=>true),
                '品牌' => array('field' => 'brand_name', 'width' => '100'),
				'基本单位'=>array('field'=>'unit_name','width'=>'100'),
				'采购到货量'=>array('field'=>'purchase_arrive_num','width'=>'100'),
				'库存量'=>array('field'=>'stock_num','width'=>'100'),
				'退货量(可编辑)'=>array('field'=>'num','width'=>'100','editor'=>'{type:"numberbox",options:{required:true,precision:'.get_config_value('point_number',0).',value:0,min:0}}'),
				'零售价(可编辑)' =>array('field' => 'ori_price', 'width' => '100','methods'=>'editor:{type:"numberbox",options:{required:true,precision:4,min:0}}'),
				'折扣率(可编辑)' =>array('field' => 'discount_rate', 'width' => '100','methods'=>'editor:{type:"numberbox",options:{required:true,precision:4,min:0}}'),
				'采购价(可编辑)' =>array('field' => 'price', 'width' => '100','methods'=>'editor:{type:"numberbox",options:{required:true,precision:4,min:0,value:0}}'),
				'金额' =>array('field' => 'amount', 'width' => '100'),
				'备注(可编辑)'=>array('field'=>'remark','width'=>'100','methods'=>'editor:{type:"textbox"}'),
			),
            'purchase_reurn_management'=>array(
                'id' => array('field' => 'id', 'hidden' => true),
                '退货单号'=>array('field'=>'return_no','width'=>'150','align' => 'center'),
//                '外部单号'=>array('field'=>'outer_no','width'=>'100','align' => 'center'),
//                'API单号'=>array('field'=>'api_outer_no','width'=>'100','align' => 'center'),
//                '仓储单号'=>array('field'=>'wms_outer_no','width'=>'100','align' => 'center'),
//                '推送信息'=>array('field'=>'error_info','width'=>'100','align' => 'center'),
                '供应商'=>array('field'=>'provider_name','width'=>'100','align' => 'center'),
                '当前状态' => array('field' => 'status', 'width' => '100','align' => 'center','formatter' => 'formatter.purchase_return_status'),
                '仓库' => array('field' => 'warehouse_name', 'width' => '100','align' => 'center'),
				'外部WMS单号' => array('field' => 'outer_no', 'width' => '150','align' => 'center'),
				'WMS错误信息' => array('field' => 'error_info', 'width' => '150','align' => 'center'),
                '物流公司' => array('field' => 'logistics_type', 'width' => '100','align' => 'center'),
                '建单者' => array('field' => 'creator_name', 'width' => '100','align' => 'center'),
                '关联采购员' => array('field' => 'purchaser_name', 'width' => '100','align' => 'center'),
                '货款' => array('field' => 'goods_fee', 'width' => '100','align' => 'center'),
                '其他费用' => array('field' => 'other_fee', 'width' => '100','align' => 'center'),
                '邮费'=>array('field'=>'post_fee','width'=>'100','align' => 'center'),
                '总货款'=>array('field'=>'total_fee','width'=>'100','align' => 'center'),
                '退货开单量'=>array('field'=>'goods_count','width'=>'100','align' => 'center'),
                '货品类别'=>array('field'=>'goods_type_count','width'=>'100','align' => 'center'),
                '退货出库量' =>array('field' => 'goods_out_count', 'width' => '100','align' => 'center'),
//                '引用采购单号' =>array('field' => 'load_purchase_no', 'width' => '100','align' => 'center'),
                '备注'=>array('field'=>'remark','width'=>'100','align' => 'center'),
                '建单时间'=>array('field'=>'created','width'=>'100','align' => 'center'),
                '修改时间'=>array('field'=>'modified','width'=>'100','align' => 'center'),
            ),
            'purchase_return_detail'=>array(
                'id' => array('field' => 'id', 'hidden' => true),
                '商家编码' => array('field' => 'spec_no', 'width' => '100'),
                '货品编号' => array('field' => 'goods_no', 'width' => '100'),
                '货品名称' => array('field' => 'goods_name', 'width' => '100'),
                '规格码' => array('field' => 'spec_code', 'width' => '100'),
                '规格名称' => array('field' => 'spec_name', 'width' => '100'),
                '条形码' => array('field' => 'barcode', 'width' => '100'),
                '退货量' =>array('field' => 'num','width' => '100'),
                '辅助数量' =>array('field' => 'num2','width' => '100'),
                '品牌id'=>array('field'=>'brand_id','width'=>'100','hidden'=>true),
                '品牌'=>array('field'=>'brand_name','width'=>'100'),
                '基本单位' => array('field' => 'unit_name', 'width' => '100'),
                '出库量' => array('field' => 'out_num', 'width' => '100'),
                '采购价' => array('field' => 'cost_price', 'width' => '100'),
                '折扣率' => array('field' => 'discount', 'width' => '100'),
                '金额' => array('field' => 'amount', 'width' => '100'),
                '备注' => array('field' => 'remark', 'width' => '100'),
            ),
            'purchase_return_log'=>array(
                'id' => array('field' => 'id', 'hidden' => true),
                '操作员' => array('field' => 'account', 'width' => '100'),
                '操作' => array('field' => 'remark', 'width' => '800'),
                '时间' => array('field' => 'created', 'width' => '150'),

            ),
		);
		return $fields[$key];
	}
}