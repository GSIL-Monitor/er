<?php

namespace Goods\Controller;

use Common\Controller\BaseController;
use Think\Exception\BusinessLogicException;
use Common\Common\DatagridExtention;
use Think\Exception;
use Common\Common\UtilTool;
use Common\Common\UtilDB;
use Common\Common\Factory;
use Common\Common\ExcelTool;
use Think\Log;

class GoodsBarcodeController extends BaseController {
    public function getGoodsBarcode($page = 1, $rows = 20, $search = array(), $sort = 'id', $order = 'desc') {
        if (IS_POST) {
            $this->ajaxReturn(D('GoodsBarcode')->loadDataByCondition($page, $rows, $search, $sort, $order));
        } else {
            $idList                                        = DatagridExtention::getRichDatagrid('GoodsBarcode', 'goods_barcode', U('GoodsBarcode/getGoodsBarcode'));
            $idList['datagrid']['options']['singleSelect'] = false;
            $idList['datagrid']['options']['ctrlSelect']   = true;
            $checkbox=array('field' => 'ck','checkbox' => true);
            array_unshift($idList['datagrid']['fields'],$checkbox);
            $idList['id_list']['fileForm']= 'goods_barcode_file_form';
            $idList['id_list']['fileDialog']='goods_barcode_file_dialog';
            $params                                        = array();
            $params['add']                                 = array();
            $params['add']['id']                           = $idList['id_list']['add'];
            $params['add']['title']                        = '新建条码';
            $params['add']['width']                        = 660;
            $params['add']['height']                       = 180;
            $params['add']['url']                          = U('Goods/GoodsBarcode/addGoodsBarcode');
            $params['edit']                                = array();
            $params['edit']['id']                          = $idList['id_list']['edit'];
            $params['edit']['title']                       = '编辑条码';
            $params['edit']['width']                       = 660;
            $params['edit']['height']                      = 180;
            $params['edit']['url']                         = U('Goods/GoodsBarcode/updateGoodsBarcode');
            $params['datagrid']                            = array();
            $params['datagrid']['url']                     = U('GoodsBarcode/getGoodsList', array('grid' => 'datagrid'));
            $params['datagrid']['id']                      = $idList['id_list']['datagrid'];
            $params['delete']['url']                       = U('Goods/GoodsBarcode/delGoodsBarcode');
            $params['search']['form_id']                   = "goods-form";
            $this->assign('id_list', $idList['id_list']);
            $this->assign('datagrid', $idList['datagrid']);
            $this->assign("params", json_encode($params));
            $this->display('show');
        }
    }

    public function delGoodsBarcode($id) {
        //$this->ajaxReturn(D('GoodsBarcode')->delGoodsBarcode($id));
        try {
            D("GoodsBarcode")->delGoodsBarcode($id);
            $res = array("status" => 0, "info" => "操作成功");
        } catch (BusinessLogicException $e) {
            $res = array("status" => 1, "info" => $e->getMessage());
        } catch (\Exception $e) {
            \Think\Log::write($e->getMessage());
            $res = array("status" => 1, "info" => "未知错误，请联系管理员");
        }
        $this->ajaxReturn($res);
    }

    public function updateGoodsBarcode($id = 0) {
        if (IS_POST) {
            $result              = I('post.');
            $updata['id']        = $result['id'];
            $updata['barcode']   = trim_all($result['barcode'],1);
            $updata['is_master'] = $result['is_master'];
            if($updata['barcode'] == ''){
                $res = array("status" => 0, "info" => "请填写条形码");
                $this->ajaxReturn($res);
            }
            try {
                D("GoodsBarcode")->updateGoodsBarcode($updata);
                $res = array("status" => 1, "info" => "操作成功");
            } catch (BusinessLogicException $e) {
                $res = array("status" => 0, "info" => $e->getMessage());
            } catch (\Exception $e) {
                \Think\Log::write($e->getMessage());
                $res = array("status" => 0, "info" => "未知错误，请联系管理员");
            }
            $this->ajaxReturn($res);
        } else {
            $datagrid_editGoodsBarcode = $this->getDatagridStr();
            $res_goods_barcode_arr     = D('GoodsBarcode')->loadSelectedData($id);
            $this->assign('goods_barcode_list', json_encode($res_goods_barcode_arr));
            $this->assign('datagrid_editGoodsBarcode', $datagrid_editGoodsBarcode);
            $this->display('goodsbarcode_edit');
        }
    }

    public function addGoodsBarcode() {
        if (IS_POST) {
            $result                   = I('post.');
            $sql_data['is_master']    = $result['is_master'];
            $sql_data['barcode']      = trim_all($result['barcode'],1);
            $sql_data['spec_no']      = $result['spec_no'];
            $sql_data['type']         = $result['type'];
            $sql_data['target_id']    = $result['rec_id'];
            $sql_data['goods_id']     = $result['rec_id'];
            $sql_data['tag']          = get_seq("goods_barcode");
            $sql_data['message']      = "新建条形码:" . $sql_data['barcode'];
            $sql_data['goods_type']   = $result['type'];
            $sql_data['operate_type'] = 58;
            $sql_data['operator_id']  = get_operator_id();
            try {
                D("GoodsBarcode")->addGoodsBarcode($sql_data);
                $res = array("status" => 1, "info" => "操作成功");
            } catch (BusinessLogicException $e) {
                $res = array("status" => 0, "info" => $e->getMessage());
            } catch (\Exception $e) {
                $res = array("status" => 0, "info" => "未知错误，请联系管理员");
            }
            $this->ajaxReturn($res);
        } else {
            $datagrid_addGoodsBarcode = $this->getDatagridStr();
            $this->assign('datagrid_addGoodsBarcode', $datagrid_addGoodsBarcode);
            $add_goods_barcode_list = array();
            $this->assign('add_goods_barcode_list', $add_goods_barcode_list);
            $this->display('goodsbarcode_add');
        }
    }

    public function getDatagridStr() {
        $datagrid = "
			{field:'id',hidden:true},
			{field:'type',hidden:true},
			{field:'barcode',title:'条形码',width:200,editor:{type:'validatebox',options:{required:true}},},
			{field:'goods_name',title:'货品名称',width:80,},
			{field:'goods_no',title:'货品编码',width:100,},
			{field:'spec_no',title:'商家编码',width:100,},
			{field:'spec_code',title:'规格码',width:80,},
			{field:'is_master',title:'是否为主条码',width:80,editor:{type:'checkbox',options:{on:'1',off:'0'}},formatter:
				function(value,row,index){
					if(1 == value){return '是';}else{return '否';}},},
			";
        return $datagrid;
    }

    public function loadSelectedData($id) {
        return D('GoodsBarcode')->loadSelectedData($id);
    }


    //导入货品条码
    public function uploadBarcodeExcel(){
        if(!self::ALLOW_EXPORT){
            $res["status"] = 0;
            $res["info"]   = self::EXPORT_MSG;
            $this->ajaxReturn(json_encode($res), "EVAL");
        }
        //获取表格相关信息
        $file = $_FILES["file"]["tmp_name"];
        $name = $_FILES["file"]["name"];
        try{
            $sql_drop = "DROP TABLE IF EXISTS  `tmp_import_detail`";
            $sql      = "CREATE TABLE IF NOT EXISTS `tmp_import_detail` (`rec_id` INT NOT NULL AUTO_INCREMENT,`id` SMALLINT,`status` TINYINT,`result` VARCHAR(30),`message` VARCHAR(60),PRIMARY KEY(`rec_id`))";
            $M = M();
            $M->execute($sql_drop);
            $M->execute($sql);
            $importDB = M("tmp_import_detail");
            $excelClass = new ExcelTool();
            $excelClass->checkExcelFile($name,$file);
            $excelClass->uploadFile($file,"GoodsBarcodeImport");
            $count = $excelClass->getExcelCount();
            //建立临时表，存储数据处理的结果
            $excelData = $excelClass->Excel2Arr($count);
            //如果tmp_import_detail表已存在，就删除并重新创建
            $res = array();
            //处理数据，将数据插入数据库并返回信息
            $goodsDB = D("GoodsBarcode");
            $enable_check_by_template = true;//是否通过模板比对上传文件
            $template_file_name = "货品条码导入模板.xls";
            $template_file_sub_path = APP_PATH."Runtime/File/";
            //$template_file_sub_path = "D:\\erp\\branches\\dev\\source\\Application\\Runtime\\File\\";   //Windows

            $template_file_path = $template_file_sub_path.$template_file_name;
            if(file_exists($template_file_path)){
                $template_excelClass = new ExcelTool();
                $template_file_path = $template_excelClass->setFilePath($template_file_name,$template_file_sub_path);
                $template_count = $template_excelClass->getExcelCount();
                $template_excelData = $template_excelClass->Excel2Arr($template_count);
            }else{
                //模板路径有问题，关闭校验，发送邮件
                $enable_check_by_template = false;
                \Think\Log::write ( '货品条码导入模板比对失败，货品条码导入模板路径有误，模板路径：'.$template_file_path ,\Think\Log::ERR);
            }
            //记录sheet数值索引
            $sheet_index = 0;
            foreach ($excelData as $sheet) {
                //表头校验
                if( $enable_check_by_template==true ){
                    //若第一个sheet表头信息不一致，则返回错误信息，若第二个及以后的表头不一致则跳过该sheet
                    if($sheet_index==0){
                        for ($t=0;$t<count($template_excelData['Sheet1'][0]);$t++){
                            if(trim($template_excelData['Sheet1'][0][$t]) != trim($sheet[0][$t])){
                                $res['status'] = 1;
                                $res['info']   = '文件第一行数据有误，请参照模板文件';
                                $this->ajaxReturn(json_encode($res), "EVAL");
                            }
                        }
                    }else{
                        $sheet_index++;
                        continue;
                    }
                }
                for ($k = 1; $k < count($sheet); $k++) {
                    $row = $sheet[$k];
                    if (UtilTool::checkArrValue($row)) continue;
                    //分类存储数据
                    $i = 0;
                    $data["spec_no"] = trim($row[$i]);//商家编码
                    $data["barcode"] = trim_all($row[++$i],1);
                    $data["is_master"] = trim($row[++$i]);
                    $M->startTrans();
                    try{
                        $goodsDB->importBarcode($data);
                        $M->commit();
                    }catch (\Exception $e) {
                        $M->rollback();
                        $err_code = $e->getCode();
                        if ($err_code == 0) {
                            $err_msg = array("id" => $k + 1, "status" => $err_code, "message" => $e->getMessage(), "result" => "失败");
                            $importDB->data($err_msg)->add();
                        } else {
                            $err_msg = array("id" => $k + 1, "status" => $err_code, "message" => "未知错误，请联系管理员", "result" => "失败");
                            $importDB->data($err_msg)->add();
                            Log::write($e->getMessage());
                        }
                    }
                }
                $sheet_index++;
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
        $this->ajaxReturn(json_encode($res), "EVAL");
    }

    //下载货品条码模板
    public function downloadBarcodeTemplet(){
        $file_name = "货品条码导入模板.xls";
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

    //Excel导出
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
                D('GoodsBarcode')->exportToExcel('', $type);
            }
            else{
                D('GoodsBarcode')->exportToExcel($id_list, $type);
            }
        }
        catch (BusinessLogicException $e){
            $result = array('status'=>1,'info'=> $e->getMessage());
        }catch (\Exception $e) {
            $result=array('status'=>1,'info'=>parent::UNKNOWN_ERROR);
        }
        exit( $result['info']);
    }
}