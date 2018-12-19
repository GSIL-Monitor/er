<?php
require_once(ROOT_DIR . '/Stock/utils.php');
require_once(ROOT_DIR . '/Manager/utils.php');
require_once(TOP_SDK_DIR. '/pdd/pddClient2.php');
require_once(TOP_SDK_DIR. '/pdd/request/GoodsSkuStockUpdateRequest_pdd.php');

function pdd_stock_sync(&$db, &$stock, $sid){
    getAppSecret($stock,$appkey,$appsecret);
    $client = new pddClient2();
    $client->clientId = $appkey;
    $client->clientSecret = $appsecret;
    $client->dataType = 'JSON';
    $client->accessToken = $stock->session;
    $req = new GoodsSkuStockUpdateRequest_pdd();
    //$req->setOuterId($stock->spec_no);
    $req->setSkuId($stock->spec_id);
    $req->setQuantity($stock->syn_stock);
    $retval = $client->execute($req);
    if(API_RESULT_OK != pddErrorTest($retval,$db,$stock->shop_id))
    {
        $error_msg = $retval->error_msg;
        logx("拼多多同步库存失败, 删除该同步记录: spec_id: {$stock->spec_id},SynStock: {$stock->syn_stock} 失败原因:{$error_msg}", $sid.'/Stock');
        syn_log($db, $stock, 0, $error_msg);
        return SYNC_FAIL;
    }
    syn_log($db, $stock, 1, "");
    logx("拼多多同步库存成功, spec_id: {$stock->spec_id}, SynStock: {$stock->syn_stock}", $sid.'/Stock');
    return SYNC_OK;
}