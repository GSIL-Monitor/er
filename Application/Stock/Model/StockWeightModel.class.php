<?php
namespace Stock\Model;
use Think\Exception\BusinessLogicException;
use Think\Model;

/**
 * @package Stock\Model
 */
class StockWeightModel extends Model{
    protected $tableName = 'stockout_order';
    protected $pk        = 'stockout_id';
	public function getWeightInfoByTradeNo($trade_no){
	    try {
			$weight_info_field=array(
					'so.stockout_id',
					'so.src_order_type',
					'so.src_order_id',
					'so.freeze_reason',
					'so.consign_status',
					'so.warehouse_id',
					'so.logistics_id',
					'so.logistics_no',
					'so.block_reason',
					'st.trade_no',
					'so.status',
					'cl.logistics_name',
					'so.logistics_no',
					'so.receiver_name',
					'so.receiver_district',
					'so.receiver_address',
					'so.calc_weight',
			);
			$weight_info_where = array('so.status'=>array('EGT',55),'st.trade_no'=>$trade_no);
	        $res = $this->alias('so')->join("LEFT JOIN sales_trade st ON st.stockout_no = so.stockout_no")->join('LEFT JOIN  cfg_logistics cl on cl.logistics_id = st.logistics_id')->join('left join stockout_order_detail sod ON sod.stockout_id = so.stockout_id')->where($weight_info_where)->field($weight_info_field)->select();
			return $res;
	    } catch (\PDOException $e) {
	        $msg = $e->getMessage();
	        \Think\Log::write($msg);
	        SE(self::PDO_ERROR);
	    }

	}
	public function getWeightInfoByLogisticsNo($logistics_no){
	    try {
			$weight_info_field=array(
					'COUNT(1) AS trade_num',
					'so.src_order_type',
					'so.src_order_id',
					'so.freeze_reason',
					'so.consign_status',
					'so.warehouse_id',
					'so.logistics_id',
					'so.logistics_no',
					'so.block_reason',
					'so.status',
					'so.stockout_id',
					'st.trade_no',
					'so.stockout_no',
					'cl.logistics_name',
					'so.logistics_no',
					'so.receiver_name',
					'so.receiver_district',
					'so.receiver_address',
					'so.calc_weight',
			);
			$weight_info_where = array('so.status'=>array('EGT',55),'st.logistics_no'=>$logistics_no);
	        $res = $this->alias('so')->join("LEFT JOIN sales_trade st ON st.stockout_no = so.stockout_no")->join('LEFT JOIN  cfg_logistics cl on cl.logistics_id = st.logistics_id')->join('left join stockout_order_detail sod ON sod.stockout_id = so.stockout_id')->where($weight_info_where)->field($weight_info_field)->group('so.stockout_id')->select();
			return $res;
	    } catch (\PDOException $e) {
	        $msg = $e->getMessage();
	        \Think\Log::write($msg);
	        SE(self::PDO_ERROR);
	    }
	}

	/**
	 * 确认称重
	 *
	 * @param $weight_info
	 */
	public function consignStockWeight($weight_info,&$error_list)
	{
		try{
			$this->startTrans();
			if(empty($weight_info['weight'])){
				$weight_info['weight'] = 0;
			}
			$operator_id 		= get_operator_id();
			$stockout_fields 	= array('stockout_id','stockout_no','status','src_order_type','src_order_id','freeze_reason','consign_status','warehouse_id','logistics_id','logistics_no','block_reason','is_allocated','warehouse_type','packager_id');
			$stockout_conditons = array('stockout_id'=>$weight_info['stockout_id'],'status'=>array('neq',5));
			$stockout_info 		= D('Stock/StockOutOrder')->field($stockout_fields)->where($stockout_conditons)->lock(true)->select();

			if(empty($stockout_info)){
				SE('销售出库单不存在!');
			}
			$stockout_info = $stockout_info[0];

			$error_list = array();
			if($stockout_info['src_order_type'] != 1){
				$error_list[] = array('stock_id'=>$stockout_info['stockout_id'],'stock_no'=>$stockout_info['stockout_no'],'msg'=>'该出库单不是销售出库单');
			}
			if($stockout_info['status'] < 55 ){
				$error_list[] = array('stock_id'=>$stockout_info['stockout_id'],'stock_no'=>$stockout_info['stockout_no'],'msg'=>'出库单状态不正确');
			}
			if(intval($stockout_info['consign_status'])&2){
				$error_list[] = array('stock_id'=>$stockout_info['stockout_id'],'stock_no'=>$stockout_info['stockout_no'],'msg'=>'订单已称重');
			}
			if(intval($stockout_info['freeze_reason']) != 0){
				$error_list[] = array('stock_id'=>$stockout_info['stockout_id'],'stock_no'=>$stockout_info['stockout_no'],'msg'=>'操作失败:出库单被冻结');
			}
			if(intval($stockout_info['block_reason']) != 0){
				$reason =  D('SalesStockOut')->getBlockReason($stockout_info['block_reason']);
				$error_list[] = array('stock_id'=>$stockout_info['stockout_id'],'stock_no'=>$stockout_info['stockout_no'],'msg'=>'出库单已经截停:'.$reason);
			}

			if(!empty($error_list)){
				$this->rollback();
				return;
			}
			//----更出库单状态
			$update_data = array(
				'consign_status'=>array('exp','(consign_status|2)'),
				'weight'		=>$weight_info['weight'],
			);
			$stock_update_where = array('stockout_id'=>$stockout_info['stockout_id']);
			$res_stock_update = D('Stock/StockOutOrder')->where($stock_update_where)->save($update_data);
			//----更新订单状态
			$update_data = array(
					'consign_status'=>array('exp','(consign_status|2)'),
			);
			$update_sales_where = array('trade_id'=>$stockout_info['src_order_id']);
			$res_sales_update = D('Trade/SalesTrade')->where($update_sales_where)->save($update_data);

			//----更新出入库日志
			$inout_data = array(
				'order_type'	=> 2,
				'order_id'		=> $stockout_info['stockout_id'],
				'operator_id'	=> $operator_id,
				'operate_type'	=> 111,
				'message'		=> '称重重量:'.$weight_info['weight']
			);
			$res_inout = D('Stock/StockInoutLog')->insertStockInoutLog($inout_data);
			//----更新订单日志
			$sales_log_data = array('data'=>99);
			$sales_log_where = array('trade_id'=>$stockout_info['src_order_id'],'type'=>102);
			$res_sales_log = D('Trade/SalesTradeLog')->updateTradeLog($sales_log_data,$sales_log_where);
			$sales_log_data = array(
				'trade_id'		=> $stockout_info['src_order_id'],
				'operator_id'	=> $operator_id,
				'type'			=> 102,
				'message'		=> '称重重量:'.$weight_info['weight']
			) ;
			$res_log_insert = D('Trade/SalesTradeLog')->addTradeLog($sales_log_data);
			//-----订单全链路
			if(intval($stockout_info['status']) < 95){
				D('Trade/TradeCheck')->traceTrade($operator_id,$stockout_info['src_order_id'],12,'','');
			}
			$this->commit();
		}catch (\PDOException $e){
			$this->rollback();
			\Think\Log::write($e->getMessage());
			SE(self::PDO_ERROR);
		}catch (BusinessLogicException $e){
			$this->rollback();
			SE($e->getMessage());
		}catch (\Exception $e){
			$this->rollback();
			\Think\Log::write($e->getMessage());
			SE(self::PDO_ERROR);
		}
	}
	
}
