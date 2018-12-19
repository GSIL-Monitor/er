<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\common\pojo\order\vo;

class OrderExchangeApplyListVO {
	
	static $_TSPEC;
	public $orderExchangeApply = null;
	public $orderExchangeReceiveAddr = null;
	public $orderExchangeReturnTransportInfo = null;
	public $orderExchangeGoods = null;
	public $orderExchangeReturnPackageInfo = null;
	public $exchangeBackFee = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'orderExchangeApply'
			),
			2 => array(
			'var' => 'orderExchangeReceiveAddr'
			),
			3 => array(
			'var' => 'orderExchangeReturnTransportInfo'
			),
			4 => array(
			'var' => 'orderExchangeGoods'
			),
			5 => array(
			'var' => 'orderExchangeReturnPackageInfo'
			),
			6 => array(
			'var' => 'exchangeBackFee'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['orderExchangeApply'])){
				
				$this->orderExchangeApply = $vals['orderExchangeApply'];
			}
			
			
			if (isset($vals['orderExchangeReceiveAddr'])){
				
				$this->orderExchangeReceiveAddr = $vals['orderExchangeReceiveAddr'];
			}
			
			
			if (isset($vals['orderExchangeReturnTransportInfo'])){
				
				$this->orderExchangeReturnTransportInfo = $vals['orderExchangeReturnTransportInfo'];
			}
			
			
			if (isset($vals['orderExchangeGoods'])){
				
				$this->orderExchangeGoods = $vals['orderExchangeGoods'];
			}
			
			
			if (isset($vals['orderExchangeReturnPackageInfo'])){
				
				$this->orderExchangeReturnPackageInfo = $vals['orderExchangeReturnPackageInfo'];
			}
			
			
			if (isset($vals['exchangeBackFee'])){
				
				$this->exchangeBackFee = $vals['exchangeBackFee'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'OrderExchangeApplyListVO';
	}
	
	public function read($input){
		
		$input->readStructBegin();
		while(true){
			
			$schemeField = $input->readFieldBegin();
			if ($schemeField == null) break;
			$needSkip = true;
			
			
			if ("orderExchangeApply" == $schemeField){
				
				$needSkip = false;
				
				$this->orderExchangeApply = new \com\vip\order\common\pojo\order\vo\OrderExchangeApplyVO();
				$this->orderExchangeApply->read($input);
				
			}
			
			
			
			
			if ("orderExchangeReceiveAddr" == $schemeField){
				
				$needSkip = false;
				
				$this->orderExchangeReceiveAddr = new \com\vip\order\common\pojo\order\vo\OrderReceiveAddrVO();
				$this->orderExchangeReceiveAddr->read($input);
				
			}
			
			
			
			
			if ("orderExchangeReturnTransportInfo" == $schemeField){
				
				$needSkip = false;
				
				$this->orderExchangeReturnTransportInfo = new \com\vip\order\common\pojo\order\vo\OrderReturnTransportInfoVO();
				$this->orderExchangeReturnTransportInfo->read($input);
				
			}
			
			
			
			
			if ("orderExchangeGoods" == $schemeField){
				
				$needSkip = false;
				
				$this->orderExchangeGoods = array();
				$_size0 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem0 = null;
						
						$elem0 = new \com\vip\order\common\pojo\order\vo\OrderExchangeGoodsVO();
						$elem0->read($input);
						
						$this->orderExchangeGoods[$_size0++] = $elem0;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readListEnd();
				
			}
			
			
			
			
			if ("orderExchangeReturnPackageInfo" == $schemeField){
				
				$needSkip = false;
				
				$this->orderExchangeReturnPackageInfo = array();
				$_size1 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem1 = null;
						
						$elem1 = new \com\vip\order\common\pojo\order\vo\OrderReturnPackageInfoVO();
						$elem1->read($input);
						
						$this->orderExchangeReturnPackageInfo[$_size1++] = $elem1;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readListEnd();
				
			}
			
			
			
			
			if ("exchangeBackFee" == $schemeField){
				
				$needSkip = false;
				
				$this->exchangeBackFee = new \com\vip\order\common\pojo\order\vo\ExchangeBackFeeVO();
				$this->exchangeBackFee->read($input);
				
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
		
		if($this->orderExchangeApply !== null) {
			
			$xfer += $output->writeFieldBegin('orderExchangeApply');
			
			if (!is_object($this->orderExchangeApply)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->orderExchangeApply->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderExchangeReceiveAddr !== null) {
			
			$xfer += $output->writeFieldBegin('orderExchangeReceiveAddr');
			
			if (!is_object($this->orderExchangeReceiveAddr)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->orderExchangeReceiveAddr->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderExchangeReturnTransportInfo !== null) {
			
			$xfer += $output->writeFieldBegin('orderExchangeReturnTransportInfo');
			
			if (!is_object($this->orderExchangeReturnTransportInfo)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->orderExchangeReturnTransportInfo->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderExchangeGoods !== null) {
			
			$xfer += $output->writeFieldBegin('orderExchangeGoods');
			
			if (!is_array($this->orderExchangeGoods)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeListBegin();
			foreach ($this->orderExchangeGoods as $iter0){
				
				
				if (!is_object($iter0)) {
					
					throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
				}
				
				$xfer += $iter0->write($output);
				
			}
			
			$output->writeListEnd();
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderExchangeReturnPackageInfo !== null) {
			
			$xfer += $output->writeFieldBegin('orderExchangeReturnPackageInfo');
			
			if (!is_array($this->orderExchangeReturnPackageInfo)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeListBegin();
			foreach ($this->orderExchangeReturnPackageInfo as $iter0){
				
				
				if (!is_object($iter0)) {
					
					throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
				}
				
				$xfer += $iter0->write($output);
				
			}
			
			$output->writeListEnd();
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->exchangeBackFee !== null) {
			
			$xfer += $output->writeFieldBegin('exchangeBackFee');
			
			if (!is_object($this->exchangeBackFee)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->exchangeBackFee->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}

?>