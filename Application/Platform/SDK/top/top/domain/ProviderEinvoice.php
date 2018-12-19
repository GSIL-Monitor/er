<?php

/**
 * 电子发票对象
 * @author auto create
 */
class ProviderEinvoice
{
	
	/** 
	 * 防伪码
	 **/
	public $anti_fake_code;
	
	/** 
	 * 密文（密码区的字符串)
	 **/
	public $ciphertext;
	
	/** 
	 * 税控设备编号(新版电子发票有)
	 **/
	public $device_no;
	
	/** 
	 * erp自定义订单号
	 **/
	public $erp_tid;
	
	/** 
	 * 文件类型(pdf,jpg,png)
	 **/
	public $file_data_type;
	
	/** 
	 * 实际开票金额
	 **/
	public $invoice_amount;
	
	/** 
	 * 发票代码
	 **/
	public $invoice_code;
	
	/** 
	 * 开票日期
	 **/
	public $invoice_date_time;
	
	/** 
	 * 发票文件PDF内容，PDF的byte[]用base64编码后的字段串。
	 **/
	public $invoice_file_data;
	
	/** 
	 * 发票号码
	 **/
	public $invoice_no;
	
	/** 
	 * 二维码
	 **/
	public $qr_code;
	
	/** 
	 * 电子发票流水号，标记业务上的唯一请求
	 **/
	public $serial_no;	
}
?>