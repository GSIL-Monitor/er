<?php
namespace Trade\Model;
use Think\Model;
use Think\Exception;
use Think\Exception\BusinessLogicException;
use Common\Common\ExcelTool;

use Think\Log;

class CashSalesTradeModel extends TradeModel{
    // 保存
    public function addCashSalesTrade($arr_form_data,$arr_orders_data,$user_id){
        $is_rollback=false;
		$sql_error_info='';
        try{
			//-----------数据整理--系统订单--------------------
			$arr_form_data['trade_type']=set_default_value($arr_form_data['trade_type'],2);
			$arr_form_data['platform_id']=0;
			$arr_form_data['trade_status']=30;//订单状态,30待客审 110已完成
			$arr_form_data['trade_time']=array('exp','NOW()');
			$arr_form_data['pay_time']=$arr_form_data['trade_time'];
			$arr_form_data['trade_from']=4;
			$arr_form_data['logistics_id']=set_default_value($arr_form_data['logistics_id'], 0);
			$arr_form_data['warehouse_id']=set_default_value($arr_form_data['warehouse_id'], 0);
			$arr_form_data['salesman_id']=set_default_value($arr_form_data['salesman_id'], 0);
			$arr_form_data['cs_remark']=set_default_value($arr_form_data['cs_remark'], '');
			$arr_form_data['invoice_type']=set_default_value($arr_form_data['invoice_type'], 0);
			$arr_form_data['delivery_term']=1;
			$arr_form_data['flag_id']=set_default_value($arr_form_data['flag_id'], 43);
			if(!$arr_form_data['flag']){
				$arr_form_data['buyer_nick']=set_default_value($arr_form_data['buyer_nick'], '无');
				$arr_form_data['receiver_name']=set_default_value($arr_form_data['receiver_name'], '无');
				$arr_form_data['receiver_mobile']=set_default_value($arr_form_data['receiver_mobile'], '');
				$arr_form_data['receiver_telno']=set_default_value($arr_form_data['receiver_telno'], '');
				$arr_form_data['buyer_message']=set_default_value($arr_form_data['buyer_message'], '无');
				$arr_form_data['receiver_zip']=set_default_value($arr_form_data['receiver_zip'], '无');
				$arr_form_data['receiver_province']=set_default_value($arr_form_data['receiver_province'], '0');
				$arr_form_data['receiver_city']=set_default_value($arr_form_data['receiver_city'], '0');
				$arr_form_data['receiver_district']=set_default_value($arr_form_data['receiver_district'], '0');
				$arr_form_data['receiver_address']=set_default_value($arr_form_data['receiver_address'], '无');
				$arr_form_data['receiver_area']=set_default_value($arr_form_data['receiver_area'], '无');
			}
			unset($arr_form_data['remission']);
			
        //-----------数据整理--原始订单--------------------
			$arr_api_trade=$arr_form_data;
			$arr_api_trade['tid']=set_default_value($arr_api_trade['src_tids'], '');
			$arr_api_trade['order_count']=$arr_form_data['goods_type_count'];
			$arr_api_trade['is_preorder']=0;
			$arr_api_trade['trade_status']=10;//订单平台的状态,10未确认 70已完成
			$arr_api_trade['guarantee_mode']=2;
			$arr_api_trade['process_status']=20;
			$arr_api_trade['received']=$arr_form_data['paid'];
			$arr_api_trade['buyer_area']=isset($arr_form_data['receiver_area'])?$arr_form_data['receiver_area']:'';
			$arr_api_trade['receiver_hash']=md5($arr_form_data['receiver_province'].' '.$arr_form_data['receiver_city'].' '.$arr_form_data['receiver_district']);
			$arr_api_trade['pay_method']=set_default_value($arr_form_data['pay_method'], 2);
			$arr_api_trade['invoice_type']=$arr_form_data['invoice_type'];
			$arr_api_trade['buyer_name']=$arr_form_data['receiver_name'];
			$arr_api_trade['dap_amount']=$arr_form_data['paid'];
			$arr_api_trade['pi_amount']=$arr_form_data['delivery_term']==4?$arr_form_data['receivable']-$arr_form_data['paid']:0;
			$arr_api_trade['x_logistics_id']=$arr_form_data['logistics_id'];
			$arr_api_trade['x_warehouse_id']=$arr_form_data['warehouse_id'];
			$arr_api_trade['x_salesman_id']=$arr_form_data['salesman_id'];
			$arr_api_trade['x_trade_flag']=$arr_form_data['flag_id'];
			$arr_api_trade['x_customer_id']=set_default_value($arr_api_trade['customer_id'], 0);
			$arr_api_trade['remark']=$arr_form_data['cs_remark'];
			$arr_api_trade['post_amount']=set_default_value($arr_api_trade['post_amount'], 0);
			unset($arr_api_trade['logistics_id']);
			unset($arr_api_trade['warehouse_id']);
			unset($arr_api_trade['salesman_id']);
			unset($arr_api_trade['customer_id']);
			unset($arr_api_trade['flag_id']);
			unset($arr_api_trade['goods_type_count']);
			unset($arr_api_trade['trade_type']);
			unset($arr_api_trade['src_tids']);
			unset($arr_api_trade['receiver_dtb']);
			unset($arr_api_trade['weight']);
            unset($arr_api_trade['cs_remark']);
            unset($arr_api_trade['flag']);
            
            //-----------数据整理--原始子订单--------------------
			$arr_api_orders=array();
			$goods_count=0;
			$goods_count_round=0;
			for ($i=0;$i<$arr_api_trade['order_count'];$i++)
			{
				$goods_count+=$arr_orders_data[$i]['num'];
				$goods_count_round+=round($arr_orders_data[$i]['num']);
				$arr_api_orders[]=array(
						'platform_id'=>0,
						'shop_id'=>$arr_form_data['shop_id'],
						//'tid'=>$arr_api_trade['tid'],
						//'oid'=>get_sys_no('apiorder',1),
						'status'=>30,//平台的状态,30待发货 70已完成
						'process_status'=>10,//处理状态,10待递交 60已完成
						'is_invalid_goods'=>0,
						'goods_id'=>set_default_value($arr_orders_data[$i]['goods_id'],''),
						'goods_no'=>set_default_value($arr_orders_data[$i]['goods_no'],''),
						'goods_name'=>set_default_value($arr_orders_data[$i]['goods_name'],''),
						'spec_id'=>set_default_value($arr_orders_data[$i]['spec_id'],''),
						'spec_no'=>set_default_value($arr_orders_data[$i]['spec_no'],''),
						'spec_name'=>set_default_value($arr_orders_data[$i]['spec_name'],''),
						'spec_code'=>set_default_value($arr_orders_data[$i]['spec_code'],''),
						'num'=>$arr_orders_data[$i]['num'],
						'price'=>$arr_orders_data[$i]['original_price'],
						'discount'=>($arr_orders_data[$i]['original_price']-$arr_orders_data[$i]['real_price'])*$arr_orders_data[$i]['num'],//$arr_orders_data[$i]['discount'],
						'share_amount'=>$arr_orders_data[$i]['real_price']*$arr_orders_data[$i]['num'],
						'total_amount'=>$arr_orders_data[$i]['total_price'],
						'gift_type'=>set_default_value($arr_orders_data[$i]['gift_type'], 0),
						'remark'=>set_default_value($arr_orders_data[$i]['cs_remark'],''),
						//'share_post'=>$arr_orders_data[$i]['share_post'],
						//'paid'=>$arr_orders_data[$i]['paid'],
						'created'=>array('exp','NOW()'),
				);
			}
			$arr_api_trade['goods_count']=$goods_count;
			$arr_form_data['goods_count']=$goods_count;
			$arr_form_data['goods_count_round']=$goods_count_round;
			//开启事务
			//创建临时表-调用货品映射前需要创建临时表
			$this->execute('CALL I_DL_TMP_SALES_TRADE_ORDER()');
			$this->execute('CALL I_DL_TMP_SUITE_SPEC()');
			$is_rollback=true;
            $this->startTrans();
            if($arr_form_data['flag']){
                //-----------整理--添加--客户信息-客户存在----------------------
                if ($arr_form_data['customer_id']<=0)
                {
                    $arr_form_data['customer_id']=D('Customer/Customer')->addCustomerByTrade($arr_form_data);
                }else{
                    $sql_error_info="manule_trade-update_customer_trade";
                    $this->execute("UPDATE crm_customer SET trade_count=trade_count+1, trade_amount=trade_amount+".$arr_form_data['receivable']." WHERE customer_id=%d",$arr_form_data['customer_id']);
                }
                //-----------整理--添加--客户地址----------------------
                D('Customer/CustomerAddress')->addAddressByTrade($arr_form_data);
                //-----------整理--添加--固话和手机----------------------
                D('Customer/CustomerTelno')->addTelnoByTrade($arr_form_data);
            }
            unset($arr_form_data['flag']);
            //-----------添加原始单->原始子订单->订单->子订单----------------------
			$arr_form_data['add_trade_type']=3;
			$api_trade_id=$this->addTrade($arr_form_data, $arr_api_trade, $arr_api_orders, $user_id);
			$trade_id=$this->query("SELECT sto.trade_id FROM sales_trade_order sto LEFT JOIN api_trade at ON sto.src_tid=at.tid WhERE at.rec_id=$api_trade_id AND at.shop_id=$arr_form_data[shop_id]");
			// 生成出库单
			$stock_out=$this->generateStockOut($user_id, $trade_id[0]['trade_id'], $arr_form_data['warehouse_id'], 55);
			// 修改出库单状态,更新物流单号
			$update=$this->execute("UPDATE sales_trade SET trade_status=55,checker_id=".$user_id.",stockout_no='".$stock_out['stockout_no']."' WHERE trade_id =".$trade_id[0]['trade_id']);
			// 发货
			$fail=array();
			$success[$stock_out['stockout_id']] = array();
			$is_force=0;
			D('Stock/SalesStockOut')->consignStockoutOrder($stock_out['stockout_id'],$fail,$success,1);
			$this->commit();
        }catch (\PDOException $e){
			if($is_rollback)
			{
				$this->rollback();
			}
			\Think\Log::write($this->name.'-'.$sql_error_info.':'.$e->getMessage());
			SE(self::PDO_ERROR);
		}catch (BusinessLogicException $e){
			if($is_rollback)
			{
				$this->rollback();
			}
			SE($e->getMessage());
		}

    }
}
    