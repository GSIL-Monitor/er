<?php
require_once(ROOT_DIR . '/Goods/utils.php');
require_once(ROOT_DIR . '/Common/api_error.php');
require_once(TOP_SDK_DIR . '/wm/newWmClient.php');

//微盟旺店商品管理


//下载库存商品列表
function weimoDownloadGoodsList(&$db, $shop, $mode, $condition, &$new_count, &$chg_count, &$error_msg)
{

    $sid=$shop->sid;
    $shopId=$shop->shop_id;
    $appkey = $shop->key;
    $appsecret = $shop->secret;
    $session = $shop->session;
    $client = new WmClient();
    $client->setSession($session);
    if(!weimoDownloadGoodsListImpl($db, $sid, $shopId, $appkey,$appsecret, $mode, $condition,$client,$new_count, $chg_count, $error_msg))
    {
        return false;
    }
    return true;
}
function weimoDownloadGoodsListImpl(&$db, $sid, $shopId, $appkey,$appsecret, $mode, $condition,&$client,&$new_count, &$chg_count, &$error_msg){

    $page_on = 1;
    $page_size = 40;
    $new_trade_count = 0;
    $chg_trade_count = 0;
    $total_trade_count = 0;
    //$method = "spuGet";  //获取商品列表  默认
    $method = 'wangpu/Spu/FullInfoGet';
    $params = array
    (
        'page_size'=>$page_size,
        'page_no' => $page_on,
        'is_onsale' => 2,  //   下架=0，上架=1，所有=2
    );

    switch ($mode)
    {
        case 1:
        {
            //增量下载
            logx("weimoDownloadGoodsList shopid: $shopId  mode:1", $sid.'/Goods');
            break;
        }
    }
    //$retval = $client ->goodsDetail($params,$method);
    $retval = $client->execute($method, $params);
    if(API_RESULT_OK !=weimoErrorTest($retval, $db, $shopId))
    {
        $error_msg['info'] = $retval->error_msg;
        $error_msg['status'] = 0;
        logx("weimoDownloadGoodsList weimo->goodsDetail fail, error_msg: {$retval->$error_msg}", $sid.'/Goods');
        logx("ERROR $sid weimoDownloadGoodsList   {$retval->$error_msg}" ,$sid.'/Goods','error');
        return TASK_OK;
    }

    if(!isset($retval->data->page_data) ||  count($retval->data->page_data) == 0 )
    {
        logx("weimoDownloadGoodsList $shopId count: 0", $sid.'/Goods');
        return true;
    }

    $goods = $retval->data->page_data;
    $total_results = intval(@$retval->data->row_count);

    logx("weimoDownloadGoodsList $shopId count: $total_results", $sid.'/Goods');

    if($total_results <= $page_size){
        //数据小于等于一页
        $goods_arr = array();
        for ($i = 0; $i < count($goods); $i++) {
            $goods_arr[] = $goods[$i];
        }
        if(count($goods_arr)>0){
            if(!downweimoGoodsDetail($sid, $shopId, $db,$goods_arr, $spec_list, $new_count, $chg_count, $error_msg)){
                return false;
            }
        }
    }else {
        //超过一页的数据  超过一页，第一页抓的作废，从最后一页开始抓
        $total_pages = ceil(floatval($total_results)/$page_size);
        for($i =$total_pages; $i >= 1; $i--){
            $params['page_no'] = $i;
            //$retval = $client ->goodsDetail($params,$method);
            $retval = $client->execute($method, $params);
            logx("weimoDownloadGoodsList go to $i",$sid.'/Goods');
            if(API_RESULT_OK != weimoErrorTest($retval, $db, $shopId)){
                $error_msg['info'] = $retval->error_msg;
                $error_msg['status'] = 0;
                logx("weimoDownloadGoodsList weimo->goodsDetail fail, error_msg: {$retval->$error_msg}", $sid.'/Goods');
                logx("ERROR $sid weimoDownloadGoodsList   {$retval->$error_msg}" ,$sid.'/Goods','error');
                return TASK_OK;
            }
            $goods = $retval->data->page_data;
            $goods_arr = array();
            for ($j = 0; $j < count($goods) ; $j++) {
                $goods_arr[] = $goods[$j];
            }
            if(count($goods_arr)>0){
                if(!downweimoGoodsDetail($sid, $shopId, $db,$goods_arr, $spec_list, $new_count, $chg_count, $error_msg)){
                    return false;
                }
            }
        }

    }
    return true;

}
function downweimoGoodsDetail($sid, $shopId, &$db,$goods_arr, &$spec_list, &$new_count, &$chg_count, &$error_msg){
    for ($i = 0; $i < count($goods_arr); $i++) {
        $goods = $goods_arr[$i];
        if(isset($goods->spu) && !empty($goods->spu))
        {
            $item = $goods->spu;
        }else{
            $item = $goods;
        }

        $spec = array(
            'status' =>($item->is_onsale)?1:2,
            'platform_id' => 28,
            'shop_id' => $shopId,
            'goods_id' => trim($item->spu_id),          //平台货品id
            'cid' => trim($item->category_id),          //平台类目
            'goods_name' => trim($item->spu_name),      //商品标题
            'price' => trim($item->low_sellprice),      //商品价格
            'pic_url' => trim($item->default_img),      //默认图片地址
            'stock_num' => $item->inventory,            //库存

            'spec_id' => '',                            //平台skuid
            'spec_sku_properties' => '',                //平台sku属性串
            'spec_code' => "",                          //平台规格码
            'spec_name' => '',                          //平台规格名
            'outer_id'  => "",                          //商家编码
            'spec_outer_id' => '',                      //规格商家编码
            'is_stock_changed' => '1',
            'created' =>date('Y-m-d H:i:s',time())
        );
        if(empty($goods->skus)){
            $spec_list[]=$spec;
        }else{
            for ($j = 0; $j < count($goods->skus); $j++) {
                $sku = $goods->skus[$j];
                $nspec = $spec;
                $nspec['status'] = (@$sku->is_onsale)?1:2;
                $nspec['goods_name'] = $sku->sku_name; //商品名称
                $nspec['spec_id'] = @$sku->sku_id;    //平台skuid
                $nspec['outer_id'] = @$item->spu_code; //商品编码
                $nspec['spec_outer_id'] = @$sku->sku_code;
                $nspec['price'] = @$sku->sale_price;
                $nspec['stock_num'] = @$sku->inventory;
                //$nspec['spec_code'] = @$sku->spu_code ;  //平台规格码
                if(!empty($sku->sku_attrs)){
                    for ($ii = 0; $ii < count($sku->sku_attrs->sku); $ii++) {
                        $nspec['spec_sku_properties'] .= $sku->sku_attrs->sku[$ii]->id.":".$sku->sku_attrs->skuval[$ii]->key.':';
                        $nspec['spec_name'] .= $sku->sku_attrs->sku[$ii]->name.":".$sku->sku_attrs->skuval[$ii]->val.':';
                    }
                    $nspec['spec_sku_properties'] = rtrim($nspec['spec_sku_properties'],":");
                    $nspec['spec_name'] = rtrim($nspec['spec_name'],":");
                }
                $spec_list[] = $nspec;
            }
        }

    }

    if(!putGoodsToDb($sid, $db, $spec_list, $new_count, $chg_count, $error_msg)){

        return false;
    }

    return true;
}