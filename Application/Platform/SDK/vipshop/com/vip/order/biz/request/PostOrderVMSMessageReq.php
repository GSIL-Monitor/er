<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\biz\request;

class PostOrderVMSMessageReq {
	
	static $_TSPEC;
	public $msgScenario = null;
	public $msgType = null;
	public $msgContent = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'msgScenario'
			),
			2 => array(
			'var' => 'msgType'
			),
			3 => array(
			'var' => 'msgContent'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['msgScenario'])){
				
				$this->msgScenario = $vals['msgScenario'];
			}
			
			
			if (isset($vals['msgType'])){
				
				$this->msgType = $vals['msgType'];
			}
			
			
			if (isset($vals['msgContent'])){
				
				$this->msgContent = $vals['msgContent'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'PostOrderVMSMessageReq';
	}
	
	public function read($input){
		
		$input->readStructBegin();
		while(true){
			
			$schemeField = $input->readFieldBegin();
			if ($schemeField == null) break;
			$needSkip = true;
			
			
			if ("msgScenario" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->msgScenario);
				
			}
			
			
			
			
			if ("msgType" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->msgType); 
				
			}
			
			
			
			
			if ("msgContent" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->msgContent);
				
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
		
		if($this->msgScenario !== null) {
			
			$xfer += $output->writeFieldBegin('msgScenario');
			$xfer += $output->writeString($this->msgScenario);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->msgType !== null) {
			
			$xfer += $output->writeFieldBegin('msgType');
			$xfer += $output->writeI32($this->msgType);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->msgContent !== null) {
			
			$xfer += $output->writeFieldBegin('msgContent');
			$xfer += $output->writeString($this->msgContent);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}

?>