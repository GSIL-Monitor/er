<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\common\pojo\order\vo;

class TransportVO {
	
	static $_TSPEC;
	public $transportNumber = null;
	public $transportOperatorLogList = null;
	public $isCod = null;
	public $transportName = null;
	public $transportId = null;
	public $transportTime = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'transportNumber'
			),
			2 => array(
			'var' => 'transportOperatorLogList'
			),
			3 => array(
			'var' => 'isCod'
			),
			4 => array(
			'var' => 'transportName'
			),
			5 => array(
			'var' => 'transportId'
			),
			6 => array(
			'var' => 'transportTime'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['transportNumber'])){
				
				$this->transportNumber = $vals['transportNumber'];
			}
			
			
			if (isset($vals['transportOperatorLogList'])){
				
				$this->transportOperatorLogList = $vals['transportOperatorLogList'];
			}
			
			
			if (isset($vals['isCod'])){
				
				$this->isCod = $vals['isCod'];
			}
			
			
			if (isset($vals['transportName'])){
				
				$this->transportName = $vals['transportName'];
			}
			
			
			if (isset($vals['transportId'])){
				
				$this->transportId = $vals['transportId'];
			}
			
			
			if (isset($vals['transportTime'])){
				
				$this->transportTime = $vals['transportTime'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'TransportVO';
	}
	
	public function read($input){
		
		$input->readStructBegin();
		while(true){
			
			$schemeField = $input->readFieldBegin();
			if ($schemeField == null) break;
			$needSkip = true;
			
			
			if ("transportNumber" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->transportNumber);
				
			}
			
			
			
			
			if ("transportOperatorLogList" == $schemeField){
				
				$needSkip = false;
				
				$this->transportOperatorLogList = array();
				$_size0 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem0 = null;
						
						$elem0 = new \com\vip\order\common\pojo\order\vo\TransportOperateLogVO();
						$elem0->read($input);
						
						$this->transportOperatorLogList[$_size0++] = $elem0;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readListEnd();
				
			}
			
			
			
			
			if ("isCod" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->isCod); 
				
			}
			
			
			
			
			if ("transportName" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->transportName);
				
			}
			
			
			
			
			if ("transportId" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->transportId);
				
			}
			
			
			
			
			if ("transportTime" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->transportTime); 
				
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
		
		if($this->transportNumber !== null) {
			
			$xfer += $output->writeFieldBegin('transportNumber');
			$xfer += $output->writeString($this->transportNumber);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->transportOperatorLogList !== null) {
			
			$xfer += $output->writeFieldBegin('transportOperatorLogList');
			
			if (!is_array($this->transportOperatorLogList)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeListBegin();
			foreach ($this->transportOperatorLogList as $iter0){
				
				
				if (!is_object($iter0)) {
					
					throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
				}
				
				$xfer += $iter0->write($output);
				
			}
			
			$output->writeListEnd();
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->isCod !== null) {
			
			$xfer += $output->writeFieldBegin('isCod');
			$xfer += $output->writeI32($this->isCod);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->transportName !== null) {
			
			$xfer += $output->writeFieldBegin('transportName');
			$xfer += $output->writeString($this->transportName);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->transportId !== null) {
			
			$xfer += $output->writeFieldBegin('transportId');
			$xfer += $output->writeString($this->transportId);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->transportTime !== null) {
			
			$xfer += $output->writeFieldBegin('transportTime');
			$xfer += $output->writeI64($this->transportTime);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}

?>