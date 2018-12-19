<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\common\pojo\order\vo;

class OrderCouponVO {
	
	static $_TSPEC;
	public $couponSn = null;
	public $couponType = null;
	public $couponTypename = null;
	public $couponField = null;
	public $couponFieldname = null;
	public $couponFavDesc = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'couponSn'
			),
			2 => array(
			'var' => 'couponType'
			),
			3 => array(
			'var' => 'couponTypename'
			),
			4 => array(
			'var' => 'couponField'
			),
			5 => array(
			'var' => 'couponFieldname'
			),
			6 => array(
			'var' => 'couponFavDesc'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['couponSn'])){
				
				$this->couponSn = $vals['couponSn'];
			}
			
			
			if (isset($vals['couponType'])){
				
				$this->couponType = $vals['couponType'];
			}
			
			
			if (isset($vals['couponTypename'])){
				
				$this->couponTypename = $vals['couponTypename'];
			}
			
			
			if (isset($vals['couponField'])){
				
				$this->couponField = $vals['couponField'];
			}
			
			
			if (isset($vals['couponFieldname'])){
				
				$this->couponFieldname = $vals['couponFieldname'];
			}
			
			
			if (isset($vals['couponFavDesc'])){
				
				$this->couponFavDesc = $vals['couponFavDesc'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'OrderCouponVO';
	}
	
	public function read($input){
		
		$input->readStructBegin();
		while(true){
			
			$schemeField = $input->readFieldBegin();
			if ($schemeField == null) break;
			$needSkip = true;
			
			
			if ("couponSn" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->couponSn);
				
			}
			
			
			
			
			if ("couponType" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->couponType);
				
			}
			
			
			
			
			if ("couponTypename" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->couponTypename);
				
			}
			
			
			
			
			if ("couponField" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->couponField);
				
			}
			
			
			
			
			if ("couponFieldname" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->couponFieldname);
				
			}
			
			
			
			
			if ("couponFavDesc" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->couponFavDesc);
				
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
		
		if($this->couponSn !== null) {
			
			$xfer += $output->writeFieldBegin('couponSn');
			$xfer += $output->writeString($this->couponSn);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->couponType !== null) {
			
			$xfer += $output->writeFieldBegin('couponType');
			$xfer += $output->writeString($this->couponType);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->couponTypename !== null) {
			
			$xfer += $output->writeFieldBegin('couponTypename');
			$xfer += $output->writeString($this->couponTypename);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->couponField !== null) {
			
			$xfer += $output->writeFieldBegin('couponField');
			$xfer += $output->writeString($this->couponField);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->couponFieldname !== null) {
			
			$xfer += $output->writeFieldBegin('couponFieldname');
			$xfer += $output->writeString($this->couponFieldname);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->couponFavDesc !== null) {
			
			$xfer += $output->writeFieldBegin('couponFavDesc');
			$xfer += $output->writeString($this->couponFavDesc);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}

?>