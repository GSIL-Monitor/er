<?php
/**
 * Created by PhpStorm.
 * User: gaosong
 * Date: 11/26/15
 * Time: 09:55
 */
namespace Setting\Model;

use Think\Model;

class UnitModel extends Model {
    protected $tableName   = 'cfg_goods_unit';
    protected $pk          = 'rec_id';
    protected $_validate   = array(
        //array(验证字段1,验证规则,错误提示,[验证条件,附加规则,验证时间]),
        array('is_disabled', array(1, 0), '停用类型不正确!', 1, in, 3),
        array('name', '', '单位名称重复，请重新填写!', 0, 'unique', 3),
    );
    protected $searchArray = array("rec_id" => "id", "name", "is_disabled", "remark");

    public function searchUnit($page, $rows, $search = array(), $sort, $order) {
        $order = $sort . ' ' . $order;//排序
        try {
            $list  = $this->fetchSql(false)->field($this->searchArray)->page($page, $rows)->order($order)->select();
            $total = count($list);
            $data  = array('total' => $total, 'rows' => $list);
        } catch (\PDOException $e) {
            \Think\Log::write($e->getMessage());
            $data = array('total' => 0, 'rows' => array());
        }
        return $data;
    }

    public function loadSelectedData($id) {
        $re['status'] = 0;
        $re['data']   = "";
        try {
            $tmp        = $this->where(array("rec_id" => $id))->field($this->searchArray)->select();
            $re['data'] = $tmp[0];
        } catch (\PDOException $e) {
            \Think\Log::write($e->getMessage());
            $re['status'] = 1;
            $re['data']   = self::PDO_ERROR;
        }
        return $re;
    }

    public function saveUnit($arr) {
    	try {
    		if('add'==$arr['type'])
    		{
    			unset($arr['type']);
    			unset($arr['rec_id']);
    			if(!$this->create($arr,1))
    			{
    				E($this->getError());
    			}
    			$this->add($arr);
    		}else
    		{
    			unset($arr['type']);
    			if(!$this->create($arr,2))
    			{
    				E($this->getError());
    			}
    			$this->save($arr);
    		}
    	}catch (\PDOException $e) {
            \Think\Log::write($e->getMessage());
            E(self::PDO_ERROR);
        } 
    }
}

