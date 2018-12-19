<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\biz\request;

class ModifyOrderGoodsReq {
	
	static $_TSPEC;
	public $orderCategory = null;
	public $modifyGoodsOrder = null;
	public $goodsInfoList = null;
	public $receiveAddressInfo = null;
	public $pmsInfo = null;
	public $payAndDiscount = null;
	public $invoiceInfo = null;
	public $customerSrc = null;
	public $isCreateNewOrder = null;
	public $isManjianEdit = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'orderCategory'
			),
			2 => array(
			'var' => 'modifyGoodsOrder'
			),
			3 => array(
			'var' => 'goodsInfoList'
			),
			4 => array(
			'var' => 'receiveAddressInfo'
			),
			5 => array(
			'var' => 'pmsInfo'
			),
			6 => array(
			'var' => 'payAndDiscount'
			),
			7 => array(
			'var' => 'invoiceInfo'
			),
			8 => array(
			'var' => 'customerSrc'
			),
			9 => array(
			'var' => 'isCreateNewOrder'
			),
			10 => array(
			'var' => 'isManjianEdit'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['orderCategory'])){
				
				$this->orderCategory = $vals['orderCategory'];
			}
			
			
			if (isset($vals['modifyGoodsOrder'])){
				
				$this->modifyGoodsOrder = $vals['modifyGoodsOrder'];
			}
			
			
			if (isset($vals['goodsInfoList'])){
				
				$this->goodsInfoList = $vals['goodsInfoList'];
			}
			
			
			if (isset($vals['receiveAddressInfo'])){
				
				$this->receiveAddressInfo = $vals['receiveAddressInfo'];
			}
			
			
			if (isset($vals['pmsInfo'])){
				
				$this->pmsInfo = $vals['pmsInfo'];
			}
			
			
			if (isset($vals['payAndDiscount'])){
				
				$this->payAndDiscount = $vals['payAndDiscount'];
			}
			
			
			if (isset($vals['invoiceInfo'])){
				
				$this->invoiceInfo = $vals['invoiceInfo'];
			}
			
			
			if (isset($vals['customerSrc'])){
				
				$this->customerSrc = $vals['customerSrc'];
			}
			
			
			if (isset($vals['isCreateNewOrder'])){
				
				$this->isCreateNewOrder = $vals['isCreateNewOrder'];
			}
			
			
			if (isset($vals['isManjianEdit'])){
				
				$this->isManjianEdit = $vals['isManjianEdit'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'ModifyOrderGoodsReq';
	}
	
	public function read($input){
		
		$input->readStructBegin();
		while(true){
			
			$schemeField = $input->readFieldBegin();
			if ($schemeField == null) break;
			$needSkip = true;
			
			
			if ("orderCategory" == $schemeField){
				
				$needSkip = false;
				$input->readByte($this->orderCategory); 
				
			}
			
			
			
			
			if ("modifyGoodsOrder" == $schemeField){
				
				$needSkip = false;
				
				$this->modifyGoodsOrder = new \com\vip\order\biz\request\ModifyGoodsOrder();
				$this->modifyGoodsOrder->read($input);
				
			}
			
			
			
			
			if ("goodsInfoList" == $schemeField){
				
				$needSkip = false;
				
				$this->goodsInfoList = array();
				$_size0 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem0 = null;
						
						$elem0 = new \com\vip\order\biz\request\NewGoodsInfo();
						$elem0->read($input);
						
						$this->goodsInfoList[$_size0++] = $elem0;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readListEnd();
				
			}
			
			
			
			
			if ("receiveAddressInfo" == $schemeField){
				
				$needSkip = false;
				
				$this->receiveAddressInfo = new \com\vip\order\biz\request\ReceiveAddressInfo();
				$this->receiveAddressInfo->read($input);
				
			}
			
			
			
			
			if ("pmsInfo" == $schemeField){
				
				$needSkip = false;
				
				$this->pmsInfo = new \com\vip\order\biz\request\PmsInfo();
				$this->pmsInfo->read($input);
				
			}
			
			
			
			
			if ("payAndDiscount" == $schemeField){
				
				$needSkip = false;
				
				$this->payAndDiscount = new \com\vip\order\biz\request\PayAndDiscount();
				$this->payAndDiscount->read($input);
				
			}
			
			
			
			
			if ("invoiceInfo" == $schemeField){
				
				$needSkip = false;
				
				$this->invoiceInfo = new \com\vip\order\biz\request\InvoiceInfo();
				$this->invoiceInfo->read($input);
				
			}
			
			
			
			
			if ("customerSrc" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->customerSrc);
				
			}
			
			
			
			
			if ("isCreateNewOrder" == $schemeField){
				
				$needSkip = false;
				$input->readBool($this->isCreateNewOrder);
				
			}
			
			
			
			
			if ("isManjianEdit" == $schemeField){
				
				$needSkip = false;
				$input->readBool($this->isManjianEdit);
				
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
		
		if($this->orderCategory !== null) {
			
			$xfer += $output->writeFieldBegin('orderCategory');
			$xfer += $output->writeByte($this->orderCategory);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->modifyGoodsOrder !== null) {
			
			$xfer += $output->writeFieldBegin('modifyGoodsOrder');
			
			if (!is_object($this->modifyGoodsOrder)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->modifyGoodsOrder->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->goodsInfoList !== null) {
			
			$xfer += $output->writeFieldBegin('goodsInfoList');
			
			if (!is_array($this->goodsInfoList)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeListBegin();
			foreach ($this->goodsInfoList as $iter0){
				
				
				if (!is_object($iter0)) {
					
					throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
				}
				
				$xfer += $iter0->write($output);
				
			}
			
			$output->writeListEnd();
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->receiveAddressInfo !== null) {
			
			$xfer += $output->writeFieldBegin('receiveAddressInfo');
			
			if (!is_object($this->receiveAddressInfo)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->receiveAddressInfo->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->pmsInfo !== null) {
			
			$xfer += $output->writeFieldBegin('pmsInfo');
			
			if (!is_object($this->pmsInfo)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->pmsInfo->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->payAndDiscount !== null) {
			
			$xfer += $output->writeFieldBegin('payAndDiscount');
			
			if (!is_object($this->payAndDiscount)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->payAndDiscount->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->invoiceInfo !== null) {
			
			$xfer += $output->writeFieldBegin('invoiceInfo');
			
			if (!is_object($this->invoiceInfo)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->invoiceInfo->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->customerSrc !== null) {
			
			$xfer += $output->writeFieldBegin('customerSrc');
			$xfer += $output->writeString($this->customerSrc);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->isCreateNewOrder !== null) {
			
			$xfer += $output->writeFieldBegin('isCreateNewOrder');
			$xfer += $output->writeBool($this->isCreateNewOrder);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->isManjianEdit !== null) {
			
			$xfer += $output->writeFieldBegin('isManjianEdit');
			$xfer += $output->writeBool($this->isManjianEdit);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}

?>