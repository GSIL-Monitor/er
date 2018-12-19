<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\biz\request;

class GetUserDeliveryAddressReq {
	
	static $_TSPEC;
	public $userId = null;
	public $statusRange = null;
	public $orderDateRange = null;
	public $orderTimeRange = null;
	public $orderLimit = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'userId'
			),
			2 => array(
			'var' => 'statusRange'
			),
			3 => array(
			'var' => 'orderDateRange'
			),
			4 => array(
			'var' => 'orderTimeRange'
			),
			5 => array(
			'var' => 'orderLimit'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['userId'])){
				
				$this->userId = $vals['userId'];
			}
			
			
			if (isset($vals['statusRange'])){
				
				$this->statusRange = $vals['statusRange'];
			}
			
			
			if (isset($vals['orderDateRange'])){
				
				$this->orderDateRange = $vals['orderDateRange'];
			}
			
			
			if (isset($vals['orderTimeRange'])){
				
				$this->orderTimeRange = $vals['orderTimeRange'];
			}
			
			
			if (isset($vals['orderLimit'])){
				
				$this->orderLimit = $vals['orderLimit'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'GetUserDeliveryAddressReq';
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
			
			
			
			
			if ("statusRange" == $schemeField){
				
				$needSkip = false;
				
				$this->statusRange = new \com\vip\order\common\pojo\order\request\RangeParam();
				$this->statusRange->read($input);
				
			}
			
			
			
			
			if ("orderDateRange" == $schemeField){
				
				$needSkip = false;
				
				$this->orderDateRange = new \com\vip\order\common\pojo\order\request\RangeParam();
				$this->orderDateRange->read($input);
				
			}
			
			
			
			
			if ("orderTimeRange" == $schemeField){
				
				$needSkip = false;
				
				$this->orderTimeRange = new \com\vip\order\common\pojo\order\request\RangeParam();
				$this->orderTimeRange->read($input);
				
			}
			
			
			
			
			if ("orderLimit" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->orderLimit); 
				
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
		
		
		if($this->statusRange !== null) {
			
			$xfer += $output->writeFieldBegin('statusRange');
			
			if (!is_object($this->statusRange)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->statusRange->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderDateRange !== null) {
			
			$xfer += $output->writeFieldBegin('orderDateRange');
			
			if (!is_object($this->orderDateRange)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->orderDateRange->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderTimeRange !== null) {
			
			$xfer += $output->writeFieldBegin('orderTimeRange');
			
			if (!is_object($this->orderTimeRange)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->orderTimeRange->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderLimit !== null) {
			
			$xfer += $output->writeFieldBegin('orderLimit');
			$xfer += $output->writeI32($this->orderLimit);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}

?>