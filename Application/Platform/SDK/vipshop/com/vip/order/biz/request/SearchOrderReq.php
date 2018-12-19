<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\biz\request;

class SearchOrderReq {
	
	static $_TSPEC;
	public $userIdList = null;
	public $orderIdList = null;
	public $orderSnList = null;
	public $userName = null;
	public $saleType = null;
	public $typeRange = null;
	public $orderModelList = null;
	public $warehouse = null;
	public $vipclubList = null;
	public $statusRange = null;
	public $subStatusRange = null;
	public $payStatus = null;
	public $buyer = null;
	public $transportSn = null;
	public $transportId = null;
	public $mobile = null;
	public $tel = null;
	public $queryRange = null;
	public $orderTimeRange = null;
	public $orderDateRange = null;
	public $updateTimeRange = null;
	public $payTypeRange = null;
	public $walletAmountRange = null;
	public $couponIdList = null;
	public $invoiceTypeList = null;
	public $allotTimeRange = null;
	public $orderFlag = null;
	public $statusFlag = null;
	public $wmsFlagList = null;
	public $isHold = null;
	public $isSpecial = null;
	public $isDisplay = null;
	public $orderSourceTypeRange = null;
	public $specialCondition = null;
	public $amountRange = null;
	public $orderCategory = null;
	public $snType = null;
	public $auditTimeRange = null;
	public $isFirst = null;
	public $isLock = null;
	public $ip = null;
	public $operator = null;
	public $deliveryTypeList = null;
	public $compensateFlagList = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'userIdList'
			),
			2 => array(
			'var' => 'orderIdList'
			),
			3 => array(
			'var' => 'orderSnList'
			),
			4 => array(
			'var' => 'userName'
			),
			5 => array(
			'var' => 'saleType'
			),
			6 => array(
			'var' => 'typeRange'
			),
			7 => array(
			'var' => 'orderModelList'
			),
			8 => array(
			'var' => 'warehouse'
			),
			9 => array(
			'var' => 'vipclubList'
			),
			10 => array(
			'var' => 'statusRange'
			),
			11 => array(
			'var' => 'subStatusRange'
			),
			12 => array(
			'var' => 'payStatus'
			),
			13 => array(
			'var' => 'buyer'
			),
			14 => array(
			'var' => 'transportSn'
			),
			15 => array(
			'var' => 'transportId'
			),
			16 => array(
			'var' => 'mobile'
			),
			17 => array(
			'var' => 'tel'
			),
			18 => array(
			'var' => 'queryRange'
			),
			19 => array(
			'var' => 'orderTimeRange'
			),
			20 => array(
			'var' => 'orderDateRange'
			),
			21 => array(
			'var' => 'updateTimeRange'
			),
			22 => array(
			'var' => 'payTypeRange'
			),
			23 => array(
			'var' => 'walletAmountRange'
			),
			24 => array(
			'var' => 'couponIdList'
			),
			25 => array(
			'var' => 'invoiceTypeList'
			),
			26 => array(
			'var' => 'allotTimeRange'
			),
			27 => array(
			'var' => 'orderFlag'
			),
			28 => array(
			'var' => 'statusFlag'
			),
			29 => array(
			'var' => 'wmsFlagList'
			),
			30 => array(
			'var' => 'isHold'
			),
			31 => array(
			'var' => 'isSpecial'
			),
			32 => array(
			'var' => 'isDisplay'
			),
			33 => array(
			'var' => 'orderSourceTypeRange'
			),
			34 => array(
			'var' => 'specialCondition'
			),
			35 => array(
			'var' => 'amountRange'
			),
			36 => array(
			'var' => 'orderCategory'
			),
			37 => array(
			'var' => 'snType'
			),
			38 => array(
			'var' => 'auditTimeRange'
			),
			39 => array(
			'var' => 'isFirst'
			),
			40 => array(
			'var' => 'isLock'
			),
			41 => array(
			'var' => 'ip'
			),
			42 => array(
			'var' => 'operator'
			),
			43 => array(
			'var' => 'deliveryTypeList'
			),
			44 => array(
			'var' => 'compensateFlagList'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['userIdList'])){
				
				$this->userIdList = $vals['userIdList'];
			}
			
			
			if (isset($vals['orderIdList'])){
				
				$this->orderIdList = $vals['orderIdList'];
			}
			
			
			if (isset($vals['orderSnList'])){
				
				$this->orderSnList = $vals['orderSnList'];
			}
			
			
			if (isset($vals['userName'])){
				
				$this->userName = $vals['userName'];
			}
			
			
			if (isset($vals['saleType'])){
				
				$this->saleType = $vals['saleType'];
			}
			
			
			if (isset($vals['typeRange'])){
				
				$this->typeRange = $vals['typeRange'];
			}
			
			
			if (isset($vals['orderModelList'])){
				
				$this->orderModelList = $vals['orderModelList'];
			}
			
			
			if (isset($vals['warehouse'])){
				
				$this->warehouse = $vals['warehouse'];
			}
			
			
			if (isset($vals['vipclubList'])){
				
				$this->vipclubList = $vals['vipclubList'];
			}
			
			
			if (isset($vals['statusRange'])){
				
				$this->statusRange = $vals['statusRange'];
			}
			
			
			if (isset($vals['subStatusRange'])){
				
				$this->subStatusRange = $vals['subStatusRange'];
			}
			
			
			if (isset($vals['payStatus'])){
				
				$this->payStatus = $vals['payStatus'];
			}
			
			
			if (isset($vals['buyer'])){
				
				$this->buyer = $vals['buyer'];
			}
			
			
			if (isset($vals['transportSn'])){
				
				$this->transportSn = $vals['transportSn'];
			}
			
			
			if (isset($vals['transportId'])){
				
				$this->transportId = $vals['transportId'];
			}
			
			
			if (isset($vals['mobile'])){
				
				$this->mobile = $vals['mobile'];
			}
			
			
			if (isset($vals['tel'])){
				
				$this->tel = $vals['tel'];
			}
			
			
			if (isset($vals['queryRange'])){
				
				$this->queryRange = $vals['queryRange'];
			}
			
			
			if (isset($vals['orderTimeRange'])){
				
				$this->orderTimeRange = $vals['orderTimeRange'];
			}
			
			
			if (isset($vals['orderDateRange'])){
				
				$this->orderDateRange = $vals['orderDateRange'];
			}
			
			
			if (isset($vals['updateTimeRange'])){
				
				$this->updateTimeRange = $vals['updateTimeRange'];
			}
			
			
			if (isset($vals['payTypeRange'])){
				
				$this->payTypeRange = $vals['payTypeRange'];
			}
			
			
			if (isset($vals['walletAmountRange'])){
				
				$this->walletAmountRange = $vals['walletAmountRange'];
			}
			
			
			if (isset($vals['couponIdList'])){
				
				$this->couponIdList = $vals['couponIdList'];
			}
			
			
			if (isset($vals['invoiceTypeList'])){
				
				$this->invoiceTypeList = $vals['invoiceTypeList'];
			}
			
			
			if (isset($vals['allotTimeRange'])){
				
				$this->allotTimeRange = $vals['allotTimeRange'];
			}
			
			
			if (isset($vals['orderFlag'])){
				
				$this->orderFlag = $vals['orderFlag'];
			}
			
			
			if (isset($vals['statusFlag'])){
				
				$this->statusFlag = $vals['statusFlag'];
			}
			
			
			if (isset($vals['wmsFlagList'])){
				
				$this->wmsFlagList = $vals['wmsFlagList'];
			}
			
			
			if (isset($vals['isHold'])){
				
				$this->isHold = $vals['isHold'];
			}
			
			
			if (isset($vals['isSpecial'])){
				
				$this->isSpecial = $vals['isSpecial'];
			}
			
			
			if (isset($vals['isDisplay'])){
				
				$this->isDisplay = $vals['isDisplay'];
			}
			
			
			if (isset($vals['orderSourceTypeRange'])){
				
				$this->orderSourceTypeRange = $vals['orderSourceTypeRange'];
			}
			
			
			if (isset($vals['specialCondition'])){
				
				$this->specialCondition = $vals['specialCondition'];
			}
			
			
			if (isset($vals['amountRange'])){
				
				$this->amountRange = $vals['amountRange'];
			}
			
			
			if (isset($vals['orderCategory'])){
				
				$this->orderCategory = $vals['orderCategory'];
			}
			
			
			if (isset($vals['snType'])){
				
				$this->snType = $vals['snType'];
			}
			
			
			if (isset($vals['auditTimeRange'])){
				
				$this->auditTimeRange = $vals['auditTimeRange'];
			}
			
			
			if (isset($vals['isFirst'])){
				
				$this->isFirst = $vals['isFirst'];
			}
			
			
			if (isset($vals['isLock'])){
				
				$this->isLock = $vals['isLock'];
			}
			
			
			if (isset($vals['ip'])){
				
				$this->ip = $vals['ip'];
			}
			
			
			if (isset($vals['operator'])){
				
				$this->operator = $vals['operator'];
			}
			
			
			if (isset($vals['deliveryTypeList'])){
				
				$this->deliveryTypeList = $vals['deliveryTypeList'];
			}
			
			
			if (isset($vals['compensateFlagList'])){
				
				$this->compensateFlagList = $vals['compensateFlagList'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'SearchOrderReq';
	}
	
	public function read($input){
		
		$input->readStructBegin();
		while(true){
			
			$schemeField = $input->readFieldBegin();
			if ($schemeField == null) break;
			$needSkip = true;
			
			
			if ("userIdList" == $schemeField){
				
				$needSkip = false;
				
				$this->userIdList = array();
				$_size0 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem0 = null;
						$input->readI64($elem0); 
						
						$this->userIdList[$_size0++] = $elem0;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readListEnd();
				
			}
			
			
			
			
			if ("orderIdList" == $schemeField){
				
				$needSkip = false;
				
				$this->orderIdList = array();
				$_size1 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem1 = null;
						$input->readI64($elem1); 
						
						$this->orderIdList[$_size1++] = $elem1;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readListEnd();
				
			}
			
			
			
			
			if ("orderSnList" == $schemeField){
				
				$needSkip = false;
				
				$this->orderSnList = array();
				$_size2 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem2 = null;
						$input->readString($elem2);
						
						$this->orderSnList[$_size2++] = $elem2;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readListEnd();
				
			}
			
			
			
			
			if ("userName" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->userName);
				
			}
			
			
			
			
			if ("saleType" == $schemeField){
				
				$needSkip = false;
				
				$this->saleType = array();
				$_size3 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem3 = null;
						$input->readI32($elem3); 
						
						$this->saleType[$_size3++] = $elem3;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readListEnd();
				
			}
			
			
			
			
			if ("typeRange" == $schemeField){
				
				$needSkip = false;
				
				$this->typeRange = new \com\vip\order\common\pojo\order\request\RangeParam();
				$this->typeRange->read($input);
				
			}
			
			
			
			
			if ("orderModelList" == $schemeField){
				
				$needSkip = false;
				
				$this->orderModelList = array();
				$_size4 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem4 = null;
						$input->readI32($elem4); 
						
						$this->orderModelList[$_size4++] = $elem4;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readListEnd();
				
			}
			
			
			
			
			if ("warehouse" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->warehouse);
				
			}
			
			
			
			
			if ("vipclubList" == $schemeField){
				
				$needSkip = false;
				
				$this->vipclubList = array();
				$_size5 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem5 = null;
						$input->readString($elem5);
						
						$this->vipclubList[$_size5++] = $elem5;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readListEnd();
				
			}
			
			
			
			
			if ("statusRange" == $schemeField){
				
				$needSkip = false;
				
				$this->statusRange = new \com\vip\order\common\pojo\order\request\RangeParam();
				$this->statusRange->read($input);
				
			}
			
			
			
			
			if ("subStatusRange" == $schemeField){
				
				$needSkip = false;
				
				$this->subStatusRange = new \com\vip\order\common\pojo\order\request\RangeParam();
				$this->subStatusRange->read($input);
				
			}
			
			
			
			
			if ("payStatus" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->payStatus); 
				
			}
			
			
			
			
			if ("buyer" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->buyer);
				
			}
			
			
			
			
			if ("transportSn" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->transportSn);
				
			}
			
			
			
			
			if ("transportId" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->transportId); 
				
			}
			
			
			
			
			if ("mobile" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->mobile);
				
			}
			
			
			
			
			if ("tel" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->tel);
				
			}
			
			
			
			
			if ("queryRange" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->queryRange); 
				
			}
			
			
			
			
			if ("orderTimeRange" == $schemeField){
				
				$needSkip = false;
				
				$this->orderTimeRange = new \com\vip\order\common\pojo\order\request\RangeParam();
				$this->orderTimeRange->read($input);
				
			}
			
			
			
			
			if ("orderDateRange" == $schemeField){
				
				$needSkip = false;
				
				$this->orderDateRange = new \com\vip\order\common\pojo\order\request\RangeParam();
				$this->orderDateRange->read($input);
				
			}
			
			
			
			
			if ("updateTimeRange" == $schemeField){
				
				$needSkip = false;
				
				$this->updateTimeRange = new \com\vip\order\common\pojo\order\request\RangeParam();
				$this->updateTimeRange->read($input);
				
			}
			
			
			
			
			if ("payTypeRange" == $schemeField){
				
				$needSkip = false;
				
				$this->payTypeRange = new \com\vip\order\common\pojo\order\request\RangeParam();
				$this->payTypeRange->read($input);
				
			}
			
			
			
			
			if ("walletAmountRange" == $schemeField){
				
				$needSkip = false;
				
				$this->walletAmountRange = new \com\vip\order\common\pojo\order\request\RangeParam();
				$this->walletAmountRange->read($input);
				
			}
			
			
			
			
			if ("couponIdList" == $schemeField){
				
				$needSkip = false;
				
				$this->couponIdList = array();
				$_size6 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem6 = null;
						$input->readString($elem6);
						
						$this->couponIdList[$_size6++] = $elem6;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readListEnd();
				
			}
			
			
			
			
			if ("invoiceTypeList" == $schemeField){
				
				$needSkip = false;
				
				$this->invoiceTypeList = array();
				$_size7 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem7 = null;
						$input->readString($elem7);
						
						$this->invoiceTypeList[$_size7++] = $elem7;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readListEnd();
				
			}
			
			
			
			
			if ("allotTimeRange" == $schemeField){
				
				$needSkip = false;
				
				$this->allotTimeRange = new \com\vip\order\common\pojo\order\request\RangeParam();
				$this->allotTimeRange->read($input);
				
			}
			
			
			
			
			if ("orderFlag" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->orderFlag); 
				
			}
			
			
			
			
			if ("statusFlag" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->statusFlag); 
				
			}
			
			
			
			
			if ("wmsFlagList" == $schemeField){
				
				$needSkip = false;
				
				$this->wmsFlagList = array();
				$_size8 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem8 = null;
						$input->readI32($elem8); 
						
						$this->wmsFlagList[$_size8++] = $elem8;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readListEnd();
				
			}
			
			
			
			
			if ("isHold" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->isHold); 
				
			}
			
			
			
			
			if ("isSpecial" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->isSpecial); 
				
			}
			
			
			
			
			if ("isDisplay" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->isDisplay); 
				
			}
			
			
			
			
			if ("orderSourceTypeRange" == $schemeField){
				
				$needSkip = false;
				
				$this->orderSourceTypeRange = new \com\vip\order\common\pojo\order\request\RangeParam();
				$this->orderSourceTypeRange->read($input);
				
			}
			
			
			
			
			if ("specialCondition" == $schemeField){
				
				$needSkip = false;
				
				$this->specialCondition = new \com\vip\order\biz\request\SpecialCondition();
				$this->specialCondition->read($input);
				
			}
			
			
			
			
			if ("amountRange" == $schemeField){
				
				$needSkip = false;
				
				$this->amountRange = new \com\vip\order\common\pojo\order\request\RangeParam();
				$this->amountRange->read($input);
				
			}
			
			
			
			
			if ("orderCategory" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->orderCategory); 
				
			}
			
			
			
			
			if ("snType" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->snType);
				
			}
			
			
			
			
			if ("auditTimeRange" == $schemeField){
				
				$needSkip = false;
				
				$this->auditTimeRange = new \com\vip\order\common\pojo\order\request\RangeParam();
				$this->auditTimeRange->read($input);
				
			}
			
			
			
			
			if ("isFirst" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->isFirst); 
				
			}
			
			
			
			
			if ("isLock" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->isLock); 
				
			}
			
			
			
			
			if ("ip" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->ip);
				
			}
			
			
			
			
			if ("operator" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->operator);
				
			}
			
			
			
			
			if ("deliveryTypeList" == $schemeField){
				
				$needSkip = false;
				
				$this->deliveryTypeList = array();
				$_size9 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem9 = null;
						$input->readI32($elem9); 
						
						$this->deliveryTypeList[$_size9++] = $elem9;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readListEnd();
				
			}
			
			
			
			
			if ("compensateFlagList" == $schemeField){
				
				$needSkip = false;
				
				$this->compensateFlagList = array();
				$_size10 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem10 = null;
						$input->readI32($elem10); 
						
						$this->compensateFlagList[$_size10++] = $elem10;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readListEnd();
				
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
		
		if($this->userIdList !== null) {
			
			$xfer += $output->writeFieldBegin('userIdList');
			
			if (!is_array($this->userIdList)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeListBegin();
			foreach ($this->userIdList as $iter0){
				
				$xfer += $output->writeI64($iter0);
				
			}
			
			$output->writeListEnd();
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderIdList !== null) {
			
			$xfer += $output->writeFieldBegin('orderIdList');
			
			if (!is_array($this->orderIdList)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeListBegin();
			foreach ($this->orderIdList as $iter0){
				
				$xfer += $output->writeI64($iter0);
				
			}
			
			$output->writeListEnd();
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderSnList !== null) {
			
			$xfer += $output->writeFieldBegin('orderSnList');
			
			if (!is_array($this->orderSnList)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeListBegin();
			foreach ($this->orderSnList as $iter0){
				
				$xfer += $output->writeString($iter0);
				
			}
			
			$output->writeListEnd();
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->userName !== null) {
			
			$xfer += $output->writeFieldBegin('userName');
			$xfer += $output->writeString($this->userName);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->saleType !== null) {
			
			$xfer += $output->writeFieldBegin('saleType');
			
			if (!is_array($this->saleType)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeListBegin();
			foreach ($this->saleType as $iter0){
				
				$xfer += $output->writeI32($iter0);
				
			}
			
			$output->writeListEnd();
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->typeRange !== null) {
			
			$xfer += $output->writeFieldBegin('typeRange');
			
			if (!is_object($this->typeRange)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->typeRange->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderModelList !== null) {
			
			$xfer += $output->writeFieldBegin('orderModelList');
			
			if (!is_array($this->orderModelList)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeListBegin();
			foreach ($this->orderModelList as $iter0){
				
				$xfer += $output->writeI32($iter0);
				
			}
			
			$output->writeListEnd();
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->warehouse !== null) {
			
			$xfer += $output->writeFieldBegin('warehouse');
			$xfer += $output->writeString($this->warehouse);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->vipclubList !== null) {
			
			$xfer += $output->writeFieldBegin('vipclubList');
			
			if (!is_array($this->vipclubList)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeListBegin();
			foreach ($this->vipclubList as $iter0){
				
				$xfer += $output->writeString($iter0);
				
			}
			
			$output->writeListEnd();
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->statusRange !== null) {
			
			$xfer += $output->writeFieldBegin('statusRange');
			
			if (!is_object($this->statusRange)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->statusRange->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->subStatusRange !== null) {
			
			$xfer += $output->writeFieldBegin('subStatusRange');
			
			if (!is_object($this->subStatusRange)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->subStatusRange->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->payStatus !== null) {
			
			$xfer += $output->writeFieldBegin('payStatus');
			$xfer += $output->writeI32($this->payStatus);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->buyer !== null) {
			
			$xfer += $output->writeFieldBegin('buyer');
			$xfer += $output->writeString($this->buyer);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->transportSn !== null) {
			
			$xfer += $output->writeFieldBegin('transportSn');
			$xfer += $output->writeString($this->transportSn);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->transportId !== null) {
			
			$xfer += $output->writeFieldBegin('transportId');
			$xfer += $output->writeI32($this->transportId);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->mobile !== null) {
			
			$xfer += $output->writeFieldBegin('mobile');
			$xfer += $output->writeString($this->mobile);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->tel !== null) {
			
			$xfer += $output->writeFieldBegin('tel');
			$xfer += $output->writeString($this->tel);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->queryRange !== null) {
			
			$xfer += $output->writeFieldBegin('queryRange');
			$xfer += $output->writeI32($this->queryRange);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderTimeRange !== null) {
			
			$xfer += $output->writeFieldBegin('orderTimeRange');
			
			if (!is_object($this->orderTimeRange)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->orderTimeRange->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderDateRange !== null) {
			
			$xfer += $output->writeFieldBegin('orderDateRange');
			
			if (!is_object($this->orderDateRange)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->orderDateRange->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->updateTimeRange !== null) {
			
			$xfer += $output->writeFieldBegin('updateTimeRange');
			
			if (!is_object($this->updateTimeRange)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->updateTimeRange->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->payTypeRange !== null) {
			
			$xfer += $output->writeFieldBegin('payTypeRange');
			
			if (!is_object($this->payTypeRange)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->payTypeRange->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->walletAmountRange !== null) {
			
			$xfer += $output->writeFieldBegin('walletAmountRange');
			
			if (!is_object($this->walletAmountRange)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->walletAmountRange->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->couponIdList !== null) {
			
			$xfer += $output->writeFieldBegin('couponIdList');
			
			if (!is_array($this->couponIdList)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeListBegin();
			foreach ($this->couponIdList as $iter0){
				
				$xfer += $output->writeString($iter0);
				
			}
			
			$output->writeListEnd();
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->invoiceTypeList !== null) {
			
			$xfer += $output->writeFieldBegin('invoiceTypeList');
			
			if (!is_array($this->invoiceTypeList)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeListBegin();
			foreach ($this->invoiceTypeList as $iter0){
				
				$xfer += $output->writeString($iter0);
				
			}
			
			$output->writeListEnd();
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->allotTimeRange !== null) {
			
			$xfer += $output->writeFieldBegin('allotTimeRange');
			
			if (!is_object($this->allotTimeRange)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->allotTimeRange->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderFlag !== null) {
			
			$xfer += $output->writeFieldBegin('orderFlag');
			$xfer += $output->writeI32($this->orderFlag);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->statusFlag !== null) {
			
			$xfer += $output->writeFieldBegin('statusFlag');
			$xfer += $output->writeI32($this->statusFlag);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->wmsFlagList !== null) {
			
			$xfer += $output->writeFieldBegin('wmsFlagList');
			
			if (!is_array($this->wmsFlagList)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeListBegin();
			foreach ($this->wmsFlagList as $iter0){
				
				$xfer += $output->writeI32($iter0);
				
			}
			
			$output->writeListEnd();
			
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
		
		
		if($this->isDisplay !== null) {
			
			$xfer += $output->writeFieldBegin('isDisplay');
			$xfer += $output->writeI32($this->isDisplay);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderSourceTypeRange !== null) {
			
			$xfer += $output->writeFieldBegin('orderSourceTypeRange');
			
			if (!is_object($this->orderSourceTypeRange)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->orderSourceTypeRange->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->specialCondition !== null) {
			
			$xfer += $output->writeFieldBegin('specialCondition');
			
			if (!is_object($this->specialCondition)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->specialCondition->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->amountRange !== null) {
			
			$xfer += $output->writeFieldBegin('amountRange');
			
			if (!is_object($this->amountRange)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->amountRange->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderCategory !== null) {
			
			$xfer += $output->writeFieldBegin('orderCategory');
			$xfer += $output->writeI32($this->orderCategory);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->snType !== null) {
			
			$xfer += $output->writeFieldBegin('snType');
			$xfer += $output->writeString($this->snType);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->auditTimeRange !== null) {
			
			$xfer += $output->writeFieldBegin('auditTimeRange');
			
			if (!is_object($this->auditTimeRange)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->auditTimeRange->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->isFirst !== null) {
			
			$xfer += $output->writeFieldBegin('isFirst');
			$xfer += $output->writeI32($this->isFirst);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->isLock !== null) {
			
			$xfer += $output->writeFieldBegin('isLock');
			$xfer += $output->writeI32($this->isLock);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->ip !== null) {
			
			$xfer += $output->writeFieldBegin('ip');
			$xfer += $output->writeString($this->ip);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->operator !== null) {
			
			$xfer += $output->writeFieldBegin('operator');
			$xfer += $output->writeString($this->operator);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->deliveryTypeList !== null) {
			
			$xfer += $output->writeFieldBegin('deliveryTypeList');
			
			if (!is_array($this->deliveryTypeList)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeListBegin();
			foreach ($this->deliveryTypeList as $iter0){
				
				$xfer += $output->writeI32($iter0);
				
			}
			
			$output->writeListEnd();
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->compensateFlagList !== null) {
			
			$xfer += $output->writeFieldBegin('compensateFlagList');
			
			if (!is_array($this->compensateFlagList)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeListBegin();
			foreach ($this->compensateFlagList as $iter0){
				
				$xfer += $output->writeI32($iter0);
				
			}
			
			$output->writeListEnd();
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}

?>