<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\common\pojo\order\vo;

class OrderTransportDetailsAndPackageVO {
	
	static $_TSPEC;
	public $orderTransportPackage = null;
	public $orderTransportDetailList = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'orderTransportPackage'
			),
			2 => array(
			'var' => 'orderTransportDetailList'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['orderTransportPackage'])){
				
				$this->orderTransportPackage = $vals['orderTransportPackage'];
			}
			
			
			if (isset($vals['orderTransportDetailList'])){
				
				$this->orderTransportDetailList = $vals['orderTransportDetailList'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'OrderTransportDetailsAndPackageVO';
	}
	
	public function read($input){
		
		$input->readStructBegin();
		while(true){
			
			$schemeField = $input->readFieldBegin();
			if ($schemeField == null) break;
			$needSkip = true;
			
			
			if ("orderTransportPackage" == $schemeField){
				
				$needSkip = false;
				
				$this->orderTransportPackage = new \com\vip\order\common\pojo\order\vo\OrderTransportPackageVO();
				$this->orderTransportPackage->read($input);
				
			}
			
			
			
			
			if ("orderTransportDetailList" == $schemeField){
				
				$needSkip = false;
				
				$this->orderTransportDetailList = array();
				$_size0 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem0 = null;
						
						$elem0 = new \com\vip\order\common\pojo\order\vo\OrderTransportDetailVO();
						$elem0->read($input);
						
						$this->orderTransportDetailList[$_size0++] = $elem0;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readListEnd();
				
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
		
		if($this->orderTransportPackage !== null) {
			
			$xfer += $output->writeFieldBegin('orderTransportPackage');
			
			if (!is_object($this->orderTransportPackage)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->orderTransportPackage->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderTransportDetailList !== null) {
			
			$xfer += $output->writeFieldBegin('orderTransportDetailList');
			
			if (!is_array($this->orderTransportDetailList)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeListBegin();
			foreach ($this->orderTransportDetailList as $iter0){
				
				
				if (!is_object($iter0)) {
					
					throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
				}
				
				$xfer += $iter0->write($output);
				
			}
			
			$output->writeListEnd();
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}

?>