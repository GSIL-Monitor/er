<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\common\pojo\order\vo;

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
	public $orderId = null;
	public $orderAddTime = null;
	public $createTime = null;
	public $moneyPaid = null;
	public $auditTime = null;
	public $couponId = null;
	public $couponMoneyPaid = null;
	public $currency = null;
	public $orderDate = null;
	public $discountRate = null;
	public $userIp = null;
	public $isHold = null;
	public $isSpecial = null;
	public $opFlag = null;
	public $operator = null;
	public $orderFlag = null;
	public $payStatus = null;
	public $payTime = null;
	public $payType = null;
	public $returnType = null;
	public $source = null;
	public $orderStatus = null;
	public $walletMoneyPaid = null;
	public $orderType = null;
	public $orderUpdateTime = null;
	public $userId = null;
	public $userName = null;
	public $virtualMoneyPaid = null;
	public $wmsFlag = null;
	public $payTypeName = null;
	public $realPayTypeName = null;
	public $payStatusName = null;
	public $orderTypeName = null;
	public $orderStatusName = null;
	public $clientType = null;
	public $queueStatus = null;
	public $orderSubStatus = null;
	
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
			11 => array(
			'var' => 'orderId'
			),
			12 => array(
			'var' => 'orderAddTime'
			),
			13 => array(
			'var' => 'createTime'
			),
			14 => array(
			'var' => 'moneyPaid'
			),
			15 => array(
			'var' => 'auditTime'
			),
			16 => array(
			'var' => 'couponId'
			),
			17 => array(
			'var' => 'couponMoneyPaid'
			),
			18 => array(
			'var' => 'currency'
			),
			19 => array(
			'var' => 'orderDate'
			),
			20 => array(
			'var' => 'discountRate'
			),
			21 => array(
			'var' => 'userIp'
			),
			22 => array(
			'var' => 'isHold'
			),
			23 => array(
			'var' => 'isSpecial'
			),
			24 => array(
			'var' => 'opFlag'
			),
			25 => array(
			'var' => 'operator'
			),
			27 => array(
			'var' => 'orderFlag'
			),
			28 => array(
			'var' => 'payStatus'
			),
			29 => array(
			'var' => 'payTime'
			),
			30 => array(
			'var' => 'payType'
			),
			31 => array(
			'var' => 'returnType'
			),
			32 => array(
			'var' => 'source'
			),
			33 => array(
			'var' => 'orderStatus'
			),
			34 => array(
			'var' => 'walletMoneyPaid'
			),
			35 => array(
			'var' => 'orderType'
			),
			36 => array(
			'var' => 'orderUpdateTime'
			),
			37 => array(
			'var' => 'userId'
			),
			38 => array(
			'var' => 'userName'
			),
			39 => array(
			'var' => 'virtualMoneyPaid'
			),
			40 => array(
			'var' => 'wmsFlag'
			),
			41 => array(
			'var' => 'payTypeName'
			),
			42 => array(
			'var' => 'realPayTypeName'
			),
			43 => array(
			'var' => 'payStatusName'
			),
			44 => array(
			'var' => 'orderTypeName'
			),
			45 => array(
			'var' => 'orderStatusName'
			),
			46 => array(
			'var' => 'clientType'
			),
			47 => array(
			'var' => 'queueStatus'
			),
			48 => array(
			'var' => 'orderSubStatus'
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
			
			
			if (isset($vals['orderId'])){
				
				$this->orderId = $vals['orderId'];
			}
			
			
			if (isset($vals['orderAddTime'])){
				
				$this->orderAddTime = $vals['orderAddTime'];
			}
			
			
			if (isset($vals['createTime'])){
				
				$this->createTime = $vals['createTime'];
			}
			
			
			if (isset($vals['moneyPaid'])){
				
				$this->moneyPaid = $vals['moneyPaid'];
			}
			
			
			if (isset($vals['auditTime'])){
				
				$this->auditTime = $vals['auditTime'];
			}
			
			
			if (isset($vals['couponId'])){
				
				$this->couponId = $vals['couponId'];
			}
			
			
			if (isset($vals['couponMoneyPaid'])){
				
				$this->couponMoneyPaid = $vals['couponMoneyPaid'];
			}
			
			
			if (isset($vals['currency'])){
				
				$this->currency = $vals['currency'];
			}
			
			
			if (isset($vals['orderDate'])){
				
				$this->orderDate = $vals['orderDate'];
			}
			
			
			if (isset($vals['discountRate'])){
				
				$this->discountRate = $vals['discountRate'];
			}
			
			
			if (isset($vals['userIp'])){
				
				$this->userIp = $vals['userIp'];
			}
			
			
			if (isset($vals['isHold'])){
				
				$this->isHold = $vals['isHold'];
			}
			
			
			if (isset($vals['isSpecial'])){
				
				$this->isSpecial = $vals['isSpecial'];
			}
			
			
			if (isset($vals['opFlag'])){
				
				$this->opFlag = $vals['opFlag'];
			}
			
			
			if (isset($vals['operator'])){
				
				$this->operator = $vals['operator'];
			}
			
			
			if (isset($vals['orderFlag'])){
				
				$this->orderFlag = $vals['orderFlag'];
			}
			
			
			if (isset($vals['payStatus'])){
				
				$this->payStatus = $vals['payStatus'];
			}
			
			
			if (isset($vals['payTime'])){
				
				$this->payTime = $vals['payTime'];
			}
			
			
			if (isset($vals['payType'])){
				
				$this->payType = $vals['payType'];
			}
			
			
			if (isset($vals['returnType'])){
				
				$this->returnType = $vals['returnType'];
			}
			
			
			if (isset($vals['source'])){
				
				$this->source = $vals['source'];
			}
			
			
			if (isset($vals['orderStatus'])){
				
				$this->orderStatus = $vals['orderStatus'];
			}
			
			
			if (isset($vals['walletMoneyPaid'])){
				
				$this->walletMoneyPaid = $vals['walletMoneyPaid'];
			}
			
			
			if (isset($vals['orderType'])){
				
				$this->orderType = $vals['orderType'];
			}
			
			
			if (isset($vals['orderUpdateTime'])){
				
				$this->orderUpdateTime = $vals['orderUpdateTime'];
			}
			
			
			if (isset($vals['userId'])){
				
				$this->userId = $vals['userId'];
			}
			
			
			if (isset($vals['userName'])){
				
				$this->userName = $vals['userName'];
			}
			
			
			if (isset($vals['virtualMoneyPaid'])){
				
				$this->virtualMoneyPaid = $vals['virtualMoneyPaid'];
			}
			
			
			if (isset($vals['wmsFlag'])){
				
				$this->wmsFlag = $vals['wmsFlag'];
			}
			
			
			if (isset($vals['payTypeName'])){
				
				$this->payTypeName = $vals['payTypeName'];
			}
			
			
			if (isset($vals['realPayTypeName'])){
				
				$this->realPayTypeName = $vals['realPayTypeName'];
			}
			
			
			if (isset($vals['payStatusName'])){
				
				$this->payStatusName = $vals['payStatusName'];
			}
			
			
			if (isset($vals['orderTypeName'])){
				
				$this->orderTypeName = $vals['orderTypeName'];
			}
			
			
			if (isset($vals['orderStatusName'])){
				
				$this->orderStatusName = $vals['orderStatusName'];
			}
			
			
			if (isset($vals['clientType'])){
				
				$this->clientType = $vals['clientType'];
			}
			
			
			if (isset($vals['queueStatus'])){
				
				$this->queueStatus = $vals['queueStatus'];
			}
			
			
			if (isset($vals['orderSubStatus'])){
				
				$this->orderSubStatus = $vals['orderSubStatus'];
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
			
			
			
			
			if ("orderId" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->orderId); 
				
			}
			
			
			
			
			if ("orderAddTime" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->orderAddTime); 
				
			}
			
			
			
			
			if ("createTime" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->createTime); 
				
			}
			
			
			
			
			if ("moneyPaid" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->moneyPaid);
				
			}
			
			
			
			
			if ("auditTime" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->auditTime); 
				
			}
			
			
			
			
			if ("couponId" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->couponId); 
				
			}
			
			
			
			
			if ("couponMoneyPaid" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->couponMoneyPaid);
				
			}
			
			
			
			
			if ("currency" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->currency);
				
			}
			
			
			
			
			if ("orderDate" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->orderDate); 
				
			}
			
			
			
			
			if ("discountRate" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->discountRate);
				
			}
			
			
			
			
			if ("userIp" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->userIp);
				
			}
			
			
			
			
			if ("isHold" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->isHold); 
				
			}
			
			
			
			
			if ("isSpecial" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->isSpecial); 
				
			}
			
			
			
			
			if ("opFlag" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->opFlag); 
				
			}
			
			
			
			
			if ("operator" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->operator);
				
			}
			
			
			
			
			if ("orderFlag" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->orderFlag);
				
			}
			
			
			
			
			if ("payStatus" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->payStatus); 
				
			}
			
			
			
			
			if ("payTime" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->payTime); 
				
			}
			
			
			
			
			if ("payType" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->payType); 
				
			}
			
			
			
			
			if ("returnType" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->returnType); 
				
			}
			
			
			
			
			if ("source" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->source);
				
			}
			
			
			
			
			if ("orderStatus" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->orderStatus); 
				
			}
			
			
			
			
			if ("walletMoneyPaid" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->walletMoneyPaid);
				
			}
			
			
			
			
			if ("orderType" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->orderType); 
				
			}
			
			
			
			
			if ("orderUpdateTime" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->orderUpdateTime); 
				
			}
			
			
			
			
			if ("userId" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->userId); 
				
			}
			
			
			
			
			if ("userName" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->userName);
				
			}
			
			
			
			
			if ("virtualMoneyPaid" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->virtualMoneyPaid);
				
			}
			
			
			
			
			if ("wmsFlag" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->wmsFlag); 
				
			}
			
			
			
			
			if ("payTypeName" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->payTypeName);
				
			}
			
			
			
			
			if ("realPayTypeName" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->realPayTypeName);
				
			}
			
			
			
			
			if ("payStatusName" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->payStatusName);
				
			}
			
			
			
			
			if ("orderTypeName" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->orderTypeName);
				
			}
			
			
			
			
			if ("orderStatusName" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->orderStatusName);
				
			}
			
			
			
			
			if ("clientType" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->clientType); 
				
			}
			
			
			
			
			if ("queueStatus" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->queueStatus); 
				
			}
			
			
			
			
			if ("orderSubStatus" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->orderSubStatus); 
				
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
		
		
		if($this->orderId !== null) {
			
			$xfer += $output->writeFieldBegin('orderId');
			$xfer += $output->writeI64($this->orderId);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderAddTime !== null) {
			
			$xfer += $output->writeFieldBegin('orderAddTime');
			$xfer += $output->writeI64($this->orderAddTime);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->createTime !== null) {
			
			$xfer += $output->writeFieldBegin('createTime');
			$xfer += $output->writeI64($this->createTime);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->moneyPaid !== null) {
			
			$xfer += $output->writeFieldBegin('moneyPaid');
			$xfer += $output->writeString($this->moneyPaid);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->auditTime !== null) {
			
			$xfer += $output->writeFieldBegin('auditTime');
			$xfer += $output->writeI64($this->auditTime);
			
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
		
		
		if($this->currency !== null) {
			
			$xfer += $output->writeFieldBegin('currency');
			$xfer += $output->writeString($this->currency);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderDate !== null) {
			
			$xfer += $output->writeFieldBegin('orderDate');
			$xfer += $output->writeI64($this->orderDate);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->discountRate !== null) {
			
			$xfer += $output->writeFieldBegin('discountRate');
			$xfer += $output->writeString($this->discountRate);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->userIp !== null) {
			
			$xfer += $output->writeFieldBegin('userIp');
			$xfer += $output->writeString($this->userIp);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->isHold !== null) {
			
			$xfer += $output->writeFieldBegin('isHold');
			$xfer += $output->writeI32($this->isHold);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->isSpecial !== null) {
			
			$xfer += $output->writeFieldBegin('isSpecial');
			$xfer += $output->writeI32($this->isSpecial);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->opFlag !== null) {
			
			$xfer += $output->writeFieldBegin('opFlag');
			$xfer += $output->writeI32($this->opFlag);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->operator !== null) {
			
			$xfer += $output->writeFieldBegin('operator');
			$xfer += $output->writeString($this->operator);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderFlag !== null) {
			
			$xfer += $output->writeFieldBegin('orderFlag');
			$xfer += $output->writeString($this->orderFlag);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->payStatus !== null) {
			
			$xfer += $output->writeFieldBegin('payStatus');
			$xfer += $output->writeI32($this->payStatus);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->payTime !== null) {
			
			$xfer += $output->writeFieldBegin('payTime');
			$xfer += $output->writeI64($this->payTime);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->payType !== null) {
			
			$xfer += $output->writeFieldBegin('payType');
			$xfer += $output->writeI32($this->payType);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->returnType !== null) {
			
			$xfer += $output->writeFieldBegin('returnType');
			$xfer += $output->writeI32($this->returnType);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->source !== null) {
			
			$xfer += $output->writeFieldBegin('source');
			$xfer += $output->writeString($this->source);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderStatus !== null) {
			
			$xfer += $output->writeFieldBegin('orderStatus');
			$xfer += $output->writeI32($this->orderStatus);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->walletMoneyPaid !== null) {
			
			$xfer += $output->writeFieldBegin('walletMoneyPaid');
			$xfer += $output->writeString($this->walletMoneyPaid);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderType !== null) {
			
			$xfer += $output->writeFieldBegin('orderType');
			$xfer += $output->writeI32($this->orderType);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderUpdateTime !== null) {
			
			$xfer += $output->writeFieldBegin('orderUpdateTime');
			$xfer += $output->writeI64($this->orderUpdateTime);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->userId !== null) {
			
			$xfer += $output->writeFieldBegin('userId');
			$xfer += $output->writeI64($this->userId);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->userName !== null) {
			
			$xfer += $output->writeFieldBegin('userName');
			$xfer += $output->writeString($this->userName);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->virtualMoneyPaid !== null) {
			
			$xfer += $output->writeFieldBegin('virtualMoneyPaid');
			$xfer += $output->writeString($this->virtualMoneyPaid);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->wmsFlag !== null) {
			
			$xfer += $output->writeFieldBegin('wmsFlag');
			$xfer += $output->writeI32($this->wmsFlag);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->payTypeName !== null) {
			
			$xfer += $output->writeFieldBegin('payTypeName');
			$xfer += $output->writeString($this->payTypeName);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->realPayTypeName !== null) {
			
			$xfer += $output->writeFieldBegin('realPayTypeName');
			$xfer += $output->writeString($this->realPayTypeName);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->payStatusName !== null) {
			
			$xfer += $output->writeFieldBegin('payStatusName');
			$xfer += $output->writeString($this->payStatusName);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderTypeName !== null) {
			
			$xfer += $output->writeFieldBegin('orderTypeName');
			$xfer += $output->writeString($this->orderTypeName);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderStatusName !== null) {
			
			$xfer += $output->writeFieldBegin('orderStatusName');
			$xfer += $output->writeString($this->orderStatusName);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->clientType !== null) {
			
			$xfer += $output->writeFieldBegin('clientType');
			$xfer += $output->writeI32($this->clientType);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->queueStatus !== null) {
			
			$xfer += $output->writeFieldBegin('queueStatus');
			$xfer += $output->writeI32($this->queueStatus);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderSubStatus !== null) {
			
			$xfer += $output->writeFieldBegin('orderSubStatus');
			$xfer += $output->writeI32($this->orderSubStatus);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}

?>