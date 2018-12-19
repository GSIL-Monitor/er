<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\vop\sync;
interface ScheduleSalesSyncServiceIf{
	
	
	public function healthCheck();
	
	public function releaseSalesStock();
	
	public function syncInventoryTask();
	
	public function syncLockSkusToCache();
	
	public function syncMasterSalesSkus();
	
	public function syncSalesToRedis();
	
	public function syncScheduleSkuByVendorId( $vendorId);
	
	public function syncScheduleSkusToRedis();
	
	public function syncSpecialSales();
	
	public function syncSpecialSalesSku();
	
	public function syncVendorSchedule();
	
}

class _ScheduleSalesSyncServiceClient extends \Osp\Base\OspStub implements \com\vip\vop\sync\ScheduleSalesSyncServiceIf{
	
	public function __construct(){
		
		parent::__construct("com.vip.vop.sync.ScheduleSalesSyncService", "1.0.0");
	}
	
	
	public function healthCheck(){
		
		$this->send_healthCheck();
		return $this->recv_healthCheck();
	}
	
	public function send_healthCheck(){
		
		$this->initInvocation("healthCheck");
		$args = new \com\vip\vop\sync\ScheduleSalesSyncService_healthCheck_args();
		
		$this->send_base($args);
	}
	
	public function recv_healthCheck(){
		
		$result = new \com\vip\vop\sync\ScheduleSalesSyncService_healthCheck_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function releaseSalesStock(){
		
		$this->send_releaseSalesStock();
		return $this->recv_releaseSalesStock();
	}
	
	public function send_releaseSalesStock(){
		
		$this->initInvocation("releaseSalesStock");
		$args = new \com\vip\vop\sync\ScheduleSalesSyncService_releaseSalesStock_args();
		
		$this->send_base($args);
	}
	
	public function recv_releaseSalesStock(){
		
		$result = new \com\vip\vop\sync\ScheduleSalesSyncService_releaseSalesStock_result();
		$this->receive_base($result);
		
	}
	
	
	public function syncInventoryTask(){
		
		$this->send_syncInventoryTask();
		return $this->recv_syncInventoryTask();
	}
	
	public function send_syncInventoryTask(){
		
		$this->initInvocation("syncInventoryTask");
		$args = new \com\vip\vop\sync\ScheduleSalesSyncService_syncInventoryTask_args();
		
		$this->send_base($args);
	}
	
	public function recv_syncInventoryTask(){
		
		$result = new \com\vip\vop\sync\ScheduleSalesSyncService_syncInventoryTask_result();
		$this->receive_base($result);
		
	}
	
	
	public function syncLockSkusToCache(){
		
		$this->send_syncLockSkusToCache();
		return $this->recv_syncLockSkusToCache();
	}
	
	public function send_syncLockSkusToCache(){
		
		$this->initInvocation("syncLockSkusToCache");
		$args = new \com\vip\vop\sync\ScheduleSalesSyncService_syncLockSkusToCache_args();
		
		$this->send_base($args);
	}
	
	public function recv_syncLockSkusToCache(){
		
		$result = new \com\vip\vop\sync\ScheduleSalesSyncService_syncLockSkusToCache_result();
		$this->receive_base($result);
		
	}
	
	
	public function syncMasterSalesSkus(){
		
		$this->send_syncMasterSalesSkus();
		return $this->recv_syncMasterSalesSkus();
	}
	
	public function send_syncMasterSalesSkus(){
		
		$this->initInvocation("syncMasterSalesSkus");
		$args = new \com\vip\vop\sync\ScheduleSalesSyncService_syncMasterSalesSkus_args();
		
		$this->send_base($args);
	}
	
	public function recv_syncMasterSalesSkus(){
		
		$result = new \com\vip\vop\sync\ScheduleSalesSyncService_syncMasterSalesSkus_result();
		$this->receive_base($result);
		
	}
	
	
	public function syncSalesToRedis(){
		
		$this->send_syncSalesToRedis();
		return $this->recv_syncSalesToRedis();
	}
	
	public function send_syncSalesToRedis(){
		
		$this->initInvocation("syncSalesToRedis");
		$args = new \com\vip\vop\sync\ScheduleSalesSyncService_syncSalesToRedis_args();
		
		$this->send_base($args);
	}
	
	public function recv_syncSalesToRedis(){
		
		$result = new \com\vip\vop\sync\ScheduleSalesSyncService_syncSalesToRedis_result();
		$this->receive_base($result);
		
	}
	
	
	public function syncScheduleSkuByVendorId( $vendorId){
		
		$this->send_syncScheduleSkuByVendorId( $vendorId);
		return $this->recv_syncScheduleSkuByVendorId();
	}
	
	public function send_syncScheduleSkuByVendorId( $vendorId){
		
		$this->initInvocation("syncScheduleSkuByVendorId");
		$args = new \com\vip\vop\sync\ScheduleSalesSyncService_syncScheduleSkuByVendorId_args();
		
		$args->vendorId = $vendorId;
		
		$this->send_base($args);
	}
	
	public function recv_syncScheduleSkuByVendorId(){
		
		$result = new \com\vip\vop\sync\ScheduleSalesSyncService_syncScheduleSkuByVendorId_result();
		$this->receive_base($result);
		
	}
	
	
	public function syncScheduleSkusToRedis(){
		
		$this->send_syncScheduleSkusToRedis();
		return $this->recv_syncScheduleSkusToRedis();
	}
	
	public function send_syncScheduleSkusToRedis(){
		
		$this->initInvocation("syncScheduleSkusToRedis");
		$args = new \com\vip\vop\sync\ScheduleSalesSyncService_syncScheduleSkusToRedis_args();
		
		$this->send_base($args);
	}
	
	public function recv_syncScheduleSkusToRedis(){
		
		$result = new \com\vip\vop\sync\ScheduleSalesSyncService_syncScheduleSkusToRedis_result();
		$this->receive_base($result);
		
	}
	
	
	public function syncSpecialSales(){
		
		$this->send_syncSpecialSales();
		return $this->recv_syncSpecialSales();
	}
	
	public function send_syncSpecialSales(){
		
		$this->initInvocation("syncSpecialSales");
		$args = new \com\vip\vop\sync\ScheduleSalesSyncService_syncSpecialSales_args();
		
		$this->send_base($args);
	}
	
	public function recv_syncSpecialSales(){
		
		$result = new \com\vip\vop\sync\ScheduleSalesSyncService_syncSpecialSales_result();
		$this->receive_base($result);
		
	}
	
	
	public function syncSpecialSalesSku(){
		
		$this->send_syncSpecialSalesSku();
		return $this->recv_syncSpecialSalesSku();
	}
	
	public function send_syncSpecialSalesSku(){
		
		$this->initInvocation("syncSpecialSalesSku");
		$args = new \com\vip\vop\sync\ScheduleSalesSyncService_syncSpecialSalesSku_args();
		
		$this->send_base($args);
	}
	
	public function recv_syncSpecialSalesSku(){
		
		$result = new \com\vip\vop\sync\ScheduleSalesSyncService_syncSpecialSalesSku_result();
		$this->receive_base($result);
		
	}
	
	
	public function syncVendorSchedule(){
		
		$this->send_syncVendorSchedule();
		return $this->recv_syncVendorSchedule();
	}
	
	public function send_syncVendorSchedule(){
		
		$this->initInvocation("syncVendorSchedule");
		$args = new \com\vip\vop\sync\ScheduleSalesSyncService_syncVendorSchedule_args();
		
		$this->send_base($args);
	}
	
	public function recv_syncVendorSchedule(){
		
		$result = new \com\vip\vop\sync\ScheduleSalesSyncService_syncVendorSchedule_result();
		$this->receive_base($result);
		
	}
	
	
}




class ScheduleSalesSyncService_healthCheck_args {
	
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




class ScheduleSalesSyncService_releaseSalesStock_args {
	
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




class ScheduleSalesSyncService_syncInventoryTask_args {
	
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




class ScheduleSalesSyncService_syncLockSkusToCache_args {
	
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




class ScheduleSalesSyncService_syncMasterSalesSkus_args {
	
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




class ScheduleSalesSyncService_syncSalesToRedis_args {
	
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




class ScheduleSalesSyncService_syncScheduleSkuByVendorId_args {
	
	static $_TSPEC;
	public $vendorId = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'vendorId'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['vendorId'])){
				
				$this->vendorId = $vals['vendorId'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			$input->readI64($this->vendorId); 
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		$xfer += $output->writeFieldBegin('vendorId');
		$xfer += $output->writeI64($this->vendorId);
		
		$xfer += $output->writeFieldEnd();
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class ScheduleSalesSyncService_syncScheduleSkusToRedis_args {
	
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




class ScheduleSalesSyncService_syncSpecialSales_args {
	
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




class ScheduleSalesSyncService_syncSpecialSalesSku_args {
	
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




class ScheduleSalesSyncService_syncVendorSchedule_args {
	
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




class ScheduleSalesSyncService_healthCheck_result {
	
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




class ScheduleSalesSyncService_releaseSalesStock_result {
	
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




class ScheduleSalesSyncService_syncInventoryTask_result {
	
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




class ScheduleSalesSyncService_syncLockSkusToCache_result {
	
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




class ScheduleSalesSyncService_syncMasterSalesSkus_result {
	
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




class ScheduleSalesSyncService_syncSalesToRedis_result {
	
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




class ScheduleSalesSyncService_syncScheduleSkuByVendorId_result {
	
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




class ScheduleSalesSyncService_syncScheduleSkusToRedis_result {
	
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




class ScheduleSalesSyncService_syncSpecialSales_result {
	
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




class ScheduleSalesSyncService_syncSpecialSalesSku_result {
	
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




class ScheduleSalesSyncService_syncVendorSchedule_result {
	
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




?>