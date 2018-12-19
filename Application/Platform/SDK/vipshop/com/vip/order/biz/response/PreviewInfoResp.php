<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\biz\response;

class PreviewInfoResp {
	
	static $_TSPEC;
	public $virtualMoney = null;
	public $totalPacketMoney = null;
	public $total = null;
	public $surplus = null;
	public $money = null;
	public $favourableMoney = null;
	public $favourableId = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'virtualMoney'
			),
			2 => array(
			'var' => 'totalPacketMoney'
			),
			3 => array(
			'var' => 'total'
			),
			4 => array(
			'var' => 'surplus'
			),
			5 => array(
			'var' => 'money'
			),
			6 => array(
			'var' => 'favourableMoney'
			),
			7 => array(
			'var' => 'favourableId'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['virtualMoney'])){
				
				$this->virtualMoney = $vals['virtualMoney'];
			}
			
			
			if (isset($vals['totalPacketMoney'])){
				
				$this->totalPacketMoney = $vals['totalPacketMoney'];
			}
			
			
			if (isset($vals['total'])){
				
				$this->total = $vals['total'];
			}
			
			
			if (isset($vals['surplus'])){
				
				$this->surplus = $vals['surplus'];
			}
			
			
			if (isset($vals['money'])){
				
				$this->money = $vals['money'];
			}
			
			
			if (isset($vals['favourableMoney'])){
				
				$this->favourableMoney = $vals['favourableMoney'];
			}
			
			
			if (isset($vals['favourableId'])){
				
				$this->favourableId = $vals['favourableId'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'PreviewInfoResp';
	}
	
	public function read($input){
		
		$input->readStructBegin();
		while(true){
			
			$schemeField = $input->readFieldBegin();
			if ($schemeField == null) break;
			$needSkip = true;
			
			
			if ("virtualMoney" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->virtualMoney);
				
			}
			
			
			
			
			if ("totalPacketMoney" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->totalPacketMoney);
				
			}
			
			
			
			
			if ("total" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->total);
				
			}
			
			
			
			
			if ("surplus" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->surplus);
				
			}
			
			
			
			
			if ("money" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->money);
				
			}
			
			
			
			
			if ("favourableMoney" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->favourableMoney);
				
			}
			
			
			
			
			if ("favourableId" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->favourableId); 
				
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
		
		if($this->virtualMoney !== null) {
			
			$xfer += $output->writeFieldBegin('virtualMoney');
			$xfer += $output->writeString($this->virtualMoney);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->totalPacketMoney !== null) {
			
			$xfer += $output->writeFieldBegin('totalPacketMoney');
			$xfer += $output->writeString($this->totalPacketMoney);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->total !== null) {
			
			$xfer += $output->writeFieldBegin('total');
			$xfer += $output->writeString($this->total);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->surplus !== null) {
			
			$xfer += $output->writeFieldBegin('surplus');
			$xfer += $output->writeString($this->surplus);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->money !== null) {
			
			$xfer += $output->writeFieldBegin('money');
			$xfer += $output->writeString($this->money);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->favourableMoney !== null) {
			
			$xfer += $output->writeFieldBegin('favourableMoney');
			$xfer += $output->writeString($this->favourableMoney);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->favourableId !== null) {
			
			$xfer += $output->writeFieldBegin('favourableId');
			$xfer += $output->writeI64($this->favourableId);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}

?>