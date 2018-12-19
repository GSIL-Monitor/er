<?php
namespace Purchase\Controller;
use Think\Exception\BusinessLogicException;
use Common\Controller\BaseController; 
use Think\Model;
use Common\Common;
use Common\Common\UtilTool;
use Common\Common\ExcelTool;
use Common\Common\UtilDB;
use Purchase\PurchaseReturnField;

class PurchaseReturnController extends BaseController {
	public function purchase_return_show(){
		$id_list = array();
		$purchase_return_tool = CONTROLLER_NAME;
		$need_ids = array('form','toolbar','datagrid','dialog_spec','put_purchase');
		$this->getIDList($id_list,$need_ids,'','new_return');
		$list = UtilDB::getCfgRightList(array('warehouse','logistics','employee','provider'),array('warehouse'=>array('is_disabled'=>0),'logistics'=>array('is_disabled'=>0),'employee'=>array('is_disabled'=>0),'provider'=>array('is_disabled'=>0)));
		foreach($list['provider'] as $k=>$v){if($v['id']==0) unset($list['provider'][$k]);}
		$fields = get_field('PurchaseReturn','purchase_reurn_order');
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
			"fields" => $fields,
		);
//		$provider = D('Setting/PurchaseProvider')->getALlPurchaseProvider(array('is_disabled'=>0,'id'=>array('neq','0')));
//		$list['provider'] = $provider['data'];
		$params = array(
			"datagrid" => $id_list["datagrid"],
			"form" => $id_list["form"],
			"id_list" => $id_list,
			"dialog" => array("purchase_spec" => $id_list["dialog_spec"]),
			"prefix" => "purchase_return_new",
			"submit_url" => U("PurchaseReturn/savePurchaseReturn"),
			"purchase_return_tool" =>$purchase_return_tool,
			"put_purchase"=>array(
				'id'=>$id_list['put_purchase'],
				'url'=>U("PurchaseReturn/put_purchase"),
				'title'=>'引入采购单',
			),
		);	
		$this->assign('purchase_return_tool',   $purchase_return_tool);
		$this->assign('params',json_encode($params));
        $this->assign('list',$list);
        $this->assign('datagrid',$datagrid);
        $this->assign('id_list',$id_list);
        $this->display('PurchaseReturn:purchase_return');
	}
    public function edit($id,$management_info){
        try{
            $id_list = array();
            $purchase_return_tool = CONTROLLER_NAME;
            $need_ids = array('form','toolbar','datagrid','dialog_spec');
            $this->getIDList($id_list,$need_ids,'','edit_return');
            $list = UtilDB::getCfgRightList(array('warehouse','logistics','employee'),array('warehouse'=>array('is_disabled'=>0),'logistics'=>array('is_disabled'=>0),'employee'=>array('is_disabled'=>0)));
            $fields = get_field('PurchaseReturn','purchase_reurn_order');
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
                "fields" => $fields,
            );
            $provider = D('Setting/PurchaseProvider')->getALlPurchaseProvider(array('is_disabled'=>0,'id'=>array('neq','0')));
            $list['provider'] = $provider['data'];
            $params = array(
                "datagrid" => $id_list["datagrid"],
                "form" => $id_list["form"],
                "id_list" => $id_list,
                "dialog" => array("purchase_spec" => $id_list["dialog_spec"]),
                "prefix" => "purchase_return_edit",
                "submit_url" => U("PurchaseReturn/updatePurchaseReturn"."?return_id=".$id),
                "order_id"=>$id,
                "parent_info"=>$management_info,
                "purchase_return_tool" =>$purchase_return_tool,
            );
            $this->assign('purchase_return_tool',   $purchase_return_tool);
            $this->assign('params',json_encode($params));
            $this->assign('list',$list);
            $this->assign('datagrid',$datagrid);
            $this->assign('id_list',$id_list);
            $this->display('PurchaseReturn:purchase_return');
        }
        catch(\PDOException $e){
            \Think\Log::write($e->getMessage());
        }
    }
    public function updatePurchaseReturn($return_id){
        try{
            $result = array('status'=>0,'info'=>'更新成功','data'=>array());
            $purchase_detail = I('','',C('JSON_FILTER'));
            $purchase_detail['return_id'] = $return_id;
            $result = D('Purchase/PurchaseReturn')->updatePurchaseReturn($purchase_detail);
        }catch(\BusinessLogicException $e){
            $result =  array('status'=>1,'info'=>$e->getMessage(),'data'=>array());
        }catch(\Exception $e){
            \Think\Log::write($e->getMessage());
            $result = array('status'=>1,'info'=>self::UNKNOWN_ERROR,'data'=>array());
        }
        return $this->ajaxReturn($result);
    }
    public function getEditinfo($id){
        try{
            $field = array('po.provider_id as `search[provider_id]`,po.status,po.warehouse_id as `search[warehouse_id]`,po.contact as `search[contact]`,po.telno as `search[telno]`,po.receive_address as `search[address]`,po.post_fee as `search[post_fee]`,po.other_fee as `search[other_fee]`,po.purchaser_id as `search[purchaser_id]`,po.logistics_type as `search[logistics_type]`,po.remark as `search[remark]`');
            $where = array('return_id'=>$id);
            $form_data = D('Purchase/PurchaseReturn')->getPurchase($field,$where);
            if(empty($form_data)){
                \Think\Log::write('未查询到采购单信息');
                SE(self::UNKNOWN_ERROR);
            }
            $form_data = $form_data[0];

            if($form_data['status'] != 20 && $form_data['status'] != 43){
                SE('采购单不是编辑状态！');
            }
            $detail_info = D('Purchase/PurchaseReturnDetail')->showPurchasedetail($id);
			foreach($detail_info as $k=>$v){
				$detail_info[$k]['price'] = $v['cost_price'];
				if($v['discount']==0){
					$detail_info[$k]['ori_price'] = $v['cost_price'];
					$detail_info[$k]['discount_rate'] = '1.0000';
				}else{
					$detail_info[$k]['discount_rate'] = $v['discount'];
					$detail_info[$k]['ori_price'] = number_format(($v['cost_price']/$v['discount']),4);
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
public function getOtherInfo(){
        $data_info = I('','',C('JSON_FILTER'));
		$id = intval($data_info['id']);
		$fields = array('contact as `search[contact]`','IF(TRIM(telno)=\'\' OR IF(telno=NULL,TRUE,FALSE),mobile,telno) as `search[telno]`','address as `search[address]`','province','city','district','remark as `search[remark]`');
        $where = array('id'=>$id);
        try{
			$info = D('Setting/PurchaseProvider')->field($fields)->where($where)->select();
            if(empty($info)){
                SE(self::UNKNOWN_ERROR);
            }
            $result = array('status'=>0,'data'=>$info[0]);
        }catch(BusinessLogicException $e){
            $result = array('status' => 1, 'info' => $e->getMessage(),);
        }catch(\Exception $e){
            \Think\Log::write($e->getMessage());
            $result = array('status'=>1,'info'=>self::UNKNOWN_ERROR);
        }
        $this->ajaxReturn($result);
    }
	public function savePurchaseReturn(){
		try{
			$result = array('status'=>0,'data'=>array(),'info'=>'');
			$data = I('','',C('JSON_FILTER'));
			$result = D('Purchase/PurchaseReturn')->savePurchaseReturn($data);
			if($result['status'] == 0){
				$result['info'] = '保存成功';
			} 
		}catch(BusinessLogicException $e){
			 \Think\Log::write($e->getMessage());
            $result = array('status'=>1,'info'=>$e->getMessage(),'data'=>array());
		}catch(\Exception $e){
			 \Think\Log::write($e->getMessage());
            $result = array('status'=>1,'info'=>self::UNKNOWN_ERROR,'data'=>array());
		}
		$this->ajaxReturn($result);
	}
	public function getPurchaseReturn($id){
		try{
			$field = array('po.provider_id as `provider_id`,pp.provider_name as `provider`,po.return_no as `search[src_order_no]`,po.return_id as `search[src_order_id]`,po.status,po.warehouse_id as `search[warehouse_id]`,po.post_fee as `search[post_fee]`,po.other_fee as `search[other_fee]`,po.purchaser_id as `purchaser_id`,po.logistics_type as `search[logistics_id]`,po.remark as `search[remark]`');
			$where = array('return_id'=>$id);
			$form_data = D('Purchase/PurchaseReturn')->getPurchase($field,$where);
			if(empty($form_data)){
				\Think\Log::write('未查询到采购单信息');
                SE(self::UNKNOWN_ERROR);
			}
			$form_data = $form_data[0];
			
			if($form_data['status'] < 40 || $form_data['status'] > 50){
				SE('采购退货单不是已审核状态！');
			}
			$point_number = get_config_value('point_number',0);
			$stock_num = 'CAST(IFNULL(ss.stock_num,0) AS DECIMAL(19,'.$point_number.')) stock_num';
			$num = 'CAST(pc.num-pc.out_num AS DECIMAL(19,'.$point_number.')) num';
			$expect_num = 'CAST(pc.num-pc.out_num AS DECIMAL(19,'.$point_number.')) expect_num';
			$detail_field = array('(CASE  WHEN ss.last_position_id THEN ss.last_position_id  ELSE -'.$form_data['search[warehouse_id]'].' END) AS position_id','(CASE  WHEN ss.last_position_id THEN cwp.position_no ELSE cwp2.position_no END) AS position_no','pc.rec_id as id','gs.spec_no','pc.spec_id','gg.goods_no','gg.goods_name','gb.brand_name','gs.spec_code','gs.spec_name','gg.goods_id','gs.barcode',$num,$expect_num,'pc.price','pc.amount as total_amount','pc.remark','pc.base_unit_id','cgu.name as unit_name',$stock_num,'pc.price as src_price','gs.retail_price','gs.lowest_price','gs.market_price');
			$detail_where = array('return_id'=>$id);
			$detail_info = D('Purchase/PurchaseReturnDetail')->fetchsql(false)->alias('pc')->field($detail_field)->join('LEFT JOIN goods_spec gs ON gs.spec_id = pc.spec_id')->join('LEFT JOIN goods_goods gg ON gg.goods_id = gs.goods_id')->join('LEFT JOIN goods_brand gb ON gg.brand_id = gb.brand_id')->join('left join cfg_goods_unit cgu on cgu.rec_id = gs.unit')->join('LEFT JOIN stock_spec ss ON(gs.spec_id=ss.spec_id AND ss.warehouse_id='.$form_data['search[warehouse_id]'].')')->join('LEFT JOIN cfg_warehouse_position cwp ON(ss.last_position_id = cwp.rec_id)')->join('LEFT JOIN cfg_warehouse_position cwp2 ON(cwp2.rec_id = -'.$form_data['search[warehouse_id]'].')')->where($detail_where)->select();
			if(empty($detail_info)){
				\Think\Log::write('未查询到采购退货单详情信息');
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
	public function put_purchase(){
		$id_list = array();
		$need_ids = array('form','toolbar','datagrid','tab_container','hidden_flag');
		$this->getIDList($id_list,$need_ids,'','put_purchase');
		$list = UtilDB::getCfgRightList(array('warehouse','provider'),array('warehouse'=>array('is_disabled'=>0),'provider'=>array('is_disabled'=>0)));
		foreach($list['provider'] as $k=>$v){if($v['id']==0) unset($list['provider'][$k]);}
		$fields = get_field('PurchaseOrder','purchasemanagement');
		$datagrid = array(
			'id'=>$id_list['datagrid'],
			'options'=>array(
				'toolbar'=>$id_list['toolbar'],
				'url'=>U('PurchaseReturn/search_purchase_order'),
				'fitColumns'=>false,
				"rownumbers" => true,
				"pagination" => true,
				"method"     => "post",
			),
			'fields'=>$fields,
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
			array('url'=>U('Purchase/PurchaseCommon/showTabsView',array('tabs'=>"purchase_order_detail")).'?prefix=purchasereturn&tab=purchase_order_detail&app=Purchase/PurchaseOrder',"id"=>$id_list['tab_container'],"title"=>"采购单详情"),
		);
//		$provider = D('Setting/PurchaseProvider')->getALlPurchaseProvider(array('is_disabled'=>0,'id'=>array('neq','0')));
//		$list['provider'] = $provider['data'];
		$warehouse_default['0'] = array('id' => 'all', 'name' => '全部');
		$warehouse_array = array_merge($warehouse_default, $list['warehouse']);
		$provider_default['0'] = array('id' => 'all','name'=>'全部');
		$provider_array = array_merge($provider_default,$list['provider']);
		$this->assign('warehouse_array',$warehouse_array);
		$this->assign('provider_array',$provider_array);
		$this->assign('params',json_encode($params));
		$this->assign('arr_tabs',json_encode($arr_tabs));
		$this->assign('datagrid',$datagrid);
		$this->assign('id_list',$id_list);
		$this->display('put_purchase');
	}
	public function search_purchase_order($page = 1,$row = 20,$search = array(),$sort = 'id',$order = 'desc'){
		try{
			$type = 'return';
			$result = D('PurchaseOrder')->search($page,$row,$search,$sort,$order,$type);
		}catch(\Exception $e){
			\Think\Log::write($e->getMessage());
			$result = array('rows'=>array(),'total'=>1);
			
		}
		$this->ajaxReturn($result);
	}

} 