<?php
namespace Setting\Model;
use Think\Model;
class FlagModel extends Model{
	protected $tableName = 'cfg_flags';
	protected $pk        = 'flag_id';
	
	
	/**
	 * 基本操作方法
	 */
	public function getCfgFlags($fields,$where,$alias='',$join=array())
	{
		try {
			$res = $this->alias($alias)->field($fields)->join($join)->where($where)->find();
			return $res;
		} catch (\PDOException $e) {
			\Think\Log::write($this->name.'-getCfgFlags-'.$e->getMessage());
			E(self::PDO_ERROR);
		}
	}
	
	/**
	 * @param integer $flag_class 分类:1订单2货品3采购单4采购退货单5出库单6客户档案7库存8入库单9销售退款单10事务11盘点12供应商货品15营销结果16保质期到期提醒20凭证21保修单22回访单23平台货品,24 结算账户,30 应收应付单,31 退货预入库单
	 * @param string $flag_mode 标识-请求的数据：true-编辑标记，flase-标记数据
	 */
	public function getFlagData($flag_class, $flag_mode = '')
	{
		$flags = array();
		$where['cf.flag_class'] = array('eq', $flag_class);//标记分类
		if($flag_class == 0){
			$where['cf.flag_class'] = array('in', array(1,5));
			$out_where['cf.flag_class'] = array('eq',5);
		}
		try {
			if ($flag_mode=='edit') {
				$where['cf.is_disabled'] = array('eq', 0);
				$flags = $this->alias('cf')->field('cf.flag_id AS id,cf.flag_class,cf.flag_name,cf.bg_color,cf.font_color,cf.font_name AS font_id,cf.is_builtin')->where($where)->order('cf.is_builtin DESC,cf.is_disabled ASC,cf.flag_id ASC')->select();
			} else if($flag_mode=='list')
			{
				$where['cf.is_disabled'] = array('eq', 0);//是否停用
				$flags=$this->alias('cf')->field('cf.flag_id AS id,cf.flag_name AS name')->where($where)->order('cf.is_builtin,cf.flag_id')->select();
			} else if(empty($flag_mode))
			{
				$where['cf.is_disabled'] = array('eq', 0);//是否停用
				$where_list['cf.is_builtin'] = array('eq', 0);//是否内置标记
				$list_flag[] = array('id' => 0, 'name' => '无', 'clazz' => '', 'selected' => true);//标记的下拉列表
				$json_flag = array();//标记每行的背景，字的颜色和体系
				if($flag_class == 0){
					$res_list_flag_arr = $this->alias('cf')->field('cf.flag_id,cf.flag_name,cf.bg_color,cf.font_color,cf.font_name')->where(array_merge($where, $where_list,$out_where))->select();
				}else{
					$res_list_flag_arr = $this->alias('cf')->field('cf.flag_id,cf.flag_name,cf.bg_color,cf.font_color,cf.font_name')->where(array_merge($where, $where_list))->select();	
				}
				$res_json_flag_arr = $this->alias('cf')->field('cf.flag_id,cf.flag_name,cf.bg_color,cf.font_color,cf.font_name')->where($where)->select();
				foreach ($res_list_flag_arr as $arr) {
					$list_flag[] = array('id' => $arr['flag_id'], 'name' => $arr['flag_name'], 'clazz' => 'background-color:' . $arr['bg_color'] . ';color:' . $arr['font_color'] . ';font-family:' . $arr['font_name'] . ';');
				}
				foreach ($res_json_flag_arr as $arr) {
					$json_flag[$arr['flag_id']] = 'background-color:' . $arr['bg_color'] . ';color:' . $arr['font_color'] . ';font-family:' . $arr['font_name'] . ';';
				}
				$flags['list'] = $list_flag;
				$flags['json'] = $json_flag;
			}
		} catch (\PDOException $e) {
			\Think\Log::write('FlagModel->getFlagData: ' . $e->getMessage());
		}
		return $flags;
	}
	
	public function checkFlag($id,$flag_class)
	{
		try {
			if($id==0)
			{
				return true;
			}
			$map['flag_id']=intval($id);
			$map['flag_class']=intval($flag_class);
			$map['is_disabled']=0;
			$result=$this->field('flag_id')->where($map)->find();
			if(!empty($result))
			{
				return true;
			}
		} catch (\PDOException $e) {
			\Think\Log::write($e->getMessage());
		}
		return false;
	}
	
	/**
	 * @param string $flag_name
	 * @param number $flag_class
	 * @return Ambigous <number, \Think\mixed>
	 */
	public function getFlagId($flag_name,$flag_class=1)
	{
		$flag_id=0;
		$key=$flag_name.'_'.$flag_class;
		$arr_flags=array(//flag_name + '_' +flag_class=>key
				'冻结_1'=>1,
				'取消_1'=>2,
				'拆分订单_1'=>3,
				'合并订单_1'=>4,
				'驳回订单_1'=>5,
				'货到付款_1'=>6,
				'手工建单_1'=>7,
		);
		if(isset($arr_flags[$key]))
		{
			$flag_id=$arr_flags[$key];
		}else{
			$map['flag_name']=$flag_name;
			$map['flag_class']=intval($flag_class);
			$map['is_disabled']=0;
			$result=$this->field('flag_id')->where($map)->find();
			if(!empty($result))
			{
				$flag_id=$result['flag_id'];
			}
		}
		return $flag_id;
	}
}