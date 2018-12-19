<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\biz\request;

class CancelOrderPrivilegeReq {
	
	static $_TSPEC;
	public $cancelSuppliersOrder = null;
	public $cancelSpecilizePreSaleOrder = null;
	public $cancelPackagedOrder = null;
	public $cancelOverseasSaleOrder = null;
	public $cancelOutOfWarehouseOrder = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'cancelSuppliersOrder'
			),
			2 => array(
			'var' => 'cancelSpecilizePreSaleOrder'
			),
			3 => array(
			'var' => 'cancelPackagedOrder'
			),
			4 => array(
			'var' => 'cancelOverseasSaleOrder'
			),
			5 => array(
			'var' => 'cancelOutOfWarehouseOrder'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['cancelSuppliersOrder'])){
				
				$this->cancelSuppliersOrder = $vals['cancelSuppliersOrder'];
			}
			
			
			if (isset($vals['cancelSpecilizePreSaleOrder'])){
				
				$this->cancelSpecilizePreSaleOrder = $vals['cancelSpecilizePreSaleOrder'];
			}
			
			
			if (isset($vals['cancelPackagedOrder'])){
				
				$this->cancelPackagedOrder = $vals['cancelPackagedOrder'];
			}
			
			
			if (isset($vals['cancelOverseasSaleOrder'])){
				
				$this->cancelOverseasSaleOrder = $vals['cancelOverseasSaleOrder'];
			}
			
			
			if (isset($vals['cancelOutOfWarehouseOrder'])){
				
				$this->cancelOutOfWarehouseOrder = $vals['cancelOutOfWarehouseOrder'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'CancelOrderPrivilegeReq';
	}
	
	public function read($input){
		
		$input->readStructBegin();
		while(true){
			
			$schemeField = $input->readFieldBegin();
			if ($schemeField == null) break;
			$needSkip = true;
			
			
			if ("cancelSuppliersOrder" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->cancelSuppliersOrder); 
				
			}
			
			
			
			
			if ("cancelSpecilizePreSaleOrder" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->cancelSpecilizePreSaleOrder); 
				
			}
			
			
			
			
			if ("cancelPackagedOrder" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->cancelPackagedOrder); 
				
			}
			
			
			
			
			if ("cancelOverseasSaleOrder" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->cancelOverseasSaleOrder); 
				
			}
			
			
			
			
			if ("cancelOutOfWarehouseOrder" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->cancelOutOfWarehouseOrder); 
				
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
		
		if($this->cancelSuppliersOrder !== null) {
			
			$xfer += $output->writeFieldBegin('cancelSuppliersOrder');
			$xfer += $output->writeI32($this->cancelSuppliersOrder);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->cancelSpecilizePreSaleOrder !== null) {
			
			$xfer += $output->writeFieldBegin('cancelSpecilizePreSaleOrder');
			$xfer += $output->writeI32($this->cancelSpecilizePreSaleOrder);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->cancelPackagedOrder !== null) {
			
			$xfer += $output->writeFieldBegin('cancelPackagedOrder');
			$xfer += $output->writeI32($this->cancelPackagedOrder);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->cancelOverseasSaleOrder !== null) {
			
			$xfer += $output->writeFieldBegin('cancelOverseasSaleOrder');
			$xfer += $output->writeI32($this->cancelOverseasSaleOrder);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->cancelOutOfWarehouseOrder !== null) {
			
			$xfer += $output->writeFieldBegin('cancelOutOfWarehouseOrder');
			$xfer += $output->writeI32($this->cancelOutOfWarehouseOrder);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}

?>