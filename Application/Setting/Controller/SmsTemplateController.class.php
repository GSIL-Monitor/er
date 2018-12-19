<?php
namespace Setting\Controller;

use Common\Controller\BaseController;
use Think\Exception;
use Think\Exception\BusinessLogicException;


class SmsTemplateController extends BaseController{
    public function getSmsTemplate($page = 1, $rows = 10, $search = array(), $sort = 'cst.rec_id', $order = 'desc'){
        if(IS_POST){
            $this->ajaxReturn(D('SmsTemplate')->getSmsTemplate($page,$rows,$search,$sort,$order));
        }else{

            $id_list = array(
                'datagrid'  => 'smstemplate_datagrid',
                'toolbar'   => 'smstemplate_toolbar',
                "form"      => "smstemplate_form",
                "add"       => "smstemplate_add_dialog",
                "add_form"  => "smstemplate_add_form",
                "edit"      => "smstemplate_edit_dialog",
                "edit_form" => "smstemplate_edit_form"
            );
            $datagrid = array(
                "id"      => $id_list["datagrid"],
                "options" => array(
                    "toolbar" => $id_list["toolbar"],
                    "url"     => U("SmsTemplate/getSmsTemplate")
                ),
                "fields"  => D('Setting/UserData')->getDatagridField('Setting/SmsTemplate','sms_template'),
            );
            $params   = array(
                "datagrid" => array(
                    "id"  => $id_list["datagrid"],
                    "url" => U("SmsTemplate/getSmsTemplate")
                ),
                "add"      => array(
                    "id"     => $id_list["add"],
                    "title"  => "新建模板",
                    "url"    => U("SmsTemplate/addSmsTemplate"),
                    'width'  => 700,
                    "height" => 360
                ),
                "edit"     => array(
                    "id"     => $id_list["edit"],
                    "title"  => "编辑模板",
                    "url"    => U("SmsTemplate/editSmsTemplate"),
                    "height" => 360
                ),
                'delete'     => [
                    'url' => U('SmsTemplate/deleteSmsTemplate')
                ],
                "search"   => array("form_id" => $id_list["form"])
            );
            $this->assign("id_list", $id_list);
            $this->assign("datagrid", $datagrid);
            $this->assign("params", json_encode($params));
            $this->display("show");
        }
    }
    public function addSmsTemplate(){
        if(IS_POST){
            $data = I('post.data');
            $res=array('status'=>1,'info'=>'操作成功');
            try{
                if (!isset($data["title"]) || $data["title"] == "") {
                    $res["status"] = 0;
                    $res["info"]   = "模板名称不能为空";
                    $this->ajaxReturn($res);
                }
                if(D('SmsTemplate')->checkSmsTitle($data['title'])){
                    $res['status'] = 0;
                    $res['info'] = '模板名称已存在';
                    $this->ajaxReturn($res);
                }
                $data['created']  = date("Y-m-d G:i:s");
                $data['modified'] = date("Y-m-d G:i:s");
                $data['title'] = trim_all($data['title']);
                $M = M();
                $M->startTrans();
                D('SmsTemplate')->updateSmsTemplate($data);
            } catch (BusinessLogicException $e) {
                $res = array("status" => 0, "info" => parent::UNKNOWN_ERROR);
            }catch (\Exception $e) {
                $M->rollback();
                \Think\Log::write($e->getMessage());
                $res["status"] = 0;
                $res["info"]   = parent::UNKNOWN_ERROR;
            }
            $this->ajaxReturn($res);
        }else{
            $id_list = array(
                'form' => 'smstemplate_add_form'
            );
            $this->assign("id_list", $id_list);
            $this->display("add");
        }
    }
    public function editSmsTemplate(){
        if (IS_POST) {
            $data = I("post.data");
            $res=array('status'=>1,'info'=>'操作成功');
            try{
                if (!isset($data["title"]) || $data["title"] == "") {
                    $res["status"] = 0;
                    $res["info"]   = "模板名称不能为空";
                    $this->ajaxReturn($res);
                }
                if (!isset($data["sign"]) || $data["sign"] == "") {
                    $res["status"] = 0;
                    $res["info"]   = "模板签名不能为空";
                    $this->ajaxReturn($res);
                }
                $M                   = M();
                $M->startTrans();
                $data['modified'] = date("Y-m-d G:i:s");
                $result = D('SmsTemplate')->getTemplateInfo($data['title'],'title',$fields=array('rec_id,title'));
                if($result['status'] == 0){
                    $this->ajaxReturn($result);
                }else{
                    foreach ($result["data"] as $v) {
                        if (!isset($data["rec_id"]) || $data["rec_id"] != $v["rec_id"]) {
                            $res["status"] = 0;
                            $res["info"]   = "该模板名称已存在";
                            $this->ajaxReturn($res);
                        }
                    }
                }
                D('SmsTemplate')->updateSmsTemplate($data);
                $M->commit();
            } catch(BusinessLogicException $e){
                SE($e->getMessage());
            }catch (\Exception $e){
                $M->rollback();
                \Think\Log::write($e->getMessage());
                $res["status"] = 0;
                $res["info"]   = parent::UNKNOWN_ERROR;
            }
            $this->ajaxReturn($res);
        }else{
            $id      = I("get.id");
            $id_list = array(
                "form" => "smstemplate_edit_form"
            );
            try{
                $sms_template_array = D('SmsTemplate')->getTemplateInfo($id,'rec_id',$fields=array("cst.rec_id,cst.is_marketing,cst.title,cst.sign,cst.content,cst.is_merge,cst.is_split"));
            }catch (\Exception $e){
                \Think\Log::write($e->getMessage());
                $sms_template_array = array();
            }
            $this->assign("id_list", $id_list);
            $this->assign("template", $sms_template_array['data'][0]);
            $this->assign("sms_template_edit", json_encode($sms_template_array['data'][0]));
            $this->display("edit");
        }

    }
    public function deleteSmsTemplate(){
        $id = I('post.id');
        $res = ['status' => 1, 'info' => '操作成功'];
        try {
            D("SmsTemplate")->deleteSmsTemplateById($id);
        } catch (BusinessLogicException $e) {
            $res = ['status' => 0, 'info' => $e->getMessage()];
        } catch (\Exception $e) {
            \Think\Log::write($e->getMessage());
            $res = ['status' => 0, 'info' => self::UNKNOWN_ERROR];
        }
        $this->ajaxReturn($res);
    }


}