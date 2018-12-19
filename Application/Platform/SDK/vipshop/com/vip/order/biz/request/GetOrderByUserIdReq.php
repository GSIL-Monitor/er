<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\biz\request;

class GetOrderByUserIdReq {
	
	static $_TSPEC;
	public $userId = null;
	public $orderIdList = null;
	public $orderSnList = null;
	public $orderCategory = null;
	public $snType = null;
	public $saleType = null;
	public $typeRange = null;
	public $orderModelList = null;
	public $vipclubList = null;
	public $statusRange = null;
	public $subStatusRange = null;
	public $payStatus = null;
	public $orderTimeRange = null;
	public $orderDateRange = null;
	public $isDisplay = null;
	public $orderSourceTypeRange = null;
	public $isFirst = null;
	public $isLock = null;
	public $deliveryTypeList = null;
	public $compensateFlagList = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'userId'
			),
			2 => array(
			'var' => 'orderIdList'
			),
			3 => array(
			'var' => 'orderSnList'
			),
			4 => array(
			'var' => 'orderCategory'
			),
			5 => array(
			'var' => 'snType'
			),
			6 => array(
			'var' => 'saleType'
			),
			7 => array(
			'var' => 'typeRange'
			),
			8 => array(
			'var' => 'orderModelList'
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
			'var' => 'orderTimeRange'
			),
			14 => array(
			'var' => 'orderDateRange'
			),
			15 => array(
			'var' => 'isDisplay'
			),
			16 => array(
			'var' => 'orderSourceTypeRange'
			),
			17 => array(
			'var' => 'isFirst'
			),
			18 => array(
			'var' => 'isLock'
			),
			19 => array(
			'var' => 'deliveryTypeList'
			),
			20 => array(
			'var' => 'compensateFlagList'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['userId'])){
				
				$this->userId = $vals['userId'];
			}
			
			
			if (isset($vals['orderIdList'])){
				
				$this->orderIdList = $vals['orderIdList'];
			}
			
			
			if (isset($vals['orderSnList'])){
				
				$this->orderSnList = $vals['orderSnList'];
			}
			
			
			if (isset($vals['orderCategory'])){
				
				$this->orderCategory = $vals['orderCategory'];
			}
			
			
			if (isset($vals['snType'])){
				
				$this->snType = $vals['snType'];
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
			
			
			if (isset($vals['orderTimeRange'])){
				
				$this->orderTimeRange = $vals['orderTimeRange'];
			}
			
			
			if (isset($vals['orderDateRange'])){
				
				$this->orderDateRange = $vals['orderDateRange'];
			}
			
			
			if (isset($vals['isDisplay'])){
				
				$this->isDisplay = $vals['isDisplay'];
			}
			
			
			if (isset($vals['orderSourceTypeRange'])){
				
				$this->orderSourceTypeRange = $vals['orderSourceTypeRange'];
			}
			
			
			if (isset($vals['isFirst'])){
				
				$this->isFirst = $vals['isFirst'];
			}
			
			
			if (isset($vals['isLock'])){
				
				$this->isLock = $vals['isLock'];
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
		
		return 'GetOrderByUserIdReq';
	}
	
	public function read($input){
		
		$input->readStructBegin();
		while(true){
			
			$schemeField = $input->readFieldBegin();
			if ($schemeField == null) break;
			$needSkip = true;
			
			
			if ("userId" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->userId); 
				
			}
			
			
			
			
			if ("orderIdList" == $schemeField){
				
				$needSkip = false;
				
				$this->orderIdList = array();
				$_size0 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem0 = null;
						$input->readI64($elem0); 
						
						$this->orderIdList[$_size0++] = $elem0;
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
				$_size1 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem1 = null;
						$input->readString($elem1);
						
						$this->orderSnList[$_size1++] = $elem1;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readListEnd();
				
			}
			
			
			
			
			if ("orderCategory" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->orderCategory); 
				
			}
			
			
			
			
			if ("snType" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->snType);
				
			}
			
			
			
			
			if ("saleType" == $schemeField){
				
				$needSkip = false;
				
				$this->saleType = array();
				$_size2 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem2 = null;
						$input->readI32($elem2); 
						
						$this->saleType[$_size2++] = $elem2;
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
				$_size3 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem3 = null;
						$input->readI32($elem3); 
						
						$this->orderModelList[$_size3++] = $elem3;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readListEnd();
				
			}
			
			
			
			
			if ("vipclubList" == $schemeField){
				
				$needSkip = false;
				
				$this->vipclubList = array();
				$_size4 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem4 = null;
						$input->readString($elem4);
						
						$this->vipclubList[$_size4++] = $elem4;
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
			
			
			
			
			if ("isDisplay" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->isDisplay); 
				
			}
			
			
			
			
			if ("orderSourceTypeRange" == $schemeField){
				
				$needSkip = false;
				
				$this->orderSourceTypeRange = new \com\vip\order\common\pojo\order\request\RangeParam();
				$this->orderSourceTypeRange->read($input);
				
			}
			
			
			
			
			if ("isFirst" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->isFirst); 
				
			}
			
			
			
			
			if ("isLock" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->isLock); 
				
			}
			
			
			
			
			if ("deliveryTypeList" == $schemeField){
				
				$needSkip = false;
				
				$this->deliveryTypeList = array();
				$_size5 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem5 = null;
						$input->readI32($elem5); 
						
						$this->deliveryTypeList[$_size5++] = $elem5;
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
				$_size6 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem6 = null;
						$input->readI32($elem6); 
						
						$this->compensateFlagList[$_size6++] = $elem6;
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
		
		if($this->userId !== null) {
			
			$xfer += $output->writeFieldBegin('userId');
			$xfer += $output->writeI64($this->userId);
			
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