<?php
require_once(ROOT_DIR . '/Stock/utils.php');
require_once(ROOT_DIR . '/Manager/utils.php');
require_once(TOP_SDK_DIR . '/alibaba/AlibabaApi.class.php');

function aliGetSku($appkey, $appsecret, $session, $goodsId, $sid)
{
	$retval = AlibabaApi::getGoodsInfo($appkey, $appsecret, $session, $goodsId);
	if($retval->total == 0) return;
	return $retval->toReturn[0];
}

function alibaba_stock_syn(&$db, &$stock, $sid)
{
	getAppSecret($stock,$appkey,$appsecret);
	$shopid = $stock->shop_id;
	
	$skus_array = aliGetSku($appkey, $appsecret, $stock->session, $stock->goods_id, $sid);
	//同步前从接口获取实际库存量
	$skus = @$skus_array->skuArray;
	if(count($skus) > 0)
	{
		foreach($skus as $sku)
		{
			if(isset($sku->specId) && ($stock->spec_id == $sku->specId))
			{
				$syn_stock = $stock->syn_stock - $sku->canBookCount; //canBookCount 商品可销售量
				break;
			}
			if(isset($sku->children[0]->specId))
			{
				for($i=0; $i<count($sku->children); $i++)
				{
					if($stock->spec_id == $sku->children[$i]->specId)
					{
						$syn_stock = $stock->syn_stock - $sku->children[$i]->canBookCount;
						break 2;
					}
				}
			}
		}
		$sku_stock = '{"'. $stock->spec_id .'":"'. $syn_stock .'"}';
	}

	if(!isset($syn_stock))
	{
		if (isset($skus_array->amountOnSale))
		{
			logx("可售库存:".$skus_array->amountOnSale, $sid . "/Stock");
			if(isset($sku_stock))	logx("sku_stock:".print_r($sku_stock, true), $sid . "/Stock");
			$sku_stock = '';
			$syn_stock = $stock->syn_stock - $skus_array->amountOnSale;
		}
		else
		{
			logx("skus:".print_r($skus, true), $sid . "/Stock");
			if(isset($sku_stock))	logx("sku_stock:".print_r($sku_stock, true), $sid . "/Stock");
			$sku_stock = '';
			$syn_stock = 0;
		}
	}
	
	$retval = AlibabaApi::syncStock(
							$appkey, 
							$appsecret,
							$stock->session,
							$stock->goods_id,
							$syn_stock,
							$sku_stock);
	if(API_RESULT_OK != alibabaErrorTest($retval, $db, $shopid))
	{
		$error_msg = @$retval->error_msg;
		if(isset($retval->errors->IC_QUANTITY_LESS_THAN_ZERO))
		{
			$error_msg = "本次同步数量：$syn_stock " . $retval->errors->IC_QUANTITY_LESS_THAN_ZERO;
		}
		if(strpos(@$retval->errors->Throwable, "skuQuantityMap cat't be null or empty") !== FALSE ||
			strpos(@$retval->errors->ITEM_NOT_FOUND, "没有找到宝贝") !== FALSE ||
			strpos(@$retval->errors->IC_SAVE_INVENTORY_TO_IP_FAILED, "保存库存信息失败") !== FALSE ||
			strpos(@$retval->errors->ITEM_CONTAIN_SKU_CANNOT_MODIFY_QUANTITY, "宝贝含有销售属性，不能直接修改商品数量") !== FALSE)
		{
			syn_delete($db, $stock);
		}
		else
		{
			syn_log($db, $stock, 0, $error_msg);
		}
		
		logx("ALi同步库存失败, goods_id: {$stock->goods_id}, spec_id: {$stock->spec_id}, syn_stock: {$stock->syn_stock}, sku_stock: $syn_stock 失败原因: {$error_msg}", $sid . "/Stock");
		
		return SYNC_FAIL;
	}
	syn_log($db, $stock, 1, "");
	logx("ALi同步库存成功: goods_id: {$stock->goods_id}, spec_id: {$stock->spec_id}, syn_stock: {$stock->syn_stock}, sku_stock: $syn_stock", $sid . "/Stock");
	return SYNC_OK;						
}