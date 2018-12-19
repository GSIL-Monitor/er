<?php

namespace Setting\Model;

use Think\Model;
use Think\Exception\BusinessLogicException;

class PurchaseProviderModel extends Model
{
    protected $tableName = 'purchase_provider';
    protected $pk = 'id';
    protected  $_validate = array(
        array('provider_name','','供应商名重复，请重新填写!！',0,'unique',3) // 在新增的时候验证name字段是否唯一
    );

    public function loadDataByCondition($page = 1, $rows = 20, $search = array(), $sort = 'id', $order = 'desc'){
        try {
            $where_limit=array();
            D('Setting/EmployeeRights')->setSearchRights($search['search'],'provider_ids',4);
            if($search['search']){
                foreach ($search['search'] as $k => $v) {
                    if ($v === '') continue;
                    switch ($k) {
                        case 'provider_name':
                            set_search_form_value($where_limit, $k, $v, 'pp', 1);
                            break;
                        case 'provider_ids':
                            set_search_form_value($where_limit,'id',$v,'pp',2);
                            break;
                        case 'mobile':
                            set_search_form_value($where_limit, $k, $v, 'pp', 2);
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
            $total  = $this->alias('pp')->where($where_limit)->count();
            $list   = $this->alias('pp')->field(array('pp.*','ppg.provider_group_name'))->join('left join purchase_provider_group ppg on ppg.id = pp.provider_group_id')->where($where_limit)->page($page,$rows)->order($order)->select();
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
            $tmp = $this->where(array("id" => $id))->field($this->searchArray)->select();
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
     * 编辑供应商
     * */
    public function editPurchaseProvider($arr){
        try{
            unset($arr['type']);
            if(!$this->create($arr,2)){
                SE($this->getError());
            }
            $this->updatePurchaseProvider($arr, array('id'=>array('eq',$arr['id'])));
        }catch (\PDOException $e) {
            \Think\Log::write($this->name.'-editPurchaseProvider-'.$e->getMessage());
            SE(self::PDO_ERROR);
        }catch(BusinessLogicException $e){
            SE($e->getMessage());
        }catch (\Exception $e) {
            \Think\Log::write($e->getMessage());
            SE(parent::PDO_ERROR);
        }
    }

    /*
     * 添加供应商
     * */
    public function addPurchaseProvider($arr){
        try{
            unset($arr['type']);
            unset($arr['id']);
            if(!$this->create($arr,1)){
                SE($this->getError());
            }
            $this->add($arr);
        }catch(\PDOException $e){
            \Think\Log::write($this->name.'-addPurchaseProvider-'.$e->getMessage());
            SE(self::PDO_ERROR);
        }catch(BusinessLogicException $e){
            SE($e->getMessage());
        }catch (\Exception $e) {
            \Think\Log::write($e->getMessage());
            SE(parent::PDO_ERROR);
        }
    }

    /*
     * 更新供应商信息
     * */
    public function updatePurchaseProvider($data,$where){
        try{
            $res = $this->where($where)->save($data);
            return $res;
        }catch (\PDOException $e){
            \Think\Log::write($this->name.'-updatePurchaseProvider-'.$e->getMessage());
            SE(self::PDO_ERROR);
        }catch(BusinessLogicException $e){
            SE($e->getMessage());
        }catch (\Exception $e) {
            \Think\Log::write($e->getMessage());
            SE(parent::PDO_ERROR);
        }
    }

    /*
     * 删除供应商
     * */
    public function delPurchaseProvider($id){
        try{
			$this->startTrans();
			D('Purchase/SupplierGoods')->where(array('provider_id'=>$id))->delete();
            $this->where(array("id" => $id))->delete();
			$this->commit();
        }catch (\PDOException $e) {
            \Think\Log::write($this->name.'-delPurchaseProvider-'.$e->getMessage());
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

    /*
     * 获取到供应商的信息
     * $where array 筛选条件
     * */
    public function getALlPurchaseProvider($where){
        $res['status'] = 0;
        $res['data'] = "";
        try{
            $tmp = $this->where($where)->field('id,provider_name as name')->select();
            $res['data'] = $tmp;
        }catch (\PDOException $e){
            \Think\Log::write($this->name.'-getALlPurchaseProvider-'.$e->getMessage());
            SE(parent::PDO_ERROR);
        }catch (\Exception $e) {
            \Think\Log::write($e->getMessage());
            SE(parent::PDO_ERROR);
        }
        return $res;
    }

}