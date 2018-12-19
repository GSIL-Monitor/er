<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace vipapis\vlg\wms;
interface OutTpsServiceIf{
	
	
	public function callbackOutShipContainerResult( $sysKey, $warehouse, $msgIds);
	
	public function getOutReturnOrderPackageResult( $appName, $maxId, $maxSize);
	
	public function getOutShipContainerResult( $sysKey, $warehouse, $pageSize);
	
	public function healthCheck();
	
	public function pushOutReturnOrderPackage(\com\vip\sce\vlg\osp\wms\service\OutReturnOrderPackage $outReturnOrderPackage);
	
	public function pushOutShipContainer( $sysKey, $warehouse, $outShipContainers);
	
}

class _OutTpsServiceClient extends \Osp\Base\OspStub implements \vipapis\vlg\wms\OutTpsServiceIf{
	
	public function __construct(){
		
		parent::__construct("vipapis.vlg.wms.OutTpsService", "1.0.0");
	}
	
	
	public function callbackOutShipContainerResult( $sysKey, $warehouse, $msgIds){
		
		$this->send_callbackOutShipContainerResult( $sysKey, $warehouse, $msgIds);
		return $this->recv_callbackOutShipContainerResult();
	}
	
	public function send_callbackOutShipContainerResult( $sysKey, $warehouse, $msgIds){
		
		$this->initInvocation("callbackOutShipContainerResult");
		$args = new \vipapis\vlg\wms\OutTpsService_callbackOutShipContainerResult_args();
		
		$args->sysKey = $sysKey;
		
		$args->warehouse = $warehouse;
		
		$args->msgIds = $msgIds;
		
		$this->send_base($args);
	}
	
	public function recv_callbackOutShipContainerResult(){
		
		$result = new \vipapis\vlg\wms\OutTpsService_callbackOutShipContainerResult_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function getOutReturnOrderPackageResult( $appName, $maxId, $maxSize){
		
		$this->send_getOutReturnOrderPackageResult( $appName, $maxId, $maxSize);
		return $this->recv_getOutReturnOrderPackageResult();
	}
	
	public function send_getOutReturnOrderPackageResult( $appName, $maxId, $maxSize){
		
		$this->initInvocation("getOutReturnOrderPackageResult");
		$args = new \vipapis\vlg\wms\OutTpsService_getOutReturnOrderPackageResult_args();
		
		$args->appName = $appName;
		
		$args->maxId = $maxId;
		
		$args->maxSize = $maxSize;
		
		$this->send_base($args);
	}
	
	public function recv_getOutReturnOrderPackageResult(){
		
		$result = new \vipapis\vlg\wms\OutTpsService_getOutReturnOrderPackageResult_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function getOutShipContainerResult( $sysKey, $warehouse, $pageSize){
		
		$this->send_getOutShipContainerResult( $sysKey, $warehouse, $pageSize);
		return $this->recv_getOutShipContainerResult();
	}
	
	public function send_getOutShipContainerResult( $sysKey, $warehouse, $pageSize){
		
		$this->initInvocation("getOutShipContainerResult");
		$args = new \vipapis\vlg\wms\OutTpsService_getOutShipContainerResult_args();
		
		$args->sysKey = $sysKey;
		
		$args->warehouse = $warehouse;
		
		$args->pageSize = $pageSize;
		
		$this->send_base($args);
	}
	
	public function recv_getOutShipContainerResult(){
		
		$result = new \vipapis\vlg\wms\OutTpsService_getOutShipContainerResult_result();
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
		$args = new \vipapis\vlg\wms\OutTpsService_healthCheck_args();
		
		$this->send_base($args);
	}
	
	public function recv_healthCheck(){
		
		$result = new \vipapis\vlg\wms\OutTpsService_healthCheck_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function pushOutReturnOrderPackage(\com\vip\sce\vlg\osp\wms\service\OutReturnOrderPackage $outReturnOrderPackage){
		
		$this->send_pushOutReturnOrderPackage( $outReturnOrderPackage);
		return $this->recv_pushOutReturnOrderPackage();
	}
	
	public function send_pushOutReturnOrderPackage(\com\vip\sce\vlg\osp\wms\service\OutReturnOrderPackage $outReturnOrderPackage){
		
		$this->initInvocation("pushOutReturnOrderPackage");
		$args = new \vipapis\vlg\wms\OutTpsService_pushOutReturnOrderPackage_args();
		
		$args->outReturnOrderPackage = $outReturnOrderPackage;
		
		$this->send_base($args);
	}
	
	public function recv_pushOutReturnOrderPackage(){
		
		$result = new \vipapis\vlg\wms\OutTpsService_pushOutReturnOrderPackage_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function pushOutShipContainer( $sysKey, $warehouse, $outShipContainers){
		
		$this->send_pushOutShipContainer( $sysKey, $warehouse, $outShipContainers);
		return $this->recv_pushOutShipContainer();
	}
	
	public function send_pushOutShipContainer( $sysKey, $warehouse, $outShipContainers){
		
		$this->initInvocation("pushOutShipContainer");
		$args = new \vipapis\vlg\wms\OutTpsService_pushOutShipContainer_args();
		
		$args->sysKey = $sysKey;
		
		$args->warehouse = $warehouse;
		
		$args->outShipContainers = $outShipContainers;
		
		$this->send_base($args);
	}
	
	public function recv_pushOutShipContainer(){
		
		$result = new \vipapis\vlg\wms\OutTpsService_pushOutShipContainer_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
}




class OutTpsService_callbackOutShipContainerResult_args {
	
	static $_TSPEC;
	public $sysKey = null;
	public $warehouse = null;
	public $msgIds = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'sysKey'
			),
			2 => array(
			'var' => 'warehouse'
			),
			3 => array(
			'var' => 'msgIds'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['sysKey'])){
				
				$this->sysKey = $vals['sysKey'];
			}
			
			
			if (isset($vals['warehouse'])){
				
				$this->warehouse = $vals['warehouse'];
			}
			
			
			if (isset($vals['msgIds'])){
				
				$this->msgIds = $vals['msgIds'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			$input->readString($this->sysKey);
			
		}
		
		
		
		
		if(true) {
			
			$input->readString($this->warehouse);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->msgIds = array();
			$_size1 = 0;
			$input->readListBegin();
			while(true){
				
				try{
					
					$elem1 = null;
					$input->readString($elem1);
					
					$this->msgIds[$_size1++] = $elem1;
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
		
		$xfer += $output->writeFieldBegin('sysKey');
		$xfer += $output->writeString($this->sysKey);
		
		$xfer += $output->writeFieldEnd();
		
		$xfer += $output->writeFieldBegin('warehouse');
		$xfer += $output->writeString($this->warehouse);
		
		$xfer += $output->writeFieldEnd();
		
		$xfer += $output->writeFieldBegin('msgIds');
		
		if (!is_array($this->msgIds)){
			
			throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
		}
		
		$output->writeListBegin();
		foreach ($this->msgIds as $iter0){
			
			$xfer += $output->writeString($iter0);
			
		}
		
		$output->writeListEnd();
		
		$xfer += $output->writeFieldEnd();
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OutTpsService_getOutReturnOrderPackageResult_args {
	
	static $_TSPEC;
	public $appName = null;
	public $maxId = null;
	public $maxSize = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'appName'
			),
			2 => array(
			'var' => 'maxId'
			),
			3 => array(
			'var' => 'maxSize'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['appName'])){
				
				$this->appName = $vals['appName'];
			}
			
			
			if (isset($vals['maxId'])){
				
				$this->maxId = $vals['maxId'];
			}
			
			
			if (isset($vals['maxSize'])){
				
				$this->maxSize = $vals['maxSize'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			$input->readString($this->appName);
			
		}
		
		
		
		
		if(true) {
			
			$input->readI64($this->maxId); 
			
		}
		
		
		
		
		if(true) {
			
			$input->readI32($this->maxSize); 
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		$xfer += $output->writeFieldBegin('appName');
		$xfer += $output->writeString($this->appName);
		
		$xfer += $output->writeFieldEnd();
		
		$xfer += $output->writeFieldBegin('maxId');
		$xfer += $output->writeI64($this->maxId);
		
		$xfer += $output->writeFieldEnd();
		
		$xfer += $output->writeFieldBegin('maxSize');
		$xfer += $output->writeI32($this->maxSize);
		
		$xfer += $output->writeFieldEnd();
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OutTpsService_getOutShipContainerResult_args {
	
	static $_TSPEC;
	public $sysKey = null;
	public $warehouse = null;
	public $pageSize = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'sysKey'
			),
			2 => array(
			'var' => 'warehouse'
			),
			3 => array(
			'var' => 'pageSize'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['sysKey'])){
				
				$this->sysKey = $vals['sysKey'];
			}
			
			
			if (isset($vals['warehouse'])){
				
				$this->warehouse = $vals['warehouse'];
			}
			
			
			if (isset($vals['pageSize'])){
				
				$this->pageSize = $vals['pageSize'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			$input->readString($this->sysKey);
			
		}
		
		
		
		
		if(true) {
			
			$input->readString($this->warehouse);
			
		}
		
		
		
		
		if(true) {
			
			$input->readI32($this->pageSize); 
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		$xfer += $output->writeFieldBegin('sysKey');
		$xfer += $output->writeString($this->sysKey);
		
		$xfer += $output->writeFieldEnd();
		
		$xfer += $output->writeFieldBegin('warehouse');
		$xfer += $output->writeString($this->warehouse);
		
		$xfer += $output->writeFieldEnd();
		
		$xfer += $output->writeFieldBegin('pageSize');
		$xfer += $output->writeI32($this->pageSize);
		
		$xfer += $output->writeFieldEnd();
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OutTpsService_healthCheck_args {
	
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




class OutTpsService_pushOutReturnOrderPackage_args {
	
	static $_TSPEC;
	public $outReturnOrderPackage = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'outReturnOrderPackage'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['outReturnOrderPackage'])){
				
				$this->outReturnOrderPackage = $vals['outReturnOrderPackage'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->outReturnOrderPackage = new \com\vip\sce\vlg\osp\wms\service\OutReturnOrderPackage();
			$this->outReturnOrderPackage->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		$xfer += $output->writeFieldBegin('outReturnOrderPackage');
		
		if (!is_object($this->outReturnOrderPackage)) {
			
			throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
		}
		
		$xfer += $this->outReturnOrderPackage->write($output);
		
		$xfer += $output->writeFieldEnd();
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OutTpsService_pushOutShipContainer_args {
	
	static $_TSPEC;
	public $sysKey = null;
	public $warehouse = null;
	public $outShipContainers = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'sysKey'
			),
			2 => array(
			'var' => 'warehouse'
			),
			3 => array(
			'var' => 'outShipContainers'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['sysKey'])){
				
				$this->sysKey = $vals['sysKey'];
			}
			
			
			if (isset($vals['warehouse'])){
				
				$this->warehouse = $vals['warehouse'];
			}
			
			
			if (isset($vals['outShipContainers'])){
				
				$this->outShipContainers = $vals['outShipContainers'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			$input->readString($this->sysKey);
			
		}
		
		
		
		
		if(true) {
			
			$input->readString($this->warehouse);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->outShipContainers = array();
			$_size0 = 0;
			$input->readListBegin();
			while(true){
				
				try{
					
					$elem0 = null;
					
					$elem0 = new \com\vip\sce\vlg\osp\wms\service\OutShipContainer();
					$elem0->read($input);
					
					$this->outShipContainers[$_size0++] = $elem0;
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
		
		$xfer += $output->writeFieldBegin('sysKey');
		$xfer += $output->writeString($this->sysKey);
		
		$xfer += $output->writeFieldEnd();
		
		$xfer += $output->writeFieldBegin('warehouse');
		$xfer += $output->writeString($this->warehouse);
		
		$xfer += $output->writeFieldEnd();
		
		$xfer += $output->writeFieldBegin('outShipContainers');
		
		if (!is_array($this->outShipContainers)){
			
			throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
		}
		
		$output->writeListBegin();
		foreach ($this->outShipContainers as $iter0){
			
			
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




class OutTpsService_callbackOutShipContainerResult_result {
	
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
			
			
			$this->success = new \com\vip\sce\vlg\osp\wms\service\PostResponse();
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




class OutTpsService_getOutReturnOrderPackageResult_result {
	
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
			
			
			$this->success = new \com\vip\sce\vlg\osp\wms\service\GetOutReturnOrderPackageResultResponse();
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




class OutTpsService_getOutShipContainerResult_result {
	
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
			
			
			$this->success = new \com\vip\sce\vlg\osp\wms\service\GetOutShipContainerResultResponse();
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




class OutTpsService_healthCheck_result {
	
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




class OutTpsService_pushOutReturnOrderPackage_result {
	
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
			
			
			$this->success = new \com\vip\sce\vlg\osp\wms\service\PostResponse();
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




class OutTpsService_pushOutShipContainer_result {
	
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
			
			
			$this->success = new \com\vip\sce\vlg\osp\wms\service\PostResponse();
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