<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\biz\request;

class BatchGetOrderActiveDetailReq {
	
	static $_TSPEC;
	public $orderSnAndUserIdList = null;
	public $activeTypeRange = null;
	public $activeFieldRange = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'orderSnAndUserIdList'
			),
			2 => array(
			'var' => 'activeTypeRange'
			),
			3 => array(
			'var' => 'activeFieldRange'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['orderSnAndUserIdList'])){
				
				$this->orderSnAndUserIdList = $vals['orderSnAndUserIdList'];
			}
			
			
			if (isset($vals['activeTypeRange'])){
				
				$this->activeTypeRange = $vals['activeTypeRange'];
			}
			
			
			if (isset($vals['activeFieldRange'])){
				
				$this->activeFieldRange = $vals['activeFieldRange'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'BatchGetOrderActiveDetailReq';
	}
	
	public function read($input){
		
		$input->readStructBegin();
		while(true){
			
			$schemeField = $input->readFieldBegin();
			if ($schemeField == null) break;
			$needSkip = true;
			
			
			if ("orderSnAndUserIdList" == $schemeField){
				
				$needSkip = false;
				
				$this->orderSnAndUserIdList = array();
				$_size1 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem1 = null;
						
						$elem1 = new \com\vip\order\common\pojo\order\vo\OrderSnAndIdVO();
						$elem1->read($input);
						
						$this->orderSnAndUserIdList[$_size1++] = $elem1;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readListEnd();
				
			}
			
			
			
			
			if ("activeTypeRange" == $schemeField){
				
				$needSkip = false;
				
				$this->activeTypeRange = new \com\vip\order\common\pojo\order\request\RangeParam();
				$this->activeTypeRange->read($input);
				
			}
			
			
			
			
			if ("activeFieldRange" == $schemeField){
				
				$needSkip = false;
				
				$this->activeFieldRange = new \com\vip\order\common\pojo\order\request\RangeParam();
				$this->activeFieldRange->read($input);
				
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
		
		if($this->orderSnAndUserIdList !== null) {
			
			$xfer += $output->writeFieldBegin('orderSnAndUserIdList');
			
			if (!is_array($this->orderSnAndUserIdList)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeListBegin();
			foreach ($this->orderSnAndUserIdList as $iter0){
				
				
				if (!is_object($iter0)) {
					
					throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
				}
				
				$xfer += $iter0->write($output);
				
			}
			
			$output->writeListEnd();
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->activeTypeRange !== null) {
			
			$xfer += $output->writeFieldBegin('activeTypeRange');
			
			if (!is_object($this->activeTypeRange)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->activeTypeRange->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->activeFieldRange !== null) {
			
			$xfer += $output->writeFieldBegin('activeFieldRange');
			
			if (!is_object($this->activeFieldRange)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->activeFieldRange->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}

?>