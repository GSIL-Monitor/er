<?php
require_once(ROOT_DIR . '/Manager/utils.php');
require_once(ROOT_DIR . '/Logistics/util.php');

require_once(TOP_SDK_DIR . '/top/Logger.php');
require_once(TOP_SDK_DIR . '/top/RequestCheckUtil.php');
require_once(TOP_SDK_DIR . '/top/TopClient.php');
//require_once(ROOT_DIR . '/modules/logistics/offline/TopClient.php');
require_once(TOP_SDK_DIR . '/top/request/LogisticsCompaniesGetRequest.php');
include_once(TOP_SDK_DIR . '/top/request/LogisticsOfflineSendRequest.php');
include_once(TOP_SDK_DIR . '/top/request/LogisticsOnlineSendRequest.php');
include_once(TOP_SDK_DIR . '/top/request/LogisticsConsignResendRequest.php');
include_once(TOP_SDK_DIR . '/top/request/LogisticsOnlineConfirmRequest.php');
include_once(TOP_SDK_DIR . '/top/request/LogisticsOrderShengxianConfirmRequest.php');
include_once(TOP_SDK_DIR . '/top/request/LogisticsDummySendRequest.php');


require_once(TOP_SDK_DIR . '/top/request/WlbWaybillIGetRequest.php');
require_once(TOP_SDK_DIR . '/top/request/WlbWaybillISearchRequest.php');
require_once(TOP_SDK_DIR . '/top/request/WlbWaybillIFullupdateRequest.php');
require_once(TOP_SDK_DIR . '/top/request/WlbWaybillICancelRequest.php');
require_once(TOP_SDK_DIR . '/top/request/WlbWaybillIPrintRequest.php');
require_once(TOP_SDK_DIR . '/top/request/WlbWaybillIQuerydetailRequest.php');
require_once(TOP_SDK_DIR . '/top/request/LogisticsAddressReachableRequest.php');

require_once(TOP_SDK_DIR . '/top/request/SmartwlAssistantGetRequest.php');
require_once(TOP_SDK_DIR . '/top/request/SmartwlPackageCreateRequest.php');
require_once(TOP_SDK_DIR . '/top/request/SmartwlUserinfoGetRequest.php');
require_once(ROOT_DIR . '/Common/api_error.php');

//家装
require_once(TOP_SDK_DIR . '/top/request/WlbOrderJzQueryRequest.php');
require_once(TOP_SDK_DIR . '/top/request/WlbOrderJzConsignRequest.php');
require_once(TOP_SDK_DIR . '/top/request/WlbOrderJzpartnerQueryRequest.php');

//天猫国际直邮发货
require_once(TOP_SDK_DIR . '/top/request/WlbImportThreeplOfflineConsignRequest.php');

//1.获取商家店铺后台默认区域ID
function top_get_logistics_address(&$db, &$shop)
{
    include_once(TOP_SDK_DIR . '/top/request/LogisticsAddressSearchRequest.php');

    $top = new TopClient();
    $top->format = 'json';
    $top->appkey = $shop->key;
    $top->secretKey = $shop->secret;

    $req = new LogisticsAddressSearchRequest();
    $req->setRdef("get_def");

    $retval = $top->execute($req, $shop->session);
    if(API_RESULT_OK != topErrorTest($retval, $db, $shop->shop_id))
    {
        logx("top_get_logistics_address falid.: {$shop->shop_id} {$retval->error_msg}", $shop->sid.'/Logistics');
        $error_msg = $retval->error_msg;
        return false;
    }

    return $retval->addresses->address_result[0]->area_id;
}
//2.获取3PL直邮物流
function top_get_threepl_companise(&$db, &$shop)
{
    include_once(TOP_SDK_DIR . '/top/request/WlbImportThreeplResourceGetRequest.php');

    $area_id = top_get_logistics_address($db, $shop);
    $top = new TopClient();
    $top->format = 'json';
    $top->appkey = $shop->key;
    $top->secretKey = $shop->secret;

    $req = new WlbImportThreeplResourceGetRequest();

    $req->setType("OFFLINE");
    $req->setFromId($area_id);
    $to_address = array();
    $to_address['province']="浙江省";
    $to_address['city']="杭州市";
    $to_address['country']="中国";

    $req->setToAddress(json_encode($to_address));
    $retval = $top->execute($req, $shop->session);
    if(API_RESULT_OK != topErrorTest($retval, $db, $shop->shop_id))
    {
        logx("top_get_threepl_companise falid.: {$shop->shop_id} {$retval->error_msg}", $shop->sid.'/Logistics');
        $error_msg = $retval->error_msg;
        return false;
    }

    if(isset($retval->result->error_code) && $retval->result->error_code == 'S_QUERY_RESOURCE_ERROR')
    {
        logx("top_get_threepl_companise falid1.: {$shop->shop_id} {$retval->result->error_msg}", $shop->sid.'/Logistics');
        return false;
    }

    return $retval->result->resources->three_pl_consign_resource_dto;
}
function top_get_logistics_companies(&$db, &$shop, &$companies, &$error_msg) {
    $top            = new TopClient();
    $top->format    = 'json';
    $top->appkey    = $shop->key;
    $top->secretKey = $shop->secret;
    $req            = new LogisticsCompaniesGetRequest();

    $req->setFields('id,code,name');

    $retval = $top->execute($req, $shop->session);

    if (API_RESULT_OK != topErrorTest($retval, $db, $shop->shop_id)) {
        logx("get_logistics_companies LogisticsCompaniesGetRequest: {$shop->shop_id} {$retval->error_msg}", $shop->sid . "/Logistics");
        $error_msg["status"] = 0;
        $error_msg["info"]   = $retval->error_msg;
        return false;
    } else {
        foreach ($retval->logistics_companies->logistics_company as $company) {
            $companies[] = array
            (
                'shop_id'        => $shop->shop_id,
                'logistics_id'   => $company->id,
                'logistics_code' => $company->code,
                'name'           => $company->name,
                'created'        => date('Y-m-d H:i:s', time())
            );
        }
        $threepls = top_get_threepl_companise($db, $shop);
        if($threepls)
        {
            foreach($threepls as $threepl)
            {
                $companies[] = array
                (
                    'shop_id' => $shop->shop_id,
                    'logistics_id' => $threepl->res_id,
                    'logistics_code' => $threepl->res_code,
                    'name' => $threepl->res_name,
                    'created' => date('Y-m-d H:i:s',time())
                );
            }
        }
        else
        {
            logx("top_get_logistics_companies download 3pl falid . {$shop->shop_id} ", $shop->sid);
        }
        $companies[] = array(
            'shop_id'        => $shop->shop_id,
            'logistics_id'   => 203104,
            'logistics_code' => 'DISTRIBUTOR_12017865',
            'name'           => '安能物流',
            'created'        => date('Y-m-d H:i:s', time())
        );
		$companies[] = array(
            'shop_id'        => $shop->shop_id,
            'logistics_id'   => '',
            'logistics_code' => 'DISTRIBUTOR_13323734',
            'name'           => '九曳鲜配',
            'created'        => date('Y-m-d H:i:s', time())
        );
        $companies[] = array(
            'shop_id'        => $shop->shop_id,
            'logistics_id'   => 204838,
            'logistics_code' => 'B2B-1669519933',
            'name'           => '卡行天下',
            'created'        => date('Y-m-d H:i:s', time())
        );
        $companies[] = array(
            'shop_id'        => $shop->shop_id,
            'logistics_id'   => '',
            'logistics_code' => 'QDHEWL-001',
            'name'           => '日日顺物流',
            'created'        => date('Y-m-d H:i:s', time())
        );

        return true;
    }
}


//安装商下载
function download_top_logistics_insall(&$db, &$shop, &$error_msg)
{
    $top = new TopClient();
    $top->format = 'json';
    $top->appkey = $shop->key;
    $top->secretKey = $shop->secret;
    $req = new WlbOrderJzpartnerQueryRequest();

    $req->setType($shop->server_type);
    $retval = $top->execute($req,$shop->session);

    if(!$retval->is_success)
    {
        logx("download_top_logistics_insall execute fail!! ".print_r($retval,true),$shop->sid.'/Logistics');
        $error_msg = $retval->result_info;
        return false;
    }

    if(isset($retval->install_list->partner_new) && !empty($retval->install_list->partner_new))
    {
        foreach($retval->install_list->partner_new as $row)
        {
            if(!$db->query("insert ignore into api_sys_install "
                ." (shop_id,target_type,target_code,target_name,is_virtual,service_type,created) "
                ." VALUES (%d,1,%s,%s,%d,%d,%s) ON DUPLICATE KEY UPDATE "
                ." target_type=VALUES(target_type),target_code=VALUES(target_code),target_name=VALUES(target_name), "
                ." is_virtual=VALUES(is_virtual),service_type=VALUES(service_type) "
                ,(int)$shop->shop_id,$row->tp_code,$row->tp_name,$row->is_virtual_tp,$row->service_type,date('Y-m-d H:i:s',time())))
            {
                logx("download_top_logistics_insall putDataToTable 1 fail",$shop->sid.'/Logistics');
                $error_msg = '服务器错误:安装服务商存储错误';
                return false;
            }
        }
    }

    if(isset($retval->server_list->partner_new) && !empty($retval->server_list->partner_new))
    {
        foreach($retval->server_list->partner_new as $row)
        {
            if(!$db->query("insert ignore into api_sys_install "
                ." (shop_id,target_type,target_code,target_name,is_virtual,service_type,created) "
                ." VALUES (%d,0,%s,%s,%d,%d,%s) ON DUPLICATE KEY UPDATE "
                ." target_type=VALUES(target_type),target_code=VALUES(target_code),target_name=VALUES(target_name), "
                ." is_virtual=VALUES(is_virtual),service_type=VALUES(service_type) "
                ,(int)$shop->shop_id,$row->tp_code,$row->tp_name,$row->is_virtual_tp,$row->service_type,date('Y-m-d H:i:s',time())))
            {
                logx("download_top_logistics_insall putDataToTable 2 fail",$shop->sid.'/Logistics');
                $error_msg = '服务器错误:物流服务商存储错误';
                return false;
            }
        }
    }

    $install_tmp = array(
        array('tp_name'=>'美家美户旗舰店','tp_code'=>'2088901839'),
        array('tp_name'=>'e安装旗舰店','tp_code'=>'2629396740'),
        array('tp_name'=>'居家通旗舰店','tp_code'=>'1023847759'),
        array('tp_name'=>'咕咕管家旗舰店','tp_code'=>'2585515132'),
        array('tp_name'=>'师傅邦旗舰店','tp_code'=>'2539117623'),
        array('tp_name'=>'上海蓝锤网络科技有限公司','tp_code'=>'2335404857'),
        array('tp_name'=>'杭州户帮户企业管理有限公司','tp_code'=>'2011492230'),
        array('tp_name'=>'神工众志（北京）科技有限公司','tp_code'=>'2101922596'),
        array('tp_name'=>'苏宁帮客旗舰店','tp_code'=>'2688671203'),
        array('tp_name'=>'e帮手旗舰店','tp_code'=>'2682670629'),
        array('tp_name'=>'中国联保旗舰店','tp_code'=>'2828150564'),
        array('tp_name'=>'蚁安居旗舰店','tp_code'=>'2938689098'),
    );

    foreach ($install_tmp as $row) {
        $db->query("insert ignore into api_sys_install(shop_id,target_type,target_code,target_name,is_virtual,service_type,created)VALUES (%d,1,%s,%s,0,11,%s) ON DUPLICATE KEY UPDATE target_type=VALUES(target_type),target_code=VALUES(target_code),target_name=VALUES(target_name),is_virtual=VALUES(is_virtual),service_type=VALUES(service_type) "
            ,(int)$shop->shop_id,$row['tp_code'],$row['tp_name'],date('Y-m-d H:i:s',time()));
    }

    return true;
}

//淘宝家装发货
function top_jz_logistics(&$db,&$trade, $sid)
{
    getAppSecret($trade,$appkey,$appsecret);

    $top = new TopClient();
    $top->appkey = $appkey;
    $top->secretKey = $appsecret;
    $req = new WlbOrderJzQueryRequest();//家装查询物流公司

    $req->setTid($trade->tid);
    $retval = $top->execute($req,$trade->session);

    if(!$retval->result_success || $retval->result_success=='false')
    {
        $error_msg = $retval->result_error_msg;
        if($error_msg == '订单服务类型异常' || strpos($error_msg, "非家装业务订单") !== FALSE)
        {
            if ($trade->msg == "CD01#发货方式不匹配")
            {
                set_sync_fail($db, $sid, $trade->rec_id, 2, $retval->error_msg);
                logx("top_sync_fail3: tid {$trade->tid}, logistics_no {$trade->logistics_no}, logistics_code {$trade->logistics_code}, error:{$retval->error_msg}", $sid.'/Logistics');
                return false;
            }
            logx("top_jz_logistics fail!! {$error_msg} tid:{$trade->tid} retval:".print_r($retval,true),$sid.'/Logistics');
            return top_offline_sync_logistics($db,$trade,$sid);
        }
        set_sync_fail($db,$sid,$trade->rec_id, 2, $error_msg);
        logx("top_jz_logistics fail!! 从接口获取默认安装商 tid:{$trade->tid} error_msg:{$error_msg} retval:".print_r($retval,true),$sid.'/Logistics');
        return false;
    }

    //安装商信息
    $ins_tp_dto = array();
    $support_install = $retval->result->support_install;//是否支持安装
    $support_install = sprintf($support_install);
    $jz_flag = 0;
    if(is_bool($support_install) && $support_install===true)
    {
        $jz_flag = 1;
        logx("top_jz_logistics tid:{$trade->tid} jz_flag:{$jz_flag},安装商-该订单需要安装 support_install:".$support_install,$sid.'/Logistics');
    }else if(is_int($support_install) && $support_install===1){
        $jz_flag = 2;
        logx("top_jz_logistics tid:{$trade->tid} jz_flag:{$jz_flag},安装商-该订单需要安装 support_install:".$support_install,$sid.'/Logistics');
    }else if(is_string($support_install) && ($support_install==="true" || $support_install==="1")){
        $jz_flag = 3;
        logx("top_jz_logistics tid:{$trade->tid} jz_flag:{$jz_flag},安装商-该订单需要安装 support_install:".$support_install,$sid.'/Logistics');
    }else{
        logx("top_jz_logistics tid:{$trade->tid} jz_flag:{$jz_flag},安装商-该订单不需要安装 support_install:".$support_install,$sid.'/Logistics');
    }

    if($jz_flag)
    {
        $install_type = 0;
        if(isset($retval->result->ins_tps) && !empty($retval->result->ins_tps))
        {
            $install_tmp = $retval->result->ins_tps->instps;
            if(count($install_tmp) == 1){
                $ins_tp_dto['name'] = sprintf($install_tmp[0]->name);
                $ins_tp_dto['code'] = sprintf($install_tmp[0]->code);
                logx("top_jz_logistics tid:{$trade->tid} 安装商-使用接口返回的唯一安装商".print_r($ins_tp_dto,true),$sid.'/Logistics');
                $install_type = 1;
            }

            //使用接口返回的默认安装公司
            foreach($retval->result->ins_tps->instps as $row)
            {
                if(isset($row->is_default) && !empty($row->is_default))
                {
                    $ins_tp_dto['name'] = sprintf($row->name);
                    $ins_tp_dto['code'] = sprintf($row->code);
                    logx("top_jz_logistics tid:{$trade->tid} 安装商-使用接口返回的默认安装商".print_r($ins_tp_dto,true),$sid.'/Logistics');
                    $install_type = 1;
                }
            }

            if($install_type == 0)
            {
                $install_id = json_decode($trade->flag,true);
                $install_id = $install_id['jiazhuang'];
                if(!empty($install_id))
                {
                    //根据默认安装商的Id从安装商列表获取安装商信息
                    $ins = $db->query_result("select * from api_sys_install where rec_id={$install_id} and is_disabled=0 and shop_id={$trade->shop_id}");
                    if($ins)
                    {
                        $ins_tp_dto['name'] = $ins['target_name'];
                        $ins_tp_dto['code'] = $ins['target_code'];
                        logx("top_jz_logistics tid:{$trade->tid} 使用为这个订单单独设置的安装公司".print_r($ins_tp_dto,true).' install_id:'.print_r($install_id,true),$sid.'/Logistics');
                        $install_type = 2;
                    }
                }
            }

            //使用店铺中设置的默认安装商
            if(empty($ins_tp_dto))
            {
                $ins = $db->query_result("select * from api_sys_install where is_defalut=1 and is_disabled=0 and shop_id={$trade->shop_id}");
                if(!empty($ins))
                {
                    $ins_tp_dto['name'] = $ins['target_name'];
                    $ins_tp_dto['code'] = $ins['target_code'];
                    logx("top_jz_logistics tid:{$trade->tid} 安装商-使用店铺中设置的安装公司".print_r($ins_tp_dto,true),$sid.'/Logistics');
                    $install_type = 3;
                }else{
                    logx("top_jz_logistics tid:{$trade->tid} 安装商-店铺中没有设置安装公司",'error');
                }
            }

            //因为获取根据服务类型获取安装商的接口停止维护，所以默认使用商家自有安装商
            if(empty($ins_tp_dto))
            {
                foreach($retval->result->ins_tps->instps as $row)
                {
                    if(stripos($row->name,'自有') !== false)
                    {
                        $ins_tp_dto['name'] = sprintf($row->name);
                        $ins_tp_dto['code'] = sprintf($row->code);
                        logx("top_jz_logistics tid:{$trade->tid} 安装商-使用接口返回的自有安装商".print_r($ins_tp_dto,true),$sid.'/Logistics');
                        $install_type = 4;
                    }
                }
            }

        }

        if($install_type == 0)
        {
            $error_msg = '安装商-接口没有默认和自有的安装商';
            set_sync_fail($db,$sid,$trade->rec_id,2,$error_msg);
            logx("top_jz_logistics fail!! tid:{$trade->tid} error_msg:{$error_msg} ins_list:".print_r($retval->result->ins_tps->instps,true),$sid.'/Logistics','error');
            return false;
        }
    }

    //物流商信息
    $lg_tp_dto = array();
    if(isset($retval->result->lg_cps) && !empty($retval->result->lg_cps))
    {
        foreach($retval->result->lg_cps->lgcps as $key)
        {
            if($key->code == $trade->logistics_code)
            {
                $lg_tp_dto['name'] = sprintf($key->name);
                $lg_tp_dto['code'] = sprintf($key->code);
                logx("top_jz_logistics tid:{$trade->tid} 物流商-使用编码一致的物流商".print_r($lg_tp_dto,true),$sid.'/Logistics');
            }
        }

        if(empty($lg_tp_dto))
        {
            foreach($retval->result->expresses->expresses as $key)
            {
                if($key->code == $trade->logistics_code)
                {
                    $lg_tp_dto['name'] = sprintf($key->name);
                    $lg_tp_dto['code'] = sprintf($key->code);
                    logx("top_jz_logistics tid:{$trade->tid} 物流商-使用编码一致的快递信息".print_r($lg_tp_dto,true),$sid.'/Logistics');
                }
            }
        }

        if(empty($lg_tp_dto))
        {
            foreach($retval->result->lg_cps->lgcps as $key)
            {
                if(stripos($key->name,'自有') !== false){
                    $lg_tp_dto['name'] = sprintf($key->name);
                    $lg_tp_dto['code'] = sprintf($key->code);
                    logx("top_jz_logistics tid:{$trade->tid} 物流商-使用自有物流商".print_r($lg_tp_dto,true),$sid.'/Logistics');
                }
            }
        }

        if(empty($lg_tp_dto))
        {
            $error_msg = '物流商-接口没有对应编码的物流公司和快递公司以及自有物流';
            set_sync_fail($db,$sid,$trade->rec_id,2,$error_msg);
            logx("top_jz_logistics fail!! tid:{$trade->tid} error_msg:{$error_msg} lg_list:".print_r($retval->result->lg_cps->lgcps,true),$sid.'/Logistics','error');
            return false;
        }
    }else{
        foreach($retval->result->expresses->expresses as $key)
        {
            if($key->code == $trade->logistics_code)
            {
                $lg_tp_dto['name'] = sprintf($key->name);
                $lg_tp_dto['code'] = sprintf($key->code);
                logx("top_jz_logistics tid:{$trade->tid} 物流商-接口未返回物流商列表 使用编码一致的快递信息".print_r($lg_tp_dto,true),$sid.'/Logistics');
            }
        }

        if(empty($lg_tp_dto))
        {
            $error_msg = '物流商-接口未返回物流商列表 物流商列表为空';
            set_sync_fail($db,$sid,$trade->rec_id,2,$error_msg);
            logx("top_jz_logistics fail!! tid:{$trade->tid} error_msg:{$error_msg} lg_list:".print_r($retval->result->lg_cps->lgcps,true),$sid.'/Logistics','error');
            return false;
        }
    }

    //获取物流公司电话
    $tel = $db->query_result_single("select telno from cfg_logistics where logistics_id={$trade->logistics_id}");
    //发货信息
    $jz_top_args = array();
    $jz_top_args['zy_company'] = $lg_tp_dto['name'];
    $jz_top_args['mail_no'] = $trade->logistics_no;
    $jz_top_args['zy_phone_number'] = $tel;
    $jz_top_args['zy_consign_time'] = date('Y-m-d H:i:s',time());

    unset($req);
    $req = new WlbOrderJzConsignRequest();
    $req->setTid($trade->tid);

    if(!empty($lg_tp_dto))
    {
        $req->setLgTpDto(json_encode($lg_tp_dto));
    }

    if(!empty($ins_tp_dto))
    {
        $req->setInsTpDto(json_encode($ins_tp_dto));
    }

    $req->setJzTopArgs(json_encode($jz_top_args));
    $retval = $top->execute($req,$trade->session);
    if(!$retval->result_success || $retval->result_success=='false')
    {
        if($retval->result_error_msg == 'jzTopArgs.ZyPhoneNumber()不可为空')
            $retval->result_error_msg = '淘宝家装发货，物流公司电话不能为空';
        $error_msg = $retval->result_error_msg;
        set_sync_fail($db,$sid,$trade->rec_id,2,$error_msg);
        logx("top_jz_logistics fail!! 发货失败 tid:{$trade->tid} error_msg:{$error_msg} retval:".print_r($retval,true),$sid.'/Logistics');
        return false;
    }

    set_sync_succ($db, $sid, $trade->rec_id);
    logx("top_jz_sync_ok: tid {$trade->tid}", $sid.'/Logistics');

    return true;
}

function top_offline_sync_logistics(&$db, &$trade, $sid) {
    getAppSecret($trade, $appkey, $appsecret);
    $top            = new TopClient();
    $top->format    = 'json';
    $top->appkey    = $appkey;
    $top->secretKey = $appsecret;

    if ($trade->logistics_type == 1) {
        $req = new LogisticsDummySendRequest();
    } else {
        if (is_empty($db, $sid, $trade->rec_id, $trade->tid, $trade->logistics_code, $trade->logistics_no)) {
            logx("top_offline_sync_empty_arg: tid {$trade->tid}, logistics_no {$trade->logistics_no}, logistics_code {$trade->logistics_code}", $sid . "/Logistics");
            return false;
        }
        $req = new LogisticsOfflineSendRequest();
        if (1 == $trade->is_part_sync) {
            handle_special_oid($db, $sid, $trade->platform_id, $trade->tid, $trade->trade_id, $trade->oids, $error_msg);
            $req->setSubTid($trade->oids);
            $req->setIsSplit($trade->is_part_sync);
        }
        $req->setOutSid($trade->logistics_no);
        $req->setCompanyCode($trade->logistics_code);
    }

    $req->setTid($trade->tid);

    $retval = $top->execute($req, $trade->session);
    if (API_RESULT_OK != topErrorTest($retval, $db, $trade->shop_id))
    {
        if (@$retval->sub_code == "isv.logistics-update-company-or-mailno-error:CD19") {
            return top_resync_logistics($db, $trade, $sid);
        }else if(@$retval->sub_msg =='CD01#发货方式不匹配'){
            $trade->msg = 'CD01#发货方式不匹配';
            return top_jz_logistics($db,$trade, $sid);
        }else if(@$retval->sub_code == "CD86")
        {
            $trade->msg = '您的订单无法通过此接口进行发货操作 #CD86';
            return top_threepl_sync_logistics($db,$trade, $sid);
        }

        if (@$retval->sub_code == "isv.logistics-offline-service-error:B150"//发货异常，请稍等后重试
            //添加新的异常信息
            || @$retval->sub_code == "isp.top-remote-connection-timeout"
        ) {
            if (isset($trade->try_times) && $trade->try_times < 2)
                set_sync_fail($db, $sid, $trade->rec_id, -100, $retval->error_msg);
        } else {
            set_sync_fail($db, $sid, $trade->rec_id, 2, $retval->error_msg);
        }


        if (1 == $trade->is_part_sync) logx("top_sync_fail: tid {$trade->tid} oids:{$trade->oids}", $sid . "/Logistics");
        logx("WARNING $sid top_sync_fail: tid {$trade->tid}, logistics_no {$trade->logistics_no}, logistics_code {$trade->logistics_code}, error:{$retval->error_msg}", $sid . "/Logistics");
        return false;
    }

    set_sync_succ($db, $sid, $trade->rec_id);
    logx("top_sync_ok: tid {$trade->tid}", $sid . "/Logistics");

    return true;
}

function top_resync_logistics(&$db, &$trade, $sid) {
    if ($trade->logistics_type == 1) {
        return top_offline_sync_logistics($db, $trade, $sid);
    }

    getAppSecret($trade, $appkey, $appsecret);

    $top            = new TopClient();
    $top->format    = 'json';
    $top->appkey    = $appkey;
    $top->secretKey = $appsecret;
    $req            = new LogisticsConsignResendRequest();

    $req->setTid($trade->tid);

    if (1 == $trade->is_part_sync) {
        handle_special_oid($db, $sid, $trade->platform_id, $trade->tid, $trade->trade_id, $trade->oids, $error_msg);
        $req->setSubTid($trade->oids);
        $req->setIsSplit($trade->is_part_sync);
    }
    $req->setOutSid($trade->logistics_no);
    $req->setCompanyCode($trade->logistics_code);

    $retval = $top->execute($req, $trade->session);
    if (API_RESULT_OK != topErrorTest($retval, $db, $trade->shop_id)) {
        if (@$retval->sub_code == "isv.logistics-offline-service-error:B27") {
            return top_offline_sync_logistics($db, $trade, $sid);
        }

        if (@$retval->sub_code == "isp.logistics-online-service-error:S01"//系统异常
            //添加新的异常信息
        ) {
            if (isset($trade->try_times) && $trade->try_times < 2)
                set_sync_fail($db, $sid, $trade->rec_id, -100, $retval->error_msg);
        } else {
            set_sync_fail($db, $sid, $trade->rec_id, 2, $retval->error_msg);
        }


        logx("WARNING $sid top_resync_fail: tid {$trade->tid}, logistics_no {$trade->logistics_no}, logistics_code {$trade->logistics_code}, error:{$retval->error_msg}", $sid . "/Logistics",'error');
        return false;
    }

    set_sync_succ($db, $sid, $trade->rec_id);
    logx("top_resync_ok: tid {$trade->tid}", $sid . "/Logistics");

    return true;
}

// for cod type 
function top_online_sync_logistics(&$db, &$trade, $sid) {
    getAppSecret($trade, $appkey, $appsecret);

    $top            = new TopClient();
    $top->format    = 'json';
    $top->appkey    = $appkey;
    $top->secretKey = $appsecret;
    $req            = new LogisticsOnlineSendRequest();

    $req->setTid($trade->tid);
    if (1 == $trade->is_part_sync) {
        handle_special_oid($db, $sid, $trade->platform_id, $trade->tid, $trade->trade_id, $trade->oids, $error_msg);
        $req->setSubTid($trade->oids);
        $req->setIsSplit($trade->is_part_sync);
    }
    $req->setOutSid($trade->logistics_no);
    $req->setCompanyCode($trade->logistics_code);

    $retval = $top->execute($req, $trade->session);
    if (API_RESULT_OK != topErrorTest($retval, $db, $trade->shop_id)) {
        $error_msg = $retval->error_msg;
        if (isset($trade->try_times) && $trade->try_times < 2) {
            set_sync_fail($db, $sid, $trade->rec_id, -100, $error_msg);
        }else {
            set_sync_fail($db, $sid, $trade->rec_id, 2, $error_msg);
        }

        logx("WARNING $sid top_ol_sync_fail: tid {$trade->tid}, logistics_no {$trade->logistics_no}, logistics_code {$trade->logistics_code}, error:{$error_msg}", $sid . "/Logistics",'error');
        return false;
    }

    set_sync_succ($db, $sid, $trade->rec_id);
    logx("top_ol_sync_ok: tid {$trade->tid}", $sid . "/Logistics");

    return true;
}

function top_shengxian_sync_logistics(&$db, &$trade, $sid) {
    getAppSecret($trade, $appkey, $appsecret);

    $top            = new TopClient();
    $top->format    = 'json';
    $top->appkey    = $appkey;
    $top->secretKey = $appsecret;

    $req = new LogisticsOrderShengxianConfirmRequest();
    $req->setTid($trade->tid);
    $req->setOutSid($trade->logistics_no);

    $send_type = intval($trade->send_type) - 1;
    $req->setDeliveryType($send_type);

    $retval = $top->execute($req, $trade->session);
    if (API_RESULT_OK != topErrorTest($retval, $db, $trade->shop_id)) {
        $error_msg = $retval->error_msg;
        set_sync_fail($db, $sid, $trade->rec_id, 2, $error_msg);

        logx("WARNING $sid top_shengxian_sync_fail: tid {$trade->tid}, logistics_no {$trade->logistics_no}, send_type {$trade->send_type}, error:{$error_msg}", $sid . "/Logistics",'error');
        return false;
    }

    set_sync_succ($db, $sid, $trade->rec_id);
    logx("top_shengxian_sync_ok: tid {$trade->tid}", $sid . "/Logistics");

    return true;
}

function top_online_reachable_logistics(&$db, &$trade, $sid, &$error_msg, &$error_code) {
    getAppSecret($trade, $appkey, $appsecret);

    $top            = new TopClient();
    $top->format    = 'json';
    $top->appkey    = $appkey;
    $top->secretKey = $appsecret;
    $req            = new LogisticsOnlineSendRequest();

    $req->setTid($trade->tid);
    if (1 == $trade->is_part_sync) {
        handle_special_oid($db, $sid, $trade->platform_id, $trade->tid, $trade->trade_id, $trade->oids, $error_msg);
        $req->setSubTid($trade->oids);
        $req->setIsSplit($trade->is_part_sync);
    }

    $req->setCompanyCode($trade->logistics_code);

    $retval = $top->execute($req, $trade->session);
    logx("top_ol_reachable_retval: " . print_r($retval, true), $sid . "/Logistics");
    if (API_RESULT_OK != topErrorTest($retval, $db, $trade->shop_id)) {
        $error_msg  = $retval->error_msg;
        $error_code = $retval->sub_code;
        logx("WARNING $sid top_ol_reachable_fail: tid {$trade->tid}, logistics_no {$trade->logistics_no}, logistics_code {$trade->logistics_code}, error:{$error_msg}", $sid . "/Logistics",'error');
        $db->execute("update api_logistics_sync set sync_status = -2, is_online = 0 where rec_id={$trade->rec_id} ");
        return false;
    }

    $db->execute("update api_logistics_sync set sync_status = -1, is_online = 0 where rec_id={$trade->rec_id} ");

    logx("top_ol_reachable_ok: tid {$trade->tid}", $sid . "/Logistics");

    return true;
}

function top_online_cancel_logistics(&$db, &$trade, $sid) {
    getAppSecret($trade, $appkey, $appsecret);

    $top            = new TopClient();
    $top->format    = 'json';
    $top->appkey    = $appkey;
    $top->secretKey = $appsecret;
    $req            = new LogisticsOnlineCancelRequest();

    $req->setTid($trade->tid);

    $retval = $top->execute($req, $trade->session);
    logx("top_ol_cancel_retval: " . print_r($retval, true), $sid . "/Logistics");
    if (API_RESULT_OK != topErrorTest($retval, $db, $trade->shop_id)) {
        logx("WARNING $sid top_ol_cancel_fail: tid {$trade->tid}, error:{$retval->error_msg}", $sid . "/Logistics",'error');
        return false;
    }

    logx("top_ol_cancel_ok: tid {$trade->tid}", $sid . "/Logistics");

    return true;
}

function top_online_confirm_logistics(&$db, &$trade, $sid) {
    getAppSecret($trade, $appkey, $appsecret);

    $top            = new TopClient();
    $top->format    = 'json';
    $top->appkey    = $appkey;
    $top->secretKey = $appsecret;
    $req            = new LogisticsOnlineConfirmRequest();

    $req->setTid($trade->tid);
    if (1 == $trade->is_part_sync) {
        handle_special_oid($db, $sid, $trade->platform_id, $trade->tid, $trade->trade_id, $trade->oids, $error_msg);
        $req->setSubTid($trade->oids);
        $req->setIsSplit($trade->is_part_sync);
    }
    $req->setOutSid($trade->logistics_no);
    $retval = $top->execute($req, $trade->session);
    if (API_RESULT_OK != topErrorTest($retval, $db, $trade->shop_id)) {
        if (isset($trade->try_times) && $trade->try_times < 2) {
            set_sync_fail($db, $sid, $trade->rec_id, -100, $retval->error_msg);
        } else {
            set_sync_fail($db, $sid, $trade->rec_id, 2, $retval->error_msg);
        }

        logx("WARNING $sid top_ol_confirm_sync_fail: tid {$trade->tid}, logistics_no {$trade->logistics_no}, logistics_code {$trade->logistics_code}, error:{$retval->error_msg}", $sid . "/Logistics",'error');
        return false;
    }

    set_sync_succ($db, $sid, $trade->rec_id);
    logx("top_ol_confirm_sync_ok: tid {$trade->tid}", $sid . "/Logistics");

    return true;
}

/*
	获取淘宝电子面单
*/
function top_get_waybill($appkey, $appsecret, $sessionkey, $WaybillApplyNewRequest) {
    $top            = new TopClient;
    $top->format    = 'json';
    $top->appkey    = $appkey;
    $top->secretKey = $appsecret;
    $req            = new WlbWaybillIGetRequest;
    $req->setWaybillApplyNewRequest($WaybillApplyNewRequest);

    return $top->execute($req, $sessionkey);
}

function top_update_waybill($appkey, $appsecret, $sessionkey, $tradeOrderInfoCols) {
    $top            = new TopClient;
    $top->format    = 'json';
    $top->appkey    = $appkey;
    $top->secretKey = $appsecret;
    $req            = new WlbWaybillIFullupdateRequest;
    $req->setWaybillApplyFullUpdateRequest($tradeOrderInfoCols);

    return $top->execute($req, $sessionkey);
}

/*打印校验*/
function top_print_waybill($appkey, $appsecret, $sessionkey, $WaybillApplyPrintcheckRequest) {
    $top            = new TopClient;
    $top->format    = 'json';
    $top->appkey    = $appkey;
    $top->secretKey = $appsecret;
    $req            = new WlbWaybillIPrintRequest;
    $req->setWaybillApplyPrintCheckRequest($WaybillApplyPrintcheckRequest);

    return $top->execute($req, $sessionkey);
}

/*查询单号信息*/
function top_query_waybill($appkey, $appsecret, $sessionkey, $waybillDetailQueryRequest) {
    $top            = new TopClient;
    $top->appkey    = $appkey;
    $top->secretKey = $appsecret;
    $req            = new WlbWaybillIQuerydetailRequest;
    $req->setWaybillDetailQueryRequest($waybillDetailQueryRequest);
    return $top->execute($req, $sessionkey);
}

function top_cancel_waybill($appkey, $appsecret, $sessionkey, $waybillCancelRequest) {
    $top            = new TopClient;
    $top->appkey    = $appkey;
    $top->secretKey = $appsecret;
    $req            = new WlbWaybillICancelRequest;
    $req->setWaybillApplyCancelRequest($waybillCancelRequest);
    return $top->execute($req, $sessionkey);
}

/**
 * 查询面单余额，物流公司信息
 **/
function top_search_waybill($appkey, $appsecret, $sessionkey, $WaybillApplyRequest) {
    $top            = new TopClient;
    $top->format    = 'json';
    $top->appkey    = $appkey;
    $top->secretKey = $appsecret;
    $req            = new WlbWaybillISearchRequest;
    $req->setWaybillApplyRequest($WaybillApplyRequest);

    return $top->execute($req, $sessionkey);
}

/*
	检查云栈电子面单地址是否可达
    partnerID 平台物流id 在api_logistics_shop里有 cfg_logistics_shop里也有存
    address  发货的地址 省市县 用空格隔开  北京 北京市 海淀区
    现已支持筛单的快递公司共15家：中国邮政、EMS、国通、汇通、快捷、全峰、优速、圆通、宅急送、中通、顺丰、天天、韵达、德邦快递、申通
*/
function top_address_reachable($partnerID, $address, &$error_msg) {
    global $ekb_top_app_config;
    $top            = new TopClient;
    $top->format    = 'json';
    $top->appkey    = $ekb_top_app_config['app_key'];
    $top->secretKey = decodeDbPwd($ekb_top_app_config['app_secret'],$ekb_top_app_config['app_key']);
    $req            = new LogisticsAddressReachableRequest;
    $req->setAddress($address);
    $req->setPartnerIds($partnerID);
    $req->setServiceType(88);
    $result = $top->execute($req);
    logx(print_r($result,true));
    if (API_RESULT_OK != topErrorTest($result)) {
        $error_msg = $result->error_msg;
        return false;
    }
    $ret = @($result->reachable_result_list->address_reachable_result);
    if (empty($ret)) {
        $error_msg = '淘宝返回格式错误';
        return false;
    }
    $ret       = (array)$ret[0];
    $error_msg = @$ret['error_code'];
    if (!empty($error_msg)) {
        return false;
    }
    $reachable = @$ret['reachable'];
    if ($reachable == 0)     //只有返回不可达时才不可达，返回不确定则视为可达
    {
        return false;
    }
    return true;
}

/*获取智选物流客户信息*/
function top_smart_logistics_userinfo($appkey, $appsecret, $sessionkey) {
    $top            = new TopClient;
    $top->format    = 'json';
    $top->appkey    = $appkey;
    $top->secretKey = $appsecret;
    $req            = new SmartwlUserinfoGetRequest;

    return $top->execute($req, $sessionkey);
}

function top_smart_logistics_get($appkey, $appsecret, $sessionkey, $query_info) {
    $top            = new TopClient;
    $top->format    = 'json';
    $top->appkey    = $appkey;
    $top->secretKey = $appsecret;
    $req            = new SmartwlAssistantGetRequest;
    $req->setCpidList($query_info['cpid_list']);
    $req->setOrderSource($query_info['order_source']);
    $req->setReceiveAddress($query_info['receive_address']);
    $req->setSendAddress($query_info['send_address']);
    $req->setServiceType($query_info['service_type']);
    $req->setTradeOrder($query_info['trade_order']);

    return $top->execute($req, $sessionkey);
}


function top_smart_logistics_sync($appkey, $appsecret, $sessionkey, $query_info) {
    $top            = new TopClient;
    $top->format    = 'json';
    $top->appkey    = $appkey;
    $top->secretKey = $appsecret;
    $req            = new SmartwlPackageCreateRequest;
    $req->setCpId($query_info['cpid_id']);
    $req->setOrderSrc($query_info['order_source']);
    $req->setTradeOrder($query_info['trade_order']);
    $req->setMailNo($query_info['mail_no']);
    $req->setWeight($query_info['weight']);

    return $top->execute($req, $sessionkey);
}

//天猫国际直邮发货
function top_threepl_sync_logistics(&$db, &$trade, $sid)
{
    getAppSecret($trade, $appkey, $appsecret);

    $top = new TopClient();
    $top->format = 'json';
    $top->appkey = $appkey;
    $top->secretKey = $appsecret;
    $req = new WlbImportThreeplOfflineConsignRequest();

    $req->setTradeId($trade->tid);
    $req->setResId($trade->logistics_platform_id);
    $req->setResCode($trade->logistics_code);
    $req->setWaybillNo($trade->logistics_no);

    $retval = $top->execute($req, $trade->session);
    if(API_RESULT_OK != topErrorTest($retval, $db, $trade->shop_id))
    {
        $error_msg = $retval->error_msg;

        set_sync_fail($db, $sid, $trade->rec_id, 2, $error_msg);

        logx("top_3pl_sync_fail: shop_id {$trade->shop_id}, tid {$trade->tid}, logistics_no {$trade->logistics_no}, logistics_code {$trade->logistics_code}, logistics_platform_id {$trade->logistics_platform_id}, error:{$error_msg}", $sid.'/Logistics');
        return false;
    }
    if(isset($retval->result->success) && $retval->result->success !=1)
    {
        $error_msg = @$retval->result->error_code."  ".@$retval->result->error_msg;
        set_sync_fail($db, $sid, $trade->rec_id, 2, $error_msg);
        logx("top_3pl_sync_fail3: shop_id {$trade->shop_id}, tid {$trade->tid}, logistics_no {$trade->logistics_no}, logistics_code {$trade->logistics_code}, logistics_platform_id {$trade->logistics_platform_id}, error:{$error_msg}", $sid.'/Logistics');
        return false;
    }

    set_sync_succ($db, $sid, $trade->rec_id);
    logx("top_3pl_sync_ok: shop_id {$trade->shop_id}, tid {$trade->tid}, logistics_no {$trade->logistics_no}, logistics_code {$trade->logistics_code}, logistics_platform_id {$trade->logistics_platform_id}", $sid.'/Logistics');

    return true;
}

?>