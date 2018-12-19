<?php
namespace Setting\Model;
use Think\Exception\BusinessLogicException;
use Think\Model;
class WarehouseZoneModel extends Model{
	protected $tableName = 'cfg_warehouse_zone';
    protected $pk = 'rec_id';
	public function insertZone($zone_data){
	  try {
            if (empty($zone_data[0]))
            {
                $res = $this->add($zone_data);
            }else {
                $res = $this->addAll($zone_data);
            }
            return $res;
        } catch (\PDOException $e) {
            $msg = $e->getMessage();
            \Think\Log::write($this->name."-insertZone-".$msg);
            SE(self::PDO_ERROR);
        }	
	}
}
