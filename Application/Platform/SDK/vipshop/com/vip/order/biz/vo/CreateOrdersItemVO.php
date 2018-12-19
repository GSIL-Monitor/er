<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\biz\vo;

class CreateOrdersItemVO {
	
	static $_TSPEC;
	public $orderSn = null;
	public $orderId = null;
	public $orderCategory = null;
	public $orderGroupSn = null;
	public $retCode = null;
	public $retMessage = null;
	public $orderCodeList = null;
	public $indexKey = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'orderSn'
			),
			2 => array(
			'var' => 'orderId'
			),
			3 => array(
			'var' => 'orderCategory'
			),
			4 => array(
			'var' => 'orderGroupSn'
			),
			5 => array(
			'var' => 'retCode'
			),
			6 => array(
			'var' => 'retMessage'
			),
			7 => array(
			'var' => 'orderCodeList'
			),
			8 => array(
			'var' => 'indexKey'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['orderSn'])){
				
				$this->orderSn = $vals['orderSn'];
			}
			
			
			if (isset($vals['orderId'])){
				
				$this->orderId = $vals['orderId'];
			}
			
			
			if (isset($vals['orderCategory'])){
				
				$this->orderCategory = $vals['orderCategory'];
			}
			
			
			if (isset($vals['orderGroupSn'])){
				
				$this->orderGroupSn = $vals['orderGroupSn'];
			}
			
			
			if (isset($vals['retCode'])){
				
				$this->retCode = $vals['retCode'];
			}
			
			
			if (isset($vals['retMessage'])){
				
				$this->retMessage = $vals['retMessage'];
			}
			
			
			if (isset($vals['orderCodeList'])){
				
				$this->orderCodeList = $vals['orderCodeList'];
			}
			
			
			if (isset($vals['indexKey'])){
				
				$this->indexKey = $vals['indexKey'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'CreateOrdersItemVO';
	}
	
	public function read($input){
		
		$input->readStructBegin();
		while(true){
			
			$schemeField = $input->readFieldBegin();
			if ($schemeField == null) break;
			$needSkip = true;
			
			
			if ("orderSn" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->orderSn);
				
			}
			
			
			
			
			if ("orderId" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->orderId); 
				
			}
			
			
			
			
			if ("orderCategory" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->orderCategory); 
				
			}
			
			
			
			
			if ("orderGroupSn" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->orderGroupSn);
				
			}
			
			
			
			
			if ("retCode" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->retCode);
				
			}
			
			
			
			
			if ("retMessage" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->retMessage);
				
			}
			
			
			
			
			if ("orderCodeList" == $schemeField){
				
				$needSkip = false;
				
				$this->orderCodeList = array();
				$_size0 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem0 = null;
						
						$elem0 = new \com\vip\order\biz\vo\OrderCodeVO();
						$elem0->read($input);
						
						$this->orderCodeList[$_size0++] = $elem0;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readListEnd();
				
			}
			
			
			
			
			if ("indexKey" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->indexKey);
				
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
		
		if($this->orderSn !== null) {
			
			$xfer += $output->writeFieldBegin('orderSn');
			$xfer += $output->writeString($this->orderSn);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderId !== null) {
			
			$xfer += $output->writeFieldBegin('orderId');
			$xfer += $output->writeI64($this->orderId);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderCategory !== null) {
			
			$xfer += $output->writeFieldBegin('orderCategory');
			$xfer += $output->writeI32($this->orderCategory);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderGroupSn !== null) {
			
			$xfer += $output->writeFieldBegin('orderGroupSn');
			$xfer += $output->writeString($this->orderGroupSn);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->retCode !== null) {
			
			$xfer += $output->writeFieldBegin('retCode');
			$xfer += $output->writeString($this->retCode);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->retMessage !== null) {
			
			$xfer += $output->writeFieldBegin('retMessage');
			$xfer += $output->writeString($this->retMessage);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderCodeList !== null) {
			
			$xfer += $output->writeFieldBegin('orderCodeList');
			
			if (!is_array($this->orderCodeList)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeListBegin();
			foreach ($this->orderCodeList as $iter0){
				
				
				if (!is_object($iter0)) {
					
					throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
				}
				
				$xfer += $iter0->write($output);
				
			}
			
			$output->writeListEnd();
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->indexKey !== null) {
			
			$xfer += $output->writeFieldBegin('indexKey');
			$xfer += $output->writeString($this->indexKey);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}

?>