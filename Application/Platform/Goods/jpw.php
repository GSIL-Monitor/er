<?php
require_once(ROOT_DIR . '/Goods/utils.php');
require_once(TOP_SDK_DIR . '/jpw/JpwClient.php');

function jpwDownloadGoodsList($db, $shop, $mode, $condition, &$new_count, &$chg_count, &$error_msg)
{
	$sid = $shop->sid;
	$shopId = $shop->shop_id;
	$appkey = $shop->key;
	$appsecret = $shop->secret;
	$session = $shop->session;
	$start_time = 0;
	$end_time = 0;
	switch ($mode) {
		case 1: {
			$start_time = $condition;
			$end_time = time();
			
			logx("jpwDownloadGoodsList shopid:$shopId start_time:" .
				date('Y-m-d H:i:s', $start_time) . " end_time: " .
				date('Y-m-d H:i:s', $end_time), $sid.'/Goods');
			break;
		}
		case 4: {
			$condition = explode(',', $condition);
			$start_time = $condition[0];
			$end_time = $condition[1];
			logx("jpwDownloadGoodsList shopid:$shopId start_time:" .
				$start_time . " end_time: " .
				$end_time, $sid.'/Goods');
			$start_time = strtotime($start_time);
			$end_time = strtotime($end_time);
			break;
		}
	}
	$spec_list = array();
	$pagesize = 50;
	$apiParams = array(
		'field' => 'sgoodsid,sgoodsid_v2',
		'jPagesize' => $pagesize,
		'jPage' => 1,
		'type' => 'json',
		'token' => $shop->refresh_token,
		'jCusKey' => $session,
		'onsale_time' => $start_time . '|' . $end_time,
		'jType' => 'sgoods_list'
	);
	$jpw = new jpwClient();
	$jpw->secret = $appsecret;
	$retval = $jpw->execute($apiParams);
	if (API_RESULT_OK != jpwErrorTest($retval, $db, $shopId)) {
		if (10004 == intval(@$retval->info) || 10040 == intval(@$retval->info) || 10042 == intval(@$retval->info) || 10001 == intval(@$retval->info)) {
			releaseDb($db);
			refreshJpwToken($appkey, $appsecret, $shop);
			$error_msg['status'] = 0;
			$error_msg['info'] = $retval->error_msg;
			return TASK_OK;
		}
		$error_msg['status'] = 0;
		$error_msg['info'] = $retval->error_msg;
		logx("jpwDownloadGoodsList $shopId $retval->error_msg", $sid.'/Goods', 'error');
		return TASK_OK;
	}
	if ($retval->info == 10007 || (isset($retval->data->count) && $retval->data->count == 0)) {
		logx("jpw : {$shopId} ; count : 0 $retval->error_msg", $sid.'/Goods');
		return true;
	}
	$total_trades = $retval->data->count;//总条数
	logx("jpw{$shopId}total_results:$total_trades", $sid.'/Goods');
	$items = $retval->data->list;
	
	if ($total_trades <= $pagesize) {
		$num_iids = array();
		for ($j = 0; $j < count($items); $j++) {
			$num_iids[] = $items[$j]->sgoodsid_v2;
		}
		if (!downJpwGoodsDetail($db, $shop, $mode, $num_iids, $new_count, $chg_count, $error_msg)) {
			return false;
		}
	} else {
		$total_pages = ceil(floatval($total_trades) / $pagesize);
		logx("total_page $total_pages", $sid.'/Goods');
		for ($i = $total_pages; $i >= 1; $i--) {
			logx("page $i", $sid.'/Goods');
			$apiParams['jPage'] = $i;
			$retval = $jpw->execute($apiParams);
			if (API_RESULT_OK != jpwErrorTest($retval, $db, $shopId)) {
				logx(print_r($apiParams,true));
				if (10004 == intval(@$retval->info) || 10040 == intval(@$retval->info) || 10042 == intval(@$retval->info) || 10001 == intval(@$retval->info)) {
					releaseDb($db);
					refreshJpwToken($appkey, $appsecret, $shop);
					$error_msg['status'] = 0;
					$error_msg['info'] = $retval->error_msg;
					return TASK_OK;
				}
				$error_msg['status'] = 0;
				$error_msg['info'] = $retval->error_msg;
				logx("jpwDownloadGoodsList $shopId $retval->error_msg", $sid.'/Goods', 'error');
				return TASK_OK;
			}
			$items = $retval->data->list;
			$num_iids = array();
			for ($j = 0; $j < count($items); $j++) {
				$num_iids[] = $items[$j]->sgoodsid_v2;
			}
			if (!downJpwGoodsDetail($db, $shop, $mode, $num_iids, $new_count, $chg_count, $error_msg)) {
				return false;
			}
		}
	}
	if($mode == 1 )
	{
		$db->execute("INSERT INTO cfg_setting(`key`,`value`) VALUES('goods_sync_shop_{$shopId}','{$end_time}') ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)");
	}
	
	return true;
}

function downJpwGoodsDetail($db, &$shop, $mode ,&$num_iids, &$new_count, &$chg_count, $error_msg)
{
	$sid = $shop->sid;
	$appkey = $shop->key;
	$appsecret = $shop->secret;
	$session = $shop->session;
	$shopId = $shop->shop_id;
	if($mode == 3) $num_iids = array($num_iids);
	for ($i = 0; $i < count($num_iids); $i++) {
		$goods_id = $num_iids[$i];
		$jpw = new jpwClient();
		$apiParams = array(
			'jType' => 'sgoods_info',
			'jCusKey' => $session,
			'jSgoodsId_v2' => $goods_id,
			'field' => 'skuid,zid,fid,zid_value,fid_value,inventory,his_inventory,sales,sgoodsno,price,cprice,add_time,last_modified',
			'type' => 'json',
			'token' => $shop->refresh_token,
		);
		$jpw->secret = $appsecret;
		$retval = $jpw->execute($apiParams);
		if (API_RESULT_OK != jpwErrorTest($retval, $db, $shopId)) {
			logx(print_r($apiParams,true));
			if (10004 == intval(@$retval->info) || 10040 == intval(@$retval->info) || 10042 == intval(@$retval->info) || 10001 == intval(@$retval->info)) {
				releaseDb($db);
				refreshJpwToken($appkey, $appsecret, $shop);
				$error_msg['status'] = 0;
				$error_msg['info'] = $retval->error_msg;
				return TASK_OK;
			}
			$error_msg['status'] = 0;
			$error_msg['info'] = $retval->error_msg;
			logx("jpwDownloadGoodsList $shopId $retval->error_msg", $sid.'/Goods', 'error');
			return TASK_OK;
		}
		$item = $retval->data;
		if (!loadJpwGoodsDetailImpl($shopId, $item, $spec_list))
			continue;
		if (!putGoodsToDb($sid, $db, $spec_list, $new_count, $chg_count, $error_msg)) {
			return false;
		}
	}
	return true;
}

function loadJpwGoodsDetailImpl($shopId, &$item, &$spec_list)
{
	$spec = array
	(
		'status' => $item->sgoodsinfo->status == 43 ? 2 : 1,//43下架
		'platform_id' => 29,
		'shop_id' => $shopId,
		'goods_id' => trim($item->sgoodsinfo->sgoodsid_v2),
		'goods_name' => trim($item->sgoodsinfo->title),
		'pic_url' => trim($item->sgoodsinfo->pic_url),
		'spec_code' => '',
		'spec_name' => '',
		'spec_outer_id' => '',
		'is_stock_changed' => '1',
		'created' => date('Y-m-d H:i:s', time())
	);
	
	$skus = $item->list;
	if (empty($skus)) {
		$spec_list[] = $spec;
	} else {
		foreach ($skus as &$sku) {
			$nspec = $spec;
			$nspec['outer_id'] = iconv_substr(trim($sku->sgoodsno), 0, 40, 'UTF-8');
			$nspec['spec_id'] = $sku->skuid;
			$nspec['spec_code'] = '';
			$nspec['spec_name'] = $sku->zid_value . ' ' . $sku->fid_value;
			$nspec['spec_outer_id'] = '';
			$nspec['price'] = @$sku->price;
			$nspec['stock_num'] = @$sku->inventory;
			$spec_list[] = $nspec;
		}
	}
	return true;
}
