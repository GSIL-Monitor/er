<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\common\pojo\order\vo;

class OrderStatusHistoryVO {
	
	static $_TSPEC;
	public $preOrderStatus = null;
	public $preOrderStatusUpdateTime = null;
	public $orderScenario = null;
	public $orderStatus = null;
	public $orderStatusUpdateTime = null;
	public $remark = null;
	public $seq = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'preOrderStatus'
			),
			2 => array(
			'var' => 'preOrderStatusUpdateTime'
			),
			3 => array(
			'var' => 'orderScenario'
			),
			4 => array(
			'var' => 'orderStatus'
			),
			5 => array(
			'var' => 'orderStatusUpdateTime'
			),
			6 => array(
			'var' => 'remark'
			),
			7 => array(
			'var' => 'seq'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['preOrderStatus'])){
				
				$this->preOrderStatus = $vals['preOrderStatus'];
			}
			
			
			if (isset($vals['preOrderStatusUpdateTime'])){
				
				$this->preOrderStatusUpdateTime = $vals['preOrderStatusUpdateTime'];
			}
			
			
			if (isset($vals['orderScenario'])){
				
				$this->orderScenario = $vals['orderScenario'];
			}
			
			
			if (isset($vals['orderStatus'])){
				
				$this->orderStatus = $vals['orderStatus'];
			}
			
			
			if (isset($vals['orderStatusUpdateTime'])){
				
				$this->orderStatusUpdateTime = $vals['orderStatusUpdateTime'];
			}
			
			
			if (isset($vals['remark'])){
				
				$this->remark = $vals['remark'];
			}
			
			
			if (isset($vals['seq'])){
				
				$this->seq = $vals['seq'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'OrderStatusHistoryVO';
	}
	
	public function read($input){
		
		$input->readStructBegin();
		while(true){
			
			$schemeField = $input->readFieldBegin();
			if ($schemeField == null) break;
			$needSkip = true;
			
			
			if ("preOrderStatus" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->preOrderStatus); 
				
			}
			
			
			
			
			if ("preOrderStatusUpdateTime" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->preOrderStatusUpdateTime); 
				
			}
			
			
			
			
			if ("orderScenario" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->orderScenario); 
				
			}
			
			
			
			
			if ("orderStatus" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->orderStatus); 
				
			}
			
			
			
			
			if ("orderStatusUpdateTime" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->orderStatusUpdateTime); 
				
			}
			
			
			
			
			if ("remark" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->remark);
				
			}
			
			
			
			
			if ("seq" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->seq); 
				
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
		
		if($this->preOrderStatus !== null) {
			
			$xfer += $output->writeFieldBegin('preOrderStatus');
			$xfer += $output->writeI32($this->preOrderStatus);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->preOrderStatusUpdateTime !== null) {
			
			$xfer += $output->writeFieldBegin('preOrderStatusUpdateTime');
			$xfer += $output->writeI64($this->preOrderStatusUpdateTime);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderScenario !== null) {
			
			$xfer += $output->writeFieldBegin('orderScenario');
			$xfer += $output->writeI32($this->orderScenario);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderStatus !== null) {
			
			$xfer += $output->writeFieldBegin('orderStatus');
			$xfer += $output->writeI32($this->orderStatus);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderStatusUpdateTime !== null) {
			
			$xfer += $output->writeFieldBegin('orderStatusUpdateTime');
			$xfer += $output->writeI64($this->orderStatusUpdateTime);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->remark !== null) {
			
			$xfer += $output->writeFieldBegin('remark');
			$xfer += $output->writeString($this->remark);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->seq !== null) {
			
			$xfer += $output->writeFieldBegin('seq');
			$xfer += $output->writeI32($this->seq);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}

?>