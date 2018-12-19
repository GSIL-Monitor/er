<?php
namespace Customer\Controller;

use Common\Controller\BaseController;
use Common\Common\Factory;
use Customer\Common\CustomerFields;
use Common\Common\UtilTool;
use Common\Common\UtilDB;
use Think\Exception\BusinessLogicException;
use Common\Common\ExcelTool;

class CustomerFileController extends BaseController {

    /**
     * @param int    $page
     * @param int    $rows
     * @param array  $search
     * @param string $sort
     * @param string $order
     * 返回客户档案列表
     * author:luyanfeng
     * table:crm_customer
     */
    public function getCustomerList($page = 1, $rows = 20, $search = array(), $sort = "cc.customer_id", $order ="desc") {
        if (IS_POST) {
        	$wangwang 	= I('get.wangwang');
        	if($wangwang!='' && empty($search)){
        		$search['wangwang']=$wangwang;
        	}
            $this->ajaxReturn(Factory::getModel("CustomerFile")->getCustomerList($page, $rows, $search, $sort, $order));
        } else {
            //todo 创建主页面
        	$wangwang 	= I('get.wangwang');
            $id_list  = array(
                "toolbar"       => "customer_file_toolbar",
                "datagrid"      => "customer_file_datagrid",
                "tab_container" => "tab_container",
                "form"          => "customer_file_form",
                "add"           => "add_customer_file",
                "edit"          => "edit_customer_file",
                "SMS"           => "SMS_customer_file",
                "hidden_flag"   => "customer_file_hidden_flag",
                "more_button"   => "customer_file_more_button",
                "more_content"  => "customer_file_more_content",
                'fileDialog'    => 'customer_file_file_dialog',
                'fileForm'      => 'customer_file_file_form',
                'customer_flag' => 'customer_set_flag',
                'edit_class_dialog'=>'edit_class_dialog',
            );
            $datagrid = array(
                "id"      => $id_list["datagrid"],
                "options" => array(
                    "toolbar"      => $id_list["toolbar"],
                    "url"          => U("CustomerFile/getCustomerList").'?wangwang='.$wangwang,
                    "pagination"   => true,
                    "singleSelect" => false,
                    "ctrlSelect"   => true,
                    "fitColumns"   => false,
                    "rownumbers"   => true,
                    "method"       => "post"
                ),
                "fields"  => CustomerFields::getCustomerFields("CustomerFile")
            );
            //TODO arr_tabs,定义tabs的属性
            $arr_tabs = array(
                array(
                    "id"    => $id_list["tab_container"],
                    "title" => "客户地址",
                    "url"   => U("CustomerFile/getCustomerAddress")
                ),
                array(
                    "id"    => $id_list["tab_container"],
                    "title" => "电话号码",
                    "url"   => U("CustomerFile/getCustomerTelno")
                ),
                array(
                    "id"    => $id_list["tab_container"],
                    "title" => "平台客户",
                    "url"   => U("CustomerFile/getPlatformCustomer")
                ),
                array(
                    "id"    => $id_list["tab_container"],
                    "title" => "历史操作",
                    "url"   => U("CustomerFile/getOperatorRecord")
                ),
                array(
                    "id"    => $id_list["tab_container"],
                    "title" => "近期订单",
                    "url"   => U("CustomerFile/getRecentTrade")
                ),
                array(
                    "id"    => $id_list["tab_container"],
                    "title" => "近期退换",
                    "url"   => U("CustomerFile/getRecentExchange")
                )
            );
            $params   = array(
                "controller" => strtolower(CONTROLLER_NAME),
                "datagrid"   => array(
                    "id" => $id_list["datagrid"]
                ),
                "tabs"       => array(
                    "id" => $id_list["tab_container"]
                ),
                "add"        => array(
                    "id"     => $id_list["add"],
                    "title"  => "客户信息编辑",
                    "url"    => U("CustomerFile/addCustomer"),
                    "height" => 300,
                    "width"  => 650,
                    'ismax'  => false
                ),
                "edit"       => array(
                    "id"     => $id_list["edit"],
                    "title"  => "客户信息编辑",
                    "url"    => U("CustomerFile/editCustomer"),
                    "height" => 300,
                    "width"  => 650,
                    'ismax'  => false
                ),
                "SMS"        => array(
                    "id"    => $id_list["SMS"],
                    "title" => "短信发送",
                    "url"   => U("CustomerFile/SMS"),
                ),
                "search"     => array(
                    "form_id"      => $id_list["form"],
                    "hidden_flag"  => $id_list["hidden_flag"],
                    "more_button"  => $id_list["more_button"],
                    "more_content" => $id_list["more_content"]
                ),
                "customer_flag" => array(
                    "url"       => U("CustomerFile/getCustomerClassList"),
                    "id"        => $id_list["customer_flag"],
                    "title"     => "编辑客户标签",
                ),
                "edit_class_dialog" => array(
                    "url"       => U("CustomerFile/batchEditCustomerClass"),
                    "id"        => $id_list['edit_class_dialog'],
                    "title"     => "批量修改用户标签",
                )
            );
            $customer_class_field = UtilDB::getCfgList(array('customer_class'));
            $template_url =  U("CustomerFile/downloadTemplet");
            $this->assign('template_url',$template_url);
            $this->assign('customer_class', $customer_class_field['customer_class']);
            $this->assign("id_list", $id_list);
            $this->assign("datagrid", $datagrid);
            $this->assign("arr_tabs", json_encode($arr_tabs));
            $this->assign("params", json_encode($params));
            $this->display("show");
        }
    }

    /**
     * 停用
     * author:luyanfeng
     * table:crm_customer
     */
    public function updateDisabled() {
        $id = I("post.id");
        $M  = M();
        $M->startTrans();
        $result = Factory::getModel("CustomerFile")->updateDisabled($id);
        if ($result["status"] == 0) {
            $M->rollback();
            $res["status"] = 0;
            $res["info"]   = $result["info"];
        } else {
            $M->commit();
            $res["status"] = 1;
            $res["info"]   = "操作成功";
        }
        $this->ajaxReturn($res);
    }

    /**
     * 返回tabs：客户地址
     * autho:luyanfeng
     * table:crm_customer_address
     */
    public function getCustomerAddress() {
        if (IS_POST) {
            $id = I("post.id");
            $this->ajaxReturn(Factory::getModel("CustomerFile")->getCustomerAddress($id));
        } else {
            $type     = I("get.type");
            $type     = $type ? $type . "_" : "";
            $id_list  = array(
                "datagrid" => $type . "tabs_customer_address_datagrid"
            );
            $datagrid = array(
                "id"      => $id_list["datagrid"],
                "options" => array(
                    "url"          => U("CustomerFile/getCustomerAddress"),
                    "pagination"   => false,
                    "singleSelect" => true,
                    "fitColumns"   => false,
                    "rownumbers"   => true,
                    "method"       => "post"
                ),
                "fields"  => CustomerFields::getCustomerFields("CustomerAddress")
            );
            $this->assign("id_list", $id_list);
            $this->assign("datagrid", $datagrid);
            $this->display("tabs_customer_address");
        }
    }

    /**
     * 返回tabs：电话号码
     * author:luyanfeng
     * table:crm_customer_telno
     */
    public function getCustomerTelno() {
        if (IS_POST) {
            $id = I("post.id");
            $this->ajaxReturn(Factory::getModel("CustomerFile")->getCustomerTelno($id));
        } else {
            $type     = I("get.type");
            $type     = $type ? $type . "_" : "";
            $id_list  = array("datagrid" => $type . "tabs_customer_telno_datagrid");
            $datagrid = array(
                "id"      => $id_list["datagrid"],
                "options" => array(
                    "url"          => U("CustomerFile/getCustomerTelno"),
                    "pagination"   => false,
                    "singleSelect" => true,
                    "fitColumns"   => false,
                    "rownumbers"   => true,
                    "method"       => "post"
                ),
                "fields"  => CustomerFields::getCustomerFields("CustomerTelno")
            );
            $this->assign("id_list", $id_list);
            $this->assign("datagrid", $datagrid);
            $this->display("tabs_customer_telno");
        }
    }

    /**
     * 返回tabs:平台客户
     * author:luyanfeng
     * table:crm_platform_customer
     */
    public function getPlatformCustomer() {
        if (IS_POST) {
            $id = I("post.id");
            $this->ajaxReturn(Factory::getModel("CustomerFile")->getPlatformCustomer($id));
        } else {
            $id_list  = array("datagrid" => "tabs_platform_customer_datagrid");
            $datagrid = array(
                "id"      => $id_list["datagrid"],
                "options" => array(
                    "url"          => U("CustomerFile/getPlatformCustomer"),
                    "pagination"   => false,
                    "singleSelect" => true,
                    "fitColumns"   => false,
                    "rownumbers"   => true,
                    "method"       => "post"
                ),
                "fields"  => CustomerFields::getCustomerFields("PlatformCustomer")
            );
            $this->assign("id_list", $id_list);
            $this->assign("datagrid", $datagrid);
            $this->display("tabs_platform_customer");
        }
    }

    /**
     * 返回tabs:历史操作
     * author:luyanfeng
     * table:crm_customer_log
     */
    public function getOperatorRecord() {
        if (IS_POST) {
            $id = I("post.id");
            $this->ajaxReturn(Factory::getModel("CustomerFile")->getOperatorRecord($id));
        } else {
            $id_list  = array("datagrid" => "tabs_operator_record_datagrid");
            $datagrid = array(
                "id"      => $id_list["datagrid"],
                "options" => array(
                    "url"        => U("CustomerFile/getOperatorRecord"),
                    "pagination" => false,
                    "fitColumns" => false,
                ),
                "fields"  => CustomerFields::getCustomerFields("OperatorRecord")
            );
            $this->assign("id_list", $id_list);
            $this->assign("datagrid", $datagrid);
            $this->display("tabs_operator_record");
        }
    }

    /**
     * 返回tabs:近期订单
     * author:luyanfeng
     * table:sales_trade
     */
    public function getRecentTrade() {
        if (IS_POST) {
            $id = I("post.id");
            $this->ajaxReturn(Factory::getModel("CustomerFile")->getRecentTrade($id));
        } else {
            $id_list  = array("datagrid" => "tabs_recent_trade_datagrid");
            $datagrid = array(
                "id"      => $id_list["datagrid"],
                "options" => array(
                    "url"          => U("CustomerFile/getRecentTrade"),
                    "pagination"   => false,
                    "singleSelect" => true,
                    "fitColumns"   => false,
                    "rownumbers"   => true,
                    "method"       => "post"
                ),
                "fields"  => CustomerFields::getCustomerFields("RecentTrade")
            );
            $this->assign("id_list", $id_list);
            $this->assign("datagrid", $datagrid);
            $this->display("tabs_recent_trade");
        }
    }

    /**
     * 返回tabs:近期退换
     * author:luyanfeng
     * table:sales_refund
     */
    public function getRecentExchange() {
        if (IS_POST) {
            $id = I("post.id");
            $this->ajaxReturn(Factory::getModel("CustomerFile")->getRecentExchange($id));
        } else {
            $id_list  = array("datagrid" => "tabs_recent_exchange_datagrid");
            $datagrid = array(
                "id"      => $id_list["datagrid"],
                "options" => array(
                    "url"          => U("CustomerFile/getRecentExchange"),
                    "pagination"   => false,
                    "singleSelect" => true,
                    "fitColumns"   => false,
                    "rownumbers"   => true,
                    "method"       => "post"
                ),
                "fields"  => CustomerFields::getCustomerFields("RecentExchange")
            );
            $this->assign("id_list", $id_list);
            $this->assign("datagrid", $datagrid);
            $this->display("tabs_recent_exchange");
        }
    }

    /**
     * 创建新的客户档案
     * author:luyanfeng
     * table:crm_customer,crm_customer_address,crm_customer_telno,crm_customer_log
     */
    public function addCustomer() {
        //todo 新建客户档案
        if (IS_POST) {
            $data = I("post.data");
            if (!isset($data["nickname"])) {
                $res["status"] = 1;
                $res["msg"]    = "网名不能为空";
                $this->ajaxReturn($res);
            }
            if (!isset($data["customer_no"]) || $data["customer_no"] == "") {
                $data["customer_no"] = get_sys_no("customer");
            }
            if (!@$data["is_disabled"]) $data["is_disabled"] = 0;
            if (!@$data["is_black"]) $data["is_black"] = 0;
            try {
                $M = M();
                $M->startTrans();
                $result        = Factory::getModel("CustomerFile")->addCustomer($data);
                $res["status"] = 1;
                $res["info"]   = "操作成功";
                $M->commit();
            } catch (\Exception $e) {
                \Think\Log::write($e->getMessage());
                $M->rollback();
                $res["status"] = 0;
                $res["info"]   = "操作失败";
            }
            $this->ajaxReturn($res);
        } else {
            $id_list = array(
                "add"      => "add_customer_file",
                "form"     => "add_customer_form",
                "datagrid" => "customer_file_datagrid"
            );
            $customer_class_field = UtilDB::getCfgList(array('customer_class'));
            $this->assign("id_list", $id_list);
            $this->assign("class_name_add", $customer_class_field['customer_class']);
            $this->display("dialog_add");
        }
    }

    /**
     * 修改客户信息
     * author:luyanfeng
     * table:crm_customer,crm_customer_address,crm_customer_telno,crm_customer_log
     */
    public function editCustomer() {
        //todo 修改客户档案
        if (IS_POST) {
            $data = I("post.data");
            if (!isset($data["nickname"])) {
                $res["status"] = 0;
                $res["msg"]    = "网名不能为空";
                $this->ajaxReturn($res);
            }
            if (!isset($data["customer_no"]) || $data["customer_no"] == "") {
                $data["customer_no"] = get_sys_no("customer");
            }
            $data["is_black"]    = isset($data["is_black"]) ? $data["is_black"] : 0;
            $data["is_disabled"] = isset($data["is_disabled"]) ? $data["is_disabled"] : 0;
            try {
                //查看修改号码权限
                if (!UtilDB::checkNumber(array($data["id"]), 'crm_customer', get_operator_id(), null, 2)) {
                    unset($data['mobile']);
                    unset($data['telno']);
                }
                $M = M();
                $M->startTrans();
                Factory::getModel("CustomerFile")->updateCustomer($data);
                $M->commit();
                $res["status"] = 1;
                $res["info"]   = "操作成功";
            } catch (\PDOException $e) {
                $M->rollback();
                \Think\Log::write($e->getMessage());
                $res["status"] = 0;
                $res["info"]   = self::UNKNOWN_ERROR;
            } catch (\Exception $e) {
                $M->rollback();
                \Think\Log::write($e->getMessage());
                $res["status"] = 0;
                $res["info"]   = $e->getMessage();
            }
            $this->ajaxReturn($res);
        } else {
            $id = I("get.id");
            try {
                $customer = Factory::getModel("CustomerFile")->getCustomerFileById($id);
            } catch (\Exception $e) {
                \Think\Log::write($e->getMessage());
                $customer = array();
            }
            $id_list = array(
                "form"     => "edit_customer_file_form",
                "edit"     => "edit_customer_file",
                "datagrid" => "customer_file_datagrid"
            );
            if ($customer['right_flag'] === 0) {
                $id_list['right_flag'] = 0;
                unset($customer['right_flag']);
            } else {
                $id_list['right_flag'] = 1;
            }
            $customer_class_field = UtilDB::getCfgList(array('customer_class'));
            $this->assign("customer", $customer);
            $this->assign("class_name", $customer_class_field['customer_class']);
            $this->assign("id_list", $id_list);
            $this->display("dialog_edit");
        }
    }

    /**
     * 发送短信
     */
    public function SMS() {
        if (IS_POST) {
            try{
                $sms     = I("post.sms");
                $ids = $sms['ids'];
                $data = D('CustomerFile')->getRealCustomerMobil($ids);
                $mobiles = $data['mobile'];
                if(substr($mobiles,-1) == ','){
                    $mobiles = substr($mobiles,0,-1);
                }
                $message = $sms["message"];
                $id=D('Customer/CustomerFile')->addSMSList($mobiles,$message);
                $message=$message.' 回T退订';
                $res     = UtilTool::SMS($mobiles, $message, 'market');
                D('Customer/CustomerFile')->updateSMSStatus($id,$res);
            }catch (BusinessLogicException $e){
                $res['status']= 1;
                $res['info'] = $e->getMessage();
            }catch(\Exception $e){
                $res['status']= 1;
                $res['info'] = $e->getMessage();
            }
            $this->ajaxReturn($res);

        } else {
            $id_list  = array(
                "datagrid" => "customer_SMS_datagrid",
                "list"     => "customer_SMS_list",
                "message"  => "customer_SMS_message",
                "show"     => "customer_file_datagrid",
                "dialog"   => "SMS_customer_file"
            );
            $datagrid = array(
                "id"      => $id_list["datagrid"],
                "options" => array(
                    "pagination" => false,
                    "fitColums"  => false,
                    "rownumbers" => false,
                    "border"     => true
                ),
                "fields"  => D('Setting/UserData')->getDatagridField('Setting/SmsTemplate','SMS')
            );
            $template[] = array('id'=>'无','name'=>'无');
            $template_res = UtilDB::getCfgList(array("sms_template"),array('is_marketing'=>array('eq','1')));
            $template = array_merge($template,$template_res["sms_template"]);
            $this->assign('template',$template);
            $this->assign("id_list", $id_list);
            $this->assign("datagrid", $datagrid);
            $this->display("dialog_SMS");
        }
    }

    /**
     * @param int    $page
     * @param int    $rows
     * @param array  $search
     * @param string $sort
     * @param string $order
     * 返回客户档案列表
     * author:luyanfeng
     * table:crm_customer
     */
    public function getCustomerListDialog($page = 1, $rows = 20, $search = array(), $sort = "cc.customer_id", $order =
    "desc") {
        if (IS_POST) {
            try {
                $page  = intval($page);
                $rows  = intval($rows);
                $sort  = addslashes($sort);
                $order = addslashes($order);
                $where = "cc.is_disabled=0 ";
                foreach ($search as $k => $v) {
                    if (!isset($v)) continue;
                    $word = "AND";
                    switch ($k) {
                        case nickname:
                            set_search_form_value($where, $k, $v, "cc", 1, $word);
                            break;
                        case telno:
                            set_search_form_value($where, $k, $v, "cc", 1, ' AND (');
                            set_search_form_value($where, 'mobile', $v,'cc',1,' OR ',' ) ');
                            break;
                        case customer_no:
                            set_search_form_value($where, $k, $v, "cc", 1, $word);
                            break;
                        case name:
                            set_search_form_value($where, $k, $v, "cc", 1, $word);
                            break;
                        case wangwang:
                            set_search_form_value($where, $k, $v, "cc", 1, $word);
                            break;
                        case email:
                            set_search_form_value($where, $k, $v, "cc", 1, $word);
                            break;
                        default :
                            continue;
                    }
                }
                $where           = $where == "" ? $where : "where " . $where;
                $limit           = ($page - 1) * $rows . "," . $rows;
                $order           = $sort . " " . $order;
                $crm_customer_db = M("crm_customer");
                $cfg_show_telno  = get_config_value('show_number_to_star', 1);
                $sql             = "SELECT cc.customer_id as id,cc.customer_no,cc.type,cc.flag_id,cc.nickname,cc.name,cc.area,cc.country,cc.province,cc.city,cc.district,cc.address,
                        cc.zip,IF(" . $cfg_show_telno . "=0,cc.telno,INSERT( cc.telno,4,4,'****')) telno,IF(" . $cfg_show_telno . "=0,cc.mobile,INSERT( cc.mobile,4,4,'****')) mobile,cc.email,cc.qq,cc.remark,cc.wangwang,cc.sex,cc.birthday,cc.is_black,cc.logistics_id,cc.class_id,
                        cc.last_trade_time,cc.modified,cc.is_disabled,cc.created,ccc.class_name,cc.trade_count,cc.trade_amount,cc.profit
                        FROM crm_customer cc LEFT JOIN crm_customer_class ccc ON (ccc.class_id=cc.class_id) $where ORDER BY $order LIMIT $limit";
                $sql_count       = "SELECT COUNT(1) AS total FROM crm_customer cc $where";
                $result          = $crm_customer_db->query($sql_count);
                $data["total"]   = $result[0]["total"];
                $data["rows"]    = $crm_customer_db->query($sql);
            } catch (\Exception $e) {
                \Think\Log::write($e->getMessage());
                $data["total"] = 0;
                $data["rows"]  = "";
            }
            $this->ajaxReturn($data);
        } else {
            //todo 创建主页面
            $id_list  = array(
                "toolbar"       => "customer_file_dialog_toolbar",
                "datagrid"      => "customer_file_dialog_datagrid",
                "tab_container" => "dialog_tab_container",
                "form"          => "customer_file_dialog_form"
            );
            $datagrid = array(
                "id"      => $id_list["datagrid"],
                "options" => array(
                    "toolbar"      => $id_list["toolbar"],
                    "url"          => U("CustomerFile/getCustomerListDialog"),
                    "pagination"   => true,
                    "singleSelect" => false,
                    "ctrlSelect"   => true,
                    "fitColumns"   => false,
                    "rownumbers"   => true,
                    "method"       => "post"
                ),
                "fields"  => CustomerFields::getCustomerFields("CustomerFile")
            );
            //TODO arr_tabs,定义tabs的属性
            $arr_tabs = array(
                array(
                    "id"    => $id_list["tab_container"],
                    "title" => "客户地址",
                    "url"   => U("CustomerFile/getCustomerAddress") . "?type=dialog"
                ),
                array(
                    "id"    => $id_list["tab_container"],
                    "title" => "电话号码",
                    "url"   => U("CustomerFile/getCustomerTelno") . "?type=dialog"
                )
            );
            $params   = array(
                "controller" => strtolower(CONTROLLER_NAME),
                "datagrid"   => array("id" => $id_list["datagrid"]),
                "tabs"       => array("id" => $id_list["tab_container"]),
                "search"     => array("form_id" => $id_list["form"])
            );
            $this->assign("id_list", $id_list);
            $this->assign("datagrid", $datagrid);
            $this->assign("arr_tabs", json_encode($arr_tabs));
            $this->assign("params", json_encode($params));
            $this->display("show_dialog");
        }
    }

    public function getCustomerFull() {
        $customer_id = I("post.customer_id");
        $address_id  = I("post.address_id");
        $telno_id    = I("post.telno_id");
        try {
            $customerFile = D("CustomerFile")->getCustomerFileById($customer_id);
            if ($address_id) {
                $address                  = D("CustomerAddress")->getAddress($address_id);
                $customerFile["name"]     = $address["name"];
                $customerFile["country"]  = $address["country"];
                $customerFile["address"]  = $address["address"];
                $customerFile["province"] = $address["province"];
                $customerFile["city"]     = $address["city"];
                $customerFile["district"] = $address["district"];
                $customerFile["mobile"]   = $address["mobile"];
                $customerFile["telno"]    = $address["telno"];
            }
            if ($telno_id) {
                $telno = D("CustomerTelno")->getTelno($telno_id);
                if ($telno["type"] == 1) {
                    $customerFile["mobile"] = $telno["telno"];
                } else {
                    $customerFile["telno"] = $telno["telno"];
                }
            }
            $res["status"] = 1;
            $res["info"]   = $customerFile;
        } catch (\Exception $e) {
            \Think\Log::write($e->getMessage());
            $res["status"] = 0;
            $res["info"]   = $e->getMessage();
        }
        $this->ajaxReturn($res);
    }

    public function selectCustomer($page = 1, $rows = 20, $search = array(), $sort = "cc.customer_id", $order ="desc")
    {
        if(IS_POST)
        {
            $this->ajaxReturn(D('CustomerFile')->selectCustomer($page, $rows, $search, $sort , $order));
        }else
        {
            $params = I("get.");//获取params参数
            $id = $params['id'][0];
            $prefix = "customer_select_datagrid";//页面中元素ID的命名前缀
            if (isset($params["prefix"])) {
                $prefix = $params["prefix"];
            }
            $type = true;//是否包含子表
            if (isset($params["type"])  && $params["type"] == "false") {
                $type = "";
            }
            $fields = CustomerFields::getCustomerFields("AddCustomer");
            //根据$type确定页面中datagrid的高度
            if ($type) {
                $style = "height:300px";
            } else {
                $style = "height:430px";
            }
            $url = U("CustomerFile/selectCustomer");
            //根据$prefix完成表中各元素的命名
            $id_list = array(
                "datagrid"     => $prefix . "_customer_select_datagrid",
                "sub_datagrid" => $prefix . "_sub_customer_select_datagrid",
                "form"         => $prefix . "_customer_select_form",
                "hidden_flag"   => $prefix . "_hidden_flag",
                "more_button"   => $prefix . "_more_button",
                "more_content"  => $prefix . "_more_content",
                "toolbar"      => $prefix . "_customer_select_toolbar",
                "sub_toolbar"  => $prefix . "_sub_customer_select_toolbar",
                "class_id"     => $prefix . "_customer_class_id",
                "prefix"       => $prefix
            );
            //主表属性
            $datagrid = array(
                "id"      => $id_list["datagrid"],
                "style"   => $style,
                "class"    =>'',
                "options" => array(
                    "toolbar"    => $id_list["toolbar"],
                    "url"        => $url,
                    "fitColumns" => false,
                    "fit"        => true,
                    'singleSelect' => false,
                    'ctrlSelect'   => true
                ),
                "fields"  => $fields
            );
            $checkbox=array('field' => 'ck','checkbox' => true);
            array_unshift($datagrid['fields'],$checkbox);
            //子表属性
           /* $sub_datagrid = array(
                "id"      => $id_list["sub_datagrid"],
                "style"   => "height:130px",
                "options" => array(
                    "border"       => false,
                    "singleSelect" => true,
                    "pagination"   => false,
                    "rownumbers"   => true,
                    "fitColumns"   => true,
                    "fit"          => true,
                ),
                "fields"  => ''
            );    */
            //主表参数
            $params = array(
                "controller" => strtolower(CONTROLLER_NAME),
                "datagrid"   => array("id" => $id_list["datagrid"]),
                "search"     => array(
                    "form_id" => $id_list["form"],
                    "more_button"  => $id_list["more_button"],
                    "more_content" => $id_list["more_content"],
                    "hidden_flag"  => $id_list["hidden_flag"]
                )
            );
            $list   = UtilDB::getCfgList(array("brand","shop"), array("brand" => array("is_disabled" => 0)));
            $shop_list[] = array("id" => "all", "name" => "全部");
            $shop_list   = array_merge($shop_list, $list["shop"]);
            //向页面发送参数
            $this->assign('id',$id);
            $this->assign("goods_brand", $list["brand"]);
            $this->assign("shop_list", json_encode($shop_list));
            $this->assign("id_list", $id_list);
            $this->assign("params", json_encode($params));
            $this->assign("datagrid", $datagrid);
            $this->assign("type", $type);
            $this->assign("prefix", $prefix);
            //如果$type为真，则发送子表的属性
            /*if ($type) {
                $this->assign("sub_datagrid", $sub_datagrid);
            }*/
            $this->display('select_customer');
        }

    }

    //下载单品导入模板
    public function downloadTemplet(){
        $file_name = "黑名单导入模板.xls";
        $file_sub_path = APP_PATH."Runtime/File/";
        try{
            ExcelTool::downloadTemplet($file_name,$file_sub_path);
        } catch (BusinessLogicException $e){
            echo '对不起，模板不存在，下载失败！';
        } catch (\Exception $e) {
            \Think\Log::write($e->getMessage());
            echo parent::UNKNOWN_ERROR;
        }
    }

    //导入黑名单客户
    public function uploadBlackListExcel()
    {
        if(!self::ALLOW_EXPORT){
            $res["status"] = 1;
            $res["info"]   = self::EXPORT_MSG;
            $this->ajaxReturn(json_encode($res), "EVAL");
        }
        //获取表格相关信息
        $file = $_FILES["file"]["tmp_name"];
        $name = $_FILES["file"]["name"];
        try{
            $excelClass = new ExcelTool();
            $excelClass->checkExcelFile($name,$file);
            $excelClass->uploadFile($file,"BlackListImport");
            $count = $excelClass->getExcelCount();
            //建立临时表，存储数据处理的结果
            $excelData = $excelClass->Excel2Arr($count);
            //如果tmp_import_detail表已存在，就删除并重新创建
            $res = array('status'=>0,'info'=>'导入成功');
            //处理数据，将数据插入数据库并返回信息
            $crm_db = D("CustomerFile");
            $excel_black_list = '';
            foreach ($excelData as $sheet) {
                for ($k = 1; $k < count($sheet); $k++) {
                    $row = $sheet[$k];
                    //在excel里的行号
                    $line = $k+1;
                    if (UtilTool::checkArrValue($row)) continue;
                    //分类存储数据
                    $i = 0;
                    $data['customer']['nickname'] = $row[$i++];//网名
                    $data['customer']['mobile'] = $row[$i++];//手机号
                    $data['customer']['province'] = $row[$i++];//省份
                    $data['customer']['city'] = $row[$i++];//城市
                    $data['customer']['district'] = $row[$i++];//县区
                    $data['customer']['address'] = $row[$i++];//县区
                    $data['customer']['customer_no'] = $row[$i++];//客户编号
                    $data['customer']['name'] = $row[$i++];//客户姓名
                    $data['customer']['sex'] = $row[$i++];//客户性别
                    $data['customer']['zip'] = $row[$i++];//邮编
                    $data['customer']['telno'] = $row[$i++];//固话
                    $data['customer']['email'] = $row[$i++];//电子邮箱
                    $data['customer']['qq'] = $row[$i++];//qq
                    $data['customer']['wangwang'] = $row[$i++];//旺旺
                    $data['customer']['birthday'] = $row[$i++];//生日
                    $data['line'] = $line;//行号
                    $excel_black_list[] = $data;
                }
            };
            $error_list = array();
            $crm_db -> importBlackList($excel_black_list,$error_list);
            if(count($error_list)>0){
                $res['status'] = 2;
                $res['info'] = $error_list;
            }
        }catch (\Exception $e){
            \Think\Log::write($e->getMessage());
            $res["status"] = 1;
            $res["info"]   = "未知错误，请联系管理员";
            $this->ajaxReturn(json_encode($res), "EVAL");
        }
        unset($data);
        $this->ajaxReturn(json_encode($res), "EVAL");
    }
    public function getCustomerClassList()
    {
        $id_list = array(
            "toolbar" => "customer_class_toolbar",
            "datagrid" => "customer_class_datagrid",
            "form" => "customer_class_form",
            "hidden_flag" => "customer_class_hidden_flag",
        );
        $data = D("CustomerClass")->getCustomerClassList();
        $this->assign("id_list", $id_list);
        $this->assign("customer_class_list", json_encode($data));
        $this->display("CustomerFile/dialog_set_class");
    }
    public function saveCustomerClass()
    {
        $data = I('post.');
        if(!empty($data['delete'])){
            foreach($data['delete'] as $k=>$v){
                if($v['id'] == 0){
                    $result = array('status' => 2,'info' => '操作失败,不能删除系统内置标签“无”');
                    $this->ajaxReturn($result);
                }
            }
        }
        $CustomerClassDB = D('CustomerClass');
        $M = M();
        $M->startTrans();
        $result = array('status' => 0,'info' => '操作失败');
        try{
            if(!empty($data['update'])){
                $CustomerClassDB->updateCustomerClass($data['update']);
            }
            if(!empty($data['delete'])){
                $CustomerClassDB->deleteCustomerClass($data['delete']);
            }
            if(!empty($data['add'])){
                $CustomerClassDB->addCustomerClass($data['add']);
            }
            $M->commit();
            $result = array('status' => 0,'info' => '操作成功');
        }catch (\Exception $e) {
            $M->rollback();
            \Think\Log::write($e->getMessage());
            $result = array('status' => 1,'info' => '操作失败，请联系管理员');
        }
        $this->ajaxReturn($result);
    }
    public function batchEditCustomerClass(){
        if(IS_POST){
            $id_list = I('post.customer_id');
            $class_id = I('post.class_id');
            try{
                $result = D('CustomerClass')->batchEditCustomerClass($id_list, $class_id);
            }catch (\Exception $e) {
                \Think\Log::write($e->getMessage());
                $result = array('status' => 1,'info' => '操作失败，请联系管理员');
            }
            $this->ajaxReturn($result);
        }else{
            $rec_id = I('get.id');
            $id_list = array(
                'form_id' => 'batch_edit_customer_class_form',
                'rec_id'  => $rec_id,
            );
            $customer_class_field = UtilDB::getCfgList(array('customer_class'));
            $this->assign('id_list', $id_list);
            $this->assign('customer_class', $customer_class_field['customer_class']);
            $this->display('dialog_batch_edit_class');
        }

    }

}