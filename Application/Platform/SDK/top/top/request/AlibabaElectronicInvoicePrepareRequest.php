<?php
/**
 * TOP API: alibaba.electronic.invoice.prepare request
 * 
 * @author auto create
 * @since 1.0, 2016.06.29
 */
class AlibabaElectronicInvoicePrepareRequest
{
	/** 
	 * 开票操作类型：1 交易成功 2 退货、退款成功 3 电子换纸质 4 换发票内容（抬头的变化等，其他未归类的情况可以填为4）。第1次开蓝票值必须是1，后续冲红及再冲蓝invoice_action_type都不能值为1。比如如果是退货退款发生时， 开红票蓝票都传2
	 **/
	private $invoiceActionType;
	
	/** 
	 * <span style=
	 **/
	private $invoiceTitle;
	
	/** 
	 * 发票类型，1:蓝票，2:红票
	 **/
	private $invoiceType;
	
	/** 
	 * 开票流水号，唯一标志开票请求。如果两次请求流水号相同，则表示重复请求, 由于ERP根据自己的业务请求确定。可采用订单id+操作代码。比如：订单号转成十六进制或36进程 + 操作代码（表示红票还是蓝票）+ 操作序号。
	 **/
	private $serialNo;
	
	/** 
	 * 淘宝的主订单id
	 **/
	private $tid;
	
	private $apiParas = array();
	
	public function setInvoiceActionType($invoiceActionType)
	{
		$this->invoiceActionType = $invoiceActionType;
		$this->apiParas["invoice_action_type"] = $invoiceActionType;
	}

	public function getInvoiceActionType()
	{
		return $this->invoiceActionType;
	}

	public function setInvoiceTitle($invoiceTitle)
	{
		$this->invoiceTitle = $invoiceTitle;
		$this->apiParas["invoice_title"] = $invoiceTitle;
	}

	public function getInvoiceTitle()
	{
		return $this->invoiceTitle;
	}

	public function setInvoiceType($invoiceType)
	{
		$this->invoiceType = $invoiceType;
		$this->apiParas["invoice_type"] = $invoiceType;
	}

	public function getInvoiceType()
	{
		return $this->invoiceType;
	}

	public function setSerialNo($serialNo)
	{
		$this->serialNo = $serialNo;
		$this->apiParas["serial_no"] = $serialNo;
	}

	public function getSerialNo()
	{
		return $this->serialNo;
	}

	public function setTid($tid)
	{
		$this->tid = $tid;
		$this->apiParas["tid"] = $tid;
	}

	public function getTid()
	{
		return $this->tid;
	}

	public function getApiMethodName()
	{
		return "alibaba.electronic.invoice.prepare";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
	
	public function check()
	{
		
		RequestCheckUtil::checkNotNull($this->invoiceActionType,"invoiceActionType");
		RequestCheckUtil::checkMaxValue($this->invoiceActionType,4,"invoiceActionType");
		RequestCheckUtil::checkMinValue($this->invoiceActionType,1,"invoiceActionType");
		RequestCheckUtil::checkMaxLength($this->invoiceTitle,50,"invoiceTitle");
		RequestCheckUtil::checkNotNull($this->invoiceType,"invoiceType");
		RequestCheckUtil::checkMaxValue($this->invoiceType,2,"invoiceType");
		RequestCheckUtil::checkMinValue($this->invoiceType,1,"invoiceType");
		RequestCheckUtil::checkNotNull($this->serialNo,"serialNo");
		RequestCheckUtil::checkMaxLength($this->serialNo,30,"serialNo");
		RequestCheckUtil::checkNotNull($this->tid,"tid");
	}
	
	public function putOtherTextParam($key, $value) {
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}
}
