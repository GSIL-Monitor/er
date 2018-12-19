<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\common\pojo\order\vo;

class OrderReturnMoneyVO {
	
	static $_TSPEC;
	public $returnMoney = null;
	public $returnType = null;
	public $returnVirtualMoney = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'returnMoney'
			),
			2 => array(
			'var' => 'returnType'
			),
			3 => array(
			'var' => 'returnVirtualMoney'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['returnMoney'])){
				
				$this->returnMoney = $vals['returnMoney'];
			}
			
			
			if (isset($vals['returnType'])){
				
				$this->returnType = $vals['returnType'];
			}
			
			
			if (isset($vals['returnVirtualMoney'])){
				
				$this->returnVirtualMoney = $vals['returnVirtualMoney'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'OrderReturnMoneyVO';
	}
	
	public function read($input){
		
		$input->readStructBegin();
		while(true){
			
			$schemeField = $input->readFieldBegin();
			if ($schemeField == null) break;
			$needSkip = true;
			
			
			if ("returnMoney" == $schemeField){
				
				$needSkip = false;
				
				$this->returnMoney = new \com\vip\order\common\pojo\order\vo\ReturnMoneyVO();
				$this->returnMoney->read($input);
				
			}
			
			
			
			
			if ("returnType" == $schemeField){
				
				$needSkip = false;
				
				$this->returnType = new \com\vip\order\common\pojo\order\vo\ReturnTypeVO();
				$this->returnType->read($input);
				
			}
			
			
			
			
			if ("returnVirtualMoney" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->returnVirtualMoney);
				
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
		
		if($this->returnMoney !== null) {
			
			$xfer += $output->writeFieldBegin('returnMoney');
			
			if (!is_object($this->returnMoney)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->returnMoney->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->returnType !== null) {
			
			$xfer += $output->writeFieldBegin('returnType');
			
			if (!is_object($this->returnType)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->returnType->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->returnVirtualMoney !== null) {
			
			$xfer += $output->writeFieldBegin('returnVirtualMoney');
			$xfer += $output->writeString($this->returnVirtualMoney);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}

?>