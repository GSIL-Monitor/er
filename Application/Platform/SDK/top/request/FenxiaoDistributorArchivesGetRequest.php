<?php
/**
 * TOP API: taobao.fenxiao.distributor.archives.get request
 * 
 * @author auto create
 * @since 1.0, 2013-01-06 16:39:45
 */
class FenxiaoDistributorArchivesGetRequest
{
	/** 
	 * 分销商淘宝店主ID
	 **/
	private $distributorUserId;
	
	private $apiParas = array();
	
	public function setDistributorUserId($distributorUserId)
	{
		$this->distributorUserId = $distributorUserId;
		$this->apiParas["distributor_user_id"] = $distributorUserId;
	}

	public function getDistributorUserId()
	{
		return $this->distributorUserId;
	}

	public function getApiMethodName()
	{
		return "taobao.fenxiao.distributor.archives.get";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
	
	public function check()
	{
		
		RequestCheckUtil::checkNotNull($this->distributorUserId,"distributorUserId");
	}
	
	public function putOtherTextParam($key, $value) {
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}
}
