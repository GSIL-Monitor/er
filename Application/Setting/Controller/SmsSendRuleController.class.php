<?php
namespace Setting\Controller;

use Common\Controller\BaseController;
use Common\Common\UtilDB;
use Common\Common\UtilTool;
use Think\Exception\BusinessLogicException;

class SmsSendRuleController extends BaseController
{
    public function getSmsSendRule()
    {
        try{
            $id_list=array(
                'tab_container'=>'sms_send_rule_tab_container',
            );
            $arr_tabs=array(
                array('id'=>$id_list['tab_container'],'url'=>U('SmsSendRule/showTabs',array('tabs'=>'sms_send_rule')).'?tab=sms_send_rule','title'=>'短信策略'),
                //array('id'=>$id_list['tab_container'],'url'=>U('SmsSendRule/showTabs',array('tabs'=>'sms_template')).'?tab=sms_template','title'=>'短信模板')
            );
            $this->assign('id_list',$id_list);
            $this->assign('arr_tabs',json_encode($arr_tabs));
        }catch(\Exception $e){
            $this->assign('message',$e->getMessage());
            $this->display('Common@Exception:dialog');
            exit();
        }
        $this->display('sms_send_rule');
    }

    function showTabs($tab)
    {
        switch ($tab){
            case 'sms_send_rule':
                $this->getSmsSendRuleList();
                break;
            case 'sms_template':
                U('SmsTemplate/getSmsTemplate');
                break;
            default:
                $this->assign('message','Not Found Tabs');
                $this->display('Common@Exception:dialog');
                exit();
                break;
        }
    }
    public function getSmsSendRuleList($page=1, $rows=20,$sort = 'rec_id', $order = 'desc')
    {
        if(IS_POST)
        {
            $this->ajaxReturn(D('SmsSendRule')->getSmsSendRuleList($page,$rows,$sort,$order));
        }else{
            $id_list  = array(
                "id_datagrid"  => "sms_send_rule_id_datagrid",
                "toolbar"   => "sms_send_rule_toolbar",
                "form"      => "sms_send_rule_search_form",
                "add"       => "sms_send_rule_add_dialog",
                "add_form"  => "sms_send_rule_add_form",
                "edit"      => "sms_send_rule_edit_dialog",
                "edit_form" => "sms_send_rule_edit_form",
            );
            $datagrid=array(
                'id'=>$id_list['id_datagrid'],
                'options'=>array(
                    'url'=>U('SmsSendRule/getSmsSendRuleList', array('grid' => 'datagrid')),
                    'toolbar' => "#{$id_list['toolbar']}",
                    'singleselete'=>true,
                    'fitClumns'=>true,
                ),
                'fields'=>get_field('SmsSendRule','sms_send_rule')
            );
            $params=array(
                'datagrid'=>array('id'=>$id_list['id_datagrid']),
                'edit'=>array('id'=>$id_list['edit'],'url'=>U('SmsSendRule/editSmsSendRule'),'title'=>'编辑短信策略','height'=>250,'width'=>300),
                'add'=>array('id'=>$id_list['add'],'url'=>U('SmsSendRule/addSmsSendRule'),'title'=>'新建短信策略','height'=>240,'width'=>300),
                'delete'=>array('url'=>U('SmsSendRule/deleteSmsSendRule'))
            );
            $this->assign('id_list',$id_list);
            $this->assign('datagrid',$datagrid);
            $this->assign('params',json_encode($params));
            $this->display('dialog_sms_send_rule');
        }
    }
    public function addSmsSendRule()
    {
        if(IS_POST)
        {
            $data = I("post.data");
            $res['status'] = 1;
            $res['status'] = '操作成功';
            try{
                D('SmsSendRule')->addSmsSendRule($data);
            }catch (BusinessLogicException $e){
                $res['status'] = 0;
                $res['info'] = $e->getMessage();
            }catch(\Exception $e){
                $res['status'] =0;
                $res['info'] = '';
                \Think\Log::write($e->getMessage());
            }
            $this->ajaxReturn($res);
        }else{
            $id_list = array(
                "add_form"  => "sms_send_rule_add_form",
            );
            $this->assign('id_list',$id_list);
            $template_list = D('SmsSendRule')->getSmsTemplate();
            $list        = UtilDB::getCfgRightList(array("shop"));
            $shop_list   = $list["shop"];
            $this->assign('template_list',$template_list);
            $this->assign('shop_list',$shop_list);
            $this->display('add');
        }
    }

    public function editSmsSendRule()
    {
        if(IS_POST)
        {
            $data = I("post.data");
            $res['status'] = 1;
            $res['status'] = '操作成功';
            try{
                D('SmsSendRule')->updateSmsSendRule($data);
            }catch (BusinessLogicException $e){
                $res['status'] = 0;
                $res['info'] = $e->getMessage();
            }catch(\Exception $e){
                $res['status'] =0;
                $res['info'] = '';
                \Think\Log::write($e->getMessage());
            }
            $this->ajaxReturn($res);
        }else{
            $id_list = array(
                "edit_form" => "sms_send_rule_edit_form",
            );
            $id      = I("get.id");
            try{
                $sms_send_rule_array = D('SmsSendRule')->getRuleById($id);
            }catch (\Exception $e){
                \Think\Log::write($e->getMessage());
                $sms_send_rule_array = array();
            }
            $template_list = D('SmsSendRule')->getSmsTemplate();
            $this->assign('rule',$sms_send_rule_array[0]);
            $this->assign('id_list',$id_list);
            $list        = UtilDB::getCfgRightList(array("shop"));
            $shop_list   = $list["shop"];
            $this->assign('template_list',$template_list);
            $this->assign('shop_list',$shop_list);
            $this->display('edit');
        }
    }
    public function deleteSmsSendRule(){
        $id = I('post.id');
        $res = ['status' => 1, 'info' => '操作成功'];
        try {
            D("SmsSendRule")->deleteSmsSendRuleById($id);
        } catch (BusinessLogicException $e) {
            $res = ['status' => 0, 'info' => $e->getMessage()];
        } catch (\Exception $e) {
            \Think\Log::write($e->getMessage());
            $res = ['status' => 0, 'info' => self::UNKNOWN_ERROR];
        }
        $this->ajaxReturn($res);
    }



}