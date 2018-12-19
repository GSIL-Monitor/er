<?php  
namespace Customer\Controller;

use Common\Controller\BaseController;
use Customer\Common\CustomerFields;
use Platform\Common\ManagerFactory;
class CustomerSMSController extends BaseController{

    //初始化
    public function _initialize()
    {
        parent::_initialize();
        parent::getIDList($this ->id_list,array('form','toolbar','datagrid'));
    }

    public function getCustomerSMSList($page = 1, $rows = 50, $search = array(), $sort = 'csr.rec_id', $order = 'desc')
    {
        if(IS_POST)
        {
            $this -> ajaxReturn(D('CustomerSMS') -> getCustomerSMSList($page,$rows,$search,$sort,$order));
        }else
        {
            $id_list = $this -> id_list;
            $datagrid = array(
                'id'      => $id_list['datagrid'],
                'options' => array(
                    'title'      => '',
                    'url'        => U('CustomerSMS/getCustomerSMSList'),
                    "toolbar"    => $id_list["toolbar"],
                    "fitColumns" => false,
                    "singleSelect" => false,
                    "rownumbers" => true,
                ),
                'fields'  => CustomerFields::getCustomerFields("CustomerSMS")      
            );
            $checkbox = array('field' => 'ck','checkbox' => true );
            array_unshift($datagrid['fields'],$checkbox);
            $params = array(
                'controller' => strtolower(CONTROLLER_NAME),
                "datagrid"   => array(
                    "id"  => $id_list["datagrid"],
                    "url" => U("CustomerSMS/getCustomerSMSList")
                ),
                "search"     => array("form_id" => $id_list["form"]),
                "cancelSend" => U("CustomerSMS/cancelSend"),
                "againSend"  => U("CustomerSMS/againSend"),
            );
            //获取操作人信息
            $employee = M('hr_employee') -> field('fullname,employee_id as id') -> where('deleted = 0') -> select();
            //获取短信余额
            $sid = get_sid();
            $SmsManager = ManagerFactory::getManager ("Sms");
            $sms_count = $SmsManager->manualGetBalance ($sid);
            //判断短信接口状态，如果状态不为0的话则返回值为0
            $sms_count = $sms_count['status']?0:$sms_count['info']['sms_num'];
            $this -> assign('sms_count',$sms_count);
            $this -> assign('employee',$employee);
			$this -> assign('seller_account',$sid);
            $this -> assign('id_list',$id_list);
            $this -> assign('params',json_encode($params));
            $this -> assign('datagrid',$datagrid);
            $this -> display('show');
        }  
    }

    //重新发送
    public function againSend($sms_id)
    {
        try {
            $res = array('status' => 0,'info' => '操作成功');
            D('CustomerSMS') -> againSend($sms_id);
        } catch (BusinessLogicException $e) {
            $res = array("status" => 1, "info" => $e->getMessage());
        } catch (\Exception $e) {
            \Think\Log::write($e->getMessage());
            $res = array("status" => 1, "info" => parent::UNKNOWN_ERROR);
        }
        $this -> ajaxReturn($res);
    }

    //取消发送
    public function cancelSend($sms_id)
    {
        try {
            $res = array('status' => 0,'info' => '操作成功');
            D('CustomerSMS') -> cancelSend($sms_id);
        } catch (BusinessLogicException $e) {
            $res = array("status" => 1, "info" => $e->getMessage());
        } catch (\Exception $e) {
            \Think\Log::write($e->getMessage());
            $res = array("status" => 1, "info" => parent::UNKNOWN_ERROR);
        }
        $this -> ajaxReturn($res);
    }
}