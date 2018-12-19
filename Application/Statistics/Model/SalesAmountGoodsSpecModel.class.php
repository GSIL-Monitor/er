<?php
namespace Statistics\Model;
use Think\Log;
use Think\Model;
use Common\Common\ExcelTool;

class SalesAmountGoodsSpecModel extends Model{
    protected $tableName = 'stat_daily_sales_spec_shop ';
    protected $pk = 'rec_id' ;
    
    public function searchFormDeal(&$where,$search,&$left_join_goods_class_str){
        foreach($search as $k=>$v){
            if (!isset($v)) continue;
            switch($k){
                case 'shop_id':
                    set_search_form_value($where,$k,$v,'ssps',2,'AND');
                    break;
                case 'warehouse_id':
                    set_search_form_value($where,$k,$v,'ssps',2,'AND');
                    break;
                case 'brand_id':
                    set_search_form_value($where,$k,$v,'gb',2,'AND');
                    break;
                case 'class_id':
                    $left_join_goods_class_str = set_search_form_value($where, $k, $v, 'gc', 7, 'AND');
                    break;
                case 'spec_no':
                    set_search_form_value($where,$k,$v,'gs',1,'AND');
                    break;
                case 'start_time':
                    set_search_form_value($where, 'sales_date', $v,'ssps', 3,' AND ',' >= ');
                    break;
                case 'end_time':
                    set_search_form_value($where, 'sales_date', $v,'ssps', 3,' AND ',' < ');
                    break;
                case 'goods_name':
                    set_search_form_value($where, $k, $v, 'gg', 1, 'AND');
            }
        }
    }

    public function loadDataByCondition($page, $rows, $search, $sort, $order){
        $page  = intval($page);
        $rows  = intval($rows);
        $where = ' WHERE TRUE ';
        /*if($search['stat_type'] == 3){
            $num = strtotime($search['end_time'])-strtotime($search['start_time']);
            if(intval($num/3600/24)>30){
                $data = array();
                return $data;
            }
        }*/
        if($search['stat_type'] == 2){
			D('Setting/EmployeeRights')->setSearchRights($search,'warehouse_id',2);
            unset($search['shop_id']);

        }else{
            //设置店铺权限
            D('Setting/EmployeeRights')->setSearchRights($search,'shop_id',1);
            unset($search['warehouse_id']);
        }

        $left_join_goods_class_str='';
        $this->searchFormDeal($where,$search,$left_join_goods_class_str);
        $limit = ($page - 1) * $rows . "," . $rows;//分页
        $sort  = $sort . " " . $order;
        $sort  = addslashes($sort);
        if( $search['stat_type'] == 2){
            $sql = "SELECT
                ssps.spec_id id,ssps.warehouse_id AS s_id,cw.name AS warehouse_name,gs.spec_no,gg.goods_no,gg.goods_name,gs.retail_price,gg.brand_id,gb.brand_name AS brand_id,gg.class_id,gc.class_name AS class_id,gs.spec_code,gs.spec_name,gg.short_name,
                cast((if(SUM(ssps.num-ssps.refund_num)=0,0,SUM(ssps.amount-ssps.refund_amount)/SUM(ssps.num-ssps.refund_num))) as decimal(19,4)) AS avg_price,SUM(ssps.num) AS num,
                SUM(ssps.return_num) AS return_num,SUM(ssps.refund_num) AS refund_num,SUM(ssps.amount) AS amount,SUM(ssps.refund_amount) AS refund_amount,
                SUM(ssps.unknown_goods_amount) AS unknown_goods_amount,SUM(ssps.goods_cost) AS goods_cost,SUM(ssps.post_amount) AS post_amount,SUM(ssps.guarante_refund_amount) AS guarante_refund_amount,
                SUM(ssps.amount-ssps.goods_cost-ssps.unknown_goods_amount-ssps.commission) AS profit,SUM(ssps.commission) AS commission,SUM(ssps.post_amount) AS post_amount,
                SUM(ssps.return_amount) AS return_amount,SUM(ssps.return_cost) AS return_cost,IFNULL(cast((if(SUM(ssps.num)=0,0,SUM(ssps.amount)/SUM(ssps.num))/gs.retail_price) as decimal(19,2)),'') AS discount_rate,
                SUM(ssps.num-ssps.refund_num-ssps.return_num) AS actual_num,SUM(ssps.amount-ssps.refund_amount-ssps.return_amount) AS actual_amount,
                SUM(ssps.goods_cost-ssps.return_cost) AS actual_goods_cost,SUM(ssps.gift_num) as gift_num,SUM(ssps.swap_num) as swap_num,
                SUM(ssps.amount+ssps.return_cost-ssps.refund_amount-ssps.goods_cost-ssps.return_amount-ssps.unknown_goods_amount-ssps.commission) AS actual_profit
                FROM stat_daily_sales_spec_warehouse  ssps
                LEFT JOIN goods_spec gs ON (gs.spec_id = ssps.spec_id)
                LEFT JOIN cfg_warehouse cw ON (cw.warehouse_id=ssps.warehouse_id)
                LEFT JOIN goods_goods gg ON (gg.goods_id = gs.goods_id)
                LEFT JOIN goods_brand gb ON (gb.brand_id = gg.brand_id)
                LEFT JOIN goods_class gc ON (gc.class_id = gg.class_id)"
                . $left_join_goods_class_str ."
                {$where} GROUP BY ssps.spec_id,ssps.warehouse_id
                ORDER BY {$sort} LIMIT {$limit}";
            $sql_count = "SELECT COUNT(1) total FROM (SELECT ssps.rec_id,gs.spec_no,gg.goods_no,gg.goods_name,gs.retail_price,gg.brand_id,gg.class_id,gs.spec_code,gs.spec_name,gg.short_name
                FROM stat_daily_sales_spec_warehouse ssps
                LEFT JOIN goods_spec gs ON (gs.spec_id = ssps.spec_id)
                LEFT JOIN cfg_warehouse cw ON (cw.warehouse_id=ssps.warehouse_id)
                LEFT JOIN goods_goods gg ON (gg.goods_id = gs.goods_id)
                LEFT JOIN goods_brand gb ON (gb.brand_id = gg.brand_id)
                LEFT JOIN goods_class gc ON (gc.class_id = gg.class_id)"
                . $left_join_goods_class_str .
                "{$where} GROUP BY ssps.spec_id,ssps.warehouse_id) tmp";
        }elseif( $search['stat_type'] == 3){//按付款时间统计
            $where = str_replace('ssps.sales_date','stl.created',$where);
            $where = str_replace('ssps.shop_id','st.shop_id',$where);
            $where = str_replace('ssps.warehouse_id','st.warehouse_id',$where);
            $tmp_sql = "SELECT  sto.rec_id,st.shop_id FROM sales_trade st INNER JOIN sales_trade_log stl USE INDEX(IX_sales_trade_log) ON stl.trade_id=st.trade_id
                    LEFT JOIN sales_trade_order sto ON st.trade_id = sto.trade_id
                    LEFT JOIN goods_spec gs ON gs.spec_id = sto.spec_id
                    LEFT JOIN goods_suite gse ON sto.suite_id =gse.suite_id
                    LEFT JOIN goods_goods gg ON gg.goods_id = gs.goods_id
                    LEFT JOIN goods_class gc ON gc.class_id = gg.class_id
                    LEFT JOIN cfg_warehouse cw ON cw.warehouse_id = st.warehouse_id
                    {$where} AND stl.type=2 AND stl.data<>-50 AND cw.type <> 127";
            $sql = "SELECT tmp.shop_id AS s_id,sto.spec_id AS id,cs.shop_name AS shop_id,gs.spec_no,gs.retail_price,gg.goods_no,gg.goods_name,gg.short_name,gg.brand_id,gb.brand_name AS brand_id,gg.class_id,gc.class_name AS class_id,gs.spec_code,gs.spec_name,
                    cast((if(SUM(sto.actual_num)=0,0,SUM(IF(sto.num>0 && sto.actual_num=0,0,sto.share_amount))/SUM(sto.actual_num))) as decimal(19,4)) AS avg_price,SUM(sto.num) AS order_num,SUM(sto.actual_num) AS actual_num,
                    SUM(sto.share_amount) AS amount,SUM(IF(sto.num>0 && sto.actual_num=0,0,sto.share_amount)) actual_amount,SUM(IF(sto.num>0 && sto.actual_num=0,0,sto.share_post)) as post_amount,
                    IFNULL(cast((if(SUM(sto.actual_num)=0,0,SUM(sto.share_amount)/SUM(sto.actual_num))/gs.retail_price) as decimal(19,2)),\"\") AS discount_rate
                    FROM ($tmp_sql ) tmp
                    LEFT JOIN sales_trade_order sto  ON sto.rec_id = tmp.rec_id
                    LEFT JOIN cfg_shop cs ON tmp.shop_id = cs.shop_id
                    LEFT JOIN goods_spec gs ON gs.spec_id = sto.`spec_id`
                    LEFT JOIN goods_goods gg ON gg.`goods_id` = gs.`goods_id`
                    LEFT JOIN goods_brand gb ON gb.brand_id = gg.brand_id
                    LEFT JOIN goods_class gc ON gc.class_id = gg.class_id
                    GROUP BY sto.spec_id,tmp.shop_id  ORDER BY sto.spec_id ASC
                    LIMIT {$limit}
                    ";
            $sql_count = "SELECT COUNT(1) total FROM ($tmp_sql GROUP BY sto.spec_id,st.shop_id) tmp";


        }else{
            //均价计算方法 如果销售总量=退换量 那么为0 否则为（销售总额-退还总额）/（销售总量-退换总量）
            $sql = "SELECT
                ssps.spec_id AS id,ssps.shop_id AS s_id,cs.shop_name AS shop_id,gs.spec_no,gg.goods_no,gg.goods_name,gs.retail_price,gg.brand_id,gb.brand_name AS brand_id,gg.class_id,gc.class_name AS class_id,gs.spec_code,gs.spec_name,gg.short_name,
                cast((if(SUM(ssps.num-ssps.refund_num)=0,0,SUM(ssps.amount-ssps.refund_amount)/SUM(ssps.num-ssps.refund_num))) as decimal(19,4)) AS avg_price,SUM(ssps.num) AS num,
                SUM(ssps.return_num) AS return_num,SUM(ssps.refund_num) AS refund_num,SUM(ssps.amount) AS amount,SUM(ssps.refund_amount) AS refund_amount,
                SUM(ssps.unknown_goods_amount) AS unknown_goods_amount,SUM(ssps.goods_cost) AS goods_cost,SUM(ssps.post_amount) AS post_amount,SUM(ssps.guarante_refund_amount) AS guarante_refund_amount,
                SUM(ssps.amount-ssps.goods_cost-ssps.unknown_goods_amount-ssps.commission) AS profit,SUM(ssps.commission) AS commission,SUM(ssps.post_amount) AS post_amount,
                SUM(ssps.return_amount) AS return_amount,SUM(ssps.return_cost) AS return_cost,IFNULL(cast((if(SUM(ssps.num)=0,0,SUM(ssps.amount)/SUM(ssps.num))/gs.retail_price) as decimal(19,2)),'') AS discount_rate,
                SUM(ssps.num-ssps.refund_num-ssps.return_num) AS actual_num,SUM(ssps.amount-ssps.refund_amount-ssps.return_amount) AS actual_amount,
                SUM(ssps.goods_cost-ssps.return_cost) AS actual_goods_cost,SUM(ssps.gift_num) as gift_num,SUM(ssps.swap_num) as swap_num,
                SUM(ssps.amount+ssps.return_cost-ssps.refund_amount-ssps.goods_cost-ssps.return_amount-ssps.unknown_goods_amount-ssps.commission) AS actual_profit
                FROM stat_daily_sales_spec_shop  ssps
                LEFT JOIN goods_spec gs ON (gs.spec_id = ssps.spec_id)
                LEFT JOIN cfg_shop cs ON (cs.shop_id=ssps.shop_id)
                LEFT JOIN goods_goods gg ON (gg.goods_id = gs.goods_id)
                LEFT JOIN goods_brand gb ON (gb.brand_id = gg.brand_id)
                LEFT JOIN goods_class gc ON (gc.class_id = gg.class_id)"
                . $left_join_goods_class_str ."
                {$where} GROUP BY ssps.spec_id,ssps.shop_id
                ORDER BY {$sort} LIMIT {$limit}";
            $sql_count = "SELECT COUNT(1) total FROM (SELECT ssps.shop_id,gs.spec_no,gg.goods_no,gg.goods_name,gs.retail_price,gg.brand_id,gg.class_id,gs.spec_code,gs.spec_name,gg.short_name
                FROM stat_daily_sales_spec_shop ssps
                LEFT JOIN goods_spec gs ON (gs.spec_id = ssps.spec_id)
                LEFT JOIN cfg_shop cs ON (cs.shop_id=ssps.shop_id)
                LEFT JOIN goods_goods gg ON (gg.goods_id = gs.goods_id)
                LEFT JOIN goods_brand gb ON (gb.brand_id = gg.brand_id)
                LEFT JOIN goods_class gc ON (gc.class_id = gg.class_id)"
                . $left_join_goods_class_str .
                "{$where} GROUP BY ssps.spec_id,ssps.shop_id) tmp";
        }


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
    public function exportToExcel($search, $id_list,$type='excel'){
        $creator=session('account');
        $where=' WHERE TRUE ';
        $left_join_goods_class_str ='';
        if($search['stat_type'] == 2){
			D('Setting/EmployeeRights')->setSearchRights($search,'warehouse_id',2);
            unset($search['shop_id']);

        }else{
            //设置店铺权限
            D('Setting/EmployeeRights')->setSearchRights($search,'shop_id',1);
            unset($search['warehouse_id']);
        }
        $this->searchFormDeal($where,$search,$left_join_goods_class_str);
        $sort = 'ssps.spec_id,gg.goods_id desc';
        $having = '';

        if($search['stat_type'] == 2) {
            if(!empty($id_list['id'])){
                $having = ' having ssps.spec_id in ('.$id_list['id'].') and ssps.warehouse_id in ('.$id_list['s_id'].') ';
            }
            $sql = "SELECT
                ssps.rec_id id,ssps.warehouse_id,cw.name AS warehouse_name,gs.spec_no,gg.goods_no,gg.goods_name,gs.retail_price,gg.brand_id,gb.brand_name AS brand_id,gg.class_id,gc.class_name AS class_id,gs.spec_code,gs.spec_name,gg.short_name,
                cast((if(SUM(ssps.num-ssps.refund_num)=0,0,SUM(ssps.amount-ssps.refund_amount)/SUM(ssps.num-ssps.refund_num))) as decimal(19,4)) AS avg_price,SUM(ssps.num) AS num,
                SUM(ssps.return_num) AS return_num,SUM(ssps.refund_num) AS refund_num,SUM(ssps.amount) AS amount,SUM(ssps.refund_amount) AS refund_amount,
                SUM(ssps.unknown_goods_amount) AS unknown_goods_amount,SUM(ssps.goods_cost) AS goods_cost,SUM(ssps.post_amount) AS post_amount,SUM(ssps.guarante_refund_amount) AS guarante_refund_amount,
                SUM(ssps.amount-ssps.goods_cost-ssps.unknown_goods_amount-ssps.commission) AS profit,SUM(ssps.commission) AS commission,SUM(ssps.post_amount) AS post_amount,
                SUM(ssps.return_amount) AS return_amount,SUM(ssps.return_cost) AS return_cost,IFNULL(cast((if(SUM(ssps.num)=0,0,SUM(ssps.amount)/SUM(ssps.num))/gs.retail_price) as decimal(19,2)),'') AS discount_rate,
                SUM(ssps.num-ssps.refund_num-ssps.return_num) AS actual_num,SUM(ssps.amount-ssps.refund_amount-ssps.return_amount) AS actual_amount,
                SUM(ssps.goods_cost-ssps.return_cost) AS actual_goods_cost,SUM(ssps.gift_num) as gift_num,SUM(ssps.swap_num) as swap_num,
                SUM(ssps.amount+ssps.return_cost-ssps.refund_amount-ssps.goods_cost-ssps.return_amount-ssps.unknown_goods_amount-ssps.commission) AS actual_profit,gg.goods_id
                FROM stat_daily_sales_spec_warehouse  ssps
                LEFT JOIN goods_spec gs ON (gs.spec_id = ssps.spec_id)
                LEFT JOIN cfg_warehouse cw ON (cw.warehouse_id=ssps.warehouse_id)
                LEFT JOIN goods_goods gg ON (gg.goods_id = gs.goods_id)
                LEFT JOIN goods_brand gb ON (gb.brand_id = gg.brand_id)
                LEFT JOIN goods_class gc ON (gc.class_id = gg.class_id)"
                . $left_join_goods_class_str . "
                {$where} GROUP BY ssps.spec_id,ssps.warehouse_id{$having}
                ORDER BY {$sort}";
        }elseif($search['stat_type'] == 3){
            if(!empty($id_list['id'])){
                $having = ' having sto.spec_id in ('.$id_list['id'].') and tmp.shop_id in ('.$id_list['s_id'].') ';
            }
            $where = str_replace('ssps.sales_date','stl.created',$where);
            $where = str_replace('ssps.shop_id','st.shop_id',$where);
            $where = str_replace('ssps.warehouse_id','st.warehouse_id',$where);
            $tmp_sql = "SELECT  sto.rec_id,st.shop_id FROM sales_trade st INNER JOIN sales_trade_log stl USE INDEX(IX_sales_trade_log) ON stl.trade_id=st.trade_id
                    LEFT JOIN sales_trade_order sto ON st.trade_id = sto.trade_id
                    LEFT JOIN goods_spec gs ON gs.spec_id = sto.spec_id
                    LEFT JOIN goods_suite gse ON sto.suite_id =gse.suite_id
                    LEFT JOIN goods_goods gg ON gg.goods_id = gs.goods_id
                    LEFT JOIN goods_class gc ON gc.class_id = gg.class_id
                    LEFT JOIN cfg_warehouse cw ON cw.warehouse_id = st.warehouse_id
                    {$where} AND stl.type=2 AND stl.data<>-50 AND cw.type <> 127";
            $sql = "SELECT tmp.shop_id,cs.shop_name AS shop_id,gs.spec_no,gs.retail_price,gg.goods_no,gg.goods_name,gg.short_name,gg.brand_id,gb.brand_name AS brand_id,gg.class_id,gc.class_name AS class_id,gs.spec_code,gs.spec_name,
                    cast((if(SUM(sto.actual_num)=0,0,SUM(IF(sto.num>0 && sto.actual_num=0,0,sto.share_amount))/SUM(sto.actual_num))) as decimal(19,4)) AS avg_price,SUM(sto.num) AS order_num,SUM(sto.actual_num) AS actual_num,
                    SUM(sto.share_amount) AS amount,SUM(IF(sto.num>0 && sto.actual_num=0,0,sto.share_amount)) actual_amount,SUM(IF(sto.num>0 && sto.actual_num=0,0,sto.share_post)) as post_amount,
                    IFNULL(cast((if(SUM(sto.actual_num)=0,0,SUM(sto.share_amount)/SUM(sto.actual_num))/gs.retail_price) as decimal(19,2)),\"\") AS discount_rate,gg.goods_id
                    FROM ($tmp_sql ) tmp
                    LEFT JOIN sales_trade_order sto  ON sto.rec_id = tmp.rec_id
                    LEFT JOIN cfg_shop cs ON tmp.shop_id = cs.shop_id
                    LEFT JOIN goods_spec gs ON gs.spec_id = sto.`spec_id`
                    LEFT JOIN goods_goods gg ON gg.`goods_id` = gs.`goods_id`
                    LEFT JOIN goods_brand gb ON gb.brand_id = gg.brand_id
                    LEFT JOIN goods_class gc ON gc.class_id = gg.class_id
                    GROUP BY sto.spec_id,tmp.shop_id{$having} ORDER BY sto.spec_id ASC
                    ";

        }else{
            if(!empty($id_list['id'])){
                $having = ' having ssps.spec_id in ('.$id_list['id'].') and ssps.shop_id in ('.$id_list['s_id'].') ';
            }
            $sql = "SELECT
                ssps.rec_id AS id,ssps.shop_id,cs.shop_name AS shop_id,gs.spec_no,gg.goods_no,gg.goods_name,gs.retail_price,gg.brand_id,gb.brand_name AS brand_id,gg.class_id,gc.class_name AS class_id,gs.spec_code,gs.spec_name,gg.short_name,
                cast((if(SUM(ssps.num-ssps.refund_num)=0,0,SUM(ssps.amount-ssps.refund_amount)/SUM(ssps.num-ssps.refund_num))) as decimal(19,4)) AS avg_price,SUM(ssps.num) AS num,
                SUM(ssps.return_num) AS return_num,SUM(ssps.refund_num) AS refund_num,SUM(ssps.amount) AS amount,SUM(ssps.refund_amount) AS refund_amount,
                SUM(ssps.unknown_goods_amount) AS unknown_goods_amount,SUM(ssps.goods_cost) AS goods_cost,SUM(ssps.post_amount) AS post_amount,SUM(ssps.guarante_refund_amount) AS guarante_refund_amount,
                SUM(ssps.amount-ssps.goods_cost-ssps.unknown_goods_amount-ssps.commission) AS profit,SUM(ssps.commission) AS commission,SUM(ssps.post_amount) AS post_amount,
                SUM(ssps.return_amount) AS return_amount,SUM(ssps.return_cost) AS return_cost,IFNULL(cast((if(SUM(ssps.num)=0,0,SUM(ssps.amount)/SUM(ssps.num))/gs.retail_price) as decimal(19,2)),'') AS discount_rate,
                SUM(ssps.num-ssps.refund_num-ssps.return_num) AS actual_num,SUM(ssps.amount-ssps.refund_amount-ssps.return_amount) AS actual_amount,
                SUM(ssps.goods_cost-ssps.return_cost) AS actual_goods_cost,SUM(ssps.gift_num) as gift_num,SUM(ssps.swap_num) as swap_num,
                SUM(ssps.amount+ssps.return_cost-ssps.refund_amount-ssps.goods_cost-ssps.return_amount-ssps.unknown_goods_amount-ssps.commission) AS actual_profit,gg.goods_id
                FROM stat_daily_sales_spec_shop  ssps
                LEFT JOIN goods_spec gs ON (gs.spec_id = ssps.spec_id)
                LEFT JOIN cfg_shop cs ON (cs.shop_id=ssps.shop_id)
                LEFT JOIN goods_goods gg ON (gg.goods_id = gs.goods_id)
                LEFT JOIN goods_brand gb ON (gb.brand_id = gg.brand_id)
                LEFT JOIN goods_class gc ON (gc.class_id = gg.class_id)"
                . $left_join_goods_class_str ."
                {$where} GROUP BY ssps.spec_id,ssps.shop_id{$having}
                ORDER BY {$sort}";
        }
        try{

            $data['rows'] = $this->query($sql);

            if($search['stat_type'] == 2) {
                $field_type = 'sales_amount_goods_spec_by_warehouse';
            }elseif($search['stat_type'] == 3){
                $field_type = 'sales_amount_goods_spec_by_pay_time';
            }else{
                $field_type = 'sales_amount_goods_spec';
            }
            $excel_header = D('Setting/UserData')->getExcelField('Statistics/SalesAmountGoodsSpec',$field_type);
            $excel_header_list = $excel_header;

            $merge_arr = array();
            $count_arr = array();
            if($type=='goods')
            {
                $repeat =array();
                //根据哪一个字段进行合并，计算每次合并行的数量。
                foreach($data['rows'] as $k=>$v)
                {
                    $goods_no = $v['goods_no'];
                    if(!in_array($goods_no,$repeat))
                    {
                        $repeat[] = $goods_no;
                        $merge_arr['goods_no'][$goods_no] = 1;
                    }else
                    {
                        $merge_arr['goods_no'][$goods_no]= $merge_arr['goods_no'][$goods_no]+1;
                    }

                }
                foreach($merge_arr['goods_no'] as $v)
                {
                    $merge_num[] = $v;
                }
                $merge_arr['goods_name'] = $merge_num;
                $merge_arr['short_name'] = $merge_num;
                $merge_arr['goods_no'] = $merge_num;
               if($search['stat_type']==2 || $search['stat_type']==1)
               {
                   $count_header_arr = array('num','amount','refund_num','refund_amount','return_num');
                   $count_arr['num_total'] = $merge_num;          //求和项  发货总量
                   $count_arr['amount_total'] = $merge_num;       //求和项  发货总金额
                   $count_arr['refund_num_total'] = $merge_num;   //求和项  退款总量
                   $count_arr['refund_amount_total'] = $merge_num;//求和项  退款总金额
                   $count_arr['return_num_total'] = $merge_num;   //求和项  换货总量
                   foreach($count_header_arr as $k=>$v)
                   {
                       $insert_data = array($v.'_total'=>'合计');
                       $offset = array_search($v,array_keys($excel_header_list))+1;
                       $first = array_splice($excel_header_list,0,$offset);
                       $excel_header_list = array_merge($first,$insert_data,$excel_header_list);
                   }

               }else{
                   $count_header_arr = array('order_num','actual_num','post_amount');
                   $count_arr['order_num_total'] = $merge_num;    //求和项   下单量
                   $count_arr['actual_num_total'] = $merge_num;   //求和项   实际销量
                   $count_arr['post_amount_total'] = $merge_num;  //求和项   邮资收入
                   foreach($count_header_arr as $k=>$v)
                   {
                       $insert_data = array($v.'_total'=>'合计');
                       $offset = array_search($v,array_keys($excel_header_list))+1;
                       $first = array_splice($excel_header_list,0,$offset);
                       $excel_header_list = array_merge($first,$insert_data,$excel_header_list);
                   }
               }
            }


            $width_list = array();
            foreach ($excel_header_list as $v)
            {
                $width_list[]=20;
            }
            $title = '单品销售统计';
            $filename = '单品销售统计';

            ExcelTool::Arr2Excel($data['rows'],$title,$excel_header_list,$width_list,$filename,$creator,$merge_arr,$count_arr);

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