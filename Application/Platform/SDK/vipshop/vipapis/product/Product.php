<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace vipapis\product;

class Product {
	
	static $_TSPEC;
	public $schedule_id = null;
	public $product_id = null;
	public $product_name = null;
	public $brand_store_sn = null;
	public $brand_name = null;
	public $brand_name_eng = null;
	public $brand_url = null;
	public $market_price = null;
	public $sell_price = null;
	public $agio = null;
	public $has_stock = null;
	public $product_url = null;
	public $small_image = null;
	public $product_image = null;
	public $show_image = null;
	public $product_mobile_url = null;
	public $product_mobile_image = null;
	public $category_id = null;
	public $nav_category_id1 = null;
	public $nav_category_id2 = null;
	public $nav_category_id3 = null;
	public $nav_first_name = null;
	public $nav_second_name = null;
	public $nav_third_name = null;
	public $warehouses = null;
	public $sell_time_from = null;
	public $sell_time_to = null;
	public $pc_show_from = null;
	public $pc_show_to = null;
	public $mobile_show_from = null;
	public $mobile_show_to = null;
	public $channels = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'schedule_id'
			),
			2 => array(
			'var' => 'product_id'
			),
			3 => array(
			'var' => 'product_name'
			),
			4 => array(
			'var' => 'brand_store_sn'
			),
			5 => array(
			'var' => 'brand_name'
			),
			6 => array(
			'var' => 'brand_name_eng'
			),
			7 => array(
			'var' => 'brand_url'
			),
			8 => array(
			'var' => 'market_price'
			),
			9 => array(
			'var' => 'sell_price'
			),
			10 => array(
			'var' => 'agio'
			),
			11 => array(
			'var' => 'has_stock'
			),
			12 => array(
			'var' => 'product_url'
			),
			13 => array(
			'var' => 'small_image'
			),
			14 => array(
			'var' => 'product_image'
			),
			15 => array(
			'var' => 'show_image'
			),
			16 => array(
			'var' => 'product_mobile_url'
			),
			17 => array(
			'var' => 'product_mobile_image'
			),
			18 => array(
			'var' => 'category_id'
			),
			19 => array(
			'var' => 'nav_category_id1'
			),
			20 => array(
			'var' => 'nav_category_id2'
			),
			21 => array(
			'var' => 'nav_category_id3'
			),
			22 => array(
			'var' => 'nav_first_name'
			),
			23 => array(
			'var' => 'nav_second_name'
			),
			24 => array(
			'var' => 'nav_third_name'
			),
			25 => array(
			'var' => 'warehouses'
			),
			26 => array(
			'var' => 'sell_time_from'
			),
			27 => array(
			'var' => 'sell_time_to'
			),
			28 => array(
			'var' => 'pc_show_from'
			),
			29 => array(
			'var' => 'pc_show_to'
			),
			30 => array(
			'var' => 'mobile_show_from'
			),
			31 => array(
			'var' => 'mobile_show_to'
			),
			32 => array(
			'var' => 'channels'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['schedule_id'])){
				
				$this->schedule_id = $vals['schedule_id'];
			}
			
			
			if (isset($vals['product_id'])){
				
				$this->product_id = $vals['product_id'];
			}
			
			
			if (isset($vals['product_name'])){
				
				$this->product_name = $vals['product_name'];
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
			
			
			if (isset($vals['market_price'])){
				
				$this->market_price = $vals['market_price'];
			}
			
			
			if (isset($vals['sell_price'])){
				
				$this->sell_price = $vals['sell_price'];
			}
			
			
			if (isset($vals['agio'])){
				
				$this->agio = $vals['agio'];
			}
			
			
			if (isset($vals['has_stock'])){
				
				$this->has_stock = $vals['has_stock'];
			}
			
			
			if (isset($vals['product_url'])){
				
				$this->product_url = $vals['product_url'];
			}
			
			
			if (isset($vals['small_image'])){
				
				$this->small_image = $vals['small_image'];
			}
			
			
			if (isset($vals['product_image'])){
				
				$this->product_image = $vals['product_image'];
			}
			
			
			if (isset($vals['show_image'])){
				
				$this->show_image = $vals['show_image'];
			}
			
			
			if (isset($vals['product_mobile_url'])){
				
				$this->product_mobile_url = $vals['product_mobile_url'];
			}
			
			
			if (isset($vals['product_mobile_image'])){
				
				$this->product_mobile_image = $vals['product_mobile_image'];
			}
			
			
			if (isset($vals['category_id'])){
				
				$this->category_id = $vals['category_id'];
			}
			
			
			if (isset($vals['nav_category_id1'])){
				
				$this->nav_category_id1 = $vals['nav_category_id1'];
			}
			
			
			if (isset($vals['nav_category_id2'])){
				
				$this->nav_category_id2 = $vals['nav_category_id2'];
			}
			
			
			if (isset($vals['nav_category_id3'])){
				
				$this->nav_category_id3 = $vals['nav_category_id3'];
			}
			
			
			if (isset($vals['nav_first_name'])){
				
				$this->nav_first_name = $vals['nav_first_name'];
			}
			
			
			if (isset($vals['nav_second_name'])){
				
				$this->nav_second_name = $vals['nav_second_name'];
			}
			
			
			if (isset($vals['nav_third_name'])){
				
				$this->nav_third_name = $vals['nav_third_name'];
			}
			
			
			if (isset($vals['warehouses'])){
				
				$this->warehouses = $vals['warehouses'];
			}
			
			
			if (isset($vals['sell_time_from'])){
				
				$this->sell_time_from = $vals['sell_time_from'];
			}
			
			
			if (isset($vals['sell_time_to'])){
				
				$this->sell_time_to = $vals['sell_time_to'];
			}
			
			
			if (isset($vals['pc_show_from'])){
				
				$this->pc_show_from = $vals['pc_show_from'];
			}
			
			
			if (isset($vals['pc_show_to'])){
				
				$this->pc_show_to = $vals['pc_show_to'];
			}
			
			
			if (isset($vals['mobile_show_from'])){
				
				$this->mobile_show_from = $vals['mobile_show_from'];
			}
			
			
			if (isset($vals['mobile_show_to'])){
				
				$this->mobile_show_to = $vals['mobile_show_to'];
			}
			
			
			if (isset($vals['channels'])){
				
				$this->channels = $vals['channels'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'Product';
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
			
			
			
			
			if ("product_id" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->product_id); 
				
			}
			
			
			
			
			if ("product_name" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->product_name);
				
			}
			
			
			
			
			if ("brand_store_sn" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->brand_store_sn);
				
			}
			
			
			
			
			if ("brand_name" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->brand_name);
				
			}
			
			
			
			
			if ("brand_name_eng" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->brand_name_eng);
				
			}
			
			
			
			
			if ("brand_url" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->brand_url);
				
			}
			
			
			
			
			if ("market_price" == $schemeField){
				
				$needSkip = false;
				$input->readDouble($this->market_price);
				
			}
			
			
			
			
			if ("sell_price" == $schemeField){
				
				$needSkip = false;
				$input->readDouble($this->sell_price);
				
			}
			
			
			
			
			if ("agio" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->agio);
				
			}
			
			
			
			
			if ("has_stock" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->has_stock); 
				
			}
			
			
			
			
			if ("product_url" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->product_url);
				
			}
			
			
			
			
			if ("small_image" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->small_image);
				
			}
			
			
			
			
			if ("product_image" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->product_image);
				
			}
			
			
			
			
			if ("show_image" == $schemeField){
				
				$needSkip = false;
				
				$this->show_image = array();
				$_size0 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem0 = null;
						$input->readString($elem0);
						
						$this->show_image[$_size0++] = $elem0;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readListEnd();
				
			}
			
			
			
			
			if ("product_mobile_url" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->product_mobile_url);
				
			}
			
			
			
			
			if ("product_mobile_image" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->product_mobile_image);
				
			}
			
			
			
			
			if ("category_id" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->category_id); 
				
			}
			
			
			
			
			if ("nav_category_id1" == $schemeField){
				
				$needSkip = false;
				
				$this->nav_category_id1 = array();
				$_size1 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem1 = null;
						$input->readString($elem1);
						
						$this->nav_category_id1[$_size1++] = $elem1;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readListEnd();
				
			}
			
			
			
			
			if ("nav_category_id2" == $schemeField){
				
				$needSkip = false;
				
				$this->nav_category_id2 = array();
				$_size2 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem2 = null;
						$input->readString($elem2);
						
						$this->nav_category_id2[$_size2++] = $elem2;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readListEnd();
				
			}
			
			
			
			
			if ("nav_category_id3" == $schemeField){
				
				$needSkip = false;
				
				$this->nav_category_id3 = array();
				$_size3 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem3 = null;
						$input->readString($elem3);
						
						$this->nav_category_id3[$_size3++] = $elem3;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readListEnd();
				
			}
			
			
			
			
			if ("nav_first_name" == $schemeField){
				
				$needSkip = false;
				
				$this->nav_first_name = array();
				$_size4 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem4 = null;
						$input->readString($elem4);
						
						$this->nav_first_name[$_size4++] = $elem4;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readListEnd();
				
			}
			
			
			
			
			if ("nav_second_name" == $schemeField){
				
				$needSkip = false;
				
				$this->nav_second_name = array();
				$_size5 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem5 = null;
						$input->readString($elem5);
						
						$this->nav_second_name[$_size5++] = $elem5;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readListEnd();
				
			}
			
			
			
			
			if ("nav_third_name" == $schemeField){
				
				$needSkip = false;
				
				$this->nav_third_name = array();
				$_size6 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem6 = null;
						$input->readString($elem6);
						
						$this->nav_third_name[$_size6++] = $elem6;
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
				$_size7 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem7 = null;
						$input->readString($elem7);
						
						$this->warehouses[$_size7++] = $elem7;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readListEnd();
				
			}
			
			
			
			
			if ("sell_time_from" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->sell_time_from);
				
			}
			
			
			
			
			if ("sell_time_to" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->sell_time_to);
				
			}
			
			
			
			
			if ("pc_show_from" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->pc_show_from);
				
			}
			
			
			
			
			if ("pc_show_to" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->pc_show_to);
				
			}
			
			
			
			
			if ("mobile_show_from" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->mobile_show_from);
				
			}
			
			
			
			
			if ("mobile_show_to" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->mobile_show_to);
				
			}
			
			
			
			
			if ("channels" == $schemeField){
				
				$needSkip = false;
				
				$this->channels = array();
				$_size8 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem8 = null;
						$input->readString($elem8);
						
						$this->channels[$_size8++] = $elem8;
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
		
		$xfer += $output->writeFieldBegin('product_id');
		$xfer += $output->writeI32($this->product_id);
		
		$xfer += $output->writeFieldEnd();
		
		$xfer += $output->writeFieldBegin('product_name');
		$xfer += $output->writeString($this->product_name);
		
		$xfer += $output->writeFieldEnd();
		
		if($this->brand_store_sn !== null) {
			
			$xfer += $output->writeFieldBegin('brand_store_sn');
			$xfer += $output->writeString($this->brand_store_sn);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->brand_name !== null) {
			
			$xfer += $output->writeFieldBegin('brand_name');
			$xfer += $output->writeString($this->brand_name);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->brand_name_eng !== null) {
			
			$xfer += $output->writeFieldBegin('brand_name_eng');
			$xfer += $output->writeString($this->brand_name_eng);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->brand_url !== null) {
			
			$xfer += $output->writeFieldBegin('brand_url');
			$xfer += $output->writeString($this->brand_url);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldBegin('market_price');
		$xfer += $output->writeDouble($this->market_price);
		
		$xfer += $output->writeFieldEnd();
		
		$xfer += $output->writeFieldBegin('sell_price');
		$xfer += $output->writeDouble($this->sell_price);
		
		$xfer += $output->writeFieldEnd();
		
		if($this->agio !== null) {
			
			$xfer += $output->writeFieldBegin('agio');
			$xfer += $output->writeString($this->agio);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldBegin('has_stock');
		$xfer += $output->writeI32($this->has_stock);
		
		$xfer += $output->writeFieldEnd();
		
		if($this->product_url !== null) {
			
			$xfer += $output->writeFieldBegin('product_url');
			$xfer += $output->writeString($this->product_url);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->small_image !== null) {
			
			$xfer += $output->writeFieldBegin('small_image');
			$xfer += $output->writeString($this->small_image);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->product_image !== null) {
			
			$xfer += $output->writeFieldBegin('product_image');
			$xfer += $output->writeString($this->product_image);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->show_image !== null) {
			
			$xfer += $output->writeFieldBegin('show_image');
			
			if (!is_array($this->show_image)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeListBegin();
			foreach ($this->show_image as $iter0){
				
				$xfer += $output->writeString($iter0);
				
			}
			
			$output->writeListEnd();
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->product_mobile_url !== null) {
			
			$xfer += $output->writeFieldBegin('product_mobile_url');
			$xfer += $output->writeString($this->product_mobile_url);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->product_mobile_image !== null) {
			
			$xfer += $output->writeFieldBegin('product_mobile_image');
			$xfer += $output->writeString($this->product_mobile_image);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->category_id !== null) {
			
			$xfer += $output->writeFieldBegin('category_id');
			$xfer += $output->writeI32($this->category_id);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->nav_category_id1 !== null) {
			
			$xfer += $output->writeFieldBegin('nav_category_id1');
			
			if (!is_array($this->nav_category_id1)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeListBegin();
			foreach ($this->nav_category_id1 as $iter0){
				
				$xfer += $output->writeString($iter0);
				
			}
			
			$output->writeListEnd();
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->nav_category_id2 !== null) {
			
			$xfer += $output->writeFieldBegin('nav_category_id2');
			
			if (!is_array($this->nav_category_id2)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeListBegin();
			foreach ($this->nav_category_id2 as $iter0){
				
				$xfer += $output->writeString($iter0);
				
			}
			
			$output->writeListEnd();
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->nav_category_id3 !== null) {
			
			$xfer += $output->writeFieldBegin('nav_category_id3');
			
			if (!is_array($this->nav_category_id3)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeListBegin();
			foreach ($this->nav_category_id3 as $iter0){
				
				$xfer += $output->writeString($iter0);
				
			}
			
			$output->writeListEnd();
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->nav_first_name !== null) {
			
			$xfer += $output->writeFieldBegin('nav_first_name');
			
			if (!is_array($this->nav_first_name)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeListBegin();
			foreach ($this->nav_first_name as $iter0){
				
				$xfer += $output->writeString($iter0);
				
			}
			
			$output->writeListEnd();
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->nav_second_name !== null) {
			
			$xfer += $output->writeFieldBegin('nav_second_name');
			
			if (!is_array($this->nav_second_name)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeListBegin();
			foreach ($this->nav_second_name as $iter0){
				
				$xfer += $output->writeString($iter0);
				
			}
			
			$output->writeListEnd();
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->nav_third_name !== null) {
			
			$xfer += $output->writeFieldBegin('nav_third_name');
			
			if (!is_array($this->nav_third_name)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeListBegin();
			foreach ($this->nav_third_name as $iter0){
				
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
		
		
		if($this->sell_time_from !== null) {
			
			$xfer += $output->writeFieldBegin('sell_time_from');
			$xfer += $output->writeString($this->sell_time_from);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->sell_time_to !== null) {
			
			$xfer += $output->writeFieldBegin('sell_time_to');
			$xfer += $output->writeString($this->sell_time_to);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->pc_show_from !== null) {
			
			$xfer += $output->writeFieldBegin('pc_show_from');
			$xfer += $output->writeString($this->pc_show_from);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->pc_show_to !== null) {
			
			$xfer += $output->writeFieldBegin('pc_show_to');
			$xfer += $output->writeString($this->pc_show_to);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->mobile_show_from !== null) {
			
			$xfer += $output->writeFieldBegin('mobile_show_from');
			$xfer += $output->writeString($this->mobile_show_from);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->mobile_show_to !== null) {
			
			$xfer += $output->writeFieldBegin('mobile_show_to');
			$xfer += $output->writeString($this->mobile_show_to);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
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
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}

?>