<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\common\pojo\order\vo;

class OrderActiveDetailVO {
	
	static $_TSPEC;
	public $activeType = null;
	public $activeField = null;
	public $activeNo = null;
	public $goodsFavInfoList = null;
	public $favCouponInfoList = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'activeType'
			),
			2 => array(
			'var' => 'activeField'
			),
			3 => array(
			'var' => 'activeNo'
			),
			4 => array(
			'var' => 'goodsFavInfoList'
			),
			5 => array(
			'var' => 'favCouponInfoList'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['activeType'])){
				
				$this->activeType = $vals['activeType'];
			}
			
			
			if (isset($vals['activeField'])){
				
				$this->activeField = $vals['activeField'];
			}
			
			
			if (isset($vals['activeNo'])){
				
				$this->activeNo = $vals['activeNo'];
			}
			
			
			if (isset($vals['goodsFavInfoList'])){
				
				$this->goodsFavInfoList = $vals['goodsFavInfoList'];
			}
			
			
			if (isset($vals['favCouponInfoList'])){
				
				$this->favCouponInfoList = $vals['favCouponInfoList'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'OrderActiveDetailVO';
	}
	
	public function read($input){
		
		$input->readStructBegin();
		while(true){
			
			$schemeField = $input->readFieldBegin();
			if ($schemeField == null) break;
			$needSkip = true;
			
			
			if ("activeType" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->activeType); 
				
			}
			
			
			
			
			if ("activeField" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->activeField); 
				
			}
			
			
			
			
			if ("activeNo" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->activeNo);
				
			}
			
			
			
			
			if ("goodsFavInfoList" == $schemeField){
				
				$needSkip = false;
				
				$this->goodsFavInfoList = array();
				$_size1 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem1 = null;
						
						$elem1 = new \com\vip\order\common\pojo\order\vo\GoodsFavInfoVO();
						$elem1->read($input);
						
						$this->goodsFavInfoList[$_size1++] = $elem1;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readListEnd();
				
			}
			
			
			
			
			if ("favCouponInfoList" == $schemeField){
				
				$needSkip = false;
				
				$this->favCouponInfoList = array();
				$_size2 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem2 = null;
						
						$elem2 = new \com\vip\order\common\pojo\order\vo\FavCouponInfoVO();
						$elem2->read($input);
						
						$this->favCouponInfoList[$_size2++] = $elem2;
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
		
		if($this->activeType !== null) {
			
			$xfer += $output->writeFieldBegin('activeType');
			$xfer += $output->writeI32($this->activeType);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->activeField !== null) {
			
			$xfer += $output->writeFieldBegin('activeField');
			$xfer += $output->writeI32($this->activeField);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->activeNo !== null) {
			
			$xfer += $output->writeFieldBegin('activeNo');
			$xfer += $output->writeString($this->activeNo);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->goodsFavInfoList !== null) {
			
			$xfer += $output->writeFieldBegin('goodsFavInfoList');
			
			if (!is_array($this->goodsFavInfoList)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeListBegin();
			foreach ($this->goodsFavInfoList as $iter0){
				
				
				if (!is_object($iter0)) {
					
					throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
				}
				
				$xfer += $iter0->write($output);
				
			}
			
			$output->writeListEnd();
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->favCouponInfoList !== null) {
			
			$xfer += $output->writeFieldBegin('favCouponInfoList');
			
			if (!is_array($this->favCouponInfoList)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeListBegin();
			foreach ($this->favCouponInfoList as $iter0){
				
				
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