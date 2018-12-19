<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\common\pojo\order\vo;

class OrderBackBankVO {
	
	static $_TSPEC;
	public $bankName = null;
	public $bankBranch = null;
	public $name = null;
	public $account = null;
	public $money = null;
	public $addTime = null;
	public $id = null;
	public $orderId = null;
	public $orderSn = null;
	public $userId = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'bankName'
			),
			2 => array(
			'var' => 'bankBranch'
			),
			3 => array(
			'var' => 'name'
			),
			4 => array(
			'var' => 'account'
			),
			5 => array(
			'var' => 'money'
			),
			6 => array(
			'var' => 'addTime'
			),
			7 => array(
			'var' => 'id'
			),
			8 => array(
			'var' => 'orderId'
			),
			9 => array(
			'var' => 'orderSn'
			),
			10 => array(
			'var' => 'userId'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['bankName'])){
				
				$this->bankName = $vals['bankName'];
			}
			
			
			if (isset($vals['bankBranch'])){
				
				$this->bankBranch = $vals['bankBranch'];
			}
			
			
			if (isset($vals['name'])){
				
				$this->name = $vals['name'];
			}
			
			
			if (isset($vals['account'])){
				
				$this->account = $vals['account'];
			}
			
			
			if (isset($vals['money'])){
				
				$this->money = $vals['money'];
			}
			
			
			if (isset($vals['addTime'])){
				
				$this->addTime = $vals['addTime'];
			}
			
			
			if (isset($vals['id'])){
				
				$this->id = $vals['id'];
			}
			
			
			if (isset($vals['orderId'])){
				
				$this->orderId = $vals['orderId'];
			}
			
			
			if (isset($vals['orderSn'])){
				
				$this->orderSn = $vals['orderSn'];
			}
			
			
			if (isset($vals['userId'])){
				
				$this->userId = $vals['userId'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'OrderBackBankVO';
	}
	
	public function read($input){
		
		$input->readStructBegin();
		while(true){
			
			$schemeField = $input->readFieldBegin();
			if ($schemeField == null) break;
			$needSkip = true;
			
			
			if ("bankName" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->bankName);
				
			}
			
			
			
			
			if ("bankBranch" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->bankBranch);
				
			}
			
			
			
			
			if ("name" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->name);
				
			}
			
			
			
			
			if ("account" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->account);
				
			}
			
			
			
			
			if ("money" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->money);
				
			}
			
			
			
			
			if ("addTime" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->addTime); 
				
			}
			
			
			
			
			if ("id" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->id); 
				
			}
			
			
			
			
			if ("orderId" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->orderId); 
				
			}
			
			
			
			
			if ("orderSn" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->orderSn);
				
			}
			
			
			
			
			if ("userId" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->userId); 
				
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
		
		if($this->bankName !== null) {
			
			$xfer += $output->writeFieldBegin('bankName');
			$xfer += $output->writeString($this->bankName);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->bankBranch !== null) {
			
			$xfer += $output->writeFieldBegin('bankBranch');
			$xfer += $output->writeString($this->bankBranch);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->name !== null) {
			
			$xfer += $output->writeFieldBegin('name');
			$xfer += $output->writeString($this->name);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->account !== null) {
			
			$xfer += $output->writeFieldBegin('account');
			$xfer += $output->writeString($this->account);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->money !== null) {
			
			$xfer += $output->writeFieldBegin('money');
			$xfer += $output->writeString($this->money);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->addTime !== null) {
			
			$xfer += $output->writeFieldBegin('addTime');
			$xfer += $output->writeI64($this->addTime);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
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
		
		
		if($this->orderSn !== null) {
			
			$xfer += $output->writeFieldBegin('orderSn');
			$xfer += $output->writeString($this->orderSn);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->userId !== null) {
			
			$xfer += $output->writeFieldBegin('userId');
			$xfer += $output->writeI64($this->userId);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}

?>