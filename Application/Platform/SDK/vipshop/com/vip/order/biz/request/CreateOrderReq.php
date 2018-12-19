<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\biz\request;

class CreateOrderReq {
	
	static $_TSPEC;
	public $order = null;
	public $exOrderSn = null;
	public $orderInvoice = null;
	public $orderElectronicInvoice = null;
	public $orderReceiveAddr = null;
	public $orderPayAndDiscount = null;
	public $orderPayDetailList = null;
	public $orderPayInstalmentList = null;
	public $orderGoodsAndDescribeList = null;
	public $orderActiveList = null;
	public $orderCookie = null;
	public $prepayOrderExtend = null;
	public $orderPeriodsInfoList = null;
	public $uniqueKey = null;
	public $indexKey = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'order'
			),
			2 => array(
			'var' => 'exOrderSn'
			),
			3 => array(
			'var' => 'orderInvoice'
			),
			4 => array(
			'var' => 'orderElectronicInvoice'
			),
			5 => array(
			'var' => 'orderReceiveAddr'
			),
			6 => array(
			'var' => 'orderPayAndDiscount'
			),
			7 => array(
			'var' => 'orderPayDetailList'
			),
			8 => array(
			'var' => 'orderPayInstalmentList'
			),
			9 => array(
			'var' => 'orderGoodsAndDescribeList'
			),
			10 => array(
			'var' => 'orderActiveList'
			),
			11 => array(
			'var' => 'orderCookie'
			),
			12 => array(
			'var' => 'prepayOrderExtend'
			),
			13 => array(
			'var' => 'orderPeriodsInfoList'
			),
			14 => array(
			'var' => 'uniqueKey'
			),
			15 => array(
			'var' => 'indexKey'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['order'])){
				
				$this->order = $vals['order'];
			}
			
			
			if (isset($vals['exOrderSn'])){
				
				$this->exOrderSn = $vals['exOrderSn'];
			}
			
			
			if (isset($vals['orderInvoice'])){
				
				$this->orderInvoice = $vals['orderInvoice'];
			}
			
			
			if (isset($vals['orderElectronicInvoice'])){
				
				$this->orderElectronicInvoice = $vals['orderElectronicInvoice'];
			}
			
			
			if (isset($vals['orderReceiveAddr'])){
				
				$this->orderReceiveAddr = $vals['orderReceiveAddr'];
			}
			
			
			if (isset($vals['orderPayAndDiscount'])){
				
				$this->orderPayAndDiscount = $vals['orderPayAndDiscount'];
			}
			
			
			if (isset($vals['orderPayDetailList'])){
				
				$this->orderPayDetailList = $vals['orderPayDetailList'];
			}
			
			
			if (isset($vals['orderPayInstalmentList'])){
				
				$this->orderPayInstalmentList = $vals['orderPayInstalmentList'];
			}
			
			
			if (isset($vals['orderGoodsAndDescribeList'])){
				
				$this->orderGoodsAndDescribeList = $vals['orderGoodsAndDescribeList'];
			}
			
			
			if (isset($vals['orderActiveList'])){
				
				$this->orderActiveList = $vals['orderActiveList'];
			}
			
			
			if (isset($vals['orderCookie'])){
				
				$this->orderCookie = $vals['orderCookie'];
			}
			
			
			if (isset($vals['prepayOrderExtend'])){
				
				$this->prepayOrderExtend = $vals['prepayOrderExtend'];
			}
			
			
			if (isset($vals['orderPeriodsInfoList'])){
				
				$this->orderPeriodsInfoList = $vals['orderPeriodsInfoList'];
			}
			
			
			if (isset($vals['uniqueKey'])){
				
				$this->uniqueKey = $vals['uniqueKey'];
			}
			
			
			if (isset($vals['indexKey'])){
				
				$this->indexKey = $vals['indexKey'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'CreateOrderReq';
	}
	
	public function read($input){
		
		$input->readStructBegin();
		while(true){
			
			$schemeField = $input->readFieldBegin();
			if ($schemeField == null) break;
			$needSkip = true;
			
			
			if ("order" == $schemeField){
				
				$needSkip = false;
				
				$this->order = new \com\vip\order\biz\vo\OrderVO();
				$this->order->read($input);
				
			}
			
			
			
			
			if ("exOrderSn" == $schemeField){
				
				$needSkip = false;
				
				$this->exOrderSn = new \com\vip\order\biz\vo\OrderCoopVO();
				$this->exOrderSn->read($input);
				
			}
			
			
			
			
			if ("orderInvoice" == $schemeField){
				
				$needSkip = false;
				
				$this->orderInvoice = new \com\vip\order\biz\vo\OrderInvoiceVO();
				$this->orderInvoice->read($input);
				
			}
			
			
			
			
			if ("orderElectronicInvoice" == $schemeField){
				
				$needSkip = false;
				
				$this->orderElectronicInvoice = new \com\vip\order\biz\vo\OrderElectronicInvoiceVO();
				$this->orderElectronicInvoice->read($input);
				
			}
			
			
			
			
			if ("orderReceiveAddr" == $schemeField){
				
				$needSkip = false;
				
				$this->orderReceiveAddr = new \com\vip\order\biz\vo\OrderReceiveAddrVO();
				$this->orderReceiveAddr->read($input);
				
			}
			
			
			
			
			if ("orderPayAndDiscount" == $schemeField){
				
				$needSkip = false;
				
				$this->orderPayAndDiscount = new \com\vip\order\biz\vo\OrderPayAndDiscountVO();
				$this->orderPayAndDiscount->read($input);
				
			}
			
			
			
			
			if ("orderPayDetailList" == $schemeField){
				
				$needSkip = false;
				
				$this->orderPayDetailList = array();
				$_size1 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem1 = null;
						
						$elem1 = new \com\vip\order\biz\vo\OrderPayDetailVO();
						$elem1->read($input);
						
						$this->orderPayDetailList[$_size1++] = $elem1;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readListEnd();
				
			}
			
			
			
			
			if ("orderPayInstalmentList" == $schemeField){
				
				$needSkip = false;
				
				$this->orderPayInstalmentList = array();
				$_size2 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem2 = null;
						
						$elem2 = new \com\vip\order\biz\vo\OrderPayInstalmentVO();
						$elem2->read($input);
						
						$this->orderPayInstalmentList[$_size2++] = $elem2;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readListEnd();
				
			}
			
			
			
			
			if ("orderGoodsAndDescribeList" == $schemeField){
				
				$needSkip = false;
				
				$this->orderGoodsAndDescribeList = array();
				$_size3 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem3 = null;
						
						$elem3 = new \com\vip\order\biz\vo\OrderGoodsAndDescribeVO();
						$elem3->read($input);
						
						$this->orderGoodsAndDescribeList[$_size3++] = $elem3;
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
				$_size4 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem4 = null;
						
						$elem4 = new \com\vip\order\biz\vo\OrderActiveVO();
						$elem4->read($input);
						
						$this->orderActiveList[$_size4++] = $elem4;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readListEnd();
				
			}
			
			
			
			
			if ("orderCookie" == $schemeField){
				
				$needSkip = false;
				
				$this->orderCookie = new \com\vip\order\biz\vo\OrderCookieVO();
				$this->orderCookie->read($input);
				
			}
			
			
			
			
			if ("prepayOrderExtend" == $schemeField){
				
				$needSkip = false;
				
				$this->prepayOrderExtend = new \com\vip\order\biz\vo\PrepayOrderExtendVO();
				$this->prepayOrderExtend->read($input);
				
			}
			
			
			
			
			if ("orderPeriodsInfoList" == $schemeField){
				
				$needSkip = false;
				
				$this->orderPeriodsInfoList = array();
				$_size5 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem5 = null;
						
						$elem5 = new \com\vip\order\biz\vo\PrepayOrderPeriodsInfoVO();
						$elem5->read($input);
						
						$this->orderPeriodsInfoList[$_size5++] = $elem5;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readListEnd();
				
			}
			
			
			
			
			if ("uniqueKey" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->uniqueKey);
				
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
		
		if($this->order !== null) {
			
			$xfer += $output->writeFieldBegin('order');
			
			if (!is_object($this->order)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->order->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->exOrderSn !== null) {
			
			$xfer += $output->writeFieldBegin('exOrderSn');
			
			if (!is_object($this->exOrderSn)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->exOrderSn->write($output);
			
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
		
		
		if($this->orderElectronicInvoice !== null) {
			
			$xfer += $output->writeFieldBegin('orderElectronicInvoice');
			
			if (!is_object($this->orderElectronicInvoice)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->orderElectronicInvoice->write($output);
			
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
		
		
		if($this->orderPayDetailList !== null) {
			
			$xfer += $output->writeFieldBegin('orderPayDetailList');
			
			if (!is_array($this->orderPayDetailList)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeListBegin();
			foreach ($this->orderPayDetailList as $iter0){
				
				
				if (!is_object($iter0)) {
					
					throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
				}
				
				$xfer += $iter0->write($output);
				
			}
			
			$output->writeListEnd();
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderPayInstalmentList !== null) {
			
			$xfer += $output->writeFieldBegin('orderPayInstalmentList');
			
			if (!is_array($this->orderPayInstalmentList)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeListBegin();
			foreach ($this->orderPayInstalmentList as $iter0){
				
				
				if (!is_object($iter0)) {
					
					throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
				}
				
				$xfer += $iter0->write($output);
				
			}
			
			$output->writeListEnd();
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderGoodsAndDescribeList !== null) {
			
			$xfer += $output->writeFieldBegin('orderGoodsAndDescribeList');
			
			if (!is_array($this->orderGoodsAndDescribeList)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeListBegin();
			foreach ($this->orderGoodsAndDescribeList as $iter0){
				
				
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
		
		
		if($this->orderCookie !== null) {
			
			$xfer += $output->writeFieldBegin('orderCookie');
			
			if (!is_object($this->orderCookie)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->orderCookie->write($output);
			
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
		
		
		if($this->orderPeriodsInfoList !== null) {
			
			$xfer += $output->writeFieldBegin('orderPeriodsInfoList');
			
			if (!is_array($this->orderPeriodsInfoList)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeListBegin();
			foreach ($this->orderPeriodsInfoList as $iter0){
				
				
				if (!is_object($iter0)) {
					
					throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
				}
				
				$xfer += $iter0->write($output);
				
			}
			
			$output->writeListEnd();
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->uniqueKey !== null) {
			
			$xfer += $output->writeFieldBegin('uniqueKey');
			$xfer += $output->writeString($this->uniqueKey);
			
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