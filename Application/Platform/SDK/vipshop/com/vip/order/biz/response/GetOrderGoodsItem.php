<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\biz\response;

class GetOrderGoodsItem {
	
	static $_TSPEC;
	public $order = null;
	public $orderReceiveAddr = null;
	public $orderPayAndDiscount = null;
	public $orderGoods = null;
	public $orderDetailsSuppInfo = null;
	
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
			'var' => 'orderGoods'
			),
			5 => array(
			'var' => 'orderDetailsSuppInfo'
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
			
			
			if (isset($vals['orderGoods'])){
				
				$this->orderGoods = $vals['orderGoods'];
			}
			
			
			if (isset($vals['orderDetailsSuppInfo'])){
				
				$this->orderDetailsSuppInfo = $vals['orderDetailsSuppInfo'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'GetOrderGoodsItem';
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
			
			
			
			
			if ("orderGoods" == $schemeField){
				
				$needSkip = false;
				
				$this->orderGoods = new \com\vip\order\common\pojo\order\vo\OrderGoodsVO();
				$this->orderGoods->read($input);
				
			}
			
			
			
			
			if ("orderDetailsSuppInfo" == $schemeField){
				
				$needSkip = false;
				
				$this->orderDetailsSuppInfo = new \com\vip\order\common\pojo\order\vo\OrderDetailsSuppInfoVO();
				$this->orderDetailsSuppInfo->read($input);
				
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
		
		
		if($this->orderGoods !== null) {
			
			$xfer += $output->writeFieldBegin('orderGoods');
			
			if (!is_object($this->orderGoods)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->orderGoods->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderDetailsSuppInfo !== null) {
			
			$xfer += $output->writeFieldBegin('orderDetailsSuppInfo');
			
			if (!is_object($this->orderDetailsSuppInfo)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->orderDetailsSuppInfo->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}

?>