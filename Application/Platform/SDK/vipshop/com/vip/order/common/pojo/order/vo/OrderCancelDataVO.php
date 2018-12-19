<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\common\pojo\order\vo;

class OrderCancelDataVO {
	
	static $_TSPEC;
	public $money = null;
	public $walletMoney = null;
	public $virtualMoney = null;
	public $totalMoney = null;
	public $couponMoney = null;
	public $returnCouponType = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'money'
			),
			2 => array(
			'var' => 'walletMoney'
			),
			3 => array(
			'var' => 'virtualMoney'
			),
			4 => array(
			'var' => 'totalMoney'
			),
			5 => array(
			'var' => 'couponMoney'
			),
			6 => array(
			'var' => 'returnCouponType'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['money'])){
				
				$this->money = $vals['money'];
			}
			
			
			if (isset($vals['walletMoney'])){
				
				$this->walletMoney = $vals['walletMoney'];
			}
			
			
			if (isset($vals['virtualMoney'])){
				
				$this->virtualMoney = $vals['virtualMoney'];
			}
			
			
			if (isset($vals['totalMoney'])){
				
				$this->totalMoney = $vals['totalMoney'];
			}
			
			
			if (isset($vals['couponMoney'])){
				
				$this->couponMoney = $vals['couponMoney'];
			}
			
			
			if (isset($vals['returnCouponType'])){
				
				$this->returnCouponType = $vals['returnCouponType'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'OrderCancelDataVO';
	}
	
	public function read($input){
		
		$input->readStructBegin();
		while(true){
			
			$schemeField = $input->readFieldBegin();
			if ($schemeField == null) break;
			$needSkip = true;
			
			
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
			
			
			
			
			if ("totalMoney" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->totalMoney);
				
			}
			
			
			
			
			if ("couponMoney" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->couponMoney);
				
			}
			
			
			
			
			if ("returnCouponType" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->returnCouponType); 
				
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
		
		
		if($this->totalMoney !== null) {
			
			$xfer += $output->writeFieldBegin('totalMoney');
			$xfer += $output->writeString($this->totalMoney);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->couponMoney !== null) {
			
			$xfer += $output->writeFieldBegin('couponMoney');
			$xfer += $output->writeString($this->couponMoney);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->returnCouponType !== null) {
			
			$xfer += $output->writeFieldBegin('returnCouponType');
			$xfer += $output->writeI32($this->returnCouponType);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}

?>