<?php
/**
 * Created by PhpStorm.
 * User: gaosong
 * Date: 15/10/8
 * Time: 下午6:45
 */
namespace Stock\Controller;
use Common\Common\UtilDB;
use Common\Controller\BaseController;
use Common\Common\DatagridExtention;
use Common\Common\UtilTool;
use Common\Common\ExcelTool;
use Think\Exception\BusinessLogicException;
use Think\Log;
use Platform\Common\ManagerFactory;

/**
 * 库存管理类
 * @package Stock\Controller
 */
class StockManagementController extends BaseController {
    /**
     *初始化库存管理界面
     */
    public function getStockSpec(){
        //'fileForm'      => 'goods_goods_file_form',
        //       'fileDialog'    => 'goods_goods_file_dialog'
        $id_list =  DatagridExtention::getIdList(array('tool_bar','add','edit','adjust','stock_alarm','select','form','tab_container','more_content','hidden_flag','datagrid','delete','file_form','file_dialog','import_stock','import_price','form_price','del_stock_goods','delstockgoods','select_shop'));
        $fields = D('Setting/UserData')->getDatagridField('Stock/StockManagement','stockmanagement');
        $datagrid=array(
                'id' => $id_list['datagrid'],
                'options' => array(
                        'title' => '',
                        'url' => U("StockManagement/loadDataByCondition"),
                        'toolbar' => "#{$id_list['tool_bar']}",
                    'frozenColumns'=>D('Setting/UserData')->getDatagridField('Stock/StockManagement','stockmanagement',1),
                        'fitColumns'   => false,
						'singleSelect' => false,
						'ctrlSelect'   => true
                    ),
                'fields' => $fields,
            );
		 //$checkbox=array('field' => 'ck','checkbox' => true);
         //array_unshift($datagrid['fields'],$checkbox);
        $arr_tabs = array(
            array('url'=>U('StockCommon/showTabsView',array('tabs'=>"executing_sale_trade")).'?prefix=stockmanagement&tab=executing_sale_trade',"id"=>$id_list['tab_container'],"title"=>"执行中的销售"),
            array('url'=>U('StockCommon/showTabsView',array('tabs'=>"warehouse_stock_detail")).'?prefix=stockmanagement&tab=warehouse_stock_detail',"id"=>$id_list['tab_container'],"title"=>"库存货品详情"),
            array('url'=>U('StockCommon/showTabsView',array('tabs'=>"warehouse_stock_detail")).'?prefix=stockmanagement&tab=stock_spec_log',"id"=>$id_list['tab_container'],"title"=>"库存货品日志"),
        );
		$operator_id = get_operator_id();
		$right = UtilDB::actionRights(40);
        $flag=D('Setting/Flag')->getFlagData(0);
        $params = array(
            'edit'=>array(
                'id'     =>  $id_list['edit'],
                'title'  =>  '盘点库存',
                'url'    =>  U('StockManagement/checkStock'),
                'width'  =>  '600',
                'height' =>  '400',
            ),
            'adjust'=>array(
                'id'     =>  $id_list['adjust'],
                'title'  =>  '调整成本价',
                'url'    =>  U('StockManagement/adjustPrice'),
                'width'  =>  '650',
                'height' =>  '500',
                'ismax'  => false
            ),
            'stock_alarm'=>array(
                'id'     =>  'reason_show_dialog',
                'title'  =>  '设置库存预警',
                'url'    =>  U('StockManagement/settingStockAlarm'),
                'width'  =>  '700',
                'height' =>  '500',
                'ismax'  => false
            ),
            'datagrid'=>array(
                'id'    =>    $id_list['datagrid'],
            ),
            'tabs'=>array(
                'id'    =>    $id_list['tab_container'],
                'url'   =>    U('StockCommon/showTabDatagridData'),
            ),
            'form'=>array(
                'id'    =>   'stock_search_form',
            ),
            'search'=>array(
                'form_id'    =>		'stock_search_form',
                'more_content' => $id_list['more_content'],
                'hidden_flag' => $id_list['hidden_flag'],
            ),
            'del_stock_goods'=>array(
                'id'=>$id_list['del_stock_goods'],
                'url'=>U('Stock/StockManagement/delStockGoods'),
                'title'=>'删除单品',
                'width'=>250,
                'height'=>155,
                'ismax'=>false,
            ),
            'flag'=>array(
                'set_flag'=>$id_list['set_flag'],
                'url'=>U('Setting/Flag/flag').'?flagClass=7',
                'json_flag'=>$flag['json'],
                'list_flag'=>$flag['list'],
                'dialog'=>array(
                    'id'=>'flag_set_dialog',
                    'url'=>U('Setting/Flag/setFlag').'?flagClass=7',
                    'title'=>'颜色标记',
                    'height'=>'250'
                    ),
                'search_flag'=>$id_list['search_flag']
            ),
            'select_shop'=>array(
                'id'     =>  $id_list['select_shop'],
                'title'  =>  '选择店铺',
                'url'    =>  U('StockManagement/getShopList'),
                'width'  =>  '280',
                'height' =>  '120',
                'ismax'  =>  false,
            ),

        );
        $stock_alarm_cfg=D('Setting/Flag')->getCfgFlags(
            'bg_color,font_color,font_name',
            array('flag_name'=>array('eq','警戒库存'),'flag_class'=>array('eq',7))
        );
        $stock_alarm_color='background-color:' . $stock_alarm_cfg['bg_color'] . ';color:' . $stock_alarm_cfg['font_color'] . ';font-family:' . $stock_alarm_cfg['font_name'] . ';';
        if(empty($stock_alarm_cfg)){$stock_alarm_color='background-color:#ee1d24;color:#fff;font-family:SimSun;';}
		$faq_url = C('faq_url');
		$this->assign('faq_url',$faq_url['stock_managent']);
        $this->assign("params",json_encode($params));
        $this->assign('arr_tabs', json_encode($arr_tabs));
        $this->assign('datagrid', $datagrid);
        $this->assign("id_list",$id_list);
        $this->assign("form_id",$id_list['delstockgoods']);
		$this->assign('right',json_encode($right));
		$this->assign('operator_id',$operator_id);
        $stock_spec_num = D('StockManagement')->getStockSpecCount();
        $list = UtilDB::getCfgRightList(array('brand','warehouse'));
        $brand_default[0] = array('id' => 'all', 'name' => '全部');
        $brand_array = array_merge($brand_default, $list['brand']);
        $this->assign('brand_array', $brand_array);
        $this->assign('warehouse_list', $list['warehouse']);
        $this->assign('stock_num',$stock_spec_num);
        $this->assign('stock_alarm_color',$stock_alarm_color);
        $this->display('show');
    }

    /**
     * 根据条件获取数据
     * @param int $page
     * @param int $rows
     * @param array $search
     * @param string $sort
     * @param string $order
     */
    public function loadDataByCondition($page = 1, $rows = 20, $search = array(), $sort = 'id', $order = 'desc')
    {
        $this->ajaxReturn(D('StockSpec')->searchStockSpec($page, $rows, $search, $sort, $order));
    }

    /**
     * 盘点库存
     * @param int $id
     */
    public function checkStock($id=0,$warehouse_id = 0){
        if(IS_POST)
        {
            try{
                $result = array('info'=>'成功','status'=>0,'data'=>array());
                $updata = I('post.');
                D('Stock/StockSpec')->quickCheck($updata);
            }catch(BusinessLogicException $e){
                $msg = $e->getMessage();
                $result = array('info'=>$msg,'status'=>1);
            }catch(\PDOException $e){
                \Think\Log::write($e->getMessage());
                $result = array('info'=>self::UNKNOWN_ERROR,'status'=>1);
            }catch(\Exception $e){
                \Think\Log::write($e->getMessage());
                $result = array('info'=>self::UNKNOWN_ERROR,'status'=>1);
            }
            $this -> ajaxReturn($result);

        }
        else
        {
            try{
                $data = D('StockManagement')->loadSelectedData($id);
                $this->assign('data',json_encode($data['stock_data']));
                $datagrid="
				{field:'position_no',title:'货位',required: 'true',width:100,align:'center'},
				{field:'stock_num',title:'库存数量',required: 'true',width:100,align:'center'},
				{field:'new_stock_num',title:'实际库存',required: 'true',width:100,editor:{type:'numberbox',options:{precision:".get_config_value('point_number',0)."}},align:'center'},
				{field:'goods_no',title:'货品编号',required: 'true',width:100,align:'center'},
				{field:'spec_no',title:'商家编码',required: 'true',width:100,align:'center'},
				{field:'goods_name',title:'货品名称',required: 'true',width:160,align:'center'},
				";
                $datagrid_checkStock=$datagrid;
				foreach($data['warehouse_info'] as $k=>$v){
					if($v['id'] == $warehouse_id){
						$select_warehouse = $data['warehouse_info'][$k];
						unset($data['warehouse_info'][$k]);
						array_unshift($data['warehouse_info'],$select_warehouse);
					} 
				}
                $this->assign('datagrid_checkStock', $datagrid_checkStock);
                $this->assign('spec_id', $id);
				$this->assign('warehouse_id', $warehouse_id);
                $this->assign('warehouse_list', $data['warehouse_info']);
                $this->display('stockmanagement_check');
            }catch (BusinessLogicException $e){
                $this->assign('message',$e->getMessage());
                $this->display('Common@Exception:dialog');
                exit();
            }catch (\Exception $e){
                $this->assign('message',$e->getMessage());
                $this->display('Common@Exception:dialog');
                exit();
            }
        }
    }
    public function uploadExcel()
    {
        if(!self::ALLOW_EXPORT){
            $res["status"] = 1;
            $res["msg"]   = self::EXPORT_MSG;
            $this->ajaxReturn(json_encode($res), "EVAL");
        }
        //获取表格相关信息
        $result = array(
            'status' => 0,
            'data'=>array(),
            'msg'=>'成功'
        );
        $file = $_FILES["file"]["tmp_name"];
        $name = $_FILES["file"]["name"];

        try {
            //将表格读取为数据
            $excelData = UtilTool::Excel2Arr($name, $file, "StockSpecImport");
        } catch(BusinessLogicException $e){
            $res["status"] = 1;
            $res["msg"]   = $e->getMessage();
            $this->ajaxReturn(json_encode($res),'EVAL');
        }catch (\Exception $e) {
            \Think\Log::write($e->getMessage());
            $res["status"] = 1;
            $res["msg"]   = "文件错误，无法读取";
            $this->ajaxReturn(json_encode($res),'EVAL');
        }
        try {
            $model = D('Stock/StockManagement');
            $fail_result = array();
            $data = array();
            $merchant_no =array();
            foreach ($excelData as $sheet) {
				$field_name_list = $sheet[0];
				if($field_name_list[0] != '商家编码' || $field_name_list[1] != '仓库名称' || $field_name_list[2] != '库存数量' || $field_name_list[3] != '成本价' || $field_name_list[4] !='货位' ){
					SE('导入模板不正确，请先下载模板');
				}
                for ($k = 1; $k < count($sheet); $k++) {
                    $row = $sheet[ $k ];
                    //分类存储数据
                    $i                                  = 0;
                    $line = $k+1;
                    $is_full_row = 1; //1  标示为空行,需要屏蔽

                    $temp_data = array(
                        "spec_no"           => trim($row[$i]),//商家编码
                        'warehouse_name'    => trim($row[++$i]),//仓库名称
                        'num'               => trim($row[++$i]),//仓库名称
                        'price'             => trim($row[++$i]),//仓库名称
                        'position_no'       => trim($row[++$i]),//货位编号
//                         "num"               => ((!empty(trim($row[++$i])) && (float)trim($row[++$i]) ==0)|| ( empty(trim($row[++$i]) && trim($row[++$i])!=0)))?'null':(float)trim($row[++$i]),//库存数量
//                         "price"             => ((!empty(trim($row[++$i])) && (float)trim($row[++$i]) ==0)|| ( empty(trim($row[++$i]) && trim($row[++$i])!=0)))?'null':(float)trim($row[++$i]),//成本价
                        'line'              => trim($line),//行号
                        'status'            => 0,
                        'message'           =>'',
                        'result'            =>'成功'
                    );
                    if(isset($merchant_no["{$temp_data['warehouse_name']}"]["{$temp_data['spec_no']}"])){
                        $merchant_no["{$temp_data['warehouse_name']}"]["{$temp_data['spec_no']}"] = false;
                    }else{
                        $merchant_no["{$temp_data['warehouse_name']}"]["{$temp_data['spec_no']}"] = true;
                    }
                    foreach($temp_data as $temp_key => $temp_value){
                        if(!empty($temp_value)&& $temp_key!='line'&& $temp_key!='message'&& $temp_key!='result'&& $temp_key!='status'){
                            $is_full_row = 0;
                        }
                        /* if($temp_key == 'num' && !check_regex('positive_number',$temp_value))
                        {
                            $temp_data["status"] = 1;
                            $temp_data['message'] = "库存不合法dfsdfsdf";
                            $temp_data['num'] = (float)$temp_value;
                        } */
                    }

                    if($is_full_row == 0){
                        $data[] = $temp_data;
                    }

                }
            }
			
			
            //筛选重复的商家编码
            foreach($data as $key => $value)
            {
                if($merchant_no["{$value['warehouse_name']}"]["{$value['spec_no']}"] == false){
                    $data[$key]['status'] = 1;
                    $data[$key]['message'] ='导入仓库['.$value['warehouse_name'].']商家编码重复';
                    $data[$key]['result'] ='失败';
                }
            }
            if(empty($data)){
                E('读取导入的数据失败!');
            }
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            \Think\Log::write(__CONTROLLER__.'--uploadExcel--'.$msg);
            $result['status'] = 1;
            $result['msg'] =$msg;
        }
        $sql_drop = "CREATE TEMPORARY TABLE IF NOT EXISTS  `tmp_import_detail` (`rec_id` INT(11) NOT NULL AUTO_INCREMENT,`spec_no` varchar(40),`position_no` varchar(40) DEFAULT '',`position_id` int(11),`warehouse_name` varchar(64) ,`stock_num` decimal(19,4),`num` decimal(19,4),`price` decimal(19,4),`warehouse_id` smallint(6),`spec_id` int(11),`cost_price` decimal(19,4),`status` TINYINT,`line` SMALLINT,`message` VARCHAR(60),`result` VARCHAR(30),PRIMARY KEY(`rec_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        $model->execute($sql_drop);
        $model->importStockSpec($data,$result);
        $model->execute("DELETE FROM tmp_import_detail");




        $this->ajaxReturn(json_encode($result),'EVAL');
    }

	public function importPrice(){
        if(!self::ALLOW_EXPORT){
            $res["status"] = 1;
            $res["msg"]   = self::EXPORT_MSG;
            $this->ajaxReturn(json_encode($res), "EVAL");
        }
        $result = array('status'=>0,'msg'=>'成功','data'=>array());
		$file = $_FILES['file']['tmp_name'];
		$name = $_FILES['file']['name'];
		try{
			$excelData = UtilTool::Excel2Arr($name,$file,'StockSpecImport');
			  
		}catch(\Exception $e){
			$result['msg'] = "文件错误，无法读取";
			$result['status'] = 1;
			\Think\Log::write($e->getMessage());
			$this->ajaxReturn(json_encode($result),'EVAL');
		}
		try{
			$model = D('Stock/StockManagement');
			$data = array();
			$exist = array();
			foreach($excelData as $info){
				$field_name_list = $info[0];
				if($field_name_list[0] != '商家编码' || $field_name_list[1] != '仓库名称' || $field_name_list[2] != '成本价'){
					SE('导入模板不正确，请先下载模板');
				}
				for($f = 1;$f < count($info); $f++){
					
					$row = $info[$f];
					$i = 0;
					$line = $f+1;
					$is_full_row = 1;
					$temp_data = array(
						'spec_no' => trim($row[$i]),
						'warehouse_name' => trim($row[++$i]),
						'adjust_price' =>trim($row[++$i]),
						 'line'              => trim($line),//行号
                        'status'            => 0,
                        'message'           =>'',
                        'result'            =>'成功'
					);
					 if(isset($exist["{$temp_data['warehouse_name']}"]["{$temp_data['spec_no']}"])){
                        $exist["{$temp_data['warehouse_name']}"]["{$temp_data['spec_no']}"] = false;
                    }else{
                        $exist["{$temp_data['warehouse_name']}"]["{$temp_data['spec_no']}"] = true;
                    }
					foreach($temp_data as $k=>$v){
						if(!empty($v) && $k!='line' && $k!='status' && $k!='message' && $k!='result'){
							
							$is_full_row = 0;
						
						}
					}
					if($is_full_row == 0){
						$data[] = $temp_data;
					}
				}
			}
				foreach($data as $key => $value){
					if(empty($value['adjust_price'])){
						$data[$key]['status'] = 1;
						$data[$key]['message'] ='成本价不存在';
						$data[$key]['result'] ='失败';
					}
					if(empty($value['warehouse_name']) && empty($value['spec_no'])){
						$data[$key]['status'] = 1;
						$data[$key]['message'] ='仓库，商家编码不存在';
						$data[$key]['result'] ='失败';
					}elseif($exist["{$value['warehouse_name']}"]["{$value['spec_no']}"] == false){
						if(empty($value['warehouse_name']) && !empty($value['spec_no'])){
							$data[$key]['status'] = 1;
							$data[$key]['message'] ='仓库不存在';
							$data[$key]['result'] ='失败';
						}elseif(!empty($value['warehouse_name']) && empty($value['spec_no'])){
							$data[$key]['status'] = 1;
							$data[$key]['message'] ='商家编码不存在';
							$data[$key]['result'] ='失败';
						}elseif(empty($value['warehouse_name']) && empty($value['spec_no'])){
							$data[$key]['status'] = 1;
							$data[$key]['message'] ='仓库，商家编码不存在';
							$data[$key]['result'] ='失败';
						}else{
							$data[$key]['status'] = 1;
							$data[$key]['message'] ='导入仓库['.$value['warehouse_name'].']商家编码重复';
							$data[$key]['result'] ='失败';
						}	
					}
				}
				if(empty($data)){
					E('数据读取错误');
				}
			
			
		}catch(\Exception $e){
			$msg = $e->getMessage();
			\Think\Log::write(__CONTROLLER__.'--importPrice--'.$msg);
			 $result['status'] = 1;
            $result['msg'] =$msg;
			$this->ajaxReturn(json_encode($result),'EVAL');
		}
		$sql = "CREATE TEMPORARY TABLE IF NOT EXISTS  `tmp_import_detail` (`rec_id` INT(11) NOT NULL AUTO_INCREMENT,`spec_no` varchar(40),`warehouse_name` varchar(64) ,`stock_num` decimal(19,4),`num` decimal(19,4),`price` decimal(19,4),`warehouse_id` smallint(6),`spec_id` int(11),`adjust_price` decimal(19,4),`remark` varchar(60),`status` TINYINT,`line` SMALLINT,`message` VARCHAR(60),`result` VARCHAR(30),PRIMARY KEY(`rec_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        $model->execute($sql);
		$model->importPrice($data,$result);
		$model->execute("delete from tmp_import_detail");
		 $this->ajaxReturn(json_encode($result),'EVAL');
		
	}
	
	
    public function getCheckData(){
        $check_info = I('post.');
        $data = array();
        try{
            $data = D('Stock/StockSpec')->getCheckData($check_info);
        }catch (\Think\Exception $e){
            $msg = $e->getMessage();
            $data = array();
        }catch(\Exception $e){
            $msg = $e->getMessage();
            \Think\Log::write(__CONTROLLER__.'-getCheckData-'.$msg);
            $data = array();
        }
        $this->ajaxReturn(array('total'=>1,rows=>$data));
    }
    public function downloadTemplet(){
        $file_name = "库存导入模板.xls";
        $file_sub_path = APP_PATH."Runtime/File/";
        try{
            ExcelTool::downloadTemplet($file_name,$file_sub_path);
        } catch (BusinessLogicException $e){
            Log::write($e->getMessage());
            echo '对不起，模板不存在，下载失败！';
        } catch (\Exception $e) {
            Log::write($e->getMessage());
            echo parent::UNKNOWN_ERROR;
        }
    }
	public function downloadPriceTemplet(){
		$file_name = "成本价导入模板.xls";
		$file_sub_path = APP_PATH."Runtime/File/";
		try{
			ExcelTool::downloadTemplet($file_name,$file_sub_path);
		}catch (BusinessLogicException $e){
            Log::write($e->getMessage());
            echo '对不起，模板不存在，下载失败！';
        } catch (\Exception $e) {
            Log::write($e->getMessage());
            echo parent::UNKNOWN_ERROR;
        }
	}
	public function exportToExcel(){
        if(!self::ALLOW_EXPORT){
            echo self::EXPORT_MSG;
            return false;
        }
		try{
            $id_list = I('get.id_list');
            $type = I('get.type');
			$result = array('status' => 0,'info' => '');
            $search = I('get.search','',C('JSON_FILTER'));
            foreach($search as $k => $v){
                $key = substr($k,7,strlen($k)-8);
                $search[$key] = $v;
                unset($search[$k]);
            }
            D('StockManagement')->exportToExcel($id_list,$search, $type);
//            if($id_list == ''){
//				D('StockManagement')->exportToExcel('',$search);
//			}else{
//				D('StockManagement')->exportToExcel($id_list,$search);
//			}
		}catch(BusinessLogicException $e){
			$result = array('status'=>1,'info'=>$e->getMessage());
			\Think\Log::write($e->getMessage());
		}catch(\Exception $e){
			$result = array('status'=>1,'info'=>parent::UNKNOWN_ERROR);
			\Think\Log::write(parent::UNKNOWN_ERROR);
		}	
		echo $result['info'];
	}
	
    public function adjustPrice()
    {

        if(IS_POST){
            try{
                $spec_id = I('get.spec_id','',C('JSON_FILTER'));
                $warehouse_id = I('get.warehouse_id','',C('JSON_FILTER'));
                if(empty($warehouse_id)){
                    $warehouse_id = '';
                }
                $resutl = D('Stock/StockSpec')->getAdjustPriceInfo($spec_id,$warehouse_id);
            }catch (BusinessLogicException $e){
                $resutl = array('total'=>0,'rows'=>array());
            }catch(\Exception $e){
                \Think\Log::write($e->getMessage());
                $resutl = array('total'=>0,'rows'=>array());
            }
            $this->ajaxReturn($resutl);
        }else{
            $params_url = '?';
			$form_data = array();
            $spec_id = I('get.spec_id','',C('JSON_FILTER'));
			$is_exist = stripos($spec_id,',');
			$fields = get_field('Stock/StockManagement','adjustprice');
			$warehouse_id = I('get.warehouse_id','',C('JSON_FILTER'));
			if($is_exist){
				if($warehouse_id == '0'){
					$search = array();
					D('Setting/EmployeeRights')->setSearchRights($search,'warehouse_id',2);
					$warehouse_id = $search['warehouse_id'];
				}
				$spec_no=array('商家编码'=>array('field' => 'spec_no','width'=>'100'));
				$fields = Array_merge($spec_no,$fields);
			}else{
				try{
					$form_data = D('Stock/StockSpec')->getGoodsInfo($spec_id);
				}catch (BusinessLogicException $e){
					$form_data = array();
				}catch (\Exception $e){
					\Think\Log::write($e->getMessage());
				}
            
				
			}
		if(!empty($warehouse_id))
		{
					$params_url .= '&warehouse_id='.$warehouse_id;
					$params_url .= '&spec_id='.$spec_id;
		}
		$params_url .= 'spec_id='.$spec_id;
		$id_list = self::getIDList($id_list,array('form','tool_bar','datagrid'),'adjustprice');
		$params = array(
			'datagrid'=>array('id'=>$id_list['datagrid']),
			'form'=>array('id'=>$id_list['form'])
		);
        $datagrid = array(
            'id' => $id_list['datagrid'],
            'options' => array(
				'title' => '',
                'url' => U('Stock/StockManagement/adjustPrice').$params_url,
                'toolbar' => "#{$id_list['tool_bar']}",
                'fitColumns' => false,
                'pagination' => false,
            ),
            'fields' => $fields,
            'class' => 'easyui-datagrid',
         );
		}
        $this->assign('id_list',$id_list);
        $this->assign('params',json_encode($params));
        $this->assign('form_data',json_encode($form_data));
        $this->assign('datagrid',$datagrid);
		$this->assign('is_exist',$is_exist);
        $this->display('dialog_adjust_price');
    }
    function fastAdjustCostPrice()
    {
        try{
            $resutl = array('status'=>0,'info'=>'成功','data'=>array());
            $adjust_info = I('','',C('JSON_FILTER'));
            //---过滤成本价没有改变的数据
            $adjust_detail = array();
            foreach($adjust_info['detail'] as $adjust_item)
            {
                
                if(empty($adjust_item['adjust_price'])  &&( (trim($adjust_item['adjust_price'])=='' && $adjust_item['adjust_price']==0) || $adjust_item['adjust_price'] !=0))
                {
                    continue;
                }else if($adjust_item['adjust_price'] < 0){
                    SE('调整价不能为负!');
                }else{
                    $adjust_detail[] = $adjust_item;
                }
            }

            if(empty($adjust_detail))
            {
                SE('没有调整成本价!');
            }
            D('Stock/StockSpec')->fastAdjustCostPrice($adjust_detail);
        }catch (BusinessLogicException $e){
            $msg = $e->getMessage();
            $resutl['status'] = 1;
            $resutl['info'] = $msg;
        }catch(\Exception $e){
            $msg = $e->getMessage();
            $resutl['status'] = 1;
            $resutl['info'] = self::UNKNOWN_ERROR;
        }
        $this->ajaxReturn($resutl);
    }
    public function settingStockAlarm()
    {

        try{
            $ids = I('get.ids','',C('JSON_FILTER'));
            $alarm_info = '';
            if($ids[0]['num']>1 || $ids[0]['spec_id'] == 0) {
                //多行数据
                if($ids[0]['warehouse_id'] == 0)
                {
                    $alarm_info = '您现在查询的是所有仓库的货品,该批量修改会修改所选货品下的所有仓库的货品预警策略!';
					if($ids[0]['spec_id'] == 0){
						$alarm_info = '您现在查询的是所有仓库的货品,该批量修改会修改所有货品下的所有仓库的货品预警策略!';
					}
                    $warehouse_list = array(array('id'=>'0','name'=>'全部'));
                }else{
                    if($ids[0]['multiple_warehouse'] == 0){
                        $alarm_info = '您现在是按单仓库查询,只支持针对当前查询的仓库来批量修改!';
                        $warehouse_list  = D('Setting/Warehouse')->field('warehouse_id AS id,name')->where(array('warehouse_id'=>$ids[0]['warehouse_id']))->order('warehouse_id asc')->select();
                    }else{
                        $alarm_info = '您现在是按多仓库查询,只支持针对当前查询的多个仓库来批量修改!';
						$warehouse_list = array(array('id'=>'0','name'=>'全部'));
                    }
                }
                $alarm_setting = array(
                    'sales_rate_type'=>'0',
                    'alarm_type'=>'0',
                    'alarm_days'=>'7',
                    'sales_rate_cycle'=>'7',
                    'sales_fixrate'=>'1',
                    'safe_stock'=>'0',
                );
                $alarm_data = array('alarm_setting'=>$alarm_setting,'goods_info'=>array());
                $spec_id = 0;
                $is_multi = 1;


            }else{
                //单行数据
                $alarm_data = D('Stock/StockSpec')->getStockAlarmDataByGoodsSpe($ids);
                $is_multi = 0;
                $spec_id = $ids[0]['spec_id'];
                $warehouse_list = $alarm_data['warehouse_list'];
                $alarm_info = '';

            }
            $id_list = array(
                'form'=>'stockmanagement_dialog_stockalarm_form',
                'goods_info'=>'stockmanagement_dialog_stockalarm_goodsinfo',
            );
            $this->assign('warehouse_list',$warehouse_list);
            $this->assign('alarm_info',$alarm_info);
            $this->assign('is_multi',$is_multi);
            $this->assign('spec_id',$spec_id);
            $this->assign('alarm_data',json_encode($alarm_data));
            $this->assign('id_list',$id_list);
        }catch(BusinessLogicException $e)
        {
            $this->assign('message',$e->getMessage());
            $this->display('Common@Exception:dialog');
            exit();
        }catch(\Exception $e)
        {
            \Think\Log::write(CONTROLLER_NAME.'-settingStockAlarm-'.$e->getMessage());
            $this->assign('message',self::UNKNOWN_ERROR);
            $this->display('Common@Exception:dialog');
            exit();
        }
        $this->display('dialog_stock_alarm');

    }
    public function getAlarmStockBySpec()
    {
        $warehouse_id= I('post.warehouse_id');
        $spec_id= I('post.spec_id');
        try{
            $result = array('status'=>0,'info'=>'获取失败！');
            $alarm_setting = D('Stock/StockSpec')->getAlarmStockBySpec($warehouse_id,$spec_id);
            $result['data'] = $alarm_setting;
        }catch(BusinessLogicException $e) {
            $result['status']=1;
            $result['info']=$e->getMessage();
        }catch(Exception $e)
        {
            \Think\Log::write(CONTORLLER_NAME.'-getAlarmStockBySpec-'.$e->getMessage());
            $result['status']=1;
            $result['info']=self::UNKNOWN_ERROR;
        }
        $this->ajaxReturn($result);
    }
    public function saveStockAlarm()
    {
        $alarm_setting_info= I('post.','',C('JSON_FILTER'));
        try{
            $result = array('status'=>0,'info'=>'保存成功！');
            $alarm_setting = D('Stock/StockSpec')->saveStockAlarm($alarm_setting_info);
            $result['data'] = $alarm_setting;
        }catch(BusinessLogicException $e) {
            $result['status']=1;
            $result['info']=$e->getMessage();
        }catch(Exception $e)
        {
            \Think\Log::write(CONTORLLER_NAME.'-saveStockAlarm-'.$e->getMessage());
            $result['status']=1;
            $result['info']=self::UNKNOWN_ERROR;
        }
        $this->ajaxReturn($result);
    }
	
	public function put(){
		try{
			$sid = get_sid();
			$uid = get_operator_id();
			$result = array('status'=>0,'info'=>'上传成功！');
			$type= I('post.type');
			$warehouse_id= I('post.warehouse_id');
            $put_type = I('post.put_type');
			$ids = '';
			if((int)$type == 1){
				$result = D('Stock/StockSpec')->field('spec_id')->where(array('warehouse_id'=>$warehouse_id))->group('spec_id')->select();
				foreach($result as $k=>$v){
					$ids .= $v['spec_id'].',';
				}
			}else{
				$ids = I('post.id');
			}
			$ids = substr($ids, 0, -1);
			$WmsManager = ManagerFactory::getManager("Wms");
            if($put_type == 'putOne'){
                $WmsManager->manual_wms_adapter_add_spec($sid,$uid,$warehouse_id,$ids);
            }else if($put_type == 'putAll'){
                $WmsManager->manual_wms_adapter_add_specs($sid,$uid,$warehouse_id,$ids);
            }
		}catch(Exception $e){
            \Think\Log::write(CONTORLLER_NAME.'-saveStockAlarm-'.$e->getMessage());
            $result['status']=1;
            $result['info']=self::UNKNOWN_ERROR;
        }
       // $this->ajaxReturn($result);
	}
    public function delStockGoods(){
        try
        {
            $id_list = DatagridExtention::getIDList(array('delstockgoods'));
            $operator_id=get_operator_id();
            $model=M('cfg_user_data');
            $where=array(
                'user_id'=>$operator_id,
                'type'=>10,
                'code'=>'del_stock_goods_dialog'
            );
            $cfg=$model->field('data')->where($where)->find();
            $this->assign("cfg", $cfg['data']);
            $this->assign("form_id", $id_list['delstockgoods']);
        }catch(\Exception $e)
        {
            $this->assign('message',$e->getMessage());
            $this->display('Common@Exception:dialog');
            exit();
        }
        $this->display('dialog_del_stock_goods');
    }
    public function saveDelStockGoods($id,$is_delete,$type) {
        $model_db       = M();
        $is_rollback    = false;
        $sql_error_info = '';
        $result=array('status'=>0,'info'=>'');
        $list=array();
        $tmp_arr=array();
        $check_spec_count = array();
        if($id==''){$this->ajaxReturn(array('status'=>1,'info'=>'没有查询到订单数据，请刷新后重试！'));}
        try {
            $operator_id=get_operator_id();
            $warehouse_id=array();
            D('Setting/EmployeeRights')->setSearchRights($warehouse_id,'warehouse_id',2);
            $warehouse_id = $warehouse_id['warehouse_id'];
            $model=M('cfg_user_data');
            $stock_log_db = M('stock_spec_log');
            $where=array(
                'user_id'=>$operator_id,
                'type'=>10,
                'code'=>'del_stock_goods_dialog'
            );
            $get_cfg=$model->where($where)->find();
            if($get_cfg){
                $save['data']=$is_delete;
                $model->where($where)->save($save);
            }else{
                $add=array(
                    'user_id'=>$operator_id,
                    'type'=>10,
                    'code'=>'del_stock_goods_dialog',
                    'data'=>$is_delete,
                );
                $model->add($add);
            }
            foreach ($id as $v){
                if($type==1 && $v == ''){
                    $result = array('status'=>0,'info'=>'');
                    break;
                }
                $sql_error_info     = 'saveDelStockGoods-get_goods_spec';
                //取出所选行对应goods_spec表的单品信息
                $res_goods_spec_arr = $model_db->table('goods_spec')->alias('gs')->field('gs.spec_id, gs.spec_no,gs.spec_name,gs.goods_id')->where(array('spec_id' => array('eq', $v), 'deleted' => array('eq', 0)))->find();
                if(empty($res_goods_spec_arr['goods_id'])||empty($res_goods_spec_arr['spec_id'])) {
                    $list[]=array('spec_no'=>$res_goods_spec_arr['spec_no'],'info'=>'没有查询到该单品，请重新打开本界面');
                    continue;
                }
                $res_spec_count     = $model_db->query("SELECT spec_count,goods_id FROM goods_goods WHERE goods_id=%d AND deleted=0",$res_goods_spec_arr['goods_id']);
                if(!array_key_exists($res_goods_spec_arr['goods_id'], $check_spec_count)){
                    $check_spec_count[$res_goods_spec_arr['goods_id']] = $res_spec_count[0]['spec_count'];
                }
                //$tmp_stock_arr[]=$res_goods_spec_arr;
//                if($is_delete==1){
//                    if ($type == 2 && $check_spec_count[$res_goods_spec_arr['goods_id']] < 2) {
//                        //E('货品档案至少需要一个单品');
//                        $list[]=array('spec_no'=>$res_goods_spec_arr['spec_no'],'info'=>'货品档案至少需要一个单品','location'=>'单品列表');
//                        continue;
//                    }
//                }
                $tmp_arr[]=$res_goods_spec_arr;
                $check_spec_count[$res_goods_spec_arr['goods_id']] -= 1;
            }
            //$goods_goods_db = Factory::getModel('GoodsGoods');
            $goods_goods_db =D('Goods/GoodsGoods');
            //删除库存里的货品信息
            $arr_goods_spec = $goods_goods_db->filterSpec($model_db, $tmp_arr, $sql_error_info, $list,0);
            //添加删除位置
            for($i=0;$i<count($list);$i++){
                if(count($list[$i])<3)
                $list[$i]['location'] = '库存管理';
            }
            $arr_length = count($tmp_arr);
            for($j=0;$j<$arr_length;$j++){
                $sql_error_info = 'saveDelStockGoods-get_stock_spec';
                $res_stock_spec = $model_db->query(" SELECT warehouse_id,stock_num,lock_num,unpay_num,subscribe_num,order_num,sending_num,purchase_num, transfer_num,to_purchase_num,
            purchase_arrive_num,return_num FROM stock_spec WHERE spec_id=%d AND stock_num=0 AND lock_num=0 AND unpay_num=0 AND subscribe_num=0 AND
            order_num=0 AND sending_num=0 AND purchase_num=0 AND transfer_num=0 AND to_purchase_num=0 AND purchase_arrive_num=0 AND return_num=0 AND warehouse_id in(%s)", $tmp_arr[$j]['spec_id'],$warehouse_id);
                //$arr_goods_spec[]=$arr_goods_spec[$j];
                $del_warehouse_count=count($res_stock_spec);
                $res_refund_val  = $model_db->query("SELECT 1 FROM sales_refund sr LEFT JOIN sales_refund_order sro ON sro.refund_id=sr.refund_id WHERE sr.process_status<>10 AND sr.process_status<>20 AND sr.process_status<>90 AND sro.spec_id =%d", $tmp_arr[$j]['spec_id']);
                if(empty($res_refund_val)){
                    for ($i = 0; $i < $del_warehouse_count; $i++) {
                        $sql_error_info = 'saveDelStockGoods-delete_stock_spec_position';
                        $model_db->execute("DELETE FROM stock_spec_position WHERE spec_id=%d AND warehouse_id=%d", $tmp_arr[$j]['spec_id'], $res_stock_spec[$i]['warehouse_id']);
                        $sql_error_info = 'saveDelStockGoods-delete_stock_spec';
                        $model_db->execute("DELETE FROM stock_spec WHERE spec_id=%d AND warehouse_id=%d", $tmp_arr[$j]['spec_id'], $res_stock_spec[$i]['warehouse_id']);
                    }
                }
                $warehouse_count=M('stock_spec')->where(array('spec_id'=>array('eq',$tmp_arr[$j]['spec_id'])))->count();
                if($warehouse_count == 0){
                    $sql_error_info = 'saveDelStockGoods-add_stock_spec_log';
                    $stock_log_arr = array(
                        'operator_id' => $operator_id,
                        'stock_spec_id' => $tmp_arr[$j]['spec_id'],
                        'operator_type' =>5,// 5：删除库存货品（新增）
                        'num' => 0,
                        'stock_num' =>0,
                        'message'=> "删除库存货品--单品--".$tmp_arr[$j]['spec_name']
                    );
                    $stock_log_db->data($stock_log_arr)->add();
                }
            }
            if($is_delete==1){
                $goods_goods_db->delSpec($model_db, $tmp_arr, $is_rollback, $sql_error_info, $list,0);
                foreach($arr_goods_spec as $k=>$v){
                    $result_spec_count     = $model_db->query("SELECT spec_count,goods_id FROM goods_goods WHERE goods_id=%d AND deleted=0",$v['goods_id']);
                    if($result_spec_count[0]['spec_count']<1){
                        $sql_error_info = 'delSpec-update_goods_goods-delete';
                        $model_db->execute("UPDATE goods_goods SET spec_count=0,deleted=" . time() . " WHERE goods_id = %d", $v['goods_id']);
                        $res_goods_goods = $model_db->table('goods_goods')->field('goods_name')->where(array('goods_id' => array('eq', $v['goods_id'])))->find();
                        $arr_goods_log   = array(
                            'goods_type'   => 1,//1-货品 2-组合装
                            'goods_id'     => $v['goods_id'],
                            'spec_id'      => 0,
                            'operator_id'  => $operator_id,
                            'operate_type' => 31,
                            'message'      => '删除货品--' . $res_goods_goods['goods_name'],
                            'created'      => array('exp', 'NOW()')
                        );
                        $sql_error_info  = 'delSpec-add_goods_goods_log';
                        $model_db->table('goods_log')->add($arr_goods_log);
                    }
                }
                for($i=0;$i<count($list);$i++){
                    if(count($list[$i])<3)
                    $list[$i]['location'] = '单品列表';
                }
            }
            if (count($list)>0){
                $result=$type==1?array('status'=>1,'info'=>$list[0]['info']):array('status'=>2,'info'=>array('total'=>count($list),'rows'=>$list));
            }
        } catch (\PDOException $e) {
            if ($is_rollback) {
                $model_db->rollback();
            }
            Log::write($sql_error_info . ':' . $e->getMessage());
            $result=array('status'=>1,'info'=>'未知错误，请联系管理员');
        } catch (\Exception $e) {
            if ($is_rollback) {
                $model_db->rollback();
            }
            $result=array('status'=>1,'info'=> $e->getMessage());
        }
        $this->ajaxReturn($result);
    }

    public function refreshPlatformStock()
    {
        $data = I('post.');
        $rec_id = $data['recid_str'];
        $res = array('status'=>0,'msg'=>'操作成功');
        $shop_id = $data['shop_id'];
        try{
            $list = array();
            D('StockManagement')->refreshPlatformStock($rec_id, $list, $shop_id);
            if(count($list)>0){
                $res['status'] = 2;
                $res['msg'] = '操作失败';
                $res['data'] = $list;
            }
        }catch(\Exception $e)
        {
            $res['status'] =1;
            $res['msg'] = parent::UNKNOWN_ERROR;
        }
        $this->ajaxReturn($res);
    }
    public function getShopList(){
        $data = I('get.');
        $rec_id = $data['recid_str'];
        $is_have_shop = 1;
        try{
            $list = D('StockManagement')->getShopList($rec_id);
            if(empty($list['data'])){
                $is_have_shop = 0;
            }
            $this->assign('shop_list', $list['data']);
            $this->assign('is_have_shop', $is_have_shop);

        }catch(\Exception $e){
            $this->assign('message',$e->getMessage());
            $this->display('Common@Exception:dialog');
            exit();
        }
        $this->display('dialog_select_shop');
    }
	public function showTotalPrice(){
		$id_list = array(
				'id_datagrid' => 'dialog_show_total_price',
				'toolbar'=> 'toolbar_show_total_price',
		);
		$datagrid=array(
				'id'=>$id_list['id_datagrid'],
				'style'=>'',
				'class'=>'easyui-datagrid',
				'options'=> array(
						'title'=>'',
						'toolbar' => "#{$id_list['toolbar']}",
						'url'   => U('Stock/StockManagement/show_total_price'),
						'fitColumns'=>false,
						'rownumbers'=>true,
						'pagination'=>false,
				),
				'fields' => get_field('Stock/StockManagement','show_total_price')
		);
		$params = array(
				'datagrid' => array('id' => $id_list['id_datagrid'])
		);
		$this->assign('params', json_encode($params));
		$this->assign('id_list', $id_list);
		$this->assign('datagrid',$datagrid);
		$this->display('show_total_price');
	
	}
	public function show_total_price($page=1, $rows=20, $search = array(), $sort = 'id', $order = 'desc'){
		 try{
           $result = D('Stock/StockManagement')->show_total_price($page=1, $rows=20, $search = array(), $sort = 'id', $order = 'desc');
        }catch(\Exception $e){
			$result = array();
           \Think\Log::write(CONTORLLER_NAME.'-show_total_price-'.$e->getMessage());
        }
		$this->ajaxReturn($result);
	}
}