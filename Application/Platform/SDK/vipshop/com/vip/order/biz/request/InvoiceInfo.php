<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\biz\request;

class InvoiceInfo {
	
	static $_TSPEC;
	public $invoiceFlag = null;
	public $invoiceHeader = null;
	public $invoiceType = null;
	public $taxPayerNo = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'invoiceFlag'
			),
			2 => array(
			'var' => 'invoiceHeader'
			),
			3 => array(
			'var' => 'invoiceType'
			),
			4 => array(
			'var' => 'taxPayerNo'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['invoiceFlag'])){
				
				$this->invoiceFlag = $vals['invoiceFlag'];
			}
			
			
			if (isset($vals['invoiceHeader'])){
				
				$this->invoiceHeader = $vals['invoiceHeader'];
			}
			
			
			if (isset($vals['invoiceType'])){
				
				$this->invoiceType = $vals['invoiceType'];
			}
			
			
			if (isset($vals['taxPayerNo'])){
				
				$this->taxPayerNo = $vals['taxPayerNo'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'InvoiceInfo';
	}
	
	public function read($input){
		
		$input->readStructBegin();
		while(true){
			
			$schemeField = $input->readFieldBegin();
			if ($schemeField == null) break;
			$needSkip = true;
			
			
			if ("invoiceFlag" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->invoiceFlag); 
				
			}
			
			
			
			
			if ("invoiceHeader" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->invoiceHeader);
				
			}
			
			
			
			
			if ("invoiceType" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->invoiceType); 
				
			}
			
			
			
			
			if ("taxPayerNo" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->taxPayerNo);
				
			}
			
			
			
			if($needSkip){
				
				\Osp\Protocol\ProtocolUtil::skip($input);
			}
			
			$input->readFieldEnd();
		}
		
		$input->readStructEnd();
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->invoiceFlag !== null) {
			
			$xfer += $output->writeFieldBegin('invoiceFlag');
			$xfer += $output->writeI32($this->invoiceFlag);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->invoiceHeader !== null) {
			
			$xfer += $output->writeFieldBegin('invoiceHeader');
			$xfer += $output->writeString($this->invoiceHeader);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->invoiceType !== null) {
			
			$xfer += $output->writeFieldBegin('invoiceType');
			$xfer += $output->writeI32($this->invoiceType);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->taxPayerNo !== null) {
			
			$xfer += $output->writeFieldBegin('taxPayerNo');
			$xfer += $output->writeString($this->taxPayerNo);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}

?>