<?php

/**
 * Created by PhpStorm.
 * User: yanfeng
 * Date: 2016/5/4
 * Time: 11:03
 */
namespace Common\Model;

use Think\Model;
use Think\Exception\BusinessLogicException;
use Think\Exception;
use Think\Log;

class DictAddressModel extends Model {

    protected $tableName = "dict_province";
    protected $pk        = "province_id";

    //将省市区的地址名字转换为id
    public function trans2no(&$province, &$city, &$district) {
        try {
            //查询地址信息
            $result   = $this->query("SELECT IFNULL(dp.province_id,0) as province_id,IFNULL(dc.city_id,0) as city_id,IFNULL(dd.district_id,0) as district_id  FROM dict_province dp
                    LEFT JOIN dict_city dc ON(dp.province_id=dc.province_id) AND (dc.name='%s')
                    LEFT JOIN dict_district dd ON(dc.city_id=dd.city_id) AND (dd.name='%s') WHERE dp.name='%s'", array($city, $district, $province));
            $province = $result[0]["province_id"];
            $city     = $result[0]["city_id"];
            $district = $result[0]["district_id"];
        } catch (\PDOException $e) {
            Log::write($e->getMessage());
            SE(self::PDO_ERROR);
        }
    }

}