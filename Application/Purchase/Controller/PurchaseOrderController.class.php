<?php
namespace Purchase\Controller;
use Think\Exception\BusinessLogicException;
use Common\Controller\BaseController;
use Think\Model;
use Common\Common;
use Common\Common\UtilDB;

class PurchaseOrderController extends BaseController{
	
	public function show(){
		try{
		$id_list = array();
		$suffix = 'new';
		$purchase_order_tool = CONTROLLER_NAME;
		$need_ids = array('form','toolbar','datagrid','dialog_purchase_spec');
		$this->getIDList($id_list,$need_ids,'',$suffix);
		$list = UtilDB::getCfgRightList(array('warehouse','logistics','employee','provider'),array('warehouse'=>array('is_disabled'=>0),'logistics'=>array('is_disabled'=>0),'employee'=>array('is_disabled'=>0),'provider'=>array('is_disabled'=>0)));
		$fields = get_field('PurchaseOrder','purchaseorder');
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
//		$provider = D('Setting/PurchaseProvider')->getALlPurchaseProvider(array('is_disabled'=>0));
//		$list['provider'] = $provider['data'];
		$params = array(
			"datagrid" => $id_list["datagrid"],
			"form" => $id_list["form"],
			"id_list" => $id_list,
			"dialog" => array("purchase_spec" => $id_list["dialog_purchase_spec"]),
			"prefix" => "purchase"."_".$suffix,
			"submit_url" => U("PurchaseOrder/savePurchaseOrder"),
			"purchase_order_tool" =>$purchase_order_tool,
		);	
		$this->assign('purchase_order_tool',   $purchase_order_tool);
		$this->assign('params',json_encode($params));
        $this->assign('list',$list);
        $this->assign('datagrid',$datagrid);
        $this->assign('id_list',$id_list);
        $this->display('PurchaseOrder:show');
		}
			catch(\PDOException $e){
	        \Think\Log::write($e->getMessage());
	    }
	}
	public function savePurchaseOrder(){
		try{
			$result = array('status'=>0,'data'=>array(),'info'=>'');
			$data = I('','',C('JSON_FILTER'));
			$result = D('Purchase/PurchaseOrder')->savePurchaseOrder($data);
			if($result['status'] == 0){
				$result['info'] = '保存成功';
			}
			
		}catch(\Exception $e){
            \Think\Log::write($e->getMessage());
            $result = array('status'=>1,'info'=>self::UNKNOWN_ERROR,'data'=>array());
        }

        return $this->ajaxReturn($result);
		
	}
	public function edit($id,$management_info){
		
		try{
		$id_list = array();
		$suffix = 'edit';
		$purchase_order_tool = CONTROLLER_NAME;
		$need_ids = array('form','toolbar','datagrid','dialog_purchase_spec');
		$this->getIDList($id_list,$need_ids,'',$suffix);
		$list = UtilDB::getCfgRightList(array('warehouse','logistics','employee'),array('warehouse'=>array('is_disabled'=>0),'logistics'=>array('is_disabled'=>0),'employee'=>array('is_disabled'=>0)));
		$fields = get_field('PurchaseOrder','purchaseorder');
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
		$provider = D('Setting/PurchaseProvider')->getALlPurchaseProvider(array('is_disabled'=>0));
		$list['provider'] = $provider['data'];
		$params = array(
			"datagrid" => $id_list["datagrid"],
			"form" => $id_list["form"],
			"id_list" => $id_list,
			"dialog" => array("purchase_spec" => $id_list["dialog_purchase_spec"]),
			"prefix" => "purchase"."_".$suffix,
			"submit_url" => U("PurchaseOrder/updatePurchase")."?order_id=".$id,
			"order_id"=>$id,
            "parent_info"=>$management_info,
			"purchase_order_tool" =>$purchase_order_tool,
		);	
		$this->assign('purchase_order_tool',   $purchase_order_tool);
		$this->assign('params',json_encode($params));
        $this->assign('list',$list);
        $this->assign('datagrid',$datagrid);
        $this->assign('id_list',$id_list);
        $this->display('PurchaseOrder:show');
		}
			catch(\PDOException $e){
	        \Think\Log::write($e->getMessage());
	    }
	}
	public function getEditinfo($id){
		try{
			$field = array('po.provider_id as `search[provider_id]`,po.status,po.warehouse_id as `search[warehouse_id]`,po.contact as `search[contact]`,po.telno as `search[telno]`,po.receive_address as `search[address]`,po.post_fee as `search[post_fee]`,po.other_fee as `search[other_fee]`,po.purchaser_id as `search[purchaser_id]`,po.logistics_type as `search[logistics_type]`,po.remark as `search[remark]`');
			$where = array('purchase_id'=>$id);
			$form_data = D('Purchase/PurchaseOrder')->getPurchase($field,$where);
			if(empty($form_data)){
				\Think\Log::write('未查询到采购单信息');
                SE(self::UNKNOWN_ERROR);
			}
			$form_data = $form_data[0];
			
			if($form_data['status'] != 20 && $form_data['status'] != 43){
				SE('采购单不是编辑状态！');
			}
			$detail_info = D('Purchase/PurchaseOrderDetail')->showPurchasedetail($id);
			foreach($detail_info as $k=>$v){
				if($v['discount']==0){
					$detail_info[$k]['price'] = $v['cost_price'];
					$detail_info[$k]['discount_rate'] = '1.0000';
				}else{
					$detail_info[$k]['discount_rate'] = $v['discount'];
					$detail_info[$k]['price'] = number_format(($v['cost_price']/$v['discount']),4);
				}
			}
			if(empty($detail_info)){
				\Think\Log::write('未查询到采购单详情信息');
                SE(self::UNKNOWN_ERROR);
			}
			$result = array(
                'status'=>0,
                'form_data'=>$form_data,
                'detail_data'=>array('total'=>count($detail_info),'rows'=>$detail_info),
            );
		}catch(\BusinessLogicException $e){
			$result =  array('status'=>1,'info'=>$e->getMessage());
        }catch (\Exception $e){
            \Think\Log::write($e->getMessage());
           $result =  array('status'=>1,'info'=>self::UNKNOWN_ERROR);
        }
        $this->ajaxReturn($result);
	}
	public function updatePurchase($order_id){
		try{
			$result = array('status'=>0,'info'=>'更新成功','data'=>array());
			$purchase_detail = I('','',C('JSON_FILTER'));
			$purchase_detail['order_id'] = $order_id;
			$result = D('Purchase/PurchaseOrder')->updatePurchase($purchase_detail);
		}catch(\BusinessLogicException $e){
			$result =  array('status'=>1,'info'=>$e->getMessage(),'data'=>array());	
		}catch(\Exception $e){
			\Think\Log::write($e->getMessage());
			$result = array('status'=>1,'info'=>self::UNKNOWN_ERROR,'data'=>array());
		}
		return $this->ajaxReturn($result);
	}
	public function getPurchaseinfo($id){
		try{
			$field = array('po.provider_id as `provider_id`,pp.provider_name as `provider`,po.purchase_no as `src_order_no`,po.status,po.warehouse_id as `warehouse_id`,po.post_fee as `post_fee`,po.other_fee as `other_fee`,po.purchaser_id as `purchaser_id`,po.logistics_type as `logistics_id`,po.remark');
			$where = array('purchase_id'=>$id);
			$form_data = D('Purchase/PurchaseOrder')->getPurchase($field,$where);
			if(empty($form_data)){
				\Think\Log::write('未查询到采购单信息');
                SE(self::UNKNOWN_ERROR);
			}
			$form_data = $form_data[0];
			
			if($form_data['status'] < 40 || $form_data['status'] > 50){
				SE('采购单不是已审核状态！');
			}
			$point_number = get_config_value('point_number',0);
			$stock_num = 'CAST(IFNULL(ss.stock_num,0) AS DECIMAL(19,'.$point_number.')) stock_num';
			$num = 'CAST(pc.num-pc.arrive_num AS DECIMAL(19,'.$point_number.')) num';
			$expect_num = 'CAST(pc.num-pc.arrive_num AS DECIMAL(19,'.$point_number.')) expect_num';
			$detail_field = array('IF(ss.last_position_id,1,IF(ss.spec_id IS NOT NULL AND (ss.order_num <> 0 OR ss.sending_num <> 0),1,0)) AS is_allocated,(CASE  WHEN ss.last_position_id THEN ss.last_position_id  ELSE -'.$form_data['warehouse_id'].' END) AS position_id','(CASE  WHEN ss.last_position_id THEN cwp.position_no ELSE cwp2.position_no END) AS position_no','pc.rec_id as id','gs.spec_no','pc.spec_id','gg.goods_no','gg.goods_name','gb.brand_name','gs.spec_code','gs.spec_name','gs.barcode',$num,$expect_num,'pc.price as cost_price','pc.amount as total_cost','pc.remark','pc.base_unit_id','cgu.name as unit_name',$stock_num,'pc.price as src_price','gs.retail_price','gs.lowest_price','gs.market_price','gs.img_url');
			$detail_where = array('purchase_id'=>$id);
			$detail_info = D('Purchase/PurchaseOrderDetail')->fetchsql(false)->alias('pc')->field($detail_field)->join('LEFT JOIN goods_spec gs ON gs.spec_id = pc.spec_id')->join('LEFT JOIN goods_goods gg ON gg.goods_id = gs.goods_id')->join('LEFT JOIN goods_brand gb ON gg.brand_id = gb.brand_id')->join('left join cfg_goods_unit cgu on cgu.rec_id = gs.unit')->join('LEFT JOIN stock_spec ss ON(gs.spec_id=ss.spec_id AND ss.warehouse_id='.$form_data['warehouse_id'].')')->join('LEFT JOIN cfg_warehouse_position cwp ON(ss.last_position_id = cwp.rec_id)')->join('LEFT JOIN cfg_warehouse_position cwp2 ON(cwp2.rec_id = -'.$form_data['warehouse_id'].')')->where($detail_where)->select();
			if(empty($detail_info)){
				\Think\Log::write('未查询到采购单详情信息');
                SE(self::UNKNOWN_ERROR);
			}
			$result = array(
                'status'=>0,
                'form_data'=>$form_data,
                'detail_data'=>array('total'=>count($detail_info),'rows'=>$detail_info),
            );
		}catch(\BusinessLogicException $e){
			$result =  array('status'=>1,'info'=>$e->getMessage());
        }catch (\Exception $e){
            \Think\Log::write($e->getMessage());
           $result =  array('status'=>1,'info'=>self::UNKNOWN_ERROR);
        }
        $this->ajaxReturn($result);
	}
	
	public function getPutPurchase($id){
		try{	
			$field = array('po.provider_id as `search[provider_id]`,pp.provider_name as `search[provider]`,po.purchase_no as `search[src_order_no]`,po.status,po.warehouse_id as `search[warehouse_id]`,po.post_fee as `search[post_fee]`,po.other_fee as `search[other_fee]`,po.purchaser_id as `search[purchaser_id]`,po.logistics_type as `search[logistics_id]`,po.remark as `search[remark]`');
			$where = array('purchase_id'=>$id);
			$form_data = D('Purchase/PurchaseOrder')->getPurchase($field,$where);
			if(empty($form_data)){
				\Think\Log::write('未查询到采购单信息');
                SE(self::UNKNOWN_ERROR);
			}
			$form_data = $form_data[0];
			$point_number = get_config_value('point_number',0);
			$stock_num = 'CAST(IFNULL(ss.stock_num,0) AS DECIMAL(19,'.$point_number.')) stock_num';
			$num = 'CAST(pc.num AS DECIMAL(19,'.$point_number.')) num';
			$expect_num = 'CAST(pc.num-pc.arrive_num AS DECIMAL(19,'.$point_number.')) expect_num';
			$detail_field = array('IF(ss.last_position_id,1,IF(ss.spec_id IS NOT NULL AND (ss.order_num <> 0 OR ss.sending_num <> 0),1,0)) AS is_allocated,(CASE  WHEN ss.last_position_id THEN ss.last_position_id  ELSE -'.$form_data['search[warehouse_id]'].' END) AS position_id','(CASE  WHEN ss.last_position_id THEN cwp.position_no ELSE cwp2.position_no END) AS position_no','pc.rec_id as id','gs.spec_no','pc.spec_id','gg.goods_no','gg.goods_name','gb.brand_name','gs.spec_code','gs.spec_name','gs.barcode',$num,$expect_num,'pc.price as cost_price','pc.price as price','pc.amount','pc.discount','pc.remark','pc.base_unit_id','cgu.name as unit_name',$stock_num,'pc.price as src_price','gs.retail_price','gs.lowest_price','gs.market_price','gs.img_url');
			$detail_where = array('purchase_id'=>$id);
			$detail_info = D('Purchase/PurchaseOrderDetail')->fetchsql(false)->alias('pc')->field($detail_field)->join('LEFT JOIN goods_spec gs ON gs.spec_id = pc.spec_id')->join('LEFT JOIN goods_goods gg ON gg.goods_id = gs.goods_id')->join('LEFT JOIN goods_brand gb ON gg.brand_id = gb.brand_id')->join('left join cfg_goods_unit cgu on cgu.rec_id = gs.unit')->join('LEFT JOIN stock_spec ss ON(gs.spec_id=ss.spec_id AND ss.warehouse_id='.$form_data['search[warehouse_id]'].')')->join('LEFT JOIN cfg_warehouse_position cwp ON(ss.last_position_id = cwp.rec_id)')->join('LEFT JOIN cfg_warehouse_position cwp2 ON(cwp2.rec_id = -'.$form_data['search[warehouse_id]'].')')->where($detail_where)->select();
			if(empty($detail_info)){
				\Think\Log::write('未查询到采购单详情信息');
                SE(self::UNKNOWN_ERROR);
			}
			$result = array(
                'status'=>0,
                'form_data'=>$form_data,
                'detail_data'=>array('total'=>count($detail_info),'rows'=>$detail_info),
            );
		}catch(\BusinessLogicException $e){
			$result =  array('status'=>1,'info'=>$e->getMessage());
        }catch (\Exception $e){
            \Think\Log::write($e->getMessage());
           $result =  array('status'=>1,'info'=>self::UNKNOWN_ERROR);
        }
        $this->ajaxReturn($result);
	}
	
	public function getOtherInfo(){
        $data_info = I('','',C('JSON_FILTER'));
		$id = intval($data_info['id']);
		
		$point_number = get_config_value('point_number',0);
		$num = 'CAST(1 AS DECIMAL(19,'.$point_number.')) num';
		$stock_num = 'CAST(IFNULL(ss.stock_num,0) AS DECIMAL(19,'.$point_number.')) stock_num,';
		$expect_num = 'CAST(0 AS DECIMAL(19,'.$point_number.')) expect_num,';
		$data_fields = array('gs.spec_id as id,gs.spec_id,'.$stock_num.$expect_num.$num.',gs.unit as base_unit_id,gs.spec_no,gg.goods_no,gg.goods_name,gs.spec_no,gs.spec_code,gs.barcode,gg.brand_id,gb.brand_name,cgu.name as unit_name,CAST(IFNULL(ss.cost_price,0) AS DECIMAL(19,4)) AS cost_price');
       
		
		$fields = array('contact as `search[contact]`','IF(TRIM(telno)=\'\' OR IF(telno=NULL,TRUE,FALSE),mobile,telno) as `search[telno]`','CONCAT(province,city,district,address) as `search[address]`');
        $where = array('warehouse_id'=>$id);
        try{
			$data = $data_info['info'];
			$spec_id = intval($data['id']);
			$load_data  = array();
			foreach($data as $value){
				$data_where = array('gs.spec_id'=>$value['spec_id']);
				$result = D('Stock/GoodsSpec')->alias('gs')->fetchsql(false)->field($data_fields)->join('left join stock_spec ss on gs.spec_id = ss.spec_id and ss.warehouse_id ='.$id)->join('left join goods_goods gg on gg.goods_id=gs.goods_id')->join('left join cfg_goods_unit cgu on gs.unit = cgu.rec_id')->join('left join goods_brand gb on gb.brand_id = gg.brand_id')->where($data_where)->select();
				array_push($load_data,$result[0]);
				
			}
			$info = D('Setting/Warehouse')->getWarehouseList($fields,$where);
            if(empty($info)){
                SE(self::UNKNOWN_ERROR);
            }
            $result = array('status'=>0,'data'=>$info[0],'load_data'=>$load_data);
        }catch(BusinessLogicException $e){
            $result = array('status' => 1, 'info' => $e->getMessage(),);
        }catch(\Exception $e){
            \Think\Log::write($e->getMessage());
            $result = array('status'=>1,'info'=>self::UNKNOWN_ERROR);
        }
        $this->ajaxReturn($result);
    }
    public function alarmPurchase(){

        try{
            $id_list = array();
            $suffix = 'alarmpurchase';
            $purchase_order_tool = CONTROLLER_NAME.'_'.$suffix;
            $need_ids = array('form','toolbar','datagrid','dialog_purchase_spec');
            $this->getIDList($id_list,$need_ids,'',$suffix);
            $list = UtilDB::getCfgRightList(array('warehouse','logistics','employee'),array('warehouse'=>array('is_disabled'=>0),'logistics'=>array('is_disabled'=>0),'employee'=>array('is_disabled'=>0)));
            $fields = get_field('PurchaseOrder','purchaseorder');
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
			$provider_id = I('get.provider_id');
			if($provider_id == '-1'){
				$provider = D('Setting/PurchaseProvider')->getALlPurchaseProvider(array('is_disabled'=>0,'id'=>0)); 
				$list['provider'] = $provider['data'];
			}else{
				$provider = D('Setting/PurchaseProvider')->getALlPurchaseProvider(array('is_disabled'=>0,'id'=>I('get.provider_id')));
				$list['provider'] =$provider['data'];
			}
            $params = array(
                "datagrid"      => $id_list["datagrid"],
                "form"          => $id_list["form"],
                "id_list"       => $id_list,
                "dialog"        => array("purchase_spec" => $id_list["dialog_purchase_spec"]),
                "order_type"   => 'alarm_purchase',
                "prefix"        => "purchase"."_".$suffix,
                "submit_url"    => U("PurchaseOrder/savePurchaseOrder"),
                "parent_info"=>array('dialog_id'=>I('get.dialog_id'),'datagrid_id'=>I('get.datagrid_id'),'warehouse_id'=>I('get.warehouse_id'),'sel_warehouse_id'=>I('get.sel_warehouse_id')),
                "purchase_order_tool" =>$purchase_order_tool,
            );
            $this->assign('purchase_order_tool',   $purchase_order_tool);
            $this->assign('params',json_encode($params));
            $this->assign('list',$list);
            $this->assign('datagrid',$datagrid);
            $this->assign('id_list',$id_list);
            $this->assign('is_dialog','alarmPurchaseDialog');
            $this->display('PurchaseOrder:show');
        }
        catch(\Exception $e)
        {
            $this->assign('message',$e->getMessage());
            $this->display('Common@Exception:dialog');
            exit();
        }
    }
    public function getAlarmInfo(){
        try{
            $spec_ids = I('post.ids','',C('JSON_FILTER'));
            $warehouse_id = I('post.warehouse_id','',C('JSON_FILTER'));

            $detail_info = D('Purchase/PurchaseOrder')->getAlarmPurchaseInfo($spec_ids,$warehouse_id);

            $result = array(
                'status'=>0,
                'detail_data'=>array('total'=>count($detail_info),'rows'=>$detail_info),
            );
        }catch(\BusinessLogicException $e){
            $result =  array('status'=>1,'info'=>$e->getMessage());
        }catch (\Exception $e){
            \Think\Log::write($e->getMessage());
            $result =  array('status'=>1,'info'=>self::UNKNOWN_ERROR);
        }
        $this->ajaxReturn($result);
    }
	public function loadEmployee(){
		try{
			$employee = D('Setting/Employee')->field('employee_id as `search[purchaser_id]`')->where(array('is_disabled'=>0,'account'=>$_SESSION['account']))->select();
			$result =  array('status'=>0,'info'=>$employee[0]);
		}catch (\Exception $e){
            \Think\Log::write($e->getMessage());
            $result =  array('status'=>1,'info'=>self::UNKNOWN_ERROR);
        }
		$this->ajaxReturn($result);
		
	}
}