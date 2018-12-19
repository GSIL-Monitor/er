<?php

namespace Trade\Model;

use Think\Exception;
use Common\Common\ExcelTool;
use Think\Log;
use Think\Model;
use Think\Exception\BusinessLogicException;

class HistorySalesTradeModel extends Model {
    protected $tableName = "sales_trade_history";
    protected $pk        = "trade_id";

    public function getHistorySalesTradeList($page,$rows,$search) {
        try {
            $where_sales_trade=' AND st_1.trade_status <= 110 ';
            $data=D('Trade')->queryTrade($where_sales_trade,$page, $rows, $search, $sort = 'trade_id', $order = 'desc',$type='history');
        } catch (\Exception $e) {
            Log::write($e->getMessage());
            $data["rows"]  = "";
            $data["total"] = 0;
        }
        return $data;
    }
    
    public function getHistoryTrade($fields,$where = array(),$alias='',$join=array()){
    	try {
    		$res = $this->alias($alias)->field($fields)->join($join)->where($where)->find();
    		return $res;
    	} catch (\PDOException $e) {
    		\Think\Log::write($this->name.'-getHistoryTradeList-'.$e->getMessage());
    		SE(self::PDO_ERROR);
    	}
    }
    public function exportToExcel($id_list,$search){

        try{
            //设置店铺权限
            D('Setting/EmployeeRights')->setSearchRights($search,'shop_id',1);
            D('Setting/EmployeeRights')->setSearchRights($search,'warehouse_id',2);
            $creator=session('account');
            $where_sales_trade=' st_1.trade_status <= 110 ';
            //拼接where
            D('Trade/Trade')->searchForm($where_sales_trade,$search);
            $flag=false;
            $sort = 'st_2.trade_id';
            $order = 'desc';
            $order = $sort . " " . $order;
            $order = addslashes($order);
            $from_table = 'sales_trade_history';
            $sql_sel_limit="SELECT st_1.trade_id FROM $from_table st_1 WHERE $where_sales_trade ";
            $cfg_show_telno=get_config_value('show_number_to_star',1);

            $point_number = get_config_value('point_number',0);
            $goods_count = "CAST(st_2.goods_count AS DECIMAL(19,".$point_number.")) goods_count";
            $raw_goods_count = "CAST(st_2.raw_goods_count AS DECIMAL(19,".$point_number.")) raw_goods_count";

            if(empty($id_list)){
                $sql_fields_str="SELECT st_2.trade_id AS id,st_2.flag_id, st_2.trade_no, st_2.platform_id, st_2.shop_id ,sh.shop_name,st_2.warehouse_id, sw.name AS warehouse_name, st_2.warehouse_type, st_2.src_tids, st_2.pay_account, st_2.trade_status, st_2.check_step, st_2.consign_status, st_2.trade_from, st_2.trade_type, TO_DAYS(NOW())-TO_DAYS(IF(st_2.delivery_term=2,st_2.trade_time,IF(st_2.pay_time>'1000-01-01 00:00:00',st_2.pay_time,st_2.trade_time))) handle_days, st_2.delivery_term, st_2.freeze_reason, cor.title AS freeze_info,st_2.refund_status, st_2.unmerge_mask, st_2.fenxiao_type, st_2.fenxiao_nick, st_2.trade_time, st_2.pay_time, st_2.delay_to_time, ".$goods_count.", st_2.goods_type_count, st_2.single_spec_no, ".$raw_goods_count.", st_2.raw_goods_type_count, st_2.customer_type, st_2.customer_id, st_2.buyer_nick, st_2.id_card_type, st_2.id_card, st_2.receiver_name, st_2.receiver_country, st_2.receiver_province, st_2.receiver_city, st_2.receiver_district, st_2.receiver_address, IF(".$cfg_show_telno."=0,st_2.receiver_mobile,INSERT( st_2.receiver_mobile,4,4,'****')) receiver_mobile,IF(".$cfg_show_telno."=0,st_2.receiver_telno,INSERT(st_2.receiver_telno,4,4,'****')) receiver_telno, st_2.receiver_zip, st_2.receiver_area, st_2.receiver_ring, st_2.receiver_dtb, st_2.to_deliver_time, st_2.dist_center, st_2.dist_site, st_2.is_prev_notify, clg.logistics_name AS logistics_id, st_2.logistics_no, st_2.buyer_message, st_2.cs_remark, st_2.remark_flag, st_2.print_remark, st_2.note_count, st_2.buyer_message_count, st_2.cs_remark_count, st_2.cs_remark_change_count, st_2.goods_amount, st_2.post_amount, st_2.other_amount, st_2.discount, st_2.receivable, st_2.discount_change, st_2.trade_prepay, st_2.dap_amount, st_2.cod_amount, st_2.pi_amount, st_2.ext_cod_fee, st_2.goods_cost, st_2.post_cost, st_2.other_cost, st_2.profit, st_2.paid, st_2.weight, st_2.volume, st_2.tax, st_2.tax_rate, st_2.commission, st_2.invoice_type, st_2.invoice_title, st_2.invoice_content, st_2.invoice_id, he.fullname AS salesman_id, st_2.sales_score, he_1.fullname AS checker_id, st_2.fchecker_id, st_2.checkouter_id, st_2.allocate_to, st_2.flag_id, st_2.bad_reason, st_2.is_sealed, st_2.gift_mask, st_2.split_from_trade_id, st_2.large_type, st_2.stockout_no, st_2.logistics_template_id, st_2.sendbill_template_id, st_2.revert_reason, st_2.cancel_reason, st_2.is_unpayment_sms, st_2.package_id, IF(st_2.flag_id=0,'无',fg.flag_name) flag_name, st_2.reserve, st_2.version_id, st_2.modified, st_2.created FROM sales_trade_history st_2";
                $sql_left_join_str='LEFT JOIN cfg_shop sh ON sh.shop_id=st_2.shop_id LEFT JOIN cfg_logistics clg ON clg.logistics_id=st_2.logistics_id LEFT JOIN hr_employee he ON he.employee_id= st_2.salesman_id LEFT JOIN hr_employee he_1 ON he_1.employee_id=st_2.checker_id LEFT JOIN cfg_warehouse sw ON sw.warehouse_id=st_2.warehouse_id LEFT JOIN cfg_flags fg ON fg.flag_id=st_2.flag_id LEFT JOIN cfg_oper_reason cor ON cor.reason_id=st_2.freeze_reason';
                $sql=$sql_fields_str.' INNER JOIN('.$sql_sel_limit.') st_3 ON st_2.trade_id=st_3.trade_id '.$sql_left_join_str.' ORDER BY '.$order;
            }else{
                $sql_fields_str="SELECT st_2.trade_id AS id,st_2.flag_id, st_2.trade_no, st_2.platform_id, st_2.shop_id ,sh.shop_name,st_2.warehouse_id, sw.name AS warehouse_name, st_2.warehouse_type, st_2.src_tids, st_2.pay_account, st_2.trade_status, st_2.check_step, st_2.consign_status, st_2.trade_from, st_2.trade_type, TO_DAYS(NOW())-TO_DAYS(IF(st_2.delivery_term=2,st_2.trade_time,IF(st_2.pay_time>'1000-01-01 00:00:00',st_2.pay_time,st_2.trade_time))) handle_days, st_2.delivery_term, st_2.freeze_reason, cor.title AS freeze_info,st_2.refund_status, st_2.unmerge_mask, st_2.fenxiao_type, st_2.fenxiao_nick, st_2.trade_time, st_2.pay_time, st_2.delay_to_time, ".$goods_count.", st_2.goods_type_count, st_2.single_spec_no, ".$raw_goods_count.", st_2.raw_goods_type_count, st_2.customer_type, st_2.customer_id, st_2.buyer_nick, st_2.id_card_type, st_2.id_card, st_2.receiver_name, st_2.receiver_country, st_2.receiver_province, st_2.receiver_city, st_2.receiver_district, st_2.receiver_address, IF(".$cfg_show_telno."=0,st_2.receiver_mobile,INSERT( st_2.receiver_mobile,4,4,'****')) receiver_mobile,IF(".$cfg_show_telno."=0,st_2.receiver_telno,INSERT(st_2.receiver_telno,4,4,'****')) receiver_telno, st_2.receiver_zip, st_2.receiver_area, st_2.receiver_ring, st_2.receiver_dtb, st_2.to_deliver_time, st_2.dist_center, st_2.dist_site, st_2.is_prev_notify, clg.logistics_name AS logistics_id, st_2.logistics_no, st_2.buyer_message, st_2.cs_remark, st_2.remark_flag, st_2.print_remark, st_2.note_count, st_2.buyer_message_count, st_2.cs_remark_count, st_2.cs_remark_change_count, st_2.goods_amount, st_2.post_amount, st_2.other_amount, st_2.discount, st_2.receivable, st_2.discount_change, st_2.trade_prepay, st_2.dap_amount, st_2.cod_amount, st_2.pi_amount, st_2.ext_cod_fee, st_2.goods_cost, st_2.post_cost, st_2.other_cost, st_2.profit, st_2.paid, st_2.weight, st_2.volume, st_2.tax, st_2.tax_rate, st_2.commission, st_2.invoice_type, st_2.invoice_title, st_2.invoice_content, st_2.invoice_id, he.fullname AS salesman_id, st_2.sales_score, he_1.fullname AS checker_id, st_2.fchecker_id, st_2.checkouter_id, st_2.allocate_to, st_2.flag_id, st_2.bad_reason, st_2.is_sealed, st_2.gift_mask, st_2.split_from_trade_id, st_2.large_type, st_2.stockout_no, st_2.logistics_template_id, st_2.sendbill_template_id, st_2.revert_reason, st_2.cancel_reason, st_2.is_unpayment_sms, st_2.package_id, IF(st_2.flag_id=0,'无',fg.flag_name) flag_name, st_2.reserve, st_2.version_id, st_2.modified, st_2.created FROM sales_trade_history st_2";
                $sql_left_join_str='LEFT JOIN cfg_shop sh ON sh.shop_id=st_2.shop_id LEFT JOIN cfg_logistics clg ON clg.logistics_id=st_2.logistics_id LEFT JOIN hr_employee he ON he.employee_id= st_2.salesman_id LEFT JOIN hr_employee he_1 ON he_1.employee_id=st_2.checker_id LEFT JOIN cfg_warehouse sw ON sw.warehouse_id=st_2.warehouse_id LEFT JOIN cfg_flags fg ON fg.flag_id=st_2.flag_id LEFT JOIN cfg_oper_reason cor ON cor.reason_id=st_2.freeze_reason';
                $sql=$sql_fields_str.' INNER JOIN('.$sql_sel_limit.') st_3 ON st_2.trade_id=st_3.trade_id '.$sql_left_join_str.' where st_2.trade_id in ('.$id_list.')'.' ORDER BY '.$order;
            }
            $data = $this->query($sql);
            //订单状态
            $trade_status=array(
                '5'=>'已取消',
                '10'=>'待付款',
                '12'=>'待尾款',
                '15'=>'等未付',
                '16'=>'延时审核',
                '19'=>'预订单前处理',
                '20'=>'前处理',
                '21'=>'委外前处理',
                '22'=>'抢单前处理',
                '25'=>'预订单',
                '27'=>'待抢单',
                '30'=>'待客审',
                '35'=>'待财审',
                '40'=>'待递交仓库',
                '45'=>'递交仓库中',
                '53'=>'已递交仓库',
                '55'=>'已审核',
                '95'=>'已发货',
                '100'=>'已签收',
                '105'=>'部分打款',
                '110'=>'已完成',
                '115'=>'无需处理',
                '120'=>'被合并',
            );
            //类型
            $trade_type=array(
                '1'=>'网店销售',
                '2'=>'线下零售',
                '3'=>'售后换货',
                '4'=>'批发业务',
            );
            //发票类别
            $invoice_type=array(
                '0'=>'不需要',
                '1'=>'普通发票',
                '2'=>'增值税发票'
            );
            //订单来源
            $trade_from=array(
                '1'=>'API抓单',
                '2'=>'手工建单',
                '3'=>'excel导入',
                '4'=>'现款销售',
            );
            //平台信息
            $platform_id=array(
                '0'=>'线下',
                '1'=>'淘宝',
                '2'=>'淘宝分销',
                '3'=>'京东',
                '4'=>'拍拍',
                '5'=>'亚马逊',
                '6'=>'1号店',
                '7'=>'当当网',
                '8'=>'库吧',
                '9'=>'阿里巴巴',
                '10'=>'ECShop',
                '11'=>'麦考林',
                '12'=>'V+',
                '13'=>'苏宁',
                '14'=>'唯品会',
                '15'=>'易迅',
                '16'=>'聚美',
                '17'=>'有赞',
                '19'=>'微铺宝',
                '20'=>'美丽说',
                '21'=>'蘑菇街',
                '22'=>'贝贝网',
                '23'=>'ecstore',
                '24'=>'折800',
                '25'=>'融e购',
                '26'=>'穿衣助手',
                '27'=>'楚楚街',
                '28'=>'微盟旺店',
                '29'=>'卷皮网',
                '30'=>'嘿客',
                '31'=>'飞牛',
                '32'=>'微店',
                '33'=>'拼多多',
                '127'=>'其它'
            );
            //发货条件
            $delivery_term=array(
                '1'=>'款到发货',
                '2'=>'货到付款',
                '3'=>'分期付款'
            );
            //退款状态
            $refund_status=array(
                '0'=>'无退款',
                '1'=>'申请退款',
                '2'=>'部分退款',
                '3'=>'全部退款'
            );
            //发货状态
            $consign_status=array(
                '1'=>'已验货',
                '2'=>'已称重',
                '4'=>'已出库',
                '8'=>'物流同步',
                '1073741824'=>'原始单已完成',
                '16'=>'已分拣',
            );
            //仓库类型
            $warehouse_type=array(
                '1'=>'普通仓库',
                '2'=>'物流宝',
                '3'=>'京东仓储',
                '4'=>'科捷',
                '5'=>'顺丰曼哈顿',
                '11'=>'奇门仓储',
                '0'=>'不限',
            );
            //标旗类别
            $remark_flag = array(
                '0' => '',
                '1' => '红',
                '2' => '黄',
                '3' => '绿',
                '4' => '蓝',
                '5' => '紫'
            );
            for($i=0;$i<count($data);$i++){
                $data[$i]['trade_status']=$trade_status[$data[$i]['trade_status']];
                $data[$i]['trade_type']=$trade_type[$data[$i]['trade_type']];
                $data[$i]['invoice_type']=$invoice_type[$data[$i]['invoice_type']];
                $data[$i]['trade_from']=$trade_from[$data[$i]['trade_from']];
                $data[$i]['delivery_term']=$delivery_term[$data[$i]['delivery_term']];
                $data[$i]['platform_id']=$platform_id[$data[$i]['platform_id']];
                $data[$i]['refund_status']=$refund_status[$data[$i]['refund_status']];
                $data[$i]['consign_status']=$consign_status[$data[$i]['consign_status']];
                $data[$i]['warehouse_type']=$warehouse_type[$data[$i]['warehouse_type']];
                $data[$i]['remark_flag']=$remark_flag[$data[$i]['remark_flag']];
            }
            $num = workTimeExportNum();
            if(count($data) > $num){
                SE(self::OVER_EXPORT_ERROR);
            }
            $excel_header = D('Setting/UserData')->getExcelField('Trade/Trade','trade_manage');
            $title = '历史订单';
            $filename = '历史订单';
            //$width_list = array('20','20','20','20','20','20','20','20','20','20','20','20','20','20','20','20','20');
            foreach ($excel_header as $v)
            {
                $width_list[]=20;
            }
            ExcelTool::Arr2Excel($data,$title,$excel_header,$width_list,$filename,$creator);
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