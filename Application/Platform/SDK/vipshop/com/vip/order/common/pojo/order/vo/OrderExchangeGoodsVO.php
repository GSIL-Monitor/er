<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\common\pojo\order\vo;

class OrderExchangeGoodsVO {
	
	static $_TSPEC;
	public $orderExchangeGoodsId = null;
	public $orderSn = null;
	public $applyId = null;
	public $reasonId = null;
	public $orderExchangeTransportId = null;
	public $merItemNo = null;
	public $merchandiseNo = null;
	public $salesNo = null;
	public $sn = null;
	public $goodsName = null;
	public $salesName = null;
	public $sizeName = null;
	public $amount = null;
	public $newMerItemNo = null;
	public $newMerchandiseNo = null;
	public $newSalesNo = null;
	public $sellPrice = null;
	public $goodsType = null;
	public $goodsBackFlag = null;
	public $backTime = null;
	public $stockSource = null;
	public $stockState = null;
	public $isDeleted = null;
	public $createTime = null;
	public $updateTime = null;
	public $newSizeName = null;
	public $remark = null;
	public $priceId = null;
	public $vSkuId = null;
	public $newPriceId = null;
	public $newVSkuId = null;
	public $newSn = null;
	public $newAmount = null;
	public $posNo = null;
	public $priceTime = null;
	public $newPriceTime = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'orderExchangeGoodsId'
			),
			2 => array(
			'var' => 'orderSn'
			),
			3 => array(
			'var' => 'applyId'
			),
			4 => array(
			'var' => 'reasonId'
			),
			5 => array(
			'var' => 'orderExchangeTransportId'
			),
			6 => array(
			'var' => 'merItemNo'
			),
			7 => array(
			'var' => 'merchandiseNo'
			),
			8 => array(
			'var' => 'salesNo'
			),
			9 => array(
			'var' => 'sn'
			),
			10 => array(
			'var' => 'goodsName'
			),
			11 => array(
			'var' => 'salesName'
			),
			12 => array(
			'var' => 'sizeName'
			),
			13 => array(
			'var' => 'amount'
			),
			14 => array(
			'var' => 'newMerItemNo'
			),
			15 => array(
			'var' => 'newMerchandiseNo'
			),
			16 => array(
			'var' => 'newSalesNo'
			),
			17 => array(
			'var' => 'sellPrice'
			),
			18 => array(
			'var' => 'goodsType'
			),
			19 => array(
			'var' => 'goodsBackFlag'
			),
			20 => array(
			'var' => 'backTime'
			),
			21 => array(
			'var' => 'stockSource'
			),
			22 => array(
			'var' => 'stockState'
			),
			23 => array(
			'var' => 'isDeleted'
			),
			24 => array(
			'var' => 'createTime'
			),
			25 => array(
			'var' => 'updateTime'
			),
			26 => array(
			'var' => 'newSizeName'
			),
			27 => array(
			'var' => 'remark'
			),
			28 => array(
			'var' => 'priceId'
			),
			29 => array(
			'var' => 'vSkuId'
			),
			30 => array(
			'var' => 'newPriceId'
			),
			31 => array(
			'var' => 'newVSkuId'
			),
			32 => array(
			'var' => 'newSn'
			),
			33 => array(
			'var' => 'newAmount'
			),
			34 => array(
			'var' => 'posNo'
			),
			35 => array(
			'var' => 'priceTime'
			),
			36 => array(
			'var' => 'newPriceTime'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['orderExchangeGoodsId'])){
				
				$this->orderExchangeGoodsId = $vals['orderExchangeGoodsId'];
			}
			
			
			if (isset($vals['orderSn'])){
				
				$this->orderSn = $vals['orderSn'];
			}
			
			
			if (isset($vals['applyId'])){
				
				$this->applyId = $vals['applyId'];
			}
			
			
			if (isset($vals['reasonId'])){
				
				$this->reasonId = $vals['reasonId'];
			}
			
			
			if (isset($vals['orderExchangeTransportId'])){
				
				$this->orderExchangeTransportId = $vals['orderExchangeTransportId'];
			}
			
			
			if (isset($vals['merItemNo'])){
				
				$this->merItemNo = $vals['merItemNo'];
			}
			
			
			if (isset($vals['merchandiseNo'])){
				
				$this->merchandiseNo = $vals['merchandiseNo'];
			}
			
			
			if (isset($vals['salesNo'])){
				
				$this->salesNo = $vals['salesNo'];
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
			
			
			if (isset($vals['amount'])){
				
				$this->amount = $vals['amount'];
			}
			
			
			if (isset($vals['newMerItemNo'])){
				
				$this->newMerItemNo = $vals['newMerItemNo'];
			}
			
			
			if (isset($vals['newMerchandiseNo'])){
				
				$this->newMerchandiseNo = $vals['newMerchandiseNo'];
			}
			
			
			if (isset($vals['newSalesNo'])){
				
				$this->newSalesNo = $vals['newSalesNo'];
			}
			
			
			if (isset($vals['sellPrice'])){
				
				$this->sellPrice = $vals['sellPrice'];
			}
			
			
			if (isset($vals['goodsType'])){
				
				$this->goodsType = $vals['goodsType'];
			}
			
			
			if (isset($vals['goodsBackFlag'])){
				
				$this->goodsBackFlag = $vals['goodsBackFlag'];
			}
			
			
			if (isset($vals['backTime'])){
				
				$this->backTime = $vals['backTime'];
			}
			
			
			if (isset($vals['stockSource'])){
				
				$this->stockSource = $vals['stockSource'];
			}
			
			
			if (isset($vals['stockState'])){
				
				$this->stockState = $vals['stockState'];
			}
			
			
			if (isset($vals['isDeleted'])){
				
				$this->isDeleted = $vals['isDeleted'];
			}
			
			
			if (isset($vals['createTime'])){
				
				$this->createTime = $vals['createTime'];
			}
			
			
			if (isset($vals['updateTime'])){
				
				$this->updateTime = $vals['updateTime'];
			}
			
			
			if (isset($vals['newSizeName'])){
				
				$this->newSizeName = $vals['newSizeName'];
			}
			
			
			if (isset($vals['remark'])){
				
				$this->remark = $vals['remark'];
			}
			
			
			if (isset($vals['priceId'])){
				
				$this->priceId = $vals['priceId'];
			}
			
			
			if (isset($vals['vSkuId'])){
				
				$this->vSkuId = $vals['vSkuId'];
			}
			
			
			if (isset($vals['newPriceId'])){
				
				$this->newPriceId = $vals['newPriceId'];
			}
			
			
			if (isset($vals['newVSkuId'])){
				
				$this->newVSkuId = $vals['newVSkuId'];
			}
			
			
			if (isset($vals['newSn'])){
				
				$this->newSn = $vals['newSn'];
			}
			
			
			if (isset($vals['newAmount'])){
				
				$this->newAmount = $vals['newAmount'];
			}
			
			
			if (isset($vals['posNo'])){
				
				$this->posNo = $vals['posNo'];
			}
			
			
			if (isset($vals['priceTime'])){
				
				$this->priceTime = $vals['priceTime'];
			}
			
			
			if (isset($vals['newPriceTime'])){
				
				$this->newPriceTime = $vals['newPriceTime'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'OrderExchangeGoodsVO';
	}
	
	public function read($input){
		
		$input->readStructBegin();
		while(true){
			
			$schemeField = $input->readFieldBegin();
			if ($schemeField == null) break;
			$needSkip = true;
			
			
			if ("orderExchangeGoodsId" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->orderExchangeGoodsId); 
				
			}
			
			
			
			
			if ("orderSn" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->orderSn);
				
			}
			
			
			
			
			if ("applyId" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->applyId); 
				
			}
			
			
			
			
			if ("reasonId" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->reasonId); 
				
			}
			
			
			
			
			if ("orderExchangeTransportId" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->orderExchangeTransportId); 
				
			}
			
			
			
			
			if ("merItemNo" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->merItemNo); 
				
			}
			
			
			
			
			if ("merchandiseNo" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->merchandiseNo); 
				
			}
			
			
			
			
			if ("salesNo" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->salesNo); 
				
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
			
			
			
			
			if ("amount" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->amount); 
				
			}
			
			
			
			
			if ("newMerItemNo" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->newMerItemNo); 
				
			}
			
			
			
			
			if ("newMerchandiseNo" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->newMerchandiseNo); 
				
			}
			
			
			
			
			if ("newSalesNo" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->newSalesNo); 
				
			}
			
			
			
			
			if ("sellPrice" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->sellPrice);
				
			}
			
			
			
			
			if ("goodsType" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->goodsType);
				
			}
			
			
			
			
			if ("goodsBackFlag" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->goodsBackFlag);
				
			}
			
			
			
			
			if ("backTime" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->backTime); 
				
			}
			
			
			
			
			if ("stockSource" == $schemeField){
				
				$needSkip = false;
				$input->readByte($this->stockSource); 
				
			}
			
			
			
			
			if ("stockState" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->stockState);
				
			}
			
			
			
			
			if ("isDeleted" == $schemeField){
				
				$needSkip = false;
				$input->readByte($this->isDeleted); 
				
			}
			
			
			
			
			if ("createTime" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->createTime); 
				
			}
			
			
			
			
			if ("updateTime" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->updateTime); 
				
			}
			
			
			
			
			if ("newSizeName" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->newSizeName);
				
			}
			
			
			
			
			if ("remark" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->remark);
				
			}
			
			
			
			
			if ("priceId" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->priceId); 
				
			}
			
			
			
			
			if ("vSkuId" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->vSkuId); 
				
			}
			
			
			
			
			if ("newPriceId" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->newPriceId); 
				
			}
			
			
			
			
			if ("newVSkuId" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->newVSkuId); 
				
			}
			
			
			
			
			if ("newSn" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->newSn);
				
			}
			
			
			
			
			if ("newAmount" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->newAmount);
				
			}
			
			
			
			
			if ("posNo" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->posNo);
				
			}
			
			
			
			
			if ("priceTime" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->priceTime); 
				
			}
			
			
			
			
			if ("newPriceTime" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->newPriceTime); 
				
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
		
		if($this->orderExchangeGoodsId !== null) {
			
			$xfer += $output->writeFieldBegin('orderExchangeGoodsId');
			$xfer += $output->writeI64($this->orderExchangeGoodsId);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderSn !== null) {
			
			$xfer += $output->writeFieldBegin('orderSn');
			$xfer += $output->writeString($this->orderSn);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->applyId !== null) {
			
			$xfer += $output->writeFieldBegin('applyId');
			$xfer += $output->writeI64($this->applyId);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->reasonId !== null) {
			
			$xfer += $output->writeFieldBegin('reasonId');
			$xfer += $output->writeI32($this->reasonId);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderExchangeTransportId !== null) {
			
			$xfer += $output->writeFieldBegin('orderExchangeTransportId');
			$xfer += $output->writeI64($this->orderExchangeTransportId);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->merItemNo !== null) {
			
			$xfer += $output->writeFieldBegin('merItemNo');
			$xfer += $output->writeI64($this->merItemNo);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->merchandiseNo !== null) {
			
			$xfer += $output->writeFieldBegin('merchandiseNo');
			$xfer += $output->writeI64($this->merchandiseNo);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->salesNo !== null) {
			
			$xfer += $output->writeFieldBegin('salesNo');
			$xfer += $output->writeI64($this->salesNo);
			
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
		
		
		if($this->amount !== null) {
			
			$xfer += $output->writeFieldBegin('amount');
			$xfer += $output->writeI32($this->amount);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->newMerItemNo !== null) {
			
			$xfer += $output->writeFieldBegin('newMerItemNo');
			$xfer += $output->writeI64($this->newMerItemNo);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->newMerchandiseNo !== null) {
			
			$xfer += $output->writeFieldBegin('newMerchandiseNo');
			$xfer += $output->writeI64($this->newMerchandiseNo);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->newSalesNo !== null) {
			
			$xfer += $output->writeFieldBegin('newSalesNo');
			$xfer += $output->writeI64($this->newSalesNo);
			
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
		
		
		if($this->stockSource !== null) {
			
			$xfer += $output->writeFieldBegin('stockSource');
			$xfer += $output->writeByte($this->stockSource);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->stockState !== null) {
			
			$xfer += $output->writeFieldBegin('stockState');
			$xfer += $output->writeString($this->stockState);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->isDeleted !== null) {
			
			$xfer += $output->writeFieldBegin('isDeleted');
			$xfer += $output->writeByte($this->isDeleted);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->createTime !== null) {
			
			$xfer += $output->writeFieldBegin('createTime');
			$xfer += $output->writeI64($this->createTime);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->updateTime !== null) {
			
			$xfer += $output->writeFieldBegin('updateTime');
			$xfer += $output->writeI64($this->updateTime);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->newSizeName !== null) {
			
			$xfer += $output->writeFieldBegin('newSizeName');
			$xfer += $output->writeString($this->newSizeName);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->remark !== null) {
			
			$xfer += $output->writeFieldBegin('remark');
			$xfer += $output->writeString($this->remark);
			
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
		
		
		if($this->newPriceId !== null) {
			
			$xfer += $output->writeFieldBegin('newPriceId');
			$xfer += $output->writeI64($this->newPriceId);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->newVSkuId !== null) {
			
			$xfer += $output->writeFieldBegin('newVSkuId');
			$xfer += $output->writeI64($this->newVSkuId);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->newSn !== null) {
			
			$xfer += $output->writeFieldBegin('newSn');
			$xfer += $output->writeString($this->newSn);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->newAmount !== null) {
			
			$xfer += $output->writeFieldBegin('newAmount');
			$xfer += $output->writeString($this->newAmount);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->posNo !== null) {
			
			$xfer += $output->writeFieldBegin('posNo');
			$xfer += $output->writeString($this->posNo);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->priceTime !== null) {
			
			$xfer += $output->writeFieldBegin('priceTime');
			$xfer += $output->writeI64($this->priceTime);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->newPriceTime !== null) {
			
			$xfer += $output->writeFieldBegin('newPriceTime');
			$xfer += $output->writeI64($this->newPriceTime);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}

?>