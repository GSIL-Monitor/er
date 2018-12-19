<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace vipapis\schedule;
interface ScheduleServiceIf{
	
	
	public function getSchedules( $warehouse, $start_date, $end_date, $schedule_id, $channel_id, $page, $limit);
	
	public function healthCheck();
	
}

class _ScheduleServiceClient extends \Osp\Base\OspStub implements \vipapis\schedule\ScheduleServiceIf{
	
	public function __construct(){
		
		parent::__construct("vipapis.schedule.ScheduleService", "1.0.1");
	}
	
	
	public function getSchedules( $warehouse, $start_date, $end_date, $schedule_id, $channel_id, $page, $limit){
		
		$this->send_getSchedules( $warehouse, $start_date, $end_date, $schedule_id, $channel_id, $page, $limit);
		return $this->recv_getSchedules();
	}
	
	public function send_getSchedules( $warehouse, $start_date, $end_date, $schedule_id, $channel_id, $page, $limit){
		
		$this->initInvocation("getSchedules");
		$args = new \vipapis\schedule\ScheduleService_getSchedules_args();
		
		$args->warehouse = $warehouse;
		
		$args->start_date = $start_date;
		
		$args->end_date = $end_date;
		
		$args->schedule_id = $schedule_id;
		
		$args->channel_id = $channel_id;
		
		$args->page = $page;
		
		$args->limit = $limit;
		
		$this->send_base($args);
	}
	
	public function recv_getSchedules(){
		
		$result = new \vipapis\schedule\ScheduleService_getSchedules_result();
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
		$args = new \vipapis\schedule\ScheduleService_healthCheck_args();
		
		$this->send_base($args);
	}
	
	public function recv_healthCheck(){
		
		$result = new \vipapis\schedule\ScheduleService_healthCheck_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
}




class ScheduleService_getSchedules_args {
	
	static $_TSPEC;
	public $warehouse = null;
	public $start_date = null;
	public $end_date = null;
	public $schedule_id = null;
	public $channel_id = null;
	public $page = null;
	public $limit = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'warehouse'
			),
			2 => array(
			'var' => 'start_date'
			),
			3 => array(
			'var' => 'end_date'
			),
			4 => array(
			'var' => 'schedule_id'
			),
			5 => array(
			'var' => 'channel_id'
			),
			6 => array(
			'var' => 'page'
			),
			7 => array(
			'var' => 'limit'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['warehouse'])){
				
				$this->warehouse = $vals['warehouse'];
			}
			
			
			if (isset($vals['start_date'])){
				
				$this->start_date = $vals['start_date'];
			}
			
			
			if (isset($vals['end_date'])){
				
				$this->end_date = $vals['end_date'];
			}
			
			
			if (isset($vals['schedule_id'])){
				
				$this->schedule_id = $vals['schedule_id'];
			}
			
			
			if (isset($vals['channel_id'])){
				
				$this->channel_id = $vals['channel_id'];
			}
			
			
			if (isset($vals['page'])){
				
				$this->page = $vals['page'];
			}
			
			
			if (isset($vals['limit'])){
				
				$this->limit = $vals['limit'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$names = \vipapis\common\Warehouse::$__names;
			$name = null;
			$input->readString($name);
			foreach ($names as $k => $v){
				
				if($name == $v){
					
					$this->warehouse = $k;
					break;
				}
				
			}
			
			
		}
		
		
		
		
		if(true) {
			
			$input->readString($this->start_date);
			
		}
		
		
		
		
		if(true) {
			
			$input->readString($this->end_date);
			
		}
		
		
		
		
		if(true) {
			
			$input->readString($this->schedule_id);
			
		}
		
		
		
		
		if(true) {
			
			$input->readString($this->channel_id);
			
		}
		
		
		
		
		if(true) {
			
			$input->readI32($this->page); 
			
		}
		
		
		
		
		if(true) {
			
			$input->readI32($this->limit); 
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->warehouse !== null) {
			
			$xfer += $output->writeFieldBegin('warehouse');
			
			$em = new \vipapis\common\Warehouse; 
			$output->writeString($em::$__names[$this->warehouse]);  
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldBegin('start_date');
		$xfer += $output->writeString($this->start_date);
		
		$xfer += $output->writeFieldEnd();
		
		$xfer += $output->writeFieldBegin('end_date');
		$xfer += $output->writeString($this->end_date);
		
		$xfer += $output->writeFieldEnd();
		
		if($this->schedule_id !== null) {
			
			$xfer += $output->writeFieldBegin('schedule_id');
			$xfer += $output->writeString($this->schedule_id);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->channel_id !== null) {
			
			$xfer += $output->writeFieldBegin('channel_id');
			$xfer += $output->writeString($this->channel_id);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->page !== null) {
			
			$xfer += $output->writeFieldBegin('page');
			$xfer += $output->writeI32($this->page);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->limit !== null) {
			
			$xfer += $output->writeFieldBegin('limit');
			$xfer += $output->writeI32($this->limit);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class ScheduleService_healthCheck_args {
	
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




class ScheduleService_getSchedules_result {
	
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
			
			
			$this->success = new \vipapis\schedule\GetScheduleListResponse();
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




class ScheduleService_healthCheck_result {
	
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




?>