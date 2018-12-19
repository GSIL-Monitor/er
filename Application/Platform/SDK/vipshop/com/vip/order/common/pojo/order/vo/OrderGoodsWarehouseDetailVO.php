<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\common\pojo\order\vo;

class OrderGoodsWarehouseDetailVO {
	
	static $_TSPEC;
	public $sizeId = null;
	public $bondedWarehouse = null;
	public $sourceWarehouseDetail = null;
	public $createTime = null;
	public $sizeSn = null;
	public $brandId = null;
	public $saleStyle = null;
	public $vrOrderType = null;
	public $isDelete = null;
	public $orderId = null;
	public $orderSn = null;
	public $userId = null;
	public $parentSn = null;
	public $merItemNo = null;
	public $salesNo = null;
	public $id = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'sizeId'
			),
			2 => array(
			'var' => 'bondedWarehouse'
			),
			3 => array(
			'var' => 'sourceWarehouseDetail'
			),
			4 => array(
			'var' => 'createTime'
			),
			5 => array(
			'var' => 'sizeSn'
			),
			6 => array(
			'var' => 'brandId'
			),
			7 => array(
			'var' => 'saleStyle'
			),
			8 => array(
			'var' => 'vrOrderType'
			),
			9 => array(
			'var' => 'isDelete'
			),
			10 => array(
			'var' => 'orderId'
			),
			11 => array(
			'var' => 'orderSn'
			),
			12 => array(
			'var' => 'userId'
			),
			13 => array(
			'var' => 'parentSn'
			),
			14 => array(
			'var' => 'merItemNo'
			),
			15 => array(
			'var' => 'salesNo'
			),
			16 => array(
			'var' => 'id'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['sizeId'])){
				
				$this->sizeId = $vals['sizeId'];
			}
			
			
			if (isset($vals['bondedWarehouse'])){
				
				$this->bondedWarehouse = $vals['bondedWarehouse'];
			}
			
			
			if (isset($vals['sourceWarehouseDetail'])){
				
				$this->sourceWarehouseDetail = $vals['sourceWarehouseDetail'];
			}
			
			
			if (isset($vals['createTime'])){
				
				$this->createTime = $vals['createTime'];
			}
			
			
			if (isset($vals['sizeSn'])){
				
				$this->sizeSn = $vals['sizeSn'];
			}
			
			
			if (isset($vals['brandId'])){
				
				$this->brandId = $vals['brandId'];
			}
			
			
			if (isset($vals['saleStyle'])){
				
				$this->saleStyle = $vals['saleStyle'];
			}
			
			
			if (isset($vals['vrOrderType'])){
				
				$this->vrOrderType = $vals['vrOrderType'];
			}
			
			
			if (isset($vals['isDelete'])){
				
				$this->isDelete = $vals['isDelete'];
			}
			
			
			if (isset($vals['orderId'])){
				
				$this->orderId = $vals['orderId'];
			}
			
			
			if (isset($vals['orderSn'])){
				
				$this->orderSn = $vals['orderSn'];
			}
			
			
			if (isset($vals['userId'])){
				
				$this->userId = $vals['userId'];
			}
			
			
			if (isset($vals['parentSn'])){
				
				$this->parentSn = $vals['parentSn'];
			}
			
			
			if (isset($vals['merItemNo'])){
				
				$this->merItemNo = $vals['merItemNo'];
			}
			
			
			if (isset($vals['salesNo'])){
				
				$this->salesNo = $vals['salesNo'];
			}
			
			
			if (isset($vals['id'])){
				
				$this->id = $vals['id'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'OrderGoodsWarehouseDetailVO';
	}
	
	public function read($input){
		
		$input->readStructBegin();
		while(true){
			
			$schemeField = $input->readFieldBegin();
			if ($schemeField == null) break;
			$needSkip = true;
			
			
			if ("sizeId" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->sizeId); 
				
			}
			
			
			
			
			if ("bondedWarehouse" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->bondedWarehouse);
				
			}
			
			
			
			
			if ("sourceWarehouseDetail" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->sourceWarehouseDetail);
				
			}
			
			
			
			
			if ("createTime" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->createTime); 
				
			}
			
			
			
			
			if ("sizeSn" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->sizeSn);
				
			}
			
			
			
			
			if ("brandId" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->brandId); 
				
			}
			
			
			
			
			if ("saleStyle" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->saleStyle); 
				
			}
			
			
			
			
			if ("vrOrderType" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->vrOrderType); 
				
			}
			
			
			
			
			if ("isDelete" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->isDelete); 
				
			}
			
			
			
			
			if ("orderId" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->orderId); 
				
			}
			
			
			
			
			if ("orderSn" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->orderSn);
				
			}
			
			
			
			
			if ("userId" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->userId); 
				
			}
			
			
			
			
			if ("parentSn" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->parentSn);
				
			}
			
			
			
			
			if ("merItemNo" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->merItemNo); 
				
			}
			
			
			
			
			if ("salesNo" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->salesNo); 
				
			}
			
			
			
			
			if ("id" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->id); 
				
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
		
		if($this->sizeId !== null) {
			
			$xfer += $output->writeFieldBegin('sizeId');
			$xfer += $output->writeI64($this->sizeId);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->bondedWarehouse !== null) {
			
			$xfer += $output->writeFieldBegin('bondedWarehouse');
			$xfer += $output->writeString($this->bondedWarehouse);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->sourceWarehouseDetail !== null) {
			
			$xfer += $output->writeFieldBegin('sourceWarehouseDetail');
			$xfer += $output->writeString($this->sourceWarehouseDetail);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->createTime !== null) {
			
			$xfer += $output->writeFieldBegin('createTime');
			$xfer += $output->writeI64($this->createTime);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->sizeSn !== null) {
			
			$xfer += $output->writeFieldBegin('sizeSn');
			$xfer += $output->writeString($this->sizeSn);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->brandId !== null) {
			
			$xfer += $output->writeFieldBegin('brandId');
			$xfer += $output->writeI32($this->brandId);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->saleStyle !== null) {
			
			$xfer += $output->writeFieldBegin('saleStyle');
			$xfer += $output->writeI32($this->saleStyle);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->vrOrderType !== null) {
			
			$xfer += $output->writeFieldBegin('vrOrderType');
			$xfer += $output->writeI32($this->vrOrderType);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->isDelete !== null) {
			
			$xfer += $output->writeFieldBegin('isDelete');
			$xfer += $output->writeI32($this->isDelete);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderId !== null) {
			
			$xfer += $output->writeFieldBegin('orderId');
			$xfer += $output->writeI64($this->orderId);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderSn !== null) {
			
			$xfer += $output->writeFieldBegin('orderSn');
			$xfer += $output->writeString($this->orderSn);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->userId !== null) {
			
			$xfer += $output->writeFieldBegin('userId');
			$xfer += $output->writeI64($this->userId);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->parentSn !== null) {
			
			$xfer += $output->writeFieldBegin('parentSn');
			$xfer += $output->writeString($this->parentSn);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->merItemNo !== null) {
			
			$xfer += $output->writeFieldBegin('merItemNo');
			$xfer += $output->writeI64($this->merItemNo);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->salesNo !== null) {
			
			$xfer += $output->writeFieldBegin('salesNo');
			$xfer += $output->writeI64($this->salesNo);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->id !== null) {
			
			$xfer += $output->writeFieldBegin('id');
			$xfer += $output->writeI64($this->id);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}

?>