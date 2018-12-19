<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\biz\request;

class PrepayOrderInfoForReq {
	
	static $_TSPEC;
	public $prepayOrderExtend = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'prepayOrderExtend'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['prepayOrderExtend'])){
				
				$this->prepayOrderExtend = $vals['prepayOrderExtend'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'PrepayOrderInfoForReq';
	}
	
	public function read($input){
		
		$input->readStructBegin();
		while(true){
			
			$schemeField = $input->readFieldBegin();
			if ($schemeField == null) break;
			$needSkip = true;
			
			
			if ("prepayOrderExtend" == $schemeField){
				
				$needSkip = false;
				
				$this->prepayOrderExtend = new \com\vip\order\common\pojo\order\vo\PrepayOrderExtendVO();
				$this->prepayOrderExtend->read($input);
				
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
		
		if($this->prepayOrderExtend !== null) {
			
			$xfer += $output->writeFieldBegin('prepayOrderExtend');
			
			if (!is_object($this->prepayOrderExtend)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->prepayOrderExtend->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}

?>