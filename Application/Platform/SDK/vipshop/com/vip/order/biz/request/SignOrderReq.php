<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\biz\request;

class SignOrderReq {
	
	static $_TSPEC;
	public $orderSnAndUserId = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'orderSnAndUserId'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['orderSnAndUserId'])){
				
				$this->orderSnAndUserId = $vals['orderSnAndUserId'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'SignOrderReq';
	}
	
	public function read($input){
		
		$input->readStructBegin();
		while(true){
			
			$schemeField = $input->readFieldBegin();
			if ($schemeField == null) break;
			$needSkip = true;
			
			
			if ("orderSnAndUserId" == $schemeField){
				
				$needSkip = false;
				
				$this->orderSnAndUserId = new \com\vip\order\common\pojo\order\vo\OrderSnAndIdVO();
				$this->orderSnAndUserId->read($input);
				
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
		
		if($this->orderSnAndUserId !== null) {
			
			$xfer += $output->writeFieldBegin('orderSnAndUserId');
			
			if (!is_object($this->orderSnAndUserId)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->orderSnAndUserId->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}

?>