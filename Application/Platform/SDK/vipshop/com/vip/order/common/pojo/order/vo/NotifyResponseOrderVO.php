<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\common\pojo\order\vo;

class NotifyResponseOrderVO {
	
	static $_TSPEC;
	public $orderSn = null;
	public $orderId = null;
	public $orderCode = null;
	public $paySn = null;
	public $retCode = null;
	public $retMessage = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'orderSn'
			),
			2 => array(
			'var' => 'orderId'
			),
			3 => array(
			'var' => 'orderCode'
			),
			4 => array(
			'var' => 'paySn'
			),
			5 => array(
			'var' => 'retCode'
			),
			6 => array(
			'var' => 'retMessage'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['orderSn'])){
				
				$this->orderSn = $vals['orderSn'];
			}
			
			
			if (isset($vals['orderId'])){
				
				$this->orderId = $vals['orderId'];
			}
			
			
			if (isset($vals['orderCode'])){
				
				$this->orderCode = $vals['orderCode'];
			}
			
			
			if (isset($vals['paySn'])){
				
				$this->paySn = $vals['paySn'];
			}
			
			
			if (isset($vals['retCode'])){
				
				$this->retCode = $vals['retCode'];
			}
			
			
			if (isset($vals['retMessage'])){
				
				$this->retMessage = $vals['retMessage'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'NotifyResponseOrderVO';
	}
	
	public function read($input){
		
		$input->readStructBegin();
		while(true){
			
			$schemeField = $input->readFieldBegin();
			if ($schemeField == null) break;
			$needSkip = true;
			
			
			if ("orderSn" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->orderSn);
				
			}
			
			
			
			
			if ("orderId" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->orderId); 
				
			}
			
			
			
			
			if ("orderCode" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->orderCode);
				
			}
			
			
			
			
			if ("paySn" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->paySn);
				
			}
			
			
			
			
			if ("retCode" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->retCode);
				
			}
			
			
			
			
			if ("retMessage" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->retMessage);
				
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
		
		if($this->orderSn !== null) {
			
			$xfer += $output->writeFieldBegin('orderSn');
			$xfer += $output->writeString($this->orderSn);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderId !== null) {
			
			$xfer += $output->writeFieldBegin('orderId');
			$xfer += $output->writeI64($this->orderId);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderCode !== null) {
			
			$xfer += $output->writeFieldBegin('orderCode');
			$xfer += $output->writeString($this->orderCode);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->paySn !== null) {
			
			$xfer += $output->writeFieldBegin('paySn');
			$xfer += $output->writeString($this->paySn);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->retCode !== null) {
			
			$xfer += $output->writeFieldBegin('retCode');
			$xfer += $output->writeString($this->retCode);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->retMessage !== null) {
			
			$xfer += $output->writeFieldBegin('retMessage');
			$xfer += $output->writeString($this->retMessage);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}

?>