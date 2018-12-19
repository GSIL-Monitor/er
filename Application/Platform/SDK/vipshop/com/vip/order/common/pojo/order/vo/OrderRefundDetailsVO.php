<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\common\pojo\order\vo;

class OrderRefundDetailsVO {
	
	static $_TSPEC;
	public $orderId = null;
	public $applyId = null;
	public $orderScenario = null;
	public $payOperation = null;
	public $payType = null;
	public $payId = null;
	public $paySn = null;
	public $refundSn = null;
	public $payAccount = null;
	public $refundTime = null;
	public $currency = null;
	public $refundMoney = null;
	public $refundStatus = null;
	public $payTypeName = null;
	public $orderSn = null;
	public $type = null;
	public $createTime = null;
	public $updateTime = null;
	public $isDeleted = null;
	public $refundWay = null;
	public $refundRequestSn = null;
	public $originalOrderSn = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			12 => array(
			'var' => 'orderId'
			),
			13 => array(
			'var' => 'applyId'
			),
			1 => array(
			'var' => 'orderScenario'
			),
			2 => array(
			'var' => 'payOperation'
			),
			3 => array(
			'var' => 'payType'
			),
			4 => array(
			'var' => 'payId'
			),
			5 => array(
			'var' => 'paySn'
			),
			6 => array(
			'var' => 'refundSn'
			),
			7 => array(
			'var' => 'payAccount'
			),
			8 => array(
			'var' => 'refundTime'
			),
			9 => array(
			'var' => 'currency'
			),
			10 => array(
			'var' => 'refundMoney'
			),
			11 => array(
			'var' => 'refundStatus'
			),
			14 => array(
			'var' => 'payTypeName'
			),
			15 => array(
			'var' => 'orderSn'
			),
			16 => array(
			'var' => 'type'
			),
			17 => array(
			'var' => 'createTime'
			),
			18 => array(
			'var' => 'updateTime'
			),
			19 => array(
			'var' => 'isDeleted'
			),
			20 => array(
			'var' => 'refundWay'
			),
			21 => array(
			'var' => 'refundRequestSn'
			),
			22 => array(
			'var' => 'originalOrderSn'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['orderId'])){
				
				$this->orderId = $vals['orderId'];
			}
			
			
			if (isset($vals['applyId'])){
				
				$this->applyId = $vals['applyId'];
			}
			
			
			if (isset($vals['orderScenario'])){
				
				$this->orderScenario = $vals['orderScenario'];
			}
			
			
			if (isset($vals['payOperation'])){
				
				$this->payOperation = $vals['payOperation'];
			}
			
			
			if (isset($vals['payType'])){
				
				$this->payType = $vals['payType'];
			}
			
			
			if (isset($vals['payId'])){
				
				$this->payId = $vals['payId'];
			}
			
			
			if (isset($vals['paySn'])){
				
				$this->paySn = $vals['paySn'];
			}
			
			
			if (isset($vals['refundSn'])){
				
				$this->refundSn = $vals['refundSn'];
			}
			
			
			if (isset($vals['payAccount'])){
				
				$this->payAccount = $vals['payAccount'];
			}
			
			
			if (isset($vals['refundTime'])){
				
				$this->refundTime = $vals['refundTime'];
			}
			
			
			if (isset($vals['currency'])){
				
				$this->currency = $vals['currency'];
			}
			
			
			if (isset($vals['refundMoney'])){
				
				$this->refundMoney = $vals['refundMoney'];
			}
			
			
			if (isset($vals['refundStatus'])){
				
				$this->refundStatus = $vals['refundStatus'];
			}
			
			
			if (isset($vals['payTypeName'])){
				
				$this->payTypeName = $vals['payTypeName'];
			}
			
			
			if (isset($vals['orderSn'])){
				
				$this->orderSn = $vals['orderSn'];
			}
			
			
			if (isset($vals['type'])){
				
				$this->type = $vals['type'];
			}
			
			
			if (isset($vals['createTime'])){
				
				$this->createTime = $vals['createTime'];
			}
			
			
			if (isset($vals['updateTime'])){
				
				$this->updateTime = $vals['updateTime'];
			}
			
			
			if (isset($vals['isDeleted'])){
				
				$this->isDeleted = $vals['isDeleted'];
			}
			
			
			if (isset($vals['refundWay'])){
				
				$this->refundWay = $vals['refundWay'];
			}
			
			
			if (isset($vals['refundRequestSn'])){
				
				$this->refundRequestSn = $vals['refundRequestSn'];
			}
			
			
			if (isset($vals['originalOrderSn'])){
				
				$this->originalOrderSn = $vals['originalOrderSn'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'OrderRefundDetailsVO';
	}
	
	public function read($input){
		
		$input->readStructBegin();
		while(true){
			
			$schemeField = $input->readFieldBegin();
			if ($schemeField == null) break;
			$needSkip = true;
			
			
			if ("orderId" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->orderId); 
				
			}
			
			
			
			
			if ("applyId" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->applyId); 
				
			}
			
			
			
			
			if ("orderScenario" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->orderScenario); 
				
			}
			
			
			
			
			if ("payOperation" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->payOperation); 
				
			}
			
			
			
			
			if ("payType" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->payType); 
				
			}
			
			
			
			
			if ("payId" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->payId); 
				
			}
			
			
			
			
			if ("paySn" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->paySn);
				
			}
			
			
			
			
			if ("refundSn" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->refundSn);
				
			}
			
			
			
			
			if ("payAccount" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->payAccount);
				
			}
			
			
			
			
			if ("refundTime" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->refundTime); 
				
			}
			
			
			
			
			if ("currency" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->currency);
				
			}
			
			
			
			
			if ("refundMoney" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->refundMoney);
				
			}
			
			
			
			
			if ("refundStatus" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->refundStatus); 
				
			}
			
			
			
			
			if ("payTypeName" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->payTypeName);
				
			}
			
			
			
			
			if ("orderSn" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->orderSn);
				
			}
			
			
			
			
			if ("type" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->type); 
				
			}
			
			
			
			
			if ("createTime" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->createTime); 
				
			}
			
			
			
			
			if ("updateTime" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->updateTime); 
				
			}
			
			
			
			
			if ("isDeleted" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->isDeleted); 
				
			}
			
			
			
			
			if ("refundWay" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->refundWay); 
				
			}
			
			
			
			
			if ("refundRequestSn" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->refundRequestSn);
				
			}
			
			
			
			
			if ("originalOrderSn" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->originalOrderSn);
				
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
		
		if($this->orderId !== null) {
			
			$xfer += $output->writeFieldBegin('orderId');
			$xfer += $output->writeI64($this->orderId);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->applyId !== null) {
			
			$xfer += $output->writeFieldBegin('applyId');
			$xfer += $output->writeI64($this->applyId);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderScenario !== null) {
			
			$xfer += $output->writeFieldBegin('orderScenario');
			$xfer += $output->writeI32($this->orderScenario);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->payOperation !== null) {
			
			$xfer += $output->writeFieldBegin('payOperation');
			$xfer += $output->writeI32($this->payOperation);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->payType !== null) {
			
			$xfer += $output->writeFieldBegin('payType');
			$xfer += $output->writeI32($this->payType);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->payId !== null) {
			
			$xfer += $output->writeFieldBegin('payId');
			$xfer += $output->writeI32($this->payId);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->paySn !== null) {
			
			$xfer += $output->writeFieldBegin('paySn');
			$xfer += $output->writeString($this->paySn);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->refundSn !== null) {
			
			$xfer += $output->writeFieldBegin('refundSn');
			$xfer += $output->writeString($this->refundSn);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->payAccount !== null) {
			
			$xfer += $output->writeFieldBegin('payAccount');
			$xfer += $output->writeString($this->payAccount);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->refundTime !== null) {
			
			$xfer += $output->writeFieldBegin('refundTime');
			$xfer += $output->writeI64($this->refundTime);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->currency !== null) {
			
			$xfer += $output->writeFieldBegin('currency');
			$xfer += $output->writeString($this->currency);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->refundMoney !== null) {
			
			$xfer += $output->writeFieldBegin('refundMoney');
			$xfer += $output->writeString($this->refundMoney);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->refundStatus !== null) {
			
			$xfer += $output->writeFieldBegin('refundStatus');
			$xfer += $output->writeI32($this->refundStatus);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->payTypeName !== null) {
			
			$xfer += $output->writeFieldBegin('payTypeName');
			$xfer += $output->writeString($this->payTypeName);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderSn !== null) {
			
			$xfer += $output->writeFieldBegin('orderSn');
			$xfer += $output->writeString($this->orderSn);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->type !== null) {
			
			$xfer += $output->writeFieldBegin('type');
			$xfer += $output->writeI32($this->type);
			
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
		
		
		if($this->isDeleted !== null) {
			
			$xfer += $output->writeFieldBegin('isDeleted');
			$xfer += $output->writeI32($this->isDeleted);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->refundWay !== null) {
			
			$xfer += $output->writeFieldBegin('refundWay');
			$xfer += $output->writeI32($this->refundWay);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->refundRequestSn !== null) {
			
			$xfer += $output->writeFieldBegin('refundRequestSn');
			$xfer += $output->writeString($this->refundRequestSn);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->originalOrderSn !== null) {
			
			$xfer += $output->writeFieldBegin('originalOrderSn');
			$xfer += $output->writeString($this->originalOrderSn);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}

?>