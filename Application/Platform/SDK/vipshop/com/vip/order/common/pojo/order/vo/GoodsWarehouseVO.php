<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\common\pojo\order\vo;

class GoodsWarehouseVO {
	
	static $_TSPEC;
	public $merchandiseNo = null;
	public $warehouse = null;
	public $salesNo = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'merchandiseNo'
			),
			2 => array(
			'var' => 'warehouse'
			),
			3 => array(
			'var' => 'salesNo'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['merchandiseNo'])){
				
				$this->merchandiseNo = $vals['merchandiseNo'];
			}
			
			
			if (isset($vals['warehouse'])){
				
				$this->warehouse = $vals['warehouse'];
			}
			
			
			if (isset($vals['salesNo'])){
				
				$this->salesNo = $vals['salesNo'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'GoodsWarehouseVO';
	}
	
	public function read($input){
		
		$input->readStructBegin();
		while(true){
			
			$schemeField = $input->readFieldBegin();
			if ($schemeField == null) break;
			$needSkip = true;
			
			
			if ("merchandiseNo" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->merchandiseNo);
				
			}
			
			
			
			
			if ("warehouse" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->warehouse);
				
			}
			
			
			
			
			if ("salesNo" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->salesNo);
				
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
		
		if($this->merchandiseNo !== null) {
			
			$xfer += $output->writeFieldBegin('merchandiseNo');
			$xfer += $output->writeString($this->merchandiseNo);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->warehouse !== null) {
			
			$xfer += $output->writeFieldBegin('warehouse');
			$xfer += $output->writeString($this->warehouse);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->salesNo !== null) {
			
			$xfer += $output->writeFieldBegin('salesNo');
			$xfer += $output->writeString($this->salesNo);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}

?>