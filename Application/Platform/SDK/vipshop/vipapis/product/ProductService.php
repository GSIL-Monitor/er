<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace vipapis\product;
interface ProductServiceIf{
	
	
	public function getProductList( $warehouse, $schedule_id, $channel_id, $category_id, $start_time, $end_time, $product_id, $product_name, $sell_price_min, $sell_price_max, $discount_min, $discount_max, $sort_type, $stock_show_type, $page, $limit, $cursorMark);
	
	public function getProductStock( $warehouse, $schedule_id, $channel_id, $category_id, $start_time, $end_time, $product_id, $product_name, $sell_price_min, $sell_price_max, $discount_min, $discount_max, $sort_type, $stock_show_type, $page, $limit, $cursorMark);
	
	public function healthCheck();
	
}

class _ProductServiceClient extends \Osp\Base\OspStub implements \vipapis\product\ProductServiceIf{
	
	public function __construct(){
		
		parent::__construct("vipapis.product.ProductService", "1.0.1");
	}
	
	
	public function getProductList( $warehouse, $schedule_id, $channel_id, $category_id, $start_time, $end_time, $product_id, $product_name, $sell_price_min, $sell_price_max, $discount_min, $discount_max, $sort_type, $stock_show_type, $page, $limit, $cursorMark){
		
		$this->send_getProductList( $warehouse, $schedule_id, $channel_id, $category_id, $start_time, $end_time, $product_id, $product_name, $sell_price_min, $sell_price_max, $discount_min, $discount_max, $sort_type, $stock_show_type, $page, $limit, $cursorMark);
		return $this->recv_getProductList();
	}
	
	public function send_getProductList( $warehouse, $schedule_id, $channel_id, $category_id, $start_time, $end_time, $product_id, $product_name, $sell_price_min, $sell_price_max, $discount_min, $discount_max, $sort_type, $stock_show_type, $page, $limit, $cursorMark){
		
		$this->initInvocation("getProductList");
		$args = new \vipapis\product\ProductService_getProductList_args();
		
		$args->warehouse = $warehouse;
		
		$args->schedule_id = $schedule_id;
		
		$args->channel_id = $channel_id;
		
		$args->category_id = $category_id;
		
		$args->start_time = $start_time;
		
		$args->end_time = $end_time;
		
		$args->product_id = $product_id;
		
		$args->product_name = $product_name;
		
		$args->sell_price_min = $sell_price_min;
		
		$args->sell_price_max = $sell_price_max;
		
		$args->discount_min = $discount_min;
		
		$args->discount_max = $discount_max;
		
		$args->sort_type = $sort_type;
		
		$args->stock_show_type = $stock_show_type;
		
		$args->page = $page;
		
		$args->limit = $limit;
		
		$args->cursorMark = $cursorMark;
		
		$this->send_base($args);
	}
	
	public function recv_getProductList(){
		
		$result = new \vipapis\product\ProductService_getProductList_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function getProductStock( $warehouse, $schedule_id, $channel_id, $category_id, $start_time, $end_time, $product_id, $product_name, $sell_price_min, $sell_price_max, $discount_min, $discount_max, $sort_type, $stock_show_type, $page, $limit, $cursorMark){
		
		$this->send_getProductStock( $warehouse, $schedule_id, $channel_id, $category_id, $start_time, $end_time, $product_id, $product_name, $sell_price_min, $sell_price_max, $discount_min, $discount_max, $sort_type, $stock_show_type, $page, $limit, $cursorMark);
		return $this->recv_getProductStock();
	}
	
	public function send_getProductStock( $warehouse, $schedule_id, $channel_id, $category_id, $start_time, $end_time, $product_id, $product_name, $sell_price_min, $sell_price_max, $discount_min, $discount_max, $sort_type, $stock_show_type, $page, $limit, $cursorMark){
		
		$this->initInvocation("getProductStock");
		$args = new \vipapis\product\ProductService_getProductStock_args();
		
		$args->warehouse = $warehouse;
		
		$args->schedule_id = $schedule_id;
		
		$args->channel_id = $channel_id;
		
		$args->category_id = $category_id;
		
		$args->start_time = $start_time;
		
		$args->end_time = $end_time;
		
		$args->product_id = $product_id;
		
		$args->product_name = $product_name;
		
		$args->sell_price_min = $sell_price_min;
		
		$args->sell_price_max = $sell_price_max;
		
		$args->discount_min = $discount_min;
		
		$args->discount_max = $discount_max;
		
		$args->sort_type = $sort_type;
		
		$args->stock_show_type = $stock_show_type;
		
		$args->page = $page;
		
		$args->limit = $limit;
		
		$args->cursorMark = $cursorMark;
		
		$this->send_base($args);
	}
	
	public function recv_getProductStock(){
		
		$result = new \vipapis\product\ProductService_getProductStock_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function healthCheck(){
		
		$this->send_healthCheck();
		return $this->recv_healthCheck();
	}
	
	public function send_healthCheck(){
		
		$this->initInvocation("healthCheck");
		$args = new \vipapis\product\ProductService_healthCheck_args();
		
		$this->send_base($args);
	}
	
	public function recv_healthCheck(){
		
		$result = new \vipapis\product\ProductService_healthCheck_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
}




class ProductService_getProductList_args {
	
	static $_TSPEC;
	public $warehouse = null;
	public $schedule_id = null;
	public $channel_id = null;
	public $category_id = null;
	public $start_time = null;
	public $end_time = null;
	public $product_id = null;
	public $product_name = null;
	public $sell_price_min = null;
	public $sell_price_max = null;
	public $discount_min = null;
	public $discount_max = null;
	public $sort_type = null;
	public $stock_show_type = null;
	public $page = null;
	public $limit = null;
	public $cursorMark = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'warehouse'
			),
			2 => array(
			'var' => 'schedule_id'
			),
			3 => array(
			'var' => 'channel_id'
			),
			4 => array(
			'var' => 'category_id'
			),
			5 => array(
			'var' => 'start_time'
			),
			6 => array(
			'var' => 'end_time'
			),
			7 => array(
			'var' => 'product_id'
			),
			8 => array(
			'var' => 'product_name'
			),
			9 => array(
			'var' => 'sell_price_min'
			),
			10 => array(
			'var' => 'sell_price_max'
			),
			11 => array(
			'var' => 'discount_min'
			),
			12 => array(
			'var' => 'discount_max'
			),
			13 => array(
			'var' => 'sort_type'
			),
			14 => array(
			'var' => 'stock_show_type'
			),
			15 => array(
			'var' => 'page'
			),
			16 => array(
			'var' => 'limit'
			),
			17 => array(
			'var' => 'cursorMark'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['warehouse'])){
				
				$this->warehouse = $vals['warehouse'];
			}
			
			
			if (isset($vals['schedule_id'])){
				
				$this->schedule_id = $vals['schedule_id'];
			}
			
			
			if (isset($vals['channel_id'])){
				
				$this->channel_id = $vals['channel_id'];
			}
			
			
			if (isset($vals['category_id'])){
				
				$this->category_id = $vals['category_id'];
			}
			
			
			if (isset($vals['start_time'])){
				
				$this->start_time = $vals['start_time'];
			}
			
			
			if (isset($vals['end_time'])){
				
				$this->end_time = $vals['end_time'];
			}
			
			
			if (isset($vals['product_id'])){
				
				$this->product_id = $vals['product_id'];
			}
			
			
			if (isset($vals['product_name'])){
				
				$this->product_name = $vals['product_name'];
			}
			
			
			if (isset($vals['sell_price_min'])){
				
				$this->sell_price_min = $vals['sell_price_min'];
			}
			
			
			if (isset($vals['sell_price_max'])){
				
				$this->sell_price_max = $vals['sell_price_max'];
			}
			
			
			if (isset($vals['discount_min'])){
				
				$this->discount_min = $vals['discount_min'];
			}
			
			
			if (isset($vals['discount_max'])){
				
				$this->discount_max = $vals['discount_max'];
			}
			
			
			if (isset($vals['sort_type'])){
				
				$this->sort_type = $vals['sort_type'];
			}
			
			
			if (isset($vals['stock_show_type'])){
				
				$this->stock_show_type = $vals['stock_show_type'];
			}
			
			
			if (isset($vals['page'])){
				
				$this->page = $vals['page'];
			}
			
			
			if (isset($vals['limit'])){
				
				$this->limit = $vals['limit'];
			}
			
			
			if (isset($vals['cursorMark'])){
				
				$this->cursorMark = $vals['cursorMark'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$names = \vipapis\common\Warehouse::$__names;
			$name = null;
			$input->readString($name);
			foreach ($names as $k => $v){
				
				if($name == $v){
					
					$this->warehouse = $k;
					break;
				}
				
			}
			
			
		}
		
		
		
		
		if(true) {
			
			$input->readString($this->schedule_id);
			
		}
		
		
		
		
		if(true) {
			
			$input->readI32($this->channel_id); 
			
		}
		
		
		
		
		if(true) {
			
			$input->readI32($this->category_id); 
			
		}
		
		
		
		
		if(true) {
			
			$input->readString($this->start_time);
			
		}
		
		
		
		
		if(true) {
			
			$input->readString($this->end_time);
			
		}
		
		
		
		
		if(true) {
			
			$input->readString($this->product_id);
			
		}
		
		
		
		
		if(true) {
			
			$input->readString($this->product_name);
			
		}
		
		
		
		
		if(true) {
			
			$input->readI32($this->sell_price_min); 
			
		}
		
		
		
		
		if(true) {
			
			$input->readI32($this->sell_price_max); 
			
		}
		
		
		
		
		if(true) {
			
			$input->readDouble($this->discount_min);
			
		}
		
		
		
		
		if(true) {
			
			$input->readDouble($this->discount_max);
			
		}
		
		
		
		
		if(true) {
			
			
			$names = \vipapis\product\SortType::$__names;
			$name = null;
			$input->readString($name);
			foreach ($names as $k => $v){
				
				if($name == $v){
					
					$this->sort_type = $k;
					break;
				}
				
			}
			
			
		}
		
		
		
		
		if(true) {
			
			
			$names = \vipapis\product\StockShowType::$__names;
			$name = null;
			$input->readString($name);
			foreach ($names as $k => $v){
				
				if($name == $v){
					
					$this->stock_show_type = $k;
					break;
				}
				
			}
			
			
		}
		
		
		
		
		if(true) {
			
			$input->readI32($this->page); 
			
		}
		
		
		
		
		if(true) {
			
			$input->readI32($this->limit); 
			
		}
		
		
		
		
		if(true) {
			
			$input->readString($this->cursorMark);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->warehouse !== null) {
			
			$xfer += $output->writeFieldBegin('warehouse');
			
			$em = new \vipapis\common\Warehouse; 
			$output->writeString($em::$__names[$this->warehouse]);  
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->schedule_id !== null) {
			
			$xfer += $output->writeFieldBegin('schedule_id');
			$xfer += $output->writeString($this->schedule_id);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->channel_id !== null) {
			
			$xfer += $output->writeFieldBegin('channel_id');
			$xfer += $output->writeI32($this->channel_id);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->category_id !== null) {
			
			$xfer += $output->writeFieldBegin('category_id');
			$xfer += $output->writeI32($this->category_id);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->start_time !== null) {
			
			$xfer += $output->writeFieldBegin('start_time');
			$xfer += $output->writeString($this->start_time);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->end_time !== null) {
			
			$xfer += $output->writeFieldBegin('end_time');
			$xfer += $output->writeString($this->end_time);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->product_id !== null) {
			
			$xfer += $output->writeFieldBegin('product_id');
			$xfer += $output->writeString($this->product_id);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->product_name !== null) {
			
			$xfer += $output->writeFieldBegin('product_name');
			$xfer += $output->writeString($this->product_name);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->sell_price_min !== null) {
			
			$xfer += $output->writeFieldBegin('sell_price_min');
			$xfer += $output->writeI32($this->sell_price_min);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->sell_price_max !== null) {
			
			$xfer += $output->writeFieldBegin('sell_price_max');
			$xfer += $output->writeI32($this->sell_price_max);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->discount_min !== null) {
			
			$xfer += $output->writeFieldBegin('discount_min');
			$xfer += $output->writeDouble($this->discount_min);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->discount_max !== null) {
			
			$xfer += $output->writeFieldBegin('discount_max');
			$xfer += $output->writeDouble($this->discount_max);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->sort_type !== null) {
			
			$xfer += $output->writeFieldBegin('sort_type');
			
			$em = new \vipapis\product\SortType; 
			$output->writeString($em::$__names[$this->sort_type]);  
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->stock_show_type !== null) {
			
			$xfer += $output->writeFieldBegin('stock_show_type');
			
			$em = new \vipapis\product\StockShowType; 
			$output->writeString($em::$__names[$this->stock_show_type]);  
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->page !== null) {
			
			$xfer += $output->writeFieldBegin('page');
			$xfer += $output->writeI32($this->page);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->limit !== null) {
			
			$xfer += $output->writeFieldBegin('limit');
			$xfer += $output->writeI32($this->limit);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->cursorMark !== null) {
			
			$xfer += $output->writeFieldBegin('cursorMark');
			$xfer += $output->writeString($this->cursorMark);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class ProductService_getProductStock_args {
	
	static $_TSPEC;
	public $warehouse = null;
	public $schedule_id = null;
	public $channel_id = null;
	public $category_id = null;
	public $start_time = null;
	public $end_time = null;
	public $product_id = null;
	public $product_name = null;
	public $sell_price_min = null;
	public $sell_price_max = null;
	public $discount_min = null;
	public $discount_max = null;
	public $sort_type = null;
	public $stock_show_type = null;
	public $page = null;
	public $limit = null;
	public $cursorMark = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'warehouse'
			),
			2 => array(
			'var' => 'schedule_id'
			),
			3 => array(
			'var' => 'channel_id'
			),
			4 => array(
			'var' => 'category_id'
			),
			5 => array(
			'var' => 'start_time'
			),
			6 => array(
			'var' => 'end_time'
			),
			7 => array(
			'var' => 'product_id'
			),
			8 => array(
			'var' => 'product_name'
			),
			9 => array(
			'var' => 'sell_price_min'
			),
			10 => array(
			'var' => 'sell_price_max'
			),
			11 => array(
			'var' => 'discount_min'
			),
			12 => array(
			'var' => 'discount_max'
			),
			13 => array(
			'var' => 'sort_type'
			),
			14 => array(
			'var' => 'stock_show_type'
			),
			15 => array(
			'var' => 'page'
			),
			16 => array(
			'var' => 'limit'
			),
			17 => array(
			'var' => 'cursorMark'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['warehouse'])){
				
				$this->warehouse = $vals['warehouse'];
			}
			
			
			if (isset($vals['schedule_id'])){
				
				$this->schedule_id = $vals['schedule_id'];
			}
			
			
			if (isset($vals['channel_id'])){
				
				$this->channel_id = $vals['channel_id'];
			}
			
			
			if (isset($vals['category_id'])){
				
				$this->category_id = $vals['category_id'];
			}
			
			
			if (isset($vals['start_time'])){
				
				$this->start_time = $vals['start_time'];
			}
			
			
			if (isset($vals['end_time'])){
				
				$this->end_time = $vals['end_time'];
			}
			
			
			if (isset($vals['product_id'])){
				
				$this->product_id = $vals['product_id'];
			}
			
			
			if (isset($vals['product_name'])){
				
				$this->product_name = $vals['product_name'];
			}
			
			
			if (isset($vals['sell_price_min'])){
				
				$this->sell_price_min = $vals['sell_price_min'];
			}
			
			
			if (isset($vals['sell_price_max'])){
				
				$this->sell_price_max = $vals['sell_price_max'];
			}
			
			
			if (isset($vals['discount_min'])){
				
				$this->discount_min = $vals['discount_min'];
			}
			
			
			if (isset($vals['discount_max'])){
				
				$this->discount_max = $vals['discount_max'];
			}
			
			
			if (isset($vals['sort_type'])){
				
				$this->sort_type = $vals['sort_type'];
			}
			
			
			if (isset($vals['stock_show_type'])){
				
				$this->stock_show_type = $vals['stock_show_type'];
			}
			
			
			if (isset($vals['page'])){
				
				$this->page = $vals['page'];
			}
			
			
			if (isset($vals['limit'])){
				
				$this->limit = $vals['limit'];
			}
			
			
			if (isset($vals['cursorMark'])){
				
				$this->cursorMark = $vals['cursorMark'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$names = \vipapis\common\Warehouse::$__names;
			$name = null;
			$input->readString($name);
			foreach ($names as $k => $v){
				
				if($name == $v){
					
					$this->warehouse = $k;
					break;
				}
				
			}
			
			
		}
		
		
		
		
		if(true) {
			
			$input->readString($this->schedule_id);
			
		}
		
		
		
		
		if(true) {
			
			$input->readI32($this->channel_id); 
			
		}
		
		
		
		
		if(true) {
			
			$input->readI32($this->category_id); 
			
		}
		
		
		
		
		if(true) {
			
			$input->readString($this->start_time);
			
		}
		
		
		
		
		if(true) {
			
			$input->readString($this->end_time);
			
		}
		
		
		
		
		if(true) {
			
			$input->readString($this->product_id);
			
		}
		
		
		
		
		if(true) {
			
			$input->readString($this->product_name);
			
		}
		
		
		
		
		if(true) {
			
			$input->readI32($this->sell_price_min); 
			
		}
		
		
		
		
		if(true) {
			
			$input->readI32($this->sell_price_max); 
			
		}
		
		
		
		
		if(true) {
			
			$input->readDouble($this->discount_min);
			
		}
		
		
		
		
		if(true) {
			
			$input->readDouble($this->discount_max);
			
		}
		
		
		
		
		if(true) {
			
			
			$names = \vipapis\product\SortType::$__names;
			$name = null;
			$input->readString($name);
			foreach ($names as $k => $v){
				
				if($name == $v){
					
					$this->sort_type = $k;
					break;
				}
				
			}
			
			
		}
		
		
		
		
		if(true) {
			
			
			$names = \vipapis\product\StockShowType::$__names;
			$name = null;
			$input->readString($name);
			foreach ($names as $k => $v){
				
				if($name == $v){
					
					$this->stock_show_type = $k;
					break;
				}
				
			}
			
			
		}
		
		
		
		
		if(true) {
			
			$input->readI32($this->page); 
			
		}
		
		
		
		
		if(true) {
			
			$input->readI32($this->limit); 
			
		}
		
		
		
		
		if(true) {
			
			$input->readString($this->cursorMark);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->warehouse !== null) {
			
			$xfer += $output->writeFieldBegin('warehouse');
			
			$em = new \vipapis\common\Warehouse; 
			$output->writeString($em::$__names[$this->warehouse]);  
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->schedule_id !== null) {
			
			$xfer += $output->writeFieldBegin('schedule_id');
			$xfer += $output->writeString($this->schedule_id);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->channel_id !== null) {
			
			$xfer += $output->writeFieldBegin('channel_id');
			$xfer += $output->writeI32($this->channel_id);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->category_id !== null) {
			
			$xfer += $output->writeFieldBegin('category_id');
			$xfer += $output->writeI32($this->category_id);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->start_time !== null) {
			
			$xfer += $output->writeFieldBegin('start_time');
			$xfer += $output->writeString($this->start_time);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->end_time !== null) {
			
			$xfer += $output->writeFieldBegin('end_time');
			$xfer += $output->writeString($this->end_time);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->product_id !== null) {
			
			$xfer += $output->writeFieldBegin('product_id');
			$xfer += $output->writeString($this->product_id);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->product_name !== null) {
			
			$xfer += $output->writeFieldBegin('product_name');
			$xfer += $output->writeString($this->product_name);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->sell_price_min !== null) {
			
			$xfer += $output->writeFieldBegin('sell_price_min');
			$xfer += $output->writeI32($this->sell_price_min);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->sell_price_max !== null) {
			
			$xfer += $output->writeFieldBegin('sell_price_max');
			$xfer += $output->writeI32($this->sell_price_max);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->discount_min !== null) {
			
			$xfer += $output->writeFieldBegin('discount_min');
			$xfer += $output->writeDouble($this->discount_min);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->discount_max !== null) {
			
			$xfer += $output->writeFieldBegin('discount_max');
			$xfer += $output->writeDouble($this->discount_max);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->sort_type !== null) {
			
			$xfer += $output->writeFieldBegin('sort_type');
			
			$em = new \vipapis\product\SortType; 
			$output->writeString($em::$__names[$this->sort_type]);  
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->stock_show_type !== null) {
			
			$xfer += $output->writeFieldBegin('stock_show_type');
			
			$em = new \vipapis\product\StockShowType; 
			$output->writeString($em::$__names[$this->stock_show_type]);  
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->page !== null) {
			
			$xfer += $output->writeFieldBegin('page');
			$xfer += $output->writeI32($this->page);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->limit !== null) {
			
			$xfer += $output->writeFieldBegin('limit');
			$xfer += $output->writeI32($this->limit);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->cursorMark !== null) {
			
			$xfer += $output->writeFieldBegin('cursorMark');
			$xfer += $output->writeString($this->cursorMark);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class ProductService_healthCheck_args {
	
	static $_TSPEC;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			
			);
			
		}
		
		if (is_array($vals)){
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class ProductService_getProductList_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \vipapis\product\GetProductListResponse();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class ProductService_getProductStock_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \vipapis\product\GetProductStockResponse();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class ProductService_healthCheck_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \com\vip\hermes\core\health\CheckResult();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




?>