<?php
namespace Stock\Model;
use Common\Common\ExcelTool;
use Think\Exception\BusinessLogicException;
use Common\Common\DatagridExtention;
use Common\Common\UtilTool;
use Think\Exception;
use Think\Model;

class SalesStockOutModel extends StockOutOrderModel{
	public function revertSalesStockout($stockout_id,$form,&$fail=array(),&$success=array(),$is_reason=true,$type='stockSalesPrint')
	{
		try{
			$this->startTrans();
			$result = $this->revertSalesStockoutNoTrans($stockout_id,$form,$fail,$success,$is_reason,$type);
			if(!empty($fail)){$this->rollback();}
			$this->commit();
			return $result;
		}catch(BusinessLogicException $e){
			$msg = $e->getMessage();
			$this->rollback();
			\Think\Log::write($this->name.'-revertSalesStockout-'.$msg);
			return false;
		}catch(\PDOException $e){
			$msg = $e->getMessage();
			$this->rollback();
			\Think\Log::write($this->name.'-revertSalesStockout-'.$msg);
			return false;
		}catch(\Exception $e){
			$msg = $e->getMessage();
			$this->rollback();
			\Think\Log::write($this->name.'-revertSalesStockout-'.$msg);
			return false;
		}
	}
	public function revertSalesStockoutNoTrans($stockout_id,$form,&$fail=array(),&$success=array(),$is_reason=true,$type='stockSalesPrint')
	{
		try
		{
			$config_values = array(
				'stockout_must_checkout' => 0,
				'stockout_disable_revert' => 1, //禁止撤销出库
				'stockout_consign_disable_revert' =>1, //发货后禁止撤销发货
			);
			$debug_point_str = "salesstockout-revertSalesStockoutNoTrans-printconfig";
			\Think\Log::write($debug_point_str.print_r($config_values,true),\Think\Log::INFO);
			//是否存在驳回原因
			$debug_point_str = 'salesstockout-revertSalesStockoutNoTrans-query_reason_title';
			if($is_reason){
				$reason_fields = array("title");
				$reason_cond = array(
					'reason_id' => array('eq',$form['reason_id']),
				);
				$reason_info = D('Setting/CfgOperReason')->getOperReason($reason_fields,$reason_cond);
				$res_query_reason = $reason_info[0];

				if(empty($res_query_reason))
				{
					SE("驳回原因不存在");
				}
			}
			$debug_point_str = 'salesstockout-revertSalesStockoutNoTrans-begin_query_info';
			$stockout_fields = "warehouse_type,stockout_id,stockout_no,src_order_id,src_order_type,src_order_no,`status`,"
				."consign_status,wms_status,checkouter_id,is_allocated,warehouse_id,logistics_id,"
				."logistics_no,weight,post_cost,batch_no,customer_id,is_stalls";//18
			$stockout_cond = array('stockout_id'=>array('eq',$stockout_id));
			$res_so_info = $this->getStockoutOrderLock($stockout_fields,$stockout_cond);
			if (empty($res_so_info))
			{
				SE("单据不存在");
			}
			$query_stockout_id = $res_so_info['stockout_id'];
			$query_warehouse_id = $res_so_info['warehouse_id'];
			$query_trade_id = $res_so_info['src_order_id'];
			$sales_trade_info = D('Trade/SalesTrade')->field(array('platform_id','trade_from'))->where(array('trade_id'=>$query_trade_id))->find();

			if ((int)$res_so_info['status'] < 52)
			{
				SE("订单状态不是已审核，禁止驳回");
			}
			if ((int)$sales_trade_info['trade_from'] == 4)
			{
				SE("现款销售的单子禁止驳回");
			}
			if($config_values['stockout_must_checkout'] && (int)$res_so_info['checkouter_id'] ==0)
			{
				SE('出库单'.$res_so_info['stockout_no'].'必须签出才可操作');
			}elseif((int)$res_so_info['checkouter_id'] && (int)$res_so_info['checkouter_id'] <> $form['operator_id'])
			{
				SE("出库单已经被其他人签出");
			}
			//---子母单  未添加   单据为顺丰

			if((int)$res_so_info['status'] == 5)
			{
				SE("出库单已经取消");
			}
			if((int)$res_so_info['status'] == 54)
			{
				SE("正在获取面单号,请稍后");
			}
			if ((int)$res_so_info['src_order_type'] <> 1)
			{
				SE("不是销售出库单");
			}
			/* if(((int)$res_so_info['consign_status'] & 4) && $config_values['stockout_disable_revert'])//可能在加配置的时候来判断是否
             {
                 SE("已发货，禁止驳回审核");
             }*/
			if( (int)$res_so_info['status'] == 95 )
			{
				SE("请先撤销出库");
			}
			if( (int)$res_so_info['status'] > 95 && (int)$sales_trade_info['platform_id'])
			{
				SE("线上订单已完成，系统禁止驳回审核");
			}
			if( (int)$res_so_info['status'] > 95 && !(int)$sales_trade_info['platform_id'])
			{
				SE("请先撤销出库");
			}
			if ((int)$res_so_info['is_stalls']==1&&$form['is_force']<>1&&$is_reason)
			{
				$stalls_sort_status = M('stalls_less_goods_detail')->fetchSql(false)->field('sort_status')->where(array('trade_id'=>$query_trade_id))->select();
				$msg = '';
				$is_break= false;
				foreach($stalls_sort_status as $v){
					switch($v['sort_status']){
						case '0' ://档口货品未分拣
							$msg = '存在未分拣的货品，请继续分拣 或 进行货品移位';
							$is_break= true;
							break;
						case '2' ://档口货品分拣完成
							$msg = '存在未分拣的货品，请继续分拣 或 进行货品移位';
							$is_break= true;
							break;
					}
					if($is_break) break;
				}
				if($msg != ''){
					$fail[] = array(
						'stock_id' => $query_stockout_id,
						'stock_no' => $res_so_info['stockout_no'],
						'msg' =>$msg,
						'solve_way' =>'<a href="javascript:void(0)"  name = "button_save" class="easyui-linkbutton" data-options="iconCls:\'icon-save\'" onclick="'.$type.'.continueSort()">继续分拣</a>' . ' 或 ' . '<a href="javascript:void(0)"  name = "button_save" class="easyui-linkbutton" data-options="iconCls:\'icon-save\'" onClick="'.$type.'.jump_url(\'分拣框货品明细\',\'index.php/Purchase/SortingWall/getSortingBoxGoodsList?stockout_no='.$res_so_info['stockout_no'].'\',\''.$res_so_info['stockout_no'].'\')">进行货品移位</a>' . ' 或 ' . '<a href="javascript:void(0)"  name = "button_save" class="easyui-linkbutton" data-options="iconCls:\'icon-save\'" onClick="'.$type.'.forceRevertCheck('.$query_stockout_id.')">强制驳回审核</a>' . ' 或 ' . '<a href="javascript:void(0)"  name = "button_save" class="easyui-linkbutton" data-options="iconCls:\'icon-save\'" onClick="'.$type.'.forceRevertAllCheck('.$query_stockout_id.')">全部强制驳回审核</a>',
					);
					//$this->rollback();
					return false;
				}
			}
			if ((int)$res_so_info['is_stalls']==0&&$form['is_force']<>1&&$is_reason)
			{
				$sort_status = M('box_goods_detail')->field('rec_id')->fetchSql(false)->where(array('trade_id'=>$query_trade_id,'sort_status'=>0))->find();
				$msg = '';
				if(!empty($sort_status['rec_id'])||$sort_status['rec_id'] != null){
					$msg = '存在未分拣的货品，请继续分拣 或 进行货品移位';
				}
				if($msg != ''){
					$fail[] = array(
						'stock_id' => $query_stockout_id,
						'stock_no' => $res_so_info['stockout_no'],
						'msg' =>$msg,
						'solve_way' =>'<a href="javascript:void(0)"  name = "button_save" class="easyui-linkbutton" data-options="iconCls:\'icon-save\'" onclick="'.$type.'.continueSort()">继续分拣</a>' . ' 或 ' . '<a href="javascript:void(0)"  name = "button_save" class="easyui-linkbutton" data-options="iconCls:\'icon-save\'" onClick="'.$type.'.jump_url(\'分拣框货品明细\',\'index.php/Purchase/SortingWall/getSortingBoxGoodsList?stockout_no='.$res_so_info['stockout_no'].'\',\''.$res_so_info['stockout_no'].'\')">进行货品移位</a>' . ' 或 ' . '<a href="javascript:void(0)"  name = "button_save" class="easyui-linkbutton" data-options="iconCls:\'icon-save\'" onClick="'.$type.'.forceRevertCheck('.$query_stockout_id.')">强制驳回审核</a>' . ' 或 ' . '<a href="javascript:void(0)"  name = "button_save" class="easyui-linkbutton" data-options="iconCls:\'icon-save\'" onClick="'.$type.'.forceRevertAllCheck('.$query_stockout_id.')">全部强制驳回审核</a>',
					);
					//$this->rollback();
					return false;
				}
			}
			$tmp_stock_spec_id = time();

			if( (int)$res_so_info['consign_status'] & 4)
			{
				//更新负库存
				$debug_point_str = 'salesstockout-revertSalesStockoutNoTrans-update_neg_stockout_num';
				//查询负库存数量
				/*  2016-1-10 注释对出库单货位有关的操作
                 $neg_stockout_cond = array(
                    'sod.stockout_id' => array('eq',$query_stockout_id),
                    'sodp.stock_spec_detail_id' => array('eq',0),

                );
                $neg_stockout_fields = array("{$res_so_info['warehouse_id']}"=>"warehouse_id","spec_id"=>"spec_id","-sodp.num"=>"neg_stockout_num");
                $neg_stockout_info  = D('Stock/StockoutOrderDetail')->getStockoutOrderDetailNumLeftToPosition($neg_stockout_fields,$neg_stockout_cond);
                //更新负库存数量
                $stock_spec_update = array(
                    'neg_stockout_num'=>array('exp'=>'neg_stockout_num+VALUES(neg_stockout_num)'),
                );
                $res_update_neg  = D('Stock/StockSpec')->insertStockSpecForUpdate($neg_stockout_info,$stock_spec_update);
     */
				//库存恢复
				$debug_point_str = 'salesstockout-revertSalesStockoutNoTrans-reback_stock_num';
				//查询出库数量详情
				$stockout_num_fields = array("{$query_warehouse_id}"=>'warehouse_id','spec_id','num');
				$stockout_num_cond = array(
					'stockout_id' =>$query_stockout_id,
				);
				$stockout_num_info = D('Stock/StockoutOrderDetailModel')->getStockoutOrderDetails($stockout_num_fields,$stockout_num_cond);
				//更新库存相关数量
				$stock_spec_num_update = array(
					'today_num'=>array('exp','IF(DATE(last_sales_time)=CURRENT_DATE(),GREATEST(today_num-VALUES(stock_num),0),today_num)'),//今日销量
					'stock_num'=>array('exp','stock_num+VALUES(stock_num)'),       //库存数量

				);
				$res_update_stock_num = D('Stock/StockSpec')->insertStockSpecForUpdate($stockout_num_info,$stock_spec_num_update);

				//已出库驳回审核不需要恢复货位占用  ???
				//$debug_point_str = 'salesstockout-revertSalesStockoutNoTrans-reback_stock_positions';

				//查询非负库存的货品占有量
				/* 2016-1-10 有关货位相关操作
                 $normal_stockout_cond = array(
                     'sod.stockout_id' => array('eq',$query_stockout_id),
                     'sodp.stock_spec_detail_id' => array('GT',0),

                 );
                 $normal_stockout_fields = array("sodp.stock_spec_detail_id"=>"rec_id","sodp.num"=>"stock_num","{$tmp_stock_spec_id}"=>"stockin_detail_id","{$tmp_stock_spec_id}"=>"stock_spec_id","{$tmp_stock_spec_id}"=>"position_id","NOW()"=>"created");
                 $normal_stockout_info  = D('Stock/StockoutOrderDetail')->getStockoutOrderDetailNumLeftToPosition($normal_stockout_fields,$normal_stockout_cond);

                 //更新出库单详情里面的数量
                 $update_stockout_detail = array(
                    'stock_num' => array('exp','stock_spec_detail.stock_num+VALUES(stock_num)') ,
                     'is_used_up' => array('exp','IF(reserve_num>=stock_num,1,0)')
                 );
                 $res_update_detail = D('Stock/StockSpecDetail')->insertStockSpecDetailForUpdate($normal_stockout_info,$update_stockout_detail);
                      */
				// 货位库存  更新相应货位的库存
				/* $sql_update_position ="INSERT INTO stock_spec_position(warehouse_id,spec_id,position_id,zone_id,stock_num)"
                 ." (SELECT {$query_warehouse_id},sod.spec_id,sodp.position_id,IFNULL(cwp.zone_id,0),sodp.num"
                 ." FROM stockout_order_detail_position sodp"
                 ." LEFT JOIN stockout_order_detail sod ON sod.rec_id=sodp.stockout_order_detail_id"
                 ." LEFT JOIN cfg_warehouse_position cwp ON cwp.rec_id=sodp.position_id"
                 ." WHERE sod.stockout_id={$query_stockout_id})"
                 ."	ON DUPLICATE KEY UPDATE stock_spec_position.stock_num=stock_spec_position.stock_num+VALUES(stock_spec_position.stock_num)";
                $res_update_position = $this->execute($sql_update_position); */
				//-- 增加待审核量
				$debug_point_str = 'salesstockout-revertSalesStockoutNoTrans-add_checking_num';
				$res_call_reserve = $this->execute("CALL I_RESERVE_STOCK({$res_so_info['src_order_id']},3,{$query_stockout_id},0)");
				//删除货位分配
				/* 2016-1-10 注释对库存历史操作记录
                 $debug_point_str = 'salesstockout-revertSalesStockoutNoTrans-allocation_stock_position';
                 $sql_delete_sodp = "DELETE sodp"
                 ." FROM stockout_order_detail_position sodp,stockout_order_detail sod"
                 ." WHERE sod.stockout_id={$query_stockout_id} AND sodp.stockout_order_detail_id=sod.rec_id";
                $res_delete_sodp = $this->execute($sql_delete_sodp);
                 */
				//获取订单类型掩码
				/*
                 $debug_point_str = 'salesstockout-revertSalesStockoutNoTrans-sales_trade_mask';
                 $sales_trade_fields = "IF(trade_type=2,1,0)|IF(trade_type=3,2,0)|IF(trade_from=4,4,0)|IF(delivery_term=2,8,0)  order_mask";
                 $sales_trade_cond = array('trade_id',array('eq',$query_trade_id));
                 $res_trade_mask = D('Trade/Trade')->getSalesTradeList($sales_trade_fields,$sales_trade_cond);
                 $order_mask = $res_trade_mask[0]['order_mask'];*/
				//更新出入库记录
				$debug_point_str = 'salesstockout-revertSalesStockoutNoTrans-update_stock_change_history';

				//获取出库单详情
				$stockout_detail_fields = array(
					"{$res_so_info['src_order_type']} as src_order_type",
					"{$query_trade_id} as src_order_id",
					"'{$res_so_info['src_order_no']}' as src_order_no",
					"'{$res_so_info['stockout_id']}' as stockio_id",
					"sod.rec_id"                               =>"stockio_detail_id",
					"'{$res_so_info['stockout_no']}' as stockio_no",
					"ss.spec_id"                               =>"spec_id",
					"'{$res_so_info['warehouse_id']}' as warehouse_id",
					"2 as type",
					"ss.cost_price"                            =>"cost_price_old",
					"ss.stock_num-SUM(sod.num)"                =>"stock_num_old",
					"ss.cost_price"                            =>"price",
					"-SUM(sod.num) as num",
					"-ss.cost_price*SUM(sod.num) as amount",
					"ss.cost_price"                            =>"cost_price_new",
					"ss.stock_num"                             =>"stock_num_new",
					"{$form['operator_id']} as operator_id",
					"'撤销出库'"                                   =>"remark"
				);
				$stockout_detail_cond = array(
					"ss.warehouse_id" => "{$query_warehouse_id}",
					"sod.stockout_id" => "{$query_stockout_id}"
				);
				$stockout_detail_info = D('Stock/StockoutOrderDetail')->getStockoutOrderDetailLeftStockSpec($stockout_detail_fields,$stockout_detail_cond);

				//插入更新记录
				$res_stockio_log = D('Stock/StockChangeHistory')->insertStockChangeHistory($stockout_detail_info);

			}/* elseif ((int)$res_so_info['is_allocated']){
	        // 还原货位保留库存
	            $debug_point_str = 'salesstockout-revertSalesStockoutNoTrans-reback_stock_hold';
	           //查询出库正常站位库存量
	            $normal_stockout_fields = array(
	                "sodp.stock_spec_detail_id" =>"rec_id",
	                "-sodp.num as reserve_num",
	                "0" =>"is_used_up",
	                "{$tmp_stock_spec_id} as stockin_detail_id",
	                "{$tmp_stock_spec_id} as stock_spec_id",
	                "{$tmp_stock_spec_id} as position_id",
	                "NOW()" =>"created",
	            ) ;
	            $normal_stockout_cond = array(
	                'sod.stockout_id' => array('eq',$query_stockout_id),
	                'sodp.stock_spec_detail_id' => array('GT',0),

	            );
	            $normal_stockout_info  = D('Stock/StockoutOrderDetail')->getStockoutOrderDetailNumLeftToPosition($normal_stockout_fields,$normal_stockout_cond);
	            //更新库存详情里的出入库信息
	            $update_stock_spec_detail = array(
	                'reserve_num' => array('exp','stock_spec_detail.reserve_num+VALUES(reserve_num)'),
	                'is_used_up'  => array('exp','IF(stock_spec_detail.reserve_num>=stock_spec_detail.stock_num,1,0)'),
	            );
	            $res_revert_num = D('Stock/StockSpecDetail')->insertForUpdateStockoutOrderDetail($normal_stockout_info,$update_stock_spec_detail);

	            // 删除货位分配
	            $debug_point_str = 'salesstockout-revertSalesStockoutNoTrans-delete_position';
	            $sql_delete_sodp = " DELETE sodp FROM stockout_order_detail_position sodp,stockout_order_detail sod"
	                ." WHERE sod.stockout_id={$query_stockout_id} AND sodp.stockout_order_detail_id=sod.rec_id";

	            $res_delete_sodp = $this->execute($sql_delete_sodp);
	            //增加待审核量
	            $debug_point_str = 'salesstockout-revertSalesStockoutNoTrans-call I_RESERVE_STOCK';
	            $res_call_reserve = $this->execute("CALL I_RESERVE_STOCK({$query_trade_id},3,{$query_warehouse_id},{$query_warehouse_id})");
	        } */
			else {
				$debug_point_str = 'salesstockout-revertSalesStockoutNoTrans-call I_RESERVE_STOCK is not allocate';
				$res_call_reserve = $this->execute("CALL I_RESERVE_STOCK({$query_trade_id},3,{$query_warehouse_id},{$query_warehouse_id})");
			}
			/* //-- 删除stockout_order_detail 的包装
             $sql_delete_poistion_package = " DELETE sodp FROM stockout_order_detail_position sodp,stockout_order_detail sod"
             ." WHERE sod.stockout_id={$query_stockout_id} AND sodp.stockout_order_detail_id = sod.rec_id AND sod.is_package=1";
             $res_delete_poistion_package = $this->execute($sql_delete_poistion_package);
             $sql_delete_detail_package = "DELETE FROM stockout_order_detail WHERE stockout_id={$query_stockout_id} AND is_package=1";
             $res_delete_detail_package = $this->execute($sql_delete_poistion_package);

             if(($is_hold_logistics_status == 0 || $is_hold_sendbill_status = 0) && !empty($res_so_info['batch_no']))
             {
             //-- 清除打印批次
             $sql_delete_detail_batch = "DELETE FROM stockout_print_batch_detail WHERE stockout_order_id=V_StockoutId";
             $res_delete_detail_batch = $this->execute($sql_delete_detail_batch);
             $sql_update_batch = "UPDATE stockout_print_batch spb"
             ." SET spb.order_num=(SELECT COUNT(1) FROM stockout_print_batch_detail spbd WHERE spbd.batch_id=spb.rec_id)"
             ." WHERE spb.batch_no=V_BatchNO";
             $res_update_batch = $this->execute($sql_update_batch);
             $sql_delete_batch = "DELETE FROM stockout_print_batch WHERE batch_no=V_BatchNO AND order_num=0";
             $res_delete_batch = $this->execute($sql_delete_batch);
             } */
			/*回收热敏单号*/
			//CALL I_STOCK_LOGISTICS_NO_RECYCLE(V_StockoutId);
			$debug_point_str = 'salesstockout-revertSalesStockoutNoTrans-call I_STOCK_LOGISTICS_NO_RECYCLE';
			$logistics_no_db = D('Stock/StockLogisticsNo');
			$logistics_no_db->recoverLogisticsNO($query_stockout_id);

			$this->removeMultiLogistics($query_stockout_id);

			//更新出库单状态  取消出库
			$debug_point_str = 'salesstockout-revertSalesStockoutNoTrans-call update_stockout_order_status';
			//2016-1-10 是否需要添加可配的物流单打印状态的清除情况
			$consign_status = (int)$res_so_info['consign_status'];
			if($consign_status & 1){
				$consign_status = $consign_status-1;
			}
			if($consign_status & 2){
				$consign_status = $consign_status-2;
			}
			if($consign_status & 4){
				$consign_status = $consign_status-4;
			}
			$update_stockout_order_data = array(
				'status'            =>5,
				'block_reason'     =>0,
				'consign_status'   =>$consign_status,
				'is_allocated'     =>0,
				'post_cost'        =>0,
				'weight'           =>0,
				'package_id'       =>0,
				'package_cost'     =>0,
				'pick_error_count' =>0,
				'picker_id'        =>0,
				'examiner_id'      =>0,
				'consigner_id'     =>0,
				'packager_id'      =>0,
				'checkouter_id'    =>0,
				'calc_weight'      =>0,
				'is_stalls'        =>0,
				'consign_time'     =>'1000-01-01 00:00:00',
				'watcher_id'       =>0,
				'logistics_print_status'=>array('exp','IF('.$form['hold_logisticsno_status'].',logistics_print_status,0)'),
				'sendbill_print_status'=>array('exp','IF('.$form['hold_sendbill_status'].',sendbill_print_status,0)'),
				'logistics_no'=>array('exp','IF("'.$form['hold_logisticsno_status'].'",logistics_no,"")')

			) ;
			$update_stockout_order_cond = array(
				'stockout_id'  => "{$res_so_info['stockout_id']}",
			);
			D('Stock/StockOutOrder')->updateStockoutOrder($update_stockout_order_data,$update_stockout_order_cond);

			$sql_so_final_status = $this->getStockoutOrders($stockout_fields,$stockout_cond);

// 	        \Think\Log::write("salesstockout-revertStockoutOrder-stockout_final_status:".print_r($sql_so_final_status,true),\Think\Log::INFO);
			//更新订单状态
			$debug_point_str = 'salesstockout-revertSalesStockoutNoTrans-call update_salse_trade_status';
			// 判断驳回前订单状态
			$flag_id = D('Setting/Flag')->getFlagId('驳回订单',1);
			$update_sales_trade_data = array(
				"trade_status"=>"30",
				"fchecker_id"=>"0",
				"check_step"=>"0",
				"consign_status"=>"0",
				"is_stalls"=>"0",
				"revert_reason"=>"{$form['reason_id']}",
				"flag_id"=>array("exp","IF(flag_id>1000,flag_id,{$flag_id})"),
				"logistics_no"=>array("exp","IF('".$form['hold_logisticsno_status']."',logistics_no,'')")

			);
			if(!$is_reason){
				unset($update_sales_trade_data['revert_reason']);
			}
			$update_sales_trade_cond = array(
				'trade_id' => $query_trade_id,
				'trade_status' => array('neq',5),
			);
			D('Trade/Trade')->updateSalesTrade($update_sales_trade_data,$update_sales_trade_cond);

			$sql_st_final_status = $this->query("select trade_id,trade_no,trade_status from sales_trade WHERE trade_id={$res_so_info['src_order_id']}");
			\Think\Log::write("salesstockout-revertStockoutOrder-salestrade_final_status:".print_r($sql_st_final_status,true),\Think\Log::INFO);
			//--  stockout_order_detail  清除扫描标记
			$clean_scan_data = array('scan_type'=>0);
			$clean_scan_where = array('stockout_id'=>$query_stockout_id);
			$res_update_scan_type = D('Stock/stockoutOrderDetail')->updateStockoutOrderDetail($clean_scan_data,$clean_scan_where);
			//删除物流追踪记录
			$this->deleteLogisticsTrace($res_so_info['src_order_id']);
			//-- salse_trade_log,修改驳回标记,方便统计
			$debug_point_str = 'salesstockout-revertSalesStockoutNoTrans-call update_log';
			$sales_trade_log_data = array(
				'data'=>'99',
			);
			$sales_trade_log_cond = array(
				'trade_id'  => "{$query_trade_id}",
				'_complex'  => array(
					'type'=>array('in','100,103,9,12,10'),
					'_logic' =>'or',
					'_complex'=>array(
						'type'=>105,
						'data'=>0
					)
				)
			);
			D('Trade/SalesTradeLog')->updateTradeLog($sales_trade_log_data,$sales_trade_log_cond);
			//删除待推送的推送任务
			M('sys_asyn_task')->where(array('target_id'=>$query_trade_id))->delete();
			//2016-1-10 因为不知是否添加清除打印标记，所以日志记录需要重新记录
			if(!$is_reason){
				$res_query_reason['title'] = '一键合并且审核，自动驳回';
			}
			//插入订单日志
			$stl_insert_data = array(
				"trade_id"=>"{$res_so_info['src_order_id']}",
				"operator_id"=>$form['operator_id'],
				"type"=>30,
				"message"=>array("exp","CONCAT(IF({$form['is_force']},'强制驳回已发货出库单到客审,驳回原因:','驳回到客审,驳回原因:'),'{$res_query_reason['title']}',IF({$form['hold_logisticsno_status']},',保留物流单打印状态','清除物流单打印状态'),'-',IF({$form['hold_sendbill_status']},',保留发货单打印状态','清除打印发货单状态'))")
			);
			D('Trade/SalesTradeLog')->addTradeLog($stl_insert_data);
			D('Purchase/StallsOrderDetail')->where(array('trade_id'=>$res_so_info['src_order_id']))->save(array('trade_status'=>1,'stalls_id'=>0));
			if($is_reason){
				$box_goods_detail_model = M('box_goods_detail');
				$sorting_wall_detail_model = M('sorting_wall_detail');
				$box_goods_info = $box_goods_detail_model->field('rec_id,box_no')->fetchSql(false)->where(array('trade_id'=>$query_trade_id,'sort_status'=>0))->find();
				if(empty($box_goods_info)||$box_goods_info==null){
					$box_goods_info = $sorting_wall_detail_model->fetchSql(false)->field('box_no')->where(array('stockout_id'=>$query_stockout_id))->find();
				}
				if(!empty($box_goods_info)||$box_goods_info!=null){
					//$box_goods_detail_model->where(array('trade_id'=>$query_trade_id,'sort_status'=>0))->save(array('sort_status'=>1));
					$box_goods_detail_model->where(array('trade_id'=>$query_trade_id))->delete();
					$sorting_wall_detail_model->where(array('box_no'=>$box_goods_info['box_no']))->save(array('is_use'=>0,'stockout_id'=>0));
					$dynamic_allocation_box =  get_config_value('dynamic_allocation_box',0);
					if($dynamic_allocation_box==1){
						$big_box_goods_map_model = M('big_box_goods_map');
						$big_box_goods_map_model->where(array("trade_id"=>$query_trade_id))->delete();
					}
				}
			}
			//扣减平台对账发货金额
			$account_sync = get_config_value('account_sync',0);
			if($account_sync)
			{
				$this->updateAccountCheckAmount($query_trade_id);
			}
			$success = array(
				'id' => $query_stockout_id,
				'stock_no' => $res_so_info['stockout_no'],
			);
			return true;
		}catch(BusinessLogicException $e){
			$msg = $e->getMessage();
			$fail[] = array(
				'stock_id' => empty($query_stockout_id)?$stockout_id:$query_stockout_id,
				'stock_no' => $res_so_info['stockout_no'],
				'msg'      => $msg,
			);
			//$this->rollback();
			return false;
		}catch(\PDOException $e){
			$msg = $e->getMessage();
			$fail[] = array(
				'stock_id' => empty($query_stockout_id)?$stockout_id:$query_stockout_id,
				'stock_no' => $res_so_info['stockout_no'],
				'msg'      => self::PDO_ERROR,
			);
			//$this->rollback();
			\Think\Log::write($this->name.'-revertSalesStockoutNoTrans-'.$debug_point_str.$msg."-".$res_so_info['stockout_no']);
			return false;
		}catch(\Exception $e){
			$msg = $e->getMessage();
			$fail[] = array(
				'stock_id' => $query_stockout_id,
				'stock_no' => $res_so_info['stockout_no'],
				'msg' =>self::PDO_ERROR,
			);
			//$this->rollback();
			\Think\Log::write($this->name.'-revertSalesStockoutNoTrans-'.$debug_point_str.$msg."-".$res_so_info['stockout_no']);
			return false;
		}
	}
	public function consignStockoutOrder($stockout_id,&$fail=array(),&$success=array(),$is_force = 0)   //$is_force是否强制出库
	{
	    $operator_id = get_operator_id();
	            
	    $consign_now = 1;      //是否现在发货，0是默认的为现在出库，1是默认的为现在发货
	
	    $this->startTrans();
	    $debug_flag_str = __CONTROLLER__."-consignStockoutOrder-";
	    try{
			//获取基本设置  验货，
	        $query_config_values = get_config_value(array('stockout_examine_goods','order_deliver_block_consign','prevent_online_block_consign_stockout','order_check_no_stock_stockout','stockout_weight_goods'),array(0,0,0,0,0));
	        $config_values = array(
	            'stockout_examine_goods' => $query_config_values['stockout_examine_goods'],   //验货
	            'stockout_weight_goods' => $query_config_values['stockout_weight_goods'],     //称重
	            'stockout_must_checkout' =>0,     //签出
// 	            'sales_trade_trace_enable' => 1
	        );
	        $debug_log_str =$debug_flag_str."get_stockout_info";
	        $stockout_order_info_fields = array("so.stockout_id","st.platform_id","st.shop_id","st.trade_type","st.src_tids","so.stockout_no","so.warehouse_id","so.warehouse_type","so.src_order_type","so.src_order_id","so.status","so.consign_status","so.logistics_id","so.logistics_no","so.customer_id","cl.logistics_type","cl.bill_type","so.post_cost","so.weight","so.receiver_area","so.checkouter_id","so.freeze_reason","so.block_reason","so.src_order_no","so.is_allocated","so.pos_allocate_mode","so.is_stalls");
	        $stockout_order_info_cond = array(
	            'stockout_id' =>$stockout_id,
	        );
			//根据出库单id获取出库单信息
	        $res_so_info = D('Stock/StockOutOrder')->getSalesStockoutOrder($stockout_order_info_fields,$stockout_order_info_cond);
			$sales_trade_order_info = M('sales_trade_order')->field('sum(is_consigned) as is_consigned')->where(array('trade_id'=>$res_so_info['src_order_id']))->find();
			$debug_log_str =$debug_flag_str."serializer_stockout_info";
	        $query_stockout_id         = $res_so_info['stockout_id'];
	        $query_trade_id            = $res_so_info['src_order_id'];
	        $query_status              = $res_so_info['status'];
	        $query_consign_status      = $res_so_info['consign_status'];
	        $query_warehouse_id        = $res_so_info['warehouse_id'];
	        $query_src_tids            = $res_so_info['src_tids'];
	        $query_shop_id             = $res_so_info['shop_id'];
			$trade_type				   = $res_so_info['trade_type'];
	        //判断是否查询成功
			if (empty($res_so_info))
	        {
	            SE('查询失败');
	        }
	        if ((int)$res_so_info['src_order_type']<>1)
	        {
	            SE("出库单不是销售出库单");
	        }
	        if ((int)$res_so_info['status']>= 95)
	        {
	            SE("订单已发货");
	        }
			if ((int)$res_so_info['status'] != 55)
	        {
	            SE("必须是已审核订单");
	        }
	        
	        if ((int)$res_so_info['freeze_reason']<>0)
	        {
	            SE("出库单已冻结");
	        }
			if ((int)$res_so_info['block_reason']<>0 && $is_force != 1)
	        {
				$block_reason = $this->getBlockReason($res_so_info['block_reason']);
				if((int)$res_so_info['block_reason'] & (1|2|4|32|128|256))
				{
					SE( "出库单[{$block_reason}]拦截出库,请驳回重新审核");
				}else if((int)$res_so_info['block_reason'] & 4096){
				    if($query_config_values['order_deliver_block_consign'] != 1 ||  ($query_config_values['order_deliver_block_consign'] == 1 && $query_config_values['prevent_online_block_consign_stockout'] == 0))
                    {
                        SE("出库单[{$block_reason}]拦截出库,请取消拦截再出库");
                    }
				}else{
                    SE("出库单[{$block_reason}]拦截出库,请取消拦截再出库");
                }
	        }
			/* if ((int)$sales_trade_order_info['is_consigned']>0 && $is_force != 1)
	        {
				SE('出库单对应的子订单已发货');
	        } */
	        //是否现在出库
	        if((int)$res_so_info['consign_status']&4)
	        {
	            SE("订单已出库");
	        }
	        if((int)$res_so_info['warehouse_type']<>1 && (int)$res_so_info['warehouse_type'] != 127)
	        {
	            SE("委外订单不能验货出库");
	        }
	        if($config_values['stockout_must_checkout'] && $res_so_info['checkouter_id']==0)
	        {
	            SE('出库单必须签出才可操作');
	        }elseif (!empty($res_so_info['checkouter_id'])  && (int)$res_so_info['checkouter_id'] <> (int)$operator_id)
	        {
	            SE('出库单已被其他人签出');
	        }
	        if((int)$res_so_info['platform_id'] !=0)
	        {
	            if (empty($res_so_info['logistics_id']) || (int)$res_so_info['logistics_id']==0)
	            {
	                SE('物流公司未设置');
	            }
	            $res_so_info['logistics_no'] = trim($res_so_info['logistics_no']);
	            if ( (int)$res_so_info['logistics_type'] > 1 &&  empty($res_so_info['logistics_no']))
	            {
	                SE('物流单号不能为空');
	            }
	        }
	        if($is_force<>1 && $consign_now)
	        {
	            if(!((int)$query_consign_status & 1) && $config_values['stockout_examine_goods'])
	            {
	                SE('发货前必须验货');
	            }
	            if(!((int)$query_consign_status & 2) && $config_values['stockout_weight_goods'])
	            {
	                SE('发货前必须称重');
	            }
	        }
			$stockout_result = M('api_logistics_sync')->field(array('sync_status'))->where($stockout_order_info_cond)->find();
			if(!empty($stockout_result) && (int)$stockout_result['sync_status'] <3 &&$is_force<>1){
				SE('预物流同步成功后才可确认发货');
			}
			if ((int)$res_so_info['is_stalls']==1&&$is_force == 0)
			{
				$stalls_sort_status = M('stalls_less_goods_detail')->fetchSql(false)->field('sort_status')->where(array('trade_id'=>$query_trade_id))->select();
				$trade_info = D('Trade/SalesTrade')->field('stalls_id')->where(array('trade_id'=>$query_trade_id))->select();
				$stalls_id = $trade_info[0]['stalls_id'];
                $msg = '';
				$is_break= false;
				foreach($stalls_sort_status as $v){
					switch($v['sort_status']){
						case '0' ://档口货品未分拣
							$msg = $stalls_id == 0?'存在未分拣的货品，请继续分拣 或 强制发货':'存在未分拣的爆款货品，请继续分拣 或 强制发货';
							$is_break= true;
							break;
						case '2' ://档口货品分拣完成
							$msg = '存在未分拣的货品，请继续分拣 或 强制发货';
							$is_break= true;
							break;
					}
					if($is_break) break;
				}
				if($msg != ''){
					$fail[] = array(
						'stock_id' => $query_stockout_id,
						'stock_no' => $res_so_info['stockout_no'],
						'msg' =>$msg,
					);
					$this->rollback();
					return false;
				}
			}
			if ((int)$res_so_info['is_stalls']==0&&$is_force == 0)
			{
				$sort_status = M('box_goods_detail')->field('rec_id')->fetchSql(false)->where(array('trade_id'=>$query_trade_id,'sort_status'=>0))->find();
				$msg = '';
				if(!empty($sort_status['rec_id'])||$sort_status['rec_id'] != null){
					$msg = '存在未分拣的货品，请继续分拣 或 强制发货';
				}
				if($msg != ''){
					$fail[] = array(
						'stock_id' => $query_stockout_id,
						'stock_no' => $res_so_info['stockout_no'],
						'msg' =>$msg,
					);
					$this->rollback();
					return false;
				}
			}
			//获取订单信息，再次审核订单的状态
	        $debug_log_str =$debug_flag_str. "get_salse_trade_info";
	        $sales_trade_info_fields = array("cs_remark,split_from_trade_id,platform_id","receivable","shop_id","trade_status","trade_mask","delivery_term","receiver_mobile","commission","other_cost","trade_from");
	        $sales_trade_info_cond = array(
	           'trade_id' =>  $query_trade_id
	        );
			$res_st_info = D('Trade/Trade')->getSalesTradeOnLock($sales_trade_info_fields,$sales_trade_info_cond);
			if($res_st_info['trade_from']==4){
				$res_so_info['logistics_type']=1;
			}
	        if ((int)$res_st_info['trade_status']<>55 && (int)$res_st_info['trade_status']<> 53)
	        {
	            SE('订单状态不正确');
	        }
	       
	        if($consign_now)
	        {
	            $query_status = $res_st_info['platform_id']==0? 110 : 95;
	        }
			if($res_so_info['logistics_type']==1){
				$query_status = 110;
			}
			if(!((int)$query_consign_status & 4))
	        {
	            if((int)$res_so_info['warehouse_id']<=0)
	            {
	                SE('未指定出库仓库');
	            }
	            //分配货位  未做
	            $debug_log_str = $debug_flag_str."consign_warehouse_out";
	            //确定出库成本价和估算重量
	            $debug_log_str = $debug_flag_str."consign_cost_price";
	             
	          /*  $sql_consign_cost_price = "UPDATE stockout_order_detail sod,stock_spec ss,goods_spec gs"
	                                       ." SET sod.cost_price=ss.cost_price,sod.weight=gs.weight*sod.num"
	                                       ." WHERE sod.stockout_id={$query_stockout_id} AND ss.spec_id=sod.spec_id AND ss.warehouse_id={$res_so_info['warehouse_id']} AND sod.spec_id = gs.spec_id";
				 */	
				$sql_cost_price = $this->query("select ss.spec_id,ss.cost_price,gs.weight from stockout_order_detail 
												sod left join stock_spec ss on ss.spec_id = sod.spec_id inner join goods_spec gs on gs.spec_id = ss.spec_id where 
												 sod.stockout_id={$query_stockout_id} and ss.warehouse_id={$res_so_info['warehouse_id']} order by sod.rec_id");
				
				foreach($sql_cost_price as $p_v){
					$this->execute("update stockout_order_detail set cost_price={$p_v['cost_price']},
					weight = num*{$p_v['weight']} where stockout_id  = {$query_stockout_id} and spec_id = {$p_v['spec_id']}");
				} 
				
				
	            //-- 估算重量
	          /*   $sql_calc_weight = "UPDATE stockout_order_detail sod,goods_spec gs"
	                               ." SET sod.weight=gs.weight*sod.num"
	                               ." WHERE sod.stockout_id={$query_stockout_id} AND gs.spec_id=sod.spec_id";
	            $res_calc_weight = $this->execute($sql_calc_weight);
	 */
                //2016-1-6  测试  计算负库存：因为涉及到从分配的货位中扣减府库存的量所以未做处理	            
	            //扣减库存
	            $debug_log_str = $debug_flag_str."dec_stock_num";
	            $stockout_order_detail_fields = array(
	                   "{$res_so_info['warehouse_id']} as warehouse_id",
	                   "sod.spec_id"=>"spec_id",
	                   "-sod.num as stock_num",
	                   "IF(1 AND sod.is_package=0,-sod.num,0)"=>"sending_num",
	                   "1 as status",
	                   "NOW()"  =>"created",
					   "IF(ssp.position_id,ssp.position_id,-{$res_so_info['warehouse_id']}) as last_position_id"
	            );
	            $stockout_order_detail_cond = array(
	                   "stockout_id" =>"{$query_stockout_id}"
	            );
	            //2016-1-6 测试出的bug   ：查询的详情中添加了group by spec_id  但是实际存储过程中并没有添加这个分组
	           // $res_stockout_num_info = D('Stock/StockoutOrderDetail')->getStockoutOrderDetails($stockout_order_detail_fields,$stockout_order_detail_cond);
                $res_stockout_num_info = D('Stock/StockoutOrderDetail')->alias('sod')->field($stockout_order_detail_fields)->join('left join stock_spec_position ssp on ssp.spec_id= sod.spec_id and ssp.warehouse_id='.$res_so_info['warehouse_id'])->where(array("sod.stockout_id"=>$query_stockout_id))->order('sod.rec_id')->select();
				//$is_allow_neg_stock = D('Stock/StockoutOrderDetail')->fetchSql(false)->alias('sod')->field('sum(if(gs.is_allow_neg_stock>0,0,if(ss.stock_num<sod.num,1,0))) is_allow_neg_stock')->join('left join stock_spec ss on ss.spec_id = sod.spec_id and ss.warehouse_id='.$res_so_info['warehouse_id'])->join('left join goods_spec gs on gs.spec_id = ss.spec_id')->where(array('sod.stockout_id'=>$query_stockout_id))->select();
				$is_allow_neg_stock = D('Stock/StockoutOrderDetail')->fetchSql(false)->alias('sod')->field('if(gs.is_allow_neg_stock>0,0,if(ss.stock_num<sod.num,1,0)) is_allow_neg_stock,sod.spec_no,cw.name warehouse_name')->join('left join stock_spec ss on ss.spec_id = sod.spec_id and ss.warehouse_id='.$res_so_info['warehouse_id'])->join('left join cfg_warehouse cw on cw.warehouse_id=ss.warehouse_id')->join('left join goods_spec gs on gs.spec_id = ss.spec_id')->where(array('sod.stockout_id'=>$query_stockout_id))->select();
				if($query_config_values['order_check_no_stock_stockout']!=0&&$is_force<>1){
					$is_finish = false;
					foreach($is_allow_neg_stock as $v){
						if((int)$v['is_allow_neg_stock'] > 0){
							$fail[] = array(
								'stock_id' => $query_stockout_id,
								'stock_no' => $res_so_info['stockout_no'],
								'msg' =>'不允许负库存出库！ 商家编码：'.$v['spec_no'].'   仓库：'.$v['warehouse_name'],
							);
							$is_finish = true;
						}
					}
					if($is_finish){return;}
				}
				$stock_log_bef  = D('Stock/StockoutOrderDetail')->alias('sod')->field(array("IFNULL(ss.rec_id,0) stock_spec_id,sod.spec_id,so.warehouse_id,'{$operator_id}' as operator_id,CONCAT('销售出库-',so.stockout_no) message,sod.num, IFNULL(ss.stock_num,0) stock_num,3 operator_type"))->join('left join stockout_order so on so.stockout_id = sod.stockout_id')->join('stock_spec ss on ss.spec_id = sod.spec_id and so.warehouse_id = ss.warehouse_id')->where(array("sod.stockout_id"=>$query_stockout_id))->select();

                $stock_spec_update = array(
	                   "today_num"         => array('exp',"IF(DATE(last_sales_time)=CURRENT_DATE(),today_num-VALUES(stock_num),-VALUES(stock_num))") ,
	                   "last_sales_time"   => array('exp',"CURRENT_DATE()"),
	                   "stock_num"         => array("exp","stock_num+VALUES(stock_num)"),
	                   "sending_num"       => array("exp","sending_num+VALUES(sending_num)"),
	                   "status"            => array("exp","1"),
					   'last_position_id'=>array('exp','VALUES(last_position_id)')
	            );
	            $res_deduc_ss = D('Stock/StockSpec')->insertStockSpecForUpdate($res_stockout_num_info,$stock_spec_update);
              //  $res_ss_u = D('Stock/StockSpec')->addAll($res_stockout_num_info,'',$stock_spec_update);
				foreach($stock_log_bef as $key=> $value)
                {
                    if($value['stock_spec_id'] == 0)
                    {
                        $stock_spec_id = D('Stock/StockSpec')->where(array('spec_id'=>$value['spec_id'],'warehouse_id'=>$value['warehouse_id']))->getField('rec_id');//感觉这个是多余的
                        $stock_log_bef[$key]['stock_spec_id'] = $stock_spec_id;
                    }
                }
                D('Stock/StockSpecLog')->addAll($stock_log_bef);
                $debug_log_str = $debug_flag_str."dec_stock_position_num";
				$ss_position_info = D('Stock/StockoutOrderDetail')->alias('sod')->field(array("ss.warehouse_id","sod.spec_id","IF(ssp.position_id IS NULL,ss.stock_num,-sod.num) as stock_num","NOW() as last_inout_time",'cwz.zone_id','IF(ssp.position_id,ssp.position_id,-ss.warehouse_id) as position_id','IF(ssp.position_id,ssp.position_id,-ss.warehouse_id) as last_position_id'))->join('left join stock_spec ss on ss.spec_id = sod.spec_id and ss.warehouse_id='.$res_so_info['warehouse_id'])->join('left join stock_spec_position ssp on ssp.spec_id= sod.spec_id and ssp.warehouse_id='.$res_so_info['warehouse_id'])->join('left join cfg_warehouse_position cwp on cwp.rec_id = ssp.position_id')->join('left join cfg_warehouse_zone cwz on cwz.warehouse_id = ss.warehouse_id')->where(array("sod.stockout_id"=>$query_stockout_id))->select();

				$stock_spec_position_up = array(
					'stock_num'=>array('exp',"stock_num+VALUES(stock_num)"),
					"last_inout_time"=>array('exp','NOW()'),
				);
				
				$res_ss_p = D('Stock/StockSpecPosition')->addAll($ss_position_info,array(),$stock_spec_position_up);
				
				\Think\Log::write(print_r($res_ss_p,true),\Think\Log::INFO);
//				E('未知错误请联系管理员,调试中');
				//扣减货位  未做
	            //trade_from- 1API抓单，2手工建单 3excel导入 4现款销售',
	            //trade_type-1网店销售2线下零售3售后换货4批发业务'
	            //delivery_term  发货条件 1款到发货 2货到付款(包含部分货到付款) 3分期付款--(冗余字段)',
	            //这里要对现款销售进行单独的判断  因为之前现款销售的src_order_type是6 后来改动后  现款销售的类型和线上销售的类型都是1了 但是库存台账需要看到现款销售- 加order_mask掩码字段来区分
                //二进制掩码1线下零售2售后换货4现款销售8COD订单',
	            $debug_log_str = $debug_flag_str."get_sales_mask";
	            $now_sales_mask_fields = array(
	                   "IF(trade_type=2,1,0)|IF(trade_type=3,2,0)|IF(trade_from=4,4,0)|IF(delivery_term=2,8,0)"=>"order_mask",  
	            );
	            $now_sales_mask_cond = array(
	                   "trade_id" => "{$query_trade_id}",
	            );
	            $res_now_sales = D('Trade/Trade')->getSalesTradeList($now_sales_mask_fields,$now_sales_mask_cond);
	            //debug 2016-1-9 因为不确保其他地方比如说入库库等操作对这个表经行操作了stock_change_history，所以当前先注释掉
	            //记录库存变化
	         // 2016-1-10  因为库存变化记录信息不完整，所以现阶段不考虑更新他的相关操作  
	           $debug_log_str = $debug_flag_str."log_stock_spec_change";
	            $stockout_stock_spec_fields = array(
                        "{$res_so_info['src_order_type']} as src_order_type",
                        "'{$res_so_info['src_order_id']}' as src_order_id",
                        "'{$res_so_info['src_order_no']}' as src_order_no",
                        "'{$res_so_info['stockout_id']}' as stockio_id",
                        "sod.rec_id"=>"stockio_detail_id",
                        "'{$res_so_info['stockout_no']}' as stockio_no",
                        "ss.spec_id"=>"spec_id",
                        "'{$res_so_info['warehouse_id']}' as warehouse_id",
                        "2 as type",
                        "ss.cost_price"=>"cost_price_old",
                        "ss.stock_num+SUM(sod.num)"=>"cost_price_old",
                        "ss.cost_price"=>"price",
                        "SUM(sod.num)"=>"num",
                        "ss.cost_price*SUM(sod.num)"=>"amount",
                        "ss.cost_price"=>"cost_price_new",
                        "ss.stock_num"=>"stock_num_new",
                        "{$operator_id} as operator_id",
                        "'{$res_now_sales[0]['order_mask']}' as order_mask",
                        "''"=>"remark"	            
	            );
	            $stockout_stock_spec_cond = array(
	                   "ss.warehouse_id" =>"{$res_so_info['warehouse_id']}",
	                   "sod.stockout_id" =>"{$query_stockout_id}",
	            );
	            $res_stockout_stock_spec = D('Stock/StockoutOrderDetail')->getStockoutOrderDetailLeftStockSpec($stockout_stock_spec_fields,$stockout_stock_spec_cond);
	            D('Stock/StockChangeHistory')->insertStockChangeHistory($res_stockout_stock_spec);
	             
	
	            /* 后期添加是否是线下销售的判断 */
	           
	            //-- 更新一下平台货品的库存标记
	            D('Common/SysProcessBackground')->stockoutChange($query_stockout_id);
	            //  修改出库单状态
	            $debug_log_str = $debug_flag_str."update_stockout_order_status";
	             //2016-1-9 修改bug  原先的为checkouter_id 现在改为consigner_id
	            $update_stockout_order_data = array(
	                   "status"            => "{$query_status}",
	                   "consign_status"    => array("exp","(consign_status | 4)"),
	                   "consigner_id"      => array("exp","{$operator_id}"),
	                   "weight"            => array("exp","IF(weight=0,calc_weight,weight)"),
	                   "post_cost"         => array("exp","IF(post_cost=0,calc_post_cost,post_cost)"),
	                   "consign_time"      => array("exp","IF({$query_status}>=95,NOW(),consign_time)")
	            );
	            $update_stockout_order_conditions = array(
	                   'stockout_id' =>$query_stockout_id  
	            );
	            $res_update_stockout_order = D('Stock/StockOutOrder')->updateStockoutOrder($update_stockout_order_data,$update_stockout_order_conditions);
	        
	            //日志
	            $debug_log_str = $debug_flag_str."update_stockout_log";
	             
	            $insert_stockout_log_data =array(
	                 array(
    	                "order_type"=>"2",
    	                "order_id"=>"{$query_stockout_id}",
    	                "operator_id"=>"{$operator_id}",
    	                "operate_type"=>array("exp","CASE {$query_status} WHEN 55 THEN 18 WHEN 95 THEN 50 ELSE 112 END"),
    	                "message"=>array("exp","CASE {$query_status} WHEN 55 THEN '出库完成' WHEN 95 THEN '出库单已发货' ELSE '出库单已完成' END"),
	               )
	            );
	            
	            D("Stock/StockInoutLog")->insertStockInoutLog($insert_stockout_log_data);
	            // 2016 - 1-6  测试bug:释放订单占用库存标记   未做
	            //UPDATE sales_trade_order SET stock_reserved=0 WHERE trade_id=V_TradeId;
	           D('Trade/SalesTradeOrder')->updateSalesTradeOrder(array('stock_reserved'=>0),array('trade_id'=>$query_trade_id));
	        }
	        
	        if((int)$query_status >= 95)
	        {
	           if(!((int)$query_consign_status & 8)&&$res_so_info['logistics_type']!=1)
	            {

	                //物流同步
	                $debug_log_str = $debug_flag_str."stock_spec_sync";
	                 
	                \Think\Log::write("consignsalesstockout-call-I_SALES_CREATE_LOGISTICS_SYNC:  trade_id：{$query_trade_id}；stockout_id:{$query_stockout_id};P_IsOnline:0",\Think\Log::INFO);
	                $sql_logistics_sync = "CALL I_SALES_CREATE_LOGISTICS_SYNC({$query_trade_id},{$query_stockout_id},0)";
	                $res_logistics_sync = $this->execute($sql_logistics_sync);
	            }
	            //将新的物流单号重新同步到平台
                $logistics_sync_info = D('Stock/ApiLogisticsSync')->field('logistics_no,sync_status')->where(array('stockout_id'=>$stockout_id))->find();
	            $oln = $logistics_sync_info['logistics_no'];
	            $nln = $res_so_info['logistics_no'];
                if($logistics_sync_info['sync_status'] == 3){
                    if($oln != $nln){
                        $sync_update = array(
                            'logistics_no'=>$nln,
                            'sync_status'=>0
                        );
                        D('Stock/ApiLogisticsSync')->where(array('stockout_id'=>$stockout_id))->save($sync_update);
                    }
                }
	            //重新读取consign_status,判断原始单是否已完成
	            $debug_log_str = $debug_flag_str."get_again_status";
	             
	            $query_consign_status_fields = array("consign_status") ;
	            $query_consign_status_cond = array("stockout_id"=>"{$stockout_id}");
	            $res_select_stockout_order = D('Stock/StockOutOrder')->getStockoutOrders($query_consign_status_fields,$query_consign_status_cond);
	            $query_consign_status = $res_select_stockout_order[0]['consign_status'];
	            //-- 如果是线下订单，发货后修改原始单信息为已发货
	            $debug_log_str = $debug_flag_str."update_salse_trade";
	             
	            $sql_update_trade = "UPDATE sales_trade_order sto LEFT JOIN api_trade `at` ON (sto.platform_id=at.platform_id AND sto.src_tid = at.tid ) SET at.process_status=60,at.trade_status=70 WHERE sto.trade_id= {$query_trade_id} AND sto.platform_id=0";
	            $res_update_trade = $this->execute($sql_update_trade);
				//如果物流类型是无物流单号,发货后直接修改原始单为已发货
				if($res_so_info['logistics_type']==1){
					$sql_update_trade = "UPDATE sales_trade_order sto LEFT JOIN api_trade `at` ON (sto.platform_id=at.platform_id AND sto.src_tid = at.tid ) SET at.process_status=60,at.trade_status=70 WHERE sto.trade_id= {$query_trade_id}";
					$res_update_trade = $this->execute($sql_update_trade);
				}
				//----统计邮资成本
                $res_so_info['weight'] = $this->where(array('stockout_id'=>$stockout_id))->getField('weight');
				$post_cost = D('Setting/LogisticsFee')->calculPostCost($query_stockout_id,$res_so_info['weight']);

				$this->where(array('stockout_id'=>$query_stockout_id))->save(array('post_cost'=>$post_cost));
				//----查询邮资成本
				$post_field = array('post_cost','weight');
				$post_where = array('stockout_id'=>$query_stockout_id);
				$post_info 	= $this->getStockoutOrders($post_field,$post_where);
				$post_info 	= $post_info[0];
				if(!empty(trim($res_so_info['logistics_no']))){
					//----插入一条待结算物流单记录
					$fa_logistics_data = array(
							'logistics_id'		=> $res_so_info['logistics_id'],
							'logistics_no'		=> $res_so_info['logistics_no'],
							'status'			=> 0,
							'type'				=> 1,
							'shop_id'			=> $res_so_info['shop_id'],
							'warehouse_id'		=> $res_so_info['warehouse_id'],
							'postage'			=> $post_info['post_cost'],
							'weight'			=> $post_info['weight'],
							'area'				=> $res_so_info['receiver_area'],
							'make_oper_id'		=> $operator_id,
							'trade_count'		=> 1,
							'created'			=> array('exp','NOW()')
					);
					$fa_logistics_duplicate = array(
							'trade_count'	=>	array('exp','trade_count+VALUES(trade_count)'),
							'weight'		=>	array('exp','weight+VALUES(weight)'),
							'postage'		=>	array('exp','postage+VALUES(postage)'),
					);
					D('Account/LogisticsFeeManagement')->add($fa_logistics_data,'',$fa_logistics_duplicate);
				}

			}
			//修改出库状态以显示物流同步情况
			if($res_so_info['logistics_type']!=1){
				$this->update_als_status('so.stockout_id = '.$query_stockout_id,1);
			}else{
				$this->execute('update stockout_order so set so.consign_status = (consign_status | 32) where so.stockout_id = '.$query_stockout_id);
			}
	        //-- 如果原始单已完成,订单直接进入完成状态
	        if($query_status>=95 && $query_status < 110 && ($query_consign_status & 1073741824))
	        {
	            $query_status = 110;
	        }
	       
	        /* 计算成本价 */
	        $stockout_detail_cost_fields = array(
	            'SUM(IFNULL(IF(is_package,0,num*cost_price),0)) as goods_cost',
	            'SUM(IFNULL(IF(is_package,num*cost_price,0),0)) as package_cost'
	        );
	        
	        $stockout_detail_cost_conditions = array(
	            'stockout_id' => array('eq',$query_stockout_id)
	        );
	        $res_stockout_detail_cost = D('Stock/StockoutOrderDetail')->getStockoutOrderDetails($stockout_detail_cost_fields,$stockout_detail_cost_conditions);
	        
	        /*-- 计算不允许0成本的  售价总额  
	SELECT SUM(IFNULL(num*price,0))
		INTO V_UnknownGoodsAmount
	FROM stockout_order_detail WHERE stockout_id=P_StockoutId AND is_package=0 AND is_zero_cost=0 AND (cost_price=0.0 OR cost_price IS NULL);   */
	        $stockout_unknown_goods_amount_fields = array(
	            'SUM(IFNULL(num*price,0)) as unknown_goods_amount',
	        );
	        $stockout_unknown_goods_amount_conditions = array(
	             'stockout_id' => array('eq',$query_stockout_id),
	             'is_package' => array('eq',0),
//	             'is_allow_zero_cost' => array('eq',0),
	             '_string' =>'(cost_price = 0.0 OR cost_price IS NULL)'
	             //'cost_price' => array('exp','=0.0 OR cost_price IS NULL')
	        );
	       $res_stockout_unknown_goods_amount = D('Stock/StockoutOrderDetail')->getStockoutOrderDetails($stockout_unknown_goods_amount_fields,$stockout_unknown_goods_amount_conditions);
	      if(empty($res_stockout_unknown_goods_amount)||empty($res_stockout_unknown_goods_amount[0]['unknown_goods_amount']))
	       {
	           $res_stockout_unknown_goods_amount[0]['unknown_goods_amount'] = 0;
	       }
	       //------物流同步只是说当前订单所关联的原始订单已经走了物流同步的相关过程
	      /* $has_no_send = $this->query("SELECT COUNT(1) as  has_no_send FROM sales_trade_order sto LEFT JOIN sales_trade st ON st.trade_id=sto.trade_id WHERE sto.shop_id={$query_shop_id} AND sto.src_tid='{$query_src_tids}' AND  sto.src_tid <> '' AND	sto.trade_id<>{$query_trade_id} AND sto.actual_num>0 AND st.trade_status<95;");
	       if (empty($has_no_send))
	       {
	           $has_no_send = 0;
	       }else{
	           $has_no_send = (int)$has_no_send[0]['has_no_send'];
	       }
			//---------判断是否经行了物流同步了
		    $is_logistics_sync = 0;
			$query_trade_order_info = D('Trade/SalesTradeOrder')->alias('sto')->field(array('sto.rec_id','sto.platform_id','sto.shop_id','sto.src_tid as tid','sto.src_tid oid'))->where(array('sto.trade_id'=>$query_trade_id))->select();
			foreach($query_trade_order_info as $key => $value){
				$logistics_sync_info = D('Stock/ApiLogisticsSync')->field('logistics_no,logistics_id')->where("tid = '{$value['tid']}' and shop_id ='{$value['shop_id']}' and FIND_IN_SET('{$value['oid']}',oids)")->select();
				if(!empty($logistics_sync_info) && trim($logistics_sync_info['logistics_no']) == trim($res_so_info['logistics_no']) && trim($res_so_info['logistics_id']) == trim($logistics_sync_info['logistics_id'])){
					$is_logistics_sync = 1;
					break;
				}
			}*/
	       $final_update_stockout_data = array(
	            'goods_total_cost'    => $res_stockout_detail_cost[0]['goods_cost'],
	            'unknown_goods_amount'=> array('exp','IFNULL('.$res_stockout_unknown_goods_amount[0]['unknown_goods_amount'].',0)'),
	            'package_cost'        => $res_stockout_detail_cost[0]['package_cost'],
	            'weight'              => array('exp','IF(weight=0,calc_weight,weight)'),
	            'post_cost'           => array('exp','IF(post_cost=0,calc_post_cost,post_cost)'),
	            "status"              => "{$query_status}",
	            "consign_status"      => array("exp","(consign_status|IF({$res_st_info['platform_id']} AND {$query_status}>=95 AND {$res_so_info['logistics_type']}<>1,12,4))"),//AND ({$has_no_send} or {$is_logistics_sync} ) = 0
	            "consign_time"        => array("exp","IF({$query_status}>=95,NOW(),consign_time)")
	        );
	        $final_update_stockout_cond = array(
	            "stockout_id"        => "{$query_stockout_id}"
	        );
	        $res_update_stockout_status = D('Stock/StockOutOrder')->updateStockoutOrder($final_update_stockout_data,$final_update_stockout_cond);
	        
	        $final_update_salestrade_data = array(
	            "trade_status"=>"{$query_status}",
	            "consign_status"=>array("exp","(consign_status|IF({$res_st_info['platform_id']} AND {$query_status}>=95,12,4))")// AND {$has_no_send} = 0
	        );
	        $final_update_salestrade_cond = array(
	            "trade_id"=>"{$res_so_info['src_order_id']}"
	        );
	        $res_update_sales_trade_status = D('Trade/Trade')->updateSalesTrade($final_update_salestrade_data,$final_update_salestrade_cond);
	        
	        $trade_log_db=D('Trade/SalesTradeLog');
	        $debug_log_str = $debug_flag_str."trade_trace";
	        $sql_error_info='';
	        //再次查询当前的子状态
	        $query_consign_status_fields = array("consign_status") ;
	        $query_consign_status_cond = array("stockout_id"=>"{$stockout_id}");
	        $res_select_stockout_order = D('Stock/StockOutOrder')->getStockoutOrders($query_consign_status_fields,$query_consign_status_cond);
	        $query_consign_status = $res_select_stockout_order[0]['consign_status'];
	        $debug_log_str = $debug_flag_str."trade_trace :consign_status-".$query_consign_status.'-status:'.$query_status;
	        // 2016-1-22  需要注意 data  更新的值不同 
	        $trace_status = '';
	        if($query_status>=95 && !($query_consign_status& 4))
	        {
	            $debug_log_str = $debug_flag_str."trade_trace_on_consign".$query_consign_status;
	             
	            $insert_salestrade_log_data = array(
	                "trade_id" =>"{$res_so_info['src_order_id']}",
	                "operator_id"=>"{$operator_id}",
	                "type"=>"105",
	                "data"=>"0",
	                "message"=>"{$res_so_info['src_order_no']}订单出库并发货",
	            );
	            $trace_status = 13;//测试记录
	            $trade_log_db->addTradeLog($insert_salestrade_log_data);
	            D('Trade/TradeCheck')->traceTrade($operator_id,$query_trade_id,13,$res_st_info['cs_remark'],$res_st_info['split_from_trade_id']);
	        }elseif((int)$query_status >= 95) {
	            $debug_log_str = $debug_flag_str."trade_trace_on_consign".$query_consign_status;
	            $insert_salestrade_log_data = array(
	                "trade_id" =>"{$res_so_info['src_order_id']}",
	                "operator_id"=>"{$operator_id}",
	                "type"=>"105",
	                "data"=>"1",
	                "message"=>"{$res_so_info['src_order_no']}订单发货",
	            );
	            
	            $trade_log_db->addTradeLog($insert_salestrade_log_data);
	            $trace_status = 14;
	            D('Trade/TradeCheck')->traceTrade($operator_id,$query_trade_id,14,$res_st_info['cs_remark'],$res_st_info['split_from_trade_id']);
	             
	        }else{
                $debug_log_str = $debug_flag_str."trade_trace_other";
                $trace_status = 13;
	            D('Trade/TradeCheck')->traceTrade($operator_id,$query_trade_id,13,$res_st_info['cs_remark'],$res_st_info['split_from_trade_id']);
	        }
	        \Think\Log::write($debug_log_str.'\n--operator_id:'.$operator_id.'-trade_id:'.$query_trade_id.'-全链路状态:'.$trace_status.'-remark:'.$res_st_info['cs_remark'].'-拆分单:'.$res_st_info['split_from_trade_id'],\Think\Log::INFO);
			if($res_so_info['bill_type'] == 1 && $res_so_info['logistics_type'] == '1311' && !empty($res_so_info['logistics_no'])){
				$result_sync_waybill = D('Stock/WayBill','Controller')->sendWayBill($query_stockout_id,$res_so_info['logistics_id']);
				if($result_sync_waybill['status'] == 2){
					SE($result_sync_waybill['data']['fail'][0]['msg']);
				}elseif ($result_sync_waybill['status'] == 1){
					SE($result_sync_waybill['msg']);
				}
			}
			//重新读取consign_status,判断原始单是否已完成
	        $get_consign_status_fields = array("consign_status,weight,consign_time,post_cost");
	        $get_consign_status_cond = array("stockout_id"=>"{$stockout_id}");
	        $res_consign_status = D('Stock/StockOutOrder')->getStockoutOrders($get_consign_status_fields,$get_consign_status_cond);
	        $query_consign_status = $res_consign_status[0]['consign_status'];
			$success = array(
	            'id' => $query_stockout_id,
	            'stock_no' => $res_so_info['stockout_no'],
	            'status' =>$query_status,
	            'consign_status' => $query_consign_status,
	            'consign_time' => $res_consign_status[0]['consign_time'],
	            'weight' => $res_consign_status[0]['weight'],
	            'post_cost' => $res_consign_status[0]['post_cost'],
	        );
			//发货发送短信
			$cfg_open_message_strategy = get_config_value('cfg_open_message_strategy',0);
			if($cfg_open_message_strategy){
				$type = $trade_type==3?3:1;
				UtilTool::crm_sms_record_insert($type,$query_trade_id);
			}
			//如果开启配置，生成物流追踪记录
			$query_logistics_no = $res_so_info['logistics_no'];
			$query_logistics_type = $res_so_info['logistics_type'];
			$query_logistics_id = $res_so_info['logistics_id'];
			$query_delivery_term = $res_st_info['delivery_term'];
			$query_receiver_mobile = $res_st_info['receiver_mobile'];
			$cfg_open_logistics_trace = get_config_value('cfg_open_logistics_trace',0);//物流追踪配置
			if($cfg_open_logistics_trace){
				$sql = "INSERT IGNORE INTO sales_logistics_trace(shop_id,warehouse_id,logistics_no,logistics_type,logistics_id,trade_id,stockout_id,created,delivery_term,receiver_mobile)
						VALUES(
						$query_shop_id,$query_warehouse_id,'{$query_logistics_no}','{$query_logistics_type}','{$query_logistics_id}',$query_trade_id,$query_stockout_id,NOW(),'{$query_delivery_term}','{$query_receiver_mobile}')";
				$this->execute($sql);

			  	$mutil_sql = "INSERT IGNORE INTO sales_logistics_trace(shop_id,warehouse_id,logistics_no,logistics_type,logistics_id,trade_id,stockout_id,created,delivery_term,receiver_mobile)
				SELECT $query_shop_id,$query_warehouse_id,srml.logistics_no,cl.logistics_type,srml.logistics_id,$query_trade_id,$query_stockout_id,NOW(),'{$query_delivery_term}','{$query_receiver_mobile}'
				FROM sales_record_multi_logistics srml,cfg_logistics cl WHERE srml.logistics_id = cl.logistics_id AND srml.trade_id = $query_trade_id";
				$this->execute($mutil_sql);

				$update_sql = "UPDATE sales_logistics_trace slt,sales_trade st
							  SET slt.trade_no = st.trade_no ,slt.stockout_no = st.stockout_no,slt.src_tids = st.src_tids,slt.buyer_nick = st.buyer_nick,
							  slt.receiver_name = st.receiver_name , slt.receiver_addr = st.receiver_address , slt.receiver_area = st.receiver_area,
							  slt.pay_time = st.pay_time,slt.created = NOW(),slt.delivery_term=st.delivery_term,slt.receiver_mobile='{$query_receiver_mobile}',
							  slt.receivable = st.receivable
							  WHERE slt.trade_id = st.trade_id AND st.trade_id = $query_trade_id";
				$this->execute($update_sql);
			}
			//支付宝对账
			$cfg_accounting_sync = get_config_value('account_sync',0);//支付宝对账配置
			if($cfg_accounting_sync)
			{
				//记录到平台对账发货金额 fa_alipay_account_check 平台对账表
				$account_check_no = $this->query("select FN_SYS_NO('account_check') account_check_no");
				$account_check_no = $account_check_no[0]['account_check_no'];
				$alipay_account_check_sql = "INSERT INTO fa_alipay_account_check(account_check_no,tid,send_amount,shop_id,
											platform_id,created,consign_time)
											(
												SELECT '{$account_check_no}',sto.src_tid,SUM(sod.total_amount)+SUM(sto.share_post),st.shop_id,
												sto.platform_id, now(),now()
												FROM stockout_order_detail sod, sales_trade_order sto,
												sales_trade st WHERE sod.src_order_detail_id=sto.rec_id AND sto.trade_id=st.trade_id
												AND sod.stockout_id=$query_stockout_id AND sto.gift_type=0 AND sto.refund_status<>5
												AND (st.trade_type=1 OR st.trade_type=2)
												GROUP BY sto.src_tid,sto.platform_id
											)
											ON DUPLICATE KEY UPDATE
											send_amount=send_amount+VALUES(send_amount)";
				$this->execute($alipay_account_check_sql);
				$check_detail_month_sql = "INSERT INTO fa_platform_check_detail_month(tid,platform_id,shop_id,check_month,send_amount,diff_amount,created)
										(
											SELECT  sto.src_tid,sto.platform_id,st.shop_id,DATE_FORMAT(NOW(),'%Y-%m'),SUM(sod.total_amount)+SUM(sto.share_post),
											SUM(sod.total_amount)+SUM(sto.share_post),now()
											FROM stockout_order_detail sod,sales_trade_order sto,sales_trade st
											WHERE sod.src_order_detail_id=sto.rec_id AND sto.trade_id=st.trade_id
											AND sod.stockout_id=$query_stockout_id AND sto.gift_type=0 AND sto.refund_status<>5
											AND ( st.trade_type=1 or st.trade_type=2 )
											GROUP BY sto.src_tid,sto.platform_id
										)
										ON DUPLICATE KEY UPDATE
										send_amount=send_amount+VALUES(send_amount),
										diff_amount=diff_amount+VALUES(diff_amount);";
				$this->execute($check_detail_month_sql);
			}

			//释放分拣框
			if ((int)$res_so_info['is_stalls']==1||(int)$res_so_info['is_stalls']==0)
			{
				$box_goods_detail_model = M('box_goods_detail');
				$stalls_less_goods_detail_model = D('Purchase/StallsOrderDetail');
				$sorting_wall_detail_model = M('sorting_wall_detail');
				$operator_stalls_pickup_log_model = M('operator_stalls_pickup_log');
				$box_goods_info = $box_goods_detail_model->field('rec_id,box_no')->fetchSql(false)->where(array('trade_id'=>$query_trade_id,'sort_status'=>0))->find();
				if(empty($box_goods_info)||$box_goods_info==null){
					$box_goods_info = $sorting_wall_detail_model->fetchSql(false)->field('box_no')->where(array('stockout_id'=>$query_stockout_id))->find();
				}
				if(!empty($box_goods_info)||$box_goods_info!=null){
					$box_goods_detail_model->where(array('trade_id'=>$query_trade_id,'sort_status'=>0))->save(array('sort_status'=>1));
					$sorting_wall_detail_model->where(array('box_no'=>$box_goods_info['box_no']))->save(array('is_use'=>0,'stockout_id'=>0));
					$dynamic_allocation_box =  get_config_value('dynamic_allocation_box',0);
					if($dynamic_allocation_box==1){
						$big_box_goods_map_model = M('big_box_goods_map');
						$big_box_goods_map_model->where(array("trade_id"=>$query_trade_id))->delete();
					}
				}
				if($res_so_info['is_stalls']==1){
					$operator_stalls_pickup_log = array();
					$stalls_less_goods_info = $stalls_less_goods_detail_model->field('unique_code,provider_id,price')->where(array('trade_id'=>$query_trade_id,'trade_status'=>0,'pickup_status'=>0))->select();
					foreach($stalls_less_goods_info as $v){
						$operator_stalls_pickup_log[] = array(
							'unique_code' => $v['unique_code'],
							'operator_id' => $operator_id,
							'purchase_id' => $v['provider_id'],
							'org_price' => $v['price'],
							'price' => $v['price'],
							'pickup_time' => array('exp','NOW()'),
						);
					}
					$operator_stalls_pickup_log_model->addAll($operator_stalls_pickup_log,array(),true);
					$stalls_less_goods_detail_model->fetchSql(false)->where(array('trade_id'=>$query_trade_id,'trade_status'=>0))->save(array('sort_status'=>3,'stockin_status'=>1,'pickup_status'=>1));
				}
			}
			$this->commit();
	    }catch(\PDOException $e){
	        $msg = $e->getMessage();
	        $fail[] = array(
	            'stock_id' => $query_stockout_id,
	            'stock_no' => $res_so_info['stockout_no'],
	            'msg' =>self::PDO_ERROR,
	        );
	
	        $this->rollback();
	
	        \Think\Log::write($debug_log_str.$msg."-".$res_so_info['stockout_no']);
	        return false;
	    }catch(BusinessLogicException $e){
	        $msg = $e->getMessage();
	        $fail[] = array(
	            'stock_id' => $query_stockout_id,
	            'stock_no' => $res_so_info['stockout_no'],
	            'msg' =>$msg,
	        );
	        $this->rollback();
	        return false;
	    }catch(\PDOException $e){
	        \Think\Log::write($this->name.'-consignStockoutOrder-stockout_no:'.$res_so_info['stockout_no'].'-'.$debug_log_str.'-'.$e->getMessage());
	        $fail[] = array(
	            'stock_id' => $query_stockout_id,
	            'stock_no' => $res_so_info['stockout_no'],
	            'msg' =>self::PDO_ERROR,
	        );
	        $this->rollback();
	        return false;
	    }catch(\Exception $e){
            \Think\Log::write($this->name.'-consignStockoutOrder-stockout_no:'.$res_so_info['stockout_no'].'-'.$debug_log_str.'-'.$e->getMessage());
	        $fail[] = array(
	            'stock_id' => $query_stockout_id,
	            'stock_no' => $res_so_info['stockout_no'],
	            'msg' =>self::PDO_ERROR,
	        );
	        $this->rollback();
	        return false;
	    }
	
	    return true;
    }
    public function revertConsignStatus($type,$stockout_id,$params,&$fail=array(),&$success=array())
    {
        try {
            $debug_point_str = __CONTROLLER__.'-revertSalesStockoutCheckGoods-';
            $config_values = array(
                'stockout_disable_revert' => 1, //禁止撤销出库
                'stockout_consign_disable_revert' =>1, //发货后禁止撤销发货
            );
            $operator_type = array(
                '1'=>'验货',
                '2'=>'称重',
            );
            $this->startTrans();
            $stockout_fields = "stockout_id,stockout_no,`status`,consign_status,src_order_type,src_order_id,checkouter_id,freeze_reason,warehouse_id,is_allocated,warehouse_type,weight";//18
            $stockout_cond = array('stockout_id'=>array('eq',$stockout_id));
            $res_so_info = $this->getStockoutOrderLock($stockout_fields,$stockout_cond);

            if (empty($res_so_info))
            {
                SE("单据不存在");
            }
            $query_stockout_id = $res_so_info['stockout_id'];
            $query_warehouse_id = $res_so_info['warehouse_id'];
            $query_trade_id = $res_so_info['src_order_id'];
            $sales_trade_info = D('Trade/SalesTrade')->field(array('platform_id','trade_from'))->where(array('trade_id'=>$query_trade_id))->find();

            if((int)$res_so_info['status'] < 55)
            {
                SE("出库单状态不正确");
            }
            if((int)$res_so_info['status'] >= 95)
            {
                SE("订单已发货");
            }
            if ((int)$res_so_info['src_order_type'] <> 1)
            {
                SE("不是销售出库单");
            }
            if (!((int)$res_so_info['consign_status'] & 1) && (int)$type&1)
            {
                SE("出库单未验货");
            }
			if (!((int)$res_so_info['consign_status'] & 2) && (int)$type&2)
            {
                SE("出库单未称重");
            }
            /*if(((int)$res_so_info['consign_status'] & 4) && $config_values['stockout_disable_revert'])//可能在加配置的时候来判断是否
            {
                SE("系统禁止撤销出库");
            }*/
            if( (int)$res_so_info['status'] == 95  )
            {
                SE("请先撤销出库");
            }
            if( (int)$res_so_info['status'] > 95 && (int)$sales_trade_info['platform_id'])
            {
                SE("线上订单已完成，系统禁止驳回".$operator_type[$type]);
            }
            if( (int)$res_so_info['status'] > 95 && !(int)$sales_trade_info['platform_id'])
            {
                SE("请先撤销出库");
            }

            if((int)$res_so_info['status']<95)
            {
               
                $mask = $this->query('select ((~0)&(~'.$type.')&0xFFFFFFFF) as mask;');
                $mask = $mask['0']['mask'];
                $update_stockout_order_data = array(
                    'status'=>55,
                    'consign_status'=>array('exp','(consign_status&'.$mask.')'),
                    'block_reason'=>0,
                );
				if((int)$type&2){
					$update_stockout_order_data['weight'] = 0;
				}
                $update_stockout_order_conditons = array('stockout_id'=>$query_stockout_id);
                $res_update_stockout_order = D('Stock/StockOutORder')->updateStockoutOrder($update_stockout_order_data,$update_stockout_order_conditons);
                $update_sales_trade_data = array(
                    'status'=>55,
                    'consign_status'=>array('exp','(consign_status&'.$mask.')'),
                );
                $update_sales_trade_conditons = array(
                    'trade_id'=>$query_trade_id  
                );
                $res_update_sales_trade = D('Trade/Trade')->updateSalesTrade($update_sales_trade_data,$update_sales_trade_conditons);
                $mask = $this->query('select 1&'.$res_so_info['consign_status'].' as mask;');
                $mask = $mask['0']['mask'];
                $update_sales_trade_log_data = array('data'=>99);
                $update_sales_trade_log_conditions = array('trade_id'=>$res_so_info['src_order_id'],'type'=>100);
                $res_update_sales_trade_log = D('Trade/SalesTradeLog')->updateTradeLog($update_sales_trade_log_data,$update_sales_trade_log_conditions);
                $insert_sales_trade_log_data = array(
                    'trade_id'=>$res_so_info['src_order_id'],
                    'operator_id'=>$params['operator_id'],
                    'type'=>120,
                    'message'=>array('exp',"MAKE_SET($type,'驳回验货','驳回称重')")
                );
                $res_insert_sales_log = D('Trade/SalesTradeLog')->addTradeLog($insert_sales_trade_log_data);
                $update_stockout_detail_data = array(
                    'is_examined'=>0,
                    'scan_type'=>0,
                );
                $update_stockout_detail_conditions = array(
                    'stockout_id'=>$query_stockout_id
                );
                $res_update_stockout_detail = D('Stock/StockoutOrderDetail')->updateStockoutOrderDetail($update_stockout_detail_data,$update_stockout_detail_conditions);
            }
            $final_so_info = $this->getStockoutOrderLock($stockout_fields,$stockout_cond);
            $success = array(
                'id' => $query_stockout_id,
                'stock_no' => $res_so_info['stockout_no'],
                'consign_status'=>$final_so_info['consign_status'],
                'weight'=>$final_so_info['weight']
            );
            $this->commit();
            return true;
        } catch(BusinessLogicException $e){
	        $msg = $e->getMessage();
	        $fail[] = array(
	            'stock_id' => empty($query_stockout_id)?$stockout_id:$query_stockout_id,
	            'stock_no' => @$res_so_info['stockout_no'],
	            'msg'      => $msg,
	        );
	        $this->rollback();
	        return false;
	    }catch(\PDOException $e){
	        $msg = $e->getMessage();
	        $fail[] = array(
	            'stock_id' => empty($query_stockout_id)?$stockout_id:$query_stockout_id,
	            'stock_no' => @$res_so_info['stockout_no'],
	            'msg'      => self::PDO_ERROR,
	        );
	        $this->rollback();
	        \Think\Log::write($this->name.'-revertConsignStatus-'.$debug_point_str.$msg."-".$res_so_info['stockout_no']);

	        return false;
	    }catch(\Exception $e){
	        $msg = $e->getMessage();
	        $fail[] = array(
	            'stock_id' => $query_stockout_id,
	            'stock_no' => @$res_so_info['stockout_no'],
	            'msg' =>self::PDO_ERROR,
	        );
	        $this->rollback();
	        \Think\Log::write($this->name.'-revertConsignStatus-'.$debug_point_str.$msg."-".$res_so_info['stockout_no']);
	        return false;
	    }
        
    }
    public function unblockStockout($stockout_id,$params,&$fail=array(),&$success=array())
    {
        try {
            $query_config_values = get_config_value(array('order_deliver_block_consign','prevent_online_block_consign_stockout'),array(0,0));
            $debug_point_str = __CONTROLLER__.'-unblockStockout-';
            $block_reason_names = C('stockout_block_reason');
            $stockout_fields = "stockout_id,stockout_no,block_reason,src_order_id";//18
            $stockout_cond = array('stockout_id'=>array('eq',$stockout_id));
            $res_so_info = $this->getStockoutOrders($stockout_fields,$stockout_cond);
            if (empty($res_so_info))
            {
                SE("单据不存在");
            }
            $res_so_info = $res_so_info[0];
            $query_stockout_id = $res_so_info['stockout_id'];
            $query_trade_id = $res_so_info['src_order_id'];
            $query_block_reason = (int)$res_so_info['block_reason'];
            if((int)$res_so_info['block_reason'] & (2|4|32|128|256))
            {
                SE( $this->getBlockReason($query_block_reason)."不能清除");
            }
            if((int)$res_so_info['block_reason']){
                $this->startTrans();
                $update_stockout_block_data = array('block_reason'=>0);
                if((int)$res_so_info['block_reason'] & 4096){
                    if($query_config_values['order_deliver_block_consign'] != 1 ||  ($query_config_values['order_deliver_block_consign'] == 1 && $query_config_values['prevent_online_block_consign_stockout'] == 0))
                    {
                        $update_stockout_block_data = array('block_reason'=>0);
                    }else{
                        $update_stockout_block_data = array('block_reason'=>4096);
                    }
                }
                $update_stockout_block_conditions = array('stockout_id'=>$query_stockout_id);
                $res_update_block_reason = $this->updateStockoutOrder($update_stockout_block_data, $update_stockout_block_conditions);
                $insert_inout_log_data = array(
                    'order_type'=>2,
                    'order_id'=>  $query_stockout_id,
                    'operator_id'=>$params['operator_id'],
                    'operate_type'=>120,
                    'message'=>'清除订单拦截原因: '.$this->getBlockReason($query_block_reason),
                );
                $res_insert_inout_log = D('Stock/StockInoutLog')->insertStockInoutLog($insert_inout_log_data);
                
                $insert_sales_log_data = array(
                    'trade_id' => $query_trade_id,
                    'operator_id'=>$params['operator_id'],
                    'type' => 160,
                    'message'=>'清除订单拦截原因: '.$this->getBlockReason($query_block_reason),
                );
                $res_insert_sales_log = D('Trade/SalesTradeLog')->addTradeLog($insert_sales_log_data);
                $final_so_info = $this->getStockoutOrders($stockout_fields,$stockout_cond);
                
                $success = array(
                    'id' => $query_stockout_id,
                    'stock_no' => $res_so_info['stockout_no'],
                    'block_reason'=>$final_so_info[0]['block_reason']
                );
                $this->commit();
               
            }else{
                SE('出库单未被拦截');
            }
            return true;
        } catch(\PDOException $e){
            $msg = $e->getMessage();
            $fail[] = array(
                'stock_id' => empty($query_stockout_id)?$stockout_id:$query_stockout_id,
                'stock_no' => @$res_so_info['stockout_no'],
                'msg'      => self::PDO_ERROR,
            );
            $this->rollback();
            \Think\Log::write($debug_point_str.$msg."-".$res_so_info['stockout_no']);
             
            return false;
        }catch(BusinessLogicException $e){
            $msg = $e->getMessage();
            $fail[] = array(
                'stock_id' => empty($query_stockout_id)?$stockout_id:$query_stockout_id,
                'stock_no' => @$res_so_info['stockout_no'],
                'msg'      => $msg,
            );
            $this->rollback();
            return false;
        }catch(\PDOException $e){
            $msg = $e->getMessage();
            $fail[] = array(
                'stock_id' => $query_stockout_id,
                'stock_no' => @$res_so_info['stockout_no'],
                'msg' =>$msg,
            );
            $this->rollback();
            \Think\Log::write($this->name.'-unblockStockout-'.$debug_point_str.$msg."-".$res_so_info['stockout_no']);
            return false;
        }catch(\Exception $e){
            $msg = $e->getMessage();
            $fail[] = array(
                'stock_id' => $query_stockout_id,
                'stock_no' => @$res_so_info['stockout_no'],
                'msg' =>self::PDO_ERROR,
            );
            $this->rollback();
            \Think\Log::write($this->name.'-unblockStockout-'.$debug_point_str.$msg."-".$res_so_info['stockout_no']);
            return false;
        }
    }
    public function unblockStockoutAndStallsOrder($trade_no){

        try{
            $query_config_values = get_config_value(array('order_deliver_block_consign','prevent_online_block_consign_stockout'),array(0,0));
            $stockout_fields = "stockout_id,stockout_no,block_reason,src_order_id";
            $stockout_cond = array('src_order_no'=>array('eq',$trade_no));
            $res_so_info = $this->getStockoutOrders($stockout_fields,$stockout_cond);
            if (empty($res_so_info))
            {
                SE("出库单不存在");
            }
            $res_so_info = $res_so_info[0];
            $query_stockout_id = $res_so_info['stockout_id'];
            $query_trade_id = $res_so_info['src_order_id'];
            $query_block_reason = (int)$res_so_info['block_reason'];
            $operate_id = get_operator_id();

            $this->startTrans();

            $update_stockout_block_data = array('block_reason'=>0);
            if((int)$res_so_info['block_reason'] & 4096){
                if($query_config_values['order_deliver_block_consign'] != 1 ||  ($query_config_values['order_deliver_block_consign'] == 1 && $query_config_values['prevent_online_block_consign_stockout'] == 0))
                {
                    $update_stockout_block_data = array('block_reason'=>0);
                }else{
                    $update_stockout_block_data = array('block_reason'=>4096);
                }
            }

            $update_stockout_block_conditions = array('src_order_no'=>$trade_no);
            $update_stalls_block_conditions = array('trade_no'=>$trade_no);
            $res_update_block_reason = $this->updateStockoutOrder($update_stockout_block_data, $update_stockout_block_conditions);
            D('Purchase/StallsOrderDetail')->where($update_stalls_block_conditions)->save($update_stockout_block_data);

            $insert_inout_log_data = array(
                'order_type'=>2,
                'order_id'=>  $query_stockout_id,
                'operator_id'=>$operate_id,
                'operate_type'=>120,
                'message'=>'清除订单拦截原因: '.$this->getBlockReason($query_block_reason),
            );
            $res_insert_inout_log = D('Stock/StockInoutLog')->insertStockInoutLog($insert_inout_log_data);

            $insert_sales_log_data = array(
                'trade_id' => $query_trade_id,
                'operator_id'=>$operate_id,
                'type' => 160,
                'message'=>'清除订单拦截原因: '.$this->getBlockReason($query_block_reason),
            );
            $res_insert_sales_log = D('Trade/SalesTradeLog')->addTradeLog($insert_sales_log_data);

            $this->commit();

        }catch(BusinessLogicException $e){
            $msg = $e->getMessage();
            $this->rollback();
            return false;
        }catch(\PDOException $e){
            $msg = $e->getMessage();
            $this->rollback();
            \Think\Log::write($this->name.'-unblockStockout-'.$msg);
            return false;
        }catch(\Exception $e){
            $msg = $e->getMessage();
            $this->rollback();
            \Think\Log::write($this->name.'-unblockStockout-'.$msg);
            return false;
        }
    }
    public function getBlockReason($reason_id){
        $block_reason_list = C('stockout_block_reason'); 
        $str = '';
        
        foreach ($block_reason_list as $key => $value)
        {
            if((int)$reason_id&(int)$key){
                $str = $str.$value.',';
            }
        }
        $str = trim($str,',');
        return $str;
    }
	public function chgLogistics($chg_info,&$fail=array(),&$success=array())
	{
		$operator_id = get_operator_id();
		$section_info = __CONTROLLER__."-chgLogistics-";
		try{
			$this->startTrans();
		    $res_so_info = $this->query('SELECT so.stockout_id,so.checkouter_id,so.status,so.freeze_reason,so.stockout_no,so.src_order_id,so.block_reason,so.logistics_id,so.logistics_no,so.warehouse_id,st.shop_id FROM stockout_order so FORCE INDEX(PRIMARY),sales_trade st FORCE INDEX(PRIMARY)	WHERE so.stockout_id = %d AND so.src_order_type=1 AND st.trade_id = so.src_order_id AND so.logistics_id<>%d;',$chg_info['id'],$chg_info['logistics_id']);//,so.receiver_province,so.receiver_city,so.receiver_district,so.calc_weight

			if(empty($res_so_info)){
				SE('没有获取到相应出库单或物流没有变');
			}
			$res_so_info = $res_so_info[0];
			$res_logistics_info = D('Setting/Logistics')->field(' logistics_name,logistics_type,bill_type ')->where(array('logistics_id'=>$chg_info['logistics_id']))->find();
			if(empty($res_logistics_info))
			{
				SE('没有查到要修改的物流信息');
			}
			if($res_so_info['status']<>55)
			{
				SE('出库单状态不正确');
			}
			if((int)$res_so_info['block_reason'] & (1|2|4|32|128|256))
			{
				SE( '阻止出库:'.$this->getBlockReason($res_so_info['block_reason']));
			}

			/*SELECT master_logistics_no INTO V_MasterLogisticsNO FROM stock_logistics_print WHERE stockout_id=V_StockoutId AND logistics_id=V_LogisticsID AND logistics_no=V_LogisticsNo;
		IF V_MasterLogisticsNO<>'' THEN
			INSERT INTO tbl_stockout_order_error(rec_id,stockout_no,error)
			VALUES(V_StockoutId,V_StockoutNo,CONCAT('单据为顺丰子母单,请先作废再修改物流方式'));
			ITERATE ORDER_LABEL;
		END IF;*/
			$section_info = __CONTROLLER__."-chgLogistics-".'调用物流单回收存储过程';
			$res_recover_logistics = D('Stock/StockLogisticsNo')->recoverLogisticsNO($res_so_info['stockout_id']);

			$this->removeMultiLogistics($res_so_info['stockout_id']);
			
			$sql_update_stockout = "UPDATE stockout_order SET logistics_no='' WHERE stockout_id={$res_so_info['stockout_id']}";
			$section_info = __CONTROLLER__."-chgLogistics-".'清除物流单号';
			$this->execute($sql_update_stockout);
			$sql_update_sales = "UPDATE sales_trade SET logistics_no='' WHERE trade_id={$res_so_info['src_order_id']}";
			$this->execute($sql_update_sales);
			$section_info = __CONTROLLER__."-chgLogistics-".'改变物流id';
			$this->execute("UPDATE sales_trade SET logistics_id =%d,logistics_no='',version_id = version_id +1 WHERE trade_id=%d;",$chg_info['logistics_id'],$res_so_info['src_order_id']);
			$this->execute("INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message)VALUES (%d,%d,20,CONCAT('单据打印--修改物流为:', '%s'));",$res_so_info['src_order_id'],$operator_id,$res_logistics_info['logistics_name']);
			$this->execute("UPDATE stockout_order SET logistics_id = %d,logistics_no='',package_count = '1' WHERE stockout_id = %d;",$chg_info['logistics_id'],$res_so_info['stockout_id']);
			$success = array('id'=>$chg_info['id'],'logistics_id'=>$chg_info['logistics_id'],'logistics_name'=>$res_logistics_info['logistics_name'],'bill_type'=>$res_logistics_info['bill_type'],'logistics_type'=>$res_logistics_info['logistics_type']);
			$this->commit();
	    }catch(BusinessLogicException $e){
			$msg = $e->getMessage();
			$fail[] = array(
			'stock_id' => $chg_info['id'],
			'stock_no' => $res_so_info['stockout_no'],
			'msg' =>$msg,
			);
			$this->rollback();
			return false;
		}catch(\PDOException $e){
			$msg = $e->getMessage();
			$fail[] = array(
			'stock_id' => $chg_info['id'],
			'stock_no' => $res_so_info['stockout_no'],
			'msg' =>self::PDO_ERROR,
			);

			$this->rollback();

			\Think\Log::write($this->name.'-chgLogistics-'.$section_info.$msg."-".$res_so_info['stockout_no']);
			return false;
		}catch(\Exception $e){
			$msg = $e->getMessage();
			$fail[] = array(
					'stock_id' => $chg_info['id'],
					'stock_no' => $res_so_info['stockout_no'],
					'msg' =>self::PDO_ERROR,
			);
			$this->rollback();
			\Think\Log::write($this->name.'-chgLogistics-'.$section_info.$msg."-".$res_so_info['stockout_no']);
			return false;
		}

	    return true;
	}
	private function removeMultiLogistics($stockout_id){
        $multi_logistics = D('Stock/SalesMultiLogistics')->alias('sml')->join('left join stockout_order so on so.stockout_id 
			= sml.stockout_id')->join(' LEFT JOIN cfg_logistics cl ON cl.logistics_id=sml.logistics_id')->
        join(' LEFT JOIN sales_trade st ON st.trade_id = so.src_order_id')
            ->field('sml.logistics_no,sml.logistics_id,so.stockout_id,so.src_order_type,so.src_order_id,
			so.stockout_no,so.receiver_area,so.receiver_dtb,so.receiver_name,so.receiver_mobile,cl.logistics_type,cl.bill_type
			,IF(st.trade_from=2 AND ( IFNULL(st.src_tids,1) OR IF(TRIM(st.src_tids)="",1,0)),st.trade_no,st.src_tids) AS src_tids 
			')->where(array('sml.stockout_id'=>$stockout_id))->fetchSql(false)->select();
        foreach($multi_logistics as $value){
            if(empty($value['logistics_no']) || (int)$value['bill_type'] == 0)
            {
                continue;
            }
            $status = $value['bill_type'] == 1? 6:5;
            $value['receiver_name'] = str_replace("'","''",$value['receiver_name']);
            $value['receiver_area'] = str_replace("'","''",$value['receiver_area']);
            $sql_logistics_no = "INSERT INTO stock_logistics_no(logistics_id,logistics_no,logistics_type,status,stockout_id,src_tids,sender_province,sender_city,sender_district,sender_address,receiver_dtb,receiver_info,created)"
                ." SELECT '{$value['logistics_id']}','{$value['logistics_no']}',{$value['logistics_type']},{$status},{$value['stockout_id']},'{$value['src_tids']}',sw.province,sw.city,sw.district,sw.address,'{$value['receiver_dtb']}',CONCAT('{$value['receiver_area']}','{$value['receiver_name']}','{$value['receiver_mobile']}'),now()"
                ."	FROM stockout_order so LEFT JOIN cfg_warehouse sw ON so.warehouse_id = sw.warehouse_id"
                ." WHERE so.stockout_id={$stockout_id}"
                ." ON DUPLICATE KEY UPDATE stockout_id=VALUES(stockout_id),src_tids = VALUES(src_tids),status = {$status},receiver_dtb = VALUES(receiver_dtb),receiver_info=VALUES(receiver_info);";
            $this->execute($sql_logistics_no);
        }
        D('Stock/SalesMultiLogistics')->where(array('stockout_id'=>$stockout_id))->delete();

        //回收多余的电子面单
        $sln_model = D('Stock/StockLogisticsNo');
        $stockLogisticsNos = $sln_model->field('rec_id')->where(array("stockout_id"=>$stockout_id))->select();
        $ids = array();
        if(count($stockLogisticsNos)>1){
            foreach ($stockLogisticsNos as $logisticsNoVal){
                $ids[] = $logisticsNoVal['rec_id'];
            }
            $result_info = array('status'=>0,'info'=>'成功','data'=>array('fail'=>array(),'success'=>array()));
            $sln_model->retrieve($ids,$result_info);
        }
    }
	public function chgPrintStatus($chg_info,&$result)
	{
		$operator_id = get_operator_id();
		$section_info = __CONTROLLER__."-chgPrintStatus-";
		try{
			$this->creatTempTable($chg_info['ids'],true);
			$this->startTrans();
			$print_type_mask = 0;

			$print_status_values = array(
				'sendbill_print_status'=>'0',
				'logistics_print_status'=>'0',
                'picklist_print_status'=>'0'
			);
			$print_status_flags = array(
                'picklist_print_status'=>4,
                'sendbill_print_status'=>2,
                'logistics_print_status'=>1
			);

			$ids = implode(',',$chg_info['ids']);
			foreach($chg_info['status_info'] as $key => $value){
				if($value['print_status'] != 1 && $value['print_status'] !=0){

					continue;
				}
				$print_status_values[$value['print_class']] = $value['print_status'];
				$print_type_mask = $print_type_mask | $print_status_flags[$value['print_class']];
			}
			$this->execute('SET @tmp_chg=0');
			$update_sql = 'UPDATE stockout_order so, tmp_xchg tx'
					      .' SET logistics_print_status=IF(('.$print_type_mask.'&1) AND logistics_print_status<>"'.$print_status_values['logistics_print_status'].'",IF(@tmp_chg:=1,"'.$print_status_values['logistics_print_status'].'",0),IF(@tmp_chg:=0,0,logistics_print_status)),'
					      .'sendbill_print_status=IF(('.$print_type_mask.'&2) AND sendbill_print_status<>"'.$print_status_values['sendbill_print_status'].'",IF(@tmp_chg:=@tmp_chg|2,"'.$print_status_values['sendbill_print_status'].'",0),sendbill_print_status),'
                          .'picklist_print_status=IF(('.$print_type_mask.'&4) AND picklist_print_status<>"'.$print_status_values['picklist_print_status'].'",IF(@tmp_chg:=@tmp_chg|4,"'.$print_status_values['picklist_print_status'].'",0),picklist_print_status),'
                          .'tx.f3=@tmp_chg '
					      .'WHERE so.stockout_id=tx.f1 AND so.src_order_type=1;';
			$this->execute($update_sql);

			//更新打印状态在sales_trade_log中的日志type 91 标记打印 打印完成data=0 表示打印状态为未打印 data= 1|2|4   //1物流单,2发货单
			$this->execute('UPDATE sales_trade_log stl,stockout_order so,tmp_xchg tx'
					     .' SET stl.data=stl.data&(~@tmp_chg)'
						 .' WHERE stl.trade_id = so.src_order_id AND so.src_order_type=1 AND so.stockout_id = tx.f1 AND stl.type=91;');

			$chg_sendbill_log_info = $print_status_values['sendbill_print_status'] ?'设置发货单':'清除发货单';
			$chg_logistics_log_info = $print_status_values['logistics_print_status'] ?'设置物流单':'清除物流单';
            $chg_picklist_log_info = $print_status_values['picklist_print_status'] ?'设置分拣单':'清除分拣单';
            $this->execute("INSERT INTO stock_inout_log(order_type,order_id,operator_id,operate_type,message)"
						  ." (SELECT 2,f1,{$operator_id},7,CONCAT('修改打印状态:',MAKE_SET(f3,'{$chg_logistics_log_info}','{$chg_sendbill_log_info}','{$chg_picklist_log_info}'))"
					 	  ." FROM tmp_xchg WHERE f3<>0);");
			$this->execute("INSERT INTO sales_trade_log(trade_id,operator_id,`type`,message)"
						  ." (SELECT so.src_order_id,{$operator_id},90,CONCAT('修改打印状态:',MAKE_SET(f3,'{$chg_logistics_log_info}','{$chg_sendbill_log_info}','{$chg_picklist_log_info}'))"
					 	  ." FROM stockout_order so,tmp_xchg tx WHERE so.stockout_id=tx.f1 AND so.src_order_type=1 AND tx.f3<>0);");
//					 	  ." FROM stockout_order so,tmp_xchg tx WHERE so.stockout_id=tx.f1 AND so.src_order_type=1);");
			$this->execute("DELETE FROM tmp_xchg;");

			$this->commit();
			$result['status']= 0;
//			$result['data']=array('success'=>$res_query);
	    }catch(\PDOException $e){
			$msg = $e->getMessage();
			$result['status'] = 1;
			$result['info'] = self::PDO_ERROR;
			$this->rollback();
			\Think\Log::write($section_info.$msg);
			return false;
		}catch(\Exception $e){
			$msg = $e->getMessage();
			$result['status'] = 1;
			$result['info'] = $msg;
			$this->rollback();
			\Think\Log::write($section_info.$msg);
			return false;
		}

	    return true;
	}
	private function creatTempTable($ids,$is_clear){
//		$sql_drop = "DROP TABLE IF EXISTS  `tmp_import_detail`";
		try{
			$sql      = "CREATE TEMPORARY TABLE IF NOT EXISTS `tmp_xchg` (
						rec_id int(11) NOT NULL AUTO_INCREMENT,
						f1 VARCHAR(40),
						f2 VARCHAR(1024),
						f3 VARCHAR(40),
						PRIMARY KEY (rec_id)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
			$this->execute($sql);
			if($is_clear){
				$this->execute('DELETE FROM tmp_xchg;');
			}
			foreach($ids as $index=>$item){
				$this->execute('INSERT INTO tmp_xchg(f1) VALUES('.$item['id'].');');
			}
		}catch (\PDOException $e){
			$msg = $e->getMessage();
			\Think\Log::write($this->name.'-creatTempTable-'.$msg);
			E(self::PDO_ERROR);
		}

	}
	public function revertStockout($stockout_id,$is_force,&$fail=array(),&$success=array())
	{
		try{
			$this->startTrans();
			//-----------获取配置值
			$stock_out_revert = get_config_value('stockout_consign_disable_revert',0);//是否禁止撤销发货,出库子状态在发货之后

			//-----------判断是否符合出库

			$stockout_info = $this->field('stockout_no,`status`,consign_status,src_order_id,checkouter_id,freeze_reason,warehouse_id,is_allocated,warehouse_type')->where(array('stockout_id'=>$stockout_id))->lock(true)->find();

			if((int)$stockout_info['warehouse_type'] > 1 && (int)$stockout_info['warehouse_type'] != 127)
			{
				SE('外部仓储系统单据不允许驳回操作');
			}
			if( (int)$stockout_info['freeze_reason']<>0 )
			{
				SE('出库单已经冻结');
			}

			$error_ar = array('95'=>'发货','100'=>'签收','105'=>'部分打款','110'=>'完成');
			$sales_trade_info = D('Trade/SalesTrade')->field('platform_id,src_tids','trade_from')->where(array('trade_id'=>$stockout_info['src_order_id']))->find();
			if($is_force)
			{

				if($sales_trade_info['platform_id'] && !empty($sales_trade_info['src_tids']) && $stockout_info['status']>=95 && $is_force!=2)
				{
					$api_trade_info = D('Trade/ApiTrade')->field('GROUP_CONCAT(trade_status) AS trade_status')->where(array('tid'=>array('in',$sales_trade_info['src_tids'])))->find();
					$api_trade_arr = explode(',',$api_trade_info['trade_status']);
					$is_stop = false;
					foreach($api_trade_arr as $value){
						if($value >= 60){$is_stop = true;}
					}
					if($stockout_info['status']>=100 || $is_stop){
						SE('订单已签收，不能撤销出库！');
					}
				}
				if( $sales_trade_info['platform_id'] && $stockout_info['status']>=100)
				{
					SE('出库单已'.$error_ar["{$stockout_info['status']}"]);
				}
			}else{
				if( $stockout_info['status']>=95)
				{
					SE('出库单已'.$error_ar["{$stockout_info['status']}"]);
				}
			}

			if($stockout_info['status']=95 && $stock_out_revert)
			{
				SE('系统禁止撤销发货');
			}
			if((int)$sales_trade_info['trade_from'] == 4)
            {
                SE("现款销售的单子禁止撤销出库");
            }

			$this->checkStockoutRevert($stockout_id);

			$final_resutl = $this->where(array('stockout_id'=>$stockout_id))->find();
			$success = array('id'=>$stockout_id,'stock_no'=>$final_resutl['stockout_no'],'consign_status'=>$final_resutl['consign_status'],'consign_time'=>$final_resutl['consign_time'],'status'=>$final_resutl['status']);
			$this->commit();

		 } catch(BusinessLogicException $e){
			$msg = $e->getMessage();
			$fail[] = array(
				'stock_id' => $stockout_id,
				'stock_no' => @$stockout_info['stockout_no'],
				'msg' =>$msg,
			);
			$this->rollback();
			return false;

		}catch(\PDOException $e){
			$msg = $e->getMessage();
			$fail[] = array(
			'stock_id' => $stockout_id,
			'stock_no' => @$stockout_info['stockout_no'],
			'msg'      => self::PDO_ERROR,
			);
			$this->rollback();
			\Think\Log::write($this->name.'-revertStockout-'.$msg."-".$stockout_info['stockout_no']);

			return false;
		 }catch(\Exception $e){
				$msg = $e->getMessage();
				$fail[] = array(
					'stock_id' => $stockout_id,
					'stock_no' => @$stockout_info['stockout_no'],
					'msg' =>$msg,
				);
				$this->rollback();
				\Think\Log::write($this->name.'-revertStockout-'.$msg."-".$stockout_info['stockout_no']);
				return false;
		 }
	}
	public function checkStockoutRevert($stockout_id)
	{
		try{
			$operator_id = get_operator_id();
			//-------------查询出库单详情
			$stockout_fields = array('so.src_order_id','so.src_order_type','so.src_order_no','so.stockout_no','so.is_allocated','so.warehouse_id','so.status','so.consign_status','so.logistics_id','so.logistics_no','so.post_cost','so.weight','so.customer_id','so.warehouse_type','cl.bill_type');
			$stockout_info = $this->alias('so')->join('LEFT JOIN cfg_logistics cl on so.logistics_id = cl.logistics_id')->field($stockout_fields)->where(array('stockout_id'=>$stockout_id))->lock(true)->find();

			if(empty($stockout_info))
			{
				SE('出库单不存在!');
			}

			if($stockout_info['src_order_type'] != 1)
			{
				SE('出库单不是销售出库单!');
			}

			if($stockout_info['status'] < 95)
			{
				SE('出库单还没有出库,请刷新界面重试!');
			}

			if($stockout_info['warehouse_type']>1 && $stockout_info['status']>=95 && (int)$stockout_info['warehouse_type'] != 127)
			{
				SE('外部仓储系统单据不能撤销发货');
			}
			$unix_timestamp = time();
			//----负库存恢复
			$stockout_detail_neg = D('Stock/StockoutOrderDetail')->field(array("{$stockout_info['warehouse_id']} as warehouse_id",'spec_id','-num as neg_stockout_num','num as stock_num'))->where(array('stockout_id'=>$stockout_id))->order('spec_id')->select();
			//库存操作日志需要记录操作前库存，因此需要在改变stock_spec之前查询
			$stock_log_bef  = D('Stock/StockoutOrderDetail')->alias('sod')->field(array("IFNULL(ss.rec_id,0) stock_spec_id,'{$operator_id}' as operator_id,CONCAT('撤销销售出库-',so.stockout_no) message,sod.num, IFNULL(ss.stock_num,0) stock_num,2 operator_type"))->join('left join stockout_order so on so.stockout_id = sod.stockout_id')->join('stock_spec ss on ss.spec_id = sod.spec_id and so.warehouse_id = ss.warehouse_id')->where(array("sod.stockout_id"=>$stockout_id))->select();
			$res_update_stock_spec_neg = D('Stock/StockSpec')->addAll($stockout_detail_neg,array(),array('neg_stockout_num'=>array('exp','neg_stockout_num+VALUES(neg_stockout_num)'),'today_num'=>array('exp','IF(DATE(last_sales_time)=CURRENT_DATE(),GREATEST(today_num-VALUES(stock_num),0),today_num)'),'stock_num'=>array('exp','stock_num+VALUES(stock_num)')));

			//-----恢复库存
			//$stockout_detail_num = D('Stock/StockoutOrderDetail')->field(array("{$stockout_info['warehouse_id']} as warehouse_id",'spec_id','num as stock_num'))->where(array('stockout_id'=>$stockout_id))->order('spec_id')->select();
            D('Stock/StockSpecLog')->addAll($stock_log_bef);
			//$res_update_stock_spec_num = D('Stock/StockSpec')->addAll($stockout_detail_num,array(),array('today_num'=>array('exp','IF(DATE(last_sales_time)=CURRENT_DATE(),GREATEST(today_num-VALUES(stock_num),0),today_num)'),'stock_num'=>array('exp','stock_num+VALUES(stock_num)')));

			//------更新货位信息
			$stockout_detail_position = D('Stock/StockoutOrderDetail')->alias('sod')->field(array("{$stockout_info['warehouse_id']} as warehouse_id","sod.spec_id as spec_id","IF(cwp.rec_id,cwp.rec_id,cwp1.rec_id) as position_id","IF(cwp.rec_id,cwp.zone_id,cwp1.zone_id) as zone_id","sod.num as stock_num"))->join('stock_spec_position ssp on ssp.warehouse_id='.$stockout_info['warehouse_id'].' and ssp.spec_id = sod.spec_id')->join('cfg_warehouse_position cwp on cwp.rec_id = ssp.position_id')->join('cfg_warehouse_position cwp1 on cwp1.rec_id = -'.$stockout_info['warehouse_id'])->where(array('sod.stockout_id'=>$stockout_id))->group('sod.rec_id')->select();

			$res_stock_spec_position = D('Stock/StockSpecPosition')->addAll($stockout_detail_position,array(),array('stock_num'=>array('exp','stock_num+VALUES(stock_num)')));
			//------恢复待发货量
			$this->execute("CALL I_RESERVE_STOCK('{$stockout_info['src_order_id']}',4,'{$stockout_info['warehouse_id']}',0)");

			//-------更新邮费成本
			$res_trade_count = D('Account/LogisticsFeeManagement')->where(array('logistics_id'=>$stockout_info['logistics_id'],'logistics_no'=>$stockout_info['logistics_no']))->getField('trade_count');

            //-------京东面单回收
            if($stockout_info['bill_type'] == 1){
            	D('Stock/StockLogisticsNo')->recoverLogisticsNO($stockout_id);
            	//D('Stock/StockLogisticsNo')->where(array('stockout_id'=>$stockout_id,'status'=>array("in","1,5")))->save(array('status'=>6));
            	
            }
			//-------删除物流追踪信息
			$this->deleteLogisticsTrace($stockout_info['src_order_id']);

			if($res_trade_count != null)
			{
				if($res_trade_count <= 1)
				{
					$res_delete_fa = D('Account/LogisticsFeeManagement')->where(array('logistics_id'=>$stockout_info['logistics_id'],'logistics_no'=>$stockout_info['logistics_no']))->delete();
				}else{
					$shop_id = D('Trade/TradeCheck')->where(array('trade_id'=>$stockout_info['src_order_id']))->getField('shop_id');
					$update_fa_data = array(
						'logistics_id'=>$stockout_info['logistics_id'],
						'logistics_no'=>$stockout_info['logistics_no'],
						'shop_id'=>$shop_id,
						'warehouse_id'=>$stockout_info['warehouse_id'],
						'postage'=>-$stockout_info['post_cost'],
						'weight'=>-$stockout_info['weight'],
						'trade_count'=>-1,
					);
					$res_update_fa = D('Account/LogisticsFeeManagement')->add($update_fa_data,array(),array('trade_count'=>array('exp','trade_count+VALUES(trade_count)'),'postage'=>array('exp','postage+VALUES(postage)'),'weight'=>array('exp','weight+VALUES(weight)')));
				}
			}
			//更新出库日志

			$res_update_sales_log = D('Trade/SalesTradeLog')->where(array('trae_id'=>$stockout_info['src_order_id'],'type'=>105))->save(array('data'=>99));
			$res_mask = $this->query('select (~0) & (~4) & 0xFFFFFFFF as mask');

			$mask = $res_mask[0]['mask'];
			$res_update_stockout = $this->where(array('stockout_id'=>$stockout_id))->save(array('status'=>55,'consign_status'=>array('exp',"(consign_status&{$mask})"),'block_reason'=>0,'consign_time'=>'1000-01-01 00:00:00'));
			$res_update_salestrade = D('Trade/SalesTrade')->where(array('trade_id'=>$stockout_info['src_order_id']))->save(array('trade_status'=>55,'consign_status'=>array('exp',"(consign_status&{$mask})")));

			$res_update_sales_log = D('Trade/SalesTradeLog')->where(array('trae_id'=>$stockout_info['src_order_id'],'_string'=>"(`type`=103 OR (`type`=105 AND `data`=0))"))->save(array('data'=>99));
			$res_update_sales_log = D('Trade/SalesTradeLog')->add(array('trade_id'=>$stockout_info['src_order_id'],'operator_id'=>$operator_id,'type'=>168,'message'=>'强制撤销出库'));

		}catch(BusinessLogicException $e){
			SE($e->getMessage());
		}catch(\PDOException $e){
			\Think\Log::write($this->name.'-checkStockoutRevert-'.$e->getMessage());
			SE(self::PDO_ERROR);
		}catch(\Exception $e){
			\Think\Log::write($this->name.'-checkStockoutRevert-'.$e->getMessage());
			SE(self::PDO_ERROR);
		}
	}

	/*
	 * 销售出库导出
	 * 获取要导出的数据
	 * */
	public function exportToExcel($search,$id_list,$type = 'excel'){
		$creator=session('account');
		$stockout_block_reason =[
					'1'=>'请退款',
					'2'=>'已退款',
					'4'=>'地址被修改',
					'8'=>'发票被修改',
					'16'=>'物流被修改',
					'32'=>'仓库变化',
					'64'=>'备注修改',
					'128'=>'更换货品',
					'256'=>'取消退款',
					//'512'=>'放弃抢单',
					//'1024'=>'其他',
					//'2048'=>'拦截赠品',
		];
		$warehouse_type = ['1'=>'普通仓库','0'=>'不限'];
        $check_step = ['1'=>',未财审','2'=>',已财审'];
		$trade_type = [ "1"=> "网店销售","2"=>"线下零售", "3"=>"售后换货",];
		$salesstockout_status= ['55'=>'已审核','95'=>'已发货','100'=>'已签收','105'=>'部分打款','110'=>'已完成'];
		try{
			$num = workTimeExportNum($type);
//			if($id_list == ''){
//				D('Setting/EmployeeRights')->setSearchRights($search,'shop_id',1);
//				//$count=D('StockOutOrder')->getOutputCount($search);
//				//$rows = $num + 1;
//				//$data = D('StockOutOrder')->searchStockoutList(1, $rows, $search, 'id','desc', 'salesstockout');
//			}else{
//				$data = D('StockOutOrder')->exportStockoutSales($id_list);
//			}
			$data = D('StockOutOrder')->exportStockoutSales($id_list,$search,$type,$num);
			if(count($data)>$num){
				if($type == 'csv'){
					SE(self::EXPORT_CSV_ERROR);
				}
				SE(self::OVER_EXPORT_ERROR);
			}
			$row=array();
			$arr=array();
			foreach($data as $k=>$v){
				$row['src_order_no']=$v['src_order_no'];
				$row['stockout_no']=$v['stockout_no'];
				$row['src_tids']=$v['src_tids'];
				$row['warehouse_name']=$v['warehouse_name'];
				$row['warehouse_type']=$warehouse_type[$v['warehouse_type']];
				$row['shop_name']=$v['shop_name'];
				$row['trade_type']=$trade_type[$v['trade_type']];
				$row['trade_time']=$v['trade_time'];
				$row['pay_time']=$v['pay_time'];
				$row['status']=$salesstockout_status[$v['status']]."{$check_step[$v['check_step']]}";
				$row['consign_status']=$this->salesConsignStatus($v['consign_status']);
				$row['block_reason']=$stockout_block_reason[$v['block_reason']];
				$row['checker_name']=$v['checker_name'];
				$row['goods_count']=$v['goods_count'];
				$row['goods_type_count']=$v['goods_type_count'];
				$row['buyer_nick']=$v['buyer_nick'];
				$row['receiver_name']=$v['receiver_name'];
				$row['receiver_area']=$v['receiver_area'];
				$row['receiver_address']=$v['receiver_address'];
				$row['receiver_mobile']=$v['receiver_mobile'];
				$row['receiver_telno']=$v['receiver_telno'];
				$row['goods_abstract']=$v['goods_abstract'];
				$row['paid']=$v['paid'];
				$row['receiver_zip']=$v['receiver_zip'];
				$row['logistics_name']=$v['logistics_name'];
				$row['buyer_message']=$v['buyer_message'];
				$row['goods_total_cost']=$v['goods_total_cost'];
				$row['calc_post_cost']=$v['calc_post_cost'];
				$row['post_cost']=$v['post_cost'];
				$row['calc_weight']=$v['calc_weight'];
				$row['weight']=$v['weight'];
				$row['has_invoice']=$v['has_invoice']==1?'是':'否';
				$row['logistics_print_status']=$v['logistics_print_status']==1?'已打印':'未打印';
				$row['sendbill_print_status']=$v['sendbill_print_status']==1?'已打印':'未打印';
				$row['logistics_no']=$v['logistics_no'];
				$row['consign_time']=$v['consign_time'];
				$arr[]=$row;
			}
			unset($data);
			$title = '销售出库单';
			$filename = '销售出库单';
			$excel_header = D('Setting/UserData')->getExcelField('Stock/SalesStockOut','sales_stockout');
			$width_list = array(
					'18','18','22','18','10',
					'15','10','25','25','20',
					'20','20','10','10','10',
					'15','15','30','30','15',
					'15','25','15','10','25',
					'20','10','10','10','10','10',
					'10','10','10','20','25');
			if($type == 'csv') {
				ExcelTool::Arr2Csv($arr, $excel_header, $filename);
			}else {
				ExcelTool::Arr2Excel($arr, $title, $excel_header, $width_list, $filename, $creator);
			}
		}catch(BusinessLogicException $e){
			SE($e->getMessage());
		}catch(\Exception $e){
			\Think\Log::write($e->getMessage());
			SE(parent::PDO_ERROR);
		}
	}

	/*
	 * 物流状态渲染函数
	 * */
	function salesConsignStatus($value) {
		$consign_status= ['1'=>'已验货','2'=>'已称重','4'=>'已出库','8'=>'物流同步','1073741824'=>'原始单已完成'];
        $str = '';
		foreach($consign_status as $k=>$v){
			if((int)$value & (int)$k){
				$str.= $v.',';
			}
		}
		return  substr($str,0,-1);
    }

	//根据出库单ids获取到短信标签信息
	function getSMSData($ids){
		try{
			$return=array();
			$sql='select so.receiver_mobile,st.trade_time,so.consign_time,so.stockout_id,st.src_tids,so.receiver_name,so.logistics_no,cs.shop_name,gg.short_name,cl.logistics_name,st.buyer_nick,st.receiver_area,st.receiver_address '
			. 'FROM stockout_order so '
			. 'JOIN sales_trade st ON so.src_order_id = st.trade_id '
			. 'JOIN cfg_shop cs ON cs.shop_id = st.shop_id '
			. 'LEFT JOIN goods_spec gs ON gs.spec_no = so.single_spec_no and gs.deleted = 0 '
			. 'LEFT JOIN goods_goods gg ON gg.goods_id = gs.goods_id '
			. 'LEFT JOIN cfg_logistics cl ON cl.logistics_id = so.logistics_id '
			. 'LEFT JOIN goods_suite gst ON gst.suite_no = so.single_spec_no and gst.deleted = 0 '
			. 'LEFT JOIN stock_logistics_no sln ON sln.logistics_id = so.logistics_id and sln.logistics_no = so.logistics_no '
			. 'where so.stockout_id in( '.$ids.' ) group by so.stockout_id order by so.stockout_id';
			$data=D('StockOutOrder')->query($sql);
			foreach($data as $row){
				$return[$row['stockout_id']]=$row;
			}
			$sql_get_goods_name='select sod.stockout_id,sto.api_goods_name as goods_name from stockout_order_detail sod LEFT JOIN sales_trade_order sto ON sod.src_order_detail_id = sto.rec_id where sod.stockout_id in ( '.$ids.' );';
			$goods_name=D('StockoutOrderDetail')->query($sql_get_goods_name);
			for($i=0;$i<count($goods_name);$i++){
				$key=$goods_name[$i]['stockout_id'];
				$return[$key]['goods_name'].=$goods_name[$i]['goods_name'];
				if($i<count($goods_name)-1)$return[$key]['goods_name'].=',';
			}
			return $return;
		}catch (\PDOException $e) {
			$msg = $e->getMessage();
			\Think\Log::write($this->name."-getSMSData-".$msg);
			SE(self::PDO_ERROR);
		}
	}
	public function synchronousLogistics($id,&$error){
		try{
			$debug_log_str = '';
			$field = array('so.status','so.consign_status','so.stockout_no','so.src_order_no','st.trade_id','so.stockout_id','cl.logistics_type','so.logistics_no','so.logistics_id','so.checkouter_id','so.freeze_reason','so.block_reason','so.src_order_type','so.warehouse_type','als.sync_status','st.platform_id');
			$where  = array('so.stockout_id'=>$id);
			$res_so_info = D('Stock/StockOutOrder')->alias('so')->field($field)->join('left join cfg_logistics cl ON cl.logistics_id=so.logistics_id')->join('left join api_logistics_sync als ON als.stockout_id = so.stockout_id ')->join('left JOIN sales_trade st ON so.src_order_id = st.trade_id')->where($where)->find();
			$query_config_values = get_config_value(array('syncLogistics_examine_goods','syncLogistics_check_weight','order_logistics_sync_time'),array(0,0,2));
			$config_values = array(
	            'syncLogistics_examine_goods' =>$query_config_values['syncLogistics_examine_goods'],   //验货
	            'syncLogistics_check_weight' =>$query_config_values['syncLogistics_check_weight'],     //称重
	            'order_logistics_sync_time' =>$query_config_values['order_logistics_sync_time'],     //称重
	            'stockout_must_checkout' =>0,     //签出
	        );
			if(empty($res_so_info)){
				SE('未查询到出库单信息');
			}
			if((int)$res_so_info['src_order_type'] != 1){
				SE('不是销售订单');
			}
			if((int)$res_so_info['platform_id'] == 0){
				SE('线下订单不需同步物流');
			}
			if((int)$res_so_info['status'] != 55){
				SE('只能预同步已审核的订单');
			}
			if(!empty($res_so_info['sync_status'])){
				SE('已经预物流同步');
			}
			if ((int)$res_so_info['freeze_reason']<>0)
	        {
	            SE("出库单已冻结");
	        }
	        if ((int)$res_so_info['block_reason']<>0 && $is_force == 0)
	        {
				$block_reason = $this->getBlockReason($res_so_info['block_reason']);
				if((int)$res_so_info['block_reason'] & (1|2|4|32|128|256))
				{
					SE( "出库单[{$block_reason}]拦截出库,请驳回重新审核");
				}else if((int)$res_so_info['block_reason'] & 4096){
				    if($query_config_values['order_deliver_block_consign'] != 1 ||  ($query_config_values['order_deliver_block_consign'] == 1 && $query_config_values['prevent_online_block_consign_stockout'] == 0))
                    {
                        SE("出库单[{$block_reason}]拦截出库,请取消拦截再出库");
                    }
				}else{
                    SE("出库单[{$block_reason}]拦截出库,请取消拦截再出库");
                }
	        }
	        if((int)$res_so_info['warehouse_type']<>1)
	        {
	            SE("委外订单不能验货出库");
	        }
	        if($config_values['stockout_must_checkout'] && $res_so_info['checkouter_id']==0)
	        {
	            SE('出库单必须签出才可操作');
	        }elseif (!empty($res_so_info['checkouter_id'])  && (int)$res_so_info['checkouter_id'] <> (int)$operator_id)
	        {
	            SE('出库单已被其他人签出');
	        }
	        if((int)$res_so_info['platform_id'] !=0)
	        {
	            if (empty($res_so_info['logistics_id']) || (int)$res_so_info['logistics_id']==0)
	            {
	                SE('物流公司未设置');
	            }
	            $res_so_info['logistics_no'] = trim($res_so_info['logistics_no']);
	            if ( (int)$res_so_info['logistics_type'] > 1 &&  empty($res_so_info['logistics_no']))
	            {
	                SE('物流单号不能为空');
	            }
	        }
			if(!((int)$res_so_info['consign_status'] & 1) && $config_values['syncLogistics_examine_goods'])
	        {
	            SE('物流同步前必须验货');
	        }
	        if(!((int)$res_so_info['consign_status'] & 2) && $config_values['syncLogistics_check_weight'])
	        {
	            SE('物流同步前必须称重');
	        }
			$where['st.split_from_trade_id'] = array('neq',0);
			$is_split_trade = D('Trade/Trade')->fetchSql(false)->alias('st')->field('st.trade_no')->join('stockout_order so ON so.src_order_id=st.trade_id')->where($where)->find();
			if(!empty($is_split_trade)&&$config_values['order_logistics_sync_time']!=1){
				SE('在当前配置(物流同步时间):"全部子订单发货才可发货"下，不能对拆分单进行预同步');
			}
			$debug_log_str = "预同步";
			try{
				$this->startTrans();
				if($res_so_info['logistics_type']!=1){
					\Think\Log::write("预同步-call-I_SALES_CREATE_LOGISTICS_SYNC: stockout_id:{$id}",\Think\Log::INFO);
					$debug_log_str = "调用存储过程";
					$sql_logistics_sync = "CALL I_SALES_CREATE_LOGISTICS_SYNC({$res_so_info['trade_id']},{$id},0)";
					$res_logistics_sync = $this->execute($sql_logistics_sync);
					$debug_log_str = "修改物流状态";
					$this->execute('update stockout_order set consign_status = consign_status+8 where stockout_id = '.$id);
					$debug_log_str = "修改出库单状态";
					$this->update_als_status('so.stockout_id = '.$id,1);
				}else{
					$this->execute('update stockout_order so set so.consign_status = (consign_status | 32) where so.stockout_id = '.$id);
				}
				$this->commit();
				return true;
			}catch(\Exception $e){
				$msg = $e->getMessage();
				\Think\Log::write($this->name."--".$debug_log_str."-tongbu-".$msg);
				$this->rollback();
				SE(self::PDO_ERROR);
			}
		}catch(\PDOException $e){
			$msg = $e->getMessage();
			\Think\Log::write($this->name."--".$debug_log_str."-tongbu-".$msg);
			SE(self::PDO_ERROR);
		}catch(BusinessLogicException $e){
			$msg = $e->getMessage();
			$error[] = array(
				'stock_id' => $id,
				'stock_no' => $res_so_info['stockout_no'],
				'order_no' => $res_so_info['src_order_no'],
				'msg' =>$msg,
			);
			return false;
		}catch(\Exception $e){
			$msg = $e->getMessage();
			\Think\Log::write($this->name."--".$debug_log_str."-tongbu-".$msg);
			$error[] = array(
				'stock_id' => $id,
				'stock_no' => $res_so_info['stockout_no'],
				'msg' =>$msg,
			);
			return false;
		}
	}
	
	public function update_als_status($where,$type = 0){
		try{
			$result = D('Stock/StockOutOrder')->alias('so')->field('IF(so.status <=55 and als.sync_status is NULL,16,IF(als.sync_status is NULL,32,IF(sum(IF(als.sync_status<0,1,0))>0,64,IF(sum(als.sync_status)=3*count(1),128,IF(sum(als.sync_status)=0,256,IF(sum(als.sync_status)=2*count(1),512,1024)))))) as update_als_status,als.stockout_id,als.sync_status,so.status')->join('LEFT JOIN api_logistics_sync als ON als.stockout_id = so.stockout_id')->where($where)->group('so.stockout_id')->select();
			if(!empty($result)){
				if($type == 0){	
					$this->execute('update stockout_order so LEFT JOIN api_logistics_sync als ON als.stockout_id = so.stockout_id set so.consign_status = if(so.consign_status & '.$result[0]['update_als_status'].',so.consign_status,so.consign_status +'.$result[0]['update_als_status'].') where '.$where);
				}else{
					$this->execute('update stockout_order so set so.consign_status = if(so.consign_status & '.$result[0]['update_als_status'].',so.consign_status,so.consign_status +'.$result[0]['update_als_status'].') where '.$where);
				}
				if($result[0]['sync_status']>=3 && ($result[0]['status']>=95||$result[0]['status']==55)){
					$this->execute("update stock_logistics_no sln LEFT JOIN stockout_order so ON sln.logistics_id = so.logistics_id AND sln.logistics_no = so.logistics_no set sln.status = 7 where so.stockout_id=".$result[0]['stockout_id']." AND sln.status = 1");
				}
			}
		}catch(Exception $e){
			$msg = $e->getMessage();
			\Think\Log::write($this->name."---update_als_status-".$msg);
			SE(self::PDO_ERROR);
		}
		
	}

	public function deleteLogisticsTrace($trade_id)
	{
		$cfg_open_logistics_trace = get_config_value('cfg_open_logistics_trace',0);
		if($cfg_open_logistics_trace)
		{
			$this->execute("DELETE FROM sales_logistics_trace WHERE trade_id = {$trade_id} AND logistics_status = 0;");
		}

	}

	public function updateAccountCheckAmount($trade_id)
	{
		$account_check_no = $this->query("select FN_SYS_NO('account_check') account_check_no");
		$account_check_no = $account_check_no[0]['account_check_no'];
		$alipay_account_check_sql = "INSERT INTO fa_alipay_account_check(account_check_no,tid,send_amount,shop_id,platform_id,created,consign_time)
		(
			SELECT '{$account_check_no}',at.tid,-SUM(sto.share_amount)-SUM(sto.share_post),at.shop_id,at.platform_id,NOW(),NOW()
			FROM sales_trade_order sto
			LEFT JOIN api_trade `at` ON sto.src_tid = at.tid AND sto.platform_id = at.platform_id
			WHERE sto.trade_id=$trade_id AND sto.platform_id>0 AND sto.refund_status<>5 GROUP BY at.tid
		)
		ON DUPLICATE KEY UPDATE
		send_amount = send_amount+VALUES(send_amount)";
		$this->execute($alipay_account_check_sql);
		$check_detail_month_sql = "INSERT INTO fa_platform_check_detail_month(tid,platform_id,shop_id,check_month,send_amount,diff_amount,created)
		(
			SELECT at.tid,at.platform_id,at.shop_id,DATE_FORMAT(NOW(),'%Y-%m'),-SUM(sto.share_amount)-SUM(sto.share_post),-SUM(sto.share_amount)-SUM(sto.share_post),NOW()
			FROM sales_trade_order sto
			LEFT JOIN api_trade `at` ON sto.src_tid = at.tid AND sto.platform_id = at.platform_id
			WHERE sto.trade_id=$trade_id AND sto.platform_id>0 AND sto.refund_status<>5 GROUP BY at.tid
		)
		ON DUPLICATE KEY UPDATE
		send_amount=send_amount+VALUES(send_amount),
		diff_amount=diff_amount+VALUES(diff_amount)";
		$this->execute($check_detail_month_sql);
	}
}