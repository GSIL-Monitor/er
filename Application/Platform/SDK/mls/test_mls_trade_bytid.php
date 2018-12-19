<?php
	include "mlsClient.php";
	define('CO', 'ZSXJ');
	define('MLS_API_URL', 'http://shopapi.meilishuo.com');
	$tid = "14121214910479";
	$session ="yxebyxuzpdwniayxUDebebqmtcwnUDwnqmqmuzqmtc" ;
	$mls = new MlsApiClient();
	$url = '/order/details';
	
	$params = array (
	'vcode' =>$session,
	'co' => CO,
	'order_id' => $tid
	);
	$retval = $mls->fetch($url, $params);
	var_dump($retval);
	
/*
object(stdClass)#2 (3) {
  ["code"]=>
  int(0)
  ["info"]=>
  object(stdClass)#3 (4) {
    ["order"]=>
    object(stdClass)#4 (21) {
      ["order_id"]=>
      int(14121214910479)
      ["status_text"]=>
      string(12) "交易成功"
      ["total_price"]=>
      string(5) "85.00"
      ["ctime"]=>
      string(19) "2014-12-12 22:47:00"
      ["comment"]=>
      string(0) ""
      ["express_price"]=>
      string(4) "0.00"
      ["express_id"]=>
      string(12) "762701490816"
      ["express_company"]=>
      string(12) "中通速递"
      ["pay_time"]=>
      string(19) "2014-12-12 22:47:00"
      ["send_time"]=>
      string(19) "2014-12-14 21:02:00"
      ["last_status_time"]=>
      string(0) ""
      ["pay_time_out"]=>
      string(19) "2014-12-13 22:47:00"
      ["receive_time_out"]=>
      string(19) "2014-12-24 21:02:00"
      ["service_time_out"]=>
      string(19) "2015-01-08 21:37:00"
      ["coupon_shop"]=>
      string(5) "10.00"
      ["coupon_platform"]=>
      string(4) "0.00"
      ["weixin_coupon_price"]=>
      string(0) ""
      ["buyer_nickname"]=>
      string(15) "qzone_Chris_454"
      ["uid"]=>
      string(8) "55346556"
      ["deliver_status"]=>
      int(0)
      ["express"]=>
      array(10) {
        [0]=>
        string(67) "2014-12-14 20:09:13 快件离开 北京通州永顺 已发往北京"
        [1]=>
        string(75) "2014-12-14 20:27:43 北京通州永顺(文滔) 已收件 进入公司分捡"
        [2]=>
        string(67) "2014-12-14 20:56:47 快件离开 北京通州永顺 已发往福州"
        [3]=>
        string(64) "2014-12-14 20:56:47 在 北京通州永顺 装包并发往福州"
        [4]=>
        string(61) "2014-12-14 22:10:09 在 北京 装包并发往泉州中转部"
        [5]=>
        string(52) "2014-12-16 13:52:52 所在包 到达泉州中转部 "
        [6]=>
        string(67) "2014-12-16 13:54:16 在 泉州中转部 装包并发往福州中转"
        [7]=>
        string(67) "2014-12-16 19:44:01 快件离开 福州中转 已发往福州连江"
        [8]=>
        string(87) "2014-12-17 07:35:29 快件到达 福州连江 正在分捡中 上一站是 福州中转"
        [9]=>
        string(45) "2014-12-17 14:02:37 签收人是 拍照签收"
      }
    }
    ["goods"]=>
    array(1) {
      [0]=>
      object(stdClass)#5 (16) {
        ["mid"]=>
        string(9) "107440372"
        ["goods_id"]=>
        string(9) "204465771"
        ["twitter_id"]=>
        string(10) "3043490177"
        ["price"]=>
        string(5) "95.00"
        ["goods_title"]=>
        string(122) "VB白盒子【独家定制】 2014秋装新款欧美高领毛衣女打底衫纯色套头衫女装高领 长款毛衣外套"
        ["goods_img"]=>
        string(84) "http://d02.res.meilishuo.net/pic/b/99/cc/e3140b76e43447e6c6ab37c84744_640_900.cc.jpg"
        ["amount"]=>
        int(1)
        ["prop"]=>
        array(2) {
          [0]=>
          object(stdClass)#6 (3) {
            ["name"]=>
            string(6) "颜色"
            ["value"]=>
            string(9) "米白色"
            ["is_show"]=>
            string(1) "1"
          }
          [1]=>
          object(stdClass)#7 (3) {
            ["name"]=>
            string(6) "尺码"
            ["value"]=>
            string(6) "均码"
            ["is_show"]=>
            string(1) "1"
          }
        }
        ["goods_no"]=>
        string(6) "ZT-080"
        ["sku_id"]=>
        string(8) "39407171"
        ["goods_code"]=>
        string(10) "ZT-080-CJM"
        ["refund_status_text"]=>
        string(0) ""
        ["goods_total_price"]=>
        string(5) "85.00"
        ["goods_shop_coupon"]=>
        string(5) "10.00"
        ["goods_platform_coupon"]=>
        string(4) "0.00"
        ["deliver_status"]=>
        int(0)
      }
    }
    ["address"]=>
    object(stdClass)#8 (8) {
      ["postcode"]=>
      string(6) "350001"
      ["nickname"]=>
      string(9) "黄敏敏"
      ["phone"]=>
      string(11) "18060894597"
      ["address"]=>
      string(100) "福建省福州市连江县贵安温泉度假区福建商业高等专科学校潘度校区2号楼217"
      ["province"]=>
      string(6) "福建"
      ["city"]=>
      string(6) "福州"
      ["district"]=>
      string(9) "连江县"
      ["street"]=>
      string(73) "贵安温泉度假区福建商业高等专科学校潘度校区2号楼217"
    }
    ["service"]=>
    array(0) {
    }
  }
  ["message"]=>
  string(0) ""
}
*/