<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\biz\request;

class ModifyOrderPayTypeRsp {
	
	static $_TSPEC;
	public $result = null;
	public $isSuccess = null;
	public $ModifyPayTypeRsp = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'result'
			),
			2 => array(
			'var' => 'isSuccess'
			),
			3 => array(
			'var' => 'ModifyPayTypeRsp'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['result'])){
				
				$this->result = $vals['result'];
			}
			
			
			if (isset($vals['isSuccess'])){
				
				$this->isSuccess = $vals['isSuccess'];
			}
			
			
			if (isset($vals['ModifyPayTypeRsp'])){
				
				$this->ModifyPayTypeRsp = $vals['ModifyPayTypeRsp'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'ModifyOrderPayTypeRsp';
	}
	
	public function read($input){
		
		$input->readStructBegin();
		while(true){
			
			$schemeField = $input->readFieldBegin();
			if ($schemeField == null) break;
			$needSkip = true;
			
			
			if ("result" == $schemeField){
				
				$needSkip = false;
				
				$this->result = new \com\vip\order\common\pojo\order\result\Result();
				$this->result->read($input);
				
			}
			
			
			
			
			if ("isSuccess" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->isSuccess); 
				
			}
			
			
			
			
			if ("ModifyPayTypeRsp" == $schemeField){
				
				$needSkip = false;
				
				$this->ModifyPayTypeRsp = array();
				$_size0 = 0;
				$input->readListBegin();
				while(true){
					
					try{
						
						$elem0 = null;
						
						$elem0 = new \com\vip\order\biz\request\OrderInfo();
						$elem0->read($input);
						
						$this->ModifyPayTypeRsp[$_size0++] = $elem0;
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
		
		if($this->result !== null) {
			
			$xfer += $output->writeFieldBegin('result');
			
			if (!is_object($this->result)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->result->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->isSuccess !== null) {
			
			$xfer += $output->writeFieldBegin('isSuccess');
			$xfer += $output->writeI32($this->isSuccess);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->ModifyPayTypeRsp !== null) {
			
			$xfer += $output->writeFieldBegin('ModifyPayTypeRsp');
			
			if (!is_array($this->ModifyPayTypeRsp)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeListBegin();
			foreach ($this->ModifyPayTypeRsp as $iter0){
				
				
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