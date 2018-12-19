<?php
//微店
require_once(ROOT_DIR . "/Stock/utils.php");
require_once(ROOT_DIR . "/Manager/utils.php");
require_once(TOP_SDK_DIR . "/vdian/vdianClient.php");


//库存同步
function vdian_stock_sync(&$db, &$stock, $sid)
{
    //getAppSecret($stock, $appkey, $appsecret);
    $sid = $stock->sid;

    $client = new vdianClient();
    $client->method = 'vdian.item.stock.update';
    $client->token = $stock->session;

    $params['type'] = 'set';
    $items['item_id'] = $stock->goods_id;//商品编号
    $items['stock'] = $stock->syn_stock;
    if(empty($stock->spec_id)){//单规格
        $items['item_sku_id'] = '';
    }else{
        $items['item_sku_id'] = $stock->spec_id;
    }
    $params['items'][] = $items;
    $retval = $client->execute($params);

    if(API_RESULT_OK != vdianErrorTest($retval, $db, $stock->shop_id))
    {
        $error_msg = $retval->error_msg;
        if(10013 == $retval->status->status_code||10016 == $retval->status->status_code||10026 == $retval->status->status_code)
        {
            releaseDb($db);
            refreshVdianToken($stock);
            return SYNC_FAIL;
        }

        if (strpos($error_msg, "商品sku的id不存在") !== FALSE)
        {
            syn_delete($db, $stock);
            logx("同步库存失败, 删除该同步记录: NumIID: {$stock->goods_id}, SkuID: {$stock->spec_id}, SynStock: {$stock->syn_stock} 失败原因:{$error_msg}", $sid.'/Stock');
        }
        else
        {
            syn_log($db, $stock, 0, $error_msg);
            logx("微店库存同步失败 itemid:{$stock->goods_id} sku->id:{$stock->spec_id} sku->stock:{$stock->syn_stock} 失败原因:{$error_msg}", $sid.'/Stock');
        }
        
        return SYNC_FAIL;
    }

    syn_log($db, $stock, 1, '');
    logx("微店库存同步成功 itemid:{$stock->goods_id} sku->id:{$stock->spec_id} sku->stock:{$stock->syn_stock}", $sid.'/Stock');

    return SYNC_OK;
}