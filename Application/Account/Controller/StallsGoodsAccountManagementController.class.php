<?php
namespace Account\Controller;


use Common\Controller\BaseController;
use Common\Common\UtilDB;
use Common\Common\DatagridExtention;
use Think\Exception\BusinessLogicException;
use Think\Exception;
use Common\Common\ExcelTool;
use Think\Log;
use Common\Common\UtilTool;

class StallsGoodsAccountManagementController extends BaseController
{
    public function getStallsGoodsAccountList($page=1, $rows=20, $search = array(), $sort = 'ssga.rec_id', $order = 'desc')
    {
        IF(IS_POST)
        {
            if(empty($search)){
                $search = array(
                    'start_time' => date('Y-m-d',strtotime('-7 day')),
                    'end_time' => date('Y-m-d')
                );
            }
            try
            {
                $data = array('total'=>0,'rows'=>array());
                D('Account/StallsGoodsAccountManagement')->getStallsGoodsAccountList($page, $rows, $search, $sort, $order,$data);
            }catch(Exception $e)
            {
                $data = array('total'=>0,'rows'=>array());
            }
            $this->ajaxReturn($data);
        }else
        {
            $id_list  = array(
                'toolbar'       => 'stalls_goods_account_toolbar',
                'tab_container' => 'stalls_goods_account_container',
                'datagrid'   => 'stalls_goods_account_datagrid',
                'edit'          => 'stalls_goods_account_dialog',
                'form'          => 'stalls_goods_account_form',
                'fileForm'      => 'stalls_goods_file_form',
                "fileDialog" => "stalls_goods_account_dialog"
            );
            $fields = D('Setting/UserData')->getDatagridField('Account/StallsGoodsAccountManagement','stalls_goods_account');
            $datagrid = array(
                'id'=>$id_list['datagrid'],
                'options'=> array(
                    'title' => '',
                    'url'   => U("StallsGoodsAccountManagement/getStallsGoodsAccountList"),
                    'toolbar' => $id_list["toolbar"],
                    'fitColumns'   => false,
                    'singleSelect'=>false,
                    'ctrlSelect'=>true
                ),
                'class' => 'easyui-datagrid',
                'style'=>"overflow:scroll",
                'fields' => $fields,
            );
            $arr_tabs = array(
                array('id' => $id_list['tab_container'], 'url' => U('Account/AccountCommon/showTabsView') . '?tab=goods_detail&prefix=stallsGoodsAccountManagement', 'title' => '货品详情'),
            );
            $params  = array();
            $params['datagrid'] = array();
            $params['datagrid']['url'] = U("getStallsGoodsAccountList/getStallsGoodsAccountList");
            $params['datagrid']['id'] = $id_list['datagrid'];
            $params['search']['form_id'] = $id_list['form'];
            $params['id_list'] = $id_list;
            $params['tabs'] = array('id' => $id_list['tab_container'], 'url' => U('AccountCommon/updateTabsData'));
            $list_form         = UtilDB::getCfgList(array('brand'), array("brand" => array("is_disabled" => 0)));
            $provider = D('Setting/PurchaseProvider')->getALlPurchaseProvider(array('is_disabled'=>0));
            $provider_default['0'] = array('id' => 'all','name'=>'全部');
            $provider_array = array_merge($provider_default,$provider['data']);
            $current_date=date('Y-m-d',time());
            $query_start_date=date('Y-m-d',strtotime('-7 day'));
            $this->assign('current_date',$current_date);
            $this->assign('query_start_date',$query_start_date);
            $this->assign('provider',$provider_array);
            $this->assign('list', $list_form);
            $this->assign("id_list",$id_list);
            $this->assign('tool_bar',$id_list['tool_bar']);
            $this->assign("params",json_encode($params));
            $this->assign('arr_tabs', json_encode($arr_tabs));
            $this->assign('datagrid', $datagrid);
            $this->display('show');
        }
    }
    public function importStallsGoodsAccount() {
        if(!self::ALLOW_EXPORT){
            $res["status"] = 1;
            $res["info"]   = self::EXPORT_MSG;
            $this->ajaxReturn(json_encode($res), "EVAL");
        }
        //获取表格相关信息
        $file = $_FILES["file"]["tmp_name"];
        $name = $_FILES["file"]["name"];
        try{
            $excelClass = new ExcelTool();
            $excelClass->checkExcelFile($name,$file);
            $excelClass->uploadFile($file,"StallGoodsAccountImport");
            $count = $excelClass->getExcelCount();
            if(workTimeUploadNum()<$count){
                SE(UtilTool::UPPER_UPLOAD);
            }
            //建立临时表，存储数据处理的结果
            $excelData = $excelClass->Excel2Arr($count);
            $data = array();
            $total_array = array();
            $error_list = array();
            $enable_check_by_template = true;//是否通过模板比对上传文件
            $template_file_name = "档口货品对账导入模板.xls";
            $template_file_sub_path = APP_PATH."Runtime/File/";                      //linux
            $template_file_path = $template_file_sub_path.$template_file_name;                  //linux

            if(file_exists($template_file_path)){
                $template_excelClass = new ExcelTool();
                $template_file_path = $template_excelClass->setFilePath($template_file_name,$template_file_sub_path);
                $template_count = $template_excelClass->getExcelCount();
                $template_excelData = $template_excelClass->Excel2Arr($template_count);
            }else{
                //模板路径有问题，关闭校验，发送邮件
                $enable_check_by_template = false;
                \Think\Log::write ( '档口货品对账导入模板比对失败，档口对账导入模板路径有误，模板路径：'.$template_file_path ,\Think\Log::ERR);
            }
            //记录sheet数值索引
            $sheet_index = 0;
            foreach ($excelData as $sheet) {
                //表头校验
                if( $enable_check_by_template==true ){
                    //若第一个sheet表头信息不一致，则返回错误信息，若第二个及以后的表头不一致则跳过该sheet
                    if($sheet_index==0){
                        for ($t=0;$t<count($template_excelData['Sheet1'][0]);$t++){
                            if(!(trim($template_excelData['Sheet1'][0][$t]) == trim($sheet[0][$t]))){
                                $res['status'] = 1;
                                $res['info']   = '文件第一行数据有误，请参照模板文件';
                                $this->ajaxReturn($res);
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
                    $i = 0;
                    $data['spec_no'] = trim_all($row[$i++]);
                    $data['provider_name'] = trim_all($row[$i++]);
                    $data['sales_date'] = trim_all($row[$i++]);
                    $data['price'] = trim_all($row[$i++]);
                    $total_array[] = $data;
                }
                $sheet_index++;
            }
            $res = array('status'=>0,'info'=>'');
            if(count($total_array)>0)
            {
                $i = 0;
                while(count($total_array)>0)
                {
                    $line = $i*100;
                    $arr = array_splice($total_array,0,100);
                    D("StallsGoodsAccountManagement")->importGoodsAccount($arr,$error_list,$line);
                    $i++;
                }

            }
            if(count($error_list)>0){
                $res['status'] = 2;
                $res['info'] = $error_list;
            }

            }catch (BusinessLogicException $e){
                $res['status'] = 1;
                $res['info']   = $e->getMessage();
            }catch (\Exception $e){
                Log::write($e->getMessage());
                $res["status"] = 1;
                $res["info"]   = parent::UNKNOWN_ERROR;
                $this->ajaxReturn($res);
            }

        $this->ajaxReturn($res);
    }
    //下载模板
    public function downloadTemplet(){
        $file_name = "档口货品对账导入模板.xls";
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
        try{
            $search = I('get.search','',C('JSON_FILTER'));
            $startnum = strlen('search[');
            $endnum = strlen('search[]');
            foreach ($search as $k => $v) {
                $key=substr($k,$startnum,strlen($k)-$endnum);
                $search[$key]=$v;
                unset($search[$k]);
            }
            if($id_list != ''){
                $search['rec_id'] = $id_list;
            }
            $search['start_time'] = array_key_exists('start_time',$search)?$search['start_time']:date('Y-m-d',strtotime('-7 day'));
            $search['end_time'] = array_key_exists('end_time',$search)?$search['end_time']:date('Y-m-d');

            D('StallsGoodsAccountManagement')->exportToExcel($search, $type);
        }
        catch (BusinessLogicException $e){
            $result = array('status'=>1,'info'=> $e->getMessage());
        }catch (\Exception $e) {
            $result=array('status'=>1,'info'=>parent::UNKNOWN_ERROR);
        }
        echo $result['info'];
    }
    public function charge(){
        $res['status'] = 0;
        $res['info'] = '结算成功';
        $id_list = I('post.id_list');
        $search = [];
        $error_list = array();
        if(empty($id_list)){
            $search = I('post.search','',C('JSON_FILTER'));
            $startnum = strlen('search[');
            $endnum = strlen('search[]');
            foreach ($search as $k => $v) {
                $key=substr($k,$startnum,strlen($k)-$endnum);
                $search[$key]=$v;
                unset($search[$k]);
            }

            $search['start_time'] = array_key_exists('start_time',$search)?$search['start_time']:date('Y-m-d',strtotime('-7 day'));
            $search['end_time'] = array_key_exists('end_time',$search)?$search['end_time']:date('Y-m-d');
        }
        try{
            D('StallsGoodsAccountManagement')->charge($id_list, $search, $error_list);
            if(count($error_list)>0){
                $res['status'] = 2;
                $res['info'] = $error_list;
            }
        }catch (BusinessLogicException $e){
            $res = array('status'=>1,'info'=> $e->getMessage());
        }catch (\Exception $e) {
            $res = array('status'=>1,'info'=>parent::UNKNOWN_ERROR);
        }
        $this->ajaxReturn($res);
    }
}