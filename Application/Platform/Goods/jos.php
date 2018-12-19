<?php
require_once(ROOT_DIR . '/Goods/utils.php');
require_once(TOP_SDK_DIR . '/jos/JdClient.php');
require_once(TOP_SDK_DIR . '/jd/request/SkuReadSearchSkuListRequest.php');
require_once(TOP_SDK_DIR . '/jd/request/WareReadFindWareByIdRequest.php');
require_once(TOP_SDK_DIR . '/jd/request/WareReadSearchWare4ValidRequest.php');
require_once(TOP_SDK_DIR . '/jd/request/SkuReadFindSkuByIdRequest.php');


function downJosGoodsDetail($sid, $shopId, &$db, &$jos, &$num_iids, &$spec_list, &$new_count, &$chg_count, &$error_msg,$item_arr)
{

    $size = 10;

    $total_num = count($num_iids);
    $total_time = ceil($total_num / $size);
    $sku_array = array();
    for ($t = 0; $t < $total_time; $t++) {

        $numiid_merge = implode(",", array_slice($num_iids, $t * $size, $size));

        $req = new SkuReadSearchSkuListRequest();

        $req->setField("skuName,wareId,skuId,outerId,wareTitle,itemNum,stockNum,jdPrice,logo,status,categoryId,imgTag,modified");

        $req->setWareId($numiid_merge);
        $req->setPageSize(100);

        $retval = $jos->execute($req, $jos->accessToken);

        if (API_RESULT_OK != josErrorTest($retval, $db, $shopId)) {
            $error_msg["status"] = 0;
            $error_msg["info"]   = $retval->error_msg;
            return false;
        }

        //这里有个坑。必须走循环。（10个10个货品去抓，如果这10个货品的SKU数量超过 设置的页码大小，必须通过循环处理）
        $total_sku = $retval->page->totalItem;
        if( $total_sku>100){
            $pageSize= 100;
            for($pageNo=2;$pageNo<=ceil($total_sku/$pageSize);$pageNo++){
                $req->setPageNo($pageNo);
                $res = $jos->execute($req, $jos->accessToken);
                if (API_RESULT_OK != josErrorTest($retval, $db, $shopId)) {
                    $error_msg["status"] = 0;
                    $error_msg["info"]   = $retval->error_msg;
                    return false;
                }
                $sku_array[] = $res->page->data;
            }
        }
        $array = array();
        foreach($sku_array as $k=>$v){
            $array = array_merge($array,$v);
        }

        $items = &$retval->page->data;
        $items = array_merge($items,$array);


        for ($i = 0; $i < count($items); $i++) {
            $item = $items[$i];

            $spec = array
            (
                'status' => ($item->status == '1') ? 1 : 2,
                'platform_id' => 3,
                'shop_id' => $shopId,
                'goods_id' => trim($item->wareId),
                'outer_id' => iconv_substr($item_arr[$t], 0, 40, 'UTF-8'),
                'cid' => @$item->categoryId,
                'goods_name' => trim($item->wareTitle),
                'price' => $item->jdPrice,
                'stock_num' => $item->stockNum,
                'pic_url' => (trim($item->logo) == "") ? "" : "http://img10.360buyimg.com/n0/".trim($item->logo),
                'spec_id' => @$item->skuId,
                'spec_code' => trim(@$item->outerId),
                'spec_name' => trim(@$item->skuName),
                'spec_outer_id' => trim(@$item->outerId),
                'is_stock_changed' => '1',
                'created' => date('Y-m-d H:i:s', time())
            );

            $spec_list[] = $spec;
        }
        //insert into db in batch
        if (count($spec_list) >= 100 && !putGoodsToDb($sid, $db, $spec_list, $new_count, $chg_count, $error_msg)) {
            return false;
        }

    }
    return true;
}

function josDownloadGoodsList($db, $shop, $mode, $condition, &$new_count, &$chg_count, &$error_msg)
{

    $sid = $shop->sid;
    $shopId = $shop->shop_id;

    $jos = new JdClient();
    $jos->appKey = $shop->key;
    $jos->appSecret = $shop->secret;
    $jos->accessToken = $shop->session;

    if ($mode == 1) {
        $start_time = $condition;
        $end_time = time();

        logx("josDownloadGoodsList shopId: $shopId start_time:" .
            date('Y-m-d H:i:s', $start_time) . " end_time: " .
            date('Y-m-d H:i:s', $end_time), $sid . "/Goods");

        $req = new WareReadSearchWare4ValidRequest();
        if (!josDownloadGoodsListImpl($db,
            $sid,
            $shopId,
            $mode,
            $condition,
            $jos,
            $req,
            $new_count,
            $chg_count,
            $error_msg)
        ) {
            return false;
        }

    } elseif ($mode == 2) {
        $req = new WareReadSearchWare4ValidRequest();
        if (!josDownloadGoodsListImpl($db,
            $sid,
            $shopId,
            $mode,
            $condition,
            $jos,
            $req,
            $new_count,
            $chg_count,
            $error_msg)
        ) {
            return false;
        }
    } else if ($mode == 3) {
        $req = new WareReadSearchWare4ValidRequest();
        if (!josDownloadGoodsListImpl($db,
            $sid,
            $shopId,
            $mode,
            $condition,
            $jos,
            $req,
            $new_count,
            $chg_count,
            $error_msg)
        ) {
            return false;
        }
    } else if ($mode == 4) {
        $req = new WareReadSearchWare4ValidRequest();
        if (!josDownloadGoodsListImpl($db,
            $sid,
            $shopId,
            $mode,
            $condition,
            $jos,
            $req,
            $new_count,
            $chg_count,
            $error_msg)
        ) {
            return false;
        }

    } else {
        $req = new WareReadSearchWare4ValidRequest();
        if (!josDownloadGoodsListImpl($db,
            $sid,
            $shopId,
            $mode,
            $condition,
            $jos,
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

function josDownloadGoodsListImpl($db, $sid, $shopId, $mode, $condition, &$jos, &$req, &$new_count, &$chg_count, &$error_msg)
{
    $page_size = 20;

    switch ($mode) {
        case 1: {
            $start_time = $condition;
            $end_time = time();

            $req->setStartModifiedTime(date('Y-m-d H:i:s', $start_time));
            $req->setEndModifiedTime(date('Y-m-d H:i:s', $end_time));
            $req->setPageNo(1);
            break;
        }
        case 2: {

            $req->setSearchField('title');
            $req->setSearchKey($condition);
            $req->setPageNo(1);
            $start_time = 0;
            $end_time = 0;
            break;
        }
        case 3: {
            $req->setWareId($condition);
            $start_time = 0;
            $end_time = 0;
            break;
        }
        case 4: {
            $time = explode(',', trim($condition));
            $start_time = strtotime($time[0]);
            $end_time = strtotime($time[1]);
            $now = time();
            if ($end_time >= $now) {
                $end_time = $now;
            }

            logx("josDownloadGoodsList shopId: $shopId start_time:" .
                date('Y-m-d H:i:s', $start_time) . " end_time: " .
                date('Y-m-d H:i:s', $end_time), $sid . "/Goods");

            $req->setStartModifiedTime(date('Y-m-d H:i:s', $start_time));
            $req->setEndModifiedTime(date('Y-m-d H:i:s', $end_time));
            $req->setPageNo(1);
            break;
        }
    }

    $req->setField("wareId");

    $spec_list = array();

    $intval = 3600*24;

    $result = splitTime($start_time, $end_time, $intval, function ($from_time, $to_time) use (&$db, &$req, &$jos, &$error_msg, &$spec_list, $sid, $shopId, $page_size, &$new_count, &$chg_count, $mode) {

        if ($from_time && $to_time) {
            $req->setStartModifiedTime(date('Y-m-d H:i:s', $from_time));
            $req->setEndModifiedTime(date('Y-m-d H:i:s', $to_time));
        }

        $retval = $jos->execute($req, $jos->accessToken);

        if (API_RESULT_OK != josErrorTest($retval, $db, $shopId)) {
            $error_msg["status"] = 0;
            $error_msg["info"]   = $retval->error_msg;
            logx("$sid josDownloadGoodsListImpl jos->execute fail, error_msg: $error_msg", $sid . "/Goods",'error');
            return false;
        }

        if (!isset($retval->page->data) || count($retval->page->data) == 0) {
            logx("josDownloadGoodsListImpl $shopId count: 0", $sid . "/Goods");
            return true;
        }

        $items = $retval->page->data;

        $total_results = intval($retval->page->totalItem);

        logx("josDownloadGoodsListImpl shopId: $shopId count: $total_results", $sid . "/Goods");

        //just one page
        if ($total_results <= count($items)) {
            logx("just one page", $sid . "/Goods");
            $numiid_arr = array();
            $item_arr = array();
            for ($j = 0; $j < count($items); $j++) {
                $numiid_arr[] = $items[$j]->wareId;
                $item_arr[]   = $items[$j]->itemNum;
            }

            if (count($numiid_arr) > 0) {
                if (!downJosGoodsDetail($sid, $shopId, $db, $jos, $numiid_arr, $spec_list, $new_count, $chg_count, $error_msg,$item_arr)) {
                    return false;
                }
            }
        } else {

            $total_pages = ceil(floatval($total_results) / $page_size);

            logx("total page: $total_pages  ", $sid . "/Goods");

            for ($i = $total_pages; $i >= 1; $i--) {

                logx("the $i page ", $sid . "/Goods");

                $req->setpageNo($i);

                $retval = $jos->execute($req, $jos->accessToken);

                if (API_RESULT_OK != josErrorTest($retval, $db, $shopId)) {
                    $error_msg["status"] = 0;
                    $error_msg["info"]   = $retval->wareId;
                    logx("$sid josDownloadGoodsListImpl jos->execute fail2", $sid . "/Goods",'error');
                    return false;
                }

                $items = $retval->page->data;

                $numiid_arr = array();
                $item_arr = array();

                for ($j = 0; $j < count($items); $j++) {
                    $numiid_arr[] = $items[$j]->wareId;
                    $item_arr[]   = $items[$j]->itemNum;
                }

                if (count($numiid_arr) > 0) {
                    if (!downJosGoodsDetail($sid, $shopId, $db, $jos, $numiid_arr, $spec_list, $new_count, $chg_count, $error_msg,$item_arr)) {
                        return false;
                    }
                }

            }
        }

        if ($mode != 4) {
            if ($to_time || count($spec_list) == 0) {
                $db->execute("INSERT INTO cfg_setting(`key`,`value`) VALUES('goods_sync_shop_{$shopId}','{$to_time}') ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)");
            }
        }

        return true;
    });

    //save data
    if ((count($spec_list) == 0 || putGoodsToDb($sid, $db, $spec_list, $new_count, $chg_count, $error_msg)) &&
        $result &&
        $end_time
    ) {
        if ($mode != 4) {
            $db->execute("INSERT INTO cfg_setting(`key`,`value`) VALUES('goods_sync_shop_{$shopId}','{$end_time}') ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)");
        }
    }
    return $result;
}


?>
