<?php
/**
 * 原始订单相关模型
 */
namespace Trade\Model;

use Common\Common\ExcelTool;
use Common\Common\UtilTool;
use Common\Common\Factory;
use Think\Exception;
use Think\Log;
use Think\Model;
use Think\Exception\BusinessLogicException;
use Platform\Common\ManagerFactory;

/**
 * Class OriginalTrade
 * @package Trade\Model
 * author:luyanfeng
 */
class OriginalTradeModel extends Model {
    protected $tableName = "api_trade";
    protected $pk        = "rec_id";


    /*
        1 trade_status
        2 pay_status
        4 refund_status
        8 remark
        16 address
        32 inovice
        64 warehouse
    */
    protected $update_trade_sql = 'ON DUPLICATE KEY UPDATE 
                                  modify_flag=IF((@old_trade_count:=(@old_trade_count+1))<0,0,IF((@modify_flag:=(
                                  IF(trade_status=VALUES(trade_status),0,1)|IF(pay_status=VALUES(pay_status),0,2)|IF(refund_status>=VALUES(refund_status),0,4)|
                                  IF(remark=VALUES(remark) AND remark_flag=VALUES(remark_flag),0,8)|IF(receiver_hash=VALUES(receiver_hash),0,16)|
                                  IF(invoice_type=VALUES(invoice_type) AND invoice_title=VALUES(invoice_title) AND invoice_content=VALUES(invoice_content),0,32)|
                                  IF(wms_type=VALUES(wms_type) AND warehouse_no=VALUES(warehouse_no),0,64)|IF(buyer_message=VALUES(buyer_message),0,128)
                                  ))<0,0,modify_flag|@modify_flag)),
                                  pay_id=IF((@chg_trade_count:=(@chg_trade_count+if(@modify_flag=0,0,1)))<0,0,VALUES(pay_id)),
                                  trade_status=GREATEST(trade_status,VALUES(trade_status)),pay_status=GREATEST(pay_status,VALUES(pay_status)),pay_time=VALUES(pay_time),
                                  refund_status=VALUES(refund_status),remark=VALUES(remark),remark_flag=VALUES(remark_flag),buyer_message=VALUES(buyer_message),
                                  purchase_id=VALUES(purchase_id),invoice_type=VALUES(invoice_type),invoice_title=VALUES(invoice_title),invoice_content=VALUES(invoice_content),
                                  receiver_name=VALUES(receiver_name),receiver_province=VALUES(receiver_province),receiver_city=VALUES(receiver_city),
                                  receiver_district=VALUES(receiver_district),receiver_address=VALUES(receiver_address),receiver_mobile=VALUES(receiver_mobile),
                                  receiver_telno=VALUES(receiver_telno),receiver_zip=VALUES(receiver_zip),receiver_area=VALUES(receiver_area),
                                  to_deliver_time=VALUES(to_deliver_time),receiver_hash=VALUES(receiver_hash),goods_amount=VALUES(goods_amount),
                                  post_amount=VALUES(post_amount),other_amount=VALUES(other_amount),discount=VALUES(discount),receivable=VALUES(receivable),paid=VALUES(paid),
                                  platform_cost=VALUES(platform_cost),received=VALUES(received),dap_amount=VALUES(dap_amount),cod_amount=VALUES(cod_amount),
                                  pi_amount=VALUES(pi_amount),refund_amount=VALUES(refund_amount),wms_type=VALUES(wms_type),warehouse_no=VALUES(warehouse_no),
                                  real_score=VALUES(real_score),got_score=VALUES(got_score),goods_count=VALUES(goods_count),order_count=VALUES(order_count),delivery_term=VALUES(delivery_term),bad_reason=0';

    /*
        1 status
        2 refund_status
        4 invoice
        8 discount
        16 goods
        32 warehouse
    */
    protected $update_order_sql = 'ON DUPLICATE KEY UPDATE 
                                  modify_flag=modify_flag|IF(status=VALUES(status),0,1)|IF(refund_status=VALUES(refund_status),0,2)|
                                  IF(invoice_type=VALUES(invoice_type) AND invoice_content=VALUES(invoice_content),0,4)|
                                  IF(adjust_amount=VALUES(adjust_amount) AND discount=VALUES(discount) AND share_discount=VALUES(share_discount),0,8)|
                                  IF((@gn:=(VALUES(goods_id)=\'\')) OR goods_id=VALUES(goods_id) AND spec_id=VALUES(spec_id),0,16)|
                                  IF(wms_type=VALUES(wms_type) AND warehouse_no=VALUES(warehouse_no),0,32),
                                  status=VALUES(status),refund_status=VALUES(refund_status),invoice_type=VALUES(invoice_type),
                                  invoice_content=VALUES(invoice_content),refund_id=VALUES(refund_id),adjust_amount=VALUES(adjust_amount),share_post=VALUES(share_post),
                                  share_discount=VALUES(share_discount),total_amount=VALUES(total_amount),share_amount=VALUES(share_amount),
                                  share_cost=VALUES(share_cost),paid=VALUES(paid),refund_amount=VALUES(refund_amount),goods_id=IF(@gn,goods_id,VALUES(goods_id)),
                                  spec_id=IF(@gn,spec_id,VALUES(spec_id)),goods_no=IF(@gn,goods_no,VALUES(goods_no)),spec_no=IF(@gn,spec_no,VALUES(spec_no)),
                                  goods_name=IF(@gn,goods_name,VALUES(goods_name)),spec_name=IF(@gn,spec_name,VALUES(spec_name)),num=VALUES(num),
                                  wms_type=VALUES(wms_type),warehouse_no=VALUES(warehouse_no),is_auto_wms=VALUES(is_auto_wms)';


    /**
     * @param int    $page
     * @param int    $rows
     * @param array  $search
     * @param string $sort
     * @param string $order
     * @return array
     * 返回原始订单列表
     * author:luyanfeng
     */
    public function searchFormDeal(&$where, $search){
        //设置店铺权限
        $shop_list = D('Setting/EmployeeRights')->setSearchRights($search,'shop_id',1);
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
                case "remark_flag":
                    set_search_form_value($where, $k, $v, "ate", 2, "AND");
                    break;
                case "trade_start_time":
                    set_search_form_value($where, 'trade_time', $v,'ate', 4,' AND ',' >= ');
                    break;
                case "trade_end_time":
                    set_search_form_value($where, 'trade_time', $v, "ate", 4, "AND",'<=');
                    break;
                case "pay_start_time":
                    set_search_form_value($where, 'pay_time', $v,'ate', 4,' AND ',' >= ');
                    break;
                case "pay_end_time":
                    set_search_form_value($where, 'pay_time', $v,'ate', 4,' AND ',' <= ');
                    break;    
                default:
                    continue;
            }
        }
    }
    public function getOriginalTradeList($page = 1, $rows = 20, $search = array(), $sort = 'ate.rec_id', $order = 'desc') {
        try {
            $where = " true ";
            D('Trade/OriginalTrade')->searchFormDeal($where, $search);
            $page = intval($page);
            $rows = intval($rows);
            $limit = ($page - 1) * $rows . "," . $rows;
            $order = $sort . " " . $order;
            $order = addslashes($order);
            //先查询出需要显示的原始订单的rec_id
            $sql_result = "SELECT ate.rec_id FROM api_trade ate WHERE $where ORDER BY $order LIMIT $limit";
            //再构造SQL查询完整的数据
            $cfg_show_telno = get_config_value('show_number_to_star', 1);
            $sql            = "SELECT ate_1.rec_id as id,ate_1.platform_id,ate_1.shop_id,cs.shop_name,ate_1.tid,ate_1.process_status,ate_1.trade_status,ate_1.guarantee_mode,ate_1.pay_status,
                    ate_1.delivery_term,ate_1.pay_method,ate_1.refund_status,ate_1.purchase_id,ate_1.bad_reason,ate_1.trade_time,ate_1.pay_time,ate_1.buyer_message,ate_1.remark,ate_1.remark_flag,
                    ate_1.buyer_nick,ate_1.pay_account,ate_1.receiver_name,ate_1.receiver_country,ate_1.receiver_area,ate_1.receiver_ring,ate_1.receiver_address,IF(" . $cfg_show_telno . "=0,ate_1.receiver_mobile,INSERT( ate_1.receiver_mobile,4,4,'****')) receiver_mobile,IF(" . $cfg_show_telno . "=0,ate_1.receiver_telno,INSERT(ate_1.receiver_telno,4,4,'****')) receiver_telno,
                    ate_1.receiver_zip,ate_1.receiver_area,ate_1.to_deliver_time,ate_1.receivable,ate_1.goods_amount,ate_1.post_amount,ate_1.other_amount,ate_1.discount,
                    ate_1.paid,ate_1.platform_cost,ate_1.received,ate_1.dap_amount,ate_1.cod_amount,ate_1.pi_amount,ate_1.refund_amount,ate_1.logistics_type,ate_1.invoice_type,ate_1.invoice_title,
                    ate_1.invoice_content,ate_1.trade_from,IF(ate_1.is_auto_wms=0,'否','是') as is_auto_wms,ate_1.fenxiao_type,ate_1.fenxiao_nick,ate_1.end_time,ate_1.modified,ate_1.created
                    FROM api_trade ate_1
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
            $sql_count      = "SELECT COUNT(1) AS total FROM api_trade ate LEFT JOIN cfg_shop cs ON (ate.shop_id=cs.shop_id)";
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
                        FROM api_trade_order ato
                        INNER JOIN api_trade ate ON(ate.platform_id=ato.platform_id AND ato.tid=ate.tid)
                        LEFT JOIN cfg_shop cs ON(cs.shop_id=ato.shop_id)
                        WHERE ate.rec_id=%d";
            $sql_count     = "SELECT COUNT(1) AS total FROM api_trade_order ato,api_trade ate
                        WHERE ate.rec_id=%d AND ato.platform_id=ate.platform_id AND ato.tid=ate.tid";
            $result        = $this->query($sql_count, $id);
            $data["total"] = $result[0]["total"];
            $data["rows"]  = $this->query($sql, $id);
        }catch(\PDOException $e){
        	Log::write($e->getMessage());
        	$data["total"] = 0;
        	$data["rows"]  = "";
        } catch (\Exception $e) {
            Log::write($e->getMessage());
            $data["total"] = 0;
            $data["rows"]  = "";
        }
        return $data;
    }

    /**
     * @param $id
     * @return mixed
     * 返回订单的tabs信息
     * author:luyanfeng
     */
    public function getSalesOrderTabs($id) {
		$search = array();
		D('Setting/EmployeeRights')->setSearchRights($search,'warehouse_id',2);
		$where_warehouse_id = $search['warehouse_id'];
        if (empty($where_warehouse_id)) {
            Log::write("系统中没有任何仓库");
            $data["total"] = 0; $data["rows"]  = "";
            return $data;
        }
        $cfg_show_telno = get_config_value('show_number_to_star', 1);
        $sql            = "SELECT DISTINCT st.trade_id,st.trade_no,st.platform_id,cs.shop_name,st.warehouse_id,cw.name,st.warehouse_type,
                        st.src_tids,st.trade_status,st.trade_from,st.trade_type,st.delivery_term,st.freeze_reason,st.refund_status,st.unmerge_mask,
                        st.fenxiao_type,st.fenxiao_nick,st.trade_time,st.pay_time,st.goods_count,st.goods_type_count,st.customer_id,
                        st.buyer_nick,st.receiver_name,st.receiver_country,st.receiver_province,st.receiver_city,st.receiver_district,
                        st.receiver_address,IF(" . $cfg_show_telno . "=0,st.receiver_mobile,INSERT( st.receiver_mobile,4,4,'****')) receiver_mobile,IF(" . $cfg_show_telno . "=0,st.receiver_telno,INSERT(st.receiver_telno,4,4,'****')) receiver_telno,
                        st.receiver_zip,st.receiver_area,st.receiver_ring,st.receiver_dtb,st.to_deliver_time,cl.logistics_name,st.buyer_message,st.cs_remark,
                        st.remark_flag,st.print_remark,st.note_count,st.buyer_message_count,st.cs_remark_count,st.goods_amount,
                        st.post_amount,st.other_amount,st.discount,st.receivable,st.discount_change,st.dap_amount,st.cod_amount,
                        st.pi_amount,st.goods_cost,st.post_cost,st.paid,st.weight,st.invoice_type,st.invoice_title,
                        st.invoice_content,st.salesman_id,st.checker_id,st.fchecker_id,st.checkouter_id,st.flag_id,st.delivery_term,he.fullname,
                        st.bad_reason,st.is_sealed,st.split_from_trade_id,st.stockout_no,st.version_id,st.modified,st.created,sto.src_tid,cor.title
                        FROM sales_trade st
                        INNER JOIN sales_trade_order sto ON(sto.src_tid='$id' AND sto.trade_id=st.trade_id)
                        LEFT JOIN cfg_shop cs ON(st.shop_id=cs.shop_id)
                        LEFT JOIN cfg_oper_reason cor ON(cor.reason_id=st.freeze_reason)
                        LEFT JOIN cfg_warehouse cw ON(st.warehouse_id=cw.warehouse_id)
                        LEFT JOIN hr_employee he ON(he.employee_id=st.salesman_id)
                        LEFT JOIN cfg_logistics cl ON(cl.logistics_id=st.logistics_id) where st.warehouse_id in ($where_warehouse_id)";
        $sql_count      = "SELECT DISTINCT COUNT(1) AS total
                        FROM sales_trade st
                        INNER JOIN sales_trade_order sto ON(sto.src_tid='$id' AND sto.trade_id=st.trade_id)
                        LEFT JOIN cfg_warehouse cw ON(st.warehouse_id=cw.warehouse_id) where st.warehouse_id in ($where_warehouse_id);";
        try {
            $data["rows"]  = $this->query($sql);
            $result        = $this->query($sql_count);
            $data["total"] = $result[0]["total"];
        }catch(\PDOException $e){
        	Log::write($e->getMessage());
        	$data["total"] = 0;
        	$data["rows"]  = "";
        } catch (\Exception $e) {
            Log::write($e->getMessage());
            $data["rows"]  = "";
            $data["total"] = 0;
        }
        return $data;
    }

    /**
     * @param $id
     * @return array
     * 递交原始订单
     * author:luyanfeng
     */
    public function submitOriginalTrade($id) {
        try {
            $ids = "";
            if (count($id) == 0) {
                $sql    = "SELECT at.rec_id AS id FROM api_trade at WHERE at.process_status=10 LIMIT 100";
                $result = $this->query($sql);
                $id     = array();
                foreach ($result as $k => $v) {
                    $id[] = $v["id"];
                }
            }
            foreach ($id as $v) {
                $ids = $ids . $v . ",";
            }
            $sql = "CALL SP_SALES_DELIVER_SOME(" . "'" . $ids . "'" . ")";
            $uid = get_operator_id();
            $this->execute("set @cur_uid=$uid");
            $result = $this->query($sql);
            $res    = array();
            // $result=D('DeliverTrade')->deliver(get_operator_id(),$id);
            if (count($result) == 0) {
                $res["status"] = 0;
                $res["info"]   = "";
            } else {
                $res["status"] = 1;
                $res["info"]   = $result;
            }
            // 检测自动审核
            D('Trade/OriginalTrade')->checkTrade();
            return $res;
        } catch (\Exception $e) {
            Log::write($e->getMessage());
            $res["status"] = 2;
            $res["info"]   = "未知错误，请联系管理员";
            return $res;
        } catch (\PDOException $e){
        	Log::write($e->getMessage());
        	SE(self::PDO_ERROR);
        }
    }
    // 检测自动审核
    public function checkTrade(){
        $now=time();
        $info=$this->query('SELECT c1.`value` AS auto_check_is_open ,c2.`value` AS buyer_message_count,c3.`value` AS cs_remark_count,
              c4.`value` AS receiver_address,c5.`value` AS invoice_type,c6.`value` AS start_time,c7.`value` AS end_time, 
              c8.`value` AS under_weight, c9.`value` AS max_weight , c10.`value` AS time_type 
              FROM cfg_setting c1,cfg_setting c2,cfg_setting c3,cfg_setting c4,cfg_setting c5,cfg_setting c6,cfg_setting c7,cfg_setting c8,cfg_setting c9,cfg_setting c10   
              WHERE c1.`key`="auto_check_is_open" AND c2.`key`="auto_check_buyer_message" AND c3.`key`="auto_check_csremark"
              AND c4.`key`="auto_check_no_adr" AND c5.`key`="auto_check_no_invoice" AND c6.`key`="auto_check_start_time" 
              AND c7.`key`="auto_check_end_time" AND c8.`key`="auto_check_under_weight" AND c9.`key`="auto_check_max_weight"
              AND c10.`key`="auto_check_time_type"');   
        $search=$info[0];
        if($search['auto_check_is_open']==0){
          return false;
        }
        if($search['under_weight']==0){
          unset ($search['max_weight']);
        }        
        foreach ($search as $k=>$v){
          if($v==0){
            unset($search[$k]);
          }
        }
        // 审核
        $check_type=2;
        $arr_remark_flags='';
        for ($i=0;$i<6;$i++)
        {
          $arr_remark_flags.=(!isset($search['remark_flag_'.$i])?'':$i.',');
        }
        if(!empty($arr_remark_flags))
        {
          $search['remark_flag']=true;
        }
        $time_type='trade_time';
        if($search['time_type']==1){
          $time_type='pay_time';
        }
        $where_str=' WHERE st.trade_status=30 ';
        foreach ($search as $k=>$v){
          if($v==='') continue;
          switch ($k)
          {
            case 'buyer_message_count':
              set_search_form_value($where_str, $k, 0,'st',2,' AND ');
              break;
            case 'cs_remark_count':
              set_search_form_value($where_str, $k, 0,'st',2,' AND ');
              break;
            case 'discount':
              set_search_form_value($where_str, $k, 0,'st',2,' AND ');
              break;
            case 'invoice_type':
              set_search_form_value($where_str, $k, 0,'st',2,' AND ');
              break;
            case 'receiver_address':
              $where_str.=" AND st.receiver_address NOT LIKE '%村%' AND st.receiver_address NOT LIKE '%组%' ";
              break;
            case 'remark_flag':
              $where_str.=" AND st.remark_flag IN (".substr($arr_remark_flags,0,-1).") ";
              break;
            case 'start_time':
              set_search_form_value($where_str, $time_type, $v,'st',4,' AND ',' >= ');
              break;
            case 'end_time':
              set_search_form_value($where_str, $time_type, $v,'st',4,' AND ',' <= ');
              break;
            case 'max_weight':
              $v=floatval($v);
              $where_str.=" AND st.weight <= ".$v." ";
              break;
  //          case 'start':
  //            set_search_form_value($where_str, 'created', $v,'st',4,' AND ',' >= ');
  //            break;
  //          case 'end':
  //            set_search_form_value($where_str, 'created', $v,'st',4,' AND ',' <= ');
  //            break;
          }
        }
        $user_id=get_operator_id();
        $trade_check_db=D('Trade/TradeCheck');
        try {
          $sql_where='SELECT st.trade_id FROM sales_trade st '.$where_str;
          $data=$trade_check_db->checkTrade($sql_where,$check_type,$user_id);
          $result=array(
              'check'=>$data['check'],
              'status'=>$data['status'],
              'info'=>array('total'=>count($data['fail']),'rows'=>$data['fail']),//失败提示信息
              'data'=>array('total'=>count($data['success']),'rows'=>$data['success']),//成功的数据
          );
        }catch (BusinessLogicException $e){
          Log::write($e->getMessage());
        }catch (Exception $e){
          Log::write($e->getMessage());
        }
        if ($result["status"]==1) {
           Log::write("AUTO_CHECK_TRADE ERROR");
           return false;
        }
        return true;
      }

    /**
     * @param        $value
     * @param string $key
     * @return bool
     * 检查原始订单是否存在
     */
    public function checkTids($value, $key = 'tid') {
        try {
            $map[$key] = $value;
            $result    = $this->field('rec_id')->where($map)->find();
            if (!empty($result)) {
                return true;
            }
        } catch (\PDOException $e) {
            Log::write($e->getMessage());
        }
        return false;
    }

    /**
     * @param        $value
     * @param string $name
     * @param string $fields
     * @return mixed
     * 获取原始订单
     * author:luyanfeng
     */
    public function getOriginalTrade($value, $name = "rec_id", $fields = "rec_id") {
        try {
            $map[$name]    = $value;
            $result        = $this->field($fields)->where($map)->select();
            $res["status"] = 1;
            $res["info"]   = "操作成功";
            $res["data"]   = $result;
        }catch(\PDOException $e){
        	Log::write($e->getMessage());
        	$res["status"] = 0;
            $res["info"]   = self::PDO_ERROR;
            $res["data"]   = array();
        } catch (\Exception $e) {
            Log::write($e->getMessage());
            $res["status"] = 0;
            $res["info"]   = "系统错误，请联系管理员";
            $res["data"]   = array();
        }
        return $res;
    }

    public function getOperateLog($id) {
        try {
            $sql  = "SELECT he.fullname,sol.message,sol.created FROM sys_other_log sol
                LEFT JOIN hr_employee he ON(he.employee_id=sol.operator_id)
                WHERE sol.type=17 AND sol.data=%d ORDER BY sol.rec_id DESC";
            $list = M("sys_other_log")->query($sql, $id);
            $res  = array("total" => 0, "rows" => $list);
        }catch(\PDOException $e){
        	Log::write($e->getMessage());
        	$res = array("total" => 0, "rows" => array());
        } catch (\Exception $e) {
            Log::write($e->getMessage());
            $res = array("total" => 0, "rows" => array());
        }
        return $res;
    }

    /**
     * @param $data
     * 导入原始订单
     */
    /*public function importTrade($data) {
        try {
            $this->startTrans();
            //判断是否缺少重要数据
            if ($data["shop_name"] == "") E("店铺名称不能为空");
            if ($data["tid"] == "") E("原始单号不能为空");
            if ($data["receiver_name"] == "") E("收件人不能为空");
            if ($data["receiver_address"] == "") E("地址不能为空");
            if ($data["delivery_term"] == "") E("发货条件不能为空");
            //if ($data["discount"] == "") E("优惠金额不能为空");
            //if ($data["spec_no"] == "") E("商家编码不能为空");
            if ($data["num"] == "") E("货品数量不能为空");
            //获取shop_id
            $shop_name = addslashes($data["shop_name"]);
            $shop_id   = $this->query("SELECT shop_id,platform_id FROM cfg_shop WHERE shop_name='{$shop_name}'");
            if (!empty($shop_id)) {
                $data["shop_id"]     = $shop_id[0]["shop_id"];
                $data["platform_id"] = $shop_id[0]["platform_id"];
            } else {
                E("该店铺不存在");
            }
            //判断该订单是否存在和订单状态；如果订单状态不符合要求，则拒绝导入；若存在，则获取order_count
            $tid     = addslashes($data["tid"]);
            $oid     = addslashes($data["oid"]);
            $shop_id = addslashes($data["shop_id"]);
            //判断该子订单是否存在
            $oid_exists = 0;
            $result     = $this->query("SELECT ato.oid,ato.num FROM api_trade_order ato WHERE ato.tid='{$tid}' AND ato.oid='{$oid}' AND ato.shop_id={$shop_id}");
            $oid_num    = 0;
            if (!empty($result)) {
                $oid_exists = 1;
                $oid_num    = floatval($result[0]["num"]);
            }
            //判断原始订单
            $result = $this->query("SELECT at.tid,at.goods_count,at.process_status,at.goods_amount FROM api_trade at WHERE at.tid='{$tid}'");
            if (empty($result)) {
                $data["goods_count"] = $data["num"];
                $goods_amount        = 0;
            } else {
                if ($oid_exists) {
                    $data["goods_count"] = $result[0]["goods_count"] - $oid_num + $data["num"];
                } else {
                    $data["goods_count"] = $result[0]["goods_count"] + $data["num"];
                }
                $goods_amount = $result[0]["goods_amount"];
                if ($result[0]["process_status"] > 10) {
                    E("原始单已递交");
                }
            }
            $result = $this->query("SELECT COUNT(1) AS total FROM api_trade_order ato WHERE ato.tid='{$tid}' AND ato.shop_id={$shop_id}");
            if (!$oid_exists) $data["order_count"] = $result[0]["total"] + 1;
            else $data["order_count"] = $result[0]["total"];
            //获取发货条件
            switch ($data["delivery_term"]) {
                case "款到发货":
                    $data["delivery_term"] = 1;
                    break;
                case "货到付款":
                    $data["delivery_term"] = 2;
                    break;
                case "分期付款":
                    $data["delivery_term"] = 3;
                    break;
                default:
                    E("发货条件不能为空");
            }
            //判断订单状态
            $data["trade_status"]   = 10;
            $data["process_status"] = 70;
            $data["refund_status"]  = 0;
            //获取订单支付状态
            switch ($data["pay_status"]) {
                case "未付款":
                    $data["pay_status"] = 0;
                    break;
                case "部分付款":
                    $data["pay_status"] = 1;
                    break;
                case "已付款":
                    $data["pay_status"] = 2;
                    break;
                default:
                    $data["pay_status"] = 0;
                    break;
            }
            //判断订单状态
            switch ($data["status"]) {
                case "未确认": {
                    $data["trade_status"]   = 10;
                    $data["process_status"] = 10;
                }
                    break;
                case "待尾款": {
                    $data["trade_status"]   = 20;
                    $data["process_status"] = 10;
                }
                    break;
                case "待发货":
                    if ($data["pay_status"] == 2 || $data["delivery_term"] == 2) {
                        $data["trade_status"]   = 30;
                        $data["process_status"] = 10;
                    } else {
                        $data["trade_status"]   = 20;
                        $data["process_status"] = 10;
                    }
                    break;
                case "部分发货":
                    $data["trade_status"] = 40;
                    break;
                case "已发货":
                    $data["trade_status"] = 50;
                    break;
                case "已签收":
                    $data["trade_status"] = 60;
                    break;
                case "已完成":
                    $data["trade_status"] = 70;
                    break;
                case "已退款":
                    $data["trade_status"] = 80;
                    break;
                case "已关闭": {
                    $data["trade_status"]  = 90;
                    $data["refund_status"] = 3;
                }
                    break;
                default:
                    $data["trade_status"] = 10;
            }
            //获取省市县数据
            $province            = addslashes($data["receiver_province"] != "" ? $data["receiver_province"] : null);
            $city                = addslashes($data["receiver_city"] != "" ? $data["receiver_city"] : null);
            $district            = addslashes($data["receiver_district"] != "" ? $data["receiver_district"] : null);
            $area                = $this->query("SELECT dp.province_id,dc.city_id,dd.district_id FROM dict_province dp
                LEFT JOIN dict_city dc ON(dc.province_id=dp.province_id AND dc.name='{$city}')
                LEFT JOIN dict_district dd ON(dd.city_id=dc.city_id AND dd.name='{$district}')
                WHERE dp.name='{$province}'");
            $data["province_id"] = !empty($area[0]["province_id"]) ? $area[0]["province_id"] : 0;
            $data["city_id"]     = !empty($area[0]["city_id"]) ? $area[0]["city_id"] : 0;
            $data["district_id"] = !empty($area[0]["district_id"]) ? $area[0]["district_id"] : 0;
            //将应收合计、邮费、优惠金额、COD买家费用转化格式
            $data["receivable"]  = floatval($data["receivable"]);
            $data["post_amount"] = floatval($data["post_amount"]);
            $data["discount"]    = floatval($data["discount"]);
            //获取发票类型
            switch ($data["invoice_type"]) {
                case "普通发票":
                    $data["invoice_type"] = 1;
                    break;
                case "增值税发票":
                    $data["invoice_type"] = 2;
                    break;
                default:
                    $data["invoice_type"] = 0;
                    break;
            }
            //获取支付方式
            switch ($data["pay_method"]) {
                case "在线转账":
                    $data["pay_method"] = 1;
                    break;
                case "现金":
                    $data["pay_method"] = 2;
                    break;
                case "银行转账":
                    $data["pay_method"] = 3;
                    break;
                case "邮局汇款":
                    $data["pay_method"] = 4;
                    break;
                case "预付款":
                    $data["pay_method"] = 5;
                    break;
                case "刷卡":
                    $data["pay_method"] = 6;
                    break;
                default:
                    $data["pay_method"] = 1;
            }
            //获取货品相关的数据
            if (!empty($data["spec_no"])) {
                $spec_no = addslashes($data["spec_no"]);
                $result  = $this->query("SELECT ags.goods_id,ags.spec_id,ags.outer_id as goods_no,ags.goods_name,ags.spec_name FROM api_goods_spec ags WHERE ags.spec_outer_id='{$spec_no}'");
            } else if (!empty($data["goods_no"])) {
                $goods_no = addslashes($data["goods_no"]);
                $result   = $this->query("SELECT ags.goods_id,ags.spec_id,ags.outer_id as goods_no,ags.goods_name,ags.spec_name FROM api_goods_spec ags WHERE ags.outer_id='{$goods_no}'");
                if (count($result) == 0) {
                    E("该货品不存在");
                } else if (count($result) > 1) {
                    E("该货品编码指向多个平台货品");
                }
            } else {
                E("该订单缺少货品编码或商家编码");
            }
            if (count($result) == 0) E("该货品不存在");
            $data["goods_id"]   = @$result[0]["goods_id"];
            $data["spec_id"]    = @$result[0]["spec_id"];
            $data["goods_no"]   = @$result[0]["goods_no"];
            $data["goods_name"] = @$result[0]["goods_name"];
            $data["spec_name"]  = @$result[0]["spec_name"];
            //修改货品数量、货品价格、货品总价、货品优惠的数据格式
            $data["num"] = floatval($data["num"]);
            if ($data["num"] == 0) E("货品数量不合法");
            $data["price"]        = floatval($data["price"]);
            $data["total_amount"] = floatval($data["price"]) * floatval($data["num"]);
            $goods_amount         = $data["total_amount"] + $goods_amount;
            $data["discount"]     = floatval($data["discount"]);
            //判断是否赠品
            switch ($data["gift_type"]) {
                case "非赠品":
                    $data["gift_type"] = 0;
                    break;
                case "自动赠送":
                    $data["gift_type"] = 1;
                    break;
                case "手动赠送":
                    $data["gift_type"] = 2;
                    break;
                default:
                    $data["gift_type"] = 0;
            }
            //获取订单支付信息
            $data["dap_amount"] = 0;
            $data["cod_amount"] = 0;
            $data["paid"]       = 0;
            $sum_share_amount   = $data["receivable"] - $data["post_amount"];
            if ($data["pay_status"] == 2) {
                $data["paid"] = $data["receivable"];
            } else {
                $data["paid"] = 0;
            }
            if ($data["delivery_term"] == 2) {
                $data["cod_amount"] = $data["paid"];
            } else {
                $data["dap_amount"] = $data["paid"];
            }
            //原始订单数据
            $trade = array(
                "platform_id"       => $data["platform_id"],
                "shop_id"           => $data["shop_id"],
                "tid"               => $data["tid"],
                "trade_status"      => $data["trade_status"],
                "pay_status"        => $data["pay_status"],
                "refund_status"     => $data["refund_status"],
                "process_status"    => $data["process_status"],
                "delivery_term"     => $data["delivery_term"],
                "trade_time"        => $data["trade_time"],
                "trade_from"        => 3,
                "pay_time"          => $data["pay_time"],
                "buyer_nick"        => $data["buyer_nick"],
                "receiver_name"     => $data["receiver_name"],
                "receiver_province" => $data["province_id"],
                "receiver_city"     => $data["city_id"],
                "receiver_district" => $data["district_id"],
                "receiver_mobile"   => $data["receiver_mobile"],
                "receiver_telno"    => $data["receiver_telno"],
                "receiver_zip"      => $data["receiver_zip"],
                "receiver_area"     => $data["receiver_province"] . " " . $data["receiver_city"] . " " . $data["receiver_district"],
                "receiver_address"  => $data["receiver_address"],
                "receiver_hash"     => $data["receiver_hash"],
                "invoice_type"      => $data["invoice_type"],
                "invoice_title"     => $data["invoice_title"],
                "invoice_content"   => $data["invoice_content"],
                "buyer_message"     => $data["buyer_message"],
                "receiver_hash"     => md5($data["receiver_name"] . $data["receiver_area"] . $data["receiver_address"] . $data["receiver_mobile"] . $data["receiver_telno"] . $data["receiver_zip"]),
                "remark"            => $data["remark"],
                "order_count"       => $data["order_count"],
                "goods_count"       => $data["goods_count"],
                "post_amount"       => $data["post_amount"],
                "goods_amount"      => $goods_amount,
                "receivable"        => $data["receivable"],
                "discount"          => $data["discount"],
                "cod_amount"        => $data["cod_amount"],
                "paid"              => $data["paid"],
                "pay_method"        => $data["pay_method"],
                "created"           => date("Y-m-d G:i:s", time())
            );
            //原始订单子订单数据
            $order = array(
                "platform_id"    => $data["platform_id"],
                "status"         => $data["trade_status"],
                "process_status" => $data["process_status"],
                "shop_id"        => $data["shop_id"],
                "tid"            => $data["tid"],
                "oid"            => $data["oid"],
                "refund_status"  => $data["refund_status"],
                "goods_id"       => $data["goods_id"],
                "spec_id"        => $data["spec_id"],
                "goods_no"       => $data["goods_no"],
                "spec_no"        => $data["spec_no"],
                "goods_name"     => $data["goods_name"],
                "spec_name"      => $data["spec_name"],
                "num"            => $data["num"],
                "price"          => $data["price"],
                "total_amount"   => $data["total_amount"],
                "share_amount"   => $data["total_amount"],
                "created"        => date("Y-m-d G:i:s", time())
            );
            $trade_list = array("0" => $trade);
            $sql_trade  = $this->putDataToTable("api_trade", $trade_list, $this->update_trade_sql);
            $this->execute($sql_trade);
            $order_list = array("0" => $order);
            $sql_order  = $this->putDataToTable("api_trade_order", $order_list, $this->update_order_sql);

            $this->execute($sql_order);
            $this->commit();
            //分摊货款，分摊邮费
            $result = $this->query("SELECT ato.rec_id,ato.total_amount FROM api_trade_order ato WHERE ato.platform_id='{$data["platform_id"]}' AND ato.tid='{$data["tid"]}'");
            $sum    = 0;
            foreach ($result as $k => $v) {
                $sum += floatval($v["total_amount"]);
            }
            $order_tb = M("api_trade_order");
            foreach ($result as $v) {
                $percent           = floatval($v["total_amount"]) / floatval($sum);
                $v["share_amount"] = floatval($sum_share_amount) * $percent;
                $v["share_post"]   = floatval($data["post_amount"]) * $percent;
                $v["paid"]         = floatval($data["paid"]) * $percent;
                $order_tb->save($v);
            }
        } catch (\PDOException $e) {
            Log::write($e->getMessage());
            E("未知错误，请联系管理员");
        } catch (\Exception $e) {
            $this->rollback();
            E($e->getMessage());
        }
    }*/

    public function rowToSQL(&$row) {
        $s = array();
        foreach ($row as $k => $v) {
            if (is_int($v))
                $s[] = $v;
            else if (is_array($v))
                $s[] = $v[0];
            else
                $s[] = "'" . addslashes($v) . "'";

        }

        return '(' . implode(',', $s) . ')';
    }

    public function putDataToTable($tab, &$rows, $update = '') {
        if (count($rows) == 0)
            return true;

        $keys = array();
        foreach ($rows[0] as $k => $v)
            $keys[] = $k;

        $data = array();
        for ($i = 0; $i < count($rows); $i++) {
            $data[] = $this->rowToSQL($rows[$i]);
        }
        $data = implode(',', $data);

        if (!empty($update))
            $sql = "INSERT INTO " . $tab . " (" . implode(',', $keys) . ") VALUES " . $data . ' ' . $update;
        else
            $sql = "INSERT IGNORE INTO " . $tab . " (" . implode(',', $keys) . ") VALUES " . $data;
        return $sql;
    }

    /**
     * @param $trade
     * @param $err_msg
     * 将导入的原始订单数据插入数据库
     */
    public function importTrade($trade, &$err_msg) {
        //获取操作用户
        $user_id = get_operator_id();
        //日志表
        $sys_other_log_tb = M("sys_other_log");
        //处理订单数据
        foreach ($trade as $k => $v) {
            try {
                $this->startTrans();
                //分别保存处理过后的订单和子订单数据
                $trade_list = array();
                $order_list = array();
                $this->loadTradeImpl($v, $trade_list, $order_list);
                // 判断同一店铺同一子订单号是否存在
                // 存在=>不能保存（如果保存的话递交后合并系统订单会使sales_trade_order的唯一键），不存在=>可以保存
                $same_num=0;
                foreach ($order_list as $o) {
                   $sql_result=M('api_trade_order')->field('shop_id')->where("oid ='".$o['oid']."' AND tid!='".$o['tid']."'")->select();
                   $shop_id=array();
                   foreach ($sql_result as $s) {$shop_id[]=$s['shop_id'];}
                   if (in_array($o['shop_id'], $shop_id)) {
                      $same_num++;
                   }
                }
                if ($same_num!=0) {
                  SE('在同一店铺下存在重复的原始子订单号');
                }
                //将订单插入数据库
                $sql_trade = $this->putDataToTable("api_trade", $trade_list, $this->update_trade_sql);
                $this->execute($sql_trade);
                $trade_id  = $this->getLastInsID();
                $sql_order = $this->putDataToTable("api_trade_order", $order_list, $this->update_order_sql);
                $this->execute($sql_order);
                $log = array(
                    "type"        => 17,
                    "operator_id" => $user_id,
                    "data"        => $trade_id,
                    "message"     => "导入原始订单 {$k}",
                    "created"     => date("Y-m-d H:i:s")
                );
                $sys_other_log_tb->data($log)->add();
                $this->commit();
            } catch (\PDOException $e) {
                $this->rollback();
                Log::write($e->getMessage());
                $err_msg[] = array("tid" =>''.$k, "result" => "失败", "message" => self::PDO_ERROR);
            } catch (BusinessLogicException $e) {
                $this->rollback();
                $err_msg[] = array("tid" => ''.$k, "result" => "失败", "message" => $e->getMessage());
            } catch (\Exception $e) {
                $this->rollback();
                Log::write($e->getMessage());
                $err_msg[] = array("tid" => ''.$k, "result" => "失败", "message" => self::PDO_ERROR);
            }
        }

    }

    public function loadTradeImpl($trade, &$trade_list, &$order_list) {
        //原始订单号
        $tid = $trade["tid"];
        //获取平台店铺等相关信息
        $shop_name = $trade["shop_name"];
        if ($shop_name == "") {
            SE("店铺不能为空");
        }
        if($tid == ''){
            SE('原始订单号不能为空');
        }
        $result = $this->query("SELECT cs.platform_id,cs.shop_id FROM cfg_shop cs WHERE cs.shop_name='%s'", $shop_name);
        if (count($result) == 0) {
            SE("不存在该店铺");
        }
        $is_online_shop = 0;
        if($result[0]['platform_id']!=0){
            $is_online_shop = 1;
        }
        $platform_id = $result[0]["platform_id"];
        $shop_id     = $result[0]["shop_id"];
        $where = array();
        $shop_id_list = D('Setting/EmployeeRights')->setSearchRights($where,'shop_id',1);
        $shop_id_list = explode(',',$shop_id_list);
        if(!in_array($shop_id,$shop_id_list)){
            SE('该员工没有此店铺权限,导入失败');
        }
        //发票类型
        $invoice_type = 0;
        switch ($trade["invoice_type"]) {
            case "普通发票":
                $invoice_type = 1;
                break;
            case "增值税发票":
                $invoice_type = 2;
                break;
            default:
                break;
        }
        //发票抬头
        $invoice_title = $trade["invoice_title"];
        //发票内容
        $invoice_content = $trade["invoice_content"];
        //退款状态
        $trade_refund_statuss = 0;//退款状态 0无退款 1申请退款 2部分退款 3全部退款
        //支付状态
        $pay_status = 0;//0未付款1部分付款2已付款
        switch ($trade["pay_status"]) {
            case "未付款":
                $pay_status = 0;
                break;
            case "部分付款":
                $pay_status = 1;
                break;
            case "已付款":
                $pay_status = 2;
                break;
            default:
                //默认修改为已付。模板里可以不用填写了
                $pay_status =2;
        }
        //发货条件
        $delivery_term = 1;//发货条件 1款到发货 2货到付款(包含部分货到付款) 3分期付款
        switch ($trade["delivery_term"]) {
            case "款到发货":
                $delivery_term = 1;
                break;
            case "货到付款":
                $delivery_term = 2;
                break;
            case "分期付款":
                $delivery_term = 3;
                break;
            default:
                //发货条件默认款到发货
                $delivery_term = 1;
        }
        //支付方式
        switch ($trade["pay_method"]) {
            case "在线转账":
                $pay_method = 1;
                break;
            case "现金":
                $pay_method = 2;
                break;
            case "银行转账":
                $pay_method = 3;
                break;
            case "邮局汇款":
                $pay_method = 4;
                break;
            case "预付款":
                $pay_method = 5;
                break;
            case "刷卡":
                $pay_method = 6;
                break;
            default:
                $pay_method = 1;
        }
        //订单当前状态
        $trade_status   = 10;//订单平台的状态 10未确认 20待尾款 30待发货 40部分发货 50已发货 60已签收 70已完成 80已退款 90已关闭
        $process_status = 70;//处理状态10待递交20已递交30部分发货40已发货50部分结算60已完成70已取消
        //订单递交条件: process_status=待递交
        //订单递交到审核的条件: process_status=待递交 AND trade_status=待发货
        //订单状态
        switch ($trade["trade_status"]) {
            case "已关闭":
                $trade_status         = 90;
                $trade_refund_statuss = 3;
                SE("不支持导入已关闭订单");
                break;
            case "待发货":
                if ($pay_status == 2 || $delivery_term == 2) {
                    $trade_status   = 30;
                    $process_status = 10;
                } else {
                    $trade_status   = 20;
                    $process_status = 10;
                }
                break;
            case "已发货":
                $trade_status = 50;
                break;
            case "已签收":
                $trade_status = 60;
                break;
            case "已完成":
                $trade_status = 70;
                break;
            case "未确认":
                $trade_status = 10;
                break;
            case "待尾款":
                $trade_status = 20;
                $process_status = 10;
                break;
            default:
                SE("订单状态错误");
        }
        $faq_url = C('faq_url');
        $faq_url = $faq_url["primitive_import_process"];
        //处理买家省市县信息
        if(2 === strlen($trade['receiver_area']) && !empty($trade['receiver_address'])){
            $trade['receiver_address'] = str_replace(array("   ","  "),array(" "," "),$trade['receiver_address']);
            $arr_address = explode(" ",$trade['receiver_address']);
            if(count($arr_address) >= 3){
                $trade['receiver_province'] = $arr_address[0];
                $trade['receiver_city'] = $arr_address[1];
                $trade['receiver_district'] = $arr_address[2];
                $trade['receiver_area'] = $trade['receiver_province']." ".$trade['receiver_city']." ".$trade['receiver_district'];
            }else{
                SE("收货地址请填写省市县(区)信息,并用空格分隔,&lt;a href='{$faq_url}' target='_blank' &gt;点击查看解决办法&lt;/a&gt;");
            }
        }
        $dict_address  = D("DictAddress");
        $receiver_area = $trade["receiver_province"] . " " . $trade["receiver_city"] . " " . $trade["receiver_district"];
        $dict_address->trans2no($trade["receiver_province"], $trade["receiver_city"], $trade["receiver_district"]);
        $receiver_hash = md5($trade["receiver_name"] . $receiver_area . $trade["receiver_address"] . $trade["receiver_mobile"] . $trade["receiver_telno"] . $trade["receiver_zip"]);
        if($trade['receiver_province']=='' || $trade["receiver_city"]=='' || $trade['receiver_district']==''){
            SE("数据异常,请检查省市县(区)信息,&lt;a href='{$faq_url}' target='_blank' &gt;点击查看解决办法&lt;/a&gt;");
        }
        //原始订单
        //货品数量
        $goods_count = 0;
        //子订单数量
        $order_count = 0;
        //货款
        $goods_amount = 0;
        //邮费
        $post_amount = 0;
        //优惠金额
        $discount = 0;
        //已付费用
        $paid = 0;
        //款到发货金额
        $dap_amount = 0;
        //货到付款金额
        $cod_amount = 0;
        //整理每个订单的信息
        $orders = $trade["order"];
        foreach ($orders as $v) {
            $oid        = $v["oid"];
            if($oid == ''){
                SE('原始子订单号不能为空');
            }
            $goods_no   = $v["goods_no"];
            $spec_no    = $v["spec_no"];
            $goods_name = $v['goods_name'];
            $spec_name  = $v['spec_name'];
            $goods_id   = $v['goods_id'];
            $spec_id    = $v['spec_id'];
            if($is_online_shop && ($goods_id == '' || $spec_id == '')){
                SE('线上店铺货品ID与规格ID不能为空');
            }
            if($v['num']==''){
                SE('货品数量不能为空');
            }
            /*if ($spec_no == "") {
                SE("原始订单号" . $tid . " 子订单号" . $oid . "：商家编码不能为空");
            }*/
            /*$result = $this->query("SELECT IFNULL(ags.goods_name,'') AS goods_name, IFNULL(ags.spec_name,'') AS spec_name,IFNULL(ags.goods_id,'') AS goods_id,
                      IFNULL(ags.spec_id,'') AS spec_id,IFNULL(ags.outer_id,'') AS outer_id,IFNULL(ags.spec_outer_id,'') AS spec_outer_id FROM api_goods_spec ags
                      WHERE ags.outer_id='%s' AND ags.spec_outer_id='%s' AND ags.shop_id=%d", array($goods_no, $spec_no, $shop_id));
            if (count($result) == 0) {
                SE("原始订单号" . $tid . " 子订单号" . $oid . "：查询不到该货品");
            } else if (count($result) > 1) {
                SE("原始订单号" . $tid . " 子订单号" . $oid . "：该商家编码指向多条货品");
            } else {
                $goods_name = $result[0]["goods_name"];
                $spec_name  = $result[0]["spec_name"];
                $goods_id   = $result[0]["goods_id"];
                $spec_id    = $result[0]["spec_id"];
                $goods_no   = $result[0]["outer_id"];
                $spec_no    = $result[0]["spec_outer_id"];
            }*/
            switch ($v["gift_type"]) {
                case "非赠品":
                    $gift_type = 0;
                    break;
                case "自动赠送":
                    $gift_type = 1;
                    break;
                case "手动赠送":
                    $gift_type = 2;
                    break;
                default:
                    $gift_type = 0;
            }
            $share_post     = $v["share_post"];
            $share_discount = $v["share_discount"];
            $share_paid     = $v["paid"];
            $total_amount   = $v["num"] * $v["price"] - $share_discount;
            if ($gift_type == 0) {
                $post_amount += $share_post;
                $discount += $share_discount;
                $paid += $share_paid;
                $goods_amount += $total_amount;
            }
            //原始订单子订单数据
            $order_list[] = array(
                "platform_id"    => $platform_id,
                "status"         => $trade_status,
                "process_status" => set_default_value($process_status, 10),
                "shop_id"        => $shop_id,
                "tid"            => $tid,
                "oid"            => $oid,
                "refund_status"  => set_default_value($trade_refund_statuss, 0),
                "goods_id"       => $goods_id,
                "spec_id"        => $spec_id,
                "goods_no"       => $goods_no,
                "spec_no"        => $spec_no,
                "goods_name"     => set_default_value($goods_name, ''),
                "spec_name"      => set_default_value($spec_name, ''),
                "num"            => $v["num"],
                "price"          => set_default_value($v["price"], 0),
                "total_amount"   => set_default_value($total_amount, 0),
                "share_post"     => set_default_value($share_post, 0),
                "share_discount" => set_default_value(floatval($share_discount),0),
                "share_amount"   => set_default_value($total_amount, 0),
                "paid"           => set_default_value($share_paid, 0),
                "gift_type"      => set_default_value($gift_type, 0),
                "remark"         => set_default_value($v["remark"], ''),
                "created"        => date("Y-m-d G:i:s", time())
            );
            $order_count++;
            $goods_count += $v["num"];
        }
        //$goods_amount += $post_amount;
        if ($delivery_term == 2) {
            $cod_amount = $goods_amount;
            $receivable = 0;
        } else {
            $dap_amount = $goods_amount;
            $receivable = $dap_amount + $post_amount;
        }
        if ($trade_status == "90") {
            $paid = 0;
        }
        if($trade['receiver_name']==''){
            SE('收件人不能为空');
        }
        if($trade['receiver_address']==''){
            SE('收货地址不能为空');
        }
        //原始订单数据
        $trade_list[] = array(
            "platform_id"       => $platform_id,
            "shop_id"           => $shop_id,
            "tid"               => $tid,
            "trade_status"      => $trade_status,
            "pay_status"        => $pay_status,
            "refund_status"     => set_default_value($trade_refund_statuss, 0),
            "process_status"    => set_default_value($process_status, 10),
            "delivery_term"     => $delivery_term,
            "trade_time"        => set_default_value($trade["trade_time"], '1000-01-01 00:00:00'),
            "trade_from"        => 3,
            "pay_time"          => set_default_value($trade["pay_time"], '1000-01-01 00:00:00'),
            "buyer_nick"        => set_default_value($trade["buyer_nick"], ''),
            "receiver_name"     => $trade["receiver_name"],
            "receiver_province" => set_default_value($trade["receiver_province"], 0),
            "receiver_city"     => set_default_value($trade["receiver_city"], 0),
            "receiver_district" => set_default_value($trade["receiver_district"], 0),
            "receiver_mobile"   => set_default_value($trade["receiver_mobile"], ''),
            "receiver_telno"    => set_default_value($trade["receiver_telno"], ''),
            "receiver_zip"      => set_default_value($trade["receiver_zip"], ''),
            "receiver_area"     => set_default_value($receiver_area, ''),
            "receiver_address"  => $trade["receiver_address"],
            "receiver_hash"     => set_default_value($receiver_hash, ''),
            "invoice_type"      => set_default_value($invoice_type, 0),
            "invoice_title"     => set_default_value($invoice_title, ''),
            "invoice_content"   => set_default_value($invoice_content, ''),
            "buyer_message"     => set_default_value($trade["buyer_message"], ''),
            "remark"            => set_default_value($trade["remark"], ''),
            "order_count"       => set_default_value($order_count, 0),
            "goods_count"       => set_default_value($goods_count, 0),
            "post_amount"       => set_default_value($post_amount, 0),
            "goods_amount"      => set_default_value($goods_amount, 0),
            "receivable"        => set_default_value($receivable, 0),
            "discount"          => set_default_value($discount, 0),
            "cod_amount"        => set_default_value($cod_amount, 0),
            "dap_amount"        => set_default_value($dap_amount, 0),
            "paid"              => set_default_value($paid, 0),
            "pay_method"        => set_default_value($pay_method, 1),
            "created"           => date("Y-m-d G:i:s", time())
        );
    }

    public function deleteUpload($ids,$user_id){
    	$success=array();
    	$sql_error_info='';
    	$list=array();
    	$is_rollback=false;
    	try{
    		$sql_error_info='getApiTrade';
    		$res_api_trade=$this->field('rec_id,process_status,trade_from,tid,buyer_nick,shop_id')->where(array('rec_id'=>array('in',$ids)))->select();
    		if(empty($res_api_trade)){
    			$list[]=array('tid'=>'','buyer_nick'=>'','error_info'=>'未找到符合条件的订单。');
    		}
    		$is_rollback=true;
    		$this->startTrans();
    		foreach ($res_api_trade as $a){
    			$res_sales_trade=D('SalesTradeOrder')->getSalesTradeOrderList('rec_id,src_tid',array('src_tid'=>$a['tid']));
    			if($a['trade_from']!=3){
    				$list[]=array('tid'=>$a['tid'],'buyer_nick'=>$a['buyer_nick'],'error_info'=>'只能删除导入的原始单。');
    				continue;
    			}
    			if(!empty($res_sales_trade)){
    				$list[]=array('tid'=>$a['tid'],'buyer_nick'=>$a['buyer_nick'],'error_info'=>'该原始订单已递交，无法删除。');
    				continue;
    			}
    			$sql_error_info='deleteApiTradeOrder';
    			$this->execute('DELETE FROM api_trade_order WHERE tid = "' .$a['tid'] .'" AND shop_id = '. $a['shop_id']);
    			$sql_error_info='deleteApiTrade';
    			$this->execute('DELETE FROM api_trade WHERE rec_id = ' .$a['rec_id']);
    			$success[]=array('id'=>$a['tid']);
    		}
    		$this->commit();
    	}catch (\PDOException $e){
    		if($is_rollback)
    		{
    			$this->rollback();
    		}
    		\Think\Log::write($this->name.'-'.$sql_error_info.':'.$e->getMessage());
    		SE(self::PDO_ERROR);
    	}
    	$result=array(
    			'delete'=>empty($success)?false:true,
    			'status'=>empty($list)?0:2,
    			'fail'=>$list,
    			'sucess'=>$success,
    	);
    	return $result;
    }
    public function exportToExcel($id_list,$search,$type='excel'){
        $user_id = get_operator_id();
        $creator=session('account');
        try {
            if ($id_list == '') {
                $where = 'true ';
                $this->searchFormDeal($where, $search);
            } else {
                $where = "rec_id in (" . $id_list . ")";
            }
            $sql_result = "SELECT ate.rec_id FROM api_trade ate WHERE $where ORDER BY ate.rec_id desc";
            $rec = $this->query($sql_result);
            $num = workTimeExportNum($type);
            if(count($rec) > $num){
              if($type=='csv'){
                SE(self::EXPORT_CSV_ERROR);
              }
              SE(self::OVER_EXPORT_ERROR);
            }
            //再构造SQL查询完整的数据
            $cfg_show_telno = get_config_value('show_number_to_star', 1);
            $sql = "SELECT ate_1.rec_id as id,ate_1.platform_id,ate_1.shop_id,cs.shop_name,ate_1.tid,ate_1.process_status,ate_1.trade_status,ate_1.guarantee_mode,ate_1.pay_status,
                    ate_1.delivery_term,ate_1.pay_method,ate_1.refund_status,ate_1.purchase_id,ate_1.bad_reason,ate_1.trade_time,ate_1.pay_time,ate_1.buyer_message,ate_1.remark,ate_1.remark_flag,
                    ate_1.buyer_nick,ate_1.pay_account,ate_1.receiver_name,ate_1.receiver_country,ate_1.receiver_area,ate_1.receiver_ring,ate_1.receiver_address,IF(" . $cfg_show_telno . "=0,ate_1.receiver_mobile,INSERT( ate_1.receiver_mobile,4,4,'****')) receiver_mobile,IF(" . $cfg_show_telno . "=0,ate_1.receiver_telno,INSERT(ate_1.receiver_telno,4,4,'****')) receiver_telno,
                    ate_1.receiver_zip,ate_1.receiver_area,ate_1.to_deliver_time,ate_1.receivable,ate_1.goods_amount,ate_1.post_amount,ate_1.other_amount,ate_1.discount,
                    ate_1.paid,ate_1.platform_cost,ate_1.received,ate_1.dap_amount,ate_1.cod_amount,ate_1.pi_amount,ate_1.refund_amount,ate_1.logistics_type,ate_1.invoice_type,ate_1.invoice_title,
                    ate_1.invoice_content,ate_1.trade_from,ate_1.fenxiao_type,ate_1.fenxiao_nick,ate_1.end_time,ate_1.modified,ate_1.created
                    FROM api_trade ate_1
                    INNER JOIN ( " . $sql_result . " ) ate_2 ON(ate_1.rec_id=ate_2.rec_id)
                    LEFT JOIN cfg_shop cs ON (ate_1.shop_id=cs.shop_id)";
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

            $excel_header = D('Setting/UserData')->getExcelField('Trade/Trade', 'originalorder');
            $title = '原始订单';
            $filename = '原始订单';
            foreach ($excel_header as $v) {
                $width_list[] = 20;
            }
            if($type == 'csv'){
              ExcelTool::Arr2Csv($data, $excel_header, $filename);
            }else{
              ExcelTool::Arr2Excel($data, $title, $excel_header, $width_list, $filename, $creator);
            }
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