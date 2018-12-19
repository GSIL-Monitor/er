<?php
namespace Platform\Manager;
require_once(ROOT_DIR . '/Manager/utils.php');
require_once(ROOT_DIR . '/Trade/util.php');
require_once(ROOT_DIR . "/Manager/Manager.class.php");
//保存需要执行递交的sid
$trade_handle_merchant = array();

class TradeSlowManager extends Manager{
	public static function register(){
		registerHandle('trade_slow_merchant', array('\\Platform\\Manager\\TradeSlowManager', 'listTradeShops'));
		registerHandle('trade_shop', array('\\Platform\\Manager\\TradeSlowManager', 'downloadTradeList'));
		registerHandle('trade_get', array('\\Platform\\Manager\\TradeSlowManager', 'tradeTradesDetail'));
		registerHandle('trade_deliver', array('\\Platform\\Manager\\TradeSlowManager', 'tradeDeliverTrade'));
		registerHandle('trade_down_span', array('\\Platform\\Manager\\TradeSlowManager', 'tradeDownloadSpan'));
		registerBeforeExit(array('\\Platform\\Manager\\TradeSlowManager', 'tradeBeforeComplete'));
	}

	public static function TradeSlow_main(){
		return enumAllMerchant('trade_slow_merchant');
	}

	public static function tradeBeforeComplete($tube, $complete){
		if($tube != 'TradeSlow')
			return;

		deleteJob();

		global $trade_handle_merchant;

		foreach($trade_handle_merchant as $sid => $v) {
			pushTask('trade_deliver', $sid, 0, 2048, 600, 300);
		}
	}

	public static function listTradeShops($sid){
		global $g_use_jst_sync;
		deleteJob();

		$db = getUserDb($sid);

		if(!$db) {
			logx("TradeSlow getShops getUserDb failed!!", $sid. "/TradeSlow");
			return TASK_OK;
		}

		$is_updating = $db->query_result_single("SELECT NOT IS_FREE_LOCK(CONCAT('sys_update_', DATABASE()))");
		if($is_updating) {
			releaseDb($db);
			logx("merchant is updating", $sid. "/TradeSlow");
			return TASK_OK;
		}

		$autoDownload =getSysCfg($db, 'order_auto_download', 0);

		if(!$autoDownload) {
			releaseDb($db);
			logx("not auto download!", $sid . "/TradeSlow");
			return TASK_OK;
		}

		/*上次有未递交成功的*/
		$hasTradeToDeliver = getSysCfg($db, 'order_should_deliver', 0);
		if($hasTradeToDeliver) {
			logx("Redeliver trades!", $sid. "/TradeSlow");
			pushTask('trade_deliver', $sid, 0, 2048, 600, 300);
			setSysCfg($db, 'order_should_deliver', 0);
		}
		//amazon alibaba ECShop vipshop mls ECstore jpw flw
		$result = $db->query("select * ".
				" from cfg_shop ".
				" where auth_state=1 and is_disabled=0 and platform_id in (5,9,14,17,29,32,34,37,47,50,53,56,60) ");
		if(!$result) {
			releaseDb($db);
			logx("query shop failed!", $sid. "/TradeSlow");
			return TASK_OK;
		}
		while($row = $db->fetch_array($result)) {
			//过滤掉不抓单的店铺
			if(isset($row['is_undownload_trade']) && $row['is_undownload_trade'] == 1) {
				continue;
			}
			if(!checkAppKey($row)) {
				continue;
			}
			$row->sid = $sid;
			pushTask('trade_shop', $row, 0, 1024, 600, 300);
		}
		$db->free_result($result);
		releaseDb($db);

		return TASK_OK;
	}

	public static function downloadTradeList($shop) {
		global $g_use_jst_sync;
		deleteJob();

		$sid = $shop->sid;
		$shopId = $shop->shop_id;

		$db = getUserDb($sid);
		if(!$db)
		{
			logx("downloadTradeList getUserDb failed!!", $sid.'/TradeSlow');
			return TASK_OK;
		}

		$now = time();

		$interval = (int)getSysCfg($db, 'order_sync_interval', 30);
		$delay = (int)getSysCfg($db, 'order_delay_interval', 2);
		//是否延时下载
		//淘宝JST可以减少延时
		if($g_use_jst_sync && $shop->push_rds_id && !empty($shop->account_nick) && ($shop->platform_id==1 || $shop->platform_id==2)) {
			$delayMinite = 1;
			$interval = 5;
		} else {
			//其它平台10分钟
			$delayMinite = $delay>10?$delay:10;

			//夜间延时加长
			$da = getdate($now);
			if($da['hours'] >= 2 && $da['hours'] <= 7)
				$delayMinite = $delay>30?$delay:30;
		}
		//京东到家O2O外卖
		/*if($shop->platform_id==40){
			$delayMinite = 0;
		}*/

		//隐藏配置，为特殊卖家开启(需要缩短抓单时间)
		/*$api_slow_trade_hide_cfg = getSysCfg($db, 'api_slow_trade_hide_cfg', 0);
		if($api_slow_trade_hide_cfg){
			$delayMinite = 5;
			if(in_array($shop->platform_id,array(127,10)))
				$delayMinite = 1;
		}*/

		$endTime = $now - $delayMinite*60;

		$postfix = '';
		if(isset($shop->order_type)) $postfix = "_{$shop->order_type}";
		$startTime = (int)getSysCfg($db, "order_last_synctime_{$shopId}{$postfix}", 0);

		//检查有没到时间间隔
		if($startTime>0) {
			if($now - $startTime > 2592000) //最长下载30days
				$startTime = $now - 2592000;
			else
				$startTime -= 1;

			if($interval < 30) $interval = 30;
			if($interval > 60) $interval = 60;

			//隐藏配置，为特殊卖家开启(需要缩短抓单时间)
			/*if($api_slow_trade_hide_cfg && in_array($shop->platform_id,array(127,9,10,47))){
				$interval = 5;
			}
			//京东到家外卖订单
			if($shop->platform_id==40){
				$interval = 1;
			}*/
			$lastTime = $startTime + $delayMinite*60;

			if($lastTime + $interval*60 > $now)
			{
				releaseDb($db);
				return TASK_OK;
			}
			$authTime = strtotime($shop->auth_time);
			if($startTime<$authTime)
				$firstTime = true;
			else
				$firstTime = false;
		} else {
			//最后下载时间没设置的话，下载最近三天
			$startTime = $now - 259200;
			$firstTime = true;
		}

		//无需下载
		if($startTime >= $endTime) {
			releaseDb($db);
			logx("Need not scan trade!! {$shopId}", $sid.'/TradeSlow');
			return TASK_OK;
		}

		$result = self::startDownloadTradeList($db, $sid, $shop, $startTime, $endTime, $firstTime, true);
		releaseDb($db);

		return $result;
	}

	public static function startDownloadTradeList($db, $sid, &$shop, $startTime, $endTime, $firstTime, $saveTime)
	{
		global $trade_handle_merchant, $g_use_jst_sync;

		$trade_handle_merchant[$sid] = 1;

		$shopId = $shop->shop_id;
		$type='auto';
		//取得appsecret
		logx($sid.'开始下载订单 shop_id:'.$shopId,$sid.'/TradeSlow');
		getAppSecret($shop, $appkey, $appsecret);
		$error_msg ='';
		if ($endTime - $startTime > 3600) {
			logx(" tradeSlow :platform_id: ".$shop->platform_id."shop_id: ".$shopId." sid: ".$sid, 'SY');
		}
		//开始下载
		switch($shop->platform_id)
		{
			case 5: //亚马逊
			{
				require_once(ROOT_DIR . '/Trade/amazon.php');

				$result = amazonDownloadTradeList(
						$db,
						$appkey,
						$appsecret,
						$shop,
						$startTime,
						$endTime,
						$saveTime,
						'trade_get',
						$error_msg);
				break;
			}
			case 9: //阿里巴巴
			{
				require_once(ROOT_DIR . '/Trade/alibaba.php');
				/*
                * 阿里巴巴接口不稳定 有时会漏单
                * 作为补充 增加非增量抓单
                * 8-20点期间 每4个小时调用
                */
				$h = intval(date('H',time()));
				if($h == 8 || $h == 12 || $h == 16 || $h == 20)
				{
					$startTime = $endTime - 3600*24*2;
					$result = aliDownloadTradeList($db, $appkey, $appsecret, $shop,0, $startTime, $endTime, $saveTime, 'trade_get', $new_trade_count, $chg_trade_count, $error_msg);
				}
				else
				{
					$result = aliDownloadTradeList($db, $appkey, $appsecret, $shop,0, $startTime, $endTime, $saveTime, 'trade_get', $new_trade_count, $chg_trade_count, $error_msg);
				}
				break;
			}
			case 14: //vipshop
			{
				require_once(ROOT_DIR . '/Trade/vipshop.php');
				$startTime = $endTime - 3600*48;
				$result = vipshopDownloadTradeList($db, $appkey, $appsecret, $shop, 0, $startTime, $endTime, $saveTime, $total_trade_count, $new_trade_count, $chg_trade_count, $error_msg);

				if(TASK_OK == $result)
				{
					logx("log_vipshop {$shopId} new $new_trade_count chg $chg_trade_count", $sid.'/TradeSlow');
				}
				break;
			}
			case 17: //kdt
			{
				require_once(ROOT_DIR . '/Trade/kdt.php');
				$startTime = $endTime - 3600*24*3;
				$result = kdtDownloadTradeList(
						$db,
						$appkey,
						$appsecret,
						$shop,
						0,
						$startTime,
						$endTime,
						$saveTime,
						$total_trade_count,
						$new_trade_count,
						$chg_trade_count,
						$error_msg
				);

				if(TASK_OK == $result)
				{
					logx("log_kdt {$shop->shop_id} new $new_trade_count chg $chg_trade_count", $sid . "/Trade");
				}

				return $result;
			}
			case 20:	//mls
			{
				require_once(ROOT_DIR.'/Trade/mls.php');
				//$startTime = $endTime - 3600*24*3;
				$result = meilishuoTradeList(
						$db,
						$appkey,
						$appsecret,
						$shop,
						0,
						$startTime,
						$endTime,
						$saveTime,
						$new_trade_count,
						$chg_trade_count,
						$error_msg);

				if(TASK_OK == $result)
				{
					logx("log_mls {$shop->shop_id} new $new_trade_count chg $chg_trade_count", $sid.'/TradeSlow');
				}
				return $result;
			}
			case 29://卷皮网
			{
				require_once(ROOT_DIR .'/Trade/jpw.php');
				$startTime = $endTime - 3600*24*3;
				$result = jpwDownloadTradeList($db, $appkey, $appsecret, $shop,0, $startTime, $endTime, $saveTime,$total_trade_count, $new_trade_count, $chg_trade_count, $error_msg);
				if(TASK_OK == $result)
				{
					logx("log_jpw {$shopId} new $new_trade_count chg $chg_trade_count", $sid.'/TradeSlow');
				}
				break;
			}
			case 32://微店
			{
				$shop->key = $appkey;
				$shop->secret = $appsecret;

				require_once(ROOT_DIR . '/Trade/vdian.php');

				$result = vdianDownloadTradeList(
						$db,
						$shop,
						$startTime,
						$endTime,
						$saveTime,
						'trade_get',
						$total_count,
						$error_msg);

				/*
                    * 微店接口不稳定 有时会漏单
                    * 作为补充 增加非增量抓单
                    * 6-23点期间 整点前10分钟调用
                    */
				$hh = intval(date('H',time()));
				$ii = intval(date('i',time()));
				if(($hh>=18 || $hh<6) && $ii<40){
					$t1 = time() - 3600*12;
					$t2 = time() - 3600*10;
					$startTime = $t1;
					$endTime = $t2;
					//$startTime = $endTime - 3600*10;
					$saveTime = false;
					$result = vdianDownloadTradeList(
							$db,
							$shop,
							$startTime,
							$endTime,
							$saveTime,
							'trade_get',
							$total_count,
							$error_msg);
				}
				break;
			}
			case 33: //拼多多
			{
				require_once(ROOT_DIR . '/Trade/pdd.php');

				if($firstTime)
				{
					$result = pddDownloadTradeList($db, $shop, $appkey, $appsecret, $startTime, $endTime, $saveTime,$total_trade_count, $new_trade_count, $chg_trade_count, $error_msg);
					if(TASK_OK == $result)
					{
						logx("log_pdd $shopId scan $total_trade_count new $new_trade_count chg $chg_trade_count", $sid.'/TradeSlow');
					}
				}else{
					$result = pddTradeList(
							$db,
							$shop,
							$appkey,
							$appsecret,
							$startTime,
							$endTime,
							$saveTime,
							'trade_get',
							$total_count,
							$error_msg,
							$type);
				}
				break;
			}
			case 34://蜜芽宝贝
			{
				require_once(ROOT_DIR .'/Trade/mia.php');
				$result = miaDownloadTradeList($db, $appkey, $appsecret, $shop,0, $startTime, $endTime, $saveTime,$total_trade_count, $new_trade_count, $chg_trade_count, $error_msg);
				if(TASK_OK == $result)
				{
					logx("log_mia {$shopId} new $new_trade_count chg $chg_trade_count", $sid.'/TradeSlow');
				}
				break;
			}
			case 37: //速卖通
			{
				require_once(ROOT_DIR . '/Trade/smt.php');
				$startTime = $endTime - 3600*24*3;
				$result = smtDownloadTradeList($db, $appkey, $appsecret, $shop,0, $startTime, $endTime, $saveTime, 'trade_get', $total_trade_count, $new_trade_count, $chg_trade_count, $error_msg);
				break;
			}
			case 47://微吧人人店
			{
				require_once(ROOT_DIR.'/Trade/rrd.php');
				$startTime = $endTime - 3600*24*3;
				$result = rrdDownloadTradeList(
						$db,
						$appkey,
						$appsecret,
						$shop,
						$startTime,
						$endTime,
						$saveTime,
						'trade_get',
						$total_count,
						$error_msg);
				break;
			}
			case 50://考拉
			{
				require_once(ROOT_DIR.'/Trade/kl.php');
				$startTime = $endTime - 3600*24*3;
				$result = klDownloadTradeList($db, $appkey, $appsecret, $shop,0, $startTime, $endTime, $saveTime, $total_count, $new_trade_count, $chg_trade_count, $error_msg);
				if (TASK_OK == $result) {
					logx("log_kaola {$shopId} new $new_trade_count chg $chg_trade_count", $sid.'/TradeSlow');
				}
				break;
			}
			case 53: //楚楚街拼团
			{
				require_once(ROOT_DIR.'/Trade/ccjpt.php');
				$startTime = $endTime - 3600*24*3;
				$result = ccjptDownloadTradeList($db, $appkey, $appsecret, $shop, $startTime, $endTime, $saveTime, $total_count, $new_trade_count, $chg_trade_count, $error_msg);
				if (TASK_OK == $result) {
					logx("log_ccjpt {$shopId} new $new_trade_count chg $chg_trade_count", $sid.'/TradeSlow');
				}
				break;
			}
			case 56://小红书
			{
				require_once(ROOT_DIR.'/Trade/xhs.php');
				$startTime = $endTime - 3600*24*3;
				$result = xhsDownloadTradeList($db, $appkey, $appsecret, $shop, $startTime, $endTime, $saveTime, 'trade_get', $total_count, $error_msg);
				if (TASK_OK == $result) {
					logx("log_xhs {$shopId} total_count: $total_count", $sid.'/TradeSlow');
				}
				break;
			}
            case 60://返利网
            {
                require_once(ROOT_DIR.'/Trade/flw.php');
                $result = flwDownloadTradeList($db, $appkey, $appsecret, $shop, $startTime, $endTime, $saveTime, 'trade_get', $total_count, $error_msg);
                if (TASK_OK == $result) {
                    logx("log_flw {$shopId} total_count: $total_count", $sid.'/TradeSlow');
                }
                break;
            }
			/*case 10: //ecshop
			{
				require_once(ROOT_DIR . '/Trade/ecshop.php');
				$appkey = $shop->key;
				$appsecret = $shop->secret;
				//$api_url = $shop->session;
				//ecshop下载最近3天
				$startTime = $endTime - 3600*24*3;
				$result = ecsDownloadTradeList($db,$appkey,$appsecret, $shop,0,$startTime, $endTime, $saveTime,$total_trade_count,$new_trade_count, $chg_trade_count, $error_msg);
				if(TASK_OK == $result)
				{
					logx("log_ecs {$shopId} new $new_trade_count chg $chg_trade_count", $sid);
				}
				break;
			}
			case 23: //ecstore
			{
				require_once(ROOT_DIR . '/Trade/ecstore.php');

				$result = ecstoreDownloadTradeList($db, $appkey, $appsecret, $shop, $startTime, $endTime, $saveTime, 'trade_get', $error_msg);

				break;
			}
			case 30: //顺丰嘿客
			{
				require_once(ROOT_DIR . '/Trade/sf.php');

				//无增量接口
				//抓3天
				$startTime = $endTime - 3600*24*3;
				$result = sfDownloadTradeList(
						$db,
						$appkey,
						$appsecret,
						$shop,
						0,
						$startTime,
						$endTime,
						$saveTime,
						$total_trade_count,
						$new_trade_count,
						$chg_trade_count,
						$error_msg);

				if(TASK_OK == $result)
				{
					logx("log_sf {$shopId} new $new_trade_count chg $chg_trade_count", $sid);
				}
				break;
			}
			case 32://微店
			{
				$shop->key = $appkey;
				$shop->secret = $appsecret;

				require_once(ROOT_DIR . '/Trade/vdian.php');
				$result = vdianDownloadTradeList(
						$db,
						$shop,
						$startTime,
						$endTime,
						$saveTime,
						'trade_get',
						$total_count,
						$error_msg);

				if (1 == $shop->sub_platform_id){
					$startTime = $endTime - 3600*24*3;
					require_once(ROOT_DIR . '/modules/trade_sync/vdian_wei.php');
					$result = vdianweiDownloadTradeList(
							$db,
							$shop,
							$startTime,
							$endTime,
							$saveTime,
							'trade_get',
							$total_count,
							$error_msg);
				}
				break;
			}
			case 40: //京东到家
			{
				if($shop->is_hold_enabled) $shop->o2o_push = 1;
				//$shop->o2o_push = 1;
				require_once(ROOT_DIR .'/Trade/jddj.php');
				$endTime = time();
				$startTime = $endTime - 3600*24*1;
				$result = jddjTradeList($db, $appkey, $appsecret, $shop,0, $startTime, $endTime, $saveTime,$total_trade_count, $new_trade_count, $chg_trade_count, $error_msg);
				if(TASK_OK == $result)
				{
					logx("log_jddj {$shopId} new $new_trade_count chg $chg_trade_count", $sid);
				}
				break;
			}

			case 49://91拼团
			{
				require_once(ROOT_DIR.'/Trade/91pt.php');
				$startTime = $endTime - 3600*24*3;
				$result = pintuanDownloadTradeList($db, $appkey, $appsecret, $shop, $startTime, $endTime, $saveTime, $total_count, $new_trade_count, $chg_trade_count, $error_msg);
				if(TASK_OK == $result)
				{
					logx("log_91pintuan {$shopId} new $new_trade_count chg $chg_trade_count", $sid);
				}
				break;
			}
			case 51://千米
			{
				require_once(ROOT_DIR.'/Trade/qm.php');
				$startTime = $endTime - 3600*24*3;
				$result = qmDownloadTradeList($db, $appkey, $appsecret, $shop, $startTime, $endTime, $saveTime, $total_count, $new_trade_count, $chg_trade_count, $error_msg);
				if (TASK_OK == $result) {
					logx("log_qianmi {$shopId} new $new_trade_count chg $chg_trade_count", $sid);
				}
				break;
			}
			case 127: //自有平台
			{
				if(!file_exists(ROOT_DIR .'/modules/trade_self.php') || (int)$shop->sub_platform_id == 0) return TASK_OK;

				require_once(ROOT_DIR .'/modules/trade_self.php');
				$shop->start_time = $startTime;
				$shop->end_time = $endTime;
				selfShopList($shop);
				return TASK_OK;
			}*/
			default:
			{
				$result = TASK_OK;
			}
		}

		return $result;
	}

	public static function tradeTradesDetail($trades){
		//logx($trades->platform_id);
		$sid = $trades->sid;
		$db = getUserDb($sid);
		if(!$db) {
			logx("tradeTrade getUserDb failed!!", $sid.'/TradeSlow');
			return TASK_SUSPEND;
		}

		//取得appsecret
		getAppSecret($trades, $appkey, $appsecret);

		$scan_count = 0;
		switch($trades->platform_id) {
			case 1: //淘宝
			{
				require_once(ROOT_DIR . '/Trade/top.php');

				$result = downTopTradesDetail(
						$db,
						$appkey,
						$appsecret,
						$trades,
						$scan_count,
						$new_trade_count,
						$chg_trade_count,
						$error_msg);

				if(TASK_OK == $result) {
					logx("log_top {$trades->shop_id} scan $scan_count new $new_trade_count chg $chg_trade_count", $trades->sid.'/TradeSlow');
				}
				break;
			}
			//2淘宝分销、3京东, 16聚美, 下载列表时直接完成订单下载
			/*case 4: //拍拍
			{
				require_once(ROOT_DIR . '/modules/trade_sync/paipai.php');

				$result = downPaipaiTradesDetail(
						$db,
						$appkey,
						$appsecret,
						$trades,
						$scan_count,
						$new_trade_count,
						$chg_trade_count,
						$error_msg);

				if(TASK_OK == $result)
				{
					logx("log_pp {$trades->shop_id} scan $scan_count new $new_trade_count chg $chg_trade_count", $trades->sid);
				}
				break;
			}*/
			case 5: //亚马逊
			{
				require_once(ROOT_DIR . '/Trade/amazon.php');

				$result = downAmazonTradesDetail(
						$db,
						$appkey,
						$appsecret,
						$trades,
						$scan_count,
						$new_trade_count,
						$chg_trade_count,
						$error_msg);

				if(TASK_OK == $result)
				{
					logx("log_amazon {$trades->shop_id} scan $scan_count new $new_trade_count chg $chg_trade_count", $trades->sid.'/TradeSlow');
				}

				break;
			}
			/*case 6: //一号店
			{
				require_once(ROOT_DIR . '/Trade/yhd.php');

				$result = downYhdTradesDetail(
						$db,
						$appkey,
						$appsecret,
						$trades,
						$new_trade_count,
						$chg_trade_count,
						$error_msg);

				if(TASK_OK == $result)
				{
					logx("log_yhd {$trades->shop_id} new $new_trade_count chg $chg_trade_count", $trades->sid);
				}
				break;
			}*/
			/*case 7: //当当
			{
				require_once(ROOT_DIR . '/Trade/dangdang.php');


				$result = downDdTradesDetail(
						$db,
						$appkey,
						$appsecret,
						$trades,
						$scan_count,
						$new_trade_count,
						$chg_trade_count,
						$error_msg);

				if(TASK_OK == $result)
				{
					logx("log_dd {$trades->shop_id} new $new_trade_count chg $chg_trade_count", $trades->sid.'/TradeSlow');
				}
				break;
			}*/
			case 9: //阿里
			{
				require_once(ROOT_DIR . '/Trade/alibaba.php');

				$result = downAliTradesDetail(
						$db,
						$appkey,
						$appsecret,
						$trades,
						$new_trade_count,
						$chg_trade_count,
						$error_msg);

				if(TASK_OK == $result)
				{
					logx("log_ali {$trades->shop_id} new $new_trade_count chg $chg_trade_count", $trades->sid.'/TradeSlow');
				}
				break;
			}
			case 17: //kdt
			{
				require_once(ROOT_DIR . '/Trade/kdt.php');

				$result = downKdtTradesDetail(
						$db,
						$appkey,
						$appsecret,
						$trades,
						$new_trade_count,
						$chg_trade_count,
						$error_msg);

				if(TASK_OK == $result)
				{
					logx("log_kdt {$trades->shop_id} new $new_trade_count chg $chg_trade_count", $trades->sid.'/Trade');
				}
				break;
			}
			/*case 23: //ecstore
			{
				require_once(ROOT_DIR . '/modules/trade_sync/ecstore.php');

				$result = ecstoreDownloadTradeDetail(
						$db,
						$appkey,
						$appsecret,
						$trades,
						$new_trade_count,
						$chg_trade_count,
						$error_msg);

				if(TASK_OK == $result)
				{
					logx("log_ecstore {$trades->shop_id} new $new_trade_count chg $chg_trade_count", $trades->sid);
				}
				break;
			}
			case 32: //微店
			{
				$trades = shopTransformation($trades);
				$trades->key = $appkey;
				$trades->secret = $appsecret;


				if (1 == $trades->vdianwei){
					require_once(ROOT_DIR . '/modules/trade_sync/vdian_wei.php');
					$result = vdianweiDownloadTradeDetail(
							$db,
							$trades,
							$scan_count,
							$new_trade_count,
							$chg_trade_count,
							$error_msg);
					if(TASK_OK == $result)
					{
						logx("log_vdian {$trades->shop_id} scan $scan_count new $new_trade_count chg $chg_trade_count vdianwei:".$trades->vdianwei, $trades->sid);
					}
				}else{
					require_once(ROOT_DIR . '/modules/trade_sync/vdian.php');
					$result = vdianDownloadTradeDetail(
							$db,
							$trades,
							$scan_count,
							$new_trade_count,
							$chg_trade_count,
							$error_msg);
					if(TASK_OK == $result)
					{
						logx("log_vdianwei {$trades->shop_id} scan $scan_count new $new_trade_count chg $chg_trade_count vdianwei:".$trades->vdianwei, $trades->sid);
					}
				}
				break;
			}*/
			case 32: //微店
			{
				$trades->key = $appkey;
				$trades->secret = $appsecret;
				require_once(ROOT_DIR . '/Trade/vdian.php');
				$result = vdianDownloadTradeDetail(
						$db,
						$trades,
						$scan_count,
						$new_trade_count,
						$chg_trade_count,
						$error_msg);
				if(TASK_OK == $result)
				{
					logx("log_vdian {$trades->shop_id} scan $scan_count new $new_trade_count chg $chg_trade_count", $trades->sid.'/TradeSlow');
				}
				break;
			}
			case 33://拼多多
			{
				require_once(ROOT_DIR . '/Trade/pdd.php');
				$result = pddTradeDetail(
						$db,
						$trades,
						$appkey,
						$appsecret,
						$scan_count,
						$new_trade_count,
						$chg_trade_count,
						$error_msg);

				if(TASK_OK == $result)
				{
					logx("log_pdd {$trades->shop_id} scan $scan_count new $new_trade_count chg $chg_trade_count", $trades->sid.'/TradeSlow');
				}
				break;
			}

			case 37: //速卖通
			{
				require_once(ROOT_DIR . '/Trade/smt.php');

				$result = downSmtTradesDetail(
						$db,
						$appkey,
						$appsecret,
						$trades,
						$new_trade_count,
						$chg_trade_count,
						$error_msg);

				if(TASK_OK == $result)
				{
					logx("log_smt {$trades->shop_id} new $new_trade_count chg $chg_trade_count", $trades->sid.'/TradeSlow');
				}
				break;
			}
			case 47://微吧人人店
			{
				require_once(ROOT_DIR.'/Trade/rrd.php');
				$result = downRrdTradesDetail(
						$db,
						$appkey,
						$appsecret,
						$trades,
						$scan_count,
						$new_trade_count,
						$chg_trade_count,
						$error_msg);
				if(TASK_OK == $result)
				{
					logx("log_rrd {$trades->shop_id} new $new_trade_count chg $chg_trade_count", $trades->sid.'/TradeSlow');
				}
				break;
			}
			case 50://考拉
			{
				require_once(ROOT_DIR.'/Trade/kl.php');
				$result = downKlTradesDetail($db, $appkey, $appsecret, $trades, $scan_count, $new_trade_count, $chg_trade_count, $error_msg);
				if (TASK_OK == $result) {
					logx("log_kaola {$trades->shop_id} new $new_trade_count chg $chg_trade_count",$trades->sid.'/TradeSlow');
				}
				break;
			}
			case 56: //小红书
			{
				require_once(ROOT_DIR . '/Trade/xhs.php');

				$result = downXhsTradesDetail(
						$db,
						$appkey,
						$appsecret,
						$trades,
						$new_trade_count,
						$chg_trade_count,
						$error_msg);

				if(TASK_OK == $result)
				{
					logx("log_xhs {$trades->shop_id} new $new_trade_count chg $chg_trade_count", $trades->sid.'/TradeSlow');
				}
				break;
			}
            case 60: //返利网
            {
                require_once(ROOT_DIR.'/Trade/flw.php');

                $result = downflwTradesDetail(
                    $db,
                    $appkey,
                    $appsecret,
                    $trades,
                    $new_trade_count,
                    $chg_trade_count,
                    $error_msg);

                if(TASK_OK == $result)
                {
                    logx("log_flw {$trades->shop_id} new $new_trade_count chg $chg_trade_count", $trades->sid.'/TradeSlow');
                }
                break;
            }
			/*
			case 49://91拼团
			{
				require_once(ROOT_DIR.'/modules/trade_sync/91pt.php');
				$new_trade_count = 0;
				$chg_trade_count = 0;
				$result = downPintuanTradeDetail(
						$db,
						$appkey,
						$appsecret,
						$trades,
						$scan_count,
						$new_trade_count,
						$chg_trade_count,
						$error_msg);
				if (TASK_OK == $result) {
					logx("log_91pintuan {$trades->shop_id} new $new_trade_count chg $chg_trade_count",$trades->sid);
				}
				break;
			}
			case 51://千米
			{
				require_once(ROOT_DIR.'/modules/trade_sync/qm.php');
				$result = downQmTradesDetail($db, $appkey, $appsecret, $trades, $scan_count, $new_trade_count, $chg_trade_count, $error_msg);
				if (TASK_OK == $result) {
					logx("log_qianmi {$trades->shop_id} new $new_trade_count chg $chg_trade_count",$trades->sid);
				}
				break;
			}*/
			default:
			{
				$result = TASK_OK;
			}
		}

		releaseDb($db);
		return $result;
	}

	public static function tradeDeliverTrade($sid)
	{
		$db = getUserDb($sid);
		$error_msg = '';
		if(!$db)
		{
			logx("tradeDeliverTrade getUserDb failed!!", $sid.'/TradeSlow');
			return TASK_OK;
		}

		$hasTradeToDeliver = getSysCfg($db, 'order_auto_submit', 0);
		if(!$hasTradeToDeliver){
			logx('未开启自动递交',$sid.'/TradeSlow');
			releaseDb($db);
			return TASK_OK;
		}

		logx($sid.'开始执行慢抓单递交',$sid.'/TradeSlow');
		deliverMerchantTrades($db, $error_msg, $sid);
		releaseDb($db);

		return TASK_OK;
	}

}

