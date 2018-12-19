<?php
namespace Platform\Manager;
use \Think\Exception;

//adater_cmd 文件移过来的

require_once(ROOT_DIR . '/Manager/utils.php');
require_once(ROOT_DIR . '/Adapter/WmsAdapter.php');
require_once(ROOT_DIR . '/Adapter/adapter_utils.php');
require_once(ROOT_DIR . '/Adapter/adapter_cmd_auto_config.php');

class Wms_cmdManager extends Manager{
    //推送采购计划单
    function wms_adapter_add_plan_po($sid, $uid, $po_id)
    {
        global $g_sid_list;

        $db = getUserDb($sid);
        if (!$db)
        {
            logx("trade_id:$po_id, GetUserDb failed! wms_adapter_add_plan_po",$sid.'/WMS_CMD');
            ackError('推送采购计划单失败:服务器内部错误');
            return false;
        }

        //单据信息获取验证
        $po_plan_info = $db->query_result("select psp.stockin_plan_no,psp.warehouse_id,psp.outer_no,psp.wms_status,po.purchase_id,po.purchase_no,psp.status,psp.remark,psp.provider_id,psp.logistics_id,psp.logistics_no, ".
            " psp.expect_arrive_time,psp.created, sw.ext_warehouse_no, sw.api_object_id,sw.type as wms_type, sw.api_key, sw.name as warehouse_name,po.status as po_status,po.prop1,po.prop2, ".
            " po.receive_address,pp.provider_no,pp.provider_name ".
            " from purchase_stockin_plan psp ".
            " left join sys_warehouse sw on psp.warehouse_id = sw.warehouse_id ".
            " left join purchase_order po on po.purchase_id = psp.purchase_id ".
            " left join purchase_provider pp on po.provider_id=pp.provider_id ".
            " where psp.stockin_plan_id = $po_id");
        if (!$po_plan_info)
        {
            logx("trade_id:$po_id, Order not exist!! wms_adapter_add_plan_po",$sid.'/WMS_CMD');
            releaseDb($db);
            ackError('推送采购计划单失败:获取采购计划单信息失败');
            return false;
        }

        if ($po_plan_info['status'] != 43 && $po_plan_info['status'] != 45)
        {
            logx("trade_id:$po_id, Order status is wrong!! wms_adapter_add_plan_po",$sid.'/WMS_CMD');
            releaseDb($db);
            ackError('推送采购计划单失败:采购计划单状态错误');
            return false;
        }

        //推送成功更新失败,直接变更状态
        if($po_plan_info['wms_status'] == 3)
        {
            logx("trade_id:$po_id, Push success! wms_adapter_add_plan_po",$sid.'/WMS_CMD');
            $db->query("UPDATE purchase_stockin_plan SET status=48, wms_status=0, error_info='' where stockin_plan_id = $po_id ");
            releaseDb($db);
            return false;
        }

        if ($po_plan_info['po_status']  <= 40 and $po_plan_info['po_status'] >=50)
        {
            logx("trade_id:$po_id, Purchase order status is wrong!! wms_adapter_add_plan_po",$sid.'/WMS_CMD');
            releaseDb($db);
            ackError('推送采购计划单失败:采购计划单关联的采购单状态错误');
            return false;
        }

        if($po_plan_info['status'] == 43 || ($po_plan_info['status'] == 45 && $po_plan_info['outer_no'] == ''))
        {
            $outer_no = $db->query_result_single("SELECT FN_SYS_NO('outer_no')", '');
            if (empty($outer_no))
            {
                logx("trade_id:$po_id, FN_SYS_NO('outer_no')  failed! wms_adapter_add_plan_po", $sid.'/WMS_CMD');
                releaseDb($db);
                ackError("推送采购计划单失败:获取外部单号失败");
                return false;
            }
            $outer_no = 'OCJ' . $outer_no;
        }
        else
        {
            $outer_no = $po_plan_info['outer_no'];
        }
        $po_plan_info['outer_no'] = $outer_no;

        $wms_info = json_decode($po_plan_info['api_key'],true);
        $wms_type = $po_plan_info['wms_type'];

        if($wms_type != 11)
        {
            logx("outer no:{$outer_no}, WMS[{$po_plan_info['wms_type']}] didn't support! wms_adapter_add_plan_po",$sid.'/WMS_CMD');
            $db->query("UPDATE purchase_stockin_plan SET status=40, wms_status=1, error_info='该委外仓暂不支持采购计划单!' where stockin_plan_id = $po_id ");
            releaseDb($db);
            ackError('不支持的委外仓类型,请按内部采购计划单流程处理');
            return false;
        }

        $data['purchase'] = $po_plan_info;//封装信息

        //获取采购退货单货品详细信息
        $goods_info = $db->query(" select pspd.stockin_plan_id,gg.goods_no,gg.goods_name,gs.spec_no,gs.barcode,pspd.num,ss.spec_wh_no2 ".
            " from purchase_stockin_plan_detail pspd ".
            " left join goods_spec gs on gs.spec_id = pspd.spec_id ".
            " left join goods_goods gg on gs.goods_id = gg.goods_id ".
            " left join stock_spec ss on ss.spec_id = pspd.spec_id ".
            " where pspd.stockin_plan_id = $po_id and ss.warehouse_id = {$po_plan_info['warehouse_id']} ");
        if (!$goods_info)
        {
            $error_msg = $db->error_msg();
            logx("outer no:$outer_no, Get goods info failed:$error_msg! wms_adapter_add_po_refund", $sid.'/WMS_CMD');
            $db->execute("UPDATE purchase_return SET wms_status = $wms_status where return_id = $return_id ");
            releaseDb($db);
            ackError('推送采购退货单失败:获取货品信息失败');

            return false;
        }

        if ($goods_info->num_rows == 0)
        {
            logx("outer no:$outer_no, Goods not exists! wms_adapter_add_po_refund", $sid.'/WMS_CMD');
            $db->execute("UPDATE purchase_return SET wms_status = $wms_status  where return_id = $return_id ");
            releaseDb($db);
            ackError('推送采购退货单失败:货品明细为空');

            return false;
        }

        while($row = $db->fetch_array($goods_info))
        {
            $data['details'][] = $row;
        }


        $wms_adapter   = new WmsAdapter($wms_type, $wms_info);
        $wms_tmp_type  = isset($wms_info['wms_type'])?$wms_info['wms_type']:'';
        $method = WMS_METHOD_PURCHASE_PLAN_ORDER;

        $error_info = '';
        $result = $wms_adapter->getTransferFlag($wms_type, $wms_tmp_type, $method, $error_info);
        if($result == 0)
        {
            logx("outer no:{$outer_no}, wms[$wms_tmp_type] didn't support, $error_info! wms_adapter_add_plan_po",$sid.'/WMS_CMD');
            $db->query("UPDATE purchase_stockin_plan SET status=40, wms_status=1, error_info='该委外仓暂不支持采购计划单!' where stockin_plan_id = $po_id ");
            releaseDb($db);
            ackError('该委外仓暂不支持采购计划单,转为内部流程处理');
            return;
        }

        $api_flag = isset($g_sid_list[$sid])?$g_sid_list[$sid]:'';
        $planFlag = isset($api_flag['plan_flag'][ORDER_TYPE_PLAN_PURCHASE])?$api_flag['plan_flag'][ORDER_TYPE_PLAN_PURCHASE]:0;

        if($planFlag == 0)
        {
            logx("outer no:{$outer_no},  have not open interface yet! wms_adapter_add_plan_po",$sid.'/WMS_CMD');
            $db->query("UPDATE purchase_stockin_plan SET status=40, wms_status=1, error_info='当前暂未开通采购计划单接口!' where stockin_plan_id = $po_id ");
            releaseDb($db);
            ackError('当前暂未开通采购计划单接口,转为内部流程处理');
            return;
        }

        logx("start to send requset,outer no:{$outer_no}  stockin_plan_id:$po_id ",$sid.'/WMS_CMD');
        $result = $wms_adapter->sendRequest(WMS_METHOD_PURCHASE_PLAN_ORDER, $data);
        $send   = $wms_adapter->getSendParams();
        $resv   = $wms_adapter->getReceived();

        logx("outer no:$outer_no  send:    ".print_r($send,true),$sid.'/WMS_CMD');
        logx("outer no:$outer_no  receive: ".print_r($resv,true),$sid.'/WMS_CMD');
        logx("outer no:$outer_no  result:  ".print_r($result,true),$sid.'/WMS_CMD');

        $code = $result['code'];
        // $code = 0;
        $error_msg = $result['error_msg'];
        if(mb_strlen($error_msg,'utf-8') > 200)//如果超过长度则截取
        {
            $error_msg = mb_substr($error_msg,0,200,"utf-8");
        }
        $rev_info = isset($result['rev_info'])?$result['rev_info']:'';

        if($code != 0)//失败
        {
            if($code<0)
            {
                logx("outer_no:$outer_no, 系统级别错误system_error:$error_msg! wms_adapter_add_plan_po", $sid.'/WMS_CMD');

            }
            else
            {
                logx("outer_no:$outer_no, 应用级别错误app_error:$error_msg! wms_adapter_add_plan_po", $sid.'/WMS_CMD');

            }
            ackError("WMS返回采购计划单推送失败:$error_msg");
        }
        else
        {
            $order_log = '推送外部WMS采购退货单成功';
            if(mb_strlen($rev_info,'utf-8') > 40)//单号超长
            {
                $rev_info = '';
                $order_log = '推送外部WMS采购计划单成功,WMS返回单号超长,请联系旺店通技术';
                logx("outer_no:$outer_no, WMS返回单号超长! wms_adapter_add_plan_po",$sid.'/WMS_CMD');
            }

            //待出库
            if(!$db->execute("UPDATE purchase_stockin_plan SET status=48 ,wms_status=1, outer_no='{$outer_no}',wms_outer_no = '{$rev_info}',error_info='' where stockin_plan_id = $po_id"))
            {
                $error_msg = $db->error_msg();
                logx("outer no:$outer_no, Update status failed:$error_msg! wms_adapter_add_plan_po", $sid.'/WMS_CMD');
                $db->execute("UPDATE purchase_stockin_plan SET status=45,wms_status=3,error_info='{$error_msg}' where stockin_plan_id = $po_id");
                releaseDb($db);
                ackError("推送采购计划单失败:单据已推送成功,系统内单据状态处理失败");
            }
            else
            {
                $db->execute("insert into purchase_stockin_plan_log(stockin_plan_id,operator_id,type,remark) values($po_id,$uid,9,'{$order_log}')");
                logx("outer no:$outer_no, Push success! wms_adapter_add_plan_po", $sid.'/WMS_CMD');
                releaseDb($db);
                ackOk(0);
            }
        }

    }

    //取消采购计划单
    function wms_adapter_cancel_plan_po($sid, $uid, $po_id)
    {
        $db = getUserDb($sid);
        if(!$db)
        {
            logx("trade_id:$po_id, GetUserDb failed! wms_adapter_cancel_plan_po", $sid.'/WMS_CMD');
            ackError('取消采购计划单失败:服务器内部错误');
            return false;
        }

        //单据信息获取验证
        $po_plan_info = $db->query_result("select psp.stockin_plan_no,psp.warehouse_id,psp.outer_no,psp.wms_outer_no,psp.wms_status,po.purchase_id,po.purchase_no,psp.status,psp.provider_id,psp.logistics_id,psp.logistics_no, ".
            " psp.created, sw.ext_warehouse_no, sw.type as wms_type, sw.api_object_id,sw.api_key, sw.name as warehouse_name,po.status as po_status ".
            " from purchase_stockin_plan psp ".
            " left join sys_warehouse sw on psp.warehouse_id = sw.warehouse_id ".
            " left join purchase_order po on po.purchase_id = psp.purchase_id ".
            " where psp.stockin_plan_id = $po_id");
        if (!$po_plan_info)
        {
            logx("trade_id:$po_id, Order not exist!! wms_adapter_cancel_plan_po",$sid.'/WMS_CMD');
            releaseDb($db);
            ackError('取消采购计划单失败:获取采购计划单信息失败');
            return false;
        }

        $outer_no = $po_plan_info['outer_no'];

        if ($po_plan_info['status'] != 48)
        {
            logx("outer_no:$outer_no, Order status is wrong!! wms_adapter_cancel_plan_po",$sid.'/WMS_CMD');
            releaseDb($db);
            ackError('取消采购计划单失败:采购计划单状态非待入库');
            return false;
        }

        if ($po_plan_info['wms_status'] == 3)
        {
            if($db->execute("SET @cur_uid={$uid}") && $result = $db->query("CALL SP_PURCHASE_STOCKIN_PLAN_REJECTCHECK_EX('{$po_id}',0)"))
            {
                //有返回失败信息，处理失败
                if($row = $db->fetch_array($result))
                {
                    $error_msg = $row['error'];
                    logx("outer_no:$outer_no, Call SP_PURCHASE_STOCKIN_PLAN_REJECTCHECK_EX failed:$error_msg! wms_adapter_cancel_plan_po",$sid.'/WMS_CMD');
                    $db->free_result($result);
                    $db->query("update purchase_stockin_plan set wms_status=3,error_info='取消操作执行失败:$error_msg' where stockin_plan_id={$po_id}");
                    ackError("取消采购计划单失败:$error_msg");
                }
                //没有返回失败信息，处理成功
                else
                {
                    $db->free_result($result);
                    if(!$db->query("UPDATE purchase_stockin_plan SET wms_status=0,error_info = '' where stockin_plan_id= {$po_id}"))
                    {
                        $error_msg = $db->error_msg();
                        logx("outer_no:$outer_no, Update wms_status failed:$error_msg! wms_adapter_cancel_plan_po", $sid.'/WMS_CMD');
                        ackError("取消采购计划单失败:服务器处理异常,请重试");
                    }
                    else
                    {
                        $db->execute("INSERT INTO purchase_stockin_plan_log(stockin_plan_id,operator_id,type,remark) VALUES({$po_id},$uid,300,'取消外部WMS采购计划单成功')");
                        logx("outer_no:$outer_no, Cancel success! wms_adapter_cancel_plan_po", $sid.'/WMS_CMD');
                        ackOk(0);
                    }
                }
            }
            else
            {
                $error_msg = $db->error_msg();
                logx("outer_no:$outer_no, Call SP_PURCHASE_ORDER_UNCHECK failed:$error_msg! wms_adapter_cancel_po",$sid.'/WMS_CMD');
                $db->query("update purchase_order set wms_status=3,error_info='取消操作执行失败' where purchase_id={$purchase_info['purchase_id']}");
                ackError("取消采购单失败:服务器处理异常,请重试");
            }
        }

        $wms_info = json_decode($po_plan_info['api_key'],true);
        $wms_type = $po_plan_info['wms_type'];

        $data['purchase'] = $po_plan_info;
        $wms_adapter = new WmsAdapter($wms_type, $wms_info);

        logx("start to send requset,outer no:$outer_no  stockin_plan_id:$po_id ",$sid.'/WMS_CMD');
        $result = $wms_adapter->sendRequest(WMS_METHOD_PURCHASE_PLAN_CANCEL, $data);
        $send   = $wms_adapter->getSendParams();
        $resv   = $wms_adapter->getReceived();

        logx("outer no:$outer_no  send:    ".print_r($send,true),$sid.'/WMS_CMD');
        logx("outer no:$outer_no  receive: ".print_r($resv,true),$sid.'/WMS_CMD');
        logx("outer no:$outer_no  result:  ".print_r($result,true),$sid.'/WMS_CMD');

        $code      = $result['code'];
        $error_msg = $result['error_msg'];
        if(mb_strlen($error_msg,'utf-8') > 200)//如果超过长度则截取
        {
            $error_msg = mb_substr($error_msg,0,200,"utf-8");
        }

        if($code != 0)  //失败
        {
            if($code<0)
            {
                logx("outer_no:$outer_no, 系统级别错误system_error:$error_msg! wms_adapter_cancel_plan_po", $sid.'/WMS_CMD');
            }
            else
            {
                logx("outer_no:$outer_no, 应用级别错误app_error:$error_msg! in wms_adapter_cancel_plan_po", $sid.'/WMS_CMD');
            }
            ackError("WMS返回取消失败:$error_msg");
        }
        else  //成功
        {
            if($db->execute("SET @cur_uid={$uid}") && $result = $db->query("CALL SP_PURCHASE_STOCKIN_PLAN_REJECTCHECK_EX('{$po_id}',0)"))
            {
                //有返回失败信息，处理失败
                if($row = $db->fetch_array($result))
                {
                    $error_msg = $row['error'];
                    logx("outer_no:$outer_no, Call SP_PURCHASE_STOCKIN_PLAN_REJECTCHECK_EX failed:$error_msg! wms_adapter_cancel_plan_po",$sid.'/WMS_CMD');
                    $db->free_result($result);
                    $db->query("update purchase_stockin_plan set wms_status=3,error_info='取消操作执行失败:$error_msg' where stockin_plan_id={$po_id}");
                    ackError("取消采购计划单失败:$error_msg");
                }
                //没有返回失败信息，处理成功
                else
                {
                    $db->free_result($result);
                    if(!$db->query("UPDATE purchase_stockin_plan SET wms_status=0,error_info = '' where stockin_plan_id= {$po_id}"))
                    {
                        $error_msg = $db->error_msg();
                        logx("outer_no:$outer_no, Update wms_status failed:$error_msg! wms_adapter_cancel_plan_po", $sid.'/WMS_CMD');
                        ackError("取消采购计划单失败:服务器处理异常,请重试");
                    }
                    else
                    {
                        $db->execute("INSERT INTO purchase_stockin_plan_log(stockin_plan_id,operator_id,type,remark) VALUES({$po_id},$uid,300,'取消外部WMS采购计划单成功')");
                        logx("outer_no:$outer_no, Cancel success! wms_adapter_cancel_plan_po", $sid.'/WMS_CMD');
                        ackOk(0);
                    }
                }
            }
            else
            {
                $error_msg = $db->error_msg();
                logx("outer_no:$outer_no, Call SP_PURCHASE_ORDER_UNCHECK failed:$error_msg! wms_adapter_cancel_po",$sid.'/WMS_CMD');
                $db->query("update purchase_order set wms_status=3,error_info='取消操作执行失败' where purchase_id={$purchase_info['purchase_id']}");
                ackError("取消采购单失败:服务器处理异常,请重试");
            }
        }
    }

    //推送采购退货单
    function wms_adapter_add_po_refund($sid, $uid, $return_id)
    {
        //连接卖家数据库
        $db = getUserDb($sid);
        if(!$db)
        {
            logx("return id:$return_id, GetUserDb failed! wms_adapter_add_po_refund",$sid.'/WMS_CMD');
            ackError('推送采购退货单失败:服务器内部错误');

            return false;
        }

        //获取采购出库单信息
        $purchase_return_info = $db->query_result(" select clw.logistics_code, pr.logistics_type, pr.status, pr.return_id, pr.return_no,pr.warehouse_id, pr.modified, sw.api_key,sw.ext_warehouse_no, ".
            " sw.type, pr.province, pr.city, pr.district, pr.wms_status, pr.outer_no, pr.receive_address, pr.created, pr.remark, ".
            " pr.contact, pr.telno, sw.is_defect, sw.api_object_id, sw.match_warehouse_id, pr.provider_id, pp.provider_no, pp.provider_name, cl.send_type ".
            " from purchase_return pr ".
            " left join sys_warehouse sw on pr.warehouse_id = sw.warehouse_id ".
            " left join cfg_logistics cl on cl.logistics_id = pr.logistics_type ".
            " left join cfg_logistics_wms clw on (clw.logistics_id=pr.logistics_type and clw.warehouse_id=pr.warehouse_id) ".
            " left join purchase_provider pp on pr.provider_id=pp.provider_id ".
            " where pr.return_id={$return_id}");
        if(!$purchase_return_info)
        {
            logx("return id:$return_id, Order not exists! wms_adapter_add_po_refund",$sid.'/WMS_CMD');
            releaseDb($db);
            ackError('推送采购退货单失败:获取采购退货单信息失败');

            return false;
        }

        //单据状态校验，42待推送，44推送失败
        if ($purchase_return_info['status'] != 42 && $purchase_return_info['status'] != 44)
        {
            logx("return id:$return_id, Status[{$purchase_return_info['status']}] error! wms_adapter_add_po_refund",$sid.'/WMS_CMD');
            releaseDb($db);
            ackError('采购退货单状态已变更，请刷新页面');

            return false;
        }

        //外部编号(推送失败且有外部单号不重新生成)
        if($purchase_return_info['status'] == 42 || ($purchase_return_info['status'] == 44 && $purchase_return_info['outer_no'] == ''))
        {
            $outer_no = $db->query_result_single("SELECT FN_SYS_NO('outer_no')", '');
            if (empty($outer_no))
            {
                logx("return id:$return_id, FN_SYS_NO('outer_no')  failed! wms_adapter_add_po_refund", $sid.'/WMS_CMD');
                releaseDb($db);
                ackError("推送采购退货单失败:获取外部单号失败");

                return false;
            }
            // 外部单号以 OCT开头   采退
            $outer_no = 'OCT' . $outer_no;
        }
        else
        {
            $outer_no = $purchase_return_info['outer_no'];
        }
        $purchase_return_info['outer_no'] = $outer_no;


        //省市区判断
        if($purchase_return_info['province'] == '' || $purchase_return_info['city'] == '')
        {
            logx("outer no:$outer_no, No receiver province or city information! wms_adapter_add_po_refund", $sid.'/WMS_CMD');
            releaseDb($db);
            ackError('推送采购退货单失败:没有收货人省市信息');

            return false;
        }

        //目前只支持奇门/顺丰
        if ($purchase_return_info['type'] != 11 && $purchase_return_info['type'] != 9)
        {
            logx("outer no:$outer_no, WMS[{$purchase_return_info['type']}] didn't support! wms_adapter_add_po_refund",$sid.'/WMS_CMD');
            $db->query("UPDATE purchase_return SET status=40, wms_status=1, error_info='该委外仓暂不支持采购退货!' where return_id = $return_id ");
            releaseDb($db);
            ackError('该委外仓暂不支持采购退货,请按内部流程处理');

            return false;
        }

        //残次品仓判断,如果为残品仓且匹配了正品仓则取对应正品仓的授权信息（向下兼容）
        $purchase_return_info['inventory_type'] = 0;
        if ($purchase_return_info['is_defect'] == 1 && array_key_exists('match_warehouse_id',$purchase_return_info) )
        {
            $match_warehouse_info = $db->query_result("select api_key,api_object_id from sys_warehouse where match_warehouse_id = {$purchase_return_info['warehouse_id']} ");
            if ($match_warehouse_info)
            {
                $purchase_return_info['api_key'] = $match_warehouse_info['api_key'];
                $purchase_return_info['api_object_id'] 	= $match_warehouse_info['api_object_id'];
                $purchase_return_info['inventory_type'] = 1;
            }
        }

        //并发控制
        $wms_status = $purchase_return_info['wms_status'];
        if(!$db->execute("UPDATE purchase_return SET wms_status = 4 WHERE return_id = $return_id AND modified = '{$purchase_return_info['modified']}'"))
        {
            $error_msg = $db->error_msg();
            logx("outer no:$outer_no, Update wms_status failed:$error_msg! wms_adapter_add_po_refund", $sid.'/WMS_CMD');
            releaseDb($db);
            ackError("推送采购退货单失败:更新状态失败");

            return false;
        }
        $affect_rows = mysqli_affected_rows($db->link_id);
        if ($affect_rows == 0)
        {
            logx("outer no:$outer_no, Order is pushing! wms_adapter_add_po_refund",$sid.'/WMS_CMD');
            releaseDb($db);
            ackError('采购退货单已在推送中,请稍后刷新页面');

            return false;
        }

        $data['otherOut'] = $purchase_return_info;
        $wms_info = json_decode($purchase_return_info['api_key'],true);
        $wms_type = $purchase_return_info['type'];

        //获取采购退货单货品详细信息
        $goods_info = $db->query(" select  prd.rec_id,gg.goods_no,gg.goods_name,gs.spec_no,gs.barcode,prd.num,ss.spec_wh_no2 ".
            " from purchase_return_detail prd ".
            " left join goods_spec gs on gs.spec_id = prd.spec_id ".
            " left join goods_goods gg on gs.goods_id = gg.goods_id ".
            " left join stock_spec ss on ss.spec_id = prd.spec_id ".
            " where prd.return_id = $return_id and ss.warehouse_id = {$purchase_return_info['warehouse_id']} ");
        if (!$goods_info)
        {
            $error_msg = $db->error_msg();
            logx("outer no:$outer_no, Get goods info failed:$error_msg! wms_adapter_add_po_refund", $sid.'/WMS_CMD');
            $db->execute("UPDATE purchase_return SET wms_status = $wms_status where return_id = $return_id ");
            releaseDb($db);
            ackError('推送采购退货单失败:获取货品信息失败');

            return false;
        }

        if ($goods_info->num_rows == 0)
        {
            logx("outer no:$outer_no, Goods not exists! wms_adapter_add_po_refund", $sid.'/WMS_CMD');
            $db->execute("UPDATE purchase_return SET wms_status = $wms_status  where return_id = $return_id ");
            releaseDb($db);
            ackError('推送采购退货单失败:货品明细为空');

            return false;
        }

        while($row = $db->fetch_array($goods_info))
        {
            $data['details'][] = $row;
        }

        //推送
        $wms_adapter   = new WmsAdapter($wms_type, $wms_info);
        $wms_tmp_type  = isset($wms_info['wms_type'])?$wms_info['wms_type']:'';
        $purchase_type = WMS_METHOD_PURCHASE_RETURN_ADD;

        $error_info = '';
        $error = $wms_adapter->getTransferFlag($wms_type, $wms_tmp_type, $purchase_type,$error_info);
        if($error == 0)
        {
            logx("outer no:$outer_no, wms[$wms_tmp_type] didn't support! wms_adapter_add_po_refund",$sid.'/WMS_CMD');
            $db->query("UPDATE purchase_return SET status=40, wms_status=1, error_info='{$error_info}' where return_id = $return_id ");
            releaseDb($db);
            ackError("委外仓[$wms_tmp_type]暂不支持采购退货,请按内部流程处理");

            return false;
        }

        logx("start to send requset,outer no:$outer_no  return id:$return_id ",$sid.'/WMS_CMD');
        $result = $wms_adapter->sendRequest(WMS_METHOD_PURCHASE_RETURN_ADD, $data, $sid);
        $send   = $wms_adapter->getSendParams();
        $resv   = $wms_adapter->getReceived();
        logx("outer no:$outer_no  send:    ".print_r($send,true),$sid.'/WMS_CMD');
        logx("outer no:$outer_no  receive: ".print_r($resv,true),$sid.'/WMS_CMD');
        logx("outer no:$outer_no  result:  ".print_r($result,true),$sid.'/WMS_CMD');

        $code      = $result['code'];
        $error_msg = $result['error_msg'];
        if(mb_strlen($error_msg,'utf-8') > 200)//如果超过长度则截取
        {
            $error_msg = mb_substr($error_msg,0,200,"utf-8");
        }
        $error_msg = $db->escape_string($error_msg);
        $rev_info = isset($result['rev_info'])?$result['rev_info']:'';//更新回传回来的仓库订单编码

        //反馈信息解析
        if($code != 0)
        {
            if($code<0)
            {
                logx("outer no:$outer_no, 系统级别错误system_error: {$error_msg}! wms_adapter_add_po_refund", $sid.'/WMS_CMD');
                $db->execute("UPDATE purchase_return SET status=44,wms_status = 1,outer_no = '{$outer_no}',error_info= 'WMS返回信息:{$error_msg}' where return_id = $return_id ");
            }
            else
            {
                logx("outer no:$outer_no, 应用级别错误app_error: {$error_msg}! wms_adapter_add_po_refund", $sid.'/WMS_CMD');
                $db->execute("UPDATE purchase_return SET status=44,wms_status = 1,outer_no = '{$outer_no}',error_info= 'WMS返回信息:{$error_msg}' where return_id = $return_id ");
            }
            ackError("WMS返回推送失败：{$error_msg}");
        }
        else
        {
            $order_log = '推送外部WMS采购退货单成功';
            if(mb_strlen($rev_info,'utf-8') > 40)//单号超长
            {
                $rev_info = '';
                $order_log = '推送外部WMS采购退货单成功,WMS返回单号超长,请联系旺店通技术';
                logx("outer_no:$outer_no, WMS返回单号超长! wms_adapter_add_po_refund",$sid.'/WMS_CMD');
            }

            //待出库
            if(!$db->execute("UPDATE purchase_return SET wms_status=2,error_info='',status=46,outer_no='{$outer_no}',wms_outer_no = '{$rev_info}' where return_id = $return_id"))
            {
                $error_msg = $db->error_msg();
                logx("outer no:$outer_no, Update status failed:$error_msg! wms_adapter_add_po_refund", $sid.'/WMS_CMD');
                ackError("推送采购退货单失败:单据已推送成功,系统内单据状态处理失败");
            }
            else
            {
                $db->execute("insert into purchase_return_log(return_id,operator_id,type,remark) values($return_id,$uid,13,'{$order_log}')");
                logx("outer no:$outer_no, Push success! wms_adapter_add_po_refund", $sid.'/WMS_CMD');

                ackOk(0);
            }
        }
        releaseDb($db);
        exit(0);
    }

    function wms_adapter_stockout_status($sid, $uid, $stockout_id)
    {
        //连接卖家数据库
        $db = getUserDb($sid);
        if(!$db)
        {
            logx("getUserDb failed in wms_adapter_stockout_status!!", $sid.'/WMS_CMD');
            ackError('服务器内部错误');
            return;
        }

        $data = array();
        $stockout_info = $db->query_result("select so.stockout_id,so.stockout_no, so.outer_no, so.src_order_id, so.status, sw.api_object_id, sw.ext_warehouse_no, sw.type as wms_type, sw.warehouse_id, sw.api_key ".
            "from stockout_order so ".
            "left join sys_warehouse sw using(warehouse_id) ".
            "where so.stockout_id = {$stockout_id}");

        if(!$stockout_info)
        {
            releaseDb($db);
            //logx("wms_adapter_stockout_status stockout not exists, {$stockout_id}!!", $sid);
            ackError('没有获取到订单信息');
            return;
        }
        //只有奇门的才支持，做判断 其他类型返回失败
        if($stockout_info['wms_type'] != 11)
        {
            releaseDb($db);
            ackError('目前只支持奇门仓库，请选择奇门仓库');
            return;
        }

        //$stockout_detail_list = $db->query();

        //查出库单号, wms单号, 仓库信息
        //$stockout_no = $stockout_info['stockout_no'];  //出库单号
        //$wmsOrder_no = $stockout_info['outer_no'];
        $wms_type = $stockout_info['wms_type'];

        $wms_info = json_decode($stockout_info['api_key'],true);

        $data['stockout'] = $stockout_info;

        //使用适配器 new
        $wms_adapter = new WmsAdapter($wms_type, $wms_info);

        //retval
        $result = $wms_adapter->sendRequest(WMS_METHOD_STOCKOUT_STATUS, $data, $sid);
        $send   = $wms_adapter->getSendParams();
        $resv   = $wms_adapter->getReceived();

        logx("the erp stockout_no is {$stockout_info['stockout_no']} "." send:    ".print_r($send,true),$sid.'/WMS_CMD');
        logx("receive: ".print_r($resv,true),$sid.'/WMS_CMD');
        //logx("receive: ".print_r($resv,true),$sid);

        $code = $result['code'];
        $error_msg = $result['error_msg'];
        if(mb_strlen($error_msg,'utf-8') > 200)//如果超过长度则截取
        {
            $error_msg = mb_substr($error_msg,0,200,"utf-8");
        }

        $rev_info = isset($result['rev_info'])?$result['rev_info']:'';

        if(mb_strlen($rev_info,'utf-8') > 200)//如果超过长度则截取
        {
            $rev_info = mb_substr($rev_info, 0, 200, "utf-8");
        }

        //失败 ，弹窗
        if($code != 0)
        {
            if($code<0)
            {
                logx("stockout_id: {$stockout_info['stockout_id']}, 系统级别错误system_error: {$error_msg} in wms_adapter_stockout_status", $sid.'/WMS_CMD');
            }
            else
            {
                logx("stockout_id: {$stockout_info['stockout_id']}, 应用级别错误app_error: {$error_msg} in wms_adapter_stockout_status", $sid.'/WMS_CMD');
            }
            ackError("查询出库单仓库流转状态失败：{$error_msg}");
//		exit(0);
        }

        // 成功 第一步， update  推送信息 第二步  ， 弹窗显示接受到并格式化的内容
        else
        {
            if(!$db->query("UPDATE stockout_order SET error_info = '{$rev_info}' where stockout_id= {$stockout_info['stockout_id']}"))
            {
                logx("stockout_id: {$error_msg}, ,error: {$error_msg} when update db failed in wms_adapter_stockout_status", $sid.'/WMS_CMD');
                ackError("查询出库单仓库流转状态失败：{$error_msg}");
                exit(0);
            }
            else
            {

                logx("stockout_id: {$error_msg}, success in wms_adapter_stockout_status", $sid.'/WMS_CMD');
                //成功提示
                ackError("{$error_msg}, 请刷新页面查看");
                //ackOk(0);
            }
        }
        releaseDb($db);
    }

    //采购单停止等待
    function wms_adapter_po_stop_waiting($sid, $uid ,$purchase_id)
    {
        global $g_sid_list;

        //数据库连接
        $db = getUserDb($sid);
        if(!$db)
        {
            logx("purchase_id:$purchase_id, GetUserDb failed! wms_adapter_po_stop_waiting",$sid.'/WMS_CMD');
            ackError('采购单停止等待失败:服务器内部错误');
            return false;
        }

        //获取采购单信息
        $purchase_info = $db->query_result(" select po.purchase_id, po.purchase_no, po.outer_no, po.wms_outer_no,po.wms_status, po.status, po.warehouse_id, po.expect_arrive_time, ".
            " sw.ext_warehouse_no, sw.type as wms_type, sw.api_key, sw.name as warehouse_name, po.receive_address, ".
            " pp.provider_no,pp.provider_name,pp.zip,pp.contact,pp.telno,pp.mobile,pp.address, po.remark,po.created, ".
            " po.modified, po.check_time, po.goods_count,po.goods_type_count, sw.is_defect, sw.api_object_id, sw.zip as warehouse_zip, ".
            " sw.province as warehouse_province, sw.city as warehouse_city, sw.district as warehouse_district, sw.address as warehouse_address, ".
            " sw.contact as warehouse_contact, sw.mobile as warehouse_mobile, sw.telno as warehouse_telno ".
            " from purchase_order po ".
            " left join sys_warehouse sw on sw.warehouse_id = po.warehouse_id ".
            " left join purchase_provider pp on po.provider_id=pp.provider_id ".
            " where po.purchase_id = {$purchase_id}");
        if(!$purchase_info)
        {
            logx("purchase id:$purchase_id, Purchase order not exists! wms_adapter_po_stop_waiting", $sid.'/WMS_CMD');
            releaseDb($db);
            ackError('采购单停止等待失败:获取采购单信息失败');

            return false;
        }

        //检验单据状态
        if($purchase_info['status'] != 50)
        {
            logx("purchase id:$purchase_id, Purchase order not exists! wms_adapter_po_stop_waiting", $sid.'/WMS_CMD');
            releaseDb($db);
            ackError('采购单停止等待失败:状态不是部分到货');

            return false;
        }

        $outer_no = $purchase_info['outer_no'];

        $wms_status = $purchase_info['wms_status'];
        if(!$db->execute("UPDATE purchase_order SET wms_status = 4 WHERE purchase_id = $purchase_id AND modified = '{$purchase_info['modified']}'"))
        {
            $error_msg = $db->error_msg();
            logx("purchase id:$purchase_id, Update wms_status failed:$error_msg! wms_adapter_add_po", $sid.'/WMS_CMD');
            releaseDb($db);
            ackError("委外采购单停止等待失败:更新状态失败");
            return false;
        }

        if ($purchase_info['wms_status'] == 3) //不需要走接口直接更新状态。
        {
            logx("outer_no:$outer_no, Updata success in wms_adapter_po_stop_waiting!", $sid.'/WMS_CMD');
            po_stop_waiting_handle($db, $uid, $sid, $purchase_id);

            releaseDb($db);
            exit(0);
        }

        $wms_info = json_decode($purchase_info['api_key'],true);
        $wms_type = $purchase_info['wms_type'];

        if($wms_type != 11)
        {
            logx("outer_no:$outer_no, the[{$wms_type}]not QIMEN 转内部逻辑! wms_adapter_po_stop_waiting!", $sid.'/WMS_CMD');
            po_stop_waiting_handle($db, $uid, $sid, $purchase_id);

            releaseDb($db);
            exit(0);
        }

        $data['waiting'] = $purchase_info;//封装信息

        $wms_adapter   = new WmsAdapter($wms_type, $wms_info);
        $wms_tmp_type  = isset($wms_info['wms_type'])?$wms_info['wms_type']:'';
        $method = WMS_METHOD_PURCHASE_STOP_WAITING;

        $error_info = '';
        $result = $wms_adapter->getTransferFlag($wms_type, $wms_tmp_type, $method, $error_info);
        if($result == 0)
        {
            logx("outer no:$outer_no, wms[$wms_tmp_type] didn't support, $error_info! wms_adapter_po_stop_waiting",$sid.'/WMS_CMD');

            po_stop_waiting_handle($db, $uid, $sid, $purchase_id);
            releaseDb($db);
            return;
        }

        $api_flag = isset($g_sid_list[$sid])?$g_sid_list[$sid]:'';
        $stopFlag = isset($api_flag['stop_flag'][ORDER_TYPE_STOP_WAITING_PO])?$api_flag['stop_flag'][ORDER_TYPE_STOP_WAITING_PO]:0;

        if($stopFlag == 0)
        {
            po_stop_waiting_handle($db, $uid, $sid, $purchase_id);
            releaseDb($db);
            return;
        }

        logx("start to send requset,outer no:$outer_no  purchase id:$purchase_id ",$sid.'/WMS_CMD');
        $result = $wms_adapter->sendRequest(WMS_METHOD_PURCHASE_STOP_WAITING, $data);
        $send   = $wms_adapter->getSendParams();
        $resv   = $wms_adapter->getReceived();

        logx("outer no:$outer_no  send:    ".print_r($send,true),$sid.'/WMS_CMD');
        logx("outer no:$outer_no  receive: ".print_r($resv,true),$sid.'/WMS_CMD');
        logx("outer no:$outer_no  result:  ".print_r($result,true),$sid.'/WMS_CMD');

        $code = $result['code'];
        // $code = 0;
        $error_msg = $result['error_msg'];
        if(mb_strlen($error_msg,'utf-8') > 200)//如果超过长度则截取
        {
            $error_msg = mb_substr($error_msg,0,200,"utf-8");
        }

        if($code != 0)//失败
        {
            if($code<0)
            {
                logx("outer_no:$outer_no, 系统级别错误system_error:$error_msg! wms_adapter_po_stop_waiting", $sid.'/WMS_CMD');

            }
            else
            {
                logx("outer_no:$outer_no, 应用级别错误app_error:$error_msg! wms_adapter_po_stop_waiting", $sid.'/WMS_CMD');

            }
            ackError("WMS返回停止等待失败:$error_msg");
        }
        else
        {
            po_stop_waiting_handle($db, $uid, $sid, $purchase_id);
        }
        releaseDb($db);
    }

    function po_stop_waiting_handle($db, $uid, $sid, $purchase_id)//为了美观写的一个处理机
    {
        if($db->execute("SET @cur_uid=$uid") && $result = $db->query("CALL SP_PURCHASE_ORDER_STOPWAIT('{$purchase_id}',1)"))
        {
            if ($result->num_rows != 0)
            {
                while($row = $db->fetch_array($result))
                {
                    $error_msg = $row['error'];

                    logx("call SP_PURCHASE_ORDER_STOPWAIT failed  {$error_msg} ",$sid.'/WMS_CMD');
                    ackError('委外采购单停止等待失败:'.$error_msg);
                }
                $db->free_result($result);
                return ;
            }
            $db->free_result($result);
            $db->query("UPDATE purchase_order SET wms_status=0,error_info='' where purchase_id={$purchase_id}");
            logx("purchase_id:$purchase_id, Success in wms_adapter_po_stop_waiting",$sid.'/WMS_CMD');
            ackOk(0);
            return ;
        }
        else
        {
            logx("call SP_PURCHASE_ORDER_STOPWAIT failed!!, purchase_id:$purchase_id error:".$db->error_msg(),$sid.'/WMS_CMD');
            $error_msg  = '服务器异常,请稍后重试';

            logx("purchase_id:$purchase_id, {$error_msg} in wms_adapter_po_stop_waiting",$sid.'/WMS_CMD');
            $db->execute("UPDATE purchase_order SET wms_status=3,error_info={$error_msg} where purchase_id={$purchase_id}");
            ackError('委外采购单停止等待失败');
            return ;
        }
    }

    /*推送采购单*/
    function wms_adapter_add_po($sid, $uid, $purchase_id)
    {
        /*
        //看是否有采购单在推送
        $lock_pid = myTestPid("wms_adapter_add_po",true);
        if(!$lock_pid)
        {
            logx(" wms_adapter_add_po  myTestPid  失败!!", $sid);
            ackError('有采购单正在推送，请稍后刷新界面重试');
            return;
        }
        */

        $db = getUserDb($sid);
        if(!$db)
        {
            logx("purchase id:$purchase_id, GetUserDb failed! wms_adapter_add_po", $sid.'/WMS_CMD');
            ackError('推送采购单失败:服务器内部错误');

            return false;
        }
        $data = array();

        $warehouse_info = $db->query_result(" select sw.* from purchase_order po left join sys_warehouse sw on po.warehouse_id = sw.warehouse_id where po.purchase_id = $purchase_id ");
        if(!$warehouse_info)
        {
            logx("purchase id:$purchase_id, Get warehouse info failed! wms_adapter_add_po", $sid.'/WMS_CMD');
            releaseDb($db);
            ackError('推送采购单失败:获取仓库信息失败');

            return false;
        }

        //残品仓如果被匹配过，则不允许推送采购单，走委外其他入库
        if ($warehouse_info['is_defect'] == 1 && array_key_exists('match_warehouse_id',$warehouse_info) )
        {
            $match_count = $db->query_result_single("select count(1) from sys_warehouse where match_warehouse_id = {$warehouse_info['warehouse_id']} and is_disabled = 0 ");
            if ($match_count > 0 )
            {
                logx("purchase id:$purchase_id, The warehouse is defect and matched,not allow to push! wms_adapter_add_po", $sid.'/WMS_CMD');
                releaseDb($db);
                ackError('残品仓已被正品仓匹配，不允许推送采购单，请使用委外其他入库');

                return false;
            }
            $db->free_result($match_count);
        }
        $db->free_result($warehouse_info);

        $purchase_info = $db->query_result(" select po.purchase_id, po.purchase_no, po.outer_no, po.wms_status, po.status, po.warehouse_id, po.expect_arrive_time, ".
            " sw.ext_warehouse_no, sw.type as wms_type, sw.api_key, sw.name as warehouse_name, po.receive_address, ".
            " pp.provider_no,pp.provider_name,pp.zip,pp.contact,pp.telno,pp.mobile,pp.address, po.remark,po.created, ".
            " po.modified, po.check_time, po.goods_count,po.goods_type_count, sw.is_defect, sw.api_object_id, sw.zip as warehouse_zip, ".
            " sw.province as warehouse_province, sw.city as warehouse_city, sw.district as warehouse_district, sw.address as warehouse_address, ".
            " sw.contact as warehouse_contact, sw.mobile as warehouse_mobile, sw.telno as warehouse_telno ".
            " from purchase_order po ".
            " left join sys_warehouse sw on sw.warehouse_id = po.warehouse_id ".
            " left join purchase_provider pp on po.provider_id=pp.provider_id ".
            " where po.purchase_id = {$purchase_id}");
        if(!$purchase_info)
        {
            logx("purchase id:$purchase_id, Purchase order not exists! wms_adapter_add_po", $sid.'/WMS_CMD');
            releaseDb($db);
            ackError('推送采购单失败:获取采购单信息失败');

            return false;
        }

        if ($purchase_info['status'] != 43 && $purchase_info['status'] != 45)
        {
            logx("purchase id:$purchase_id, Status[{$purchase_info['status']}] is wrong! wms_adapter_add_po",$sid.'/WMS_CMD');
            releaseDb($db);
            ackError('采购单状态已变更,请刷新页面');

            return false;
        }

        //并发控制
        $wms_status = $purchase_info['wms_status'];
        if(!$db->execute("UPDATE purchase_order SET wms_status = 4 WHERE purchase_id = $purchase_id AND modified = '{$purchase_info['modified']}'"))
        {
            $error_msg = $db->error_msg();
            logx("purchase id:$purchase_id, Update wms_status failed:$error_msg! wms_adapter_add_po", $sid.'/WMS_CMD');
            releaseDb($db);
            ackError("推送采购单失败:更新状态失败");

            return false;
        }
        $affect_rows = mysqli_affected_rows($db->link_id);
        if ($affect_rows == 0)
        {
            logx("purchase id:$purchase_id, Order has pushed! wms_adapter_add_po",$sid.'/WMS_CMD');
            releaseDb($db);
            ackError('采购单已在推送中，请稍后刷新页面');

            return false;
        }
        //外部编号(推送失败且有外部单号不重新生成)
        if($purchase_info['status'] == 43 || ($purchase_info['status'] == 45 && $purchase_info['outer_no'] == ''))
        {
            $outer_no = $db->query_result_single("select FN_SYS_NO('outer_no')", '');
            if (empty($outer_no))
            {
                logx("purchase id:$purchase_id, FN_SYS_NO('outer_no') failed! wms_adapter_add_po", $sid.'/WMS_CMD');
                $db->execute("UPDATE purchase_order SET wms_status = $wms_status WHERE purchase_id = $purchase_id ");
                releaseDb($db);
                ackError("推送采购单失败:生成外部单号失败");

                return false;
            }
            $outer_no = 'OCG' . $outer_no;
        }
        else
        {
            $outer_no = $purchase_info['outer_no'];
        }
        $purchase_info['outer_no'] = $outer_no;

        $data['purchase'] = $purchase_info;
        $wms_info = json_decode($purchase_info['api_key'],true);
        $wms_type = $purchase_info['wms_type'];

        $purchase_detail_list = $db->query(" SELECT pod.rec_id,pod.spec_id,pod.price,gs.spec_no,gg.goods_no,gs.spec_name,gg.goods_name,pod.num,pod.remark,IFNULL(ss.spec_wh_no,'') AS spec_wh_no,IFNULL(ss.spec_wh_no2,'') AS spec_wh_no2".
            " FROM purchase_order_detail pod ".
            " LEFT JOIN goods_spec gs ON gs.spec_id=pod.spec_id ".
            " LEFT JOIN goods_goods gg ON gg.goods_id = gs.goods_id ".
            " LEFT JOIN stock_spec ss on ss.spec_id = pod.spec_id AND ss.warehouse_id = {$purchase_info['warehouse_id']} ".
            " WHERE pod.purchase_id= $purchase_id ");

        if (!$purchase_detail_list)
        {
            $error_msg = $db->error_msg();
            logx("outer no:$outer_no, Get goods info failed:$error_msg! wms_adapter_add_po", $sid.'/WMS_CMD');
            $db->execute("UPDATE purchase_order SET wms_status = $wms_status WHERE purchase_id = $purchase_id ");
            releaseDb($db);
            ackError("推送采购单失败:获取货品信息失败");

            return false;
        }
        if ($purchase_detail_list->num_rows == 0)
        {
            logx("outer no:$outer_no, Goods not exists! wms_adapter_add_po", $sid.'/WMS_CMD');
            $db->execute("UPDATE purchase_order SET wms_status = $wms_status WHERE purchase_id = $purchase_id ");
            releaseDb($db);
            ackError('推送采购单失败:货品明细为空');

            return false;
        }

        while($row = $db->fetch_array($purchase_detail_list))
        {
            //力威没有货品上传接口，不做判断
            if($wms_type != 14)
            {
                if ($row['spec_wh_no'] == '')
                {
                    logx( "outer no:$outer_no,Spec[{$row['spec_no']}] doesn't exist in wms! wms_adapter_add_po", $sid.'/WMS_CMD' );
                    $db->execute( "UPDATE purchase_order SET wms_status = {$purchase_info['wms_status']} WHERE purchase_id = $purchase_id " );
                    releaseDb($db);
                    ackError("推送采购单失败:商品[{$row['spec_no']}]未上传过WMS,请先上传货品到WMS后再推送");

                    return false;
                }
            }
            //心怡仓储要求传商品的平台编码用于海关校验
            if ($wms_type == 13)
            {
                $api_goods_info     = xy_get_api_info($db,'api_goods_info',$row['spec_id']);
                $row['api_spec_no'] = isset($api_goods_info['spec_outer_id'])?$api_goods_info['spec_outer_id']:'';
            }
            if($wms_type == 6 && $sid == 'lyf2')
            {
                //360sku 来伊份货品编码问题，需要用货品编号作为唯一标识
                $row['spec_no'] = $row['goods_no'];
                $data['details'][] = $row;
            }
            else
            {
                $data['details'][] = $row;
            }
        }

        $wms_adapter = new WmsAdapter($wms_type, $wms_info);

        logx("start to send requset,outer no:$outer_no  purchase id:$purchase_id ",$sid.'/WMS_CMD');
        $result = $wms_adapter->sendRequest(WMS_METHOD_PURCHASE_ADD, $data, $sid);
        $send   = $wms_adapter->getSendParams();
        $resv   = $wms_adapter->getReceived();

        logx("outer no:$outer_no  send:    ".print_r($send,true),$sid.'/WMS_CMD');
        logx("outer no:$outer_no  receive: ".print_r($resv,true),$sid.'/WMS_CMD');
        logx("outer no:$outer_no  result:  ".print_r($result,true),$sid.'/WMS_CMD');

        $code = $result['code'];
        $error_msg = $result['error_msg'];
        if(mb_strlen($error_msg,'utf-8') > 200)//如果超过长度则截取
        {
            $error_msg = mb_substr($error_msg,0,200,"utf-8");
        }
        $rev_info = isset($result['rev_info'])?$result['rev_info']:'';//更新回传回来的仓库订单编码

        if($code != 0)
        {
            if($code<0)
            {
                logx("outer_no:$outer_no, 系统级别错误system_error:$error_msg! wms_adapter_add_po", $sid.'/WMS_CMD');
                $db->query("UPDATE purchase_order SET status=45,wms_status=1,outer_no =%s, error_info=%s where purchase_id=%d",$outer_no ,'WMS返回信息:'.$error_msg, (int)$purchase_info['purchase_id']);
            }
            else
            {
                logx("outer_no:$outer_no, 应用级别错误app_error:$error_msg! wms_adapter_add_po", $sid.'/WMS_CMD');
                $db->query("UPDATE purchase_order SET status=45,wms_status=1,outer_no =%s, error_info=%s where purchase_id=%d",$outer_no, 'WMS返回信息:'.$error_msg, (int)$purchase_info['purchase_id']);
            }
            ackError("WMS返回推送失败:$error_msg");
        }
        else
        {
            //待收货

            //处理异步wms
            if($purchase_info['wms_type'] == 5)//百世wms,先把wms_status置为0，等百世回传成功接收再置为2
            {
                if(!$db->query("UPDATE purchase_order SET wms_status=0,error_info = '',status = 48,outer_no='{$outer_no}' , wms_outer_no= '{$rev_info}' where purchase_id= {$purchase_info['purchase_id']}"))
                {
                    $error_msg = $db->error_msg();
                    logx("outer_no:$outer_no, Update db failed:$error_msg! wms_adapter_add_po", $sid.'/WMS_CMD');
                    ackError("推送采购单失败:WMS已收到单据申请,系统内单据状态处理失败");
                }
                else
                {
                    $db->execute("INSERT INTO purchase_order_log(purchase_id,operator_id,type,remark) VALUES({$purchase_info['purchase_id']},$uid,300,'推送外部WMS采购单申请成功')");
                    logx("outer_no:$outer_no, Push success! wms_adapter_add_po", $sid.'/WMS_CMD');
                    ackOk(0);
                }
            }
            else//同步反馈
            {
                $order_log = '推送外部WMS采购单成功';
                if(mb_strlen($rev_info,'utf-8') > 40)//单号超长
                {
                    $rev_info = '';
                    $order_log = '推送外部WMS采购单成功,WMS返回单号超长,请联系旺店通技术';
                    logx("outer_no:$outer_no, WMS返回单号超长! wms_adapter_add_po",$sid.'/WMS_CMD');
                }

                if(!$db->query("UPDATE purchase_order SET wms_status=2,error_info = '',status = 48,outer_no='{$outer_no}' , wms_outer_no= '{$rev_info}' where purchase_id= {$purchase_info['purchase_id']}"))
                {
                    $error_msg = $db->error_msg();
                    logx("outer_no:$outer_no, Update db failed:$error_msg! wms_adapter_add_po", $sid.'/WMS_CMD');
                    ackError("推送采购单失败:单据已推送成功,系统内单据状态处理失败");
                }
                else
                {
                    $db->execute("INSERT INTO purchase_order_log(purchase_id,operator_id,type,remark) VALUES({$purchase_info['purchase_id']},$uid,300,'{$order_log}')");
                    logx("outer_no:$outer_no, Push success! wms_adapter_add_po", $sid.'/WMS_CMD');
                    ackOk(0);
                }

            }

        }
        releaseDb($db);

        exit(0);
    }

    /*取消采购单*/
    function wms_adapter_cancel_po($sid, $uid, $purchase_id)
    {
        //连接卖家数据库
        $db = getUserDb($sid);
        if(!$db)
        {
            logx("purchase id:$purchase_id, GetUserDb failed! wms_adapter_cancel_po", $sid.'/WMS_CMD');
            ackError('取消采购单失败:服务器内部错误');

            return false;
        }

        //获取符合条件的采购单
        $purchase_info = $db->query_result("select po.purchase_id, po.status, po.outer_no , po.warehouse_id , po.wms_outer_no, sw.is_defect, sw.api_object_id, sw.type as wms_type, sw.api_key, sw.ext_warehouse_no, po.purchase_no, po.wms_status,  ".
            "pp.provider_no,pp.provider_name,pp.telno,pp.mobile,pp.address, po.remark,po.created,po.modified,po.expect_arrive_time ".
            "from purchase_order po ".
            "left join sys_warehouse sw on po.warehouse_id=sw.warehouse_id ".
            "left join purchase_provider pp on po.provider_id = pp.provider_id ".
            "where po.purchase_id = {$purchase_id}");
        if(!$purchase_info)
        {
            logx("purchase_id:$purchase_id, Order not exists! wms_adapter_cancel_po",$sid.'/WMS_CMD');
            releaseDb($db);
            ackError('取消采购单失败:没有获取到采购单信息');

            return false;
        }
        $outer_no = $purchase_info['outer_no'];

        if ($purchase_info['status'] != 48)
        {
            logx("purchase_id:$purchase_id, Status[{$purchase_info['status']}] error! wms_adapter_cancel_po",$sid.'/WMS_CMD');
            releaseDb($db);
            ackError('采购单状态已变更，请刷新页面');

            return false;
        }

        if($purchase_info['wms_status'] == 4)//已推送取消申请，用于异步回传
        {
            releaseDb($db);
            ackError("取消采购单申请已成功推送到仓库，请稍后刷新页面查看");

            return false;
        }

        /*
        $db->execute(" UPDATE purchase_order SET wms_status = 4 WHERE purchase_id = $purchase_id AND modified = '{$purchase_info['modified']}'");
        $affect_rows = mysqli_affected_rows($db->link_id);
        if ($affect_rows == 0)
        {
            releaseDb($db);
            logx("wms_adapter_cancel_po order has pushed,{$purchase_id}!!",$sid);
            ackError('取消采购单已推送！请刷新页面');
            return;
        }
        */
        $wms_info = json_decode($purchase_info['api_key'],true);
        $wms_type = $purchase_info['wms_type'];

        if($purchase_info['wms_status'] == 3 || ($wms_type == 9 && isset($wms_info['access_code']) && empty($wms_info['access_code'])))//对方已经成功取消，只是调用存储过程失败或者顺丰不走OMS取消入库单接口
        {
            if($db->execute("SET @cur_uid={$uid}") && $result = $db->query("CALL SP_PURCHASE_ORDER_UNCHECK('{$purchase_info['purchase_id']}',0)"))
            {
                //有返回失败信息，处理失败
                if($row = $db->fetch_array($result))
                {
                    $error_msg = $row['error'];
                    logx("outer_no:$outer_no, Call SP_PURCHASE_ORDER_UNCHECK failed:$error_msg! wms_adapter_cancel_po",$sid.'/WMS_CMD');
                    $db->free_result($result);
                    $db->query("update purchase_order set wms_status=3,error_info='取消操作执行失败:$error_msg' where purchase_id={$purchase_info['purchase_id']}");
                    ackError("取消采购单失败:$error_msg");
                }
                //没有返回失败信息，处理成功
                else
                {
                    $db->free_result($result);
                    if(!$db->query("UPDATE purchase_order SET wms_status=0,error_info = '' where purchase_id= {$purchase_info['purchase_id']}"))
                    {
                        $error_msg = $db->error_msg();
                        logx("outer_no:$outer_no, Update wms_status failed:$error_msg! wms_adapter_cancel_po", $sid.'/WMS_CMD');
                        ackError("取消采购单失败:服务器处理异常,请重试");
                    }
                    else
                    {
                        $db->execute("INSERT INTO purchase_order_log(purchase_id,operator_id,type,remark) VALUES({$purchase_info['purchase_id']},$uid,300,'取消外部WMS采购单成功')");
                        logx("outer_no:$outer_no, Cancel success! wms_adapter_cancel_po", $sid.'/WMS_CMD');
                        if($purchase_info['wms_type'] == 9)
                        {
                            ackError("取消成功,请及时通知仓储取消该单据！");
                        }
                        else
                        {
                            ackOk(0);
                        }
                    }
                }
            }
            else
            {
                $error_msg = $db->error_msg();
                logx("outer_no:$outer_no, Call SP_PURCHASE_ORDER_UNCHECK failed:$error_msg! wms_adapter_cancel_po",$sid.'/WMS_CMD');
                $db->query("update purchase_order set wms_status=3,error_info='取消操作执行失败' where purchase_id={$purchase_info['purchase_id']}");
                ackError("取消采购单失败:服务器处理异常,请重试");
            }
            releaseDb($db);
            exit(0);
        }

        //转送到适配器，进行数据分析
        $data['purchase'] = $purchase_info;
        $wms_adapter = new WmsAdapter($wms_type, $wms_info);

        logx("start to send requset,outer no:$outer_no  purchase id:$purchase_id ",$sid.'/WMS_CMD');
        $result = $wms_adapter->sendRequest(WMS_METHOD_PURCHASE_CANCEL, $data, $sid);
        $send   = $wms_adapter->getSendParams();
        $resv   = $wms_adapter->getReceived();

        logx("outer no:$outer_no  send:    ".print_r($send,true),$sid.'/WMS_CMD');
        logx("outer no:$outer_no  receive: ".print_r($resv,true),$sid.'/WMS_CMD');
        logx("outer no:$outer_no  result:  ".print_r($result,true),$sid.'/WMS_CMD');

        $code      = $result['code'];
        $error_msg = $result['error_msg'];
        if(mb_strlen($error_msg,'utf-8') > 200)//如果超过长度则截取
        {
            $error_msg = mb_substr($error_msg,0,200,"utf-8");
        }

        if($code != 0)  //失败
        {
            if($code<0)
            {
                logx("outer_no:$outer_no, 系统级别错误system_error:$error_msg! wms_adapter_cancel_po", $sid.'/WMS_CMD');
            }
            else
            {
                logx("outer_no:$outer_no, 应用级别错误app_error:$error_msg! in wms_adapter_cancel_po", $sid.'/WMS_CMD');
            }
            ackError("WMS返回取消失败:$error_msg");
        }
        else  //成功
        {

            //处理异步
            if($purchase_info['wms_type'] == 5) //百世，先把wms_status置为4，然后等待取消结果，再返回
            {
                if(!$db->query("UPDATE purchase_order SET wms_status=4,error_info = '' where purchase_id= {$purchase_info['purchase_id']}"))
                {
                    $error_info = $db->error_msg();
                    logx("outer_no:$outer_no, Update db failed:$error_info! wms_adapter_cancel_po", $sid.'/WMS_CMD');
                    ackError("取消采购单失败:服务器处理异常,请重试");
                }
                else//推送成功
                {
                    $db->execute("INSERT INTO purchase_order_log(purchase_id,operator_id,type,remark) VALUES({$purchase_info['purchase_id']},$uid,300,'取消外部wms采购单申请已提交')");
                    logx("outer_no:$outer_no, Push cancel request  success! wms_adapter_cancel_po", $sid.'/WMS_CMD');

                    //延时等待
                    $error_info = '';
                    $query_status_sql = "select wms_status from purchase_order where purchase_id={$purchase_info['purchase_id']}";
                    $query_info_sql = "select error_info from purchase_order where purchase_id={$purchase_info['purchase_id']}";
                    $result = delayCancelOrder($db,$query_status_sql,$query_info_sql,$error_info);

                    //收到百世取消成功的消息
                    if($result)
                    {
                        logx("outer_no:$outer_no, Cancel success! wms_adapter_cancel_po", $sid.'/WMS_CMD');
                        ackOk(0);
                    }
                    else
                    {
                        ackError("取消采购单失败:$error_info");
                    }

                }
            }
            else
            {
                if($db->execute("SET @cur_uid={$uid}") && $result = $db->query("CALL SP_PURCHASE_ORDER_UNCHECK('{$purchase_info['purchase_id']}',0)"))
                {
                    //有返回失败信息，处理失败
                    if($row = $db->fetch_array($result))
                    {
                        $error_msg = $row['error'];
                        logx("outer_no:$outer_no, Call SP_PURCHASE_ORDER_UNCHECK failed:$error_msg! wms_adapter_cancel_po",$sid.'/WMS_CMD');
                        $db->free_result($result);
                        $db->query("update purchase_order set wms_status=3,error_info='取消操作执行失败:$error_msg' where purchase_id={$purchase_info['purchase_id']}");
                        ackError("取消采购单失败:$error_msg");
                    }
                    //没有返回失败信息，处理成功
                    else
                    {
                        $db->free_result($result);
                        if(!$db->query("UPDATE purchase_order SET wms_status=0,error_info = '' where purchase_id= {$purchase_info['purchase_id']}"))
                        {
                            $error_msg = $db->error_msg();
                            logx("outer_no:$outer_no, Update wms_status failed:$error_msg! wms_adapter_cancel_po", $sid.'/WMS_CMD');
                            ackError("取消采购单失败:服务器处理异常,请重试");
                        }
                        else
                        {
                            $db->execute("INSERT INTO purchase_order_log(purchase_id,operator_id,type,remark) VALUES({$purchase_info['purchase_id']},$uid,300,'取消外部WMS采购单成功')");
                            logx("outer_no:$outer_no, Cancel success! wms_adapter_cancel_po", $sid.'/WMS_CMD');
                            ackOk(0);
                        }
                    }
                }
                else
                {
                    $error_msg = $db->error_msg();
                    logx("outer_no:$outer_no, Call SP_PURCHASE_ORDER_UNCHECK failed:$error_msg! wms_adapter_cancel_po",$sid.'/WMS_CMD');
                    $db->query("update purchase_order set wms_status=3,error_info='取消操作执行失败' where purchase_id={$purchase_info['purchase_id']}");
                    ackError("取消采购单失败:服务器处理异常,请重试");
                }
            }

        }
        releaseDb($db);

        exit(0);
    }

    //取消采购退货单
    function wms_adapter_cancel_po_return($sid, $uid, $return_id)
    {
        $db = getUserDb($sid);//连数据库
        if(!$db)
        {
            logx("return id:$return_id, GetUserDb failed! wms_adapter_cancel_po_return", $sid.'/WMS_CMD');
            ackError('取消采购退货单失败:服务器内部错误');

            return false;
        }

        $purchase_return_info = $db->query_result("select por.return_id, por.status, por.outer_no , por.warehouse_id , por.wms_outer_no, sw.is_defect, sw.api_object_id, sw.type as wms_type, sw.match_warehouse_id, sw.api_key, sw.ext_warehouse_no, por.return_no, por.provider_id, por.logistics_type, por.wms_status,  ".
            "pp.provider_no,pp.provider_name,pp.telno,pp.mobile,pp.address, por.remark,por.created,por.modified ".
            "from purchase_return por ".
            "left join sys_warehouse sw on por.warehouse_id=sw.warehouse_id ".
            "left join purchase_provider pp on por.return_id = pp.provider_id ".
            "where por.return_id = {$return_id}");
        if(!$purchase_return_info)
        {

            logx("return_id:$return_id, Order not exists! wms_adapter_cancel_po_return",$sid.'/WMS_CMD');
            releaseDb($db);
            ackError('取消采购退货单失败:没有获取到采购退货单信息');

            return false;
        }
        $outer_no = $purchase_return_info['outer_no'];

        //首先判断是否具备取消条件
        if ($purchase_return_info['status'] != 46)// 46  才允许取消 对仓库取消，对WDT是反审核操作
        {
            logx("outer_no:$outer_no, Status[{$purchase_return_info['status']}] error! wms_adapter_cancel_po_return",$sid.'/WMS_CMD');
            releaseDb($db);
            ackError('采购退货单状态已变更，请刷新页面');

            return false;
        }

        //判断采购退货单内的东西是不是次品，然后找对应仓库为了给WMS对应回传
        if ($purchase_return_info['is_defect'] == 1 && array_key_exists('match_warehouse_id',$purchase_return_info) )
        {
            $match_warehouse_info = $db->query_result("select api_key,api_object_id from sys_warehouse where match_warehouse_id = {$purchase_return_info['warehouse_id']} ");
            if ($match_warehouse_info)
            {
                $purchase_return_info['api_key']        = $match_warehouse_info['api_key'];
                $purchase_return_info['api_object_id'] 	= $match_warehouse_info['api_object_id'];
            }
        }

        if($purchase_return_info['wms_status'] == 3)//已经成功取消可是存储过程调用失败
        {
            if($db->execute("BEGIN") && $db->execute("SET @cur_uid={$uid} ") && $db->query("CALL I_PURCHASE_RETURN_UNCHECK('{$return_id}',1)"))
            {
                $db->execute("update purchase_return set wms_status=0,error_info='' where return_id={$return_id}");
                $db->execute("INSERT INTO purchase_return_log(return_id,operator_id,type,remark) VALUES({$return_id},$uid,14,'取消采购退货单成功')");
                $db->execute("COMMIT");
                logx("outer_no:$outer_no, Cancel success! wms_adapter_cancel_po_return", $sid.'/WMS_CMD');
                ackOk(0);
            }
            else
            {
                $error_msg = $db->error_msg();
                logx("outer_no:$outer_no, Call I_PURCHASE_RETURN_UNCHECK failed:$error_msg! wms_adapter_cancel_po_return",$sid.'/WMS_CMD');
                $db->execute("ROLLBACK");
                $db->query("update purchase_return set wms_status=3,error_info='取消操作执行失败' where return_id={$purchase_return_info['return_id']}");
                ackError("取消采购退货单失败:服务器处理异常,请重试");
            }
            releaseDb($db);
            exit(0);
        }

        $data['return'] = $purchase_return_info;
        $wms_info = json_decode($purchase_return_info['api_key'],true);
        $wms_type = $purchase_return_info['wms_type'];

        $wms_adapter = new WmsAdapter($wms_type, $wms_info);

        logx("start to send requset,outer no:$outer_no  return id:$return_id ",$sid.'/WMS_CMD');
        $result = $wms_adapter->sendRequest(WMS_METHOD_PURCHASE_RETURN_CANCEL, $data, $sid);
        $send   = $wms_adapter->getSendParams();
        $resv   = $wms_adapter->getReceived();

        logx("outer no:$outer_no  send:     ".print_r($send,true),$sid.'/WMS_CMD');
        logx("outer no:$outer_no  receive:  ".print_r($resv,true),$sid.'/WMS_CMD');
        logx("outer no:$outer_no  result:   ".print_r($result,true),$sid.'/WMS_CMD');

        $code = $result['code'];
        $error_msg = $result['error_msg'];
        if(mb_strlen($error_msg,'utf-8') > 200)//如果超过长度则截取
        {
            $error_msg = mb_substr($error_msg,0,200,"utf-8");
        }

        //判断是否成功
        if($code != 0)
        {
            if($code<0)
            {
                logx("outer_no:$outer_no, 系统级别错误system_error:$error_msg! wms_adapter_cancel_po_return", $sid.'/WMS_CMD');
            }
            else
            {
                logx("outer_no:$outer_no, 应用级别错误app_error:$error_msg! wms_adapter_cancel_po_return", $sid.'/WMS_CMD');
            }
            ackError("WMS返回取消失败:$error_msg");
        }
        else//成功
        {
            if($db->execute("BEGIN") && $db->execute("SET @cur_uid={$uid} ") && $db->query("CALL I_PURCHASE_RETURN_UNCHECK('{$return_id}',1)"))
            {
                $db->execute("update purchase_return set wms_status=0,error_info='' where return_id={$return_id}");
                $db->execute("INSERT INTO purchase_return_log(return_id,operator_id,type,remark) VALUES({$return_id},$uid,14,'取消外部WMS采购退货单成功')");
                $db->execute("COMMIT");
                logx("outer_no:$outer_no, Cancel success! wms_adapter_cancel_po_return", $sid.'/WMS_CMD');
                ackOk(0);
            }
            else
            {
                $error_msg = $db->error_msg();
                logx("outer_no:$outer_no, Call I_PURCHASE_RETURN_UNCHECK failed:$error_msg! wms_adapter_cancel_po_return",$sid.'/WMS_CMD');
                $db->execute("ROLLBACK");
                $db->query("update purchase_return set wms_status=3,error_info='取消操作执行失败' where return_id={$purchase_return_info['return_id']}");
                ackError("取消采购退货单失败:单据已取消成功,系统内单据状态处理失败");
            }
        }
        releaseDb($db);
        exit(0);
    }

    //延时处理机制
    function delayCancelOrder(&$db,$query_status_sql,$query_info_sql,&$error_msg)
    {
        //先等5秒,异步一般会在5秒以后返回结果
        sleep(5);
        //阻塞30秒,等待取消返回信息,如果没有得到取消结果则弹窗提示正在处理
        $wms_status = 0;
        for($i=0; $i<20; $i++)
        {
            $wms_status = (int)$db->query_result_single($query_status_sql,0);
            if($wms_status === 4)
            {
                sleep(1);
                continue;
            }
            else
                break;
        }
        if($wms_status == 4)
        {
            $error_msg = '取消请求已经成功提交给仓库,仓库没有返回取消结果,请稍后刷新界面查看结果!';
            return false;
        }
        else if($wms_status == 1 || $wms_status==3)//取消失败
        {
            $error_msg = $db->query_result_single($query_info_sql,'');
            return false;
        }
        else if($wms_status == 0)
        {
            return true;
        }

        $error_msg = '处理异常';//除非代码写的有问题,一般不会走到这里
        return false;
    }

    //推送调拨出库单
    function wms_adapter_add_transfer_out($sid, $uid, $transfer_id)
    {
        $db = getUserDb($sid);
        if(!$db)
        {
            logx("transfer id:$transfer_id, GetUserDb failed! wms_adapter_add_transfer_out", $sid.'/WMS_CMD');
            ackError('推送调拨出库单失败:服务器内部错误');

            return false;
        }
        $data = array();

        $warehouse_info = $db->query("select sw.*,if(st.from_warehouse_id = sw.warehouse_id,1,0) as out_flag from stock_transfer st left join sys_warehouse sw on st.from_warehouse_id = sw.warehouse_id or st.to_warehouse_id = sw.warehouse_id where st.rec_id = $transfer_id ");
        if($warehouse_info->num_rows == 0 )
        {
            logx("transfer_id:$transfer_id, Get warehouse info failed! wms_adapter_add_transfer_out", $sid.'/WMS_CMD');
            releasedb($db);
            ackerror('推送调拨出库单失败:没有获取到仓库信息');

            return false;
        }

        //残品仓如果被匹配过，则不允许推送调拨出库单，走委外其他出库(同时校验出库仓库和入库仓库)
        while($row = $db->fetch_array($warehouse_info))
        {
            if ($row['type'] > 1 && $row['type'] != 127 && $row['is_defect'] == 1 && array_key_exists('match_warehouse_id', $row))
            {
                $match_count = $db->query_result_single("select count(1) from sys_warehouse where match_warehouse_id = {$row['warehouse_id']} and is_disabled = 0 ");
                if ($match_count > 0)
                {
                    if ($row['out_flag'] == 1)
                    {
                        logx("transfer_id:$transfer_id, The from_warehouse is defect and matched,not allow to push! wms_adapter_add_transfer_out", $sid.'/WMS_CMD');
                        ackError('推送调拨出库单失败:调出仓已被正品仓匹配，不允许推送调拨出库单，请使用委外其他出库');
                    }
                    else
                    {
                        logx("transfer_id:$transfer_id, The to_warehouse is defect and matched,not allow to push! wms_adapter_add_transfer_out", $sid.'/WMS_CMD');
                        ackError('推送调拨出库单失败:调入仓已被正品仓匹配，不允许推送调拨出库单，请使用委外其他出库');
                    }
                    releaseDb($db);

                    return false;
                }
                $db->free_result($match_count);
            }
        }
        $db->free_result($warehouse_info);

        $transfer_info = $db->query_result(" select st.rec_id,st.from_warehouse_id , st.wms_status, st.status, st.transfer_no, st.outer_no, st.contact, st.telno, st.logistics_no, st.modified, st.created, st.remark, ".
            " sw2.name AS to_warehouse_name, sw2.province,sw2.address, sw2.contact, sw2.city, sw2.district, sw2.zip, sw2.mobile, clw.logistics_code, cl.send_type, ".
            " sw1.is_defect AS from_is_defect,sw1.api_object_id AS from_api_object_id,sw1.api_key AS from_api_key,sw1.ext_warehouse_no AS from_ext_warehouse_no,sw1.type AS from_warehouse_type, ".
            " sw2.is_defect AS to_is_defect,sw2.api_object_id AS to_api_object_id,sw2.api_key AS to_api_key,sw2.ext_warehouse_no AS to_ext_warehouse_no,  sw2.type AS to_warehouse_type ".
            " from stock_transfer st ".
            " left join cfg_logistics cl using(logistics_id) ".
            " left join cfg_logistics_wms clw on (clw.logistics_id=st.logistics_id and clw.warehouse_id = st.from_warehouse_id) ".
            " left join sys_warehouse sw1 on sw1.warehouse_id = st.from_warehouse_id ".
            " left join sys_warehouse sw2 on sw2.warehouse_id = st.to_warehouse_id ".
            " where st.rec_id={$transfer_id}");

        if(!$transfer_info)
        {
            logx("transfer_id:$transfer_id, Transfer not exists! wms_adapter_add_transfer_out", $sid.'/WMS_CMD');
            releaseDb($db);
            ackError('推送调拨出库单失败:没有获取到调拨单信息');

            return false;
        }

        if ($transfer_info['status'] != 42 && $transfer_info['status'] != 44)
        {
            logx("transfer_id:$transfer_id, Status[{$transfer_info['status']}] error! wms_adapter_add_transfer_out",$sid.'/WMS_CMD');
            releaseDb($db);
            ackError('调拨单状态已变更，请刷新页面');

            return false;
        }

        //委外仓库类型判断,目前支持sku360(6)、顺丰(9)、奇门(11)
        if($transfer_info['from_warehouse_type'] != 6 && $transfer_info['from_warehouse_type'] != 9 && $transfer_info['from_warehouse_type'] != 11)
        {
            logx("transfer_id:$transfer_id, Warehouse[{$transfer_info['from_warehouse_type']}] not support! wms_adapter_add_transfer_out", $sid.'/WMS_CMD');
            releaseDb($db);
            ackError('推送调拨出库单失败:调出仓库委外暂不支持调拨');

            return false;
        }

        //并发处理：同一调拨单同时只能执行一个推送调拨单操作
        if(!$db->execute("UPDATE stock_transfer SET wms_status = 4 WHERE rec_id = $transfer_id AND modified = '{$transfer_info['modified']}'"))
        {
            $error_msg = $db->error_msg();
            logx("transfer_id: $transfer_id, Update wms_status failed:$error_msg! wms_adapter_add_transfer_out", $sid.'/WMS_CMD');
            releaseDb($db);
            ackError("推送调拨出库单失败:更新单据状态失败");

            return false;
        }
        $affect_rows = mysqli_affected_rows($db->link_id);
        if ($affect_rows == 0)
        {
            logx("transfer_id:$transfer_id, Order is pushing! wms_adapter_add_transfer_out",$sid.'/WMS_CMD');
            releaseDb($db);
            ackError('调拨出库单已在推送中,请稍后刷新页面');

            return false;
        }

        //外部编号(推送失败且有单号不重新生成)
        if($transfer_info['status'] == 42 || ($transfer_info['status'] = 44 && $transfer_info['outer_no'] == ''))
        {
            $outer_no = $db->query_result_single("select FN_SYS_NO('outer_no')", '');
            if (empty($outer_no))
            {
                logx("transfer_id:$transfer_id, FN_SYS_NO('outer_no') failed! wms_adapter_add_transfer_out", $sid.'/WMS_CMD');
                $db->query("UPDATE stock_transfer SET wms_status= {$transfer_info['wms_status']} where rec_id=$transfer_id ");
                releaseDb($db);
                ackError("推送调拨出库单失败:生成外部单号失败");

                return false;
            }
            $outer_no = 'ODB' . $outer_no;
        }
        else
        {
            $outer_no = $transfer_info['outer_no'];
        }
        $transfer_info['outer_no'] = $outer_no;
        $data['transfer'] = $transfer_info;

        //委外调委外，是否相同仓库类型判断
        $transfer_type = WMS_METHOD_TRANSFEROUT_DIVERSE_ADD;
        $data['transfer']['plan_flag'] = 0;//不同货主

        if ($transfer_info['from_warehouse_type'] == $transfer_info['to_warehouse_type'])
        {
            //奇门
            if($transfer_info['from_warehouse_type'] == 11)
            {
                $from_wms_info = json_decode($transfer_info['from_api_key'], true);
                $to_wms_info   = json_decode($transfer_info['to_api_key'], true);

                if(!isset($from_wms_info['wms_type']) || !isset($to_wms_info['wms_type']) || $from_wms_info['wms_type'] == '' || $to_wms_info['wms_type'] == '' )
                {
                    $error_msg = '奇门仓储授权信息需更新';
                    logx("outer_no:$outer_no, 奇门仓储授权信息需更新，没有wms_type字段! wms_adapter_add_transfer_out",$sid.'/WMS_CMD');
                    $db->query("UPDATE stock_transfer SET status=44,wms_status=1,error_info='{$error_msg}' where rec_id=$transfer_id ");
                    releaseDb($db);
                    ackError('推送调拨出库单失败:仓库授权信息有误，请联系旺店通售后升级，并按照最新文档填写相关信息');

                    return false;
                }
                if($from_wms_info['wms_type'] == $to_wms_info['wms_type'])//相同的仓储类型
                {
                    if($from_wms_info['customerId'] == $to_wms_info['customerId'] && $from_wms_info['sub_customerId'] == $to_wms_info['sub_customerId'])
                    {
                        $data['transfer']['plan_flag'] = 1;
                    }
                    else
                    {
                        $data['transfer']['plan_flag'] = 0;
                    }
                    $transfer_type = WMS_METHOD_TRANSFEROUT_SAME_ADD;
                }
                else
                {
                    $data['transfer']['plan_flag'] = 0;//不同货主
                    $transfer_type = WMS_METHOD_TRANSFEROUT_DIVERSE_ADD;
                }
            }
            else
            {
                $data['transfer']['plan_flag'] = 1;
                $transfer_type = WMS_METHOD_TRANSFEROUT_SAME_ADD;
            }

        }

        $wms_info = json_decode($transfer_info['from_api_key'], true);
        $wms_type = $transfer_info['from_warehouse_type'];

        $transfer_detail_list = $db->query(" SELECT std.rec_id,std.spec_id, std.num,std.remark,gs.spec_no,gs.spec_name,gg.goods_no,gg.goods_name,IFNULL(ss.spec_wh_no2,'') as spec_wh_no2 ".
            " FROM stock_transfer_detail std".
            " LEFT JOIN goods_spec gs ON gs.spec_id=std.spec_id ".
            " LEFT JOIN goods_goods gg ON gg.goods_id = gs.goods_id ".
            " LEFT JOIN stock_spec ss ON (ss.spec_id = std.spec_id and ss.warehouse_id = {$transfer_info['from_warehouse_id']}) ".
            " WHERE std.transfer_id= $transfer_id ");
        if (!$transfer_detail_list)
        {
            $error_msg = $db->error_msg();
            logx("outer_no:$outer_no, Get goods info failed:$error_msg! wms_adapter_add_transfer_out", $sid.'/WMS_CMD');
            $db->query("UPDATE stock_transfer SET status=44,wms_status=1,error_info='获取货品明细失败' where rec_id=$transfer_id ");
            releaseDb($db);
            ackError('推送调拨出库单失败:获取调拨单货品明细失败');

            return false;
        }
        if ($transfer_detail_list->num_rows == 0)
        {
            logx("outer_no:$outer_no, Goods not exists! wms_adapter_add_transfer_out", $sid.'/WMS_CMD');
            $db->query("UPDATE stock_transfer SET status=44,wms_status=1,error_info='货品明细为空' where rec_id=$transfer_id ");
            releaseDb($db);
            ackError('推送调拨出库单失败:调拨单货品明细为空');

            return false;
        }

        while($row = $db->fetch_array($transfer_detail_list))
        {
            if($wms_type == 6 && $sid == 'lyf2')
            {
                $row['spec_no'] = $row['goods_no'];
            }
            $data['details'][] = $row;
        }

        $wms_adapter = new WmsAdapter($wms_type, $wms_info);

        $wms_tmp_type = isset($wms_info['wms_type'])?$wms_info['wms_type']:'';
        $error_info = '';
        $result = $wms_adapter->getTransferFlag($wms_type, $wms_tmp_type, $transfer_type,$error_info);
        if($result == 0)
        {
            logx("outer_no:$outer_no, GetTransferFlag failed! wms_adapter_add_transfer_out", $sid.'/WMS_CMD');
            $db->query("UPDATE stock_transfer SET status=44,wms_status=1,error_info='{$error_info}' where rec_id=$transfer_id ");
            releaseDb($db);
            ackError("推送调拨出库单失败:$error_info");

            return false;
        }

        logx("start to send requset,outer no:$outer_no  transfer id:$transfer_id ",$sid.'/WMS_CMD');
        $result = $wms_adapter->sendRequest($transfer_type, $data, $sid);
        $send   = $wms_adapter->getSendParams();
        $resv   = $wms_adapter->getReceived();

        logx("outer no:$outer_no  send:     ".print_r($send,true),$sid.'/WMS_CMD');
        logx("outer no:$outer_no  receive:  ".print_r($resv,true),$sid.'/WMS_CMD');
        logx("outer no:$outer_no  result:   ".print_r($result,true),$sid.'/WMS_CMD');

        $code      = $result['code'];
        $error_msg = $result['error_msg'];

        $rev_info = isset($result['rev_info'])?$result['rev_info']:'';//更新回传回来的仓库单据编码
        if(mb_strlen($error_msg,'utf-8') > 200)//如果超过长度则截取
        {
            $error_msg = mb_substr($error_msg,0,200,"utf-8");
        }
        $error_msg = $db->escape_string($error_msg);

        if($code != 0)
        {
            if($code<0)
            {
                logx("outer_no:$outer_no, 系统级别错误system_error:$error_msg! wms_adapter_add_transfer_out", $sid.'/WMS_CMD');
                $db->query("UPDATE stock_transfer SET status=44,wms_status=1,outer_no = '{$outer_no}',error_info='WMS返回信息:{$error_msg}' where rec_id=$transfer_id ");
            }
            else
            {
                logx("outer_no:$outer_no, 应用级别错误app_error:$error_msg! wms_adapter_add_transfer_out", $sid.'/WMS_CMD');
                $db->query("UPDATE stock_transfer SET status=44,wms_status=1,outer_no = '{$outer_no}',error_info='WMS返回信息:{$error_msg}' where rec_id=$transfer_id ");
            }
            ackError("WMS返回推送失败:$error_msg");
        }
        else
        {
            $order_log = '推送外部WMS调拨出库单成功';
            if(mb_strlen($rev_info,'utf-8') > 40)//单号超长
            {
                $rev_info = '';
                $order_log = '推送外部WMS调拨出库单成功,WMS返回单号超长,请联系旺店通技术';
                logx("outer_no:$outer_no, WMS返回单号超长! wms_adapter_add_transfer_out",$sid.'/WMS_CMD');
            }

            //待出库
            if(!$db->query("UPDATE stock_transfer SET wms_status=2,error_info = '',status = 46,outer_no='{$outer_no}',from_wms_order_no = '{$rev_info}' where rec_id = $transfer_id"))
            {
                $error_msg = $db->error_msg();
                logx("outer_no:$outer_no, Update status failed:$error_msg! wms_adapter_add_transfer_out", $sid.'/WMS_CMD');
                releaseDb($db);
                ackError("推送调拨出库单失败:单据已推送成功,系统内单据状态处理失败");

                return false;
            }
            else
            {
                $db->execute("INSERT INTO stock_inout_log(order_type,order_id,operator_id,operate_type,message) VALUES(3,$transfer_id,$uid,70,'{$order_log}')");
                logx("outer_no:$outer_no, Push success! wms_adapter_add_transfer_out", $sid.'/WMS_CMD');
                //sku360调拨出库不回传出库信息，推送成功即认为已全部出库
                if ($transfer_info['from_warehouse_type'] == 6 )
                {
                    $order = array();
                    $order[0] = array(
                        'order_no'    => $transfer_info['outer_no'],
                        'order_type'  => ORDER_TYPE_TRANSFEROUT,
                        'status'      => STATUS_FINISH,
                        'status_name' => '发货完成',
                        'remark'      => ''
                    );

                    $details = array();
                    $Items = $transfer_detail_list;
                    foreach ($Items as $item)
                    {
                        $details[] = array(
                            'order_no' => $transfer_info['outer_no'],
                            'spec_no'  => $item['spec_no'],
                            'num'      => $item['num'],
                            'price'    => ''
                        );
                    }
                    $error = array('code' =>0, 'msg'=>'');
                    wms_update_order_status($db, $order, $details, $error);

                    //调用存储过程崩溃（死锁等）重试机制
                    $retryCount = 0;
                    while ($error['code'] == 99 && $retryCount < 2)
                    {
                        $error['code'] = 0;
                        $error['msg'] = '';
                        $delay = rand(0, 300) * 10000; //us
                        usleep($delay);
                        logx($transfer_info['outer_no'] . " has retry! retryCount: " . ++$retryCount . ", delay:" . $delay,$sid.'/WMS_CMD');
                        wms_update_order_status($db, $order, $details, $error);
                    }
                    if ($error['code'] != 0)
                    {
                        logx("outer_no:$outer_no, Failed wms_update_order_status:{$error['msg']}! wms_adapter_add_transfer_out",$sid.'/WMS_CMD');
                        $db->execute("UPDATE stock_transfer SET status=46,wms_status=0,error_info='系统自动出库失败:{$error['msg']},请人工创建出库单出库' where rec_id= $transfer_id ");
                        ackError("系统自动出库失败:{$error['msg']},请人工创建出库单出库");
                        releaseDb($db);

                        return false;
                    }
                }
                ackOk(0);
            }
        }
        releaseDb($db);
        exit(0);
    }

    /*取消调拨出库单*/
    function wms_adapter_cancel_transfer_out($sid, $uid, $transfer_id)
    {
        //连接卖家数据库
        $db = getUserDb($sid);
        if(!$db)
        {
            logx("transfer id:$transfer_id, GetUserDb failed! wms_adapter_cancel_transfer_out", $sid.'/WMS_CMD');
            ackError('取消调拨出库单失败:服务器内部错误');

            return false;
        }

        //获取符合条件的调拨单
        $transfer_info = $db->query_result(" select st.rec_id,st.from_wms_order_no, st.transfer_no, st.outer_no, st.status, st.wms_status, st.remark, st.modified,".
            " sw1.is_defect AS from_is_defect,sw1.api_object_id AS from_api_object_id,sw1.api_key AS from_api_key,sw1.ext_warehouse_no AS from_ext_warehouse_no,sw1.type AS from_warehouse_type, ".
            " sw2.is_defect AS to_is_defect,sw2.api_object_id AS to_api_object_id,sw2.api_key AS to_api_key,sw2.ext_warehouse_no   AS to_ext_warehouse_no,  sw2.type AS to_warehouse_type ".
            " from stock_transfer st ".
            " left join sys_warehouse sw1 on sw1.warehouse_id = st.from_warehouse_id ".
            " left join sys_warehouse sw2 on sw2.warehouse_id = st.to_warehouse_id ".
            " where st.rec_id={$transfer_id}");

        if(!$transfer_info)
        {
            logx("transfer_id:$transfer_id, Order not exists! wms_adapter_cancel_transfer_out",$sid.'/WMS_CMD');
            releaseDb($db);
            ackError('取消调拨出库单失败:没有获取到调拨单信息');

            return false;
        }
        $outer_no = $transfer_info['outer_no'];

        //只能取消状态为待出库的调拨单
        if ($transfer_info['status'] != 46)
        {
            logx("outer_no:$outer_no, Status[{$transfer_info['status']}] error! wms_adapter_cancel_transfer_out",$sid.'/WMS_CMD');
            releaseDb($db);
            ackError('调拨单状态已变更，请刷新页面');

            return false;
        }

        //判断是否有相关出库单，如果有的话则不能撤销委外调拨单，需要先取消相关出库单
        $stockout_order_num = $db->query_result_single("SELECT COUNT(1)  FROM stockout_order WHERE src_order_type=2  AND `status`<>5 AND src_order_id = $transfer_id ");
        if ($stockout_order_num >0)
        {
            logx("outer_no:$outer_no, Exist stockout order! wms_adapter_cancel_transfer_out",$sid.'/WMS_CMD');
            releaseDb($db);
            ackError('取消调拨出库单失败:调拨单存在有效出库单,请取消相关出库单后再操作');

            return false;
        }
        //委外仓库类型判断,目前支持顺丰(9)、奇门(11)
        if ($transfer_info['from_warehouse_type'] != 9 && $transfer_info['from_warehouse_type'] != 11)
        {
            logx("outer_no:$outer_no, Warehouse[{$transfer_info['from_warehouse_type']}] dosen't support cancel transfer! wms_adapter_cancel_transfer_out", $sid.'/WMS_CMD');
            releaseDb($db);
            ackError('取消调拨出库单失败:调出仓库委外暂不支持取消调拨');

            return false;
        }

        //并发处理：同一调拨单同时只能执行一个取消调拨操作
        $db->execute(" UPDATE stock_transfer SET wms_status = 4 WHERE rec_id = $transfer_id AND modified = '{$transfer_info['modified']}'");
        $affect_rows = mysqli_affected_rows($db->link_id);
        if ($affect_rows == 0)
        {
            logx("outer_no:$outer_no, Order is pushing! wms_adapter_cancel_transfer_out",$sid.'/WMS_CMD');
            releaseDb($db);
            ackError('取消调拨出库单已推送!请稍后刷新页面');

            return false;
        }

        if($transfer_info['wms_status'] == 3)//对方已经成功取消，只是调用存储过程失败;
        {
            if($db->execute("SET @cur_uid={$uid}") && $result = $db->query("CALL I_STOCK_TRANSFER_REVERT($transfer_id,1)"))
            {
                if(!$db->query("UPDATE stock_transfer SET wms_status=0,error_info = '' where rec_id=$transfer_id "))
                {
                    $error_msg = $db->error_msg();
                    logx("outer_no:$outer_no, Update db failed:$error_msg! wms_adapter_cancel_transfer_out", $sid.'/WMS_CMD');
                    ackError("取消调拨出库单失败:服务器处理异常,请稍后重试");
                }
                else
                {
                    $db->execute("INSERT INTO stock_inout_log(order_type,order_id,operator_id,operate_type,message) VALUES(3,$transfer_id,$uid,76,'取消外部WMS调拨出库单成功')");
                    logx("outer_no:$outer_no, Cancel success! wms_adapter_cancel_transfer_out", $sid.'/WMS_CMD');
                    ackOk(0);
                }
            }
            else
            {
                $error_msg = $db->error_msg();
                logx("outer_no:$outer_no, Call I_STOCK_TRANSFER_REVERT failed:$error_msg! wms_adapter_cancel_transfer_out",$sid.'/WMS_CMD');
                $db->query("update stock_transfer set wms_status=3,error_info='取消操作执行失败' where rec_id=$transfer_id ");
                ackError("取消调拨出库单失败:服务器处理异常,请重试");
            }
            releaseDb($db);
            exit(0);
        }

        $data['transfer'] = $transfer_info;

        //转送到适配器，进行数据分析
        $transfer_type = WMS_METHOD_TRANSFEROUT_CANCEL;

        if ($transfer_info['from_warehouse_type'] == $transfer_info['to_warehouse_type'])
        {
            //奇门
            if($transfer_info['from_warehouse_type'] == 11)
            {
                $from_wms_info = json_decode($transfer_info['from_api_key'], true);
                $to_wms_info   = json_decode($transfer_info['to_api_key'], true);
                if( !isset($from_wms_info['wms_type']) || !isset($to_wms_info['wms_type']) ||empty($from_wms_info['wms_type']) || empty($to_wms_info['wms_type']))
                {
                    logx("outer_no:$outer_no, 奇门仓储授权信息需更新，没有wms_type字段! wms_adapter_add_transfer_out",$sid.'/WMS_CMD');
                    $db->query("UPDATE stock_transfer SET wms_status={$transfer_info['wms_status']} where rec_id=$transfer_id ");
                    releaseDb($db);
                    ackError('取消调拨出库单失败:仓库授权信息有误，请联系旺店通售后升级，并按照最新文档填写相关信息');

                    return false;
                }

                if($from_wms_info['wms_type'] == $to_wms_info['wms_type'])//相同的仓储类型
                {
                    $transfer_type = WMS_METHOD_TRANSFEROUT_CANCEL;
                }
                else
                {
                    $transfer_type = WMS_METHOD_TRANSFEROUT_DIVERSE_CANCEL;
                }
            }
        }
        else if($transfer_info['from_warehouse_type'] == 11)
        {
            $transfer_type = WMS_METHOD_TRANSFEROUT_DIVERSE_CANCEL;
        }

        $wms_info = json_decode($transfer_info['from_api_key'], true);
        $wms_type = $transfer_info['from_warehouse_type'];

        $wms_adapter = new WmsAdapter($wms_type, $wms_info);

        logx("start to send requset,outer no:$outer_no  transfer id:$transfer_id ",$sid.'/WMS_CMD');
        $result = $wms_adapter->sendRequest($transfer_type, $data, $sid);
        $send   = $wms_adapter->getSendParams();
        $resv   = $wms_adapter->getReceived();

        logx("outer no:$outer_no  send:    ".print_r($send,true),$sid.'/WMS_CMD');
        logx("outer no:$outer_no  receive: ".print_r($resv,true),$sid.'/WMS_CMD');
        logx("outer no:$outer_no  result:  ".print_r($result,true),$sid.'/WMS_CMD');

        $code      = $result['code'];
        $error_msg = $result['error_msg'];
        if(mb_strlen($error_msg,'utf-8') > 200)//如果超过长度则截取
        {
            $error_msg = mb_substr($error_msg,0,200,"utf-8");
        }
        $error_msg = $db->escape_string($error_msg);

        if($code != 0)  //失败
        {
            if($code<0)
            {
                logx("outer_no:$outer_no, 系统级别错误system_error:$error_msg! wms_adapter_cancel_transfer_out", $sid.'/WMS_CMD');
            }
            else
            {
                logx("outer_no:$outer_no, 应用级别错误app_error:$error_msg! wms_adapter_cancel_transfer_out", $sid.'/WMS_CMD');
            }
            $db->query("update stock_transfer set wms_status={$transfer_info['wms_status']},error_info='{$error_msg}' where rec_id=$transfer_id ");
            ackError("WMS返回取消失败:$error_msg");
        }
        else  //成功
        {
            if($db->execute("SET @cur_uid={$uid}") && $result = $db->query("CALL I_STOCK_TRANSFER_REVERT($transfer_id,1)"))
            {
                if(!$db->query("UPDATE stock_transfer SET wms_status=0,error_info = '' where rec_id=$transfer_id "))
                {
                    $error_msg = $db->error_msg();
                    logx("outer_no:$outer_no, Update wms_status failed:$error_msg! wms_adapter_cancel_transfer_out", $sid.'/WMS_CMD');
                    ackError("取消调拨出库单失败:单据已取消成功,系统内单据状态处理失败");
                }
                else
                {
                    $db->execute("INSERT INTO stock_inout_log(order_type,order_id,operator_id,operate_type,message) VALUES(3,$transfer_id,$uid,76,'取消外部WMS调拨出库单成功')");
                    logx("outer_no:$outer_no, Cancel success! wms_adapter_cancel_transfer_out", $sid.'/WMS_CMD');
                    ackOk(0);
                }
            }
            else
            {
                $error_msg = $db->error_msg();
                logx("outer_no:$outer_no, Call I_STOCK_TRANSFER_REVERT failed:$error_msg! wms_adapter_cancel_transfer_out",$sid.'/WMS_CMD');
                $db->query("update stock_transfer set wms_status=3,error_info='取消操作执行失败' where rec_id=$transfer_id ");
                ackError("取消调拨出库单失败:服务器处理异常,请重试");
            }
        }
        releaseDb($db);
        exit(0);
    }

    //推送调拨入库单
    function wms_adapter_add_transfer_in($sid, $uid, $transfer_id)
    {
        $db = getUserDb($sid);
        if(!$db)
        {
            logx("transfer id:$transfer_id, GetUserDb failed! wms_adapter_transfer_in", $sid.'/WMS_CMD');
            ackError('推送调拨入库单失败:服务器内部错误');

            return false;
        }
        $data = array();

        $warehouse_info = $db->query_result("select sw.* from stock_transfer st left join sys_warehouse sw on st.to_warehouse_id = sw.warehouse_id where st.rec_id = $transfer_id ");
        if(!$warehouse_info)
        {
            logx("transfer_id:$transfer_id, Get warehouse info failed! wms_adapter_add_transfer_in", $sid.'/WMS_CMD');
            releaseDb($db);
            ackError('推送调拨入库单失败:没有获取到仓库信息');

            return false;
        }

        //残品仓如果被匹配过，则不允许推送调拨入库单，走委外其他出库
        if ($warehouse_info['is_defect'] == 1 && array_key_exists('match_warehouse_id',$warehouse_info) )
        {
            $match_count = $db->query_result_single("select count(1) from sys_warehouse where match_warehouse_id = {$warehouse_info['warehouse_id']} and is_disabled = 0 ");
            if ($match_count > 0 )
            {
                logx("transfer_id:$transfer_id, The warehouse is defect and matched,not allow to push! wms_adapter_add_transfer_in", $sid.'/WMS_CMD');
                releaseDb($db);
                ackError('推送调拨入库单失败:调入仓已被正品仓匹配，不允许推送调拨入库单，请使用委外其他入库');

                return false;
            }
            $db->free_result($match_count);
        }
        $db->free_result($warehouse_info);

        $transfer_info = $db->query_result(" select st.rec_id, st.to_warehouse_id, st.wms_status, st.status, st.transfer_no, st.outer_no2, st.address, st.contact, st.telno, st.logistics_no, st.modified, st.created, st.remark, ".
            " sw2.name AS to_warehouse_name, sw2.province, sw2.city, sw2.district, sw2.zip, sw2.mobile, clw.logistics_code, cl.send_type, ".
            " sw1.is_defect AS from_is_defect,sw1.api_object_id AS from_api_object_id,sw1.api_key AS from_api_key,sw1.ext_warehouse_no AS from_ext_warehouse_no,sw1.type AS from_warehouse_type, ".
            " sw2.is_defect AS to_is_defect,sw2.api_object_id AS to_api_object_id,sw2.api_key AS to_api_key,sw2.ext_warehouse_no   AS to_ext_warehouse_no,  sw2.type AS to_warehouse_type ".
            " from stock_transfer st ".
            " left join cfg_logistics cl using(logistics_id) ".
            " left join cfg_logistics_wms clw on (clw.logistics_id=st.logistics_id and clw.warehouse_id = st.from_warehouse_id) ".
            " left join sys_warehouse sw1 on sw1.warehouse_id = st.from_warehouse_id ".
            " left join sys_warehouse sw2 on sw2.warehouse_id = st.to_warehouse_id ".
            " where st.rec_id={$transfer_id}");

        if(!$transfer_info)
        {
            logx("transfer_id:$transfer_id, Transfer info not exists! wms_adapter_add_transfer_in", $sid.'/WMS_CMD');
            releaseDb($db);
            ackError('推送调拨入库单失败:没有获取到调拨单信息');

            return false;
        }

        if ($transfer_info['status'] != 50 && $transfer_info['status'] != 62 && $transfer_info['status'] != 64)
        {
            logx("transfer_id:$transfer_id, Status[{$transfer_info['status']}] error! wms_adapter_add_transfer_in",$sid.'/WMS_CMD');
            releaseDb($db);
            ackError('调拨单状态已变更，请刷新页面');

            return false;
        }

        //委外仓库类型判断,目前支持sku360(6)、顺丰(9)、奇门(11),如果走进来会导致调拨单卡住，所以在审核调拨单的时候也做了仓库类型限制
        if($transfer_info['to_warehouse_type'] != 6 && $transfer_info['to_warehouse_type'] != 9 && $transfer_info['to_warehouse_type'] != 11)
        {
            logx("transfer_id:$transfer_id, Warehouse[{$transfer_info['to_warehouse_type']}] not support! wms_adapter_transfer_in", $sid.'/WMS_CMD');
            releaseDb($db);
            ackError('推送调拨入库单失败:调入仓库委外暂不支持调拨');

            return false;
        }

        //并发处理：同一调拨单同时只能执行一个推送调拨单操作
        if(!$db->execute("UPDATE stock_transfer SET wms_status = 4 WHERE rec_id = $transfer_id AND modified = '{$transfer_info['modified']}'"))
        {
            $error_msg = $db->error_msg();
            logx("transfer_id: $transfer_id, Update wms_status failed:$error_msg! wms_adapter_add_transfer_in", $sid.'/WMS_CMD');
            releaseDb($db);
            ackError("推送调拨入库单失败:更新状态失败");

            return false;
        }
        $affect_rows = mysqli_affected_rows($db->link_id);
        if ($affect_rows == 0)
        {
            logx("transfer_id:$transfer_id, Order is pushing! wms_adapter_add_transfer_in",$sid.'/WMS_CMD');
            releaseDb($db);
            ackError('调拨入库单已在推送中！请稍后刷新页面');

            return false;
        }

        //外部编号(推送失败且有外部单号则不重新生成)
        if($transfer_info['status'] == 50 || $transfer_info['status'] == 62 || ($transfer_info['outer_no2'] == '' && $transfer_info['status'] == 64))
        {
            $outer_no = $db->query_result_single("select FN_SYS_NO('outer_no')", '');
            if (empty($outer_no))
            {
                logx("transfer_id:$transfer_id, FN_SYS_NO('outer_no') failed! wms_adapter_transfer_in", $sid.'/WMS_CMD');
                $db->query("UPDATE stock_transfer SET wms_status= {$transfer_info['wms_status']} where rec_id=$transfer_id ");
                ackError("推送调拨入库单失败:生成外部单号失败");
                releaseDb($db);

                return false;
            }
            $outer_no = 'ODB' . $outer_no;
        }
        else
        {
            $outer_no = $transfer_info['outer_no2'];
        }
        $transfer_info['outer_no'] = $outer_no;

        $data['transfer'] = $transfer_info;

        //委外调委外，是否相同仓库类型判断
        $transfer_type = WMS_METHOD_TRANSFERIN_DIVERSE_ADD;
        $data['transfer']['plan_flag'] = 0;

        if ($transfer_info['from_warehouse_type'] == $transfer_info['to_warehouse_type'])
        {
            //奇门
            if($transfer_info['from_warehouse_type'] == 11)
            {
                $from_wms_info = json_decode($transfer_info['from_api_key'], true);
                $to_wms_info   = json_decode($transfer_info['to_api_key'], true);

                if( !isset($from_wms_info['wms_type']) || !isset($to_wms_info['wms_type']) || empty($from_wms_info['wms_type']) || empty($to_wms_info['wms_type']))
                {
                    $error_msg = '奇门仓储授权信息需更新';
                    logx("outer_no:$outer_no, 奇门仓储授权信息需更新，没有wms_type字段! wms_adapter_add_transfer_out",$sid.'/WMS_CMD');
                    $db->query("UPDATE stock_transfer SET status=64,wms_status=1,error_info='{$error_msg}' where rec_id=$transfer_id ");
                    ackError('推送调拨入库单失败:仓库授权信息有误，请联系旺店通售后升级，并按照最新文档填写相关信息');
                    releaseDb($db);

                    return false;
                }

                if($from_wms_info['wms_type'] == $to_wms_info['wms_type'])//相同的仓储类型
                {
                    if($from_wms_info['customerId'] == $to_wms_info['customerId'] && $from_wms_info['sub_customerId'] == $to_wms_info['sub_customerId'])
                    {
                        $data['transfer']['plan_flag'] = 1;
                    }
                    else
                    {
                        $data['transfer']['plan_flag'] = 0;
                    }
                    $transfer_type = WMS_METHOD_TRANSFERIN_SAME_ADD;
                }
                else
                {
                    $data['transfer']['plan_flag'] = 0;
                    $transfer_type = WMS_METHOD_TRANSFERIN_DIVERSE_ADD;
                }
            }
            else
            {
                $data['transfer']['plan_flag'] = 1;
                $transfer_type = WMS_METHOD_TRANSFERIN_SAME_ADD;
            }
        }

        $wms_info = json_decode($transfer_info['to_api_key'], true);
        $wms_type = $transfer_info['to_warehouse_type'];

        $transfer_detail_list = $db->query(" SELECT std.rec_id,std.spec_id, std.num,std.remark,gs.spec_no,gs.spec_name,gg.goods_no,gg.goods_name, IFNULL(ss.spec_wh_no,'') as spec_wh_no, IFNULL(ss.spec_wh_no2,'') as spec_wh_no2 ".
            " FROM stock_transfer_detail std".
            " LEFT JOIN goods_spec gs ON gs.spec_id=std.spec_id ".
            " LEFT JOIN goods_goods gg ON gg.goods_id = gs.goods_id ".
            " LEFT JOIN stock_spec ss ON ss.spec_id = std.spec_id and ss.warehouse_id = {$transfer_info['to_warehouse_id']} ".
            " WHERE std.transfer_id= $transfer_id ");

        if (!$transfer_detail_list)
        {
            $error_msg = $db->error_msg();
            logx("outer_no:$outer_no, Get goods info failed:$error_msg! wms_adapter_add_transfer_in", $sid.'/WMS_CMD');
            $db->query("UPDATE stock_transfer SET status=64,wms_status=1,error_info='获取货品明细失败' where rec_id=$transfer_id ");
            releaseDb($db);

            return false;
        }
        if ($transfer_detail_list->num_rows == 0)
        {
            logx("outer_no:$outer_no, Goods not exists! wms_adapter_add_transfer_in", $sid.'/WMS_CMD');
            $db->query("UPDATE stock_transfer SET status=64,wms_status=1,error_info='货品明细为空' where rec_id=$transfer_id ");
            releaseDb($db);

            return false;
        }

        while($row = $db->fetch_array($transfer_detail_list))
        {
            //力威没有货品上传接口，不做判断
            if($wms_type != 14)
            {
                if ($row['spec_wh_no'] == '')
                {
                    logx( "outer no:$outer_no,Spec[{$row['spec_no']}] doesn't exist in wms! wms_adapter_add_transfer_in", $sid .'/WMS_CMD');
                    $db->execute( " UPDATE stock_transfer SET wms_status = {$transfer_info['wms_status']} WHERE rec_id = $transfer_id " );
                    releaseDb($db);
                    ackError( "推送调拨入库单失败:商品[{$row['spec_no']}]未上传过WMS,请先上传货品到WMS后再推送" );

                    return false;
                }
            }
            if($wms_type == 6 && $sid == 'lyf2')
            {
                $row['spec_no'] = $row['goods_no'];
            }
            $data['details'][] = $row;
        }

        //获取供应商编号(顺丰用)
        $provider = $db->query_result(" select pp.provider_no ".
            " from purchase_provider_goods  ppg ".
            " left join purchase_provider pp on ppg.provider_id = pp.provider_id ".
            " where ppg.spec_id = {$data['details'][0]['spec_id']} limit 1");
        if (!$provider)
        {
            $data['transfer']['provider_no'] = '';
        }

        $data['transfer']['provider_no'] = $provider['provider_no'];
        $wms_adapter = new WmsAdapter($wms_type, $wms_info);

        $error_info = '';
        $result = $wms_adapter->getTransferFlag($wms_type, @$wms_info['wms_type'], $transfer_type,$error_info);
        if($result == 0)
        {
            logx("outer_no:$outer_no, GetTransferFlag failed! wms_adapter_transfer_in", $sid.'/WMS_CMD');
            $db->query("UPDATE stock_transfer SET status=64,wms_status=1,error_info='{$error_info}' where rec_id=$transfer_id ");
            ackError("推送调拨入库单失败：{$error_info}");
            releaseDb($db);

            return false;
        }

        logx("start to send requset,outer no:$outer_no  transfer id:$transfer_id ",$sid.'/WMS_CMD');
        $result = $wms_adapter->sendRequest($transfer_type, $data, $sid);
        $send   = $wms_adapter->getSendParams();
        $resv   = $wms_adapter->getReceived();

        logx("outer no:$outer_no   send:    ".print_r($send,true),$sid.'/WMS_CMD');
        logx("outer no:$outer_no   receive: ".print_r($resv,true),$sid.'/WMS_CMD');
        logx("outer no:$outer_no   result:  ".print_r($result,true),$sid.'/WMS_CMD');

        $code      = $result['code'];
        $error_msg = $result['error_msg'];
        $rev_info  = isset($result['rev_info'])?$result['rev_info']:'';//更新回传回来的仓库单据编码

        if(mb_strlen($error_msg,'utf-8') > 200)//如果超过长度则截取
        {
            $error_msg = mb_substr($error_msg,0,200,"utf-8");
        }
        $error_msg = $db->escape_string($error_msg);

        if($code != 0)
        {
            if($code<0)
            {
                logx("outer_no:$outer_no, 系统级别错误system_error:$error_msg! wms_adapter_add_transfer_in", $sid.'/WMS_CMD');
                $db->query("UPDATE stock_transfer SET status=64,wms_status=1,outer_no2 = '{$outer_no}',error_info='WMS返回信息:{$error_msg}' where rec_id=$transfer_id ");
            }
            else
            {
                logx("outer_no:$outer_no, 应用级别错误app_error:$error_msg! wms_adapter_transfer_in", $sid.'/WMS_CMD');
                $db->query("UPDATE stock_transfer SET status=64,wms_status=1,outer_no2 = '{$outer_no}',error_info='WMS返回信息:{$error_msg}' where rec_id=$transfer_id ");
            }
            ackError("WMS返回推送失败:$error_msg");
        }
        else
        {
            $order_log = '推送外部WMS调拨入库单成功';
            if(mb_strlen($rev_info,'utf-8') > 40)//单号超长
            {
                $rev_info = '';
                $order_log = '推送外部WMS调拨入库单成功,WMS返回单号超长,请联系旺店通技术';
                logx("outer_no:$outer_no, WMS返回单号超长! wms_adapter_add_transfer_in",$sid.'/WMS_CMD');
            }

            //待出库
            if(!$db->query("UPDATE stock_transfer SET wms_status=2,error_info = '',status = 66,outer_no2='{$outer_no}',to_wms_order_no = '{$rev_info}' where rec_id = $transfer_id"))
            {
                $error_msg = $db->error_msg();
                logx("outer_no:$outer_no, Update status failed:$error_msg! wms_adapter_add_transfer_in", $sid.'/WMS_CMD');
                ackError("推送调拨入库单失败:单据已推送成功,系统内单据状态处理失败");
                releaseDb($db);

                return false;
            }
            else
            {
                $db->execute("INSERT INTO stock_inout_log(order_type,order_id,operator_id,operate_type,message) VALUES(3,$transfer_id,$uid,70,'{$order_log}')");
                logx("outer_no:$outer_no, Push success! wms_adapter_add_transfer_in", $sid.'/WMS_CMD');

                ackOk(0);
            }
        }
        releaseDb($db);

        exit(0);
    }

    /*取消调拨入库单*/
    function wms_adapter_cancel_transfer_in($sid, $uid, $transfer_id)
    {
        //连接卖家数据库
        $db = getUserDb($sid);
        if(!$db)
        {
            logx("transfer id:$transfer_id, GetUserDb failed! wms_adapter_cancel_transfer_in", $sid.'/WMS_CMD');
            ackError('取消调拨入库单但失败:服务器内部错误');

            return false;
        }

        //获取符合条件的调拨单
        $transfer_info = $db->query_result(" select st.rec_id,st.to_wms_order_no, st.transfer_no, st.outer_no, st.outer_no2,st.status, st.wms_status, st.remark, st.modified,".
            " sw1.is_defect AS from_is_defect,sw1.api_object_id AS from_api_object_id,sw1.api_key AS from_api_key,sw1.ext_warehouse_no AS from_ext_warehouse_no,sw1.type AS from_warehouse_type, ".
            " sw2.is_defect AS to_is_defect,sw2.api_object_id AS to_api_object_id,sw2.api_key AS to_api_key,sw2.ext_warehouse_no   AS to_ext_warehouse_no,  sw2.type AS to_warehouse_type ".
            " from stock_transfer st ".
            " left join sys_warehouse sw1 on sw1.warehouse_id = st.from_warehouse_id ".
            " left join sys_warehouse sw2 on sw2.warehouse_id = st.to_warehouse_id ".
            " where st.rec_id={$transfer_id}");

        if(!$transfer_info)
        {
            logx("transfer_id:$transfer_id, Order not exists! wms_adapter_cancel_transfer_in",$sid.'/WMS_CMD');
            releaseDb($db);
            ackError('取消调拨入库单失败:没有获取到调拨单信息');

            return false;
        }
        $outer_no = $transfer_info['outer_no2'];
        //只能取消状态为待入库调拨单
        if ($transfer_info['status'] != 66)
        {
            logx("outer_no:$outer_no, Status[{$transfer_info['status']}] error! wms_adapter_cancel_transfer_in",$sid.'/WMS_CMD');
            releaseDb($db);
            ackError('调拨单状态已变更，请刷新页面');

            return false;
        }

        //委外仓库类型判断,目前支持SKU360(6)、顺丰(9)、奇门(11)
        if($transfer_info['to_warehouse_type'] !=6 && $transfer_info['to_warehouse_type'] != 9 && $transfer_info['to_warehouse_type'] != 11)
        {
            logx("outer_no:$outer_no, Warehouse[{$transfer_info['to_warehouse_type']}] not support! wms_adapter_cancel_transfer_in", $sid.'/WMS_CMD');
            releaseDb($db);
            ackError('取消调拨入库单失败:调出仓库委外暂不支持取消调拨');

            return false;
        }

        //并发处理：同一调拨单同时只能执行一个取消调拨操作
        $db->execute(" UPDATE stock_transfer SET wms_status = 4 WHERE rec_id = $transfer_id AND modified = '{$transfer_info['modified']}'");
        $affect_rows = mysqli_affected_rows($db->link_id);
        if ($affect_rows == 0)
        {
            logx("outer_no:$outer_no, Order is pushing! wms_adapter_cancel_transfer_in",$sid.'/WMS_CMD');
            releaseDb($db);
            ackError('取消调拨入库单已在推送！请稍后刷新页面');

            return false;
        }

        $wms_info = json_decode($transfer_info['to_api_key'], true);
        $wms_type = $transfer_info['to_warehouse_type'];

        //顺丰不走OMS取消入库单接口，所以直接更改调拨单状态
        if ($wms_type == 9 && isset($wms_info['access_code']) && empty($wms_info['access_code']))
        {
            if(!$db->query("UPDATE stock_transfer SET status=62,wms_status=0,error_info = '' where rec_id=$transfer_id "))
            {
                $error_msg = $db->error_msg();
                logx("outer_no:$outer_no, Update status failed:$error_msg! wms_adapter_cancel_transfer_in", $sid.'/WMS_CMD');
                ackError("取消调拨入库单失败:服务器处理异常");
            }
            else
            {
                $db->execute("INSERT INTO stock_inout_log(order_type,order_id,operator_id,operate_type,message) VALUES(3,$transfer_id,$uid,76,'取消调拨入库单成功')");
                logx("outer_no:$outer_no, Cancel success! wms_adapter_cancel_transfer_in", $sid.'/WMS_CMD');
                ackError("取消成功,请及时通知仓储取消该单据！");
            }
            releaseDb($db);

            return false;
        }

        $data['transfer'] = $transfer_info;

        //转送到适配器，进行数据分析
        $transfer_type = WMS_METHOD_TRANSFERIN_CANCEL;

        if ($transfer_info['from_warehouse_type'] == $transfer_info['to_warehouse_type'])
        {
            //奇门
            if($transfer_info['to_warehouse_type'] == 11)
            {
                $from_wms_info = json_decode($transfer_info['from_api_key'], true);
                $to_wms_info   = json_decode($transfer_info['to_api_key'], true);
                if( !isset($from_wms_info['wms_type']) || !isset($to_wms_info['wms_type']) ||empty($from_wms_info['wms_type']) || empty($to_wms_info['wms_type']))
                {
                    $error_msg = '奇门仓储授权信息需更新';
                    logx("outer_no:$outer_no, 奇门仓储授权信息需更新，没有wms_type字段! wms_adapter_cancel_transfer_in",$sid.'/WMS_CMD');
                    $db->query("UPDATE stock_transfer SET wms_status=0,error_info='{$error_msg}' where rec_id=$transfer_id ");
                    ackError('取消调拨入库单失败:仓库授权信息有误，请联系旺店通售后升级，并按照最新文档填写相关信息');
                    releaseDb($db);

                    return false;
                }

                if($from_wms_info['wms_type'] == $to_wms_info['wms_type'])//相同的仓储类型
                {
                    $transfer_type = WMS_METHOD_TRANSFERIN_CANCEL;
                }
                else
                {
                    $transfer_type = WMS_METHOD_TRANSFERIN_DIVERSE_CANCEL;
                }
            }
        }
        else if($transfer_info['to_warehouse_type'] == 11)
        {
            $transfer_type = WMS_METHOD_TRANSFERIN_DIVERSE_CANCEL;
        }

        //转送到适配器，进行数据分析
        $wms_adapter = new WmsAdapter($wms_type, $wms_info);

        logx("start to send requset,outer no:$outer_no  transfer id:$transfer_id ",$sid.'/WMS_CMD');
        $result = $wms_adapter->sendRequest($transfer_type, $data, $sid);
        $send   = $wms_adapter->getSendParams();
        $resv   = $wms_adapter->getReceived();

        logx("outer no:$outer_no  send:    ".print_r($send,true),$sid.'/WMS_CMD');
        logx("outer no:$outer_no  receive: ".print_r($resv,true),$sid.'/WMS_CMD');
        logx("outer no:$outer_no  result:  ".print_r($resv,true),$sid.'/WMS_CMD');

        $code = $result['code'];
        $error_msg = $result['error_msg'];
        if(mb_strlen($error_msg,'utf-8') > 200)//如果超过长度则截取
        {
            $error_msg = mb_substr($error_msg,0,200,"utf-8");
        }
        $error_msg = $db->escape_string($error_msg);

        if($code != 0)  //失败
        {
            if($code<0)
            {
                logx("outer_no:$outer_no, 系统级别错误system_error:$error_msg! wms_adapter_cancel_transfer_in", $sid.'/WMS_CMD');
            }
            else
            {
                logx("outer_no:$outer_no, 应用级别错误app_error:$error_msg! wms_adapter_cancel_transfer_in", $sid.'/WMS_CMD');
            }
            $db->query("update stock_transfer set wms_status={$transfer_info['wms_status']},error_info='{$error_msg}' where rec_id=$transfer_id ");
            ackError("WMS返回取消失败:$error_msg");
        }
        else  //成功
        {
            if(!$db->query("UPDATE stock_transfer SET status=62,wms_status=0,error_info = '' where rec_id=$transfer_id "))
            {
                $error_msg = $db->error_msg();
                logx("outer_no:$outer_no, Update db failed:$error_msg! wms_adapter_cancel_transfer_in", $sid.'/WMS_CMD');
                ackError("推送调拨入库单失败:单据已取消成功,系统内单据状态处理失败");
            }
            else
            {
                $db->execute("INSERT INTO stock_inout_log(order_type,order_id,operator_id,operate_type,message) VALUES(3,$transfer_id,$uid,76,'取消外部WMS调拨入库单成功')");
                logx("outer_no:$outer_no, Cancel success! wms_adapter_cancel_transfer_in", $sid.'/WMS_CMD');
                ackOk(0);
            }
        }
        releaseDb($db);
        exit(0);
    }

    /*推送销售退货单*/
    function wms_adapter_add_refund($sid, $uid, $refund_id)
    {
        /*
        //看是否有退货单在推送
        $lock_pid = myTestPid("wms_adapter_add_refund",true);
        if(!$lock_pid)
        {
            logx(" wms_adapter_add_refund  myTestPid  失败!!", $sid);
            ackError('有退货单正在推送，请稍后刷新界面重试');
            return;
        }
        */

        //连接卖家数据库
        $db = getUserDb($sid);
        if(!$db)
        {
            logx("refund id:$refund_id, GetUserDb failed! wms_adapter_add_refund!!", $sid.'/WMS_CMD');
            ackError('推送销售退货单失败:服务器内部错误');

            return false;
        }

        $warehouse_info = $db->query_result(" select sw.* from sales_refund sr left join sys_warehouse sw on sr.warehouse_id = sw.warehouse_id where sr.refund_id = $refund_id ");
        if(!$warehouse_info)
        {
            logx("refund_id:$refund_id, Get warehouse info failed! wms_adapter_add_refund", $sid.'/WMS_CMD');
            releaseDb($db);
            ackError('推送销售退货单失败:没有获取到仓库信息');

            return false;
        }

        //残品仓如果被匹配过，则不允许推送退货单，走委外其他入库
        if ($warehouse_info['is_defect'] == 1 && array_key_exists('match_warehouse_id',$warehouse_info) )
        {
            $match_count = $db->query_result_single("select count(1) from sys_warehouse where match_warehouse_id = {$warehouse_info['warehouse_id']} and is_disabled = 0 ");
            if ($match_count > 0 )
            {
                logx("refund_id:$refund_id, The warehouse is defect and matched,not allow to push! wms_adapter_add_refund", $sid.'/WMS_CMD');
                releaseDb($db);
                ackError('推送销售退货单失败:该残次品仓已被正品仓匹配，不允许推送退货单，请使用委外其他入库');

                return false;
            }
            $db->free_result($match_count);
        }
        $db->free_result($warehouse_info);

        //获取退货单信息
        $refund_info = $db->query_result("select sr.refund_id,sr.refund_no, sr.process_status, sr.wms_status, sr.sales_trade_id, sr.reason_id, he.fullname, sr.outer_no,  SUM(1)AS goods_type_count , SUM(sro.order_num) AS goods_count, ".
            " sw.warehouse_id,sw.is_defect,sw.api_object_id,sw.ext_warehouse_no,sw.name as warehouse_name, sw.type AS `wms_type` ,sw.api_key,sr.type AS order_type ,so.stockout_no AS src_stockout_no, so.outer_no AS src_outer_no, ".
            " so.receiver_zip,so.receiver_name ,so.receiver_mobile ,so.receiver_telno ,so.receiver_area ,so.receiver_address ,sr.logistics_no, sr.logistics_name, sr.sales_tid, cl.logistics_id, clw.logistics_code, ".
            " sw.zip as warehouse_zip, sw.province as warehouse_province, sw.city as warehouse_city, sw.district as warehouse_district, sw.address as warehouse_address, sw.contact as warehouse_contact, sw.mobile as warehouse_mobile, sw.telno as warehouse_telno, ".
            " sr.remark,sr.created,sr.modified,sr.return_name,sr.return_mobile,sr.return_telno,sr.buyer_nick ,ss.shop_name , ss.shop_no,sr.logistics_name  ".
            " from sales_refund sr ".
            " left join sales_refund_order sro ON sro.refund_id = sr.refund_id".
            " left join stockout_order so ON so.src_order_no = sr.sales_tid".
            " left join sys_warehouse sw ON sw.warehouse_id = sr.warehouse_id ".
            " left join hr_employee he on sr.operator_id = he.employee_id ".
            " left join sys_shop ss on sr.shop_id = ss.shop_id ".
            " left join cfg_logistics cl on cl.logistics_name = sr.logistics_name ".
            " left join cfg_logistics_wms clw on cl.logistics_id = clw.logistics_id and sr.warehouse_id = clw.warehouse_id ".
            " where sr.refund_id = {$refund_id} and (sr.type>=2 AND sr.type<=3) ");
        if(!$refund_info)
        {
            logx("refund_id:$refund_id, Order not exists! wms_adapter_add_refund", $sid.'/WMS_CMD');
            releaseDb($db);
            ackError('推送销售退货单失败:没有获取到退货单信息');

            return false;
        }

        if ($refund_info['process_status'] != 63 && $refund_info['process_status'] != 64)
        {
            logx("refund_id:$refund_id, Process_status[{$refund_info['process_status']}] error! wms_adapter_add_refund",$sid.'/WMS_CMD');
            releaseDb($db);
            ackError('退货单状态已变更，请刷新页面');

            return false;
        }

        //并发控制
        if(!$db->execute(" UPDATE sales_refund SET wms_status = 4 WHERE refund_id = $refund_id AND modified = '{$refund_info['modified']}'"))
        {
            $error_msg = $db->error_msg();
            logx("refund_id:$refund_id, Update wms_status failed:$error_msg! wms_adapter_add_refund",$sid.'/WMS_CMD');
            releaseDb($db);
            ackError("推送销售退货单失败:更新单据状态失败");

            return false;
        }
        $affect_rows = mysqli_affected_rows($db->link_id);
        if ($affect_rows == 0)
        {
            logx("refund_id:$refund_id, Order is pushing! wms_adapter_add_refund",$sid.'/WMS_CMD');
            releaseDb($db);
            ackError('销售退货单已在推送！请稍后刷新页面');

            return false;
        }

        //外部编号(推送失败且有外部单号不重新生成)
        if($refund_info['process_status'] == 63 || ($refund_info['process_status'] == 64 && $refund_info['outer_no'] == ''))
        {
            $outer_no = $db->query_result_single("SELECT FN_SYS_NO('outer_no')", '');
            if (empty($outer_no))
            {
                logx("refund_id:$refund_id, FN_SYS_NO('outer_no') failed! wms_adapter_add_refund", $sid.'/WMS_CMD');
                $db->execute(" UPDATE sales_refund SET wms_status = {$refund_info['wms_status']} WHERE refund_id = $refund_id ");
                releaseDb($db);
                ackError("推送销售退货单失败:获取外部单号失败");

                return false;
            }
            $outer_no = 'OTH' . $outer_no;
        }
        else
        {
            $outer_no = $refund_info['outer_no'];
        }
        $refund_info['outer_no'] = $outer_no;

        //取出省市区
        $receiver_area = trim($refund_info['receiver_area']);

        //历史订单没有出库单，需要到订单表中取收件人信息
        if (empty($receiver_area))
        {
            $receiver_area = $db->query_result_single(" select st.receiver_area from sales_refund sr left join sales_trade st on sr.sales_trade_id = st.trade_id where sr.refund_id = $refund_id ");
        }
        $area = explode(" ", $receiver_area);
        if(count($area) < 2)
        {
            logx("outer_no:$outer_no, 引用的发货单的收件人地址信息不全! wms_adapter_add_refund",$sid.'/WMS_CMD');
            $db->execute(" UPDATE sales_refund SET wms_status = {$refund_info['wms_status']} WHERE refund_id = $refund_id ");
            releaseDb($db);
            ackError("推送销售退货单失败：没有发货人省市具体信息");

            return false;
        }

        if(!isset($area[2]))
            $area[2] = '';

        $refund_info['province']  = $area[0];
        $refund_info['city']      = $area[1];
        $refund_info['district']  = $area[2];

        //东和昌物流(原中联网仓)需要取退换原因来区分2B退货还是2C退货
        if ($refund_info['wms_type'] == 8)
        {
            $reason_title = $db->query_result_single(" select title from cfg_oper_reason where reason_id = {$refund_info['reason_id']} ");
            $refund_info['reason_title'] = $reason_title;
        }
        $data['refund'] = $refund_info;

        $wms_info = json_decode($refund_info['api_key'],true);
        $wms_type = $refund_info['wms_type'];


        //获取退货单货品信息
        $goods_info = $db->query("select sro.order_id,ss.spec_id, ss.spec_wh_no, sro.refund_num,gs.spec_no,sro.price,sro.total_amount, sro.remark, gs.spec_name,gg.goods_no,gg.goods_name, ifnull(ss.spec_wh_no,'') AS spec_wh_no, ifnull(ss.spec_wh_no2,'') AS spec_wh_no2 ".
            "from sales_refund_order sro ".
            "LEFT JOIN sales_refund sr ON sro.refund_id = sr.refund_id ".
            "left join stock_spec ss on (ss.spec_id = sro.spec_id and ss.warehouse_id = sr.warehouse_id) ".
            "left join goods_spec gs on gs.spec_id= sro.spec_id ".
            "left join goods_goods gg on gs.goods_id = gg.goods_id ".
            "where sro.refund_id={$refund_id}");
        if(!$goods_info)
        {
            $error_msg = $db->error_msg();
            logx("outer_no:$outer_no, Get goods info failed:$error_msg! wms_adapter_add_refund",$sid.'/WMS_CMD');
            $db->execute(" UPDATE sales_refund SET wms_status = {$refund_info['wms_status']} WHERE refund_id = $refund_id ");
            releaseDb($db);
            ackError("推送销售退货单失败:获取退货单货品信息失败");

            return false;
        }
        if($goods_info->num_rows == 0)
        {
            logx("outer_no:$outer_no, Goods not exists! wms_adapter_add_refund",$sid.'/WMS_CMD');
            $db->execute(" UPDATE sales_refund SET wms_status = {$refund_info['wms_status']} WHERE refund_id = $refund_id ");
            releaseDb($db);
            ackError("推送销售退货单失败:货品明细为空");

            return false;
        }

        while($row = $db->fetch_array($goods_info))
        {
            //力威没有货品上传接口，不做判断
            if($wms_type != 14)
            {
                if ($row['spec_wh_no'] == '')
                {
                    logx( "outer no:$outer_no,Spec[{$row['spec_no']}] doesn't exist in wms! wms_adapter_add_refund", $sid.'/WMS_CMD' );
                    $db->execute( " UPDATE sales_refund SET wms_status = {$refund_info['wms_status']} WHERE refund_id = $refund_id " );
                    releaseDb($db);
                    ackError("推送退货单失败:商品[{$row['spec_no']}]未上传过WMS,请先上传货品到WMS后再推送");

                    return false;
                }
            }

            //心怡仓储要求传商品的平台编码用于海关校验
            if ($wms_type == 13)
            {
                $api_goods_info     = xy_get_api_info($db,'api_goods_info',$row['spec_id']);
                $row['api_spec_no'] = isset($api_goods_info['spec_outer_id'])?$api_goods_info['spec_outer_id']:'';
            }
            if($wms_type == 6 && $sid == 'lyf2')
            {
                //360sku 来伊份货品编码问题，需要用货品编号作为唯一标识
                $row['spec_no'] = $row['goods_no'];
                $data['details'][] = $row;
            }
            else
                $data['details'][] = $row;
        }
        //获取供应商编号(顺丰用)
        $provider = $db->query_result(" select pp.provider_no ".
            " from purchase_provider_goods  ppg ".
            " left join purchase_provider pp on ppg.provider_id = pp.provider_id ".
            " where ppg.spec_id = {$data['details'][0]['spec_id']} limit 1");
        if (!$provider)
        {
            $data['refund']['provider_no'] = '';
        }
        $data['refund']['provider_no'] = $provider['provider_no'];

        $wms_adapter = new WmsAdapter($wms_type, $wms_info);

        logx("start to send requset,outer no:$outer_no  refund id:$refund_id ",$sid.'/WMS_CMD');
        $result = $wms_adapter->sendRequest(WMS_METHOD_REFUND_ADD, $data, $sid);
        $send   = $wms_adapter->getSendParams();
        $resv   = $wms_adapter->getReceived();

        logx("outer no:$outer_no,  send:    ".print_r($send,true),$sid.'/WMS_CMD');
        logx("outer no:$outer_no,  receive: ".print_r($resv,true),$sid.'/WMS_CMD');
        logx("outer no:$outer_no,  result:  ".print_r($result,true),$sid.'/WMS_CMD');

        $code = $result['code'];
        $error_msg = $result['error_msg'];
        if(mb_strlen($error_msg,'utf-8') > 200)//如果超过长度则截取
        {
            $error_msg = mb_substr($error_msg,0,200,"utf-8");
        }
        $error_msg = $db->escape_string($error_msg);
        $rev_info = isset($result['rev_info'])?$result['rev_info']:'';//更新回传回来的仓库订单编码

        if($code != 0)
        {
            if($code<0)
            {
                logx("outer_no:$outer_no, 系统级别错误system_error:$error_msg! wms_adapter_add_refund", $sid.'/WMS_CMD');
                $db->query("UPDATE sales_refund SET process_status=64,wms_status=1,outer_no = '{$outer_no}',wms_result='WMS返回信息:{$error_msg}' where refund_id = {$refund_id}");
            }
            else
            {
                logx("outer_no:$outer_no, 应用级别错误app_error:$error_msg! wms_adapter_add_refund", $sid.'/WMS_CMD');
                $db->query("UPDATE sales_refund SET process_status=64,wms_status=1,outer_no = '{$outer_no}',wms_result='WMS返回信息:$error_msg' where refund_id = $refund_id ");
            }
            ackError("WMS返回推送失败:$error_msg");
        }
        else//成功
        {
            //异步处理
            if($refund_info['wms_type'] == 5)//百世wms,先把wms_status置为0，等百世回传成功接收再置为2
            {
                if(!$db->query("UPDATE sales_refund SET wms_status=0 ,wms_result='' ,process_status=65 ,outer_no='{$refund_info['outer_no']}' ,wms_outer_no = '{$rev_info}' where refund_id = $refund_id "))
                {
                    $error_msg = $db->error_msg();
                    logx("outer_no:$outer_no, Update status failed:$error_msg! wms_adapter_add_refund",$sid.'/WMS_CMD');
                    ackError("推送销售退货单失败:单据申请已推送成功,系统内单据状态处理失败");
                }
                else
                {
                    $db->execute("INSERT INTO sales_refund_log(refund_id,operator_id,`type`,remark) VALUES({$refund_id},$uid,100,'推送外部WMS销售退货单申请成功')");
                    logx("outer_no:$outer_no, Push success! wms_adapter_add_refund", $sid.'/WMS_CMD');
                    ackOk(0);
                }
            }
            else
            {
                $order_log = '推送外部WMS销售退货单成功';
                if(mb_strlen($rev_info,'utf-8') > 40)//单号超长
                {
                    $rev_info = '';
                    $order_log = '推送外部WMS销售退货单成功,WMS返回单号超长,请联系旺店通技术';
                    logx("outer_no:$outer_no, WMS返回单号超长! wms_adapter_add_refund",$sid.'/WMS_CMD');
                }

                if(!$db->query("UPDATE sales_refund SET wms_status=2 ,wms_result='' ,process_status=65 ,outer_no='{$refund_info['outer_no']}' ,wms_outer_no = '{$rev_info}' where refund_id = $refund_id "))
                {
                    $error_msg = $db->error_msg();
                    logx("outer_no:$outer_no, Update status failed:$error_msg! wms_adapter_add_refund",$sid.'/WMS_CMD');
                    ackError("推送销售退货单失败:单据已推送成功,系统内单据状态处理失败");
                }
                else
                {
                    $db->execute("INSERT INTO sales_refund_log(refund_id,operator_id,`type`,remark) VALUES({$refund_id},$uid,100,'{$order_log}')");
                    logx("outer_no:$outer_no, Push success! wms_adapter_add_refund", $sid.'/WMS_CMD');
                    ackOk(0);
                }
            }

        }
        releaseDb($db);

        exit(0);
    }

    /*取消退货单*/
    function wms_adapter_cancel_refund($sid, $uid, $refund_id)
    {
        //连接卖家数据库
        $db = getUserDb($sid);
        if(!$db)
        {
            logx("refund id:$refund_id, GetUserDb failed! wms_adapter_cancel_refund!", $sid.'/WMS_CMD');
            ackError('取消销售退货单失败:服务器内部错误');

            return false;
        }

        //获取退货单信息
        $refund_info = $db->query_result(" select sr.refund_id,sr.type as refund_type,sr.sales_tid,sr.swap_trade_id as swap_trade_id,sr.refund_no,sr.process_status,sr.outer_no ,sr.wms_outer_no ,sr.remark,sr.created,sr.modified, ".
            " sw.is_defect, sw.api_object_id, sw.type as wms_type, sw.api_key , sr.wms_status ".
            " from sales_refund sr ".
            " left join sys_warehouse sw using(warehouse_id) ".
            " where sr.refund_id = {$refund_id}");

        if(!$refund_info)
        {
            logx("refund_id:$refund_id, Order not exists! wms_adapter_cancel_refund", $sid.'/WMS_CMD');
            releaseDb($db);
            ackError('取消销售退货单失败:没有获取到退货单信息');

            return false;
        }
        $outer_no = $refund_info['outer_no'];

        if ($refund_info['process_status'] != 65)
        {
            logx("outer_no:$outer_no, Process_status[{$refund_info['process_status']}] error! wms_adapter_cancel_refund",$sid.'/WMS_CMD');
            releaseDb($db);
            ackError('销售退货单状态已变更，请刷新页面');

            return false;
        }

        //如果是换货进入分支
        if($refund_info['refund_type'] == 3)
        {
            //获取换出的订单状态
            $sales_info = $db->query("SELECT DISTINCT st.trade_status FROM sales_trade st WHERE st.trade_id IN
									(SELECT sto.trade_id FROM sales_trade_order sto  LEFT JOIN api_trade ate ON ate.tid=sto.src_tid
									 WHERE ate.rec_id={$refund_info['swap_trade_id']})
								 ");

            //换出的订单是否为已取消状态，是 就继续，不是，返回错误，弹窗提示
            while($row = $db->fetch_array($sales_info))
            {
                if($row['trade_status'] != 5)
                {
                    logx("outer_no:$outer_no, Exchange order is not cancelled! wms_adapter_cancel_refund", $sid.'/WMS_CMD');
                    releaseDb($db);
                    ackError("取消销售退货单失败:换货产生的订单不是已取消状态，不允许取消退货单");//换货产生的订单不是已取消状态，不允许取消退货单

                    return false;
                }
            }
        }

        /*
        $db->execute(" UPDATE sales_refund SET wms_status = 4 WHERE refund_id = $refund_id AND modified = '{$refund_info['modified']}'");
        $affect_rows = mysqli_affected_rows($db->link_id);
        if ($affect_rows == 0)
        {
            releaseDb($db);
            logx("wms_adapter_cancel_refund order has pushed,{$refund_id}!!",$sid);
            ackError('取消退货单已推送！请刷新页面');
            return;
        }
        */
        $wms_info = json_decode($refund_info['api_key'],true);
        $wms_type = $refund_info['wms_type'];

        if($refund_info['wms_status'] == 3 || ($wms_type == 9 && isset($wms_info['access_code']) && empty($wms_info['access_code'])))//对方已经成功取消，只是调用存储过程失败或者顺丰不走OMS取消入库单接口
        {
            if($db->execute("SET @cur_uid={$uid} ") && $result = $db->query("CALL SP_SALES_REFUND_REVERT('{$refund_id}')"))
            {
                //有返回错误信息，处理失败
                if($row = $db->fetch_array($result))
                {
                    $error_msg = $row['error'];
                    logx("outer_no:$outer_no, Call SP_SALES_REFUND_REVERT failed:$error_msg! wms_adapter_cancel_refund",$sid.'/WMS_CMD');
                    $db->free_result($result);
                    $db->query("update sales_refund set wms_status=3,wms_result='取消操作执行失败' where refund_id={$refund_id}");
                    ackError("取消销售退货单失败:$error_msg");
                }
                //没有返回错误信息，处理成功
                else
                {
                    $db->free_result($result);
                    $db->execute("update sales_refund set wms_status=0,wms_result='' where refund_id={$refund_id}");
                    $db->execute("INSERT INTO sales_refund_log(refund_id,operator_id,type,remark) VALUES({$refund_id},$uid,100,'取消外部WMS退货单成功')");
                    logx("outer_no:$outer_no, Cancel success! wms_adapter_cancel_refund", $sid.'/WMS_CMD');
                    if($refund_info['wms_type'] == 9)
                    {
                        ackError("取消成功,请及时通知仓储取消该单据！");
                    }
                    else
                    {
                        ackOk(0);
                    }
                }

            }
            else
            {
                $error_msg = $db->error_msg();
                logx("outer_no:$outer_no, Call SP_SALES_REFUND_REVERT failed:$error_msg! wms_adapter_cancel_refund",$sid.'/WMS_CMD');
                $db->query("update sales_refund set wms_status=3,wms_result='取消操作执行失败' where refund_id={$refund_id}");
                ackError("取消销售退货单失败:服务器处理异常,请稍后重试");
            }
            releaseDb($db);
            exit(0);
        }
        $data['refund'] = $refund_info;

        $wms_adapter = new WmsAdapter($wms_type, $wms_info);

        logx("start to send requset,outer no:$outer_no  refund id:$refund_id ",$sid.'/WMS_CMD');
        $result = $wms_adapter->sendRequest(WMS_METHOD_REFUND_CANCEL, $data, $sid);
        $send   = $wms_adapter->getSendParams();
        $resv   = $wms_adapter->getReceived();

        logx("outer no:$outer_no,  send:    ".print_r($send,true),$sid.'/WMS_CMD');
        logx("outer no:$outer_no   receive: ".print_r($resv,true),$sid.'/WMS_CMD');
        logx("outer no:$outer_no   result:  ".print_r($result,true),$sid.'/WMS_CMD');

        $code = $result['code'];
        $error_msg = $result['error_msg'];
        if(mb_strlen($error_msg,'utf-8') > 200)//如果超过长度则截取
        {
            $error_msg = mb_substr($error_msg,0,200,"utf-8");
        }

        //判断是否成功
        if($code != 0)
        {
            if($code<0)
            {
                logx("outer_no:$outer_no, 系统级别错误system_error:$error_msg! wms_adapter_cancel_refund", $sid.'/WMS_CMD');
            }
            else
            {
                logx("outer_no:$outer_no, 应用级别错误app_error:$error_msg! wms_adapter_cancel_refund", $sid.'/WMS_CMD');
            }
            ackError("WMS返回取消失败:$error_msg");
        }
        else
        {
            //异步处理
            if($refund_info['wms_type'] == 5)//百世wms
            {
                if(!$db->query("update sales_refund set wms_status=4 where refund_id={$refund_info['refund_id']}"))
                {
                    $error_info = $db->error_msg();
                    logx("outer_no:$outer_no, Update status failed:$error_info! wms_adapter_cancel_refund", $sid.'/WMS_CMD');
                    ackError("取消销售退货单失败:服务器处理异常,请稍后重试");
                }
                else//推送成功
                {
                    $db->execute("INSERT INTO sales_refund_log(refund_id,operator_id,type,remark) VALUES({$refund_id},$uid,100,'取消外部WMS销售退货单申请成功')");
                    logx("outer_no:$outer_no, Push cancel request success! wms_adapter_cancel_refund, wms_statu=4", $sid.'/WMS_CMD');

                    //延时等待
                    $error_info = '';
                    $query_status_sql = "select wms_status from sales_refund where refund_id={$refund_info['refund_id']}";
                    $query_info_sql = "select wms_result from salse_refund where refund_id={$refund_info['refund_id']}";
                    $result = delayCancelOrder($db,$query_status_sql,$query_info_sql,$error_info);

                    //收到百世取消成功的消息
                    if($result)
                    {
                        logx("outer_no:$outer_no, Cancel success! wms_adapter_cancel_refund", $sid.'/WMS_CMD');
                        ackOk(0);
                    }
                    else
                    {
                        ackError("取消销售退货单失败:$error_info");
                    }

                }
            }
            else//同步的
            {
                if($db->execute("SET @cur_uid={$uid} ") && $result = $db->query("CALL SP_SALES_REFUND_REVERT('{$refund_id}')"))
                {
                    if($row = $db->fetch_array($result))
                    {
                        $error_msg = $row['error'];
                        logx("outer_no:$outer_no, Call SP_SALES_REFUND_REVERT failed:$error_msg! wms_adapter_cancel_refund",$sid.'/WMS_CMD');
                        $db->free_result($result);
                        $db->query("update sales_refund set wms_status=3,wms_result='取消操作执行失败' where refund_id={$refund_id}");
                        ackError("取消销售退货单信息失败:$error_msg");
                    }
                    else
                    {
                        $db->free_result($result);
                        $db->execute("update sales_refund set wms_status=0,wms_result='' where refund_id={$refund_id}");
                        $db->execute("INSERT INTO sales_refund_log(refund_id,operator_id,type,remark) VALUES({$refund_id},$uid,100,'取消外部WMS销售退货单成功')");
                        logx("outer_no:$outer_no, Cancel success! wms_adapter_cancel_refund", $sid.'/WMS_CMD');
                        ackOk(0);
                    }

                }
                else
                {
                    $error_msg = $db->error_msg();
                    logx("refund_id:$refund_id,Call SP_SALES_REFUND_REVERT failed:$error_msg! wms_adapter_cancel_refund",$sid.'/WMS_CMD');
                    $db->query("update sales_refund set wms_status=3,wms_result='取消操作执行失败' where refund_id={$refund_id}");
                    ackError("取消销售退货单失败:WMS已取消成功,系统内单据状态处理失败,请重试！");
                }
            }
        }
        releaseDb($db);
        exit(0);
    }

    /*订单更新处理*/
    function order_refresh_handle($db, $stockout_id, $uid, $sid, $reason_id, $trade_id)
    {
        if( $db->execute('BEGIN') !== false &&
            $db->execute("SET @cur_uid=$uid") &&
            $db->query("call I_STOCKOUT_ORDER_REVERT_CHECK($stockout_id,$reason_id,0,0,1)")
        )
        {
            $sys_msg = $db->query_result("SELECT @sys_code as code, @sys_message as msg");
            if($sys_msg['code'] != 0)//失败
            {
                $error_msg = $sys_msg['msg'];
                $db->execute('ROLLBACK');
                logx("call I_STOCKOUT_ORDER_REVERT_CHECK failed  {$error_msg} ",$sid.'/WMS_CMD');
                ackError('取消wms订单 失败');
                return ;
            }
            $db->query("UPDATE stockout_order SET wms_status=0,error_info='' where stockout_id=%d", $stockout_id);
            $db->execute("COMMIT");

            logx("stockout_id: {$stockout_id}, success in wms_adapter_cancel_order",$sid.'/WMS_CMD');
            $db->execute("insert into sales_trade_log(trade_id,operator_id,type,message) values($trade_id,$uid,300,'取消外部WMS订单成功')");
            ackOk(0);
            return ;
        }
        else
        {
            logx("call I_STOCKOUT_ORDER_REVERT_CHECK failed!!,stockout_id:$stockout_id error:".$db->error_msg(),$sid.'/WMS_CMD');
            $db->execute("ROLLBACK");
            $error_msg  = '服务器异常,请稍后重试';

            logx("stockout_id: {$stockout_id}, {$error_msg} in wms_adapter_cancel_order",$sid.'/WMS_CMD');
            $db->execute("UPDATE stockout_order SET wms_status=3,error_info={$error_msg} where stockout_id={$stockout_id}");
            ackError('取消外部wms订单 失败');
            return ;
        }

    }

    // 取消推送jit出库单
    function wms_adapter_cancel_jit_order($sid, $uid, $rec_id)
    {
        $db = getUserDb($sid);
        if(!$db)
        {
            logx("getUserDb failed in wms_adapter_cancel_jit_order!!", $sid.'/WMS_CMD');
            ackError('服务器内部错误');
            return;
        }

        //获取订单信息
        $jit_pick_info = $db->query_result("select jp.rec_id, jp.vph_pick_no, jp.status,jp.outer_no, sw.api_object_id, sw.ext_warehouse_no, sw.type as wms_type, sw.api_key, sw.warehouse_id, ".
            " jp.modified, jp.wms_status, jp.wms_outer_no ".
            " from jit_pick jp ".
            " left join sys_warehouse sw using(warehouse_id) ".
            " where jp.rec_id = {$rec_id}");

        //logx("the jit_pick is ".print_r($jit_pick_info,true));

        if(!$jit_pick_info)
        {
            logx("rec_id:$rec_id, Order not exists! wms_adapter_cancel_jit_order", $sid.'/WMS_CMD');
            releaseDb($db);
            ackError('取消JIT出库单失败:没有获取到JIT出库单信息');
            return;
        }
        $outer_no = $jit_pick_info['outer_no'];

        if ($jit_pick_info['status'] != 37)
        {
            logx("outer_no:$outer_no, Status is wrong, current status is {$jit_pick_info['status']}! wms_adapter_cancel_jit_order",$sid.'/WMS_CMD');
            releaseDb($db);
            ackError('出库单状态不正确，请刷新后重试');
            return;
        }

        if($jit_pick_info['wms_status'] == 3)//对方已经成功取消，只是调用存储过程失败
        {
            if($db->execute("SET @cur_uid={$uid}") && $result = $db->query("CALL SP_JIT_PICK_BACK('{$jit_pick_info['rec_id']}',1)")) //参数1 代表接口调用，0代表客户端自己调用
            {

                $db->free_result($result);
                if(!$db->query("UPDATE jit_pick SET outer_no = '',wms_status=0,error_info = '' where rec_id= {$jit_pick_info['rec_id']}"))
                {
                    $error_msg = $db->error_msg();
                    logx("outer_no:$outer_no, Update db failed:$error_msg! wms_adapter_cancel_jit_order", $sid.'/WMS_CMD');
                    ackError("取消JIT出库单失败:WMS已取消成功,系统内单据状态处理失败");
                }
                else
                {
                    $db->execute("INSERT INTO jit_po_log(order_id,type,operator_type, operater_id,message) VALUES($rec_id,1,0,$uid,'取消jit出库单成功')");
                    logx("outer_no:$outer_no, Cancel success! wms_adapter_cancel_jit_order", $sid.'/WMS_CMD');
                    ackOk(0);
                }
            }
            else
            {
                $error_msg = $db->error_msg();
                logx("outer_no:$outer_no, Call SP_JIT_PICK_BACK failed:$error_msg! wms_adapter_cancel_jit_order",$sid.'/WMS_CMD');
                $db->query("update jit_pick set wms_status=3,error_info='取消操作执行失败' where rec_id={$jit_pick_info['rec_id']}");
                ackError("取消JIT出库单失败:$error_msg");
            }
            releaseDb($db);
            exit(0);
        }

        $data['JitOut'] = $jit_pick_info;
        $wms_info = json_decode($jit_pick_info['api_key'],true);
        $wms_type = $jit_pick_info['wms_type'];

        //推送
        $wms_adapter = new WmsAdapter($wms_type, $wms_info);

        logx("==begin push===",$sid.'/WMS_CMD');
        $result = $wms_adapter->sendRequest(WMS_METHOD_JIT_PICK_CANCEL, $data);
        $send   = $wms_adapter->getSendParams();
        $resv   = $wms_adapter->getReceived();

        logx("send:    ".print_r($send,true),$sid.'/WMS_CMD');
        logx("receive: ".print_r($resv,true),$sid.'/WMS_CMD');

        $code = $result['code'];
        $error_msg = $result['error_msg'];
        if(mb_strlen($error_msg,'utf-8') > 200)//如果超过长度则截取
        {
            $error_msg = mb_substr($error_msg,0,200,"utf-8");
        }
        $error_msg = $db->escape_string($error_msg);
        logx("result: ".print_r($result,true),$sid.'/WMS_CMD');

        //反馈信息解析
        if($code != 0)
        {
            if($code<0)
            {
                logx("outer_no:$outer_no, 系统级别错误system_error:$error_msg! wms_adapter_cancel_jit_order", $sid.'/WMS_CMD');
            }
            else
            {
                logx("outer_no:$outer_no, 应用级别错误app_error:$error_msg! wms_adapter_cancel_jit_order", $sid.'/WMS_CMD');
            }
            ackError("取消JIT出库单失败：{$error_msg}");
        }
        else
        {
            if($db->execute("BEGIN") !== false && $db->execute("SET @cur_uid={$uid}") && $result = $db->query("CALL SP_JIT_PICK_BACK('{$rec_id}',1)"))
            {
                $db->free_result($result);
                if (!$db->execute("UPDATE jit_pick SET outer_no = '', wms_status=0,error_info = '',outer_no = '' where rec_id= $rec_id"))
                {
                    $error_msg = $db->error_msg();
                    logx("outer_no:$outer_no, Update db failed:$error_msg! wms_adapter_cancel_jit_order", $sid.'/WMS_CMD');
                    $db->execute("update jit_pick set wms_status=3,error_info='取消操作执行失败:$error_msg' where rec_id=$rec_id");
                    ackError("取消JIT出库单失败:WMS已取消成功,系统内单据状态处理失败,请重试！");
                }
                else
                {
                    $db->execute("INSERT INTO jit_po_log(order_id,type,operator_type, operater_id,message) VALUES({$rec_id},1,0,$uid,'取消jit出库单成功', NOW())");
                    logx("outer_no:$outer_no, Cancel success! wms_adapter_cancel_jit_order", $sid.'/WMS_CMD');
                    ackOk(0);
                }
            }
            else
            {
                $error_msg = $db->error_msg();
                logx("call jit_pick_back failed in wms_adapter_cancel_jit_order:{$error_msg}",$sid.'/WMS_CMD');
                $db->execute("update jit_pick set wms_status=3,error_info='取消操作执行失败:$error_msg' where rec_id=$rec_id");
                ackError("取消JIT出库单失败：{$error_msg}");
            }
        }
        releaseDb($db);
        exit(0);
    }

    /*取消订单*/
    function wms_adapter_cancel_order($sid, $uid, $stockout_id, $reason_id)
    {
        //连接卖家数据库
        $db = getUserDb($sid);
        if(!$db)
        {
            logx("stockout id:$stockout_id, GetUserDb failed! wms_adapter_cancel_order", $sid.'/WMS_CMD');
            ackError('取消订单失败:服务器内部错误');

            return false;
        }

        //获取订单信息
        $stockout_info = $db->query_result(" select so.stockout_id, so.stockout_no, so.status, so.wms_status, so.outer_no,sw.is_defect, sw.api_object_id, sw.ext_warehouse_no, sw.type as wms_type, sw.api_key, so.src_order_no ,sw.warehouse_id , ".
            " so.src_order_id,so.receiver_name,so.receiver_telno,so.receiver_mobile, so.modified ".
            " from stockout_order so ".
            " left join sys_warehouse sw using(warehouse_id) ".
            " where so.stockout_id = {$stockout_id}");

        if(!$stockout_info)
        {
            logx("stockout_id:$stockout_id, Order not exists! wms_adapter_cancel_order", $sid.'/WMS_CMD');
            releaseDb($db);
            ackError('取消订单失败:没有获取到订单信息');

            return false;
        }
        $stockout_no = $stockout_info['stockout_no'];

        if ($stockout_info['status'] != 55)
        {
            logx("stockout no:$stockout_no, Status[{$stockout_info['status']}] error! wms_adapter_cancel_order",$sid.'/WMS_CMD');
            releaseDb($db);
            ackError('销售出库单状态已变更，请刷新页面');

            return false;
        }

        /*
        $db->execute(" UPDATE stockout_order SET wms_status = 4 WHERE stockout_id = $stockout_id AND modified = '{$stockout_info['modified']}'");
        $affect_rows = mysqli_affected_rows($db->link_id);
        if ($affect_rows == 0)
        {
            releaseDb($db);
            logx("wms_adapter_cancel_order order has pushed,{$stockout_id}!!",$sid);
            ackError('取消订单已推送！请刷新页面');
            return;
        }
        */

        //判断订单状态
        $wms_status = $stockout_info['wms_status'];
        if($wms_status == 3)//调用存储过程失败
        {
            order_refresh_handle($db, $stockout_id, $uid, $sid,$reason_id,$stockout_info['src_order_id']);
            releaseDb($db);

            exit(0);
        }

        if($wms_status == 4)//异步处理，提交至委外仓库，成功wms_status = 4
        {
            ackError('取消订单已向仓库申请成功，请稍后刷新界面查看处理结果');
            releaseDb($db);

            return false;
        }

        $sync_list = $db->query_result("select status from sys_asyn_task where task_type = 2  and target_type = 1 and target_id = {$stockout_info['src_order_id']} ");

        if($sync_list)//如果存在
        {
            if($sync_list['status'] != 1)//不是处理状态
            {
                if(!$db->execute("delete from sys_asyn_task where target_id = {$stockout_info['src_order_id']}"))
                {
                    logx("删除任务失败",$sid.'/WMS_CMD');
                    if($sync_list['status'] == 0)
                    {
                        ackError("取消订单正在处理,请稍候刷新界面查看结果");
                    }
                    else
                    {
                        ackError("取消订单失败,任务列表删除失败");
                    }

                    releaseDb($db);

                    return false;
                }
            }
            else
            {
                ackError("取消订单正在后台处理,请稍候刷新界面查看结果");
                releaseDb($db);

                return false;
            }
        }

        $data['trade'] = $stockout_info;
        $wms_info = json_decode($stockout_info['api_key'],true);
        $wms_type = $stockout_info['wms_type'];

        $wms_adapter = new WmsAdapter($wms_type, $wms_info);

        logx("start to send requset,stockout no:$stockout_no  stockout id:$stockout_id   ",$sid.'/WMS_CMD');
        $result = $wms_adapter->sendRequest(WMS_METHOD_TRADE_CANCEL, $data, $sid);
        $send   = $wms_adapter->getSendParams();
        $resv   = $wms_adapter->getReceived();

        logx("stockout no:$stockout_no  send:    ".print_r($send,true),$sid.'/WMS_CMD');
        logx("stockout no:$stockout_no  receive: ".print_r($resv,true),$sid.'/WMS_CMD');
        logx("stockout no:$stockout_no  result:  ".print_r($result,true),$sid.'/WMS_CMD');

        $code = $result['code'];
        $error_msg = $result['error_msg'];
        if(mb_strlen($error_msg,'utf-8') > 200)//如果超过长度则截取
        {
            $error_msg = mb_substr($error_msg,0,200,"utf-8");
        }
        $error_msg = $db->escape_string($error_msg);

        //判断是否成功
        if($code != 0)//失败
        {
            if($code<0)
            {
                logx("stockout no:$stockout_no, 系统级别错误system_error:$error_msg! wms_adapter_cancel_order", $sid.'/WMS_CMD');
                $db->execute("UPDATE stockout_order SET wms_status=1,error_info='{$error_msg},请重新取消' where stockout_id={$stockout_id}");
            }
            else
            {
                logx("stockout no:$stockout_no, 应用级别错误app_error:$error_msg! wms_adapter_cancel_order", $sid.'/WMS_CMD');
                $db->execute("UPDATE stockout_order SET wms_status=1,error_info='{$error_msg}' where stockout_id={$stockout_id}");
            }
            ackError("WMS返回取消失败:$error_msg");
        }
        else//成功
        {
            //异步处理
            if($stockout_info['wms_type'] == 5) //百世，先把wms_status置为4，然后等待取消结果，再返回
            {
                if(!$db->query("update stockout_order so,sales_trade st set so.wms_status=4,st.revert_reason=$reason_id where so.src_order_type=1 and so.src_order_id=st.trade_id and so.stockout_id={$stockout_id}"))
                {
                    $error_info = $db->error_msg();
                    logx("stockout no:$stockout_no, Update status failed:$error_msg! wms_adapter_cancel_order", $sid.'/WMS_CMD');
                    ackError("取消订单失败:WMS已收到取消申请,系统内单据状态处理失败");
                }
                else//推送成功
                {
                    $db->execute("insert into sales_trade_log(trade_id,operator_id,type,message) values({$stockout_info['src_order_id']},$uid,300,'取消外部WMS订单的申请已提交到仓库')");
                    logx("stockout no:$stockout_no, Push cancel request success! wms_adapter_cancel_order", $sid.'/WMS_CMD');

                    //延时等待
                    $error_info = '';
                    $query_status_sql = "select wms_status from stockout_order where stockout_id={$stockout_info['stockout_id']}";
                    $query_info_sql = "select error_info from stockout_order where stockout_id={$stockout_info['stockout_id']}";
                    $result = delayCancelOrder($db,$query_status_sql,$query_info_sql,$error_info);

                    //收到百世取消成功的消息
                    if($result)
                    {
                        logx("stockout no:$stockout_no, Cancel success! wms_adapter_cancel_order", $sid.'/WMS_CMD');
                        ackOk(0);
                    }
                    else
                    {
                        ackError("取消订单失败:$error_info");
                    }

                }
            }
            else
            {
                //更新订单信息
                order_refresh_handle($db, $stockout_id, $uid, $sid, $reason_id,$stockout_info['src_order_id']);
            }

        }
        releaseDb($db);
    }

    /*推送其他入库单*/
    function wms_adapter_add_other_in($sid, $uid, $stockin_order_id)
    {
        //连接卖家数据库
        $db = getUserDb($sid);
        if(!$db)
        {
            logx("stockin_id:$stockin_order_id, GetUserDb failed! wms_adapter_add_other_in",$sid.'/WMS_CMD');
            ackError('推送其他入库单失败:服务器内部错误');

            return false;
        }

        //获取其他入库单信息
        $stockin_order_info = $db->query_result("select owo.order_id, owo.order_no, owo.outer_no, owo.status, owo.wms_status, owo.remark as order_remark, owo.created as order_created, owo.modified as order_modified, owo.prop1 as order_prop1, sw.*  ".
            "from outside_wms_order owo ".
            "left join sys_warehouse sw ON owo.warehouse_id = sw.warehouse_id   ".
            "where owo.order_id =$stockin_order_id ");

        if(!$stockin_order_info)
        {
            logx("stockin_id:$stockin_order_id, Order not exists! wms_adapter_add_other_in",$sid.'/WMS_CMD');
            releaseDb($db);
            ackError('推送其他入库单失败:没有获取到其他入库单信息');

            return false;
        }

        $wms_status = $stockin_order_info['wms_status'];
        //单据状态校验，40待推送，50推送失败
        if ($stockin_order_info['status'] != 40 && $stockin_order_info['status'] != 50)
        {
            logx("stockin_id:$stockin_order_id, Status[{$stockin_order_info['status']}] error! wms_adapter_add_other_in",$sid.'/WMS_CMD');
            releaseDb($db);
            ackError('委外入库单状态已变更，请刷新页面');

            return false;
        }

        //目前只支持奇门和顺丰
        if ($stockin_order_info['type'] != 11 && $stockin_order_info['type'] != 9)
        {
            logx("stockin_order_id:$stockin_order_id, Warehouse{$stockin_order_info['type']} not support! wms_adapter_add_other_in",$sid.'/WMS_CMD');
            releaseDb($db);
            ackError('推送其他入库单失败:该委外仓暂不支持其他出入库');

            return false;
        }

        //残次品仓判断,如果为残品仓且匹配了正品仓则取对应正品仓的授权信息（向下兼容）
        $stockin_order_info['inventory_type'] = 0;
        if ($stockin_order_info['is_defect'] == 1 && array_key_exists('match_warehouse_id',$stockin_order_info))
        {
            $match_warehouse_info = $db->query_result("select api_key,api_object_id from sys_warehouse where match_warehouse_id = {$stockin_order_info['warehouse_id']} ");
            if ($match_warehouse_info)
            {
                $stockin_order_info['api_key'] = $match_warehouse_info['api_key'];
                $stockin_order_info['api_object_id'] 	= $match_warehouse_info['api_object_id'];
                $stockin_order_info['inventory_type'] = 1;
            }
        }

        //并发控制
        if(!$db->execute("UPDATE outside_wms_order SET wms_status = 4 WHERE order_id = $stockin_order_id AND modified = '{$stockin_order_info['order_modified']}'"))
        {
            $error_msg = $db->error_msg();
            logx("stockin_order_id:$stockin_order_id, Update wms_status failed:$error_msg! wms_adapter_add_other_in", $sid.'/WMS_CMD');
            ackError("推送其他入库单失败：{$error_msg},请稍后重试");
            releaseDb($db);
            return false;
        }
        $affect_rows = mysqli_affected_rows($db->link_id);
        if ($affect_rows == 0)
        {
            logx("stockin_order_id:$stockin_order_id, Order has pushed! wms_adapter_add_other_in",$sid.'/WMS_CMD');
            releaseDb($db);
            ackError('其他入库单已在推送！请刷新页面');

            return false;
        }

        //外部编号(推送失败且有外部单号不重新生成)
        if($stockin_order_info['status'] == 40 || ($stockin_order_info['status'] == 50 && $stockin_order_info['outer_no'] == ''))
        {
            $outer_no = $db->query_result_single( "select FN_SYS_NO('outer_no')", '' );
            if (empty($outer_no))
            {
                logx("stockin_id:$stockin_order_id, FN_SYS_NO('outer_no') failed! wms_adapter_add_other_in", $sid.'/WMS_CMD');
                $db->execute("UPDATE outside_wms_order SET wms_status = {$wms_status}  where order_id = $stockin_order_id ");
                releaseDb($db);
                ackError('推送其他入库单失败:生成外部单号失败');
                return false;
            }
            $outer_no = 'OQT' . $outer_no;
        }
        else
        {
            $outer_no = $stockin_order_info['outer_no'];
        }
        $stockin_order_info['outer_no'] = $outer_no;

        $data['otherIn'] = $stockin_order_info;
        $wms_info = json_decode($stockin_order_info['api_key'],true);
        $wms_type = $stockin_order_info['type'];

        //获取其他入库单 货品详细信息
        $goods_info = $db->query("select owod.rec_id,gs.spec_id,gs.spec_no,gs.barcode,owod.remark,owod.num, ifnull(ss.spec_wh_no,'') as spec_wh_no,ifnull(ss.spec_wh_no2,'') as spec_wh_no2 ".
            "from outside_wms_order_detail owod ".
            "left join goods_spec gs on gs.spec_id = owod.spec_id ".
            "left join goods_goods gg on gs.goods_id = gg.goods_id ".
            "left join stock_spec ss on ss.spec_id = owod.spec_id and ss.warehouse_id = {$stockin_order_info['warehouse_id']} ".
            "where owod.order_id = $stockin_order_id ");
        if (!$goods_info)
        {
            logx("outer_no:$outer_no, Get_goods_info error! wms_adapter_add_other_in", $sid.'/WMS_CMD');
            $db->execute("UPDATE outside_wms_order SET wms_status = {$wms_status}  where order_id = $stockin_order_id ");
            releaseDb($db);
            ackError('推送其他入库单失败:获取其他入库单货品列表信息失败');
            return false;
        }
        while($row = $db->fetch_array($goods_info))
        {
            //力威没有货品上传接口，不做判断
            if($wms_type != 14)
            {
                if ($row['spec_wh_no'] == '')
                {
                    logx( "outer no:$outer_no,Spec[{$row['spec_no']}] doesn't exist in wms! wms_adapter_add_other_in", $sid.'/WMS_CMD' );
                    $db->execute( "UPDATE outside_wms_order SET wms_status = {$wms_status}  where order_id = $stockin_order_id " );
                    releaseDb($db);
                    ackError( "推送其他入库单失败:商品[{$row['spec_no']}]未上传过WMS,请先上传货品到WMS后再推送" );

                    return false;
                }
            }
            $data['details'][] = $row;
        }

        $provider = $db->query_result(" select pp.provider_no ".
            " from purchase_provider_goods  ppg ".
            " left join purchase_provider pp on ppg.provider_id = pp.provider_id ".
            " where ppg.spec_id = {$data['details'][0]['spec_id']} AND pp.is_disabled = 0 limit 1 ");
        if (!$provider)
        {
            $data['otherIn']['provider_no'] = '';
        }
        $data['otherIn']['provider_no'] = $provider['provider_no'];

        //推送
        $wms_adapter = new WmsAdapter($wms_type, $wms_info);

        logx("start to send requset,outer no:$outer_no  stockin id:$stockin_order_id   ",$sid.'/WMS_CMD');
        $result = $wms_adapter->sendRequest(WMS_METHOD_STOCKIN_ADD, $data, $sid);
        $send   = $wms_adapter->getSendParams();
        $resv   = $wms_adapter->getReceived();

        logx("outer no:$outer_no,  send:    ".print_r($send,true),$sid.'/WMS_CMD');
        logx("outer no:$outer_no,  receive: ".print_r($resv,true),$sid.'/WMS_CMD');
        logx("outer no:$outer_no,  result:  ".print_r($result,true),$sid.'/WMS_CMD');

        $code = $result['code'];
        $error_msg = $result['error_msg'];
        if(mb_strlen($error_msg,'utf-8') > 200)//如果超过长度则截取
        {
            $error_msg = mb_substr($error_msg,0,200,"utf-8");
        }
        $error_msg = $db->escape_string($error_msg);
        $rev_info = isset($result['rev_info'])?$result['rev_info']:'';//更新回传回来的仓库订单编码

        //反馈信息解析
        if($code != 0)
        {
            if($code<0)
            {
                logx("outer_no:$outer_no, 系统级别错误system_error:$error_msg! wms_adapter_add_other_in", $sid.'/WMS_CMD');
                $db->execute("UPDATE outside_wms_order SET status=50,wms_status = 1,outer_no = '{$outer_no}',error_info= 'WMS返回信息:{$error_msg}' where order_id = $stockin_order_id ");
            }
            else
            {
                logx("outer_no:$outer_no, 应用级别错误app_error:$error_msg! wms_adapter_add_other_in", $sid.'/WMS_CMD');
                $db->execute("UPDATE outside_wms_order SET status=50,wms_status = 1,outer_no = '{$outer_no}',error_info= 'WMS返回信息:{$error_msg}' where order_id = $stockin_order_id ");
            }
            ackError("WMS返回推送失败:$error_msg");
        }
        else
        {
            $order_log = '推送外部WMS其他入库单成功';
            if(mb_strlen($rev_info,'utf-8') > 40)//单号超长
            {
                $rev_info = '';
                $order_log = '推送外部WMS其他入库单成功,WMS返回单号超长,请联系旺店通技术';
                logx("outer_no:$outer_no, WMS返回单号超长! wms_adapter_add_other_in",$sid.'/WMS_CMD');
            }

            //待收货
            if(!$db->execute("UPDATE outside_wms_order SET wms_status=2,error_info='',status=65,outer_no='{$outer_no}',wms_outer_no = '{$rev_info}' where order_id = $stockin_order_id"))
            {
                $error_msg = $db->error_msg();
                logx("outer_no:$outer_no, Update status failed:$error_msg! wms_adapter_add_other_in", $sid.'/WMS_CMD');
                ackError("推送其他入库单失败:WMS已推送成功,系统内单据状态处理失败");
            }
            else
            {
                $db->execute("insert into outside_wms_order_log(order_id,operator_id,operate_type,message) values($stockin_order_id,$uid,4,'{$order_log}')");
                logx("outer_no:$outer_no, Push success! wms_adapter_add_other_in", $sid.'/WMS_CMD');
                ackOk(0);
            }

        }
        releaseDb($db);
        exit(0);
    }

    /*取消其他入库单*/
    function wms_adapter_cancel_other_in($sid, $uid, $stockin_order_id)
    {
        //连接卖家数据库
        $db = getUserDb($sid);
        if(!$db)
        {
            logx("stockin id:$stockin_order_id, GetUserDb failed! wms_adapter_cancel_other_in",$sid.'/WMS_CMD');
            ackError('取消其他入库单失败:服务器内部错误');

            return false;
        }

        //获取其他入库单信息
        $stockin_order_info = $db->query_result("select owo.order_no, owo.outer_no, owo.wms_outer_no, owo.status, owo.wms_status, owo.remark as order_remark, sw.*  ".
            "from outside_wms_order owo ".
            "left join sys_warehouse sw ON owo.warehouse_id = sw.warehouse_id   ".
            "where owo.order_id =$stockin_order_id ");

        if(!$stockin_order_info)
        {
            logx("stockin_id:$stockin_order_id, Order not exists! wms_adapter_cancel_other_in",$sid.'/WMS_CMD');
            releaseDb($db);
            ackError('取消其他入库单失败:没有获取到其他入库单信息');

            return false;
        }
        $outer_no = $stockin_order_info['outer_no'];

        if ($stockin_order_info['status'] != 65)
        {
            logx("outer_no:$outer_no, Status[{$stockin_order_info['status']}] error! wms_adapter_cancel_other_in",$sid.'/WMS_CMD');
            releaseDb($db);
            ackError('委外入库单状态已变更，请刷新页面');

            return false;
        }

        //目前只支持奇门和顺丰
        if ($stockin_order_info['type'] != 11 && $stockin_order_info['type'] != 9)
        {
            logx("outer_no:$outer_no, Warehouse[{$stockin_order_info['type']}] didn't support,! wms_adapter_cancel_other_in",$sid.'/WMS_CMD');
            ackError('取消其他入库单失败:该委外仓暂不支持其他出入库');
            releaseDb($db);

            return false;
        }

        //残次品仓判断,如果为残品仓且匹配了正品仓则取对应正品仓的授权信息（向下兼容）
        if ($stockin_order_info['is_defect'] == 1 && array_key_exists('match_warehouse_id',$stockin_order_info))
        {
            $match_warehouse_info = $db->query_result("select api_key,api_object_id from sys_warehouse where match_warehouse_id = {$stockin_order_info['warehouse_id']} ");
            if ($match_warehouse_info)
            {
                $stockin_order_info['api_key']        = $match_warehouse_info['api_key'];
                $stockin_order_info['api_object_id']  = $match_warehouse_info['api_object_id'];
            }
        }

        $wms_info = json_decode($stockin_order_info['api_key'],true);
        $wms_type = $stockin_order_info['type'];

        if ($stockin_order_info['wms_status'] == 3 || ($wms_type == 9 && isset($wms_info['access_code']) && empty($wms_info['access_code'])))//接口调用成功了，但是存储过程执行失败了，重新执行存储过程即可
        {
            if($db->execute("BEGIN") !== false && $db->execute("SET @cur_uid={$uid}") && $result = $db->query("CALL I_STOCK_OUTSIDE_WMS_REVERT('{$stockin_order_id}',1)"))
            {

                $db->free_result($result);
                $result = $db->query_result("select @sys_code,@sys_msg");
                $code   = $result['@sys_code'];
                if($code != 0)
                {
                    $error_msg = $result['@sys_msg'];
                    logx("outer_no:$outer_no, Call I_STOCK_OUTSIDE_WMS_REVERT failed:$error_msg! wms_adapter_cancel_other_in",$sid.'/WMS_CMD');
                    $db->execute("ROLLBACK");
                    $db->free_result($result);
                    $db->execute("update outside_wms_order set wms_status=3,error_info='取消操作执行失败:$error_msg' where order_id=$stockin_order_id");
                    ackError("取消委外其他入库单失败:$error_msg");
                }
                else
                {
                    if (!$db->execute("UPDATE outside_wms_order SET wms_status=0,error_info = '',outer_no = '' where order_id= $stockin_order_id"))
                    {
                        $error_msg = $db->error_msg();
                        logx("outer_no:$outer_no, Update status failed:$error_msg! wms_adapter_cancel_other_in", $sid.'/WMS_CMD');
                        $db->execute("ROLLBACK");
                        $db->execute("update outside_wms_order set wms_status=3,error_info='取消操作执行失败:$error_msg' where order_id=$stockin_order_id");
                        ackError("取消其他入库单失败:系统内单据状态处理失败");
                    }
                    else
                    {
                        $db->execute("INSERT INTO outside_wms_order_log(order_id,operator_id,operate_type,message) VALUES($stockin_order_id,$uid,14,'取消外部WMS其他入库单成功')");
                        logx("outer_no:$outer_no, Cancel success! wms_adapter_cancel_other_in", $sid.'/WMS_CMD');
                        $db->execute("COMMIT");
                        if($stockin_order_info['type'] == 9)
                        {
                            ackError("取消成功,请及时通知仓储取消该单据！");
                        }
                        else
                        {
                            ackOk(0);
                        }
                    }
                }
            }
            else
            {
                $error_msg = $db->error_msg();
                logx("outer_no:$outer_no, Call I_STOCK_OUTSIDE_WMS_REVERT failed:$error_msg! wms_adapter_cancel_po",$sid.'/WMS_CMD');
                $db->execute("ROLLBACK");
                $db->execute("update outside_wms_order set wms_status=3,error_info='取消操作执行失败' where order_id=$stockin_order_id");
                ackError("取消其他入库单失败:系统内单据状态处理失败");
            }
            releaseDb($db);
            exit(0);
        }

        $data['otherIn'] = $stockin_order_info;

        //推送
        $wms_adapter = new WmsAdapter($wms_type, $wms_info);

        logx("start to send requset,outer no:$outer_no  stockin id:$stockin_order_id   ",$sid.'/WMS_CMD');
        $result = $wms_adapter->sendRequest(WMS_METHOD_STOCKIN_CANCEL, $data, $sid);
        $send   = $wms_adapter->getSendParams();
        $resv   = $wms_adapter->getReceived();

        logx("outer no:$outer_no,  send:    ".print_r($send,true),$sid.'/WMS_CMD');
        logx("outer no:$outer_no,  receive: ".print_r($resv,true),$sid.'/WMS_CMD');
        logx("outer no:$outer_no,  result:  ".print_r($result,true),$sid.'/WMS_CMD');

        $code = $result['code'];
        $error_msg = $result['error_msg'];
        if(mb_strlen($error_msg,'utf-8') > 200)//如果超过长度则截取
        {
            $error_msg = mb_substr($error_msg,0,200,"utf-8");
        }
        $error_msg = $db->escape_string($error_msg);

        //反馈信息解析
        if($code != 0)
        {
            if($code<0)
            {
                logx("outer_no:$outer_no, 系统级别错误system_error:$error_msg! wms_adapter_cancel_other_in", $sid.'/WMS_CMD');
            }
            else
            {
                logx("outer_no:$outer_no, 应用级别错误app_error:$error_msg! wms_adapter_cancel_other_in", $sid.'/WMS_CMD');
            }
            ackError("WMS返回取消失败：{$error_msg}");
        }
        else
        {
            if($db->execute("BEGIN") !== false && $db->execute("SET @cur_uid={$uid}") && $result = $db->query("CALL I_STOCK_OUTSIDE_WMS_REVERT('{$stockin_order_id}',1)"))
            {
                $db->free_result($result);
                $result = $db->query_result("select @sys_code,@sys_msg");
                $code = $result['@sys_code'];
                if($code != 0)
                {
                    $error_msg = $result['$sys_msg'];
                    logx("outer_no:$outer_no, Call I_STOCK_OUTSIDE_WMS_REVERT failed:$error_msg! wms_adapter_cancel_other_in",$sid.'/WMS_CMD');
                    $db->execute("ROLLBACK");
                    $db->free_result($result);
                    $db->execute("update outside_wms_order set wms_status=3,error_info='取消操作执行失败:$error_msg' where order_id=$stockin_order_id");
                    ackError("取消其他入库单失败:$error_msg");
                }
                else
                {
                    if (!$db->execute("UPDATE outside_wms_order SET wms_status=0,error_info = '',outer_no = '' where order_id= $stockin_order_id"))
                    {
                        $error_msg = $db->error_msg();
                        logx("outer_no:$outer_no, Update status failed:$error_msg! wms_adapter_cancel_other_in", $sid.'/WMS_CMD');
                        $db->execute("ROLLBACK");
                        $db->execute("update outside_wms_order set wms_status=3,error_info='取消操作执行失败:$error_msg' where order_id=$stockin_order_id");
                        ackError("取消其他入库单失败:WMS已取消成功,系统内单据状态处理失败");
                    }
                    else
                    {
                        $db->execute("INSERT INTO outside_wms_order_log(order_id,operator_id,operate_type,message) VALUES($stockin_order_id,$uid,14,'取消外部WMS其他入库单成功')");
                        logx("outer_no:$outer_no, Cancel success! wms_adapter_cancel_other_in", $sid.'/WMS_CMD');
                        $db->execute("COMMIT");
                        ackOk(0);
                    }
                }
            }
            else
            {
                $error_msg = $db->error_msg();
                logx("outer_no:$outer_no, Call I_STOCK_OUTSIDE_WMS_REVERT failed:$error_msg! wms_adapter_cancel_po",$sid.'/WMS_CMD');
                $db->execute("ROLLBACK");
                $db->execute("update outside_wms_order set wms_status=3,error_info='取消操作执行失败:$error_msg' where order_id=$stockin_order_id");
                ackError("取消其他入库单失败:WMS已取消成功,系统内单据状态处理失败");
            }
        }
        releaseDb($db);
        exit(0);
    }

    /*推送其他出库单*/
    function wms_adapter_add_other_out($sid, $uid, $stockout_order_id)
    {
        //连接卖家数据库
        $db = getUserDb($sid);
        if(!$db)
        {
            logx("stockout id:$stockout_order_id,  GetUserDb failed! wms_adapter_add_other_out",$sid.'/WMS_CMD');
            ackError('推送其他出库单失败:服务器内部错误');

            return false;
        }

        //获取其他出库单信息
        $stockout_order_info = $db->query_result("select owo.order_id, owo.order_no, owo.outer_no, owo.warehouse_id, owo.status, owo.wms_status, owo.remark as order_remark, owo.created as order_created, owo.modified as order_modified, sw.*, ".
            "owo.transport_mode, owo.logistics_id, owo.logistics_no, owo.receiver_name, owo.receiver_country, owo.receiver_province, owo.receiver_city, owo.receiver_area, owo.receiver_district,  ".
            "owo.receiver_zip, owo.receiver_address, owo.receiver_mobile, owo.receiver_telno, owo.prop1 as order_prop1, owo.prop2 as order_prop2,clw.logistics_code ".
            "from outside_wms_order owo ".
            "left join cfg_logistics cl using(logistics_id) ".
            "left join sys_warehouse sw ON owo.warehouse_id = sw.warehouse_id ".
            "left join cfg_logistics_wms clw on (clw.logistics_id=owo.logistics_id and clw.warehouse_id = owo.warehouse_id) ".
            "where owo.order_id =$stockout_order_id ");

        if(!$stockout_order_info)
        {
            logx("stockout_id:$stockout_order_id, Order not exists! wms_adapter_add_other_out",$sid.'/WMS_CMD');
            releaseDb($db);
            ackError('推送其他出库单失败:没有获取到其他出库单信息');

            return false;
        }

        //取出省市区
        $receiver_area = trim($stockout_order_info['receiver_area']);
        $area = explode(" ", $receiver_area);
        if(count($area) < 2)
        {
            logx("stockout_order_id:$stockout_order_id, No receiver province or city infomation! wms_adapter_add_other_out", $sid.'/WMS_CMD');
            ackError('推送其他出库单失败:没有收货人省市具体信息');
            releaseDb($db);

            return false;
        }
        if(!isset($area[2]))
            $area[2] = '';
        $stockout_order_info['receiver_province']  = $area[0];
        $stockout_order_info['receiver_city']      = $area[1];
        $stockout_order_info['receiver_district']  = $area[2];

        $wms_status = $stockout_order_info['wms_status'];
        //单据状态校验，40待推送，50推送失败
        if ($stockout_order_info['status'] != 40 && $stockout_order_info['status'] != 50)
        {
            logx("stockout_id:$stockout_order_id, Status[{$stockout_order_info['status']}] error! wms_adapter_add_other_out",$sid.'/WMS_CMD');
            ackError('委外出库单状态已变更，请刷新页面');
            releaseDb($db);

            return false;
        }

        //目前只支持奇门和顺丰
        if ($stockout_order_info['type'] != 11 && $stockout_order_info['type'] != 9)
        {
            logx("stockout_id:$stockout_order_id, Warehouse[{$stockout_order_info['type']}] not support! wms_adapter_add_other_out",$sid.'/WMS_CMD');
            ackError('推送其他出库单失败:该委外仓暂不支持其他出入库');
            releaseDb($db);

            return false;
        }

        //残次品仓判断,如果为残品仓且匹配了正品仓则取对应正品仓的授权信息（向下兼容）
        $stockout_order_info['inventory_type'] = 0;
        if ($stockout_order_info['is_defect'] == 1 && array_key_exists('match_warehouse_id',$stockout_order_info) )
        {
            $match_warehouse_info = $db->query_result("select api_key,api_object_id from sys_warehouse where match_warehouse_id = {$stockout_order_info['warehouse_id']} ");
            if ($match_warehouse_info)
            {
                $stockout_order_info['api_key'] = $match_warehouse_info['api_key'];
                $stockout_order_info['api_object_id'] 	= $match_warehouse_info['api_object_id'];
                $stockout_order_info['inventory_type'] = 1;
            }
        }

        //并发控制
        if(!$db->execute("UPDATE outside_wms_order SET wms_status = 4 WHERE order_id = $stockout_order_id AND modified = '{$stockout_order_info['order_modified']}'"))
        {
            $error_msg = $db->error_msg();
            logx("stockout_order_id:$stockout_order_id, Update wms_status failed:$error_msg! wms_adapter_add_other_out", $sid.'/WMS_CMD');
            ackError("推送其他出库单失败:更新状态失败");
            releaseDb($db);

            return false;
        }
        $affect_rows = mysqli_affected_rows($db->link_id);
        if ($affect_rows == 0)
        {
            logx("stockout_id:$stockout_order_id, Order is pushing! wms_adapter_add_other_out",$sid.'/WMS_CMD');
            releaseDb($db);
            ackError('其他出库单已在推送！请稍后刷新页面');

            return false;
        }

        //外部编号(推送失败且有外部单号不重新生成)
        if($stockout_order_info['status'] == 40 || ($stockout_order_info['status'] == 50 && $stockout_order_info['outer_no'] == ''))
        {
            $outer_no = $db->query_result_single( "select FN_SYS_NO('outer_no')", '' );
            if (empty($outer_no))
            {
                logx("stockout_id:stockout_order_id, FN_SYS_NO('outer_no') failed! wms_adapter_add_other_out", $sid.'/WMS_CMD' );
                $db->execute( "UPDATE outside_wms_order SET wms_status = {$wms_status}  where order_id = $stockout_order_id " );
                releaseDb($db);
                ackError('推送其他出库单失败:生成外部单号失败');

                return false;
            }
            $outer_no = 'OQT' . $outer_no;
        }
        else
        {
            $outer_no = $stockout_order_info['outer_no'];
        }
        $stockout_order_info['outer_no'] = $outer_no;

        $data['otherOut'] = $stockout_order_info;
        $wms_info = json_decode($stockout_order_info['api_key'],true);
        $wms_type = $stockout_order_info['type'];

        //获取其他出库单货品详细信息
        $goods_info = $db->query("select  owod.rec_id,gg.goods_no,gg.goods_name,gs.spec_no,gs.barcode,owod.num,ss.spec_wh_no2 ".
            "from outside_wms_order_detail owod ".
            "left join goods_spec gs on gs.spec_id = owod.spec_id ".
            "left join goods_goods gg on gs.goods_id = gg.goods_id ".
            "left join stock_spec ss on ss.spec_id = owod.spec_id ".
            "where owod.order_id = $stockout_order_id ".
            "and ss.warehouse_id = {$stockout_order_info['warehouse_id']} ");
        if (!$goods_info)
        {
            $error_msg = $db->error_msg();
            logx("outer_no:$outer_no, Get goods info failed:$error_msg! wms_adapter_add_other_out", $sid.'/WMS_CMD');
            $db->execute("UPDATE outside_wms_order SET wms_status = {$wms_status}  where order_id = $stockout_order_id ");
            releaseDb($db);
            ackError('推送其他出库单失败:获取其他出库单货品列表信息失败');

            return false;
        }
        if ($goods_info->num_rows == 0)
        {
            logx("outer_no:$outer_no, Goods not exists! wms_adapter_add_other_out", $sid.'/WMS_CMD');
            $db->execute("UPDATE outside_wms_order SET wms_status = {$wms_status}  where order_id = $stockout_order_id ");
            releaseDb($db);
            ackError('推送其他出库单失败:货品明细为空');

            return false;
        }

        while($row = $db->fetch_array($goods_info))
        {
            $data['details'][] = $row;
        }
        //推送
        $wms_adapter = new WmsAdapter($wms_type, $wms_info);

        logx("start to send requset,outer no:$outer_no  stockout id:$stockout_order_id   ",$sid.'/WMS_CMD');
        $result = $wms_adapter->sendRequest(WMS_METHOD_STOCKOUT_ADD, $data, $sid);
        $send   = $wms_adapter->getSendParams();
        $resv   = $wms_adapter->getReceived();

        logx("outer no:$outer_no,  send:    ".print_r($send,true),$sid.'/WMS_CMD');
        logx("outer no:$outer_no,  receive: ".print_r($resv,true),$sid.'/WMS_CMD');
        logx("outer no:$outer_no,  result:  ".print_r($result,true),$sid.'/WMS_CMD');

        $code = $result['code'];
        $error_msg = $result['error_msg'];
        if(mb_strlen($error_msg,'utf-8') > 200)//如果超过长度则截取
        {
            $error_msg = mb_substr($error_msg,0,200,"utf-8");
        }
        $error_msg = $db->escape_string($error_msg);
        $rev_info = isset($result['rev_info'])?$result['rev_info']:'';//更新回传回来的仓库订单编码

        //反馈信息解析
        if($code != 0)
        {
            if($code<0)
            {
                logx("outer_no:$outer_no, 系统级别错误system_error:$error_msg! wms_adapter_add_other_out", $sid.'/WMS_CMD');
                $db->execute("UPDATE outside_wms_order SET status=50,wms_status = 1,outer_no = '{$outer_no}',error_info= 'WMS返回信息:{$error_msg}' where order_id = $stockout_order_id ");
            }
            else
            {
                logx("outer_no:$outer_no, 应用级别错误app_error:$error_msg! wms_adapter_add_other_out", $sid.'/WMS_CMD');
                $db->execute("UPDATE outside_wms_order SET status=50,wms_status = 1,outer_no = '{$outer_no}',error_info= 'WMS返回信息:{$error_msg}' where order_id = $stockout_order_id ");
            }
            ackError("WMS返回推送失败:$error_msg");
        }
        else
        {
            $order_log = '推送外部WMS其他出库单成功';
            if(mb_strlen($rev_info,'utf-8') > 40)//单号超长
            {
                $rev_info = '';
                $order_log = '推送外部WMS其他出库单成功,WMS返回单号超长,请联系旺店通技术';
                logx("outer_no:$outer_no, WMS返回单号超长! wms_adapter_add_other_out",$sid.'/WMS_CMD');
            }

            //待出库
            if(!$db->execute("UPDATE outside_wms_order SET wms_status=2,error_info='',status=60,outer_no='{$outer_no}',wms_outer_no = '{$rev_info}' where order_id = $stockout_order_id"))
            {
                $error_msg = $db->error_msg();
                logx("outer_no:$outer_no, Update status failed:$error_msg! wms_adapter_add_other_out", $sid.'/WMS_CMD');
                ackError("推送其他出库单失败:WMS已推送成功,系统内单据状态处理失败");
            }
            else
            {
                $db->execute("insert into outside_wms_order_log(order_id,operator_id,operate_type,message) values($stockout_order_id,$uid,4,'{$order_log}')");
                logx("outer_no:$outer_no, Push success! wms_adapter_add_other_out", $sid.'/WMS_CMD');

                ackOk(0);
            }
        }
        releaseDb($db);
        exit(0);
    }


    /*取消其他出库单*/
    function wms_adapter_cancel_other_out($sid, $uid, $stockout_order_id)
    {
        //连接卖家数据库
        $db = getUserDb($sid);
        if(!$db)
        {
            logx("stockout id:$stockout_order_id, GetUserDb failed! wms_adapter_cancel_other_out",$sid.'/WMS_CMD');
            ackError('取消其他出库单失败:服务器内部错误');

            return false;
        }

        //获取其他出库单信息
        $stockout_order_info = $db->query_result("select owo.order_no, owo.outer_no, owo.wms_outer_no, owo.status, owo.wms_status, owo.remark as order_remark, sw.*  ".
            "from outside_wms_order owo ".
            "left join sys_warehouse sw ON owo.warehouse_id = sw.warehouse_id   ".
            "where owo.order_id =$stockout_order_id ");
        if(!$stockout_order_info)
        {
            logx("stockout_id:$stockout_order_id, Order not exists! wms_adapter_cancel_other_out",$sid.'/WMS_CMD');
            releaseDb($db);
            ackError('取消其他出库单失败:没有获取到其他出库单信息');

            return false;
        }
        $outer_no = $stockout_order_info['outer_no'];

        if ($stockout_order_info['status'] != 60)
        {
            logx("outer_no:$outer_no, Status[{$stockout_order_info['status']}] error! wms_adapter_cancel_other_out",$sid.'/WMS_CMD');
            ackError('委外出库单状态已变更，请刷新页面');
            releaseDb($db);

            return false;
        }

        //目前只支持奇门和顺丰
        if ($stockout_order_info['type'] != 11 && $stockout_order_info['type'] != 9)
        {
            logx("outer_no:$outer_no, Warehouse[{$stockout_order_info['type']}] error! wms_adapter_cancel_other_out",$sid.'/WMS_CMD');
            ackError('取消其他出库单失败:该委外仓暂不支持其他出入库');
            releaseDb($db);

            return false;
        }

        //残次品仓判断,如果为残品仓且匹配了正品仓则取对应正品仓的授权信息（向下兼容）
        if ($stockout_order_info['is_defect'] == 1 && array_key_exists('match_warehouse_id',$stockout_order_info) )
        {
            $match_warehouse_info = $db->query_result("select api_key,api_object_id from sys_warehouse where match_warehouse_id = {$stockout_order_info['warehouse_id']} ");
            if ($match_warehouse_info)
            {
                $stockout_order_info['api_key']         = $match_warehouse_info['api_key'];
                $stockout_order_info['api_object_id']   = $match_warehouse_info['api_object_id'];
            }
        }

        if ($stockout_order_info['wms_status'] == 3)//接口调用成功了，但是存储过程执行失败了，重新执行存储过程即可
        {
            if($db->execute("BEGIN") !== false && $db->execute("SET @cur_uid={$uid}") && $result = $db->query("CALL I_STOCK_OUTSIDE_WMS_REVERT('{$stockout_order_id}',1)"))
            {
                $db->free_result($result);
                $result = $db->query_result("select @sys_code,@sys_msg");
                $code = $result['@sys_code'];
                if($code != 0)
                {
                    $error_msg = $result['@sys_msg'];
                    logx("outer_no:$outer_no, Call I_STOCK_OUTSIDE_WMS_REVERT failed:$error_msg! wms_adapter_cancel_other_out",$sid.'/WMS_CMD');
                    $db->execute("ROLLBACK");
                    $db->free_result($result);
                    $db->execute("update outside_wms_order set wms_status=3,error_info='取消操作执行失败:$error_msg' where order_id=$stockout_order_id");
                    ackError("取消其他出库单失败:$error_msg");
                }
                else
                {
                    if (!$db->execute("UPDATE outside_wms_order SET wms_status=0,error_info = '',outer_no = ''  where order_id= $stockout_order_id"))
                    {
                        $error_msg = $db->error_msg();
                        logx("outer_no:$outer_no, Update status failed:$error_msg! wms_adapter_cancel_other_out", $sid.'/WMS_CMD');
                        $db->execute("ROLLBACK");
                        $db->execute("update outside_wms_order set wms_status=3,error_info='取消操作执行失败:$error_msg' where order_id=$stockout_order_id");
                        ackError("取消其他出库单失败:系统内单据状态处理失败");
                    }
                    else
                    {
                        $db->execute("INSERT INTO outside_wms_order_log(order_id,operator_id,operate_type,message) VALUES($stockout_order_id,$uid,14,'取消外部WMS其他出库单成功')");
                        logx("outer_no:$outer_no, Cancel success! wms_adapter_cancel_other_out", $sid.'/WMS_CMD');
                        $db->execute("COMMIT");
                        ackOk(0);
                    }
                }
            }
            else
            {
                $error_msg = $db->error_msg();
                logx("outer_no:$outer_no, Call I_STOCK_OUTSIDE_WMS_REVERT failed:$error_msg! wms_adapter_cancel_other_out",$sid.'/WMS_CMD');
                $db->execute("ROLLBACK");
                $db->execute("update outside_wms_order set wms_status=3,error_info='取消操作执行失败' where order_id=$stockout_order_id");
                ackError("取消其他出库单失败:系统内单据状态处理失败");
            }
            releaseDb($db);
            exit(0);
        }
        $data['otherOut'] = $stockout_order_info;
        $wms_info = json_decode($stockout_order_info['api_key'],true);
        $wms_type = $stockout_order_info['type'];

        //推送
        $wms_adapter = new WmsAdapter($wms_type, $wms_info);

        logx("start to send requset,outer no:$outer_no  stockout id:$stockout_order_id   ",$sid.'/WMS_CMD');
        $result = $wms_adapter->sendRequest(WMS_METHOD_STOCKOUT_CANCEL, $data, $sid);
        $send   = $wms_adapter->getSendParams();
        $resv   = $wms_adapter->getReceived();

        logx("outer no:$outer_no,  send:    ".print_r($send,true),$sid.'/WMS_CMD');
        logx("outer no:$outer_no,  receive: ".print_r($resv,true),$sid.'/WMS_CMD');
        logx("outer no:$outer_no,  result:  ".print_r($result,true),$sid.'/WMS_CMD');

        $code = $result['code'];
        $error_msg = $result['error_msg'];
        if(mb_strlen($error_msg,'utf-8') > 200)//如果超过长度则截取
        {
            $error_msg = mb_substr($error_msg,0,200,"utf-8");
        }
        $error_msg = $db->escape_string($error_msg);

        //反馈信息解析
        if($code != 0)
        {
            if($code<0)
            {
                logx("outer_no:$outer_no, 系统级别错误system_error:$error_msg! wms_adapter_cancel_other_out", $sid.'/WMS_CMD');
            }
            else
            {
                logx("outer_no:$outer_no, 应用级别错误app_error:$error_msg! wms_adapter_cancel_other_out", $sid.'/WMS_CMD');
            }
            ackError("WMS返回取消失败:$error_msg");
        }
        else
        {
            if($db->execute("BEGIN") !== false && $db->execute("SET @cur_uid={$uid}") && $result = $db->query("CALL I_STOCK_OUTSIDE_WMS_REVERT('{$stockout_order_id}',1)"))
            {
                $db->free_result($result);
                $result = $db->query_result("select @sys_code,@sys_msg");
                $code = $result['@sys_code'];
                if($code != 0)
                {
                    $error_msg = $result['@sys_msg'];
                    logx("outer_no:$outer_no, Call I_STOCK_OUTSIDE_WMS_REVERT failed:$error_msg! wms_adapter_cancel_other_out",$sid.'/WMS_CMD');
                    $db->execute("ROLLBACK");
                    $db->free_result($result);
                    $db->execute("update outside_wms_order set wms_status=3,error_info='取消操作执行失败:$error_msg' where order_id=$stockout_order_id");
                    ackError("取消其他出库单失败:$error_msg");
                }
                else
                {
                    if (!$db->execute("UPDATE outside_wms_order SET wms_status=0,error_info = '',outer_no = '' where order_id= $stockout_order_id"))
                    {
                        $error_msg = $db->error_msg();
                        logx("outer_no:$outer_no, Update status failed:$error_msg! wms_adapter_cancel_other_out", $sid.'/WMS_CMD');
                        $db->execute("ROLLBACK");
                        $db->execute("update outside_wms_order set wms_status=3,error_info='取消操作执行失败' where order_id=$stockout_order_id");
                        ackError("取消其他出库单失败:WMS已取消成功,系统内单据状态处理失败");
                    }
                    else
                    {
                        $db->execute("INSERT INTO outside_wms_order_log(order_id,operator_id,operate_type,message) VALUES($stockout_order_id,$uid,14,'取消外部WMS其他入库单成功')");
                        logx("outer_no:$outer_no, Cancel success! wms_adapter_cancel_other_out", $sid.'/WMS_CMD');
                        $db->execute("COMMIT");
                        ackOk(0);
                    }
                }
            }
            else
            {
                $error_msg = $db->error_msg();
                logx("outer_no:$outer_no, Call I_STOCK_OUTSIDE_WMS_REVERT failed:$error_msg! wms_adapter_cancel_other_out",$sid.'/WMS_CMD');
                $db->execute("ROLLBACK");
                $db->execute("update outside_wms_order set wms_status=3,error_info='取消操作执行失败' where order_id=$stockout_order_id");
                ackError("取消其他出库单失败:系统内单据处理失败");
            }
        }
        releaseDb($db);
        exit(0);
    }

    //推送批量商品信息
    function wms_adapter_add_specs($sid,$uid,$warehouse_id,$spec_ids)
    {
        $db = getUserDb($sid);
        if(!$db)
        {
            logx("GetUserDb failed! wms_adapter_add_specs",$sid.'/WMS_CMD');
            ackError('上传货品信息失败:服务器内部错误');

            return false;
        }

        if(empty($warehouse_id))
        {
            releaseDb($db);
            ackError('上传货品信息失败:请指定仓库');

            return false;
        }

        $wms_info = $db->query_result("select * from sys_warehouse where warehouse_id=$warehouse_id");
        $wms_type = $wms_info['type'];
        $wms_infos = json_decode($wms_info['api_key'],true);
        $api_url = $wms_infos['api_url'];
        if($wms_type<5)
        {
            logx("Warehouse[$wms_type] not support! wms_adapter_add_specs",$sid.'/WMS_CMD');
            releaseDb($db);
            ackError('上传商品信息失败:仓库类型不支持');

            return false;
        }

        $wms_info_type = isset($wms_infos['wms_type'])?$wms_infos['wms_type']:'';
        $method = WMS_METHOD_SKUS_ADD;
        $error_info = '';
        $result = WmsAdapter::getTransferFlag($wms_type, $wms_info_type, $method, $error_info,$api_url);
        if($result == 0)
        {
            releaseDb($db);
            wms_adapter_add_spec($sid,$uid,$warehouse_id,$spec_ids);

            return;
        }

        //残品仓如果被匹配过，则不允许推送商品信息
        if ($wms_info['is_defect'] == 1 && array_key_exists('match_warehouse_id',$wms_info) )
        {
            $match_count = $db->query_result_single("select count(1) from sys_warehouse where match_warehouse_id = $warehouse_id and is_disabled = 0 ");
            if ($match_count > 0 )
            {
                logx("warehouse_id:$warehouse_id, The warehouse is defect and matched,not allow to push! wms_adapter_add_specs", $sid.'/WMS_CMD');
                releaseDb($db);
                ackError('上传商品信息失败:残品仓已被正品仓匹配,不允许推送货品信息,请在对应正品仓中推送');

                return false;
            }
            $db->free_result($match_count);
        }

        //同时更新对应残次品仓的货品信息
        $match_warehouse_id = 0;
        if(array_key_exists('match_warehouse_id',$wms_info) && $wms_info['match_warehouse_id'] != 0)
        {
            $match_warehouse_id = $wms_info['match_warehouse_id'];
        }

        $wms_name = $wms_info['name'];
        $api_object_id = $wms_info['api_object_id'];

        $wms_info = json_decode($wms_info['api_key'],true);
        if(empty($wms_info))
        {
            logx("warehouse_id:$warehouse_id, The warehouse-api_key is empty! wms_adapter_add_specs",$sid.'/WMS_CMD');
            releaseDb($db);
            ackError('上传商品信息失败:仓库授权信息为空');

            return false;
        }

        $ext_sql = ' ';
        if(!empty($spec_ids))
        {
            $ext_sql .= " and ss.spec_id in({$spec_ids}) ";
        }
        $spec_list = $db->query(" select gs.spec_no, gs.spec_id, gs.spec_code, gs.spec_name, gg.goods_no, gg.short_name, gg.goods_name, gs.remark ,gg.remark as goodRemark,gg.goods_type,gg.prop1,gg.prop2,gg.prop3, ".
            " gg.class_id, gc.class_name, gg.brand_id, gb.brand_no, gb.brand_name, gg.origin, gs.length, gs.width, gs.height, gs.validity_days,".
            " gs.prop1 as specProp1, gs.prop2 as specProp2, gs.prop3 as specProp3, gs.prop4 as specProp4,gs.prop5 as specProp5,gs.prop6 as specProp6,gg.prop1 as goodsProp1,gg.prop2 as goodsProp2,gg.prop3 as goodsProp3,gg.prop4 as goodsProp4,gg.prop5 as goodsProp5,gg.prop6 as goodsProp6,".
            " gs.weight,gs.retail_price,gs.barcode,ss.spec_wh_no2,cgu.name as unitName,cgau.name as auxUnitName,cgau.base_ratio as auxUnitRatio ".
            " from stock_spec ss ".
            " left join goods_spec gs using(spec_id)".
            " left join goods_goods gg using(goods_id) ".
            " left join goods_class gc using(class_id) ".
            " left join goods_brand gb using(brand_id) ".
            " left join cfg_goods_unit cgu on gs.unit = cgu.rec_id ".
            " left join cfg_goods_aux_unit cgau on gg.aux_unit = cgau.rec_id ".
            " where ss.warehouse_id=$warehouse_id and ss.status = 1 and gs.deleted = 0 $ext_sql ");
        if(!$spec_list)
        {
            $error_msg = $db->error_msg();
            logx("Get spec info failed:$error_msg! wms_adapter_add_specs!",$sid.'/WMS_CMD');
            releaseDb($db);
            ackError('上传商品信息失败:获取商品信息失败');

            return false;
        }
        if ($spec_list->num_rows == 0)
        {
            logx("Goods not exists! wms_adapter_add_specs!",$sid.'/WMS_CMD');
            releaseDb($db);
            ackError('上传商品信息失败:没有获取到商品信息');

            return false;
        }
        $cols = array("spec_barcode"=>TYPE_DT_STRING, "wh_name"=>TYPE_DT_STRING, "msg"=>TYPE_DT_STRING);
        $rows = array();
        $success = true;
        $wms_adapter = new WmsAdapter($wms_type, $wms_info);

        $num = $spec_list->num_rows;
        if($num > 200)
        {
            releaseDb($db);
            ackError('当前选择了'.$num.'条数据，批量上传最大支持200条数据，请重新选择');

            return false;
        }

        $spec_info   = array();
        $spec_arrids = array();
        while ($spec = $db->fetch_array($spec_list))
        {
            $spec_arrids[$spec['spec_no']] = $spec['spec_id'];
            $spec['api_object_id'] = $api_object_id;
            $spec_info[] = $spec ;
        }
        logx("==begin push===",$sid.'/WMS_CMD');
        $result = $wms_adapter->sendRequest(WMS_METHOD_SKUS_ADD, array('spec' => $spec_info));
        $send   = $wms_adapter->getSendParams();
        $resv   = $wms_adapter->getReceived();

        logx("send:    ".print_r($send,true),$sid.'/WMS_CMD');
        logx("receive: ".print_r($resv,true),$sid.'/WMS_CMD');
        $code = $result['code'];
        $error_msg = $result['error_msg'];
        $items = isset($result['items'])?$result['items']:'';

        if(mb_strlen($error_msg,'utf-8') > 200)//如果超过长度则截取
        {
            $error_msg = mb_substr($error_msg,0,200,"utf-8");
        }
        //$wms_specode = isset($result['rev_info'])?$result['rev_info']:'';

        if($code != 0)//失败
        {
            if(!empty($items))
            {
                foreach($items as $info)
                {
                    $spec_no   = $info['itemCode'];
                    $error_msg = $info['message'];
                    $rows[] = array(strval($spec_no),$wms_name,$error_msg);
                    logx("spec_no:$spec_no, error message:$error_msg! wms_adapter_add_specs", $sid.'/WMS_CMD');
                    unset($spec_arrids[$spec_no]);
                }
            }
            else
            {
                foreach($spec_arrids as $spec_no => $spec_id)
                {
                    $rows[] = array(strval($spec_no),$wms_name,$error_msg);
                }
                logx("error message:{$error_msg},本次上传全部失败",$sid.'/WMS_CMD');
                unset($spec_arrids);
            }
            $success = false;
        }
        if(!empty($spec_arrids))
        {
            //$wms_specode = $db->escape_string($wms_specode);

            foreach ($spec_arrids as $spec_no => $spec_id)
            {
                if(!$db->query("UPDATE stock_spec SET spec_wh_no='$spec_no' where warehouse_id in ($warehouse_id,$match_warehouse_id) and spec_id=$spec_id"))
                {
                    $error_msg = $db->error_msg();
                    logx("spec_no:$spec_no, Update db failed:$error_msg! wms_adapter_add_specs", $sid.'/WMS_CMD');
                    $rows[]  = array(strval($spec_no),$wms_name,$error_msg);
                    $success = false;
                }
            }
        }
        releaseDb($db);

        if($success)
        {
            ackOk(0);
        }
        else
        {
            ackResult($cols, $rows);
            exit(0);
        }
    }

    //同步商品信息(单个上传)
    function wms_adapter_add_spec($sid,$uid,$warehouse_id,$spec_ids )
    {
        $db = getUserDb($sid);
        if(!$db)
        {
            logx("GetUserDb failed! wms_adapter_add_spec",$sid.'/WMS_CMD');
            ackError('上传货品信息失败:服务器内部错误');

            return false;
        }

        if(empty($warehouse_id))
        {
            releaseDb($db);
            ackError('上传货品信息失败:请指定仓库');

            return false;
        }

        $wms_info = $db->query_result("select * from sys_warehouse where warehouse_id=$warehouse_id");
        $wms_type = $wms_info['type'];
        if($wms_type<5)
        {
            logx("Warehouse[$wms_type] not support! wms_adapter_add_spec",$sid.'/WMS_CMD');
            releaseDb($db);
            ackError('上传商品信息失败:仓库类型不支持');

            return false;
        }

        //残品仓如果被匹配过，则不允许推送商品信息
        if ($wms_info['is_defect'] == 1 && array_key_exists('match_warehouse_id',$wms_info) )
        {
            $match_count = $db->query_result_single("select count(1) from sys_warehouse where match_warehouse_id = $warehouse_id and is_disabled = 0 ");
            if ($match_count > 0 )
            {
                logx("warehouse_id:$warehouse_id, The warehouse is defect and matched,not allow to push! ", $sid.'/WMS_CMD');
                releaseDb($db);
                ackError('上传商品信息失败:该残次品仓已被正品仓匹配，不允许推送货品信息，请在对应正品仓中推送');

                return false;
            }
            $db->free_result($match_count);
        }

        //同时更新对应残次品仓的货品信息
        $match_warehouse_id = 0;
        if(array_key_exists('match_warehouse_id',$wms_info) && $wms_info['match_warehouse_id'] != 0)
        {
            $match_warehouse_id = $wms_info['match_warehouse_id'];
        }

        $wms_name      = $wms_info['name'];
        $api_object_id = $wms_info['api_object_id'];

        $wms_info = json_decode($wms_info['api_key'],true);
        if(empty($wms_info))
        {
            logx("warehouse_id:warehouse_id, The warehouse-api_key is empty! wms_adapter_add_spec",$sid.'/WMS_CMD');
            releaseDb($db);
            ackError('上传商品信息失败:仓库授权信息为空');

            return false;
        }

        $ext_sql = ' ';
        if(!empty($spec_ids))
        {
            $ext_sql .= " and ss.spec_id in({$spec_ids}) ";
        }
        $spec_list = $db->query(" select gs.spec_no, gs.spec_id, gs.spec_code, gs.spec_name, gg.goods_no, gg.short_name, gg.goods_name, gs.remark ,gg.remark as goodRemark,gg.goods_type,gg.prop1,gg.prop2,gg.prop3, ".
            " gg.class_id, gc.class_name, gg.brand_id, gb.brand_no, gb.brand_name, gg.origin, gs.length, gs.width, gs.height, gs.validity_days,".
            " gs.prop1 as specProp1, gs.prop2 as specProp2, gs.prop3 as specProp3, gs.prop4 as specProp4,gs.prop5 as specProp5,gs.prop6 as specProp6,gg.prop1 as goodsProp1,gg.prop2 as goodsProp2,gg.prop3 as goodsProp3,gg.prop4 as goodsProp4,gg.prop5 as goodsProp5,gg.prop6 as goodsProp6,".
            " gs.weight,gs.retail_price,gs.barcode,ss.spec_wh_no,ss.spec_wh_no2,cgu.name as unitName,cgau.name as auxUnitName,cgau.base_ratio as auxUnitRatio ".
            " from stock_spec ss ".
            " left join goods_spec gs using(spec_id)".
            " left join goods_goods gg using(goods_id) ".
            " left join goods_class gc using(class_id) ".
            " left join goods_brand gb using(brand_id) ".
            " left join cfg_goods_unit cgu on gs.unit = cgu.rec_id ".
            " left join cfg_goods_aux_unit cgau on gg.aux_unit = cgau.rec_id ".
            " where ss.warehouse_id=$warehouse_id and ss.status = 1 and gs.deleted = 0 $ext_sql ");
        if(!$spec_list)
        {
            $error_msg = $db->error_msg();
            logx("Get spec info failed:$error_msg! wms_adapter_add_spec!",$sid.'/WMS_CMD');
            releaseDb($db);
            ackError('上传商品信息失败:获取商品信息失败');

            return false;
        }
        if($spec_list->num_rows == 0)
        {
            logx("Goods not exists! wms_adapter_add_spec!",$sid.'/WMS_CMD');
            releaseDb($db);
            ackError('上传商品信息失败:没有获取到商品信息');

            return false;
        }
        $cols = array("spec_barcode"=>TYPE_DT_STRING, "wh_name"=>TYPE_DT_STRING, "msg"=>TYPE_DT_STRING);
        $rows = array();
        $success = true;
        $wms_adapter = new WmsAdapter($wms_type, $wms_info);
        while($spec = $db->fetch_array($spec_list))
        {
            $spec_id = $spec['spec_id'];
            $spec_no = $spec['spec_no'];

            $spec['api_object_id'] = $api_object_id;
            if($wms_type == 6 && $sid == 'lyf2')
            {
                //360sku 来伊份货品编码问题，需要用货品编号作为唯一标识
                $spec['spec_no'] = $spec['goods_no'];
            }

            //心怡仓储要求维护商品平台url用于海关校验(随意一条url即可)
            if ($wms_type == 13)
            {
                $api_goods_info       = xy_get_api_info($db,'api_goods_info',$spec_id);
                $spec['api_goods_no'] = isset($api_goods_info['outer_id'])?$api_goods_info['outer_id']:'';
            }

            logx("==begin push===",$sid.'/WMS_CMD');
            $result = $wms_adapter->sendRequest(WMS_METHOD_SKU_ADD, array('spec' => $spec));

            $send   = $wms_adapter->getSendParams();
            $resv   = $wms_adapter->getReceived();

            logx("send:    ".print_r($send,true),$sid.'/WMS_CMD');
            logx("receive: ".print_r($resv,true),$sid.'/WMS_CMD');
            $code = $result['code'];
            $error_msg = $result['error_msg'];
            if(mb_strlen($error_msg,'utf-8') > 200)//如果超过长度则截取
            {
                $error_msg = mb_substr($error_msg,0,200,"utf-8");
            }
            $wms_specode = isset($result['rev_info'])?$result['rev_info']:'';
            if($code != 0)//失败
            {
                if($code<0)
                {
                    logx("spec_no:$spec_no, 系统级别错误system_error:$error_msg! wms_adapter_add_spec", $sid.'/WMS_CMD');
                }
                else
                {
                    logx("spec_no:$spec_no, 应用级别错误system_error:$error_msg! wms_adapter_add_spec", $sid.'/WMS_CMD');
                }
                $rows[] = array(strval($spec_no),$wms_name,$error_msg);
                $success = false;
            }
            else//成功
            {
                if(mb_strlen($wms_specode,'utf-8') > 40)//编码超长
                {
                    $wms_specode = '';
                    logx("spec_no:$spec_no, WMS返回WMS货品编码超长! wms_adapter_add_spec",$sid.'/WMS_CMD');
                }
                $wms_specode = $db->escape_string($wms_specode);
                if(!$db->query("UPDATE stock_spec SET spec_wh_no='$spec_no',spec_wh_no2='$wms_specode' where warehouse_id in ($warehouse_id,$match_warehouse_id) and spec_id=$spec_id"))
                {
                    $error_msg = $db->error_msg();
                    logx("spec_no:$spec_no, Update db failed:$error_msg! wms_adapter_add_spec", $sid.'/WMS_CMD');
                    $rows[] = array(strval($spec_no),$wms_name,$error_msg);
                }
                else
                {
                    logx("spec_no:$spec_no, Push success! wms_apater_add_spec", $sid.'/WMS_CMD');
                }
            }
        }

        releaseDb($db);

        if($success)
        {
            ackOk(0);
        }
        else
        {
            ackResult($cols, $rows);
            exit(0);
        }

    }

    function wms_adapter_get_logistics($sid,$uid,$warehouse_id)
    {
        $db = getUserDb($sid);
        if(!$db)
        {
            logx("wms_adapter_get_logistics getUserDb failed!!", $sid.'/WMS_CMD');
            ackError('服务器内部错误');
            return;
        }

        $warehouse_id = (int)$warehouse_id;

        $warehouse = $db->query_result("select type, api_key from sys_warehouse where warehouse_id={$warehouse_id} and is_disabled=0");
        if(!$warehouse)
        {
            releaseDb($db);
            logx("query warehouse failed in wms_adapter_get_logistics!", $sid.'/WMS_CMD');
            ackError('仓库没启用');
            return false;
        }
        $wms_type = $warehouse['type'];
        if($wms_type<5)
        {
            logx("warehouse_type is error cur_type:$wms_type",$sid.'/WMS_CMD');
            ackError('仓库类型不支持');
            return;
        }
        $wms_info = json_decode($warehouse['api_key'],true);
        if(empty($wms_info))
        {
            releaseDb($db);
            logx("query warehouse failed in wms_adapter_get_logistics!", $sid.'/WMS_CMD');
            ackError('仓库未授权');
            return false;
        }

        $success = true;
        $wms_adapter = new WmsAdapter($wms_type, $wms_info);
        $logistics_companies = $wms_adapter->getWmsLogistics();
        if(count($logistics_companies) > 0)
        {
            foreach($logistics_companies as $idx =>$logistics)
            {
                $logistics_companies[$idx]['warehouse_id'] = $warehouse_id;
            }
            if( !$db->execute('BEGIN') ||
                !$db->execute("update cfg_api_logistics_wms set flag=1 where warehouse_id={$warehouse_id} AND flag<>2") ||
                !putDataToTable($db, 'cfg_api_logistics_wms', $logistics_companies, 'on duplicate key update flag=0') ||
                !$db->execute('DELETE cls FROM cfg_logistics_wms cls,cfg_api_logistics_wms alw'
                    . ' WHERE alw.flag=1 AND cls.warehouse_id=alw.warehouse_id AND cls.logistics_code=alw.logistics_code') ||
                !$db->execute("delete from cfg_api_logistics_wms where warehouse_id={$warehouse_id} and flag=1") ||
                !$db->execute('COMMIT'))
            {
                $db->execute('ROLLBACK');

                $success = false;
                ackError("保存数据失败");
            }

            //刷新物流映射
            $db->execute("INSERT IGNORE INTO cfg_logistics_wms(warehouse_id,logistics_code,logistics_id,cod_support,created) "
                . "SELECT {$warehouse_id},alw.logistics_code,alw.logistics_id,alw.cod_support,NOW() "
                . "FROM cfg_api_logistics_wms alw INNER JOIN dict_logistics_code lc ON (lc.platform_id={$wms_type} and lc.type = 2 and alw.logistics_code=lc.logistics_code) "
                . "WHERE alw.warehouse_id={$warehouse_id}");

            $db->execute("INSERT IGNORE INTO cfg_logistics_wms(warehouse_id,logistics_code,logistics_id,cod_support,created) "
                . "SELECT {$warehouse_id},alw.logistics_code,cl.logistics_id,alw.cod_support,NOW() "
                . "FROM cfg_api_logistics_wms alw INNER JOIN cfg_logistics cl ON (cl.logistics_name=alw.name) "
                . "WHERE alw.warehouse_id={$warehouse_id} AND (cl.logistics_type = 1311 OR cl.bill_type = 0)");
        }
        releaseDb($db);

        if($success)
            ackOk(0);
    }

    /*查询出库单状态*/
    function wms_adapter_check_order_status($sid, $uid, $stockout_id)
    {
        //连接卖家服务器
        $db = getUserDb($sid);
        if(!$db)
        {
            logx("wms_adapter_check_order_status getUserDb failed!!", $sid.'/WMS_CMD');
            ackError('服务器内部错误');
            return;
        }

        //获取其他出库单信息
        $sale_order_info = $db->query_result("select so.outer_no,sw.api_key,sw.ext_warehouse_no,sw.type as wms_type ".
            "from stockout_order so  ".
            "left join sys_warehouse sw ON so.warehouse_id = sw.warehouse_id ".
            "where so.stockout_id={$stockout_id}");
        if(!$sale_order_info)
        {
            releaseDb($db);
            logx("stockout_id:$stockout_id, Order not exists! wms_adapter_check_order_status",$sid.'/WMS_CMD');
            ackError('查询出库单状态失败:没有获取到其他出库单信息');
            return ;
        }


        $data['stockout_info'] = $sale_order_info;
        $wms_info = json_decode($sale_order_info['api_key'],true);
        $wms_type = $sale_order_info['wms_type'];

        //推送
        $wms_adapter = new WmsAdapter($wms_type, $wms_info);

        logx("==begin push===",$sid.'/WMS_CMD');
        $result = $wms_adapter->sendRequest(WMS_METHOD_STOCKOUT_CHECK, $data);
        $send   = $wms_adapter->getSendParams();
        $resv   = $wms_adapter->getReceived();

        logx("send:    ".print_r($send,true),$sid.'/WMS_CMD');
        logx("receive: ".print_r($resv,true),$sid.'/WMS_CMD');

        //反馈信息解析
        $code = $result['code'];
        $error_msg = $result['error_msg'];
        if(mb_strlen($error_msg,'utf-8') > 200)//如果超过长度则截取
        {
            $error_msg = mb_substr($error_msg,0,200,"utf-8");
        }
        logx("result: ".print_r($result,true),$sid.'/WMS_CMD');

        //反馈信息解析
        if($code != 0)//失败
        {
            if($code<0)
            {
                logx("stockout_id: {$stockout_id}, 系统级别错误system_error: {$error_msg} in wms_adapter_check_order_status", $sid.'/WMS_CMD');
            }
            else
            {
                logx("stockout_id: {$stockout_id}, 应用级别错误app_error: {$error_msg} in wms_adapter_check_order_status", $sid.'/WMS_CMD');
            }
            ackError("查询委外出库单状态失败：{$error_msg}");
        }
        else//成功
        {
            logx("查询委外出库单状态成功 in wms_adapter_check_order_status", $sid.'/WMS_CMD');
            ackOk(0);
        }
        releaseDb($db);
    }

    /*修改商品信息*/
    function wms_adapter_modify_spec($sid, $uid, $warehouse_id, $spec_id)
    {
        $db = getUserDb($sid);
        if(!$db)
        {
            logx("GetUserDb failed! wms_adapter_modify_spec",$sid.'/WMS_CMD');
            ackError('更新货品信息失败:服务器内部错误');

            return false;
        }

        if(empty($warehouse_id))
        {
            releaseDb($db);
            ackError('更新货品信息失败:请指定仓库');

            return false;
        }

        $wms_info = $db->query_result("select * from sys_warehouse where warehouse_id=$warehouse_id");
        $wms_type = $wms_info['type'];
        if($wms_type<5)
        {
            logx("Warehouse[$wms_type] not support! wms_adapter_modify_spec",$sid.'/WMS_CMD');
            releaseDb($db);
            ackError('更新商品信息失败:仓库类型不支持');

            return false;
        }

        //残品仓如果被匹配过，则不允许修改商品信息
        if ($wms_info['is_defect'] == 1 && array_key_exists('match_warehouse_id',$wms_info) )
        {
            $match_count = $db->query_result_single("select count(1) from sys_warehouse where match_warehouse_id = $warehouse_id and is_disabled = 0 ");
            if ($match_count > 0 )
            {
                logx("warehouse_id:$warehouse_id, The warehouse is defect and matched,not allow to push! wms_adapter_modify_spec", $sid.'/WMS_CMD');
                releaseDb($db);
                ackError('更新商品信息失败:该残次品仓已被正品仓匹配，不允许更新货品信息，请在对应正品仓中更新');

                return false;
            }
            $db->free_result($match_count);
        }

        //同时更新对应残次品仓的货品信息
        $match_warehouse_id = 0;
        if(array_key_exists('match_warehouse_id',$wms_info) && $wms_info['match_warehouse_id'] != 0)
        {
            $match_warehouse_id = $wms_info['match_warehouse_id'];
        }

        $wms_name = $wms_info['name'];
        $api_object_id = $wms_info['api_object_id'];

        $wms_info = json_decode($wms_info['api_key'],true);
        if(empty($wms_info))
        {
            logx("warehouse_id:$warehouse_id, The warehouse-api_key is empty! wms_adapter_modify_spec",$sid.'/WMS_CMD');
            releaseDb($db);
            ackError('更新商品信息失败:仓库授权信息为空');

            return false;
        }

        $spec_list = $db->query("select gs.spec_no, gs.spec_id, gs.spec_code, gs.spec_name, gg.goods_no, gg.short_name, gg.goods_name, gs.remark ,gg.goods_type ,gg.remark as goodRemark, ".
            "gg.class_id, gc.class_name, gg.brand_id, gb.brand_no, gb.brand_name, gg.origin, ".
            "gs.length, gs.width, gs.height, gs.validity_days, ".
            "gs.weight,gs.retail_price,gs.barcode,ss.spec_wh_no,ss.spec_wh_no2 ,cgau.name as auxUnitName,cgau.base_ratio as auxUnitRatio, gs.prop1 as specProp1, gs.prop2 as specProp2, gs.prop3 as specProp3, gs.prop4 as specProp4,gs.prop5 as specProp5,gs.prop6 as specProp6,gg.prop1 as goodsProp1,gg.prop2 as goodsProp2,gg.prop3 as goodsProp3,gg.prop4 as goodsProp4,gg.prop5 as goodsProp5,gg.prop6 as goodsProp6  ".
            "from stock_spec ss ".
            "left join goods_spec gs using(spec_id)".
            "left join goods_goods gg using(goods_id) ".
            "left join goods_class gc using(class_id) ".
            "left join goods_brand gb using(brand_id) ".
            "left join cfg_goods_aux_unit cgau on gg.aux_unit = cgau.rec_id ".
            "where ss.warehouse_id=$warehouse_id and ss.status = 1 and gs.deleted = 0 and ss.spec_id = $spec_id ");
        if(!$spec_list)
        {
            $error_msg = $db->error_msg();
            logx("spec_id:$spec_id, Get spec info failed:$error_msg! wms_adapter_modify_spec!",$sid.'/WMS_CMD');
            releaseDb($db);
            ackError('更新商品信息失败:获取商品信息失败');

            return false;
        }
        if($spec_list->num_rows == 0)
        {
            logx("spec_id:$spec_id, Goods not exists! wms_adapter_modify_spec!",$sid.'/WMS_CMD');
            releaseDb($db);
            ackError('更新商品信息失败:没有获取到商品信息');

            return false;
        }

        $cols = array("spec_barcode"=>TYPE_DT_STRING, "wh_name"=>TYPE_DT_STRING, "msg"=>TYPE_DT_STRING);
        $rows = array();
        $success = true;
        $wms_adapter = new WmsAdapter($wms_type, $wms_info);
        while($spec = $db->fetch_array($spec_list))
        {
            $spec_id = $spec['spec_id'];
            $spec_no = $spec['spec_no'];

            $spec['api_object_id'] = $api_object_id;

            logx("==begin push===",$sid.'/WMS_CMD');
            $result = $wms_adapter->sendRequest(WMS_METHOD_SKU_MODIFY, array('spec' => $spec));
            $send   = $wms_adapter->getSendParams();
            $resv   = $wms_adapter->getReceived();

            logx("send:    ".print_r($send,true),$sid.'/WMS_CMD');
            logx("receive: ".print_r($resv,true),$sid.'/WMS_CMD');
            $code = $result['code'];
            $error_msg = $result['error_msg'];

            if(mb_strlen($error_msg,'utf-8') > 200)//如果超过长度则截取
            {
                $error_msg = mb_substr($error_msg,0,200,"utf-8");
            }
            $wms_specode = isset($result['rev_info'])?$result['rev_info']:'';
            if($code != 0)//失败
            {
                if($code<0)
                {
                    logx("spec_no:$spec_no, 系统级别错误system_error:$error_msg! wms_adapter_modify_spec", $sid.'/WMS_CMD');
                }
                else
                {
                    logx("spec_no:$spec_no, 应用级别错误system_error:$error_msg! wms_adapter_modify_spec", $sid.'/WMS_CMD');
                }
                $rows[] = array($spec_no,$wms_name,$error_msg);
                $success = false;
            }
            else//成功
            {
                if(mb_strlen($wms_specode,'utf-8') > 40)//编码超长
                {
                    $wms_specode = '';
                    logx("spec_no:$spec_no, WMS返回WMS货品编码超长! wms_adapter_modify_spec",$sid.'/WMS_CMD');
                }
                $wms_specode = $db->escape_string($wms_specode);
                if(!$db->query("UPDATE stock_spec SET spec_wh_no='$spec_no',spec_wh_no2='$wms_specode' where warehouse_id in ($warehouse_id,$match_warehouse_id)  and spec_id=$spec_id"))
                {
                    $error_msg = $db->error_msg();
                    logx("spec_no:$spec_no, Update db failed:$error_msg! wms_adapter_modify_spec", $sid.'/WMS_CMD');
                    $rows[] = array($spec_no,$wms_name,$error_msg);
                }
                else
                {
                    logx("spec_no:$spec_no, Modify success! wms_adapter_modify_spec", $sid.'/WMS_CMD');
                }
            }
        }
        releaseDb($db);

        if($success)
        {
            ackOk(0);
        }
        else
        {
            ackResult($cols, $rows);
            exit(0);
        }
    }

    //防止采购单和退货单重复推送
    function myTestPid($method, $return=false)
    {
        $lock_pid = fopen(ROOT_DIR . "/pids/outer-{$method}.pid", 'a+');
        if($lock_pid)
        {
            if(flock($lock_pid, LOCK_EX|LOCK_NB))
            {
                if(!$return)
                {
                    fclose($lock_pid);
                    return true;
                }

                return $lock_pid;
            }
            fclose($lock_pid);
        }

        return false;
    }

    function xy_get_api_info($db,$type,$target_id)
    {
        if ($type == 'api_goods_info')
        {
            //先查询单品平台编码
            $goodsInfo = $db->query_result("select outer_id, spec_outer_id from api_goodsspec where is_deleted = 0 and match_target_type = 1 and match_target_id = {$target_id} limit 1");
            //如果单品找不到，则尝试寻找包含该单品的组合装的平台编码
            if (empty($goodsInfo))
            {
                $goodsInfo = $db->query_result(" select ags.outer_id, ags.spec_outer_id ".
                    " from api_goodsspec ags ".
                    " left join goods_suite_detail gsd on ags.match_target_id = gsd.suite_id ".
                    " where ags.is_deleted = 0 and ags.match_target_type = 2 ".
                    " and gsd.spec_id = $target_id limit 1");
                if (empty($goodsInfo))
                {
                    return '';
                }
            }
            return $goodsInfo;
        }
        if ($type = 'api_trade_info')
        {
            $tradeInfo = $db->query_result(" select at.pay_id,at.pay_account,at.buyer_name,at.id_card_type,at.id_card,at.cust_data".
                " from sales_trade_order sto ".
                " left join api_trade at on sto.platform_id = at.plateform_id and sto.src_tid = at.tid ".
                " where sto.plateform_id >0 and sto.trade_id = $target_id limit 1");
            //从自定义字段中解析出买家手机号和收件人证件号
            if (!empty($tradeInfo['cust_data']))
            {
                $cust_info = json_decode($tradeInfo,true);
                if ($cust_info)
                {
                    $tradeInfo['tax']              = $cust_info['tax'];
                    $tradeInfo['buyer_phone']      = $cust_info['buyer_phone'];
                    $tradeInfo['receiver_id_card'] = $cust_info['receiver_id_card'];
                }

            }
            return $tradeInfo;
        }
    }

    //TOP创建门店（非批量）  卖家账号 ，  当前操作员，平台店铺id, 系统内门店id
    function adapter_add_top_store($sid, $uid, $erp_store_id)
    {

        $lock_pid = myTestPid("adapter_add_top_store",true);
        if(!$lock_pid)
        {
            logx(" adapter_add_top_store  myTestPid  失败!!", $sid.'/WMS_CMD');
            ackError('有门店正在推送，请稍后刷新界面重试');
            return;
        }


        //连接卖家服务器
        $db = getUserDb($sid);
        if(!$db)
        {
            logx("getUserDb failed in adapter_add_top_store!!",$sid.'/WMS_CMD');
            ackError('服务器内部错误');
            return ;
        }


        //获取要新增的门店信息
        $store_info = $db->query_result("select csws.*,ss.account_id from cfg_shop_warehouse_store csws ".
            " left join sys_shop ss on ss.shop_id=csws.shop_id where csws.store_id = $erp_store_id ");
        if (!$store_info)
        {
            logx("adapter_add_top_shop get store_info error!", $sid.'/WMS_CMD');
            //$db->execute("UPDATE cfg_shop_warehouse_store SET status = 2 WHERE store_id = $erp_store_id ");
            ackError("获取门店信息失败");
            return false;
        }

        // 0 待推送 1  推送成功  2  推送失败
        if($store_info['status'] == 1)
        {
            ackError("门店已推送至平台");
            return false;
        }

        $wms_info['account_id'] = $store_info['account_id'];
        //调用适配器推送数据 门店
        $wms_adapter = new WmsAdapter(-1, $wms_info);

        logx("==begin push===",$sid.'/WMS_CMD');
        $result = $wms_adapter->sendRequest(TOP_ADD_STORE, $store_info);
        $send   = $wms_adapter->getSendParams();
        $resv   = $wms_adapter->getReceived();

        //记录日志
        logx("send:    ".print_r($send,true),$sid.'/WMS_CMD');
        logx("receive: ".print_r($resv,true),$sid.'/WMS_CMD');

        //对反馈信息做处理，成功，失败
        $code = $result['code'];
        $error_msg = $result['error_msg'];
        if(mb_strlen($error_msg,'utf-8') > 254)//如果超过长度则截取
        {
            $error_msg = mb_substr($error_msg,0,200,"utf-8");
        }
        $rev_info = isset($result['rev_info'])?$result['rev_info']:'';

        if(mb_strlen($rev_info,'utf-8') > 254)//如果超过长度则截取
        {
            $error_msg = mb_substr($error_msg, 0, 200, "utf-8");
        }

        //失败 ，弹窗
        if($code != 0)
        {
            if($code<0)
            {
                logx("shop_id: {$store_info['store_id']}, 系统级别错误system_error: {$error_msg} in adapter_add_top_store", $sid.'/WMS_CMD');
            }
            else
            {
                logx("shop_id: {$store_info['store_id']}, 应用级别错误app_error: {$error_msg} in adapter_add_top_store", $sid.'/WMS_CMD');
            }
            ackError("同步门店到平台失败：{$error_msg}");
        }

        // 成功
        else
        {
            if(!$db->query("UPDATE cfg_shop_warehouse_store SET status = 1,store_platform_id={$rev_info} where store_id = $erp_store_id  "))
            {
                logx("shop_id: {$store_info['store_id']},error: {$error_msg} when update db failed in adapter_add_top_store", $sid.'/WMS_CMD');
                ackError("同步成功，更新门店同步状态失败");
                return;
            }
            else
            {
                $db->execute("INSERT INTO cfg_shop_warehouse_store_log(store_id,operator_id,type,message) VALUES($erp_store_id,$uid,4,'绑定门店成功')");
                logx("shop_id: {$store_info['store_id']}, success in adapter_add_top_store", $sid.'/WMS_CMD');
                //成功提示
                ackError("{$error_msg}, 请刷新页面查看");
                //ackOk(0);
            }
        }
        releaseDb($db);
    }

    //TOP更新门店 卖家账号 ，  当前操作员，平台店铺id, 系统内门店id
    function adapter_modify_top_store($sid,$uid, $erp_store_id)
    {
        $lock_pid = myTestPid("adapter_modify_top_store",true);
        if(!$lock_pid)
        {
            logx(" adapter_modify_top_store  myTestPid  失败!!", $sid.'/WMS_CMD');
            ackError('有门店正在更新，请稍后刷新界面重试');
            return;
        }

        //连接卖家服务器
        $db = getUserDb($sid);
        if(!$db)
        {
            logx("getUserDb failed in adapter_modify_top_store!!",$sid.'/WMS_CMD');
            ackError('服务器内部错误');
            return ;
        }

        //获取要新增的门店信息
        $store_info = $db->query_result("select csws.*,ss.account_id from cfg_shop_warehouse_store csws ".
            " left join sys_shop ss on ss.shop_id=csws.shop_id where csws.store_id = $erp_store_id ");
        if (!$store_info)
        {
            logx("adapter_modify_top_store get store_info error!", $sid.'/WMS_CMD');
            ackError("获取门店信息失败");
            return false;
        }

        // 0 待推送 1  推送成功  2  推送失败
        if($store_info['status'] != 1)
        {
            ackError("请先同步门店至平台");
            return false;
        }

        $wms_info['account_id'] = $store_info['account_id'];
        //调用适配器推送数据 门店
        $wms_adapter = new WmsAdapter(-1, $wms_info);

        logx("==begin push===",$sid.'/WMS_CMD');
        $result = $wms_adapter->sendRequest(TOP_MODIFY_STORE, $store_info);
        $send   = $wms_adapter->getSendParams();
        $resv   = $wms_adapter->getReceived();

        //记录日志
        logx("send:    ".print_r($send,true),$sid.'/WMS_CMD');
        logx("receive: ".print_r($resv,true),$sid.'/WMS_CMD');

        //对反馈信息做处理，成功，失败
        $code = $result['code'];
        $error_msg = $result['error_msg'];
        if(mb_strlen($error_msg,'utf-8') > 200)//如果超过长度则截取
        {
            $error_msg = mb_substr($error_msg,0,200,"utf-8");
        }
        $rev_info = isset($result['rev_info'])?$result['rev_info']:'';

        if(mb_strlen($rev_info,'utf-8') > 200)//如果超过长度则截取
        {
            $error_msg = mb_substr($error_msg, 0, 200, "utf-8");
        }
        //失败 ，弹窗
        if($code != 0)
        {
            if($code<0)
            {
                logx("shop_id: {$store_info['store_id']}, 系统级别错误system_error: {$error_msg} in adapter_modify_top_store", $sid.'/WMS_CMD');
            }
            else
            {
                logx("shop_id: {$store_info['store_id']}, 应用级别错误app_error: {$error_msg} in adapter_modify_top_store", $sid.'/WMS_CMD');
            }
            ackError("更新门店信息到平台失败：{$error_msg}");
        }

        // 成功
        else
        {
            $db->execute("INSERT INTO cfg_shop_warehouse_store_log(store_id,operator_id,type,message) VALUES($erp_store_id,$uid,4,'更新绑定信息成功')");
            logx("shop_id: {$store_info['store_id']}, 更新绑定信息成功",$sid.'/WMS_CMD');
            ackError("更新绑定信息成功，请刷新界面查看");

        }
        releaseDb($db);
    }


    //TOP删除门店 卖家账号 ，  当前操作员，平台店铺id, 系统内门店id
    function adapter_delete_top_store($sid,$uid,$erp_store_id)
    {

        $lock_pid = myTestPid("adapter_delete_top_store",true);
        if(!$lock_pid)
        {
            logx(" adapter_delete_top_store  myTestPid  失败!!", $sid.'/WMS_CMD');
            ackError('有门店正在删除绑定，请稍后刷新界面重试');
            return;
        }

        //连接卖家服务器
        $db = getUserDb($sid);
        if(!$db)
        {
            logx("getUserDb failed in adapter_delete_top_store!!",$sid.'/WMS_CMD');
            ackError('服务器内部错误');
            return ;
        }

        //获取要新增的门店信息
        $store_info = $db->query_result("select csws.*,ss.account_id from cfg_shop_warehouse_store csws ".
            " left join sys_shop ss on ss.shop_id=csws.shop_id where csws.store_id = $erp_store_id ");
        if (!$store_info)
        {
            logx("adapter_delete_top_store get store_info error!", $sid.'/WMS_CMD');
            ackError("获取门店信息失败");
            return false;
        }

        // 0 待推送 1  推送成功  2  推送失败
        if($store_info['status'] != 1)
        {
            ackError("门店未绑定平台店铺，无需解绑");
            return false;
        }

        $wms_info['account_id'] = $store_info['account_id'];
        //调用适配器推送数据 门店
        $wms_adapter = new WmsAdapter(-1, $wms_info);

        logx("==begin push===",$sid.'/WMS_CMD');
        $result = $wms_adapter->sendRequest(TOP_DELETE_STORE, $store_info);
        $send   = $wms_adapter->getSendParams();
        $resv   = $wms_adapter->getReceived();

        //记录日志
        logx("send:    ".print_r($send,true),$sid.'/WMS_CMD');
        logx("receive: ".print_r($resv,true),$sid.'/WMS_CMD');

        //对反馈信息做处理，成功，失败
        $code = $result['code'];
        $error_msg = $result['error_msg'];
        if(mb_strlen($error_msg,'utf-8') > 200)//如果超过长度则截取
        {
            $error_msg = mb_substr($error_msg,0,200,"utf-8");
        }
        $rev_info = isset($result['rev_info'])?$result['rev_info']:'';

        if(mb_strlen($rev_info,'utf-8') > 200)//如果超过长度则截取
        {
            $error_msg = mb_substr($error_msg, 0, 200, "utf-8");
        }

        //失败 ，弹窗
        if($code != 0)
        {
            if($code<0)
            {
                logx("shop_id: {$store_info['store_id']}, 系统级别错误system_error: {$error_msg} in adapter_delete_top_store", $sid.'/WMS_CMD');
            }
            else
            {
                logx("shop_id: {$store_info['store_id']}, 应用级别错误app_error: {$error_msg} in adapter_delete_top_store", $sid.'/WMS_CMD');
            }
            ackError("解除门店绑定失败：{$error_msg}");
        }

        // 成功
        else
        {
            if(!$db->query("UPDATE cfg_shop_warehouse_store SET status = 0,store_platform_id=0 where store_id = $erp_store_id  "))
            {
                logx("shop_id: {$store_info['store_id']},error: {$error_msg} when update db failed in adapter_delete_top_store", $sid.'/WMS_CMD');
                ackError("解除绑定成功，更新门店状态失败");
                return;
            }
            else
            {
                $db->execute("INSERT INTO cfg_shop_warehouse_store_log(store_id,operator_id,type,message) VALUES($erp_store_id,$uid,4,'解除绑定门店成功')");
                logx("shop_id: {$store_info['store_id']}, success in adapter_delete_top_store", $sid.'/WMS_CMD');
                //成功提示
                ackError("解除绑定成功，请刷新界面查看");
                //ackOk(0);
            }
        }
        releaseDb($db);
    }

    //TOP商品绑定门店
    function adapter_add_top_store_goods($sid,$uid,$erp_store_id,$goods_ids)
    {

        //连接卖家服务器
        $db = getUserDb($sid);
        if(!$db)
        {
            logx("getUserDb failed in adapter_add_top_store_goods!!",$sid.'/WMS_CMD');
            ackError('服务器内部错误');
            return ;
        }

        //获取门店信息
        $store_info = $db->query_result("select csws.*,ss.account_id from cfg_shop_warehouse_store csws ".
            " left join sys_shop ss on ss.shop_id=csws.shop_id where csws.store_id = $erp_store_id ");
        if (!$store_info)
        {
            logx("adapter_add_top_store_goods get store_info error!", $sid.'/WMS_CMD');
            ackError("获取门店信息失败");
            return false;
        }

        // 0 待推送 1  推送成功  2  推送失败
        if($store_info['status'] != 1)
        {
            ackError("门店未绑定平台店铺");
            return false;
        }


        //获取商品信息
        $ext_sql = ' ';
        if(!empty($goods_ids))
        {
            $ext_sql .= " and goods_id in({$goods_ids}) ";
        }
        $goods_list = $db->query("select * from cfg_shop_warehouse_store_goods where store_id = $erp_store_id $ext_sql ");
        if(!$goods_list)
        {
            releaseDb($db);
            logx("query goods_list failed in adapter_add_top_store_goods!",$sid.'/WMS_CMD');
            ackError('获取商品信息失败');
            return;
        }
        //平台货品id,门店名称，消息列表
        $cols = array("goods_id"=>TYPE_DT_STRING, "store_id"=>TYPE_DT_STRING, "msg"=>TYPE_DT_STRING,"rec_id"=>TYPE_DT_STRING);
        $rows = array();
        $success = true;

        //调用适配器推送数据 门店
        $wms_info['account_id'] = $store_info['account_id'];
        $wms_adapter = new WmsAdapter(-1, $wms_info);

        while($goods = $db->fetch_array($goods_list))
        {
            $goods_id = $goods['goods_id'];
            $goods['store_platform_id'] = $store_info['store_platform_id'];
            logx("==begin push===",$sid.'/WMS_CMD');
            $result = $wms_adapter->sendRequest(TOP_ADD_STORE_GOODS, array('goods' => $goods));

            $send   = $wms_adapter->getSendParams();
            $resv   = $wms_adapter->getReceived();

            logx("send:    ".print_r($send,true),$sid.'/WMS_CMD');
            logx("receive: ".print_r($resv,true),$sid.'/WMS_CMD');
            $code = $result['code'];
            $error_msg = $result['error_msg'];
            if(mb_strlen($error_msg,'utf-8') > 254)//如果超过长度则截取
            {
                $error_msg = mb_substr($error_msg,0,200,"utf-8");
            }

            if($code != 0)//失败
            {
                if($code<0)
                {
                    logx("goods_id: $goods_id, 系统级别错误system_error: {$error_msg} in adapter_add_top_store_goods", $sid.'/WMS_CMD');
                }
                else
                {
                    logx("goods_id: $goods_id, 应用级别错误system_error: {$error_msg} in adapter_add_top_store_goods", $sid.'/WMS_CMD');
                }
                $db->query("UPDATE cfg_shop_warehouse_store_goods SET status=2 where goods_id = $goods_id");

                $rows[] = array($goods_id,$erp_store_id,$error_msg,$goods['rec_id']);
                $success = false;
            }
            else//成功
            {
                if(!$db->query("UPDATE cfg_shop_warehouse_store_goods SET status=1 where goods_id = $goods_id"))
                {
                    $error_msg = $db->error_msg();
                    logx("error: {$error_msg} when update db failed in adapter_add_top_store_goods goods_id:goods_id", $sid.'/WMS_CMD');
                    $rows[] = array($goods_id,$store_info['store_name'],$error_msg,$goods['rec_id']);
                }
                else
                {
                    logx("success in adapter_add_top_store_goods", $sid.'/WMS_CMD');
                }
            }
        }
        releaseDb($db);

        if($success)
        {
            ackOk(0);
        }
        else
        {
            ackResult($cols, $rows);
            exit(0);
        }
    }

    //TOP商品解除门店绑定
    function adapter_delete_top_store_goods($sid,$uid,$erp_store_id,$goods_ids)
    {
        //连接卖家服务器
        $db = getUserDb($sid);
        if(!$db)
        {
            logx("getUserDb failed in adapter_delete_top_store_goods!!",$sid.'/WMS_CMD');
            ackError('服务器内部错误');
            return ;
        }

        //获取门店信息
        $store_info = $db->query_result("select csws.*,ss.account_id from cfg_shop_warehouse_store csws ".
            " left join sys_shop ss on ss.shop_id=csws.shop_id where csws.store_id = $erp_store_id ");
        if (!$store_info)
        {
            logx("adapter_delete_top_store_goods get store_info error!", $sid.'/WMS_CMD');
            ackError("获取门店信息失败");
            return false;
        }

        //获取商品信息
        $ext_sql = ' ';
        if(!empty($goods_ids))
        {
            $ext_sql .= " and goods_id in({$goods_ids}) ";
        }
        $goods_list = $db->query("select * from cfg_shop_warehouse_store_goods where store_id = $erp_store_id $ext_sql ");
        if(!$goods_list)
        {
            releaseDb($db);
            logx("query goods_list failed in adapter_delete_top_store_goods!",$sid.'/WMS_CMD');
            ackError('获取商品信息失败');
            return;
        }
        //平台货品id,门店名称，消息列表
        $cols = array("goods_id"=>TYPE_DT_STRING, "store_id"=>TYPE_DT_STRING, "msg"=>TYPE_DT_STRING,"rec_id"=>TYPE_DT_STRING);
        $rows = array();
        $success = true;

        //调用适配器推送数据 门店
        $wms_info['account_id'] = $store_info['account_id'];
        $wms_adapter = new WmsAdapter(-1, $wms_info);

        while($goods = $db->fetch_array($goods_list))
        {
            $goods_id = $goods['goods_id'];
            $goods['store_platform_id'] = $store_info['store_platform_id'];
            // 0 待推送 1  推送成功  2  推送失败
            if($goods['status'] != 1)
            {
                $rows[] = array($goods_id,$erp_store_id,'货品未绑定平台门店，无需解绑',$goods['rec_id']);
                $success = false;
                continue;
            }

            logx("==begin push===",$sid.'/WMS_CMD');
            $result = $wms_adapter->sendRequest(TOP_DELETE_STORE_GOODS, array('goods' => $goods));

            $send   = $wms_adapter->getSendParams();
            $resv   = $wms_adapter->getReceived();

            logx("send:    ".print_r($send,true),$sid.'/WMS_CMD');
            logx("receive: ".print_r($resv,true),$sid.'/WMS_CMD');
            $code = $result['code'];
            $error_msg = $result['error_msg'];
            if(mb_strlen($error_msg,'utf-8') > 254)//如果超过长度则截取
            {
                $error_msg = mb_substr($error_msg,0,200,"utf-8");
            }

            if($code != 0)//失败
            {
                if($code<0)
                {
                    logx("goods_id: $goods_id, 系统级别错误system_error: {$error_msg} in adapter_delete_top_store_goods", $sid.'/WMS_CMD');
                }
                else
                {
                    logx("goods_id: $goods_id, 应用级别错误system_error: {$error_msg} in adapter_delete_top_store_goods", $sid.'/WMS_CMD');
                }
                $rows[] = array($goods_id,$store_info['store_name'],$error_msg,$goods['rec_id']);
                $success = false;
            }
            else//成功
            {
                if(!$db->query("UPDATE cfg_shop_warehouse_store_goods SET status=0 where goods_id = $goods_id"))
                {
                    $error_msg = $db->error_msg();
                    logx("error: {$error_msg} when update db failed in adapter_delete_top_store_goods goods_id:goods_id", $sid.'/WMS_CMD');
                    $rows[] = array($goods_id,$store_info['store_name'],$error_msg,$goods['rec_id']);
                }
                else
                {
                    logx("success in adapter_delete_top_store_goods", $sid.'/WMS_CMD');
                }
            }
        }
        releaseDb($db);

        if($success)
        {
            ackOk(0);
        }
        else
        {
            ackResult($cols, $rows);
            exit(0);
        }
    }

    //TOP获取门店类目

    function adapter_get_top_storecategory($sid,$uid,$erp_store_id)
    {
        /*
        //连接卖家服务器
        $db = getUserDb($sid);
        if(!$db)
        {
        logx("getUserDb failed in adapter_get_top_storecategory!!",$sid.'/WMS_CMD');
        ackError('服务器内部错误');
        return ;
        }

        //获取要新增的门店信息
        $store_info = $db->query_result("select ss.account_id from sys_shop ss  where ss.shop_id=$erp_store_id ");
        if (!$store_info)
        {
        logx("adapter_get_top_storecategory get store_info error!", $sid.'/WMS_CMD');
        ackError("获取店铺信息失败");
        return false;
        }

         */
        //调用适配器推送数据
        //$wms_info['account_id'] = $store_info['account_id'];
        $wms_info['account_id'] = 1;
        //调用适配器推送数据 门店
        $wms_adapter = new WmsAdapter(-1, $wms_info);
        $store_info = array();
        logx("==begin push===",$sid.'/WMS_CMD');
        $result = $wms_adapter->sendRequest(TOP_GET_STORECATEGORY, $store_info);
        $send   = $wms_adapter->getSendParams();
        $resv   = $wms_adapter->getReceived();

        logx("the resv is ".print_r($resv,true),$sid.'/WMS_CMD');
        logx("the send is ".print_r($send,true),$sid.'/WMS_CMD');

        $rev_info = isset($result['rev_info'])?$result['rev_info']:'';
        logx("the rev_info is ".print_r($rev_info,true),$sid.'/WMS_CMD');

        foreach($rev_info as $hehe)
        {
            $id = $hehe['id'];

            logx("一级类目 ：  {$hehe['id']}  {$hehe['name']}",$sid.'/WMS_CMD');

            if(isset($hehe['subCategorys']))
            {
                foreach($hehe['subCategorys'] as $haha)
                {
                    logx("	二级类目：	{$haha['id']}  {$haha['name']}",$sid.'/WMS_CMD');

                    if(isset($haha['subCategorys']))
                    {
                        foreach($haha['subCategorys'] as $huhu)
                        {
                            logx("		三级类目：	{$huhu['id']}  {$huhu['name']}",$sid.'/WMS_CMD');

                            if(isset($huhu['subCategorys']))
                            {
                                logx("醉了",$sid.'/WMS_CMD');
                            }

                        }

                    }

                }

            }
        }

        logx("end============================",$sid.'/WMS_CMD');
        //对反馈信息做处理，记录日志，成功，失败

        //反馈给客户端
    }

    //jit退货推送
    function wms_adapter_add_jitpo_refund($sid, $uid, $rec_id)
    {
        $db = getUserDb($sid);
        if(!$db)
        {
            logx("trade_id:$rec_id, getUserDb failed !! wms_adapter_add_jitpo_refund!!", $sid.'/WMS_CMD');
            ackError('服务器内部错误');
            return false;
        }

        $warehouse_info = $db->query_result(" select sw.* from jit_refund jr left join sys_warehouse sw on jr.warehouse_id = sw.warehouse_id where jr.rec_id = $rec_id ");
        if(!$warehouse_info)
        {
            releaseDb($db);
            logx("trade_id:$rec_id, get warehouse info failed!! wms_adapter_add_jitpo_refund", $sid.'/WMS_CMD');
            ackError('推送JIT退货单失败:没有获取到仓库信息');
            return false;
        }

        $jit_refund_info = $db->query_result("select jr.rec_id,jr.refund_no, jr.status, jr.vph_warehouse, jr.warehouse_id, jr.outer_no, jr.vph_refund_no, SUM(1)AS num, ".
            "sw.warehouse_id,sw.is_defect,sw.ext_warehouse_no,sw.name AS warehouse_name, sw.type AS wms_type ,sw.api_key,jr.refund_type AS order_type , jr.outer_no AS src_outer_no,jr.receiver ,jr.receive_phone ,jr.receive_tel ,jr.receive_area ,jr.receive_address , ".
            "sw.province AS warehouse_province, sw.city AS warehouse_city,sw.api_object_id, sw.district AS warehouse_district, sw.address AS warehouse_address, sw.contact AS warehouse_contact, sw.mobile AS warehouse_mobile, sw.telno AS warehouse_telno, jrd.remark,".
            "jr.goods_type,jr.created,jr.modified,jr.wms_outer_no ".
            "FROM jit_refund jr ".
            "LEFT JOIN sys_warehouse sw ON sw.warehouse_id = jr.warehouse_id ".
            "LEFT JOIN jit_refund_detail jrd ON jrd.vph_refund_no = jr.refund_no ".
            "WHERE jr.rec_id = {$rec_id}");
        if(!$jit_refund_info)
        {
            releaseDb($db);
            logx("trade_id:$rec_id, JIT order not exists!! wms_adapter_add_jitpo_refund", $sid.'/WMS_CMD');
            ackError('推送JIT退货单失败:没有获取到退货单信息');
            return false;
        }

        //残次品判断
        $jit_refund_info['inventory_type'] = 0;
        if ($jit_refund_info['is_defect'] == 1 && array_key_exists('match_warehouse_id',$jit_refund_info))
        {
            $match_warehouse_info = $db->query_result("select api_key from sys_warehouse where match_warehouse_id = {$jit_refund_info['warehouse_id']} ");
            if ($match_warehouse_info)
            {
                $jit_refund_info['api_key'] = $match_warehouse_info['api_key'];
                $jit_refund_info['inventory_type'] = 1;
            }
        }

        if ($jit_refund_info['status'] != 33 && $jit_refund_info['status'] != 35)
        {
            releaseDb($db);
            logx("trade_id:$rec_id, status is wrong!! wms_adapter_add_jitpo_refund",$sid.'/WMS_CMD');
            ackError('JIT退货入库单状态已变更，请刷新页面');
            return false;
        }

        //外部编号(推送失败且有外部单号不重新生成)
        if($jit_refund_info['status'] == 33 || ($jit_refund_info['status'] == 35 && $jit_refund_info['outer_no'] == ''))
        {
            $outer_no = $db->query_result_single("SELECT FN_SYS_NO('outer_no')", '');
            if (empty($outer_no))
            {
                logx("wms_adapter_add_jitpo_refund FN_SYS_NO('outer_no')  failed!!!", $sid.'/WMS_CMD');
                releaseDb($db);
                ackError("推送JIT退货单失败：获取外部单号失败");
                return false;
            }
            $outer_no = 'OJIT' . $outer_no;
        }
        else
        {
            $outer_no = $jit_refund_info['outer_no'];
        }
        $jit_refund_info['outer_no'] = $outer_no;

        //取出省市区
        $area = array(
            'province' => $jit_refund_info['warehouse_province'],
            'city'     => $jit_refund_info['warehouse_city'],
            'district' => $jit_refund_info['warehouse_district']
        );

        if(count($area) < 2)
        {
            logx("outer_no:{$jit_refund_info['outer_no']}, 没有收货人区县信息 wms_adapter_add_jitpo_refund", $sid.'/WMS_CMD');
            ackError('推送JIT退货单失败:没有收货人区县具体信息');
            releaseDb($db);
            return false ;
        }

        //获取退货单货品信息
        $jit_goods_info = $db->query(" select jr.rec_id,gs.spec_id, jrd.num, gs.spec_no, jrd.num, jrd.remark, jrd.po_no,jrd.box_no,gs.spec_name,gg.goods_no,gg.goods_name,ss.spec_wh_no2 ".
            " FROM jit_refund_detail jrd ".
            " LEFT JOIN jit_refund jr ON jr.vph_refund_no = jrd.vph_refund_no ".
            " LEFT JOIN goods_spec gs ON gs.spec_id = jrd.spec_id ".
            " LEFT JOIN goods_goods gg ON gg.goods_id = gs.goods_id ".
            " left join stock_spec ss on ss.spec_id = gs.spec_id and ss.warehouse_id = jr.warehouse_id ".
            " WHERE jr.rec_id={$rec_id} ");
        if(!$jit_goods_info)
        {
            logx("outer_no:{$jit_refund_info['outer_no']}, goods not exists!!! wms_adapter_add_jitpo_refund",$sid.'/WMS_CMD');
            ackError("推送JIT退货单失败:获取退货单货品信息失败");
            return false;
        }

        while($row = $db->fetch_array($jit_goods_info))
        {
            $data['details'][] = $row;
        }

        $data['jit'] = $jit_refund_info;
        $wms_info = json_decode($jit_refund_info['api_key'],true);
        $wms_type = $jit_refund_info['wms_type'];
        $wms_adapter = new WmsAdapter($wms_type, $wms_info);


        logx("==begin push===",$sid.'/WMS_CMD');
        $result = $wms_adapter->sendRequest(WMS_METHOD_JIT_REFUND_ADD, $data);
        $send   = $wms_adapter->getSendParams();
        $resv   = $wms_adapter->getReceived();

        $retry_count = @$wms_adapter->retryCount;
        if($retry_count>0)
        {
            $con_error = $wms_adapter->getConError();
            logx("   连接出现异常，重试次数:$retry_count",$sid.'/WMS_CMD');
            logx("   异常信息：".$con_error ,$sid.'/WMS_CMD');
        }

        logx("the erp refund_no is ".$jit_refund_info['refund_no']." send:    ".print_r($send,true),$sid.'/WMS_CMD');
        logx("receive: ".print_r($resv,true),$sid.'/WMS_CMD');

        $code = $result['code'];
        $error_msg = $result['error_msg'];
        if(mb_strlen($error_msg,'utf-8') > 200)//如果超过长度则截取
        {
            $error_msg = mb_substr($error_msg,0,200,"utf-8");
        }
        $error_msg = $db->escape_string($error_msg);
        $rev_info = isset($result['rev_info'])?$result['rev_info']:'';//更新回传回来的仓库订单编码
        logx("result: ".print_r($result,true),$sid.'/WMS_CMD');

        if($code != 0)
        {
            if($code<0)
            {
                logx("outer_no:{$jit_refund_info['outer_no']}, 系统级别错误system_error: {$error_msg} in wms_adapter_add_jitpo_refund", $sid.'/WMS_CMD');
                $db->query("UPDATE jit_refund SET status=33,wms_status=1,outer_no = '{$outer_no}',error_info='仓库返回信息:{$error_msg}' where rec_id = {$rec_id}");
            }
            else
            {
                logx("outer_no:{$jit_refund_info['outer_no']}, 应用级别错误app_error: {$error_msg} in wms_adapter_add_jitpo_refund", $sid.'/WMS_CMD');
                $db->query("UPDATE jit_refund SET status=33,wms_status=1,outer_no = '{$outer_no}',error_info='仓库返回信息:$error_msg' where rec_id = $rec_id ");
            }
            ackError("推送JIT退货单失败:{$error_msg}");
        }
        else//成功
        {
            $order_log = '推送外部WMS的JIT退货单成功';
            if(mb_strlen($rev_info,'utf-8') > 40)//单号超长
            {
                $rev_info = '';
                $order_log = '推送外部WMS的JIT退货单成功,WMS返回单号超长,请联系旺店通技术';
                logx("outer_no:$outer_no, WMS返回单号超长! wms_adapter_add_po_refund",$sid.'/WMS_CMD');
            }

            if(!$db->query("UPDATE jit_refund SET wms_status=2 ,error_info='' ,status=37 ,outer_no='{$jit_refund_info['outer_no']}' ,wms_outer_no = '{$rev_info}' where rec_id = $rec_id "))
            {
                $error_msg = $db->error_msg();
                logx("outer_no:{$jit_refund_info['outer_no']}, error:$error_msg where update db failed in wms_adapter_add_jitpo_refund",$sid.'/WMS_CMD');
                ackError("推送JIT退货单失败:{$error_msg}");
            }
            else
            {
                $db->execute("INSERT INTO jit_po_log(rec_id,provider_id,`type`,remark) VALUES({$rec_id},$uid,37,'{$order_log}')");
                logx("outer_no:{$jit_refund_info['outer_no']}, success in wms_adapter_add_jitpo_refund", $sid.'/WMS_CMD');
                ackOk(0);
            }

        }
        releaseDb($db);
        exit(0);
    }

    //取消jit退货
    function wms_adapter_cancel_jitpo_return($sid, $uid, $rec_id)
    {
        $db = getUserDb($sid);
        if(!$db)
        {
            logx("trade_id:$rec_id, getUserDb failed !! wms_adapter_cancel_jitpo_return!!", $sid.'/WMS_CMD');
            ackError('服务器内部错误');
            return;
        }

        $jit_info = $db->query_result("select jr.rec_id, jr.status, jr.vph_refund_no, jr.wms_status,jr.outer_no, jr.wms_outer_no, jr.vph_warehouse, jr.vph_refund_no, jr.warehouse_id, jr.receiver, sw.type AS wms_type, sw.api_key, sw.api_object_id,sw.warehouse_id, ".
            " jr.receive_count,jr.receive_province,jr.receive_city,jr.receive_area,jr.receive_town,jr.receive_address,jr.receive_tel,jr.receive_phone,jrd.remark  ".
            " FROM jit_refund jr ".
            "LEFT JOIN sys_warehouse sw ON jr.warehouse_id=sw.warehouse_id ".
            "LEFT JOIN sys_jit_warehouse sjw ON jr.vph_warehouse=sjw.warehouse_no ".
            "LEFT JOIN jit_refund_detail jrd ON jr.vph_refund_no = jrd.vph_refund_no ".
            "WHERE jr.rec_id = {$rec_id}");

        if(!$jit_info)
        {
            releaseDb($db);
            logx("trade_id:$rec_id, order not exists!!! wms_adapter_cancel_jitpo_return",$sid.'/WMS_CMD');
            ackError('取消JIT退货单失败:没有获取到jit退货单信息');
            return ;
        }

        $outer_no = $jit_info['outer_no'];

        if ($jit_info['status'] != 37)// 37  待入库
        {
            releaseDb($db);
            logx("outer_no:$outer_no, status is wrong!! wms_adapter_cancel_jitpo_return",$sid.'/WMS_CMD');
            ackError('JIT退货单状态已变更，请刷新页面');
            return;
        }

        if($jit_info['wms_status'] == 3)//已经成功取消可是存储过程调用失败
        {
            if($db->execute("BEGIN") && $db->execute("SET @cur_uid={$uid} ") && $db->query("CALL SP_JIT_REFUND_BACK('{$rec_id}',1)"))
            {
                $db->execute("update jit_refund set wms_status=0,error_info='' where rec_id={$rec_id}");
                $db->execute("INSERT INTO jit_po_log(rec_id,provider_id,type,remark) VALUES({$rec_id},$uid,5,'取消jit退货单成功')");
                $db->execute("COMMIT");
                logx("outer_no:$outer_no, cancel success !! wms_adapter_cancel_jitpo_return", $sid.'/WMS_CMD');
                ackOk(0);
            }
            else
            {
                $error_msg = $db->error_msg();
                logx("outer_no:$outer_no, error:$error_msg where update db failed!! wms_adapter_cancel_jitpo_return",$sid.'/WMS_CMD');
                $db->execute("ROLLBACK");
                $db->query("update jit_refund set wms_status=3,error_info='取消操作执行失败' where rec_id={$jit_info['rec_id']}");
                ackError("更新jit退货单信息失败：{$error_msg}");
            }
            releaseDb($db);
            exit(0);
        }

        $data['jit'] = $jit_info;
        $wms_info = json_decode($jit_info['api_key'],true);
        $wms_type = $jit_info['wms_type'];

        $wms_adapter = new WmsAdapter($wms_type, $wms_info);

        logx("==begin push===",$sid.'/WMS_CMD');
        $result = $wms_adapter->sendRequest(WMS_METHOD_JIT_REFUND_CANCEL, $data);
        $send   = $wms_adapter->getSendParams();
        $resv   = $wms_adapter->getReceived();

        $retry_count = @$wms_adapter->retryCount;
        if($retry_count>0)
        {
            $con_error = $wms_adapter->getConError();
            logx("   连接出现异常，重试次数:$retry_count",$sid.'/WMS_CMD');
            logx("   异常信息：".$con_error ,$sid.'/WMS_CMD');
        }

        logx("the erp rec_id is ".$jit_info['rec_id']." send:    ".print_r($send,true),$sid.'/WMS_CMD');
        logx("receive: ".print_r($resv,true),$sid.'/WMS_CMD');

        $code = $result['code'];
        $error_msg = $result['error_msg'];
        if(mb_strlen($error_msg,'utf-8') > 200)//如果超过长度则截取
        {
            $error_msg = mb_substr($error_msg,0,200,"utf-8");
        }
        logx("result: ".print_r($result,true),$sid.'/WMS_CMD');

        //判断是否成功
        if($code != 0)
        {
            if($code<0)
            {
                logx("outer_no:$outer_no, 系统级别错误system_error: {$error_msg}!! wms_adapter_cancel_jitpo_return", $sid.'/WMS_CMD');
            }
            else
            {
                logx("outer_no:$outer_no, 应用级别错误app_error: {$error_msg}!! wms_adapter_cancel_jitpo_return", $sid.'/WMS_CMD');
            }
            ackError("取消jit退货单失败：{$error_msg}");
        }
        else//成功
        {
            if($db->execute("BEGIN") && $db->execute("SET @cur_uid={$uid} ") && $db->query("CALL SP_JIT_REFUND_BACK('{$rec_id}',1)"))
            {
                $db->execute("update jit_refund set wms_status=0,error_info='' where rec_id={$rec_id}");
                $db->execute("INSERT INTO jit_po_log(rec_id,provider_id,type,remark) VALUES({$rec_id},$uid,25,'取消jit退货单成功')");
                $db->execute("COMMIT");
                logx("outer_no:$outer_no, cancel success!! wms_adapter_cancel_jitpo_return", $sid.'/WMS_CMD');
                ackOk(0);
            }

            else
            {
                $error_msg = $db->error_msg();
                logx("outer_no:$outer_no, error:$error_msg where update db failed!! wms_adapter_cancel_jitpo_return",$sid.'/WMS_CMD');
                $db->execute("ROLLBACK");
                $db->query("update jit_refund set wms_status=3,error_info='取消操作执行失败' where rec_id={$jit_info['rec_id']}");
                ackError("更新jit退货单信息失败：{$error_msg}");
            }

        }
        releaseDb($db);
        exit(0);
    }



}
