<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\vis\order\manage\service\pickmanage;
interface PickManageServiceIf{
	
	
	public function getPickOrderList(\com\vip\vis\order\manage\service\pickmanage\PickOrderParam $pickOrderParam);
	
	public function healthCheck();
	
	public function selectPickWarehouseByPickNos(\com\vip\vis\order\manage\service\pickmanage\PickNoListParam $param);
	
}

class _PickManageServiceClient extends \Osp\Base\OspStub implements \com\vip\vis\order\manage\service\pickmanage\PickManageServiceIf{
	
	public function __construct(){
		
		parent::__construct("com.vip.vis.order.manage.service.pickmanage.PickManageService", "1.0.0");
	}
	
	
	public function getPickOrderList(\com\vip\vis\order\manage\service\pickmanage\PickOrderParam $pickOrderParam){
		
		$this->send_getPickOrderList( $pickOrderParam);
		return $this->recv_getPickOrderList();
	}
	
	public function send_getPickOrderList(\com\vip\vis\order\manage\service\pickmanage\PickOrderParam $pickOrderParam){
		
		$this->initInvocation("getPickOrderList");
		$args = new \com\vip\vis\order\manage\service\pickmanage\PickManageService_getPickOrderList_args();
		
		$args->pickOrderParam = $pickOrderParam;
		
		$this->send_base($args);
	}
	
	public function recv_getPickOrderList(){
		
		$result = new \com\vip\vis\order\manage\service\pickmanage\PickManageService_getPickOrderList_result();
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
		$args = new \com\vip\vis\order\manage\service\pickmanage\PickManageService_healthCheck_args();
		
		$this->send_base($args);
	}
	
	public function recv_healthCheck(){
		
		$result = new \com\vip\vis\order\manage\service\pickmanage\PickManageService_healthCheck_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function selectPickWarehouseByPickNos(\com\vip\vis\order\manage\service\pickmanage\PickNoListParam $param){
		
		$this->send_selectPickWarehouseByPickNos( $param);
		return $this->recv_selectPickWarehouseByPickNos();
	}
	
	public function send_selectPickWarehouseByPickNos(\com\vip\vis\order\manage\service\pickmanage\PickNoListParam $param){
		
		$this->initInvocation("selectPickWarehouseByPickNos");
		$args = new \com\vip\vis\order\manage\service\pickmanage\PickManageService_selectPickWarehouseByPickNos_args();
		
		$args->param = $param;
		
		$this->send_base($args);
	}
	
	public function recv_selectPickWarehouseByPickNos(){
		
		$result = new \com\vip\vis\order\manage\service\pickmanage\PickManageService_selectPickWarehouseByPickNos_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
}




class PickManageService_getPickOrderList_args {
	
	static $_TSPEC;
	public $pickOrderParam = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'pickOrderParam'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['pickOrderParam'])){
				
				$this->pickOrderParam = $vals['pickOrderParam'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->pickOrderParam = new \com\vip\vis\order\manage\service\pickmanage\PickOrderParam();
			$this->pickOrderParam->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->pickOrderParam !== null) {
			
			$xfer += $output->writeFieldBegin('pickOrderParam');
			
			if (!is_object($this->pickOrderParam)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->pickOrderParam->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class PickManageService_healthCheck_args {
	
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




class PickManageService_selectPickWarehouseByPickNos_args {
	
	static $_TSPEC;
	public $param = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'param'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['param'])){
				
				$this->param = $vals['param'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->param = new \com\vip\vis\order\manage\service\pickmanage\PickNoListParam();
			$this->param->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->param !== null) {
			
			$xfer += $output->writeFieldBegin('param');
			
			if (!is_object($this->param)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->param->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class PickManageService_getPickOrderList_result {
	
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
			
			
			$this->success = new \com\vip\vis\order\manage\service\pickmanage\PickOrderResult();
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




class PickManageService_healthCheck_result {
	
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




class PickManageService_selectPickWarehouseByPickNos_result {
	
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
			
			$input->readString($this->success);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			$xfer += $output->writeString($this->success);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




?>