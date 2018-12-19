<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\biz\request;

class GroupByOrderAuditReq {
	
	static $_TSPEC;
	public $groupByOrderAuditParamSet = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'groupByOrderAuditParamSet'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['groupByOrderAuditParamSet'])){
				
				$this->groupByOrderAuditParamSet = $vals['groupByOrderAuditParamSet'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'GroupByOrderAuditReq';
	}
	
	public function read($input){
		
		$input->readStructBegin();
		while(true){
			
			$schemeField = $input->readFieldBegin();
			if ($schemeField == null) break;
			$needSkip = true;
			
			
			if ("groupByOrderAuditParamSet" == $schemeField){
				
				$needSkip = false;
				
				$this->groupByOrderAuditParamSet = array();
				$_size0 = 0;
				$input->readSetBegin();
				while(true){
					
					try{
						
						$elem0 = null;
						
						$elem0 = new \com\vip\order\biz\request\GroupByOrderAuditParam();
						$elem0->read($input);
						
						$this->groupByOrderAuditParamSet[$_size0++] = $elem0;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readSetEnd();
				
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
		
		if($this->groupByOrderAuditParamSet !== null) {
			
			$xfer += $output->writeFieldBegin('groupByOrderAuditParamSet');
			
			if (!is_array($this->groupByOrderAuditParamSet)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeSetBegin();
			foreach ($this->groupByOrderAuditParamSet as $iter0){
				
				
				if (!is_object($iter0)) {
					
					throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
				}
				
				$xfer += $iter0->write($output);
				
			}
			
			$output->writeSetEnd();
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}

?>