<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\biz\vo;

class OrderSplitVO {
	
	static $_TSPEC;
	public $orderSn = null;
	public $userId = null;
	public $walletPaySn = null;
	public $pointPaySn = null;
	public $favourablePaySn = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'orderSn'
			),
			2 => array(
			'var' => 'userId'
			),
			3 => array(
			'var' => 'walletPaySn'
			),
			4 => array(
			'var' => 'pointPaySn'
			),
			5 => array(
			'var' => 'favourablePaySn'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['orderSn'])){
				
				$this->orderSn = $vals['orderSn'];
			}
			
			
			if (isset($vals['userId'])){
				
				$this->userId = $vals['userId'];
			}
			
			
			if (isset($vals['walletPaySn'])){
				
				$this->walletPaySn = $vals['walletPaySn'];
			}
			
			
			if (isset($vals['pointPaySn'])){
				
				$this->pointPaySn = $vals['pointPaySn'];
			}
			
			
			if (isset($vals['favourablePaySn'])){
				
				$this->favourablePaySn = $vals['favourablePaySn'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'OrderSplitVO';
	}
	
	public function read($input){
		
		$input->readStructBegin();
		while(true){
			
			$schemeField = $input->readFieldBegin();
			if ($schemeField == null) break;
			$needSkip = true;
			
			
			if ("orderSn" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->orderSn);
				
			}
			
			
			
			
			if ("userId" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->userId); 
				
			}
			
			
			
			
			if ("walletPaySn" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->walletPaySn);
				
			}
			
			
			
			
			if ("pointPaySn" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->pointPaySn);
				
			}
			
			
			
			
			if ("favourablePaySn" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->favourablePaySn);
				
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
		
		
		if($this->walletPaySn !== null) {
			
			$xfer += $output->writeFieldBegin('walletPaySn');
			$xfer += $output->writeString($this->walletPaySn);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->pointPaySn !== null) {
			
			$xfer += $output->writeFieldBegin('pointPaySn');
			$xfer += $output->writeString($this->pointPaySn);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->favourablePaySn !== null) {
			
			$xfer += $output->writeFieldBegin('favourablePaySn');
			$xfer += $output->writeString($this->favourablePaySn);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}

?>