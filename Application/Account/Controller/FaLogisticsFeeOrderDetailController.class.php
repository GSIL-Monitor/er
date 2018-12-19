<?php
/**
 * Created by PhpStorm.
 * User: yanfeng
 * Date: 2016/6/14
 * Time: 4:32
 */
namespace Account\Controller;

use Common\Controller\BaseController;
use Common\Common\UtilDB;
use Common\Common\UtilTool;
use Think\Exception\BusinessLogicException;
use Common\Common\ExcelTool;
use Think\Log;

class FaLogisticsFeeOrderDetailController extends BaseController {

    public function getFaLogisticsFeeOrderDetailList() {
        if (IS_POST) {
            if (isset($_POST["order_id"])) {
                $order_id = I("post.order_id");
            } else {
                $this->ajaxReturn(array("total" => 0, "rows" => array()));
            }
            try {
                $data = D("FaLogisticsFeeOrderDetail")->getFaLogisticsFeeOrderDetailList($order_id);
            } catch (\Exception $e) {
                $data = array("total" => 0, "rows" => array());
            }
            $this->ajaxReturn($data);
        } else {
            $id_list   = array(
                "toolbar"    => "fa_logistics_fee_order_detail_toolbar",
                "datagrid"   => "fa_logistics_fee_order_detail_datagrid",
                "form"       => "fa_logistics_fee_order_detail_form",
                "fileForm"   => "fa_logistics_fee_order_detail_fileForm",
                "fileDialog" => "fa_logistics_fee_order_detail_fileDialog"
            );
            $datagrid  = array(
                "id"      => $id_list["datagrid"],
                "options" => array(
                    "toolbar" => $id_list["toolbar"],
                    "url"     => U("FaLogisticsFeeOrderDetail/getFaLogisticsFeeOrderDetailList")
                ),
                "fields"  => get_field("Account/FaLogisticsFeeOrderDetail", "FaLogisticsFeeOrderDetail")
            );
            $params    = array(
                "controller" => strtolower(CONTROLLER_NAME),
                "datagrid"   => array("id" => $id_list["datagrid"])
            );
            $list_form = UtilDB::getCfgList(array("logistics"));
            $this->assign('list', $list_form);
            $this->assign("id_list", $id_list);
            $this->assign("datagrid", $datagrid);
            $this->assign("params", json_encode($params));
            $this->display("show");
        }
    }

    public function importLogisticsCost() {
        if(!self::ALLOW_EXPORT){
            $res["status"] = 1;
            $res["info"]   = self::EXPORT_MSG;
            $this->ajaxReturn(json_encode($res), "EVAL");
        }
        //获取Excel表格相关的数据
        $file = $_FILES["file"]["tmp_name"];
        $name = $_FILES["file"]["name"];

        $logistics_id = I("get.logistics_id");

        //读取表格数据
        try {
            $excelData = UtilTool::Excel2Arr($name, $file, "importLogisticsCost");
        } catch (\Exception $e) {
            $res = array("status" => 0, "info" => $e->getMessage());
            $this->ajaxReturn(json_encode($res), "EVAL");
        }
        try {
            foreach ($excelData as $v) {
                $data = $v;
                break;
            }
            $order_id = D("FaLogisticsFeeOrderDetail")->importLogisticsFee($data, $logistics_id);
            $res      = array("status" => 0, "info" => "操作成功", "order_id" => $order_id);
        } catch (BusinessLogicException $e) {
            $res = array("status" => 1, "info" => $e->getMessage());
        } catch (\Exception $e) {
            Log::write($e->getMessage());
            $res = array("status" => 1, "info" => self::UNKNOWN_ERROR);
        }
        $this->ajaxReturn(json_encode($res), "EVAL");
    }

    public function charge() {
        $order_id = I("post.order_id");
        try {
            D("FaLogisticsFeeOrderDetail")->charge($order_id);
            $res = array("status" => 0, "info" => "操作成功");
        } catch (BusinessLogicException $e) {
            $res = array("status" => 1, "info" => $e->getMessage());
        } catch (\Exception $e) {
            Log::write($e->getMessage());
            $res = array("status" => 1, "info" => self::UNKNOWN_ERROR);
        }
        $this->ajaxReturn($res);
    }
    public function downloadTemplet(){
        $file_name = "物流资费导入模板.xls";
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

}