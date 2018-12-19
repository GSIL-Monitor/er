<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\vop\sync;
interface InventoryOccupiedOrderSyncServiceIf{
	
	
	public function delDeductOrderRedisKey( $intervalDays, $startIndex, $isNewRedis);
	
	public function delExpiredOccupiedOrdersFromRedis();
	
	public function delOccupiedOrdersFromRedis( $startDays, $endDays, $isNewRedis);
	
	public function healthCheck();
	
	public function syncAllDeductOrderToRedis( $isCluster);
	
	public function syncAllOccupiedOrderToRedis( $isCluster);
	
	public function syncIncDeductOrderToRedis( $startIndex, $isCluster);
	
	public function syncIncOccupiedOrderToRedis( $startIndex, $isCluster);
	
}

class _InventoryOccupiedOrderSyncServiceClient extends \Osp\Base\OspStub implements \com\vip\vop\sync\InventoryOccupiedOrderSyncServiceIf{
	
	public function __construct(){
		
		parent::__construct("com.vip.vop.sync.InventoryOccupiedOrderSyncService", "1.0.0");
	}
	
	
	public function delDeductOrderRedisKey( $intervalDays, $startIndex, $isNewRedis){
		
		$this->send_delDeductOrderRedisKey( $intervalDays, $startIndex, $isNewRedis);
		return $this->recv_delDeductOrderRedisKey();
	}
	
	public function send_delDeductOrderRedisKey( $intervalDays, $startIndex, $isNewRedis){
		
		$this->initInvocation("delDeductOrderRedisKey");
		$args = new \com\vip\vop\sync\InventoryOccupiedOrderSyncService_delDeductOrderRedisKey_args();
		
		$args->intervalDays = $intervalDays;
		
		$args->startIndex = $startIndex;
		
		$args->isNewRedis = $isNewRedis;
		
		$this->send_base($args);
	}
	
	public function recv_delDeductOrderRedisKey(){
		
		$result = new \com\vip\vop\sync\InventoryOccupiedOrderSyncService_delDeductOrderRedisKey_result();
		$this->receive_base($result);
		
	}
	
	
	public function delExpiredOccupiedOrdersFromRedis(){
		
		$this->send_delExpiredOccupiedOrdersFromRedis();
		return $this->recv_delExpiredOccupiedOrdersFromRedis();
	}
	
	public function send_delExpiredOccupiedOrdersFromRedis(){
		
		$this->initInvocation("delExpiredOccupiedOrdersFromRedis");
		$args = new \com\vip\vop\sync\InventoryOccupiedOrderSyncService_delExpiredOccupiedOrdersFromRedis_args();
		
		$this->send_base($args);
	}
	
	public function recv_delExpiredOccupiedOrdersFromRedis(){
		
		$result = new \com\vip\vop\sync\InventoryOccupiedOrderSyncService_delExpiredOccupiedOrdersFromRedis_result();
		$this->receive_base($result);
		
	}
	
	
	public function delOccupiedOrdersFromRedis( $startDays, $endDays, $isNewRedis){
		
		$this->send_delOccupiedOrdersFromRedis( $startDays, $endDays, $isNewRedis);
		return $this->recv_delOccupiedOrdersFromRedis();
	}
	
	public function send_delOccupiedOrdersFromRedis( $startDays, $endDays, $isNewRedis){
		
		$this->initInvocation("delOccupiedOrdersFromRedis");
		$args = new \com\vip\vop\sync\InventoryOccupiedOrderSyncService_delOccupiedOrdersFromRedis_args();
		
		$args->startDays = $startDays;
		
		$args->endDays = $endDays;
		
		$args->isNewRedis = $isNewRedis;
		
		$this->send_base($args);
	}
	
	public function recv_delOccupiedOrdersFromRedis(){
		
		$result = new \com\vip\vop\sync\InventoryOccupiedOrderSyncService_delOccupiedOrdersFromRedis_result();
		$this->receive_base($result);
		
	}
	
	
	public function healthCheck(){
		
		$this->send_healthCheck();
		return $this->recv_healthCheck();
	}
	
	public function send_healthCheck(){
		
		$this->initInvocation("healthCheck");
		$args = new \com\vip\vop\sync\InventoryOccupiedOrderSyncService_healthCheck_args();
		
		$this->send_base($args);
	}
	
	public function recv_healthCheck(){
		
		$result = new \com\vip\vop\sync\InventoryOccupiedOrderSyncService_healthCheck_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function syncAllDeductOrderToRedis( $isCluster){
		
		$this->send_syncAllDeductOrderToRedis( $isCluster);
		return $this->recv_syncAllDeductOrderToRedis();
	}
	
	public function send_syncAllDeductOrderToRedis( $isCluster){
		
		$this->initInvocation("syncAllDeductOrderToRedis");
		$args = new \com\vip\vop\sync\InventoryOccupiedOrderSyncService_syncAllDeductOrderToRedis_args();
		
		$args->isCluster = $isCluster;
		
		$this->send_base($args);
	}
	
	public function recv_syncAllDeductOrderToRedis(){
		
		$result = new \com\vip\vop\sync\InventoryOccupiedOrderSyncService_syncAllDeductOrderToRedis_result();
		$this->receive_base($result);
		
	}
	
	
	public function syncAllOccupiedOrderToRedis( $isCluster){
		
		$this->send_syncAllOccupiedOrderToRedis( $isCluster);
		return $this->recv_syncAllOccupiedOrderToRedis();
	}
	
	public function send_syncAllOccupiedOrderToRedis( $isCluster){
		
		$this->initInvocation("syncAllOccupiedOrderToRedis");
		$args = new \com\vip\vop\sync\InventoryOccupiedOrderSyncService_syncAllOccupiedOrderToRedis_args();
		
		$args->isCluster = $isCluster;
		
		$this->send_base($args);
	}
	
	public function recv_syncAllOccupiedOrderToRedis(){
		
		$result = new \com\vip\vop\sync\InventoryOccupiedOrderSyncService_syncAllOccupiedOrderToRedis_result();
		$this->receive_base($result);
		
	}
	
	
	public function syncIncDeductOrderToRedis( $startIndex, $isCluster){
		
		$this->send_syncIncDeductOrderToRedis( $startIndex, $isCluster);
		return $this->recv_syncIncDeductOrderToRedis();
	}
	
	public function send_syncIncDeductOrderToRedis( $startIndex, $isCluster){
		
		$this->initInvocation("syncIncDeductOrderToRedis");
		$args = new \com\vip\vop\sync\InventoryOccupiedOrderSyncService_syncIncDeductOrderToRedis_args();
		
		$args->startIndex = $startIndex;
		
		$args->isCluster = $isCluster;
		
		$this->send_base($args);
	}
	
	public function recv_syncIncDeductOrderToRedis(){
		
		$result = new \com\vip\vop\sync\InventoryOccupiedOrderSyncService_syncIncDeductOrderToRedis_result();
		$this->receive_base($result);
		
	}
	
	
	public function syncIncOccupiedOrderToRedis( $startIndex, $isCluster){
		
		$this->send_syncIncOccupiedOrderToRedis( $startIndex, $isCluster);
		return $this->recv_syncIncOccupiedOrderToRedis();
	}
	
	public function send_syncIncOccupiedOrderToRedis( $startIndex, $isCluster){
		
		$this->initInvocation("syncIncOccupiedOrderToRedis");
		$args = new \com\vip\vop\sync\InventoryOccupiedOrderSyncService_syncIncOccupiedOrderToRedis_args();
		
		$args->startIndex = $startIndex;
		
		$args->isCluster = $isCluster;
		
		$this->send_base($args);
	}
	
	public function recv_syncIncOccupiedOrderToRedis(){
		
		$result = new \com\vip\vop\sync\InventoryOccupiedOrderSyncService_syncIncOccupiedOrderToRedis_result();
		$this->receive_base($result);
		
	}
	
	
}




class InventoryOccupiedOrderSyncService_delDeductOrderRedisKey_args {
	
	static $_TSPEC;
	public $intervalDays = null;
	public $startIndex = null;
	public $isNewRedis = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'intervalDays'
			),
			2 => array(
			'var' => 'startIndex'
			),
			3 => array(
			'var' => 'isNewRedis'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['intervalDays'])){
				
				$this->intervalDays = $vals['intervalDays'];
			}
			
			
			if (isset($vals['startIndex'])){
				
				$this->startIndex = $vals['startIndex'];
			}
			
			
			if (isset($vals['isNewRedis'])){
				
				$this->isNewRedis = $vals['isNewRedis'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			$input->readI32($this->intervalDays); 
			
		}
		
		
		
		
		if(true) {
			
			$input->readI64($this->startIndex); 
			
		}
		
		
		
		
		if(true) {
			
			$input->readBool($this->isNewRedis);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		$xfer += $output->writeFieldBegin('intervalDays');
		$xfer += $output->writeI32($this->intervalDays);
		
		$xfer += $output->writeFieldEnd();
		
		$xfer += $output->writeFieldBegin('startIndex');
		$xfer += $output->writeI64($this->startIndex);
		
		$xfer += $output->writeFieldEnd();
		
		if($this->isNewRedis !== null) {
			
			$xfer += $output->writeFieldBegin('isNewRedis');
			$xfer += $output->writeBool($this->isNewRedis);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class InventoryOccupiedOrderSyncService_delExpiredOccupiedOrdersFromRedis_args {
	
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




class InventoryOccupiedOrderSyncService_delOccupiedOrdersFromRedis_args {
	
	static $_TSPEC;
	public $startDays = null;
	public $endDays = null;
	public $isNewRedis = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'startDays'
			),
			2 => array(
			'var' => 'endDays'
			),
			3 => array(
			'var' => 'isNewRedis'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['startDays'])){
				
				$this->startDays = $vals['startDays'];
			}
			
			
			if (isset($vals['endDays'])){
				
				$this->endDays = $vals['endDays'];
			}
			
			
			if (isset($vals['isNewRedis'])){
				
				$this->isNewRedis = $vals['isNewRedis'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			$input->readI32($this->startDays); 
			
		}
		
		
		
		
		if(true) {
			
			$input->readI32($this->endDays); 
			
		}
		
		
		
		
		if(true) {
			
			$input->readBool($this->isNewRedis);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		$xfer += $output->writeFieldBegin('startDays');
		$xfer += $output->writeI32($this->startDays);
		
		$xfer += $output->writeFieldEnd();
		
		$xfer += $output->writeFieldBegin('endDays');
		$xfer += $output->writeI32($this->endDays);
		
		$xfer += $output->writeFieldEnd();
		
		if($this->isNewRedis !== null) {
			
			$xfer += $output->writeFieldBegin('isNewRedis');
			$xfer += $output->writeBool($this->isNewRedis);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class InventoryOccupiedOrderSyncService_healthCheck_args {
	
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




class InventoryOccupiedOrderSyncService_syncAllDeductOrderToRedis_args {
	
	static $_TSPEC;
	public $isCluster = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'isCluster'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['isCluster'])){
				
				$this->isCluster = $vals['isCluster'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			$input->readBool($this->isCluster);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->isCluster !== null) {
			
			$xfer += $output->writeFieldBegin('isCluster');
			$xfer += $output->writeBool($this->isCluster);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class InventoryOccupiedOrderSyncService_syncAllOccupiedOrderToRedis_args {
	
	static $_TSPEC;
	public $isCluster = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'isCluster'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['isCluster'])){
				
				$this->isCluster = $vals['isCluster'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			$input->readBool($this->isCluster);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->isCluster !== null) {
			
			$xfer += $output->writeFieldBegin('isCluster');
			$xfer += $output->writeBool($this->isCluster);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class InventoryOccupiedOrderSyncService_syncIncDeductOrderToRedis_args {
	
	static $_TSPEC;
	public $startIndex = null;
	public $isCluster = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'startIndex'
			),
			2 => array(
			'var' => 'isCluster'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['startIndex'])){
				
				$this->startIndex = $vals['startIndex'];
			}
			
			
			if (isset($vals['isCluster'])){
				
				$this->isCluster = $vals['isCluster'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			$input->readI64($this->startIndex); 
			
		}
		
		
		
		
		if(true) {
			
			$input->readBool($this->isCluster);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		$xfer += $output->writeFieldBegin('startIndex');
		$xfer += $output->writeI64($this->startIndex);
		
		$xfer += $output->writeFieldEnd();
		
		if($this->isCluster !== null) {
			
			$xfer += $output->writeFieldBegin('isCluster');
			$xfer += $output->writeBool($this->isCluster);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class InventoryOccupiedOrderSyncService_syncIncOccupiedOrderToRedis_args {
	
	static $_TSPEC;
	public $startIndex = null;
	public $isCluster = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'startIndex'
			),
			2 => array(
			'var' => 'isCluster'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['startIndex'])){
				
				$this->startIndex = $vals['startIndex'];
			}
			
			
			if (isset($vals['isCluster'])){
				
				$this->isCluster = $vals['isCluster'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			$input->readI64($this->startIndex); 
			
		}
		
		
		
		
		if(true) {
			
			$input->readBool($this->isCluster);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		$xfer += $output->writeFieldBegin('startIndex');
		$xfer += $output->writeI64($this->startIndex);
		
		$xfer += $output->writeFieldEnd();
		
		if($this->isCluster !== null) {
			
			$xfer += $output->writeFieldBegin('isCluster');
			$xfer += $output->writeBool($this->isCluster);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class InventoryOccupiedOrderSyncService_delDeductOrderRedisKey_result {
	
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




class InventoryOccupiedOrderSyncService_delExpiredOccupiedOrdersFromRedis_result {
	
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




class InventoryOccupiedOrderSyncService_delOccupiedOrdersFromRedis_result {
	
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




class InventoryOccupiedOrderSyncService_healthCheck_result {
	
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




class InventoryOccupiedOrderSyncService_syncAllDeductOrderToRedis_result {
	
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




class InventoryOccupiedOrderSyncService_syncAllOccupiedOrderToRedis_result {
	
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




class InventoryOccupiedOrderSyncService_syncIncDeductOrderToRedis_result {
	
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




class InventoryOccupiedOrderSyncService_syncIncOccupiedOrderToRedis_result {
	
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