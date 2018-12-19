<?php
namespace Platform\Manager;
use Think\Exception\BusinessLogicException;

include_once (APP_PATH . "Platform/Common/ManagerFactory.class.php");
include_once(APP_PATH . 'Platform/Common/utils.php');
include_once(APP_PATH . "Platform/WayBill/util.php");
include_once(APP_PATH . 'Platform/Manager/utils.php');

class WayBillManager extends Manager {
    
    
    /**
     * @param array   $message
     * @param array   $conditions
     * 电子面单处理逻辑
     */
    public function manual(&$message, $conditions = null) {
       try {
           $sid = get_sid();
           $db = getUserDb($sid);
           $message['status'] =0;
           if (!$db) {
               \Think\Log::write("WayBillManager-manual getUserDb failed in waybillManager!!".$sid);
               E('电子面单获取店铺账号信息失败');
           }
           switch ($conditions['type']) {
               case 'getWayBill': {//获取电子面单
                   $result = $this->getWayBill($db,$conditions['stockout_ids'], $conditions['logistics_id'],$message);
                   break;
               }
               case 'cancelWayBill': {//取消电子面单
                   $result = $this->cancelWayBill($db,$conditions['stockout_ids'], $conditions['logistics_id'],$message);
                   break;
               }
               case 'sendWayBill': {//同步电子面单 jos
                   $result = $this->sendWayBill($db,$conditions['stockout_ids'], $conditions['logistics_id'],$message);
                   break;
               }
               case 'printCheckWayBill': {//打印校验电子面单 top
                   $result = $this->printCheckWayBill($db,$conditions['stockout_ids'], $conditions['logistics_id'],$message);
                   break;
               }
               case 'productWayBill': {//获取服务类型 top
                   $result = $this->productWayBill($db,$conditions['logistics_id'],$message);
                   break;
               }
               case 'queryWayBill': {//查询电子面单
                   $result = $this->queryWayBill($db,$conditions['stockout_ids'], $conditions['logistics_id'],$message);
                   break;
               }
               case 'searchWayBill': {//查询面单服务订购及面单使用情况
                   $result = $this->searchWayBill($db, $conditions['logistics_id'],$message);
                   break;
               }
               case 'searchAllWayBill': {//查询面单服务订购及面单使用情况
                   $result = $this->searchAllWayBill($db,$message);
                   break;
               }
               default: {
                   \Think\Log::write("WayBillManager-manual type error in waybillManager!!".$sid);
                   E('未知电子面的处理');
               }
           }
           releaseDb($db);
       } catch (\Exception $e) {
           $msg = $e->getMessage();
           \Think\Log::write('WayBillManager-manual'.$msg);
           if(($message['status'] == 0 || $message['status']==2)&&!empty($message['data'])&&is_array($message['data']['fail'])){
               
               $message['data']['fail'] = array_push($message['data']['fail'],array('stock_id'=>'','stock_no'=>'','msg'=>'请求电子面单异常，请联系管理员！'));
           }else{
               $message['status'] = 1;
               $message['msg'] = $msg;
               $message['data'] = array();
           }
           $result = false;
       }
       return $result;
    }
    //获取面单号
    public function getWayBill(&$db,$stockout_ids, $logistics_id, &$result = array()) {
        $logistics_info = array();
        if (!$this->checkLogisticsAuth($logistics_id, $logistics_info, $result)) {
            return false;
        }
        if ($logistics_info->bill_type == 1)//线上还是线下：1-线下普通、2-线上热敏
        {
            //当前为空
            switch ((string)$logistics_info->logistics_type) {
                case "1311": //top
                {
                    require_once(APP_PATH . 'Platform/WayBill/jos.php');
                    if (!josGetWaybill($db,$stockout_ids, $logistics_info,$result)) {
                        return false;
                    }
                    break;
                }
                case "8": //sf
                {
                    require_once(APP_PATH . 'Platform/WayBill/sf.php');
                    if (!sf_get_waybill($db,$logistics_info,$stockout_ids,$result)) {
                        return false;
                    }
                    break;
                }
                default:///默认为菜鸟电子面单
                {
                    $result['status'] = 1;
                    $result['msg'] = "电子面单所在的平台未找到";
                    return false;
                }
            }
        } elseif ($logistics_info->bill_type == 2) {
            require_once(APP_PATH . 'Platform/WayBill/top.php');
            if (!topGetWaybill($db,$stockout_ids, $logistics_info, $result)) {
                return false;
            }
        } else {
            $result['status'] = 1;
            $result['msg'] = "电子面单类型不匹配";
            return false;
        }
        return true;
    }

    //京东快递   提交运单信息接口
    public function sendWayBill(&$db,$stockout_ids, $logistics_id, &$result = array()) {
        $logistics_info = array();
        if (!$this->checkLogisticsAuth($logistics_id, $logistics_info, $result)) {
            return false;
        }
        if ($logistics_info->bill_type == 1)//线上还是线下：1-线下普通、2-线上热敏
        {
            //当前为空
            switch ((string)$logistics_info->logistics_type) {
                case "1311": //top
                {
                    require_once(APP_PATH . 'Platform/WayBill/jos.php');
                    if (!josSendWaybill($db,$stockout_ids, $logistics_info, $result)) {
                        return false;
                    }
                    break;
                }
                default:///默认为菜鸟电子面单
                {
                    $result['status'] = 1;
                    $result['msg'] = "电子面单所在的平台未找到";
                    return false;
                }
            }
        } else {
            $result['status'] = 1;
            $result['msg'] = "物流类型不匹配";
            return false;
        }

        return true;
    }

    //菜鸟电子面单打印校验接口
    public function printCheckWayBill(&$db,$stockout_ids, $logistics_id, &$result = array()) {
        $logistics_info = array();
        if (!$this->checkLogisticsAuth($logistics_id, $logistics_info, $result)) {
            return false;
        }
        if ($logistics_info->bill_type == 2) {
            require_once(APP_PATH . 'Platform/WayBill/top.php');
            if (!topPrintWaybill($db,$stockout_ids, $logistics_info, $result)) {
                return false;
            }
        } else {
            $result['status'] = 1;
            $result['msg'] = "物流类型不匹配";
            return false;
        }

        return true;
    }

    //取消电子面单接口
    public function cancelWayBill(&$db,$stockout_ids, $logistics_id, &$result = array()) {
        $logistics_info = array();
        if (!$this->checkLogisticsAuth($logistics_id, $logistics_info, $result)) {
            return false;
        }
        if ((int)$logistics_info->bill_type == 1)//线上还是线下：1-线下普通、2-线上热敏
        {
            //当前为空
            switch ((string)$logistics_info->logistics_type) {
                case "1311": //top
                {
                    require_once(APP_PATH . 'Platform/WayBill/jos.php');
                    if (!josGetWaybill($db,$stockout_ids, $logistics_info, $result)) {
                        return false;
                    }
                    break;
                }
                default:///默认为菜鸟电子面单
                {
                    $result['status'] = 1;
                    $result['msg'] = "电子面单所在的平台未找到";
                    return false;
                }
            }
        } elseif ((int)$logistics_info->bill_type == 2) {
            require_once(APP_PATH . 'Platform/WayBill/top.php');
            if (!topCancelWaybill($db,$stockout_ids, $logistics_info, $result)) {
                return false;
            }
        }
        return true;
    }

    //获取物流商产品类型接口
    public function productWayBill(&$db,$logistics_id, &$result = array()) {
        $logistics_info = array();
        if (!$this->checkLogisticsAuth($logistics_id, $logistics_info, $result)) {
            return false;
        }
        if ($logistics_info->bill_type == 2) {
            require_once(APP_PATH . 'Platform/WayBill/top.php');
            if (!topProductWaybill($db,$logistics_info, $result)) {
                return false;
            }
        } else {
            $result['status'] = 1;
            $result['msg'] = "物流类型不匹配";
            return false;
        }

        return true;
    }

    //查询电子面单详情
    public function queryWayBill(&$db,$stockout_ids, $logistics_id, &$result = array()) {
        $logistics_info = array();
        if (!$this->checkLogisticsAuth($logistics_id, $logistics_info, $result)) {
            return false;
        }
        if ($logistics_info->bill_type == 2) {
            require_once(APP_PATH . 'Platform/WayBill/top.php');
            if (!topQueryWaybill($db,$stockout_ids, $logistics_info, $result)) {
                return false;
            }
        } else {
            $result['status'] = 1;
            $result['msg'] = "物流类型不匹配";
            return false;
        }

        return true;
    }

    //菜鸟 获取发货地&CP 开通状态&账户的使用情况
    public function searchWayBill(&$db,$logistics_id, &$result = array()) {
        $logistics_info = array();
        if (!$this->checkLogisticsAuth($logistics_id, $logistics_info, $result)) {
            return false;
        }
        if ($logistics_info->bill_type == 2) {
            require_once(APP_PATH . 'Platform/WayBill/top.php');
            if (!topSearchWaybill($db,$logistics_info, $result)) {
                return false;
            }
        } else {
            $result['status'] = 1;
            $result['msg'] = "物流类型不匹配";
            return false;
        }

        return true;
    }
    //菜鸟 获取发货地&CP 开通状态&账户的使用情况
    public function searchAllWayBill(&$db, &$result = array()) {
        $logistics_info = array();
        if (!$this->getAllShopAuth($logistics_info, $result)) {
            return false;
        }

        require_once(APP_PATH . 'Platform/WayBill/top.php');
        if (!topAllSearchWaybill($db,$logistics_info, $result))
        {
            return false;
        }


        return true;
    }

    //
    function checkLogisticsAuth($logistics_id, &$logistics_info = array(), &$result = array()) {
        try {

            $Logistics_db = D('Setting/Logistics');
            $logistics_info = $Logistics_db->getLogisticsInfo($logistics_id);
            if (empty($logistics_info)) {
                E('物流公司信息不存在');
            }
            $logistics_info = $logistics_info[0];
            if ((int)$logistics_info['bill_type'] == 2) {
                //获取物流类型淘宝对应的平台编码
                $dict_logistics_code = C('logistics_code');

                //记录选择的物流相关信息
                if (!isset($dict_logistics_code[ $logistics_info['logistics_type'] ])) {
                    E('电子面单不支持当前的物流类型');
                }
                $logistics_info['code'] = $dict_logistics_code[ $logistics_info['logistics_type'] ];
            }
            $logistics_info['sid'] = get_sid();//设置用户id
            self::parse_app_key($logistics_info);
            // 顺丰热敏直接退出
            if ((int)$logistics_info->bill_type == 1) {
                if((int)$logistics_info->logistics_type == 8){
                    return true;
                }
            }
            $logistics_info = Obj2Arr($logistics_info);
            //解析logistics_info 并转化为json对象
            if (!isset($logistics_info['shop_id'])) {
                E('物流公司不存在授权的店铺');
            }
            $shop = array();
            $message = array();
            $this->sync_auth_check($logistics_info['shop_id'],$shop,$message);
            if($message['status'] == 0 && $message['info'] !='')
            {
                E($message['info']);
            }
            //getAppSecret
            if(is_array($shop))
                self::parse_app_key($shop);
            getAppSecret($shop, $appkey, $appsecret);
            $shop->key  = $appkey;
            $shop->secret = $appsecret;
            $shop_info = Obj2Arr($shop);
            $logistics_info = array_merge($logistics_info, $shop_info);
            self::parse_app_key($logistics_info);  
        } catch (\PDOException $e) {
            $msg = $e->getMessage();
            $result['status'] = 1;
            $result['msg'] = $msg;
            return false;
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            $result['status'] = 1;
            $result['msg'] = $msg;
            return false;
        }
        return true;
    }
    function getAllShopAuth(&$auth_shop = array(), &$result = array()) {
        try {
            $shop_list = D('Setting/Shop')->field('shop_id')->where(array('platform_id'=>1))->select();
            if(empty($shop_list))
            {
                SE('不存在淘宝店铺!');
            }
            foreach ($shop_list as $key =>$shop_item){
                $shop = array();
                $message = array();
                $this->sync_auth_check($shop_item['shop_id'],$shop,$message);
                if($message['status'] == 0 && $message['info'] !=''){
                    continue;
                }else{
                    array_push($auth_shop,$shop);
                }
            }
            if(empty($auth_shop))
            {
                SE('不存在授权的店铺!');
            }
            foreach ($auth_shop as $k=>$auth_item){
                if(is_array($auth_shop[$k])){
                    self::parse_app_key($auth_shop[$k]);
                }
                getAppSecret($auth_shop[$k], $appkey, $appsecret);
                $auth_shop[$k]->key  = $appkey;
                $auth_shop[$k]->secret = $appsecret;
            }
        } catch (\PDOException $e) {
            $msg = $e->getMessage();
            $result['status'] = 1;
            $result['msg'] = $msg;
            return false;
        }catch (BusinessLogicException $e) {
            $msg = $e->getMessage();
            $result['status'] = 1;
            $result['msg'] = $msg;
            return false;
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            $result['status'] = 1;
            $result['msg'] = $msg;
            return false;
        }
        return true;
    }
}