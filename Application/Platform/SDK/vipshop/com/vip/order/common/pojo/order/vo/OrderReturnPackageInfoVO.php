<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\common\pojo\order\vo;

class OrderReturnPackageInfoVO {
	
	static $_TSPEC;
	public $orderReturnTransportId = null;
	public $carriersName = null;
	public $transportNo = null;
	public $carriage = null;
	public $carriagePayType = null;
	public $inpackType = null;
	public $inpackTime = null;
	public $goodsBackWay = null;
	public $hasUpdated = null;
	public $warehouse = null;
	public $applyId = null;
	public $orderSn = null;
	public $operator = null;
	public $ip = null;
	public $getTime = null;
	public $addTime = null;
	public $createTime = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'orderReturnTransportId'
			),
			2 => array(
			'var' => 'carriersName'
			),
			3 => array(
			'var' => 'transportNo'
			),
			4 => array(
			'var' => 'carriage'
			),
			5 => array(
			'var' => 'carriagePayType'
			),
			6 => array(
			'var' => 'inpackType'
			),
			7 => array(
			'var' => 'inpackTime'
			),
			8 => array(
			'var' => 'goodsBackWay'
			),
			9 => array(
			'var' => 'hasUpdated'
			),
			10 => array(
			'var' => 'warehouse'
			),
			11 => array(
			'var' => 'applyId'
			),
			12 => array(
			'var' => 'orderSn'
			),
			13 => array(
			'var' => 'operator'
			),
			14 => array(
			'var' => 'ip'
			),
			15 => array(
			'var' => 'getTime'
			),
			16 => array(
			'var' => 'addTime'
			),
			17 => array(
			'var' => 'createTime'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['orderReturnTransportId'])){
				
				$this->orderReturnTransportId = $vals['orderReturnTransportId'];
			}
			
			
			if (isset($vals['carriersName'])){
				
				$this->carriersName = $vals['carriersName'];
			}
			
			
			if (isset($vals['transportNo'])){
				
				$this->transportNo = $vals['transportNo'];
			}
			
			
			if (isset($vals['carriage'])){
				
				$this->carriage = $vals['carriage'];
			}
			
			
			if (isset($vals['carriagePayType'])){
				
				$this->carriagePayType = $vals['carriagePayType'];
			}
			
			
			if (isset($vals['inpackType'])){
				
				$this->inpackType = $vals['inpackType'];
			}
			
			
			if (isset($vals['inpackTime'])){
				
				$this->inpackTime = $vals['inpackTime'];
			}
			
			
			if (isset($vals['goodsBackWay'])){
				
				$this->goodsBackWay = $vals['goodsBackWay'];
			}
			
			
			if (isset($vals['hasUpdated'])){
				
				$this->hasUpdated = $vals['hasUpdated'];
			}
			
			
			if (isset($vals['warehouse'])){
				
				$this->warehouse = $vals['warehouse'];
			}
			
			
			if (isset($vals['applyId'])){
				
				$this->applyId = $vals['applyId'];
			}
			
			
			if (isset($vals['orderSn'])){
				
				$this->orderSn = $vals['orderSn'];
			}
			
			
			if (isset($vals['operator'])){
				
				$this->operator = $vals['operator'];
			}
			
			
			if (isset($vals['ip'])){
				
				$this->ip = $vals['ip'];
			}
			
			
			if (isset($vals['getTime'])){
				
				$this->getTime = $vals['getTime'];
			}
			
			
			if (isset($vals['addTime'])){
				
				$this->addTime = $vals['addTime'];
			}
			
			
			if (isset($vals['createTime'])){
				
				$this->createTime = $vals['createTime'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'OrderReturnPackageInfoVO';
	}
	
	public function read($input){
		
		$input->readStructBegin();
		while(true){
			
			$schemeField = $input->readFieldBegin();
			if ($schemeField == null) break;
			$needSkip = true;
			
			
			if ("orderReturnTransportId" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->orderReturnTransportId); 
				
			}
			
			
			
			
			if ("carriersName" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->carriersName);
				
			}
			
			
			
			
			if ("transportNo" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->transportNo);
				
			}
			
			
			
			
			if ("carriage" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->carriage);
				
			}
			
			
			
			
			if ("carriagePayType" == $schemeField){
				
				$needSkip = false;
				$input->readByte($this->carriagePayType); 
				
			}
			
			
			
			
			if ("inpackType" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->inpackType);
				
			}
			
			
			
			
			if ("inpackTime" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->inpackTime); 
				
			}
			
			
			
			
			if ("goodsBackWay" == $schemeField){
				
				$needSkip = false;
				$input->readI16($this->goodsBackWay); 
				
			}
			
			
			
			
			if ("hasUpdated" == $schemeField){
				
				$needSkip = false;
				$input->readByte($this->hasUpdated); 
				
			}
			
			
			
			
			if ("warehouse" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->warehouse);
				
			}
			
			
			
			
			if ("applyId" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->applyId); 
				
			}
			
			
			
			
			if ("orderSn" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->orderSn);
				
			}
			
			
			
			
			if ("operator" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->operator);
				
			}
			
			
			
			
			if ("ip" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->ip);
				
			}
			
			
			
			
			if ("getTime" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->getTime); 
				
			}
			
			
			
			
			if ("addTime" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->addTime); 
				
			}
			
			
			
			
			if ("createTime" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->createTime); 
				
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
		
		if($this->orderReturnTransportId !== null) {
			
			$xfer += $output->writeFieldBegin('orderReturnTransportId');
			$xfer += $output->writeI64($this->orderReturnTransportId);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->carriersName !== null) {
			
			$xfer += $output->writeFieldBegin('carriersName');
			$xfer += $output->writeString($this->carriersName);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->transportNo !== null) {
			
			$xfer += $output->writeFieldBegin('transportNo');
			$xfer += $output->writeString($this->transportNo);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->carriage !== null) {
			
			$xfer += $output->writeFieldBegin('carriage');
			$xfer += $output->writeString($this->carriage);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->carriagePayType !== null) {
			
			$xfer += $output->writeFieldBegin('carriagePayType');
			$xfer += $output->writeByte($this->carriagePayType);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->inpackType !== null) {
			
			$xfer += $output->writeFieldBegin('inpackType');
			$xfer += $output->writeString($this->inpackType);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->inpackTime !== null) {
			
			$xfer += $output->writeFieldBegin('inpackTime');
			$xfer += $output->writeI64($this->inpackTime);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->goodsBackWay !== null) {
			
			$xfer += $output->writeFieldBegin('goodsBackWay');
			$xfer += $output->writeI16($this->goodsBackWay);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->hasUpdated !== null) {
			
			$xfer += $output->writeFieldBegin('hasUpdated');
			$xfer += $output->writeByte($this->hasUpdated);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->warehouse !== null) {
			
			$xfer += $output->writeFieldBegin('warehouse');
			$xfer += $output->writeString($this->warehouse);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->applyId !== null) {
			
			$xfer += $output->writeFieldBegin('applyId');
			$xfer += $output->writeI64($this->applyId);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderSn !== null) {
			
			$xfer += $output->writeFieldBegin('orderSn');
			$xfer += $output->writeString($this->orderSn);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->operator !== null) {
			
			$xfer += $output->writeFieldBegin('operator');
			$xfer += $output->writeString($this->operator);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->ip !== null) {
			
			$xfer += $output->writeFieldBegin('ip');
			$xfer += $output->writeString($this->ip);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->getTime !== null) {
			
			$xfer += $output->writeFieldBegin('getTime');
			$xfer += $output->writeI64($this->getTime);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->addTime !== null) {
			
			$xfer += $output->writeFieldBegin('addTime');
			$xfer += $output->writeI64($this->addTime);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->createTime !== null) {
			
			$xfer += $output->writeFieldBegin('createTime');
			$xfer += $output->writeI64($this->createTime);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}

?>