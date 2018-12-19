<?php
/**
 * TOP API: alibaba.einvoice.create.results.increment.get request
 * 
 * @author auto create
 * @since 1.0, 2016.05.31
 */
class AlibabaEinvoiceCreateResultsIncrementGetRequest
{
	/** 
	 * 终止查询时间
	 **/
	private $endModified;
	
	/** 
	 * 显示的页码
	 **/
	private $pageNo;
	
	/** 
	 * 页面大小(不能超过200)
	 **/
	private $pageSize;
	
	/** 
	 * 收款方税务登记证号
	 **/
	private $payeeRegisterNo;
	
	/** 
	 * 起始查询时间
	 **/
	private $startModified;
	
	/** 
	 * 开票状态 (waiting = 开票中) 、(create_success = 开票成功)、(create_failed = 开票失败)
	 **/
	private $status;
	
	private $apiParas = array();
	
	public function setEndModified($endModified)
	{
		$this->endModified = $endModified;
		$this->apiParas["end_modified"] = $endModified;
	}

	public function getEndModified()
	{
		return $this->endModified;
	}

	public function setPageNo($pageNo)
	{
		$this->pageNo = $pageNo;
		$this->apiParas["page_no"] = $pageNo;
	}

	public function getPageNo()
	{
		return $this->pageNo;
	}

	public function setPageSize($pageSize)
	{
		$this->pageSize = $pageSize;
		$this->apiParas["page_size"] = $pageSize;
	}

	public function getPageSize()
	{
		return $this->pageSize;
	}

	public function setPayeeRegisterNo($payeeRegisterNo)
	{
		$this->payeeRegisterNo = $payeeRegisterNo;
		$this->apiParas["payee_register_no"] = $payeeRegisterNo;
	}

	public function getPayeeRegisterNo()
	{
		return $this->payeeRegisterNo;
	}

	public function setStartModified($startModified)
	{
		$this->startModified = $startModified;
		$this->apiParas["start_modified"] = $startModified;
	}

	public function getStartModified()
	{
		return $this->startModified;
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
		return "alibaba.einvoice.create.results.increment.get";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
	
	public function check()
	{
		
		RequestCheckUtil::checkNotNull($this->endModified,"endModified");
		RequestCheckUtil::checkNotNull($this->payeeRegisterNo,"payeeRegisterNo");
		RequestCheckUtil::checkNotNull($this->startModified,"startModified");
	}
	
	public function putOtherTextParam($key, $value) {
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}
}
