<?php
namespace Customer\Model;

use Think\Exception\BusinessLogicException;
use Think\Model;
use Common\Common\UtilDB;

class CustomerFileModel extends Model {

    protected $tableName = "crm_customer";
    protected $pk        = "goods_id";

    /**
     * @param int    $page
     * @param int    $rows
     * @param array  $search
     * @param string $sort
     * @param string $order
     * @return mixed
     * 返回客户档案列表数据
     * author:luyanfeng
     */
    public function getCustomerList($page = 1, $rows = 20, $search = array(), $sort = "cc.customer_id", $order = "desc") {
        try {
            $page  = intval($page);
            $rows  = intval($rows);
            $sort  = addslashes($sort);
            $order = addslashes($order);
            $where = " true ";
            foreach ($search as $k => $v) {
                if ($v === "") continue;
                $word = "AND";
                switch ($k) {
                    case 'nickname':
                        set_search_form_value($where, $k, $v, "cc", 1, $word);
                        break;
                    case 'telno':
                        set_search_form_value($where, $k, $v, "cc", 1, $word.' (');
                        set_search_form_value($where, 'mobile', $v,'cc',1,' OR ',' ) ');
                        break;
                    case 'customer_no':
                        set_search_form_value($where, $k, $v, "cc", 1, $word);
                        break;
                    case 'name':
                        set_search_form_value($where, $k, $v, "cc", 1, $word);
                        break;
                    case 'wangwang':
                        set_search_form_value($where, $k, $v, "cc", 1, $word);
                        break;
                    case 'email':
                        set_search_form_value($where, $k, $v, "cc", 1, $word);
                        break;
                    case 'is_black':
                        set_search_form_value($where, $k, $v, "cc", 2, $word);
                        break;
                    case 'class_id':
                        set_search_form_value($where, $k, $v, "cc", 2, $word);
                        break;
                    default :
                        continue;
                }
            }
            $where = $where == "" ? $where : "where " . $where;
            $limit = ($page - 1) * $rows . "," . $rows;
            $order = $sort . " " . $order;
            //先查询出需要在页面显示的客户的customer_id
            $sql_result = "SELECT cc.customer_id FROM crm_customer cc LEFT JOIN crm_customer_class ccc ON (ccc.class_id=cc.class_id) $where ORDER BY $order LIMIT $limit";

            //再构造SQL查询完整的数据
            $cfg_show_telno = get_config_value('show_number_to_star', 1);
            $sql            = "SELECT cc_1.customer_id as id,cc_1.customer_no,cc_1.type,cc_1.flag_id,cc_1.nickname,cc_1.name,cc_1.area,cc_1.country,cc_1.province,cc_1.city,cc_1.district,cc_1.address,cc_1.trade_count,cc_1.trade_amount,cc_1.refund_amount,cc_1.profit ,
                        cc_1.zip,IF(" . $cfg_show_telno . "=0,cc_1.telno,INSERT( cc_1.telno,4,4,'****')) telno,IF(" . $cfg_show_telno . "=0,cc_1.mobile,INSERT( cc_1.mobile,4,4,'****')) mobile,cc_1.email,cc_1.qq,cc_1.remark,cc_1.wangwang,cc_1.sex,cc_1.birthday,cc_1.is_black,cc_1.logistics_id,cc_1.class_id,
                        cc_1.last_trade_time,cc_1.modified,cc_1.is_disabled,cc_1.created,ccc.class_name FROM crm_customer cc_1
                        INNER JOIN ( " . $sql_result . " ) cc_2 ON (cc_1.customer_id=cc_2.customer_id)
                        LEFT JOIN crm_customer_class ccc ON (ccc.class_id=cc_1.class_id)";
            $sql_count      = "SELECT COUNT(1) AS total FROM crm_customer cc $where";
            $result         = $this->query($sql_count);
            $data["total"]  = $result[0]["total"];
            $data["rows"]   = $this->query($sql);
        } catch (\Exception $e) {
            \Think\Log::write($e->getMessage());
            $data["total"] = 0;
            $data["rows"]  = "";
        }
        return $data;
    }

    /**
     * @param $id
     * @return mixed
     * 停用客户
     * author:luyanfeng
     */
    public function updateDisabled($id) {
        try {
            $id_list = implode(",",$id);
            $sql = "UPDATE crm_customer cc SET cc.is_disabled=1 WHERE cc.customer_id IN ({$id_list})";
            $this->execute($sql);
            $sql                 = "SELECT cc.name,cc.customer_id AS id FROM crm_customer cc WHERE cc.customer_id IN ({$id_list})";
            $log_arr                = $this->query($sql);
            $sid = get_operator_id();
            $crm_customer_log_tb = M("crm_customer_log");
            foreach($log_arr as $k=>$v){
                $arr_crm_log         = array(
                    "customer_id" => $v['id'],
                    "operator_id" => $sid,
                    "type"        => 2,
                    "message"     => "停用--" . $v['name'],
                    "created"     => date("Y-m-d G:i:s")
                );
                $crm_customer_log_tb->data($arr_crm_log)->add();
            }
            $res["status"] = 1;
            $res["info"]   = "操作成功";
        } catch (\Exception $e) {
            \Think\Log::write($e->getMessage());
            $res["status"] = 0;
            $res["info"]   = "未知错误，请联系管理员";
        }
        return $res;
    }

    /**
     * @param $id
     * @return mixed
     * 获取客户地址
     * author:luyanfeng
     */
    public function getCustomerAddress($id) {
        try {
            $cfg_show_telno = get_config_value('show_number_to_star', 1);
            $sql            = "SELECT cca.rec_id as id,cca.name,cca.province,cca.city,cca.district,cca.address,cca.zip,IF(" . $cfg_show_telno . "=0,cca.mobile,INSERT( cca.mobile,4,4,'****')) mobile,
                    IF(" . $cfg_show_telno . "=0,cca.telno,INSERT( cca.telno,4,4,'****')) telno,cca.email,cca.modified,cca.created FROM crm_customer_address cca WHERE cca.customer_id=%d";
            $sql_count      = "SELECT COUNT(1) AS total FROM crm_customer_address cca WHERE cca.customer_id=%d";
            $result         = $this->query($sql_count, $id);
            $data["total"]  = $result[0]["total"];
            $data["rows"]   = $this->query($sql, $id);
        } catch (\Exception $e) {
            \Think\Log::write($e->getMessage());
            $data["total"] = 0;
            $data["rows"]  = "";
        }
        return $data;
    }

    /**
     * @param $id
     * @return mixed
     * 获取客户电话号码
     * author:luyanfeng
     */
    public function getCustomerTelno($id) {
        try {
            $cfg_show_telno = get_config_value('show_number_to_star', 1);
            $sql            = "SELECT rec_id as id,type,IF(" . $cfg_show_telno . "=0,telno,INSERT(telno,4,4,'****')) telno,modified,created FROM crm_customer_telno WHERE customer_id=%d AND is_disabled=0";
            $sql_count      = "SELECT COUNT(1) AS total FROM crm_customer_telno WHERE customer_id=%d AND is_disabled=0";
            $result         = $this->query($sql_count, $id);
            $data["total"]  = $result[0]["total"];
            $data["rows"]   = $this->query($sql, $id);
        } catch (\Exception $e) {
            \Think\Log::write($e->getMessage());
            $data["total"] = 0;
            $data["rows"]  = "";
        }
        return $data;
    }

    /*
     * 发送短信时获取客户真实的电话号码（不显示*号）
     */
    public function getRealCustomerMobil($id){
        $res = array();
        try{
            $data=$this->field('mobile')->where(array('customer_id'=>array('in',$id)))->select();
            foreach($data as $v){
                $res['mobile'] .= $v['mobile'].',';
            }
        }catch (\PDOException $e){
            \Think\Log::write($e->getMessage());
        }
        return $res;
    }

    /**
     * @param $id
     * @return mixed
     * 获取平台客户信息
     * author:luyanfeng
     */
    public function getPlatformCustomer($id) {
        try {
            $sql           = "SELECT rec_id as id,platform_id,account,created FROM crm_platform_customer WHERE customer_id=%d";
            $sql_count     = "SELECT COUNT(1) AS total FROM crm_platform_customer WHERE customer_id=%d";
            $result        = $this->query($sql_count, $id);
            $data["total"] = $result[0]["total"];
            $data["rows"]  = $this->query($sql, $id);
        } catch (\Exception $e) {
            \Think\Log::write($e->getMessage());
            $data["total"] = 0;
            $data["rows"]  = "";
        }
        return $data;
    }

    /**
     * @param $id
     * @return mixed
     * 获取操作记录
     * author:luyanfeng
     */
    public function getOperatorRecord($id) {
        try {
            $sql           = "SELECT ccl.rec_id AS id,ccl.message,ccl.created,he.fullname FROM crm_customer_log ccl
                    LEFT JOIN hr_employee he ON(ccl.operator_id=he.employee_id) WHERE ccl.customer_id=%d ORDER BY id DESC";
            $sql_count     = "SELECT COUNT(1) AS total FROM crm_customer_log ccl WHERE ccl.customer_id=%d";
            $result        = $this->query($sql_count, $id);
            $data["total"] = $result[0]["total"];
            $data["rows"]  = $this->query($sql, $id);
        } catch (\Exception $e) {
            \Think\Log::write($e->getMessage());
            $data["total"] = 0;
            $data["rows"]  = "";
        }
        return $data;
    }

    /**
     * @param $id
     * @return mixed
     * 获取近期订单
     * author:luyanfeng
     */
    public function getRecentTrade($id) {
        try {
            $cfg_show_telno = get_config_value('show_number_to_star', 1);
            $sql            = "SELECT trade_id as id,st.trade_no,st.platform_id,cs.shop_name,cw.name,st.warehouse_type,st.
                        src_tids,st.trade_status,st.consign_status,st.trade_from,st.trade_type,st.delivery_term,cor.title,st.refund_status,
                        st.unmerge_mask,st.fenxiao_type,st.fenxiao_nick,st.trade_time,st.pay_time,cl.logistics_name,
                        if(st.platform_id>0,st.pay_account,'') as pay_account,customer_id,st.goods_count,st.goods_type_count,st.
                        buyer_nick,st.receiver_name,st.receiver_country,st.receiver_province,st.receiver_city,st.receiver_district,st.
                        receiver_address,IF(" . $cfg_show_telno . "=0,st.receiver_mobile,INSERT( st.receiver_mobile,4,4,'****')) receiver_mobile,IF(" . $cfg_show_telno . "=0,st.receiver_telno,INSERT(st.receiver_telno,4,4,'****')) receiver_telno,
                        st.receiver_zip,st.receiver_area,st.receiver_ring,st.receiver_dtb,st.to_deliver_time,st.logistics_id,st.logistics_no,st.buyer_message,st.cs_remark,st.
                        remark_flag,st.print_remark,st.note_count,st.buyer_message_count,st.cs_remark_count,st.goods_amount,st.
                        post_amount,st.other_amount,st.discount,st.receivable,st.discount_change,st.dap_amount,st.cod_amount,st.ext_cod_fee,
                        st.pi_amount,st.commission,st.profit FROM sales_trade st
                        LEFT JOIN cfg_shop cs ON(st.shop_id=cs.shop_id)
                        LEFT JOIN cfg_warehouse cw ON(cw.warehouse_id=st.warehouse_id)
                        LEFT JOIN cfg_oper_reason cor ON(cor.reason_id=st.freeze_reason)
                        LEFT JOIN cfg_logistics cl ON(cl.logistics_id=st.logistics_id)
                        WHERE st.customer_id=%d AND st.created>DATE_ADD(now(),INTERVAL -3 MONTH) AND st.trade_status<120";
            $sql_count      = "SELECT COUNT(1) AS total FROM sales_trade WHERE customer_id=%d";
            $result         = $this->query($sql_count, $id);
            $data["total"]  = $result[0]["total"];
            $data["rows"]   = $this->query($sql, $id);
        } catch (\Exception $e) {
            \Think\Log::write($e->getMessage());
            $data["total"] = 0;
            $data["rows"]  = "";
        }
        return $data;
    }

    /**
     * @param $id
     * @return mixed
     * 获取近期退换单
     * author:luyanfeng
     */
    public function getRecentExchange($id) {
        try {
            $cfg_show_telno = get_config_value('show_number_to_star', 1);
            $sql            = "SELECT r.refund_id as id,r.refund_no,r.src_no,r.platform_id,r.shop_id,
                        r.type,r.process_status,r.status,r.sync_status,cs.shop_name,
                        r.sync_result,r.pay_account,r.pay_no,r.actual_refund_amount,r.refund_amount,
                        r.post_amount,r.other_amount,r.guarante_refund_amount,r.direct_refund_amount,
                        r.goods_amount,r.exchange_amount,r.tid,r.logistics_name,
                        r.logistics_no,r.buyer_nick,r.receiver_name,r.receiver_address,r.return_address,
                        r.refund_time,r.from_type,r.reason_id,r.flag_id,r.remark,
                        r.customer_id,r.operator_id,r.trade_id,r.warehouse_type,
                        r.warehouse_id,r.wms_status,r.wms_result,r.outer_no,r.swap_trade_id,
                        r.note_count,IF(" . $cfg_show_telno . "=0,r.return_mobile,INSERT( r.return_mobile,4,4,'****')) return_mobile,IF(" . $cfg_show_telno . "=0,r.return_telno,INSERT( r.return_telno,4,4,'****')) return_telno,r.created,he.fullname,cw.name,cor.title
                        FROM sales_refund r
                        LEFT JOIN cfg_shop cs ON(r.shop_id=cs.shop_id)
                        LEFT JOIN hr_employee he ON(he.employee_id=r.operator_id)
                        LEFT JOIN cfg_warehouse cw ON(cw.warehouse_id=r.warehouse_id)
                        LEFT JOIN cfg_oper_reason cor ON(cor.reason_id=r.reason_id)
                        WHERE r.customer_id=%d AND r.created>DATE_ADD(now(),INTERVAL -3 MONTH)";
            $sql_count      = "SELECT COUNT(1) AS total FROM sales_refund r WHERE r.customer_id=%d";
            $result         = $this->query($sql_count, $id);
            $data["total"]  = $result[0]["total"];
            $data["rows"]   = $this->query($sql, $id);
        } catch (\Exception $e) {
            \Think\Log::write($e->getMessage());
            $data["total"] = 0;
            $data["rows"]  = "";
        }
        return $data;
    }

    //新建客户档案
    public function addCustomer($data,$check=true) {
        try {
            //判断该客户编号是否已存在，如果存在则不允许创建
            if($check)
            {
                $customer_no = $data["customer_no"];
                if (count($this->query("SELECT customer_id FROM crm_customer WHERE customer_no='%S'", $customer_no))) {
                    E("客户编号已存在");
                }
            }
            //判断客户生日是否符合日期格式
            $date             = strtotime($data["birthday"]);
            $data["birthday"] = $date ? date("Y-m-d G:i:s", $date) : "00-00-00 00:00:00";
            //创建客户信息
            $data["created"] = date("Y-m-d G:i:s");
            $customer_id     = $this->data($data)->add();
            //如果地址不为空，创建一组地址信息
            if ($data["address"] != "" || $data["province"] != "" || $data["city"] != "" || $data["district"] != "") {
                $customer_address              = array(
                    "customer_id" => $customer_id,
                    "name"        => $data["name"],
                    "country"     => 0,
                    "province"    => $data["province"],
                    "city"        => $data["city"],
                    "district"    => $data["district"],
                    "area"        => $data["area"],
                    "address"     => $data["address"],
                    "zip"         => $data["zip"],
                    "telno"       => $data["telno"],
                    "mobile"      => $data["mobile"],
                    "email"       => $data["email"],
                    "is_disabled" => $data["is_disabled"],
                    "modified"    => date("Y-m-d G:i:s"),
                    "created"     => date("Y-m-d G:i:s")
                );
                $customer_address["addr_hash"] = md5($customer_address["province"] . $customer_address["city"] . $customer_address["district"] . $customer_address["address"]);
                M("crm_customer_address")->data($customer_address)->add();
            }
            //如果手机号不为空，创建手机信息
            if (!empty($data["mobile"])) {
                $customer_mobile = array(
                    "customer_id" => $customer_id,
                    "type"        => 1,
                    "telno"       => $data["mobile"],
                    "is_disabled" => $data["is_disabled"],
                    "modified"    => date("Y-m-d G:i:s"),
                    "created"     => date("Y-m-d G:i:s")
                );
                M("crm_customer_telno")->data($customer_mobile)->add();
            }
            //如果电话号码不为空，创建电话信息
            if (!empty($data["telno"])) {
                $customer_telno        = array(
                    "customer_id" => $customer_id,
                    "type"        => 2,
                    "telno"       => $data["telno"],
                    "is_disabled" => $data["is_disabled"],
                    "modified"    => date("Y-m-d G:i:s"),
                    "created"     => date("Y-m-d G:i:s")
                );
                $crm_customer_telno_tb = M("crm_customer_telno");
                $crm_customer_telno_tb->data($customer_telno)->add();
            }
            //创建操作日志
            $customer_log_tb  = M("crm_customer_log");
            $uid              = get_operator_id();
            $arr_customer_log = array(
                "customer_id" => $customer_id,
                "operator_id" => $uid,
                "type"        => 1,
                "message"     => "新建客户信息",
                "created"     => date("Y-m-d G:i:s")
            );
            $customer_log_tb->data($arr_customer_log)->add();
            $res["status"] = 1;
            $res["info"]   = "创建成功";
            return $res;
        } catch (\PDOException $e) {
            \Think\Log::write($e->getMessage());
            E("未知错误，请联系管理员");
        } catch (\Exception $e) {
            E($e->getMessage());
        }
    }


    /**
     * @param        $value
     * @param string $name
     * @return bool
     * 查询客户是否存在
     * author:luyanfeng
     */
    public function checkCustomer($value, $name = "customer_id") {
        $map[$name] = $value;
        try {
            $result = $this->field('customer_id')->where($map)->find();
            if (!empty($result)) return true;
        } catch (\Exception $e) {
            \Think\Log::write($e->getMessage());
        }
        return false;
    }


    public function updateCustomer($data) {
        try {
            //如果没有id无法更新，提示错误
            if (!@$data["id"]) E("未知错误，请联系管理员");
            $data["customer_id"] = $data["id"];
            unset($data["id"]);
            //检查客户编号是否重复
            $customer_no = $data["customer_no"];
            $sql         = "SELECT customer_id FROM crm_customer WHERE customer_no='%s' AND customer_id<>%d";
            if (count($this->query($sql, array($customer_no, $data["customer_id"]))) != 0) {
                E("该客户编号已存在");
            }
            if (!@$data["nickname"]) {
                E("客户网名不能为空");
            }
            //判断客户生日是否符合日期格式
            $date             = strtotime($data["birthday"]);
            $data["birthday"] = $date ? date("Y-m-d G:i:s", $date) : "00-00-00 00:00:00";
            //获取原来的数据
            $oldData = $this->query("SELECT customer_id,customer_no,nickname,name,sex,birthday,mobile,telno,wangwang,qq,email,type,address,province,city,district,
                  zip,logistics_id,remark,is_black,is_disabled FROM crm_customer WHERE customer_no='%s'", $customer_no);
            $oldData = $oldData[0];
            //保存更新后的数据
            $this->data($data)->save();

            //创建日志
            $crm_customer_log_tb = M("crm_customer_log");
            $uid                 = get_operator_id();
            $arr_customer_log    = array(
                "customer_id" => $data["customer_id"],
                "operator_id" => $uid,
                "type"        => 2,
                "message"     => "更新客户信息",
                "created"     => date("Y-m-d G:i:s")
            );
            $crm_customer_log_tb->data($arr_customer_log)->add();
            //更新客户地址，如果联合索引已存在，则不插入，否则插入

            $customer_address_tb = M("crm_customer_address");

            //插入数据
            $data_address              = array(
                "customer_id" => $data["customer_id"],
                "name"        => $data["name"],
                "country"     => 0,
                "province"    => $data["province"],
                "city"        => $data["city"],
                "district"    => $data["district"],
                "area"        => $data["area"],
                "address"     => $data["address"],
                "zip"         => $data["zip"],
                "telno"       => set_default_value($data["telno"], ''),
                "mobile"      => set_default_value($data["mobile"], ''),
                "email"       => $data["email"],
                "is_disabled" => $data["is_disabled"],
                "modified"    => date("Y-m-d G:i:s"),
                "created"     => date("Y-m-d G:i:s")
            );
            $data_address["addr_hash"] = md5($data["province"] . $data["city"] . $data["district"] . $data["address"]);
            $sql                       = "SELECT cca.rec_id FROM crm_customer_address cca WHERE cca.name='%s' AND cca.addr_hash='%s' AND cca.customer_id=%d";
            if (count($customer_address_tb->query($sql, array($data_address["name"], $data_address["addr_hash"], $data_address["customer_id"]))) == 0) {
                $customer_address_tb->data($data_address)->add();
            }
            //更新电话号码
            //检查手机是否改变
            $crm_customer_telno_tb = M("crm_customer_telno");
            if (!empty($data["mobile"]) && $data["mobile"] != $oldData["mobile"]) {
                $data_mobile = array(
                    "customer_id" => $data["customer_id"],
                    "type"        => 1,
                    "telno"       => $data["mobile"],
                    "is_disabled" => $data["is_disabled"],
                    "modified"    => date("Y-m-d G:i:s"),
                    "created"     => date("Y-m-d G:i:s")
                );
                $sql         = "SELECT cct.rec_id FROM crm_customer_telno cct WHERE cct.type=1 AND cct.telno='%s' AND cct.customer_id=%d";
                if (count($crm_customer_telno_tb->query($sql, array($data_mobile["telno"], $data_mobile["customer_id"]))) == 0) {
                    $crm_customer_telno_tb->data($data_mobile)->add();
                }
            }
            //检查电话号码是否改变
            if (!empty($data["telno"]) && $data["telno"] != $oldData["telno"]) {
                $data_telno = array(
                    "customer_id" => $data["customer_id"],
                    "type"        => 2,
                    "telno"       => $data["telno"],
                    "is_disabled" => $data["is_disabled"],
                    "modified"    => date("Y-m-d G:i:s"),
                    "created"     => date("Y-m-d G:i:s")
                );
                $sql        = "SELECT cct.rec_id FROM crm_customer_telno cct WHERE cct.type=2 AND cct.telno='%s' AND cct.customer_id=%d";
                if (count($crm_customer_telno_tb->query($sql, array($data_telno["telno"], $data_telno['customer_id']))) == 0) {
                    $crm_customer_telno_tb->data($data_telno)->add();
                }
            }
            $res["status"] = 1;
            $res["info"]   = "操作成功";
            return $res;
        } catch (\PDOException $e) {
            \Think\Log::write($e->getMessage());
            E("未知错误，请联系管理员");
        } catch (\Exception $e) {
            E($e->getMessage());
        }
    }

    /**
     * @param $id
     * @return mixed
     * 获取客户信息
     * author:luyanfeng
     */
    public function getCustomerFileById($id) {
        try {
            $sql      = "SELECT cc.customer_id as id,cc.customer_no,cc.nickname,cc.name,cc.sex,cc.birthday,
                    cc.mobile,cc.telno,cc.wangwang,cc.qq,cc.email,cc.type,cc.province,cc.city,cc.district,
                    cc.address,cc.zip,cc.score,cc.logistics_id,cc.class_id,
                    cc.remark,cc.is_black,cc.is_disabled FROM crm_customer cc WHERE cc.customer_id=%d";
            $customer = $this->query($sql, $id);
            //查看号码日志
            $cfg_show_telno = get_config_value('show_number_to_star', 1);
            if ($cfg_show_telno == 1) {
                $right_flag = UtilDB::checkNumber(array($id), "crm_customer", get_operator_id());
                if ($right_flag == false) {
                    $customer[0]['right_flag'] = 0;
                    $customer[0]['mobile']     = empty($customer[0]['mobile'])?$customer[0]['mobile']:substr_replace($customer[0]['mobile'], '*****', 3, 4);
                    $customer[0]['telno']      = empty($customer[0]['telno'])?$customer[0]['telno']:substr_replace($customer[0]['telno'], '*****', 3, 4);
                }
            }
        } catch (\PDOException $e) {
            \Think\Log::write($e->getMessage());
            E("未知错误，联系管理员");
            $res = array();
        } catch (\Exception $e) {
            E($e->getMessage());
            $customer = array();
        }
        return $customer[0];
    }

    /*发送短信时添加记录信息
	 * $mobile array  电话号码的数组
	 * $msg   string  发送的内容
	 * 返回值为 新添加数据的id
	 * */
    function addSMSList($mobile,$msg){
        try {
            $one_len=66;
            if(substr($mobile,'-1')==','){
                $mobiles=substr($mobile,0,strlen($mobile)-1);
                $mobile_arr=explode(',',$mobiles);
                $count = count($mobile_arr);
            }else{
                $mobiles=$mobile;
                $count=1;
            }
            if(empty($mobiles)){
                SE('手机号码不能为空');
            }
            $len=mb_strlen($msg);
            $unit=$len%$one_len>0?($len/$one_len)+1:$len/$one_len;
            $user_id = get_operator_id();
            $model = M('crm_sms_record');
            $batch_no=$model->query("SELECT FN_SYS_NO('sms')");
            $batch_no = $batch_no[0]["fn_sys_no('sms')"];
            $add = array(
                'status' => 0,
                'sms_type' => 0,
                'operator_id' => $user_id,
                'phone_num' => $count,
                'phones' => $mobiles,
                'message' => $msg,
                'send_time' => date('Y-m-d H:i:s', time()),
                'batch_no' => $batch_no,
                'created' => date('Y-m-d H:i:s', time()),
                'pre_count' => $unit*$count,
                'success_people' => $count,
                'success_count' => $unit*$count,
                'send_type' => 0 //营销短信
            );
            $result = $model->add($add);
            $return=array(
                'id'=>$result,
                'unit'=>$unit
            );
            return $return;
        }catch (BusinessLogicException $e){
            SE($e->getMessage());
        }catch (\PDOException $e) {
            $msg = $e->getMessage();
            \Think\Log::write($this->name."-addSMSList-".$msg);
            SE(self::PDO_ERROR);
        }
    }

    /*发送短信后对记录信息进行修改
     * $id   array    修改记录数据（id 这次短信发送的记录id，unit 每个人要发送的短信单位条数）
     * $msg  array  发送短信后返回值（msg 提示信息，code 短信发送情况 0为成功）
     * 返回值 无
     * 特殊强调：$msg['msg']='成功10条，失败 0 条';  按空格截取字符串获取key值为1的为失败的条数
     * */
    function updateSMSStatus($id,$msg){
        try {
            $model = M('crm_sms_record');
            $where['rec_id'] = $id['id'];
            $result=$model->where($where)->find();
            $error_count=0;
            $status=2;
            $add_try_time=0;
            $error_msg='发送成功';
            if($msg['status'] != 0) {
                $info=explode(" ",$msg['info']);
                if(!$info[1]){
                    $error_count=$result['success_count'];
                }else{
                    $error_count=$info[1];
                }
                $error_count = empty($error_count)?$result['pre_count']:$error_count;
                $status=3;
                $add_try_time=1;
                $error_msg=$msg['info'];
            }
            $save = array(
                'error_msg' => $error_msg,
                'try_times' => (int)$result['try_times']+$add_try_time,
                'success_people'=>$result['success_people']-$error_count/$id['unit'],
                'success_count'=>$result['success_count']-$error_count,
                'status' => $status
            );
            $model->where($where)->save($save);
        }catch (\PDOException $e) {
            $msg = $e->getMessage();
            \Think\Log::write($this->name."-updateSMSStatus-".$msg);
            SE(self::PDO_ERROR);
        }
    }

    public function selectCustomer($page = 1, $rows = 20, $search = array(), $sort = "cc.customer_id", $order = "desc")
    {
        $page  = intval($page);
        $rows  = intval($rows);
        $sort  = addslashes($sort);
        $order = addslashes($order);
        $where = " true ";
        try
        {
            foreach ($search as $k => $v) {
                if ($v === "") continue;
                $condition = "AND";
                switch ($k) {
                    case 'nickname':
                        set_search_form_value($where, $k, $v, "cc", 1, $condition);
                        break;
                    case 'start_time':
                        $sales_trade = true;//用来判断是否进行连表
                        set_search_form_value($where, 'trade_time',  $v,'st', 3,' AND ',' >= ');
                        break;
                    case 'end_time':
                        $sales_trade = true;
                        set_search_form_value($where, 'trade_time',  $v,'st', 3,' AND ',' <= ');
                        break;
                    case 'brand_id':
                        if($v!="all"){
                            $goods_goods = true;
                        }
                        set_search_form_value($where, $k, $v, "gg", 2, $condition);
                        break;
                    case 'spec_no':
                        $goods_spec = true;
                        set_search_form_value($where, $k, $v, "gs", 1, $condition);
                        break;
                    case 'shop_id':
                        $sales_trade = true;
                        set_search_form_value($where, $k, $v, "st", 2, $condition);
                        break;
                    case 'province':
                        if($v == 0) break;
                        set_search_form_value($where, $k, $v, "cc", 2, $condition);
                        break;
                    case 'city':
                        if($v == 0) break;
                        set_search_form_value($where, $k, $v, "cc", 2, $condition);
                        break;
                    case 'district':
                        if($v == 0) break;
                        set_search_form_value($where, $k, $v, "cc", 2, $condition);
                        break;
                    default :
                        continue;
                }
            }
            $where = $where == "" ? $where : "where " . $where;
            $limit = ($page - 1) * $rows . "," . $rows;
            $order = $sort . " " . $order;
            //先查询出需要在页面显示的客户的customer_id
            $sql_result = "SELECT DISTINCT cc.customer_id FROM crm_customer cc ";
            if(@$sales_trade)
            {
                $sql_result.= " LEFT JOIN sales_trade st ON(cc.customer_id = st.customer_id) ";
            }
            if(@$goods_goods || @$goods_spec)
            {
                $sql_result.= " LEFT JOIN sales_trade_order sto ON (st.trade_id = sto.trade_id)";
                $sql_result.= @goods_goods ? " LEFT JOIN goods_goods gg ON(gg.goods_id = sto.goods_id)":'';
            }
            $sql_result.= " $where ORDER BY $order LIMIT $limit";
            $sql = "SELECT cc1.customer_id AS id,cc1.nickname,cc1.name,cc1.mobile,cc1.email,cc1.sex,cc1.birthday,cc1.created FROM crm_customer cc1
                    INNER JOIN (".$sql_result.") cc2 ON (cc2.customer_id = cc1.customer_id)";
            $sql_count = substr($sql_result,0,strpos($sql_result,'LIMIT'));
            $sql_count = count($this->query($sql_count));
            $data["total"]  = $sql_count;
            $data["rows"]   = $this->query($sql);

            $file = APP_PATH."/Runtime/File/Customer";
            $cache_sql_result = substr($sql_result,0,stripos($sql_result,'limit'));
            $cache_sql = "SELECT cc1.customer_id AS id,cc1.nickname,cc1.name,cc1.mobile,cc1.email,cc1.sex,cc1.birthday,cc1.created,cc1.flag_id FROM crm_customer cc1
                    INNER JOIN (".$cache_sql_result.") cc2 ON (cc2.customer_id = cc1.customer_id)";
            if(file_exists($file))unlink($file);
            file_put_contents($file,print_r($cache_sql,true));
        }catch (\PDOException $e)
        {
            \Think\Log::write('selectCustomer'.$e->getMessage());
            $data = array();
            SE(parent::PDO_ERROR);
        }catch (\Exception $e)
        {
            \Think\Log::write('selectCustomer'.$e->getMessage());
            $data = array();
            SE(parent::PDO_ERROR);
        }
        return $data;
    }

    public function importBlackList($data,&$error_list)
    {
        try {
            $sex_array        = array('男'=>1,'女'=>2);
            $customer_log_tb  = M("crm_customer_log");
            $operator_id      = get_operator_id();
            $now              = date("Y-m-d H:i:s");
            foreach($data as $k1 => $v1)
            {
                if(empty($v1['customer']['nickname']))
                {
                    $error_list[] = array('id'=>$v1['line'],'result'=>'导入失败','message'=>'客户网名不能为空');
                    continue;
                }
                if(empty($v1['customer']['mobile']))
                {
                    $error_list[] = array('id'=>$v1['line'],'result'=>'导入失败','message'=>'导入客户手机号不能为空');
                    continue;
                }
                $customer_no = $v1['customer']['customer_no'];
                getAddress( $v1['customer']['province'],$v1['customer']['city'],$v1['customer']['district'],$province_id,$city_id,$district_id);
                $v1['customer']['province'] = $province_id;
                $v1['customer']['city'] = $city_id;
                $v1['customer']['district'] = $district_id;
                $v1['customer']["area"] = $v1['customer']['province'].' '.$v1['customer']['city'].' '.$v1['customer']['district'];
                $v1['customer']["is_disabled"] = 0;
                if(empty($v1['customer']['province']) || empty($v1['customer']['city']) || empty($v1['customer']['district']))
                {
                    $error_list[] = array('id'=>$v1['line'],'result'=>'导入失败','message'=>'省市区地址不能为空或填写错误');
                    continue;
                }

                $sex = empty($sex_array[$v1['sex']])?0:$sex_array[$v1['sex']];
                $v1['customer']['sex'] = $sex;
                //客户编码部位空查找是否已存在，存在直接更新(如果一个excel中有重复的客户编码，以第一个导入进来的客户信息为准，后续操作)
                $this->startTrans();
                if(empty($customer_no))
                {
                    //新增客户档案
                    $v1['customer']["customer_no"] = get_sys_no("customer");
                    $v1['customer']["is_black"] = 1;
                    $this->addCustomer($v1['customer'],false);
                }else
                {
                    $where = array('customer_no'=>$customer_no);
                    $org_customer_no = $this->field('customer_id')->where($where)->find();
                    if(!empty($org_customer_no))
                    {
                        $this->data(array('is_black'=>1))->where($where)->save();
                        $customer_log = array(
                            "customer_id" => $org_customer_no['customer_id'],
                            "operator_id" => $operator_id,
                            "type"        => 2,
                            "message"     => "导入操作更新为黑名单",
                            "created"     => $now
                        );
                        $customer_log_tb->data($customer_log)->add();
                        continue;
                    }else
                    {
                        $v1['customer']["is_black"] = 1;
                        $this->addCustomer($v1['customer'],false);
                    }
                }
                $this->commit();
            }
        } catch (\PDOException $e) {
            $this->rollback();
            SE($e->getMessage());
        } catch (\Exception $e) {
            $this->rollback();
            SE($e->getMessage());
        }
    }

}