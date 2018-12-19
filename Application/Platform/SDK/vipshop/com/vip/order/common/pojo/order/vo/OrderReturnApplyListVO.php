<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\common\pojo\order\vo;

class OrderReturnApplyListVO {
	
	static $_TSPEC;
	public $orderReturnApply = null;
	public $orderReturnReceiveAddr = null;
	public $orderReturnTransportInfo = null;
	public $orderReturnGoods = null;
	public $orderReturnPackageInfo = null;
	public $orderReturnRefundDetailsList = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'orderReturnApply'
			),
			2 => array(
			'var' => 'orderReturnReceiveAddr'
			),
			3 => array(
			'var' => 'orderReturnTransportInfo'
			),
			4 => array(
			'var' => 'orderReturnGoods'
			),
			5 => array(
			'var' => 'orderReturnPackageInfo'
			),
			6 => array(
			'var' => 'orderReturnRefundDetailsList'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['orderReturnApply'])){
				
				$this->orderReturnApply = $vals['orderReturnApply'];
			}
			
			
			if (isset($vals['orderReturnReceiveAddr'])){
				
				$this->orderReturnReceiveAddr = $vals['orderReturnReceiveAddr'];
			}
			
			
			if (isset($vals['orderReturnTransportInfo'])){
				
				$this->orderReturnTransportInfo = $vals['orderReturnTransportInfo'];
			}
			
			
			if (isset($vals['orderReturnGoods'])){
				
				$this->orderReturnGoods = $vals['orderReturnGoods'];
			}
			
			
			if (isset($vals['orderReturnPackageInfo'])){
				
				$this->orderReturnPackageInfo = $vals['orderReturnPackageInfo'];
			}
			
			
			if (isset($vals['orderReturnRefundDetailsList'])){
				
				$this->orderReturnRefundDetailsList = $vals['orderReturnRefundDetailsList'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'OrderReturnApplyListVO';
	}
	
	public function read($input){
		
		$input->readStructBegin();
		while(true){
			
			$schemeField = $input->readFieldBegin();
			if ($schemeField == null) break;
			$needSkip = true;
			
			
			if ("orderReturnApply" == $schemeField){
				
				$needSkip = false;
				
				$this->orderReturnApply = new \com\vip\order\common\pojo\order\vo\OrderReturnApplyVO();
				$this->orderReturnApply->read($input);
				
			}
			
			
			
			
			if ("orderReturnReceiveAddr" == $schemeField){
				
				$needSkip = false;
				
				$this->orderReturnReceiveAddr = new \com\vip\order\common\pojo\order\vo\OrderReceiveAddrVO();
				$this->orderReturnReceiveAddr->read($input);
				
			}
			
			
			
			
			if ("orderReturnTransportInfo" == $schemeField){
				
				$needSkip = false;
				
				$this->orderReturnTransportInfo = new \com\vip\order\common\pojo\order\vo\OrderReturnTransportInfoVO();
				$this->orderReturnTransportInfo->read($input);
				
			}
			
			
			
			
			if ("orderReturnGoods" == $schemeField){
				
				$needSkip = false;
				
				$this->orderReturnGoods = array();
				$_size1 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem1 = null;
						
						$elem1 = new \com\vip\order\common\pojo\order\vo\OrderReturnGoodsVO();
						$elem1->read($input);
						
						$this->orderReturnGoods[$_size1++] = $elem1;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readListEnd();
				
			}
			
			
			
			
			if ("orderReturnPackageInfo" == $schemeField){
				
				$needSkip = false;
				
				$this->orderReturnPackageInfo = array();
				$_size2 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem2 = null;
						
						$elem2 = new \com\vip\order\common\pojo\order\vo\OrderReturnPackageInfoVO();
						$elem2->read($input);
						
						$this->orderReturnPackageInfo[$_size2++] = $elem2;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readListEnd();
				
			}
			
			
			
			
			if ("orderReturnRefundDetailsList" == $schemeField){
				
				$needSkip = false;
				
				$this->orderReturnRefundDetailsList = array();
				$_size3 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem3 = null;
						
						$elem3 = new \com\vip\order\common\pojo\order\vo\OrderRefundDetailsVO();
						$elem3->read($input);
						
						$this->orderReturnRefundDetailsList[$_size3++] = $elem3;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readListEnd();
				
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
		
		if($this->orderReturnApply !== null) {
			
			$xfer += $output->writeFieldBegin('orderReturnApply');
			
			if (!is_object($this->orderReturnApply)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->orderReturnApply->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderReturnReceiveAddr !== null) {
			
			$xfer += $output->writeFieldBegin('orderReturnReceiveAddr');
			
			if (!is_object($this->orderReturnReceiveAddr)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->orderReturnReceiveAddr->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderReturnTransportInfo !== null) {
			
			$xfer += $output->writeFieldBegin('orderReturnTransportInfo');
			
			if (!is_object($this->orderReturnTransportInfo)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->orderReturnTransportInfo->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderReturnGoods !== null) {
			
			$xfer += $output->writeFieldBegin('orderReturnGoods');
			
			if (!is_array($this->orderReturnGoods)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeListBegin();
			foreach ($this->orderReturnGoods as $iter0){
				
				
				if (!is_object($iter0)) {
					
					throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
				}
				
				$xfer += $iter0->write($output);
				
			}
			
			$output->writeListEnd();
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderReturnPackageInfo !== null) {
			
			$xfer += $output->writeFieldBegin('orderReturnPackageInfo');
			
			if (!is_array($this->orderReturnPackageInfo)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeListBegin();
			foreach ($this->orderReturnPackageInfo as $iter0){
				
				
				if (!is_object($iter0)) {
					
					throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
				}
				
				$xfer += $iter0->write($output);
				
			}
			
			$output->writeListEnd();
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderReturnRefundDetailsList !== null) {
			
			$xfer += $output->writeFieldBegin('orderReturnRefundDetailsList');
			
			if (!is_array($this->orderReturnRefundDetailsList)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeListBegin();
			foreach ($this->orderReturnRefundDetailsList as $iter0){
				
				
				if (!is_object($iter0)) {
					
					throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
				}
				
				$xfer += $iter0->write($output);
				
			}
			
			$output->writeListEnd();
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}

?>