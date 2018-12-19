<?php
require_once(ROOT_DIR . '/Stock/utils.php');
require_once(ROOT_DIR . '/Manager/utils.php');
require_once(TOP_SDK_DIR . '/jos/JdClient.php');
require_once(TOP_SDK_DIR . '/jos/JdException.php');
require_once(TOP_SDK_DIR . '/jos/JosRequest.php');
include_once(TOP_SDK_DIR . '/jos/request/ware/WareSkuStockUpdateRequest.php');
include_once(TOP_SDK_DIR . '/jos/request/ware/WareUpdateListingRequest.php');
include_once(TOP_SDK_DIR . '/jos/request/ware/WareUpdateDelistingRequest.php');

function jos_update_listing(&$db, &$stock, $sid, &$jos) {
    $req = new WareUpdateListingRequest();
    $req->setWareIds($stock->goods_id);
    $req->setTradeNo($stock->rec_id);
    $retval = $jos->execute($req);
    logx(print_r($retval, true), $sid . "/Stock");
    if (API_RESULT_OK != josErrorTest($retval, $db, $stock->shop_id)) {
        logx("上架失败, 失败原因: {$retval->error_msg},goods_id: {$stock->goods_id}", $sid . "/Stock");
        return SYNC_FAIL;
    }
    logx("jos_update_listing 上架操作成功，上架数量为{$stock->syn_stock},goods_id: {$stock->goods_id}", $sid . "/Stock");
    top_sale_status($db, $stock->rec_id, 1);
}

function jos_update_delisting(&$db, &$stock, $sid) {
    $req = new WareUpdateDelistingRequest();
    $req->setWareIds($stock->goods_id);
    $req->setTradeNo($stock->rec_id);
    $retval = $jos->execute($req);


    if (API_RESULT_OK != josErrorTest($retval, $db, $stock->shop_id)) {
        logx("下架失败, 失败原因: {$retval->error_msg}", $sid . "/Stock");
        return SYNC_FAIL;
    }
    logx("jos_update_delisting 下架操作成功，下架数量为{$stock->syn_stock}", $sid . "/Stock");

}

function jos_stock_syn(&$db, &$stock, $sid,&$fail) {
    getAppSecret($stock, $appkey, $appsecret);
    $jos = new JdClient();
    $jos->appKey = $appkey;
    $jos->appSecret = $appsecret;
    $jos->accessToken = $stock->session;

    $req = new WareSkuStockUpdateRequest();
    $req->setOuterId($stock->spec_outer_id);
    $req->setSkuId($stock->spec_id);
    $req->setQuantity($stock->syn_stock);

    $retval = $jos->execute($req);
    if (API_RESULT_OK != josErrorTest($retval, $db, $stock->shop_id)) {
        if (@$retval->en_desc == "sku already deleted") {
            //对于这些错误, 不需再次同步, 可将其从match表中删掉
            syn_delete($db, $stock);
            logx("京东同步库存失败, 删除该同步记录: NumIID: {$stock->goods_id}, SkuID: {$stock->spec_id}, SynStock: {$stock->syn_stock} 失败原因: {$retval->error_msg}", $sid . "/Stock");
        } else {
            syn_log($db, $stock, 0, $retval->error_msg);
            logx("京东同步库存失败, NumIID: {$stock->goods_id}, SkuID: {$stock->spec_id}, SynStock: {$stock->syn_stock} 失败原因: " . print_r($retval, true), $sid . "/Stock");
            $fail[]   = array("rec_id" => @$stock->rec_id, "goods_id" => @$stock->goods_id, "spec_id" => @$stock->spec_id, "msg" => "{$retval->error_msg}");
        }
        return SYNC_FAIL;
    }


    if ($stock->syn_stock < 1)//货品下架
    {
        //jos_update_delisting($db,$stock, $sid);
        top_sale_status($db, $stock->rec_id, 2);
        syn_log($db, $stock, 3, "");
    }else if ($stock->syn_stock >= $stock->stock_syn_min && $stock->stock_syn_min >= 0 && $stock->syn_stock > 0
        && $stock->is_auto_listing == 1 && $stock->status == 2
    )
    {
        //货品上架
        //超过最小库存且设置了自动上架
        //status状态在platstock.php中维护

        jos_update_listing($db, $stock, $sid, $jos);
        syn_log($db, $stock, 2, "");
    }else{
        syn_log($db, $stock, 1, "");
    }
    logx("京东同步库存成功: NumIID: {$stock->goods_id}, SkuID: {$stock->spec_id}, SynStock: {$stock->syn_stock}", $sid . "/Stock");

    return SYNC_OK;
}

?>
