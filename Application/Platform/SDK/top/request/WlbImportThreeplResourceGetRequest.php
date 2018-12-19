<?php
/**
 * TOP API: taobao.wlb.import.threepl.resource.get request
 * 
 * @author auto create
 * @since 1.0, 2017.04.11
 */
class WlbImportThreeplResourceGetRequest
{
	/** 
	 * 发货地区域id
	 **/
	private $fromId;
	
	/** 
	 * 收件人地址
	 **/
	private $toAddress;
	
	/** 
	 * ONLINE或者OFFLINE
	 **/
	private $type;
	
	private $apiParas = array();
	
	public function setFromId($fromId)
	{
		$this->fromId = $fromId;
		$this->apiParas["from_id"] = $fromId;
	}

	public function getFromId()
	{
		return $this->fromId;
	}

	public function setToAddress($toAddress)
	{
		$this->toAddress = $toAddress;
		$this->apiParas["to_address"] = $toAddress;
	}

	public function getToAddress()
	{
		return $this->toAddress;
	}

	public function setType($type)
	{
		$this->type = $type;
		$this->apiParas["type"] = $type;
	}

	public function getType()
	{
		return $this->type;
	}

	public function getApiMethodName()
	{
		return "taobao.wlb.import.threepl.resource.get";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
	
	public function check()
	{
		
		RequestCheckUtil::checkNotNull($this->fromId,"fromId");
		RequestCheckUtil::checkNotNull($this->type,"type");
	}
	
	public function putOtherTextParam($key, $value) {
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}
}
