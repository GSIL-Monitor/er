<?php

define('API_RESULT_OK', 0);
define('API_RESULT_RETRY', 1);
define('API_RESULT_FAILED', 2);

function markShopAuthExpired(&$db, $shopId) {
    if (!$shopId) return;

    $db->execute("UPDATE cfg_shop SET auth_state=2 WHERE shop_id=$shopId AND auth_state=1");
    //消息通知
    if ($db->query_result_single("SELECT ROW_COUNT()") > 0) //阻止反复通知
    {
        $shopName = $db->query_result_single("SELECT shop_name FROM cfg_shop WHERE shop_id=$shopId");
        $message = "店铺-$shopName-授权失效";

        //发即时消息
        $msg = array(
            'type'  => 10,
            'topic' => 'shop_auth_fail',
            'msg'   => $message
        );
        SendMerchantNotify($db->get_tag(), $msg);

        $message = addslashes($message);
        $db->execute("INSERT INTO sys_notification(type,message,priority) VALUES(2,'$message',9)");

    }
}

function markAlipayAuthExpired(&$db, $shopId) {
    if (!$shopId) return;

    $db->execute("UPDATE cfg_shop SET pay_auth_state=2 WHERE shop_id=$shopId AND pay_auth_state=1");
    //消息通知
    if ($db->query_result_single("SELECT ROW_COUNT()") > 0) //阻止反复通知
    {
        $shopName = $db->query_result_single("SELECT shop_name FROM cfg_shop WHERE shop_id=$shopId");
        $message = "店铺-$shopName-支付宝授权失效";

        //发即时消息
        $msg = array(
            'type'  => 10,
            'topic' => 'shop_pay_auth_fail',
            'msg'   => $message
        );
        SendMerchantNotify($db->get_tag(), $msg);

        $message = addslashes($message);
        $db->execute("INSERT INTO sys_notification(type,message,priority) VALUES(2,'$message',9)");

    }
}

function topErrorTest(&$result, &$db, $shopId) {
    if (empty($result) || !is_object($result)) {
        $errstr = print_r($result, true);
        logx("TopInvalid_Response: $errstr");
        logx("ERROR topErrorTest $errstr");

        $result = (object)array('code' => -1, 'error_msg' => '淘宝服务器失败');
        return API_RESULT_RETRY;
    }

    if (isset($result->code) && $result->code <> 0) {
        if (isset($result->sub_code))
            $sub_code = ' #' . $result->sub_code;
        else
            $sub_code = '';

        if (isset($result->sub_msg))
            $msg = $result->sub_msg . $sub_code;
        else if (isset($result->msg))
            $msg = $result->msg . $sub_code;
        else if (isset($result->sub_code)) {
            $msg = $result->sub_code;
            logx("TOP_ERROR " . print_r($result, true));
        } else {
            switch ($result->code) {
                case 7:
                    $msg = '应用调用次数超限';
                    break;
                case 9:
                    $msg = 'HTTP方法被禁止';
                    break;
                case 10:
                    $msg = '服务不可用';
                    break;
                case 11:
                    $msg = '开发者权限不足';
                    break;
                case 12:
                    $msg = '用户权限不足';
                    break;
                case 13:
                    $msg = '合作伙伴权限不足';
                    break;
                case 21:
                    $msg = '缺少方法名参数';
                    break;
                case 22:
                    $msg = '不存在的方法名';
                    break;
                case 23:
                    $msg = '无效数据格式';
                    break;
                case 24:
                    $msg = '缺少签名参数';
                    break;
                case 25:
                    $msg = '无效签名';
                    break;
                case 26:
                    $msg = '缺少SessionKey参数';
                    break;
                case 27:
                    $msg = '无效的SessionKey参数';
                    break;
                case 28:
                    $msg = '缺少AppKey参数';
                    break;
                case 29:
                    $msg = '无效的AppKey参数';
                    break;
                case 30:
                    $msg = '缺少时间戳参数';
                    break;
                case 31:
                    $msg = '非法的时间戳参数';
                    break;
                case 32:
                    $msg = '缺少版本参数';
                    break;
                case 33:
                    $msg = '非法的版本参数';
                    break;
                case 34:
                    $msg = '不支持的版本号';
                    break;
                case 42:
                    $msg = '短授权权限不足';
                    break;
                case 43:
                    $msg = '参数错误';
                    break;
                case 47:
                    $msg = '编码错误';
                    break;
                case 52:
                    $msg = '后台组件服务执行出现异常';
                    break;
                default:
                    $msg = '未知错误' . print_r($result, true);
                    break;
            }
        }


        $result->error_msg = $msg;

        if ($result->code == 26 || $result->code == 27) {
            markShopAuthExpired($db, $shopId);
        }

        logx("TOP_ERROR {$result->code} $msg");
        logx("ERROR topErrorTest {$result->code} $msg");

        switch ($result->code) {
            case 10:
            case 52:
            case 520:
                return API_RESULT_RETRY;
        }

        return API_RESULT_FAILED;
    }

    return API_RESULT_OK;
}

//京东
function josErrorTest(&$result, &$db, $shopId) {
    if (empty($result) || !is_object($result)) {
        $errstr = print_r($result, true);
        logx("JosInvalid_Response: $errstr");
        logx("ERROR josErrorTest $errstr");

        $result = (object)array('code' => -1, 'error_msg' => '京东服务器失败');
        return API_RESULT_RETRY;
    }

    if (isset($result->code) && $result->code <> 0 && $result->code <> 100) {
        switch ($result->code) {
            case 5:
                $msg = '不支持的版本号';
                break;
            case 12:
                $msg = '无效签名';
                break;
            case 15:
                $msg = '不存在的方法名';
                break;
            case 19:
                $msg = '无效access_token';
                break;
            case 21:
                $msg = '无效App Key';
                break;
            case 24:
                $msg = '该商家无权调用API';
                break;
            case 65:
                $msg = '系统处理超时！请重新进行检索！';
                break;
            default:
                if (isset($result->zh_desc))
                    $msg = $result->zh_desc;
                else if (isset($result->message))
                    $msg = $result->message;
                else
                    $msg = '未知错误' . print_r($result, true);
                break;
        }

        $result->error_msg = $msg;

        logx("JOS_ERROR {$result->code} $msg");
        logx("ERROR josErrorTest {$result->code} $msg");

        if ($result->code == 19 || $result->code == 11100006) //11100006 店铺未开通
        {
            markShopAuthExpired($db, $shopId);
        }

        switch ($result->code) {
            case 33:
                return API_RESULT_RETRY;
        }

        return API_RESULT_FAILED;
    }

    if (isset($result->process_code) && $result->process_code <> 1) {

        if (isset($result->error_message)) {
            $msg = $result->error_message;
        } else {
            $msg = '未知错误' . print_r($result, true);
        }

        $result->error_msg = $msg;

        logx("JOS_ERROR {$result->process_code} $msg");
        logx("ERROR josErrorTest {$result->process_code} $msg");

        return API_RESULT_FAILED;
    }

    return API_RESULT_OK;
}

//京东二期
function josSecondErrorTest(&$result, &$db, $shopId) {
    if (empty($result) || !is_object($result)) {
        $errstr = print_r($result, true);
        logx("JosSecondInvalid_Response: $errstr");
        logx("ERROR josSecondErrorTest $errstr");

        $result = (object)array('code' => -1, 'error_msg' => '京东服务器失败');
        return API_RESULT_RETRY;
    }
    logx("record jos eclp v2 result " . print_r($result, true) . "\n");//由于京云仓二期的错误信息格式有所变化，所依据记录一下
    logx("ERROR josErrorTest record jos eclp v2 result " . print_r($result, true) . "\n");//由于京云仓二期的错误信息格式有所变化，所依据记录一下
    if (isset($result->code) && count((array)$result) == 3) {
        if (array_key_exists("zh_desc", $result) !== false && array_key_exists("en_desc", $result) !== false) {
            $get_code_ar = explode(" ", $result->code);
            $match_code_iterm = $get_code_ar[0];
            $match_code_iterm_small_str = strtolower($match_code_iterm);
            switch ($match_code_iterm_small_str) {
                case 'master':
                case 'goods':
                case 'so':
                case 'po':
                case 'stock':
                case 'rtw':
                case 'rts':
                case 'checkstock':
                default: {
                    $msg = $result->zh_desc;
                }

            }
            $result->error_msg = $msg;

            logx("JOS_SEC_ERROR {$result->code} $msg");
            logx("ERROR josSecondErrorTest {$result->code} $msg");
            return API_RESULT_FAILED;
        }

    }
    if (isset($result->code) && count(((array)$result)) == 2) {
        if ((int)$result->code != 0) {
            if (array_key_exists("msg", (array)$result) === true && array_key_exists("code", (array)$result) === true) {
                if ((int)$result->code != 1) {
                    $msg = $result->msg;
                    $result->error_msg = $msg;

                    logx("JOS_ERROR {$result->code} $msg");
                    logx("ERROR josErrorTest {$result->code} $msg");

                    return API_RESULT_FAILED;
                } else {
                    return API_RESULT_OK;
                }
            }
        } else {
            foreach ((array)$result as $key => $value) {
                if (stripos($key, "result") !== false) {
                    if (empty($value)) {
                        $msg = "返回值为空";
                        $result->error_msg = $msg;

                        logx("JOS_ERROR NULL $msg");
                        logx("ERROR josErrorTest NULL $msg");

                        return API_RESULT_FAILED;
                    }
                }
            }
        }


    }
    if (array_key_exists("resultCode", (array)$result) && array_key_exists("eclpRtwNo", (array)$result)) {


        if ((int)$result->resultCode != 1) {
            $msg = $result->reason;
            $result->error_msg = $msg;

            logx("JOS_ERROR {$result->resultCode} $msg");
            logx("ERROR josErrorTest {$result->resultCode} $msg");
            return API_RESULT_FAILED;
        } else {
            return API_RESULT_OK;
        }


    }
    if (array_key_exists("code", (array)$result) && count((array)$result) == 2 && (int)$result->code == 0) {

        foreach ($result as $key => $value) {
            if (stripos($key, "updateresult") !== false) {
                if ((int)$value == 1) {
                    return API_RESULT_OK;
                } else {
                    if (isset($result->msg)) {
                        $msg = $result->msg;
                    } else {
                        $msg = '更新信息不成功' . print_r($result, true);
                    }

                    $result->error_msg = $msg;

                    logx("JOS_ERROR {$result->code} $msg");
                    logx("ERROR josErrorTest {$result->code} $msg");

                    return API_RESULT_FAILED;
                }
            }
        }
    }
    if (isset($result->code) && $result->code <> 0 && $result->code <> 100) {

        switch ($result->code) {
            case 5:
                $msg = '不支持的版本号';
                break;
            case 12:
                $msg = '无效签名';
                break;
            case 15:
                $msg = '不存在的方法名';
                break;
            case 19:
                $msg = '无效access_token';
                break;
            case 21:
                $msg = '无效App Key';
                break;
            case 24:
                $msg = '该商家无权调用API';
                break;
            case 33:
                $msg = '系统处理超时！请重新进行检索！';
                break;
            default:
                if (isset($result->zh_desc))
                    $msg = $result->zh_desc;
                else if (isset($result->message))
                    $msg = $result->message;
                else
                    $msg = '未知错误' . print_r($result, true);
                break;
        }

        $result->error_msg = $msg;

        logx("JOS_SEC_ERROR {$result->code} $msg");
        logx("ERROR josSecondErrorTest {$result->code} $msg");

        if ($result->code == 19) {
            markShopAuthExpired($db, $shopId);
        }

        switch ($result->code) {
            case 33:
                return API_RESULT_RETRY;
        }

        return API_RESULT_FAILED;
    }

    if (isset($result->process_code) && $result->process_code <> 1) {

        if (isset($result->error_message)) {
            $msg = $result->error_message;
        } else {
            $msg = '未知错误' . print_r($result, true);
        }

        $result->error_msg = $msg;

        logx("JOS_ERROR {$result->process_code} $msg");
        logx("ERROR josErrorTest {$result->process_code} $msg");

        return API_RESULT_FAILED;
    }

    return API_RESULT_OK;
}


function josLogisticsErrorTest(&$result, &$db, $shopId) {
    if (empty($result) || !is_object($result)) {
        $errstr = print_r($result, true);
        logx("JosInvalid_Response: $errstr");
        logx("ERROR josLogisticsErrorTest $errstr");

        $result = (object)array('code' => -1, 'error_msg' => '京东服务器失败');
        return API_RESULT_RETRY;
    }

    if (isset($result->code) &&$result->code<>0 && $result->code <> 100) {
        switch ($result->code) {
            case 5:
                $msg = '不支持的版本号';
                break;
            case 12:
                $msg = '无效签名';
                break;
            case 15:
                $msg = '不存在的方法名';
                break;
            case 19:
                $msg = '无效access_token';
                break;
            case 21:
                $msg = '无效App Key';
                break;
            case 24:
                $msg = '该商家无权调用API';
                break;
            case 33:
                $msg = '系统处理超时！请重新进行检索！';
                break;
            default:
                if (isset($result->zh_desc))
                    $msg = $result->zh_desc;
                else if (isset($result->message))
                    $msg = $result->message;
                else
                    $msg = '未知错误' . print_r($result, true);
                break;
        }

        $result->error_msg = $msg;

        logx("JOS_ERROR {$result->code} $msg");
        logx("ERROR josLogisticsErrorTest {$result->code} $msg");

        if ($result->code == 19 || $result->code == 11100006) //11100006 店铺未开通
        {
            markShopAuthExpired($db, $shopId);
        }

        return API_RESULT_FAILED;
    }

    return API_RESULT_OK;
}

//paipai
function ppErrorTest(&$result, &$db, $shopId) {
    if (empty($result) || !is_object($result)) {
        $errstr = print_r($result, true);
        logx("PpInvalid_Response: $errstr");
        logx("ERROR ppErrorTest $errstr");

        $result = (object)array('code' => -1, 'error_msg' => '拍拍服务器失败');
        return API_RESULT_RETRY;
    }

    if (isset($result->errorCode) && $result->errorCode <> 0) {
        if (isset($result->errorMessage))
            $msg = $result->errorMessage;
        else
            $msg = '未知错误' . print_r($result, true);

        $result->error_msg = $msg;

        logx("PP_ERROR {$result->errorCode} $msg");
        logx("ERROR ppErrorTest {$result->errorCode} $msg");

        //101 ErrorMsg:系统失败,oidb CheckAccessToken error
        if (18 == $result->errorCode || 1267 == $result->errorCode || 101 == $result->errorCode) {
            markShopAuthExpired($db, $shopId);
        }

        switch ($result->errorCode) {
            case 4096:
            case 4097:
            case 4098:
            case 4099:
            case 6:
            case 432:
            case 12291:
                return API_RESULT_RETRY;
        }

        return API_RESULT_FAILED;
    }

    return API_RESULT_OK;
}

//1号店
//yhd.system.param.checkcode_invalid
//yhd.system.param.sign_invalid
function yhdErrorTest(&$result, &$db, $shopId) {
    if (empty($result) || !is_object($result)) {
        $errstr = print_r($result, true);
        logx("YhdInvalid_Response: $errstr");
        logx("ERROR yhdErrorTest $errstr");

        $result = (object)array('code' => -1, 'error_msg' => '1号店服务器失败');
        return API_RESULT_RETRY;
    }

    if (isset($result->errorCount) && $result->errorCount <> 0) {
        $code = '';
        $msg = '';
        foreach ($result->errInfoList->errDetailInfo as $err) {
            if ($err->errorCode == 'yhd.system.param.sessionkey_not_found') {
                markShopAuthExpired($db, $shopId);
            }
            if (empty($code)) {
                $code = $err->errorCode;
                $msg = $err->errorDes;
            } else {
                $code .= ',' . $err->errorCode;
                $msg .= ',' . $err->errorDes;
            }
        }

        $result->code = $code;
        $result->error_msg = $msg;

        if ($code == 'yhd.orders.get.not_found')
            return API_RESULT_OK;

        logx("YHD_ERROR $code $msg");
        logx("ERROR yhdErrorTest $code $msg");

        return API_RESULT_FAILED;
    }

    return API_RESULT_OK;
}

//当当网
function ddErrorTest(&$result, &$db, $shopId) {
    if (empty($result) || !is_object($result)) {
        $errstr = print_r($result, true);
        logx("DdInvalid_Response: $errstr");
        logx("ERROR ddErrorTest $errstr");

        $result = (object)array('code' => -1, 'error_msg' => 'dangdang服务器失败');
        return API_RESULT_RETRY;
    }

    if (isset($result->errorCode) && $result->errorCode == 176)    //APP与SHOP订购关系过期
    {
        markShopAuthExpired($db, $shopId);
    }

    if (isset($result->Error) && $result->Error->operCode) {
        $code = $result->Error->operCode;
        $msg = $result->Error->operation;

        $result->error_msg = $msg;

        logx("DD_ERROR $code $msg");
        logx("ERROR ddErrorTest $code $msg");

        return API_RESULT_FAILED;
    } else if (isset($result->Result) &&
        isset($result->Result->OrdersList) &&
        isset($result->Result->OrdersList->OrderInfo) &&
        (int)$result->Result->OrdersList->OrderInfo->orderOperCode != 0
    ) {
        $code = $result->Result->OrdersList->OrderInfo->orderOperCode;
        $msg = $result->Result->OrdersList->OrderInfo->orderOperation;

        $result->error_msg = $msg;

        logx("DD_ERROR $code $msg");
        logx("ERROR ddErrorTest $code $msg");

        return API_RESULT_FAILED;
    } else if (isset($result->errorCode)) {
        $code = $result->errorCode;
        $msg = $result->errorMessage;

        $result->error_msg = $msg;

        logx("dd_error $code $msg");
        return API_RESULT_FAILED;
    }else if(isset($result->Result) && $result->Result->operCode <> 0)
    {
        $code=$result->Result->operCode;
        $msg=$result->Result->operation;

        $result->error_msg=$msg;

        logx("dd_error1  $code $msg");
        return API_RESULT_FAILED;
    }

    return API_RESULT_OK;
}

//库巴
function coo8ErrorTest(&$result, &$db, $shopId) {
    if (empty($result) || !is_object($result)) {
        $errstr = print_r($result, true);
        logx("Coo8Invalid_Response: $errstr");
        logx("ERROR coo8ErrorTest $errstr");

        $result = (object)array('code' => -1, 'error_msg' => '库巴服务器失败');
        return API_RESULT_RETRY;
    }

    if (isset($result->msg)) {
        $res_str = strstr($result->msg, "String index out of range");
        if (!empty($res_str)) {
            markShopAuthExpired($db, $shopId);
        }

    }

    if (count($result) == 1 && isset($result->msg) && $result->msg != 'SUCCESS') {
        $result->error_msg = $result->msg;

        logx("COO8_ERROR {$result->msg}");
        logx("ERROR coo8ErrorTest {$result->msg}");

        return API_RESULT_FAILED;
    }

    return API_RESULT_OK;
}

function alibabaErrorTest(&$result, &$db, $shopId) {
    if (empty($result) || !is_object($result)) {
        $errstr = print_r($result, true);
        logx("aliInvalid_Response: $errstr");
        logx("ERROR alibabaErrorTest $errstr");

        $result = (object)array('code' => -1, 'error_msg' => '阿里服务器失败');
        return API_RESULT_RETRY;
    }

    if (isset($result->success) && $result->success) {
        return API_RESULT_OK;
    }

    if (isset($result->total) && $result->total == 0 && empty($result->toReturn)) {
        return API_RESULT_OK;
    }
    if (isset($result->orderModel))
    {
        return API_RESULT_OK;
    }
    /*
    会发生有数据但是success为空的情况
    if(isset($result->total) && empty($result->success))
    {
        return API_RESULT_OK;
    }
    */
    if (isset($result->resultMsg))
        $result->error_msg = $result->resultMsg;
    else if (empty($result->error_message))
        $result->error_msg = print_r($result, true);
    else
        $result->error_msg = $result->error_message;

    if (isset($result->error_code)) {
        $result->error_msg = 'error_msg:' . $result->error_msg . '  ' . 'error_code:' . $result->error_code;
    }
    logx("ALI_ERROR {$result->error_msg}");
    logx("ERROR alibabaErrorTest  {$result->error_msg}");

    return API_RESULT_FAILED;
}

function ecsErrorTest(&$result, &$db, $shopId) {
    if (empty($result) || !is_object($result)) {
        $errstr = print_r($result, true);
        logx("ecsInvalid_Response: $errstr");
        logx("ERROR ecsErrorTest $errstr");

        $result = (object)array('code' => -1, 'error_msg' => 'ECShop服务器失败');
        return API_RESULT_RETRY;
    }

    if (isset($result->code) && $result->code == -4) //[desc] => username pass error
    {
        markShopAuthExpired($db, $shopId);
    }

    if (isset($result->code) && $result->code) {
        $result->error_msg = $result->desc;
        logx("ECS_ERROR {$result->code} {$result->error_msg}");
        logx("ERROR ecsErrorTest {$result->code} {$result->error_msg}");

        return API_RESULT_FAILED;
    }

    return API_RESULT_OK;
}

function jumeiErrorTest(&$result, &$db, $shopId) {
    if (empty($result) || !is_object($result)) {
        $errstr = print_r($result, true);
        logx("jumeiInvalid_Response: $errstr");
        logx("ERROR jumeiErrorTest $errstr");

        $result = (object)array('code' => -1, 'error_msg' => '聚美服务器失败');
        return API_RESULT_RETRY;
    }

    if (isset($result->code) && 403 == $result->code)//[message] => Client_id should be numeric
    {
        markShopAuthExpired($db, $shopId);
    }

    if (isset($result->code) && $result->code <> 0) {
        $result->error_msg = $result->message;
        logx("Jumei_ERROR code:{$result->code} msg:{$result->message}");
        logx("ERROR jumeiErrorTest code:{$result->code} msg:{$result->message}");

        return API_RESULT_FAILED;
    }

    if (isset($result->error) && $result->error <> 0) {
        $result->error_msg = $result->error;
        logx("Jumei_ERROR {$result->error_msg}");
        logx("ERROR jumeiErrorTest {$result->error_msg}");

        return API_RESULT_FAILED;
    }

    return API_RESULT_OK;
}

//口袋通
function kdtErrorTest(&$result, &$db, $shopId)
{
    if(empty($result) || !is_array($result))
    {
        $errstr = print_r($result, true);
        logx("kdtInvalid_Response: $errstr");
        logx("ERROR kdtErrorTest $errstr", 'error');

        $result = array('code'=>-1, 'error_msg' => '口袋通服务器失败');
        return API_RESULT_RETRY;
    }

    if(isset($result['error_response']['code']) && $result['error_response']['code'] <> 0)
    {
        $result['code'] = $result['error_response']['code'];
        if($result['code']==40002 || $result['code']==40009 || $result['code']==40011)
        {
            markShopAuthExpired($db, $shopId);
        }

        $result['error_msg'] = $result['error_response']['msg'];

        logx("Kdt_ERROR code:{$result['error_response']['code']} msg:{$result['error_response']['msg']}");
        logx("ERROR KdtErrorTest code:{$result['error_response']['code']} msg:{$result['error_response']['msg']}", 'error');

        return API_RESULT_FAILED;
    }

    return API_RESULT_OK;
}
//微铺宝
function vpubaoErrorTest(&$result, &$db, $shopId) {
    if (empty($result) || !is_array($result)) {
        $errstr = print_r($result, true);
        logx("vpubaoInvalid_Response:$errstr");
        logx("ERROR vpubaoErrorTest $errstr");

        $result = (object)array('code' => -1, 'error_msg' => '微铺宝服务器失败');
        return API_RESULT_RETRY;
    }

    if (isset($result['code']) && $result['code'] <> 0)//请求接收结果（0成功，非0失败）
    {
        $result['error_msg'] = $result['message'];
        if ($result['message'] == '访问未被商家授权') {
            markShopAuthExpired($db, $shopId);
        }

        logx("Vpubao_ERROR {$result['message']}");
        logx("ERROR vpubaoErrorTest {$result['message']}");

        return API_RESULT_FAILED;
    }

    return API_RESULT_OK;
}

//麦考林
function mklErrorTest(&$result, &$db, $shopId) {
    if (empty($result) || !is_object($result)) {
        $errstr = print_r($result, true);
        logx("mklInvalid_Response: $errstr");
        logx("ERROR mklErrorTest $errstr");

        $result = (object)array('code' => -1, 'error_msg' => '麦考林服务器失败');
        return API_RESULT_RETRY;
    }

    if (isset($result->ResultCode) && $result->ResultCode <> 0) {
        switch ($result->ResultCode) {
            case 1:
                $msg = '处理失败';
                break;
            case 10:
                $msg = '已处理完成的订单';
                break;
            case 20:
                $msg = '不存在“已付款”并等待确认的订单';
                break;
            case 30:
                $msg = '无法处理[退换申请/可直接领取的商品/优惠券商品]';
                break;
            case 200:
                $msg = '订单号匹配错误';
                break;
            case 998:
                $msg = '商家认证码（key）错误';
                break;
            case 999:
                $msg = '其他错误';
                break;
        }
        logx("MKL_ERROR {$result->ResultCode} $msg");
        logx("ERROR mklErrorTest {$result->ResultCode} $msg");

        return API_RESULT_FAILED;
    }
    return API_RESULT_OK;
}

//sunning
function sosErrorTest(&$result, &$db, $shopId) {
    if (empty($result) || !is_object($result)) {
        $errstr = print_r($result, true);
        logx("sosInvalid_Response: $errstr");
        logx("ERROR sosErrorTest $errstr");

        $result = (object)array('code' => -1, 'error_msg' => '苏宁服务器失败');
        return API_RESULT_RETRY;
    }

    if (isset($result->sn_error)) {
        /*if ($result->sn_error->error_code == 'sys.check.api-permission:authority' || $result->sn_error->error_code == 'sys.check.oauth-permission:authority' || $result->sn_error->error_code == 'sys.oauth.check.access_token:overdue' || $result->sn_error->error_code == 'sys.check.method-permission:authority' || $result->sn_error->error_code == 'sys.oauth.check.access_token:inexistence') {
            markShopAuthExpired($db, $shopId);
        }*/

        $result->error_msg = $result->sn_error->error_code;
        logx("SOS_ERROR {$result->error_msg}");
        logx("ERROR sosErrorTest {$result->error_msg}");

        return API_RESULT_FAILED;
    }

    return API_RESULT_OK;
}

function vipshopErrorTest(&$result, &$db, $shopId) {
    if (empty($result) || !is_object($result)) {
        $errstr = print_r($result, true);
        logx("vipshopInvalid_Response: $errstr");
        logx("ERROR vipshopErrorTest $errstr");

        $result = (object)array('code' => -1, 'error_msg' => '唯品会服务器失败');
        return API_RESULT_RETRY;
    }
    if (isset($result->returnCode))
    {
        if ($result->returnCode == 'CALLEE_USER_30111'
            || $result->returnCode == 'CALLEE_USER_vipapis.JitDeliveryService.invalid-parameter'
            || $result->returnCode == 'CALLEE_USER_vipapis.DvdDeliveryService.invalid-parameter'
            || $result->returnCode == 'CALLEE_USER_vipapis.access-denied'
            || $result->returnCode == 'vipapis.oauth-invalidate-failure'
            || $result->returnCode == 'vipapis.DvdDeliveryService.invalid-parameter'
            || strpos(@$result->returnMessage, "输入的供应商ID不能为空") !== FALSE
            || $result->returnCode == '30111')//授权错误或者缺少
        {
            markShopAuthExpired($db, $shopId);
        }
        if (isset($result->returnMessage) && !empty($result->returnMessage)) {
            logx("VIP_ERROR returncode:{$result->returnCode} returnmsg:{$result->returnMessage}");
            logx("ERROR vipErrorTest  {$result->returnMessage}");
            return API_RESULT_FAILED;
        }
    }


    if (isset($result->success_num) && $result->success_num == 0) {
        logx("vipshop_sync_logistics ship fail!");
        return API_RESULT_FAILED;
    }

    return API_RESULT_OK;
}

function meilishuoErrorTest(&$result, &$db, $shopId)//mls
{
    if(empty($result) || !is_object($result))
    {
        $errstr = print_r($result, true);
        logx("meilishuoInvalid_Response: $errstr");
        logx("ERROR meilishuoErrorTest $errstr", 'error');

        $result = (object)array('code'=>-1, 'error_msg' => '美丽说服务器失败!');
        return API_RESULT_RETRY;
    }

    if(isset($result->status->code) && ($result->status->code == '0000011'))
    {
        markShopAuthExpired($db, $shopId);
        logx("meilishuoErrorTest shopid: {$shopId} msg:{$result->status->msg} 授权失效了");
    }

    if(isset($result->status->code) && $result->status->code == "0000000")
    {
        return API_RESULT_OK;
    }

    if(isset($result->status->code) && $result->status->code <> "0000000")
    {
        $result->error_msg = 'code:' . $result->status->code.'  '.'msg:'. $result->status->msg;

        logx("MLS_ERROR {$result->error_msg}");
        logx("ERROR meilishuoErrorTest  {$result->error_msg}", 'error');
        return API_RESULT_FAILED;
    }

    return API_RESULT_OK;
}

//蘑菇街
function mgjErrorTest(&$result, &$db, $shopId) {
    if (empty($result) || !is_object($result)) {
        $errstr = print_r($result, true);
        logx("mgjInvalid_Response: $errstr");
        logx("ERROR mgjErrorTest $errstr");

        $result = (object)array('code' => -1, 'error_msg' => '蘑菇街服务器失败');
        return API_RESULT_RETRY;
    }

    if (isset($result->status->code) && 10018 == $result->status->code)//[msg] => 授权通行证不存在或已过期
    {
        markShopAuthExpired($db, $shopId);
    }

    if (isset($result->status->code) && $result->status->code <> 10001) {

        $result->error_msg = $result->status->msg;
        logx("mgj_ERROR $result->error_msg");
        //logx("ERROR mgjErrorTest $result->result->data", 'error');

        return API_RESULT_FAILED;
    }

    return API_RESULT_OK;
}
//蜜芽宝贝
function miaErrorTest(&$result, &$db, $shopId)
{
    if (empty($result) || !is_object($result))
    {
        $errstr = print_r($result,true);
        logx("MiaInvalid_Response: $errstr");
        logx("ERROR sfErrorTest $errstr", 'error');

        $result = (object)array('code'=>-1, 'msg' => '蜜芽服务器失败');
        return API_RESULT_RETRY;
    }

    if (intval($result->code) != 200)
    {
        if (intval($result->code) != 182)
        {
            if($result->code == 500 && $result->msg == '身份信息不存在')
            {
                markShopAuthExpired($db, $shopId);//授权失败/或者授权已经过期
            }
            $msg = $result->msg;
            logx("MIA_ERROR  $msg");
            logx("ERROR miaErrorTest  $msg", 'error');
            return API_RESULT_FAILED;
        }
    }

    return API_RESULT_OK;
}
//贝贝网
function bbwErrorTest(&$result, &$db, $shopId) {
    if (empty($result) || !is_object($result)) {
        $errstr = print_r($result, true);
        logx("bbwInvalid_Response: $errstr");
        logx("ERROR bbwErrorTest $errstr");

        $result = (object)array('code' => -1, 'error_msg' => '贝贝网服务器失败');
        return API_RESULT_RETRY;
    }
    if (isset($result->err_msg) && !empty($result->err_msg)) {
        $result->error_msg = $result->err_msg;
        logx("bbw_ERROR {$result->error_msg}");
        logx("ERROR bbwErrorTest {$result->error_msg}");

        return API_RESULT_FAILED;
    }
    if ($result->success == false) {
        $result->error_msg = $result->message;
        logx("bbw_ERROR {$result->error_msg}");
        logx("ERROR bbwErrorTest {$result->error_msg}");

        return API_RESULT_FAILED;
    }

    return API_RESULT_OK;
}
//考拉
function klErrorTest(&$result, &$db, $shopId)
{
    if (empty($result)) {
        $errstr = print_r($result, true);
        logx("kaolaInvalid_Response:$errstr");
        logx("ERROR kaolaErrorTest $errstr", 'error');
        $result = (object)array('code' => -1, 'error_msg' => '考拉服务器失败');
        return API_RESULT_RETRY;
    }
    if (isset($result->error_response->subErrors)) {
        $subErrors = $result->error_response->subErrors;
        for ($i=0; $i < count($subErrors); $i++) {
            $result->code = $subErrors[$i]->code;
            $result->error_msg = $subErrors[$i]->msg;
            logx("kaola_ERROR msg:{$result->error_msg}");
            logx("ERROR klErrorTest msg:{$result->error_msg}");
            return API_RESULT_FAILED;
        }
    }
    return API_RESULT_OK;

}
//ecstore
function ecstoreErrorTest(&$result, &$db, $shopId) {
    if (empty($result) || !is_object($result)) {
        $errstr = print_r($result, true);
        logx("ecstoreInvalid_Response: $errstr");
        logx("ERROR ecstoreErrorTest $errstr");

        $result = (object)array('code' => -1, 'error_msg' => 'Ecstore服务器失败');
        return API_RESULT_RETRY;
    }

    if (isset($result->rsp) && !empty($result->rsp) && $result->rsp == 'fail') {
        if (isset($result->data) && !empty($result->data) && $result->data != 'true')
            $result->error_msg = $result->data;

        if (isset($result->res) && !empty($result->res) && $result->res != 'true')
            $result->error_msg = $result->res;

        logx("ecstore_ERROR {$result->error_msg}");
        logx("ERROR ecstoreErrorTest {$result->error_msg}");

        return API_RESULT_FAILED;
    }

    return API_RESULT_OK;
}

function zheErrorTest(&$result, &$db, $shopId) {
    
    if (empty($result) || !is_object($result)) {
        $errstr = print_r($result, true);
        logx("ZheInvalid_Response:$errstr");
        logx("ERROR zheErrorTest $errstr");

        $result = (object)array('code' => -1, 'error_msg' => 'zhe800服务器失败');
        return API_RESULT_RETRY;
    }

    if (isset($result->data->errors[0]) && $result->data->errors[0] == 'token错误') {
        markShopAuthExpired($db, $shopId);
    }

    if (isset($result->code) && $result->code != 200) {

        $code = $result->code;

        $msg = $result->data->errors[0];



        $result->error_msg = $msg;
        logx("ZHE800_ERROR $code $msg");
        logx("ERROR zhe800ErrorTest $code $msg");

        return API_RESULT_FAILED;

    }



    return API_RESULT_OK;
}

function icbcErrorTest(&$result, &$db, $shopId) {
	if (empty ( $result ) || ! is_array ( $result ))
	{
		$errstr = print_r ( $result, true );
		logx ( "IcbcInvalid_Response:$errstr" );

		$result = array();
        $result['error_msg'] = $errstr;
		return API_RESULT_RETRY;
	}

	if ($result ['head']['ret_msg'] == '授权码已过期！' || $result ['head']['ret_msg'] == 'APPKEY与AUTHCODE未找到匹配记录')
	{
		markShopAuthExpired($db, $shopId);
	}

	/*if (!is_int($result['head']['ret_code']))
	{
		$result ['error_msg'] = $result ['head']['ret_msg'];
		logx ( "icbc_ERROR {$result ['head']['ret_msg']}" );
		logx ( "ERROR icbcErrorTest {$result ['head']['ret_msg']}", 'error' );

		return API_RESULT_FAILED;
	}*/

    if (isset ( $result ['head']['ret_code'] ) && $result ['head']['ret_code'] != 0) // 请求接收结果（0成功，非0失败）
	{
		$result ['error_msg'] = $result ['head']['ret_msg'];
		logx ( "icbc_ERROR {$result ['head']['ret_msg']}" );

		return API_RESULT_FAILED;
	}

	return API_RESULT_OK;
}

//穿衣助手
function ichuanyiErrorTest(&$result, &$db, $shopId) {
    if (empty($result) || !is_array($result) || !isset($result['data'])) {
        $errstr = print_r($result, true);
        logx("ichuanyiInvalid_Response: $errstr");
        logx("ERROR ichuanyiErrorTest $errstr");

        $result = array('code' => -1, 'error_msg' => '穿衣助手服务器失败');
        return API_RESULT_RETRY;
    }

    if (isset($result['result']) && $result['result'] > 0) {

        $result['error_msg'] = $result['msg'];
        logx("ichuanyi_ERROR {$result['error_msg']}");
        logx("ERROR ichuanyiErrorTest {$result['error_msg']}");

        return API_RESULT_FAILED;
    }

    return API_RESULT_OK;
}

//君乐宝南京美驰crm
function crmErrorTest(&$result, &$db, $shopId) {
    if (empty($result) || !isset($result['SALES_ORDER'])) {
        $errstr = print_r($result, true);
        logx("crm_Response: $errstr");
        logx("ERROR crmErrorTest $errstr");

        $result = array('code' => -1, 'error_msg' => '南京美驰crm系统服务器失败');
        return API_RESULT_RETRY;
    }

    if (isset($result) && !is_array($result)) {
        $result = array('error_msg' => $result);
        // $result->error_msg = $result
        logx("crm_ERROR {$result}");
        logx("ERROR crmErrorTest {$result}");
        return API_RESULT_FAILED;
    }
    return API_RESULT_OK;
}

//君乐宝jlb
function jlbErrorTest(&$result, &$db, $shopId) {
    if (empty($result) || !isset($result->SALES_ORDER)) {
        $errstr = print_r($result, true);
        logx("jlb_Response: $errstr");
        logx("ERROR jlbErrorTest $errstr");

        $result = array('code' => -1, 'error_msg' => '君乐宝系统服务器失败');
        return API_RESULT_RETRY;
    }
    if (isset($result) && is_array($result)) {
        $result = array('error_msg' => $result);
        // $result->error_msg = $result
        logx("jlb_ERROR {$result}");
        logx("ERROR jlbErrorTest {$result}");
        return API_RESULT_FAILED;
    }
    return API_RESULT_OK;
}

//楚楚街
function ccjErrorTest(&$result, &$db, $shopId) {
    if (empty($result) || !is_object($result)) {
        $errstr = print_r($result, true);
        logx("CcjInvalid_Response: $errstr");
        logx("ERROR ccjErrorTest $errstr");

        $result = (object)array('code' => -1, 'error_msg' => '楚楚街服务器失败');
        return API_RESULT_RETRY;
    }

    if (isset($result->message) && $result->message == '权限验证失败') {
        markShopAuthExpired($db, $shopId);
    }

    if (isset($result->code) && $result->code <> 0) {
        $msg = $result->message;
        $result->error_msg = $msg;

        if($result->code !=300)
        {
            logx("CCJ_ERROR {$result->code} $msg");
            logx("ERROR ccjErrorTest {$result->code} $msg");
        }

        return API_RESULT_FAILED;
    }

    return API_RESULT_OK;
}

//微盟
function weimoErrorTest(&$result, &$db, $shopId)
{
    if(empty($result) || !is_object($result))
    {
        $errstr = print_r($result, true);
        logx("weimoInvalid_Response: $errstr");
        logx("ERROR weimoErrorTest $errstr", 'error');

        $result = (object)array('code'=>-1, 'error_msg' => '微盟旺铺服务器失败');
        return API_RESULT_RETRY;
    }

    if(isset($result->code->errcode) && $result->code->errcode <> 0 && $result->code->errcode <> 204)
    {
        if($result->code->errcode == 8000001)
        {
            markShopAuthExpired($db, $shopId);
        }

        $code = $result->code->errcode;

        $result->error_msg = $result->code->errmsg;
        if(!$result->code->errmsg){
            $result->error_msg = "未知错误".print_r($result,true);
        }

        logx("weimo_ERROR code:{$code} msg:{$result->error_msg}");
        logx("ERROR weimoErrorTest code:{$code} msg:{$result->error_msg}", 'error');

        return API_RESULT_FAILED;
    }
    if(isset($result->is_success) && !$result->is_success){
        logx("weimo_ERROR weimo_sync_logistics faild msg:{$result->error_message}");
        logx("ERROR weimoErrorTest weimo_sync_logistics faild  msg:{$result->error_message}", 'error');

        return API_RESULT_FAILED;
    }

    return API_RESULT_OK;
}

//卷皮网
function jpwErrorTest(&$result, &$db, $shopId) {
    if (empty($result) || !is_object($result)) {
        $errstr = print_r($result, true);
        logx("JpwInvalid_Response: $errstr");
        logx("ERROR jpwErrorTest $errstr");

        $result = (object)array('code' => -1, 'error_msg' => '卷皮服务器失败');
        return API_RESULT_RETRY;
    }

    if (isset($result->status) && $result->status == 0 && $result->info <> 20000 && $result->info <> 10007) {
        if ($result->info == 10011 || $result->info == 10012 || $result->info == 10013 || $result->info == 10014) {
            markShopAuthExpired($db, $shopId);
        }
        switch ($result->info) {
            case 10001:
                $msg = '参数丢失';
                break;
            case 10002:
                $msg = '非法请求，不在的接口';
                break;
            case 10003:
                $msg = '不存在的SECRET或SECRET不可用';
                break;
            case 10004:
                $msg = 'TOKEN无效，过期或者不存在';
                break;
            case 10005:
                $msg = 'ERP权限不正确';
                break;
            case 10006:
                $msg = 'ERP帐号被禁用';
                break;
            case 10008:
                $msg = '不存在的订单';
                break;
            case 10009:
                $msg = '发货失败';
                break;
            case 10010:
                $msg = 'ERP帐号异常';
                break;
            case 10011:
                $msg = '商家状态不可用';
                break;
            case 10012:
                $msg = '不存在此商家';
                break;
            case 10013:
                $msg = '商家密钥错误';
                break;
            case 10014:
                $msg = '商家密钥过期';
                break;
            case 10015:
                $msg = '订单已发货';
                break;
            case 10016:
                $msg = '订单商品全部售后中';
                break;
            case 10017:
                $msg = '发货号和快递公司不匹配';
                break;
            case 10018:
                $msg = '仓库中的商品不能发货';
                break;
            case 10019:
                $msg = '库存设置保护时间(5 分钟)';
                break;
            case 10020:
                $msg = '减少库存数大于实时库存数';
                break;
            case 10021:
                $msg = '服务化库存查询错误';
                break;
            case 10022:
                $msg = '服务化库存设置错误';
                break;
            case 10023:
                $msg = '入库商品不能修改库存';
                break;
            case 10024:
                $msg = '用户与商品所属商家不一致';
                break;
            case 10025:
                $msg = '商品未上架, 不可修改库存';
                break;
            case 10026:
                $msg = '入库商品不可查询';
                break;
            case 10027:
                $msg = '物流公司不存在';
                break;
            case 10028:
                $msg = '该商品数据错误，无法设置库存';
                break;
            case 10029:
                $msg = '不支持该订单类型查询/发货';
                break;
            case 10030:
                $msg = '等待上架商品sku修改后总库存不能低于10';
                break;
            case 10031:
                $msg = '上架商品sku修改后库存必须大于销量+50';
                break;
            case 10032:
                $msg = '不支持该订单状态修改收货地址';
                break;
            case 10033:
                $msg = '改商品不能被erp同步库存';
                break;
            case 10034:
                $msg = '发货失败（当前订单不在可发货状态）';
                break;
            case 10035:
                $msg = '发货失败（物流单号已被使用）';
                break;
            case 10036:
                $msg = '发货失败（物流单号已有物流信息)';
                break;
            case 10037:
                $msg = '试用商品   库存不可以修改';
                break;
            case 10040:
                $msg = 'TOKEN失效';
                break;
            case 10041:
                $msg = '签名错误';
                break;
            case 10042:
                $msg = 'TOKEN权限不足';
                break;
            case 50000:
                $msg = '服务器错误';
                break;
            case 50001:
                $msg = 'scope为空';
                break;
            default:
                $msg = '未知错误' . print_r($result, true);
                break;
        }

        $result->error_msg = $msg;

        logx("JPW_ERROR {$result->info} $msg");
        logx("ERROR jpwErrorTest {$result->info} $msg");
        return API_RESULT_FAILED;
    }

    return API_RESULT_OK;
}
function ccjptErrorTest(&$result, &$db, $shopId)
{
    if (empty($result)) {
        $errstr = print_r($result, true);
        logx("ccjptInvalid_Response:$errstr");
        logx("ERROR ccjptErrorTest $errstr", 'error');
        $result = (object)array('code' => -1, 'msg' => '楚楚街拼团服务器失败');
        return API_RESULT_RETRY;
    }
    if (isset($result->errno) && $result->errno <> 200) {
        $result->code = $result->errno;
        logx("ccjpt_ERROR code:{$result->errno} msg:{$result->msg}");
        logx("ERROR ccjptErrorTest msg:{$result->msg}", 'error');
        return API_RESULT_FAILED;
    }
    return API_RESULT_OK;
}


//返利网
function flwErrorTest(&$result, &$db, $shopId){
    if (empty($result) || !is_object($result)) {
        $errstr = print_r($result, true);
        logx("flwInvalid_Response: $errstr");
        logx("ERROR flwErrorTest $errstr", 'error');

        $result = (object)array('code' => -1, 'error_msg' => '返利网服务器失败');
        return API_RESULT_RETRY;
    }
    if (isset($result->success) && @$result->success == true) {
        return API_RESULT_OK;
    }
    if(isset($result->responseCode) || isset($result->responseDesc))
        $result->error_msg = 'error_msg:'. $result->responseDesc .'  '.'error_code:' . $result->responseCode;

    logx("FLW_ERROR {$result->error_msg}");
    logx("ERROR flwErrorTest  {$result->error_msg}");
    return API_RESULT_FAILED;
}

//飞牛网
function fnErrorTest(&$result, &$db, $shopId) {
    if (empty($result) || !is_object($result)) {
        $errstr = print_r($result, true);
        logx("FnInvalid_Response: $errstr");
        logx("ERROR FnErrorTest $errstr");

        $result = (object)array('code' => -1, 'error_msg' => '飞牛街服务器失败');
        return API_RESULT_RETRY;
    }
    

    if (isset($result->code) && $result->code <> 100) {
        $msg = $result->msg;
        $result->error_msg = $msg;
        
        if ($result->code == 202) { //授权失败/或者授权已经过期

            markShopAuthExpired($db, $shopId);
        }

        logx("FN_ERROR {$result->code} $msg");
        logx("ERROR fnErrorTest {$result->code} $msg");

        return API_RESULT_FAILED;
    }

    return API_RESULT_OK;
}

//顺丰嘿客
function sfErrorTest(&$result, &$db, $shopId) {
    if (empty($result) || !is_object($result)) {
        $errstr = print_r($result, true);
        logx("SfInvalid_Response: $errstr");
        logx("ERROR sfErrorTest $errstr");

        $result = (object)array('code' => -1, 'error_msg' => '嘿客服务器失败');
        return API_RESULT_RETRY;
    }

    if (isset($result->head) && $result->head->code <> 'EX_CODE_OPENAPI_0200') {
        $msg = $result->head->message;
        $result->error_msg = $msg;
        logx("SF_ERROR  $msg");
        logx("ERROR sfErrorTest  $msg");
        return API_RESULT_FAILED;

    }

    if (!empty($result->body->error_response)) {
        $msg = $result->body->error_response->message;
        $transType = $result->head->transType;
        $result->error_msg = $msg;
        logx("SF_ERROR $transType $msg");
        logx("ERROR sfErrorTest  $msg");
        return API_RESULT_FAILED;

    }

    return API_RESULT_OK;
}

//微店
function vdianErrorTest(&$result, &$db, $shopId) {
    if (empty($result) || !is_object($result)) {
        $errstr = print_r($result, true);
        logx("Invalid_Response: $errstr");
        logx("ERROR ErrorTest $errstr");

        $result = (object)array('code' => -1, 'error_msg' => '微店服务器失败');
        return API_RESULT_RETRY;
    }

    if (isset($result->status->status_code) && $result->status->status_code <> 0) {
        $msg = $result->status->status_reason;
        $result->error_msg = $msg;

        logx("vdian_ERROR {$result->status->status_code} $msg");
        logx("ERROR vdianErrorTest {$result->status->status_code} $msg");
        return API_RESULT_FAILED;
    }
}

//百度mall
function bdmallErrorTest(&$result, &$db, $shopId) {
    if (empty($result) || !is_object($result)) {
        $errstr = print_r($result, true);
        logx("Invalid_Response: $errstr");
        logx("ERROR ErrorTest $errstr");

        $result = (object)array('code' => -1, 'error_msg' => '百度mall服务器失败');
        return API_RESULT_RETRY;
    }

    if (isset($result->body)) {
        $result = $result->body->data[0];
    } else {
        $msg = $result->header->failures[0]->message;
        $code = $result->header->failures[0]->code;
        $result->error_msg = $msg;

        logx("bdmall_ERROR $code $msg");
        logx("ERROR bdmallErrorTest $code $msg");
        return API_RESULT_FAILED;
    }

    if (isset($result->code) && $result->code <> 200 && isset($result->msg)) {
        $msg = $result->msg;
        $result->error_msg = $msg;

        logx("bdmall_ERROR {$result->code} $msg");
        logx("ERROR bdmallErrorTest {$result->code} $msg");
        return API_RESULT_FAILED;
    }
    return API_RESULT_OK;
}

//建行善融商城
function ccbErrorTest(&$result, &$db, $shopId)
{
    if(empty($result) || !is_array($result))
    {
        $errstr = print_r($result, true);
        logx("Invalid_Response: $errstr");

        $result = array('code'=>-1, 'error_msg' => '善融商城服务器失败');
        return API_RESULT_RETRY;
    }

    if(isset($result[0]) && $result[0] == '您还未被授权为对接商户！')
    {
        markShopAuthExpired($db, $shopId);
        $result = array('code'=>-1, 'error_msg' => '该店铺授权已失效！');
        return API_RESULT_FAILED;
    }

    if(isset($result['head']['ret_code']) && $result['head']['ret_code'] != '000000' && isset($result['head']['ret_msg']))
    {
        $msg = $result['head']['ret_msg'];
        $result['error_msg'] = $msg;

        logx("ERROR ccbErrorTest {$result['head']['ret_code']} $msg");
        return API_RESULT_FAILED;
    }

    return API_RESULT_OK;
}


//京东到家
function jddjErrorTest(&$result, &$db, $shopId) {
    if (empty($result) || !is_object($result)) {
        $errstr = print_r($result, true);
        logx("Invalid_Response: $errstr");
        logx("ERROR ErrorTest $errstr");

        $result = (object)array('code' => -1, 'error_msg' => '京东到家服务器失败');
        return API_RESULT_RETRY;
    }

    if (isset($result->code) && $result->code <> 0 && isset($result->msg)) {
        $msg = $result->msg;
        $result->error_msg = $msg;

        logx("jddj_ERROR {$result->code} $msg");
        logx("ERROR jddjErrorTest {$result->code} $msg");
        return API_RESULT_FAILED;
    }
}
//拼多多
function pddErrorTest(&$result, &$db, $shopid)
{
    if(empty($result) || !is_object($result))
    {
        $errorstr = print_r($result,true);
        logx("pdd_invalid_Request:",$errorstr);
        logx("ERROR pddErrorTest $errorstr",'error');

        $result = array('code'=>-1,'error_msg'=>'拼多多服务器失败');
        return API_RESULT_RETRY;
    }

    if(isset($result->error_code) && $result->error_code <> 1)
    {
        $error_code = $result->error_code;
        $error_msg = $result->error_msg;
        //$result->error_msg = $error_msg;
        if($error_msg == '授权已被取消'){
            markShopAuthExpired($db, $shopid);
        }
        logx("ERROR pddErrorTest error_code:{$error_code} error_msg:{$error_msg}");
        return API_RESULT_FAILED;
    }
    return API_RESULT_OK;
}

//速卖通
function smtbabaErrorTest(&$result, &$db, $shopId)
{
    if(empty($result) || !is_object($result))
    {
        $errstr = print_r($result, true);
        logx("smtInvalid_Response: $errstr");
        logx("ERROR smtErrorTest $errstr");

        $result = (object)array('code'=>-1, 'error_msg' => '速卖通服务器失败');
        return API_RESULT_RETRY;
    }


    if(isset($result->error_code))
    {
        $result->error_msg = 'error_msg:'. $result->error_message .'  '.'error_code:' . $result->error_code;
        logx("SMT_ERROR {$result->error_msg}");
        logx("ERROR smtErrorTest  {$result->error_msg}");
        return API_RESULT_FAILED;

    }

    return API_RESULT_OK;
}

//人人店
function rrdErrorTest(&$result, &$db, $shopId)
{
    if(empty($result))
    {
        $errstr = print_r($result, true);
        logx("rrdInvalid_Response: $errstr");
        logx("ERROR rrdErrorTest $errstr", 'error');

        $result = (object)array('code'=>-1, 'error_msg' => '微吧人人店服务器失败');
        return API_RESULT_RETRY;
    }
    
    if(isset($result->errCode) && $result->errCode <> 0)
    {
        $code = $result->errCode;
        $msg = "";
        if (30007 == $code || 30009 == $code || 30017 == $code) {
            //授权失效
            markShopAuthExpired($db, $shopId);
        }
        switch ($code) {
            case '10001': $msg = "缺少appid,传入正确的appid"; break;
            case '10002': $msg = "缺少response_type或值不正确,传入值为code的response_type"; break;
            case '10003': $msg = "缺少回调地址,传入回调地址"; break;
            case '10004': $msg = "应用不存在,传入正确的appid"; break;
            case '10005': $msg = "应用被停用,检查应用状态"; break;
            case '10006': $msg = "回调地址不合法,检查回调地址是否正确"; break;
            case '10007': $msg = "商户信息错误,检测商户用户名是否正确"; break;
            case '10008': $msg = "商户登陆失败,检查商户用户名和密码是否正确"; break;
            case '10009': $msg = "Code生成失败,系统繁忙，稍后在试"; break;
            case '20001': $msg = "缺少appid,传入正确的appid"; break;
            case '20002': $msg = "缺少secret,传入正确的secret"; break;
            case '20003': $msg = "缺少grant_type或传入的值不对,传入正确的grant_type"; break;
            case '20004': $msg = "刷新token时缺少refresh_token,传入正确的refresh_token"; break;
            case '20005': $msg = "缺少回调地址,传入回调地址"; break;
            case '20006': $msg = "缺少code,传入上一步获取到得code"; break;
            case '20007': $msg = "应用不存在,传入正确的appid"; break;
            case '20008': $msg = "传入的secret错误,传入对应的secret"; break;
            case '20009': $msg = "传入的回调地址错误,传入正确的回调地址"; break;
            case '20010': $msg = "传入的Code错误或已失效 传入正确的code"; break;
            case '20011': $msg = "刷新token时传入的refresh_token错误    传入正确的refresh_token"; break;
            case '20012': $msg = "当前应用不能刷新token 检查应用"; break;
            case '20013': $msg = "Token生成失败 系统繁忙，稍后在试"; break;
            case '30001': $msg = "缺少appid   传入正确的appid"; break;
            case '30002': $msg = "应用不存在 传入正确的appid"; break;
            case '30003': $msg = "应用被停用 检查应用状态"; break;
            case '30004': $msg = "接口为空  传入接口名称"; break;
            case '30005': $msg = "缺少时间参数    传入时间参数"; break;
            case '30006': $msg = "传入的时间误差超过10分钟 检查时间参数"; break;
            case '30007': $msg = "缺少access_token    传入access_token"; break;
            case '30008': $msg = "无效的access_token   传入正确的access_token"; break;
            case '30009': $msg = "Appid与access_token不匹配 传入正确的appid和access_token"; break;
            case '30010': $msg = "缺少sign    传入正确的sign"; break;
            case '30011': $msg = "签名错误  检查并传入正确的签名"; break;
            case '30012': $msg = "错误的接口 传入正确的接口"; break;
            case '30013':
            case '30014': $msg = "无法载入接口文件  看接口名称是否正确，均无错误请联系我们"; break;
            case '30015': $msg = "接口验证错误。   请检测参数和请求的网关，均无错误请联系我们"; break;
            case '30016': $msg = "接口无权限 如需开通权限，请联系我们"; break;
            case '5011001': $msg = "缺少order_sn订单号   传入order_sn"; break;
            case '5011002': $msg = "物流公司代码错误    根据快递公司接口获取快递公司数据传入正确的快递公司代码"; break;
            case '5011003': $msg = "子订单sub_order_info非json格式    检查sub_order_info数据格式"; break;
            case '5011004': $msg = "无效order_sn订单号   传入有效的订单号"; break;
            case '5011005': $msg = "订单状态不正确 该订单不在发货状态不可发货"; break;
            case '5011006': $msg = "物流信息不完整 请传入完整的物流信息logis_code,logis_code,logis_name参数"; break;
            case '5011007': $msg = "发货商品数量不合法   发货数量可以小于1,检测子订单quantity值"; break;
            case '5011008': $msg = "子订单商品不存在    检测子订单oid的值"; break;
            case '5011009': $msg = "发货数量超出最大可发数量    准确传入发货数量，检测子订单quantity值"; break;
            case '5011010': $msg = "当前订单下有子订单处于维权状态，不可发货"; break;
            case '5011011': $msg = "快递公司代码不能为空"; break;
            case '5011013': $msg = "判断是否需要物流装"; break;
            case '5011020': $msg = "当前代付订单异常，不能发货！"; break;
            case '5011021': $msg = "系统繁忙 请稍后再试,请联系人人店"; break;
            case '5011200': $msg = "此店铺不支持该快递公司"; break;
            case '5012001': $msg = "缺少order_sn订单号   传入order_sn参数"; break;
            case '5012002': $msg = "无效order_sn订单号,找不到该订单    传入有效的订单号"; break;
            case '5022001': $msg = "oid 为空"; break;
            case '5022002': $msg = "自有库存为空 不能发货"; break;
            case '6012001': $msg = "缺少goods_id参数    传入正确的goods_id参数"; break;
            case '6012002': $msg = "无此goods_id或者商品不属于改商家    传入正确的商品id"; break;
            case '6012099': $msg = "添加失败，请联系我们  请联系我们"; break;
            case '6012100': $msg = "缺少goods_id商品id参数    传入正确的商品goods_id商品id"; break;
            case '6012101': $msg = "缺少base_csale商品基础销量参数    传入正确的商品基础销量参数"; break;
            case '6012221': $msg = "商超总店铺不适用此接口";   break;
            case '6013010': $msg = "缺少title参数   传入正确的title参数"; break;
            case '6013020': $msg = "缺少goods_cat_id参数    传入正确的goods_cat_id参数"; break;
            case '6013021': $msg = "goods_cat_id参数格式错误  传入正确的goods_cat_id参数"; break;
            case '6013030': $msg = "缺少img主图参数   传入正确的img参数"; break;
            case '6013040': $msg = "缺少price价格参数 传入正确的price价格参数"; break;
            case '6013041': $msg = "price价格参数错误 传入正确的price价格参数"; break;
            case '6013050': $msg = "缺少stock库存参数 传入正确的stock参数"; break;
            case '6013051': $msg = "Stock参数错误   传入正确的stock参数"; break;
            case '6013060': $msg = "collect_fields参数不合法 请传入正确的collect_fields参数"; break;
            case '6014000': $msg = "缺少手机号   传入手机号码"; break;
            case '6014001': $msg = "手机号码格式不正确   传入正确的手机号码"; break;
            case '6014002': $msg = "该会员尚未注册"; break;
            case '6014003': $msg = "会员不在此店铺下"; break;
            case '6015001': $msg = "缺少ids商品id参数 传入正确的ids参数，单条直接商品id，批量逗号分割如1，2，3"; break;
            case '6016001': $msg = "缺少goods_id商品id参数";break;
            case '6016002': $msg = "缺少products库存数据或者数据格式不对"; break;
            case '6016003': $msg = "无效goods_id商品id或无商品编辑权限"; break;
            case '6016004': $msg = "修改库存不得小于锁定库存";break;
            case '6016005': $msg = "缺少商品库存skuid检测skus参数"; break;
            case '6016006': $msg = "缺少商品库存id检测skus product_sn或者skuid 错误"; break;
            case '6016099': $msg = "上下架失败，请联系人人店"; break;
            case '6016101': $msg = "缺少goods_id商品id参数    传入正确的goods_id"; break;
            case '6016102': $msg = "无效商品信息  传入正确的goods_id"; break;
            case '6017001': $msg = "缺少goods_id商品id参数"; break;
            case '6017002': $msg = "缺少商品基础库存参数"; break;
            case '6017003': $msg = "基础库存参数格式错误,基础库存参数参数只能为0或者大于0的数字"; break;
            case '6017005': $msg = "找不到商品，无此商品权限或者商品不存在"; break;
            case '6017006': $msg = "该商品多规格商品不可以用本接口"; break;
            case '6017099': $msg = "上下架失败，请联系人人店"; break;
            case '6017100': $msg = "只有厂家可以编辑，代理商无权限"; break;
            case '6017101': $msg = "缺少goods_cat_id商品分类id    传入正确的商品分类id"; break;
            case '6017201': $msg = "缺少name缺少属性名称    传入name属性名称"; break;
            case '6017299': $msg = "系统错误添加失败    检查自己的参数，如有疑惑联系技术"; break;
            case '6017200': $msg = "缺少id 属性id参数 传入正确的id参数"; break;
            case '6017300': $msg = "缺少id 属性id参数 传入正确的id参数"; break;
            case '6017399': $msg = "系统错误删除失败    检查自己的参数，如有疑惑联系技术"; break;
            case '6017501': $msg = "缺少prop_id 属性id  传入正确的prop_id 属性id "; break;
            case '6017502': $msg = "缺少 name 属性名称    传入正确的name 属性名称"; break;
            case '6017503': $msg = "无效prop_id 属性id查找不到对于的属性 传入正确的prop_id 属性id查找不到对于的属性"; break;
            case '6017599': $msg = "系统错误添加失败    检查自己的参数，如有疑惑联系技术"; break;
            case '6017601': $msg = "缺少id 属性值id  传入正确的id 属性值id "; break;
            case '6017602': $msg = "无效prop_id 属性id查找不到对于的属性 传入正确的prop_id 属性id查找不到对于的属性"; break;
            case '6017603': $msg = "缺少 name 属性名称    传入正确的name 属性名称"; break;
            case '6017604': $msg = "无效prop_id 属性id查找不到对于的属性 传入正确的prop_id 属性id查找不到对于的属性"; break;
            case '6017699': $msg = "系统错误编辑失败    检查自己的参数，如有疑惑联系技术"; break;
            case '6017701': $msg = "缺少id 属性值id  传入正确的id 属性值id "; break;
            case '6017702': $msg = "缺少prop_id 属性值id 传入正确的prop_id 属性值id"; break;
            case '6017703': $msg = "无效prop_id 属性id查找不到对于的属性 传入正确的prop_id 属性id查找不到对于的属性"; break;
            case '6017799': $msg = "系统错误编辑失败    检查自己的参数，如有疑惑联系技术"; break;
            case '6018000': $msg = "手机号码格式不正确   传入正确的手机号码"; break;
            case '6018001': $msg = "至少选择一个查询条件 guider_id 或 mobile   传入一个查询条件"; break;
            case '6018002': $msg = "查询的推客会员不存在"; break;
            case '6018003': $msg = "输入的筛选状态不正确  请输入正确的筛选状态"; break;
            case '6080001': $msg = "缺少images图片内容参数  传入正确的images图片内容"; break;
            case '6086002': $msg = "images图片内容据格式不对 传入正确的images目前只支持图片png、jpg、gif"; break;
            case '6086003': $msg = "images图片内容超过最大限制    目前最大允许上传图片2M"; break;
            case '6086004': $msg = "上传图片失败，系统错误 请连续我们"; break;
            default: $msg = "未知错误！！！"; break;
        }
        $result->error_msg = $msg;
        logx("rrd_ERROR code:{$code} msg:{$msg}");
        logx("ERROR rrdErrorTest msg:{$msg}", '$code');

        return API_RESULT_FAILED;
    }
    return API_RESULT_OK;
}

//小红书
function xhsErrorTest(&$result, &$db, $shopId)
{
    if (empty($result)) {
        $errstr = print_r($result, true);
        logx("ERROR xhsErrorTest $errstr", 'error');
        $result = (object)array('code' => -1, 'error_msg' => '小红书服务器失败');
        return API_RESULT_RETRY;
    }
    if ($result->error_code != NULL) {
        logx("ERROR xhsErrorTest msg:{$result->error_msg}");
        return API_RESULT_FAILED;
    }
    return API_RESULT_OK;
}

?>
