<?php
define('FATAL', "fatal");
define('ERROR', "error");
define('WARNING', "warning");

//淘宝共享appkey
global $top_app_config;
//$top_app_config = array
//(
//    'app_key'    => '21363512',
//    'app_secret' => '5rjnxecdj934YF05dExtYQDd+XiTXGIzhf8PdydyQs8=',
//);
$top_app_config = array
(
    'app_key'    => 'test',
    'app_secret' => 'test',
);
global $ekb_top_app_config;
$ekb_top_app_config = array(
    'app_key'    => '23305776',
    'app_secret' =>'XIzcYNIXYjQZCj0M+BTGvHq6GdFXbVPtF/FpxfSKB9c=',
);
//京东共享appkey
global $jos_app_config;
$jos_app_config = array
(
    'app_key'    => 'B6338AEEDC43043B5398CE3D91795C1F',
    'app_secret' => 'Qdd7KAC2vHxAEQ5oRsq71jLUFhArxIPNSCAldhSA7cs=',
);
//E快帮京东appkey
global $ekb_jos_app_config;
$ekb_jos_app_config = array(
    'app_key'   =>'5DF7C7B419EA5A377DAE9BABCDB3030B',
    'app_secret'=>'w5hQkkp36gcs6VhD6K71mqG+tHHpmHXGUqSMIJpzWxI=',
);

global $g_jos_gate_url ;
$g_jos_gate_url ="http://103.235.243.59/api.php";
//一号店共享appkey
global $yhd_app_config;
$yhd_app_config = array
(
    'app_key' => '10210016042000003957',
    'app_secret' => 'NyZN+WiD6eymB9SJCx0wz+XXhZA8VWL4INawqrS6vmc='
);
//苏宁店共享appkey
global $sos_app_config;
$sos_app_config = array
(
    'app_key' => '104e36691a98ee9e2fb500c8e37b76f5',
    'app_secret' => 'NuPu5w1yLwVbDL7cOuiHMxXHiO8xbbGl9AKazyGLL5E='
);
global $new_sos_app_config;
$new_sos_app_config = array
(
    'app_key' => 'f4d002e1fda38b9e786b95aa89ef1640',
    //'app_secret' => '89244b49bf90fed976e828b18187bdf0'
    'app_secret' => '5SYvgUTk2EZ2f0AFHli4QitmAoxJ2N6SepScjAbAJtY='
);
//阿里巴巴共享appkey
global $alibaba_app_config;
$alibaba_app_config = array
(
    'app_key' => '1004535',
    'app_secret' => 'xy4rrQVElYPD/gE='
);
//新阿里共享appkey
global $new_alibaba_app_config;
$new_alibaba_app_config = array
(
    'app_key' => '7630861',
    'app_secret' => 'G+N8yxyxXb3Lkd/M'
);
//贝贝网
global $bbw_app_config;
$bbw_app_config = array
(
    'app_key' => 'eiii',//app_id
    //'app_secret' => '50e1e34ebf74b1ea3df7a3bc985b4c72',
    'app_secret' => 'WQ+SQxyn6tZelDmFZtqCpNJignmCQAOjb/Lk05tKDl0='
);

//美丽说共享appkey
global $mls_app_config;
$mls_app_config = array
(
    /*'app_key' => 'MJ157582121108',
    'app_secret' => '916c254c18c35271de046e4af3562421'*/
    'app_key' => '100377',
    //'app_secret' => '9C2FB583C63B32F23610A5F5E575EEE5'
    'app_secret' => '2YOEzYaspyC3ushgLC7ugGYQVMosSd7u7U6Vq3MQudE='
);


//飞牛网共享appkey
global $fn_app_config;
$fn_app_config = array
(
    'app_key' => '0153368237767445',
    //'app_secret' => '75ca5b09d9344dac87ed9db5e62bc854'
    'app_secret' =>'Cg+Q+l/8KU72N9DLZxHGtxZrQMIcMnDSMnvKdGzP8VQ='
);
//人人店共享appkey
global $rrd_app_config;  
$rrd_app_config = array
(
    'app_key' => '276dd3cbac353859',
    'app_secret' => 'i3TfusJPVoJLjA/2fMyi+LrwrpgQtBwyY8Eofvs1SZo='
);

//当当网
global $dangdang_app_config;
$dangdang_app_config = array
(
    'app_key' => '2100006529',
    //'app_secret' => "2FC059FC6C142E4D3853F0B98AAE0086"
    'app_secret' => "eFt3CBuiKPK1gJX2pYXqGX6NkvzXOyAUaNmhIm93zLE="
);

//卷皮网
global $jpw_app_config;
$jpw_app_config = array
(
    'app_key' => 'ae15ce97742574d790a48dd424b7e9c7',
    'app_secret' => "E/ZP4IXJHDdl99cVbg8SznqwSqwbZ3bYsYXMOzxYE78="
);
//考拉
global $kl_app_config;
$kl_app_config = array
(
    'app_key' => '2015c793bf5fffe1bda73ca46396e946',
    'app_secret' => 'CVrUwgQzD8edIATG8cscZduO9ZGwjAVi0GlelJKNTlEW0wfIMYJPlg=='
);
//唯品会
global $vip_app_config;
$vip_app_config = array(
    'app_key' => '58fe6cf4',
    'app_secret' => 'z8ot9l08dj1tee55QNdFEmDZQtTVBwfm213JR3VGE3c='
);
global $smt_app_config;
$smt_app_config = array
(
    'app_key' => '3364191',
    'app_secret' => 'XlOqL/XBiynbjE8='
);
//楚楚街
global $ccj_app_config;
$ccj_app_config['org_name'] = 'EKbangM10';
global $rds_name_list;
$rds_name_list = array('rdsb3ticn9p0r3hh2g59');

//支付宝app_id
global $alipay_app_config;
$alipay_app_config['app_id'] = '2017121100553603';
$alipay_app_config['public_app_secret'] = '55UMXoq8rq2ftRp+Z2sfip3sV264ArZReDdehIUvCoAF33wNF4U8tNw5FpQOEelsqk+VaP0A7YlWke2HzOpUAqzKxDrrP92/aCOo/21sehOMbDJRxajUHSQW8LlUPrElgzuzfdu8kPtHlBV3KTVG8YURVbMR+Q34eKvvN5vYNE0wQ5RADSQ+j/ER1F4961pY8Sm0BFEE4GPdrSmeJ5qZ3xRbe2SI/UqevpHSUcH1ty6U2f4MRhqstxNlYe9hGkQn+qJzwW0GZy7cBybr4zBTJHgJ50MIrpAQ1yiiYiAzoBVZgSLQ9l8Zvq2j/MOzf7zeYI51+fz+KmestbNXMTLU2ZPaAwSCGHGbtQ6UaktjfJa3anOvXws0dmLfxSmYcBM3hhhf0P5qTWIB3zlMZ/BOWZvmvfFmRZBjdg8g0t6OnGGTuKB84Tz2Oeng5L+ehFVH3gnreFIb1P0H/7extfd4ALRCf8Wy7xBj1Yx7L1Wsw66V1z/U0uaE3wz708rvWXdAWjSle1WgW4A=';
//$alipay_app_config['public_app_secret'] = 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAjjrLwOIu4irIN3OdrubCpmOc63WPj7lcigQgZLgeJh4lcUwkwQMZsZ4Pzm7SRl65bMHrLBMHKfc4xd0eOe2jQmDniNJgBjfN4hITD6IM4sfcNeGDTEcb3QVUOgtzIGu2AWsIGixUOhXWwb//m5ZycAZixCp4XekjmDQti6EbD5PVWp8K8aJ/3RNhiPezCwuhllXqCpaXx7CHiUSV+7uIUTKbYd5Am6o//cyp6AgRokZnv0E9AV/qwPFipvCe6S+l3YQVdDV4ZY58qUNBVdxk8t9v74iuy8uwx/bzQOIThTneP002428F6UT+lI75f2qwor8V5j0s1lQ86sacpmNYewIDAQAB';
$alipay_app_config['private_app_secret'] = '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';
$g_tmc_bt_config = array
(
    'host'            => '10.128.2.207',
    'port'            => 11300,
    'connect_timeout' => 5,
    'tube'            => 'tmc_out'
);

//微盟旺店
global $wm_app_config;
$wm_app_config = array
(
    'app_key' => '542091285D59B0CC7489F9E97F94E7ED',
    'app_secret' => 'kbDZo0/xrWOFP8pBOLptU0B3Ueds/6OqYZ7nsr9WkO8='
);
//折800
global $zhe_app_config;
$zhe_app_config = array
(
    'app_key' => 'NDQwMzk2MzEtNzUw',
    'app_secret' =>'CCrixdsgW228kytl71RKFxC6bmS1tAiappXjhKnRUCk=',
);

//返利网
global $flw_app_config;
$flw_app_config = array
(
    'app_key' => 'MTMzRDg4RTUzNzA4QzY2MQ==',
    'app_secret' => 'KEkAubqU8gHRB4qncyqPdsJGYdfWIT8jzK7tePAVrMA='
);

//有赞 口袋通
global $kdt_app_config;
$kdt_app_config = array
(
    'app_key' => '419021b25a14a5094b',
    'app_secret' => 'QsV6dWMFezj9Qc0knXRBlyNQ1nmlyD9lEpDt4TVzkNQ=',
);
//微店
global $wd_app_config;
$wd_app_config = array
(
    'app_key' => '690383',
    'app_secret' =>'8CabydSMQIVHduqLzckIB15XKGYAtn3E+0h4MunIUvQ=',
);
//拼多多
global $pdd_app_config;
$pdd_app_config = array
(
    'app_key' => 'c7ca670b585c4579ab69b50408aa9006',
    'app_secret' => 'GP2V17UyzqYBCVOs1GlfU8udJDQIib6w3Hdl61Kz1GUsWP9xvUVRQQ==',
);
//融e购
global $icbc_app_config;
$icbc_app_config = array(
    'app_key' => 'R9ZvXZz8',
    'app_secret' => '888888'
);


global $g_eshop_router_url;
$g_eshop_router_url = "http://10.132.180.236/index.php";

define('YHD_API_URL', 'http://openapi.yhd.com/app/api/rest/router');
define('AMAZON_API_URL', 'https://mws.amazonservices.com.cn/Orders/2013-09-01');
define('COO8_API_URL', 'http://api.coo8.com/ApiControl');
//当当
define('DD_NEW_API_URL', 'http://api.open.dangdang.com/openapi/rest');

//淘宝https请求地址（加密解密使用）
define('TOP_API_HTTPS_URL', 'https://eco.taobao.com/router/rest');
//淘宝加密解密安全令牌码
define('TOP_SECURITY_CODE', 'l/3xXTkfLEqoKPRYWI1MbSamK4KKObzzpqw0q+RRgak=');

global $g_jst_hch_enable;
if(stristr(PHP_OS, 'WIN')){
    $g_jst_hch_enable = false;
}else{
    $g_jst_hch_enable = true;
}


global $current_front_host;
$intranet_ip = exec("/sbin/ip addr show eth0|grep 'inet '|awk '{print $2}'|cut -d/ -f1");

//beanstalkd config
$g_bt_config = array(
    'host'            => $intranet_ip,
    'port'            => 11300,
    'connect_timeout' => 5
);

//主服务器配置。用来存储统计数据，svn上不对  线上修改
global $g_trade_main_db_config;
$g_trade_main_db_config = array(
    'host' => '',
    'db_user' => '',
    'db_pwd' => '',
    'db_name' => ''
);

global $g_jst_db_config_list;
$g_jst_db_config_list = array(array('db_name' => 'sys_info','host' => 'rdsb3ticn9p0r3hh2g59.mysql.rds.aliyuncs.com','instance' => 'rdsb3ticn9p0r3hh2g59',
                                     'user' => 'ekbrdsuser1','pwd' => '6uKnIYar4IZgJSjw','salt'=>'@8d)ieOWdu#$kd24'),
                                array('db_name' => 'sys_info','host' => 'rds647n9zoox5029f98c.mysql.rds.aliyuncs.com','instance' => 'rds647n9zoox5029f98c',
                                     'user' => 'u_test','pwd' => 'LKCRavNFJkblpI/k','salt'=>'iE9)wA2oc$9E,'),
                         );

global $g_email_num;
$g_email_num=3;
global $g_email_host;
$g_email_host = "SMTP.163.com";
global $g_email_user_name;
$g_email_user_name = "ekb_erp@163.com";
global $g_re_email_user_name;
$g_re_email_user_name = "ekb_erp1@163.com";
global $g_email_password;
$g_email_password = "ekb1234";
global $g_re_email_password;
$g_re_email_password = 'Ekb,1234';
global $g_email_sender_account;
$g_email_sender_account = "ekb_erp@163.com";
global $g_email_sender_name;
$g_email_sender_name = "E快帮";
global $g_email_sentto_list;
$g_email_sentto_list = array("wangshengyong@wangdian.cn","sunyi@wangdian.cn","wangbo1@wangdian.cn");

?>
