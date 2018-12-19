<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\common\pojo\order\vo;

class OrderAfterSaleOpTypeVO {
	
	static $_TSPEC;
	public $opFlag = null;
	public $reasonCode = null;
	public $reason = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'opFlag'
			),
			2 => array(
			'var' => 'reasonCode'
			),
			3 => array(
			'var' => 'reason'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['opFlag'])){
				
				$this->opFlag = $vals['opFlag'];
			}
			
			
			if (isset($vals['reasonCode'])){
				
				$this->reasonCode = $vals['reasonCode'];
			}
			
			
			if (isset($vals['reason'])){
				
				$this->reason = $vals['reason'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'OrderAfterSaleOpTypeVO';
	}
	
	public function read($input){
		
		$input->readStructBegin();
		while(true){
			
			$schemeField = $input->readFieldBegin();
			if ($schemeField == null) break;
			$needSkip = true;
			
			
			if ("opFlag" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->opFlag); 
				
			}
			
			
			
			
			if ("reasonCode" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->reasonCode);
				
			}
			
			
			
			
			if ("reason" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->reason);
				
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
		
		if($this->opFlag !== null) {
			
			$xfer += $output->writeFieldBegin('opFlag');
			$xfer += $output->writeI32($this->opFlag);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->reasonCode !== null) {
			
			$xfer += $output->writeFieldBegin('reasonCode');
			$xfer += $output->writeString($this->reasonCode);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->reason !== null) {
			
			$xfer += $output->writeFieldBegin('reason');
			$xfer += $output->writeString($this->reason);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}

?>