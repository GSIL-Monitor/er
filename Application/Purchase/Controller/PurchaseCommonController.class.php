<?php
namespace Purchase\Controller;

use Common\Controller\BaseController;
use Common\Common;


class PurchaseCommonController extends BaseController {
    
    public function showTabsView($tab,$prefix,$app = "Purchase/PurchaseCommon")
    {
        $arr_tab=array(
			//key值对应showTabDatagridData函数中的case值，value值对应field值
			'purchase_order_detail'=>'purchase_order_detail',
			'purchase_order_log'=>'purchase_order_log',
            'purchase_return_detail'=>'purchase_return_detail',
            'purchase_return_log'=>'purchase_return_log',
			'stalls_order_detail'=>'stalls_order_detail',
			'stalls_order_log'=>'stalls_order_log',
			'sorting_box_detail'=>'sorting_box_detail',
			'trade_order_detail'=>'trade_order_detail',
        );
		//映射相应的是否添加分页
		$arr_tab_page = array(
			'purchase_order_detail'=>false,
			'purchase_order_log'=>false,
            'purchase_return_detail'=>false,
            'purchase_return_log'=>false,
            'sorting_box_detail'=>false,
            'stalls_order_detail'=>true,
			'trade_order_detail'=>true,
		);
        if(empty($arr_tab[$tab]))
        {
            \Think\Log::write('PurchaseCommon->联动未知的tabs');
            return null;
        }
        $tab_view='tabs_purchase_common';
		$id = $prefix.'datagrid_'.$tab;
		$fields = get_field($app, $arr_tab[$tab]);
		$datagrid = array(
			'id'         =>$id,
			'options'    => array(
				'title'      => '',
				'url'        => null,
				'fitColumns' => false,
				'pagination' => true,
				'rownumbers' => true,

			),
			'fields'=>$fields,
			'class'=>'easyui-datagrid',
			'style'=>'padding:5px;'
		);
		$datagrid['options']['pagination'] = $arr_tab_page[$tab]===true?true:false;
        if(count($datagrid['fields'])<12 && count($datagrid['fields'])>6)
        {
            $datagrid['options']['fitColumns']=true;
        }
    
        $this->assign('datagrid',$datagrid);
        $this->display($tab_view);
    }
	
	public function showTabDatagridData($id,$datagridId)
	{
		$data=array();
		$id=intval($id);
		if($id==0)
		{//过滤非法字符（非数字字符串经过intval()方法后自动转换成0）和屏蔽第一次请求
			$data=array('total'=>0,'rows'=>array());
			$this->ajaxReturn($data);
		}
		$tab=substr($datagridId, strpos($datagridId, '_')+1);//得到tab
		switch (strtolower($tab)) {
			case 'purchase_order_detail':
				$data = D('Purchase/PurchaseOrderDetail')->showPurchasedetail($id);
				break;
			case 'purchase_order_log':
				$data = D('Purchase/PurchaseOrderDetail')->showPurchaselog($id);
				break;
            case 'purchase_return_detail':
                $data = D('Purchase/PurchaseReturnDetail')->showPurchasedetail($id);
                break;
            case 'purchase_return_log':
                $data = D('Purchase/PurchaseReturnDetail')->showPurchaseReturnlog($id);
                break;
			case 'stalls_order_detail':
				$data = D('Purchase/StallsOrderDetail')->showStallsDetail($id);
                break;
			case 'stalls_order_log':
				$data = D('Purchase/StallsOrderDetail')->showStallsLog($id);
                break;
			case 'sorting_box_detail':
				$data = D('Purchase/SortingWall')->showSortingBoxDeatil($id);
                break;
			case 'trade_order_detail':
				$data = D('Purchase/StallsOrderDetail')->showTradeOrderDetail($id);
				break;
			default:
				\Think\Log::write("unknown tab in showTabDatagridData:" . print_r($tab, true));
				return '';
		}
		$this->ajaxReturn($data);
	}
}