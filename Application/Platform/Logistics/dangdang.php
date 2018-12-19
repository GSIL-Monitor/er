<?php
//当当
require_once(ROOT_DIR . '/Manager/utils.php');
require_once(ROOT_DIR . '/Logistics/util.php');
require_once(TOP_SDK_DIR . '/dangdang/DangdangClient.php');

function dangdang_get_logistics_companies(&$db,&$shop,&$companies,&$error_msg)
{
    $result = $db->query("select dl.logistics_name as name, dlc.logistics_code as code ".
                        " from dict_logistics_code dlc ".
                        " left join dict_logistics dl using(logistics_type) ".
                        " where dlc.platform_id = 4");       
    foreach($result as $k=>$v){  
        $companies[]=array(
            'shop_id' => $shop->shop_id,
            'logistics_code' => $v['code'],
            'name' => $v['name'],
            'created' => date('Y-m-d H:i:s',time())
        );

    }
    return true;        
}

function dd_sync_logistics(&$db, &$trade, $sid,&$code_msg)
{
    getAppSecret($trade, $appkey, $appsecret);
    
    if(is_empty($db, $sid, $trade->rec_id, $trade->tid, $trade->logistics_code, $trade->logistics_no))
    {
        logx("dd_empty_arg: tid {$trade->tid}, logistics_no {$trade->logistics_no}, logistics_code {$trade->logistics_code}", $sid.'/Logistics');
        return false;
    }
    
    //API参数
    
        $dd = new DangdangClient(DD_NEW_API_URL);
        $dd->setAppKey($appkey);
        $dd->setAppSecret($appsecret);
        $dd->setMethod('dangdang.order.goods.send');
        $dd->setSession($trade->session);
        $params = array();
        $function = 'dangdang.order.goods.send';
    
    $date = date('Y-m-d H:i:s', time());

    //货品
    $result = $db->query(sprintf("select goods_id,CAST(num AS UNSIGNED) num from api_trade_order where tid='%s' and platform_id = 7" , $trade->tid));
    $goods = '';
    
    while($row = $db->fetch_array($result))
    {
        $goods .= '<ItemInfo><itemID>' . $row['goods_id'] . '</itemID><sendGoodsCount>' . $row['num'] . '</sendGoodsCount><belongPromo></belongPromo></ItemInfo>';
    }
    $db->free_result($result);

    //XML模板
    $template = <<<EOD
<?xml version="1.0" encoding="GBK"?>
<request>
    <functionID>%s</functionID>
    <time>%s</time>
    <OrdersList>
        <OrderInfo>
            <orderID>%s</orderID>
            <logisticsName>%s</logisticsName>
            <logisticsTel>114</logisticsTel>
            <logisticsOrderID>%s</logisticsOrderID>
            <SendGoodsList>
                %s
            </SendGoodsList>
        </OrderInfo>
    </OrdersList>
</request>
EOD;
    $uploadFile = array('sendGoods'=>sprintf($template, $function, $date, $trade->tid, $trade->logistics_code, $trade->logistics_no, $goods));
    

    $retval = $dd->sendByPost('sendGoods.php', $params, $appsecret, $uploadFile);

    logx("dd_sync_logistics retval:" . print_r($retval, true), $sid.'/Logistics');
    
    if(API_RESULT_OK != ddErrorTest($retval,$db,$trade->shop_id))
    {
        set_sync_fail($db, $sid,$trade->rec_id,2, $retval->error_msg);
        logx("{$sid} dd_sync_fail: tid {$trade->tid} logistics_no {$trade->logistics_no} logistics_code {$trade->logistics_code},error:{$retval->error_msg}", $sid.'/Logistics');
        if(isset($retval->errorCode))
        {
            $code_msg=$retval->errorCode;
        }
        return false;
    }
    
    set_sync_succ($db, $sid,$trade->rec_id);
    logx("dd_sync_ok: {$trade->tid}", $sid.'/Logistics');
    
    return true;
}

//当当在线发货
function ddSyncOnlineLogistics(&$db,&$trade,$sid)
{
    getAppSecret($trade, $appkey, $appsecret);
    
    $dd = new DangdangClient(DD_NEW_API_URL);
    $dd->setAppKey($appkey);
    $dd->setAppSecret($appsecret);
    $dd->setMethod('dangdang.order.goods.ddsend');
    $dd->setSession($trade->session);
    $params = array();
    
    $params['o'] = (int)$trade->tid;
    
    $retval = $dd->sendByPost('', $params, $appsecret);

    logx("dd_online_sync_data ".print_r($retval,true),$sid.'/Logistics');
    
    if(API_RESULT_OK != ddErrorTest($retval,$db,$trade->shop_id))
    {
        set_sync_fail($db, $sid,$trade->rec_id,2, $retval->error_msg);
        
        logx("{$sid} dd_online_sync_fail: tid {$trade->tid} ,error:{$retval->error_msg}", $sid.'/Logistics','error');      
        return false;
    }
    
    set_sync_succ($db, $sid,$trade->rec_id);
    logx("dd_online_sync_ok: {$trade->tid}", $sid.'/Logistics');
    
    return true;
    
    
}

?>