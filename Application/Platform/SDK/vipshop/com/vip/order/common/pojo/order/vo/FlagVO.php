<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\common\pojo\order\vo;

class FlagVO {
	
	static $_TSPEC;
	public $flagCode = null;
	public $time = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'flagCode'
			),
			2 => array(
			'var' => 'time'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['flagCode'])){
				
				$this->flagCode = $vals['flagCode'];
			}
			
			
			if (isset($vals['time'])){
				
				$this->time = $vals['time'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'FlagVO';
	}
	
	public function read($input){
		
		$input->readStructBegin();
		while(true){
			
			$schemeField = $input->readFieldBegin();
			if ($schemeField == null) break;
			$needSkip = true;
			
			
			if ("flagCode" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->flagCode);
				
			}
			
			
			
			
			if ("time" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->time);
				
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
		
		if($this->flagCode !== null) {
			
			$xfer += $output->writeFieldBegin('flagCode');
			$xfer += $output->writeString($this->flagCode);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->time !== null) {
			
			$xfer += $output->writeFieldBegin('time');
			$xfer += $output->writeString($this->time);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}

?>