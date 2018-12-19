<?php
namespace Stock\Controller;
use Common\Common\DatagridExtention;
use Common\Controller\BaseController;
use Think\Exception\BusinessLogicException;
use Think\Model;
use Platform\Common\ManagerFactory;
use Common\Common\Factory;
use Common\Common\UtilDB;

class ApiLogisticsSyncController extends BaseController {

    public function getApiLogisticsSyncList($page=1, $rows=20, $search = array(), $sort = 'sync_status', $order = 'ASC'){

	    if (IS_POST) {
			$this->ajaxReturn(Factory::getModel('ApiLogisticsSync')->getApiLogisticsSyncList($page, $rows, $search, $sort, $order));
        }else{
            $sync_status = I('get.sync_status');
            $datagrid_id = strtolower(CONTROLLER_NAME.'_'.ACTION_NAME.'_datagrid');
            $tab_container='api_logistics_sync_tab_container';
            $id_list = array(
                    'tool_bar' => 'api_logistics_sync_datagrid_toolbar',    //tool_bar id
                    'search_form'=>'api_logistics_sync_search_form',
                    'more_button' => 'api_logistics_sync_more_button',
                    'more_content'=>'api_logistics_sync_more_search',
                    'tab_container'=>$tab_container,
                    'id_datagrid' =>$datagrid_id,
                    'hidden_flag'=>'api_logistics_sync_hidden_flag',
            		'chg_dialog'=>'apiLogSynLogisticsDialog',
            );

            $fields = get_field("ApiLogisticsSync","apilogisticssync");
			$datagrid = DatagridExtention::getMainDatagrid($datagrid_id,U("ApiLogisticsSync/getApiLogisticsSyncList")."?sync_status=".$sync_status,$id_list['tool_bar'],$fields);
            $datagrid['options']['singleSelect'] = false;
            $datagrid['options']['ctrlSelect'] = true;
            $checkbox=array('field' => 'ck','checkbox' => true);
            array_unshift($datagrid['fields'],$checkbox);
            $arr_tabs = array(
            		array('url'=>U('StockCommon/showTabsView',array('tabs'=>'api_logistics_sync_detail')).'?prefix=ApiLogisticsSync&tab=api_logistics_sync_detail','id'=>$id_list['tab_container'],'title'=>'详细信息')
            		);
            $params['datagrid'] = array();
            $params['datagrid']['url'] = U("ApiLogisticsSync/getApiLogisticsSyncList");
            $params['datagrid']['id'] = $datagrid_id;
            $params['chg'] = array();
            $params['chg']['url'] = U('ApiLogisticsSync/showLogisticsDilog');
            $params['chg']['id'] = $id_list['chg_dialog'];
            $params['chg']['title'] = "修改物流";
            $params['chg']['width'] =340;
            $params['chg']['height']=200;
            $params['chg']['toolbar'] = null;
            $params['chg']['ismax'] = false;
            $params['form']['id'] = $id_list['search_form'];
            $params['search']['more_button'] = $id_list['more_button'];
            $params['search']['more_content'] = $id_list['more_content'];
            $params['search']['hidden_flag'] = $id_list['hidden_flag'];
            $params['search']['form_id'] = $id_list['search_form'];
            $params['tabs'] = array();
            $params['tabs']['id'] = $id_list['tab_container'];
            $params['tabs']['url'] = U('StockCommon/showTabDatagridData')."?prefix=apiLogisticsSync&tab=api_logistics_sync_detail";//用来加载数据的
            $params['search']['more_button']  = $id_list['more_button'];  //更多按钮的button
            $params['search']['more_content'] = $id_list['more_content']; //更多的层id
            $params['search']['hidden_flag']  = $id_list['hidden_flag'];  //更多的隐藏值，用来判断是否是展开
            $params['search']['form_id']      = $id_list['search_form'];   //用作获取form的作用的，用来提交查询信息
            $this->assign("arr_tabs", json_encode($arr_tabs));
            $this->assign("id_list", $id_list);
            $faq_url=C('faq_url');
            $this->assign('faq_url',$faq_url['logistics_sync_question']);
            $this->assign("datagrid", $datagrid);
            $this->assign('params', json_encode($params));


			$list = UtilDB::getCfgList(array('shop','logistics'));
            $setting = get_config_value('logistics_auto_sync');
			$form_list['list_sync_status'][0] =array('key'=>'all','value'=>'全部');
			$list_apilogssync_sync_status = C('apilogssync_sync_status');
			$form_list['list_sync_status']= array_merge($form_list['list_sync_status'], $list_apilogssync_sync_status);
			$form_list['list_shop'][0] = array('id'=>'all','name'=>'全部');
			$form_list['list_shop'] = array_merge($form_list['list_shop'], $list['shop']);
			$form_list['list_logistics'] = $list['logistics'];

            $this->assign('list_sync_status', $form_list['list_sync_status']);
            $this->assign('list_shop', $form_list['list_shop']);
            $this->assign('setting',$setting);
            $this->assign('formatter_shop', json_encode($list['shop']));
            $this->assign('formatter_logistics', json_encode($list['logistics']));
            $this->display("api_logistics_sync_edit");
        }
    }

    //首页代办事项跳转至物流同步页面，重新获取数据函数
    public function search($page=1, $rows=20, $search = array(), $sort = 'sync_status', $order = 'ASC'){
        $this->ajaxReturn(Factory::getModel('ApiLogisticsSync')->getApiLogisticsSyncList($page, $rows, $search, $sort, $order));
    }

    public function showLogisticsDilog() {
        $rec_id = I("get.rec_id");
        $params = array(
            'rec_id'      => $rec_id,
            'chg_dialog'  => 'apiLogSynLogisticsDialog',
            'id_datagrid' => strtolower(CONTROLLER_NAME . '_' . "getApiLogisticsSyncList" . '_datagrid'),
            'form_id'     => 'als_save_form',
            'form_url'    => U('ApiLogisticsSync/chgLogistics'),
        );

        $form_list = UtilDB::getCfgList(array('logistics'));
        $this->assign("params", $params);
        $this->assign('list_logistics', $form_list['logistics']);
        $this->display('dialog_chg_logistics');
    }

    public function chgLogistics() {
        $logistics = I("post.");
        $rec_id    = $logistics["rec_id"];
        unset($logistics["rec_id"]);
        $this->ajaxReturn(json_encode(D('ApiLogisticsSync')->chgLogistics($logistics, $rec_id)), "EVAL");
    }
    public function retrySync()
    {
        $ids = I('post.ids','',C('JSON_FILTER'));
        $result['info']   = "success";
        $result['status'] = 0;
        $result['data'] = array();
        try{
            D('ApiLogisticsSync')->retrySync($ids,$result);
        }catch(\Exception $e){
            $msg = $e->getMessage();
            \Think\Log::write(__CONTROLLER.'-retrySync-'.$msg);
            $result= array(
              'info'=>'未知错误,请联系管理员',
              'status'=>1
            );
        }
    	$this->ajaxReturn($result);
    }
    public function setSyncSuccess()
    {
		$ids=I('post.ids','',C('JSON_FILTER'));
		$result['info']='success';
		$result['status']=0;
		$result['data']=array();
		try{
			D('ApiLogisticsSync')->setSyncSuccess($ids,$result);
		}catch(\Exception $e){
			$msg=$e->getMessage();
			\Think\Log::write(__CONTROLLER.'--setSyncSuccess--'.$msg);
			$result=array(
				'info'=>'未知错误，请联系管理员',
				'status'=>1
			);
		}
    	$this->ajaxReturn($result);
    }
    public function cancelSync()
    {
    	$ids=I('post.ids','',C('JSON_FILTER'));
		$result['info']='success';
		$result['status']=0;
		$result['data']=array();
		try{
			D('ApiLogisticsSync')->cancelSync($ids,$result);
		}catch(\Exception $e){
			$msg=$e->getMessage();
			\Think\Log::write(__CONTROLLER.'--cancelSync--'.$msg);
			$result=array(
				'info'=>'未知错误，请联系管理员',
				'status'=>1
			);
		}
    	$this->ajaxReturn($result);
    }

    public function manualSyncLogistics(){
        $ids=I('post.ids','',C('JSON_FILTER'));
        $result['status']=0;
        $sid  = get_sid();
        try{
            $LogisticsManager = ManagerFactory::getManager("Logistics");
            $LogisticsManager->manualSyncLogistics($sid,$result,$ids);
        }catch(\Exception $e){
            $msg=$e->getMessage();
            \Think\Log::write(__CONTROLLER.'--manualSyncLogistics--'.$msg);
            $result=array(
                'info'=>'未知错误，请联系管理员',
                'status'=>1
            );
        }
        $this->ajaxReturn($result);
    }

    public function getErrorMsgSolution($id){

            $id = I('get.id');
            $result = array('status'=>0,'info'=>array());
            try{
                D('ApiLogisticsSync')->getErrorMsgSolution($id,$result);
            }catch (\Exception $e){
                \Think\Log::write('getErrorMsgSolution:'.$e->getMessage());
            }
            //$this->ajaxReturn($result);
            $id_list  = array(
                "datagrid" => "dialog_solution_datagrid",
                "list"     => "dialog_solution_list",
                "message"  => "dialog_solution_message",
                "show"     => "dialog_solution_datagrid",
                "dialog"   => "dialog_solution_file"
            );
            $datagrid = array(
                "id"      => $id_list["datagrid"],
                "options" => array(
                    "pagination" => false,
                    "fitColums"  => false,
                    "rownumbers" => false,
                    "border"     => true
                ),
                //'fields' => get_field("ApiLogisticsSync","dialog_solution")
        );
            $this->assign("id_list", $id_list);
            $this->assign("datagrid", $datagrid);
            $this->assign('res',$result['info']['rows']);
            $this->assign('status',$result['status']);
            $this->assign('id',$result['info']['rows']['msg_id']);
            $this->display('dialog_solution');
    }

    public function solveSolution($id){
        //先通过api_logistics_sync表里rec_id查询得到该物流同步信息  然后通过店铺id去查询该店铺是否已经下载过物流公司
        //如果没有下载过物流公司，去下载物流公司，然后再去查看是否有对应的物流
        try{
            $msg    = array("status" => 0, "info" => "下载物流公司失败");
            $result = array('status'=>1 , 'info'=>'请查看<a href="http://114.55.14.106/ESearch/?type=stock&class=logistics_sync_question#logistics_sync_question_shop" target="_blank"><span style="color: blue;">帮帮精灵</span></a>');
            $res = D('ApiLogisticsSync')->solveSolution($id);
            if($res['status']==1){
                $db = M('api_logistics_shop');
                $LogisticsManager = ManagerFactory::getManager("LogisticsCompany");
                $LogisticsManager->manualDownloadShopLogistics($db, get_sid(), "", $res['info']['shop_id'], $msg);
                if($msg['status']==0){
                    SE('下载物流公司，请联系管理员');
                }else{
                    $result = array(
                        'info'=>'操作成功，请将同类错误订单选中进行重新物流同步后查看结果',
                        'status'=>0
                    );
                }
            }

        }catch(BusinessLogicException $e){
            $result = array(
                'info'=>$e->getMessage(),
                'status'=>1
            );
        }catch(\Exception $e){
            $message=$e->getMessage();
            \Think\Log::write(__CONTROLLER.'--manualSyncLogistics--'.$message);
            $result=array(
                'info'=>'未知错误，请联系管理员',
                'status'=>1
            );
        }
        $this->ajaxReturn($result);
    }

}