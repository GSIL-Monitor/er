<?php
require_once(ROOT_DIR . '/Manager/utils.php');
require_once(ROOT_DIR . '/Logistics/util.php');
require_once(TOP_SDK_DIR . '/kl/klClient.php');
require_once(ROOT_DIR.'/Common/api_error.php');

/*$GLOBALS['kl_logistics_name_map'] = array(
	'EMS' => "邮政速递",
	'SF' => "顺丰速运",
	'YUNDA' => "韵达快递",
	'ZTO' => "中通快递",
	'BEST' => "百世汇通快递",
	'YTO' => "圆通快递",
	'STO' => "申通快递",
	'TTK' => "天天快递",
	'QF' => "全峰快递",
	'ZJS' => "宅急送",
	'GTO' => "国通",
	'SJKD' => "快捷速递",
	'DBWL' => "德邦物流",
	'YSWL' => "优速物流",
	'LBWL' => "龙邦物流",
	'TDHY' => "天地华宇",
	'SEKD' => "速尔快递",
	'FEDEX' => "FEDEX-国际",
	'DHL' => "DHL",
	'TNT' => "TNT",
	'UPS' => "UPS",
	'YAMAXUNWULIU' => "亚马逊物流",
	'SUFANG' => "速方快递",
	'ZENGSU' => "增速跨境",
	'ZHONGYUAN' => "中远E环球",
	'SHIPGCE' => "飞洋快递",
	'ZHIMAKAIMEN' => "芝麻开门",
	'WANXIANGWULIU' => "万象物流",
	'GOLDHAITAO' => "金海淘",
	'XYNYC' => "新元国际",
	'XIAOCEX' => "小C海淘",
	'ZHONGWAIYUN' => "中外运",
	'EMSGUOJI' => "EMS国际",
	'ZHONGTIEWULIU' => "中铁快运",
	'EPANEX' => "泛捷国际",
	'JIUYESCM' => "九曳供应链",
	'MEIQUICK' => "美快",
	'SUNSPEEDY' => "新速航国际转运",
	'XLOBO' => "贝海物流",
	'BIRDEX' => "笨鸟海淘",
	'QATEST' => "QA-自动化测试[勿删]",
	'BanMa' => "斑马物流",
	'ETK' => "E特快"
	);*/

function kl_logistics_companies(&$db, &$shop, &$companies, &$error_msg)
{
    $shop_id = $shop->shop_id;
    $sid = $shop->sid;
    $kl = new klClient();
    $kl->app_key = $shop->key;
    $kl->app_secret = $shop->secret;
    $kl->access_token = $shop->session;
    $kl->method = "kaola.logistics.companies.get";
    $params = array();
    $retval = $kl->execute($params);
    logx("kl_logistics_companies".print_r($retval,true) ,$sid.'/Logistics');
    if (API_RESULT_OK != klErrorTest($retval, $db, $shop_id)) {
        $error_msg['status'] = 0;
        $error_msg['info'] = $retval->error_msg;
        logx("kl_get_logistics_companies kl_get fail error_msg:{$error_msg['info']}",$sid.'/Logistics','error');
        return TASK_OK;
    }else{
        $ret = $retval->kaola_logistics_companies_get_response->logistics_companys;
        for ($i=0; $i < count($ret); $i++) {
            $company = $ret[$i];
            $companies[] = array(
                'shop_id' => $shop->shop_id,
                'name' => $company->express_company_name,
                'logistics_code' => $company->express_company_code,
                'created' => date('Y-m-d H:i:s',time())
            );
        }
        return true;
    }

}

function kl_sync_logistics(&$db, &$trade, &$sid)
{
    getAppSecret($trade, $appkey, $appsecret);
    //获取店铺授权信息
    $shop = getShopAuth($sid, $db, $trade->shop_id);

    $tid = $trade->tid;

    $sid = $shop->sid;
    $appkey = $shop->key;
    $appsecret = $shop->secret;
    $session = $shop->session;

    $kl = new klClient();
    $kl->app_key = $appkey;
    $kl->app_secret = $appsecret;
    $kl->access_token = $session;
    $kl->method = "kaola.logistics.deliver";
    $params = array(
        'order_id' => $tid,
        'express_company_code' => $trade->logistics_code,
        'express_no' => $trade->logistics_no
    );

    $retval = $kl->execute($params);

    logx("kl_sync_logistics retval:".print_r($retval,true),$sid.'/Logistics');
    if (API_RESULT_OK != klErrorTest($retval, $db, $trade->shop_id)) {
        $error_msg['status'] = 0;
        $error_msg['info'] = $retval->error_msg;
        set_sync_fail($db, $sid, $trade->rec_id, 2, $retval->error_msg);
        logx ( "kl_sync_fail: tid {$trade->tid} logistics_no {$trade->logistics_no} logistics_code {$trade->logistics_code} error:{$retval->error_msg}", $sid.'/Logistics' );
        return TASK_OK;
    }
    set_sync_succ($db, $sid, $trade->rec_id);
    logx ( "kl_sync_ok: tid {$trade->tid} logistics_no {$trade->logistics_no} logistics_code {$trade->logistics_code}", $sid.'/Logistics' );
    return true;
}