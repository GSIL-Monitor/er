<?php
namespace Platform\Manager;

require_once(TOP_SDK_DIR.'/kdn/kdnClient.php');
require_once(ROOT_DIR . "/Manager/Manager.class.php");
require_once(ROOT_DIR . "/Manager/utils.php");


class LogisticsTraceManager extends Manager
{
    public static function register()
    {
        registerHandle("logistics_trace_merchant", array("\\Platform\\Manager\\LogisticsTraceManager", "logistics_trace_sync"));
    }

    public static function LogisticsTrace_main()
    {
        enumAllMerchant("logistics_trace_merchant");
    }

    function logistics_trace_sync($sid)
    {
        deleteJob();
        $kdn_logistics_codes = array(
            '2'		=>	'YZPY',
            '3'		=>	'EMS',
            '4'		=>	'YTO',
            '5'		=>	'ZTO',
            '6'		=>	'STO',
            '7'		=>	'QFKD',
            '8'		=>	'SF',
            '9'		=>	'YD',
            '10'	=>	'HTKY',
            '11'	=>	'ZTKY',
            '13'	=>	'LB',
            '14'	=>	'FAST',
            '15'	=>	'QRT',
            '16'	=>	'HHTT',
            '18'	=>	'LHT',
            '19'	=>	'ZJS',
            '20'	=>	'HTKY',
            '21'	=>	'FEDEX',
            '22'	=>	'DBL',
            '23'	=>	'hq568',
            '25'	=>	'YFSD',
            '27'	=>	'DTWL',
            '30'	=>	'CCES',
            '34'	=>	'XBWL',
            '35'	=>	'UC',
            '36'	=>	'NEDA',
            '37'	=>	'UAPEX',
            '45'	=>	'EMS',
            '49'	=>	'CITY100',
            '50'	=>	'ZHQKD',
            '51'	=>	'YZPY',
            '52'	=>	'GTO',
            '87'    =>  'ANE',
            '1311'	=>	'JD',
            '2062' => 'DBL');

        $db = getUserDb($sid);
        if(!$db)
        {
            logx("logistics_trace_sync getUserDb failed!!", $sid.'/LogisticsTrace','error');
            return TASK_OK;
        }
        //  添加该库配置   INSERT INTO g_cfg_sys(CfgKey,CfgValue) VALUES('logistic_trace_sync_be_on', 1);
        $open_logistics_trace = getSysCfg($db,'cfg_open_logistics_trace',0);
        if(!$open_logistics_trace)
        {
            releaseDb($db);
            return TASK_OK;
        }
        $order_info = $db->query("SELECT logistics_no,logistics_type FROM sales_logistics_trace WHERE logistics_status = 0 LIMIT 500;");
        if(!$order_info || $order_info->num_rows == 0)
        {
            releaseDb($db);
            return TASK_OK;
        }

        $logistics_sync		=array();
        $logistics_no		=array();
        $logistics_unsync	= array();//不递交物流单号
        $count = 0;
        $kdn = new \kdnClient();
        while($row = $db->fetch_array($order_info))
        {
            if(isset($kdn_logistics_codes["{$row['logistics_type']}"]))
            {
                $count++;
                $logistics_sync["{$row['logistics_type']}"]['Code'] = $kdn_logistics_codes["{$row['logistics_type']}"];
                $logistics_sync["{$row['logistics_type']}"]['Item'][] = array('No'=>$row['logistics_no'],'Bk'=>base64_encode(rc4('hhysbyj!Q@W', $sid)));
                $logistics_no[] = $row['logistics_no'];
            }
            else//如果找不到对应的code则不推送，标记为推送失败
            {
                $logistics_unsync[]=$row['logistics_no'];
            }
            if($count>=100)
            {
                logx(print_r($logistics_sync,true),$sid.'/LogisticsTrace');
                $sync_info = array_values($logistics_sync);
                $result = $kdn->syncLogisticNo($sync_info);
                if($result['Success'] == 1)
                {
                    $db->query("UPDATE sales_logistics_trace SET logistics_status=1 WHERE logistics_no IN ('".implode("','",$logistics_no)."') AND logistics_status = 0");
                    logx('上传物流单号成功',$sid.'/LogisticsTrace');
                }
                else
                {
                    $db->query("UPDATE sales_logistics_trace SET logistics_status=99,remark='{$result['Reason']}' WHERE logistics_no IN ('".implode("','",$logistics_no)."') AND logistics_status = 0");
                    logx('上传单号失败:'.$result['Reason'].implode(',',$logistics_no),$sid.'/LogisticsTrace');
                }
                $logistics_no	=array();
                $logistics_sync	=array();
            }
        }
        if(count($logistics_sync) > 0)//处理可以递交的单号
        {
            logx(print_r($logistics_sync,true),$sid.'/LogisticsTrace');
            $sync_info = array_values($logistics_sync);
            $result = $kdn->syncLogisticNo($sync_info);
            if($result['Success'] == 1)
            {
                $db->query("UPDATE sales_logistics_trace SET logistics_status=1 WHERE logistics_no IN ('".implode("','",$logistics_no)."') AND logistics_status = 0");
                logx('上传物流单号成功',$sid.'/LogisticsTrace');
            }
            else
            {
                $db->query("UPDATE sales_logistics_trace SET logistics_status=99,remark='{$result['Reason']}' WHERE logistics_no IN ('".implode("','",$logistics_no)."') AND logistics_status = 0");
                logx('上传单号失败:'.$result['Reason'].implode(',',$logistics_no),$sid.'/LogisticsTrace');
            }
        }
        if(count($logistics_unsync) > 0)//处理不能递交的单号
        {
            $db->query("UPDATE sales_logistics_trace SET logistics_status=99,remark='暂不支持该物流的追踪查询' WHERE logistics_no IN ('".implode("','",$logistics_unsync)."') AND logistics_status = 0");
            logx('物流单对应物流公司暂不支持递交:'.implode(',',$logistics_unsync),$sid.'/LogisticsTrace');
        }

        releaseDb($db);
        return TASK_OK;
    }
}
