<?php
require_once(ROOT_DIR . '/Goods/utils.php');
require_once(TOP_SDK_DIR . '/youzan/YZTokenClient.php');
//口袋通商品管理
/**
 * 下载库存商品列表
 * @param unknown $db 数据库
 * @param unknown $shop
 * @param unknown $mode
 * @param unknown $condition
 * @param unknown $new_count
 * @param unknown $chg_count
 * @param unknown $error_msg
 * @return boolean
 */
function kdtDownloadGoodsList(&$db, $shop, $mode, $condition, &$new_count, &$chg_count, &$error_msg) {

    $sid       = $shop->sid;
    $shopId    = $shop->shop_id;
    $appkey    = $shop->key;
    $appsecret = $shop->secret;
    $session = $shop->session;

    $client = new YZTokenClient($session);

    $page_size         = 40;
    $new_trade_count   = 0;
    $chg_trade_count   = 0;
    $total_trade_count = 0;
    $method = 'youzan.items.onsale.get';
    $methodVersion = '3.0.0';//要调用的api版本号
    $params = array();
    $params['page_size'] = $page_size;

    switch ($mode) {
        case 1: {
            //增量下载
            logx("kdtDownloadGoodsList shopid: $shopId  mode:1", $sid . "/Goods");
            break;
        }
        case 2: {
            //货品名称
            $params['q'] = $condition;
            logx("kdtDownloadGoodsList shopid: $shopId title：   " . $condition, $sid . "/Goods");
            break;
        }
        case 3: {
            //货品ID
                $method = "youzan.item.get";  //得到单个商品信息
            $condition         = (int)$condition;
            $params['item_id'] = $condition;
            logx("kdtDownloadGoodsList shopid: $shopId  method: $method   goods_id: $condition", $sid . "/Goods");
            break;
        }
    }

    $retval = $client->post($method, $methodVersion, $params);

    if (API_RESULT_OK != kdtErrorTest($retval, $db, $shopId)) {
        if (40010 == @$retval['code'])
        {
            releaseDb($db);
            refreshKdtToken($appkey, $appsecret, $shop);
            $error_msg = $retval['error_msg'];
            return TASK_OK;
        }
        $error_msg = array("status" => 0, "info" => $retval['error_msg']);
        logx("ERROR $sid kdtDownloadGoodsList  kdt->get fail, error_msg: {$error_msg}", $sid . "/Goods",'error');
        return TASK_OK;
    }
    if ($mode == 3) {
        if(!isset($retval['response']['item']) ||  count($retval['response']['item']) == 0 ){
            logx("kdtDownloadGoodsList $shopId count: 0", $sid . "/Goods");
            return true;
        }
        $goods_arr   = array();
        $goods = $retval['response']['item'];
        $goods_arr[] = $goods;
        if(!loadkdtGoods($sid, $shopId, $db,$goods_arr, $spec_list, $new_count, $chg_count, $error_msg)){
            return false;
        }
        return true;
    }

    if(!isset($retval['response']['items']) ||  count($retval['response']['items']) == 0 ){
        logx("kdtDownloadGoodsList $shopId count: 0", $sid . "/Goods");
        return true;
    }

    $goods = $retval['response']['items'];
    $total_results = $retval['response']['count'];

    logx("kdtDownloadGoodsList $shopId count: $total_results", $sid . "/Goods");

    if ($total_results < $page_size) {
        //数据小于等于一页
        $goods_ids = array();
        for ($i = 0; $i < count($goods); $i++) {
            $goods_ids[] = $goods[$i]['item_id'];
        }
        if(count($goods_ids)>0){
            if(!downkdtGoodsDetail($sid, $shop, $db,$goods_ids, $spec_list, $new_count, $chg_count, $error_msg)){
                return false;
            }
        }
    } else {
        //超过一页的数据  超过一页，第一页抓的作废，从最后一页开始抓
        $total_pages = ceil(floatval($total_results) / $page_size);
        for ($i = $total_pages; $i >= 1; $i--) {
            $params['page_no'] = $i;
            $retval = $client->post($method, $methodVersion, $params);
            if (API_RESULT_OK != kdtErrorTest($retval, $db, $shopId)) {
                if (40010 == @$retval['code'])
                {
                    releaseDb($db);
                    refreshKdtToken($appkey, $appsecret, $shop);
                    $error_msg = $retval['error_msg'];
                    return TASK_OK;
                }
                $error_msg = array("status" => 0, "info" => $retval['error_msg']);
                logx("ERROR $sid kdtDownloadGoodsList kdt->get fail, error_msg:  ".$retval['error_msg'], $sid . "/Goods",'error');
                return TASK_OK;
            }
            $goods = $retval['response']['items'];
            $goods_ids = array();
            for ($j = 0; $j < count($goods); $j++) {
                $goods_ids[] = $goods[$j]['item_id'];
            }
            if(count($goods_ids)>0){
                if(!downkdtGoodsDetail($sid, $shop, $db,$goods_ids, $spec_list, $new_count, $chg_count, $error_msg)){
                    return false;
                }
            }
        }

    }
    return true;

}
function downkdtGoodsDetail($sid, $shop, &$db,$goods_ids, &$spec_list, &$new_count, &$chg_count, &$error_msg){
    $shopId=$shop->shop_id;
    $appkey = $shop->key;
    $appsecret = $shop->secret;
    $session = $shop->session;

    $client = new YZTokenClient($session);
    $method = 'youzan.item.get';
    $methodVersion = '3.0.0';//要调用的api版本号
    $params = array();
    $goods_arr = array();
    foreach ($goods_ids as $item_id) {
        $params['item_id'] = $item_id;
        $retval = $client->post($method, $methodVersion, $params);
        if(API_RESULT_OK !=kdtErrorTest($retval, $db, $shopId)){
                if (40010 == @$retval['code'])
                {
                    releaseDb($db);
                    refreshKdtToken($appkey, $appsecret, $shop);
                    $error_msg = $retval['error_msg'];
                    return TASK_OK;
                }
                $error_msg = $retval['error_msg'];

            $error_msg = array("status" => 0, "info" =>$retval['error_msg']);
            logx("ERROR $sid kdtDownloadGoodsList kdt->get fail, error_msg:  ".$retval['error_msg'], $sid . "/Goods",'error');


                return TASK_OK;
            }
        if(!isset($retval['response']['item']) ||  count($retval['response']['item']) == 0 ){
           logx("downkdtGoodsDetail $shopId count: 0", $sid . "/Goods");
           return true;
        }
        $goods = $retval['response']['item'];
        $goods_arr[] = $goods;
    }

	if(!loadkdtGoods($sid, $shopId, $db,$goods_arr, $spec_list, $new_count, $chg_count, $error_msg)){

		return false;
	}

	return true;
}

function loadkdtGoods($sid, $shopId, &$db, $goods_arr, &$spec_list, &$new_count, &$chg_count, &$error_msg) {
    for ($i = 0; $i < count($goods_arr); $i++) {
        $item    = $goods_arr[$i];
        $spec    = array(
            'status'              => 1,  //架上
            'platform_id'         => 17,
            'shop_id'             => $shopId,
            'goods_id' => trim($item['item_id']),
            'cid' => trim($item['cid']),
            'goods_name' => trim($item['title']),
            'price' => bcdiv($item['price'], 100, 2),
            'pic_url' => trim($item['pic_url']),
            'stock_num' => $item['quantity'],

            'spec_id'             => '',
            'spec_sku_properties' => '',
            'spec_code'           => '',
            'spec_name'           => '',
            'outer_id'  => trim(@$item['item_no']),//商家编码
            'spec_outer_id'       => '',  //规格商家编码
            'is_stock_changed'    => '1',
            'created'             => date('Y-m-d H:i:s', time())
        );
        $message = '';
        $log_msg = '';
        if (iconv_strlen($spec['outer_id'], 'UTF-8') > 40) {
            $log_msg          = $spec['outer_id'] . "\t";
            $spec['outer_id'] = iconv_substr($spec['outer_id'], 0, 40, 'UTF-8');
        }

        if(empty($item['skus'])){
            if (!empty($log_msg) && !empty($message)) {
                logx("GOODS_SPEC_NO_EXCEED\t{$log_msg}" . @$spec['goods_name'], $sid . "/Goods");
            }
            $spec_list[] = $spec;
        } else {
            for ($j = 0; $j < count($item['skus']); $j++) {
                $sku = $item['skus'][$j];
                $nspec = $spec;

                $nspec['spec_id'] = @$sku['sku_id'];    //平台skuid
                $nspec['spec_code'] = @$sku['sku_unique_code'];  //平台规格码
                $pn = @json_decode(@$sku['properties_name_json'],true);
                if (is_array($pn) && count($pn) > 0) {
                    foreach ($pn as $k => $v) {
                        $nspec['spec_sku_properties'] .= @$v['kid'] . ":" . @$v['vid'] . ":";
                        $nspec['spec_name'] .= @$v['k'] . ":" . @$v['v'] . "/";
                    }
                    $nspec['spec_sku_properties'] = @rtrim($nspec['spec_sku_properties'], ":"); //平台sku属性串
                    $nspec['spec_name']           = @rtrim($nspec['spec_name'], "/");//平台规格名称
                }
                $nspec['spec_outer_id'] = @$sku['item_no'];
                $nspec['price'] = bcdiv(@$sku['price'], 100, 2);
                $nspec['stock_num'] = @$sku['quantity'];

                if (iconv_strlen($nspec['spec_outer_id'], 'UTF-8') > 40) {
                    logx("GOODS_SPEC_NO_EXCEED\t{$log_msg}{$nspec['spec_outer_id']}\t" . @$spec['goods_name'], $sid . "/Goods");
                    $nspec['spec_outer_id'] = iconv_substr($nspec['spec_outer_id'], 0, 40, 'UTF-8');
                }
                $spec_list[] = $nspec;
            }
        }

    }

    if (!putGoodsToDb($sid, $db, $spec_list, $new_count, $chg_count, $error_msg)) {

        return false;
    }

    return true;
}