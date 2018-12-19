<?php
require_once(ROOT_DIR . '/Goods/utils.php');
require_once(TOP_SDK_DIR . '/pdd/pddClient2.php');
require_once(TOP_SDK_DIR . '/pdd/request/GoodsListGetRequest_pdd.php');
require_once(TOP_SDK_DIR . '/pdd/request/GoodsInfoGetRequest_pdd.php');

function pddDownloadGoodsList(&$db, $shop, $mode, $condition, &$new_count, &$chg_count, &$error_msg)
{
    //设置参数
    $client = new pddClient2();
    $client->clientId = $shop->key;
    $client->clientSecret = $shop->secret;
    $client->dataType = 'JSON';
    $client->accessToken = $shop->session;

    $sid       = $shop->sid;
    $shop_id    = $shop->shop_id;

    $page = 0;
    $pageSize = 40;

    $req = new GoodsListGetRequest_pdd();
    $req->getPage($page);
    $req->setPageSize($pageSize);

    if ($mode == 2) {
        $req->setGoodsName($condition);
    }elseif ($mode == 3) {
        $req->setOuterId($condition);
    }

    //请求数据
    $retval = $client->execute($req);

    if (API_RESULT_OK != pddErrorTest ( $retval, $db, $shop_id )) {
        $error_msg['info'] = $retval->error_msg;
        $error_msg['status'] = 0;
        logx ( "ERROR $sid pddDownloadGoodsList fail errCode: {$retval->error_code}，msg:{$retval->error_msg}", $sid.'/Goods','error' );
        return TASK_OK;
    }
    if ($retval->total_count == 0) {
        logx ( "pddDownloadGoodsList $shop_id count: 0", $sid.'/Goods' );
        return true;
    }

    $data = $retval->goods_list;
    $total = $retval->total_count;
    logx("pddDownloadGoodsList $shop_id count: $total",$sid.'/Goods');

    //不足一页，抓一次
    if ($total <= count($data))
    {
        logx(" 不足一页， 只抓这一次即可", $sid.'/Goods');
        for ($i=0; $i < count($data); $i++) {
            $ret = $data[$i];
            if(!pddDownloadGoodsDetail($db,  $shop, $new_count, $chg_count, $error_msg, $ret->goods_id)){
                return false;
            };
        }
    }
    else
    {//多页，倒序抓取

        $page = ceil($total/$pageSize);
        logx(" 共发现 $page 页 ", $sid.'/Goods');
        for ($i=$page; $i > 0; $i--) {
            logx("共{$page} 页, 当前第 {$i} 页",$sid.'/Goods');

            $req->setPage($i);
            $req->setPageSize($pageSize);
            $retval = $client->execute($req);

            if (API_RESULT_OK != pddErrorTest ( $retval, $db, $shop_id ))
            {
                $error_msg['info'] = $retval->error_msg;
                $error_msg['status'] = 0;
                logx ( "ERROR $sid pddDownloadGoodsList fail errCode: {$retval->error_code}，msg:{$retval->error_msg}", $sid.'/Goods','error' );
                return TASK_OK;
            }

            $data = $retval->goods_list;

            for ($k=0; $k < count($data); $k++) {
                $ret = $data[$k];
                if(!pddDownloadGoodsDetail($db,  $shop, $new_count, $chg_count, $error_msg, $ret->goods_id))
                {
                    return false;
                }
            }
        }
    }
    return true;
}

function pddDownloadGoodsDetail(&$db, $shop, &$new_count, &$chg_count, &$error_msg, &$goods_id)
{
    $shop_id    = $shop->shop_id;
    $sid        = $shop->sid;
    //设置参数
    $client = new pddClient2();
    $client->clientId = $shop->key;
    $client->clientSecret = $shop->secret;
    $client->dataType = 'JSON';
    $client->accessToken = $shop->session;

    $req = new GoodsInfoGetRequest_pdd();
    $req->setGoodsId($goods_id);

    //获取商品详情
    $retval = $client->execute($req);

    if (API_RESULT_OK != pddErrorTest ( $retval, $db, $shop_id )) {
        $error_msg['info'] = $retval->error_msg;
        $error_msg['status'] = 0;
        logx ( "ERROR $sid pddDownloadGoodsDetail fail errCode: {$retval->error_code}，msg:{$retval->error_msg}", $sid.'/Goods','error' );
        return TASK_OK;
    }
    //$new_count++;

    //商品详情数据
    $ret = $retval->goods_info;

    if ($ret->is_onsale == 0) {//下架
        $status = 2;
    }elseif ($ret->is_onsale == 1) {//上架
        $status = 1;
    }
    $spec_list = array();
    $spec = array(
        'status' => $status,
        'platform_id' => 33,
        'goods_id' => $ret->goods_id,//商品id
        'shop_id' => $shop_id,
        'goods_name' => $ret->goods_name,//平台货品名称
        'price' => '0',//平台售价，开启库存同步才准确
        'pic_url' => '',//图片url
        'stock_num' => '0',//平台库存量
        'outer_id' => '',//商家编码
        'spec_id' => '',//平台skuid
        'spec_name'=>'',//规格名称
        'spec_code' => '',
        'spec_outer_id' => '',
        'spec_sku_properties' => '',//平台sku属性串
        'is_stock_changed' => '1',//最后一次库存同步后，库存有没发生变化
        'created' =>date('Y-m-d H:i:s',time())
    );
    //规格
    $skus = $ret->sku_list;
    if(empty($skus))
    {
        $spec['outer_id'] = $ret->goods_id;
        $spec_list[] = $spec;
    }
    else
    {
        foreach($skus as $sku)
        {
            $nspec = $spec;
            $nspec['goods_id'] = $ret->goods_id;
            $nspec['outer_id'] = $ret->goods_id;
            $nspec['spec_id'] = @$sku->sku_id;
            $nspec['spec_outer_id'] = $sku->outer_id;

            $nspec['spec_name'] = $sku->spec;
            $nspec['stock_num'] = $sku->sku_quantity;
            $nspec['price'] = $sku->group_price;
            $nspec['pic_url'] = $sku->sku_img;
            $spec_list[] = $nspec;
        }
    }
    //入库
    if(!putGoodsToDb($sid,$db, $spec_list, $new_count, $chg_count, $error_msg))
    {
        return false;
    }
    return true;
}

