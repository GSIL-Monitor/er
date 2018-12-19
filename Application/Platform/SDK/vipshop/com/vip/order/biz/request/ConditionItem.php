<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\biz\request;

class ConditionItem {
	
	static $_TSPEC;
	public $fieldName = null;
	public $expr = null;
	public $values = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'fieldName'
			),
			2 => array(
			'var' => 'expr'
			),
			3 => array(
			'var' => 'values'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['fieldName'])){
				
				$this->fieldName = $vals['fieldName'];
			}
			
			
			if (isset($vals['expr'])){
				
				$this->expr = $vals['expr'];
			}
			
			
			if (isset($vals['values'])){
				
				$this->values = $vals['values'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'ConditionItem';
	}
	
	public function read($input){
		
		$input->readStructBegin();
		while(true){
			
			$schemeField = $input->readFieldBegin();
			if ($schemeField == null) break;
			$needSkip = true;
			
			
			if ("fieldName" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->fieldName);
				
			}
			
			
			
			
			if ("expr" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->expr);
				
			}
			
			
			
			
			if ("values" == $schemeField){
				
				$needSkip = false;
				
				$this->values = array();
				$_size0 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem0 = null;
						$input->readString($elem0);
						
						$this->values[$_size0++] = $elem0;
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
		
		if($this->fieldName !== null) {
			
			$xfer += $output->writeFieldBegin('fieldName');
			$xfer += $output->writeString($this->fieldName);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->expr !== null) {
			
			$xfer += $output->writeFieldBegin('expr');
			$xfer += $output->writeString($this->expr);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->values !== null) {
			
			$xfer += $output->writeFieldBegin('values');
			
			if (!is_array($this->values)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeListBegin();
			foreach ($this->values as $iter0){
				
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