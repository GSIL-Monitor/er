<?php


/*
* Copyright (c) 2008-2015 vip.com, All Rights Reserved.
* Powered by com.vip.osp.idlc-1.2.0.
* Generation Time: Wed Apr 22 19:26:00 CST 2015.
*/

namespace vipapis\product;

class ChannelProduct {
	
	static $_TSPEC;
	public $vendor_spu_id = null;
	public $schedule_id = null;
	public $sell_time_from = null;
	public $sell_time_to = null;
	public $art_no = null;
	public $product_id = null;
	public $product_name = null;
	public $sell_price = null;
	public $market_price = null;
	public $agio = null;
	public $status = null;
	public $standard = null;
	public $washing_instruct = null;
	public $color = null;
	public $material = null;
	public $accessory_info = null;
	public $product_description = null;
	public $weight_type = null;
	public $title_big = null;
	public $title_small = null;
	public $sale_service = null;
	public $area_output = null;
	public $brand_id = null;
	public $brand_name = null;
	public $brand_name_eng = null;
	public $brand_url = null;
	public $warehouses = null;
	public $size = null;
	public $size_table_id = null;
	public $point_describe = null;
	public $product_url = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'vendor_spu_id'
			),
			2 => array(
			'var' => 'schedule_id'
			),
			3 => array(
			'var' => 'sell_time_from'
			),
			4 => array(
			'var' => 'sell_time_to'
			),
			5 => array(
			'var' => 'art_no'
			),
			6 => array(
			'var' => 'product_id'
			),
			7 => array(
			'var' => 'product_name'
			),
			8 => array(
			'var' => 'sell_price'
			),
			9 => array(
			'var' => 'market_price'
			),
			10 => array(
			'var' => 'agio'
			),
			11 => array(
			'var' => 'status'
			),
			12 => array(
			'var' => 'standard'
			),
			13 => array(
			'var' => 'washing_instruct'
			),
			14 => array(
			'var' => 'color'
			),
			15 => array(
			'var' => 'material'
			),
			16 => array(
			'var' => 'accessory_info'
			),
			17 => array(
			'var' => 'product_description'
			),
			18 => array(
			'var' => 'weight_type'
			),
			19 => array(
			'var' => 'title_big'
			),
			20 => array(
			'var' => 'title_small'
			),
			21 => array(
			'var' => 'sale_service'
			),
			22 => array(
			'var' => 'area_output'
			),
			23 => array(
			'var' => 'brand_id'
			),
			24 => array(
			'var' => 'brand_name'
			),
			25 => array(
			'var' => 'brand_name_eng'
			),
			26 => array(
			'var' => 'brand_url'
			),
			27 => array(
			'var' => 'warehouses'
			),
			28 => array(
			'var' => 'size'
			),
			29 => array(
			'var' => 'size_table_id'
			),
			30 => array(
			'var' => 'point_describe'
			),
			31 => array(
			'var' => 'product_url'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['vendor_spu_id'])){
				
				$this->vendor_spu_id = $vals['vendor_spu_id'];
			}
			
			
			if (isset($vals['schedule_id'])){
				
				$this->schedule_id = $vals['schedule_id'];
			}
			
			
			if (isset($vals['sell_time_from'])){
				
				$this->sell_time_from = $vals['sell_time_from'];
			}
			
			
			if (isset($vals['sell_time_to'])){
				
				$this->sell_time_to = $vals['sell_time_to'];
			}
			
			
			if (isset($vals['art_no'])){
				
				$this->art_no = $vals['art_no'];
			}
			
			
			if (isset($vals['product_id'])){
				
				$this->product_id = $vals['product_id'];
			}
			
			
			if (isset($vals['product_name'])){
				
				$this->product_name = $vals['product_name'];
			}
			
			
			if (isset($vals['sell_price'])){
				
				$this->sell_price = $vals['sell_price'];
			}
			
			
			if (isset($vals['market_price'])){
				
				$this->market_price = $vals['market_price'];
			}
			
			
			if (isset($vals['agio'])){
				
				$this->agio = $vals['agio'];
			}
			
			
			if (isset($vals['status'])){
				
				$this->status = $vals['status'];
			}
			
			
			if (isset($vals['standard'])){
				
				$this->standard = $vals['standard'];
			}
			
			
			if (isset($vals['washing_instruct'])){
				
				$this->washing_instruct = $vals['washing_instruct'];
			}
			
			
			if (isset($vals['color'])){
				
				$this->color = $vals['color'];
			}
			
			
			if (isset($vals['material'])){
				
				$this->material = $vals['material'];
			}
			
			
			if (isset($vals['accessory_info'])){
				
				$this->accessory_info = $vals['accessory_info'];
			}
			
			
			if (isset($vals['product_description'])){
				
				$this->product_description = $vals['product_description'];
			}
			
			
			if (isset($vals['weight_type'])){
				
				$this->weight_type = $vals['weight_type'];
			}
			
			
			if (isset($vals['title_big'])){
				
				$this->title_big = $vals['title_big'];
			}
			
			
			if (isset($vals['title_small'])){
				
				$this->title_small = $vals['title_small'];
			}
			
			
			if (isset($vals['sale_service'])){
				
				$this->sale_service = $vals['sale_service'];
			}
			
			
			if (isset($vals['area_output'])){
				
				$this->area_output = $vals['area_output'];
			}
			
			
			if (isset($vals['brand_id'])){
				
				$this->brand_id = $vals['brand_id'];
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
			
			
			if (isset($vals['warehouses'])){
				
				$this->warehouses = $vals['warehouses'];
			}
			
			
			if (isset($vals['size'])){
				
				$this->size = $vals['size'];
			}
			
			
			if (isset($vals['size_table_id'])){
				
				$this->size_table_id = $vals['size_table_id'];
			}
			
			
			if (isset($vals['point_describe'])){
				
				$this->point_describe = $vals['point_describe'];
			}
			
			
			if (isset($vals['product_url'])){
				
				$this->product_url = $vals['product_url'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'ChannelProduct';
	}
	
	public function read($input){
		
		$input->readStructBegin();
		while(true){
			
			$schemeField = $input->readFieldBegin();
			if ($schemeField == null) break;
			$needSkip = true;
			
			
			if ("vendor_spu_id" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->vendor_spu_id);
				
			}
			
			
			
			
			if ("schedule_id" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->schedule_id); 
				
			}
			
			
			
			
			if ("sell_time_from" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->sell_time_from);
				
			}
			
			
			
			
			if ("sell_time_to" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->sell_time_to);
				
			}
			
			
			
			
			if ("art_no" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->art_no);
				
			}
			
			
			
			
			if ("product_id" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->product_id); 
				
			}
			
			
			
			
			if ("product_name" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->product_name);
				
			}
			
			
			
			
			if ("sell_price" == $schemeField){
				
				$needSkip = false;
				$input->readDouble($this->sell_price);
				
			}
			
			
			
			
			if ("market_price" == $schemeField){
				
				$needSkip = false;
				$input->readDouble($this->market_price);
				
			}
			
			
			
			
			if ("agio" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->agio);
				
			}
			
			
			
			
			if ("status" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->status);
				
			}
			
			
			
			
			if ("standard" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->standard);
				
			}
			
			
			
			
			if ("washing_instruct" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->washing_instruct);
				
			}
			
			
			
			
			if ("color" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->color);
				
			}
			
			
			
			
			if ("material" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->material);
				
			}
			
			
			
			
			if ("accessory_info" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->accessory_info);
				
			}
			
			
			
			
			if ("product_description" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->product_description);
				
			}
			
			
			
			
			if ("weight_type" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->weight_type);
				
			}
			
			
			
			
			if ("title_big" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->title_big);
				
			}
			
			
			
			
			if ("title_small" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->title_small);
				
			}
			
			
			
			
			if ("sale_service" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->sale_service);
				
			}
			
			
			
			
			if ("area_output" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->area_output);
				
			}
			
			
			
			
			if ("brand_id" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->brand_id);
				
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
			
			
			
			
			if ("warehouses" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->warehouses);
				
			}
			
			
			
			
			if ("size" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->size);
				
			}
			
			
			
			
			if ("size_table_id" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->size_table_id); 
				
			}
			
			
			
			
			if ("point_describe" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->point_describe);
				
			}
			
			
			
			
			if ("product_url" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->product_url);
				
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
		
		$xfer += $output->writeFieldBegin('vendor_spu_id');
		$xfer += $output->writeString($this->vendor_spu_id);
		
		$xfer += $output->writeFieldEnd();
		
		$xfer += $output->writeFieldBegin('schedule_id');
		$xfer += $output->writeI64($this->schedule_id);
		
		$xfer += $output->writeFieldEnd();
		
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
		
		
		if($this->art_no !== null) {
			
			$xfer += $output->writeFieldBegin('art_no');
			$xfer += $output->writeString($this->art_no);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldBegin('product_id');
		$xfer += $output->writeI64($this->product_id);
		
		$xfer += $output->writeFieldEnd();
		
		if($this->product_name !== null) {
			
			$xfer += $output->writeFieldBegin('product_name');
			$xfer += $output->writeString($this->product_name);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldBegin('sell_price');
		$xfer += $output->writeDouble($this->sell_price);
		
		$xfer += $output->writeFieldEnd();
		
		$xfer += $output->writeFieldBegin('market_price');
		$xfer += $output->writeDouble($this->market_price);
		
		$xfer += $output->writeFieldEnd();
		
		if($this->agio !== null) {
			
			$xfer += $output->writeFieldBegin('agio');
			$xfer += $output->writeString($this->agio);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->status !== null) {
			
			$xfer += $output->writeFieldBegin('status');
			$xfer += $output->writeString($this->status);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->standard !== null) {
			
			$xfer += $output->writeFieldBegin('standard');
			$xfer += $output->writeString($this->standard);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->washing_instruct !== null) {
			
			$xfer += $output->writeFieldBegin('washing_instruct');
			$xfer += $output->writeString($this->washing_instruct);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->color !== null) {
			
			$xfer += $output->writeFieldBegin('color');
			$xfer += $output->writeString($this->color);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->material !== null) {
			
			$xfer += $output->writeFieldBegin('material');
			$xfer += $output->writeString($this->material);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->accessory_info !== null) {
			
			$xfer += $output->writeFieldBegin('accessory_info');
			$xfer += $output->writeString($this->accessory_info);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->product_description !== null) {
			
			$xfer += $output->writeFieldBegin('product_description');
			$xfer += $output->writeString($this->product_description);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->weight_type !== null) {
			
			$xfer += $output->writeFieldBegin('weight_type');
			$xfer += $output->writeString($this->weight_type);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->title_big !== null) {
			
			$xfer += $output->writeFieldBegin('title_big');
			$xfer += $output->writeString($this->title_big);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->title_small !== null) {
			
			$xfer += $output->writeFieldBegin('title_small');
			$xfer += $output->writeString($this->title_small);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->sale_service !== null) {
			
			$xfer += $output->writeFieldBegin('sale_service');
			$xfer += $output->writeString($this->sale_service);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->area_output !== null) {
			
			$xfer += $output->writeFieldBegin('area_output');
			$xfer += $output->writeString($this->area_output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->brand_id !== null) {
			
			$xfer += $output->writeFieldBegin('brand_id');
			$xfer += $output->writeString($this->brand_id);
			
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
		
		
		if($this->warehouses !== null) {
			
			$xfer += $output->writeFieldBegin('warehouses');
			$xfer += $output->writeString($this->warehouses);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->size !== null) {
			
			$xfer += $output->writeFieldBegin('size');
			$xfer += $output->writeString($this->size);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->size_table_id !== null) {
			
			$xfer += $output->writeFieldBegin('size_table_id');
			$xfer += $output->writeI64($this->size_table_id);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->point_describe !== null) {
			
			$xfer += $output->writeFieldBegin('point_describe');
			$xfer += $output->writeString($this->point_describe);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->product_url !== null) {
			
			$xfer += $output->writeFieldBegin('product_url');
			$xfer += $output->writeString($this->product_url);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}

?>