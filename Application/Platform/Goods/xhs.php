<?php
//微店
require_once(ROOT_DIR . '/Goods/utils.php');

require_once(TOP_SDK_DIR . '/xhs/XhsClient.php');

function xhsDownloadGoodsList($db, $shop, $mode, $condition, &$new_count, &$chg_count, &$error_msg)
{
    $sid = $shop->sid;
    $shopid = $shop->shop_id;
    $page_size = 40;

    $xhs = new XhsClient();
    $appkey=$shop->key;
    $appsecret=$shop->secret;
    $url = "/ark/open_api/v1/items";
    $system_param['timestamp'] = time();
    $system_param['app-key'] = $appkey;

    $def_param['status'] = 2;//商品状态(0为编辑中，1为待审核，2为审核通过)
    $def_param['page_no'] = 1;
    $def_param['page_size'] = $page_size;

    switch($mode){
        case 1:
        {   //增量下载
            
            $start_time=$condition;
            $end_time = time();
            $def_param['update_time_from'] = $start_time;
            $def_param['update_time_to'] = $end_time;
          
            logx("xhsDownloadGoodsList shopid: $shopid mode:1", $sid.'/Goods');
            logx("xhsDownloadGoodsList shopid: $shopid start_time:".date ( 'Y-m-d H:i:s', $start_time )." end_time:". date ( 'Y-m-d H:i:s', $end_time ), $sid.'/Goods');
            break;
        }
        case 4:
        {   //时间段
            $condition = explode(',', $condition);
            $start_time = strtotime($condition[0]);
            $end_time = strtotime($condition[1]);
            $now=time();
            if($end_time>=$now)
            {
                $end_time=$now;
            }
            //根据货品更新时间下载
            $def_param['update_time_from'] = $start_time;
            $def_param['update_time_to'] = $end_time;
            logx("xhsDownloadGoodsList shopid: $shopid mode:4", $sid.'/Goods');
            logx("xhsDownloadGoodsList shopid: $shopid start_time:".date ( 'Y-m-d H:i:s', $start_time )." end_time:".date ( 'Y-m-d H:i:s', $end_time ), $sid.'/Goods');
            break;
        }
    }
    
    

    $retval = $xhs->sendByGet($url,$appsecret, $system_param, $def_param);

    if(API_RESULT_OK != xhsErrorTest($retval, $db, $shopid))
    {
        $error_msg = $retval->error_msg;
        logx("xhsDownloadGoodsList xhs->sendByGet 1 error_msg: {$error_msg}", $sid.'/Goods','error');
        return TASK_OK;
    }
    if($retval->data->total == 0)
    {
        logx("xhsDownloadGoodsList count: 0", $sid.'/Goods');
        return true;
    }
    $total_count = $retval->data->total;

    if($total_count <= $page_size)
    {
        $items = $retval->data->hits;

        if(!loadGoodsImpl($db, $shop, $items, $new_count, $chg_count, $error_msg))
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

            $def_param['page_no'] = $i;
            $retval = $xhs->sendByGet($url,$appsecret, $system_param, $def_param);
            if(API_RESULT_OK != xhsErrorTest($retval, $db, $shopid))
            {
                $error_msg = $retval->error_msg;
                logx("xhsDownloadGoodsList xhs->sendByGet 2 error_msg: {$error_msg}", $sid.'/Goods','error');
                return TASK_OK;
            }

            $items = $retval->data->hits;
            
            if(!loadGoodsImpl($db, $shop, $items, $new_count, $chg_count, $error_msg))
            {
                return false;
            }
        }
    }
    if($mode != 4 && $end_time)
    {
        $db->execute("INSERT INTO cfg_setting(`key`,`value`) VALUES('goods_sync_shop_{$shopid}','{$end_time}') ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)");
    }

    return true;
}


function loadGoodsImpl($db, $shop, $item, &$new_count, &$chg_count, &$error_msg)
{
    $spec_list = array();

    foreach ($item as $item_value) {

        if($item_value->item->available == true){

            $status = 1;
        }else{

            $status = 2;
        }
         $spec = array(
            'status' =>$status,
            'platform_id' => 56,
            'shop_id' => $shop->shop_id,
            'goods_id' => trim(@$item_value->item->barcode),              //平台货品id
            'outer_id'  => trim(@$item_value->item->barcode),     
            'cid' => @$item_value->spu->category_id,//平台类目,多个取第一个
            "goods_name"=>iconv_substr(@$item_value->item->name,0,255,'UTF-8'),
            'price' => trim(@$item_value->item->price),       //商品价格
            'pic_url' => trim(@$item_value->item->top_image->link),//默认图片地址
            'stock_num' => @$item_value->item->stock, //库存

            'spec_id' => '',                            //平台skuid
            'spec_sku_properties' => '',                //平台sku属性串
            'spec_code' => '',                          //平台规格码
            'spec_name' => '',                          //平台规格名
            'spec_outer_id' => '',                      //规格商家编码
            'is_stock_changed' => '1',
            'created' =>date('Y-m-d H:i:s',time())
        );       
        $spec_list[] = $spec;
    }
    
    if(count($spec_list)>0 && !putGoodsToDb($shop->sid, $db, $spec_list, $new_count, $chg_count, $error_msg))
    {
        return false;
    }

    return true;
}