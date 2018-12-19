<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\biz\request;

class UpdateOrderPayResultReq {
	
	static $_TSPEC;
	public $userId = null;
	public $orderCategory = null;
	public $orderCodeList = null;
	public $orderList = null;
	public $orderInvoice = null;
	public $orderPayDetailsList = null;
	public $tradeNumber = null;
	public $invoiceDeductRemark = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'userId'
			),
			2 => array(
			'var' => 'orderCategory'
			),
			3 => array(
			'var' => 'orderCodeList'
			),
			4 => array(
			'var' => 'orderList'
			),
			5 => array(
			'var' => 'orderInvoice'
			),
			6 => array(
			'var' => 'orderPayDetailsList'
			),
			7 => array(
			'var' => 'tradeNumber'
			),
			8 => array(
			'var' => 'invoiceDeductRemark'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['userId'])){
				
				$this->userId = $vals['userId'];
			}
			
			
			if (isset($vals['orderCategory'])){
				
				$this->orderCategory = $vals['orderCategory'];
			}
			
			
			if (isset($vals['orderCodeList'])){
				
				$this->orderCodeList = $vals['orderCodeList'];
			}
			
			
			if (isset($vals['orderList'])){
				
				$this->orderList = $vals['orderList'];
			}
			
			
			if (isset($vals['orderInvoice'])){
				
				$this->orderInvoice = $vals['orderInvoice'];
			}
			
			
			if (isset($vals['orderPayDetailsList'])){
				
				$this->orderPayDetailsList = $vals['orderPayDetailsList'];
			}
			
			
			if (isset($vals['tradeNumber'])){
				
				$this->tradeNumber = $vals['tradeNumber'];
			}
			
			
			if (isset($vals['invoiceDeductRemark'])){
				
				$this->invoiceDeductRemark = $vals['invoiceDeductRemark'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'UpdateOrderPayResultReq';
	}
	
	public function read($input){
		
		$input->readStructBegin();
		while(true){
			
			$schemeField = $input->readFieldBegin();
			if ($schemeField == null) break;
			$needSkip = true;
			
			
			if ("userId" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->userId); 
				
			}
			
			
			
			
			if ("orderCategory" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->orderCategory); 
				
			}
			
			
			
			
			if ("orderCodeList" == $schemeField){
				
				$needSkip = false;
				
				$this->orderCodeList = array();
				$_size0 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem0 = null;
						$input->readString($elem0);
						
						$this->orderCodeList[$_size0++] = $elem0;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readListEnd();
				
			}
			
			
			
			
			if ("orderList" == $schemeField){
				
				$needSkip = false;
				
				$this->orderList = array();
				$_size1 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem1 = null;
						
						$elem1 = new \com\vip\order\common\pojo\order\vo\NotifyRequestOrderVO();
						$elem1->read($input);
						
						$this->orderList[$_size1++] = $elem1;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readListEnd();
				
			}
			
			
			
			
			if ("orderInvoice" == $schemeField){
				
				$needSkip = false;
				
				$this->orderInvoice = new \com\vip\order\common\pojo\order\vo\OrderInvoiceVO();
				$this->orderInvoice->read($input);
				
			}
			
			
			
			
			if ("orderPayDetailsList" == $schemeField){
				
				$needSkip = false;
				
				$this->orderPayDetailsList = array();
				$_size2 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem2 = null;
						
						$elem2 = new \com\vip\order\common\pojo\order\vo\OrderPayDetailVO();
						$elem2->read($input);
						
						$this->orderPayDetailsList[$_size2++] = $elem2;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readListEnd();
				
			}
			
			
			
			
			if ("tradeNumber" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->tradeNumber);
				
			}
			
			
			
			
			if ("invoiceDeductRemark" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->invoiceDeductRemark);
				
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
		
		if($this->userId !== null) {
			
			$xfer += $output->writeFieldBegin('userId');
			$xfer += $output->writeI64($this->userId);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderCategory !== null) {
			
			$xfer += $output->writeFieldBegin('orderCategory');
			$xfer += $output->writeI32($this->orderCategory);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderCodeList !== null) {
			
			$xfer += $output->writeFieldBegin('orderCodeList');
			
			if (!is_array($this->orderCodeList)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeListBegin();
			foreach ($this->orderCodeList as $iter0){
				
				$xfer += $output->writeString($iter0);
				
			}
			
			$output->writeListEnd();
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderList !== null) {
			
			$xfer += $output->writeFieldBegin('orderList');
			
			if (!is_array($this->orderList)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeListBegin();
			foreach ($this->orderList as $iter0){
				
				
				if (!is_object($iter0)) {
					
					throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
				}
				
				$xfer += $iter0->write($output);
				
			}
			
			$output->writeListEnd();
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderInvoice !== null) {
			
			$xfer += $output->writeFieldBegin('orderInvoice');
			
			if (!is_object($this->orderInvoice)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->orderInvoice->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderPayDetailsList !== null) {
			
			$xfer += $output->writeFieldBegin('orderPayDetailsList');
			
			if (!is_array($this->orderPayDetailsList)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeListBegin();
			foreach ($this->orderPayDetailsList as $iter0){
				
				
				if (!is_object($iter0)) {
					
					throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
				}
				
				$xfer += $iter0->write($output);
				
			}
			
			$output->writeListEnd();
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->tradeNumber !== null) {
			
			$xfer += $output->writeFieldBegin('tradeNumber');
			$xfer += $output->writeString($this->tradeNumber);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->invoiceDeductRemark !== null) {
			
			$xfer += $output->writeFieldBegin('invoiceDeductRemark');
			$xfer += $output->writeString($this->invoiceDeductRemark);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}

?>