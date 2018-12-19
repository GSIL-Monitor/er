<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\common\pojo\order\vo;

class OrderGoodsVO {
	
	static $_TSPEC;
	public $orderGoodsId = null;
	public $merchandiseNo = null;
	public $amount = null;
	public $priceId = null;
	public $skuId = null;
	public $goodsVersion = null;
	public $salesNo = null;
	public $merItemNo = null;
	public $sn = null;
	public $sellPrice = null;
	public $goodsType = null;
	public $presentId = null;
	public $presentName = null;
	public $remark = null;
	public $isDelete = null;
	public $goodsStatus = null;
	public $totalAomunt = null;
	public $exActSubtotal = null;
	public $exCouponSubTotal = null;
	public $exPaySubtotal = null;
	public $exAllSubtotal = null;
	public $subOrderSn = null;
	public $saleStyle = null;
	public $brandWarehouse = null;
	public $bondedWarehouse = null;
	public $orderWarehouse = null;
	public $goodsSubtotal = null;
	public $posNo = null;
	public $allocateId = null;
	public $priceTime = null;
	public $isCjcLarge = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'orderGoodsId'
			),
			2 => array(
			'var' => 'merchandiseNo'
			),
			3 => array(
			'var' => 'amount'
			),
			4 => array(
			'var' => 'priceId'
			),
			5 => array(
			'var' => 'skuId'
			),
			6 => array(
			'var' => 'goodsVersion'
			),
			7 => array(
			'var' => 'salesNo'
			),
			8 => array(
			'var' => 'merItemNo'
			),
			9 => array(
			'var' => 'sn'
			),
			10 => array(
			'var' => 'sellPrice'
			),
			11 => array(
			'var' => 'goodsType'
			),
			12 => array(
			'var' => 'presentId'
			),
			13 => array(
			'var' => 'presentName'
			),
			14 => array(
			'var' => 'remark'
			),
			15 => array(
			'var' => 'isDelete'
			),
			16 => array(
			'var' => 'goodsStatus'
			),
			17 => array(
			'var' => 'totalAomunt'
			),
			18 => array(
			'var' => 'exActSubtotal'
			),
			19 => array(
			'var' => 'exCouponSubTotal'
			),
			20 => array(
			'var' => 'exPaySubtotal'
			),
			21 => array(
			'var' => 'exAllSubtotal'
			),
			22 => array(
			'var' => 'subOrderSn'
			),
			23 => array(
			'var' => 'saleStyle'
			),
			24 => array(
			'var' => 'brandWarehouse'
			),
			25 => array(
			'var' => 'bondedWarehouse'
			),
			26 => array(
			'var' => 'orderWarehouse'
			),
			27 => array(
			'var' => 'goodsSubtotal'
			),
			28 => array(
			'var' => 'posNo'
			),
			29 => array(
			'var' => 'allocateId'
			),
			30 => array(
			'var' => 'priceTime'
			),
			31 => array(
			'var' => 'isCjcLarge'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['orderGoodsId'])){
				
				$this->orderGoodsId = $vals['orderGoodsId'];
			}
			
			
			if (isset($vals['merchandiseNo'])){
				
				$this->merchandiseNo = $vals['merchandiseNo'];
			}
			
			
			if (isset($vals['amount'])){
				
				$this->amount = $vals['amount'];
			}
			
			
			if (isset($vals['priceId'])){
				
				$this->priceId = $vals['priceId'];
			}
			
			
			if (isset($vals['skuId'])){
				
				$this->skuId = $vals['skuId'];
			}
			
			
			if (isset($vals['goodsVersion'])){
				
				$this->goodsVersion = $vals['goodsVersion'];
			}
			
			
			if (isset($vals['salesNo'])){
				
				$this->salesNo = $vals['salesNo'];
			}
			
			
			if (isset($vals['merItemNo'])){
				
				$this->merItemNo = $vals['merItemNo'];
			}
			
			
			if (isset($vals['sn'])){
				
				$this->sn = $vals['sn'];
			}
			
			
			if (isset($vals['sellPrice'])){
				
				$this->sellPrice = $vals['sellPrice'];
			}
			
			
			if (isset($vals['goodsType'])){
				
				$this->goodsType = $vals['goodsType'];
			}
			
			
			if (isset($vals['presentId'])){
				
				$this->presentId = $vals['presentId'];
			}
			
			
			if (isset($vals['presentName'])){
				
				$this->presentName = $vals['presentName'];
			}
			
			
			if (isset($vals['remark'])){
				
				$this->remark = $vals['remark'];
			}
			
			
			if (isset($vals['isDelete'])){
				
				$this->isDelete = $vals['isDelete'];
			}
			
			
			if (isset($vals['goodsStatus'])){
				
				$this->goodsStatus = $vals['goodsStatus'];
			}
			
			
			if (isset($vals['totalAomunt'])){
				
				$this->totalAomunt = $vals['totalAomunt'];
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
			
			
			if (isset($vals['subOrderSn'])){
				
				$this->subOrderSn = $vals['subOrderSn'];
			}
			
			
			if (isset($vals['saleStyle'])){
				
				$this->saleStyle = $vals['saleStyle'];
			}
			
			
			if (isset($vals['brandWarehouse'])){
				
				$this->brandWarehouse = $vals['brandWarehouse'];
			}
			
			
			if (isset($vals['bondedWarehouse'])){
				
				$this->bondedWarehouse = $vals['bondedWarehouse'];
			}
			
			
			if (isset($vals['orderWarehouse'])){
				
				$this->orderWarehouse = $vals['orderWarehouse'];
			}
			
			
			if (isset($vals['goodsSubtotal'])){
				
				$this->goodsSubtotal = $vals['goodsSubtotal'];
			}
			
			
			if (isset($vals['posNo'])){
				
				$this->posNo = $vals['posNo'];
			}
			
			
			if (isset($vals['allocateId'])){
				
				$this->allocateId = $vals['allocateId'];
			}
			
			
			if (isset($vals['priceTime'])){
				
				$this->priceTime = $vals['priceTime'];
			}
			
			
			if (isset($vals['isCjcLarge'])){
				
				$this->isCjcLarge = $vals['isCjcLarge'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'OrderGoodsVO';
	}
	
	public function read($input){
		
		$input->readStructBegin();
		while(true){
			
			$schemeField = $input->readFieldBegin();
			if ($schemeField == null) break;
			$needSkip = true;
			
			
			if ("orderGoodsId" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->orderGoodsId); 
				
			}
			
			
			
			
			if ("merchandiseNo" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->merchandiseNo); 
				
			}
			
			
			
			
			if ("amount" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->amount); 
				
			}
			
			
			
			
			if ("priceId" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->priceId); 
				
			}
			
			
			
			
			if ("skuId" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->skuId); 
				
			}
			
			
			
			
			if ("goodsVersion" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->goodsVersion); 
				
			}
			
			
			
			
			if ("salesNo" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->salesNo); 
				
			}
			
			
			
			
			if ("merItemNo" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->merItemNo); 
				
			}
			
			
			
			
			if ("sn" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->sn);
				
			}
			
			
			
			
			if ("sellPrice" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->sellPrice);
				
			}
			
			
			
			
			if ("goodsType" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->goodsType); 
				
			}
			
			
			
			
			if ("presentId" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->presentId); 
				
			}
			
			
			
			
			if ("presentName" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->presentName);
				
			}
			
			
			
			
			if ("remark" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->remark);
				
			}
			
			
			
			
			if ("isDelete" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->isDelete); 
				
			}
			
			
			
			
			if ("goodsStatus" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->goodsStatus); 
				
			}
			
			
			
			
			if ("totalAomunt" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->totalAomunt); 
				
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
			
			
			
			
			if ("subOrderSn" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->subOrderSn);
				
			}
			
			
			
			
			if ("saleStyle" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->saleStyle); 
				
			}
			
			
			
			
			if ("brandWarehouse" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->brandWarehouse);
				
			}
			
			
			
			
			if ("bondedWarehouse" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->bondedWarehouse);
				
			}
			
			
			
			
			if ("orderWarehouse" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->orderWarehouse);
				
			}
			
			
			
			
			if ("goodsSubtotal" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->goodsSubtotal);
				
			}
			
			
			
			
			if ("posNo" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->posNo);
				
			}
			
			
			
			
			if ("allocateId" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->allocateId); 
				
			}
			
			
			
			
			if ("priceTime" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->priceTime); 
				
			}
			
			
			
			
			if ("isCjcLarge" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->isCjcLarge); 
				
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
		
		if($this->orderGoodsId !== null) {
			
			$xfer += $output->writeFieldBegin('orderGoodsId');
			$xfer += $output->writeI64($this->orderGoodsId);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->merchandiseNo !== null) {
			
			$xfer += $output->writeFieldBegin('merchandiseNo');
			$xfer += $output->writeI64($this->merchandiseNo);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->amount !== null) {
			
			$xfer += $output->writeFieldBegin('amount');
			$xfer += $output->writeI32($this->amount);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->priceId !== null) {
			
			$xfer += $output->writeFieldBegin('priceId');
			$xfer += $output->writeI64($this->priceId);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->skuId !== null) {
			
			$xfer += $output->writeFieldBegin('skuId');
			$xfer += $output->writeI64($this->skuId);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->goodsVersion !== null) {
			
			$xfer += $output->writeFieldBegin('goodsVersion');
			$xfer += $output->writeI32($this->goodsVersion);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->salesNo !== null) {
			
			$xfer += $output->writeFieldBegin('salesNo');
			$xfer += $output->writeI64($this->salesNo);
			
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
		
		
		if($this->sellPrice !== null) {
			
			$xfer += $output->writeFieldBegin('sellPrice');
			$xfer += $output->writeString($this->sellPrice);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->goodsType !== null) {
			
			$xfer += $output->writeFieldBegin('goodsType');
			$xfer += $output->writeI32($this->goodsType);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->presentId !== null) {
			
			$xfer += $output->writeFieldBegin('presentId');
			$xfer += $output->writeI32($this->presentId);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->presentName !== null) {
			
			$xfer += $output->writeFieldBegin('presentName');
			$xfer += $output->writeString($this->presentName);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->remark !== null) {
			
			$xfer += $output->writeFieldBegin('remark');
			$xfer += $output->writeString($this->remark);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->isDelete !== null) {
			
			$xfer += $output->writeFieldBegin('isDelete');
			$xfer += $output->writeI32($this->isDelete);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->goodsStatus !== null) {
			
			$xfer += $output->writeFieldBegin('goodsStatus');
			$xfer += $output->writeI32($this->goodsStatus);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->totalAomunt !== null) {
			
			$xfer += $output->writeFieldBegin('totalAomunt');
			$xfer += $output->writeI32($this->totalAomunt);
			
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
		
		
		if($this->subOrderSn !== null) {
			
			$xfer += $output->writeFieldBegin('subOrderSn');
			$xfer += $output->writeString($this->subOrderSn);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->saleStyle !== null) {
			
			$xfer += $output->writeFieldBegin('saleStyle');
			$xfer += $output->writeI32($this->saleStyle);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->brandWarehouse !== null) {
			
			$xfer += $output->writeFieldBegin('brandWarehouse');
			$xfer += $output->writeString($this->brandWarehouse);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->bondedWarehouse !== null) {
			
			$xfer += $output->writeFieldBegin('bondedWarehouse');
			$xfer += $output->writeString($this->bondedWarehouse);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderWarehouse !== null) {
			
			$xfer += $output->writeFieldBegin('orderWarehouse');
			$xfer += $output->writeString($this->orderWarehouse);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->goodsSubtotal !== null) {
			
			$xfer += $output->writeFieldBegin('goodsSubtotal');
			$xfer += $output->writeString($this->goodsSubtotal);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->posNo !== null) {
			
			$xfer += $output->writeFieldBegin('posNo');
			$xfer += $output->writeString($this->posNo);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->allocateId !== null) {
			
			$xfer += $output->writeFieldBegin('allocateId');
			$xfer += $output->writeI64($this->allocateId);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->priceTime !== null) {
			
			$xfer += $output->writeFieldBegin('priceTime');
			$xfer += $output->writeI64($this->priceTime);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->isCjcLarge !== null) {
			
			$xfer += $output->writeFieldBegin('isCjcLarge');
			$xfer += $output->writeI32($this->isCjcLarge);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}

?>