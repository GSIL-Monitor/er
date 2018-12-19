<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\biz\response;

class GetMergeOrderResp {
	
	static $_TSPEC;
	public $result = null;
	public $mainOrder = null;
	public $canMergeOrders = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'result'
			),
			2 => array(
			'var' => 'mainOrder'
			),
			3 => array(
			'var' => 'canMergeOrders'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['result'])){
				
				$this->result = $vals['result'];
			}
			
			
			if (isset($vals['mainOrder'])){
				
				$this->mainOrder = $vals['mainOrder'];
			}
			
			
			if (isset($vals['canMergeOrders'])){
				
				$this->canMergeOrders = $vals['canMergeOrders'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'GetMergeOrderResp';
	}
	
	public function read($input){
		
		$input->readStructBegin();
		while(true){
			
			$schemeField = $input->readFieldBegin();
			if ($schemeField == null) break;
			$needSkip = true;
			
			
			if ("result" == $schemeField){
				
				$needSkip = false;
				
				$this->result = new \com\vip\order\common\pojo\order\result\Result();
				$this->result->read($input);
				
			}
			
			
			
			
			if ("mainOrder" == $schemeField){
				
				$needSkip = false;
				
				$this->mainOrder = new \com\vip\order\biz\response\MergeOrderItem();
				$this->mainOrder->read($input);
				
			}
			
			
			
			
			if ("canMergeOrders" == $schemeField){
				
				$needSkip = false;
				
				$this->canMergeOrders = array();
				$_size0 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem0 = null;
						
						$elem0 = new \com\vip\order\biz\response\MergeOrderItem();
						$elem0->read($input);
						
						$this->canMergeOrders[$_size0++] = $elem0;
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
		
		if($this->result !== null) {
			
			$xfer += $output->writeFieldBegin('result');
			
			if (!is_object($this->result)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->result->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->mainOrder !== null) {
			
			$xfer += $output->writeFieldBegin('mainOrder');
			
			if (!is_object($this->mainOrder)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->mainOrder->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->canMergeOrders !== null) {
			
			$xfer += $output->writeFieldBegin('canMergeOrders');
			
			if (!is_array($this->canMergeOrders)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeListBegin();
			foreach ($this->canMergeOrders as $iter0){
				
				
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