<?php
namespace Goods\Model;
use Think\Exception;
use Think\Exception\BusinessLogicException;
use Think\Model;
use Common\Common\ExcelTool;
class GoodsMerchantNoModel extends Model{
    protected $tableName = "goods_merchant_no";
    protected $pk        = "rec_id";

    public function getGoodsMerchantNoList($page = 1, $rows = 10, $search = array(), $sort = 'rec_id', $order = 'desc'){
        try{
            $page = intval($page);
            $rows = intval($rows);
            //搜索表单-数据处理
            $where_goods_spec_no = '';
            $where_goods_suite_no = '';
            $where_goods_goods = '';
            $this->searchFormDeal($where_goods_spec_no,$where_goods_suite_no,$where_goods_goods,$search);
            $limit = ($page - 1) * $rows . "," . $rows;//分页
            $order = $sort . ' ' . $order;//排序
            $order = addslashes($order);
            $sql_total ='(SELECT COUNT(gm.rec_id) num FROM goods_merchant_no gm,goods_spec gs,goods_goods gg WHERE gm.type=1 AND gm.target_id=gs.spec_id AND gs.goods_id=gg.goods_id ' . $where_goods_goods . $where_goods_spec_no . ') ';
            $sql = '(SELECT gm.rec_id,gm.merchant_no,0 is_suite,gm.target_id,gg.goods_no,gs.spec_no merchant_no,gg.goods_name,gg.short_name,gs.spec_name,gs.spec_code FROM goods_merchant_no gm,goods_spec gs,goods_goods gg WHERE gm.type=1 AND gm.target_id=gs.spec_id AND gs.goods_id=gg.goods_id ' . $where_goods_goods . $where_goods_spec_no . ') ';
            if (empty($where_goods_goods))
            {
                $sql_total.='UNION (SELECT COUNT(gm.rec_id) num FROM goods_merchant_no gm,goods_suite s WHERE gm.type=2 AND gm.target_id=s.suite_id ' . $where_goods_suite_no . ')';
                $sql.='UNION (SELECT gm.rec_id,gm.merchant_no,1 is_suite,gm.target_id,\'\',s.suite_no,s.suite_name,s.short_name,\'\',\'\' FROM goods_merchant_no gm,goods_suite s WHERE gm.type=2 AND gm.target_id=s.suite_id ' . $where_goods_suite_no . ') ORDER BY ' . $order . ' LIMIT ' . $limit;
                $cache_sql = substr($sql,0,stripos($sql,'limit'));

            }else{
                $sql_total.='UNION (SELECT COUNT(gm.rec_id) num FROM goods_merchant_no gm,goods_suite s,goods_suite_detail gsd,goods_spec gs,goods_goods gg WHERE gm.type=2 AND gm.target_id=s.suite_id AND s.suite_id=gsd.suite_id AND gsd.spec_id=gs.spec_id AND gs.goods_id=gg.goods_id ' . $where_goods_goods . $where_goods_suite_no . ')';
                $sql.='UNION (SELECT gm.rec_id,gm.merchant_no,1 is_suite,gm.target_id,\'\',s.suite_no,s.suite_name,s.short_name,\'\',\'\' FROM goods_merchant_no gm,goods_suite s,goods_suite_detail gsd,goods_spec gs,goods_goods gg WHERE gm.type=2 AND gm.target_id=s.suite_id AND s.suite_id=gsd.suite_id AND gsd.spec_id=gs.spec_id AND gs.goods_id=gg.goods_id ' . $where_goods_goods . $where_goods_suite_no . ') ORDER BY ' . $order . ' LIMIT ' . $limit;
                $cache_sql = substr($sql,0,stripos($sql,'limit'));
            }
            $res_total_arr = $this->query($sql_total);
            $total = $res_total_arr[0]['num'] + $res_total_arr[1]['num'];
            $list = $total ? $this->query($sql) : array();
            $data = array('total' => $total, 'rows' => $list);
            $file = APP_PATH."/Runtime/File/goods_merchant_no";
            if(file_exists($file))unlink($file);
            file_put_contents($file,print_r($cache_sql,true));
        }catch (\Exception $e){
            \Think\Log::write($e->getMessage());
            $data["total"] = 0;
            $data["rows"]  = "";
        }
        return $data;
    }
    public function searchFormDeal(&$where_goods_spec_no,&$where_goods_suite_no,&$where_goods_goods,$search){
        foreach ($search as $k => $v) {
            if ($v === "") continue;
            switch ($k) {
                case 'merchant_no':
                    set_search_form_value($where_goods_spec_no, 'spec_no', $v, 'gs', 10,  'AND');
                    set_search_form_value($where_goods_suite_no, 'suite_no', $v, 's', 10, 'AND');
                    break;
                case 'goods_no':
                    set_search_form_value($where_goods_goods, $k, $v, 'gg', 1, 'AND');
                    break;
                default:
                    continue;
            }
        }
    }
    public function exportToExcel($id_list, $type){
        $creator=session('account');
        try{
            $file = APP_PATH."/Runtime/File/goods_merchant_no";
            if(empty($id_list)){
                $sql = file_get_contents($file);
                $data = $this->query($sql);
                $count = count($data);
            }else{
                $sql = "(SELECT gm.rec_id,gm.merchant_no,0 is_suite,gm.target_id,gg.goods_no,gs.spec_no merchant_no,gg.goods_name,gg.short_name,gs.spec_name,gs.spec_code FROM goods_merchant_no gm,goods_spec gs,goods_goods gg WHERE gm.rec_id in ({$id_list}) AND gm.type=1 AND gm.target_id=gs.spec_id AND gs.goods_id=gg.goods_id ) UNION (SELECT gm.rec_id,gm.merchant_no,1 is_suite,gm.target_id,'',s.suite_no,s.suite_name,s.short_name,'','' FROM goods_merchant_no gm,goods_suite s WHERE gm.rec_id in ({$id_list}) AND gm.type=2 AND gm.target_id=s.suite_id ) ORDER BY rec_id DESC";
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
            $title = '商家编码';
            $filename = '商家编码';
            $excel_header = D('Setting/UserData')->getExcelField('Goods/GoodsMerchantNo','goods_merchant_no');
            foreach ($excel_header as $v)
            {
                $width_list[]=20;
            }
            foreach($data as $k=>$v){
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