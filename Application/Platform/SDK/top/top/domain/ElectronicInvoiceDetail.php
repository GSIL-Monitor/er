<?php

/**
 * 电子发票详情
 * @author auto create
 */
class ElectronicInvoiceDetail
{
	
	/** 
	 * 电子发票号
	 **/
	public $electronic_invoice_no;
	
	/** 
	 * 发票代码
	 **/
	public $invoice_code;
	
	/** 
	 * 发票的下载地址，7天失效。
	 **/
	public $invoice_file_url;
	
	/** 
	 * 发票号码
	 **/
	public $invoice_no;
	
	/** 
	 * 开票日期
	 **/
	public $invoice_time;
	
	/** 
	 * 1 蓝票 2 红票
	 **/
	public $invoice_type;
	
	/** 
	 * 开票流水号
	 **/
	public $serial_no;
	
	/** 
	 * 当前开票类型的开票状态，0:等待开票, 1:开票中，2:开票完成。具体的开票类型参考invoice_type字段。status与invoice_type一起决定是蓝票的开票状态，还是红票的开票状态。如果状态是0或1时，除了invoice_type外的其它返回字段表示上一次开票结果。在调用alibaba.electronic.invoice.prepare前为等待开票；在调用alibaba.electronic.invoice.upload前为开票中；在调用alibaba.electronic.invoice.upload后为开票完成。
	 **/
	public $status;
	
	/** 
	 * 淘宝的主订单id
	 **/
	public $tid;	
}
?>