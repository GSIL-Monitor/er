<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\biz\request;

class BatchGetOrderListReq {
	
	static $_TSPEC;
	public $orderTypeList = null;
	public $dbIndex = null;
	public $includeDeletedOrder = null;
	public $orderStatusRange = null;
	public $orderDateRange = null;
	public $orderTimeRange = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'orderTypeList'
			),
			2 => array(
			'var' => 'dbIndex'
			),
			3 => array(
			'var' => 'includeDeletedOrder'
			),
			4 => array(
			'var' => 'orderStatusRange'
			),
			5 => array(
			'var' => 'orderDateRange'
			),
			6 => array(
			'var' => 'orderTimeRange'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['orderTypeList'])){
				
				$this->orderTypeList = $vals['orderTypeList'];
			}
			
			
			if (isset($vals['dbIndex'])){
				
				$this->dbIndex = $vals['dbIndex'];
			}
			
			
			if (isset($vals['includeDeletedOrder'])){
				
				$this->includeDeletedOrder = $vals['includeDeletedOrder'];
			}
			
			
			if (isset($vals['orderStatusRange'])){
				
				$this->orderStatusRange = $vals['orderStatusRange'];
			}
			
			
			if (isset($vals['orderDateRange'])){
				
				$this->orderDateRange = $vals['orderDateRange'];
			}
			
			
			if (isset($vals['orderTimeRange'])){
				
				$this->orderTimeRange = $vals['orderTimeRange'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'BatchGetOrderListReq';
	}
	
	public function read($input){
		
		$input->readStructBegin();
		while(true){
			
			$schemeField = $input->readFieldBegin();
			if ($schemeField == null) break;
			$needSkip = true;
			
			
			if ("orderTypeList" == $schemeField){
				
				$needSkip = false;
				
				$this->orderTypeList = array();
				$_size1 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem1 = null;
						$input->readI32($elem1); 
						
						$this->orderTypeList[$_size1++] = $elem1;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readListEnd();
				
			}
			
			
			
			
			if ("dbIndex" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->dbIndex); 
				
			}
			
			
			
			
			if ("includeDeletedOrder" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->includeDeletedOrder); 
				
			}
			
			
			
			
			if ("orderStatusRange" == $schemeField){
				
				$needSkip = false;
				
				$this->orderStatusRange = new \com\vip\order\common\pojo\order\request\RangeParam();
				$this->orderStatusRange->read($input);
				
			}
			
			
			
			
			if ("orderDateRange" == $schemeField){
				
				$needSkip = false;
				
				$this->orderDateRange = new \com\vip\order\common\pojo\order\request\RangeParam();
				$this->orderDateRange->read($input);
				
			}
			
			
			
			
			if ("orderTimeRange" == $schemeField){
				
				$needSkip = false;
				
				$this->orderTimeRange = new \com\vip\order\common\pojo\order\request\RangeParam();
				$this->orderTimeRange->read($input);
				
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
		
		if($this->orderTypeList !== null) {
			
			$xfer += $output->writeFieldBegin('orderTypeList');
			
			if (!is_array($this->orderTypeList)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeListBegin();
			foreach ($this->orderTypeList as $iter0){
				
				$xfer += $output->writeI32($iter0);
				
			}
			
			$output->writeListEnd();
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->dbIndex !== null) {
			
			$xfer += $output->writeFieldBegin('dbIndex');
			$xfer += $output->writeI32($this->dbIndex);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->includeDeletedOrder !== null) {
			
			$xfer += $output->writeFieldBegin('includeDeletedOrder');
			$xfer += $output->writeI32($this->includeDeletedOrder);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderStatusRange !== null) {
			
			$xfer += $output->writeFieldBegin('orderStatusRange');
			
			if (!is_object($this->orderStatusRange)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->orderStatusRange->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderDateRange !== null) {
			
			$xfer += $output->writeFieldBegin('orderDateRange');
			
			if (!is_object($this->orderDateRange)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->orderDateRange->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderTimeRange !== null) {
			
			$xfer += $output->writeFieldBegin('orderTimeRange');
			
			if (!is_object($this->orderTimeRange)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->orderTimeRange->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}

?>