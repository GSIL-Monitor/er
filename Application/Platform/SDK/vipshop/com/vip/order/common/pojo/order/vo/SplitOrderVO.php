<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\common\pojo\order\vo;

class SplitOrderVO {
	
	static $_TSPEC;
	public $order = null;
	public $orderGoodsList = null;
	public $orderActiveList = null;
	public $prepayOrderMoneyDetailList = null;
	public $payDetailList = null;
	public $orderPayAndDiscount = null;
	public $goodsWarehouseList = null;
	public $orderInvoice = null;
	public $orderWarehouseList = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'order'
			),
			2 => array(
			'var' => 'orderGoodsList'
			),
			3 => array(
			'var' => 'orderActiveList'
			),
			4 => array(
			'var' => 'prepayOrderMoneyDetailList'
			),
			5 => array(
			'var' => 'payDetailList'
			),
			6 => array(
			'var' => 'orderPayAndDiscount'
			),
			7 => array(
			'var' => 'goodsWarehouseList'
			),
			8 => array(
			'var' => 'orderInvoice'
			),
			9 => array(
			'var' => 'orderWarehouseList'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['order'])){
				
				$this->order = $vals['order'];
			}
			
			
			if (isset($vals['orderGoodsList'])){
				
				$this->orderGoodsList = $vals['orderGoodsList'];
			}
			
			
			if (isset($vals['orderActiveList'])){
				
				$this->orderActiveList = $vals['orderActiveList'];
			}
			
			
			if (isset($vals['prepayOrderMoneyDetailList'])){
				
				$this->prepayOrderMoneyDetailList = $vals['prepayOrderMoneyDetailList'];
			}
			
			
			if (isset($vals['payDetailList'])){
				
				$this->payDetailList = $vals['payDetailList'];
			}
			
			
			if (isset($vals['orderPayAndDiscount'])){
				
				$this->orderPayAndDiscount = $vals['orderPayAndDiscount'];
			}
			
			
			if (isset($vals['goodsWarehouseList'])){
				
				$this->goodsWarehouseList = $vals['goodsWarehouseList'];
			}
			
			
			if (isset($vals['orderInvoice'])){
				
				$this->orderInvoice = $vals['orderInvoice'];
			}
			
			
			if (isset($vals['orderWarehouseList'])){
				
				$this->orderWarehouseList = $vals['orderWarehouseList'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'SplitOrderVO';
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
			
			
			
			
			if ("orderGoodsList" == $schemeField){
				
				$needSkip = false;
				
				$this->orderGoodsList = array();
				$_size0 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem0 = null;
						
						$elem0 = new \com\vip\order\common\pojo\order\vo\OrderGoodsVO();
						$elem0->read($input);
						
						$this->orderGoodsList[$_size0++] = $elem0;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readListEnd();
				
			}
			
			
			
			
			if ("orderActiveList" == $schemeField){
				
				$needSkip = false;
				
				$this->orderActiveList = array();
				$_size1 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem1 = null;
						
						$elem1 = new \com\vip\order\common\pojo\order\vo\OrderActiveVO();
						$elem1->read($input);
						
						$this->orderActiveList[$_size1++] = $elem1;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readListEnd();
				
			}
			
			
			
			
			if ("prepayOrderMoneyDetailList" == $schemeField){
				
				$needSkip = false;
				
				$this->prepayOrderMoneyDetailList = array();
				$_size2 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem2 = null;
						
						$elem2 = new \com\vip\order\common\pojo\order\vo\PrepayOrderMoneyDetailVO();
						$elem2->read($input);
						
						$this->prepayOrderMoneyDetailList[$_size2++] = $elem2;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readListEnd();
				
			}
			
			
			
			
			if ("payDetailList" == $schemeField){
				
				$needSkip = false;
				
				$this->payDetailList = array();
				$_size3 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem3 = null;
						
						$elem3 = new \com\vip\order\common\pojo\order\vo\OrderPayDetailVO();
						$elem3->read($input);
						
						$this->payDetailList[$_size3++] = $elem3;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readListEnd();
				
			}
			
			
			
			
			if ("orderPayAndDiscount" == $schemeField){
				
				$needSkip = false;
				
				$this->orderPayAndDiscount = new \com\vip\order\common\pojo\order\vo\OrderPayAndDiscountVO();
				$this->orderPayAndDiscount->read($input);
				
			}
			
			
			
			
			if ("goodsWarehouseList" == $schemeField){
				
				$needSkip = false;
				
				$this->goodsWarehouseList = array();
				$_size4 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem4 = null;
						
						$elem4 = new \com\vip\order\common\pojo\order\vo\OrderGoodsWarehouseDetailVO();
						$elem4->read($input);
						
						$this->goodsWarehouseList[$_size4++] = $elem4;
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
			
			
			
			
			if ("orderWarehouseList" == $schemeField){
				
				$needSkip = false;
				
				$this->orderWarehouseList = array();
				$_size5 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem5 = null;
						
						$elem5 = new \com\vip\order\common\pojo\order\vo\OrderWarehouseVO();
						$elem5->read($input);
						
						$this->orderWarehouseList[$_size5++] = $elem5;
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
		
		if($this->order !== null) {
			
			$xfer += $output->writeFieldBegin('order');
			
			if (!is_object($this->order)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->order->write($output);
			
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
		
		
		if($this->prepayOrderMoneyDetailList !== null) {
			
			$xfer += $output->writeFieldBegin('prepayOrderMoneyDetailList');
			
			if (!is_array($this->prepayOrderMoneyDetailList)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeListBegin();
			foreach ($this->prepayOrderMoneyDetailList as $iter0){
				
				
				if (!is_object($iter0)) {
					
					throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
				}
				
				$xfer += $iter0->write($output);
				
			}
			
			$output->writeListEnd();
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->payDetailList !== null) {
			
			$xfer += $output->writeFieldBegin('payDetailList');
			
			if (!is_array($this->payDetailList)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeListBegin();
			foreach ($this->payDetailList as $iter0){
				
				
				if (!is_object($iter0)) {
					
					throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
				}
				
				$xfer += $iter0->write($output);
				
			}
			
			$output->writeListEnd();
			
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
		
		
		if($this->goodsWarehouseList !== null) {
			
			$xfer += $output->writeFieldBegin('goodsWarehouseList');
			
			if (!is_array($this->goodsWarehouseList)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeListBegin();
			foreach ($this->goodsWarehouseList as $iter0){
				
				
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