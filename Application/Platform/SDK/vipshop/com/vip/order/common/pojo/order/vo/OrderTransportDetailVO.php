<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\common\pojo\order\vo;

class OrderTransportDetailVO {
	
	static $_TSPEC;
	public $transportId = null;
	public $transportNo = null;
	public $transportCode = null;
	public $transportDetail = null;
	public $carriersCode = null;
	public $carriersShortName = null;
	public $carriersName = null;
	public $carriersType = null;
	public $warehouse = null;
	public $payType = null;
	public $extPayTypeCode = null;
	public $extPayType = null;
	public $orderSn = null;
	public $time = null;
	public $backSn = null;
	public $id = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'transportId'
			),
			2 => array(
			'var' => 'transportNo'
			),
			3 => array(
			'var' => 'transportCode'
			),
			4 => array(
			'var' => 'transportDetail'
			),
			5 => array(
			'var' => 'carriersCode'
			),
			6 => array(
			'var' => 'carriersShortName'
			),
			7 => array(
			'var' => 'carriersName'
			),
			8 => array(
			'var' => 'carriersType'
			),
			9 => array(
			'var' => 'warehouse'
			),
			10 => array(
			'var' => 'payType'
			),
			11 => array(
			'var' => 'extPayTypeCode'
			),
			12 => array(
			'var' => 'extPayType'
			),
			13 => array(
			'var' => 'orderSn'
			),
			14 => array(
			'var' => 'time'
			),
			15 => array(
			'var' => 'backSn'
			),
			16 => array(
			'var' => 'id'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['transportId'])){
				
				$this->transportId = $vals['transportId'];
			}
			
			
			if (isset($vals['transportNo'])){
				
				$this->transportNo = $vals['transportNo'];
			}
			
			
			if (isset($vals['transportCode'])){
				
				$this->transportCode = $vals['transportCode'];
			}
			
			
			if (isset($vals['transportDetail'])){
				
				$this->transportDetail = $vals['transportDetail'];
			}
			
			
			if (isset($vals['carriersCode'])){
				
				$this->carriersCode = $vals['carriersCode'];
			}
			
			
			if (isset($vals['carriersShortName'])){
				
				$this->carriersShortName = $vals['carriersShortName'];
			}
			
			
			if (isset($vals['carriersName'])){
				
				$this->carriersName = $vals['carriersName'];
			}
			
			
			if (isset($vals['carriersType'])){
				
				$this->carriersType = $vals['carriersType'];
			}
			
			
			if (isset($vals['warehouse'])){
				
				$this->warehouse = $vals['warehouse'];
			}
			
			
			if (isset($vals['payType'])){
				
				$this->payType = $vals['payType'];
			}
			
			
			if (isset($vals['extPayTypeCode'])){
				
				$this->extPayTypeCode = $vals['extPayTypeCode'];
			}
			
			
			if (isset($vals['extPayType'])){
				
				$this->extPayType = $vals['extPayType'];
			}
			
			
			if (isset($vals['orderSn'])){
				
				$this->orderSn = $vals['orderSn'];
			}
			
			
			if (isset($vals['time'])){
				
				$this->time = $vals['time'];
			}
			
			
			if (isset($vals['backSn'])){
				
				$this->backSn = $vals['backSn'];
			}
			
			
			if (isset($vals['id'])){
				
				$this->id = $vals['id'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'OrderTransportDetailVO';
	}
	
	public function read($input){
		
		$input->readStructBegin();
		while(true){
			
			$schemeField = $input->readFieldBegin();
			if ($schemeField == null) break;
			$needSkip = true;
			
			
			if ("transportId" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->transportId); 
				
			}
			
			
			
			
			if ("transportNo" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->transportNo);
				
			}
			
			
			
			
			if ("transportCode" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->transportCode);
				
			}
			
			
			
			
			if ("transportDetail" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->transportDetail);
				
			}
			
			
			
			
			if ("carriersCode" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->carriersCode);
				
			}
			
			
			
			
			if ("carriersShortName" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->carriersShortName);
				
			}
			
			
			
			
			if ("carriersName" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->carriersName);
				
			}
			
			
			
			
			if ("carriersType" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->carriersType);
				
			}
			
			
			
			
			if ("warehouse" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->warehouse);
				
			}
			
			
			
			
			if ("payType" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->payType); 
				
			}
			
			
			
			
			if ("extPayTypeCode" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->extPayTypeCode);
				
			}
			
			
			
			
			if ("extPayType" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->extPayType); 
				
			}
			
			
			
			
			if ("orderSn" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->orderSn);
				
			}
			
			
			
			
			if ("time" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->time); 
				
			}
			
			
			
			
			if ("backSn" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->backSn);
				
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
		
		if($this->transportId !== null) {
			
			$xfer += $output->writeFieldBegin('transportId');
			$xfer += $output->writeI32($this->transportId);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->transportNo !== null) {
			
			$xfer += $output->writeFieldBegin('transportNo');
			$xfer += $output->writeString($this->transportNo);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->transportCode !== null) {
			
			$xfer += $output->writeFieldBegin('transportCode');
			$xfer += $output->writeString($this->transportCode);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->transportDetail !== null) {
			
			$xfer += $output->writeFieldBegin('transportDetail');
			$xfer += $output->writeString($this->transportDetail);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->carriersCode !== null) {
			
			$xfer += $output->writeFieldBegin('carriersCode');
			$xfer += $output->writeString($this->carriersCode);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->carriersShortName !== null) {
			
			$xfer += $output->writeFieldBegin('carriersShortName');
			$xfer += $output->writeString($this->carriersShortName);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->carriersName !== null) {
			
			$xfer += $output->writeFieldBegin('carriersName');
			$xfer += $output->writeString($this->carriersName);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->carriersType !== null) {
			
			$xfer += $output->writeFieldBegin('carriersType');
			$xfer += $output->writeString($this->carriersType);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->warehouse !== null) {
			
			$xfer += $output->writeFieldBegin('warehouse');
			$xfer += $output->writeString($this->warehouse);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->payType !== null) {
			
			$xfer += $output->writeFieldBegin('payType');
			$xfer += $output->writeI32($this->payType);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->extPayTypeCode !== null) {
			
			$xfer += $output->writeFieldBegin('extPayTypeCode');
			$xfer += $output->writeString($this->extPayTypeCode);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->extPayType !== null) {
			
			$xfer += $output->writeFieldBegin('extPayType');
			$xfer += $output->writeI32($this->extPayType);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderSn !== null) {
			
			$xfer += $output->writeFieldBegin('orderSn');
			$xfer += $output->writeString($this->orderSn);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->time !== null) {
			
			$xfer += $output->writeFieldBegin('time');
			$xfer += $output->writeI64($this->time);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->backSn !== null) {
			
			$xfer += $output->writeFieldBegin('backSn');
			$xfer += $output->writeString($this->backSn);
			
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