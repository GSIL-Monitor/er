<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\biz\request;

class SearchOrderDetailReq {
	
	static $_TSPEC;
	public $userId = null;
	public $orderSnList = null;
	public $idList = null;
	public $orderCategory = null;
	public $couponId = null;
	public $specialCondition = null;
	public $returnFields = null;
	public $snType = null;
	public $isDisplay = null;
	
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
			'var' => 'idList'
			),
			4 => array(
			'var' => 'orderCategory'
			),
			5 => array(
			'var' => 'couponId'
			),
			6 => array(
			'var' => 'specialCondition'
			),
			7 => array(
			'var' => 'returnFields'
			),
			8 => array(
			'var' => 'snType'
			),
			9 => array(
			'var' => 'isDisplay'
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
			
			
			if (isset($vals['idList'])){
				
				$this->idList = $vals['idList'];
			}
			
			
			if (isset($vals['orderCategory'])){
				
				$this->orderCategory = $vals['orderCategory'];
			}
			
			
			if (isset($vals['couponId'])){
				
				$this->couponId = $vals['couponId'];
			}
			
			
			if (isset($vals['specialCondition'])){
				
				$this->specialCondition = $vals['specialCondition'];
			}
			
			
			if (isset($vals['returnFields'])){
				
				$this->returnFields = $vals['returnFields'];
			}
			
			
			if (isset($vals['snType'])){
				
				$this->snType = $vals['snType'];
			}
			
			
			if (isset($vals['isDisplay'])){
				
				$this->isDisplay = $vals['isDisplay'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'SearchOrderDetailReq';
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
						$input->readString($elem0);
						
						$this->orderSnList[$_size0++] = $elem0;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readListEnd();
				
			}
			
			
			
			
			if ("idList" == $schemeField){
				
				$needSkip = false;
				
				$this->idList = array();
				$_size1 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem1 = null;
						$input->readI64($elem1); 
						
						$this->idList[$_size1++] = $elem1;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readListEnd();
				
			}
			
			
			
			
			if ("orderCategory" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->orderCategory); 
				
			}
			
			
			
			
			if ("couponId" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->couponId);
				
			}
			
			
			
			
			if ("specialCondition" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->specialCondition);
				
			}
			
			
			
			
			if ("returnFields" == $schemeField){
				
				$needSkip = false;
				
				$this->returnFields = array();
				$input->readMapBegin();
				while(true){
					
					try{
						
						$key2 = '';
						$input->readString($key2);
						
						$val2 = '';
						$input->readString($val2);
						
						$this->returnFields[$key2] = $val2;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readMapEnd();
				
			}
			
			
			
			
			if ("snType" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->snType);
				
			}
			
			
			
			
			if ("isDisplay" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->isDisplay); 
				
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
				
				$xfer += $output->writeString($iter0);
				
			}
			
			$output->writeListEnd();
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->idList !== null) {
			
			$xfer += $output->writeFieldBegin('idList');
			
			if (!is_array($this->idList)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeListBegin();
			foreach ($this->idList as $iter0){
				
				$xfer += $output->writeI64($iter0);
				
			}
			
			$output->writeListEnd();
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderCategory !== null) {
			
			$xfer += $output->writeFieldBegin('orderCategory');
			$xfer += $output->writeI32($this->orderCategory);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->couponId !== null) {
			
			$xfer += $output->writeFieldBegin('couponId');
			$xfer += $output->writeString($this->couponId);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->specialCondition !== null) {
			
			$xfer += $output->writeFieldBegin('specialCondition');
			$xfer += $output->writeString($this->specialCondition);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->returnFields !== null) {
			
			$xfer += $output->writeFieldBegin('returnFields');
			
			if (!is_array($this->returnFields)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeMapBegin();
			foreach ($this->returnFields as $kiter0 => $viter0){
				
				$xfer += $output->writeString($kiter0);
				
				$xfer += $output->writeString($viter0);
				
			}
			
			$output->writeMapEnd();
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->snType !== null) {
			
			$xfer += $output->writeFieldBegin('snType');
			$xfer += $output->writeString($this->snType);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->isDisplay !== null) {
			
			$xfer += $output->writeFieldBegin('isDisplay');
			$xfer += $output->writeI32($this->isDisplay);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}

?>