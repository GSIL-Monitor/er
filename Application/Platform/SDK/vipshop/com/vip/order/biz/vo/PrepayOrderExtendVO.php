<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\biz\vo;

class PrepayOrderExtendVO {
	
	static $_TSPEC;
	public $parentSn = null;
	public $orderCode = null;
	public $periods = null;
	public $isFirst = null;
	public $isLast = null;
	public $isLock = null;
	public $payId = null;
	public $totalMoney = null;
	public $startPayTime = null;
	public $endPayTime = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'parentSn'
			),
			2 => array(
			'var' => 'orderCode'
			),
			3 => array(
			'var' => 'periods'
			),
			4 => array(
			'var' => 'isFirst'
			),
			5 => array(
			'var' => 'isLast'
			),
			6 => array(
			'var' => 'isLock'
			),
			7 => array(
			'var' => 'payId'
			),
			8 => array(
			'var' => 'totalMoney'
			),
			9 => array(
			'var' => 'startPayTime'
			),
			10 => array(
			'var' => 'endPayTime'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['parentSn'])){
				
				$this->parentSn = $vals['parentSn'];
			}
			
			
			if (isset($vals['orderCode'])){
				
				$this->orderCode = $vals['orderCode'];
			}
			
			
			if (isset($vals['periods'])){
				
				$this->periods = $vals['periods'];
			}
			
			
			if (isset($vals['isFirst'])){
				
				$this->isFirst = $vals['isFirst'];
			}
			
			
			if (isset($vals['isLast'])){
				
				$this->isLast = $vals['isLast'];
			}
			
			
			if (isset($vals['isLock'])){
				
				$this->isLock = $vals['isLock'];
			}
			
			
			if (isset($vals['payId'])){
				
				$this->payId = $vals['payId'];
			}
			
			
			if (isset($vals['totalMoney'])){
				
				$this->totalMoney = $vals['totalMoney'];
			}
			
			
			if (isset($vals['startPayTime'])){
				
				$this->startPayTime = $vals['startPayTime'];
			}
			
			
			if (isset($vals['endPayTime'])){
				
				$this->endPayTime = $vals['endPayTime'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'PrepayOrderExtendVO';
	}
	
	public function read($input){
		
		$input->readStructBegin();
		while(true){
			
			$schemeField = $input->readFieldBegin();
			if ($schemeField == null) break;
			$needSkip = true;
			
			
			if ("parentSn" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->parentSn);
				
			}
			
			
			
			
			if ("orderCode" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->orderCode);
				
			}
			
			
			
			
			if ("periods" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->periods); 
				
			}
			
			
			
			
			if ("isFirst" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->isFirst); 
				
			}
			
			
			
			
			if ("isLast" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->isLast); 
				
			}
			
			
			
			
			if ("isLock" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->isLock); 
				
			}
			
			
			
			
			if ("payId" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->payId); 
				
			}
			
			
			
			
			if ("totalMoney" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->totalMoney);
				
			}
			
			
			
			
			if ("startPayTime" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->startPayTime); 
				
			}
			
			
			
			
			if ("endPayTime" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->endPayTime); 
				
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
		
		if($this->parentSn !== null) {
			
			$xfer += $output->writeFieldBegin('parentSn');
			$xfer += $output->writeString($this->parentSn);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderCode !== null) {
			
			$xfer += $output->writeFieldBegin('orderCode');
			$xfer += $output->writeString($this->orderCode);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->periods !== null) {
			
			$xfer += $output->writeFieldBegin('periods');
			$xfer += $output->writeI32($this->periods);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->isFirst !== null) {
			
			$xfer += $output->writeFieldBegin('isFirst');
			$xfer += $output->writeI32($this->isFirst);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->isLast !== null) {
			
			$xfer += $output->writeFieldBegin('isLast');
			$xfer += $output->writeI32($this->isLast);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->isLock !== null) {
			
			$xfer += $output->writeFieldBegin('isLock');
			$xfer += $output->writeI32($this->isLock);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->payId !== null) {
			
			$xfer += $output->writeFieldBegin('payId');
			$xfer += $output->writeI64($this->payId);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->totalMoney !== null) {
			
			$xfer += $output->writeFieldBegin('totalMoney');
			$xfer += $output->writeString($this->totalMoney);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->startPayTime !== null) {
			
			$xfer += $output->writeFieldBegin('startPayTime');
			$xfer += $output->writeI64($this->startPayTime);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->endPayTime !== null) {
			
			$xfer += $output->writeFieldBegin('endPayTime');
			$xfer += $output->writeI64($this->endPayTime);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}

?>