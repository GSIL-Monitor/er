<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\vis\order\manage\service\pickmanage;

class OrderSku {
	
	static $_TSPEC;
	public $orderSn = null;
	public $goodSn = null;
	public $pickNum = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'orderSn'
			),
			2 => array(
			'var' => 'goodSn'
			),
			3 => array(
			'var' => 'pickNum'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['orderSn'])){
				
				$this->orderSn = $vals['orderSn'];
			}
			
			
			if (isset($vals['goodSn'])){
				
				$this->goodSn = $vals['goodSn'];
			}
			
			
			if (isset($vals['pickNum'])){
				
				$this->pickNum = $vals['pickNum'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'OrderSku';
	}
	
	public function read($input){
		
		$input->readStructBegin();
		while(true){
			
			$schemeField = $input->readFieldBegin();
			if ($schemeField == null) break;
			$needSkip = true;
			
			
			if ("orderSn" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->orderSn);
				
			}
			
			
			
			
			if ("goodSn" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->goodSn);
				
			}
			
			
			
			
			if ("pickNum" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->pickNum); 
				
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
		
		if($this->orderSn !== null) {
			
			$xfer += $output->writeFieldBegin('orderSn');
			$xfer += $output->writeString($this->orderSn);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->goodSn !== null) {
			
			$xfer += $output->writeFieldBegin('goodSn');
			$xfer += $output->writeString($this->goodSn);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->pickNum !== null) {
			
			$xfer += $output->writeFieldBegin('pickNum');
			$xfer += $output->writeI32($this->pickNum);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}

?>