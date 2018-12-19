<?php


namespace Trade\Model;

use Think\Exception;
use Think\Log;
use Common\Common\ExcelTool;
use Think\Model;
use Think\Exception\BusinessLogicException;
/**
 * @package Trade\Model
 */
class HistoryOriginalTradeModel extends Model {
    protected $tableName = "api_trade_history";
    protected $pk        = "rec_id";
   
    public function getHistoryOriginalTradeList($page = 1, $rows = 20, $search = array(), $sort = 'ate.rec_id', $order = 'desc') {
        try {
            //设置店铺权限
            D('Setting/EmployeeRights')->setSearchRights($search,'shop_id',1);
            //拼接where
            $where = $this->searchForm($search);
            $page = intval($page);
            $rows = intval($rows);
            $limit = ($page - 1) * $rows . "," . $rows;
            $order = $sort . " " . $order;
            $order = addslashes($order);
            //先查询出需要显示的原始订单的rec_id
            $sql_result = "SELECT ate.rec_id FROM api_trade_history ate WHERE $where ORDER BY $order LIMIT $limit";
            //再构造SQL查询完整的数据
            $cfg_show_telno = get_config_value('show_number_to_star', 1);
            $sql            = "SELECT ate_1.rec_id as id,ate_1.platform_id,ate_1.shop_id,cs.shop_name,ate_1.tid,ate_1.process_status,ate_1.trade_status,ate_1.guarantee_mode,ate_1.pay_status,
                    ate_1.delivery_term,ate_1.pay_method,ate_1.refund_status,ate_1.purchase_id,ate_1.bad_reason,ate_1.trade_time,ate_1.pay_time,ate_1.buyer_message,ate_1.remark,ate_1.remark_flag,
                    ate_1.buyer_nick,ate_1.pay_account,ate_1.receiver_name,ate_1.receiver_country,ate_1.receiver_area,ate_1.receiver_ring,ate_1.receiver_address,IF(" . $cfg_show_telno . "=0,ate_1.receiver_mobile,INSERT( ate_1.receiver_mobile,4,4,'****')) receiver_mobile,IF(" . $cfg_show_telno . "=0,ate_1.receiver_telno,INSERT(ate_1.receiver_telno,4,4,'****')) receiver_telno,
                    ate_1.receiver_zip,ate_1.receiver_area,ate_1.to_deliver_time,ate_1.receivable,ate_1.goods_amount,ate_1.post_amount,ate_1.other_amount,ate_1.discount,
                    ate_1.paid,ate_1.platform_cost,ate_1.received,ate_1.dap_amount,ate_1.cod_amount,ate_1.pi_amount,ate_1.refund_amount,ate_1.logistics_type,ate_1.invoice_type,ate_1.invoice_title,
                    ate_1.invoice_content,ate_1.trade_from,ate_1.fenxiao_type,ate_1.fenxiao_nick,ate_1.end_time,ate_1.modified,ate_1.created
                    FROM api_trade_history ate_1
                    INNER JOIN ( " . $sql_result . " ) ate_2 ON(ate_1.rec_id=ate_2.rec_id)
                    LEFT JOIN cfg_shop cs ON (ate_1.shop_id=cs.shop_id)";
            $result         = $this->query($sql);
            foreach ($result as $k1 => $v1) {
              //标旗图片展示
              if($result[$k1]['remark_flag'] == 0){
                $result[$k1]['remark_flag'] = "";
              }else{
                $result[$k1]['remark_flag'] = "<img src='./Public/Image/Icons/op_memo_".$result[$k1]['remark_flag'].".png' >";
              }
            }
            $sql_count      = "SELECT COUNT(1) AS total FROM api_trade_history ate LEFT JOIN cfg_shop cs ON (ate.shop_id=cs.shop_id)";
            $sql_count      = $where == "" ? $sql_count : $sql_count . " where $where";
            $count          = $this->query($sql_count);
            $count          = $count[0]["total"];
            $data           = array();
            $data['rows']   = $result;
            $data['total']  = $count;
        } catch (\Exception $e) {
            Log::write($e->getMessage());
            $data["rows"]  = "";
            $data["total"] = 0;
        }
        return $data;
    }
    public function searchForm($search){
        $where = " true ";
        foreach ($search as $k => $v) {
            if ($v === '') continue;
            switch ($k) {
                case "process_status":
                    set_search_form_value($where, $k, $v, "ate", 2, "AND");
                    break;
                case "tid":
                    set_search_form_value($where, $k, $v, "ate", 1, "AND");
                    break;
                case "buyer_nick":
                    set_search_form_value($where, $k, $v, "ate", 1, "AND");
                    break;
                case "receiver_mobile":
                    set_search_form_value($where, $k, $v, "ate", 1, "AND");
                    break;
                case "shop_id":
                    set_search_form_value($where, $k, $v, "ate", 2, "AND");
                    break;
                case "trade_status":
                    set_search_form_value($where, $k, $v, "ate", 2, "AND");
                    break;
                case "pay_status":
                    set_search_form_value($where, $k, $v, "ate", 2, "AND");
                    break;
                case "delivery_term":
                    set_search_form_value($where, $k, $v, "ate", 2, "AND");
                    break;
                case "refund_status":
                    set_search_form_value($where, $k, $v, "ate", 2, "AND");
                    break;
                default:
                    continue;
            }
        }
        return $where;
    }
    /**
     * @param $id
     * @return mixed
     * 返回货品列表的tabs
     * author:luyanfeng
     */
    public function getGoodsList($id) {
        try {
            $point_number = get_config_value('point_number',0);
            $num = "CAST(ato.num AS DECIMAL(19,".$point_number.")) num";
            $sql           = "SELECT ato.rec_id,ato.oid,ato.status,ato.process_status,ato.refund_status,ato.process_refund_status,ato.order_type,ato
                        .invoice_type,ato.invoice_content,ato.bind_oid,ato.goods_id,ato.spec_id,ato.goods_no,ato.spec_no,ato.goods_name,
                        ato.spec_name,ato.refund_id,ato.is_invalid_goods,".$num.",ato.price,ato.adjust_amount,ato.discount,ato.
                        share_discount,ato.total_amount,ato.share_amount,ato.share_post,ato.refund_amount,ato.modified,ato.created,cs.shop_name
                        FROM api_trade_order_history ato
                        INNER JOIN api_trade_history ate ON(ate.platform_id=ato.platform_id AND ato.tid=ate.tid)
                        LEFT JOIN cfg_shop cs ON(cs.shop_id=ato.shop_id)
                        WHERE ate.rec_id=%d";
            $sql_count     = "SELECT COUNT(1) AS total FROM api_trade_order_history ato,api_trade_history ate
                        WHERE ate.rec_id=%d AND ato.platform_id=ate.platform_id AND ato.tid=ate.tid";
            $result        = $this->query($sql_count, $id);
            $data["total"] = $result[0]["total"];
            $data["rows"]  = $this->query($sql, $id);
        } catch (\Exception $e) {
            Log::write($e->getMessage());
            $data["total"] = 0;
            $data["rows"]  = "";
        }
        return $data;
    }

    public function exportToExcel($id_list,$search){

        try{
            //设置店铺权限
            D('Setting/EmployeeRights')->setSearchRights($search,'shop_id',1);
            $creator=session('account');
            $where = $this->searchForm($search);
            $sort = 'ate_1.rec_id';
            $order = 'desc';
            $order = $sort . " " . $order;
            $order = addslashes($order);
            //先查询出需要显示的原始订单的rec_id
            $sql_result = "SELECT ate.rec_id FROM api_trade_history ate WHERE $where ";
            //再构造SQL查询完整的数据
            $cfg_show_telno = get_config_value('show_number_to_star', 1);
            if(empty($id_list)){
                $sql_fields_str='SELECT ate_1.rec_id as id,ate_1.platform_id,ate_1.shop_id,cs.shop_name,ate_1.tid,ate_1.process_status,ate_1.trade_status,ate_1.guarantee_mode,ate_1.pay_status,ate_1.delivery_term,ate_1.pay_method,ate_1.refund_status,ate_1.purchase_id,ate_1.bad_reason,ate_1.trade_time,ate_1.pay_time,ate_1.buyer_message,ate_1.remark,ate_1.remark_flag,ate_1.buyer_nick,ate_1.pay_account,ate_1.receiver_name,ate_1.receiver_country,ate_1.receiver_area,ate_1.receiver_ring,ate_1.receiver_address,IF(" . $cfg_show_telno . "=0,ate_1.receiver_mobile,INSERT( ate_1.receiver_mobile,4,4,\'****\')) receiver_mobile,IF(" . $cfg_show_telno . "=0,ate_1.receiver_telno,INSERT(ate_1.receiver_telno,4,4,\'****\')) receiver_telno,ate_1.receiver_zip,ate_1.receiver_area,ate_1.to_deliver_time,ate_1.receivable,ate_1.goods_amount,ate_1.post_amount,ate_1.other_amount,ate_1.discount,ate_1.paid,ate_1.platform_cost,ate_1.received,ate_1.dap_amount,ate_1.cod_amount,ate_1.pi_amount,ate_1.refund_amount,ate_1.logistics_type,ate_1.invoice_type,ate_1.invoice_title,ate_1.invoice_content,ate_1.trade_from,ate_1.fenxiao_type,ate_1.fenxiao_nick,ate_1.end_time,ate_1.modified,ate_1.created FROM api_trade_history ate_1';
                $sql_left_join_str=' INNER JOIN ( ' . $sql_result . ' ) ate_2 ON(ate_1.rec_id=ate_2.rec_id) LEFT JOIN cfg_shop cs ON (ate_1.shop_id=cs.shop_id) ';
                $sql=$sql_fields_str.$sql_left_join_str.' ORDER BY '.$order;
            }else{
                $sql_fields_str='SELECT ate_1.rec_id as id,ate_1.platform_id,ate_1.shop_id,cs.shop_name,ate_1.tid,ate_1.process_status,ate_1.trade_status,ate_1.guarantee_mode,ate_1.pay_status,ate_1.delivery_term,ate_1.pay_method,ate_1.refund_status,ate_1.purchase_id,ate_1.bad_reason,ate_1.trade_time,ate_1.pay_time,ate_1.buyer_message,ate_1.remark,ate_1.remark_flag,ate_1.buyer_nick,ate_1.pay_account,ate_1.receiver_name,ate_1.receiver_country,ate_1.receiver_area,ate_1.receiver_ring,ate_1.receiver_address,IF(" . $cfg_show_telno . "=0,ate_1.receiver_mobile,INSERT( ate_1.receiver_mobile,4,4,\'****\')) receiver_mobile,IF(" . $cfg_show_telno . "=0,ate_1.receiver_telno,INSERT(ate_1.receiver_telno,4,4,\'****\')) receiver_telno,ate_1.receiver_zip,ate_1.receiver_area,ate_1.to_deliver_time,ate_1.receivable,ate_1.goods_amount,ate_1.post_amount,ate_1.other_amount,ate_1.discount,ate_1.paid,ate_1.platform_cost,ate_1.received,ate_1.dap_amount,ate_1.cod_amount,ate_1.pi_amount,ate_1.refund_amount,ate_1.logistics_type,ate_1.invoice_type,ate_1.invoice_title,ate_1.invoice_content,ate_1.trade_from,ate_1.fenxiao_type,ate_1.fenxiao_nick,ate_1.end_time,ate_1.modified,ate_1.created FROM api_trade_history ate_1';
                $sql_left_join_str=' INNER JOIN ( ' . $sql_result . ' ) ate_2 ON(ate_1.rec_id=ate_2.rec_id) LEFT JOIN cfg_shop cs ON (ate_1.shop_id=cs.shop_id) ';
                $sql=$sql_fields_str.$sql_left_join_str.' where ate_1.rec_id in ('.$id_list.')'.' ORDER BY '.$order;
            }
            $data = $this->query($sql);
            //处理状态
            $process_status=array(
                '10'=>'待递交',
                '20'=>'已递交',
                '30'=>'部分发货',
                '40'=>'已发货',
                '50'=>'部分结算',
                '60'=>'已完成',
                '70'=>'已取消',
            );
            //订单状态
            $trade_status=array(
                '10'=>'未确认',
                '20'=>'待尾款',
                '30'=>'待发货',
                '40'=>'部分发货',
                '50'=>'已发货',
                '60'=>'已签收',
                '70'=>'已完成',
                '80'=>'已退款',
                '90'=>'已关闭',
            );
            //支付状态
            $pay_status=array(
                '0'=>'未付款',
                '1'=>'部分付款',
                '2'=>'已付款',
            );
            //支付方式
            $pay_method=array(
                '1'=>'在线转帐',
                '2'=>'现金',
                '3'=>'银行转账',
                '4'=>'邮局汇款',
                '5'=>'预付款',
                '6'=>'刷卡',
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
                '3'=>'excel导入'
            );
            //发货条件
            $delivery_term=array(
                '1'=>'款到发货',
                '2'=>'货到付款'
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
            //物流类别
            $logistics_type=array(
                '-1'=>'无',
                '30'=>'CCES',
                '3'=>'EMS',
                '45'=>'EMS经济快递',
                '53'=>'E速宝',
                '71'=>'RUSTON',
                '81'=>'ZTOGZ',
                '82'=>'ZTOSH',
                '31'=>'东方汇',
                '2'=>'中国邮政',
                '12'=>'中远',
                '5'=>'中通快递',
                '11'=>'中铁快运',
                '25'=>'亚风',
                '1311'=>'京邦达(京东快递)',
                '35'=>'优速快递',
                '56'=>'佳吉快递',
                '83'=>'保宏物流',
                '73'=>'信丰物流',
                '37'=>'全一快递',
                '7'=>'全峰快递',
                '15'=>'全日通快递',
                '2000'=>'其他',
                '57'=>'凡宇速递',
                '55'=>'北京EMS',
                '23'=>'华强物流',
                '17'=>'发网',
                '80'=>'合众阳晟',
                '54'=>'同城快递',
                '41'=>'四川快捷',
                '52'=>'国通快递',
                '4'=>'圆通速递',
                '49'=>'城市100',
                '84'=>'增益速递',
                '27'=>'大田',
                '58'=>'天地华宇',
                '16'=>'天天快递',
                '19'=>'宅急送',
                '29'=>'安得',
                '47'=>'尚橙物流',
                '59'=>'居无忧',
                '48'=>'广东EMS',
                '22'=>'德邦物流',
                '14'=>'快捷快递',
                '34'=>'新邦物流',
                '1'=>'无单号物流',
                '24'=>'星辰急便',
                '0'=>'未知',
                '50'=>'汇强快递',
                '70'=>'派易国际物流77',
                '39'=>'浙江ABC',
                '36'=>'港中能达',
                '77'=>'燕文上海',
                '79'=>'燕文义乌',
                '74'=>'燕文北京',
                '76'=>'燕文国际',
                '75'=>'燕文广州',
                '78'=>'燕文深圳',
                '6'=>'申通快递',
                '10'=>'百世汇通',
                '20'=>'百世物流',
                '60'=>'美国速递',
                '18'=>'联昊通',
                '21'=>'联邦快递',
                '42'=>'贝业新兄弟',
                '33'=>'远长',
                '72'=>'速尔',
                '51'=>'邮政国内小包',
                '28'=>'长发',
                '26'=>'长宇',
                '1309'=>'青岛日日顺',
                '9'=>'韵达快递',
                '8'=>'顺丰速运',
                '40'=>'飞远(爱彼西)配送',
                '46'=>'飞远配送',
                '32'=>'首业',
                '38'=>'黑猫宅急便',
                '13'=>'龙邦速递',
                '87'=>'安能物流',
            );
            //退款状态
            $refund_status=array(
                '0'=>'无退款',
                '1'=>'申请退款',
                '2'=>'部分退款',
                '3'=>'全部退款'
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
                $data[$i]['invoice_type']=$invoice_type[$data[$i]['invoice_type']];
                $data[$i]['pay_status']=$pay_status[$data[$i]['pay_status']];
                $data[$i]['pay_method']=$pay_method[$data[$i]['pay_method']];
                $data[$i]['process_status']=$process_status[$data[$i]['process_status']];
                $data[$i]['trade_from']=$trade_from[$data[$i]['trade_from']];
                $data[$i]['delivery_term']=$delivery_term[$data[$i]['delivery_term']];
                $data[$i]['platform_id']=$platform_id[$data[$i]['platform_id']];
                $data[$i]['logistics_type']=$logistics_type[$data[$i]['logistics_type']];
                $data[$i]['refund_status']=$refund_status[$data[$i]['refund_status']];
                $data[$i]['remark_flag']=$remark_flag[$data[$i]['remark_flag']];
            }
            $num = workTimeExportNum();
            if(count($data) > $num){
                SE(self::OVER_EXPORT_ERROR);
            }
            $excel_header = D('Setting/UserData')->getExcelField('Trade/Trade','originalorder');
            $title = '历史原始订单';
            $filename = '历史原始订单';
            foreach ($excel_header as $v){
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