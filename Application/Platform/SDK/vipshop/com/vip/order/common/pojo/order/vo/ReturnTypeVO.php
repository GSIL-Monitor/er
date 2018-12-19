<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\common\pojo\order\vo;

class ReturnTypeVO {
	
	static $_TSPEC;
	public $type = null;
	public $wallet = null;
	public $giftMoney = null;
	public $favMoney = null;
	public $creditCardMoney = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'type'
			),
			2 => array(
			'var' => 'wallet'
			),
			3 => array(
			'var' => 'giftMoney'
			),
			4 => array(
			'var' => 'favMoney'
			),
			5 => array(
			'var' => 'creditCardMoney'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['type'])){
				
				$this->type = $vals['type'];
			}
			
			
			if (isset($vals['wallet'])){
				
				$this->wallet = $vals['wallet'];
			}
			
			
			if (isset($vals['giftMoney'])){
				
				$this->giftMoney = $vals['giftMoney'];
			}
			
			
			if (isset($vals['favMoney'])){
				
				$this->favMoney = $vals['favMoney'];
			}
			
			
			if (isset($vals['creditCardMoney'])){
				
				$this->creditCardMoney = $vals['creditCardMoney'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'ReturnTypeVO';
	}
	
	public function read($input){
		
		$input->readStructBegin();
		while(true){
			
			$schemeField = $input->readFieldBegin();
			if ($schemeField == null) break;
			$needSkip = true;
			
			
			if ("type" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->type);
				
			}
			
			
			
			
			if ("wallet" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->wallet);
				
			}
			
			
			
			
			if ("giftMoney" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->giftMoney);
				
			}
			
			
			
			
			if ("favMoney" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->favMoney);
				
			}
			
			
			
			
			if ("creditCardMoney" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->creditCardMoney);
				
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
		
		if($this->type !== null) {
			
			$xfer += $output->writeFieldBegin('type');
			$xfer += $output->writeString($this->type);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->wallet !== null) {
			
			$xfer += $output->writeFieldBegin('wallet');
			$xfer += $output->writeString($this->wallet);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->giftMoney !== null) {
			
			$xfer += $output->writeFieldBegin('giftMoney');
			$xfer += $output->writeString($this->giftMoney);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->favMoney !== null) {
			
			$xfer += $output->writeFieldBegin('favMoney');
			$xfer += $output->writeString($this->favMoney);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->creditCardMoney !== null) {
			
			$xfer += $output->writeFieldBegin('creditCardMoney');
			$xfer += $output->writeString($this->creditCardMoney);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}

?>