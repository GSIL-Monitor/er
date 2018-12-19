<?php
require_once(TOP_SDK_DIR .'/jpw/JpwClient.php');

$GLOBALS['jpw_logistics_name_map'] = array(
    "andewuliu" => "安得物流",
    "annengwuliu" => "安能物流",
    "anxindakuaixi" => "安信达快递",
    "auspost" => "澳邮快运",
    "baishiwuliu" => "百世物流",
    "cces" => "CCES",
    "chinapost" => "China Post（中国国际邮政）",
    "city100" => "城市100",
    "datianwuliu" => "大田快运",
    "debangwuliu" => "德邦物流",
    "dhl" => "DHL代理",
    "disifang" => "递四方",
    "ems" => "EMS经济快递",
    "emsbiaozhun" => "EMS标准快递",
    "emsguoji" => "EWE全球快递",
    "exfresh" => "安鲜达",
    "fanyukuaidi" => "凡宇快递",
    "Feikangda" => "飞康达快运",
    "feiyuanvipshop" => "飞远配送",
    "ganzhongnengda" => "能达速递",
    "guotongkuaidi" => "国通快递",
    "hengluwuliu" => "恒路",
    "hkpost" => "香港进口",
    "hongtaiwuliu" => "鸿泰物流",
    "huitongkuaidi" => "百世快递",
    "httx56" => "汇通天下物流",
    "jiajiwuliu" => "佳吉快运",
    "jiayiwuliu" => "佳怡物流",
    "jiayunmeiwuliu" => "加运美速递",
    "jd" => "京东物流",
    "jinguangsudikuaijian" => "京广快递",
    "kuaijiesudi" => "快捷快递",
    "lianbangkuaidi" => "联邦快递",
    "lianhaowuliu" => "联昊通",
    "pcaexpress" => "PCA Express（PCA快递）",
    "pingandatengfei" => "平安达腾飞",
    "quanfengkuaidi" => "全峰快递",
    "quanritongkuaidi" => "全日通快递",
    "quanyikuaidi" => "全一快递",
    "rrs" => "日日顺物流",
    "rufengda" => "如风达",
    "saiaodi" => "赛澳递",
    "shentong" => "申通快递",
    "shunfeng" => "顺丰速运",
    "subida" => "速必达",
    "suer" => "速尔快递",
    "sutongwuliu" => "速通物流",
    "tengdawuliu" => "腾达物流",
    "tiandihuayu" => "天地华宇",
    "tiantian" => "天天快递",
    "UPS" => "UPS快递",
    "wanxiangwuliu" => "万象物流",
    "xinbangwuliu" => "新邦物流",
    "xinfengwuliu" => "信丰物流",
    "yafengsudi" => "亚风快递",
    "yibangwuliu" => "一邦速递",
    "vipexpress" => "鹰运国际速递",
    "yitongfeihong" => "一统快递",
    "youshuwuliu" => "优速物流",
    "youzhengguoji" => "国际快递查询",
    "youzhengguonei" => "邮政快递包裹",
    "yuananda" => "源安达",
    "yuanchengwuliu" => "远成物流",
    "yuantong" => "圆通速递",
    "yuefengwuliu" => "越丰物流",
    "yunda" => "韵达快递",
    "yuntongkuaidi" => "运通中港物流",
    "yuxingwuliu" => "宇鑫物流",
    "zengyisudi" => "增益速递",
    "zhaijisong" => "宅急送",
    "zhongtiewuliu" => "中铁物流",
    "zhongtong" => "中通快递",
    "zhongtongkuaiyun" => "中通快运",
    "zhuanyunsifang" => "转运四方",
    "ztky" => "中铁快运",
);

function jpw_get_logistics_companies(&$db, &$shop, &$companies, &$error_msg)
{
    global $jpw_logistics_name_map;

    foreach($jpw_logistics_name_map as $code=>$name)
    {
        $companies[]=array
        (
            'shop_id'=>$shop->shop_id,
            'logistics_code'=>$code,
            'name'=>$name,
            'created'=>date('Y-m-d H:i:s',time())
        );
    }
    return true;
}

function jpw_sync_logistics(&$db, &$trade, $sid)
{
    global  $jpw_logistics_name_map;
    getAppSecret ( $trade, $appkey, $appsecret );
    $shopid = $trade->shop_id;
    $logistics_code = $trade->logistics_code;
    $token = $db->query_result("select refresh_token,re_expire_time from cfg_shop where shop_id = {$shopid}");
    if(is_empty($db, $sid, $trade->rec_id, $trade->tid, $trade->logistics_no, $trade->logistics_code, $jpw_logistics_name_map[$trade->logistics_code]))
    {
        logx("jpw_sync_empty: tid {$trade->tid} logistics_no {$trade->logistics_no} logistics_code {$trade->logistics_code} logistics_name {$jpw_logistics_name_map[$trade->logistics_code]}", $sid.'/Logistics');
        return false;
    }
    // API参数
    $jpw=new jpwClient();
    $jpw->secret = $appsecret;
    $params = array(
        'jType' => 'send_goods',
        'jCusKey' => $trade->session,
        'jOrderNo' => $trade->tid,
        'jDeliverEname' => $trade->logistics_code,
        'jDeliverCname' => $jpw_logistics_name_map[$trade->logistics_code],
        'jDeliverNo' => $trade->logistics_no,
        'token' => $token['refresh_token'],
        'type' => 'json'
    );
    $retval = $jpw->execute($params);

    logx("jpw_sync_logistics :".print_r($retval,true) ,$sid.'/Logistics');

    if(API_RESULT_OK != jpwErrorTest($retval,$db,$shopid))
    {

        if (10004 == intval(@$retval->info) || 10040 == intval(@$retval->info) || 10042 == intval(@$retval->info))
        {
            releaseDb($db);
            refreshJpwToken($appkey, $appsecret, $trade);
            return false;
        }
        set_sync_fail ( $db, $sid, $trade->rec_id, 2, $retval->error_msg );
        logx ( "jpw_sync_fail: tid {$trade->tid} logistics_no {$trade->logistics_no} logistics_code {$trade->logistics_code} error:{$retval->error_msg}", $sid.'/Logistics' );
        return false;
    }

    set_sync_succ ( $db, $sid, $trade->rec_id );
    logx ( "jpw_sync_ok: tid {$trade->tid}", $sid.'/Logistics' );

    return true;
}

?>