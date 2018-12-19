<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\vop\vendor;
interface VendorWhiteListServiceIf{
	
	
	public function getCooperationNoConfigInfo( $vendorId, $cooperationNo);
	
	public function getInventoryUpdatePath( $vendorCode, $cooperationNo);
	
	public function healthCheck();
	
	public function isCooperationNoInWhiteList( $vendorCode, $cooperationNo);
	
	public function isScheduleInWhiteList( $vendorCode, $scheduleId);
	
	public function isVopVendorByVendorCode( $vendorCode);
	
	public function isVopVendorByVendorId( $vendorId);
	
}

class _VendorWhiteListServiceClient extends \Osp\Base\OspStub implements \com\vip\vop\vendor\VendorWhiteListServiceIf{
	
	public function __construct(){
		
		parent::__construct("com.vip.vop.vendor.VendorWhiteListService", "1.0.0");
	}
	
	
	public function getCooperationNoConfigInfo( $vendorId, $cooperationNo){
		
		$this->send_getCooperationNoConfigInfo( $vendorId, $cooperationNo);
		return $this->recv_getCooperationNoConfigInfo();
	}
	
	public function send_getCooperationNoConfigInfo( $vendorId, $cooperationNo){
		
		$this->initInvocation("getCooperationNoConfigInfo");
		$args = new \com\vip\vop\vendor\VendorWhiteListService_getCooperationNoConfigInfo_args();
		
		$args->vendorId = $vendorId;
		
		$args->cooperationNo = $cooperationNo;
		
		$this->send_base($args);
	}
	
	public function recv_getCooperationNoConfigInfo(){
		
		$result = new \com\vip\vop\vendor\VendorWhiteListService_getCooperationNoConfigInfo_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function getInventoryUpdatePath( $vendorCode, $cooperationNo){
		
		$this->send_getInventoryUpdatePath( $vendorCode, $cooperationNo);
		return $this->recv_getInventoryUpdatePath();
	}
	
	public function send_getInventoryUpdatePath( $vendorCode, $cooperationNo){
		
		$this->initInvocation("getInventoryUpdatePath");
		$args = new \com\vip\vop\vendor\VendorWhiteListService_getInventoryUpdatePath_args();
		
		$args->vendorCode = $vendorCode;
		
		$args->cooperationNo = $cooperationNo;
		
		$this->send_base($args);
	}
	
	public function recv_getInventoryUpdatePath(){
		
		$result = new \com\vip\vop\vendor\VendorWhiteListService_getInventoryUpdatePath_result();
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
		$args = new \com\vip\vop\vendor\VendorWhiteListService_healthCheck_args();
		
		$this->send_base($args);
	}
	
	public function recv_healthCheck(){
		
		$result = new \com\vip\vop\vendor\VendorWhiteListService_healthCheck_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function isCooperationNoInWhiteList( $vendorCode, $cooperationNo){
		
		$this->send_isCooperationNoInWhiteList( $vendorCode, $cooperationNo);
		return $this->recv_isCooperationNoInWhiteList();
	}
	
	public function send_isCooperationNoInWhiteList( $vendorCode, $cooperationNo){
		
		$this->initInvocation("isCooperationNoInWhiteList");
		$args = new \com\vip\vop\vendor\VendorWhiteListService_isCooperationNoInWhiteList_args();
		
		$args->vendorCode = $vendorCode;
		
		$args->cooperationNo = $cooperationNo;
		
		$this->send_base($args);
	}
	
	public function recv_isCooperationNoInWhiteList(){
		
		$result = new \com\vip\vop\vendor\VendorWhiteListService_isCooperationNoInWhiteList_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function isScheduleInWhiteList( $vendorCode, $scheduleId){
		
		$this->send_isScheduleInWhiteList( $vendorCode, $scheduleId);
		return $this->recv_isScheduleInWhiteList();
	}
	
	public function send_isScheduleInWhiteList( $vendorCode, $scheduleId){
		
		$this->initInvocation("isScheduleInWhiteList");
		$args = new \com\vip\vop\vendor\VendorWhiteListService_isScheduleInWhiteList_args();
		
		$args->vendorCode = $vendorCode;
		
		$args->scheduleId = $scheduleId;
		
		$this->send_base($args);
	}
	
	public function recv_isScheduleInWhiteList(){
		
		$result = new \com\vip\vop\vendor\VendorWhiteListService_isScheduleInWhiteList_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function isVopVendorByVendorCode( $vendorCode){
		
		$this->send_isVopVendorByVendorCode( $vendorCode);
		return $this->recv_isVopVendorByVendorCode();
	}
	
	public function send_isVopVendorByVendorCode( $vendorCode){
		
		$this->initInvocation("isVopVendorByVendorCode");
		$args = new \com\vip\vop\vendor\VendorWhiteListService_isVopVendorByVendorCode_args();
		
		$args->vendorCode = $vendorCode;
		
		$this->send_base($args);
	}
	
	public function recv_isVopVendorByVendorCode(){
		
		$result = new \com\vip\vop\vendor\VendorWhiteListService_isVopVendorByVendorCode_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function isVopVendorByVendorId( $vendorId){
		
		$this->send_isVopVendorByVendorId( $vendorId);
		return $this->recv_isVopVendorByVendorId();
	}
	
	public function send_isVopVendorByVendorId( $vendorId){
		
		$this->initInvocation("isVopVendorByVendorId");
		$args = new \com\vip\vop\vendor\VendorWhiteListService_isVopVendorByVendorId_args();
		
		$args->vendorId = $vendorId;
		
		$this->send_base($args);
	}
	
	public function recv_isVopVendorByVendorId(){
		
		$result = new \com\vip\vop\vendor\VendorWhiteListService_isVopVendorByVendorId_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
}




class VendorWhiteListService_getCooperationNoConfigInfo_args {
	
	static $_TSPEC;
	public $vendorId = null;
	public $cooperationNo = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'vendorId'
			),
			2 => array(
			'var' => 'cooperationNo'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['vendorId'])){
				
				$this->vendorId = $vals['vendorId'];
			}
			
			
			if (isset($vals['cooperationNo'])){
				
				$this->cooperationNo = $vals['cooperationNo'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			$input->readI32($this->vendorId); 
			
		}
		
		
		
		
		if(true) {
			
			$input->readI32($this->cooperationNo); 
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		$xfer += $output->writeFieldBegin('vendorId');
		$xfer += $output->writeI32($this->vendorId);
		
		$xfer += $output->writeFieldEnd();
		
		$xfer += $output->writeFieldBegin('cooperationNo');
		$xfer += $output->writeI32($this->cooperationNo);
		
		$xfer += $output->writeFieldEnd();
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class VendorWhiteListService_getInventoryUpdatePath_args {
	
	static $_TSPEC;
	public $vendorCode = null;
	public $cooperationNo = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'vendorCode'
			),
			2 => array(
			'var' => 'cooperationNo'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['vendorCode'])){
				
				$this->vendorCode = $vals['vendorCode'];
			}
			
			
			if (isset($vals['cooperationNo'])){
				
				$this->cooperationNo = $vals['cooperationNo'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			$input->readString($this->vendorCode);
			
		}
		
		
		
		
		if(true) {
			
			$input->readI32($this->cooperationNo); 
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		$xfer += $output->writeFieldBegin('vendorCode');
		$xfer += $output->writeString($this->vendorCode);
		
		$xfer += $output->writeFieldEnd();
		
		$xfer += $output->writeFieldBegin('cooperationNo');
		$xfer += $output->writeI32($this->cooperationNo);
		
		$xfer += $output->writeFieldEnd();
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class VendorWhiteListService_healthCheck_args {
	
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




class VendorWhiteListService_isCooperationNoInWhiteList_args {
	
	static $_TSPEC;
	public $vendorCode = null;
	public $cooperationNo = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'vendorCode'
			),
			2 => array(
			'var' => 'cooperationNo'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['vendorCode'])){
				
				$this->vendorCode = $vals['vendorCode'];
			}
			
			
			if (isset($vals['cooperationNo'])){
				
				$this->cooperationNo = $vals['cooperationNo'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			$input->readString($this->vendorCode);
			
		}
		
		
		
		
		if(true) {
			
			$input->readI32($this->cooperationNo); 
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		$xfer += $output->writeFieldBegin('vendorCode');
		$xfer += $output->writeString($this->vendorCode);
		
		$xfer += $output->writeFieldEnd();
		
		$xfer += $output->writeFieldBegin('cooperationNo');
		$xfer += $output->writeI32($this->cooperationNo);
		
		$xfer += $output->writeFieldEnd();
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class VendorWhiteListService_isScheduleInWhiteList_args {
	
	static $_TSPEC;
	public $vendorCode = null;
	public $scheduleId = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'vendorCode'
			),
			2 => array(
			'var' => 'scheduleId'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['vendorCode'])){
				
				$this->vendorCode = $vals['vendorCode'];
			}
			
			
			if (isset($vals['scheduleId'])){
				
				$this->scheduleId = $vals['scheduleId'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			$input->readString($this->vendorCode);
			
		}
		
		
		
		
		if(true) {
			
			$input->readI32($this->scheduleId); 
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		$xfer += $output->writeFieldBegin('vendorCode');
		$xfer += $output->writeString($this->vendorCode);
		
		$xfer += $output->writeFieldEnd();
		
		$xfer += $output->writeFieldBegin('scheduleId');
		$xfer += $output->writeI32($this->scheduleId);
		
		$xfer += $output->writeFieldEnd();
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class VendorWhiteListService_isVopVendorByVendorCode_args {
	
	static $_TSPEC;
	public $vendorCode = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'vendorCode'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['vendorCode'])){
				
				$this->vendorCode = $vals['vendorCode'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			$input->readString($this->vendorCode);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		$xfer += $output->writeFieldBegin('vendorCode');
		$xfer += $output->writeString($this->vendorCode);
		
		$xfer += $output->writeFieldEnd();
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class VendorWhiteListService_isVopVendorByVendorId_args {
	
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
			
			$input->readI32($this->vendorId); 
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		$xfer += $output->writeFieldBegin('vendorId');
		$xfer += $output->writeI32($this->vendorId);
		
		$xfer += $output->writeFieldEnd();
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class VendorWhiteListService_getCooperationNoConfigInfo_result {
	
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
			
			
			$this->success = new \com\vip\vop\vendor\CooperationNoConfigInfo();
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




class VendorWhiteListService_getInventoryUpdatePath_result {
	
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
			
			$input->readI32($this->success); 
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		$xfer += $output->writeFieldBegin('success');
		$xfer += $output->writeI32($this->success);
		
		$xfer += $output->writeFieldEnd();
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class VendorWhiteListService_healthCheck_result {
	
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




class VendorWhiteListService_isCooperationNoInWhiteList_result {
	
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
			
			$input->readI32($this->success); 
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		$xfer += $output->writeFieldBegin('success');
		$xfer += $output->writeI32($this->success);
		
		$xfer += $output->writeFieldEnd();
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class VendorWhiteListService_isScheduleInWhiteList_result {
	
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
			
			$input->readI32($this->success); 
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		$xfer += $output->writeFieldBegin('success');
		$xfer += $output->writeI32($this->success);
		
		$xfer += $output->writeFieldEnd();
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class VendorWhiteListService_isVopVendorByVendorCode_result {
	
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
			
			$input->readBool($this->success);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		$xfer += $output->writeFieldBegin('success');
		$xfer += $output->writeBool($this->success);
		
		$xfer += $output->writeFieldEnd();
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class VendorWhiteListService_isVopVendorByVendorId_result {
	
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
			
			$input->readBool($this->success);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		$xfer += $output->writeFieldBegin('success');
		$xfer += $output->writeBool($this->success);
		
		$xfer += $output->writeFieldEnd();
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




?>