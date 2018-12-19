<?php
require_once(ROOT_DIR . '/Trade/util.php');
require_once(TOP_SDK_DIR . '/wm/newWmClient.php');
//weimob 订单下载

//单条订单下载
function downWeimoTradesDetail(&$db, $trades, &$scan_count, &$new_trade_count, &$chg_trade_count, &$error_msg)
{
	$new_trade_count = 0;
	$chg_trade_count = 0;

	$trade_list = array();
	$order_list = array();
	$discount_list = array();

	$sid = $trades->sid;
	$shopid = $trades->shop_id;
	$tids = &$trades->tids;

	$session = $trades->session;
	//微盟新版上线通知授权失效--临时处理
	if (empty($session)) {
		markShopAuthExpired($db, $shopid);
		releaseDb($db);
		logx("wm_sync_trade_detail shop not auth {$shopid}!!", $sid.'/Trade');
		return TASK_OK;
	}
	$client = new WmClient();
	$client->setSession($session);

	$param = array(
			'order_no' => '',
			'need_distribution_info' => 'false',    //是否需要分销信息
	);

	$api_action = 'wangpu/Order/FullInfoGetHighly'; //查看单个订单详情-效率高

	for ($i = 0; $i < count($tids); $i++) {
		++$scan_count;
		$tid = $tids[$i];
		$param['order_no'] = $tid;  //订单编号

		$retval = $client->execute($api_action, $param);
		if (API_RESULT_OK != weimoErrorTest($retval, $db, $shopid)) {
			$error_msg['status'] = 0;
			$error_msg['info'] = $retval->error_msg;
			if($retval->code->errcode == 80001001000119 || $retval->code->errcode == 8000101)
			{
				logx("weimoDownloadTradeDetail {$retval->code->errcode}:{$retval->error_msg} need refreshweimoToken shop_id:{$shopid}", $sid.'/Trade');

				refreshweimoToken($db, $trades);
				return TASK_OK;
			}else{
				logx("downWeimobTradesDetail {$shopid} {$tid} failed error_msg: {$error_msg['info']}", $sid.'/Trade');
			}
			return TASK_OK;
		}

		if (!isset($retval->data->order_no) || !$retval->data) {
			logx("downWeimobTradesDetail {$shopid} {$tid} data is null " . print_r($retval, true), $sid.'/Trade');
			continue;
		}

		if (!loadWeimobTradeImpl($db, $trades, $retval->data, $trade_list, $order_list, $discount_list)) {
			return TASK_SUSPEND;
		}

		if (count($order_list) > 100) {
			if (!putTradesToDb($db, $trade_list, $order_list, $discount_list, $new_trade_count, $chg_trade_count, $error_msg, $sid)) {
				return TASK_SUSPEND;
			}
		}
	}

	if (count($order_list) > 0) {
		if (!putTradesToDb($db, $trade_list, $order_list, $discount_list, $new_trade_count, $chg_trade_count, $error_msg, $sid)) {
			return TASK_SUSPEND;
		}
	}

	return TASK_OK;
}

//同步下载订单列表
function weimoSyncDownloadTradeList(&$db, $shop, $countLimit, $start_time, $end_time, &$scan_count, &$total_new, &$total_chg, &$error_msg)
{
	$scan_count = 0;
	$total_new = 0;
	$total_chg = 0;
	$error_msg = '';
	$cbp = function (&$trades) use (&$db, $shop, $countLimit, &$scan_count, &$total_new, &$total_chg, &$error_msg) {
		downWeimoTradesDetail($db, $trades, $scan_count, $new_trade_count, $chg_trade_count, $error_msg);

		$total_new += $new_trade_count;
		$total_chg += $chg_trade_count;

		return ($scan_count < $countLimit);
	};

	return weimobDownloadTradeListImpl($db, $shop, $start_time, $end_time, false, $total_count, $error_msg, $cbp);
}

//异步下载订单列表
function weimoDownloadTradeList(&$db, $shop, $start_time, $end_time, $save_time, $trade_detail_cmd, &$total_count, &$error_msg)

{
	$cbp = function (&$trades) use ($trade_detail_cmd) {
		pushTask($trade_detail_cmd, $trades);
		return true;
	};

	return weimobDownloadTradeListImpl($db, $shop, $start_time, $end_time, $save_time, $total_count, $error_msg, $cbp);
}

//下载订单列表
function weimobDownloadTradeListImpl(&$db, $shop, $start_time, $end_time, $save_time, &$total_count, &$error_msg, $cbp)
{

	$ptime = $end_time;
	$loop_count = 0;
	$page_no = 1;
	$page_size = 40;
	$new_trade_count = 0;
	$chg_trade_count = 0;
	$total_trade_count = 0;

	if ($save_time)
		$save_time = $end_time;

	$sid = $shop->sid;
	$shopid = $shop->shop_id;
	$session = $shop->session;
	//微盟新版上线通知授权失效--临时处理
	if (empty($session)) {
		markShopAuthExpired($db, $shopid);
		releaseDb($db);
		logx("wm_sync_trade_list shop not auth {$shopid}!!", $sid.'/Trade');
		return TASK_OK;
	}
	logx("weimobDownloadTradeListImpl $shopid start_time:" . date('Y-m-d H:i:s', $start_time) . " end_time:" . date('Y-m-d H:i:s', $end_time), $sid.'/Trade');

	$client = new WmClient();
	$client->setSession($session);

	$api_action = 'wangpu/Order/GetHighly';

	$trade_list = array();
	$order_list = array();
	$discount_list = array();

	while ($ptime > $start_time) {
		$ptime = ($ptime - $start_time > 3600 * 24) ? ($end_time - 3600 * 24 + 1) : $start_time;
		$loop_count++;
		//if ($loop_count > 1) resetAlarm();

		$update_begin_time = date("Y-m-d H:i:s", $ptime);
		$update_end_time = date("Y-m-d H:i:s", $end_time);
		$param = array(
				'order_status' => '',
				'pay_status' => '',
				'delivery_status' => '',
				'update_begin_time' => $update_begin_time,
				'update_end_time' => $update_end_time,
				'page_size' => $page_size,
				'page_no' => $page_no,
		);

		$retval = $client->execute($api_action, $param);
		if (API_RESULT_OK != weimoErrorTest($retval, $db, $shopid)) {
			$error_msg['status'] = 0;
			$error_msg['info'] = $retval->error_msg;

			if($retval->code->errcode == 80001001000119 || $retval->code->errcode == 8000101)
			{
				logx("weimoDownloadTradeList {$retval->code->errcode}:{$retval->error_msg} need refreshweimoToken shop_id:{$shopid}", $sid.'/Trade');

				refreshweimoToken($db, $shop);
				return TASK_OK;
			}

			if ($retval->code->errcode == 204 || $error_msg['info'] == '无数据') {
				$end_time = $ptime + 1;
				logx("weimobDownloadTradeListImpl {$shopid} count: 0 msg:" . $retval->error_msg, $sid.'/Trade');
				continue;
			}
			// 授权失效？

			logx("weimobDownloadTradeListImpl failed, error_msg: {$error_msg['info']}", $sid.'/Trade');
			logx("ERROR $sid weimobDownloadTradeListImpl, error_msg: {$error_msg['info']}",$sid.'/Trade', 'error');
			return TASK_OK;
		}

		if (empty($retval->data) || !isset($retval->data->page_data) || count($retval->data->page_data) == 0) {
			$end_time = $ptime + 1;
			logx("weimobDownloadTradeListImpl $shopid count: 0", $sid.'/Trade');
			continue;
		}

		$trades = $retval->data->page_data;
		//总条数
		$total_results = intval($retval->data->row_count);
		logx("weimobDownloadTradeListImpl total $shopid count: $total_results", $sid.'/Trade');

		//如果不足一页，则不需要再抓了
		if ($total_results <= $page_size) {
			$tids = array();
			for ($j = 0; $j < count($trades); $j++) {
				$tids[] = $trades[$j]->order_no;
			}
			if (count($tids) > 0) {
				$shop->tids = $tids;
				if (!$cbp($shop))
					return TASK_SUSPEND;
			}
		} else //超过一页，第一页抓的作废，从最后一页开始抓
		{
			$total_pages = ceil(floatval($total_results) / $page_size);
			for ($i = $total_pages; $i >= 1; $i--) {
				$param['page_no'] = $i;
				$retval = $client->execute($api_action, $param);
				if (API_RESULT_OK != weimoErrorTest($retval, $db, $shopid)) {
					$error_msg['status'] = 0;
					$error_msg['info'] = $retval->error_msg;
					logx("weimobDownloadTradeListImpl fail {$shopid} current_page: {$i} error_msg: {$error_msg}", $sid.'/Trade');
					logx("ERROR $sid weimobDownloadTradeListImpl, error_msg: {$error_msg['info']}",$sid.'/Trade', 'error');
					return TASK_OK;
				}

				$trades = $retval->data->page_data;

				$tids = array();
				for ($j = 0; $j < count($trades); $j++) {
					$tids[] = $trades[$j]->order_no;
				}
				if (count($tids) > 0) {
					$shop->tids = $tids;
					if (!$cbp($shop)) {
						return TASK_SUSPEND;
					}
				}
			}
		}

		$end_time = $ptime + 1;
	}
	//保存剩下的到数据库
	if (count($order_list) > 0) {
		if (!putTradesToDb($db, $trade_list, $order_list, $discount_list, $new_trade_count, $chg_trade_count, $error_msg, $sid)) {
			return TASK_SUSPEND;//状态为：任务挂起，下一次订时再处理
		}
	}

	if ($save_time) {
		logx("order_last_synctime_{$shopid}".'上次抓单时间保存 weimo平台 '.print_r($save_time,true),$sid. "/default");
		setSysCfg($db, "order_last_synctime_{$shopid}", $save_time);
	}

	return TASK_OK;
}

//订单模板
function loadWeimobTradeImpl($db, $trades, &$trade, &$trade_list, &$order_list, &$discount_list)
{
	global $zhi_xia_shi;
	$sid = $trades->sid;
	$platform_id = $trades->platform_id;
	$shop_id = $trades->shop_id;

	$t = &$trade;
	$tid = $t->order_no;

	// 订单过滤
	$is_filter_fx = true;
	if ($is_filter_fx && false === in_array($t->order_source, array('旺铺', '不发货分销商'))) {
		logx("loadWeimobTradeImpl filter fenxiao order  order_no:{$tid} order_source:{$t->order_source}", $sid.'/Trade');
		return true;
	}

	// 自提订单？
	//
	// 拼团订单
	if (isset($t->activity_type) && $t->activity_type == 3) {
		if ($t->pt_status != 4) {
			// 拼团还未成功
			logx("loadWeimobTradeImpl filter pintuan order  order_no:{$tid} order_source:{$t->order_source} pt_status:{$t->pt_status}", $sid.'/Trade');
			return true;
		}
	}
	$delivery_term = 1; // 发货条件 1款到发货 2货到付款(包含部分货到付款) 3分期付款
	if (isset($t->is_onlinepay) && !$t->is_onlinepay) {
		$delivery_term = 2;
	}    // 货到付款

	$receiver_address = @$t->receiver_address;// 收货地址
	$receiver_city = @$t->receiver_region->city;            // 城市
	$receiver_district = @$t->receiver_region->district;    // 区县
	$receiver_state = @$t->receiver_region->province;    // 省份

	$receiver_mobile = @$t->receiver_tel;        // 手机
	$receiver_name = @$t->receiver_name;        // 姓名
	$receiver_phone = '';    // 电话


	// 将地址中省市区去掉
	$prefix = $receiver_state . $receiver_city . $receiver_district;
	$len = iconv_strlen($prefix, 'UTF-8');
	if (iconv_substr($receiver_address, 0, $len, 'UTF-8') == $prefix)
		$receiver_address = iconv_substr($receiver_address, $len, 256, 'UTF-8');

	$receiver_state = wmProvince($receiver_state);

	if (in_array($receiver_state, $zhi_xia_shi)) {
		$receiver_district = $receiver_city;
		$receiver_city = $receiver_state . '市';
	}

	if (!empty($receiver_district)) {
		$receiver_area = "$receiver_state $receiver_city $receiver_district";
	} else {
		$receiver_area = "$receiver_state $receiver_city";
	}

	getAddressID($receiver_state, $receiver_city, $receiver_district, $province_id, $city_id, $district_id);

	// 订单状态
	$trade_status = 10;        // 10未确认 20待尾款 30待发货 40部分发货 50已发货 60已签收 70已完成 80已退款 90已关闭,付款前取消
	$process_status = 70;    // 处理：10待递交 20已递交 30部分发货 40已发货 50部分结算 60已完成 70已取消
	$pay_status = 0;        // 0未付款 1部分付款 2已付款
	// trade退款状态
	$trade_refund_states = 0;    // 0无退款 1申请退款 2部分退款 3全部退款
	// order退款状态
	$order_refund_status = 0;    // 0无退款1取消退款,2已申请退款,3等待退货,4等待收货,5退款成功

	switch ($t->order_status) {
		case 0:    // 所有
		{
			$process_status = 10;
			logx("invalid_trade_status tid:{$tid} process_status:{$process_status} trade_status:{$trade_status} order_status:{$t->order_status} pay_status:{$t->pay_status} delivery_status:{$t->delivery_status}", $sid.'/Trade');
			break;
		}
		case 1: // 交易中
		{
			if ($t->pay_status == 0) // 支付状态(0待支付，1已支付)
			{
				$process_status = 10;
			} elseif ($t->pay_status == 1) {
				$trade_status = 30;
				$process_status = 10;
				$pay_status = 2;
			}

			if ($t->delivery_status == 0 && $delivery_term == 2)  // 物流状态(0待发货，1卖家发货,2买家收货)
			{
				$trade_status = 30;
			} elseif ($t->delivery_status == 1) {
				$trade_status = 50;
			} elseif ($t->delivery_status == 2) {
				$trade_status = 60;
			}
			break;
		}
		case 2: // 交易成功
		{
			$trade_status = 70;
			break;
		}
		case 3: // 交易关闭
		{
			$trade_status = 90;
			break;
		}
		default: {
			logx("weimob_invalid_trade_status $shop_id $tid {$t->order_status}", $sid.'/Trade');
			logx("ERROR $sid weimob_invalid_trade_status $tid {$t->order_status}",$sid.'/Trade', 'error');
			break;
		}
	}
	if($t->pay_status == 1){
		$pay_status = 2;
	}

	$post_fee = $t->delivery_amount;  // 运费
	$trade_total_fee = $t->goods_amount;  // 商品总金额
	$discount = bcadd($t->discount_amount, $t->coupon_amount);
	$trade_discount_money = bcsub(0, floatval($discount));    // 涨价或降价处理
	// 应收金额 = 交易总价格  - 优惠价格  + 邮费
	$receivable = bcadd(bcadd($trade_total_fee, $trade_discount_money), $post_fee);
	// 分摊费用处理
	$left_post = $post_fee;
	$left_share_discount = $trade_discount_money;

	$goods = &$t->order_details;
	$orderId = 1;
	$order_arr = array();
	$order_count = count($goods);
	$goods_count = 0;    // 总货品数量
	$refund_count = 0;    // 退款子单数量-部分退款

	for ($k = 0; $k < $order_count; $k++) {
		$g = &$goods[$k];
		$oid = 'WM' . $tid . '_' . $g->sku_id;    // 订单编号
		if (isset($order_arr[$oid])) {
			$oid .= '_' . $orderId;
			++$orderId;
		}
		$order_arr[$oid] = 1;

		$order_num = $g->qty;        // 购买数量
		$order_price = $g->sale_price;    // 原价
		$order_discount_money = bcmul(bcsub($order_price, $g->price), $order_num);
		//$adjust_fee

		$goods_count = bcadd($goods_count, $order_num);
		$goods_fee = bcmul($order_price, $order_num);

		if ($k == $order_count - 1) {
			$share_discount = $left_share_discount;
			$share_post = $left_post;
		} else {
			$share_discount = bcdiv(bcmul($trade_discount_money, $goods_fee), $trade_total_fee);
			$left_share_discount = bcsub($left_share_discount, $share_discount);

			$share_post = bcdiv(bcmul($post_fee, $goods_fee), $trade_total_fee);
			$left_post = bcsub($left_post, $share_post);
		}
		$share_amount = bcadd($goods_fee, $share_discount);

		if (2 == $delivery_term) {
			$order_paid = 0;
		} else {
			$order_paid = bcadd($share_amount, $share_post);
		}
		//维权
		if (@$g->return_id > 0) {
			//1申请退款->10商家同意退款->微盟支付处理中->3已退款。
			//已发货申请退货退款状态： 2申请退款退货->4商家同意->等待退货->5商家收到退货->10微盟支付退款中->7已退款。
			//适用于申请退款或退货退款的： 8商家拒绝维权，9取消维权)
			if ($g->return_status == 1 || $g->return_status == 2) {
				// 申请退款/退货
				$trade_refund_states = 1;
				$order_refund_status = 2;
			} elseif ($g->return_status == 4) {
				// 商家同意等待退货
				$trade_refund_states = 1;
				$order_refund_status = 3;
			} elseif ($g->return_status == 3 || $g->return_status == 7) {
				// 已退款
				$refund_count++;

				$trade_refund_states = 3;
				$order_refund_status = 5;
			} elseif ($g->return_status == 8 || $g->return_status == 9) {
				// 拒绝/取消
				$trade_refund_states = 0;
				$order_refund_status = 1;
			}

		}

		$order_list[] = array
		(
				"platform_id" => $platform_id,    // 平台id
				'shop_id' => $shop_id,
				"tid" => $tid,                // 交易编号
				"oid" => iconv_substr($oid, 0, 40, 'UTF-8'),    // 订单编号
				"status" => $trade_status,    // 状态
				"refund_status" => $order_refund_status,
				"goods_id" => @$g->spu_id,    // 平台货品id
				"spec_id" => @$g->sku_id,        // 规格id
				"goods_no" => iconv_substr(trim(@$g->spu_code), 0, 40, 'UTF-8'),    // 商家编码
				"spec_no" => iconv_substr(trim(@$g->sku_code), 0, 40, 'UTF-8'),    // 规格商家编码
				"goods_name" => @$g->sku_name,    // 货品名
				"spec_name" => @$g->sku_description,        // 规格名
				'num' => $order_num,
				'price' => $order_price,

				'discount' => $order_discount_money,  // 优惠金额
				'total_amount' => $goods_fee,    // 分摊前扣除优惠货款num*price+adjust-discount
				'share_amount' => $share_amount,

				'share_post' => floatval($share_post),// 分摊邮费

				'adjust_amount' => $share_discount,  // 分摊优惠--相当于手工调价
				'paid' => $order_paid,

				'created' => array('NOW()')
		);

		if (bccomp($order_discount_money, 0)) {
			$discount_list[] = array
			(
					'platform_id' => $platform_id,
					'tid' => $tid,
					'oid' => '',
					'sn' => '',
					'type' => '',
					'name' => '商品优惠',
					'is_bonus' => 0,
					'detail' => '',
					'amount' => $order_discount_money,
					'created' => array('NOW()')
			);
		}
	}

	if ($refund_count > 0) {
		if ($refund_count < $order_count) {
			// 部分退款
			$trade_refund_states = 2;
		} elseif ($refund_count == $order_count) {
			// 全部退款
			$trade_status = 80; // 主订单退款
		}

		logx("loadWeimobTradeImpl trade_refund_status tid:$tid trade_status:{$trade_status} trade_refund_states：{$trade_refund_states} refund_count:{$refund_count}", $sid.'/Trade');
	}

	$text = explode(' ', trim(@$t->sender_address), 4);//空格分割成4个部分
	$buyer_area = iconv_substr(trim(@$text[3]), 0, 40, 'UTF-8');
	$buyer_nick = (isset($t->fans_info) && $t->fans_info && isset($t->fans_info->nickname))?$t->fans_info->nickname:$t->receiver_name;
	$buyer_nick = iconv_substr(trim($buyer_nick), 0, 40, 'UTF-8');

	$trade_list[] = array
	(
			"tid" => $tid,                            // 订单号
			"platform_id" => $platform_id,            // 平台id
			"shop_id" => $shop_id,                // 店铺ID
			"process_status" => $process_status,        // 处理订单状态
			"trade_status" => $trade_status,            // 平台订单状态
			"refund_status" => $trade_refund_states,    // 退货状态
			'pay_status' => $pay_status,

			'order_count' => $order_count,
			'goods_count' => $goods_count,
			"trade_time" => dateValue(@str_replace("T", " ", @$t->create_time)),    // 下单时间
			"pay_time" => dateValue(@str_replace("T", " ", @$t->pay_time)),    // 付款时间

			"buyer_nick" => valid_utf8(trim(@$buyer_nick)),
			"buyer_message" => valid_utf8(@$t->remark),    // 买家购买附言
			"buyer_email" => '',
			"buyer_area" => $buyer_area,

			"receiver_name" => valid_utf8($receiver_name),
			"receiver_province" => $province_id,        // 省份id
			"receiver_city" => $city_id,                // 市id
			"receiver_district" => $district_id,        // 地区id
			"receiver_area" => $receiver_area,        // 省市区
			"receiver_address" => $receiver_address,    // 地址
			"receiver_zip" => '',                    // 邮编
			"receiver_mobile" => $receiver_mobile,            // 电话
			'to_deliver_time' => '',
			"receiver_hash" => md5($receiver_name . $receiver_area . @$receiver_address . $receiver_mobile . ''),
			"logistics_type" => -1,                    // 创建交易的物流方法$t->shipping_type

			'goods_amount' => $trade_total_fee,
			'post_amount' => $post_fee,
			'receivable' => $receivable,
			'discount' => $trade_discount_money,
			'paid' => $t->real_amount,

			'platform_cost' => 0,
			'invoice_type' => 0,
			'invoice_title' => '',
			"delivery_term" => $delivery_term,        // 是否货到付款
			"pay_id" => '',                            // 支付宝账号
			"remark" => @$t->order_flag_memo_wp, // 卖家备注
			"remark_flag" => (int)@$t->order_flag,    // 星标
			'cod_amount' => 2 == $delivery_term ? $t->real_amount : 0,
			'dap_amount' => 2 == $delivery_term ? 0 : $receivable,
			'refund_amount' => 0,
			'trade_mask' => 0,
			'score' => 0,
			'real_score' => 0,
			'got_score' => 0,
			'created' => array('NOW()')
	);

	if (bccomp($trade_discount_money, 0)) {
		$discount_list[] = array
		(
				'platform_id' => $platform_id,
				'tid' => $tid,
				'oid' => '',
				'sn' => '',
				'type' => '',
				'name' => '折扣优惠',
				'is_bonus' => 0,
				'detail' => '',
				'amount' => $trade_discount_money,
				'created' => array('NOW()')
		);
	}


	return true;
}

//省份处理
function wmProvince($province)
{
	global $spec_province_map;

	if (empty($province)) return '';

	if (iconv_substr($province, -1, 1, 'UTF-8') != '省') {
		$prefix = iconv_substr($province, 0, 2, 'UTF-8');

		if (isset($spec_province_map[$prefix]))
			return $spec_province_map[$prefix];

		return $province . '省';
	}

	return $province;
}
