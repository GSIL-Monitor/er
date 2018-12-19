<?php
namespace Statistics\Model;

use Think\Model;
class StatUseModel extends Model{
	protected $tableName="stat_use";
	protected $pk="rec_id";
	
	public function statUse($module,$controller,$action,$params,$user_id){
		$is_common=false;
		switch (strtolower($controller)){
			case 'settingcommon':
				$is_common=true;
				break;
			case 'goodscommon':
				$is_common=true;
				break;
			case 'tradecommon':
				$is_common=true;
				break;
			case 'stockcommon':
				$is_common=true;
				break;
		}
		if($is_common==true){
			return;
		}else if($action=='getbrowserandresolution') {
			$sql='INSERT INTO stat_use (sid,module,controller,action,operator_id,date,num,prop1,prop2,prop3,created,modified)
				VALUES("'.$sid.'","'.$module.'","'.$controller.'","'.$action.'","'.$user_id.'","'.date('Y-m-d').'",1,"'.$params['browser'].'","'.$params['width'].'","'.$params['height'].'","'.date("Y-m-d G:i:s").'","'.date("Y-m-d G:i:s").'")
				ON DUPLICATE KEY UPDATE num=num+1, modified="'.date("Y-m-d G:i:s").'"';
		}else{
			$sid = get_sid();
			$sql_search='';
			if(!empty($params['search'])){
				foreach ($params['search'] as $k=>$v){
					if($v!=''&&$v!='all'&&$v!='-1'&&$v!='0'){
						$sql_search.="('".$sid."','".$module."','".$controller."','".$action."','".$user_id."','".$k."','".date('Y-m-d')."',1,'".date("Y-m-d G:i:s")."','".date("Y-m-d G:i:s")."'),";
					}
				}
			}
			if($sql_search!=''){
				$sql='INSERT INTO stat_use (sid,module,controller,action,operator_id,search_data,date,num,created,modified) 
							VALUES '.$sql_search;
				$sql=substr($sql, 0,strlen($sql)-1);
				$sql.=" ON DUPLICATE KEY UPDATE num=num+1, modified='".date("Y-m-d G:i:s")."'";
			}else{
				$sql='INSERT INTO stat_use (sid,module,controller,action,operator_id,date,num,created,modified) 
				VALUES("'.$sid.'","'.$module.'","'.$controller.'","'.$action.'","'.$user_id.'","'.date('Y-m-d').'",1,"'.date("Y-m-d G:i:s").'","'.date("Y-m-d G:i:s").'") 
				ON DUPLICATE KEY UPDATE num=num+1, modified="'.date("Y-m-d G:i:s").'"';
			}
		}
		try{
			$this->execute($sql);
		}catch(\PDOException $e){
			\Think\Log::write($this->name.$e->getMessage());
		}
	}
}