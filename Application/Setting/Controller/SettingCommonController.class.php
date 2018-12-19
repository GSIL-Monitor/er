<?php
namespace Setting\Controller;
use Common\Controller\BaseController;


class SettingCommonController extends BaseController
{

    public function showTabsView($tab, $prefix, $field = "Setting/SettingCommon")
    {
        $arr_tab = array(
            //'stockout_order_detail' => 'stockout_order_detail',
            'login_log' => 'login_log',
            'position_spec'=>'position_spec',
            //'employee_rights' => 'employee_rights',
            'upon_logistics' => 'upon_logistics'
        );
        $arr_tab_page =array(
            'position_spec'=>true
        );
        if (empty($arr_tab[$tab])) {
            \Think\Log::write('SettingCommon->联动未知的tabs',\Think\Log::WARN);
            return null;
        }
        $tab_view='tabs_setting_common';
        $datagrid=array(
        		'id'=>$prefix.'datagrid_'.$tab,
        		'style'=>'',
        		'class'=>'easyui-datagrid',
        		'options'=> array(
        				'title'=>'',
        				'pagination'=>false,
        				'fitColumns'=>false,
        		),
        		'fields' => get_field($field, $arr_tab[$tab])
        );
        $datagrid['options']['pagination'] = $arr_tab_page[$tab]===true?true:false;
        if (count($datagrid['fields']) < 12) 
        {
            $datagrid['options']['fitColumns'] = true;
        }
        $this->assign('datagrid', $datagrid);
        $this->display($tab_view);
    }

    public function updateTabsData($id, $datagridId)
    {
        $data = array();
        $id = intval($id);
        if ($id == 0) 
        {//过滤非法字符（非数字字符串经过intval()方法后自动转换成0）和屏蔽第一次请求
            $data = array('total' => 0, 'rows' => array());
            $this->ajaxReturn($data);
        }
        $tab = substr($datagridId, strpos($datagridId, '_') + 1);//得到tab

        switch (strtolower($tab)) {
            /* case 'sales_stockout_log':
                $data = D('Trade/TradeCommon', 'Controller')->updateTabsData($id, $datagridId);
                break; */
            case 'login_log':
                $data = D('Setting/Employee')->getLoginLogs($id);
                break;
            case 'position_spec':
                $page = I('post.page');
                $rows = I('post.rows');
                $data = D('Stock/StockSpecPosition')->getPositionSpecInfo($id,$page,$rows,$sort = 'ssp.rec_id');
                break;
            case 'upon_logistics':
                $data = D('Setting/Logistics')->getUponLogistics($id);
                break;
            default:
                \Think\Log::write("unknown tab in showTabDatagridData:" . print_r($tab, true));
                $data = array('total' => 0, 'rows' => array());
                break;
        }
        $this->ajaxReturn($data);
    }
}
