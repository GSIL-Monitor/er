<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\biz\response;

class OrderDetailItem {
	
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
	public $orderLogsList = null;
	public $orderStatusHistoryList = null;
	public $orderTransportDetailsAndPackageList = null;
	public $prepayOrderExtend = null;
	public $orderReturnApplyList = null;
	public $orderExchangeApplyList = null;
	public $orderDetailsSuppInfo = null;
	public $returnAddress = null;
	public $orderCardList = null;
	public $orderCancelData = null;
	public $orderGoodsBackList = null;
	public $orderBackBankList = null;
	public $ordersPayType = null;
	public $orderPos = null;
	public $couponList = null;
	public $opStatusList = null;
	public $prepayOrderExtendList = null;
	public $orderRefundDetailsList = null;
	public $orderPaySnList = null;
	public $orderCompensate = null;
	public $orderWarehouseList = null;
	
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
			'var' => 'orderLogsList'
			),
			12 => array(
			'var' => 'orderStatusHistoryList'
			),
			13 => array(
			'var' => 'orderTransportDetailsAndPackageList'
			),
			14 => array(
			'var' => 'prepayOrderExtend'
			),
			15 => array(
			'var' => 'orderReturnApplyList'
			),
			16 => array(
			'var' => 'orderExchangeApplyList'
			),
			17 => array(
			'var' => 'orderDetailsSuppInfo'
			),
			18 => array(
			'var' => 'returnAddress'
			),
			19 => array(
			'var' => 'orderCardList'
			),
			20 => array(
			'var' => 'orderCancelData'
			),
			21 => array(
			'var' => 'orderGoodsBackList'
			),
			22 => array(
			'var' => 'orderBackBankList'
			),
			23 => array(
			'var' => 'ordersPayType'
			),
			24 => array(
			'var' => 'orderPos'
			),
			25 => array(
			'var' => 'couponList'
			),
			26 => array(
			'var' => 'opStatusList'
			),
			27 => array(
			'var' => 'prepayOrderExtendList'
			),
			28 => array(
			'var' => 'orderRefundDetailsList'
			),
			29 => array(
			'var' => 'orderPaySnList'
			),
			30 => array(
			'var' => 'orderCompensate'
			),
			31 => array(
			'var' => 'orderWarehouseList'
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
			
			
			if (isset($vals['orderLogsList'])){
				
				$this->orderLogsList = $vals['orderLogsList'];
			}
			
			
			if (isset($vals['orderStatusHistoryList'])){
				
				$this->orderStatusHistoryList = $vals['orderStatusHistoryList'];
			}
			
			
			if (isset($vals['orderTransportDetailsAndPackageList'])){
				
				$this->orderTransportDetailsAndPackageList = $vals['orderTransportDetailsAndPackageList'];
			}
			
			
			if (isset($vals['prepayOrderExtend'])){
				
				$this->prepayOrderExtend = $vals['prepayOrderExtend'];
			}
			
			
			if (isset($vals['orderReturnApplyList'])){
				
				$this->orderReturnApplyList = $vals['orderReturnApplyList'];
			}
			
			
			if (isset($vals['orderExchangeApplyList'])){
				
				$this->orderExchangeApplyList = $vals['orderExchangeApplyList'];
			}
			
			
			if (isset($vals['orderDetailsSuppInfo'])){
				
				$this->orderDetailsSuppInfo = $vals['orderDetailsSuppInfo'];
			}
			
			
			if (isset($vals['returnAddress'])){
				
				$this->returnAddress = $vals['returnAddress'];
			}
			
			
			if (isset($vals['orderCardList'])){
				
				$this->orderCardList = $vals['orderCardList'];
			}
			
			
			if (isset($vals['orderCancelData'])){
				
				$this->orderCancelData = $vals['orderCancelData'];
			}
			
			
			if (isset($vals['orderGoodsBackList'])){
				
				$this->orderGoodsBackList = $vals['orderGoodsBackList'];
			}
			
			
			if (isset($vals['orderBackBankList'])){
				
				$this->orderBackBankList = $vals['orderBackBankList'];
			}
			
			
			if (isset($vals['ordersPayType'])){
				
				$this->ordersPayType = $vals['ordersPayType'];
			}
			
			
			if (isset($vals['orderPos'])){
				
				$this->orderPos = $vals['orderPos'];
			}
			
			
			if (isset($vals['couponList'])){
				
				$this->couponList = $vals['couponList'];
			}
			
			
			if (isset($vals['opStatusList'])){
				
				$this->opStatusList = $vals['opStatusList'];
			}
			
			
			if (isset($vals['prepayOrderExtendList'])){
				
				$this->prepayOrderExtendList = $vals['prepayOrderExtendList'];
			}
			
			
			if (isset($vals['orderRefundDetailsList'])){
				
				$this->orderRefundDetailsList = $vals['orderRefundDetailsList'];
			}
			
			
			if (isset($vals['orderPaySnList'])){
				
				$this->orderPaySnList = $vals['orderPaySnList'];
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
		
		return 'OrderDetailItem';
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
			
			
			
			
			if ("exOrderSn" == $schemeField){
				
				$needSkip = false;
				
				$this->exOrderSn = new \com\vip\order\common\pojo\order\vo\OrderCoopVO();
				$this->exOrderSn->read($input);
				
			}
			
			
			
			
			if ("orderInvoice" == $schemeField){
				
				$needSkip = false;
				
				$this->orderInvoice = new \com\vip\order\common\pojo\order\vo\OrderInvoiceVO();
				$this->orderInvoice->read($input);
				
			}
			
			
			
			
			if ("orderElectronicInvoice" == $schemeField){
				
				$needSkip = false;
				
				$this->orderElectronicInvoice = new \com\vip\order\common\pojo\order\vo\OrderElectronicInvoiceVO();
				$this->orderElectronicInvoice->read($input);
				
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
			
			
			
			
			if ("orderPayDetailList" == $schemeField){
				
				$needSkip = false;
				
				$this->orderPayDetailList = array();
				$_size0 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem0 = null;
						
						$elem0 = new \com\vip\order\common\pojo\order\vo\OrderPayDetailVO();
						$elem0->read($input);
						
						$this->orderPayDetailList[$_size0++] = $elem0;
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
				$_size1 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem1 = null;
						
						$elem1 = new \com\vip\order\common\pojo\order\vo\OrderPayInstalmentVO();
						$elem1->read($input);
						
						$this->orderPayInstalmentList[$_size1++] = $elem1;
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
				$_size2 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem2 = null;
						
						$elem2 = new \com\vip\order\common\pojo\order\vo\OrderGoodsAndDescribeVO();
						$elem2->read($input);
						
						$this->orderGoodsAndDescribeList[$_size2++] = $elem2;
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
				$_size3 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem3 = null;
						
						$elem3 = new \com\vip\order\common\pojo\order\vo\OrderActiveVO();
						$elem3->read($input);
						
						$this->orderActiveList[$_size3++] = $elem3;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readListEnd();
				
			}
			
			
			
			
			if ("orderLogsList" == $schemeField){
				
				$needSkip = false;
				
				$this->orderLogsList = array();
				$_size4 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem4 = null;
						
						$elem4 = new \com\vip\order\common\pojo\order\vo\OrderLogsVO();
						$elem4->read($input);
						
						$this->orderLogsList[$_size4++] = $elem4;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readListEnd();
				
			}
			
			
			
			
			if ("orderStatusHistoryList" == $schemeField){
				
				$needSkip = false;
				
				$this->orderStatusHistoryList = array();
				$_size5 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem5 = null;
						
						$elem5 = new \com\vip\order\common\pojo\order\vo\OrderStatusHistoryVO();
						$elem5->read($input);
						
						$this->orderStatusHistoryList[$_size5++] = $elem5;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readListEnd();
				
			}
			
			
			
			
			if ("orderTransportDetailsAndPackageList" == $schemeField){
				
				$needSkip = false;
				
				$this->orderTransportDetailsAndPackageList = array();
				$_size6 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem6 = null;
						
						$elem6 = new \com\vip\order\common\pojo\order\vo\OrderTransportDetailsAndPackageVO();
						$elem6->read($input);
						
						$this->orderTransportDetailsAndPackageList[$_size6++] = $elem6;
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
			
			
			
			
			if ("orderReturnApplyList" == $schemeField){
				
				$needSkip = false;
				
				$this->orderReturnApplyList = array();
				$_size7 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem7 = null;
						
						$elem7 = new \com\vip\order\common\pojo\order\vo\OrderReturnApplyListVO();
						$elem7->read($input);
						
						$this->orderReturnApplyList[$_size7++] = $elem7;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readListEnd();
				
			}
			
			
			
			
			if ("orderExchangeApplyList" == $schemeField){
				
				$needSkip = false;
				
				$this->orderExchangeApplyList = array();
				$_size8 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem8 = null;
						
						$elem8 = new \com\vip\order\common\pojo\order\vo\OrderExchangeApplyListVO();
						$elem8->read($input);
						
						$this->orderExchangeApplyList[$_size8++] = $elem8;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readListEnd();
				
			}
			
			
			
			
			if ("orderDetailsSuppInfo" == $schemeField){
				
				$needSkip = false;
				
				$this->orderDetailsSuppInfo = new \com\vip\order\common\pojo\order\vo\OrderDetailsSuppInfoVO();
				$this->orderDetailsSuppInfo->read($input);
				
			}
			
			
			
			
			if ("returnAddress" == $schemeField){
				
				$needSkip = false;
				
				$this->returnAddress = new \com\vip\order\common\pojo\order\vo\ReturnAddressVO();
				$this->returnAddress->read($input);
				
			}
			
			
			
			
			if ("orderCardList" == $schemeField){
				
				$needSkip = false;
				
				$this->orderCardList = array();
				$_size9 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem9 = null;
						
						$elem9 = new \com\vip\order\common\pojo\order\vo\OrderCardVO();
						$elem9->read($input);
						
						$this->orderCardList[$_size9++] = $elem9;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readListEnd();
				
			}
			
			
			
			
			if ("orderCancelData" == $schemeField){
				
				$needSkip = false;
				
				$this->orderCancelData = new \com\vip\order\common\pojo\order\vo\OrderCancelDataVO();
				$this->orderCancelData->read($input);
				
			}
			
			
			
			
			if ("orderGoodsBackList" == $schemeField){
				
				$needSkip = false;
				
				$this->orderGoodsBackList = array();
				$_size10 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem10 = null;
						
						$elem10 = new \com\vip\order\common\pojo\order\vo\OrderGoodsBackVO();
						$elem10->read($input);
						
						$this->orderGoodsBackList[$_size10++] = $elem10;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readListEnd();
				
			}
			
			
			
			
			if ("orderBackBankList" == $schemeField){
				
				$needSkip = false;
				
				$this->orderBackBankList = array();
				$_size11 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem11 = null;
						
						$elem11 = new \com\vip\order\common\pojo\order\vo\OrderBackBankVO();
						$elem11->read($input);
						
						$this->orderBackBankList[$_size11++] = $elem11;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readListEnd();
				
			}
			
			
			
			
			if ("ordersPayType" == $schemeField){
				
				$needSkip = false;
				
				$this->ordersPayType = new \com\vip\order\common\pojo\order\vo\OrdersPayTypeVO();
				$this->ordersPayType->read($input);
				
			}
			
			
			
			
			if ("orderPos" == $schemeField){
				
				$needSkip = false;
				
				$this->orderPos = new \com\vip\order\common\pojo\order\vo\OrderPosVO();
				$this->orderPos->read($input);
				
			}
			
			
			
			
			if ("couponList" == $schemeField){
				
				$needSkip = false;
				
				$this->couponList = array();
				$_size12 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem12 = null;
						
						$elem12 = new \com\vip\order\common\pojo\order\vo\OrderCouponVO();
						$elem12->read($input);
						
						$this->couponList[$_size12++] = $elem12;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readListEnd();
				
			}
			
			
			
			
			if ("opStatusList" == $schemeField){
				
				$needSkip = false;
				
				$this->opStatusList = array();
				$_size13 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem13 = null;
						
						$elem13 = new \com\vip\order\common\pojo\order\vo\OpStatusVO();
						$elem13->read($input);
						
						$this->opStatusList[$_size13++] = $elem13;
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
				$_size14 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem14 = null;
						
						$elem14 = new \com\vip\order\common\pojo\order\vo\PrepayOrderExtendVO();
						$elem14->read($input);
						
						$this->prepayOrderExtendList[$_size14++] = $elem14;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readListEnd();
				
			}
			
			
			
			
			if ("orderRefundDetailsList" == $schemeField){
				
				$needSkip = false;
				
				$this->orderRefundDetailsList = array();
				$_size15 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem15 = null;
						
						$elem15 = new \com\vip\order\common\pojo\order\vo\OrderRefundDetailsVO();
						$elem15->read($input);
						
						$this->orderRefundDetailsList[$_size15++] = $elem15;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readListEnd();
				
			}
			
			
			
			
			if ("orderPaySnList" == $schemeField){
				
				$needSkip = false;
				
				$this->orderPaySnList = array();
				$_size16 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem16 = null;
						
						$elem16 = new \com\vip\order\common\pojo\order\vo\OrderPaySnVO();
						$elem16->read($input);
						
						$this->orderPaySnList[$_size16++] = $elem16;
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
				$_size17 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem17 = null;
						
						$elem17 = new \com\vip\order\common\pojo\order\vo\OrderWarehouseVO();
						$elem17->read($input);
						
						$this->orderWarehouseList[$_size17++] = $elem17;
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
		
		
		if($this->orderLogsList !== null) {
			
			$xfer += $output->writeFieldBegin('orderLogsList');
			
			if (!is_array($this->orderLogsList)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeListBegin();
			foreach ($this->orderLogsList as $iter0){
				
				
				if (!is_object($iter0)) {
					
					throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
				}
				
				$xfer += $iter0->write($output);
				
			}
			
			$output->writeListEnd();
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderStatusHistoryList !== null) {
			
			$xfer += $output->writeFieldBegin('orderStatusHistoryList');
			
			if (!is_array($this->orderStatusHistoryList)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeListBegin();
			foreach ($this->orderStatusHistoryList as $iter0){
				
				
				if (!is_object($iter0)) {
					
					throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
				}
				
				$xfer += $iter0->write($output);
				
			}
			
			$output->writeListEnd();
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderTransportDetailsAndPackageList !== null) {
			
			$xfer += $output->writeFieldBegin('orderTransportDetailsAndPackageList');
			
			if (!is_array($this->orderTransportDetailsAndPackageList)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeListBegin();
			foreach ($this->orderTransportDetailsAndPackageList as $iter0){
				
				
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
		
		
		if($this->orderReturnApplyList !== null) {
			
			$xfer += $output->writeFieldBegin('orderReturnApplyList');
			
			if (!is_array($this->orderReturnApplyList)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeListBegin();
			foreach ($this->orderReturnApplyList as $iter0){
				
				
				if (!is_object($iter0)) {
					
					throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
				}
				
				$xfer += $iter0->write($output);
				
			}
			
			$output->writeListEnd();
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderExchangeApplyList !== null) {
			
			$xfer += $output->writeFieldBegin('orderExchangeApplyList');
			
			if (!is_array($this->orderExchangeApplyList)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeListBegin();
			foreach ($this->orderExchangeApplyList as $iter0){
				
				
				if (!is_object($iter0)) {
					
					throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
				}
				
				$xfer += $iter0->write($output);
				
			}
			
			$output->writeListEnd();
			
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
		
		
		if($this->returnAddress !== null) {
			
			$xfer += $output->writeFieldBegin('returnAddress');
			
			if (!is_object($this->returnAddress)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->returnAddress->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderCardList !== null) {
			
			$xfer += $output->writeFieldBegin('orderCardList');
			
			if (!is_array($this->orderCardList)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeListBegin();
			foreach ($this->orderCardList as $iter0){
				
				
				if (!is_object($iter0)) {
					
					throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
				}
				
				$xfer += $iter0->write($output);
				
			}
			
			$output->writeListEnd();
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderCancelData !== null) {
			
			$xfer += $output->writeFieldBegin('orderCancelData');
			
			if (!is_object($this->orderCancelData)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->orderCancelData->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderGoodsBackList !== null) {
			
			$xfer += $output->writeFieldBegin('orderGoodsBackList');
			
			if (!is_array($this->orderGoodsBackList)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeListBegin();
			foreach ($this->orderGoodsBackList as $iter0){
				
				
				if (!is_object($iter0)) {
					
					throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
				}
				
				$xfer += $iter0->write($output);
				
			}
			
			$output->writeListEnd();
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderBackBankList !== null) {
			
			$xfer += $output->writeFieldBegin('orderBackBankList');
			
			if (!is_array($this->orderBackBankList)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeListBegin();
			foreach ($this->orderBackBankList as $iter0){
				
				
				if (!is_object($iter0)) {
					
					throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
				}
				
				$xfer += $iter0->write($output);
				
			}
			
			$output->writeListEnd();
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->ordersPayType !== null) {
			
			$xfer += $output->writeFieldBegin('ordersPayType');
			
			if (!is_object($this->ordersPayType)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->ordersPayType->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderPos !== null) {
			
			$xfer += $output->writeFieldBegin('orderPos');
			
			if (!is_object($this->orderPos)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->orderPos->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->couponList !== null) {
			
			$xfer += $output->writeFieldBegin('couponList');
			
			if (!is_array($this->couponList)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeListBegin();
			foreach ($this->couponList as $iter0){
				
				
				if (!is_object($iter0)) {
					
					throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
				}
				
				$xfer += $iter0->write($output);
				
			}
			
			$output->writeListEnd();
			
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
		
		
		if($this->orderRefundDetailsList !== null) {
			
			$xfer += $output->writeFieldBegin('orderRefundDetailsList');
			
			if (!is_array($this->orderRefundDetailsList)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeListBegin();
			foreach ($this->orderRefundDetailsList as $iter0){
				
				
				if (!is_object($iter0)) {
					
					throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
				}
				
				$xfer += $iter0->write($output);
				
			}
			
			$output->writeListEnd();
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderPaySnList !== null) {
			
			$xfer += $output->writeFieldBegin('orderPaySnList');
			
			if (!is_array($this->orderPaySnList)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeListBegin();
			foreach ($this->orderPaySnList as $iter0){
				
				
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