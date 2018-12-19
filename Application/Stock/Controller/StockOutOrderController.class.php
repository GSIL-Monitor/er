<?php
/**
 * Created by PhpStorm.
 * User: gaosong
 * Date: 15/9/21
 * Time: 下午2:43
 */
namespace Stock\Controller;

use Common\Common\DatagridExtention;
use Common\Controller\BaseController;
use Think\Model;
//use Common\Common\Factory;
use Common\Common;
use Common\Common\UtilTool;
use Common\Common\UtilDB;
use Think\Exception\BusinessLogicException;
class StockOutOrderController extends BaseController
{
    public function getStockOutOrderList($id = 0,$editDialogId='',$stockManagementObject='')
    {
		$id_ar = array('tool_bar','add','edit','select','form','tab_container','hidden_flag','datagrid','delete','reset_button','outmanage','return');
        if(0 == $id){
            $idList = DatagridExtention::getRichDatagrid('StockOut','StockOutOrder','',"add",$id_ar);
            $prefix = 'add_';
        }else{
            $idList = DatagridExtention::getRichDatagrid('StockOut','StockOutOrder','',"edit",$id_ar);
            $prefix ='edit_';
        }
        //定义出库单调用的工具名称
        $stockout_order_tool = $prefix.CONTROLLER_NAME;
        $fields = $idList['datagrid']['fields'];
        $fields['总货款']['editor'] =  '{type:"numberbox",options:{required:true,value:0,min:0,precision:4,readonly:true,disabled:true}}';
       // $fields['出库数量']['editor'] = '{type:"numberbox",options:{required:true,value:0,min:0,precision:'.UtilDB::getnumber().',onChange:function(newValue,oldValue){ var that = this; '.$stockout_order_tool.'.setNum(newValue,oldValue,that);}}}';
        $fields['单价']['editor'] = '{type:"numberbox",options:{precision:4,onChange:function(newValue,oldValue){ var that = this; '.$stockout_order_tool.'.setPrice(newValue,oldValue,that);}}}';
        $fields['备注']['editor'] =   '{type:"textbox"}';
        $idList['datagrid']['fields'] = $fields;
        //可编辑列的编辑器的类型是 numberbox 还是textbox
        $fields_to_editor_type = array('remark'=>'textbox','total_amount'=>'numberbox','num'=>'numberbox','price'=>'numberbox');

        $params = array();
        $params['select']['id'] = $idList['id_list']['select'];
		$params['return']['title'] = '选择采购退货单';
		$params['return']['url'] = U('Stock/StockOutOrder/purchase_return');
		$params['return']['id'] =  $idList['id_list']['return'];
	
        $params['datagrid'] = array();
        $params['datagrid']['url'] = U('GoodsGoods/getGoodsList', array('grid' => 'datagrid'));
        $params['datagrid']['id'] = $idList['id_list']['datagrid'];
        $params['delete']['url'] = U('Goods/GoodsGoods/delGoods');
        $params['form']['id'] = $prefix.'stockoutorder_form';
        $params['id_list'] = $idList['id_list'];
        $idList['id_list']['tool_bar_form'] = $prefix.'stockoutorder_form';
        $params['stockout_order_tool'] = $stockout_order_tool;
        $params['show_type'] = "{$prefix}";
        $params['form']['url'] = U('Stock/StockOutOrder/saveOrder');

        $params['fields_to_editor_type'] = $fields_to_editor_type;
        $params['stockout_management_info']=array(
            'editDialogId'=>$editDialogId,
            'stockManagementObject'=>$stockManagementObject
        ) ;
        
        $data = "'none'";
        $form = "'none'";
        if ($id != 0) {$this->loadFormData($id,$form,$data);}
        $idList['datagrid']['options']['pagination'] = false;
        $this->assign("datagrid", $idList['datagrid']);

        $logistics_default['0'] = array('id' => '0', 'name' => '无');
        $tmp = Common\UtilDB::getCfgRightList(array('logistics','unit','warehouse'),array('warehouse'=>array('is_disabled'=>0,'type'=>1)));
        $logistics_array = array_merge($logistics_default, $tmp['logistics']);

        $this->assign('unit',json_encode($tmp['unit']));
        $this->assign('list_logistics', $logistics_array);
        $this->assign('warehouse_list', $tmp['warehouse']);
        $this->assign("params", json_encode($params));
        $this->assign("id_list", $idList['id_list']);
        $this->assign('data', $data);
        $this->assign('form', $form);
        $this->assign('prefix',$prefix);
        $this->assign('stockout_order_tool',$stockout_order_tool);
        $this->display('show');
    }

    public function loadFormData(&$id,&$form,&$data){
        $data = $this->loadSelectedData($id);
        if ($data == "0") {
            //数据库错误
            $data = "'err'";
            $form = "'db_err'";
        } elseif ($data == '1') {
            //状态类型错误
            $data = "'err'";
            $form = "'st_err'";
        }else{
            $form_data = $data[0];
            $form = array(
                'search[stockout_no]' => $form_data['stockout_no'],
                'search[warehouse_id]' => $form_data['warehouse_id'],
                'search[remark]' => $form_data['form_remark'],
                'search[logistics_id]' => $form_data['logistics_id'],
                'search[logistics_no]' => $form_data['logistics_no'],
                'search[post_fee]' => $form_data['post_cost'],
                'search[stockout_type]' => $form_data['src_order_type'],
				'search[src_order_no]' => $form_data['src_order_no'],
				'search[src_order_id]' => $form_data['src_order_id'],
            );
            $data = json_encode($data);
            $form = json_encode($form);
        }
    }

    public function loadSelectedData($id)
    {
       return D('StockOutOrder')->loadSelectedData($id);
    }

    public function saveOrder(){
        try{
            $Params = I("",'',C('JSON_FILTER'));
            $rows = $Params['rows'];
            $search = $Params['search'];
            $result['info'] = "";
            $result['status'] = '2';
            $stockout_no = $search['stockout_no'];
           /* $getwarehouse_fields = D('Setting/Warehouse')->getField('warehouse_id');
            $search['warehouse_id'] = $getwarehouse_fields[0];*/

            if($stockout_no != null){
                $update = $rows['update_spec'];
                $len = count($update);
                for($i = 0;$i<$len;$i++){
                    if($update[$i]['num']<=0){
                        $result['status'] = 0;
                        $result['info'] = "出库数量必须为正数！";
                        $this->ajaxReturn($result);
                    }
                }
                $add = $rows['add_spec'];
                $len = count($add);
                for($i = 0;$i<$len;$i++){
                    if($add[$i]['num']<=0){
                        $result['status'] = 0;
                        $result['msg'] = "出库数量必须为正数！";
                        $this->ajaxReturn($result);
                    }
                }
            }else{
                $len = count($rows);
                for($i = 0;$i<$len;$i++){
                    if($rows[$i]['num']<=0){
                        $result['status'] = 0;
                        $result['info'] = "出库数量必须为正数！";
                        $this->ajaxReturn($result);
                    }
                }
            }

            if ($stockout_no != null) {
                //编辑出库单
                $result = D('StockOutOrder')->updataStockOrder($search,$rows);
                //$this->ajaxReturn(json_encode($result),'EVAL');
            }else{
                $stockout_id = '';
                $result = D('StockOutOrder')->addStockOrder($search,$rows,$stockout_id);
                //$this->ajaxReturn(json_encode($result),'EVAL');
            }
        }catch(\Exception $e){
            \Think\Log::write($e->getMessage());
            $result = array('status'=>0,'info'=>self::UNKNOWN_ERROR,'data'=>array());
            $this->ajaxReturn(json_encode($result),'EVAL');
        }

        try{
            //自动提交
            if($stockout_no == null){
                $stockout_auto_commit = get_config_value('stockout_auto_commit',0);
                if($stockout_auto_commit == 1){
                    //D('StockOutOrder')->submitStockOutOrder($stockout_id);
                    $result['stockout_auto_commit_cfg'] = 1;
                    D('StockOutOrder')->checkStockout($stockout_id);
                }
            }
        }catch (BusinessLogicException $e){
            $msg = $e->getMessage();
            $result['status']=3;
            $result['msg']='自动提交失败，失败原因：'.$msg;
        }catch(\Exception $e){
            \Think\Log::write(CONTROLLER_NAME.'-saveOrder-'.$e->getMessage());
            $result['status']=3;
            $result['msg']=self::UNKNOWN_ERROR;
        }
        $this->ajaxReturn(json_encode($result),'EVAL');
    }
	 public function purchase_return(){  //退货单添加
        try{
            $id_list = array();
            $need_ids = array('form','toolbar','tab_container','hidden_flag','datagrid','more_content','edit','hidden_flag','delete');
            $this->getIDList($id_list,$need_ids,'return','');
            $datagrid = array(
                'id'=>$id_list['datagrid'],
                'options'=>array(
                    'url'=>U('Stock/StockOutOrder/search_return'),
                    'toolbar'=>$id_list['toolbar'],
                    'fitColumns'=>false,
                    "rownumbers" => true,
                    "pagination" => true,
                    "method"     => "post",
                ),
                'fields'=>get_field('Purchase/PurchaseReturn','purchase_reurn_management'),
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
              array('url'=>U('Purchase/PurchaseCommon/showTabsView',array('tabs'=>"purchase_return_detail")).'?prefix=purchasereturnout&tab=purchase_return_detail&app=Purchase/PurchaseReturn',"id"=>$id_list['tab_container'],"title"=>"退货单详情"),
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
            $stockout_order_tool = CONTROLLER_NAME;
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
        $this->assign('stockout_order_tool',   $stockout_order_tool);
        $this->display('purchase_return_out');
    }
	public function search_return($page = 1,$row = 20,$search = array(),$sort = 'id',$order = 'desc'){
		try{
			$type = 'return';
            $result = D('Purchase/PurchaseReturn')->search($page,$row,$search,$sort,$order,$type);
        }catch(\Exception $e){
            \Think\Log::write($e->getMessage());
            $result = array('rows'=>array(),'total'=>1);
        }
        $this->ajaxReturn($result);
	}

}