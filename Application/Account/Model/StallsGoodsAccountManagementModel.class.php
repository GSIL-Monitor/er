<?php
namespace Account\Model;

use Think\Log;
use Common\Common\ExcelTool;
use Common\Common\UtilTool;
use Think\Exception;
use Think\Exception\BusinessLogicException;
use Think\Model;
class StallsGoodsAccountManagementModel extends Model
{
    protected $tableName = 'stat_stalls_goods_amount';
    protected $pk = 'rec_id';

    public function getStallsGoodsAccountList($page=1, $rows=20, $search = array(), $sort = 'ssga.rec_id', $order = 'desc',&$data)
    {
        try
        {
            $where = "true ";
            $page = intval($page);
            $rows = intval($rows);
            $order = $sort . ' ' .$order;
            $this->searchFormDeal($where,$search);
            $limit = ($page - 1) * $rows . "," . $rows;
            //档口采购单详情中已完成和 编辑中已拿货的
            $sql = "SELECT ssga.rec_id id,gg.goods_no,gg.goods_name,gs.spec_no,gc_1.class_name class_id,gb.brand_name brand_id,pp.provider_name,
                    ssga.num,ssga.price AS price,ssga.sales_date created,ssga.status,ssga.import_price,IFNULL(ssga.import_price-ssga.price,0) AS diff_price,
                    he.fullname AS charge_oper_id,ssga.charge_time
                    FROM stat_stalls_goods_amount ssga
                    LEFT JOIN purchase_provider pp ON ssga.provider_id = pp.id
                    LEFT JOIN goods_spec gs ON ssga.spec_id = gs.spec_id
                    LEFT JOIN goods_goods gg ON ssga.goods_id = gg.goods_id
                    LEFT JOIN goods_brand gb ON gg.brand_id = gb.brand_id
                    LEFT JOIN goods_class gc_1 ON gg.class_id = gc_1.class_id
                    LEFT JOIN hr_employee he ON he.employee_id = ssga.charge_oper_id
                    LEFT JOIN cfg_warehouse cw ON cw.warehouse_id=ssga.warehouse_id
                    WHERE $where AND cw.type!=127 ORDER BY $order LIMIT $limit";
            $sql_total = "SELECT COUNT(*) AS total FROM stat_stalls_goods_amount ssga
                          LEFT JOIN purchase_provider pp ON ssga.provider_id = pp.id
                          LEFT JOIN goods_spec gs ON ssga.spec_id = gs.spec_id
                          LEFT JOIN goods_goods gg ON ssga.goods_id = gg.goods_id
                          LEFT JOIN goods_brand gb ON gg.brand_id = gb.brand_id
                          LEFT JOIN goods_class gc_1 ON gg.class_id = gc_1.class_id
                          LEFT JOIN hr_employee he ON he.employee_id = ssga.charge_oper_id
                          LEFT JOIN cfg_warehouse cw ON cw.warehouse_id=ssga.warehouse_id
                          WHERE $where AND cw.type!=127";
            $data["total"] = $this->query($sql_total)[0]["total"];
            $data["rows"] = $this->query($sql);

        }catch(\PDOException $e)
        {
            Log::write($e->getMessage());
            $data["total"] = 0;
            $data["rows"] = "";
            SE(parent::PDO_ERROR);
        }catch (\Exception $e)
        {
            $data["total"] = 0;
            $data["rows"] = "";
            Log::write($e->getMessage());
            SE($e->getMessage());
        }
        return $data;
    }

    public function searchFormDeal(&$where, $search){
        //设置店铺权限
        D('Setting/EmployeeRights')->setSearchRights($search, 'shop_id', 1);
        foreach($search as $k => $v){
            if($v === "") continue;
            switch($k){
                case "goods_name":
                    //set_search_form_value($where, $k, $v, 'ag', 6, "AND");
                    //  $res = UtilTool::check_search_form_value($v, 1);

                    $res = trim($v);
                    if(empty($v) && ($v !== 0) && ($v !== '0')){
                        return false;
                    }
                    $res=strtr($res,array('%'=>'\%', '_'=>'\_', '\\'=>'\\\\'));
                    $res = addslashes($res);
                    $where = $res === false ? $where : $where . " AND gg." . $k . " LIKE '%" . $res . "%'";
                    break;
                case 'goods_no'://goods_goods
                    set_search_form_value($where, $k, $v, 'gg', 1, ' AND ');
                    break;
                case 'spec_no':
                    set_search_form_value($where, $k, $v, 'gs', 10, ' AND ');
                    break;
                case 'provider': //供货商
                    set_search_form_value($where,'id',$v,'pp',2,' and ');
                    break;
                case 'brand_id':
                    set_search_form_value($where, $k, $v, 'gg', 2, ' AND ');
                    break;
                case 'class_id'://分类         goods_gooods
                    set_search_form_value($where, $k, $v, 'gg', 7, ' AND ');
                    break;
                case 'start_time':
                    set_search_form_value($where, 'sales_date', $v,'ssga', 3,' AND ',' >= ');
                    break;
                case 'end_time':
                    set_search_form_value($where, 'sales_date', $v,'ssga', 3,' AND ',' < ');
                    break;
                case 'status':
                    set_search_form_value($where, $k, $v,'ssga', 2,' AND ');
                    break;
                case 'rec_id':
                    $where=$where.' AND '.$k.' in('.addslashes($v).') ';
                    break;
                default:
                    continue;
            }
        }
    }


    //导出
    public function exportToExcel($search, $type='excel')
    {
        $creator = session('account');
        $excel_no = array();
        try {
            $where = "true ";
            $this->searchFormDeal($where, $search);
            $sql = "SELECT ssga.rec_id id,gg.goods_no,gg.goods_name,gs.spec_no,gc_1.class_name,gb.brand_name,pp.provider_name,
                        ssga.num,ssga.price price,ssga.sales_date created,ssga.status,ssga.import_price,IFNULL(ssga.import_price-ssga.price,0) AS diff_price,
                        he.fullname AS charge_oper_id,ssga.charge_time
                        FROM stat_stalls_goods_amount ssga
                        LEFT JOIN purchase_provider pp ON ssga.provider_id = pp.id
                        LEFT JOIN goods_spec gs ON ssga.spec_id = gs.spec_id
                        LEFT JOIN goods_goods gg ON ssga.goods_id = gg.goods_id
                        LEFT JOIN goods_brand gb ON gg.brand_id = gb.brand_id
                        LEFT JOIN goods_class gc_1 ON gg.class_id = gc_1.class_id
                        LEFT JOIN hr_employee he ON he.employee_id = ssga.charge_oper_id
                        WHERE $where ORDER BY ssga.rec_id desc";
            $stalls_goods = $this->query($sql);
            if(count($stalls_goods) == 0){
                SE('导出数据不能为空，请重新搜索后导出');
            }

            $num = workTimeExportNum($type);
            if (count($stalls_goods) > $num) {
                if ($type == 'csv') {
                    SE(self::EXPORT_CSV_ERROR);
                }
                SE(self::OVER_EXPORT_ERROR);
            }

            foreach ($stalls_goods as $k => $v) {
                switch($v['status']){
                    case '0':
                        $row['status'] = '未导入';
                        break;
                    case '1':
                        $row['status'] = '待结算';
                        break;
                    case '2':
                        $row['status'] = '已结算';
                        break;
                }
                $row['goods_no'] = $v['goods_no'];
                $row['goods_name'] = $v['goods_name'];
                $row['spec_no'] = $v['spec_no'];
                $row['class_name'] = $v['class_name'];
                $row['brand_name'] = $v['brand_name'];
                $row['provider_name'] = $v['provider_name'];
                $row['num'] = $v['num'];
                $row['created'] = $v['created'];
                $row['price'] = $v['price'];
                $row['import_price'] = $v['import_price'];
                $row['diff_price'] = $v['diff_price'];
                $row['charge_oper_id'] = $v['charge_oper_id'];
                $row['charge_time'] = $v['charge_time'];
                $data[] = $row;
            }
            foreach ($data as $k => $v) {
                $keys_arr = array_keys($v);
            }

            foreach ($keys_arr as $k => $v) {
                switch ($v) {
                    case 'status':
                        $excel_no['status'] = '结算状态';
                        break;
                    case 'goods_no':
                        $excel_no['goods_no'] = '货品编码';
                        break;
                    case 'goods_name':
                        $excel_no['goods_name'] = '货品名称';
                        break;
                    case 'spec_no':
                        $excel_no['spec_no'] = '商家编码';
                        break;
                    case 'class_name':
                        $excel_no['class_name'] = '分类';
                        break;
                    case 'brand_name':
                        $excel_no['brand_name'] = '品牌';
                        break;
                    case 'provider_name':
                        $excel_no['provider_name'] = '供货商名称';
                        break;
                    case 'num':
                        $excel_no['num'] = '数量';
                        break;
                    case 'created':
                        $excel_no['created'] = '入库时间';
                        break;
                    case 'price':
                        $excel_no['price'] = '采购总金额';
                        break;
                    case 'import_price':
                        $excel_no['import_price'] = '导入金额';
                        break;
                    case 'diff_price':
                        $excel_no['diff_price'] = '采购差额';
                        break;
                    case 'charge_oper_id':
                        $excel_no['charge_oper_id'] = '结算人';
                        break;
                    case 'charge_time':
                        $excel_no['charge_time'] = '结算时间';
                        break;

                }
            }
            $title = '档口货品对账';
            $filename = '档口货品对账';
            $width_list = array('10', '17', '17', '15', '10', '10', '17', '10', '10', '10', '10', '10', '10', '17');
            if ($type == 'csv') {
                ExcelTool::Arr2Csv($data, $excel_no, $filename);
            } else {
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
    //货品详情tab页
    public function getGoodsDetail($id)
    {
        try
        {
            $id = intval($id);
            $stat_res = $this->where(array('rec_id'=>$id))->find();
            $spec_id = $stat_res['spec_id'];
            $start_stockin_time = $stat_res['sales_date'].' 00:00:00';
            $end_stockin_time = $stat_res['sales_date'].' 23:59:59';
            $where = "slgd.spec_id = $spec_id AND slgd.stockin_time>= '$start_stockin_time' AND slgd.stockin_time<='$end_stockin_time' AND slgd.stockin_status=1 AND cw.type<>127";
            $res = M('stalls_less_goods_detail')->alias('slgd')->field('slgd.trade_id,slgd.stalls_id')->join('LEFT JOIN cfg_warehouse cw ON cw.warehouse_id=slgd.warehouse_id')->where($where)->group('trade_id')->select();
            foreach($res as $v)
            {
                if($v['stalls_id'] ==0)
                {
                    $sql = "SELECT st.trade_no,'无'  purchaser_id,sgd.num,sgd.price detail_price,sgd.num*sgd.price price,sgd.stockin_time,'驳回单号为空' stalls_no,sgd.unique_code
                FROM stalls_less_goods_detail sgd
                LEFT JOIN stalls_order so ON so.stalls_id = sgd.stalls_id
                LEFT JOIN sales_trade st ON st.trade_id = sgd.trade_id
                LEFT JOIN cfg_warehouse cw ON cw.warehouse_id=sgd.warehouse_id
                WHERE sgd.trade_id={$v['trade_id']} and sgd.spec_id={$spec_id} and sgd.stockin_time>= '$start_stockin_time' and sgd.stockin_time<='$end_stockin_time' and sgd.stockin_status=1 and cw.type!=127";
                }else
                {
                    $sql = "SELECT st.trade_no,he.fullname  purchaser_id,sgd.num,sgd.price detail_price,sgd.num*sgd.price price,sgd.stockin_time,so.stalls_no,sgd.unique_code
                FROM stalls_less_goods_detail sgd
                LEFT JOIN stalls_order so ON so.stalls_id = sgd.stalls_id
                LEFT JOIN sales_trade st ON st.trade_id = sgd.trade_id
                LEFT JOIN hr_employee he ON so.purchaser_id=he.employee_id
                LEFT JOIN cfg_warehouse cw ON cw.warehouse_id=sgd.warehouse_id
                WHERE sgd.trade_id={$v['trade_id']} AND so.stalls_id={$v['stalls_id']} and sgd.spec_id={$spec_id} and sgd.stockin_time>= '$start_stockin_time' AND sgd.stockin_time<='$end_stockin_time' AND sgd.stockin_status=1 and cw.type!=127";
                }
                $detail = M()->query($sql);
                foreach($detail as $v){
                    $detail_res[] = $v;
                }

            }
            $data = array('total' => count($detail_res), 'rows' => $detail_res);

        }catch(\PDOException $e)
        {
            Log::write($e->getMessage());
            $data = array('total' => 0, 'rows' => array());
            SE(parent::PDO_ERROR);
        }catch (\Exception $e)
        {
            $data = array('total' => 0, 'rows' => array());
            Log::write($e->getMessage());
            SE($e->getMessage());
        }
        return $data;
    }
    public function importGoodsAccount($data,&$error_list,$line)
    {
        try{
            foreach($data as $k=>$v){
                if($v['spec_no'] == ''){
                    $error_list[] = array('id'=>$k+2+$line,'result'=>'导入失败','message'=>'商家编码不能为空');
                    continue;
                }
                if($v['price'] == ''){
                    $error_list[] = array('id'=>$k+2+$line,'result'=>'导入失败','message'=>'导入金额不能为空');
                    continue;
                }
                if($v['sales_date'] == ''){
                    $error_list[] = array('id'=>$k+2+$line,'result'=>'导入失败','message'=>'入库日期不能为空');
                    continue;
                }
                $now_date = date('Y-m-d',strtotime('-1 MONTH'));
                if(strtotime($now_date) > strtotime($v['sales_date'])){
                    $error_list[] = array('id'=>$k+2+$line,'result'=>'导入失败','message'=>'不能导入入库日期为30天之前的数据');
                    continue;
                }
                $search = array(
                    'spec_no'       => $v['spec_no'],
                    'provider_name' => $v['provider_name']==''?'无':$v['provider_name'],
                    'sales_date'    => $v['sales_date']
                );
                $res = $this->alias('ssga')->field('ssga.rec_id, ssga.status')->join('LEFT JOIN purchase_provider pp ON ssga.provider_id = pp.id')
                    ->join('LEFT JOIN goods_spec gs ON ssga.spec_id = gs.spec_id')->where($search)->select();
                $count = count($res);
                if($count == 0){
                    $error_list[] = array('id'=>$k+2+$line,'result'=>'导入失败','message'=>'系统内无该档口货品数据');
                    continue;
                }
                if($count > 1){
                    $error_list[] = array('id'=>$k+2+$line,'result'=>'导入失败','message'=>'系统内存在多条该档口货品数据');
                    continue;
                }
                if($res[0]['status'] == 2){
                    $error_list[] = array('id'=>$k+2+$line,'result'=>'导入失败','message'=>'该数据已结算，不能再次导入结算');
                    continue;
                }
                $data = array(
                    'import_price' => $v['price'],
                    'status'       => 1
                );
                $this->where(array('rec_id'=>$res[0]['rec_id']))->fetchsql(false)->save($data);

            }
        }catch (\PDOException $e) {
            Log::write('importStallsGoodsAccount PDO ERR:'.print_r($e->getMessage(),true));
            //不抛出异常，剩余任务继续执行
            //SE($e->getMessage());
        } catch (\Exception $e) {
            Log::write('importStallsGoodsAccount ERR:'.print_r($e->getMessage(),true));
            SE($e->getMessage());
        }
    }
    public function charge($id_list, $search, &$error_list)
    {
        $where = ' WHERE true ';
        $left_join_sql = ' LEFT JOIN purchase_provider pp ON ssga.provider_id = pp.id
                    LEFT JOIN goods_spec gs ON ssga.spec_id = gs.spec_id
                    LEFT JOIN goods_goods gg ON ssga.goods_id = gg.goods_id
                    LEFT JOIN goods_brand gb ON gg.brand_id = gb.brand_id
                    LEFT JOIN goods_class gc_1 ON gg.class_id = gc_1.class_id
                    LEFT JOIN hr_employee he ON he.employee_id = ssga.charge_oper_id ';
        $userId = get_operator_id();
        try
        {
            if(!empty($id_list)){
                $id = implode(',',$id_list);
                $where .= 'and ssga.rec_id in ('.$id.')';
            }else{
                $this->searchFormDeal($where, $search);
            }
            $res = $this->query('SELECT ssga.rec_id,ssga.status,gs.spec_no,pp.provider_name,ssga.sales_date FROM stat_stalls_goods_amount ssga'.$left_join_sql.$where);
            foreach($res as $v){
                if($v['status'] == 0){
                    $error_list[] = array('spec_no'=>$v['spec_no'],'provider_name'=>$v['provider_name'],'sales_date'=>$v['sales_date'],'message'=>'请先导入后再结算');
                    continue;
                }
                if($v['status'] == 2){
                    $error_list[] = array('spec_no'=>$v['spec_no'],'provider_name'=>$v['provider_name'],'sales_date'=>$v['sales_date'],'message'=>'该数据已结算，不能重复结算');
                    continue;
                }
                $data = array(
                    'status'        => 2,
                    'charge_oper_id'=> $userId,
                    'charge_time'   => date('Y-m-d H:i:s',time())
                );
                $this->where(array('rec_id'=>$v['rec_id']))->fetchsql(false)->save($data);
            }
        }catch(\PDOException $e)
        {
            Log::write($e->getMessage());
            SE(parent::PDO_ERROR);
        }catch (\Exception $e)
        {
            Log::write($e->getMessage());
            SE($e->getMessage());
        }
    }

}