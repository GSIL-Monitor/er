<?php
namespace Customer\Controller;

use Common\Controller\BaseController;
use Customer\Common\CustomerFields;
use Common\Common\UtilDB;
use Think\Exception;
use Think\Exception\BusinessLogicException;
use Common\Common\ExcelTool;
use Common\Common\UtilTool;

class MarketManagementController extends BaseController
{
    public function getMarketList($page = 1, $rows = 20, $search = array(), $sort = "cmp.rec_id", $order ="desc")
    {
        if(IS_POST)
        {
            $this->ajaxReturn(D('MarketManagement')->getMarketList($page, $rows, $search, $sort, $order));
        }else
        {
            $id_list  = array(
                "toolbar"       => "market_management_toolbar",
                "datagrid"      => "market_management_datagrid",
                "tab_container" => "market_management_tab_container",
                "form"          => "market_management_form",
                "add"           => "add_market_management",
                "edit"          => "edit_market_management",
                'fileDialog'    => 'market_management_file_dialog',
                'fileForm'      => 'market_management_file_form',
                'add_customer'  => 'market_management_add_customer'
            );
            $datagrid = array(
                "id"      => $id_list["datagrid"],
                "options" => array(
                    "toolbar"      => $id_list["toolbar"],
                    "url"          => U("MarketManagement/getMarketList"),
                    "pagination"   => true,
                    "singleSelect" => false,
                    "ctrlSelect"   => true,
                    "fitColumns"   => true,
                    "rownumbers"   => true,
                    "method"       => "post"
                ),
                "fields"  => CustomerFields::getCustomerFields("MarketManagement")
            );
            $arr_tabs = array(
                array(
                    "id"    => $id_list["tab_container"],
                    "title" => "营销详情",
                    "url"   => U("MarketManagement/getMarketDetail")
                ),
            );

            $params   = array(
                "controller" => strtolower(CONTROLLER_NAME),
                "datagrid"   => array(
                    "id" => $id_list["datagrid"]
                ),
                "tabs"       => array(
                    "id" => $id_list["tab_container"]
                ),
                "add"        => array(
                    "id"     => $id_list["add"],
                    "title"  => "营销方案-新建",
                    "url"    => U("MarketManagement/addMarketPlan"),
                    "height" => 360,
                    "width"  => 400,
                    'ismax'  => false
                ),
                "edit"       => array(
                    "id"     => $id_list["edit"],
                    "title"  => "营销方案-编辑",
                    "url"    => U("MarketManagement/editMarketPlan"),
                    "height" => 360,
                    "width"  => 400,
                    'ismax'  => false
                ),
                'delete'   => array('url' => U('MarketManagement/delPlanById')),
                "search"     => array(
                    "form_id"      => $id_list["form"],
                )
            );

            $this->assign("id_list", $id_list);
            $this->assign("datagrid", $datagrid);
            $this->assign("arr_tabs", json_encode($arr_tabs));
            $this->assign("params", json_encode($params));
            $this->display("show");
        }
    }

    public function getMarketDetail()
    {
        if(IS_POST)
        {
            $id = intval(I('post.id'));
            $page = intval(I('post.page'));
            $rows = intval(I('post.rows'));
            $data = D('MarketManagement')->getMarketDetail($id,$page,$rows,$sort='cmr.rec_id',$order='desc');
            $this->ajaxReturn($data);
        }else
        {
            $id_list  = array(
                "datagrid" => "tabs_market_detail_datagrid",
                'form' => 'tabs_market_detail_form',
                'toolbar' => 'tabs_market_toolbar',
                'downloadUrl'   => U('MarketManagement/downloadTemplet'),
            );
            $datagrid = array(
                "id"      => $id_list["datagrid"],
                "options" => array(
                    "url"        => U("MarketManagement/getMarketDetail"),
                    'toolbar' => "#tabs_market_toolbar",
                    "pagination" => true,
                    "fitColumns" => true,
                ),
                'class'=>'easyui-datagrid',
                "fields"  => CustomerFields::getCustomerFields("MarketDetail")
            );
            $params = array(
                'datagrid'=>array(
                    'id'    =>    $id_list["datagrid"],
                ),
            );
            $this->assign('id_list',$id_list);
            $this->assign('datagrid',$datagrid);
            $this->assign('params',json_encode($params));
            $this->display("tabs_market_detail");
        }

    }

    public function addMarketPlan()
    {
        if(IS_POST)
        {
            $sms = I('post.sms');
            $res = array('status'=>0,'info'=>'添加成功');
            try
            {
                D('MarketManagement')->addMarketPlan($sms,'add');
            }catch (BusinessLogicException $e)
            {
                $res['status'] = 1;
                $res['info'] = $e->getMessage();
            }catch (\Exception $e)
            {
                $res['status'] = 1;
                $res['info'] = self::UNKNOWN_ERROR;
            }
            $this->ajaxReturn($res);

        }else
        {
            $id_list           = array(
                'datagrid_id' => 'add_market_management_datagrid',
                'add_form'    => 'add_market_management_form',
                'toolbar'     => 'add_market_management_toolbar',
                'message'     => 'add_market_management_message',
                'remark'      => 'add_market_management_remark'
            );
            $template[] = array('id'=>'无','name'=>'无');
            $template_res = UtilDB::getCfgList(array("sms_template"),array('is_marketing'=>array('eq','1')));
            $template = array_merge($template,$template_res["sms_template"]);
            $this->assign('template',$template);
            $this->assign("id_list", $id_list);
            $this->display('add');
        }
    }

    public function editMarketPlan()
    {
        if (IS_POST)
        {
            $sms = I("post.sms");
            $res["status"] = 0;
            $res["info"]   = "操作成功";
            try
            {
                D('MarketManagement')->addMarketPlan($sms,'update');
            } catch(BusinessLogicException $e)
            {
                $res["status"] = 1;
                $res["info"]   = $e->getMessage();
            }catch (\PDOException $e)
            {
                $res["status"] = 1;
                $res["info"]   = self::UNKNOWN_ERROR;
            } catch (\Exception $e)
            {
                $res["status"] = 1;
                $res["info"]   = self::UNKNOWN_ERROR;
            }
            $this->ajaxReturn($res);
        } else {
            $id = I("get.id");
            try {
                $market = D('MarketManagement')->getMarketPlanById($id);
            } catch (\Exception $e) {
                \Think\Log::write($e->getMessage());
                $market = array();
            }
            $id_list           = array(
                'datagrid_id' => 'edit_market_management_datagrid',
                'edit_form'    => 'edit_market_management_form',
                'toolbar'     => 'edit_market_management_toolbar',
                'message'     => 'edit_market_management_message',
                'remark'      => 'edit_market_management_remark'
            );
            $template[] = array('id'=>'无','name'=>'无');
            $template_res = UtilDB::getCfgList(array("sms_template"),array('is_marketing'=>array('eq','1')));
            $template = array_merge($template,$template_res["sms_template"]);
            $this->assign('template',$template);
            $this->assign("market", $market);
            $this->assign("id_list", $id_list);
            $this->display("edit");
        }
    }

    public function delPlanById($id)
    {
        //rich-datagrid 封装的删除方法 status状态的值跟后来改的不同。
        $res = array('status'=>1,'info'=>'操作成功');
        try {
            D('MarketManagement')->delPlanById($id,'');
        } catch (\Exception $e) {
            $res = array('status'=>0,'info'=>self::UNKNOWN_ERROR);
        }
        $this->ajaxReturn($res);
    }

    //下载单品导入模板
    public function downloadTemplet(){
        $file_name = "客户导入模板.xls";
        $file_sub_path = APP_PATH."Runtime/File/";
        try{
            ExcelTool::downloadTemplet($file_name,$file_sub_path);
        } catch (BusinessLogicException $e){
            echo '对不起，模板不存在，下载失败！';
        } catch (\Exception $e) {
            \Think\Log::write($e->getMessage());
            echo parent::UNKNOWN_ERROR;
        }
    }

    public function uploadExcel()
    {
        if(!self::ALLOW_EXPORT){
            $res["status"] = 1;
            $res["info"]   = self::EXPORT_MSG;
            $this->ajaxReturn(json_encode($res), "EVAL");
        }
        $id = I('get.id');
        $file = $_FILES["file"]["tmp_name"];
        $name = $_FILES["file"]["name"];
        try{
            $M = M();
            $excelClass = new ExcelTool();
            $excelClass->checkExcelFile($name,$file);
            $excelClass->uploadFile($file,"CustomerImport");
            $count = $excelClass->getExcelCount();
            //建立临时表，存储数据处理的结果
            $excelData = $excelClass->Excel2Arr($count);
            //如果tmp_import_detail表已存在，就删除并重新创建
            $res = array();
            //处理数据，将数据插入数据库并返回信息
            $marketDB = D("MarketManagement");
            $total_array = array();
            $error_list = array();
            //记录sheet数值索引
            $sheet_index = 0;
            foreach ($excelData as $sheet) {
                //数据填充
                for ($k = 1; $k < count($sheet); $k++) {
                    $row = $sheet[$k];
                    if (UtilTool::checkArrValue($row)) continue;
                    //分类存储数据
                    $i = 0;
                    $data["customer_no"] = trim_all($row[$i++]);//客户编码
                    $data["name"] = trim_all($row[$i++]); //客户姓名
                    $data["mobile"]= substr(trim_all($row[$i++]),0,11);//手机号
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
                    $M->startTrans();
                    $marketDB->importCustomer($id,$arr,$error_list,$line);
                    $M->commit();
                    $i++;
                }

            }
            if(count($error_list)>0){
                $res['status'] = 2;
                $res['info'] = $error_list;
            }

        }catch (\PDOException $e){
            $res['status'] = 1;
            $res['info']   = $e->getMessage();
        }catch (\Exception $e){
            $res["status"] = 1;
            $res["info"]   = $e->getMessage();
            $this->ajaxReturn(json_encode($res), "EVAL");
        }
        unset($data);

        $this->ajaxReturn(json_encode($res), "EVAL");
    }

    public function addMarketById()
    {
        $ids = I('post.id');//customer_id
        $plan_id = I('post.plan_id');//营销任务的id
        $res = array('status'=>0,'info'=>'添加成功');
        try
        {
            M()->startTrans();
            D('MarketManagement')->addMarketById($ids,$plan_id);
            M()->commit();
        }catch (\PDOException $e)
        {
            $res['status'] = 1;
            $res['info'] = $e->getMessage();
            M()->rollback();
        }catch (\Exception $e)
        {
            M()->rollback();
            $res['status'] =1;
            $res['info'] = parent::UNKNOWN_ERROR;
        }
        $this->ajaxReturn($res);
    }

    public function delMarketCustomer()
    {
        $id = I('post.id');
        $res = array('status'=>0,'info'=>'操作成功');
        try
        {
            D('MarketManagement')->delPlanById($id,'result');
        }catch (\PDOException $e)
        {
            $res['status'] = 1;
            $res['info'] = $e->getMessage();
        }catch (\Exception $e)
        {
            $res['status'] =1;
            $res['info'] = parent::UNKNOWN_ERROR;
        }
        $this->ajaxReturn($res);
    }

    public function sendMarketSms()
    {
        $id = I('post.id');
        $id = $id[0];
        try
        {
            $res=D('MarketManagement')->sendMarketSms($id);
        }catch (\PDOException $e)
        {
            $res['status'] = 1;
            $res['info'] = $e->getMessage();
        }catch (\Exception $e)
        {
            $res['status'] =1;
            $res['info'] = parent::UNKNOWN_ERROR;
        }
        $this->ajaxReturn($res);
    }


}
