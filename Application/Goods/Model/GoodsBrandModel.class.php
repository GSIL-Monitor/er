<?php
/**
 * Created by PhpStorm.
 * User: yanfeng
 * Date: 2015/10/12
 * Time: 17:37
 */
namespace Goods\Model;

use Think\Exception\BusinessLogicException;
use Think\Model;

class GoodsBrandModel extends Model {

    protected $tableName = "goods_brand";
    protected $pk        = "brand_id";

    /**
     * @param $page
     * @param $rows
     * @param $search
     * @param $sort
     * @param $order
     * @return mixed
     * 获取品牌列表信息
     * author:luyanfeng
     */
    public function getGoodsBrandList($page, $rows, $search, $sort, $order) {
        try {
            $where = "true ";
            $page = intval($page);
            $rows = intval($rows);
            foreach ($search as $k => $v) {
                if ($v === "") continue;
                switch ($k) {
                    case "brand_name":
                        set_search_form_value($where, "brand_name", $v, "gb", 6, "AND");
                        break;
                    default:
                        continue;
                }
            }
            if($search['show_disabled']!=1){
                //是否显示停用的品牌
                $where = $where.' AND is_disabled=0';
            }
            $limit = ($page - 1) * $rows . "," . $rows;
            $sort  = $sort . " " . $order;
            $sort  = addslashes($sort);
            //先查询出需要显示的品牌的brand_id
            $sql_result = $this->alias('gb')->field('gb.brand_id')->where($where)->order($sort)->limit($limit)->fetchSql(true)->select();
            //拼接完整的SQL语句，查询出完整的数据
            $sql_rows = $this->alias('gb_1')->field('gb_1.brand_id AS id,gb_1.brand_name,gb_1.remark,gb_1.is_disabled,gb_1.modified,gb_1.created')->join("($sql_result) gb_2 on (gb_1.brand_id = gb_2.brand_id)")->select();
            $sql_count    = $this->alias('gb')->field('count(1) AS total')->where($where)->select();
            $res["total"] = $sql_count[0]["total"];
            $res["rows"]  = $sql_rows;
        } catch (\PDOException $e) {
            \Think\Log::write($e->getMessage());
            $res["total"] = 0;
            $res["rows"]  = array();
            SE(parent::PDO_ERROR);
        }catch (\Exception $e) {
            \Think\Log::write($e->getMessage());
            $res["total"] = 0;
            $res["rows"]  = array();
        }
        return $res;
    }
    /**
     * @param $data
     * @return bool
     * 添加或者更新品牌信息
     * author:luyanfeng
     */
    public function updateGoodsBrand($data) {
        try {
            $result = $this->getGoodsBrand($data["brand_name"], "brand_name");
            foreach ($result as $v) {
                if ((isset($data["brand_id"]) && $v["brand_id"] != $data["brand_id"]) || (!isset($data["brand_id"]) && !empty($v))) {
                    SE("该品牌名称已存在");
                }
            }
            if ($data["is_disabled"] == 1) {
                $result = D("GoodsSuite")->checkSuite($data["brand_id"], "brand_id");
                if ($result) {
                    SE("该品牌不能停用：该品牌下包含组合装");
                }
                $result = D("GoodsGoods")->checkGoods($data["brand_id"], "brand_id");
                if ($result) {
                    SE("该品牌不能停用：该品牌下包含货品档案");
                }
            }
            $this->startTrans();
            if (!isset($data["brand_id"])) {
                $brand_id      = $this->data($data)->add();
                $arr_goods_log = array(
                    "goods_type"   => "3",
                    "goods_id"     => $brand_id,
                    "operate_type" => "54",
                    "operator_id"  => get_operator_id(),
                    "message"      => "新建货品品牌--" . $data["brand_name"],
                    "careted"      => date("Y-m-d G:i:s")
                );
            } else {
                $this->data($data)->save();
                $arr_goods_log = array(
                    "goods_type"   => "3",
                    "goods_id"     => $data["brand_id"],
                    "operate_type" => "54",
                    "operator_id"  => get_operator_id(),
                    "message"      => "更新货品品牌--" . $data["brand_name"],
                    "careted"      => date("Y-m-d G:i:s")
                );
            }
            $goods_log_db = M("goods_log");
            $goods_log_db->data($arr_goods_log)->add();
            $this->commit();
        } catch (\PDOException $e) {
            $this->rollback();
            \Think\Log::write($e->getMessage());
            SE(parent::PDO_ERROR);
        } catch(BusinessLogicException $e){
            SE($e->getMessage());
        }catch (\Exception $e) {
            $this->rollback();
            \Think\Log::write($e->getMessage());
            SE(parent::PDO_ERROR);
        }
    }

    /**
     * @param        $id
     * @param string $name
     * @return array
     * 根据不同的参数获取货品品牌数据
     * author:luyaneng
     */
    public function getGoodsBrand($id, $name = "brand_id") {
        try {
            $map[ $name ] = $id;
            $result       = $this->where($map)->select();
            return $result;
        } catch (\PDOException $e) {
            \Think\Log::write($e->getMessage());
            SE(parent::PDO_ERROR);
        }catch (\Exception $e) {
            \Think\Log::write($e->getMessage());
            return array();
        }
    }

    /**
     * @param $id
     * @return bool
     * 根据brand_id移除货品品牌
     * author:luyanfeng
     */
    public function disableGoodsBrandById($id) {
        try {
            $result = D("GoodsSuite")->checkSuite($id, "brand_id");
            if ($result) {
                SE('不能停用，该品牌下包含组合装');
            }
            $result = D("GoodsGoods")->checkGoods($id, "brand_id");
            if ($result) {
                SE('不能停用：该品牌下包含货品档案');
            }
            $data             = array("brand_id" => $id, "is_disabled" => 1);
            $goods_brand_name = $this->alias('gb')->field('gb.brand_name')->where(array('gb.brand_id'=>array('eq',$id)))->select();
            //$this->where("brand_id=$id")->delete();
            $this->startTrans();
            $this->data($data)->save();
            $goods_log_db  = M("goods_log");
            $arr_goods_log = array(
                "goods_type"   => "3",
                "goods_id"     => $id,
                "operate_type" => "54",
                "operator_id"  => get_operator_id(),
                "message"      => "停用货品品牌--" . $goods_brand_name[0]["brand_name"],
                "careted"      => date("Y-m-d G:i:s")
            );
            $goods_log_db->data($arr_goods_log)->add();
            $this->commit();
        } catch (\PDOException $e) {
            $this->rollback();
            \Think\Log::write($e->getMessage());
            SE(parent::PDO_ERROR);
        } catch(BusinessLogicException $e){
            SE($e->getMessage());
        } catch (\Exception $e) {
            \Think\Log::write($e->getMessage());
            SE(parent::PDO_ERROR);
        }
    }

    /**
     * 检查货品品牌是否存在
     * author:luyanfeng
     * @param        $value
     * @param string $name
     * @return bool
     */
    public function checkBrand($value, $name = "brand_id") {
        $map[ $name ] = $value;
        try {
            $result = $this->field('brand_id')->where($map)->find();
            if (!empty($result)) return true;
        } catch(\PDOException $e){
            \Think\Log::write($e->getMessage());
            SE(parent::PDO_ERROR);
        } catch (\Exception $e) {
            \Think\Log::write($e->getMessage());
        }
        return false;
    }
}