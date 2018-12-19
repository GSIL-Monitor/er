<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\common\pojo\order\vo;

class ParentOrderInfoVO {
	
	static $_TSPEC;
	public $parentOrder = null;
	public $parentOrderReceiveAddr = null;
	public $parentOrderPayAndDiscount = null;
	public $parentOrderDetailsSuppInfo = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'parentOrder'
			),
			2 => array(
			'var' => 'parentOrderReceiveAddr'
			),
			3 => array(
			'var' => 'parentOrderPayAndDiscount'
			),
			4 => array(
			'var' => 'parentOrderDetailsSuppInfo'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['parentOrder'])){
				
				$this->parentOrder = $vals['parentOrder'];
			}
			
			
			if (isset($vals['parentOrderReceiveAddr'])){
				
				$this->parentOrderReceiveAddr = $vals['parentOrderReceiveAddr'];
			}
			
			
			if (isset($vals['parentOrderPayAndDiscount'])){
				
				$this->parentOrderPayAndDiscount = $vals['parentOrderPayAndDiscount'];
			}
			
			
			if (isset($vals['parentOrderDetailsSuppInfo'])){
				
				$this->parentOrderDetailsSuppInfo = $vals['parentOrderDetailsSuppInfo'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'ParentOrderInfoVO';
	}
	
	public function read($input){
		
		$input->readStructBegin();
		while(true){
			
			$schemeField = $input->readFieldBegin();
			if ($schemeField == null) break;
			$needSkip = true;
			
			
			if ("parentOrder" == $schemeField){
				
				$needSkip = false;
				
				$this->parentOrder = new \com\vip\order\common\pojo\order\vo\OrderVO();
				$this->parentOrder->read($input);
				
			}
			
			
			
			
			if ("parentOrderReceiveAddr" == $schemeField){
				
				$needSkip = false;
				
				$this->parentOrderReceiveAddr = new \com\vip\order\common\pojo\order\vo\OrderReceiveAddrVO();
				$this->parentOrderReceiveAddr->read($input);
				
			}
			
			
			
			
			if ("parentOrderPayAndDiscount" == $schemeField){
				
				$needSkip = false;
				
				$this->parentOrderPayAndDiscount = new \com\vip\order\common\pojo\order\vo\OrderPayAndDiscountVO();
				$this->parentOrderPayAndDiscount->read($input);
				
			}
			
			
			
			
			if ("parentOrderDetailsSuppInfo" == $schemeField){
				
				$needSkip = false;
				
				$this->parentOrderDetailsSuppInfo = new \com\vip\order\common\pojo\order\vo\OrderDetailsSuppInfoVO();
				$this->parentOrderDetailsSuppInfo->read($input);
				
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
		
		if($this->parentOrder !== null) {
			
			$xfer += $output->writeFieldBegin('parentOrder');
			
			if (!is_object($this->parentOrder)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->parentOrder->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->parentOrderReceiveAddr !== null) {
			
			$xfer += $output->writeFieldBegin('parentOrderReceiveAddr');
			
			if (!is_object($this->parentOrderReceiveAddr)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->parentOrderReceiveAddr->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->parentOrderPayAndDiscount !== null) {
			
			$xfer += $output->writeFieldBegin('parentOrderPayAndDiscount');
			
			if (!is_object($this->parentOrderPayAndDiscount)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->parentOrderPayAndDiscount->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->parentOrderDetailsSuppInfo !== null) {
			
			$xfer += $output->writeFieldBegin('parentOrderDetailsSuppInfo');
			
			if (!is_object($this->parentOrderDetailsSuppInfo)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->parentOrderDetailsSuppInfo->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}

?>