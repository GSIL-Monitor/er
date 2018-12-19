<?php
/**
 * Created by PhpStorm.
 * User: yanfeng
 * Date: 2015/8/20
 * Time: 16:00
 */
namespace Goods\Model;

use Think\Exception\BusinessLogicException;
use Think\Log;
use Think\Model;
use Common\Common\ExcelTool;

class GoodsSuiteModel extends Model {
    protected $tableName = "goods_suite";
    protected $pk        = "suite_id";

    /**
     * @param        $value
     * @param string $name
     * @return bool
     */
    public function checkSuite($value, $name = "suite_id") {
        $map[$name] = $value;
        try {
            $result = $this->field('suite_id')->where($map)->find();
            if (!empty($result)) return true;
        } catch (\Exception $e) {
            Log::write($e->getMessage());
            //SE(self::PDO_ERROR);
        }
        return false;
    }

    /**
     * @param int    $page
     * @param int    $rows
     * @param array  $search
     * @param string $sort
     * @param string $order
     * @return mixed
     */
    public function getGoodsSuiteList($page = 1, $rows = 10, $search = array(), $sort = 'suite_id', $order = 'desc',$model ='') {
        try {
            $where = " WHERE gs.deleted=0 ";
            $page = intval($page);
            $rows = intval($rows);
            $this->searchFormDeal($where,$search,$model,$left_join_goods_class_str);
            $limit = ($page - 1) * $rows . "," . $rows;
            $sort  = $sort . " " . $order;
            $sort  = addslashes($sort);
            if($model=='goodssuitebarcode'){
                $sql = "SELECT gs.suite_id AS id,gs.suite_id,gbc.rec_id as barcode_id, gbc.barcode, gs.suite_no, gs.suite_name, IF(gbc.is_master,'是','否') is_master, gb.brand_name 
                               FROM goods_barcode gbc
                               LEFT JOIN goods_suite gs ON gbc.target_id=gs.suite_id
                               LEFT JOIN goods_brand gb ON gb.brand_id=gs.brand_id"
                               .$where." AND gbc.type=2   ORDER BY $sort LIMIT $limit";
            $sql_count    =  "SELECT count(1) as total
                               FROM goods_barcode gbc
                               LEFT JOIN goods_suite gs ON gbc.target_id=gs.suite_id"
                               .$where." AND gbc.type=2  ";

            }else{
                //先查询出需要显示的组合装的suite_id
                $sql_result = "SELECT DISTINCT gs.suite_id FROM goods_suite gs
                        LEFT JOIN goods_suite_detail gsd ON(gs.suite_id=gsd.suite_id)
                        LEFT JOIN goods_spec gsp ON(gsd.spec_id=gsp.spec_id)"
                    . $left_join_goods_class_str .
                    "LEFT JOIN goods_brand gb ON(gb.brand_id=gs.brand_id)
                        LEFT JOIN goods_goods gg ON(gsp.goods_id=gg.goods_id)
                        $where
                        ORDER BY $sort LIMIT $limit";
                //再构造SQL查询完整的信息
                $sql           = "SELECT gs_1.suite_id AS id,gs_1.suite_name,gs_1.suite_no,gs_1.barcode,gs_1.retail_price,
                        gs_1.market_price,gb.brand_name,gc.class_name,gs_1.weight,gs_1.remark
                        FROM goods_suite gs_1
                        INNER JOIN( " . $sql_result . " )gs_2 ON(gs_1.suite_id=gs_2.suite_id)
                        LEFT JOIN goods_brand gb ON(gs_1.brand_id=gb.brand_id)
                        LEFT JOIN goods_class gc ON(gs_1.class_id=gc.class_id)";
                $sql_count     = "SELECT COUNT(DISTINCT gs.suite_id) AS total FROM goods_suite gs
                        LEFT JOIN goods_suite_detail gsd ON(gs.suite_id=gsd.suite_id)
                        LEFT JOIN goods_spec gsp ON(gsd.spec_id=gsp.spec_id)"
                    . $left_join_goods_class_str .
                    "LEFT JOIN goods_brand gb ON(gb.brand_id=gs.brand_id)
                        LEFT JOIN goods_goods gg ON(gg.goods_id=gsp.goods_id)
                        $where";
            }

            $result        = $this->query($sql);
            $data['rows']  = $result;
            $result        = $this->query($sql_count);
            $data["total"] = $result["0"]["total"];
        } catch (\Exception $e) {
            Log::write($e->getMessage());
            $data['total'] = 0;
            $data['rows']  = '';
        }
        return $data;
    }

    public function searchFormDeal(&$where, $search,$model ='',&$left_join_goods_class_str)
    {
        foreach ($search as $k => $v) {
            if ($v === "") continue;
            switch ($k) {
                case "suite_no":
                    set_search_form_value($where, 'suite_no', $v, 'gs', 10, 'AND');
                    break;
                case "suite_name":
                    set_search_form_value($where, 'suite_name', $v, 'gs', 6, 'AND');
                    break;
                case "barcode":
                    if($model == 'goodssuitebarcode'){
                        set_search_form_value($where, 'barcode', $v, 'gbc', 1, 'AND');
                    }else{
                        set_search_form_value($where, 'barcode', $v, 'gs', 1, 'AND');
                    }
                    break;
                case "brand_id":
                    set_search_form_value($where, 'brand_id', $v, 'gb', 2, 'AND');
                    break;
                case "class_id":
                    $left_join_goods_class_str = set_search_form_value($where, 'class_id', $v, 'gs', 7, 'AND');
                    break;
                case "spec_no":
                    set_search_form_value($where, 'spec_no', $v, 'gsp', 10, 'AND');
                    break;
                case "spec_name":
                    set_search_form_value($where, 'spec_name', $v, 'gsp', 6, 'AND');
                    break;
                case "goods_name":
                    set_search_form_value($where, 'goods_name', $v, 'gg', 10, 'AND');
                    break;
                default:
                    continue;
            }
        }
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getGoodsSuiteLog($id) {
        try {
            $sql           = "SELECT gl.rec_id as id,gl.message,gl.created,he.fullname
                    FROM goods_log gl
                    LEFT JOIN hr_employee he ON(he.employee_id = gl.operator_id)
                    WHERE gl.goods_id = %d AND gl.goods_type = 2 ORDER BY gl.rec_id DESC";
            $sql_count     = "SELECT COUNT(1) AS total FROM goods_log gl
                    LEFT JOIN hr_employee he ON(he.employee_id = gl.operator_id)
                    WHERE gl.goods_id = %d AND gl.goods_type = 2";
            $goods_log_db  = M("goods_log");
            $result        = $goods_log_db->query($sql_count, $id);
            $data["total"] = $result[0]["total"];
            $data["rows"]  = $goods_log_db->query($sql, $id);
        } catch (\Exception $e) {
            Log::write($e->getMessage());
            $data["total"] = 0;
            $data["rows"]  = array();
        }
        return $data;
    }

    /**
     * @param $id
     * @param $type
     * @return mixed
     */
    public function getTabsPlatformGoods($id, $type = "goods_suite") {
        try {
            switch ($type) {
                case 'goods_suite':
                    $sql           = "SELECT DISTINCT ag.rec_id as id,ag.spec_outer_id,s.shop_name,ag.goods_name,ag.spec_name,ag.goods_id,ag.spec_name,ag.goods_id,ag.spec_id,ag.hold_stock_type,ag.hold_stock,ag.spec_sku_properties,
                        ag.outer_id,ag.price,gx.suite_no,gx.suite_name,ag.match_target_type,gx.retail_price,ag.status,ag.stock_num,ag.last_syn_num,ag.last_syn_time
                        FROM api_goods_spec ag
                        LEFT JOIN goods_suite gx ON gx.suite_id = ag.match_target_id
                        LEFT JOIN cfg_shop s ON s.shop_id=ag.shop_id
                        WHERE ag.match_target_type=2 AND ag.match_target_id=%d";
                    $sql_count     = "SELECT DISTINCT COUNT(1) AS total FROM api_goods_spec ag WHERE ag.match_target_type=2 AND ag.match_target_id=%d";
                    $result        = $this->query($sql_count, $id);
                    $data["total"] = $result["0"]["total"];

                    break;
                case 'goods_spec':
                    $sql           = "SELECT ag.rec_id id,ss.shop_name shop_id,ag.goods_name,ag.spec_name,ag.outer_id,ag.spec_outer_id,ag.goods_id,ag.spec_id,ag.price,ag.stock_num,ag.is_auto_listing,ag.is_auto_delisting,ag.status,IF(ag.is_manual_match,0,1) AS is_auto_match ,ag.match_target_type,ag.last_syn_num,ag.last_syn_time,ag.modified"
                                     . " FROM api_goods_spec ag"
                                     . " LEFT JOIN cfg_shop ss ON (ag.shop_id=ss.shop_id)"
                                     . " WHERE ag.match_target_type=1 AND ag.match_target_id = %d";
                    $data["total"] = 0;//需要修改changtao指派luyanfeng
                    break;
                default:
                    Log::write($this->name . '-getGoodsLog-' . '不存在该平台类型：' . $type);
                    $sql = '';
            }
            $data["rows"] = $this->query($sql, $id);
        } catch (\Exception $e) {
            Log::write($e->getMessage());
            $data["total"] = 0;
            $data["rows"]  = array();
        }
        return $data;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getGoodsSuiteById($id) {
        try {
            $sql    = "SELECT gs.suite_id AS id,gs.suite_name,gs.suite_no,gs.barcode,gs.retail_price,gs.wholesale_price,
                        gs.member_price,gs.market_price,gs.brand_id,gs.class_id,gs.weight,gs.prop1,gs.prop2,gs.prop3,gs.prop4,gs.remark,gs.is_print_suite
                        FROM goods_suite gs WHERE gs.suite_id = %d";
            $result = $this->query($sql, $id);
        } catch (\Exception $e) {
            Log::write($e->getMessage());
            $result = [[]];
        }
        return $result["0"];
    }

    /**
     * @param        $value
     * @param string $name
     * @param string $fields
     * @return array|mixed
     */
    public function getGoodsSuite($value, $name = "suite_id", $fields = "*") {
        try {
            $map["deleted"] = 0;
            $map[$name]     = $value;
            $result         = $this->field($fields)->where($map)->select();
        } catch (\Exception $e) {
            Log::write($e->getMessage());
            $result = [];
        }
        return $result;
    }

    /**
     * @param $id
     * @return array|mixed
     */
    public function getGoodsSuiteDetailById($id) {
        try {
            $point_number = get_config_value('point_number',0);
            $num = "CAST(gsd.num AS DECIMAL(19,".$point_number.")) num";
            $sql                = "SELECT gsd.rec_id as id, gs.spec_id,gs.goods_id,gs.spec_no,gg.goods_no,gg.goods_name,gs.spec_name,gs.spec_code,
                                ".$num.",gsd.fixed_price AS retail_price,gsd.ratio,gsd.is_fixed_price,gs.is_allow_neg_stock,gs.weight,
                                gs.tax_rate,gs.large_type,gs.barcode,gg.goods_type/*,IFNULL(ss.stock_num,0) AS stock_num*/
                                FROM goods_suite_detail gsd
                                LEFT JOIN goods_spec gs ON gsd.spec_id = gs.spec_id
                                LEFT JOIN goods_goods gg ON gs.goods_id = gg.goods_id
                                /*LEFT JOIN stock_spec ss ON(gsd.spec_id = ss.spec_id)*/
                                WHERE gsd.suite_id = %d";
            $goods_suite_detail = $this->query($sql, $id);
            //获取组合装下单品的可用库存
            foreach($goods_suite_detail as &$v1)
            {
                $spec_id = $v1['spec_id'];
                $stock_sql = "SELECT sum(stock_num),sum(order_num),sum(sending_num) FROM stock_spec WHERE spec_id = %d";
                $stock_res = $this -> query($stock_sql,$spec_id);
                if(!empty($stock_res[0]['sum(stock_num)']))
                {
                    $v1['avaliable_num'] = $stock_res[0]['sum(stock_num)'] - $stock_res[0]['sum(order_num)'] - $stock_res[0]['sum(sending_num)'];
                }else
                {
                    $v1['avaliable_num'] = '未入库';
                }
            }
        } catch (\Exception $e) {
            Log::write($e->getMessage());
            $goods_suite_detail = [];
        }
        return $goods_suite_detail;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function removeGoodsSuiteById($id) {
        try {
            $this->startTrans();
            foreach($id as $v) {
                $goods_suite = $this->where("suite_id = " . $v)->getField("suite_name");
                $goods_merchant_no_db = M("goods_merchant_no");
                $goods_barcode_db = M("goods_barcode");
                $goods_suite_detail_db = M("goods_suite_detail");
                $goods_merchant_no_db->where(["type" => 2, "target_id" => $v])->delete();
                $goods_barcode_db->where(["type" => 2, "target_id" => $v])->delete();
                $goods_suite_detail_db->where(["suite_id" => $v])->delete();
                $this->where(["suite_id" => $v])->save(["deleted" => strtotime(date("Y-m-d G:i:s"))]);
                $sql = "UPDATE api_goods_spec ags SET ags.match_target_id=0,ags.match_target_type=0 WHERE ags.is_deleted=0 AND ags.match_target_id=%d AND ags.match_target_type=2";
                $this->execute($sql, $v);
                $goods_log_db = M("goods_log");
                $arr_goods_log = [
                    "goods_type" => "2",
                    "goods_id" => $v,
                    "spec_id" => "0",
                    "operator_id" => get_operator_id(),
                    "operate_type" => "33",
                    "message" => "删除组合装--" . $goods_suite,
                    "created" => date("Y-m-d G:i:s")
                ];
                $goods_log_db->add($arr_goods_log);
            }
            $this->commit();
        } catch (\PDOException $e) {
            $this->rollback();
            Log::write($e->getMessage());
            SE(self::PDO_ERROR);
        } catch (BusinessLogicException $e) {
            $this->rollback();
            SE($e->getMessage());
        } catch (\Exception $e) {
            $this->rollback();
            Log::write($e->getMessage());
            SE(self::PDO_ERROR);
        }
    }

    /**
     * 创建组合装
     * author:luyanfeng
     * @param $goods_suite
     * @param $goods_suite_detail
     * @return mixed
     */
    public function addGoodsSuite($goods_suite, $goods_suite_detail) {
        try {
            $this->startTrans();
            //检查该商家编码是否存在
            $goods_merchant_no_db  = M("goods_merchant_no");
            $result = $goods_merchant_no_db->query('SELECT count(*) AS total FROM goods_merchant_no gmn WHERE merchant_no=\'%s\'', $goods_suite['suite_no']);
            if ($result[0]['total'] != 0) SE('该商家编码已存在');
            //对数据进行合法性判断
            if (!isset($goods_suite["suite_name"]) || $goods_suite["suite_name"] == "") {
                SE("组合装名称不能为空");
            }
            if (!isset($goods_suite["suite_no"]) || $goods_suite["suite_no"] == "") {
                SE("商家编码不能为空");
            }
            if (!isset($goods_suite["weight"]) || $goods_suite["weight"] == "") {
                $goods_suite['weight'] = 0;
            }
            //插入组合装
            $goods_suite["created"]      = date("Y-m-d G:i:s");
            $goods_suite["modified"]     = date("Y-m-d G:i:s");
            $goods_suite['retail_price'] = floatval($goods_suite['retail_price']);
            $goods_suite['market_price'] = floatval($goods_suite['market_price']);
            $suite_id                    = $this->data($goods_suite)->add();
            //创建组合装的日志
            $arr_goods_log[] = [
                "goods_type"   => "2",
                "goods_id"     => $suite_id,
                "spec_id"      => "0",
                "operator_id"  => get_operator_id(),
                "operate_type" => "13",
                "message"      => "新建组合装--" . $goods_suite["suite_name"],
                "created"      => date("Y-m-d G:i:s")
            ];
            //插入商家编码
            $arr_goods_merchant_no = [
                "merchant_no" => $goods_suite["suite_no"],
                "type"        => 2,
                "target_id"   => $suite_id,
                "modified"    => date("Y-m-d G:i:s"),
                "created"     => date("Y-m-d G:i:s")
            ];
            $goods_merchant_no_db->data($arr_goods_merchant_no)->add();
            //创建商家编码的日志
            $arr_goods_log[] = [
                "goods_type"   => "2",
                "goods_id"     => $suite_id,
                "spec_id"      => "0",
                "operator_id"  => get_operator_id(),
                "operate_type" => "53",
                "message"      => "创建组合装 " . $goods_suite["suite_no"] . " 的商家编码：" . $goods_suite["suite_no"],
                "created"      => date("Y-m-d G:i:s")
            ];
            //插入条形码
            if (isset($goods_suite["barcode"]) && $goods_suite["barcode"] != "") {
                $arr_goods_barcode = [
                    "barcode"   => $goods_suite["barcode"],
                    "type"      => "2",
                    "target_id" => $suite_id,
                    "is_master" => "1",
                    "tag"       => get_seq("goods_barcode"),
                    "modified"  => date("Y-m-d G:i:s"),
                    "created"   => date("Y-m-d G:i:s")
                ];
                $goods_barcode_db  = M("goods_barcode");
                $goods_barcode_db->data($arr_goods_barcode)->add();
                //创建条形码的日志
                $arr_goods_log[] = [
                    "goods_type"   => "2",
                    "goods_id"     => $suite_id,
                    "spec_id"      => "0",
                    "operator_id"  => get_operator_id(),
                    "operate_type" => "53",
                    "message"      => "创建组合装 " . $goods_suite["suite_no"] . " 的条形码：" . $goods_suite["barcode"],
                    "created"      => date("Y-m-d G:i:s")
                ];
            }
            //插入组合装单品
            $goods_suite_detail_db = M("goods_suite_detail");
            foreach ($goods_suite_detail as $row) {
                if (!is_array($row) && !is_object($row)) {
                    continue;
                }
                unset($row["id"]);
                $temp                   = array();
                $temp['suite_id']       = $suite_id;
                $temp['created']        = date("Y-m-d G:i:s");
                $temp['modified']       = date("Y-m-d G:i:s");
                $temp["spec_id"]        = $row["spec_id"];
                $temp["num"]            = $row["num"];
                $temp["fixed_price"]    = $row["retail_price"];
                $temp["ratio"]          = $row["ratio"];
                $temp["is_fixed_price"] = $row["is_fixed_price"];
                $goods_suite_detail_db->add($temp);
            }
            //检查组合装单品非固定价格占比之和是否为1
            $goods_suite_detail_db = M("goods_suite_detail");
            $sum                   = $goods_suite_detail_db->query("SELECT SUM(ratio) AS total FROM goods_suite_detail WHERE  suite_id = %d AND is_fixed_price = 0", $suite_id);
            $sum                   = $sum[0]["total"];
            if ($sum != 1) {
                $url = C('faq_url');
                $url = '<a href="'.$url["goods_question"]."#proportion_of_amount".'" target="_blank">点击查看解决办法</a>';
                SE("非固定金额占比之和不能为0，{$url}");
            }
            //匹配平台货品
            $message = "";
            D("goods_goods")->matchApiGoodsSpecByMerchantNo($this, $goods_suite["suite_no"], $message);
            //插入日志
            $goods_log_db = M("goods_log");
            $goods_log_db->addAll($arr_goods_log);
            //插入创建组合装单品的日志
            $user_id = get_operator_id();
            $sql     = "INSERT INTO goods_log(goods_type, goods_id, spec_id, operator_id, operate_type, message, created)
                SELECT 2,$suite_id,gsd.spec_id,$user_id,12,CONCAT('添加单品', '--', gs.spec_no),NOW()
                FROM goods_suite_detail gsd LEFT JOIN goods_spec gs ON gs.spec_id = gsd.spec_id WHERE suite_id = %d;";
            $goods_log_db->execute($sql, $suite_id);
            $this->commit();
        } catch (\PDOException $e) {
            $this->rollback();
            Log::write($e->getMessage());
            SE(self::PDO_ERROR);
        } catch (BusinessLogicException $e) {
            $this->rollback();
            SE($e->getMessage());
        } catch (\Exception $e) {
            $this->rollback();
            Log::write($e->getMessage());
            SE(self::PDO_ERROR);
        }
    }
 
    /**
     * 更新组合装
     * author:luyanfeng
     * @param $goods_suite
     * @param $goods_suite_detail
     */
    public function updateGoodsSuite($goods_suite, $goods_suite_detail) {
        $sql_err_info = "";
        try {
            $this->startTrans();
            //检查该商家编码是否存在
            $goods_merchant_no_db = M("goods_merchant_no");
            $result = $goods_merchant_no_db->query('SELECT gmn.type,gmn.target_id FROM goods_merchant_no gmn WHERE merchant_no=\'%s\'', $goods_suite['suite_no']);
            if (count($result) != 0 && ($result[0]['type'] != 2 || $result[0]['target_id'] != $goods_suite['suite_id'])) {
                SE('该商家编码已存在');
            }
            //对数据进行合法性判断
            if (!isset($goods_suite["suite_name"]) || $goods_suite["suite_name"] == "") {
                SE('组合装名称不能为空');
            }
            if (!isset($goods_suite["suite_no"]) || $goods_suite["suite_no"] == "") {
                SE('商家编码不能为空');
            }
            //日志信息
            $arr_goods_log[] = [
                "goods_type"   => "2",
                "goods_id"     => $goods_suite["suite_id"],
                "spec_id"      => "0",
                "operator_id"  => get_operator_id(),
                "operate_type" => "53",
                "message"      => "更新组合装，组合装商家编码为 " . $goods_suite["suite_no"],
                "created"      => date("Y-m-d G:i:s")
            ];
            //获取原组合装数据
            $sql_err_info    = "updateGoodsSuite--getOldGoodsSuite";
            $old_goods_suite = $this->field(true)->where(array("suite_id" => $goods_suite["suite_id"]))->find();
            //检查组合装名称是否改变
            if ($goods_suite["suite_name"] != $old_goods_suite["suite_name"]) {
                $arr_goods_log[] = [
                    "goods_type"   => "2",
                    "goods_id"     => $goods_suite["suite_id"],
                    "spec_id"      => "0",
                    "operator_id"  => get_operator_id(),
                    "operate_type" => "53",
                    "message"      => "修改组合装名称，从 " . $old_goods_suite["suite_name"] . " 到 " . $goods_suite["suite_name"],
                    "created"      => date("Y-m-d G:i:s")
                ];
            }
            //检查条形码是否改变
            if ($goods_suite["barcode"] != $old_goods_suite["barcode"]) {
                //更新组合装条形码
                $goods_barcode_db = M("goods_barcode");
                $sql_err_info     = "updateGoodsSuite--updateGoodsBarcode";
                if($old_goods_suite['barcode'] != ""){
                    $goods_barcode_db->where(array("type" => 2, "target_id" => $goods_suite["suite_id"], "is_master" => 1,'barcode'=>$old_goods_suite['barcode']))->delete();
                }
                if ($goods_suite["barcode"] != "") {
                    $count = $goods_barcode_db->where(array('type'=>2,'target_id'=>$goods_suite['suite_id'],'barcode'=>$goods_suite['barcode']))->count();
                    if($count){
                        $goods_barcode_db->where(array('type'=>2,'target_id'=>$goods_suite['suite_id'],'barcode'=>$goods_suite['barcode']))->save(array('is_master'=>1,"modified"=>date("Y-m-d H:i:s", time())));
                    }else{
                        $goods_barcode_db->data(array("type" => 2, "target_id" => $goods_suite["suite_id"], "is_master" => 1, "tag" => get_seq("goods_barcode"), "barcode" => $goods_suite["barcode"]))->add();
                    }
                }
                $arr_goods_log[] = [
                    "goods_type"   => "2",
                    "goods_id"     => $goods_suite["suite_id"],
                    "spec_id"      => "0",
                    "operator_id"  => get_operator_id(),
                    "operate_type" => "53",
                    "message"      => "修改组合装条码，从 " . $old_goods_suite["barcode"] . " 到 " . $goods_suite["barcode"],
                    "created"      => date("Y-m-d G:i:s")
                ];
            }
            $api_gooods_spec_db = M("api_goods_spec");
            //检查组合装商家编码是否改变
            if ($goods_suite["suite_no"] != $old_goods_suite["suite_no"]) {
                //更新组合装商家编码
                $sql_err_info         = "updateGoodsSuite--updateGoodsMerchantNo";
                $goods_merchant_no_db->where(array("type" => "2", "target_id" => $goods_suite["suite_id"]))->save(array("merchant_no" => $goods_suite["suite_no"]));
                //更新平台货品
                $goods_goods_model = D("GoodsGoods");
                $sql_err_info      = "updateGoodsSuite--matchApiGoodsSpecByTargetId";
                $goods_goods_model->matchApiGoodsSpecByTargetId(M(), 2, $goods_suite["suite_id"], $sql_err_info);
                $sql_err_info = "updateGoodsSuite--matchApiGoodsSpecByMerchantNo";
                $goods_goods_model->matchApiGoodsSpecByMerchantNo(M(), $goods_suite["suite_no"], $sql_err_info);
                $arr_goods_log[] = [
                    "goods_type"   => "2",
                    "goods_id"     => $goods_suite["suite_id"],
                    "spec_id"      => "0",
                    "operator_id"  => get_operator_id(),
                    "operate_type" => "53",
                    "message"      => "修改组合装商家编码，从 " . $old_goods_suite["suite_no"] . " 到 " . $goods_suite["suite_no"],
                    "created"      => date("Y-m-d G:i:s")
                ];
            }
            //检查组合装品牌是否改变
            if ($goods_suite["brand_id"] != $old_goods_suite["brand_id"]) {
                //刷新平台货品
                $sql_err_info = "updateGoodsSuite--refreshApiGoodsSpecByGoodsBrand";
                $api_gooods_spec_db->where(array("is_deleted" => "0", "match_target_type" => 2, "match_target_id" => $goods_suite["suite_id"]))->save(array("brand_id" => $goods_suite["brand_id"]));
                //创建组合装品牌更新日志
                $goods_brand_db  = M("goods_brand");
                $sql_err_info    = "updateGoodsSuite--getGoodsBrandName";
                $brand_name      = $goods_brand_db->field("brand_name")->where(array("brand_id" => $goods_suite["brand_id"]))->find();
                $brand_name      = $brand_name["brand_name"];
                $old_brand_name  = $goods_brand_db->field("brand_name")->where(array("brand_id" => $old_goods_suite["brand_id"]))->find();
                $old_brand_name  = $old_brand_name["brand_name"];
                $arr_goods_log[] = [
                    "goods_type"   => "2",
                    "goods_id"     => $goods_suite["suite_id"],
                    "spec_id"      => "0",
                    "operator_id"  => get_operator_id(),
                    "operate_type" => "53",
                    "message"      => "修改组合装品牌，从 " . $old_brand_name . " 到 " . $brand_name,
                    "created"      => date("Y-m-d G:i:s")
                ];
            }
            //检查组合装分类是否改变
            if ($goods_suite["class_id"] != $old_goods_suite["class_id"]) {
                //刷新平台货品
                $sql_err_info   = "updateGoodsSuite--refreshApiGoodsSpecByGoodsClass";
                $goods_class_db = M("goods_class");
                $class_path     = $goods_class_db->field("path")->where(array("class_id" => $goods_suite["class_id"]))->find();
                $api_gooods_spec_db->where(array("is_deleted" => "0", "match_target_type" => "2", "match_target_id" => $goods_suite["suite_id"]))->save(array("class_id_path" => $class_path["path"]));
                //创建组合装分类更新日志
                $sql_err_info    = "updateGoodsSuite--getGoodsClassName";
                $class_name      = $goods_class_db->field("class_name")->where(array("class_id" => $goods_suite["class_id"]))->select();
                $class_name      = $class_name[0]["class_name"];
                $old_class_name  = $goods_class_db->field("class_name")->where(array("class_id" => $goods_suite["class_id"]))->select();
                $old_class_name  = $old_class_name[0]["class_name"];
                $arr_goods_log[] = [
                    "goods_type"   => "2",
                    "goods_id"     => $goods_suite["suite_id"],
                    "spec_id"      => "0",
                    "operator_id"  => get_operator_id(),
                    "operate_type" => "53",
                    "message"      => "修改组合装分类，从 " . $old_class_name . " 到 " . $class_name,
                    "created"      => date("Y-m-d G:i:s")
                ];
            }
            //检查组合装零售价是否改变
            if ($goods_suite["retail_price"] != $old_goods_suite["retail_price"]) {
                $arr_goods_log[] = [
                    "goods_type"   => "2",
                    "goods_id"     => $goods_suite["suite_id"],
                    "spec_id"      => "0",
                    "operator_id"  => get_operator_id(),
                    "operate_type" => "53",
                    "message"      => "修改组合装零售价，从 " . $old_goods_suite["retail_price"] . " 到 " . $goods_suite["retail_price"],
                    "created"      => date("Y-m-d G:i:s")
                ];
            }
            //检查组合装市场价是否改变
            if ($goods_suite["market_price"] != $old_goods_suite["market_price"]) {
                $arr_goods_log[] = [
                    "goods_type"   => "2",
                    "goods_id"     => $goods_suite["suite_id"],
                    "spec_id"      => "0",
                    "operator_id"  => get_operator_id(),
                    "operate_type" => "53",
                    "message"      => "修改组合装市场价，从 " . $old_goods_suite["market_price"] . " 到 " . $goods_suite["market_price"],
                    "created"      => date("Y-m-d G:i:s")
                ];
            }
            $goods_suite_detail_db = M("goods_suite_detail");
            $goods_spec_db         = M("goods_spec");
            $sql_err_info          = "updateGoodsSuite--getOldGoodsSuiteDetail";
            //获取原来的组合装单品的数据
            $old_goods_suite_detail = $goods_suite_detail_db->where(array("suite_id" => $goods_suite["suite_id"]))->field(true)->select();
            //删除旧的组合装单品的数据
            $goods_suite_detail_db->where(array("suite_id" => $goods_suite["suite_id"]))->delete();
            foreach ($goods_suite_detail as $key => $value) {
                //调整单品数据
                /*if (isset($value["id"]) && $value["id"] != "") {
                    $value["spec_id"] = $value["id"];
                    unset($value["id"]);
                }*/
                $value["fixed_price"] = $value["retail_price"];
                unset($value["retail_price"]);
                $old_value = [];//使用该变量来记录原来已经有的组合装单品
                foreach ($old_goods_suite_detail as $old_k => $old_v) {
                    if ($value["spec_id"] == $old_v["spec_id"]) {
                        $old_value = $old_v;
                        break;
                    }
                }
                if (empty($old_value)) {
                    //插入组合装单品
                    $temp         = [
                        "suite_id"       => $goods_suite["suite_id"],
                        "spec_id"        => $value["spec_id"],
                        "num"            => $value["num"],
                        "fixed_price"    => $value["fixed_price"],
                        "ratio"          => $value["ratio"],
                        "is_fixed_price" => $value["is_fixed_price"],
                        "created"        => date("Y-m-d G:i:s"),
                        "modified"       => date("Y-m-d G:i:s")
                    ];
                    $sql_err_info = "updateGoodsSuite--insertGoodsSuiteDetail";
                    $goods_suite_detail_db->data($temp)->add();
                    //创建组合装日志
                    $sql_err_info    = "updateGoodsSuite--getSpecNo";
                    $spec_no         = $goods_spec_db->where(array("spec_id" => $value["spec_id"]))->field("spec_no")->select();
                    $spec_no         = $spec_no[0]["spec_no"];
                    $arr_goods_log[] = [
                        "goods_type"   => "2",
                        "goods_id"     => $goods_suite["suite_id"],
                        "spec_id"      => $value["spec_id"],
                        "operator_id"  => get_operator_id(),
                        "operate_type" => "12",
                        "message"      => "添加单品--" . $spec_no,
                        "created"      => date("Y-m-d G:i:s")
                    ];
                } else {
                    //更新组合装单品
                    $temp = [
                        "suite_id"       => $goods_suite["suite_id"],
                        "spec_id"        => $value["spec_id"],
                        "num"            => $value["num"],
                        "fixed_price"    => $value["fixed_price"],
                        "ratio"          => $value["ratio"],
                        "is_fixed_price" => $value["is_fixed_price"],
                        "modified"       => date("Y-m-d G:i:s")
                    ];
                    $goods_suite_detail_db->data($temp)->add();
                    //创建更新日志
                    $sql_err_info = "updateGoodsSuite--getSpecNo";
                    $spec_no      = $goods_spec_db->where(array("spec_id" => $value["spec_id"]))->field("spec_no")->select();
                    $spec_no      = $spec_no[0]["spec_no"];
                    //添加单品数量的更新日志
                    if ($value["num"] != $old_value["num"]) {
                        $arr_goods_log[] = [
                            "goods_type"   => "2",
                            "goods_id"     => $goods_suite["suite_id"],
                            "spec_id"      => $value["spec_id"],
                            "operator_id"  => get_operator_id(),
                            "operate_type" => "53",
                            "message"      => "修改单品 " . $spec_no . " 数量，从 " . $old_value["num"] . " 到 " . $value["num"],
                            "created"      => date("Y-m-d G:i:s")
                        ];
                    }
                    //添加金额占比的更新日志
                    $spec_no = $goods_spec_db->where(array("spec_id" => $value["spec_id"]))->field("spec_no")->find();
                    $spec_no = $spec_no["spec_no"];
                    if ($value["ratio"] != $old_value["ratio"]) {
                        $arr_goods_log[] = [
                            "goods_type"   => "2",
                            "goods_id"     => $goods_suite["suite_id"],
                            "spec_id"      => $value["spec_id"],
                            "operator_id"  => get_operator_id(),
                            "operate_type" => "53",
                            "message"      => "修改商家编码为 " . $spec_no . " 的单品金额占比，从 " . $old_value["ratio"] . " 到 " . $value["ratio"],
                            "created"      => date("Y-m-d G:i:s")
                        ];
                    }
                    //添加是否开启固定价格的更新日志
                    $old_is_fixed_price = $old_value["is_fixed_price"] == 1 ? "是" : "否";
                    $is_fixed_price     = $value["is_fixed_price"] == 1 ? "是" : "否";
                    if ($value["is_fixed_price"] != $old_value["is_fixed_price"]) {
                        $arr_goods_log[] = [
                            "goods_type"   => "2",
                            "goods_id"     => $goods_suite["suite_id"],
                            "spec_id"      => $value["spec_id"],
                            "operator_id"  => get_operator_id(),
                            "operate_type" => "53",
                            "message"      => "修改单品是否固定价格，从 " . $old_is_fixed_price . " 到 " . $is_fixed_price,
                            "created"      => date("Y-m-d G:i:s")
                        ];
                    }
                }
            }
            //检查非固定价格占比之和是否唯一
            $sql_sum_ratio = "SELECT SUM(ratio) AS sum FROM goods_suite_detail WHERE suite_id=%d AND is_fixed_price=0;";
            $result        = $goods_suite_detail_db->query($sql_sum_ratio, $goods_suite["suite_id"]);
            if ($result[0]["sum"] != 1) {
                $url = C('faq_url');
                $url = '<a href="'.$url["goods_question"]."#proportion_of_amount".'" target="_blank">点击查看解决办法</a>';
                SE("非固定金额占比之和不能为0，{$url}");
            }
        
            //更新组合装数据
            $goods_suite["modified"] = date("Y-m-d G:i:s");
            //组合装重量默认为0
            if(!isset($goods_suite['weight']) || $goods_suite['weight'] == ''){
                $goods_suite['weight'] = 0;
            }
            $sql_err_info            = "updateGoodsSuite--updateGoodsSuite";
            $this->data($goods_suite)->save();
            //刷新库存同步规则
            $platform_goods_model = D("PlatformGoods");
            $platform_goods_model->updateStockSyncRule(1, $goods_suite["suite_id"]);
            //插入操作日志
            $goods_log_db = M("goods_log");
            $goods_log_db->addAll($arr_goods_log);
            $this->commit();
        } catch (\PDOException $e) {
            $this->rollback();
            Log::write($sql_err_info . ":" . $e->getMessage());
            SE(self::PDO_ERROR);
        } catch (BusinessLogicException $e) {
            $this->rollback();
            SE($e->getMessage());
        } catch (\Exception $e) {
            $this->rollback();
            Log::write($sql_err_info . ":" . $e->getMessage());
            SE(self::PDO_ERROR);
        }
    }

    /**
     * 判断excel导入的组合装信息
     * @param $data  excel导入中每行的数据
     * @param array
     */
    public function importSpec($data)
    {
        try {
            foreach($data as $k1 => $v1)
            {
                $goods_suite        = $v1['goods_suite'];
                $goods_suite_detail = $v1['goods_suite_detail'];
                $line               = $v1['line']; 
                $suite_no = $goods_suite['suite_no'];
                //判断组合装是否已在系统中存在
                $old_suite_info = M('goods_suite') -> where("suite_no = '{$suite_no}' AND deleted = 0") -> find();
                $old_merchant_no = M('goods_merchant_no') -> where("merchant_no = '{$suite_no}'") -> find();
                if(!empty($old_suite_info))
                {
                    SE("组合装已在系统中存在，第{$line}行",'',1);
                }
                //商家编码不能重复
                if(!empty($old_merchant_no)){
                    SE("商家编码已存在，第{$line}行",'',1);
                }
                //判断每一行的信息是否正确
                //组合装名称不能为空
                if(!isset($goods_suite['suite_name']) || $goods_suite['suite_name'] == '')
                {
                    SE("组合装名称不能为空，第{$line}行",'',1);
                }
                //组合装商家编码不能为空
                if(!isset($goods_suite['suite_no']) || $goods_suite['suite_no'] == '')
                {
                    SE("组合装商家编码不能为空，第{$line}行",'',1);
                }
                //单品商家编码不能为空
                if(!isset($goods_suite_detail['spec_id']) || $goods_suite_detail['spec_id'] == '')
                {
                    SE("单品商家编码不能为空，第{$line}行",'',1);
                }
                //判断单品商家编码是否存在
                $where['merchant_no'] = array('eq',$goods_suite_detail['spec_id']);
                $where['type'] = array('eq',1);

                //获得单品编码在goods_merchant_no表里的详细信息
                $spec_id_info = M('goods_merchant_no') -> where($where) -> select();
                if(empty($spec_id_info))
                {
                    SE("单品商家编码{$goods_suite_detail['spec_id']}不存在，第{$line}行",'',1);
                }
                //单品价格、组合装零售价、组合装市场价不能为负
                if($goods_suite_detail['fixed_price'] < 0 || $goods_suite['retail_price'] < 0 || $goods_suite['market_price'] < 0)
                {
                    SE("单品价格、组合装零售价、组合装市场价不能为负，第{$line}行",'',1);
                }
                //单品数量不能为负
                if(!isset($goods_suite_detail['num'])||$goods_suite_detail['num']=="")
                {
                    SE("单品数量不能为空，第{$line}行",'',1);
                }
                //单品数量不能为空
                if($goods_suite_detail['num'] < 0)
                {
                    SE("单品数量不能为负,第{$line}行",'',1);
                }
                //组合装类别判断
                $result = M("goods_class") ->field("COUNT(1) AS total,class_id,is_leaf")->where(array("class_name" => $goods_suite["class_name"]))->select();
                $v1['goods_suite']["class_id"] = isset($result[0]["class_id"]) ? $result[0]["class_id"] : 0;
                if ($result[0]["is_leaf"] == 0) $v1['goods_suite']["class_id"] = 0;
                //组合装品牌判断
                $result = M("goods_brand")->field("COUNT(1) AS total,brand_id")->where(array("brand_name" => $goods_suite["brand_name"]))->select();
                $v1['goods_suite']["brand_id"] = isset($result[0]["brand_id"]) ? $result[0]["brand_id"] : 0;
                //设置单品固定价格
                switch ($v1['goods_suite_detail']['is_fixed_price']) {
                    case '是':
                        $v1['goods_suite_detail']['is_fixed_price'] = 1;
                        break;
                    default:
                        $v1['goods_suite_detail']['is_fixed_price'] = 0;
                        break;
                }
                unset($v1['goods_suite']['brand_name']);
                unset($v1['goods_suite']['class_name']);
                //将组合装分组：一个组合装里有一个单品和一个组合装里有多个单品
                $check_arr["{$goods_suite['suite_no']}"][] = $v1;

                //计算单品总价
                if($v1['goods_suite_detail']['is_fixed_price'] == 0)
                {
                    $check_arr["{$goods_suite['suite_no']}"]['sum'] += $v1['goods_suite_detail']['fixed_price']*$v1['goods_suite_detail']['num'];
                }
            }
            //验证同一个组合装里有多个单品
            foreach($check_arr as $k1 => $v1)
            {

                $check_spec_id = [];
                $sum_price = 0;
                if($v1['sum'] == 0)
                {
                    $url = C('faq_url');
                    $url = '<a href="'.$url["goods_question"]."#proportion_of_amount".'" target="_blank">点击查看解决办法</a>';
                    SE('非固定金额占比之和不能为0，组合装商家编码'.$v1[0]['goods_suite']['suite_no'].'，'.$url,'',1);
                }
                foreach($v1 as $k2 => $v2)
                {
                    //v2为每一个组合装
                    //验证单品商家编码是否重复
                    if(!in_array($v2['goods_suite_detail']['spec_id'],$check_spec_id))
                    {
                        $check_spec_id[] = $v2['goods_suite_detail']['spec_id'];
                    }else
                    {
                        SE("单品商家编码重复，第{$v2['line']}行",'',1);
                    }
                    //验证组合装信息是否一致
                    if($k2 != 0)
                    {
                        if($v1[$k2]['goods_suite'] != $v1[$k2 -1 ]['goods_suite'])
                        {
                            SE("组合装信息不一致，第{$v2['line']}行",'',1);
                        }
                    }
                    //执行插入数据库
                    if($k2 === 0)
                    {
                        $suite_goods_log = [];
                        $arr_goods_merchant_no = [];
                        $arr_goods_barcode = [];
                        //插入组合装
                        $v2['goods_suite']["created"]   = date("Y-m-d G:i:s");
                        $v2['goods_suite']["modified"]  = date("Y-m-d G:i:s");
                        $suite_id                       = $this->data($v2['goods_suite'])->add();
                        //创建组合装的日志
                        $suite_goods_log[] = [
                            "goods_type"   => "2",
                            "goods_id"     => $suite_id,
                            "spec_id"      => "0",
                            "operator_id"  => get_operator_id(),
                            "operate_type" => "12",
                            "message"      => "新建组合装--" . $v2['goods_suite']["suite_name"],
                            "created"      => date("Y-m-d G:i:s")
                        ];
                        //插入商家编码
                        $arr_goods_merchant_no = [
                            "merchant_no" => $v2['goods_suite']["suite_no"],
                            "type"        => 2,
                            "target_id"   => $suite_id,
                            "modified"    => date("Y-m-d G:i:s"),
                            "created"     => date("Y-m-d G:i:s")
                        ];
                        M('goods_merchant_no')->data($arr_goods_merchant_no)->add();
                        //创建商家编码的日志
                        $suite_goods_log[] = [
                            "goods_type"   => "2",
                            "goods_id"     => $suite_id,
                            "spec_id"      => "0",
                            "operator_id"  => get_operator_id(),
                            "operate_type" => "56",
                            "message"      => "创建组合装 " . $v2['goods_suite']["suite_no"] . " 的商家编码：" . $v2['goods_suite']["suite_no"],
                            "created"      => date("Y-m-d G:i:s")
                        ];
                        //插入条形码
                        if(isset($v2['goods_suite']["barcode"]) && $v2['goods_suite']["barcode"] != "")
                        {
                            $arr_goods_barcode = [
                            "barcode"   => $v2['goods_suite']["barcode"],
                            "type"      => "2",
                            "target_id" => $suite_id,
                            "is_master" => "1",
                            "tag"       => get_seq("goods_barcode"),
                            "modified"  => date("Y-m-d G:i:s"),
                            "created"   => date("Y-m-d G:i:s")
                            ];
                            $goods_barcode_db  = M("goods_barcode");
                            $goods_barcode_db->data($arr_goods_barcode)->add();
                            //创建条形码的日志
                            $suite_goods_log[] = [
                                "goods_type"   => "2",
                                "goods_id"     => $suite_id,
                                "spec_id"      => "0",
                                "operator_id"  => get_operator_id(),
                                "operate_type" => "58",
                                "message"      => "创建组合装 " . $v2['goods_suite']["suite_no"] . " 的条形码：" . $v2['goods_suite']["barcode"],
                                "created"      => date("Y-m-d G:i:s")
                            ];
                        }
                        M('goods_log') -> addAll($suite_goods_log);
                    }  
                    //插入组合装单品
                    $goods_suite_detail_db = M("goods_suite_detail");
                    $spec_id = M('goods_spec') -> field("spec_id") -> where(array("spec_no" => array("eq",$v2['goods_suite_detail']['spec_id']))) -> find();
                    $v2['goods_suite_detail']['suite_id']       = $suite_id;
                    $v2['goods_suite_detail']['spec_id']       = $spec_id["spec_id"];
                    $v2['goods_suite_detail']['created']        = date("Y-m-d G:i:s");
                    $v2['goods_suite_detail']['modified']       = date("Y-m-d G:i:s");
                    if($v2['goods_suite_detail']['is_fixed_price'] == 0)
                    {
                        $v2['goods_suite_detail']["ratio"]          = $v2['goods_suite_detail']['fixed_price']/$v1['sum']*$v2['goods_suite_detail']['num'];
                    }else
                    {
                        $v2['goods_suite_detail']["ratio"] = 0;
                    }
                    $rec_id = $goods_suite_detail_db->add($v2['goods_suite_detail']);
                    //匹配平台货品
                    $message = "";
                    D("goods_goods")->matchApiGoodsSpecByMerchantNo($this, $v2['goods_suite_detail']["suite_id"], $message);
                    //插入日志
                    $goods_log_db = M("goods_log");
                    //插入创建组合装单品的日志
                    $user_id = get_operator_id();
                    $sql     = "INSERT INTO goods_log(goods_type, goods_id, spec_id, operator_id, operate_type, message, created)
                        SELECT 2,$suite_id,gsd.spec_id,$user_id,12,CONCAT('添加单品', '--', gs.spec_no),NOW()
                        FROM goods_suite_detail gsd LEFT JOIN goods_spec gs ON gs.spec_id = gsd.spec_id WHERE rec_id = '%d'";

                    $goods_log_db->execute($sql, $rec_id);        
                }
            }
        } catch (\PDOException $e) {
            SE($e->getMessage());
        } catch (BusinessLogicException $e) {
            SE($e->getMessage(),'',$e -> getCode());
        } catch (\Exception $e) {
            SE($e->getMessage());
        }
    }

    public function updateSuite($data,&$error_list)
    {
        try
        {
            $goods_spec_db      = M("goods_spec");
            $goods_class_db     = M("goods_class");
            $goods_brand_db     = M("goods_brand");
            $goods_barcode_db   = M("goods_barcode");
            $goods_log_db       = M("goods_log");
            $suite_detail_db    = M('goods_suite_detail');
            $operator_id    = get_operator_id();
            foreach($data as $key=>$value)
            {
                $suite_detail = $value['goods_suite_detail'];
                $suite = $value['goods_suite'];
                //检查单品商家编码和组合装商家编码是否为空
                if (!isset($suite_detail["spec_id"]) || $suite_detail["spec_id"] == "")
                {
                    $error_list[] = array('id'=>$key+2,'result'=>'更新失败','message'=>'单品商家编码不能为空');
                    continue;
                }
                //检查货品编号是否为空
                if (!isset($suite["suite_no"]) || $suite["suite_no"] == "")
                {
                    $error_list[] = array('id'=>$key+2,'result'=>'更新失败','message'=>'组合装商家编码不能为空');
                    continue;
                }
                //检查组合装名称是否为空
                if (!isset($suite["suite_name"]) || $suite["suite_name"] == "")
                {
                    $error_list[] = array('id'=>$key+2,'result'=>'更新失败','message'=>'组合装名称不能为空');
                    continue;
                }
                //首先单品的商家编码和组合装的商家编码得存在。
                $org_spec_id = $goods_spec_db->field('spec_id')->where(array('spec_no'=>$suite_detail['spec_id']))->find();

                if (!$org_spec_id)
                {
                    $error_list[] = array('id'=>$key+2,'result'=>'更新失败','message'=>'单品商家编码不存在');
                    continue;
                }

                //检查价格是否为负数
                if ($suite["retail_price"] < 0 || $suite["market_price"] < 0 ) {
                    $error_list[] = array('id'=>$key+2,'result'=>'更新失败','message'=>'组合装的零售价和市场价不能为负数');
                    continue;
                }
                //检查重量是否为负数
                if ($suite["weight"] < 0  ) {
                    $error_list[] = array('id'=>$key+2,'result'=>'更新失败','message'=>'组合装的重量不能为负数');
                    continue;
                }
                //查询class_id，无则设为0 如果为空则不更新
                if($suite['class_name']!='')
                {
                    $result            = $goods_class_db->field("COUNT(1) AS total,class_id,is_leaf")->where(array("class_name" => $suite["class_name"]))->select();
                    $goods["class_id"] = isset($result[0]["class_id"]) ? $result[0]["class_id"] : 0;
                    if ($result[0]["is_leaf"] == 0) $goods["class_id"] = 0;
                }
                //查询brand_id，无则设为0
                if($suite["brand_name"]!='')
                {
                    $result            = $goods_brand_db->field("COUNT(1) AS total,brand_id")->where(array("brand_name" => $suite["brand_name"]))->select();
                    $goods["brand_id"] = isset($result[0]["brand_id"]) ? $result[0]["brand_id"] : 0;
                }
                //单品价格是否固定
                switch ($suite_detail["is_fixed_price"]) {
                    case "是":
                        $suite_detail["is_fixed_price"] = 1;
                        break;
                    case "否":
                        $suite_detail["is_fixed_price"] = 0;
                        break;
                    default:
                        $suite_detail["is_fixed_price"] = 0;
                }

                $this->startTrans();

                //检查更新后的组合装商家编码是否存在
                $org_suite_id = $this->field('suite_id,barcode,retail_price,suite_name,market_price,weight,remark,class_id,brand_id')->where(array('suite_no'=>$suite['suite_no'],'deleted'=>0))->find();
                //存在往这个goods_suite 下更新信息，不存在的话先插入goods_suite
                if($org_suite_id)
                {
                    $in_suite = $suite;
                    unset($in_suite['suite_no']);
                    if($in_suite['barcode']==='') unset($in_suite['barcode']);
                    if($in_suite['retail_price']==='') unset($in_suite['retail_price']);
                    if($in_suite['market_price']==='') unset($in_suite['market_price']);
                    if($in_suite['weight']==='') unset($in_suite['weight']);
                    if($in_suite['remark']==='') unset($in_suite['remark']);
                    $in_suite['deleted'] = 0;
                    $suite_id = $org_suite_id['suite_id'];
                    $this->data($in_suite)->where(array('suite_id'=>$suite_id))->save();
                    $suite_goods_log[] = [
                        "goods_type"   => "2",
                        "goods_id"     => $suite_id,
                        "spec_id"      => "0",
                        "operator_id"  => $operator_id,
                        "operate_type" => "61",
                        "message"      => "更新导入更新组合装,组合装商家编码为 " . $suite["suite_no"] ,
                        "created"      => date("Y-m-d G:i:s")
                    ];
                    //更新条码
                    if($org_suite_id['barcode']!=$suite['barcode'])
                    {
                        if($org_suite_id['barcode'] != "")
                        {
                            $goods_barcode_db->where(array("type" => 2, "target_id" => $suite_id, "is_master" => 1,'barcode'=>$org_suite_id['barcode']))->delete();
                        }
                        if ($suite["barcode"] != "")
                        {
                            $count = $goods_barcode_db->where(array('type'=>2,'target_id'=>$suite_id,'barcode'=>$suite['barcode']))->count();
                            if($count)
                            {
                                $goods_barcode_db->where(array('type'=>2,'target_id'=>$suite_id,'barcode'=>$suite['barcode']))->save(array('is_master'=>1,"modified"=>date("Y-m-d H:i:s", time())));
                            }else
                            {
                                $goods_barcode_db->data(array("type" => 2, "target_id" => $suite_id, "is_master" => 1, "tag" => get_seq("goods_barcode"), "barcode" => $suite["barcode"]))->add();
                            }
                        }
                        $arr_goods_log[] = [
                            "goods_type"   => "2",
                            "goods_id"     => $suite["suite_id"],
                            "spec_id"      => "0",
                            "operator_id"  => $operator_id,
                            "operate_type" => "58",
                            "message"      => "修改组合装条码，从 " . $org_suite_id["barcode"] . " 到 " . $suite["barcode"],
                            "created"      => date("Y-m-d G:i:s")
                        ];
                    }
                    if($org_suite_id['retail_price']!=$in_suite['retail_price'] )
                    {
                        $arr_goods_log[] = [
                            "goods_type"   => "2",
                            "goods_id"     => $suite["suite_id"],
                            "spec_id"      => "0",
                            "operator_id"  => $operator_id,
                            "operate_type" => "58",
                            "message"      => "修改组合装零售价，从 " . $org_suite_id["retail_price"] . " 到 " . $suite["retail_price"],
                            "created"      => date("Y-m-d G:i:s")
                        ];
                    }
                    if($org_suite_id['market_price']!=$in_suite['market_price'] )
                    {
                        $arr_goods_log[] = [
                            "goods_type"   => "2",
                            "goods_id"     => $suite["suite_id"],
                            "spec_id"      => "0",
                            "operator_id"  => $operator_id,
                            "operate_type" => "58",
                            "message"      => "修改组合装市场价，从 " . $org_suite_id["market_price"] . " 到 " . $suite["market_price"],
                            "created"      => date("Y-m-d G:i:s")
                        ];
                    }
                    if($org_suite_id['weight']!=$in_suite['weight'] )
                    {
                        $arr_goods_log[] = [
                            "goods_type"   => "2",
                            "goods_id"     => $suite["suite_id"],
                            "spec_id"      => "0",
                            "operator_id"  => $operator_id,
                            "operate_type" => "58",
                            "message"      => "修改组合重量，从 " . $org_suite_id["weight"] . " 到 " . $suite["weight"],
                            "created"      => date("Y-m-d G:i:s")
                        ];
                    }

                    unset($in_suite);
                }else
                {
                    $suite_id = $this->data($suite)->add();
                    M('goods_merchant_no')->data($suite['suite_no'])->add();
                    //创建商家编码的日志
                    $suite_goods_log[] = [
                        "goods_type"   => "2",
                        "goods_id"     => $suite_id,
                        "spec_id"      => "0",
                        "operator_id"  => get_operator_id(),
                        "operate_type" => "56",
                        "message"      => "更新导入创建组合装 " . $suite["suite_name"] . " 商家编码：" . $suite["suite_no"],
                        "created"      => date("Y-m-d G:i:s")
                    ];
                    if (isset($suite["barcode"]) && $suite["barcode"] != "")
                    {
                        $arr_goods_barcode = [
                            "barcode"   => $suite["barcode"],
                            "type"      => "2",
                            "target_id" => $suite_id,
                            "is_master" => "1",
                            "tag"       => get_seq("goods_barcode"),
                            "modified"  => date("Y-m-d G:i:s"),
                            "created"   => date("Y-m-d G:i:s")
                        ];
                        $goods_barcode_db->data($arr_goods_barcode)->add();
                        //创建条形码的日志
                        $arr_goods_log[] = [
                            "goods_type"   => "2",
                            "goods_id"     => $suite_id,
                            "spec_id"      => "0",
                            "operator_id"  => $operator_id,
                            "operate_type" => "58",
                            "message"      => "更新导入创建组合装 " . $suite["suite_no"] . " 的条形码：" . $suite["barcode"],
                            "created"      => date("Y-m-d G:i:s")
                        ];
                    }

                }

                //单品如果在原组合装存在的话更新，不存在新增
                $suite_spec_res = $suite_detail_db->field('spec_id,num,fixed_price')->where(array('suite_id'=>$suite_id))->select();
                $suite_spec_id = array();
                foreach($suite_spec_res as $n)
                {
                    $suite_spec_id[] = $n['spec_id'];
                }
                $suite_detail['suite_no'] =$suite_detail['spec_id'];
                unset($suite_detail['spec_id']);
                if($suite_detail['num']==='')            unset($suite_detail['num']);
                if($suite_detail['fixed_price']==='')    unset($suite_detail['fixed_price']);
                if($suite_detail['is_fixed_price']==='') unset($suite_detail['is_fixed_price']);
                //if($org_suite_id['suite_id']!=$suite_id) $suite_detail['suite_id'] = $suite_id;
                //重新计算金额占比
                $sum_price = 0;

                if(!isset($suite_detail['is_fixed_price']) || $suite_detail['is_fixed_price']==0)
                {
                    if(!in_array($org_spec_id['spec_id'] ,$suite_spec_id))
                    {
                        $merge_array['merge']['spec_id'] = $org_spec_id['spec_id'];
                        $merge_array['merge']['num'] = $suite_detail['num'];
                        $merge_array['merge']['fixed_price'] = $suite_detail['fixed_price'];
                        $suite_spec_res = array_merge($suite_spec_res,$merge_array);
                    }
                    foreach($suite_spec_res as $m=>$n)
                    {
                        if($n['spec_id']==$org_spec_id['spec_id'])
                        {
                            $suite_spec_res[$m]['fixed_price'] = $suite_detail['fixed_price'];
                            $suite_spec_res[$m]['num'] = $suite_detail['num'];
                            $suite_spec_res[$m]['sum_price'] += $suite_detail['fixed_price'] * $suite_detail['num'];//导入商品的数量和价格
                        }else
                        {
                            $suite_spec_res[$m]['sum_price'] += $n['fixed_price'] * $n['num'];//数据库原来商品的数量和价格
                        }
                        $sum_price += $suite_spec_res[$m]['sum_price'];
                    }
                }else
                {
                    $suite_detail['ratio'] = 0;
                }
                if($sum_price ==0)
                {
                    $error_list[] = array('id'=>$key+2,'result'=>'更新失败','message'=>'非固定金额占比不能为0,请添加单品单价');
                    $this->rollback();
                    continue;
                }
                $sum_ratio = 1;
                foreach($suite_spec_res as $m=>$n)
                {
                    if ($n['is_fixed_price'] == 0)
                    {
                        $suite_spec_res[$m]['ratio'] = round($suite_spec_res[$m]['sum_price'] / $sum_price * 10000) / 10000;
                        unset($suite_spec_res[$m]['sum_price']);
                        $sum_ratio -= $suite_spec_res[$m]['ratio'];
                    }
                }
                foreach ($suite_spec_res as $m=>$n)
                {
                    if ($suite_spec_res[$m]['is_fixed_price'] == 0)
                    {
                        $suite_spec_res[$m]['ratio'] = round(($suite_spec_res[$m]['ratio'] + $sum_ratio) * 10000) / 10000;
                        break;
                    }
                }

                foreach($suite_spec_res as $m=>$n)
                {
                    $n['suite_id'] = $suite_id;
                    $update_spec_id = $n['spec_id'];
                    if(!in_array($update_spec_id ,$suite_spec_id))
                    {
                        $import_suite_detail['spec_id'] = $org_spec_id['spec_id'];
                        $import_suite_detail['suite_id'] = $suite_id;
                        $import_suite_detail['ratio'] = $n['ratio'];
                        $import_suite_detail['num'] = isset($suite_detail['num'])?$suite_detail['num']:0;
                        $import_suite_detail['is_fixed_price'] = isset($suite_detail['is_fixed_price'])?$suite_detail['is_fixed_price']:0;
                        $import_suite_detail['fixed_price'] = isset($suite_detail['fixed_price'])?$suite_detail['fixed_price']:0;
                        $suite_detail_db->data($import_suite_detail)->fetchSql(false)->add();
                    }else
                    {
                        if($n['spec_id']==$org_spec_id['spec_id'])
                        {
                            unset($suite_detail['suite_no']);
                            $suite_detail['ratio'] = $n['ratio'];
                            $suite_detail_db->data($suite_detail)->where(array('spec_id'=>$update_spec_id,'suite_id'=>$suite_id))->fetchSql(false)->save();
                        }else
                        {
                            unset($n['spec_id']);
                            $suite_detail_db->data($n)->where(array('spec_id'=>$update_spec_id,'suite_id'=>$suite_id))->fetchSql(false)->save();
                        }
                    }

                    $arr_goods_log[] = [
                        "goods_type"   => "2",
                        "goods_id"     => $suite_id,
                        "spec_id"      => "0",
                        "operator_id"  => $operator_id,
                        "operate_type" => "58",
                        "message"      => "更新导入更新组合装单品 " . $n["spec_id"] ,
                        "created"      => date("Y-m-d G:i:s")
                    ];
                }

                //更新完以后检查下该组合装下是否还有单品，没有的话删除
                $spec_num=$suite_detail_db->field('count(1) count')->where(array('suite_id'=>$suite_id))->find();

                if($spec_num['count']=1)
                {
                    if($suite_id!=$org_suite_id['suite_id'])
                    {
                        $this->data(array('deleted'=>time()))->where(array('suite_id'=>$suite_id))->save();
                    }
                }
                //不填写就不更新  为原来的值



                //插入单品日志
                $arr_goods_log = array(
                    "goods_id"     => $suite_id,
                    "spec_id"      => $org_spec_id['spec_id'],
                    "operator_id"  => $operator_id,
                    "operate_type" => 43,//修改单品
                    "message"      => "组合装导入更新单品：" . $suite_detail["spec_no"]."从".$org_suite_id['suite_name'].'到'.$suite['suite_name'],
                    "created"      => date("Y-m-d H:i:s", time())
                );
                $goods_log_db->data($suite_goods_log)->add();
                $this->commit();
            }
        }catch (\PDOException $e)
        {
            $this->rollback();
            \Think\Log::write($e->getMessage());
            SE($e->getMessage());
        } catch (\Exception $e)
        {
            $this->rollback();
            \Think\Log::write($e->getMessage());
            SE($e->getMessage());
        }

    }

    public function exportToExcel($id_list, $search, $type = 'excel')
    {
        $creator = session('account');
        $excel_no = array();
        try
        {
            if(empty($id_list)){
                $where = " gs.deleted=0 ";
                $this->searchFormDeal($where, $search,'',$left_join_goods_class_str);
                $rows = $this->alias('gs')->field('suite_id')->where($where)->fetchSql(false)->select();
                for($i = 0; $i < count($rows); $i++){
                    $id_list[$i] = $rows[$i]['suite_id'];
                }
            }
            $where = array('gsd.suite_id' => array('in', $id_list));
            $suite_goods = M('goods_suite_detail')->alias('gsd')->field("gsp.spec_no,gse.suite_no,gse.suite_name,gsd.num,gsd.fixed_price,IF(gsd.is_fixed_price=0,'否','是') is_fixed_price,gse.barcode,gse.retail_price,gse.market_price,gb.brand_name,gc.class_name,gse.weight,gse.remark")->join("LEFT JOIN goods_suite gse ON gse.suite_id = gsd.suite_id")->join("LEFT JOIN goods_spec gsp ON gsd.spec_id = gsp.spec_id")->join("LEFT JOIN goods_class gc ON gse.class_id = gc.class_id")->join("LEFT JOIN goods_brand gb ON gb.brand_id=gse.brand_id")->where($where)->order('gsd.suite_id DESC')->fetchSql(false)->select();

            $num = workTimeExportNum($type);
            if(count($suite_goods) > $num){
                if($type == 'csv'){
                    SE(self::EXPORT_CSV_ERROR);
                }
                SE(self::OVER_EXPORT_ERROR);
            }
            foreach($suite_goods as $k => $v){
                $keys_arr = array_keys($v);
            }
            foreach($keys_arr as $k => $v)
            {
                switch($v)
                {
                    case 'spec_no':
                        $excel_no['spec_no'] = '单品商家编码';
                        break;
                    case 'suite_no':
                        $excel_no['suite_no'] = '组合装商家编码';
                        break;
                    case 'suite_name':
                        $excel_no['suite_name'] = '组合装名称';
                        break;
                    case 'num':
                        $excel_no['num'] = '单品数量';
                        break;
                    case 'fixed_price':
                        $excel_no['fixed_price'] = '单品单价';
                        break;
                    case 'is_fixed_price':
                        $excel_no['is_fixed_price'] = '单品固定价格(是/否)';
                        break;
                    case 'barcode':
                        $excel_no['barcode'] = '条形码';
                        break;
                    case 'retail_price':
                        $excel_no['retail_price'] = '组合装零售价';
                        break;
                    case 'market_price':
                        $excel_no['market_price'] = '组合装市场价';
                        break;
                    case 'brand_name':
                        $excel_no['brand_name'] = '品牌';
                        break;
                    case 'class_name':
                        $excel_no['class_name'] = '类别';
                        break;
                    case 'weight':
                        $excel_no['weight'] = '组合装重量';
                        break;
                    case 'remark':
                        $excel_no['remark'] = '组合装备注';
                        break;
                }
            }
            $title = '组合装';
            $filename = '组合装';
            $width_list = array('20', '20', '20', '10', '10', '25', '15', '15', '15', '10', '10', '20', '20');
            ExcelTool::Arr2Excel($suite_goods, $title, $excel_no, $width_list, $filename, $creator);
        }catch (\PDOException $e)
        {

        }catch(BusinessLogicException $e)
        {

        }catch(\Exception $e)
        {

        }

    }
}