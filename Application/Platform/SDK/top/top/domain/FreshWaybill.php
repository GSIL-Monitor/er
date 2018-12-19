<?php

/**
 * 生鲜电子面单返回信息
 * @author auto create
 */
class FreshWaybill
{
	
	/** 
	 * 简称
	 **/
	public $alias;
	
	/** 
	 * 预留扩展字段
	 **/
	public $feature;
	
	/** 
	 * 大头笔
	 **/
	public $short_address;
	
	/** 
	 * 预计到达时间
	 **/
	public $time;
	
	/** 
	 * 交易号
	 **/
	public $trade_id;
	
	/** 
	 * 获取的所有电子面单号，以“;”分隔
	 **/
	public $waybill_code;	
}
?>