<?php

/**
 * 支付宝交易明细
 * @author auto create
 */
class TradeRecord
{
	
	/** 
	 * 支付宝订单号
	 **/
	public $alipay_order_no;
	
	/** 
	 * 订单创建时间
	 **/
	public $create_time;
	
	/** 
	 * 资金流入流程类型,in表示收入,out表示支出
	 **/
	public $in_out_type;
	
	/** 
	 * 商户订单号
	 **/
	public $merchant_order_no;
	
	/** 
	 * 订单最后修改时间
	 **/
	public $modified_time;
	
	/** 
	 * 对方支付宝登录号，需要隐藏
	 **/
	public $opposite_logon_id;
	
	/** 
	 * 对方姓名，需要隐藏
	 **/
	public $opposite_name;
	
	/** 
	 * 对方支付宝账号
	 **/
	public $opposite_user_id;
	
	/** 
	 * 订单来源，为空查询所有来源。淘宝(taobao)，支付宝(alipay)，其它(other)
	 **/
	public $order_from;
	
	/** 
	 * 订单状态
	 **/
	public $order_status;
	
	/** 
	 * 订单的名称，描述订单的摘要信息，如交易的商品名称
	 **/
	public $order_title;
	
	/** 
	 * 订单类型
	 **/
	public $order_type;
	
	/** 
	 * 本方支付宝登录号，需要隐藏
	 **/
	public $owner_logon_id;
	
	/** 
	 * 本方姓名，需要隐藏
	 **/
	public $owner_name;
	
	/** 
	 * 本方支付宝账号
	 **/
	public $owner_user_id;
	
	/** 
	 * 订单服务费
	 **/
	public $service_charge;
	
	/** 
	 * 订单总金额
	 **/
	public $total_amount;	
}
?>