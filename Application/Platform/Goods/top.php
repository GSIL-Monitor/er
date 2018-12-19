<?php
require_once(ROOT_DIR . '/Goods/utils.php');
require_once(TOP_SDK_DIR . '/top/Logger.php');
require_once(TOP_SDK_DIR . '/top/RequestCheckUtil.php');
require_once(TOP_SDK_DIR . '/top/TopClient.php');
require_once(TOP_SDK_DIR . '/top/request/ItemsListGetRequest.php');
require_once(TOP_SDK_DIR . '/top/request/ItemsOnsaleGetRequest.php');
require_once(TOP_SDK_DIR . '/top/request/ItemsInventoryGetRequest.php');
require_once(TOP_SDK_DIR . '/top/request/ItemSkuUpdateRequest.php');
require_once(TOP_SDK_DIR . '/top/request/ItemUpdateRequest.php');
require_once(TOP_SDK_DIR . '/top/request/ItemGetRequest.php');
require_once(TOP_SDK_DIR . '/top/request/TmallItemOuteridUpdateRequest.php');

function downTopGoodsDetail($sid,
                            $shopId,
                            &$db,
                            &$top,
                            $session,
                            $mode,
                            $num_iids,
                            &$spec_list,
                            &$new_count,
                            &$chg_count,
                            &$error_msg) {
    $req = new ItemsListGetRequest();

    $req->setFields('num_iid,title,num,sku,is_fenxiao,auction_point,outer_id,pic_url,property_alias,props,price,list_time,delist_time,approve_status,prop_img,cid,sub_stock');
    $req->setNumIids($num_iids);
    $retval = $top->execute($req, $session);
    if (API_RESULT_OK != topErrorTest($retval, $db, $shopId)) {
        logx('req detail error: '.print_r($req,true),$sid . "/Goods");
        logx('retval detail error: '.print_r($retval,true),$sid . "/Goods");
        $error_msg["status"] = 0;
        $error_msg["info"] = $retval->error_msg;
        return false;
    }
    //货品状态初始化
    if ($mode == 2)
        $db->execute("update api_goods_spec set status = 0 where shop_id = {$shopId} and goods_id in ({$num_iids})");

    $items = &$retval->items->item;
    $db_version = (int)getSysCfg($db, 'sys_db_version', 0);
    for ($i = 0; $i < count($items); $i++) {
        $item = $items[ $i ];
        $item->db_version = $db_version;
        if (!loadGoodsDetailImpl($shopId,$sid, $item, $spec_list))
            continue;
    }

    //超过100条写一次库
    if (count($spec_list) >= 100 && !putGoodsToDb($sid, $db, $spec_list, $new_count, $chg_count, $error_msg)) {
        return false;
    }

    return true;
}

function topDownloadGoodsListImpl($db,
                                  $sid,
                                  $shopId,
                                  $session,
                                  $mode,
                                  $condition,
                                  &$top,
                                  &$req,
                                  &$new_count,
                                  &$chg_count,
                                  &$error_msg) {
    switch ($mode) {
        case 1: {
            $start_time = $condition;
            $end_time = time();

            $req->setStartModified(date('Y-m-d H:i:s', $start_time));
            $req->setEndModified(date('Y-m-d H:i:s', $end_time));
            $req->setPageNo(1);
            $req->setPageSize(200);
            break;
        }
        case 2: {
            $start_time = 0;
            $end_time = 0;

            $req->setQ($condition);
            $req->setPageNo(1);
            $req->setPageSize(200);
            break;
        }
        case 3: {
            $start_time = 0;
            $end_time = 0;
            $req->setNumIid($condition);
            break;
        }
    }

    $req->setFields('num_iid');


    $spec_list = array();

    $result = splitTime($start_time, $end_time, 3600 * 24, function ($from_time, $to_time) use (&$db, &$req, &$top, &$error_msg, &$spec_list, $session, $sid, $shopId, $mode, &$new_count, &$chg_count) {
        if ($from_time && $to_time) {
            $req->setStartModified(date('Y-m-d H:i:s', $from_time));
            $req->setEndModified(date('Y-m-d H:i:s', $to_time));
        }

        $retval = $top->execute($req, $session);
        if (API_RESULT_OK != topErrorTest($retval, $db, $shopId)) {
            $error_msg["status"] = 0;
            $error_msg["info"] = $retval->error_msg;
            logx("$sid topDownloadGoodsList top->execute fail", $sid . "/Goods",'error');
            return false;
        }
        if ($mode == 1 || $mode == 2) {
            if (!isset($retval->items) || count($retval->items) == 0) {
                return true;
            }

            $items = $retval->items->item;
            $total_results = intval($retval->total_results);
        } else if ($mode == 3) {
            $items = array(0 => $retval->item);
            $total_results = 1;
        }


        //总条数

        logx("total_results : $total_results ", $sid . "/Goods");

        //如果不足一页，则不需要再抓了
        if ($total_results <= count($items)) {
            $num_iids = array();
            for ($j = 0; $j < count($items); $j++) {
                if(!empty($items[ $j ]->num_iid) && $items[ $j ]->num_iid!='') {
                    $num_iids[] = $items[$j]->num_iid;
                }
                if($j>0 && $j % 20==19){
                    if (!downTopGoodsDetail($sid, $shopId, $db, $top, $session, $mode, implode(',', $num_iids), $spec_list, $new_count, $chg_count, $error_msg))
                        return false;
                    unset($num_iids);
                }
            }
            if(!empty($num_iids)){
                if (!downTopGoodsDetail($sid, $shopId, $db, $top, $session, $mode, implode(',', $num_iids), $spec_list, $new_count, $chg_count, $error_msg))
                    return false;
            }

        } else //超过一页，第一页抓的作废，从最后一页开始抓
        {
            $total_pages = ceil(floatval($total_results) / 200);
            logx("total_page $total_pages", $sid . "/Goods");
            for ($i = $total_pages; $i >= 1; $i--) {
                logx("page $i ", $sid . "/Goods");
                $req->setPageNo($i);
                $retval = $top->execute($req, $session);
                if (API_RESULT_OK != topErrorTest($retval, $db, $shopId)) {
                    logx('req error: '.print_r($req,true),$sid . "/Goods");
                    logx('retval error: '.print_r($retval,true),$sid . "/Goods");
                    $error_msg["status"] = 0;
                    $error_msg["info"] = $retval->error_msg;
                    logx("$sid topDownloadGoodsList top->execute fail2", $sid . "/Goods");
                    return false;
                }

                $items = $retval->items->item;
                $num_iids = array();
                for ($j = 0; $j < count($items); $j++) {
                    if(!empty($items[ $j ]->num_iid) && $items[ $j ]->num_iid!=''){
                        $num_iids[] = $items[ $j ]->num_iid;
                    }
                    if($j>0 && $j % 20==19){
                        if (!downTopGoodsDetail($sid, $shopId, $db, $top, $session, $mode, implode(',', $num_iids), $spec_list, $new_count, $chg_count, $error_msg))
                            return false;
                        unset($num_iids);
                    }
                }
                if(!empty($num_iids)){
                    if (!downTopGoodsDetail($sid, $shopId, $db, $top, $session, $mode, implode(',', $num_iids), $spec_list, $new_count, $chg_count, $error_msg))
                        return false;
                }
            }
        }

        if ($to_time || count($spec_list) == 0) {
            $db->execute("INSERT INTO cfg_setting(`key`,`value`) VALUES('goods_sync_shop_{$shopId}','{$to_time}') ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)");
        }

        return true;
    });

    //保存数据
    if ((count($spec_list) == 0 || putGoodsToDb($sid, $db, $spec_list, $new_count, $chg_count, $error_msg)) &&
        $result &&
        $end_time
    ) {
        $db->execute("INSERT INTO cfg_setting(`key`,`value`) VALUES('goods_sync_shop_{$shopId}','{$end_time}') ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)");
    }

    return $result;
}

//taobao下载货品列表
function topDownloadGoodsList($db, $shop, $mode, $condition, &$new_count, &$chg_count, &$error_msg) {
    $sid = $shop->sid;
    $shopId = $shop->shop_id;

    if ($mode == 1) {
        $start_time = $condition;
        $end_time = time();

        logx("topDownloadGoodsList shopid: $shopId start_time:" .
             date('Y-m-d H:i:s', $start_time) . " end_time: " .
             date('Y-m-d H:i:s', $end_time), $sid . "/Goods");
    } else {
        $start_time = 0;
        $end_time = 0;
        logx("topDownloadGoodsList shopid: $shopId title {$condition}", $sid . "/Goods");
    }

    //taobao
    $top = new TopClient();
    $top->format = 'json';
    $top->appkey = $shop->key;
    $top->secretKey = $shop->secret;

    if ($mode == 1 || $mode == 2) {
        $req = new ItemsOnsaleGetRequest();

        if (!topDownloadGoodsListImpl($db,
                                      $sid,
                                      $shopId,
                                      $shop->session,
                                      $mode,
                                      $condition,
                                      $top,
                                      $req,
                                      $new_count,
                                      $chg_count,
                                      $error_msg)
        ) {
            return false;
        }

        $req = new ItemsInventoryGetRequest();
        if (!topDownloadGoodsListImpl($db,
                                      $sid,
                                      $shopId,
                                      $shop->session,
                                      $mode,
                                      $condition,
                                      $top,
                                      $req,
                                      $new_count,
                                      $chg_count,
                                      $error_msg)
        ) {
            return false;
        }

        $req->setBanner("sold_out");
        if (!topDownloadGoodsListImpl($db,
                                      $sid,
                                      $shopId,
                                      $shop->session,
                                      $mode,
                                      $condition,
                                      $top,
                                      $req,
                                      $new_count,
                                      $chg_count,
                                      $error_msg)
        ) {
            return false;
        }

    } else if ($mode == 3) {
        $req = new ItemGetRequest();
        if (!topDownloadGoodsListImpl($db,
                                      $sid,
                                      $shopId,
                                      $shop->session,
                                      $mode,
                                      $condition,
                                      $top,
                                      $req,
                                      $new_count,
                                      $chg_count,
                                      $error_msg)
        ) {
            return false;
        }
    }

    return true;
}

//取出淘宝规格的别名
//sku_properties: '1627207:3232483;20509:28315';
//property_alias: '1627207:3232483:蝴蝶兰;1627207:3232484:抹茶绿'
//sku_properties_name:  '1627207:3232483:颜色分类:军绿色;20509:28315:尺码:M'
//返回值示例： 蝴蝶兰M
function analysisAlias($sku_properties, $property_alias, $sku_properties_name) {
    $sku_properties_arr = explode(';', $sku_properties);
    $sku_properties_name_arr = explode(';', $sku_properties_name);
    $property_alias_arr = explode(';', $property_alias);

    $result = "";
    foreach ($sku_properties_arr as $sku_properties_item) {
        //1627207:3232483
        $find_alias = false;
        //先找别名
        foreach ($property_alias_arr as $property_alias_item) {
            //1627207:3232483:蝴蝶兰
            if (strpos($property_alias_item, $sku_properties_item) !== false) {
                $result .= substr($property_alias_item, strrpos($property_alias_item, ":") + 1);
                $find_alias = true;
                break;
            }
        }
        if (!$find_alias) {
            //再找默认名称
            foreach ($sku_properties_name_arr as $sku_properties_name_item) {
                //1627207:3232483:颜色分类:军绿色
                if (strpos($sku_properties_name_item, $sku_properties_item) !== false) {
                    $result .= substr($sku_properties_name_item, strrpos($sku_properties_name_item, ":") + 1);
                    break;
                }
            }
        }
    }

    return trim($result);
}


function loadGoodsDetailImpl($shopId,$sid, &$item, &$spec_list, $is_deleted = 0) {
    $outer_id = trim(@$item->outer_id);
    if (iconv_strlen($outer_id, 'UTF-8') > 40) {
        logx("GOODS_NO_EXCEED\t{$outer_id}\t{$item->title}",$sid. "/Goods");
        $outer_id = iconv_substr($outer_id, 0, 40, 'UTF-8');
    }

    $property_alias = trim(@$item->property_alias);

    /*if (isset($item->db_version) && $item->db_version > 2150) //版本控制
    {*/
        $spec = array
        (
            'status'              => $is_deleted == 1 ? 0 : ($item->approve_status == 'onsale') ? 1 : 2,
            'platform_id'         => 1,
            'shop_id'             => $shopId,
            'goods_id'            => trim($item->num_iid),
            'outer_id'            => $outer_id,
            'cid'                 => @$item->cid,
            'goods_name'          => trim($item->title),
            'price'               => $item->price,
            'stock_num'           => $item->num,
            'hold_stock_type'     => (int)@$item->sub_stock,
            'hold_stock'          => (int)@$item->with_hold_quantity,
            'pic_url'             => @trim($item->pic_url),
            'list_time'           => empty($item->list_time) ? '1000-01-01 00:00:00' : $item->list_time, //定时上架时间
            'delist_time'         => empty($item->delist_time) ? '1000-01-01 00:00:00' : $item->delist_time, //定时下架时间
            'spec_id'             => '',
            'spec_code'           => '',
            'spec_name'           => '',
            'spec_outer_id'       => '',
            'is_stock_changed'    => '1',
            'is_deleted'          => $is_deleted,
            'spec_sku_properties' => '',
            'created'             => date('Y-m-d H:i:s', time())
        );
    /*} else {
            $spec = array
            (
                'status'              => $is_deleted == 1 ? 0 : ($item->approve_status == 'onsale') ? 1 : 2,
                'platform_id'         => 1,
                'shop_id'             => $shopId,
                'goods_id'            => trim($item->num_iid),
                'outer_id'            => $outer_id,
                'cid'                 => @$item->cid,
                'goods_name'          => trim($item->title),
                'price'               => $item->price,
                'stock_num'           => $item->num,
                'hold_stock_type'     => (int)@$item->sub_stock,
                'hold_stock'          => (int)@$item->with_hold_quantity,
                'pic_url'             => trim($item->pic_url),
                'spec_id'             => '',
                'spec_code'           => '',
                'spec_name'           => '',
                'spec_outer_id'       => '',
                'is_stock_changed'    => '1',
                'is_deleted'          => $is_deleted,
                'spec_sku_properties' => '',
                'created'             => date('Y-m-d H:i:s', time())
        );
    }*/

    //规格
    $skus = &$item->skus->sku;
    if (empty($skus)) {
        $spec_list[] = $spec;
    } else {
        $prop_imgs = @$item->prop_imgs->prop_img;

        foreach ($skus as &$sku) {
            $spec_outer_id = trim(@$sku->outer_id);
            if (iconv_strlen($spec_outer_id, 'UTF-8') > 40) {
                logx("SPEC_NO_EXCEED\t{$outer_id}\t{$spec_outer_id}\t{$item->title}",$sid. "/Goods");
                $spec_outer_id = iconv_substr($spec_outer_id, 0, 40, 'UTF-8');
            }

            $nspec = $spec;
            $nspec['spec_id'] = @$sku->sku_id;
            $nspec['spec_code'] = $spec_outer_id;
            $nspec['spec_sku_properties'] = @$sku->properties;
            $nspec['spec_name'] = analysisAlias(@$sku->properties, $property_alias, @$sku->properties_name);
            $nspec['spec_outer_id'] = $spec_outer_id;
            $nspec['price'] = @$sku->price;
            $nspec['stock_num'] = @$sku->quantity;
            $nspec['hold_stock'] = (int)@$sku->with_hold_quantity;

            $sku_properties = explode(';', $sku->properties);
            //规格图片
            if (!empty($prop_imgs) && !empty($sku_properties[0])) {
                foreach ($prop_imgs as &$prop_img) {
                    if ($prop_img->properties == $sku_properties[0]) {
                        $nspec['pic_url'] = @$prop_img->url;
                        break;
                    }
                }
            }

            $spec_list[] = $nspec;
        }
    }


    return true;
}

//taobao回写商家编码
function topUploadSpecno($db, $shop, $numIid, $skuId, $skuProperties, $outerId, &$error_msg) {
    $sid = $shop->sid;
    $shopId = $shop->shop_id;
    $session = $shop->session;

    logx("topUploadSpecno shopid: $shopId numIid {$numIid} skuId {$skuId} skuProperties {$skuProperties} outerId {$outerId} ", $sid . "/Goods");

    //taobao
    $top = new TopClient();
    $top->format = 'json';
    $top->appkey = $shop->key;
    $top->secretKey = $shop->secret;

    if (empty($skuId)) {
        //链接对应单规格宝贝
        $req = new ItemUpdateRequest();
        $req->setNumIid($numIid);
        $req->setOuterId($outerId);

        $retval = $top->execute($req, $session);
        if (API_RESULT_OK != topErrorTest($retval, $db, $shopId)) {
            $error_msg["status"] = 0;
            $error_msg["info"] = $retval->error_msg;
            logx("topUploadSpecno top->execute fail", $sid . "/Goods");
            return false;
        }

        if (isset($retval->item)) {
            return true;
        } else {
            $error_msg["status"] = 0;
            $error_msg["info"] = '未知错误';
            return false;
        }

    } else {
        //链接对应多规格报表
        if (!empty($skuProperties)) {
            //正常数据
            $req = new ItemSkuUpdateRequest();
            $req->setNumIid($numIid);
            $req->setProperties($skuProperties);
            $req->setOuterId($outerId);

            $retval = $top->execute($req, $session);
            if (API_RESULT_OK != topErrorTest($retval, $db, $shopId)) {
                $error_msg["status"] = 0;
                $error_msg["info"] = $retval->error_msg;
                logx("topUploadSpecno top->execute fail", $sid . "/Goods");
                return false;
            }

            if (isset($retval->sku)) {
                return true;
            } else {
                $error_msg["status"] = 0;
                $error_msg["info"] = '未知错误';
                return false;
            }
        } else {
            //历史数据, 月亮宝贝和话梅会有, 有skuid但是没有skuproperties, 需要重新下载该链接
            $error_msg["status"] = 0;
            $error_msg["info"] = "不是最新数据, 请重新下载该链接";
            return false;
        }
    }

    return true;
}

//天猫回写商家编码
function tmUploadSpecno($db, $shop, $numIid, $skuId, $skuProperties, $outerId, &$error_msg) {
    $sid = $shop->sid;
    $shopId = $shop->shop_id;
    $session = $shop->session;

    logx("tmUploadSpecno shopid: $shopId numIid {$numIid} skuId {$skuId} skuProperties {$skuProperties} outerId {$outerId} ", $sid . "/Goods");

    //taobao
    $top = new TopClient();
    $top->format = 'json';
    $top->appkey = $shop->key;
    $top->secretKey = $shop->secret;

    if (empty($skuId)) {
        //链接对应单规格宝贝
        $req = new TmallItemOuteridUpdateRequest();
        $req->setItemId($numIid);
        $req->setOuterId($outerId);

        logx("tmUploadSpecno one " . print_r($req, true), $sid . "/Goods");
        $retval = $top->execute($req, $session);
        logx("tmUploadSpecno one " . print_r($retval, true), $sid . "/Goods");
        if (API_RESULT_OK != topErrorTest($retval, $db, $shopId)) {
            $error_msg["status"] = 0;
            $error_msg["info"] = $retval->error_msg;
            logx("tmUploadSpecno top->execute fail", $sid . "/Goods");
            return false;
        }

        if (isset($retval->outerid_update_result) && $retval->outerid_update_result == $outerId) {
            return true;
        } else {
            $error_msg["status"] = 0;
            $error_msg["info"] = '未知错误';
            return false;
        }

    } else {
        //链接对应多规格报表

        $req = new TmallItemOuteridUpdateRequest();
        $req->setItemId($numIid);
        $sku_outers = new UpdateSkuOuterId();
        $sku_outers->SkuId = $skuId;
        $sku_outers->Properties = $skuProperties;
        $sku_outers->OuterId = $outerId;
        $req->SkuOuters = $sku_outers;
        logx("tmUploadSpecno two " . print_r($req, true), $sid . "/Goods");
        $retval = $top->execute($req, $session);
        logx("tmUploadSpecno two " . print_r($retval, true), $sid . "/Goods");
        if (API_RESULT_OK != topErrorTest($retval, $db, $shopId)) {
            $error_msg["status"] = 0;
            $error_msg["info"] = $retval->error_msg;
            logx("tmUploadSpecno top->execute fail", $sid . "/Goods");
            return false;
        }

        if (isset($retval->outerid_update_result) && $retval->outerid_update_result == $numIid) {
            return true;
        } else {
            $error_msg["status"] = 0;
            $error_msg["info"] = '未知错误';
            return false;
        }
    }

    return true;
}

?>
