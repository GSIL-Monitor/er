<?php
namespace Setting\Controller;

/**
 * Created by PhpStorm.
 * User: gaosong
 * Date: 10/21/15
 * Time: 09:41
 */
use Common\Common\UtilDB;
use Common\Controller\BaseController;
use Common\Common\DatagridExtention;

//use Common\Common\Factory;

class StockSyncStrategyController extends BaseController {
    public function getStocksyncstrategy() {
        $idList = DatagridExtention::getRichDatagrid('StockSyncStrategy', 'stocksyncstrategy', U("StockSyncStrategy/loadDataByCondition"));

        $params = array(
            'add'      => array(
                'id'     => $idList['id_list']['add'],
                'title'  => '新建库存同步策略',
                'url'    => U('StockSyncStrategy/addSyncStrategy'),
                'width'  => '650',
                'height' => '400',
                'ismax'	 =>  false
            ),
            'edit'     => array(
                'id'     => $idList['id_list']['edit'],
                'title'  => '编辑库存同步策略',
                'url'    => U('StockSyncStrategy/addSyncStrategy'),
                'width'  => '650',
                'height' => '400',
                'ismax'	 =>  false
            ),
            'datagrid' => array(
                'id' => $idList['id_list']['datagrid'],
            ),
            'search'   => array(
                'form_id' => 'stocksyncstrategy-form',
            ),
            'delete'   => array(
                'id'  => $idList['id_list']['delete'],
                'url' => U('StockSyncStrategy/delSyncStrategy'),
            ),
        );

        $this->assign("params", json_encode($params));
        $this->assign('datagrid', $idList['datagrid']);
        $this->assign("id_list", $idList['id_list']);

        $list               = UtilDB::getCfgList(array('brand', 'goods_class', 'shop', 'warehouse'));
        $brand_default['0'] = array('id' => '-1', 'name' => '全部');
        $brand_array        = array_merge($brand_default, $list['brand']);
        $class_default['0'] = array('id' => '-1', 'name' => '全部');
        $class_array        = array_merge($class_default, $list['goods_class']);
        $faq_url=C('faq_url');
        $this->assign('faq_url',$faq_url['inventory_sync']);
        $this->assign('map_brand', json_encode($brand_array));
        $this->assign('map_class', json_encode($class_array));
        $this->assign('map_shop', json_encode($list['shop']));
        $this->assign('map_warehouse', json_encode($list['warehouse']));

        $this->display('show');
    }

    public function loadDataByCondition($page = 1, $rows = 20, $search = array(), $sort = 'id', $order = 'desc') {
        $this->ajaxReturn(D('CfgStockSyncRule')->searchStockSyncStrategy($page, $rows, $search, $sort, $order));
    }

    public function delSyncStrategy($id = 0) {
        $this->ajaxReturn(D('CfgStockSyncRule')->delData($id));
    }

    public function addSyncStrategy($id = 0) {
        $id = (int)$id;
        if ($id != 0) {
            $data = $this->loadSelectedData($id);
            if ($data == 0) {
                return 'err!';
            }
            $form       = array(
                'id'                => $data['id'],
                'rule_no'           => $data['rule_no'],
                'rule_name'         => $data['rule_name'],
                'priority'          => $data['priority'],
                'shop_list'         => $data['shop_list'],
                'warehouse_list'    => $data['warehouse_list'],
                'class_id'          => $data['class_id'],
                'brand_id'          => $data['brand_id'],
                'percent'           => $data['percent'],
                'plus_value'        => $data['plus_value'],
                'min_stock'         => $data['min_stock'],
                'is_auto_listing'   => $data['is_auto_listing'],
                'is_auto_delisting' => $data['is_auto_delisting'],
                'is_disabled'       => $data['is_disabled'],
                'is_disable_syn'    => $data['is_disable_syn'],
            );
            $stock_flag = decbin($data['stock_flag']);
            $stock_flag = substr("0000000000000000", 0, 16 - strlen($stock_flag)) . $stock_flag;

            $form['to_transfer_num']     = $stock_flag[0];
            $form['lock_num']            = $stock_flag[1];
            $form['refund_exch_num']     = $stock_flag[2];
            $form['return_exch_num']     = $stock_flag[3];
            $form['refund_num']          = $stock_flag[4];
            $form['return_num']          = $stock_flag[5];
            $form['sending_num']         = $stock_flag[6];
            $form['unpay_num']           = $stock_flag[7];
            $form['order_num']           = $stock_flag[8];
            $form['subscribe_num']       = $stock_flag[9];
            $form['refund_onway_num']    = $stock_flag[10];
            $form['return_onway_num']    = $stock_flag[11];
            $form['purchase_arrive_num'] = $stock_flag[12];
            $form['transfer_num']        = $stock_flag[13];
            $form['to_purchase_num']     = $stock_flag[14];
            $form['purchase_num']        = $stock_flag[15];

            $ndata['shop_list']      = explode(",", $form['shop_list']);
            $ndata['warehouse_list'] = explode(",", $form['warehouse_list']);

            $form  = json_encode($form);
            $ndata = json_encode($ndata);
        } else {
            $form  = "'none'";
            $ndata = "'none'";
        }


        $this->assign('form', $form);
        $this->assign('data', $ndata);

        $list = UtilDB::getCfgList(array('shop', 'brand', 'warehouse'));
        $this->assign('list_shop', $list['shop']);
        $this->assign('list_warehouse', $list['warehouse']);

        $brand_default['0'] = array('id' => '-1', 'name' => '全部');
        $brand_array        = array_merge($brand_default, $list['brand']);
        $this->assign('list_brand', $brand_array);

        if ($id == 0) {
            $mark   = 'add';
            $status = 'true';
        } else {
            $mark   = 'edit';
            $status = 'false';
        }
        $this->assign('mark', $mark);
        $this->assign('status', $status);
        $this->display('stocksyncstrategy_edit');
    }

    public function loadSelectedData($id) {
        return D('CfgStockSyncRule')->loadSelectedData($id);
    }

    public function saveSyncStrategy() {
        $arr = I('post.');
        $this->ajaxReturn(json_encode(D('CfgStockSyncRule')->saveData($arr)), 'EVAL');
    }

    public function refreshSyncStrategy() {
        try {
            $PlatformGoodsModel = D("Goods/PlatformGoods");
            $PlatformGoodsModel->updateStockSyncRule(5);
            $sys_other_log = M("sys_other_log");
            $arr_sys_other_log = array(
                "type" => "22",
                "operator_id" => get_operator_id(),
                "data" => 1,
                "message" => "应用全局库存同步策略",
                "created" => date("Y-m-d G:i:s")
            );
            $sys_other_log->data($arr_sys_other_log)->add();
            $res["status"] = 1;
            $res["info"]   = "操作成功";
        } catch (\Exception $e) {
            $res["status"] = 0;
            $res["info"]   = "未知错误，请联系管理员";
        }
        $this->ajaxReturn($res);
    }

}