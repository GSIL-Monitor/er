<?php
namespace Setting\Model;

use Think\Model;
use Common\Common\VerifyParams;

/**
 * Created by PhpStorm.
 * User: gaosong
 * Date: 10/27/15
 * Time: 17:22
 */
class CfgStockSyncRuleModel extends Model {
    protected $tableName = 'cfg_stock_sync_rule';
    protected $pk        = 'rec_id';

    public function searchStockSyncStrategy($page = 1, $rows = 20, $search = array(), $sort = 'id', $order = 'desc') {
        $where_limit = '';
        foreach ($search as $k => $v) {
            if (!isset($v)) continue;
            switch ($k) {
                case 'rule_no'://规则编号        cfg_stock_sync_rule
                    set_search_form_value($where_limit, $k, $v, 'cssr', 1, 'AND');
                    break;
                case 'rule_name'://规则名称
                    set_search_form_value($where_limit, $k, $v, 'cssr', 1, 'AND');
                    break;
            }
        }
        $where_limit = ltrim($where_limit, ' AND ');
        $limit       = ($page - 1) * $rows . "," . $rows;//分页
        $order       = $sort . ' ' . $order;//排序
        try {
            $total_sql = $this->fetchSql(false)->alias('cssr')->field('cssr.rec_id AS id')->where($where_limit)->select();
            $total     = count($total_sql);
            $list      = $this->fetchSql(false)->alias('cssr')->field('rec_id AS id,rule_no,rule_name,priority,shop_list,warehouse_list,class_id,brand_id,replace(concat(\'实际库存\',make_set(stock_flag,\'+采购在途量\',\'+待采购量\',\'+调拨在途量\',\'+采购到货量\',\'+采购换货在途量\',\'+销售换货在途量\',\'-预订单量\',\'-待审核量\',\'-未付款量\',\'-待发货量\',\'-采购退货量\',\'-销售退货量\',\'-采购换货量\',\'-销售换货量\',\'-锁定库存量\',\'-待调拨量\')),\',\',\'\') stock_flag_string,percent,plus_value,min_stock,is_auto_listing,is_auto_delisting,is_disabled,created,modified')->where($where_limit)->limit($limit)->order($order)->select();
        } catch (\Exception $e) {
            \Think\Log::write($e->getMessage());
            return ("1");
        }
        $data = array('total' => $total, 'rows' => $list);
        return ($data);
    }

    public function delData($id) {
        $id             = (int)$id;
        $data['status'] = 1;
        $data['info']   = '';
        try {
            $this->where('rec_id=' . $id)->delete();
        } catch (\Exception $e) {
            \Think\Log::write($e->getMessage());
            $data['status'] = 0;
            $data['info']   = '系统错误请联系管理员！';
        }
        return ($data);
    }

    public function loadSelectedData($id) {
        $id = intval($id);
        try {
            $result = $this->where('rec_id = ' . $id)->field('rec_id AS id,rule_no,rule_name,priority,shop_list,warehouse_list,class_id,brand_id,stock_flag,percent,plus_value,min_stock,is_auto_listing,is_auto_delisting,is_disabled,is_disable_syn,created,modified')->select();
        } catch (\Exception $e) {
            \Think\Log::write($e->getMessage());
            return 0;
        }
        $data = $result[0];
        return ($data);
    }

    public function saveData($arr) {
        $data['status'] = 1;
        $data['info']   = 'info';
        if (!VerifyParams::regexVerify("integ", $arr["min_stock"])) {
            $data["status"] = 0;
            $data["info"]   = "最小库存不能小于0";
            return $data;
        }
        if (!isset($arr['is_disabled']) || $arr['is_disabled'] != '1') {
            $arr['is_disabled'] = 0;
        }
        if (!isset($arr['is_disable_syn']) || $arr['is_disable_syn'] != '1') {
            $arr['is_disable_syn'] = 0;
        }
        if (!isset($arr['is_auto_listing']) || $arr['is_auto_listing'] != '1') {
            $arr['is_auto_listing'] = 0;
        }
        if (!isset($arr['is_auto_delisting']) || $arr['is_auto_delisting'] != '1') {
            $arr['is_auto_delisting'] = 0;
        }

        if (!isset($arr['purchase_num']) || $arr['purchase_num'] != '1') {
            $arr['purchase_num'] = 0;
        }
        if (!isset($arr['to_purchase_num']) || $arr['to_purchase_num'] != '1') {
            $arr['to_purchase_num'] = 0;
        }
        if (!isset($arr['transfer_num']) || $arr['transfer_num'] != '1') {
            $arr['transfer_num'] = 0;
        }
        if (!isset($arr['purchase_arrive_num']) || $arr['purchase_arrive_num'] != '1') {
            $arr['purchase_arrive_num'] = 0;
        }

        $arr['return_onway_num'] = 0;
        $arr['refund_onway_num'] = 0;

        if (!isset($arr['subscribe_num']) || $arr['subscribe_num'] != '1') {
            $arr['subscribe_num'] = 0;
        }
        if (!isset($arr['order_num']) || $arr['order_num'] != '1') {
            $arr['order_num'] = 0;
        }
        if (!isset($arr['unpay_num']) || $arr['unpay_num'] != '1') {
            $arr['unpay_num'] = 0;
        }
        if (!isset($arr['sending_num']) || $arr['sending_num'] != '1') {
            $arr['sending_num'] = 0;
        }

        $arr['return_num']      = 0;
        $arr['refund_num']      = 0;
        $arr['return_exch_num'] = 0;
        $arr['refund_exch_num'] = 0;

        if (!isset($arr['lock_num']) || $arr['lock_num'] != '1') {
            $arr['lock_num'] = 0;
        }
        if (!isset($arr['to_transfer_num']) || $arr['to_transfer_num'] != '1') {
            $arr['to_transfer_num'] = 0;
        }

        $arr['stock_flag'] = $arr['to_transfer_num'] . $arr['lock_num'] . $arr['refund_exch_num'] . $arr['return_exch_num'] . $arr['refund_num'] . $arr['return_num'] . $arr['sending_num'] . $arr['unpay_num'] . $arr['order_num'] . $arr['subscribe_num'] . $arr['refund_onway_num'] . $arr['return_onway_num'] . $arr['purchase_arrive_num'] . $arr['transfer_num'] . $arr['to_purchase_num'] . $arr['purchase_num'];

        $arr['stock_flag'] = bindec($arr['stock_flag']);

        $arr['created'] = date('Y-m-d H:i:s');

        if ($arr['type'] == 'add') {
            try {
                $re = $this->fetchSql(false)->where("rule_no =\"{$arr['rule_no']}\" OR rule_name = \"{$arr['rule_name']}\"")->select();
                if (count($re)) {
                    $data['status'] = 0;
                    $data['info']   = '规则编号或名称重复';
                    return ($data);
                }
            } catch (\Exception $e) {
                \Think\Log::write($e->getMessage());
                $data['status'] = 0;
                $data['info']   = '系统错误请联系管理员！';
                return ($data);
            }

            try {
                $this->data($arr)->add();
            } catch (\Exception $e) {
                \Think\Log::write($e->getMessage());
                $data['status'] = 0;
                $data['info']   = '系统错误请联系管理员！';
            }
            $data['type'] = 'add';
        }
        if ($arr['type'] == 'edit') {
            unset($arr['created']);
            try {
                $old_data = $this->getRuleByNo($arr['rule_no']);
                $this->where("rule_no = \"" . $arr['rule_no'] . "\"")->save($arr);
                $new_data = $this->getRuleByNo($arr['rule_no']);
                $sys_other_log = M("sys_other_log");
                $arr_sys_other_log = array(
                    "type" => "15",
                    "operator_id" => get_operator_id(),
                    "data" => 1,
                    "message" => "策略编号:{$arr['rule_no']}修改前策略详情:规则名称:{$old_data[0]['rule_name']}; 优先级:{$old_data[0]['priority']}; 店铺列表:{$old_data[0]['shop_name']};
                        仓库列表:{$old_data[0]['warehouse_name']}; 分类:{$old_data[0]['class_name']}; 品牌:{$old_data[0]['brand_name']};库存方法:{$old_data[0]['stock_flag_string']}; 百分比:{$old_data[0]['percent']}; 百分比附加值:{$old_data[0]['plus_value']};
                        最小库存同步量:{$old_data[0]['min_stock']}; 自动上架:{$old_data[0]['is_auto_listing']}; 自动下架:{$old_data[0]['is_auto_delisting']}; 是否同步库存:{$old_data[0]['is_disable_syn']}; 是否停用:{$old_data[0]['is_disabled']};
                        修改后策略详情:规则名称:{$new_data[0]['rule_name']}; 优先级:{$new_data[0]['priority']}; 店铺列表:{$new_data[0]['shop_name']}; 仓库列表:{$new_data[0]['warehouse_name']};
                        分类:{$new_data[0]['class_name']}; 品牌:{$new_data[0]['brand_name']};库存方法:{$new_data[0]['stock_flag_string']}; 百分比:{$new_data[0]['percent']}; 百分比附加值:{$new_data[0]['plus_value']}; 最小库存同步量:{$new_data[0]['min_stock']};
                        自动上架:{$new_data[0]['is_auto_listing']}; 自动下架:{$new_data[0]['is_auto_delisting']}; 是否同步库存:{$new_data[0]['is_disable_syn']}; 是否停用:{$new_data[0]['is_disabled']};",
                    "created" => date("Y-m-d G:i:s")
                );
                if(!empty($old_data) && !empty($new_data)){
                    $sys_other_log->data($arr_sys_other_log)->add();
                }

            } catch (\Exception $e) {
                \Think\Log::write($e->getMessage());
                $data['status'] = 0;
                $data['info']   = '未知错误，请联系管理员！';
            }
            $data['type'] = 'edit';
        }

        return ($data);
    }

    public function getCfgStockSyncRule($rec_id) {
        try {
            $sql  = "SELECT css.rec_id,css.rule_no,css.rule_name,css.warehouse_list,css.stock_flag as stock_flag,css.priority,
            replace(concat('实际库存',make_set(stock_flag,'+采购在途量','+待采购量','+调拨在途量','+采购到货量','+采购换货在途量','+销售换货在途量',
            '-预订单量','-待审核量','-未付款量','-待发货量','-采购退货量','-销售退货量','-采购换货量','-销售换货量','-锁定库存量','-待调拨量')),',','') stock_flag_string,
            css.percent,css.plus_value,css.min_stock,css.is_auto_listing,css.is_auto_delisting,css.is_disable_syn,css.is_disabled,css.created,css.modified
            FROM cfg_stock_sync_rule css
            INNER JOIN api_goods_spec ags ON(css.rec_id=ags.stock_syn_rule_id AND ags.rec_id=%d)";
            $list = $this->query($sql, $rec_id);
            $data = array("total" => count($list), "rows" => $list);
        } catch (\Exception $e) {
            \Think\Log::write($e->getMessage());
            $data = array("total" => 0, "rows" => array());
        }
        return $data;
    }
    public function getRuleByNo($rule_no){
        try {
            $sql = "SELECT css.rule_no,css.rule_name,css.shop_list,css.warehouse_list,IF(css.class_id='-1','全部',gc.class_name) class_name,IF(css.brand_id='-1','全部',gb.brand_name) brand_name,stock_flag,priority,
            replace(concat('实际库存',make_set(css.stock_flag,'+采购在途量','+待采购量','+调拨在途量','+采购到货量','+采购换货在途量','+销售换货在途量',
                '-预订单量','-待审核量','-未付款量','-待发货量','-采购退货量','-销售退货量','-采购换货量','-销售换货量','-锁定库存量','-待调拨量')),',','') stock_flag_string,
            css.percent,css.plus_value,css.min_stock,IF(css.is_auto_listing=0,'否','是') AS is_auto_listing,IF(css.is_auto_delisting=0,'否','是') AS is_auto_delisting,IF(css.is_disable_syn=0,'否','是') AS is_disable_syn,IF(css.is_disabled=0,'否','是') AS is_disabled
            FROM cfg_stock_sync_rule css
             LEFT JOIN goods_class gc ON css.class_id=gc.class_id
             LEFT JOIN goods_brand gb ON css.brand_id=gb.brand_id WHERE rule_no='{$rule_no}'";
            $data = $this->query($sql);
            if(!empty($data)){
                $shop_result = $this->query("SELECT GROUP_CONCAT(shop_name) AS shop_name FROM cfg_shop WHERE shop_id IN ({$data[0]['shop_list']})");
                $warehouse_result = $this->query("SELECT GROUP_CONCAT(`name`) AS warehouse_name FROM cfg_warehouse WHERE warehouse_id IN ({$data[0]['warehouse_list']})");
                $data[0]['shop_name'] = $shop_result[0]['shop_name'];
                $data[0]['warehouse_name'] = $warehouse_result[0]['warehouse_name'];
            }
        } catch (\Exception $e) {
            \Think\Log::write($e->getMessage());
            $data = array();
        }
        return $data;
    }

}