<?php
namespace Platform\Manager;

require_once(ROOT_DIR . '/Manager/utils.php');
require_once(ROOT_DIR . '/Stock/utils.php');
require_once(ROOT_DIR . "/Manager/Manager.class.php");

class StockManager extends Manager {

    public static function Stock_main() {
        return enumAllMerchant('stock_merchant');
    }

    public static function synStockComplete($tube, $complete) {
        //logx('Process Exit');
    }

    public static function syn_stock($sid) {
        $db = getUserDb($sid);
        if (!$db) {
            logx("getStock getUserDb failed!!", $sid . "/Stock");
            return TASK_OK;
        }
        //在库存同步之前调用该方法，将库存更新数据同步到平台货品
        self::batchDealSysProcessBackground($db, $sid);

        //�?查是否开启库存同�?
        $open_stock_sync = (int)getSysCfg($db, 'stock_auto_sync', 0);
        if ($open_stock_sync != 1) {
            releaseDb($db);
            return TASK_OK;
        }

        //�?查是否到达同步间�?
        $now          = time();
        $lastSyncTime = (int)getSysCfg($db, 'stock_last_sync_time', 0);

        if ($lastSyncTime > 0) {
            $interval = (int)getSysCfg($db, 'stock_sync_interval', 10);
            if ($interval < 5) $interval = 5;
            if ($interval > 30) $interval = 30;

            if ($lastSyncTime + $interval * 60 > $now) {
                releaseDb($db);
                logx("未到同步时间 LastTime:" . date('Y-m-d H:i:s', $lastSyncTime) . " Now:" . date('Y-m-d H:i:s', $now), $sid . "/Stock");
                return TASK_OK;
            }
        }
        $stock_limit = 2000;

        $sql = "SELECT * FROM v_api_goodsspec_sync ORDER BY rec_id LIMIT $stock_limit";

        $result = $db->query($sql);
        if (!$result) {
            releaseDb($db);
            logx("query stocks failed", $sid . "/Stock");
            return TASK_OK;
        }

        while ($row = $db->fetch_array($result)) {
            if (!checkAppKey($row))
                continue;
            $row              = (array)$row;
            $row['sid']       = $sid;
            $row['is_manual'] = 0;
            $row['syn_time']  = date('Y-m-d H:i:s', $now);
            pushTask('stock_syn', $row);
        }
        $db->free_result($result);

        //设置本次同步的时�?

        setSysCfg($db, "stock_last_sync_time", $now);
        logx("设置本次同步时间: {$now} ", $sid . "/Stock");

        releaseDb($db);
        return TASK_OK;

    }

    //自动调用的时候应该首先调用该方法,注册类内部的方法
    public static function register() {
        registerHandle('stock_merchant', array('\\Platform\\Manager\\StockManager', 'syn_stock'));
        registerHandle('stock_syn', 'do_syn_stock');
        registerExit(array('\\Platform\\Manager\\StockManager', 'synStockComplete'));
    }

    //手动库存同步的入口
    function stock_syn($sid, $uid, $recid_str, &$fail, &$success) {

        /*$ack_cols = array("goods_id" => TYPE_DT_STRING, "spec_id" => TYPE_DT_STRING, "msg" => TYPE_DT_STRING);
        $ack_rows = array();*/

        $db = getUserDb($sid);
        if (!$db) {
            logx("stock_cmd getUserDb failed!!", $sid . "/Stock");
            $fail[] = array("rec_id" => "", "goods_id" => "", "spec_id" => "", "msg" => "未知错误，请联系管理员");
            return;
        }

        //在库存同步之前调用该方法，将库存更新数据同步到平台货品
        self::batchDealSysProcessBackground($db, $sid);

        $task      = array();
        if($recid_str == ''){
            $res = $db->query("SELECT rec_id FROM api_goods_spec");
            while($row = $db->fetch_array($res)){
                $rec_array[] = $row['rec_id'];
            }
        }else{
            $rec_array=explode(",",$recid_str);
        }
        foreach ($rec_array as $recid) {
            $sql = " SELECT ag.*,sh.sub_platform_id,sh.account_id,sh.app_key,sh.auth_state,sh.auth_time "
                . " FROM api_goods_spec ag "
                . " LEFT JOIN cfg_shop sh ON ag.shop_id=sh.shop_id AND ag.platform_id=sh.platform_id "
                . " WHERE ag.rec_id='$recid' ";

            $row = $db->query_result($sql);

            if (empty($row)) {
                logx("映射记录{$recid}不存在!!", $sid . "/Stock");
                $fail[] = array("rec_id" => "", "goods_id" => "", "spec_id" => "", "msg" => "错误:映射记录不存在!");
                continue;
            }

            if ($row['auth_state'] <> 1) {
                logx("stock_cmd {$recid} 该店铺授权失效!!", $sid . "/Stock");
                $goods_id = $row->goods_id;
                $spec_id  = $row->spec_id;
                $fail[]   = array("rec_id" => @$row->rec_id, "goods_id" => "{$goods_id}", "spec_id" => "{$spec_id}", "msg" => '错误:该店铺授权失效!');
                continue;
            }

            if ($row['is_disable_syn'] == 1) {
                logx("同步策略规则 {$recid} 不进行库存同步!!", $sid . "/Stock");
                $goods_id = $row['goods_id'];
                $spec_id  = $row['spec_id'];
                $fail[]   = array("rec_id" => @$row->rec_id, "goods_id" => @$goods_id, "spec_id" => @$spec_id, "msg" => "错误:同步策略规则不进行库存同步!");
                continue;
            }

            $specid   = $row['match_target_id'];
            $shoptype = $row['platform_id'];
            $shopid   = $row['shop_id'];

            if (empty($specid)) {
                logx("{$recid}待同步记录未关联单品或组合装!!", $sid . "/Stock");
                $goods_id = $row['goods_id'];
                $spec_id  = $row['spec_id'];
                $fail[]   = array("rec_id" => @$row->rec_id, "goods_id" => @$goods_id, "spec_id" => @$spec_id, "msg" => "错误:待同步记录未关联单品或组合装!");
                continue;
            }

            if (!in_array($shoptype, array(1,2,3,6,7,8,9,13,17,20,22,24,25,27,28,29,31,32,33,34,47,56))) {
                logx("{$recid} 该平台不支持同步库存!!", $sid . "/Stock");
                $goods_id = $row['goods_id'];
                $spec_id  = $row['spec_id'];
                $fail[]   = array("rec_id" => @$row->rec_id, "goods_id" => $goods_id, "spec_id" => $spec_id, "msg" => "错误:该平台不支持同步库存!");
                continue;
            }

            if (!checkAppKey($row)) {
                logx("stock_cmd {$recid} 该店铺未授权!!", $sid . "/Stock");
                $goods_id = $row->goods_id;
                $spec_id  = $row->spec_id;
                $fail[]   = array("rec_id" => @$row->rec_id, "goods_id" => $goods_id, "spec_id" => $spec_id, "msg" => "错误:该店铺未授权!");
                continue;
            }

            $row->sid       = $sid;
            $row->is_manual = 1;
            $row->syn_time  = date('Y-m-d H:i:s', time());
            $task[]         = $row;

        }

        releaseDb($db);
        foreach ($task as &$stock) {
            do_syn_stock_impl($stock, 1,$fail);
        }
        logx("stock_cmd 批量库存同步操作成功，请查看同步记录!!", $sid . "/Stock");
        return;
    }

    /*
    top平台手动上架
    根据同步规则计算SKU的应该同步库存
    */
    function stock_onsale($sid, $recid_str,&$fail)
    {
        $db = getUserDb($sid);
        if(!$db)
        {
            logx("stock_cmd getUserDb failed!!", $sid.'/Stock');
            $fail[] = array("rec_id" => "", "goods_id" => "", "spec_id" => "", "msg" => "未知错误，请联系管理员");
            return;
        }

        $task = array();
        if($recid_str == ''){
            $res = $db->query("SELECT rec_id FROM api_goods_spec");
            while($row = $db->fetch_array($res)){
                $rec_array[] = $row['rec_id'];
            }
        }else{
            $rec_array=explode(",",$recid_str);
        }
        foreach ($rec_array as $k=>$recid)
        {
            $sql = " SELECT ag.*,sh.sub_platform_id,sh.account_id,sh.app_key,sh.auth_time "
                ." FROM api_goods_spec ag "
                ." LEFT JOIN cfg_shop sh ON ag.shop_id=sh.shop_id AND ag.platform_id=sh.platform_id "
                ." WHERE ag.rec_id in ('$recid') ";

            $row = $db->query_result($sql);
            if(empty($row))
            {
                logx("映射记录{$recid}不存在!!", $sid);
                $fail[] = array("rec_id" => "", "goods_id" => "", "spec_id" => "", "msg" => "错误:映射记录不存在!");
                continue;
            }

            $specid = $row['match_target_id'];
            $shoptype = $row['platform_id'];
            $shopid = $row['shop_id'];

            if(empty($specid))
            {
                logx("{$recid}待同步记录未关联单品或组合装!!", $sid);
                $goods_id = $row['goods_id'];
                $spec_id  = $row['spec_id'];
                $fail[]   = array("rec_id" => @$row['rec_id'], "goods_id" => @$goods_id, "spec_id" => @$spec_id, "msg" => "待同步记录未关联单品或组合装!");
                continue;
            }

            if(!in_array($shoptype, array(1,2,3)))
            {
                logx("{$recid} 该平台不支持上架操作!!", $sid);
                $goods_id = $row['goods_id'];
                $spec_id  = $row['spec_id'];
                $fail[]   = array("rec_id" => @$row['rec_id'], "goods_id" => @$goods_id, "spec_id" => @$spec_id, "msg" => "该平台不支持上架操作!");
                continue;
            }

            if($row['status']==1)
            {
                logx("{$recid} 该记录已经是onsale状态!!", $sid);
                $goods_id = $row['goods_id'];
                $spec_id  = $row['spec_id'];
                $fail[]   = array("rec_id" => @$row['rec_id'], "goods_id" => @$goods_id, "spec_id" => @$spec_id, "msg" => "已经是上架状态!");
                continue;
            }

            if(!checkAppKey($row))
            {
                logx("stock_cmd {$recid} 该店铺未授权!!", $sid);
                $goods_id = $row['goods_id'];
                $spec_id  = $row['spec_id'];
                $fail[]   = array("rec_id" => @$row['rec_id'], "goods_id" => @$goods_id, "spec_id" => @$spec_id, "msg" => "该店铺未授权!");
                continue;
            }


            $syn_stock =calc_syn_stock($sid,$db,$row);
            //这里$row 为对象
            if($syn_stock<=0 &&$row->stock_syn_min<=0)
            {
                logx("stock_cmd {$recid} 根据规则计算出同步库存数量小于等于0!", $sid);
                $goods_id = @$row->goods_id;
                $spec_id  = @$row->spec_id;
                $fail[]   = array("rec_id" => @$row->rec_id, "goods_id" => @$goods_id, "spec_id" => @$spec_id, "msg" => "根据规则计算出同步库存数量小于等于0!");
                continue;
            }

            //contemplate
            $row->sid = $sid;
            $row->is_manual = 1;
            $row->syn_time= date('Y-m-d H:i:s',time());

            $row->is_auto_listing=1;
            $row->syn_stock=$syn_stock;
            $row->stock_syn_min=$syn_stock;

            $task[]=$row;

        }


        releaseDb($db);
        foreach($task as &$stock)
        {
            do_syn_stock_impl($stock, 1,$fail);
        }

        logx("stock_cmd 批量上架操作成功，请查看同步记录!!", $sid.'/Stock');
        return;
    }

    /*
    系统设计的status字段都是针对每个单品SKU
    top平台手动下架
    多规格的的情况
    ItemUpdateDelistingRequest 会把全部numiid下架,弃用
    对无货规格如果要下架 同步0库存即可
    需要观察
    该接口暂时不用
    */
    function stock_offsale($sid, $recid_str,&$fail)
    {
        $db = getUserDb($sid);
        if (!$db) {
            logx("stock_cmd getUserDb failed!!", $sid . "/Stock");
            $fail[] = array("rec_id" => "", "goods_id" => "", "spec_id" => "", "msg" => "未知错误，请联系管理员");
            return;
        }

        $task      = array();
        $rec_array = explode(",", $recid_str);
        foreach ($rec_array as $k=>$recid) {
            $sql = " SELECT ag.*,sh.sub_platform_id,sh.account_id,sh.app_key,sh.auth_time "
                . " FROM api_goods_spec ag "
                . " LEFT JOIN cfg_shop sh ON ag.shop_id=sh.shop_id AND ag.platform_id=sh.platform_id "
                . " WHERE ag.rec_id='$recid' ";

            $row = $db->query_result($sql);
            if (empty($row)) {
                logx("映射记录{$recid}不存在!!", $sid . "/Stock");
                $fail[] = array("rec_id" => "", "goods_id" => "", "spec_id" => "", "msg" => "映射记录不存在!");
                continue;
            }

            $specid   = $row['match_target_id'];
            $shoptype = $row['platform_id'];
            $shopid   = $row['shop_id'];

            if (empty($specid)) {
                logx("{$recid}待同步记录未关联单品或组合装!!", $sid . "/Stock");
                $goods_id = $row['goods_id'];
                $spec_id  = $row['spec_id'];
                $fail[]   = array("rec_id" => @$row->rec_id, "goods_id" => @$goods_id, "spec_id" => @$spec_id, "msg" => "待同步记录未关联单品或组合装!");
                continue;
            }

            if (!in_array($shoptype, array(1, 2, 3))) {
                logx("{$recid} 该平台不支持下架操作!!", $sid . "/Stock");
                $goods_id = $row['goods_id'];
                $spec_id  = $row['spec_id'];
                $fail[]   = array("rec_id" => @$row->rec_id, "goods_id" => @$goods_id, "spec_id" => @$spec_id, "msg" => "该平台不支持下架操作!");
                continue;
            }

            if ($row['status'] == 2) {
                logx("{$recid} 该记录已经是offsale状态!!", $sid . "/Stock");
                $goods_id = $row['goods_id'];
                $spec_id  = $row['spec_id'];
                $fail[]   = array("rec_id" => @$row->rec_id, "goods_id" => @$goods_id, "spec_id" => @$spec_id, "msg" => "已经是下架状态!");
                continue;
            }

            if (!checkAppKey($row)) {
                logx("stock_cmd {$recid} 该店铺未授权!!", $sid . "/Stock");
                $goods_id = $row['goods_id'];
                $spec_id  = $row['spec_id'];
                $fail[]   = array("rec_id" => @$row->rec_id, "goods_id" => @$goods_id, "spec_id" => @$spec_id, "msg" => "该店铺未授权!");
                continue;
            }


            $row->sid               = $sid;
            $row->is_manual         = 1;
            $row->syn_time          = date('Y-m-d H:i:s', time());
            $row->is_auto_delisting = 1;
            $row->syn_stock         = 0;

            $task[] = $row;

        }


        releaseDb($db);
        foreach ($task as &$stock) {
            do_syn_stock_impl($stock, 1,$fail);
        }

        logx("stock_cmd 批量下架操作成功，请查看同步记录!!", $sid . "/Stock");
        return;


    }

    /**
     * @param $db
     * @param $sid
     * @param $msg
     * @author luyanfeng
     * 参照存储过程：ET_POST_DELIVER
     */
    public static function batchDealSysProcessBackground(&$db, $sid, &$msg = "") {
        try {
            $db->execute("START TRANSACTION");
            //查询出最新的库存修改信息
            $sql            = "SELECT IFNULL(MAX(rec_id),0) AS MaxProcessId FROM sys_process_background";
            $V_MaxProcessId = $db->multi_query($sql);
            $V_MaxProcessId = $db->fetch_array($V_MaxProcessId)["MaxProcessId"];
            //将单品和组合装的信息分别查询出来
            $sql             = "SELECT DISTINCT object_id FROM sys_process_background WHERE rec_id<={$V_MaxProcessId} AND type=1";
            $res_goods_spec  = $db->execute($sql);
            $sql             = "SELECT DISTINCT object_id FROM sys_process_background WHERE rec_id<={$V_MaxProcessId} AND type=2";
            $res_goods_suite = $db->execute($sql);
            while ($row = $db->fetch_array($res_goods_spec)) {
                $V_ObjectId = $row["object_id"];
                $sql        = "UPDATE api_goods_spec SET is_stock_changed=1,stock_change_count=stock_change_count+1 WHERE is_deleted=0 AND match_target_type=1 AND match_target_id={$V_ObjectId}";
                $db->execute($sql);
            }
            while ($row = $db->fetch_array($res_goods_suite)) {
                $V_ObjectId = $row["object_id"];
                $sql        = "UPDATE api_goods_spec SET is_stock_changed=1,stock_change_count=stock_change_count+1 WHERE is_deleted=0 AND match_target_type=2 AND match_target_id={$V_ObjectId}";
                $db->execute($sql);
            }
            $sql = "DELETE FROM sys_process_background WHERE rec_id<={$V_MaxProcessId}";
            $db->execute($sql);
            $db->execute("commit");
            logx("刷新库存同步信息成功", $sid . "/Stock");
        } catch (\Exception $e) {
            $db->execute('rollback');
            $msg = $e->getMessage();
            logx($msg, $sid . "/Stock");
        }
    }
}

?>