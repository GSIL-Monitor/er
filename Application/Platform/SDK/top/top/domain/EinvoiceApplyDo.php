<?php

/**
 * 查询结果
 * @author auto create
 */
class EinvoiceApplyDo
{
	
	/** 
	 * 开票申请ID
	 **/
	public $apply_id;
	
	/** 
	 * 买家备注
	 **/
	public $memo;
	
	/** 
	 * 订单所属平台
	 **/
	public $platform_code;
	
	/** 
	 * 订单号
	 **/
	public $platform_tid;
	
	/** 
	 * 拒绝申请原因
	 **/
	public $reason;
	
	/** 
	 * 申请状态，0-拒绝，1-申请中，2-同意
	 **/
	public $status;
	
	/** 
	 * 发票抬头
	 **/
	public $title;	
}
?>