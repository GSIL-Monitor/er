<?php
namespace Platform\Manager;
/*
根据JST 推送信息
从C店 TAMLL获取平台库存,平台货品上下架状态信息,链接是否删除信息
*/
require_once(ROOT_DIR . '/Manager/utils.php');
require_once(ROOT_DIR . "/Manager/Manager.class.php");

class GoodsManager extends Manager {
    //put task to all the sids
    public static function Goods_main() {
        return enumAllMerchant('goods_merchant');
    }

    // get all shops for the sid
    public static function listGoodsShops($sid) {
        deleteJob();

        $db = getUserDb($sid);
        if (!$db) {
            logx("$sid getShops getUserDb failed in listGoodsShops!!", $sid . "/Goods",'error');
            return TASK_OK;
        }

        $goods_auto_download = $db->query_result_single("SELECT `value` FROM cfg_setting WHERE `key`='goods_auto_download'", 0);
        if ($goods_auto_download == 0) {
            logx("not goods_auto_download", $sid . "/Goods");
            return TASK_OK;
        }

        $result = $db->query("SELECT shop_id,platform_id,sub_platform_id,account_id,account_nick,push_rds_id,app_key,wms_check,auth_time FROM cfg_shop WHERE auth_state=1 and is_disabled=0 AND platform_id=1");

        if (!$result) {
            releaseDb($db);
            logx("$sid query shop failed in listGoodsShops!", $sid . "/Goods",'error');
            return TASK_OK;
        }

        while ($row = $db->fetch_array($result)) {
            //if $g_debug_shopid is set, the checkAppKey will use it
            if (!checkAppKey($row))
                continue;

            $row        = (array)$row;
            $row['sid'] = $sid;
            pushTask('goods_shop', $row);
        }

        $db->free_result($result);
        releaseDb($db);

        return TASK_OK;
    }

    //check the start, end and delay time
    public static function downloadGoodsList($shop) {
        deleteJob();
        $sid     = $shop->sid;
        $shop_id = $shop->shop_id;

        $db = getUserDb($sid);
        if (!$db) {
            logx("$sid downloadGoodsList getUserDb failed!!", $sid . "/Goods",'error');
            return TASK_OK;
        }

        $delayMinite = 1;

        $now = time();
        if ($delayMinite < 10) {
            $da = getdate($now);
            if ($da['hours'] >= 2 && $da['hours'] <= 5)
                $delayMinite = 10;
        }

        if ($delayMinite < 1) $delayMinite = 1;

        $endTime = $now - $delayMinite * 60;

        $startTime = $db->query_result_single("SELECT `value` FROM cfg_setting WHERE `key`='stock_last_platdown_time{$shop_id}'", 0);

        //检查有没到时间间隔
        if ($startTime > 0) {
            if ($now - $startTime > 3600 * 24 * 30) //最长30days
                $startTime = $now - 3600 * 24 * 30;
            else
                $startTime -= 1;

            $interval = $db->query_result_single("SELECT `value` FROM cfg_setting WHERE `key`='stock_platdown_intevel'", 1);
            if ($interval < 1) $interval = 1;

            $lastTime = $startTime + $delayMinite * 60;

            if ($lastTime + $interval * 60 > $now) {
                releaseDb($db);

                return TASK_OK;
            }
        } else {
            //最后下载时间没设置的话，下载最近三天
            $startTime = $now - 3600 * 24 * 3;
        }

        releaseDb($db);

        //无需下载
        if ($startTime >= $endTime) {
            logx("Need not scan goods!! {$shop_id}", $sid . "/Goods");
            return TASK_OK;
        }

        return self::startDownloadGoodsList($sid, $shop, $startTime, $endTime, true, $message);
    }

    public static function startDownloadGoodsList($sid, &$shop, $startTime, $endTime, $saveTime, &$message) {
        global $g_use_jst_sync;
        //开始下载
        switch ($shop->platform_id) {
            case 1: //淘宝天猫
            {
                if (!empty($shop->account_nick)) //使用聚石塔
                {
                    require_once(ROOT_DIR . '/Goods/jst.php');
                    return jstTopDownloadGoodsList($shop, $startTime, $endTime, $saveTime, $message);
                } else {
                    logx(" only support jst in startDownloadGoodsList!", $sid . "/Goods");
                    break;
                }
            }
            default: {
                logx(" not support shop type in startDownloadGoodsList!", $sid . "/Goods");
                break;
            }

        }

        return TASK_OK;
    }

    //自动调用的时候应该首先调用该方法,注册类内部的方法
    public static function register() {
        registerHandle('goods_merchant', array('\\Platform\\Manager\\GoodsManager', 'listGoodsShops'));
        registerHandle('goods_shop', array('\\Platform\\Manager\\GoodsManager', 'downloadGoodsList'));
    }

    //手动下载货品的入口
    public function manualSync($shopId, &$message, $conditions = null) {
        $shop = array();
        $res = parent::sync_auth_check($shopId, $shop, $message);
        if($res['status']==0 && $res['info']!=''){
            return false;
        }
        /*if (!parent::sync_auth_check($shopId, $shop, $message)) {
            E($message['info']);
            return;
        }*/
        getAppSecret($shop, $key, $secret);
        $shop->key    = $key;
        $shop->secret = $secret;
        $sid          = get_sid();
        $shop->sid    = $sid;
        $db           = getUserDb($sid);
        $new_count    = 0;
        $chg_count    = 0;
        $platform_id  = $shop->platform_id;
        switch ($platform_id) {
            case 1: //淘宝
                $param = $this->judge($conditions);
                if ($conditions["radio"] == 4) {
                    releaseDb($db);
                    self::startDownloadGoodsList($sid,$shop, strtotime($conditions["start"]), strtotime($conditions["end"]), true, $message);
                }else if($conditions["radio"] == 1){
                    releaseDb($db);
                    $start_time = $param;
                    $end_time = time();
                    self::startDownloadGoodsList($sid,$shop, $start_time, $end_time, true, $message);

                }else {
                    require_once(ROOT_DIR . "/Goods/top.php");
                    topDownloadGoodsList($db, $shop, $conditions["radio"], $param, $new_count, $chg_count, $message);
                }
                break;
            case 2: //淘宝分销
            {
                require_once(ROOT_DIR . '/Goods/top_fx.php');
                if($conditions["radio"] != 2){
                    $param = $this->judge($conditions);
                    topFenxiaoDownloadGoodsList($db, $shop, $conditions["radio"], $param, $new_count, $chg_count, $message);
                }else{
                    $message["status"] = 0;
                    $message["info"] = "淘宝分销不支持按货品名称下载";
                }

                break;
            }
            case 3: //京东
                require_once(ROOT_DIR . "/Goods/jos.php");
                $param = $this->judge($conditions);
                josDownloadGoodsList($db, $shop, $conditions["radio"], $param, $new_count, $chg_count, $message);
                break;
            case 5: //亚马逊
                $message['status'] = 0;
                $message['info'] = '亚马逊不支持货品下载';
                break;
            case 7: //当当
            {
                require_once(ROOT_DIR . '/Goods/dangdang.php');
                $param = $this->judge($conditions);
                if($conditions["radio"] == 1)
                {
                    ddDownloadGoodsList($db, $shop, $conditions["radio"], $param, $new_count, $chg_count, $message);
                }
                else if($conditions["radio"] == 3)
                {
                    downDdGoodsDetailById($db, $shop, $conditions["radio"], $param, $new_count, $chg_count, $message);
                }
                else
                {
                    $message['info'] = '当当不支持按时间段和货品名称下载';
                    $message['status'] = 0;
                }
                break;
            }
            case 8:  //国美
            {
                require_once(ROOT_DIR . '/Goods/coo8.php');
                if($conditions['radio']!=2){
                    $param = $this->judge($conditions);
                    coo8DownloadGoodsList($db, $shop, $conditions["radio"], $param, $new_count, $chg_count, $message);
                } else {
                    $message["status"] = 0;
                    $message["info"] = "国美不支持按货品名称下载";
                }
                break;
            }
            case 9: //alibaba
            {
                require_once(ROOT_DIR . '/Goods/alibaba.php');
                if ($conditions['radio'] == 1) {
                    alibabaDownloadGoodsList($db, $shop, $new_count, $chg_count, $message);
                }elseif ($conditions['radio'] == 3) {
                    $param = $this->judge($conditions);
                    downAliGoodsDetailById($db, $shop, $conditions["radio"], $param, $new_count, $chg_count, $message);
                } else {
                    $message["status"] = 0;
                    $message["info"] = "阿里巴巴只支持新增/修改货品下载和按货品ID下载";
                }

                break;
            }
            case 13://苏宁
            {
                require_once(ROOT_DIR . '/Goods/sos.php');
                if ($conditions['radio'] ==3 || $conditions['radio']==1) {
                    $param = $this->judge($conditions);
                    sosDownloadGoodsList($db, $shop, $conditions["radio"], $param, $new_count, $chg_count, $message);
                } else{
                    $message["status"] = 0;
                    $message["info"] = "苏宁只支持新增/修改货品下载和按货品ID下载";
                }
                break;
            }
            case 14: //唯品会
            {
                $message['status'] = 0;
                $message['info'] = '唯品会不支持货品下载';
                break;
            }
            case 17: //kdt
            {
                require_once(ROOT_DIR . '/Goods/kdt.php');
                if ($conditions["radio"] != 4) {
                    $param  = $this->judge($conditions);
                    $result = kdtDownloadGoodsList($db, $shop, $conditions["radio"], $param, $new_count, $chg_count, $error_msg);
                } else {
                    $message["status"] = 0;
                    $message["info"]   = "有赞（口袋通）不支持按时间下载";
                }
                break;
            }
            case 20: //美丽说
            {
                require_once(ROOT_DIR . '/Goods/mls.php');
                $param = $this->judge($conditions);
                if ($conditions["radio"] == 3) {
                    downMeilishuoGoodsDetailById($db, $shop, $conditions["radio"], $param, $new_count, $chg_count, $message);
                }elseif ($conditions["radio"] == 1){
                    meilishuoDownloadGoodsList($db, $shop, $conditions["radio"], $param, $new_count, $chg_count, $message);
                } else {
                    $message["status"] = 0;
                    $message["info"]   = "美丽说只支持新增/修改货品下载和按货品ID下载";
                }
                break;
            }
            case 22: //贝贝网
            {
                require_once(ROOT_DIR . '/Goods/bbw.php');
                if ($conditions['radio'] == 1) {
                    bbwDownloadGoodsList($db, $shop, $new_count, $chg_count, $message);
                }else {
                    $message["status"] = 0;
                    $message["info"] = "贝贝网只支持新增/修改货品下载";
                }
                break;
            }
            case 24://折800
            {
                require_once(ROOT_DIR . '/Goods/zhe800.php');
                $param = $this->judge($conditions);
                if($conditions['radio'] == 1 || $conditions['radio'] == 2)
                {
                    zheDownloadGoodsList($db, $shop, $conditions['radio'], $param, $new_count, $chg_count, $message);
                }
                elseif($conditions['radio'] == 3)
                {
                    zheGoodsDetail($db, $shop, $conditions['radio'], $param, $new_count, $chg_count, $message);
                }
                else
                {
                    $message["status"] = 0;
                    $message["info"] = "折800不支持按时间下载";

                }

                break;
            }
            case 25: //融e购
            {
                require_once(ROOT_DIR . '/Goods/icbc.php');
                if ($conditions["radio"] != 2) {
                    $param  = $this->judge($conditions);
                    icbcDownloadGoodsList($db, $shop, $conditions["radio"], $param, $new_count, $chg_count, $message);
                } else {
                    $message["status"] = 0;
                    $message["info"]   = "融e购不支持货品名称下载";
                }
                break;
            }
            case 27: //楚楚街
            {
                require_once(ROOT_DIR . '/Goods/ccj.php');
                $param = $this->judge($conditions);
                $result = ccjDownloadGoodsList($db, $shop, $conditions["radio"], $param, $new_count, $chg_count, $error_msg);
                break;
            }

            case 28: // 微盟旺店
            {
                require_once(ROOT_DIR . '/Goods/weimo.php');
                $param = $this->judge($conditions);
                if ($conditions["radio"] == 1)
                {
                    weimoDownloadGoodsList($db, $shop, $conditions["radio"], $param, $new_count, $chg_count, $message);
                }
                else
                {
                    $message['status'] = 0;
                    $message['info'] = '微盟只支持新增/修改货品下载';
                }
                break;
            }
            case 29: //卷皮网
            {
                require_once(ROOT_DIR . '/Goods/jpw.php');
                $param  = $this->judge($conditions);
                if($conditions['radio']==4 || $conditions['radio']==1)
                {
                    jpwDownloadGoodsList($db, $shop, $conditions['radio'], $param, $new_count, $chg_count, $message);
                }
                else if($conditions['radio'] == 3)
                {
                    downJpwGoodsDetail($db, $shop, $conditions['radio'], $param,  $new_count, $chg_count, $message);
                }
                else
                {
                    $message['status'] = 0;
                    $message['info'] = '卷皮网不支持按货品名称下载';
                }
                break;
            }
            case 31: //飞牛
            {

                require_once(ROOT_DIR . '/Goods/fn.php');
                $param  = $this->judge($conditions);
                if ($conditions['radio']==4 || $conditions['radio']==1)
                {
                    fnDownloadGoodsList($db, $shop, $conditions['radio'], $param, $new_count, $chg_count, $message);
                }elseif ($conditions['radio'] == 3)
                {
                    fnDownloadGoodsDetail($db, $shop, $conditions['radio'], $param, $new_count, $chg_count, $message);
                }
                else
                {
                    $message['status'] = 0;
                    $message['info'] = '飞牛不支持货品名称下载';
                }
                break;
            }
            case 32: //微店
            {
                require_once(ROOT_DIR . '/Goods/vdian.php');
                $param = $this->judge($conditions);

                if ($conditions['radio'] == 1 || $conditions['radio'] == 4)
                {
                    vdianDownloadGoodsList($db, $shop, $conditions['radio'], $param, $new_count, $chg_count, $message);
                }
                else if($conditions['radio'] == 3)
                {
                    vdianDownloadGodosDetail($db, $shop, $conditions['radio'], $param, $new_count, $chg_count, $message);
                }
                else
                {
                    $message['status'] = 0;
                    $message['info'] = '微店不支持按货品名称下载';
                }
                break;
            }
            case 33: //拼多多
            {
                require_once(ROOT_DIR . '/Goods/pdd.php');
                $param = $this->judge($conditions);

                if ($conditions['radio']==1 || $conditions['radio']==2 ){
                   pddDownloadGoodsList($db, $shop, $conditions['radio'], $param, $new_count, $chg_count, $message);
                }
                else
                {
                    $message['status'] = 0;
                    $message['info'] = '拼多多只支持新增/修改货品下载和按货品名称下载';
                }
                break;
            }
            case 34: //蜜芽宝贝
            {
                $message['status'] = 0;
                $message['info'] = '蜜芽宝贝不支持货品下载';
                break;
            }
            case 36: //善融商城
            {
                $message['status'] = 0;
                $message['info'] = '善融商城不支持货品下载';
                break;
            }
            case 37: //速卖通
            {
                $message['status'] = 0;
                $message['info'] = '速卖通不支持货品下载';
                break;
            }
            case 47: //人人店
            {
                require_once(ROOT_DIR . '/Goods/rrd.php');
                $param = $this->judge($conditions);
                if($conditions['radio']==2 || $conditions['radio']==1){
                    rrdDownloadGoodsList($db, $shop, $conditions['radio'], $param, $new_count, $chg_count, $message);
                }else if ($conditions['radio'] == 3) {
                    rrdDownloadGoodsDetail($db, $shop, $new_count, $chg_count, $message, $param);
                }
                else
                {
                    $message['status'] = 0;
                    $message['info'] = '人人店不支持按时间下载';
                }
                break;
            }
            case 50: //考拉
            {
                $message['status'] = 0;
                $message['info'] = '网易考拉海购不支持货品下载';
                break;
            }
            case 53: //楚楚街拼团
            {
                $message['status'] = 0;
                $message['info'] = '楚楚街拼团不支持货品下载';
                break;
            }
            case 56: //小红书
            {
                require_once(ROOT_DIR . '/Goods/xhs.php');
                $param = $this->judge($conditions);
                if (($conditions['radio']== 1 || $conditions['radio'] == 4))
                {
                    xhsDownloadGoodsList($db, $shop, $conditions['radio'], $param, $new_count, $chg_count, $message);
                }
                else
                {
                    $message['status'] = 0;
                    $message['info'] = '小红书不支持按货品名称或货品ID下载';
                }
                break;
            }
            case 60: //返利网
            {
                $message['status'] = 0;
                $message['info'] = '返利网不支持货品下载';
                break;
            }
            default:
                $message["status"] = 0;
                $message["info"]   = "未知条件下载货品";
        }
        if ($message["status"] == 0 && $message["info"] == "") {
            $message["status"] = 1;
            $message["info"]   = "下载完成：新增货品数量" . $new_count . " 更新货品数量" . $chg_count;
        }
        return;
    }

    //判断下载的方式，返回需要的参数
    function judge($conditons) {
        switch ($conditons["radio"]) {
            case 1:
                return strtotime("-1 year");
            case 2:
                return $conditons["goods_name"];
            case 3:
                return $conditons["goods_id"];
            case 4:
                return $conditons["start"] . "," . $conditons["end"];
        }
    }

}

?>