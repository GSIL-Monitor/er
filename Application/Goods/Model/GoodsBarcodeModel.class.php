<?php
namespace Goods\Model;

use Think\Model;
use Think\Exception;
use Think\Exception\BusinessLogicException;
use Common\Common\ExcelTool;

/**
 * 条形码模型类
 * @package Goods\Model
 * @author  gaosong
 */
class GoodsBarcodeModel extends Model {
    protected $tableName = 'goods_barcode';
    protected $pk        = 'rec_id';
    protected $_validate = array(
        //array(验证字段1,验证规则,错误提示,[验证条件,附加规则,验证时间]),
        //          验证条件
        // 			self::EXIST S_VALIDAT E 或者0 存在字段就验证（默认）
        // 			self::MUST _VALIDAT E 或者1 必须验证
        // 			self::VALUE_VALIDAT E或者2 值不为空的时候验证
        //          验证时间
        // 			self::MODEL_INSERT或者1新增数据时候验证
        // 			self::MODEL_UPDAT E或者2编辑数据时候验证
        // 			self::MODEL_BOT H或者3全部情况下验证（默认）
        //array('barcode', '/^[a-zA-Z0-9]{1,}$/', '请输入英文或数字', 1, 'unique', 3),
        //array('is_master', array(1, 0), '是否是主条形码类型错误!', 1, in, 3),
    );

    /**
     * 根据条件获取条形码
     *
     * @param int    $page
     * @param int    $rows
     * @param array  $search
     * @param string $sort
     * @param string $order
     * @return array|int
     * @throws array('total' => 0, 'rows' => array())
     */
    public function loadDataByCondition($page = 1, $rows = 20, $search = array(), $sort = 'id', $order = 'desc') {
        $where_barcode = " ";
        $where_spec    = " ";
        $where_suite   = " ";
        $where_is_master = " ";
        $type          = trim($search["type"]) == "" ? 3 : trim($search["type"]);
        $page = intval($page);
        $rows = intval($rows);
        foreach ($search as $k => $v) {
            if ($v === "") continue;
            switch ($k) {
                case 'barcode':
                    set_search_form_value($where_barcode, $k, $v, 'gb', 6, ' AND ');
                    break;
                case 'spec_no':
                    if (($type & 1)== 1) set_search_form_value($where_spec, $k, $v, 'gsp', 10, ' AND ');
                    if (($type & 2)== 2) set_search_form_value($where_suite, "suite_no", $v, 'gsu', 10, ' AND ');
                    break;
                case 'is_master':
                    set_search_form_value($where_is_master,$k,$v,'gb',2,' AND ');
                    break;
            }
        }
        $limit = ($page - 1) * $rows . "," . $rows;
        $order = $sort . " " . $order;
        $order = addslashes($order);
        try {
            $sql          = "SELECT * FROM (SELECT gb.rec_id AS id, gb.barcode, gsp.spec_no, gg.goods_name, gg.short_name, gg.goods_no, gsp.spec_name, gsp.spec_code, gb.is_master, 0 AS is_suite, gb.type FROM goods_barcode gb
                LEFT JOIN goods_spec gsp ON gb.type=1 AND gb.target_id=gsp.spec_id
                LEFT JOIN goods_goods gg ON(gg.goods_id=gsp.goods_id)
                WHERE gb.type=1 AND IF({$type}&1=1, true, false) {$where_barcode} {$where_spec} {$where_is_master}
                UNION ALL
                SELECT gb.rec_id AS id, gb.barcode, gsu.suite_no AS spec_no, gsu.suite_name AS goods_name, '' AS short_name, '' AS goods_no, '' AS spec_name, '' AS spec_code, gb.is_master, 1 AS is_suite, gb.type FROM goods_barcode gb
                LEFT JOIN goods_suite gsu ON gb.target_id=gsu.suite_id
                WHERE gb.type=2 AND IF({$type}&2=2, true, false) {$where_barcode} {$where_suite} {$where_is_master})  temp WHERE true ORDER BY {$order} LIMIT {$limit}";
            $sql_count    = "SELECT COUNT(1) AS total FROM (SELECT gb.rec_id FROM goods_barcode gb LEFT JOIN goods_spec gsp ON gb.type=1 AND gb.target_id=gsp.spec_id WHERE gb.type=1 AND IF({$type}&1=1, true, false) {$where_barcode} {$where_spec} {$where_is_master}
                UNION ALL
                SELECT gb.rec_id FROM goods_barcode gb LEFT JOIN goods_suite gsu ON gb.target_id=gsu.suite_id WHERE gb.type=2 AND IF({$type}&2=2, true, false) {$where_barcode} {$where_suite} {$where_is_master}) temp";
            $file = APP_PATH."/Runtime/File/goods_barcode";
            $cache_sql = substr($sql,0,stripos($sql,'limit'));
            if(file_exists($file))unlink($file);
            file_put_contents($file,print_r($cache_sql,true));
            $total        = $this->query($sql_count);
            $res["total"] = $total[0]["total"];
            $res["rows"]  = $this->query($sql);
        } catch (\PDOException $e) {
            \Think\Log::write($e->getMessage());
            $res["total"] = 0;
            $res["rows"]  = array();
        } catch (\Exception $e) {
            \Think\Log::write($e->getMessage());
            $res["total"] = 0;
            $res["rows"]  = array();
        }
        return $res;
    }

    /**
     * 删除条形码
     * @param $id
     * @return mixed
     * @throws $data = array('status'=>0,'info'=>\PDOException)|$data = array('status'=>0,'info'=>\Exception)
     */
    public function delGoodsBarcode($id) {
        //$id = intval($id);
        try {
            foreach($id as $v) {
                $M = M();
                $M->startTrans();
                //查询要删除的条码的信息
                $sql = "SELECT type,target_id,barcode,is_master FROM goods_barcode WHERE rec_id=%d FOR UPDATE";
                $oldBarcode = $this->query($sql, $v);
                //查询要删除的条码对应货品的主条码
                if ($oldBarcode[0]["type"] == 1) {
                    $sql = "SELECT goods_id,barcode FROM goods_spec WHERE spec_id=%d FOR UPDATE";
                    $mainBarcode = M("goods_spec")->query($sql, $oldBarcode[0]['target_id']);
                    $goods_id = $mainBarcode[0]["goods_id"];
                    $spec_id = $oldBarcode[0]["target_id"];
                    $type = 1;
                } else {
                    $sql = "SELECT suite_id,barcode FROM goods_suite WHERE suite_id=%d FOR UPDATE";
                    $mainBarcode = M("goods_suite")->query($sql, $oldBarcode[0]['target_id']);
                    $goods_id = $mainBarcode[0]["suite_id"];
                    $spec_id = 0;
                    $type = 2;
                }
                //删除条码
                $sql = "DELETE FROM goods_barcode WHERE rec_id=%d";
                $this->execute($sql, $v);
                if ($oldBarcode[0]["is_master"] == 1) {
                    if ($oldBarcode[0]["type"] == 1) {
                        $sql = "UPDATE goods_spec SET barcode='' WHERE spec_id=%d";
                        M("goods_spec")->execute($sql, $oldBarcode[0]['target_id']);
                    } else {
                        $sql = "UPDATE goods_suite SET barcode='' WHERE suite_id=%d";
                        M("goods_suite")->execute($sql, $oldBarcode[0]['target_id']);
                    }
                }
                //记录日志
                $arr_goods_log = array(
                    "goods_type" => $type,
                    "goods_id" => $goods_id,
                    "spec_id" => $spec_id,
                    "operator_id" => get_operator_id(),
                    "operate_type" => 58,
                    "message" => "删除条形码：{$oldBarcode[0]['barcode']}"
                );
                M("goods_log")->data($arr_goods_log)->add();
                $M->commit();
                $res["status"] = 0;
                $res["info"] = "操作成功";
            }
        } catch (\PDOException $e) {
            $M->rollback();
            \Think\Log::write($e->getMessage());
            SE("未知错误，请联系管理员");
        } catch (BusinessLogicException $e) {
            $M->rollback();
            SE($e->getMessage());
        } catch (\Exception $e) {
            $M->rollback();
            \Think\Log::write($e->getMessage());
            SE("未知错误，请联系管理员");
        }
        return $res;
    }

    /**
     * 获取选中的条码数据
     * @param $id
     * @return array|int
     * @throws 0
     */
    public function loadSelectedData($id) {
        $id = (int)$id;
        try {
            $re   = $this->fetchSql(false)->alias("gb")->field("gb.rec_id as id, gb.barcode as barcode, gb.type as type, IF(type =1,gs.spec_no,gsu.suite_no) spec_no,IF(type =1,'否','是') is_suite,IF(type =1,gg.goods_name,gsu.suite_name) goods_name,IF(type =2,gsu.short_name,gg.short_name) short_name,IF(type =1,gg.goods_no,'') goods_no,IF(type =1,gs.spec_name,'') spec_name,IF(type =1,gs.spec_code,'') spec_code,gb.is_master")->join("LEFT JOIN goods_spec gs on gs.spec_id = gb.target_id LEFT JOIN goods_suite gsu on gsu.suite_id = gb.target_id left join goods_goods gg on gg.goods_id = gs.goods_id")->where('rec_id = ' . $id)->select();
            $data = array('total' => 1, 'rows' => $re);
        } catch (\PDOException $e) {
            \Think\Log::write($e->getMessage());
            return false;
        }
        return $data;
    }

    /**
     * 新建条码
     * @param $sql_data
     * @return mixed
     * @throws $data = array('status'=>0,'info'=>\PDOException)|$data = array('status'=>0,'info'=>\Exception)
     */
    public function addGoodsBarcode($sql_data) {
        $data['status'] = 1;
        $data['info']   = "操作成功!";
        $upd_message = '';
        try {
            $this->startTrans();
            $this->checkData($sql_data);
            if($sql_data['is_master']==1){
                if($sql_data['type'] == 1){
                    $spec_model=M('GoodsSpec');
                    $save['barcode']=$sql_data['barcode'];
                    $where['spec_no']=$sql_data['spec_no'];
                    $res=$spec_model->where($where)->save($save);
                    if($res===false){
                        SE('设为主码失败');
                    }
                }else if($sql_data['type'] == 2){
                    $suite_model = M('GoodsSuite');
                    $save['barcode'] = $sql_data['barcode'];
                    $where['suite_no'] = $sql_data['spec_no'];
                    $res=$suite_model->where($where)->save($save);
                    if($res===false){
                        SE('设为主码失败');
                    }
                }
                $where = array(
                    'target_id' => $sql_data['target_id'],
                    'is_master' => 1
                );
                $upd_save['is_master'] = 0;
                $upd_barcode = $this->field('barcode')->where($where)->select();
                if(!empty($upd_barcode)){
                    $upd_res = $this->where($where)->save($upd_save);
                    if($upd_res === false){
                        SE('修改原主码状态失败');
                    }
                    foreach($upd_barcode as $v){
                        $upd_message .= " 修改条码:{$v['barcode']} 主条码由'是'到'否'";
                    }

                }
            }
            if (!$this->create($sql_data, 1)) {
                \Think\Log::write($this->getError());
                SE("未知错误，请联系管理员");
            };
            $this->add($sql_data);
            $logdata['message']      = "新建条形码:" . $sql_data['barcode'] . $upd_message;
            $logdata['operate_type'] = 58;
            $logdata['operator_id']  = get_operator_id();
            $logdata['goods_type']   = $sql_data['goods_type'];
            if (1 == $logdata['goods_type']) {
                $goods_spec          = M("goods_spec")->query("SELECT goods_id FROM goods_spec WHERE spec_id=%d", $sql_data['goods_id']);
                $logdata['spec_id']  = $sql_data['goods_id'];
                $logdata['goods_id'] = $goods_spec[0]['goods_id'];
            } else {
                $logdata['goods_id'] = $sql_data['goods_id'];
            }
            M('goods_log')->fetchSql(false)->data($logdata)->add();
            $this->commit();
        } catch (\PDOException $e) {
            $this->rollback();
            \Think\Log::write($e->getMessage());
            SE("未知错误，请联系管理员");
        } catch (BusinessLogicException $e) {
            $this->rollback();
            SE($e->getMessage());
        } catch (\Exception $e) {
            $this->rollback();
            \Think\Log::write($e->getMessage());
            SE("未知错误，请联系管理员");
        }
        return $data;
    }

    /**
     * 更新条码
     * @param $updata ::id,barcode,is_master,
     * @return mixed
     * @throws $data = array('status'=>0,'info'=>\PDOException)|array('status'=>0,'info'=>\Exception)
     */
    public function updateGoodsBarcode($updata) {
        $upd_message = '';
        try {
            $M = M();
            $M->startTrans();
            $sql        = "SELECT type,target_id,barcode,is_master FROM goods_barcode WHERE rec_id=%d FOR UPDATE";
            $result     = $this->query($sql, $updata["id"]);
            $barcodeOld = $result[0];
            $where = array(
                "target_id" => $barcodeOld['target_id'],
                "barcode" => $updata['barcode'],
                "rec_id" => array('neq', $updata['id'])
            );
            $is_same = $this->where($where)->count();
            if($is_same) SE("条形码已存在，请重新填写!");
            if ($barcodeOld["type"] == 1) {
                $specTable   = M("goods_spec");
                $sql         = "SELECT goods_id,barcode FROM goods_spec WHERE spec_id=%d FOR UPDATE";
                $result      = $specTable->query($sql, $barcodeOld["target_id"]);
                $specBarcode = $result[0];
            }
            $tag = get_seq("goods_barcode");
            $sql = "UPDATE goods_barcode SET barcode='%s',tag='{$tag}',is_master='%d' WHERE rec_id=%d";
            $this->execute($sql, array($updata["barcode"],$updata['is_master'], $updata["id"]));
            if ($barcodeOld["type"] == 1) {
                if ($updata["is_master"] == 1) {
                    $sql = "UPDATE goods_spec SET barcode='%s' WHERE spec_id=%d";
                    $specTable->execute($sql, array($updata["barcode"], $barcodeOld["target_id"]));
                    $where = array(
                        'rec_id' => array('neq', $updata['id']),
                        'target_id' => $barcodeOld['target_id'],
                        'is_master' => 1
                    );
                    $upd_save['is_master'] = 0;
                    $upd_barcode = $this->field('barcode')->where($where)->select();
                    if(!empty($upd_barcode)){
                        $upd_res = $this->where($where)->save($upd_save);
                        if($upd_res === false){
                            SE('修改原主码状态失败');
                        }
                        foreach($upd_barcode as $v){
                            $upd_message .= " 修改条码:{$v['barcode']} 主条码由'是'到'否'";
                        }
                    }
                }else{
                    $where = array(
                        'spec_id' => $barcodeOld['target_id'],
                        'barcode' => $barcodeOld['barcode']
                    );
                    $upd_spec_barcode = array(
                        'barcode' => ''
                    );
                    M("goods_spec")->where($where)->save($upd_spec_barcode);
                }
                $message = '';
                if($barcodeOld["barcode"] != $updata["barcode"]){
                    $message .= "修改条形码：从" . $barcodeOld["barcode"] . "到" . $updata["barcode"];
                }
                $is_master = $updata['is_master'] == 0?'否':'是';
                if($barcodeOld['is_master'] != $updata['is_master']){
                    $message .= "修改条形码:". $updata['barcode'] . "主条码为 " . $is_master;
                }
                $arr_goods_log = array(
                    "goods_type"   => 1,
                    "goods_id"     => $specBarcode["goods_id"],
                    "spec_id"      => $barcodeOld["target_id"],
                    "operator_id"  => get_operator_id(),
                    "operate_type" => 58,
                    "message"      => $message . $upd_message
                );
            } else {
                if ($updata["is_master"] == 1) {
                    $sql = "UPDATE goods_suite SET barcode='%s' WHERE suite_id=%d";
                    M("goods_suite")->execute($sql, array($updata["barcode"], $barcodeOld["target_id"]));
                    $where = array(
                        'rec_id' => array('neq', $updata['id']),
                        'target_id' => $barcodeOld['target_id'],
                        'is_master' => 1
                    );
                    $upd_save['is_master'] = 0;
                    $upd_barcode = $this->field('barcode')->where($where)->select();
                    if (!empty($upd_barcode)) {
                        $upd_res = $this->where($where)->save($upd_save);
                        if ($upd_res === false) {
                            SE('修改原主码状态失败');
                        }
                        foreach ($upd_barcode as $v) {
                            $upd_message .= " 修改条码:{$v['barcode']} 主条码由'是'到'否'";
                        }
                    }
                }else{
                    $where = array(
                        'suite_id' => $barcodeOld['target_id'],
                        'barcode' => $barcodeOld['barcode']
                    );
                    $upd_suite_barcode = array(
                        'barcode' => ''
                    );
                    M("goods_suite")->where($where)->save($upd_suite_barcode);
                }
                $message = '';
                if($barcodeOld["barcode"] != $updata["barcode"]){
                    $message .= "修改条形码：从" . $barcodeOld["barcode"] . "到" . $updata["barcode"];
                }
                $is_master = $updata['is_master'] == 0?'否':'是';
                if($barcodeOld['is_master'] != $updata['is_master']){
                    $message .= "修改条形码:". $updata['barcode'] . "主条码为 " . $is_master;
                }
                $arr_goods_log = array(
                    "goods_type"   => 2,
                    "goods_id"     => $barcodeOld["target_id"],
                    "spec_id"      => 0,
                    "operator_id"  => get_operator_id(),
                    "operate_type" => 58,
                    "message"      => $message . $upd_message
                );
            }
            if($message != '' || $upd_message != ''){
                $logTable = M("goods_log");
                $logTable->data($arr_goods_log)->add();
            }
            $M->commit();
            $res["status"] = 1;
            $res["info"] = "操作成功";
        } catch (\PDOException $e) {
            $M->rollback();
            \Think\Log::write($e->getMessage());
            SE("未知错误，请联系管理员");
        } catch (BusinessLogicException $e) {
            $M->rollback();
            SE($e->getMessage());
        } catch (\Exception $e) {
            $M->rollback();
            \Think\Log::write($e->getMessage());
            SE("未知错误，请联系管理员");
        }
        return $res;
    }

    /**
     * 验货出库扫条码时选择货品信息
     *
     * 通过 传入条码信息  查询含有该条码标识的所有货品信息
     *
     * @author homedown
     * @param string $barcode
     * @return array
     */
    public function getGoodsByBarcode($barcode) {
        try {
            $this->execute("set @tmp_goods_name='',@tmp_short_name='',@tmp_merchant_no='',@tmp_spec_name='',@tmp_spec_code='',@tmp_goods_id='',@tmp_spec_id='',@tmp_sn_enable=0;");
            /* SELECT (`type`=2) is_suite,target_id,FN_GOODS_NO(`type`,target_id) goods_no,@tmp_merchant_no spec_no,
                @tmp_goods_name goods_name,@tmp_short_name short_name,@tmp_spec_name spec_name,@tmp_spec_code spec_code,
                @tmp_sn_enable is_sn_enable
                FROM goods_barcode WHERE barcode = :barcode */
            $fields = array(
                '(`type`=2) AS is_suite',
                'target_id',
                'FN_GOODS_NO(`type`,target_id) goods_no',
                '@tmp_merchant_no spec_no',
                '@tmp_goods_name goods_name',
                '@tmp_short_name short_name',
                '@tmp_spec_name spec_name',
                '@tmp_spec_code spec_code',
                '@tmp_sn_enable is_sn_enable'
            );
            $res    = $this->field($fields)->fetchSql(false)->where(array('trim(barcode)' => $barcode))->select();
            return $res;
        } catch(BusinessLogicException $e){
			SE($e->getMessage());
		} catch (\PDOException $e) {
            $msg = $e->getMessage();
            \Think\Log::write($this->name . '-getGoodsByBarcode-' . $msg);
            SE(self::PDO_ERROR);
        }
    }

    public function importBarcode($data){
        try {
            $this->checkData($data);
            $result['is_master']=0;
            if($data['is_master']=='是'){
                $result['is_master']=1;
            }
            $model=M('GoodsSpec');
            $where['spec_no']=$data['spec_no'];
            $rst=$model->where($where)->field('spec_id')->find();
            $sql_data['is_master']   =$result['is_master'];
            $sql_data['spec_no']     =$data['spec_no'];
            $sql_data['barcode']      = $data['barcode'];
            $sql_data['type']         = 1;
            $sql_data['target_id']    = $rst['spec_id'];
            $sql_data['goods_id']     = $rst['spec_id'];
            $sql_data['tag']          = get_seq("goods_barcode");
            $sql_data['message']      = "新建条形码:" . $sql_data['barcode'];
            $sql_data['goods_type']   = 1;
            $sql_data['operate_type'] = 58;
            $sql_data['operator_id']  = get_operator_id();
            $sql_data['input']  = 1;
            D("GoodsBarcode")->addGoodsBarcode($sql_data);
        }catch(\PDOException $e) {
            \Think\Log::write($this->name.'-importBarcode-'.$e->getMessage());
            SE(self::PDO_ERROR);
        }
    }

    function checkData($data){
        $Barcode=M('GoodsBarcode');
        $Spec=M('GoodsSpec');
        $Suite = M('GoodsSuite');
        if (!isset($data["spec_no"]) || $data["spec_no"] == "")SE("商品编号不能为空");
        if (!isset($data["barcode"]) || $data["barcode"] == "")SE("条形码不能为空！");
        if($data['type'] == 2){
            $where = array(
                'suite_no'=>$data['spec_no']
            );
            $suite_res = $Suite->where($where)->find();
            if(!$suite_res){
                SE("商品编码不存在，请重新填写!");
            }
            $where=array(
                'barcode'=>$data['barcode'],
                'target_id'=>$suite_res['suite_id']
            );
            if($Barcode->where($where)->find())SE("条形码已存在，请重新填写!");
        }else{
            $where=array(
                'spec_no'=>$data['spec_no']
            );
            $res = $Spec->where($where)->find();
            if(!$res){
                SE("商品编号不存在，请重新填写!");
            }
            $where=array(
                'barcode'=>$data['barcode'],
                'target_id'=>$res['spec_id']
            );
            if($Barcode->where($where)->find())SE("条形码已存在，请重新填写!");
        }

    }

    public function exportToExcel($id_list, $type){
        $creator=session('account');
        try{
            $file = APP_PATH."/Runtime/File/goods_barcode";
            if(empty($id_list)){
                $sql = file_get_contents($file);
                $data = $this->query($sql);
                $count = count($data);
            }else{
                $sql = "SELECT * FROM (SELECT gb.rec_id AS id, gb.barcode, gsp.spec_no, gg.goods_name, gg.short_name, gg.goods_no, gsp.spec_name, gsp.spec_code, gb.is_master, 0 AS is_suite, gb.type FROM goods_barcode gb
                LEFT JOIN goods_spec gsp ON gb.type=1 AND gb.target_id=gsp.spec_id
                LEFT JOIN goods_goods gg ON(gg.goods_id=gsp.goods_id)
                WHERE gb.type=1 AND IF(3&1=1, true, false)  AND rec_id in ({$id_list})
                UNION ALL
                SELECT gb.rec_id AS id, gb.barcode, gsu.suite_no AS spec_no, gsu.suite_name AS goods_name, '' AS short_name, '' AS goods_no, '' AS spec_name, '' AS spec_code, gb.is_master, 1 AS is_suite, gb.type FROM goods_barcode gb
                LEFT JOIN goods_suite gsu ON gb.target_id=gsu.suite_id
                WHERE gb.type=2 AND IF(3&2=2, true, false) AND rec_id in ({$id_list})) temp ORDER BY id desc ";
                $data = $this->query($sql);
                $count = count(explode(',',$id_list));
            }
            $num = workTimeExportNum($type);
            if($count>$num){
                if($type == 'csv'){
                    SE(self::EXPORT_CSV_ERROR);
                }
                SE(self::OVER_EXPORT_ERROR);
            }
            $title = '条形码';
            $filename = '条形码';
            $excel_header = D('Setting/UserData')->getExcelField('Goods/GoodsBarcode','goods_barcode');
            foreach ($excel_header as $v)
            {
                $width_list[]=20;
            }
            foreach($data as $k=>$v){
                if($v['is_master']==0){
                    $data[$k]['is_master'] = '否';
                }else{
                    $data[$k]['is_master'] = '是';
                }
                if($v['is_suite']==0){
                    $data[$k]['is_suite'] = '否';
                }else{
                    $data[$k]['is_suite'] = '是';
                }
            }
            if($type == 'csv') {
                ExcelTool::Arr2Csv($data, $excel_header, $filename);
            }else {
                ExcelTool::Arr2Excel($data, $title, $excel_header, $width_list, $filename, $creator);
            }
            unset($data);
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

}