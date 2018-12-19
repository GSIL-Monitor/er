<?php
namespace Platform\Manager;

require_once(ROOT_DIR . '/Manager/utils.php');
require_once(ROOT_DIR . '/Logistics/util.php');
require_once(ROOT_DIR . "/Manager/Manager.class.php");

class LogisticsCompanyManager extends Manager{

    public static function register() {
        registerHandle("logisitics_company_merchant", array("\\Platform\\Manager\\LogisticsCompanyManager", "listCompanyShop"));
        registerHandle("logistics_company_task", array("\\Platform\\Manager\\LogisticsCompanyManager", "downloadLogisticsCompany"));
    }
    public static function LogisticsCompany_main() {
        return enumAllMerchant('logisitics_company_merchant');
    }

    public static function listCompanyShop($sid){
        deleteJob();
        $db = getUserDb($sid);

        if (!$db) {
            logx("listCompanyShop getUserDb failed!!", $sid . "/Logistics");
            return TASK_OK;
        }
        $shop_sql = "SELECT * FROM cfg_shop cs WHERE NOT EXISTS (SELECT * FROM api_logistics_shop als WHERE als.`shop_id`=cs.`shop_id`) AND cs.`auth_state`=1 AND cs.`is_disabled`=0";
        $shop = $db->query($shop_sql);
        if(!$shop){
            releaseDb($db);
            logx("query shop failed!", $sid . "/Logistics");
            return TASK_OK;
        }
        
        while($row = $db->fetch_array($shop)){
            if (!checkAppKey($row))
                continue;
            $row->sid = $sid;
            pushTask('logistics_company_task',$row,0,1024,600,300);
        }
        $db->free_result($shop);
        releaseDb($db);
    }

    public static function downloadLogisticsCompany($shop)
    {
        $sid = $shop->sid;
        $shopID = $shop->shop_id;
        $db = getUserDb($sid);
        if (!$db) {
            logx("downloadLogisticsCompany getUserDb failed!!", $sid . "/Logistics");
            return TASK_OK;
        }

        getAppSecret($shop, $appkey, $secret);
        $shop->key = $appkey;
        $shop->secret = $secret;
        $platform_id = intval($shop->platform_id);
        $companies = array();
        self::logisticsCompanyImp($platform_id, $db, $shop, $companies, $msg);

        $db->execute("BEGIN");
        if (count($companies) > 0) {
            $bool = false;
            if (false === $db->execute("UPDATE api_logistics_shop SET flag=1 WHERE shop_id={$shopID}") ||
                false === $db->execute('DELETE cls FROM cfg_logistics_shop cls,api_logistics_shop als' . ' WHERE als.flag=1 AND cls.shop_id=als.shop_id AND cls.logistics_code=als.logistics_code') ||
                false === $db->execute("DELETE FROM api_logistics_shop WHERE flag=1")
            ) {
                $bool = true;
            }
            if (!$bool) {
                foreach ($companies as $v) {
                    $v["created"] = date("Y-m-d H:i:s", time());
                    $v['name'] = empty($v['name']) ? $v['logistics_code'] : $v['name'];
                    $data[]=$v;
                    $res =putDataToTable($db,'api_logistics_shop',$data,'');
                    if (false === $res) {
                        logx('putDataToTable False',$sid.'/Logistics');
                        $bool = true;
                        break;
                    }
                }
            }
            if ($bool) {
                $db->execute("ROLLBACK");
                logx('保存物流公司失败',$sid.'/Logistics');
                return;
            } else {
                $db->execute("commit");
            }
            // 刷新物流映射
            $platform_id = addslashes($platform_id);
            $db->execute("INSERT IGNORE INTO cfg_logistics_shop(shop_id,logistics_type,logistics_code,logistics_id,logistics_name,cod_support ,created) " .
                "SELECT {$shopID},lc.logistics_type,als.logistics_code,als.logistics_id,als.name,als.cod_support,NOW() " .
                "FROM api_logistics_shop als LEFT JOIN dict_logistics_code lc ON (als.logistics_code=lc.logistics_code) " .
                "WHERE lc.platform_id={$platform_id} and lc.type = 1 and als.shop_id={$shopID}");

            $db->execute("INSERT IGNORE INTO cfg_logistics_shop(shop_id,logistics_type,logistics_code,logistics_id,logistics_name,cod_support ,created) " .
                "SELECT {$shopID},dl.logistics_type,als.logistics_code,als.logistics_id,als.name,als.cod_support,NOW() " .
                "FROM api_logistics_shop als LEFT JOIN dict_logistics dl ON (dl.logistics_name=als.name) " .
                "WHERE als.shop_id={$shopID}");
        }
        logx('保存物流公司成功',$sid.'/Logistics');
        releaseDb($db);
    }

    public static function logisticsCompanyImp($platform_id,$db, $shop, &$companies,&$msg){
        switch ($platform_id) {
            case 1 : // 淘宝天猫
            case 2: //淘宝分销
            {
                require_once(ROOT_DIR . '/Logistics/top.php');
                top_get_logistics_companies($db, $shop, $companies, $msg);
                break;
            }
            case 3 : { // 京东商城
                require_once(ROOT_DIR . '/Logistics/jos.php');
                jos_get_logistics_companies($db, $shop, $companies, $msg);
                break;
            }
            case 5 ://amazon
            {
                require_once (ROOT_DIR  . '/Logistics/amazon.php');
                amazon_get_logistics_companies( $db, $shop, $companies, $msg );
                break;
            }
            case 6 : { //一号店
                require_once(ROOT_DIR . '/Logistics/yhd.php');
                yhd_get_logistics_companies($db,$shop,$companies,$msg);
                break;
            }
            case 7: { //当当

                require_once(ROOT_DIR . '/Logistics/dangdang.php');
                dangdang_get_logistics_companies($db,$shop,$companies,$msg);
                break;
            }
            case 8 :{ //国美
                require_once(ROOT_DIR . '/Logistics/coo8.php');
                coo8_get_logistics_companies($db,$shop,$companies,$msg);
                break;
            }
            case 9 : // ali
            {
                require_once (ROOT_DIR . '/Logistics/alibaba.php');
                alibaba_get_logistics_companies ( $db, $shop, $companies, $msg );
                break;
            }
            case 13 : // sos
            {
                require_once (ROOT_DIR . '/Logistics/sos.php');
                sos_get_logistics_companies ( $db, $shop, $companies, $msg );
                break;
            }
            case 14 : // 唯品会
            {
                require_once (ROOT_DIR . '/Logistics/vipshop.php');
                vipshop_get_logistics_companies ( $db, $shop, $companies, $msg );
                break;
            }
            case 17 : // kdt
            {
                require_once (ROOT_DIR . '/Logistics/kdt.php');
                kdt_get_logistics_companies ( $db, $shop, $companies, $msg );
                break;
            }
            case 20 : // mls
            {
                require_once (ROOT_DIR . '/Logistics/mls.php');
                meilishuo_get_logistics_companies ( $db, $shop, $companies, $msg );
                break;
            }
			case 22 : // bbw
            {
                require_once (ROOT_DIR . '/Logistics/bbw.php');
                bbw_get_logistics_companies ( $db, $shop, $companies, $msg );
                break;
            } 			
            case 24 : //折800
            {
                require_once (ROOT_DIR . '/Logistics/zhe800.php');
                zhe_get_logistics_companies ( $db, $shop, $companies, $msg );
                break;
            }
            case 25 : // 融e购
			{
				require_once (ROOT_DIR . '/Logistics/icbc.php');
				icbc_get_logistics_companies ( $db, $shop, $companies, $msg );
				break;
			}
            case 28 : // weimo
            {
                require_once (ROOT_DIR . '/Logistics/weimo.php');
                weimo_get_logistics_companies ( $db, $shop, $companies, $msg );
                break;
            }
			case 27 : // 楚楚街
            {
                require_once (ROOT_DIR . '/Logistics/ccj.php');
                ccj_get_logistics_companies ( $db, $shop, $companies, $msg );
                break;
            }
            case 29 : // 卷皮网
            {
                require_once (ROOT_DIR . '/Logistics/jpw.php');
                jpw_get_logistics_companies ( $db, $shop, $companies, $msg );
                break;
            }
            case 31://飞牛
            {
                require_once(ROOT_DIR . '/Logistics/fn.php');
                fn_get_logistics_companies($db,$shop,$companies,$msg);
                break;
            }
            case 32://微店
            {
                require_once(ROOT_DIR . '/Logistics/vdian.php');
                vdian_get_logistics_companies($db,$shop,$companies,$msg);
                break;
            }
            case 33: //pdd
            {
                require_once (ROOT_DIR . '/Logistics/pdd.php');
                pdd_get_logistics_companies($db,$shop,$companies,$msg);
                break;
            }
            case 34://蜜芽宝贝
            {
                require_once(ROOT_DIR .'/Logistics/mia.php');
                mia_get_logistics_companies($db, $shop, $companies, $msg);
                break;
            }
            case 36 : // 善融商城
            {
                require_once (ROOT_DIR . '/Logistics/ccb.php');
                ccb_get_logistics_companies($db, $shop, $companies, $msg);
                break;
            }
            case 37: //速卖通
            {
                require_once (ROOT_DIR . '/Logistics/smt.php');
                smt_get_logistics_companies($db,$shop,$companies,$msg);
                break;
            }
            case 47: //人人店
            {
                require_once(ROOT_DIR . '/Logistics/rrd.php');
                rrd_get_logistics_companies($db,$shop,$companies,$msg);
                break;
            }
            case 50: //考拉
            {
                require_once(ROOT_DIR . '/Logistics/kl.php');
                kl_logistics_companies($db,$shop,$companies,$msg);
                break;
            }
            case 53://楚楚街拼团
            {
                require_once(ROOT_DIR.'/Logistics/ccjpt.php');
                ccjpt_get_logistics_companies($db, $shop, $companies, $msg);
                break;
            }
            case 56://小红书
            {
                require_once(ROOT_DIR.'/Logistics/xhs.php');
                xhs_get_logistics_companies($db, $shop, $companies, $msg);
                break;
            }
            case 60: //返利网
            {
                require_once(ROOT_DIR . '/Logistics/flw.php');
                flw_get_logistics_companies($db,$shop,$companies,$msg);
                break;
            }
            default : {
                $msg["status"] = 0;
                $msg["info"]   = "店铺不支持物流公司下载";
                logx('店铺不支持物流公司下载',$shop->sid . '/Logistics');
                break;
            }
        }
    }


    public function manualDownloadShopLogistics($db, $sid, $uid, $shopID, &$msg) {
        //$shop_id = intval($data['shopID']);
        //$model = new Model('', '', $connection);
        //$model = getUserDb($sid);
        $model = $db;
        if (!$model) {
            $msg["status"] = 0;
            $msg["info"]   = "数据库获取失败，请联系管理员";
            logx("$sid downloadShopLogistics:获取数据库失败", $sid . "/Logistics",'error');
            return;
        }
        //todo-ken 需要修改table值后上传
        $shopID = addslashes($shopID);
        $shop   = $model->query("SELECT shop_id,platform_id,app_key FROM cfg_shop where shop_id ={$shopID} and is_disabled = 0 and auth_state = 1");

        $shop = $shop[0]["shop_id"];
        //get_shop_auth($shop);
        $res = parent::sync_auth_check($shopID, $shop, $msg);
        if($res['status']==0 && $res['info']!=''){
            SE($msg["info"]);
            return false;
        }
        getAppSecret($shop, $appkey, $secret);
        $shop->key     = $appkey;
        $shop->secret  = $secret;
        $platform_id   = intval($shop->platform_id);
        $shop->sid     = $sid;
        $companies     = array();
        $msg['status'] = 1;
        $msg["info"]   = "操作成功";

        self::logisticsCompanyImp($platform_id,$db, $shop, $companies,$msg);

        //$api_model = new Model('api_logistics_shop');
        //false === $model->addAll($companies, array(), array('flag' => 0)) ||
        //false === $model->execute("COMMIT")
        if (1 == $msg['status'] && count($companies) > 0) {

            $model->execute("BEGIN");
            /*if (false === $model->execute("UPDATE api_logistics_shop SET flag=1 WHERE shop_id={$shopID}") ||
                false === $model->execute('DELETE cls FROM cfg_logistics_shop cls,api_logistics_shop als' .
                                          ' WHERE als.flag=1 AND cls.shop_id=als.shop_id AND cls.logistics_code=als.logistics_code') ||
                false === $model->execute("DELETE FROM api_logistics_shop WHERE flag=1")
            ) {
                $bool = false;
                foreach ($companies as $v) {
                    $v["created"] = date("Y-m-d H:i:s", time());
                    $value_str    = '';
                    foreach ($v as $key => $val) {
                        $value_str [] = "'" . $key . "','" . $val . "'";
                    }
                    $sql = "insert into api_logistics_shop (`shop_id`,`logistics_code`,`name`,`code_support`,``created) values ("
                        . implode("),(", $value_str) . ")";
                    if (false == $model->execute($sql)) {
                        $bool = true;
                    }
                }
                $model->execute("ROLLBACK");
                $res['status'] = 1;
                $res['info']   = "保存物流公司失败";
                $this->ajaxReturn($res);
            }*/
            $bool = false;
            if (false === $model->execute("UPDATE api_logistics_shop SET flag=1 WHERE shop_id={$shopID}") ||
                false === $model->execute('DELETE cls FROM cfg_logistics_shop cls,api_logistics_shop als' . ' WHERE als.flag=1 AND cls.shop_id=als.shop_id AND cls.logistics_code=als.logistics_code') ||
                false === $model->execute("DELETE FROM api_logistics_shop WHERE flag=1")
            ) {
                $bool = true;
            }
            if (!$bool) {
                try {
                    foreach ($companies as $v) {
                        $v["created"] = date("Y-m-d H:i:s", time());
                        $v['name'] = empty($v['name'])?$v['logistics_code']:$v['name'];
                        /*$value_str    = '';
                        foreach ($v as $key => $val) {
                            //$value_str = "'" . $key . "','" . $val . "'";
                            $value_str = $value_str . "'" . $val . "'" . ",";
                        }
                        $value_str = substr($value_str, 0, strlen($value_str) - 1);
                        $sql       = "insert into api_logistics_shop (`shop_id`,`logistics_id`,`logistics_code`,`name`,`cod_support`,`created`) values ("
                            . $value_str . ")";*/
                        /*\Think\Log::write($sql);
                        $res = $model->execute($sql);
                        \Think\Log::write($res);*/
                        $res = $model->data($v)->add();
                        if (false === $res) {
                            $model->execute("ROLLBACK");
                            $bool = true;
                            break;
                        }
                    }
                } catch (\Exception $e) {
                    $model->execute("ROLLBACK");
                    throw new Exception($e->getMessage());
                }
            }
            if ($bool) {
                $model->execute("ROLLBACK");
                $msg['status'] = 0;
                $msg['info']   = "保存物流公司失败";
                //$this->ajaxReturn($res);
                return;
            } else {
                $model->execute("commit");
            }
            // 刷新物流映射
            $platform_id = addslashes($platform_id);
            $model->execute("INSERT IGNORE INTO cfg_logistics_shop(shop_id,logistics_type,logistics_code,logistics_id,logistics_name,cod_support ,created) " .
                "SELECT {$shopID},lc.logistics_type,als.logistics_code,als.logistics_id,als.name,als.cod_support,NOW() " .
                "FROM api_logistics_shop als INNER JOIN dict_logistics_code lc ON (als.logistics_code=lc.logistics_code) " .
                "WHERE lc.platform_id={$platform_id} and lc.type = 1 and als.shop_id={$shopID}");

            $model->execute("INSERT IGNORE INTO cfg_logistics_shop(shop_id,logistics_type,logistics_code,logistics_id,logistics_name,cod_support ,created) " .
                "SELECT {$shopID},dl.logistics_type,als.logistics_code,als.logistics_id,als.name,als.cod_support,NOW() " .
                "FROM api_logistics_shop als INNER JOIN dict_logistics dl ON (dl.logistics_name=als.name) " .
                "WHERE als.shop_id={$shopID}");
        }
    }


}