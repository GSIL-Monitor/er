<?php
require_once(ROOT_DIR . '/Stock/utils.php');
require_once(ROOT_DIR . '/Manager/utils.php');
require_once(TOP_SDK_DIR . '/wm/newWmClient.php');

//库存同步
function weimo_stock_syn(&$db, &$stock, $sid)
{
	getAppSecret($stock, $appkey, $appsecret);

	$session = $stock->session;

	$client = new WmClient();
	$client->setSession($session);

	$stock_num = $stock->syn_stock;
	$api_action = 'wangpu/Inventory/Update';

	$updateType = "TOTAL"; // 全量更新TOTAL,增加更新CHANGED

	$param = array(
			'spu_code' => $stock->outer_id,
			'inventory' => $stock_num,
			'type' => $updateType
	);
	if (!empty($stock->spec_outer_id)) {
		$param['sku_list'] = array(
				array(
						'sku_code' => $stock->spec_outer_id,
						'inventory' => $stock_num
				),
		);
	}
	$param = json_encode($param);

	//$param = '{spu_code: "'.$stock->outer_id.'",inventory: '.$stock_num.',sku_list: [{sku_code: "'.$stock->spec_outer_id.'",inventory: '.$stock_num.'}],type: "'.$updateType.'"}';
	$retval = $client->execute($api_action, $param);
	if (API_RESULT_OK != weimoErrorTest($retval, $db, $stock->shop_id)) {
		$error_msg = $retval->error_msg;

		if ($retval->code->errcode == 80001001000119) {
			logx("weimob_stock_sync fail {$stock->shop_id} refreshWeimoToken error_msg:{$error_msg}", $sid.'/Stock');
			refreshWeimoToken($db, $stock);
			releaseDb($db);
			return SYNC_FAIL;
		}

		if (strpos($error_msg, '不存在') !== false) {
			logx("微盟同步库存失败,删除同步: NumIID: {$stock->goods_id}, SkuID: {$stock->spec_id}, SynStock: {$stock->syn_stock} 失败原因:{$error_msg}", $sid.'/Stock');
			syn_delete($db, $stock, $error_msg);
		} elseif (strpos($error_msg, '关闭了同步库存功能') !== false) {
			logx("微盟同步库存失败,停止同步: NumIID: {$stock->goods_id}, SkuID: {$stock->spec_id}, SynStock: {$stock->syn_stock} 失败原因:{$error_msg}", $sid.'/Stock');
			syn_disable($db, $stock, $error_msg);
		} else {
			logx("微盟库存同步失败 NumIID:{$stock->goods_id} SkuID:{$stock->spec_id} SynStock:{$stock->syn_stock} 失败原因:{$error_msg}", $sid.'/Stock');
			syn_log($db, $stock, 0, $error_msg);
		}

		return SYNC_FAIL;
	}

	syn_log($db, $stock, 1, '');

	logx("微盟库存同步成功 NumIID:{$stock->goods_id} SkuID:{$stock->spec_id} SynStock:{$stock->syn_stock}", $sid.'/Stock');

	return SYNC_OK;
}