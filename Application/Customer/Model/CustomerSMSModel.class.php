<?php  
namespace Customer\Model;
use Think\Model;
use Think\Exception\BusinessLogicException;

class CustomerSMSModel extends Model{
    protected $tableName = 'crm_sms_record';
    protected $pk = 'rec_id';

    public function getCustomerSMSList($page,$rows,$search,$sort,$order)
    {
        try {
            $where = "true ";
            $page = intval($page);
            $rows = intval($rows);
            $sort  = $sort . " " . $order;
            $sort  = addslashes($sort);
            foreach($search as $k => $v)
            {
                switch($k)
                { 
                    case 'batch_no':
                        set_search_form_value($where, $k, $v, 'csr', 1, ' AND ');
                        break;
                    case 'operator_id':
                        set_search_form_value($where, $k, $v, 'csr', 2, ' AND ');
                        break;
                    case 'start_time':
                        set_search_form_value($where, 'send_time', $v.' 00:00:00','csr', 4,' AND ',' >= ');
                        break;
                    case 'end_time':
                        set_search_form_value($where, 'send_time', $v.' 23:59:59','csr', 4,' AND ',' <= ');
                        break;
                }
            }
            $res['rows'] = $this ->alias('csr') -> field("csr.*,hr.fullname") -> where($where) -> page($page,$rows) -> order($sort) -> join("hr_employee as hr on hr.employee_id = csr.operator_id") ->select();
            $sql_count   = $this  ->alias('csr')-> field('count(1) as total') -> where($where) -> select();
            $res['total'] = $sql_count[0]['total'];
        } catch (\PDOException $e) {
            \Think\Log::write($e->getMessage());
            $res["total"] = 0;
            $res["rows"]  = array();
            SE(parent::PDO_ERROR);
        }catch (\Exception $e) {
            \Think\Log::write($e->getMessage());
            $res["total"] = 0;
            $res["rows"]  = array();
        }
        return $res;
    }

    //取消发送
    public function cancelSend($sms_id)
    {
        try {
            foreach($sms_id as $v)
            {
                $data['status'] = 4;
                $this -> where("rec_id = %d",$v) -> save($data);
            }
        } catch (\PDOException $e) {
            \Think\Log::write($e->getMessage());
            SE(parent::PDO_ERROR);
        } catch (BusinessLogicException $e){
            SE($e->getMessage());
        } catch (\Exception $e) {
            \Think\Log::write($e->getMessage());
            SE(parent::PDO_ERROR);
        }
    }

    //重新发送
    public function againSend($sms_id)
    {
        try {
            foreach($sms_id as $v)
            {
                $data['status'] = 0;
                $this -> where("rec_id = %d",$v) -> save($data);
            }
        } catch (\PDOException $e) {
            \Think\Log::write($e->getMessage());
            SE(parent::PDO_ERROR);
        } catch (BusinessLogicException $e){
            SE($e->getMessage());
        } catch (\Exception $e) {
            \Think\Log::write($e->getMessage());
            SE(parent::PDO_ERROR);
        }
    }
}
