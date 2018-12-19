<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/12/2
 * Time: 16:15
 */

namespace Purchase\Controller;


use Common\Controller\BaseController;
use Common\Common\UtilDB;
use Think\Exception;

class AlarmPerDayController extends BaseController
{
    public function getStockAlarmList()
    {
        try
        {
            $id_list=self::getIDList($this->id_list,array('form','toolbar','hidden_flag','datagrid','more_content','goods_class'));
            $datagrid = array(
                "id"      => $id_list["datagrid"],
                "options" => array(
                    "url"        =>  U("AlarmPerDay/search"),
                    "toolbar"    => '#'.$id_list["toolbar"],
                    "fitColumns" => false,
                    'singleSelect'=>false,
                    'ctrlSelect'=>true,
                ),
                "fields"  => get_field('AlarmPerDay','alarmperday'),
            );
            $params = array(
                'datagrid'  =>array('id'=>$id_list['datagrid']),
                'purchase'  =>array(
                    'id'        => 'reason_show_dialog',
                    'url'       => U('Purchase/PurchaseOrder/alarmPurchase'),
                    'title'     => '采购开单',
                    'height'    => '550',
                    'width'     => '1250'),
                'search'    =>array('form_id'=>$id_list['form'],'more_content' => $id_list['more_content'],'hidden_flag' => $id_list['hidden_flag']),
                'form'      =>array('id'=>$id_list['form']),
            );
            $list = UtilDB::getCfgRightList(array('brand','warehouse','provider'),array('provider'=>array('is_disabled'=>0,'id'=>array('gt',0))));
            foreach($list['provider'] as $k=>$v){if($v['id']==0) unset($list['provider'][$k]);}
            $warehouse_default['0'] = array('id' => 'all', 'name' => '全部');
            $warehouse_array = array_merge($warehouse_default, $list['warehouse']);
            $brand_default['0'] = array('id' => 'all', 'name' => '全部');
            $brand_array = array_merge($brand_default, $list['brand']);
			
//			$provider = D('Setting/PurchaseProvider')->getALlPurchaseProvider(array('is_disabled'=>0,'id'=>array('gt',0)));
//			$list['provider'] = $provider['data'];
			$provider_default['0'] = array('id' => 'all','name'=>'全部');
			$provider_array = array_merge($provider_default,$list['provider']);
			

            $this->assign('warehouse_array', $warehouse_array);
			$this->assign('provider_array', $provider_array);
            $this->assign('brand_array', $brand_array);
            $this->assign('params', json_encode($params));
            $this->assign('datagrid',$datagrid);
            $this->assign("id_list", $id_list);
        }catch(\Exception $e)
        {
            $this->assign('message',$e->getMessage());
            $this->display('Common@Exception:dialog');
            exit();
        }
        $this->display('show');
    }
    public function search($page=1, $rows=20, $search = array(), $sort = 'id', $order = 'desc')
    {
        try{
                        $data = D('Stock/StockSpec')->alarmPerDaySearch($page, $rows, $search, $sort, $order);
        }catch (Exception $e)
        {
            \Think\Log::write(CONTROLLER_NAME.'-search-'.$e->getMessage());
            $data = array('total'=>0,'rows'=>array());
        }
        $this->ajaxReturn($data);
    }
}