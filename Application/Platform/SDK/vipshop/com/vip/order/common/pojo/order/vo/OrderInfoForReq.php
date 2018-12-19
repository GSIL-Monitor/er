<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\common\pojo\order\vo;

class OrderInfoForReq {
	
	static $_TSPEC;
	public $order = null;
	public $orderPayAndDiscount = null;
	public $orderPayDetail = null;
	public $orderPayInstalment = null;
	public $orderGoodsList = null;
	public $orderActive = null;
	public $orderLog = null;
	public $prepayOrderExtendVO = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'order'
			),
			2 => array(
			'var' => 'orderPayAndDiscount'
			),
			3 => array(
			'var' => 'orderPayDetail'
			),
			4 => array(
			'var' => 'orderPayInstalment'
			),
			5 => array(
			'var' => 'orderGoodsList'
			),
			6 => array(
			'var' => 'orderActive'
			),
			7 => array(
			'var' => 'orderLog'
			),
			8 => array(
			'var' => 'prepayOrderExtendVO'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['order'])){
				
				$this->order = $vals['order'];
			}
			
			
			if (isset($vals['orderPayAndDiscount'])){
				
				$this->orderPayAndDiscount = $vals['orderPayAndDiscount'];
			}
			
			
			if (isset($vals['orderPayDetail'])){
				
				$this->orderPayDetail = $vals['orderPayDetail'];
			}
			
			
			if (isset($vals['orderPayInstalment'])){
				
				$this->orderPayInstalment = $vals['orderPayInstalment'];
			}
			
			
			if (isset($vals['orderGoodsList'])){
				
				$this->orderGoodsList = $vals['orderGoodsList'];
			}
			
			
			if (isset($vals['orderActive'])){
				
				$this->orderActive = $vals['orderActive'];
			}
			
			
			if (isset($vals['orderLog'])){
				
				$this->orderLog = $vals['orderLog'];
			}
			
			
			if (isset($vals['prepayOrderExtendVO'])){
				
				$this->prepayOrderExtendVO = $vals['prepayOrderExtendVO'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'OrderInfoForReq';
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
			
			
			
			
			if ("orderPayAndDiscount" == $schemeField){
				
				$needSkip = false;
				
				$this->orderPayAndDiscount = new \com\vip\order\common\pojo\order\vo\OrderPayAndDiscountVO();
				$this->orderPayAndDiscount->read($input);
				
			}
			
			
			
			
			if ("orderPayDetail" == $schemeField){
				
				$needSkip = false;
				
				$this->orderPayDetail = new \com\vip\order\common\pojo\order\vo\OrderPayDetailVO();
				$this->orderPayDetail->read($input);
				
			}
			
			
			
			
			if ("orderPayInstalment" == $schemeField){
				
				$needSkip = false;
				
				$this->orderPayInstalment = new \com\vip\order\common\pojo\order\vo\OrderPayInstalmentVO();
				$this->orderPayInstalment->read($input);
				
			}
			
			
			
			
			if ("orderGoodsList" == $schemeField){
				
				$needSkip = false;
				
				$this->orderGoodsList = array();
				$_size0 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem0 = null;
						
						$elem0 = new \com\vip\order\common\pojo\order\vo\OrderGoodsVO();
						$elem0->read($input);
						
						$this->orderGoodsList[$_size0++] = $elem0;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readListEnd();
				
			}
			
			
			
			
			if ("orderActive" == $schemeField){
				
				$needSkip = false;
				
				$this->orderActive = new \com\vip\order\common\pojo\order\vo\OrderActiveVO();
				$this->orderActive->read($input);
				
			}
			
			
			
			
			if ("orderLog" == $schemeField){
				
				$needSkip = false;
				
				$this->orderLog = new \com\vip\order\common\pojo\order\vo\OrderLogsVO();
				$this->orderLog->read($input);
				
			}
			
			
			
			
			if ("prepayOrderExtendVO" == $schemeField){
				
				$needSkip = false;
				
				$this->prepayOrderExtendVO = new \com\vip\order\common\pojo\order\vo\PrepayOrderExtendVO();
				$this->prepayOrderExtendVO->read($input);
				
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
		
		
		if($this->orderPayAndDiscount !== null) {
			
			$xfer += $output->writeFieldBegin('orderPayAndDiscount');
			
			if (!is_object($this->orderPayAndDiscount)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->orderPayAndDiscount->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderPayDetail !== null) {
			
			$xfer += $output->writeFieldBegin('orderPayDetail');
			
			if (!is_object($this->orderPayDetail)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->orderPayDetail->write($output);
			
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
		
		
		if($this->orderGoodsList !== null) {
			
			$xfer += $output->writeFieldBegin('orderGoodsList');
			
			if (!is_array($this->orderGoodsList)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeListBegin();
			foreach ($this->orderGoodsList as $iter0){
				
				
				if (!is_object($iter0)) {
					
					throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
				}
				
				$xfer += $iter0->write($output);
				
			}
			
			$output->writeListEnd();
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderActive !== null) {
			
			$xfer += $output->writeFieldBegin('orderActive');
			
			if (!is_object($this->orderActive)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->orderActive->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderLog !== null) {
			
			$xfer += $output->writeFieldBegin('orderLog');
			
			if (!is_object($this->orderLog)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->orderLog->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->prepayOrderExtendVO !== null) {
			
			$xfer += $output->writeFieldBegin('prepayOrderExtendVO');
			
			if (!is_object($this->prepayOrderExtendVO)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->prepayOrderExtendVO->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}

?>