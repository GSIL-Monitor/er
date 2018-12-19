<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\biz\request;

class PmsInfo {
	
	static $_TSPEC;
	public $pmsTicketId = null;
	public $isFreeCarriage = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'pmsTicketId'
			),
			2 => array(
			'var' => 'isFreeCarriage'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['pmsTicketId'])){
				
				$this->pmsTicketId = $vals['pmsTicketId'];
			}
			
			
			if (isset($vals['isFreeCarriage'])){
				
				$this->isFreeCarriage = $vals['isFreeCarriage'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'PmsInfo';
	}
	
	public function read($input){
		
		$input->readStructBegin();
		while(true){
			
			$schemeField = $input->readFieldBegin();
			if ($schemeField == null) break;
			$needSkip = true;
			
			
			if ("pmsTicketId" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->pmsTicketId);
				
			}
			
			
			
			
			if ("isFreeCarriage" == $schemeField){
				
				$needSkip = false;
				$input->readBool($this->isFreeCarriage);
				
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
		
		if($this->pmsTicketId !== null) {
			
			$xfer += $output->writeFieldBegin('pmsTicketId');
			$xfer += $output->writeString($this->pmsTicketId);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->isFreeCarriage !== null) {
			
			$xfer += $output->writeFieldBegin('isFreeCarriage');
			$xfer += $output->writeBool($this->isFreeCarriage);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}

?>