<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\biz\vo;

class OrderGoodsDescribeVO {
	
	static $_TSPEC;
	public $seqNum = null;
	public $descType = null;
	public $descRemark = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'seqNum'
			),
			2 => array(
			'var' => 'descType'
			),
			3 => array(
			'var' => 'descRemark'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['seqNum'])){
				
				$this->seqNum = $vals['seqNum'];
			}
			
			
			if (isset($vals['descType'])){
				
				$this->descType = $vals['descType'];
			}
			
			
			if (isset($vals['descRemark'])){
				
				$this->descRemark = $vals['descRemark'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'OrderGoodsDescribeVO';
	}
	
	public function read($input){
		
		$input->readStructBegin();
		while(true){
			
			$schemeField = $input->readFieldBegin();
			if ($schemeField == null) break;
			$needSkip = true;
			
			
			if ("seqNum" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->seqNum); 
				
			}
			
			
			
			
			if ("descType" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->descType); 
				
			}
			
			
			
			
			if ("descRemark" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->descRemark);
				
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
		
		if($this->seqNum !== null) {
			
			$xfer += $output->writeFieldBegin('seqNum');
			$xfer += $output->writeI32($this->seqNum);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->descType !== null) {
			
			$xfer += $output->writeFieldBegin('descType');
			$xfer += $output->writeI32($this->descType);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->descRemark !== null) {
			
			$xfer += $output->writeFieldBegin('descRemark');
			$xfer += $output->writeString($this->descRemark);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}

?>