<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\common\pojo\order\request;

class RangeParam {
	
	static $_TSPEC;
	public $inclusion = null;
	public $exclusion = null;
	public $begin = null;
	public $end = null;
	public $beginInclusive = null;
	public $endInclusive = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'inclusion'
			),
			2 => array(
			'var' => 'exclusion'
			),
			3 => array(
			'var' => 'begin'
			),
			4 => array(
			'var' => 'end'
			),
			5 => array(
			'var' => 'beginInclusive'
			),
			6 => array(
			'var' => 'endInclusive'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['inclusion'])){
				
				$this->inclusion = $vals['inclusion'];
			}
			
			
			if (isset($vals['exclusion'])){
				
				$this->exclusion = $vals['exclusion'];
			}
			
			
			if (isset($vals['begin'])){
				
				$this->begin = $vals['begin'];
			}
			
			
			if (isset($vals['end'])){
				
				$this->end = $vals['end'];
			}
			
			
			if (isset($vals['beginInclusive'])){
				
				$this->beginInclusive = $vals['beginInclusive'];
			}
			
			
			if (isset($vals['endInclusive'])){
				
				$this->endInclusive = $vals['endInclusive'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'RangeParam';
	}
	
	public function read($input){
		
		$input->readStructBegin();
		while(true){
			
			$schemeField = $input->readFieldBegin();
			if ($schemeField == null) break;
			$needSkip = true;
			
			
			if ("inclusion" == $schemeField){
				
				$needSkip = false;
				
				$this->inclusion = array();
				$_size0 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem0 = null;
						$input->readString($elem0);
						
						$this->inclusion[$_size0++] = $elem0;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readListEnd();
				
			}
			
			
			
			
			if ("exclusion" == $schemeField){
				
				$needSkip = false;
				
				$this->exclusion = array();
				$_size1 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem1 = null;
						$input->readString($elem1);
						
						$this->exclusion[$_size1++] = $elem1;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readListEnd();
				
			}
			
			
			
			
			if ("begin" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->begin);
				
			}
			
			
			
			
			if ("end" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->end);
				
			}
			
			
			
			
			if ("beginInclusive" == $schemeField){
				
				$needSkip = false;
				$input->readBool($this->beginInclusive);
				
			}
			
			
			
			
			if ("endInclusive" == $schemeField){
				
				$needSkip = false;
				$input->readBool($this->endInclusive);
				
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
		
		if($this->inclusion !== null) {
			
			$xfer += $output->writeFieldBegin('inclusion');
			
			if (!is_array($this->inclusion)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeListBegin();
			foreach ($this->inclusion as $iter0){
				
				$xfer += $output->writeString($iter0);
				
			}
			
			$output->writeListEnd();
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->exclusion !== null) {
			
			$xfer += $output->writeFieldBegin('exclusion');
			
			if (!is_array($this->exclusion)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeListBegin();
			foreach ($this->exclusion as $iter0){
				
				$xfer += $output->writeString($iter0);
				
			}
			
			$output->writeListEnd();
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->begin !== null) {
			
			$xfer += $output->writeFieldBegin('begin');
			$xfer += $output->writeString($this->begin);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->end !== null) {
			
			$xfer += $output->writeFieldBegin('end');
			$xfer += $output->writeString($this->end);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->beginInclusive !== null) {
			
			$xfer += $output->writeFieldBegin('beginInclusive');
			$xfer += $output->writeBool($this->beginInclusive);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->endInclusive !== null) {
			
			$xfer += $output->writeFieldBegin('endInclusive');
			$xfer += $output->writeBool($this->endInclusive);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}

?>