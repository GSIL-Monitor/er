<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\biz\response;

class GetUnpayOrderResp {
	
	static $_TSPEC;
	public $result = null;
	public $unpayOrderSnMap = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'result'
			),
			2 => array(
			'var' => 'unpayOrderSnMap'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['result'])){
				
				$this->result = $vals['result'];
			}
			
			
			if (isset($vals['unpayOrderSnMap'])){
				
				$this->unpayOrderSnMap = $vals['unpayOrderSnMap'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'GetUnpayOrderResp';
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
			
			
			
			
			if ("unpayOrderSnMap" == $schemeField){
				
				$needSkip = false;
				
				$this->unpayOrderSnMap = array();
				$input->readMapBegin();
				while(true){
					
					try{
						
						$key0 = 0;
						$input->readI64($key0); 
						
						$val0 = null;
						
						$val0 = array();
						$_size1 = 0;
						$input->readListBegin();
						while(true){
							
							try{
								
								$elem1 = null;
								$input->readString($elem1);
								
								$val0[$_size1++] = $elem1;
							}
							catch(\Exception $e){
								
								break;
							}
						}
						
						$input->readListEnd();
						
						$this->unpayOrderSnMap[$key0] = $val0;
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
		
		if($this->result !== null) {
			
			$xfer += $output->writeFieldBegin('result');
			
			if (!is_object($this->result)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->result->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->unpayOrderSnMap !== null) {
			
			$xfer += $output->writeFieldBegin('unpayOrderSnMap');
			
			if (!is_array($this->unpayOrderSnMap)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeMapBegin();
			foreach ($this->unpayOrderSnMap as $kiter0 => $viter0){
				
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