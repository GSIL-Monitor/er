<?php
//微店
require_once(ROOT_DIR . '/Manager/utils.php');
require_once(ROOT_DIR . '/Logistics/util.php');
require_once(ROOT_DIR.'/Common/api_error.php');
require_once(TOP_SDK_DIR . '/vdian/vdianClient.php');


function vdian_get_logistics_companies(&$db, &$shop, &$companies, &$error_msg)
{
    $client = new vdianClient();
    $client->method = 'vdian.order.expresslist';
    $client->token = $shop->session;

    $params = array(
        'a'=>1
    );
    $retval = $client->execute($params);

    if(API_RESULT_OK != vdianErrorTest($retval,$db,$shop->shop_id))
    {
        logx("vdian_get_logistics_companies : {$shop->shop_id} {$retval->error_msg}", $shop->sid. "/Logistics");
        $error_msg["status"] = 0;
        $error_msg["info"]   = $retval->error_msg;
        return false;
    }
//    //微店平台更改物流公司,需先清除旧的物流公司
    if(!$db->execute("DELETE FROM api_logistics_shop WHERE shop_id={$shop->shop_id}"))
    {
        logx("vdian_get_logistics_companies 删除物流公司失败",$shop->sid. "/Logistics");
    }

    foreach($retval->result->common_express as $company)
    {
        $companies[] = array(
            'shop_id' => $shop->shop_id,
            'logistics_code' => $company->id,
            'name' => $company->express_company,
            'created' => date('Y-m-d H:i:s', time())
        );
    }
    return true;
}

function vdian_sync_logistics(&$db, &$trade, $sid)
{
    //getAppSecret($trade, $appkey, $appsecret);

    $client = new vdianClient();
    $client->method = 'vdian.order.deliver';
    $client->token = $trade->session;

    $params['order_id'] = $trade->tid;
    if($trade->logistics_type == 1)//无需物流发货
    {
        $params['express_type'] = 999;
    }
    else if($trade->logistics_code)//指定物流发货
    {
        $params['express_no'] = $trade->logistics_no;//物流单号
        $params['express_type'] = $trade->logistics_code;//快递编号编号
    }
    else//自定义物流发货
    {
        $params['express_type'] = 0;
        $params['express_no'] = $trade->logistics_no;
        $params['express_custom'] = iconv_substr($trade->logistics_name, 0, 10,'UTF-8');
        //传递自定义的物流公司名称，长度限制在10个字符以内

        if($trade->logistics_name == ''){
            $error_msg = '微店自定义物流发货，物流公司名称不能为空';
            logx("vdian_sync_fail tid:{$trade->tid} logistics_no:{$trade->logistics_no} logistics_code:{$trade->logistics_code} logistics_name:{$trade->logistics_name} error:{$error_msg}",$sid. "/Logistics");
            set_sync_fail($db, $sid, $trade->rec_id, 2, $error_msg);
            return false;
        }
    }
    logx("vdian_sync express_type:".$params['express_type'],$sid. "/Logistics");

    if(1 == $trade->is_part_sync){
        handle_special_oid($db, $sid, $trade->platform_id, $trade->tid, $trade->trade_id, $trade->oids, $error_msg);

        $t_oids = explode(',', $trade->oids);
        for ($k = 0; $k < count($t_oids); $k++) {
            $t_oids[$k] = "'" . $t_oids[$k] . "'";
        }
        $trade->oids = implode(",", $t_oids);
        $vd_oid = $db->query("select goods_id,spec_id from api_trade_order where platform_id = 32 and oid in ($trade->oids)");

        if (!$vd_oid) {
            logx("query bind_oid error in mt_sync_logistics!", $sid. "/Logistics");
            $error_msg = '获取微店sku失败';
            return false;
        }
        while ($row = $db->fetch_array($vd_oid)) {
            $params['items'][] = array(
                'item_id'     => $row['goods_id'],
                'item_sku_id' => $row['spec_id']
            );
        }
        //拆单变更接口
        $client->method = 'vdian.order.deliver.split';
    }

    $retval = $client->execute($params);

    if(API_RESULT_OK != vdianErrorTest($retval, $db, $trade->shop_id))
    {
        if(strpos($retval->error_msg, '非法')!==false){
            logx("vdian_export_sync_ingore: tid:{$trade->tid} logistics_no:{$trade->logistics_no} logistics_code:{$trade->logistics_code} logistics_name:{$trade->logistics_name} error:{$retval->error_msg}", $sid. "/Logistics");
            //return false;
        }
        set_sync_fail($db, $sid, $trade->rec_id, 2, $retval->error_msg);
        if(10013 == $retval->status->status_code||10016 == $retval->status->status_code||10026 == $retval->status->status_code)
        {
            releaseDb($db);
            refreshVdianToken($trade);
            return false;
        }
        logx("vdian_export_sync_fail: tid:{$trade->tid} logistics_no:{$trade->logistics_no} logistics_code:{$trade->logistics_code} logistics_name:{$trade->logistics_name} error:{$retval->error_msg}", $sid. "/Logistics");
        return false;
    }

    set_sync_succ($db, $sid, $trade->rec_id);
    logx("vdian_sync_ok: tid:{$trade->tid} logistics_no:{$trade->logistics_no} logistics_code:{$trade->logistics_code} logistics_name:{$trade->logistics_name}", $sid. "/Logistics");

    return true;
}
