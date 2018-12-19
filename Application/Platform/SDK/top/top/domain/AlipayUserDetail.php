<?php

/**
 * 支付宝会员信息详情
 * @author auto create
 */
class AlipayUserDetail
{
	
	/** 
	 * 支付宝用户userId
	 **/
	public $alipay_user_id;
	
	/** 
	 * 是否通过实名认证
	 **/
	public $certified;
	
	/** 
	 * 支付宝登录号
	 **/
	public $logon_id;
	
	/** 
	 * 真实姓名
	 **/
	public $real_name;
	
	/** 
	 * 性别。可选值:m(男),f(女)
	 **/
	public $sex;
	
	/** 
	 * 用户状态。可选:normal(正常), supervise (监管),delete(注销)
	 **/
	public $user_status;
	
	/** 
	 * 用户类型。可选：personal（个人），company（公司）
	 **/
	public $user_type;	
}
?>