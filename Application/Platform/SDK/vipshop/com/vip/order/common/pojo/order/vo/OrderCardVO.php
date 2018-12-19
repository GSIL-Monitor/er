<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\common\pojo\order\vo;

class OrderCardVO {
	
	static $_TSPEC;
	public $cardType = null;
	public $addTime = null;
	public $stopTime = null;
	public $state = null;
	public $cardCode = null;
	public $money = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'cardType'
			),
			2 => array(
			'var' => 'addTime'
			),
			3 => array(
			'var' => 'stopTime'
			),
			4 => array(
			'var' => 'state'
			),
			5 => array(
			'var' => 'cardCode'
			),
			6 => array(
			'var' => 'money'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['cardType'])){
				
				$this->cardType = $vals['cardType'];
			}
			
			
			if (isset($vals['addTime'])){
				
				$this->addTime = $vals['addTime'];
			}
			
			
			if (isset($vals['stopTime'])){
				
				$this->stopTime = $vals['stopTime'];
			}
			
			
			if (isset($vals['state'])){
				
				$this->state = $vals['state'];
			}
			
			
			if (isset($vals['cardCode'])){
				
				$this->cardCode = $vals['cardCode'];
			}
			
			
			if (isset($vals['money'])){
				
				$this->money = $vals['money'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'OrderCardVO';
	}
	
	public function read($input){
		
		$input->readStructBegin();
		while(true){
			
			$schemeField = $input->readFieldBegin();
			if ($schemeField == null) break;
			$needSkip = true;
			
			
			if ("cardType" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->cardType);
				
			}
			
			
			
			
			if ("addTime" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->addTime); 
				
			}
			
			
			
			
			if ("stopTime" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->stopTime); 
				
			}
			
			
			
			
			if ("state" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->state); 
				
			}
			
			
			
			
			if ("cardCode" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->cardCode);
				
			}
			
			
			
			
			if ("money" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->money);
				
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
		
		if($this->cardType !== null) {
			
			$xfer += $output->writeFieldBegin('cardType');
			$xfer += $output->writeString($this->cardType);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->addTime !== null) {
			
			$xfer += $output->writeFieldBegin('addTime');
			$xfer += $output->writeI64($this->addTime);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->stopTime !== null) {
			
			$xfer += $output->writeFieldBegin('stopTime');
			$xfer += $output->writeI64($this->stopTime);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->state !== null) {
			
			$xfer += $output->writeFieldBegin('state');
			$xfer += $output->writeI32($this->state);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->cardCode !== null) {
			
			$xfer += $output->writeFieldBegin('cardCode');
			$xfer += $output->writeString($this->cardCode);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->money !== null) {
			
			$xfer += $output->writeFieldBegin('money');
			$xfer += $output->writeString($this->money);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}

?>