<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\common\pojo\order\vo;

class OrderDetailsSuppInfoVO {
	
	static $_TSPEC;
	public $payTypeName = null;
	public $realPayTypeName = null;
	public $mulPayTypeName = null;
	public $orderStatusName = null;
	public $haitaoStatusName = null;
	public $orderTypeName = null;
	public $payStatusName = null;
	public $areaFlagName = null;
	public $virtualIntegral = null;
	public $clientType = null;
	public $isCartSplitOrder = null;
	public $splitOrders = null;
	public $originSns = null;
	public $oxoSubSns = null;
	public $totalPacketRefundMoney = null;
	public $totalPacketMoney = null;
	public $lastPayType = null;
	public $needSplit = null;
	public $splitParentSn = null;
	public $splitChildrenSns = null;
	public $exchangeOriginOrdersn = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'payTypeName'
			),
			2 => array(
			'var' => 'realPayTypeName'
			),
			3 => array(
			'var' => 'mulPayTypeName'
			),
			4 => array(
			'var' => 'orderStatusName'
			),
			5 => array(
			'var' => 'haitaoStatusName'
			),
			6 => array(
			'var' => 'orderTypeName'
			),
			7 => array(
			'var' => 'payStatusName'
			),
			8 => array(
			'var' => 'areaFlagName'
			),
			9 => array(
			'var' => 'virtualIntegral'
			),
			10 => array(
			'var' => 'clientType'
			),
			11 => array(
			'var' => 'isCartSplitOrder'
			),
			12 => array(
			'var' => 'splitOrders'
			),
			13 => array(
			'var' => 'originSns'
			),
			14 => array(
			'var' => 'oxoSubSns'
			),
			15 => array(
			'var' => 'totalPacketRefundMoney'
			),
			16 => array(
			'var' => 'totalPacketMoney'
			),
			17 => array(
			'var' => 'lastPayType'
			),
			18 => array(
			'var' => 'needSplit'
			),
			19 => array(
			'var' => 'splitParentSn'
			),
			20 => array(
			'var' => 'splitChildrenSns'
			),
			21 => array(
			'var' => 'exchangeOriginOrdersn'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['payTypeName'])){
				
				$this->payTypeName = $vals['payTypeName'];
			}
			
			
			if (isset($vals['realPayTypeName'])){
				
				$this->realPayTypeName = $vals['realPayTypeName'];
			}
			
			
			if (isset($vals['mulPayTypeName'])){
				
				$this->mulPayTypeName = $vals['mulPayTypeName'];
			}
			
			
			if (isset($vals['orderStatusName'])){
				
				$this->orderStatusName = $vals['orderStatusName'];
			}
			
			
			if (isset($vals['haitaoStatusName'])){
				
				$this->haitaoStatusName = $vals['haitaoStatusName'];
			}
			
			
			if (isset($vals['orderTypeName'])){
				
				$this->orderTypeName = $vals['orderTypeName'];
			}
			
			
			if (isset($vals['payStatusName'])){
				
				$this->payStatusName = $vals['payStatusName'];
			}
			
			
			if (isset($vals['areaFlagName'])){
				
				$this->areaFlagName = $vals['areaFlagName'];
			}
			
			
			if (isset($vals['virtualIntegral'])){
				
				$this->virtualIntegral = $vals['virtualIntegral'];
			}
			
			
			if (isset($vals['clientType'])){
				
				$this->clientType = $vals['clientType'];
			}
			
			
			if (isset($vals['isCartSplitOrder'])){
				
				$this->isCartSplitOrder = $vals['isCartSplitOrder'];
			}
			
			
			if (isset($vals['splitOrders'])){
				
				$this->splitOrders = $vals['splitOrders'];
			}
			
			
			if (isset($vals['originSns'])){
				
				$this->originSns = $vals['originSns'];
			}
			
			
			if (isset($vals['oxoSubSns'])){
				
				$this->oxoSubSns = $vals['oxoSubSns'];
			}
			
			
			if (isset($vals['totalPacketRefundMoney'])){
				
				$this->totalPacketRefundMoney = $vals['totalPacketRefundMoney'];
			}
			
			
			if (isset($vals['totalPacketMoney'])){
				
				$this->totalPacketMoney = $vals['totalPacketMoney'];
			}
			
			
			if (isset($vals['lastPayType'])){
				
				$this->lastPayType = $vals['lastPayType'];
			}
			
			
			if (isset($vals['needSplit'])){
				
				$this->needSplit = $vals['needSplit'];
			}
			
			
			if (isset($vals['splitParentSn'])){
				
				$this->splitParentSn = $vals['splitParentSn'];
			}
			
			
			if (isset($vals['splitChildrenSns'])){
				
				$this->splitChildrenSns = $vals['splitChildrenSns'];
			}
			
			
			if (isset($vals['exchangeOriginOrdersn'])){
				
				$this->exchangeOriginOrdersn = $vals['exchangeOriginOrdersn'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'OrderDetailsSuppInfoVO';
	}
	
	public function read($input){
		
		$input->readStructBegin();
		while(true){
			
			$schemeField = $input->readFieldBegin();
			if ($schemeField == null) break;
			$needSkip = true;
			
			
			if ("payTypeName" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->payTypeName);
				
			}
			
			
			
			
			if ("realPayTypeName" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->realPayTypeName);
				
			}
			
			
			
			
			if ("mulPayTypeName" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->mulPayTypeName);
				
			}
			
			
			
			
			if ("orderStatusName" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->orderStatusName);
				
			}
			
			
			
			
			if ("haitaoStatusName" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->haitaoStatusName);
				
			}
			
			
			
			
			if ("orderTypeName" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->orderTypeName);
				
			}
			
			
			
			
			if ("payStatusName" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->payStatusName);
				
			}
			
			
			
			
			if ("areaFlagName" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->areaFlagName);
				
			}
			
			
			
			
			if ("virtualIntegral" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->virtualIntegral);
				
			}
			
			
			
			
			if ("clientType" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->clientType); 
				
			}
			
			
			
			
			if ("isCartSplitOrder" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->isCartSplitOrder); 
				
			}
			
			
			
			
			if ("splitOrders" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->splitOrders);
				
			}
			
			
			
			
			if ("originSns" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->originSns);
				
			}
			
			
			
			
			if ("oxoSubSns" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->oxoSubSns);
				
			}
			
			
			
			
			if ("totalPacketRefundMoney" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->totalPacketRefundMoney);
				
			}
			
			
			
			
			if ("totalPacketMoney" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->totalPacketMoney);
				
			}
			
			
			
			
			if ("lastPayType" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->lastPayType); 
				
			}
			
			
			
			
			if ("needSplit" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->needSplit); 
				
			}
			
			
			
			
			if ("splitParentSn" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->splitParentSn);
				
			}
			
			
			
			
			if ("splitChildrenSns" == $schemeField){
				
				$needSkip = false;
				
				$this->splitChildrenSns = array();
				$_size1 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem1 = null;
						$input->readString($elem1);
						
						$this->splitChildrenSns[$_size1++] = $elem1;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readListEnd();
				
			}
			
			
			
			
			if ("exchangeOriginOrdersn" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->exchangeOriginOrdersn);
				
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
		
		if($this->payTypeName !== null) {
			
			$xfer += $output->writeFieldBegin('payTypeName');
			$xfer += $output->writeString($this->payTypeName);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->realPayTypeName !== null) {
			
			$xfer += $output->writeFieldBegin('realPayTypeName');
			$xfer += $output->writeString($this->realPayTypeName);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->mulPayTypeName !== null) {
			
			$xfer += $output->writeFieldBegin('mulPayTypeName');
			$xfer += $output->writeString($this->mulPayTypeName);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderStatusName !== null) {
			
			$xfer += $output->writeFieldBegin('orderStatusName');
			$xfer += $output->writeString($this->orderStatusName);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->haitaoStatusName !== null) {
			
			$xfer += $output->writeFieldBegin('haitaoStatusName');
			$xfer += $output->writeString($this->haitaoStatusName);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderTypeName !== null) {
			
			$xfer += $output->writeFieldBegin('orderTypeName');
			$xfer += $output->writeString($this->orderTypeName);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->payStatusName !== null) {
			
			$xfer += $output->writeFieldBegin('payStatusName');
			$xfer += $output->writeString($this->payStatusName);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->areaFlagName !== null) {
			
			$xfer += $output->writeFieldBegin('areaFlagName');
			$xfer += $output->writeString($this->areaFlagName);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->virtualIntegral !== null) {
			
			$xfer += $output->writeFieldBegin('virtualIntegral');
			$xfer += $output->writeString($this->virtualIntegral);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->clientType !== null) {
			
			$xfer += $output->writeFieldBegin('clientType');
			$xfer += $output->writeI32($this->clientType);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->isCartSplitOrder !== null) {
			
			$xfer += $output->writeFieldBegin('isCartSplitOrder');
			$xfer += $output->writeI32($this->isCartSplitOrder);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->splitOrders !== null) {
			
			$xfer += $output->writeFieldBegin('splitOrders');
			$xfer += $output->writeString($this->splitOrders);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->originSns !== null) {
			
			$xfer += $output->writeFieldBegin('originSns');
			$xfer += $output->writeString($this->originSns);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->oxoSubSns !== null) {
			
			$xfer += $output->writeFieldBegin('oxoSubSns');
			$xfer += $output->writeString($this->oxoSubSns);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->totalPacketRefundMoney !== null) {
			
			$xfer += $output->writeFieldBegin('totalPacketRefundMoney');
			$xfer += $output->writeString($this->totalPacketRefundMoney);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->totalPacketMoney !== null) {
			
			$xfer += $output->writeFieldBegin('totalPacketMoney');
			$xfer += $output->writeString($this->totalPacketMoney);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->lastPayType !== null) {
			
			$xfer += $output->writeFieldBegin('lastPayType');
			$xfer += $output->writeI32($this->lastPayType);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->needSplit !== null) {
			
			$xfer += $output->writeFieldBegin('needSplit');
			$xfer += $output->writeI32($this->needSplit);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->splitParentSn !== null) {
			
			$xfer += $output->writeFieldBegin('splitParentSn');
			$xfer += $output->writeString($this->splitParentSn);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->splitChildrenSns !== null) {
			
			$xfer += $output->writeFieldBegin('splitChildrenSns');
			
			if (!is_array($this->splitChildrenSns)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeListBegin();
			foreach ($this->splitChildrenSns as $iter0){
				
				$xfer += $output->writeString($iter0);
				
			}
			
			$output->writeListEnd();
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->exchangeOriginOrdersn !== null) {
			
			$xfer += $output->writeFieldBegin('exchangeOriginOrdersn');
			$xfer += $output->writeString($this->exchangeOriginOrdersn);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}

?>