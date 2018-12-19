<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\common\pojo\order\vo;

class GoodsFavInfoVO {
	
	static $_TSPEC;
	public $unitDiscountMoney = null;
	public $merItemNo = null;
	public $merchandiseNo = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'unitDiscountMoney'
			),
			2 => array(
			'var' => 'merItemNo'
			),
			3 => array(
			'var' => 'merchandiseNo'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['unitDiscountMoney'])){
				
				$this->unitDiscountMoney = $vals['unitDiscountMoney'];
			}
			
			
			if (isset($vals['merItemNo'])){
				
				$this->merItemNo = $vals['merItemNo'];
			}
			
			
			if (isset($vals['merchandiseNo'])){
				
				$this->merchandiseNo = $vals['merchandiseNo'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'GoodsFavInfoVO';
	}
	
	public function read($input){
		
		$input->readStructBegin();
		while(true){
			
			$schemeField = $input->readFieldBegin();
			if ($schemeField == null) break;
			$needSkip = true;
			
			
			if ("unitDiscountMoney" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->unitDiscountMoney);
				
			}
			
			
			
			
			if ("merItemNo" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->merItemNo); 
				
			}
			
			
			
			
			if ("merchandiseNo" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->merchandiseNo); 
				
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
		
		if($this->unitDiscountMoney !== null) {
			
			$xfer += $output->writeFieldBegin('unitDiscountMoney');
			$xfer += $output->writeString($this->unitDiscountMoney);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->merItemNo !== null) {
			
			$xfer += $output->writeFieldBegin('merItemNo');
			$xfer += $output->writeI64($this->merItemNo);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->merchandiseNo !== null) {
			
			$xfer += $output->writeFieldBegin('merchandiseNo');
			$xfer += $output->writeI64($this->merchandiseNo);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}

?>