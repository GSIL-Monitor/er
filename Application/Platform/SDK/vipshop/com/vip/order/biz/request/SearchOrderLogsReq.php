<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\biz\request;

class SearchOrderLogsReq {
	
	static $_TSPEC;
	public $orderList = null;
	public $userNameList = null;
	public $operateTypeRange = null;
	public $dateRange = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'orderList'
			),
			2 => array(
			'var' => 'userNameList'
			),
			5 => array(
			'var' => 'operateTypeRange'
			),
			4 => array(
			'var' => 'dateRange'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['orderList'])){
				
				$this->orderList = $vals['orderList'];
			}
			
			
			if (isset($vals['userNameList'])){
				
				$this->userNameList = $vals['userNameList'];
			}
			
			
			if (isset($vals['operateTypeRange'])){
				
				$this->operateTypeRange = $vals['operateTypeRange'];
			}
			
			
			if (isset($vals['dateRange'])){
				
				$this->dateRange = $vals['dateRange'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'SearchOrderLogsReq';
	}
	
	public function read($input){
		
		$input->readStructBegin();
		while(true){
			
			$schemeField = $input->readFieldBegin();
			if ($schemeField == null) break;
			$needSkip = true;
			
			
			if ("orderList" == $schemeField){
				
				$needSkip = false;
				
				$this->orderList = array();
				$_size0 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem0 = null;
						
						$elem0 = new \com\vip\order\common\pojo\order\vo\OrderVO();
						$elem0->read($input);
						
						$this->orderList[$_size0++] = $elem0;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readListEnd();
				
			}
			
			
			
			
			if ("userNameList" == $schemeField){
				
				$needSkip = false;
				
				$this->userNameList = array();
				$_size1 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem1 = null;
						$input->readString($elem1);
						
						$this->userNameList[$_size1++] = $elem1;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readListEnd();
				
			}
			
			
			
			
			if ("operateTypeRange" == $schemeField){
				
				$needSkip = false;
				
				$this->operateTypeRange = new \com\vip\order\common\pojo\order\request\RangeParam();
				$this->operateTypeRange->read($input);
				
			}
			
			
			
			
			if ("dateRange" == $schemeField){
				
				$needSkip = false;
				
				$this->dateRange = new \com\vip\order\common\pojo\order\request\RangeParam();
				$this->dateRange->read($input);
				
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
		
		if($this->orderList !== null) {
			
			$xfer += $output->writeFieldBegin('orderList');
			
			if (!is_array($this->orderList)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeListBegin();
			foreach ($this->orderList as $iter0){
				
				
				if (!is_object($iter0)) {
					
					throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
				}
				
				$xfer += $iter0->write($output);
				
			}
			
			$output->writeListEnd();
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->userNameList !== null) {
			
			$xfer += $output->writeFieldBegin('userNameList');
			
			if (!is_array($this->userNameList)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeListBegin();
			foreach ($this->userNameList as $iter0){
				
				$xfer += $output->writeString($iter0);
				
			}
			
			$output->writeListEnd();
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->operateTypeRange !== null) {
			
			$xfer += $output->writeFieldBegin('operateTypeRange');
			
			if (!is_object($this->operateTypeRange)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->operateTypeRange->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->dateRange !== null) {
			
			$xfer += $output->writeFieldBegin('dateRange');
			
			if (!is_object($this->dateRange)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->dateRange->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}

?>