<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\common\pojo\order\vo;

class OrderPayAndDiscountVO {
	
	static $_TSPEC;
	public $payId = null;
	public $payType = null;
	public $payStatus = null;
	public $currency = null;
	public $moneyPaid = null;
	public $moneyRemaining = null;
	public $walletMoneyPaid = null;
	public $walletMoneyRemaining = null;
	public $virtualMoneyPaid = null;
	public $virtualMoneyRemaining = null;
	public $discountRate = null;
	public $couponId = null;
	public $couponMoneyPaid = null;
	public $couponMoneyRemaining = null;
	public $exDiscountType = null;
	public $exDiscountMoneyPaid = null;
	public $exDiscountMoneyRemaining = null;
	public $returnType = null;
	public $isDelete = null;
	public $payTime = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'payId'
			),
			2 => array(
			'var' => 'payType'
			),
			3 => array(
			'var' => 'payStatus'
			),
			4 => array(
			'var' => 'currency'
			),
			5 => array(
			'var' => 'moneyPaid'
			),
			6 => array(
			'var' => 'moneyRemaining'
			),
			7 => array(
			'var' => 'walletMoneyPaid'
			),
			8 => array(
			'var' => 'walletMoneyRemaining'
			),
			9 => array(
			'var' => 'virtualMoneyPaid'
			),
			10 => array(
			'var' => 'virtualMoneyRemaining'
			),
			11 => array(
			'var' => 'discountRate'
			),
			12 => array(
			'var' => 'couponId'
			),
			13 => array(
			'var' => 'couponMoneyPaid'
			),
			14 => array(
			'var' => 'couponMoneyRemaining'
			),
			15 => array(
			'var' => 'exDiscountType'
			),
			16 => array(
			'var' => 'exDiscountMoneyPaid'
			),
			17 => array(
			'var' => 'exDiscountMoneyRemaining'
			),
			18 => array(
			'var' => 'returnType'
			),
			19 => array(
			'var' => 'isDelete'
			),
			20 => array(
			'var' => 'payTime'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['payId'])){
				
				$this->payId = $vals['payId'];
			}
			
			
			if (isset($vals['payType'])){
				
				$this->payType = $vals['payType'];
			}
			
			
			if (isset($vals['payStatus'])){
				
				$this->payStatus = $vals['payStatus'];
			}
			
			
			if (isset($vals['currency'])){
				
				$this->currency = $vals['currency'];
			}
			
			
			if (isset($vals['moneyPaid'])){
				
				$this->moneyPaid = $vals['moneyPaid'];
			}
			
			
			if (isset($vals['moneyRemaining'])){
				
				$this->moneyRemaining = $vals['moneyRemaining'];
			}
			
			
			if (isset($vals['walletMoneyPaid'])){
				
				$this->walletMoneyPaid = $vals['walletMoneyPaid'];
			}
			
			
			if (isset($vals['walletMoneyRemaining'])){
				
				$this->walletMoneyRemaining = $vals['walletMoneyRemaining'];
			}
			
			
			if (isset($vals['virtualMoneyPaid'])){
				
				$this->virtualMoneyPaid = $vals['virtualMoneyPaid'];
			}
			
			
			if (isset($vals['virtualMoneyRemaining'])){
				
				$this->virtualMoneyRemaining = $vals['virtualMoneyRemaining'];
			}
			
			
			if (isset($vals['discountRate'])){
				
				$this->discountRate = $vals['discountRate'];
			}
			
			
			if (isset($vals['couponId'])){
				
				$this->couponId = $vals['couponId'];
			}
			
			
			if (isset($vals['couponMoneyPaid'])){
				
				$this->couponMoneyPaid = $vals['couponMoneyPaid'];
			}
			
			
			if (isset($vals['couponMoneyRemaining'])){
				
				$this->couponMoneyRemaining = $vals['couponMoneyRemaining'];
			}
			
			
			if (isset($vals['exDiscountType'])){
				
				$this->exDiscountType = $vals['exDiscountType'];
			}
			
			
			if (isset($vals['exDiscountMoneyPaid'])){
				
				$this->exDiscountMoneyPaid = $vals['exDiscountMoneyPaid'];
			}
			
			
			if (isset($vals['exDiscountMoneyRemaining'])){
				
				$this->exDiscountMoneyRemaining = $vals['exDiscountMoneyRemaining'];
			}
			
			
			if (isset($vals['returnType'])){
				
				$this->returnType = $vals['returnType'];
			}
			
			
			if (isset($vals['isDelete'])){
				
				$this->isDelete = $vals['isDelete'];
			}
			
			
			if (isset($vals['payTime'])){
				
				$this->payTime = $vals['payTime'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'OrderPayAndDiscountVO';
	}
	
	public function read($input){
		
		$input->readStructBegin();
		while(true){
			
			$schemeField = $input->readFieldBegin();
			if ($schemeField == null) break;
			$needSkip = true;
			
			
			if ("payId" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->payId); 
				
			}
			
			
			
			
			if ("payType" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->payType); 
				
			}
			
			
			
			
			if ("payStatus" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->payStatus); 
				
			}
			
			
			
			
			if ("currency" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->currency);
				
			}
			
			
			
			
			if ("moneyPaid" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->moneyPaid);
				
			}
			
			
			
			
			if ("moneyRemaining" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->moneyRemaining);
				
			}
			
			
			
			
			if ("walletMoneyPaid" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->walletMoneyPaid);
				
			}
			
			
			
			
			if ("walletMoneyRemaining" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->walletMoneyRemaining);
				
			}
			
			
			
			
			if ("virtualMoneyPaid" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->virtualMoneyPaid);
				
			}
			
			
			
			
			if ("virtualMoneyRemaining" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->virtualMoneyRemaining);
				
			}
			
			
			
			
			if ("discountRate" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->discountRate);
				
			}
			
			
			
			
			if ("couponId" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->couponId); 
				
			}
			
			
			
			
			if ("couponMoneyPaid" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->couponMoneyPaid);
				
			}
			
			
			
			
			if ("couponMoneyRemaining" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->couponMoneyRemaining);
				
			}
			
			
			
			
			if ("exDiscountType" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->exDiscountType);
				
			}
			
			
			
			
			if ("exDiscountMoneyPaid" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->exDiscountMoneyPaid);
				
			}
			
			
			
			
			if ("exDiscountMoneyRemaining" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->exDiscountMoneyRemaining);
				
			}
			
			
			
			
			if ("returnType" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->returnType); 
				
			}
			
			
			
			
			if ("isDelete" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->isDelete); 
				
			}
			
			
			
			
			if ("payTime" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->payTime); 
				
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
		
		if($this->payId !== null) {
			
			$xfer += $output->writeFieldBegin('payId');
			$xfer += $output->writeI32($this->payId);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->payType !== null) {
			
			$xfer += $output->writeFieldBegin('payType');
			$xfer += $output->writeI32($this->payType);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->payStatus !== null) {
			
			$xfer += $output->writeFieldBegin('payStatus');
			$xfer += $output->writeI32($this->payStatus);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->currency !== null) {
			
			$xfer += $output->writeFieldBegin('currency');
			$xfer += $output->writeString($this->currency);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->moneyPaid !== null) {
			
			$xfer += $output->writeFieldBegin('moneyPaid');
			$xfer += $output->writeString($this->moneyPaid);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->moneyRemaining !== null) {
			
			$xfer += $output->writeFieldBegin('moneyRemaining');
			$xfer += $output->writeString($this->moneyRemaining);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->walletMoneyPaid !== null) {
			
			$xfer += $output->writeFieldBegin('walletMoneyPaid');
			$xfer += $output->writeString($this->walletMoneyPaid);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->walletMoneyRemaining !== null) {
			
			$xfer += $output->writeFieldBegin('walletMoneyRemaining');
			$xfer += $output->writeString($this->walletMoneyRemaining);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->virtualMoneyPaid !== null) {
			
			$xfer += $output->writeFieldBegin('virtualMoneyPaid');
			$xfer += $output->writeString($this->virtualMoneyPaid);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->virtualMoneyRemaining !== null) {
			
			$xfer += $output->writeFieldBegin('virtualMoneyRemaining');
			$xfer += $output->writeString($this->virtualMoneyRemaining);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->discountRate !== null) {
			
			$xfer += $output->writeFieldBegin('discountRate');
			$xfer += $output->writeString($this->discountRate);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->couponId !== null) {
			
			$xfer += $output->writeFieldBegin('couponId');
			$xfer += $output->writeI32($this->couponId);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->couponMoneyPaid !== null) {
			
			$xfer += $output->writeFieldBegin('couponMoneyPaid');
			$xfer += $output->writeString($this->couponMoneyPaid);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->couponMoneyRemaining !== null) {
			
			$xfer += $output->writeFieldBegin('couponMoneyRemaining');
			$xfer += $output->writeString($this->couponMoneyRemaining);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->exDiscountType !== null) {
			
			$xfer += $output->writeFieldBegin('exDiscountType');
			$xfer += $output->writeString($this->exDiscountType);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->exDiscountMoneyPaid !== null) {
			
			$xfer += $output->writeFieldBegin('exDiscountMoneyPaid');
			$xfer += $output->writeString($this->exDiscountMoneyPaid);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->exDiscountMoneyRemaining !== null) {
			
			$xfer += $output->writeFieldBegin('exDiscountMoneyRemaining');
			$xfer += $output->writeString($this->exDiscountMoneyRemaining);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->returnType !== null) {
			
			$xfer += $output->writeFieldBegin('returnType');
			$xfer += $output->writeI32($this->returnType);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->isDelete !== null) {
			
			$xfer += $output->writeFieldBegin('isDelete');
			$xfer += $output->writeI32($this->isDelete);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->payTime !== null) {
			
			$xfer += $output->writeFieldBegin('payTime');
			$xfer += $output->writeI64($this->payTime);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}

?>