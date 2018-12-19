<?php
namespace Goods\Controller;

use Common\Common\Factory;
use Common\Controller\BaseController;
use Common\Common\UtilDB;
use Common\Common\UtilTool;
use Platform\Common\ManagerFactory;
use Think\Exception;
use Think\Exception\BusinessLogicException;

class PlatformGoodsController extends BaseController {

    /**
     * @param int    $page
     * @param int    $rows
     * @param array  $search
     * @param string $sort
     * @param string $order
     * 返回平台货品主页面
     * author:luyanfeng
     * table:api_goods_spec
     */
    public function getPlatformGoodsList($page = 1, $rows = 10, $search = array(), $sort = 'rec_id', $order =
    'desc') {
        if (IS_POST) {
            $data = D("PlatformGoods")->getPlatformGoodsList($page, $rows, $search, $sort, $order);
            foreach($data['rows'] as $key => &$val) {
                if ($val['platform_id'] == '3' && substr($val['pic_url'],0,3) == 'jfs') {
                    $val['pic_url'] = "http://img10.360buyimg.com/n0/".$val['pic_url'];
                }
            }
            $this -> ajaxReturn($data);
//            $this->ajaxReturn(D("PlatformGoods")->getPlatformGoodsList($page, $rows, $search, $sort, $order));
        } else {

            $id_list = array(
                "datagrid"      => "platform_goods_datagrid",
                "toolbar"       => "platform_goods_toolbar",
                "form"          => "platform_goods_form",
                "tab_container" => "platform_goods_tab_container",
                "goods_suite"   => "platform_goods_select_goods_suite",
                "goods_spec"    => "platform_goods_select_goods_spec",
                'set_flag'      => 'platform_goods_set_flag',
                'search_flag'   => "platform_goods_search_flag",
                "hidden_flag"   => "platform_goods_hidden_flag",
                "more_button"   => "platform_goods_more_button",
                "more_content"  => "platform_goods_more_content",
                "edit"          => "platform_goods_Strategy_edit",
                "add"           => "platform_goods_Strategy_add",
                "delete"        => "platform_goods_Strategy_delete",
                "import_stock"  => "platform_goods_import_stock"
            );

            $datagrid = array(
                "id"      => $id_list["datagrid"],
                "options" => array(
                    "toolbar"      => $id_list["toolbar"],
                    "url"          => U("PlatformGoods/getPlatformGoodsList"),
                    "pagination"   => true,
                    "singleSelect" => false,
                    "fitColumns"   => false,
                    "rownumber"    => true,
                    "method"       => "post",
                    "ctrlSelect"   => true,
                ),
                "fields"  => get_field("PlatformGoods", "platform_goods"),
            );
            $checkbox = array('field' => 'ck','checkbox' => true );
            array_unshift($datagrid['fields'],$checkbox);

            //联动
            $arr_tabs = array(
                array(
                    "id"    => $id_list["tab_container"],
                    "title" => "系统货品",
                    "url"   => U("GoodsCommon/getTabsView") . "?tab=system_goods&prefix=platformGoods"
                ),
                array(
                    "id"    => $id_list["tab_container"],
                    "title" => "平台货品匹配日志",
                    "url"   => U("GoodsCommon/getTabsView") . "?tab=platform_goods_log&prefix=platformGoods"
                ),
                array(
                    "id"    => $id_list["tab_container"],
                    "title" => "库存同步策略",
                    "url"   => U("GoodsCommon/getTabsView") . "?tab=cfg_stock_sync_rule&prefix=platform&field=Setting/SettingCommon"
                ),
                array(
                    "id"    => $id_list["tab_container"],
                    "title" => "库存同步记录",
                    "url"   => U("GoodsCommon/getTabsView") . "?tab=api_stock_sync_record&prefix=platformgoods&field=Stock/StockCommon"
                )
            );

            $arr_flag = Factory::getModel('Setting/Flag')->getFlagData(23);
            $params   = array(
                "controller" => strtolower(CONTROLLER_NAME),
                "datagrid"   => array(
                    "id" => $id_list["datagrid"]
                ),
                "tabs"       => array(
                    "id"  => $id_list["tab_container"],
                    "url" => U("GoodsCommon/updateTabsData")
                ),
                'flag'       => array(
                    'set_flag'    => $id_list['set_flag'],
                    'url'         => U('Setting/Flag/flag') . '?flagClass=23',
                    'json_flag'   => $arr_flag['json'],
                    'list_flag'   => $arr_flag['list'],
                    'dialog'      => array('id' => 'flag_set_dialog', 'url' => U('Setting/Flag/setFlag') . '?flagClass=23', 'title' => '颜色标记'),
                    'search_flag' => $id_list['search_flag']
                ),
                "search"     => array(
                    "form_id"      => $id_list["form"],
                    "more_button"  => $id_list["more_button"],
                    "more_content" => $id_list["more_content"],
                    "hidden_flag"  => $id_list["hidden_flag"]
                ),
                'edit'       => array(
                    "id"     => $id_list['edit'],
                    "title"  => '单品库存同步策略',
                    "url"    => U('PlatformGoods/addSyncStrategy'),
                    'width'  => '650',
                    'height' => '400',
                    'ismax'  => false
                ),
                'delete'     => array(
                    'id'     => $id_list['delete'],
                    'url'    => U('PlatformGoods/delPlatformGoods'),
                ),
            );

            $shop_list[] = array("id" => "all", "name" => "全部");
            $list        = UtilDB::getCfgRightList(array("shop",'warehouse'));
            $shop_list   = array_merge($shop_list, $list["shop"]);
            $stock_spec_num = D("Stock/StockManagement")->getStockSpecCount();
            $goods_spec_num = D('Goods/GoodsSpec')->getGoodsSpecCount();
            $faq_url=C('faq_url');
            $flag_goods_name_changed = D('Setting/System')->getOneSysteSetting('flag_goods_name_changed')[0]['value'];
            $this->assign('flag_goods_name_changed',$flag_goods_name_changed);
            $this->assign('faq_url_goods_interpretation',$faq_url['goods_interpretation']);//货品名词解释
            $this->assign('faq_url_goods_question',$faq_url['goods_question']);//货品常见问题
            $this->assign('goods_spec_num',$goods_spec_num);
            $this->assign('stock_num',$stock_spec_num);
            $this->assign("shop_list", json_encode($shop_list));
            $this->assign('warehouse_list', $list['warehouse']);
            $this->assign("arr_tabs", json_encode($arr_tabs));
            $this->assign("params", json_encode($params));
            $this->assign("datagrid", $datagrid);
            $this->assign("id_list", $id_list);
            $this->display("show");
        }
    }

    /**
     * 平台货品删除，将与平台货品关联的库存同步日志删除
     * table:api_goods_spec、api_stock_sync_record
     */
    public function delPlatformGoods()
    {

        //获取每一行的行号，为数组格式
        $ids = I('post.id');
        try {
            $result = D('PlatformGoods') -> delPlatformGoods($ids);
        } catch (BusinessLogicException $e) {
            $result = array("status" => 1, "info" => $e->getMessage());
        } catch (\Exception $e) {
            \Think\Log::write($e->getMessage());
            $result = array("status" => 1, "info" => parent::UNKNOWN_ERROR);
        }
        $this->ajaxReturn($result);
    }

    /**
     * 货品下载接口
     * author:luyanfeng
     */
    public function download() {
        $id        = I("post.id");
        $form_data = I("post.form_data");
        switch ($id) {
            case 1:
                if (empty($form_data["shop_id"])) {
                    $this->error("请选择店铺");
                }
                break;
            case 2:
                if (empty($form_data["shop_id"]) || empty($form_data["goods_name"])) {
                    $this->error("请选择店铺并输入货品名称");
                }
                break;
            case 3:
                if (empty($form_data["shop_id"]) || empty($form_data["goods_id"])) {
                    $this->error("请选择店铺并输入货品ID");
                }
                break;
            case 4:
                if (empty($form_data["shop_id"]) || empty($form_data["start"]) || empty($form_data["end"])) {
                    $this->error("请选择店铺并输入时间段");
                }
                if (!check_regex('time', $form_data['start']) || !check_regex('time', $form_data['end'])) {
                    $this->error('时间格式不正确');
                }
                if (strtotime($form_data['start']) > strtotime($form_data['end'])) {
                    $this->error('开始时间不能大于结束时间');
                }
                if ((strtotime($form_data['end']) - strtotime($form_data['start'])) > 86400*30) {
                    $this->error('时间跨度不能超过30天');
                }
                if (strtotime($form_data['end']) > time()) {
                    $this->error('结束时间不能大于当前时间');
                }
                break;
            default:
                $this->error("非法输入");
                break;
        }
        $msg                = array("status" => 0, "info" => "");
        $form_data['radio'] = $id;
        try {
            /*$sync_data = \Platform\Common\SyncData::getInstance();
            $sync_data->sync_goods($form_data["shop_id"], $msg, $form_data);*/
            //查询当前api_goods_spec表中的主键值
            $o_rec_id = D('PlatformGoods')->getRecId('true','single');
            $o_rec_id = empty($o_rec_id['rec_id'])?0:$o_rec_id['rec_id'];
            $GoodsManager = ManagerFactory::getManager("Goods");
            $GoodsManager->manualSync($form_data['shop_id'], $msg, $form_data);
            //获取是否开启了自动转入货品档案配置
            $sys_goods_auto_make = get_config_value('sys_goods_auto_make',0);
            if($sys_goods_auto_make)
            {
                $where = "rec_id >$o_rec_id";
                $import_rec_id = D('PlatformGoods')->getRecId($where);
                $rec_id = array();
                //一次下载量过大时,分批处理
                if(count($import_rec_id)>0)
                {
                    foreach($import_rec_id as $v)
                    {
                        $rec_id[]=$v['rec_id'];
                    }
                    if(count($rec_id)>0)
                    {
                        while(count($rec_id)>0)
                        {
                            $arr = array_splice($rec_id,0,1000);//一次取1000条
                            $import_id = implode(',',$arr);
                            D('PlatformGoods')->importApiGoods($import_id,$list);
                        }
                    }
                }
                D("PlatformGoods")->autoMatchUnmatchPlatformGoods();
            }
        } catch (\PDOException $e) {
            $msg['status'] = 0;
            $msg['info']   = '未知错误，请联系管理员';
            \Think\Log::write('sql_exception' . $e->getMessage());
        } catch (\Exception $e) {
            $msg["status"] = 0;
            $msg["info"]   = $e->getMessage();
            \Think\Log::write($msg["info"]);
        }
        $this->ajaxReturn($msg);
    }

    /**
     * 货品匹配：指定组合装
     * author:luyanfeng
     * table:api_goods_spec
     */
    public function matchGoodsSuite() {
        $id       = I("post.id");
        $suite_id = I("post.suite_id");
        try {
            $M = M();
            $M->startTrans();
            $result = Factory::getModel("PlatformGoods")->matchGoodsSuite($id, $suite_id);
            if ($result["status"] == 0) {
                $M->rollback();
                $res["status"] = 0;
                $res["info"]   = $result["info"];
            } else {
                $M->commit();
                $res["status"] = 1;
                $res["info"]   = "操作成功";
            }
        } catch (\Exception $e) {
            $M->rollback();
            \Think\Log::write($e->getMessage());
            $res["status"] = 0;
            $res["info"]   = "未知错误，请联系管理员";
        }
        $this->ajaxReturn($res);
    }

    /**
     * 货品匹配：指定单品
     * author:luyanfeng
     * table:api_goods_spec
     */
    public function matchGoodsSpec() {
        $id      = I("post.id");
        $spec_id = I("post.spec_id");
        try {
            $M = M();
            $M->startTrans();
            $result = Factory::getModel("PlatformGoods")->matchGoodsSpec($id, $spec_id);
            if ($result["status"] == 0) {
                $M->rollback();
                $res["status"] = 0;
                $res["info"]   = $result["info"];
            } else {
                $M->commit();
                $res["status"] = 1;
                $res["info"]   = "操作成功";
            }
        } catch (\Exception $e) {
            $M->rollback();
            \Think\Log::write($e->getMessage());
            $res["status"] = 0;
            $res["info"]   = "未知错误，请联系管理员";
        }
        $this->ajaxReturn($res);
    }

    /**
     * 自动匹配
     * author:luyanfeng
     * table:api_goods_spec
     */
    public function autoMatchPlatformGoods() {
        try {
            $M = M();
            $M->startTrans();
            $result = Factory::getModel("PlatformGoods")->autoMatchPlatformGoods();
            if ($result["status"] == 0) {
                $M->rollback();
                $res["status"] = 0;
                $res["info"]   = $result["info"];
            } else {
                $M->commit();
                $res["status"] = 1;
                $res["info"]   = "操作成功";
            }
        } catch (\Exception $e) {
            \Think\Log::write($e->getMessage());
            $res["status"] = 0;
            $res["info"]   = "未知错误，请联系管理员";
        }
        $this->ajaxReturn($res);
    }

    /**
     * 自动匹配未匹配
     * author:luyanfeng
     * table:api_goods_spec
     */
    public function autoMatchUnmatchPlatformGoods() {
        try {
            $M = M();
            $M->startTrans();
            $result = Factory::getModel("PlatformGoods")->autoMatchUnmatchPlatformGoods();
            if ($result["status"] == 0) {
                $M->rollback();
                $res["status"] = 0;
                $res["info"]   = $result["info"];
            } else {
                $M->commit();
                $res["status"] = 1;
                $res["info"]   = "操作成功";
            }
        } catch (\Exception $e) {
            \Think\Log::write($e->getMessage());
            $res["status"] = 0;
            $res["info"]   = "未知错误，请联系管理员";
        }
        $this->ajaxReturn($res);
    }
    /**
     * 库存同步
     * author:changtao
     * table:api_goods_spec
     */
    /*public function stockSync($recid_str) {
        $res = array(
            'status' => 0,
            'info'   => 'success',
            'data'   => array()
        );
        $sid = 1;
        $model = D('Goods/PlatformGoods');
        $fail_sync = array();
        $success_sync = array();
        $rec_array = explode(",", $recid_str);
        foreach ($rec_array as $rec_id) {

            try {
                $row = $model->getSyncInfo($rec_id);
                $temp = $row;
                if (empty($row)) {
                    \Think\Log::write("映射记录{$rec_id}不存在!!");
                    E('错误:映射记录不存在!');
                }

                $spec_id = $row['match_target_id'];  //ERP货品的ID，如果match_target_type=1，这值是goods_spec的主键，如果match_target_id=2，这值是goods_suite的主键
                $shop_type = $row['platform_id'];
                $shop_id = $row['shop_id'];

                if (empty($spec_id)) {
                    \Think\Log::write("{$rec_id}待同步记录未关联单品或组合装!!");
                    E('错误:待同步记录未关联单品或组合装!');
                }

                if (!in_array($shop_type, array(1, 2, 3, 4, 6, 7, 9, 10, 11, 12, 13, 19, 21, 22, 23))) {
                    \Think\Log::write("{$rec_id} 该平台不支持同步库存!!" . $sid);
                    E('错误:该平台不支持同步库存!');
                }

                if (!check_app_key($row)) {
                    \Think\Log::write("stock_cmd {$rec_id} 该店铺未授权!!");
                    E('错误:该店铺未授权!');
                }
                $row->rec_id = $rec_id;
                $row->sid = $sid;
                $row->is_manual = 1;
                $row->syn_time = date('Y-m-d H:i:s', time());
                $stock = $row;
                do_syn_stock_impl($model, $stock, 1, $fail_sync, $success_sync);
            } catch (\Exception $e) {
                $msg = $e->getMessage();
                $goods_id = @$temp['goods_id'];
                $spec_id = @$temp['spec_id'];
                $fail_sync[] = array(
                    'rec_id'   => $rec_id,
                    'goods_id' => "{$goods_id}",
                    'spec_id'  => "{$spec_id}",
                    'msg'      => $msg
                );
                continue;
            }
        }
        \Think\Log::write("stock_sync 批量库存同步操作完成，请查看同步记录!!" . "fail--" . json_encode($fail_sync) . "---success---" . json_encode($success_sync), \Think\Log::INFO);
        if (!empty($fail_sync)) {
            $res['status'] = 2;

        }
        $res['data'] = array(
            'fail'    => $fail_sync,
            'success' => $success_sync
        );
        $fields = array('rec_id', 'stock_num', 'status', 'is_stock_changed', 'last_syn_num', 'last_syn_time');
        $conditions = array(
            'rec_id' => array('in', $rec_array),
        );
        $update_info = $model->getApiGoodsSpec($fields, $conditions);
        foreach ($update_info as $key => $value) {
            $temp_update["{$value['rec_id']}"] = $value;
        }
        $res['data']['update'] = $temp_update;
        $this->ajaxReturn($res);
    }*/

    public function stockSync() {
        $recid_str   = I("post.recid_str");
        $uid         = get_operator_id();
        $sid         = get_sid();
        $msg         = "";
        $failSync    = array();
        $successSync = array();
        try {
            /*$sync_data = \Platform\Common\SyncData::getInstance();
            $sync_data->sync_goods($form_data["shop_id"], $msg, $form_data);*/
            require_once(APP_PATH . "Platform/Common/ManagerFactory.class.php");
            $StockManager = ManagerFactory::getManager("Stock");
            $StockManager->stock_syn($sid, $uid, $recid_str, $failSync, $successSync);
            if (empty($failSync)) {
                $msg["status"] = 1;
                $msg["info"]   = "操作成功，请查看库存同步记录";
            } else {
                $msg["status"] = 2;
                $msg["data"]   = $failSync;
            }
        } catch (\PDOException $e) {
            $msg['status'] = 0;
            $msg['info']   = '数据库错误';
            \Think\Log::write('sql_exception' . $msg['info']);
        } catch (\Exception $e) {
            $msg["status"] = 0;
            $msg["info"]   = $e->getMessage();
            \Think\Log::write($msg["info"]);
        }
        $this->ajaxReturn($msg);
    }

    public function onOffSale(){
        $recid_str   = I("post.recid_str");
        /*if($recid_str == ''){
            $res = M('api_goods_spec')->field('rec_id')->select();
            foreach($res as $v){
                $rows[]=$v['rec_id'];
            }
        }else{
            $rows[] = $recid_str;
        }*/
        $type = I("post.type");
        $uid         = get_operator_id();
        $sid         = get_sid();
        $msg         = "";
        $failSync    = array();
        try {
            require_once(APP_PATH . "Platform/Common/ManagerFactory.class.php");
            $StockManager = ManagerFactory::getManager("Stock");
            if($type == 'on'){
                $StockManager->stock_onsale($sid,$recid_str, $failSync);
            }else{
                $StockManager->stock_offsale($sid,$recid_str,$failSync);
            }
            if (empty($failSync)) {
                $msg["status"] = 1;
                $msg["info"]   = "操作成功";
                //将平台货品状态更改为已下架
            } else {
                $msg["status"] = 2;
                $msg["data"]   = $failSync;
            }
        } catch (\PDOException $e) {
            $msg['status'] = 0;
            $msg['info']   = '数据库错误';
            \Think\Log::write('sql_exception' . $msg['info'].$e->getMessage());
        } catch (\Exception $e) {
            $msg["status"] = 0;
            $msg["info"]   = $e->getMessage();
            \Think\Log::write($msg["info"]);
        }
        $this->ajaxReturn($msg);
    }

    /*将平台货品模块的货品导入系统货品档案下
    *$arr_api_goods 可以导入货品档案的平台货品信息
    */
    public function importApiGoods(){
        $id_list = I('post.id_list');
        $list = array();
        $result=array('status'=>0,'info'=>'');
        try{
            D('PlatformGoods')->importApiGoods($id_list,$list);
            D("PlatformGoods")->autoMatchUnmatchPlatformGoods();
            if (count($list)>0){
                $result=array('status'=>2,'info'=>array('total'=>count($list),'rows'=>$list));
            }
        }catch (BusinessLogicException $e) {
            $result=array('status'=>1,'info'=> $e->getMessage());
        }catch (\Exception $e) {
            $result=array('status'=>1,'info'=>parent::UNKNOWN_ERROR);
        }

        $this->ajaxReturn($result);
    }

    public function exportToExcel(){
        if(!self::ALLOW_EXPORT){
            echo self::EXPORT_MSG;
            return false;
        }
        $id_list = I('get.id_list');
        $type = I('get.type');
        $result = array('status'=>0,'info'=>'');
        try{
            if($id_list==''){
                $search = I('get.search','',C('JSON_FILTER'));
                foreach ($search as $k => $v) {
                    $key=substr($k,7,strlen($k)-8);
                    $search[$key]=$v;
                    unset($search[$k]);
                }
                D('PlatformGoods')->exportToExcel('',$search, $type);
            }
            else{
                D('PlatformGoods')->exportToExcel($id_list, null, $type);
            }
        }
        catch (BusinessLogicException $e){
            $result = array('status'=>1,'info'=> $e->getMessage());
        }catch (\Exception $e) {
            $result=array('status'=>1,'info'=>parent::UNKNOWN_ERROR);
        }
        //$this->ajaxReturn($result);
        echo $result['info'];
    }

    public function addSyncStrategy($id) {
        $id = (int)$id;
        $mark='edit';
        $data = $this->loadSelectedData($id);
        $form       = array(
            'id'                => $data['id'],
            'rule_no'           => set_default_value($data['rule_no'],''),
            'warehouse_list'    => set_default_value($data['warehouse_list'],''),
            'percent'           => set_default_value($data['stock_syn_percent'],''),
            'plus_value'        => set_default_value($data['stock_syn_plus'],''),
            'min_stock'         => set_default_value($data['stock_syn_min'],''),
            'is_auto_listing'   => set_default_value($data['is_auto_listing'],''),
            'is_auto_delisting' => set_default_value($data['is_auto_delisting'],''),
            'is_disable_syn'    => set_default_value($data['is_disable_syn'],''),
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

        //$ndata['shop_list']      = explode(",", $form['shop_list']);
        $ndata['warehouse_list'] = explode(",", $form['warehouse_list']);
        //$ndata['class_id'] = explode(",", $form['class_id']);
        //$ndata['brand_id'] = explode(",", $form['brand_id']);

        //根据仓库信息判断是否有库存同步策略
        $is_no_rule = 0;
        if($form['warehouse_list'] == ''){
            $is_no_rule = 1;
        }
        //根据是否有规则编号来判定是否是自定义策略
        $is_custom = empty($form['rule_no'])?1:0;

        //获取快捷策略列表
        $strategy_arr = D('PlatformGoods')->getShortCutStrategyList();

        $form  = json_encode($form);
        $ndata = json_encode($ndata);
        $this->assign('form', $form);
        $this->assign('data', $ndata);
        $this->assign('is_no_rule',$is_no_rule);
        $this->assign('is_custom',$is_custom);
        $this->assign('list_shortcut_strategy',$strategy_arr);
        $list = UtilDB::getCfgRightList(array('warehouse'));
        $this->assign('list_warehouse', $list['warehouse']);
        $this->assign('id',$id);
        $this->assign('mark', $mark);
        $this->display('syn_rule_edit');
    }

    public function loadSelectedData($id) {
        return D('PlatformGoods')->loadSelectedData($id);
    }

    public function saveSyncStrategy() {
        $arr = I('post.');
        $res = array('status'=>0,'info'=>'操作成功');
        try{
            D('PlatformGoods')->saveData($arr);
        }catch (BusinessLogicException $e)
        {
            $res['status'] = 1;
            $res['info'] = $e->getMessage();
        }catch(\Exception $e)
        {
            $res['status'] = 1;
            $res['info'] = parent::UNKNOWN_ERROR;
        }
        $this->ajaxReturn($res);
    }

    /**
     * 更新系统单品及货品名称
     */
    public function updateSpecNameGoodsName(){

        $recid_str = I('post.recid_str');

        if($recid_str!=''){
            $rec_id_arr = explode(',',$recid_str);
        }else{
            $rec_id_arr = [];
        }
        $setting = get_config_value('specname_goodsname_allow_update');

        if($setting==1){
            try {


                $data_changed['is_name_changed'] = 0;
                if (empty($rec_id_arr)){
                    $res_val = M('api_goods_spec')->where(true)->save($data_changed);

                }else{
                    $where_changed['rec_id'] = ['in',$rec_id_arr];
                    $res_val = M('api_goods_spec')->where($where_changed)->save($data_changed);

                }


                $date = D("PlatformGoods")->updateSpecNameGoodsName($rec_id_arr);

                $update_result =[];
                foreach($date['success_spec_no_list'] as $spec_no){
                    $update_result[] = [
                        'status' => '更新成功',
                        'spec_no' => $spec_no
                    ];
                }
                foreach($date['fail_spec_no_list'] as $spec_no){
                    $update_result[] = [
                        'status' => '更新失败',
                        'spec_no' => $spec_no
                    ];
                }
                foreach($date['ignore_spec_no_list'] as $spec_no){
                    $update_result[] = [
                        'status' => '不支持更新（多个平台单品对应同一系统单品）',
                        'spec_no' => $spec_no
                    ];
                }

                $res = array("status" => 2, "info" => "更新完成",'update_result'=>$update_result);
            } catch (BusinessLogicException $e) {
                $res = array("status" => 1, "info" => $e->getMessage());
            } catch (\Exception $e) {
                \Think\Log::write($e->getMessage());
                $res = array("status" => 1, "info" => "未知错误，请联系管理员");
            }
        }else{
            $res = array("status" => 1, "info" => "未开启更新货品名称配置");
        }

        $this->ajaxReturn($res);
    }
    public function showShortCutStrategyNameDialog()
    {
        $data = I('get.data');
        $params = array(
            'add_dialog'  => 'add_shortcut_strategy_name',
            'id_datagrid' => strtolower(CONTROLLER_NAME . '_' . "add_shortcut_strategy_name" . '_datagrid'),
            'form_id'     => 'add_shortcut_strategy_name_form',
            'form_url'    => U('PlatformGoods/addShortcutStrategy'),
        );
        $list = D('PlatformGoods')->getShortCutStrategyList();

        $this->assign("params", $params);
        $this->assign("list",json_encode($list));
        $this->assign('data',htmlspecialchars_decode($data));
        $this->display('dialog_add_name');
    }

    public function addShortcutStrategy()
    {
        $data = I('post.');
        $res = array('status'=>0,'info'=>'添加成功');
        try
        {
            $return_id = D('PlatformGoods')->addShortcutStrategy($data,$res);
            if($return_id){
                $res['id'] = $return_id;
            }
        }catch (BusinessLogicException $e)
        {
            $res['status'] = 1;
            $res['info'] = $e->getMessage();
        }catch (\Exception $e)
        {
            $res['status'] = 1;
            $res['info'] = parent::UNKNOWN_ERROR;
        }
        $this->ajaxReturn($res);
    }

    public function getShortcutStrategy()
    {
        $data = D('PlatformGoods')->getShortCutStrategyList();
        $this->ajaxReturn($data);
    }
    public function getShortCutStrategyById(){
        $id = I('post.');
        $id = array_keys($id);
        $id = $id[0];
        $data = D('PlatformGoods')->getShortCutStrategyList($id);
        $res_data = json_decode($data['data']);
        $res['status'] = 0;
        $res['is_disable_syn'] = set_default_value($res_data->is_disable_syn,0);
        $res['purchase_num'] = set_default_value($res_data->purchase_num,0);
        //$res['purchase_arrive_num'] = set_default_value($res_data->purchase_arrive_num,0);
        $res['order_num'] = set_default_value($res_data->order_num,0);
        $res['sending_num'] = set_default_value($res_data->sending_num,0);
        $res['is_auto_delisting'] = set_default_value($res_data->is_auto_delisting,0);
        $res['is_auto_listing'] = set_default_value($res_data->is_auto_listing,0);
        $res['percent'] = set_default_value($res_data->percent,0);
        $res['plus_value'] = set_default_value($res_data->plus_value,0);
        $res['min_stock'] = set_default_value($res_data->min_stock,0);
        $res['warehouse_name'] = $data['warehouse_name'];
        $res['warehouse_id'] = $data['warehouse_id'];
        $this->ajaxReturn($res);
    }

    public function removeShortcutStrategy()
    {
        $id = I('post.id');
        $res = array('status'=>0,'info'=>'删除成功');
        try
        {
            D('PlatformGoods')->removeShortcutStrategy($id);

        }catch (BusinessLogicException $e)
        {
            $res['status'] = 1;
            $res['info'] = $e->getMessage();
        }catch(\Exception $e)
        {
            $res['status'] = 1;
            $res['info'] = parent::UNKNOWN_ERROR;
        }
        $this->ajaxReturn($res);
    }

    public function changeNameStatus()
    {

        $ids = I('post.id');

        $result = array('status' => 0, 'info' => ' ');

        try {
            //选中id
            if (is_array($ids) and count($ids)>1) {
                $where['rec_id'] = array('in', &$ids);
            } elseif(is_array($ids) and count($ids)==1) {
                $where['rec_id'] = array('eq', &$ids[0]);
            }else{
                $where['rec_id'] = array('eq', &$ids);
            }

            $rec_ids = M('api_goods_spec')->field('rec_id')->where(array_merge($where,['is_name_changed'=>['eq',1]]))->select();

            if (empty($rec_ids)){
                $result = array('status' => 0, 'info' => '无需清除');
                $this->ajaxReturn($result);
            }

            //需要变动状态的id
            $ids = [];
            foreach ($rec_ids as $id){
                $ids[] = $id['rec_id'];
            }

            $data['is_name_changed'] = 0;

            $res_val = M('api_goods_spec')->where($where)->save($data);

            if ($res_val) {
                $result = array('status' => 0, 'info' => '清除成功');
            } else {
                $result = array('status' => 1, 'info' => '清除失败');
            }

        }catch (\PDOException $e) {
            \Think\Log::write($e->getMessage());
            $result = array('status' => 1, 'info' => '未知错误');
        }catch (\Exception $e) {
            \Think\Log::write($e->getMessage());
            $result = array('status' => 1, 'info' => '未知错误');
        }

        $this->ajaxReturn($result);
    }


}