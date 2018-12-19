<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\biz\vo;

class OrderCookieVO {
	
	static $_TSPEC;
	public $phpsessionId = null;
	public $marsCid = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'phpsessionId'
			),
			2 => array(
			'var' => 'marsCid'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['phpsessionId'])){
				
				$this->phpsessionId = $vals['phpsessionId'];
			}
			
			
			if (isset($vals['marsCid'])){
				
				$this->marsCid = $vals['marsCid'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'OrderCookieVO';
	}
	
	public function read($input){
		
		$input->readStructBegin();
		while(true){
			
			$schemeField = $input->readFieldBegin();
			if ($schemeField == null) break;
			$needSkip = true;
			
			
			if ("phpsessionId" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->phpsessionId);
				
			}
			
			
			
			
			if ("marsCid" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->marsCid);
				
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
		
		if($this->phpsessionId !== null) {
			
			$xfer += $output->writeFieldBegin('phpsessionId');
			$xfer += $output->writeString($this->phpsessionId);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->marsCid !== null) {
			
			$xfer += $output->writeFieldBegin('marsCid');
			$xfer += $output->writeString($this->marsCid);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}

?>