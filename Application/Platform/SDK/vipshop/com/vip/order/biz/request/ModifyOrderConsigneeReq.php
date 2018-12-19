<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\biz\request;

class ModifyOrderConsigneeReq {
	
	static $_TSPEC;
	public $service = null;
	public $order = null;
	public $orderInvoice = null;
	public $orderAddress = null;
	public $is4Level = null;
	public $addressId = null;
	public $supplierCancel = null;
	public $orderCategory = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'service'
			),
			2 => array(
			'var' => 'order'
			),
			3 => array(
			'var' => 'orderInvoice'
			),
			4 => array(
			'var' => 'orderAddress'
			),
			5 => array(
			'var' => 'is4Level'
			),
			6 => array(
			'var' => 'addressId'
			),
			7 => array(
			'var' => 'supplierCancel'
			),
			8 => array(
			'var' => 'orderCategory'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['service'])){
				
				$this->service = $vals['service'];
			}
			
			
			if (isset($vals['order'])){
				
				$this->order = $vals['order'];
			}
			
			
			if (isset($vals['orderInvoice'])){
				
				$this->orderInvoice = $vals['orderInvoice'];
			}
			
			
			if (isset($vals['orderAddress'])){
				
				$this->orderAddress = $vals['orderAddress'];
			}
			
			
			if (isset($vals['is4Level'])){
				
				$this->is4Level = $vals['is4Level'];
			}
			
			
			if (isset($vals['addressId'])){
				
				$this->addressId = $vals['addressId'];
			}
			
			
			if (isset($vals['supplierCancel'])){
				
				$this->supplierCancel = $vals['supplierCancel'];
			}
			
			
			if (isset($vals['orderCategory'])){
				
				$this->orderCategory = $vals['orderCategory'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'ModifyOrderConsigneeReq';
	}
	
	public function read($input){
		
		$input->readStructBegin();
		while(true){
			
			$schemeField = $input->readFieldBegin();
			if ($schemeField == null) break;
			$needSkip = true;
			
			
			if ("service" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->service);
				
			}
			
			
			
			
			if ("order" == $schemeField){
				
				$needSkip = false;
				
				$this->order = new \com\vip\order\common\pojo\order\vo\OrderVO();
				$this->order->read($input);
				
			}
			
			
			
			
			if ("orderInvoice" == $schemeField){
				
				$needSkip = false;
				
				$this->orderInvoice = new \com\vip\order\common\pojo\order\vo\OrderInvoiceVO();
				$this->orderInvoice->read($input);
				
			}
			
			
			
			
			if ("orderAddress" == $schemeField){
				
				$needSkip = false;
				
				$this->orderAddress = new \com\vip\order\common\pojo\order\vo\OrderReceiveAddrVO();
				$this->orderAddress->read($input);
				
			}
			
			
			
			
			if ("is4Level" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->is4Level); 
				
			}
			
			
			
			
			if ("addressId" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->addressId); 
				
			}
			
			
			
			
			if ("supplierCancel" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->supplierCancel); 
				
			}
			
			
			
			
			if ("orderCategory" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->orderCategory); 
				
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
		
		if($this->service !== null) {
			
			$xfer += $output->writeFieldBegin('service');
			$xfer += $output->writeString($this->service);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->order !== null) {
			
			$xfer += $output->writeFieldBegin('order');
			
			if (!is_object($this->order)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->order->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderInvoice !== null) {
			
			$xfer += $output->writeFieldBegin('orderInvoice');
			
			if (!is_object($this->orderInvoice)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->orderInvoice->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderAddress !== null) {
			
			$xfer += $output->writeFieldBegin('orderAddress');
			
			if (!is_object($this->orderAddress)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->orderAddress->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->is4Level !== null) {
			
			$xfer += $output->writeFieldBegin('is4Level');
			$xfer += $output->writeI32($this->is4Level);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->addressId !== null) {
			
			$xfer += $output->writeFieldBegin('addressId');
			$xfer += $output->writeI64($this->addressId);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->supplierCancel !== null) {
			
			$xfer += $output->writeFieldBegin('supplierCancel');
			$xfer += $output->writeI32($this->supplierCancel);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderCategory !== null) {
			
			$xfer += $output->writeFieldBegin('orderCategory');
			$xfer += $output->writeI32($this->orderCategory);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}

?>