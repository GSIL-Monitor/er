<?php
namespace Setting\Controller;

use Common\Controller\BaseController;
use Common\Common\Factory;
use Common\Common\UtilDB;
use Platform\Common\ManagerFactory;
use Setting\Common\SettingFields;
use Platform\Manager\ShopAuthManager;
use Think\Exception;
use Think\Exception\BusinessLogicException;
use Common\Common\ExcelTool;
use Common\Common\UtilTool;
use Think\Log;

class ShopController extends BaseController {

    /**
     * 返回店铺列表
     * author:luyanfeng
     * @param int    $page
     * @param int    $rows
     * @param array  $search
     * @param string $sort
     * @param string $order
     */
    public function getShopList($page = 1, $rows = 10, $search = array(), $sort = 'cs.shop_id', $order = 'desc') {
        if (IS_POST) {
            $this->ajaxReturn(Factory::getModel("Shop")->getShopList($page, $rows, $search, $sort, $order));
        } else {
            $id_list  = array(
                "datagrid"  => "shop_datagrid",
                "toolbar"   => "shop_toolbar",
                "form"      => "shop_search_form",
                "add"       => "shop_add_dialog",
                "add_form"  => "shop_add_form",
                "edit"      => "shop_edit_dialog",
                "edit_form" => "shop_edit_form",
                "auth"      => "shop_auth_dialog",
                "auth_form" => "shop_auth_form",
                'fileForm'  => 'shop_file_form',
                "fileDialog"=> "shop_file_dialog"

            );
            $datagrid = array(
                "id"      => $id_list["datagrid"],
                "options" => array(
                    "toolbar" => $id_list["toolbar"],
                    "url"     => U("Shop/getShopList")
                ),
                "fields"  => SettingFields::getSettingFields("shop")
            );
            $params   = array(
                "datagrid" => array(
                    "id"  => $id_list["datagrid"],
                    "url" => U("Shop/getShopList")
                ),
                "add"      => array(
                    "id"     => $id_list["add"],
                    "title"  => "新建店铺",
                    "url"    => U("Shop/addShop"),
                    "height" => 300,
                    "ismax"  => false
                ),
                "edit"     => array(
                    "id"     => $id_list["edit"],
                    "title"  => "编辑店铺",
                    "url"    => U("Shop/editShop"),
                    "height" => 300,
                    "ismax"  => false
                ),
                "search"   => array("form_id" => $id_list["form"])
            );
            $faq_url=C('faq_url');
            $this->assign('faq_url_shop_question',$faq_url['shop_question']);
            $this->assign("id_list", $id_list);
            $this->assign("datagrid", $datagrid);
            $this->assign("params", json_encode($params));
            $this->display("show");
        }
    }

    /**
     * 新建店铺
     * author:luyanfeng
     */
    public function addShop() {
        if (IS_POST) {
            $data = I("post.data");
            try {
                if (!isset($data["shop_name"]) || $data["shop_name"] == "") {
                    $res["status"] = 0;
                    $res["info"]   = "店铺名称不能为空";
                    $this->ajaxReturn($res);
                }
                $data["is_disabled"] = isset($data["is_disabled"]) ? $data["is_disabled"] : 0;
                if (Factory::getModel("Shop")->checkShop($data["shop_name"], "shop_name")) {
                    $res["status"] = 0;
                    $res["info"]   = "该店铺名称已存在";
                    $this->ajaxReturn($res);
                }
                if(!isset($data['group_id'])||!is_numeric($data['group_id'])){
                	$res["status"] = 0;
                	$res["info"]   = "分组必须为数字";
                	$this->ajaxReturn($res);
                }
                $M = M();
                $M->startTrans();
                //过滤默认数据
                if ($data["platform_id"] == "all") {
                    unset($data["platform_id"]);
                }
                if ($data["sub_platform_id"] == "all") {
                    unset($data["sub_platform_id"]);
                }
                if ($data["logistics_id"] == "no") {
                    unset($data["logistics_id"]);
                }
                if ($data["cod_logistics_id"] == "no") {
                    unset($data["cod_logistics_id"]);
                }
                $data['created']  = date("Y-m-d G:i:s");
                $data['modified'] = date("Y-m-d G:i:s");
                $result           = Factory::getModel("shop")->updateShop($data);
                if ($result) {
                    $M->commit();
                    $res["status"] = 1;
                    $res["info"]   = "操作成功";
                } else {
                    $M->rollback();
                    $res["status"] = 0;
                    $res["info"]   = $result["info"];
                }
            } catch (\Exception $e) {
                $M->rollback();
                \Think\Log::write($e->getMessage());
                $res["status"] = 0;
                $res["info"]   = "系统错误，请联系管理员";
            }
            $this->ajaxReturn($res);
        } else {
            $id_list = array(
                "form" => "shop_add_form"
            );
            try {
                $cfg_logistics_db  = M("cfg_logistics");
                $sql               = "SELECT logistics_id AS id,logistics_name AS name FROM cfg_logistics cl WHERE is_support_cod=1";
                $cod_cfg_logistics = $cfg_logistics_db->query($sql);
            } catch (\Exception $e) {
                \Think\Log::write($e->getMessage());
                $cod_cfg_logistics = array();
            }
            $cfg_logistics = UtilDB::getCfgList(array("logistics"),array('logistics'=>array('is_disabled'=>array('eq',0))));
            $cfg_logistics["logistics"][]=array('id'=>0,'name'=>'无');
            $cod_cfg_logistics[]=array('id'=>0,'name'=>'无');
            $this->assign("cfg_logistics", $cfg_logistics["logistics"]);
            $this->assign("cod_cfg_logistics", $cod_cfg_logistics);
            $this->assign("id_list", $id_list);
            $this->display("shop_add");
        }
    }

    /**
     * 编辑店铺信息
     * author:luyanfeng
     */
    public function editShop() {
        if (IS_POST) {
            $data = I("post.data");
            try {
                if (!isset($data["shop_name"]) || $data["shop_name"] == "") {
                    $res["status"] = 0;
                    $res["info"]   = "店铺名称不能为空";
                    $this->ajaxReturn($res);
                }
                if(!isset($data['group_id'])||!is_numeric($data['group_id'])){
                	$res["status"] = 0;
                	$res["info"]   = "分组必须为数字";
                	$this->ajaxReturn($res);
                }
                //过滤默认数据
                if ($data["platform_id"] == "all") {
                    unset($data["platform_id"]);
                }
                if ($data["sub_platform_id"] == "all") {
                    unset($data["sub_platform_id"]);
                }
                if ($data["logistics_id"] == "no") {
                    unset($data["logistics_id"]);
                }
                if ($data["cod_logistics_id"] == "no") {
                    unset($data["cod_logistics_id"]);
                }
                $data["is_disabled"] = isset($data["is_disabled"]) ? $data["is_disabled"] : 0;
                $M                   = M();
                $M->startTrans();
                $data['created']  = date("Y-m-d G:i:s");
                $data['modified'] = date("Y-m-d G:i:s");
                $result           = Factory::getModel("Shop")->getShop($data["shop_name"], "shop_name", $fields = array("shop_id,shop_name"));
                if ($result == 0) {
                    $res["status"] = $result["status"];
                    $res["info"]   = $result["info"];
                    $this->ajaxReturn($res);
                } else {
                    foreach ($result["data"] as $v) {
                        if (!isset($data["shop_id"]) || $data["shop_id"] != $v["shop_id"]) {
                            $res["status"] = 0;
                            $res["info"]   = "该店铺名称已存在";
                            $this->ajaxReturn($res);
                        }
                    }
                }
                /*$result = Factory::getModel("Shop")->getShop($data["shop_no"], "shop_no", $fields = array("shop_id,shop_no"));
                if ($result == 0) {
                    $res["status"] = $result["status"];
                    $res["info"]   = $result["info"];
                    $this->ajaxReturn($res);
                } else {
                    foreach ($result["data"] as $v) {
                        if (!isset($data["shop_id"]) || $data["shop_id"] != $v["shop_id"]) {
                            $res["status"] = 0;
                            $res["info"]   = "该店铺编号已存在";
                            $this->ajaxReturn($res);
                        }
                    }
                }*/
                $result = Factory::getModel("shop")->updateShop($data);
                if ($result["status"]) {
                    $M->commit();
                    $res["status"] = 1;
                    $res["info"]   = "操作成功";
                } else {
                    $M->rollback();
                    $res["status"] = 0;
                    $res["info"]   = $result["info"];
                }
            } catch (\Exception $e) {
                $M->rollback();
                \Think\Log::write($e->getMessage());
                $res["status"] = 0;
                $res["info"]   = "系统错误，请联系管理员";
            }
            $this->ajaxReturn($res);
        } else {
            $id      = I("get.id");
            $id_list = array(
                "form" => "shop_edit_form"
            );
            try {
                $cfg_shop_db = M("cfg_shop");
                $sql         = "SELECT cs.platform_id,cs.sub_platform_id,cs.shop_id,cs.shop_name,cs.pay_account_id,cs.auth_state,cs.logistics_id,
                    cs.cod_logistics_id,cs.contact,cs.country,cs.province,cs.city,cs.district,cs.address,cs.telno,cs.mobile,cs.zip,cs.auth_state,
                    cs.email,cs.remark,cs.website,cs.is_disabled,cs.is_setwarebygoods,cs.group_id 
                    FROM cfg_shop cs WHERE cs.shop_id=%d";
                $shop        = $cfg_shop_db->query($sql, $id);
                $shop        = $shop[0];
            } catch (\Exception $e) {
                \Think\Log::write($e->getMessage());
                $shop = array();
            }
            try {
                $cfg_logistics_db  = M("cfg_logistics");
                $sql               = "SELECT logistics_id AS id,logistics_name AS name FROM cfg_logistics cl WHERE is_support_cod=1";
                $cod_cfg_logistics = $cfg_logistics_db->query($sql);
            } catch (\Exception $e) {
                \Think\Log::write($e->getMessage());
                $cod_cfg_logistics = array();
            }
            $cfg_logistics = UtilDB::getCfgList(array("logistics"),array('logistics'=>array('is_disabled'=>array('eq',0))));
            $cfg_logistics["logistics"][]=array('id'=>0,'name'=>'无');
            $cod_cfg_logistics[]=array('id'=>0,'name'=>'无');
            $this->assign("cfg_logistics", $cfg_logistics["logistics"]);
            $this->assign("cod_cfg_logistics", $cod_cfg_logistics);
            $this->assign("id_list", $id_list);
            $this->assign("shop", $shop);
            $this->assign("shop_edit", json_encode($shop));
            $this->display("shop_edit");
        }
    }

    public function getLogisticsShop($id = 0) {
        $shop_id = (int)$id;
        try {
            $rs = M()->query("SELECT als.shop_id id, als.logistics_code,als.name,GROUP_CONCAT(cls.logistics_name) logistics_name
            from api_logistics_shop als LEFT JOIN cfg_logistics_shop cls ON(cls.shop_id=als.shop_id AND cls.logistics_code=als.logistics_code)
            WHERE als.shop_id={$shop_id}
            GROUP BY als.shop_id,als.logistics_code");
        } catch (\Exception $e) {
            \Think\Log::write($e->getMessage());
        }
        echo json_encode($rs);
    }

    public function getHomeServiceShop($id = 0) {
        $shop_id = (int)$id;
        try {

            $rs = M()->query("SELECT rec_id,target_name,target_type,target_code,shop_id,is_virtual,service_type,is_defalut,is_disabled,modified,created
            FROM api_sys_install WHERE shop_id = $shop_id ORDER BY is_defalut DESC");
        } catch (\Exception $e) {
            \Think\Log::write($e->getMessage());
        }
        echo json_encode($rs);
    }

    public function getLogisticsList() {
        try {
            $model = M('dict_logistics');
            $data  = $model->query('select logistics_type,logistics_name from dict_logistics');
        } catch (\Exception $e) {
            \Think\Log::write($e->getMessage());
        }
        return $data;
    }

    public function UpdateLogisticsList() {
        $i         = I('post.data');
        $i         = htmlspecialchars_decode($i);
        $i         = json_decode($i,true);
        $rows      = $i['rows'];
        $total     = $i['total'];
        $data      = self::getLogisticsList();
        $tem_array = array();
        if ($total == '' || $total = 0) {
            return;
        }
        $name_array = array();
        foreach ($rows as $row) {
            $tem        = explode(",", $row['logistics_name']);
            $name_array = array_merge($name_array, $tem);
            foreach ($tem as $ntem) {
                $logistics_type = '';
                $length         = count($tem_array);
                for ($j = 0; $j < count($data); $j++) {
                    if ($data[ $j ]['logistics_name'] == $ntem) {
                        $logistics_type = $data[ $j ]['logistics_type'];
                        break;
                    }
                }
                $tem_array[ $length ] = array("id" => $row['id'], "logistics_code" => $row['logistics_code'], "logistics_name" => $ntem, "name" => $row['name'], "logistics_type" => $logistics_type);

            }
        }
        foreach ($name_array as $k => $v) {
            if (!$v)
                unset($name_array[ $k ]);
        }
        if (count($name_array) != count(array_unique($name_array))) {
            $this->ajaxReturn("2");
        }

        $l = count($tem_array);
        for ($n = 0; $n < $l; $n++) {
            if ($tem_array[ $n ]['logistics_name'] == "" || $tem_array[ $n ]['logistics_name'] == null || $tem_array[ $n ]['logistics_type'] == "") {
                unset($tem_array[ $n ]);
            }
        }
        $m     = 0;
        $array = array();
        for ($i = 0; $i < $l; $i++) {
            if (!empty($tem_array[ $i ])) {
                $array[ $m ] = $tem_array[ $i ];
                $m++;
            }
        }
        $tem_array = $array;

        $model = M('cfg_logistics_shop');
        $model->startTrans();
        try {
            $model->where('shop_id=' . $rows[0]['id'])->delete();
            for ($i = 0; $i < count($tem_array); $i++) {
                $sql = "INSERT INTO cfg_logistics_shop(shop_id, logistics_code, logistics_name, logistics_type, created) VALUES (" . $tem_array[ $i ]['id'] . ",\"" . $tem_array[ $i ]['logistics_code'] . "\",\"" . $tem_array[ $i ]['logistics_name'] . "\"," . $tem_array[ $i ]['logistics_type'] . ",NOW())";
                $model->execute($sql);
            }
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            \Think\Log::write($msg);
            $model->rollback();
            $this->ajaxReturn("1");
        }
        $model->commit();
        $this->ajaxReturn("0");


    }

    public function authorize($shop_id, $platform_id, $sub_platform_id,$authorize_type = '') {
        try {
            if (0 == intval($shop_id)) {
                E('无效店铺');
            }
            $url = ShopAuthManager::authorize($shop_id, $platform_id, $sub_platform_id,$authorize_type);
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            \Think\Log::write($msg);
            $ret['status'] = 1;
            $ret['info']   = $msg;
            $this->ajaxReturn($ret);
        }

        $ret['status'] = 0;
        $ret['info']   = $url;
        $this->ajaxReturn($ret);

    }

    public function alipayAuthorize($shop_id, $platform_id){
        try {
            if (1 != intval($platform_id)) {
                SE('该店铺不支持支付宝授权');
            }
            $url = ShopAuthManager::alipayAuthorize($shop_id, $platform_id);
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            \Think\Log::write($msg);
            $ret['status'] = 1;
            $ret['info']   = $msg;
            $this->ajaxReturn($ret);
        }

        $ret['status'] = 0;
        $ret['info']   = $url;
        $this->ajaxReturn($ret);

    }

    //下载物流公司
    public function downloadShopLogistics() {
        $msg    = array("status" => 0, "info" => "");
        $shopID = I("post.shopID");
        try {
            $Logistics_tb     = M("api_logistics_shop");
            $LogisticsManager = ManagerFactory::getManager("LogisticsCompany");
            $LogisticsManager->manualDownloadShopLogistics($Logistics_tb, get_sid(), "", $shopID, $msg);
        } catch (\PDOException $e) {
            $msg["status"] = 0;
            $msg["info"]   = "未知错误，请联系管理员";
            \Think\Log::write("sql_exception" . $e->getMessage());
        } catch (\Exception $e) {
            $msg["status"] = 0;
            $msg["info"]   = $e->getMessage();
            \Think\Log::write($e->getMessage());
        }
        $this->ajaxReturn($msg);
    }

    //下载家装服务
    public function download_top_logistics_install()
    {
        $msg    = array("status" => 0, "info" => "下载成功");
        $shopID = I("post.shopID");
        $service_type = I("post.service_type");
        try {
            $LogisticsManager = ManagerFactory::getManager("Logistics");
            $LogisticsManager->download_top_logistics_install(get_sid(), $shopID, $service_type,$msg);
        } catch (\PDOException $e) {
            $msg["status"] = 1;
            $msg["info"]   = "未知错误，请联系管理员";
            \Think\Log::write("sql_exception" . $e->getMessage());
        } catch (\Exception $e) {
            $msg["status"] = 1;
            $msg["info"]   = $e->getMessage();
            \Think\Log::write($e->getMessage());
        }
        $this->ajaxReturn($msg);
    }

    public function getAuthInfo() {
        $id = I("get.shop_id");
        try {
            $id_list = array(
                "auth_form" => "shop_auth_form"
            );
            $data    = D("Shop")->getAuthInfo($id);
            switch ($data["platform_id"]) {
                case "5":
                {
                    $show = array(
                        "account_nick" => true,
                        "key"          => true,
                        "secret"       => false,
                        "session"      => true,
                    );
                    $def  = array(
                        "account_nick" => "卖家账号，商城编号",
                        "key"          => "AWS编号",
                        "secret"       => "AppSecret",
                        "session"      => "密钥",
                    );
                    break;
                }
                case  "8":
                {
                    $show = array(
                        "account_nick" => true,
                        "key"          => false,
                        "secret"       => true,
                        "session"      => false,
                    );
                    $def  = array(
                        "account_nick" => "VenderId",
                        "key"          => "",
                        "secret"       => "Secret",
                        "session"      => "",
                    );
                    break;
                }
                case "25":
                {
                    $show = array(
                        "account_nick" => false,
                        "key"          => true,
                        "secret"       => true,
                        "session"      => true,
                    );
                    $def  = array(
                        "account_nick" => "",
                        "key"          => "应用key值",
                        "secret"       => "应用密码",
                        "session"      => "授权码",
                    );
                    break;
                }
                case "27":
                {
                    $show = array(
                        "account_nick" => false,
                        "key"          => true,
                        "secret"       => true,
                        "session"      => false,
                    );
                    $def  = array(
                        "account_nick" => "",
                        "key"          => "AppKey",
                        "secret"       => "AppSecret",
                        "session"      => "",
                    );
                    break;
                }
                case "29": //卷皮网
                {
                    $show = array(
                        "account_nick" => false,
                        "key"          => false,
                        "secret"       => false,
                        "session"      => true,
                    );
                    $def  = array(
                        "account_nick" => "",
                        "key"          => "接入码",
                        "secret"       => "Secret",
                        "session"      => "商户key",
                    );
                    break;
                }
                case "33"://拼多多
                {
                    $show = array(
                        "account_nick" => false,
                        "key"          => true,
                        "secret"       => true,
                        "session"      => false,
                    );
                    $def  = array(
                        "account_nick" => "",
                        "key"          => "接入码",
                        "secret"       => "Secret",
                        "session"      => "",
                    );
                    break;
                }
                case "34"://蜜芽宝贝
                {

                    $show = array(
                        "account_nick" => false,
                        "key"          => true,
                        "secret"       => true,
                        "session"      => false,
                    );
                    $def  = array(
                        "account_nick" => "",
                        "key"          => "vendor_key",
                        "secret"       => "secret",
                        "session"      => "",
                    );
                    break;
                }
                case "36": //善融商城
                {
                    $show = array(
                        "account_nick" => false,
                        "key"          => true,
                        "secret"       => true,
                        "session"      => false,
                    );
                    $def  = array(
                        "account_nick" => "",
                        "key"          => "商户编号",
                        "secret"       => "商户秘钥",
                        "session"      => "",
                    );
                    break;
                }
                case "53"://楚楚街拼团
                {

                    $show = array(
                        "account_nick" => false,
                        "key"          => true,
                        "secret"       => true,
                        "session"      => false,
                    );
                    $def  = array(
                        "account_nick" => "",
                        "key"          => "app_key",
                        "secret"       => "appsecret",
                        "session"      => "",
                    );
                    break;
                }
                case "56"://小红书
                {

                    $show = array(
                        "account_nick" => false,
                        "key"          => true,
                        "secret"       => true,
                        "session"      => false,
                    );
                    $def  = array(
                        "account_nick" => "",
                        "key"          => "app-id",
                        "secret"       => "app-secret",
                        "session"      => "",
                    );
                    break;
                }
                case "60":
                {

                    $show = array(
                        "account_nick" => false,
                        "key"          => true,
                        "secret"       => true,
                        "session"      => false,
                    );
                    $def  = array(
                        "account_nick" => "",
                        "key"          => "userKey",
                        "secret"       => "userSecret",
                        "session"      => "",
                    );
                    break;
                }
                default: {
                    $show = array(
                        "account_nick" => true,
                        "key"          => true,
                        "secret"       => true,
                        "session"      => true,
                    );
                    $def  = array(
                        "account_nick" => "平台账号",
                        "key"          => "AppKey",
                        "secret"       => "AppSecret",
                        "session"      => "session",
                    );
                    break;
                }
            }
            $app_key = json_decode($data["app_key"], true);
            $auth    = array(
                "shop_id"      => $id,
                "account_nick" => $data["account_nick"],
                "key"          => $app_key["key"],
                "secret"       => $app_key["secret"],
                "session"      => $app_key["session"]
            );
            $this->assign("def", $def);
            $this->assign("show", $show);
            $this->assign("id_list", $id_list);
            $this->assign("auth", $auth);
        } catch (\Exception $e) {
            \THink\Log::write($e->getMessage());
        }
        $this->display("shop_auth");
    }

    public function saveAuthInfo() {
        $data = I("post.data");
        try {
            $appkey = array(
                "key"     => isset($data["key"]) ? $data["key"] : "",
                "secret"  => isset($data["secret"]) ? $data["secret"] : "",
                "session" => isset($data["session"]) ? $data["session"] : ""
            );
            $appkey = json_encode($appkey, JSON_UNESCAPED_UNICODE);
            $data   = array(
                "shop_id"      => $data["shop_id"],
                "account_nick" => isset($data["account_nick"]) ? $data["account_nick"] : "",
                "app_key"      => $appkey
            );
            D("Shop")->saveAuthInfo($data);
            $res = array("status" => 0, "info" => "操作成功");
        } catch (BusinessLogicException $e) {
            $res = array("status" => 1, "info" => $e->getMessage());
        } catch (\Exception $e) {
            \Think\Log::write($e->getMessage());
            $res = array("status" => 1, "info" => self::UNKNOWN_ERROR);
        }
        $this->ajaxReturn($res);
    }

    public function downloadTemplet(){
        $file_name = "店铺导入模板.xls";
        $file_sub_path = APP_PATH."Runtime/File/";
        try{
            ExcelTool::downloadTemplet($file_name,$file_sub_path);
        } catch (BusinessLogicException $e){
            Log::write($e->getMessage());
            echo '对不起，模板不存在，下载失败！';
        } catch (\Exception $e) {
            Log::write($e->getMessage());
            echo parent::UNKNOWN_ERROR;
        }

    }

    public function uploadExcel()
    {
        //获取表格相关信息
        $file = $_FILES["file"]["tmp_name"];
        $name = $_FILES["file"]["name"];
        try {
            //临时表，用来存放导入时的错误信息。
            $sql_drop = "DROP TABLE IF EXISTS  `tmp_import_detail`";
            $sql      = "CREATE TABLE IF NOT EXISTS `tmp_import_detail` (`rec_id` INT NOT NULL AUTO_INCREMENT,`id` SMALLINT,`status` TINYINT,`result` VARCHAR(30),`message` VARCHAR(60),PRIMARY KEY(`rec_id`))";
            $M = M();
            $M->execute($sql_drop);
            $M->execute($sql);
            $importDB = M("tmp_import_detail");
            //将表格读取为数据
            $excelClass = new ExcelTool();
            $excelClass->checkExcelFile($name, $file);
            $excelClass->uploadFile($file,"ShopImport");
            $count = $excelClass->getExcelCount();
            $excelData = $excelClass->Excel2Arr($count);
            $res = array();
            foreach($excelData as $sheet){
                for($k=1;$k<count($sheet);$k++){
                    $row = $sheet[$k];
                    if (UtilTool::checkArrValue($row)) continue;
                    //获取表头
                    $i=0;
                    $data['platform_id'] = $row[$i];
                    $data['sub_platform_id'] = $row[++$i];
                    $data['shop_name'] = $row[++$i];
                    $data['logistics_id'] = $row[++$i];
                    $data['cod_logistics_id'] = $row[++$i];
                    $data['contact'] = $row[++$i];
                    $data['province'] = $row[++$i];
                    $data['city'] = $row[++$i];
                    $data['district'] = $row[++$i];
                    $data['mobile'] = $row[++$i];
                    $data['telno'] = $row[++$i];
                    $data['email'] = $row[++$i];
                    $data['zip'] = $row[++$i];
                    $data['address'] = $row[++$i];
                    $data['website'] = $row[++$i];
                    $data['remark'] = $row[++$i];
                    $M->startTrans();
                    try{
                        array_map("trim_all",$data);
                        D('Shop')->importShop($data);
                        $M->commit();
                    }catch (BusinessLogicException $e) {
                        $M->rollback();
                        $err_msg = array("id" => $k + 1, "status" => 1, "message" => $e->getMessage(), "result" => "失败");
                        $importDB->data($err_msg)->add();
                    }
                }
            }
        }  catch (\Exception $e) {
            Log::write($e->getMessage());
            $res["status"] = 1;
            $res["info"] = "未知错误，请联系管理员";
            $this->ajaxReturn(json_encode($res), "EVAL");
        }
        unset($data);
        try {
            $sql    = "SELECT id,status,result,message FROM tmp_import_detail";
            $result = $M->query($sql);
            if (count($result) == 0) {
                $res["status"] = 0;
                $res["info"]   = "操作成功";
            } else {
                $res["status"] = 2;
                $res["info"]   = $result;
            }
            $sql_drop = "DROP TABLE IF EXISTS  `tmp_import_detail`";
            $M->execute($sql_drop);
        } catch (\Exception $e) {
            $res["status"] = 1;
            $res["info"] = "未知错误，请联系管理员";
        }
        $this->ajaxReturn(json_encode($res), "EVAL");

    }

}
