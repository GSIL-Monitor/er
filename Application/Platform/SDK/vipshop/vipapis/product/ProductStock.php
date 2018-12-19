<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace vipapis\product;

class ProductStock {
	
	static $_TSPEC;
	public $product_id = null;
	public $product_name = null;
	public $sell_price = null;
	public $hasStock = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'product_id'
			),
			2 => array(
			'var' => 'product_name'
			),
			3 => array(
			'var' => 'sell_price'
			),
			4 => array(
			'var' => 'hasStock'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['product_id'])){
				
				$this->product_id = $vals['product_id'];
			}
			
			
			if (isset($vals['product_name'])){
				
				$this->product_name = $vals['product_name'];
			}
			
			
			if (isset($vals['sell_price'])){
				
				$this->sell_price = $vals['sell_price'];
			}
			
			
			if (isset($vals['hasStock'])){
				
				$this->hasStock = $vals['hasStock'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'ProductStock';
	}
	
	public function read($input){
		
		$input->readStructBegin();
		while(true){
			
			$schemeField = $input->readFieldBegin();
			if ($schemeField == null) break;
			$needSkip = true;
			
			
			if ("product_id" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->product_id); 
				
			}
			
			
			
			
			if ("product_name" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->product_name);
				
			}
			
			
			
			
			if ("sell_price" == $schemeField){
				
				$needSkip = false;
				$input->readDouble($this->sell_price);
				
			}
			
			
			
			
			if ("hasStock" == $schemeField){
				
				$needSkip = false;
				$input->readBool($this->hasStock);
				
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
		
		$xfer += $output->writeFieldBegin('product_id');
		$xfer += $output->writeI32($this->product_id);
		
		$xfer += $output->writeFieldEnd();
		
		$xfer += $output->writeFieldBegin('product_name');
		$xfer += $output->writeString($this->product_name);
		
		$xfer += $output->writeFieldEnd();
		
		$xfer += $output->writeFieldBegin('sell_price');
		$xfer += $output->writeDouble($this->sell_price);
		
		$xfer += $output->writeFieldEnd();
		
		$xfer += $output->writeFieldBegin('hasStock');
		$xfer += $output->writeBool($this->hasStock);
		
		$xfer += $output->writeFieldEnd();
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}

?>