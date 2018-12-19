<?php

/**
 * 用户订购信息
 * @author auto create
 */
class AlipayContract
{
	
	/** 
	 * 支付宝用户ID
	 **/
	public $alipay_user_id;
	
	/** 
	 * 订购的应用名称，有效时间。
	 **/
	public $contract_content;
	
	/** 
	 * 订购的失效时间
	 **/
	public $end_time;
	
	/** 
	 * 订购URL。在sign返回false时返回应用的订购地址，可以引导用户订购。
	 **/
	public $page_url;
	
	/** 
	 * 订购的生效时间
	 **/
	public $start_time;
	
	/** 
	 * 是否订购的标识。true：代表已订购。
	 **/
	public $subscribe;	
}
?>