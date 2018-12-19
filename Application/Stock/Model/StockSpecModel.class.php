<?php
/**
 * Created by PhpStorm.
 * User: gaosong
 * Date: 10/28/15
 * Time: 10:44
 */
namespace Stock\Model;
use Common\Common\UtilDB;
use Think\Exception\BusinessLogicException;
use Think\Model;

/**
 * 库存单品模型类
 * @package Stock\Model
 */
class StockSpecModel extends Model{
    protected $tableName = 'stock_spec';
    protected $pk        = 'spec_id';

    /**
     * 根据条件获取单品列表
     * @param int $page
     * @param int $rows
     * @param array $search
     * @param string $sort
     * @param string $order
     * @return array|string
     * @throws array('total' => 0, 'rows' => array())
     */
    public function searchStockSpec($page = 1, $rows = 20, $search = array(), $sort = 'id', $order = 'desc')
    {
        //----------判断是否是单仓库和是否开启了库存警戒来设置提醒是否有库存不足
        /*$is_show_alarm = 1;
        $warehouse_list = D('Setting/Warehouse')->field('warehouse_id')->where(array('is_disabled'=>0))->select();
        if(count($warehouse_list) != 1)
        {
            $is_show_alarm = 0;
        }*/
        $right_search = array();
        $right_list = array();
		D('Setting/EmployeeRights')->setSearchRights($right_search,'warehouse_id',2);
		$right = $right_search['warehouse_id'];
		$disabled_list = D('Setting/Warehouse')->field('GROUP_CONCAT(DISTINCT warehouse_id ORDER BY warehouse_id) list')->where(array('is_disabled'=>1))->find();
        $disabled_list = $disabled_list['list'];
		if(!empty($right) && !empty($disabled_list)){
		    $right_list = explode(',',$right);
            $disabled_list = explode(',',$disabled_list);
            $right_list = array_diff($right_list,$disabled_list);
            $right = implode(',',$right_list);
        }
        $sys_config =  get_config_value(array('sys_available_stock','point_number','purchase_alarmstock_open'),array(640,0,0));
		$available_str = D('Stock/StockSpec')->getAvailableStrBySetting($sys_config['sys_available_stock']);
        $point_number = $sys_config['point_number'];
        $point_number = intval($point_number);
        $where_goods_spec = '';
        $where_goods_goods = '';
        $where_stock_spec = '';
        $where_left_join_goods_class='';
        set_search_form_value($where_goods_spec, 'deleted', 0, 'gs_1', 2, ' AND ');
        set_search_form_value($where_goods_goods, 'deleted', 0, 'gg_1', 2, ' AND ');
        $isset_warehouse = 0;
        $multiple_warehouse = 0;
        $mul_ware_ids = '0';
        foreach ($search as $k => $v) {
            if ($v === '')  continue;
            switch ($k) {
                case 'spec_no'://商家编码      goods_spec
                    set_search_form_value($where_goods_spec, $k, $v, 'gs_1', 10, ' AND ');
                    break;
                case 'barcode'://条形码   goods_spec
                    set_search_form_value($where_goods_spec, $k, $v, 'gs_1', 1, ' AND ');
                    break;
                case 'goods_no': //货品编号     goods_gooods
                    set_search_form_value($where_goods_goods, $k, $v, 'gg_1', 1, ' AND ');
                    break;
                case 'goods_name'://货品名称  goods_gooods
                    set_search_form_value($where_goods_goods, $k, $v, 'gg_1', 10, ' AND ');
                    break;
                case 'class_id'://分类         goods_gooods
                    $where_left_join_goods_class=set_search_form_value($where_goods_goods, $k, $v, 'gg_1', 7, ' AND ');
                    break;
                case 'brand_id'://品牌        goods_gooods
                    set_search_form_value($where_goods_goods, $k, $v, 'gg_1', 2, ' AND ');
                    break;
                case 'warehouse_id'://仓库   stock_spec
                    if(strpos($v,'all')!==false&&strlen($v)>3){//包含all的多选条件清除all
                        $v=str_replace('all,', '', $v);
                        $v=str_replace(',all', '', $v);
                    }
                    set_search_form_value($where_stock_spec, $k, $v, 'ss_1', 2, ' AND ');
                    if(stripos($where_stock_spec,$k)!==false){
                        $isset_warehouse = 1;
                    }
                    if(strpos($v,',')!==false){
                        $multiple_warehouse = 1;
                    }
                    $mul_ware_ids = $v;
                    break;
            }
        }
		$ware_num = 0;
		if($isset_warehouse == 1 && $multiple_warehouse != 1){
			$ware_num = 1;
		}
		if($isset_warehouse == 0 && !stripos($right,',')){
			$ware_num = 1;
		}
		$warehouse_where = str_replace('ss_1','ssps',$where_stock_spec);
		$config_data = get_config_value('stock_out_num',30);
        $page = intval($page);
        $rows = intval($rows);
        $arr_sort=array('id'=>'ss.spec_id','spec_no'=>'gs.spec_no','goods_no'=>'gg.goods_no','goods_name'=>'gg.goods_name','short_name'=>'gg.short_name','spec_code'=>'gs.spec_code','spec_name'=>'gs.spec_name','barcode'=>'gs.barcode','brand_id'=>'gb.brand_name','class_id'=>'gc.class_name','stock_num'=>'stock_num','safe_stock'=>'safe_stock','cost_price'=>'cost_price','all_cost_price'=>'all_cost_price','avaliable_num'=>'avaliable_num','order_num'=>'order_num','sending_num'=>'sending_num','purchase_num'=>'purchase_num','unpay_num'=>'unpay_num','purchase_arrive_num'=>'purchase_arrive_num','subscribe_num'=>'subscribe_num','retail_price'=>'gs.retail_price','market_price'=>'gs.market_price','spec_wh_no'=>'ss.spec_wh_no','spec_wh_no2'=>'ss.spec_wh_no2','seven_outnum'=>'ssps_1.seven_outnum','fourteen_outnum'=>'ssps_2.fourteen_outnum','recent_outnum'=>'ssps_3.recent_outnum',);
        //$arr_sort=array('id'=>'ss.spec_id','spec_no'=>'gs.spec_no','goods_no'=>'gg.goods_no','goods_name'=>'gg.goods_name','short_name'=>'gg.short_name','spec_code'=>'gs.spec_code','spec_name'=>'gs.spec_name','barcode'=>'gs.barcode','brand_id'=>'gb.brand_name','class_id'=>'gc.class_name','stock_num'=>'ss.stock_num','safe_stock'=>'ss.safe_stock','cost_price'=>'ss.cost_price','all_cost_price'=>'all_cost_price','avaliable_num'=>'avaliable_num','order_num'=>'order_num','sending_num'=>'sending_num','purchase_num'=>'purchase_num','purchase_arrive_num'=>'purchase_arrive_num','subscribe_num'=>'subscribe_num','retail_price'=>'gs.retail_price','market_price'=>'gs.market_price','spec_wh_no'=>'ss.spec_wh_no','spec_wh_no2'=>'ss.spec_wh_no2');
        $in_arr_sort=array(
            'id'=>'ss_1.spec_id',
            'spec_no'=>'gs_1.spec_no',
            'goods_no'=>'gg_1.goods_no',
            'goods_name'=>'gg_1.goods_name',
            'short_name'=>'gg_1.short_name',
            'spec_code'=>'gs_1.spec_code',
            'spec_name'=>'gs_1.spec_name',
            'barcode'=>'gs_1.barcode',
            'brand_id'=>'gg_1.brand_id',
            'class_id'=>'gg_1.class_id',
            'stock_num'=>'sum(ss_1.stock_num)',
            'safe_stock'=>'sum(ss_1.safe_stock)',
            'cost_price'=>'IFNULL(IF(SUM(GREATEST(stock_num,0))=0,AVG(ss_1.cost_price),(SUM(GREATEST(stock_num,0)*ss_1.cost_price)/SUM(GREATEST(stock_num,0)))),0)',
            'all_cost_price'=>'IFNULL(SUM(GREATEST(ss_1.stock_num,0)*ss_1.cost_price),0)',
            'avaliable_num'=>'sum(IFNULL(ss_1.stock_num-ss_1.order_num-ss_1.sending_num,0))',
            'order_num'=>'sum(ss_1.order_num)',
            'sending_num'=>'sum(ss_1.sending_num)',
            'purchase_num'=>'sum(ss_1.purchase_num)',
            'unpay_num'=>'sum(ss_1.unpay_num)',
            'purchase_arrive_num'=>'sum(ss_1.purchase_arrive_num)',
            'subscribe_num'=>'sum(ss_1.subscribe_num)',
            'retail_price'=>'gs_1.retail_price',
            'market_price'=>'gs_1.market_price',
            'spec_wh_no'=>'ss_1.spec_wh_no',
            'spec_wh_no2'=>'ss_1.spec_wh_no2',
			'seven_outnum'=>'ssps_in_1.seven_outnum',
			'fourteen_outnum'=>'ssps_in_2.fourteen_outnum',
			'recent_outnum'=>'ssps_in_3.recent_outnum',
            );
        $limit=($page - 1) * $rows . "," . $rows;//分页
        $out_order = $arr_sort[$sort].' '.$order;//排序
        $out_order = addslashes($out_order);
        $in_order = $in_arr_sort[$sort].' '.$order;//排序
        $in_order = addslashes($in_order);
        $sql_sel_limit='SELECT ss_1.spec_id FROM stock_spec ss_1 ';
        $sql_total='SELECT ss_1.spec_id FROM stock_spec ss_1 ';
        $flag=false;
        $sql_where='';
        if(!empty($where_goods_spec)||!empty($where_goods_goods))
        {
            $sql_where .= ' LEFT JOIN goods_spec gs_1 ON gs_1.spec_id=ss_1.spec_id ';
        }
        if(!empty($where_goods_goods))
        {
            $sql_where .= ' LEFT JOIN goods_goods gg_1 ON gg_1.goods_id=gs_1.goods_id ';
            $sql_where .= $where_left_join_goods_class;
        }
		$where_date = date('Y-m-d',time());
		$sql_left_join_seven_outnum = " LEFT JOIN (select SUM(ssps.num-ssps.refund_num-ssps.return_num) AS seven_outnum,ssps.spec_id from stat_daily_sales_spec_warehouse ssps where ssps.sales_date >= date_add('{$where_date}',INTERVAL -7 DAY) and ssps.sales_date < '{$where_date}' {$warehouse_where} and ssps.warehouse_id IN (".$right.") group by ssps.spec_id) ssps_in_1 on ssps_in_1.spec_id =ss_1.spec_id ";
		$sql_left_join_fourteen_outnum = " LEFT JOIN (select SUM(ssps.num-ssps.refund_num-ssps.return_num) AS fourteen_outnum,ssps.spec_id from stat_daily_sales_spec_warehouse ssps where ssps.sales_date >= date_add('{$where_date}',INTERVAL -14 DAY) and ssps.sales_date < '{$where_date}' {$warehouse_where} and ssps.warehouse_id IN (".$right.") group by ssps.spec_id) ssps_in_2 on ssps_in_2.spec_id =ss_1.spec_id ";
		$sql_left_join_recent_outnum = " LEFT JOIN (select SUM(ssps.num-ssps.refund_num-ssps.return_num) AS recent_outnum,ssps.spec_id from stat_daily_sales_spec_warehouse ssps where ssps.sales_date >= date_add('{$where_date}',INTERVAL -{$config_data} DAY) and ssps.sales_date < '{$where_date}' {$warehouse_where} and ssps.warehouse_id IN (".$right.") group by ssps.spec_id) ssps_in_3 on ssps_in_3.spec_id =ss_1.spec_id ";
		if($sort == 'seven_outnum'){
			$sql_where .= $sql_left_join_seven_outnum;
		}
		if($sort == 'fourteen_outnum'){
			$sql_where .= $sql_left_join_fourteen_outnum;
		}
		if($sort == 'recent_outnum'){
			$sql_where .= $sql_left_join_recent_outnum;
		}
        connect_where_str($sql_where, $where_goods_spec, $flag);
        connect_where_str($sql_where, $where_goods_goods, $flag);
        connect_where_str($sql_where, $where_stock_spec, $flag);
        $out_where_stock_spec = str_replace('ss_1','ss',$where_stock_spec);
        if($right == ''){
			$right = 0;
		}
		$field_content = array(
			'cost_price'=>'cast(IFNULL(IF(SUM(GREATEST(ss.stock_num,0))=0,AVG(ss.cost_price),(SUM(GREATEST(ss.stock_num,0)*ss.cost_price)/SUM(GREATEST(ss.stock_num,0)))),0) as decimal(19,4)) as cost_price',
			'all_cost_price'=>'cast(IFNULL(SUM(GREATEST(ss.stock_num,0)*ss.cost_price),0) as decimal(19,4)) as all_cost_price '
		);
		$field_right = D('Setting/EmployeeRights')->getFieldsRight('',$field_content); //获取字段权限，第一个参数是表别名，后面要加".";第二个参数是指当前字段需要经过运算得出，则将字段名对应的算式写成数组传入
        $sql_sel_limit.=$sql_where.' and ss_1.warehouse_id in ('.$right.') GROUP BY ss_1.spec_id ORDER BY  '.$in_order.' LIMIT '.$limit;//多仓库的时候 $sql_where.' GROUP BY ss_1.spec_id ORDER BY '.$order.' LIMIT '.$limit
        $sql_total.=$sql_where.' and ss_1.warehouse_id in ('.$right.') '.' GROUP BY ss_1.spec_id';
        //单仓库情况--SP_STOCK_SPEC_QUERY(后期加多仓库，改进)
        $sql_fields_str='SELECT ss.spec_id AS id,cw.type as warehouse_type,ss.spec_id,ss.spec_wh_no,ss.spec_wh_no2,gg.goods_id,gs.spec_no,gg.goods_no,gg.goods_name,gg.short_name,gb.brand_name brand_id,gc.class_name class_id,gs.retail_price, gs.market_price,gs.spec_code,ss.flag_id,gs.spec_name,gs.barcode,CAST(sum(ss.stock_num) AS DECIMAL(19,'.$point_number.')) stock_num,CAST(sum(IFNULL('.$available_str.',0)) AS DECIMAL(19,'.$point_number.')) avaliable_num, CAST(sum(ss.order_num) AS DECIMAL(19,'.$point_number.')) order_num,CAST(sum(ss.purchase_num) AS DECIMAL(19,'.$point_number.')) purchase_num,CAST(sum(ss.unpay_num) AS DECIMAL(19,'.$point_number.')) unpay_num, CAST(sum(ss.subscribe_num) AS DECIMAL(19,'.$point_number.')) subscribe_num,CAST(sum(ss.purchase_arrive_num) AS DECIMAL(19,'.$point_number.')) purchase_arrive_num, CAST(sum(ss.sending_num) AS DECIMAL(19,'.$point_number.')) sending_num,'.$field_right['cost_price'].', '.$field_right['all_cost_price'].',IF('.$isset_warehouse.',ss.warehouse_id,0) as warehouse_id,'.$multiple_warehouse.' as multiple_warehouse,"'.$mul_ware_ids.'" as mul_ware_ids,'.$ware_num.' as ware_num,CAST(sum(ss.safe_stock) as DECIMAL(19,'.$point_number.')) safe_stock,MAX(IF(ss.safe_stock>ss.stock_num and '.$sys_config['purchase_alarmstock_open'].',1,0)) alarm_flag,IFNULL(CAST(ssps_1.seven_outnum AS DECIMAL(19,'.$point_number.')),0) as seven_outnum,IFNULL(CAST(ssps_2.fourteen_outnum AS DECIMAL(19,'.$point_number.')),0) as fourteen_outnum,IFNULL(CAST(ssps_3.recent_outnum AS DECIMAL(19,'.$point_number.')),0) as recent_outnum FROM stock_spec ss';
        $sql_left_join_str='LEFT JOIN goods_spec gs ON ss.spec_id = gs.spec_id LEFT JOIN goods_goods gg ON gg.goods_id = gs.goods_id LEFT JOIN goods_class gc ON gc.class_id = gg.class_id LEFT JOIN goods_brand gb ON gb.brand_id = gg.brand_id left join cfg_warehouse cw on cw.warehouse_id = ss.warehouse_id';//LEFT JOIN cfg_warehouse_position cwp ON cwp.rec_id = ss.default_position_id

        $sql_left_join_volumn_str = " LEFT JOIN (select SUM(ssps.num-ssps.refund_num-ssps.return_num) AS seven_outnum,ssps.spec_id from stat_daily_sales_spec_warehouse ssps where ssps.sales_date >= date_add('{$where_date}',INTERVAL -7 DAY) and ssps.sales_date < '{$where_date}' {$warehouse_where} and ssps.warehouse_id IN (".$right.") group by ssps.spec_id) ssps_1 on ssps_1.spec_id =ss.spec_id ";
		$sql_left_join_volumn_str .= " LEFT JOIN (select SUM(ssps.num-ssps.refund_num-ssps.return_num) AS fourteen_outnum,ssps.spec_id from stat_daily_sales_spec_warehouse ssps where ssps.sales_date >= date_add('{$where_date}',INTERVAL -14 DAY) and ssps.sales_date < '{$where_date}' {$warehouse_where} and ssps.warehouse_id IN (".$right.") group by ssps.spec_id) ssps_2 on ssps_2.spec_id =ss.spec_id ";
		$sql_left_join_volumn_str .= " LEFT JOIN (select SUM(ssps.num-ssps.refund_num-ssps.return_num) AS recent_outnum,ssps.spec_id from stat_daily_sales_spec_warehouse ssps where ssps.sales_date >= date_add('{$where_date}',INTERVAL -{$config_data} DAY) and ssps.sales_date < '{$where_date}' {$warehouse_where} and ssps.warehouse_id IN (".$right.") group by ssps.spec_id) ssps_3 on ssps_3.spec_id =ss.spec_id ";
		$sql_left_join_str .= $sql_left_join_volumn_str;
		//多仓库
        $comma_pos = strpos($search['warehouse_id'],',');
        $all_pos = strpos($search['warehouse_id'],'all');
        $comma_count = substr_count($search['warehouse_id'],',');
        //多仓库货位设置
        if(!empty($search)&&$comma_pos===false&&$all_pos===false||!empty($search)&&$all_pos!==false&&$comma_count==1){
            $sql_fields_str='SELECT ss.spec_id AS id,cw.type as warehouse_type,ss.spec_id,ss.spec_wh_no,ss.spec_wh_no2,IFNULL(cwp.position_no,cwp2.position_no) position_no,gg.goods_id,gs.spec_no,gg.goods_no,gg.goods_name,gg.short_name,gb.brand_name brand_id,gc.class_name class_id,gs.retail_price, gs.market_price,gs.spec_code,ss.flag_id,gs.spec_name,gs.barcode,CAST(sum(ss.stock_num) AS DECIMAL(19,'.$point_number.')) stock_num,CAST(sum(IFNULL('.$available_str.',0)) AS DECIMAL(19,'.$point_number.')) avaliable_num, CAST(sum(ss.order_num) AS DECIMAL(19,'.$point_number.')) order_num,CAST(sum(ss.purchase_num) AS DECIMAL(19,'.$point_number.')) purchase_num,CAST(sum(ss.unpay_num) AS DECIMAL(19,'.$point_number.')) unpay_num, CAST(sum(ss.subscribe_num) AS DECIMAL(19,'.$point_number.')) subscribe_num,CAST(sum(ss.purchase_arrive_num) AS DECIMAL(19,'.$point_number.')) purchase_arrive_num, CAST(sum(ss.sending_num) AS DECIMAL(19,'.$point_number.')) sending_num,'.$field_right['cost_price'].', '.$field_right['all_cost_price'].',IF('.$isset_warehouse.',ss.warehouse_id,0) as warehouse_id,'.$multiple_warehouse.' as multiple_warehouse,"'.$mul_ware_ids.'" as mul_ware_ids,'.$ware_num.' as ware_num,CAST(sum(ss.safe_stock) as DECIMAL(19,'.$point_number.')) safe_stock,MAX(IF(ss.safe_stock>ss.stock_num and '.$sys_config['purchase_alarmstock_open'].',1,0)) alarm_flag,IFNULL(CAST(ssps_1.seven_outnum AS DECIMAL(19,'.$point_number.')),0) as seven_outnum,IFNULL(CAST(ssps_2.fourteen_outnum AS DECIMAL(19,'.$point_number.')),0) as fourteen_outnum,IFNULL(CAST(ssps_3.recent_outnum AS DECIMAL(19,'.$point_number.')),0) as recent_outnum FROM stock_spec ss';
            $sql_left_join_str='LEFT JOIN goods_spec gs ON ss.spec_id = gs.spec_id LEFT JOIN goods_goods gg ON gg.goods_id = gs.goods_id LEFT JOIN goods_class gc ON gc.class_id = gg.class_id LEFT JOIN goods_brand gb ON gb.brand_id = gg.brand_id left join cfg_warehouse cw on cw.warehouse_id = ss.warehouse_id left join stock_spec_position ssp on ssp.spec_id = ss.spec_id and ssp.warehouse_id = ss.warehouse_id left join cfg_warehouse_position cwp on cwp.rec_id = ssp.position_id left join cfg_warehouse_position cwp2 on cwp2.rec_id = -ss.warehouse_id ';
			$sql_left_join_str .= " LEFT JOIN (select SUM(ssps.num-ssps.refund_num-ssps.return_num) AS seven_outnum,ssps.spec_id from stat_daily_sales_spec_warehouse ssps where ssps.sales_date >= date_add('{$where_date}',INTERVAL -7 DAY) and ssps.sales_date < '{$where_date}' {$warehouse_where} and ssps.warehouse_id IN (".$right.") group by ssps.spec_id) ssps_1 on ssps_1.spec_id =ss.spec_id ";
			$sql_left_join_str .= " LEFT JOIN (select SUM(ssps.num-ssps.refund_num-ssps.return_num) AS fourteen_outnum,ssps.spec_id from stat_daily_sales_spec_warehouse ssps where ssps.sales_date >= date_add('{$where_date}',INTERVAL -14 DAY) and ssps.sales_date < '{$where_date}' {$warehouse_where} and ssps.warehouse_id IN (".$right.") group by ssps.spec_id) ssps_2 on ssps_2.spec_id =ss.spec_id ";
			$sql_left_join_str .= " LEFT JOIN (select SUM(ssps.num-ssps.refund_num-ssps.return_num) AS recent_outnum,ssps.spec_id from stat_daily_sales_spec_warehouse ssps where ssps.sales_date >= date_add('{$where_date}',INTERVAL -{$config_data} DAY) and ssps.sales_date < '{$where_date}' {$warehouse_where} and ssps.warehouse_id IN (".$right.") group by ssps.spec_id) ssps_3 on ssps_3.spec_id =ss.spec_id ";
        }
        $sql=$sql_fields_str.' INNER JOIN('.$sql_sel_limit.') ss_2 ON ss.spec_id=ss_2.spec_id '.$sql_left_join_str.' WHERE ss.warehouse_id IN ('.$right.') '.$out_where_stock_spec.' GROUP BY ss.spec_id ORDER BY '.$out_order ;//多仓库的时候   GROUP BY ss.spec_id
	    $data=array();
        try{
            $total=$this->query('select count(1) as total from ('.$sql_total.') as t');
            $total=intval($total[0]['total']);
            $list=$total?$this->query($sql):array();
            $data=array('total'=>$total,'rows'=>$list);
        }catch (\Exception $e) {
            $msg = $e->getMessage();
            \Think\Log::write($msg);
            $data=array('total' => 0, 'rows' => array());
        }
        $data = array('total' => $total, 'rows' => $list);
        return($data);
    }

    /**
     * 获取库存同步时候同步策略的库存量 ---单品
     * @param $fields
     * @param array $conditions
     * @return mixed
     */
    public function getSyncStockNumSpec($fields, $conditions=array())
    {
        try {
            $res = $this->alias('ss')->field($fields)->join("LEFT JOIN cfg_warehouse w on ss.warehouse_id=w.warehouse_id")->where($conditions)->find();
            return $res;
        } catch (\PDOException $e) {
            $msg = $e->getMessage();
            \Think\Log::write($this->name.'-getSyncStockNumSpec-'.$msg);
            E(self::PDO_ERROR);
        }
    }

    /**
     * @param $fields
     * @param array $conditions
     * @return mixed
     */
    public function getSyncStockNumSuite($fields, $conditions=array())
    {
        try {
            $res = $this->alias('ss')->field($fields)->join("left join goods_suite_detail gsd on gsd.spec_id = ss.spec_id")->join("left join cfg_warehouse w on ss.warehouse_id = w.warehouse_id")->where($conditions)->select();
            return $res;
        } catch (\PDOException $e) {
            $msg = $e->getMessage();
            \Think\Log::write($this->name.'-getSyncStockNumSuite-'.$msg);
            E(self::PDO_ERROR);
        }
    }

    /**
     * @param $data
     * @param bool $update
     * @param string $options
     * @return bool|mixed|string
     */
    public function insertStockSpecForUpdate($data, $update=false, $options='')
    {
        try {
            if(empty($data[0]))
            {
                $res = $this->add($data,$options,$update);

            }else{
                $res = $this->addAll($data,$options,$update);

            }
            return $res;
        } catch (\PDOException $e) {
            $msg = $e->getMessage();
            \Think\Log::write($this->name.'-insertGoodsSpecForUpdate-'.$msg);
            E(self::PDO_ERROR);
        }
    }
    public function getStockSpecObject($fields,$condtions=array())
    {
        try {
            $result = $this->field($fields)->where($condtions)->find();
            return $result;
        } catch (\PDOException $e) {
            $msg = $e->getMessage();
            \Think\Log::write($this->name.'--getStockSpecObject--'.$msg);
            E(self::PDO_ERROR);
        }
    }
    public function updateStockSpec($data,$conditions)
    {
        try {
            $res_update = $this->where($conditions)->save($data);
            return $res_update;
        }catch(\PDOException $e)
        {
            $msg = $e->getMessage();
            \Think\Log::write($this->name.'-updateStockSpec-'.$msg);
            E(self::PDO_ERROR);
        }

    }
    public function getCheckData($check_info)
    {
        try{//"SELECT gs.spec_id AS id, gs.spec_no, gg.goods_no, gg.goods_name, ss.stock_num as stock_num, ss.cost_price,gg.goods_id, gg.goods_no, gs.spec_name, gs.spec_id, gs.spec_no, gs.spec_code FROM goods_spec as gs LEFT JOIN goods_goods AS gg ON gs.goods_id = gg.goods_id LEFT JOIN stock_spec AS ss ON ss.spec_id = gs.spec_id WHERE gs.spec_id = '%s' AND ss.warehouse_id = '%s' order by ss.warehouse_id limit 1 "
            $res = $this->fetchSql(false)->alias('ss')->field('IFNULL(cwp.position_no,cwp2.position_no) position_no,IFNULL(cwp.rec_id,cwp2.rec_id) position_id,ss.warehouse_id,gs.spec_id AS id, gs.spec_no, gg.goods_no, gg.goods_name, ss.stock_num as stock_num, ss.cost_price,gg.goods_id, gg.goods_no, gs.spec_name, gs.spec_id, gs.spec_no, gs.spec_code')->join('left join goods_spec gs on gs.spec_id = ss.spec_id')->join('left join goods_goods gg on gg.goods_id = gs.goods_id')->join('left join stock_spec_position ssp on ssp.warehouse_id=ss.warehouse_id and ssp.spec_id = ss.spec_id')->join('left join cfg_warehouse_position cwp on cwp.rec_id = ssp.position_id')->join('left join cfg_warehouse_position cwp2 on cwp2.rec_id = -ss.warehouse_id')->where(array('ss.spec_id'=>array('eq',$check_info['spec_id']),'ss.warehouse_id'=>array('eq',$check_info['warehouse_id'])))->select();
        }catch (\PDOException $e){
            $msg = $e->getMessage();
            \Think\Log::write($this->name.'-getCheckData-'.$msg);
            E(self::PDO_ERROR);
        }
        return $res;
    }
    public function getDefaultPosition($position_info)
    {
        try{

            $data = array();
            $spec_list = array();
            foreach($position_info['detail'] as $key=>$value){
                array_push($spec_list,$value);
            }
            $spec_list = implode(',',$spec_list);
            $where = array(
                'ss.warehouse_id'=>$position_info['warehouse_id'],
                'ss.spec_id'=>array('in',$spec_list)
            );
            $fields = array(
                'ss.spec_id',
                '(CASE  WHEN ss.last_position_id THEN ss.last_position_id WHEN ss.default_position_id THEN ss.default_position_id ELSE -'.$position_info["warehouse_id"].' END) AS position_id',
                '(CASE  WHEN ss.last_position_id THEN cwp.position_no WHEN ss.default_position_id THEN cwp2.position_no ELSE cwp3.position_no END) AS position_no',
                'IF(ss.last_position_id,1,IF(ss.spec_id IS NOT NULL AND (ss.order_num <> 0 OR ss.sending_num <> 0),1,0)) AS is_allocated',
                'CAST(IFNULL(ss.cost_price,0) AS DECIMAL(19,4)) AS src_price'
            );
            $res = $this->fetchSql(false)->alias('ss')->field($fields)->join('LEFT JOIN cfg_warehouse_position cwp ON(ss.last_position_id = cwp.rec_id)')->join('LEFT JOIN cfg_warehouse_position cwp2 ON(ss.default_position_id= cwp2.rec_id)')->join('LEFT JOIN cfg_warehouse_position cwp3 ON( cwp3.rec_id = -'.$position_info['warehouse_id'].')')->where($where)->select();
            $def_position_no = D('Setting/WarehousePosition')->where(array('rec_id'=>'-'.$position_info['warehouse_id']))->getField('position_no');
            foreach($position_info['detail'] as $k=>$v){
                foreach($res as $row){
                    if($row['spec_id'] == $v){
                        $data[$k] = array('position_id'=>$row['position_id'],'src_price'=>$row['src_price'],'position_no'=>$row['position_no'],'is_allocated'=>$row['is_allocated']);
                        continue 2;
                    }
                }
                $data[$k] = array('position_id'=>-$position_info['warehouse_id'],'src_price'=>0.0000,'position_no'=>$def_position_no,'is_allocated'=>0);
            }
            return $data;
        }catch (BusinessLogicException $e){
            SE($e->getMessage());
        }catch(\PDOException $e){
            \Think\Log::write($e->getMessage());
            SE(self::PDO_ERROR);
        }catch(\Exception $e){
            \Think\Log::write($e->getMessage());
            SE(self::PDO_ERROR);
        }
    }

    /**
     * @param $stat_data array 盘点数据
     * @throws \Think\Exception\BusinessLogicException
     */
    public function quickCheck($stat_data)
    {
        try{
            $this->startTrans();
            $operator_id = get_operator_id();
            //----------校验数据是否正确
            if($stat_data['new_stock_num']<0){
                SE('填写库存数量不能为负数!');
            }
            //----------加锁防止停用时把货位信息删除
            $position_info = D('Stock/StockSpecPosition')->field(array('position_id','stock_num'))->where(array('warehouse_id'=>$stat_data['warehouse_id'],'spec_id'=>$stat_data['spec_id']))->lock(true)->find();
            if(empty($position_info)){
                if($stat_data['position_id'] != -$stat_data['warehouse_id']){
                    SE('盘点货位不正确!');
                }else{
                    $position_id    = -$stat_data['warehouse_id'];
                    $stock_info     = $this->field(array('stock_num'))->where(array('warehouse_id'=>$stat_data['warehouse_id'],'spec_id'=>$stat_data['spec_id']))->find();
                    $stock_num      = $stock_info['stock_num'];
                }
            }else{
                $position_id    = $position_info['position_id'];
                $stock_num      = $position_info['stock_num'];
            }

            //---------生成盘点信息
            $pd_order_data = array(
                'pd_no'         => array('exp',"FN_SYS_NO('stockpd')"),
                'creator_id'       => $operator_id,
                'status'        => 1,
                'warehouse_id'  => $stat_data['warehouse_id'],
                'mode'          => 2,
                'type'          => 1,
                'remark'        => '',
                'created'       => array('exp','NOW()')
            );
            $pd_id = M('stock_pd')->add($pd_order_data);
            $pd_detail_data = array(
                'pd_id'         => $pd_id,
                'spec_id'       => $stat_data['spec_id'],
                'position_id'   => $position_id,
                'old_num'       => $stock_num,
                'input_num'     => $stat_data['new_stock_num'],
                'remark'        => '',
                'created'       => array('exp','NOW()'),
            );
            M('stock_pd_detail')->add($pd_detail_data);
            //--------提交盘点
            $this->submitPd($pd_id);

            $this->commit();
        }catch(BusinessLogicException $e){
            $this->rollback();
            SE($e->getMessage());
        }catch(\PDOException $e){
            $this->rollback();
            $msg = $e->getMessage();
            \Think\Log::write('-quickCheckStockSpec-'.$msg);
            SE(self::PDO_ERROR);
        }catch(\Exception $e){
            $this->rollback();
            $msg = $e->getMessage();
            \Think\Log::write('-quickCheckStockSpec-'.$msg);
            SE(self::PDO_ERROR);
        }
    }

    /**
     * 盘点提交,更新库存数据信息
     *
     * @param $pd_id int 盘点单id
     * @throws \Think\Exception\BusinessLogicException
     */
    public function submitPd($pd_id,$is_return=false)
    {
        try{
            $operator_id = get_operator_id();
            $field = array('pd_no', 'warehouse_id', 'status', 'mode', 'type');
            $where = array('rec_id'=>$pd_id);
            $pd_info = M('stock_pd')->field($field)->where($where)->find();
            if(empty($pd_info)){
                if($is_return){
                    return '盘点单不存在！';
                }else{
                    SE('盘点单不存在！');
                }
            }
            if($pd_info['status']!=1){
                if($is_return){
                    return '盘点状态已改变！';
                }else{
                    SE('盘点状态已改变！');
                }
            }

            //----------查询盘点详情
            $detail_field = array('rec_id', 'input_num','spec_id');
            $detail_where = array('pd_id'=>$pd_id);
            $detail_info = M('stock_pd_detail')->field($detail_field)->where($detail_where)->select();
            $stock_spec_ids = '';
            foreach($detail_info as $item_info){
                $stock_spec_ids .= $item_info['spec_id'].',';
            }
            $stock_spec_ids = substr($stock_spec_ids,0,-1);
            $stpck_spec_where = array(
                'warehouse_id'=>$pd_info['warehouse_id'],
                'spec_id'=>array('in',$stock_spec_ids),
            );
            $stock_spec_ids_arr = explode(',',$stock_spec_ids);
            $stock_spec_query = M('stock_spec')->fetchSql(false)->field('spec_id')->where($stpck_spec_where)->select();
            $stock_spec_query_arr = array();
            foreach($stock_spec_query as $item_info){
                array_push($stock_spec_query_arr,$item_info['spec_id']);
            }
            $del_arr = array_diff($stock_spec_ids_arr,$stock_spec_query_arr);
            if(!empty($del_arr)){
                $del_spec_id = array_values($del_arr)[0];
                $del_spec_no = M('goods_spec')->field('spec_no')->find($del_spec_id);
                if($is_return){
                    return $del_spec_no['spec_no'].'在该仓库中不存在！';
                }else{
                    SE($del_spec_no['spec_no'].'在该仓库中不存在！');
                }
            }
            $isset_stockin = 0;
            $isset_stockout = 0;
            foreach($detail_info as $detail_item)
            {
                $pd_detail_info = M('stock_pd_detail')->alias('spd')->field('gs.spec_id,gs.goods_id,gg.goods_no,gg.goods_name,gs.spec_no,gs.spec_code,gs.spec_name,spd.position_id, spd.old_num')->join('LEFT JOIN goods_spec gs ON gs.spec_id=spd.spec_id')->join('LEFT JOIN goods_goods gg ON gs.goods_id=gg.goods_id')->where(array('spd.rec_id'=>$detail_item['rec_id']))->find();//spd.expire_date, spd.batch_id,
                switch ($pd_info['mode'])
                {
                    case '0'://单品盘点
                    {
                        $stock_spec_position_model = D('Stock/StockSpecPosition');
                        $now_stock_position_num = $stock_spec_position_model->field('stock_num')->where(array('spec_id'=>$pd_detail_info['spec_id'],'warehouse_id'=>$pd_info['warehouse_id']))->find();
                        $now_stock_num = $this->field('stock_num')->where(array('spec_id'=>$pd_detail_info['spec_id'],'warehouse_id'=>$pd_info['warehouse_id']))->find();
                        if($now_stock_position_num['stock_num'] != $now_stock_num['stock_num']){
                            $stock_spec_position_model->where(array('spec_id'=>$pd_detail_info['spec_id'],'warehouse_id'=>$pd_info['warehouse_id']))->save(array('stock_num'=>$now_stock_num['stock_num']));
                        }
                        $now_stock_num = $now_stock_num['stock_num'];
                        break;
                    }
                    case '2'://明细盘点
                    {
                        $stock_spec_position_model = D('Stock/StockSpecPosition');
                        $now_stock_position_num = $stock_spec_position_model->field('stock_num')->where(array('spec_id'=>$pd_detail_info['spec_id'],'warehouse_id'=>$pd_info['warehouse_id']))->find();
                        $now_stock_num = $this->field('stock_num')->where(array('spec_id'=>$pd_detail_info['spec_id'],'warehouse_id'=>$pd_info['warehouse_id']))->find();
                        if($now_stock_position_num['stock_num'] != $now_stock_num['stock_num']){
                            $stock_spec_position_model->where(array('spec_id'=>$pd_detail_info['spec_id'],'warehouse_id'=>$pd_info['warehouse_id']))->save(array('stock_num'=>$now_stock_num['stock_num']));
                        }
                        $now_stock_num = $now_stock_num['stock_num'];
                        break;
                    }
                }
                //--------获取成本价
                $cost_price = $this->field('cost_price')->where(array('spec_id'=>$pd_detail_info['spec_id'],'warehouse_id'=>$pd_info['warehouse_id']))->find();
                $cost_price = $cost_price['cost_price'];
                //-------更新盘点数量和状态
                $update_detail_data = array(
                    'pd_flag'       => 1,
                    'old_num'       => $now_stock_num,
                    'new_num'       => $detail_item['input_num'],
                    'cost_price'    => $cost_price,
                );
                $update_detail_where = array('rec_id'=>$detail_item['rec_id']);
                M('stock_pd_detail')->where($update_detail_where)->save($update_detail_data);
                //-------判断是否入库

                if($detail_item['input_num']>$now_stock_num)
                {
                    if(!$isset_stockin)
                    {
                        $stockin_data = array(
                            'stockin_no'    => array('exp',"FN_SYS_NO('stockin')"),
                            'status'        => 20,
                            'warehouse_id'  => $pd_info['warehouse_id'],
                            'src_order_type'=> 4,
                            'src_order_id'  => $pd_id,
                            'src_order_no'  => $pd_info['pd_no'],
                            'operator_id'   => $operator_id,
                            'created'       => array('exp','NOW()')
                        );
                        $stockin_id = D('Stock/StockInOrder')->add($stockin_data);
                        $isset_stockin = 1;

                        $stockin_log = array(
                            'order_type'    => 1,
                            'order_id'      => $stockin_id,
                            'operator_id'   => $operator_id,
                            'operate_type'  => 13,
                            'message'       => '生成盘点入库单'
                        );
                        $res_stockin_log = D('Stock/StockInoutLog')->add($stockin_log);

                    }
                    //------------入库单详情
                    $stockin_detail_data = array(
                        'stockin_id'            => $stockin_id,
                        'src_order_type'        => 4,
                        'src_order_detail_id'   => $detail_item['rec_id'],
                        'spec_id'               => $pd_detail_info['spec_id'],
                        'position_id'           => $pd_detail_info['position_id'],
                        'num'                   => $detail_item['input_num']-$now_stock_num,
                        'src_price'             => $cost_price,
                        'cost_price'            => $cost_price
                    );
                    $res_stockin_detail = D('Stock/StockinOrderDetail')->add($stockin_detail_data);
                }
                //-------判断是否出库

                if($detail_item['input_num']<$now_stock_num)
                {
                    if(!$isset_stockout)
                    {
                        $stockout_data = array(
                            'stockout_no'       => array('exp',"FN_SYS_NO('stockout')"),
                            'status'            => 48,
                            'warehouse_id'      => $pd_info['warehouse_id'],
                            'src_order_type'    => 4,
                            'src_order_id'      => $pd_id,
                            'src_order_no'      => $pd_info['pd_no'],
                            'operator_id'       => $operator_id,
                            'created'           => array('exp','NOW()')
                        );
                        $stockout_id = D('Stock/StockOutOrder')->add($stockout_data);
                        $isset_stockout = 1;
                        $stockout_log = array(
                            'order_type'        => 2,
                            'order_id'          => $stockout_id,
                            'operator_id'       => $operator_id,
                            'operate_type'      => 13,
                            'message'           => '生成盘点出库单'
                        );
                        $res_stockout_log = D('Stock/StockInoutLog')->add($stockout_log);
                    }
                    //------------出库单详情
                    $stockout_detail_data = array(
                        'stockout_id'           => $stockout_id,
                        'src_order_type'        => 4,
                        'src_order_detail_id'   => $detail_item['rec_id'],
                        'spec_id'               => $pd_detail_info['spec_id'],
                        'spec_no'               => $pd_detail_info['spec_no'],
                        'spec_code'             => $pd_detail_info['spec_code'],
                        'spec_name'             => $pd_detail_info['spec_name'],
                        'goods_id'              => $pd_detail_info['goods_id'],
                        'goods_name'            => $pd_detail_info['goods_name'],
                        'goods_no'              => $pd_detail_info['goods_no'],
                        'position_id'           => $pd_detail_info['position_id'],
                        'num'                   => $now_stock_num-$detail_item['input_num'],
                        'price'                 => $cost_price,
                        'total_amount'          => $cost_price*($now_stock_num-$detail_item['input_num']),
                    );
                    $res_stockout_detail = D('Stock/StockoutOrderDetail')->add($stockout_detail_data);
                }
            }
            //----------更新盘点单状态
            M('stock_pd')->where(array('rec_id'=>$pd_id))->save(array('status'=>2));
            $pd_inout_log = array(
                'order_type'        => 4,
                'order_id'          => $pd_id,
                'operator_id'       => $operator_id,
                'operate_type'      => 16,
                'message'           => array('exp',"CONCAT('确定盘点:','".$pd_info['pd_no']."')")
            );
            $res_pd_inout_log = D('Stock/StockInoutLog')->add($pd_inout_log);

            if($isset_stockin)
            {
                //-------统计相关详情
                $stat_in_info = D('Stock/StockinOrderDetail')->field(array('COUNT(DISTINCT spec_id) as goods_type_count','SUM(num) as goods_count','SUM(src_price*num) as goods_amount','SUM(src_price*num) as total_price'))->where(array('stockin_id'=>$stockin_id))->select();
                $res_stockin_update = D('Stock/StockInOrder')->where(array('stockin_id'=>$stockin_id))->save($stat_in_info[0]);
                //----入库单提交
                $order_detial = array(
                    'id'                => $stockin_id,
                    'src_order_type'    => 4,
                    'src_order_no'      => $pd_info['pd_no'],
                    'warehouse_id'      => $pd_info['warehouse_id']
                );
                $check_stockin_result = D('Stock/StockIn')->submitStockInOrder($order_detial,'init');
                if($check_stockin_result['status']==1){
                    if($is_return){
                        return $check_stockin_result['info'];
                    }else{
                        SE($check_stockin_result['info']);
                    }
                }

            }
            if($isset_stockout)
            {

                //-------统计相关详情,更新出库信息
                $stat_out_info = D('Stock/StockoutOrderDetail')->field(array('COUNT(DISTINCT spec_id) as goods_type_count','SUM(num) as goods_count','SUM(total_amount) as goods_amount'))->where(array('stockout_id'=>$stockout_id))->select();
                $res_stockout_update = D('Stock/StockOutOrder')->where(array('stockout_id'=>$stockout_id))->save($stat_out_info[0]);
                //----出库单提交
                D('Stock/StockOutOrder')->checkStockout($stockout_id);
            }
        }catch(BusinessLogicException $e){
            SE($e->getMessage());
        }catch(\PDOException $e){
            $msg = $e->getMessage();
            \Think\Log::write('-submitPd-'.$msg);
            SE(self::PDO_ERROR);
        }catch(\Exception $e){
            $msg = $e->getMessage();
            \Think\Log::write('-submitPd-'.$msg);
            SE(self::PDO_ERROR);
        }
    }
    public function getAdjustPriceInfo($spec_id,$warehouse_id)
    {
        try {
            $where = array(
                'ss.spec_id'=>array('in',$spec_id),
                'cw.is_disabled'=>array('neq',1)
            );
            if(!empty($warehouse_id) && !$warehouse_id==''){
                $where['ss.warehouse_id']= $warehouse_id;
            }
			$point_number = get_config_value('point_number',0);
            $point_number = intval($point_number);
			$stock_num = "CAST(ss.stock_num AS DECIMAL(19,".$point_number.")) stock_num";
            $res = $this->alias('ss')->fetchSql(false)->field("ss.spec_id,'' as adjust_price,ss.warehouse_id,".$stock_num.",'' as remark,ss.cost_price,gs.spec_code,gs.spec_no,gs.spec_name,gg.goods_no,gg.goods_name,cw.name warehouse_name")->join('left join goods_spec gs on gs.spec_id = ss.spec_id')->join('left join goods_goods gg on gg.goods_id = gs.goods_id')->join('left join cfg_warehouse cw on cw.warehouse_id = ss.warehouse_id')->where($where)->select();
			$result = array('total' => count($res), 'rows' => $res);
        }catch(\PDOException $e){
            \Think\Log::write($e->getMessage());
            SE(self::PDO_ERROR);
        }catch(\Exception $e){
            \Think\Log::write($e->getMessage());
            SE(self::PDO_ERROR);
        }
        return $result;
    }
    public function getGoodsInfo($spec_id)
    {
        try{
            $res = D('Goods/GoodsSpec')->alias('gs')->fetchSql(false)->field(array('replace(trim(gs.spec_no),char(9),"") as spec_no,replace(trim(gs.spec_name),char(9),"") as spec_name,replace(trim(gs.spec_code),char(9),"") as spec_code,replace(trim(gg.goods_no),char(9),"") as goods_no,replace(trim(gg.goods_name),char(9),"") as goods_name'))->join('left join goods_goods gg on gg.goods_id = gs.goods_id')->where(array('gs.spec_id'=>$spec_id))->find();
		}catch (\PDOException $e){
            \Think\Log::write($e->getMessage());
            SE(self::PDO_ERROR);
        }catch(\Exception $e){
            \Think\Log::write($e->getMessage());
            SE(self::PDO_ERROR);
        }
        return $res;
    }
    public function fastAdjustCostPrice($adjust_info)
    {
        try{
            $this->startTrans();
            $operator_id = get_operator_id();
            $_validate = array(
                array('warehouse_id,spec_id','checkIsset','调整成本价对应库存信息不存在!',1,'callback'),
            );
            foreach($adjust_info as $adjust_item)
            {
                if(!$this->validate($_validate)->create($adjust_item))
                {
                    SE($this->getError());
                }
            }

            foreach($adjust_info as $adjust_item)
            {

                //---------获取成本价信息
                $cost_price = $this->where(array('warehouse_id'=>$adjust_item['warehouse_id'],'spec_id'=>$adjust_item['spec_id']))->getField('cost_price');
                //---------新建调整成本价
                $adjust_order_data = array(
                    'adjust_no'     => array('exp',"FN_SYS_NO('stockinadjust')"),
                    'warehouse_id'  => $adjust_item['warehouse_id'],
                    'src_order_type'=> 0,
                    'status'        => 1,
                    'adjust_amount' => $adjust_item['stock_num']*(floatval($adjust_item['adjust_price'])-floatval($cost_price)),
                    'operator_id'   => $operator_id,
                    'created'       => array('exp',"NOW()")
                );
                $adjust_order_id = M('stockin_adjust_order')->add($adjust_order_data);
                //-------新建调整成本价订单详情
                $adjust_detail_data = array(
                    'stockin_adjust_id'     => $adjust_order_id,
                    'src_order_type'        => 0,
                    'src_order_detail_id'   => 0,
                    'spec_id'               => $adjust_item['spec_id'],
                    'num'                   => $adjust_item['stock_num'],
                    'adjust_price'          => floatval($adjust_item['adjust_price'])-floatval($cost_price),
                    'total_adjust_price'    => intval($adjust_item['stock_num'])*(floatval($adjust_item['adjust_price'])-floatval($cost_price)),
                    'remark'                => $adjust_item['remark'],
                    'created'               => array('exp',"NOW()")
                );
                M('stockin_adjust_order_detail')->add($adjust_detail_data);
                //-----调价单审核
                D('Stock/StockinAdjust')->checkAdjustOrder($adjust_order_id);
                //----修改调价单状体
                D('Stock/StockinAdjust')->save(array('rec_id'=>$adjust_order_id,'status'=>2));
            }
            $this->commit();
        }catch (BusinessLogicException $e){
            $this->rollback();
            $msg = $e->getMessage();
            SE($msg);
        }catch (\PDOException $e){
            $this->rollback();
            $msg = $e->getMessage();
            \Think\Log::write($this->name.'-fastAdjustCostPrice-'.$msg);
            SE(self::PDO_ERROR);
        }catch (\Exception $e){
            $this->rollback();
            $msg = $e->getMessage();
            \Think\Log::write($this->name.'-fastAdjustCostPrice-'.$msg);
            SE(self::PDO_ERROR);
        }
    }
    protected function checkIsset($check_info)
    {
        try{
            $count = $this->where($check_info)->count();
            if($count>0){
                return true;
            }else{
                return false;
            }
        }catch (\PDOException $e) {
            $msg = $e->getMessage();
            \Think\Log::write($this->name.'-checkIsset-'.$msg);
            return false;
        }
    }
    //这里是功能函数，只做了校验正确与否
    //return  boolean
    public function checkIsAllocatedPosition($warehouse_id,$spec_id,$position_id)
    {
        try{
            //select (CASE  WHEN ss.last_position_id THEN ss.last_position_id ELSE -".intval($warehouse_id)." END) AS position
            $position_info = $this->fetchSql(false)->alias('ss')->field("IFNULL(ssp.position_id,-".intval($warehouse_id).") as position_id")->join("left join stock_spec_position ssp on ss.spec_id = ssp.spec_id and ss.warehouse_id = ssp.warehouse_id")->where(array('ss.warehouse_id'=>$warehouse_id,'ss.spec_id'=>$spec_id))->find();
            //$position_info = $this->fetchSql(false)->alias('ss')->field("ssp.position_id as position_id")->join("left join stock_spec_position ssp on ss.spec_id = ssp.spec_id and ss.warehouse_id = ssp.warehouse_id")->where(array('ss.warehouse_id'=>$warehouse_id,'ss.spec_id'=>$spec_id))->find();
            if(empty($position_info)){
                return false;
            }
            if($position_info['position_id'] != $position_id && $position_info['position_id'] != '-'.intval($warehouse_id) )
            {
                return true;
            }else{
                return false;
            }
        }catch(\PDOException $e){
            \Think\Log::write($this->name."-checkIsAllocatedPosition-".$e->getMessage());
            return true;
        }catch(\Exception $e){
            \Think\Log::write($this->name."-checkIsAllocatedPosition-".$e->getMessage());
            return true;
        }
    }
    public function alarmPerDaySearch($page = 1, $rows = 20, $search = array(), $sort = 'id', $order = 'desc')
    {
//        $warehouse_list = D('Setting/Warehouse')->field('warehouse_id')->where(array('is_disabled'=>0))->order('warehouse_id asc')->select();
//        //-------调试注释
//        /*if(count($warehouse_list) == 1)
//        {
//            $search['warehouse_id'] = $warehouse_list[0]['warehouse_id'];
//        }else{
//            $data = array('total' => 0, 'rows' => array());
//        }*/
//        $search['warehouse_id'] = $warehouse_list[0]['warehouse_id'];
		$warehouse_list = D('Setting/EmployeeRights')->setSearchRights($search,'warehouse_id',2);
//        D('Setting/EmployeeRights')->setSearchRights($search,'provider_ids',4);
        $sys_config =  get_config_value(array('sys_available_stock','point_number','sys_available_purchase'),array(640,0,0));
        $available_str_stock = D('Stock/StockSpec')->getAvailableStrBySetting($sys_config['sys_available_stock']);
        $available_str_purchase = D('Stock/StockSpec')->getAvailableStrBySetting($sys_config['sys_available_purchase'],'ss','stock_num');
        $available_str_purchase .= '-ss.safe_stock';
        $where_goods_spec = '';
        $where_goods_goods = '';
        $where_stock_spec = '';
		$where_ppg_spec = '';
        $where_left_join_goods_class='';
        set_search_form_value($where_goods_spec, 'deleted', 0, 'gs_1', 2, ' AND ');
        set_search_form_value($where_goods_goods, 'deleted', 0, 'gg_1', 2, ' AND ');
        $isset_warehouse = 0;
        foreach ($search as $k => $v) {
            if ($v === '')  continue;
            switch ($k) {
				case 'provider_id':
					set_search_form_value($where_ppg_spec, $k, $v, 'ppg_1', 2, ' AND ');
					break;
//                case 'provider_ids':
//					set_search_form_value($where_ppg_spec, 'provider_id', $v, 'ppg_1', 2, ' AND ');
//					break;
                case 'spec_no'://商家编码      goods_spec
                    set_search_form_value($where_goods_spec, $k, $v, 'gs_1', 10, ' AND ');
                    break;
                case 'sales_cycle_days'://销售周期      goods_spec
                    set_search_form_value($where_goods_spec, $k, $v, 'gs_1', 1, ' AND ');
                    break;
                case 'barcode'://条形码   goods_spec
                    set_search_form_value($where_goods_spec, $k, $v, 'gs_1', 1, ' AND ');
                    break;
                case 'goods_no': //货品编号     goods_gooods
                    set_search_form_value($where_goods_goods, $k, $v, 'gg_1', 1, ' AND ');
                    break;
                case 'goods_name'://货品名称  goods_gooods
                    set_search_form_value($where_goods_goods, $k, $v, 'gg_1', 6, ' AND ');
                    break;
                case 'class_id'://分类         goods_gooods
                    $where_left_join_goods_class=set_search_form_value($where_goods_goods, $k, $v, 'gg_1', 7, ' AND ');
                    break;
                case 'brand_id'://品牌        goods_gooods
                    set_search_form_value($where_goods_goods, $k, $v, 'gg_1', 2, ' AND ');
                    break;
                case 'warehouse_id'://仓库   stock_spec
                    set_search_form_value($where_stock_spec, $k, $v, 'ss_1', 2, ' AND ');
                    break;
            }
        }
        if(empty($search['warehouse_id']) || intval($search['warehouse_id']) == 0)
        {
            $warehouse_id = 0;
        }else{
            $warehouse_id = intval($search['warehouse_id']);
        }
        $page = intval($page);
        $rows = intval($rows);
        $arr_sort=array('id'=>'spec_id');
        $limit=($page - 1) * $rows . "," . $rows;//分页
        $order = 'ss.'.$arr_sort[$sort].' '.$order;//排序
        $order = addslashes($order);
        $sql_sel_limit='SELECT ss_1.rec_id FROM stock_spec ss_1 ';
        $sql_total='SELECT sum(ss_1.stock_num) stock_num,sum(ss_1.safe_stock) safe_stock, ss_1.spec_id   FROM stock_spec ss_1 ';
        $flag=false;
        $sql_where='';
        if(!empty($where_goods_spec)||!empty($where_goods_goods))
        {
            $sql_where .= ' LEFT JOIN goods_spec gs_1 ON gs_1.spec_id=ss_1.spec_id ';
        }
		if(!empty($where_ppg_spec))
        {
            $sql_where .= ' LEFT JOIN purchase_provider_goods ppg_1 ON gs_1.spec_id=ppg_1.spec_id ';
        }       
	   if(!empty($where_goods_goods))
        {
            $sql_where .= ' LEFT JOIN goods_goods gg_1 ON gg_1.goods_id=gs_1.goods_id ';
            $sql_where .= $where_left_join_goods_class;
        }


        connect_where_str($sql_where, $where_goods_spec, $flag);
        connect_where_str($sql_where, $where_goods_goods, $flag);
        connect_where_str($sql_where, $where_stock_spec, $flag);
		connect_where_str($sql_where, $where_ppg_spec, $flag);
        connect_where_str($sql_where, 'AND ss_1.stock_num<ss_1.safe_stock ', $flag);
        $sql_where.='';
        $sql_sel_limit.=$sql_where;//多仓库的时候 $sql_where.' GROUP BY ss_1.spec_id ORDER BY '.$order.' LIMIT '.$limit

        $sql_total.=$sql_where.' GROUP BY ss_1.spec_id';

        $point_number = $sys_config['point_number'];
        $point_number = intval($point_number);
        //单仓库情况--SP_STOCK_SPEC_QUERY(后期加多仓库，改进)
        $sql_fields_str='SELECT ss.spec_id AS id,if(ppg.provider_id is null,1,0) as is_provider,ppg.provider_id,ss.spec_id,gb.brand_name,gc.class_name,gs.spec_no,gg.goods_name,gg.goods_no,gs.spec_name,gs.spec_code,gs.barcode,CAST(sum(ss.stock_num) AS DECIMAL(19,'.$point_number.')) stock_num,CAST(sum(IFNULL('.$available_str_stock.',0)) AS DECIMAL(19,'.$point_number.')) avaliable_num,CAST(sum(IF('.$available_str_purchase.'<0,-('.$available_str_purchase.'),0)) AS DECIMAL(19,'.$point_number.')) need_purchase_num,CAST(sum(ss.subscribe_num) AS DECIMAL(19,'.$point_number.')) subscribe_num,CAST(sum(ss.order_num) AS DECIMAL(19,'.$point_number.')) order_num,CAST(sum(ss.sending_num) AS DECIMAL(19,'.$point_number.')) sending_num,CAST(sum(ss.unpay_num) AS DECIMAL(19,'.$point_number.')) unpay_num,CAST(sum(ss.purchase_arrive_num) AS DECIMAL(19,'.$point_number.')) purchase_arrive_num,CAST(sum(ss.purchase_num) AS DECIMAL(19,'.$point_number.')) purchase_num,CAST(sum(ss.safe_stock) AS DECIMAL(19,'.$point_number.')) safe_stock,'.$warehouse_id.' warehouse_id  FROM stock_spec ss';
        $sql_left_join_str='LEFT JOIN goods_spec gs ON ss.spec_id = gs.spec_id LEFT JOIN goods_goods gg ON gg.goods_id = gs.goods_id LEFT JOIN goods_class gc ON gc.class_id = gg.class_id LEFT JOIN goods_brand gb ON gb.brand_id = gg.brand_id  left join purchase_provider_goods ppg on ppg.spec_id = gs.spec_id';//LEFT JOIN cfg_warehouse_position cwp ON cwp.rec_id = ss.default_position_id
        $sql=$sql_fields_str.' INNER JOIN('.$sql_sel_limit.') ss_2 ON ss.rec_id=ss_2.rec_id '.$sql_left_join_str.' GROUP BY ss.spec_id ORDER BY '.$order.' LIMIT '.$limit ;//多仓库的时候   GROUP BY ss.spec_id
        echo $sql;die;
        $data=array();
        try{
            $total=$this->query('select count(1) as total from ('.$sql_total.' ) as t');
            $total=intval($total[0]['total']);
            $list=$total?$this->query($sql):array();
            $data=array('total'=>$total,'rows'=>$list);
        }catch (\Exception $e) {
            $msg = $e->getMessage();
            \Think\Log::write($msg);
            $data=array('total' => 0, 'rows' => array());
        }
        $data = array('total' => $total, 'rows' => $list);
        return($data);
    }
    /**
     * 工具函数，根据配置获取计算方式
     * @param number $sys_available_stock 库存640 待采购量961
     * @param string $alias 字段前缀别名(表别名)
     * @param string $column 要获取的字段值
     * @return string 返回计算方式字符串
     */
    public function getAvailableStrBySetting($sys_available_stock,$alias='ss',$column='stock_num')
    {
        try{
            $sys_available_stock = intval($sys_available_stock);
            $available_ar= array(
                0=>array('+','.purchase_num'),
                1=>array('+','.to_purchase_num'),
                2 =>array('+','.transfer_num'),
                3=>array('+','.purchase_arrive_num'),
                4=>array('+','.return_onway_num'),
                5=>array('+','.refund_exch_num'),
                6=>array('-','.subscribe_num'),
                7=>array('-','.order_num'),
                8=>array('-','.unpay_num'),
                9=>array('-','.sending_num'),
                10=>array('-','.return_num'),
                11=>array('-','.refund_num'),
                12=>array('-','.return_exch_num'),
                13=>array('-','.refund_onway_num'),
                14=>array('-','.lock_num'),
                15=>array('-','.to_transfer_num'),);
            $str = $alias.'.'.$column;
            for($i = 0;$i<count($available_ar);$i++)
            {
                if($sys_available_stock & pow(2,$i))
                {
                    $str.=$available_ar[$i][0].$alias.$available_ar[$i][1];
                }
            }
//            $res = $this->query('select FN_GET_STOCK('.$sys_available_stock.') as available_str');
            return $str;
        }catch (\PDOException $e){
            \Think\Log::write($this->name.'-getStockNumBySetting-'.$e->getMessage());
            E(self::PDO_ERROR);
        }catch (\Exception $e){
            \Think\Log::write($this->name.'-getStockNumBySetting-'.$e->getMessage());
            E(self::PDO_ERROR);
        }
    }

    public function getStockAlarmDataByGoodsSpe($ids)
    {
        try{
            $fields = array(
                'gs.spec_no',
                'gs.spec_code',
                'gg.goods_no',
                'gg.goods_name',
                'ss.warehouse_id',
            );
            $alarm_fields = array(
                'ss.safe_stock',
                 'ss.alarm_type',
                 'ss.alarm_days',
                 'ss.sales_rate_type',
                 'ss.sales_rate_cycle',
                 'ss.sales_fixrate'
            );
			$search = array();
			D('Setting/EmployeeRights')->setSearchRights($search,'warehouse_id',2);
			$right_warehouse_id = $search['warehouse_id'];
			if(empty($right_warehouse_id) || $right_warehouse_id == ''){SE('没有仓库权限');}
            $res = $this->alias('ss')->fetchSql(false)->field($fields)->join('left join goods_spec gs on gs.spec_id = ss.spec_id ')->join('left join goods_goods gg on gg.goods_id = gs.goods_id')->where(array('ss.spec_id'=>$ids[0]['spec_id'],'ss.warehouse_id'=>array('in',$right_warehouse_id)))->order('ss.warehouse_id asc')->select();
		   $goods_info = array('spec_no'=>$res[0]['spec_no'],'spec_code'=>$res[0]['spec_code'],'goods_no'=>$res[0]['goods_no'],'goods_name'=>$res[0]['goods_name']);
            $alarm_setting = $this->alias('ss')->field($alarm_fields)->where(array('ss.spec_id'=>$ids[0]['spec_id'],'ss.warehouse_id'=>$res[0]['warehouse_id']))->find();
            $warehouse_ids = array_column($res,'warehouse_id');
            $warehouse_list = D('Setting/Warehouse')->field('warehouse_id AS id,name')->where(array('warehouse_id'=>array('in',$warehouse_ids)))->order('warehouse_id asc')->select();
            return array('goods_info'=>$goods_info,'alarm_setting'=>$alarm_setting,'warehouse_list'=>$warehouse_list);
        }catch (\PDOException $e){
            \Think\Log::write($this->name.'-getMultiStockAlarmData-'.$e->getMessage());
            SE(self::PDO_ERROR);
        }catch (BusinessLogicException $e){
            SE($e->getMessage());
        }
    }
    public function getAlarmStockBySpec($warehouse_id,$spec_id)
    {
        try{
            $alarm_fields = array(
                'ss.safe_stock',
                'ss.alarm_type',
                'ss.alarm_days',
                'ss.sales_rate_type',
                'ss.sales_rate_cycle',
                'ss.sales_fixrate'
            );
            $alarm_setting = $this->alias('ss')->field($alarm_fields)->where(array('ss.spec_id'=>$spec_id,'ss.warehouse_id'=>$warehouse_id))->find();
            return $alarm_setting;
        }catch (BusinessLogicException $e){
            $this->rollback();
            SE($e->getMessage());
        }catch (\PDOException $e){
            $this->rollback();
            \Think\Log::write($this->name.'-getAlarmStockBySpec-'.$e->getMessage());
            SE(self::PDO_ERROR);
        }
    }
    public function saveStockAlarm($alarm_setting_data)
    {
        try{
            $this->execute("CREATE TEMPORARY TABLE IF NOT EXISTS tmp_stock_spec_alarm_log(
                                  rec_id bigint(20) NOT NULL ,
                                  alarm_days INT(11) DEFAULT 0,
                                  operator_id INT(11) DEFAULT 0,
                                  operator_type INT(11) DEFAULT 0,
                                  sales_rate DECIMAL(19,4) DEFAULT '1.0',
                                  sales_fixrate DECIMAL(19,4) DEFAULT '1.0',
                                  safe_stock DECIMAL(19,4) DEFAULT '0',
                                  sales_rate_cycle DECIMAL(19,0) DEFAULT '0',
                                  sales_rate_type INT(11) DEFAULT 0,
                                  alarm_type INT(11) DEFAULT 0,
                                  `message` varchar(1024) NOT NULL DEFAULT '' COMMENT '操作日志',
                                  PRIMARY KEY (`rec_id`),
                                  INDEX `IDX_tmp_sales_rate_type` (`sales_rate_type`)
                        );");
            $this->execute("DELETE FROM tmp_stock_spec_alarm_log;");
            $this->startTrans();
            $operator_id = get_operator_id();
            $alarm_setting = array('alarm_type'=>0);
            if($alarm_setting_data['project_type'] == 2)
            {
                $alarm_setting['alarm_type'] = $alarm_setting['alarm_type']|2;
            }
            isset($alarm_setting_data['alarm_type'])?$alarm_setting['alarm_type'] = set_default_value($alarm_setting['alarm_type'] | $alarm_setting_data['alarm_type'],0):'';
            isset($alarm_setting_data['sales_rate_type'])?$alarm_setting['sales_rate_type'] =  set_default_value($alarm_setting_data['sales_rate_type'],0):'';
            isset($alarm_setting_data['sales_fixrate'])?$alarm_setting['sales_fixrate'] =  set_default_value($alarm_setting_data['sales_fixrate'],1):'';
            isset($alarm_setting_data['sales_rate_cycle'])?$alarm_setting['sales_rate_cycle'] =  set_default_value($alarm_setting_data['sales_rate_cycle'],7):'';
            isset($alarm_setting_data['alarm_days'])?$alarm_setting['alarm_days'] =  set_default_value($alarm_setting_data['alarm_days'],7):'';
            isset($alarm_setting_data['safe_stock'])?$alarm_setting['safe_stock'] =  set_default_value($alarm_setting_data['safe_stock'],0):'';

            if($alarm_setting_data['is_multi'] == 1)
            {
                $where = array();
				if($alarm_setting_data['ids'][0]['spec_id'] == 0){
					if($alarm_setting_data['ids'][0]['warehouse_id'] == 0){
						$search_warehouse = array();
						D('Setting/EmployeeRights')->setSearchRights($search_warehouse,'warehouse_id',2);
						$right_warehouse = $search_warehouse['warehouse_id'];
					}else{
						 $right_warehouse = $alarm_setting_data['mul_ware_ids'];
					}
					$spec_list = $this->field('spec_id')->fetchSql(false)->where(array('warehouse_id'=>array('in',$right_warehouse)))->group('spec_id')->select();
					$spec_list =array_column($spec_list,'spec_id');
				}else{
					$spec_list = array_column($alarm_setting_data['ids'],'spec_id');
				}
                $spec_str  = implode(',',$spec_list);
				
                $warehouse_id = $alarm_setting_data['ids'][0]['warehouse_id'];
                $where['spec_id'] = array('in',$spec_list);
                 if($alarm_setting_data['ids'][0]['warehouse_id'] != 0){
                     $warehouse_ids = '';
                     $warehouse_ids = $alarm_setting_data['mul_ware_ids'];
                     $where['warehouse_id'] = array('in', $warehouse_ids);
                     $this->execute("INSERT INTO tmp_stock_spec_alarm_log(rec_id,operator_id,operator_type,alarm_days,sales_rate_type,sales_fixrate,sales_rate_cycle,sales_rate,alarm_type,safe_stock)
                            SELECT rec_id,'{$operator_id}' operator_id,1 operator_type,alarm_days,sales_rate_type,sales_fixrate,sales_rate_cycle,sales_rate,alarm_type,safe_stock FROM stock_spec where spec_id in (%s) and warehouse_id in (%s)",$spec_str,$warehouse_ids);
                 
				}else{
					$search = array();
					D('Setting/EmployeeRights')->setSearchRights($search,'warehouse_id',2);
					$right_warehouse_id = $search['warehouse_id'];
					$where['warehouse_id'] = array('in',$right_warehouse_id);
                     $this->execute("INSERT INTO tmp_stock_spec_alarm_log(rec_id,operator_id,operator_type,alarm_days,sales_rate_type,sales_fixrate,sales_rate_cycle,sales_rate,alarm_type,safe_stock)
                            SELECT rec_id,'{$operator_id}' operator_id,1 operator_type,alarm_days,sales_rate_type,sales_fixrate,sales_rate_cycle,sales_rate,alarm_type,safe_stock FROM stock_spec where spec_id in (%s) ",$spec_str);
				}
              
            }else{
                if(!isset($alarm_setting_data['warehouse_id']))
                {
                    SE('未设置仓库');
                }
                $where = array();
                $spec_list = array_column($alarm_setting_data['ids'],'spec_id');
                $spec_str = implode(',',$spec_list);
                $warehouse_id = $alarm_setting_data['warehouse_id'];
                $warehouse_ids = $warehouse_id;
                $where['spec_id'] = array('in',$spec_list);
                $where['warehouse_id'] = $alarm_setting_data['warehouse_id'];

                $this->execute("INSERT INTO tmp_stock_spec_alarm_log(rec_id,operator_id,operator_type,alarm_days,sales_rate_type,sales_fixrate,sales_rate_cycle,sales_rate,alarm_type,safe_stock)
                            SELECT rec_id,'{$operator_id}' operator_id,1 operator_type,alarm_days,sales_rate_type,sales_fixrate,sales_rate_cycle,sales_rate,alarm_type,safe_stock FROM stock_spec where spec_id in (%s) and warehouse_id = '%d'",$spec_str,$alarm_setting_data['warehouse_id']);
            }
			$rec_id_array = $this->field('rec_id')->where($where)->order('rec_id')->select();
			$rec_id_array = implode(',',array_column($rec_id_array,'rec_id'));
			$rec_id_where = array('rec_id'=>array('in',$rec_id_array));
			$sql = $this->fetchSql(false)->where($rec_id_where)->save($alarm_setting);
            $this->execute("INSERT INTO tmp_stock_spec_alarm_log(rec_id,operator_id,operator_type,alarm_days,sales_rate_type,sales_fixrate,sales_rate_cycle,sales_rate,alarm_type,safe_stock)
                            SELECT rec_id,'{$operator_id}' operator_id,1 operator_type,alarm_days,sales_rate_type,sales_fixrate,sales_rate_cycle,sales_rate,alarm_type,safe_stock FROM stock_spec where spec_id in (%s) and IF(%d <>0,warehouse_id in (%d),1)
                            ON DUPLICATE KEY UPDATE 
                            tmp_stock_spec_alarm_log.message = CONCAT(
                                '修改警戒库存：',
                                IF (
                                    (VALUES(alarm_type)&2) <> (tmp_stock_spec_alarm_log.alarm_type&2),
                                    CONCAT('警戒库存方案---从【',
                                          IF(tmp_stock_spec_alarm_log.alarm_type&2, '方案二','方案一') ,
                                          '】到【',
                                          IF(VALUES(alarm_type)&2, '方案二','方案一'),
                                          '】,'
                                    ),
                                    CONCAT('使用警戒库存方案---【',
                                          IF(VALUES(alarm_type)&2, '方案二','方案一'),
                                          '】,'
                                    )
                                ),
                                 IF (
                                    VALUES(alarm_type)&2,   
                                    CONCAT('手动设置警戒库存为“',VALUES(safe_stock),'”') ,
                                    CONCAT(
                                       IF (
                                           IF (tmp_stock_spec_alarm_log.alarm_days, 0, 1)	AND IF (tmp_stock_spec_alarm_log.alarm_type, 0, 1)	AND	IF (tmp_stock_spec_alarm_log.sales_rate, 0, 1)	AND	IF (tmp_stock_spec_alarm_log.sales_fixrate, 0, 1)	AND	IF (tmp_stock_spec_alarm_log.sales_rate_cycle, 0, 1),
                                       
                                           CASE VALUES(sales_rate_type)
                                               WHEN 0 THEN
                                                   '使用全局配置的销售增长率计算方式---'
                                               WHEN 1 THEN
                                                   CONCAT('手动配置的固定月销售增长率,月固定增长率为:“',	VALUES(sales_fixrate),'”---')
                                               WHEN 2 THEN
                                                   CONCAT('手动配置的动态销售增长率计算周期，计算周期为:“',VALUES(sales_rate_cycle),'”---')
                                           END ,
                                       
                                           IF (
                                               VALUES(sales_rate_type) <> tmp_stock_spec_alarm_log.sales_rate_type,
                                               CONCAT('手动修改警戒库存增长率计算方式---从【',
                                                     CASE tmp_stock_spec_alarm_log.sales_rate_type WHEN 0 THEN '按全局配置的销售增长率计算方式' WHEN 1 THEN  '月固定销售增长率' WHEN 2 THEN '动态销售增长' END ,
                                                     '】到【',
                                                     CASE VALUES(sales_rate_type)  WHEN 0 THEN '按全局配置的销售增长率计算方式' WHEN 1 THEN  '月固定销售增长率' WHEN 2 THEN '动态销售增长' END,
                                                     '】,',
                                                     CASE VALUES(sales_rate_type)   WHEN 1 THEN  CONCAT('月固定销售增长率从“',tmp_stock_spec_alarm_log.sales_fixrate,'”改为“',VALUES(sales_fixrate),'”，') WHEN 2 THEN CONCAT('动态销售增长周期从“',tmp_stock_spec_alarm_log.sales_rate_cycle,'”改为“',VALUES(sales_rate_cycle),'”，') ELSE '' END
                                               ),
                                               CASE VALUES(sales_rate_type)
                                                   WHEN 1 THEN
                                                       CONCAT('使用【月固定销售增长率】计算方式,月固定增长率改为“',VALUES(sales_fixrate),'”，')
                                                   WHEN 2 THEN
                                                       CONCAT('使用【动态销售增长】计算方式,动态计算周期改为“',VALUES(sales_rate_cycle),'”，')
                                                  WHEN 0 THEN
                                                      '使用全局配置的销售增长率计算方式，'
                                               END 
                                           )
                                           
                                       ),
                                       IF(
                                          (VALUES(alarm_type)&1),
                                           CONCAT('使用全局配置的警戒库存天数'),
                                          IF(tmp_stock_spec_alarm_log.alarm_days<>VALUES(alarm_days),CONCAT('---设置警戒库存天数从“',tmp_stock_spec_alarm_log.alarm_days,'”到“',VALUES(alarm_days),'”'),'')
                                       )
                                    )
                                 )
                            )
                          ",$spec_str,$warehouse_id,$warehouse_ids);
            $this->execute("insert into stock_spec_log(stock_spec_id,operator_id,operator_type,message,`data`)
                            SELECT rec_id stock_spec_id,operator_id,operator_type,message ,1
                            FROM tmp_stock_spec_alarm_log");
            $this->execute('DELETE FROM tmp_stock_spec_alarm_log');
            $this->commit();

        }catch (BusinessLogicException $e){
            $this->rollback();
            SE($e->getMessage());
        }catch (\PDOException $e){
            $this->rollback();
            \Think\Log::write($this->name.'-saveStockAlarm-'.$e->getMessage());
            SE(self::PDO_ERROR);
        }catch (\Exception $e){
            $this->rollback();
            \Think\Log::write($this->name.'-saveStockAlarm-'.$e->getMessage());
            SE(self::PDO_ERROR);
        }
    }
    
    //初始化货品库存信息
    public function initStockSpec($spec_id,$warehouse_id){
    	$refresh = get_config_value(array('addgoods_refresh_stock'),array(0));
		if($warehouse_id==''){
			if($refresh['addgoods_refresh_stock'] == 0){
				$warehouse_id=$this->query("SELECT warehouse_id FROM cfg_warehouse WHERE is_disabled=0");
			}else{
				$warehouse_id=$this->query("SELECT warehouse_id FROM cfg_warehouse WHERE is_disabled=0 and type = 11");
			}
    	}
		if(empty($warehouse_id)){
			return false;
		}
    	$stock_spec=array();
    	is_array($spec_id)?$spec=$spec_id:$spec[0]['spec_id']=$spec_id;
    	is_array($warehouse_id)?$warehouse=$warehouse_id:$warehouse[0]['warehouse_id']=$warehouse_id;
        $warehouse_zone_id = M('cfg_warehouse_zone')->field('zone_id,warehouse_id')->select();
        for($i=0; $i<count($warehouse_zone_id); $i++){
            $warehouse_zone_id_map[$warehouse_zone_id[$i]['warehouse_id']] = $warehouse_zone_id[$i]['zone_id'];
        }		
		foreach ($spec as $s){
			foreach ($warehouse as $w){
				$stock_spec[]=array(
					'spec_id'=>$s['spec_id'],
					'warehouse_id'=>$w['warehouse_id'],
					//'last_position_id'=>'-'.$w['warehouse_id'],
				);
				 $position_info = D('Stock/StockSpecPosition')->field('position_id')->where(array('warehouse_id'=>$w['warehouse_id'],'spec_id'=>$s['spec_id']))->find();
				if(!empty($position_info) && !empty($position_info['position_id'])){
					$stock_position_id = $position_info['position_id'];
				}else{
					$stock_position_id = '-'.$w['warehouse_id'];
				}
				$stock_spec_position[]=array(
					'spec_id'=>$s['spec_id'],
					'warehouse_id'=>$w['warehouse_id'],
					'position_id'=>$stock_position_id,
					'zone_id'=>$warehouse_zone_id_map[$w['warehouse_id']],
				);
			}

		}
			
    	if(!empty($stock_spec)){
    		M('stock_spec')->addAll($stock_spec);
    	}
        if(!empty($stock_spec_position)){
            M('stock_spec_position')->addAll($stock_spec_position);
        }
    }
    // 判断仓库能否停用
    public function enableToDisable($warehouse_id){

        $result = array();
        $result['status'] = 0;
        $result['msg'] = '';
        $orderMsg = '';
        $goodsMsg = '';
        $outSideOrderMsg = '';
        $msg = '仓库停用失败！原因: 该仓库存在';
        $sql = "select sum(order_num) as order_num,sum(sending_num) AS sending_num,sum(purchase_num) AS purchase_num,sum(transfer_num) AS transfer_num
                from stock_spec WHERE warehouse_id = '".$warehouse_id."'";
        $goods_info = $this->query($sql);
        if($goods_info[0]['order_num']>0){
            $goodsMsg.='待审核、';
        }
        if($goods_info[0]['sending_num']>0){
            $goodsMsg.='待发货、';
        }
        if($goods_info[0]['purchase_num']>0){
            $goodsMsg.='采购在途、';
        }
        if($goods_info[0]['transfer_num']>0){
            $goodsMsg.='调拨在途、';
        }
        if($goodsMsg !=''){
            $goodsMsg = rtrim($goodsMsg,'、').'的货品';
            $msg = $msg.$goodsMsg;
        }
        $editStockInOrders =  D('Stock/StockInOrder')->field('status')->where(array('warehouse_id'=>$warehouse_id,'status'=>20))->select();
        if(count($editStockInOrders)>0){
            $orderMsg.= '入库单、';
        }
        $editStockOutOrders = D('Stock/StockOutOrder')->field('status')->where(array('warehouse_id'=>$warehouse_id,'status'=>48))->select();
        if(count($editStockOutOrders)>0){
            $orderMsg.= '出库单、';
        }
        $editTransferOrders = D('Stock/StockTransfer')->field('status')->where(array('to_warehouse_id'=>$warehouse_id,'status'=>20))->select();
        if(count($editTransferOrders)>0){
            $orderMsg.= '调拨单、';
        }
        $editStockPdOrders = D('Stock/StockInventoryManagement')->field('status')->where(array('warehouse_id'=>$warehouse_id,'status'=>1))->select();
        if(count($editStockPdOrders)>0){
            $orderMsg.= '盘点单、';
        }
        $editOutsideWmsOrders = D('Stock/OutsideWmsOrder')->field('status')->where(array('warehouse_id'=>$warehouse_id,'status'=>40))->select();
        if(count($editOutsideWmsOrders)>0){
            $outSideOrderMsg = ' 待推送的委外单';
        }
        if($orderMsg !=''){
            $orderMsg = rtrim($orderMsg,'、');
            $orderMsg = ' 编辑中的'.$orderMsg;
            $msg = $msg.$orderMsg;
        }
        if($outSideOrderMsg !=''){
            $msg = $msg.$outSideOrderMsg;
        }
        if($goodsMsg !='' || $orderMsg !='' || $outSideOrderMsg!=''){
            $result['status'] = 1;
            $result['msg'] = $msg;
        }else{
            $result['status'] = 0;
            $result['msg'] = '';
        }
        return $result;
    }
}
