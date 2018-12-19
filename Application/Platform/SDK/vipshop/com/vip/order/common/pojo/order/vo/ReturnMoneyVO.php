<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\common\pojo\order\vo;

class ReturnMoneyVO {
	
	static $_TSPEC;
	public $goodsMoney = null;
	public $discount = null;
	public $transportFee = null;
	public $returnFee = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'goodsMoney'
			),
			2 => array(
			'var' => 'discount'
			),
			3 => array(
			'var' => 'transportFee'
			),
			4 => array(
			'var' => 'returnFee'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['goodsMoney'])){
				
				$this->goodsMoney = $vals['goodsMoney'];
			}
			
			
			if (isset($vals['discount'])){
				
				$this->discount = $vals['discount'];
			}
			
			
			if (isset($vals['transportFee'])){
				
				$this->transportFee = $vals['transportFee'];
			}
			
			
			if (isset($vals['returnFee'])){
				
				$this->returnFee = $vals['returnFee'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'ReturnMoneyVO';
	}
	
	public function read($input){
		
		$input->readStructBegin();
		while(true){
			
			$schemeField = $input->readFieldBegin();
			if ($schemeField == null) break;
			$needSkip = true;
			
			
			if ("goodsMoney" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->goodsMoney);
				
			}
			
			
			
			
			if ("discount" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->discount);
				
			}
			
			
			
			
			if ("transportFee" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->transportFee);
				
			}
			
			
			
			
			if ("returnFee" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->returnFee);
				
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
		
		if($this->goodsMoney !== null) {
			
			$xfer += $output->writeFieldBegin('goodsMoney');
			$xfer += $output->writeString($this->goodsMoney);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->discount !== null) {
			
			$xfer += $output->writeFieldBegin('discount');
			$xfer += $output->writeString($this->discount);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->transportFee !== null) {
			
			$xfer += $output->writeFieldBegin('transportFee');
			$xfer += $output->writeString($this->transportFee);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->returnFee !== null) {
			
			$xfer += $output->writeFieldBegin('returnFee');
			$xfer += $output->writeString($this->returnFee);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}

?>