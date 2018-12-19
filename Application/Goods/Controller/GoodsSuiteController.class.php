<?php
/**
 * Created by PhpStorm.
 * User: yanfeng
 * Date: 2015/8/18
 * Time: 14:41
 */
namespace Goods\Controller;

use Common\Controller\BaseController;
use Common\Common\UtilDB;
use Common\Common\UtilTool;
use Common\Common\ExcelTool;
use Think\Log;
use Think\Exception\BusinessLogicException;

/**
 * Class GoodsSuiteController
 * @package Goods\Controller
 */
class GoodsSuiteController extends BaseController {

    /**
     * 初始化方法，需要在该初始化方法中调用父类的初始化方法
     */
    function _initialize() {
        parent::_initialize();
        parent::getIDList($this->id_list, ['toolbar', 'datagrid', 'edit', 'add', 'tab_container', 'select', 'form', 'add_datagrid', 'add_form',
                                           'add_toolbar', 'edit_datagrid', 'edit_form', 'edit_toolbar', 'more_content', 'hidden_flag','file_Form','file_Dialog']);
    }

    /**
     * 获取组合装页面
     */
    public function getGoodsSuiteList() {
        $id_list  = $this->id_list;
        $datagrid = [
            'id'      => $id_list['datagrid'],
            'options' => [
                'style'      => '',
                'title'      => '',
                'url'        => U('GoodsSuite/getGoodsSuiteData'),
                'toolbar'    => $id_list['toolbar'],
                'fitColumns' => false,
                'rownumbers' => true,
                'pagination' => true,
                'method'     => 'post',
                'singleSelect' => false,
                'ctrlSelect'   => true
            ],
            'fields'  => get_field('GoodsSuite', 'goods_suite')
        ];
        $checkbox=array('field' => 'ck','checkbox' => true);
        array_unshift($datagrid['fields'],$checkbox);
        $this->assign('id_list', $id_list);
        $arr_tabs = [
            ['id' => $id_list['tab_container'], 'url' => U('GoodsCommon/getTabsView') . '?tab=goods_suite_detail&prefix=goodsSuite', 'title' => '组合装单品'],
            ['id' => $id_list['tab_container'], 'url' => U('GoodsCommon/getTabsView') . '?tab=platform_goods&prefix=goodsSuite', 'title' => '平台货品'],
            ['id' => $id_list['tab_container'], 'url' => U('GoodsCommon/getTabsView') . '?tab=suite_set_out_logistics&prefix=goodsSuite', 'title' => '出库物流'],
            ['id' => $id_list['tab_container'], 'url' => U('GoodsCommon/getTabsView') . '?tab=goods_suite_log&prefix=goodsSuite', 'title' => '操作日志']
        ];
        $params   = [
            'controller' => 'GoodsSuiteController',
            'datagrid'   => [
                'id'  => $id_list['datagrid'],
                'url' => U('GoodsSuite/getGoodsSuiteList')
            ],
            'edit'       => [
                'id'    => $id_list['edit'],
                'title' => '编辑组合装',
                'url'   => U('GoodsSuite/editGoodsSuite')
            ],
            'add'        => [
                'id'    => $id_list['add'],
                'title' => '添加组合装',
                'url'   => U('GoodsSuite/addGoodsSuite')
            ],
            'select'     => [
                'id'    => $id_list['select'],
                'title' => '单品选择',
                'url'   => U('GoodsSpec/SelectGoodsSpec')
            ],
            'tabs'       => [
                'id'  => $id_list['tab_container'],
                'url' => U('GoodsCommon/updateTabsData')
            ],
            'delete'     => [
                'url' => U('GoodsSuite/removeGoodsSuite')
            ],
            'search'     => [
                'form_id'      => $id_list['form'],
                'more_content' => $id_list['more_content'],
                'hidden_flag'  => $id_list['hidden_flag']
            ]
        ];
        $faq_url=C('faq_url');
        $this->assign('faq_url_goods_interpretation',$faq_url['goods_interpretation']);//货品名词解释
        $this->assign('faq_url_goods_question',$faq_url['goods_interpretation']);//货品常见问题
        $list     = UtilDB::getCfgList(['brand'], ['brand' => ['is_disabled' => 0]]);
        $this->assign('goods_brand', $list['brand']);
        $this->assign('datagrid', $datagrid);
        $this->assign('params', json_encode($params));
        $this->assign('arr_tabs', json_encode($arr_tabs));
        $this->display('show');
    }

    /**
     * @param int    $page
     * @param int    $rows
     * @param array  $search
     * @param string $sort
     * @param string $order
     */
    public function getGoodsSuiteData($page = 1, $rows = 10, $search = [], $sort = 'suite_id', $order = 'desc') {
        $res = D("GoodsSuite")->getGoodsSuiteList($page, $rows, $search, $sort, $order);
        $this->ajaxReturn($res);
    }
    public function getDialogGoodsSuiteBarcode($page = 1, $rows = 10, $search = [], $sort = 'suite_id', $order = 'desc') {
        $res = D("GoodsSuite")->getGoodsSuiteList($page, $rows, $search, $sort, $order,'goodssuitebarcode');
        $this->ajaxReturn($res);
    }


    /**
     * 添加或者更新goodssuite数据
     * author:luyanfeng
     */
    public function updateGoodsSuite() {
        $goods_suite_detail = I('post.detail');
        $goods_suite        = I('post.suite');
        foreach ($goods_suite as $v) {
            $suite[$v['name']] = $v['value'];
        }
        $suite['suite_id'] = $suite['id'];
        unset($suite['id']);
        try {
            //验证商家编码
            $check_res = check_regex('check_merchant_no',$suite['suite_no']);
            if($check_res)
            {

                SE("组合装商家编码请不要包括特殊字符,如:! @ # \$ % & *: \" \\/'");
            }
            if (!isset($suite["weight"]) || $suite["weight"] == "") {
                $suite['weight'] = 0;
            }
            D("GoodsSuite")->updateGoodsSuite($suite, $goods_suite_detail);
            $res = ['status' => 0, 'info' => '操作成功'];
        } catch (BusinessLogicException $e) {
            $res = ["status" => 1, "info" => $e->getMessage()];
        } catch (\Exception $e) {
            Log::write($e->getMessage());
            $res = ["status" => 1, "info" => self::UNKNOWN_ERROR];
        }
        $this->ajaxReturn($res);
    }

    /**
     * 新增组合装
     */
    public function saveGoodsSuite() {
        $goods_suite_detail = I('post.detail');
        $goods_suite        = I('post.suite');
        foreach ($goods_suite as $v) {
            $suite[$v['name']] = $v['value'];
        }
        try {
            //验证商家编码
            $check_res = check_regex('check_merchant_no',$suite['suite_no']);
            if($check_res)
            {
                SE("组合装商家编码请不要包括特殊字符,如:! @ # \$ % & *: \" \\/'");
            }
            //组合装默认为0
            if (!isset($suite["weight"]) || $suite["weight"] == "") {
                $suite['weight'] = 0;
            }
            D("GoodsSuite")->addGoodsSuite($suite, $goods_suite_detail);
            $res = ['status' => 0, 'info' => '操作成功'];
        } catch (BusinessLogicException $e) {
            $res = ["status" => 1, "info" => $e->getMessage()];
        } catch (\Exception $e) {
            Log::write($e->getMessage());
            $res = ["status" => 1, "info" => self::UNKNOWN_ERROR];
        }
        $this->ajaxReturn($res);
    }
    /**
     * 删除组合装
     * author:luyanfeng
     * table:goods_suite,goods_suite_detail
     */
    public function removeGoodsSuite() {
        $id = I('post.id');
        try {
            D("GoodsSuite")->removeGoodsSuiteById($id);
            $res = ['status' => 0, 'info' => '操作成功'];
        } catch (BusinessLogicException $e) {
            $res = ['status' => 1, 'info' => $e->getMessage()];
        } catch (\Exception $e) {
            Log::write($e->getMessage());
            $res = ['status' => 1, 'info' => self::UNKNOWN_ERROR];
        }
        $this->ajaxReturn($res);
    }

    /**
     * 打开组合装编辑页面
     * author:luyanfeng
     * table:goods_suite
     */
    public function editGoodsSuite() {
        //todo 组合装编辑
        $id                 = I("get.id");
        $goods_suite        = D("GoodsSuite")->getGoodsSuiteById($id);
        $goods_suite_detail = D("GoodsSuite")->getGoodsSuiteDetailById($id);
        $id_list = [
            'toolbar'  => $this->id_list['edit_toolbar'],
            'datagrid' => $this->id_list['edit_datagrid'],
            'form'     => $this->id_list['edit_form'],
            'select'   => $this->id_list['select'],
        ];
        $datagrid           = [
            "id"      => $id_list["datagrid"],
            "options" => [
                "toolbar" => $id_list["toolbar"],
            ],
            "fields"  => get_field("GoodsCommon", "goods_suite_detail_unstock")
        ];
        $this->assign('suite_data', $goods_suite);
        $this->assign("id_list", $id_list);
        $list = UtilDB::getCfgList(["brand", "goods_class"], ["brand" => ["is_disabled" => 0]]);
        $this->assign("goods_brand", $list["brand"]);
        $this->assign("goods_class", $list["goods_class"]);
        $this->assign('goods_suite_detail', json_encode($goods_suite_detail));
        $this->assign("datagrid", $datagrid);
        $this->display('edit');
    }

    /**
     * 打开组合装添加页面
     * author:luyanfeng
     * table:goods_brand,goods_class
     */
    public function addGoodsSuite() {
        $id_list = [
            'toolbar'  => $this->id_list['add_toolbar'],
            'datagrid' => $this->id_list['add_datagrid'],
            'form'     => $this->id_list['add_form'],
            'select'   => $this->id_list['select'],
        ];
        $datagrid = [
            "id"      => $id_list["datagrid"],
            "options" => [
                "toolbar" => $id_list["toolbar"],
            ],
            "fields"  => get_field("GoodsCommon", "goods_suite_detail_unstock")
        ];
        $list     = UtilDB::getCfgList(["brand", "goods_class"], ["brand" => ["is_disabled" => 0]]);
        $this->assign("id_list", $id_list);
        $this->assign("goods_brand", $list["brand"]);
        $this->assign("goods_class", $list["goods_class"]);
        $this->assign("datagrid", $datagrid);
        $this->display('add');
    }



    public function getDialogGoodsSuiteList() {
        $prefix   = I("get.prefix");
        $model   = empty(I("get.model"))?'':I("get.model");
        switch ($model){
            case 'goodssuitbarcode':{
                $field_type = 'goods_suite_barcode';
                $data_url = U('GoodsSuite/getDialogGoodsSuiteBarcode');
                break;
            }
            default:{
                $field_type = 'goods_suite';
                $data_url = U('GoodsSuite/getGoodsSuiteData');
            }
        }
        $prefix   = $prefix ? $prefix : "goods_suite";
        $id_list  = [
            "datagrid"      => $prefix . "_goods_suite_list_datagrid",
            "toolbar"       => $prefix . "_goods_suite_list_toolbar",
            "form"          => $prefix . "_goods_suite_list_form",
            "tab_container" => $prefix . "_goods_suite_list_tab_container",
            "prefix"        => $prefix
        ];
        $datagrid = [
            'id'      => $id_list["datagrid"],
            'options' => [
                'url'        => $data_url,
                'toolbar'    => $id_list["toolbar"],
                'fitColumns' => false,
                'rownumbers' => true,
                'pagination' => true,
                'method'     => 'post',
            ],
            "fields"  => get_field("GoodsSuite", $field_type)
        ];
        $arr_tabs = [
            [
                'id'    => $id_list["tab_container"],
                'url'   => U("GoodsSuite/getGoodsSuiteDetailList") . "?prefix=" . $prefix,
                'title' => '组合装单品'
            ],
        ];
        $params   = [
            "controller" => strtolower(CONTROLLER_NAME),
            "datagrid"   => ["id" => $id_list["datagrid"]],
            "tabs"       => [
                'id'    => $id_list["tab_container"],
                'url'   => U("GoodsSuite/getGoodsSuiteDetailList") . "?prefix=" . $prefix,
                'title' => '组合装单品'
            ],
            "search"     => ["form_id" => $id_list["form"]]
        ];
        $list     = UtilDB::getCfgList(array("brand"), array("brand" => array("is_disabled" => 0)));
        $this->assign("goods_brand", $list["brand"]);
        $this->assign("id_list", $id_list);
        $this->assign("model", $model);
        $this->assign('datagrid', $datagrid);
        $this->assign('arr_tabs', json_encode($arr_tabs));
        $this->assign('params', json_encode($params));
        $this->display('goods_suite_list');


    }

    /**
     * 获取组合装的详细信息
     * author:luyanfeng
     * table:goods_suite_detail
     */
    public function getGoodsSuiteDetailList() {
        if (IS_POST) {
            $id = I("post.id");
            $this->ajaxReturn(D("GoodsSuite")->getGoodsSuiteDetailById($id));
        } else {
            $prefix   = I("get.prefix");
            $prefix   = $prefix ? $prefix : "goods_suite";
            $id_list  = [
                "datagrid" => $prefix . "_tabs_detail_datagrid"
            ];
            $datagrid = [
                "id"      => $id_list["datagrid"],
                "options" => [
                    /*'url'        => U('GoodsSuite/getGoodsSuiteDetailList') . "?prefix=" . $prefix,*/
                    'fitColumns' => false,
                    'rownumbers' => false,
                    'pagination' => false,
                    'method'     => 'post',
                ],
                "fields"  => get_field("GoodsCommon", "goods_suite_detail")
            ];
            $this->assign("id_list", $id_list);
            $this->assign("datagrid", $datagrid);
            $this->display("tabs_goods_suite_detail");
        }
    }

    public function uploadExcel(){
        if(!self::ALLOW_EXPORT){
            $res["status"] = 0;
            $res["info"]   = self::EXPORT_MSG;
            $this->ajaxReturn(json_encode($res), "EVAL");
        }
        $type = I('get.type');
        //获取表格相关信息
        $file = $_FILES["file"]["tmp_name"];
        $name = $_FILES["file"]["name"];
        try{
            $sql_drop = "DROP TABLE IF EXISTS  `tmp_import_detail`";
            $sql      = "CREATE TABLE IF NOT EXISTS `tmp_import_detail` (`rec_id` INT NOT NULL AUTO_INCREMENT,`id` SMALLINT,`status` TINYINT,`result` VARCHAR(30),`message` VARCHAR(255),PRIMARY KEY(`rec_id`))";
            $M = M();
            $M->execute($sql_drop);
            $M->execute($sql);
            $importDB = M("tmp_import_detail");
            $error_list = array();
            $excelClass = new ExcelTool();
            $excelClass->checkExcelFile($name,$file);
            $excelClass->uploadFile($file,"GoodsGoodsImport");
            $count = $excelClass->getExcelCount();
            //建立临时表，存储数据处理的结果
            $excelData = $excelClass->Excel2Arr($count);
            //如果tmp_import_detail表已存在，就删除并重新创建
            $res = array();
            //处理数据，将数据插入数据库并返回信息
            $goodsDB = D("GoodsSuite");
            foreach ($excelData as $sheet) {
                for ($k = 1; $k < count($sheet); $k++) {
                    $row = $sheet[$k];
                    //在excel里的行号
                    $line = $k+1;
                    if (UtilTool::checkArrValue($row)) continue;
                    //分类存储数据
                    $i = 0;
                    $data['goods_suite_detail']['spec_id'] = $row[$i++];//单品商家编码
                    $data['goods_suite']['suite_no'] = $row[$i++];//组合装商家编码
                    $data['goods_suite']['suite_name'] = $row[$i++];//组合装名称
                    $data['goods_suite_detail']['num'] = $row[$i++];//单品数量
                    $data['goods_suite_detail']['fixed_price'] = floatval($row[$i++]);//单品单价
                    $data['goods_suite_detail']['is_fixed_price'] = $row[$i++];//单品固定价格
                    $data['goods_suite']['barcode'] = $row[$i++];//组合装条形码
                    $data['goods_suite']['retail_price'] = floatval($row[$i++]);//组合装零售价
                    $data['goods_suite']['market_price'] = floatval($row[$i++]);//组合装市场价
                    $data['goods_suite']['brand_name'] = $row[$i++];//组合装品牌
                    $data['goods_suite']['class_name'] = $row[$i++];//组合装类别
                    $data['goods_suite']['weight'] = floatval($row[$i++]);//组合装重量
                    $data['goods_suite']['remark'] = $row[$i++];//组合装备注
                    $data['line'] = $line;//行号
                    $excelGoodsSuite[] = $data;
                }
            };
            try{
                $M->startTrans();
                $goodsDB -> importSpec($excelGoodsSuite);
                $M->commit();
            }catch (\Exception $e) {
                $M -> rollBack();
                $err_code = $e->getCode();
                if ($err_code != 0) {
                    $err_msg = array("status" => $err_code, "message" =>$e->getMessage(), "result" => "失败");
                    $importDB->data($err_msg)->add();
                } else {
                    $err_msg = array("status" => $err_code, "message" => "未知错误，请联系管理员", "result" => "失败");
                    $importDB->data($err_msg)->add();
                    Log::write($e->getMessage());
                }
            }
        }catch (BusinessLogicException $e){
            $res['status'] = 0;
            $res['info'] = $e->getMessage();
            $this->ajaxReturn(json_encode($res), "EVAL");
        }catch (\Exception $e){
            Log::write($e->getMessage());
            $res["status"] = 0;
            $res["info"]   = "未知错误，请联系管理员";
            $this->ajaxReturn(json_encode($res), "EVAL");
        }
        unset($data);
        try {
            $sql    = "SELECT id,status,result,message FROM tmp_import_detail";
            $result = $M->query($sql);
            if (count($result) == 0) {
                $res["status"] = 1;
                $res["info"]   = "操作成功";
            } else {
                $res["status"] = 2;
                $res["info"]   = $result;
            }
            $sql_drop = "DROP TABLE IF EXISTS  `tmp_import_detail`";
            $M->execute($sql_drop);
        } catch (\Exception $e) {
            $res["status"] = 0;
            $res["info"] = "未知错误，请联系管理员";
        }

        /*header('Con')}tent-Type:text/plain; charset=utf-8');
        exit(json_encode($res, 0));*/
        $this->ajaxReturn($res);
    }

    //下载单品导入模板
    public function downloadTemplet(){
        $file_name = "组合装导入模板.xls";
        $file_sub_path = APP_PATH."Runtime/File/";
        try{
            ExcelTool::downloadTemplet($file_name,$file_sub_path);
        } catch (BusinessLogicException $e){
            Log::write($e->getMessage());
            echo '对不起，模板不存在，下载失败！';
        } catch (\Exception $e) {
            Log::write($e->getMessage());
            echo parent::UNKNOWN_ERROR;
        }
    }

    public function exportToExcel(){
        if(!self::ALLOW_EXPORT){
            echo self::EXPORT_MSG;
            return false;
        }
        $id_list = I('get.id_list');
        $type = I('get.type');
        $result = array('status'=>0,'info'=>'');
        $goods_suit_db = D('GoodsSuite');
        try{
            if($id_list==''){
                $search = I('get.search','',C('JSON_FILTER'));
                foreach ($search as $k => $v) {
                    $key=substr($k,7,strlen($k)-8);
                    $search[$key]=$v;
                    unset($search[$k]);
                }
                $goods_suit_db->exportToExcel('',$search, $type);
            }
            else{
                $goods_suit_db->exportToExcel($id_list, null, $type);
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
}