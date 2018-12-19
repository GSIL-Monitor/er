<?php
namespace Stock\Model;
use Think\Model;
 
 class CfgEscaleModel extends Model{
 	protected $tableName = 'cfg_escale';
 	protected $pk = 'type';
 	
 	
 	public function getEscaleInfo($fields,$where){
 		//$fields = array('type' ,'name' ,'bandrate' ,'pattern' ,'default_port'  );
 		$where = " name LIKE '湘平%' OR name LIKE '耀华%' OR name LIKE '坤宏%'";
 		try{
 			$data = $this->field($fields)->where($where)->select();
 		}catch(\PDOException $e){
 			$message = $e->getMessage() ;
 			\Think\Log::write($message);
 			return false;
 		}
 		return $data;
 	}
 	public function setEscale($type,$bandrate,$defaultType,$defaultBandrate){
 		$ret['status'] = 0;
 		try{
 			$data = array(array('name'=>'111','type'=>$type,'bandrate'=>$bandrate,'reversed'=>1),array('name'=>'123','type'=>$defaultType,'bandrate'=>$defaultBandrate,'reversed'=>0));
 			$type = $this->addAll($data,array(),'type,bandrate,reversed');
 			$ret['success']['type'] = $type;
 		}catch(\PDOException $e){
 			\Think\Log::write($e->getMessage());
 			$type = $e->getMessage();
 			$ret['status'] = 1;
 			$ret['fail']['msg'] = $type;
 		}
 		return json_encode($ret);
 	}
 }