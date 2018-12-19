<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\vop\vendor;

class VendorInfo {
	
	static $_TSPEC;
	public $developerId = null;
	public $account = null;
	public $name = null;
	public $linkName = null;
	public $mobileNo = null;
	public $email = null;
	public $registerDate = null;
	public $province = null;
	public $cityName = null;
	public $county = null;
	public $address = null;
	public $type = null;
	public $emailStatus = null;
	public $cellStatus = null;
	public $businessLicense = null;
	public $licensePicNum = null;
	public $organizationCode = null;
	public $organizationPicNum = null;
	public $taxPicNum = null;
	public $taxNum = null;
	public $accountLicense = null;
	public $accountName = null;
	public $accountNum = null;
	public $vipName = null;
	public $accessToken = null;
	public $status = null;
	public $dealStatus = null;
	public $submitStatus = null;
	public $registEmail = null;
	public $registMobileNo = null;
	public $updateDate = null;
	public $userId = null;
	public $vendorId = null;
	public $vendorPkid = null;
	public $vendorLoginName = null;
	public $taxPicNumDev = null;
	public $licensePicNumDev = null;
	public $organizationPicNumDev = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'developerId'
			),
			2 => array(
			'var' => 'account'
			),
			3 => array(
			'var' => 'name'
			),
			4 => array(
			'var' => 'linkName'
			),
			5 => array(
			'var' => 'mobileNo'
			),
			6 => array(
			'var' => 'email'
			),
			7 => array(
			'var' => 'registerDate'
			),
			8 => array(
			'var' => 'province'
			),
			9 => array(
			'var' => 'cityName'
			),
			10 => array(
			'var' => 'county'
			),
			11 => array(
			'var' => 'address'
			),
			12 => array(
			'var' => 'type'
			),
			13 => array(
			'var' => 'emailStatus'
			),
			14 => array(
			'var' => 'cellStatus'
			),
			15 => array(
			'var' => 'businessLicense'
			),
			16 => array(
			'var' => 'licensePicNum'
			),
			17 => array(
			'var' => 'organizationCode'
			),
			18 => array(
			'var' => 'organizationPicNum'
			),
			19 => array(
			'var' => 'taxPicNum'
			),
			20 => array(
			'var' => 'taxNum'
			),
			21 => array(
			'var' => 'accountLicense'
			),
			22 => array(
			'var' => 'accountName'
			),
			23 => array(
			'var' => 'accountNum'
			),
			24 => array(
			'var' => 'vipName'
			),
			25 => array(
			'var' => 'accessToken'
			),
			26 => array(
			'var' => 'status'
			),
			27 => array(
			'var' => 'dealStatus'
			),
			28 => array(
			'var' => 'submitStatus'
			),
			29 => array(
			'var' => 'registEmail'
			),
			30 => array(
			'var' => 'registMobileNo'
			),
			31 => array(
			'var' => 'updateDate'
			),
			32 => array(
			'var' => 'userId'
			),
			33 => array(
			'var' => 'vendorId'
			),
			34 => array(
			'var' => 'vendorPkid'
			),
			35 => array(
			'var' => 'vendorLoginName'
			),
			36 => array(
			'var' => 'taxPicNumDev'
			),
			37 => array(
			'var' => 'licensePicNumDev'
			),
			38 => array(
			'var' => 'organizationPicNumDev'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['developerId'])){
				
				$this->developerId = $vals['developerId'];
			}
			
			
			if (isset($vals['account'])){
				
				$this->account = $vals['account'];
			}
			
			
			if (isset($vals['name'])){
				
				$this->name = $vals['name'];
			}
			
			
			if (isset($vals['linkName'])){
				
				$this->linkName = $vals['linkName'];
			}
			
			
			if (isset($vals['mobileNo'])){
				
				$this->mobileNo = $vals['mobileNo'];
			}
			
			
			if (isset($vals['email'])){
				
				$this->email = $vals['email'];
			}
			
			
			if (isset($vals['registerDate'])){
				
				$this->registerDate = $vals['registerDate'];
			}
			
			
			if (isset($vals['province'])){
				
				$this->province = $vals['province'];
			}
			
			
			if (isset($vals['cityName'])){
				
				$this->cityName = $vals['cityName'];
			}
			
			
			if (isset($vals['county'])){
				
				$this->county = $vals['county'];
			}
			
			
			if (isset($vals['address'])){
				
				$this->address = $vals['address'];
			}
			
			
			if (isset($vals['type'])){
				
				$this->type = $vals['type'];
			}
			
			
			if (isset($vals['emailStatus'])){
				
				$this->emailStatus = $vals['emailStatus'];
			}
			
			
			if (isset($vals['cellStatus'])){
				
				$this->cellStatus = $vals['cellStatus'];
			}
			
			
			if (isset($vals['businessLicense'])){
				
				$this->businessLicense = $vals['businessLicense'];
			}
			
			
			if (isset($vals['licensePicNum'])){
				
				$this->licensePicNum = $vals['licensePicNum'];
			}
			
			
			if (isset($vals['organizationCode'])){
				
				$this->organizationCode = $vals['organizationCode'];
			}
			
			
			if (isset($vals['organizationPicNum'])){
				
				$this->organizationPicNum = $vals['organizationPicNum'];
			}
			
			
			if (isset($vals['taxPicNum'])){
				
				$this->taxPicNum = $vals['taxPicNum'];
			}
			
			
			if (isset($vals['taxNum'])){
				
				$this->taxNum = $vals['taxNum'];
			}
			
			
			if (isset($vals['accountLicense'])){
				
				$this->accountLicense = $vals['accountLicense'];
			}
			
			
			if (isset($vals['accountName'])){
				
				$this->accountName = $vals['accountName'];
			}
			
			
			if (isset($vals['accountNum'])){
				
				$this->accountNum = $vals['accountNum'];
			}
			
			
			if (isset($vals['vipName'])){
				
				$this->vipName = $vals['vipName'];
			}
			
			
			if (isset($vals['accessToken'])){
				
				$this->accessToken = $vals['accessToken'];
			}
			
			
			if (isset($vals['status'])){
				
				$this->status = $vals['status'];
			}
			
			
			if (isset($vals['dealStatus'])){
				
				$this->dealStatus = $vals['dealStatus'];
			}
			
			
			if (isset($vals['submitStatus'])){
				
				$this->submitStatus = $vals['submitStatus'];
			}
			
			
			if (isset($vals['registEmail'])){
				
				$this->registEmail = $vals['registEmail'];
			}
			
			
			if (isset($vals['registMobileNo'])){
				
				$this->registMobileNo = $vals['registMobileNo'];
			}
			
			
			if (isset($vals['updateDate'])){
				
				$this->updateDate = $vals['updateDate'];
			}
			
			
			if (isset($vals['userId'])){
				
				$this->userId = $vals['userId'];
			}
			
			
			if (isset($vals['vendorId'])){
				
				$this->vendorId = $vals['vendorId'];
			}
			
			
			if (isset($vals['vendorPkid'])){
				
				$this->vendorPkid = $vals['vendorPkid'];
			}
			
			
			if (isset($vals['vendorLoginName'])){
				
				$this->vendorLoginName = $vals['vendorLoginName'];
			}
			
			
			if (isset($vals['taxPicNumDev'])){
				
				$this->taxPicNumDev = $vals['taxPicNumDev'];
			}
			
			
			if (isset($vals['licensePicNumDev'])){
				
				$this->licensePicNumDev = $vals['licensePicNumDev'];
			}
			
			
			if (isset($vals['organizationPicNumDev'])){
				
				$this->organizationPicNumDev = $vals['organizationPicNumDev'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'VendorInfo';
	}
	
	public function read($input){
		
		$input->readStructBegin();
		while(true){
			
			$schemeField = $input->readFieldBegin();
			if ($schemeField == null) break;
			$needSkip = true;
			
			
			if ("developerId" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->developerId); 
				
			}
			
			
			
			
			if ("account" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->account);
				
			}
			
			
			
			
			if ("name" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->name);
				
			}
			
			
			
			
			if ("linkName" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->linkName);
				
			}
			
			
			
			
			if ("mobileNo" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->mobileNo);
				
			}
			
			
			
			
			if ("email" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->email);
				
			}
			
			
			
			
			if ("registerDate" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->registerDate);
				
			}
			
			
			
			
			if ("province" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->province);
				
			}
			
			
			
			
			if ("cityName" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->cityName);
				
			}
			
			
			
			
			if ("county" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->county);
				
			}
			
			
			
			
			if ("address" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->address);
				
			}
			
			
			
			
			if ("type" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->type); 
				
			}
			
			
			
			
			if ("emailStatus" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->emailStatus); 
				
			}
			
			
			
			
			if ("cellStatus" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->cellStatus); 
				
			}
			
			
			
			
			if ("businessLicense" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->businessLicense);
				
			}
			
			
			
			
			if ("licensePicNum" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->licensePicNum); 
				
			}
			
			
			
			
			if ("organizationCode" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->organizationCode);
				
			}
			
			
			
			
			if ("organizationPicNum" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->organizationPicNum); 
				
			}
			
			
			
			
			if ("taxPicNum" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->taxPicNum); 
				
			}
			
			
			
			
			if ("taxNum" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->taxNum);
				
			}
			
			
			
			
			if ("accountLicense" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->accountLicense);
				
			}
			
			
			
			
			if ("accountName" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->accountName);
				
			}
			
			
			
			
			if ("accountNum" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->accountNum);
				
			}
			
			
			
			
			if ("vipName" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->vipName);
				
			}
			
			
			
			
			if ("accessToken" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->accessToken);
				
			}
			
			
			
			
			if ("status" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->status); 
				
			}
			
			
			
			
			if ("dealStatus" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->dealStatus); 
				
			}
			
			
			
			
			if ("submitStatus" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->submitStatus); 
				
			}
			
			
			
			
			if ("registEmail" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->registEmail);
				
			}
			
			
			
			
			if ("registMobileNo" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->registMobileNo);
				
			}
			
			
			
			
			if ("updateDate" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->updateDate);
				
			}
			
			
			
			
			if ("userId" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->userId);
				
			}
			
			
			
			
			if ("vendorId" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->vendorId); 
				
			}
			
			
			
			
			if ("vendorPkid" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->vendorPkid); 
				
			}
			
			
			
			
			if ("vendorLoginName" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->vendorLoginName);
				
			}
			
			
			
			
			if ("taxPicNumDev" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->taxPicNumDev); 
				
			}
			
			
			
			
			if ("licensePicNumDev" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->licensePicNumDev); 
				
			}
			
			
			
			
			if ("organizationPicNumDev" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->organizationPicNumDev); 
				
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
		
		$xfer += $output->writeFieldBegin('developerId');
		$xfer += $output->writeI32($this->developerId);
		
		$xfer += $output->writeFieldEnd();
		
		$xfer += $output->writeFieldBegin('account');
		$xfer += $output->writeString($this->account);
		
		$xfer += $output->writeFieldEnd();
		
		if($this->name !== null) {
			
			$xfer += $output->writeFieldBegin('name');
			$xfer += $output->writeString($this->name);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->linkName !== null) {
			
			$xfer += $output->writeFieldBegin('linkName');
			$xfer += $output->writeString($this->linkName);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->mobileNo !== null) {
			
			$xfer += $output->writeFieldBegin('mobileNo');
			$xfer += $output->writeString($this->mobileNo);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->email !== null) {
			
			$xfer += $output->writeFieldBegin('email');
			$xfer += $output->writeString($this->email);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->registerDate !== null) {
			
			$xfer += $output->writeFieldBegin('registerDate');
			$xfer += $output->writeI64($this->registerDate);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->province !== null) {
			
			$xfer += $output->writeFieldBegin('province');
			$xfer += $output->writeString($this->province);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->cityName !== null) {
			
			$xfer += $output->writeFieldBegin('cityName');
			$xfer += $output->writeString($this->cityName);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->county !== null) {
			
			$xfer += $output->writeFieldBegin('county');
			$xfer += $output->writeString($this->county);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->address !== null) {
			
			$xfer += $output->writeFieldBegin('address');
			$xfer += $output->writeString($this->address);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->type !== null) {
			
			$xfer += $output->writeFieldBegin('type');
			$xfer += $output->writeI32($this->type);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->emailStatus !== null) {
			
			$xfer += $output->writeFieldBegin('emailStatus');
			$xfer += $output->writeI32($this->emailStatus);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->cellStatus !== null) {
			
			$xfer += $output->writeFieldBegin('cellStatus');
			$xfer += $output->writeI32($this->cellStatus);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->businessLicense !== null) {
			
			$xfer += $output->writeFieldBegin('businessLicense');
			$xfer += $output->writeString($this->businessLicense);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->licensePicNum !== null) {
			
			$xfer += $output->writeFieldBegin('licensePicNum');
			$xfer += $output->writeI32($this->licensePicNum);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->organizationCode !== null) {
			
			$xfer += $output->writeFieldBegin('organizationCode');
			$xfer += $output->writeString($this->organizationCode);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->organizationPicNum !== null) {
			
			$xfer += $output->writeFieldBegin('organizationPicNum');
			$xfer += $output->writeI32($this->organizationPicNum);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->taxPicNum !== null) {
			
			$xfer += $output->writeFieldBegin('taxPicNum');
			$xfer += $output->writeI32($this->taxPicNum);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->taxNum !== null) {
			
			$xfer += $output->writeFieldBegin('taxNum');
			$xfer += $output->writeString($this->taxNum);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->accountLicense !== null) {
			
			$xfer += $output->writeFieldBegin('accountLicense');
			$xfer += $output->writeString($this->accountLicense);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->accountName !== null) {
			
			$xfer += $output->writeFieldBegin('accountName');
			$xfer += $output->writeString($this->accountName);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->accountNum !== null) {
			
			$xfer += $output->writeFieldBegin('accountNum');
			$xfer += $output->writeString($this->accountNum);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->vipName !== null) {
			
			$xfer += $output->writeFieldBegin('vipName');
			$xfer += $output->writeString($this->vipName);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->accessToken !== null) {
			
			$xfer += $output->writeFieldBegin('accessToken');
			$xfer += $output->writeString($this->accessToken);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->status !== null) {
			
			$xfer += $output->writeFieldBegin('status');
			$xfer += $output->writeI32($this->status);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->dealStatus !== null) {
			
			$xfer += $output->writeFieldBegin('dealStatus');
			$xfer += $output->writeI32($this->dealStatus);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->submitStatus !== null) {
			
			$xfer += $output->writeFieldBegin('submitStatus');
			$xfer += $output->writeI32($this->submitStatus);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->registEmail !== null) {
			
			$xfer += $output->writeFieldBegin('registEmail');
			$xfer += $output->writeString($this->registEmail);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->registMobileNo !== null) {
			
			$xfer += $output->writeFieldBegin('registMobileNo');
			$xfer += $output->writeString($this->registMobileNo);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->updateDate !== null) {
			
			$xfer += $output->writeFieldBegin('updateDate');
			$xfer += $output->writeI64($this->updateDate);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->userId !== null) {
			
			$xfer += $output->writeFieldBegin('userId');
			$xfer += $output->writeString($this->userId);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->vendorId !== null) {
			
			$xfer += $output->writeFieldBegin('vendorId');
			$xfer += $output->writeI32($this->vendorId);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->vendorPkid !== null) {
			
			$xfer += $output->writeFieldBegin('vendorPkid');
			$xfer += $output->writeI32($this->vendorPkid);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->vendorLoginName !== null) {
			
			$xfer += $output->writeFieldBegin('vendorLoginName');
			$xfer += $output->writeString($this->vendorLoginName);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->taxPicNumDev !== null) {
			
			$xfer += $output->writeFieldBegin('taxPicNumDev');
			$xfer += $output->writeI32($this->taxPicNumDev);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->licensePicNumDev !== null) {
			
			$xfer += $output->writeFieldBegin('licensePicNumDev');
			$xfer += $output->writeI32($this->licensePicNumDev);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->organizationPicNumDev !== null) {
			
			$xfer += $output->writeFieldBegin('organizationPicNumDev');
			$xfer += $output->writeI32($this->organizationPicNumDev);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}

?>