<?php

require_once(TOP_SDK_DIR . '/top/Logger.php');
require_once(TOP_SDK_DIR . '/top/RequestCheckUtil.php');
require_once(TOP_SDK_DIR . '/top/TopClient.php');
/*require_once(TOP_SDK_DIR . 'wm/wmClient.php');*/
require_once(ROOT_DIR . '/Common/address.php');
require_once(TOP_SDK_DIR . '/top/security_sdk/top/top_client.php');

function ifEmpty($x, $d = 0) {
    if (empty($x)) return $d;
    return $x;
}

$g_disable_alarm = false;
function resetAlarm($secs = 120) {
    global $g_os_win, $g_disable_alarm;
    if ($g_disable_alarm) return;
    $res=php_sapi_name();
    $g_os_mode = (strtolower(substr($res,0,3)) === 'apa');
    if (!$g_os_mode){
        pcntl_alarm($secs);
    }
}

function dateValue($d) {
    if (empty($d) || '0001-01-01 00:00:00' == $d || '1970-01-01 00:00:00' == $d) {
        return '1000-01-01 00:00:00';
    }
    return $d;
}

function enumAllMerchant($msg) {

      global $g_debug_sid, $current_cluster, $current_front_host, $g_eshop_router_url;
		
	// if debug, no need to cache	
	 if (empty($g_debug_sid)){
		static $cache = '';
		if (empty($cache)){
			$cache = new Memcached();
			$cache->addServer('10.132.180.236', 30001);
		} 
		$merchant_list = $cache->get($current_front_host);
		
	 }
	 
     $url = $g_eshop_router_url . "?action=merchant_list&host=" . urlencode($current_front_host);

     if (!empty($g_debug_sid)) {
		 $url .= '&sid=' . urlencode($g_debug_sid);
		 $str = file_get_contents($url);
	 } else if (!empty($merchant_list)){
		 $str = $merchant_list;
	 } else {
		$str = file_get_contents($url);
		$cache->set($current_front_host, $str);
	 }
     

     $result = json_decode($str);
     if ($result->status != 0) {
         if (empty($g_debug_sid)){
			 $cache->delete($current_front_host);
		 }
         logx($msg . "  enumAllMerchant fail!");
         return false;
     }
     $has_task = false;
     foreach ($result->info as $sid) {
         pushTask($msg, $sid, 0, 1024, 1200, 300);
         $has_task = true;
     }

    return $has_task;
}

function getAlipaySecret(&$appkey, &$private_appsecret,&$public_appsecret)
{
    global $alipay_app_config;
    $appkey    = $alipay_app_config["app_id"];
    $private_appsecret = decodeDbPwd($alipay_app_config["private_app_secret"], $alipay_app_config["app_id"]);
    $public_appsecret = decodeDbPwd($alipay_app_config["public_app_secret"], $alipay_app_config["app_id"]);

}

function getAppSecret(&$shop, &$appkey, &$appsecret) {
    switch ($shop->platform_id) {
        case 1: //淘宝天猫
        case 2: //淘宝分销
        {
            if($shop->auth_time > '2017-01-10 16:00:00'){
                global $ekb_top_app_config;
                $appkey    = "test";//$ekb_top_app_config['app_key'];
                $appsecret = "test";//decodeDbPwd($ekb_top_app_config['app_secret'], $ekb_top_app_config["app_key"]);
                break;
            }else{
                global $top_app_config;
                $appkey    = "test";//$top_app_config["app_key"];
                $appsecret = "test";//decodeDbPwd($top_app_config["app_secret"], $top_app_config["app_key"]);
                break;
            }
        }
        case 3: //京东
        {
            if($shop->auth_time > '2017-01-17 16:00:00'){
                global $ekb_jos_app_config;
                $appkey    = $ekb_jos_app_config['app_key'];
                $appsecret = decodeDbPwd($ekb_jos_app_config['app_secret'], $ekb_jos_app_config["app_key"]);
                break;
            }else{
                global $jos_app_config;
                $appkey    = $jos_app_config['app_key'];
                $appsecret = decodeDbPwd($jos_app_config['app_secret'], $jos_app_config["app_key"]);
                break;
            }
        }
        case 5: //亚马逊
        {
            $appkey = $shop->key;
            $appsecret = '';
            break;
        }
        case 6: //一号店
        {
            global $yhd_app_config;
            $appkey = $yhd_app_config['app_key'];
            $appsecret =  decodeDbPwd($yhd_app_config['app_secret'], $yhd_app_config["app_key"]);
            break;
        }
        case 7: //当当
        {
            global $dangdang_app_config;
            $appkey = $dangdang_app_config['app_key'];
            $appsecret = decodeDbPwd($dangdang_app_config['app_secret'], $dangdang_app_config["app_key"]);
            break;
        }
        case 8: //国美
        {
            $appkey    = $shop->key;
            $appsecret = $shop->secret;
            break;
        }
        case 9://阿里巴巴
        {
            if($shop->auth_time > '2018-01-10 12:00:00'){
                global $new_alibaba_app_config;
                $appkey    = $new_alibaba_app_config['app_key'];
                $appsecret = decodeDbPwd($new_alibaba_app_config['app_secret'], $new_alibaba_app_config["app_key"]);
                break;
            }else{
                global $alibaba_app_config;
                $appkey = $alibaba_app_config['app_key'];
                $appsecret =  decodeDbPwd($alibaba_app_config['app_secret'], $alibaba_app_config["app_key"]);
                break;
            }
        }
        case 13: //sos
        {
            if($shop->auth_time > '2018-03-05 08:00:00'){
                global $new_sos_app_config;
                $appkey    = $new_sos_app_config['app_key'];
                $appsecret = decodeDbPwd($new_sos_app_config['app_secret'], $new_sos_app_config["app_key"]);
                break;
            }else{
            global $sos_app_config;
            $appkey = $sos_app_config['app_key'];
            $appsecret = decodeDbPwd($sos_app_config['app_secret'],$sos_app_config['app_key']);
            break;
        }
        }
        case 14: //唯品会
        {
            global $vip_app_config;
            $appkey = $vip_app_config['app_key'];
            $appsecret = decodeDbPwd($vip_app_config['app_secret'],$vip_app_config['app_key']);
            break;
        }
        case 17: //有赞
        {
            global $kdt_app_config;
            $appkey = $kdt_app_config['app_key'];
            $appsecret = decodeDbPwd($kdt_app_config['app_secret'],$kdt_app_config['app_key']);
            break;
        }
        case 20: //美丽说
        {
            global $mls_app_config;
            $appkey = $mls_app_config['app_key'];
            $appsecret = decodeDbPwd($mls_app_config['app_secret'],$mls_app_config['app_key']);
            break;
        }
        case 22: //贝贝网
        {
            global $bbw_app_config;
            $appkey = $bbw_app_config['app_key'];
            $appsecret = decodeDbPwd($bbw_app_config['app_secret'],$bbw_app_config['app_key']);
            break;

        }
        case 24://折800
        {
            global $zhe_app_config;
            if(empty($shop->key))
            {
                $appkey = $zhe_app_config['app_key'];
                $appsecret = decodeDbPwd($zhe_app_config['app_secret'], $zhe_app_config["app_key"]);
            }
            else
            {
                $appkey = $shop->key;
                $appsecret = $shop->secret;
            }
            break;
        }
        case 25: //融e购
        case 27: //楚楚街
        {
            $appkey    = $shop->key;
            $appsecret = $shop->secret;
            break;
        }
        case 28: //weimo
        {
            if(empty($shop->session))
            {
                $appkey = $shop->key;
                $appsecret = $shop->secret;
            }
            else
            {
                global $wm_app_config;
                $appkey = $wm_app_config['app_key'];
                $appsecret = decodeDbPwd($wm_app_config['app_secret'], $wm_app_config["app_key"]);
            }
            break;
        }
        case 29://卷皮网
        {
            global $jpw_app_config;
            $appkey = $jpw_app_config['app_key'];
            $appsecret = decodeDbPwd($jpw_app_config['app_secret'],$jpw_app_config['app_key']);
            break;
        }
        case 31://飞牛
        {
            global $fn_app_config;
            $appkey = $fn_app_config['app_key'];
            $appsecret = decodeDbPwd($fn_app_config['app_secret'],$fn_app_config['app_key']);
            break;
        }
        case 33: //拼多多
        {
            if(!empty($shop->session)){
                global $pdd_app_config;
                $appkey = $pdd_app_config['app_key'];
                $appsecret = decodeDbPwd($pdd_app_config['app_secret'],$pdd_app_config['app_key']);
            }else{
                $appkey = $shop->key;
                $appsecret = $shop->secret;
            }
            break;
        }
        case 34: //蜜芽宝贝
        case 36: //善融商城
        case 56: //小红书
        case 60: //返利网
        {
            $appkey    = $shop->key;
            $appsecret = $shop->secret;
            break;
        }
        case 37: //速卖通
        {
            global $smt_app_config;

            $appkey = $smt_app_config['app_key'];
            $appsecret = decodeDbPwd($smt_app_config['app_secret'],$smt_app_config['app_key']);
            break;
        }
        case 47: //人人店
        {
            global $rrd_app_config;
            $appkey = $rrd_app_config['app_key'];
            $appsecret = decodeDbPwd($rrd_app_config['app_secret'],$rrd_app_config['app_key']);
            break;
        }
        case 50: //考拉
        {
            global $kl_app_config;
            $appkey = $kl_app_config['app_key'];
            $appsecret = decodeDbPwd($kl_app_config['app_secret'],$kl_app_config['app_key']);
            break;
        }
        case 53: //楚楚街拼团
        {
            $appkey    = $shop->key;
            $appsecret = $shop->secret;
            break;
        }

        default: {
            return false;
        }
    }
    return true;
}

/*
 * 支付宝授权校验
 * */
function checkAlipayAuth($row)
{
    global $g_debug_shopid;

    if (isset($g_debug_shopid) && $g_debug_shopid > 0 && $g_debug_shopid == (int)$row["shop_id"])
    {
        return true;
    }
    if(empty($row['alipay_app_key']) || empty($row['alipay_auth_app_id']))
    {
        return false;
    }
    return true;

}

function checkAppKey(&$row) {

    global $g_debug_shopid;

    if (isset($g_debug_shopid) && $g_debug_shopid > 0 && $g_debug_shopid != (int)$row["shop_id"]) {
        return false;
    }

    $platformID = (int)$row['platform_id'];
    $secret     = json_decode(@$row['app_key'], true);
    if ($platformID == 127) {
        unset($row['app_key']);
        $row = (object)$row;
        return true;
    }

    switch ($platformID) {
        case 1: //淘宝天猫
        case 2: //淘宝分销
        case 3: //京东
        case 6: //一号店
        case 7: //当当网
        case 9: //阿里巴巴
        case 13://苏宁店
        case 14://唯品会
        case 17: //有赞（口袋通）
        case 22://贝贝网
        case 24://折800
        case 29://卷皮网
        case 31://飞牛网
        case 32://微店
        case 37://速卖通
        case 47://人人店
        case 50://考拉
        {
            if (empty($secret['session'])) {
                return false;
            }

            break;
        }
        case 5:	//亚马逊
        {
            if(empty($secret['session']) || empty($secret['key']))
                return false;
            break;
        }
        case  8: //国美 
		{
            if(empty($secret['secret']))
				return false;
            break;
        }
        case 20: //美丽说
        case 27: //楚楚街
        case 33: //拼多多
        {
            if(empty($secret['session']) && (empty($secret['key']) || empty($secret['secret'])))
            {
                return false;
            }
            break;
        }
        case 25://icbc
        case 36: //建行善融商城
        {
            if(empty($secret['key']) || empty($secret['secret']) )
            {
                return false;
            }
            break;
        }
        case 34: //蜜芽宝贝
        case 28://微盟旺店
        {
            if(empty($secret['session']) && ((empty($secret['key']) || empty($secret['secret']))) )
            {
                return false;
            }
            break;
        }
		case 53: //楚楚街拼团
        case 56: //小红书
        case 60: //返利网
		{
			if(empty($secret['key']) || empty($secret['secret'])){
				return false;
			}
				break;
		}
        default: {
            return false;
        }
    }

    foreach ($secret as $key => $val) {
        $row[ $key ] = $val;
    }

    unset($row['app_key']);

    $row = (object)$row;

    return true;
}

function xmlArrayCount(&$node) {
    if (is_array($node)) return count($node);

    return 1;
}

function refreshAliToken($appkey, $appsecret, $shop) {
    $sid     = $shop->sid;
    $shop_id = intval($shop->shop_id);

    $db = getUserDb($sid);
    if (!$db) {
        logx("getUserDb failed in refreshAliToken!!", $sid . "/default",'error');
        return;
    }

    if (empty($shop->refresh_token)) {
        $db->execute("update cfg_shop set auth_state=2 where shop_id=$shop_id");
        releaseDb($db);
        return;
    }


    $params = array(
        'grant_type'    => 'refresh_token',
        'client_id'     => $appkey,
        'client_secret' => $appsecret,
        'refresh_token' => $shop->refresh_token
    );

    $url = 'https://gw.open.1688.com/openapi/param2/1/system.oauth2/getToken/' . $appkey;

    try {
        $top            = new TopClient();
        $top->format    = 'json';
        $top->appkey    = $appkey;
        $top->secretKey = $appsecret;
        $json           = $top->curl($url, $params);
        $params         = json_decode_safe($json);
    } catch (Exception $e) {
        $err_code = $e->getCode();
        $err_msg  = $e->getMessage();
        logx("$sid refreshAliToke top->curl failed, error code:{$err_code}, message: {$err_msg} ", $sid . "/default",'error');
        if ($err_code == 400) //{"error":"invalid_request","error_description":"wrong refreshToken"}
        {
            require_once(ROOT_DIR . '/Common/api_error.php');
            markShopAuthExpired($db, $shop_id);
        }
        releaseDb($db);
        return;
    }

    $app_key = array('key' => '', 'secret' => '', 'session' => $params->access_token, 'account_id' => $params->memberId);
    $app_key = json_encode($app_key);

    if (!$db->query("update cfg_shop set app_key=%s,auth_time=NOW() where shop_id=%d",
                    $app_key, $shop_id)
    ) {
        logx("$sid {$shop_id} refreshAliToke update access token failed!!", $sid . "/default",'error');
    }


    logx("$sid {$shop_id} refreshAliToken Success, info:" . print_r($params, true), $sid . "/default");

    $session        = $params->access_token;
    $refresh_token  = $shop->refresh_token;
    $res            = $db->query_result("select re_expire_time from cfg_shop where shop_id = {$shop_id} ");
    $re_expire_time = strtotime($res['re_expire_time']);//先转为时间戳
    //刷新之后，检查刷新令牌是否过期，若离过期时间<30天，则执行获取新的刷新令牌，然后再刷新access_token
    if ($re_expire_time - time() < 30 * 3600 * 24) {
        $refresh_params = array(
            'access_token'  => $session,
            'client_id'     => $appkey,
            'client_secret' => $appsecret,
            'refresh_token' => $refresh_token
        );

        $refresh_url = 'https://gw.open.1688.com/openapi/param2/1/system.oauth2/postponeToken/' . $appkey . '/' . http_build_query($refresh_params);
        logx("$refresh_url", $sid . "/default");
        try {
            $top            = new TopClient();
            $top->format    = 'json';
            $top->appkey    = $appkey;
            $top->secretKey = $appsecret;
            $json           = $top->curl($refresh_url);
            $refresh_para   = json_decode($json, TRUE);
            logx("refresh_para:" . print_r($refresh_para, true), $sid . "/default");

            $app_key        = array('key' => '', 'secret' => '', 'session' => $refresh_para['access_token'], 'account_id' => $refresh_para['memberId']);
            $app_key        = json_encode($app_key);
            $re_expire_time = (int)substr($refresh_para['refresh_token_timeout'], 0, 14);


            if (!$db->query("update cfg_shop set refresh_token=%s,re_expire_time=%d,app_key=%s,SessionDate=NOW() where ShopID=%d",
                            $refresh_para['refresh_token'], $re_expire_time, $app_key, $shop_id)
            ) {
                logx("refreshAliToken update  refresh_token failed!!", $sid . "/default",'error');
            }
        } catch (Exception $e) {
            releaseDb($db);
            $err_code = $e->getCode();
            $err_msg  = $e->getMessage();
            logx("$sid refreshAliToken   refresh_token  top->curl failed, error code:{$err_code}, message: {$err_msg} ", $sid . "/default",'error');
        }


    }
    releaseDb($db);
}

function refreshWeimoToken(&$db, &$shop){
    require_once(TOP_SDK_DIR . '/wm/newWmClient.php');

    $sid = $shop->sid;
    $shop_id = intval($shop->shop_id);

    if(empty($shop->refresh_token))
    {
        $result = $db->query_result("select refresh_token from cfg_shop where platform_id = {$shop->platform_id} and shop_id = {$shop_id}");
        $shop->refresh_token = $result['refresh_token'];

        if(empty($shop->refresh_token)){
            $db->execute("update cfg_shop set auth_state=2 where shop_id=$shop_id");
            logx("refreshWeimoToken {$shop_id} refresh_token empty", $sid . "/default");
        }
        return;
    }

    global $wm_app_config;

    $wm = new WmClient();
    $retval = $wm->refreshToken($wm_app_config['app_key'], decodeDbPwd($wm_app_config['app_secret'],$wm_app_config['app_key']), $shop->refresh_token);
    
    if(!$retval || isset($retval->error) || !isset($retval->access_token)){
        logx("refreshWeimoToken {$shop_id} request failed error:" . print_r($retval, true), $sid . "/default");
        return ;
    }
    $shop->session = $retval->access_token;
    $app_key = array('key'=>'', 'secret'=>'', 'session'=>$retval->access_token);
    $app_key = json_encode($app_key);
    $expires_in = time() + $retval->expires_in;
    $re_expire_time = time() + $retval->refresh_token_expires_in;
    
    if(!$db->query("update cfg_shop set app_key=%s,refresh_token=%s,auth_state=1,auth_time=NOW(),expire_time=FROM_UNIXTIME(%d),re_expire_time=FROM_UNIXTIME(%d) where shop_id=%s",
        $app_key, $retval->refresh_token, $expires_in, $re_expire_time, $shop_id))
    {
        logx("refreshWeimoToken {$shop_id} update session failed", $sid . "/default");
    }
    else
    {
        logx("refreshWeimoToken {$shop_id} refresh session success", $sid . "/default");
    }
}

function refreshSosToken($appkey,$appsecret,$shop,&$db){
    $sid     = $shop->sid;
    $shop_id = intval($shop->shop_id);

    if (empty($shop->refresh_token)) {
        $db->execute("update cfg_shop set auth_state=2 where shop_id=$shop_id");
        logx('refreshSosToke fail,empty refresh_token',$sid.'/default','error');
        releaseDb($db);
        return;
    }
    $params = array(
        'client_id' => $appkey,
        'client_secret' => $appsecret,
        'refresh_token' => $shop->refresh_token,
        'grant_type' => 'refresh_token',
        'redirect_uri' => 'http://ekb.wangdian.cn/auth.php'
    );
    $url = 'http://open.suning.com/api/oauth/token';
    try {
        $top            = new TopClient();
        $top->format    = 'json';
        $top->appkey    = $appkey;
        $top->secretKey = $appsecret;
        $json           = $top->curl($url, $params);
        $parameters     = json_decode_safe($json);
    } catch (Exception $e) {
        $err_code = $e->getCode();
        $err_msg  = $e->getMessage();
        logx("refreshSosToke top->curl failed, error code:{$err_code}, message: {$err_msg} ", $sid . "/default",'error');
        return;
    }

    if(empty($parameters->access_token)){
        if($db->query("update cfg_shop set auth_state=2 WHERE shop_id=%d",$shop_id)){
            logx("{$shop_id} refreshSosToken update auth_state failed!!", $sid . "/default",'error');
        }
    }
    $app_key = array('key' => '', 'secret' => '', 'session' => $parameters->access_token);
    $app_key = json_encode($app_key);

    if (!$db->query("update cfg_shop set auth_state=1,app_key=%s,auth_time=NOW(),expire_time=DATE_ADD(NOW(),INTERVAL {$parameters->expires_in} SECOND) where shop_id=%d",
        $app_key, $shop_id)
    ) {
        logx("{$shop_id} refreshSosToken update access token failed!!", $sid . "/default",'error');
    }

    logx("{$shop_id} refreshSosToken Success, info:" . print_r($parameters, true), $sid . "/default");
}

function refreshMlsToken($appkey, $appsecret, $shop)
{
    $sid = $shop->sid;
    $shop_id = intval($shop->shop_id);

    $db = getUserDb($sid);
    if(!$db)
    {
        logx("getUserDb failed in refreshMlsToken!!", $sid.'/default');
        return;
    }

    if(empty($shop->refresh_token))
    {
        $db->execute("update cfg_shop set auth_state=2 where shop_id=$shop_id");
        releaseDb($db);
        return;
    }

    $parameters = array(
        'app_key' => $appkey,
        'app_secret'=>$appsecret,
        'grant_type' => 'refresh_token',
        'refresh_token' => $shop->refresh_token
    );

    $url = 'https://oauth.mogujie.com/token';

    try
    {
        $top = new TopClient();
        $top->format = 'json';
        $top->appkey = $appkey;
        $top->secretKey = $appsecret;
        $json = $top->curl($url, $parameters);
        $parameters = json_decode_safe($json);
    }
    catch (Exception $e)
    {
        releaseDb($db);
        $err_code = $e->getCode();
        $err_msg = $e->getMessage();
        logx("refreshMlsToke top->curl failed, error code:{$err_code}, message: {$err_msg} ", $sid.'/default');
        return;
    }


    $app_key = array('key'=>'', 'secret'=>'', 'session'=>$parameters->access_token);
    $app_key = json_encode($app_key);

    if(!$db->query("update cfg_shop set app_key=%s,refresh_token=%s,auth_time=NOW() where shop_id=%d",
        $app_key,$parameters->refresh_token,$shop_id))
    {
        $db->execute("UPDATE cfg_shop SET auth_state=2 WHERE shop_id=$shop_id AND auth_state=1");
        markShopAuthExpired($db, $shop_id);
        logx("{$shop_id} refreshMlsToken update access token failed!!", $sid.'/default');
    }

    releaseDb($db);

    logx("{$shop_id} refreshMlsToken Success, info:" . print_r($parameters, true), $sid.'/default');

}

function refreshJpwToken($appkey, $appsecret, $shop) {
    require_once(ROOT_DIR . '/Common/api_error.php');
    $sid     = $shop->sid;
    $shop_id = intval($shop->shop_id);
    $db = getUserDb($sid);
    if (!$db) {
        logx("getUserDb failed in refreshJpwToken!!", $sid . "/default",'error');
        return;
    }

    /*if(empty($shop->refresh_token))
    {
        $db->execute("update cfg_shop set auth_state=2 where shop_id=$shop_id");
        releaseDb($db);
        return;
    }*/


    $jpw         = new jpwClient();
    $jpw->secret = $appsecret;
    // $jpw->gwUrl = "http://119.97.143.29:8109/erpapi/authorize";//测试
    $jpw->gwUrl = "http://seller.juanpi.com/erpapi/authorize";
    $params     = array(
        'secret' => $appkey,
        'type'   => 'json',
        'scope'  => 'order_list,order_info,send_goods,get_express,update_goods_inventory,sgoods_list,sgoods_info'
    );
    $retval = $jpw->getToken($params);
    if (API_RESULT_OK != jpwErrorTest($retval, $db, $shop_id)) {
        $error_msg = $retval->error_msg;
        logx("refreshJpwToken $shop_id $error_msg", $sid . "/default");
        return;
    }
    $token          = $retval->data->token;
    $re_expire_time = date('Y-m-d H:i:s', $retval->data->expire);
    if (!$db->query("update cfg_shop set refresh_token=%s,auth_time=NOW() where shop_id=%d",
        $token, $shop_id)
    ) {
        logx("{$shop_id} refreshJpwToken update access token failed!!", $sid . "/default",'error');
    }

    releaseDb($db);

    logx("{$shop_id} refreshJpwToken Success, info:" . print_r($retval, true), $sid . "/default");


}

function refreshVdianToken(&$shop) {
    require_once(ROOT_DIR . '/Common/api_error.php');
    $sid     = $shop->sid;
    $shop_id = intval($shop->shop_id);

    $db = getUserDb($sid);
    if (!$db) {
        logx("getUserDb failed in refreshVdianToken!!", $sid . "/default");
        return;
    }

    $client      = new vdianClient();
    $client->url = $client::API_SERVER_REFRESHTOKEN . '?appkey=' . $shop->key . '&refresh_token=' . $shop->refresh_token
        . '&grant_type=refresh_token';
    $retval      = $client->http();

    if (API_RESULT_OK != vdianErrorTest($retval, $db, $shop_id)) {
        $error_msg = $retval->error_msg;
        if ($retval->status->status_code == 10023)//refreshtoken过期
        {
            markShopAuthExpired($db, $shop_id);//设置授权失效,需要重新授权
        }
        logx("refreshVdianToken vdian->http fail, error_msg:{$error_msg}", $sid . "/default",'error');
        return;
    }

    $appkey = array(
        'key'     => $shop->key,
        'secret'  => $shop->secret,
        'session' => $retval->result->access_token
    );
    $appkey = json_encode($appkey);

    $expire_time = date("Y-m-d H:i:s", time() + $retval->result->expire_in);

    if (!$db->query("update cfg_shop set app_key=%s,expire_time=%s,auth_time=NOW() where shop_id=%d",
                    $appkey, $expire_time, $shop_id)
    ) {
        logx("{$shop_id} refreshVdianToken update access token failed!!" . print_r($retval, true), $sid . "/default",'error');
        releaseDb($db);
        return;
    }

    releaseDb($db);
    logx("{$shop_id} refreshVdianToken Success, info:" . print_r($retval, true), $sid . "/default");

}

function refreshSfToken($appkey, $appsecret, $shop) {
    require_once(TOP_SDK_DIR . '/sf/sfClient.php');

    $sid    = $shop->sid;
    $shopid = $shop->shop_id;

    $db = getUserDb($sid);
    if (!$db) {
        logx("getUserDb failed in refreshSfToken!!", $sid . "/default",'error');
        return;
    }

    $sf            = new sfclient();
    $sf->sf_appid  = $appkey;
    $sf->sf_appkey = $appsecret;
    $sf->type      = 'public';
    $sf->resource  = 'security/access_token';
    if ((int)$shop->account_nick == 1) {
        $sf->url = "https://open-prod.sf-express.com";
    } else {
        $sf->url = "https://open-sbox.sf-express.com";
    }
    $params = array(
        'head' => array(
            'transType' => '301'
        )
    );
    $retval = $sf->execute($params);
    if (API_RESULT_OK != sfErrorTest($retval, $db, $shopid)) {
        $error_msg = $retval->error_msg;
        logx("refreshSfToken $shopid $error_msg", $sid . "/default",'error');
        return;
    }

    $app_key = array('key' => '', 'secret' => '', 'session' => $retval->body->accessToken);
    $app_key = json_encode($app_key);
    logx("update cfg_shop set app_key='" . $app_key . "',refresh_token='" . $retval->body->refreshToken . "' ,auth_time=NOW() where shop_id=$shopid");
    if (!$db->execute("update cfg_shop set app_key='" . $app_key . "',refresh_token='" . $retval->body->refreshToken . "' ,auth_time=NOW() where shop_id=$shopid")) {
        logx("{$shopid} refreshSfToken update access token failed!!", $sid . "/default",'error');
    }


    releaseDb($db);

    logx("{$shopid} refreshSfToken Success, info:" . print_r($retval, true), $sid . "/default");


}

function refreshSmtToken($appkey, $appsecret, $shop)
{
    require_once(TOP_SDK_DIR . '/smt/SmtClient.php');
    $sid = $shop->sid;
    $shop_id = intval($shop->shop_id);

    $db = getUserDb($sid);
    if(!$db)
    {
        logx("getUserDb failed in refreshAliToken!!", $sid);
        return;
    }

    if(empty($shop->refresh_token))
    {
        $db->execute("update cfg_shop set auth_state=2 where shop_id=$shop_id");
        releaseDb($db);
        return;
    }


    $params = array(
        'grant_type' => 'refresh_token',
        'client_id' => $appkey,
        'client_secret' => $appsecret,
        'refresh_token' => $shop->refresh_token
    );

    $url = 'https://gw.api.alibaba.com/openapi/param2/1/system.oauth2/getToken/' . $appkey;

    $retval = smt::sendByPost($url,$params);
    //print_r($retval);
    if($retval->err_code == 400) //{"error":"invalid_request","error_description":"wrong refreshToken"}
    {
        require_once(ROOT_DIR . '/Common/api_error.php');
        markShopAuthExpired($db, $shop_id);
        releaseDb($db);
        return;
    }

    $app_key = array('key'=>'', 'secret'=>'', 'session'=>$retval->access_token);
    $app_key = json_encode($app_key);

    if(!$db->query("update cfg_shop set app_key=%s,auth_time=NOW() where shop_id=%d",
        $app_key, $shop_id))
    {
        logx("{$shop_id} refreshSmtToke update access token failed!!", $sid);
    }



    logx("{$shop_id} refreshSmtToken Success, info:" . print_r($retval, true), $sid);
    /*
    $session = $retval->access_token;
    $refresh_token = $shop->refresh_token;
    $res = $db->query_result ( "select re_expire_time from sys_shop where shop_id = {$shop_id} " );
    $re_expire_time = strtotime($res['re_expire_time']);//先转为时间戳
    //刷新之后，检查刷新令牌是否过期，若离过期时间<30天，则执行获取新的刷新令牌，然后再刷新access_token
    if($re_expire_time - time()<30*3600*24)
    {
        $refresh_params = array(
            'access_token' => $session,
            'client_id' => $appkey,
            'client_secret' => $appsecret,
            'refresh_token' => $refresh_token
        );

        $refresh_url = 'https://gw.api.alibaba.com/openapi/param2/1/system.oauth2/postponeToken/'.$appkey.'/'.http_build_query($refresh_params);
        logx("$refresh_url".print_r($refresh_url,true),$sid);

        $retval = smt::sendByPost($refresh_url,$refresh_params);
        if (!empty($retval->error))
        {
            releaseDb($db);
            $err_code = $e->getCode();
            $err_msg = $e->getMessage();
            logx("refreshSmtToken   refresh_token  top->curl failed, error code:{$err_code}, message: {$err_msg} ", $sid);
        }else
        {
            $app_key = array('key'=>'', 'secret'=>'', 'session'=>$refresh_params['access_token']);
            $app_key = json_encode($app_key);
            $re_expire_time = (int)substr($refresh_params['refresh_token_timeout'], 0, 14);

            if(!$db->query("update sys_shop set refresh_token=%s,re_expire_time=%d,app_key=%s,SessionDate=NOW() where ShopID=%d",
            $refresh_params['refresh_token'], $re_expire_time,$app_key,$shop_id))
            {
                logx("refreshSmtToken update  refresh_token failed!!", $sid);
            }
        }
    }*/
    releaseDb($db);
    return;
}

function refreshYhdToken(&$db, &$shop)
{
    $sid = $shop->sid;
    $shop_id = intval($shop->shop_id);

    if(empty($shop->refresh_token))
    {
        return;
    }

    $yhd_param = array(
        'client_id' => $shop->key,
        'client_secret' => $shop->secret,
        'grant_type' => 'refresh_token',
        'refresh_token' => $shop->refresh_token
    );

    try
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://member.yhd.com/login/refreshToken.do');
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch,CURLOPT_POST,true);
        curl_setopt($ch,CURLOPT_POSTFIELDS, http_build_query($yhd_param));
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        $content = curl_exec($ch);
        curl_close($ch);
        $content = json_decode($content);

    }
    catch (Exception $e)
    {
        $err_code = $e->getCode();
        $err_msg = $e->getMessage();
        logx("refreshYhdToke top->curl failed, error code:{$err_code}, message: {$err_msg} ", $sid . "/default",'error');
        return;
    }
    logx("yhd refreshtoken :".print_r($content,true),$sid);
    $app_key = array('key'=>'', 'secret'=>'', 'session'=>$content->accessToken);
    $app_key = json_encode($app_key);
    $expire_in = (int)substr($content->expiresIn,0,-3);
    $re_expire_time = $content->refreshExpiresIn/1000;

    if(!$db->query("update cfg_shop set app_key=%s,refresh_token=%s,auth_state=1,auth_time=NOW(),expire_time=FROM_UNIXTIME(%d),re_expire_time=FROM_UNIXTIME(%d) where shop_id=%d",
        $app_key, $content->refreshToken, $expire_in, $re_expire_time, $shop_id))
    {
        logx("{$shop_id} refreshYhdToken update access token failed!!", $sid . "/default",'error');
    }
    else
    {
        logx("{$shop_id} refreshYhdToken Success", $sid);
    }
}
function refreshRrdToken($appkey, $appsecret, $shop)
{
    $sid = $shop->sid;
    $shop_id = intval($shop->shop_id);
    
    $db = getUserDb($sid);
    if(!$db)
    {
        logx("getUserDb failed in refreshRrdToken!!", $sid. "/default");
        return;
    }
    
    if(empty($shop->refresh_token)) 
    {
        $db->execute("update cfg_shop set auth_state=2 where shop_id=$shop_id");
        releaseDb($db);
        return;
    }
    
    $rrdApi = new RrdClient();
    $rrdApi->appid = $appkey;
    $rrdApi->secret = $appsecret;
    $rrdApi->refresh_token = $shop->refresh_token;
    $rrdApi->apiUrl = 'http://apis.wxrrd.com/token';
    $retval = $rrdApi->getAccessToken();
    
    $app_key = array('key'=>'', 'secret'=>'', 'session'=>$retval->access_token);
    $app_key = json_encode($app_key);

    $time = $retval->expiresd;
    $expire_time = date("Y-m-d H:i:s",time()+$time);
    
    if(!$db->query("update cfg_shop set app_key=%s,refresh_token=%s,expire_time=%s where shop_id=%d",
            $app_key,$retval->refresh_token,$expire_time,$shop_id))
    {
        logx("{$shop_id} refreshRrdToken update access token failed!!", $sid. "/default");
    }
    
    releaseDb($db);
    
    logx("{$shop_id} refreshRrdToken Success, info:" . print_r($retval, true), $sid. "/default");

}

//有赞刷新access_token
function refreshKdtToken($appkey, $appsecret, $shop)
{
    require_once(TOP_SDK_DIR . '/youzan/YZGetTokenClient.php');
    $sid = $shop->sid;
    $shop_id = intval($shop->shop_id);

    $db = getUserDb($sid);
    if(!$db)
    {
        logx("getUserDb failed in refreshKdtToken!!", $sid . "/default");
        return;
    }

    if(empty($shop->refresh_token))
    {
        $db->execute("update cfg_shop set auth_state=2 where shop_id=$shop_id");
        releaseDb($db);
        return;
    }

    $kdt = new YZGetTokenClient( $appkey,$appsecret);
    $keys = array();
    $type = 'refresh_token';
    $keys['refresh_token'] = $shop->refresh_token;
    $retval = $kdt->get_token($type,$keys);

    $app_key = array('key'=>'', 'secret'=>'', 'session'=>$retval['access_token']);
    $app_key = json_encode($app_key);

    $time = $retval['expires_in'];
    $expire_time = date("Y-m-d H:i:s",time()+$time);

    if(!$db->query("update cfg_shop set app_key=%s,refresh_token=%s,expire_time=%s where shop_id=%d",
        $app_key,$retval['refresh_token'],$expire_time,$shop_id))
    {
        logx("{$shop_id} refreshKdtToken update access token failed!!", $sid. "/default",'error');
    }

    releaseDb($db);

    logx("{$shop_id} refreshKdtToken Success, info:" . print_r($retval, true), $sid . "/default");

}
function refreshPddToken($db,$appkey,$appsecret,&$shop){
    require_once(TOP_SDK_DIR. '/pdd/pddClient2.php');
    $sid = $shop->sid;
    $shop_id = intval($shop->shop_id);
    $client = new pddClient2();
    $params['client_id'] = $appkey;
    $params['client_secret'] = $appsecret;
    $params['grant_type'] = 'refresh_token';
    $params['refresh_token'] = $shop->refresh_token;
    $url = 'http://open-api.pinduoduo.com/oauth/token';
    $retval = $client->curl($url,$params,true);
    $retval = json_decode_safe($retval);
    if(isset($retval->error_response) && $retval->error_response->error_code == 1000000){
        logx("{$shop->shop_id} refreshPDDToken update access token failed!!" . print_r($retval, true), $sid.'/default');

        markShopAuthExpired($db, $shop_id);
        return false;
    }

    $appkey = array(
        'key' => '',
        'secret' => '',
        'session' => $retval->access_token,
    );
    $shop->session = $retval->access_token;
    $appkey = json_encode($appkey);
    $expire_time = time()+$retval->expires_in;

    if(!$db->query("update cfg_shop set app_key=%s,refresh_token=%s,auth_state=1,expire_time=FROM_UNIXTIME(%d) where shop_id=%d",
        $appkey, $retval->refresh_token, $expire_time, $shop_id))
    {
        logx("{$shop_id} refreshPDDToken update access token failed!!" . print_r($retval, true), $sid.'/default', 'error');
    }
    logx("{$shop_id} refreshPDDToken Success, info:" . print_r($retval, true),  $sid.'/default');
    return true;
}


function getShopAuth($sid, &$db, $shop_id) {
    $shop = $db->query_result("select * from cfg_shop where shop_id={$shop_id} and auth_state=1 and is_disabled=0");
    if (!$shop) {
        logx("query shop failed in getShopAuth!", $sid . "/default",'error');
        return false;
    }

    if (!checkAppKey($shop)) {
        logx("shop not auth1!", $sid . "/default",'error');
        return false;
    }

    $shop->sid = $sid;

    if (!getAppSecret($shop, $appkey, $appsecret)) {
        logx("shop not auth2!", $sid . "/default",'error');
        return false;
    }

    $shop->key    = $appkey;
    $shop->secret = $appsecret;

    return $shop;
}

function get_warehouse_auth($sid, $db, $warehouse_id) {
    $warehouse = $db->query_result("select type, api_key from cfg_warehouse where warehouse_id={$warehouse_id} and is_disabled=0");

    if (!$warehouse) {
        logx("query warehouse failed in get_warehouse_auth!", $sid . "/default",'error');
        return false;
    }

    $warehouse = (Object)$warehouse;

    return $warehouse;
}

function splitTime($start_time, $end_time, $intval, $cb) {
    if ($start_time == 0) {
        return $cb($start_time, $end_time);
    }

    /*if($start_time == 0)
    {
        $incr_start_time = $end_time - $intval;
        while($incr_start_time > 0)
        {
            if(!$cb($incr_start_time-1, $end_time+1))
                return false;

            $end_time = $incr_start_time;
            $incr_start_time = $end_time - $intval;
        }

        return true;
    }*/

    $incr_end_time = $start_time;
    while ($incr_end_time < $end_time) {
        // at most one day in case of too much data got
        if ($end_time - $start_time > $intval) {
            $incr_end_time = $start_time + $intval;
        } else {
            $incr_end_time = $end_time;
        }

        if (!$cb($start_time - 1, $incr_end_time + 1))
            return false;

        $start_time = $incr_end_time;
    }

    return true;
}

function getSysCfg(&$db, $key, $def = 0) {
    $val = $db->query_result_single("select `value` from cfg_setting where `key`='" . addslashes($key) . "'", $def);
    if ($val === FALSE) {
        return $def;
    }

    return $val;
}

function setSysCfg(&$db, $key, $val) {
    return $db->execute("INSERT INTO cfg_setting(`key`,`value`) VALUES('" . addslashes($key) . "','" . addslashes($val) . "') ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)");
}

function getAddressID($province, $city, $district, &$province_id, &$city_id, &$district_id) {
    global $g_province_map, $g_city_map, $g_district_map;

    $province_id = 0;
    $city_id     = 0;
    $district_id = 0;

    $tmp = @$g_province_map[ $province ];
    if (!$tmp) {
        logx("invalid_province $province");
        return;
    }

    $province_id = $tmp;
    $tmp         = @$g_city_map["{$province_id}-{$city}"];
    if (!$tmp) {
        logx("invalid_city $city");
        return;
    }

    $city_id = $tmp;
    $tmp     = @$g_district_map["{$city_id}-{$district}"];
    if (!$tmp) {
        if (!empty($district)) logx("invalid_district $district");
        return;
    }
    $district_id = $tmp;
}

//小写金额转大写
function get_amount($num) {
    $c1  = "零壹贰叁肆伍陆柒捌玖";
    $c2  = "分角元拾佰仟万拾佰仟亿";
    $num = round($num, 2);
    $num = $num * 100;
    if (strlen($num) > 10) {
        return false;
    }
    $i = 0;
    $c = "";
    while (1) {
        if ($i == 0) {
            $n = substr($num, strlen($num) - 1, 1);
        } else {
            $n = $num % 10;
        }
        $p1 = substr($c1, 3 * $n, 3);
        $p2 = substr($c2, 3 * $i, 3);
        if ($n != '0' || ($n == '0' && ($p2 == '亿' || $p2 == '万' || $p2 == '元'))) {
            $c = $p1 . $p2 . $c;
        } else {
            $c = $p1 . $c;
        }
        $i   = $i + 1;
        $num = $num / 10;
        $num = (int)$num;
        if ($num == 0) {
            break;
        }
    }
    $j    = 0;
    $slen = strlen($c);
    while ($j < $slen) {
        $m = substr($c, $j, 6);
        if ($m == '零元' || $m == '零万' || $m == '零亿' || $m == '零零') {
            $left  = substr($c, 0, $j);
            $right = substr($c, $j + 3);
            $c     = $left . $right;
            $j     = $j - 3;
            $slen  = $slen - 3;
        }
        $j = $j + 3;
    }
    if (substr($c, strlen($c) - 3, 3) == '零') {
        $c = substr($c, 0, strlen($c) - 3);
    }
    if (empty($c)) {
        return "零元整";
    } else {
        return $c . "整";
    }
}

function splitArea($area, &$province, &$city, &$district) {
    $arr = explode(' ', $area);

    $province = '';
    $city     = '';
    $district = '';

    if (isset($arr[0]))
        $province = $arr[0];

    if (isset($arr[1]))
        $city = $arr[1];

    if (isset($arr[2]))
        $district = $arr[2];
}

function Obj2Arr($obj) {
    $arr = (array)$obj;
    foreach ($arr as $k => $v) {
        if (gettype($v) == 'resource') return;
        if (gettype($v) == 'object' || gettype($v) == 'array')
            $arr[ $k ] = (array)Obj2Arr($v);
    }
    return $arr;
}

function to_query_params($query) {
    if (!is_array($query) && empty($query)) {
        return '';
    }

    $result = '';
    foreach ($query as $key => $value) {
        if (is_bool($value)) $value = $value == 1 ? '1' : '0';
        if (is_null($value)) $value = '';
        $result .= sprintf("%'02d", iconv_strlen($key, 'UTF-8')) . '-' . $key . ':' . sprintf("%'04d", iconv_strlen($value, 'UTF-8')) . '-' . $value . ';';
    }
    if (strlen($result))
        $result = substr($result, 0, strlen($result) - 1);
    return $result;
}
function pddmanualresetAlarm($secs = 120) {
    $res=php_sapi_name();
    $g_os_mode = (strtolower(substr($res,0,3)) === 'apa');
    if (!$g_os_mode){
        pcntl_alarm($secs);
    }
}

function ackResult(&$cols, &$rows)
{
    echo json_encode(array($cols, $rows));
}

function ackOk($num)
{
    echo json_encode(array('updated'=>$num));
}

function ackError($error)
{
	if(strpos($error,'获取不到卖家') !== false && strpos($error,'授权关系') !== false){
		$error = '该仓库未经授权或授权信息错误，请填写正确的授权信息';
	}
    echo json_encode(array('error'=>$error));
}
 function delete_task(&$db,$task_id=0)
    {
        if($task_id>0)
            return $db->execute("delete from sys_asyn_task where task_id=$task_id");
        else
            return $db->execute("delete from sys_asyn_task");

	}
 function delete_tasks(&$db,$task_ids=array())
   {
        if (!empty($task_ids))
        {
            $str_ids = '('.implode(",",$task_ids).')';
            return $db->execute("delete from sys_asyn_task where task_id in $str_ids");
        }
        else
        {
            return $db->execute("delete from sys_asyn_task");
        }

    }

function top_decode($val, $type, $session, $sid, $shopId)
{
    //淘宝解密
    require_once(TOP_SDK_DIR . '/top/security_sdk/top/security/SecurityClient.php');
    require_once(TOP_SDK_DIR . '/top/security_sdk/top/security/YacCache.php');
    global $ekb_top_app_config;

    $appkey = $ekb_top_app_config['app_key'];
    $appsecret = decodeDbPwd($ekb_top_app_config['app_secret'],$ekb_top_app_config['app_key']);

    $c = new top_client;
    $c->appkey = $appkey ;
    $c->secretKey = $appsecret;
    $c->gatewayUrl = TOP_API_HTTPS_URL;

    $client = new SecurityClient($c,TOP_SECURITY_CODE);
    $cacheClient = new YacCache($sid,$shopId);
    $client->setCacheClient($cacheClient);
    //检查代码是否同步到所有服务器
    /*$openSSLKey = ROOT_DIR.'/pids/php_ssl_ext';
    if (isFirstRun($openSSLKey))
    {
        $postData['module'] = phpOpenSSLExt();

        $tb_test_url = "http://120.26.202.135/tb_error.php?";

        $postData = http_build_query($postData);

        $url=$tb_test_url.$postData;

        $cl = curl_init($url);
        curl_setopt($cl, CURLOPT_ENCODING, 'UTF-8');
        curl_setopt($cl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($cl, CURLOPT_SSL_VERIFYPEER, false);
        $content = curl_exec($cl);

        curl_close($cl);
    }*/
    //end

    if($client->isEncryptData($val,$type))
    {
        $originalValue = $client->decrypt($val,$type,$session);
    }
    else
    {
        $originalValue = $val;
    }
    //对解密后数据再验证是否是密文
    if($client->isEncryptData($originalValue,$type))
    {
        return 'ERROR';
    }
    return $originalValue;
}

//淘宝加密
function top_encode($val, $type, $session, $sid, $shopId)
{
    //淘宝解密
    require_once(TOP_SDK_DIR . '/top/security_sdk/top/security/SecurityClient.php');
    require_once(TOP_SDK_DIR . '/top/security_sdk/top/security/YacCache.php');
    global $ekb_top_app_config;

    $appkey = $ekb_top_app_config['app_key'];
    $appsecret = decodeDbPwd($ekb_top_app_config['app_secret'],$ekb_top_app_config['app_key']);

    $c = new top_client;
    $c->appkey = $appkey ;
    $c->secretKey = $appsecret;
    $c->gatewayUrl = TOP_API_HTTPS_URL;

    $client = new SecurityClient($c,TOP_SECURITY_CODE);
    $cacheClient = new YacCache($sid,$shopId);
    $client->setCacheClient($cacheClient);

    $encryptValue = $client->encrypt($val,$type,$session);

    return $encryptValue;
}

//根据平台货品rec_id 自动生成系统货品
//暂时使用
function create_goods($db,$ids)
{
    try
    {
        if(empty($ids))
        {
            return false;
        }
        //获取设置信息
        $sys_goods_match_concat_code = getSysCfg($db,"sys_goods_match_concat_code", 0);
        $goods_match_split_char = getSysCfg($db,"goods_match_split_char", "");
        $regular_api_goods = array();
        $now = date('Y-m-d H:i:s');

        $all_api_goods = $db->query("SELECT ags.rec_id AS id,ags.shop_id,ags.goods_id,ags.spec_id,ags.spec_code,ags.goods_name,
                                    ags.spec_name,ags.outer_id,ags.spec_outer_id,ags.price,ags.pic_url,ags.barcode
                                    FROM api_goods_spec ags WHERE ags.rec_id IN($ids)");
        while($row = $db->fetch_array($all_api_goods))
        {
            if($sys_goods_match_concat_code==3 && $row['outer_id']=='')
            {
                continue;
            }else
            {
                $regular_api_goods[] = $row;
            }
        }
        $sql = "SET @cfg_goods_match_concat_code=\"{$sys_goods_match_concat_code}\"";
        $db->execute($sql);
        $sql = "SET @cfg_goods_match_split_char=\"{$goods_match_split_char}\"";
        $db->execute($sql);
        foreach($regular_api_goods as $v){
            $arr_rec_id[] = $v['id'];
        }
        $arr_rec_id = join(',', $arr_rec_id);
        $merchant_no_arr_res = $db->query("SELECT gs.rec_id,FN_SPEC_NO_CONV(IF($sys_goods_match_concat_code=2,gs.goods_id,gs.outer_id),IF($sys_goods_match_concat_code>=2,gs.rec_id,gs.spec_outer_id)) merchant_no FROM api_goods_spec gs WHERE gs.is_deleted=0 AND gs.rec_id IN($arr_rec_id)");
        while($row =$db->fetch_array($merchant_no_arr_res))
        {
            $merchant_no_arr[] = $row;
        }
        foreach($regular_api_goods as $k => $v)
        {
            if($sys_goods_match_concat_code==3 || $sys_goods_match_concat_code==1)
            {
                $goods_no_res = $v['outer_id'];
            }else
            {
                $goods_no_res = $v['goods_id'];
            }
            $db->execute("begin");
            $goods_goods_data[0]['goods_no'] = $goods_no_res;
            $goods_goods_data[0]['goods_name'] = $v['goods_name'];
            $goods_goods_data[0]['goods_type'] = 1;
            $goods_goods_data[0]['flag_id'] = 0;
            $goods_goods_data[0]['modified'] = $now;
            $goods_goods_data[0]['created'] = $now;

            //兼容旧数据
            $goods_goods_re_old = $db->query_result("SELECT gg.goods_id,gg.goods_no FROM goods_goods gg WHERE gg.deleted=0 AND gg.goods_no='{$v['outer_id']}'");
            if(empty($goods_goods_re_old)){
                //新匹配方式
                $goods_goods_re = $db->query_result("SELECT gg.goods_id,gg.goods_no FROM goods_goods gg WHERE gg.deleted=0 AND gg.goods_no='{$goods_no_res}'");
                if(!empty($goods_goods_re))
                {
                    $res_goods_id = $goods_goods_re['goods_id'];
                }else
                {
                    $res_goods_res =putDataToTable($db, 'goods_goods', $goods_goods_data);
                    if($res_goods_res===false)
                    {
                        logx('put data to goods_goods false');
                        $db->execute('rollback');
                        continue;
                    }
                    $res_goods_id = $db->insert_id();
                }
            }else{
                $res_goods_id = $goods_goods_re_old['goods_id'];
            }

            if(empty($goods_goods_re) && $res_goods_id){
                //导入货品记录日志
                $arr_goods_log[] = array(
                    'goods_type' => 1,//1-货品 2-组合装
                    'goods_id' => $res_goods_id,
                    'spec_id' => 0,
                    'operator_id' => 1,
                    'operate_type' => 11,
                    'message' => '从平台货品导入货品--' . $v['goods_name'],
                    'created' => $now
                );
                $goods_log_res = putDataToTable($db,'goods_log',$arr_goods_log);
                if($goods_log_res===false)
                {
                    logx('put data to goods_log false');
                    $db->execute('rollback');
                    continue;
                }
            }
            $re_spec_no = $db->query_result("SELECT gm.merchant_no FROM goods_merchant_no gm WHERE gm.merchant_no ='{$merchant_no_arr[$k]['merchant_no']}'");
            if($re_spec_no){
                //$list[] = array('spec_no' => $merchant_no_arr[$k]['merchant_no'], 'goods_name' => $v['goods_name'], 'info' => '该商家编码在货品档案或组合装中已经存在');
                $db->execute('rollback');
                continue;
            }
            $goods_spec_data[0]['goods_id'] = $res_goods_id;
            $goods_spec_data[0]['spec_no'] = $merchant_no_arr[$k]['merchant_no'];
            $goods_spec_data[0]['spec_name'] = $v['spec_name'];
            $goods_spec_data[0]['spec_code'] = $v['spec_outer_id'];
            $goods_spec_data[0]['retail_price'] = $v['price'];
            $goods_spec_data[0]['img_url'] = $v['pic_url'];
            $goods_spec_data[0]['barcode'] = $v['barcode'];
            $goods_spec_data[0]['is_allow_neg_stock'] = 0;//默认允许负库存出库为否
            $goods_spec_data[0]['flag_id'] = 9;
            $goods_spec_data[0]['modified'] = $now;
            $goods_spec_data[0]['created'] = $now;
            $res_goods_spec_id = putDataToTable($db,'goods_spec',$goods_spec_data);
            if($res_goods_spec_id===false)
            {
                logx('put data to goods_spec false');
                $db->execute('rollback');
                continue;
            }
            $spec_id = $db->insert_id();
            if($res_goods_spec_id){

                $db->execute("UPDATE goods_goods SET spec_count=(SELECT COUNT(spec_id) FROM goods_spec WHERE goods_id=$res_goods_id AND deleted=0) WHERE goods_id=$res_goods_id");
                if($v['barcode'] != ''){
                    $barcode[0]['barcode'] = $v['barcode'];
                    $barcode[0]["type"] = 1;
                    $barcode[0]["target_id"] = $spec_id;
                    $barcode[0]["tag"] = get_seq("goods_barcode");
                    $barcode[0]["is_master"] = 1;
                    $barcode[0]["created"] = date("Y-m-d H:i:s", time());
                    $goods_barcode_res = putDataToTable($db,'goods_barcode',$barcode);
                    if($goods_barcode_res===false)
                    {
                        logx('put data to goods_barcode false');
                        $db->execute('rollback');
                        continue;
                    }
                }
                //初始化单品库存
                //D('Stock/StockSpec')->initStockSpec($res_goods_spec_id);
                $refresh = getSysCfg($db,'addgoods_refresh_stock',0);
                if($refresh == 0){
                    $warehouse_res=$db->query("SELECT warehouse_id FROM cfg_warehouse WHERE is_disabled=0");
                }else{
                    $warehouse_res=$db->query("SELECT warehouse_id FROM cfg_warehouse WHERE is_disabled=0 and type = 11");
                }
                if(empty($warehouse_res)){
                    return false;
                }
                while($row=$db->fetch_array($warehouse_res))
                {
                    $warehouse_id []=$row['warehouse_id'];
                }

                $stock_spec=array();
                is_array($spec_id)?$spec=$spec_id:$spec[0]['spec_id']=$spec_id;
                $warehouse = $warehouse_id;
                $warehouse_zone_id = $db->query("SELECT zone_id,warehouse_id FROM cfg_warehouse_zone");
                while($row=$db->fetch_array($warehouse_zone_id))
                {
                    $warehouse_zone_id_map[$row['warehouse_id']] = $row['zone_id'];
                }
                foreach ($spec as $s){
                    foreach ($warehouse as $w){
                        $stock_spec[]=array(
                            'spec_id'=>$s['spec_id'],
                            'warehouse_id'=>$w,
                            //'last_position_id'=>'-'.$w['warehouse_id'],
                        );
                        $position_info = $db->query_result("SELECT position_id FROM stock_spec_position WHERE warehouse_id={$w} AND spec_id={$s['spec_id']}");
                        if(!empty($position_info) && !empty($position_info['position_id'])){
                            $stock_position_id = $position_info['position_id'];
                        }else{
                            $stock_position_id = '-'.$w;
                        }
                        $stock_spec_position[]=array(
                            'spec_id'=>$s['spec_id'],
                            'warehouse_id'=>$w,
                            'position_id'=>$stock_position_id,
                            'zone_id'=>$warehouse_zone_id_map[$w],
                        );
                    }

                }

                if(!empty($stock_spec))
                {
                    $stocs_spec_res = putDataToTable($db,'stock_spec',$stock_spec);
                    if($stocs_spec_res===false)
                    {
                        logx('put data to stock_spec false');
                        $db->execute('rollback');
                        continue;
                    }
                }
                if(!empty($stock_spec_position))
                {
                    $stocs_spec_position_res = putDataToTable($db,'stock_spec_position',$stock_spec_position);
                    if($stocs_spec_position_res===false)
                    {
                        logx('put data to stock_spec_position false');
                        $db->execute('rollback');
                        continue;
                    }
                }
            }
            $arr_goods_spec_log[] = array(
                'goods_type' => 1,//1-货品 2-组合装
                'goods_id' => $res_goods_id,
                'spec_id' => $res_goods_spec_id,
                'operator_id' => 1,
                'operate_type' => 11,
                'message' => '从平台货品导入--单品--' . $v['spec_name'],
                'created' => $now
            );
            $goods_log_res = putDataToTable($db,'goods_log',$arr_goods_spec_log);
            if($goods_log_res===false)
            {
                logx('put data to goods_log1 false');
                $db->execute('rollback');
                continue;
            }
            $arr_goods_merchant_no[] = array(
                'merchant_no' => $merchant_no_arr[$k]['merchant_no'],
                'type' => 1,//1普通规格，2组合装
                'target_id' => $spec_id,
                'modified' => $now,
                'created' => $now
            );
            $goods_merchant_res = putDataToTable($db,'goods_merchant_no',$arr_goods_merchant_no);
            if($goods_merchant_res===false)
            {
                logx('put data to goods_merchant_no false');
                $db->execute('rollback');
                continue;
            }
            $db->execute('commit');
        }
    }catch (Exception $e)
    {
        logx('生成系统货品错误:'.print_r($e->getMessage(),true),'','error');
    }
}

function matchUnmatchPlatformGoods($db)
{
    $now = date("Y-m-d G:i:s",time());
    //插入系统日志
    $arr_sys_other_log[] = array(
        "type"        => "22",
        "operator_id" => 1,
        "data"        => 1,
        "message"     => "匹配未匹配货品",
        "careted"     => $now
    );
    $sys_other_log_res = putDataToTable($db,'sys_other_log',$arr_sys_other_log);
    if($sys_other_log_res===false)
    {
        return false;
    }

    //获取配置信息的值
    $sys_goods_match_concat_code = get_config_value("sys_goods_match_concat_code", 0);
    $goods_match_split_char = get_config_value("goods_match_split_char", "");

    $sql = "SET @cfg_goods_match_concat_code=\"{$sys_goods_match_concat_code}\"";
    $this->execute($sql);
    $sql = "SET @cfg_goods_match_split_char=\"{$goods_match_split_char}\"";
    $this->execute($sql);

    $sql1 = "SET @tmp_match_count=0;";

    //将平台货品匹配为系统货品
    $sql2 = "UPDATE api_goods_spec gs INNER JOIN
              (SELECT gs.rec_id,FN_SPEC_NO_CONV(IF($sys_goods_match_concat_code=2,gs.goods_id,gs.outer_id),IF($sys_goods_match_concat_code>=2,gs.rec_id,gs.spec_outer_id)) merchant_no
              FROM api_goods_spec gs WHERE gs.is_deleted=0 AND gs.match_target_type=0) tmp ON gs.rec_id=tmp.rec_id
              LEFT JOIN goods_merchant_no mn ON mn.merchant_no=tmp.merchant_no AND mn.merchant_no<>''
            SET gs.match_target_type=IFNULL(mn.type,0), gs.match_target_id=IFNULL(mn.target_id,0),
              gs.match_code=IFNULL(mn.merchant_no,''),
              gs.is_manual_match=IF(@tmp_match_count:=@tmp_match_count+IF(ISNULL(mn.target_id),0,1),0,0),
              gs.is_stock_changed=IF(gs.match_target_id,1,0),gs.modify_flag=gs.is_stock_changed,stock_change_count=stock_change_count+1;";

    //更新平台货品数据与匹配的货品对应-----单品
    $sql3 = "UPDATE api_goods_spec ag,goods_spec gs,goods_goods gg,goods_class gc
            SET ag.brand_id=gg.brand_id,ag.class_id_path=gc.path,gs.img_url=IF(gs.img_url='',ag.pic_url,gs.img_url)
            WHERE ag.is_deleted=0 AND ag.match_target_type=1 AND gs.spec_id=ag.match_target_id
            AND gg.goods_id=gs.goods_id AND gc.class_id=gg.class_id;";

    //更新平台货品数据与匹配的货品一一对应-----组合装
    $sql4 = "UPDATE api_goods_spec ag,goods_suite gs,goods_class gc
            SET ag.brand_id=gs.brand_id,ag.class_id_path=gc.path
            WHERE ag.is_deleted=0 AND ag.match_target_type=2 AND gs.suite_id=ag.match_target_id AND gc.class_id=gs.class_id;";

    //更新包含无效货品的订单
    $sql5 = "UPDATE api_trade ax,api_trade_order ato,api_goods_spec ag SET ato.is_invalid_goods=0,ax.bad_reason=(bad_reason&~1)
            WHERE ato.is_invalid_goods=1 AND ato.platform_id = ag.platform_id AND ato.goods_id = ag.goods_id
            AND ato.spec_id = ag.spec_id AND ato.status <=30 AND ax.platform_id=ato.platform_id AND ax.tid=ato.tid AND ag.modify_flag > 0 AND (ag.modify_flag&1);";

    $this->execute($sql1);
    $this->execute($sql2);
    $this->execute($sql3);
    $this->execute($sql4);
    $this->execute($sql5);
    //刷新库存同步规则
    $db->execute("CALL I_DL_INIT_REFRESH_STOCK_SYNC()");
    //插入操作日志
    $sql_log = "INSERT INTO sys_other_log (`type`,operator_id,`data`,message)
                SELECT 14,1,ag.rec_id,concat_ws(' ','自动匹配平台货品,平台货品ID为:',ag.goods_id,'规格ID为:',ag.spec_id,'匹配系统货品商家编码:',ag.match_code)
                FROM api_goods_spec ag
                WHERE ag.modify_flag > 0 AND (ag.modify_flag&1) AND ag.match_target_type > 0 ;";
    $db->execute($sql_log);
    //更新modify_flag
    $sql = "UPDATE api_goods_spec SET modify_flag = (modify_flag&~1) WHERE modify_flag > 0;";
    $this->execute($sql);
}

?>
