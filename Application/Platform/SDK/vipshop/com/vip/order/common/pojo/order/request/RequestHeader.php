<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\common\pojo\order\request;

class RequestHeader {
	
	static $_TSPEC;
	public $operation = null;
	public $sourceSys = null;
	public $transId = null;
	public $transTimestamp = null;
	public $operator = null;
	public $operatorName = null;
	public $clientIp = null;
	public $appVersion = null;
	public $operatorRole = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'operation'
			),
			2 => array(
			'var' => 'sourceSys'
			),
			3 => array(
			'var' => 'transId'
			),
			4 => array(
			'var' => 'transTimestamp'
			),
			5 => array(
			'var' => 'operator'
			),
			6 => array(
			'var' => 'operatorName'
			),
			7 => array(
			'var' => 'clientIp'
			),
			8 => array(
			'var' => 'appVersion'
			),
			9 => array(
			'var' => 'operatorRole'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['operation'])){
				
				$this->operation = $vals['operation'];
			}
			
			
			if (isset($vals['sourceSys'])){
				
				$this->sourceSys = $vals['sourceSys'];
			}
			
			
			if (isset($vals['transId'])){
				
				$this->transId = $vals['transId'];
			}
			
			
			if (isset($vals['transTimestamp'])){
				
				$this->transTimestamp = $vals['transTimestamp'];
			}
			
			
			if (isset($vals['operator'])){
				
				$this->operator = $vals['operator'];
			}
			
			
			if (isset($vals['operatorName'])){
				
				$this->operatorName = $vals['operatorName'];
			}
			
			
			if (isset($vals['clientIp'])){
				
				$this->clientIp = $vals['clientIp'];
			}
			
			
			if (isset($vals['appVersion'])){
				
				$this->appVersion = $vals['appVersion'];
			}
			
			
			if (isset($vals['operatorRole'])){
				
				$this->operatorRole = $vals['operatorRole'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'RequestHeader';
	}
	
	public function read($input){
		
		$input->readStructBegin();
		while(true){
			
			$schemeField = $input->readFieldBegin();
			if ($schemeField == null) break;
			$needSkip = true;
			
			
			if ("operation" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->operation);
				
			}
			
			
			
			
			if ("sourceSys" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->sourceSys);
				
			}
			
			
			
			
			if ("transId" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->transId);
				
			}
			
			
			
			
			if ("transTimestamp" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->transTimestamp); 
				
			}
			
			
			
			
			if ("operator" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->operator);
				
			}
			
			
			
			
			if ("operatorName" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->operatorName);
				
			}
			
			
			
			
			if ("clientIp" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->clientIp);
				
			}
			
			
			
			
			if ("appVersion" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->appVersion);
				
			}
			
			
			
			
			if ("operatorRole" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->operatorRole); 
				
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
		
		if($this->operation !== null) {
			
			$xfer += $output->writeFieldBegin('operation');
			$xfer += $output->writeString($this->operation);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->sourceSys !== null) {
			
			$xfer += $output->writeFieldBegin('sourceSys');
			$xfer += $output->writeString($this->sourceSys);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->transId !== null) {
			
			$xfer += $output->writeFieldBegin('transId');
			$xfer += $output->writeString($this->transId);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->transTimestamp !== null) {
			
			$xfer += $output->writeFieldBegin('transTimestamp');
			$xfer += $output->writeI64($this->transTimestamp);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->operator !== null) {
			
			$xfer += $output->writeFieldBegin('operator');
			$xfer += $output->writeString($this->operator);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->operatorName !== null) {
			
			$xfer += $output->writeFieldBegin('operatorName');
			$xfer += $output->writeString($this->operatorName);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->clientIp !== null) {
			
			$xfer += $output->writeFieldBegin('clientIp');
			$xfer += $output->writeString($this->clientIp);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->appVersion !== null) {
			
			$xfer += $output->writeFieldBegin('appVersion');
			$xfer += $output->writeString($this->appVersion);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->operatorRole !== null) {
			
			$xfer += $output->writeFieldBegin('operatorRole');
			$xfer += $output->writeI32($this->operatorRole);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}

?>