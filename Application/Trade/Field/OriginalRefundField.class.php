<?php
namespace Trade\Field;
use Common\Common\Field;
class OriginalRefundField extends Field{

	protected function get($key){
		$fields=array(
				'original_refund'=>array(
						'id'		=>array('field'=>'id','hidden'=>true),
						'checkbox'	=>array('field'=>'ck','checkbox'=>true,'frozen'=>true),
						'平台'		=>array('field'=>'platform_id','width'=>80,'formatter'=>'formatter.platform_id'),
						'店铺'		=>array('field'=>'shop_id','width'=>100),
						'退款单号'		=>array('field'=>'refund_no','width'=>120),
						'原始单号'		=>array('field'=>'tid','width'=>120),
						'类型'		=>array('field'=>'type','width'=>80,'formatter'=>'formatter.api_refund_type'),
						'平台状态'		=>array('field'=>'status','width'=>80,'formatter'=>'formatter.api_refund_status'),
						'系统状态'		=>array('field'=>'process_status','width'=>80,'formatter'=>'formatter.api_refund_process_status'),
						'客服介入'		=>array('field'=>'cs_status','width'=>80,'formatter'=>'formatter.api_refund_cs_status'),
						'买家支付账号'	=>array('field'=>'pay_account','width'=>100),
						'申请退款金额'	=>array('field'=>'refund_amount','width'=>100),
						'实际退款金额'	=>array('field'=>'actual_refund_amount','width'=>100),
						'昵称'		=>array('field'=>'buyer_nick','width'=>100),
						'退款原因'		=>array('field'=>'reason','width'=>100),
						'备注'		=>array('field'=>'remark','width'=>100),
						'物流公司'		=>array('field'=>'logistics_name','width'=>100),
						'物流单号'		=>array('field'=>'logistics_no','width'=>100),
						'操作人'		=>array('field'=>'operator_id','width'=>100),
						'退款时间'		=>array('field'=>'refund_time','width'=>100),
						'修改时间'		=>array('field'=>'modified','width'=>100),
						'创建时间'		=>array('field'=>'created','width'=>100),
				),
		);
		return $fields[$key];
	}
}