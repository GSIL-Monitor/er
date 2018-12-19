<?php

namespace Goods\Controller;

use Common\Controller\BaseController;
use Think\Exception\BusinessLogicException;
use Common\Common\DatagridExtention;
use Common\Common\UtilTool;
use Common\Common\ExcelTool;
use Think\Log;
use Common\Common\UtilDB;

class GoodsBarcodePrintController extends BaseController {
    public function getGoodsBarcode() {
        $idList = self::getIDList($id_list,array('tool_bar','datagrid','file_dialog','file_form','print_dialog'));
        $fields = get_field("GoodsBarcodePrint","barcodeprint");
        propFildConv($fields,'prop','goods_spec');
        $checkbox=array('field' => 'ck','checkbox' => true);
        array_unshift($fields,$checkbox);
        $datagrid = array(
            'id'        =>$id_list['datagrid'],
            'options'   => array(
                'title'         =>  '',
                'toolbar'       =>  "#{$id_list['tool_bar']}",
                'fitColumns'    =>  true,
                'singleSelect'  =>  false,
                'ctrlSelect'    =>  true,
                'pagination'    =>  false,
            ),
            'fields'    => $fields,
            'class'     => 'easyui-datagrid',
            'style'     => "overflow:scroll",
        );
        $id_list['add_spec'] = array('id'=>'reason_show_dialog');
        $id_list['add_suite'] = array('id'=>'reason_show_dialog');
        $id_list['add_stockin_order'] = array('id'=>'reason_show_dialog');
        $faq_url = C('faq_url');
        $this->assign('faq_url',$faq_url['print_goods_barcode']);
        $this->assign('datagrid',$datagrid);
        $this->assign('id_list',$id_list);
        $this->assign('params',json_encode($id_list));
        $this->display('show');
    }
    public function downloadTemplet(){
        $file_name = "打印条码模板.xls";
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
    public function uploadExcel()
    {
        if(!self::ALLOW_EXPORT){
            $res["status"] = 1;
            $res["msg"]   = self::EXPORT_MSG;
            $this->ajaxReturn(json_encode($res), "EVAL");
        }
        //获取表格相关信息
        $result = array('status' => 0,'data'=>array(),'msg'=>'成功');
        $file = $_FILES["file"]["tmp_name"];
        $name = $_FILES["file"]["name"];

        try {
            //将表格读取为数据
            $excelData = UtilTool::Excel2Arr($name, $file, "GoodsBarcodePrintImport");
        } catch (\Exception $e) {
            \Think\Log::write($e->getMessage());
            $res["status"] = 1;
            $res["msg"]   = "文件错误，无法读取";
            $this->ajaxReturn(json_encode($res),'EVAL');
        }
        try {
            $data = array();
            $repeatFlag = 0;
            foreach ($excelData as $sheet) {
                for ($k = 1; $k < count($sheet); $k++) {
                    $row = $sheet[ $k ];
                    //分类存储数据
                    $i                                  = 0;
                    $line = $k+1;

                    $temp_data = array(
                        "barcode"              => trim($row[$i]),//条码
                        'is_master'             => empty(trim($row[++$i]))?'是':trim($row[$i]),//是否组合装
                        'print_num'            => empty(trim($row[++$i]))?1:trim($row[$i]),//打印次数
                        'merchant_no'          => trim($row[++$i]),//商家编码
                        'goods_name'           => trim($row[++$i]),//货品名称
                        'short_name'           => trim($row[++$i]),//简称
                        'goods_no'             => trim($row[++$i]),//货位编号
                        'spec_name'            => trim($row[++$i]),//规格名称
                        'spec_code'            => trim($row[++$i]),//规格编码
                        'is_suite'             => empty(trim($row[++$i]))?'否':trim($row[$i]),//是否组合装
                        'line'                 => trim($line),//行号
//                        'status'              => 0,
//                        'message'              => '',
//                        'result'               => '成功'
                    );
                    if(!empty($data)){
                        for($i=0;$i<count($data);$i++){
                            if($temp_data['barcode'] == $data[$i]['barcode']){
                                $repeatFlag = 1;
                            }
                        }
                    }
                    if(empty($temp_data['barcode'])){
                        $result['fail'][] = array('id'=>$temp_data['line'],'result'=>'请先维护好条码信息','message'=>'条码信息不能为空');
                        $result['status']=2;
                    }else if($repeatFlag){
                        $result['repeat'][] = array('id'=>$temp_data['line'],'result'=>'失败','message'=>'条形码已存在，请重新填写!');
                        $result['status']=3;
                        $repeatFlag = 0;
                    }else{
                        $data[] = $temp_data;
                    }

                }
            }
            $result['data'] = array('total'=>0,'rows'=>$data);
            if(empty($data)){
                E('读取导入的数据失败!');
            }
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            \Think\Log::write(__CONTROLLER__.'--uploadExcel--'.$msg);
            $result['status'] = 1;
            $result['msg'] =$msg;
        }
        $this->ajaxReturn(json_encode($result),'EVAL');
    }
    public function printBarcode(){
        try{
            $fields = 'rec_id as id,title as name,content';
            $model = D('Setting/PrintTemplate');
            $dialog_div = 'goodsbarcode_print_dialog';
            $result = $model->field($fields)->where(array('type'=>array('in',array(5,6,9)),'title'=>array('LIKE','条码打印_%')))->order('is_default desc')->select();
            foreach($result as $key){
                $contents[$key['id']] = $key['content'];
            }
            $list = UtilDB::getCfgRightList(array('warehouse'));
            $this->assign('warehouse_list', $list['warehouse']);
            $this->assign('contents',json_encode($contents));
            $this->assign('dialog_div',$dialog_div);
            $this->assign('goods_template',$result);
        }catch(\PDOException $e){
            \Think\Log::write($e->getMessage());
        }

        $this->display('print_barcode');
    }
    public function onWSError(){
        $this->display('waybillDownload');
    }
    public function setDefaultPrinter($content,$templateId)
    {
        $data['content'] = $content;
        $data['rec_id']  = $templateId;
        $data['type']    = "";
        $data['title']   = "";
        $ret             = array("status" => 0, "msg" => "成功");
        try {
            $res = D('Setting/PrintTemplate')->save($data, 'content');
        } catch (\Exception $e) {
            $ret['status'] = 1;
            $ret['meg']    = self::UNKNOWN_ERROR;
        }
        $this->ajaxReturn($ret);
    }
    public function getPrintWarehousePosition($spec_ids,$warehouse_id){
        try{
//        $sql = "SELECT gbc.rec_id AS id,cwp.position_no FROM goods_barcode gbc ".
//            "LEFT JOIN goods_spec gs ON gbc.type=1 AND gbc.target_id=gs.spec_id ".
//            "LEFT JOIN stock_spec_position ssp ON ssp.spec_id=gs.spec_id ".
//            "LEFT JOIN cfg_warehouse_position cwp ON ssp.position_id=cwp.rec_id ".
//            "WHERE ssp.warehouse_id=".$warehouse_id[0]." AND gbc.rec_id in ('".$ids."')";
            if(empty($warehouse_id)){ $this->ajaxReturn(null);}
            $sql = "SELECT ssp.spec_id,cwp.position_no FROM stock_spec_position ssp LEFT JOIN cfg_warehouse_position cwp ON ssp.position_id=cwp.rec_id WHERE ssp.warehouse_id=".$warehouse_id[0]." AND ssp.spec_id in (".$spec_ids.")";
            $result = M('')->query($sql);
            foreach($result as $key => $value){
                $ret[$value['spec_id']] = $value['position_no'];
            }
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            \Think\Log::write(__CONTROLLER__.'--getPrintWarehousePosition--'.$msg);
        }
        $this->ajaxReturn($ret);
    }
}