<?php
/**
 * TOP API: alibaba.einvoice.serialno.batch.generate request
 * 
 * @author auto create
 * @since 1.0, 2016.07.27
 */
class AlibabaEinvoiceSerialnoBatchGenerateRequest
{
	
	private $apiParas = array();
	
	public function getApiMethodName()
	{
		return "alibaba.einvoice.serialno.batch.generate";
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
