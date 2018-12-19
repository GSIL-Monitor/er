<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\biz\vo;

class VerifyStockAndGetPayableFlagVO {
	
	static $_TSPEC;
	public $payResult = null;
	public $orderInfoVo = null;
	public $takeMap = null;
	public $noTakeMap = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'payResult'
			),
			2 => array(
			'var' => 'orderInfoVo'
			),
			3 => array(
			'var' => 'takeMap'
			),
			4 => array(
			'var' => 'noTakeMap'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['payResult'])){
				
				$this->payResult = $vals['payResult'];
			}
			
			
			if (isset($vals['orderInfoVo'])){
				
				$this->orderInfoVo = $vals['orderInfoVo'];
			}
			
			
			if (isset($vals['takeMap'])){
				
				$this->takeMap = $vals['takeMap'];
			}
			
			
			if (isset($vals['noTakeMap'])){
				
				$this->noTakeMap = $vals['noTakeMap'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'VerifyStockAndGetPayableFlagVO';
	}
	
	public function read($input){
		
		$input->readStructBegin();
		while(true){
			
			$schemeField = $input->readFieldBegin();
			if ($schemeField == null) break;
			$needSkip = true;
			
			
			if ("payResult" == $schemeField){
				
				$needSkip = false;
				$input->readBool($this->payResult);
				
			}
			
			
			
			
			if ("orderInfoVo" == $schemeField){
				
				$needSkip = false;
				
				$this->orderInfoVo = new \com\vip\order\common\pojo\order\vo\OrderInfoVO();
				$this->orderInfoVo->read($input);
				
			}
			
			
			
			
			if ("takeMap" == $schemeField){
				
				$needSkip = false;
				
				$this->takeMap = array();
				$input->readMapBegin();
				while(true){
					
					try{
						
						$key0 = 0;
						$input->readI32($key0); 
						
						$val0 = 0;
						$input->readI32($val0); 
						
						$this->takeMap[$key0] = $val0;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readMapEnd();
				
			}
			
			
			
			
			if ("noTakeMap" == $schemeField){
				
				$needSkip = false;
				
				$this->noTakeMap = array();
				$input->readMapBegin();
				while(true){
					
					try{
						
						$key1 = 0;
						$input->readI32($key1); 
						
						$val1 = 0;
						$input->readI32($val1); 
						
						$this->noTakeMap[$key1] = $val1;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readMapEnd();
				
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
		
		if($this->payResult !== null) {
			
			$xfer += $output->writeFieldBegin('payResult');
			$xfer += $output->writeBool($this->payResult);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderInfoVo !== null) {
			
			$xfer += $output->writeFieldBegin('orderInfoVo');
			
			if (!is_object($this->orderInfoVo)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->orderInfoVo->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->takeMap !== null) {
			
			$xfer += $output->writeFieldBegin('takeMap');
			
			if (!is_array($this->takeMap)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeMapBegin();
			foreach ($this->takeMap as $kiter0 => $viter0){
				
				$xfer += $output->writeI32($kiter0);
				
				$xfer += $output->writeI32($viter0);
				
			}
			
			$output->writeMapEnd();
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->noTakeMap !== null) {
			
			$xfer += $output->writeFieldBegin('noTakeMap');
			
			if (!is_array($this->noTakeMap)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeMapBegin();
			foreach ($this->noTakeMap as $kiter0 => $viter0){
				
				$xfer += $output->writeI32($kiter0);
				
				$xfer += $output->writeI32($viter0);
				
			}
			
			$output->writeMapEnd();
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}

?>