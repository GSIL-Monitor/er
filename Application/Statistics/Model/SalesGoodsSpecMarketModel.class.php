<?php
namespace Statistics\Model;
use Think\Log;
use Think\Model;
use Common\Common\ExcelTool;

class SalesGoodsSpecMarketModel extends Model{
    protected $tableName = 'stat_daily_sales_spec_shop ';
    protected $pk = 'rec_id' ;

    public function searchFormDeal(&$where,$search,&$left_join_goods_class_str){
        foreach ($search as $k => $v) {
            if ($v === '') continue;
                switch($k){
                case 'brand_id':
                    set_search_form_value($where,$k,$v,'gb',2,'AND');
                    break;
                case 'class_id':
                    $left_join_goods_class_str = set_search_form_value($where, $k, $v, 'gc', 7, 'AND');
                    break;
                case 'spec_no':
                    set_search_form_value($where,$k,$v,'gs',6,'AND');
                    break;
                case 'goods_no':
                    set_search_form_value($where,$k,$v,'gg',6,'AND');
                    break;
                case 'goods_name':
                    set_search_form_value($where,$k,$v,'gg',6,'AND');
                    break;
                case 'start_time':
                    set_search_form_value($where, 'sales_date', $v,'ssps', 3,' AND ',' >= ');
                    break;
                case 'end_time':
                    set_search_form_value($where, 'sales_date', $v,'ssps', 3,' AND ',' < ');
            }
        }
    }

    public function loadDataByCondition($page, $rows, $search, $sort, $order){
        $page  = intval($page);
        $rows  = intval($rows);
        $where = ' WHERE TRUE ';
        $count_num=0;
        D('Setting/EmployeeRights')->setSearchRights($search,'shop_id',1);
		D('Setting/EmployeeRights')->setSearchRights($search,'warehouse_id',2);
        $left_join_goods_class_str = '';
        $this->searchFormDeal($where,$search,$left_join_goods_class_str);
        $limit = ($page - 1) * $rows . "," . $rows;
        $sql = "SELECT
                ssps.spec_id id,gs.spec_no,gg.goods_no,gg.goods_name,gs.retail_price,gg.brand_id,gb.brand_name AS brand_id,gg.class_id,gc.class_name AS class_id,gs.spec_code,gs.spec_name,gg.short_name,
                cast((if(SUM(ssps.num-ssps.refund_num)=0,0,SUM(ssps.amount-ssps.refund_amount)/SUM(ssps.num-ssps.refund_num))) as decimal(19,4)) AS avg_price,SUM(ssps.num) AS num,
                SUM(ssps.return_num) AS return_num,SUM(ssps.refund_num) AS refund_num,SUM(ssps.unknown_goods_amount) AS unknown_goods_amount,SUM(ssps.goods_cost) AS goods_cost,
                SUM(ssps.gift_num) as gift_num,IFNULL(cast((if(SUM(ssps.num)=0,0,SUM(ssps.amount)/SUM(ssps.num))/gs.retail_price) as decimal(19,2)),'') AS discount_rate,
                SUM(ssps.swap_num) as swap_num,SUM(ssps.num-ssps.refund_num-ssps.return_num) AS actual_num,SUM(ssps.post_amount) AS post_amount,SUM(ssps.return_amount) AS return_amount,
                SUM(ssps.amount) as amount,SUM(ssps.amount-ssps.goods_cost-ssps.unknown_goods_amount-ssps.commission) AS profit,SUM(ssps.return_cost) AS return_cost,
                SUM(ssps.amount-ssps.refund_amount-ssps.return_amount) AS actual_amount,SUM(ssps.goods_cost-ssps.return_cost) AS actual_goods_cost,
                SUM(ssps.amount+ssps.return_cost-ssps.refund_amount-ssps.goods_cost-ssps.return_amount-ssps.unknown_goods_amount-ssps.commission) AS actual_profit
                FROM stat_daily_sales_spec_shop  ssps
                LEFT JOIN goods_spec gs ON (gs.spec_id = ssps.spec_id)
                LEFT JOIN cfg_shop cs ON (cs.shop_id=ssps.shop_id)
                LEFT JOIN goods_goods gg ON (gg.goods_id = gs.goods_id)
                LEFT JOIN goods_brand gb ON (gb.brand_id = gg.brand_id)
                LEFT JOIN goods_class gc ON (gc.class_id = gg.class_id)"
                . $left_join_goods_class_str ."
                {$where} GROUP BY ssps.spec_id
                ORDER BY {$sort} {$order},goods_name DESC LIMIT {$limit}";

        $sql_count = "SELECT COUNT(1) total FROM (SELECT
                gs.spec_no,gg.goods_no,gg.goods_name,gs.retail_price,gg.brand_id,gg.class_id,gs.spec_code,gs.spec_name,gg.short_name,
                SUM(ssps.num) AS num,
                SUM(ssps.return_num) AS return_num,SUM(ssps.refund_num) AS refund_num,
                SUM(ssps.gift_num) as gift_num,
                SUM(ssps.swap_num) as swap_num,
                SUM(ssps.amount) as amount                
                FROM stat_daily_sales_spec_shop  ssps
                LEFT JOIN goods_spec gs ON (gs.spec_id = ssps.spec_id)
                LEFT JOIN cfg_shop cs ON (cs.shop_id=ssps.shop_id)
                LEFT JOIN goods_goods gg ON (gg.goods_id = gs.goods_id)
                LEFT JOIN goods_brand gb ON (gb.brand_id = gg.brand_id)
                LEFT JOIN goods_class gc ON (gc.class_id = gg.class_id)"
                . $left_join_goods_class_str ."
                {$where} GROUP BY ssps.spec_id
                ORDER BY {$sort} {$order}) tmp";
        try{
            $count = $this->query($sql_count);
            $data['rows'] = $this->query($sql);
            $data['total'] = $count[0]['total'];
        }catch (\PDOException $e){
            Log::write($e->getMessage());
            $data = array("total" => 0,"rows" => array());
        }catch (\Exception $e){
            Log::write($e->getMessage());
            $data = array("total" => 0,"rows" => array());
        }
        return $data;
    }
    public function exportToExcel($search, $id_list){
        $creator=session('account');
        $where=' WHERE TRUE  ';
        D('Setting/EmployeeRights')->setSearchRights($search,'shop_id',1);
        $left_join_goods_class_str='';
        $this->searchFormDeal($where,$search,$left_join_goods_class_str);
        $having = '';
        if(!empty($id_list)){
            $having = ' having ssps.spec_id in (' . $id_list . ') ';
        }

        $sql = "SELECT
                ssps.rec_id AS id,gs.spec_no,gg.goods_no,gg.goods_name,gs.retail_price,gg.brand_id,gb.brand_name AS brand_id,gg.class_id,gc.class_name AS class_id,gs.spec_code,gs.spec_name,gg.short_name,
                cast((if(SUM(ssps.num-ssps.refund_num)=0,0,SUM(ssps.amount-ssps.refund_amount)/SUM(ssps.num-ssps.refund_num))) as decimal(19,4)) AS avg_price,SUM(ssps.num) AS num,
                SUM(ssps.return_num) AS return_num,SUM(ssps.refund_num) AS refund_num,SUM(ssps.unknown_goods_amount) AS unknown_goods_amount,SUM(ssps.goods_cost) AS goods_cost,
                SUM(ssps.gift_num) as gift_num,IFNULL(cast((if(SUM(ssps.num)=0,0,SUM(ssps.amount)/SUM(ssps.num))/gs.retail_price) as decimal(19,2)),'') AS discount_rate,
                SUM(ssps.swap_num) as swap_num,SUM(ssps.num-ssps.refund_num-ssps.return_num) AS actual_num,SUM(ssps.post_amount) AS post_amount,SUM(ssps.return_amount) AS return_amount,
                SUM(ssps.amount) as amount,SUM(ssps.amount-ssps.goods_cost-ssps.unknown_goods_amount-ssps.commission) AS profit,SUM(ssps.return_cost) AS return_cost,
                SUM(ssps.amount-ssps.refund_amount-ssps.return_amount) AS actual_amount,SUM(ssps.goods_cost-ssps.return_cost) AS actual_goods_cost,
                SUM(ssps.amount+ssps.return_cost-ssps.refund_amount-ssps.goods_cost-ssps.return_amount-ssps.unknown_goods_amount-ssps.commission) AS actual_profit
                FROM stat_daily_sales_spec_shop  ssps
                LEFT JOIN goods_spec gs ON (gs.spec_id = ssps.spec_id)
                LEFT JOIN cfg_shop cs ON (cs.shop_id=ssps.shop_id)
                LEFT JOIN goods_goods gg ON (gg.goods_id = gs.goods_id)
                LEFT JOIN goods_brand gb ON (gb.brand_id = gg.brand_id)
                LEFT JOIN goods_class gc ON (gc.class_id = gg.class_id)"
                . $left_join_goods_class_str ."
                {$where}GROUP BY ssps.spec_id{$having}
                ORDER BY num DESC,goods_name DESC";
        try{
            $data['rows'] = $this->query($sql);
            $excel_header = D('Setting/UserData')->getExcelField('Statistics/SalesGoodsSpecMarket','sales_goods_spec_market');
            $width_list = array();
            foreach ($excel_header as $v)
            {
                $width_list[]=20;
            }
            $title = '单品畅销统计';
            $filename = '单品畅销统计';

            ExcelTool::Arr2Excel($data['rows'],$title,$excel_header,$width_list,$filename,$creator);

        }catch(\PDOException $e){
            \Think\Log::write($e->getMessage());
            SE(parent::PDO_ERROR);
        }catch(BusinessLogicException $e){
            SE($e->getMessage());
        }catch(\Exception $e){
            \Think\Log::write($e->getMessage());
            SE(parent::PDO_ERROR);
        }

    }
}