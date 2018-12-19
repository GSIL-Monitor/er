<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\biz\request;

class CancelOrderFixDataReq {
	
	static $_TSPEC;
	public $operation = null;
	public $orderSn = null;
	public $goodsStatus = null;
	public $refundType = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'operation'
			),
			2 => array(
			'var' => 'orderSn'
			),
			3 => array(
			'var' => 'goodsStatus'
			),
			4 => array(
			'var' => 'refundType'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['operation'])){
				
				$this->operation = $vals['operation'];
			}
			
			
			if (isset($vals['orderSn'])){
				
				$this->orderSn = $vals['orderSn'];
			}
			
			
			if (isset($vals['goodsStatus'])){
				
				$this->goodsStatus = $vals['goodsStatus'];
			}
			
			
			if (isset($vals['refundType'])){
				
				$this->refundType = $vals['refundType'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'CancelOrderFixDataReq';
	}
	
	public function read($input){
		
		$input->readStructBegin();
		while(true){
			
			$schemeField = $input->readFieldBegin();
			if ($schemeField == null) break;
			$needSkip = true;
			
			
			if ("operation" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->operation);
				
			}
			
			
			
			
			if ("orderSn" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->orderSn);
				
			}
			
			
			
			
			if ("goodsStatus" == $schemeField){
				
				$needSkip = false;
				$input->readBool($this->goodsStatus);
				
			}
			
			
			
			
			if ("refundType" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->refundType); 
				
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
		
		if($this->operation !== null) {
			
			$xfer += $output->writeFieldBegin('operation');
			$xfer += $output->writeString($this->operation);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderSn !== null) {
			
			$xfer += $output->writeFieldBegin('orderSn');
			$xfer += $output->writeString($this->orderSn);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->goodsStatus !== null) {
			
			$xfer += $output->writeFieldBegin('goodsStatus');
			$xfer += $output->writeBool($this->goodsStatus);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->refundType !== null) {
			
			$xfer += $output->writeFieldBegin('refundType');
			$xfer += $output->writeI32($this->refundType);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}

?>