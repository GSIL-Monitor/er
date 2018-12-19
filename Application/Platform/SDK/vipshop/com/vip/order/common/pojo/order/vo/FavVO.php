<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\common\pojo\order\vo;

class FavVO {
	
	static $_TSPEC;
	public $activeType = null;
	public $activeField = null;
	public $favDetailList = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'activeType'
			),
			2 => array(
			'var' => 'activeField'
			),
			3 => array(
			'var' => 'favDetailList'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['activeType'])){
				
				$this->activeType = $vals['activeType'];
			}
			
			
			if (isset($vals['activeField'])){
				
				$this->activeField = $vals['activeField'];
			}
			
			
			if (isset($vals['favDetailList'])){
				
				$this->favDetailList = $vals['favDetailList'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'FavVO';
	}
	
	public function read($input){
		
		$input->readStructBegin();
		while(true){
			
			$schemeField = $input->readFieldBegin();
			if ($schemeField == null) break;
			$needSkip = true;
			
			
			if ("activeType" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->activeType); 
				
			}
			
			
			
			
			if ("activeField" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->activeField); 
				
			}
			
			
			
			
			if ("favDetailList" == $schemeField){
				
				$needSkip = false;
				
				$this->favDetailList = array();
				$_size2 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem2 = null;
						
						$elem2 = new \com\vip\order\common\pojo\order\vo\FavDetailVO();
						$elem2->read($input);
						
						$this->favDetailList[$_size2++] = $elem2;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readListEnd();
				
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
		
		if($this->activeType !== null) {
			
			$xfer += $output->writeFieldBegin('activeType');
			$xfer += $output->writeI32($this->activeType);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->activeField !== null) {
			
			$xfer += $output->writeFieldBegin('activeField');
			$xfer += $output->writeI32($this->activeField);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->favDetailList !== null) {
			
			$xfer += $output->writeFieldBegin('favDetailList');
			
			if (!is_array($this->favDetailList)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeListBegin();
			foreach ($this->favDetailList as $iter0){
				
				
				if (!is_object($iter0)) {
					
					throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
				}
				
				$xfer += $iter0->write($output);
				
			}
			
			$output->writeListEnd();
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}

?>