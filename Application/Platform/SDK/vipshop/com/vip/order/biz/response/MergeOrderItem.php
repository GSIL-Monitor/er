<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\biz\response;

class MergeOrderItem {
	
	static $_TSPEC;
	public $order = null;
	public $orderReceiveAddr = null;
	public $orderPayAndDiscount = null;
	public $areaFlagName = null;
	public $areaFlag = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'order'
			),
			2 => array(
			'var' => 'orderReceiveAddr'
			),
			3 => array(
			'var' => 'orderPayAndDiscount'
			),
			4 => array(
			'var' => 'areaFlagName'
			),
			5 => array(
			'var' => 'areaFlag'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['order'])){
				
				$this->order = $vals['order'];
			}
			
			
			if (isset($vals['orderReceiveAddr'])){
				
				$this->orderReceiveAddr = $vals['orderReceiveAddr'];
			}
			
			
			if (isset($vals['orderPayAndDiscount'])){
				
				$this->orderPayAndDiscount = $vals['orderPayAndDiscount'];
			}
			
			
			if (isset($vals['areaFlagName'])){
				
				$this->areaFlagName = $vals['areaFlagName'];
			}
			
			
			if (isset($vals['areaFlag'])){
				
				$this->areaFlag = $vals['areaFlag'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'MergeOrderItem';
	}
	
	public function read($input){
		
		$input->readStructBegin();
		while(true){
			
			$schemeField = $input->readFieldBegin();
			if ($schemeField == null) break;
			$needSkip = true;
			
			
			if ("order" == $schemeField){
				
				$needSkip = false;
				
				$this->order = new \com\vip\order\common\pojo\order\vo\OrderVO();
				$this->order->read($input);
				
			}
			
			
			
			
			if ("orderReceiveAddr" == $schemeField){
				
				$needSkip = false;
				
				$this->orderReceiveAddr = new \com\vip\order\common\pojo\order\vo\OrderReceiveAddrVO();
				$this->orderReceiveAddr->read($input);
				
			}
			
			
			
			
			if ("orderPayAndDiscount" == $schemeField){
				
				$needSkip = false;
				
				$this->orderPayAndDiscount = new \com\vip\order\common\pojo\order\vo\OrderPayAndDiscountVO();
				$this->orderPayAndDiscount->read($input);
				
			}
			
			
			
			
			if ("areaFlagName" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->areaFlagName);
				
			}
			
			
			
			
			if ("areaFlag" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->areaFlag); 
				
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
		
		if($this->order !== null) {
			
			$xfer += $output->writeFieldBegin('order');
			
			if (!is_object($this->order)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->order->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderReceiveAddr !== null) {
			
			$xfer += $output->writeFieldBegin('orderReceiveAddr');
			
			if (!is_object($this->orderReceiveAddr)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->orderReceiveAddr->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderPayAndDiscount !== null) {
			
			$xfer += $output->writeFieldBegin('orderPayAndDiscount');
			
			if (!is_object($this->orderPayAndDiscount)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->orderPayAndDiscount->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->areaFlagName !== null) {
			
			$xfer += $output->writeFieldBegin('areaFlagName');
			$xfer += $output->writeString($this->areaFlagName);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->areaFlag !== null) {
			
			$xfer += $output->writeFieldBegin('areaFlag');
			$xfer += $output->writeI32($this->areaFlag);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}

?>