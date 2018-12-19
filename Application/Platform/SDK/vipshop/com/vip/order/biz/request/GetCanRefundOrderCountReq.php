<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\biz\request;

class GetCanRefundOrderCountReq {
	
	static $_TSPEC;
	public $orderSnSet = null;
	public $userIdSet = null;
	public $orderTimeRange = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'orderSnSet'
			),
			2 => array(
			'var' => 'userIdSet'
			),
			3 => array(
			'var' => 'orderTimeRange'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['orderSnSet'])){
				
				$this->orderSnSet = $vals['orderSnSet'];
			}
			
			
			if (isset($vals['userIdSet'])){
				
				$this->userIdSet = $vals['userIdSet'];
			}
			
			
			if (isset($vals['orderTimeRange'])){
				
				$this->orderTimeRange = $vals['orderTimeRange'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'GetCanRefundOrderCountReq';
	}
	
	public function read($input){
		
		$input->readStructBegin();
		while(true){
			
			$schemeField = $input->readFieldBegin();
			if ($schemeField == null) break;
			$needSkip = true;
			
			
			if ("orderSnSet" == $schemeField){
				
				$needSkip = false;
				
				$this->orderSnSet = array();
				$_size0 = 0;
				$input->readSetBegin();
				while(true){
					
					try{
						
						$elem0 = null;
						$input->readString($elem0);
						
						$this->orderSnSet[$_size0++] = $elem0;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readSetEnd();
				
			}
			
			
			
			
			if ("userIdSet" == $schemeField){
				
				$needSkip = false;
				
				$this->userIdSet = array();
				$_size1 = 0;
				$input->readSetBegin();
				while(true){
					
					try{
						
						$elem1 = null;
						$input->readI64($elem1); 
						
						$this->userIdSet[$_size1++] = $elem1;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readSetEnd();
				
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
		
		if($this->orderSnSet !== null) {
			
			$xfer += $output->writeFieldBegin('orderSnSet');
			
			if (!is_array($this->orderSnSet)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeSetBegin();
			foreach ($this->orderSnSet as $iter0){
				
				$xfer += $output->writeString($iter0);
				
			}
			
			$output->writeSetEnd();
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->userIdSet !== null) {
			
			$xfer += $output->writeFieldBegin('userIdSet');
			
			if (!is_array($this->userIdSet)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeSetBegin();
			foreach ($this->userIdSet as $iter0){
				
				$xfer += $output->writeI64($iter0);
				
			}
			
			$output->writeSetEnd();
			
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