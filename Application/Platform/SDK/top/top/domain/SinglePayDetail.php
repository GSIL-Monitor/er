<?php

/**
 * 小额支付单笔支付
 * @author auto create
 */
class SinglePayDetail
{
	
	/** 
	 * 支付宝冻结订单号
	 **/
	public $alipay_order_no;
	
	/** 
	 * 本次支付金额
	 **/
	public $amount;
	
	/** 
	 * 创建时间
	 **/
	public $create_time;
	
	/** 
	 * 最近修改时间
	 **/
	public $modified_time;
	
	/** 
	 * 本次支付url地址
	 **/
	public $pay_url;
	
	/** 
	 * 收款人的userId
	 **/
	public $receive_user_id;
	
	/** 
	 * 本次支付的支付宝流水号
	 **/
	public $transfer_order_no;
	
	/** 
	 * 本次支付的外部单据号
	 **/
	public $transfer_out_order_no;	
}
?>