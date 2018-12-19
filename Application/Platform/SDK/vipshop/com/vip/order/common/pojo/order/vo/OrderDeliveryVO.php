<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\common\pojo\order\vo;

class OrderDeliveryVO {
	
	static $_TSPEC;
	public $addressCode = null;
	public $saleTypeList = null;
	public $orderWarehouse = null;
	public $goodsWarehouseList = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'addressCode'
			),
			2 => array(
			'var' => 'saleTypeList'
			),
			3 => array(
			'var' => 'orderWarehouse'
			),
			4 => array(
			'var' => 'goodsWarehouseList'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['addressCode'])){
				
				$this->addressCode = $vals['addressCode'];
			}
			
			
			if (isset($vals['saleTypeList'])){
				
				$this->saleTypeList = $vals['saleTypeList'];
			}
			
			
			if (isset($vals['orderWarehouse'])){
				
				$this->orderWarehouse = $vals['orderWarehouse'];
			}
			
			
			if (isset($vals['goodsWarehouseList'])){
				
				$this->goodsWarehouseList = $vals['goodsWarehouseList'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'OrderDeliveryVO';
	}
	
	public function read($input){
		
		$input->readStructBegin();
		while(true){
			
			$schemeField = $input->readFieldBegin();
			if ($schemeField == null) break;
			$needSkip = true;
			
			
			if ("addressCode" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->addressCode);
				
			}
			
			
			
			
			if ("saleTypeList" == $schemeField){
				
				$needSkip = false;
				
				$this->saleTypeList = array();
				$_size0 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem0 = null;
						$input->readI32($elem0); 
						
						$this->saleTypeList[$_size0++] = $elem0;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readListEnd();
				
			}
			
			
			
			
			if ("orderWarehouse" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->orderWarehouse);
				
			}
			
			
			
			
			if ("goodsWarehouseList" == $schemeField){
				
				$needSkip = false;
				
				$this->goodsWarehouseList = array();
				$_size1 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem1 = null;
						
						$elem1 = new \com\vip\order\common\pojo\order\vo\GoodsWarehouseVO();
						$elem1->read($input);
						
						$this->goodsWarehouseList[$_size1++] = $elem1;
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
		
		if($this->addressCode !== null) {
			
			$xfer += $output->writeFieldBegin('addressCode');
			$xfer += $output->writeString($this->addressCode);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->saleTypeList !== null) {
			
			$xfer += $output->writeFieldBegin('saleTypeList');
			
			if (!is_array($this->saleTypeList)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeListBegin();
			foreach ($this->saleTypeList as $iter0){
				
				$xfer += $output->writeI32($iter0);
				
			}
			
			$output->writeListEnd();
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderWarehouse !== null) {
			
			$xfer += $output->writeFieldBegin('orderWarehouse');
			$xfer += $output->writeString($this->orderWarehouse);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->goodsWarehouseList !== null) {
			
			$xfer += $output->writeFieldBegin('goodsWarehouseList');
			
			if (!is_array($this->goodsWarehouseList)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeListBegin();
			foreach ($this->goodsWarehouseList as $iter0){
				
				
				if (!is_object($iter0)) {
					
					throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
				}
				
				$xfer += $iter0->write($output);
				
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