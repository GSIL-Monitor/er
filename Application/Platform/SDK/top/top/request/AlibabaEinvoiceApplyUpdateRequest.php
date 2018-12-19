<?php
/**
 * TOP API: alibaba.einvoice.apply.update request
 * 
 * @author auto create
 * @since 1.0, 2016.06.23
 */
class AlibabaEinvoiceApplyUpdateRequest
{
	/** 
	 * 开票申请ID
	 **/
	private $applyId;
	
	/** 
	 * 订单所属平台
	 **/
	private $platformCode;
	
	/** 
	 * 订单号
	 **/
	private $platformTid;
	
	/** 
	 * 拒绝申请的原因
	 **/
	private $reason;
	
	/** 
	 * 审核结果，0-拒绝，2-同意
	 **/
	private $status;
	
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

	public function setReason($reason)
	{
		$this->reason = $reason;
		$this->apiParas["reason"] = $reason;
	}

	public function getReason()
	{
		return $this->reason;
	}

	public function setStatus($status)
	{
		$this->status = $status;
		$this->apiParas["status"] = $status;
	}

	public function getStatus()
	{
		return $this->status;
	}

	public function getApiMethodName()
	{
		return "alibaba.einvoice.apply.update";
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
		RequestCheckUtil::checkNotNull($this->status,"status");
	}
	
	public function putOtherTextParam($key, $value) {
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}
}
