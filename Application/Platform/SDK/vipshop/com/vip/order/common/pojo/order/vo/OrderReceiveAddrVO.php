<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\common\pojo\order\vo;

class OrderReceiveAddrVO {
	
	static $_TSPEC;
	public $address = null;
	public $addressType = null;
	public $areaId = null;
	public $areaName = null;
	public $consignee = null;
	public $mobile = null;
	public $postcode = null;
	public $tel = null;
	public $transportDay = null;
	public $transportTime = null;
	public $countryId = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'address'
			),
			2 => array(
			'var' => 'addressType'
			),
			3 => array(
			'var' => 'areaId'
			),
			4 => array(
			'var' => 'areaName'
			),
			5 => array(
			'var' => 'consignee'
			),
			6 => array(
			'var' => 'mobile'
			),
			7 => array(
			'var' => 'postcode'
			),
			8 => array(
			'var' => 'tel'
			),
			9 => array(
			'var' => 'transportDay'
			),
			10 => array(
			'var' => 'transportTime'
			),
			11 => array(
			'var' => 'countryId'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['address'])){
				
				$this->address = $vals['address'];
			}
			
			
			if (isset($vals['addressType'])){
				
				$this->addressType = $vals['addressType'];
			}
			
			
			if (isset($vals['areaId'])){
				
				$this->areaId = $vals['areaId'];
			}
			
			
			if (isset($vals['areaName'])){
				
				$this->areaName = $vals['areaName'];
			}
			
			
			if (isset($vals['consignee'])){
				
				$this->consignee = $vals['consignee'];
			}
			
			
			if (isset($vals['mobile'])){
				
				$this->mobile = $vals['mobile'];
			}
			
			
			if (isset($vals['postcode'])){
				
				$this->postcode = $vals['postcode'];
			}
			
			
			if (isset($vals['tel'])){
				
				$this->tel = $vals['tel'];
			}
			
			
			if (isset($vals['transportDay'])){
				
				$this->transportDay = $vals['transportDay'];
			}
			
			
			if (isset($vals['transportTime'])){
				
				$this->transportTime = $vals['transportTime'];
			}
			
			
			if (isset($vals['countryId'])){
				
				$this->countryId = $vals['countryId'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'OrderReceiveAddrVO';
	}
	
	public function read($input){
		
		$input->readStructBegin();
		while(true){
			
			$schemeField = $input->readFieldBegin();
			if ($schemeField == null) break;
			$needSkip = true;
			
			
			if ("address" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->address);
				
			}
			
			
			
			
			if ("addressType" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->addressType);
				
			}
			
			
			
			
			if ("areaId" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->areaId);
				
			}
			
			
			
			
			if ("areaName" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->areaName);
				
			}
			
			
			
			
			if ("consignee" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->consignee);
				
			}
			
			
			
			
			if ("mobile" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->mobile);
				
			}
			
			
			
			
			if ("postcode" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->postcode);
				
			}
			
			
			
			
			if ("tel" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->tel);
				
			}
			
			
			
			
			if ("transportDay" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->transportDay); 
				
			}
			
			
			
			
			if ("transportTime" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->transportTime); 
				
			}
			
			
			
			
			if ("countryId" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->countryId); 
				
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
		
		if($this->address !== null) {
			
			$xfer += $output->writeFieldBegin('address');
			$xfer += $output->writeString($this->address);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->addressType !== null) {
			
			$xfer += $output->writeFieldBegin('addressType');
			$xfer += $output->writeString($this->addressType);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->areaId !== null) {
			
			$xfer += $output->writeFieldBegin('areaId');
			$xfer += $output->writeString($this->areaId);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->areaName !== null) {
			
			$xfer += $output->writeFieldBegin('areaName');
			$xfer += $output->writeString($this->areaName);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->consignee !== null) {
			
			$xfer += $output->writeFieldBegin('consignee');
			$xfer += $output->writeString($this->consignee);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->mobile !== null) {
			
			$xfer += $output->writeFieldBegin('mobile');
			$xfer += $output->writeString($this->mobile);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->postcode !== null) {
			
			$xfer += $output->writeFieldBegin('postcode');
			$xfer += $output->writeString($this->postcode);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->tel !== null) {
			
			$xfer += $output->writeFieldBegin('tel');
			$xfer += $output->writeString($this->tel);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->transportDay !== null) {
			
			$xfer += $output->writeFieldBegin('transportDay');
			$xfer += $output->writeI32($this->transportDay);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->transportTime !== null) {
			
			$xfer += $output->writeFieldBegin('transportTime');
			$xfer += $output->writeI32($this->transportTime);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->countryId !== null) {
			
			$xfer += $output->writeFieldBegin('countryId');
			$xfer += $output->writeI32($this->countryId);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}

?>