<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\biz\request;

class GetOrdersBySizeIdReq {
	
	static $_TSPEC;
	public $sizeId = null;
	public $orderStatusSet = null;
	public $orderTypeSet = null;
	public $payTypeSet = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'sizeId'
			),
			2 => array(
			'var' => 'orderStatusSet'
			),
			3 => array(
			'var' => 'orderTypeSet'
			),
			4 => array(
			'var' => 'payTypeSet'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['sizeId'])){
				
				$this->sizeId = $vals['sizeId'];
			}
			
			
			if (isset($vals['orderStatusSet'])){
				
				$this->orderStatusSet = $vals['orderStatusSet'];
			}
			
			
			if (isset($vals['orderTypeSet'])){
				
				$this->orderTypeSet = $vals['orderTypeSet'];
			}
			
			
			if (isset($vals['payTypeSet'])){
				
				$this->payTypeSet = $vals['payTypeSet'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'GetOrdersBySizeIdReq';
	}
	
	public function read($input){
		
		$input->readStructBegin();
		while(true){
			
			$schemeField = $input->readFieldBegin();
			if ($schemeField == null) break;
			$needSkip = true;
			
			
			if ("sizeId" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->sizeId); 
				
			}
			
			
			
			
			if ("orderStatusSet" == $schemeField){
				
				$needSkip = false;
				
				$this->orderStatusSet = array();
				$_size0 = 0;
				$input->readSetBegin();
				while(true){
					
					try{
						
						$elem0 = null;
						$input->readI32($elem0); 
						
						$this->orderStatusSet[$_size0++] = $elem0;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readSetEnd();
				
			}
			
			
			
			
			if ("orderTypeSet" == $schemeField){
				
				$needSkip = false;
				
				$this->orderTypeSet = array();
				$_size1 = 0;
				$input->readSetBegin();
				while(true){
					
					try{
						
						$elem1 = null;
						$input->readI32($elem1); 
						
						$this->orderTypeSet[$_size1++] = $elem1;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readSetEnd();
				
			}
			
			
			
			
			if ("payTypeSet" == $schemeField){
				
				$needSkip = false;
				
				$this->payTypeSet = array();
				$_size2 = 0;
				$input->readSetBegin();
				while(true){
					
					try{
						
						$elem2 = null;
						$input->readI32($elem2); 
						
						$this->payTypeSet[$_size2++] = $elem2;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readSetEnd();
				
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
		
		if($this->sizeId !== null) {
			
			$xfer += $output->writeFieldBegin('sizeId');
			$xfer += $output->writeI32($this->sizeId);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderStatusSet !== null) {
			
			$xfer += $output->writeFieldBegin('orderStatusSet');
			
			if (!is_array($this->orderStatusSet)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeSetBegin();
			foreach ($this->orderStatusSet as $iter0){
				
				$xfer += $output->writeI32($iter0);
				
			}
			
			$output->writeSetEnd();
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderTypeSet !== null) {
			
			$xfer += $output->writeFieldBegin('orderTypeSet');
			
			if (!is_array($this->orderTypeSet)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeSetBegin();
			foreach ($this->orderTypeSet as $iter0){
				
				$xfer += $output->writeI32($iter0);
				
			}
			
			$output->writeSetEnd();
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->payTypeSet !== null) {
			
			$xfer += $output->writeFieldBegin('payTypeSet');
			
			if (!is_array($this->payTypeSet)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeSetBegin();
			foreach ($this->payTypeSet as $iter0){
				
				$xfer += $output->writeI32($iter0);
				
			}
			
			$output->writeSetEnd();
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}

?>