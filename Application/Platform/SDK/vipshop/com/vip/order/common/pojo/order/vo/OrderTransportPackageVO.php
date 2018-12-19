<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\common\pojo\order\vo;

class OrderTransportPackageVO {
	
	static $_TSPEC;
	public $id = null;
	public $orderSn = null;
	public $orderId = null;
	public $packageName = null;
	public $transportNumber = null;
	public $createTime = null;
	public $updateTime = null;
	public $detail = null;
	public $status = null;
	public $carriersShortname = null;
	public $carriersCode = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'id'
			),
			2 => array(
			'var' => 'orderSn'
			),
			3 => array(
			'var' => 'orderId'
			),
			4 => array(
			'var' => 'packageName'
			),
			5 => array(
			'var' => 'transportNumber'
			),
			6 => array(
			'var' => 'createTime'
			),
			7 => array(
			'var' => 'updateTime'
			),
			8 => array(
			'var' => 'detail'
			),
			9 => array(
			'var' => 'status'
			),
			10 => array(
			'var' => 'carriersShortname'
			),
			11 => array(
			'var' => 'carriersCode'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['id'])){
				
				$this->id = $vals['id'];
			}
			
			
			if (isset($vals['orderSn'])){
				
				$this->orderSn = $vals['orderSn'];
			}
			
			
			if (isset($vals['orderId'])){
				
				$this->orderId = $vals['orderId'];
			}
			
			
			if (isset($vals['packageName'])){
				
				$this->packageName = $vals['packageName'];
			}
			
			
			if (isset($vals['transportNumber'])){
				
				$this->transportNumber = $vals['transportNumber'];
			}
			
			
			if (isset($vals['createTime'])){
				
				$this->createTime = $vals['createTime'];
			}
			
			
			if (isset($vals['updateTime'])){
				
				$this->updateTime = $vals['updateTime'];
			}
			
			
			if (isset($vals['detail'])){
				
				$this->detail = $vals['detail'];
			}
			
			
			if (isset($vals['status'])){
				
				$this->status = $vals['status'];
			}
			
			
			if (isset($vals['carriersShortname'])){
				
				$this->carriersShortname = $vals['carriersShortname'];
			}
			
			
			if (isset($vals['carriersCode'])){
				
				$this->carriersCode = $vals['carriersCode'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'OrderTransportPackageVO';
	}
	
	public function read($input){
		
		$input->readStructBegin();
		while(true){
			
			$schemeField = $input->readFieldBegin();
			if ($schemeField == null) break;
			$needSkip = true;
			
			
			if ("id" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->id); 
				
			}
			
			
			
			
			if ("orderSn" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->orderSn);
				
			}
			
			
			
			
			if ("orderId" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->orderId); 
				
			}
			
			
			
			
			if ("packageName" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->packageName); 
				
			}
			
			
			
			
			if ("transportNumber" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->transportNumber);
				
			}
			
			
			
			
			if ("createTime" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->createTime); 
				
			}
			
			
			
			
			if ("updateTime" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->updateTime); 
				
			}
			
			
			
			
			if ("detail" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->detail);
				
			}
			
			
			
			
			if ("status" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->status); 
				
			}
			
			
			
			
			if ("carriersShortname" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->carriersShortname);
				
			}
			
			
			
			
			if ("carriersCode" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->carriersCode);
				
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
		
		if($this->id !== null) {
			
			$xfer += $output->writeFieldBegin('id');
			$xfer += $output->writeI64($this->id);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderSn !== null) {
			
			$xfer += $output->writeFieldBegin('orderSn');
			$xfer += $output->writeString($this->orderSn);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderId !== null) {
			
			$xfer += $output->writeFieldBegin('orderId');
			$xfer += $output->writeI64($this->orderId);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->packageName !== null) {
			
			$xfer += $output->writeFieldBegin('packageName');
			$xfer += $output->writeI32($this->packageName);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->transportNumber !== null) {
			
			$xfer += $output->writeFieldBegin('transportNumber');
			$xfer += $output->writeString($this->transportNumber);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->createTime !== null) {
			
			$xfer += $output->writeFieldBegin('createTime');
			$xfer += $output->writeI64($this->createTime);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->updateTime !== null) {
			
			$xfer += $output->writeFieldBegin('updateTime');
			$xfer += $output->writeI64($this->updateTime);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->detail !== null) {
			
			$xfer += $output->writeFieldBegin('detail');
			$xfer += $output->writeString($this->detail);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->status !== null) {
			
			$xfer += $output->writeFieldBegin('status');
			$xfer += $output->writeI32($this->status);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->carriersShortname !== null) {
			
			$xfer += $output->writeFieldBegin('carriersShortname');
			$xfer += $output->writeString($this->carriersShortname);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->carriersCode !== null) {
			
			$xfer += $output->writeFieldBegin('carriersCode');
			$xfer += $output->writeString($this->carriersCode);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}

?>