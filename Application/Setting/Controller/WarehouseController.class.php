<?php
/**
 * Created by PhpStorm.
 * User: gaosong
 * Date: 15/9/23
 * Time: 上午11:36
 */
namespace Setting\Controller;

use Common\Common\DatagridExtention;
use Common\Controller\BaseController;
use Think\Exception\BusinessLogicException;
use Platform\Common\ManagerFactory;
use Common\Common\UtilDB;

class WarehouseController extends BaseController
{
    /**
     * 初始化id_list
     */
    function _initialize() {
        parent::_initialize();
        parent::getIDList($this->id_list, array("form", "toolbar", "datagrid", "add", "add_form", "edit", "edit_form","search_form","address"),CONTROLLER_NAME);
    }
    public function getWarehouseList()
    {
        $id_list = $this->id_list ;
        $datagrid = array(
            "id"      => $id_list["datagrid"],
            "options" => array(
                "url"        => U("Warehouse/search"),
                "toolbar"    => $id_list["toolbar"],
                "fitColumns" => false,
                "rownumbers" => true,
                "pagination" => true,
            ),
            "fields"  => get_field("Warehouse", "warehouse")
        );
        $params = array(
            'add'=>array(
                'id'     =>  $id_list['add'],
                'title'  =>  '新建仓库',
                'url'    =>  U('Setting/Warehouse/dialogAddWarehouse'),
                'width'  =>  '620',
                'height' =>  '250',
                'ismax'	 =>  false
            ),
            'edit'=>array(
                'id'     =>  $id_list['edit'],
                'title'  =>  '编辑仓库',
                'url'    =>  U('Setting/Warehouse/dialogEditWarehouse'),
                'width'  =>  '620',
                'height' =>  '250',
                'ismax'	 =>  false
            ),
			
            'datagrid'=>array(
                'id'    =>    $id_list["datagrid"],
            ),
            'warehouse_auth' => array(
                'id'     => 'reason_show_dialog',
                'title'  =>'仓库授权',
                'width'  => '300',
                'height' => 'auto',
                'url' => U('Setting/Warehouse/warehouseAuth')
            ),
            'search'=>array(
                'form_id'    =>   $id_list['form'],
            ),
        );

        $this->assign('params', json_encode($params));
        $this->assign('datagrid', $datagrid);
        $this->assign("id_list", $id_list);

        $this->display('warehouse_edit');
    }

    public function search($page = 1, $rows = 20, $search = array(), $sort = 'id', $order = 'desc')
    {
        try{
            $data = D('warehouse')->search($page,$rows,$search,$sort,$order);
        }catch(BusinessLogicException $e){
			$data = array('total' => 0, 'rows' => array());
		}catch(\Exception $e){
            $msg = $e->getMessage();
            \Think\Log::write($msg);
            $data = array('total' => 0, 'rows' => array());
        }
        $this->ajaxReturn($data);
    }
    function dialogAddWarehouse()
    {
        try{
            self::getIDList($address_id_list,array('province','city','district','dialog','address_object'),CONTROLLER_NAME,'dialog_add');
            $warehouse_info = array();
            $this->addOrEditShowCommon($this->id_list['add_form'],$warehouse_info,$address_id_list,'add');
        }catch(\Exception $e){
            \Think\Log::write($e->getMessage());
            $this->error('未知错误,请联系管理员');
        }
    }
    function dialogEditWarehouse($id)
    {
        $id = intval($id);
        try{
            self::getIDList($address_id_list,array('province','city','district','dialog','address_object'),CONTROLLER_NAME,'dialog_edit');
            try{
                $warehouse_info = D('Warehouse')->getEditWarehouseInfo($id);
            }catch(BusinessLogicException $e){
                $this->error($e->getMessage());
            }catch(\Exception $e){
                \Think\Log::write($e->getMessage());
                $this->error('未知错误,请联系管理员');
            }
            $this->addOrEditShowCommon($this->id_list['edit_form'],$warehouse_info,$address_id_list,'edit');
        }catch(\Exception $e){
            \Think\Log::write($e->getMessage());
            $this->error('未知错误,请联系管理员');
        }
    }
    private function addOrEditShowCommon($form_id,$warehouse_info,$address_id_list,$type)
    {

        $dialog_list = array(
            'form'=>$form_id,
            'province'=>$address_id_list['province'],
            'city'=>$address_id_list['city'],
            'district'=>$address_id_list['district'],
            'source_js'=>$address_id_list['dialog'],
            'address_object' => $address_id_list['address_object'],
        );
        $this->assign('type',$type);
        $this->assign('warehouse_info',json_encode($warehouse_info));
        $this->assign('dialog_list',$dialog_list);
        $this->assign('dialog_list_json',json_encode($dialog_list));
        $this->display('dialog_warehouse_add_edit');
    }
    public function warehouseAuth()
    {
        $id = I('get.id');
        if(empty($id) || $id == ''){
            return;
        }else{
            $api_key = D('cfg_warehouse')->field('api_key')->where(array('warehouse_id'=>array('eq',$id)))->find();
            $api_key = (array)json_decode($api_key['api_key']);
            $api_key = json_encode($api_key);
            $this->assign('api_key', $api_key);
            $this->display("warehouse_auth");
        }
    }

    function saveWarehouse()
    {
        $arr =  I('','',C('JSON_FILTER'));
        $result['status'] = 0;
        $result['info'] ='success';
        try{
            $result = D('warehouse')->saveWarehouse($arr);
            if($arr['is_disabled'] == 1){
                $res = D('Stock/StockSpec')->enableToDisable($arr['warehouse_id']);
                if($res['status'] == 1){
                    $result['status'] = 2;
                    $result['info'] = $res['msg'];
                    $arr['is_disabled'] = 0;
                    D('warehouse')->saveWarehouse($arr);
                }

            }
        } catch(BusinessLogicException $e){
            
			$result['status'] = 0;
            $result['info'] =$e->getMessage();
		}catch(\Exception $e){
            \Think\Log::write($e->getMessage());
            $result['status'] = 0;
            $result['info'] = self::UNKNOWN_ERROR;
        }
        $this->ajaxReturn($result);
    }
    function saveWarehouseAuth()
    {
        $arr =  I('','',C('JSON_FILTER'));
        $id = I('get.id');
        $data['ext_warehouse_no'] = I('post.warehouseCode');
        $arr = json_encode($arr,JSON_FORCE_OBJECT);
        $data['api_key'] = $arr;
        $result['status'] = 0;
        $result['info'] ='success';
        try{
            $result = D('cfg_warehouse')->where(array('warehouse_id'=>array('eq',$id)))->save($data);
        } catch(BusinessLogicException $e){

            $result['status'] = 0;
            $result['info'] =$e->getMessage();
        }catch(\Exception $e){
            \Think\Log::write($e->getMessage());
            $result['status'] = 0;
            $result['info'] = self::UNKNOWN_ERROR;
        }
        $this->ajaxReturn($result);
    }
    public function getLogisticsWarehouse($id = 0) {
        $warehouse_id = (int)$id;
        try {
            $rs = M()->query("SELECT calw.warehouse_id id, calw.logistics_code,calw.name,GROUP_CONCAT(clw.logistics_name) logistics_name
            from cfg_api_logistics_wms calw LEFT JOIN cfg_logistics_wms clw ON(clw.warehouse_id=calw.warehouse_id AND clw.logistics_code=calw.logistics_code)
            WHERE calw.warehouse_id={$warehouse_id}
            GROUP BY calw.warehouse_id,calw.logistics_code");
        } catch (\Exception $e) {
            \Think\Log::write($e->getMessage());
        }
        echo json_encode($rs);
    }
    //下载物流公司
    public function downloadWarehouseLogistics() {
        $msg    = array("status" => 1, "info" => "下载成功！");
        $warehouseID = I("post.warehouseID");
        $sid = get_sid();
        $uid = get_operator_id();
        try {
            //$Logistics_tb     = M("cfg_api_logistics_wms");
            $WmsManager = ManagerFactory::getManager("Wms");
            $WmsManager-> manual_wms_adapter_get_logistics($sid,$uid,$warehouseID);
        } catch (\PDOException $e) {
            $msg["status"] = 0;
            $msg["info"]   = "未知错误，请联系管理员";
            \Think\Log::write("sql_exception" . $e->getMessage());
        } catch (\Exception $e) {
            $msg["status"] = 0;
            $msg["info"]   = $e->getMessage();
            \Think\Log::write($e->getMessage());
        }
        //$this->ajaxReturn($msg);
    }
    public function UpdateLogisticsList() {
        $i         = I('post.data');
        $rows      = $i['rows'];
        $total     = $i['total'];
        $data      = self::getLogisticsList();
        $tem_array = array();
        if ($total == '' || $total = 0) {
            return;
        }

        $name_array = array();
        foreach ($rows as $row) {
            $tem        = explode(",", $row['logistics_name']);
            $name_array = array_merge($name_array, $tem);

            foreach ($tem as $ntem) {
                $logistics_type = '';
                $length         = count($tem_array);
                for ($j = 0; $j < count($data); $j++) {
                    if ($data[ $j ]['logistics_name'] == $ntem) {
                        $logistics_type = $data[ $j ]['logistics_type'];
                        break;
                    }elseif($ntem == '京邦达'){
                        $logistics_type = '1311';
                    }
                }
                $tem_array[ $length ] = array("id" => $row['id'], "logistics_code" => $row['logistics_code'], "logistics_name" => $ntem, "name" => $row['name'], "logistics_type" => $logistics_type);

            }

        }


        foreach ($name_array as $k => $v) {
            if (!$v)
                unset($name_array[ $k ]);
        }

        if (count($name_array) != count(array_unique($name_array))) {
            $this->ajaxReturn("2");
        }
        $l = count($tem_array);
        for ($n = 0; $n < $l; $n++) {
            if ($tem_array[ $n ]['logistics_name'] == "" || $tem_array[ $n ]['logistics_name'] == null || $tem_array[ $n ]['logistics_type'] == "") {
                unset($tem_array[ $n ]);
            }
        }
        $m     = 0;
        $array = array();
        for ($i = 0; $i < $l; $i++) {
            if (!empty($tem_array[ $i ])) {
                $array[ $m ] = $tem_array[ $i ];
                $m++;
            }
        }
        $tem_array = $array;
        $model = M('cfg_logistics_wms');
        $model->startTrans();
        try {
            $model->where('warehouse_id=' . $rows[0]['id'])->delete();
            for ($i = 0; $i < count($tem_array); $i++) {
                $logistics_id = $model->query("select logistics_id from cfg_logistics where logistics_name= '".$tem_array[ $i ]['logistics_name']."' and logistics_type= ".$tem_array[ $i ]['logistics_type']);
                $logistics_id = empty($logistics_id[0]['logistics_id'])?0:$logistics_id[0]['logistics_id'];
                $sql = "INSERT INTO cfg_logistics_wms(warehouse_id, logistics_code, logistics_name, logistics_type, logistics_id, created) VALUES (" . $tem_array[ $i ]['id'] . ",\"" . $tem_array[ $i ]['logistics_code'] . "\",\"" . $tem_array[ $i ]['logistics_name'] . "\"," . $tem_array[ $i ]['logistics_type'] . ",\"" . $logistics_id . "\",NOW())";
                $model->execute($sql);
            }
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            \Think\Log::write($msg);
            $model->rollback();
            $this->ajaxReturn("1");
        }
        $model->commit();
        $this->ajaxReturn("0");


    }
    public function getLogisticsList() {
        try {
            $model = M('dict_logistics');
            $data  = $model->query('select logistics_type,logistics_name from dict_logistics');
        } catch (\Exception $e) {
            \Think\Log::write($e->getMessage());
        }
        return $data;
    }
	public function address(){
		$parentObject = I('get.parentObject');
		self::getIDList($id_list,array("toolbar", "datagrid"),CONTROLLER_NAME,'address');
		$datagrid=array(
			'id'=>$id_list['datagrid'],
			'style'=>'',
			'class'=>'easyui-datagrid',
			'options'=> array(
					'title'=>'',
					'toolbar' => "#{$id_list['toolbar']}",
					'url'   => U('Home/Index/getWayBillAddress'),
					'fitColumns'=>false,
					'rownumbers'=>true,
					'pagination'=>false,
			),
			'fields'=>get_field("Warehouse", "warehouse_address")
		); 
		 $params = array(
			'datagrid'=>array('id'=>$id_list['datagrid']),
			'url'=>U('Home/Index/getWayBillCount'),
			'parentObject'=>$parentObject?$parentObject:'',
		 );
		
		$this->assign('params', json_encode($params));
		$this->assign('id_list', $id_list);
		$this->assign('datagrid',$datagrid);
		$this->display('warehouse_address');
	
	}
	public function downloadWarehouse(){
		$shop = UtilDB::getCfgRightList(array('shop'));
		$this->assign('shop',$shop['shop']);
		$this->display('downloadWarehouse');
		
	}
	public function downloadWarehouseData(){
		$msg    = array("status" => 1, "info" => "下载成功！");
        $data = I("post.data");
        $sid = get_sid();
        $uid = get_operator_id();
        try {
            //$Logistics_tb     = M("cfg_api_logistics_wms");
            $WmsManager = ManagerFactory::getManager("Alpha");
            $WmsManager-> wms_adapter_get_warehouses($sid,$uid,$data['shop'],$data['warehouse_type'],$data['deptNo']);
        } catch (\PDOException $e) {
            $msg["status"] = 0;
            $msg["info"]   = "未知错误，请联系管理员";
            \Think\Log::write("sql_exception" . $e->getMessage());
        } catch (\Exception $e) {
            $msg["status"] = 0;
            $msg["info"]   = $e->getMessage();
            \Think\Log::write($e->getMessage());
        }
	}
}