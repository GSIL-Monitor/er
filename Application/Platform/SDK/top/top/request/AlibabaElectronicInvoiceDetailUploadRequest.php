<?php
/**
 * TOP API: alibaba.electronic.invoice.detail.upload request
 * 
 * @author auto create
 * @since 1.0, 2016.06.01
 */
class AlibabaElectronicInvoiceDetailUploadRequest
{
	/** 
	 * 防伪码
	 **/
	private $antiFakeCode;
	
	/** 
	 * 电子发票号(一般是:发票代码+发票号码)
	 **/
	private $electronicInvoiceNo;
	
	/** 
	 * 开票金额（价税合计金额），格式:100.00, 冲红时格式为"-100.00"
	 **/
	private $invoiceAmount;
	
	/** 
	 * 发票代码
	 **/
	private $invoiceCode;
	
	/** 
	 * 发票文件内容,目前只支持jpg,png,bmp,pdf格式
	 **/
	private $invoiceFileData;
	
	/** 
	 * {}
	 **/
	private $invoiceItems;
	
	/** 
	 * 发票号码
	 **/
	private $invoiceNo;
	
	/** 
	 * 开票日期, 格式"YYYY-MM-DD HH:SS:MM"
	 **/
	private $invoiceTime;
	
	/** 
	 * 发票抬头，付款方名称
	 **/
	private $invoiceTitle;
	
	/** 
	 * 1 蓝票 2 红票
	 **/
	private $invoiceType;
	
	/** 
	 * 回流红票时，对应的原蓝票发票代码
	 **/
	private $normalInvoiceCode;
	
	/** 
	 * 回流红票时，对应的原蓝票发票号码
	 **/
	private $normalInvoiceNo;
	
	/** 
	 * 开票方名称，xx商城
	 **/
	private $payeeName;
	
	/** 
	 * 收款方税务登记号
	 **/
	private $payeeRegisterNo;
	
	/** 
	 * 二维码,二维码扫码的结果。
	 **/
	private $qrCode;
	
	/** 
	 * 开票流水号，唯一标志开票请求。如果两次请求流水号相同，则表示重复请求, 由于ERP根据自己的业务请求确定。可采用订单id+操作代码，比如：订单号转成十六进制 +  操作代码（表示红票还是蓝票）+ 操作序号
	 **/
	private $serialNo;
	
	/** 
	 * 淘宝的主订单id
	 **/
	private $tid;
	
	private $apiParas = array();
	
	public function setAntiFakeCode($antiFakeCode)
	{
		$this->antiFakeCode = $antiFakeCode;
		$this->apiParas["anti_fake_code"] = $antiFakeCode;
	}

	public function getAntiFakeCode()
	{
		return $this->antiFakeCode;
	}

	public function setElectronicInvoiceNo($electronicInvoiceNo)
	{
		$this->electronicInvoiceNo = $electronicInvoiceNo;
		$this->apiParas["electronic_invoice_no"] = $electronicInvoiceNo;
	}

	public function getElectronicInvoiceNo()
	{
		return $this->electronicInvoiceNo;
	}

	public function setInvoiceAmount($invoiceAmount)
	{
		$this->invoiceAmount = $invoiceAmount;
		$this->apiParas["invoice_amount"] = $invoiceAmount;
	}

	public function getInvoiceAmount()
	{
		return $this->invoiceAmount;
	}

	public function setInvoiceCode($invoiceCode)
	{
		$this->invoiceCode = $invoiceCode;
		$this->apiParas["invoice_code"] = $invoiceCode;
	}

	public function getInvoiceCode()
	{
		return $this->invoiceCode;
	}

	public function setInvoiceFileData($invoiceFileData)
	{
		$this->invoiceFileData = $invoiceFileData;
		$this->apiParas["invoice_file_data"] = $invoiceFileData;
	}

	public function getInvoiceFileData()
	{
		return $this->invoiceFileData;
	}

	public function setInvoiceItems($invoiceItems)
	{
		$this->invoiceItems = $invoiceItems;
		$this->apiParas["invoice_items"] = $invoiceItems;
	}

	public function getInvoiceItems()
	{
		return $this->invoiceItems;
	}

	public function setInvoiceNo($invoiceNo)
	{
		$this->invoiceNo = $invoiceNo;
		$this->apiParas["invoice_no"] = $invoiceNo;
	}

	public function getInvoiceNo()
	{
		return $this->invoiceNo;
	}

	public function setInvoiceTime($invoiceTime)
	{
		$this->invoiceTime = $invoiceTime;
		$this->apiParas["invoice_time"] = $invoiceTime;
	}

	public function getInvoiceTime()
	{
		return $this->invoiceTime;
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

	public function setNormalInvoiceCode($normalInvoiceCode)
	{
		$this->normalInvoiceCode = $normalInvoiceCode;
		$this->apiParas["normal_invoice_code"] = $normalInvoiceCode;
	}

	public function getNormalInvoiceCode()
	{
		return $this->normalInvoiceCode;
	}

	public function setNormalInvoiceNo($normalInvoiceNo)
	{
		$this->normalInvoiceNo = $normalInvoiceNo;
		$this->apiParas["normal_invoice_no"] = $normalInvoiceNo;
	}

	public function getNormalInvoiceNo()
	{
		return $this->normalInvoiceNo;
	}

	public function setPayeeName($payeeName)
	{
		$this->payeeName = $payeeName;
		$this->apiParas["payee_name"] = $payeeName;
	}

	public function getPayeeName()
	{
		return $this->payeeName;
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

	public function setQrCode($qrCode)
	{
		$this->qrCode = $qrCode;
		$this->apiParas["qr_code"] = $qrCode;
	}

	public function getQrCode()
	{
		return $this->qrCode;
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
		return "alibaba.electronic.invoice.detail.upload";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
	
	public function check()
	{
		
		RequestCheckUtil::checkNotNull($this->electronicInvoiceNo,"electronicInvoiceNo");
		RequestCheckUtil::checkNotNull($this->invoiceAmount,"invoiceAmount");
		RequestCheckUtil::checkNotNull($this->invoiceCode,"invoiceCode");
		RequestCheckUtil::checkNotNull($this->invoiceFileData,"invoiceFileData");
		RequestCheckUtil::checkNotNull($this->invoiceNo,"invoiceNo");
		RequestCheckUtil::checkNotNull($this->invoiceTime,"invoiceTime");
		RequestCheckUtil::checkNotNull($this->invoiceTitle,"invoiceTitle");
		RequestCheckUtil::checkNotNull($this->invoiceType,"invoiceType");
		RequestCheckUtil::checkMaxValue($this->invoiceType,2,"invoiceType");
		RequestCheckUtil::checkMinValue($this->invoiceType,1,"invoiceType");
		RequestCheckUtil::checkNotNull($this->payeeName,"payeeName");
		RequestCheckUtil::checkNotNull($this->payeeRegisterNo,"payeeRegisterNo");
		RequestCheckUtil::checkNotNull($this->serialNo,"serialNo");
		RequestCheckUtil::checkNotNull($this->tid,"tid");
	}
	
	public function putOtherTextParam($key, $value) {
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}
}
