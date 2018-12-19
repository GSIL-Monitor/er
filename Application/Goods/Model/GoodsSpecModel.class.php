<?php
/**
 * Created by PhpStorm.
 * User: yanfeng
 * Date: 2015/8/20
 * Time: 16:00
 */
namespace Goods\Model;

use Think\Model;
use Think\Exception\BusinessLogicException;

class GoodsSpecModel extends Model {

    /**
     * 获得选取单品页面表格格式
     * author:luyanfeng
     */
    public function getSelectStr() {
        $fields = array(
            "id"   => array("field" => 'id', "hidden" => true),
            '图片'    => array('field'=>'img_url','width'=>'100','formatter'=>'formatter.print_img'),
            "商家编码" => array("field" => 'spec_no', "title" => '商家编码', "width" => "11%"),
            "货品编码" => array("field" => 'goods_no', "title" => '货品编码', "width" => "11%"),
            "货品名称" => array("field" => 'goods_name', "title" => '货品名称', "width" => "11%"),
            "品牌"   => array("field" => 'brand_name', "title" => '品牌', "width" => "11%"),
            "规格码"  => array("field" => 'spec_code', "title" => '规格码', "width" => "11%"),
            "规格名称" => array("field" => 'spec_name', "title" => '规格名称', "width" => "11%"),
            "条形码"  => array("field" => 'barcode', "title" => '条形码', "width" => "11%"),
            "零售价"   => array("field" => 'retail_price', "title" => '零售价', "width" => "11%"),
            "市场价"   => array("field" => 'market_price', "title" => '市场价', "width" => "11%"),
            "最低价"   => array("field" => 'lowest_price', "title" => '最低价', "width" => "11%" ),
            "规格数"  => array("field" => 'spec_count', "title" => '规格数', "width" => "11%"),
            /*"库存数量" => array("field" => "stock_num", "title" => "库存数量", "width" => "9%")*/);
        return $fields;
    }

    /**
     * 获取单品页面选择结果datagrid的字段
     * author:luyanfeng
     */
    public function getSubSelectStr() {
        $fields = array(
            "id"   => array("field" => 'id', "hidden" => true),
            '图片'    => array('field'=>'img_url','width'=>'100','formatter'=>'formatter.print_img'),
            "商家编码" => array("field" => 'spec_no', "title" => '商家编码', "width" => "11%",),
            "货品编码" => array("field" => 'goods_no', "title" => '货品编码', "width" => "11%"),
            "货品名称" => array("field" => 'goods_name', "title" => '货品名称', "width" => "11%"),
            "品牌"   => array("field" => 'brand_name', "title" => '品牌', "width" => "11%"),
            "规格码"  => array("field" => 'spec_code', "title" => '规格码', "width" => "11%"),
            "规格名称" => array("field" => 'spec_name', "title" => '规格名称', "width" => "11%"),
            "条形码"  => array("field" => 'barcode', "title" => '条形码', "width" => "11%"),
            "零售价"   => array("field" => 'retail_price', "title" => '零售价', "width" => "11%"),
            '数量(可编辑)'=>array('field'=>'num','width'=>100,'methods'=>'editor:{type:"numberbox",options:{precision:3}}'),
            "市场价"   => array("field" => 'market_price', "title" => '市场价', "width" => "11%"),
            "最低价"   => array("field" => 'lowest_price', "title" => '最低价', "width" => "11%"),
            "规格数"  => array("field" => 'spec_count', "title" => '规格数', "width" => "11%")
        );
        return $fields;
    }

    /**
     * 返回库存相关的单品列表
     * @return array
     */
    public function getStockSelectDatagridFields() {
        $fields = array(
            "id"   => array("field" => "id", "hidden" => true),
            '图片'    => array('field'=>'img_url','width'=>'100','formatter'=>'formatter.print_img'),
            "商家编码" => array("field" => 'spec_no', "title" => '商家编码', "width" => 74),
            "货品编码" => array("field" => 'goods_no', "title" => '货品编码', "width" => 74),
            "货品名称" => array("field" => 'goods_name', "title" => '货品名称', "width" => 74),
            "货品简称" => array("field" => "short_name", "title" => "货品简称", "width" => 74),
            "品牌"   => array("field" => 'brand_name', "title" => '品牌', "width" => 74),
            "规格码"  => array("field" => 'spec_code', "title" => '规格码', "width" => 74),
            "规格名称" => array("field" => 'spec_name', "title" => '规格名称', "width" => 74),
            "条形码"  => array("field" => 'barcode', "title" => '条形码', "width" => 74),
            "实际库存" => array("field" => "stock_num", "title" => "实际库存", "width" => 74),
            "可发库存" => array("field" => "orderable_num", "title" => "可发库存", "width" => 74),
            /*"锁定量"  => array("field" => "lock_num", "title" => "锁定量", "width" => 74),
            "预订单量" => array("field" => "subscribe_num", "title" => "预订单量", "width" => 74),
            "订购量"  => array("field" => "order_num", "title" => "订购量", "width" => 74),
            "待发货量" => array("field" => "sending_num", "title" => "待发货量", "width" => 74),
            "采购到货" => array("field" => "purchase_arrive_num", "title" => "采购到货", "width" => 74),
            "采购在途" => array("field" => "purchase_num", "title" => "采购在途", "width" => 74),
            "调拨在途" => array("field" => "transfer_num", "title" => "调拨在途", "width" => 74),
            "未付款量" => array("field" => "unpay_num", "title" => "未付款量", "width" => 74),*/
            "价格"   => array("field" => "retail_price", "title" => "价格", "width" => 74),
            "是否允许负库存出库"   => array("field" => "is_allow_neg_stock", "title" => "是否允许负库存出库", "width" => 120,"formatter" => "formatter.boolen")
        );
        return $fields;
    }
    /**
     * 返回库存相关的单品列表子表
     * @return array
     */
    public function getSubStockSelectDatagridFields() {
        $fields = array(
            "id"   => array("field" => "id", "hidden" => true),
            '图片'    => array('field'=>'img_url','width'=>'100','formatter'=>'formatter.print_img'),
            "商家编码" => array("field" => 'spec_no', "title" => '商家编码', "width" => 74),
            "货品编码" => array("field" => 'goods_no', "title" => '货品编码', "width" => 74),
            "货品名称" => array("field" => 'goods_name', "title" => '货品名称', "width" => 74),
            "货品简称" => array("field" => "short_name", "title" => "货品简称", "width" => 74),
            "品牌"   => array("field" => 'brand_name', "title" => '品牌', "width" => 74),
            "规格码"  => array("field" => 'spec_code', "title" => '规格码', "width" => 74),
            "规格名称" => array("field" => 'spec_name', "title" => '规格名称', "width" => 74),
            "条形码"  => array("field" => 'barcode', "title" => '条形码', "width" => 74),
            '数量(可编辑)'=>array('field'=>'num','width'=>100,'methods'=>'editor:{type:"numberbox",options:{precision:3}}'),
            "实际库存" => array("field" => "stock_num", "title" => "实际库存", "width" => 74),
            "可发库存" => array("field" => "orderable_num", "title" => "可发库存", "width" => 74),
            "价格"   => array("field" => "retail_price", "title" => "价格", "width" => 74),
            "是否允许负库存出库"   => array("field" => "is_allow_neg_stock", "title" => "是否允许负库存出库", "width" => 120,"formatter" => "formatter.boolen")
        );
        return $fields;
    }
    /**
     * 返回库存相关的单品列表
     * @return array
     */
    public function getGoodsBarcodeDatagridFields() {
        $fields = array(
            "id"   => array("field" => "id", "hidden" => true),
            "商家编码" => array("field" => 'spec_no', "title" => '商家编码', "width" => 74),
            "货品编码" => array("field" => 'goods_no', "title" => '货品编码', "width" => 74),
            "货品名称" => array("field" => 'goods_name', "title" => '货品名称', "width" => 74),
            "货品简称" => array("field" => "short_name", "title" => "货品简称", "width" => 74),
            "品牌"   => array("field" => 'brand_name', "title" => '品牌', "width" => 74),
            "规格码"  => array("field" => 'spec_code', "title" => '规格码', "width" => 74),
            "规格名称" => array("field" => 'spec_name', "title" => '规格名称', "width" => 74),
            "条形码"  => array("field" => 'barcode', "title" => '条形码', "width" => 74),
            "主条码"  => array("field" => 'is_master', "title" => '主条码', "width" => 74),
        );
        return $fields;
    }
    /**
     * 返回库存相关的单品列表
     * @return array
     */
    public function getSubGoodsBarcodeDatagridFields() {
        $fields = array(
            "id"   => array("field" => "id", "hidden" => true),
            "商家编码" => array("field" => 'spec_no', "title" => '商家编码', "width" => 74),
            "货品编码" => array("field" => 'goods_no', "title" => '货品编码', "width" => 74),
            "货品名称" => array("field" => 'goods_name', "title" => '货品名称', "width" => 74),
            "货品简称" => array("field" => "short_name", "title" => "货品简称", "width" => 74),
            "品牌"   => array("field" => 'brand_name', "title" => '品牌', "width" => 74),
            "规格码"  => array("field" => 'spec_code', "title" => '规格码', "width" => 74),
            "规格名称" => array("field" => 'spec_name', "title" => '规格名称', "width" => 74),
            "条形码"  => array("field" => 'barcode', "title" => '条形码', "width" => 74),
            "主条码"  => array("field" => 'is_master', "title" => '主条码', "width" => 74),
            '打印次数(可编辑)'=>array('field'=>'num','width'=>100,'methods'=>'editor:{type:"numberbox",options:{precision:0}}'),
        );
        return $fields;
    }

    /**
     * 查询
     * author:changtao
     */
    public function loadDataByCondition($page = 1, $rows = 20, $search = array(), $sort = 'id', $order = 'desc') {
        $where_goods_goods = array();
        $where_goods_spec  = array();
        $where_goods_class = array();
        set_search_form_value($where_goods_spec, 'deleted', 0, 'gs', 2);
        $page = intval($page);
        $rows = intval($rows);
        foreach ($search as $k => $v) {
            if ($v === "") continue;
            switch ($k) {
                case 'goods_goods_no':
                    set_search_form_value($where_goods_goods, 'goods_no', $v, 'gg', 1);
                    break;
                case 'spec_spec_no':
                    set_search_form_value($where_goods_spec, 'spec_no', $v, 'gs', 10);
                    break;
                case 'goods_goods_name':
                    set_search_form_value($where_goods_goods, 'goods_name', $v, 'gg', 10);
                    break;
                case 'goods_spec_barcode':
                    set_search_form_value($where_goods_spec, 'barcode', $v, 'gs', 1);
                    break;
                case 'goods_short_name':
                    set_search_form_value($where_goods_goods, 'short_name', $v, 'gg', 6);
                    break;
                case 'spec_spec_name':
                    set_search_form_value($where_goods_spec, 'spec_name', $v, 'gs', 6);
                    break;
                case 'brand_id':
                    set_search_form_value($where_goods_goods, 'brand_id', $v, 'gg', 2);
                    break;
                case 'class_id':
                    $left_join_goods_class_str = set_search_form_value($where_goods_class, 'class_id', $v, 'gg', 7);
                    break;
                /*case 'deleted':
                 if ($v == 1) {
                 set_search_form_value($where_limit, 'deleted', 0, 'gs', 2,"AND");
                 }
                 break;*/
                default:
                    continue;
            }
        }
        $limit = ($page - 1) * $rows . "," . $rows;//分页
        $order = $sort . " " . $order;//排序
        $order = addslashes($order);
        try {
            $m    = $this->alias('gs');
            $m    = $this->where($where_goods_spec);
            $m    = $m->join('goods_goods gg on gg.goods_id = gs.goods_id')->where($where_goods_goods);
            $m    = $m->join($left_join_goods_class_str)->where($where_goods_class);
            
            $page = clone $m;
            
            $sql_total = $m->fetchSql(true)->count();
            $total = $m->query($sql_total);
            $total = $total[0]['tp_count'];
            
            $sql_page = $page->field('gs.spec_id id')->order($order)->limit($limit)->group('gs.spec_id')->fetchSql(true)->select();
            $sql      = 'SELECT gs.spec_id as id,gs.spec_no,gs.spec_name,gs.spec_code,gs.barcode,gs.retail_price,'
                . 'gs.wholesale_price,gs.member_price,gs.market_price,gs.lowest_price,gs.validity_days,'
                . 'gs.length,gs.width,gs.height,gs.sale_score,gs.pack_score,gs.pick_score,gs.weight,'
                . 'gs.is_sn_enable,gs.is_allow_neg_stock,gs.is_not_need_examine,'
                . 'gs.is_allow_zero_cost,gs.is_allow_lower_cost,gs.large_type,gs.remark,gs.is_hotcake,'
                . 'gs.prop1,gs.prop2,gs.prop3,gs.prop4,'
                . 'gg.goods_name,gg.short_name,gg.goods_no,gg.class_id,gg.brand_id '
                . ' FROM goods_spec gs'
                . ' LEFT JOIN goods_goods gg ON gg.goods_id = gs.goods_id'
                . ' JOIN (' . $sql_page . ') page ON page.id = gs.spec_id'
                . ' order by ' . $order;
            $list = $total ? $m->query($sql) : array();
            $data = array('total' => $total, 'rows' => $list);
        } catch (\PDOException $e) {
            $msg = $e->getMessage();
            \Think\Log::write($this->name . '-loadDataByCondition-' . $msg);
            SE(self::PDO_ERROR);
        };
        return $data;
    }
    public function getGoodsSpecObject($fields,$conditions= array())
    {
        try {
            $result = $this->field($fields)->where($conditions)->find();
            return $result;
        } catch (\PDOException $e) {
            $msg = $e->getMessage();
            \Think\Log::write($this->name.'--getGoodsSpecObject--'.$msg);
            SE(self::PDO_ERROR);
        }
    }
    public function getGoodsInfoObjectForStockImport($fields,$conditions= array())
    {
        try {
            $result = $this->alias('gs')->field($fields)->join('left join goods_goods gg on gg.goods_id = gs.goods_id')->where($conditions)->find();
            return $result;
        } catch (\PDOException $e) {
            $msg = $e->getMessage();
            \Think\Log::write($this->name.'--getGoodsSpecObject--'.$msg);
            SE(self::PDO_ERROR);
        }
    }
    public function getGoodsSpecCount(){
        try {
            $num = $this->field('spec_id')->where(array('deleted'=>0))->select();
            return count($num);
        } catch (\PDOException $e) {
            $msg = $e->getMessage();
            \Think\Log::write($this->name.'--getGoodsSpecCount--'.$msg);
            SE(self::PDO_ERROR);
        }
    }

    /*
     * 根据id获获取单品和货品的数据
     * ids 为spec_id 支持数组和int类型
     * 如果ids是数组查询所有的数据 如果是int只查该spec_id对应的数据
     * */
    public function getGoodSpecData($ids){
         try {
             $sql="select gs.spec_id,gs.spec_code,gs.spec_no,gs.spec_name,gg.goods_name,gg.goods_no from goods_spec gs left join goods_goods gg on gg.goods_id=gs.goods_id";
             if(is_array($ids)){
                 $id_arr=implode(',',$ids);
                 $where=" where gs.spec_id in({$id_arr})";
             }else{
                 $where=" where gs.spec_id= {$ids}";
             }
             $sql=$sql.$where;
             $result=$this->query($sql);
             return $result;
         } catch (\PDOException $e) {
             $msg = $e->getMessage();
             \Think\Log::write($this->name.'--getGoodSpecData--'.$msg);
             SE(self::PDO_ERROR);
         }
    }

    public function chgHotCake($rec_id,$hotcake,&$result)
    {
        try
        {
            $rec_id_arr = explode(',',$rec_id);
            $cur_time = date('Y-m-d H:i:s');
            $this->startTrans();
            $log_model = M('goods_log');
            $goods_goods_model = D('GoodsGoods');
            $operator_id = get_operator_id;
            foreach($rec_id_arr as $v)
            {
                $data['is_hotcake'] = $hotcake;
                $log_hotcake = $hotcake==1?'是':'否';
                $data['modified'] = $cur_time;
                $goods_res = $goods_goods_model->alias('gg')->where(array('gs.spec_id'=>$v))->join('LEFT JOIN goods_spec gs ON gg.goods_id=gs.goods_id')->fetchSql(false)->find();
                //获取货品信息,记录日志
                $spec_res = $this->field('is_hotcake,spec_no')->where(array('spec_id'=>$v))->find();
                $spec_res['is_hotcake'] = $spec_res['is_hotcake']==1?'是':'否';
                M('goods_spec')->fetchSql(false)->where(array('spec_id'=>$v))->save($data);


                //记录日志
                $arr_goods_log = array(
                    "goods_id"     => $goods_res['goods_id'],
                    "goods_type"   => 1,
                    "spec_id"      => $v,
                    "operator_id"  => $operator_id,
                    "operate_type" => 52,
                    "message"      => "修改单品--".$spec_res['spec_no']." 是否爆款从 ".$spec_res['is_hotcake'].'到'.$log_hotcake  ,
                    "created"      => $cur_time
                );
                $log_model->data($arr_goods_log)->add();
            }
            $this->commit();
        }catch (\PDOException $e){
            \Think\Log::write('sql-error:chgHotCake'.$e->getMessage());
            $this->rollback();
            $result=array('status'=>1,'msg'=> parent::PDO_ERROR);
        }catch (\Exception $e){
            \Think\Log::write('chgHotCake'.$e->getMessage());
            $this->rollback();
            $result=array('status'=>1,'msg'=> parent::PDO_ERROR);
        }

    }
}