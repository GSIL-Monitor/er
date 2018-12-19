<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace vipapis\order;
interface OmniOrderServiceIf{
	
	
	public function confirmOrderInvoice( $vendor_id, $order_invoices);
	
	public function confirmStoreDelivery( $vendor_id, $order_id, $store_sn, $package_no, $estimate_delivery_time);
	
	public function getInventoryCancelledOrders(\vipapis\order\InventoryCancelledOrdersRequest $inventoryCancelledOrdersRequest);
	
	public function getInventoryDeductOrders(\vipapis\order\InventoryDeductOrdersRequest $inventoryDeductOrdersRequest);
	
	public function getInventoryOccupiedOrders(\vipapis\order\InventoryOccupiedOrdersRequest $inventoryOccupiedOrdersRequest);
	
	public function getOmniCancelledOrders( $vendor_id, $brand_id, $order_type, $st_query_time, $et_query_time, $limit, $page);
	
	public function getOmniOrders( $vendor_id, $brand_id, $business_type, $st_query_time, $et_query_time, $limit, $page);
	
	public function getOrderInvoice( $vendor_id, $orders);
	
	public function healthCheck();
	
	public function printInvoice( $vendor_id, $store_sn, $batch_no, $orders,\vipapis\order\ExtInfo $ext_info);
	
	public function pushOrderPackageWeight( $vendor_id, $store_sn, $orders);
	
	public function pushQCResult( $vendor_id, $store_sn, $orders);
	
	public function replyStoreSn( $vendor_id, $order_store_mapping);
	
	public function responseOrderStore( $vendor_id, $order_id, $store_sn);
	
	public function updateStoreSn( $vendor_id, $order_store_mapping);
	
}

class _OmniOrderServiceClient extends \Osp\Base\OspStub implements \vipapis\order\OmniOrderServiceIf{
	
	public function __construct(){
		
		parent::__construct("vipapis.order.OmniOrderService", "1.0.0");
	}
	
	
	public function confirmOrderInvoice( $vendor_id, $order_invoices){
		
		$this->send_confirmOrderInvoice( $vendor_id, $order_invoices);
		return $this->recv_confirmOrderInvoice();
	}
	
	public function send_confirmOrderInvoice( $vendor_id, $order_invoices){
		
		$this->initInvocation("confirmOrderInvoice");
		$args = new \vipapis\order\OmniOrderService_confirmOrderInvoice_args();
		
		$args->vendor_id = $vendor_id;
		
		$args->order_invoices = $order_invoices;
		
		$this->send_base($args);
	}
	
	public function recv_confirmOrderInvoice(){
		
		$result = new \vipapis\order\OmniOrderService_confirmOrderInvoice_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function confirmStoreDelivery( $vendor_id, $order_id, $store_sn, $package_no, $estimate_delivery_time){
		
		$this->send_confirmStoreDelivery( $vendor_id, $order_id, $store_sn, $package_no, $estimate_delivery_time);
		return $this->recv_confirmStoreDelivery();
	}
	
	public function send_confirmStoreDelivery( $vendor_id, $order_id, $store_sn, $package_no, $estimate_delivery_time){
		
		$this->initInvocation("confirmStoreDelivery");
		$args = new \vipapis\order\OmniOrderService_confirmStoreDelivery_args();
		
		$args->vendor_id = $vendor_id;
		
		$args->order_id = $order_id;
		
		$args->store_sn = $store_sn;
		
		$args->package_no = $package_no;
		
		$args->estimate_delivery_time = $estimate_delivery_time;
		
		$this->send_base($args);
	}
	
	public function recv_confirmStoreDelivery(){
		
		$result = new \vipapis\order\OmniOrderService_confirmStoreDelivery_result();
		$this->receive_base($result);
		
	}
	
	
	public function getInventoryCancelledOrders(\vipapis\order\InventoryCancelledOrdersRequest $inventoryCancelledOrdersRequest){
		
		$this->send_getInventoryCancelledOrders( $inventoryCancelledOrdersRequest);
		return $this->recv_getInventoryCancelledOrders();
	}
	
	public function send_getInventoryCancelledOrders(\vipapis\order\InventoryCancelledOrdersRequest $inventoryCancelledOrdersRequest){
		
		$this->initInvocation("getInventoryCancelledOrders");
		$args = new \vipapis\order\OmniOrderService_getInventoryCancelledOrders_args();
		
		$args->inventoryCancelledOrdersRequest = $inventoryCancelledOrdersRequest;
		
		$this->send_base($args);
	}
	
	public function recv_getInventoryCancelledOrders(){
		
		$result = new \vipapis\order\OmniOrderService_getInventoryCancelledOrders_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function getInventoryDeductOrders(\vipapis\order\InventoryDeductOrdersRequest $inventoryDeductOrdersRequest){
		
		$this->send_getInventoryDeductOrders( $inventoryDeductOrdersRequest);
		return $this->recv_getInventoryDeductOrders();
	}
	
	public function send_getInventoryDeductOrders(\vipapis\order\InventoryDeductOrdersRequest $inventoryDeductOrdersRequest){
		
		$this->initInvocation("getInventoryDeductOrders");
		$args = new \vipapis\order\OmniOrderService_getInventoryDeductOrders_args();
		
		$args->inventoryDeductOrdersRequest = $inventoryDeductOrdersRequest;
		
		$this->send_base($args);
	}
	
	public function recv_getInventoryDeductOrders(){
		
		$result = new \vipapis\order\OmniOrderService_getInventoryDeductOrders_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function getInventoryOccupiedOrders(\vipapis\order\InventoryOccupiedOrdersRequest $inventoryOccupiedOrdersRequest){
		
		$this->send_getInventoryOccupiedOrders( $inventoryOccupiedOrdersRequest);
		return $this->recv_getInventoryOccupiedOrders();
	}
	
	public function send_getInventoryOccupiedOrders(\vipapis\order\InventoryOccupiedOrdersRequest $inventoryOccupiedOrdersRequest){
		
		$this->initInvocation("getInventoryOccupiedOrders");
		$args = new \vipapis\order\OmniOrderService_getInventoryOccupiedOrders_args();
		
		$args->inventoryOccupiedOrdersRequest = $inventoryOccupiedOrdersRequest;
		
		$this->send_base($args);
	}
	
	public function recv_getInventoryOccupiedOrders(){
		
		$result = new \vipapis\order\OmniOrderService_getInventoryOccupiedOrders_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function getOmniCancelledOrders( $vendor_id, $brand_id, $order_type, $st_query_time, $et_query_time, $limit, $page){
		
		$this->send_getOmniCancelledOrders( $vendor_id, $brand_id, $order_type, $st_query_time, $et_query_time, $limit, $page);
		return $this->recv_getOmniCancelledOrders();
	}
	
	public function send_getOmniCancelledOrders( $vendor_id, $brand_id, $order_type, $st_query_time, $et_query_time, $limit, $page){
		
		$this->initInvocation("getOmniCancelledOrders");
		$args = new \vipapis\order\OmniOrderService_getOmniCancelledOrders_args();
		
		$args->vendor_id = $vendor_id;
		
		$args->brand_id = $brand_id;
		
		$args->order_type = $order_type;
		
		$args->st_query_time = $st_query_time;
		
		$args->et_query_time = $et_query_time;
		
		$args->limit = $limit;
		
		$args->page = $page;
		
		$this->send_base($args);
	}
	
	public function recv_getOmniCancelledOrders(){
		
		$result = new \vipapis\order\OmniOrderService_getOmniCancelledOrders_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function getOmniOrders( $vendor_id, $brand_id, $business_type, $st_query_time, $et_query_time, $limit, $page){
		
		$this->send_getOmniOrders( $vendor_id, $brand_id, $business_type, $st_query_time, $et_query_time, $limit, $page);
		return $this->recv_getOmniOrders();
	}
	
	public function send_getOmniOrders( $vendor_id, $brand_id, $business_type, $st_query_time, $et_query_time, $limit, $page){
		
		$this->initInvocation("getOmniOrders");
		$args = new \vipapis\order\OmniOrderService_getOmniOrders_args();
		
		$args->vendor_id = $vendor_id;
		
		$args->brand_id = $brand_id;
		
		$args->business_type = $business_type;
		
		$args->st_query_time = $st_query_time;
		
		$args->et_query_time = $et_query_time;
		
		$args->limit = $limit;
		
		$args->page = $page;
		
		$this->send_base($args);
	}
	
	public function recv_getOmniOrders(){
		
		$result = new \vipapis\order\OmniOrderService_getOmniOrders_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function getOrderInvoice( $vendor_id, $orders){
		
		$this->send_getOrderInvoice( $vendor_id, $orders);
		return $this->recv_getOrderInvoice();
	}
	
	public function send_getOrderInvoice( $vendor_id, $orders){
		
		$this->initInvocation("getOrderInvoice");
		$args = new \vipapis\order\OmniOrderService_getOrderInvoice_args();
		
		$args->vendor_id = $vendor_id;
		
		$args->orders = $orders;
		
		$this->send_base($args);
	}
	
	public function recv_getOrderInvoice(){
		
		$result = new \vipapis\order\OmniOrderService_getOrderInvoice_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function healthCheck(){
		
		$this->send_healthCheck();
		return $this->recv_healthCheck();
	}
	
	public function send_healthCheck(){
		
		$this->initInvocation("healthCheck");
		$args = new \vipapis\order\OmniOrderService_healthCheck_args();
		
		$this->send_base($args);
	}
	
	public function recv_healthCheck(){
		
		$result = new \vipapis\order\OmniOrderService_healthCheck_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function printInvoice( $vendor_id, $store_sn, $batch_no, $orders,\vipapis\order\ExtInfo $ext_info){
		
		$this->send_printInvoice( $vendor_id, $store_sn, $batch_no, $orders, $ext_info);
		return $this->recv_printInvoice();
	}
	
	public function send_printInvoice( $vendor_id, $store_sn, $batch_no, $orders,\vipapis\order\ExtInfo $ext_info){
		
		$this->initInvocation("printInvoice");
		$args = new \vipapis\order\OmniOrderService_printInvoice_args();
		
		$args->vendor_id = $vendor_id;
		
		$args->store_sn = $store_sn;
		
		$args->batch_no = $batch_no;
		
		$args->orders = $orders;
		
		$args->ext_info = $ext_info;
		
		$this->send_base($args);
	}
	
	public function recv_printInvoice(){
		
		$result = new \vipapis\order\OmniOrderService_printInvoice_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function pushOrderPackageWeight( $vendor_id, $store_sn, $orders){
		
		$this->send_pushOrderPackageWeight( $vendor_id, $store_sn, $orders);
		return $this->recv_pushOrderPackageWeight();
	}
	
	public function send_pushOrderPackageWeight( $vendor_id, $store_sn, $orders){
		
		$this->initInvocation("pushOrderPackageWeight");
		$args = new \vipapis\order\OmniOrderService_pushOrderPackageWeight_args();
		
		$args->vendor_id = $vendor_id;
		
		$args->store_sn = $store_sn;
		
		$args->orders = $orders;
		
		$this->send_base($args);
	}
	
	public function recv_pushOrderPackageWeight(){
		
		$result = new \vipapis\order\OmniOrderService_pushOrderPackageWeight_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function pushQCResult( $vendor_id, $store_sn, $orders){
		
		$this->send_pushQCResult( $vendor_id, $store_sn, $orders);
		return $this->recv_pushQCResult();
	}
	
	public function send_pushQCResult( $vendor_id, $store_sn, $orders){
		
		$this->initInvocation("pushQCResult");
		$args = new \vipapis\order\OmniOrderService_pushQCResult_args();
		
		$args->vendor_id = $vendor_id;
		
		$args->store_sn = $store_sn;
		
		$args->orders = $orders;
		
		$this->send_base($args);
	}
	
	public function recv_pushQCResult(){
		
		$result = new \vipapis\order\OmniOrderService_pushQCResult_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function replyStoreSn( $vendor_id, $order_store_mapping){
		
		$this->send_replyStoreSn( $vendor_id, $order_store_mapping);
		return $this->recv_replyStoreSn();
	}
	
	public function send_replyStoreSn( $vendor_id, $order_store_mapping){
		
		$this->initInvocation("replyStoreSn");
		$args = new \vipapis\order\OmniOrderService_replyStoreSn_args();
		
		$args->vendor_id = $vendor_id;
		
		$args->order_store_mapping = $order_store_mapping;
		
		$this->send_base($args);
	}
	
	public function recv_replyStoreSn(){
		
		$result = new \vipapis\order\OmniOrderService_replyStoreSn_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function responseOrderStore( $vendor_id, $order_id, $store_sn){
		
		$this->send_responseOrderStore( $vendor_id, $order_id, $store_sn);
		return $this->recv_responseOrderStore();
	}
	
	public function send_responseOrderStore( $vendor_id, $order_id, $store_sn){
		
		$this->initInvocation("responseOrderStore");
		$args = new \vipapis\order\OmniOrderService_responseOrderStore_args();
		
		$args->vendor_id = $vendor_id;
		
		$args->order_id = $order_id;
		
		$args->store_sn = $store_sn;
		
		$this->send_base($args);
	}
	
	public function recv_responseOrderStore(){
		
		$result = new \vipapis\order\OmniOrderService_responseOrderStore_result();
		$this->receive_base($result);
		
	}
	
	
	public function updateStoreSn( $vendor_id, $order_store_mapping){
		
		$this->send_updateStoreSn( $vendor_id, $order_store_mapping);
		return $this->recv_updateStoreSn();
	}
	
	public function send_updateStoreSn( $vendor_id, $order_store_mapping){
		
		$this->initInvocation("updateStoreSn");
		$args = new \vipapis\order\OmniOrderService_updateStoreSn_args();
		
		$args->vendor_id = $vendor_id;
		
		$args->order_store_mapping = $order_store_mapping;
		
		$this->send_base($args);
	}
	
	public function recv_updateStoreSn(){
		
		$result = new \vipapis\order\OmniOrderService_updateStoreSn_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
}




class OmniOrderService_confirmOrderInvoice_args {
	
	static $_TSPEC;
	public $vendor_id = null;
	public $order_invoices = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'vendor_id'
			),
			2 => array(
			'var' => 'order_invoices'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['vendor_id'])){
				
				$this->vendor_id = $vals['vendor_id'];
			}
			
			
			if (isset($vals['order_invoices'])){
				
				$this->order_invoices = $vals['order_invoices'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			$input->readI32($this->vendor_id); 
			
		}
		
		
		
		
		if(true) {
			
			
			$this->order_invoices = array();
			$_size1 = 0;
			$input->readListBegin();
			while(true){
				
				try{
					
					$elem1 = null;
					
					$elem1 = new \vipapis\order\OrderInvoiceReq();
					$elem1->read($input);
					
					$this->order_invoices[$_size1++] = $elem1;
				}
				catch(\Exception $e){
					
					break;
				}
			}
			
			$input->readListEnd();
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		$xfer += $output->writeFieldBegin('vendor_id');
		$xfer += $output->writeI32($this->vendor_id);
		
		$xfer += $output->writeFieldEnd();
		
		$xfer += $output->writeFieldBegin('order_invoices');
		
		if (!is_array($this->order_invoices)){
			
			throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
		}
		
		$output->writeListBegin();
		foreach ($this->order_invoices as $iter0){
			
			
			if (!is_object($iter0)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $iter0->write($output);
			
		}
		
		$output->writeListEnd();
		
		$xfer += $output->writeFieldEnd();
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OmniOrderService_confirmStoreDelivery_args {
	
	static $_TSPEC;
	public $vendor_id = null;
	public $order_id = null;
	public $store_sn = null;
	public $package_no = null;
	public $estimate_delivery_time = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'vendor_id'
			),
			2 => array(
			'var' => 'order_id'
			),
			3 => array(
			'var' => 'store_sn'
			),
			4 => array(
			'var' => 'package_no'
			),
			5 => array(
			'var' => 'estimate_delivery_time'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['vendor_id'])){
				
				$this->vendor_id = $vals['vendor_id'];
			}
			
			
			if (isset($vals['order_id'])){
				
				$this->order_id = $vals['order_id'];
			}
			
			
			if (isset($vals['store_sn'])){
				
				$this->store_sn = $vals['store_sn'];
			}
			
			
			if (isset($vals['package_no'])){
				
				$this->package_no = $vals['package_no'];
			}
			
			
			if (isset($vals['estimate_delivery_time'])){
				
				$this->estimate_delivery_time = $vals['estimate_delivery_time'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			$input->readI64($this->vendor_id); 
			
		}
		
		
		
		
		if(true) {
			
			$input->readString($this->order_id);
			
		}
		
		
		
		
		if(true) {
			
			$input->readString($this->store_sn);
			
		}
		
		
		
		
		if(true) {
			
			$input->readString($this->package_no);
			
		}
		
		
		
		
		if(true) {
			
			$input->readI64($this->estimate_delivery_time); 
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		$xfer += $output->writeFieldBegin('vendor_id');
		$xfer += $output->writeI64($this->vendor_id);
		
		$xfer += $output->writeFieldEnd();
		
		$xfer += $output->writeFieldBegin('order_id');
		$xfer += $output->writeString($this->order_id);
		
		$xfer += $output->writeFieldEnd();
		
		$xfer += $output->writeFieldBegin('store_sn');
		$xfer += $output->writeString($this->store_sn);
		
		$xfer += $output->writeFieldEnd();
		
		if($this->package_no !== null) {
			
			$xfer += $output->writeFieldBegin('package_no');
			$xfer += $output->writeString($this->package_no);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->estimate_delivery_time !== null) {
			
			$xfer += $output->writeFieldBegin('estimate_delivery_time');
			$xfer += $output->writeI64($this->estimate_delivery_time);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OmniOrderService_getInventoryCancelledOrders_args {
	
	static $_TSPEC;
	public $inventoryCancelledOrdersRequest = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'inventoryCancelledOrdersRequest'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['inventoryCancelledOrdersRequest'])){
				
				$this->inventoryCancelledOrdersRequest = $vals['inventoryCancelledOrdersRequest'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->inventoryCancelledOrdersRequest = new \vipapis\order\InventoryCancelledOrdersRequest();
			$this->inventoryCancelledOrdersRequest->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->inventoryCancelledOrdersRequest !== null) {
			
			$xfer += $output->writeFieldBegin('inventoryCancelledOrdersRequest');
			
			if (!is_object($this->inventoryCancelledOrdersRequest)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->inventoryCancelledOrdersRequest->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OmniOrderService_getInventoryDeductOrders_args {
	
	static $_TSPEC;
	public $inventoryDeductOrdersRequest = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'inventoryDeductOrdersRequest'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['inventoryDeductOrdersRequest'])){
				
				$this->inventoryDeductOrdersRequest = $vals['inventoryDeductOrdersRequest'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->inventoryDeductOrdersRequest = new \vipapis\order\InventoryDeductOrdersRequest();
			$this->inventoryDeductOrdersRequest->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->inventoryDeductOrdersRequest !== null) {
			
			$xfer += $output->writeFieldBegin('inventoryDeductOrdersRequest');
			
			if (!is_object($this->inventoryDeductOrdersRequest)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->inventoryDeductOrdersRequest->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OmniOrderService_getInventoryOccupiedOrders_args {
	
	static $_TSPEC;
	public $inventoryOccupiedOrdersRequest = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'inventoryOccupiedOrdersRequest'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['inventoryOccupiedOrdersRequest'])){
				
				$this->inventoryOccupiedOrdersRequest = $vals['inventoryOccupiedOrdersRequest'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->inventoryOccupiedOrdersRequest = new \vipapis\order\InventoryOccupiedOrdersRequest();
			$this->inventoryOccupiedOrdersRequest->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->inventoryOccupiedOrdersRequest !== null) {
			
			$xfer += $output->writeFieldBegin('inventoryOccupiedOrdersRequest');
			
			if (!is_object($this->inventoryOccupiedOrdersRequest)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->inventoryOccupiedOrdersRequest->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OmniOrderService_getOmniCancelledOrders_args {
	
	static $_TSPEC;
	public $vendor_id = null;
	public $brand_id = null;
	public $order_type = null;
	public $st_query_time = null;
	public $et_query_time = null;
	public $limit = null;
	public $page = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'vendor_id'
			),
			2 => array(
			'var' => 'brand_id'
			),
			3 => array(
			'var' => 'order_type'
			),
			4 => array(
			'var' => 'st_query_time'
			),
			5 => array(
			'var' => 'et_query_time'
			),
			6 => array(
			'var' => 'limit'
			),
			7 => array(
			'var' => 'page'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['vendor_id'])){
				
				$this->vendor_id = $vals['vendor_id'];
			}
			
			
			if (isset($vals['brand_id'])){
				
				$this->brand_id = $vals['brand_id'];
			}
			
			
			if (isset($vals['order_type'])){
				
				$this->order_type = $vals['order_type'];
			}
			
			
			if (isset($vals['st_query_time'])){
				
				$this->st_query_time = $vals['st_query_time'];
			}
			
			
			if (isset($vals['et_query_time'])){
				
				$this->et_query_time = $vals['et_query_time'];
			}
			
			
			if (isset($vals['limit'])){
				
				$this->limit = $vals['limit'];
			}
			
			
			if (isset($vals['page'])){
				
				$this->page = $vals['page'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			$input->readI64($this->vendor_id); 
			
		}
		
		
		
		
		if(true) {
			
			$input->readI64($this->brand_id); 
			
		}
		
		
		
		
		if(true) {
			
			$input->readByte($this->order_type); 
			
		}
		
		
		
		
		if(true) {
			
			$input->readI64($this->st_query_time); 
			
		}
		
		
		
		
		if(true) {
			
			$input->readI64($this->et_query_time); 
			
		}
		
		
		
		
		if(true) {
			
			$input->readI32($this->limit); 
			
		}
		
		
		
		
		if(true) {
			
			$input->readI32($this->page); 
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		$xfer += $output->writeFieldBegin('vendor_id');
		$xfer += $output->writeI64($this->vendor_id);
		
		$xfer += $output->writeFieldEnd();
		
		if($this->brand_id !== null) {
			
			$xfer += $output->writeFieldBegin('brand_id');
			$xfer += $output->writeI64($this->brand_id);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->order_type !== null) {
			
			$xfer += $output->writeFieldBegin('order_type');
			$xfer += $output->writeByte($this->order_type);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldBegin('st_query_time');
		$xfer += $output->writeI64($this->st_query_time);
		
		$xfer += $output->writeFieldEnd();
		
		$xfer += $output->writeFieldBegin('et_query_time');
		$xfer += $output->writeI64($this->et_query_time);
		
		$xfer += $output->writeFieldEnd();
		
		if($this->limit !== null) {
			
			$xfer += $output->writeFieldBegin('limit');
			$xfer += $output->writeI32($this->limit);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->page !== null) {
			
			$xfer += $output->writeFieldBegin('page');
			$xfer += $output->writeI32($this->page);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OmniOrderService_getOmniOrders_args {
	
	static $_TSPEC;
	public $vendor_id = null;
	public $brand_id = null;
	public $business_type = null;
	public $st_query_time = null;
	public $et_query_time = null;
	public $limit = null;
	public $page = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'vendor_id'
			),
			2 => array(
			'var' => 'brand_id'
			),
			3 => array(
			'var' => 'business_type'
			),
			4 => array(
			'var' => 'st_query_time'
			),
			5 => array(
			'var' => 'et_query_time'
			),
			6 => array(
			'var' => 'limit'
			),
			7 => array(
			'var' => 'page'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['vendor_id'])){
				
				$this->vendor_id = $vals['vendor_id'];
			}
			
			
			if (isset($vals['brand_id'])){
				
				$this->brand_id = $vals['brand_id'];
			}
			
			
			if (isset($vals['business_type'])){
				
				$this->business_type = $vals['business_type'];
			}
			
			
			if (isset($vals['st_query_time'])){
				
				$this->st_query_time = $vals['st_query_time'];
			}
			
			
			if (isset($vals['et_query_time'])){
				
				$this->et_query_time = $vals['et_query_time'];
			}
			
			
			if (isset($vals['limit'])){
				
				$this->limit = $vals['limit'];
			}
			
			
			if (isset($vals['page'])){
				
				$this->page = $vals['page'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			$input->readI64($this->vendor_id); 
			
		}
		
		
		
		
		if(true) {
			
			$input->readI64($this->brand_id); 
			
		}
		
		
		
		
		if(true) {
			
			$input->readByte($this->business_type); 
			
		}
		
		
		
		
		if(true) {
			
			$input->readI64($this->st_query_time); 
			
		}
		
		
		
		
		if(true) {
			
			$input->readI64($this->et_query_time); 
			
		}
		
		
		
		
		if(true) {
			
			$input->readI32($this->limit); 
			
		}
		
		
		
		
		if(true) {
			
			$input->readI32($this->page); 
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		$xfer += $output->writeFieldBegin('vendor_id');
		$xfer += $output->writeI64($this->vendor_id);
		
		$xfer += $output->writeFieldEnd();
		
		if($this->brand_id !== null) {
			
			$xfer += $output->writeFieldBegin('brand_id');
			$xfer += $output->writeI64($this->brand_id);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->business_type !== null) {
			
			$xfer += $output->writeFieldBegin('business_type');
			$xfer += $output->writeByte($this->business_type);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldBegin('st_query_time');
		$xfer += $output->writeI64($this->st_query_time);
		
		$xfer += $output->writeFieldEnd();
		
		$xfer += $output->writeFieldBegin('et_query_time');
		$xfer += $output->writeI64($this->et_query_time);
		
		$xfer += $output->writeFieldEnd();
		
		if($this->limit !== null) {
			
			$xfer += $output->writeFieldBegin('limit');
			$xfer += $output->writeI32($this->limit);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->page !== null) {
			
			$xfer += $output->writeFieldBegin('page');
			$xfer += $output->writeI32($this->page);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OmniOrderService_getOrderInvoice_args {
	
	static $_TSPEC;
	public $vendor_id = null;
	public $orders = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'vendor_id'
			),
			2 => array(
			'var' => 'orders'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['vendor_id'])){
				
				$this->vendor_id = $vals['vendor_id'];
			}
			
			
			if (isset($vals['orders'])){
				
				$this->orders = $vals['orders'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			$input->readI32($this->vendor_id); 
			
		}
		
		
		
		
		if(true) {
			
			
			$this->orders = array();
			$_size0 = 0;
			$input->readListBegin();
			while(true){
				
				try{
					
					$elem0 = null;
					$input->readString($elem0);
					
					$this->orders[$_size0++] = $elem0;
				}
				catch(\Exception $e){
					
					break;
				}
			}
			
			$input->readListEnd();
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		$xfer += $output->writeFieldBegin('vendor_id');
		$xfer += $output->writeI32($this->vendor_id);
		
		$xfer += $output->writeFieldEnd();
		
		$xfer += $output->writeFieldBegin('orders');
		
		if (!is_array($this->orders)){
			
			throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
		}
		
		$output->writeListBegin();
		foreach ($this->orders as $iter0){
			
			$xfer += $output->writeString($iter0);
			
		}
		
		$output->writeListEnd();
		
		$xfer += $output->writeFieldEnd();
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OmniOrderService_healthCheck_args {
	
	static $_TSPEC;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			
			);
			
		}
		
		if (is_array($vals)){
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OmniOrderService_printInvoice_args {
	
	static $_TSPEC;
	public $vendor_id = null;
	public $store_sn = null;
	public $batch_no = null;
	public $orders = null;
	public $ext_info = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'vendor_id'
			),
			2 => array(
			'var' => 'store_sn'
			),
			3 => array(
			'var' => 'batch_no'
			),
			4 => array(
			'var' => 'orders'
			),
			5 => array(
			'var' => 'ext_info'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['vendor_id'])){
				
				$this->vendor_id = $vals['vendor_id'];
			}
			
			
			if (isset($vals['store_sn'])){
				
				$this->store_sn = $vals['store_sn'];
			}
			
			
			if (isset($vals['batch_no'])){
				
				$this->batch_no = $vals['batch_no'];
			}
			
			
			if (isset($vals['orders'])){
				
				$this->orders = $vals['orders'];
			}
			
			
			if (isset($vals['ext_info'])){
				
				$this->ext_info = $vals['ext_info'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			$input->readI64($this->vendor_id); 
			
		}
		
		
		
		
		if(true) {
			
			$input->readString($this->store_sn);
			
		}
		
		
		
		
		if(true) {
			
			$input->readString($this->batch_no);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->orders = array();
			$_size1 = 0;
			$input->readListBegin();
			while(true){
				
				try{
					
					$elem1 = null;
					
					$elem1 = new \vipapis\order\OrderSeq();
					$elem1->read($input);
					
					$this->orders[$_size1++] = $elem1;
				}
				catch(\Exception $e){
					
					break;
				}
			}
			
			$input->readListEnd();
			
		}
		
		
		
		
		if(true) {
			
			
			$this->ext_info = new \vipapis\order\ExtInfo();
			$this->ext_info->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		$xfer += $output->writeFieldBegin('vendor_id');
		$xfer += $output->writeI64($this->vendor_id);
		
		$xfer += $output->writeFieldEnd();
		
		$xfer += $output->writeFieldBegin('store_sn');
		$xfer += $output->writeString($this->store_sn);
		
		$xfer += $output->writeFieldEnd();
		
		$xfer += $output->writeFieldBegin('batch_no');
		$xfer += $output->writeString($this->batch_no);
		
		$xfer += $output->writeFieldEnd();
		
		$xfer += $output->writeFieldBegin('orders');
		
		if (!is_array($this->orders)){
			
			throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
		}
		
		$output->writeListBegin();
		foreach ($this->orders as $iter0){
			
			
			if (!is_object($iter0)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $iter0->write($output);
			
		}
		
		$output->writeListEnd();
		
		$xfer += $output->writeFieldEnd();
		
		if($this->ext_info !== null) {
			
			$xfer += $output->writeFieldBegin('ext_info');
			
			if (!is_object($this->ext_info)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->ext_info->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OmniOrderService_pushOrderPackageWeight_args {
	
	static $_TSPEC;
	public $vendor_id = null;
	public $store_sn = null;
	public $orders = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'vendor_id'
			),
			2 => array(
			'var' => 'store_sn'
			),
			3 => array(
			'var' => 'orders'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['vendor_id'])){
				
				$this->vendor_id = $vals['vendor_id'];
			}
			
			
			if (isset($vals['store_sn'])){
				
				$this->store_sn = $vals['store_sn'];
			}
			
			
			if (isset($vals['orders'])){
				
				$this->orders = $vals['orders'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			$input->readI64($this->vendor_id); 
			
		}
		
		
		
		
		if(true) {
			
			$input->readString($this->store_sn);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->orders = array();
			$_size0 = 0;
			$input->readListBegin();
			while(true){
				
				try{
					
					$elem0 = null;
					
					$elem0 = new \vipapis\order\OrderPackageWeight();
					$elem0->read($input);
					
					$this->orders[$_size0++] = $elem0;
				}
				catch(\Exception $e){
					
					break;
				}
			}
			
			$input->readListEnd();
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		$xfer += $output->writeFieldBegin('vendor_id');
		$xfer += $output->writeI64($this->vendor_id);
		
		$xfer += $output->writeFieldEnd();
		
		$xfer += $output->writeFieldBegin('store_sn');
		$xfer += $output->writeString($this->store_sn);
		
		$xfer += $output->writeFieldEnd();
		
		$xfer += $output->writeFieldBegin('orders');
		
		if (!is_array($this->orders)){
			
			throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
		}
		
		$output->writeListBegin();
		foreach ($this->orders as $iter0){
			
			
			if (!is_object($iter0)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $iter0->write($output);
			
		}
		
		$output->writeListEnd();
		
		$xfer += $output->writeFieldEnd();
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OmniOrderService_pushQCResult_args {
	
	static $_TSPEC;
	public $vendor_id = null;
	public $store_sn = null;
	public $orders = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'vendor_id'
			),
			2 => array(
			'var' => 'store_sn'
			),
			3 => array(
			'var' => 'orders'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['vendor_id'])){
				
				$this->vendor_id = $vals['vendor_id'];
			}
			
			
			if (isset($vals['store_sn'])){
				
				$this->store_sn = $vals['store_sn'];
			}
			
			
			if (isset($vals['orders'])){
				
				$this->orders = $vals['orders'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			$input->readI64($this->vendor_id); 
			
		}
		
		
		
		
		if(true) {
			
			$input->readString($this->store_sn);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->orders = array();
			$_size1 = 0;
			$input->readListBegin();
			while(true){
				
				try{
					
					$elem1 = null;
					
					$elem1 = new \vipapis\order\OrderQCResult();
					$elem1->read($input);
					
					$this->orders[$_size1++] = $elem1;
				}
				catch(\Exception $e){
					
					break;
				}
			}
			
			$input->readListEnd();
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		$xfer += $output->writeFieldBegin('vendor_id');
		$xfer += $output->writeI64($this->vendor_id);
		
		$xfer += $output->writeFieldEnd();
		
		$xfer += $output->writeFieldBegin('store_sn');
		$xfer += $output->writeString($this->store_sn);
		
		$xfer += $output->writeFieldEnd();
		
		$xfer += $output->writeFieldBegin('orders');
		
		if (!is_array($this->orders)){
			
			throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
		}
		
		$output->writeListBegin();
		foreach ($this->orders as $iter0){
			
			
			if (!is_object($iter0)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $iter0->write($output);
			
		}
		
		$output->writeListEnd();
		
		$xfer += $output->writeFieldEnd();
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OmniOrderService_replyStoreSn_args {
	
	static $_TSPEC;
	public $vendor_id = null;
	public $order_store_mapping = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'vendor_id'
			),
			2 => array(
			'var' => 'order_store_mapping'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['vendor_id'])){
				
				$this->vendor_id = $vals['vendor_id'];
			}
			
			
			if (isset($vals['order_store_mapping'])){
				
				$this->order_store_mapping = $vals['order_store_mapping'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			$input->readI64($this->vendor_id); 
			
		}
		
		
		
		
		if(true) {
			
			
			$this->order_store_mapping = array();
			$_size1 = 0;
			$input->readListBegin();
			while(true){
				
				try{
					
					$elem1 = null;
					
					$elem1 = new \vipapis\order\OrderStoreMapping();
					$elem1->read($input);
					
					$this->order_store_mapping[$_size1++] = $elem1;
				}
				catch(\Exception $e){
					
					break;
				}
			}
			
			$input->readListEnd();
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		$xfer += $output->writeFieldBegin('vendor_id');
		$xfer += $output->writeI64($this->vendor_id);
		
		$xfer += $output->writeFieldEnd();
		
		$xfer += $output->writeFieldBegin('order_store_mapping');
		
		if (!is_array($this->order_store_mapping)){
			
			throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
		}
		
		$output->writeListBegin();
		foreach ($this->order_store_mapping as $iter0){
			
			
			if (!is_object($iter0)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $iter0->write($output);
			
		}
		
		$output->writeListEnd();
		
		$xfer += $output->writeFieldEnd();
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OmniOrderService_responseOrderStore_args {
	
	static $_TSPEC;
	public $vendor_id = null;
	public $order_id = null;
	public $store_sn = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'vendor_id'
			),
			2 => array(
			'var' => 'order_id'
			),
			3 => array(
			'var' => 'store_sn'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['vendor_id'])){
				
				$this->vendor_id = $vals['vendor_id'];
			}
			
			
			if (isset($vals['order_id'])){
				
				$this->order_id = $vals['order_id'];
			}
			
			
			if (isset($vals['store_sn'])){
				
				$this->store_sn = $vals['store_sn'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			$input->readI64($this->vendor_id); 
			
		}
		
		
		
		
		if(true) {
			
			$input->readString($this->order_id);
			
		}
		
		
		
		
		if(true) {
			
			$input->readString($this->store_sn);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		$xfer += $output->writeFieldBegin('vendor_id');
		$xfer += $output->writeI64($this->vendor_id);
		
		$xfer += $output->writeFieldEnd();
		
		$xfer += $output->writeFieldBegin('order_id');
		$xfer += $output->writeString($this->order_id);
		
		$xfer += $output->writeFieldEnd();
		
		$xfer += $output->writeFieldBegin('store_sn');
		$xfer += $output->writeString($this->store_sn);
		
		$xfer += $output->writeFieldEnd();
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OmniOrderService_updateStoreSn_args {
	
	static $_TSPEC;
	public $vendor_id = null;
	public $order_store_mapping = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'vendor_id'
			),
			2 => array(
			'var' => 'order_store_mapping'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['vendor_id'])){
				
				$this->vendor_id = $vals['vendor_id'];
			}
			
			
			if (isset($vals['order_store_mapping'])){
				
				$this->order_store_mapping = $vals['order_store_mapping'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			$input->readI64($this->vendor_id); 
			
		}
		
		
		
		
		if(true) {
			
			
			$this->order_store_mapping = array();
			$_size0 = 0;
			$input->readListBegin();
			while(true){
				
				try{
					
					$elem0 = null;
					
					$elem0 = new \vipapis\order\OrderStoreMappingEx();
					$elem0->read($input);
					
					$this->order_store_mapping[$_size0++] = $elem0;
				}
				catch(\Exception $e){
					
					break;
				}
			}
			
			$input->readListEnd();
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		$xfer += $output->writeFieldBegin('vendor_id');
		$xfer += $output->writeI64($this->vendor_id);
		
		$xfer += $output->writeFieldEnd();
		
		$xfer += $output->writeFieldBegin('order_store_mapping');
		
		if (!is_array($this->order_store_mapping)){
			
			throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
		}
		
		$output->writeListBegin();
		foreach ($this->order_store_mapping as $iter0){
			
			
			if (!is_object($iter0)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $iter0->write($output);
			
		}
		
		$output->writeListEnd();
		
		$xfer += $output->writeFieldEnd();
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OmniOrderService_confirmOrderInvoice_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \vipapis\order\OrderInvoiceConfirmResp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OmniOrderService_confirmStoreDelivery_result {
	
	static $_TSPEC;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OmniOrderService_getInventoryCancelledOrders_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \vipapis\order\CancelledOrdersResponse();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OmniOrderService_getInventoryDeductOrders_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \vipapis\order\InventoryDeductOrdersResponse();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OmniOrderService_getInventoryOccupiedOrders_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \vipapis\order\OccupiedOrderResponse();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OmniOrderService_getOmniCancelledOrders_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \vipapis\order\GetOmniCancelledOrdersResp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OmniOrderService_getOmniOrders_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \vipapis\order\GetOmniOrdersResp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OmniOrderService_getOrderInvoice_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \vipapis\order\OrderInvoiceQueryResp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OmniOrderService_healthCheck_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \com\vip\hermes\core\health\CheckResult();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OmniOrderService_printInvoice_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \vipapis\order\PrintInvoiceResp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OmniOrderService_pushOrderPackageWeight_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \vipapis\order\PushOrderPackageWeightResp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OmniOrderService_pushQCResult_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \vipapis\order\PushQCResultResp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OmniOrderService_replyStoreSn_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \vipapis\order\ReplyStoreSnResp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OmniOrderService_responseOrderStore_result {
	
	static $_TSPEC;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OmniOrderService_updateStoreSn_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \vipapis\order\UpdateStoreSnResp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




?>