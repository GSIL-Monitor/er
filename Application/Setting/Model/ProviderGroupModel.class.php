<?php

namespace Setting\Model;

use Think\Model;
use Think\Exception\BusinessLogicException;

class ProviderGroupModel extends Model
{
    protected $tableName = 'purchase_provider_group';
    protected $pk = 'id';

    public function loadDataByCondition($page = 1, $rows = 20, $search = array(), $sort = 'id', $order = 'desc'){
        try {
            $where_limit=array();
            if($search['search']){
                foreach ($search['search'] as $k => $v) {
                    if ($v === '') continue;
                    switch ($k) {
                        case 'provider_group_no':
                            set_search_form_value($where_limit, $k, $v, '', 1);
                            break;
                        case 'provider_group_name':
                            set_search_form_value($where_limit, $k, $v, '', 2);
                            break;
                        default:
                            break;
                    }
                }
            }
            $page = intval($page);
            $rows = intval($rows);
            $order = $sort . ' ' . $order;//排序
            $order = addslashes($order);
            $total  = $this->where($where_limit)->count();
            $list   = $this->where($where_limit)->page($page,$rows)->order($order)->select();
            $res = array('total' => $total, 'rows' => $list);
        } catch (\PDOException $e) {
            \Think\Log::write($e->getMessage());
            SE(parent::PDO_ERROR);
        }catch (\Exception $e) {
            \Think\Log::write($e->getMessage());
            SE(parent::PDO_ERROR);
        }
        return $res;
    }

    /*
     *获取信息
     * */
    public function loadSelectedData($id){
        $res['status'] = 0;
        $res['data'] = "";
        try{
            $tmp = $this->where(array("id" => $id))->select();
            $res['data'] = $tmp[0];
        }catch (\PDOException $e){
            \Think\Log::write($this->name.'-loadSelectedData-'.$e->getMessage());
            SE(parent::PDO_ERROR);
        }catch (\Exception $e) {
            \Think\Log::write($e->getMessage());
            SE(parent::PDO_ERROR);
        }
        return $res;
    }

    /*
     * 编辑供应商分组
     * */
    public function editProviderGroup($arr){
        try{
            unset($arr['type']);
            $this->updateProviderGroup($arr, array('id'=>array('eq',$arr['id'])));
        }catch (\PDOException $e) {
            \Think\Log::write($this->name.'-editProviderGroup-'.$e->getMessage());
            SE(self::PDO_ERROR);
        }catch(BusinessLogicException $e){
            SE($e->getMessage());
        }catch (\Exception $e) {
            \Think\Log::write($e->getMessage());
            SE(parent::PDO_ERROR);
        }
    }

    /*
     * 添加供应商分组
     * */
    public function addProviderGroup($arr){
        try{
            unset($arr['type']);
            unset($arr['id']);
            $this->add($arr);
        }catch(\PDOException $e){
            \Think\Log::write($this->name.'-addProviderGroup-'.$e->getMessage());
            SE(self::PDO_ERROR);
        }catch(BusinessLogicException $e){
            SE($e->getMessage());
        }catch (\Exception $e) {
            \Think\Log::write($e->getMessage());
            SE(parent::PDO_ERROR);
        }
    }

    /*
     * 更新供应商分组信息
     * */
    public function updateProviderGroup($data,$where){
        try{
            $res = $this->where($where)->save($data);
            return $res;
        }catch (\PDOException $e){
            \Think\Log::write($this->name.'-updateProviderGroup-'.$e->getMessage());
            SE(self::PDO_ERROR);
        }catch(BusinessLogicException $e){
            SE($e->getMessage());
        }catch (\Exception $e) {
            \Think\Log::write($e->getMessage());
            SE(parent::PDO_ERROR);
        }
    }

    /*
     * 删除供应商分组
     * */
    public function delProviderGroup($id){
        try{
			$this->startTrans();
			D('Setting/PurchaseProvider')->where(array('provider_group_id'=>$id))->save(array('provider_group_id'=>1));
            $this->where(array("id" => $id))->delete();
			$this->commit();
        }catch (\PDOException $e) {
            \Think\Log::write($this->name.'-delProviderGroup-'.$e->getMessage());
			$this->rollback();
			SE(self::PDO_ERROR);
        }catch(BusinessLogicException $e){
			$this->rollback();
            SE($e->getMessage());
        }catch (\Exception $e) {
            \Think\Log::write($e->getMessage());
			$this->rollback();
            SE(parent::PDO_ERROR);
        }
    }

}