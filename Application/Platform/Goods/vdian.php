<?php
//微店
require_once(ROOT_DIR . '/Goods/utils.php');

require_once(TOP_SDK_DIR . '/vdian/vdianClient.php');

function vdianDownloadGoodsList($db, $shop, $mode, $condition, &$new_count, &$chg_count, &$error_msg)
{
    $sid = $shop->sid;
    $shopid = $shop->shop_id;
    $page_size = 40;

    $client = new vdianClient();
    $client->method = 'vdian.item.list.get';
    $client->token = $shop->session;

    $params['page_size'] = $page_size;
    $params['page_num'] = 1;

    switch($mode){
        case 1:
        {   //增量下载
            $star_time = date('Y-m-d H:i:s', $condition);
            $end_time = date('Y-m-d H:i:s', time());
            logx("vdianDownloadGoodsList mode:1 shopid: $shopid start_time:$star_time end_time:$end_time", $sid.'/Goods');

            break;
        }
        case 4:
        {   //时间段
            $condition = explode(',', $condition);
            $star_time = $condition[0];
            $end_time = $condition[1];
            $now=time();
            if($end_time>=$now)
            {
                $end_time=$now;
            }
            logx("vdianDownloadGoodsList  mode:4 shopid: $shopid start_time:$star_time end_time:$end_time", $sid.'/Goods');
        }
    }
    $params['update_start'] = $star_time;
    $params['update_end'] = $end_time;

    $retval = $client->execute($params);

    if(API_RESULT_OK != vdianErrorTest($retval, $db, $shopid))
    {
        $error_msg['info'] = $retval->error_msg;
        $error_msg['status'] = 0;
        if(10013 == $retval->status->status_code||10016 == $retval->status->status_code||10026 == $retval->status->status_code)
        {
            releaseDb($db);
            refreshVdianToken($shop);
            return TASK_OK;
        }
        logx ( "ERROR $sid vdianDownloadGoodsList fail ，msg:{$retval->error_msg}", $sid.'/Goods','error' );



        return TASK_OK;
    }
    if(!isset($retval->result->items) || count($retval->result->items)==0)
    {
        logx("vdianDownloadGoodsList count: 0", $sid.'/Goods');
        return true;
    }
    $total_count = count($retval->result->items);

    if($total_count <= $page_size)
    {
        $items = $retval->result->items;
        $num_iids = array();

        for($j=0; $j<count($items); $j++)
        {
            $num_iids[] = $items[$j]->itemid;
        }

        if(!vdianDownloadGodosDetail($db, $shop, $mode, $num_iids, $new_count, $chg_count, $error_msg))
        {
            return false;
        }
    }
    else
    {
        $total_pages = ceil(floatval($total_count)/$page_size);
        for($i=$total_pages; $i>0; $i--)
        {
            logx("page $i",$sid.'/Goods');

            $params['page_num'] = $i;
            $retval = $client->execute($params);
            if(API_RESULT_OK != vdianErrorTest($retval, $db, $shopid))
            {
                $error_msg['info'] = $retval->error_msg;
                $error_msg['status'] = 0;
                if(10013 == $retval->status->status_code||10016 == $retval->status->status_code||10026 == $retval->status->status_code)
                {
                    releaseDb($db);
                    refreshVdianToken($shop);
                    return TASK_OK;
                }
                logx ( "ERROR $sid vdianDownloadGoodsList fail ,msg:{$retval->error_msg}", $sid.'/Goods','error' );

                return TASK_OK;
            }

            $items = $retval->result->items;
            $num_iids = array();

            for($j=0; $j<count($items); $j++)
            {
                $num_iids[] = $items[$j]->itemid;
            }

            if(!vdianDownloadGodosDetail($db, $shop, $mode, $num_iids, $new_count, $chg_count, $error_msg))
            {
                return false;
            }
        }
    }
    if($mode != 4 && $end_time)
    {
        $end_time = strtotime($end_time);
        $db->execute("INSERT INTO cfg_setting(`key`,`value`) VALUES('goods_sync_shop_{$shopid}','{$end_time}') ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)");
    }

    return true;
}

function vdianDownloadGodosDetail($db, $shop, $mode, $condition, &$new_count, &$chg_count, &$error_msg)
{
    $sid = $shop->sid;
    $shopid = $shop->shop_id;

    $client = new vdianClient();
    $client->method = 'vdian.item.get';
    $client->token = $shop->session;

    if($mode == 3)
        $item[0] = $condition;
    else
        $item = $condition;

    $res = array();
    for($i=0; $i<count($item); $i++)
    {
        $params['itemid'] = $item[$i];
        $retval = $client->execute($params);

        if(API_RESULT_OK != vdianErrorTest($retval, $db, $shopid))
        {
            $error_msg['info'] = $retval->error_msg;
            $error_msg['status'] = 0;
            if(10013 == $retval->status->status_code||10016 == $retval->status->status_code||10026 == $retval->status->status_code)
            {
                releaseDb($db);
                refreshVdianToken($shop);
                return TASK_OK;
            }
            logx ( "ERROR $sid vdianDownloadGoodsDetail fail ,msg:{$retval->error_msg}", $sid.'/Goods','error' );

            return TASK_OK;
        }

        $res[0] = $retval->result;
        if(!loadGoodsImpl($db, $shop, $res, $new_count, $chg_count, $error_msg))
        {
            return false;
        }
    }

    return true;
}

function loadGoodsImpl($db, $shop, $items, &$new_count, &$chg_count, &$error_msg)
{
    for($i=0; $i<count($items); $i++)
    {
        $item = $items[$i];
        if($item->status == 'delete')$status = 0;
        else if($item->status == 'onsale')$status = 1;
        else if($item->status == 'instock')$status = 2;
        $spec = array(
            'status' =>$status,
            'platform_id' => 32,
            'shop_id' => $shop->shop_id,
            'goods_id' => trim(@$item->itemid),              //平台货品id
            'outer_id'  => trim(iconv_substr(@$item->merchant_code,0,40,'UTF-8')), //商家编码
            'cid' => trim(@$item->cates->cate_id),           //平台类目
            //货品名,微店的商品名称可能出现回车，需要进行替换
            "goods_name"=>iconv_substr(@(str_replace(array("\r\n", "\r", "\n"), " ", valid_utf8($item->item_name))),0,255,'UTF-8'),
            'price' => trim(floatval(@$item->price)),       //商品价格
            'pic_url' => trim(@$item->imgs[0]),                 //默认图片地址
            'stock_num' => trim(floatval(@$item->stock)),    //库存

            'spec_id' => '',                            //平台skuid
            'spec_sku_properties' => '',                //平台sku属性串
            'spec_code' => '',                          //平台规格码
            'spec_name' => '',                          //平台规格名
            'spec_outer_id' => '',                      //规格商家编码
            'is_stock_changed' => '1',
            'created' =>date('Y-m-d H:i:s',time())
        );
        if(empty($item->skus)){
            $spec_list[] = $spec;
        }else{
            for ($j = 0; $j < count($item->skus); $j++) {
                $sku = $item->skus[$j];
                $nspec = $spec;
                $nspec['spec_name'] = trim(@$sku->title);  //商品名称
                $nspec['spec_id'] = trim(@$sku->id);  //平台skuid
                $nspec['spec_code'] = trim(@$sku->sku_merchant_code);   //平台规格码
                $nspec['spec_outer_id'] = iconv_substr(trim(@$sku->sku_merchant_code), 0,40,'UTF-8');    //规格商家编码
                $nspec['price'] = trim(@$sku->price);
                $nspec['stock_num'] = trim(@$sku->stock);
                $spec_list[] = $nspec;
            }
        }
    }
    if(count($spec_list)>0 && !putGoodsToDb($shop->sid, $db, $spec_list, $new_count, $chg_count, $error_msg))
    {
        return false;
    }

    return true;
}