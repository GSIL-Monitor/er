<?php
/**
 * TOP API: taobao.wlb.import.threepl.offline.consign request
 * 
 * @author auto create
 * @since 1.0, 2017.05.08
 */
class WlbImportThreeplOfflineConsignRequest
{
	/** 
	 * 发件人地址库id
	 **/
	private $fromId;
	
	/** 
	 * 资源code
	 **/
	private $resCode;
	
	/** 
	 * 资源id
	 **/
	private $resId;
	
	/** 
	 * 交易单号
	 **/
	private $tradeId;
	
	/** 
	 * 运单号
	 **/
	private $waybillNo;
	
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

	public function setResCode($resCode)
	{
		$this->resCode = $resCode;
		$this->apiParas["res_code"] = $resCode;
	}

	public function getResCode()
	{
		return $this->resCode;
	}

	public function setResId($resId)
	{
		$this->resId = $resId;
		$this->apiParas["res_id"] = $resId;
	}

	public function getResId()
	{
		return $this->resId;
	}

	public function setTradeId($tradeId)
	{
		$this->tradeId = $tradeId;
		$this->apiParas["trade_id"] = $tradeId;
	}

	public function getTradeId()
	{
		return $this->tradeId;
	}

	public function setWaybillNo($waybillNo)
	{
		$this->waybillNo = $waybillNo;
		$this->apiParas["waybill_no"] = $waybillNo;
	}

	public function getWaybillNo()
	{
		return $this->waybillNo;
	}

	public function getApiMethodName()
	{
		return "taobao.wlb.import.threepl.offline.consign";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
	
	public function check()
	{
		
	}
	
	public function putOtherTextParam($key, $value) {
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}
}
