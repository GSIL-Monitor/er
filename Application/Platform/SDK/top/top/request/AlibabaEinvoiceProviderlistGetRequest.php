<?php
/**
 * TOP API: alibaba.einvoice.providerlist.get request
 * 
 * @author auto create
 * @since 1.0, 2016.05.31
 */
class AlibabaEinvoiceProviderlistGetRequest
{
	
	private $apiParas = array();
	
	public function getApiMethodName()
	{
		return "alibaba.einvoice.providerlist.get";
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