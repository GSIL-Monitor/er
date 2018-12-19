<?php
namespace Setting\Model; 
use Think\Model;
use Think\Exception\BusinessLogicException;

class CfgOperReasonModel extends Model
{
    protected $tableName = "cfg_oper_reason";
    protected $pk = "reason_id";
    public function addReason($data)
    {
        try {        
            $title_list=array();$title=array();    
            $title_list = $this->field('title')->where('class_id='.$data['class_id'])->select();
            foreach ($title_list as $k => $v) {
               $title[]=$v['title'];
            }
            if (in_array($data['title'], $title)) {
                SE('原因名称重名-'.$data['title']);
            }
            if (empty($data[0]))
            {
                $res_add = $this->add($data);
                
            }else{
                $res_add = $this->addAll($data);
                
            }
            return $res_add;
        }catch(BusinessLogicException $e){
            SE($e->getMessage());
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            \Think\Log::write($this->name.'-addreason-'.$msg);
            SE(self::PDO_ERROR);
        }
    }
    public function updateReason($data,$conditions)
    {
        try {
            $title_list=array();$title=array();  
            $title_list = $this->field('title')->where('class_id='.$data['class_id'])->select();
            foreach ($title_list as $k => $v) {
               $title[]=$v['title'];
            }
            if (in_array($data['title'], $title)) {
                SE('原因名称重名-'.$data['title']);
            }
            $res_update = $this->where($conditions)->save($data);
            return $res_update;
        }catch(BusinessLogicException $e){
            SE($e->getMessage());
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            \Think\Log::write($this->name.'-updateReason-'.$msg);
            SE(self::PDO_ERROR);
        }
    }
    /**
     * @param integer $reason_class 分类:分类:1订单驳回2订单冻结3订单取消4退款原因 5来电类别 6 保修类型 8 保修单冻结原因 9保修结束语 100出库单冻结101出库单取消原因
     * @param bool $set_reason 标识-请求的数据：true-编辑标记，flase-标记数据
     */
    public function getReasonData($reason_class, $set_reason = false, $is_disabled = 0)
    {
        $reason = array();
        $where['cor.class_id'] = array('eq', $reason_class);//标记分类
        if($is_disabled != 1){
            //是否显示停用的原因
            $where['cor.is_disabled'] = array('eq', 0);
        }
        try {
            if ($set_reason) {
                $data = $this->alias('cor')->field('cor.reason_id as id,cor.title,cor.is_disabled,cor.priority')->where($where)->order('cor.is_disabled ASC,cor.reason_id ASC')->select();
                $reason['data'] = $data;
            } else {
                $list_reason = array();
                $where['cor.is_disabled'] = array('eq', 0);//是否停用
                $where_list['cor.is_builtin'] = array('eq', 0);//是否内置原因
                 
                $res_list_reason_arr = $this->alias('cor')->field('cor.reason_id as id,cor.title as name')->where(array_merge($where, $where_list))->select();
                 
//                $default_reason[] = array('id' => 0, 'name' => '无','select'=>true);//原因的下拉列表
                if(!empty($res_list_reason_arr))
                {
                    $res_list_reason_arr[0]['selected'] = true;
                }
                $list_reason = array_merge($list_reason,$res_list_reason_arr);//= array_merge($default_reason,);

                $reason['list'] = $list_reason;
            }
            return $reason;
    
        } catch (\PDOException $e) {
            
            \Think\Log::write('UtilDB->getReasonData: ' . $e->getMessage());
            SE(self::PDO_ERROR);
        } catch(BusinessLogicException $e){
			SE($e->getMessage());
		}
         
    }
    /*获取原因*/
    public function getOperReason($fields,$conditions = array())
    {
        try {
            $res = $this->table('cfg_oper_reason')->field($fields)->where($conditions)->select();
            return $res;
        }catch (\PDOException $e)
        {
            $msg = $e->getMessage();
            \Think\Log::write($this->name.'-getOperReason-'.$msg);
            E('未知错误，请联系管理员');
        }
    }
    //校验冻结原因
    public function checkFreezeReason($value,$key='reason_id'){
    	try{
    		$map[$key]=$value;
    		$map['class_id']=2;
    		$result=$this->field('reason_id')->where($map)->find();
    		if(!empty($result)){
    			return true;
    		}
    	}catch(\PDOException $e){
    		\Think\Log::write($e->getMessage());
    	}
    	return false;
    }
}
?>