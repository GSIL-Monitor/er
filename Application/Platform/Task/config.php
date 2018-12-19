<?php

//log dir
$g_log_dir = ROOT_DIR . "/../Runtime/Logs/default/Platform";

//临时文件目录
$g_tmp_dir = ROOT_DIR . '/../Runtime/File';

//是否调试环境
//$g_debug_sid = 'erp_dev';

//$g_debug_shopid = 22;

//使用聚塔同步
$g_use_jst_sync = true;

////////////////////////////////////
//当前cluster
$current_cluster = 'dev';
//当前前端机
//$current_front_host = '101.200.202.174';

$g_wlb_code_map = array
(
    'COSCO-000'  => 'ZY',
    'QDHEWL-001' => 'QDRRS'
);

//将tube映射到module，如果没指定则module与tube名相同
$g_tube_mod_map = array
(
    'svc_task'    => 'svc',
    'trade_test6' => 'trade'
);


/*$g_top_gate_url = 'http://121.199.38.85/api/api.php';
$g_gate_key = 'wdt2212LKkd';*/
$g_top_gate_url = '';
$g_gate_key     = '';

//$g_eshop_router_url = "http://121.199.38.85:10000/api";

?>
