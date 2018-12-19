<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\common\pojo\order\vo;

class OrderInfoVO {
	
	static $_TSPEC;
	public $orderCategory = null;
	public $order = null;
	public $orderReceiveAddr = null;
	public $orderPayAndDiscount = null;
	public $orderGoodsList = null;
	public $orderInvoice = null;
	public $orderActiveList = null;
	public $prepayOrderExtend = null;
	public $orderDetailsSuppInfo = null;
	public $opStatusList = null;
	public $orderGoodsWarehouseDetailList = null;
	public $prepayOrderExtendList = null;
	public $orderCompensate = null;
	public $orderWarehouseList = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'orderCategory'
			),
			2 => array(
			'var' => 'order'
			),
			3 => array(
			'var' => 'orderReceiveAddr'
			),
			4 => array(
			'var' => 'orderPayAndDiscount'
			),
			5 => array(
			'var' => 'orderGoodsList'
			),
			6 => array(
			'var' => 'orderInvoice'
			),
			7 => array(
			'var' => 'orderActiveList'
			),
			8 => array(
			'var' => 'prepayOrderExtend'
			),
			9 => array(
			'var' => 'orderDetailsSuppInfo'
			),
			10 => array(
			'var' => 'opStatusList'
			),
			11 => array(
			'var' => 'orderGoodsWarehouseDetailList'
			),
			12 => array(
			'var' => 'prepayOrderExtendList'
			),
			13 => array(
			'var' => 'orderCompensate'
			),
			14 => array(
			'var' => 'orderWarehouseList'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['orderCategory'])){
				
				$this->orderCategory = $vals['orderCategory'];
			}
			
			
			if (isset($vals['order'])){
				
				$this->order = $vals['order'];
			}
			
			
			if (isset($vals['orderReceiveAddr'])){
				
				$this->orderReceiveAddr = $vals['orderReceiveAddr'];
			}
			
			
			if (isset($vals['orderPayAndDiscount'])){
				
				$this->orderPayAndDiscount = $vals['orderPayAndDiscount'];
			}
			
			
			if (isset($vals['orderGoodsList'])){
				
				$this->orderGoodsList = $vals['orderGoodsList'];
			}
			
			
			if (isset($vals['orderInvoice'])){
				
				$this->orderInvoice = $vals['orderInvoice'];
			}
			
			
			if (isset($vals['orderActiveList'])){
				
				$this->orderActiveList = $vals['orderActiveList'];
			}
			
			
			if (isset($vals['prepayOrderExtend'])){
				
				$this->prepayOrderExtend = $vals['prepayOrderExtend'];
			}
			
			
			if (isset($vals['orderDetailsSuppInfo'])){
				
				$this->orderDetailsSuppInfo = $vals['orderDetailsSuppInfo'];
			}
			
			
			if (isset($vals['opStatusList'])){
				
				$this->opStatusList = $vals['opStatusList'];
			}
			
			
			if (isset($vals['orderGoodsWarehouseDetailList'])){
				
				$this->orderGoodsWarehouseDetailList = $vals['orderGoodsWarehouseDetailList'];
			}
			
			
			if (isset($vals['prepayOrderExtendList'])){
				
				$this->prepayOrderExtendList = $vals['prepayOrderExtendList'];
			}
			
			
			if (isset($vals['orderCompensate'])){
				
				$this->orderCompensate = $vals['orderCompensate'];
			}
			
			
			if (isset($vals['orderWarehouseList'])){
				
				$this->orderWarehouseList = $vals['orderWarehouseList'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'OrderInfoVO';
	}
	
	public function read($input){
		
		$input->readStructBegin();
		while(true){
			
			$schemeField = $input->readFieldBegin();
			if ($schemeField == null) break;
			$needSkip = true;
			
			
			if ("orderCategory" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->orderCategory); 
				
			}
			
			
			
			
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
			
			
			
			
			if ("orderGoodsList" == $schemeField){
				
				$needSkip = false;
				
				$this->orderGoodsList = array();
				$_size1 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem1 = null;
						
						$elem1 = new \com\vip\order\common\pojo\order\vo\OrderGoodsVO();
						$elem1->read($input);
						
						$this->orderGoodsList[$_size1++] = $elem1;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readListEnd();
				
			}
			
			
			
			
			if ("orderInvoice" == $schemeField){
				
				$needSkip = false;
				
				$this->orderInvoice = new \com\vip\order\common\pojo\order\vo\OrderInvoiceVO();
				$this->orderInvoice->read($input);
				
			}
			
			
			
			
			if ("orderActiveList" == $schemeField){
				
				$needSkip = false;
				
				$this->orderActiveList = array();
				$_size2 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem2 = null;
						
						$elem2 = new \com\vip\order\common\pojo\order\vo\OrderActiveVO();
						$elem2->read($input);
						
						$this->orderActiveList[$_size2++] = $elem2;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readListEnd();
				
			}
			
			
			
			
			if ("prepayOrderExtend" == $schemeField){
				
				$needSkip = false;
				
				$this->prepayOrderExtend = new \com\vip\order\common\pojo\order\vo\PrepayOrderExtendVO();
				$this->prepayOrderExtend->read($input);
				
			}
			
			
			
			
			if ("orderDetailsSuppInfo" == $schemeField){
				
				$needSkip = false;
				
				$this->orderDetailsSuppInfo = new \com\vip\order\common\pojo\order\vo\OrderDetailsSuppInfoVO();
				$this->orderDetailsSuppInfo->read($input);
				
			}
			
			
			
			
			if ("opStatusList" == $schemeField){
				
				$needSkip = false;
				
				$this->opStatusList = array();
				$_size3 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem3 = null;
						
						$elem3 = new \com\vip\order\common\pojo\order\vo\OpStatusVO();
						$elem3->read($input);
						
						$this->opStatusList[$_size3++] = $elem3;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readListEnd();
				
			}
			
			
			
			
			if ("orderGoodsWarehouseDetailList" == $schemeField){
				
				$needSkip = false;
				
				$this->orderGoodsWarehouseDetailList = array();
				$_size4 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem4 = null;
						
						$elem4 = new \com\vip\order\common\pojo\order\vo\OrderGoodsWarehouseDetailVO();
						$elem4->read($input);
						
						$this->orderGoodsWarehouseDetailList[$_size4++] = $elem4;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readListEnd();
				
			}
			
			
			
			
			if ("prepayOrderExtendList" == $schemeField){
				
				$needSkip = false;
				
				$this->prepayOrderExtendList = array();
				$_size5 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem5 = null;
						
						$elem5 = new \com\vip\order\common\pojo\order\vo\PrepayOrderExtendVO();
						$elem5->read($input);
						
						$this->prepayOrderExtendList[$_size5++] = $elem5;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readListEnd();
				
			}
			
			
			
			
			if ("orderCompensate" == $schemeField){
				
				$needSkip = false;
				
				$this->orderCompensate = new \com\vip\order\common\pojo\order\vo\OrderCompensateVO();
				$this->orderCompensate->read($input);
				
			}
			
			
			
			
			if ("orderWarehouseList" == $schemeField){
				
				$needSkip = false;
				
				$this->orderWarehouseList = array();
				$_size6 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem6 = null;
						
						$elem6 = new \com\vip\order\common\pojo\order\vo\OrderWarehouseVO();
						$elem6->read($input);
						
						$this->orderWarehouseList[$_size6++] = $elem6;
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
		
		if($this->orderCategory !== null) {
			
			$xfer += $output->writeFieldBegin('orderCategory');
			$xfer += $output->writeI32($this->orderCategory);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
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
		
		
		if($this->orderGoodsList !== null) {
			
			$xfer += $output->writeFieldBegin('orderGoodsList');
			
			if (!is_array($this->orderGoodsList)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeListBegin();
			foreach ($this->orderGoodsList as $iter0){
				
				
				if (!is_object($iter0)) {
					
					throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
				}
				
				$xfer += $iter0->write($output);
				
			}
			
			$output->writeListEnd();
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderInvoice !== null) {
			
			$xfer += $output->writeFieldBegin('orderInvoice');
			
			if (!is_object($this->orderInvoice)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->orderInvoice->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderActiveList !== null) {
			
			$xfer += $output->writeFieldBegin('orderActiveList');
			
			if (!is_array($this->orderActiveList)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeListBegin();
			foreach ($this->orderActiveList as $iter0){
				
				
				if (!is_object($iter0)) {
					
					throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
				}
				
				$xfer += $iter0->write($output);
				
			}
			
			$output->writeListEnd();
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->prepayOrderExtend !== null) {
			
			$xfer += $output->writeFieldBegin('prepayOrderExtend');
			
			if (!is_object($this->prepayOrderExtend)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->prepayOrderExtend->write($output);
			
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
		
		
		if($this->opStatusList !== null) {
			
			$xfer += $output->writeFieldBegin('opStatusList');
			
			if (!is_array($this->opStatusList)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeListBegin();
			foreach ($this->opStatusList as $iter0){
				
				
				if (!is_object($iter0)) {
					
					throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
				}
				
				$xfer += $iter0->write($output);
				
			}
			
			$output->writeListEnd();
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderGoodsWarehouseDetailList !== null) {
			
			$xfer += $output->writeFieldBegin('orderGoodsWarehouseDetailList');
			
			if (!is_array($this->orderGoodsWarehouseDetailList)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeListBegin();
			foreach ($this->orderGoodsWarehouseDetailList as $iter0){
				
				
				if (!is_object($iter0)) {
					
					throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
				}
				
				$xfer += $iter0->write($output);
				
			}
			
			$output->writeListEnd();
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->prepayOrderExtendList !== null) {
			
			$xfer += $output->writeFieldBegin('prepayOrderExtendList');
			
			if (!is_array($this->prepayOrderExtendList)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeListBegin();
			foreach ($this->prepayOrderExtendList as $iter0){
				
				
				if (!is_object($iter0)) {
					
					throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
				}
				
				$xfer += $iter0->write($output);
				
			}
			
			$output->writeListEnd();
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderCompensate !== null) {
			
			$xfer += $output->writeFieldBegin('orderCompensate');
			
			if (!is_object($this->orderCompensate)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->orderCompensate->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderWarehouseList !== null) {
			
			$xfer += $output->writeFieldBegin('orderWarehouseList');
			
			if (!is_array($this->orderWarehouseList)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeListBegin();
			foreach ($this->orderWarehouseList as $iter0){
				
				
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