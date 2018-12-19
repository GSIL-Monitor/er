<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace vipapis\schedule;

class Schedule {
	
	static $_TSPEC;
	public $schedule_id = null;
	public $schedule_name = null;
	public $start_time = null;
	public $end_time = null;
	public $index_image = null;
	public $index_advance_image = null;
	public $schedule_self_logo = null;
	public $logo = null;
	public $agio = null;
	public $brand_store_sn = null;
	public $brand_name = null;
	public $brand_name_eng = null;
	public $brand_url = null;
	public $schedule_url = null;
	public $schedule_flash = null;
	public $schedule_mobile_url = null;
	public $mobile_image_one = null;
	public $mobile_image_two = null;
	public $mobile_show_from = null;
	public $mobile_show_to = null;
	public $pc_show_from = null;
	public $pc_show_to = null;
	public $channels = null;
	public $warehouses = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'schedule_id'
			),
			2 => array(
			'var' => 'schedule_name'
			),
			3 => array(
			'var' => 'start_time'
			),
			4 => array(
			'var' => 'end_time'
			),
			5 => array(
			'var' => 'index_image'
			),
			6 => array(
			'var' => 'index_advance_image'
			),
			7 => array(
			'var' => 'schedule_self_logo'
			),
			8 => array(
			'var' => 'logo'
			),
			9 => array(
			'var' => 'agio'
			),
			10 => array(
			'var' => 'brand_store_sn'
			),
			11 => array(
			'var' => 'brand_name'
			),
			12 => array(
			'var' => 'brand_name_eng'
			),
			13 => array(
			'var' => 'brand_url'
			),
			14 => array(
			'var' => 'schedule_url'
			),
			15 => array(
			'var' => 'schedule_flash'
			),
			16 => array(
			'var' => 'schedule_mobile_url'
			),
			17 => array(
			'var' => 'mobile_image_one'
			),
			18 => array(
			'var' => 'mobile_image_two'
			),
			19 => array(
			'var' => 'mobile_show_from'
			),
			20 => array(
			'var' => 'mobile_show_to'
			),
			21 => array(
			'var' => 'pc_show_from'
			),
			22 => array(
			'var' => 'pc_show_to'
			),
			23 => array(
			'var' => 'channels'
			),
			24 => array(
			'var' => 'warehouses'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['schedule_id'])){
				
				$this->schedule_id = $vals['schedule_id'];
			}
			
			
			if (isset($vals['schedule_name'])){
				
				$this->schedule_name = $vals['schedule_name'];
			}
			
			
			if (isset($vals['start_time'])){
				
				$this->start_time = $vals['start_time'];
			}
			
			
			if (isset($vals['end_time'])){
				
				$this->end_time = $vals['end_time'];
			}
			
			
			if (isset($vals['index_image'])){
				
				$this->index_image = $vals['index_image'];
			}
			
			
			if (isset($vals['index_advance_image'])){
				
				$this->index_advance_image = $vals['index_advance_image'];
			}
			
			
			if (isset($vals['schedule_self_logo'])){
				
				$this->schedule_self_logo = $vals['schedule_self_logo'];
			}
			
			
			if (isset($vals['logo'])){
				
				$this->logo = $vals['logo'];
			}
			
			
			if (isset($vals['agio'])){
				
				$this->agio = $vals['agio'];
			}
			
			
			if (isset($vals['brand_store_sn'])){
				
				$this->brand_store_sn = $vals['brand_store_sn'];
			}
			
			
			if (isset($vals['brand_name'])){
				
				$this->brand_name = $vals['brand_name'];
			}
			
			
			if (isset($vals['brand_name_eng'])){
				
				$this->brand_name_eng = $vals['brand_name_eng'];
			}
			
			
			if (isset($vals['brand_url'])){
				
				$this->brand_url = $vals['brand_url'];
			}
			
			
			if (isset($vals['schedule_url'])){
				
				$this->schedule_url = $vals['schedule_url'];
			}
			
			
			if (isset($vals['schedule_flash'])){
				
				$this->schedule_flash = $vals['schedule_flash'];
			}
			
			
			if (isset($vals['schedule_mobile_url'])){
				
				$this->schedule_mobile_url = $vals['schedule_mobile_url'];
			}
			
			
			if (isset($vals['mobile_image_one'])){
				
				$this->mobile_image_one = $vals['mobile_image_one'];
			}
			
			
			if (isset($vals['mobile_image_two'])){
				
				$this->mobile_image_two = $vals['mobile_image_two'];
			}
			
			
			if (isset($vals['mobile_show_from'])){
				
				$this->mobile_show_from = $vals['mobile_show_from'];
			}
			
			
			if (isset($vals['mobile_show_to'])){
				
				$this->mobile_show_to = $vals['mobile_show_to'];
			}
			
			
			if (isset($vals['pc_show_from'])){
				
				$this->pc_show_from = $vals['pc_show_from'];
			}
			
			
			if (isset($vals['pc_show_to'])){
				
				$this->pc_show_to = $vals['pc_show_to'];
			}
			
			
			if (isset($vals['channels'])){
				
				$this->channels = $vals['channels'];
			}
			
			
			if (isset($vals['warehouses'])){
				
				$this->warehouses = $vals['warehouses'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'Schedule';
	}
	
	public function read($input){
		
		$input->readStructBegin();
		while(true){
			
			$schemeField = $input->readFieldBegin();
			if ($schemeField == null) break;
			$needSkip = true;
			
			
			if ("schedule_id" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->schedule_id); 
				
			}
			
			
			
			
			if ("schedule_name" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->schedule_name);
				
			}
			
			
			
			
			if ("start_time" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->start_time);
				
			}
			
			
			
			
			if ("end_time" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->end_time);
				
			}
			
			
			
			
			if ("index_image" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->index_image);
				
			}
			
			
			
			
			if ("index_advance_image" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->index_advance_image);
				
			}
			
			
			
			
			if ("schedule_self_logo" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->schedule_self_logo);
				
			}
			
			
			
			
			if ("logo" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->logo);
				
			}
			
			
			
			
			if ("agio" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->agio);
				
			}
			
			
			
			
			if ("brand_store_sn" == $schemeField){
				
				$needSkip = false;
				
				$this->brand_store_sn = array();
				$_size0 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem0 = null;
						$input->readString($elem0);
						
						$this->brand_store_sn[$_size0++] = $elem0;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readListEnd();
				
			}
			
			
			
			
			if ("brand_name" == $schemeField){
				
				$needSkip = false;
				
				$this->brand_name = array();
				$_size1 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem1 = null;
						$input->readString($elem1);
						
						$this->brand_name[$_size1++] = $elem1;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readListEnd();
				
			}
			
			
			
			
			if ("brand_name_eng" == $schemeField){
				
				$needSkip = false;
				
				$this->brand_name_eng = array();
				$_size2 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem2 = null;
						$input->readString($elem2);
						
						$this->brand_name_eng[$_size2++] = $elem2;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readListEnd();
				
			}
			
			
			
			
			if ("brand_url" == $schemeField){
				
				$needSkip = false;
				
				$this->brand_url = array();
				$_size3 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem3 = null;
						$input->readString($elem3);
						
						$this->brand_url[$_size3++] = $elem3;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readListEnd();
				
			}
			
			
			
			
			if ("schedule_url" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->schedule_url);
				
			}
			
			
			
			
			if ("schedule_flash" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->schedule_flash);
				
			}
			
			
			
			
			if ("schedule_mobile_url" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->schedule_mobile_url);
				
			}
			
			
			
			
			if ("mobile_image_one" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->mobile_image_one);
				
			}
			
			
			
			
			if ("mobile_image_two" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->mobile_image_two);
				
			}
			
			
			
			
			if ("mobile_show_from" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->mobile_show_from);
				
			}
			
			
			
			
			if ("mobile_show_to" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->mobile_show_to);
				
			}
			
			
			
			
			if ("pc_show_from" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->pc_show_from);
				
			}
			
			
			
			
			if ("pc_show_to" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->pc_show_to);
				
			}
			
			
			
			
			if ("channels" == $schemeField){
				
				$needSkip = false;
				
				$this->channels = array();
				$_size4 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem4 = null;
						$input->readString($elem4);
						
						$this->channels[$_size4++] = $elem4;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readListEnd();
				
			}
			
			
			
			
			if ("warehouses" == $schemeField){
				
				$needSkip = false;
				
				$this->warehouses = array();
				$_size5 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem5 = null;
						$input->readString($elem5);
						
						$this->warehouses[$_size5++] = $elem5;
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
		
		$xfer += $output->writeFieldBegin('schedule_id');
		$xfer += $output->writeI32($this->schedule_id);
		
		$xfer += $output->writeFieldEnd();
		
		$xfer += $output->writeFieldBegin('schedule_name');
		$xfer += $output->writeString($this->schedule_name);
		
		$xfer += $output->writeFieldEnd();
		
		$xfer += $output->writeFieldBegin('start_time');
		$xfer += $output->writeString($this->start_time);
		
		$xfer += $output->writeFieldEnd();
		
		$xfer += $output->writeFieldBegin('end_time');
		$xfer += $output->writeString($this->end_time);
		
		$xfer += $output->writeFieldEnd();
		
		if($this->index_image !== null) {
			
			$xfer += $output->writeFieldBegin('index_image');
			$xfer += $output->writeString($this->index_image);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->index_advance_image !== null) {
			
			$xfer += $output->writeFieldBegin('index_advance_image');
			$xfer += $output->writeString($this->index_advance_image);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->schedule_self_logo !== null) {
			
			$xfer += $output->writeFieldBegin('schedule_self_logo');
			$xfer += $output->writeString($this->schedule_self_logo);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->logo !== null) {
			
			$xfer += $output->writeFieldBegin('logo');
			$xfer += $output->writeString($this->logo);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->agio !== null) {
			
			$xfer += $output->writeFieldBegin('agio');
			$xfer += $output->writeString($this->agio);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->brand_store_sn !== null) {
			
			$xfer += $output->writeFieldBegin('brand_store_sn');
			
			if (!is_array($this->brand_store_sn)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeListBegin();
			foreach ($this->brand_store_sn as $iter0){
				
				$xfer += $output->writeString($iter0);
				
			}
			
			$output->writeListEnd();
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->brand_name !== null) {
			
			$xfer += $output->writeFieldBegin('brand_name');
			
			if (!is_array($this->brand_name)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeListBegin();
			foreach ($this->brand_name as $iter0){
				
				$xfer += $output->writeString($iter0);
				
			}
			
			$output->writeListEnd();
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->brand_name_eng !== null) {
			
			$xfer += $output->writeFieldBegin('brand_name_eng');
			
			if (!is_array($this->brand_name_eng)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeListBegin();
			foreach ($this->brand_name_eng as $iter0){
				
				$xfer += $output->writeString($iter0);
				
			}
			
			$output->writeListEnd();
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->brand_url !== null) {
			
			$xfer += $output->writeFieldBegin('brand_url');
			
			if (!is_array($this->brand_url)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeListBegin();
			foreach ($this->brand_url as $iter0){
				
				$xfer += $output->writeString($iter0);
				
			}
			
			$output->writeListEnd();
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->schedule_url !== null) {
			
			$xfer += $output->writeFieldBegin('schedule_url');
			$xfer += $output->writeString($this->schedule_url);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->schedule_flash !== null) {
			
			$xfer += $output->writeFieldBegin('schedule_flash');
			$xfer += $output->writeString($this->schedule_flash);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->schedule_mobile_url !== null) {
			
			$xfer += $output->writeFieldBegin('schedule_mobile_url');
			$xfer += $output->writeString($this->schedule_mobile_url);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->mobile_image_one !== null) {
			
			$xfer += $output->writeFieldBegin('mobile_image_one');
			$xfer += $output->writeString($this->mobile_image_one);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->mobile_image_two !== null) {
			
			$xfer += $output->writeFieldBegin('mobile_image_two');
			$xfer += $output->writeString($this->mobile_image_two);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldBegin('mobile_show_from');
		$xfer += $output->writeString($this->mobile_show_from);
		
		$xfer += $output->writeFieldEnd();
		
		$xfer += $output->writeFieldBegin('mobile_show_to');
		$xfer += $output->writeString($this->mobile_show_to);
		
		$xfer += $output->writeFieldEnd();
		
		$xfer += $output->writeFieldBegin('pc_show_from');
		$xfer += $output->writeString($this->pc_show_from);
		
		$xfer += $output->writeFieldEnd();
		
		$xfer += $output->writeFieldBegin('pc_show_to');
		$xfer += $output->writeString($this->pc_show_to);
		
		$xfer += $output->writeFieldEnd();
		
		if($this->channels !== null) {
			
			$xfer += $output->writeFieldBegin('channels');
			
			if (!is_array($this->channels)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeListBegin();
			foreach ($this->channels as $iter0){
				
				$xfer += $output->writeString($iter0);
				
			}
			
			$output->writeListEnd();
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->warehouses !== null) {
			
			$xfer += $output->writeFieldBegin('warehouses');
			
			if (!is_array($this->warehouses)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeListBegin();
			foreach ($this->warehouses as $iter0){
				
				$xfer += $output->writeString($iter0);
				
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