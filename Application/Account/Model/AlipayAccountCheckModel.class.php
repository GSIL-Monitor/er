<?php
namespace Account\Model;

use Think\Exception;
use Think\Exception\BusinessLogicException;
use Think\Model;
use Think\Log;

class AlipayAccountCheckModel extends Model{
    protected $tableName = 'fa_alipay_account_check';
    protected $pk = 'rec_id';

    public function getAlipayAccountCheckList($page=1, $rows=20, $search = array(), $sort = 'faac.rec_id', $order = 'ASC')
    {
        try
        {
            $where = "true ";
            $page = intval($page);
            $rows = intval($rows);
            $order = $sort . ' ' .$order;
            $this->searchFormDeal($where,$search);
            $limit = ($page - 1) * $rows . "," . $rows;
            //先查询出自增主键当索引使用
            $rec_result = $this->alias('faac')->field('faac.rec_id')->where($where)->limit($limit)->order($order)->fetchSql(true)->select();
            $rows = $this->alias('fac')->field('fac.rec_id as id,fac.account_check_no,fac.tid,fac.platform_id,
                    cs.shop_name shop_id,fac.pay_amount,fac.trade_amount,fac.send_amount,fac.status,fac.refund_amount,apt.refund_status,fac.cost_amount,fac.confirm_amount,
                    if(fac.send_amount>=fac.trade_amount,1,0) as is_send_all,fac.check_time,fac.consign_time,
                    fac.confirm_time')->join("($rec_result) fac2 ON fac.rec_id=fac2.rec_id")->join('LEFT JOIN cfg_shop cs ON cs.shop_id=fac.shop_id')->join('left join api_trade apt on apt.platform_id=fac.platform_id and apt.tid=fac.tid')->fetchSql(false)->select();
            $sql_total = $this->alias('faac')->field('count(1) AS total')->where($where)->select();
            $data["total"] =$sql_total[0]["total"];
            $data["rows"] = $rows;
        }catch (\PDOException $e)
        {
            Log::write($e->getMessage());
            $data["total"] = 0;
            $data["rows"] = "";
            SE(parent::PDO_ERROR);
        }catch(\Exception $e)
        {
            Log::write($e->getMessage());
            $data["total"] = 0;
            $data["rows"] = "";
            SE($e->getMessage());
        }
        return $data;

    }

    public function searchFormDeal(&$where, $search)
    {
        D('Setting/EmployeeRights')->setSearchRights($search, 'shop_id', 1);
        foreach($search as $k => $v){
            if($v === "") continue;
            switch($k){
                case 'shop_id':
                    set_search_form_value($where, $k, $v, 'faac', 2, ' AND ');
                    break;
                case 'platform_id':
                    set_search_form_value($where, $k, $v, 'faac', 2, ' AND ');
                    break;
                case 'tid':
                    set_search_form_value($where, $k, $v, 'faac', 1, ' AND ');
                    break;
                case 'consign_start_time': //订单发货日期
                    set_search_form_value($where, 'consign_time', $v,'faac', 4,' AND ',' >= ');
                    break;
                case 'consign_end_time':
                    set_search_form_value($where, 'consign_time', $v,'faac', 4,' AND ',' < ');
                    break;
                case 'confirm_start_time': //确认收货日期
                    set_search_form_value($where, 'confirm_time', $v,'faac', 4,' AND ',' >= ');
                    break;
                case 'confirm_end_time':
                    set_search_form_value($where, 'confirm_time', $v,'faac', 4,' AND ',' < ');
                    break;
                case 'check_start_time': //订单对账日期
                    set_search_form_value($where, 'check_time', $v,'faac', 4,' AND ',' >= ');
                    break;
                case 'check_end_time':
                    set_search_form_value($where, 'check_time', $v,'faac', 4,' AND ',' < ');
                    break;

                default:
                    continue;
            }
        }
    }

    public function getAccountSalesTrade($id)
    {
        try
        {
            $res = $this->field('tid,platform_id')->where(array('rec_id'=>$id))->find();
            $tid = $res['tid'];
            $platform_id = $res['platform_id'];
            $where = array('src_tid'=>$tid,'platform_id'=>$platform_id);
            $rows = M('sales_trade_order')->field('is_consigned,if(refund_status =0,0,1) is_refund,share_amount,share_post,share_post+share_amount as amount,goods_name,spec_name,spec_no')->where($where)->select();
            $count = M('sales_trade_order')->field('count(1) AS total')->where($where)->select();
            $data["total"] = $count[0]["total"];
            $data["rows"] = $rows;
        }catch (\PDOException $e)
        {
            $data["total"] = 0;
            $data["rows"] = "";
            Log::write($e->getMessage());
            SE(parent::PDO_ERROR);
        }catch(\Exception $e)
        {
            Log::write($e->getMessage());
            $data["total"] = 0;
            $data["rows"] = "";
            SE($e->getMessage());
        }
        return $data;
    }

    public function getAlipayBillByTid($id)
    {
        try
        {
            $res = $this->field('tid,platform_id')->where(array('rec_id'=>$id))->find();
            $tid = $res['tid'];
            $platform_id = $res['platform_id'];
            $where = array('order_no'=>$tid,'platform_id'=>$platform_id,'post_status'=>4);
            $rows = M('alipay_account_bill_detail')->field('item,order_no pay_order_no,opt_pay_account,order_no,in_amount,out_amount,in_amount+out_amount as amount,balance,remark')->where($where)->select();
            $count = M('alipay_account_bill_detail')->field('count(1) AS total')->where($where)->select();
            $data["total"] = $count[0]["total"];
            $data["rows"] = $rows;
        }catch (\PDOException $e)
        {
            $data["total"] = 0;
            $data["rows"] = "";
            Log::write($e->getMessage());
            SE(parent::PDO_ERROR);
        }catch(\Exception $e)
        {
            Log::write($e->getMessage());
            $data["total"] = 0;
            $data["rows"] = "";
            SE($e->getMessage());
        }
        return $data;
    }

    public function getAlipayAccountCheckLog($id)
    {
        try
        {
            $res = $this->field('tid,platform_id')->where(array('rec_id'=>$id))->find();
            $tid = $res['tid'];
            $platform_id = $res['platform_id'];
            $where = array('facl.src_tid'=>$tid,'facl.platform_id'=>$platform_id);
            $rows = M('fa_alipay_account_log')->alias('facl')->field('he.fullname as operator_id, facl.message,facl.created')->where($where)->join("left join hr_employee he ON facl.operator_id=he.employee_id")->fetchSql(false)->select();
            $count = M('fa_alipay_account_log')->alias('facl')->field('count(1) AS total')->where($where)->select();
            $data["total"] = $count[0]["total"];
            $data["rows"] = $rows;
        }catch (\PDOException $e)
        {
            $data["total"] = 0;
            $data["rows"] = "";
            Log::write($e->getMessage());
            SE(parent::PDO_ERROR);
        }catch(\Exception $e)
        {
            Log::write($e->getMessage());
            $data["total"] = 0;
            $data["rows"] = "";
            SE($e->getMessage());
        }
        return $data;
    }

    public function modifyAccountCheckAmount($id,$data)
    {
        $new_pay_amount = empty($data['new_pay_amount'])?0:$data['new_pay_amount'];
        $new_pay_date = empty($data['new_pay_date'])?'0000-00-00 00:00:00':$data['pay_amount'];
        $new_refund_amount = empty($data['new_refund_amount'])?0:$data['new_refund_amount'];
        $new_refund_date = empty($data['new_refund_date'])?'0000-00-00 00:00:00':$data['new_refund_date'];
        try
        {
            $res = $this->field('tid,platform_id,shop_id,status')->where(array('rec_id'=>$id))->find();
            $tid = $res['tid'];
            $platform_id = $res['platform_id'];
            $shop_id = $res['shop_id'];
            $status = $res['status'];
            if($new_pay_amount==0 || $new_refund_amount==0)
            {
                SE('请输入合法的数值');
            }
            if($new_pay_amount<>0)
            {
                if($new_pay_date =='0000-00-00 00:00:00' ||!$new_pay_date)
                {
                    SE('校正收款时间不能为空');
                }
                $receive_amount = $this->query("SELECT IFNULL(receive_amount,0) as receive_amount FROM fa_platform_check_detail_month
                                                WHERE check_month=DATE_FORMAT('{$new_pay_date}','%Y-%m') AND tid='{$tid}' AND platform_id=$platform_id");
                if($receive_amount[0]['receive_amount']+$new_pay_amount <0)
                {
                    SE('校正后当月收款金额小于0');
                }
            }
            if($new_refund_amount<>0)
            {
                if($new_refund_date =='0000-00-00 00:00:00' ||!$new_refund_date)
                {
                    SE('校正退款时间不能为空');
                }
                $cur_refund_amount = $this->query("SELECT IFNULL(refund_amount,0) as cur_refund_amount FROM fa_platform_check_detail_month
                                                  WHERE check_month=DATE_FORMAT('{$new_refund_date}','%Y-%m') AND tid='{$tid}' AND platform_id=$platform_id");
                if($cur_refund_amount[0]['cur_refund_amount']+$new_refund_amount<0)
                {
                    SE('校正后当月退款金额小于0');
                }

            }
            $operator_id = get_operator_id();
            $new_pay_month = date('Y-m',strtotime($new_pay_date));
            $new_refund_month = date('Y-m',strtotime($new_refund_date));
            $this->startTrans();
            if($new_pay_date<>'0000-00-00 00:00:00' && $new_pay_date<>0)
            {
                if($status==5)
                {
                    $res = $this->query("SELECT rec_id FROM fa_platform_check_detail_month WHERE tid = $tid AND platform_id=$platform_id AND check_month='{$new_pay_month}'");
                    if(!empty($res[0]['rec_id']))
                    {
                        $this->execute("UPDATE fa_platform_check_detail_month SET
                                  receive_amount=receive_amount+$new_pay_amount,diff_amount=diff_amount-$new_pay_amount,`status`=IF(diff_amount=0,3,5)
                                  WHERE check_month='{$new_pay_month}' AND tid='{$tid}' AND platform_id=$platform_id");
                    }else
                    {
                        $this->execute("INSERT INTO fa_platform_check_detail_month(check_month,tid,shop_id,platform_id,receive_amount,diff_amount,created,`status`)
						            VALUES('{$new_pay_month}','{$tid}',$shop_id,$platform_id,$new_pay_amount,-$new_pay_amount,NOW(),5)");
                    }
                }else
                {
                    $res = $this->query("SELECT rec_id FROM fa_platform_check_detail_month WHERE tid = $tid AND platform_id=$platform_id AND check_month='{$new_pay_month}'");
                    if(!empty($res[0]['rec_id']))
                    {
                        $this->execute("UPDATE fa_platform_check_detail_month SET
                                  receive_amount=receive_amount+$new_pay_amount,diff_amount=diff_amount-$new_pay_amount,`status`=IF(diff_amount=0,3,1)
                                  WHERE check_month='{$new_pay_month}' AND tid='{$tid}' AND platform_id=$platform_id");
                    }else
                    {
                        $this->execute("INSERT INTO fa_platform_check_detail_month(check_month,tid,shop_id,platform_id,receive_amount,diff_amount,created,`status`)
						            VALUES('{$new_pay_month}','{$tid}',$shop_id,$platform_id,$new_pay_amount,-$new_pay_amount,NOW(),1)");
                    }
                }
                $result = $this->query("SELECT `status`,`diff_amount`,`send_amount`+`last_send_amount` as send_amount,`receive_amount`+`last_receive_amount` as receive_amount,
                                    `refund_amount`+`last_refund_amount` as refund_amount,`is_transfer`,wait_refund_amount
                                    FROM fa_platform_check_detail_month WHERE tid='{$tid}' AND platform_id=$platform_id AND check_month='{$new_pay_month}'");
                $result = $result[0];
                $modify_status = $result['status'];
                $modify_diff_amount = $result['diff_amount'];
                $modify_send_amount = $result['send_amount'];
                $modify_receive_amount = $result['receive_amount'];
                $modify_refund_amount = $result['refund_amount'];
                $transfer = $result['is_transfer'];
                $modify_wait_amount = $result['wait_refund_amount'];
                if($modify_status==1)
                {
                    if($modify_send_amount==$modify_receive_amount)
                    {
                        $modify_sub_status =5;
                    }elseif($modify_diff_amount==$modify_wait_amount)
                    {
                        $modify_sub_status =6;
                    }elseif($modify_wait_amount<>0)
                    {
                        $modify_sub_status =7;
                    }else
                    {
                        $modify_sub_status =$transfer==1?8:9;
                    }
                }elseif($modify_status==5)
                {
                    $modify_sub_status =1;
                }else
                {
                    $modify_sub_status =$transfer==1?3:2;
                }

                $this->execute("UPDATE fa_platform_check_detail_month SET sub_status=$modify_sub_status  WHERE tid='{$tid}' AND platform_id=$platform_id AND check_month='{$new_pay_month}'");
            }
            if($new_refund_date<>'0000-00-00 00:00:00' && $new_refund_amount<>0)
            {
                $res = $this->query("SELECT rec_id FROM fa_platform_check_detail_month WHERE tid = $tid AND platform_id=$platform_id AND check_month='{$new_refund_month}'");
                if(!empty($res[0]['rec_id']))
                {
                    $this->execute("UPDATE fa_platform_check_detail_month SET refund_amount=refund_amount+$new_refund_amount,diff_amount=diff_amount-$new_refund_amount,`status`=IF(diff_amount=0,3,1)   WHERE check_month='{$new_refund_month}' AND tid='{$tid}' AND platform_id=$platform_id");
                }else
                {
                    $this->execute("INSERT INTO fa_platform_check_detail_month(check_month,tid,shop_id,platform_id,refund_amount,diff_amount,created,`status`)
					                VALUES('{$new_refund_month}','{$tid}',$shop_id,$platform_id,$new_refund_amount,-$new_refund_amount,NOW(),1)");
                }
                $result = $this->query("SELECT `status`,`diff_amount`,`send_amount`+`last_send_amount` as send_amount,`receive_amount`+`last_receive_amount` as receive_amount,
                                      `refund_amount`+`last_refund_amount` as refund_amount,`is_transfer`,wait_refund_amount
                                       FROM fa_platform_check_detail_month WHERE tid='{$tid}' AND platform_id=$platform_id AND check_month='{$new_refund_month}'");
                $modify_status = $result['status'];
                $modify_diff_amount = $result['diff_amount'];
                $modify_send_amount = $result['send_amount'];
                $modify_receive_amount = $result['receive_amount'];
                $modify_refund_amount = $result['refund_amount'];
                $transfer = $result['is_transfer'];
                $modify_wait_amount = $result['wait_refund_amount'];
                if($modify_status==1)
                {
                    if($modify_send_amount==$modify_receive_amount)
                    {
                        $modify_sub_status =5;
                    }elseif($modify_diff_amount==$modify_wait_amount)
                    {
                        $modify_sub_status =6;
                    }elseif($modify_wait_amount<>0)
                    {
                        $modify_sub_status =7;
                    }else
                    {
                        $modify_sub_status =$transfer==1?8:9;
                    }
                }elseif($modify_status==5)
                {
                    $modify_sub_status =1;
                }else
                {
                    $modify_sub_status =$transfer==1?3:2;
                }
                $this->execute("UPDATE fa_platform_check_detail_month SET sub_status=$modify_sub_status  WHERE tid='{$tid}' AND platform_id=$platform_id AND check_month='{$new_refund_month}';");
            }
            $this->execute("UPDATE fa_alipay_account_check SET pay_amount=pay_amount+$new_pay_amount,refund_amount=refund_amount+$new_refund_amount WHERE rec_id=$id");
            $this->execute("INSERT INTO fa_alipay_account_log(src_tid,platform_id,operator_id,`type`,message,created)
			              VALUES('{$tid}',$platform_id,$operator_id,1,CONCAT('校正收款金额:',$new_pay_amount,' ;校正售中退款金额:',$new_refund_amount,' ;'),NOW());");

            //修改金额后对账
            D('Trade/Model/RefundManage')->faAlipayAccountCheck($tid,$platform_id);


            $this->commit();
        }catch (\PDOException $e)
        {
            $this->rollback();
            Log::write($e->getMessage());
            SE(parent::PDO_ERROR);
        }catch(BusinessLogicException $e)
        {
            $this->rollback();
            SE($e->getMessage());
        }catch(\Exception $e)
        {
            $this->rollback();
            Log::write($e->getMessage());
            SE($e->getMessage());
        }

    }


    public function alipayAccountCheckSetSuccess($id)
    {
        try
        {
            $operator_id = get_operator_id();
            $this->startTrans();
            //下一个对账明细期间 时间记录。暂时不用。
            $tmp_last_time = get_config_value('fa_checked_detail_month_failed_last',date('Y-m',strtotime('-1 month')).'-01 00:00:00');
            $tmp_real_time = date('Y-m',strtotime($tmp_last_time)).'-01 00:00:00';
            $this->execute("UPDATE fa_alipay_account_check SET `status`=4,check_time=NOW() WHERE `rec_id`=$id");
            $this->execute("UPDATE  fa_alipay_account_check fac
                            LEFT JOIN  fa_platform_check_detail_month fdm
				            ON fdm.tid=fac.tid AND fdm.platform_id=fac.platform_id
	                        SET  fdm.diff_amount=0,fdm.status=4,fdm.title='已设置对账成功',fdm.sub_status=4
	                        WHERE fdm.created>='{$tmp_real_time}'");
            //记录日志
            $this->execute("INSERT INTO fa_alipay_account_log(src_tid,platform_id,operator_id,`type`,message,created)
		                    SELECT fac.tid,fac.platform_id,$operator_id,2,'设置对账成功',NOW()
		                    FROM fa_alipay_account_check fac WHERE fac.rec_id=$id");
            $this->commit();
        }catch (\PDOException $e)
        {
            $this->rollback();
            Log::write($e->getMessage());
            SE(parent::PDO_ERROR);
        }catch(\Exception $e)
        {
            $this->rollback();
            Log::write($e->getMessage());
            SE($e->getMessage());
        }

    }

}