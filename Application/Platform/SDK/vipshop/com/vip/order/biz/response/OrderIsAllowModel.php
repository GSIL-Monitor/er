<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\biz\response;

class OrderIsAllowModel {
	
	static $_TSPEC;
	public $isAllow = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'isAllow'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['isAllow'])){
				
				$this->isAllow = $vals['isAllow'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'OrderIsAllowModel';
	}
	
	public function read($input){
		
		$input->readStructBegin();
		while(true){
			
			$schemeField = $input->readFieldBegin();
			if ($schemeField == null) break;
			$needSkip = true;
			
			
			if ("isAllow" == $schemeField){
				
				$needSkip = false;
				$input->readBool($this->isAllow);
				
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
		
		if($this->isAllow !== null) {
			
			$xfer += $output->writeFieldBegin('isAllow');
			$xfer += $output->writeBool($this->isAllow);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}

?>