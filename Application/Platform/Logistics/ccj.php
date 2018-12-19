<?php
require_once(ROOT_DIR . '/Manager/utils.php');
require_once(ROOT_DIR . '/Logistics/util.php');
require_once(ROOT_DIR.'/Common/api_error.php');
require_once(TOP_SDK_DIR . '/chuchujie/chuchujieClient.php');

$GLOBALS['ccj_logistics_name_map'] = array(
            'zhongtong'=>'中通快递',
            'ems'=>'EMS快递',
            'yuantong'=>'圆通快递',
            'yunda'=>'韵达快递',
            'shentong'=>'申通快递',
            'shunfeng'=>'顺丰快递',
            'chengji'=>'城际快递',
            'aae'=>'AAE快递',
            'anjie'=>'安捷快递',
            //'anneng'=>'安能物流',
            'aoshuo'=>'奥硕物流',
            'aramex'=>'Aramex国际快递',
            /*'baiqian'=>'百千城国际物流',
            'balunzhi'=>'巴伦支',
            'baotongda'=>'宝通达',
            'benteng'=>'成都奔腾国际快递',
            'changtong'=>'长通物流',
            'chengguang'=>'程光快递',*/
            'chengshi100'=>'城市100',
            'chuanxi'=>'传喜快递',//
            /*'chuanzhi' => '传志快递',
            'chukouyi' => '出口易物流',*/
            'citylink' => 'CityLinkExpress',
            'coe' => '东方快递',
            /*'coscon' => '中国远洋运输(COSCON)',
            'cszx' => '城市之星',
            'dada' => '大达物流',
            'dajin' => '大金物流',*/
            'datian' => '大田物流',
            //'dayang' => '大洋物流快递',
            'debangwuliu' => '德邦物流',
            //'dechuang' => '德创物流',
            'dhl' => 'DHL快递',
            //'diantong' => '店通快递',
            'disifang' => '递四方速递',
            'dpex' => 'DPEX快递',
            'dsu' => 'D速快递',
            'ees' => '百福东方物流',
            'eyoubao' => 'E邮宝',
            //'fanyu' => '凡宇快递',
            'fardar' => 'Fardar',
            'fedex' => '国际Fedex',
            //'fedexcn' => 'Fedex国内',
            'feibao' => '飞豹快递',
            'feihang' => '原飞航物流',
            /*'feihu' => '飞狐快递',
            'feite' => '飞特物流',
            'feiyuan' => '飞远物流',
            'fengda' => '丰达快递',
            'fkd' => '飞康达快递',
            'gaotie' => '高铁快递',*/
            'gdyz' => '广东邮政物流',
            'gnxb' => '邮政国内小包',
            'gongsuda' => '共速达物流|快递',
            //'guanda' => '冠达快递',
            'guotong' => '国通快递',
            'haihong' => '山东海红快递',
            //'haolaiyun' => '好来运快递',
            //'haosheng' => '昊盛物流',
            'hebeijianhua' => '河北建华快递',
            'henglu' => '恒路物流',
            /*8'huacheng' => '华诚物流',
            'huahan' => '华翰物流',
            'huahang' => '华航快递',
            'huangmajia' => '黄马甲快递',*/
            'huaqi' => '华企快递',
            'huayu' => '天地华宇物流',
            'huitong' => '汇通快递',
            //'hutong' => '户通物流',
            'hwhq' => '海外环球快递',
            'jiaji' => '佳吉快运',
            'jiayi' => '佳怡物流',
            'jiayu' => '佳宇物流',
            'jiayunmei' => '加运美快递',
            'jiete' => '捷特快递',
            'jinda' => '金大物流',
            'jingguang' => '京广快递',
            'jinyue' => '晋越快递',
            //'jiuyi' => '久易快递',
            'jixianda' => '急先达物流',
            'jldt' => '嘉里大通物流',
            'kangli' => '康力物流',
            //'kcs' => '顺鑫(KCS)快递',
            'kuaijie' => '快捷快递',
            //'kuaiyouda' => '快优达速递',
            //'kuanrong' => '宽容物流',
            'kuayue' => '跨越快递',
            //'lanhu' => '蓝弧快递',
            'lejiedi' => '乐捷递快递',
            'lianhaotong' => '联昊通快递',
            'lijisong' => '成都立即送快递',
            //'lindao' => '上海林道货运',
            'longbang' => '龙邦快递',
            'menduimen' => '门对门快递',
            //'minbang' => '民邦快递',
            /*'mingliang' => '明亮物流',
            'minsheng' => '闽盛快递',
            'nell' => '尼尔快递',*/
            'nengda' => '港中能达快递',
            //'nsf' => '新顺丰（NSF）快递',
            'ocs' => 'OCS快递',
            //'peixing' => '陪行物流',
            //'pinganda' => '平安达',
            'quanchen' => '全晨快递',
            'quanfeng' => '全峰快递',
            'quanritong' => '全日通快递',
            'quanyi' => '全一快递',
            //'riyu' => '日昱物流',
            //'rpx' => 'RPX保时达',
            'rufeng' => '如风达快递',
            'saiaodi' => '赛澳递',
            'santai' => '三态速递',
            //'scs' => '伟邦(SCS)快递',
            'shengan' => '圣安物流',
            //'shengbang' => '晟邦物流',
            'shengfeng' => '盛丰物流',
            'shenghui' => '盛辉物流',
            //'shiyun' => '世运快递',
            //'suchengzhaipei' => '速呈宅配',
            'suijia' => '穗佳物流',
            'sure' => '速尔快递',
            'tiantian' => '天天快递',
            'tnt' => 'TNT快递',
            //'ucs' => '合众速递',
            'ups' => 'UPS快递',
            'usps' => 'USPS快递',
            //'wanbo' => '万博快递',
            'weitepai' => '微特派',
            //'xianglong' => '祥龙运通快递',
            'xinbang' => '新邦物流',
            'xinfeng' => '信丰快递',
            //'xingchengzhaipei' => '星程宅配快递',
            'xiyoute' => '希优特快递',
            'yad' => '源安达快递',
            'yafeng' => '亚风快递',
            'yibang' => '一邦快递',
            'yinjie' => '银捷快递',
            //'yishunhang' => '亿顺航快递',
            'yousu' => '优速快递',
            'ytfh' => '北京一统飞鸿快递',
            'yuancheng' => '远成物流',
            'yuefeng' => '越丰快递',
            //'yuhong' => '宇宏物流',
            //'yumeijie' => '誉美捷快递',
            'yuntong' => '运通中港快递',
            //'zengyi' => '增益快递',
            'zhaijisong' => '宅急送快递',
            'zhengzhoujianhua' => '郑州建华快递',
            'zhima' => '芝麻开门快递',
            'zhongtian' => '济南中天万运',
            'zhongtie' => '中铁快运',
            'zhongxinda' => '忠信达快递',
            'zhongyou' => '中邮物流',
            //'qita'=>'其它',
            'wanxiangwuliu'=>'万象物流',
            'jd'=>'京东快递',
            'yamaxun'=>'亚马逊物流',
            'ririshunwuliu'=>'日日顺物流',
            'fkd' => '飞康达快递',
            'sxhongmajia'=>'山西红马甲',
            'nanjingshengbang'=>'晟邦物流',
            'pjbest'=>'品骏快递',
            'feiyuanvipshop'=>'飞远配送',
            'sccod'=>'丰程物流'

    );
//下载物流公司
function ccj_get_logistics_companies(&$db,&$shop,&$companies,&$error_msg)
{
     global $ccj_logistics_name_map;
     
     foreach($ccj_logistics_name_map as $code=>$name)
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

function ccj_sync_logistics(&$db, &$trade, $sid)
{
    getAppSecret($trade, $appkey, $appsecret);

    if(is_empty($db, $sid, $trade->rec_id, $trade->tid, $trade->logistics_no, $trade->logistics_code))
    {
        logx("ccj_sync_empty: tid {$trade->tid} logistics_no {$trade->logistics_no} logistics_code {$trade->logistics_code}", $sid.'/Logistics');
        return false;
    }

    global $ccj_app_config;
            
    $session = $ccj_app_config['org_name']; 
    $ccj = new ChuchujieClient();
    
    $ccj->setApp_key($appkey);
    $ccj->setDirname('/Order/api_order_shipping_v2');
    $ccj->setApp_secret($appsecret);
    $ccj->setSession($session);
    
    $params = array();
    $params['oid']=$trade->tid;
    $params['express_company'] = $trade->logistics_code;
    $params['express_no']=$trade->logistics_no;

    $retval=$ccj->execute($params);
    
    logx("楚楚街".print_r($retval,true),$sid.'/Logistics');
    
    if (API_RESULT_OK != ccjErrorTest ( $retval, $db, $trade->shop_id ))
    {
        set_sync_fail ( $db, $sid, $trade->rec_id, 2, $retval->error_msg );
    
        logx ( "ccj_sync_fail: tid {$trade->tid} logistics_no {$trade->logistics_no} logistics_code {$trade->logistics_code} error:{$retval->error_msg}", $sid.'/Logistics');
        return false;
    }

    set_sync_succ($db, $sid, $trade->rec_id);
    logx("ccj_sync_ok: tid {$trade->tid}", $sid.'/Logistics');

    return true;

}

?>