<?php
namespace Stock\Controller;
use Common\Common\UtilDB;
use Common\Controller\BaseController;
use Think\Exception\BusinessLogicException;

/**
 * Created by PhpStorm.
 * User: ct
 * Date: 2016/5/11
 * Time: 14:32
 */
class StockTransferController extends BaseController{
    /**
     * 显示调拨开单
     */
    public function show()
    {
        $id_list = array();
        $suffix = 'new';
        $need_ids = array('form','toolbar','datagrid','dialog_stock_spec');
        $this->getIDList($id_list,$need_ids,'',$suffix);
        $cfg_list = UtilDB::getCfgRightList(array('warehouse','logistics'),array('warehouse'=>array('is_disabled'=>0),'logistics'=>array('is_disabled'=>0)));
        $fields = get_field("StockTransfer", "stock_transfer_made_detail");
        $datagrid = array(
            "id"      => $id_list["datagrid"],
            "options" => array(
                "toolbar"    => $id_list["toolbar"],
                "fitColumns" => false,
                "rownumbers" => true,
                "pagination" => false,
                "singleSelect"=>false,
                "ctrlSelect" => true,
            ),
            "fields"  => $fields
        );


        $params = array(
            'datagrid'=>array('id'=>$id_list['datagrid']),
            'form'=>array('id'=>$id_list['form']),
            'id_list'=>$id_list,
            'dialog'=>array('stock_spec'=>$id_list['dialog_stock_spec'],'position'=>'flag_set_dialog'),
            'prefix'=>'stocktransfer'.'_'.$suffix,
            'submit_url'=>U('StockTransfer/saveTransferOrder'),
        );
        $this->assign('params',json_encode($params));
        $this->assign('list',$cfg_list);
        $this->assign('datagrid',$datagrid);
        $this->assign('id_list',$id_list);
        $this->assign('js_name',$params['prefix']);
        $this->display('StockTransfer:stock_transfer');
    }
    /**
     * 保存调拨开单
     */
    public function saveTransferOrder()
    {

        try{
            $result = array('status'=>0,'info'=>'','data'=>array());
            $transfer_info          = I('','',C('JSON_FILTER'));
            $new_info = $transfer_info['data'];
            $new_info['search'] = $transfer_info['search'];
            $result = D('Stock/StockTransfer')->saveTransferOrder($new_info);
            if($result['status'] == 0)
            {
                $result['info']='保存成功';
            }
        }catch(\Exception $e){
            \Think\Log::write($e->getMessage());
            $result = array('status'=>1,'info'=>self::UNKNOWN_ERROR,'data'=>array());
            return $this->ajaxReturn($result);
        }
        try{
            //自动提交
            $stocktransfer_auto_commit = get_config_value('stocktransfer_auto_commit',0);
            if($stocktransfer_auto_commit == 1){
                $result['stocktransfer_auto_commit'] = 1;
                D("Stock/StockTransfer")->submitStockTransOrder($result['order_id'],false);
            }
        }catch(BusinessLogicException $e){
            $msg = $e->getMessage();
            $result['status'] = 2;
            $result['info'] = '自动提交失败，失败原因：'.$msg;
        }catch(\Exception $e){
            \Think\Log::write($e->getMessage());
            $result = array('status'=>2,'info'=>self::UNKNOWN_ERROR,'data'=>array());
        }
        return $this->ajaxReturn($result);
    }
    /**
     * 更新调拨开单
     */
    public function updateTransferOrder($order_id)
    {
        try{
            $result = array('status'=>0,'info'=>'','data'=>array());
            $transfer_info          = I('','',C('JSON_FILTER'));
            //$transfer_info['order_id'] = $order_id;
            $new_info = $transfer_info['data'];
            $new_info['search'] = $transfer_info['search'];
            $new_info['order_id'] = $order_id;
            $result = D('Stock/StockTransfer')->updateTransferOrder($new_info);
            if($result['status'] == 0)
            {
                $result['info']='更新成功';
            }
        }catch(\Exception $e){
            \Think\Log::write($e->getMessage());
            $result = array('status'=>1,'info'=>self::UNKNOWN_ERROR,'data'=>array());
        }

        return $this->ajaxReturn($result);
    }

    public function getOtherInfo(){
        $position_info = I('post.','',C('JSON_FILTER'));
        $id = $position_info['warehouse_id'];
        $fields = array('contact as `search[contact]`','IF(TRIM(telno)=\'\' OR IF(telno=NULL,TRUE,FALSE),mobile,telno) as `search[telno]`','CONCAT(province,city,district,address) as `search[address]`');
        $where = array('warehouse_id'=>$id);
        try{
            $info = D('Setting/Warehouse')->getWarehouseList($fields,$where);
            $data = D('Stock/StockSpec')->getDefaultPosition($position_info);
            if(empty($info)){
                SE(self::UNKNOWN_ERROR);
            }
            $result = array('status'=>0,'data'=>array('warehouse_info'=>$info[0],'detail_info'=>$data));
        }catch(BusinessLogicException $e){
            $result = array('status' => 1, 'info' => $e->getMessage(),);
        }catch(\Exception $e){
            \Think\Log::write($e->getMessage());
            $result = array('status'=>1,'info'=>self::UNKNOWN_ERROR);
        }
        $this->ajaxReturn($result);
    }
    public function edit($id,$management_info)
    {
        try{
            $id_list = array();
            $suffix = 'edit';
            $need_ids = array('form','toolbar','datagrid','dialog_stock_spec');
            $this->getIDList($id_list,$need_ids,'',$suffix);
            $cfg_list = UtilDB::getCfgRightList(array('warehouse','logistics'),array('warehouse'=>array('is_disabled'=>0),'logistics'=>array('is_disabled'=>0)));
            $fields = get_field("StockTransfer", "stock_transfer_made_detail");
            $datagrid = array(
                "id"      => $id_list["datagrid"],
                "options" => array(
                    "toolbar"    => $id_list["toolbar"],
                    "fitColumns" => false,
                    "rownumbers" => true,
                    "pagination" => false,
                    "singleSelect"=>false,
                    "ctrlSelect" => true,
                ),
                "fields"  => $fields
            );


            $params = array(
                'datagrid'=>array('id'=>$id_list['datagrid']),
                'form'=>array('id'=>$id_list['form']),
                'id_list'=>$id_list,
                'dialog'=>array('stock_spec'=>$id_list['dialog_stock_spec'],'position'=>'flag_set_dialog'),
                'prefix'=>'stocktransfer'.'_'.$suffix,
                'order_id'=>$id,
                'parent_info'=>$management_info,
                'submit_url'=>U('StockTransfer/updateTransferOrder').'?order_id='.$id,
            );

            $this->assign('params',json_encode($params));
            $this->assign('list',$cfg_list);
            $this->assign('datagrid',$datagrid);
            $this->assign('id_list',$id_list);
            $this->assign('js_name',$params['prefix']);
            $this->display('StockTransfer:stock_transfer');
        }catch (BusinessLogicException $e){
            $this->error($e->getMessage());
        }catch (\Exception $e){
            \Think\Log::write($e->getMessage());
            $this->error(self::UNKNOWN_ERROR);
        }
    }
    public function getEditInfo($id){
        try{
            $order_fields = array('type as `search[type]`','status','mode as `search[mode]`','from_warehouse_id as `search[from_warehouse_id]`','to_warehouse_id as `search[to_warehouse_id]`','contact as `search[contact]`','address as `search[address]`','remark as `search[remark]`','telno as `search[telno]`','logistics_id as `search[logistics_id]`');
            $order_where = array('rec_id'=>$id);
            $order_info = D('Stock/StockTransfer')->getStockTransOrder($order_fields,$order_where);
            if(empty($order_info)){
                \Think\Log::write('未查询到调拨单信息');
                SE(self::UNKNOWN_ERROR);
            }
            $order_info = $order_info[0];

            if($order_info['status']!=20 && $order_info['status']!=42){
                SE('调拨单状态不是出于编辑中');
            }
			$point_number = get_config_value('point_number',0);
			
			$sys_available_stock =  get_config_value('sys_available_stock',640);
			$available_str = D('Stock/StockSpec')->getAvailableStrBySetting($sys_available_stock);
			
			$stock_num = 'CAST(std.stock_num AS DECIMAL(19,'.$point_number.')) stock_num';
			$orderable_num = 'CAST(IFNULL('.$available_str.', 0) AS DECIMAL(19,'.$point_number.')) as orderable_num';
			$num = 'CAST(std.num AS DECIMAL(19,'.$point_number.')) num';
			
            $detail_fields = array('std.rec_id','gs.spec_no','std.spec_id','gg.goods_no','gg.goods_name','gb.brand_name','gs.spec_code','gs.spec_name','gs.barcode',$stock_num,$orderable_num,$num,'cgu.name as unit_name','std.remark','IF(ss_t.last_position_id AND ss_t.order_num = 0 AND ss_t.sending_num = 0 AND '.$order_info['search[mode]'].'<>1,1,IF(ss_t.spec_id IS NOT NULL and '.$order_info['search[mode]'].'<>1,1,0)) AS is_allocated','cwp_f.position_no as from_position_no','cwp_t.position_no as to_position_no','std.to_position','std.from_position');
            $detail_where = array('transfer_id'=>$id);
            $detail_info = D('Stock/StockTransferDetail')->alias('std')->field($detail_fields)->join('left join goods_spec gs on gs.spec_id = std.spec_id')->join('left join goods_goods gg on gg.goods_id = gs.goods_id')->join('left join goods_brand gb on gg.brand_id = gb.brand_id')->join('left join cfg_goods_unit cgu on cgu.rec_id = gs.unit')->join('stock_spec ss on ss.spec_id = std.spec_id and ss.warehouse_id ='.$order_info['search[from_warehouse_id]'])->join('left join cfg_warehouse_position cwp_f on cwp_f.rec_id = std.from_position')->join('left join cfg_warehouse_position cwp_t on cwp_t.rec_id = std.to_position')->join('left join stock_spec ss_t on ss_t.warehouse_id = '.$order_info['search[to_warehouse_id]'].' and ss_t.spec_id = std.spec_id')->where($detail_where)->select();
            if(empty($detail_info)){
                \Think\Log::write('未查询到调拨单详情信息');
                SE(self::UNKNOWN_ERROR);
            }
            $result = array(
                'status'=>0,
                'form_data'=>$order_info,
                'detail_data'=>array('total'=>count($detail_info),'rows'=>$detail_info),
            );

        }catch (BusinessLogicException $e){
           $result =  array('status'=>1,'info'=>$e->getMessage());
        }catch (\Exception $e){
            \Think\Log::write($e->getMessage());
           $result =  array('status'=>1,'info'=>self::UNKNOWN_ERROR);
        }
        $this->ajaxReturn($result);

    }
}
