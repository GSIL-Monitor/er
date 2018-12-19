<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\biz\request;

class CheckDeliveryFetchExchangeReq {
	
	static $_TSPEC;
	public $orderSn = null;
	public $userId = null;
	public $areaId = null;
	public $addressLevel = null;
	public $merItemNoSet = null;
	public $areaIdStr = null;
	
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
			'var' => 'areaId'
			),
			4 => array(
			'var' => 'addressLevel'
			),
			5 => array(
			'var' => 'merItemNoSet'
			),
			6 => array(
			'var' => 'areaIdStr'
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
			
			
			if (isset($vals['areaId'])){
				
				$this->areaId = $vals['areaId'];
			}
			
			
			if (isset($vals['addressLevel'])){
				
				$this->addressLevel = $vals['addressLevel'];
			}
			
			
			if (isset($vals['merItemNoSet'])){
				
				$this->merItemNoSet = $vals['merItemNoSet'];
			}
			
			
			if (isset($vals['areaIdStr'])){
				
				$this->areaIdStr = $vals['areaIdStr'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'CheckDeliveryFetchExchangeReq';
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
			
			
			
			
			if ("areaId" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->areaId); 
				
			}
			
			
			
			
			if ("addressLevel" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->addressLevel); 
				
			}
			
			
			
			
			if ("merItemNoSet" == $schemeField){
				
				$needSkip = false;
				
				$this->merItemNoSet = array();
				$_size0 = 0;
				$input->readSetBegin();
				while(true){
					
					try{
						
						$elem0 = null;
						$input->readI64($elem0); 
						
						$this->merItemNoSet[$_size0++] = $elem0;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readSetEnd();
				
			}
			
			
			
			
			if ("areaIdStr" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->areaIdStr);
				
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
		
		
		if($this->areaId !== null) {
			
			$xfer += $output->writeFieldBegin('areaId');
			$xfer += $output->writeI64($this->areaId);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->addressLevel !== null) {
			
			$xfer += $output->writeFieldBegin('addressLevel');
			$xfer += $output->writeI32($this->addressLevel);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->merItemNoSet !== null) {
			
			$xfer += $output->writeFieldBegin('merItemNoSet');
			
			if (!is_array($this->merItemNoSet)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeSetBegin();
			foreach ($this->merItemNoSet as $iter0){
				
				$xfer += $output->writeI64($iter0);
				
			}
			
			$output->writeSetEnd();
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->areaIdStr !== null) {
			
			$xfer += $output->writeFieldBegin('areaIdStr');
			$xfer += $output->writeString($this->areaIdStr);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}

?>