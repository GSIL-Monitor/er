<?php
/**
 * Created by PhpStorm.
 * User: yanfeng
 * Date: 2015/9/7
 * Time: 19:23
 */
namespace Goods\Model;

use Common\Common\ExcelTool;
use Common\Common\UtilTool;
use Think\Exception;
use Think\Exception\BusinessLogicException;
use Think\Model;

class PlatformGoodsModel extends Model{

    protected $tableName = "api_goods_spec";
    protected $pk = "rec_id";

    /**
     * @param array $ids
     * @return array   $result
     * 删除平台货品，并且删除与平台货品关联的库存同步日志
     */
    public function delPlatformGoods($ids){

        //初始化ajax返回值
        $result = array('status' => 0, 'info' => '');
        try{

            //遍历每一行的行号
            foreach($ids as $rec_id){
                //获取该行号平台货品的goods_id、spec_id
                $api_goods_spec_data = $this->field('goods_id,spec_id,platform_id,shop_id')->where(array('rec_id' => array('eq', $rec_id)))->select();
                $where = array();
                $where['platform_id'] = array('eq', $api_goods_spec_data[0]['platform_id']);
                $where['shop_id'] = array('eq', $api_goods_spec_data[0]['shop_id']);
                $where['goods_id'] = array('eq', $api_goods_spec_data[0]['goods_id']);
                $where['spec_id'] = array('eq', $api_goods_spec_data[0]['spec_id']);
                $res_plat_goods_arr = M('api_stock_sync_record')->field('rec_id')->where($where)->select();
                //开启事务
                $this->startTrans();

                //遍历每一个api_stock_sync_record表
                foreach($res_plat_goods_arr as $k => $v){
                    //删除库存同步日志
                    M('api_stock_sync_record')->where("rec_id = '%d'", $res_plat_goods_arr[$k]['rec_id'])->delete();
                }

                //删除该行号的平台货品
                $this->execute("DELETE FROM api_goods_spec WHERE rec_id = %d", $rec_id);
                //提交事务
                $this->commit();
            }
        } catch(\PDOException $e){
            $this->rollback();
            \Think\Log::write($e->getMessage());
            $result = array('status' => 1, 'info' => self::PDO_ERROR);
            SE(parent::PDO_ERROR);
        } catch(BusinessLogicException $e){
            $this->rollback();
            $result = array('status' => 1, 'info' => self::PDO_ERROR);
            SE($e->getMessage());
        } catch(\Exception $e){
            $this->rollback();
            \Think\Log::write($e->getMessage());
            $result = array('status' => 1, 'info' => self::PDO_ERROR);
            SE(parent::PDO_ERROR);
        }
        return $result;
    }

    /**
     * @param int $page
     * @param int $rows
     * @param array $search
     * @param string $sort
     * @param string $order
     * @return mixed
     * 返回客户档案列表
     * author:luyanfeng
     */
    public function getPlatformGoodsList($page = 1, $rows = 10, $search = array(), $sort = 'rec_id', $order =
    'desc'){
        try{
            $where = "true ";
            $page = intval($page);
            $rows = intval($rows);
            D('Goods/PlatformGoods')->searchFormDeal($where, $search);
            $inner_sort = 'ag.' . $sort . " " . $order;
            $inner_sort = addslashes($inner_sort);
            $outer_sort = 'ag_1.' . $sort . " " . $order;
            $outer_sort = addslashes($outer_sort);
            $limit = ($page - 1) * $rows . "," . $rows;
            //先查询出需要显示的平台货品的rec_id
            $sql_result = "SELECT ag.rec_id FROM api_goods_spec ag LEFT JOIN goods_merchant_no gmn ON gmn.type=ag.match_target_type AND gmn.target_id=ag.match_target_id WHERE $where ORDER BY $inner_sort LIMIT $limit";
            //再构造SQL查询完整的数据
            $sql = "SELECT ag_1.rec_id as id,ag_1.status,ag_1.platform_id,ag_1.shop_id,ag_1.pic_url,cs.shop_name,ag_1.goods_id,ag_1.spec_id,
                ag_1.spec_code,ag_1.goods_name,ag_1.spec_name,ag_1.outer_id,ag_1.spec_outer_id,ag_1.price,ag_1.stock_num,
                ag_1.pic_url,(1-ag_1.is_manual_match) as is_auto_match,ag_1.match_code,ag_1.match_target_type,ag_1.match_target_id,
                ag_1.stock_syn_rule_no,ag_1.stock_syn_rule_id,ag_1.stock_syn_warehouses,ag_1.hold_stock_type,ag_1.hold_stock,ag_1.spec_sku_properties,
                ag_1.stock_syn_mask,ag_1.stock_syn_percent,ag_1.stock_syn_plus,ag_1.stock_syn_min,ag_1.is_auto_listing,ag_1.is_auto_delisting,
                ag_1.last_syn_num,ag_1.last_syn_time,ag_1.is_stock_changed,ag_1.pic_url,cf.flag_name,ag_1.flag_id,
                ag_1.is_disable_syn,ag_1.stock_change_count,ag_1.modified,ag_1.created,cs.platform_id,ag_1.is_name_changed
                FROM api_goods_spec ag_1
                INNER JOIN (" . $sql_result . ") ag_2 ON(ag_1.rec_id=ag_2.rec_id)
                LEFT JOIN goods_merchant_no gmn ON gmn.type=ag_1.match_target_type AND gmn.target_id=ag_1.match_target_id
                LEFT JOIN cfg_shop cs ON(ag_1.shop_id=cs.shop_id)
                LEFT JOIN cfg_flags cf ON(ag_1.flag_id=cf.flag_id)
                ORDER BY $outer_sort";
            $sql_total = "SELECT COUNT(*) AS total FROM api_goods_spec ag LEFT JOIN goods_merchant_no gmn ON gmn.type=ag.match_target_type AND gmn.target_id=ag.match_target_id WHERE $where";
            $data["total"] = $this->query($sql_total)[0]["total"];
            $data["rows"] = $this->query($sql);
        } catch(\Exception $e){
            \Think\Log::write($e->getMessage());
            $data["total"] = 0;
            $data["rows"] = "";
        }
        return $data;
    }

    /**
     * @param $id
     * @return mixed
     * 返回平台货品的匹配记录
     * author:luyanfeng
     */
    public function getTabsMatchLog($id){
        try{
            $sql = "SELECT sol.rec_id as id,sol.message,sol.created,he.fullname
                    FROM sys_other_log sol
                    LEFT JOIN hr_employee he ON(he.employee_id=sol.operator_id)
                    WHERE sol.data=%d AND sol.type=14 ORDER BY sol.rec_id DESC";
            $sql_count = "SELECT COUNT(1) AS total FROM sys_other_log sol WHERE sol.data=%d AND sol.type=14";
            $sys_other_log_tb = M("sys_other_log");
            $result = $sys_other_log_tb->query($sql_count, $id);
            $data["total"] = $result["0"]["total"];
            $data["rows"] = $sys_other_log_tb->query($sql, $id);
        } catch(\Exception $e){
            \Think\Log::write($e->getMessage());
            $data["total"] = 0;
            $data["rows"] = array();
        }
        return $data;
    }

    /**
     * @param $id
     * @return mixed
     * 返回系统货品
     * author:luyanfeng
     */
    public function getTabsSystemGoods($id){
        try{
            $match_target_type = $this->query("SELECT ags.match_target_type FROM api_goods_spec ags WHERE ags.rec_id=%d", $id);
            if(!empty($match_target_type)) $match_target_type = $match_target_type[0]["match_target_type"];
            else $match_target_type = 0;
            if($match_target_type == 1){
                $sql = "SELECT gs.spec_id as id,ags.match_target_type,gs.spec_no,gg.goods_no,gs.barcode,
                            gg.goods_name,gs.spec_name,gs.retail_price,gc.class_name,gb.brand_name,gs.remark
                             FROM api_goods_spec ags
                             INNER JOIN goods_spec gs ON(ags.rec_id=%d AND ags.match_target_id=gs.spec_id)
                             LEFT JOIN goods_goods gg ON(gs.goods_id=gg.goods_id)
                             LEFT JOIN goods_class gc ON(gg.class_id=gc.class_id)
                             LEFT JOIN goods_brand gb ON(gg.brand_id=gb.brand_id)";
                $sql_count = "SELECT gs.spec_id as id FROM api_goods_spec ags
                             INNER JOIN goods_spec gs ON(ags.rec_id=%d AND ags.match_target_id=gs.spec_id)
                             LEFT JOIN goods_goods gg ON(gs.goods_id=gg.goods_id)
                             LEFT JOIN goods_class gc ON(gg.class_id=gc.class_id)
                             LEFT JOIN goods_brand gb ON(gg.brand_id=gb.brand_id)";
            } else if($match_target_type == 2){
                $sql = "SELECT gs.suite_id as id,ags.match_target_type,gs.suite_no AS spec_no,
                              gs.barcode,gs.suite_name AS spec_name,gs.retail_price,gc.class_name,gb.brand_name,gs.remark
                              FROM api_goods_spec ags
                              INNER JOIN goods_suite gs ON(ags.rec_id=%d AND ags.match_target_id=gs.suite_id)
                              LEFT JOIN goods_class gc ON(gs.class_id=gc.class_id)
                              LEFT JOIN goods_brand gb ON(gs.brand_id=gb.brand_id)";
                $sql_count = "SELECT gs.suite_id as id FROM api_goods_spec ags
                             INNER JOIN goods_suite gs ON(ags.rec_id=%d AND ags.match_target_id=gs.suite_id)
                             LEFT JOIN goods_class gc ON(gs.class_id=gc.class_id)
                             LEFT JOIN goods_brand gb ON(gs.brand_id=gb.brand_id)";
            } else{
                $data["total"] = 0;
                $data["rows"] = array();
                return $data;
            }
            $data["total"] = count($this->query($sql_count, $id));
            $data["rows"] = $this->query($sql, $id);
        } catch(\Exception $e){
            \Think\Log::write($e->getMessage());
            $data["total"] = 0;
            $data["rows"] = "";
        }
        return $data;
    }

    /**
     * @param $id
     * @param $suite_id
     * @return mixed
     * 指定组合装
     * author:luyanfeng
     */
    public function matchGoodsSuite($id, $suite_id){
        try{

            //将平台货品与指定的组合装匹配
            $sql1 = "UPDATE api_goods_spec ags,goods_suite gs SET ags.match_target_type=2,ags.match_target_id=gs.suite_id,
                    ags.is_manual_match=1,ags.match_code=IFNULL(FN_SPEC_NO_CONV(ags.outer_id,ags.spec_outer_id),''),ags.is_stock_changed=1
                    WHERE ags.rec_id=%d AND gs.suite_id=%d;";

            //更新平台货品的数据与匹配的组合装对应
            $sql2 = "UPDATE api_goods_spec ag,goods_suite gs,goods_class gc
                    SET ag.brand_id=gs.brand_id,ag.class_id_path=gc.path
                    WHERE ag.is_deleted=0 AND ag.match_target_type=2 AND gs.suite_id=ag.match_target_id
                    AND gc.class_id=gs.class_id AND ag.rec_id=%d;";

            //更新包含该货品的订单
            $sql3 = "UPDATE api_trade ax,api_trade_order ato,api_goods_spec ag SET ato.is_invalid_goods=0,ax.bad_reason=(bad_reason&~1)
                    WHERE ato.is_invalid_goods=1 AND ato.platform_id = ag.platform_id AND ato.goods_id = ag.goods_id
                    AND ato.spec_id = ag.spec_id AND ato.status <=30 AND ax.platform_id=ato.platform_id AND ax
                    .tid=ato.tid AND ato.rec_id=%d;";

            $this->execute($sql1, array($id, $suite_id));
            $this->execute($sql2, $id);
            $this->execute($sql3, $id);
            //获取该组合装的商家编码
            $sql = "SELECT gs.suite_no FROM goods_suite gs WHERE gs.suite_id=%d";
            $goods_suite_tb = M("goods_suite");
            $goods_suite_no = $goods_suite_tb->query($sql, $suite_id);
            //刷新库存同步规则
            $this->updateStockSyncRule(4, $id);
            //记录日志
            $sys_other_log = M("sys_other_log");
            $arr_sys_other_log = array(
                "type" => "14",
                "operator_id" => get_operator_id(),
                "data" => $id,
                "message" => "平台货品关联为系统货品，系统货品的商家编码为" . $goods_suite_no[0]["suite_no"],
                "created" => date("Y-m-d G:i:s")
            );
            $sys_other_log->data($arr_sys_other_log)->add();
            $res["status"] = 1;
            $res["info"] = "操作成功";
        } catch(\Exception $e){
            \Think\Log::write($e->getMessage());
            $res["status"] = 0;
            $res["info"] = self::PDO_ERROR;
        }
        return $res;
    }

    /**
     * @param $id
     * @param $spec_id
     * @return mixed
     * 指定单品
     * author:luyanfeng
     */
    public function matchGoodsSpec($id, $spec_id){
        try{

            //将平台货品与指定的系统货品匹配
            $sql1 = "UPDATE api_goods_spec ags,goods_spec gs SET ags.match_target_type=1,ags.match_target_id=gs.spec_id,ags.is_stock_changed=1,
                      ags.is_manual_match=1,ags.match_code='' WHERE ags.rec_id=%d AND gs.spec_id=%d;";

            //更新平台货品的数据与指定的单品对应
            $sql2 = "UPDATE api_goods_spec ag,goods_spec gs,goods_goods gg,goods_class gc
                      SET ag.brand_id=gg.brand_id,ag.class_id_path=gc.path,gs.img_url=IF(gs.img_url='',ag.pic_url,gs.img_url)
                      WHERE ag.is_deleted=0 AND ag.match_target_type=1 AND gs.spec_id=ag.match_target_id
                      AND gg.goods_id=gs.goods_id AND gc.class_id=gg.class_id AND ag.rec_id=%d;";

            //更新包含该货品的订单
            $sql3 = "UPDATE api_trade ax,api_trade_order ato,api_goods_spec ag SET ato.is_invalid_goods=0,ax.bad_reason=(bad_reason&~1)
                      WHERE ato.is_invalid_goods=1 AND ato.platform_id = ag.platform_id AND ato.goods_id = ag.goods_id
                      AND ato.spec_id = ag.spec_id AND ato.status <=30 AND ax.platform_id=ato.platform_id AND ax
                      .tid=ato.tid AND ato.rec_id=%d;";

            $this->execute($sql1, array($id, $spec_id));
            $this->execute($sql2, $id);
            $this->execute($sql3, $id);
            //刷新库存同步规则
            $this->updateStockSyncRule("4", $id);
            //记录日志
            $sql = "SELECT gs.spec_no FROM goods_spec gs WHERE gs.spec_id=%d";
            $goods_spec_tb = M("goods_spec");
            $goods_spec_no = $goods_spec_tb->query($sql, $spec_id);
            $sys_other_log = M("sys_other_log");
            $arr_sys_other_log = array(
                "type" => "14",
                "operator_id" => get_operator_id(),
                "data" => $id,
                "message" => "平台货品关联为系统货品，系统货品的商家编码为" . $goods_spec_no[0]["spec_no"],
                "created" => date("Y-m-d G:i:s")
            );
            $sys_other_log->data($arr_sys_other_log)->add();
            $res["status"] = 1;
            $res["info"] = "操作成功";
        } catch(\Exception $e){
            \Think\Log::write($e->getMessage());
            $res["status"] = 0;
            $res["info"] = self::PDO_ERROR;
        }
        return $res;
    }

    /**
     * @return mixed
     * 自动匹配
     * author:luyanfeng
     */
    public function autoMatchPlatformGoods(){
        try{
            //插入系统日志
            $arr_sys_other_log = array(
                "type"        => "22",
                "operator_id" => get_operator_id(),
                "data"        => 1,
                "message"     => "重新匹配全部货品",
                "careted"     => date("Y-m-d G:i:s")
            );
            M("sys_other_log")->data($arr_sys_other_log)->add();

            //获取配置信息的值
            $sys_goods_match_concat_code = get_config_value("sys_goods_match_concat_code", 0);
            $goods_match_split_char = get_config_value("goods_match_split_char", "");
            $sql = "SET @cfg_goods_match_concat_code=\"{$sys_goods_match_concat_code}\"";
            $this->execute($sql);
            $sql = "SET @cfg_goods_match_split_char=\"{$goods_match_split_char}\"";
            $this->execute($sql);
            $sql1 = "SET @tmp_match_count=0;";

            //匹配平台货品与系统货品
            $sql2 = "UPDATE api_goods_spec gs INNER JOIN
              (SELECT gs.rec_id,FN_SPEC_NO_CONV(IF($sys_goods_match_concat_code=2,gs.goods_id,gs.outer_id),IF($sys_goods_match_concat_code>=2,gs.rec_id,gs.spec_outer_id)) merchant_no
              FROM api_goods_spec gs WHERE gs.is_deleted=0) tmp ON gs.rec_id=tmp.rec_id
              LEFT JOIN goods_merchant_no mn ON mn.merchant_no=tmp.merchant_no AND mn.merchant_no<>''
            SET gs.match_target_type=IFNULL(mn.type,0), gs.match_target_id=IFNULL(mn.target_id,0),
              gs.match_code=IFNULL(mn.merchant_no,''),
              gs.is_manual_match=IF(@tmp_match_count:=@tmp_match_count+IF(ISNULL(mn.target_id),0,1),0,0),
              gs.is_stock_changed=IF(gs.match_target_id,1,0),stock_change_count=stock_change_count+1;";

            //更新平台货品数据与匹配的系统货品对照-----单品
            $sql3 = "UPDATE api_goods_spec ag,goods_spec gs,goods_goods gg,goods_class gc
            SET ag.brand_id=gg.brand_id,ag.class_id_path=gc.path,gs.img_url=IF(gs.img_url='',ag.pic_url,gs.img_url)
            WHERE ag.is_deleted=0 AND ag.match_target_type=1 AND gs.spec_id=ag.match_target_id
            AND gg.goods_id=gs.goods_id AND gc.class_id=gg.class_id;";

            //更新平台货品数据与匹配的系统货品对照-----组合装
            $sql4 = "UPDATE api_goods_spec ag,goods_suite gs,goods_class gc
            SET ag.brand_id=gs.brand_id,ag.class_id_path=gc.path
            WHERE ag.is_deleted=0 AND ag.match_target_type=2 AND gs.suite_id=ag.match_target_id AND gc.class_id=gs.class_id;";

            //更新订单无效货品
            $sql5 = "UPDATE api_trade ax,api_trade_order ato,api_goods_spec ag SET ato.is_invalid_goods=0,ax.bad_reason=(bad_reason&~1)
            WHERE ato.is_invalid_goods=1 AND ato.platform_id = ag.platform_id AND ato.goods_id = ag.goods_id
            AND ato.spec_id = ag.spec_id AND ato.status <=30 AND ax.platform_id=ato.platform_id AND ax.tid=ato.tid;";

            $this->execute($sql1);
            $this->execute($sql2);
            $this->execute($sql3);
            $this->execute($sql4);
            $this->execute($sql5);
            //刷新库存同步规则
            $this->updateStockSyncRule("2");
            //插入操作日志
            $sys_other_log_db = M("sys_other_log");
            $user_id = get_operator_id();
            $sql_log = "INSERT INTO sys_other_log(`type`,operator_id,`data`,message)
                SELECT 14,$user_id,ag.rec_id,concat_ws('  ','自动匹配平台货品,平台货品ID为:',ag.goods_id,'规格ID为:',ag.spec_id,'匹配系统货品商家编码:',ag.match_code)
                FROM api_goods_spec ag
                WHERE ag.match_target_type > 0 ;";
            $sys_other_log_db->execute($sql_log);
            $res["status"] = 1;
            $res["info"] = "操作成功";
        } catch(\Exception $e){
            \Think\Log::write($e->getMessage());
            $res["status"] = 0;
            $res["info"] = self::PDO_ERROR;
        }
        return $res;
    }

    /**
     * @return mixed
     * 自动匹配未匹配
     *
     */
    public function autoMatchUnmatchPlatformGoods(){
        try{
            //插入系统日志
            $arr_sys_other_log = array(
                "type"        => "22",
                "operator_id" => get_operator_id(),
                "data"        => 1,
                "message"     => "匹配未匹配货品",
                "careted"     => date("Y-m-d G:i:s")
            );
            M("sys_other_log")->data($arr_sys_other_log)->add();

            //获取配置信息的值
            $sys_goods_match_concat_code = get_config_value("sys_goods_match_concat_code", 0);
            $goods_match_split_char = get_config_value("goods_match_split_char", "");

            $sql = "SET @cfg_goods_match_concat_code=\"{$sys_goods_match_concat_code}\"";
            $this->execute($sql);
            $sql = "SET @cfg_goods_match_split_char=\"{$goods_match_split_char}\"";
            $this->execute($sql);

            $sql1 = "SET @tmp_match_count=0;";

            //将平台货品匹配为系统货品
            $sql2 = "UPDATE api_goods_spec gs INNER JOIN
              (SELECT gs.rec_id,FN_SPEC_NO_CONV(IF($sys_goods_match_concat_code=2,gs.goods_id,gs.outer_id),IF($sys_goods_match_concat_code>=2,gs.rec_id,gs.spec_outer_id)) merchant_no
              FROM api_goods_spec gs WHERE gs.is_deleted=0 AND gs.match_target_type=0) tmp ON gs.rec_id=tmp.rec_id
              LEFT JOIN goods_merchant_no mn ON mn.merchant_no=tmp.merchant_no AND mn.merchant_no<>''
            SET gs.match_target_type=IFNULL(mn.type,0), gs.match_target_id=IFNULL(mn.target_id,0),
              gs.match_code=IFNULL(mn.merchant_no,''),
              gs.is_manual_match=IF(@tmp_match_count:=@tmp_match_count+IF(ISNULL(mn.target_id),0,1),0,0),
              gs.is_stock_changed=IF(gs.match_target_id,1,0),gs.modify_flag=gs.is_stock_changed,stock_change_count=stock_change_count+1;";

            //更新平台货品数据与匹配的货品对应-----单品
            $sql3 = "UPDATE api_goods_spec ag,goods_spec gs,goods_goods gg,goods_class gc
            SET ag.brand_id=gg.brand_id,ag.class_id_path=gc.path,gs.img_url=IF(gs.img_url='',ag.pic_url,gs.img_url)
            WHERE ag.is_deleted=0 AND ag.match_target_type=1 AND gs.spec_id=ag.match_target_id
            AND gg.goods_id=gs.goods_id AND gc.class_id=gg.class_id;";

            //更新平台货品数据与匹配的货品一一对应-----组合装
            $sql4 = "UPDATE api_goods_spec ag,goods_suite gs,goods_class gc
            SET ag.brand_id=gs.brand_id,ag.class_id_path=gc.path
            WHERE ag.is_deleted=0 AND ag.match_target_type=2 AND gs.suite_id=ag.match_target_id AND gc.class_id=gs.class_id;";

            //更新包含无效货品的订单
            $sql5 = "UPDATE api_trade ax,api_trade_order ato,api_goods_spec ag SET ato.is_invalid_goods=0,ax.bad_reason=(bad_reason&~1)
            WHERE ato.is_invalid_goods=1 AND ato.platform_id = ag.platform_id AND ato.goods_id = ag.goods_id
            AND ato.spec_id = ag.spec_id AND ato.status <=30 AND ax.platform_id=ato.platform_id AND ax.tid=ato.tid AND ag.modify_flag > 0 AND (ag.modify_flag&1);";

            $this->execute($sql1);
            $this->execute($sql2);
            $this->execute($sql3);
            $this->execute($sql4);
            $this->execute($sql5);
            //刷新库存同步规则
            $this->updateStockSyncRule("3");
            //插入操作日志
            $sys_other_log_db = M("sys_other_log");
            $user_id = get_operator_id();
            $sql_log = "INSERT INTO sys_other_log (`type`,operator_id,`data`,message)
                SELECT 14,$user_id,ag.rec_id,concat_ws(' ','自动匹配平台货品,平台货品ID为:',ag.goods_id,'规格ID为:',ag.spec_id,'匹配系统货品商家编码:',ag.match_code)
                FROM api_goods_spec ag
                WHERE ag.modify_flag > 0 AND (ag.modify_flag&1) AND ag.match_target_type > 0 ;";
            $sys_other_log_db->execute($sql_log);
            //更新modify_flag
            $sql = "UPDATE api_goods_spec SET modify_flag = (modify_flag&~1) WHERE modify_flag > 0;";
            $this->execute($sql);
            $res["status"] = 1;
            $res["info"] = "操作成功";
        } catch(\Exception $e){
            \Think\Log::write($e->getMessage());
            $res["status"] = 0;
            $res["info"] = self::PDO_ERROR;
        }
        return $res;
    }

    public function getCfgStockSyncRule($id){
        try{
            $id = addslashes($id);
            $sql = "SELECT ags.stock_syn_rule_id as rule_id,ags.stock_syn_rule_no as rule_no,ags.stock_syn_warehouses as warehouse_list,ags.stock_syn_percent
            ,ags.stock_syn_plus,ags.stock_syn_min,ags.is_disable_syn,replace(concat('实际库存',make_set(ags.stock_syn_mask,'+采购在途量','+待采购量','+调拨在途量','+采购到货量',
            '+采购换货在途量','+销售换货在途量','-预订单量','-待审核量','-未付款量','-待发货量','-采购退货量','-销售退货量','-采购换货量','-销售换货量','-锁定库存量','-待调拨量')),',','') stock_flag_string
            FROM api_goods_spec ags WHERE ags.rec_id=$id";
            $res = $this->query($sql);
            if($res[0]['rule_id'] == 0){
                $res[0]['stock_syn_info'] = '自定义同步规则';
            }elseif($res[0]['rule_id'] == -1){
                $res[0]['stock_syn_info'] = '无同步规则';
            }else{
                $res[0]['stock_syn_info'] = '默认同步规则';
            }
            $data = array("total" => count($res), "rows" => $res);
        } catch(\Exception $e){
            \Think\Log::write($e->getMessage());
            $data = array("total" => 0, "rows" => array());
        }
        return $data;
    }

    public function getSyncInfo($rec_id){
        try{
            $conditions = array(
                'ag.rec_id' => array('eq', $rec_id),
                'ag.is_disable_syn' => array('eq', 0),           //停止库存同步
                'ag.is_stock_changed' => array('eq', 1),           //最后一次库存同步后，库存有没发生变化
                'ag.disable_syn_until' => array('exp', '<NOW()'),   //后台信息同步库存，直接到达这个时间(unix timestamp)
                'ag.is_deleted' => array('eq', 0),           //是否已经删除,status的一个备份
                'ag.status' => array('neq', 0),          //0删除 1在架 2下架
                'sh.auth_state' => array('eq', 1)           //店铺授权状态
            );
            $res_sync_info = $this->alias('ag')->field("ag.*,sh.sub_platform_id,sh.account_id,sh.app_key")->join("left join cfg_shop sh on ag.shop_id = sh.shop_id and ag.platform_id = sh.platform_id")->where($conditions)->find();
            return $res_sync_info;
        } catch(\PDOException $e){
            $msg = $e->getMessage();
            \Think\Log::write($this->name . '-getSyncInfo-' . $msg);
            E("未知错误,请联系管理员");
        }
    }

    /* 更新api_goods_spec 信息
     *
     */
    public function updateApiGoodsSpec($data, $conditions){
        try{
            $res = $this->where($conditions)->save($data);
            return $res;
        } catch(\PDOException $e){
            $msg = $e->getMessage();
            \Think\Log::write($this->name . '-updateApiGoodsSpec-' . $msg);
            E('未知错误，请联系管理员');
        }

    }

    /* 获取api_goods_spec 记录  */
    public function getApiGoodsSpec($fields, $conditions){
        try{
            $res = $this->where($conditions)->field($fields)->select();
            return $res;
        } catch(\PDOException $e){
            $msg = $e->getMessage();
            \Think\Log::write($this->name . '-getApiGoodsSpec-' . $msg);
            E('未知错误，请联系管理员');
        }
    }

    /**
     * 刷新库存同步规则
     * author:luyanfeng
     * @param $type
     * @param $code
     */
    public function updateStockSyncRule($type, $code = ""){
        try{
            if($type == "1"){
                //根据id刷新库存同步规则
                $code = addslashes($code);
                $code = empty($code)?0:$code;
                $where = "WHERE ag.is_deleted=0 AND ag.match_target_type=2 AND ag.match_target_id=$code AND ag.stock_syn_rule_id<>0";
            } else if($type == "2"){
                //平台货品自动匹配
                $where = "WHERE ag.is_manual_match=0 AND ag.stock_syn_rule_id<>0";
            } else if($type == "3"){
                //平台货品自动匹配未匹配
                $where = "WHERE ag.modify_flag>0 AND ag.stock_syn_rule_id<>0";
            } else if($type == "4"){
                //根据单品id刷新库存同步规则  如果有单品库存同步策略不更新
                $code = addslashes($code);
                $code = empty($code)?0:$code;
                $where = "WHERE ag.rec_id=$code AND ag.stock_syn_rule_id<>0";
            } else if($type == "5"){
                //修改完库存同步策略之后刷新平台货品的库存同步策略
                //$where = "WHERE true";
                $where = "WHERE true";
                $spec_priority = get_config_value('spec_stocksyn_priority', 0);
                if($spec_priority == 1){
                    $where .= ' AND ag.stock_syn_rule_id<>0';
                }
            } else if($type == "6"){
                //根据单品id刷新库存同步规则  如果有单品库存同步策略不更新
                $code = addslashes($code);
                $where = "WHERE ag.rec_id=$code";
            }
            if($type == "2" || $type == "3" || $type == "5"){
                $sql = "UPDATE api_goods_spec gs,
                (SELECT * FROM  (
                    SELECT ag.rec_id,rule.rec_id rule_id,rule.priority,rule.rule_no,rule.warehouse_list,rule.stock_flag,rule.percent,rule.plus_value,rule.min_stock,rule.is_auto_listing,rule.is_auto_delisting,rule.is_disable_syn
                    FROM api_goods_spec ag
                    LEFT JOIN cfg_stock_sync_rule rule ON (rule.is_disabled=0 AND FIND_IN_SET(rule.class_id,ag.class_id_path) AND FIND_IN_SET(ag.shop_id, rule.shop_list)AND ag.brand_id=IF(rule.brand_id=-1,ag.`brand_id`,rule.`brand_id`))
                    $where ORDER BY rule.priority DESC) _ALIAS_ GROUP BY rec_id ) da
                SET
                    gs.stock_syn_rule_id=IFNULL(da.rule_id,-1),
                    gs.stock_syn_rule_no=IFNULL(da.rule_no,''),
                    gs.stock_syn_warehouses=IFNULL(da.warehouse_list,''),
                    gs.stock_syn_mask=IFNULL(da.stock_flag,0),
                    gs.stock_syn_percent=IFNULL(da.percent,100),
                    gs.stock_syn_plus=IFNULL(da.plus_value,0),
                    gs.stock_syn_min=IFNULL(da.min_stock,0),
                    gs.is_auto_listing=IFNULL(da.is_auto_listing,1),
                    gs.is_auto_delisting=IFNULL(da.is_auto_delisting,1),
                    gs.is_disable_syn=IFNULL(da.is_disable_syn,1),
                    gs.is_stock_changed=1
                WHERE gs.rec_id=da.rec_id;";
            } else if($type == "1" || $type == "4" || $type == '6'){
                $sql = "UPDATE api_goods_spec gs,
                (SELECT rule.rec_id rule_id,rule.priority,rule.rule_no,rule.warehouse_list,rule.stock_flag,rule.percent,rule.plus_value,rule.min_stock,rule.is_auto_listing,rule.is_auto_delisting,rule.is_disable_syn
                FROM api_goods_spec ag
                LEFT JOIN cfg_stock_sync_rule rule ON (rule.is_disabled=0 AND FIND_IN_SET(rule.class_id,ag.class_id_path) AND FIND_IN_SET(ag.shop_id, rule.shop_list)AND ag.brand_id=IF(rule.brand_id=-1,ag.`brand_id`,rule.`brand_id`))
                $where ORDER BY rule.priority DESC LIMIT 1) da
                SET
                    gs.stock_syn_rule_id=IFNULL(da.rule_id,-1),
                    gs.stock_syn_rule_no=IFNULL(da.rule_no,''),
                    gs.stock_syn_warehouses=IFNULL(da.warehouse_list,''),
                    gs.stock_syn_mask=IFNULL(da.stock_flag,0),
                    gs.stock_syn_percent=IFNULL(da.percent,100),
                    gs.stock_syn_plus=IFNULL(da.plus_value,0),
                    gs.stock_syn_min=IFNULL(da.min_stock,0),
                    gs.is_auto_listing=IFNULL(da.is_auto_listing,1),
                    gs.is_auto_delisting=IFNULL(da.is_auto_delisting,1),
                    gs.is_disable_syn=IFNULL(da.is_disable_syn,1),
                    gs.is_stock_changed=1
                WHERE gs.rec_id=$code;";
            }
            $this->execute($sql);
        } catch(\Exception $e){
            \Think\Log::write("updateStockSyncRule:" . $e->getMessage());
            E(self::PDO_ERROR);
        }
    }

    //将平台货品 导入到系统货品档案中
    public function importApiGoods($id_list, &$list){
        $goods_goods_db = D('Goods/goods_goods');
        $goods_spec_db = D('Goods/goods_spec');
        $goods_log_db = M('goods_log');
        $goods_barcode_db = D('Goods/goods_barcode');
        $goods_merchant_db = D('Goods/goods_merchant_no');
        $goods_goods_data = array();
        $goods_spec_data = array();
        $userId = get_operator_id();
        $regular_api_goods = array();
        $sql_error_info = '';
        try{
            //获取设置信息
            $sys_goods_match_concat_code = get_config_value("sys_goods_match_concat_code", 0);
            $goods_match_split_char = get_config_value("goods_match_split_char", "");
            $goods_spec_num = $goods_spec_db->getGoodsSpecCount();
            if($goods_spec_num > 0 && empty($id_list)){
                SE('不支持全部操作，请选中导入');
                return;
            }
            //取得货品商家编码不为空的数据
            $where = empty($id_list) ? '' : array('rec_id' => array('in', $id_list));
            $all_api_goods = $this->alias('ags')->field('ags.rec_id AS id,ags.shop_id,ags.goods_id,ags.spec_id,ags.spec_code,ags.goods_name,ags.spec_name,ags.outer_id,ags.spec_outer_id,ags.price,ags.pic_url,ags.barcode')->where($where)->select();
            foreach($all_api_goods as $k => $v){
                if($sys_goods_match_concat_code == 1  && $v['outer_id'] == '' && $v['spec_outer_id'] == ''){
                    $list[] = array('goods_name' => $v['goods_name'], 'spec_no' => $v['outer_id'], 'info' => '货品商家编码为空或规格商家编码为空，请填写');
                } elseif($sys_goods_match_concat_code == 0 && $v['spec_outer_id'] == '') {
                    $list[] = array('goods_name' => $v['goods_name'], 'spec_no' => $v['outer_id'], 'info' => '规格商家编码为空，请填写');
                } elseif($sys_goods_match_concat_code==3 && $v['outer_id']==''){
                    $list[] = array('goods_name' => $v['goods_name'], 'spec_no' => $v['outer_id'], 'info' => '货品商家编码为空，请填写');
                }else{
                    $regular_api_goods[] = $v;
                }
            }
            //取得规格编码相同的数据
            $repeat_arr = array();
            //$map = array();
            if($sys_goods_match_concat_code == 0){
                foreach($regular_api_goods as $k => $v){
                    if($v['spec_outer_id'] != ''){
                        if(in_array($v['spec_outer_id'], $repeat_arr)){
                            $list[] = array('spec_no' => $v['outer_id'], 'goods_name' => $v['goods_name'], 'info' => '该商家编码货品的规格编码对应多个单品');
                            unset($regular_api_goods[$k]);
                        } else{
                            $repeat_arr[] = $v['spec_outer_id'];
                        }
                        //$map[$v['spec_outer_id']][] = $v;
                    }
                }
            }

            sort($regular_api_goods);
            /*foreach($map as $k=>$v){
                $check_flag = true;
                if(count($map[$k])>1){
                    $check_flag = false;
                    foreach($v as $v1){
                        $repeat_arr[] = $v1;
                        $list[] = array('spec_no'=>$v1['outer_id'],'goods_name'=>$v1['goods_name'],'info'=>'该商家编码货品的规格编码对应多个单品');
                    }
                }
            }
            //取得能够导入到系统的平台货品数组
            foreach($regular_api_goods as $k1=>$v1){
                foreach($repeat_arr as $k2=>$v2){
                    if($v1==$v2){
                        unset($regular_api_goods[$k1]);
                    }
                }
            }*/

            $sql = "SET @cfg_goods_match_concat_code=\"{$sys_goods_match_concat_code}\"";
            $this->execute($sql);
            $sql = "SET @cfg_goods_match_split_char=\"{$goods_match_split_char}\"";
            $this->execute($sql);
            $arr_rec_id = array();
            if(!empty($regular_api_goods)){
                foreach($regular_api_goods as $v){
                    $arr_rec_id[] = $v['id'];
                }
                $arr_rec_id = join(',', $arr_rec_id);
                $merchant_no_arr = $this->alias('gs')->field("gs.rec_id,FN_SPEC_NO_CONV(IF($sys_goods_match_concat_code=2,gs.goods_id,gs.outer_id),IF($sys_goods_match_concat_code>=2,gs.rec_id,gs.spec_outer_id)) merchant_no")->where(array('gs.is_deleted' => 0, 'gs.rec_id' => array('in', $arr_rec_id)))->select();
                //$merchant_no_sql = "SELECT gs.rec_id,FN_SPEC_NO_CONV(gs.outer_id,gs.spec_outer_id) merchant_no FROM api_goods_spec gs WHERE gs.is_deleted=0 AND gs.rec_id in ($arr_rec_id)";
                //$merchant_no_arr = $this->query($merchant_no_sql);
                //插入货品表中
                foreach($regular_api_goods as $k => $v){
                    //$merchant_no_sql = "SELECT FN_SPEC_NO_CONV(gs.outer_id,gs.spec_outer_id) merchant_no FROM api_goods_spec gs WHERE gs.is_deleted=0 AND gs.rec_id=%d";
                    //$merchant_no_arr = $this->query($merchant_no_sql,$v['id']);
                    try{
                        if($sys_goods_match_concat_code==3 || $sys_goods_match_concat_code==1)
                        {
                            $goods_no_res = $v['outer_id'];
                        }else
                        {
                            $goods_no_res = $v['goods_id'];
                        }
                        $this->startTrans();
                        $goods_goods_data['goods_no'] = $goods_no_res;
                        $goods_goods_data['goods_name'] = $v['goods_name'];
                        $goods_goods_data['goods_type'] = 1;
                        $goods_goods_data['flag_id'] = 0;
                        $goods_goods_data['modified'] = array('exp', 'NOW()');
                        $goods_goods_data['created'] = array('exp', 'NOW()');

                        //兼容旧数据
                        $goods_goods_re_old = $goods_goods_db->alias('gg')->field('gg.goods_id,gg.goods_no')->where(array('gg.deleted' => 0, 'gg.goods_no' => array('eq', $v['outer_id'])))->select();
                        if(empty($goods_goods_re_old)){
                            //新匹配方式
                            $goods_goods_re = $goods_goods_db->alias('gg')->field('gg.goods_id,gg.goods_no')->where(array('gg.deleted' => 0, 'gg.goods_no' => array('eq', $goods_no_res)))->select();
                            $res_goods_id = !empty($goods_goods_re) ? $goods_goods_re[0]['goods_id'] : M('goods_goods')->add($goods_goods_data);
                        }else{
                            $res_goods_id = $goods_goods_re_old[0]['goods_id'];
                        }


                        if(empty($goods_goods_re) && $res_goods_id){
                            //导入货品记录日志
                            $arr_goods_log = array(
                                'goods_type' => 1,//1-货品 2-组合装
                                'goods_id' => $res_goods_id,
                                'spec_id' => 0,
                                'operator_id' => $userId,
                                'operate_type' => 11,
                                'message' => '从平台货品导入货品--' . $v['goods_name'],
                                'created' => array('exp', 'NOW()')
                            );
                            $sql_error_info = 'add_goods_goods_log';
                            $goods_log_db->add($arr_goods_log);
                        }
                        //$re_spec_no = $goods_spec_db->query("SELECT gs.spec_no FROM goods_spec gs WHERE gs.deleted=0 AND gs.spec_no='%s'", $merchant_no_arr[$k]['merchant_no']);
                        $re_spec_no = $goods_merchant_db->alias('gm')->field('gm.merchant_no')->where(array('gm.merchant_no' => array('eq', $merchant_no_arr[$k]['merchant_no'])))->select();
                        if($re_spec_no){
                            $list[] = array('spec_no' => $merchant_no_arr[$k]['merchant_no'], 'goods_name' => $v['goods_name'], 'info' => '该商家编码在货品档案或组合装中已经存在');
                            $this->rollback();
                            continue;
                        }
                        $goods_spec_data['goods_id'] = $res_goods_id;
                        $goods_spec_data['spec_no'] = $merchant_no_arr[$k]['merchant_no'];
                        $goods_spec_data['spec_name'] = $v['spec_name'];
                        $goods_spec_data['spec_code'] = $v['spec_outer_id'];
                        $goods_spec_data['retail_price'] = $v['price'];
                        $goods_spec_data['img_url'] = $v['pic_url'];
                        $goods_spec_data['barcode'] = $v['barcode'];
                        $goods_spec_data['is_allow_neg_stock'] = 0;//默认允许负库存出库为否
                        $goods_spec_data['flag_id'] = 9;
                        $goods_spec_data['modified'] = array('exp', 'NOW()');
                        $goods_spec_data['created'] = array('exp', 'NOW()');
                        $sql_error_info = 'importApiGoods';
                        $res_goods_spec_id = $goods_spec_db->add($goods_spec_data);
                        if($res_goods_spec_id){
                            $sql_error_info = 'update spec_count';
                            $this->execute("UPDATE goods_goods SET spec_count=(SELECT COUNT(spec_id) FROM goods_spec WHERE goods_id=%d AND deleted=0) WHERE goods_id=%d", array($res_goods_id, $res_goods_id));
                            if($v['barcode'] != ''){
                                $barcode['barcode'] = $v['barcode'];
                                $barcode["type"] = 1;
                                $barcode["target_id"] = $res_goods_spec_id;
                                $barcode["tag"] = get_seq("goods_barcode");
                                $barcode["is_master"] = 1;
                                $barcode["created"] = date("Y-m-d H:i:s", time());
                                $goods_barcode_db->add($barcode);
                            }
                            //初始化单品库存
                            D('Stock/StockSpec')->initStockSpec($res_goods_spec_id);
                        }
                        $arr_goods_spec_log = array(
                            'goods_type' => 1,//1-货品 2-组合装
                            'goods_id' => $res_goods_id,
                            'spec_id' => $res_goods_spec_id,
                            'operator_id' => $userId,
                            'operate_type' => 11,
                            'message' => '从平台货品导入--单品--' . $v['spec_name'],
                            'created' => array('exp', 'NOW()')
                        );
                        $sql_error_info = 'add_goods_spec_log';
                        $goods_log_db->add($arr_goods_spec_log);
                        $arr_goods_merchant_no = array(
                            'merchant_no' => $merchant_no_arr[$k]['merchant_no'],
                            'type' => 1,//1普通规格，2组合装
                            'target_id' => $res_goods_spec_id,
                            'modified' => array('exp', 'NOW()'),
                            'created' => array('exp', 'NOW()')
                        );
                        $sql_error_info = 'add_goods_merchant_no';
                        M('goods_merchant_no')->add($arr_goods_merchant_no);
                        //D('GoodsGoods')->matchApiGoodsSpecByMerchantNo($this, $merchant_no_arr[$k]['merchant_no']);  遍历去匹配效率低,在控制器中插入完数据批量匹配
                        $this->commit();
                    } catch(\PDOException $e){
                        $this->rollback();
                        \Think\Log::write($sql_error_info . '-importApiGoods-' . $e->getMessage());
                        $list[] = array('spec_no' => $merchant_no_arr[$k]['merchant_no'], 'goods_name' => $v['goods_name'], 'info' => '未知错误，请联系管理员');
                    } catch(\Exception $e){
                        $this->rollback();
                        \Think\Log::write($e->getMessage());
                        $list[] = array('spec_no' => $merchant_no_arr[$k]['merchant_no'], 'goods_name' => $v['goods_name'], 'info' => '未知错误,请联系管理员');
                    }
                }
            }
        } catch(\PDOException $e){
            $this->rollback();
            \Think\Log::write($sql_error_info . '-importApiGoods-' . $e->getMessage());
            SE(parent::PDO_ERROR);
        } catch(BusinessLogicException $e){
            $this->rollback();
            SE($e->getMessage());
        } catch(\Exception $e){
            $this->rollback();
            \Think\Log::write($e->getMessage());
            SE(parent::PDO_ERROR);
        }
    }

    public function exportToExcel($id_list, $search, $type = 'excel'){
        $creator = session('account');
        $excel_no = array();
        try{
            if(empty($id_list)){
                $where = "true ";
                D('Goods/PlatformGoods')->searchFormDeal($where, $search);
                $sql_res = "SELECT ag.rec_id FROM api_goods_spec ag LEFT JOIN goods_merchant_no gmn ON gmn.type=ag.match_target_type AND gmn.target_id=ag.match_target_id WHERE $where ";
                $rows = $this->query($sql_res);
                if(empty($rows)){
                    SE('导出不能为空，请检查搜索条件');
                }
                for($i = 0; $i < count($rows); $i++){
                    $id_list[$i] = $rows[$i]['rec_id'];
                }
                $where = array('rec_id' => array('in', $id_list));
            } else{
                $where = array('rec_id' => array('in', $id_list));
            }
            $row = array();
            //获取设置信息
            $sys_goods_match_concat_code = get_config_value("sys_goods_match_concat_code", 0);
            $goods_match_split_char = get_config_value("goods_match_split_char", "");
            $sql = "SET @cfg_goods_match_concat_code=\"{$sys_goods_match_concat_code}\"";
            $this->execute($sql);
            $sql = "SET @cfg_goods_match_split_char=\"{$goods_match_split_char}\"";
            $this->execute($sql);
            //得到根据设置生成的商家编码数组
            $res_rec_id = $this->field('rec_id')->where($where)->select();
            $arr_rec_id = array();
            foreach($res_rec_id as $k => $v){
                $arr_rec_id[] = $v['rec_id'];
            }
            $merchant_no_arr = $this->alias('gs')->field("gs.rec_id,FN_SPEC_NO_CONV(IF($sys_goods_match_concat_code=2,gs.goods_id,gs.outer_id),IF($sys_goods_match_concat_code>=2,gs.rec_id,gs.spec_outer_id)) merchant_no")->where(array('gs.rec_id' => array('in', $arr_rec_id)))->select();
            $platform_goods = $this->alias('ags')->field('ags.rec_id,ags.goods_id,ags.goods_name,ags.spec_name,ags.outer_id,ags.spec_outer_id,ags.price,ags.pic_url,ags.barcode,cs.shop_name,ags.stock_num,ags.status')
                ->where($where)->join('left join cfg_shop cs on cs.shop_id=ags.shop_id')->select();
            $num = workTimeExportNum($type);
            if(count($platform_goods) > $num){
                if($type == 'csv'){
                    SE(self::EXPORT_CSV_ERROR);
                }
                SE(self::OVER_EXPORT_ERROR);
            }
            foreach($platform_goods as $k => $v){
                if($sys_goods_match_concat_code==3 && $v['outer_id']=='')
                {
                    $row['merchant_no'] = '';
                }else
                {
                    $row['merchant_no'] = $merchant_no_arr[$k]['merchant_no'];
                }
                $row['goods_id'] = $v['goods_id'];
                $row['goods_name'] = $v['goods_name'];
                $row['short_name'] = '';
                $row['alias'] = '';
                $row['class_name'] = '';
                $row['brand_name'] = '';
                $row['goods_type'] = '销售商品';
                $row['is_hotcake'] = '';
                $row['unit_name'] = '个';
                $row['flag'] = '';
                $row['origin'] = '';
                $row['pic_url'] = $v['pic_url'];
                $row['spec_name'] = $v['spec_name'];
                $row['spec_code'] = $v['spec_outer_id'];
                $row['barcode'] = $v['barcode'];
                $row['lowest_price'] = 0;
                $row['retail_price'] = $v['price'];
                $row['market_price'] = 0;
                $row['validity_days'] = '';
                $row['length'] = 0;
                $row['width'] = 0;
                $row['height'] = 0;
                $row['weight'] = 0;
                $row['remark'] = '';
                $row['prop1'] = '';
                $row['prop2'] = '';
                $row['prop3'] = '';
                $row['prop4'] = '';
                $row['is_allow_neg_stock'] = '';
                $row['large_type'] = '';
                $row['stock_num'] = $v['stock_num'];
                switch($v['status']){
                    case '0':
                        $row['status'] = '删除';
                        break;
                    case '1':
                        $row['status'] = '在架';
                        break;
                    case '2':
                        $row['status'] = '下架';
                        break;
                    default:
                        $row['status'] = '未知';

                }
                $data[] = $row;
            }
            foreach($data as $k => $v){
                $keys_arr = array_keys($v);
            }
            foreach($keys_arr as $k => $v){
                switch($v){
                    case'merchant_no':
                        $excel_no['merchant_no'] = '商家编码';
                        break;
                    case'goods_id':
                        $excel_no['goods_id'] = '货品编号 ';
                        break;
                    case 'goods_name':
                        $excel_no['goods_name'] = '货品名称';
                        break;
                    case 'short_name':
                        $excel_no['short_name'] = '货品简称';
                        break;
                    case 'alias':
                        $excel_no['alias'] = '货品别名';
                        break;
                    case 'class_name':
                        $excel_no['class_name'] = '分类';
                        break;
                    case 'brand_name':
                        $excel_no['brand_name'] = '品牌';
                        break;
                    case 'stock_num':
                        $excel_no['stock_num'] = '平台库存';
                        break;
                    case 'goods_type':
                        $excel_no['goods_type'] = '货品类别 ';
                        break;
                    case 'unit_name':
                        $excel_no['unit_name'] = '单位';
                        break;
                    case 'flag':
                        $excel_no['flag'] = '标记';
                        break;
                    case 'origin':
                        $excel_no['origin'] = '产地';
                        break;
                    case 'pic_url':
                        $excel_no['pic_url'] = '图片链接';
                        break;
                    case 'spec_name':
                        $excel_no['spec_name'] = '规格名称';
                        break;
                    case 'spec_code':
                        $excel_no['spec_code'] = '规格码';
                        break;
                    case 'barcode':
                        $excel_no['barcode'] = '条码';
                        break;
                    case 'lowest_price':
                        $excel_no['lowest_price'] = '最低售价';
                        break;
                    case 'retail_price':
                        $excel_no['retail_price'] = '零售价';
                        break;
                    case 'market_price':
                        $excel_no['market_price'] = '市场价';
                        break;
                    case 'validity_days':
                        $excel_no['validity_days'] = '有效期';
                        break;
                    case 'length':
                        $excel_no['length'] = '长';
                        break;
                    case 'width':
                        $excel_no['width'] = '宽';
                        break;
                    case 'height':
                        $excel_no['height'] = '高';
                        break;
                    case 'weight':
                        $excel_no['weight'] = '重量';
                        break;
                    case 'remark':
                        $excel_no['remark'] = '单品备注';
                        break;
                    case 'prop1':
                        $excel_no['prop1'] = '自定义1';
                        break;
                    case 'prop2':
                        $excel_no['prop2'] = '自定义2';
                        break;
                    case 'prop3':
                        $excel_no['prop3'] = '自定义3';
                        break;
                    case 'prop4':
                        $excel_no['prop4'] = '自定义4';
                        break;
                    case 'is_allow_neg_stock':
                        $excel_no['is_allow_neg_stock'] = '允许负库存出库';
                        break;
                    case 'large_type':
                        $excel_no['large_type'] = '大件类别';
                        break;
                    case 'status':
                        $excel_no['status'] = '状态';
                        break;
                    case 'is_hotcake':
                        $excel_no['is_hotcake'] = '是否爆款';
                        break;
                }
            }
            $title = '平台货品';
            $filename = '平台货品';
            $width_list = array('20', '10', '17', '10', '10', '10', '10', '10','10', '10', '10', '10', '10', '10', '10', '10', '10', '10', '10', '10', '10', '15',
                '10', '10', '10','15','15','15','15', '17', '10', '10', '10');
            if($type == 'csv') {
                ExcelTool::Arr2Csv($data, $excel_no, $filename);
            }else{
                ExcelTool::Arr2Excel($data, $title, $excel_no, $width_list, $filename, $creator);
            }
        } catch(\PDOException $e){
            \Think\Log::write($e->getMessage());
            SE(parent::PDO_ERROR);
        } catch(BusinessLogicException $e){
            SE($e->getMessage());
        } catch(\Exception $e){
            \Think\Log::write($e->getMessage());
            SE(parent::PDO_ERROR);
        }
    }

    public function searchFormDeal(&$where, $search){
        //设置店铺权限
        D('Setting/EmployeeRights')->setSearchRights($search, 'shop_id', 1);
        foreach($search as $k => $v){
            if($v === "") continue;
            switch($k){
                case "shop_id":
                    //set_search_form_value($where, $k, $v, 'ag', 2, "AND");
                    $res = UtilTool::check_search_form_value($v, 2);
                    $where = $res === false ? $where : $where . " AND ag." . $k . " in(" . $res . ")";
                    break;
                case "goods_name":
                    //set_search_form_value($where, $k, $v, 'ag', 6, "AND");
                  //  $res = UtilTool::check_search_form_value($v, 1);

                    $res = trim($v);
                    if(empty($v) && ($v !== 0) && ($v !== '0')){
                        return false;
                    }
                    $res=strtr($res,array('%'=>'\%', '_'=>'\_', '\\'=>'\\\\'));
                    $res = addslashes($res);
                    $where = $res === false ? $where : $where . " AND ag." . $k . " LIKE '%" . $res . "%'";
                    break;
                case "outer_id":
                    //set_search_form_value($where, $k, $v, 'ag', 1, "AND");
                    $res = UtilTool::check_search_form_value($v, 1);
                    $where = $res === false ? $where : $where . " AND ag." . $k . "='" . $res . "'";
                    break;
                case "spec_name":
                    //set_search_form_value($where, $k, $v, 'ag', 6, "AND");
                    $res = UtilTool::check_search_form_value($v, 1);
                    $where = $res === false ? $where : $where . " AND ag." . $k . " LIKE '" . $res . "%'";
                    break;
                case "goods_id":
                    //set_search_form_value($where, $k, $v, 'ag', 1, "AND");
                    $res = UtilTool::check_search_form_value($v, 1);
                    $where = $res === false ? $where : $where . " AND ag." . $k . "='" . $res . "'";
                    break;
                case "spec_id":
                    //set_search_form_value($where, $k, $v, 'ag', 1, "AND");
                    $res = UtilTool::check_search_form_value($v, 1);
                    $where = $res === false ? $where : $where . " AND ag." . $k . "='" . $res . "'";
                    break;
                case "flag_id":
                    //set_search_form_value($where, $k, $v, 'ag', 2, "AND");
                    $res = UtilTool::check_search_form_value($v, 2);
                    $where = $res === false ? $where : $where . " AND ag." . $k . "=" . $res;
                    break;
                case "spec_outer_id":
                    //set_search_form_value($where, $k, $v, 'ag', 1, "AND");
                    $res = UtilTool::check_search_form_value($v, 1);
                    $where = $res === false ? $where : $where . " AND ag." . $k . "='" . $res . "'";
                    break;
                case "merchant_no":
                    //set_search_form_value($where, $k, $v, "gmn", 1, "AND");
                    $res = UtilTool::check_search_form_value($v, 1);
                    $where = $res === false ? $where : $where . " AND gmn." . $k . "='" . $res . "'";
                    break;
                case "status":
                    $res = UtilTool::check_search_form_value($v, 2);
                    $where = $res === false ? $where : $where . " AND ag." . $k . "=" . $res;
                    break;
                case "is_match":
                    $res = UtilTool::check_search_form_value($v, 2);
                    if($res == 0){
                        $where = $res === false ? $where : $where . " AND ag.match_target_id" . " = " . $res;
                    } elseif($res == 1){
                        $where = $res === false ? $where : $where . " AND ag.match_target_id != 0";
                    }
                    break;
                case "is_name_changed":
                    $res = UtilTool::check_search_form_value($v, 2);
                    $where = $res === false ? $where : $where . " AND ag." . $k . "=" . $res;
                    break;
                default:
                    continue;
            }
        }
    }

    public function loadSelectedData($id){
        $id = intval($id);
        try{
            $result = $this->alias('ags')->where('ags.rec_id = ' . $id)->field('ags.rec_id AS id,ags.stock_syn_rule_id,ags.stock_syn_rule_no as rule_no,ags.stock_syn_warehouses as warehouse_list,ags.stock_syn_mask as stock_flag,ags.stock_syn_percent,ags.stock_syn_plus,ags.stock_syn_min,ags.is_auto_listing,ags.is_auto_delisting,ags.is_disable_syn,ags.created,ags.modified')->select();
        } catch(\Exception $e){
            \Think\Log::write($e->getMessage());
            return false;
        }
        $data = $result[0];
        return ($data);
    }

    public function saveData($arr){
        if(!isset($arr['is_disabled']) || $arr['is_disabled'] != '1'){
            $arr['is_disabled'] = 0;
        }
        if(!isset($arr['is_disable_syn']) || $arr['is_disable_syn'] != '1'){
            $arr['is_disable_syn'] = 0;
        }
        if(!isset($arr['is_auto_listing']) || $arr['is_auto_listing'] != '1'){
            $arr['is_auto_listing'] = 0;
        }
        if(!isset($arr['is_auto_delisting']) || $arr['is_auto_delisting'] != '1'){
            $arr['is_auto_delisting'] = 0;
        }
        if(!isset($arr['purchase_num']) || $arr['purchase_num'] != '1'){
            $arr['purchase_num'] = 0;
        }
        if(!isset($arr['to_purchase_num']) || $arr['to_purchase_num'] != '1'){
            $arr['to_purchase_num'] = 0;
        }
        if(!isset($arr['transfer_num']) || $arr['transfer_num'] != '1'){
            $arr['transfer_num'] = 0;
        }
        if(!isset($arr['purchase_arrive_num']) || $arr['purchase_arrive_num'] != '1'){
            $arr['purchase_arrive_num'] = 0;
        }

        $arr['return_onway_num'] = 0;
        $arr['refund_onway_num'] = 0;

        if(!isset($arr['subscribe_num']) || $arr['subscribe_num'] != '1'){
            $arr['subscribe_num'] = 0;
        }
        if(!isset($arr['order_num']) || $arr['order_num'] != '1'){
            $arr['order_num'] = 0;
        }
        if(!isset($arr['unpay_num']) || $arr['unpay_num'] != '1'){
            $arr['unpay_num'] = 0;
        }
        if(!isset($arr['sending_num']) || $arr['sending_num'] != '1'){
            $arr['sending_num'] = 0;
        }

        $arr['return_num'] = 0;
        $arr['refund_num'] = 0;
        $arr['return_exch_num'] = 0;
        $arr['refund_exch_num'] = 0;

        if(!isset($arr['lock_num']) || $arr['lock_num'] != '1'){
            $arr['lock_num'] = 0;
        }
        if(!isset($arr['to_transfer_num']) || $arr['to_transfer_num'] != '1'){
            $arr['to_transfer_num'] = 0;
        }

        $arr['stock_flag'] = $arr['to_transfer_num'] . $arr['lock_num'] . $arr['refund_exch_num'] . $arr['return_exch_num'] . $arr['refund_num'] . $arr['return_num'] . $arr['sending_num'] . $arr['unpay_num'] . $arr['order_num'] . $arr['subscribe_num'] . $arr['refund_onway_num'] . $arr['return_onway_num'] . $arr['purchase_arrive_num'] . $arr['transfer_num'] . $arr['to_purchase_num'] . $arr['purchase_num'];

        $arr['stock_flag'] = bindec($arr['stock_flag']);

        $arr['created'] = date('Y-m-d H:i:s');
        $id = intval($arr['ags_id']);
        if(!$id){
            \Think\Log::write('add custom stock_syn_rule ERR: UNKNOW id');
            SE(self::PDO_ERROR);
        }
        if(substr($arr['warehouse_list'],0,1) == ',')
        {
            $arr['warehouse_list'] = substr($arr['warehouse_list'],1);
        }
        $is_custom = $arr['is_custom'];
        try{
            if($is_custom == 1 ){
                $api_goods_spec_arr = array(
                    'stock_syn_rule_id' => 0, //单品库存同步策略为 0
                    'stock_syn_rule_no' => '',
                    'stock_syn_warehouses' => $arr['warehouse_list'],
                    'stock_syn_mask' => $arr['stock_flag'],
                    'stock_syn_percent' => $arr['percent'],
                    'stock_syn_plus' => $arr['plus_value'],
                    'stock_syn_min' => $arr['min_stock'],
                    'is_auto_listing' => $arr['is_auto_listing'],
                    'is_auto_delisting' => $arr['is_auto_delisting'],
                    'is_disable_syn' => $arr['is_disable_syn'],
                    'modified' => date('Y-m-d H:i:s',time())
                );
                $this->fetchSql(false)->where(array('rec_id'=>$id))->save($api_goods_spec_arr);
            }else {
                $this->updateStockSyncRule(6,$id);
            }

        } catch(\PDOException $e){
            \Think\Log::write($e->getMessage());
            SE(self::PDO_ERROR);
        }catch(\Exception $e){
            \Think\Log::write($e->getMessage());
            SE(self::PDO_ERROR);
        }
    }

    public function addShortcutStrategy($data)
    {
        try
        {
            if(substr($data['data']['warehouse_list'],0,1) == ',')
            {
                $data['data']['warehouse_list'] = substr($data['data']['warehouse_list'],1);
            }
            /*$warehouse_id_list = M('cfg_warehouse')->field('warehouse_id')->where(array('name'=>array('in',$data['data']['warehouse_list'])))->fetchSql(false)->select();
            $warehouse_id = array();
            foreach($warehouse_id_list as $v){
                    $warehouse_id[] = $v['warehouse_id'];
            }
            $warehouse_id = join(',',$warehouse_id);
            $data['data']['warehouse_list'] = $warehouse_id;*/
            $params = array();
            $params['user_id'] = 0;//员工id  0表示全局配置
            $params['type'] = 6; //自定义库存同步策略
            $params['code'] = $data['shortcut_strategy_name'];
            $params['data'] = json_encode($data['data']);
            $params['created'] = date('Y-m-d H:i:s',time());
            if($data['shortcut_strategy_name']=='无'){
                SE('不能使用内置名称,请重新命名');
            }
            $repet_res = M('cfg_user_data')->where(array('user_id'=>0,'type'=>6,'code'=>$data['shortcut_strategy_name']))->find();
            if(!empty($repet_res)){
                SE('名称已存在！');
            }
            $return_id = M('cfg_user_data')->data($params)->fetchSql(false)->add();
        }catch (\PDOException $e)
        {
            \Think\Log::write($e->getMessage());
            SE(self::PDO_ERROR);
        }catch(BusinessLogicException $e)
        {
            SE($e->getMessage());
        }catch(\Exception $e)
        {
            \Think\Log::write($e->getMessage());
            SE(self::PDO_ERROR);
        }
        return $return_id;
    }

    public function removeShortcutStrategy($id)
    {
        $id = intval($id);
        if($id == 0){
            SE('无法删除该策略');
        }
        try{
            M('cfg_user_data')->where(array('rec_id'=>$id))->fetchSql(false)->delete();
        } catch(\PDOException $e)
        {
            \Think\Log::write($e->getMessage());
            SE(self::PDO_ERROR);
        }catch(BusinessLogicException $e)
        {
            SE($e->getMessage());
        }catch(\Exception $e){
            \Think\Log::write($e->getMessage());
            SE(self::PDO_ERROR);
        }
    }

    public function getShortCutStrategyList($id){
        $res = array();
        try
        {
            if(isset($id) && $id!='无'){
                $res = M('cfg_user_data')->where(array('rec_id'=>$id))->find();
                if(!empty($res)){
                    $data = json_decode($res['data']);
                    $warehouse_list = $data->warehouse_list;
                    $warehouse_name = M('cfg_warehouse')->field('warehouse_id,name')->where(array('warehouse_id'=>array('in',$warehouse_list)) )->fetchSql(false)->select();
                    $name_list = array();
                    foreach($warehouse_name as $k=>$v){
                        $name_list['id'][] = $v['warehouse_id'];
                        $name_list['name'][] = $v['name'];
                    }
                    $res['warehouse_id'] = join(',',$name_list['id']);
                    $res['warehouse_name'] = join(',',$name_list['name']);
                }
            }else{
                $res = M('cfg_user_data')->where(array('user_id'=>0,'type'=>6))->select();
            }
        }catch (\PDOException $e)
        {
            \Think\Log::write($e->getMessage());
            SE(self::PDO_ERROR);
        }catch(\Exception $e)
        {
            \Think\Log::write($e->getMessage());
            SE(self::PDO_ERROR);
        }
        return $res;
    }

    public function updateSpecNameGoodsName($rec_id_arr){

        $userId = get_operator_id();

        $goods_log_db = M('goods_log');

        try{
            //需要获取的 平台单品表中 字段
            $fields = 'rec_id,goods_name,spec_name,match_target_type,match_target_id';

            //获取平台单品信息条件
            if(is_array($rec_id_arr) && empty($rec_id_arr)){
                $conditions = [
                    'is_deleted' => ['eq', 0],
                    'match_target_type' => ['neq', 0]
                ];

            } elseif(is_array($rec_id_arr) && !empty($rec_id_arr)){
                $conditions = [
                    'is_deleted' => ['eq', 0],
                    'match_target_type' => ['neq', 0],
                    'rec_id' => ['in', $rec_id_arr]
                ];
            } else{
                throw new Exception("Value type must be array");
            }


            $platformGoods_list = D("Goods/PlatformGoods")->getApiGoodsSpec($fields, $conditions);

            $goods_spec_list = [];

            foreach($platformGoods_list as $k => $v){

                if(intval($v['match_target_type']) == 1){
                    //单品

                    if(empty($goods_spec_list[$v['match_target_id']])){
                        $goods_spec_list[$v['match_target_id']]['num'] = 1;
                        $goods_spec_list[$v['match_target_id']]['data'][] = $v;
                    } else{
                        $goods_spec_list[$v['match_target_id']]['num'] += 1;
                        $goods_spec_list[$v['match_target_id']]['data'][] = $v;
                    }

                } elseif(intval($v['match_target_type']) == 2){
                    //组合装
                }
            }

            //更新失败的商家编码
            $fail_spec_no_list = [];
            //更新成功的商家编码
            $success_spec_no_list = [];
            //忽略更新的商家编码
            $ignore_spec_no_list = [];

            foreach($goods_spec_list as $spec_id => $item){


                //获取系统单品条件
                $where_goods_spec = [
                    'match_target_type' => 1,
                    'spec_id' => $spec_id,
                    'deleted' => 0
                ];

                //单品更新结果
                $res_save_spec = '';
                //货品更新结果
                $res_save_goods = '';

                //获取单个系统单品信息
                $res_spec = D("Goods/GoodsSpec")->field('spec_id,spec_name,goods_id,spec_no')->where($where_goods_spec)->find();
                if(!empty($res_spec)){

                    //判断系统单品对应平台单品个数
                    if(intval($item['num']) == 1 && $res_spec['spec_name'] != $item['data'][0]['spec_name']){
                        $spec_where = ['spec_id' => $item['data'][0]['match_target_id'], 'deleted' => 0,];
                        $spec_data = ['spec_name' => $item['data'][0]['spec_name']];
                        $res_save_spec = D("Goods/GoodsSpec")->where($spec_where)->save($spec_data);

                        if($res_save_spec===1){
                            $arr_goods_spec_log = array(
                                'goods_type' => 1,//1-货品 2-组合装
                                'goods_id' => $res_spec['goods_id'],
                                'spec_id' => $res_spec['spec_id'],
                                'operator_id' => $userId,
                                'operate_type' => 52,
                                'message' => '从平台货品更新--系统单品名称--' . $item['data'][0]['spec_name'],
                                'created' => array('exp', 'NOW()')
                            );

                            $goods_log_db->add($arr_goods_spec_log);
                        }

                    } elseif(intval($item['num']) >= 2){

                        $res_save_spec = 'multi';
                        unset($res_spec['goods_id']);

                    }

                }

                //是否有对应系统货品
                if(!empty($res_spec['goods_id'])){
                    //系统货品条件
                    $where_goods_goods = ['goods_id' => $res_spec['goods_id'], 'deleted' => 0];
                    //获取系统货品信息
                    $res_goods = D("Goods/GoodsGoods")->field('goods_id,goods_name')->where($where_goods_goods)->find();
                    //判断系统货品名与 当前平台单品对应的货品名 是否一致

                    if(intval($item['num']) == 1 && $res_goods['goods_name'] != $item['data'][0]['goods_name']){
                        $goods_where = ['goods_id' => $res_spec['goods_id'], 'deleted' => 0];
                        $goods_data = ['goods_name' => $item['data'][0]['goods_name']];
                        $res_save_goods = D("Goods/GoodsGoods")->where($goods_where)->save($goods_data);

                        if($res_save_goods===1){
                            $arr_goods_spec_log = array(
                                'goods_type' => 1,//1-货品 2-组合装
                                'goods_id' => $res_spec['goods_id'],
                                'spec_id' => 0,
                                'operator_id' => $userId,
                                'operate_type' => 51,
                                'message' => '从平台货品更新--系统货品名称--' . $item['data'][0]['goods_name'],
                                'created' => array('exp', 'NOW()')
                            );

                            $goods_log_db->add($arr_goods_spec_log);
                        }



                    }


                }
                //对于不存在对应系统单品的不予提示
                if(!empty($res_spec['spec_no'])){

                    //记录更新成功、失败和忽略更新的商家编码
                    if($res_save_goods === false && $res_save_spec === false){
                        $fail_spec_no_list[] = $res_spec['spec_no'];
                    } elseif($res_save_goods === 1 || $res_save_spec === 1){
                        $success_spec_no_list[] = $res_spec['spec_no'];
                    } elseif($res_save_spec == 'multi'){
                        $ignore_spec_no_list[] = $res_spec['spec_no'];
                    }
                }
            }


            $result = [
                'success_spec_no_list' => $success_spec_no_list,
                'fail_spec_no_list' => $fail_spec_no_list,
                'ignore_spec_no_list' => $ignore_spec_no_list,
            ];


            return $result;


        } catch(\Exception $e){
            Log::write($e->getMessage());
            SE('未知错误，请联系管理员');
        }

    }

    //获取api_goods_spec 表中现有的rec_id最大值
    public function getRecId($where,$type='muliti')
    {
        try
        {
            if($type == 'single')
            {
                $rec_id = $this->field('rec_id')->where($where)->order('rec_id desc')->fetchSql(false)->find();
            }else{
                $rec_id = $this->field('rec_id')->where($where)->order('rec_id desc')->fetchSql(false)->select();
            }

        }catch (\PDOException $e)
        {
            \Think\Log::write($e->getMessage());
            $rec_id = '';
            SE(self::PDO_ERROR);
        }catch(\Exception $e)
        {
            \Think\Log::write($e->getMessage());
            $rec_id = '';
            SE(self::PDO_ERROR);
        }
        return $rec_id;
    }


}