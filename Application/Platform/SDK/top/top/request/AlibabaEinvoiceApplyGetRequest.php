<?php
/**
 * TOP API: alibaba.einvoice.apply.get request
 * 
 * @author auto create
 * @since 1.0, 2016.06.23
 */
class AlibabaEinvoiceApplyGetRequest
{
	/** 
	 * 开票申请ID，跟消息中的apply_id对应
	 **/
	private $applyId;
	
	/** 
	 * 电商平台代码。TB=淘宝 、TM=天猫 、JD=京东、DD=当当、PP=拍拍、YX=易讯、EBAY=ebay、QQ=QQ网购、AMAZON=亚马逊、SN=苏宁、GM=国美、WPH=唯品会、JM=聚美、LF=乐蜂、MGJ=蘑菇街、JS=聚尚、PX=拍鞋、YT=银泰、YHD=1号店、VANCL=凡客、YL=邮乐、YG=优购、1688=阿里巴巴、POS=POS门店、OTHER=其他,  (只传英文编码)
	 **/
	private $platformCode;
	
	/** 
	 * 平台订单号
	 **/
	private $platformTid;
	
	private $apiParas = array();
	
	public function setApplyId($applyId)
	{
		$this->applyId = $applyId;
		$this->apiParas["apply_id"] = $applyId;
	}

	public function getApplyId()
	{
		return $this->applyId;
	}

	public function setPlatformCode($platformCode)
	{
		$this->platformCode = $platformCode;
		$this->apiParas["platform_code"] = $platformCode;
	}

	public function getPlatformCode()
	{
		return $this->platformCode;
	}

	public function setPlatformTid($platformTid)
	{
		$this->platformTid = $platformTid;
		$this->apiParas["platform_tid"] = $platformTid;
	}

	public function getPlatformTid()
	{
		return $this->platformTid;
	}

	public function getApiMethodName()
	{
		return "alibaba.einvoice.apply.get";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
	
	public function check()
	{
		
		RequestCheckUtil::checkNotNull($this->applyId,"applyId");
		RequestCheckUtil::checkNotNull($this->platformCode,"platformCode");
		RequestCheckUtil::checkNotNull($this->platformTid,"platformTid");
	}
	
	public function putOtherTextParam($key, $value) {
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}
}
