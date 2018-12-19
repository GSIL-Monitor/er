<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\biz\vo;

class OrderActiveVO {
	
	static $_TSPEC;
	public $activeField = null;
	public $activeName = null;
	public $activeNo = null;
	public $activeType = null;
	public $detail = null;
	public $isDelete = null;
	public $addTime = null;
	public $updateTime = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'activeField'
			),
			2 => array(
			'var' => 'activeName'
			),
			3 => array(
			'var' => 'activeNo'
			),
			4 => array(
			'var' => 'activeType'
			),
			5 => array(
			'var' => 'detail'
			),
			6 => array(
			'var' => 'isDelete'
			),
			7 => array(
			'var' => 'addTime'
			),
			8 => array(
			'var' => 'updateTime'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['activeField'])){
				
				$this->activeField = $vals['activeField'];
			}
			
			
			if (isset($vals['activeName'])){
				
				$this->activeName = $vals['activeName'];
			}
			
			
			if (isset($vals['activeNo'])){
				
				$this->activeNo = $vals['activeNo'];
			}
			
			
			if (isset($vals['activeType'])){
				
				$this->activeType = $vals['activeType'];
			}
			
			
			if (isset($vals['detail'])){
				
				$this->detail = $vals['detail'];
			}
			
			
			if (isset($vals['isDelete'])){
				
				$this->isDelete = $vals['isDelete'];
			}
			
			
			if (isset($vals['addTime'])){
				
				$this->addTime = $vals['addTime'];
			}
			
			
			if (isset($vals['updateTime'])){
				
				$this->updateTime = $vals['updateTime'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'OrderActiveVO';
	}
	
	public function read($input){
		
		$input->readStructBegin();
		while(true){
			
			$schemeField = $input->readFieldBegin();
			if ($schemeField == null) break;
			$needSkip = true;
			
			
			if ("activeField" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->activeField); 
				
			}
			
			
			
			
			if ("activeName" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->activeName);
				
			}
			
			
			
			
			if ("activeNo" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->activeNo);
				
			}
			
			
			
			
			if ("activeType" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->activeType); 
				
			}
			
			
			
			
			if ("detail" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->detail);
				
			}
			
			
			
			
			if ("isDelete" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->isDelete); 
				
			}
			
			
			
			
			if ("addTime" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->addTime); 
				
			}
			
			
			
			
			if ("updateTime" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->updateTime);
				
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
		
		if($this->activeField !== null) {
			
			$xfer += $output->writeFieldBegin('activeField');
			$xfer += $output->writeI32($this->activeField);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->activeName !== null) {
			
			$xfer += $output->writeFieldBegin('activeName');
			$xfer += $output->writeString($this->activeName);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->activeNo !== null) {
			
			$xfer += $output->writeFieldBegin('activeNo');
			$xfer += $output->writeString($this->activeNo);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->activeType !== null) {
			
			$xfer += $output->writeFieldBegin('activeType');
			$xfer += $output->writeI32($this->activeType);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->detail !== null) {
			
			$xfer += $output->writeFieldBegin('detail');
			$xfer += $output->writeString($this->detail);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->isDelete !== null) {
			
			$xfer += $output->writeFieldBegin('isDelete');
			$xfer += $output->writeI32($this->isDelete);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->addTime !== null) {
			
			$xfer += $output->writeFieldBegin('addTime');
			$xfer += $output->writeI64($this->addTime);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->updateTime !== null) {
			
			$xfer += $output->writeFieldBegin('updateTime');
			$xfer += $output->writeString($this->updateTime);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}

?>