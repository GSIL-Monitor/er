<?php

/**
 * 支付宝用户账户信息
 * @author auto create
 */
class AlipayAccount
{
	
	/** 
	 * 支付宝用户ID
	 **/
	public $alipay_user_id;
	
	/** 
	 * 可用余额
	 **/
	public $available_amount;
	
	/** 
	 * 不可用余额
	 **/
	public $freeze_amount;
	
	/** 
	 * 余额总额
	 **/
	public $total_amount;	
}
?>