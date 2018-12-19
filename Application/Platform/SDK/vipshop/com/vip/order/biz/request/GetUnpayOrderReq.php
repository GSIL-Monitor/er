<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\biz\request;

class GetUnpayOrderReq {
	
	static $_TSPEC;
	public $userIdList = null;
	public $vipclubList = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'userIdList'
			),
			2 => array(
			'var' => 'vipclubList'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['userIdList'])){
				
				$this->userIdList = $vals['userIdList'];
			}
			
			
			if (isset($vals['vipclubList'])){
				
				$this->vipclubList = $vals['vipclubList'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'GetUnpayOrderReq';
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
				$input->readSetBegin();
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
				
				$input->readSetEnd();
				
			}
			
			
			
			
			if ("vipclubList" == $schemeField){
				
				$needSkip = false;
				
				$this->vipclubList = array();
				$_size1 = 0;
				$input->readSetBegin();
				while(true){
					
					try{
						
						$elem1 = null;
						$input->readString($elem1);
						
						$this->vipclubList[$_size1++] = $elem1;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readSetEnd();
				
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
			
			$output->writeSetBegin();
			foreach ($this->userIdList as $iter0){
				
				$xfer += $output->writeI64($iter0);
				
			}
			
			$output->writeSetEnd();
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->vipclubList !== null) {
			
			$xfer += $output->writeFieldBegin('vipclubList');
			
			if (!is_array($this->vipclubList)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeSetBegin();
			foreach ($this->vipclubList as $iter0){
				
				$xfer += $output->writeString($iter0);
				
			}
			
			$output->writeSetEnd();
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}

?>