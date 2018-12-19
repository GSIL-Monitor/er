<?php
define('SYNC_OK', 0);    //同步成功
define('SYNC_QUIT', 1);    //同步失败
define('SYNC_FAIL', 2);    //终止当前所有同步任务,如api调用超限再同步也没意义

//调用次数超限平台
$g_overload_platforms = array();

/*
   calculate stock
*/
function calc_syn_stock($sid, &$db, &$stock) {
    $syn_stock = 0;

    $spec_id = $stock->match_target_id;
    $mask    = $stock->stock_syn_mask;
    /* $count_sql = array(
        " +purchase_num",  //0 采购在途量
        " +to_purchase_num", //1代采购量
        " +transfer_num",//2调拨在途量
        " +purchase_arrive_num",//3采购到货量
        " +return_onway_num", //4采购换货在途量0
        " +refund_onway_num",//5销售换货在途量（从买家回到卖家）0
        " -subscribe_num",//6预订单量
        " -order_num",//7待审核量
        " -unpay_num",//8未付款量
        " -sending_num",//9待发货量
        " -return_num",//10采购退货量0
        " -refund_num",//11销售退货量0
        " -return_exch_num",//12采购换货量0
        " -refund_exch_num",//13销售换货在途量(卖家发给买家)0
        " -lock_num",//14锁定库存量
        "-to_transfer_num",//15带调拨量
    ); */
    $count_sql = array(
        " +purchase_num",
        " +to_purchase_num",
        " +transfer_num",
        " +purchase_arrive_num",
        " +return_onway_num",
        " +refund_onway_num",
        " -subscribe_num",
        " -order_num",
        " -unpay_num",
        " -sending_num",
        " -return_num",
        " -refund_num",
        " -return_exch_num",
        " -refund_exch_num",
        " -lock_num",
        " -to_transfer_num",
    );

    //suite
    if ($stock->match_target_type == 1) {
        $sql = " SELECT IFNULL(SUM(stock_num ";
        foreach ($count_sql as $key => $value) {
            if ($mask & (1 << $key))
                $sql .= $value;
        }

        $sql .= '+IF(w.is_outer_stock,wms_stock_diff,0)';

        $sql .= "),0) AS stock FROM stock_spec ss "
            . " LEFT JOIN cfg_warehouse w on ss.warehouse_id=w.warehouse_id "
            . " WHERE w.is_defect=0 AND ss.spec_id={$spec_id}"
            . " AND w.warehouse_id IN ({$stock->stock_syn_warehouses}) ";

        $syn_stock = $db->query_result_single($sql, 0);
        logx("计算单品库存sql:{$sql} 库存:{$syn_stock}", $sid . "/Stock");

    } else {

        $sql_sel_head = " SELECT  FLOOR(MIN(IF(ss.stock_num IS NULL,0,(ss.stock_num ";
        foreach ($count_sql as $key => $value) {
            if ($mask & (1 << $key))
                $sql_sel_head .= $value;
        }

        $sql_sel_head .= '+IF(w.is_outer_stock,wms_stock_diff,0)';

        $syn_stock     = 0;
        $warehouse_arr = explode(',', $stock->stock_syn_warehouses);
        //循环每个仓库的组合装库存, 并求和
        foreach ($warehouse_arr as $warehouse_id) {
            $sql             = $sql_sel_head . ")/gsd.num))) AS stock "
                . "	FROM  goods_suite_detail gsd "
                . "	LEFT JOIN stock_spec ss on ss.spec_id=gsd.spec_id and ss.warehouse_id = {$warehouse_id} "
                . "  LEFT JOIN cfg_warehouse w on w.warehouse_id={$warehouse_id} "
                . "	WHERE w.is_defect=0 and gsd.suite_id={$spec_id} and w.warehouse_id= {$warehouse_id} ";
            $suite_stock_num = $db->query_result_single($sql, 0);
            logx("仓库 {$warehouse_id} 中有库存 {$suite_stock_num} ", $sid . "/Stock");
            $syn_stock += $suite_stock_num;
        }

        logx("计算组合装其中一个仓库库存sql:{$sql},仓库列表:{$stock->stock_syn_warehouses},库存 $syn_stock ", $sid . "/Stock");
    }
    //y=kx+b线性关系计算
    //猛犸象家单独走四舍五入方式进行库存计算。其余卖家仍为向下取整
    if($sid == 'mengmaxiang'){
        $syn_stock = round($syn_stock * ($stock->stock_syn_percent / 100) + $stock->stock_syn_plus);

    }else{
        $syn_stock = floor($syn_stock * ($stock->stock_syn_percent / 100) + $stock->stock_syn_plus);
    }
    logx("线性关系后库存为 {$syn_stock} ", $sid . "/Stock");
    return $syn_stock;
}

function do_syn_stock($stock) {
    return do_syn_stock_impl($stock, 0,$fail);
}

/*
自动同步
手动同步
上架
下架均复用这个function
*/
function do_syn_stock_impl($stock, $man,&$fail) {
    
    global $g_overload_platforms;

    //deleteJob();

    //如果调用api超限不需要再执行了
    if (in_array($stock->platform_id, $g_overload_platforms, false))
        return TASK_OK;

    $sid = $stock->sid;
    $db  = getUserDb($sid);

    if (!$db) {
        logx("$sid syncStock getUserDb failed!!", $sid . "/Stock",'error');
        return TASK_OK;
    }

    //待同步记录信息
    $spec_id = $stock->match_target_id;

    //step1: 保护措施:如果该规则为自动绑定，并且商家编码为空, 删除该同步记录

    //match_target_id 默认是0，货品匹配之后
    if ($spec_id == 0) {
        logx("未匹配货品 "
             . "该记录为 num_iid: {$stock->goods_id} skuid: {$stock->spec_id} spec_id: {$stock->match_target_id} "
             . "match_target_type{$stock->match_target_type} shop_id: {$stock->shop_id} outer_id: {$stock->outer_id} spec_outer_id: {$stock->spec_outer_id} ", $sid . "/Stock");
        $fail[]   = array("rec_id" => @$stock->rec_id, "goods_id" => @$stock->goods_id, "spec_id" => @$stock->spec_id, "msg" => "未匹配货品!");
        syn_pause($db, $stock);
        releaseDb($db);
        return TASK_OK;
    }

    if ($stock->is_manual_match == 0)//不进行手动匹配
    {
        if (empty($stock->match_code)) {
            logx("自动匹配到空商家编码, "
                 . "该记录为 num_iid: {$stock->goods_id} skuid: {$stock->spec_id} spec_id: {$stock->match_target_id} "
                 . "match_target_type{$stock->match_target_type} shop_id: {$stock->shop_id} outer_id: {$stock->outer_id} spec_outer_id: {$stock->spec_outer_id} ", $sid . "/Stock");
            $fail[]   = array("rec_id" => @$stock->rec_id, "goods_id" => @$stock->goods_id, "spec_id" => @$stock->spec_id, "msg" => "空商家编码!");
            syn_disable($db, $stock, '空商家编码');
            releaseDb($db);
            return TASK_OK;
        }
    }

    //step2: 记录该单品/组合装的同步规则
    if ($stock->match_target_type == 1) {
        logx("发现待同步的单品, 该记录为 spec_id: {$spec_id} shop_id: {$stock->shop_id} ", $sid . "/Stock");
    } else {
        logx("发现待同步的组合装, 该记录为 spec_id: {$spec_id} shop_id: {$stock->shop_id} ", $sid . "/Stock");
    }

    logx("发现平台货品映射记录rec_id: {$stock->rec_id} 是否手动同步:{$stock->is_manual}"
         . "该同步规则的详情为:  "
         . "同步规则id: {$stock->stock_syn_rule_id} 同步规则编号: {$stock->stock_syn_rule_no} "
         . "最小库存: {$stock->stock_syn_min} 超过最小库存自动上架: {$stock->is_auto_listing} "
         . "低于最小库存自动下架: {$stock->is_auto_delisting} "
         . "同步百分比: {$stock->stock_syn_percent} 附加值: {$stock->stock_syn_plus} "
         . "同步仓库列表: {$stock->stock_syn_warehouses} "
         . "库存数量计算方式: {$stock->stock_syn_mask}  ", $sid . "/Stock");


    if (!isset($stock->stock_syn_warehouses) || empty($stock->stock_syn_warehouses)) {
        logx("$sid 仓库列表为空 同步失败", $sid . "/Stock");
        $fail[]   = array("rec_id" => @$stock->rec_id, "goods_id" => @$stock->goods_id, "spec_id" => @$stock->spec_id, "msg" => "仓库列表为空!");
        releaseDb($db);
        return TASK_OK;
    }


    //step3: 获取待同步的库存 手动上架 手动下架的情况syn_stock已经指定
    if (isset($stock->syn_stock)) {
        $syn_stock = $stock->syn_stock;
    } else {
        $syn_stock = calc_syn_stock($sid, $db, $stock);
    }

    $force_sync = false; //强制同步
    //step4: 检查计算后的库存和最小库存的关系
    if ($syn_stock < $stock->stock_syn_min) {
        //同步了0库存会自动下架
        if ($stock->is_auto_delisting == 1) {
            logx("计算库存值为$syn_stock 设置的最小库存为 $stock->stock_syn_min ,设置了低于最小库存自动下架,本次将同步数量0 ", $sid . "/Stock");
            $syn_stock = 0;
        } else {
            $syn_stock  = $stock->stock_syn_min;
            $force_sync = true;

            logx("设置的最小库存为 $syn_stock , 本次将同步 $syn_stock ", $sid . "/Stock");
        }
    }

    /*
    step5: 如果库存未发生变化,则取消同步
    top平台有jst 维护stock_num
    select count(1) from api_goods_spec where stock_num  <>last_syn_num  and platform_id =1 and is_stock_changed=0;
    会出来一些数据stock_num  的数据必须有goods.php来维护准确
    */
    if (!$man && !$force_sync && $stock->platform_id == 1 && $syn_stock == $stock->stock_num) {
        logx("该记录库存未发生变化,不同步", $sid . "/Stock");
        syn_cancel($db, $stock);
        releaseDb($db);
        return TASK_OK;
    }

    //step6: 调接口同步(库存大于0, 以及同步零库存, 都会调用)
    //计算出来的同步数量
    $stock->syn_stock = (int)$syn_stock;

    $retval = SYNC_FAIL;
    if ($stock->platform_id == 1) //淘宝
    {
        require_once(ROOT_DIR . '/Stock/top.php');
        $retval = top_stock_syn($db, $stock, $sid,$fail);
    } else if($stock->platform_id == 2) //淘宝分销
    {
        require_once(ROOT_DIR . '/Stock/top_fx.php');
        $retval = top_fx_stock_syn($db, $stock, $sid);
    } else if ($stock->platform_id == 3) //京东
    {
        require_once(ROOT_DIR . '/Stock/jos.php');
        $retval = jos_stock_syn($db, $stock, $sid,$fail);
    } else if ($stock->platform_id == 6) //一号店
    {
        require_once(ROOT_DIR . '/Stock/yhd.php');
        $retval = yhd_stock_syn($db, $stock, $sid);
    } else if ($stock->platform_id == 7) //当当
    {
        require_once(ROOT_DIR . '/Stock/dangdang.php');
        $retval = dd_stock_syn($db, $stock, $sid);
    } else if($stock->platform_id == 8) //国美
    {
        require_once(ROOT_DIR . '/Stock/coo8.php');
        $retval = coo8_stock_syn($db, $stock, $sid);
    } else if($stock->platform_id == 9) //阿里巴巴    
    {
        require_once(ROOT_DIR . '/Stock/alibaba.php');
        $retval = alibaba_stock_syn($db, $stock, $sid);
    }else if ($stock->platform_id == 13)//sos
    {
        require_once(ROOT_DIR . '/Stock/sos.php');
        $retval = sos_stock_syn($db, $stock, $sid);
    } else if ($stock->platform_id == 17)//kdt
    {
        require_once(ROOT_DIR . '/Stock/kdt.php');
        $retval = kdt_stock_syn($db, $stock, $sid);
    }  else if ($stock->platform_id == 22)//贝贝网
    {
        require_once(ROOT_DIR . '/Stock/bbw.php');
        $retval = bbw_stock_syn($db, $stock, $sid);
    }else if($stock->platform_id == 25) // icbc 融e购
	{
		require_once(ROOT_DIR . '/Stock/icbc.php');
		$retval = icbc_stock_syn ( $db, $stock, $sid );
	}else if ($stock->platform_id == 27)//楚楚街
    {   
        require_once(ROOT_DIR . '/Stock/ccj.php');
        $retval = ccj_stock_syn($db, $stock, $sid);
    } else if ($stock->platform_id == 20)//美丽说
    {
        require_once(ROOT_DIR . '/Stock/mls.php');
        $retval =meilishuo_stock_syn($db, $stock, $sid);
    } else if ($stock->platform_id == 24)//折800
    {
        require_once(ROOT_DIR . '/Stock/zhe800.php');
        $retval = zhe_stock_syn($db, $stock, $sid);
    }else if ($stock->platform_id == 28)//微盟旺店
    {
        require_once(ROOT_DIR . '/Stock/weimo.php');
        $retval = weimo_stock_syn($db, $stock, $sid);
    } else if ($stock->platform_id == 29)//卷皮网
    {
        require_once(ROOT_DIR . '/Stock/jpw.php');
        $retval =jpw_stock_syn($db, $stock, $sid);
    }else if($stock->platform_id == 31)//飞牛
    {
        require_once(ROOT_DIR . '/Stock/fn.php');
        $retval = fn_stock_syn($db, $stock, $sid);
    }else if($stock->platform_id == 32)//微店
    {
        require_once(ROOT_DIR . '/Stock/vdian.php');
        $retval = vdian_stock_sync($db, $stock, $sid);
    }else if($stock->platform_id == 33) //拼多多
    {
        require_once(ROOT_DIR . '/Stock/pdd.php');
        $retval = pdd_stock_sync($db, $stock, $sid);
    }else if($stock->platform_id == 34) //蜜芽宝贝
    {
        require_once(ROOT_DIR . '/Stock/mia.php');
        $retval = mia_stock_syn($db, $stock, $sid);
    }else if($stock->platform_id == 47)//人人店
    {
        require_once(ROOT_DIR . '/Stock/rrd.php');
        $retval = rrd_stock_syn($db, $stock, $sid);
    }
    else if($stock->platform_id == 56) //小红书
    {
        require_once(ROOT_DIR . '/Stock/xhs.php');
        $retval = xhs_stock_syn($db, $stock, $sid);
    }else {
        logx("$sid UNSURPPORTED PLATFORM", $sid . "/Stock",'error');
    }

    releaseDb($db);

    if ($retval == SYNC_QUIT)
        $g_overload_platforms[] = $stock->platform_id;

    return TASK_OK;
}

//设置某记录为不需同步
function syn_cancel(&$db, $stock) {
    $sql = " UPDATE api_goods_spec SET "
        . " is_stock_changed=IF(stock_change_count={$stock->stock_change_count},0,1) "
        . " WHERE rec_id='{$stock->rec_id}'";
    $db->execute($sql);
}

//平台货品已被删除
function syn_delete(&$db, $stock) {
    $sql = "UPDATE api_goods_spec SET is_stock_changed=0,is_disable_syn=1 WHERE rec_id='{$stock->rec_id}'";
    $db->execute($sql);
}

//停止同步，除非人工重新开启
function syn_disable(&$db, $stock, $reason) {
    $sql = "UPDATE api_goods_spec SET is_disable_syn=1,disabled_reason=%s WHERE rec_id=%d";
    $db->query($sql, $reason, (int)$stock->rec_id);
}

//延时同步
function syn_delay(&$db, $stock, $secs, $reason) {
    $sql = "UPDATE api_goods_spec SET disable_syn_until=UNIX_TIMESTAMP()+%d,disabled_reason=%s WHERE rec_id=%d";
    $db->query($sql, (int)$secs, $reason, (int)$stock->rec_id);
}

//暂停同步,库存变化时自动重启
function syn_pause(&$db, $stock) {
    $sql = "UPDATE api_goods_spec SET is_stock_changed=0  WHERE rec_id='{$stock->rec_id}' AND match_target_id=0";
    $db->execute($sql);
}

//设置同步成功
function syn_success(&$db, &$stock) {
    $sql = " UPDATE  api_goods_spec SET "
        . " last_syn_time='{$stock->syn_time}', "
        . " last_syn_num={$stock->syn_stock}, "
        . " stock_num={$stock->syn_stock}, "
        . " is_stock_changed=IF(stock_change_count={$stock->stock_change_count},0,1) "
        . " WHERE rec_id='{$stock->rec_id}'";
    $db->execute($sql);
}

/*
 更新status
0删除 1在架 2下架
*/
function  top_sale_status(&$db, $recid, $status) {
    $sql = " UPDATE  api_goods_spec SET  `status`={$status} WHERE rec_id='{$recid}'";
    $db->execute($sql);
}

//增加库存同步日志
function syn_log(&$db, &$stock, $bSuccess, $errorMsg) {
    if ($bSuccess > 0) {
        $errorMsg = '库存同步成功';
        if($bSuccess == 2){
            $errorMsg = '商品上架成功';
        }else if($bSuccess == 3){
            $errorMsg = '商品下架成功';
        }
        $bSuccess = 1;
        $sql      = " UPDATE  api_goods_spec SET "
            . " last_syn_time='{$stock->syn_time}', "
            . " last_syn_num={$stock->syn_stock}, "
            . " stock_num={$stock->syn_stock}, "
            . " is_stock_changed=IF(stock_change_count={$stock->stock_change_count},0,1) "
            . " WHERE rec_id='{$stock->rec_id}'";
        $db->execute($sql);
    }

    $arr   = array();
    $arr[] = array(
        "shop_id"           => $stock->shop_id,
        "platform_id"       => $stock->platform_id,
        "goods_id"          => $stock->goods_id,
        "spec_id"           => $stock->spec_id,
        "match_target_type" => $stock->match_target_type,
        "match_target_id"   => $stock->match_target_id,
        "num"               => $stock->syn_stock,
        "rule_id"           => $stock->stock_syn_rule_id,
        "rule_no"           => $stock->stock_syn_rule_no,
        "warehouse_list"    => $stock->stock_syn_warehouses,
        "mask"              => $stock->stock_syn_mask,
        "percent"           => $stock->stock_syn_percent,
        "plus_value"        => $stock->stock_syn_plus,
        "min_stock"         => $stock->stock_syn_min,
        "is_auto_listing"   => $stock->is_auto_listing,
        "is_auto_delisting" => $stock->is_auto_delisting,
        "is_sucess"         => $bSuccess,
        "is_manual"         => $stock->is_manual,
        "result"            => $errorMsg);

    if (!putDataToTable($db, 'api_stock_sync_record', $arr, "")) {
        logx("$stock->sid 插入库存同步日志失败", $stock->sid . "/Stock",'error');
    }
}

?>