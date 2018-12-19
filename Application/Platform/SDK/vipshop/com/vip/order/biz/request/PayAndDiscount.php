<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\biz\request;

class PayAndDiscount {
	
	static $_TSPEC;
	public $couponId = null;
	public $couponMoney = null;
	public $isCancelCoupon = null;
	public $isForceUseCoupon = null;
	public $useWalletMoney = null;
	public $discountRate = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'couponId'
			),
			2 => array(
			'var' => 'couponMoney'
			),
			3 => array(
			'var' => 'isCancelCoupon'
			),
			4 => array(
			'var' => 'isForceUseCoupon'
			),
			5 => array(
			'var' => 'useWalletMoney'
			),
			6 => array(
			'var' => 'discountRate'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['couponId'])){
				
				$this->couponId = $vals['couponId'];
			}
			
			
			if (isset($vals['couponMoney'])){
				
				$this->couponMoney = $vals['couponMoney'];
			}
			
			
			if (isset($vals['isCancelCoupon'])){
				
				$this->isCancelCoupon = $vals['isCancelCoupon'];
			}
			
			
			if (isset($vals['isForceUseCoupon'])){
				
				$this->isForceUseCoupon = $vals['isForceUseCoupon'];
			}
			
			
			if (isset($vals['useWalletMoney'])){
				
				$this->useWalletMoney = $vals['useWalletMoney'];
			}
			
			
			if (isset($vals['discountRate'])){
				
				$this->discountRate = $vals['discountRate'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'PayAndDiscount';
	}
	
	public function read($input){
		
		$input->readStructBegin();
		while(true){
			
			$schemeField = $input->readFieldBegin();
			if ($schemeField == null) break;
			$needSkip = true;
			
			
			if ("couponId" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->couponId); 
				
			}
			
			
			
			
			if ("couponMoney" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->couponMoney);
				
			}
			
			
			
			
			if ("isCancelCoupon" == $schemeField){
				
				$needSkip = false;
				$input->readBool($this->isCancelCoupon);
				
			}
			
			
			
			
			if ("isForceUseCoupon" == $schemeField){
				
				$needSkip = false;
				$input->readBool($this->isForceUseCoupon);
				
			}
			
			
			
			
			if ("useWalletMoney" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->useWalletMoney); 
				
			}
			
			
			
			
			if ("discountRate" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->discountRate);
				
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
		
		if($this->couponId !== null) {
			
			$xfer += $output->writeFieldBegin('couponId');
			$xfer += $output->writeI64($this->couponId);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->couponMoney !== null) {
			
			$xfer += $output->writeFieldBegin('couponMoney');
			$xfer += $output->writeString($this->couponMoney);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->isCancelCoupon !== null) {
			
			$xfer += $output->writeFieldBegin('isCancelCoupon');
			$xfer += $output->writeBool($this->isCancelCoupon);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->isForceUseCoupon !== null) {
			
			$xfer += $output->writeFieldBegin('isForceUseCoupon');
			$xfer += $output->writeBool($this->isForceUseCoupon);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->useWalletMoney !== null) {
			
			$xfer += $output->writeFieldBegin('useWalletMoney');
			$xfer += $output->writeI32($this->useWalletMoney);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->discountRate !== null) {
			
			$xfer += $output->writeFieldBegin('discountRate');
			$xfer += $output->writeString($this->discountRate);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}

?>