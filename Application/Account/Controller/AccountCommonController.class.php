<?php
namespace Account\Controller;

use Common\Controller\BaseController;
use Common\Common;


class AccountCommonController extends BaseController {

    public function showTabsView($tab,$prefix,$app = "Account/AccountCommon")
    {

        $arr_tab=array(
            //key值对应showTabDatagridData函数中的case值，value值对应field值
            'goods_list'=>'goods_list',
            'goods_detail'=>'goods_detail',
            'stalls_purchaser_goods_detail' =>'stalls_purchaser_goods_detail',
            'history_stalls_purchaser_goods_detail' => 'stalls_purchaser_goods_detail',
            'sales_trade'=>'sales_trade',
            'alipay_account_bill'=>'alipay_account_bill',
            'payment_bill'=>'payment_bill',
            'alipay_account_check_log'=>'alipay_account_check_log'
        );
        if(empty($arr_tab[$tab]))
        {
            \Think\Log::write('AccountCommon->联动未知的tabs');
            return null;
        }

        //映射相应的是否添加分页
        $arr_tab_page = array(
            'goods_detail'=>false,
            'stalls_purchaser_goods_detail'=>false,
            'history_stalls_purchaser_goods_detail'=>false
        );
        $tab_view='AccountCommon/tabs_account_common';
        $id = $prefix.'datagrid_'.$tab;
        $fields = get_field($app, $arr_tab[$tab]);
        $datagrid = array(
            'id'         =>$id,
            'options'    => array(
                'title'      => '',
                'url'        => null,
                'fitColumns' => false,
                'pagination' => false,
                'rownumbers' => true,
            ),
            'fields'=>$fields,
            'class'=>'easyui-datagrid',
            'style'=>'padding:5px;'
        );
        $datagrid['options']['pagination'] = $arr_tab_page[$tab]===true?true:false;
        $this->assign('datagrid',$datagrid);
        $this->display($tab_view);
    }

    public function updateTabsData($id, $datagridId) {

        if (intval($id) == 0) {
            $data = array("total" => "0", "rows" => array());
            $this->ajaxReturn($data);
        }
        $page = intval(I('post.page'));
        $rows = intval(I('post.rows'));
        $tab = substr($datagridId, strpos($datagridId, "_") + 1);
        switch ($tab) {
            case "goods_detail":
                $data = D("StallsGoodsAccountManagement")->getGoodsDetail($id);
                break;
            case "stalls_purchaser_goods_detail":
                $data = D("StallsPurchaserAccount")->getPurchaserGoodsDetail($id);
                break;
            case "history_stalls_purchaser_goods_detail":
                $data = D("HistoryStallsPurchaserAccount")->getHistoryPurchaserGoodsDetail($id);
                break;
            case 'sales_trade'://平台对账中订单详情
                $data = D('AlipayAccountCheck')->getAccountSalesTrade($id);
                break;
            case 'alipay_account_bill'://平台对账中支付宝账单
                $data = D('AlipayAccountCheck')->getAlipayBillByTid($id);
                break;
            case 'payment_bill'://平台对账中收款单详情
                $data = D('AlipayAccountCheck')->getPaymentBillByTid($id);
                break;
            case 'alipay_account_check_log':
                $data = D('AlipayAccountCheck')->getAlipayAccountCheckLog($id);
                break;
            default:
                $data = array("total" => "0", "rows" => array());
                break;
        }
        $this->ajaxReturn($data);
    }

}