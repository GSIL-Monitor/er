<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\common\pojo\order\vo;

class ReturnOrExchangeGoodsVO {
	
	static $_TSPEC;
	public $merchandiseNo = null;
	public $merItemNo = null;
	public $sn = null;
	public $amount = null;
	public $priceId = null;
	public $skuId = null;
	public $goodsVersion = null;
	public $salesNo = null;
	public $sellPrice = null;
	public $goodsType = null;
	public $reasonCode = null;
	public $reason = null;
	public $exchangeGoodsStockList = null;
	public $exActSubtotal = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'merchandiseNo'
			),
			2 => array(
			'var' => 'merItemNo'
			),
			3 => array(
			'var' => 'sn'
			),
			4 => array(
			'var' => 'amount'
			),
			5 => array(
			'var' => 'priceId'
			),
			6 => array(
			'var' => 'skuId'
			),
			7 => array(
			'var' => 'goodsVersion'
			),
			8 => array(
			'var' => 'salesNo'
			),
			9 => array(
			'var' => 'sellPrice'
			),
			10 => array(
			'var' => 'goodsType'
			),
			11 => array(
			'var' => 'reasonCode'
			),
			12 => array(
			'var' => 'reason'
			),
			13 => array(
			'var' => 'exchangeGoodsStockList'
			),
			14 => array(
			'var' => 'exActSubtotal'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['merchandiseNo'])){
				
				$this->merchandiseNo = $vals['merchandiseNo'];
			}
			
			
			if (isset($vals['merItemNo'])){
				
				$this->merItemNo = $vals['merItemNo'];
			}
			
			
			if (isset($vals['sn'])){
				
				$this->sn = $vals['sn'];
			}
			
			
			if (isset($vals['amount'])){
				
				$this->amount = $vals['amount'];
			}
			
			
			if (isset($vals['priceId'])){
				
				$this->priceId = $vals['priceId'];
			}
			
			
			if (isset($vals['skuId'])){
				
				$this->skuId = $vals['skuId'];
			}
			
			
			if (isset($vals['goodsVersion'])){
				
				$this->goodsVersion = $vals['goodsVersion'];
			}
			
			
			if (isset($vals['salesNo'])){
				
				$this->salesNo = $vals['salesNo'];
			}
			
			
			if (isset($vals['sellPrice'])){
				
				$this->sellPrice = $vals['sellPrice'];
			}
			
			
			if (isset($vals['goodsType'])){
				
				$this->goodsType = $vals['goodsType'];
			}
			
			
			if (isset($vals['reasonCode'])){
				
				$this->reasonCode = $vals['reasonCode'];
			}
			
			
			if (isset($vals['reason'])){
				
				$this->reason = $vals['reason'];
			}
			
			
			if (isset($vals['exchangeGoodsStockList'])){
				
				$this->exchangeGoodsStockList = $vals['exchangeGoodsStockList'];
			}
			
			
			if (isset($vals['exActSubtotal'])){
				
				$this->exActSubtotal = $vals['exActSubtotal'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'ReturnOrExchangeGoodsVO';
	}
	
	public function read($input){
		
		$input->readStructBegin();
		while(true){
			
			$schemeField = $input->readFieldBegin();
			if ($schemeField == null) break;
			$needSkip = true;
			
			
			if ("merchandiseNo" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->merchandiseNo); 
				
			}
			
			
			
			
			if ("merItemNo" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->merItemNo); 
				
			}
			
			
			
			
			if ("sn" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->sn);
				
			}
			
			
			
			
			if ("amount" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->amount); 
				
			}
			
			
			
			
			if ("priceId" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->priceId); 
				
			}
			
			
			
			
			if ("skuId" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->skuId); 
				
			}
			
			
			
			
			if ("goodsVersion" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->goodsVersion); 
				
			}
			
			
			
			
			if ("salesNo" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->salesNo); 
				
			}
			
			
			
			
			if ("sellPrice" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->sellPrice);
				
			}
			
			
			
			
			if ("goodsType" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->goodsType); 
				
			}
			
			
			
			
			if ("reasonCode" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->reasonCode);
				
			}
			
			
			
			
			if ("reason" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->reason);
				
			}
			
			
			
			
			if ("exchangeGoodsStockList" == $schemeField){
				
				$needSkip = false;
				
				$this->exchangeGoodsStockList = array();
				$_size0 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem0 = null;
						
						$elem0 = new \com\vip\order\common\pojo\order\vo\ExchangeGoodsStockVO();
						$elem0->read($input);
						
						$this->exchangeGoodsStockList[$_size0++] = $elem0;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readListEnd();
				
			}
			
			
			
			
			if ("exActSubtotal" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->exActSubtotal);
				
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
		
		if($this->merchandiseNo !== null) {
			
			$xfer += $output->writeFieldBegin('merchandiseNo');
			$xfer += $output->writeI64($this->merchandiseNo);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->merItemNo !== null) {
			
			$xfer += $output->writeFieldBegin('merItemNo');
			$xfer += $output->writeI64($this->merItemNo);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->sn !== null) {
			
			$xfer += $output->writeFieldBegin('sn');
			$xfer += $output->writeString($this->sn);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->amount !== null) {
			
			$xfer += $output->writeFieldBegin('amount');
			$xfer += $output->writeI32($this->amount);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->priceId !== null) {
			
			$xfer += $output->writeFieldBegin('priceId');
			$xfer += $output->writeI64($this->priceId);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->skuId !== null) {
			
			$xfer += $output->writeFieldBegin('skuId');
			$xfer += $output->writeI64($this->skuId);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->goodsVersion !== null) {
			
			$xfer += $output->writeFieldBegin('goodsVersion');
			$xfer += $output->writeI32($this->goodsVersion);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->salesNo !== null) {
			
			$xfer += $output->writeFieldBegin('salesNo');
			$xfer += $output->writeI64($this->salesNo);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->sellPrice !== null) {
			
			$xfer += $output->writeFieldBegin('sellPrice');
			$xfer += $output->writeString($this->sellPrice);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->goodsType !== null) {
			
			$xfer += $output->writeFieldBegin('goodsType');
			$xfer += $output->writeI32($this->goodsType);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->reasonCode !== null) {
			
			$xfer += $output->writeFieldBegin('reasonCode');
			$xfer += $output->writeString($this->reasonCode);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->reason !== null) {
			
			$xfer += $output->writeFieldBegin('reason');
			$xfer += $output->writeString($this->reason);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->exchangeGoodsStockList !== null) {
			
			$xfer += $output->writeFieldBegin('exchangeGoodsStockList');
			
			if (!is_array($this->exchangeGoodsStockList)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeListBegin();
			foreach ($this->exchangeGoodsStockList as $iter0){
				
				
				if (!is_object($iter0)) {
					
					throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
				}
				
				$xfer += $iter0->write($output);
				
			}
			
			$output->writeListEnd();
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->exActSubtotal !== null) {
			
			$xfer += $output->writeFieldBegin('exActSubtotal');
			$xfer += $output->writeString($this->exActSubtotal);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}

?>