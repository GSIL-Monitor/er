<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\common\pojo\order\request;

class OrderBy {
	
	static $_TSPEC;
	public $field = null;
	public $direction = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'field'
			),
			2 => array(
			'var' => 'direction'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['field'])){
				
				$this->field = $vals['field'];
			}
			
			
			if (isset($vals['direction'])){
				
				$this->direction = $vals['direction'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'OrderBy';
	}
	
	public function read($input){
		
		$input->readStructBegin();
		while(true){
			
			$schemeField = $input->readFieldBegin();
			if ($schemeField == null) break;
			$needSkip = true;
			
			
			if ("field" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->field);
				
			}
			
			
			
			
			if ("direction" == $schemeField){
				
				$needSkip = false;
				
				$names = \com\vip\order\common\pojo\order\request\OrderByDirection::$__names;
				$name = null;
				$input->readString($name);
				foreach ($names as $k => $v){
					
					if($name == $v){
						
						$this->direction = $k;
						break;
					}
					
				}
				
				
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
		
		if($this->field !== null) {
			
			$xfer += $output->writeFieldBegin('field');
			$xfer += $output->writeString($this->field);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->direction !== null) {
			
			$xfer += $output->writeFieldBegin('direction');
			
			$em = new \com\vip\order\common\pojo\order\request\OrderByDirection; 
			$output->writeString($em::$__names[$this->direction]);  
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}

?>