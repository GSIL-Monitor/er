<?php
	include "mlsClient.php";
	define('CO', 'ZSXJ');
	define('MLS_API_URL', 'http://shopapi.meilishuo.com');
	$session ="yxebyxuzpdwniayxUDebebqmtcwnUDwnqmqmuzqmtc" ;
	$mls = new MlsApiClient();
	
	$url = '/goods/goods_list';
	$params = array (
	'vcode' =>$session,
	'co' => CO,
	'page' =>0,
	'page_size' =>20,
	'co' =>CO
	);
	$retval = $mls->fetch($url, $params);
	$retval = $mls->fetch($url, $params);
	var_dump($retval);

/*
    [8]=>
    object(stdClass)#127 (12) {
      ["twitter_id"]=>
      string(10) "3407776655"
      ["goods_id"]=>
      string(9) "241881777"
      ["goods_title"]=>
      string(26) "VB白盒子呢子阔腿裤"
      ["goods_img"]=>
      string(86) "http://imgtest.meiliworks.com/pic/_o/31/75/67121808fd54721fa1964ee514ae_640_900.ch.jpg"
      ["goods_no"]=>
      string(6) "CK-152"
      ["goods_price"]=>
      string(6) "459.00"
      ["goods_status"]=>
      string(1) "1"
      ["sale_num"]=>
      string(1) "1"
      ["goods_first_catalog"]=>
      string(5) "11801"
      ["goods_catalog"]=>
      string(5) "11989"
      ["shop_category"]=>
      array(1) {
        [0]=>
        object(stdClass)#128 (2) {
          ["category_id"]=>
          string(6) "259220"
          ["category_name"]=>
          string(24) "2015.春款新品.力荐"
        }
      }
      ["stocks"]=>
      array(4) {
        [0]=>
        object(stdClass)#129 (7) {
          ["sku_id"]=>
          string(8) "87363858"
          ["1st"]=>
          string(9) "薄墨灰"
          ["2rd"]=>
          string(1) "S"
          ["repertory"]=>
          string(2) "50"
          ["goods_code"]=>
          string(9) "CK-152-AS"
          ["color"]=>
          string(9) "薄墨灰"
          ["size"]=>
          string(1) "S"
        }
        [1]=>
        object(stdClass)#130 (7) {
          ["sku_id"]=>
          string(8) "87363860"
          ["1st"]=>
          string(9) "薄墨灰"
          ["2rd"]=>
          string(1) "M"
          ["repertory"]=>
          string(2) "50"
          ["goods_code"]=>
          string(9) "CK-152-AM"
          ["color"]=>
          string(9) "薄墨灰"
          ["size"]=>
          string(1) "M"
        }
        [2]=>
        object(stdClass)#131 (7) {
          ["sku_id"]=>
          string(8) "87363862"
          ["1st"]=>
          string(9) "暗夜黑"
          ["2rd"]=>
          string(1) "S"
          ["repertory"]=>
          string(2) "50"
          ["goods_code"]=>
          string(9) "CK-152-BS"
          ["color"]=>
          string(9) "暗夜黑"
          ["size"]=>
          string(1) "S"
        }
        [3]=>
        object(stdClass)#132 (7) {
          ["sku_id"]=>
          string(8) "87363864"
          ["1st"]=>
          string(9) "暗夜黑"
          ["2rd"]=>
          string(1) "M"
          ["repertory"]=>
          string(2) "50"
          ["goods_code"]=>
          string(9) "CK-152-BM"
          ["color"]=>
          string(9) "暗夜黑"
          ["size"]=>
          string(1) "M"
        }
      }
    }
    [9]=>
    object(stdClass)#133 (12) {
      ["twitter_id"]=>
      string(10) "3407772387"
      ["goods_id"]=>
      string(9) "241881651"
      ["goods_title"]=>
      string(32) "VB白盒子蕾丝小礼服裙黑"
      ["goods_img"]=>
      string(86) "http://imgtest.meiliworks.com/pic/_o/4f/30/439dcf252c7dc3739ad23c26baa0_640_900.cf.jpg"
      ["goods_no"]=>
      string(7) "LYQ-185"
      ["goods_price"]=>
      string(6) "469.00"
      ["goods_status"]=>
      string(1) "1"
      ["sale_num"]=>
      string(1) "4"
      ["goods_first_catalog"]=>
      string(5) "11801"
      ["goods_catalog"]=>
      string(5) "11977"
      ["shop_category"]=>
      array(2) {
        [0]=>
        object(stdClass)#134 (2) {
          ["category_id"]=>
          string(6) "259220"
          ["category_name"]=>
          string(24) "2015.春款新品.力荐"
        }
        [1]=>
        object(stdClass)#135 (2) {
          ["category_id"]=>
          string(6) "237415"
          ["category_name"]=>
          string(17) "最美年会-LOOK"
        }
      }
      ["stocks"]=>
      array(6) {
        [0]=>
        object(stdClass)#136 (7) {
          ["sku_id"]=>
          string(8) "87363560"
          ["1st"]=>
          string(9) "珍珠白"
          ["2rd"]=>
          string(1) "S"
          ["repertory"]=>
          string(2) "50"
          ["goods_code"]=>
          string(10) "LYQ-185-BS"
          ["color"]=>
          string(9) "珍珠白"
          ["size"]=>
          string(1) "S"
        }
        [1]=>
        object(stdClass)#137 (7) {
          ["sku_id"]=>
          string(8) "87363562"
          ["1st"]=>
          string(9) "珍珠白"
          ["2rd"]=>
          string(1) "M"
          ["repertory"]=>
          string(2) "50"
          ["goods_code"]=>
          string(10) "LYQ-185-BM"
          ["color"]=>
          string(9) "珍珠白"
          ["size"]=>
          string(1) "M"
        }
        [2]=>
        object(stdClass)#138 (7) {
          ["sku_id"]=>
          string(8) "87363564"
          ["1st"]=>
          string(9) "珍珠白"
          ["2rd"]=>
          string(1) "L"
          ["repertory"]=>
          string(2) "50"
          ["goods_code"]=>
          string(10) "LYQ-185-BL"
          ["color"]=>
          string(9) "珍珠白"
          ["size"]=>
          string(1) "L"
        }
        [3]=>
        object(stdClass)#139 (7) {
          ["sku_id"]=>
          string(8) "87363566"
          ["1st"]=>
          string(9) "暗夜黑"
          ["2rd"]=>
          string(1) "S"
          ["repertory"]=>
          string(2) "50"
          ["goods_code"]=>
          string(10) "LYQ-185-AS"
          ["color"]=>
          string(9) "暗夜黑"
          ["size"]=>
          string(1) "S"
        }
        [4]=>
        object(stdClass)#140 (7) {
          ["sku_id"]=>
          string(8) "87363568"
          ["1st"]=>
          string(9) "暗夜黑"
          ["2rd"]=>
          string(1) "M"
          ["repertory"]=>
          string(2) "50"
          ["goods_code"]=>
          string(10) "LYQ-185-AM"
          ["color"]=>
          string(9) "暗夜黑"
          ["size"]=>
          string(1) "M"
        }
        [5]=>
        object(stdClass)#141 (7) {
          ["sku_id"]=>
          string(8) "87363570"
          ["1st"]=>
          string(9) "暗夜黑"
          ["2rd"]=>
          string(1) "L"
          ["repertory"]=>
          string(2) "50"
          ["goods_code"]=>
          string(10) "LYQ-185-AL"
          ["color"]=>
          string(9) "暗夜黑"
          ["size"]=>
          string(1) "L"
        }
      }
    }
    [10]=>
    object(stdClass)#142 (12) {
      ["twitter_id"]=>
      string(10) "3407765149"
      ["goods_id"]=>
      string(9) "241881469"
      ["goods_title"]=>
      string(32) "VB白盒子菱形蝙蝠袖衬衫"
      ["goods_img"]=>
      string(85) "http://d04.res.meilishuo.net/pic/_o/e1/99/78527f620b4ddf4b55afe788cc48_640_900.ch.jpg"
      ["goods_no"]=>
      string(6) "CS-071"
      ["goods_price"]=>
      string(6) "329.00"
      ["goods_status"]=>
      string(1) "1"
      ["sale_num"]=>
      string(1) "0"
      ["goods_first_catalog"]=>
      string(5) "11801"
      ["goods_catalog"]=>
      string(5) "11943"
      ["shop_category"]=>
      array(1) {
        [0]=>
        object(stdClass)#143 (2) {
          ["category_id"]=>
          string(6) "259220"
          ["category_name"]=>
          string(24) "2015.春款新品.力荐"
        }
      }
      ["stocks"]=>
      array(3) {
        [0]=>
        object(stdClass)#144 (7) {
          ["sku_id"]=>
          string(8) "87362904"
          ["1st"]=>
          string(9) "俏皮红"
          ["2rd"]=>
          string(6) "均码"
          ["repertory"]=>
          string(2) "50"
          ["goods_code"]=>
          string(10) "CS-071-CJM"
          ["color"]=>
          string(9) "俏皮红"
          ["size"]=>
          string(6) "均码"
        }
        [1]=>
        object(stdClass)#145 (7) {
          ["sku_id"]=>
          string(8) "87362906"
          ["1st"]=>
          string(9) "深海蓝"
          ["2rd"]=>
          string(6) "均码"
          ["repertory"]=>
          string(2) "50"
          ["goods_code"]=>
          string(10) "CS-071-AJM"
          ["color"]=>
          string(9) "深海蓝"
          ["size"]=>
          string(6) "均码"
        }
        [2]=>
        object(stdClass)#146 (7) {
          ["sku_id"]=>
          string(8) "87362908"
          ["1st"]=>
          string(9) "摩卡褐"
          ["2rd"]=>
          string(6) "均码"
          ["repertory"]=>
          string(2) "50"
          ["goods_code"]=>
          string(10) "CS-071-BJM"
          ["color"]=>
          string(9) "摩卡褐"
          ["size"]=>
          string(6) "均码"
        }
      }
    }
    [11]=>
    object(stdClass)#147 (12) {
      ["twitter_id"]=>
      string(10) "3404739531"
      ["goods_id"]=>
      string(9) "241795589"
      ["goods_title"]=>
      string(86) "VB白盒子  2015春装新款牛仔背带裤学院风长裤女牛仔裤宽松裤子潮"
      ["goods_img"]=>
      string(86) "http://imgtest.meiliworks.com/pic/_o/2d/e8/3413f5ac4559457c657e7081c2c6_640_900.cg.jpg"
      ["goods_no"]=>
      string(7) "LTK-019"
      ["goods_price"]=>
      string(6) "519.00"
      ["goods_status"]=>
      string(1) "1"
      ["sale_num"]=>
      string(2) "16"
      ["goods_first_catalog"]=>
      string(5) "11801"
      ["goods_catalog"]=>
      string(5) "11993"
      ["shop_category"]=>
      array(1) {
        [0]=>
        object(stdClass)#148 (2) {
          ["category_id"]=>
          string(6) "259220"
          ["category_name"]=>
          string(24) "2015.春款新品.力荐"
        }
      }
      ["stocks"]=>
      array(5) {
        [0]=>
        object(stdClass)#149 (7) {
          ["sku_id"]=>
          string(8) "86999958"
          ["1st"]=>
          string(12) "牛仔深蓝"
          ["2rd"]=>
          string(2) "26"
          ["repertory"]=>
          string(1) "5"
          ["goods_code"]=>
          string(10) "LTK-019-26"
          ["color"]=>
          string(12) "牛仔深蓝"
          ["size"]=>
          string(2) "26"
        }
        [1]=>
        object(stdClass)#150 (7) {
          ["sku_id"]=>
          string(8) "86999960"
          ["1st"]=>
          string(12) "牛仔深蓝"
          ["2rd"]=>
          string(2) "27"
          ["repertory"]=>
          string(1) "5"
          ["goods_code"]=>
          string(10) "LTK-019-27"
          ["color"]=>
          string(12) "牛仔深蓝"
          ["size"]=>
          string(2) "27"
        }
        [2]=>
        object(stdClass)#151 (7) {
          ["sku_id"]=>
          string(8) "86999962"
          ["1st"]=>
          string(12) "牛仔深蓝"
          ["2rd"]=>
          string(2) "28"
          ["repertory"]=>
          string(1) "5"
          ["goods_code"]=>
          string(10) "LTK-019-28"
          ["color"]=>
          string(12) "牛仔深蓝"
          ["size"]=>
          string(2) "28"
        }
        [3]=>
        object(stdClass)#152 (7) {
          ["sku_id"]=>
          string(8) "86999964"
          ["1st"]=>
          string(12) "牛仔深蓝"
          ["2rd"]=>
          string(2) "29"
          ["repertory"]=>
          string(1) "5"
          ["goods_code"]=>
          string(10) "LTK-019-29"
          ["color"]=>
          string(12) "牛仔深蓝"
          ["size"]=>
          string(2) "29"
        }
        [4]=>
        object(stdClass)#153 (7) {
          ["sku_id"]=>
          string(8) "86999966"
          ["1st"]=>
          string(12) "牛仔深蓝"
          ["2rd"]=>
          string(2) "30"
          ["repertory"]=>
          string(1) "5"
          ["goods_code"]=>
          string(10) "LTK-019-30"
          ["color"]=>
          string(12) "牛仔深蓝"
          ["size"]=>
          string(2) "30"
        }
      }
    }
    [12]=>
    object(stdClass)#154 (12) {
      ["twitter_id"]=>
      string(10) "3404712427"
      ["goods_id"]=>
      string(9) "241794491"
      ["goods_title"]=>
      string(85) "VB白盒子 2014女装冬新冬装棉长袖衬衣格子格纹衬衫修身上衣外套"
      ["goods_img"]=>
      string(85) "http://d05.res.meilishuo.net/pic/_o/c4/dd/c43a2b9868d54af1791d8a29cb76_640_900.cg.jpg"
      ["goods_no"]=>
      string(6) "CS-066"
      ["goods_price"]=>
      string(6) "339.00"
      ["goods_status"]=>
      string(1) "1"
      ["sale_num"]=>
      string(1) "1"
      ["goods_first_catalog"]=>
      string(5) "11801"
      ["goods_catalog"]=>
      string(5) "11943"
      ["shop_category"]=>
      array(1) {
        [0]=>
        object(stdClass)#155 (2) {
          ["category_id"]=>
          string(6) "259220"
          ["category_name"]=>
          string(24) "2015.春款新品.力荐"
        }
      }
      ["stocks"]=>
      array(2) {
        [0]=>
        object(stdClass)#156 (7) {
          ["sku_id"]=>
          string(8) "86995344"
          ["1st"]=>
          string(12) "红黑格纹"
          ["2rd"]=>
          string(6) "均码"
          ["repertory"]=>
          string(2) "15"
          ["goods_code"]=>
          string(10) "CS-066-AJM"
          ["color"]=>
          string(12) "红黑格纹"
          ["size"]=>
          string(6) "均码"
        }
        [1]=>
        object(stdClass)#157 (7) {
          ["sku_id"]=>
          string(8) "86995346"
          ["1st"]=>
          string(12) "蓝白格纹"
          ["2rd"]=>
          string(6) "均码"
          ["repertory"]=>
          string(2) "10"
          ["goods_code"]=>
          string(10) "CS-066-BJM"
          ["color"]=>
          string(12) "蓝白格纹"
          ["size"]=>
          string(6) "均码"
        }
      }
    }
    [13]=>
    object(stdClass)#158 (12) {
      ["twitter_id"]=>
      string(10) "3404706865"
      ["goods_id"]=>
      string(9) "241794267"
      ["goods_title"]=>
      string(85) "VB白盒子 2015欧美春秋新款圆领套头短款做旧毛衣打底衫针织衫女"
      ["goods_img"]=>
      string(86) "http://imgtest.meiliworks.com/pic/_o/9c/ef/a2f4540eba70e4bf367bb05474d2_640_900.cg.jpg"
      ["goods_no"]=>
      string(6) "ZT-130"
      ["goods_price"]=>
      string(6) "499.00"
      ["goods_status"]=>
      string(1) "1"
      ["sale_num"]=>
      string(1) "1"
      ["goods_first_catalog"]=>
      string(5) "11801"
      ["goods_catalog"]=>
      string(5) "11947"
      ["shop_category"]=>
      array(1) {
        [0]=>
        object(stdClass)#159 (2) {
          ["category_id"]=>
          string(6) "259220"
          ["category_name"]=>
          string(24) "2015.春款新品.力荐"
        }
      }
      ["stocks"]=>
      array(2) {
        [0]=>
        object(stdClass)#160 (7) {
          ["sku_id"]=>
          string(8) "86994194"
          ["1st"]=>
          string(13) "米底+蓝色"
          ["2rd"]=>
          string(6) "均码"
          ["repertory"]=>
          string(1) "3"
          ["goods_code"]=>
          string(10) "ZT-130-BJM"
          ["color"]=>
          string(13) "米底+蓝色"
          ["size"]=>
          string(6) "均码"
        }
        [1]=>
        object(stdClass)#161 (7) {
          ["sku_id"]=>
          string(8) "86994196"
          ["1st"]=>
          string(13) "米底+黑色"
          ["2rd"]=>
          string(6) "均码"
          ["repertory"]=>
          string(1) "2"
          ["goods_code"]=>
          string(10) "ZT-130-AJM"
          ["color"]=>
          string(13) "米底+黑色"
          ["size"]=>
          string(6) "均码"
        }
      }
    }
    [14]=>
    object(stdClass)#162 (12) {
      ["twitter_id"]=>
      string(10) "3404701885"
      ["goods_id"]=>
      string(9) "241794049"
      ["goods_title"]=>
      string(84) "VB白盒子 2015春装新款BF风复古棒球服针织夹克外套女装学生潮流"
      ["goods_img"]=>
      string(85) "http://d04.res.meilishuo.net/pic/_o/73/ae/40a36c0fe0fd5cfc8ef2f6dc8f1a_640_900.ch.jpg"
      ["goods_no"]=>
      string(6) "WT-113"
      ["goods_price"]=>
      string(6) "699.00"
      ["goods_status"]=>
      string(1) "1"
      ["sale_num"]=>
      string(1) "0"
      ["goods_first_catalog"]=>
      string(5) "11801"
      ["goods_catalog"]=>
      string(5) "11971"
      ["shop_category"]=>
      array(1) {
        [0]=>
        object(stdClass)#163 (2) {
          ["category_id"]=>
          string(6) "259220"
          ["category_name"]=>
          string(24) "2015.春款新品.力荐"
        }
      }
      ["stocks"]=>
      array(1) {
        [0]=>
        object(stdClass)#164 (7) {
          ["sku_id"]=>
          string(8) "86993090"
          ["1st"]=>
          string(12) "黑白杂色"
          ["2rd"]=>
          string(6) "均码"
          ["repertory"]=>
          string(2) "12"
          ["goods_code"]=>
          string(9) "WT-113-JM"
          ["color"]=>
          string(12) "黑白杂色"
          ["size"]=>
          string(6) "均码"
        }
      }
    }
    [15]=>
    object(stdClass)#165 (12) {
      ["twitter_id"]=>
      string(10) "3404682893"
      ["goods_id"]=>
      string(9) "241793163"
      ["goods_title"]=>
      string(85) "VB白盒子 2015春装新款女装针织中长款高领套头毛衣女修身连衣裙"
      ["goods_img"]=>
      string(85) "http://d06.res.meilishuo.net/pic/_o/64/eb/52bc5ca969d4278b692b3fc261b0_640_900.ch.jpg"
      ["goods_no"]=>
      string(7) "LYQ-178"
      ["goods_price"]=>
      string(3) "439"
      ["goods_status"]=>
      string(1) "1"
      ["sale_num"]=>
      string(1) "1"
      ["goods_first_catalog"]=>
      string(5) "11801"
      ["goods_catalog"]=>
      string(5) "11977"
      ["shop_category"]=>
      array(1) {
        [0]=>
        object(stdClass)#166 (2) {
          ["category_id"]=>
          string(6) "259220"
          ["category_name"]=>
          string(24) "2015.春款新品.力荐"
        }
      }
      ["stocks"]=>
      array(3) {
        [0]=>
        object(stdClass)#167 (7) {
          ["sku_id"]=>
          string(8) "86989084"
          ["1st"]=>
          string(9) "暗夜黑"
          ["2rd"]=>
          string(6) "均码"
          ["repertory"]=>
          string(1) "3"
          ["goods_code"]=>
          string(11) "LYQ-178-AJM"
          ["color"]=>
          string(9) "暗夜黑"
          ["size"]=>
          string(6) "均码"
        }
        [1]=>
        object(stdClass)#168 (7) {
          ["sku_id"]=>
          string(8) "86989086"
          ["1st"]=>
          string(9) "米白色"
          ["2rd"]=>
          string(6) "均码"
          ["repertory"]=>
          string(1) "2"
          ["goods_code"]=>
          string(11) "LYQ-178-BJM"
          ["color"]=>
          string(9) "米白色"
          ["size"]=>
          string(6) "均码"
        }
        [2]=>
        object(stdClass)#169 (7) {
          ["sku_id"]=>
          string(8) "86989088"
          ["1st"]=>
          string(9) "浅灰色"
          ["2rd"]=>
          string(6) "均码"
          ["repertory"]=>
          string(1) "5"
          ["goods_code"]=>
          string(11) "LYQ-178-CJM"
          ["color"]=>
          string(9) "浅灰色"
          ["size"]=>
          string(6) "均码"
        }
      }
    }
    [16]=>
    object(stdClass)#170 (12) {
      ["twitter_id"]=>
      string(10) "3404674119"
      ["goods_id"]=>
      string(9) "241792633"
      ["goods_title"]=>
      string(85) "VB白盒子 2014秋冬新款简约开叉上衣针织衫半高领套头螺纹毛衣潮"
      ["goods_img"]=>
      string(85) "http://d06.res.meilishuo.net/pic/_o/df/34/58b703a3a7ca5d419601672c5e94_640_900.cf.jpg"
      ["goods_no"]=>
      string(6) "ZT-129"
      ["goods_price"]=>
      string(6) "499.00"
      ["goods_status"]=>
      string(1) "1"
      ["sale_num"]=>
      string(1) "2"
      ["goods_first_catalog"]=>
      string(5) "11801"
      ["goods_catalog"]=>
      string(5) "11947"
      ["shop_category"]=>
      array(1) {
        [0]=>
        object(stdClass)#171 (2) {
          ["category_id"]=>
          string(6) "259220"
          ["category_name"]=>
          string(24) "2015.春款新品.力荐"
        }
      }
      ["stocks"]=>
      array(3) {
        [0]=>
        object(stdClass)#172 (7) {
          ["sku_id"]=>
          string(8) "86986564"
          ["1st"]=>
          string(6) "黄色"
          ["2rd"]=>
          string(6) "均码"
          ["repertory"]=>
          string(2) "10"
          ["goods_code"]=>
          string(10) "ZT-129-AJM"
          ["color"]=>
          string(6) "黄色"
          ["size"]=>
          string(6) "均码"
        }
        [1]=>
        object(stdClass)#173 (7) {
          ["sku_id"]=>
          string(8) "86986566"
          ["1st"]=>
          string(9) "乳白色"
          ["2rd"]=>
          string(6) "均码"
          ["repertory"]=>
          string(1) "2"
          ["goods_code"]=>
          string(10) "ZT-129-BJM"
          ["color"]=>
          string(9) "乳白色"
          ["size"]=>
          string(6) "均码"
        }
        [2]=>
        object(stdClass)#174 (7) {
          ["sku_id"]=>
          string(8) "86986568"
          ["1st"]=>
          string(9) "米灰色"
          ["2rd"]=>
          string(6) "均码"
          ["repertory"]=>
          string(2) "11"
          ["goods_code"]=>
          string(10) "ZT-129-CJM"
          ["color"]=>
          string(9) "米灰色"
          ["size"]=>
          string(6) "均码"
        }
      }
    }
    [17]=>
    object(stdClass)#175 (12) {
      ["twitter_id"]=>
      string(10) "3404655309"
      ["goods_id"]=>
      string(9) "241791489"
      ["goods_title"]=>
      string(85) "VB 2015新款欧美裹胸修身包臀裙连衣裙礼服裙性感紧身聚会短裙子"
      ["goods_img"]=>
      string(85) "http://d05.res.meilishuo.net/pic/_o/43/b6/272da1172233fa39aaaaa8582c04_640_900.cf.jpg"
      ["goods_no"]=>
      string(7) "LYQ-180"
      ["goods_price"]=>
      string(3) "399"
      ["goods_status"]=>
      string(1) "1"
      ["sale_num"]=>
      string(1) "2"
      ["goods_first_catalog"]=>
      string(5) "11801"
      ["goods_catalog"]=>
      string(5) "11977"
      ["shop_category"]=>
      array(2) {
        [0]=>
        object(stdClass)#176 (2) {
          ["category_id"]=>
          string(6) "259220"
          ["category_name"]=>
          string(24) "2015.春款新品.力荐"
        }
        [1]=>
        object(stdClass)#177 (2) {
          ["category_id"]=>
          string(6) "237415"
          ["category_name"]=>
          string(17) "最美年会-LOOK"
        }
      }
      ["stocks"]=>
      array(3) {
        [0]=>
        object(stdClass)#178 (7) {
          ["sku_id"]=>
          string(8) "86981710"
          ["1st"]=>
          string(6) "黑色"
          ["2rd"]=>
          string(1) "S"
          ["repertory"]=>
          string(1) "5"
          ["goods_code"]=>
          string(9) "LYQ-180-S"
          ["color"]=>
          string(6) "黑色"
          ["size"]=>
          string(1) "S"
        }
        [1]=>
        object(stdClass)#179 (7) {
          ["sku_id"]=>
          string(8) "86981712"
          ["1st"]=>
          string(6) "黑色"
          ["2rd"]=>
          string(1) "M"
          ["repertory"]=>
          string(1) "5"
          ["goods_code"]=>
          string(9) "LYQ-180-M"
          ["color"]=>
          string(6) "黑色"
          ["size"]=>
          string(1) "M"
        }
        [2]=>
        object(stdClass)#180 (7) {
          ["sku_id"]=>
          string(8) "86981714"
          ["1st"]=>
          string(6) "黑色"
          ["2rd"]=>
          string(1) "L"
          ["repertory"]=>
          string(1) "5"
          ["goods_code"]=>
          string(9) "LYQ-180-L"
          ["color"]=>
          string(6) "黑色"
          ["size"]=>
          string(1) "L"
        }
      }
    }
    [18]=>
    object(stdClass)#181 (12) {
      ["twitter_id"]=>
      string(10) "3404650657"
      ["goods_id"]=>
      string(9) "241791153"
      ["goods_title"]=>
      string(85) "VB白盒子 2015春装女装新款长袖中长款格纹羊毛呢连衣裙呢子裙子"
      ["goods_img"]=>
      string(86) "http://imgtest.meiliworks.com/pic/_o/86/83/8cda86b65abb4d14c2f088d0be5b_640_900.cg.jpg"
      ["goods_no"]=>
      string(7) "LYQ-181"
      ["goods_price"]=>
      string(6) "389.00"
      ["goods_status"]=>
      string(1) "1"
      ["sale_num"]=>
      string(1) "3"
      ["goods_first_catalog"]=>
      string(5) "11801"
      ["goods_catalog"]=>
      string(5) "11977"
      ["shop_category"]=>
      array(2) {
        [0]=>
        object(stdClass)#182 (2) {
          ["category_id"]=>
          string(6) "259220"
          ["category_name"]=>
          string(24) "2015.春款新品.力荐"
        }
        [1]=>
        object(stdClass)#183 (2) {
          ["category_id"]=>
          string(6) "237415"
          ["category_name"]=>
          string(17) "最美年会-LOOK"
        }
      }
      ["stocks"]=>
      array(1) {
        [0]=>
        object(stdClass)#184 (7) {
          ["sku_id"]=>
          string(8) "86980426"
          ["1st"]=>
          string(12) "黑白格纹"
          ["2rd"]=>
          string(6) "均码"
          ["repertory"]=>
          string(1) "5"
          ["goods_code"]=>
          string(10) "LYQ-181-JM"
          ["color"]=>
          string(12) "黑白格纹"
          ["size"]=>
          string(6) "均码"
        }
      }
    }
    [19]=>
    object(stdClass)#185 (12) {
      ["twitter_id"]=>
      string(10) "3404643729"
      ["goods_id"]=>
      string(9) "241790695"
      ["goods_title"]=>
      string(85) "VB白盒子 2015春装毛衣女装中长款高领打底衫连衣裙针织衫毛衣裙"
      ["goods_img"]=>
      string(85) "http://d04.res.meilishuo.net/pic/_o/ab/0e/f51964b657f96aa8d280e1f55c06_640_900.cg.jpg"
      ["goods_no"]=>
      string(6) "ZT-135"
      ["goods_price"]=>
      string(3) "399"
      ["goods_status"]=>
      string(1) "1"
      ["sale_num"]=>
      string(1) "2"
      ["goods_first_catalog"]=>
      string(5) "11801"
      ["goods_catalog"]=>
      string(5) "11947"
      ["shop_category"]=>
      array(1) {
        [0]=>
        object(stdClass)#186 (2) {
          ["category_id"]=>
          string(6) "259220"
          ["category_name"]=>
          string(24) "2015.春款新品.力荐"
        }
      }
      ["stocks"]=>
      array(1) {
        [0]=>
        object(stdClass)#187 (7) {
          ["sku_id"]=>
          string(8) "86978596"
          ["1st"]=>
          string(6) "灰色"
          ["2rd"]=>
          string(6) "均码"
          ["repertory"]=>
          string(1) "6"
          ["goods_code"]=>
          string(10) "ZT-135-AJM"
          ["color"]=>
          string(6) "灰色"
          ["size"]=>
          string(6) "均码"
        }
      }
    }
  }
  ["total_num"]=>
  string(3) "153"
}
*/