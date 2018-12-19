<?php
namespace Stock\Model;

use Think\Model;
use Common\Common\UtilDB;

/**
 * Created by PhpStorm.
 * User: gaosong
 * Date: 11/19/15
 * Time: 14:01
 */
class StockInModel extends Model
{
    protected $tableName = 'stockin_order_detail';

    public function saveOrder($rows, $search, $type)
    {
        $result['info'] = "";
        $result['status'] = 0;

        if ($type == "add") {
            $this->addStockInOrder($result, $search, $rows);
        } else {
            $this->updataStockInOrder($result, $search, $rows);
        }
        return $result;
    }

    public function deleteStockInOrder($id)
    {
        $data['status'] = 0;
        $data['info'] = "取消成功！";
        $operator_id = get_operator_id();
        try {
            $field = array("status", "src_order_type", "src_order_no");
            $result = D('StockInOrder')->getStockinOrder($field,array('stockin_id'=>$id));//->where("stockin_id = " . $id)->field($field)->find();
        } catch (\PDOException $e) {
            $msg = $e->getMessage();
            \Think\Log::write($msg);
            $data['status'] = 1;
            $data['info'] = self::PDO_ERROR;
            return $data;
        }
        $status = $result['status'];
        $src_order_type = $result['src_order_type'];
        if ($status != "20") {
            $data['status'] = 1;
            $data['info'] = "订单状态不正确!";
            return $data;
        }

        M()->startTrans();
        try {
            //更新入库单状态
            $updata = array('status' => '10');
            $update_stockin_order = D('StockInOrder')->where("stockin_id = " . $id)->setField($updata);
            //更新入库日志
            $log_data = array(
                "order_type" => 1,
                "order_id" => $id,
                "operator_id" => $operator_id,
                "operate_type" => "51",
                "message" => "取消入库单"
            );
            D('StockInoutLog')->add($log_data);
            //
           /*  if ($src_order_type == "3") {
                $refund_no = $result['src_order_no'];
                $refund_id = M('sales_refund')->where("refund_no= \"" . $refund_no . "\"")->field("refund_id")->find();
                $refund_id = $refund_id['refund_id'];

                $log_data = array(
                    "refund_id"     => $refund_id,
                    "type"          => 9,
                    "operator_id"   => $operator_id,
                    "remark"        => "取消入库单"
                );
                M('sales_refund_log')->add($log_data);
                $tmp_data["status"] = 10;
                M('sales_refund')->where("refund_id= " . $refund_id)->save($tmp_data);
            } */
        } catch (\PDOException $e) {
            $msg = $e->getMessage();
            \Think\Log::write($msg);
            M()->rollback();
            $data['status'] = 1;
            $data['info'] = \Think\Model::PDO_ERROR;
            return $data;
        }
        M()->commit();
        return $data;
    }

    public function submitStockInOrder($search,$handle_type='other')
    {
        $operator_id = get_operator_id();
        if($handle_type == 'other'){
            $this->startTrans();
        }
        $stockin_order_info = D('Stock/StockInOrder')->getStockinOrderList(array('stockin_id','stockin_no','src_order_id','src_order_no','warehouse_id'),array('stockin_id'=>$search['id']));
        $stockin_order_info = $stockin_order_info[0];

        try {
            $now_stockin_id = $search['id'];

            switch ((string)$search['src_order_type'] ) {
                    case '1':{//采购入库
                        $query_purchase_no_field = array(
                            'src_order_no',
                            'logistics_id' ,
                            'post_fee',
                            'other_fee'
                        );
                        $query_purchase_no_conditions = array(
                            'stockin_id'=>$now_stockin_id
                        );
                        $purchase_no_res = D('Stock/StockInOrder')->getStockinOrder($query_purchase_no_field,$query_purchase_no_conditions);
                        
                        $purchase_no = $purchase_no_res['src_order_no'];
                        
                        //获取默认的入库仓库id
                        $warehouse_info = UtilDB::getCfgList(array('warehouse'),array('warehouse'=>array('warehouse_id'=>$search['warehouse_id'])));
                        if(empty($warehouse_info['warehouse'])){
                            E('入库仓库不存在');
                        }
                        $warehouse_id = $search['warehouse_id'];
                        
                        //获取物流类型
                        $query_logistics_info_fields = array(
                            'logistics_type'
                        );
                        $logistics_info_res = D('Setting/Logistics')->get($query_logistics_info_fields,$purchase_no_res['logistics_id']);
                        if($purchase_no_res['logistics_id']!=0 && !empty($purchase_no_res['logistics_id'])){
                            if(!empty($logistics_info_res)){
                                $logistics_type = $logistics_info_res[0]['logistics_type'];
                            }else{
                                E('未查询到物流信息!');
                            }
                        }else{
                            $logistics_type = '';
                        }
                        
                        
                        $insert_purchase_order_data = array(
                            'purchase_no'       =>	$purchase_no,
                            'creator_id'        =>	$operator_id,
                            'purchaser_id'      =>  0,
                            'status'            =>	'90',
                            'provider_id'       =>	0,
                            'warehouse_id'      =>  $warehouse_id,
                            'logistics_type'    =>	$logistics_type,
                            'expect_arrive_time'=>	array('exp','NOW()'),
                            'other_fee'         =>	$purchase_no_res['other_fee'],
                            'post_fee'          =>	$purchase_no_res['post_fee'],
                            'remark'            =>	'',
                            'flag_id'           =>	0,
                            'created'           =>	array('exp','NOW()')
                        );
                        $insert_purchase_order_res = D('Purchase/PurchaseOrder')->insertPurchaseOrderForUpdate($insert_purchase_order_data);

                        $purchase_id = $insert_purchase_order_res;
                        //插入采购日志
                        
                        $insert_purchase_order_log_data = array(
                            'purchase_id'=>$purchase_id,
                            'operator_id'=>$operator_id,
                            'type'       =>0,
                            'remark'     =>  '快速采购入库创建采购单---'.$purchase_no
                        );
                        $insert_purchase_order_log_res = D('Purchase/PurchaseOrderLog')->insertPurchaseOrderLogForUpdate($insert_purchase_order_log_data);
                        $query_stockin_order_detail_field = array(
                            "{$purchase_id} as purchase_id",
                            'spec_id',
                            "{$warehouse_id} as warehouse_id",
                            '0 AS tag',
                            'SUM(num) as num',
                            'SUM(num) AS arrive_num',
                            'base_unit_id',
                            'unit_id',
                            '1 AS unit_ratio',
                            'src_price AS price',
                            'IF(src_price=0,0,src_price*num) AS amount',
                            'IF(src_price=0,1,cost_price/src_price) as discount',
                            'tax',
                            'tax_price',
                            'tax_amount',
                            'CONCAT_WS(remark,"由入库明细引入采购明细") AS remark',
                            'NOW() AS created'
                        );
                        $query_stockin_order_detail_conditions = array(
                            'stockin_id'=>$now_stockin_id
                        );
                        $query_stockin_order_detail_res = D('Stock/StockinOrderDetail')->getStockinOrderDetailList($query_stockin_order_detail_field,$query_stockin_order_detail_conditions,'rec_id');
                        $insert_purchase_detail_res = D('Purchase/PurchaseOrderDetail')->insertPurchaseOrderDetailForUpdate($query_stockin_order_detail_res);
                        //更新采购单信息 getPurchaseOrderDetailList
                        $query_purchase_order_detail_fields = array(
                            'COUNT(spec_id) as goods_type_count',
                            'SUM(num) as goods_count',
                            'SUM(arrive_num) as goods_arrive_count',
                            'SUM(tax_amount) as tax_fee',
                            'SUM(price*num) as goods_fee'
                        );
                        $query_purchase_order_detail_conditons = array(
                            'purchase_id'=>$purchase_id
                        );
                        $query_purchase_order_detail_res = D('Purchase/PurchaseOrderDetail')->getPurchaseOrderDetailList($query_purchase_order_detail_fields,$query_purchase_order_detail_conditons);
                        $update_purchase_order_conditions = array(
                            'purchase_id'  =>$purchase_id
                        );
                        $update_purchase_order_res = D('Purchase/PurchaseOrder')->updatePurchaseOrder($query_purchase_order_detail_res[0],$update_purchase_order_conditions);
                       
                        $update_stockin_order_detail_res = M()->execute("UPDATE stockin_order_detail sod,purchase_order_detail pod SET sod.src_order_detail_id = pod.rec_id WHERE sod.stockin_id = {$now_stockin_id} AND sod.spec_id = pod.spec_id AND pod.purchase_id = {$purchase_id};");
                       
                        $query_purchase_order_log_info_fields = array(
                            "{$purchase_id} as purchase_id",
                            "{$operator_id} as operator_id",
                            '5 as type',
                            "CONCAT('添加单品----商家编码----',spec_no) as remark"  
                        );
                        $query_purchase_order_log_info_conditions = array(
                            'pod.purchase_id'=>$purchase_id,
                        );
                        $query_purchase_order_log_info_res = D('Purchase/PurchaseOrderDetail')->getPurchaseOrderDetailLogInfoLeftGoodsSpec($query_purchase_order_log_info_fields,$query_purchase_order_log_info_conditions);
                        $insert_purchase_order_detail_log_res = D('Purchase/PurchaseOrderLog')->insertPurchaseOrderLogForUpdate($query_purchase_order_log_info_res);
                        
                        break;
                    }
                    case '2'://调拨
                    {
                        $transfer_order_info = D('Stock/StockTransfer')->getStockTransOrder(array('from_warehouse_id','mode'),array('rec_id'=>$stockin_order_info['src_order_id']));
                        $from_warehouse_id = $transfer_order_info[0]['from_warehouse_id'];
                        //----更新调拨单详情中   注:货位没有设置,统一设置为0
                        $trans_stockin_detail_info = D('Stock/StockinOrderDetail')->getStockinOrderDetailList(array('src_order_detail_id as rec_id',"{$stockin_order_info['src_order_id']} as transfer_id",'num','num as in_num','0 as from_position' ),array('stockin_id'=>$stockin_order_info['stockin_id']));
                        $update_transfer_detail_data = $trans_stockin_detail_info;
                        $update_transfer_detail_dup = array('in_num'=>array('exp','in_num+VALUES(in_num)'));
                        $update_transfer_detail_res = D('Stock/StockTransferDetail')->insert($update_transfer_detail_data,$update_transfer_detail_dup);
                        //----更新带调拨量:没做
                        //----更新调拨单总入库数量
                        $tansfer_goods_count = D('Stock/StockTransferDetail')->getDetailInfo(array('SUM(in_num) as goods_in_count'),array('transfer_id'=>$stockin_order_info['src_order_id']));
                        $update_transfer = D('Stock/StockTransfer')->where(array('rec_id'=>$stockin_order_info['src_order_id']))->save($tansfer_goods_count[0]);
                        $transfer_detail_info = D('Stock/StockTransferDetail')->field('from_position,to_position,spec_id')->where(array('transfer_id'=>$stockin_order_info['src_order_id']))->select();
                        if($transfer_order_info[0]['mode'] ==1){
                            foreach($transfer_detail_info as $transfer_detail){
                                D('Stock/StockSpecPosition')->where(array('position_id'=>$transfer_detail['from_position'],'spec_id'=>$transfer_detail['spec_id'],'warehouse_id'=>$from_warehouse_id))->delete();
                            }
                        }
                        break;
                    }
                    case '3'://退款入库
                    {
        //                 $res_refund_status_now = D('Trade/SalesRefund')->getSalesRefund(array('process_status'),array('refund_id'=>array('eq',$sales_refund_id)));
                        
                        $sales_refund_field = "type,swap_trade_id,refund_id,process_status";//is_trade_charged
                        $res_sales_refund_info = D("SalesRefund")->where("refund_no = '{$search['src_order_no']}'")->field($sales_refund_field)->find();
                        
                        if($res_sales_refund_info['process_status']<>60&&$res_sales_refund_info['process_status']<>65&&$res_sales_refund_info['process_status']<>70&&$res_sales_refund_info['process_status']<>69){
                            E('入库单对应的退货单状态错误');
                        }
                        $sales_refund_id = $res_sales_refund_info['refund_id'];
                        $sales_refund_status = $res_sales_refund_info['process_status'];
        
                        //更新入库单详情的相关信息  如：金额
                        //logic 更新退换详单 入库单详单信息：入库数量 入库总价
                        $this->execute("UPDATE sales_refund_order sro,stockin_order_detail sod SET sro.stockin_num=sro.stockin_num+sod.num,sro.stockin_amount = sro.stockin_amount + sod.num *sod.cost_price WHERE sod.src_order_detail_id = sro.refund_order_id AND sod.stockin_id=%d", $now_stockin_id);
        
                        //更新退货入库单的状态
                        $res_sales_refund_status = $this->query("SELECT SUM(IF(refund_num-stockin_num<0,0,refund_num-stockin_num)) count,trade_id  FROM sales_refund_order WHERE refund_id=%d", $sales_refund_id);
                        $count = (int)$res_sales_refund_status[0]['count'];
                        //logic 根据$count判断是部分入库还是全部入库
                        //notes
                        if ($count > 0) {
                            if ($sales_refund_status == 69 || $sales_refund_status == 71) {
                                $this->execute("UPDATE sales_refund SET process_status=71 WHERE refund_no = '%s'", $search['src_order_no']);
                            } else {
                                $this->execute("UPDATE sales_refund SET process_status=70 WHERE refund_no = '%s'", $search['src_order_no']);
                            }
                        } else {
                            if ($sales_refund_status == 69 || $sales_refund_status == 71) {
                                $this->execute("UPDATE sales_refund SET process_status=90 WHERE refund_no = '%s'", $search['src_order_no']);
                            } else {
                                $this->execute("UPDATE sales_refund SET process_status=90 WHERE refund_no = '%s'", $search['src_order_no']);
                            }
                        }
                        //更新销售入库单记录
                        //logic 更新退还换日志
                        //notes 退还状态需要修改
                        //`type`  '操作类型: 1创建退款单2同意3拒绝4取消退款 5平台同意6平台取消7平台拒绝8停止等待9驳回',
                        if($count > 0){
                            $remark = '退换入库部分到货';
                        }else{
                            $remark = '退换入库全部到货';
                        }
                        $sales_refund_data = array("refund_id" => $sales_refund_id, "type" => 10, "operator_id" => $operator_id, "remark" => $remark);
                        D("SalesRefundLog")->add($sales_refund_data);
                        break;
                }
                case '4':
                {
                    $this->execute("UPDATE stock_spec ss, stockin_order_detail sod 
					SET ss.last_pd_time=NOW()
					WHERE ss.spec_id=sod.spec_id 
					AND ss.warehouse_id={$stockin_order_info['warehouse_id']} AND sod.src_order_type=4 AND sod.stockin_id={$stockin_order_info['stockin_id']};");

                    $pd_sid_info = D('Stock/StockinOrderDetail')->alias('sod')->field("IF(ssp.position_id IS NULL,-{$stockin_order_info['warehouse_id']},ssp.position_id) as position_id,sod.spec_id,NOW() as last_pd_time,{$stockin_order_info['warehouse_id']} as warehouse_id")->join('left join stock_spec_position ssp on ssp.spec_id= sod.spec_id and ssp.warehouse_id='.$stockin_order_info['warehouse_id'])->where(array('sod.stockin_id'=>$stockin_order_info['stockin_id']))->select();
                    $for_update_ssp = array('last_pd_time'=>array('exp','VALUES(last_pd_time)'));
                    D('Stock/StockSpecPosition')->addAll($pd_sid_info,array(),$for_update_ssp);
                    $update_status = array(
                        'status'=>80
                    );

                    $this->where(array('stockin_id'=>$stockin_order_info['stockin_id']))->save($update_status);
                    break;
                }
            }

            $type_map = C('stockin_type');
            $stock_message = $type_map["{$search['src_order_type']}"].'-'.$stockin_order_info['stockin_no'];
            //先根据入库单id找到stockin_order_detail中的$new_num和$new_price
            //再从stock_spec中根据spec_id查出$old_num和$old_price
            //最后调用costPriceCalc得出最后的num和cost_price插入到stock_spec中

            $tmp_arr = M('stockin_order_detail')->alias('sod')->where(array('sod.stockin_id'=>$now_stockin_id,))->field(array('sod.remark','sod.src_order_detail_id','sod.position_id','sod.num','cwz.zone_id','sod.cost_price','sod.spec_id',"{$stockin_order_info['warehouse_id']} as warehouse_id"))->join('left join cfg_warehouse_position cwp on cwp.rec_id = sod.position_id')->join('left join cfg_warehouse_zone cwz on cwz.zone_id = cwp.zone_id')->select();
            $tmp =M('stockin_order')->where(array('stockin_id'=>$now_stockin_id,))->field(array('warehouse_id'))->find();
            $stockin_change_history = array();
			for($i=0;$i<count($tmp_arr);$i++)
            {
               
                //查询当前库存量
                $stock_spec_info = D('Stock/StockSpec')->field(array('rec_id', 'stock_num', 'neg_stockout_num', 'cost_price', 'stock_diff', 'default_position_id'))->where(array('warehouse_id'=>$stockin_order_info['warehouse_id'],'spec_id'=>$tmp_arr[$i]['spec_id']))->find();
                if(!empty($stock_spec_info)){
                    $new_arr = $this->costPriceCalc($stock_spec_info['stock_num'],$stock_spec_info['cost_price'],$tmp_arr[$i]['num'],$tmp_arr[$i]['cost_price']);
                    M('stock_spec')->where(array('spec_id'=>$tmp_arr[$i]['spec_id'],'warehouse_id'=>$tmp_arr[$i]['warehouse_id']))->save(array('stock_num'=>$new_arr['stock_num'],'cost_price'=>$new_arr['cost_price'],'last_position_id'=>$tmp_arr[$i]['position_id']));
                }else{
                    $new_arr = array('stock_num' =>$tmp_arr[$i]['num']);
                    $stock_spec_id = M('stock_spec')->add(array('warehouse_id'=>$tmp_arr[$i]['warehouse_id'],'spec_id'=>$tmp_arr[$i]['spec_id'],'status'=>1,'stock_num'=>$tmp_arr[$i]['num'],'cost_price'=>$tmp_arr[$i]['cost_price'],'last_position_id'=>$tmp_arr[$i]['position_id']));
                }
                D('Stock/StockSpecLog')->add(array('operator_type'=>2,'operator_id'=>$operator_id,'stock_spec_id'=>empty($stock_spec_info['rec_id'])?$stock_spec_id:$stock_spec_info['rec_id'],'message'=>$stock_message,'stock_num'=>empty($stock_spec_info['stock_num'])?0:$stock_spec_info['stock_num'],'num'=>$tmp_arr[$i]['num']));

                //更新stock_spec_position信息
               /*INSERT INTO stock_spec_position(warehouse_id,spec_id,position_id,zone_id,stock_num,last_inout_time,created)
				VALUES(V_WarehouseId,V_SpecId,V_PositionId,V_ZoneId,V_Num,NOW(),NOW())
				ON DUPLICATE KEY UPDATE rec_id=LAST_INSERT_ID(rec_id),stock_num=stock_num+VALUES(stock_num),last_inout_time=NOW();
				SELECT LAST_INSERT_ID() INTO V_StockPositonId;*/
                $position_info = D('Stock/StockSpecPosition')->field('position_id')->where(array('warehouse_id'=>$tmp_arr[$i]['warehouse_id'],'spec_id'=>$tmp_arr[$i]['spec_id']))->find();
				if(!empty($position_info) && !empty($position_info['position_id'])){
					$stock_position_id = $position_info['position_id'];
				}else{
					$stock_position_id = $tmp_arr[$i]['position_id'];
				}
				$ssp_data = array(
                    'warehouse_id'=>$tmp_arr[$i]['warehouse_id'],
                    'spec_id'=>$tmp_arr[$i]['spec_id'],
                    'position_id'=>$stock_position_id,
                    'last_position_id'=>$stock_position_id,
                    'zone_id'=>empty($tmp_arr[$i]['zone_id'])?0:$tmp_arr[$i]['zone_id'],
                    'stock_num'=>$new_arr['stock_num'],
                    'last_inout_time'=>array('exp','NOW()'),
                    'created'=>array('exp','NOW()')
                );
                $ssp_update_data = array(
                    'stock_num'=>array('exp','VALUES(stock_num)'),
                    'last_inout_time'=>array('exp','NOW()'),
                );
                $res_ssp = D('Stock/StockSpecPosition')->add($ssp_data,'',$ssp_update_data);
                $res_ss_u = D('Stock/StockSpec')->add($ssp_data,'',array('last_position_id'=>array('exp','VALUES(last_position_id)')));

				$stockin_change_history[] = array(
					'src_order_type' => $search['src_order_type'],
					'stockio_id'   => $search['id'],
					'stockio_no'   =>$stockin_order_info['stockin_no'],
					'src_order_id' => empty($stockin_order_info['src_order_id'])?0:$stockin_order_info['src_order_id'],
					'src_order_no' =>empty($search['src_order_no'])?"":$search['src_order_no'],
					'stockio_detail_id' =>empty($tmp_arr[$i]['src_order_detail_id'])?0:$tmp_arr[$i]['src_order_detail_id'],
					'spec_id'  =>$tmp_arr[$i]['spec_id'],
					'warehouse_id' =>$tmp_arr[$i]['warehouse_id'],
					'type' =>1,
					'cost_price_old' =>empty($stock_spec_info['cost_price'])?0:$stock_spec_info['cost_price'],
					'stock_num_old' =>empty($stock_spec_info['stock_num'])?0:$stock_spec_info['stock_num'],
					'price' =>$tmp_arr[$i]['cost_price'],
					'num' =>$tmp_arr[$i]['num'],
					'amount' =>$tmp_arr[$i]['cost_price']*$tmp_arr[$i]['num'],
					'cost_price_new' =>empty($new_arr['cost_price'])?0:$new_arr['cost_price'],
					'stock_num_new' =>$new_arr['stock_num'],
					'operator_id' =>$operator_id,
					'created' =>array('exp', 'NOW()'),
					
				);
				
			}
			D('Stock/StockChangeHistory')->insertStockChangeHistory($stockin_change_history);


//        需要修改 最后的库存总量
//        logic 更新库存
//        logic 更新入库单状态
            $tmp_data["status"] = 80;
            $tmp_data["check_time"] = date("Y-m-d H:i:s");
            D("Stock/StockInOrder")->where("stockin_id =" . $now_stockin_id)->save($tmp_data);

            D('Common/SysProcessBackground')->stockinChange($now_stockin_id);

            //纪录完成日志
            $finish_log_data = array(
                "order_type" => 1,
                "order_id" => $now_stockin_id,
                "operator_id" => $operator_id,
                "operate_type" => 17,
                "message" => "完成入库单",
            );
            D('StockInoutLog')->add($finish_log_data);
        } catch (\PDOException $e) {
            $msg = $e->getMessage();
            if($handle_type == 'other'){
                $this->rollback();
            }
            \Think\Log::write($msg);
            $data['status'] = 1;
            $data['info'] = \Think\Model::PDO_ERROR;
            return $data;
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            if($handle_type == 'other'){
                $this->rollback();
            }
            \Think\Log::write($msg);
            $data['status'] = 1;
            $data['info'] = $msg;
            return $data;
        }
        if($handle_type == 'other'){
            $this->commit();
        }
        
        $data['status'] = 0;
        $data['info'] = "提交成功";
        $data['data'] = array(
            'check_time'=>$tmp_data['check_time'],
            'status'    =>$tmp_data['status'],
            'id'        =>$now_stockin_id
        ); 
        
        return $data;
    }

    public function addStockInOrder(&$result, &$search, &$rows)
    {
        $this->startTrans();
        try {
            switch ((string)$search['src_order_type']) {
                case '1': {
                    //purchase
                    $sql_purchase_no = 'select FN_SYS_NO("purchase") stockin_no';
                    $res_purchase_no = $this->query($sql_purchase_no);
                    $search['src_order_no'] = $res_purchase_no[0]['stockin_no'];
                    break;
                }
                case '6': {
                    //OtherRefund   无原始单号
                   $search['src_order_no'] = '';
                    break;
                }
                case '3': {
                    $sql_check_sales_refund_status = "SELECT process_status ,refund_id FROM sales_refund WHERE refund_no= '%s'";
                    $res_check_sales_refund_status = $this->query($sql_check_sales_refund_status,$search['src_order_no']);
                    $sales_refund_status = (int)$res_check_sales_refund_status[0]['process_status'];
                    if ($sales_refund_status <> 60 && $sales_refund_status <> 65 && $sales_refund_status <> 70 && $sales_refund_status <> 69) {
                        //入库单对应的退货单状态错误
                        $result['info'] = "入库单对应的退货单状态错误";
                        $result['status'] = 0;
                        $this->rollback();
                        return $result;
                        break;
                    }
                    break;
                }
                default:
                    $result['info'] = "入库单类型填写错误";
                    $result['status'] = 0;
                    \Think\Log::write($this->name."-addStockInOrder-"."入库单类型填写错误");
                    return ;
            }
            $sql_get_no = 'select FN_SYS_NO("stockin") stockin_no';
            $res_stockin_order_no = $this->query($sql_get_no);
            $stockin_order_no = $res_stockin_order_no[0]['stockin_no'];
            
            //获取默认的入库仓库id
//            $warehouse_info = D('Setting/Warehouse')->getWarehouseLimitOne();
            
//            $warehouse_id = $search['warehouse_id'];
            $warehouse_info = UtilDB::getCfgList(array('warehouse'),array('warehouse'=>array('warehouse_id'=>$search['warehouse_id'])));
            if(empty($warehouse_info['warehouse'])){
                E('入库仓库不存在');
            }
            $result['status'] = 1;
            $result['info'] = $stockin_order_no;

            //logic 新建入库单
            $search['stockin_no'] = $stockin_order_no;
            $search['goods_amount'] = $search['src_price'];
            $search['status'] = 20;
            D("Stock/StockInOrder")->add($search);

            $sql_query_id = "SELECT LAST_INSERT_ID() stockin_id";
            $res_inserted_stockin_id = $this->query($sql_query_id);
            $now_stockin_id = $res_inserted_stockin_id[0]['stockin_id'];
            //logic 插入新建入库单日志

            $tmp_data = array(
                "order_type"    => 1,
                "order_id"      => $now_stockin_id,
                "operator_id"   => $search['operator_id'],
                "operate_type"  => 11,
                "message"       => "创建入库单",
            );
            D("StockInoutLog")->add($tmp_data);
            unset($tmp_data);
            foreach ($rows as $key => $value) {
                $value = (array)$value;
                /*
                *src_order_detail_id 字段使用的为默认的0值
                *remark  在编辑中没有添加字段，需要添加编辑字段，现使用默认值Reserved field
                */
                $tmp_data = array(
                    "stockin_id"            => $now_stockin_id,
                    "src_order_type"        => $search['src_order_type'],
                    "src_order_detail_id"   => $value['id'],
                    "spec_id"               => $value['spec_id'],
                    "expect_num"            => $value['expect_num'],        //预期入库数量（基本单位数量，显示可自动转换成辅助单位）
                    "base_unit_id"          => $value['base_unit_id'],      //基本单位
                    "num"                   => $value['num'],               //库存单位量
                    "src_price"             => $value['src_price'],         //原价
                    "cost_price"            => $value['cost_price'],        //成本价,为空时表示成本不确定
                    "tax"                   => $value['tax_rate'],          //税率
                    "tax_price"             => $value['tax_price'],         //税后单价
                    "tax_amount"            => $value['tax_amount'],        //税后金额
                    "total_cost"            => $value['total_cost'],        //'总成本num*cost_price'
                    "remark"                => isset($value['remark'])?$value['remark']:"",
                    'position_id'           => $value['position_id']
                );
                //logic 新建入库详单
                D('StockinOrderDetail')->add($tmp_data);
                unset($tmp_data);
            }
            //logic 根据入库详单更新入库单
            $res_calc = $this->query("SELECT COUNT(spec_id) AS spec_type_count,SUM(num) goods_count,SUM(total_cost) total_cost  FROM stockin_order_detail  WHERE stockin_id=%d", $now_stockin_id);

            $res_calc = $res_calc[0];


            $tmp_data = array(
                "goods_count"       => $res_calc['goods_count'],
                "goods_type_count"  => $res_calc['spec_type_count'],
                "created"           => date("Y-m-d H:i:s"),
            );
            D("Stock/StockInOrder")->where("stockin_id = $now_stockin_id")->save($tmp_data);
            unset($tmp_data);
            $this->commit();
        } catch (\PDOException $e) {
            $msg = $e->getMessage();
            \Think\Log::write('pdo:'.$msg);
            $result['info'] = \Think\Model::PDO_ERROR;
            $result['status'] = 0;
            $this->rollback();
            return $result;
        } catch (\Think\Exception $e) {
            $msg = $e->getMessage();
//            \Think\Log::write($msg);
            $result['info'] = $msg;
            $result['status'] = 0;
            $this->rollback();
            return $result;
        }catch (\Exception $e) {
            $msg = $e->getMessage();
            \Think\Log::write($msg);
            $result['info'] = $msg;
            $result['status'] = 0;
            $this->rollback();
            return $result;
        }
       
        return $result;
    }

    public function updataStockInOrder(&$result, &$search, &$rows)
    {
        $this->startTrans();
        try {
            $rows_remark = "";//reservedField
            $result['status'] = 2;
            $result['info'] = $search['stockin_no'];
            $tmp_data = array("sio.logistics_id","sio.warehouse_id", "sio.logistics_no","cw.name", "sio.post_fee", "sio.other_fee", "sio.remark", "sio.stockin_id", "sio.src_order_type");
            $sql_old_stockin_data = D("Stock/StockInOrder")->alias('sio')->field($tmp_data)->join("left join cfg_warehouse cw on cw.warehouse_id = sio.warehouse_id")->where(array('stockin_no'=>$search['stockin_no']))->find();
            $now_stockin_id = $sql_old_stockin_data['stockin_id'];
            $stockin_id = $now_stockin_id;
            $old_stockin_data = $sql_old_stockin_data;
            unset($tmp_data);
            $search["src_order_type"] = $sql_old_stockin_data["src_order_type"];
            $warehouse_info = UtilDB::getCfgList(array('warehouse'),array('warehouse'=>array('warehouse_id'=>$search['warehouse_id'])));
            if(empty($warehouse_info['warehouse'])){
                E('入库仓库不存在');
            }
            //更新入库单
            $tmp_data = array(
                "logistics_id"      => $search['logistics_id'],
                "warehouse_id"      => $search['warehouse_id'],
                "logistics_no"      => $search['logistics_no'],
                "goods_amount"      => $search['src_price'],
                "total_price"       => $search['total_price'],
                "discount"          => $search['discount'],
                "post_fee"          => $search['post_fee'],
                "other_fee"         => $search['other_fee'],
                "tax_amount"        => $search['tax_amount'],
                "remark"            => $search['remark'],
                "modified"          => date('y-m-d H:i:s',time()),
                "operator_id"       => $search['operator_id'],
            );
            /* $return_data = array(
                'goods_amount' => $search['src_price'],
                "total_price"       => $search['total_price'],
                "discount"          => $search['discount'],
                "post_fee"          => $search['post_fee'],
                "other_fee"         => $search['other_fee'],
                "remark"            => $search['remark'],
                "modified"          => date('y-m-d H:i:s',time()),
            ); */
            D("Stock/StockInOrder")->where(array("stockin_no"=>$search['stockin_no']))->save($tmp_data);
            unset($tmp_data);

            $tmp_data = array(
                "order_type"        => 1,
                "order_id"          => $stockin_id,
                "operator_id"       => $search['operator_id'],
                "operate_type"      => 13,
                "message"           => "编辑入库单",
            );
            D("StockInoutLog")->add($tmp_data);
            unset($tmp_data);

            if ($old_stockin_data['logistics_id'] != $search['logistics_id']) {
                $logistics = UtilDB::getCfgList(array('logistics'));
                $logistics = $logistics['logistics'];
                $len = count($logistics);
                for ($i = 0; $i < $len; $i++) {
                    if ($logistics[$i]['id'] == $old_stockin_data['logistics_id']) {
                        $old_logistics = $logistics[$i]['name'];
                    };
                    if ($logistics[$i]['id'] == $search['logistics_id']) {
                        $new_logistics = $logistics[$i]['name'];
                    };
                }
                $msg['logistics_id'] = "物流公司：由[" . $old_logistics . "]变为[" . $new_logistics . "];";
            } else {
                $msg['logistics_id'] = "";
            };
            if ($old_stockin_data['logistics_no'] != $search['logistics_no']) {
                $msg['logistics_no'] = "物流单号：由[" . $old_stockin_data['logistics_no'] . "]变为[" . $search['logistics_no'] . "];";
            } else {
                $msg['logistics_no'] = "";
            };
            if ($old_stockin_data['post_fee'] != $search['post_fee']) {
                $msg['post_fee'] = "邮费：由[" . $old_stockin_data['post_fee'] . "]变为[" . $search['post_fee'] . "];";
            } else {
                $msg['post_fee'] = "";
            };
            if ($old_stockin_data['other_fee'] != $search['other_fee']) {
                $msg['other_fee'] = "其他费用：由[" . $old_stockin_data['other_fee'] . "]变为[" . $search['other_fee'] . "];";
            } else {
                $msg['other_fee'] = "";
            };
            if ($old_stockin_data['remark'] != $search['remark']) {
                $msg['remark'] = "评论：由[" . $old_stockin_data['remark'] . "]变为[" . $search['remark'] . "];";
            } else {
                $msg['remark'] = "";
            };
            if ($old_stockin_data['warehouse_id'] != $search['warehouse_id']) {
                $msg['warehouse'] = "仓库：由[" . $old_stockin_data['name'] . "]变为[" . $warehouse_info['warehouse'][0]['name'] . "];";
            } else {
                $msg['warehouse'] = "";
            };

            

            $stockin_msg = implode("", $msg);
            if (!empty($stockin_msg)) {

                $tmp_data = array(
                    "order_type" => 1,
                    "order_id" => $stockin_id,
                    "operator_id" => $search['operator_id'],
                    "operate_type" => 13,
                    "message" => $stockin_msg,
                );
                D("StockInoutLog")->add($tmp_data);
                unset($tmp_data);
            }
            //删除已删除单品
            foreach ($rows['del_spec'] as $key => $value) {
                $value = (array)$value;
                D("StockinOrderDetail")->where(array('stockin_id'=>$stockin_id,'spec_id'=>$value['spec_id']))->delete();
                $tmp_data = array(
                    "order_type" => 1,
                    "order_id" => $stockin_id,
                    "operator_id" => $search['operator_id'],
                    "operate_type" => 13,
                    "message" => "删除单品，商家编码:" . $value['spec_no'],
                );
                D("StockInoutLog")->add($tmp_data);
                unset($tmp_data);
            }
            //更新入库详单
            foreach ($rows['update_spec'] as $key => $value) {
                $value = (array)$value;
                if ($value['id'] == null) {
                    $value['id'] = 0;
                }
                $tmp_data = array("spec_id", "num", "cost_price", "remark","rec_id");
                $exist = D("StockinOrderDetail")->where(array('stockin_id'=>$stockin_id,'spec_id'=>$value['spec_id']))->field($tmp_data)->find();
                unset($tmp_data);

                if (empty($exist)) {
                    $tmp_data = array(
                        "stockin_id"            => $stockin_id,
                        "src_order_type"        => $search['src_order_type'],
                        "src_order_detail_id"   => $value['id'],
                        "spec_id"               => $value['spec_id'],
                        "num"                   => $value['num'],
                        "expect_num"            => $value['expect_num'],
                        "base_unit_id"          => $value['base_unit_id'],
                        "src_price"             => $value['src_price'],
                        "cost_price"            => $value['cost_price'],
                        "tax"                   => $value['tax_rate'],
                        "tax_price"             => $value['tax_price'],
                        "tax_amount"            => $value['tax_amount'],
                        "total_cost"            => $value['total_cost'],
                        "remark"                => $rows_remark,
                        'position_id'           => $value['position_id'],
                        "created"               => date('y-m-d H:i:s',time()),
                    );
                    D("StockinOrderDetail")->add($tmp_data);
                    unset($tmp_data);

                    $tmp_data = array(
                        "order_type" => 1,
                        "order_id" => $stockin_id,
                        "operator_id" => $search['operator_id'],
                        "operate_type" => 13,
                        "message" => "增加单品，商家编码" . $value['spec_no'],
                    );
                    D("StockInoutLog")->add($tmp_data);
                    unset($tmp_data);
                } else {

                    $tmp_data = array(
                        "src_price"             => $value['src_price'],
                        "cost_price"            => $value['cost_price'],
                        "tax"                   => $value['tax_rate'],
                        "tax_price"             => $value['tax_price'],
                        "tax_amount"            => $value['tax_amount'],
                        "total_cost"            => $value['total_cost'],
                        "remark"                => $rows_remark,
                        'num'                   => $value['num'],
                        'position_id'           => $value['position_id'],
                        "modified"              => date('y-m-d H:i:s',time()),
                        "created"               => date('y-m-d H:i:s',time()),
                    );
                    D("StockinOrderDetail")->where("rec_id = " . $exist['rec_id'])->save($tmp_data);
                    unset($tmp_data);

                    $msg_price      = "";
                    $msg_num        = "";
                    $msg_remark     = "";
                    if ((int)$value['num'] != (int)$exist['num']) {
                        $msg_num = "数量由[" . $exist['num'] . "]变为[" . $value['num'] . "]";
                    }
                    if ($value['cost_price'] != $exist['cost_price']) {
                        $msg_cost_price = "入库价由[" . $exist['cost_price'] . "]变为[" . $value['cost_price'] . "]";
                    }
                    if ($value['tax_rate'] != $exist['tax']) {
                        $msg_tax = "税率由[" . $exist['tax'] . "]变为[" . $value['tax_rate'] . "]";
                    }
                    if ($value['src_price'] != $exist['src_price']) {
                        $msg_src_price = "原价由[" . $exist['src_price'] . "]变为[" . $value['src_price'] . "]";
                    }
                    
                    $msg = $msg_num .'-'. $msg_cost_price .'-'. $msg_tax.'-'.$msg_src_price;

                    if (!empty($msg)) {
                        $tmp_data = array(
                            "order_type" => 1,
                            "order_id" => $stockin_id,
                            "operator_id" => $search['operator_id'],
                            "operate_type" => 13,
                            "message" => "修改单品，商家编码" . $value['spec_no'] . "" . $msg,
                        );
                        D("StockInoutLog")->add($tmp_data);
                        unset($tmp_data);
                    }
                }
            }
            //logic 根据入库详单更新入库单
            $res_calc = $this->query("SELECT COUNT(spec_id) AS spec_type_count,SUM(num) goods_count,SUM(total_cost) total_cost  FROM stockin_order_detail  WHERE stockin_id=%d", $now_stockin_id);

            $res_calc = $res_calc[0];


            $tmp_data = array(
                "goods_count" => $res_calc['goods_count'],
                "goods_type_count" => $res_calc['spec_type_count'],
            );
            D("Stock/StockInOrder")->where("stockin_id = $now_stockin_id")->save($tmp_data);
            unset($tmp_data);
        } catch (\PDOException $e) {
            $msg = $e->getMessage();
            \Think\Log::write($msg);
            $result['info'] = \Think\Model::PDO_ERROR;
            $result['status'] = 0;
            $this->rollback();
            return $result;
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            \Think\Log::write($msg);
            $result['info'] = \Think\Model::E_ERROR;
            $result['status'] = 0;
            $this->rollback();
            return $result;
        }
        $this->commit();
//         $result['data'] = $return_data;
        return $result;
    }

    public function costPriceCalc($old_num, $old_price, $new_num, $new_price)
    {
        $arr = array();
        $old_price = (float)$old_price;
        $new_price = (float)$new_price;
        if ($old_num >= 0) {
            //没有负库存
            if($old_num+$new_num >0){
                $arr['stock_num'] = $old_num+$new_num;
                //加权平均
                $arr['cost_price'] = ($old_num*$old_price+$new_num*$new_price)/($old_num+$new_num);
            }else{
                //之前库存为0，入库数量也是0，特殊处理，否则会除0异常
                $arr['stock_num'] = 0;
                $arr['cost_price'] = $new_price;
            }
        }else{
            //有负库存
            if(abs($old_num)>=$new_num){
                //如果负库存量大于 该次入库数量
                $arr['stock_num'] = $old_num+$new_num;
                //cost_price等于原来的cost_price
                $arr['cost_price'] = $old_price;
            }else{
                //如果负库存量 小于 该次入库数量
                $arr['stock_num'] = $old_num+$new_num;
                //cost_price等于新的的cost_price
                $arr['cost_price'] = $new_price;
            }
        }
        return $arr;
    }

}