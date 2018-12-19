<?php

/**
 * 支付宝交易记录明细
 * @author auto create
 */
class AlipayRecord
{
	
	/** 
	 * 支付宝订单号
	 **/
	public $alipay_order_no;
	
	/** 
	 * 当时支付宝账户余额
	 **/
	public $balance;
	
	/** 
	 * 子业务类型
	 **/
	public $business_type;
	
	/** 
	 * 创建时间
	 **/
	public $create_time;
	
	/** 
	 * 收入金额
	 **/
	public $in_amount;
	
	/** 
	 * 账号备注
	 **/
	public $memo;
	
	/** 
	 * 支付宝订单号
	 **/
	public $merchant_order_no;
	
	/** 
	 * 对方的支付宝ID
	 **/
	public $opt_user_id;
	
	/** 
	 * 支出金额
	 **/
	public $out_amount;
	
	/** 
	 * 自己的支付宝ID
	 **/
	public $self_user_id;
	
	/** 
	 * 账务类型
	 **/
	public $type;	
}
?>