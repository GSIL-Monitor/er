<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/11/30
 * Time: 17:47
 */

namespace Setting\Model;


use Think\Exception;
use Think\Model;
use Think\Exception\BusinessLogicException;


class AlarmStockModel extends Model
{
    protected $tableName = 'cfg_setting';
    protected $pk = 'key';

    public function saveAlarmRule($data)
    {
        try{
            $this->startTrans();
            foreach ($data as $k => $v) {
                $temp = array();
                $temp["key"] = $k;
                $temp["value"] = $v;
                $temp["class"] = "system";
                $temp["value_type"] = 2;
                $temp["log_type"] = 5;
                $result = D("Setting/System")->updateSystemSetting($temp);
                if (!$result) {
                    SE("保存失败");
                    break;
                }
            }
            $this->commit();
        }catch (BusinessLogicException $e){
            $this->rollback();
            SE($e->getMessage());
        }catch (\PDOException $e){
            $this->rollback();
            \Think\Log::write($this->name.'-saveAlarmRule-'.$e->getMessage());
            SE(self::PDO_ERROR);
        }catch (Exception $e){
            $this->rollback();
            \Think\Log::write($this->name.'-saveAlarmRule-'.$e->getMessage());
            SE(self::PDO_ERROR);
        }
    }
   
}
