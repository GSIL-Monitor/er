<?php
require_once(ROOT_DIR . '/Stock/utils.php');
require_once(ROOT_DIR . '/Manager/utils.php');
require_once(TOP_SDK_DIR . '/jpw/JpwClient.php');


function jpw_stock_syn(&$db, &$stock, $sid)
{
    getAppSecret($stock, $appkey, $appsecret);
    $shopid = $stock->shop_id;
    $token = $db->query_result("select app_key,refresh_token,re_expire_time from cfg_shop where shop_id = {$shopid}");
    $jpw = new jpwClient();
    $jpw->secret = $appsecret;
    $params = array(
        'jType' => "update_goods_inventory",
        'jCusKey' => $stock->session,
        'token' => $token['refresh_token'],
        'goodsSkuId' => $stock->spec_id,//商品编号
        'goodsStock' => $stock->syn_stock,
        'setType' => 1
    );

    $retval = $jpw->execute($params);

    if(API_RESULT_OK != jpwErrorTest($retval, $db, $shopid))
    {
        if ( 10004 == intval(@$retval->info) || 10040 == intval(@$retval->info) || 10042 == intval(@$retval->info) || 10001 == intval(@$retval->info))
        {
            releaseDb($db);
            refreshJpwToken($appkey, $appsecret, $stock);
            $error_msg = $retval->error_msg;
            return TASK_OK;
        }

        $error_msg = $retval->error_msg;
        
        if ( 10020 == intval(@$retval->info) || 10025 == intval(@$retval->info) || 10022 == intval(@$retval->info) || 10021 == intval(@$retval->info))
        {
            //减少库存数大于实时库存数
            //商品已经下架
            //服务化库存设置错误
            syn_disable($db, $stock, $retval->error_msg);
            logx("卷皮网同步库存失败,停止同步: goods_id: {$stock->goods_id}, spec_id: {$stock->spec_id}, SynStock: {$stock->syn_stock} 失败原因:{$retval->error_msg}", $sid.'/Stock');
        }

        if (10023 == intval(@$retval->info) || 10024 == intval(@$retval->info) || 10033 == intval(@$retval->info) || mb_strpos('sku修改后库存必须',$error_msg)) {
            //入库商品不能修改库存
            //用户与商品所属商家不一致
            //该商品不能被erp同步库存
            syn_delete($db, $stock);
            logx("卷皮网同步库存失败,删除同步记录: goods_id: {$stock->goods_id}, spec_id: {$stock->spec_id}, SynStock: {$stock->syn_stock} 失败原因:{$retval->error_msg}---------{$retval->info}", $sid.'/Stock');
        }

        logx("卷皮网库存同步失败 goods_id: {$stock->goods_id}, spec_id: {$stock->spec_id}, SynStock: {$stock->syn_stock} 失败原因: {$error_msg}---------{$retval->info}", $sid.'/Stock');
        syn_log($db, $stock, 0, $error_msg);
        return SYNC_FAIL;
    }
    syn_log($db, $stock, 1, "");
    logx("卷皮网同步库存成功, goods_id: {$stock->goods_id}, spec_id: {$stock->spec_id}, SynStock: {$stock->syn_stock}",$sid.'/Stock');
    return SYNC_OK;
}