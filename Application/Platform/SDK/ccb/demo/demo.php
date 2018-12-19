<?php

require_once('../ccbClient.php');
require_once('config.php');

$client = new ccbClient();
$client->setTranCode = $tran_code;
$client->setCustId = $cust_id;
$client->setTranSid = $tran_sid;

$params = array(
    'head' => array(
	    'tran_code' => $tran_code,
	    'cust_id' => $cust_id,
	    'tran_sid' => $tran_sid
    ),
    'body' => array(
        'order' => array(
            'start_created' => '2015-10-20 06:00:00',
            'end_created' => '2015-10-20 06:00:00',
			'status' => ''
        ),
    ),
);

$xml = '<?xml version=“1.0” encoding=“UTF-8”?>
<response>
 	<head>
   	<tran_sid>20120627000000000622</tran_sid>
	<cust_id>0011</cust_id>
   	<ret_code>000000</ret_code>
   	<ret_msg></ret_msg>
 	</head>
 	<body>
   		<order_items>
			<!—order_info可重复 -->
			<order_info>
				<order_id>120725162516105</order_id>
<order_memo>周末不在家，如要送货请提前电话联系</order_ memo>
<status>WAIT_SELLER_SEND_GOODS</status>
				<buyer_email>test@abc.com</buyer_email>
				<buyer_name>张三</buyer_name>
				<order_time>2012-07-25 16:25:16</order_time>
				<payment_time>2012-07-25 16:29:39</payment_time>
				<order_prod_amt>8156.00</order_prod_amt>
				<order_pay_amt>8056.00</order_pay_amt>
<order_coupon>100</order_coupon>
				<merchant_discount>0</merchant_discount>
<delivery_fee>0</delivery_fee>
				<shipping_info>
					<consignee_name>李四</consignee_name>
					<consignee_province>广东</consignee_province>
					<consignee_city>广州市</consignee_city>
					<consignee_county>天河区</consignee_county>
					<consignee_address>华景路xx号</consignee_address>
					<consignee_zip>222222</consignee_zip>
					<consignee_mobile>18663139929</consignee_mobile>
					<consignee_phone>0532-88931301</consignee_phone>
				</shipping_info>
				<product_items>
					<!-- item可重复，以便实现一个订单包括多个商品 -->
					<item>
						<sku_id>BA09X004Z</sku_id>
						<pro_id></pro_id>
						<prod_name>华帝油烟机某某型号</prod_name>
						<prod_buy_amt>2</prod_buy_amt>
						<prod_price>1899.00</prod_price>
						<prod_discount>100.00</prod_discount>
					</item>

					<item>
						<sku_id >AA8Y09000</sku_id>
						<prod_name>Iphone3gs</prod_name>
						<prod_buy_amt>2</prod_buy_amt>
						<prod_price>2229.00</prod_price>
						<prod_discount>0</prod_discount>									</item>
				</product_items>

				<invoice_info>
					<invoice_type>1</invoice_type>
					<invoice_title>广州建发有限公司</ invoice_title>
					<tax_payer_id>370202794012379</tax_payer_id>
	<register_address>
广州市建工路10号
</register_address>
					<register_phone>020-80790195</register_phone>
	<bank_name>
中国建设银行广东省分行天河工业园支行
</bank_name>
					<bank_acount>622200322106622</bank_acount>
				</invoice_info>
			</order_info>
		</order_items>
 </body>
</response>';

echo "\n".'arr2xml: '."\n";
$retval = $client->arr2xml($params);
print_r($retval);

echo "\n".'arr encode: '."\n";
$retval = json_encode($params);
print_r($retval);

echo "\n".'xml2arr: '."\n";
$retval = $client->xml2arr($xml);
print_r($retval);

echo "\n".'xml encrypt: '."\n";
$retval = $client->encrypt($xml);
print_r($retval);


echo "\n".'xml decrypt: '."\n";
$retval = $client->decrypt($retval);
print_r($retval);

if($xml === $retval)
	echo "\n".'xml = retval'."\n";

$temp = array(
	'tran_code' => $tran_code,
	'cust_id' => $cust_id,
	'tran_sid' => $tran_sid,
	'ccbParam' => '<?xml version=“1.0” encoding=“UTF-8”?>
<request>
 	<head>
   		<tran_code>交易代码</tran_code>
<cust_id>客户编号</cust_id>
   		<tran_sid>流水号</tran_sid>
 	</head>
 	<body>
 	</body>
</request>'
);
print_r($temp);
echo "\n http:\n";
$retval = $client->http($temp);
print_r($retval);

/*echo "\n".'params soap'."\n";
$clientt = new SoapClient("http://110.249.210.77:8092/FormOrder.asmx?wsdl");
$retval = $clientt->__call('T0007', $params);
print_r($retval);*/



