<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\biz\request;

class ReceiveAddressInfo {
	
	static $_TSPEC;
	public $addressId = null;
	public $addressType = null;
	public $transportDays = null;
	public $transportTimeType = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'addressId'
			),
			2 => array(
			'var' => 'addressType'
			),
			3 => array(
			'var' => 'transportDays'
			),
			4 => array(
			'var' => 'transportTimeType'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['addressId'])){
				
				$this->addressId = $vals['addressId'];
			}
			
			
			if (isset($vals['addressType'])){
				
				$this->addressType = $vals['addressType'];
			}
			
			
			if (isset($vals['transportDays'])){
				
				$this->transportDays = $vals['transportDays'];
			}
			
			
			if (isset($vals['transportTimeType'])){
				
				$this->transportTimeType = $vals['transportTimeType'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'ReceiveAddressInfo';
	}
	
	public function read($input){
		
		$input->readStructBegin();
		while(true){
			
			$schemeField = $input->readFieldBegin();
			if ($schemeField == null) break;
			$needSkip = true;
			
			
			if ("addressId" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->addressId); 
				
			}
			
			
			
			
			if ("addressType" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->addressType); 
				
			}
			
			
			
			
			if ("transportDays" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->transportDays); 
				
			}
			
			
			
			
			if ("transportTimeType" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->transportTimeType); 
				
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
		
		if($this->addressId !== null) {
			
			$xfer += $output->writeFieldBegin('addressId');
			$xfer += $output->writeI64($this->addressId);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->addressType !== null) {
			
			$xfer += $output->writeFieldBegin('addressType');
			$xfer += $output->writeI32($this->addressType);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->transportDays !== null) {
			
			$xfer += $output->writeFieldBegin('transportDays');
			$xfer += $output->writeI32($this->transportDays);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->transportTimeType !== null) {
			
			$xfer += $output->writeFieldBegin('transportTimeType');
			$xfer += $output->writeI32($this->transportTimeType);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}

?>