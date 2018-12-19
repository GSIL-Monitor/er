<?php
/**
 * Created by PhpStorm.
 * User: yanfeng
 * Date: 2016/6/14
 * Time: 4:33
 */
namespace Account\Model;

use Think\Exception;
use Think\Exception\BusinessLogicException;
use Think\Model;
use Think\Log;

class FaLogisticsFeeOrderDetailModel extends Model {

    protected $tableName = 'fa_logistics_fee_order_detail';
    protected $pk        = 'rec_id';

    public function getFaLogisticsFeeOrderDetailList($id) {
        try {
            $sql   = "SELECT rec_id,logistics_id,logistics_no,area,created,postage,import_postage,
            IFNULL(import_postage-postage,0) AS diff_postage,
            weight,import_weight,
            IFNULL(import_weight-weight,0) AS diff_weight,
            remark,import_summary,import_time FROM fa_logistics_fee_order_detail WHERE order_id =%d";
            $rows  = $this->query($sql, $id);
            $total = count($rows);
            $data  = array("total" => $total, "rows" => $rows);
            return $data;
        } catch (\Exception $e) {
            SE(self::PDO_ERROR);
            Log::write($e->getMessage());
        }
    }

    public function importLogisticsFee($excelData, $logistics_id) {
        try {
            $this->startTrans();
            //先生成结算单
            $fa_logistics_fee_order = array(
                "logistics_id"   => $logistics_id,
                "order_no"       => get_sys_no("logistics_fee"),
                "status"         => 0,
                "make_oper_id"   => get_operator_id(),
                "created"        => date("Y-m-d H:i:s", time()),
                "import_postage" => 0,
                "import_weight"  => 0
            );
            //获取结算单order_id
            $order_id = D("fa_logistics_fee_order")->data($fa_logistics_fee_order)->add();
            $flag     = 0;
            foreach ($excelData as $v) {
                if (!$flag) {
                    $flag++;
                    continue;
                }
                $i              = 0;
                $logistics_no   = addslashes($v[$i++]);
                $import_postage = floatval($v[$i++]);
                $import_weight  = floatval($v[$i++]);
                $import_summary = addslashes($v[$i]);
                $sql            = "SELECT logistics_id,logistics_no,`status`,postage,weight,area,is_refered,shop_id,warehouse_id,created
		    FROM fa_logistics_fee WHERE logistics_no = '%s' AND logistics_id = %d;";
                $result         = $this->query($sql, array($logistics_no, $logistics_id));
                if (count($result) == 0) {
                    SE("系统内不存在的物流单号--{$logistics_no}");
                }
                if ($result[0]["status"] == 1) {
                    SE("该物流单已经结算--{$logistics_no}");
                }
                if ($result[0]["status"] == 2) {
                    SE("该物流单已经冲销--{$logistics_no}");
                }
                if ($result[0]["is_refered"] == 1) {
                    SE("该物流单已经存在于其他结算单--{$logistics_no}");
                }
                //更新物流单导入数据
                $sql = "UPDATE fa_logistics_fee
			SET import_postage ={$import_postage}, import_weight ={$import_weight}, import_summary='{$import_summary}', is_refered = 1
			WHERE logistics_no = '{$logistics_no}' AND logistics_id={$logistics_id};";
                D("fa_logistics_fee")->execute($sql);
                //插入结算单明细
                $logistics_fee_order_detail = array(
                    "order_id"       => $order_id,
                    "shop_id"        => $result[0]["shop_id"],
                    "warehouse_id"   => $result[0]["warehouse_id"],
                    "postage"        => $result[0]["postage"],
                    "weight"         => $result[0]["weight"],
                    "import_postage" => $import_postage,
                    "import_weight"  => $import_weight,
                    "import_summary" => $import_summary,
                    "is_refrered"    => 1,
                    "logistics_no"   => $logistics_no,
                    "logistics_id"   => $logistics_id,
                    "area"           => $result[0]["area"],
                    "created"        => $result[0]["created"],
                    "import_time"    => date("Y-m-d H:i:s", time())
                );
                $this->data($logistics_fee_order_detail)->add();
                $sql = "SELECT rec_id,logistics_fee_count,estimate_postage,import_postage,estimate_weight,import_weight FROM fa_logistics_fee_order WHERE rec_id={$order_id}";
                $res = $this->query($sql);
                $res[0]["logistics_fee_count"]++;
                $res[0]["estimate_postage"] += $result[0]["postage"];
                $res[0]["import_postage"] += $import_postage;
                $res[0]["estimate_weight"] += $result[0]["weight"];
                $res[0]["import_weight"] += $import_weight;
                D("fa_logistics_fee_order")->data($res[0])->save();
            }
            $this->commit();
            return $order_id;
        } catch (\PDOException $e) {
            Log::write($e->getMessage());
            SE(self::PDO_ERROR);
        } catch (BusinessLogicException $e) {
            SE($e->getMessage());
        } catch (\Exception $e) {
            Log::write($e->getMessage());
            SE(self::PDO_ERROR);
        }
    }

    public function charge($order_id) {
        try {
            $this->startTrans();
            $sql = "UPDATE fa_logistics_fee flf, fa_logistics_fee_order_detail fod SET flf.status = 1 WHERE
		flf.logistics_id=fod.logistics_id AND flf.logistics_no=fod.logistics_no	AND fod.order_id = %d;";
            $this->execute($sql, $order_id);
            $uid = get_operator_id();
            $sql = "UPDATE fa_logistics_fee_order SET `status` = 1, charge_oper_id = {$uid}, charge_time = NOW() WHERE rec_id = %d;";
            $this->execute($sql, $order_id);
            $this->commit();
        } catch (\PDOException $e) {
            Log::write($e->getMessage());
            SE(self::PDO_ERROR);
        } catch (Exception $e) {
            Log::write($e->getMessage());
            SE(self::PDO_ERROR);
        }
    }

}