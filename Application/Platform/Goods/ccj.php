<?php
require_once(ROOT_DIR . '/Goods/utils.php');
require_once(TOP_SDK_DIR . '/chuchujie/chuchujieClient.php');


function ccjDownloadGoodsList($db, $shop, $mode, $condition, &$new_count, &$chg_count, &$error_msg)
{
    $sid = $shop->sid;
    $shopid = $shop->shop_id;
    $appkey = $shop->key;
    $appsecret = $shop->secret;
    global $ccj_app_config;
    $session = $ccj_app_config['org_name'];
    
    $page_size = 50;
    
    $params = array();
    $ccj=new ChuchujieClient();
    
    $ccj->setApp_key($appkey);
    $ccj->setDirname('/Order/get_goodsinfo_for_key');
    $ccj->setApp_secret($appsecret);
    $ccj->setSession($session);
    
    
    if($mode == 1)//增量下载
    {
        $start_time = $condition;
        $end_time = time();
        
        logx("ccjDownloadGoodsList shopid: $shopid start_time:" . 
            date('Y-m-d H:i:s', $start_time) . " end_time: " . 
            date('Y-m-d H:i:s', $end_time), $sid.'/Goods');
            
        $params['update_time'] = date('Y-m-d H:i:s', $start_time);
        $params['page'] = 0;
        $params['page_size'] = $page_size;
        $params['goods_status'] = 1;//在售商品
    }
    elseif($mode == 2)//商品名称
    {
        $start_time = 0;
        $end_time = 0;
        logx("ccjDownloadGoodsList shopid: $shopid title {$condition}", $sid.'/Goods');
        
        $params['goods_title'] = $condition;
    }
    elseif($mode == 3)//商品id
    {
        $start_time = 0;
        $end_time = 0;
        logx("ccjDownloadGoodsList shopid: $shopid goodsid {$condition}", $sid.'/Goods');
        
        $params['goods_id'] = (int)$condition;
    }
    elseif($mode == 4)
    {
        $time=explode(',',trim($condition));
        $start_time=strtotime($time[0]);
                
        $params['update_time'] = date('Y-m-d H:i:s', $start_time);
        $params['page'] = 0;
        $params['page_size'] = $page_size;
        $params['goods_status'] = 1;//在售商品
        logx("ccjDownloadGoodsList shopid: $shopid start_time:" . 
            date('Y-m-d H:i:s', $start_time) . " end_time: " . 
            date('Y-m-d H:i:s', time()), $sid.'/Goods');     
    }
    else
    {
        logx("ccjDownloadGoodsList shopid: $shopid 不支持", $sid.'/Goods');
    }
    

    $retval=$ccj->execute($params);

    $spec_list = array();
    
    if(API_RESULT_OK != ccjErrorTest($retval, $db, $shopid, $sid))
    {
        $error_msg = $retval->error_msg;
        logx("ccjDownloadGoodsList fail", $sid.'/Goods');
        return false;
    }
    
    if(!isset($retval->info) || count($retval->info) == 0)
    {
        return true;
    }
    
    $items = $retval->info;
    $total_results = intval($retval->total_num);
    
    //总条数
        
    logx("total_results : $total_results ", $sid.'/Goods');
    
    if($total_results <= $page_size)
    {
        if(!loadCcjGoods($sid, $shopid, $db, $items, $spec_list, $new_count, $chg_count, $error_msg))
                return false;
    }
    else
    {
        $total_pages = ceil(floatval($total_results)/$page_size);
        logx("total_page $total_pages", $sid.'/Goods');
        for($i=$total_pages - 1; $i>=0; $i--)
        {
            logx("page $i ", $sid.'/Goods');
            
            $params['page'] = $i;
            $retval=$ccj->execute($params);
            
            if(API_RESULT_OK != ccjErrorTest($retval, $db, $shopid, $sid))
            {
                $error_msg = $retval->error_msg;
                logx("ccjDownloadGoodsList fail2", $sid.'/Goods');
                return false;
            }
            
            $items = $retval->info;
            
            if(!loadCcjGoods($sid, $shopid, $db, $items, $spec_list, $new_count, $chg_count, $error_msg))
                    return false;
        }   
        
        
    }
    
    if($mode == 1)
    {
        $db->execute("INSERT INTO cfg_setting(`key`,`value`) VALUES('goods_sync_shop_{$shopid}','{NOW()}') ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)");
    }
    return true;
}

function loadCcjGoods($sid, $shopid, &$db, &$items, &$spec_list, &$new_count, &$chg_count, &$error_msg)
{

    for($i = 0; $i < count($items); $i++)
    {
        $item = $items[$i];
        
        $spec = array
        (
            'status' => $item->goods_status == 1?1:2,
            'platform_id' => 27,
            'shop_id' => $shopid,
            'goods_id' => $item->goods_id,
            'outer_id' => $item->goods_code,
            'goods_name' => trim($item->goods_title),
            'pic_url' => iconv_substr(@trim($item->goods_img), 0, 255, 'UTF-8'),
            'is_stock_changed' => '1',
            'created' =>date('Y-m-d H:i:s',time())
        );
        
        $skus = $item->sku;
        for($j = 0; $j < count($skus); $j++)
        {
            $sku = $skus[$j];
            $nspec = $spec;
            $nspec['spec_id'] = @$sku->sku_id;
            $nspec['spec_code'] = '';
            $nspec['spec_sku_properties'] = '';
            $nspec['spec_name'] = $sku->value;
            $nspec['spec_outer_id'] = iconv_substr($sku->sku_code, 0, 40, 'UTF-8');
            $nspec['price'] = @$sku->sku_price;
            $nspec['stock_num'] = @$sku->sku_stock;
                        
            $spec_list[] = $nspec;
        }

    }
    
    if(!putGoodsToDb($sid, $db, $spec_list, $new_count, $chg_count, $error_msg))
    {
        return false;
    }
    
    return true;

}

















?>