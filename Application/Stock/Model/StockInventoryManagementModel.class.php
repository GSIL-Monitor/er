<?php
namespace Stock\Model;
use Think\Log;
use Think\Model;
use Think\Exception\BusinessLogicException;
use Common\Common\UtilDB;
use Common\Common\ExcelTool;
class StockInventoryManagementModel extends Model
{
	protected $tableName = 'stock_pd';
	protected $pk = 'rec_id';
	
	protected $_validate = array(
		array('num','require','盘点数量不能为空!'),
        array('spec_no','require','商家编码不能为空!'),
        array('warehouse_name','require','仓库名称不能为空!'),
        array('num','checkNum','库存数量不合法!',1,'callback'),
    );
    protected $patchValidate = true;
    protected function checkNum($num)
    {
        return check_regex('positive_number',$num);
    }
	
	public function searchStockinvenmanList($page=1, $rows=20, $search = array(), $sort = 'id', $order = 'desc',$type){
		D('Setting/EmployeeRights')->setSearchRights($search,'warehouse_id',2);
		$where_arr = $this->searchForm($search);
		$where_stock_pd = $where_arr['where_stock_pd'];
		$where_sales_pd_order = $where_arr['where_sales_pd_order'];
		$page = intval($page);
		$rows = intval($rows);
	    $limit=($page - 1) * $rows . "," . $rows;
	    $order = $sort.' '.$order;
		$order = addslashes($order);

	    try {
	        $m = $this->alias("so");
	        switch ($type)
	        {
	           
	            case 'stockinvenmanage':{
					$sql = $this->StockInventoryManagement($m, $where_stock_pd,$order,$limit);
	                break;
	            }
	           
	            default:
	                \Think\Log::write("unknown type:" . $type);
	                break;
	        }
	        	
	        $sql_total = $m->fetchSql(true)->count('distinct so.rec_id');
	        $total=$m->query($sql_total);
	        $total = $total[0]['tp_count'];
	        	
	        $list=$total?$m->query($sql):array();
			
	        $data=array('total'=>$total,'rows'=>$list);
	    }catch(\PDOException $e){
	        \Think\Log::write($e->getMessage());
	        SE($e->getMessage());
	    }
	    return $data;
	
	}
	public function searchForm($search){
		$where_stock_pd = array();
		$where_sales_pd_order = array();
		foreach ($search as $k => $v) {
			if ($v === '') continue;
			switch ($k) {
				case 'pd_no':{//盘点单号  stock_pd
					set_search_form_value($where_stock_pd, $k, $v, 'so' );
					break;
				}
				case 'goods_no'://货品编号
					set_search_form_value($where_stock_pd, $k, $v, 'so', 2);
					break;

				case 'status':{//盘点单状态 stock_pd
					set_search_form_value($where_stock_pd, $k, $v, 'so', 2);
					break;
				}
				case 'warehouse_id'://仓库类型
					set_search_form_value($where_stock_pd, $k, $v, 'so', 2);
					break;

				case 'creator_id':{//经办人  stock_pd
					set_search_form_value($where_stock_pd, $k, $v, 'so', 2);
					break;
				}

				case 'spec_no':{//商家编码
					set_search_form_value($where_sales_pd_order, $k, $v, 'sto');
					break;
				}

				default:
					\Think\Log::write("unknown field:" . print_r($k, true) . ",value:" . print_r($v, true));
					break;
			}
		}
		return array(
			'where_stock_pd'=>$where_stock_pd,
			'where_sales_pd_order'=>$where_sales_pd_order,
		);
	}
	private function StockInventoryManagement(&$m,&$where_stock_pd,$order,$limit)
	{
	    $m = $m->where($where_stock_pd);
	    $page = clone $m;
	    $sql_page = $page->field('so.rec_id id')->order($order)->group('so.rec_id')->limit($limit)->fetchSql(true)->select();
	
	    $sql = " select so.rec_id AS id,so.warehouse_id ,pd_no, so.status,"
	            ." so.mode,so.modified,so.creator_id, so.remark, so.created, "
	            ." he.fullname creator_name, "
	            ." cw.name warehouse_name"
				." FROM stock_pd so"
				." LEFT JOIN hr_employee he ON he.employee_id = so.creator_id"
				." LEFT JOIN cfg_warehouse cw ON cw.warehouse_id = so.warehouse_id"
				." join (".$sql_page.") page ON page.id = so.rec_id ORDER BY id DESC";
	    return $sql;
	}
	
	public function importStockSpec($data,&$result){
		 try{
                $operator_id = get_operator_id();
                \Think\Log::write('导入数据提示:'.print_r($data,true),\Think\Log::DEBUG);
                $check_fields_ar = array('num','price','spec_no','warehouse_name','position_no,warehouse_name','spec_no,position_no,warehouse_name');
                foreach ($data as $import_index => $import_rows)
                {
                    if (!$this->create($import_rows)) {
                        // 如果创建失败 表示验证没有通过 输出错误提示信息
                        $data[$import_index]['status'] = 1;
                        $data[$import_index]['result'] = '失败';
                        foreach ($check_fields_ar as $check_field)
                        {
                            $temp_error_ar = $this->getError();
                            if(isset($temp_error_ar["{$check_field}"]))
                            {
                                $data[$import_index]['message'] = $temp_error_ar["{$check_field}"];
                                break;
                            }
                            
                        }
                        
                    }
                }
                \Think\Log::write('导入数据验证:'.print_r($data,true),\Think\Log::DEBUG);
                //查入临时表信息包括验证完的信息
                foreach($data as $item)
                {
                    $res_insert_temp_import_detail = $this->execute("insert into tmp_import_detail(`spec_no`,`position_no`,`num`,`warehouse_name`,`price`,`status`,`result`,`message`,`line`) values('{$item['spec_no']}','{$item['position_no']}',".(float)$item['num'].",'{$item['warehouse_name']}',".(float)$item['price'].",".(int)$item['status'].",'".$item['result']."','{$item['message']}',".(int)$item['line'].")");
				}
                //校验是否存在相应的仓库和商家编码
				$search = array();
				D('Setting/EmployeeRights')->setSearchRights($search,'warehouse_id',2);
				$right_warehouse_id = $search['warehouse_id'];
                $query_warehouse_id_list_sql = "SELECT * FROM tmp_import_detail " ;
                $res_query_warehouse_id_list = $this->query($query_warehouse_id_list_sql);
                \Think\Log::write('导入临时表的数据:'.print_r($res_query_warehouse_id_list,true),\Think\Log::DEBUG);
                $query_warehouse_isset_sql = "UPDATE tmp_import_detail tid LEFT JOIN cfg_warehouse sw ON sw.name=tid.warehouse_name"
                             ."  SET tid.warehouse_id=sw.warehouse_id,tid.status=IF(sw.warehouse_id IS NULL,1,0),tid.result=IF(sw.warehouse_id IS NULL,'失败',''),tid.message=IF(sw.warehouse_id IS NULL,'仓库不存在','') "
                             ."  WHERE tid.status=0 ;";
                $res_query_warehouse_isset = $this->execute($query_warehouse_isset_sql);
				$right_warehouse_sql = "UPDATE tmp_import_detail tid left join (select warehouse_id,name from cfg_warehouse where warehouse_id in ($right_warehouse_id)) wd on wd.warehouse_id = tid.warehouse_id"
							 ." set tid.status = if(wd.name is null,1,0),tid.result=if(wd.name is null,'失败',''),tid.message=if(wd.name is null,'仓库不存在','') "
							 ." where tid.status = 0;";
				$right_warehouse = $this->execute($right_warehouse_sql);
			   $query_goods_spec_isset_sql = "UPDATE tmp_import_detail tid LEFT JOIN goods_spec gs ON gs.spec_no=tid.spec_no"
                                              ." SET tid.spec_id=gs.spec_id,tid.status=IF(gs.spec_id IS NULL,1,0),tid.result= IF(gs.spec_id IS NULL,'失败',''),tid.message=IF(gs.spec_id IS NULL,'商家编码不存在','')"
                                              ." WHERE tid.status=0;";
                $res_query_goods_spec_isset = $this->execute($query_goods_spec_isset_sql);
				$res_query_stock_sql = "UPDATE tmp_import_detail tid left join stock_spec ss on ss.spec_id=tid.spec_id and ss.warehouse_id = tid.warehouse_id"
										." set tid.status = if(ss.rec_id is null,1,0),tid.result = if(ss.rec_id is null,'失败',''), tid.message=if(ss.rec_id is null,'在该仓库内该商品不存在','')"
										." where tid.status = 0;";
				$res_query_stock_isset = $this->execute($res_query_stock_sql);
				$info = $this->query("select rec_id,line, spec_no,position_no,warehouse_name from tmp_import_detail where status = 0");
				
				$query_position_no_isset_sql = "UPDATE tmp_import_detail tid LEFT JOIN stock_spec_position ssp ON ssp.spec_id = tid.spec_id and ssp.warehouse_id = tid.warehouse_id"
                                              ." SET tid.position_id=ssp.position_id"
                                              ." WHERE tid.status=0;";
                $res_query_position_no_isset = $this->execute($query_position_no_isset_sql);
            
                $record_original_prince_and_num_sql = "UPDATE tmp_import_detail tid LEFT JOIN stock_spec ss ON  tid.spec_id=ss.spec_id AND tid.warehouse_id=ss.warehouse_id"
                                                     ."	SET tid.cost_price=ss.cost_price,tid.stock_num=ss.stock_num"
                                                     ." WHERE tid.status=0;";
                $res_record_original_prince_and_num = $this->execute($record_original_prince_and_num_sql);
                $query_warehouse_id_list_sql = "SELECT warehouse_id FROM tmp_import_detail WHERE STATUS=0 GROUP BY warehouse_id" ;
                $res_query_warehouse_id_list = $this->query($query_warehouse_id_list_sql);
                if(empty($res_query_warehouse_id_list))
                {
                    $deal_import_data_result = $this->query("select line as id,message,status,result,spec_no from tmp_import_detail where status =1");
                    \Think\Log::write(print_r($deal_import_data_result,true),\Think\Log::INFO);

                    if(!empty($deal_import_data_result)){
                        $result['status'] = 2;
                        $result['data'] = $deal_import_data_result;
                    }

                    return;
                }
				$operator_id = get_operator_id();
				foreach($res_query_warehouse_id_list as $item){
					$sql_get_no = 'select FN_SYS_NO("stockpd") pd_no';			
					$res_stockpd_no = $this->query($sql_get_no);
					$stockpd_no = $res_stockpd_no[0]['pd_no'];
					$add_data = array(
						'warehouse_id'=>$item['warehouse_id'],
						'pd_no'=>$stockpd_no,
						'mode'=>0,
						'type'=>0,
						'note_count'=>0,
						'creator_id'=>$operator_id,
						'status'=>1,
						'remark'=>'',
						'created'=>array('exp','NOW()'),
					);
					$add_result = M('stock_pd')->add($add_data);
					$stockpd_id_sql = "SElECT rec_id FROM stock_pd WHERE pd_no = \"{$stockpd_no}\"";
					$stockpd_id = $this->query($stockpd_id_sql);
					$stockpd_id = $stockpd_id[0]['rec_id'];
					if(empty($item['position_id']) || !isset($item['position_id'])){
						$item['position_id'] = (-$item['warehouse_id']);
					}
					
					$pd_detail = $this->query("	SELECT {$stockpd_id} AS pd_id,tid.spec_id,IF(tid.position_id is null,-tid.warehouse_id
					,tid.position_id) AS position_id,tid.stock_num AS old_num,tid.num AS input_num,tid.num AS new_num,tid.cost_price AS cost_price
					,'' AS remark,NOW() as created from tmp_import_detail tid where tid.status = 0 and  tid.warehouse_id = ".$item['warehouse_id']);
	
					$res_add = M('stock_pd_detail')->addAll($pd_detail);
					
					
					$update_log=array(
						'operator_id'=>$operator_id,
						'order_type' =>4,
						'order_id'   =>$stockpd_id,
						'operate_type'=>'11',
						'message'   =>"生成盘点单"
					);
					D('StockInoutLog')->add($update_log);
					D('Stock/StockSpec')->submitPd($stockpd_id);
				}
            }catch (\PDOException $e) {
                $msg = $e->getMessage();
                \Think\Log::write($this->name.'--importStockSpec--'.$msg);
                 $result['status'] = 1;
                $result['msg'] = $msg;
				return;
				
                
            } catch (\Exception $e){
                $msg = $e->getMessage();
                \Think\Log::write($this->name.'--importStockSpec--'.$msg);
                $result['status'] = 1;
                $result['msg'] = $msg;
                return;
            }
			
			try {
                $deal_import_data_result = $this->query("select line as id,message,status,result from tmp_import_detail where status =1");
                if(!empty($deal_import_data_result)){
                    $result['status'] = 2;
                    $result['data'] = $deal_import_data_result;
                }
             } catch (\PDOException $e) {
                $msg = $e->getMessage();
                $result['status'] = 1;
                $result['msg'] = '导入信息获取失败,联系管理员';
                $result['data'] = array();
                \Think\Log::write($this->name.'--importStockSpec--'.$msg);
            } catch (\Exception $e) {
                $msg = $e->getMessage();
                $result['status'] = 1;
                $result['msg'] = '导入信息获取失败,联系管理员';
                $result['data'] = array();
                \Think\Log::write($this->name.'--importStockSpec--'.$msg);
            }
           
	}
	public function exportToExcel($id_list,$search,$type = 'excel'){
		try {
			D('Setting/EmployeeRights')->setSearchRights($search, 'warehouse_id', 2);
			$creator=session('account');
			$where_arr = $this->searchForm($search);
			$where_stock_pd = $where_arr['where_stock_pd'];
			$where_sales_pd_order = $where_arr['where_sales_pd_order'];
			$m = $this->alias("so")->where($where_stock_pd);
			$page = clone $m;
			$sql_page = $page->field('so.rec_id id')->group('so.rec_id')->fetchSql(true)->select();
			if(empty($id_list)){
				$select_ids_sql = '';
			}else{
				$select_ids_sql = " where so.rec_id in (".$id_list.") ";
			}
			$sql = " select so.rec_id AS id,so.warehouse_id ,pd_no, so.status,spd.remark,"
				. " so.mode,so.modified,so.creator_id, so.created,spd.new_num,spd.old_num,(spd.new_num-spd.old_num) AS pd_num, "
				. " he.fullname creator_name, "
				. " cw.name warehouse_name,cwp.position_no,"
				. " gs.spec_no,gs.spec_code,gs.barcode,gs.spec_name,gg.goods_no,gg.goods_name"
				. " FROM stock_pd so"
				. " LEFT JOIN stock_pd_detail spd ON so.rec_id = spd.pd_id "
				. " LEFT JOIN goods_spec gs ON gs.spec_id = spd.spec_id "
				. " LEFT JOIN goods_goods gg ON gg.goods_id = gs.goods_id "
				. " LEFT JOIN hr_employee he ON he.employee_id = so.creator_id"
				. " LEFT JOIN cfg_warehouse cw ON cw.warehouse_id = so.warehouse_id"
				. " LEFT JOIN cfg_warehouse_position cwp ON cwp.rec_id = spd.position_id and cwp.warehouse_id = so.warehouse_id"
				. " JOIN (" . $sql_page . ") page ON page.id = so.rec_id ".$select_ids_sql." ORDER BY id DESC";
			$data = $this->query($sql);
			$stockpd_type=array(
				'0'=>'单品盘点',
				'1'=>'货位盘点',
				'2'=>'明细盘点',
			);
			$stockpd_status=array(
				'1'=>'编辑中',
				'2'=>'已完成',
				'3'=>'已取消',
			);
			for($i=0;$i<count($data);$i++){
				$data[$i]['mode']=$stockpd_type[$data[$i]['mode']];
				$data[$i]['status']=$stockpd_status[$data[$i]['status']];
			}
			$num = workTimeExportNum($type);
			if (count($data) > $num) {
				if($type == 'csv'){
					SE(self::EXPORT_CSV_ERROR);
				}
				SE('导出的详情数据超过设定值，8:00-19:00可以导出1000条，其余时间可以导出4000条!');
			}
			$excel_header = D('Setting/UserData')->getExcelField('Stock/StockInventory', 'stockpdexport');
			$title = '盘点单';
			$filename = '盘点单';
			foreach ($excel_header as $v) {
				$width_list[] = 20;
			}
			if($type == 'csv') {
				$ignore_arr = array('商家编码','货品编号','货品名称','货品简称','规格码','规格名称','条形码','仓库名称','盘点人','货位');
				ExcelTool::Arr2Csv($data, $excel_header, $filename, $ignore_arr);
			}else {
				ExcelTool::Arr2Excel($data, $title, $excel_header, $width_list, $filename, $creator);
			}
		} catch (\PDOException $e) {
			\Think\Log::write($e->getMessage());
			SE(parent::PDO_ERROR);
		} catch (BusinessLogicException $e) {
			SE($e->getMessage());
		} catch (\Exception $e) {
			\Think\Log::write($e->getMessage());
			SE(parent::PDO_ERROR);
		}
	}
}



?>