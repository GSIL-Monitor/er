<?php
require_once(ROOT_DIR . '/Manager/utils.php');
require_once(ROOT_DIR . '/Trade/jos.php');

require_once(TOP_SDK_DIR . '/jos/JdClient.php');
require_once(TOP_SDK_DIR . '/jos/JdException.php');
require_once(TOP_SDK_DIR . '/jos/JosRequest.php');
require_once(TOP_SDK_DIR . '/jos/request/delivery/DeliveryLogisticsGetRequest.php');
include_once(TOP_SDK_DIR . '/jos/request/logistics/EtmsWaybillSendRequest.php');
include_once(TOP_SDK_DIR . '/jos/request/logistics/EtmsWaybillcodeGetRequest.php');
include_once(TOP_SDK_DIR . '/jos/request/order/OrderSopOutstorageRequest.php');
include_once(TOP_SDK_DIR . '/jos/request/order/OrderSopWaybillUpdateRequest.php');
include_once(TOP_SDK_DIR . '/jos/request/order/OrderLbpWaybillUpdateRequest.php');
include_once(TOP_SDK_DIR . '/jos/request/order/OrderSoplWaybillUpdateRequest.php');
include_once(TOP_SDK_DIR . '/jos/request/order/OrderLbpOutstorageRequest.php');
include_once(TOP_SDK_DIR . '/jos/request/order/OverseasOrderSopDeliveryRequest.php');
include_once(TOP_SDK_DIR . '/jos/request/order/OverseasOrderSopOutstorageRequest.php');
include_once(TOP_SDK_DIR . '/jos/request/order/OrderSoplOutstorageRequest.php');

require_once(ROOT_DIR . '/Common/api_error.php');

function jos_get_logistics_companies(&$db, &$shop, &$companies, &$error_msg) {
    $jos              = new JdClient();
    $jos->appKey      = $shop->key;
    $jos->appSecret   = $shop->secret;
    $jos->accessToken = $shop->session;
    $req              = new DeliveryLogisticsGetRequest();

    $retval = $jos->execute($req, $shop->session);
    if (API_RESULT_OK != josErrorTest($retval, $db, $shop->shop_id)) {
        logx("get_logistics_companies DeliveryLogisticsGetRequest: {$shop->shop_id} {$retval->error_msg}", $shop->sid . "/Logistics");
        //$error_msg = $retval->error_msg;
        $msg["status"] = 0;
        $msg["info"]   = $retval->error_msg;
        return false;
    } else {
        foreach ($retval->logistics_companies->logistics_list as $company) {
            $companies[] = array
            (
                'shop_id'        => $shop->shop_id,
                'logistics_code' => $company->logistics_id,
                'name'           => $company->logistics_name,
                'cod_support'    => isset($company->is_cod) ? ($company->is_cod ? 2 : 1) : 0,
                'created'        => date('Y-m-d H:i:s', time())
            );

        }

        return true;
    }
}

function jos_send_waybill(&$trade, &$db, &$error_msg) {
    $sid = $trade->SID;

    /*
    if(empty($trade->sender_name) ||
      empty($trade->sender_address) ||
      empty($trade->sender_telno) ||
      empty($trade->receiver_mobile))
    {
        //$db->execute("INSERT INTO sys_notification(type,message,priority) VALUES(3,'京邦达要求仓库设置联系人信息',9)");
        logx("jos_send_waybill 仓库信息不完整!!", $sid);

        $error_msg = '仓库信息不完整!!';
        return false;
    }
    */
    if (empty($trade->sender_name)) {
        $error_msg = '仓库缺少联系人信息！';
        return false;
    }
    if (empty($trade->sender_address)) {
        $error_msg = '仓库缺少地址信息！';
        return false;
    }
    if (empty($trade->sender_telno)) {
        $error_msg = '仓库缺少固话信息！';
        return false;
    }
    if (empty($trade->receiver_mobile)) {
        $error_msg = '缺少收件人手机信息！';
        return false;
    }

    $auth_info = json_decode($trade->logistics_key);
    $shop_id   = (int)$auth_info->shop_id;

    $shop = getShopAuth($sid, $db, $shop_id);
    if (!$shop) {
        logx("jos_send_waybill shop not auth {$shop_id}!!", $sid . "/Logistics");
        $error_msg = '物流公司授权信息错误';
        return false;
    }

    $tid = $db->query_result_single("select src_tids from stockout_order so left join sales_trade st on st.trade_id=so.src_order_id where so.stockout_id={$trade->stockout_id}");

    $jos              = new JdClient();
    $jos->appKey      = $shop->key;
    $jos->appSecret   = $shop->secret;
    $jos->accessToken = $shop->session;
    $req              = new EtmsWaybillSendRequest();

    $req->setDeliveryId($trade->logistics_no);
    $req->setSalePlat("0010001");
    $req->setCustomerCode($auth_info->customer_code);
    $req->setOrderId($tid);
    $req->setThrOrderId($tid);
    $req->setSenderName($trade->sender_name);
    $req->setSenderAddress($trade->sender_address);

    $req->setSenderTel($trade->sender_telno);
    $req->setSenderMobile($trade->sender_mobile);

    $req->setReceiveName($trade->receiver_name);
    $req->setReceiveAddress($trade->receiver_address);
    $req->setReceiveTel($trade->receiver_telno);
    $req->setReceiveMobile($trade->receiver_mobile);

    $req->setPackageCount(1);
    $req->setWeight(round((float)$trade->weight, 2));
    $req->setVloumn(0);

    if (2 == $trade->delivery_term) {
        //这里看能不能使用cod_amount优化?
        $result = jos_order_print_data($db, $sid, $shop_id, $shop->key, $shop->secret, $shop->session, $tid, $data);
        if (!$result) {
            logx("get should_pay fail: $tid {$shop_id}!!", $sid . "/Logistics");
            $error_msg = '京东接口无法返回货到付款金额，请稍后重试！';
            return false;
        }

        logx("jd_cod: " . print_r($data, true), $sid . "/Logistics");
        logx("cod_amount: {$trade->cod_amount}", $sid . "/Logistics");

        $req->setCollectionValue(1);
        $req->setCollectionMoney($data['should_pay']);
    } else {
        $req->setCollectionValue(0);
    }

    $retval = $jos->execute($req);

    if (API_RESULT_OK != josLogisticsErrorTest($retval, $db, $trade->shop_id)) {
        logx("ERROR $sid jos_send_waybill $tid {$retval->error_msg}", $sid . "/Logistics",'error');

        $error_msg = $retval->error_msg;
        return false;
    }

    logx("jos_send_waybill ok: $tid", $sid . "/Logistics");
    return true;
}


function jos_sync_logistics(&$db, &$trade, $sid) {
    getAppSecret($trade, $appkey, $appsecret);

    $jos              = new JdClient();
    $jos->appKey      = $appkey;
    $jos->appSecret   = $appsecret;
    $jos->accessToken = $trade->session;

    if (1 == $trade->sub_platform_id) {
        $sendMode = 'jos_lbp';
        $req      = new OrderLbpOutstorageRequest();
        //only support 1 package
        $req->setPackageNum(1);
    } else if (2 == $trade->sub_platform_id) {
        $sendMode = 'jos_sopl';
        $req      = new OrderSoplOutstorageRequest();
        //only support 1 package
        $req->setPackageNum(1);
    } else if (4 == $trade->sub_platform_id) {
        $sendMode = 'jos_oversea';

        $req = new OverseasOrderSopOutstorageRequest();
        $req->setOrderId($trade->tid);
        $req->setTradeNo($trade->rec_id);
        $retval = $jos->execute($req);
        if (API_RESULT_OK != josErrorTest($retval, $db, $trade->shop_id)) {
            // 订单已出库，不需要调用此函数
            if ($retval->code != 10400001) {
                set_sync_fail($db, $sid, $trade->rec_id, 2, $retval->error_msg);

                logx("WARNING $sid {$sendMode}_stockout_fail: tid {$trade->tid} logistics_no {$trade->logistics_no} logistics_code {$trade->logistics_code} error:{$retval->error_msg}", $sid . "/Logistics");
                return false;
            }
        }

        $req = new OverseasOrderSopDeliveryRequest();
    } else {
        if (is_empty($db, $sid, $trade->rec_id, $trade->tid, $trade->logistics_code)) {
            logx("jos_offline_sync_empty_arg: tid {$trade->tid}, logistics_no {$trade->logistics_no}, logistics_code {$trade->logistics_code}", $sid . "/Logistics");
            return false;
        }
        $sendMode = 'jos_sop';
        $req      = new OrderSopOutstorageRequest();
    }

    $req->setOrderId($trade->tid);
    $req->setWaybill($trade->logistics_no);
    $req->setLogisticsId($trade->logistics_code);
    $req->setTradeNo($trade->rec_id);

    $retval = $jos->execute($req);
    if (API_RESULT_OK != josErrorTest($retval, $db, $trade->shop_id)) {
        set_sync_fail($db, $sid, $trade->rec_id, 2, $retval->error_msg);

        logx("WARNING $sid {$sendMode}_sync_fail: tid {$trade->tid}, logistics_no {$trade->logistics_no}, logistics_code {$trade->logistics_code}, error:{$retval->error_msg}", $sid . "/Logistics");
        return false;
    }

    set_sync_succ($db, $sid, $trade->rec_id);
    logx("{$sendMode}_sync_ok: tid {$trade->tid}", $sid . "/Logistics");

    return true;
}

function jos_resync_logistics(&$db, &$trade, $sid) {
    getAppSecret($trade, $appkey, $appsecret);

    $jos              = new JdClient();
    $jos->appKey      = $appkey;
    $jos->appSecret   = $appsecret;
    $jos->accessToken = $trade->session;

    if (1 == $trade->sub_platform_id) {
        $req = new OrderLbpWaybillUpdateRequest();
    } else if (2 == $trade->sub_platform_id) {
        $req = new OrderSoplWaybillUpdateRequest();
    } else {
        $req = new SopOrderWaybillUpdateRequest();
    }

    $req->setOrderId($trade->tid);
    $req->setWaybill($trade->logistics_no);
    $req->setLogisticsId($trade->logistics_code);
    $req->setTradeNo($trade->rec_id);

    $retval = $jos->execute($req);
    if (API_RESULT_OK != josErrorTest($retval, $db, $trade->shop_id)) {
        if (@$retval->code == "10400014")
            return jos_sync_logistics($db, $trade, $sid);

        set_sync_fail($db, $sid, $trade->rec_id, 2, $retval->error_msg);

        logx("WARNING $sid jos_resync_fail: tid {$trade->tid}, logistics_no {$trade->logistics_no}, logistics_code {$trade->logistics_code} error:{$retval->error_msg}", $sid . "/Logistics");
        return false;
    }

    set_sync_succ($db, $sid, $trade->rec_id);
    logx("jos_sync_ok: tid {$trade->tid}", $sid . "/Logistics");

    return true;
}

function jos_get_waybill($sid, &$db, $waybill_info, &$waybill_list, $total, &$error_msg) {
    $auth_info = json_decode($waybill_info['app_key']);
    $shop_id   = $auth_info->shop_id;
    $shop      = getShopAuth($sid, $db, $shop_id);

    if (!$shop) {
        logx("jos_get_waybill shop not auth {$shop_id}!!", $sid . "/Logistics");
        $error_msg = '物流公司授权信息错误';
        return false;
    }

    $jos              = new JdClient();
    $jos->appKey      = $shop->key;
    $jos->appSecret   = $shop->secret;
    $jos->accessToken = $shop->session;

    $req = new EtmsWaybillcodeGetRequest();

    $max = 100;
    $i   = 0;

    if ($total > $max) {
        for ($i = 0; $i < floor($total / $max); ++$i) {
            $req->setPreNum($max);
            $req->setCustomerCode($auth_info->customer_code);
            $retval = $jos->execute($req);

            if (API_RESULT_OK != josErrorTest($retval, $db, $shop_id)) {
                logx("jos_get_waybill failed: {$retval->error_msg}", $sid . "/Logistics");
                $error_msg = $retval->error_msg;
                return false;
            }

            foreach ($retval->deliveryIdList as $delivery_id) {
                $waybill_list[] = array("logistics_id"   => $waybill_info['logistics_id'],
                                        "logistics_type" => $waybill_info['logistics_type'],
                                        "logistics_no"   => $delivery_id,
                                        "shop_id"        => $shop_id);
            }
        }
    }

    $rest = $total - $i * $max;

    if ($rest >= 0) {
        $req->setPreNum($max);
        $req->setCustomerCode($auth_info->customer_code);
        $retval = $jos->execute($req);

        if (API_RESULT_OK != josErrorTest($retval, $db, $shop_id)) {
            logx("jos_get_waybill failed: {$retval->error_msg}", $sid . "/Logistics");
            $error_msg = $retval->error_msg;
            return false;
        }

        foreach ($retval->deliveryIdList as $delivery_id) {
            $waybill_list[] = array("logistics_id"   => $waybill_info['logistics_id'],
                                    "logistics_type" => $waybill_info['logistics_type'],
                                    "logistics_no"   => $delivery_id,
                                    "shop_id"        => $shop_id);
        }
    }

    return true;

}


?>