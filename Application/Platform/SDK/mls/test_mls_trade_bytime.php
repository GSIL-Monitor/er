<?php
	include "mlsClient.php";
	define('CO', 'ZSXJ');
	define('MLS_API_URL', 'http://shopapi.meilishuo.com');
	$tid = "14121214910479";
	$ptime =1418909923;
	$end_time = 1418919923;
	$session ="yxebyxuzpdwniayxUDebebqmtcwnUDwnqmqmuzqmtc" ;
	$mls = new MlsApiClient();
	$url = '/order/list_info';
	$params = array (
	'vcode' =>$session,
	'co' =>CO,
	'page' => 0,
	'page_size' => 50,
	'uptime_start' =>date('Y-m-d H:i:s', $ptime),
	'uptime_end' => date('Y-m-d H:i:s', $end_time), 
	'co' =>CO
	);
	$retval = $mls->fetch($url, $params);
	var_dump($retval);
	
/*
   [1]=>
        object(stdClass)#57 (15) {
          ["mid"]=>
          string(9) "107860950"
          ["goods_id"]=>
          string(9) "129160297"
          ["twitter_id"]=>
          string(10) "2888629999"
          ["price"]=>
          string(5) "79.00"
          ["goods_title"]=>
          string(83) "VB白盒子 2014新款包臀紧身修身打底吊带裙 无袖背心连衣短裙女"
          ["goods_no"]=>
          string(7) "LYQ-064"
          ["sku_id"]=>
          string(8) "17273305"
          ["goods_code"]=>
          string(10) "LYQ-064-AM"
          ["goods_img"]=>
          string(84) "http://d06.res.meilishuo.net/pic/b/36/3b/94c166bc56e33fdeae204b75de9b_640_960.c8.jpg"
          ["amount"]=>
          int(1)
          ["prop"]=>
          array(2) {
            [0]=>
            object(stdClass)#58 (3) {
              ["name"]=>
              string(6) "颜色"
              ["value"]=>
              string(6) "白色"
              ["is_show"]=>
              string(1) "1"
            }
            [1]=>
            object(stdClass)#59 (3) {
              ["name"]=>
              string(6) "尺码"
              ["value"]=>
              string(1) "M"
              ["is_show"]=>
              string(1) "1"
            }
          }
          ["refund_status_text"]=>
          string(0) ""
          ["plat_coupon"]=>
          float(19.26)
          ["shop_coupon"]=>
          int(0)
          ["deliver_status"]=>
          int(0)
        }
        [2]=>
        object(stdClass)#60 (15) {
          ["mid"]=>
          string(9) "107860952"
          ["goods_id"]=>
          string(9) "151223379"
          ["twitter_id"]=>
          string(10) "2928360139"
          ["price"]=>
          string(6) "125.30"
          ["goods_title"]=>
          string(113) "【VB白盒子.原创设计定制款】 2014秋装新款连衣裙格纹衬衫女装短袖中长款防晒衬衣潮"
          ["goods_no"]=>
          string(6) "CS-060"
          ["sku_id"]=>
          string(8) "20993115"
          ["goods_code"]=>
          string(10) "CS-060-BJM"
          ["goods_img"]=>
          string(84) "http://d02.res.meilishuo.net/pic/b/b5/f8/51b15edbcf33fe3e3b306fe71cb6_640_900.c8.jpg"
          ["amount"]=>
          int(1)
          ["prop"]=>
          array(2) {
            [0]=>
            object(stdClass)#61 (3) {
              ["name"]=>
              string(6) "颜色"
              ["value"]=>
              string(12) "深海藏蓝"
              ["is_show"]=>
              string(1) "1"
            }
            [1]=>
            object(stdClass)#62 (3) {
              ["name"]=>
              string(6) "尺码"
              ["value"]=>
              string(6) "均码"
              ["is_show"]=>
              string(1) "1"
            }
          }
          ["refund_status_text"]=>
          string(0) ""
          ["plat_coupon"]=>
          float(30.52)
          ["shop_coupon"]=>
          int(0)
          ["deliver_status"]=>
          int(0)
        }
        [3]=>
        object(stdClass)#63 (15) {
          ["mid"]=>
          string(9) "107860954"
          ["goods_id"]=>
          string(9) "222491697"
          ["twitter_id"]=>
          string(10) "3135801715"
          ["price"]=>
          string(6) "157.21"
          ["goods_title"]=>
          string(98) "VB白盒子【定制】  2014秋装新款休闲上衣针织衫女装钩花镂空套头毛衣外套"
          ["goods_no"]=>
          string(7) "LYQ-177"
          ["sku_id"]=>
          string(8) "55725389"
          ["goods_code"]=>
          string(11) "LYQ-177-BJM"
          ["goods_img"]=>
          string(84) "http://d06.res.meilishuo.net/pic/b/06/1a/c80b1c63c989d75171d7bafab48d_640_900.cc.jpg"
          ["amount"]=>
          int(1)
          ["prop"]=>
          array(2) {
            [0]=>
            object(stdClass)#64 (3) {
              ["name"]=>
              string(6) "颜色"
              ["value"]=>
              string(9) "湖水绿"
              ["is_show"]=>
              string(1) "1"
            }
            [1]=>
            object(stdClass)#65 (3) {
              ["name"]=>
              string(6) "尺码"
              ["value"]=>
              string(6) "均码"
              ["is_show"]=>
              string(1) "1"
            }
          }
          ["refund_status_text"]=>
          string(0) ""
          ["plat_coupon"]=>
          float(38.29)
          ["shop_coupon"]=>
          int(0)
          ["deliver_status"]=>
          int(0)
        }
      }
      ["address"]=>
      object(stdClass)#66 (8) {
        ["postcode"]=>
        string(0) ""
        ["nickname"]=>
        string(9) "吕泽华"
        ["phone"]=>
        string(11) "18826100419"
        ["address"]=>
        string(66) "广东省广州市荔湾区东漖街道浣花路129号达盛烟酒"
        ["province"]=>
        string(6) "广东"
        ["city"]=>
        string(6) "广州"
        ["district"]=>
        string(9) "荔湾区"
        ["street"]=>
        string(39) "东漖街道浣花路129号达盛烟酒"
      }
      ["service"]=>
      array(0) {
      }
    }
    [9]=>
    object(stdClass)#67 (4) {
      ["order"]=>
      object(stdClass)#68 (16) {
        ["order_id"]=>
        int(14121296503111)
        ["status_text"]=>
        string(12) "交易成功"
        ["total_price"]=>
        string(6) "232.34"
        ["ctime"]=>
        string(19) "2014-12-12 22:18:00"
        ["comment"]=>
        string(0) ""
        ["express_price"]=>
        string(4) "0.00"
        ["express_id"]=>
        string(12) "762701490315"
        ["express_company"]=>
        string(12) "中通速递"
        ["pay_time"]=>
        string(19) "2014-12-12 22:18:00"
        ["send_time"]=>
        string(19) "2014-12-13 19:06:00"
        ["last_status_time"]=>
        string(0) ""
        ["pay_time_out"]=>
        string(19) "2014-12-13 22:18:00"
        ["receive_time_out"]=>
        string(19) "2014-12-23 19:06:00"
        ["service_time_out"]=>
        string(19) "2015-01-02 23:32:00"
        ["buyer_nickname"]=>
        string(13) "熙熙*^▁^*"
        ["deliver_status"]=>
        int(0)
      }
      ["goods"]=>
      array(1) {
        [0]=>
        object(stdClass)#69 (15) {
          ["mid"]=>
          string(9) "107380314"
          ["goods_id"]=>
          string(9) "232087391"
          ["twitter_id"]=>
          string(10) "3295947429"
          ["price"]=>
          string(6) "312.34"
          ["goods_title"]=>
          string(60) "VB百盒子A型伞形显瘦短款加厚显瘦带帽羽绒服"
          ["goods_no"]=>
          string(7) "YRF-014"
          ["sku_id"]=>
          string(8) "73194242"
          ["goods_code"]=>
          string(10) "YRF-014-BM"
          ["goods_img"]=>
          string(84) "http://d05.res.meilishuo.net/pic/b/a9/a3/f39bafb2b6d8207a72c65b57b681_640_900.cf.jpg"
          ["amount"]=>
          int(1)
          ["prop"]=>
          array(2) {
            [0]=>
            object(stdClass)#70 (3) {
              ["name"]=>
              string(6) "颜色"
              ["value"]=>
              string(6) "红色"
              ["is_show"]=>
              string(1) "1"
            }
            [1]=>
            object(stdClass)#71 (3) {
              ["name"]=>
              string(6) "尺码"
              ["value"]=>
              string(1) "M"
              ["is_show"]=>
              string(1) "1"
            }
          }
          ["refund_status_text"]=>
          string(0) ""
          ["plat_coupon"]=>
          int(60)
          ["shop_coupon"]=>
          int(20)
          ["deliver_status"]=>
          int(0)
        }
      }
      ["address"]=>
      object(stdClass)#72 (8) {
        ["postcode"]=>
        string(6) "650000"
        ["nickname"]=>
        string(9) "赵敏鑫"
        ["phone"]=>
        string(11) "13529192793"
        ["address"]=>
        string(48) "云南省昆明市官渡区官渡物流园9-4号"
        ["province"]=>
        string(6) "云南"
        ["city"]=>
        string(6) "昆明"
        ["district"]=>
        string(9) "官渡区"
        ["street"]=>
        string(21) "官渡物流园9-4号"
      }
      ["service"]=>
      array(0) {
      }
    }
    [10]=>
    object(stdClass)#73 (4) {
      ["order"]=>
      object(stdClass)#74 (16) {
        ["order_id"]=>
        int(14121297460255)
        ["status_text"]=>
        string(12) "交易成功"
        ["total_price"]=>
        string(5) "70.83"
        ["ctime"]=>
        string(19) "2014-12-12 22:11:00"
        ["comment"]=>
        string(0) ""
        ["express_price"]=>
        string(4) "0.00"
        ["express_id"]=>
        string(12) "762701183021"
        ["express_company"]=>
        string(12) "中通速递"
        ["pay_time"]=>
        string(19) "2014-12-12 22:18:00"
        ["send_time"]=>
        string(19) "2014-12-14 21:02:00"
        ["last_status_time"]=>
        string(0) ""
        ["pay_time_out"]=>
        string(19) "2014-12-13 22:11:00"
        ["receive_time_out"]=>
        string(19) "2014-12-24 21:02:00"
        ["service_time_out"]=>
        string(19) "2015-01-02 22:29:00"
        ["buyer_nickname"]=>
        string(11) "qzone896003"
        ["deliver_status"]=>
        int(0)
      }
      ["goods"]=>
      array(1) {
        [0]=>
        object(stdClass)#75 (15) {
          ["mid"]=>
          string(9) "107367238"
          ["goods_id"]=>
          string(9) "212702655"
          ["twitter_id"]=>
          string(10) "3095297717"
          ["price"]=>
          string(5) "99.00"
          ["goods_title"]=>
          string(100) "VB白盒子 2014秋冬新款款休闲卫裤修身显瘦靴裤低腰棉运动裤女加绒加厚长裤"
          ["goods_no"]=>
          string(6) "CK-027"
          ["sku_id"]=>
          string(8) "47093577"
          ["goods_code"]=>
          string(9) "CK-027-AS"
          ["goods_img"]=>
          string(84) "http://d06.res.meilishuo.net/pic/b/64/f6/300dff0af0387311f33a059f86ed_640_900.cc.jpg"
          ["amount"]=>
          int(1)
          ["prop"]=>
          array(2) {
            [0]=>
            object(stdClass)#76 (3) {
              ["name"]=>
              string(6) "颜色"
              ["value"]=>
              string(9) "花灰色"
              ["is_show"]=>
              string(1) "1"
            }
            [1]=>
            object(stdClass)#77 (3) {
              ["name"]=>
              string(6) "尺码"
              ["value"]=>
              string(1) "S"
              ["is_show"]=>
              string(1) "1"
            }
          }
          ["refund_status_text"]=>
          string(0) ""
          ["plat_coupon"]=>
          float(18.17)
          ["shop_coupon"]=>
          int(10)
          ["deliver_status"]=>
          int(0)
        }
      }
      ["address"]=>
      object(stdClass)#78 (8) {
        ["postcode"]=>
        string(6) "427000"
        ["nickname"]=>
        string(9) "王建华"
        ["phone"]=>
        string(11) "13037446669"
        ["address"]=>
        string(60) "湖南省张家界市永定区张家界市中级人民法院"
        ["province"]=>
        string(6) "湖南"
        ["city"]=>
        string(9) "张家界"
        ["district"]=>
        string(9) "永定区"
        ["street"]=>
        string(30) "张家界市中级人民法院"
      }
      ["service"]=>
      array(0) {
      }
    }
    [11]=>
    object(stdClass)#79 (4) {
      ["order"]=>
      object(stdClass)#80 (16) {
        ["order_id"]=>
        int(14121286135233)
        ["status_text"]=>
        string(12) "交易成功"
        ["total_price"]=>
        string(5) "95.00"
        ["ctime"]=>
        string(19) "2014-12-12 20:58:00"
        ["comment"]=>
        string(0) ""
        ["express_price"]=>
        string(4) "0.00"
        ["express_id"]=>
        string(12) "762701490772"
        ["express_company"]=>
        string(12) "中通速递"
        ["pay_time"]=>
        string(19) "2014-12-12 21:02:00"
        ["send_time"]=>
        string(19) "2014-12-14 21:02:00"
        ["last_status_time"]=>
        string(0) ""
        ["pay_time_out"]=>
        string(19) "2014-12-13 20:58:00"
        ["receive_time_out"]=>
        string(19) "2014-12-24 21:02:00"
        ["service_time_out"]=>
        string(19) "2015-01-02 22:54:00"
        ["buyer_nickname"]=>
        string(12) "扎心乎扰"
        ["deliver_status"]=>
        int(0)
      }
      ["goods"]=>
      array(1) {
        [0]=>
        object(stdClass)#81 (15) {
          ["mid"]=>
          string(9) "107214592"
          ["goods_id"]=>
          string(9) "204465771"
          ["twitter_id"]=>
          string(10) "3043490177"
          ["price"]=>
          string(5) "95.00"
          ["goods_title"]=>
          string(122) "VB白盒子【独家定制】 2014秋装新款欧美高领毛衣女打底衫纯色套头衫女装高领 长款毛衣外套"
          ["goods_no"]=>
          string(6) "ZT-080"
          ["sku_id"]=>
          string(8) "39407171"
          ["goods_code"]=>
          string(10) "ZT-080-CJM"
          ["goods_img"]=>
          string(84) "http://d02.res.meilishuo.net/pic/b/99/cc/e3140b76e43447e6c6ab37c84744_640_900.cc.jpg"
          ["amount"]=>
          int(1)
          ["prop"]=>
          array(2) {
            [0]=>
            object(stdClass)#82 (3) {
              ["name"]=>
              string(6) "颜色"
              ["value"]=>
              string(9) "米白色"
              ["is_show"]=>
              string(1) "1"
            }
            [1]=>
            object(stdClass)#83 (3) {
              ["name"]=>
              string(6) "尺码"
              ["value"]=>
              string(6) "均码"
              ["is_show"]=>
              string(1) "1"
            }
          }
          ["refund_status_text"]=>
          string(0) ""
          ["plat_coupon"]=>
          int(0)
          ["shop_coupon"]=>
          int(0)
          ["deliver_status"]=>
          int(0)
        }
      }
      ["address"]=>
      object(stdClass)#84 (8) {
        ["postcode"]=>
        string(0) ""
        ["nickname"]=>
        string(9) "易小东"
        ["phone"]=>
        string(11) "15920284776"
        ["address"]=>
        string(66) "广东省东莞市东莞市道滘镇南丫村景天工业园三楼"
        ["province"]=>
        string(6) "广东"
        ["city"]=>
        string(6) "东莞"
        ["district"]=>
        string(0) ""
        ["street"]=>
        string(48) "东莞市道滘镇南丫村景天工业园三楼"
      }
      ["service"]=>
      array(0) {
      }
    }
    [12]=>
    object(stdClass)#85 (4) {
      ["order"]=>
      object(stdClass)#86 (16) {
        ["order_id"]=>
        int(14121271836435)
        ["status_text"]=>
        string(12) "交易成功"
        ["total_price"]=>
        string(5) "95.00"
        ["ctime"]=>
        string(19) "2014-12-12 18:01:00"
        ["comment"]=>
        string(0) ""
        ["express_price"]=>
        string(4) "0.00"
        ["express_id"]=>
        string(12) "762701490801"
        ["express_company"]=>
        string(12) "中通速递"
        ["pay_time"]=>
        string(19) "2014-12-12 23:14:00"
        ["send_time"]=>
        string(19) "2014-12-14 21:02:00"
        ["last_status_time"]=>
        string(0) ""
        ["pay_time_out"]=>
        string(19) "2014-12-13 18:01:00"
        ["receive_time_out"]=>
        string(19) "2014-12-24 21:02:00"
        ["service_time_out"]=>
        string(19) "2015-01-02 23:40:00"
        ["buyer_nickname"]=>
        string(9) "洗豆豆"
        ["deliver_status"]=>
        int(0)
      }
      ["goods"]=>
      array(1) {
        [0]=>
        object(stdClass)#87 (15) {
          ["mid"]=>
          string(9) "106941932"
          ["goods_id"]=>
          string(9) "204465771"
          ["twitter_id"]=>
          string(10) "3043490177"
          ["price"]=>
          string(5) "95.00"
          ["goods_title"]=>
          string(122) "VB白盒子【独家定制】 2014秋装新款欧美高领毛衣女打底衫纯色套头衫女装高领 长款毛衣外套"
          ["goods_no"]=>
          string(6) "ZT-080"
          ["sku_id"]=>
          string(8) "39407171"
          ["goods_code"]=>
          string(10) "ZT-080-CJM"
          ["goods_img"]=>
          string(84) "http://d02.res.meilishuo.net/pic/b/99/cc/e3140b76e43447e6c6ab37c84744_640_900.cc.jpg"
          ["amount"]=>
          int(1)
          ["prop"]=>
          array(2) {
            [0]=>
            object(stdClass)#88 (3) {
              ["name"]=>
              string(6) "颜色"
              ["value"]=>
              string(9) "米白色"
              ["is_show"]=>
              string(1) "1"
            }
            [1]=>
            object(stdClass)#89 (3) {
              ["name"]=>
              string(6) "尺码"
              ["value"]=>
              string(6) "均码"
              ["is_show"]=>
              string(1) "1"
            }
          }
          ["refund_status_text"]=>
          string(0) ""
          ["plat_coupon"]=>
          int(0)
          ["shop_coupon"]=>
          int(0)
          ["deliver_status"]=>
          int(0)
        }
      }
      ["address"]=>
      object(stdClass)#90 (8) {
        ["postcode"]=>
        string(6) "650000"
        ["nickname"]=>
        string(6) "杨敏"
        ["phone"]=>
        string(11) "15925208144"
        ["address"]=>
        string(119) "云南省昆明市五华区兴华街 昆明十中教工宿舍 一栋三单元601号（电话不能接听时交门卫）"
        ["province"]=>
        string(6) "云南"
        ["city"]=>
        string(6) "昆明"
        ["district"]=>
        string(9) "五华区"
        ["street"]=>
        string(92) "兴华街 昆明十中教工宿舍 一栋三单元601号（电话不能接听时交门卫）"
      }
      ["service"]=>
      array(0) {
      }
    }
    [13]=>
    object(stdClass)#91 (4) {
      ["order"]=>
      object(stdClass)#92 (16) {
        ["order_id"]=>
        int(14121233205471)
        ["status_text"]=>
        string(12) "交易成功"
        ["total_price"]=>
        string(5) "67.54"
        ["ctime"]=>
        string(19) "2014-12-12 12:59:00"
        ["comment"]=>
        string(18) "米白色！！！"
        ["express_price"]=>
        string(4) "0.00"
        ["express_id"]=>
        string(12) "762701490060"
        ["express_company"]=>
        string(12) "中通速递"
        ["pay_time"]=>
        string(19) "2014-12-12 12:59:00"
        ["send_time"]=>
        string(19) "2014-12-12 20:09:00"
        ["last_status_time"]=>
        string(0) ""
        ["pay_time_out"]=>
        string(19) "2014-12-13 12:59:00"
        ["receive_time_out"]=>
        string(19) "2014-12-22 20:09:00"
        ["service_time_out"]=>
        string(19) "2015-01-03 00:06:00"
        ["buyer_nickname"]=>
        string(13) "余小渔1990"
        ["deliver_status"]=>
        int(0)
      }
      ["goods"]=>
      array(1) {
        [0]=>
        object(stdClass)#93 (15) {
          ["mid"]=>
          string(9) "106498580"
          ["goods_id"]=>
          string(9) "204465771"
          ["twitter_id"]=>
          string(10) "3043490177"
          ["price"]=>
          string(5) "95.00"
          ["goods_title"]=>
          string(122) "VB白盒子【独家定制】 2014秋装新款欧美高领毛衣女打底衫纯色套头衫女装高领 长款毛衣外套"
          ["goods_no"]=>
          string(6) "ZT-080"
          ["sku_id"]=>
          string(8) "39407171"
          ["goods_code"]=>
          string(10) "ZT-080-CJM"
          ["goods_img"]=>
          string(84) "http://d02.res.meilishuo.net/pic/b/99/cc/e3140b76e43447e6c6ab37c84744_640_900.cc.jpg"
          ["amount"]=>
          int(1)
          ["prop"]=>
          array(2) {
            [0]=>
            object(stdClass)#94 (3) {
              ["name"]=>
              string(6) "颜色"
              ["value"]=>
              string(9) "米白色"
              ["is_show"]=>
              string(1) "1"
            }
            [1]=>
            object(stdClass)#95 (3) {
              ["name"]=>
              string(6) "尺码"
              ["value"]=>
              string(6) "均码"
              ["is_show"]=>
              string(1) "1"
            }
          }
          ["refund_status_text"]=>
          string(0) ""
          ["plat_coupon"]=>
          float(17.46)
          ["shop_coupon"]=>
          int(10)
          ["deliver_status"]=>
          int(0)
        }
      }
      ["address"]=>
      object(stdClass)#96 (8) {
        ["postcode"]=>
        string(0) ""
        ["nickname"]=>
        string(6) "余祺"
        ["phone"]=>
        string(11) "15182119317"
        ["address"]=>
        string(72) "四川省内江市威远县连界镇三十米大街贤龙医院护士站"
        ["province"]=>
        string(6) "四川"
        ["city"]=>
        string(6) "内江"
        ["district"]=>
        string(9) "威远县"
        ["street"]=>
        string(45) "连界镇三十米大街贤龙医院护士站"
      }
      ["service"]=>
      array(0) {
      }
    }
    [14]=>
    object(stdClass)#97 (4) {
      ["order"]=>
      object(stdClass)#98 (16) {
        ["order_id"]=>
        int(14121258044285)
        ["status_text"]=>
        string(12) "交易成功"
        ["total_price"]=>
        string(6) "197.25"
        ["ctime"]=>
        string(19) "2014-12-12 08:37:00"
        ["comment"]=>
        string(0) ""
        ["express_price"]=>
        string(4) "0.00"
        ["express_id"]=>
        string(12) "500020489967"
        ["express_company"]=>
        string(12) "圆通速递"
        ["pay_time"]=>
        string(19) "2014-12-12 10:03:00"
        ["send_time"]=>
        string(19) "2014-12-14 19:30:00"
        ["last_status_time"]=>
        string(0) ""
        ["pay_time_out"]=>
        string(19) "2014-12-13 08:37:00"
        ["receive_time_out"]=>
        string(19) "2014-12-24 19:30:00"
        ["service_time_out"]=>
        string(19) "2015-01-02 22:02:00"
        ["buyer_nickname"]=>
        string(9) "小晓浪"
        ["deliver_status"]=>
        int(0)
      }
      ["goods"]=>
      array(1) {
        [0]=>
        object(stdClass)#99 (15) {
          ["mid"]=>
          string(9) "106033694"
          ["goods_id"]=>
          string(9) "233744511"
          ["twitter_id"]=>
          string(10) "3360087225"
          ["price"]=>
          string(6) "207.60"
          ["goods_title"]=>
          string(86) "VB白盒子  1212秋冬新款牛仔背带裤学院风长裤女牛仔裤宽松裤子潮"
          ["goods_no"]=>
          string(7) "LTK-019"
          ["sku_id"]=>
          string(8) "81150776"
          ["goods_code"]=>
          string(10) "LTK-019-26"
          ["goods_img"]=>
          string(84) "http://d05.res.meilishuo.net/pic/b/10/89/41489d8b5b365701a0b78ba68cb0_640_900.ch.jpg"
          ["amount"]=>
          int(1)
          ["prop"]=>
          array(2) {
            [0]=>
            object(stdClass)#100 (3) {
              ["name"]=>
              string(6) "颜色"
              ["value"]=>
              string(12) "牛仔深蓝"
              ["is_show"]=>
              string(1) "1"
            }
            [1]=>
            object(stdClass)#101 (3) {
              ["name"]=>
              string(6) "尺码"
              ["value"]=>
              string(2) "26"
              ["is_show"]=>
              string(1) "1"
            }
          }
          ["refund_status_text"]=>
          string(0) ""
          ["plat_coupon"]=>
          float(0.35)
          ["shop_coupon"]=>
          int(10)
          ["deliver_status"]=>
          int(0)
        }
      }
      ["address"]=>
      object(stdClass)#102 (8) {
        ["postcode"]=>
        string(0) ""
        ["nickname"]=>
        string(9) "银晓浪"
        ["phone"]=>
        string(11) "15123149872"
        ["address"]=>
        string(45) "湖南省长沙市宁乡县双凫铺开发区"
        ["province"]=>
        string(6) "湖南"
        ["city"]=>
        string(6) "长沙"
        ["district"]=>
        string(9) "宁乡县"
        ["street"]=>
        string(18) "双凫铺开发区"
      }
      ["service"]=>
      array(0) {
      }
    }
    [15]=>
    object(stdClass)#103 (4) {
      ["order"]=>
      object(stdClass)#104 (16) {
        ["order_id"]=>
        int(14121153692181)
        ["status_text"]=>
        string(12) "交易成功"
        ["total_price"]=>
        string(5) "99.00"
        ["ctime"]=>
        string(19) "2014-12-11 20:03:00"
        ["comment"]=>
        string(0) ""
        ["express_price"]=>
        string(4) "0.00"
        ["express_id"]=>
        string(12) "500020489210"
        ["express_company"]=>
        string(12) "圆通速递"
        ["pay_time"]=>
        string(19) "2014-12-11 20:16:00"
        ["send_time"]=>
        string(19) "2014-12-12 20:10:00"
        ["last_status_time"]=>
        string(0) ""
        ["pay_time_out"]=>
        string(19) "2014-12-12 20:03:00"
        ["receive_time_out"]=>
        string(19) "2014-12-22 20:10:00"
        ["service_time_out"]=>
        string(19) "2015-01-02 23:08:00"
        ["buyer_nickname"]=>
        string(18) "梁小文的盒子"
        ["deliver_status"]=>
        int(0)
      }
      ["goods"]=>
      array(1) {
        [0]=>
        object(stdClass)#105 (15) {
          ["mid"]=>
          string(9) "105039168"
          ["goods_id"]=>
          string(9) "212702655"
          ["twitter_id"]=>
          string(10) "3095297717"
          ["price"]=>
          string(5) "99.00"
          ["goods_title"]=>
          string(100) "VB白盒子 2014秋冬新款款休闲卫裤修身显瘦靴裤低腰棉运动裤女加绒加厚长裤"
          ["goods_no"]=>
          string(6) "CK-027"
          ["sku_id"]=>
          string(8) "47093579"
          ["goods_code"]=>
          string(9) "CK-027-AM"
          ["goods_img"]=>
          string(84) "http://d06.res.meilishuo.net/pic/b/64/f6/300dff0af0387311f33a059f86ed_640_900.cc.jpg"
          ["amount"]=>
          int(1)
          ["prop"]=>
          array(2) {
            [0]=>
            object(stdClass)#106 (3) {
              ["name"]=>
              string(6) "颜色"
              ["value"]=>
              string(9) "花灰色"
              ["is_show"]=>
              string(1) "1"
            }
            [1]=>
            object(stdClass)#107 (3) {
              ["name"]=>
              string(6) "尺码"
              ["value"]=>
              string(1) "M"
              ["is_show"]=>
              string(1) "1"
            }
          }
          ["refund_status_text"]=>
          string(0) ""
          ["plat_coupon"]=>
          int(0)
          ["shop_coupon"]=>
          int(0)
          ["deliver_status"]=>
          int(0)
        }
      }
      ["address"]=>
      object(stdClass)#108 (8) {
        ["postcode"]=>
        string(0) ""
        ["nickname"]=>
        string(6) "梁文"
        ["phone"]=>
        string(11) "18098335001"
        ["address"]=>
        string(46) "江苏省徐州市泉山区雁东小区5号楼"
        ["province"]=>
        string(6) "江苏"
        ["city"]=>
        string(6) "徐州"
        ["district"]=>
        string(9) "泉山区"
        ["street"]=>
        string(19) "雁东小区5号楼"
      }
      ["service"]=>
      array(0) {
      }
    }
  }
  ["total_num"]=>
  string(2) "16"
}
*/