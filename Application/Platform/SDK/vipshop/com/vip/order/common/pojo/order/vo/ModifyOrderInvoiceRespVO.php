<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\common\pojo\order\vo;

class ModifyOrderInvoiceRespVO {
	
	static $_TSPEC;
	public $orderSn = null;
	public $retCode = null;
	public $retMessage = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'orderSn'
			),
			2 => array(
			'var' => 'retCode'
			),
			3 => array(
			'var' => 'retMessage'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['orderSn'])){
				
				$this->orderSn = $vals['orderSn'];
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
		
		return 'ModifyOrderInvoiceRespVO';
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