<?php
namespace Purchase\Model;

use Think\Model;
use Think\Exception\BusinessLogicException;
class StallsOrderDetailModel extends Model
{
    const SORT_FINISH = 3;
    protected $tableName = 'stalls_less_goods_detail';
    protected $pk        = 'rec_id';
    public function insertStallsOrderDetailForUpdate($data,$update = false,$options = ''){
        try {
            if(empty($data[0]))
            {
                $res = $this->add($data,$options,$update);
    
            }else{
                $res = $this->addAll($data,$options,$update);
    
            }
            return $res;
        } catch (\PDOException $e) {
            $msg = $e->getMessage();
            \Think\Log::write($this->name."-insertStallsOrderDetailForUpdate-".$msg);
            E(self::PDO_ERROR);
        }
    }
   
	public function showStallsDetail($id, $page = 1, $rows = 20){
        $rows=intval($rows);
        $page=intval($page);
        $page_info = I('','',C('JSON_FILTER'));
        if(isset($page_info['page']))
        {
            $rows = intval($page_info['rows']);
            $page = intval($page_info['page']);
        }
		try{
			$point_number=get_config_value('point_number',0);
			$num = 'CAST(sum(sod.num) AS DECIMAL(19,'.$point_number.')) num';
			$in_num = 'CAST(sum(sod.stockin_status) AS DECIMAL(19,'.$point_number.')) in_num';
			$put_num = 'CAST(sum(sod.pickup_status) AS DECIMAL(19,'.$point_number.')) put_num';
            $where = array('sod.stalls_id'=>$id);
            $limit = ($page - 1)*$rows.','.$rows;
            $all_data = $this->fetchsql(false)->alias('sod')->field('sod.rec_id as id')->where($where)->group('sod.spec_id')->select();
            $rows_num = count($all_data);
            $fields =  array('sod.rec_id as id','pp.provider_name','pp.id as provider_id','gs.spec_no',$in_num,$put_num,'sod.spec_id','gg.goods_no','gg.goods_name','gb.brand_name','gs.spec_code','gs.spec_name','gs.barcode',$num,'CAST(sum(sod.price)/count(sod.spec_id) AS DECIMAL(19,4)) as price','CAST(sum(sod.price) AS DECIMAL(19,4)) as amount','sod.remark','cgu.name as unit_name');
			$row = $this->fetchsql(false)->alias('sod')->field($fields)->join('LEFT JOIN purchase_provider pp ON pp.id = sod.provider_id')->join('LEFT JOIN goods_spec gs ON gs.spec_id = sod.spec_id')->join('LEFT JOIN goods_goods gg ON gg.goods_id = gs.goods_id')->join('LEFT JOIN goods_brand gb ON gg.brand_id = gb.brand_id')->join('left join cfg_goods_unit cgu on cgu.rec_id = gs.unit')->where($where)->group('gs.spec_no')->limit($limit)->select();
            $data = array('total'=>$rows_num,'rows'=>$row);
        }catch(\PDOException $e){
			$msg=$e->getMessage();
			\Think\Log::write($msg);
			SE(self::PDO_ERROR);
		}
		return $data;
	}
    public function getStallsDetail($id){
		try{
			$point_number=get_config_value('point_number',0);
			$num = 'CAST(sum(sod.num) AS DECIMAL(19,'.$point_number.')) num';
			$in_num = 'CAST(sum(sod.stockin_status) AS DECIMAL(19,'.$point_number.')) in_num';
			$put_num = 'CAST(sum(sod.pickup_status) AS DECIMAL(19,'.$point_number.')) put_num';
            $where = array('sod.stalls_id'=>$id);
            $all_data = $this->fetchsql(false)->alias('sod')->field('sod.rec_id as id')->where($where)->group('sod.spec_id')->select();
            $rows_num = count($all_data);
            $fields =  array('sod.rec_id as id','pp.provider_name','pp.id as provider_id','gs.spec_no',$in_num,$put_num,'sod.spec_id','gg.goods_no','gg.goods_name','gb.brand_name','gs.spec_code','gs.spec_name','gs.barcode',$num,'CAST(sum(sod.price)/count(sod.spec_id) AS DECIMAL(19,4)) as price','CAST(sum(sod.price) AS DECIMAL(19,4)) as amount','sod.remark','cgu.name as unit_name');
            $data = $this->fetchsql(false)->alias('sod')->field($fields)->join('LEFT JOIN purchase_provider pp ON pp.id = sod.provider_id')->join('LEFT JOIN goods_spec gs ON gs.spec_id = sod.spec_id')->join('LEFT JOIN goods_goods gg ON gg.goods_id = gs.goods_id')->join('LEFT JOIN goods_brand gb ON gg.brand_id = gb.brand_id')->join('left join cfg_goods_unit cgu on cgu.rec_id = gs.unit')->where($where)->group('gs.spec_no')->select();
        }catch(\PDOException $e){
			$msg=$e->getMessage();
			\Think\Log::write($msg);
			SE(self::PDO_ERROR);
		}
		return $data;
	}
    public function getStallsSingleGoodsInfo($id){
        try{
            $point_number=get_config_value('point_number',0);
            $num = 'CAST(sum(sod.num) AS DECIMAL(19,'.$point_number.')) num';
            $in_num = 'CAST(sum(sod.stockin_status) AS DECIMAL(19,'.$point_number.')) in_num';
            $put_num = 'CAST(sum(sod.pickup_status) AS DECIMAL(19,'.$point_number.')) put_num';
            $where = array('sod.rec_id'=>$id);
            $fields =  array('gs.spec_no',$in_num,$put_num,'sod.spec_id','gg.goods_no','gg.goods_name','gb.brand_name','gs.spec_code','gs.spec_name','gs.barcode','gs.img_url as url',$num,'sod.price','count(sod.spec_id)*sod.price as amount','sod.remark','cgu.name as unit_name');
            $data = $this->fetchsql(false)->alias('sod')->field($fields)->join('LEFT JOIN goods_spec gs ON gs.spec_id = sod.spec_id')->join('LEFT JOIN goods_goods gg ON gg.goods_id = gs.goods_id')->join('LEFT JOIN goods_brand gb ON gg.brand_id = gb.brand_id')->join('left join cfg_goods_unit cgu on cgu.rec_id = gs.unit')->where($where)->select();
        }catch(\PDOException $e){
            $msg=$e->getMessage();
            \Think\Log::write($msg);
            SE(self::PDO_ERROR);
        }
        return $data;
    }
	public function showStallsLog($id){
		try{
			$field = array('he.account','sol.remark','sol.created');
			$where = array('stalls_id'=>$id);
			$data = M('stalls_order_log')->alias('sol')->field($field)->join('LEFT JOIN hr_employee he ON he.employee_id = sol.operator_id')->where($where)->order('sol.created desc')->select();
		}catch(\PDOException $e){
			\Think\Log::write($e->getMessage());
			SE(self::PDO_ERROR);
		}
		return $data;
	}
	public function updateProvider($details,$order_id,$purchaser_id){
		try{
			$log_data = array();
			$goods_info = array();
			$operator_id = get_operator_id();
			$this->startTrans();
			D('StallsOrder')->where(array('stalls_id'=>$order_id))->save(array('purchaser_id'=>$purchaser_id));
			foreach($details['update'] as $key=>$detail){
				$this->where(array('stalls_id'=>$order_id,'spec_id'=>$detail['spec_id']))->field(array('provider_id','price'))->save($detail);
				$goods_info[] = array(
					'spec_id'=>$detail['spec_id'],
					'provider_id'=>$detail['provider_id'],
					'price'=>$detail['price'],
					'last_price'=>$detail['price'],
					'created' => array('exp','NOW()'),
					
				);
				$log_data[] = array(
					'stalls_id' => $order_id,
					'operator_id' => $operator_id,
					'type' => 12,
					'remark' => "修改货品:修改货品".$detail['spec_no']."的供应商".'或采购价',
					'created' => array('exp','NOW()'),
				);
			}
			$update = array('modified'=>array('exp','NOW()'));
			M('purchase_provider_goods')->addAll($goods_info,'',$update);
			if(empty($log_data[0])){
				M('stalls_order_log')->add($log_data);
			}else{
				M('stalls_order_log')->addAll($log_data);
			}
			$this->commit();
		}catch(\PDOException $e){
			\Think\Log::write($e->getMessage());
			$this->rollback();
			SE(self::PDO_ERROR);
		}catch(BusinessLogicException $e){
			\Think\Log::write($e->getMessage());
			$this->rollback();
			SE($e->getMessage);
		}catch(\Exception $e){
			\Think\Log::write($e->getMessage());
			$this->rollback();
			SE(self::PDO_ERROR);
		}
		return true;
	}
	public function cancelStallsOrder($id){
		try{
			$data['status'] = 0;
			$data['info'] = '取消成功';
			$operator_id = get_operator_id();
			$where = array('stalls_id'=>$id);
			$result=D('StallsOrder')->fetchsql(false)->field('status')->where($where)->select();
			$data_detali = $this->query('select rec_id from stalls_less_goods_detail where (stockin_status = 1 or pickup_status = 1) and stalls_id = %d',$id);
			if(!empty($data_detali)){
				SE('档口单货品已取货或已入库，不能取消');
			}
			if($result[0]['status'] != 20){
				SE('档口单状态不正确,只能取消编辑中的档口单');
			}
			$this->startTrans();
			$update_status=array('status'=>10);
			$updata_purchase = D('StallsOrder')->where($where)->save($update_status);
			$this->where($where)->save(array('generate_status'=>0));
			$log_data = array(
				'stalls_id'=>$id,
				'operator_id'=>$operator_id,
				'type'=>8,
				'remark'=>'取消档口单'
			);
			M('stalls_order_log')->add($log_data);
			$this->commit();
		}catch(\PDOException $e){
			\Think\Log::write($e->getMessage());
			$this->rollback();
			SE(self::PDO_ERROR);
		}catch(BusinessLogicException $e){
			\Think\Log::write($e->getMessage());
			$this->rollback();
			SE($e->getMessage());
		}catch(\Exception $e){
			\Think\Log::write($e->getMessage());
			$this->rollback();
			SE(self::PDO_ERROR);
		}
		return $data;
	}
	public function pickListNotStallsOrder($goodsInfo){

        $result = array();
        $result['status'] = 0;
        $result['msg'] = 'success';
        $result['data'] = array();
        $printData = array();
        $sortData = array();
        $box_no = '';
        $trade_no = '';
        $trade_id = '';
        try{
            $this->startTrans();
            $lessGoodsOrder = $this->field('trade_no,trade_id,sort_status')->where(array('sort_status'=>2))->group('trade_no')->select();
//            \Think\Log::write('lessGoods--'.print_r($lessGoodsOrder,true));
            if(empty($lessGoodsOrder)){
                SE('请先将该订单中所有档口货品扫描完成');
            }
            $stockoutOrderModel = D('Stock/StockOutOrder');
            $box_goods_detail_model = M('box_goods_detail');
            $sorting_wall_detail_model = M('sorting_wall_detail');

            foreach ($lessGoodsOrder as $order){
                $goods = $stockoutOrderModel->alias('so')->field('sod.spec_no,sod.num,so.box_no,st.revert_reason')->join('inner join sales_trade st on st.trade_id = so.src_order_id')->join('LEFT JOIN stockout_order_detail sod ON sod.stockout_id = so.stockout_id')->where(array('src_order_no'=>$order['trade_no']))->select();
                foreach ($goods as $good){
//                    \Think\Log::write('spec_no1==='.$good['spec_no'].'spec_no2==='.$goodsInfo['spec_no']);
                    if($good['spec_no'] != $goodsInfo['spec_no']) continue;
					if($good['revert_reason'] != 0){
						SE('货品对应的订单已被驳回');
					}
                    $goodsNumberOfSpecInBox = $box_goods_detail_model->field('sum(num) as goods_num_spec')->where(array('trade_no'=>$order['trade_no'],'spec_id'=>$goodsInfo['spec_id']))->find();
                    $goodsNumOfSpecInBox = empty($goodsNumberOfSpecInBox)?0:intval($goodsNumberOfSpecInBox['goods_num_spec']);
                    $goodsNumOfSpecInOrder = intval($good['num']);
//                    \Think\Log::write('goodsNumOfSpecInOrder==='.$goodsNumOfSpecInOrder.'==goodsNumOfSpecInBox==='.$goodsNumOfSpecInBox.'==goodsNumOfSpecInStallsOrder==='.$goodsNumOfSpecInStallsOrder);
                    if($goodsNumOfSpecInBox<$goodsNumOfSpecInOrder){
					   $trade_id = $order['trade_id'];
					   $box_no = $good['box_no'];
					   $trade_no = $order['trade_no'];
					   // 货品放入格子
                       $this->putBox($good['box_no'],$order['trade_no'],$order['trade_id'],$goodsInfo['spec_id'],$box_goods_detail_model);						
                        break 2;
                    }else{
                        continue;
                    }
                }
            }
            if($box_no ==''){
                SE('请先将该订单中所有档口货品扫描完成！');
            }
			$pick_status = 0;
			$sortData['trade_no'] = $trade_no;
			$this->checkPick(1,$trade_no,$box_no,$trade_id,$box_goods_detail_model,$sorting_wall_detail_model,$sortData,$pick_status);
			$this->commit();
			$user_id = get_operator_id();
			$base_set = D('Setting/UserData')->fetchSql(false)->field(array('code,data'))->where(array('user_id'=>$user_id,'type'=>7,'code'=>'stalls_base_set'))->select();
			$base_set_data = json_decode($base_set[0]['data'],true);
            //$base_set_data = $base_set[0]['data'];
			if(empty($base_set_data) || empty($base_set_data['stalls_print_logistics'])){
				$cfg_print_logistics = 0;
			}else{
				$cfg_print_logistics = 1;
			}
			if($pick_status == 1 && $cfg_print_logistics == 0){
				// 打印信息
                $printData = $this->getLogisticsAndTagPrintData($trade_no);
                if($printData['status'] ==1){  // status = 2,非电子面单，不抛异常，下面的流程正常执行。
                    SE($printData['msg']);
                }else if(($printData['status'] ==2)){
                    $result['status'] = 2;
                    $result['msg'] = $printData['msg'];
                }
			}else{
				$printData = $this->getTagPrintData();
			}
			
            $stockout_order = D('Stock/StockOutOrder')->where(array('src_order_no'=>$trade_no))->find();
            $printData['data']['stockout_id'] = $stockout_order['stockout_id'];
			if($printData['status'] !=0){
				SE($printData['msg']);
			}
            $goodsData = array('goods_name'=>$goodsInfo['goods_name'],'spec_no'=>$goodsInfo['spec_no']);
            $result['data'] = ["status"=>$pick_status,"print_data"=>$printData['data'],"sort_data"=>$sortData,"goods_data"=>$goodsData];
        }catch(\PDOException $e){
            \Think\Log::write($e->getMessage());
            $this->rollback();
            SE(self::PDO_ERROR);
        }catch(BusinessLogicException $e){
            \Think\Log::write($e->getMessage(),\Think\Log::WARN);
            $this->rollback();
            SE($e->getMessage());
        }catch(\Exception $e){
            \Think\Log::write($e->getMessage());
            $this->rollback();
            SE(self::PDO_ERROR);
        }
        return $result;
    }
    private function getBlockResult($msg,$detailData){

	    $result = array();
        $sortData['trade_no'] =  $detailData['trade_no'];
        $goodsInfo = $this->getStallsSingleGoodsInfo($detailData['rec_id']);
        $goodsInfo = $goodsInfo[0];
        $result['status'] = 3;
        $result['msg'] = $msg;
        $result['data'] = ["sort_data"=>$sortData,"goods_data"=>$goodsInfo];
        return $result;
    }
    public function pickListStallsOrder($uniqueCode){

        $result = array();
        $result['status'] = 0;
        $result['msg'] = 'success';
        $result['data'] = array();
        $printData = array();
        $sortData = array();
        $change_detail = '';
        $box_no = '';
        $repeatSortFlag = 0;
        try{
            $this->startTrans();
            //------------------------入库 ---------------------
            $detailData = $this->field('rec_id,spec_id,trade_id,trade_no,stalls_id,warehouse_id,trade_status,block_reason,stockin_status,sort_status,pickup_status,generate_status')->where(array('unique_code'=>$uniqueCode))->select();
            if(empty($detailData)){
                SE('该唯一码（或条形码）不存在');
            }
			$revert_reason = M('sales_trade')->field('revert_reason')->where(array('trade_id'=>$detailData[0]['trade_id']))->find();
			if($revert_reason['revert_reason'] != 0){
				SE('货品对应的订单已被驳回审核');
			}

            $detailData = $detailData[0];
			if($detailData['sort_status'] == self::SORT_FINISH){
//                SE('该唯一码对应的订单已分拣完成');
                $this->rollback();
                return $this->getBlockResult('该唯一码对应的订单已分拣完成',$detailData);
            }
            if($detailData['trade_status'] !=0){
                SE('该唯一码对应的订单已被驳回审核');
            }
            $goodsInfo = $this->getStallsSingleGoodsInfo($detailData['rec_id']);
            $goodsInfo = $goodsInfo[0];
            $stockSpecModel = D('Stock/StockSpec');
            $stockSpecPositionModel = D('Stock/StockSpecPosition');
            if($detailData['stockin_status'] ==0){
                $stockSpec = $stockSpecModel->field('stock_num')->where(array("spec_id"=>$detailData['spec_id'],"warehouse_id"=>$detailData['warehouse_id']))->select();
                $stockSpecModel->where(array("spec_id"=>$detailData['spec_id'],"warehouse_id"=>$detailData['warehouse_id']))->save(array("stock_num"=>$stockSpec[0]['stock_num']+1));

                $stockSpecPosition = $stockSpecPositionModel->field('stock_num')->where(array("spec_id"=>$detailData['spec_id'],"warehouse_id"=>$detailData['warehouse_id']))->select();
                $stockSpecPositionModel->where(array("spec_id"=>$detailData['spec_id'],"warehouse_id"=>$detailData['warehouse_id']))->save(array("stock_num"=>$stockSpecPosition[0]['stock_num']+1));

                $date = date('Y-m-d H:i:s', time());
                $this->where(array('unique_code'=>$uniqueCode))->save(array('stockin_status'=>1,'stockin_time'=>$date));

                // 插入订单日志
                $message = '档口货品分拣入库--'.$uniqueCode;
                $this->insertToOrderLog($detailData['trade_id'],$message);
            }
            $this->commit();

            $stallsData = D('StallsOrder')->field('status')->where(array('stalls_id'=>$detailData['stalls_id']))->select();
            if($detailData['block_reason'] !=0){
                if($detailData['block_reason'] &(1|8|64|128|4096)){
                    if($detailData['block_reason'] &(8|64)){
                        $tradeInfo = D('SalesTrade')->field('cs_remark as remark,invoice_type as type,invoice_title as title,invoice_content as content')->where(array('trade_id'=>array('eq',$detailData['trade_id'])))->find();
                        $change_detail = json_encode($tradeInfo);
                    }
//                    SE("blockReasonDeal_".$detailData['block_reason']."_".$cs_remark);
                    return $this->getBlockResult("blockReasonDeal_".$detailData['block_reason']."_".$change_detail,$detailData);

                }
//                SE("blockReason_".$detailData['block_reason']);
                return $this->getBlockResult("blockReason_".$detailData['block_reason'],$detailData);
            }
            if(!empty($stallsData)){
                if($stallsData[0]['status'] == 10){
//                    SE('档口采购单已取消');
                    return $this->getBlockResult('档口采购单已取消',$detailData);
                }else if($stallsData[0]['status'] == 20){

                    $notStockInCount = $this->where(array('stalls_id'=>$detailData['stalls_id'],"stockin_status"=>0))->count();
                    if($notStockInCount == 0){
                        D('StallsOrder')->where(array('stalls_id'=>$detailData['stalls_id']))->save(array("status"=>90));
                    }
                }
            }
            $this->startTrans();
            //------------------------分拣 ---------------------
            if($detailData['sort_status'] == 1 || $detailData['sort_status'] == 2|| $detailData['sort_status'] == 3){
                $repeatSortFlag = 1;
            }
            $sortData['trade_no'] =  $detailData['trade_no'];
			$sortData['trade_id'] =  $detailData['trade_id'];
            $this->where(array('unique_code'=>$uniqueCode))->save(array("sort_status"=>1));
//            $stallsGoodsInOneOrder = $this->fetchSql(false)->where(array("trade_no"=>$detailData['trade_no']))->count();
			$stalls_num = $this->field('sum(num) as goods_count')->where(array('trade_id'=>$detailData['trade_id']))->find();
            $stock_num = D('Stock/StockOutOrder')->alias('so')->field('sum(sod.num) as goods_count')->join('left join stockout_order_detail sod on sod.stockout_id = so.stockout_id')->where(array('so.src_order_id'=>$detailData['trade_id']))->find();
			$pick_status = 0; //0 档口货品分拣完成 1 订单分拣完成 2档口货品未分拣完成 3档口货品和订单同时分拣完成
            $goodsCountInOrder = intval($stock_num['goods_count']);
            $goodsCountInStallsOrder = intval($stalls_num['goods_count']);
            if($goodsCountInOrder ==1){ //一单一货
                /* $printData = $this->getLogisticsAndTagPrintData($detailData['trade_no']);
                if($printData['status'] ==1){ // status = 2,非电子面单，不抛异常，下面的流程正常执行。
                    SE($printData['msg']);
                }else if(($printData['status'] ==2)){
                    $result['status'] = 2;
                    $result['msg'] = $printData['msg'];
                } */
				$pick_status = 3;
                $sortData['sort_finish'] = '分拣完成';
                $this->where(array("trade_id"=>$detailData['trade_id']))->save(array("sort_status"=>3));
                if($repeatSortFlag == 1){
                    $sortData['sort_finish'] = '重复分拣';
                    $repeatSortFlag = 0;
                }
                $sortData['box_no'] = '';
                // 插入订单日志
                $message = '档口单分拣完成';
                $this->insertToOrderLog($detailData['trade_id'],$message);
				$this->commit();
            }elseif ($goodsCountInOrder>1){ //一单多货

                if($goodsCountInStallsOrder == 0){
                    SE('该订单不存在档口单');
                }

                $orderData = D('Stock/StockOutOrder')->field('box_no,stockout_id,stockout_no')->where(array("src_order_no"=>$detailData['trade_no']))->select();
                $sorting_wall_detail_model = M('sorting_wall_detail');
                $box_goods_detail_model = M('box_goods_detail');
                $big_box_goods_map_model = M('big_box_goods_map');
				$dynamic_box_model = M('cfg_dynamic_box');
                $orderData = $orderData[0];
                $sys_config =  get_config_value(array('dynamic_allocation_box'),array(0));
                if($orderData['box_no'] == ''){
                    $sort_wall = D('Purchase/SortingWall')->where(array('type'=>1))->select();
                    if(empty($sort_wall)){SE('请先建立分拣墙');}
                    foreach ($sort_wall as $wall){
                        $envaliable_box = $sorting_wall_detail_model->where(array("wall_id"=>$wall['wall_id'],"is_use"=>0))->select();
                        if(empty($envaliable_box)){
                            continue;
                        }else{
                            $box_no = $envaliable_box[0]['box_no'];

                            // 防止sorting_wall_detail 和 box_goods_detail中分拣框状态不一致
                            $box_in_sorting = $box_goods_detail_model->where(array('box_no'=>$box_no,'sort_status'=>0))->select();
                            if(empty($box_in_sorting)){
                                D('Stock/StockOutOrder')->where(array("src_order_no"=>$detailData['trade_no']))->save(array("box_no"=>$box_no));
                                $stockout_id = $orderData['stockout_id'];
                                $sorting_wall_detail_model->where(array("box_id"=>$envaliable_box[0]['box_id']))->save(array("is_use"=>1,"stockout_id"=>$stockout_id));
                                if($sys_config['dynamic_allocation_box']==1){
                                    $dynamic_box_info = $dynamic_box_model->field('wall_id,wall_no,box_num,goods_num')->where(array('type'=>1,'is_disabled'=>0))->order('wall_no')->select();
									if(empty($dynamic_box_info)){SE('请先建立动态分拣墙');}
									$is_big_box = 0;
									$out_big_box_no = '';$out_sub_big_box_no = '';
									foreach($dynamic_box_info as $v){
										$valiable_box = $big_box_goods_map_model->field('box_no,big_box_no,sub_big_box_no,sum(goods_num) as num,count(big_box_no) as big_box_no_num')->where(array('big_box_no'=>array('like',$v['wall_no']."_%")))->group('big_box_no')->select();
										$big_box_no = '';$sub_big_box_no = '';
										$big_box_no_arr = array();
										if(empty($valiable_box)){
											$big_box_no = $v['wall_no'].'-1';
											$sub_big_box_no = $v['wall_no'].'-1-1';
										}else{
											foreach($valiable_box as $box){
												$arr_push_num = substr($box['big_box_no'],strrpos($box['big_box_no'],'-')+1);
												if(!in_array($arr_push_num,$big_box_no_arr,true)){
													$big_box_no_arr[]=$arr_push_num;
												}
												if($box['num']+$goodsCountInOrder>$v['goods_num']){
													continue;
												}else{
													$big_box_no = $box['big_box_no'];
													//$sub_big_box_no = substr($box['sub_big_box_no'],strrpos($box['sub_big_box_no'],'-')+1)+1;
													$sub_big_box_no = $box['big_box_no_num']+1;
													$sub_big_box_no = $box['big_box_no'].'-'.$sub_big_box_no;
													break;
												}
											}
										}
										if($big_box_no==''||$sub_big_box_no==''){
											if($v['box_num'] <= count($valiable_box)){
												continue;
											}
											$tmp_num = 1;
											while(true){
												if(!in_array($tmp_num,$big_box_no_arr)){
													break;
												}
												$tmp_num++;
											}
											$big_box_no = $v['wall_no'].'-'.$tmp_num;
											$sub_big_box_no = $big_box_no.'-1';
											$out_big_box_no = $v['wall_no'].'-'.$tmp_num;
											$out_sub_big_box_no = $big_box_no.'-1';
											$wall_id = $v['wall_id'];
											if($v['goods_num'] < $goodsCountInOrder){
												continue;
											}
											
										}
										$is_big_box = 1;
										$data = array('wall_id'=>$v['wall_id'],'box_no'=>$box_no,'big_box_no'=>$big_box_no,'sub_big_box_no'=>$sub_big_box_no,'trade_id'=>$detailData['trade_id'],'trade_no'=>$detailData['trade_no'],'stockout_id'=>$stockout_id,'stockout_no'=>$orderData['stockout_no'],'goods_num'=>$goodsCountInOrder);
										$big_box_goods_map_model->add($data);
										break;
									}
									if(!$is_big_box){
										$valiable_box_info = $big_box_goods_map_model->field('box_no')->group('big_box_no')->select();
										$dynamic_box_info = $dynamic_box_model->field('sum(box_num) as box_num')->where(array('type'=>1,'is_disabled'=>0))->find();
										if(count($valiable_box_info) >= $dynamic_box_info['box_num']){
											SE('分拣框不够用,请添加分拣框!');
										}
										$data = array('wall_id'=>$wall_id,'box_no'=>$box_no,'big_box_no'=>$out_big_box_no,'sub_big_box_no'=>$out_sub_big_box_no,'trade_id'=>$detailData['trade_id'],'trade_no'=>$orderData['stockout_no'],'goods_num'=>$goodsCountInOrder);
										$big_box_goods_map_model->add($data);
									}
                                }
                                break;
                            }else{
                                $this->where(array('unique_code'=>$uniqueCode))->save(array("sort_status"=>0));
                                //修复分拣框状态
                                $sorting_wall_detail_model->where(array("box_id"=>$envaliable_box[0]['box_id']))->save(array("is_use"=>1));
                                $this->commit();
                                SE('分拣框出现异常，请重新分拣');
                            }
                        }
                    }
                    if($box_no == ''){ SE('分拣墙已满'); }
                }else{
                    $box_no = $orderData['box_no'];
                }
                // 货品放入格子
                if($repeatSortFlag ==0){
					$this->putBox($box_no,$detailData['trade_no'],$detailData['trade_id'],$detailData['spec_id'],$box_goods_detail_model);
                }
                if($sys_config['dynamic_allocation_box']==1){
                    $sub_big_box_no = $big_box_goods_map_model->field('sub_big_box_no')->where(array("trade_id"=>$detailData['trade_id']))->find();
                    $sub_big_box_no = $sub_big_box_no['sub_big_box_no'];
                }
                $goodsCountInBox = $box_goods_detail_model->field('sum(num) as num')->where(array('trade_no'=>$detailData['trade_no']))->find();
                $goodsCountInBox = empty($goodsCountInBox)?0:intval($goodsCountInBox['num']);
                $notSortingCount = $this->where(array("trade_no"=>$detailData['trade_no'],"sort_status"=>0))->count();
                if($notSortingCount == 0){ //档口单分拣完成

                    if($goodsCountInOrder == $goodsCountInStallsOrder){ //不存在非档口货品，则订单分拣完成
                        $pick_status = 3;
                        $sortData['sort_finish'] = '分拣完成';
                        $this->orderPickOver(1,$box_goods_detail_model,$sorting_wall_detail_model,$box_no,$detailData['trade_id'],$sortData);
                        if($sys_config['dynamic_allocation_box']==1){
                            $sortData['box_no'] = $sub_big_box_no;
                        }
                        $this->commit();
					}else{ //档口单分拣完成，还存在未分拣的非档口货品需要分拣.

                        if($goodsCountInOrder != $goodsCountInBox){
                            $sortData['sort_finish'] = '还有非档口货品未分拣';
                            if($sys_config['dynamic_allocation_box']==1){
                                $sortData['box_no'] = $sub_big_box_no;
                            }else{
                                $sortData['box_no'] = $box_no;
                            }
                            //修改分拣状态为档口单已分拣完成--2
                            $this->where(array("trade_id"=>$detailData['trade_id']))->save(array("sort_status"=>2));
                            $this->commit();
                        }else{
                            //订单分拣完成，打印物流单。因为条码只能扫描一次，若打印物流单失败，则无法二次打印。只能去扫描之前的唯一码去打印物流单
							$pick_status = 1;
                            $sortData['sort_finish'] = '分拣完成';
                            if($sys_config['dynamic_allocation_box']==1){
                                $sortData['box_no'] = $sub_big_box_no;
                            }else{
                                $sortData['box_no'] = $box_no;
                            }
                        }
                    }
					
                }else{
					$pick_status = 2;
                    $printData = $this->getTagPrintData();

                    if($printData['status'] !=0){
                        SE($printData['msg']);
                    }
                    $sortData['sort_finish'] = '';
                    if($repeatSortFlag == 1){
                        $sortData['sort_finish'] = '重复分拣';
                        $repeatSortFlag = 0;
                    }
                    if($sys_config['dynamic_allocation_box']==1){
                        $sortData['box_no'] = $sub_big_box_no;
                    }else{
                        $sortData['box_no'] = $box_no;
                    }
					$this->commit();
                }
            }else{
                SE('订单不存在，请切换到英文输入法。如果已经是英文输入法，请联系管理员');
            }
			$user_id = get_operator_id();
			$base_set = D('Setting/UserData')->fetchSql(false)->field(array('code,data'))->where(array('user_id'=>$user_id,'type'=>7,'code'=>'stalls_base_set'))->select();
            $base_set_data = json_decode($base_set[0]['data'],true);
			if(empty($base_set_data) || empty($base_set_data['stalls_print_logistics'])){
				$cfg_print_logistics = 0;
			}else{
				$cfg_print_logistics = 1;
			}
            if(($pick_status == 1 && $cfg_print_logistics == 0) || ($pick_status == 0 && $cfg_print_logistics == 1) || $pick_status == 3){
				$printData = $this->getLogisticsAndTagPrintData($detailData['trade_no']);
				if($printData['status'] ==1){  // status = 2,非电子面单，不抛异常，下面的流程正常执行。
					//SE($printData['msg']);
					return $this->getBlockResult($printData['msg'],$detailData);
				}else if(($printData['status'] ==2)){
					$result['status'] = 2;
					$result['msg'] = $printData['msg'];
				}
			}
			$stockout_info = D('Stock/StockoutOrder')->field('stockout_id')->where(array('src_order_id'=>$detailData['trade_id']))->find();
			$printData['data']['stockout_id'] = $stockout_info['stockout_id'];
            $result['data'] = ["status"=>$pick_status,"print_data"=>$printData['data'],"sort_data"=>$sortData,"goods_data"=>$goodsInfo];

        }catch(\PDOException $e){
            \Think\Log::write($e->getMessage());
            $this->rollback();
            $result['status'] = 1;
            $result['msg'] = self::PDO_ERROR;
            SE(self::PDO_ERROR);
        }catch(BusinessLogicException $e){
            \Think\Log::write($e->getMessage(),\Think\Log::WARN);
            $this->rollback();
            $result['status'] = 1;
            $result['msg'] = $e->getMessage();
            SE($e->getMessage());
        }catch(\Exception $e){
            \Think\Log::write($e->getMessage());
            $this->rollback();
            $result['status'] = 1;
            $result['msg'] = $e->getMessage();
            SE(self::PDO_ERROR);
        }
        return $result;

    }

    private function insertToOrderLog($trade_id,$message){

        $operator = get_operator_id();
        // 插入分拣入库日志
        $insert_sales_log_data = array(
            'type' => '173',
            'trade_id' => $trade_id,
            'operator_id'=>$operator,
            'message'=>$message,
        );
        $res_insert_sales_log = D('Trade/SalesTradeLog')->addTradeLog($insert_sales_log_data);
    }

    private function getLogisticsAndTagPrintData($src_order_no){

        $result = array();
        $result['status'] = 0;
        $result['msg'] = 'success';
        $result['data'] = array();
        $printerInfo = array();

        $tagPrinterInfo = $this->getTagPrintData();
        if($tagPrinterInfo['status'] !=0){
            $result['status'] = 1;
            $result['msg'] = $tagPrinterInfo['msg'];
            return $result;
        }
        $printerInfo = $tagPrinterInfo['data'];
        $stockoutInfo = D('Stock/StockOutOrder')->field('stockout_id,logistics_id')->where(array('src_order_no'=>$src_order_no))->select();
        if(empty($stockoutInfo)){
            $result['status'] = 1;
            $result['msg'] = '该订单没有生成对应的出库单';
            return $result;
        }
        $stockout_id = $stockoutInfo[0]['stockout_id'];
        $logistics_id = $stockoutInfo[0]['logistics_id'];
        $logisticsData = D('Setting/Logistics')->getLogisticsInfo($logistics_id);
        $logisticsInfo = $logisticsData[0];
        if((int)$logisticsInfo['bill_type'] != 2){
            $result['status'] = 2;   //状态设为2，不抛异常。
            $result['msg'] = '自动打印物流单只支持打印菜鸟电子面单，请前往“单据打印”界面打印物流单';
            $printerInfo['stockout_id'] = $stockout_id;
            $printerInfo['logistics_id'] = $logistics_id;
            $printerInfo['src_order_no'] = $src_order_no;
            $result['data'] = $printerInfo;   //打不了物流单，但不影响吊牌的打印。
            return $result;
        }
        $fields = array('rec_id as id,type,title,content');
        $templatesData = D('Setting/PrintTemplate')->getTemplateByLogistics($fields,'4,8,7',$logisticsInfo['logistics_type'],false);
        if(empty($templatesData)){
            $result['status'] = 1;
            $result['msg'] = '请前往“打印模板”界面下载"'.$logisticsInfo['logistics_name'].'"物流公司下的模板';
            return $result;
        }
        $templatesInfo = $templatesData[0];
        $standerTemplateUrl = json_decode($templatesInfo['content'],true)['user_std_template_url'];

        $printerInfo['stockout_id'] = $stockout_id;
        $printerInfo['logistics_id'] = $logistics_id;
        $printerInfo['stander_template_url'] = $standerTemplateUrl;
        $printerInfo['src_order_no'] = $src_order_no;

        $result['data'] = $printerInfo;
        return $result;
    }
    public function getTagPrintData(){

        $result = array();
        $result['status'] = 0;
        $result['msg'] = 'success';
        $result['data'] = array();
        $user_id = get_operator_id();
        $baseData = D('Setting/UserData')->field(array('data'))->where(array('user_id'=>$user_id,'type'=>7,'code'=>'stalls_base_set'))->select();
        $baseInfo = json_decode($baseData[0]['data'],true);
        if(($baseInfo['print_tag'] == 0)||(!isset($baseInfo['print_tag']))){
            return $result;
        }

        $printerData = D('Setting/UserData')->field(array('data'))->where(array('user_id'=>$user_id,'type'=>7,'code'=>'stalls_print_set'))->select();
        if(empty($printerData)){
            $result['status'] = 1;
            $result['msg'] = '请先去“档口采购分拣”界面设置打印机和模板';
            return $result;
        } 
        $printerInfo = json_decode($printerData[0]['data'],true);
        $tag_template = D('Setting/PrintTemplate')->where(array('rec_id'=>$printerInfo['tag_template']))->select();
         if(empty($tag_template)){
            $result['status'] = 1;
            $result['msg'] = '请先去“档口采购分拣”界面设置“吊牌”模板';
            return $result;
        } 
        $tag_template = json_decode($tag_template[0]['content'],true);
        $tag_template = $tag_template['custom_area_url'];
        $printerInfo['tag_template'] = $tag_template;
        $printerInfo['stander_template_url'] = ''; //为了区分是否要打印物流单

        $result['data'] = $printerInfo;
        return $result;
    }
    public function changePrintStatus($unique_code,$order_no,$task_id){
        try{
//            $field = $task_id == 22?'logistics_print_status':'tag_print_status';
            $src_order_no = $order_no;
            if($task_id == 22 || $task_id == 66){
                $this->fetchSql(false)->where(array("trade_no"=>$src_order_no))->save(array('logistics_print_status'=>1));
            }else{
                $this->fetchSql(false)->where(array("unique_code"=>$unique_code))->save(array('tag_print_status'=>1));
            }
            $stockoutData = D('Stock/StockOutOrder')->field('stockout_id')->where(array('src_order_no'=>$src_order_no))->select();
            if(!empty($stockoutData)){
                if($task_id == 22 || $task_id == 66){
                    $stockout_ids = $stockoutData[0]['stockout_id'];
                    $print_type = 'logistics';
                    D("Stock/StockOutOrder")->changePrintStatus($stockout_ids,$print_type);
                }
            }

        }catch(\PDOException $e){
            \Think\Log::write($e->getMessage());
            SE(self::PDO_ERROR);
        }catch(BusinessLogicException $e){
            \Think\Log::write($e->getMessage());
            SE($e->getMessage());
        }catch(\Exception $e){
            \Think\Log::write($e->getMessage());
            SE(self::PDO_ERROR);
        }
    }
	public function pickListOrder($goodsInfo,$sortingCode){

        $result = array();
        $result['status'] = 0;
        $result['msg'] = 'success';
        $result['data'] = array();
        $printData = array();
        $sortData = array();
        $box_no = '';
        $trade_no = '';
        try{
            $this->startTrans();
            $lessGoodsOrder =M('stockout_print_batch')->field('queue')->where(array('pick_list_no'=>$sortingCode))->find();
//            \Think\Log::write('lessGoods--'.print_r($lessGoodsOrder,true));
            if(empty($lessGoodsOrder) || empty($lessGoodsOrder['queue'])){
                SE('分拣单号不正确');
            }
			$lessGoodsOrder_list = D('Stock/StockOutOrder')->alias('so')->fetchSql(false)->field('so.src_order_no as trade_no,so.src_order_id as trade_id')->join('inner join sales_trade st on st.trade_id = so.src_order_id')->where(array('so.stockout_id'=>array('in',$lessGoodsOrder['queue'])))->group('so.src_order_id')->order('st.revert_reason')->select();      
			$stockoutOrderModel = D('Stock/StockOutOrder');
            $box_goods_detail_model = M('box_goods_detail');
            $sorting_wall_detail_model = M('sorting_wall_detail');

            foreach ($lessGoodsOrder_list as $order){
                $goods = $stockoutOrderModel->alias('so')->field('sod.spec_no,sod.num,so.box_no,st.revert_reason')->join('inner join sales_trade st on st.trade_id = so.src_order_id')->join('LEFT JOIN stockout_order_detail sod ON sod.stockout_id = so.stockout_id')->where(array('so.src_order_no'=>$order['trade_no']))->select();
       			foreach ($goods as $good){
//                    \Think\Log::write('spec_no1==='.$good['spec_no'].'spec_no2==='.$goodsInfo['spec_no']);
                    if($good['spec_no'] != $goodsInfo['spec_no']) continue;
					if($good['revert_reason'] != 0){
						SE('货品对应的订单已被驳回');
					}
                    $goodsNumberOfSpecInBox = $box_goods_detail_model->field('sum(num) as goods_num_spec')->where(array('trade_no'=>$order['trade_no'],'spec_id'=>$goodsInfo['spec_id']))->find();
                    $goodsNumOfSpecInBox = empty($goodsNumberOfSpecInBox)?0:intval($goodsNumberOfSpecInBox['goods_num_spec']);
                    $goodsNumOfSpecInOrder = intval($good['num']);
                    if($goodsNumOfSpecInBox<$goodsNumOfSpecInOrder){             
						if($good['box_no'] == ''){
							$sort_wall = D('Purchase/SortingWall')->where(array('type'=>1))->select();
							if(empty($sort_wall)){SE('请先建立分拣墙');}
							foreach ($sort_wall as $wall){
								$envaliable_box = $sorting_wall_detail_model->where(array("wall_id"=>$wall['wall_id'],"is_use"=>0))->select();
								if(empty($envaliable_box)){
									continue;
								}else{
									$box_no = $envaliable_box[0]['box_no'];
									D('Stock/StockOutOrder')->where(array("src_order_no"=>$order['trade_no']))->save(array("box_no"=>$box_no));
                                    $stockout_order = D('Stock/StockOutOrder')->field('stockout_id')->where(array("src_order_no"=>$order['trade_no']))->find();
                                    $stockout_id = $stockout_order['stockout_id'];
									$sorting_wall_detail_model->where(array("box_id"=>$envaliable_box[0]['box_id']))->save(array("is_use"=>1,"stockout_id"=>$stockout_id));
									break;
								}
							}
							if($box_no == ''){ SE('分拣墙已满'); }
						}else{
							 $box_no = $good['box_no'];
							 
						}
						
                        $trade_no = $order['trade_no'];
						$trade_id = $order['trade_id'];
                        // 货品放入格子
						$this->putBox($good['box_no'],$order['trade_no'],$order['trade_id'],$goodsInfo['spec_id'],$box_goods_detail_model);
                        break 2;
                    }else{
                        continue;
                    }
                }
            }
			if($box_no ==''){
                SE('货品在已分拣或者在订单中不存在！');
            }
			$pick_status = 0;
			$sortData['trade_no'] = $trade_no;
			$this->checkPick(0,$trade_no,$box_no,$trade_id,$box_goods_detail_model,$sorting_wall_detail_model,$sortData,$pick_status);
			
			$this->commit();
			if($pick_status == 1){
				// 打印信息
                $printData = $this->getLogisticsAndTagPrintData($trade_no);
                if($printData['status'] ==1){  // status = 2,非电子面单，不抛异常，下面的流程正常执行。
                    SE($printData['msg']);
                }else if(($printData['status'] ==2)){
                    $result['status'] = 2;
                    $result['msg'] = $printData['msg'];
                }
			}else{
				$printData = $this->getTagPrintData();
			}
            $goodsData = array('goods_name'=>$goodsInfo['goods_name'],'spec_no'=>$goodsInfo['spec_no']);
            $result['data'] = ["status"=>$pick_status,"print_data"=>$printData['data'],"sort_data"=>$sortData,"goods_data"=>$goodsData];

        }catch(\PDOException $e){
            \Think\Log::write($e->getMessage());
            $this->rollback();
            SE(self::PDO_ERROR);
        }catch(BusinessLogicException $e){
            \Think\Log::write($e->getMessage(),\Think\Log::WARN);
            $this->rollback();
            SE($e->getMessage());
        }catch(\Exception $e){
            \Think\Log::write($e->getMessage());
            $this->rollback();
            SE(self::PDO_ERROR);
        }
		 if($sortData['sort_finish'] == '分拣完成'){
			 $all_sort_finish = D('Stock/StockOutOrder')->alias('so')->field('bgd.rec_id')->join('inner join box_goods_detail bgd on bgd.trade_id = so.src_order_id')->where(array('bgd.sort_status'=>1,'so.stockout_id'=>array('in',$lessGoodsOrder['queue'])))->group('bgd.trade_id')->select();
			 $order_num = substr_count($lessGoodsOrder['queue'],',')+1;
			 if((int)$order_num == (int)count($all_sort_finish)){
				 $sortData['sort_finish'] == '订单全部分拣完成';
			 }
		}
        return $result;
    }
	public function oneSplit($id,&$fail){
		 try {
           //获取配置
		   $this->startTrans();
           //$instant_split_sorted_goods=get_config_value('instant_split_sorted_goods',0);
		   
		   $user_id = get_operator_id();
		   $baseData = D('Setting/UserData')->field(array('data'))->where(array('user_id'=>$user_id,'type'=>7,'code'=>'stalls_base_set'))->select();
		   $baseInfo = json_decode($baseData[0]['data'],true);
		   $instant_split_sorted_goods = isset($baseInfo['instant_split_sorted_goods'])?$baseInfo['instant_split_sorted_goods']:0;
		   
		   $data = array();
		   $box_no = M('sorting_wall_detail')->field('box_no')->where(array('box_id'=>$id))->find();
           $order_info = M('box_goods_detail')->alias('bgd')->fetchSql(false)->field('sum(bgd.num) num,bgd.trade_id,bgd.trade_no')->where(array('bgd.box_no'=>$box_no['box_no'],'bgd.sort_status'=>0))->select();
		   if(empty($order_info) || (int)$order_info[0]['num'] == 0){
			   SE('分拣框尚未分拣货品');
		   }
		   $stockout = D('Stock/StockOutOrder')->field('stockout_id,warehouse_id')->where(array('src_order_id'=>$order_info[0]['trade_id']))->find();
		   
		   $order_info_detail = M('box_goods_detail')->alias('bgd')->fetchSql(false)->field('bgd.spec_id,bgd.num')->where(array('bgd.box_no'=>$box_no['box_no'],'bgd.sort_status'=>0))->select();
		   $trade_info = M('sales_trade_order')->alias('sto')->field('sto.spec_id,sto.actual_num,sto.src_oid')->where(array('sto.trade_id'=>$order_info[0]['trade_id']))->select();
		   $new_order_info = array();
           //考虑合并订单子订单货品信息一样的情况
           $spec=array();//记录订单已经拆分的数量==未分拣的拆出来
           foreach($order_info_detail as $v){
                $spec[$v['spec_id']]=$v['num'];//已分拣数量
           }
		   foreach($trade_info as $v){
			   $is_exist = 0;
			   foreach($order_info_detail as $val){
				   if($v['spec_id'] != $val['spec_id']) continue;
                   $is_exist = 1;
                   //判断是全部分拣还是部分分拣
                   $new_num = (int)$v['actual_num']-(int)$spec[$v['spec_id']];
                   if($new_num>=0){
                       $spec[$v['spec_id']]=0;
                   }else{
                       $spec[$v['spec_id']]=abs($new_num);
                   }
                   if($new_num>0){
                       $new_num_spec = M('sales_trade_order')->field('rec_id as id,src_oid,spec_no,goods_name,api_goods_name,api_spec_name,spec_name,num,actual_num,price,share_price,order_price,discount,share_post,share_amount,remark,weight,'.$val['num'].' as left_num,'.$new_num.' as split_num')->where(array('trade_id'=>$order_info[0]['trade_id'],'spec_id'=>$v['spec_id'],'src_oid'=>$v['src_oid']))->find();
                       $new_order_info[] = $new_num_spec;
                   }
				   break;
			   }
			   if($is_exist == 0){
				    $new_num_spec = M('sales_trade_order')->field('rec_id as id,src_oid,spec_no,goods_name,api_goods_name,api_spec_name,spec_name,num,actual_num,price,share_price,order_price,discount,share_post,share_amount,remark,weight,0 as left_num,'.$v['actual_num'].' as split_num')->where(array('trade_id'=>$order_info[0]['trade_id'],'spec_id'=>$v['spec_id'],'src_oid'=>$v['src_oid']))->find();
					$new_order_info[] = $new_num_spec;
			   }
		   }
		   //拆分订单
		   $new_trade_id = D('Trade/TradeCheck')->splitTrade($order_info[0]['trade_id'],$new_order_info,$user_id,1);
		   //修改新订单状态
		   M('sales_trade')->where(array('trade_id'=>$new_trade_id))->save(array('trade_status'=>30));
		   $this->execute("CALL I_RESERVE_STOCK({$new_trade_id},3,{$stockout['warehouse_id']},{$stockout['warehouse_id']})");
            //判断配置
             if($instant_split_sorted_goods==1){
                 //更新未分拣的档口缺货明细信息
                 $update_data=array(
                     'trade_id'=>$new_trade_id,
                     'trade_status'=>1,
                 );
                 M('stalls_less_goods_detail')->where(array('trade_id'=>$order_info[0]['trade_id'],'sort_status'=>0))->save($update_data);
             }else{
                 //删除未分拣的档口缺货信息
		         M('stalls_less_goods_detail')->where(array('trade_id'=>$order_info[0]['trade_id'],'sort_status'=>0))->delete();
             }
            //订单分拣完成，修改状态
		   M('box_goods_detail')->where(array('trade_id'=>$order_info[0]['trade_id']))->save(array('sort_status'=>1));
		   M('sorting_wall_detail')->where(array('box_id'=>$id))->save(array('is_use'=>0,'stockout_id'=>0));
		   M('big_box_goods_map')->where(array('box_no'=>$box_no['box_no']))->delete();
		   
		   M('stalls_less_goods_detail')->where(array('trade_id'=>$order_info[0]['trade_id']))->save(array('sort_status'=>3));
		  //修改出库单货品信息
			D('Stock/StockoutOrderDetail')->where(array('stockout_id'=>$stockout['stockout_id']))->delete();
			$this->execute('insert into stockout_order_detail(stockout_id,src_order_type,src_order_detail_id,base_unit_id,unit_id,num,price,total_amount,cost_price,goods_name,goods_id,goods_no,spec_name,spec_id,spec_no,spec_code,weight,position_id,is_allow_zero_cost,remark) select '.$stockout['stockout_id'].' as stockout_id,1 as src_order_type,sto.rec_id as src_order_detail_id,gs.unit as base_unit_id,gs.aux_unit as unit_id,sto.num,sto.price,sto.num*sto.price as total_amount,sto.price as cost_price,sto.goods_name,sto.goods_id,sto.goods_no,sto.spec_name,sto.spec_id,sto.spec_no,sto.spec_code,sto.weight*sto.num as weight,cwp.position_id,sto.is_allow_zero_cost,sto.remark from sales_trade_order sto left join stock_spec_position cwp on cwp.warehouse_id='.$stockout['warehouse_id'].' and sto.spec_id = cwp.spec_id left join goods_spec gs on gs.spec_id = sto.spec_id where sto.trade_id = '.$order_info[0]['trade_id']);
			
			/*  $stockout_order_info = D('Stock/StockOutOrder')->alias('so')->field('sod.spec_id,sod.num,so.stockout_id,sod.price,sod.weight')->join('left join stockout_order_detail sod on so.stockout_id = sod.stockout_id')->where(array('so.src_order_id'=>$order_info[0]['trade_id']))->select();
			 foreach($stockout_order_info as $r){
				 $is_outexist = 0;
				 foreach($order_info_detail as $f ){
					 if($r['spec_id'] != $f['spec_id']) continue;
					 $is_outexist = 1;
					 D('Stock/StockoutOrderDetail')->where(array('stockout_id'=>$r['stockout_id'],'spec_id'=>$r['spec_id']))->save(array('num'=>$f['num'],'total_amount'=>$f['num']*$r['price'],'weight'=>($r['weight']/$r['num'])*$f['num']));
					 break;
				 }
				 if($is_outexist == 0){
					  D('Stock/StockoutOrderDetail')->where(array('stockout_id'=>$r['stockout_id'],'spec_id'=>$r['spec_id']))->delete();
				 }
			 } */
			 //修改出库单信息
			 $stockout_order = M('sales_trade')->field('goods_count,goods_type_count,single_spec_no,goods_amount as goods_total_cost,post_cost as calc_post_cost,post_amount as post_cost,weight as calc_weight,goods_amount as goods_total_amount')->where(array('trade_id'=>$order_info[0]['trade_id']))->find();
			 D('Stock/StockOutOrder')->where(array('stockout_id'=>$stockout['stockout_id']))->save($stockout_order);
			 $stockout_id = $stockout['stockout_id'];
             //判断配置是否开启
             if($instant_split_sorted_goods==1){
                 //将拆分出来的订单审核过去,保留原唯一码
                 $finacial_trade = array();$stockout_orders = array();$stockout_sync = array();
                 $list = array();$success = array();
                 $remain_info['old_order']=$new_trade_id;
                 $sql_where=D('Trade/TradeCheck')->fetchSql(true)->alias('st')->field('st.trade_id')->where(array('st.trade_id'=>array('eq',$new_trade_id)))->select();
                 $sql=D('Trade/TradeCheck')->alias('st')->field('st.trade_id')->where(array('st.trade_id'=>array('eq',$new_trade_id)))->select();
                 D('Trade/TradeCheck')->checkTradeNoTrans($sql_where,0,$user_id,0,0,$remain_info,$list,$success,$finacial_trade,$stockout_orders,$stockout_sync);
                 //分配分拣框
                 $trade=D('Trade/TradeCheck')->getSalesTrade(
                     'st.trade_id,st.trade_no,st.stockout_no,so.stockout_id',
                     array('st.trade_id'=>array('eq',$new_trade_id)),
                     'st',
                     'LEFT JOIN stockout_order so ON so.stockout_no=st.stockout_no'
                 );
                 $trade_num=D('SalesTrade')->field('goods_count')->where(array('trade_id'=>array('eq',$new_trade_id)))->find();
                 $box_no=D('Trade/TradeCheck')->allotSortingWall($trade['trade_id'],$trade['trade_no'],$trade_num['goods_count']);
                 $this->execute("UPDATE sorting_wall_detail SET stockout_id=".$trade['stockout_id'].",is_use=1  WHERE box_no='".$box_no."'");
                 $this->execute("UPDATE stockout_order SET box_no='".$box_no."' where stockout_id='".$trade['stockout_id']."'");
             }
			 $this->commit();
		}catch(BusinessLogicException $e){
			$this->rollback();
	        $msg = $e->getMessage();
	        $fail[] = array(
	            'box_id' => $id,
	            'box_no' => $box_no['box_no'],
	            'msg'      => $msg,
	        );
	        return false;
	    }catch(\PDOException $e){
			$this->rollback();
	        $msg = $e->getMessage();
	        $fail[] = array(
	            'box_id' => $id,
	            'box_no' => $box_no['box_no'],
	            'msg'      => self::PDO_ERROR,
	        );
	        \Think\Log::write($this->name.'-oneSplit-'.$msg);

	        return false;
	    }catch(\Exception $e){
			$this->rollback();
	        $msg = $e->getMessage();
	        $fail[] = array(
	            'box_id' => $id,
	            'box_no' => $box_no['box_no'],
	            'msg' =>self::PDO_ERROR,
	        );
	        \Think\Log::write($this->name.'-oneSplit-'.$msg);
	        return false;
	    }
		return $stockout_id;
	}
	public function putBox($box_no,$trade_no,$trade_id,$spec_id,$box_goods_detail_model,$goods_num = 1){
		try{
			// 货品放入格子
			$spec_in_one_order = array(
				'trade_no'=>$trade_no,
				'spec_id'=>$spec_id
			);
			$box_goods_detail_model->where(array('box_no'=>$box_no,'sort_status'=>1))->delete();
			$orderSpec =  $box_goods_detail_model->field('num,sort_status')->where($spec_in_one_order)->select();
			if(empty($orderSpec)){
				$goods = array('box_no'=>$box_no,'spec_id'=>$spec_id,'trade_id'=>$trade_id,'trade_no'=>$trade_no,'sort_status'=>0,'num'=>$goods_num);
				$box_goods_detail_model->add($goods);
			}else{
				$box_goods_detail_model->where($spec_in_one_order)->save(array('num'=>$orderSpec[0]['num']+$goods_num));
			}
		}catch(\PDOException $e){
            \Think\Log::write($e->getMessage());
            SE(self::PDO_ERROR);
        }catch(BusinessLogicException $e){
            \Think\Log::write($e->getMessage(),\Think\Log::WARN);
            SE($e->getMessage());
        }catch(\Exception $e){
            \Think\Log::write($e->getMessage());
            SE(self::PDO_ERROR);
        }
		return true;
	}
	public function checkPick($is_stalls,$trade_no,$box_no,$trade_id,$box_goods_detail_model,$sorting_wall_detail_model,&$sortData,&$pick_status){
		try{
			//判断非档口货品是否分拣完成
            $goodsNumInBox = $box_goods_detail_model->field('sum(num) as goods_num')->where(array('trade_no'=>$trade_no))->find();
            $goodsNumInOrder = D('Stock/StockOutOrder')->alias('so')->field('sum(sod.num) as goods_num')->join('left join stockout_order_detail sod on sod.stockout_id = so.stockout_id')->where(array('so.src_order_no'=>$trade_no))->find();
            $goodsNumInBox = intval($goodsNumInBox['goods_num']);
            $goodsNumInOrder = intval($goodsNumInOrder['goods_num']);
		   if($goodsNumInBox ==$goodsNumInOrder){
			   $pick_status = 1;
			   $this->orderPickOver($is_stalls,$box_goods_detail_model,$sorting_wall_detail_model,$box_no,$trade_id,$sortData);
        
            }else if($goodsNumInBox < $goodsNumInOrder){
                $sortData['sort_finish'] = '';
                $sortData['box_no'] = $box_no;

            }else{
                SE('格子中的货品超出订单货品数量，请联系管理员');
            }
		}catch(\PDOException $e){
            \Think\Log::write($e->getMessage());
            SE(self::PDO_ERROR);
        }catch(BusinessLogicException $e){
            \Think\Log::write($e->getMessage(),\Think\Log::WARN);
            SE($e->getMessage());
        }catch(\Exception $e){
            \Think\Log::write($e->getMessage());
            SE(self::PDO_ERROR);
        }
		return true;
	}
	public function orderPickOver($is_stalls,$box_goods_detail_model,$sorting_wall_detail_model,$box_no,$trade_id,&$sortData){
		try{
			//档口货品详情设置分拣订单分拣完成
			if($is_stalls){
				 $this->where(array("trade_id"=>$trade_id))->save(array("sort_status"=>3));
			}

            $sys_config =  get_config_value(array('dynamic_allocation_box','dynamic_allocation_box_value'),array(0,20));
//            if($sys_config['dynamic_allocation_box']==1){
//                $big_box_goods_map_model = M('big_box_goods_map');
//                $box_goods_detail_model->where(array("trade_id"=>$trade_id))->save(array("sort_status"=>1));
//                $big_box_no = $big_box_goods_map_model->field('big_box_no')->where(array("trade_id"=>$trade_id))->find();
//                $big_box_no = substr($big_box_no['big_box_no'],0,strrpos($big_box_no['big_box_no'],'-')+1);
//                $big_box_goods_map_model->where(array("trade_id"=>$trade_id))->delete();
//                $box_count = $big_box_goods_map_model->where(array("big_box_no"=>array('like',$big_box_no.'%')))->count();
//                if($box_count==0){
//                    $sorting_wall_detail_model->where(array("box_no"=>$box_no))->save(array("is_use"=>0,"stockout_id"=>0));
//                }
//            }else {
//                // sort_status = 1: 该订单货品不再占用该格子
//                $box_goods_detail_model->where(array("trade_id" => $trade_id))->save(array("sort_status" => 1));
//                // 释放格子
//                $sorting_wall_detail_model->where(array("box_no" => $box_no))->save(array("is_use" => 0, "stockout_id" => 0));
//            }

            // sort_status = 1: 该订单货品不再占用该格子
            $box_goods_detail_model->where(array("trade_id" => $trade_id))->save(array("sort_status" => 1));
			//$box_goods_detail_model->where(array("trade_id" => $trade_id))->delete(); //删除分拣框数据
            // 释放格子
            $sorting_wall_detail_model->where(array("box_no" => $box_no))->save(array("is_use" => 0, "stockout_id" => 0));
            if($sys_config['dynamic_allocation_box']==1){
                $big_box_goods_map_model = M('big_box_goods_map');
//                $big_box_no = $big_box_goods_map_model->field('big_box_no')->where(array("trade_id"=>$trade_id))->find();
                $big_box_goods_map_model->where(array("trade_id"=>$trade_id))->delete();
//                $box_no = $big_box_no['big_box_no'];
            }
			$sortData['sort_finish'] = '订单分拣完成';
			$sortData['box_no'] = $box_no;
			// 插入订单日志
			$message = '订单分拣完成';
			$this->insertToOrderLog($trade_id,$message);
		}catch(\PDOException $e){
            \Think\Log::write($e->getMessage());
            SE(self::PDO_ERROR);
        }catch(BusinessLogicException $e){
            \Think\Log::write($e->getMessage(),\Think\Log::WARN);
            SE($e->getMessage());
        }catch(\Exception $e){
            \Think\Log::write($e->getMessage());
            SE(self::PDO_ERROR);
        }
		return true;
	}
	 //换货
    public function exchangeOrder($order_id,$orders,$user_id){
        $result = array('status'=>1,'info'=>'');
        $sql_error_info = '';
        $list=array();
        $suite_id=array();
        try{
            //调用订单的换货
            $sto_db=D('Trade/SalesTradeOrder');
            $order = $sto_db->getSalesTradeOrderList(
                'trade_id,shop_id,platform_id,src_tid,src_oid,bind_oid,guarantee_mode,gift_type,invoice_type,suite_num,suite_amount,suite_discount,
				invoice_content,share_amount,discount,share_post,paid,tax_rate,is_master,actual_num,refund_status,
				is_print_suite,from_mask,flag,spec_id,suite_id,spec_no,spec_name,suite_name,goods_name,goods_no',
                array('rec_id'=>array('in',$order_id))
            );
            $trade = D('Trade/Trade')->getSalesTrade('st.trade_no,st.trade_status,st.freeze_reason,st.checkouter_id,st.warehouse_id,st.delivery_term,st.version_id,st.stockout_no,so.stockout_id',
                            array('trade_id'=>array('eq',$order[0]['trade_id'])),
                            'st',
                            'LEFT JOIN stockout_order so ON so.stockout_no=st.stockout_no');

            if (empty($order)){
                $list[]=array('trade_no'=>$trade['trade_no'],'result_info'=>'无效子订单');
            }
            foreach ($order as $o){
                if ($o['refund_status']>1){
                    $list[]=array('trade_no'=>$trade['trade_no'],'result_info'=>'不能替换退款货品');
                }
                if ($o['suite_id']!=0) {
                    $suite_id[]=$o['suite_id'];
                }
            }
            if (empty($trade))
            {
                $list[]=array('trade_no'=>$trade['trade_no'],'result_info'=>'订单不存在');
            }
            if ($trade['freeze_reason']>0)
            {
                $list[]=array('trade_no'=>$trade['trade_no'],'result_info'=>'订单已冻结');
            }
            if(empty($list)) {
                $this->startTrans();
                //订单换货
                D('Trade/SalesTrade')->exchangeOrderNoTrans($order_id, $orders, $order, $trade, $suite_id, $user_id);
                //出库单换货==删掉原来的==插入新的数据
                //--------------------删除原货品-----------------------
                $sql_error_info='exchangeOrder-delete-stockout_order_detail';
                 $this->execute('DELETE FROM stockout_order_detail WHERE stockout_id='.$trade['stockout_id']);

                //--------------------添加新货品-----------------------
                $sales_order_db=D('Trade/SalesTradeOrder');
                $trade_orders=$sto_db->getSalesTradeOrderList(
                    $trade['stockout_id'].' AS stockout_id, 1 src_order_type,if(ssp.position_id is NULL,-'.$trade['warehouse_id'].',ssp.position_id) position_id,so.rec_id AS src_order_detail_id,so.actual_num AS num,so.share_price AS price,so.share_amount AS total_amount,
				so.goods_name,so.goods_id,so.goods_no, so.spec_name,so.spec_id,so.spec_no,so.spec_code,so.weight,so.is_allow_zero_cost,so.remark,NOW() created',
                    array('so.trade_id'=>array('eq',$order[0]['trade_id']),'so.actual_num'=>array('gt',0)),'so','left join stock_spec_position ssp on ssp.spec_id = so.spec_id and ssp.warehouse_id = '.$trade['warehouse_id']
                );
                $sql_error_info='exchangeOrder-sales_trade_order_add-get_stockout_order_detail';
                M('stockout_order_detail')->addAll($trade_orders);
                //缺货明细更新===删掉原来的缺货明细===增加新的
                foreach($order as $d){
                    $sql_error_info='exchangeOrder-get-delete-unique_code';
                    $unique_code=$this->query('SELECT unique_code FROM stalls_less_goods_detail WHERE trade_id='.$order[0]['trade_id'].' AND spec_id='.$d['spec_id']);
                    $box=M('box_goods_detail')->field('num')->where('trade_id='.$order[0]['trade_id'].' AND spec_id='.$d['spec_id'])->select();
                    if($box[0]['num']==$d['actual_num']){
                        $this->execute('DELETE FROM box_goods_detail WHERE trade_id='.$order[0]['trade_id'].' AND spec_id='.$d['spec_id']);
                    }
                    $sql_error_info='exchangeOrder-delete-stalls_less_goods_detail';
                    foreach($unique_code as $k=>$u) {//intval($d['actual_num']
                        if($k<=intval($d['actual_num']-1)){
                            $this->execute("DELETE FROM stalls_less_goods_detail WHERE unique_code ='".$u['unique_code']."'");
                        }
                    }
                }
                //判断新货品是否有库存，没有则生成缺货明细
                foreach($orders as $o){
                    $sql_error_info='exchangeOrder-get';
                    $stock_info=$this->query('SELECT IFNULL(cost_price,0) cost_price,(stock_num-sending_num) as available_stock FROM stock_spec WHERE spec_id='.$o['spec_id'].' AND warehouse_id='.$trade['warehouse_id']);
                    $odd_num=$stock_info[0]['available_stock']-$o['num'];
                    if($odd_num>=0){//不缺货==档口单是否已分拣完成
                        continue;
                    }
                    $less_num=abs($odd_num)>o['num']?$o['num']:abs($odd_num);
                    //查找对应的供应商
                    $sql_error_info='exchangeOrder-get_provider_id';
                    $provider_id=M('purchase_provider_goods')->field('provider_id')->where('spec_id='.$o['spec_id'].' and is_disabled=0')->order('price')->find();
                    $sto=array(
                        'spec_id'=>$o['spec_id'],
                        'trade_id'=>$order[0]['trade_id'],
                        'trade_no'=>$trade['trade_no'],
                        'warehouse_id'=>$trade['warehouse_id'],
                        'provider_id'=>empty($provider_id)?0:$provider_id['provider_id'],
                        'block_reason'=>0,
                        'cost_price'=>$stock_info[0]['cost_price']
                    );
                    //判断缺货明细中是否已经存在新货品==有，则加数，无则新生成
                    $slgd_unique_code=M('stalls_less_goods_detail')->field('unique_code')->where('trade_id='.$order[0]['trade_id'].' AND spec_id='.$o['spec_id'])->select();
                    if(empty($slgd_unique_code)){
                        $sql_error_info='exchangeOrder-addStallsLessGoodsDetail-no-exist';
                        D('Trade/Trade')->addStallsLessGoodsDetail($sto,$less_num,0);
                    }else{
                        $sql_error_info='exchangeOrder-addStallsLessGoodsDetail-exist';
                        foreach($slgd_unique_code as $u){
                            $arr=explode('-', $u['unique_code']);
                            $num=intval($arr[2]);
                            D('Trade/Trade')->addStallsLessGoodsDetail($sto,$less_num,$num);//
                        }
                    }

                }
                //订单是否为档口单==查找有没有对应的缺货明细，有则为档口单，没有则不是
                $sql_error_info='exchangeOrder-get_count_stalls_less_goods_detail';
                $count=$this->execute("SELECT count(1) as num FROM stalls_less_goods_detail WHERE trade_id=%d",$order[0]['trade_id']);
                if($count['num']>0){
                    $sql_error_info='exchangeOrder-update-sales_trade_is_stalls';
                    $this->execute('UPDATE sales_trade SET is_stalls=1 where trade_id=%d',$order[0]['trade_id']);
                    $sql_error_info='exchangeOrder-update-stockout_order_is_stalls';
                    $this->execute('UPDATE stockout_order SET is_stalls=1 where stockout_id=%d',$trade['stockout_id']);
                }
                $this->commit();
                $rows = $sto_db->getSalesTradeOrderList(
                    "sto.rec_id AS id,sto.rec_id AS sto_id,sto.trade_id,sto.spec_id,sto.platform_id,sto.src_oid,sto.suite_id,
						 sto.num,sto.price,sto.actual_num,sto.order_price,sto.share_price, sto.share_amount,
						 sto.share_price,sto.discount,sto.share_post,sto.paid,sto.goods_name,sto.goods_id,
						 sto.goods_no,sto.spec_name, sto.spec_no,sto.spec_code,sto.gift_type,sto.suite_name,
						 IF(sto.suite_num,sto.suite_num,'') suite_num,sto.suite_no, sto.weight,ss.stock_num,
						 0 AS is_suite,sto.remark,IF(sto.gift_type,1,IF(sto.platform_id,2,3)) edit,sto.refund_status,
						 gs.img_url,gg.spec_count",
                    array('sto.trade_id' => array('eq', $order[0]['trade_id'])),
                    'sto',
                    "LEFT JOIN goods_spec gs on gs.spec_id = sto.spec_id LEFT JOIN stock_spec ss ON ss.warehouse_id=" . intval($trade['warehouse_id']) . " AND ss.spec_id=sto.spec_id LEFT JOIN goods_goods gg ON gg.goods_id=gs.goods_id",
                    'refund_status,id ASC'
                );
                $result['data'] = array('total' => count($rows), 'rows' => $rows);
                $result['trade'] = D('Trade/Trade')->getSalesTrade('goods_amount,discount,receivable,version_id', array('trade_id' => array('eq', $order[0]['trade_id'])));
            }
        }catch(\PDOException $e)
        {
            $this->rollback();
            \Think\Log::write($this->name.'-'.$sql_error_info.':'.$e->getMessage());
            SE(self::PDO_ERROR);
        }catch(BusinessLogicException $e)
        {
            $this->rollback();
            SE($e->getMessage());
        }catch(\Exception $e){
            $this->rollback();
            SE($e->getMessage());
        }
        $result['status']=empty($list)?0:1;
		$result['info']=$list;
		return $result;
    }
    //分拣时换货
    public function pickExchangeOrder($id,$old_orders,$new_orders,$user_id){
        $result = array('status'=>1,'info'=>'');
        $sql_error_info = '';
        $list=array();
        $suite_id=array();
        try{
            $this->startTrans();
            //先清除异常（缺货明细、出库单）
            M('stalls_less_goods_detail')->execute('UPDATE stalls_less_goods_detail SET block_reason=0 WHERE trade_id='.$id);
            M('stockout_order')->execute('UPDATE stockout_order SET block_reason=0 WHERE src_order_type=1 and src_order_id='.$id);
            //解除订单和爆款单之间的关系
            D('Trade/SalesTrade')->where(array('trade_id'=>$id))->save(array('stalls_id'=>0));
            //按订单号筛选出对应子订单
            $sql_error_info='getSalesTradeOrderList';
            $res_orders_arr=D('Trade/SalesTradeOrder')->getSalesTradeOrderList('sto.rec_id,sto.trade_id,sto.spec_id,sto.actual_num,st.trade_no',
                array('sto.trade_id'=>array('eq',$id)),
                'sto',
                ' LEFT JOIN sales_trade st ON st.trade_id=sto.trade_id ');
            $exchange_ids=array();
            foreach($res_orders_arr as $k=>$v){
                foreach ($old_orders as $old){
                    if($v['spec_id']==$old['spec_id']&&$v['actual_num']==$old['num']){
                        if(!in_array($v['rec_id'],$exchange_ids)){
                            $exchange_ids[]=$v['rec_id'];
                        }
                    }
                }
            }
            //再处理换货
            if(count($exchange_ids)!=count($old_orders)){
               SE('订单中不包含符合条件的货品');
            }else{
                $result=D('Purchase/StallsOrderDetail')->exchangeOrder($exchange_ids,$new_orders,get_operator_id());
            }
            if($result[status]!=0){
                SE($result['info'][0]['result_info']);
            }
            $this->execute('UPDATE api_trade_order ato LEFT JOIN sales_trade_order sto ON sto.src_tid=ato.tid AND sto.src_oid=ato.oid  SET ato.other_flags=0 WHERE sto.trade_id='.$id);
            $this->commit();
        }catch(\PDOException $e)
        {
            $this->rollback();
            \Think\Log::write($this->name.'-'.$sql_error_info.':'.$e->getMessage());
            SE(self::PDO_ERROR);
        }catch(BusinessLogicException $e)
        {
            $this->rollback();
            SE($e->getMessage());
        }catch(\Exception $e){
            $this->rollback();
            SE($e->getMessage());
        }
        return $result;
    }
	public function showTradeOrderDetail($id, $page = 1, $rows = 20){
		try{
			$rows=intval($rows);
			$page=intval($page);
			$page_info = I('','',C('JSON_FILTER'));
			if(isset($page_info['page']))
			{
				$rows = intval($page_info['rows']);
				$page = intval($page_info['page']);
			}
			$limit = ($page - 1)*$rows;
			$cfg_show_telno = get_config_value('show_number_to_star', 1);
			$sql            = "SELECT DISTINCT st.trade_id,st.trade_no,st.platform_id,cs.shop_name,st.warehouse_id,cw.name,st.warehouse_type,
							st.src_tids,st.trade_status,st.trade_from,st.trade_type,st.delivery_term,st.freeze_reason,st.refund_status,st.unmerge_mask,
							st.fenxiao_type,st.fenxiao_nick,st.trade_time,st.pay_time,st.goods_count,st.goods_type_count,st.customer_id,
							st.buyer_nick,st.receiver_name,st.receiver_country,st.receiver_province,st.receiver_city,st.receiver_district,
							st.receiver_address,IF(" . $cfg_show_telno . "=0,st.receiver_mobile,INSERT( st.receiver_mobile,4,4,'****')) receiver_mobile,IF(" . $cfg_show_telno . "=0,st.receiver_telno,INSERT(st.receiver_telno,4,4,'****')) receiver_telno,
							st.receiver_zip,st.receiver_area,st.receiver_ring,st.receiver_dtb,st.to_deliver_time,cl.logistics_name,st.buyer_message,st.cs_remark,
							st.remark_flag,st.print_remark,st.note_count,st.buyer_message_count,st.cs_remark_count,st.goods_amount,
							st.post_amount,st.other_amount,st.discount,st.receivable,st.discount_change,st.dap_amount,st.cod_amount,
							st.pi_amount,st.goods_cost,st.post_cost,st.paid,st.weight,st.invoice_type,st.invoice_title,
							st.invoice_content,st.salesman_id,st.checker_id,st.fchecker_id,st.checkouter_id,st.flag_id,st.delivery_term,he.fullname,
							st.bad_reason,st.is_sealed,st.split_from_trade_id,st.stockout_no,st.version_id,st.modified,st.created,sto.src_tid,cor.title
							FROM sales_trade st
							INNER JOIN sales_trade_order sto ON(sto.trade_id=st.trade_id)
							INNER JOIN stalls_less_goods_detail slgd on slgd.trade_id = st.trade_id
							LEFT JOIN cfg_shop cs ON(st.shop_id=cs.shop_id)
							LEFT JOIN cfg_warehouse cw ON(cw.warehouse_id=st.warehouse_id)
							LEFT JOIN cfg_oper_reason cor ON(cor.reason_id=st.freeze_reason)
							LEFT JOIN hr_employee he ON(he.employee_id=st.salesman_id)
							LEFT JOIN cfg_logistics cl ON(cl.logistics_id=st.logistics_id) where slgd.stalls_id = $id GROUP BY st.trade_id limit $limit,$rows";
			$sql_count      = "SELECT DISTINCT st.trade_id
							FROM stalls_less_goods_detail st
							where st.stalls_id = $id group by st.trade_id";
			$data["rows"]  = $this->query($sql);
			$result        = $this->query($sql_count);
			$data["total"] = count($result);
		}catch(\PDOException $e){
			\Think\Log::write($e->getMessage());
			SE(self::PDO_ERROR);
		}catch(BusinessLogicException $e){
			\Think\Log::write($e->getMessage());
			SE($e->getMessage);
		}catch(\Exception $e){
			\Think\Log::write($e->getMessage());
			SE(self::PDO_ERROR);
		}
		return $data;
	}
}

?>