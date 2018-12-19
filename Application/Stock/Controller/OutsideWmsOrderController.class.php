<?php
namespace Stock\Controller;

use Common\Controller\BaseController;
use Think\Exception;
use Think\Exception\BusinessLogicException;
use Common\Common;
use Common\Common\UtilDB;
class OutsideWmsOrderController extends BaseController {
	
	public function showWmsOrder(){
		$id_list = array();
		$suffix = 'new';
		$need_ids = array('form','toolbar','datagrid','dialog_wms');
		$this->getIDList($id_list,$need_ids,'',$suffix);
		$list = UtilDB::getCfgRightList(array('warehouse','logistics'),array('warehouse'=>array('is_disabled'=>0,'type'=>11),'logistics'=>array('is_disabled'=>0)));
		$fields = get_field('OutsideWmsOrder','outsidewmsorder');
		$params = array(
			'datagrid' => $id_list['datagrid'],
			'form' =>$id_list['form'],
			'id_list'=>$id_list,
			'prefix' =>'OutsideWmsOrder_'.$suffix,
			"submit_url" => U("OutsideWmsOrder/saveWmsOrder"),
			"wms_order_tool" =>CONTROLLER_NAME,
			'dialog' => $id_list['dialog_wms'],
			
		);
		$datagrid = array(
			'id' => $id_list['datagrid'],
			'options'=>array(
				"toolbar" =>$id_list['toolbar'],
				"fitColumns" => false,
                "rownumbers" => true,
                "pagination" => false,
                "singleSelect"=>false,
                "ctrlSelect" => true,
			),
			'fields' =>$fields,
		);
		$this->assign('list',$list);
		$this->assign('datagrid',$datagrid);
		$this->assign('params',json_encode($params));
		$this->assign('id_list',$id_list);
		$this->display('WmsOutsideOrder:showWmsOrder');
	}
	public function getOtherInfo(){
		$data_info = I('','',C('JSON_FILTER'));
		$id = intval($data_info['id']);
		$fields = array('zip as receiver_zip','contact as `search[receiver_name]`','IF(TRIM(telno)=\'\' OR IF(telno=NULL,TRUE,FALSE),mobile,telno) as `search[receiver_telno]`','CONCAT(province,city,district,address) as `search[receiver_address]`');
        $where = array('warehouse_id'=>$id);
        try{
			$info = D('Setting/Warehouse')->getWarehouseList($fields,$where);
            if(empty($info)){
                SE(self::UNKNOWN_ERROR);
            }
            $result = array('status'=>0,'data'=>$info[0]);
        }catch(BusinessLogicException $e){
            $result = array('status' => 1, 'data' => $e->getMessage(),);
        }catch(\Exception $e){
            \Think\Log::write($e->getMessage());
            $result = array('status'=>1,'data'=>self::UNKNOWN_ERROR);
        }
        $this->ajaxReturn($result);
	}
	public function saveWmsOrder(){
		try{
			$result = array('status'=>0,'data'=>array(),'info'=>'');
			$data = I('','',C('JSON_FILTER'));
			$result = D('Stock/OutsideWmsOrder')->saveWmsOrder($data);
			if($result['status'] == 0){
				$result['info'] = '保存成功';
			}
			
		}catch(\Exception $e){
            \Think\Log::write($e->getMessage());
            $result = array('status'=>1,'info'=>self::UNKNOWN_ERROR,'data'=>array());
        }

        return $this->ajaxReturn($result);
		
	} 
	public function getEditinfo(){
		try{
			$id = I('post.id');
			$form_field = array('order_type as `search[order_type]`','warehouse_id as `search[warehouse_id]`','receiver_name as `search[receiver_name]`','receiver_telno as `search[receiver_telno]`','receiver_address as `search[receiver_address]`','logistics_id as `search[logistics_id]`','logistics_no as `search[logistics_no]`','receiver_zip as `search[receiver_zip]`','logistics_fee as `search[logistics_fee]`','other_fee as `search[other_fee]`','total_price as `search[amount]`','remark as `search[remark]`','transport_mode as `search[transport_mode]`');
			$order_where = array('order_id'=>$id);
			$form_data = D('OutsideWmsOrder')->getOutsideWms($form_field,$order_where);
			if(empty($form_data)){
				SE('未查询到委外单数据');
			}
			$point_number = get_config_value('point_number',0);
			$stock_num = 'CAST(IFNULL(ss.stock_num,0) AS DECIMAL(19,'.$point_number.')) stock_num';
			$num = 'CAST(owd.num AS DECIMAL(19,'.$point_number.')) num';
			$detail_field = array('cgu.name as unit_name,owd.spec_id,owd.rec_id as id,CAST((owd.num*owd.price)as DECIMAL(19,4)) amount,'.$num.',owd.price as cost_price,owd.position_id,gs.spec_no,gs.spec_code,gs.spec_name,gs.barcode,gg.goods_name,gg.goods_no,'.$stock_num.',gb.brand_name,cwp.position_no');
			$detail_where = array('owd.order_id'=>$id);
			$join = 'left join outside_wms_order owo on owo.order_id = owd.order_id left join stock_spec ss on owd.spec_id = ss.spec_id and owo.warehouse_id = ss.warehouse_id left join goods_spec gs on gs.spec_id = owd.spec_id '.
					'left join goods_goods gg on gg.goods_id = gs.goods_id left join goods_brand gb on '.
					'gb.brand_id = gg.brand_id left join cfg_warehouse_position cwp on cwp.rec_id = owd.position_id '.
					' left join cfg_goods_unit cgu on cgu.rec_id = owd.base_unit_id';
			$detail_data = D('OutsideWmsDateil')->getOutsideWmsDetail($detail_field,$detail_where,$join,'owd');
			if(empty($detail_data)){
				SE('未查询到委外单详情数据');
			}
			$result = array('status'=>0,'form_data'=>$form_data[0],'detail_data'=>$detail_data);
		}catch(BusinessLogicException $e){
            $result = array('status' => 1, 'info' =>$e->getMessage(),'data'=>array());
		}catch(\Exception $e){
			$result = array('status' => 1, 'info' =>self::UNKNOWN_ERROR,'data'=>array());
		}
		$this->ajaxReturn($result);
	}
	
	public function updateWmsOrder($order_id){
		try{
			$data = I('','',C('JSON_FILTER'));
			$result = D('OutsideWmsOrder')->updateWmsOrder($data,$order_id);
		}catch(BusinessLogicException $e){
			$result = array('status'=>1,'info'=>$e->getMessage());
		}catch(\Exception $e){
			\Think\Log::write('updateWmsOrder--'.$e->getMessage());
			$result = array('status'=>1,'info'=>self::UNKNOWN_ERROR);
		}
		$this->ajaxReturn($result);
	}
}