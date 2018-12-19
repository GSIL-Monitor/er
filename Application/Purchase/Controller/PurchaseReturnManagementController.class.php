<?php
namespace Purchase\Controller;
use Common\Controller\BaseController;
use Think\Exception\BusinessLogicException;
use Common\Common\UtilDB;
//use Common\Common\UtilTool;
//use Common\Common\ExcelTool;
//use Thinl\Model;
//use Stock\StockCommonField;
//use Stock\StockPurchaseManagementField;
use Platform\Common\ManagerFactory;

class PurchaseReturnManagementController extends BaseController{

    public function show(){

        try{
            $id_list = array();
            $need_ids = array('form','toolbar','tab_container','hidden_flag','datagrid','more_content','edit','hidden_flag','delete','file_form','file_dialog','print_dialog');
            $this->getIDList($id_list,$need_ids,'','');
            $datagrid = array(
                'id'=>$id_list['datagrid'],
                'options'=>array(
                    'url'=>U('PurchaseReturnManagement/search'),
                    'toolbar'=>$id_list['toolbar'],
                    'fitColumns'=>false,
                    "rownumbers" => true,
                    "pagination" => true,
                    "method"     => "post",
                ),
                'fields'=>get_field('PurchaseReturn','purchase_reurn_management'),
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
                'edit'=>array('id'=>$id_list['edit'],'url'=>U('Purchase/PurchaseReturnManagement/edit'),'title'=>'采购退货开单编辑'),
            );
            $arr_tabs = array(
                array('url'=>U('Purchase/PurchaseCommon/showTabsView',array('tabs'=>"purchase_return_detail")).'?prefix=purchasereturnmanagment&tab=purchase_return_detail&app=Purchase/PurchaseReturn',"id"=>$id_list['tab_container'],"title"=>"退货单详情"),
                array('url'=>U('Purchase/PurchaseCommon/showTabsView',array('tabs'=>"purchase_return_log")).'?prefix=purchasereturnmanagment&tab=purchase_return_log&app=Purchase/PurchaseReturn',"id"=>$id_list['tab_container'],"title"=>"日志"),
            );
            $list = UtilDB::getCfgRightList(array('warehouse','employee','provider'));
//            $provider = D('Setting/PurchaseProvider')->getALlPurchaseProvider();
//            $list['provider'] = $provider['data'];
            $warehouse_default['0'] = array('id' => 'all', 'name' => '全部');
            $warehouse_array = array_merge($warehouse_default, $list['warehouse']);
            $employee_default['0'] = array('id' => 'all','name'=>'全部');
            $employee_array = array_merge($employee_default,$list['employee']);
            $provider_default['0'] = array('id' => 'all','name'=>'全部');
            $provider_array = array_merge($provider_default,$list['provider']);
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
        $this->display('show');

    }
    public function search($page = 1,$row = 20,$search = array(),$sort = 'id',$order = 'desc'){
        try{
            $result = D('PurchaseReturn')->search($page,$row,$search,$sort,$order);
        }catch(\Exception $e){
            \Think\Log::write($e->getMessage());
            $result = array('rows'=>array(),'total'=>1);
        }
        $this->ajaxReturn($result);
    }
    public function edit(){
        $edit_info = I('','',C('JSON_FILTER'));
        $id = $edit_info['id'];
        $management = $edit_info['management_info'];
        D('Purchase/PurchaseReturn','Controller')->edit($id,$management);
    }
    public function cancelPurchaseReturn($id){
        try{
            $data = array('status'=>0,'info'=>'取消成功');
            $data = D('PurchaseReturn')->cancelPurchaseReturn($id);
        }catch(\Exception $e){
            \Think\Log::write($e->getMessage());
            $data = array('status'=>1,'info'=>$e->getMessage());
        }
        $this->ajaxReturn($data);
    }
    public function submitPurchaseReturn($id){
        try{
            $data = array('status'=>0,'info'=>'审核成功');
            $data = D('PurchaseReturn')->submitPurchaseReturn($id);
        }catch(\Exception $e){
            \Think\Log::write($e->getMessage());
            $data = array('status'=>1,'info'=>$e->getMessage());
        }
        $this->ajaxReturn($data);
    }
    public function revertCheck($id){
        try{
            if(empty($id)){$this->ajaxReturn(array('status'=>1,'info'=>'没有获取到采购单信息，请刷新后重试！'));}
            $data = array('status'=>0,'info'=>'驳回成功');
            $data = D('PurchaseReturn')->revertCheck($id);
        }catch(\Exception $e){
            \Think\Log::write($e->getMessage());
            $data = array('status'=>1,'info'=>$e->getMessage());
        }
        $this->ajaxReturn($data);
    }
	public function send($ids)
    {
        $sid = get_sid();
		$uid = get_operator_id();
		$consign_info = I('post.ids','',C('JSON_FILTER'));
        $result = array(
            'status'=>0,
            'info'=>'success',
            'data'=>array()
        );
        $fail = array();
        $success = array();
        if(empty($consign_info))
        {
            $result['info'] ="请选择采购单";
            $result['status'] = 1;
            $this->ajaxReturn($result);
        }
		try{
			foreach ($consign_info as $key=>$id){   
				$purchase_info_fields = array("po.status","cw.type");
				$purchase_info_cond = array(
					'po.return_id' =>$id,
				);
				//根据出库单id获取出库单信息
				$res_so_info = D('Stock/PurchaseReturn')->alias('po')->field($purchase_info_fields)->join('left join cfg_warehouse cw on cw.warehouse_id = po.warehouse_id ')->where($purchase_info_cond)->find();
				//判断是否查询成功
				if (empty($res_so_info))
				{
					SE('查询失败');
				}
				
				if ((int)$res_so_info['status']!= 42 && (int)$res_so_info['status']!= 44)
				{
					SE("采购状态不正确");
				}
				if((int)$res_so_info['type']!=11 && (int)$res_so_info['type']!=15)
				{
					SE("仓库类型不正确");
				}
					
				$WmsManager = ManagerFactory::getManager("Wms");
				$WmsManager->manual_wms_adapter_add_po_refund($sid, $uid, $id);
			}
		} catch (BusinessLogicException $e){
            \Think\Log::write($e->getMessage());
			echo json_encode(array('error'=>$e->getMessage()));
        }catch (\Exception $e) {
            \Think\Log::write($e->getMessage());
			echo json_encode(array('error'=>parent::UNKNOWN_ERROR));
        }
     //   $this->ajaxReturn($result);
        
    }
	
	public function cancel_po($ids)
    {
        $sid = get_sid();
		$uid = get_operator_id();
		$consign_info = I('post.ids','',C('JSON_FILTER'));
        $result = array(
            'status'=>0,
            'info'=>'success',
            'data'=>array()
        );
        $fail = array();
        $success = array();
        if(empty($consign_info))
        {
            $result['info'] ="请选择采购单";
            $result['status'] = 1;
            $this->ajaxReturn($result);
        }
		try{
			foreach ($consign_info as $key=>$id){   
				$purchase_info_fields = array("po.status","cw.type");
				$purchase_info_cond = array(
					'po.return_id' =>$id,
				);
				//根据出库单id获取出库单信息
				$res_so_info = D('Stock/PurchaseReturn')->alias('po')->field($purchase_info_fields)->join('left join cfg_warehouse cw on cw.warehouse_id = po.warehouse_id ')->where($purchase_info_cond)->find();
				//判断是否查询成功
				if (empty($res_so_info))
				{
					SE('查询失败');
				}
				
				if ((int)$res_so_info['status']!= 46 && (int)$res_so_info['status']!= 42)
				{
					SE("采购状态不正确");
				}
				if((int)$res_so_info['type']!=11 && (int)$res_so_info['type']!=15)
				{
					SE("仓库类型不正确");
				}
				if((int)$res_so_info['status'] == 42){
					D('Purchase/PurchaseReturn')->cancel_po($id);
				}else{
					$WmsManager = ManagerFactory::getManager("Wms");
					$WmsManager->manual_wms_adapter_cancel_po_return($sid, $uid, $id);
				}
			}
		} catch (BusinessLogicException $e){
			echo json_encode(array('error'=>$e->getMessage()));
        }catch (\Exception $e) {
            \Think\Log::write($e->getMessage());
			echo json_encode(array('error'=>parent::UNKNOWN_ERROR));
        }
     //   $this->ajaxReturn($result);
        
    }
    public function printPurchase(){
        try{
            $ids = I('get.ids');
            $fields = 'rec_id as id,title as name,content';
            $model = D('Setting/PrintTemplate');
            $dialog_div = 'purchase_return_print_dialog';
            $result = $model->field($fields)->where(array('type'=>array('in',array(5,6,9)),'title'=>array('LIKE','采购退货单_%')))->order('is_default desc')->select();
            foreach($result as $key){
                $contents[$key['id']] = $key['content'];
            }
            $ids_arr = explode(',',$ids);
            foreach($ids_arr as $id){
                $purchase_goods = D('Purchase/PurchaseReturn')->getPurchaseReturnDetailPrintData($id);
                foreach($purchase_goods as $v){
                    if(!isset($no[$v['id']]))
                        $no[$v['id']] = 0;
                    if($v['discount'] == 0){
                        $v['ori_price'] = '0.0000';
                    }else{
                        $v['ori_price'] = number_format($v['cost_price']/$v['discount'],4);
                    }
                    $v['no'] = ++$no[$v['id']];
                    $v['discount'] =  sprintf("%01.2f", $v['discount']*100).'%';
                    $purchase_detail[$v['id']][] = $v;
                }
            }
            $list = UtilDB::getCfgRightList(array('warehouse'));
            $this->assign('warehouse_list', $list['warehouse']);
            $this->assign('contents',json_encode($contents));
            $this->assign('purchase_detail',json_encode($purchase_detail));
            $this->assign('dialog_div',$dialog_div);
            $this->assign('goods_template',$result);
        }catch(\PDOException $e){
            \Think\Log::write($e->getMessage());
        }

        $this->display('print_purchase_return');
    }
    public function printPurchaseReturnLog($ids){
        try{
            if(empty($ids)){return;}
            $operator_id = get_operator_id();
            $ids = explode(',',$ids);
            for($i = 0; $i < count($ids); $i++) {
                $log_data[] = array(
                    'return_id' => $ids[$i],
                    'operator_id' => $operator_id,
                    'type' => 20,
                    'remark' => '打印采购退货单'
                );
            }
            D('Purchase/PurchaseReturnLog')->addAll($log_data);
        }catch(\Exception $e){
            \Think\Log::write($e->getMessage());
        }
    }
}