<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\common\pojo\order\vo;

class OrderReturnGoodsVO {
	
	static $_TSPEC;
	public $applyId = null;
	public $orderReturnGoodsId = null;
	public $orderReturnTransportId = null;
	public $merItemNo = null;
	public $sn = null;
	public $goodsName = null;
	public $salesName = null;
	public $sizeName = null;
	public $priceId = null;
	public $vSkuId = null;
	public $goodsVersion = null;
	public $amount = null;
	public $sellPrice = null;
	public $goodsType = null;
	public $returnReasonId = null;
	public $ourReasonFlag = null;
	public $specialRefund = null;
	public $specialRefundReason = null;
	public $reasonRemark = null;
	public $goodsBackFlag = null;
	public $backTime = null;
	public $returnReasonDetails = null;
	public $isDeleted = null;
	public $salesNo = null;
	public $merchandiseNo = null;
	public $orderId = null;
	public $exActSubtotal = null;
	public $exCouponSubTotal = null;
	public $exPaySubtotal = null;
	public $exAllSubtotal = null;
	public $addTime = null;
	public $operatorName = null;
	public $priceTime = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			30 => array(
			'var' => 'applyId'
			),
			1 => array(
			'var' => 'orderReturnGoodsId'
			),
			2 => array(
			'var' => 'orderReturnTransportId'
			),
			3 => array(
			'var' => 'merItemNo'
			),
			4 => array(
			'var' => 'sn'
			),
			5 => array(
			'var' => 'goodsName'
			),
			6 => array(
			'var' => 'salesName'
			),
			7 => array(
			'var' => 'sizeName'
			),
			8 => array(
			'var' => 'priceId'
			),
			9 => array(
			'var' => 'vSkuId'
			),
			10 => array(
			'var' => 'goodsVersion'
			),
			11 => array(
			'var' => 'amount'
			),
			12 => array(
			'var' => 'sellPrice'
			),
			13 => array(
			'var' => 'goodsType'
			),
			14 => array(
			'var' => 'returnReasonId'
			),
			15 => array(
			'var' => 'ourReasonFlag'
			),
			16 => array(
			'var' => 'specialRefund'
			),
			17 => array(
			'var' => 'specialRefundReason'
			),
			18 => array(
			'var' => 'reasonRemark'
			),
			19 => array(
			'var' => 'goodsBackFlag'
			),
			20 => array(
			'var' => 'backTime'
			),
			21 => array(
			'var' => 'returnReasonDetails'
			),
			22 => array(
			'var' => 'isDeleted'
			),
			23 => array(
			'var' => 'salesNo'
			),
			24 => array(
			'var' => 'merchandiseNo'
			),
			25 => array(
			'var' => 'orderId'
			),
			26 => array(
			'var' => 'exActSubtotal'
			),
			27 => array(
			'var' => 'exCouponSubTotal'
			),
			28 => array(
			'var' => 'exPaySubtotal'
			),
			29 => array(
			'var' => 'exAllSubtotal'
			),
			31 => array(
			'var' => 'addTime'
			),
			32 => array(
			'var' => 'operatorName'
			),
			33 => array(
			'var' => 'priceTime'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['applyId'])){
				
				$this->applyId = $vals['applyId'];
			}
			
			
			if (isset($vals['orderReturnGoodsId'])){
				
				$this->orderReturnGoodsId = $vals['orderReturnGoodsId'];
			}
			
			
			if (isset($vals['orderReturnTransportId'])){
				
				$this->orderReturnTransportId = $vals['orderReturnTransportId'];
			}
			
			
			if (isset($vals['merItemNo'])){
				
				$this->merItemNo = $vals['merItemNo'];
			}
			
			
			if (isset($vals['sn'])){
				
				$this->sn = $vals['sn'];
			}
			
			
			if (isset($vals['goodsName'])){
				
				$this->goodsName = $vals['goodsName'];
			}
			
			
			if (isset($vals['salesName'])){
				
				$this->salesName = $vals['salesName'];
			}
			
			
			if (isset($vals['sizeName'])){
				
				$this->sizeName = $vals['sizeName'];
			}
			
			
			if (isset($vals['priceId'])){
				
				$this->priceId = $vals['priceId'];
			}
			
			
			if (isset($vals['vSkuId'])){
				
				$this->vSkuId = $vals['vSkuId'];
			}
			
			
			if (isset($vals['goodsVersion'])){
				
				$this->goodsVersion = $vals['goodsVersion'];
			}
			
			
			if (isset($vals['amount'])){
				
				$this->amount = $vals['amount'];
			}
			
			
			if (isset($vals['sellPrice'])){
				
				$this->sellPrice = $vals['sellPrice'];
			}
			
			
			if (isset($vals['goodsType'])){
				
				$this->goodsType = $vals['goodsType'];
			}
			
			
			if (isset($vals['returnReasonId'])){
				
				$this->returnReasonId = $vals['returnReasonId'];
			}
			
			
			if (isset($vals['ourReasonFlag'])){
				
				$this->ourReasonFlag = $vals['ourReasonFlag'];
			}
			
			
			if (isset($vals['specialRefund'])){
				
				$this->specialRefund = $vals['specialRefund'];
			}
			
			
			if (isset($vals['specialRefundReason'])){
				
				$this->specialRefundReason = $vals['specialRefundReason'];
			}
			
			
			if (isset($vals['reasonRemark'])){
				
				$this->reasonRemark = $vals['reasonRemark'];
			}
			
			
			if (isset($vals['goodsBackFlag'])){
				
				$this->goodsBackFlag = $vals['goodsBackFlag'];
			}
			
			
			if (isset($vals['backTime'])){
				
				$this->backTime = $vals['backTime'];
			}
			
			
			if (isset($vals['returnReasonDetails'])){
				
				$this->returnReasonDetails = $vals['returnReasonDetails'];
			}
			
			
			if (isset($vals['isDeleted'])){
				
				$this->isDeleted = $vals['isDeleted'];
			}
			
			
			if (isset($vals['salesNo'])){
				
				$this->salesNo = $vals['salesNo'];
			}
			
			
			if (isset($vals['merchandiseNo'])){
				
				$this->merchandiseNo = $vals['merchandiseNo'];
			}
			
			
			if (isset($vals['orderId'])){
				
				$this->orderId = $vals['orderId'];
			}
			
			
			if (isset($vals['exActSubtotal'])){
				
				$this->exActSubtotal = $vals['exActSubtotal'];
			}
			
			
			if (isset($vals['exCouponSubTotal'])){
				
				$this->exCouponSubTotal = $vals['exCouponSubTotal'];
			}
			
			
			if (isset($vals['exPaySubtotal'])){
				
				$this->exPaySubtotal = $vals['exPaySubtotal'];
			}
			
			
			if (isset($vals['exAllSubtotal'])){
				
				$this->exAllSubtotal = $vals['exAllSubtotal'];
			}
			
			
			if (isset($vals['addTime'])){
				
				$this->addTime = $vals['addTime'];
			}
			
			
			if (isset($vals['operatorName'])){
				
				$this->operatorName = $vals['operatorName'];
			}
			
			
			if (isset($vals['priceTime'])){
				
				$this->priceTime = $vals['priceTime'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'OrderReturnGoodsVO';
	}
	
	public function read($input){
		
		$input->readStructBegin();
		while(true){
			
			$schemeField = $input->readFieldBegin();
			if ($schemeField == null) break;
			$needSkip = true;
			
			
			if ("applyId" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->applyId); 
				
			}
			
			
			
			
			if ("orderReturnGoodsId" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->orderReturnGoodsId); 
				
			}
			
			
			
			
			if ("orderReturnTransportId" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->orderReturnTransportId); 
				
			}
			
			
			
			
			if ("merItemNo" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->merItemNo); 
				
			}
			
			
			
			
			if ("sn" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->sn);
				
			}
			
			
			
			
			if ("goodsName" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->goodsName);
				
			}
			
			
			
			
			if ("salesName" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->salesName);
				
			}
			
			
			
			
			if ("sizeName" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->sizeName);
				
			}
			
			
			
			
			if ("priceId" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->priceId); 
				
			}
			
			
			
			
			if ("vSkuId" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->vSkuId); 
				
			}
			
			
			
			
			if ("goodsVersion" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->goodsVersion); 
				
			}
			
			
			
			
			if ("amount" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->amount); 
				
			}
			
			
			
			
			if ("sellPrice" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->sellPrice);
				
			}
			
			
			
			
			if ("goodsType" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->goodsType);
				
			}
			
			
			
			
			if ("returnReasonId" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->returnReasonId); 
				
			}
			
			
			
			
			if ("ourReasonFlag" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->ourReasonFlag);
				
			}
			
			
			
			
			if ("specialRefund" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->specialRefund);
				
			}
			
			
			
			
			if ("specialRefundReason" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->specialRefundReason); 
				
			}
			
			
			
			
			if ("reasonRemark" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->reasonRemark);
				
			}
			
			
			
			
			if ("goodsBackFlag" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->goodsBackFlag);
				
			}
			
			
			
			
			if ("backTime" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->backTime); 
				
			}
			
			
			
			
			if ("returnReasonDetails" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->returnReasonDetails);
				
			}
			
			
			
			
			if ("isDeleted" == $schemeField){
				
				$needSkip = false;
				$input->readByte($this->isDeleted); 
				
			}
			
			
			
			
			if ("salesNo" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->salesNo);
				
			}
			
			
			
			
			if ("merchandiseNo" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->merchandiseNo); 
				
			}
			
			
			
			
			if ("orderId" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->orderId); 
				
			}
			
			
			
			
			if ("exActSubtotal" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->exActSubtotal);
				
			}
			
			
			
			
			if ("exCouponSubTotal" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->exCouponSubTotal);
				
			}
			
			
			
			
			if ("exPaySubtotal" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->exPaySubtotal);
				
			}
			
			
			
			
			if ("exAllSubtotal" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->exAllSubtotal);
				
			}
			
			
			
			
			if ("addTime" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->addTime); 
				
			}
			
			
			
			
			if ("operatorName" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->operatorName);
				
			}
			
			
			
			
			if ("priceTime" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->priceTime); 
				
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
		
		if($this->applyId !== null) {
			
			$xfer += $output->writeFieldBegin('applyId');
			$xfer += $output->writeI64($this->applyId);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderReturnGoodsId !== null) {
			
			$xfer += $output->writeFieldBegin('orderReturnGoodsId');
			$xfer += $output->writeI64($this->orderReturnGoodsId);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderReturnTransportId !== null) {
			
			$xfer += $output->writeFieldBegin('orderReturnTransportId');
			$xfer += $output->writeI64($this->orderReturnTransportId);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->merItemNo !== null) {
			
			$xfer += $output->writeFieldBegin('merItemNo');
			$xfer += $output->writeI64($this->merItemNo);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->sn !== null) {
			
			$xfer += $output->writeFieldBegin('sn');
			$xfer += $output->writeString($this->sn);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->goodsName !== null) {
			
			$xfer += $output->writeFieldBegin('goodsName');
			$xfer += $output->writeString($this->goodsName);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->salesName !== null) {
			
			$xfer += $output->writeFieldBegin('salesName');
			$xfer += $output->writeString($this->salesName);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->sizeName !== null) {
			
			$xfer += $output->writeFieldBegin('sizeName');
			$xfer += $output->writeString($this->sizeName);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->priceId !== null) {
			
			$xfer += $output->writeFieldBegin('priceId');
			$xfer += $output->writeI64($this->priceId);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->vSkuId !== null) {
			
			$xfer += $output->writeFieldBegin('vSkuId');
			$xfer += $output->writeI64($this->vSkuId);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->goodsVersion !== null) {
			
			$xfer += $output->writeFieldBegin('goodsVersion');
			$xfer += $output->writeI32($this->goodsVersion);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->amount !== null) {
			
			$xfer += $output->writeFieldBegin('amount');
			$xfer += $output->writeI32($this->amount);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->sellPrice !== null) {
			
			$xfer += $output->writeFieldBegin('sellPrice');
			$xfer += $output->writeString($this->sellPrice);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->goodsType !== null) {
			
			$xfer += $output->writeFieldBegin('goodsType');
			$xfer += $output->writeString($this->goodsType);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->returnReasonId !== null) {
			
			$xfer += $output->writeFieldBegin('returnReasonId');
			$xfer += $output->writeI32($this->returnReasonId);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->ourReasonFlag !== null) {
			
			$xfer += $output->writeFieldBegin('ourReasonFlag');
			$xfer += $output->writeString($this->ourReasonFlag);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->specialRefund !== null) {
			
			$xfer += $output->writeFieldBegin('specialRefund');
			$xfer += $output->writeString($this->specialRefund);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->specialRefundReason !== null) {
			
			$xfer += $output->writeFieldBegin('specialRefundReason');
			$xfer += $output->writeI32($this->specialRefundReason);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->reasonRemark !== null) {
			
			$xfer += $output->writeFieldBegin('reasonRemark');
			$xfer += $output->writeString($this->reasonRemark);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->goodsBackFlag !== null) {
			
			$xfer += $output->writeFieldBegin('goodsBackFlag');
			$xfer += $output->writeString($this->goodsBackFlag);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->backTime !== null) {
			
			$xfer += $output->writeFieldBegin('backTime');
			$xfer += $output->writeI64($this->backTime);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->returnReasonDetails !== null) {
			
			$xfer += $output->writeFieldBegin('returnReasonDetails');
			$xfer += $output->writeString($this->returnReasonDetails);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->isDeleted !== null) {
			
			$xfer += $output->writeFieldBegin('isDeleted');
			$xfer += $output->writeByte($this->isDeleted);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->salesNo !== null) {
			
			$xfer += $output->writeFieldBegin('salesNo');
			$xfer += $output->writeString($this->salesNo);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->merchandiseNo !== null) {
			
			$xfer += $output->writeFieldBegin('merchandiseNo');
			$xfer += $output->writeI32($this->merchandiseNo);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderId !== null) {
			
			$xfer += $output->writeFieldBegin('orderId');
			$xfer += $output->writeI64($this->orderId);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->exActSubtotal !== null) {
			
			$xfer += $output->writeFieldBegin('exActSubtotal');
			$xfer += $output->writeString($this->exActSubtotal);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->exCouponSubTotal !== null) {
			
			$xfer += $output->writeFieldBegin('exCouponSubTotal');
			$xfer += $output->writeString($this->exCouponSubTotal);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->exPaySubtotal !== null) {
			
			$xfer += $output->writeFieldBegin('exPaySubtotal');
			$xfer += $output->writeString($this->exPaySubtotal);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->exAllSubtotal !== null) {
			
			$xfer += $output->writeFieldBegin('exAllSubtotal');
			$xfer += $output->writeString($this->exAllSubtotal);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->addTime !== null) {
			
			$xfer += $output->writeFieldBegin('addTime');
			$xfer += $output->writeI64($this->addTime);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->operatorName !== null) {
			
			$xfer += $output->writeFieldBegin('operatorName');
			$xfer += $output->writeString($this->operatorName);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->priceTime !== null) {
			
			$xfer += $output->writeFieldBegin('priceTime');
			$xfer += $output->writeI64($this->priceTime);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}

?>