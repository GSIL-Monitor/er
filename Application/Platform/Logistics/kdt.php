<?php
require_once(TOP_SDK_DIR . '/youzan/YZTokenClient.php');
require_once(ROOT_DIR . '/Manager/utils.php');
require_once(ROOT_DIR . '/Logistics/util.php');
require_once(ROOT_DIR . '/Common/api_error.php');

function kdt_get_logistics_companies(&$db, &$shop, &$companies, &$error_msg)
{
    $shopId=$shop->shop_id;
	$appkey = $shop->key;
    $appsecret = $shop->secret;
    $session = $shop->session;
    $client = new YZTokenClient($session);
    $method = 'youzan.logistics.express.get';
    $methodVersion = '3.0.0';//要调用的api版本号

    $params = array();

    $retval = $client->post($method, $methodVersion, $params);

    if(API_RESULT_OK != kdtErrorTest($retval,$db,$shopId))
    {
        if (40010 == @$retval['code'])
        {
            releaseDb($db);
            refreshKdtToken($appkey, $appsecret, $shop);
            $error_msg = $retval['error_msg'];
            return TASK_OK;
        }
        $error_msg['status'] = 0;
        $error_msg['info'] = $retval['error_msg'];
        logx("kdt_get_logistics_companies  fail error_msg:".$retval['error_msg'],$sid.'/Logistics','error');

        return TASK_OK;
    }
	$row = $retval['response']['allExpress'];

    foreach($row as $key=>$value)
    {
        $companies[]=array
        (
            'shop_id' => $shop->shop_id,
            'logistics_code' => $value['id'],
            'name' => $value['name'],
            'created' => date('Y-m-d H:i:s',time())
        );
    }
    return true;
}

function kdt_sync_logistics(&$db, &$trade, $sid)
{
    getAppSecret($trade, $appkey, $appsecret);
	$shop = getShopAuth($sid, $db, $trade->shop_id);

	$appkey =$shop->key;
    $appsecret = $shop->secret;
    $session = $shop->session;
    $client = new YZTokenClient($session);
    $method = 'youzan.logistics.online.confirm';
    $methodVersion = '3.0.0';
    $params = array();

    $params['tid'] = $trade->tid;
    if($trade->logistics_type == 1)
    {
        $params['is_no_express'] = 1;
    }
    else
    {
        if(is_empty($db, $sid, $trade->rec_id, $trade->tid, $trade->logistics_no, $trade->logistics_code))
        {
            logx("kdt_sync_empty: tid {$trade->tid} logistics_no {$trade->logistics_no} logistics_code {$trade->logistics_code}", $sid.'/Logistics');
            return false;
        }

        if(1 == $trade->is_part_sync)//有赞拆单发货
        {
            //获取递交后sales_trade_order表的原始子订单号src_oid
            handle_special_oid($db, $sid, $trade->platform_id, $trade->tid, $trade->trade_id, $trade->oids, $error_msg);

            $t_oids = explode(',', $trade->oids);


            for($k = 0; $k < count($t_oids); $k++)
            {
                $t_oids[$k] = "'".$t_oids[$k]."'";
            }
            $trade->oids = implode(",",$t_oids);

            $kdt_oid = $db->query("select bind_oid from api_trade_order where platform_id = 17 and oid in ($trade->oids)");

            if(!$kdt_oid)
            {
                logx("query bind_oid error in kdt_sync_logistics!", $sid.'/Logistics');
                $error_msg = '获取有赞交易明细的编号失败';
                return false;
            }

            $kdt_oid_arr = array();
            while($row = $db->fetch_array($kdt_oid))
            {
                $kdt_oid_arr[] = $row['bind_oid'];
            }
            $db->free_result($kdt_oid);

            $kdt_oids = implode(',', $kdt_oid_arr);

            $params['oids'] = $kdt_oids;
        }

        $params['is_no_express'] = 0;
        $params['out_stype'] = $trade->logistics_code;
        $params['out_sid'] = $trade->logistics_no;

    }

    $retval = $client->post($method, $methodVersion, $params);

    if(API_RESULT_OK != kdtErrorTest($retval, $db, $trade->shop_id))
    {
        if (40010 == @$retval['code'])
        {
            releaseDb($db);
            refreshKdtToken($appkey, $appsecret, $shop);
            return TASK_OK;
        }
        set_sync_fail($db, $sid, $trade->rec_id, 2, $retval['error_msg']);
        $error_msg['status'] = 0;
        $error_msg['info'] = $retval['error_msg'];
        logx("kdt_export_sync_fail: tid {$trade->tid} logistics_no {$trade->logistics_no} logistics_code {$trade->logistics_code} error:{$retval['error_msg']}", $sid.'/Logistics' );
        return false;
    }

    set_sync_succ($db, $sid, $trade->rec_id);
    logx("kdt_sync_ok: tid {$trade->tid}", $sid.'/Logistics');

    return true;
}