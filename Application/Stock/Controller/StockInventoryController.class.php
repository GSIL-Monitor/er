<?php
/**
 * Created by PhpStorm.
 * User: liulixin
 * Date: 16/7/18
 * Time: ����9:30
 */
namespace Stock\Controller;

use Common\Controller\BaseController;
use Think\Model;
use Common\Common;
use Think\Exception\BusinessLogicException;
use Common\Common\UtilDB;



class StockInventoryController extends BaseController
{
	
	
    public function getStockInventoryList($id='' ,$editDialogId='',$stockManagementObject='',$type='')
    {
        try{

            if($type!="add")
            {
                $prefix = "old";
                $show_type = "add";
            }else{
                $prefix = "new";
                $show_type = "dialog";
            }

            $datagrid_id        = strtolower($prefix . CONTROLLER_NAME . '_' . ACTION_NAME . '_datagrid');

            $id_list            = self::getIDList($id_list,array('tool_bar','add','form','select','warehouse','reset_button','order_type','src_order','invenmanage','saomiao'),$prefix);
            $stockInven_tool    = $prefix.CONTROLLER_NAME;
            $fields             = get_field("StockInventory","stockinventory");
			$fields['备注(可编辑)']['editor'] =   '{type:"textbox"}';
            $datagrid = array(
                'id' => $datagrid_id,
                'options' => array(
                    'title' => '',
                    'url' => '',
                    'toolbar' => "#{$id_list['tool_bar']}",
                    'fitColumns' => false,
                    'pagination' => false,
                ),
                'fields' => $fields,
                'class' => 'easyui-datagrid',
            );

            $params = array();
            $params['select']       = array('id'=>$id_list['select']);
			$params['saomiao'] = array(
				'id'=>$id_list['saomiao'],
				'url' => U('SalesStockoutExamine/showGoodsList'), 
				'title' => '条码选择货品',
			);
            $params['datagrid']     = array('id'=>$datagrid_id);
            $params['form']         = array(
                'id'    =>$id_list['form'],
                'url'   =>U('Stock/StockInventory/saveOrder')."?type={$show_type}"
            );

            $params['id_list']          = $id_list;
            $params['show_type']        = $show_type;
            $params['stockInven_tool']  = $stockInven_tool;
            $params['prefix']           = $prefix;


            $params['stockin_management_info']=array(
                'editDialogId'=>$editDialogId,
                'stockManagementObject'=>$stockManagementObject
            ) ;

            $list = UtilDB::getCfgRightList(array('warehouse'));
            $this->assign('list',$list);
            $this->assign('show_type',       $show_type);
            $this->assign('stockInven_tool', $stockInven_tool);

            $this->assign("params",          json_encode($params));
            $this->assign("id_list",         $id_list);
            $this->assign("datagrid",        $datagrid);
            $this->assign("prefix",          $prefix);
            $data = "null";
            $data_form = "null";
            if ($type == "add") {
                $this->loadFormData($id,$data,$data_form);
            }
            $this->assign('data', $data);
            $this->assign('data_form', $data_form);
        }catch(\PDOException $e){
            \Think\Log::write($e->getMessage()."-loadSelectedData-");
        }catch(\Exception $e){
            \Think\Log::write($e->getMessage()."-loadSelectedData-");
        }
        $this->display('InventoryOrder');
    }

    public function loadFormData(&$id,&$data,&$data_form){
		try{
			$data = $this->loadSelectedData($id);
				if(!empty($data)){
					$info = $data[0];
					$data_form = array(
						'search[warehouse_id]' => $info['warehouse_id'],               
						'search[remark]' => $info['form_remark'],
						'search[pd_mode]' => $info['pd_mode'],
						'search[stockinven_no]' => $info['pd_no'],
				
					);
				}
				$data = json_encode($data);
				$data_form = json_encode($data_form);
			
		}catch(\Exception $e){
            \Think\Log::write($e->getMessage());
			$data = $e->getMessage();
        }
    }

    public function loadSelectedData($id)
    {
	try{
       return D('StockInventory')->loadSelectedData($id);
	}catch(BusinessLogicException $e){
            return null;
    }catch (\Exception $e) {
		$msg = $e->getMessage();
		\Think\Log::write($msg);
		return null;
		}
    }

   public function saveOrder(){
		try{
	   $Params = I("",'',C('JSON_FILTER'));
        $rows = $Params['rows'];
        $search = $Params['search'];
        $result['info'] = "";
		$stockinven_no = $search['stockinven_no'];
       /*
            $len = count($rows);
            for($i = 0;$i<$len;$i++){
                if($rows[$i]['pd_num']<=0){
                    $result['status'] = 0;
                    $result['info'] = "实际盘点量必须为正数";
                    $this->ajaxReturn($result);
                }
            }
    */
		
		if (!empty($stockinven_no)) {
            //编辑盘点单
            $result = D('StockInventory')->updataStockInven($search,$rows);
            //$this->ajaxReturn(json_encode($result),'EVAL');
        }else{
            $stockinven_id = '';
            $result = D('StockInventory')->addStockInven($search,$rows,$stockinven_id);
            //$this->ajaxReturn(json_encode($result),'EVAL');
        }
     

		} catch(BusinessLogicException $e){
            $result['status'] = 1;
            $result['info'] = $e->getMessage();
			$this->ajaxReturn(json_encode($result),'EVAL');
        } catch (\Exception $e) {
			$msg = $e->getMessage();
			\Think\Log::write($msg);
			$result['info'] = self::UNKNOWN_ERROR;
			$result['status'] = 1;
			$this->ajaxReturn(json_encode($result),'EVAL');
			
		}
       try{
           //自动提交
           if(empty($stockinven_no)){
               $stockinventory_auto_commit = get_config_value('stockinventory_auto_commit',0);
               if($stockinventory_auto_commit == 1){
                   $result['stockinventory_auto_commit'] = 1;
                   D('Stock/StockSpec')->submitPd($stockinven_id);
               }
           }
       }catch(BusinessLogicException $e){
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
	public function getBarcodeInfo(){
		try{
			$result = array('status'=>0,'info'=>'');
			$barcode = I('post.','',C('JSON_FILTER'));
			$where = array("gbd.barcode"=>$barcode['barcode']);
			$point_number=get_config_value('point_number',0);
			$expect_num = "CAST(ss.stock_num AS DECIMAL(19,".$point_number.")) stock_num";
			$num = "CAST(1 AS DECIMAL(19,".$point_number.")) AS pd_num";
			$fields = array("gs.spec_no","gs.unit unit_id", "gs.spec_id as id","gs.spec_id","gs.spec_name"," gs.spec_code"," gbd.barcode",
                        "gg.short_name","gg.spec_count","gg.goods_no","gg.goods_name", "gg.goods_id","gg.brand_id", 
                        "gb.brand_name",
                        "cgu.name as unit_name",
                         $expect_num, $num,"CAST(IFNULL(ss.cost_price,0) AS DECIMAL(19,4)) AS market_price",
                        "(CASE  WHEN ss.last_position_id THEN ss.last_position_id ELSE -{$barcode['warehouse_id']} END) AS position_id",
                        "(CASE  WHEN ss.last_position_id THEN cwp.position_no  ELSE cwp2.position_no END) AS position_no");
			$data = D('Goods/GoodsBarcode')->alias('gbd')->fetchSql(false)->field($fields)->join("left join goods_spec gs on gs.spec_id = gbd.target_id")->join("LEFT JOIN goods_goods gg ON(gs.goods_id=gg.goods_id)")->join("LEFT JOIN goods_brand gb ON(gg.brand_id=gb.brand_id)")->join("inner JOIN stock_spec ss ON(gs.spec_id=ss.spec_id AND ss.warehouse_id={$barcode['warehouse_id']})")->join("LEFT JOIN stock_spec_position ssp ON(gs.spec_id=ssp.spec_id AND ssp.warehouse_id={$barcode['warehouse_id']})")->join("LEFT JOIN cfg_goods_unit cgu ON(gs.unit = cgu.rec_id)")->join("LEFT JOIN cfg_warehouse_position cwp ON(ss.last_position_id = cwp.rec_id)")->join("LEFT JOIN cfg_warehouse_position cwp2 ON(cwp2.rec_id = -{$barcode['warehouse_id']})")->where($where)->select();
			if($data == '' || empty($data)){
				$result = array('status'=>1,'info'=>'没有该条形码或该仓库没有该条形码的货品');
			}else{
				$result = array('status'=>0,'info'=>$data);
			}
		}catch(\Exception $e){
            \Think\Log::write($e->getMessage());
            $result = array('status'=>1,'info'=>self::UNKNOWN_ERROR);
        }
		$this->ajaxReturn($result);
	}
	
}