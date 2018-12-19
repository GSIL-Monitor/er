<?php


/*
* Copyright (c) 2008-2015 vip.com, All Rights Reserved.
* Powered by com.vip.osp.idlc-1.2.0.
* Generation Time: Wed Apr 22 19:26:00 CST 2015.
*/

namespace vipapis\product;

class ImageUrl {
	
	static $_TSPEC;
	public $small_image = null;
	public $middle_image = null;
	public $big_image = null;
	public $list_image = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'small_image'
			),
			2 => array(
			'var' => 'middle_image'
			),
			3 => array(
			'var' => 'big_image'
			),
			4 => array(
			'var' => 'list_image'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['small_image'])){
				
				$this->small_image = $vals['small_image'];
			}
			
			
			if (isset($vals['middle_image'])){
				
				$this->middle_image = $vals['middle_image'];
			}
			
			
			if (isset($vals['big_image'])){
				
				$this->big_image = $vals['big_image'];
			}
			
			
			if (isset($vals['list_image'])){
				
				$this->list_image = $vals['list_image'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'ImageUrl';
	}
	
	public function read($input){
		
		$input->readStructBegin();
		while(true){
			
			$schemeField = $input->readFieldBegin();
			if ($schemeField == null) break;
			$needSkip = true;
			
			
			if ("small_image" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->small_image);
				
			}
			
			
			
			
			if ("middle_image" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->middle_image);
				
			}
			
			
			
			
			if ("big_image" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->big_image);
				
			}
			
			
			
			
			if ("list_image" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->list_image);
				
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
		
		if($this->small_image !== null) {
			
			$xfer += $output->writeFieldBegin('small_image');
			$xfer += $output->writeString($this->small_image);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->middle_image !== null) {
			
			$xfer += $output->writeFieldBegin('middle_image');
			$xfer += $output->writeString($this->middle_image);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->big_image !== null) {
			
			$xfer += $output->writeFieldBegin('big_image');
			$xfer += $output->writeString($this->big_image);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->list_image !== null) {
			
			$xfer += $output->writeFieldBegin('list_image');
			$xfer += $output->writeString($this->list_image);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}

?>