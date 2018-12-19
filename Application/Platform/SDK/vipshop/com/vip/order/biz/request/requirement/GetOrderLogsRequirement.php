<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\biz\request\requirement;

class GetOrderLogsRequirement {
	
	static $_TSPEC;
	public $returnFields = null;
	public $limit = null;
	public $offset = null;
	public $orderbyList = null;
	
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
			'var' => 'orderbyList'
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
			
			
			if (isset($vals['orderbyList'])){
				
				$this->orderbyList = $vals['orderbyList'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'GetOrderLogsRequirement';
	}
	
	public function read($input){
		
		$input->readStructBegin();
		while(true){
			
			$schemeField = $input->readFieldBegin();
			if ($schemeField == null) break;
			$needSkip = true;
			
			
			if ("returnFields" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->returnFields);
				
			}
			
			
			
			
			if ("limit" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->limit); 
				
			}
			
			
			
			
			if ("offset" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->offset); 
				
			}
			
			
			
			
			if ("orderbyList" == $schemeField){
				
				$needSkip = false;
				
				$this->orderbyList = array();
				$_size0 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem0 = null;
						
						$elem0 = new \com\vip\order\biz\request\requirement\GetOrderLogstOrderBy();
						$elem0->read($input);
						
						$this->orderbyList[$_size0++] = $elem0;
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
			$xfer += $output->writeString($this->returnFields);
			
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
		
		
		if($this->orderbyList !== null) {
			
			$xfer += $output->writeFieldBegin('orderbyList');
			
			if (!is_array($this->orderbyList)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeListBegin();
			foreach ($this->orderbyList as $iter0){
				
				
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