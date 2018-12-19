<?php
namespace Goods\Model;
use Common\Common\UtilTool;
use Think\Exception;
use Think\Exception\BusinessLogicException;
use Think\Model;

class GoodsMatchModel extends Model{
    protected $tableName = "api_goods_spec";
    protected $pk        = "rec_id";

    public function getGoodsMatchList($page = 1, $rows = 10, $search = array(), $sort = 'rec_id', $order =
    'desc')
    {
        try{
            $where = "true ";
            $page = intval($page);
            $rows = intval($rows);
            D('Goods/PlatformGoods')->searchFormDeal($where,$search);
            $inner_sort = 'ag.' . $sort . " " . $order;
            $inner_sort = addslashes($inner_sort);
            $outer_sort = 'ag_1.' . $sort . " " . $order;
            $outer_sort = addslashes($outer_sort);

            $limit      = ($page - 1) * $rows . "," . $rows;
            //先查询出需要显示的平台货品的rec_id
            $sql_result = "SELECT ag.rec_id FROM api_goods_spec ag LEFT JOIN goods_merchant_no gmn ON gmn.type=ag.match_target_type AND gmn.target_id=ag.match_target_id WHERE $where ORDER BY $inner_sort LIMIT $limit";

            $sql = "SELECT ag_1.rec_id as id,ag_1.status,ag_1.platform_id,ag_1.shop_id,cs.shop_name,ag_1.goods_id,ag_1.spec_id,
                ag_1.spec_code,ag_1.goods_name AS platform_goods_name,ag_1.spec_name AS platform_spec_name,ag_1.outer_id AS platform_outer_id,
                ag_1.spec_outer_id AS platform_spec_outer_id,
                ag_1.pic_url,(1-ag_1.is_manual_match) as is_auto_match,ag_1.match_code,ag_1.match_target_type,ag_1.match_target_id,
                IFNULL(gmn.merchant_no,'未匹配到系统货品') AS merchant_no,(gmn.type-1) AS is_suite,IF(gmn.type=1,gg.goods_name,'') AS goods_name,t.spec_name AS spec_name,/*IF(gmn.type=1,gs.spec_name,gsu.suite_name) AS spec_name,*/
                t.spec_code AS spec_code
                FROM api_goods_spec ag_1
                INNER JOIN (" . $sql_result . ") ag_2 ON(ag_1.rec_id=ag_2.rec_id)
                /*INNER JOIN goods_spec gs ON(ag_1.match_target_id=gs.spec_id AND ag_1.match_target_type=1)
                INNER JOIN goods_suite gsu ON(ag_1.match_target_id=gsu.suite_id AND ag_1.match_target_type=2)*/
                LEFT JOIN (SELECT 1 AS type,spec_id,spec_name,spec_code,goods_id FROM goods_spec
                            UNION ALL
                            SELECT 2 AS type,suite_id,suite_name,prop1,prop2 FROM goods_suite) t ON ag_1.match_target_type = t.type and ag_1.match_target_id = t.spec_id
                LEFT JOIN goods_goods gg ON(t.goods_id=gg.goods_id)
                LEFT JOIN goods_merchant_no gmn ON (gmn.type=ag_1.match_target_type AND gmn.target_id=ag_1.match_target_id)
                LEFT JOIN cfg_shop cs ON(ag_1.shop_id=cs.shop_id)
                ORDER BY $outer_sort";
            $sql_total     = "SELECT COUNT(*) AS total FROM api_goods_spec ag LEFT JOIN goods_merchant_no gmn ON gmn.type=ag.match_target_type AND gmn.target_id=ag.match_target_id WHERE $where";
            $data["total"] = $this->query($sql_total)[0]["total"];
            $data["rows"]  = $this->query($sql);
        }catch (\Exception $e) {
            \Think\Log::write($e->getMessage());
            $data["total"] = 0;
            $data["rows"]  = "";
        }
        return $data;
    }



}