<?php
namespace Stock\Controller;

use Common\Common\UtilDB;
use Common\Controller\BaseController;
use Common\Common\DatagridExtention;
use Common\Common\UtilTool;
use Think\Exception;
use Think\Exception\BusinessLogicException;

class StockInOrderController extends BaseController
{

    public function show($id = "",$parent_win='')
    {
    	if(empty($id))
		{
			$prefix = "";
		}else{
			$prefix = "dialog";
		}

        $id_list = array();
        $id_list = self::getIDList($id_list,array('datagrid','toolbar','add','form','edit'),$prefix);
        
		$fields = get_field("StockIn","stockinorder");
        $datagrid = array(
            'id'        =>$id_list['datagrid'],
            'options'   => array(
                'title'         =>  '',
                'toolbar'       =>  "#{$id_list['toolbar']}",
                'fitColumns'    =>  true,
                'singleSelect'  =>  false,
                'ctrlSelect'    =>  true,
                'pagination'    =>  false,
            ),
            'fields'    => $fields,
            'class'     => 'easyui-datagrid',
            'style'     => "overflow:scroll",
        );

        
        $params = array(
            'add'=>array(
                '3'=>array(
                    'width'     =>'780',
                    'height'     =>'560',
                    'id'        =>'flag_set_dialog',
                    'title'     =>'退款单选择',
                    'url'       =>U('Stock/StockInOrder/showSalesRefund')."?prefix={$prefix}"
                ),
                '11'=>array(
                    'width'     =>'850',
                    'height'     =>'560',
                    'id'        =>'flag_set_dialog',
                    'title'     =>'采购单选择',
                    'url'       =>U('Stock/StockInOrder/showPurchase')."?prefix={$prefix}"
                ),
            ),
            'select'=>array(
                'id'        =>'flag_set_dialog',
				'url' => U('SalesStockoutExamine/showGoodsList'), 
				'title' => '条码选择货品',
            ),
            'form'=>array(
                'id'    =>$id_list['form'],
                'url'   =>U('Stock/StockInOrder/saveOrder')."?id={$id}"
            ),
            'datagrid'=>array(
                'id'    =>$id_list['datagrid'],
            ),
            'warehouse'=>array(
                'url'   =>U("Stock/StockInOrder/getDetailPosition")
            ),
            'id_list'=>$id_list,
            'id'=>$id,
            'prefix'=>$prefix
        );
        $test_json = json_decode($parent_win);
        if(!empty($parent_win)&&is_object($test_json))
        {
            $params['parent_win'] = UtilTool::json2array($parent_win);
        }
        
        $list = UtilDB::getCfgRightList(array('brand','logistics','unit','warehouse'));
        $this->assign('map_brand', json_encode($list['brand']));
        $this->assign('map_unit',  json_encode($list['unit']));

        $this->assign('list_logistics',$list['logistics']);
        $this->assign('warehouse_list',$list['warehouse']);
        $this->assign("params",json_encode($params));
        $this->assign("id_list",$id_list);
        $this->assign("datagrid",$datagrid);
        $this->assign("prefix",$prefix);

        $this->display("show");
    }
    public function showSalesRefund($prefix = '')
    {
        $datagrid_id = $prefix.strtolower(CONTROLLER_NAME . '_' . ACTION_NAME . '_datagrid');
        $id_list = array(
            'tab_container'     => $prefix.'sio_sr_south_container',     //layout  south tab id
            'tool_bar'          => $prefix.'sio_sr_datagrid_toolbar',    //tool_bar id
            'search_form'       => $prefix.'sio_sr_search_form',
            'more_content'      => $prefix.'sio_sr_more_search',
            'hidden_flag'       => $prefix.'sio_sr_hidden_flag',
        	'search_flag'       => $prefix.'sio_sr_search_flag',//标记作为搜索条件,不作为搜索条件不写
        );                    //联动  便签  datagrid id

        $fields = get_field('Trade/RefundManage','refund_manage');
        $datagrid = array(
            'id'        =>$datagrid_id,
            'options'   => array(
                'title'         =>  '',
                'url'           => U("StockInOrder/loadDataSalesRefund"),
                'toolbar'       =>  "#{$id_list['tool_bar']}",
                'fitColumns'    =>  false,
                'singleSelect'  =>  false,
                'ctrlSelect'    =>  true,
            ),
            'fields'    => $fields,
            'class'     => 'easyui-datagrid',
            'style'     => "overflow:scroll",
        );

        $arr_tabs = array(
            array('id'=>$id_list['tab_container'],'url'=>U('Trade/TradeCommon/showTabsView',array('tabs'=>'refund_order')).'?tab=refund_order&prefix=stockinorder','title'=>'系统货品'),
            array('id'=>$id_list['tab_container'],'url'=>U('Trade/TradeCommon/showTabsView',array('tabs'=>'api_order')).'?tab=api_order&prefix=stockinorder','title'=>'平台货品'),
            array('id'=>$id_list['tab_container'],'url'=>U('Trade/TradeCommon/showTabsView',array('tabs'=>'api_trade')).'?tab=api_trade&prefix=stockinorder','title'=>'原始订单'),
            array('id'=>$id_list['tab_container'],'url'=>U('Trade/TradeCommon/showTabsView',array('tabs'=>'sales_trade')).'?tab=sales_trade&prefix=stockinorder','title'=>'系统订单'),
            array('id'=>$id_list['tab_container'],'url'=>U('Trade/TradeCommon/showTabsView',array('tabs'=>'refund_log')).'?tab=refund_log&prefix=stockinorder','title'=>'退款单日志')

        );
        $params = array(
            'datagrid'=>array(
                'url'=>U("StockInOrder/loadDataSalesRefund"),
                'id'=>$datagrid_id
            ),
            'search'=>array(
                'more_content'=>  $id_list['more_content'],
                'hidden_flag'=>  $id_list['hidden_flag'],
                'form_id'=>  $id_list['search_form'],
            ),
            'tabs'=>array(
                'id'=>$id_list['tab_container'],
                'url'=>U('Trade/TradeCommon/updateTabsData'),//用来加载数据的,
            ),
            'form'=>array(
                'id'=>$id_list['search_form'],
            )
        );
        $arr_flag=D('Setting/Flag')->getFlagData(9);
        $params['flag']=array( 'set_flag'=>$id_list['set_flag'], 'json_flag'=>$arr_flag['json'], 'list_flag'=>$arr_flag['list'],'search_flag'=>$id_list['search_flag'] );

        //模板赋值
        $this->assign("arr_tabs", json_encode($arr_tabs));
        $this->assign("id_list", $id_list);
        $this->assign("datagrid", $datagrid);
        $list_form=UtilDB::getCfgRightList(array('shop','logistics'));
		$this->assign("list",$list_form);
        //对象初始胡赋值
        $this->assign("params", json_encode($params));

        //显示模板
        $this->display("sales_refund");
    }


    public function saveOrder()
    {
        try{
            $result = array('status'=>0,'info'=>'成功','data'=>array());
            $params = I("",'',C('JSON_FILTER'));
            $rows = $params['data']['rows'];
            $form = $params['data']['form'];
            if(empty($form['id']))
            {	
                $result['data']['stockin_no'] = D("StockInOrder")->saveOrder($rows,$form,$result);
            }else{
				
                D("StockInOrder")->updateOrder($rows,$form,$result);
            }
        }catch (BusinessLogicException $e){
            $result['status']=1;
            $result['info']=$e->getMessage();
            $this->ajaxReturn(json_encode($result),'EVAL');
        }catch(\Exception $e){
            \Think\Log::write(CONTROLLER_NAME.'-saveOrder-'.$e->getMessage());
            $result['status']=1;
            $result['info']=self::UNKNOWN_ERROR;
            $this->ajaxReturn(json_encode($result),'EVAL');
        }
        try{
            //自动提交
            if(empty($form['id'])){
                $stockin_auto_commit = get_config_value('stockin_auto_commit',0);
                if($stockin_auto_commit == 1){
                    $result['stockin_auto_commit_cfg'] = 1;
                    D("StockInOrder")->checkOrder($result['order_id'],$result);
                }
            }
        }catch (BusinessLogicException $e){
            $msg = $e->getMessage();
            $result['status']=2;
            $result['info']='自动提交失败，失败原因：'.$msg;
        }catch(\Exception $e){
            \Think\Log::write(CONTROLLER_NAME.'-saveOrder-'.$e->getMessage());
            $result['status']=2;
            $result['info']=self::UNKNOWN_ERROR;
        }
        $this->ajaxReturn(json_encode($result),'EVAL');
    }

    public function loadDataSalesRefund($page = 1, $rows = 20, $search = array(), $sort = 'refund_id', $order = 'desc')
    {
        $where_sales_refund_str = " AND (sr_1.process_status=60 OR sr_1.process_status=70) ";
        $this->ajaxReturn(D("Trade/RefundManage")->queryRefund($where_sales_refund_str,$page, $rows, $search, $sort, $order ,$type='manage'));
    }


    //添加退货入库单信息到前台
    public function getSalesRefundInfo($id)
    {
        $this->ajaxReturn(D('Trade/RefundManage')->getSalesRefundInfo($id));
    }
    public function getDetailPosition(){
        $position_info = I('post.','',C('JSON_FILTER'));
        $result = array('status'=>0,'info'=>'成功','data'=>array());
        try{
           $data = D('Stock/StockSpec')->getDefaultPosition($position_info);
            $result['data'] = $data;
        }catch (BusinessLogicException $e){
            $result['status'] =1;
            $result['info'] = $e->getMessage();
        }catch(\Exception $e){
            \Think\Log::write($e->getMessage());
            $result['status'] =1;
            $result['info'] = self::UNKNOWN_ERROR;
        }
        $this->ajaxReturn($result);
    }
    //--------------------柳立新  采购对话框
    public function showPurchase(){  //采购单添加
        try{
            $id_list = array();
            $need_ids = array('form','toolbar','tab_container','hidden_flag','datagrid','more_content','edit','hidden_flag','delete');
            $this->getIDList($id_list,$need_ids,'purchase','');
            $datagrid = array(
                'id'=>$id_list['datagrid'],
                'options'=>array(
                    'url'=>U('StockInOrder/search'),
                    'toolbar'=>$id_list['toolbar'],
                    'fitColumns'=>false,
                    "rownumbers" => true,
                    "pagination" => true,
                    "method"     => "post",
                ),
                'fields'=>get_field('Purchase/PurchaseOrder','purchasemanagement'),
            );
            $params = array(
                'datagrid'=>array(
                    'id'=>$id_list['datagrid'],
                ),
                'search'=>array(
                    'form_id'=> $id_list['form'],
                ),
                'tabs'=>array(
                    'id'=>$id_list['tab_container'],
                    'url'=>U('Purchase/PurchaseCommon/showTabDatagridData'),
                ),
            );
            $arr_tabs = array(
                array('url'=>U('Purchase/PurchaseCommon/showTabsView',array('tabs'=>"purchase_inorder_detail")).'?prefix=purchmanagment&tab=purchase_order_detail&app=Purchase/PurchaseOrder',"id"=>$id_list['tab_container'],"title"=>"采购单详情"),
            );
            $list = UtilDB::getCfgRightList(array('warehouse','employee'));
			$provider = D('Setting/PurchaseProvider')->getALlPurchaseProvider(array('is_disabled'=>0));
			$list['provider'] = $provider['data'];
            $warehouse_default['0'] = array('id' => 'all', 'name' => '全部');
            $warehouse_array = array_merge($warehouse_default, $list['warehouse']);
            $employee_default['0'] = array('id' => 'all','name'=>'全部');
            $employee_array = array_merge($employee_default,$list['employee']);
            $provider_default['0'] = array('id' => 'all','name'=>'全部');
            $provider_array = array_merge($provider_default,$list['provider']);
            $stockin_order_tool = CONTROLLER_NAME;
        }catch(\Exception $e){
            \Think\Log::write($e->getMessage());
        }
        $this->assign('warehouse_array',$warehouse_array);
        $this->assign('employee_array',$employee_array);
        $this->assign('provider_array',$provider_array);
        $this->assign('params',json_encode($params));
        $this->assign('arr_tabs',json_encode($arr_tabs));
        $this->assign('datagrid',$datagrid);
        $this->assign('id_list',$id_list);
        $this->assign('stockin_order_tool',   $stockin_order_tool);
        $this->display('dialog_purchase_order');
    }
    public function search($page = 1,$row = 20,$search = array(),$sort = 'id',$order = 'desc'){//采购单添加
        try{
			$type = 'purchase';
            $result = D('Purchase/PurchaseOrder')->search($page,$row,$search,$sort,$order,$type);
        }catch(\Exception $e){
            \Think\Log::write($e->getMessage());
            $result = array('rows'=>array(),'total'=>1);
        }
        $this->ajaxReturn($result);
    }

	public function getBarcodeInfo(){
		try{
			$result = array('status'=>0,'info'=>'');
			$barcode_info = I('post.','',C('JSON_FILTER'));
			$model = D('Goods/GoodsBarcode');
			 $barcode = trim($barcode_info['barcode']);
			 $model->execute("set @tmp_goods_name='',@tmp_short_name='',@tmp_merchant_no='',@tmp_spec_name='',@tmp_spec_code='',@tmp_goods_id='',@tmp_spec_id='',@tmp_sn_enable=0;");
             $fields = array(
                '(`type`=2) AS is_suite',
                'target_id',
                'FN_GOODS_NO(`type`,target_id) goods_no',
                '@tmp_merchant_no spec_no',
                '@tmp_goods_name goods_name',
                '@tmp_short_name short_name',
                '@tmp_spec_name spec_name',
                '@tmp_spec_code spec_code',
                '@tmp_sn_enable is_sn_enable'
            );
            $res = $model->alias('gb')->field($fields)->fetchSql(false)->where(array('trim(barcode)' => $barcode))->select();
			if($res == '' || empty($res)){
				$result = array('status'=>1,'info'=>'没有该条形码');
			}else{
				$result = array('status'=>0,'info'=>$res);
			}
		}catch(\Exception $e){
            \Think\Log::write($e->getMessage());
            $result = array('status'=>1,'info'=>self::UNKNOWN_ERROR);
        }
		$this->ajaxReturn($result);
	}
	public  function getGoodsInfo(){
		try{
			$is_suite = I('post.is_suite');
			$target_id = I('post.target_id');
			$warehouse_id = I('post.warehouse_id');
			$warehouse_id = empty($warehouse_id)?0:$warehouse_id;
			$point_number=get_config_value('point_number',0);
			$expect_num = "CAST(0 AS DECIMAL(19,".$point_number.")) expect_num";
			$num = "CAST(1 AS DECIMAL(19,".$point_number.")) AS num";
			$where = array("gbd.target_id"=>$target_id);
			$fields = array("gs.spec_no","gs.unit base_unit_id","(gbd.type=2) AS is_suite", "gs.spec_id as id","gs.spec_id","gs.spec_name", "gs.market_price"," gs.spec_code"," gbd.barcode"," gs.lowest_price","gs.retail_price", "gs.weight",
					"gg.short_name","gg.spec_count","gg.goods_no","gg.goods_name", "gg.goods_id","gg.brand_id", 
					"gb.brand_name",
					"cgu.name as unit_name",
					"CAST(1 as DECIMAL(19,4)) AS rebate", $expect_num, $num,"CAST(0 AS DECIMAL(19,4)) AS total_cost","CAST(IFNULL(ss.cost_price,0) AS DECIMAL(19,4)) AS tax_price","CAST(IFNULL(ss.cost_price,0) AS DECIMAL(19,4)) AS tax_amount","CAST(0 AS DECIMAL(19,4)) AS tax","CAST(IFNULL(ss.cost_price,0) AS DECIMAL(19,4)) AS src_price","CAST(IFNULL(ss.cost_price,0) AS DECIMAL(19,4)) AS cost_price",
					"(CASE  WHEN ss.last_position_id THEN ss.last_position_id ELSE -{$warehouse_id} END) AS position_id",
					"(CASE  WHEN ss.last_position_id THEN cwp.position_no  ELSE cwp2.position_no END) AS position_no",
					"IF(ss.last_position_id,1,IF(ss.spec_id IS NOT NULL AND (ss.order_num <> 0 OR ss.sending_num <> 0),1,0)) AS is_allocated");
			
			if((int)$is_suite == 0){
				$data = D('Goods/GoodsBarcode')->alias('gbd')->fetchSql(false)->field($fields)->join("inner join goods_spec gs on gs.spec_id = gbd.target_id and gbd.type = 1")->join("LEFT JOIN goods_goods gg ON(gs.goods_id=gg.goods_id)")->join("LEFT JOIN goods_brand gb ON(gg.brand_id=gb.brand_id)")->join("LEFT JOIN stock_spec ss ON(gs.spec_id=ss.spec_id AND ss.warehouse_id={$warehouse_id})")->join("LEFT JOIN stock_spec_position ssp ON(gs.spec_id=ssp.spec_id AND ssp.warehouse_id={$warehouse_id})")->join("LEFT JOIN cfg_goods_unit cgu ON(gs.unit = cgu.rec_id)")->join("LEFT JOIN cfg_warehouse_position cwp ON(ss.last_position_id = cwp.rec_id)")->join("LEFT JOIN cfg_warehouse_position cwp2 ON(cwp2.rec_id = -{$warehouse_id})")->where($where)->select();
			}elseif((int)$is_suite == 1){
				$data = D('Goods/GoodsBarcode')->alias('gbd')->fetchSql(false)->field($fields)->join("inner join goods_suite gs_1 on gs_1.suite_id = gbd.target_id and gbd.type = 2")->join("left join goods_suite_detail gsd on gsd.suite_id = gs_1.suite_id")->join("left join goods_spec gs on gs.spec_id = gsd.spec_id")->join("LEFT JOIN goods_goods gg ON(gs.goods_id=gg.goods_id)")->join("LEFT JOIN goods_brand gb ON(gg.brand_id=gb.brand_id)")->join("LEFT JOIN stock_spec ss ON(gs.spec_id=ss.spec_id AND ss.warehouse_id={$warehouse_id})")->join("LEFT JOIN stock_spec_position ssp ON(gs.spec_id=ssp.spec_id AND ssp.warehouse_id={$warehouse_id})")->join("LEFT JOIN cfg_goods_unit cgu ON(gs.unit = cgu.rec_id)")->join("LEFT JOIN cfg_warehouse_position cwp ON(ss.last_position_id = cwp.rec_id)")->join("LEFT JOIN cfg_warehouse_position cwp2 ON(cwp2.rec_id = -{$warehouse_id})")->where($where)->select();
			}else{
				$where = array("gs_1.suite_id"=>$target_id);
				$data = D('Goods/GoodsSuite')->alias('gs_1')->fetchSql(false)->field($fields)->join("left join goods_suite_detail gsd on gsd.suite_id = gs_1.suite_id")->join("left join goods_spec gs on gs.spec_id = gsd.spec_id")->join("LEFT JOIN goods_goods gg ON(gs.goods_id=gg.goods_id)")->join("LEFT JOIN goods_brand gb ON(gg.brand_id=gb.brand_id)")->join("LEFT JOIN stock_spec ss ON(gs.spec_id=ss.spec_id AND ss.warehouse_id={$warehouse_id})")->join("LEFT JOIN stock_spec_position ssp ON(gs.spec_id=ssp.spec_id AND ssp.warehouse_id={$warehouse_id})")->join("LEFT JOIN cfg_goods_unit cgu ON(gs.unit = cgu.rec_id)")->join("LEFT JOIN cfg_warehouse_position cwp ON(ss.last_position_id = cwp.rec_id)")->join("LEFT JOIN cfg_warehouse_position cwp2 ON(cwp2.rec_id = -{$warehouse_id})")->join("left join goods_barcode gbd on gbd.target_id = gs.spec_id and gbd.type = 1")->where($where)->select();
			}
			$result = array('status'=>0,'info'=>$data);
		}catch(\Exception $e){
            \Think\Log::write($e->getMessage());
            $result = array('status'=>1,'info'=>self::UNKNOWN_ERROR);
        }
		$this->ajaxReturn($result);
	}
    public function getDialogStockInOrderList() {
        $prefix   = I("get.pre");
        $model   = empty(I("get.model"))?'':I("get.model");
        $field_type = 'stock_in_order_barcode';
        $data_url = U('StockInOrder/getDialogStockInOrderBarcode');
        //$data_url = U("Stock/StockInManagement/loadDataByCondition");
        $prefix   = $prefix ? $prefix : "gb_print";
        $id_list  = [
            "datagrid"      => $prefix . "_stock_in_order_list_datagrid",
            "toolbar"       => $prefix . "_stock_in_order_list_toolbar",
            "form"          => $prefix . "_stock_in_order_list_form",
            "tab_container" => $prefix . "_stock_in_order_list_tab_container",
            "prefix"        => $prefix
        ];
        $datagrid = [
            'id'      => $id_list["datagrid"],
            //"style"   => "height:530px",
           // "class"    =>'',
            'options' => [
                'url'        => $data_url,
                'toolbar'    => $id_list["toolbar"],
                'fitColumns' => false,
                'rownumbers' => true,
                'pagination' => true,
                'method'     => 'post',
            ],
            "fields"  => get_field("StockIn", $field_type)
        ];
        $arr_tabs = [
            [
                'id'    => $id_list["tab_container"],
                'url'   => U("StockInOrder/getStockInOrderDetailList") . "?prefix=" . $prefix,
                'title' => '入库单详情'
//                'url'=>U('StockCommon/showTabsView',array('tabs'=>"stockin_order_detail")).'?prefix=stockinmanagement&tab=stockInManagementDetail&app=StockIn',
//                "title"=>"入库单详情",
            ],
        ];
//        $arr_tabs = array(
//            array('url'=>U('StockCommon/showTabsView',array('tabs'=>"stockin_order_detail")).'?prefix=stockinmanagement&tab=stockInManagementDetail&app=StockIn',"id"=>$idList['id_list']['tab_container'],"title"=>"入库单详情"),
//            array('url'=>U('StockCommon/showTabsView',array('tabs'=>"stockin_order")).'?prefix=stockinmanagement&tab=stockInLog',"id"=>$idList['id_list']['tab_container'],"title"=>"日志"),
//        );
        $params   = [
            "controller" => strtolower(CONTROLLER_NAME),
            "datagrid"   => ["id" => $id_list["datagrid"]],
            "tabs"       => [
                'id'    => $id_list["tab_container"],
                'url'   => U("StockInOrder/getStockInOrderDetailList") . "?prefix=" . $prefix,
//                'url'   => U('StockCommon/showTabDatagridData'),
                'title' => '入库单详情',
            ],
            "search"     => ["form_id" => $id_list["form"]]
        ];
        $list     = UtilDB::getCfgList(array("brand"), array("brand" => array("is_disabled" => 0)));
        $this->assign("goods_brand", $list["brand"]);
        $this->assign("id_list", $id_list);
        $this->assign("model", $model);
        $this->assign('datagrid', $datagrid);
        $this->assign('arr_tabs', json_encode($arr_tabs));
        $this->assign('params', json_encode($params));
        $this->display('stock_in_order_list');
    }
    public function getStockInOrderDetailList() {
        if (IS_POST) {
            $id = I("post.id");
            $this->ajaxReturn(D('StockinOrderDetail','Model')->showData($id));
        } else {
            $prefix   = I("get.prefix");
            $prefix   = $prefix ? $prefix : "gb_print";
            $id_list  = [
                "datagrid" => $prefix . "_tabs_detail_datagrid"
            ];
            $datagrid = [
                "id"      => $id_list["datagrid"],
//                "style"   => "height:230px",
//                 "class"    =>'',
                "options" => [
                    'fitColumns' => false,
                    'rownumbers' => false,
                    'pagination' => false,
                    'method'     => 'post',
                ],
                "fields"  => get_field("Stock/StockIn", "stockindetail")
            ];
            $this->assign("id_list", $id_list);
            $this->assign("datagrid", $datagrid);
            $this->display("tabs_stock_in_order_detail");
        }
    }
    public function getDialogStockInOrderBarcode($page=1, $rows=20, $search = array(), $sort = 'id', $order = 'desc') {
        $search = array(
            'day_start' => date('Y-m-d',strtotime('-1 day')).' 23:59:59',
            'day_end' => date('Y-m-d').' 23:59:59'
        );
        $this->ajaxReturn(D('StockInOrder')->loadDataByCondition($page, $rows, $search, $sort, $order));
    }
    public function getIntelligenceReturnStockIn(){
        $id_list = array();
        $id_list = self::getIDList($id_list,array('datagrid','toolbar','form','form_id','toolbar_bottom','form_bottom_id','add_spec','print_dialog'),'intelligence_return');
        $list = UtilDB::getCfgRightList(array('warehouse'));
        $fields = get_field('Stock/StockIn','intelligence_return_stock_in');
        $checkbox=array('field' => 'ck','checkbox' => true);
        array_unshift($fields,$checkbox);
        $setting = array();
        $operator_id = get_operator_id();
        $where=array('user_id'=>$operator_id,'type'=>10,'code'=>array('like','intelligence_return%'));
        $setting_data = D('Setting/UserData')->field('code,data')->where($where)->select();
        foreach($setting_data as $v){
            $setting[$v['code']] = $v['data'];
        }
        $datagrid = array(
            'id'        =>  $id_list['datagrid'],
            'options'   => array(
                'title'         =>  '',
                'toolbar'       =>  "#{$id_list['toolbar']}",
                'fitColumns'    =>  true,
                'singleSelect'  =>  false,
                'ctrlSelect'    =>  true,
                'pagination'    =>  false,
            ),
            'fields'     =>  $fields,
        );
        $id_list['select'] = array(
            'id'        =>'flag_set_dialog',
            'url' => U('StockInOrder/showGoodsList'),
            'title' => '条码选择货品',
        );
        $id_list['select_order'] = array(
            'id'        =>'flag_set_dialog',
            'url' => U('StockInOrder/showOrderList'),
            'title' => '选择订单',
        );
        $result = D('Setting/PrintTemplate')->field('rec_id as id,title as name,content')->where(array('type'=>array('in',array(5,6,9)),'title'=>array('LIKE','条码打印_%')))->order('is_default desc')->select();
        foreach($result as $key){
            $contents[$key['id']] = $key['content'];
        }
        $this->assign('contents',json_encode($contents));
        $this->assign('goods_template',$result);
        $this->assign('id_list',$id_list);
        $this->assign('list',$list);
        $this->assign('setting',json_encode($setting));
        $this->assign('params',json_encode($id_list));
        $this->assign('datagrid', $datagrid);
        $this->display('intelligence_return_stock_in');
    }
    public function showGoodsList($parent_datagrid_id,$parent_object,$goods_list_dialog){
        $id_list = DatagridExtention::getIdList(array('datagrid','tool_bar'),$parent_object);

        $fields = get_field('Stock/SalesStockoutExamine','goodslist');

        $datagrid = array(
            'id'=>$id_list['datagrid'],
            'options'=> array(
                'title' => '',
                'url'   => '',
                'toolbar' => "#{$id_list['tool_bar']}",
                'fitColumns'   => true,
                'singleSelect'=>true,
                'ctrlSelect'=>false,
                'pagination'=>false,
            ),
            'fields' => $fields,
            'class' => 'easyui-datagrid',
            'style'=>"overflow:scroll",
        );
        $params = array(
            'datagrid'=>array(
                'id' =>$id_list['datagrid']
            ),
            'parent_datagrid'=>array(
                'id'=>$parent_datagrid_id
            ),
            'parent_object'=>$parent_object,
            'goods_list_dialog'=>array('id'=>$goods_list_dialog)
        );
        $this->assign('params',json_encode($params));
        $this->assign("datagrid",$datagrid);
        $this->assign('id_list',$id_list);
        $this->display('barcode_goods_list');
    }
    public function returnStockInSetting(){
        $id_list = array(
            'form'  => 'intelligence_return_setting_stockinorder_form'
        );
        $setting = array();
        $operator_id = get_operator_id();
        $where=array('user_id'=>$operator_id,'type'=>10,'code'=>array('like','intelligence_return%'));
        $setting_data = D('Setting/UserData')->field('code,data')->where($where)->select();
        foreach($setting_data as $v){
            $setting[$v['code']] = $v['data'];
        }
        $this->assign('id_list',$id_list);
        $this->assign('setting',json_encode($setting));
        $this->display('return_stock_in_setting');
    }
    public function updateSetting(){
        $val = I("post.data");
        try {
            $M = M();
            $operator_id = get_operator_id();
            $user_data_model=D('Setting/UserData');
            $M->startTrans();
            $res["status"] = 1;
            $res["info"] = "操作成功！";
            foreach ($val as $k => $v) {
                $where=array('user_id'=>$operator_id,'type'=>10,'code'=>$k);
                $get_cfg=$user_data_model->where($where)->find();
                if($get_cfg){
                    $save['data']=$v;
                    $result = $user_data_model->updateUserData($save,$where);
                }else{
                    $add=array('user_id'=>$operator_id,'type'=>10,'code'=>$k,'data'=>$v);
                    $result = $user_data_model->addUserData($add);
                }
                if ($result === false) {
                    $res["status"] = 0;
                    $res["info"] = "操作失败,请联系管理员！";
                    break;
                }
            }
            $M->commit();
        } catch (\Exception $e) {
            $M->rollback();
            \Think\Log::write($e->getMessage());
            $res["status"] = 0;
            $res["info"] = "操作失败";
        }
        $this->ajaxReturn($res);
    }
    public function getReturnInfo($scan_value,$scan_type){
        try {
            $result = array(
                'status'=>0,
                'refund_info'=>array(),
                'refund_detial_goods_info'=>array() ,
                'msg'=>'成功'
            );
            $refund_info = array();
            $refund_info_conditions = array();
            $refund_detail_goods_info = array();
            if(empty($scan_value))
            {
                SE('原始退款单不存在');
            }
            switch($scan_type)
            {
                case 0 :
                case 1 :
                case 4 :
                    $refund_info_fields = array(
                        'ar.refund_id',
                        'ar.status',
                        'ar.type AS refund_type',
                        'ar.process_status',
                        'ar.logistics_no AS refund_info_logistics',
                        'ar.logistics_name AS refund_info_logistics_name',
                        'ar.reason AS refund_info_reason',
                        'ar.remark AS refund_info_remark',
                        'at.buyer_nick AS refund_info_buyer_nick',
                        'at.receiver_mobile AS refund_info_receiver_mobile',
                        'at.tid',
                    );
                    $refund_info_join = 'LEFT JOIN api_trade at ON ar.tid=at.tid';
                    if($scan_type == 0)
                    {
                        $refund_info_conditions_key = 'ar.logistics_no';
                    }
                    if($scan_type == 1)
                    {
                        $refund_info_conditions_key = 'st.logistics_no';
                        $refund_info_join .= ' LEFT JOIN sales_trade_order sto ON sto.src_tid=ar.tid AND sto.shop_id=ar.shop_id LEFT JOIN sales_trade st ON st.trade_id=sto.trade_id';
                    }
                    if($scan_type == 2)
                    {
                        $refund_info_conditions_key = 'at.receiver_mobile';
                    }
                    if($scan_type == 4)
                    {
                        $refund_info_conditions_key = 'ar.tid';
                    }
//                    $refund_info_conditions = array(
//                        $refund_info_conditions_key   =>$scan_value,
//                        //'ar.status'         =>4,
//                        //'ar.type'           =>array('in','2,3')
//                    );
                    $refund_info_conditions[$refund_info_conditions_key] = $scan_value;
                    if($scan_type == 2)
                    {
                        $refund_info = D('Trade/OriginalRefund')->field($refund_info_fields)->where($refund_info_conditions)->alias('ar')->join($refund_info_join)->select();
                        $this->ajaxReturn($result);
                    }
                    else
                    {
                        $refund_info = D('Trade/OriginalRefund')->getApiRefund($refund_info_fields,$refund_info_conditions,'ar',$refund_info_join);
                    }
                    if(empty($refund_info))
                    {
                        $result['solve_way'] = "原始退款单不存在！";
                        SE('原始退款单不存在');
                    }
                    $suite_no = D('Trade/Trade')->alias('st')->field('suite_no')->join('left join sales_trade_order sto on sto.trade_id = st.trade_id')->where(array('st.src_tids'=>array('like',$refund_info['tid'])))->group('suite_no')->select();
                    if(!empty($suite_no))
                    {
                        $result['suite_no'] = $suite_no;
                    }
                    else
                    {
                        $result['suite_no'] = "";
                    }
                    if ((int)$refund_info['refund_type']<>2&&(int)$refund_info['refund_type']<>3)
                    {
                        $result['solve_way'] = "只支持退货和换货的退款单进行智能退换入库！";
                        SE('只支持退货和换货的退款单进行智能退换入库');
                    }
                    if((int)$refund_info['status']<>4)
                    {
                        $result['solve_way'] = '原始退款单平台状态不正确！';
                        SE('原始退款单平台状态不正确');
                    }
                    if((int)$refund_info['process_status']==10||(int)$refund_info['process_status']==80)
                    {
                        $result['solve_way'] = '原始退款单已取消或者已完成！';
                        SE('原始退款单已取消或者已完成');
                    }
                    //$refund_detail_goods_info = D('Trade/OriginalRefund')->getApiRefundGoods($refund_info['refund_id']);
//                    $refund_detail_goods_info=D('Trade/SalesTradeOrder')->getSalesTradeOrderList(
//                        'sto.platform_id, sto.src_oid AS oid, sto.src_tid AS tid, sto.trade_id, sto.rec_id AS trade_order_id, st.trade_no,
//							sto.actual_num AS order_num, sto.share_price, sto.price AS original_price, sto.discount, sto.paid, aro.num AS expect_num,
//							sto.goods_id, sto.goods_name, sto.goods_no, sto.spec_id, sto.spec_name, sto.spec_no, sto.suite_id, sto.suite_name,
//							sto.suite_num, sto.order_price*aro.num AS total_amount,sto.rec_id,gbc.barcode,
//							st.receiver_province AS swap_province, st.receiver_city AS swap_city, st.receiver_district AS swap_district,cs.shop_name,"0.0000" AS stockin_num ',
//                        array('ar.refund_id'=>array('eq',$refund_info['refund_id'])),
//                        'sto',
//                        'LEFT JOIN api_refund ar ON ar.tid=sto.src_tid
//                         LEFT JOIN sales_trade st ON st.trade_id=sto.trade_id
//                         INNER JOIN api_refund_order aro ON aro.shop_id=ar.shop_id AND aro.refund_no=ar.refund_no AND aro.spec_id=sto.spec_id
//                         LEFT JOIN cfg_shop cs ON cs.shop_id=aro.shop_id
//                         LEFT JOIN goods_barcode gbc ON gbc.target_id=sto.spec_id AND gbc.is_master = 1');
                    $refund_detail_sql="SELECT sto.platform_id, sto.src_oid AS oid, sto.src_tid AS tid, sto.trade_id, sto.rec_id AS trade_order_id, st.trade_no,
							sto.actual_num AS order_num, sto.share_price, sto.price AS original_price, sto.discount, sto.paid, aro.num AS expect_num,
							sto.goods_id, sto.goods_name, sto.goods_no, sto.spec_id, sto.spec_name, sto.spec_no, sto.suite_id, sto.suite_name,
							sto.suite_num, sto.order_price*aro.num AS total_amount,sto.rec_id,gbc.barcode,
							st.receiver_province AS swap_province, st.receiver_city AS swap_city, st.receiver_district AS swap_district,cs.shop_name,\"0.0000\" AS stockin_num
                        FROM api_refund_order aro
                        LEFT JOIN api_refund ar ON aro.shop_id=ar.shop_id AND aro.refund_no=ar.refund_no
                        LEFT JOIN api_trade_order ato ON ato.oid=aro.oid AND ato.shop_id=aro.shop_id
                        LEFT JOIN api_goods_spec ags ON ags.spec_id=ato.spec_id AND ags.goods_id=ato.goods_id
                        LEFT JOIN cfg_shop cs ON cs.shop_id=aro.shop_id
                        LEFT JOIN goods_spec gs ON gs.spec_id = ags.match_target_id AND gs.deleted=0
			            LEFT JOIN goods_goods gg ON gg.goods_id=gs.goods_id
                        LEFT JOIN goods_barcode gbc ON gbc.target_id=aro.spec_id AND gbc.is_master = 1
                        LEFT JOIN sales_trade_order sto ON sto.src_oid=aro.oid AND sto.shop_id=aro.shop_id
                        LEFT JOIN sales_trade st ON st.trade_id=sto.trade_id
                        WHERE ar.refund_id=".$refund_info['refund_id'];
                    $refund_detail_goods_info=M()->query($refund_detail_sql);
                    if(empty($refund_detail_goods_info[0]['rec_id']))
                    {
                        SE('未找到该退款单对应的系统订单');
                    }
                    $refund_detail_goods_info=array('total'=>count($refund_detail_goods_info),'rows'=>$refund_detail_goods_info);
                    $result['refund_info']=$refund_info;
                    $result['refund_detail_goods_info'] = $refund_detail_goods_info;
                    break;
                case 5 :
                    $goods_info = D('Goods/GoodsBarcode')->getGoodsByBarcode($scan_value);
                    if(empty($goods_info))
                    {
                        SE('没有查询到有关条码的货品信息');
                    }
                    $result['match_goods_list']=array(
                        'total'=>0,
                        'rows'=>$goods_info
                    );
                    break;
            }
        }catch (BusinessLogicException $e) {
            $result["status"] = 2;
            //$result["stock"] = $refund_info;
            $result["msg"] = $e->getMessage();
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            \Think\Log::write(__CONTROLLER__.'-getReturnInfo-'.$msg);
            $result['status'] = 1;
            $result['msg']='未知错误,请联系管理员';
        }
        $this->ajaxReturn($result);
    }
    public function submitReturnStockIn(){
        try{
            $result = array(
                'status'=>0,
                'msg'=>'退货入库成功'
            );
            $arr_form_data=I('post.info','',C('JSON_FILTER'));
            $arr_refund_order=I('post.refund_order','',C('JSON_FILTER'));
            $arr_return_order=I('post.refund_order','',C('JSON_FILTER'));
            $id=I('post.id','',C('JSON_FILTER'));
            $is_api=I('post.is_api','',C('JSON_FILTER'));
            $result = D('StockInOrder')->submitReturnStockIn($arr_form_data,$arr_refund_order,$arr_return_order,$id,$is_api);
            /*$user_id=get_operator_id();
            $refund_data=D('Trade/OriginalRefund')->getApiRefund(
                "ar.refund_no AS src_no, ar.platform_id, ar.shop_id, ar.type, ar.pay_account, ar.pay_no, ar.refund_amount , ar.actual_refund_amount AS goods_amount, ar.remark ,
							 ar.logistics_name, ar.logistics_no, ar.buyer_nick, ar.tid, st.trade_no, ar.actual_refund_amount AS guarante_refund_amount, st.trade_id   ",
                array('refund_id'=>array('eq',$arr_form_data['refund_id'])),
                "ar",
                "LEFT JOIN sales_trade_order sto ON sto.src_tid=ar.tid
							 LEFT JOIN sales_trade st ON st.trade_id=sto.trade_id");
//            $refund_data['refund']['refund_id']=0;
//            $refund_data['refund']['swap_province']=0;
//            $refund_data['refund']['swap_city']=0;
//            $refund_data['refund']['swap_district']=0;
            $refund_data['logistics_no'] = $arr_form_data['refund_info_logistics'];
            $refund_data['logistics_name'] = $arr_form_data['refund_info_logistics_name'];
            $refund_data['buyer_nick'] = $arr_form_data['refund_info_buyer_nick'];
            $refund_data['remark'] = $arr_form_data['refund_info_remark'];
            $refund_data['goods_refund_count'] = $arr_form_data['goods_refund_count'];
            $refund_data['warehouse_id'] = $arr_form_data['warehouse_id'];
            $refund_data['refund_no'] = '';
            $refund_data['reason_id'] = '0';
            $refund_data['direct_refund_amount'] = '0.0000';
            $refund_data['pay_method'] = '1';
            $refund_data['stockin_pre_no'] = '';
            $refund_data['flag_id'] = '0';
            $refund_data['post_amount'] = '0.0000';
            unset($refund_data['platform_id']);unset($refund_data['shop_id']);unset($refund_data['pay_no']);
            //D('Trade/RefundManage')->validateRefund($refund_data);
            D('Trade/RefundManage')->addRefundNoTrans($refund_data,$arr_refund_order,$arr_return_order,$user_id,$is_api);
//            $res_cfg_value=get_config_value('refund_auto_agree');
//            if($res_cfg_value!=1){
//                $agree_result=$this->agreeRefund($refund_id,$user_id);
//                if ($agree_result['status']==0) {
//                    $agree_result['status']=1;
//                }elseif($agree_result['status']==1){
//                    $agree_result['status']=0;
//                }
//                return $agree_result;
//            }
            */
        }catch (BusinessLogicException $e){
            $this->error($e->getMessage());
        }catch (\Exception $e) {
            $msg = $e->getMessage();
            \Think\Log::write(__CONTROLLER__.'-submitReturnStockIn-'.$msg);
            $result['status'] = 1;
            $result['msg']='未知错误,请联系管理员';
        }
        $this->ajaxReturn($result);
    }
    public function exchangeRefund($id,$is_api=0,$refund_id='')
    {
        $id=intval($id);
        $refund_id=intval($refund_id);
        $user_id=get_operator_id();

        if(IS_POST){
            $arr_form_data=I('post.info','',C('JSON_FILTER'));
            $arr_refund_order=I('post.refund_order','',C('JSON_FILTER'));
            $arr_return_order=I('post.return_order','',C('JSON_FILTER'));
            $is_api=I('post.is_api','',C('JSON_FILTER'));
            if($arr_form_data['type']==3||$arr_form_data['type']==5)
            {
                $arr_return_data=I('post.return_info','',C('JSON_FILTER'));
                $arr_form_data['shop_id']=$arr_return_data['shop_id'];
                $arr_form_data['swap_warehouse_id']=$arr_return_data['swap_warehouse_id'];
                $arr_form_data['goods_return_count']=$arr_return_data['goods_return_count'];
                unset($arr_return_data);
            }
            $form_data_list = D('Trade/OriginalRefund')->getApiRefund(
                "ar.refund_no AS src_no, ar.platform_id, ar.shop_id, ar.type, ar.pay_account, ar.pay_no, ar.refund_amount , ar.actual_refund_amount AS goods_amount, ar.remark ,
							 ar.logistics_name, ar.logistics_no, ar.buyer_nick, ar.tid, st.trade_no, ar.actual_refund_amount AS guarante_refund_amount, st.trade_id   ",
                array('refund_id'=>array('eq',$refund_id)),
                "ar",
                "LEFT JOIN sales_trade_order sto ON sto.src_tid=ar.tid
                LEFT JOIN sales_trade st ON st.trade_id=sto.trade_id");
            $arr_form_data['tid'] = $form_data_list['tid'];
            $arr_form_data['trade_no'] = $form_data_list['trade_no'];
            $arr_form_data['goods_amount'] = $form_data_list['goods_amount'];
            $arr_form_data['buyer_nick'] = $form_data_list['buyer_nick'];
            $arr_form_data['type'] = $form_data_list['type'];
            $arr_form_data['src_no'] = $form_data_list['src_no'];
            $arr_form_data['refund_amount'] = $form_data_list['refund_amount'];
            $arr_form_data['pay_account'] = $form_data_list['pay_account'];
            $arr_form_data['pay_account'] = $form_data_list['pay_account'];
            $arr_form_data['guarante_refund_amount'] = $form_data_list['guarante_refund_amount'];
            $arr_form_data['remark'] = $form_data_list['remark'];
            $arr_form_data['logistics_no'] = $form_data_list['logistics_no'];
            $arr_form_data['swap_warehouse_id'] = $arr_form_data['warehouse_id'];
            $arr_form_data['direct_refund_amount'] = '0.0000';
            $arr_form_data['pay_method'] = '1';
            $arr_form_data['stockin_pre_no'] = '';
            $arr_form_data['post_amount'] = '0.0000';
            $arr_form_data['flow_type'] = 1;
            $arr_form_data['flag_id'] = 0;
            $arr_form_data['reason_id'] = 0;
            $arr_form_data['refund_no'] = '';

            if($is_api!=2&&$id>0)
            {
                $arr_form_data['refund_id']=$id;
            }
            $refund_db=D('Trade/RefundManage');
            try {
                //$refund_db->validateRefund($arr_form_data);
                /*if(!$refund_db->validate($refund_db->getRules())->create($arr_form_data))
                {
                    $this->error($refund_db->getError());
                }*/
                //查看修改号码权限
                $cfg_show_telno=get_config_value('show_number_to_star',1);
                if(($arr_form_data['type']==3||$arr_form_data['type']==5) && !UtilDB::checkNumber(array($id), 'sales_refund', $user_id,null,2) && $cfg_show_telno==1){
                    unset($arr_form_data['swap_mobile']);
                    unset($arr_form_data['swap_telno']);
                }
                $result = D('StockInOrder')->submitReturnStockIn($arr_form_data,$arr_refund_order,$arr_return_order,$id,$is_api);

//                if($id>0){//编辑
//                    $refund_db->updateRefund($arr_form_data,$arr_refund_order,$arr_return_order,$user_id);
//                }else{//新增
//                    $add_result=$refund_db->addRefund($arr_form_data,$arr_refund_order,$arr_return_order,$user_id,$is_api);
//                    $res_cfg_value=get_config_value('refund_auto_agree');
//                    if($arr_form_data['type']!=5&&$res_cfg_value==1){//开启了自动同意配置
//                        $this->ajaxReturn($add_result);
//                    }
//                }
            }catch (BusinessLogicException $e){
                $this->error($e->getMessage());
            }catch (\Exception $e) {
                $this->error($e->getMessage());
            }
            $this->ajaxReturn($result);
            //$this->success();
        }else{
            $refund_db=D('Trade/RefundManage');
            try {
                if ($is_api==1) {
                    $prefix='intelligence_exchange';$dialog_id='intelligence_exchange_add';
                }else{
                    $prefix='refund';$dialog_id='sales_refund_add';
                }
                $id_list=array(
                    'id_datagrid_refund'=>$prefix.'_editrefund_datagrid_refund',
                    'id_datagrid_return'=>$prefix.'_editrefund_datagrid_return',
                    'toolbar_refund'=>$prefix.'_toolbar_refund',
                    'toolbar_return'=>$prefix.'_toolbar_return',
                    'tab_container'=>$prefix.'_tab_container',
                    'form_id'=>$prefix.'_form',
                    'more_content_logistics'=>$prefix.'_more_content_logistics',
                    'more_content_info'=>$prefix.'_more_content_info',
                    'dialog_id'=>$dialog_id,
                    'return_form'=>$prefix.'_return_form',
                    'province'=>$prefix.'_refund_province',
                    'city'=>$prefix.'_refund_city',
                    'district'=>$prefix.'_refund_district',
                    'url_exchange'=>U('RefundManage/exchangeRefundGoods'),
                );
                $add_sp_order_dialog = array('title'=>'其他入库单查询','id'=>'flag_set_dialog','url'=>U('Stock/StockInManagement/refundLinkSpOrder'),'height'=>'560','width'=>'1100');
                $list_form=UtilDB::getCfgRightList(array('shop','logistics','warehouse','reason'),array('warehouse'=>array('is_disabled'=>array('eq',0)),'reason'=>array('class_id'=>array('eq',4),'is_disabled'=>array('eq',0))));
                $list_form['flags']=D('Setting/Flag')->getFlagData(9,'list');
                //$datagrid=$refund_db->getDialogView('refund_edit',$id_list);
                $datagrid['return_order']=array(
                    'title'=>'换出货品',
                    'id'=>$id_list['id_datagrid_return'],
                    'style'=>'',
                    'class'=>'easyui-datagrid',
                    'options'=> array(
                        'title'=>'',
                        'toolbar' => "#{$id_list['toolbar_return']}",
                        'pagination'=>false,
                        'fitColumns'=>true,
                        'fit'=>true,
                        //'methods'=>'onEndEdit:endEditReturnOrder,onBeginEdit:beginEditReturnOrder',
                    ),
                    'fields' =>get_field('Trade/RefundManage','return_order'),
                );
                $refund_data=array();
                //查看号码权限---用于判断号码可否编辑
                $cfg_show_telno=get_config_value('show_number_to_star',1);
                $id_list['right_flag'] = 1;
                if($cfg_show_telno==1){
                    $right_flag=UtilDB::checkNumber(array($id), 'sales_refund', $user_id);
                    $id_list['right_flag'] = $right_flag==false?0:1;
                }
                if($id>0&&$is_api==1){
                    $refund_data['refund']=D('Trade/OriginalRefund')->getApiRefund(
                        "ar.refund_no AS src_no, ar.platform_id, ar.shop_id, ar.type, ar.pay_account, ar.pay_no, ar.refund_amount , ar.actual_refund_amount AS goods_amount, ar.remark ,
							 ar.logistics_name, ar.logistics_no, ar.buyer_nick, ar.tid, st.trade_no, ar.actual_refund_amount AS guarante_refund_amount, st.trade_id   ",
                        array('refund_id'=>array('eq',$id)),
                        "ar",
                        "LEFT JOIN sales_trade_order sto ON sto.src_tid=ar.tid
							 LEFT JOIN sales_trade st ON st.trade_id=sto.trade_id");
                    $refund_data['refund']['refund_id']=0;
                    $refund_data['refund']['swap_province']=0;
                    $refund_data['refund']['swap_city']=0;
                    $refund_data['refund']['swap_district']=0;
                    $res_refund_orders=D('Trade/SalesTradeOrder')->getSalesTradeOrderList(
                        'sto.platform_id, sto.src_oid AS oid, sto.src_tid AS tid, sto.trade_id, sto.rec_id AS trade_order_id, st.trade_no,
							sto.actual_num AS order_num, sto.share_price, sto.price AS original_price, sto.discount, sto.paid, aro.num AS refund_num,
							sto.goods_id, sto.goods_name, sto.goods_no, sto.spec_id, sto.spec_name, sto.spec_no, sto.suite_id, sto.suite_name,
							sto.suite_num, sto.order_price*aro.num AS total_amount,sto.rec_id,
							st.receiver_province AS swap_province, st.receiver_city AS swap_city, st.receiver_district AS swap_district ',
                        array('ar.refund_id'=>array('eq',$id)),
                        'sto',
                        'LEFT JOIN api_refund ar ON ar.tid=sto.src_tid
							 LEFT JOIN sales_trade st ON st.trade_id=sto.trade_id
							 LEFT JOIN api_refund_order aro ON aro.shop_id=ar.shop_id AND aro.refund_no=ar.refund_no');
                    $refund_data['refund_data']=array('total'=>count($res_refund_orders),'rows'=>$res_refund_orders);
                }else{//新建
                    $refund_data['refund']=array('refund_id'=>0,'type'=>'3');
                }
                $id_list['refund_id']=$refund_data['refund']['refund_id'];
                $this->assign('list',$list_form);
                $this->assign('add_sp_order_dialog',$add_sp_order_dialog);
                $this->assign('id_list',$id_list);
                $this->assign('datagrid', $datagrid);
                $this->assign('is_api',$is_api);
                $this->assign('refund_data', json_encode($refund_data));
            }catch (BusinessLogicException $e){
                $this->assign('message',$e->getMessage());
                $this->display('Common@Exception:dialog');
                exit();
            }catch (\Exception $e){
                $this->assign('message',$e->getMessage());
                $this->display('Common@Exception:dialog');
                exit();
            }
            $this->display('dialog_intelligence_exchange_refund_edit');
        }
    }
    public function showOrderList($parent_datagrid_id,$parent_object,$order_list_dialog,$scan_value,$scan_type){
        $id_list = DatagridExtention::getIdList(array('datagrid','tool_bar'),$parent_object);
        $url = U("StockInOrder/orderListLoadData") . '?scan_value=' . $scan_value . '&scan_type=' . $scan_type;
        $fields = get_field('Stock/StockIn','intelligence_return_order_list');
        $datagrid = array(
            'id'=>$id_list['datagrid'],
            'options'=> array(
                'title' => '',
                'url'   => $url,
//                'url'   => '',
                'toolbar' => "#{$id_list['tool_bar']}",
                'fitColumns'   => false,
                'singleSelect'=>true,
                'ctrlSelect'=>false,
                'pagination'=>true,
            ),
            'fields' => $fields,
            'class' => 'easyui-datagrid',
            //'style'=>"overflow:scroll",
        );
        $params = array(
            'datagrid'=>array(
                'id' =>$id_list['datagrid']
            ),
            'parent_datagrid'=>array(
                'id'=>$parent_datagrid_id
            ),
            'parent_object'=>$parent_object,
            'order_list_dialog'=>array('id'=>$order_list_dialog)
        );
        $this->assign('params',json_encode($params));
        $this->assign("datagrid",$datagrid);
        $this->assign('id_list',$id_list);
        $this->display('intelligence_return_order_list');
    }
    public function orderListLoadData($page = 1, $rows = 20, $search = array(), $sort = 'trade_id', $order = 'desc',$scan_value, $scan_type){
        $where_sales_trade = '';
        if($scan_type == 2)
        {
            $search['receiver_mobile'] = $scan_value;
        }
        if($scan_type == 3)
        {
            $search['buyer_nick'] = $scan_value;
        }
        $search['trade_from'] = '1,2,3';
        $search['trade_status'] = '110';
        $data=D('Trade/Trade')->queryTrade($where_sales_trade,$page,$rows,$search,$sort,$order,'manage');
        $this->ajaxReturn($data);
    }
}
