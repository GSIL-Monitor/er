<?php
/**
 * 关于旺店通介绍的控制器
 *
 * @author gaosong
 * @date: 15/12/20
 * @time: 上午11:09
 */
namespace Help\Controller;
use Common\Controller\BaseController;
class SystemAboutController extends BaseController
{
    /**
     *获取旺店痛介绍界面
     */
    public function getAbout(){
        $this->display("show");
    }
}