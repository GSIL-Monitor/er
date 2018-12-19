<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\biz\request;

class SearchOrderElectronicInvoicesReq {
	
	static $_TSPEC;
	public $userIdList = null;
	public $orderIdList = null;
	public $orderSnList = null;
	public $idList = null;
	public $statusList = null;
	public $fpFm = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'userIdList'
			),
			2 => array(
			'var' => 'orderIdList'
			),
			3 => array(
			'var' => 'orderSnList'
			),
			4 => array(
			'var' => 'idList'
			),
			5 => array(
			'var' => 'statusList'
			),
			6 => array(
			'var' => 'fpFm'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['userIdList'])){
				
				$this->userIdList = $vals['userIdList'];
			}
			
			
			if (isset($vals['orderIdList'])){
				
				$this->orderIdList = $vals['orderIdList'];
			}
			
			
			if (isset($vals['orderSnList'])){
				
				$this->orderSnList = $vals['orderSnList'];
			}
			
			
			if (isset($vals['idList'])){
				
				$this->idList = $vals['idList'];
			}
			
			
			if (isset($vals['statusList'])){
				
				$this->statusList = $vals['statusList'];
			}
			
			
			if (isset($vals['fpFm'])){
				
				$this->fpFm = $vals['fpFm'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'SearchOrderElectronicInvoicesReq';
	}
	
	public function read($input){
		
		$input->readStructBegin();
		while(true){
			
			$schemeField = $input->readFieldBegin();
			if ($schemeField == null) break;
			$needSkip = true;
			
			
			if ("userIdList" == $schemeField){
				
				$needSkip = false;
				
				$this->userIdList = array();
				$_size0 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem0 = null;
						$input->readI64($elem0); 
						
						$this->userIdList[$_size0++] = $elem0;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readListEnd();
				
			}
			
			
			
			
			if ("orderIdList" == $schemeField){
				
				$needSkip = false;
				
				$this->orderIdList = array();
				$_size1 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem1 = null;
						$input->readI64($elem1); 
						
						$this->orderIdList[$_size1++] = $elem1;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readListEnd();
				
			}
			
			
			
			
			if ("orderSnList" == $schemeField){
				
				$needSkip = false;
				
				$this->orderSnList = array();
				$_size2 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem2 = null;
						$input->readString($elem2);
						
						$this->orderSnList[$_size2++] = $elem2;
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
				$_size3 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem3 = null;
						$input->readI64($elem3); 
						
						$this->idList[$_size3++] = $elem3;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readListEnd();
				
			}
			
			
			
			
			if ("statusList" == $schemeField){
				
				$needSkip = false;
				
				$this->statusList = array();
				$_size4 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem4 = null;
						$input->readI32($elem4); 
						
						$this->statusList[$_size4++] = $elem4;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readListEnd();
				
			}
			
			
			
			
			if ("fpFm" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->fpFm);
				
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
		
		if($this->userIdList !== null) {
			
			$xfer += $output->writeFieldBegin('userIdList');
			
			if (!is_array($this->userIdList)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeListBegin();
			foreach ($this->userIdList as $iter0){
				
				$xfer += $output->writeI64($iter0);
				
			}
			
			$output->writeListEnd();
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderIdList !== null) {
			
			$xfer += $output->writeFieldBegin('orderIdList');
			
			if (!is_array($this->orderIdList)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeListBegin();
			foreach ($this->orderIdList as $iter0){
				
				$xfer += $output->writeI64($iter0);
				
			}
			
			$output->writeListEnd();
			
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
		
		
		if($this->statusList !== null) {
			
			$xfer += $output->writeFieldBegin('statusList');
			
			if (!is_array($this->statusList)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeListBegin();
			foreach ($this->statusList as $iter0){
				
				$xfer += $output->writeI32($iter0);
				
			}
			
			$output->writeListEnd();
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->fpFm !== null) {
			
			$xfer += $output->writeFieldBegin('fpFm');
			$xfer += $output->writeString($this->fpFm);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}

?>