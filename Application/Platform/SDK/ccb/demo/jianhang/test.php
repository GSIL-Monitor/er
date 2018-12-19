<?php
$client = new SoapClient("http://211.90.16.91:8092/FormOrder.asmx?wsdl");
$end_time = date ( 'Y-m-d H:i:s', time() );
$begin_time = date("Y-m-d H:i:s" , time()-60);
$status = '';
$arrayName = array('begin_time' => $begin_time, 'end_time' => $end_time , 'status' => '');
print_r($arrayName);

$return = $client->GetOrderFormByTimeAndState($arrayName);
echo "来了GetOrderFormByTimeAndState=";print_r($return);

$tmp = $return->GetOrderFormByTimeAndStateResult;
echo "来了GetOrderFormByTimeAndStateResult=";print_r($tmp);

$retval = json_decode($tmp);
echo "来了json_decode=";print_r($retval);
$trade = $retval->SALES_ORDER;
print_r($retval);