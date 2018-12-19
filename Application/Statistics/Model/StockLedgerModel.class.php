<?php
namespace Statistics\Model;
use Think\Log;
use Think\Model;
use Common\Common\ExcelTool;

class StockLedgerModel extends Model{
    protected $tableName = 'stock_ledger';
    protected $pk = 'rec_id' ;

    public function searchFormDeal(&$where,$search){
        foreach($search as $k=>$v){
            if (!isset($v)) continue;
            switch($k){
                case 'warehouse_id':
                    set_search_form_value($where,$k,$v,'tsa',2,'AND');
                    break;
                case 'spec_no':
                    set_search_form_value($where,$k,$v,'gs',1,'AND');
                    break;
                case 'goods_name':
                    set_search_form_value($where,$k,$v,'gg',1,'AND');
                    break;
                case 'short_name':
                    set_search_form_value($where,$k,$v,'gg',1,'AND');
                    break;
                case 'goods_no':
                    set_search_form_value($where,$k,$v,'gg',1,'AND');
                    break;
                case 'class_id':
                    set_search_form_value($where,$k,$v,'gc',7,'AND');
                    break;
                case 'brand_id':
                    set_search_form_value($where,$k,$v,'gb',2,'AND');
                    break;
                case 'day_start':
                    set_search_form_value($where, 'created', $v,'sl', 3,' AND ',' >= ');
                    break;
                case 'day_end':
                    set_search_form_value($where, 'created', $v,'sl', 3,' AND ',' < ');
            }
        }
    }

    public function loadDataByCondition($page, $rows, $search, $sort, $order){
        $data=array();
        if($search['warehouse_id']==0)unset($search['warehouse_id']);
        $time_start = date('Y-m-d',strtotime('-3 month'));
        $time_end = date('Y-m-d',time());

        //组织临时表时间区间的where
        $search_where=' where true ';
        $get_data_search=array(
            'day_start'=>$search['day_start'],
            'day_end'=>$search['day_end']
        );
        if($get_data_search['day_start']<$time_start||$get_data_search['day_start']>$time_end||$get_data_search['day_end']<$time_start||$get_data_search['day_end']>$time_end){
            return array("total" => 0,"rows" => array());
        }
        $this->searchFormDeal($search_where,$get_data_search);

        //组织查询条件where
        $where = ' where true ';
        unset($search['day_start']);
        unset($search['day_end']);
        $this->searchFormDeal($where,$search);

        $page  = intval($page);
        $rows  = intval($rows);
        $limit = ($page - 1) * $rows . "," . $rows;//分页
        $sort  = 'tsa.'.$sort . " " . $order;
        $sort  = addslashes($sort);
        try{
            //创建临时表存储数据
            $tmp_stock_acclist_sql = "CREATE TEMPORARY TABLE IF NOT EXISTS tmp_stock_acclist (
                    rec_id INT(11) NOT NULL AUTO_INCREMENT,
                    spec_id INT(11) DEFAULT NULL,
                    warehouse_id INT(11) DEFAULT NULL,
                    begin_stock DECIMAL(19,4) DEFAULT '0.0000' COMMENT '期初节余',
                    begin_cost DECIMAL(19,4) DEFAULT '0.0000' COMMENT '期初成本',
                    begin_costtotal DECIMAL(19,4) DEFAULT '0.0000' COMMENT '期初节余总额',
                    end_stock DECIMAL(19,4) DEFAULT '0.0000' COMMENT '期末节余',
                    end_cost DECIMAL(19,4) DEFAULT '0.0000' COMMENT '期末成本',
                    end_costtotal DECIMAL(19,4) DEFAULT '0.0000' COMMENT '期末节余总额',
                    stockout_count DECIMAL(19,4) DEFAULT '0.0000' COMMENT '出库总数',
                    stockout_cost DECIMAL(19,4) DEFAULT '0.0000' COMMENT '出库总成本',
                    stockout_money DECIMAL(19,4) DEFAULT '0.0000' COMMENT '出库总金额',
                    stockin_count DECIMAL(19,4) DEFAULT '0.0000' COMMENT '入库总数',
                    stockin_money DECIMAL(19,4) DEFAULT '0.0000' COMMENT '入库总金额',
                    sell_count DECIMAL(19,4) DEFAULT '0.0000' COMMENT '销售出库数量',
                    sell_cost DECIMAL(19,4) DEFAULT '0.0000' COMMENT '销售出库成本',
                    sell_money DECIMAL(19,4) DEFAULT '0.0000' COMMENT '销售出库总额',
                    tra_out_count DECIMAL(19,4) DEFAULT '0.0000' COMMENT '调拨出库数量',
                    tra_out_cost DECIMAL(19,4) DEFAULT '0.0000' COMMENT '调拨出库成本',
                    tra_out_money DECIMAL(19,4) DEFAULT '0.0000' COMMENT '调拨出库总额',
                    pd_out_count DECIMAL(19,4) DEFAULT '0.0000' COMMENT '盘点出库数量',
                    pd_out_cost DECIMAL(19,4) DEFAULT '0.0000' COMMENT '盘点出库成本',
                    pd_out_money DECIMAL(19,4) DEFAULT '0.0000' COMMENT '盘点出库总额',
                    man_out_count DECIMAL(19,4) DEFAULT '0.0000' COMMENT '其它出库数量',
                    man_out_cost DECIMAL(19,4) DEFAULT '0.0000' COMMENT '其它出库成本',
                    man_out_money DECIMAL(19,4) DEFAULT '0.0000' COMMENT '其它出库总额',
                    int_out_count DECIMAL(19,4) DEFAULT '0.0000' COMMENT '初始化出库数量',
                    int_out_cost DECIMAL(19,4) DEFAULT '0.0000' COMMENT '初始化出库成本',
                    int_out_money DECIMAL(19,4) DEFAULT '0.0000' COMMENT '初始化出库总额',
                    pur_in_count DECIMAL(19,4) DEFAULT '0.0000' COMMENT '采购入库数量',
                    pur_in_money DECIMAL(19,4) DEFAULT '0.0000' COMMENT '采购入库总额',
                    tra_in_count DECIMAL(19,4) DEFAULT '0.0000' COMMENT '调拨入库数量',
                    tra_in_money DECIMAL(19,4) DEFAULT '0.0000' COMMENT '调拨入库总额',
                    sellback_count DECIMAL(19,4) DEFAULT '0.0000' COMMENT '销售退货数量',
                    sellback_money DECIMAL(19,4) DEFAULT '0.0000' COMMENT '销售退货总额',
                    pd_in_count DECIMAL(19,4) DEFAULT '0.0000' COMMENT '盘点入库数量',
                    pd_in_money DECIMAL(19,4) DEFAULT '0.0000' COMMENT '盘点入库总额',
                    man_in_count DECIMAL(19,4) DEFAULT '0.0000' COMMENT '其它入库数量',
                    man_in_money DECIMAL(19,4) DEFAULT '0.0000' COMMENT '其它入库总额',
                    int_in_count DECIMAL(19,4) DEFAULT '0.0000' COMMENT '初始化入库数量',
                    int_in_money DECIMAL(19,4) DEFAULT '0.0000' COMMENT '初始化入库总额',
                    PRIMARY KEY (rec_id),
                    UNIQUE KEY IDX_SW (spec_id,warehouse_id)
            );";
            $this->execute($tmp_stock_acclist_sql);
            $this->execute("DELETE FROM tmp_stock_acclist");

            //创建临时表查询期初期末id
            $tmp_stock_acctime_sql = "CREATE TEMPORARY TABLE IF NOT EXISTS tmp_stock_acctime(spec_id INT(11),warehouse_id INT(11),min_rec_id INT(11),max_rec_id INT(11),UNIQUE INDEX(spec_id, warehouse_id));";
            $this->execute($tmp_stock_acctime_sql);
            $this->execute("DELETE FROM tmp_stock_acctime");

            //查询数据存入临时表
            $get_data_sql="INSERT INTO tmp_stock_acclist( spec_id,warehouse_id,stockout_count,stockout_cost,stockout_money,stockin_count,stockin_money,
				sell_count,sell_cost,sell_money,tra_out_count,tra_out_cost,tra_out_money,pd_out_count,pd_out_cost,pd_out_money,man_out_count,man_out_cost,man_out_money,int_out_count,int_out_cost,
				int_out_money,pur_in_count,pur_in_money,tra_in_count,tra_in_money,sellback_count,sellback_money,pd_in_count,pd_in_money,man_in_count,man_in_money,int_in_count,int_in_money)
		        SELECT spec_id,warehouse_id,
		           SUM(IF(`type`=2 AND src_order_type<70 ,num,0)),
                   SUM(IF(`type`=2 AND src_order_type<70,cost,0)),
                   SUM(IF(`type`=2 AND src_order_type<70,money,0)),
                   SUM(IF(`type`=1,num,0)),
                   SUM(IF(`type`=1,cost,0)),
                   SUM(IF(`type`=2 AND src_order_type=1,num,0)),
                   SUM(IF(`type`=2 AND src_order_type=1,cost,0)),
                   SUM(IF(`type`=2 AND src_order_type=1,money,0)),
                   SUM(IF(`type`=2 AND src_order_type=2,num,0)),
                   SUM(IF(`type`=2 AND src_order_type=2,cost,0)),
                   SUM(IF(`type`=2 AND src_order_type=2,money,0)),
                   SUM(IF(`type`=2 AND src_order_type=4,num,0)),
                   SUM(IF(`type`=2 AND src_order_type=4,cost,0)),
                   SUM(IF(`type`=2 AND src_order_type=4,money,0)),
                   SUM(IF(`type`=2 AND src_order_type=7,num,0)),
                   SUM(IF(`type`=2 AND src_order_type=7,cost,0)),
                   SUM(IF(`type`=2 AND src_order_type=7,money,0)),
                   SUM(IF(`type`=2 AND src_order_type=11,num,0)),
                   SUM(IF(`type`=2 AND src_order_type=11,cost,0)),
                   SUM(IF(`type`=2 AND src_order_type=11,money,0)),
                   SUM(IF(`type`=1 AND src_order_type=1,num,0)),
                   SUM(IF(`type`=1 AND src_order_type=1,cost,0)),
                   SUM(IF(`type`=1 AND src_order_type=2,num,0)),
                   SUM(IF(`type`=1 AND src_order_type=2,cost,0)),
                   SUM(IF(`type`=1 AND src_order_type=3,num,0)),
                   SUM(IF(`type`=1 AND src_order_type=3,cost,0)),
                   SUM(IF(`type`=1 AND src_order_type=4,num,0)),
                   SUM(IF(`type`=1 AND src_order_type=4,cost,0)),
                   SUM(IF(`type`=1 AND src_order_type=6,num,0)),
                   SUM(IF(`type`=1 AND src_order_type=6,cost,0)),
                   SUM(IF(`type`=1 AND src_order_type=9,num,0)),
                   SUM(IF(`type`=1 AND src_order_type=9,cost,0))
                   FROM stock_ledger sl {$search_where} GROUP BY spec_id,warehouse_id ";
            $this->execute($get_data_sql);

            $stock_acctime_sql="INSERT INTO tmp_stock_acctime(spec_id,warehouse_id,min_rec_id,max_rec_id) SELECT
		    spec_id,warehouse_id,MIN(rec_id),MAX(rec_id) FROM stock_change_history sl {$search_where} GROUP BY spec_id,warehouse_id";
            $this->execute($stock_acctime_sql);

            //获取期初成本等数据
            $begin_sql="INSERT INTO tmp_stock_acclist(spec_id, warehouse_id, begin_stock,begin_cost, begin_costtotal)
		    SELECT sat.spec_id,sat.warehouse_id,sch.stock_num_old as begin_stock,sch.cost_price_old as begin_cost,IF(sch.cost_price_old*sch.stock_num_old>999999999999999.9999,999999999999999.9999,sch.cost_price_old*sch.stock_num_old)
		    FROM tmp_stock_acctime sat LEFT JOIN stock_change_history sch ON sch.rec_id=sat.min_rec_id
		    ON DUPLICATE KEY UPDATE begin_stock=VALUES(begin_stock), begin_cost=VALUES(begin_cost), begin_costtotal=VALUES(begin_costtotal);";
            $this->execute($begin_sql);

            //获取期末成本等数据
            $end_sql="INSERT INTO tmp_stock_acclist(spec_id, warehouse_id, end_stock,end_cost, end_costtotal)
		    SELECT sat.spec_id,sat.warehouse_id,sch.stock_num_new as end_stock,sch.cost_price_new as end_cost,IF(sch.cost_price_new*sch.stock_num_new>999999999999999.9999,999999999999999.9999,sch.cost_price_new*sch.stock_num_new)
		    FROM tmp_stock_acctime sat LEFT JOIN stock_change_history sch ON sch.rec_id=sat.max_rec_id
		    ON DUPLICATE KEY UPDATE end_stock=VALUES(end_stock), end_cost=VALUES(end_cost), end_costtotal=VALUES(end_costtotal);";
            $this->execute($end_sql);

            $sql="select tsa.*,cw.name as warehouse_name,gg.short_name,gg.goods_name,gg.goods_no,gs.spec_no,gs.spec_name,gs.spec_code,gc.class_name as class_id,gb.brand_name as brand_id
                  from tmp_stock_acclist tsa
                  left join goods_spec gs on gs.spec_id=tsa.spec_id
                  left join goods_goods gg on gg.goods_id=gs.goods_id
                  left join goods_class gc on gc.class_id=gg.class_id
                  left join goods_brand gb on gb.brand_id=gg.brand_id
                  left join cfg_warehouse cw on cw.warehouse_id=tsa.warehouse_id {$where} group by tsa.spec_id,tsa.warehouse_id ORDER BY {$sort} LIMIT {$limit};";
            $count_sql="select count(1) as count
                  from tmp_stock_acclist tsa
                  left join goods_spec gs on gs.spec_id=tsa.spec_id
                  left join goods_goods gg on gg.goods_id=gs.goods_id
                  left join goods_class gc on gc.class_id=gg.class_id
                  left join goods_brand gb on gb.brand_id=gg.brand_id
                  left join cfg_warehouse cw on cw.warehouse_id=tsa.warehouse_id {$where} group by tsa.spec_id,tsa.warehouse_id";
            $data['rows'] = $this->query($sql);
            $count = $this->query($count_sql);
            $data['total'] =count($count);
            //删除临时表数据
            $this->execute("DELETE FROM tmp_stock_acclist");
            $this->execute("DELETE FROM tmp_stock_acctime");
            //删除临时表
            $this->execute("DROP TEMPORARY TABLE tmp_stock_acclist");
            $this->execute("DROP TEMPORARY TABLE tmp_stock_acctime");
        }catch (\PDOException $e){
            Log::write($e->getMessage());
            $data = array("total" => 0,"rows" => array());
        }catch (\Exception $e){
            Log::write($e->getMessage());
            $data = array("total" => 0,"rows" => array());
        }
        return $data;
    }

    //查询时间范围内的出入库详情
    public function getLedgerDetial($page, $rows, $search, $sort, $order){
        $endtime=date('Y-m-d'). " 00:00:00";
        $where=" where sod_1.modified < '{$endtime}' ";
        $page  = intval($page);
        $rows  = intval($rows);
        $limit = ($page - 1) * $rows . "," . $rows;//分页
        $sort  = 'temp1.'.$sort . " " . $order;
        $sort  = addslashes($sort);
        if($search['warehouse_id']==0)unset($search['warehouse_id']);
        foreach($search as $k=>$v){
            if (!isset($v)) continue;
            switch($k){
                case 'spec_id':
                    set_search_form_value($where,$k,$v,'sod_1',2,'AND');
                    break;
                case 'warehouse_id':
                    set_search_form_value($where,$k,$v,'so_1',2,'AND');
                    break;
                case 'day_start':
                    set_search_form_value($where, 'modified', $v,'sod_1', 3,' AND ',' >= ');
                    break;
                case 'day_end':
                    set_search_form_value($where, 'modified', $v,'sod_1', 3,' AND ',' < ');
            }
        }
        try{
            //创建整理数据的临时表
            $temp_table_sql = "CREATE TEMPORARY TABLE IF NOT EXISTS temp_table(
                                      rec_id int(11) NOT NULL auto_increment,
                                      spec_id INT(11),
                                      warehouse_id INT(11),
                                      src_order_type INT(11),
                                      num INT(11),
                                      modified timestamp NOT NULL,
                                      type INT(11),
                                      remark VARCHAR(512) NOT NULL DEFAULT '',
                                      total_cost DECIMAL(19,4) DEFAULT '0.0000',
                                      price DECIMAL(19,4) DEFAULT '0.0000',
                                      stock_num_old DECIMAL(19,4) DEFAULT '0.0000',
                                      stock_num_new DECIMAL(19,4) DEFAULT '0.0000',
                                      cost_price_old DECIMAL(19,4) DEFAULT '0.0000',
                                      cost_price_new DECIMAL(19,4) DEFAULT '0.0000',
                                      stock_no varchar(40) NOT NULL DEFAULT '',
                                      src_order_no varchar(40) NOT NULL DEFAULT '',
                                      operator_id INT(11),
                                      PRIMARY KEY (rec_id));";
            $this->execute($temp_table_sql);
            $this->execute("DELETE FROM temp_table");

            $old=strtotime('-3month');
            $start=strtotime($search['day_start']);

            //获取到查询条件对应的id
            $in_new_ids=" select sod_1.rec_id from stockin_order_detail sod_1 left join stockin_order so_1 on sod_1.stockin_id=so_1.stockin_id {$where} and so_1.status=80 ";
            $out_new_ids="select sod_1.rec_id from stockout_order_detail sod_1 left join stockout_order so_1 on sod_1.stockout_id=so_1.stockout_id {$where} and so_1.status>=95 ";
            $out_old_ids="select sod_1.rec_id from stockout_order_detail_history sod_1 left join stockout_order_history so_1 on sod_1.stockout_id=so_1.stockout_id {$where} and so_1.status>=95 ";

            //出入库字段
            $in_field="sod.spec_id,so.warehouse_id,so.src_order_type,sod.num,sod.modified,sod.remark,sod.total_cost,1 as type,so.stockin_no as stock_no,so.src_order_no,so.operator_id,sch.cost_price_old,sch.stock_num_old,sch.cost_price_new,sch.stock_num_new,sch.price";
            $out_field="sod.spec_id,so.warehouse_id,so.src_order_type,sod.num,sod.modified,sod.remark,sod.total_amount,2 as type,so.stockout_no as stock_no,so.src_order_no,so.operator_id,sch.cost_price_old,sch.stock_num_old,sch.cost_price_new,sch.stock_num_new,sch.price";

            // 组织获取数据的sql  type: 1入库 2出库 0全部
            if($search['type']==1){
               $sql=" select $in_field  from stockin_order_detail sod JOIN ($in_new_ids) siod_l ON siod_l.rec_id = sod.rec_id left join stockin_order so on sod.stockin_id=so.stockin_id left join stock_change_history sch on sch.spec_id=sod.spec_id and sch.stockio_id=so.stockin_id ";
            }else if($search['type']==2){
                if($start>$old){
                     $sql="select $out_field from stockout_order_detail sod JOIN ($out_new_ids) siod_l ON siod_l.rec_id = sod.rec_id left join stockout_order so on sod.stockout_id=so.stockout_id  left join stock_change_history sch on sch.spec_id=sod.spec_id and sch.stockio_id=so.stockout_id";
                }else{
                   $sql="(select $out_field from stockout_order_detail sod JOIN ($out_new_ids) siod_l ON siod_l.rec_id = sod.rec_id left join stockout_order so on sod.stockout_id=so.stockout_id  left join stock_change_history sch on sch.spec_id=sod.spec_id and sch.stockio_id=so.stockout_id)
                       union all
                       (select $out_field from stockout_order_detail_history sod JOIN ($out_old_ids) siod_l ON siod_l.rec_id = sod.rec_id left join stockout_order_history so on sod.stockout_id=so.stockout_id  left join stock_change_history sch on sch.spec_id=sod.spec_id and sch.stockio_id=so.stockout_id)";
                }
            }else{
               $sql="(select $in_field from stockin_order_detail sod JOIN ($in_new_ids) siod_l ON siod_l.rec_id = sod.rec_id left join stockin_order so on sod.stockin_id=so.stockin_id  left join stock_change_history sch on sch.spec_id=sod.spec_id and sch.stockio_id=so.stockin_id)
                   union all
                   (select $out_field from stockout_order_detail sod JOIN ($out_new_ids) siod_2 ON siod_2.rec_id = sod.rec_id left join stockout_order so on sod.stockout_id=so.stockout_id  left join stock_change_history sch on sch.spec_id=sod.spec_id and sch.stockio_id=so.stockout_id)";
                if($start<$old){
                    $sql.=" union all (select $out_field from stockout_order_detail_history sod JOIN ($out_old_ids) siod_l ON siod_l.rec_id = sod.rec_id left join stockout_order_history so on sod.stockout_id=so.stockout_id  left join stock_change_history sch on sch.spec_id=sod.spec_id and sch.stockio_id=so.stockout_id)";
                }
            }
            $insert_sql="INSERT INTO temp_table(spec_id,warehouse_id,src_order_type,num,modified,remark,total_cost,type,stock_no,src_order_no,operator_id,cost_price_old,stock_num_old,cost_price_new,stock_num_new,price){$sql}";
            $this->execute($insert_sql);

            //查询表整理数据
            $end_sql="select temp1.*,he.fullname as operator_id from temp_table temp1 left join hr_employee he on he.employee_id = temp1.operator_id  ORDER BY {$sort} LIMIT {$limit};";
            $count_sql="select rec_id from temp_table";
            $data['rows'] = $this->query($end_sql);
            $get_ids=$this->query($count_sql);
            $data['total']=count($get_ids);

            //删除临时表数据
            $this->execute("DELETE FROM temp_table");
            //删除临时表
            $this->execute("DROP TEMPORARY TABLE temp_table");

        }catch (\PDOException $e){
            Log::write($e->getMessage());
            $data = array("total" => 0,"rows" => array());
        }catch (\Exception $e){
            Log::write($e->getMessage());
            $data = array("total" => 0,"rows" => array());
        }
        return $data;
    }

}