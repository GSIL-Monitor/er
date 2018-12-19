<?php
namespace Stock\Model;
use Common\Common\UtilDB;
use Common\Common\ExcelTool;
use Common\Common\UtilTool;
use Think\Exception;
use Think\Exception\BusinessLogicException;
use Think\Model;
/**
 * Created by PhpStorm.
 * User: gaosong
 * Date: 11/19/15
 * Time: 15:14
 */
class StockManagementModel extends Model{
    protected $tableName = 'stockin_order_detail';
    protected $_validate = array(
        //array('class_name','checkName','只能输入英文、中文、数字、_、-',0,'callback'),
        array('spec_no','require','商家编码不能为空!'),
        array('warehouse_name','require','仓库名称不能为空!'),
        array('num','checkNum','库存数量不合法!',1,'callback'),
        array('price','checkPrice','成本价不合法!',1,'callback'),
		array('adjust_price','checkPrice','成本价不合法!',1,'callback'),
     //   array('position_no,warehouse_name','checkPositionNo','不存在该货位!',1,'callback'),
        array('spec_no,position_no,warehouse_name','checkIssetPosition','该货品已分配货位,请填写空或者填写原库存货位!',1,'callback'),
    );
    protected $patchValidate = true;
    protected function checkNum($num)
    {
        return check_regex('positive_number',$num);
    }
    protected function checkPrice($cost_price)
    {
        return check_regex('positive_number',$cost_price);
    }
    protected function checkIssetPosition($position_info)
    {
        try{
            if(!empty($position_info['position_no']))
            {
                $res = D('Stock/StockSpecPosition')->alias('ssp')->join('left join goods_spec gs on gs.spec_id = ssp.spec_id')->join('left join cfg_warehouse cw on ssp.warehouse_id = cw.warehouse_id')->join('left join cfg_warehouse_position cwp on cwp.warehouse_id = ssp.warehouse_id and cwp.rec_id = ssp.position_id')->where(array('gs.spec_no'=>$position_info['spec_no'],'cw.name'=>$position_info['warehouse_name'],'cwp.position_no'=>$position_info['position_no']))->select();
                $res_or = D('Stock/StockSpecPosition')->alias('ssp')->join('left join goods_spec gs on gs.spec_id = ssp.spec_id')->join('left join cfg_warehouse cw on ssp.warehouse_id = cw.warehouse_id')->where(array('gs.spec_no'=>$position_info['spec_no'],'cw.name'=>$position_info['warehouse_name']))->select();

                if(!empty($res_or) && empty($res)){
                    return false;
                }
            }

            return true;
        }catch (\PDOException $e){
            \Think\Log::write($e->getMessage());
            return false;
        }catch (\Exception $e){
            \Think\Log::write($e->getMessage());
            return false;
        }
    }
    protected function checkPositionNo($position_info)
    {
        try{
            $res = D('Setting/warehousePosition')->alias('cwp')->join('left join cfg_warehouse cw on cwp.warehouse_id = cw.warehouse_id')->where(array('cw.name'=>$position_info['warehouse_name'],'cwp.position_no'=>$position_info['position_no']))->select();

            if(empty($res) && !empty($position_info['position_no'])){
                return false;
            }
            return true;
        }catch (\PDOException $e){
            \Think\Log::write($e->getMessage());
            return false;
        }catch (\Exception $e){
            \Think\Log::write($e->getMessage());
            return false;
        }
    }
    /**
     * 快速盘点
     *
     * 注意:  此方法以以后不使用,此经过重构 到 stockspecmodel.class.php中quickCheckStockSpec
     *
     * @param $updata
     * @return mixed
     * @throws  $data = array('status' => 0,'info' => PDO_ERROR)|$data = array('status' => 0,'info' => E_ERROR)
     */
/*    public function quickCount($updata)
    {
        //开启事务
        $data['status'] = 1;
        $data[info] = '';
        $this->startTrans();
        try {
            //生成盘点单
            $pd_no = $this->query("SELECT FN_SYS_NO('stockpd')");
            $pd_no = $pd_no[0]["fn_sys_no('stockpd')"];

            //插入盘点单
            $tmp_data = array(
                "pd_no" => $pd_no,
                "status" => 1,
                "warehouse_id" => $updata['warehouse_id'],
                "created" => date("Y-m-d H:i:s"),
            );
            $pd_id=M('stock_pd')->add($tmp_data);
            unset($tmp_data);

            //插入盘点明细
            //$pd_id = $this->query("SELECT rec_id FROM stock_pd WHERE pd_no ='%s'", $pd_no);

            //$pd_id = $pd_id[0]['rec_id'];

            $tmp_data = array(
                "pd_id" => $pd_id,
                "spec_id" => $updata['id'],
                "old_num" => $updata['stock_num'],
                "input_num" => $updata['new_stock_num'],
                "created" => date("Y-m-d H:i:s"),
            );
            M('stock_pd_detail')->add($tmp_data);
            unset($tmp_data);


            $position_info = D('Stock/StockSpec')->alias('ss')->field('IF(ss.last_position_id is NULL OR ss.last_position_id = 0,-ss.`warehouse_id`,ss.last_position_id) as position_id,IFNULL(cwp.zone_id, cwp2.zone_id) as zone_id')->join('left join cfg_warehouse_position cwp on cwp.rec_id = ss.last_position_id')->join('left join cfg_warehouse_position cwp2 on cwp2.rec_id = -ss.warehouse_id')->where(array('ss.spec_id'=>$updata['spec_id'],'ss.warehouse_id'=>$updata['warehouse_id']))->find();
            $tmp_data = array(
                "stock_num"=>$updata['new_stock_num'],
                "zone_id"=>$position_info['zone_id'],
                'last_pd_time'=>array('exp',"NOW()"),
                'warehouse_id'=>$updata['warehouse_id'],
                'position_id'=>$position_info['position_id'],
                "spec_id" => $updata['id'],
                'last_position_id'=>$position_info['position_id'],
            );
            D('Stock/StockSpec')->add($tmp_data,'',array('last_position_id'=>array('exp','VALUES(last_position_id)'),'stock_num'=>array('exp','VALUES(stock_num)'),'last_pd_time'=>array('exp',"NOW()")));
            D('Stock/StockSpecPosition')->add($tmp_data,'',array('stock_num'=>array('exp','VALUES(stock_num)'),'last_pd_time'=>array('exp',"NOW()")));
            unset($tmp_data);

            //计算盘点差异数
            $delta = (int)$updata['stock_num'] - (int)$updata['new_stock_num'];

            if ((int)$delta > 0) {
                //出库
                //插入出库单
                $stockout = $this->query("SELECT FN_SYS_NO('stockout')");
                $stockout = $stockout[0]["fn_sys_no('stockout')"];

                $tmp_data = array(
                    "stockout_no" => $stockout,
                    "status" => 110,
                    "src_order_type" => 4,
                    "src_order_id" => $pd_id,
                    "created" => date("Y-m-d H:i:s"),
                    "warehouse_id" => $updata['warehouse_id'],
                    "reserve_i" => 0,
                    "reserve_i2" => 0,
                    "goods_type_count" => 1,
                    "goods_count" => $delta,
                    "goods_total_cost" => $updata['cost_price']*$delta,//货品总成本价
                    "goods_total_amount" => $updata['cost_price']*$delta*1,//(1--代表种类) 货品总售价,stockout_order_detail的total_amount之和    
                    "operator_id" => $updata['operator_id'],
                    "consign_time" =>date("Y-m-d H:i:s"),
                    "modified"=>date("Y-m-d H:i:s"),
//                    "created"=>date("Y-m-d H:i:s"),
                );
                $stockout_id=M('stockout_order')->add($tmp_data);
                unset($tmp_data);

                //插入出库单明细
                //$stockout_id = $this->query("SELECT stockout_id FROM stockout_order WHERE stockout_no ='%s'", $stockout);

                //$stockout_id = $stockout_id[0]['stockout_id'];

                $tmp_data = array(
                    "stockout_id" => $stockout_id,
                    "src_order_type" => 5,
                    "src_order_detail_id" => 1,
                    "num" => $delta,
                    "goods_name" => $updata['goods_name'],
                    "goods_id" => $updata['goods_id'],
                    "goods_no" => $updata['goods_no'],
                    "spec_name" => $updata['spec_name'],
                    "spec_id" => $updata['spec_id'],
                    "position_id" => $position_info['position_id'],
                    "spec_no" => $updata['spec_no'],
                    "spec_code" => $updata['spec_code'],
                    "cost_price" => $updata['cost_price'],//成本价
                    "price" => $updata['cost_price'],//最终价格，暂时默认为成本价
                    "total_amount" => $updata['cost_price']*abs($delta),//总货款,price * num
                    "created" => date("Y-m-d H:i:s"),
                );
                M('stockout_order_detail')->add($tmp_data);
                unset($tmp_data);

                D('Common/SysProcessBackground')->stockoutChange($stockout_id);

                //插入日志

                $tmp_data = array(
                    "order_type" => 2,
                    "order_id" => $stockout_id,
                    "message" => "生成盘点出库单:" . $stockout . "并完成出库",
                    "created" => date("Y-m-d H:i:s"),
                    "operator_id" => $updata['operator_id'],
                );
                M('stock_inout_log')->add($tmp_data);
                unset($tmp_data);
            }

            if ((int)$delta < 0) {
                //入库
                //插入入库单
                $stockin = $this->query("SELECT FN_SYS_NO('stockin')");
                $stockin = $stockin[0]["fn_sys_no('stockin')"];
                $delta=abs($delta);
                $tmp_data = array(
                    "stockin_no" => $stockin,
                    "status" => 80,
                    "src_order_type" => 4,
                    "src_order_id" => $pd_id,
                    "warehouse_id" => $updata['warehouse_id'],
                    "goods_type_count" => 1,
                    "goods_count" => $delta,
                	"goods_amount" => $updata['cost_price']*$delta,//扣除优惠之前的  
                	"tax_amount" => $updata['cost_price']*$delta,//税后总金额
                	"total_price" => $updata['cost_price']*$delta,//扣除优惠之后的 --默认优惠为0
                	"discount" => 0,//扣除优惠之后的 --默认优惠为0
                	"operator_id" => $updata['operator_id'],
                    "created" => date("Y-m-d H:i:s"),
                    "modified"=>date("Y-m-d H:i:s"),
                    "check_time"=>date("Y-m-d H:i:s"),
                );
                $stockin_id=M("stockin_order")->add($tmp_data);
                unset($tmp_data);

                //插入入库单明细
                //$stockin_id = $this->query("SELECT stockin_id FROM stockin_order WHERE stockin_no = '%s'", $stockin);
                //$stockin_id = $stockin_id[0]['stockin_id'];

                $tmp_data = array(
                    "stockin_id" => $stockin_id,
                    "src_order_type" => 5,
                    "src_order_detail_id" => 1,
                    "adjust_num" => $delta,
                    "spec_id" => $updata['spec_id'],
                    "position_id" => $position_info['position_id'],                    "cost_price" => $updata['cost_price'],//默认库存成本价
                    "tax_price" => $updata['cost_price'],//默认税后金额=库存成本价
                    "discount" => 0,//默认为0
                    "total_cost" =>$updata['cost_price']*$delta,//总成本
                    "created" => date("Y-m-d H:i:s"),
                    "num" => abs($delta),
                );
                M('stockin_order_detail')->add($tmp_data);

                D('Common/SysProcessBackground')->stockinChange($stockin_id);

                //插入日志

                $tmp_data = array(
                    "order_type" => 1,
                    "order_id" => $stockin_id,
                    "message" => "生成盘点入库单:" . $stockin . "并完成入库",
                    "created" => date("Y-m-d H:i:s"),
                    "operator_id" => $updata['operator_id'],
                );
                M('stock_inout_log')->add($tmp_data);
                unset($tmp_data);
            }

        } catch (\PDOException $e) {
            \Think\Log::write($e->getMessage());
            $data['status'] = 0;
            $data['info'] = self::PDO_ERROR;
            $this->rollback();
            return $data;
        } catch (\Exception $e) {
            \Think\Log::write($e->getMessage());
            $data['status'] = 0;
            $data['info'] = $this::PDO_ERROR;
            $this->rollback();
            return $data;
        }
        $this->commit();
        return $data;
    }*/

    /**
     * 获取选中的数据
     * @param $id
     * @return int|mixed
     * @throws 0
     */
    public function loadSelectedData($id)
    {
        $data = array();
        try {
			$search = array();
			D('Setting/EmployeeRights')->setSearchRights($search,'warehouse_id',2);
            if(empty($search['warehouse_id'])){
                SE('没有仓库权限');
            }
			$right_warehouse_id = $search['warehouse_id'];
			$point_number = get_config_value('point_number',0);
			$stock_num = "CAST(ss.stock_num AS DECIMAL(19,".$point_number.")) stock_num";
            $warehouse_list = $this->query('SELECT GROUP_CONCAT(DISTINCT warehouse_id ORDER BY warehouse_id) warehouse_list from stock_spec ss where ss.warehouse_id in ('.$right_warehouse_id.') and  ss.spec_id = "%d"',$id);
            $warehouse_list = $warehouse_list[0]['warehouse_list'];
            $warehouse_info = UtilDB::getCfgList(array('warehouse'),array('warehouse'=>array('warehouse_id'=>array('in',$warehouse_list))));
            $data['warehouse_info'] = $warehouse_info['warehouse'];
            if(!empty($data['warehouse_info'])){
                $data['stock_data'] = $this->query( "SELECT IFNULL(cwp.position_no,cwp2.position_no) position_no,IFNULL(cwp.rec_id,cwp2.rec_id) position_id,ss.warehouse_id,gs.spec_id AS id, gs.spec_no, gg.goods_no, gg.goods_name,".$stock_num.", ss.cost_price,gg.goods_id, gg.goods_no, gs.spec_name, gs.spec_id, gs.spec_no, gs.spec_code FROM goods_spec as gs LEFT JOIN goods_goods AS gg ON gs.goods_id = gg.goods_id LEFT JOIN stock_spec AS ss ON ss.spec_id = gs.spec_id left join stock_spec_position ssp on ssp.warehouse_id=ss.warehouse_id and ssp.spec_id = ss.spec_id left join cfg_warehouse_position cwp on cwp.rec_id = ssp.position_id left join cfg_warehouse_position cwp2 on cwp2.rec_id = -ss.warehouse_id WHERE gs.spec_id = %d  AND ss.warehouse_id = %d order by ss.warehouse_id limit 1 ",$id,$data['warehouse_info'][0]['id']);// LEFT JOIN stock_spec_detail AS ssd ON ss.rec_id = ssd.stock_spec_id
            }else{
                $data['stock_data'] = array();
            }
        } catch (\PDOException $e) {
            $msg = $e->getMessage();
            \Think\Log::write($msg);
            return 0;
        }
        return $data;
    }

    /**
     * 显示该货品销售中的订单
     * @param $rec_id
     * @return int|mixed
     * @throws 0
     */
    public function showSalesTradeDetaiData($rec_id)
    {//stock_spec_get_selling
        $spec_id = $rec_id;
		$page_info = I('','',C('JSON_FILTER'));
		$limit = '';
		if(isset($page_info['page']))
		{
			$page = intval($page_info['page']);
			$rows = intval($page_info['rows']);
			$limit=" limit ".($page - 1) * $rows . "," . $rows;//分页
		}
        try {
			$point_number = get_config_value('point_number',0);
			$actual_num = "CAST(sto.actual_num AS DECIMAL(19,".$point_number.")) actual_num";
			$total = $this->query("SELECT count(1) as total FROM sales_trade AS st LEFT JOIN  sales_trade_order AS sto ON st.trade_id = sto.trade_id WHERE st.trade_status >20 AND st.trade_status<=55 AND sto.spec_id=".$spec_id);
            $data = $this->query("SELECT st.trade_no, st.trade_status, st.trade_time, sto.spec_no, sto.goods_no, sto.goods_name, sto.spec_code, sto.spec_name, ".$actual_num." FROM sales_trade AS st LEFT JOIN  sales_trade_order AS sto ON st.trade_id = sto.trade_id WHERE st.trade_status >20 AND st.trade_status<=55 AND sto.spec_id=".$spec_id." ".$limit);
        } catch (\PDOException $e) {
            $msg = $e->getMessage();
            \Think\Log::write($msg);
            return 0;
        }
        return array('total'=>$total[0]['total'],'rows'=>$data);
    }
    /*
     * 库存导入
     * 
     * @param $data  导入数据
     * 
     * 
     */
    public function importStockSpec($data,&$result)
    {

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
                $query_warehouse_id_list_sql = "SELECT * FROM tmp_import_detail " ;
                $res_query_warehouse_id_list = $this->query($query_warehouse_id_list_sql);
                \Think\Log::write('导入临时表的数据:'.print_r($res_query_warehouse_id_list,true),\Think\Log::DEBUG);
                $query_warehouse_isset_sql = "UPDATE tmp_import_detail tid LEFT JOIN cfg_warehouse sw ON sw.name=tid.warehouse_name"
                             ."  SET tid.warehouse_id=sw.warehouse_id,tid.status=IF(sw.warehouse_id IS NULL,1,0),tid.result=IF(sw.warehouse_id IS NULL,'失败',''),tid.message=IF(sw.warehouse_id IS NULL,'仓库不存在','') "
                             ."  WHERE tid.status=0 ;";
                $res_query_warehouse_isset = $this->execute($query_warehouse_isset_sql);
                $search = array();
				D('Setting/EmployeeRights')->setSearchRights($search,'warehouse_id',2);
				$right_warehouse_id = $search['warehouse_id'];
				if(empty($right_warehouse_id)) SE('没有新建仓库，请到仓库界面新建仓库');
				$right_warehouse_sql = "UPDATE tmp_import_detail tid left join (select warehouse_id,name from cfg_warehouse where warehouse_id in ($right_warehouse_id)) wd on wd.warehouse_id = tid.warehouse_id"
							 ." set tid.status = if(wd.name is null,1,0),tid.result=if(wd.name is null,'失败',''),tid.message=if(wd.name is null,'仓库不存在','') "
							 ." where tid.status = 0;";
				$right_warehouse = $this->execute($right_warehouse_sql);
				
				$query_goods_spec_isset_sql = "UPDATE tmp_import_detail tid LEFT JOIN goods_spec gs ON gs.spec_no=tid.spec_no"
                                              ." SET tid.spec_id=gs.spec_id,tid.status=IF(gs.spec_id IS NULL,1,0),tid.result= IF(gs.spec_id IS NULL,'失败',''),tid.message=IF(gs.spec_id IS NULL,'商家编码不存在','')"
                                              ." WHERE tid.status=0;";
                $res_query_goods_spec_isset = $this->execute($query_goods_spec_isset_sql);
				
				$info = $this->query("select rec_id,line, spec_no,position_no,warehouse_name from tmp_import_detail where status = 0");
				foreach($info as $value){
					if(!$value['position_no'] == ''){					
						$res_or = D('Stock/StockSpecPosition')->alias('ssp')->join('left join goods_spec gs on gs.spec_id = ssp.spec_id')->join('left join cfg_warehouse cw on ssp.warehouse_id = cw.warehouse_id')->where(array('gs.spec_no'=>$value['spec_no'],'cw.name'=>$value['warehouse_name']))->select();
						if(empty($res_or)){
							$position_sql = "select tid.position_no,cwp.rec_id,sw.warehouse_id from tmp_import_detail tid left join cfg_warehouse sw on tid.warehouse_name = sw.name left join cfg_warehouse_position cwp on cwp.warehouse_id = sw.warehouse_id and cwp.position_no = tid.position_no where tid.position_no = '".$value['position_no']."' and tid.warehouse_name = '".$value['warehouse_name']."'";
							$position = $this->query($position_sql);
							if(!empty($position[0]['position_no']) && empty($position[0]['rec_id'])){
								$updata_data = array();
								$updata_data['warehouse_id'] = $position[0]['warehouse_id'];
								$updata_data['position_no'] = $position[0]['position_no'];
								$updata_data['id'] = 0;
								$updata_data['is_disabled'] = 0;
								$result = D('Setting/warehousePosition')->savePosition($updata_data);
							}
						}
					}					
				}
				
				$query_position_no_isset_sql = "UPDATE tmp_import_detail tid LEFT JOIN cfg_warehouse sw ON sw.name=tid.warehouse_name LEFT JOIN cfg_warehouse_position cwp ON cwp.position_no=tid.position_no and cwp.warehouse_id=sw.warehouse_id"
                                              ." SET tid.position_id=IF(tid.position_no = '',-tid.warehouse_id,cwp.rec_id),tid.status=IF(cwp.rec_id IS NULL AND tid.position_no <> '',1,0),tid.result= IF(cwp.rec_id IS NULL AND tid.position_no <> '','失败',''),tid.message=IF(cwp.rec_id IS NULL AND tid.position_no <> '','货位不存在','')"
                                              ." WHERE tid.status=0;";
                $res_query_position_no_isset = $this->execute($query_position_no_isset_sql);
                $update_stock_spec_besides_num_sql = "INSERT INTO stock_spec(warehouse_id,spec_id,default_position_id,cost_price,`status`,created)"
                                                    ."	(SELECT tid.warehouse_id,tid.spec_id,IF(LENGTH(tid.position_id),tid.position_id,-tid.warehouse_id),IF(LENGTH(tid.price),tid.price,0),1,NOW()"
                                                    ." FROM tmp_import_detail tid"
                                                    ." WHERE tid.status=0 )"
                                                    ." ON DUPLICATE KEY UPDATE"
                                                    ."	default_position_id=default_position_id,"
                                                    ."  `status`=1;"; //--   更新价格的时候,如果存在相关库存信息则不更新价格
                $res_update_stock_spec_besides_num = $this->execute($update_stock_spec_besides_num_sql);
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
            foreach($res_query_warehouse_id_list as $warehouse_key => $warehouse_info)
            {
                
                    $warehouse_id = $warehouse_info['warehouse_id'];
                   /*$update_stock_spec_num_price_sql = "UPDATE tmp_import_detail tid"
                                                       ." LEFT JOIN stock_spec ss ON ss.spec_id=tid.spec_id AND warehouse_id={$warehouse_id} "
                                                       ." LEFT JOIN goods_spec gs ON ss.spec_id=gs.spec_id"
                                                       ." SET	tid.status=0,tid.message=''"
                                                       ."	WHERE tid.status=0;";
                    $res_update_stock_spec_num_price_sql = $this->execute($update_stock_spec_num_price_sql);
                    */
                    try {
                        $test_str_in = 'query stockin count';
                        $query_isset_stockin_sql = "SELECT COUNT(1) as count FROM tmp_import_detail tid WHERE  tid.status=0 AND LENGTH(tid.num)>0 AND (CAST(tid.num AS DECIMAL(19,4))>CAST(tid.stock_num AS DECIMAL(19,4))) AND tid.warehouse_id={$warehouse_id}";
                        $res_query_isset_stockin = $this->query($query_isset_stockin_sql);

                        if( (int)$res_query_isset_stockin[0]['count'] >0)
                        {
                            $exception_type = 'stockin';
                            $this->startTrans();
                            $test_str_in = 'query stockin no';
                            $query_stockin_no_fields = array("FN_SYS_NO('stockin') as stockin_no");
                            $res_query_stockin_no = $this->query("select FN_SYS_NO('stockin') as stockin_no");
                            $res_query_stockin_no = $res_query_stockin_no[0];
                            $test_str_in = 'insert stockin order';
                            $insert_stockin_order_data = array(
                                'stockin_no' => $res_query_stockin_no['stockin_no'],
                                'warehouse_id' => $warehouse_id,
                                'src_order_type' => 9,
                                'status'  => 20,
                                'operator_id' =>$operator_id,
                                'created'=>array('exp','NOW()')
                            );
                            $res_insert_stockin_order = D('Stock/StockInOrder')->insertStockinOrderfForUpdate($insert_stockin_order_data);
                            $test_str_in = 'query stockin order detail';
                            $query_stockin_order_detail_sql = "SELECT {$res_insert_stockin_order} as stockin_id,9 as src_order_type,tid.spec_id,tid.position_id,IF(LENGTH(tid.price),tid.price,0) as src_price,IF(LENGTH(tid.price),tid.price,0) as cost_price,tid.num-tid.stock_num as num,'' as remark,NOW() as created"
                                                             ." FROM tmp_import_detail tid"
                                                             ." LEFT JOIN stock_spec ss ON ss.spec_id=tid.spec_id AND ss.warehouse_id=tid.warehouse_id"
                                                             ." WHERE tid.status=0 AND (CAST(tid.num AS DECIMAL(19,4))>CAST(tid.stock_num AS DECIMAL(19,4))) AND tid.warehouse_id={$warehouse_id};";
                            $res_query_stockin_order_detail = $this->query($query_stockin_order_detail_sql);
                            if(empty($res_query_stockin_order_detail))
                            {
                                E('未知错误,请联系管理员!');
                            }
                            $exception_type = 0;
                            $test_str_in = 'insert stockin order detail';
                            $res_insert_stockin_order_detail = D('Stock/StockinOrderDetail')->insertStockinOrderDetailfForUpdate($res_query_stockin_order_detail);
                            $test_str_in = 'query stockin order num count';
                            $query_statistics_num_fields = array(
                                'COUNT(spec_id) as goods_type_count' ,
                                'SUM(num) as goods_count',
                                'SUM(cost_price*num) as goods_amount',
                                'SUM(cost_price*num) as total_price'
                            );
                            $query_statistics_num_cond = array(
                                'stockin_id'   => $res_insert_stockin_order
                            );
                            $res_query_statistics_num = D('Stock/StockinOrderDetail')->getStockinOrderDetailList($query_statistics_num_fields,$query_statistics_num_cond);
                            $test_str_in = 'update stockin order num count';
                            $res_query_statistics_num = $res_query_statistics_num[0];
                            $update_stockin_order_num_cond = array(
                                'stockin_id' => $res_insert_stockin_order
                            );
                            $res_update_stockin_order_num = D('Stock/StockInOrder')->updateStockinOrder($res_query_statistics_num,$update_stockin_order_num_cond);
                            $test_str_in = 'insert stockin order log';
                            $insert_stock_inout_log_data = array(
                                'order_type'=>1,
                                'order_id'=>$res_insert_stockin_order,
                                'operator_id'=>$operator_id,
                                'operate_type'=>11,
                                'message'=>'初始化入库',
                                'created'=>array('exp','NOW()'),
                                'data'=>0
                            );
                            $res_insert_stock_inout_log = D('Stock/StockInoutLog')->insertStockInoutLog($insert_stock_inout_log_data);
                            $test_str_in = 'insert stockin order submit';
                            $order_detial = array(
                                'id'=>$res_insert_stockin_order,
                                'src_order_type'=>9,
                                'src_order_no'=>0,
                                'warehouse_id'=>$warehouse_id
                            );
                            $check_stockin_result = D('Stock/StockIn')->submitStockInOrder($order_detial,'init');
                            if($check_stockin_result['status']==1){
                                E($check_stockin_result['info']);
                            }
                            $this->commit();
                       
                        }
                    } catch (\PDOException $e) {
                        $msg = $e->getMessage();
                        \Think\Log::write($this->name.'--importStockSpec--'.$test_str_in."--初始化入库PDO错误--".$msg);
                        $this->execute("UPDATE tmp_import_detail tid SET tid.status=1,tid.message='{$msg}',tid.result='失败' WHERE tid.status=0 AND (tid.num>tid.stock_num)  AND tid.warehouse_id={$warehouse_id};");
                        $this->rollback();
                        $result['status'] = 1;
                        $result['msg'] = $msg;
                        $result['data'] = array();
                    } catch (\Exception $e) {
                        $msg = $e->getMessage();
                        $this->execute("UPDATE tmp_import_detail tid SET tid.status=1,tid.message='{$msg}',tid.result='失败' WHERE tid.status=0 AND (tid.num>tid.stock_num)  AND tid.warehouse_id={$warehouse_id};");
                        $this->rollback();
                        $result['status'] = 1;
                        $result['msg'] = $msg;
                        $result['data'] = array();
                        \Think\Log::write($this->name.'--importStockSpec--'.$test_str_in."--初始化入库异常--".$msg);
                    }
                    try {
                        $test_str = 'query stockout count';
                        $query_isset_stockout_sql = "SELECT COUNT(1) as count FROM tmp_import_detail tid WHERE  tid.status=0 AND LENGTH(tid.num)>0 AND (CAST(tid.num AS DECIMAL(19,4))<CAST(tid.stock_num AS DECIMAL(19,4))) AND tid.warehouse_id={$warehouse_id}";
                        $res_query_isset_stockout = $this->query($query_isset_stockout_sql);

                        if((int)$res_query_isset_stockout[0]['count']>0)
                        {
                            $exception_type = 'stockout';
                            $test_str = 'query stockout no';
                            $query_stockout_no_fields = array("FN_SYS_NO('stockout') as stockout_no");
                            $res_query_stockout_no = $this->query("select FN_SYS_NO('stockout') as stockout_no");
                            $test_str = 'insert stockout order';
                            $insert_stockout_order_data = array(
                                'stockout_no' => $res_query_stockout_no[0]['stockout_no'],
                                'warehouse_id' => $warehouse_id,
                                'src_order_type' => 11,
                                'src_order_id' => 0,
                                'warehouse_type' => 0,
                                'status'  => 48,
                                'operator_id' =>$operator_id,
                                'created'=>array('exp','NOW()')
                            );
                            $res_insert_stockout_order = D('Stock/StockOutOrder')->insertStockoutOrderForUpdate($insert_stockout_order_data);
                            $test_str = 'query stockout order detail';
                            $query_stockout_order_detail_sql = "SELECT {$res_insert_stockout_order} as stockout_id,11 as src_order_type,0 as src_order_detail_id,tid.stock_num-tid.num as num,tid.spec_id,gg.goods_id,gg.goods_no,gg.goods_name,gs.spec_no,gs.spec_code,gs.spec_name,IF(LENGTH(tid.price),tid.price,0),-tid.warehouse_id as position_id,'' as remark,NOW() as created"
                                                              ." FROM tmp_import_detail tid"
                                                              ." LEFT JOIN stock_spec ss ON ss.spec_id=tid.spec_id AND ss.warehouse_id=tid.warehouse_id"
                                                              ." LEFT JOIN goods_spec gs ON gs.spec_id=tid.spec_id"
                                                              ." LEFT JOIN goods_goods gg ON gg.goods_id=gs.goods_id"
                                                              ." WHERE tid.status=0 AND (CAST(tid.num AS DECIMAL(19,4))<CAST(tid.stock_num AS DECIMAL(19,4))) AND tid.warehouse_id={$warehouse_id};";
                            $res_query_stockout_order_detail = $this->query($query_stockout_order_detail_sql);

                            if(empty($res_query_stockout_order_detail))
                            {
                                E('未知错误,请联系管理员!');
                            }
                            $test_str = 'insert stockout order detail';
                            $res_insert_stockout_order_detail = D('Stock/StockoutOrderDetail')->insertForUpdateStockoutOrderDetail($res_query_stockout_order_detail);
                            $test_str = 'query stockout order detail num count';
                            $query_statistics_num_fields = array(
                                'COUNT(spec_id) as goods_type_count' ,
                                'SUM(num) as goods_count',
                                'SUM(cost_price*num) as goods_amount'
                            );
                            $query_statistics_num_cond = array(
                                'stockout_id'   => $res_insert_stockout_order
                            );
                            $res_query_statistics_num = D('Stock/StockoutOrderDetail')->getStockoutOrderDetails($query_statistics_num_fields,$query_statistics_num_cond);
                            $test_str = 'update stockout order num count';
                            $res_query_statistics_num = $res_query_statistics_num[0];
                            $update_stockout_order_num_cond = array(
                                'stockout_id' => $res_insert_stockout_order
                            );
                            $res_update_stockout_order_num = D('Stock/StockOutOrder')->updateStockoutOrder($res_query_statistics_num,$update_stockout_order_num_cond);
                            $test_str = 'insert stockout order log';
                            $insert_stock_inout_log_data = array(
                                'order_type'=>2,
                                'order_id'=>$res_insert_stockout_order,
                                'operator_id'=>$operator_id,
                                'operate_type'=>11,
                                'message'=>'初始化出库',
                                'created'=>array('exp','NOW()'),
                                'data'=>0
                            );
                            $res_insert_stock_inout_log = D('Stock/StockInoutLog')->insertStockInoutLog($insert_stock_inout_log_data);
                            $test_str = 'submit stockout order ';
                            $check_stockout_result = D('Stock/StockOutOrder')->submitStockOutOrder($res_insert_stockout_order,'init');
                            if($check_stockout_result['status']==1){
                                E($check_stockout_result['info']);
                            }
                            $this->commit();
                        }
                    } catch (\PDOException $e) {
                        $msg = $e->getMessage();
                        \Think\Log::write($this->name.'--importStockSpec--'.$test_str."--初始化出库PDO错误--".$msg);
                        $this->execute("UPDATE tmp_import_detail tid SET tid.status=1,tid.message='{$msg}',tid.result='失败' WHERE tid.status=0 AND (tid.num>tid.stock_num)  AND tid.warehouse_id={$warehouse_id};");
                        $this->rollback();
                        $result['status'] = 1;
                        $result['msg'] = $msg;
                        $result['data'] = array();
                    } catch (\Exception $e) {
                        $msg = $e->getMessage();
                        $this->execute("UPDATE tmp_import_detail tid SET tid.status=1,tid.message='{$msg}',tid.result='失败' WHERE tid.status=0 AND (tid.num<tid.stock_num)  AND tid.warehouse_id={$warehouse_id};");
                        $this->rollback();
                        \Think\Log::write($this->name.'--importStockSpec--'.$test_str."--初始化出库异常--".$msg);
                        $result['status'] = 1;
                        $result['msg'] = $msg;
                        $result['data'] = array();
                    }
            }
            try {
                $deal_import_data_result = $this->query("select line as id,message,status,result from tmp_import_detail where status =1");
                \Think\Log::write(print_r($deal_import_data_result,true),\Think\Log::INFO);
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
    public function showTabInfoAboutWarehouseStock($id)
    {
        try{
			$search = array();
			D('Setting/EmployeeRights')->setSearchRights($search,'warehouse_id',2);
			$right_warehouse_id = $search['warehouse_id'];
			$sys_config =  get_config_value(array('sys_available_stock','point_number','purchase_alarmstock_open'),array(640,0,0));
            $point_number = $sys_config['point_number'];
            $available_str = D('Stock/StockSpec')->getAvailableStrBySetting($sys_config['sys_available_stock']);
			$stock_num = 'CAST(sum(ss.stock_num) AS DECIMAL(19,'.$point_number.')) stock_num';
			$avaliable_num = 'CAST(sum(IFNULL('.$available_str.',0)) AS DECIMAL(19,'.$point_number.')) avaliable_num';
			$order_num = 'CAST(sum(ss.order_num) AS DECIMAL(19,'.$point_number.')) order_num';
			$sending_num = 'CAST(sum(ss.sending_num) AS DECIMAL(19,'.$point_number.')) sending_num';
			$subscribe_num = 'CAST(sum(ss.subscribe_num) AS DECIMAL(19,'.$point_number.')) subscribe_num';
            $res = $this->query('SELECT ss.spec_id AS id,cw.warehouse_id ,cw.name as warehouse_name,cw.type as warehouse_type,gs.retail_price, gs.market_price,gs.spec_code,'.$stock_num.','.$avaliable_num.','.$order_num.', '.$sending_num.','.$subscribe_num.',cast(IFNULL(IF(SUM(GREATEST(ss.stock_num,0))=0,AVG(ss.cost_price),(SUM(GREATEST(ss.stock_num,0)*ss.cost_price)/SUM(GREATEST(ss.stock_num,0)))),0) as decimal(19,4)) cost_price, cast(IFNULL(SUM(GREATEST(ss.stock_num,0)*ss.cost_price),0) as decimal(19,4)) as all_cost_price,IFNULL(cwp.position_no,cwp2.position_no) position_no,CAST(sum(ss.safe_stock) AS DECIMAL(19,'.$point_number.')) safe_stock,ss.alarm_days,CAST(MAX(ss.safe_stock) as DECIMAL(19,'.$point_number.')) safe_stock,IF(ss.safe_stock>ss.stock_num and '.$sys_config['purchase_alarmstock_open'].',1,0) alarm_flag FROM stock_spec ss left join goods_spec gs on gs.spec_id = ss.spec_id left join cfg_warehouse cw on cw.warehouse_id = ss.warehouse_id left join stock_spec_position ssp on ssp.spec_id = ss.spec_id and ssp.warehouse_id = ss.warehouse_id left join cfg_warehouse_position cwp on cwp.rec_id = ssp.position_id left join cfg_warehouse_position cwp2 on cwp2.rec_id = -ss.warehouse_id where ss.spec_id = "%d" and ss.warehouse_id in ('.$right_warehouse_id.') and cw.is_disabled <>1 group by ss.spec_id,ss.warehouse_id',$id);
            return $data = array('total'=>count($res),'rows'=>$res);
        }catch (\PDOException $e){
            $msg = $e->getMessage();
            \Think\Log::write($this->name.'-showTabInfoAboutWarehouseStock-'.$msg);
            return $data=array('total'=>0,'rows'=>array());
        }
    }
	
	public function exportToExcel($id_list,$search, $type = 'excel'){

		try{
            $search['warehouse_id'] = implode(',',$search['warehouse_id']);
            $ware = $search['warehouse_id'];
            $comma_pos = strpos($ware,',');
            $all_pos = strpos($ware,'all');
            $comma_count = substr_count($ware,',');
            if($all_pos!==false&&strlen($ware)>3){//包含all的多选条件清除all
                $ware=str_replace('all,', '', $ware);
                $ware=str_replace(',all', '', $ware);
                $search['warehouse_id'] = $ware;
            }else if($all_pos!==false&&strlen($ware)==3){
                D('Setting/EmployeeRights')->setSearchRights($search,'warehouse_id',2);
            }
			//D('Setting/EmployeeRights')->setSearchRights($search,'warehouse_id',2);
            $sys_available_stock =  get_config_value('sys_available_stock',640);
			$available_str = D('Stock/StockSpec')->getAvailableStrBySetting($sys_available_stock);
            $creator=session('account');

			$where_goods_spec = '';
			$where_goods_goods = '';
			set_search_form_value($where_goods_spec, 'deleted', 0, 'gs_1', 2, ' AND ');
			set_search_form_value($where_goods_goods, 'deleted', 0, 'gg_1', 2, ' AND ');
			$where_stock_spec = '';
			$where_left_join_goods_class='';

            $where = "true ";
            foreach ($search as $k => $v) {
                if ($v === "")
                    continue;
                switch ($k) {
                   case 'spec_no'://商家编码      goods_spec
                    set_search_form_value($where_goods_spec, $k, $v, 'gs_1', 10, ' AND ');
                    break;
                case 'barcode'://条形码   goods_spec
                    set_search_form_value($where_goods_spec, $k, $v, 'gs_1', 1, ' AND ');
                    break;
                case 'goods_no': //货品编号     goods_gooods
                    set_search_form_value($where_goods_goods, $k, $v, 'gg_1', 1, ' AND ');
                    break;
                case 'goods_name'://货品名称  goods_gooods
                    set_search_form_value($where_goods_goods, $k, $v, 'gg_1', 10, ' AND ');
                    break;
                case 'class_id'://分类         goods_gooods
                    $where_left_join_goods_class=set_search_form_value($where_goods_goods, $k, $v, 'gg_1', 7, ' AND ');
                    break;
                case 'brand_id'://品牌        goods_gooods
                    set_search_form_value($where_goods_goods, $k, $v, 'gg_1', 2, ' AND ');
                    break;
                case 'warehouse_id'://仓库   stock_spec

                    set_search_form_value($where_stock_spec, $k, $v, 'ss_1', 2, ' AND ');
                    break;

                 default:
                    continue;
                }
            }
            $sql_sel_limit='SELECT ss_1.rec_id FROM stock_spec ss_1 ';
            $flag=false;
            $sql_where='';
            if(!empty($where_goods_spec)||!empty($where_goods_goods))
            {
                $sql_where .= ' LEFT JOIN goods_spec gs_1 ON gs_1.spec_id=ss_1.spec_id ';
            }
            if(!empty($where_goods_goods))
            {
                $sql_where .= ' LEFT JOIN goods_goods gg_1 ON gg_1.goods_id=gs_1.goods_id ';
                $sql_where .= $where_left_join_goods_class;
            }
			$warehouse_where = str_replace('ss_1','ssps',$where_stock_spec);
            connect_where_str($sql_where, $where_goods_spec, $flag);
            connect_where_str($sql_where, $where_goods_goods, $flag);
            connect_where_str($sql_where, $where_stock_spec, $flag);
            $sql_sel_limit.=$sql_where;
			$where_date = date('Y-m-d',time());
			$config_data = get_config_value('stock_out_num',30);
			$sys_config =  get_config_value(array('point_number'),array(0));
			$point_number = $sys_config['point_number'];
            if(empty($id_list)){
                $sql_fields_str='SELECT gs.spec_no,gg.goods_no,gg.goods_name,"" as position_no,gg.short_name,gs.spec_code,gs.spec_name,gs.barcode,gb.brand_name brand_id,gc.class_name class_id,sum(ss.stock_num) stock_num,cast(IFNULL(IF(SUM(GREATEST(stock_num,0))=0,AVG(ss.cost_price),(SUM(GREATEST(stock_num,0)*ss.cost_price)/SUM(GREATEST(stock_num,0)))),0) as decimal(19,4)) cost_price, cast(IFNULL(SUM(GREATEST(ss.stock_num,0)*ss.cost_price),0) as decimal(19,4)) as all_cost_price,sum(IFNULL('.$available_str.',0)) avaliable_num, sum(ss.order_num) order_num, sum(ss.sending_num) sending_num, gs.retail_price, gs.market_price,sum(ss.subscribe_num) subscribe_num,sum(ss.safe_stock) safe_stock,sum(ss.purchase_num) purchase_num,sum(ss.purchase_arrive_num) purchase_arrive_num,ss.spec_id,CAST(ssps_1.seven_outnum AS DECIMAL(19,'.$point_number.')) as seven_outnum,CAST(ssps_2.fourteen_outnum AS DECIMAL(19,'.$point_number.')) as fourteen_outnum,CAST(ssps_3.recent_outnum AS DECIMAL(19,'.$point_number.')) as recent_outnum FROM stock_spec ss';
                $sql_left_join_str='LEFT JOIN goods_spec gs ON ss.spec_id = gs.spec_id LEFT JOIN goods_goods gg ON gg.goods_id = gs.goods_id LEFT JOIN goods_class gc ON gc.class_id = gg.class_id LEFT JOIN goods_brand gb ON gb.brand_id = gg.brand_id ';
                if($comma_pos===false&&$all_pos===false||$all_pos!==false&&$comma_count==1){
                    $sql_fields_str='SELECT gs.spec_no,gg.goods_no,gg.goods_name,IFNULL(cwp.position_no, cwp2.position_no) position_no,gg.short_name,gs.spec_code,gs.spec_name,gs.barcode,gb.brand_name brand_id,gc.class_name class_id,sum(ss.stock_num) stock_num,cast(IFNULL(IF(SUM(GREATEST(ss.stock_num,0))=0,AVG(ss.cost_price),(SUM(GREATEST(ss.stock_num,0)*ss.cost_price)/SUM(GREATEST(ss.stock_num,0)))),0) as decimal(19,4)) cost_price, cast(IFNULL(SUM(GREATEST(ss.stock_num,0)*ss.cost_price),0) as decimal(19,4)) as all_cost_price,sum(IFNULL('.$available_str.',0)) avaliable_num, sum(ss.order_num) order_num, sum(ss.sending_num) sending_num, gs.retail_price, gs.market_price,sum(ss.subscribe_num) subscribe_num,sum(ss.safe_stock) safe_stock,sum(ss.purchase_num) purchase_num,sum(ss.purchase_arrive_num) purchase_arrive_num,ss.spec_id,CAST(ssps_1.seven_outnum AS DECIMAL(19,'.$point_number.')) as seven_outnum,CAST(ssps_2.fourteen_outnum AS DECIMAL(19,'.$point_number.')) as fourteen_outnum,CAST(ssps_3.recent_outnum AS DECIMAL(19,'.$point_number.')) as recent_outnum FROM stock_spec ss';
                    $sql_left_join_str='LEFT JOIN goods_spec gs ON ss.spec_id = gs.spec_id LEFT JOIN goods_goods gg ON gg.goods_id = gs.goods_id LEFT JOIN goods_class gc ON gc.class_id = gg.class_id LEFT JOIN goods_brand gb ON gb.brand_id = gg.brand_id LEFT JOIN cfg_warehouse cw ON cw.warehouse_id = ss.warehouse_id LEFT JOIN stock_spec_position ssp ON ssp.spec_id = ss.spec_id AND ssp.warehouse_id = ss.warehouse_id LEFT JOIN cfg_warehouse_position cwp ON cwp.rec_id = ssp.position_id LEFT JOIN cfg_warehouse_position cwp2 ON cwp2.rec_id = -ss.warehouse_id ';
                }
				$sql_left_join_str .= " LEFT JOIN (select SUM(ssps.num-ssps.refund_num-ssps.return_num) AS seven_outnum,ssps.spec_id from stat_daily_sales_spec_warehouse ssps where ssps.sales_date >= date_add('{$where_date}',INTERVAL -7 DAY) and ssps.sales_date < '{$where_date}' {$warehouse_where}  group by ssps.spec_id) ssps_1 on ssps_1.spec_id =ss.spec_id ";
				$sql_left_join_str .= " LEFT JOIN (select SUM(ssps.num-ssps.refund_num-ssps.return_num) AS fourteen_outnum,ssps.spec_id from stat_daily_sales_spec_warehouse ssps where ssps.sales_date >= date_add('{$where_date}',INTERVAL -14 DAY) and ssps.sales_date < '{$where_date}' {$warehouse_where}  group by ssps.spec_id) ssps_2 on ssps_2.spec_id =ss.spec_id ";
				$sql_left_join_str .= " LEFT JOIN (select SUM(ssps.num-ssps.refund_num-ssps.return_num) AS recent_outnum,ssps.spec_id from stat_daily_sales_spec_warehouse ssps where ssps.sales_date >= date_add('{$where_date}',INTERVAL -{$config_data} DAY) and ssps.sales_date < '{$where_date}' {$warehouse_where}  group by ssps.spec_id) ssps_3 on ssps_3.spec_id =ss.spec_id ";
	
                $sql=$sql_fields_str.' INNER JOIN('.$sql_sel_limit.') ss_2 ON ss.rec_id=ss_2.rec_id '.$sql_left_join_str.' GROUP BY ss.spec_id ORDER BY ss.spec_id DESC';
			}else{
                $sql_fields_str='SELECT gs.spec_no,gg.goods_no,gg.goods_name,"" as position_no,gg.short_name,gs.spec_code,gs.spec_name,gs.barcode,gb.brand_name brand_id,gc.class_name class_id,sum(ss.stock_num) stock_num,cast(IFNULL(IF(SUM(GREATEST(stock_num,0))=0,AVG(ss.cost_price),(SUM(GREATEST(stock_num,0)*ss.cost_price)/SUM(GREATEST(stock_num,0)))),0) as decimal(19,4)) cost_price, cast(IFNULL(SUM(GREATEST(ss.stock_num,0)*ss.cost_price),0) as decimal(19,4)) as all_cost_price,sum(IFNULL('.$available_str.',0)) avaliable_num, sum(ss.order_num) order_num, sum(ss.sending_num) sending_num, gs.retail_price, gs.market_price,sum(ss.subscribe_num) subscribe_num,sum(ss.safe_stock) safe_stock,sum(ss.purchase_num) purchase_num,sum(ss.purchase_arrive_num) purchase_arrive_num,ss.spec_id,CAST(ssps_1.seven_outnum AS DECIMAL(19,'.$point_number.')) as seven_outnum,CAST(ssps_2.fourteen_outnum AS DECIMAL(19,'.$point_number.')) as fourteen_outnum,CAST(ssps_3.recent_outnum AS DECIMAL(19,'.$point_number.')) as recent_outnum FROM stock_spec ss';
                $sql_left_join_str=' LEFT JOIN goods_spec gs ON ss.spec_id = gs.spec_id LEFT JOIN goods_goods gg ON gg.goods_id = gs.goods_id LEFT JOIN goods_class gc ON gc.class_id = gg.class_id LEFT JOIN goods_brand gb ON gb.brand_id = gg.brand_id ';
                //$sql=$sql_fields_str.$sql_left_join_str.' where ss.spec_id in ('.$id_list.')'.' GROUP BY ss.spec_id';
                if($comma_pos===false&&$all_pos===false||$all_pos!==false&&$comma_count==1) {
                    $sql_fields_str='SELECT gs.spec_no,gg.goods_no,gg.goods_name,IFNULL(cwp.position_no, cwp2.position_no) position_no,gg.short_name,gs.spec_code,gs.spec_name,gs.barcode,gb.brand_name brand_id,gc.class_name class_id,sum(ss.stock_num) stock_num,cast(IFNULL(IF(SUM(GREATEST(ss.stock_num,0))=0,AVG(ss.cost_price),(SUM(GREATEST(ss.stock_num,0)*ss.cost_price)/SUM(GREATEST(ss.stock_num,0)))),0) as decimal(19,4)) cost_price, cast(IFNULL(SUM(GREATEST(ss.stock_num,0)*ss.cost_price),0) as decimal(19,4)) as all_cost_price,sum(IFNULL('.$available_str.',0)) avaliable_num, sum(ss.order_num) order_num, sum(ss.sending_num) sending_num, gs.retail_price, gs.market_price,sum(ss.subscribe_num) subscribe_num,sum(ss.safe_stock) safe_stock,sum(ss.purchase_num) purchase_num,sum(ss.purchase_arrive_num) purchase_arrive_num,ss.spec_id,CAST(ssps_1.seven_outnum AS DECIMAL(19,'.$point_number.')) as seven_outnum,CAST(ssps_2.fourteen_outnum AS DECIMAL(19,'.$point_number.')) as fourteen_outnum,CAST(ssps_3.recent_outnum AS DECIMAL(19,'.$point_number.')) as recent_outnum FROM stock_spec ss';
                    $sql_left_join_str = ' LEFT JOIN goods_spec gs ON ss.spec_id = gs.spec_id LEFT JOIN goods_goods gg ON gg.goods_id = gs.goods_id LEFT JOIN goods_class gc ON gc.class_id = gg.class_id LEFT JOIN goods_brand gb ON gb.brand_id = gg.brand_id LEFT JOIN cfg_warehouse cw ON cw.warehouse_id = ss.warehouse_id LEFT JOIN stock_spec_position ssp ON ssp.spec_id = ss.spec_id AND ssp.warehouse_id = ss.warehouse_id LEFT JOIN cfg_warehouse_position cwp ON cwp.rec_id = ssp.position_id LEFT JOIN cfg_warehouse_position cwp2 ON cwp2.rec_id = -ss.warehouse_id ';
                }
				$sql_left_join_str .= " LEFT JOIN (select SUM(ssps.num-ssps.refund_num-ssps.return_num) AS seven_outnum,ssps.spec_id from stat_daily_sales_spec_warehouse ssps where ssps.sales_date >= date_add('{$where_date}',INTERVAL -7 DAY) and ssps.sales_date < '{$where_date}' {$warehouse_where}  group by ssps.spec_id) ssps_1 on ssps_1.spec_id =ss.spec_id ";
				$sql_left_join_str .= " LEFT JOIN (select SUM(ssps.num-ssps.refund_num-ssps.return_num) AS fourteen_outnum,ssps.spec_id from stat_daily_sales_spec_warehouse ssps where ssps.sales_date >= date_add('{$where_date}',INTERVAL -14 DAY) and ssps.sales_date < '{$where_date}' {$warehouse_where}  group by ssps.spec_id) ssps_2 on ssps_2.spec_id =ss.spec_id ";
				$sql_left_join_str .= " LEFT JOIN (select SUM(ssps.num-ssps.refund_num-ssps.return_num) AS recent_outnum,ssps.spec_id from stat_daily_sales_spec_warehouse ssps where ssps.sales_date >= date_add('{$where_date}',INTERVAL -{$config_data} DAY) and ssps.sales_date < '{$where_date}' {$warehouse_where}  group by ssps.spec_id) ssps_3 on ssps_3.spec_id =ss.spec_id ";

                $sql=$sql_fields_str.' INNER JOIN('.$sql_sel_limit.') ss_2 ON ss.rec_id=ss_2.rec_id '.$sql_left_join_str.' where ss.spec_id in ('.$id_list.')'.' GROUP BY ss.spec_id ORDER BY ss.spec_id DESC';
            }
            $data = $this->query($sql);

            $num = workTimeExportNum($type);
				if(count($data) > $num){
                    if($type == 'csv'){
                        SE(self::EXPORT_CSV_ERROR);
                    }
					SE(self::OVER_EXPORT_ERROR);
				}
            $excel_header = D('Setting/UserData')->getExcelField('Stock/StockManagement','StockManagement');			
            $title = '库存';
            $filename = '库存';
			//$width_list = array('20','20','20','20','20','20','20','20','20','20','20','20','20','20','20','20','20');
            foreach ($excel_header as $v) {
                $width_list[]=20;
            }
            if($type == 'csv') {
                $ignore_arr = array('商家编码','货品编号','货品名称','货品简称','规格码','规格名称','条形码','品牌','分类','默认货位');
                ExcelTool::Arr2Csv($data, $excel_header, $filename, $ignore_arr);
            }else {
                ExcelTool::Arr2Excel($data, $title, $excel_header, $width_list, $filename, $creator);
            }
		}catch(\PDOException $e){
            \Think\Log::write($e->getMessage());
            SE(parent::PDO_ERROR);
        }catch(BusinessLogicException $e){
            SE($e->getMessage());
        }catch(\Exception $e){
            \Think\Log::write($e->getMessage());
            SE(parent::PDO_ERROR);
        }
		
	}

    public function getStockSpecCount(){
        try{
//            $stock_num=D('Stock_spec')->alias('ss')->field('rec_id')->join('left join goods_spec gs on ss.spec_id=gs.spec_id')->where(array('gs.deleted'=>0))->select();
//            return count($stock_num);
            $stock_num=D('Stock_spec')->alias('ss')->field('rec_id')->join('left join goods_spec gs on ss.spec_id=gs.spec_id')->where(array('gs.deleted'=>0))->count();
            return $stock_num;
        }catch (Exception $e){
            \Think\Log::write($e->getMessage());
            SE(parent::PDO_ERROR);
        }
    }
	public function importPrice($data,&$result){
		try{
			$operator_id = get_operator_id();
			$check_fields_ar = array('adjust_price','spec_no','warehouse_name');
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
			foreach($data as $item){
				$insert_index = $this->execute("insert into tmp_import_detail(`spec_no`,`warehouse_name`,`adjust_price`,`status`,`result`,`message`,`line`)values('{$item['spec_no']}','{$item['warehouse_name']}',".(float)$item['adjust_price'].",".(int)$item['status'].",'{$item['result']}','{$item['message']}',".(int)$item['line'].")");
			}
			$check_warehouse_sql = "update tmp_import_detail tid left join cfg_warehouse cw on cw.name = tid.warehouse_name set ".
								" tid.warehouse_id = cw.warehouse_id ,tid.status=if(cw.warehouse_id is null ,1,0),tid.message=if(cw.warehouse_id is null,'仓库不存在',''),tid.result=if(cw.warehouse_id is null,'失败','') where tid.status=0";
			$check_warehouse = $this->execute($check_warehouse_sql);
			
			$search = array();
			D('Setting/EmployeeRights')->setSearchRights($search,'warehouse_id',2);
			$right_warehouse_id = $search['warehouse_id'];
			$right_warehouse_sql = "UPDATE tmp_import_detail tid left join (select warehouse_id,name from cfg_warehouse where warehouse_id in ($right_warehouse_id)) wd on wd.warehouse_id = tid.warehouse_id"
							 ." set tid.status = if(wd.name is null,1,0),tid.result=if(wd.name is null,'失败',''),tid.message=if(wd.name is null,'仓库不存在','') "
							 ." where tid.status = 0;";
			$right_warehouse = $this->execute($right_warehouse_sql);
			$check_spec_no_sql = "update tmp_import_detail tid left join goods_spec gs on gs.spec_no = tid.spec_no set ".
							"tid.spec_id = gs.spec_id ,tid.status = if(gs.spec_id is null ,1,0),tid.result=if(gs.spec_id is null ,'失败',''),tid.message = if(gs.spec_id is null,'商家编码不存在','') where tid.status = 0";
			$check_spec_no = $this->execute($check_spec_no_sql);
			
		
			$check_sql = "update tmp_import_detail tid left join stock_spec ss on ss.warehouse_id = tid.warehouse_id and ss.spec_id=tid.spec_id set ".
								"tid.stock_num = ss.stock_num,tid.remark='', tid.status=if(ss.rec_id is null ,1,0),tid.message=if(ss.rec_id is null,'该仓库中该商品编码不存在',''),tid.result=if(ss.rec_id is null,'失败','') where tid.status=0";
			$check = $this->execute($check_sql);
			$data_info = $this->query("select warehouse_id from tmp_import_detail where status =0 group by warehouse_id");
			if(empty($data_info)){
				$error_info = $this->query("select line as id,spec_no,status,message,result from tmp_import_detail where status=1");
				if(!empty($error_info)){
					$result['status'] = 2;
					$result['data'] = $error_info;
				}
				return;
			}
			
			$price_data = $this->query("select stock_num,adjust_price,warehouse_id,spec_id,remark from tmp_import_detail where status = 0");
			D('Stock/StockSpec')->fastAdjustCostPrice($price_data);
			
			//$modifiy_sql ="update tmp_import_detail tid left join stock_spec ss on tid.spec_id=ss.spec_id and ss.warehouse_id=tid.warehouse_id set ".
				//	  " ss.cost_price = tid.cost_price where tid.status=0";
			//$modifiy = $this->execute($modifiy_sql);
		}catch(BusinessLogicException $e){
            $msg = $e->getMessage();
            \Think\Log::write($this->name.'--importPrice--'.$msg);
            $result['status'] = 1;
            $result['msg'] = $msg;
            return;
        }catch(\PDOException $e){
			$msg = $e->getMessage();
            \Think\Log::write($this->name.'--importPrice--'.$msg);
            $result['status'] = 1;
            $result['msg'] = $msg;
			return;
		}catch(\Exception $e){
			$msg = $e->getMessage();
            \Think\Log::write($this->name.'--importPrice--'.$msg);
            $result['status'] = 1;
            $result['msg'] = $msg;
            return;
		}
		try{
			$deal_import_data_result = $this->query("select line as id,status,spec_no,message,result from tmp_import_detail where status=1");
			if(!empty($deal_import_data_result)){
				$result['status'] = 2;
				$result['data'] = $deal_import_data_result;
			}
		} catch (\PDOException $e) {
            $msg = $e->getMessage();
            $result['status'] = 1;
            $result['msg'] = '导入信息获取失败,联系管理员';
            $result['data'] = array();
            \Think\Log::write($this->name.'--importPrice--'.$msg);
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            $result['status'] = 1;
            $result['msg'] = '导入信息获取失败,联系管理员';
            $result['data'] = array();
            \Think\Log::write($this->name.'--importPrice--'.$msg);
        }
	}

    public function refreshPlatformStock($rec_id,&$list, $shop_id)
    {
        try{
            $operator_id = get_operator_id();
            $stock_spec_db = M('stock_spec');
            $goods_spec_db = M('goods_spec');
            $stock_log_db = M('stock_spec_log');
            $api_goods_spec_db = M('api_goods_spec');
            $where = $rec_id ==''?' true ':"ss.spec_id IN ($rec_id)";
            //先查出来所选的库存管理中的单品结果集
            $stock_spec_res = $stock_spec_db->alias('ss')->field('ss.rec_id,ss.spec_id,ss.stock_num')->join('LEFT JOIN goods_spec gs ON ss.spec_id=gs.spec_id')
                ->where($where)->where(array('gs.deleted'=>0))->group('ss.spec_id')->fetchSql(false)->select();
            //遍历结果集筛选掉有多个仓库的单品跟对应多个平台货品的单品
            foreach($stock_spec_res as $k=>$v){
                $count_spec_res = $stock_spec_db->alias('ss')->join('LEFT JOIN cfg_warehouse cw ON ss.warehouse_id=cw.warehouse_id')->where(array('ss.spec_id'=>$v['spec_id'],'cw.is_disabled'=>0))->count();
                $spec_res = $goods_spec_db->field('spec_no')->where(array('spec_id'=>$v['spec_id']))->find();
                //过滤掉对应多个平台货品的单品
                $count_platform_res = $api_goods_spec_db->where(array('match_target_id'=>$v['spec_id'],'match_target_type'=>1,'shop_id'=>$shop_id))->count();
                if($count_platform_res>1 || $count_platform_res==0){
                    $list[] = array('spec_no'=>$spec_res['spec_no'],'info'=>'该商家编码对应多个或零个平台货品,刷新失败');
                    unset($stock_spec_res[$k]);
                    continue;
                }
                if($count_spec_res>1){
                    $list[] = array('spec_no'=>$spec_res['spec_no'],'info'=>'该商家编码的单品存在多个仓库库存,刷新失败');
                    unset($stock_spec_res[$k]);
                    continue;
                }
                //过滤完以后剩余的$stock_spec_res数组中的数据是可以进行刷新平台库存的
                //逐个查询所更新的货品的平台库存
                if(!empty($stock_spec_res)){
                    $platform_stock_num = $api_goods_spec_db->field('stock_num')->where(array('match_target_id'=>$v['spec_id']))->fetchSql(false)->find();
                    $data = $platform_stock_num['stock_num'];
                    if($data == null){
                        \Think\Log::write($this->name.'平台库存量结果为null,spec_id :'.print_r($v['spec_id'],true));
                        SE(parent::PDO_ERROR);
                    }
                    $sql = "UPDATE stock_spec ss LEFT JOIN cfg_warehouse cw ON cw.warehouse_id=ss.warehouse_id SET ss.stock_num=%d WHERE ss.spec_id=%d AND cw.is_disabled=0";
                    $stock_spec_db->startTrans();
                    $result = $stock_spec_db->execute($sql,$data,$v['spec_id']);
                    $stock_log_arr = array(
                        'operator_id' => $operator_id,
                        'stock_spec_id' => $v['rec_id'],
                        'operator_type' =>4,// 4 是刷新平台库存  新增
                        'num' => $data,
                        'stock_num' =>$v['stock_num'],
                        'message'=> "刷新为平台库存"
                    );
                    $stock_log_db->data($stock_log_arr)->add();
                    $stock_spec_db->commit();
                }
            }

        }catch (\PDOException $e)
        {
            $stock_spec_db->rollback();
            $msg = $e->getMessage();
            \Think\Log::write($this->name.'--refreshPlatformStock PDO--'.$msg);

        }catch (\Exception $e)
        {
            $stock_spec_db->rollback();
            $msg = $e->getMessage();
            \Think\Log::write($this->name.'--refreshPlatformStock--'.$msg);
        }
    }
    public function getShopList($rec_id){
        try{
            $specid_arr = array();
            $stock_spec_db = M('stock_spec');
            $api_goods_spec_db = M('api_goods_spec');
            $rec_where = $rec_id ==''?' true ':"spec_id IN ($rec_id)";
            $stock_spec_res = $stock_spec_db->field('spec_id')->where($rec_where)->group('spec_id')->fetchSql(false)->select();
            $specid_arr = array_column($stock_spec_res, 'spec_id');
            if(empty($specid_arr)){
                $res['status'] = 0;
                $res['data'] = array();
                return $res;
            }
            $specid_list = implode(',', $specid_arr);
            $get_shop_list = UtilDB::getCfgRightList(array('shop'));
            $shop_right_list = array_column($get_shop_list['shop'], 'id');
            if(empty($shop_right_list)){
                $res['status'] = 0;
                $res['data'] = array();
                return $res;
            }
            $shop_right_str = implode(',', $shop_right_list);
            $spec_where = "ags.match_target_id IN ($specid_list) AND ags.shop_id IN ($shop_right_str) ";
            $res['status'] = 0;
            $res['data'] = $api_goods_spec_db->alias('ags')->field('cs.shop_id AS id,cs.shop_name AS name')
                            ->join('LEFT JOIN cfg_shop cs ON ags.shop_id=cs.shop_id')->where($spec_where)
                            ->where(array('ags.match_target_type'=>1,'cs.is_disabled'=>0))->group('cs.shop_id')->select();
        }catch (\PDOException $e)
        {
            $msg = $e->getMessage();
            $res['status'] = 1;
            $res['data'] = array();
            \Think\Log::write('StockManagementController--getShopList PDO--'.$msg);
            SE('店铺列表获取失败,请联系管理员');

        }catch (\Exception $e)
        {
            $msg = $e->getMessage();
            $res['status'] = 1;
            $res['data'] = array();
            \Think\Log::write('StockManagementController--getShopList--'.$msg);
            SE('店铺列表获取失败,请联系管理员');
        }
        return $res;
    }
	public function show_total_price($page=1, $rows=20, $search = array(), $sort = 'id', $order = 'desc'){
		try{
			$limit = ($page-1)*$rows.','.$rows;
			$res = D('Stock/StockSpec')->alias('ss')->fetchSql(false)->field('sum(ss.stock_num) stock_num,sum(ss.cost_price) cost_price,ss.warehouse_id,cw.name as warehouse_name,sum(refund_num) refund_num,sum(safe_stock) safe_stock,count(rec_id) spec_num')->join('left join cfg_warehouse cw on cw.warehouse_id = ss.warehouse_id')->where(array('ss.stock_num'=>array('gt',0),'cw.is_disabled'=>array('neq',1)))->group('ss.warehouse_id')->select();
			$result = array('total'=>count($res),'rows'=>$res);
		}catch (\PDOException $e)
        {
            $msg = $e->getMessage();
            
            $result = array('total'=>'','rows'=>array());
            \Think\Log::write('StockManagement--show_total_price PDO--'.$msg);
           
        }catch (\Exception $e)
        {  
            $result = array('total'=>'','rows'=>array());
            \Think\Log::write('StockManagement--show_total_price--'.$msg);
        }
		return $result;
	} 
}
