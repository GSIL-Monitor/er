<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\biz\response;

class PrepayOrderUnpayMsg {
	
	static $_TSPEC;
	public $parentSn = null;
	public $orderCode = null;
	public $vipclub = null;
	public $endPayTime = null;
	public $startPayTime = null;
	public $addTime = null;
	public $saleType = null;
	public $merItemNoList = null;
	public $salesNoList = null;
	public $merchandiseNoList = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'parentSn'
			),
			2 => array(
			'var' => 'orderCode'
			),
			3 => array(
			'var' => 'vipclub'
			),
			4 => array(
			'var' => 'endPayTime'
			),
			5 => array(
			'var' => 'startPayTime'
			),
			6 => array(
			'var' => 'addTime'
			),
			7 => array(
			'var' => 'saleType'
			),
			8 => array(
			'var' => 'merItemNoList'
			),
			9 => array(
			'var' => 'salesNoList'
			),
			10 => array(
			'var' => 'merchandiseNoList'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['parentSn'])){
				
				$this->parentSn = $vals['parentSn'];
			}
			
			
			if (isset($vals['orderCode'])){
				
				$this->orderCode = $vals['orderCode'];
			}
			
			
			if (isset($vals['vipclub'])){
				
				$this->vipclub = $vals['vipclub'];
			}
			
			
			if (isset($vals['endPayTime'])){
				
				$this->endPayTime = $vals['endPayTime'];
			}
			
			
			if (isset($vals['startPayTime'])){
				
				$this->startPayTime = $vals['startPayTime'];
			}
			
			
			if (isset($vals['addTime'])){
				
				$this->addTime = $vals['addTime'];
			}
			
			
			if (isset($vals['saleType'])){
				
				$this->saleType = $vals['saleType'];
			}
			
			
			if (isset($vals['merItemNoList'])){
				
				$this->merItemNoList = $vals['merItemNoList'];
			}
			
			
			if (isset($vals['salesNoList'])){
				
				$this->salesNoList = $vals['salesNoList'];
			}
			
			
			if (isset($vals['merchandiseNoList'])){
				
				$this->merchandiseNoList = $vals['merchandiseNoList'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'PrepayOrderUnpayMsg';
	}
	
	public function read($input){
		
		$input->readStructBegin();
		while(true){
			
			$schemeField = $input->readFieldBegin();
			if ($schemeField == null) break;
			$needSkip = true;
			
			
			if ("parentSn" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->parentSn);
				
			}
			
			
			
			
			if ("orderCode" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->orderCode);
				
			}
			
			
			
			
			if ("vipclub" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->vipclub);
				
			}
			
			
			
			
			if ("endPayTime" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->endPayTime); 
				
			}
			
			
			
			
			if ("startPayTime" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->startPayTime); 
				
			}
			
			
			
			
			if ("addTime" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->addTime);
				
			}
			
			
			
			
			if ("saleType" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->saleType);
				
			}
			
			
			
			
			if ("merItemNoList" == $schemeField){
				
				$needSkip = false;
				
				$this->merItemNoList = array();
				$_size0 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem0 = null;
						$input->readString($elem0);
						
						$this->merItemNoList[$_size0++] = $elem0;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readListEnd();
				
			}
			
			
			
			
			if ("salesNoList" == $schemeField){
				
				$needSkip = false;
				
				$this->salesNoList = array();
				$_size1 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem1 = null;
						$input->readString($elem1);
						
						$this->salesNoList[$_size1++] = $elem1;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readListEnd();
				
			}
			
			
			
			
			if ("merchandiseNoList" == $schemeField){
				
				$needSkip = false;
				
				$this->merchandiseNoList = array();
				$_size2 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem2 = null;
						$input->readString($elem2);
						
						$this->merchandiseNoList[$_size2++] = $elem2;
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
		
		if($this->parentSn !== null) {
			
			$xfer += $output->writeFieldBegin('parentSn');
			$xfer += $output->writeString($this->parentSn);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderCode !== null) {
			
			$xfer += $output->writeFieldBegin('orderCode');
			$xfer += $output->writeString($this->orderCode);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->vipclub !== null) {
			
			$xfer += $output->writeFieldBegin('vipclub');
			$xfer += $output->writeString($this->vipclub);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->endPayTime !== null) {
			
			$xfer += $output->writeFieldBegin('endPayTime');
			$xfer += $output->writeI64($this->endPayTime);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->startPayTime !== null) {
			
			$xfer += $output->writeFieldBegin('startPayTime');
			$xfer += $output->writeI64($this->startPayTime);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->addTime !== null) {
			
			$xfer += $output->writeFieldBegin('addTime');
			$xfer += $output->writeString($this->addTime);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->saleType !== null) {
			
			$xfer += $output->writeFieldBegin('saleType');
			$xfer += $output->writeString($this->saleType);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->merItemNoList !== null) {
			
			$xfer += $output->writeFieldBegin('merItemNoList');
			
			if (!is_array($this->merItemNoList)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeListBegin();
			foreach ($this->merItemNoList as $iter0){
				
				$xfer += $output->writeString($iter0);
				
			}
			
			$output->writeListEnd();
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->salesNoList !== null) {
			
			$xfer += $output->writeFieldBegin('salesNoList');
			
			if (!is_array($this->salesNoList)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeListBegin();
			foreach ($this->salesNoList as $iter0){
				
				$xfer += $output->writeString($iter0);
				
			}
			
			$output->writeListEnd();
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->merchandiseNoList !== null) {
			
			$xfer += $output->writeFieldBegin('merchandiseNoList');
			
			if (!is_array($this->merchandiseNoList)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeListBegin();
			foreach ($this->merchandiseNoList as $iter0){
				
				$xfer += $output->writeString($iter0);
				
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