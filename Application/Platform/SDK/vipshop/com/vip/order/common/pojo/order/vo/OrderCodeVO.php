<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\common\pojo\order\vo;

class OrderCodeVO {
	
	static $_TSPEC;
	public $orderCodeId = null;
	public $orderCode = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'orderCodeId'
			),
			2 => array(
			'var' => 'orderCode'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['orderCodeId'])){
				
				$this->orderCodeId = $vals['orderCodeId'];
			}
			
			
			if (isset($vals['orderCode'])){
				
				$this->orderCode = $vals['orderCode'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'OrderCodeVO';
	}
	
	public function read($input){
		
		$input->readStructBegin();
		while(true){
			
			$schemeField = $input->readFieldBegin();
			if ($schemeField == null) break;
			$needSkip = true;
			
			
			if ("orderCodeId" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->orderCodeId); 
				
			}
			
			
			
			
			if ("orderCode" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->orderCode);
				
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
		
		if($this->orderCodeId !== null) {
			
			$xfer += $output->writeFieldBegin('orderCodeId');
			$xfer += $output->writeI64($this->orderCodeId);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderCode !== null) {
			
			$xfer += $output->writeFieldBegin('orderCode');
			$xfer += $output->writeString($this->orderCode);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}

?>