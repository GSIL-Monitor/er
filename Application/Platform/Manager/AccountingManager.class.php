<?php
namespace Platform\Manager;

require_once(ROOT_DIR . "/Manager/utils.php");
require_once(ROOT_DIR . "/Manager/Manager.class.php");
require_once(ROOT_DIR.'/Common/api_error.php');
require_once(TOP_SDK_DIR . '/top/Logger.php');
require_once(TOP_SDK_DIR . '/top/RequestCheckUtil.php');
require_once(TOP_SDK_DIR . '/top/TopClient.php');
require_once(TOP_SDK_DIR . '/alipay/AopSdk.php');
require_once(TOP_SDK_DIR . '/alipay/aop/request/AlipayDataDataserviceBillDownloadurlQueryRequest.php');


class AccountingManager extends Manager
{
    public static function register() {
        registerHandle("acc_merchant", array("\\Platform\\Manager\\AccountingManager", "listAccountingShops"));
        registerHandle("acc_syn_shop", array("\\Platform\\Manager\\AccountingManager", "getAccountingRecord"));
        registerHandle("acc_get_result", array("\\Platform\\Manager\\AccountingManager", "getAccountingResult"));
        registerHandle("acc_check", array("\\Platform\\Manager\\AccountingManager", "alipayAccountCheck"));
    }

    public static function  Accounting_main()
    {
        return enumAllMerchant('acc_merchant');
    }

    public static function listAccountingShops($sid)
    {
        $db = getUserDb($sid);
        if(!$db)
        {
            logx("listAccountingShops getUserDb failed!!", $sid.'/Accounting');
            return TASK_OK;
        }

        $accountSync = getSysCfg($db, 'alipay_download_account_sync', 0);

        if(!$accountSync)
        {
            releaseDb($db);
            return TASK_OK;
        }

        $result = $db->query("select shop_id,platform_id,account_nick,alipay_app_key,alipay_auth_app_id from cfg_shop where platform_id=1 and auth_state=1 and pay_auth_state=1 and is_disabled=0");

        if(!$result)
        {
            releaseDb($db);
            logx("query shop failed!", $sid.'/Accounting');
            return TASK_OK;
        }

        while($row = $db->fetch_array($result))
        {
            if(!checkAlipayAuth($row))
            {
                markAlipayAuthExpired($db,$row['shop_id']);
                logx(print_r($row,true).'支付宝授权不存在',$sid.'/Accounting');
                continue;
            }

            $row['sid'] = $sid;
            pushTask('acc_syn_shop', $row, 0, 1024, 600, 300);
        }

        $db->free_result($result);
        releaseDb($db);

        return TASK_OK;
    }


    /*
     * 蚂蚁金服开放平台  查询对账单
     * type 默认为日账单，1为月账单
     * */
    public static function asyncDownloadRecord(&$db, &$shop,$start_date,$end_date)
    {
        global $g_tmp_dir;
        $sid = $shop->sid;
        $shop_id = $shop->shop_id;
        $session = $shop->alipay_app_key;
        if(empty($session))
        {
            //店铺置为支付宝授权失效。
            markAlipayAuthExpired($db,$shop_id);
            releaseDb($db);
            return TASK_OK;
        }
        $ptime=$end_date;
        if($start_date == $ptime)
        {
            releaseDb($db);
            return TASK_OK;
        }
        $appkey = '';
        $private_appsecret = '';
        $public_appsecret = '';
        getAlipaySecret($appkey,$private_appsecret,$public_appsecret);

        logx("alipay date: {$shop->shop_id} $start_date $end_date", $sid.'/Accounting');


        $ptime = $start_date;
        $loop_count = 0;
        $start_time = strtotime($start_date);
        $end_time = strtotime($end_date);
        $ptime_stamp = strtotime($ptime);


        while ($ptime_stamp < $end_time)
        {
            $ptime = ($end_time - $ptime_stamp > 3600 * 24) ? date('Y-m-d',($start_time + 3600 * 24)) : $end_date;
            $loop_count++;
            if ($loop_count > 1) resetAlarm();

            logx("alipay_query ".$ptime.' '.$end_date,$sid.'/Accounting');

            $aop = new \AopClient ();
            //$aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
            $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
            $aop->appId = $appkey;
            //$aop->appId = '2016082700322284';
            $aop->rsaPrivateKey = $private_appsecret;
            //$aop->rsaPrivateKey = 'MIIEpAIBAAKCAQEAq5cS9hBL13ngIMVyCEx9cfd6/JY7TG4dap8fdvh+glzlVUfDhkSf9HlcTndQQ/iWKV+8mkF+Vk4LC6YiD5SlSULUnDgKmP4F2iFrqEF3J0OZc+q5icDJd+4RHkBiG2bv7EmCHKHfiFwC8e8Iaf/nQh47Rxl8kjdYhWolYIeo14re83pDNYMZpkuawhITKsGt1K/uUyQ8oyx4C1viECG5djDwEnEJOsB1nVMo8R1AOEHZmOdM39tjqkKOqIAo4AVZ8KTEczE90VIrFpzvrXnvZ3GLFKdW8186cDyw4bHJKbCFQk/4F6/+JVG/xeSF3JpznoHyYzn8Wl20Fu15OYjFFwIDAQABAoIBAQCcjxcJ1Aye6eCJhh1pXQEIPxnD5P7t2XqVkeIPluM25rOIgBXyKCMZ2LFUFqDxo5q+3U8kH6W58TM6ybZCKQo2MffzIV7qALwuLlggCLtC4/bbQMtQ2Mn51wlfZLce8WjvWpKQtVFTBUDapZIzxP2n4hWL5cE3V7A46oR38s8m+VtSmR2vQFXJXHU5e35VlJkiJ/RTg6Kfs4ZyzoMEMmqrydUaICwKSRRJy7KQ2uuL6i63UwgOELyOKNbLsYnwDDiHQGu+gOpIxdDaMgl23cWobq7asONtOr7DvQCOnWvcoR7RLwrAmx0cdm6hzJggfyNJoIZxsZQuiZ/MwA83dhgBAoGBANv1+FNHLR+MOmDJXa7D4Glw4Bp+uIN4SNxH6hi4t3SZ4+kIigS0gkq3NVUBIuTz7eQ1vTlq1AAo9tUUGT+vmcDIhP3RDqOaKOUWzfMLSwAYW+6JQggsGZTRSSHgD70KyKxwrZHcouLkn5vbEU8u9JJhjrUBxvn6wyVeX4tRiVUBAoGBAMe0PtLVkIomLp5q9XeycIW6B+9i0H6AIKqm8c3rWdV86cAtEkzVEF1WJcoWlpXOBZVygDMiLN0l3CtWi6MFjCZ021Cc+k3dkDioro3DW4aZD/9iLV5nhHpr2gqQaXBg0K3pQj5MA5Ph3DPx+C2QLd6Y+ulSe2R23mV09oug6CIXAoGAP+dWHduv4Fp9G2FlNkDyEbAZa5klQgzQHi9Gc6g2pEmRTUKN1pAaylovxGJwINQ9aO+z6dp/fQxpqb4NF9OMd9XJzXPPLPi8qNHXQ6UkRQLOsp5t8LHfPL0Q4iaWa/WWF4Mk6huPtxt0w3MBtF+P7ncpXq0Fgdq4l0Kzv2YQ4AECgYBzqMyJxu6DVEHDtiacQFgy2t8loZEm8oX4z99TZ28L2eB3UKM8pFlp9S7Fr/deo4dQWpQtCSn6mqa84s7Uh633x84NDh1ZY2zXo7oUmIQ1nAhL3ExyVHnBfR026RRn5Wp2jpWzBss7pp+l5gnaOZqXRPpsjzyvnriHAgqYK4TF2QKBgQCiqYOkJ+qUxVexyqY7I3TjMGqeNjx/gcqaxSI4kULxWM9gvgTibysRGRx/knuPWK8tJpk40gj/ijRLessuhoygpfFtgBUOnUcWaNdSrGsGZsGjjcG6bZLn4Cuc0m62L5ea75ETgPnYkaEbfs+9e+VqssZ5QQkO4/JUh2LiabXg9A==';
            $aop->alipayrsaPublicKey= $public_appsecret;
            //$aop->alipayrsaPublicKey= 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAs5yPvRTtuLLAGW2E5V9ZPHFOtUxfcDJy5QMDe7QPb+V97K8mEZhwmNsB4E8U/lwd9ne4rG+BYJdPRI+jhxjUuv1b5LH/FTX0uUJxfIJ4z4YZIyIbl/smF0p8sAwGeKVQ21DhYWhTKopBXlIrcIgFArjJDQETuNcX/QpJiiWFv7phX9lnGxw/MJg6UlMbfj+KbAmFbptceC8Vn+8t7YOfD54yVWivVo42w1k3ecpSex++lTk6kV0csb4ZQNUMtJKnMt5VramIb57r3XS/cgYeJ3wXZu1OBs2V4jNaTnmUg225Ho6Q0CrtkIdcUjW1qEKuVk5U3yTdfXNXrLc/lYSkUwIDAQAB';
            $aop->apiVersion = '1.0';
            $aop->signType = 'RSA2';
            $aop->postCharset='UTF-8';
            $aop->format='json';
            $request = new \AlipayDataDataserviceBillDownloadurlQueryRequest  ();
            //获取基于基于商户支付宝账单    type传trade 或signcustomer 不影响，会把所有的账单都下载下来。
            $request->setBizContent("{" .
                "\"bill_type\":\"signcustomer\"," .
                "\"bill_date\":\"$ptime\"" .
                "  }");
            $result = $aop->execute ( $request,'',$session);

            $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
            $retval = $result->$responseNode;
            $resultCode = $retval->code;
            $resultSubCode = @$retval->sub_code;
            if(!empty($resultCode)&&$resultCode == 10000)
            {
                resetAlarm();
                $bill_download_url = $retval->bill_download_url;//链接有效时间30秒
                logx('url:'.print_r($bill_download_url,true),$sid.'/Accounting');
                $dir = $g_tmp_dir.'/'.$sid;
                if(!is_dir($dir))
                {
                    mkdir($dir,0755);
                }
                $file_path = $dir.'/bill_'.$ptime.'.zip';
                if(file_exists($file_path))
                {
                    $start_time = $ptime_stamp + 3600*24;
                    $ptime_stamp = $ptime_stamp +3600*24;
                    logx('当天账单已下载:'.print_r($file_path,true),$sid.'/Accounting');
                    continue;
                }
                logx($file_path,$sid.'/Accounting');
                $fp = fopen($bill_download_url,'r');
                if(!$fp)
                {
                    logx('打开url文件失败',$sid.'/Accounting');
                    return TASK_SUSPEND;
                }
                $my_file = fopen($file_path,'w+');
                if(!$my_file)
                {
                    logx('打开写入文件失败',$sid.'/Accounting');
                    return TASK_SUSPEND;
                }
                if($fp)
                {
                    while(!feof($fp))
                    {
                        fwrite($my_file, fgets($fp)."");
                    }
                    logx('保存文件成功',$sid.'/Accounting');
                }
                fclose($fp);
                fclose($my_file);
                unset($fp);
                unset($my_file);

                $dir = $g_tmp_dir.'/'.$sid;
                $zip_files = glob($dir.'/*.zip');
                if(empty($zip_files))
                {
                    logx('无压缩文件',$sid.'/Accounting');
                    return TASK_OK;
                }
                $zip = new \ZipArchive();

                $bill_date = substr($file_path,strrpos($file_path,'_')+1,-4);
                $bill_dir = $dir.'/'.$bill_date;
                if(!is_dir($bill_dir))
                {
                    mkdir($bill_dir,0777);
                }
                $zip_resource = $zip->open($file_path);
                //有中文名乱码，直接解压会失败，先改名
                if($zip_resource===true)
                {
                    $zip->renameIndex(0,$sid.'_'.$bill_date.'_1.csv');
                    $zip->renameIndex(1,$sid.'_'.$bill_date.'_2.csv');
                    $zip->renameIndex(2,$sid.'_'.$bill_date.'_3.csv');
                    $zip->renameIndex(3,$sid.'_'.$bill_date.'_4.csv');
                }
                $zip->close();


            } elseif($resultSubCode=='isp.bill_not_exist')
            {
                $start_time = $ptime_stamp + 3600*24;
                $ptime_stamp = $ptime_stamp +3600*24;
                logx($ptime.'当天账单不存在',$sid.'/Accounting');
                setSysCfg($db, "accounting_sync_time{$shop_id}", $ptime);
                logx("shop: $shop_id save_time: $ptime ",$sid.'/Accounting');
                continue;
            }elseif($resultSubCode=='isv.invalid_arguments')
            {
                logx('请求参数错误:'.print_r($request,true),$sid.'/Accounting');
                return TASK_OK;
            }else
            {
                logx('retval:'.print_r($retval,true),$sid.'/Accounting');
                return TASK_OK;
            }

            $start_time = $ptime_stamp + 3600*24;
            $ptime_stamp = $ptime_stamp +3600*24;
            logx("shop: $shop_id save_time: $ptime ",$sid.'/Accounting');
            setSysCfg($db, "accounting_sync_time{$shop_id}", $ptime);
        }
        return true;
    }




//accounting_sync_time
    public static function getAccountingRecord($shop)
    {
        $sid = $shop->sid;
        $shop_id = $shop->shop_id;
        $db = getUserDb($sid);
        if(!$db)
        {
            logx("getAccountingRecord getUserDb failed!!", $sid.'/Accounting');
            return TASK_OK;
        }

        $date = date('Y-m-d',strtotime('-1 day'));

        $lastSyncTime = getSysCfg($db, "accounting_sync_time{$shop_id}", 0);
        if($lastSyncTime == 0) //第一次下载月账单,按月下载无法下载当月账单统一走按天。
        {
            $start_date = date('Y-m-d',strtotime('-1 month'));
        }
        else if($date == $lastSyncTime)//每天下载一次
        {
            $lastDataSyncTime =  getSysCfg($db, "accounting_data_sync_time{$shop_id}", 0);
            if($lastDataSyncTime !=$date)
            {
                self::getZip($db,$shop);
            }
            releaseDb($db);
            return TASK_OK;
        }
        else
        {
            if($date - $lastSyncTime > 31*24*3600)//间隔时间超过31天 ,最多下载30天账单
            {
                $start_date = date('Y-m-d',strtotime('-1 month'));
            }
            else
            {
                $start_date =  $lastSyncTime; //上次获取时间保存为当天的时间格式。 例 2017-12-23
            }
        }
        $end_date = date('Y-m-d',strtotime('-1 day'));//不能下载当天的账单


        $record_result = self::asyncDownloadRecord($db, $shop,$start_date, $end_date);
        if(!$record_result)
        {
            releaseDb($db);
            return TASK_OK;
        }
        logx("alipay_download_ok shopid:$shop_id",$sid.'/Accounting');
        self::getZip($db,$shop);
        releaseDb($db);
        return TASK_OK;
    }

    public static function getZip($db,$shop)
    {
        global $g_tmp_dir;
        $sid = $shop->sid;
        $shop_id = $shop->shop_id;
        //下载完文件后进行解压缩。
        $dir = $g_tmp_dir.'/'.$sid;
        $zip_files = glob($dir.'/*.zip');
        if(empty($zip_files))
        {
            logx('无压缩文件',$sid.'/Accounting');
            releaseDb($db);
            return TASK_OK;
        }
        $zip = new \ZipArchive();
        $last_bill_data = getSysCfg($db,'accounting_data_sync_time'.$shop_id,0);
        foreach($zip_files as $v)
        {
            $bill_date = substr($v,strrpos($v,'_')+1,-4);
            if($bill_date<=$last_bill_data)
            {
                unlink($v);
                continue;
            }
            $bill_dir = $dir.'/'.$bill_date;
            if(!is_dir($bill_dir))
            {
                mkdir($bill_dir,0777);
            }
            $zip_resource = $zip->open($v);
            if($zip_resource===true)
            {
                $zip->extractTo($bill_dir.'/');
                $status = $zip->getStatusString();
                logx(print_r($status,true),$sid.'/Accounting');
                if($status =='No error')
                {
                    self::getCsvContent($db,$shop,$v,$bill_dir);//读取csv中的内容
                }else
                {
                    logx('文件'.$v.'解压失败',$sid.'/Accounting');
                    continue;
                }

            }else
            {
                logx('文件'.$v.'解压失败',$sid.'/Accounting');
                continue;
            }
            $zip->close();
        }
    }

    public static function getCsvContent($db,$shop,$zip_path,$bill_dir)
    {
        $bill_files = glob($bill_dir.'/*.csv');
        $data = array();
        foreach($bill_files as $v)
        {

            $file_source = fopen($v,'r');

            while($row = fgetcsv($file_source) )
            {
                //必须进行转码处理中文
                foreach($row as $m=>$n)
                {
                    $row[$m] = iconv('gb2312','utf-8',$n);
                }
                $data[] = $row;
            }
            fclose($file_source);

        }
        //logx('读取csv文件完成。内容:'.print_r($data,true),$sid.'/Accounting');

        self::loadDataByCsv($db,$shop,$data,$zip_path,$bill_dir);
    }

//保存支付记录
    public static function loadDataByCsv($db,$shop,$data,$zip_path,$bill_dir)
    {
        $start_key = '';
        $end_key = '';
        $sid = $shop->sid;
        $shop_id = $shop->shop_id;
        $save_date = substr($bill_dir,strrpos($bill_dir,'/')+1,10);
        $cur_time = date('Y-m-d H:i:s',time());
        //将账务明细数据获取
        foreach($data as $k=>$v)
        {

            if(strpos($v[0],'------------账务明细列表------'))
            {
               $start_key = $k+2;
            }
            if(strpos($v[0],'----------------账务明细列表结束------------'))
            {
                $end_key = $k-1;
            }
            if(strpos($v[0],'------------账务汇总列表------'))
            {
                $start_count_key = $k+2;
            }
            if(strpos($v[0],'----------------账务汇总列表结束------------'))
            {
                $end_count_key = $k-1;
            }
        }
        if(!empty($start_key) && !empty($end_key))
        {
            for($i=$start_key;$i<=$end_key;$i++)
            {
                $bill_detail[] = $data[$i];
            }
        }
        if(!empty($start_count_key) && !empty($end_count_key))
        {
            for($i=$start_count_key;$i<=$end_count_key;$i++)
            {
                $bill[] = $data[$i];
            }
        }

        if(empty($bill_detail))
        {
            logx('账务明细为空',$sid.'/Accounting');
            setSysCfg($db,'accounting_data_sync_time'.$shop_id,$save_date);
            self::deldir($bill_dir);
            return TASK_OK;
        }

        /*foreach($bill_detail as $v)
        {
            $bill_list[] = array(
                'platform_id' => 1,
                'shop_id' => $shop_id,
                'alipay_order_no'       => $v[0],
                'raw_order_no'          => $v[1],
                'item'                  => $v[2],
                'goods_name'            => $v[3],
                'alipay_create_time'    => $v[4],
                'alipay_complete_time'  => $v[5],
                'store_no'              => $v[6],
                'store_name'            => $v[7],
                'operator_name'         => $v[8],
                'terminal_num'          => $v[9],
                'opt_pay_account'       => $v[10],
                'order_amount'          => $v[11],
                'in_amount'             => $v[12],
                'alipay_red_packet'     => $v[13],
                'ollection_treasure'    => $v[14],
                'alipay_preferential'   => $v[15],
                'seller_preferential'   => $v[16],
                'amount_voucher'        => $v[17],
                'amount_name'           => $v[18],
                'seller_red_packet'     => $v[19],
                'card_consumption'      => $v[20],
                'refund_batch_no'       => $v[21],
                'service_amount'        => $v[22],
                'share_benefit'         => $v[23],
                'remark'                => $v[24],
            );
        }*/
        //账务明细汇总
        foreach($bill as $v)
        {
            $bill_list[] = array
            (
                'platform_id'   =>1,
                'shop_id'   =>$shop_id,
                'item'  => $v[0],
                'in_num'    => $v[1],
                'in_amount' => $v[2],
                'out_num'   => $v[3],
                'out_amount'    => $v[4],
                'total_amount'  => $v[5],
                'create_time'  => $save_date,
                'created'   => $cur_time
            );
        }

        //账务明细
        foreach($bill_detail as $v)
        {
            //财务流水号
            $financial_no = $v[0];
            //业务流水号
            $business_no = $v[1];
            //商户订单号
            $merchant_order_no = trim_all_space($v[2]);
            //商品名称
            $goods_name = $v[3];
            //发生时间
            $order_time = $v[4];
            //对方账户
            $pay_account = $v[5];
            //收入金额
            $in_amount = $v[6];
            //支出金额
            $out_amount = $v[7];
            //账户余额
            $balance = $v[8];
            //交易渠道
            $order_pass = $v[9];
            //业务类型
            $item =$v[10];
            //备注
            $remark = trim_all_space($v[11]);
            //系统订单号
            $order_no = '';
            if(strpos($merchant_order_no,'T200P')!==false)
            {
                $order_no = substr($merchant_order_no,5);
            }
            if(strpos($merchant_order_no,'CAE_CHARITY_')!==false)
            {
                $order_no = substr($merchant_order_no,12);
            }
            if(strpos($merchant_order_no,'T10000P')!==false)
            {
                $order_no = substr($merchant_order_no,7);
            }
            if(mb_strpos($remark,'保费收取(')!==false)
            {
                $order_no = mb_substr($remark,mb_strpos($remark,'(')+1,-1);
            }
            if(mb_strpos($remark,'淘宝订单号')!==false)
            {
                $order_no = mb_substr($remark,mb_strpos($remark,'号')+1);
            }
            if(strpos($merchant_order_no,'COM==')!==false)//橙诺达
            {
                $logistics_no = substr($merchant_order_no,strrpos($merchant_order_no,'==')+2);
                $order_no = $db->query_result("SELECT tid FROM api_logistics_sync WHERE logistics_no='{$logistics_no}'");
                if(!$order_no || empty($order_no))
                {
                    $order_no = '对应物流单号:'.$logistics_no;
                }else{
                    $order_no = $order_no['tid'];
                }
            }
            $bill_detail_list[] = array(
                'platform_id'   =>1,
                'shop_id'   =>$shop_id,
                'financial_no'  => trim_all_space($financial_no),
                'business_no'   => trim_all_space($business_no),
                'merchant_order_no' => trim_all_space($merchant_order_no),
                'order_no'  => trim_all_space($order_no),
                'item'   => trim_all_space($item),
                'sub_item'   =>1,//暂时写死
                'in_amount' => $in_amount,
                'out_amount'    => $out_amount,
                'balance'   => $balance,
                'opt_pay_account'   => $pay_account,
                'post_status'   =>2,//2.0中1的状态是还未生成账单，这里直接用2
                'voucher_id'    =>0,
                'remark'    => $remark,
                'create_time'   => $order_time,
                'goods_name'    => $goods_name,
            );
            //logx('账单详情:'.print_r($v,true),$sid.'/Accounting');

        }


        if(putDataToTable($db, 'alipay_account_bill_detail', $bill_detail_list, '') === false)
        {
            logx("alipay_bill_detail write_db_fail:", $sid.'/Accounting');
            logx(print_r($bill_detail_list, true), $sid.'/Accounting');
            releaseDb($db);
            return TASK_OK;
        }
        if(putDataToTable($db, 'alipay_account_bill', $bill_list, '') === false)
        {
            logx("alipay_bill write_db_fail:", $sid.'/Accounting');
            logx(print_r($bill_list, true), $sid.'/Accounting');
            releaseDb($db);
            return TASK_OK;
        }

        logx('保存数据成功'.print_r($bill_dir,true),$sid.'/Accounting');

        setSysCfg($db,'accounting_data_sync_time'.$shop_id,$save_date);
        //保存成功以后删除文件
        self::deldir($bill_dir);

        //SP_FA_API_ACCOUNT_POST
        //收款单生成方式默认为支付单生成的时候生成,2.0的存储过程是用来生成账单数据和收款单,这里是对账单状态修改并生成收款单数据
        $auto_payment = getSysCfg($db,'fa_sales_wait_payment',0);
        $auto_payment2 = getSysCfg($db,'fa_sales_auto_payment',1);
        $account_sql = "SELECT rec_id,order_no,in_amount,platform_id,shop_id,create_time FROM
		                alipay_account_bill_detail WHERE post_status = 2 AND order_no<>'' AND in_amount>0";
        if($auto_payment==0) //第一次修改
        {
            $db->execute("REPLACE INTO `cfg_setting` (`key`, `value`, `class`, `name`, `log_type`, `value_type`, `value_desc`, `modify_mode`, `modified`) VALUES
			('fa_sales_wait_payment',$auto_payment2,'','真正的决定怎样形成订单付款单',5,2,'',0,NOW())");
            $auto_payment = $auto_payment2;
        }
        while(true)
        {
            //更新之前账单post_status 为3
            $count = $db->query_result("SELECT rec_id FROM alipay_account_bill_detail WHERE post_status=0 OR post_status=2 LIMIT 1");
            if(empty($count))
            {
                logx('账单状态更新完成',$sid.'/Accounting');
                break;
            }
            $db->execute('begin');
            //每次获取500条账单将post_status改为2 待处理  2.0中1的状态是还未生成账单，这里直接用2
            $db->execute("UPDATE alipay_account_bill_detail SET post_status=2 WHERE post_status=0 LIMIT 500");
            if($auto_payment==1)//订单完成系统自动生成收款单   现在无收款单逻辑，默认为1
            {
                $db->execute("UPDATE alipay_account_bill_detail SET post_status=3 WHERE post_status=2;");
            }else{
                $account_res = $db->query($account_sql);
                while($row = $db->fetch_array($account_res))
                {
                    $rec_id = $row['rec_id'];
                    $order_no = $row['order_no'];
                    $in_amount = $row['in_amount'];
                    $platform_id = $row['platform_id'];
                    $create_time = $row['create_time'];
                    $db->execute("INSERT INTO fa_payment_bill(payment_no,payment_status,payment_type,obj_type,obj_id,obj_name,amount,last_amount,pre_business_type,pre_business_order,created,platform_id,teller_id,title,account_id,pay_receipt)
					SELECT 	FN_SYS_NO('payment_bill'),0,3,3,customer_id,buyer_nick,$in_amount,$in_amount,1,'{$order_no}','{$create_time}',$platform_id,@cur_uid,'线上订单收款',0,2
					FROM
					(SELECT DISTINCT at.rec_id,st.customer_id,st.buyer_nick
						FROM api_trade `at`
						LEFT JOIN sales_trade_order sto ON sto.src_tid = at.tid AND sto.platform_id = at.platform_id
						LEFT JOIN sales_trade st ON st.trade_id = sto.trade_id
						WHERE at.tid = '{$order_no}' AND at.platform_id = $platform_id ) ss");
                    /*$api_trade_count = $db->query_result("SELECT 1 FROM api_trade WHERE tid = '{$order_no}' AND platform_id = $platform_id");
                    if(!empty($api_trade_count))
                    {
                        $payment_id = $db->insert_id();
                        $db->execute("UPDATE alipay_account_bill_detail SET post_status = 3 WHERE rec_id = $rec_id;");
                        //$db->execute("CALL I_FA_CONTACTS_ORDER_CHECK(V_PaymentID,3);");
                        //--核销
                        $api_trade_sql = "SELECT DISTINCT trade_no,stockout_no
		                                  FROM sales_trade_order sto
		                                  LEFT JOIN sales_trade st ON sto.trade_id = st.trade_id
	                                      WHERE sto.src_tid = '{$order_no}' AND sto.platform_id = $platform_id";
                        $api_trade = $db->query($api_trade_sql);
                        while($res = $db->fetch_array($api_trade))
                        {
                            $trade_no = $res['trade_no'];
                            $stockout_no = $res['stockout_no'];
                            //$db->execute("CALL I_FA_AUTO_VERIFY(V_TradeNO,V_StockoutNO,1,10)"); 自动核销
                        }
                    }*/
                }
                $db->execute("UPDATE alipay_account_bill_detail SET post_status=3 WHERE post_status=2;");
            }
            $db->execute('commit');
        }
        setSysCfg($db,'fa_sales_wait_payment',$auto_payment2);

        //是否开启支付宝对账
        $account_sync = getSysCfg($db,'account_sync',0);
        if($account_sync)
        {
            self::alipayAccountCheck($db,$sid);
        }else
        {
            logx("account_sync not open",$sid.'/Accounting');
        }

        releaseDb($db);
        return TASK_OK;

    }

    public static function deldir($path){
        $dh = opendir($path);
        while(($d = readdir($dh)) !== false){
            if($d == '.' || $d == '..'){//如果为.或..
                continue;
            }
            $tmp = $path.'/'.$d;
            if(!is_dir($tmp)){//如果为文件
                unlink($tmp);
            }else{//如果为目录
                self::deldir($tmp);
            }
        }
        closedir($dh);
        rmdir($path);
    }

    //SP_FA_ALIPAY_ACCOUNT_CHECK
    public static function alipayAccountCheck($db,$sid)
    {
        $account_sql = "SELECT order_no,shop_id,platform_id,item,sub_item
	                        FROM alipay_account_bill_detail WHERE post_status = 3 AND order_no !=''
	                        GROUP BY order_no,shop_id,platform_id,sub_item,item ORDER BY rec_id ASC LIMIT 500";
        $lock_name = $db->query_result("SELECT CONCAT('alipay_account_check_lock_', DATABASE()) AS lock_name");
        $lock_name = $lock_name['lock_name'];
        $is_lock = $db->query_result("SELECT IS_FREE_LOCK('{$lock_name}')");
        if(empty($is_lock))
        {
            logx('alipay_account_check is lock',$sid.'/Accounting');
            return false;
        }
        //加锁
        $get_lock = $db->execute("SELECT GET_LOCK('{$lock_name}',1)");
        if(!$get_lock)
        {
            logx("alipay_account_check get lock fail",$sid.'/Accounting');
            return false;
        }
        $trade_pay_check_by_confirm = getSysCfg($db,'trade_pay_check_by_confirm',0);
        while(true)
        {
            $account_cursor = $db->query($account_sql);
            if($account_cursor->num_rows==0 || !$account_cursor)
            {
                logx('account_cursor break',$sid.'/Accounting');
                break;
            }
            while($row = $db->fetch_array($account_cursor))
            {
                $order_no = $row['order_no'];
                $shop_id = $row['shop_id'];
                $platform_id = $row['platform_id'];
                $item = $row['item'];
                $sub_item = $row['sub_item'];
                $v_status=0;
                $v_new_status=0;
                $v_new_sub_status=0;
                $v_all_send_amount=0;
                $v_all_receive_amount=0;
                $v_all_refund_amount=0;
                $v_new_diff_amount=0;
                $v_new_wait_refund_amount=0;
                $where = " AND  order_no='{$order_no}'  AND platform_id=$platform_id AND shop_id=$shop_id";
                $db->execute('begin');
                if(!$db->execute("UPDATE alipay_account_bill_detail SET post_status=100 WHERE post_status=3 $where"))
                {
                    $db->execute('rollback');
                    logx("update alipay_account_bill_detail post_status=100 fail",$sid.'/Accounting');
                    return false;
                }
                //先处理收款
                $db->execute("UPDATE alipay_account_bill_detail SET post_status=90 WHERE post_status=100 $where AND (item='交易付款' or item='在线支付')");
                $in_amount_sql = "SELECT SUM(in_amount) as in_amount FROM alipay_account_bill_detail WHERE post_status=90 $where";
                $in_amount_res = $db->query_result($in_amount_sql);
                $out_amount_sql = "SELECT SUM(in_amount+out_amount) as out_amount FROM alipay_account_bill_detail WHERE post_status=100 $where";
                $out_amount_res = $db->query_result($out_amount_sql);
                if(!$in_amount_res || empty($in_amount_res))
                {
                    $db->execute('rollback');
                    logx("order_no".print_r($order_no,true).'query SUM fail',$sid.'/Accounting');
                    return false;
                }
                $in_amount = $in_amount_res['in_amount'];
                $out_amount = $out_amount_res['out_amount'];
                if(!$db->execute("UPDATE fa_alipay_account_check fac,api_trade `at` SET fac.trade_amount = `at`.received,fac.check_time = NOW()
			                      WHERE fac.tid=`at`.tid AND fac.platform_id=`at`.platform_id AND fac.tid='{$order_no}' AND fac.platform_id=$platform_id"))
                {
                    $db->execute('rollback');
                    logx('update fa_alipay_account_check 1 fail',$sid.'/Accountint');
                    return false;
                }
                //in_amount 大于0 为收入
                if($in_amount>0)
                {
                    if(($item=='交易付款' or $item='在线支付') && $sub_item==1)//-- 只对买家付款 类型更新 收款数据
                        //-- 对于原始单已完成，系统已取消的也进行对账。为了汇总支付宝数据
                        // -- 没有发货，但是有支付宝数据-- 并且有原始订单,对应订单若有收款时，以未关联显示；
                    {
                        $o_account = $db->query_result("SELECT rec_id FROM fa_alipay_account_check WHERE tid='{$order_no}' AND platform_id=$platform_id");
                        if(empty($o_account))
                        {
                            if(!$db->execute("INSERT INTO fa_alipay_account_check(account_check_no,tid,shop_id,platform_id,created,`status`,check_time)
							                  VALUES(	 FN_SYS_NO('account_check'),'{$order_no}',$shop_id,$platform_id,NOW(),5,NOW())"))
                            {
                                $db->execute('rollback');
                                logx('INSERT  fa_alipay_account_check  fail',$sid.'/Accountint');
                                return false;
                            }
                            if(!$db->execute("UPDATE fa_alipay_account_check fac,api_trade `at` SET fac.trade_amount = `at`.received,fac.check_time = NOW()
							                  WHERE fac.tid=`at`.tid AND fac.platform_id=`at`.platform_id AND fac.tid='{$order_no}' AND fac.platform_id=$platform_id"))
                            {
                                $db->execute('rollback');
                                logx('UPDATE fa_alipay_account_check 2 fail',$sid.'/Accountint');
                                return false;
                            }
                        }
                        $status = $db->query_result("SELECT `status` FROM fa_alipay_account_check WHERE tid='{$order_no}' AND platform_id=$platform_id");
                        $v_status = $status['status'];
                        $account_detail_sql = "SELECT in_amount,create_time FROM alipay_account_bill_detail WHERE
	                              order_no='{$order_no}' AND platform_id=$platform_id AND shop_id=$shop_id  AND post_status=90";
                        $detail_cursor = $db->query($account_detail_sql);
                        if($detail_cursor->num_rows ==0 || !$detail_cursor)
                        {
                            logx('account_detail_cursor not fund order_no:'.print_r($order_no,true),$sid.'/Accounting');
                            continue;
                        }
                        while($detail_row = $db->fetch_array($detail_cursor))
                        {
                            $detail_in_amount = $detail_row['in_amount'];
                            $create_time = $detail_row['create_time'];
                            $sub_status = 0;
                            $after_status = 0;
                            $transfer = 0;
                            $check_month = date('Y-m',strtotime($create_time));
                            $check_detail_month = $db->query_result("SELECT rec_id FROM fa_platform_check_detail_month WHERE
											                                    check_month= '{$check_month}' AND tid='{$order_no}' AND platform_id=$platform_id");
                            if($v_status==5)
                            {
                                if(!empty($check_detail_month))
                                {
                                    if(!$db->execute("UPDATE fa_platform_check_detail_month SET receive_amount=receive_amount+$detail_in_amount,
												diff_amount=diff_amount-$detail_in_amount,`status`=IF(diff_amount=-receive_amount-last_receive_amount,5,IF(diff_amount=0,3,1))
												WHERE  check_month = '{$check_month}' AND tid='{$order_no}' AND platform_id=$platform_id"))
                                    {
                                        $db->execute('rollback');
                                        logx('UPDATE fa_platform_check_detail_month 1 fail',$sid.'/Accountint');
                                        return false;
                                    }
                                }else
                                {
                                    if(!$db->execute("INSERT INTO fa_platform_check_detail_month(tid,platform_id,shop_id,check_month,created,receive_amount,diff_amount,`status`)
											              VALUES('{$order_no}',$platform_id,$shop_id,'{$check_month}',NOW(),$detail_in_amount,-$detail_in_amount,5)"))
                                    {
                                        $db->execute('rollback');
                                        logx('INSERT fa_platform_check_detail_month 1 fail',$sid.'/Accountint');
                                        return false;
                                    }
                                }
                            }else
                            {
                                if(!empty($check_detail_month))
                                {
                                    if(!$db->execute("UPDATE fa_platform_check_detail_month SET receive_amount=receive_amount+$detail_in_amount,
											              diff_amount=diff_amount-$detail_in_amount,`status`=IF(diff_amount=0,3,1)
											              WHERE  check_month='{$check_month}' AND tid='{$order_no}' AND platform_id=$platform_id"))
                                    {
                                        $db->execute('rollback');
                                        logx('UPDATE fa_platform_check_detail_month 2 fail',$sid.'/Accountint');
                                        return false;
                                    }
                                }else
                                {
                                    if(!$db->execute("INSERT INTO fa_platform_check_detail_month(tid,platform_id,shop_id,check_month,created,receive_amount,diff_amount,`status`)
											              VALUES('{$order_no}',$platform_id,$shop_id,'{$check_month}',NOW(),$detail_in_amount,-$detail_in_amount,1)"))
                                    {
                                        $db->execute('rollback');
                                        logx('INSERT fa_platform_check_detail_month 2 fail',$sid.'/Accountint');
                                        return false;
                                    }
                                }
                            }
                            $detail_month_sql = "SELECT `status`,`sub_status`,`diff_amount`,`send_amount`+`last_send_amount` as all_send_amount,`receive_amount`+`last_receive_amount` as all_receive_amount,`refund_amount`+`last_refund_amount` as all_refund_amount,`wait_refund_amount`,`is_transfer`
                                                                          FROM fa_platform_check_detail_month
                                                                          WHERE	check_month='{$check_month}' AND tid='{$order_no}' AND platform_id=$platform_id";
                            $res = $db->query_result("$detail_month_sql");
                            if(!$res)
                            {
                                $db->execute('rollback');
                                logx('query detail_month fail:'.print_r($detail_month_sql,true),$sid.'/Accounting');
                                return false;
                            }
                            $v_new_status = $res['status'];
                            $v_new_sub_status = $res['sub_status'];
                            $v_new_diff_amount = $res['diff_amount'];
                            $v_all_send_amount = $res['all_send_amount'];
                            $v_all_receive_amount = $res['all_receive_amount'];
                            $v_all_refund_amount = $res['all_refund_amount'];
                            $v_new_wait_refund_amount = $res['wait_refund_amount'];
                            $v_transfer = $res['is_transfer'];
                            if($v_new_status==1)//对账失败的
                            {
                                if($v_all_send_amount==$v_all_receive_amount){$v_new_sub_status=5;}//退款阶段错误
                                elseif($v_new_diff_amount==$v_new_wait_refund_amount){$v_new_sub_status=6;}//退换未结成功
                                elseif($v_new_wait_refund_amount<>0){$v_new_sub_status=7;}//退换未结失败
                                else
                                {
                                    if($v_transfer==1)
                                    {
                                        $v_new_sub_status = 8;//结转失败
                                    }else
                                    {
                                        $v_new_sub_status = 9;//对账失败
                                    }
                                }
                            }elseif($v_new_status==5)
                            {
                                $v_new_sub_status = 1; //未关联
                            }else
                            {
                                if($v_transfer==1)
                                {
                                    $v_new_sub_status = 3;//结转成功
                                }else
                                {
                                    $v_new_sub_status = 2; //对账成功
                                }
                            }
                            if(!$db->execute("UPDATE fa_platform_check_detail_month SET `sub_status`=$v_new_sub_status  WHERE check_month='{$check_month}' AND tid='{$order_no}' AND platform_id=$platform_id"))
                            {
                                $db->execute('rollback');
                                logx('UPDATE fa_platform_check_detail_month 3 fail',$sid.'/Accountint');
                                return false;
                            }
                        }
                        //需要确定对账单是否是已发货的，未发货的只更新对账单
                        if($v_status<>5)
                        {
                            if($trade_pay_check_by_confirm>0)//按确认收货时间对账 配置 1开启
                            {
                                $db->execute("UPDATE fa_alipay_account_check SET pay_amount = pay_amount + $in_amount,`status`=IF(pay_amount=confirm_amount,3,2)
							                      WHERE tid='{$order_no}' AND platform_id = $platform_id ");
                            }else
                            {
                                $db->execute("UPDATE fa_alipay_account_check SET pay_amount = pay_amount + $in_amount,`status`=IF(pay_amount=send_amount-refund_amount,3,2)
                                                  WHERE tid='{$order_no}' AND platform_id = $platform_id");
                            }
                        }else
                        {
                            $db->execute("UPDATE fa_alipay_account_check SET pay_amount = pay_amount + $in_amount,check_time=NOW() WHERE tid='{$order_no}' AND platform_id = $platform_id");
                        }
                    }
                }
                if($out_amount>0)//只有费用,不更新状态
                {
                    $db->execute("UPDATE fa_alipay_account_check SET cost_amount = cost_amount + $out_amount WHERE tid='{$order_no}' AND  platform_id = $platform_id");
                }
                $db->execute("UPDATE alipay_account_bill_detail SET post_status = 4 WHERE post_status=100 AND order_no = '{$order_no}' AND platform_id = $platform_id AND shop_id=$shop_id AND item = '{$item}'");
                $db->execute("UPDATE alipay_account_bill_detail SET post_status = 4 WHERE post_status=90 AND order_no = '{$order_no}' AND platform_id = $platform_id AND shop_id=$shop_id AND item = '{$item}'");

                $db->execute('commit');
            }
        }
        $alipay_days_to_fail = getSysCfg($db,'alipay_days_to_fail',0);//支付宝对账状态由部分对账变为失败对账的天数
        $db->execute("UPDATE fa_alipay_account_check SET `status` = 1 WHERE `status`=2 AND TO_DAYS(CURRENT_DATE())-TO_DAYS(check_time) >=$alipay_days_to_fail");

        //解锁
        $db->execute("SELECT RELEASE_LOCK('{$lock_name}')");
        logx('alipay_account_check OK',$sid.'/Accounting');
        releaseDb($db);
        return true;
    }

    //SP_FA_ALIPAY_MONTH_REFUND_CLASSIFY
    //退换单关联对账失败单据，进行分类
    //暂不使用
    public function alipay_month_refund_classify()
    {


    }

}


