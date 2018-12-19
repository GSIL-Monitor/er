<?php


/*
* Copyright (c) 2008-2015 vip.com, All Rights Reserved.
* Powered by com.vip.osp.idlc-1.2.0.
* Generation Time: Wed Apr 22 19:26:00 CST 2015.
*/

namespace vipapis\product;

class SizeTable {
	
	static $_TSPEC;
	public $size_table_id = null;
	public $size_table_type = null;
	public $size_table_detail = null;
	public $size_table_html = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'size_table_id'
			),
			2 => array(
			'var' => 'size_table_type'
			),
			3 => array(
			'var' => 'size_table_detail'
			),
			4 => array(
			'var' => 'size_table_html'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['size_table_id'])){
				
				$this->size_table_id = $vals['size_table_id'];
			}
			
			
			if (isset($vals['size_table_type'])){
				
				$this->size_table_type = $vals['size_table_type'];
			}
			
			
			if (isset($vals['size_table_detail'])){
				
				$this->size_table_detail = $vals['size_table_detail'];
			}
			
			
			if (isset($vals['size_table_html'])){
				
				$this->size_table_html = $vals['size_table_html'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'SizeTable';
	}
	
	public function read($input){
		
		$input->readStructBegin();
		while(true){
			
			$schemeField = $input->readFieldBegin();
			if ($schemeField == null) break;
			$needSkip = true;
			
			
			if ("size_table_id" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->size_table_id); 
				
			}
			
			
			
			
			if ("size_table_type" == $schemeField){
				
				$needSkip = false;
				$input->readI16($this->size_table_type); 
				
			}
			
			
			
			
			if ("size_table_detail" == $schemeField){
				
				$needSkip = false;
				
				$this->size_table_detail = array();
				$input->readMapBegin();
				while(true){
					
					try{
						
						$key0 = '';
						$input->readString($key0);
						
						$val0 = null;
						
						$val0 = array();
						$input->readMapBegin();
						while(true){
							
							try{
								
								$key1 = '';
								$input->readString($key1);
								
								$val1 = '';
								$input->readString($val1);
								
								$val0[$key1] = $val1;
							}
							catch(\Exception $e){
								
								break;
							}
						}
						
						$input->readMapEnd();
						
						$this->size_table_detail[$key0] = $val0;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readMapEnd();
				
			}
			
			
			
			
			if ("size_table_html" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->size_table_html);
				
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
		
		if($this->size_table_id !== null) {
			
			$xfer += $output->writeFieldBegin('size_table_id');
			$xfer += $output->writeI64($this->size_table_id);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->size_table_type !== null) {
			
			$xfer += $output->writeFieldBegin('size_table_type');
			$xfer += $output->writeI16($this->size_table_type);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->size_table_detail !== null) {
			
			$xfer += $output->writeFieldBegin('size_table_detail');
			
			if (!is_array($this->size_table_detail)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeMapBegin();
			foreach ($this->size_table_detail as $kiter0 => $viter0){
				
				$xfer += $output->writeString($kiter0);
				
				
				if (!is_array($viter0)){
					
					throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
				}
				
				$output->writeMapBegin();
				foreach ($viter0 as $kiter1 => $viter1){
					
					$xfer += $output->writeString($kiter1);
					
					$xfer += $output->writeString($viter1);
					
				}
				
				$output->writeMapEnd();
				
			}
			
			$output->writeMapEnd();
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->size_table_html !== null) {
			
			$xfer += $output->writeFieldBegin('size_table_html');
			$xfer += $output->writeString($this->size_table_html);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}

?>