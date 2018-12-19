<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\common\pojo\order\vo;

class PrepayOrderPeriodsInfoVO {
	
	static $_TSPEC;
	public $seq = null;
	public $money = null;
	public $walletMoney = null;
	public $virtualMoney = null;
	public $couponMoney = null;
	public $payType = null;
	public $payId = null;
	public $startPayTime = null;
	public $endPayTime = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'seq'
			),
			2 => array(
			'var' => 'money'
			),
			3 => array(
			'var' => 'walletMoney'
			),
			4 => array(
			'var' => 'virtualMoney'
			),
			5 => array(
			'var' => 'couponMoney'
			),
			6 => array(
			'var' => 'payType'
			),
			7 => array(
			'var' => 'payId'
			),
			8 => array(
			'var' => 'startPayTime'
			),
			9 => array(
			'var' => 'endPayTime'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['seq'])){
				
				$this->seq = $vals['seq'];
			}
			
			
			if (isset($vals['money'])){
				
				$this->money = $vals['money'];
			}
			
			
			if (isset($vals['walletMoney'])){
				
				$this->walletMoney = $vals['walletMoney'];
			}
			
			
			if (isset($vals['virtualMoney'])){
				
				$this->virtualMoney = $vals['virtualMoney'];
			}
			
			
			if (isset($vals['couponMoney'])){
				
				$this->couponMoney = $vals['couponMoney'];
			}
			
			
			if (isset($vals['payType'])){
				
				$this->payType = $vals['payType'];
			}
			
			
			if (isset($vals['payId'])){
				
				$this->payId = $vals['payId'];
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
		
		return 'PrepayOrderPeriodsInfoVO';
	}
	
	public function read($input){
		
		$input->readStructBegin();
		while(true){
			
			$schemeField = $input->readFieldBegin();
			if ($schemeField == null) break;
			$needSkip = true;
			
			
			if ("seq" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->seq); 
				
			}
			
			
			
			
			if ("money" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->money);
				
			}
			
			
			
			
			if ("walletMoney" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->walletMoney);
				
			}
			
			
			
			
			if ("virtualMoney" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->virtualMoney);
				
			}
			
			
			
			
			if ("couponMoney" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->couponMoney);
				
			}
			
			
			
			
			if ("payType" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->payType); 
				
			}
			
			
			
			
			if ("payId" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->payId); 
				
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
		
		if($this->seq !== null) {
			
			$xfer += $output->writeFieldBegin('seq');
			$xfer += $output->writeI32($this->seq);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->money !== null) {
			
			$xfer += $output->writeFieldBegin('money');
			$xfer += $output->writeString($this->money);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->walletMoney !== null) {
			
			$xfer += $output->writeFieldBegin('walletMoney');
			$xfer += $output->writeString($this->walletMoney);
			
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
		
		
		if($this->payType !== null) {
			
			$xfer += $output->writeFieldBegin('payType');
			$xfer += $output->writeI32($this->payType);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->payId !== null) {
			
			$xfer += $output->writeFieldBegin('payId');
			$xfer += $output->writeI64($this->payId);
			
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