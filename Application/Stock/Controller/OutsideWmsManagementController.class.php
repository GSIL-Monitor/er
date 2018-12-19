<?php
namespace Stock\Controller;

use Common\Controller\BaseController;
use Think\Exception\BusinessLogicException;
use Common\Common\UtilDB;
use Platform\Common\ManagerFactory;

class OutsideWmsManagementController extends BaseController{
	
	public function showWmsManage(){
		$id_list = array();
		$needs = array('datagrid','form','toolbar','edit','tab_container');
		$this->getIDList($id_list,$needs,'','');
		$datagrid = array(
			'id' =>$id_list['datagrid'],
			'options'=>array(
				'url'=>U('Stock/OutsideWmsManagement/search'),
				'toolbar'=>$id_list['toolbar'],
				'fitColumns'=>false,
				'rownumbers'=>true,
				'pagination'=>true,
				'method'=>'post',
			),
			'fields'=>get_field('OutsideWmsOrder','outsidewmsmanage'),
		);
		$params = array(
			'datagrid'=>array('id'=>$id_list['datagrid']),
			'search'=>array('form_id'=>$id_list['form']),
			'tabs'=>array('id'=>$id_list['tab_container'],'url'=>U('Stock/StockCommon/showTabDatagridData')),
			'edit'=>array('id'=>$id_list['edit'],'url'=>U('OutsideWmsManagement/edit'),'title'=>'委外出入库编辑'),
			
			
		);
		$arr_tab = array(
			array('url'=>U('Stock/StockCommon/showTabsView',array('tabs'=>'Outside_wms_dateil')).'?prefix=outsidewmsmanage&tab=Outside_wms_dateil&app=Stock/OutsideWmsOrder','id'=>$id_list['tab_container'],'title'=>'委外出入库详情'),
			array('url'=>U('Stock/StockCommon/showTabsView',array('tabs'=>'Outside_wms_log')).'?prefix=outsidewmsmanage&tab=Outside_wms_log&app=Stock/OutsideWmsOrder','id'=>$id_list['tab_container'],'title'=>'日志'),

		);
		$list = UtilDB::getCfgRightList(array('warehouse','employee'));
		$warehouse_default['0'] = array('id' => 'all', 'name' => '全部');
		$warehouse_array = array_merge($warehouse_default, $list['warehouse']);
		$employee_default['0'] = array('id' => 'all','name'=>'全部');
		$employee_array = array_merge($employee_default,$list['employee']);
		$this->assign('warehouse_array',$warehouse_array);
		$this->assign('employee_array',$employee_array);
		$this->assign('params',json_encode($params));
		$this->assign('arr_tabs',json_encode($arr_tab));
		$this->assign('datagrid',$datagrid);
		$this->assign('id_list',$id_list);
		$this->display('WmsOutsideManage::WmsOutsideManage');
	}
	public function search($page = 1,$row = 20,$search = array(),$sort = 'order_id',$order = 'desc'){
		try{
			$result = D('Stock/OutsideWmsOrder')->search($page,$row,$search,$sort,$order);
		}catch(\Exception $e){
			\Think\Log::write($e->getMessage());
			$result = array('rows'=>array(),'total'=>0);
		}
		$this->ajaxReturn($result);
	}
	public function edit(){
		$edit_info = I('','',C('JSON_FILTER'));
		$id = $edit_info['id'];
		$management_info = $edit_info['management_info'];
		$id_list = array();
		$suffix = 'edit';
		$need_ids = array('form','toolbar','datagrid','dialog_wms');
		$this->getIDList($id_list,$need_ids,'',$suffix);
		$list = UtilDB::getCfgRightList(array('warehouse','logistics','employee'),array('warehouse'=>array('is_disabled'=>0,'type'=>11),'logistics'=>array('is_disabled'=>0),'employee'=>array('is_disabled'=>0)));
		$fields = get_field('OutsideWmsOrder','outsidewmsorder');
		$datagrid = array(
			"id" => $id_list["datagrid"],
			"options" => array(
				"toolbar" => $id_list["toolbar"],
				"fitColumns" => false,
                "rownumbers" => true,
                "pagination" => false,
                "singleSelect"=>false,
                "ctrlSelect" => true,
			),
			"fields" => $fields
		);
		$params = array(
			"datagrid" => $id_list["datagrid"],
			"form" => $id_list["form"],
			"id_list" => $id_list,
			"dialog" => $id_list['dialog_wms'],
			"prefix" => "OutsideWmsOrder"."_".$suffix,
			"submit_url" => U("OutsideWmsOrder/updateWmsOrder")."?order_id=".$id,
			"order_id"=>$id,
            "parent_info"=>$management_info,
			"wms_order_tool" =>CONTROLLER_NAME,
		);	
		$this->assign('params',json_encode($params));
        $this->assign('list',$list);
        $this->assign('datagrid',$datagrid);
        $this->assign('id_list',$id_list);
        $this->display('WmsOutsideOrder:showWmsOrder');
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
            $result['info'] ="请选择委外单";
            $result['status'] = 1;
            $this->ajaxReturn($result);
        }
		try{
			foreach ($consign_info as $key=>$id){   
				$wms_info_fields = array("ow.status","ow.warehouse_type","ow.order_type");
				$wms_info_cond = array(
					'ow.order_id' =>$id,
				);
				//根据出库单id获取出库单信息
				$res_so_info = D('Stock/OutsideWmsOrder')->alias('ow')->field($wms_info_fields)->where($wms_info_cond)->find();
				//判断是否查询成功
				if (empty($res_so_info))
				{
					SE('查询失败');
				}
				
				if ((int)$res_so_info['status']!= 40 && (int)$res_so_info['status']!= 50)
				{
					SE("状态不正确");
				}
				if((int)$res_so_info['warehouse_type']!=11 && (int)$res_so_info['warehouse_type']!=15)
				{
					SE("仓库类型不正确");
				}
				$WmsManager = ManagerFactory::getManager("Wms");
				if((int)$res_so_info['order_type'] == 2){
					$WmsManager->manual_wms_adapter_add_other_in($sid, $uid, $id);
				}else{
					$WmsManager->manual_wms_adapter_add_other_out($sid, $uid, $id);
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
	public function cancel_wo($ids)
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
            $result['info'] ="请选择委外单";
            $result['status'] = 1;
            $this->ajaxReturn($result);
        }
		try{
			foreach ($consign_info as $key=>$id){   
				$wms_info_fields = array("wo.status","wo.warehouse_type","wo.order_type");
				$wms_info_cond = array(
					'wo.order_id' =>$id,
				);
				//根据出库单id获取出库单信息
				$res_so_info = D('Stock/OutsideWmsOrder')->alias('wo')->field($wms_info_fields)->where($wms_info_cond)->find();
				//判断是否查询成功
				if (empty($res_so_info))
				{
					SE('查询失败');
				}
				
				if ((int)$res_so_info['status']!= 65 && (int)$res_so_info['status']!= 60 && (int)$res_so_info['status']!= 40)
				{
					SE("状态不正确");
				}
				if((int)$res_so_info['warehouse_type']!=11 && (int)$res_so_info['warehouse_type']!=15)
				{
					SE("仓库类型不正确");
				}
				if((int)$res_so_info['status'] == 40){
					D('OutsideWmsOrder')->cancel_wo($id);
				}else{
					$WmsManager = ManagerFactory::getManager("Wms");
					if((int)$res_so_info['order_type'] == 1){
						$WmsManager->manual_wms_adapter_cancel_other_out($sid, $uid, $id);
					}else{
						$WmsManager->manual_wms_adapter_cancel_other_in($sid, $uid, $id);
					}
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

	
}