<?php
/**
 * Created by PhpStorm.
 * User: ct
 * Date: 2016/8/15
 * Time: 14:12
 */

namespace Stock\Model;


use Think\Model;
use Think\Exception\BusinessLogicException;

class StockinAdjustModel extends Model
{
    protected $tableName = 'stockin_adjust_order';
    protected $pk        = 'rec_id';

    public function checkAdjustOrder($id)
    {
        try{
            $operator_id = get_operator_id();
            $adjust_order = $this->field('status, adjust_no, warehouse_id, ignore_voucher, src_order_type, src_order_id, src_order_no')->where(array('rec_id'=>$id))->find();
            if(empty($adjust_order))
            {
                SE('调价单不存在!');
            }
            if($adjust_order['status']<>1)
            {
                SE('调价单状态不正确!');
            }
            $adjust_order_detail = M('stockin_adjust_order_detail')->alias('saod')->field(array('saod.spec_id','saod.num','saod.adjust_price','ss.rec_id'))->join('left join stock_spec ss on ss.warehouse_id='.$adjust_order['warehouse_id'].' and ss.spec_id = saod.spec_id')->where(array('saod.stockin_adjust_id'=>$id))->select();

            foreach($adjust_order_detail as $detail)
            {
                //-----查询当前库存信息
                $stock_spec_info = D('Stock/StockSpec')->field('spec_id, warehouse_id, stock_num, cost_price')->where(array('rec_id'=>$detail['rec_id']))->find();
                $stock_amount = $stock_spec_info['cost_price']*$stock_spec_info['stock_num'];
                $adjust_amount = $detail['adjust_price']*$detail['num'];

                if($stock_spec_info['stock_num'] > 0)
                {
                    if( $adjust_amount >= 0 )
                    {
                        $adjust_cost_price = ($stock_amount+$adjust_amount)/$stock_spec_info['stock_num'];
                    }else{
                        if( $stock_amount+$adjust_amount >= 0 )
                        {
                            $adjust_cost_price = ($stock_amount+$adjust_amount)/$stock_spec_info['stock_num'];
                        }else{
                            $adjust_cost_price = 0;
                        }
                    }
                }else if($stock_spec_info['stock_num'] == 0)
                {
                    if($adjust_amount==0)
                    {
                        $adjust_cost_price = $stock_spec_info['cost_price']+$detail['adjust_price'];
                    }else{
                        $adjust_cost_price = $stock_spec_info['cost_price'];
                    }
                }else{
                    if($adjust_amount <= 0)
                    {
                        $adjust_cost_price = ($adjust_amount+$stock_amount)/$stock_spec_info['stock_num'];
                    }else{
                        if($adjust_amount+$stock_amount<=0)
                        {
                            $adjust_cost_price = ($adjust_amount+$stock_amount)/$stock_spec_info['stock_num'];
                        }else{
                            $adjust_cost_price = 0;
                        }
                    }
                }
                //-----更新成本价
                $res_update_cost = D('Stock/StockSpec')->save(array('rec_id'=>$detail['rec_id'],'cost_price'=>$adjust_cost_price));
                //----单品库存变化记录
               /* $history_data = array(
                    'src_order_type'    => $adjust_order['src_order_type'],
                    'src_order_id'      => $adjust_order['src_order_id'],
                    'src_order_no'      => $adjust_order['src_order_no'],
                    'stockio_id'        => $id,
                    'stockio_no'        => $adjust_order['adjust_no'],
                    'spec_id'           => $detail['spec_id'],
                    'warehouse_id'      => $adjust_order['warehouse_id'],
                    'type'              => 0,
                    'cost_price_old'    => $stock_spec_info['cost_price'],
                    'stock_num_old'     => $stock_spec_info['stock_num'],
                    'price'             => $detail['adjust_price'],
                    'num'               => 0,
                    'amount'            => $adjust_amount,
                    'cost_price_new'    => $adjust_cost_price,
                    'stock_num_new'     => $stock_spec_info['stock_num'],
                    'operator_id'       => $operator_id,
                    'remark'            => '');
                $res_update_history = M('stock_change_history')->add($history_data);*/
            }

        }catch (BusinessLogicException $e){
            $msg = $e->getMessage();
            SE($msg);
        }catch(\PDOException $e){
            $msg = $e->getMessage();
            \Think\Log::write($this->name.'-checkAdjustOrder-'.$msg);
            SE(self::PDO_ERROR);
        }catch(\Exception $e){
            $msg = $e->getMessage();
            \Think\Log::write($this->name.'-checkAdjustOrder-'.$msg);
            SE(self::PDO_ERROR);
        }
    }
}