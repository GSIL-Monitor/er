<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\common\pojo\order\vo;

class OrderInstalmentVO {
	
	static $_TSPEC;
	public $order = null;
	public $orderPayInstalment = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'order'
			),
			2 => array(
			'var' => 'orderPayInstalment'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['order'])){
				
				$this->order = $vals['order'];
			}
			
			
			if (isset($vals['orderPayInstalment'])){
				
				$this->orderPayInstalment = $vals['orderPayInstalment'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'OrderInstalmentVO';
	}
	
	public function read($input){
		
		$input->readStructBegin();
		while(true){
			
			$schemeField = $input->readFieldBegin();
			if ($schemeField == null) break;
			$needSkip = true;
			
			
			if ("order" == $schemeField){
				
				$needSkip = false;
				
				$this->order = new \com\vip\order\common\pojo\order\vo\OrderVO();
				$this->order->read($input);
				
			}
			
			
			
			
			if ("orderPayInstalment" == $schemeField){
				
				$needSkip = false;
				
				$this->orderPayInstalment = new \com\vip\order\common\pojo\order\vo\OrderPayInstalmentVO();
				$this->orderPayInstalment->read($input);
				
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
		
		if($this->order !== null) {
			
			$xfer += $output->writeFieldBegin('order');
			
			if (!is_object($this->order)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->order->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderPayInstalment !== null) {
			
			$xfer += $output->writeFieldBegin('orderPayInstalment');
			
			if (!is_object($this->orderPayInstalment)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->orderPayInstalment->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}

?>