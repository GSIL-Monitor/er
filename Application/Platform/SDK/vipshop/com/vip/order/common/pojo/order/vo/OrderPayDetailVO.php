<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\common\pojo\order\vo;

class OrderPayDetailVO {
	
	static $_TSPEC;
	public $payStatus = null;
	public $payType = null;
	public $payTime = null;
	public $paySn = null;
	public $payOperation = null;
	public $payMoney = null;
	public $payId = null;
	public $payAccount = null;
	public $orderScenario = null;
	public $currency = null;
	public $orderId = null;
	public $payDetailId = null;
	public $originalOrderSn = null;
	public $period = null;
	public $originalPaySn = null;
	public $refundWay = null;
	public $refundRequestSn = null;
	public $createTime = null;
	public $updateTime = null;
	public $id = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'payStatus'
			),
			2 => array(
			'var' => 'payType'
			),
			3 => array(
			'var' => 'payTime'
			),
			4 => array(
			'var' => 'paySn'
			),
			5 => array(
			'var' => 'payOperation'
			),
			6 => array(
			'var' => 'payMoney'
			),
			7 => array(
			'var' => 'payId'
			),
			8 => array(
			'var' => 'payAccount'
			),
			9 => array(
			'var' => 'orderScenario'
			),
			10 => array(
			'var' => 'currency'
			),
			11 => array(
			'var' => 'orderId'
			),
			12 => array(
			'var' => 'payDetailId'
			),
			13 => array(
			'var' => 'originalOrderSn'
			),
			14 => array(
			'var' => 'period'
			),
			15 => array(
			'var' => 'originalPaySn'
			),
			16 => array(
			'var' => 'refundWay'
			),
			17 => array(
			'var' => 'refundRequestSn'
			),
			18 => array(
			'var' => 'createTime'
			),
			19 => array(
			'var' => 'updateTime'
			),
			20 => array(
			'var' => 'id'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['payStatus'])){
				
				$this->payStatus = $vals['payStatus'];
			}
			
			
			if (isset($vals['payType'])){
				
				$this->payType = $vals['payType'];
			}
			
			
			if (isset($vals['payTime'])){
				
				$this->payTime = $vals['payTime'];
			}
			
			
			if (isset($vals['paySn'])){
				
				$this->paySn = $vals['paySn'];
			}
			
			
			if (isset($vals['payOperation'])){
				
				$this->payOperation = $vals['payOperation'];
			}
			
			
			if (isset($vals['payMoney'])){
				
				$this->payMoney = $vals['payMoney'];
			}
			
			
			if (isset($vals['payId'])){
				
				$this->payId = $vals['payId'];
			}
			
			
			if (isset($vals['payAccount'])){
				
				$this->payAccount = $vals['payAccount'];
			}
			
			
			if (isset($vals['orderScenario'])){
				
				$this->orderScenario = $vals['orderScenario'];
			}
			
			
			if (isset($vals['currency'])){
				
				$this->currency = $vals['currency'];
			}
			
			
			if (isset($vals['orderId'])){
				
				$this->orderId = $vals['orderId'];
			}
			
			
			if (isset($vals['payDetailId'])){
				
				$this->payDetailId = $vals['payDetailId'];
			}
			
			
			if (isset($vals['originalOrderSn'])){
				
				$this->originalOrderSn = $vals['originalOrderSn'];
			}
			
			
			if (isset($vals['period'])){
				
				$this->period = $vals['period'];
			}
			
			
			if (isset($vals['originalPaySn'])){
				
				$this->originalPaySn = $vals['originalPaySn'];
			}
			
			
			if (isset($vals['refundWay'])){
				
				$this->refundWay = $vals['refundWay'];
			}
			
			
			if (isset($vals['refundRequestSn'])){
				
				$this->refundRequestSn = $vals['refundRequestSn'];
			}
			
			
			if (isset($vals['createTime'])){
				
				$this->createTime = $vals['createTime'];
			}
			
			
			if (isset($vals['updateTime'])){
				
				$this->updateTime = $vals['updateTime'];
			}
			
			
			if (isset($vals['id'])){
				
				$this->id = $vals['id'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'OrderPayDetailVO';
	}
	
	public function read($input){
		
		$input->readStructBegin();
		while(true){
			
			$schemeField = $input->readFieldBegin();
			if ($schemeField == null) break;
			$needSkip = true;
			
			
			if ("payStatus" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->payStatus); 
				
			}
			
			
			
			
			if ("payType" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->payType); 
				
			}
			
			
			
			
			if ("payTime" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->payTime); 
				
			}
			
			
			
			
			if ("paySn" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->paySn);
				
			}
			
			
			
			
			if ("payOperation" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->payOperation); 
				
			}
			
			
			
			
			if ("payMoney" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->payMoney);
				
			}
			
			
			
			
			if ("payId" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->payId); 
				
			}
			
			
			
			
			if ("payAccount" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->payAccount);
				
			}
			
			
			
			
			if ("orderScenario" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->orderScenario); 
				
			}
			
			
			
			
			if ("currency" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->currency);
				
			}
			
			
			
			
			if ("orderId" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->orderId); 
				
			}
			
			
			
			
			if ("payDetailId" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->payDetailId); 
				
			}
			
			
			
			
			if ("originalOrderSn" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->originalOrderSn);
				
			}
			
			
			
			
			if ("period" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->period); 
				
			}
			
			
			
			
			if ("originalPaySn" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->originalPaySn);
				
			}
			
			
			
			
			if ("refundWay" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->refundWay); 
				
			}
			
			
			
			
			if ("refundRequestSn" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->refundRequestSn);
				
			}
			
			
			
			
			if ("createTime" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->createTime); 
				
			}
			
			
			
			
			if ("updateTime" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->updateTime); 
				
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
		
		if($this->payStatus !== null) {
			
			$xfer += $output->writeFieldBegin('payStatus');
			$xfer += $output->writeI32($this->payStatus);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->payType !== null) {
			
			$xfer += $output->writeFieldBegin('payType');
			$xfer += $output->writeI32($this->payType);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->payTime !== null) {
			
			$xfer += $output->writeFieldBegin('payTime');
			$xfer += $output->writeI64($this->payTime);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->paySn !== null) {
			
			$xfer += $output->writeFieldBegin('paySn');
			$xfer += $output->writeString($this->paySn);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->payOperation !== null) {
			
			$xfer += $output->writeFieldBegin('payOperation');
			$xfer += $output->writeI32($this->payOperation);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->payMoney !== null) {
			
			$xfer += $output->writeFieldBegin('payMoney');
			$xfer += $output->writeString($this->payMoney);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->payId !== null) {
			
			$xfer += $output->writeFieldBegin('payId');
			$xfer += $output->writeI32($this->payId);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->payAccount !== null) {
			
			$xfer += $output->writeFieldBegin('payAccount');
			$xfer += $output->writeString($this->payAccount);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderScenario !== null) {
			
			$xfer += $output->writeFieldBegin('orderScenario');
			$xfer += $output->writeI32($this->orderScenario);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->currency !== null) {
			
			$xfer += $output->writeFieldBegin('currency');
			$xfer += $output->writeString($this->currency);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderId !== null) {
			
			$xfer += $output->writeFieldBegin('orderId');
			$xfer += $output->writeI64($this->orderId);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->payDetailId !== null) {
			
			$xfer += $output->writeFieldBegin('payDetailId');
			$xfer += $output->writeI64($this->payDetailId);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->originalOrderSn !== null) {
			
			$xfer += $output->writeFieldBegin('originalOrderSn');
			$xfer += $output->writeString($this->originalOrderSn);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->period !== null) {
			
			$xfer += $output->writeFieldBegin('period');
			$xfer += $output->writeI32($this->period);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->originalPaySn !== null) {
			
			$xfer += $output->writeFieldBegin('originalPaySn');
			$xfer += $output->writeString($this->originalPaySn);
			
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