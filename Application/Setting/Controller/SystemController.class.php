<?php

/**
 * Created by PhpStorm.
 * User: yanfeng
 * Date: 2015/10/28
 * Time: 10:21
 */
namespace Setting\Controller;

use Common\Common\Factory;
use Common\Controller\BaseController;
use Common\Common\UtilTool;
use Common\Common\ExcelTool;

class SystemController extends BaseController {

    /**
     * 返回系统设置页面
     * author:luyanfeng
     */
    public function showSystemSetting() {
        $setting = Factory::getModel("System")->getSystemSetting();
        $role=D('Setting/Employee')->getRole(get_operator_id());
        $this->assign("role", $role);
        $id=I('get.id');
        $this->assign('id',$id);
        $tab_type = I('get.tab_type');
        $this->assign('tab_type',empty($tab_type)?'':$tab_type);
        $config_name = I('get.config_name');
        $this->assign('config_name',empty($config_name)?'':$config_name);
        $info = I('get.info');
        $this->assign('info',empty($info)?'':$info);
        $this->assign("setting", json_encode($setting));
        $this->assign("stalls_init", $setting['stalls_system_init']);
        $this->display("show");
    }

    public function showCommonMenuSetting(){
        $id=I('get.id');
        $this->assign('id',$id);
        $this->display("common_menu");
    }

    /**
     * 更新系统设置信息
     * author:luyanfeng
     */
    public function updateSystemSetting() {
        $val = I("post.data");
        if($val['stockout_sendbill_print_status'] == 'all'){
            $val['stockout_sendbill_print_status'] = '3';
        }
        if($val['stockout_logistics_print_status'] == 'all'){
            $val['stockout_logistics_print_status'] = '3';
        }
        try {
            $M = M();
            $M->startTrans();
            $res["status"] = 1;
            $res["info"] = "操作成功";
            foreach ($val as $k => $v) {
                $temp = array();
                $temp["key"] = $k;
                $temp["value"] = $v;
                $temp["class"] = "system";
                $temp["value_type"] = 2;
                $temp["log_type"] = 5;
                $result = Factory::getModel("System")->updateSystemSetting($temp);


                if (!$result) {
                    $res["status"] = 0;
                    $res["info"] = "操作失败";
                    break;
                }

            }
            $common_menu=D('System')->commonMenu();
            $res['data']=$common_menu;
            if ($res["status"] == 0) {
                $M->rollback();
            }
            $M->commit();
            $this->ajaxReturn($res);
        } catch (\Exception $e) {
            $M->rollback();
            \Think\Log::write($e->getMessage());
            $res["status"] = 0;
            $res["info"] = "操作失败";
        }
        $this->ajaxReturn($res);
    }

    //获取常用菜单
    public function getMenuTree()
    {
        $employee_id=get_operator_id();
        $result=array();
        try
        {
            if($employee_id!=1){
                $where=array(
                    'type'=>0,
                    'employee_id'=>$employee_id,
                    'is_denied'=>0
                );
                $menus=M('cfg_employee_rights')->field("right_id")->where($where)->select();
                foreach($menus as $row){
                    $menu[]=$row['right_id'];
                }
                $menu_ids=M('dict_url')->field("url_id,parent_id")->order('parent_id ASC, sort_order DESC')->where(array('type'=>array('in',array(1,2,4)),'url_id'=>array('in',$menu),'is_leaf'=>array('neq',2)))->select();
            }else{
                $menu_ids=M('dict_url')->field("url_id,parent_id")->order('parent_id ASC, sort_order DESC')->where(array('type'=>array('in',array(1,2,4)),'is_leaf'=>array('neq',2)))->select();
            }
            $children=array();
            foreach($menu_ids as $ids){
                $children[]=$ids['url_id'];
                if(!in_array($ids['parent_id'],$children)){
                    $children[]=$ids['parent_id'];
                }
            }
            $result=M('dict_url')->field("url_id AS id, name AS text,parent_id")->order('parent_id ASC, sort_order DESC')->where(array('url_id'=>array('in',$children),'is_leaf'=>array('neq',2)))->select();
            $where=array(
                'user_id'=>$employee_id,
                'type'=>2,
            );
            $rights=M('cfg_user_data')->field('data')->where($where)->find();
            $right=explode(',',$rights['data']);
            $len=count($result);
            $map=array();
            for($i=0;$i<$len;$i++){
                foreach ($right as $r)
                {
                    if($result[$i]['id']==$r && $result[$i]['parent_id']!=0)
                    {
                        $result[$i]['checked']=true;
                        $map[$result[$i]['parent_id']]=true;
                    }
                }
            }
            for($i=0;$i<$len;$i++)
            {
                if(isset($map[$result[$i]['id']]))
                {
                    if(isset($result[$i]['checked']))
                    {
                        unset($result[$i]['checked']);
                    }
                }
            }
            $result=UtilTool::array2tree($result, 'id', 'parent_id', 'children');
        }catch (\PDOException $e)
        {
            \Think\Log::write($e->getMessage());
        }
        $this->ajaxReturn(array_reverse($result));
    }

    /**
     * 关闭档口模式
     */
    public function hideStallsSetting(){
        try{
            $res["status"] = 1;
            $res["info"] = "操作成功";
            $data = array(
                'key'           => 'stalls_system_init',
                'value'         => 0,
                'class'         => 'system',
                'value_type'    => 2,
                'log_type'      => 5,
            );
            $result = D('Setting/System')->hideStallsSetting($data);
            if(!$result){
                $res["status"] = 0;
                $res["info"] = "操作失败";
            }
        } catch (\Exception $e) {
            \Think\Log::write($e->getMessage());
            $res["status"] = 0;
            $res["info"] = "操作失败";
        }
        $this->ajaxReturn($res);
    }

}