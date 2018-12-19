<?php
require_once('../ccbClient.php');

$appkey = 'yanfa018';
$secret = 'ECPB2CABCDEFGHIJ';

//$tran_code = '5445524';//相当于 method
$cust_id = '5445524';//客户唯一标识 yanfa018/5445524

$tran_sid= substr(md5(time()),0,20);//流水码20位 每个只能使用一次

//trade list
$start_time = '2015-05-07 00:00:00';
$end_time = '2015-05-08 00:00:00';
$page_num = 1;
$page_size = 2;
$status = 'WAIT_SELLER_SEND_GOODS';


//trade detail
$tid = '201407180000062679';


//logistics
$logistics_code = '0000000002';
$out_sid = '123456';
$type = '0';

//
/*
42597b9460736ee65c6e
3bdaefb9493e9420524e
990a47b59596ab28e311

*/

function logx($msg,$title){
	$time = time();

	$dt = date('Y-m-d',$time);
	$ddt = date('Y-m-d H:i:s',$time);
	$filepath = dirname(__FILE__)."/logx/$dt/test.log";

	@mkdir(dirname(__FILE__)."/logx");
	@mkdir(dirname(__FILE__)."/logx/$dt");

	if(empty($msg))
		$msg = 'null';

	file_put_contents($filepath, "$ddt $title: \n".print_r($msg,true)."\n\n",FILE_APPEND);

}

