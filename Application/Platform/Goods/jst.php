<?php
require_once(TOP_SDK_DIR . '/top/Logger.php');
require_once(ROOT_DIR . '/Goods/top.php');
require_once(ROOT_DIR . '/Goods/utils.php');

function jstTopDownloadGoodsList($shop, $start_time, $end_time, $save_time, &$message) {
    $sid     = $shop->sid;
    $shop_id = $shop->shop_id;

    $db = getUserDb($sid);
    if (!$db) {
        $message["status"] = 0;
        $message["ïnfo"]   = "未知错误，请联系管理员";
        logx("ERROR $sid jstTopDownloadGoodsList getUserDb failed!!", $sid . "/Goods",'error');
        return TASK_SUSPEND;
    }

    if ($save_time)
        $save_time = $end_time;

    logx("jstTopDownloadGoodsList shopid: {$shop_id}"
         . " start_time:" . date('Y-m-d H:i:s', $start_time) . " end_time: " . date('Y-m-d H:i:s', $end_time), $sid . "/Goods");

    $start_time = date('Y-m-d H:i:s', $start_time);
    $end_time   = date('Y-m-d H:i:s', $end_time);


    $jst_db = getJstDb($sid, $shop->push_rds_id);
    if (!$jst_db) {
        $message["status"] = 0;
        $message["info"]   = "未知错误，请联系管理员";
        logx("$sid getJstDb Failed", $sid . "/Goods",'error');
        return TASK_OK;
    }

    //taobao
    $goods_sql = "select jdp_delete,jdp_response from jdp_tb_item " .
        " where nick=" . "'" . addslashes($shop->account_nick) . "'" . " and jdp_modified>='{$start_time}' and jdp_modified<='{$end_time}' order by jdp_modified desc";

    $offset     = 0;
    $spec_list  = array();
    $db_version = (int)getSysCfg($db, 'sys_db_version', 0);
    $new_count = 0;
    $chg_count = 0;
    while (true) {
        if ($offset == 0)
            $all_goods = $jst_db->query($goods_sql . " limit 1000");
        else
            $all_goods = $jst_db->query($goods_sql . " limit $offset,1000");
        if (!$all_goods) {
            releaseDb($db);
            $message["status"] = 0;
            $message["info"]   = "未知错误，请联系管理员";
            logx("jstTopDownloadGoodsList query failed", $sid . "/Goods");
            return TASK_OK;
        }

        $count = 0;
        while ($goods = $jst_db->fetch_array($all_goods)) {
            ++$count;

            $response_goods   = json_decode_safe($goods['jdp_response']);
            $jdp_delete       = $goods['jdp_delete'];
            $item             = $response_goods->item_get_response->item;
            $item->db_version = $db_version;
            if (!loadGoodsDetailImpl($shop_id,$sid, $item, $spec_list, $jdp_delete))
                continue;

            //超过100条写一次库
            if (count($spec_list) >= 100 && !putGoodsToDb($sid, $db, $spec_list, $new_count, $chg_count, $error_msg)) {
                releaseDb($db);
                $message["status"] = 0;
                $message["info"]   = "未知错误，请联系管理员";
                logx("jstTopDownloadGoodsList putGoodsToDb failed", $sid . "/Goods");
                return TASK_OK;
            }

        }

        $jst_db->free_result($all_goods);
        if ($count < 1000)
            break;

        $offset += 1000;
    }

    //保存到数据库
    if (count($spec_list) > 0 && !putGoodsToDb($sid, $db, $spec_list, $new_count, $chg_count, $error_msg)) {
        releaseDb($db);
        $message["status"] = 0;
        $message["info"]   = "未知错误，请联系管理员";
        logx("$sid jstTopDownloadGoodsList putGoodsToDb failed", $sid . "/Goods",'error');
        return TASK_OK;
    }


    if ($save_time) {
        $db->execute("INSERT INTO cfg_setting(`key`,`value`) VALUES('stock_last_platdown_time{$shop_id}',{$save_time}) ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)");
    }

    releaseDb($db);
    $message["status"] = 1;
    $message["info"]   = "下载完成：新增货品数量" . $new_count . " 更新货品数量" . $chg_count;
    logx("jstTopDownloadGoodsList downloaded successfully!", $sid . "/Goods");
    return TASK_OK;
}


?>