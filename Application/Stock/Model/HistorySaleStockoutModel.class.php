<?php


namespace Stock\Model;

use Think\Exception;
use Think\Log;
use Think\Model;
use Think\Exception\BusinessLogicException;
/**
 * @package Stock\Model
 */
class HistorySaleStockoutModel extends Model {
    protected $tableName = "stockout_order_history";
    protected $pk        = "stockout_id";

    public function getHistorySaleStockout($page = 1, $rows = 20, $search = array(), $sort = 'so.stockout_id', $order = 'desc') {
        try {
            //设置店铺权限
			D('Setting/EmployeeRights')->setSearchRights($search,'warehouse_id',2);
            D('Setting/EmployeeRights')->setSearchRights($search,'shop_id',1);
            $where_stockout_order = array();
            $where_sales_trade_order = array();
            $where_sales_trade = array();
            foreach ($search as $k => $v) {
                if ($v === '') continue;
                switch ($k) {
                    case 'stockout_no':{//出库单号  stockout_order
                        set_search_form_value($where_stockout_order, $k, $v, 'so' );
                        break;
                    }
                    case 'src_order_no':{//原始单号 stockout_order
                        set_search_form_value($where_stockout_order, $k, $v,'so');
                        break;
                    }
                    case 'warehouse_id'://仓库类型
                        set_search_form_value($where_stockout_order, $k, $v, 'so', 2);
                        break;
                    case 'logistics_id':{//物流公司 stockout_order
                        set_search_form_value($where_stockout_order, $k, $v, 'so', 2);
                        break;
                    }
                    case 'receiver_mobile':{//收件人手机 stockout_order
                        set_search_form_value($where_stockout_order, $k, $v, 'so');
                        break;
                    }
                    case 'shop_id':{//店铺 sales_trade
                        set_search_form_value($where_sales_trade, $k, $v, 'st',2);
                        break;
                    }
                    case 'buyer_nick':{//客户网名 sales_trade
                        set_search_form_value($where_sales_trade, $k, $v, 'st',6);
                        break;
                    }
                    case 'spec_no':{//商家编码
                        set_search_form_value($where_sales_trade_order, $k, $v, 'sto');
                        break;
                    }
                    case 'consign_time_start':
                        set_search_form_value($where_stockout_order['_string'], 'consign_time', $v,'so', 4,' AND ',' >= ');
                        break;
                    case 'consign_time_end':
                        set_search_form_value($where_stockout_order['_string'], 'consign_time', $v,'so', 4,' AND ',' <= ');
                        break;
                    default:
                        \Think\Log::write("unknown field:" . print_r($k, true) . ",value:" . print_r($v, true));
                        break;
                }
            }
            $page = intval($page);
            $rows = intval($rows);
            $limit=($page - 1) * $rows . "," . $rows;
            $order = $sort.' '.$order;
            $order = addslashes($order);
            $m=D('StockoutOrderHistory');
            $m->alias('so');
            if(isset($where_stockout_order['_string'])) {
                $where_stockout_order['_string'] = trim($where_stockout_order['_string'], ' AND');
            }
            $where_stockout_order['so.src_order_type'] = array('eq',1);
            if(!empty($where_sales_trade))
            {
                $m = $m->join('sales_trade_history st on  so.src_order_id = st.trade_id')->where($where_sales_trade);
            }
            if(!empty($where_sales_trade_order))
            {
                $m = $m->join('sales_trade_order_history sto on so.src_order_id = sto.trade_id')->where($where_sales_trade_order);
            }
            $m = $m->where($where_stockout_order);
            $page = clone $m;
            $sql_page = $page->field('so.stockout_id id')->order($order)->group('so.stockout_id')->limit($limit)->fetchSql(true)->select();
            $cfg_show_telno=get_config_value('show_number_to_star',1);
            $point_number = get_config_value('point_number',0);
			$field_right = D('Setting/EmployeeRights')->getFieldsRight('soh.');
            $goods_count = 'CAST(sum(soh.goods_count) AS DECIMAL(19,'.$point_number.')) goods_count';
            $sql = 'select soh.src_order_id,soh.error_info,soh.logistics_print_status,soh.sendbill_print_status,soh.receiver_province,soh.receiver_city,soh.receiver_district,'
                .'soh.logistics_id,'.$field_right['goods_total_cost'].',soh.printer_id,soh.watcher_id,soh.logistics_print_status,soh.sendbill_print_status,soh.batch_no,soh.picklist_no,'//,so.outer_no
                .'soh.stockout_id id,soh.src_order_no,soh.stockout_no,soh.src_order_type,soh.status,soh.consign_status,'.$goods_count.',soh.goods_type_count,'//so.picker_id,so.examiner_id,
                .'soh.receiver_address,soh.receiver_name,soh.receiver_area,IF('.$cfg_show_telno.'=0,soh.receiver_mobile,INSERT( soh.receiver_mobile,4,4,\'****\')) receiver_mobile,IF('.$cfg_show_telno.'=0,soh.receiver_telno,INSERT(soh.receiver_telno,4,4,\'****\')) receiver_telno,soh.receiver_zip,soh.calc_post_cost,soh.post_cost,soh.calc_weight,soh.weight,'//
                .'soh.has_invoice,soh.logistics_no,soh.consign_time,soh.block_reason,'//so.flag_id,
                .'cl.logistics_name,cl.bill_type,cl.logistics_type, cl.logistics_id,'
                .'st.warehouse_id,cw.type warehouse_type,st.checker_id,st.shop_id,st.src_tids,st.buyer_message,st.receivable,st.salesman_id,st.trade_time,st.pay_time,st.buyer_nick,st.trade_type,st.platform_id,st.paid,'
                .'he.fullname as checker_name,'
                .'cw.contact,cw.mobile,cw.telno,cw.province,cw.city,cw.district,cw.address,cw.zip,cw.name warehouse_name,'
                .'cs.shop_name,cs.website,'
                .'IF(gs.spec_no IS NULL,IFNULL(gst.suite_name,\'多种货品\'),CONCAT(gg.goods_name,\'-\',gs.spec_name)) goods_abstract '
                . 'FROM stockout_order_history soh '
                . 'JOIN sales_trade_history st ON soh.src_order_id = st.trade_id '
                . 'JOIN cfg_shop cs ON cs.shop_id = st.shop_id '
                . 'LEFT JOIN goods_spec gs ON gs.spec_no = soh.single_spec_no and gs.deleted = 0 '
                . 'LEFT JOIN goods_goods gg ON gg.goods_id = gs.goods_id '
                . 'LEFT JOIN goods_suite gst ON gst.suite_no = soh.single_spec_no and gst.deleted = 0 '
                . 'LEFT JOIN cfg_logistics cl ON cl.logistics_id = soh.logistics_id '
                . 'LEFT JOIN cfg_warehouse cw ON cw.warehouse_id = soh.warehouse_id '
                . 'LEFT JOIN stock_logistics_no sln ON sln.logistics_id = soh.logistics_id and sln.logistics_no = soh.logistics_no '
                . 'LEFT JOIN hr_employee he ON he.employee_id = st.checker_id '
                . 'JOIN (' . $sql_page . ') page ON page.id = soh.stockout_id'
                . ' group by soh.stockout_id order by soh.'.$order;
            $result         = $this->query($sql);
            $sql_total = $m->fetchSql(true)->count('distinct so.stockout_id');
            $total=$m->query($sql_total);
            $total = $total[0]['tp_count'];
            $data           = array();
            $data['rows']   = $result;
            $data['total']  = $total;
        } catch (\Exception $e) {
            Log::write($e->getMessage());
            $data["rows"]  = "";
            $data["total"] = 0;
        }
        return $data;
    }

    /*
     * 获取到tab中出库单详情的数据
     * */
    public function getStockoutOrderList($rec_id) {
        try {
            $point_number = get_config_value('point_number',0);
            $num = 'CAST(sto.num AS DECIMAL(19,'.$point_number.')) num';
            $suite_num = 'CAST(sto.suite_num AS DECIMAL(19,'.$point_number.')) suite_num';
            $stockout_order_detail_fields = array(
                'gb.brand_name as brand_id',
                'gs.barcode',
                'sto.spec_no',
                'sto.goods_no',
                'sto.goods_name',
                'sto.spec_code',
                'sto.spec_name',
                'sto.api_goods_name',
                'sto.api_spec_name',
                'sto.price',
                $num,
                'sto.suite_name',
                'sto.suite_no',
                $suite_num,
                'sto.weight',
                'sto.remark',
                'IFNULL(cwp.position_no,cwp2.position_no) position_no'
            );
            $stockout_order_detail_cond = array(
                "sod.stockout_id"=>$rec_id,
            );
            $m=D('StockoutOrderDetailHistory');
            $data = $m->alias('sod')->field($stockout_order_detail_fields)->join("LEFT JOIN sales_trade_order_history sto ON sod.src_order_detail_id = sto.rec_id")->join("LEFT JOIN goods_goods gg ON sto.goods_id = gg.goods_id")->join("LEFT JOIN goods_spec gs ON sto.spec_id = gs.spec_id")->join("LEFT JOIN goods_brand gb on gb.brand_id = gg.brand_id")->join('LEFT JOIN stockout_order so ON sod.stockout_id = so.stockout_id')->join('LEFT JOIN stock_spec_position ssp ON ssp.spec_id = sod.spec_id and ssp.warehouse_id = so.warehouse_id')->join('LEFT JOIN cfg_warehouse_position cwp ON cwp.rec_id = ssp.position_id')->join('LEFT JOIN cfg_warehouse_position cwp2 ON cwp2.rec_id = -so.warehouse_id')->where($stockout_order_detail_cond)->select();
        } catch (\PDOException $e) {
            $msg = $e->getMessage();
            \Think\Log::write($this->name."-showStockoutOrderDetailHistoryData-".$msg);
            $data = array('total' => 0, 'rows' => array());
        }
        return $data;
    }

}