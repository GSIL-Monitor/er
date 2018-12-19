<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\biz\request;

class GetRdcReq {
	
	static $_TSPEC;
	public $warehouse = null;
	public $merItemNoList = null;
	public $merItemNoWarehouseMap = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'warehouse'
			),
			2 => array(
			'var' => 'merItemNoList'
			),
			3 => array(
			'var' => 'merItemNoWarehouseMap'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['warehouse'])){
				
				$this->warehouse = $vals['warehouse'];
			}
			
			
			if (isset($vals['merItemNoList'])){
				
				$this->merItemNoList = $vals['merItemNoList'];
			}
			
			
			if (isset($vals['merItemNoWarehouseMap'])){
				
				$this->merItemNoWarehouseMap = $vals['merItemNoWarehouseMap'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'GetRdcReq';
	}
	
	public function read($input){
		
		$input->readStructBegin();
		while(true){
			
			$schemeField = $input->readFieldBegin();
			if ($schemeField == null) break;
			$needSkip = true;
			
			
			if ("warehouse" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->warehouse);
				
			}
			
			
			
			
			if ("merItemNoList" == $schemeField){
				
				$needSkip = false;
				
				$this->merItemNoList = array();
				$_size0 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem0 = null;
						$input->readI64($elem0); 
						
						$this->merItemNoList[$_size0++] = $elem0;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readListEnd();
				
			}
			
			
			
			
			if ("merItemNoWarehouseMap" == $schemeField){
				
				$needSkip = false;
				
				$this->merItemNoWarehouseMap = array();
				$input->readMapBegin();
				while(true){
					
					try{
						
						$key1 = 0;
						$input->readI64($key1); 
						
						$val1 = null;
						
						$val1 = array();
						$_size2 = 0;
						$input->readListBegin();
						while(true){
							
							try{
								
								$elem2 = null;
								$input->readString($elem2);
								
								$val1[$_size2++] = $elem2;
							}
							catch(\Exception $e){
								
								break;
							}
						}
						
						$input->readListEnd();
						
						$this->merItemNoWarehouseMap[$key1] = $val1;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readMapEnd();
				
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
		
		if($this->warehouse !== null) {
			
			$xfer += $output->writeFieldBegin('warehouse');
			$xfer += $output->writeString($this->warehouse);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->merItemNoList !== null) {
			
			$xfer += $output->writeFieldBegin('merItemNoList');
			
			if (!is_array($this->merItemNoList)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeListBegin();
			foreach ($this->merItemNoList as $iter0){
				
				$xfer += $output->writeI64($iter0);
				
			}
			
			$output->writeListEnd();
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->merItemNoWarehouseMap !== null) {
			
			$xfer += $output->writeFieldBegin('merItemNoWarehouseMap');
			
			if (!is_array($this->merItemNoWarehouseMap)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeMapBegin();
			foreach ($this->merItemNoWarehouseMap as $kiter0 => $viter0){
				
				$xfer += $output->writeI64($kiter0);
				
				
				if (!is_array($viter0)){
					
					throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
				}
				
				$output->writeListBegin();
				foreach ($viter0 as $iter1){
					
					$xfer += $output->writeString($iter1);
					
				}
				
				$output->writeListEnd();
				
			}
			
			$output->writeMapEnd();
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}

?>