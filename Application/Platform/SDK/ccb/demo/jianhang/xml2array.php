<?php

$xml = "
<?xml version=“1.0” encoding=“UTF-8”?>
<response>
 	<head>
   		<tran_sid>20120627000000000622</tran_sid>
		<cust_id>0011</cust_id>
   		<ret_code>000001</ret_code>
   		<err_msg>缺货</err_msg>
 	</head>
 	<body>
   		<order_id>20120627000000088888</order_id>
   		<order_timeout>120</order_timeout>
   		<sku>
      		 <sku_id>123123123</sku_id>
       		<stock>0</stock>
  		 </sku>
   		<sku>
       		<sku_id>234234234</sku_id>
       		<stock>1</stock>
   		</sku>
</body>
</response>
";
/**
 * 最简单的XML转数组
 * @param string $xmlstring XML字符串
 * @return array XML数组
 */
function simplest_xml_to_array($xmlstring) {
	return json_decode(json_encode((array) simplexml_load_string($xmlstring)), true);
}
//$res = string($xml);
print_r($xml);