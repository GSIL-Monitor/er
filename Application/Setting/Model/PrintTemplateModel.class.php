<?php
namespace Setting\Model;
use Think\Model;
use Think\Exception\BusinessLogicException;
class PrintTemplateModel extends Model{
	protected $tableName = 'cfg_print_template';
	protected $pk        = 'rec_id';
	
	public function save($data,$update_field) {
		try {
			$data['content'] = htmlspecialchars_decode($data['content']);
			$data['created'] = Date('Y-m-d H:i:s');
			$ret['id'] = $this->add($data, '',$update_field);
			$ret['status'] = 0;
		} catch (\Exception $e) {
			$msg = $e->getMessage();			
			\Think\Log::write($msg);
			$ret['status'] = 1;
			$ret['msg'] = '保存数据失败';
		}
		
		return $ret;
	}
	
	public function get($field, $type = '', $id = '',$templateType='') {
            // \Think\Log::write($type);
			if (!empty($type)){
				$condition['type'] = array('in', $type);
				
			}

			if (!empty($id)){
				$condition['rec_id'] = intval($id);
			}if(!empty($templateType)){
				$condition['logistics_list'] = array('in',array($templateType,'')); 
			}
			try {
                $result = $this->field($field)->where($condition)->order('is_default desc')->select();
            } catch (\Exception $e) {
                \Think\Log::write($e->getMessage());
                $result = array();
            }

            return $result;
        }
    public function getTemplateByLogistics($field, $type = '',$logistics_id='',$sysTemp = true) {
            $condition = array();
            $condition['is_disabled'] = 0;
            if (!empty($type)){
                $condition['type'] = array('in', $type);
            }
            if($sysTemp){
                $condition['_string'] = 'logistics_list="" or '.$logistics_id.'=0 or FIND_IN_SET('.$logistics_id.',logistics_list)';
            }else{
                $condition['_string'] = $logistics_id.'=0 or FIND_IN_SET('.$logistics_id.',logistics_list)';
            }
            try {
                $result = $this->field($field)->where($condition)->select();
            } catch (\Exception $e) {
                \Think\Log::write($e->getMessage());
                $result = array();
            }
        
            return $result;
        }
	public function addTemplates($data,$template_ids){
		try{
			$ret['ids'] = $this->addAll($data,array(),'template_id,content,title,type,logistics_list,shop_ids');
			$ret['status'] = 0;
		}catch(\PDOException $e){
			\Think\Log::write($e->getMessage());
			$ret['status'] = 1;
			$ret['msg'] = "下载数据失败";
		}	
		return $ret;
	}
	public function deleteTemplate($rec_id){
		try{
			$this->startTrans();
			$rec_id = $this->delete($rec_id);
			D('Setting/UserData')->where(array('data'=>$rec_id))->delete();
			$this->commit();
			return $rec_id;
		}catch(\PDOException $e){
			\Think\Log::write($e->getMessage());
			$this->rollback();
			return false;
		}
	}
	public function getTemplateType($template_id){
		try{
			$result = $this->field('type')->where(array('rec_id'=>$template_id))->select();
		}catch(\PDOException $e){
			\Think\Log::write($e->getMessage());
			SE('模板不存在！');
		}
		return $result;
	}
}