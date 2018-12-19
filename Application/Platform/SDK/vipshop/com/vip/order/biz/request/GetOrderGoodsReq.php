<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\biz\request;

class GetOrderGoodsReq {
	
	static $_TSPEC;
	public $userIdSet = null;
	public $orderIdSet = null;
	public $orderSnSet = null;
	public $userName = null;
	public $saleType = null;
	public $typeList = null;
	public $vipclub = null;
	public $statusRange = null;
	public $payStatus = null;
	public $buyer = null;
	public $transportSN = null;
	public $transportId = null;
	public $mobile = null;
	public $queryRange = null;
	public $orderTimeRange = null;
	public $updateTimeRange = null;
	public $payTypeRange = null;
	public $moneyRange = null;
	public $allotTimes = null;
	public $orderFlag = null;
	public $statusFlag = null;
	public $wmsFlag = null;
	public $isHold = null;
	public $isSpecial = null;
	public $specialCondition = null;
	public $warehouse = null;
	public $orderGoodsCondition = null;
	public $vipclubList = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'userIdSet'
			),
			2 => array(
			'var' => 'orderIdSet'
			),
			3 => array(
			'var' => 'orderSnSet'
			),
			4 => array(
			'var' => 'userName'
			),
			5 => array(
			'var' => 'saleType'
			),
			6 => array(
			'var' => 'typeList'
			),
			7 => array(
			'var' => 'vipclub'
			),
			8 => array(
			'var' => 'statusRange'
			),
			9 => array(
			'var' => 'payStatus'
			),
			10 => array(
			'var' => 'buyer'
			),
			11 => array(
			'var' => 'transportSN'
			),
			12 => array(
			'var' => 'transportId'
			),
			13 => array(
			'var' => 'mobile'
			),
			14 => array(
			'var' => 'queryRange'
			),
			15 => array(
			'var' => 'orderTimeRange'
			),
			16 => array(
			'var' => 'updateTimeRange'
			),
			17 => array(
			'var' => 'payTypeRange'
			),
			18 => array(
			'var' => 'moneyRange'
			),
			19 => array(
			'var' => 'allotTimes'
			),
			20 => array(
			'var' => 'orderFlag'
			),
			21 => array(
			'var' => 'statusFlag'
			),
			22 => array(
			'var' => 'wmsFlag'
			),
			23 => array(
			'var' => 'isHold'
			),
			24 => array(
			'var' => 'isSpecial'
			),
			25 => array(
			'var' => 'specialCondition'
			),
			26 => array(
			'var' => 'warehouse'
			),
			27 => array(
			'var' => 'orderGoodsCondition'
			),
			28 => array(
			'var' => 'vipclubList'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['userIdSet'])){
				
				$this->userIdSet = $vals['userIdSet'];
			}
			
			
			if (isset($vals['orderIdSet'])){
				
				$this->orderIdSet = $vals['orderIdSet'];
			}
			
			
			if (isset($vals['orderSnSet'])){
				
				$this->orderSnSet = $vals['orderSnSet'];
			}
			
			
			if (isset($vals['userName'])){
				
				$this->userName = $vals['userName'];
			}
			
			
			if (isset($vals['saleType'])){
				
				$this->saleType = $vals['saleType'];
			}
			
			
			if (isset($vals['typeList'])){
				
				$this->typeList = $vals['typeList'];
			}
			
			
			if (isset($vals['vipclub'])){
				
				$this->vipclub = $vals['vipclub'];
			}
			
			
			if (isset($vals['statusRange'])){
				
				$this->statusRange = $vals['statusRange'];
			}
			
			
			if (isset($vals['payStatus'])){
				
				$this->payStatus = $vals['payStatus'];
			}
			
			
			if (isset($vals['buyer'])){
				
				$this->buyer = $vals['buyer'];
			}
			
			
			if (isset($vals['transportSN'])){
				
				$this->transportSN = $vals['transportSN'];
			}
			
			
			if (isset($vals['transportId'])){
				
				$this->transportId = $vals['transportId'];
			}
			
			
			if (isset($vals['mobile'])){
				
				$this->mobile = $vals['mobile'];
			}
			
			
			if (isset($vals['queryRange'])){
				
				$this->queryRange = $vals['queryRange'];
			}
			
			
			if (isset($vals['orderTimeRange'])){
				
				$this->orderTimeRange = $vals['orderTimeRange'];
			}
			
			
			if (isset($vals['updateTimeRange'])){
				
				$this->updateTimeRange = $vals['updateTimeRange'];
			}
			
			
			if (isset($vals['payTypeRange'])){
				
				$this->payTypeRange = $vals['payTypeRange'];
			}
			
			
			if (isset($vals['moneyRange'])){
				
				$this->moneyRange = $vals['moneyRange'];
			}
			
			
			if (isset($vals['allotTimes'])){
				
				$this->allotTimes = $vals['allotTimes'];
			}
			
			
			if (isset($vals['orderFlag'])){
				
				$this->orderFlag = $vals['orderFlag'];
			}
			
			
			if (isset($vals['statusFlag'])){
				
				$this->statusFlag = $vals['statusFlag'];
			}
			
			
			if (isset($vals['wmsFlag'])){
				
				$this->wmsFlag = $vals['wmsFlag'];
			}
			
			
			if (isset($vals['isHold'])){
				
				$this->isHold = $vals['isHold'];
			}
			
			
			if (isset($vals['isSpecial'])){
				
				$this->isSpecial = $vals['isSpecial'];
			}
			
			
			if (isset($vals['specialCondition'])){
				
				$this->specialCondition = $vals['specialCondition'];
			}
			
			
			if (isset($vals['warehouse'])){
				
				$this->warehouse = $vals['warehouse'];
			}
			
			
			if (isset($vals['orderGoodsCondition'])){
				
				$this->orderGoodsCondition = $vals['orderGoodsCondition'];
			}
			
			
			if (isset($vals['vipclubList'])){
				
				$this->vipclubList = $vals['vipclubList'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'GetOrderGoodsReq';
	}
	
	public function read($input){
		
		$input->readStructBegin();
		while(true){
			
			$schemeField = $input->readFieldBegin();
			if ($schemeField == null) break;
			$needSkip = true;
			
			
			if ("userIdSet" == $schemeField){
				
				$needSkip = false;
				
				$this->userIdSet = array();
				$_size0 = 0;
				$input->readSetBegin();
				while(true){
					
					try{
						
						$elem0 = null;
						$input->readI64($elem0); 
						
						$this->userIdSet[$_size0++] = $elem0;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readSetEnd();
				
			}
			
			
			
			
			if ("orderIdSet" == $schemeField){
				
				$needSkip = false;
				
				$this->orderIdSet = array();
				$_size1 = 0;
				$input->readSetBegin();
				while(true){
					
					try{
						
						$elem1 = null;
						$input->readI64($elem1); 
						
						$this->orderIdSet[$_size1++] = $elem1;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readSetEnd();
				
			}
			
			
			
			
			if ("orderSnSet" == $schemeField){
				
				$needSkip = false;
				
				$this->orderSnSet = array();
				$_size2 = 0;
				$input->readSetBegin();
				while(true){
					
					try{
						
						$elem2 = null;
						$input->readString($elem2);
						
						$this->orderSnSet[$_size2++] = $elem2;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readSetEnd();
				
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
			
			
			
			
			if ("typeList" == $schemeField){
				
				$needSkip = false;
				
				$this->typeList = array();
				$_size4 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem4 = null;
						$input->readI32($elem4); 
						
						$this->typeList[$_size4++] = $elem4;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readListEnd();
				
			}
			
			
			
			
			if ("vipclub" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->vipclub);
				
			}
			
			
			
			
			if ("statusRange" == $schemeField){
				
				$needSkip = false;
				
				$this->statusRange = new \com\vip\order\common\pojo\order\request\RangeParam();
				$this->statusRange->read($input);
				
			}
			
			
			
			
			if ("payStatus" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->payStatus); 
				
			}
			
			
			
			
			if ("buyer" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->buyer);
				
			}
			
			
			
			
			if ("transportSN" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->transportSN);
				
			}
			
			
			
			
			if ("transportId" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->transportId); 
				
			}
			
			
			
			
			if ("mobile" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->mobile);
				
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
			
			
			
			
			if ("moneyRange" == $schemeField){
				
				$needSkip = false;
				
				$this->moneyRange = new \com\vip\order\common\pojo\order\request\RangeParam();
				$this->moneyRange->read($input);
				
			}
			
			
			
			
			if ("allotTimes" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->allotTimes);
				
			}
			
			
			
			
			if ("orderFlag" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->orderFlag); 
				
			}
			
			
			
			
			if ("statusFlag" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->statusFlag); 
				
			}
			
			
			
			
			if ("wmsFlag" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->wmsFlag); 
				
			}
			
			
			
			
			if ("isHold" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->isHold); 
				
			}
			
			
			
			
			if ("isSpecial" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->isSpecial); 
				
			}
			
			
			
			
			if ("specialCondition" == $schemeField){
				
				$needSkip = false;
				
				$this->specialCondition = new \com\vip\order\biz\request\SpecialCondition();
				$this->specialCondition->read($input);
				
			}
			
			
			
			
			if ("warehouse" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->warehouse);
				
			}
			
			
			
			
			if ("orderGoodsCondition" == $schemeField){
				
				$needSkip = false;
				
				$this->orderGoodsCondition = new \com\vip\order\biz\request\OrderGoodsCondition();
				$this->orderGoodsCondition->read($input);
				
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
		
		if($this->userIdSet !== null) {
			
			$xfer += $output->writeFieldBegin('userIdSet');
			
			if (!is_array($this->userIdSet)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeSetBegin();
			foreach ($this->userIdSet as $iter0){
				
				$xfer += $output->writeI64($iter0);
				
			}
			
			$output->writeSetEnd();
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderIdSet !== null) {
			
			$xfer += $output->writeFieldBegin('orderIdSet');
			
			if (!is_array($this->orderIdSet)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeSetBegin();
			foreach ($this->orderIdSet as $iter0){
				
				$xfer += $output->writeI64($iter0);
				
			}
			
			$output->writeSetEnd();
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderSnSet !== null) {
			
			$xfer += $output->writeFieldBegin('orderSnSet');
			
			if (!is_array($this->orderSnSet)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeSetBegin();
			foreach ($this->orderSnSet as $iter0){
				
				$xfer += $output->writeString($iter0);
				
			}
			
			$output->writeSetEnd();
			
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
		
		
		if($this->typeList !== null) {
			
			$xfer += $output->writeFieldBegin('typeList');
			
			if (!is_array($this->typeList)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeListBegin();
			foreach ($this->typeList as $iter0){
				
				$xfer += $output->writeI32($iter0);
				
			}
			
			$output->writeListEnd();
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->vipclub !== null) {
			
			$xfer += $output->writeFieldBegin('vipclub');
			$xfer += $output->writeString($this->vipclub);
			
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
		
		
		if($this->transportSN !== null) {
			
			$xfer += $output->writeFieldBegin('transportSN');
			$xfer += $output->writeString($this->transportSN);
			
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
		
		
		if($this->moneyRange !== null) {
			
			$xfer += $output->writeFieldBegin('moneyRange');
			
			if (!is_object($this->moneyRange)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->moneyRange->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->allotTimes !== null) {
			
			$xfer += $output->writeFieldBegin('allotTimes');
			$xfer += $output->writeString($this->allotTimes);
			
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
		
		
		if($this->wmsFlag !== null) {
			
			$xfer += $output->writeFieldBegin('wmsFlag');
			$xfer += $output->writeI32($this->wmsFlag);
			
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
		
		
		if($this->specialCondition !== null) {
			
			$xfer += $output->writeFieldBegin('specialCondition');
			
			if (!is_object($this->specialCondition)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->specialCondition->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->warehouse !== null) {
			
			$xfer += $output->writeFieldBegin('warehouse');
			$xfer += $output->writeString($this->warehouse);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderGoodsCondition !== null) {
			
			$xfer += $output->writeFieldBegin('orderGoodsCondition');
			
			if (!is_object($this->orderGoodsCondition)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->orderGoodsCondition->write($output);
			
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
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}

?>