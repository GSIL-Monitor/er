<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\biz\request;

class RollbackOrderReq {
	
	static $_TSPEC;
	public $userId = null;
	public $orderSnList = null;
	public $failType = null;
	public $failCode = null;
	public $failMsg = null;
	public $createIp = null;
	public $createTime = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'userId'
			),
			2 => array(
			'var' => 'orderSnList'
			),
			3 => array(
			'var' => 'failType'
			),
			4 => array(
			'var' => 'failCode'
			),
			5 => array(
			'var' => 'failMsg'
			),
			6 => array(
			'var' => 'createIp'
			),
			7 => array(
			'var' => 'createTime'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['userId'])){
				
				$this->userId = $vals['userId'];
			}
			
			
			if (isset($vals['orderSnList'])){
				
				$this->orderSnList = $vals['orderSnList'];
			}
			
			
			if (isset($vals['failType'])){
				
				$this->failType = $vals['failType'];
			}
			
			
			if (isset($vals['failCode'])){
				
				$this->failCode = $vals['failCode'];
			}
			
			
			if (isset($vals['failMsg'])){
				
				$this->failMsg = $vals['failMsg'];
			}
			
			
			if (isset($vals['createIp'])){
				
				$this->createIp = $vals['createIp'];
			}
			
			
			if (isset($vals['createTime'])){
				
				$this->createTime = $vals['createTime'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'RollbackOrderReq';
	}
	
	public function read($input){
		
		$input->readStructBegin();
		while(true){
			
			$schemeField = $input->readFieldBegin();
			if ($schemeField == null) break;
			$needSkip = true;
			
			
			if ("userId" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->userId); 
				
			}
			
			
			
			
			if ("orderSnList" == $schemeField){
				
				$needSkip = false;
				
				$this->orderSnList = array();
				$_size0 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem0 = null;
						
						$elem0 = new \com\vip\order\common\pojo\order\vo\OrderIdSnVO();
						$elem0->read($input);
						
						$this->orderSnList[$_size0++] = $elem0;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readListEnd();
				
			}
			
			
			
			
			if ("failType" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->failType); 
				
			}
			
			
			
			
			if ("failCode" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->failCode); 
				
			}
			
			
			
			
			if ("failMsg" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->failMsg);
				
			}
			
			
			
			
			if ("createIp" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->createIp);
				
			}
			
			
			
			
			if ("createTime" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->createTime);
				
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
		
		if($this->userId !== null) {
			
			$xfer += $output->writeFieldBegin('userId');
			$xfer += $output->writeI64($this->userId);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderSnList !== null) {
			
			$xfer += $output->writeFieldBegin('orderSnList');
			
			if (!is_array($this->orderSnList)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeListBegin();
			foreach ($this->orderSnList as $iter0){
				
				
				if (!is_object($iter0)) {
					
					throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
				}
				
				$xfer += $iter0->write($output);
				
			}
			
			$output->writeListEnd();
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->failType !== null) {
			
			$xfer += $output->writeFieldBegin('failType');
			$xfer += $output->writeI32($this->failType);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->failCode !== null) {
			
			$xfer += $output->writeFieldBegin('failCode');
			$xfer += $output->writeI32($this->failCode);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->failMsg !== null) {
			
			$xfer += $output->writeFieldBegin('failMsg');
			$xfer += $output->writeString($this->failMsg);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->createIp !== null) {
			
			$xfer += $output->writeFieldBegin('createIp');
			$xfer += $output->writeString($this->createIp);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->createTime !== null) {
			
			$xfer += $output->writeFieldBegin('createTime');
			$xfer += $output->writeString($this->createTime);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}

?>