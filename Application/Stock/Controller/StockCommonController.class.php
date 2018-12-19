<?php
namespace Stock\Controller;

use Common\Controller\BaseController;
use Common\Common;


class StockCommonController extends BaseController {
    
    public function showTabsView($tab,$prefix,$app = "Stock/StockCommon")
    {
        $arr_tab=array(
			//key值对应showTabDatagridData函数中的case值，value值对应field值
            'stockout_order_detail'=>'stockout_order_detail',
            'stocksales_print_detail'=>'stocksales_print_detail',
			//'executingSale'=>'showSalesTradeDetaiData',
			'stockInManagementDetail'=>'stockindetail',
			'stockOutDetail'=>'stockoutdetail',
			'stockmanagementdetail'=>'stockoutdetail',
			'stockInLog'=>'stock_log',
			'stockOutLog'=>'stock_log',
			'stock_trans_detail'=>'stock_trans_detail',
			'stock_trans_log'=>'stock_log',
			'executing_sale_trade'=>'salestradedetail',
			'warehouse_stock_detail'=>'warehousestockdetail',
            'api_logistics_sync_detail'=>'api_logistics_sync_detail',
            'stockPdDetail'=>'stockpddetail',
			'stockPd'=>'stock_log',
			'stockpddetail_specifics' => 'stockpddetailspecifics',
			'stock_spec_log' => 'stockspeclog',
			'Outside_wms_dateil'=>'outsidewmsdetail',
			'Outside_wms_log'=>'outsidewmslog',

        );
		//映射相应的是否添加分页
		$arr_tab_page = array(
			'stockInManagementDetail'=>true,
			'stockmanagementdetail'=>true,
			'executing_sale_trade'=>true,
			'stock_spec_log'=>true,
		);
        if(empty($arr_tab[$tab]))
        {
            \Think\Log::write('StockCommon->联动未知的tabs');
            return null;
        }
        $tab_view='tabs_stock_common';
        //特殊界面添加映射
        $view_map = array(
            'warehouse_stock_detail'=>'stock_spec_detail'
        );
		$id = $prefix.'datagrid_'.$tab;
		$fields = D('Setting/UserData')->getDatagridField($app, $arr_tab[$tab]);
		$datagrid = array(
			'id'         =>$id,
			'options'    => array(
				'title'      => '',
				'url'        => null,
				'fitColumns' => false,
				'pagination' => false,
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
        $this->display(empty($view_map[$tab])?$tab_view:$view_map[$tab]);
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
			case 'stockpddetail':			
				$data = D('Stock/StockpdDetail')->showStockpdDetailData($id);
				break;
			case 'stockpd':
				$data = D('StockInoutLog')->showLog(4,$id);
				//$data = D('Stock/StockpdDetail')->showStockpdLog($id);
				break;
			case 'stocksales_print_detail':
				$data = D('Stock/StockoutOrderDetail')->showStockoutOrderDetailData($id,'stocksales_print_detail');
				break;
            case 'stockout_order_detail':
				$data = D('Stock/StockoutOrderDetail')->showStockoutOrderDetailData($id);
				break;
			case 'sales_stockout_log':
				$data = D('Trade/TradeCommon','Controller')->updateTabsData($id,$datagridId);
				break;
			case 'sales_order_detail' :
				$data = D('Trade/TradeCommon','Controller')->updateTabsData($id,$datagridId);
				break;
			case 'executing_sale_trade':
				$data = D('StockManagement')->showSalesTradeDetaiData($id);
				break;
			case 'stockinlog':
				$data = D('StockInoutLog')->showLog(1,$id);
				break;
			case 'stockoutlog':
				$data = D('StockInoutLog')->showLog(2,$id);
				break;
			case 'stockinmanagementdetail':
				$data = D('StockinOrderDetail','Model')->showData($id);
				break;
			case 'stockoutdetail':
				$data = D('StockOutOrder')->showstockOutDetailData($id);
				break;
			case 'stockmanagementdetail':
				$data = D('StockOutOrder')->showstockOutDetailData($id);
				break;
			case 'stock_trans_detail':
				$data = D('StockTransferDetail','Model')->getStockTransDetailData($id);
				break;
			case 'stock_trans_log':
				$data = D('StockInoutLog')->showLog(3,$id);
				break;
			case 'api_logistics_sync_detail':
				$data = D('Stock/ApiLogisticsSync')->showTabInfor($id);
				break;
			case 'warehouse_stock_detail':
				$data = D('Stock/StockManagement')->showTabInfoAboutWarehouseStock($id);
				break;
			case 'sales_multi_logistics':
				$data = D('Stock/SalesMultiLogistics')->getStockPrintTabInfo($id);
				break;
			case 'stockpddetail_specifics':
				$data = D('Stock/StockPdProfitLoss')->getStockPdProfitLossTabInfo($id);
				break;
            case 'stock_spec_log':
				$data = D('Stock/StockSpecLog')->getStockGoodsLog($id);
				break;
			case 'outside_wms_dateil':
				$data = D('Stock/OutsideWmsDateil')->getOutsideWmsDateil($id); //委外出入库tab详情
				break;
			case 'outside_wms_log':
				$data = D('Stock/OutsideWmsDateil')->getOutsideWmsLog($id);//委外出入库日志
				break;
			default:
				\Think\Log::write("unknown tab in showTabDatagridData:" . print_r($tab, true));
				return '';
		}
		
		$this->ajaxReturn($data);
	}
}