<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\common\pojo\order\vo;

class OrderCompensateVO {
	
	static $_TSPEC;
	public $id = null;
	public $userId = null;
	public $orderId = null;
	public $orderSn = null;
	public $compensateType = null;
	public $compensateWay = null;
	public $money = null;
	public $compensateTime = null;
	public $reason = null;
	public $compensateStatus = null;
	public $addTime = null;
	public $procTimes = null;
	public $updateTime = null;
	public $isDeleted = null;
	public $operateSys = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'id'
			),
			2 => array(
			'var' => 'userId'
			),
			3 => array(
			'var' => 'orderId'
			),
			4 => array(
			'var' => 'orderSn'
			),
			5 => array(
			'var' => 'compensateType'
			),
			6 => array(
			'var' => 'compensateWay'
			),
			7 => array(
			'var' => 'money'
			),
			8 => array(
			'var' => 'compensateTime'
			),
			9 => array(
			'var' => 'reason'
			),
			10 => array(
			'var' => 'compensateStatus'
			),
			11 => array(
			'var' => 'addTime'
			),
			12 => array(
			'var' => 'procTimes'
			),
			13 => array(
			'var' => 'updateTime'
			),
			14 => array(
			'var' => 'isDeleted'
			),
			15 => array(
			'var' => 'operateSys'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['id'])){
				
				$this->id = $vals['id'];
			}
			
			
			if (isset($vals['userId'])){
				
				$this->userId = $vals['userId'];
			}
			
			
			if (isset($vals['orderId'])){
				
				$this->orderId = $vals['orderId'];
			}
			
			
			if (isset($vals['orderSn'])){
				
				$this->orderSn = $vals['orderSn'];
			}
			
			
			if (isset($vals['compensateType'])){
				
				$this->compensateType = $vals['compensateType'];
			}
			
			
			if (isset($vals['compensateWay'])){
				
				$this->compensateWay = $vals['compensateWay'];
			}
			
			
			if (isset($vals['money'])){
				
				$this->money = $vals['money'];
			}
			
			
			if (isset($vals['compensateTime'])){
				
				$this->compensateTime = $vals['compensateTime'];
			}
			
			
			if (isset($vals['reason'])){
				
				$this->reason = $vals['reason'];
			}
			
			
			if (isset($vals['compensateStatus'])){
				
				$this->compensateStatus = $vals['compensateStatus'];
			}
			
			
			if (isset($vals['addTime'])){
				
				$this->addTime = $vals['addTime'];
			}
			
			
			if (isset($vals['procTimes'])){
				
				$this->procTimes = $vals['procTimes'];
			}
			
			
			if (isset($vals['updateTime'])){
				
				$this->updateTime = $vals['updateTime'];
			}
			
			
			if (isset($vals['isDeleted'])){
				
				$this->isDeleted = $vals['isDeleted'];
			}
			
			
			if (isset($vals['operateSys'])){
				
				$this->operateSys = $vals['operateSys'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'OrderCompensateVO';
	}
	
	public function read($input){
		
		$input->readStructBegin();
		while(true){
			
			$schemeField = $input->readFieldBegin();
			if ($schemeField == null) break;
			$needSkip = true;
			
			
			if ("id" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->id); 
				
			}
			
			
			
			
			if ("userId" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->userId); 
				
			}
			
			
			
			
			if ("orderId" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->orderId); 
				
			}
			
			
			
			
			if ("orderSn" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->orderSn);
				
			}
			
			
			
			
			if ("compensateType" == $schemeField){
				
				$needSkip = false;
				$input->readByte($this->compensateType); 
				
			}
			
			
			
			
			if ("compensateWay" == $schemeField){
				
				$needSkip = false;
				$input->readByte($this->compensateWay); 
				
			}
			
			
			
			
			if ("money" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->money);
				
			}
			
			
			
			
			if ("compensateTime" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->compensateTime); 
				
			}
			
			
			
			
			if ("reason" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->reason);
				
			}
			
			
			
			
			if ("compensateStatus" == $schemeField){
				
				$needSkip = false;
				$input->readByte($this->compensateStatus); 
				
			}
			
			
			
			
			if ("addTime" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->addTime); 
				
			}
			
			
			
			
			if ("procTimes" == $schemeField){
				
				$needSkip = false;
				$input->readByte($this->procTimes); 
				
			}
			
			
			
			
			if ("updateTime" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->updateTime); 
				
			}
			
			
			
			
			if ("isDeleted" == $schemeField){
				
				$needSkip = false;
				$input->readBool($this->isDeleted);
				
			}
			
			
			
			
			if ("operateSys" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->operateSys);
				
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
		
		if($this->id !== null) {
			
			$xfer += $output->writeFieldBegin('id');
			$xfer += $output->writeI64($this->id);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->userId !== null) {
			
			$xfer += $output->writeFieldBegin('userId');
			$xfer += $output->writeI64($this->userId);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderId !== null) {
			
			$xfer += $output->writeFieldBegin('orderId');
			$xfer += $output->writeI64($this->orderId);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderSn !== null) {
			
			$xfer += $output->writeFieldBegin('orderSn');
			$xfer += $output->writeString($this->orderSn);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->compensateType !== null) {
			
			$xfer += $output->writeFieldBegin('compensateType');
			$xfer += $output->writeByte($this->compensateType);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->compensateWay !== null) {
			
			$xfer += $output->writeFieldBegin('compensateWay');
			$xfer += $output->writeByte($this->compensateWay);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->money !== null) {
			
			$xfer += $output->writeFieldBegin('money');
			$xfer += $output->writeString($this->money);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->compensateTime !== null) {
			
			$xfer += $output->writeFieldBegin('compensateTime');
			$xfer += $output->writeI32($this->compensateTime);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->reason !== null) {
			
			$xfer += $output->writeFieldBegin('reason');
			$xfer += $output->writeString($this->reason);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->compensateStatus !== null) {
			
			$xfer += $output->writeFieldBegin('compensateStatus');
			$xfer += $output->writeByte($this->compensateStatus);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->addTime !== null) {
			
			$xfer += $output->writeFieldBegin('addTime');
			$xfer += $output->writeI32($this->addTime);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->procTimes !== null) {
			
			$xfer += $output->writeFieldBegin('procTimes');
			$xfer += $output->writeByte($this->procTimes);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->updateTime !== null) {
			
			$xfer += $output->writeFieldBegin('updateTime');
			$xfer += $output->writeI64($this->updateTime);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->isDeleted !== null) {
			
			$xfer += $output->writeFieldBegin('isDeleted');
			$xfer += $output->writeBool($this->isDeleted);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->operateSys !== null) {
			
			$xfer += $output->writeFieldBegin('operateSys');
			$xfer += $output->writeString($this->operateSys);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}

?>