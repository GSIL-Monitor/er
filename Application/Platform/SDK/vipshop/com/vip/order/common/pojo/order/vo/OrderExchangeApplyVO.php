<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\common\pojo\order\vo;

class OrderExchangeApplyVO {
	
	static $_TSPEC;
	public $applyId = null;
	public $userId = null;
	public $orderSn = null;
	public $orderId = null;
	public $operator = null;
	public $remark = null;
	public $orderAfterStatus = null;
	public $orderAfterStatusUpdtime = null;
	public $applyTime = null;
	public $newOrderSn = null;
	public $returnMethod = null;
	public $exchangeProcessStatus = null;
	public $returnTransportNo = null;
	public $updateTime = null;
	public $ip = null;
	public $subExchangeStatus = null;
	public $returnsPayType = null;
	public $returnWarehouse = null;
	public $afterSaleSn = null;
	public $afterSaleStatus = null;
	public $isDeleted = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'applyId'
			),
			2 => array(
			'var' => 'userId'
			),
			3 => array(
			'var' => 'orderSn'
			),
			4 => array(
			'var' => 'orderId'
			),
			5 => array(
			'var' => 'operator'
			),
			6 => array(
			'var' => 'remark'
			),
			7 => array(
			'var' => 'orderAfterStatus'
			),
			8 => array(
			'var' => 'orderAfterStatusUpdtime'
			),
			9 => array(
			'var' => 'applyTime'
			),
			10 => array(
			'var' => 'newOrderSn'
			),
			11 => array(
			'var' => 'returnMethod'
			),
			12 => array(
			'var' => 'exchangeProcessStatus'
			),
			13 => array(
			'var' => 'returnTransportNo'
			),
			14 => array(
			'var' => 'updateTime'
			),
			15 => array(
			'var' => 'ip'
			),
			16 => array(
			'var' => 'subExchangeStatus'
			),
			17 => array(
			'var' => 'returnsPayType'
			),
			18 => array(
			'var' => 'returnWarehouse'
			),
			19 => array(
			'var' => 'afterSaleSn'
			),
			20 => array(
			'var' => 'afterSaleStatus'
			),
			21 => array(
			'var' => 'isDeleted'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['applyId'])){
				
				$this->applyId = $vals['applyId'];
			}
			
			
			if (isset($vals['userId'])){
				
				$this->userId = $vals['userId'];
			}
			
			
			if (isset($vals['orderSn'])){
				
				$this->orderSn = $vals['orderSn'];
			}
			
			
			if (isset($vals['orderId'])){
				
				$this->orderId = $vals['orderId'];
			}
			
			
			if (isset($vals['operator'])){
				
				$this->operator = $vals['operator'];
			}
			
			
			if (isset($vals['remark'])){
				
				$this->remark = $vals['remark'];
			}
			
			
			if (isset($vals['orderAfterStatus'])){
				
				$this->orderAfterStatus = $vals['orderAfterStatus'];
			}
			
			
			if (isset($vals['orderAfterStatusUpdtime'])){
				
				$this->orderAfterStatusUpdtime = $vals['orderAfterStatusUpdtime'];
			}
			
			
			if (isset($vals['applyTime'])){
				
				$this->applyTime = $vals['applyTime'];
			}
			
			
			if (isset($vals['newOrderSn'])){
				
				$this->newOrderSn = $vals['newOrderSn'];
			}
			
			
			if (isset($vals['returnMethod'])){
				
				$this->returnMethod = $vals['returnMethod'];
			}
			
			
			if (isset($vals['exchangeProcessStatus'])){
				
				$this->exchangeProcessStatus = $vals['exchangeProcessStatus'];
			}
			
			
			if (isset($vals['returnTransportNo'])){
				
				$this->returnTransportNo = $vals['returnTransportNo'];
			}
			
			
			if (isset($vals['updateTime'])){
				
				$this->updateTime = $vals['updateTime'];
			}
			
			
			if (isset($vals['ip'])){
				
				$this->ip = $vals['ip'];
			}
			
			
			if (isset($vals['subExchangeStatus'])){
				
				$this->subExchangeStatus = $vals['subExchangeStatus'];
			}
			
			
			if (isset($vals['returnsPayType'])){
				
				$this->returnsPayType = $vals['returnsPayType'];
			}
			
			
			if (isset($vals['returnWarehouse'])){
				
				$this->returnWarehouse = $vals['returnWarehouse'];
			}
			
			
			if (isset($vals['afterSaleSn'])){
				
				$this->afterSaleSn = $vals['afterSaleSn'];
			}
			
			
			if (isset($vals['afterSaleStatus'])){
				
				$this->afterSaleStatus = $vals['afterSaleStatus'];
			}
			
			
			if (isset($vals['isDeleted'])){
				
				$this->isDeleted = $vals['isDeleted'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'OrderExchangeApplyVO';
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
			
			
			
			
			if ("userId" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->userId); 
				
			}
			
			
			
			
			if ("orderSn" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->orderSn);
				
			}
			
			
			
			
			if ("orderId" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->orderId); 
				
			}
			
			
			
			
			if ("operator" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->operator);
				
			}
			
			
			
			
			if ("remark" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->remark);
				
			}
			
			
			
			
			if ("orderAfterStatus" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->orderAfterStatus); 
				
			}
			
			
			
			
			if ("orderAfterStatusUpdtime" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->orderAfterStatusUpdtime); 
				
			}
			
			
			
			
			if ("applyTime" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->applyTime); 
				
			}
			
			
			
			
			if ("newOrderSn" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->newOrderSn);
				
			}
			
			
			
			
			if ("returnMethod" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->returnMethod); 
				
			}
			
			
			
			
			if ("exchangeProcessStatus" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->exchangeProcessStatus); 
				
			}
			
			
			
			
			if ("returnTransportNo" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->returnTransportNo);
				
			}
			
			
			
			
			if ("updateTime" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->updateTime); 
				
			}
			
			
			
			
			if ("ip" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->ip);
				
			}
			
			
			
			
			if ("subExchangeStatus" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->subExchangeStatus); 
				
			}
			
			
			
			
			if ("returnsPayType" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->returnsPayType); 
				
			}
			
			
			
			
			if ("returnWarehouse" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->returnWarehouse);
				
			}
			
			
			
			
			if ("afterSaleSn" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->afterSaleSn);
				
			}
			
			
			
			
			if ("afterSaleStatus" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->afterSaleStatus); 
				
			}
			
			
			
			
			if ("isDeleted" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->isDeleted); 
				
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
		
		
		if($this->userId !== null) {
			
			$xfer += $output->writeFieldBegin('userId');
			$xfer += $output->writeI64($this->userId);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderSn !== null) {
			
			$xfer += $output->writeFieldBegin('orderSn');
			$xfer += $output->writeString($this->orderSn);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderId !== null) {
			
			$xfer += $output->writeFieldBegin('orderId');
			$xfer += $output->writeI64($this->orderId);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->operator !== null) {
			
			$xfer += $output->writeFieldBegin('operator');
			$xfer += $output->writeString($this->operator);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->remark !== null) {
			
			$xfer += $output->writeFieldBegin('remark');
			$xfer += $output->writeString($this->remark);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderAfterStatus !== null) {
			
			$xfer += $output->writeFieldBegin('orderAfterStatus');
			$xfer += $output->writeI32($this->orderAfterStatus);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderAfterStatusUpdtime !== null) {
			
			$xfer += $output->writeFieldBegin('orderAfterStatusUpdtime');
			$xfer += $output->writeI64($this->orderAfterStatusUpdtime);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->applyTime !== null) {
			
			$xfer += $output->writeFieldBegin('applyTime');
			$xfer += $output->writeI64($this->applyTime);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->newOrderSn !== null) {
			
			$xfer += $output->writeFieldBegin('newOrderSn');
			$xfer += $output->writeString($this->newOrderSn);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->returnMethod !== null) {
			
			$xfer += $output->writeFieldBegin('returnMethod');
			$xfer += $output->writeI32($this->returnMethod);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->exchangeProcessStatus !== null) {
			
			$xfer += $output->writeFieldBegin('exchangeProcessStatus');
			$xfer += $output->writeI32($this->exchangeProcessStatus);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->returnTransportNo !== null) {
			
			$xfer += $output->writeFieldBegin('returnTransportNo');
			$xfer += $output->writeString($this->returnTransportNo);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->updateTime !== null) {
			
			$xfer += $output->writeFieldBegin('updateTime');
			$xfer += $output->writeI64($this->updateTime);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->ip !== null) {
			
			$xfer += $output->writeFieldBegin('ip');
			$xfer += $output->writeString($this->ip);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->subExchangeStatus !== null) {
			
			$xfer += $output->writeFieldBegin('subExchangeStatus');
			$xfer += $output->writeI32($this->subExchangeStatus);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->returnsPayType !== null) {
			
			$xfer += $output->writeFieldBegin('returnsPayType');
			$xfer += $output->writeI32($this->returnsPayType);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->returnWarehouse !== null) {
			
			$xfer += $output->writeFieldBegin('returnWarehouse');
			$xfer += $output->writeString($this->returnWarehouse);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->afterSaleSn !== null) {
			
			$xfer += $output->writeFieldBegin('afterSaleSn');
			$xfer += $output->writeString($this->afterSaleSn);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->afterSaleStatus !== null) {
			
			$xfer += $output->writeFieldBegin('afterSaleStatus');
			$xfer += $output->writeI32($this->afterSaleStatus);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->isDeleted !== null) {
			
			$xfer += $output->writeFieldBegin('isDeleted');
			$xfer += $output->writeI32($this->isDeleted);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}

?>