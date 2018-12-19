<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\common\pojo\order\request;

class ResultFilter {
	
	static $_TSPEC;
	public $returnFields = null;
	public $limit = null;
	public $offset = null;
	public $orderby = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'returnFields'
			),
			2 => array(
			'var' => 'limit'
			),
			3 => array(
			'var' => 'offset'
			),
			4 => array(
			'var' => 'orderby'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['returnFields'])){
				
				$this->returnFields = $vals['returnFields'];
			}
			
			
			if (isset($vals['limit'])){
				
				$this->limit = $vals['limit'];
			}
			
			
			if (isset($vals['offset'])){
				
				$this->offset = $vals['offset'];
			}
			
			
			if (isset($vals['orderby'])){
				
				$this->orderby = $vals['orderby'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'ResultFilter';
	}
	
	public function read($input){
		
		$input->readStructBegin();
		while(true){
			
			$schemeField = $input->readFieldBegin();
			if ($schemeField == null) break;
			$needSkip = true;
			
			
			if ("returnFields" == $schemeField){
				
				$needSkip = false;
				
				$this->returnFields = array();
				$input->readMapBegin();
				while(true){
					
					try{
						
						$key0 = '';
						$input->readString($key0);
						
						$val0 = '';
						$input->readString($val0);
						
						$this->returnFields[$key0] = $val0;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readMapEnd();
				
			}
			
			
			
			
			if ("limit" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->limit); 
				
			}
			
			
			
			
			if ("offset" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->offset); 
				
			}
			
			
			
			
			if ("orderby" == $schemeField){
				
				$needSkip = false;
				
				$this->orderby = array();
				$_size1 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem1 = null;
						
						$elem1 = new \com\vip\order\common\pojo\order\request\OrderBy();
						$elem1->read($input);
						
						$this->orderby[$_size1++] = $elem1;
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
		
		if($this->returnFields !== null) {
			
			$xfer += $output->writeFieldBegin('returnFields');
			
			if (!is_array($this->returnFields)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeMapBegin();
			foreach ($this->returnFields as $kiter0 => $viter0){
				
				$xfer += $output->writeString($kiter0);
				
				$xfer += $output->writeString($viter0);
				
			}
			
			$output->writeMapEnd();
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->limit !== null) {
			
			$xfer += $output->writeFieldBegin('limit');
			$xfer += $output->writeI32($this->limit);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->offset !== null) {
			
			$xfer += $output->writeFieldBegin('offset');
			$xfer += $output->writeI32($this->offset);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderby !== null) {
			
			$xfer += $output->writeFieldBegin('orderby');
			
			if (!is_array($this->orderby)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeListBegin();
			foreach ($this->orderby as $iter0){
				
				
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