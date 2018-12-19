<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\common\pojo\order\vo;

class OrderGoodsBackVO {
	
	static $_TSPEC;
	public $id = null;
	public $orderId = null;
	public $orderGoodsId = null;
	public $oldAmount = null;
	public $amount = null;
	public $spoilNum = null;
	public $opType = null;
	public $reason = null;
	public $changeSize = null;
	public $addTime = null;
	public $orderSn = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'id'
			),
			2 => array(
			'var' => 'orderId'
			),
			3 => array(
			'var' => 'orderGoodsId'
			),
			4 => array(
			'var' => 'oldAmount'
			),
			5 => array(
			'var' => 'amount'
			),
			6 => array(
			'var' => 'spoilNum'
			),
			7 => array(
			'var' => 'opType'
			),
			8 => array(
			'var' => 'reason'
			),
			9 => array(
			'var' => 'changeSize'
			),
			10 => array(
			'var' => 'addTime'
			),
			11 => array(
			'var' => 'orderSn'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['id'])){
				
				$this->id = $vals['id'];
			}
			
			
			if (isset($vals['orderId'])){
				
				$this->orderId = $vals['orderId'];
			}
			
			
			if (isset($vals['orderGoodsId'])){
				
				$this->orderGoodsId = $vals['orderGoodsId'];
			}
			
			
			if (isset($vals['oldAmount'])){
				
				$this->oldAmount = $vals['oldAmount'];
			}
			
			
			if (isset($vals['amount'])){
				
				$this->amount = $vals['amount'];
			}
			
			
			if (isset($vals['spoilNum'])){
				
				$this->spoilNum = $vals['spoilNum'];
			}
			
			
			if (isset($vals['opType'])){
				
				$this->opType = $vals['opType'];
			}
			
			
			if (isset($vals['reason'])){
				
				$this->reason = $vals['reason'];
			}
			
			
			if (isset($vals['changeSize'])){
				
				$this->changeSize = $vals['changeSize'];
			}
			
			
			if (isset($vals['addTime'])){
				
				$this->addTime = $vals['addTime'];
			}
			
			
			if (isset($vals['orderSn'])){
				
				$this->orderSn = $vals['orderSn'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'OrderGoodsBackVO';
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
			
			
			
			
			if ("orderId" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->orderId); 
				
			}
			
			
			
			
			if ("orderGoodsId" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->orderGoodsId); 
				
			}
			
			
			
			
			if ("oldAmount" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->oldAmount); 
				
			}
			
			
			
			
			if ("amount" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->amount); 
				
			}
			
			
			
			
			if ("spoilNum" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->spoilNum); 
				
			}
			
			
			
			
			if ("opType" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->opType); 
				
			}
			
			
			
			
			if ("reason" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->reason);
				
			}
			
			
			
			
			if ("changeSize" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->changeSize);
				
			}
			
			
			
			
			if ("addTime" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->addTime); 
				
			}
			
			
			
			
			if ("orderSn" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->orderSn);
				
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
		
		
		if($this->orderId !== null) {
			
			$xfer += $output->writeFieldBegin('orderId');
			$xfer += $output->writeI64($this->orderId);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderGoodsId !== null) {
			
			$xfer += $output->writeFieldBegin('orderGoodsId');
			$xfer += $output->writeI64($this->orderGoodsId);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->oldAmount !== null) {
			
			$xfer += $output->writeFieldBegin('oldAmount');
			$xfer += $output->writeI32($this->oldAmount);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->amount !== null) {
			
			$xfer += $output->writeFieldBegin('amount');
			$xfer += $output->writeI32($this->amount);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->spoilNum !== null) {
			
			$xfer += $output->writeFieldBegin('spoilNum');
			$xfer += $output->writeI32($this->spoilNum);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->opType !== null) {
			
			$xfer += $output->writeFieldBegin('opType');
			$xfer += $output->writeI32($this->opType);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->reason !== null) {
			
			$xfer += $output->writeFieldBegin('reason');
			$xfer += $output->writeString($this->reason);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->changeSize !== null) {
			
			$xfer += $output->writeFieldBegin('changeSize');
			$xfer += $output->writeString($this->changeSize);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->addTime !== null) {
			
			$xfer += $output->writeFieldBegin('addTime');
			$xfer += $output->writeI64($this->addTime);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderSn !== null) {
			
			$xfer += $output->writeFieldBegin('orderSn');
			$xfer += $output->writeString($this->orderSn);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}

?>