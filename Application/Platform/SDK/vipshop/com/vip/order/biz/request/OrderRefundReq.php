<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\biz\request;

class OrderRefundReq {
	
	static $_TSPEC;
	public $refundMoney = null;
	public $orderSn = null;
	public $userId = null;
	public $orderCategory = null;
	public $sceneType = null;
	public $billType = null;
	public $refundReason = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'refundMoney'
			),
			2 => array(
			'var' => 'orderSn'
			),
			3 => array(
			'var' => 'userId'
			),
			4 => array(
			'var' => 'orderCategory'
			),
			5 => array(
			'var' => 'sceneType'
			),
			6 => array(
			'var' => 'billType'
			),
			7 => array(
			'var' => 'refundReason'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['refundMoney'])){
				
				$this->refundMoney = $vals['refundMoney'];
			}
			
			
			if (isset($vals['orderSn'])){
				
				$this->orderSn = $vals['orderSn'];
			}
			
			
			if (isset($vals['userId'])){
				
				$this->userId = $vals['userId'];
			}
			
			
			if (isset($vals['orderCategory'])){
				
				$this->orderCategory = $vals['orderCategory'];
			}
			
			
			if (isset($vals['sceneType'])){
				
				$this->sceneType = $vals['sceneType'];
			}
			
			
			if (isset($vals['billType'])){
				
				$this->billType = $vals['billType'];
			}
			
			
			if (isset($vals['refundReason'])){
				
				$this->refundReason = $vals['refundReason'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'OrderRefundReq';
	}
	
	public function read($input){
		
		$input->readStructBegin();
		while(true){
			
			$schemeField = $input->readFieldBegin();
			if ($schemeField == null) break;
			$needSkip = true;
			
			
			if ("refundMoney" == $schemeField){
				
				$needSkip = false;
				
				$this->refundMoney = new \com\vip\order\biz\request\RefundMoneyUnit();
				$this->refundMoney->read($input);
				
			}
			
			
			
			
			if ("orderSn" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->orderSn);
				
			}
			
			
			
			
			if ("userId" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->userId); 
				
			}
			
			
			
			
			if ("orderCategory" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->orderCategory); 
				
			}
			
			
			
			
			if ("sceneType" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->sceneType); 
				
			}
			
			
			
			
			if ("billType" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->billType); 
				
			}
			
			
			
			
			if ("refundReason" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->refundReason);
				
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
		
		if($this->refundMoney !== null) {
			
			$xfer += $output->writeFieldBegin('refundMoney');
			
			if (!is_object($this->refundMoney)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->refundMoney->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderSn !== null) {
			
			$xfer += $output->writeFieldBegin('orderSn');
			$xfer += $output->writeString($this->orderSn);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
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
		
		
		if($this->sceneType !== null) {
			
			$xfer += $output->writeFieldBegin('sceneType');
			$xfer += $output->writeI32($this->sceneType);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->billType !== null) {
			
			$xfer += $output->writeFieldBegin('billType');
			$xfer += $output->writeI32($this->billType);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->refundReason !== null) {
			
			$xfer += $output->writeFieldBegin('refundReason');
			$xfer += $output->writeString($this->refundReason);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}

?>