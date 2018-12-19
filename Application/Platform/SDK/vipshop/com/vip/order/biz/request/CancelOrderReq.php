<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\biz\request;

class CancelOrderReq {
	
	static $_TSPEC;
	public $userId = null;
	public $orderCategory = null;
	public $orderId = null;
	public $orderSn = null;
	public $reasonChoice = null;
	public $reasonRemark = null;
	public $isPreview = null;
	public $appVersion = null;
	
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
			'var' => 'orderId'
			),
			4 => array(
			'var' => 'orderSn'
			),
			5 => array(
			'var' => 'reasonChoice'
			),
			6 => array(
			'var' => 'reasonRemark'
			),
			7 => array(
			'var' => 'isPreview'
			),
			8 => array(
			'var' => 'appVersion'
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
			
			
			if (isset($vals['orderId'])){
				
				$this->orderId = $vals['orderId'];
			}
			
			
			if (isset($vals['orderSn'])){
				
				$this->orderSn = $vals['orderSn'];
			}
			
			
			if (isset($vals['reasonChoice'])){
				
				$this->reasonChoice = $vals['reasonChoice'];
			}
			
			
			if (isset($vals['reasonRemark'])){
				
				$this->reasonRemark = $vals['reasonRemark'];
			}
			
			
			if (isset($vals['isPreview'])){
				
				$this->isPreview = $vals['isPreview'];
			}
			
			
			if (isset($vals['appVersion'])){
				
				$this->appVersion = $vals['appVersion'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'CancelOrderReq';
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
				$input->readByte($this->orderCategory); 
				
			}
			
			
			
			
			if ("orderId" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->orderId); 
				
			}
			
			
			
			
			if ("orderSn" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->orderSn);
				
			}
			
			
			
			
			if ("reasonChoice" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->reasonChoice); 
				
			}
			
			
			
			
			if ("reasonRemark" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->reasonRemark);
				
			}
			
			
			
			
			if ("isPreview" == $schemeField){
				
				$needSkip = false;
				$input->readBool($this->isPreview);
				
			}
			
			
			
			
			if ("appVersion" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->appVersion);
				
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
			$xfer += $output->writeByte($this->orderCategory);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderId !== null) {
			
			$xfer += $output->writeFieldBegin('orderId');
			$xfer += $output->writeI64($this->orderId);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderSn !== null) {
			
			$xfer += $output->writeFieldBegin('orderSn');
			$xfer += $output->writeString($this->orderSn);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->reasonChoice !== null) {
			
			$xfer += $output->writeFieldBegin('reasonChoice');
			$xfer += $output->writeI32($this->reasonChoice);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->reasonRemark !== null) {
			
			$xfer += $output->writeFieldBegin('reasonRemark');
			$xfer += $output->writeString($this->reasonRemark);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->isPreview !== null) {
			
			$xfer += $output->writeFieldBegin('isPreview');
			$xfer += $output->writeBool($this->isPreview);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->appVersion !== null) {
			
			$xfer += $output->writeFieldBegin('appVersion');
			$xfer += $output->writeString($this->appVersion);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}

?>