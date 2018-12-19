<?php

namespace Help\Controller;
use Common\Controller\BaseController;
use Think\Exception\BusinessLogicException;
class ProcessMenuController extends BaseController
{
    
    public function getProcessMenu()
    {
    	$process_menu=array();
    	try 
    	{
    		$process=array(
    			'setting'=>array('setting'=>8,'shop'=>9,'logistics'=>10,'warehouse'=>11,'employee'=>16,'g_template'=>13,'l_template'=>14),
    			'goods'=>array('class'=>18,'brand'=>19,'goods'=>20,'spec'=>21,'suite'=>22,'platform'=>25),
    			'stock'=>array('in'=>38,'in_m'=>39,'manage'=>40,'out'=>36,'out_m'=>37,'transfer'=>58,'transfer_m'=>59),
    			'trade'=>array('down'=>28,'original'=>29,'manual'=>27,'check'=>26,'manage'=>30,'stock_out'=>32,'refund'=>31,'print'=>34)
    		);
    		$menu=D('Home/Menu')->getMenu();
    		$dict_menu = array();
	        foreach ($menu as $m) 
	        {
	            $dict_menu[strval($m['id'])]=$m;
	        }
	        foreach ($process as $key => $pm) 
	        {
	        	$process_menu[$key]=array();
	        	foreach ($pm as $k => $v) 
	        	{
	        		$process_menu[$key][$k]=$dict_menu[$v];
	        	}
	        }
    	}catch(BusinessLogicException $e)
    	{
    		$process_menu=array('setting'=>array(),'goods'=>array(),'stock'=>array(),'trade'=>array());
    	}
    	$this->assign('setting',$process_menu['setting']);
    	$this->assign('goods',$process_menu['goods']);
    	$this->assign('stock',$process_menu['stock']);
    	$this->assign('trade',$process_menu['trade']);
        $this->display("show");
    }
}