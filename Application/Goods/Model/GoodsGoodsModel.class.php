<?php
namespace Goods\Model;

use Think\Exception;
use Think\Model;
use Common\Common\Factory;
use Common\Common\UtilDB;
use Common\Common\UtilTool;
use Common\Common\ExcelTool;
use Think\Exception\BusinessLogicException;
use Think\Log;

class GoodsGoodsModel extends Model {
    protected $tableName = 'goods_goods';
    protected $pk        = 'goods_id';
    protected $_validate = array(
        //array(验证字段1,验证规则,错误提示,[验证条件(0),附加规则,验证时间(3)])
        //验证条件
        //self::EXIST S_VALIDAT E 或者0 存在字段就验证（默认）
        //self::MUST _VALIDAT E 或者1 必须验证
        //self::VALUE_VALIDAT E或者2 值不为空的时候验证
        //验证时间
        //self::MODEL_INSERT或者1新增数据时候验证
        //self::MODEL_UPDAT E或者2编辑数据时候验证
        //self::MODEL_BOT H或者3全部情况下验证（默认）

        array('goods_name', 'require', '货品名称不能为空！'), //默认情况下用正则进行验证
        array('goods_no', 'require', '货品编码不能为空！'), //
        array('spec_count', '1,100', '至少添加一个规格！', 1, 'length'), //长度暂时定为1-100
        array('goods_type', 'number', '该货品类别未找到！', 0),
        array('flag_id', 'number', '该货品标记未找到！', 0),
        array('brand_id', 'number', '该货品品牌未找到！', 0),
        array('unit', 'number', '该货品单位未找到！', 0),
        array('class_id', 'number', '该货品分类未找到！', 0),
        array('goods_type', array(0, 1), '该货品类别未找到！', 1, 'in'),
        array('flag_id', 'checkFlag', '无效标记！', 1, 'callback'), // 回调函数验证标记
        array('brand_id', 'checkBrand', '无效品牌！', 2, 'callback'),
        array('unit', 'checkUnit', '无效单位！', 2, 'callback'), // 回调函数验证单位
        array('class_id', 'checkClass', '无效分类！', 2, 'callback'), // 回调函数验证分类
        array('goods_no', 'checkGoodsNo', '该货品编码已存在，请重新输入！', 0, 'callback', 1), // 在新增的时候验证goods_no字段是否唯一
    );

    protected function checkGoodsNo($goods_no) {
        try {
            $map["goods_no"] = $goods_no;
            $map["deleted"]  = 0;
            $result          = $this->where($map)->find();
            if (!$result) {
                return true;
            } else {
                return false;
            }
        } catch (\PDOException $e) {
            \Think\Log::write($e->getMessage());
            return false;
        } catch (\Exception $e) {
            //\Think\Log::write($e->getMessage());
            return false;
        }
        return false;
    }

    protected function checkFlag($flag_id) {
        return Factory::getModel('Setting/Flag')->checkFlag($flag_id, 2);
    }

    protected function checkBrand($brand_id) {
        return Factory::getModel('GoodsBrand')->checkBrand($brand_id);
    }

    protected function checkUnit($unit) {
        try {
            $map['rec_id'] = $unit;
            $result        = M('cfg_goods_unit')->field('rec_id')->where($map)->find();
            if (!empty($result)) {
                return true;
            }
        } catch (\PDOException $e) {
            \Think\Log::write($e->getMessage());
        }
        return false;
    }

    protected function checkClass($class_id) {
        try {
            $map['class_id'] = $class_id;
            $result          = M('goods_class')->field('class_id')->where($map)->find();
            if (!empty($result)) {
                return true;
            }
        } catch (\PDOException $e) {
            \Think\Log::write($e->getMessage());
        }
        return false;
    }

    public function getSpecList($id) {
        $data = array();
        try {
            $sql  = 'SELECT gs.spec_no, gs.spec_name, gs.spec_code, gs.barcode, gs.retail_price, gs.wholesale_price,gs.member_price,
        gs.market_price, gs.lowest_price, gs.validity_days, gs.length, gs.width, gs.height, gs.sale_score, gs.pack_score, gs.pick_score,
        gs.weight, gs.tax_rate, gs.is_sn_enable, gs.is_allow_neg_stock, gs.is_not_need_examine, gs.is_allow_zero_cost, gs.is_allow_lower_cost ,
        gs.large_type, gu.name AS unit, gs.remark, gs.prop1, gs.prop2, gs.prop3, gs.prop4 FROM goods_spec gs LEFT JOIN cfg_goods_unit gu ON gu.rec_id=gs.unit WHERE gs.goods_id=%d AND gs.deleted=0';
            $list = M()->query($sql, $id);
            $data = array('total' => count($list), 'rows' => $list);
        } catch (\PDOException $e) {
            $data = array('total' => 0, 'rows' => array());
            \Think\Log::write($e->getMessage());
        }
        return $data;
    }

    public function getGoodsLog($id, $type = 'goods_goods') {
        $data = array();
        try {
            switch ($type) {
                case 'goods_goods':
                    $sql = 'SELECT gl.rec_id,gg.goods_no,gs.spec_no,gs.spec_name,he.fullname AS operator_id,gl.message,
                gl.created FROM goods_log gl LEFT JOIN goods_goods gg ON gg.goods_id = gl.goods_id
                LEFT JOIN goods_spec gs ON gl.spec_id=gs.spec_id LEFT JOIN hr_employee he
                ON gl.operator_id=he.employee_id WHERE gl.goods_id=%d AND gl.goods_type=1 GROUP BY gl.rec_id ORDER BY gl.rec_id DESC';
                    break;
                case 'goods_spec':
                    $sql = "SELECT gl.message,he.fullname as operator_id,gl.created FROM goods_log gl LEFT JOIN hr_employee he
                ON (he.employee_id = gl.operator_id) WHERE gl.spec_id = %d AND gl.goods_type=1 ORDER BY gl.rec_id DESC ";
                    break;
                default:
                    \Think\Log::write($this->name . '-getGoodsLog-' . '不存在该日志类型：' . $type);
                    $sql = '';
            }
            $list = M()->query($sql, $id);
            $data = array('total' => count($list), 'rows' => $list);
        } catch (\PDOException $e) {
            $data = array('total' => 0, 'rows' => array());
            \Think\Log::write($e->getMessage());
        }
        return $data;
    }
    // 获取tab中的出库仓库、物流信息
    public function getGoodsOutInfo($id,$from,$type){
        $data = array();
        $is_suite=0;
        try {
            if($from=='goods'){
                $spec = M("goods_spec")->field("spec_id")->where("goods_id=".$id)->select();
                if(empty($spec)){
                    $data = array('total' => 0, 'rows' => array());
                    return $data;
                }
                $spec_id = "";
                foreach ($spec as $sp) {
                    $spec_id .= $sp["spec_id"].",";
                }
                $spec_id = rtrim($spec_id,",");
            }else if($from=='suite') {
                $spec_id=intval($id);
                $is_suite=1;
            }else{
                $spec_id=intval($id);
            }
            if($type=='warehouse'){
                $sql = 'SELECT cgw.rec_id AS id,IF(cgw.shop_id=0,"全部",cs.shop_name) AS shop_id,IF(cgw.warehouse_id=0,"全部",cw.name) AS warehouse_id
                    FROM cfg_goods_warehouse cgw
                    LEFT JOIN cfg_shop cs ON cgw.shop_id = cs.shop_id
                    LEFT JOIN cfg_warehouse cw ON cgw.warehouse_id=cw.warehouse_id
                    WHERE cgw.spec_id in ('.$spec_id.') GROUP BY cgw.shop_id,cgw.warehouse_id';
            }else{
                $sql = 'SELECT cgl.rec_id AS id,IF(cgl.shop_id=0,"全部",cs.shop_name) AS shop_id,IF(cgl.warehouse_id=0,"全部",cw.name) AS warehouse_id,IF(cgl.logistics_id=0,"全部",cl.logistics_name) AS logistics_id
                    FROM cfg_goods_logistics cgl
                    LEFT JOIN cfg_shop cs ON cgl.shop_id = cs.shop_id
                    LEFT JOIN cfg_warehouse cw ON cgl.warehouse_id=cw.warehouse_id
                    LEFT JOIN cfg_logistics cl ON cgl.logistics_id=cl.logistics_id
                    WHERE cgl.spec_id in ('.$spec_id.') AND cgl.type='.$is_suite.' GROUP BY cgl.shop_id,cgl.warehouse_id,cgl.logistics_id';
            }
            $list = M()->query($sql);
            $data = array('total' => count($list), 'rows' => $list);
        } catch (\PDOException $e) {
            $data = array('total' => 0, 'rows' => array());
            \Think\Log::write($e->getMessage());
        }
        return $data;
    }

    /**
     * 添加单品
     * @param unknown $db
     * @param unknown $arr_goods_spec
     * @param unknown $is_rollback
     * @param unknown $sql_error_info
     * @param unknown $goods_id
     * @param unknown $userId
     * @param string  $mode
     */
    public function addSpec(&$db, $arr_goods_spec, &$is_rollback, &$sql_error_info, $goods_id, $userId, $mode = 'insert') {
        $arr_temp = array();
        if ($mode == 'insert') {//添加货品--
            $sql_error_info = 'add_goods_specs';
            $res_spec_id    = $db->table('goods_spec')->addAll($arr_goods_spec);

            $sql_error_info = 'get_goods_specs';
            $arr_temp       = $db->table('goods_spec')->field('spec_id,spec_no,spec_code,barcode,spec_name')->where(array('goods_id' => $goods_id))->select();

        } else if ($mode == 'update') {//更新货品--
            for ($i = 0; $i < count($arr_goods_spec); $i++) {
                $sql_error_info                  = 'add_goods_spec';
                $res_spec_id                     = $db->table('goods_spec')->add($arr_goods_spec[ $i ]);
                $arr_goods_spec[ $i ]['spec_id'] = $res_spec_id;
            }
            $arr_temp = $arr_goods_spec;
        }

        $i = 0;//初始化下标
        foreach ($arr_temp as $v) {
            if (isset($v["barcode"]) && $v["barcode"] != "") {//条码数据整理
                $sql_error_info          = 'updateGoods-goods_barcode-check';
                $arr_goods_barcode[ $i ] = array(
                    'barcode'   => $v['barcode'],
                    'type'      => 1,//1普通规格，2组合装
                    'target_id' => $v['spec_id'],
                    //'tag'      =>array('exp',"IF(0,0,FN_SEQ('goods_barcode'))"),
                    'tag'       => get_seq("goods_barcode"),
                    'is_master' => 1,
                    'modified'  => array('exp', 'NOW()'),
                    'created'   => array('exp', 'NOW()')
                );
            }
            //商家编码数据整理
            $arr_goods_merchant_no[ $i ] = array(
                'merchant_no' => $v['spec_no'],
                'type'        => 1,//1普通规格，2组合装
                'target_id'   => $v['spec_id'],
                'modified'    => array('exp', 'NOW()'),
                'created'     => array('exp', 'NOW()')
            );
            //单品插入日志
            $arr_goods_spec_log[ $i ] = array(
                'goods_type'   => 1,//1-货品 2-组合装
                'goods_id'     => $goods_id,
                'spec_id'      => $v['spec_id'],
                'operator_id'  => $userId,//操作者暂时默认为user_id=1
                'operate_type' => 11,
                'message'      => '新建货品--单品--' . $v['spec_name'],//"CONCAT('新建货品','--','')",
                'created'      => array('exp', 'NOW()')
            );
            $i++;

        }
        //插入条码
        if (!empty($arr_goods_barcode)) {
            $sql_error_info = 'add_goods_barcode';
            M("goods_barcode")->addAll($arr_goods_barcode);
        }

        //插入商家编码
        $sql_error_info = 'add_merchant_no';
        $db->table('goods_merchant_no')->addAll($arr_goods_merchant_no);

        //更新api_goods_spec
        foreach ($arr_temp as $v) {
            $this->matchApiGoodsSpecByMerchantNo($db, $v['spec_no'], $sql_error_info);
        }

        //初始化库存
        $sql_error_info="add_stock_spec";
        D('Stock/StockSpec')->initStockSpec($arr_temp);
        //插入单品日志
        $sql_error_info = 'add_goods_spec_log';
        $db->table('goods_log')->addAll($arr_goods_spec_log);
    }
    /**
     * 过滤可以删除的单品
     * @param unknown $db
     * @param unknown $goods_spec_arr
     * @param unknown $sql_error_info
     * @param unknown $list
     * @param number  $goods_id
     */
    public function filterSpec(&$db, $goods_spec_arr, &$sql_error_info, &$list,$goods_id){
        $arr_goods_spec=array();
        foreach ($goods_spec_arr as $v) {
            $check_flag=true;
            $sql_error_info = 'filterSpec-get_stock_spec';
            $res_stock_spec = $db->query(" SELECT stock_num,lock_num,unpay_num,subscribe_num,order_num,sending_num,purchase_num, transfer_num,to_purchase_num,
            purchase_arrive_num,return_num FROM stock_spec WHERE spec_id=%d AND (stock_num<>0 OR lock_num<>0 OR unpay_num<>0 OR subscribe_num<>0 OR
            order_num<>0 OR sending_num<>0 OR purchase_num<>0 OR transfer_num<>0 OR to_purchase_num<>0 OR purchase_arrive_num<>0 OR return_num<>0) LIMIT 1", $v['spec_id']);
            if ($res_stock_spec[0]['stock_num'] != 0) {
                //E('单品:' . $v['spec_no'] . '库存不为零,不可删除');
                $list[]=$goods_id==0?array('spec_no'=>$v['spec_no'],'info'=>'库存不为零,不可删除'):array('goods_no'=>$v['goods_no'],'info'=>'单品:' . $v['spec_no'] . '库存不为零,不可删除');
                $check_flag=false;
            }
            if ($res_stock_spec[0]['lock_num'] != 0) {
                //E('单品:' . $v['spec_no'] . '锁定量不为零,不可删除');
                $list[]=$goods_id==0?array('spec_no'=>$v['spec_no'],'info'=>'锁定量不为零,不可删除'):array('goods_no'=>$v['goods_no'],'info'=>'单品:' . $v['spec_no'] . '锁定量不为零,不可删除');
                $check_flag=false;
            }
            if ($res_stock_spec[0]['unpay_num'] != 0) {
                //E('单品:' . $v['spec_no'] . '未付款量不为零,不可删除');
                $list[]=$goods_id==0?array('spec_no'=>$v['spec_no'],'info'=>'未付款量不为零,不可删除'):array('goods_no'=>$v['goods_no'],'info'=>'单品:' . $v['spec_no'] . '未付款量不为零,不可删除');
                $check_flag=false;
            }
            if ($res_stock_spec[0]['subscribe_num'] != 0) {
                //E('单品:' . $v['spec_no'] . '预订量不为零,不可删除');
                $list[]=$goods_id==0?array('spec_no'=>$v['spec_no'],'info'=>'预订量不为零,不可删除'):array('goods_no'=>$v['goods_no'],'info'=>'单品:' . $v['spec_no'] . '预订量不为零,不可删除');
                $check_flag=false;
            }
            if ($res_stock_spec[0]['order_num'] != 0) {
                //E('单品:' . $v['spec_no'] . '待审核量不为零,不可删除');
                $list[]=$goods_id==0?array('spec_no'=>$v['spec_no'],'info'=>'待审核量不为零,不可删除'):array('goods_no'=>$v['goods_no'],'info'=>'单品:' . $v['spec_no'] . '待审核量不为零,不可删除');
                $check_flag=false;
            }
            if ($res_stock_spec[0]['sending_num'] != 0) {
                //E('单品:' . $v['spec_no'] . '待发货量不为零,不可删除');
                $list[]=$goods_id==0?array('spec_no'=>$v['spec_no'],'info'=>'待发货量不为零,不可删除'):array('goods_no'=>$v['goods_no'],'info'=>'单品:' . $v['spec_no'] . '待发货量不为零,不可删除');
                $check_flag=false;
            }
            if ($res_stock_spec[0]['purchase_num'] != 0) {
                //E('单品:' . $v['spec_no'] . '采购在途量不为零,不可删除');
                $list[]=$goods_id==0?array('spec_no'=>$v['spec_no'],'info'=>'采购在途量不为零,不可删除'):array('goods_no'=>$v['goods_no'],'info'=>'单品:' . $v['spec_no'] . '采购在途量不为零,不可删除');
                $check_flag=false;
            }
            if ($res_stock_spec[0]['transfer_num'] != 0) {
                //E('单品:' . $v['spec_no'] . '调拨在途量不为零,不可删除');
                $list[]=$goods_id==0?array('spec_no'=>$v['spec_no'],'info'=>'调拨在途量不为零,不可删除'):array('goods_no'=>$v['goods_no'],'info'=>'单品:' . $v['spec_no'] . '调拨在途量不为零,不可删除');
                $check_flag=false;
            }
            if ($res_stock_spec[0]['to_purchase_num'] != 0) {
                //E('单品:' . $v['spec_no'] . '待采购量不为零,不可删除');
                $list[]=$goods_id==0?array('spec_no'=>$v['spec_no'],'info'=>'待采购量不为零,不可删除'):array('goods_no'=>$v['goods_no'],'info'=>'单品:' . $v['spec_no'] . '待采购量不为零,不可删除');
                $check_flag=false;
            }
            /*if ($res_stock_spec[0]['purchase_arrive_num'] != 0) {
                //E('单品:' . $v['spec_no'] . '采购到货量不为零,不可删除');
                $list[]=$goods_id==0?array('spec_no'=>$v['spec_no'],'info'=>'采购到货量不为零,不可删除'):array('goods_no'=>$v['goods_no'],'info'=>'单品:' . $v['spec_no'] . '采购到货量不为零,不可删除');
                $check_flag=false;
            }
            if ($res_stock_spec[0]['return_num'] != 0) {
                //E('单品:' . $v['spec_no'] . '采购退货量不为零,不可删除');
                $list[]=$goods_id==0?array('spec_no'=>$v['spec_no'],'info'=>'采购退货量不为零,不可删除'):array('goods_no'=>$v['goods_no'],'info'=>'单品:' . $v['spec_no'] . '采购退货量不为零,不可删除');
                $check_flag=false;
            }*/
            $sql_error_info = 'filterSpec-get_suite_detail';
            $res_suite_val  = $db->query("SELECT 1 FROM goods_suite_detail gsd,goods_suite gs WHERE gsd.spec_id=%d AND
            gs.suite_id=gsd.suite_id AND gs.deleted=0", $v['spec_id']);
            if (!empty($res_suite_val)) {
                //E('单品:' . $v['spec_no'] . '被组合装使用,不可删除');
                $list[]=$goods_id==0?array('spec_no'=>$v['spec_no'],'info'=>'被组合装使用,不可删除'):array('goods_no'=>$v['goods_no'],'info'=>'单品:' . $v['spec_no'] . '被组合装使用,不可删除');
                $check_flag=false;
            }
            $sql_error_info = 'filterSpec-get_refund_detail';
            $res_refund_val  = $db->query("SELECT 1 FROM sales_refund sr LEFT JOIN sales_refund_order sro ON sro.refund_id=sr.refund_id WHERE sr.process_status<>10 AND sr.process_status<>20 AND sr.process_status<>90 AND sro.spec_id =%d", $v['spec_id']);
            if (!empty($res_refund_val)) {
                $list[]=$goods_id==0?array('spec_no'=>$v['spec_no'],'info'=>'退换入库量不为零,不可删除'):array('goods_no'=>$v['goods_no'],'info'=>'单品:' . $v['spec_no'] . '退换入库量不为零,不可删除');
                $check_flag=false;
            }
            if($check_flag){
                $arr_goods_spec[]=$v;
            }
        }
        return $arr_goods_spec;
    }
    /**
     * 删除单品
     * @param unknown $db
     * @param unknown $arr_goods_spec
     * @param unknown $is_rollback
     * @param unknown $sql_error_info
     * @param number  $goods_id
     * @param number  $spec_count
     */
    public function delSpec(&$db, $goods_spec_arr, &$is_rollback, &$sql_error_info, &$list,$goods_id = 0) {
        $userId = get_operator_id();
        //判断是否可以删除单品
        $arr_goods_spec = $this->filterSpec($db, $goods_spec_arr, $sql_error_info, $list,$goods_id);
        //货品档案中只要有一个单品不满足删除条件，则整个货品档案（包括单品）不做删除操作
        $length=count($arr_goods_spec);
        if($goods_id!=0 && count($goods_spec_arr)>$length)
        {
        	return;
        }
        $is_rollback = true;
        $db->startTrans();
        for ($i = 0; $i < $length; $i++) {
            $sql_error_info = 'delSpec-update_goods_spec-delete';
            $db->execute("UPDATE goods_spec SET deleted=" . time() . " WHERE spec_id=%d", $arr_goods_spec[ $i ]['spec_id']);
            //$spec_count--;

            $sql_error_info = 'delSpec-delete_goods_merchant_no';
            $db->execute("DELETE FROM goods_merchant_no WHERE type=1 AND target_id=%d", $arr_goods_spec[ $i ]['spec_id']);

            $sql_error_info = 'delSpec-delete_goods_barcode';
            $db->execute("DELETE FROM goods_barcode WHERE type=1 AND target_id=%d", $arr_goods_spec[ $i ]['spec_id']);

            if ($goods_id == 0) {
                $sql_error_info = 'delSpec-update_goods_goods';
                $res_spec_count=$db->query("SELECT COUNT(1) AS spec_count FROM goods_spec WHERE goods_id=".$arr_goods_spec[$i]['goods_id']." AND deleted=0");
                $db->execute("UPDATE goods_goods SET spec_count=%d WHERE goods_id =%d", array($res_spec_count[0]['spec_count'], $arr_goods_spec[ $i ]['goods_id']));
            }

            $sql_error_info = 'delSpec-update_api_goods_spec';
            $db->execute("UPDATE api_goods_spec SET match_target_type=0,match_target_id=0 WHERE is_deleted=0 AND match_target_type=1 AND match_target_id=%d", $arr_goods_spec[ $i ]['spec_id']);

            $arr_goods_spec[ $i ]['goods_type'] = 1;
            //$arr_goods_spec[$i]['goods_id']=$id;
            $arr_goods_spec[ $i ]['operator_id']  = $userId;
            $arr_goods_spec[ $i ]['operate_type'] = 32;
            $arr_goods_spec[ $i ]['message']      = '删除单品:' . $arr_goods_spec[ $i ]['spec_name'] . '---' . $arr_goods_spec[ $i ]['spec_no'];
            $arr_goods_spec[ $i ]['created']      = array('exp', 'NOW()');
            unset($arr_goods_spec[ $i ]['spec_no']);
            unset($arr_goods_spec[ $i ]['goods_no']);
            unset($arr_goods_spec[ $i ]['spec_name']);
        }
        $sql_error_info = 'delSpec-add_goods_spec_log';
        $db->table('goods_log')->addAll($arr_goods_spec);
        if ($goods_id != 0) {
            $sql_error_info = 'delSpec-update_goods_spec-delete';
            $db->execute("UPDATE goods_goods SET spec_count=0,deleted=" . time() . " WHERE goods_id = %d", $goods_id);
            $res_goods_goods = $db->table('goods_goods')->field('goods_name')->where(array('goods_id' => array('eq', $goods_id)))->find();
            $arr_goods_log   = array(
                'goods_type'   => 1,//1-货品 2-组合装
                'goods_id'     => $goods_id,
                'spec_id'      => 0,
                'operator_id'  => $userId,
                'operate_type' => 31,
                'message'      => '删除货品--' . $res_goods_goods['goods_name'],
                'created'      => array('exp', 'NOW()')
            );
            $sql_error_info  = 'delSpec-add_goods_goods_log';
            $db->table('goods_log')->add($arr_goods_log);
        }
        $db->commit();
    }

    /**
     * 验证单品商家编码
     * @param unknown $db
     * @param unknown $spec_no
     * @param unknown $sql_error_info
     */
    public function checkSpecNo(&$db, $spec_no, &$sql_error_info) {
        $sql_error_info = 'checkSpecNo-check_spec-goods_spec_deleted';
        $res_tmp        = $db->query("SELECT gs.spec_no,gs.deleted FROM goods_spec gs WHERE gs.spec_no='%s' AND gs.deleted=0 LIMIT 1", array($spec_no));
        if (!empty($res_tmp)) {
            //if ($res_tmp["0"]["deleted"] == 0) {
            E('商家编码已存在');
            /*} else {
                E("商家编码与已删除货品相同，请重新输入");
            }*/
        }

        $sql_error_info = 'checkSpecNo-check_spec-goods_suite';
        $res_tmp        = $db->query("SELECT suite_no,deleted  FROM goods_suite WHERE suite_no='%s' AND deleted=0 LIMIT 1", array($spec_no));
        if (!empty($res_tmp)) {
            //if ($res_tmp["0"]["deleted"] == 0) {
                E('商家编码已存在');
            /*} else {
                E("商家编码与已删除货品相同，请重新输入");
            }*/
        }
    }

    /**
     * 添加平台单品
     * @param unknown $db
     * @param unknown $spec_list
     * @param unknown $sql_error_info
     * 刷新库存同步策略
     */
    public function addApiSpec(&$db, $spec_list, &$sql_error_info) {
        $arr_api_spec   = array();
        $sql_error_info = 'update_api_goods_spec_info';
        foreach ($spec_list as $v) {
            $arr_api_spec['stock_syn_rule_id']    = set_default_value($v['rule_id'], -1);
            $arr_api_spec['stock_syn_rule_no']    = set_default_value($v['rule_no'], '');
            $arr_api_spec['stock_syn_warehouses'] = set_default_value($v['warehouse_list'], '');
            $arr_api_spec['stock_syn_mask']       = set_default_value($v['stock_flag'], 0);
            $arr_api_spec['stock_syn_percent']    = set_default_value($v['percent'], 100);
            $arr_api_spec['stock_syn_plus']       = set_default_value($v['plus_value'], 0);
            $arr_api_spec['stock_syn_min']        = set_default_value($v['min_stock'], 0);
            $arr_api_spec['is_auto_listing']      = set_default_value($v['is_auto_listing'], 1);
            $arr_api_spec['is_auto_delisting']    = set_default_value($v['is_auto_delisting'], 1);
            $arr_api_spec['is_disable_syn']       = set_default_value($v['is_disable_syn'], 1);
            $db->table('api_goods_spec')->where('rec_id=' . $v['rec_id'])->save($arr_api_spec);
        }
    }

    /**
     * 通过spec_id匹配平台单品
     * @param unknown $db
     * @param unknown $target_type
     * @param unknown $target_id
     * @param unknown $sql_error_info
     */
    public function matchApiGoodsSpecByTargetId(&$db, $target_type, $target_id, &$sql_error_info) {//FN_SPEC_NO_CONV未改写
        $sql_error_info = 'matchApiGoodsSpecByTargetId-get_api_goods_spec';
        $res_cfg_val    = get_config_value(array('sys_goods_match_concat_code', 'goods_match_split_char'), array(0, ''));
        $sys_goods_match_concat_code = $res_cfg_val['sys_goods_match_concat_code'];
        $db->execute("set @cfg_goods_match_concat_code=" . $res_cfg_val['sys_goods_match_concat_code'] . ", @cfg_goods_match_split_char= '" . $res_cfg_val['goods_match_split_char'] . "'");
        $res_api_spec_arr = $db->query("SELECT rec_id,match_target_type,match_target_id FROM api_goods_spec
        WHERE is_manual_match=0 AND is_deleted=0 AND match_target_type=%d AND match_target_id=%d", array($target_type, $target_id));
        foreach ($res_api_spec_arr as $v) {
            $sql_error_info = 'matchApiGoodsSpecByTargetId-update_api_goods_spec';
            $db->execute("UPDATE api_goods_spec gs LEFT JOIN goods_merchant_no mn ON mn.merchant_no=FN_SPEC_NO_CONV(IF($sys_goods_match_concat_code=2,gs.goods_id,gs.outer_id),IF($sys_goods_match_concat_code>=2,gs.rec_id,gs.spec_outer_id))
        AND mn.merchant_no<>'' SET gs.match_target_type=IFNULL(mn.type,0),gs.match_target_id=IFNULL(mn.target_id,0),gs.match_code=IFNULL(mn.merchant_no,''),
        gs.is_stock_changed=IF(gs.match_target_id,1,0),stock_change_count=stock_change_count+1 WHERE gs.rec_id=%d", $v['rec_id']);
            $v = $db->query("SELECT rec_id, match_target_type,match_target_id FROM api_goods_spec WHERE rec_id=%d;", $v['rec_id']);
            $v = $v[0];
            if ($v['match_target_type'] == 0) {
                continue;
            }
            if ($v['match_target_type'] == 1) {
                $sql_error_info = 'matchApiGoodsSpecByTargetId-update_api_goods_spec-1';
                $db->execute("UPDATE api_goods_spec ag,goods_spec gs,goods_goods gg,goods_class gc SET ag.brand_id=gg.brand_id,ag.class_id_path=gc.path
            WHERE ag.rec_id=%d AND ag.match_target_type=1 AND gs.spec_id=ag.match_target_id AND gg.goods_id=gs.goods_id AND gc.class_id=gg.class_id", $v['rec_id']);
            } else {
                $sql_error_info = 'matchApiGoodsSpecByTargetId-update_api_goods_spec-2';
                $db->execute("UPDATE api_goods_spec ag,goods_suite gs,goods_class gc SET ag.brand_id=gs.brand_id,ag.class_id_path=gc.path
            WHERE ag.rec_id=%d AND ag.match_target_type=2 AND gs.suite_id=ag.match_target_id AND gc.class_id=gs.class_id", $v['rec_id']);
            }
            $sql_error_info = 'matchApiGoodsSpecByTargetId-update_api_trade';
            $db->execute("UPDATE api_trade ax,api_trade_order ato,api_goods_spec ag SET ato.is_invalid_goods=0,ax.bad_reason=(bad_reason&~1)
            WHERE ato.is_invalid_goods=1 AND ato.`platform_id` = ag.`platform_id` AND ato.`goods_id` = ag.`goods_id` AND ato.`spec_id` = ag.`spec_id`
            AND ato.status <=30 AND ax.platform_id=ato.`platform_id` AND ax.tid=ato.tid AND ag.rec_id=%d", $v['rec_id']);

            $sql_error_info = 'matchApiGoodsSpecByTargetId-get_cfg_stock_sync_rule';
            $res_tmp_arr    = $db->query("SELECT ag.rec_id,rule.rec_id rule_id,rule.priority,rule.rule_no,rule.warehouse_list,
            rule.stock_flag, rule.percent,rule.plus_value,rule.min_stock,rule.is_auto_listing,rule.is_auto_delisting,rule.is_disable_syn
            FROM api_goods_spec ag
            LEFT JOIN cfg_stock_sync_rule rule
            ON (rule.is_disabled=0 AND FIND_IN_SET(rule.class_id,ag.class_id_path) AND FIND_IN_SET(ag.shop_id, rule.shop_list)AND
            ag.brand_id=IF(rule.brand_id=-1,ag.`brand_id`,rule.`brand_id`))
            WHERE ag.rec_id=%d AND ag.stock_syn_rule_id<>0 ORDER BY rule.priority DESC LIMIT 1", $v['rec_id']);
            $this->addApiSpec($db, $res_tmp_arr, $sql_error_info);
            $operator_id = get_operator_id();
            M("sys_other_log")->execute("INSERT INTO sys_other_log(`type`,operator_id,`data`,message)
                SELECT 14,{$operator_id},{$v['rec_id']},CONCAT('平台货品关联系统货品，系统货品商家编码为:',gmn.merchant_no)
                FROM goods_merchant_no gmn WHERE gmn.type = %d AND gmn.target_id = %d;", array($v['match_target_type'], $v['match_target_id']));
        }
    }

    /**
     * 通过merchant_no匹配平台单品
     * @param unknown $db
     * @param unknown $merchant_no
     * @param unknown $sql_error_info
     */
    public function matchApiGoodsSpecByMerchantNo(&$db, $merchant_no, &$sql_error_info) {
        $res_cfg_val = get_config_value(array('sys_goods_match_concat_code', 'goods_match_split_char'), array(0, ''));
        $sys_goods_match_concat_code = $res_cfg_val['sys_goods_match_concat_code'];

        $sql_error_info = 'matchApiGoodsSpecByMerchantNo-set_cfg_val';
        $db->execute("set @cfg_goods_match_concat_code=%d, @cfg_goods_match_split_char= '%s'", array($res_cfg_val['sys_goods_match_concat_code'], $res_cfg_val['goods_match_split_char']));

        $sql_error_info   = 'matchApiGoodsSpecByMerchantNo-get_api_goods_spec';
        $res_api_spec_arr = $db->query("SELECT rec_id,match_target_type,match_target_id FROM api_goods_spec WHERE is_manual_match=0 AND is_deleted=0
                  AND FN_SPEC_NO_CONV(IF($sys_goods_match_concat_code=2,goods_id,outer_id),IF($sys_goods_match_concat_code>=2,rec_id,spec_outer_id))='%s'", array($merchant_no));
        foreach ($res_api_spec_arr as $v) {
            $sql_error_info = 'matchApiGoodsSpecByMerchantNo-update_api_goods_spec';
            $db->execute("UPDATE api_goods_spec gs LEFT JOIN goods_merchant_no mn ON mn.merchant_no='%s' AND mn.merchant_no<>''
                  SET gs.match_target_type=IFNULL(mn.type,0),gs.match_target_id=IFNULL(mn.target_id,0),gs.match_code=IFNULL(mn.merchant_no,''),
                  gs.is_stock_changed=IF(gs.match_target_id,1,0),stock_change_count=stock_change_count+1 WHERE gs.rec_id=%d", array($merchant_no, $v['rec_id']));
            $v = $db->query("SELECT rec_id, match_target_type,match_target_id FROM api_goods_spec WHERE rec_id=%d;", $v['rec_id']);
            $v = $v[0];
            if ($v['match_target_type'] == 0) {
                continue;
            }
            if ($v['match_target_type'] == 1) {
                $sql_error_info = 'matchApiGoodsSpecByMerchantNo-update_api_goods_spec-1';
                $db->execute("UPDATE api_goods_spec ag,goods_spec gs,goods_goods gg,goods_class gc SET ag.brand_id=gg.brand_id,ag.class_id_path=gc.path
                  WHERE ag.rec_id=%d AND ag.match_target_type=1 AND gs.spec_id=ag.match_target_id AND gg.goods_id=gs.goods_id AND gc.class_id=gg.class_id", array($v['rec_id']));
            } else {
                $sql_error_info = 'matchApiGoodsSpecByMerchantNo-update_api_goods_spec-2';
                $db->execute("UPDATE api_goods_spec ag,goods_suite gs,goods_class gc SET ag.brand_id=gs.brand_id,ag.class_id_path=gc.path
                  WHERE ag.rec_id=%d AND ag.match_target_type=2 AND gs.suite_id=ag.match_target_id AND gc.class_id=gs.class_id", array($v['rec_id']));
            }
            $sql_error_info = 'matchApiGoodsSpecByMerchantNo-update_api_trade';
            $db->execute("UPDATE api_trade ax,api_trade_order ato,api_goods_spec ag SET ato.is_invalid_goods=0,ax.bad_reason=(bad_reason&~1)
                  WHERE ato.is_invalid_goods=1 AND ato.`platform_id` = ag.`platform_id` AND ato.`goods_id` = ag.`goods_id` AND ato.`spec_id` = ag.`spec_id`
                  AND ato.status <=30 AND ax.platform_id=ato.`platform_id` AND ax.tid=ato.tid AND ag.rec_id=%d", array($v['rec_id']));

            $sql_error_info = 'matchApiGoodsSpecByMerchantNo-get_cfg_stock_sync_rule';
            $res_tmp_arr    = $db->query("SELECT  ag.rec_id,rule.rec_id AS rule_id,rule.priority,rule.rule_no,rule.warehouse_list,rule.stock_flag, rule.percent,
                  rule.plus_value,rule.min_stock,rule.is_auto_listing,rule.is_auto_delisting,rule.is_disable_syn
                  FROM api_goods_spec ag
                  LEFT JOIN cfg_stock_sync_rule rule ON (rule.is_disabled=0 AND FIND_IN_SET(rule.class_id,ag.class_id_path) AND FIND_IN_SET(ag.shop_id, rule.shop_list)
                  AND ag.brand_id=IF(rule.brand_id=-1,ag.`brand_id`,rule.`brand_id`))
                  WHERE ag.rec_id=%d AND ag.stock_syn_rule_id<>0 ORDER BY rule.priority DESC LIMIT 1", array($v['rec_id']));
            $this->addApiSpec($db, $res_tmp_arr, $sql_error_info);
            $operator_id = get_operator_id();
            M("sys_other_log")->execute("INSERT INTO sys_other_log(`type`,operator_id,`data`,message)
                SELECT 14,{$operator_id},{$v['rec_id']},CONCAT('平台货品关联系统货品，系统货品商家编码为:',gmn.merchant_no)
                FROM goods_merchant_no gmn WHERE gmn.type = %d AND gmn.target_id = %d;", array($v['match_target_type'], $v['match_target_id']));
        }
    }

    public function getDatagridStr() {
        $list           = UtilDB::getCfgList(array('unit'));
        $unit           = $list['unit'];
        $formatter_unit = UtilTool::array2dict($unit);
        $large_type = array(array("id"=>"0","name"=>"非大件",'selected'=>'true'),array("id"=>"1","name"=>"普通大件"),array("id"=>"2","name"=>"独立大件"));
        $datagrid       = "
				{field:'spec_id',hidden:true},    
				{field:'img_url',title:'图片',width:80,editor:'textbox',formatter:formatter.print_img},
				{field:'spec_no',title:'商家编码',width:100,editor:{type:'validatebox',options:{required:true,validType:['spec_no_unique','check_merchant_no']}}},
				{field:'spec_name',title:'规格名称',width:100,editor:'textbox'},
				{field:'spec_code',title:'规格码',width:80,editor:'textbox'},

				{field:'barcode',title:'主条码',width:80,editor:'textbox',editor:{type:'validatebox'}},
				{field:'retail_price',title:'零售价',width:60,align:'right',editor:{type:'numberbox',options:{precision:4,min:0}}},
				{field:'market_price',title:'市场价',width:60,align:'right',editor:{type:'numberbox',options:{precision:4,min:0}}},
				{field:'lowest_price',title:'最低价',width:60,align:'right',editor:{type:'numberbox',options:{precision:4,min:0}}},
				{field:'validity_days',title:'有效期',width:60,editor:'numberbox'},
			    {field:'is_allow_neg_stock',title:'允许负库存出库',width:130,align:'center',editor:{type:'checkbox',options:{on:'1',off:'0'}},formatter:formatter.boolen},
				{field:'length',title:'长(CM)',width:60,editor:{type:'numberbox',options:{precision:4,min:0}}},
				{field:'width',title:'宽(CM)',width:60,editor:{type:'numberbox',options:{precision:4,min:0}}},
				{field:'height',title:'高(CM)',width:60,editor:{type:'numberbox',options:{precision:4,min:0}}},
				{field:'weight',title:'重量(kg)',width:60,editor:{type:'numberbox',options:{precision:4,min:0}}},
				{field:'unit',title:'单位',width:80,editor:{type:'combobox',options:{editable:false,valueField:'id', textField:'name',required:true,data:" . json_encode($unit) . "}},formatter:function(value,row){var data=JSON.parse('" . json_encode($formatter_unit) . "'); return data[row.unit]; }},
				{field:'large_type',title:'大件类别',width:60,editor:{type:'combobox',options:{editable:false,valueField:'id', textField:'name',data:".json_encode($large_type)."}},formatter:formatter.large_type},
				{field:'is_not_need_examine',title:'无需验货',width:60,align:'center',editor:{type:'checkbox',options:{on:'1',off:'0'}},formatter:formatter.boolen},
				{field:'remark',title:'备注',width:60,editor:'textbox'},
				{field:'prop1',title:'自定义1',width:60,editor:'textbox'},
				{field:'prop2',title:'自定义2',width:60,editor:'textbox'},
				{field:'prop3',title:'自定义3',width:60,editor:'textbox'},
				{field:'prop4',title:'自定义4',width:60,editor:'textbox'}
			";
        //{field:'tax_rate',title:'税率',width:60,editor:{type:'numberbox',options:{precision:4}}}
        return $datagrid;
    }

    public function checkGoods($value, $name = "goods_id") {
        $map[ $name ]   = $value;
        $map["deleted"] = 0;
        try {
            $result = $this->field("goods_id")->where($map)->find();
            if (!empty($result)) return true;
        } catch (\Exception $e) {
            \Think\Log::write($e->getMessage());
        }
        return false;
    }


    //导入单品数据
    public function importSpec($data,&$error_list,$line) {
        try{
            $goodsModel     = M("goods_goods");
            $specModel      = M("goods_spec");
            $classModel     = M("goods_class");
            $brandModel     = M("goods_brand");
            $unitModel      = M("cfg_goods_unit");
            $flagModel      = M("cfg_flags");
            $merchanNoModel = M("goods_merchant_no");
            $barcodeModel   = M("goods_barcode");
            $logModel       = M("goods_log");
            $goods_match    = true;//是否进行货品匹配
            $api_goods_spec_count = M('api_goods_spec')->count();
            if($api_goods_spec_count>50000)
            {
                $goods_match = false; //平台货品数量大于5万不走匹配
            }

            foreach($data as $k=>$v)
            {
                $goods          = $v["goods"];
                $spec           = $v["spec"];
                $class          = $v["class"];
                $brand          = $v["brand"];
                $unit           = $v["unit"];
                $flag           = $v["flag"];
                $merchant_no    = $v["merchant_no"];
                $barcode        = $v["barcode"];
                //分拆原来的数据并建立对应的数据库连接

                if ($spec["lowest_price"] < 0 || $spec["retail_price"] < 0 || $spec["market_price"] < 0 || $spec["validity_days"] < 0) {
                    $error_list[] = array('id'=>$k+2+$line,'result'=>'导入失败','message'=>'货品的最低价、零售价、市场价和有效期不能为负数');
                    continue;
                }
                if ($spec["length"] < 0 || $spec["width"] < 0 || $spec["height"] < 0 || $spec["weight"] < 0) {
                    $error_list[] = array('id'=>$k+2+$line,'result'=>'导入失败','message'=>'货品的长、宽、高和重量不能为负数');
                    continue;
                }
                //检查商家编码是否为空
                if (!isset($spec["spec_no"]) || $spec["spec_no"] == "") {
                    $error_list[] = array('id'=>$k+2+$line,'result'=>'导入失败','message'=>'商家编码不能为空');
                    continue;
                }
                //检查货品编号是否为空
                if (!isset($goods["goods_no"]) || $goods["goods_no"] == "") {
                    $error_list[] = array('id'=>$k+2+$line,'result'=>'导入失败','message'=>'货品编码不能为空');
                    continue;
                }
                //检查商家编码是否存在
                //$sql    = "SELECT COUNT(1) AS total FROM goods_spec gs WHERE gs.spec_no={$merchant_no['merchant_no']}";
                $result = $merchanNoModel->field("COUNT(1) AS total")->where(array("merchant_no" => $merchant_no['merchant_no']))->select();
                if ($result[0]["total"]) {
                    $error_list[] = array('id'=>$k+2+$line,'result'=>'导入失败','message'=>'商家编码已存在');
                    continue;
                }
                //检查货品条码是否存在
                //$sql    = "SELECT COUNT(1) AS total FROM goods_barcode gb WHERE gb.barcode={$barcode['barcode']}";
                /*$result = $barcodeModel->field("COUNT(1) AS total")->where(array("barcode" => $barcode["barcode"]))->select();
                if ($result[0]["total"]) {
                    E("该条形码已存在", 1);
                }*/
                //查询class_id，无则设为0
                //$sql              = "SELECT gc.class_id FROM goods_class gc WHERE gc.class_name={$class['class_name']}";
                $result            = $classModel->field("COUNT(1) AS total,class_id,is_leaf")->where(array("class_name" => $class["class_name"]))->select();
                $goods["class_id"] = isset($result[0]["class_id"]) ? $result[0]["class_id"] : 0;
                if ($result[0]["is_leaf"] == 0) $goods["class_id"] = 0;
                //查询brand_id，无则设为0
                //$sql              = "SELECT gb.brand_id FROM goods_brand gb WHERE gb.brand_name={$brand['brand_name']}";
                $result            = $brandModel->field("COUNT(1) AS total,brand_id")->where(array("brand_name" => $brand["brand_name"]))->select();
                $goods["brand_id"] = isset($result[0]["brand_id"]) ? $result[0]["brand_id"] : 0;
                //查询标记，无则设为0
                //$sql              = "SELECT cf.flag_id FROM cfg_flags cf WHERE cf.flag_name={$flag['flag_name']}";
                $result           = $flagModel->field("COUNT(1) AS total,flag_id")->where(array("flag_name" => $flag["flag_name"]))->select();
                $goods["flag_id"] = isset($result[0]["flag_id"]) ? $result[0]["flag_id"] : 0;
                //查询单位，无则设为0
                //$sql           = "SELECT cgu.rec_id FROM cfg_goods_unit cgu WHERE cgu.name={$unit['name']}";
                $result        = $unitModel->field("COUNT(1) AS total,rec_id")->where(array("name" => $unit["name"]))->select();
                $goods["unit"] = isset($result[0]["rec_id"]) ? $result[0]["rec_id"] : 0;
                //设置货品类型
                switch ($goods["goods_type"]) {
                    case "销售商品":
                        $goods["goods_type"] = 1;
                        break;
                    /*case "原材料":
                        $goods["goods_type"] = 2;
                        break;
                    case "包装":
                        $goods["goods_type"] = 3;
                        break;
                    case "周转材料":
                        $goods["goods_type"] = 4;
                        break;
                    case "虚拟商品":
                        $goods["goods_type"] = 5;
                        break;
                    case "固定资产":
                        $goods["goods_type"] = 6;
                        break;*/
                    case "其他":
                        $goods["goods_type"] = 0;
                        break;
                    default:
                        $goods["goods_type"] = 1;
                }
                switch ($spec['large_type']){
                    case '非大件':
                        $spec['large_type'] = 0;
                        break;
                    case '普通大件':
                        $spec['large_type'] = 1;
                        break;
                    case '独立大件':
                        $spec['large_type'] = 2;
                        break;
                    default:
                        $spec['large_type'] = 0;
                }
                switch ($spec['is_allow_neg_stock']){
                    case '是':
                        $spec['is_allow_neg_stock'] = 1;
                        break;
                    case '否':
                        $spec['is_allow_neg_stock'] = 0;
                        break;
                    default:
                        $spec['is_allow_neg_stock'] = 0;
                }
                switch ($spec['is_hotcake']){
                    case '是':
                        $spec['is_hotcake'] = 1;
                        break;
                    default:
                        $spec['is_hotcake'] = 0;
                }
                //检查货品是否存在
                $result = $goodsModel->field("COUNT(1) AS total,goods_id")->where(array("goods_no" => $goods["goods_no"], "deleted" => 0))->select();
                if (!$result["0"]["total"]) {
                    //插入货品
                    $goods_id = $goodsModel->data($goods)->add();
                    //插入货品日志
                    $arr_goods_log = array(
                        "goods_id"     => $goods_id,
                        "goods_type"   => $goods["goods_type"],
                        "spec_id"      => 0,
                        "operator_id"  => get_operator_id(),
                        "operate_type" => 11,
                        "message"      => "导入货品：" . $goods["goods_name"],
                        "created"      => date("Y-m-d H:i:s", time())
                    );
                    $logModel->data($arr_goods_log)->add();
                } else {
                    $goods_id = $result[0]["goods_id"];
                }
                //插入单品
                $spec["goods_id"] = $goods_id;
                $spec_id          = $specModel->data($spec)->add();
                //更新货品规格数目
                $goodsModel->execute("UPDATE goods_goods SET spec_count=(SELECT COUNT(spec_id) FROM goods_spec WHERE goods_id=%d AND deleted=0) WHERE goods_id=%d", array($goods_id, $goods_id));
                //插入条码
                if ($barcode["barcode"] != "") {
                    $barcode["type"]      = 1;
                    $barcode["target_id"] = $spec_id;
                    $barcode["tag"]       = get_seq("goods_barcode");
                    $barcode["is_master"] = 1;
                    $barcode["created"]   = date("Y-m-d H:i:s", time());
                    $barcodeModel->data($barcode)->add();
                }
                //插入商家编码
                $merchant_no["type"]      = 1;
                $merchant_no["target_id"] = $spec_id;
                $merchant_no["created"]   = date("Y-m-d H:i:s", time());
                $merchanNoModel->data($merchant_no)->add();
                //刷新平台货品匹配，暂时跳过  (如果货品数量大的话会特别慢,不建议开启)
                if($goods_match){
                    $platformGoodsDB = M("api_goods_spec");
                    $this->matchApiGoodsSpecByMerchantNo($platformGoodsDB, $merchant_no["merchant_no"]);
                }
                //初始化库存
                D('Stock/StockSpec')->initStockSpec($spec_id);
                //插入单品日志
                $arr_goods_log = array(
                    "goods_id"     => $goods_id,
                    "spec_id"      => $spec_id,
                    "operator_id"  => get_operator_id(),
                    "operate_type" => 12,
                    "message"      => "导入单品：" . $spec["spec_name"],
                    "created"      => date("Y-m-d H:i:s", time())
                );
                $logModel->data($arr_goods_log)->add();
            }
        }catch (\PDOException $e) {
            Log::write('importGoods PDO ERR:'.print_r($e->getMessage(),true));
            //不抛出异常，剩余任务继续执行
            //SE($e->getMessage());
        } catch (\Exception $e) {
            Log::write('importGoods ERR:'.print_r($e->getMessage(),true));
            SE($e->getMessage());
        }
        unset($goodsModel);
        unset($barcodeModel);
        unset($brandModel);
        unset($classModel);
        unset($logModel);
        unset($merchanNoModel);
        unset($logModel);
        unset($classModel);
        unset($unitModel);
    }

    public function updateSpec($data,&$error_list,$line){
        try{
            //分拆原来的数据并建立对应的数据库连接
            $goodsModel     = M("goods_goods");
            $specModel      = M("goods_spec");
            $classModel     = M("goods_class");
            $brandModel     = M("goods_brand");
            $unitModel      = M("cfg_goods_unit");
            $flagModel      = M("cfg_flags");
            $barcodeModel   = M("goods_barcode");
            $logModel       = M("goods_log");
            foreach($data as $k=>$v)
            {
                $goods          = $v["goods"];
                $spec           = $v["spec"];
                $class          = $v["class"];
                $brand          = $v["brand"];
                $unit           = $v["unit"];
                $flag           = $v["flag"];
                $merchant_no    = $v["merchant_no"];
                $barcode        = $v["barcode"];
                if ($spec["lowest_price"] < 0 || $spec["retail_price"] < 0 || $spec["market_price"] < 0 || $spec["validity_days"] < 0) {
                    $error_list[] = array('id'=>$k+2+$line,'result'=>'更新失败','message'=>'货品的最低价、零售价、市场价和有效期不能为负数');
                    continue;
                }
                if ($spec["length"] < 0 || $spec["width"] < 0 || $spec["height"] < 0 || $spec["weight"] < 0) {
                    $error_list[] = array('id'=>$k+2+$line,'result'=>'更新失败','message'=>'货品的长、宽、高和重量不能为负数');
                    continue;
                }
                //检查商家编码是否为空
                if (!isset($spec["spec_no"]) || $spec["spec_no"] == "") {
                    $error_list[] = array('id'=>$k+2+$line,'result'=>'更新失败','message'=>'商家编码不能为空');
                    continue;
                }
                //检查货品编号是否为空
                if (!isset($goods["goods_no"]) || $goods["goods_no"] == "") {
                    $error_list[] = array('id'=>$k+2+$line,'result'=>'更新失败','message'=>'货品编码不能为空');
                    continue;
                }
                $old_spec = $specModel->where(array("spec_no" => $merchant_no['merchant_no'],'deleted'=>'0'))->find();
                if (!$old_spec) {
                    $error_list[] = array('id'=>$k+2+$line,'result'=>'更新失败','message'=>'商家编码不存在');
                    continue;
                }
                //查询class_id，无则设为0 如果为空则不更新
                if($class['class_name']!='')
                {
                    $result            = $classModel->field("COUNT(1) AS total,class_id,is_leaf")->where(array("class_name" => $class["class_name"]))->select();
                    $goods["class_id"] = isset($result[0]["class_id"]) ? $result[0]["class_id"] : 0;
                    if ($result[0]["is_leaf"] == 0) $goods["class_id"] = 0;
                }
                //查询brand_id，无则设为0
                if($brand["brand_name"]!='')
                {
                    $result            = $brandModel->field("COUNT(1) AS total,brand_id")->where(array("brand_name" => $brand["brand_name"]))->select();
                    $goods["brand_id"] = isset($result[0]["brand_id"]) ? $result[0]["brand_id"] : 0;
                }
                //查询标记，无则设为0
                if($flag["flag_name"]!='')
                {
                    $result           = $flagModel->field("COUNT(1) AS total,flag_id")->where(array("flag_name" => $flag["flag_name"]))->select();
                    $goods["flag_id"] = isset($result[0]["flag_id"]) ? $result[0]["flag_id"] : 0;
                }
                if($unit["name"]!='')
                {
                    //查询单位，无则设为0
                    $result        = $unitModel->field("COUNT(1) AS total,rec_id")->where(array("name" => $unit["name"]))->select();
                    $goods["unit"] = isset($result[0]["rec_id"]) ? $result[0]["rec_id"] : 0;
                }
                switch ($goods["goods_type"]) {
                    case "销售商品":
                        $goods["goods_type"] = 1;
                        break;
                    case "其他":
                        $goods["goods_type"] = 0;
                        break;
                    default:
                        $goods["goods_type"] = 1;
                }
                switch ($spec['large_type']){
                    case '非大件':
                        $spec['large_type'] = 0;
                        break;
                    case '普通大件':
                        $spec['large_type'] = 1;
                        break;
                    case '独立大件':
                        $spec['large_type'] = 2;
                        break;
                    default:
                        $spec['large_type'] = 0;
                }
                switch ($spec['is_allow_neg_stock']){
                    case '是':
                        $spec['is_allow_neg_stock'] = 1;
                        break;
                    case '否':
                        $spec['is_allow_neg_stock'] = 0;
                        break;
                    default:

                        $spec['is_allow_neg_stock'] = 0;
                }
                switch ($spec['is_hotcake']){
                    case '是':
                        $spec['is_hotcake'] = 1;
                        break;
                    default:
                        $spec['is_hotcake'] = 0;
                }
                //检查更新后的货品编码是否存在
                $import_goods_id = $goodsModel->field('goods_id')->where(array('goods_no'=>$goods['goods_no']))->find();
                //存在往这个goods_goods 下更新货品信息，不存在的话先插入goods_goods
                if($import_goods_id){
                    if($goods['goods_name']==='')   unset($goods['goods_name']);
                    if($goods['short_name']==='')   unset($goods['short_name']);
                    if($goods['alias']==='')        unset($goods['alias']);
                    if($goods['goods_type']==='')   unset($goods['goods_type']);
                    if($goods['origin']==='')       unset($goods['origin']);
                    $goods['deleted'] = 0;
                    $goodsModel->data($goods)->where(array('goods_id'=>$import_goods_id['goods_id']))->save();
                    $goods_id = $import_goods_id['goods_id'];
                }else{
                    $goods_id = $goodsModel->data($goods)->add();
                }
                //更新完以后检查下该货品档案下是否还有单品，没有的话删除,有的话该货品档案单品数量扣减
                $spec_num=$goodsModel->field('spec_count')->where(array('goods_id'=>$old_spec['goods_id']))->find();
                if($spec_num['spec_count']>1){
                    $goodsModel->execute("UPDATE goods_goods SET spec_count = spec_count-1 WHERE goods_id=%d",$old_spec['goods_id']);
                }else{
                    if($goods_id!=$old_spec['goods_id']){
                        $goodsModel->execute("UPDATE goods_goods SET deleted = ".time()." WHERE goods_id=%d",$old_spec['goods_id']);
                    }
                }
                //插入货品日志
                $arr_goods_log = array(
                    "goods_id"     => $goods_id,
                    "goods_type"   => $goods["goods_type"],
                    "spec_id"      => 0,
                    "operator_id"  => get_operator_id(),
                    "operate_type" => 42,
                    "message" => "导入更新货品：" . $goods["goods_name"],
                    "created"      => date("Y-m-d H:i:s", time())
                );
                $logModel->data($arr_goods_log)->add();
                //更新单品信息
                //不填写就不更新  为原来的值
                if($spec['img_url']==='')           unset($spec['img_url']);
                if($spec['spec_name']==='')         unset($spec['spec_name']);
                if($spec['spec_code']==='')         unset($spec['spec_code']);
                if($spec['barcode']==='')           unset($spec['barcode']);
                if(empty($spec['lowest_price']))    unset($spec['lowest_price']);
                if(empty($spec['retail_price']))    unset($spec['retail_price']);
                if(empty($spec['market_price']))    unset($spec['market_price']);
                if(empty($spec['validity_days']))   unset($spec['validity_days']);
                if(empty($spec['length']))          unset($spec['length']);
                if(empty($spec['width']))           unset($spec['width']);
                if(empty($spec['height']))          unset($spec['height']);
                if(empty($spec['weight']))          unset($spec['weight']);
                if(empty($spec['remark']))          unset($spec['remark']);
                if(empty($spec['prop1']))          unset($spec['prop1']);
                if(empty($spec['prop2']))          unset($spec['prop2']);
                if(empty($spec['prop3']))          unset($spec['prop3']);
                if(empty($spec['prop4']))          unset($spec['prop4']);
                if(empty($spec['is_allow_neg_stock'])) unset($spec['is_allow_neg_stock']);
                if(empty($spec['is_hotcake'])) unset($spec['is_hotcake']);
                if(empty($spec['large_type']))      unset($spec['large_type']);
                $spec["goods_id"] = $goods_id;
                $specModel->data($spec)->where(array('spec_no'=>$spec['spec_no']))->save();
                $spec_id = $specModel->field('spec_id')->where(array('spec_no'=>$spec['spec_no']))->find();
                //更新货品规格数目
                $goodsModel->execute("UPDATE goods_goods SET spec_count=(SELECT COUNT(spec_id) FROM goods_spec WHERE goods_id=%d AND deleted=0) WHERE goods_id=%d", array($goods_id, $goods_id));
                //更新条码
                if($barcode['barcode'] !=''){
                    $barcodeModel->where(array("type" => 1, "target_id" => $spec_id['spec_id'], "is_master" => 1))->delete();
                    $barcode_res=$barcodeModel->where(array("type" => 1, "target_id" => $spec_id['spec_id'],'barcode'=>$barcode['barcode']))->count();
                    if($barcode_res){
                        $barcodeModel->where(array("type" => 1, "target_id" => $spec_id['spec_id'],'barcode'=>$barcode['barcode']))->save(array('is_master'=>1,"modified"=>date("Y-m-d H:i:s", time())));
                    }else{
                        $barcode["type"]      = 1;
                        $barcode["target_id"] = $spec_id['spec_id'];
                        $barcode["tag"]       = get_seq("goods_barcode");
                        $barcode["is_master"] = 1;
                        $barcodeModel->data($barcode)->add();
                    }
                }
                //插入单品日志
                $arr_goods_log = array(
                    "goods_id"     => $goods_id,
                    "spec_id"      => $spec_id['spec_id'],
                    "operator_id"  => get_operator_id(),
                    "operate_type" => 43,//修改单品
                    "message"      => "导入更新单品：" . $spec["spec_name"],
                    "created"      => date("Y-m-d H:i:s", time())
                );
                $logModel->data($arr_goods_log)->add();
            }

        }catch (\PDOException $e){
            Log::write('updateSpec PDO ERR:'.print_r($e->getMessage(),true));
            //不抛出异常，继续执行
            //SE($e->getMessage());
        }catch (\Exception $e){
            Log::write('updateSpec ERR:'.print_r($e->getMessage(),true));
            SE($e->getMessage());
        }
        unset($goodsModel);
        unset($barcodeModel);
        unset($brandModel);
        unset($classModel);
        unset($logModel);
        unset($logModel);
        unset($classModel);
        unset($unitModel);

    }

    //刷新单品的库存同步规则
    public function refreshStockSyncRule($goods_id) {
        try {
            $where = " AND true";
            $spec_priority = get_config_value('spec_stocksyn_priority', 0);
            if($spec_priority == 1){
                $where .= ' AND ag.stock_syn_rule_id<>0';
            }
            $goods_id = addslashes($goods_id);
            $sql      = "UPDATE api_goods_spec gs,
		(SELECT * FROM
			(
			SELECT ag.rec_id,rule.rec_id rule_id,rule.priority,rule.rule_no,rule.warehouse_list,rule.stock_flag,
			rule.percent,rule.plus_value,rule.min_stock,rule.is_auto_listing,rule.is_auto_delisting,rule.is_disable_syn
			FROM api_goods_spec ag
			LEFT JOIN cfg_stock_sync_rule rule ON ( rule.is_disabled=0 AND FIND_IN_SET(rule.class_id,ag.class_id_path) AND FIND_IN_SET(ag.shop_id, rule.shop_list)AND ag.brand_id=IF(rule.brand_id=-1,ag.`brand_id`,rule.`brand_id`))
			INNER JOIN goods_spec tgs ON (tgs.spec_id=ag.match_target_id AND ag.match_target_type=1 AND ag.is_deleted=0)
			WHERE tgs.goods_id={$goods_id} {$where}  ORDER BY rule.priority DESC
			)
			_ALIAS_ GROUP BY rec_id
		 ) da
		SET
			gs.stock_syn_rule_id=IFNULL(da.rule_id,-1),
			gs.stock_syn_rule_no=IFNULL(da.rule_no,''),
			gs.stock_syn_warehouses=IFNULL(da.warehouse_list,''),
			gs.stock_syn_mask=IFNULL(da.stock_flag,0),
			gs.stock_syn_percent=IFNULL(da.percent,100),
			gs.stock_syn_plus=IFNULL(da.plus_value,0),
			gs.stock_syn_min=IFNULL(da.min_stock,0),
			gs.is_auto_listing=IFNULL(da.is_auto_listing,1),
			gs.is_auto_delisting=IFNULL(da.is_auto_delisting,1),
			gs.is_disable_syn=IFNULL(da.is_disable_syn,1),
			gs.is_stock_changed=1
		WHERE gs.rec_id=da.rec_id;";
            $this->execute($sql);
        } catch (\Exception $e) {
            E($e->getMessage());
        }
    }

    public function getImportStockGoods($warehouse_id,&$result){
        try{
            $goods_spec_db = D('GoodsSpec');
            $api_spec_db = D('PlatformGoods');
            $warehouse_db = M('cfg_warehouse');
            $stock_spec_db = D('StockSpec');
            $warehouse_name = $warehouse_db->field('name')->where(array('warehouse_id'=>$warehouse_id))->find();
            $stock = $stock_spec_db->alias('ss')->field('rec_id')->join('left join goods_spec gs on ss.spec_id=gs.spec_id')->where(array('gs.deleted'=>0))->select();
            if(count($stock)>0){
                SE('库存数据不为空，无法进行初始化库存');
            }
            if(!isset($warehouse_name['name'])){
                SE('所选仓库不存在');
            }
            $regular_api_goods = array();
            $goods_spec_spec_no = $goods_spec_db->field('spec_no,spec_name')->where(array('deleted'=>0))->select();
            if(empty($goods_spec_spec_no)){
                SE('需要先添加货品才可以进行初始化库存操作');
            }
            //取得货品商家编码不为空的数据
            $where         = empty($id_list) ? '' : array('rec_id' => array('in', $id_list));
            $all_api_goods = $api_spec_db->alias('ags')->field('ags.rec_id AS id,ags.shop_id,ags.spec_id,ags.spec_code,ags.goods_name,ags.spec_name,ags.outer_id,ags.spec_outer_id,ags.price,ags.pic_url,ags.barcode,ags.stock_num')->where($where)->select();
            foreach ($all_api_goods as $k => $v) {
                if ($v['outer_id'] == '') {
                    $list[] = array('goods_name' => $v['goods_name'], 'spec_no' => $v['outer_id'], 'info' => '货品商家编码为空，请填写');
                } else {
                    $regular_api_goods[] = $v;
                }
            }
            //取得规格编码相同的数据
            $repeat_arr = array();
            //$map = array();
            foreach ($regular_api_goods as $k => $v) {
                if ($v['spec_outer_id'] != '') {
                    if (in_array($v['spec_outer_id'], $repeat_arr)) {
                        $list[] = array('spec_no' => $v['outer_id'], 'goods_name' => $v['goods_name'], 'info' => '该商家编码货品的规格编码对应多个单品');
                        unset($regular_api_goods[$k]);
                    }
                }
            }
            sort($regular_api_goods);
            //获取设置信息
            $sys_goods_match_concat_code = get_config_value("sys_goods_match_concat_code", 0);
            $goods_match_split_char      = get_config_value("goods_match_split_char", "");
            $sql                         = "SET @cfg_goods_match_concat_code=\"{$sys_goods_match_concat_code}\"";
            $this->execute($sql);
            $sql = "SET @cfg_goods_match_split_char=\"{$goods_match_split_char}\"";
            $this->execute($sql);
            $arr_rec_id = array();
            if(!empty($regular_api_goods)){
                foreach ($regular_api_goods as $v) {
                    $arr_rec_id[] = $v['id'];
                }
                $arr_rec_id      = join(',', $arr_rec_id);
                $merchant_no_arr = $api_spec_db->alias('gs')->field("gs.rec_id,stock_num,price,FN_SPEC_NO_CONV(IF($sys_goods_match_concat_code=2,gs.goods_id,gs.outer_id),IF($sys_goods_match_concat_code=2,gs.rec_id,gs.spec_outer_id)) merchant_no")->where(array('gs.is_deleted' => 0, 'gs.rec_id' => array('in', $arr_rec_id)))->select();
            }
            //获得导入库存的数组
            $import_stock_arr = array();
            foreach($goods_spec_spec_no as $k=>$v){
                foreach($merchant_no_arr as $i=>$j){
                    if($v['spec_no']==$j['merchant_no']){
                        $goods_spec_spec_no[$k]['stock_num'] = $j['stock_num'];
                        $goods_spec_spec_no[$k]['price'] = $j['price'];
                        break;
                    }else{
                        $goods_spec_spec_no[$k]['stock_num'] = 0;
                        $goods_spec_spec_no[$k]['price'] = 0;
                    }
                }
            }
            foreach($goods_spec_spec_no as $k=>$v){
                $import_stock_arr[$k]['spec_no'] = $v['spec_no'];
                $import_stock_arr[$k]['warehouse_name'] = $warehouse_name['name'];
                $import_stock_arr[$k]['num'] = $v['stock_num'];
                $import_stock_arr[$k]['price'] = $v['price'];
                $import_stock_arr[$k]['position_no']='';
                $import_stock_arr[$k]['line'] = $k;
                $import_stock_arr[$k]['status'] = 0;
                $import_stock_arr[$k]['message'] = '';
                $import_stock_arr[$k]['result'] = '成功';
            }
            return $import_stock_arr;

        }catch (\PDOException $e){
            Log::write('sql-error:getImportStockGoods'.$e->getMessage());
            $result=array('status'=>1,'msg'=> parent::PDO_ERROR);
        }catch(BusinessLogicException $e){
            $result=array('status'=>1,'msg'=> $e->getMessage());
        }catch(\Exception $e){
            Log::write('getImportStockGoods'.$e->getMessage());
            $result=array('status'=>1,'msg'=> parent::PDO_ERROR);
        }

    }

    public function batchChg($rec_id,$id,&$result,$type){
        try{
            $userId = get_operator_id();
            if($rec_id == ''){
                $this->startTrans();
                if($type == 'class'){
                    $sql = "UPDATE goods_goods SET class_id = %d";
                    $res = $this->execute($sql,$id);
                    if($res === false){
                        Log::write('批量修改全部分类出错：class_id= '.$id);
                        $this->rollback();
                        return false;
                    }
                    $chg_class_name = $this->table('goods_class')->field('class_name,path')->where(array('class_id'=>array('eq',$id)))->find();
                    $sql = "UPDATE api_goods_spec ags INNER JOIN goods_spec gs on ags.match_target_id=gs.spec_id SET ags.class_id_path='{$chg_class_name['path']}' WHERE ags.is_deleted=0 AND ags.match_target_type=1";
                    $res = $this->execute($sql);
                    if($res === false){
                        Log::write('批量修改全部平台货品分类出错：class_id= '.$id);
                        $this->rollback();
                        return false;
                    }
                    $arr_goods_log = array(
                        'goods_type'   => 1,
                        'goods_id'     => 0000,
                        'spec_id'      => 0,
                        'operator_id'  => $userId,
                        'operate_type' => 61,//修改全部货品分类
                        'message'      => '批量修改全部货品分类为：' . $chg_class_name['class_name'],
                        'created'      => array('exp', 'NOW()')
                    );
                    $res = M('goods_log')->fetchSql(false)->add($arr_goods_log);
                    if($res === false){
                        Log::write('批量修改全部分类记录日志出错：'.print_r($arr_goods_log,true));
                        $this->rollback();
						return false;
                    }
                }else{
                    $sql = "UPDATE goods_goods SET brand_id = %d";
                    $res = $this->execute($sql,$id);
                    if($res===false){
                        Log::write('批量修改全部品牌出错：brand_id: '.$id);
                        $this->rollback();
                        return false;
                    }
                    $chg_brand_name = $this->table('goods_brand')->field('brand_name')->where(array('brand_id'=>array('eq',$id)))->find();
                    $sql = "UPDATE api_goods_spec ags INNER JOIN goods_spec gs on ags.match_target_id=gs.spec_id SET ags.brand_id={$id} WHERE ags.is_deleted=0 AND ags.match_target_type=1";
                    $res = $this->execute($sql);
                    if($res === false){
                        Log::write('批量修改全部平台货品品牌出错：brand_id= '.$id);
                        $this->rollback();
                        return false;
                    }
                    $arr_goods_log = array(
                        'goods_type'   => 1,
                        'goods_id'     => 00000,
                        'spec_id'      => 0,
                        'operator_id'  => $userId,
                        'operate_type' => 62,//修改全部货品品牌
                        'message'      => '批量修改全部货品品牌为：' . $chg_brand_name['brand_name'],
                        'created'      => array('exp', 'NOW()')
                    );
                    $res = M('goods_log')->fetchSql(false)->add($arr_goods_log);
                    if($res === false){
                        Log::write('批量修改全部货品品牌记录日志出错：'.print_r($arr_goods_log,true));
                        $this->rollback();
                        return false;
                    }
                }
                D("Goods/PlatformGoods")->updateStockSyncRule(5);
                $this->commit();
                return;
            }else{
                $goods_arr = $this->field('goods_id,class_id,brand_id')->where(array('goods_id'=>array('in',$rec_id)))->fetchSql(false)->select();
            }
            switch($type){
                case 'class':
                {
                    $class_id = $id;
                    $chg_class_name = $this->table('goods_class')->field('class_name,path')->where(array('class_id'=>array('eq',$class_id)))->find();
                    foreach($goods_arr as $k=>$v){
                        $this->startTrans();
                        //更新分类
                        $res=$this->where(array('goods_id'=>$v['goods_id']))->fetchSql(false)->save(array('class_id'=>$class_id));
                        if($res===false){
                            Log::write('批量修改分类出错：'.print_r($v['goods_id'],true));
                            $this->rollback();
                            continue;
                        }
                        $spec_id_arr = array();
                        $data = M('goods_spec')->field('spec_id')->where(array('goods_id'=>$v['goods_id']))->select();
                        foreach($data as $k1=>$v1){
                            $spec_id_arr[] = $v1['spec_id'];
                        }
                        $res = M('api_goods_spec')->where(array('is_deleted' => array('eq', 0), 'match_target_type' => array('eq', 1), 'match_target_id' => array('in', $spec_id_arr)))->save(array('class_id_path' => $chg_class_name['path']));
                        if($res===false){
                            Log::write('批量修改平台货品分类出错：'.print_r($v['goods_id'],true));
                            $this->rollback();
                            continue;
                        }
                        $ori_class_name = $this->table('goods_class')->field('class_name')->where(array('class_id'=>array('eq',$v['class_id'])))->find();
                        //插入日志记录
                        $arr_goods_log    = array(
                            'goods_type'   => 1,
                            'goods_id'     => $v['goods_id'],
                            'spec_id'      => 0,
                            'operator_id'  => $userId,
                            'operate_type' => 51,
                            'message'      => '批量修改货品分类：从 ' . $ori_class_name['class_name'] . ' 到 ' . $chg_class_name['class_name'],
                            'created'      => array('exp', 'NOW()')
                        );
                        $res=M('goods_log')->fetchSql(false)->add($arr_goods_log);
                        if($res === false){
                            Log::write('批量修改分类记录日志出错：'.print_r($arr_goods_log,true));
                            $this->rollback();
                            continue;
                        }
                        $this->refreshStockSyncRule($v['goods_id']);
                        $this->commit();
                    }
                    break;
                }
                case 'brand':
                {
                    $brand_id = $id;
                    $chg_brand_name = M('goods_brand')->field('brand_name')->where(array('brand_id'=>array('eq',$brand_id)))->find();
                    foreach($goods_arr as $k=>$v){
                        $this->startTrans();
                        //更新品牌
                        $res=$this->where(array('goods_id'=>$v['goods_id']))->save(array('brand_id'=>$brand_id));
                        if($res===false){
                            Log::write('批量修改品牌出错：'.print_r($v['goods_id'],true));
                            $this->rollback();
							continue;
                        }
                        $spec_id_arr = array();
                        $data = M('goods_spec')->field('spec_id')->where(array('goods_id'=>$v['goods_id']))->select();
                        foreach($data as $k1=>$v1){
                            $spec_id_arr[] = $v1['spec_id'];
                        }
                        $res = M('api_goods_spec')->where(array('is_deleted' => array('eq', 0), 'match_target_type' => array('eq', 1), 'match_target_id' => array('in', $spec_id_arr)))->save(array('brand_id' => $brand_id));
                        if($res===false){
                            Log::write('批量修改平台货品品牌出错：'.print_r($v['goods_id'],true));
                            $this->rollback();
							continue;
                        }
                        $ori_brand_name = M('goods_brand')->field('brand_name')->where(array('brand_id'=>array('eq',$v['brand_id'])))->find();
                        //插入日志记录
                        $arr_goods_log    = array(
                            'goods_type'   => 1,
                            'goods_id'     => $v['goods_id'],
                            'spec_id'      => 0,
                            'operator_id'  => $userId,
                            'operate_type' => 51,//修改货品
                            'message'      => '批量修改货品品牌：从 ' . $ori_brand_name['brand_name'] . ' 到 ' . $chg_brand_name['brand_name'],
                            'created'      => array('exp', 'NOW()')
                        );
                        $res=M('goods_log')->fetchSql(false)->add($arr_goods_log);
                        if($res === false){
                            Log::write('批量修改日志记录日志出错：'.print_r($arr_goods_log,true));
                            $this->rollback();
							continue;
                        }
                        $this->refreshStockSyncRule($v['goods_id']);
                        $this->commit();
                    }
                }
            }
        }catch (\PDOException $e){
            var_dump($e->getMessage());
            \Think\Log::write('sql-error:chgClass'.$e->getMessage());
            $result=array('status'=>1,'msg'=> parent::PDO_ERROR);
        }catch (\Exception $e){
            \Think\Log::write('chgClass'.$e->getMessage());
            $result=array('status'=>1,'msg'=> parent::PDO_ERROR);
        }
    }
    // 设置出库仓库
    public function setOutWarehouse($out_warehouse,$goods_id,$shop_id,$type){
        try
        {
            $shop_id=intval($shop_id);
            $spec=array();
            $spec_id = array();
            $add_out_warehouse=array();
            $now=date('Y-m-d H:i:s',time());
            $cgw = M('cfg_goods_warehouse');
            switch ($type) {
                case 'spec':
                    // 如果有逗号证明是多个单品进行拆分，否则spec_id=goods_id
                    if (strpos($goods_id, ",")===flase) {
                        $spec_id = $goods_id;
                    }else {
                        $spec_id = explode(',',$goods_id); 
                    }
                    break;
                case 'goods':                    
                    $spec=M("goods_spec")->alias('gs')
                                         ->field("gs.spec_id,gg.spec_count,gg.goods_no")
                                         ->join('LEFT JOIN goods_goods gg ON gg.goods_id=gs.goods_id')
                                         ->where("gs.goods_id in (".$goods_id.")")
                                         ->select();                                         
                    foreach ($spec as $s) {
                        if ($s['spec_count']<1) {
                            SE('货品编码：'.$s['goods_no'].'没有对应的单品！');
                        }
                        $spec_id[] = $s['spec_id'];
                    }
                    break;
                default:
                    # code...
                    break;
            }
            foreach ($spec_id as $sp) {
               foreach ($out_warehouse as $ow)
                {
                    if ($ow['is_select']==1)
                    {
                        $add_out_warehouse[]=array(
                            'spec_id'=>$sp,
                            'shop_id'=>$shop_id,
                            'warehouse_id'=>intval($ow['id']),
                            'priority'=>set_default_value($ow['priority'],1),
                            'modified'=>$now,
                        );
                    }
                }
            }
            $cgw->startTrans();
            // 删除对应店铺下所有的原有仓库
            foreach ($spec_id as  $sp) {
                $cgw->where(array('spec_id'=>array('eq',$sp),'shop_id'=>array('eq',$shop_id)))->delete();
            }  
           //添加或更新选中的仓库
            $sql=$cgw->addAll($add_out_warehouse,array(),true);           
            $cgw->commit();
        } catch (\PDOException $e)
        {
            $cgw->rollback();
            \Think\Log::write($this->name.'-editShopWarehouse-'.$e->getMessage());
            SE(self::PDO_ERROR);
        }

    }
    // 设置出库物流
    public function setOutLogistics($out_logistics,$goods_id,$shop_id,$warehouse_id,$type){
        try
        {
            $shop_id=intval($shop_id);
            $warehouse_id=intval($warehouse_id);
            $spec_id = array();
            $add_out_logistics=array();
            $now=date('Y-m-d H:i:s',time());
            $cgl = M('cfg_goods_logistics');
            $is_suite=0;
            switch ($type) {
                case 'spec':
                    // 如果有逗号证明是多个单品进行拆分，否则spec_id=goods_id
                    if (strpos($goods_id, ",")===flase) {
                        $spec_id = $goods_id;
                    }else {
                        $spec_id = explode(',',$goods_id);
                    }
                    break;
                case 'goods':
                    $spec=M("goods_spec")->alias('gs')
                        ->field("gs.spec_id,gg.spec_count,gg.goods_no")
                        ->join('LEFT JOIN goods_goods gg ON gg.goods_id=gs.goods_id')
                        ->where("gs.goods_id in (".$goods_id.")")
                        ->select();
                    foreach ($spec as $s) {
                        if ($s['spec_count']<1) {
                            SE('货品编码：'.$s['goods_no'].'没有对应的单品！');
                        }
                        $spec_id[] = $s['spec_id'];
                    }
                    break;
                case 'suite':
                    if (strpos($goods_id, ",")===flase) {
                        $spec_id = $goods_id;
                    }else {
                        $spec_id = explode(',',$goods_id);
                    }
                    $is_suite=1;
                    break;
                default:
                    # code...
                    break;
            }
            foreach ($spec_id as $sp) {
                foreach ($out_logistics as $ow)
                {
                    if ($ow['is_select']==1)
                    {
                        $add_out_logistics[]=array(
                            'spec_id'=>$sp,
                            'shop_id'=>$shop_id,
                            'warehouse_id'=>$warehouse_id,
                            'logistics_id'=>intval($ow['id']),
                            'priority'=>set_default_value($ow['priority'],1),
                            'type'=>$is_suite,
                            'modified'=>$now,
                        );
                    }
                }
            }
            $cgl->startTrans();
            // 删除对应店铺下所有的原有仓库
            foreach ($spec_id as  $sp) {
                $cgl->where(array('spec_id'=>array('eq',$sp),'shop_id'=>array('eq',$shop_id),'warehouse_id'=>array('eq',$warehouse_id)))->delete();
            }
            //添加或更新选中的仓库
            $sql=$cgl->addAll($add_out_logistics,array(),true);
            $cgl->commit();
        } catch (\PDOException $e)
        {
            $cgl->rollback();
            \Think\Log::write($this->name.'-editShopWarehouse-'.$e->getMessage());
            SE(self::PDO_ERROR);
        }
    }

    /**
     * 导出Excel
     */
    public function exportToExcel($id_list, $search, $type = 'excel'){
        $creator = session('account');
        $excel_no = array();
        try{
            //获取输出数据
            $where_goods_goods_str = '';
            $where_goods_spec_str = '';
            set_search_form_value($where_goods_goods_str, 'deleted', 0, 'gg_1', 2, ' AND ');

            if(empty($id_list)) {
                foreach ($search as $k => $v) {
                    if ($v === "") continue;
                    switch ($k) {   //set_search_form_value->Common/Common/function.php
                        case 'goods_no'://goods_goods
                            set_search_form_value($where_goods_goods_str, $k, $v, 'gg_1', 1, ' AND ');
                            break;
                        case 'goods_name':
                            set_search_form_value($where_goods_goods_str, $k, $v, 'gg_1', 10, ' AND ');
                            break;
                        case 'goods_type':
                            set_search_form_value($where_goods_goods_str, $k, $v, 'gg_1', 2, ' AND ');
                            break;
                        case 'short_name':
                            set_search_form_value($where_goods_goods_str, $k, $v, 'gg_1', 6, ' AND ');
                            break;
                        case 'brand_id':
                            set_search_form_value($where_goods_goods_str, $k, $v, 'gg_1', 2, ' AND ');
                            break;
                        case 'flag_id':
                            set_search_form_value($where_goods_goods_str, $k, $v, 'gg_1', 2, ' AND ');
                            break;
                        case 'class_id'://分类         goods_gooods
                            $where_left_join_goods_class = set_search_form_value($where_goods_goods_str, $k, $v, 'gg_1', 7, ' AND ');
                            break;
                        case 'spec_no'://goods_spec
                            set_search_form_value($where_goods_spec_str, $k, $v, 'gs', 10, ' AND ');
                            break;
                        case 'spec_name':
                            set_search_form_value($where_goods_spec_str, $k, $v, 'gs', 6, ' AND ');
                            break;
                        case 'barcode':
                            set_search_form_value($where_goods_spec_str, $k, $v, 'gs', 1, ' AND ');
                            break;
                        case 'created'://处理
                            break;
                        case 'large_type':
                            break;
                    }
                }
            }else{
                $where_goods_goods_str .= ' AND  gg_1.goods_id IN ('.addslashes($id_list).')  ';
            }
            $where_final = $where_goods_goods_str . ' ' . $where_goods_spec_str;
            //去掉第一个多余的 and
            $where_final = stripos(trim($where_final), 'and')===0?substr($where_final,stripos($where_final, 'and')+3):$where_final;
            //根据货品搜索条件或者货品id  查询符合条件货品下的所有未删除的单品信息
            $sql_final = 'SELECT gs_f.spec_no as merchant_no,gg.goods_no,gg.goods_name,gg.short_name,gg.alias,gc.class_name,gb.brand_name, 
                          gg.goods_type,cgu.`name` as `unit_name`,cf.flag_name,gg.origin,gs_f.img_url,gs_f.spec_name,gs_f.spec_code,gs_f.barcode,
                          gs_f.lowest_price,gs_f.retail_price,gs_f.market_price,gs_f.validity_days,gs_f.length,gs_f.width,gs_f.height,
                          gs_f.weight,gs_f.remark,gs_f.prop1,gs_f.prop2,gs_f.prop3,gs_f.prop4,gs_f.is_allow_neg_stock,gs_f.large_type,gs_f.is_hotcake
                          FROM goods_spec gs_f
                          LEFT JOIN goods_goods gg ON gs_f.goods_id = gg.goods_id
                          LEFT JOIN goods_brand gb ON gg.brand_id=gb.brand_id
                          LEFT JOIN cfg_goods_unit cgu ON gg.unit=cgu.rec_id
                          LEFT JOIN goods_class gc ON gg.class_id=gc.class_id
                          LEFT JOIN cfg_flags cf ON cf.flag_id=gg.flag_id
                          INNER JOIN(
                          	SELECT gg_1.goods_id FROM goods_goods gg_1
                          	LEFT JOIN goods_spec gs ON gg_1.goods_id=gs.goods_id AND gs.deleted=0
                          	WHERE  '. $where_final  .'
                           	GROUP BY gg_1.goods_id ORDER BY gg_1.goods_id
                          )gg_2 ON gs_f.goods_id=gg_2.goods_id
                          where gs_f.deleted=0';

            $goods_spec = $this->query($sql_final);

            $num = workTimeExportNum($type);
            if(count($goods_spec) > $num){
                if($type == 'csv'){
                    SE(self::EXPORT_CSV_ERROR);
                }
                SE(self::OVER_EXPORT_ERROR);
            }

            foreach($goods_spec as $k => $v){
                $row['merchant_no'] = $v['merchant_no'];
                $row['goods_no'] = $v['goods_no'];
                $row['goods_name'] = $v['goods_name'];
                $row['short_name'] = $v['short_name'];
                $row['alias'] = $v['alias'];
                $row['class_name'] = $v['class_name'];
                $row['brand_name'] = $v['brand_name'];
                $row['goods_type'] =  $v['goods_type'];
                $row['is_hotcake'] =  $v['is_hotcake']?'是':'否';
                $row['unit_name'] = $v['unit_name'];
                $row['flag_name'] =  $v['flag_name'];
                $row['origin'] =  $v['origin'];
                $row['img_url'] =  $v['img_url'];
                $row['spec_name'] = $v['spec_name'];
                $row['spec_code'] = $v['spec_code'];
                $row['barcode'] =  $v['barcode'];
                $row['lowest_price'] =  $v['lowest_price'];
                $row['retail_price'] =  $v['retail_price'];
                $row['market_price'] =  $v['market_price'];
                $row['validity_days'] =  $v['validity_days'];
                $row['length'] =  $v['length'];
                $row['width'] =  $v['width'];
                $row['height'] =  $v['height'];
                $row['weight'] =  $v['weight'];
                $row['remark'] =  $v['remark'];
                $row['prop1'] =  $v['prop1'];
                $row['prop2'] =  $v['prop2'];
                $row['prop3'] =  $v['prop3'];
                $row['prop4'] =  $v['prop4'];
                $row['is_allow_neg_stock'] = $v['is_allow_neg_stock']?'是':'否';
                switch($v['large_type']){
                    case 0:
                        $row['large_type'] = '非大件';
                        break;
                    case 1:
                        $row['large_type'] = '普通大件';
                        break;
                    case 2:
                        $row['large_type'] = '独立大件';
                        break;
                }
                $data[] = $row;
            }
            foreach($data as $k => $v){
                $keys_arr = array_keys($v);
            }

            foreach($keys_arr as $k => $v){
                switch($v){
                    case'merchant_no':
                        $excel_no['merchant_no'] = '商家编码';
                        break;
                    case'goods_no':
                        $excel_no['goods_no'] = '货品编号 ';
                        break;
                    case 'goods_name':
                        $excel_no['goods_name'] = '货品名称';
                        break;
                    case 'short_name':
                        $excel_no['short_name'] = '货品简称';
                        break;
                    case 'alias':
                        $excel_no['alias'] = '货品别名';
                        break;
                    case 'class_name':
                        $excel_no['class_name'] = '分类';
                        break;
                    case 'brand_name':
                        $excel_no['brand_name'] = '品牌';
                        break;
                    case 'goods_type':
                        $excel_no['goods_type'] = '货品类别 ';
                        break;
                    case 'unit_name':
                        $excel_no['unit_name'] = '单位';
                        break;
                    case 'flag_name':
                        $excel_no['flag_name'] = '标记';
                        break;
                    case 'origin':
                        $excel_no['origin'] = '产地';
                        break;
                    case 'img_url':
                        $excel_no['img_url'] = '图片链接';
                        break;
                    case 'spec_name':
                        $excel_no['spec_name'] = '规格名称';
                        break;
                    case 'spec_code':
                        $excel_no['spec_code'] = '规格码';
                        break;
                    case 'barcode':
                        $excel_no['barcode'] = '条码';
                        break;
                    case 'lowest_price':
                        $excel_no['lowest_price'] = '最低售价';
                        break;
                    case 'retail_price':
                        $excel_no['retail_price'] = '零售价';
                        break;
                    case 'market_price':
                        $excel_no['market_price'] = '市场价';
                        break;
                    case 'validity_days':
                        $excel_no['validity_days'] = '有效期';
                        break;
                    case 'length':
                        $excel_no['length'] = '长';
                        break;
                    case 'width':
                        $excel_no['width'] = '宽';
                        break;
                    case 'height':
                        $excel_no['height'] = '高';
                        break;
                    case 'weight':
                        $excel_no['weight'] = '重量';
                        break;
                    case 'remark':
                        $excel_no['remark'] = '单品备注';
                        break;
                    case 'prop1':
                        $excel_no['prop1'] = '自定义1';
                        break;
                    case 'prop2':
                        $excel_no['prop2'] = '自定义2';
                        break;
                    case 'prop3':
                        $excel_no['prop3'] = '自定义3';
                        break;
                    case 'prop4':
                        $excel_no['prop4'] = '自定义4';
                        break;
                    case 'is_allow_neg_stock':
                        $excel_no['is_allow_neg_stock'] = '允许负库存出库';
                        break;
                    case 'large_type':
                        $excel_no['large_type'] = '大件类别';
                        break;
                    case 'is_hotcake':
                        $excel_no['is_hotcake'] = '是否爆款';
                        break;
                }
            }
            foreach($excel_no as $k=>$v)
            {
                $conv_excel_no[$v] = $k;

            }
            unset($excel_no);
            propFildConv($conv_excel_no,'prop','goods_spec');
            foreach($conv_excel_no as $k=>$v)
            {
                $excel_no[$v] = $k;
            }
            $title = '货品档案';
            $filename = '货品档案';
            $width_list = array('20', '10', '17', '10', '10', '10', '10', '10', '10','10','10', '10', '10', '10', '10', '10', '10', '10', '10', '10', '10', '15',
                '10', '10', '10', '15','15','15','15','17','10');
            if($type == 'csv') {
                ExcelTool::Arr2Csv($data, $excel_no, $filename);
            }else {
                ExcelTool::Arr2Excel($data, $title, $excel_no, $width_list, $filename, $creator);
            }
        } catch(\PDOException $e){
            \Think\Log::write($e->getMessage());
            SE(parent::PDO_ERROR);
        } catch(BusinessLogicException $e){
            SE($e->getMessage());
        } catch(\Exception $e){
            \Think\Log::write($e->getMessage());
            SE(parent::PDO_ERROR);
        }
    }





}