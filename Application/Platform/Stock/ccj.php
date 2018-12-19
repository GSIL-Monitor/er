<?php
require_once(ROOT_DIR . '/Stock/utils.php');
require_once(ROOT_DIR . '/Manager/utils.php');
require_once(TOP_SDK_DIR . '/chuchujie/chuchujieClient.php');

function ccj_stock_syn(&$db,&$stock, $sid)
{
    getAppSecret($stock,$appkey,$appsecret);
    $shopid = $stock->shop_id;
    
    global $ccj_app_config;
            
    $session = $ccj_app_config['org_name']; 
    
    $params = array();
    $ccj=new ChuchujieClient();
    
    $ccj->setApp_key($appkey);
    $ccj->setDirname('/Order/api_edit_stock');
    $ccj->setApp_secret($appsecret);
    $ccj->setSession($session);
    
    $params['goods_id'] = $stock->goods_id;
    $params['sku_id'] = $stock->spec_id;
    $params['sku_stock'] = $stock->syn_stock;
    
    $retval=$ccj->execute($params);
    
    if(API_RESULT_OK != ccjErrorTest($retval, $db, $shopid, $sid))
    {
        $error_msg = $retval->error_msg;
        
        if( strpos(@$retval->error_msg, "调用次数超限") !== FALSE || strpos(@$retval->error_msg, "缺少参数") !== FALSE)
        {
            syn_delay($db, $stock, 3600*12, @$retval->error_msg);
            logx("楚楚街同步库存失败,延时同步 iid: {$stock->goods_id}, OuterID: {$stock->outer_id} ,sku_id: {$stock->spec_id},syn_stock: {$stock->syn_stock} 失败原因: {$error_msg}", $sid.'/Stock');
        
        }
        if(strpos(@$retval->error_msg, "商品属性库存一致，不修改") !== FALSE)
        {
            syn_delete($db,$stock);
            logx("楚楚街同步库存失败,删除库存同步记录 iid: {$stock->goods_id}, OuterID: {$stock->outer_id} ,sku_id: {$stock->spec_id},syn_stock: {$stock->syn_stock} 失败原因: {$error_msg}", $sid.'/Stock');
        }
        else
        {
            logx("楚楚街同步库存失败, iid: {$stock->goods_id}, OuterID: {$stock->outer_id} ,sku_id: {$stock->spec_id},syn_stock: {$stock->syn_stock} 失败原因: {$error_msg}", $sid.'/Stock');
        
        }
        
        syn_log($db, $stock, 0, $error_msg);
    
        return SYNC_FAIL;
    }
        
    syn_log($db, $stock, 1, "");
    
    logx("楚楚街同步库存成功: iid: {$stock->goods_id}, OuterID: {$stock->spec_id}, syn_stock: {$stock->syn_stock}", $sid.'/Stock');
    
    return SYNC_OK;
}