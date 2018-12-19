<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\biz\request;

class BatchGetOrderTransportListReq {
	
	static $_TSPEC;
	public $idRange = null;
	public $transportCodeRange = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'idRange'
			),
			2 => array(
			'var' => 'transportCodeRange'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['idRange'])){
				
				$this->idRange = $vals['idRange'];
			}
			
			
			if (isset($vals['transportCodeRange'])){
				
				$this->transportCodeRange = $vals['transportCodeRange'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'BatchGetOrderTransportListReq';
	}
	
	public function read($input){
		
		$input->readStructBegin();
		while(true){
			
			$schemeField = $input->readFieldBegin();
			if ($schemeField == null) break;
			$needSkip = true;
			
			
			if ("idRange" == $schemeField){
				
				$needSkip = false;
				
				$this->idRange = new \com\vip\order\common\pojo\order\request\RangeParam();
				$this->idRange->read($input);
				
			}
			
			
			
			
			if ("transportCodeRange" == $schemeField){
				
				$needSkip = false;
				
				$this->transportCodeRange = new \com\vip\order\common\pojo\order\request\RangeParam();
				$this->transportCodeRange->read($input);
				
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
		
		if($this->idRange !== null) {
			
			$xfer += $output->writeFieldBegin('idRange');
			
			if (!is_object($this->idRange)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->idRange->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->transportCodeRange !== null) {
			
			$xfer += $output->writeFieldBegin('transportCodeRange');
			
			if (!is_object($this->transportCodeRange)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->transportCodeRange->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}

?>