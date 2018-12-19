<?php
/**
 * Created by PhpStorm.
 * User: gaosong
 * Date: 11/19/15
 * Time: 11:21
 */
namespace Stock\Model;

use Think\Model;

class ApiStockSyncRecordModel extends Model {
    protected $tableName = 'api_stock_sync_record';
    protected $pk        = 'rec_id';

    public function loadDataByCondition($page, $rows, $search, $sort, $order) {
        $where = '';
        foreach ($search as $k => $v) {
            if ($v === '') continue;
            switch ($k) {
                case 'shop_id':
                    set_search_form_value($where, $k, $v, 'ass_1', 2, ' AND ');
                    break;
                case 'outer_id':
                    set_search_form_value($where, $k, $v, 'ag', 1, ' AND ');
                    break;
                case 'goods_id':
                    set_search_form_value($where, $k, $v, 'ag', 1, ' AND ');
                    break;
                case 'spec_id':
                    set_search_form_value($where, $k, $v, 'ag', 1, ' AND ');
                    break;
                case 'start_time':
                    set_search_form_value($where, 'created', $v,'ass_1', 3,' AND ',' >= ');
                    break;
                case 'end_time':
                    set_search_form_value($where, 'created', $v,'ass_1', 3,' AND ',' <= ');
                    break;

                default:
                    break;
            }
        }
        if ($where != "") {
            $where = " WHERE " . ltrim($where, ' AND');
        }
        $page = intval($page);
        $rows = intval($rows);
        $limit = ($page - 1) * $rows . "," . $rows;//分页
        $order = $sort . ' ' . $order;//排序
        $order = addslashes($order);
		$point_number = get_config_value('point_number',0);
		
		$syn_stock = "CAST(ass.num AS DECIMAL(19,".$point_number.")) syn_stock";
		$stock_syn_plus = "CAST(ass.plus_value AS DECIMAL(19,".$point_number.")) stock_syn_plus";
		$stock_syn_min = "CAST(ass.min_stock AS DECIMAL(19,".$point_number.")) stock_syn_min";
		
        try {
            $sql       = "SELECT ass_1.rec_id FROM api_stock_sync_record ass_1
                LEFT JOIN api_goods_spec ag ON ag.shop_id=ass_1.shop_id AND ag.goods_id=ass_1.goods_id AND ag.spec_id=ass_1.spec_id
                LEFT JOIN goods_spec gsp ON ass_1.match_target_type=1 AND ass_1.match_target_id=gsp.spec_id
                LEFT JOIN goods_suite gsu ON ass_1.match_target_type=2 AND ass_1.match_target_id=gsu.suite_id
                LEFT JOIN cfg_shop cs ON ag.shop_id=cs.shop_id {$where} ORDER BY {$order} LIMIT {$limit}";
            $sql_count = "SELECT COUNT(*) AS total FROM api_stock_sync_record ass_1
                LEFT JOIN api_goods_spec ag ON ag.shop_id=ass_1.shop_id AND ag.goods_id=ass_1.goods_id AND ag.spec_id=ass_1.spec_id
                LEFT JOIN goods_spec gsp ON ass_1.match_target_type=1 AND ass_1.match_target_id=gsp.spec_id
                LEFT JOIN goods_suite gsu ON ass_1.match_target_type=2 AND ass_1.match_target_id=gsu.suite_id
                LEFT JOIN cfg_shop cs ON ag.shop_id=cs.shop_id {$where}";
            $sql_list  = "SELECT ass.rec_id as id,cs.shop_name shop_id,ag.outer_id,ag.goods_id,ag.spec_id,ag.goods_name api_goods_name,
                ag.spec_outer_id,ag.spec_name api_spec_name,IF(ass.match_target_type=1,'单品','组合装') AS goods_type,".$syn_stock.",
                ass.rule_no stock_syn_rule_no,ass.warehouse_list stock_syn_warehouses,ass.mask stock_syn_mask,ass.percent stock_syn_percent,
                ".$stock_syn_plus.",".$stock_syn_min.",IF(ass.is_auto_listing=1,'是','否') is_auto_listing,
                IF(ass.is_auto_delisting=1,'是','否') is_auto_delisting,IF(ass.is_sucess=1,'是','否') is_syn_sucess,ass.result syn_result,
                IF(ass.is_manual=1,'手动','自动') AS syn_type,ass.created,IF(ass.match_target_type=1,gs1.spec_no,gs2.suite_no) AS spec_no,ag.match_target_id,
                replace(CONCAT('实际库存',make_set(ass.mask,'+采购在途量','+待采购量','+调拨在途量','+采购到货量','+采购换货在途量','+销售换货在途量','-预订单量','-待审核量','-未付款量','-待发货量','-采购退货量','-销售退货量','-采购换货量','-销售换货量')),',','') stock_syn_mask
                FROM api_stock_sync_record ass
                INNER JOIN(" . $sql . ") temp ON (ass.rec_id=temp.rec_id)
                LEFT JOIN api_goods_spec AS ag ON ag.shop_id = ass.shop_id AND ag.goods_id=ass.goods_id AND ag.spec_id = ass.spec_id
                LEFT JOIN goods_spec AS gs1 ON ass.match_target_id = gs1.spec_id AND ass.match_target_type=1
                LEFT JOIN goods_suite AS gs2 ON ass.match_target_id = gs2.suite_id AND ass.match_target_type=2
                LEFT JOIN cfg_shop cs ON cs.shop_id=ass.shop_id";
            $total     = $this->query($sql_count);
            $total     = $total[0]["total"];
            $rows      = $this->query($sql_list);
            $data      = array("total" => $total, "rows" => $rows);
        } catch (\PDOException $e) {
            $msg = $e->getMessage();
            \Think\Log::write($msg);
            $data = array("total" => 0, "rows" => array());
        } catch (\Exception $e) {
            \Think\Log::write($e->getMessage());
            $data = array("total" => 0, "rows" => array());
        }
        return $data;
    }

    public function getStockSyncRecord($rec_id, $page = 1, $rows = 20) {
        try {
            $page = intval($page);
            $rows = intval($rows);
            $limit = ($page - 1) * $rows . "," . $rows;//分页
            $sql_count = "SELECT count(*) as total FROM api_goods_spec ag,api_stock_sync_record ass
                LEFT JOIN goods_spec AS gs1 ON ass.match_target_id = gs1.spec_id AND ass.match_target_type=1
                LEFT JOIN goods_suite AS gs2 ON ass.match_target_id = gs2.suite_id AND ass.match_target_type=2
                LEFT JOIN cfg_shop cs ON(ass.shop_id=cs.shop_id)
                WHERE ag.rec_id =%d AND ass.platform_id = ag.platform_id AND ass.goods_id = ag.goods_id AND ass.spec_id=ag.spec_id";
            $sql  = "SELECT ass.rec_id as id,cs.shop_name shop_id,ag.outer_id,ag.goods_id,ag.spec_id,ag.goods_name api_goods_name,
                ag.spec_outer_id,ag.spec_name api_spec_name,IF(ass.match_target_type=1,'单品','组合装') AS goods_type,ass.num syn_stock,
                ass.rule_no stock_syn_rule_no,ass.warehouse_list stock_syn_warehouses,ass.mask stock_syn_mask,ass.percent stock_syn_percent,
                ass.plus_value stock_syn_plus,ass.min_stock stock_syn_min,IF(ass.is_auto_listing=1,'是','否') is_auto_listing,
                IF(ass.is_auto_delisting=1,'是','否') is_auto_delisting,IF(ass.is_sucess=1,'是','否') is_syn_sucess,ass.result syn_result,
                IF(ass.is_manual=1,'手动','自动') AS syn_type,ass.created,IF(ass.match_target_type=1,gs1.spec_no,gs2.suite_no) AS spec_no,ass.match_target_id,
                replace(CONCAT('实际库存',make_set(ass.mask,'+采购在途量','+待采购量','+调拨在途量','+采购到货量','+采购换货在途量','+销售换货在途量','-预订单量','-待审核量','-未付款量','-待发货量','-采购退货量','-销售退货量','-采购换货量','-销售换货量')),',','') stock_syn_mask
                FROM api_goods_spec ag,api_stock_sync_record ass
                LEFT JOIN goods_spec AS gs1 ON ass.match_target_id = gs1.spec_id AND ass.match_target_type=1
                LEFT JOIN goods_suite AS gs2 ON ass.match_target_id = gs2.suite_id AND ass.match_target_type=2
                LEFT JOIN cfg_shop cs ON(ass.shop_id=cs.shop_id)
                WHERE ag.rec_id =%d AND ass.platform_id = ag.platform_id AND ass.goods_id = ag.goods_id and ass.spec_id=ag.spec_id
                order by ass.rec_id desc LIMIT $limit";
            $total = M()->query($sql_count, $rec_id);
            $total = $total[0]["total"];
            $list = M()->query($sql, $rec_id);
            $data = array("total" => $total, "rows" => $list);
        } catch (\Exception $e) {
            \Think\Log::write($e->getMessage());
            $data = array("total" => 0, "rows" => array());
        }
        return $data;
    }
}