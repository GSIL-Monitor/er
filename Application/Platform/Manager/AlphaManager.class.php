<?php
namespace Platform\Manager;
use Think\Exception;
use Platform\Wms\WmsAdapter;
require_once(ROOT_DIR . '/Manager/utils.php');
require_once(ROOT_DIR . '/Manager/Manager.class.php');
require_once(ROOT_DIR . '/Wms/WmsAdapter.php');
require_once(ROOT_DIR . '/Wms/adapter_utils.php');
require_once(ROOT_DIR . '/Wms/adapter_cmd_auto_config.php');

class AlphaManager extends Manager{
	
	public static  function  register(){
		registerHandle('task_alpha', 'scan_all_task');
		registerHandle('task_wms_get_trade', 'scan_wms_query_trade');
		registerHandle('task_wms_get_purchase', 'scan_wms_query_purchase');
		registerHandle('task_wms_get_purchase_return', 'scan_wms_query_purchase_return');
		registerHandle('task_wms_get_vmi_stockchange', 'scan_wms_query_vmi_stockchange');
		registerHandle('task_wms_get_sales_refund', 'scan_wms_query_sales_refund');

		registerHandle('task_wms_query_trade', 'wms_query_trade');//查询订单
		registerHandle('task_wms_query_purchase', 'wms_query_purchase');//查询采购单
		registerHandle('task_wms_query_purchase_return', 'wms_query_purchase_return');//查询采购退货单
		registerHandle('task_wms_query_vmi_stockchange', 'wms_query_vmi_stockchange');//查询京东VMI库存流水
		registerHandle('task_wms_query_sales_refund', 'wms_query_sales_refund');//查询销售退货入库

	}
	public function  Alpha_main()
{
    return enumAllMerchant('task_alpha');
}

//下载仓库信息
public function  wms_adapter_get_warehouses($sid, $uid, $shop_id, $type, $seller_no = '')
{
    //连接卖家数据库
    $db = getUserDb($sid);
    if(!$db)
    {
        logx("seller no:$seller_no, GetUserDb failed! wms_adapter_get_warehouses",$sid);
        ackError('下载仓库信息失败:服务器内部错误');

        return false;
    }
    $wms_type = $type;
    $wms_info = array();
    $wms_info['shop_id'] = $shop_id;

    //目前只支持京东沧海
    if ($wms_type != 15)
    {
        logx("seller no:$seller_no, Wms type[$wms_type] not support! wms_adapter_get_warehouses",$sid);
        releaseDb($db);
        ackError("下载仓库信息失败:该仓库类型暂不支持下载仓库");

        return false;
    }

    if ($wms_type == 15)
    {
        $shop_api = getShopAuth($sid, $db, $wms_info['shop_id']);
        if(!$shop_api)
        {
            logx("seller no:$seller_no, shop[{$wms_info['shop_id']}] not auth! wms_adapter_get_warehouses",$sid);
            releaseDb($db);
            ackError("下载仓库信息失败:店铺授权信息有误,请核对店铺授权信息");

            return false;
        }
        $wms_info['appKey']      = $shop_api->key;
        $wms_info['appSecret']   = $shop_api->secret;
        $wms_info['accessToken'] = $shop_api->session;
    }

    $data = array();
    $api_key = array( 'shop_id'=>$shop_id,'deptNo'=>$seller_no);
    $data['deptNo'] = $seller_no;
    $wms_adapter   = new WmsAdapter($wms_type, $wms_info);
    //推送
    logx("start to send request,sid:".$sid);
    $result = $wms_adapter->sendRequest(WMS_METHOD_WAREHOUSE_GET, $data, $sid);
    $send   = $wms_adapter->getSendParams();
    $resv   = $wms_adapter->getReceived();
    logx("send:    ".print_r($send,true),$sid);
    logx("receive: ".print_r($resv,true),$sid);
    logx("result:  ".print_r($result,true),$sid);
    $code = $result['code'];
    $error_msg = $result['error_msg'];
    if(mb_strlen($error_msg,'utf-8') > 200)//如果超过长度则截取
    {
        $error_msg = mb_substr($error_msg,0,200,"utf-8");
    }
    $error_msg = $db->escape_string($error_msg);
    $rev_info = isset($result['rev_info'])?$result['rev_info']:'';

    //失败就弹窗
    if(!$code == 0)
    {
        logx("seller no:$seller_no, Get warehouse failure:$error_msg! wms_adapter_get_warehouses",$sid);
        ackError("WMS返回下载失败:$error_msg");
        releaseDb($db);

        return false;
    }
    else
    {
        if (!isset($rev_info['warehouse']) || empty($rev_info['warehouse']))
        {
            logx("seller no:$seller_no, No return warehouse info! wms_adapter_get_warehouses",$sid);
            releaseDb($db);
            ackError("WMS返回下载失败:WMS未返回仓库信息,请联系WMS相关人员处理");

            return false;
        }
        foreach ($rev_info['warehouse'] as $key => $resv)
        {
            //停用的仓库不予保存到数据库中
            if($resv['status'] == 3)
            {
                continue;
            }

            $warehouse_no = isset($resv['warehouseNo'])?$resv['warehouseNo']:'';
            if ($warehouse_no == '')
            {
                logx("seller no:$seller_no, No return warehouse no! wms_adapter_get_warehouses",$sid);
                releaseDb($db);
                ackError("WMS返回下载失败:WMS未返回仓库编号,请联系WMS相关人员处理");

                return false;
            }

            $warehouse_name = isset($resv['warehouseName'])?$resv['warehouseName']:'';
            if ($warehouse_name == '')
            {
                logx("seller no:$seller_no, No return warehouse name! wms_adapter_get_warehouses",$sid);
                releaseDb($db);
                ackError("WMS返回下载失败:WMS未返回仓库名称,请联系WMS相关人员处理");

                return false;
            }

            $api_key['ext_warehouse_no'] = $warehouse_no;
            $warehouse_list[] = array
            (
               // 'warehouse_no'     => array("FN_SYS_NO('warehouse')"),
                'api_object_id'    => "JD2",
                'ext_warehouse_no' => $warehouse_no,/*对应的wms的仓库编号*/
                'name'             => $warehouse_name,
                'contact'          => isset($resv['contacts'])?$resv['contacts']:'',
                'telno'            => isset($resv['phone'])?$resv['phone']:'',
                'province'         => isset($resv['province'])?$resv['province']:'',
                'city'             => isset($resv['city'])?$resv['city']:'',
                'address'          => isset($resv['address'])?$resv['address']:'',
                'type'             => 15,
                'api_key'          => json_encode($api_key),
                'created'          => array('NOW()'),
            );

        }
        $success = true;
        //将仓库信息插入到数据库中
        if( !$db->execute('BEGIN') ||
            !putDataToTable($db, 'cfg_warehouse', $warehouse_list, 'on duplicate key update address=values(address), telno=values(telno), api_key=values(api_key), modified=now()') ||
            !$db->execute("INSERT INTO cfg_warehouse_zone(type,warehouse_id,zone_no,name,is_disabled,created) SELECT 0,warehouse_id,'ZC','暂存',0,NOW() FROM cfg_warehouse wl WHERE wl.warehouse_id NOT IN (SELECT warehouse_id FROM cfg_warehouse_zone)") ||
            !$db->execute("INSERT IGNORE INTO cfg_warehouse_position(rec_id,warehouse_id,zone_id,position_no,is_disabled,created) SELECT -warehouse_id,warehouse_id,zone_id,'ZANCUN',0,NOW() FROM cfg_warehouse_zone wz WHERE wz.zone_id NOT IN (SELECT zone_id FROM cfg_warehouse_position) and wz.type = 0 ") ||
            !$db->execute('COMMIT'))
        {
            $db->execute('ROLLBACK');
            $error_msg = $db->error_msg();
            logx("seller no:$seller_no, insert into database failed:$error_msg! wms_adapter_get_warehouses",$sid);
            $success = false;
            ackError("下载仓库信息失败:服务器内部错误,请稍后重试");
        }

    }
    releaseDb($db);
    if($success)
    {
        logx( "seller no:$seller_no, Get warehouse success! wms_adapter_get_warehouses", $sid );
        ackOk(0);
    }
}

public function  scan_all_task($sid)
{
    global $g_sid_list;
    deleteJob();

    $db = getUserDb($sid);
    if (!$db)
    {
        logx("scan_all_task, getUserDb failed!", $sid);
        return TASK_OK;
    }

    //查询订单
    pushTask('task_wms_get_trade',$sid);
    resetAlarm();
     
    //查询采购单
    pushTask('task_wms_get_purchase',$sid);
    resetAlarm();

    //查询采购退货单
    pushTask('task_wms_get_purchase_return',$sid);
    resetAlarm();

    //查询京东VMI库存流水
    /* $vmi_stockchange_flag = isset($g_sid_list[$sid][WMS_METHOD_VMI_STOCKCHANGE_QUERY])?1:0;
    if ($vmi_stockchange_flag)
    {
        $vmi_shop = $g_sid_list[$sid][WMS_METHOD_VMI_STOCKCHANGE_QUERY];
        pushTask('task_wms_get_vmi_stockchange', array('sid' => $sid,'vmi_shop' => $vmi_shop));
        resetAlarm();
    }
 */
    //查询销售退货单
    pushTask('task_wms_get_sales_refund',$sid);
    resetAlarm();

    releaseDb($db);
    return TASK_OK;
}

public function  scan_wms_query_trade($sid)
{
    deleteJob();
    $db = getUserDb($sid);
    if (!$db)
    {
        logx("scan_wms_query_trade, getUserDb failed!", $sid);
        return TASK_OK;
    }

    //取出已审核的出库单,优先取出尚未查询过的出库单
    $trade_list = $db->query("    SELECT so.stockout_id ".
                             "      FROM stockout_order so ".
                             " LEFT JOIN cfg_warehouse sw USING(warehouse_id) ".
                             "     WHERE so.src_order_type = 1 AND so.status = 55 AND sw.type = 15 ORDER BY so.modified LIMIT 500 ");
    if (!$trade_list)
    {
        $error_msg = $db->error_msg();
        logx("scan_wms_query_trade, get trade list failed:$error_msg!",$sid);
        releaseDb($db);
        return TASK_OK;
    }

    if ($trade_list->num_rows == 0)
    {
        releaseDb($db);
        return TASK_OK;
    }

    while($trade = $db->fetch_array($trade_list))
    {
        $query_info = array('stockout_id' => $trade['stockout_id'],'sid' => $sid);
        pushTask('task_wms_query_trade',$query_info);
        resetAlarm();
    }

    $db->free_result($trade_list);
    releaseDb($db);
    return TASK_OK;
}

public function  scan_wms_query_purchase($sid)
{
    deleteJob();
    $db = getUserDb($sid);
    if(!$db)
    {
        logx("scan_wms_query_purchase, getUserDb failed!", $sid);
        return TASK_OK;
    }
   
    //取出已审核的采购单,优先取出尚未查询过的采购单
    $purchase_list = $db->query(" SELECT po.purchase_id ".
                             "      FROM purchase_order po ".
                             " LEFT JOIN cfg_warehouse sw USING(warehouse_id) ".
                             "     WHERE po.status = 48 AND sw.type = 15 ORDER BY po.modified LIMIT 500 ");

    if(!$purchase_list)
    {
        $error_msg = $db->error_msg();
        logx("scan_wms_query_purchase, get purchase list failed:$error_msg!",$sid);
        releaseDb($db);
        return TASK_OK;
    }

    if ($purchase_list->num_rows == 0)
    {
        releaseDb($db);
        return TASK_OK;
    }

    while($purchase = $db->fetch_array($purchase_list))
    {
        $query_info = array('purchase_id' => $purchase['purchase_id'],'sid' => $sid);
        pushTask('task_wms_query_purchase',$query_info);
        resetAlarm();
    }

    $db->free_result($purchase_list);
    releaseDb($db);
    return TASK_OK;
}

public function  scan_wms_query_purchase_return($sid)
{
    deleteJob();
    $db = getUserDb($sid);
    if (!$db)
    {
        logx("scan_wms_query_purchase_return, getUserDb failed!", $sid);
        return TASK_OK;
    }

    //取出已审核的采购退货单,优先取出尚未查询过的采购退货单
    $trade_list = $db->query("    SELECT pr.return_id ".
                             "      FROM purchase_return pr ".
                             " LEFT JOIN cfg_warehouse sw USING(warehouse_id) ".
                             "     WHERE pr.status = 46 AND sw.type = 15 ORDER BY pr.modified LIMIT 500 ");
    if (!$trade_list)
    {
        $error_msg = $db->error_msg();
        logx("scan_wms_query_purchase_return, get purchase_return list failed:$error_msg!",$sid);
        releaseDb($db);
        return TASK_OK;
    }

    if ($trade_list->num_rows == 0)
    {
        releaseDb($db);
        return TASK_OK;
    }

    while($trade = $db->fetch_array($trade_list))
    {
        $query_info = array('return_id' => $trade['return_id'],'sid' => $sid);
        pushTask('task_wms_query_purchase_return',$query_info);
        resetAlarm();
    }

    $db->free_result($trade_list);
    releaseDb($db);
    return TASK_OK;
}

public function  scan_wms_query_vmi_stockchange($task)
{
    deleteJob();
    $sid      = $task->sid;
    $vmi_shop = $task->vmi_shop;

    if(!is_object($vmi_shop))
    {
        logx("scan_wms_query_vmi_stockchange, vmi shop info error:".print_r($vmi_shop), $sid);
        return TASK_OK;
    }

    foreach ($vmi_shop as $shop_no => $warehouse_list)
    {
        foreach ($warehouse_list as $warehouse_no)
        {
            pushTask( 'task_wms_query_vmi_stockchange', array( 'sid' => $sid, 'shop_no' => $shop_no, 'warehouse_no' => $warehouse_no ) );
            resetAlarm();
        }
    }

    return TASK_OK;
}

//读取未查询的销售退货单
public function  scan_wms_query_sales_refund($sid)
{
    deleteJob();
    $db = getUserDb($sid);
    if(!$db)
    {
        logx("scan_wms_query_sales_refund, getUserDb failed!", $sid);
        return TASK_OK;
    }

    //取出已审核的销售退货单,优先取出尚未查询过的销售退货单
    $sales_refund_list = $db->query(" SELECT sr.refund_id ".
                                 "      FROM sales_refund sr ".
                                 " LEFT JOIN cfg_warehouse sw USING(warehouse_id) ".
                                 "     WHERE sr.process_status = 65 AND sw.type = 15 ORDER BY sr.modified LIMIT 500 ");

    if(!$sales_refund_list)
    {
        $error_msg = $db->error_msg();
        logx("scan_wms_query_sales_refund, get sales refund list failed:$error_msg!",$sid);
        releaseDb($db);
        return TASK_OK;
    }

    if($sales_refund_list->num_rows == 0)
    {
        releaseDb($db);
        return TASK_OK;
    }

    while($sales_refund = $db->fetch_array($sales_refund_list))
    {
        $sales_refund_info = array('refund_id' =>$sales_refund['refund_id'] ,'sid' => $sid);
        pushTask('task_wms_query_sales_refund',$sales_refund_info);
        resetAlarm();
    }

    $db->free_result($sales_refund_list);
    releaseDb($db);
    return TASK_OK;
}
//查询订单
public function  wms_query_trade($task)
{
    deleteJob();
    $sid         = $task->sid;
    $stockout_id = $task->stockout_id;

    $db = getUserDb($sid);
    if(!$db)
    {
        logx("stockout id:$stockout_id, GetUserDb failed! wms_query_trade",$sid);

        return TASK_OK;
    }

    $data = array();

    $trade = $db->query_result("     SELECT so.stockout_id, so.stockout_no, so.outer_no, so.status, so.wms_status, so.logistics_id, so.src_order_id ,so.error_info ,sw.warehouse_id, sw.type as wms_type, sw.api_key".
                               "       FROM stockout_order so ".
                               "  LEFT JOIN cfg_warehouse sw using(warehouse_id) ".
                               "      WHERE so.stockout_id = $stockout_id ");
    if(!$trade)
    {
        logx("stockout id:$stockout_id, Get trade info failed! wms_query_trade",$sid);
        $db->execute(" UPDATE stockout_order SET error_info = '自动查询订单失败：获取单据信息失败' where stockout_id = $stockout_id ");
        releaseDb($db);

        return TASK_OK;
    }

    $stockout_no = $trade['stockout_no'];
    
    if($trade['status'] != 55 )
    {
        logx("stockout no:$stockout_no, Stockout order status[{$trade['status']}] error! wms_query_trade",$sid);
        $db->execute(" UPDATE stockout_order SET error_info = '自动查询订单失败：销售出库单状态非已审核' where stockout_id = $stockout_id ");
        releaseDb($db);

        return TASK_OK;
    }

    $wms_info = json_decode($trade['api_key'],true);
    $wms_type = $trade['wms_type'];

    //目前只支持京东沧海
    if($wms_type <> 15)
    {
        logx("stockout no:$stockout_no, Warehouse type[$wms_type] error! wms_query_trade",$sid);
        $db->execute(" UPDATE stockout_order SET error_info = '自动查询订单失败：该仓库类型暂不支持' where stockout_id = $stockout_id ");
        releaseDb($db);

        return TASK_OK;
    }
    //京东沧海需要到店铺中获取授权信息
    if($wms_type == 15)
    {
        $shop_api = getShopAuth($sid, $db, $wms_info['shop_id']);
        if(!$shop_api)
        {
            logx("stockout no:$stockout_no, Shop[{$wms_info['shop_id']}] not auth! wms_query_trade",$sid);
            $db->execute(" UPDATE stockout_order SET error_info= '自动查询订单失败：获取店铺授权信息失败' where stockout_id = $stockout_id");
            releaseDb($db);

            return TASK_OK;
        }
        $wms_info['appKey']      = $shop_api->key;
        $wms_info['appSecret']   = $shop_api->secret;
        $wms_info['accessToken'] = $shop_api->session;
    }
    $data['trade'] = $trade;
    $wms_adapter = new WmsAdapter($wms_type,$wms_info);
    logx("start to send requset, stockout_no:$stockout_no  stockout_id:$stockout_id",$sid);
    $result = $wms_adapter->sendRequest(WMS_METHOD_TRADE_QUERY,$data,$sid);
    $send   = $wms_adapter->getSendParams();
    $resv   = $wms_adapter->getReceived();

    logx("stockout no:$stockout_no".' send:    '.print_r($send,true),$sid);
    logx("stockout no:$stockout_no".' receive: '.print_r($resv,true),$sid);

    $code = $result['code'];
    $error_msg = 'WMS返回查询失败：'.$result['error_msg'];
    $rev_info = isset($result['rev_info'])?$result['rev_info']:'';

    if(mb_strlen($error_msg,'utf-8') > 254)//如果超过长度则截取
    {
        $error_msg = mb_substr($error_msg,0,200,"utf-8");
    }
    $error_msg = $db->escape_string($error_msg);

    if($code != 0)
    {
        logx("stockout no:$stockout_no, Error:$error_msg! wms_query_trade ", $sid);
        $db->execute(" UPDATE stockout_order SET error_info= '$error_msg' where stockout_id = $stockout_id");
        releaseDb($db);

        return TASK_OK;
    }
    else
    {
        if(empty($rev_info))
        {
            logx("stockout no:$stockout_no, No return order info! wms_query_trade ", $sid);
            $db->execute(" UPDATE stockout_order SET error_info= 'WMS未返回单据信息' where stockout_id = $stockout_id");
            releaseDb($db);

            return TASK_OK;
        }

        $trade_info = $rev_info['order'];
        //单据状态
        if($trade_info['status'] != STATUS_FINISH)
        {
            $flag = time();
            switch($trade_info['status'])
            {
                case STATUS_CANCELED://取消成功
                    if($trade['wms_status'] == 4)
                    {
                        if(!$db->query("update stockout_order set wms_status=3 where src_order_type=1 and stockout_id={$stockout_id}"))
                        {
                            $error_info = $db->error_msg();
                            logx("stockout_id: {$stockout_id}, when update db failed in wms_query_trade, error_info : ".$error_info, $sid);
                        }
                        //更新订单信息
                        order_refresh_handle($db, $stockout_id, $sid, $trade['src_order_id'], 1);
                    }
                    else
                    {
                        logx("stockout no:$stockout_no, Order unfinished,current status[{$trade_info['status_name']}]! wms_query_trade ", $sid);
                        $db->execute(" UPDATE stockout_order SET error_info= '自动查询订单:{$trade_info['status_name']}$flag' where stockout_id = $stockout_id");
                    }
                    releaseDb($db);

                    return TASK_OK;
                case STATUS_CANCELFAIL://取消失败
                    if($trade['wms_status'] == 4)
                    {
                        $db->execute("insert into sales_trade_log(trade_id,operator_id,type,message) values({$trade['src_order_id']},0,300,'系统自动取消外部WMS订单失败')");
                        if(!$db->query("update stockout_order set wms_status=0  where src_order_type=1 and stockout_id={$stockout_id}"))
                        {
                            $error_info = $db->error_msg();
                            logx("stockout_id: {$stockout_id}, when update db failed in wms_query_trade, error_info : ".$error_info, $sid);
                        }
                        else
                        {
                            logx("stockout_id: {$stockout_id}, failed in wms_query_trade", $sid);
                        }
                    }
                    else
                    {
                        logx("stockout no:$stockout_no, Order unfinished,current status[{$trade_info['status_name']}]! wms_query_trade ", $sid);
                        $db->execute(" UPDATE stockout_order SET error_info= '自动查询订单:{$trade_info['status_name']}$flag' where stockout_id = $stockout_id");
                    }
                    releaseDb($db);

                    return TASK_OK;
                default:
                    logx("stockout no:$stockout_no, Order unfinished,current status[{$trade_info['status_name']}]! wms_query_trade ", $sid);
                    $db->execute(" UPDATE stockout_order SET error_info= '自动查询订单:{$trade_info['status_name']}$flag' where stockout_id = $stockout_id");
                    releaseDb($db);

                    return TASK_OK;
            }

        }

        //单号校验
        if($trade_info['outer_no'] == '')
        {
            logx("stockout no:$stockout_no, No return outer no! wms_query_trade ", $sid);
            $db->execute(" UPDATE stockout_order SET error_info= '自动查询订单失败：WMS未返回外部单号' where stockout_id = $stockout_id");
            releaseDb($db);

            return TASK_OK;
        }
        if($trade_info['outer_no'] != $stockout_no)
        {
            logx("stockout no:$stockout_no, wms_outer_no[{$trade_info['outer_no']}] is different from erp_outer_no[$stockout_no]! wms_query_trade ", $sid);
            $db->execute(" UPDATE stockout_order SET error_info= '自动查询订单失败：WMS返回外部单号与旺店通中不一致' where stockout_id = $stockout_id");
            releaseDb($db);

            return TASK_OK;
        }

        //承运商编号校验
        if($trade_info['logistics_code'] == '')
        {
            logx("stockout no:$stockout_no, No return logistics code! wms_query_trade ", $sid);
            $db->execute(" UPDATE stockout_order SET error_info= '自动查询订单失败：WMS未返回承运商编号' where stockout_id = $stockout_id");
            releaseDb($db);

            return TASK_OK;
        }

        $order[] = array(
            'order_no'          => $trade_info['outer_no'],
            'order_type'        => ORDER_TYPE_TRADE,
            'status'            => STATUS_FINISH,
            'logistics_code'    => $trade_info['logistics_code'],
            'logistics_no'      => $trade_info['logistics_no'],
            'logistics_list'    => $trade_info['logistics_list'],
            'weight'            => $trade_info['weight'],
        );

        $logistics_list = array();
        if(array_key_exists('logistics',$rev_info))
        {
            $logistics_list = $rev_info['logistics'];
        }

        if(!array_key_exists('details',$rev_info))
        {
            logx("stockout no:$stockout_no, No return details! wms_query_trade ", $sid);
            $db->execute(" UPDATE stockout_order SET error_info= '自动查询订单失败：WMS未返回商品明细' where stockout_id = $stockout_id");
            releaseDb($db);

            return TASK_OK;
        }
        $detail_list = $rev_info['details'];

        if($wms_type == 15)
        {
            //京东沧海回传的是仓库编码，需要先转换成商家编码
            $details = $db->query("    SELECT gs.spec_no,ss.spec_wh_no2 ".
                                  "      FROM stockout_order_detail sod ".
                                  " LEFT JOIN goods_spec gs ON sod.spec_id = gs.spec_id ".
                                  " LEFT JOIN stock_spec ss ON sod.spec_id = ss.spec_id AND ss.warehouse_id = {$trade['warehouse_id']} ".
                                  "     WHERE sod.stockout_id = {$trade['stockout_id']} AND ss.spec_wh_no2 <> '' ");
            if(!$details)
            {
                $error_msg = $db->error_msg();
                logx( "stockout no:$stockout_no, Get details failed:$error_msg! wms_query_trade ", $sid );
                $db->execute( " UPDATE stockout_order SET error_info= '自动查询订单失败：服务器内部错误' where stockout_id = $stockout_id" );
                releaseDb($db);

                return TASK_OK;
            }

            if($details->num_rows == 0)
            {             
                logx( "stockout no:$stockout_no, Get 0 details  wms_query_trade ", $sid );
                $db->execute( " UPDATE stockout_order SET error_info= '自动查询订单失败：获取单据明细为空' where stockout_id = $stockout_id" );
                releaseDb($db);

                return TASK_OK;
            }

            while($row = $db->fetch_array($details))
            {
                $spec_transform[$row['spec_wh_no2']] = $row['spec_no'];
            }
            foreach($detail_list as &$detail)
            {
                if(!array_key_exists($detail['spec_no'], $spec_transform))
                {
                    logx( "stockout no:$stockout_no, unknown spec[{$detail['spec_no']}]! wms_query_trade ", $sid );
                    $db->execute( " UPDATE stockout_order SET error_info= '自动查询订单失败：WMS返回不明商品[{$detail['spec_no']}]' where stockout_id = $stockout_id" );
                    releaseDb($db);

                    return TASK_OK;
                }
                $detail['spec_no'] = $spec_transform[$detail['spec_no']];
            }
        }

        $error = array();
        wms_update_order_status($db,$order,$detail_list,$error,$logistics_list);
        if($error['code'] != 0)
        {
            $error_msg = $error['msg'];
            logx("stockout no:$stockout_no, Call SP_WMS_ORDER_HANDLE faild:$error_msg! wms_query_trade ", $sid);
            $db->execute(" UPDATE stockout_order SET error_info= '自动查询订单失败:$error_msg' where stockout_id = $stockout_id");
            releaseDb($db);

            return TASK_OK;
        }
        else
        {
            $error_msg = $error['msg'];
            logx("stockout no:$stockout_no, Query success:$error_msg! wms_query_trade ", $sid);
        }

    }
    releaseDb($db);

    return TASK_OK;
}


//查询采购单
public function  wms_query_purchase($task)
{
    deleteJob();
    $sid         = $task->sid;
    $purchase_id = $task->purchase_id;

    $db = getUserDb($sid);
    if(!$db)
    {
        logx("purchase id:$purchase_id, GetUserDb failed! wms_query_purchase",$sid);

        return TASK_OK;
    }

    $data = array();

    $purchase = $db->query_result("  SELECT po.purchase_id, po.purchase_no, po.outer_no,po.wms_outer_no, po.status, po.wms_status ,po.error_info , sw.warehouse_id, sw.type as wms_type, sw.api_key".
                               "       FROM purchase_order po ".
                               "  LEFT JOIN cfg_warehouse sw using(warehouse_id) ".
                               "      WHERE po.purchase_id = $purchase_id ");

    if(!$purchase)
    {
        $error_msg = $db->error_msg();
        logx("purchase id:$purchase_id, Get purchase info failed:$error_msg! wms_query_purchase",$sid);
        $db->execute(" UPDATE purchase_order SET error_info = '自动查询采购单失败：获取单据信息失败' where purchase_id = $purchase_id ");
        releaseDb($db);

        return TASK_OK;
    }

    $purchase_no = $purchase['purchase_no'];
    $outer_no = $purchase['outer_no'];
    if($purchase['status'] != 48 )
    {
        logx("outer_no:$outer_no, Purchase order status[{$purchase['status']}] error! wms_query_purchase",$sid);
        $db->execute(" UPDATE purchase_order SET error_info = '自动查询采购单失败：采购单状态非待收货' where purchase_id = $purchase_id ");
        releaseDb($db);

        return TASK_OK;
    }
    
    $wms_info = json_decode($purchase['api_key'],true);
    $wms_type = $purchase['wms_type'];

    //目前只支持京东沧海
    if ($wms_type <> 15)
    {
        logx("purchase no:$purchase_no, Warehouse type[$wms_type] error! wms_query_purchase",$sid);
        $db->execute(" UPDATE purchase_order SET error_info = '自动查询采购单失败：该仓库类型暂不支持' where purchase_id = $purchase_id ");
        releaseDb($db);

        return TASK_OK;
    }

    //京东沧海需要到店铺中获取授权信息
    if ($wms_type == 15)
    {
        $shop_api = getShopAuth($sid, $db, $wms_info['shop_id']);
        if(!$shop_api)
        {
            logx("outer_no:$outer_no, Shop[{$wms_info['shop_id']}] not auth! wms_query_purchase",$sid);
            $db->execute(" UPDATE purchase_order SET error_info= '自动查询采购单失败：获取店铺授权信息失败' where purchase_id = $purchase_id");
            releaseDb($db);

            return TASK_OK;
        }
        $wms_info['appKey']      = $shop_api->key;
        $wms_info['appSecret']   = $shop_api->secret;
        $wms_info['accessToken'] = $shop_api->session;
    }
    
    $data['purchase'] = $purchase;
    $wms_adapter = new WmsAdapter($wms_type,$wms_info);
    logx("start to send requset, outer_no:$outer_no  purchase_id:$purchase_id",$sid);
    $result = $wms_adapter->sendRequest(WMS_METHOD_PURCHASE_QUERY,$data,$sid);
    $send   = $wms_adapter->getSendParams();
    $resv   = $wms_adapter->getReceived();

    logx("outer_no:$outer_no".' send:    '.print_r($send,true),$sid);
    logx("outer_no:$outer_no".' receive: '.print_r($resv,true),$sid);

    $code = $result['code'];
    $error_msg = $result['error_msg'];
    $rev_info = isset($result['rev_info'])?$result['rev_info']:'';

    if(mb_strlen($error_msg,'utf-8') > 254)//如果超过长度则截取
    {
        $error_msg = mb_substr($error_msg,0,200,"utf-8");
    }
    $error_msg = $db->escape_string($error_msg);

    if($code != 0)
    {
        logx("outer_no:$outer_no, Error:$error_msg! wms_query_purchase ", $sid);
        $db->execute(" UPDATE purchase_order SET error_info= '自动查询采购单失败：$error_msg' where purchase_id = $purchase_id");
        releaseDb($db);

        return TASK_OK;
    }
    else
    {
        if(empty($rev_info))
        {
            logx("outer_no:$outer_no, No return order info! wms_query_purchase ", $sid);
            $db->execute(" UPDATE purchase_order SET error_info= '自动查询采购单失败：WMS未返回单据信息' where purchase_id = $purchase_id");
            releaseDb($db);

            return TASK_OK;
        }
        $purchase_info = $rev_info['order'];

        //单据状态
        if($purchase_info['status'] != STATUS_FINISH)
        {
            logx("outer_no:$outer_no, Order unfinished,current status[{$purchase_info['status_name']}]! wms_query_purchase ", $sid);
            $db->execute(" UPDATE purchase_order SET error_info= '自动查询采购单:{$purchase_info['status_name']}' where purchase_id = $purchase_id");
            releaseDb($db);

            return TASK_OK;
        }

        //单号校验
        if($purchase_info['outer_no'] == '')
        {
            logx("outer_no:$outer_no, No return outer no! wms_query_purchase ", $sid);
            $db->execute(" UPDATE purchase_order SET error_info= '自动查询采购单失败：WMS未返回外部单号' where purchase_id = $purchase_id");
            releaseDb($db);

            return TASK_OK;
        }
        if($purchase_info['outer_no'] != $outer_no)
        {
            logx("outer_no:$outer_no, wms_outer_no[{$purchase_info['outer_no']}] is different from erp_outer_no[$outer_no]! wms_query_purchase ", $sid);
            $db->execute(" UPDATE purchase_order SET error_info= '自动查询采购单失败：WMS返回外部单号与旺店通中不一致' where purchase_id = $purchase_id");
            releaseDb($db);

            return TASK_OK;
        }

        $order[] = array(
            'order_no'          => $purchase_info['outer_no'],
            'order_type'        => ORDER_TYPE_PURCHASE,
            'status'            => STATUS_FINISH,
        );

        $detail_list = $rev_info['details'];

        if($wms_type == 15)
        {
            //京东沧海回传的是仓库编码，需要先转换成商家编码
            $details = $db->query("    SELECT gs.spec_no,ss.spec_wh_no2 ".
                                  "      FROM purchase_order_detail pod ".
                                  " LEFT JOIN goods_spec gs ON pod.spec_id = gs.spec_id ".
                                  " LEFT JOIN stock_spec ss ON pod.spec_id = ss.spec_id AND ss.warehouse_id = {$purchase['warehouse_id']} ".
                                  "     WHERE pod.purchase_id = {$purchase['purchase_id']} AND ss.spec_wh_no2 <> '' ");
            if (!$details)
            {
                $error_msg = $db->error_msg();
                logx( "purchase no:$purchase_no, Get details failed:$error_msg! wms_query_purchase ", $sid );
                $db->execute( " UPDATE purchase_order SET error_info= '自动查询采购单失败：获取单据明细失败' where purchase_id = $purchase_id" );
                releaseDb($db);

                return TASK_OK;
            }

            while ($row = $db->fetch_array($details))
            {
                $spec_transform[$row['spec_wh_no2']] = $row['spec_no'];
            }
            foreach ($detail_list as &$detail)
            {
                if (!array_key_exists($detail['spec_no'], $spec_transform)) 
                {
                    logx( "outer_no:$outer_no, unknown spec[{$detail['spec_no']}]! wms_query_purchase ", $sid );
                    $db->execute( " UPDATE purchase_order SET error_info= '自动查询采购单失败：WMS返回不明商品[{$detail['spec_no']}]' where purchase_id = $purchase_id" );
                    releaseDb($db);

                    return TASK_OK;
                }
                $detail['spec_no'] = $spec_transform[$detail['spec_no']];
            }
        }
        
        $error = array();
        wms_update_order_status($db,$order,$detail_list,$error);
        if($error['code'] != 0)
        {
            $error_msg = $error['msg'];
            logx("outer_no:$outer_no, Call SP_WMS_ORDER_HANDLE faild:$error_msg! wms_query_purchase ", $sid);
            $db->execute(" UPDATE purchase_order SET error_info= '自动查询采购单失败:$error_msg' where purchase_id = $purchase_id");
            releaseDb($db);

            return TASK_OK;
        }
    }
    releaseDb($db);

    return TASK_OK;

}

//查询采购退货
public function  wms_query_purchase_return($task)
{
    deleteJob();
    $sid         = $task->sid;
    $return_id = $task->return_id;

    $db = getUserDb($sid);
    if(!$db)
    {
        logx("return_id id:$return_id, GetUserDb failed! wms_query_purchase_return",$sid);

        return TASK_OK;
    }

    $data = array();

    $trade = $db->query_result("     SELECT pr.return_id, pr.return_no, pr.outer_no, pr.wms_outer_no,  pr.status, pr.wms_status,pr.error_info, sw.warehouse_id, sw.type as wms_type, sw.api_key".
                               "       FROM purchase_return pr ".
                               "  LEFT JOIN cfg_warehouse sw using(warehouse_id) ".
                               "      WHERE pr.return_id = $return_id ");
    if(!$trade)
    {
        $error_msg = $db->error_msg();
        logx("return_id id:$return_id, Get purchase_return info failed:$error_msg ! wms_query_purchase_return",$sid);
        $db->execute(" UPDATE purchase_return SET error_info = '自动查询采购退货单失败：获取单据信息失败' where return_id = $return_id ");
        releaseDb($db);

        return TASK_OK;
    }

    $outer_no = $trade['outer_no'];
    if($trade['status'] != 46 )
    {
        logx("outer no:$outer_no, Purchase return status[{$trade['status']}] error! wms_query_purchase_return",$sid);
        $db->execute(" UPDATE purchase_return SET error_info = '自动查询采购退货单失败：采购退货单状态非委外待出库' where return_id = $return_id ");
        releaseDb($db);

        return TASK_OK;
    }

    $wms_info = json_decode($trade['api_key'],true);
    $wms_type = $trade['wms_type'];

    //目前只支持京东沧海
    if ($wms_type <> 15)
    {
        logx("outer no:$outer_no, Warehouse type[$wms_type] error! wms_query_purchase_return",$sid);
        $db->execute(" UPDATE stockout_order SET error_info = '自动查询采购退货单失败：该仓库类型暂不支持' where return_id = $return_id ");
        releaseDb($db);

        return TASK_OK;
    }
    //京东沧海需要到店铺中获取授权信息
    if ($wms_type == 15)
    {
        $shop_api = getShopAuth($sid, $db, $wms_info['shop_id']);
        if(!$shop_api)
        {
            logx("outer no:$outer_no, Shop[{$wms_info['shop_id']}] not auth! wms_query_purchase_return",$sid);
            $db->execute(" UPDATE purchase_return SET error_info= '自动查询订采购退货单失败：获取店铺授权信息失败' where return_id = $return_id");
            releaseDb($db);

            return TASK_OK;
        }
        $wms_info['appKey']      = $shop_api->key;
        $wms_info['appSecret']   = $shop_api->secret;
        $wms_info['accessToken'] = $shop_api->session;
    }
    $data['return'] = $trade;
    $wms_adapter = new WmsAdapter($wms_type,$wms_info);
    logx("start to send requset, outer no:$outer_no  return_id:$return_id",$sid);
    $result = $wms_adapter->sendRequest(WMS_METHOD_PURCHASE_RETURN_QUERY,$data,$sid);
    $send   = $wms_adapter->getSendParams();
    $resv   = $wms_adapter->getReceived();

    logx("outer no:$outer_no".' send:    '.print_r($send,true),$sid);
    logx("outer no:$outer_no".' receive: '.print_r($resv,true),$sid);

    $code = $result['code'];
    $error_msg = $result['error_msg'];
    $rev_info = isset($result['rev_info'])?$result['rev_info']:'';

    if(mb_strlen($error_msg,'utf-8') > 254)//如果超过长度则截取
    {
        $error_msg = mb_substr($error_msg,0,200,"utf-8");
    }
    $error_msg = $db->escape_string($error_msg);

    if($code != 0)
    {
        logx("outer no:$outer_no, Error:$error_msg! wms_query_purchase_return ", $sid);
        $db->execute(" UPDATE purchase_return SET error_info= '自动查询采购退货单失败：$error_msg' where return_id = $return_id");
        releaseDb($db);

        return TASK_OK;
    }
    else
    {
        if(empty($rev_info))
        {
            logx("outer no:$outer_no, No return order info! wms_query_purchase_return ", $sid);
            $db->execute(" UPDATE purchase_return SET error_info= '自动查询采购退货单失败：WMS未返回单据信息' where return_id = $return_id");
            releaseDb($db);

            return TASK_OK;
        }

        $trade_info = $rev_info['order'];
        //单据状态
        if($trade_info['status'] != STATUS_FINISH)
        {
            logx("outer no:$outer_no, Order unfinished,current status[{$trade_info['status_name']}]! wms_query_purchase_return ", $sid);
            $db->execute(" UPDATE purchase_return SET error_info= '自动查询采购退货单:{$trade_info['status_name']}' where return_id = $return_id");
            releaseDb($db);

            return TASK_OK;
        }

        //单号校验
        if($trade_info['outer_no'] == '')
        {
            logx("outer no:$outer_no, No return outer no! wms_query_purchase_return ", $sid);
            $db->execute(" UPDATE purchase_return SET error_info= '自动查询采购退货单失败：WMS未返回外部单号' where return_id = $return_id");
            releaseDb($db);

            return TASK_OK;
        }
        if($trade_info['outer_no'] != $outer_no)
        {
            logx("outer no:$outer_no, wms_outer_no[{$trade_info['outer_no']}] is different from erp_outer_no[$outer_no]! wms_query_purchase_return ", $sid);
            $db->execute(" UPDATE purchase_return SET error_info= '自动查询采购退货单失败：WMS返回外部单号与旺店通中不一致' where return_id = $return_id");
            releaseDb($db);

            return TASK_OK;
        }

        $order[] = array(
            'order_no'          => $trade_info['outer_no'],
            'order_type'        => ORDER_TYPE_PURCHASE_RETURN,
            'status'            => STATUS_FINISH,
        );

        $detail_list = $rev_info['details'];
        if($wms_type == 15)
        {
            //京东沧海回传的是仓库编码，需要先转换成商家编码
            $details = $db->query("    SELECT gs.spec_no,ss.spec_wh_no2 ".
                                  "      FROM purchase_return_detail prd ".
                                  " LEFT JOIN goods_spec gs ON prd.spec_id = gs.spec_id ".
                                  " LEFT JOIN stock_spec ss ON prd.spec_id = ss.spec_id AND ss.warehouse_id = {$trade['warehouse_id']} ".
                                  "     WHERE prd.return_id = {$trade['return_id']} AND ss.spec_wh_no2 <> '' ");
            if (!$details)
            {
                $error_msg = $db->error_msg();
                logx( "outer no:$outer_no, Get details failed:$error_msg! wms_query_purchase_return ", $sid );
                $db->execute( " UPDATE purchase_return SET error_info= '自动查询采购退货单失败：获取单据明细失败' where return_id = $return_id" );
                releaseDb($db);

                return TASK_OK;
            }

            while ($row = $db->fetch_array($details))
            {
                $spec_transform[$row['spec_wh_no2']] = $row['spec_no'];
            }
            foreach ($detail_list as &$detail)
            {
                if (!array_key_exists($detail['spec_no'], $spec_transform))
                {
                    logx( "outer no:$outer_no, unknown spec[{$detail['spec_no']}]! wms_query_purchase_return ", $sid );
                    $db->execute( " UPDATE purchase_return SET error_info= '自动查询采购退货单失败：WMS返回不明商品[{$detail['spec_no']}]' where return_id = $return_id" );
                    releaseDb($db);

                    return TASK_OK;
                }
                $detail['spec_no'] = $spec_transform[$detail['spec_no']];
            }
        }

        $error = array();
        wms_update_order_status($db,$order,$detail_list,$error);
        if($error['code'] != 0)
        {
            $error_msg = $error['msg'];
            logx("outer no:$outer_no, Call SP_WMS_ORDER_HANDLE faild:$error_msg! wms_query_purchase_return ", $sid);
            $db->execute(" UPDATE purchase_return SET error_info= '自动查询采购退货单失败:$error_msg' where return_id = $return_id");
            releaseDb($db);

            return TASK_OK;
        }

    }
    releaseDb($db);

    return TASK_OK;
}

//查询京东VMI库存流水
public function  wms_query_vmi_stockchange($task)
{
    $sid          = $task->sid;
    $shop_no      = $task->shop_no;
    $warehouse_no = $task->warehouse_no;

    $db = getUserDb($sid);
    if(!$db)
    {
        logx("shop no:$shop_no--warehouse no:$warehouse_no, GetUserDb failed! wms_query_vmi_stockchange", $sid);

        return TASK_OK;
    }

    //取上次查询成功的时间
    $end_time   = time();
    $page_size  = 100;
    $start_time = $db->query_result_single(" SELECT `value` FROM sys_setting WHERE `key` = 'vmi_stockchange_last_synctime_{$shop_no}_{$warehouse_no}'");
    if(!$start_time)
    {
        //判断表里是否已经存在未处理的情况
        if($db->query_result_single(" SELECT rec_id FROM wms_vmi_exception WHERE shop_no = '{$shop_no}' AND warehouse_no = '{$warehouse_no}' AND vmi_type = 1 AND is_handled = 0 AND error_msg = '获取开始时间失败，请联系旺店通处理'"))
        {
            logx("shop no:$shop_no--warehouse no:$warehouse_no, Get start time failed and wms_vmi_exception already has info! wms_query_vmi_stockchange", $sid); 
        }
        else
        {
            $db->execute("INSERT INTO wms_vmi_exception(shop_no,warehouse_no,page_size,error_msg,vmi_type) VALUES('{$shop_no}','{$warehouse_no}',{$page_size},'获取开始时间失败，请联系旺店通处理',1)");
            logx("shop no:$shop_no--warehouse no:$warehouse_no, Get start time failed! wms_query_vmi_stockchange", $sid);
        }

        releaseDb($db);

        return TASK_OK;
    }


    if($start_time >= $end_time)
    {
        logx("shop no:$shop_no--warehouse no:$warehouse_no, start_time[$start_time] is over end_time[$end_time]! wms_query_vmi_stockchange", $sid);
        releaseDb($db);

        return TASK_OK;
    }

    //查询间隔不能超过30天，如果超过30天则只查询最近30天的
    /*if ($end_time - $start_time > 30*24*60*60)
    {

        logx("shop no:$shop_no--warehouse no:$warehouse_no, Query interval is over 30 days,adjust to 30 days! wms_query_vmi_stockchange", $sid);
        $start_time = $end_time - 30*24*60*60;
    }*/
    $start_time = date("Y-m-d H:i:s",$start_time -1); //提前一秒，防止漏查
    $end_time   = date("Y-m-d H:i:s",$end_time);


    $warehouse_info = $db->query_result(" SELECT warehouse_id,api_key,type ".
                                        "   FROM cfg_warehouse ".
                                        "  WHERE ext_warehouse_no = '$warehouse_no'  AND type = 15  ");
    if(!$warehouse_info)
    {
        wms_merge_vmi_stockchange($db,$shop_no,$warehouse_no,$sid, $start_time,$end_time,"VMI查询失败：获取仓库信息失败，请检查京东沧海[{$warehouse_no}]仓库是否已下载",$page_size);
        logx("shop no:$shop_no--warehouse no:$warehouse_no, Get warehouse info failed! wms_query_vmi_stockchange",$sid);
        releaseDb($db);

        return TASK_OK;
    }
    $wms_type = $warehouse_info['type'];
    $wms_info = json_decode($warehouse_info['api_key'],true);




    $spec_info = $db->query("    SELECT ss.spec_wh_no2,gs.spec_no,gs.spec_id".
                            "      FROM stock_spec ss ".
                            " LEFT JOIN goods_spec gs USING(spec_id) ".
                            "     WHERE ss.warehouse_id = {$warehouse_info['warehouse_id']} AND ss.status = 1 AND gs.deleted = 0 AND ss.spec_wh_no2 <> '' ");
    if(!$spec_info)
    {
        $error_msg = $db->error_msg();
        wms_merge_vmi_stockchange($db,$shop_no,$warehouse_no,$sid, $start_time,$end_time,"VMI查询失败：服务器内部错误",$page_size);
        logx("shop no:$shop_no--warehouse no:$warehouse_no, Get spec info failed:$error_msg! wms_query_vmi_stockchange",$sid);
        releaseDb($db);

        return TASK_OK;
    }
    if($spec_info->num_rows == 0)
    {
        wms_merge_vmi_stockchange($db,$shop_no,$warehouse_no,$sid, $start_time,$end_time,'VMI查询失败：仓库无商品信息，请添加商品后再操作',$page_size);
        logx("shop no:$shop_no--warehouse no:$warehouse_no, Get 0 spec! wms_query_vmi_stockchange",$sid);
        releaseDb($db);

        return TASK_OK;
    }
    $spec_transform = array();
    while($row = $db->fetch_array($spec_info))
    {
        $spec_transform[$row['spec_wh_no2']] = array(
            'spec_id'   => $row['spec_id'],
            'spec_no'   => $row['spec_no'],
        );
    }

    $shop_api = getShopAuth($sid, $db, $wms_info['shop_id']);
    if (!$shop_api)
    {
        wms_merge_vmi_stockchange($db,$shop_no,$warehouse_no,$sid, $start_time,$end_time,'VMI查询失败：京东店铺未授权，请授权后重试',$page_size);
        logx("shop no:$shop_no--warehouse no:$warehouse_no, Shop not auth! wms_query_vmi_stockchange",$sid);
        releaseDb($db);

        return TASK_OK;
    }
    $wms_info['appKey']      = $shop_api->key;
    $wms_info['appSecret']   = $shop_api->secret;
    $wms_info['accessToken'] = $shop_api->session;
    
    $page_no = 0;
    $code = 0;
    $data = array(
        'shop_no'     => $shop_no,
        'start_time'  => $start_time,
        'end_time'    => $end_time,
        'page_no'     => $page_no,
        'page_size'   => $page_size
    );

    //查询结束条件：1.报错 2.查不到数据
    while($code == 0)
    {
        $page_no += 1;
        $data['page_no'] = $page_no;

        $wms_adapter = new WmsAdapter($wms_type,$wms_info);
        logx("start to send requset, shop no:$shop_no--warehouse no:$warehouse_no--start time:$start_time--end time:$end_time--page no:$page_no",$sid);
        $result = $wms_adapter->sendRequest(WMS_METHOD_VMI_STOCKCHANGE_QUERY,$data,$sid);
        $send   = $wms_adapter->getSendParams();
        $resv   = $wms_adapter->getReceived();

        logx("shop no:$shop_no--warehouse no:$warehouse_no--page no:$page_no".' send:    '.print_r($send,true),$sid);
        logx("shop no:$shop_no--warehouse no:$warehouse_no--page no:$page_no".' receive: '.print_r($resv,true),$sid);

        $code = $result['code'];
        $error_msg = $result['error_msg'];
        $rev_info = isset($result['rev_info'])?$result['rev_info']:'';
        if (mb_strlen($error_msg,'utf-8') > 254)//如果超过长度则截取
        {
            $error_msg = mb_substr($error_msg,0,200,"utf-8");
        }
        $error_msg = $db->escape_string($error_msg);

        if ($code <> 0)
        {
            //查不到数据
            if ($code == 911)
            {
                logx("shop no:$shop_no--warehouse no:$warehouse_no--page no:$page_no, Query end! wms_query_vmi_stockchange");
                $code = 0;
                break;
            }
            else
            {
                wms_merge_vmi_stockchange($db,$shop_no,$warehouse_no,$sid,$start_time,$end_time,$error_msg,$page_size,$page_no);
                logx("shop no:$shop_no--warehouse no:$warehouse_no--page no:$page_no, Error:$error_msg! wms_query_vmi_stockchange");
                break;
            }
        }

        if (empty($rev_info))
        {
            wms_merge_vmi_stockchange($db,$shop_no,$warehouse_no,$sid,$start_time,$end_time,'VMI查询失败：京东未返回库存流水信息',$page_size,$page_no);
            logx("shop no:$shop_no--warehouse no:$warehouse_no--page no:$page_no, No return stockchange info! wms_query_vmi_stockchange", $sid);
            $code = 99;
            break;
        }

        $detail_list = $rev_info['details'];
        $order   = array();
        $details = array();
        foreach ($detail_list as $detail)
        {
            //仓库编号
            if ($detail['warehouse_no'] == '')
            {
                wms_merge_vmi_stockchange($db,$shop_no,$warehouse_no,$sid,$start_time,$end_time,'VMI查询失败：京东未返回仓库编号信息',$page_size,$page_no);
                logx("shop no:$shop_no--warehouse no:$warehouse_no--page no:$page_no, No return warehouse no! wms_query_vmi_stockchange ", $sid);
                $code = 99;
                break 2;
            }
            if ($detail['warehouse_no'] != $warehouse_no)
            {
                wms_merge_vmi_stockchange($db,$shop_no,$warehouse_no,$sid,$start_time,$end_time,'VMI查询失败：京东返回仓库编号与系统仓库编号不一致',$page_size,$page_no);
                logx("shop no:$shop_no--warehouse no:$warehouse_no--page no:$page_no, Wms warehouse no[{$detail['warehouse_no']}] is different from oms warehouse no[$warehouse_no]! wms_query_vmi_stockchange ", $sid);
                $code = 99;
                break 2;
            }

            //流水号（唯一ID校验）
            if ($detail['biz_code'] == '')
            {
                wms_merge_vmi_stockchange($db,$shop_no,$warehouse_no,$sid,$start_time,$end_time,'VMI查询失败：京东未返回流水号信息',$page_size,$page_no);
                logx("shop no:$shop_no--warehouse no:$warehouse_no--page no:$page_no, No return biz code! wms_query_vmi_stockchange ", $sid);
                $code = 99;
                break 2;
            }

            //商品编号
            if ($detail['spec_no'] == '')
            {
                wms_merge_vmi_stockchange($db,$shop_no,$warehouse_no,$sid,$start_time,$end_time,'VMI查询失败：京东未返回商品编号信息',$page_size,$page_no);
                logx("shop no:$shop_no--warehouse no:$warehouse_no--page no:$page_no, No return spec no! wms_query_vmi_stockchange ", $sid);
                $code = 99;
                break 2;
            }

            //旧数量
            if ($detail['old_num'] === '')
            {
                wms_merge_vmi_stockchange($db,$shop_no,$warehouse_no,$sid,$start_time,$end_time,'VMI查询失败：京东未返回变更前数量',$page_size,$page_no);
                logx("shop no:$shop_no--warehouse no:$warehouse_no--page no:$page_no, No return old num! wms_query_vmi_stockchange ", $sid);
                $code = 99;
                break 2;
            }
            //京东要求数量为负则跳过
            if ($detail['old_num'] < 0)
            {
                logx("shop no:$shop_no--warehouse no:$warehouse_no--page no:$page_no, old num[{$detail['old_num']}] < 0! wms_query_vmi_stockchange ", $sid);
                continue;
            }

            //新数量
            if ($detail['new_num'] === '')
            {
                wms_merge_vmi_stockchange($db,$shop_no,$warehouse_no,$sid,$start_time,$end_time,'VMI查询失败：京东未返回变更后数量',$page_size,$page_no);
                logx("shop no:$shop_no--warehouse no:$warehouse_no--page no:$page_no, No return new num! wms_query_vmi_stockchange ", $sid);
                $code = 99;
                break 2;
            }
            //京东要求数量为负则跳过
            if ($detail['new_num'] < 0)
            {
                logx("shop no:$shop_no--warehouse no:$warehouse_no--page no:$page_no, new num[{$detail['new_num']}] < 0! wms_query_vmi_stockchange ", $sid);
                continue;
            }

            //修改原因
            if ($detail['remark'] == '')
            {
                wms_merge_vmi_stockchange($db,$shop_no,$warehouse_no,$sid,$start_time,$end_time,'VMI查询失败：京东未返回修改原因',$page_size,$page_no);
                logx("shop no:$shop_no--warehouse no:$warehouse_no--page no:$page_no, No return remark! wms_query_vmi_stockchange ", $sid);
                $code = 99;
                break 2;
            }
            //只处理部分流水数据
            if ($detail['remark'] == '设置VMI库存' || $detail['remark'] == '商品库存比例调整')
            {
                logx("shop no:$shop_no--warehouse no:$warehouse_no--page no:$page_no, Remark[{$detail['remark']}] not handle! wms_query_vmi_stockchange ", $sid);
                continue;
            }

            $diff_num = $detail['new_num'] - $detail['old_num'];
            if ($diff_num == 0)
            {
                logx("shop no:$shop_no--warehouse no:$warehouse_no--page no:$page_no, new num == old num! wms_query_vmi_stockchange ", $sid);
                continue;
            }

            //商家编码转换
            if (!array_key_exists($detail['spec_no'],$spec_transform))
            {
                wms_merge_vmi_stockchange($db,$shop_no,$warehouse_no,$sid,$start_time,$end_time,"VMI查询失败：京东返回未知货品[{$detail['spec_no']}]",$page_size,$page_no);
                logx("shop no:$shop_no--warehouse no:$warehouse_no--page no:$page_no, Unknow spec[{$detail['spec_no']}]! wms_query_vmi_stockchange ", $sid);
                $code = 99;
                break 2;
            }
            $spec_no = $spec_transform[$detail['spec_no']]['spec_no'];
            $spec_id = $spec_transform[$detail['spec_no']]['spec_id'];

            $details[] = array(
                'order_no'  => 'VMI',
                'spec_no'   => $spec_no,
                'spec_id'   => $spec_id,
                'num'       => $diff_num,
                'biz_code'  => md5($detail['biz_code']),
                'remark'    => $detail['biz_code'].':'.$detail['remark']
            );

        }

        $order[]   = array(
            'wms_no'     => $warehouse_no,
            'order_no'   => 'VMI',
            'owner_no'   => $wms_info['deptNo'],
            'order_type' => ORDER_TYPE_VMI_STOCKCHANGE,
        );
        if (!empty($details))
        {
            $error = array();
            wms_update_order_status($db,$order,$details,$error);
            if($error['code'] != 0)
            {
                $code = $error['code'];
                $error_msg = $error['msg'];
                wms_merge_vmi_stockchange($db,$shop_no,$warehouse_no,$sid,$start_time,$end_time,"{$error_msg}",$page_size,$page_no);
                logx("shop no:$shop_no--warehouse no:$warehouse_no--page no:$page_no, CALL SP_WMS_ORDER_HANDLE failed:$error_msg! wms_query_vmi_stockchange ", $sid);
                releaseDb($db);
                break;
            }
            logx("shop no:$shop_no--warehouse no:$warehouse_no--page no:$page_no, CALL SP_WMS_ORDER_HANDLE success! wms_query_vmi_stockchange ", $sid);
        }
    }
    //本次查询成功，更新sys_setting
    if($code == 0)
    {
        $end_time = strtotime($end_time);
        if(!$db->execute(" UPDATE sys_setting SET `value` = '$end_time' WHERE `key` = 'vmi_stockchange_last_synctime_{$shop_no}_{$warehouse_no}'"))
        {
            $error_msg = $db->error_msg();
            logx("shop no:$shop_no--warehouse no:$warehouse_no, Update sys_setting faild:$error_msg! wms_query_trade ", $sid);
        }
        logx("shop no:$shop_no--warehouse no:$warehouse_no, Query success! wms_query_trade ", $sid);
    }

    releaseDb($db);
    return TASK_OK;
}

//合并时间段
public function  wms_merge_vmi_stockchange(&$db,$shop_no,$warehouse_no,$sid,$start_time,$end_time,$error_msg,$page_size,$page_no=1)
{
    $timezones = $db->query("SELECT rec_id,start_time,end_time FROM wms_vmi_exception WHERE shop_no = '{$shop_no}' AND warehouse_no = '{$warehouse_no}' AND vmi_type = 1 AND is_handled = 0");
    if($timezones)
    {
        while ($timezone= $db->fetch_array( $timezones))
        {
            if(strtotime($start_time) <= strtotime($timezone['end_time']))
            {
                $end_time_chuo = strtotime($end_time);
                if(!$db->execute('BEGIN') || !$db->execute("UPDATE wms_vmi_exception SET end_time = '{$end_time}',page_no = {$page_no}, error_msg = '{$error_msg}' WHERE rec_id = {$timezone['rec_id']}") || !$db->execute(" UPDATE sys_setting SET `value` = '{$end_time_chuo}'  WHERE `key` = 'vmi_stockchange_last_synctime_{$shop_no}_{$warehouse_no}'"))
                {
                    if(!$db->execute('ROLLBACK'))
                    {
                        logx("ROLLBACK failed  ".$db->error_msg(),$sid);
                    }
                    else
                    {
                        logx("ROLLBACK success",$sid);
                    }

                    logx("shop no:$shop_no--warehouse no:$warehouse_no,  {$error_msg}--update failed! wms_merge_vmi_stockchange".$db->error_msg(), $sid);

                }
                else
                {
                    if(!$db->execute('COMMIT'))
                    {
                        logx("COMMIT failed  ".$db->error_msg(),$sid);
                    }
                    else
                    {
                        logx("COMMIT sucess  ",$sid);
                    }
                }
                return;

            }
        }

    }
    $end_time_chuo = strtotime($end_time);
    logx("shop no:$shop_no--warehouse no:$warehouse_no, {$error_msg}--Mermging timezone: get timezones failed! wms_query_vmi_stockchange", $sid);
    if(!$db->execute("BEGIN") || !$db->execute("INSERT INTO wms_vmi_exception(shop_no,warehouse_no,start_time,end_time,page_no,page_size,error_msg,vmi_type) VALUES('{$shop_no}','{$warehouse_no}','{$start_time}','{$end_time}',{$page_no},{$page_size},'{$error_msg}',1)") || !$db->execute(" UPDATE sys_setting SET `value` = '{$end_time_chuo}'  WHERE `key` = 'vmi_stockchange_last_synctime_{$shop_no}_{$warehouse_no}'"))
    {
        if(!$db->execute('ROLLBACK'))
        { 
            logx("ROLLBACK failed  ".$db->error_msg(),$sid);
        }        
        else
        {   
            logx("ROLLBACK success",$sid);
        } 
        logx("shop no:$shop_no--warehouse no:$warehouse_no,  {$error_msg}--insert failed! wms_merge_vmi_stockchange".$db->error_msg(), $sid);

    }
    else
    {
        if(!$db->execute('COMMIT'))
        {    
            logx("COMMIT failed  ".$db->error_msg(),$sid);
        }
        else
        {
            logx("COMMIT sucess  ",$sid);
        }
    }
    
    return;
}

//订单驳回
public function  order_refresh_handle($db, $stockout_id, $sid, $trade_id, $is_revert_pushed_order)
{
    if( $db->execute('BEGIN') !== false &&
        $db->execute("SET @cur_uid=0") &&
        $db->query("call I_STOCKOUT_ORDER_REVERT_CHECK2($stockout_id,0,0,0,1,$is_revert_pushed_order)")
    )
    {
        $sys_msg = $db->query_result("SELECT @sys_code as code, @sys_message as msg");
        if($sys_msg['code'] != 0)//失败
        {
            $error_msg = $sys_msg['msg'];
            $db->execute('ROLLBACK');
            $db->execute("insert into sales_trade_log(trade_id,operator_id,type,message) values($trade_id,0,300,'系统自动取消外部WMS订单失败')");
            logx("call I_STOCKOUT_ORDER_REVERT_CHECK failed  {$error_msg} ",$sid);
            return ;
        }
        $db->query("UPDATE stockout_order SET wms_status=0,error_info='' where stockout_id=%d", $stockout_id);
        $db->execute("COMMIT");

        logx("stockout_id: {$stockout_id}, success in wms_query_trade",$sid);
        $db->execute("insert into sales_trade_log(trade_id,operator_id,type,message) values($trade_id,0,300,'系统自动取消外部WMS订单成功')");

        return ;
    }
    else
    {
        logx("call I_STOCKOUT_ORDER_REVERT_CHECK failed!!,stockout_id:$stockout_id error:".$db->error_msg(),$sid);
        $db->execute("ROLLBACK");
        $error_msg  = '服务器异常,请稍后重试';

        logx("stockout_id: {$stockout_id}, {$error_msg} in wms_query_trade",$sid);
        $db->execute("UPDATE stockout_order SET wms_status=3,error_info='{$error_msg}' where stockout_id={$stockout_id}");
        $db->execute("insert into sales_trade_log(trade_id,operator_id,type,message) values($trade_id,0,300,'系统自动取消外部WMS订单失败')");
        return ;
    }

}

    

//查询销售退货入库
public function  wms_query_sales_refund($task)
{
    deleteJob();
    $sid = $task->sid;
    $refund_id = $task->refund_id;

    $db = getUserDb($sid);
    if(!$db)
    {
        logx("refund id:$refund_id, GetUserDb failed! wms_query_sales_refund", $sid);

        return TASK_OK;
    }

    $data = array();

    $sales_refund = $db->query_result("     SELECT sr.refund_id, sr.refund_no,  sr.wms_outer_no, sr.outer_no, sr.process_status, 
                                                   sr.wms_status, sw.warehouse_id,sw.ext_warehouse_no, sw.type as wms_type, sw.api_key, sr.sales_trade_id, so.outer_no as stockout_outer_no" .
                                      "       FROM sales_refund sr " .
                                      "  LEFT JOIN stockout_order so on sr.sales_trade_id=so.src_order_id" .
                                      "  LEFT JOIN cfg_warehouse sw on sr.warehouse_id=sw.warehouse_id".
                                      "      WHERE sr.refund_id = $refund_id ");
    if(!$sales_refund)
    {
        $error_msg = $db->error_msg();
        logx("refund_id id:$refund_id, Get sales_refund info failed:$error_msg ! wms_query_sales_refund", $sid);
        $db->execute(" UPDATE sales_refund SET wms_result= '自动查询销售退货单失败:$error_msg' where refund_id = $refund_id");
        releaseDb($db);

        return TASK_OK;
    }
    $outer_no = $sales_refund['outer_no'];
    if($sales_refund['process_status'] != 65)
    {
        logx("outer no:$outer_no, Get sales_refund info failed:Sales refund status[{$sales_refund['process_status']}] error! wms_query_sales_refund", $sid);
        $db->execute(" UPDATE sales_refund SET wms_result = '自动查询销售退货单失败：销售退货单处于非委外待收货状态' where refund_id = $refund_id ");
        releaseDb($db);

        return TASK_OK;
    }

    $wms_info = json_decode($sales_refund['api_key'], true);
    $wms_type = $sales_refund['wms_type'];

    //目前只支持京东沧海
    if($wms_type <> 15)
    {
        logx("outer no:$outer_no, Get sales_refund info failed:Warehouse type[$wms_type] error! wms_query_sales_refund", $sid);
        $db->execute(" UPDATE sales_refund SET wms_result = '自动查询销售退货单失败：该仓库类型暂不支持' where refund_id = $refund_id ");
        releaseDb($db);

        return TASK_OK;
    }
    //京东沧海需要到店铺中获取授权信息
    if($wms_type == 15)
    {
        $shop_api = getShopAuth($sid, $db, $wms_info['shop_id']);
        if (!$shop_api)
        {
            logx("outer no:$outer_no, Get sales_refund info failed:Shop[{$wms_info['shop_id']}] not auth! wms_query_sales_refund", $sid);
            $db->execute(" UPDATE sales_refund  SET wms_result= '自动查询销售退货单失败：获取店铺授权信息失败' where refund_id = $refund_id");
            releaseDb($db);

            return TASK_OK;
        }
        $wms_info['appKey'] = $shop_api->key;
        $wms_info['appSecret'] = $shop_api->secret;
        $wms_info['accessToken'] = $shop_api->session;
    }
    $data['refund'] = $sales_refund;

    $wms_adapter = new WmsAdapter($wms_type, $wms_info);
    logx("start to send requset, outer no:$outer_no  refund_id:$refund_id",$sid);
    $result = $wms_adapter->sendRequest(WMS_METHOD_SALES_REFUND_QUERY, $data, $sid);
    $send = $wms_adapter->getSendParams();
    $resv = $wms_adapter->getReceived();

    logx("outer no:$outer_no" . ' send:    ' . print_r($send, true), $sid);
    logx("outer no:$outer_no" . ' receive: ' . print_r($resv, true), $sid);

    $code = $result['code'];
    $error_msg = $result['error_msg'];
    $rev_info = isset($result['rev_info']) ? $result['rev_info'] : '';

    if(mb_strlen($error_msg, 'utf-8') > 254)//如果超过长度则截取
    {
        $error_msg = mb_substr($error_msg, 0, 200, "utf-8");
    }
    $error_msg = $db->escape_string($error_msg);

    if($code != 0)
    {
        $flag = time();
        logx("outer no:$outer_no, Error:$error_msg! wms_query_sales_refund ", $sid);
        $db->execute(" UPDATE sales_refund SET wms_result= '自动查询销售退库单失败：$error_msg.$flag' where refund_id = $refund_id");
        releaseDb($db);

        return TASK_OK;
    }
    else
    {

        if(empty($rev_info))
        {
            logx("outer_no:$outer_no,  Get sales_refund info failed:No return order info! wms_query_sales_refund ", $sid);
            $db->execute(" UPDATE sales_refund SET wms_result= '自动查询销售退货单失败：WMS未返回单据信息' where refund_id = $refund_id");
            releaseDb($db);

            return TASK_OK;
        }

        $sales_refund_info = $rev_info['order'];
        //判断传过来的单号是否与发送的外部单号（stockout_outer_no一致）
        if($sales_refund_info['outer_no'] != $sales_refund['stockout_outer_no'])
        {
            logx("outer_no:$outer_no, Get sales_refund info failed:The order number of sending and receiving is inconsistent ！The order number of sending：[{$sales_refund['stockout_outer_no']}];The order number of receiving：[{$sales_refund_info['outer_no']}]! wms_query_sales_refund ", $sid);
            $db->execute(" UPDATE sales_refund SET wms_result= '自动查询销售退货单失败:发送与接收的订单号不一致！发送订单号:{$sales_refund['stockout_outer_no']};接收的订单号:{$sales_refund_info['outer_no']}' where refund_id = $refund_id");
            releaseDb($db);

            return TASK_OK;
        }
        //判断仓库单号是否一致
        if($sales_refund_info['warehouse_no'] != $sales_refund['ext_warehouse_no'])
        {
            logx("outer_no:$outer_no, Get sales_refund info failed:The warehouse_no is inconsistent ! wms_query_sales_refund ", $sid);
            $db->execute(" UPDATE sales_refund SET wms_result= '自动查询销售退货单失败:仓库单号不一致！' where refund_id = $refund_id");
            releaseDb($db);

            return TASK_OK;
        }

        //单据状态
        if($sales_refund_info['status'] != STATUS_FINISH)
        {
            $flag = time();
            logx("outer_no:$outer_no, Get sales_refund info:Order unfinished,current status[{$sales_refund_info['status_name']}]! wms_query_sales_refund ", $sid);
            $db->execute(" UPDATE sales_refund SET wms_result= '自动查询销售退货单:{$sales_refund_info['status_name']}$flag' where refund_id = $refund_id");
            releaseDb($db);

            return TASK_OK;
        }

        $order[] = array(
            'order_no' => $sales_refund['outer_no'],
            'order_type' => ORDER_TYPE_REFUND,//单据类型-退货的存储过程
            'status' => STATUS_FINISH,
        );

        if($wms_type == 15)
        {
            //京东沧海回传的是仓库编码，需要先转换成商家编码
            $details = $db->query("SELECT spec_id, spec_no, order_num as num".
                                  "  FROM sales_refund_order " .
                                  " WHERE refund_id = {$sales_refund['refund_id']} ");
            if (!$details)
            {
                $error_msg = $db->error_msg();
                logx("outer no:$outer_no, Get details failed:$error_msg! wms_query_sales_refund ", $sid);
                $db->execute(" UPDATE sales_refund SET wms_result= '自动查询销售退货单失败：获取单据明细失败' where refund_id = $refund_id");
                releaseDb($db);

                return TASK_OK;
            }

            $detail_list = array();
            while($row = $db->fetch_array($details))
            {
                $detail_list[] = $row;
            }
            foreach($detail_list as $key => $value)
            {
                 $detail_list[$key]['order_no']=$sales_refund['outer_no'];
            }

            $error = array();
            wms_update_order_status($db, $order, $detail_list, $error);

            if($error['code'] != 0)
            {
                $error_msg = $error['msg'];
                logx("outer no:$outer_no, Call ORDER_TYPE_REFUND faild:$error_msg! wms_query_sales_refund ", $sid);
                $db->execute(" UPDATE sales_refund SET wms_result= '自动查询销售退货单失败:$error_msg' where refund_id = $refund_id");
                releaseDb($db);

                return TASK_OK;
            }

        }
        releaseDb($db);

        return TASK_OK;
    }

}


}