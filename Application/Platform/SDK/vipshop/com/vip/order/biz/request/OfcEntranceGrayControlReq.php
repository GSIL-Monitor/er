<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\biz\request;

class OfcEntranceGrayControlReq {
	
	static $_TSPEC;
	public $orderSnAndIdList = null;
	public $orderCategory = null;
	public $snType = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'orderSnAndIdList'
			),
			2 => array(
			'var' => 'orderCategory'
			),
			3 => array(
			'var' => 'snType'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['orderSnAndIdList'])){
				
				$this->orderSnAndIdList = $vals['orderSnAndIdList'];
			}
			
			
			if (isset($vals['orderCategory'])){
				
				$this->orderCategory = $vals['orderCategory'];
			}
			
			
			if (isset($vals['snType'])){
				
				$this->snType = $vals['snType'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'OfcEntranceGrayControlReq';
	}
	
	public function read($input){
		
		$input->readStructBegin();
		while(true){
			
			$schemeField = $input->readFieldBegin();
			if ($schemeField == null) break;
			$needSkip = true;
			
			
			if ("orderSnAndIdList" == $schemeField){
				
				$needSkip = false;
				
				$this->orderSnAndIdList = array();
				$_size1 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem1 = null;
						
						$elem1 = new \com\vip\order\common\pojo\order\vo\OrderSnAndIdVO();
						$elem1->read($input);
						
						$this->orderSnAndIdList[$_size1++] = $elem1;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readListEnd();
				
			}
			
			
			
			
			if ("orderCategory" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->orderCategory); 
				
			}
			
			
			
			
			if ("snType" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->snType);
				
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
		
		if($this->orderSnAndIdList !== null) {
			
			$xfer += $output->writeFieldBegin('orderSnAndIdList');
			
			if (!is_array($this->orderSnAndIdList)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeListBegin();
			foreach ($this->orderSnAndIdList as $iter0){
				
				
				if (!is_object($iter0)) {
					
					throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
				}
				
				$xfer += $iter0->write($output);
				
			}
			
			$output->writeListEnd();
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderCategory !== null) {
			
			$xfer += $output->writeFieldBegin('orderCategory');
			$xfer += $output->writeI32($this->orderCategory);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->snType !== null) {
			
			$xfer += $output->writeFieldBegin('snType');
			$xfer += $output->writeString($this->snType);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}

?>