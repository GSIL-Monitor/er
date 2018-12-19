<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\biz\request;

class MergeOrderReq {
	
	static $_TSPEC;
	public $userId = null;
	public $mainOrderId = null;
	public $orderIdList = null;
	public $mainOrderSn = null;
	public $orderSnList = null;
	public $customerSrc = null;
	public $codType = null;
	public $codAppAccount = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'userId'
			),
			2 => array(
			'var' => 'mainOrderId'
			),
			3 => array(
			'var' => 'orderIdList'
			),
			4 => array(
			'var' => 'mainOrderSn'
			),
			5 => array(
			'var' => 'orderSnList'
			),
			6 => array(
			'var' => 'customerSrc'
			),
			7 => array(
			'var' => 'codType'
			),
			8 => array(
			'var' => 'codAppAccount'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['userId'])){
				
				$this->userId = $vals['userId'];
			}
			
			
			if (isset($vals['mainOrderId'])){
				
				$this->mainOrderId = $vals['mainOrderId'];
			}
			
			
			if (isset($vals['orderIdList'])){
				
				$this->orderIdList = $vals['orderIdList'];
			}
			
			
			if (isset($vals['mainOrderSn'])){
				
				$this->mainOrderSn = $vals['mainOrderSn'];
			}
			
			
			if (isset($vals['orderSnList'])){
				
				$this->orderSnList = $vals['orderSnList'];
			}
			
			
			if (isset($vals['customerSrc'])){
				
				$this->customerSrc = $vals['customerSrc'];
			}
			
			
			if (isset($vals['codType'])){
				
				$this->codType = $vals['codType'];
			}
			
			
			if (isset($vals['codAppAccount'])){
				
				$this->codAppAccount = $vals['codAppAccount'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'MergeOrderReq';
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
			
			
			
			
			if ("mainOrderId" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->mainOrderId); 
				
			}
			
			
			
			
			if ("orderIdList" == $schemeField){
				
				$needSkip = false;
				
				$this->orderIdList = array();
				$_size0 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem0 = null;
						$input->readI64($elem0); 
						
						$this->orderIdList[$_size0++] = $elem0;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readListEnd();
				
			}
			
			
			
			
			if ("mainOrderSn" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->mainOrderSn);
				
			}
			
			
			
			
			if ("orderSnList" == $schemeField){
				
				$needSkip = false;
				
				$this->orderSnList = array();
				$_size1 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem1 = null;
						$input->readString($elem1);
						
						$this->orderSnList[$_size1++] = $elem1;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readListEnd();
				
			}
			
			
			
			
			if ("customerSrc" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->customerSrc);
				
			}
			
			
			
			
			if ("codType" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->codType);
				
			}
			
			
			
			
			if ("codAppAccount" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->codAppAccount);
				
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
		
		
		if($this->mainOrderId !== null) {
			
			$xfer += $output->writeFieldBegin('mainOrderId');
			$xfer += $output->writeI64($this->mainOrderId);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderIdList !== null) {
			
			$xfer += $output->writeFieldBegin('orderIdList');
			
			if (!is_array($this->orderIdList)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeListBegin();
			foreach ($this->orderIdList as $iter0){
				
				$xfer += $output->writeI64($iter0);
				
			}
			
			$output->writeListEnd();
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->mainOrderSn !== null) {
			
			$xfer += $output->writeFieldBegin('mainOrderSn');
			$xfer += $output->writeString($this->mainOrderSn);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderSnList !== null) {
			
			$xfer += $output->writeFieldBegin('orderSnList');
			
			if (!is_array($this->orderSnList)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeListBegin();
			foreach ($this->orderSnList as $iter0){
				
				$xfer += $output->writeString($iter0);
				
			}
			
			$output->writeListEnd();
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->customerSrc !== null) {
			
			$xfer += $output->writeFieldBegin('customerSrc');
			$xfer += $output->writeString($this->customerSrc);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->codType !== null) {
			
			$xfer += $output->writeFieldBegin('codType');
			$xfer += $output->writeString($this->codType);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->codAppAccount !== null) {
			
			$xfer += $output->writeFieldBegin('codAppAccount');
			$xfer += $output->writeString($this->codAppAccount);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}

?>