<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\biz\request;

class RefundMoneyUnit {
	
	static $_TSPEC;
	public $refundType = null;
	public $payType = null;
	public $money = null;
	public $surplus = null;
	public $virtualMoney = null;
	public $couponMoney = null;
	public $couponId = null;
	public $totalPacketMoney = null;
	public $total = null;
	public $remark = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'refundType'
			),
			2 => array(
			'var' => 'payType'
			),
			3 => array(
			'var' => 'money'
			),
			4 => array(
			'var' => 'surplus'
			),
			5 => array(
			'var' => 'virtualMoney'
			),
			6 => array(
			'var' => 'couponMoney'
			),
			7 => array(
			'var' => 'couponId'
			),
			8 => array(
			'var' => 'totalPacketMoney'
			),
			9 => array(
			'var' => 'total'
			),
			10 => array(
			'var' => 'remark'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['refundType'])){
				
				$this->refundType = $vals['refundType'];
			}
			
			
			if (isset($vals['payType'])){
				
				$this->payType = $vals['payType'];
			}
			
			
			if (isset($vals['money'])){
				
				$this->money = $vals['money'];
			}
			
			
			if (isset($vals['surplus'])){
				
				$this->surplus = $vals['surplus'];
			}
			
			
			if (isset($vals['virtualMoney'])){
				
				$this->virtualMoney = $vals['virtualMoney'];
			}
			
			
			if (isset($vals['couponMoney'])){
				
				$this->couponMoney = $vals['couponMoney'];
			}
			
			
			if (isset($vals['couponId'])){
				
				$this->couponId = $vals['couponId'];
			}
			
			
			if (isset($vals['totalPacketMoney'])){
				
				$this->totalPacketMoney = $vals['totalPacketMoney'];
			}
			
			
			if (isset($vals['total'])){
				
				$this->total = $vals['total'];
			}
			
			
			if (isset($vals['remark'])){
				
				$this->remark = $vals['remark'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'RefundMoneyUnit';
	}
	
	public function read($input){
		
		$input->readStructBegin();
		while(true){
			
			$schemeField = $input->readFieldBegin();
			if ($schemeField == null) break;
			$needSkip = true;
			
			
			if ("refundType" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->refundType); 
				
			}
			
			
			
			
			if ("payType" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->payType); 
				
			}
			
			
			
			
			if ("money" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->money);
				
			}
			
			
			
			
			if ("surplus" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->surplus);
				
			}
			
			
			
			
			if ("virtualMoney" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->virtualMoney);
				
			}
			
			
			
			
			if ("couponMoney" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->couponMoney);
				
			}
			
			
			
			
			if ("couponId" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->couponId); 
				
			}
			
			
			
			
			if ("totalPacketMoney" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->totalPacketMoney);
				
			}
			
			
			
			
			if ("total" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->total);
				
			}
			
			
			
			
			if ("remark" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->remark);
				
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
		
		if($this->refundType !== null) {
			
			$xfer += $output->writeFieldBegin('refundType');
			$xfer += $output->writeI32($this->refundType);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->payType !== null) {
			
			$xfer += $output->writeFieldBegin('payType');
			$xfer += $output->writeI32($this->payType);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->money !== null) {
			
			$xfer += $output->writeFieldBegin('money');
			$xfer += $output->writeString($this->money);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->surplus !== null) {
			
			$xfer += $output->writeFieldBegin('surplus');
			$xfer += $output->writeString($this->surplus);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->virtualMoney !== null) {
			
			$xfer += $output->writeFieldBegin('virtualMoney');
			$xfer += $output->writeString($this->virtualMoney);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->couponMoney !== null) {
			
			$xfer += $output->writeFieldBegin('couponMoney');
			$xfer += $output->writeString($this->couponMoney);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->couponId !== null) {
			
			$xfer += $output->writeFieldBegin('couponId');
			$xfer += $output->writeI32($this->couponId);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->totalPacketMoney !== null) {
			
			$xfer += $output->writeFieldBegin('totalPacketMoney');
			$xfer += $output->writeString($this->totalPacketMoney);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->total !== null) {
			
			$xfer += $output->writeFieldBegin('total');
			$xfer += $output->writeString($this->total);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->remark !== null) {
			
			$xfer += $output->writeFieldBegin('remark');
			$xfer += $output->writeString($this->remark);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}

?>