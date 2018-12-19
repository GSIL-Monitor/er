<?php
namespace Account\Model;

use Think\Exception;
use Think\Exception\BusinessLogicException;
use Think\Model;
use Think\Log;

class AlipayBillAccountModel extends Model{
    protected $tableName = 'alipay_account_bill_detail';
    protected $pk = 'rec_id';

    public function getAlipayBillAccountList($page=1, $rows=20, $search = array(), $sort = 'abd.rec_id', $order = 'ASC')
    {
        try
        {
            $where = "true ";
            $page = intval($page);
            $rows = intval($rows);
            $order = $sort . ' ' .$order;
            $this->searchFormDeal($where,$search);
            $limit = ($page - 1) * $rows . "," . $rows;
            $sql = "SELECT abd.rec_id id,cs.shop_name,abd.financial_no,abd.business_no,abd.merchant_order_no,abd.order_no,abd.item,abd.goods_name,abd.create_time,
                abd.remark,abd.in_amount,abd.out_amount,abd.balance,abd.opt_pay_account
                FROM alipay_account_bill_detail abd
                LEFT JOIN cfg_shop cs ON cs.shop_id = abd.shop_id
                WHERE $where ORDER BY $order LIMIT $limit";
            $sql_total = "SELECT count(*) AS count FROM alipay_account_bill_detail abd WHERE $where";
            $data["total"] = $this->query($sql_total)[0]["count"];
            $data["rows"] = $this->query($sql);
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
                    set_search_form_value($where, $k, $v, 'abd', 2, ' AND ');
                    break;
                case 'start_time':
                    set_search_form_value($where, 'create_time', $v,'abd', 3,' AND ',' >= ');
                    break;
                case 'end_time':
                    set_search_form_value($where, 'create_time', $v,'abd', 3,' AND ',' < ');
                    break;

                default:
                    continue;
            }
        }
    }

    public function showAccountSummary($page=1, $rows=20, $search = array(), $sort = 'abd.rec_id', $order = 'ASC')
    {
        try
        {
            $where = "true ";
            $page = intval($page);
            $rows = intval($rows);
            $order = $sort . ' ' .$order;
            $this->searchFormDeal($where,$search);
            $limit = ($page - 1) * $rows . "," . $rows;
            $sql = "SELECT abd.rec_id id,cs.shop_name,abd.create_time,abd.in_amount,abd.out_amount,abd.total_amount,abd.in_num,abd.out_num,abd.item
                    FROM alipay_account_bill abd
                    LEFT JOIN cfg_shop cs ON cs.shop_id = abd.shop_id
                    WHERE $where ORDER BY $order LIMIT $limit";
            $sql_total = "SELECT count(*) AS count FROM alipay_account_bill abd WHERE $where";
            $data["total"] = $this->query($sql_total)[0]["count"];
            $data["rows"] = $this->query($sql);
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

}