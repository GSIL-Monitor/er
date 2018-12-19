<?php
namespace Stock\Model;
use Think\Log;
use Think\Model;
use Think\Exception\BusinessLogicException;
use Common\Common\UtilDB;
use Common\Common\ExcelTool;

class StockOutOrderModel extends Model
{
	protected $tableName = 'stockout_order';
	protected $pk = 'stockout_id';

    protected $_validate = array(
		//array(验证字段1,验证规则,错误提示,[验证条件,附加规则,验证时间]),
		/*self::EXISTS_VALIDATE 或者0 存在字段就验证（默认）
         self::MUST_VALIDATE 或者1 必须验证
         self::VALUE_VALIDATE或者2 值不为空的时候验证*/
		//提示信息做了特殊处理,使用数字来表明是属于什么验证失败了 1-不符合单号规则 2-物流单号重复
//			array('logistics_no,logistics_type','checkRule','单号格式不正确',1,'callback'),
			array('logistics_no,stockout_id','checkRepeat','物流单号重复',1,'callback'),
	);
	protected function checkRule($logistics_info){
		try{
			$logistics_regex = M('dict_logistics')->field(array('logistics_regex','logistics_type'))->where(array('logistics_type'=>$logistics_info['logistics_type']))->find();
			$source_logistics_no = trim($logistics_info['logistics_no']);
			if(!empty($logistics_regex['logistics_regex'])){
				$is_match = preg_match('/'.$logistics_regex['logistics_regex'].'/',$source_logistics_no,$match_item);
				if(is_numeric($is_match) ){
					if($is_match === 0){
						SE('单号格式不正确!');
					}
				}else{
					SE('单号规则匹配异常');
				}
			}

		}catch (\PDOException $e){
			$msg = $e->getMessage();
			\Think\Log::write($msg);
			return false;
		}catch (BusinessLogicException $e){
			return false;
		}catch (\Exception $e){
			$msg = $e->getMessage();
			\Think\Log::write($msg);
			return false;
		}
		return true;
	}
	protected function checkRepeat($logistics_info){
		try{
			$num = $this->where(array('logistics_no'=>trim($logistics_info['logistics_no']),'status'=>array('EGT',55),'stockout_id'=>array('neq',$logistics_info['stockout_id'])))->count();
			if($num>0){
				SE('存在重复的物流单号!');
			}

		}catch (\PDOException $e){
			$msg = $e->getMessage();
			\Think\Log::write($msg);
			return false;
		}catch (BusinessLogicException $e){
			return false;
		}catch (\Exception $e){
			$msg = $e->getMessage();
			\Think\Log::write($msg);
			return false;
		}
		return true;
	}
	public function searchStockoutList($page=1, $rows=20, $search = array(), $sort = 'id', $order = 'desc',$type){
		$where_stockout_order = array();
	    $where_sales_trade_order = array();
	    $where_sales_trade = array();
	    $where_stockout_order_detail = array();
		$is_empty_search = empty($search);
		$print_batch_order = '';
		//设置店铺权限
		D('Setting/EmployeeRights')->setSearchRights($search,'shop_id',1);
		D('Setting/EmployeeRights')->setSearchRights($search,'warehouse_id',2);
		foreach ($search as $k => $v) {
	        if ($v === '') continue;
	        switch ($k) {
				case 'stockout_no': {//出库单号  stockout_order
					set_search_form_value($where_stockout_order, $k, $v, 'so');
					break;
				}
				case 'print_batch':{//打印批次
					if($v!='all'){
						set_search_form_value($where_stockout_order,'stockout_id',$v,'so',2);
						$print_batch_order = $v;
					}
					break;
				}
	            case 'src_order_no':{//原始单号 stockout_order
	                set_search_form_value($where_stockout_order, $k, $v,'so');
	                break;
	            }
	            case 'status':{//出库单状态 stockout_order
	                set_search_form_value($where_stockout_order, $k, $v, 'so', 2);
	                break;
	            }
				case 'warehouse_id'://仓库类型
					set_search_form_value($where_stockout_order, $k, $v, 'so', 2);
					break;
	            case 'src_order_type':{//出库单类别 stockout_order
	                set_search_form_value($where_stockout_order, $k, $v, 'so', 2);
	                break;
	            }
	            case 'block_reason':{//拦截原因 stockout_order
	                if(!check_regex('number',$v))
	                {
	                    break;
	                }
	                $where_stockout_order['so.block_reason'] = array('exp','&'.$v);
	                break;
// 	                set_search_form_value($where_stockout_order, $k, $v, 'so', 2);
// 	                break;
	            }
	            case 'trade_from':{//订单来源 sales_trade
	                set_search_form_value($where_sales_trade, $k, $v, 'st', 2);
	                break;
	            }
	            case 'logistics_id':{//物流公司 stockout_order
	                set_search_form_value($where_stockout_order, $k, $v, 'so', 2);
	                break;
	            }
	            case 'operator_id':{//经办人  stockout_order
	                set_search_form_value($where_stockout_order, $k, $v, 'so', 2);
	                break;
	            }
	            case 'logistics_no':{//物流单号 stockout_order
	            	set_search_form_value($where_stockout_order, $k, $v, 'so');
	                break;
	            }
				case 'passel_logistics_nos':{//批量物流单号 stockout_order
					$where_stockout_order['_string'].=" AND so.logistics_no IN (".$v.") ";
					//set_search_form_value($where_stockout_order, 'logistics_no', $v, 'so' ,2);
	                break;
	            }
	            case 'multi_logistics_no':{//多物流单号 stockout_order
	            	set_search_form_value($where_stockout_order, 'logistics_no', $v, 'sml');
	                break;
	            }
	            case 'receiver_mobile':{//收件人手机 stockout_order
	                set_search_form_value($where_stockout_order, $k, $v, 'so');
	                break;
	            }
				case 'receiver_province':{//省份 receiver_province
					$v = ','.$v.',';
					if(strpos($v,',0,')!==false){$v = str_replace(',0,','',$v);}
					$v=trim($v,',');
					if($v==0||$v==''){
						 break;
					}
	                set_search_form_value($where_sales_trade, $k, $v, 'so',2);
	                break;
	            }
	            case 'receiver_city':{//城市 receiver_city
					if($v==0){
						 break;
					}
	                set_search_form_value($where_sales_trade, $k, $v, 'so');
	                break;
	            }
	            case 'receiver_country':{//区县 receiver_country
					if($v==0){
						 break;
					}
	                set_search_form_value($where_sales_trade, 'receiver_district', $v, 'so');
	                break;
	            }
	            case 'receiver_name':{//收件人姓名 stockout_order
	                set_search_form_value($where_stockout_order, $k, $v, 'so');
	                break;
	            }
	            case 'flag_id':{//出库单标记  stokout_order
	                set_search_form_value($where_stockout_order, $k, $v, 'so',2);
	                break;
	            }
				case 'small_number':{ //货品数量区间小
					set_search_form_value($where_sales_trade['_string'], 'goods_count', $v, 'so',9,' AND ',' >= ');
					break;
				}
				case 'big_number':{ //货品数量区间大
					set_search_form_value($where_sales_trade['_string'], 'goods_count', $v, 'so',9,' AND ',' <= ');
					break;
				}
				case 'small_type':{ //货品种类区间小
					set_search_form_value($where_sales_trade['_string'], 'goods_type_count', $v, 'so',9,' AND ',' >= ');
					break;
				}
				case 'big_type':{ //货品种类区间大
					set_search_form_value($where_sales_trade['_string'], 'goods_type_count', $v, 'so',9,' AND ',' <= ');
					break;
				}
	            case 'has_invoice':{//是否包含发票 stockout_order
	                set_search_form_value($where_stockout_order, $k, $v, 'so',2);
	                break;
	            }
	            case 'is_block':{//是否被拦截
	                set_search_form_value($where_stockout_order,'block_reason',$v,'so',8);
					break;
	            }
	            case 'logistics_print_status':{ //是否打印物流单
	                set_search_form_value($where_stockout_order, $k, $v, 'so', 2);
	                break;
	            }
	            case 'sendbill_print_status':{ //是否打印发货单
	                set_search_form_value($where_stockout_order, $k, $v, 'so', 2);
	                break;
	            }
	            case 'src_tids':{//原始订单号  sales_trade
	                //set_search_form_value($where_sales_trade, $k, $v, 'st');
					set_search_form_value($where_sales_trade_order, 'src_tid', $v,'sto');
	                break;
	            }
	            case 'shop_id':{//店铺 sales_trade
	                set_search_form_value($where_sales_trade, $k, $v, 'st',2);
	                break;
	            }
	            case 'buyer_nick':{//客户网名 sales_trade
	                set_search_form_value($where_sales_trade, $k, $v, 'st',6);
	                break;
	            }
	            case 'spec_no':{//商家编码
	                set_search_form_value($where_sales_trade_order, $k, $v, 'sto',6);
	                break;
	            }
				case 'suite_no':{//组合装商家编码
	                set_search_form_value($where_sales_trade_order, $k, $v, 'sto',6);
	                break;
	            }
				case 'suite_name':{//组合装名称
	                set_search_form_value($where_sales_trade_order, $k, $v, 'sto',6);
	                break;
	            }
	            case 'consign_status':{
	                if(!check_regex('number',$v))
	                {
	                    break;
	                }
	                $where_stockout_order['so.consign_status'] = array('exp','&'.$v);
	                break;
	            }
				case 'consign_time_start':
					set_search_form_value($where_stockout_order['_string'], 'consign_time', $v,'so', 4,' AND ',' >= ');
					break;
				case 'consign_time_end':
					set_search_form_value($where_stockout_order['_string'], 'consign_time', $v,'so', 4,' AND ',' <= ');
					break;
				case 'multi_logistics':
					;
					break;
				case 'brand_id':
				    set_search_form_value($where_stockout_order, $k, $v, 'gg', 2, ' AND ');
					break;
				case 'class_id':
					$join = set_search_form_value($where_stockout_order, $k, $v, 'gg', 7, ' AND ');
				    break;
				case 'trade_fc_status':
				    set_search_form_value($where_sales_trade, 'check_step', $v, 'st', 2);
				    break;
				case 'fast_logis_printed':
				    set_search_form_value($where_stockout_order, 'logistics_print_status', 1, 'so', 2);
				    break;
                case 'fast_no_logis_printed':
				    set_search_form_value($where_stockout_order, 'logistics_print_status', 0, 'so', 2);
				    break;
                case 'fast_printed_not_stockout':
				    set_search_form_value($where_stockout_order, 'logistics_print_status', 1, 'so', 2);
                    set_search_form_value($where_stockout_order, 'status', 55, 'so', 2);
				    break;
                case 'fast_stockout_not_printed':
                    set_search_form_value($where_stockout_order, 'logistics_print_status', 0, 'so', 2);
                    set_search_form_value($where_stockout_order, 'status', 95, 'so',9,'','egt');
                    break;
				case 'examine_num':
					set_search_form_value($where_stockout_order,'logistics_print_status',1,'so',2);
					$where_stockout_order['so.consign_status'] = array('exp','&1=0');
                    $where_stockout_order['so.status'] = array('ELT',90);
					break;
					
				case 'fast_goods_printed':
				    set_search_form_value($where_stockout_order, 'sendbill_print_status', 1, 'so', 2);
				    break;
                case 'fast_no_goods_printed':
				    set_search_form_value($where_stockout_order, 'sendbill_print_status', 0, 'so', 2);
				    break;
                case 'fast_no_stockout':
				    set_search_form_value($where_stockout_order, 'status', 55, 'so', 2);
				    break;
                case 'fast_is_stockout':
				    set_search_form_value($where_stockout_order, 'status', 95, 'so', 2);
				    break;
                case 'fast_is_finish':
				    set_search_form_value($where_stockout_order, 'status', 110, 'so', 2);
				    break;
                case 'fast_is_checked':
                    $where_stockout_order['so.consign_status'] = array('exp','& 1');
				    break;
                case 'fast_no_checked':
                    $where_stockout_order['so.consign_status'] = array('exp','&1=0');
				    break;
                case 'fast_is_weighted':
                    $where_stockout_order['so.consign_status'] = array('exp','&2');
				    break;
                case 'fast_no_weighted':
                    $where_stockout_order['so.consign_status'] = array('exp','&2=0');
				    break;
                case 'fast_is_blocked':
                    set_search_form_value($where_stockout_order,'block_reason',1,'so',8);
                    break;
                case 'fast_no_blocked':
                    set_search_form_value($where_stockout_order,'block_reason',0,'so',8);
                    break;
                case 'fast_has_cliented':
                    $where_sales_trade['_string'] .= " AND  st.buyer_message <> ''";
                    break;
                case 'fast_no_cliented':
                    $where_sales_trade['_string'] .= " AND  st.buyer_message = ''";
                    break;
                case 'fast_one_day':
                    set_search_form_value($where_sales_trade['_string'], 'trade_time', date('Y-m-d'),'st', 3,' AND ',' >= ');
                    break;
                case 'fast_tow_day':
                    set_search_form_value($where_sales_trade['_string'], 'trade_time', date('Y-m-d',strtotime('-1 day')),'st', 3,' AND ',' >= ');
                    break;
                case 'fast_one_week':
                    set_search_form_value($where_sales_trade['_string'], 'trade_time', date('Y-m-d',strtotime('-1 week')),'st', 3,' AND ',' >= ');
                    break;
                case 'fast_one_month':
                    set_search_form_value($where_sales_trade['_string'], 'trade_time', date('Y-m-d',strtotime('-1 month')),'st', 3,' AND ',' >= ');
                    break;
				case 'cs_remark':
					set_search_form_value($where_sales_trade, $k, $v,'st', 6,' AND ');
					break;
				case 'buyer_message':
					set_search_form_value($where_sales_trade, $k, $v,'st', 10,' AND ');
					break;
				case 'remark_id':
					if($v!=''&&$v!='all'){
						if($v==0){//无备注
							$where_sales_trade['_string'].=" AND st.cs_remark = '' AND st.buyer_message = '' ";
						}elseif($v==1){//有备注
							$where_sales_trade['_string'].=" AND (st.cs_remark <> '' OR st.buyer_message <> '' ) ";
						}elseif($v==2){//有客服备注
							$where_sales_trade['_string'].=" AND st.cs_remark <> '' ";
						}elseif($v==3){//有买家留言
							$where_sales_trade['_string'].=" AND st.buyer_message <> '' ";
						}elseif($v==4){//备注+留言
							$where_sales_trade['_string'].=" AND st.cs_remark <> '' AND st.buyer_message <> '' ";
						}
					}
					break;
				case 'one_order_one_good':{//一单一货
					if($v=='1'){
						set_search_form_value($where_sales_trade['_string'], 'goods_count', '1', 'so',9,' AND ',' = ');
					}elseif($v=='0'){
						set_search_form_value($where_sales_trade['_string'], 'goods_count', '1', 'so',9,' AND ',' <> ');
					}
					break;
				}
				case 'is_stalls':{//是否为档口单
					if($v!='all')
						set_search_form_value($where_stockout_order, $k, $v, 'so',1);
					break;
				}
				case 'goods_name_include':{//品名包含
					set_search_form_value($where_stockout_order_detail['include'], 'goods_name', $v,'sod', 10,' AND ');
					break;
				}
				case 'goods_name_not_include':{//品名不包含
					set_search_form_value($where_stockout_order_detail['not_include'], 'goods_name', $v,'sod', 10,' AND ');
					$where_stockout_order_detail['not_include_page'] = " AND page_1.stockout_id is NULL ";
					break;
				}
				case 'small_paid':{ //实付金额区间小
					$where_sales_trade['_string'].=' AND st.paid >= '.addslashes($v).' ';
					break;
				}
				case 'big_paid':{ //实付金额区间大
					$where_sales_trade['_string'].=' AND st.paid <= '.addslashes($v).' ';
					break;
				}
				case 'small_calc_weight':{ //预估重量区间小
					$where_sales_trade['_string'].=' AND so.calc_weight >= '.addslashes($v).' ';
					break;
				}
				case 'big_calc_weight':{ //预估重量区间大
					$where_sales_trade['_string'].=' AND so.calc_weight <= '.addslashes($v).' ';
					break;
				}
				case 'include_goods_type_count':{ //包含货品
					$ret = $this->includeGoodsIds($v);
					set_search_form_value($where_stockout_order,'stockout_id',$ret,'so',2);
					break;
				}
				case 'not_include_goods_type_count':{ //不包含货品
					$ret = $this->includeGoodsIds($v);
					$where_stockout_order['_string'].= " AND so.stockout_id NOT IN (".$ret.") ";
					break;
				}
				case 'batch_no': {//批次号
					set_search_form_value($where_stockout_order, $k, $v, 'so');
					break;
				}
                case 'unique_code':{//唯一码
                    set_search_form_value($where_stockout_order, 'unique_code', $v, 'slgd',1);
                    break;
                }
				case 'radio' : {
					if($v == 1 && $type == 'stockoutPrint' && $sort == 'id'){
						$sort = 'spec_id';
					}elseif($v == 2 && $type == 'stockoutPrint' && $sort == 'id'){
						$sort = 'position_id';
					}
					break;
				}
	            default:
	                \Think\Log::write("unknown field:" . print_r($k, true) . ",value:" . print_r($v, true));
	                break;
	        }
	    }
		if($print_batch_order!=''){
			unset($where_stockout_order['so.sendbill_print_status']);
			unset($where_stockout_order['so.logistics_print_status']);
		}
		$operator_id = get_operator_id();
		if($operator_id == 1 && strpos($search['warehouse_id'],',') != false){
			unset($where_stockout_order['so.warehouse_id']);
		}
		if($operator_id == 1 && strpos($search['shop_id'],',') != false){
			unset($where_sales_trade['st.shop_id']);
		}
		$page = intval($page);
		$rows = intval($rows);
	    $limit=($page - 1) * $rows . "," . $rows;
	    if($type == 'stockoutPrint'){
			$order_type = $order;
			unset($order);
			$arr_sort=array('position_id'=>'sod.position_id','spec_id'=>'sod.spec_id','warehouse_type'=>'so.warehouse_type','invoice_title'=>'st.invoice_title','id'=>'so.stockout_id','buyer_nick'=>'st.buyer_nick','logistics_name'=>'so.logistics_id','buyer_message'=>'st.buyer_message','cs_remark'=>'st.cs_remark','print_remark'=>'st.print_remark','warehouse_name'=>'so.warehouse_id','shop_name'=>'st.shop_id','src_tids'=>'st.src_tids','trade_type'=>'st.trade_type','checker_name'=>'st.checker_id','pay_time'=>'st.pay_time','trade_time'=>'st.trade_time','weight'=>'so.weight','goods_abstract'=>'so.single_spec_no','paid'=>'st.paid');//用于映射排序
			$order['in_sort'] = addslashes((empty($arr_sort[$sort]) ? 'so.'.$sort : $arr_sort[$sort]).' '.$order_type);//内层排序
			if($print_batch_order!=''&&$sort=='id'&&$order_type=='desc'){
				$order['out_sort'] = "FIND_IN_SET(so.stockout_id,'".$print_batch_order."')";
			}else{
				$order['out_sort'] = addslashes((empty($arr_sort[$sort]) ? 'so.'.$sort : $arr_sort[$sort]).' '.$order_type);//外层排序
			}
		}else{
			$order = $sort.' '.$order;
			$order = addslashes($order);
		}
		if(isset($where_stockout_order['_string'])){
			$where_stockout_order['_string'] = trim($where_stockout_order['_string'],' AND ');
		}
		if(isset($where_sales_trade['_string'])){
            $where_sales_trade['_string'] = trim($where_sales_trade['_string'],' AND ');
		}
	    try {
	        $m = $this->alias("so");
	        switch ($type)
	        {
	            case 'salesstockout':{
	            	if(isset($search['multi_logistics'])&&$search['multi_logistics']==1 || !empty($search['multi_logistics_no']))
				        $m->join('sales_multi_logistics sml on so.stockout_id = sml.stockout_id');
                    if(!empty($search['unique_code']))
                        $m->join('stalls_less_goods_detail slgd on so.src_order_id = slgd.trade_id');
                    $sql = $this->stockoutSales($m, $where_stockout_order, $where_sales_trade, $where_sales_trade_order,$where_stockout_order_detail,$order,$limit);
                    break;
	            }
	            case 'stockoutmanage':{
					$sql = $this->stockoutManagement($m, $where_stockout_order,$order,$limit);
	                break;
	            }
	            case 'stockoutPrint':{
                    if($is_empty_search){
						$print_status_list = get_config_value(array('stockout_sendbill_print_status','stockout_logistics_print_status'),array(0,0));
						if($print_status_list['stockout_sendbill_print_status'] != 3)
							$where_stockout_order['so.sendbill_print_status'] = array('eq',$print_status_list['stockout_sendbill_print_status']);
						if($print_status_list['stockout_logistics_print_status'] != 3)
							$where_stockout_order['so.logistics_print_status'] = array('eq',$print_status_list['stockout_logistics_print_status']);
					}
					if(isset($search['multi_logistics'])&&$search['multi_logistics']==1 || !empty($search['multi_logistics_no']))
						$m->join('sales_multi_logistics sml on so.stockout_id = sml.stockout_id');
                    if(!empty($search['unique_code']))
                        $m->join('stalls_less_goods_detail slgd on so.src_order_id = slgd.trade_id');
					if((isset($search['brand_id'])&&$search['brand_id']!="all")||(isset($search['class_id'])&&$search['class_id']!=-1)){
						$m->join('left join stockout_order_detail sod ON sod.stockout_id = so.stockout_id')->join('left join goods_goods gg ON gg.goods_id = sod.goods_id')->join($join);
					}

					$sql = $this->stockoutPrint($m, $where_stockout_order, $where_sales_trade, $where_sales_trade_order,$where_stockout_order_detail,$order,$limit);
	                break;
	            }
	            default:
	                \Think\Log::write("unknown type:" . $type);
	                break;
	        }
			$sql_total = $m->fetchSql(true)->count('distinct so.stockout_id');
			$total=$m->query($sql_total);
			$total = $total[0]['tp_count'];
			$list=$total?$m->query($sql):array();
            if($type != 'stockoutmanage'){
            foreach ($list as $k1 => $v1) {
                    if($list[$k1]['logistics_sync_info'] == '尚未确认发货'){
                        continue;
                    }else{
                        $list[$k1]['logistics_sync_info'] = '<a href="javascript:void(0)" onClick="open_menu(\'物流同步\', \''.U('Stock/ApiLogisticsSync/getApiLogisticsSyncList').'\')">'.$list[$k1]['logistics_sync_info'].'</a>';
                    }
                }
            }
            //'<a href="javascript:void(0)" onClick="'+"open_menu('物流同步', '{:U('Stock/StockSalesPrint/getPrintList')}')"+'">'+value+'</a>';
			$data=array('total'=>$total,'rows'=>$list);
	    }catch(\PDOException $e){
			
	        \Think\Log::write('sql:'.$sql.'----'.$e->getMessage());
	        $data=array('total'=>0,'rows'=>array(),'msg'=>$e->getMessage());
	    }
	    return $data;
	}
	public function includeGoodsIds($where){
		try{
			$where = json_decode($where,true);
			$tmpArr = [];
			$conditionMap = ['<','=','>'];
			//$includeRelationMap = ['<','=','>'];
			$includeRelationCount = substr_count($where['include_val'],',')+1;
			$includes = explode(',',$where['include_val']);
			foreach($includes as $v){
				$tmpArr[] = explode('-',$v);
			}
			switch($where['include_relation']){
				case '0':
					$where_sql = " AND sod.spec_id = '".$tmpArr[0][0]."' AND sod.num ".$conditionMap[$tmpArr[0][1]]." '".$tmpArr[0][2]."' AND so.goods_type_count>=".$includeRelationCount." )";
					break;
				case '1':
					foreach($tmpArr as $k=>$v){
						if($k==0){
							$where_sql = " AND sod.spec_id = '".$v[0]."' AND sod.num ".$conditionMap[$v[1]]." '".$v[2]."'";
						}else{
							$where_sql .= " OR (sod.spec_id = '".$v[0]."' AND sod.num ".$conditionMap[$v[1]]." '".$v[2]."')";
						}
					}
					$where_sql .= " )";
					break;
				case '2':
					$where_sql = " AND sod.spec_id = '".$tmpArr[0][0]."' AND sod.num ".$conditionMap[$tmpArr[0][1]]." '".$tmpArr[0][2]."' AND so.goods_type_count=".$includeRelationCount." )";
					break;
				default:
					\Think\Log::write("unknown include_relation:" . $where['include_relation']);
					break;
			}
			$sales_print_time_range = get_config_value('sales_print_time_range',7);
			$sql = "SELECT GROUP_CONCAT(DISTINCT so.stockout_id) AS id"
			." FROM stockout_order so"
			." INNER JOIN sales_trade st ON so.src_order_id = st.trade_id"
			." INNER JOIN stockout_order_detail sod ON sod.stockout_id = so.stockout_id"
			." WHERE so.src_order_type = 1 AND so.status >= 55 AND ( (so.consign_time = '1000-01-01' OR DATEDIFF(NOW(),so.consign_time) <= ".$sales_print_time_range.") AND so.warehouse_type = 1".$where_sql;
			$tmpStockoutIds = $this->query($sql);
			$tmpStockoutIds = $tmpStockoutIds[0]['id'];
			if($tmpStockoutIds!='' && !empty($tmpStockoutIds)&&$where['include_relation']!=1){
				array_shift($tmpArr);
				foreach($tmpArr as $k=>$v){
					$query = "SELECT GROUP_CONCAT(DISTINCT sod.stockout_id) AS id FROM stockout_order_detail sod"
						." WHERE sod.stockout_id IN (".$tmpStockoutIds.") AND sod.spec_id = '".$v[0]."' AND sod.num ".$conditionMap[$v[1]]." '".$v[2]."'";
					$tmpStockoutIds = $this->query($query);
					$tmpStockoutIds = $tmpStockoutIds[0]['id'];
					if($tmpStockoutIds==''||empty($tmpStockoutIds)){
						break;
					}
				}
			}
			return empty($tmpStockoutIds)?'':$tmpStockoutIds;
		}catch(\PDOException $e){
			$msg = $e->getMessage();
			\Think\Log::write($this->name.'-includeGoodsIds-'.$msg.$query?$query:'');
		}catch(\Exception $e){
			$msg = $e->getMessage();
			\Think\Log::write($this->name.'-includeGoodsIds-'.$msg.$query?$query:'');
		}
	}
	/*
	 * 检测销售出库单数据
	 * */
	function getOutputCount($search){
		D('Setting/EmployeeRights')->setSearchRights($search,'warehouse_id',2);
		$where_stockout_order = array();
		$where_sales_trade_order = array();
		$where_sales_trade = array();
		foreach ($search as $k => $v) {
			if ($v === '') continue;
			switch ($k) {
				case 'stockout_no':{//出库单号  stockout_order
					set_search_form_value($where_stockout_order, $k, $v, 'so' );
					break;
				}
				case 'src_order_no':{//原始单号 stockout_order
					set_search_form_value($where_stockout_order, $k, $v,'so');
					break;
				}
				case 'status':{//出库单状态 stockout_order
					set_search_form_value($where_stockout_order, $k, $v, 'so', 2);
					break;
				}
				case 'warehouse_id'://仓库类型
					set_search_form_value($where_stockout_order, $k, $v, 'so', 2);
					break;
				case 'src_order_type':{//出库单类别 stockout_order
					set_search_form_value($where_stockout_order, $k, $v, 'so', 2);
					break;
				}
				case 'block_reason':{//拦截原因 stockout_order
					if(!check_regex('number',$v))
					{
						break;
					}
					$where_stockout_order['so.block_reason'] = array('exp','&'.$v);
					break;
				}
				case 'trade_from':{//订单来源 sales_trade
					set_search_form_value($where_sales_trade, $k, $v, 'st', 2);
					break;
				}
                case 'trade_fc_status':
                    set_search_form_value($where_sales_trade, 'check_step', $v, 'st', 2);
                    break;
				case 'logistics_id':{//物流公司 stockout_order
					set_search_form_value($where_stockout_order, $k, $v, 'so', 2);
					break;
				}
				case 'operator_id':{//经办人  stockout_order
					set_search_form_value($where_stockout_order, $k, $v, 'so', 2);
					break;
				}
				case 'logistics_no':{//物流单号 stockout_order
					if($search['multi_logistics'] == 1)
						set_search_form_value($where_stockout_order, $k, $v, 'sml');
					else set_search_form_value($where_stockout_order, $k, $v, 'so');
					break;
				}
				case 'receiver_mobile':{//收件人手机 stockout_order
					set_search_form_value($where_stockout_order, $k, $v, 'so');
					break;
				}
				case 'receiver_name':{//收件人姓名 stockout_order
					set_search_form_value($where_stockout_order, $k, $v, 'so');
					break;
				}
				case 'flag_id':{//出库单标记  stokout_order
					set_search_form_value($where_stockout_order, $k, $v, 'so',2);
					break;
				}
				case 'has_invoice':{//是否包含发票 stockout_order
					set_search_form_value($where_stockout_order, $k, $v, 'so',2);
					break;
				}
				case 'is_block':{//是否被拦截
					set_search_form_value($where_stockout_order,'block_reason',$v,'so',8);
					break;
				}
				case 'logistics_print_status':{ //是否打印物流单
					set_search_form_value($where_stockout_order, $k, $v, 'so', 2);
					break;
				}
				case 'sendbill_print_status':{ //是否打印发货单
					set_search_form_value($where_stockout_order, $k, $v, 'so', 2);
					break;
				}
				case 'src_tids':{//原始订单号  sales_trade
					set_search_form_value($where_sales_trade_order, 'src_tid', $v,'sto');
					break;
				}
				case 'shop_id':{//店铺 sales_trade
					set_search_form_value($where_sales_trade, $k, $v, 'st',2);
					break;
				}
				case 'buyer_nick':{//客户网名 sales_trade
					set_search_form_value($where_sales_trade, $k, $v, 'st',6);
					break;
				}
				case 'spec_no':{//商家编码
					set_search_form_value($where_sales_trade_order, $k, $v, 'sto');
					break;
				}
				case 'consign_status':{
					if(!check_regex('number',$v))
					{
						break;
					}
					$where_stockout_order['so.consign_status'] = array('exp','&'.$v);
					break;
				}
				case 'consign_time_start':
					set_search_form_value($where_stockout_order['_string'], 'consign_time', $v,'so', 4,' AND ',' >= ');
					break;
				case 'consign_time_end':
					set_search_form_value($where_stockout_order['_string'], 'consign_time', $v,'so', 4,' AND ',' <= ');
					break;
				case 'multi_logistics':
					;
					break;
				case 'brand_id':
					set_search_form_value($where_stockout_order, $k, $v, 'gg', 2, ' AND ');
					break;
				default:
					\Think\Log::write("unknown field:" . print_r($k, true) . ",value:" . print_r($v, true));
					break;
			}
		}
		if(isset($where_stockout_order['_string'])){
			$where_stockout_order['_string'] = trim($where_stockout_order['_string'],' AND');
		}
		$m = M('StockoutOrder')->alias("so");
		$sql=$this->getStockoutSalesList($m, $where_stockout_order, $where_sales_trade, $where_sales_trade_order);
		$total=$m->query($sql);
		return $total[0]['count'];
	}

	private function stockoutManagement(&$m,&$where_stockout_order,$order,$limit)
	{
	    isset($where_stockout_order['so.src_order_type'])?:$where_stockout_order['so.src_order_type'] = array('neq',1);
	    $m = $m->where($where_stockout_order);
	    $page = clone $m;
	    $sql_page = $page->field('so.stockout_id id')->order($order)->group('so.stockout_id')->limit($limit)->fetchSql(true)->select();
		$point_number = get_config_value('point_number',0);
		$point_number = intval($point_number);
		$show_sql = "CAST(so.goods_count AS DECIMAL(19,".$point_number."))";	
	    $sql = " select so.stockout_id AS id,so.warehouse_id ,so.stockout_no, so.src_order_type, so.src_order_no, so.status,"
	            ." so.logistics_no, so.post_cost,".$show_sql." goods_count, so.goods_type_count, so.remark, so.created, so.consign_time,"
	            ." he.fullname operator_id, cl.logistics_name logistics_id,"
	            ." cw.name warehouse_name"
				." FROM stockout_order so"
				." LEFT JOIN hr_employee he ON he.employee_id = so.operator_id"
				." LEFT JOIN cfg_logistics cl ON cl.logistics_id = so.logistics_id"
				." LEFT JOIN cfg_warehouse cw ON cw.warehouse_id = so.warehouse_id"
				." join (".$sql_page.") page ON page.id = so.stockout_id ORDER BY id DESC";
	    return $sql;
	}
	/* private function stockoutPrint(&$m,&$where_stockout_order,$where_sales_trade,$where_sales_trade_order,$order,$limit)
	{
	    $where_stockout_order['so.src_order_type'] = array('eq',1);
	    if(isset($where_stockout_order['so.status']))
	    {
	        $where_stockout_order['so.status'] = array(array('EGT',55),$where_stockout_order['so.status']);
	    }else{
	        $where_stockout_order['so.status'] = array('EGT',55);
	    }
	    if(!empty($where_sales_trade))
	    {
	        $m = $m->join('sales_trade st on  so.src_order_id = st.trade_id')->where($where_sales_trade);
	    }
	    if(!empty($where_sales_trade_order))
	    {
	        $m = $m->join('sales_trade_order sto on so.src_order_id = st.trade_id')->where($where_sales_trade_order);
	    }
	    $m = $m->where($where_stockout_order);
	    $page = clone $m;
	    $sql_page = $page->field('so.stockout_id id')->order($order)->group('so.stockout_id')->limit($limit)->fetchSql(true)->select();
	    $sql = 'SELECT so.stockout_id id,so.src_order_no,so.stockout_no,so.src_order_type,so.src_order_id,so.status, '
				. 'so.error_info,so.consign_status,so.operator_id,so.goods_count,so.goods_type_count,so.logistics_print_status,so.sendbill_print_status,'//so.freeze_reason,cor.title as freeze_reason,
				. 'so.receiver_address,so.receiver_name,so.receiver_area,so.receiver_mobile,so.receiver_telno,so.receiver_zip,so.receiver_province,so.receiver_city,so.receiver_district,'
				. 'so.logistics_id,cl.logistics_name,cl.bill_type,cl.logistics_type,so.goods_count goods_total_cost,so.calc_post_cost,so.post_cost,so.calc_weight,'
				. 'so.weight,so.has_invoice,so.printer_id,so.package_id packager_id,so.watcher_id,so.logistics_print_status,so.sendbill_print_status,'
				. 'so.batch_no,so.logistics_no,so.picklist_no,so.consign_time,so.outer_no,so.picker_id,so.examiner_id,sln.waybill_info,so.flag_id,'
				. 'st.warehouse_id,st.warehouse_type,st.trade_time,st.pay_time,st.buyer_nick,st.shop_id,st.src_tids,st.buyer_message,st.receivable,'
				. 'st.salesman_id,he.fullname as checker_id,st.fchecker_id,st.shop_id,st.trade_type,cs.shop_name,cw.contact,cw.mobile,cw.telno,cw.province,cw.city,cw.district,cw.address,cw.zip '//st.checker_id
				. 'FROM stockout_order so '
				. 'JOIN sales_trade st ON so.src_order_id = st.trade_id '
				. 'JOIN cfg_shop cs ON cs.shop_id = st.shop_id '
				. 'LEFT JOIN cfg_logistics cl ON cl.logistics_id = so.logistics_id '
				. 'LEFT JOIN cfg_warehouse cw ON cw.warehouse_id = so.warehouse_id '
				. 'LEFT JOIN stock_logistics_no sln ON sln.logistics_id = so.logistics_id and sln.logistics_no = so.logistics_no '
// 				. 'LEFT JOIN cfg_oper_reason cor ON cor.reason_id = so.freeze_reason '
				. 'LEFT JOIN hr_employee he ON he.employee_id = st.checker_id '
				. 'JOIN (' . $sql_page . ') page ON page.id = so.stockout_id'
	            . ' order by '.$order;
	    return $sql;
	     
	} */
	private function stockoutPrint(&$m,&$where_stockout_order,$where_sales_trade,$where_sales_trade_order,$where_stockout_order_detail,$order,$limit)
	{
		$setting_config = get_config_value(array('sales_print_time_range','stockout_field_goods_pic','show_number_to_star','point_number','stock_print_sender_from'),array(7,0,0,0,0));
     /*   if($setting_config['stockout_field_goods_pic']&1)
        {
            $field_pic = 'TRIM(BOTH \',\' FROM GROUP_CONCAT(CONCAT(if(TRIM(gs_1.img_url)=\'\' OR gs_1.img_url IS NULL,\'无\',gs_1.img_url),\'::\',CAST(sod.num AS DECIMAL(19,'.$setting_config['point_number'].')),\'::\',gg_1.goods_name,\'-\',gs_1.spec_name))) img_url, ';
            $left_pic = ' left JOIN stockout_order_detail sod ON sod.stockout_id = so.stockout_id '. 'LEFT JOIN goods_spec gs_1 ON gs_1.spec_id = sod.spec_id and gs_1.deleted = 0 left join goods_goods gg_1 on gg_1.goods_id = gs_1.goods_id ';
        }else{
            $field_pic = ' ';
            $left_pic = ' ';
        }
		*/
        $where_page_not_incluede['so.src_order_type'] = $where_stockout_order['so.src_order_type'] = array('eq',1);

	    if(isset($where_stockout_order['so.status']))
	    {
			$where_page_not_incluede['so.status'] = $where_stockout_order['so.status'] = array(array('EGT',55),$where_stockout_order['so.status']);
	    }else{
			$where_page_not_incluede['so.status'] = $where_stockout_order['so.status'] = array('EGT',55);
	    }
		//$in_order = explode(' ',$order['in_sort'])[0];
		//$trade_order_arr = array('st.buyer_nick','st.paid','st.buyer_message','st.cs_remark','st.shop_id','st.src_tids','st.trade_type','st.checker_id','st.trade_time','st.pay_time');
		//if(!empty($where_sales_trade['st.shop_id']) || in_array($in_order,$trade_order_arr)){
			$m = $m->join('sales_trade st on  so.src_order_id = st.trade_id')->where($where_sales_trade);
		//}
	     if(!empty($where_sales_trade_order))
	    {
			$m = $m->join('sales_trade_order sto on so.src_order_id = sto.trade_id')->where($where_sales_trade_order);
		}
		//$m = $m->join('cfg_warehouse cw ON cw.warehouse_id = so.warehouse_id');
		if(isset($where_stockout_order['_string'])){
			$order_where = " AND (so.consign_time = '1000-01-01' OR DATEDIFF(NOW(),so.consign_time) <= ".$setting_config['sales_print_time_range'].") ";
			$where_page_not_incluede['_string'] = " (so.consign_time = '1000-01-01' OR DATEDIFF(NOW(),so.consign_time) <=  ".$setting_config['sales_print_time_range'].") ";
		}else{
			$where_page_not_incluede['_string'] = $order_where = " (so.consign_time = '1000-01-01' OR DATEDIFF(NOW(),so.consign_time) <=  ".$setting_config['sales_print_time_range'].") ";
		}
		if((!empty($where_stockout_order_detail['include']) || stripos($order['in_sort'],'sod') !== false) && (!isset($where_stockout_order['gg.brand_id']) && !isset($where_stockout_order['gg.brand_id']))){
			$m = $m->join('stockout_order_detail sod ON sod.stockout_id = so.stockout_id');
			$order_where .= $where_stockout_order_detail['include'];
		}
		$join_sod_sql = '';
		if(stripos($order['in_sort'],'sod') !== false){
			$join_sod_sql = ' LEFT JOIN stockout_order_detail sod ON sod.stockout_id = so.stockout_id ';
		}
		$where_stockout_order['_string'] = (isset($where_stockout_order['_string'])?$where_stockout_order['_string']:'').$order_where;
		if(isset($where_sales_trade['_string'])){
            isset($where_stockout_order['_string'])?$where_stockout_order['_string'] =' ('.$where_stockout_order['_string']. ') AND '.$where_sales_trade['_string']:$where_stockout_order['_string'] = $where_sales_trade['_string'];
        }
		$where_page_not_incluede['so.warehouse_type'] = $where_stockout_order['so.warehouse_type'] = array('in',array(1,127));
		if(!empty($where_stockout_order_detail['not_include'])){
			$where_page_not_incluede['_string'].=$where_stockout_order_detail['not_include'];
			$sql_page_not_include = M('stockout_order')->field('so.stockout_id')->alias('so')->join('stockout_order_detail sod ON sod.stockout_id = so.stockout_id')->where($where_page_not_incluede)->group('so.stockout_id')->fetchSql(true)->select();
			$where_stockout_order['_string'].=$where_stockout_order_detail['not_include_page'];
			$m = $m->join('LEFT JOIN ('.$sql_page_not_include.') page_1 ON page_1.stockout_id = so.stockout_id ');
		}
	    $m = $m->where($where_stockout_order);
		$page = clone $m;
		$sql_page = $page->field('so.stockout_id id')->order($order['in_sort'])->group('so.stockout_id')->limit($limit)->fetchSql(true)->select();
	    $print_price_point_num = D('Stock/StockoutOrderDetail')->savePrintPointNum();
		$print_price_point_num = empty($print_price_point_num['data'])&&!is_numeric($print_price_point_num['data'])?'4':$print_price_point_num['data'];
		$point_number = $setting_config['point_number'];
		$point_number = intval($point_number);
		$field_content = array(
			'goods_total_cost'=>'CAST(so.goods_total_cost AS DECIMAL(19,'.$print_price_point_num.')) goods_total_cost',
		);
		$field_right = D('Setting/EmployeeRights')->getFieldsRight('',$field_content);
		$goods_total_cost = $field_right['goods_total_cost'];
		$receivable = 'CAST(st.receivable AS DECIMAL(19,'.$print_price_point_num.')) receivable';
		$cod_amount = 'CAST(st.cod_amount AS DECIMAL(19,'.$print_price_point_num.')) cod_amount';
		$post_amount = 'CAST(st.post_amount AS DECIMAL(19,'.$print_price_point_num.')) post_amount';
		$goods_count = 'CAST(so.goods_count AS DECIMAL(19,'.$point_number.')) goods_count';
		$sql = 'select so.src_order_id,so.package_count,so.receiver_dtb,so.error_info,so.operator_id,so.logistics_print_status,so.sendbill_print_status,so.picklist_print_status,so.receiver_province,so.receiver_city,so.receiver_district,'
	                    .'so.logistics_id,'.$goods_total_cost.',so.printer_id,so.watcher_id,so.batch_no,so.picklist_no,'//,so.outer_no,so.logistics_print_status,so.sendbill_print_status
	                    .'so.stockout_id id,so.src_order_no,so.stockout_no,so.src_order_type,so.status,so.consign_status,'.$goods_count.',so.goods_type_count,'//so.picker_id,so.examiner_id,
	                    .'so.receiver_district  district_id,so.receiver_city city_id,so.receiver_province province_id,so.receiver_address,so.receiver_name,so.receiver_area,IF('.$setting_config['show_number_to_star'].'=0,so.receiver_mobile,INSERT( so.receiver_mobile,4,4,\'****\')) receiver_mobile,IF('.$setting_config['show_number_to_star'].'=0,so.receiver_telno,INSERT(so.receiver_telno,4,4,\'****\')) receiver_telno,so.receiver_zip,so.calc_post_cost,so.post_cost,so.calc_weight,so.weight,'//
	                    .'so.has_invoice,so.logistics_no,so.consign_time,IF(so.flag_id,so.flag_id,st.flag_id) flag_id,so.block_reason,so.is_stalls,'//
                        .'cl.logistics_name,cl.bill_type,cl.logistics_type, cl.logistics_id,cl.app_key,'
                        .'st.delivery_term,st.warehouse_id,cw.type warehouse_type,st.checker_id,st.shop_id,st.src_tids,st.buyer_message,st.cs_remark,st.print_remark,'.$receivable.',st.salesman_id,st.trade_time,st.pay_time,st.buyer_nick,st.trade_type,st.platform_id,st.invoice_title,st.invoice_content,CONCAT_WS(\' \',st.invoice_title,st.invoice_content) as invoice_message,st.paid,'.$cod_amount.',st.check_step,'.$post_amount.','
                        //.'sln.waybill_info,'
                        .'so.reserve as waybill_info,'
                        .'he.fullname as checker_name,'
                        .'IF('.$setting_config['stock_print_sender_from'].',cs.contact,cw.contact) contact,IF('.$setting_config['stock_print_sender_from'].',cs.mobile,cw.mobile) mobile,IF('.$setting_config['stock_print_sender_from'].',cs.telno,cw.telno) telno,IF('.$setting_config['stock_print_sender_from'].',dp.name,cw.province) province,IF('.$setting_config['stock_print_sender_from'].',dc.name,cw.city) city,IF('.$setting_config['stock_print_sender_from'].',dd.name,cw.district) district,IF('.$setting_config['stock_print_sender_from'].',cs.address,cw.address) address,cw.zip,cw.name warehouse_name,'
                        .'cs.shop_name,cs.website,'
                       // .$field_pic
						//.'IF(als.sync_status is null,"",als.sync_status) AS sync_status,'
                        .'IF(so.consign_status & 128,\'物流同步成功\',IF(so.consign_status & 1024,\'多条同步信息,单击跳转查看\',IF(so.consign_status & 512,\'同步失败\',IF(so.consign_status & 256,\'等待同步\',IF(so.consign_status & 64,\'请跳转查看\',IF(so.consign_status & 32,\'无需物流同步\',\'尚未确认发货\')))) )) as logistics_sync_info,'
						
						//.'IF(so.consign_status & ,\'尚未确认发货\',IF(als.sync_status is NULL,\'无需物流同步\',IF(sum(IF(als.sync_status<0,1,0))>0,\'请跳转查看\',IF(sum(als.sync_status)=3*count(1),\'物流同步成功\',IF(sum(als.sync_status)=0,\'等待同步\',IF(sum(als.sync_status)=2*count(1),\'同步失败\',\'多条同步信息,单击跳转查看\')))) )) as logistics_sync_info,'
                      
					  //  .'IF(sod.spec_no IS NULL,IFNULL(gst.suite_name,\'多种货品\'),CONCAT(sod.goods_name,\'-\',sod.spec_name)) goods_abstract '
						.'so.single_spec_no goods_abstract '
					   . 'FROM stockout_order so '
						 . 'JOIN (' . $sql_page . ') page ON page.id = so.stockout_id'
	                    . ' JOIN sales_trade st ON so.src_order_id = st.trade_id '
	                    . ' JOIN cfg_shop cs ON cs.shop_id = st.shop_id '
                       // .$left_pic
                     //   . 'LEFT JOIN goods_spec gs ON gs.spec_no = so.single_spec_no and gs.deleted = 0 '
						.$join_sod_sql
					//	. 'LEFT JOIN stockout_order_detail sod ON sod.stockout_id = so.stockout_id '
                     //   . 'LEFT JOIN goods_goods gg ON gg.goods_id = gs.goods_id '
					//	. 'LEFT JOIN goods_suite gst ON gst.suite_no = so.single_spec_no and gst.deleted = 0 '
	                    . 'LEFT JOIN cfg_logistics cl ON cl.logistics_id = so.logistics_id '
                      //  . 'LEFT JOIN api_logistics_sync als ON als.trade_id = st.trade_id '
                        . 'LEFT JOIN cfg_warehouse cw ON cw.warehouse_id = so.warehouse_id '
	                  //  . 'LEFT JOIN stock_logistics_no sln ON sln.logistics_id = so.logistics_id and sln.logistics_no = so.logistics_no '
	                    // 				. 'LEFT JOIN cfg_oper_reason cor ON cor.reason_id = so.freeze_reason '
	                   . 'LEFT JOIN hr_employee he ON he.employee_id = st.checker_id '

	                   . 'LEFT JOIN dict_province dp ON dp.province_id = cs.province '
	                   . 'LEFT JOIN dict_city dc ON dc.city_id = cs.city '
	                   . 'LEFT JOIN dict_district dd ON dd.district_id = cs.district '

					   . ' group by so.stockout_id order by '.$order['out_sort'];
	    return $sql;
	
	}
	private function stockoutSales(&$m,&$where_stockout_order,$where_sales_trade,$where_sales_trade_order,$where_stockout_order_detail,$order,$limit)
{
	$where_page_not_incluede['so.src_order_type'] = $where_stockout_order['so.src_order_type'] = array('eq',1);

	if(isset($where_stockout_order['so.status']))
	{
		$where_page_not_incluede['so.status'] = $where_stockout_order['so.status'] = array(array('EGT',55),$where_stockout_order['so.status']);
	}else{
		$where_page_not_incluede['so.status'] = $where_stockout_order['so.status'] = array('EGT',55);
	}
//	if(!empty($where_sales_trade))
//	{
	$m = $m->join('sales_trade st on  so.src_order_id = st.trade_id')->where($where_sales_trade);
//	}
	if(!empty($where_sales_trade_order))
	{
		$m = $m->join('sales_trade_order sto on so.src_order_id = sto.trade_id')->where($where_sales_trade_order);
	}
	$order_where = '';
	if(!empty($where_stockout_order_detail['include'])){
		$m = $m->join('stockout_order_detail sod ON sod.stockout_id = so.stockout_id');
		$order_where .= ltrim($where_stockout_order_detail['include'], ' AND ');
		$where_stockout_order['_string'] = (isset($where_stockout_order['_string'])?$where_stockout_order['_string'].' AND ':'').$order_where;
	}
	if(isset($where_sales_trade['_string'])){
        isset($where_stockout_order['_string'])?$where_stockout_order['_string'] =' ('.$where_stockout_order['_string']. ') AND '.$where_sales_trade['_string']:$where_stockout_order['_string'] = $where_sales_trade['_string'];
    }
	if(!empty($where_stockout_order_detail['not_include'])){
		$where_page_not_incluede['_string']=ltrim($where_stockout_order_detail['not_include'], ' AND ');
		$sql_page_not_include = M('stockout_order')->field('so.stockout_id')->alias('so')->join('stockout_order_detail sod ON sod.stockout_id = so.stockout_id')->where($where_page_not_incluede)->group('so.stockout_id')->fetchSql(true)->select();
		$where_stockout_order['_string'].=(isset($where_stockout_order['_string'])?' AND ':'').ltrim($where_stockout_order_detail['not_include_page'], ' AND ');
		$m = $m->join('LEFT JOIN ('.$sql_page_not_include.') page_1 ON page_1.stockout_id = so.stockout_id ');
	}
	$m = $m->where($where_stockout_order);
	$page = clone $m;
	$sql_page = $page->field('so.stockout_id id')->order($order)->group('so.stockout_id')->limit($limit)->fetchSql(true)->select();
	$cfg_show_telno=get_config_value('show_number_to_star',1);
	$point_number = get_config_value('point_number',0);
	$point_number = intval($point_number);
	$field_right = D('Setting/EmployeeRights')->getFieldsRight('so.');
	$goods_count = 'CAST(sum(so.goods_count) AS DECIMAL(19,'.$point_number.')) goods_count';
	$sql = 'select so.src_order_id,so.error_info,so.operator_id,so.logistics_print_status,so.sendbill_print_status,so.receiver_province,so.receiver_city,so.receiver_district,'
			.'so.logistics_id,'.$field_right['goods_total_cost'].',so.printer_id,so.watcher_id,so.logistics_print_status,so.sendbill_print_status,so.batch_no,so.picklist_no,'//,so.outer_no
			.'so.stockout_id id,so.src_order_no,so.stockout_no,so.src_order_type,so.status,so.consign_status,'.$goods_count.',so.goods_type_count,'//so.picker_id,so.examiner_id,
			.'so.receiver_address,so.receiver_name,so.receiver_area,IF('.$cfg_show_telno.'=0,so.receiver_mobile,INSERT( so.receiver_mobile,4,4,\'****\')) receiver_mobile,IF('.$cfg_show_telno.'=0,so.receiver_telno,INSERT(so.receiver_telno,4,4,\'****\')) receiver_telno,so.receiver_zip,so.calc_post_cost,so.post_cost,so.calc_weight,so.weight,'//
			.'so.has_invoice,so.logistics_no,so.consign_time,so.block_reason,so.flag_id,'
			.'cl.logistics_name,cl.bill_type,cl.logistics_type, cl.logistics_id,so.outer_no,so.error_info,'
			.'st.warehouse_id,cw.type warehouse_type,st.checker_id,st.shop_id,st.src_tids,st.buyer_message,st.receivable,st.salesman_id,st.trade_time,st.pay_time,st.buyer_nick,st.trade_type,st.platform_id,st.paid,st.check_step,'
//                        .'sln.waybill_info,'
			.'he.fullname as checker_name,'
			.'cw.contact,cw.mobile,cw.telno,cw.province,cw.city,cw.district,cw.address,cw.zip,cw.name warehouse_name,'
			.'cs.shop_name,cs.website,'
			//.'IF(so.status <=55,\'尚未确认发货\',IF(als.sync_status is NULL,\'无需物流同步\',IF(sum(IF(als.sync_status<0,1,0))>0,\'请跳转查看\',IF(sum(als.sync_status)=3*count(1),\'物流同步成功\',IF(sum(als.sync_status)=0,\'等待同步\',IF(sum(als.sync_status)=2*count(1),\'同步失败\',\'多条同步信息,单击跳转查看\')))) )) as logistics_sync_info,'
			.'IF(so.consign_status & 128,\'物流同步成功\',IF(so.consign_status & 1024,\'多条同步信息,单击跳转查看\',IF(so.consign_status & 512,\'同步失败\',IF(so.consign_status & 256,\'等待同步\',IF(so.consign_status & 64,\'请跳转查看\',IF(so.consign_status & 32,\'无需物流同步\',\'尚未确认发货\')))) )) as logistics_sync_info,'
            //.'IF(gs.spec_no IS NULL,IFNULL(gst.suite_name,\'多种货品\'),CONCAT(gg.goods_name,\'-\',gs.spec_name)) goods_abstract '
			.'so.single_spec_no goods_abstract '
			. 'FROM stockout_order so '
			. 'JOIN sales_trade st ON so.src_order_id = st.trade_id '
			. 'JOIN cfg_shop cs ON cs.shop_id = st.shop_id '
			//. 'LEFT JOIN goods_spec gs ON gs.spec_no = so.single_spec_no and gs.deleted = 0 '
			//. 'LEFT JOIN goods_goods gg ON gg.goods_id = gs.goods_id '
			//. 'LEFT JOIN goods_suite gst ON gst.suite_no = so.single_spec_no and gst.deleted = 0 '
			. 'LEFT JOIN cfg_logistics cl ON cl.logistics_id = so.logistics_id '
            //. 'LEFT JOIN api_logistics_sync als ON als.stockout_id = so.stockout_id '
            . 'LEFT JOIN cfg_warehouse cw ON cw.warehouse_id = so.warehouse_id '
			//. 'LEFT JOIN stock_logistics_no sln ON sln.logistics_id = so.logistics_id and sln.logistics_no = so.logistics_no '
			// 				. 'LEFT JOIN cfg_oper_reason cor ON cor.reason_id = so.freeze_reason '
			. 'LEFT JOIN hr_employee he ON he.employee_id = st.checker_id '
			. 'JOIN (' . $sql_page . ') page ON page.id = so.stockout_id'
			. ' group by so.stockout_id order by '.$order;
	return $sql;

}

	/*
	 * 获取到销售出库单导出的数据数量
	 * */
	private function getStockoutSalesList(&$m,&$where_stockout_order,$where_sales_trade,$where_sales_trade_order)
	{
		$where_stockout_order['so.src_order_type'] = array('eq',1);

		if(isset($where_stockout_order['so.status']))
		{
			$where_stockout_order['so.status'] = array(array('EGT',55),$where_stockout_order['so.status']);
		}else{
			$where_stockout_order['so.status'] = array('EGT',55);
		}
		if(!empty($where_sales_trade))
		{
			$m = $m->join('sales_trade st on  so.src_order_id = st.trade_id')->where($where_sales_trade);
		}
		if(!empty($where_sales_trade_order))
		{
			$m = $m->join('sales_trade_order sto on so.src_order_id = sto.trade_id')->where($where_sales_trade_order);
		}
		$m = $m->where($where_stockout_order);
		$page = clone $m;
		$sql_page = $page->field('so.stockout_id id')->fetchSql(true)->select();
		$sql = 'select count(so.stockout_id) as count  FROM stockout_order so '
				. 'JOIN sales_trade st ON so.src_order_id = st.trade_id '
				. 'JOIN cfg_shop cs ON cs.shop_id = st.shop_id '
				. 'LEFT JOIN goods_spec gs ON gs.spec_no = so.single_spec_no and gs.deleted = 0 '
				. 'LEFT JOIN goods_goods gg ON gg.goods_id = gs.goods_id '
				. 'LEFT JOIN goods_suite gst ON gst.suite_no = so.single_spec_no and gst.deleted = 0 '
				. 'LEFT JOIN cfg_logistics cl ON cl.logistics_id = so.logistics_id '
				. 'LEFT JOIN cfg_warehouse cw ON cw.warehouse_id = so.warehouse_id '
				. 'LEFT JOIN stock_logistics_no sln ON sln.logistics_id = so.logistics_id and sln.logistics_no = so.logistics_no '
				. 'LEFT JOIN hr_employee he ON he.employee_id = st.checker_id '
				. 'JOIN (' . $sql_page . ') page ON page.id = so.stockout_id';
		return $sql;
	}
	/*private function stockoutSales(&$m,&$where_stockout_order,$where_sales_trade,$where_sales_trade_order,$order,$limit)
	{
	    $where_stockout_order['so.src_order_type'] = array('eq',1);
	    if(isset($where_stockout_order['so.status']))
	    {
	        $where_stockout_order['so.status'] = array(array('EGT',55),$where_stockout_order['so.status']);
	    }else{
	        $where_stockout_order['so.status'] = array('EGT',55);
	    }
	    if(!empty($where_sales_trade))
	    {
	        $m = $m->join('sales_trade st on  so.src_order_id = st.trade_id')->where($where_sales_trade);
	    }
	    if(!empty($where_sales_trade_order))
	    {
	        $m = $m->join('sales_trade_order sto on so.src_order_id = sto.trade_id')->where($where_sales_trade_order);
	    }
	    $m = $m->where($where_stockout_order);
	    $page = clone $m;
	    $sql_page = $page->field('so.stockout_id id')->order($order)->group('so.stockout_id')->limit($limit)->fetchSql(true)->select();
	    $cfg_show_telno=get_config_value('show_number_to_star',1);
	    $sql ='SELECT so.stockout_id id, so.src_order_no, so.flag_id,so.consign_status, so.stockout_no, so.status, '
	        .'so.goods_count,so.goods_type_count,so.receiver_address,' //so.freeze_reason,so.operator_id,
	        .'so.receiver_name,so.receiver_area,IF('.$cfg_show_telno.'=0,so.receiver_mobile,INSERT( so.receiver_mobile,4,4,\'****\')) receiver_mobile,IF('.$cfg_show_telno.'=0,so.receiver_telno,INSERT(so.receiver_telno,4,4,\'****\')) receiver_telno,so.receiver_zip,'
	        .'so.goods_count goods_total_cost,so.calc_post_cost,so.post_cost,so.calc_weight,'
	        .'so.weight,so.has_invoice,so.block_reason,'//so.package_id packager_id,so.watcher_id,so.printer_id,
	        .'so.logistics_no,so.consign_time,'//so.picker_id,so.examiner_id,so.batch_no,,so.picklist_no,so.outer_no
	        .'so.warehouse_type,st.trade_time,st.pay_time,st.buyer_nick,'
	        .'so.src_order_type,cl.logistics_type,'//隐藏字段
	        .'st.trade_type,st.platform_id,'//st.salesman_id,st.fchecker_id,st.checker_id,
	        .'sw.name warehouse_id,'
	        .'cl.logistics_name logistics_id,'
	        .'cs.shop_name shop_id'
	        .' FROM stockout_order so '
	        .' left JOIN sales_trade st '
	        .' ON so.src_order_id = st.trade_id '
	        .' LEFT JOIN cfg_warehouse sw ON sw.warehouse_id = so.warehouse_id'
	        .' LEFT JOIN cfg_logistics cl ON cl.logistics_id = so.logistics_id'
	        .' LEFT JOIN cfg_shop cs ON cs.shop_id = st.shop_id'
	        .' JOIN ('.$sql_page.') page on page.id = so.stockout_id'
	        .' order by '.$order;
	    return $sql;
	}
	*/
	public function getStockoutOrderDetailPrintData($id_list)
	{
        $point_number = get_config_value('point_number',0);
		$point_number = intval($point_number);
        $orderFlag = D('Stock/StockoutOrderDetail')->saveGoodsOrder();
        $orderFlag = empty($orderFlag['data'])&&!is_numeric($orderFlag['data'])?'0':$orderFlag['data'];
        $orderFlag = intval($orderFlag);
		$print_price_point_num = D('Stock/StockoutOrderDetail')->savePrintPointNum();
		$print_price_point_num = empty($print_price_point_num['data'])&&!is_numeric($print_price_point_num['data'])?'4':$print_price_point_num['data'];
        $show_sql = "CAST(SUM(sod.num) AS DECIMAL(19,".$point_number.")) as num";
        $suite_num = "CAST(SUM(sto.suite_num) AS DECIMAL(19,".$point_number.")) as suite_num";
		$price = 'CAST(sto.price AS DECIMAL(19,'.$print_price_point_num.')) price';
		$order_price = 'CAST(sto.order_price AS DECIMAL(19,'.$print_price_point_num.')) order_price';
		$discount = 'CAST(sto.discount AS DECIMAL(19,'.$print_price_point_num.')) discount';
        $price_amount = "CAST(sod.price * SUM(sod.num) AS DECIMAL(19,".$print_price_point_num.")) as goods_amount";
        switch ($orderFlag){
            case 0:
                $order_sql = " convert(sod.goods_name USING gbk) ";
                break;
            case 1:
                $order_sql = " sto.gift_type,convert(sod.goods_name USING gbk) ";
                break;
            case 2:
                $order_sql = " convert(cwp.position_no USING gbk) ";
                break;
            default:
                break;
        }
		$sql = "SELECT  sod.stockout_id AS id,sod.goods_name,so.src_order_no,so.package_count,sto.gift_type,sod.goods_id,IF(sod.spec_name is NULL or sod.spec_name ='','无规格名',sod.spec_name) spec_name ,".$show_sql.",".$price.",".$order_price.",".$discount.",gs.spec_no,gs.spec_id,gs.prop1,gs.prop2,gs.prop3,gs.prop4,IF(gs.img_url = '',gs.img_url,IF(locate('http',gs.img_url),gs.img_url,concat('http://img10.360buyimg.com/n0/',gs.img_url))) img_url,".$price_amount.",gb.brand_name AS goods_brand,gg.goods_no,gg.remark,cgu.name AS unit_name,IF(gg.short_name is NULL or gg.short_name ='','无简称',gg.short_name) short_name,IF(gs.spec_code is NULL or gs.spec_code = '','无规格码',gs.spec_code) spec_code,sto.suite_name,".$suite_num.",sto.suite_id,(CASE  WHEN ss.last_position_id THEN cwp.position_no  ELSE cwp2.position_no END) AS position_no,IF(gs.barcode is NULL or gs.barcode='','无条码',gs.barcode) as barcode
 				 FROM stockout_order_detail sod 
 				 left join sales_trade_order sto on sto.rec_id = sod.src_order_detail_id
 				 left join goods_suite gst on gst.suite_id = sto.suite_id
				 left join goods_spec gs on gs.spec_id = sod.spec_id  
				 left join goods_goods gg on gg.goods_id = gs.goods_id 
				 left join cfg_goods_unit cgu on cgu.rec_id = gg.unit 
				 left join goods_brand gb on gb.brand_id = gg.brand_id
				 left join stockout_order so on so.stockout_id = sod.stockout_id 
				 LEFT JOIN stock_spec ss ON(gs.spec_id=ss.spec_id AND ss.warehouse_id=so.warehouse_id)
				 LEFT JOIN stock_spec_position ssp ON(gs.spec_id=ssp.spec_id AND ssp.warehouse_id=so.warehouse_id)
				 LEFT JOIN cfg_warehouse_position cwp ON(ss.last_position_id = cwp.rec_id)
				 LEFT JOIN cfg_warehouse_position cwp2 ON(cwp2.rec_id = -so.warehouse_id)
				 WHERE sod.stockout_id IN (%s)
				 GROUP BY  sod.spec_id,IF(gst.is_print_suite = 0,null,sto.suite_id),sod.stockout_id
				 ORDER BY ".$order_sql;
		try {
			$data = $this->query($sql, $id_list);
		} catch (\PDOException $e) {
			\Think\Log::write($e->getMessage());
			$data = array('total' => 0, 'rows' => array());
		}
		return $data;
	}
    public function getPickListOrderDetailPrintData($id_list)
    {
        $point_number = get_config_value('point_number',0);
		$point_number = intval($point_number);
        $orderFlag = D('Stock/StockoutOrderDetail')->saveGoodsOrder();
        $orderFlag = empty($orderFlag['data'])&&!is_numeric($orderFlag['data'])?'0':$orderFlag['data'];
        $orderFlag = intval($orderFlag);
        $print_price_point_num = D('Stock/StockoutOrderDetail')->savePrintPointNum();
        $print_price_point_num = empty($print_price_point_num['data'])&&!is_numeric($print_price_point_num['data'])?'4':$print_price_point_num['data'];
        $show_sql = "CAST(SUM(sod.num) AS DECIMAL(19,".$point_number.")) as num";
        $suite_num = "CAST(sto.suite_num AS DECIMAL(19,".$point_number.")) as suite_num";
        $price = 'CAST(sto.price AS DECIMAL(19,'.$print_price_point_num.')) price';
        $price_amount = "CAST(sod.price * SUM(sod.num) AS DECIMAL(19,".$print_price_point_num.")) as goods_amount";
        $orderFlag?$order_sql =" sto.gift_type,convert(sod.goods_name USING gbk) ":$order_sql = " convert(sod.goods_name USING gbk) ";

        //分拣单货品分布情况
        $seq_sql = "FIND_IN_SET(sod.stockout_id,'".$id_list."')";
        $distribute_sql = "GROUP_CONCAT(CONCAT('[',$seq_sql,']x',sod.num) ORDER BY $seq_sql) as distribution";

        $sql = "SELECT sod.goods_name,".$distribute_sql.",so.src_order_no,so.package_count,sto.gift_type,sod.goods_id,IF(sod.spec_name is NULL or sod.spec_name ='','无规格名',sod.spec_name) spec_name ,".$show_sql.",".$price.",gs.spec_no,gs.spec_id,gs.prop1,gs.prop2,gs.prop3,gs.prop4,".$price_amount.",gb.brand_name AS goods_brand,gg.goods_no,IF(gg.short_name is NULL or gg.short_name ='','无简称',gg.short_name) short_name,IF(gs.spec_code is NULL or gs.spec_code = '','无规格码',gs.spec_code) spec_code,sto.suite_name,".$suite_num.",sto.suite_id, (CASE  WHEN ss.last_position_id THEN cwp.position_no  ELSE cwp2.position_no END) AS position_no,IF(gs.barcode is NULL or gs.barcode='','无条码',gs.barcode) as barcode
 				 FROM stockout_order_detail sod 
 				 left join sales_trade_order sto on sto.rec_id = sod.src_order_detail_id 
				 left join goods_spec gs on gs.spec_id = sod.spec_id  
				 left join goods_goods gg on gg.goods_id = gs.goods_id 
				 left join goods_brand gb on gb.brand_id = gg.brand_id
				 left join stockout_order so on so.stockout_id = sod.stockout_id 
				 LEFT JOIN stock_spec ss ON(gs.spec_id=ss.spec_id AND ss.warehouse_id=so.warehouse_id)
				 LEFT JOIN stock_spec_position ssp ON(gs.spec_id=ssp.spec_id AND ssp.warehouse_id=so.warehouse_id)
				 LEFT JOIN cfg_warehouse_position cwp ON(ss.last_position_id = cwp.rec_id)
				 LEFT JOIN cfg_warehouse_position cwp2 ON(cwp2.rec_id = -so.warehouse_id)
				 WHERE sod.stockout_id IN (%s)
				 GROUP BY sod.spec_id,sto.suite_id
				 ORDER BY ".$order_sql;
        try {
            $data = $this->query($sql, $id_list);
        } catch (\PDOException $e) {
            \Think\Log::write($e->getMessage());
            $data = array('total' => 0, 'rows' => array());
        }
        return $data;
    }
    //  platform_id  来自于cfg_shop 因为手工建单时platform_id为0
    /*IF(st.platform_id=0 AND ( IF(st.src_tids is NULL,1,0) OR IF(TRIM(st.src_tids)='',1,0)),st.trade_no,IF(st.platform_id <> 1 AND st.platform_id <>3,st.trade_no,st.src_tids)) AS src_tids,
    src_tids来源单的查询逻辑是  如果是线下订单 则回传系统订单号trade_no ,如果是线上订单为淘宝订单则回传 trade_no 字段*/
	public function getStockoutInfoBeforeApplyWaybill($stockout_ids,$is_group = false)
	{
	    $sql = "SELECT so.stockout_id,so.stockout_no,so.package_count,so.warehouse_id,so.warehouse_type,so.src_order_id,so.src_order_no,so.logistics_no,"
	        . " so.receiver_area,so.receiver_name,so.receiver_address,so.receiver_mobile,so.receiver_telno,so.receiver_province,so.receiver_city,so.receiver_district,so.receiver_zip,so.receiver_dtb,"
	         . " so.calc_weight,so.stockout_no,"
	         . " sod.goods_name,sod.num,"
	         . " st.shop_id,IF(st.platform_id=0 AND ( IF(st.src_tids is NULL,1,0) OR IF(TRIM(st.src_tids)='',1,0)),st.trade_no,IF(st.platform_id <> 1 AND st.platform_id <>3,st.trade_no,st.src_tids)) AS src_tids,st.delivery_term,st.cod_amount,st.trade_type,"//st.platform_id
	         . " ss.app_key,ss.platform_id,ss.contact shop_contact,ss.mobile shop_mobile,ss.telno shop_telno,"
	         . " sw.contact AS sender_name, sw.address AS sender_address, sw.telno AS sender_telno,sw.mobile AS sender_mobile, sw.zip AS sender_zip,sw.province AS sender_province,sw.city AS sender_city,sw.district AS sender_district"
	         . " FROM stockout_order so"
	         . " LEFT JOIN sales_trade st ON st.trade_id = so.src_order_id"
	         . " LEFT JOIN stockout_order_detail sod ON so.stockout_id = sod.stockout_id"
	         . " LEFT JOIN cfg_shop ss ON ss.shop_id = st.shop_id"
	         . " LEFT JOIN cfg_warehouse sw ON sw.warehouse_id = so.warehouse_id"
	         . " WHERE so.stockout_id in(%s)";
        if($is_group){
            $sql = $sql. " GROUP BY so.stockout_id";
        }
		$res = $this->query($sql, array($stockout_ids));
		return $res;
	}

	public function queryWayBillInfo($stockout_ids)
	{
		$res = $this->field('stockout_id,stockout_no,logistics_no')->where(array('stockout_id' => array('in', $stockout_ids)))->select();
		return $res;
	}

	public function updatePrintStatus($stockoutIdList, $field, $status)
	{
		$condition['stockout_id'] = array('in', $stockoutIdList);
		$data[$field] = $status;
		try {
			$this->where($condition)->save($data);
		} catch (\PDOException $e) {
			\Think\Log::write($e->getMessage());
			return false;
		}
		return true;
	}

	public function loadSelectedData($id)
	{
		$id = intval($id);
		try {
			$result = $this->where('stockout_id = ' . $id)->field('src_order_type,status,post_cost')->select();
		} catch (\PDOException $e) {
			$msg = $e->getMessage();
			\Think\Log::write($msg);
			return 0;
		}

		$src_order_type = $result[0]["src_order_type"];
		$status = $result[0]["status"];

		if (($src_order_type != 7 && $src_order_type != 3 )|| $status == 5) {
			return 1;
		}

		try {
			$point_number = get_config_value('point_number',0);
			$point_number = intval($point_number);
			$num = "CAST(sod.num AS DECIMAL(19,".$point_number.")) num";
			$data = $this->query("SELECT distinct sod.spec_id AS id,CAST(IFNULL(ss.stock_num,0) AS DECIMAL(19,4)) stock_num,so.src_order_type,so.src_order_id,so.src_order_no,so.post_cost,sod.total_amount,sod.cost_price AS price,
                          so.logistics_no,so.logistics_id,so.remark as form_remark,so.stockout_no, sod.goods_name, sod.spec_name,so.warehouse_id,
                          sod.spec_code,gs.barcode,sod.spec_no,gg.brand_id,".$num.",
                          sod.base_unit_id,sod.remark,gg.goods_id,gs.spec_id,
			              cgu.name unit_name,
			              gb.brand_name,
			              IF(ssp.position_id,cwp2.position_no,cwp.position_no) position_no
                          FROM stockout_order_detail AS sod
                          LEFT JOIN goods_goods AS gg on sod.goods_id = gg.goods_id
                          LEFT JOIN stockout_order AS so ON so.stockout_id = sod.stockout_id
                          LEFT JOIN goods_spec AS gs ON gs.spec_id = sod.spec_id
                          LEFT JOIN cfg_goods_unit as cgu ON cgu.rec_id = sod.base_unit_id
                          LEFT JOIN stock_spec_position as ssp ON ssp.spec_id = gs.spec_id and ssp.warehouse_id = so.warehouse_id
                          LEFT JOIN stock_spec as ss ON ss.spec_id = gs.spec_id and ss.warehouse_id = so.warehouse_id
                          LEFT JOIN cfg_warehouse_position as cwp ON cwp.rec_id = -so.warehouse_id
                          LEFT JOIN cfg_warehouse_position as cwp2 ON cwp2.rec_id = ssp.position_id
			              LEFT JOIN goods_brand as gb ON gb.brand_id = gg.brand_id
			              WHERE sod.stockout_id = %d",$id);
		} catch (\Exception $e) {
			$msg = $e->getMessage();
			\Think\Log::write($msg);
			return 0;
		}

		return $data;
	}

	public function updataStockOrder($search, $rows)
	{
		$operator_id = get_operator_id();
		$result['info'] = "";
		$result['status'] = '2';
		if ($search['stockout_no'] != null) {
			//编辑出库单
			try {
				$this->startTrans();

				$stockout_info = $this->fetchSql(false)->alias('soo')->field("soo.stockout_id, soo.status,cw.warehouse_id,cw.name")->join('left join cfg_warehouse cw on cw.warehouse_id = soo.warehouse_id')->where(array('soo.stockout_no'=>$search['stockout_no']))->select();
				$stockout_id = $stockout_info[0]['stockout_id'];
				$warehouse_id = $stockout_info[0]['warehouse_id'];

				$sql_old_stockout_data = $this->query("SELECT logistics_id, logistics_no,post_cost,remark FROM stockout_order WHERE stockout_no = '%s'",$search['stockout_no']);
				$old_stockout_data = $sql_old_stockout_data[0];
				$soo_updata_data = array(
				    'logistics_id'=>$search['logistics_id'],
				    'warehouse_id'=>$warehouse_id,
				    'logistics_no'=>$search['logistics_no'],
				    'post_cost'=>$search['post_fee'],
				    'operator_id'=>$operator_id,
				    'remark'=>$search['remark']
				);
				$this->where(array('stockout_id'=>$stockout_id))->save($soo_updata_data);

					$upate_msg = "编辑出库单";
				//插入返回编辑日志
				$tmp_data = array(
					"order_type"=>2,
					"order_id"=>$stockout_id,
					"operator_id"=>$operator_id,
					"operate_type"=>13,
					"message"=>$upate_msg,
				);
				D("StockInoutLog")->add($tmp_data);
				unset($tmp_data);

				if ($old_stockout_data['logistics_id'] != $search['logistics_id']) {
					$logistics = UtilDB::getCfgList(array('logistics'));
					$logistics = $logistics['logistics'];
					$len = count($logistics);
					for ($i = 0; $i < $len; $i++) {
						if ($logistics[$i]['id'] == $old_stockout_data['logistics_id']) {
							$old_logistics = $logistics[$i]['name'];
						};
						if ($logistics[$i]['id'] == $search['logistics_id']) {
							$new_logistics = $logistics[$i]['name'];
						};
					}
					$msg['logistics_id'] = "物流公司：由[" . $old_logistics . "]变为[" . $new_logistics . "];";
				} else {
					$msg['logistics_id'] = "";
				};
				if ($old_stockout_data['logistics_no'] != $search['logistics_no']) {
					$msg['logistics_no'] = "物流单号：由[" . $old_stockout_data['logistics_no'] . "]变为[" . $search['logistics_no'] . "];";
				} else {
					$msg['logistics_no'] = "";
				};
				if ($old_stockout_data['post_cost'] != $search['post_fee']) {
					$msg['post_cost'] = "邮费：由[" . $old_stockout_data['post_cost'] . "]变为[" . $search['post_fee'] . "];";
				} else {
					$msg['post_fee'] = "";
				};
				if ($old_stockout_data['remark'] != $search['remark']) {
					$msg['remark'] = "备注：由[" . $old_stockout_data['remark'] . "]变为[" . $search['remark'] . "];";
				} else {
					$msg['remark'] = "";
				};

				$stockout_msg = implode("", $msg);
				if (!empty($stockout_msg)) {

					$tmp_data = array(
						"order_type"=>2,
						"order_id"=>$stockout_id,
						"operator_id"=>$operator_id,
						"operate_type"=>13,
						"message"=>$stockout_msg,
					);
					D("StockInoutLog")->add($tmp_data);
					unset($tmp_data);
				}

				//删除stockout_order_detail表
				foreach ($rows['del_spec'] as $key => $value) {
					$value = (array)$value;
					$this->execute("DELETE FROM stockout_order_detail WHERE stockout_id = %d AND spec_id = %d",$stockout_id,$value['spec_id']);

					$tmp_data = array(
						"order_type"=>2,
						"order_id"=>$stockout_id,
						"operator_id"=>$operator_id,
						"operate_type"=>13,
						"message"=>"删除单品，商家编码:".$value['spec_no'],
					);
					D("StockInoutLog")->add($tmp_data);
					unset($tmp_data);
				}

				//更新stockout_order_detail表
				foreach ($rows['update_spec'] as $key => $value) {
					$value = (array)$value;
					$exist = $this->query("SELECT gs.spec_no,sod.spec_id,sod.num,sod.cost_price,sod.remark FROM stockout_order_detail sod left join goods_spec gs on gs.spec_id = sod.spec_id WHERE sod.stockout_id = %d AND sod.spec_id = %d",$stockout_id,$value['spec_id']);
					if (empty($exist)) {
					    $sood_insert_data = array(
					       'stockout_id'=>$stockout_id,
					        'src_order_type'=>7,
					        'base_unit_id'=>$value['base_unit_id'],
					        'num'=>$value['num'],
					        'cost_price'=>$value['price'],
					        'total_amount'=>$value['total_amount'],
					        'goods_name'=>$value['goods_name'],
					        'goods_id'=>$value['goods_id'],
					        'goods_no'=>$value['goods_no'],
					        'spec_name'=>$value['spec_name'],
					        'spec_id'=>$value['spec_id'],
					        'spec_no'=>$value['spec_no'],
					        'spec_code'=>$value['spec_code'],
					        'remark'=>$value['remark'],
					        'created'=>array('exp','NOW()'),
					        'src_order_detail_id'=>0,
					        
					    );
					    $res_sood_insert = D('Stock/StockoutOrderDetail')->add($sood_insert_data);
						/* $sood_sql = "INSERT INTO stockout_order_detail(stockout_id,src_order_type,base_unit_id,num,cost_price,total_amount,goods_name,goods_id,goods_no,spec_name,spec_id,spec_no,spec_code,remark,created,src_order_detail_id) VALUES (\"{$stockout_id}\",7,{$value['base_unit_id']}, {$value['num']}, {$value['price']}, {$value['total_amount']},\"{$value['goods_name']}\",{$value['goods_id']},\"{$value['goods_no']}\", \"{$value['spec_name']}\", \"{$value['spec_id']}\", \"{$value['spec_no']}\", \"{$value['spec_code']}\",\"{$value['remark']}\",NOW(),0)";
						$this->execute($sood_sql); */

						$tmp_data = array(
							"order_type"=>2,
							"order_id"=>$stockout_id,
							"operator_id"=>$operator_id,
							"operate_type"=>13,
							"message"=>"增加单品，商家编码:".$value['spec_no'],
						);
						D("StockInoutLog")->add($tmp_data);
						unset($tmp_data);

					} else {
						$this->execute("UPDATE stockout_order_detail SET total_amount = %d,num = %d,cost_price = %d,remark = '%s' WHERE stockout_id = %d AND spec_id = %d",$value['total_amount'],$value['num'],$value['price'],$value['remark'],$stockout_id,$value['spec_id']);

						$msg_price = "";
						$msg_num = "";
						$msg_remark = "";
						if ((int)$value['num'] != (int)$exist[0]['num']) {
							$msg_num = "数量由[" . $exist[0]['num'] . "]变为[" . $value['num'] . "]";
						}
						if ($value['price'] != $exist[0]['cost_price']) {
							$msg_price = "价格由[" . $exist[0]['cost_price'] . "]变为[" . $value['price'] . "]";
						}
						if ($value['remark'] != $exist[0]['remark']) {
							$msg_remark = "备注由[" . $exist[0]['remark'] . "]变为[" . $value['remark'] . "]";
						}
						$msg = $exist[0]['spec_no'].':'.$msg_num .'-'. $msg_price .'-'. $msg_remark;

							$tmp_data = array(
								"order_type"=>2,
								"order_id"=>$stockout_id,
								"operator_id"=>$operator_id,
								"operate_type"=>13,
								"message"=>"修改单品，商家编码".$msg,
							);
							D("StockInoutLog")->add($tmp_data);
							unset($tmp_data);
						}
					}

				//更新出库单货品数量 和货品类型数量
				$sood_query_sql = "SELECT COUNT(spec_id) AS gcount,SUM(num) AS num,SUM(total_amount) AS tamount FROM stockout_order_detail WHERE stockout_id=\"{$stockout_id}\"";
				$result_sql = $this->query($sood_query_sql);
				$result_sql = $result_sql[0];
				$soo_updata_sql = "UPDATE stockout_order SET goods_count={$result_sql['num']},goods_type_count={$result_sql['gcount']},goods_total_amount={$result_sql['tamount']} WHERE stockout_id=\"{$stockout_id}\"";
				$this->execute($soo_updata_sql);

				//判断出库状态
//				if ($status == 110) {
//					//如果状态是已完成，更新成编辑中
//					$update_soo_status_spl = "UPDATE stockout_order SET status = 48 WHERE stockout_id = {$stockout_id}";
//					$this->execute($update_soo_status_spl);
//
//                    D('Common/SysProcessBackground')->stockoutChange($stockout_id);
//
//					$result['2edit'] = '1';
//
//					//更新货品库存数量
//					$status_sql = "SELECT so.status, so.src_order_type, so.src_order_no, so.stockout_no, sod.spec_id, sod.num FROM stockout_order AS so LEFT JOIN stockout_order_detail AS sod ON so.stockout_id = sod.stockout_id WHERE so.stockout_id = " . $stockout_id . " AND so.warehouse_id = " . $search['warehouse_id'];
//					$result_num = $this->query($status_sql);
//
//					for ($i = 0; $i < count($result_num); $i++) {
//						$spec_id = $result_num[$i]['spec_id'];
//						$num = (int)$result_num[$i]['num'];
//						$stock_num_sql = "SELECT stock_num FROM stock_spec WHERE spec_id = " . $spec_id . " AND warehouse_id =" . $search['warehouse_id'];
//						$stock_num = $this->query($stock_num_sql);
//						$stock_num = (int)$stock_num[0]['stock_num'];
//						$new_num = $stock_num + $num;
//						$stock_spec_sql = "UPDATE stock_spec AS ss SET ss.stock_num = {$new_num} WHERE ss.spec_id = " . $spec_id . " AND ss.warehouse_id =" . $search['warehouse_id'];
//						$this->execute($stock_spec_sql);
//					}
//				}

			} catch (\PDOException $e) {
				$msg = $e->getMessage();
				\Think\Log::write($msg);
				$result['info'] = self::PDO_ERROR;
				$result['status'] = '0';
				$this->rollback();
				return $result;
			} catch (\Exception $e) {
				$msg = $e->getMessage();
				\Think\Log::write($msg);
				$result['info'] = self::PDO_ERROR;
				$result['status'] = '0';
				$this->rollback();
				return $result;
			}
			$this->commit();
			return $result;
		}
	}

	public function addStockOrder($search, $rows, &$stockout_id)
	{
		$result['info'] = "";
		$result['status'] = '1';
		$operator_id = get_operator_id();
		try {
			$this->startTrans();

			$sql_get_no = 'select FN_SYS_NO("stockout") stockout_no';
			$res_stockout_order_no = $this->query($sql_get_no);
			$stockout_no = $res_stockout_order_no[0]['stockout_no'];
			$warehouse_info = UtilDB::getCfgList(array('warehouse'),array('warehouse'=>array('warehouse_id'=>$search['warehouse_id'])));
			if(empty($warehouse_info['warehouse'])){
				E('入库仓库不存在');
			}
			//把生成的出库单返回前台
			$result['info'] = $stockout_no;

			//插入出库单
			//todo-ken src_order_id 没有源单据插入0
			//todo-ken 默认插入到1号仓库
			$warehouse_status = D('Setting/Warehouse')->field('type')->where(array('warehouse_id'=>$search['warehouse_id']))->select();
			if((int)$warehouse_status[0]['type'] == 11){
				$order_status = 52;
			}else{
				$order_status = 48;
			}
				
			$soo_add_data = array(
			  'stockout_no'=>  $stockout_no,
			    'warehouse_id'=>$search['warehouse_id'],
			    'src_order_type'=>$search['stockout_type'],
			    'logistics_id'=>$search['logistics_id'],
			    'logistics_no'=>$search['logistics_no'],
			    'post_cost'=>$search['post_fee'],
			    'operator_id'=>$operator_id,
			    'status'=>$order_status,
			    'remark'=>$search['remark'],
			    'created'=>array('exp','NOW()'),
			    'src_order_id'=>$search['src_order_id'],
				'src_order_no'=>$search['src_order_no']			
			);
			$res_soo_add = $this->add($soo_add_data);
			$stockout_id = $res_soo_add;
			/* $soo_sql = "INSERT INTO stockout_order(stockout_no,warehouse_id,src_order_type,logistics_id,logistics_no,post_cost,operator_id,`status`,remark,created,src_order_id)
	VALUES(\"{$stockout_no}\",{$search['warehouse_id']},{$search['stockout_type']},{$search['logistics_id']},\"{$search['logistics_no']}\",{$search['post_fee']},{$operator_id},48,\"{$search['remark']}\",NOW(),0)";
			$this->execute($soo_sql); */

			//插入出库单日志
			$stockout_id_sql = "SElECT stockout_id FROM stockout_order WHERE stockout_no = \"{$stockout_no}\"";
			$stockout_id = $this->query($stockout_id_sql);
			$stockout_id = $stockout_id[0]['stockout_id'];

			$tmp_data = array(
				"order_type"=>2,
				"order_id"=>$stockout_id,
				"operator_id"=>$operator_id,
				"operate_type"=>13,
				"message"=>"新建出库单",
			);
			D("StockInoutLog")->add($tmp_data);
			unset($tmp_data);

			//插入出库详情
			foreach ($rows as $key => $value) {
				$value = (array)$value;
				$sood_add_data = array(
				  'stockout_id'=>  $stockout_id,
				    'src_order_type'=>$search['stockout_type'],
				    'base_unit_id'=>$value['base_unit_id'],
				    'num'=>$value['num'],
				    'cost_price'=>$value['price'],
				    'total_amount'=>$value['total_amount'],
				    'goods_name'=>$value['goods_name'],
				    'goods_id'=>$value['goods_id'],
				    'goods_no'=>$value['goods_no'],
				    'spec_name'=>$value['spec_name'],
				    'spec_id'=>$value['spec_id'],
				    'spec_no'=>$value['spec_no'],
				    'spec_code'=>$value['spec_code'],
				    'remark'=>$value['remark'],
				    'created'=>array('exp','NOW()'),
				    'src_order_detail_id'=>0
				);
				$res_sood_add = D('Stock/StockoutOrderDetail')->add($sood_add_data);
				/* $sood_sql = "INSERT INTO stockout_order_detail(stockout_id,src_order_type,base_unit_id,num,cost_price,total_amount,goods_name,goods_id,goods_no,spec_name,spec_id,spec_no,spec_code,remark,created,src_order_detail_id) VALUES (\"{$stockout_id}\",{$search['stockout_type']},{$value['base_unit_id']}, {$value['num']}, {$value['price']}, {$value['total_amount']},\"{$value['goods_name']}\",{$value['goods_id']},\"{$value['goods_no']}\", \"{$value['spec_name']}\", \"{$value['spec_id']}\", \"{$value['spec_no']}\", \"{$value['spec_code']}\",\"{$value['remark']}\",NOW(),0)";
				$this->execute($sood_sql); */

				$tmp_data = array(
					"order_type"=>2,
					"order_id"=>$stockout_id,
					"operator_id"=>$operator_id,
					"operate_type"=>13,
					"message"=>"插入单品，商家编码".$value['spec_no'],
				);
				D("StockInoutLog")->add($tmp_data);
				unset($tmp_data);
			}

			//更新出库单货品数量 和货品类型数量
			$sood_query_sql = "SELECT COUNT(spec_id) AS gcount,SUM(num) AS num,SUM(total_amount) AS tamount FROM stockout_order_detail WHERE stockout_id=\"{$stockout_id}\"";
			$result_sql = $this->query($sood_query_sql);
			$result_sql = $result_sql[0];

			$soo_updata_sql = "UPDATE stockout_order SET goods_count={$result_sql['num']},goods_type_count={$result_sql['gcount']},goods_total_amount={$result_sql['tamount']} WHERE stockout_id=\"{$stockout_id}\"";
			$this->execute($soo_updata_sql);

		} catch (\PDOException $e) {
			$msg = $e->getMessage();
			\Think\Log::write($msg);
			$result['info'] = self::PDO_ERROR;
			$result['status'] = '0';
			$this->rollback();
			return $result;
		} catch (\Exception $e) {
			$msg = $e->getMessage();
			\Think\Log::write($msg);
			$result['info'] = self::PDO_ERROR;
			$result['status'] = '0';
			$this->rollback();
			return $result;
		}
		$this->commit();
		return $result;
	}

	public function  showstockOutLogData($rec_id){
		$stockout_id = $rec_id;
		$sql = "SELECT he.fullname, sil.message, sil.created FROM stock_inout_log AS sil LEFT JOIN hr_employee he ON he.employee_id = sil.operator_id
WHERE sil.order_type = '2' AND sil.order_id = {$stockout_id} ORDER BY sil.created DESC";
		try {
			$data = $this->query($sql);
		} catch (\PDOException $e) {
			$msg = $e->getMessage();
			\Think\Log::write($msg);
			return 1;
		}
		return $data;
	}

	public function showstockOutDetailData($rec_id){
		$stockout_id = intval($rec_id);
		$page_info = I('','',C('JSON_FILTER'));
		$limit = '';
		if(isset($page_info['page']))
		{
			$page = intval($page_info['page']);
			$rows = intval($page_info['rows']);
			$limit=" limit ".($page - 1) * $rows . "," . $rows;//分页
		}
		$count = D('Stock/StockoutOrderDetail')->where(array('stockout_id'=>$stockout_id))->count();
		$page_sql = 'select sod.rec_id from stockout_order_detail sod where sod.stockout_id = '.intval($stockout_id).' '.$limit;
		$point_number = get_config_value('point_number',0);
		$point_number = intval($point_number);
		$num = "CAST(sod.num AS DECIMAL(19,".$point_number.")) num";
		$sql = "SELECT sod.rec_id,IFNULL(cwp.position_no,cwp2.position_no) position_no,sod.spec_id,sod.goods_id,sod.goods_name,sod.goods_no,sod.spec_code,sod.spec_name,sod.spec_no,gs.barcode,".$num.",sod.scan_type,sod.remark,gg.brand_id,sod.cost_price
        FROM stockout_order_detail sod
        JOIN ($page_sql) sod_l on sod_l.rec_id = sod.rec_id
        LEFT JOIN stockout_order so ON sod.stockout_id = so.stockout_id
        LEFT JOIN stock_spec_position ssp ON ssp.spec_id = sod.spec_id and ssp.warehouse_id = so.warehouse_id
        LEFT JOIN cfg_warehouse_position cwp ON cwp.rec_id = ssp.position_id
        LEFT JOIN cfg_warehouse_position cwp2 ON cwp2.rec_id = -so.warehouse_id
        LEFT JOIN goods_spec gs ON sod.spec_id = gs.spec_id
        LEFT JOIN goods_goods gg ON gs.goods_id = gg.goods_id
        WHERE sod.is_package=0 AND sod.stockout_id = \"{$stockout_id}\" ";//sod.weight,
		try {
			$data = $this->query($sql);
		} catch (\PDOException $e) {
			$msg = $e->getMessage();
			\Think\Log::write($msg);
			return 1;
		}
		return array('total'=>$count,'rows'=>$data);
	}

	public function deleteStockOutOrder($id = 0){
		$return['status'] = 0;
		$return['info'] = '取消成功!';
		$status_sql = "SELECT so.status, so.src_order_type, so.src_order_no FROM stockout_order AS so WHERE stockout_id = %d";
		try {
			$result = $this->query($status_sql,$id);
		} catch (\PDOException $e) {
			$msg = $e->getMessage();
			\Think\Log::write($msg);
			$return['status'] = 1;
			$return['info'] = self::PDO_ERROR;
			return $return;
		}

		$status = $result[0]['status'];

		if ($status != "48") {
			$return['status'] = 1;
			$return['info'] = "订单状态不正确!";
			return $return;
		}
		//更新出库单状态
		$operator_id = get_operator_id();
		//更新出库日志
		$this->startTrans();
		try {
			$this->execute(" UPDATE stockout_order SET status = 5 WHERE stockout_id = %d",$id);
//			$this->execute($updata_stockout_log_sql);
			$tmp_data = array(
				"order_type"=>2,
				"order_id"=>$id,
				"operator_id"=>$operator_id,
				"operate_type"=>13,
				"message"=>"取消出库单",
			);
			D("StockInoutLog")->add($tmp_data);
			unset($tmp_data);

		} catch (\PDOException $e) {
			$msg = $e->getMessage();
			\Think\Log::write($msg);
			$this->rollback();
			$return['status'] = 1;
			$return['info'] = self::PDO_ERROR;
			return $return;
		}
		$this->commit();
		return $return;
	}

	/**
	 * 此接口使用地方有:调拨管理提交,其他出库提交,库存导入等
	 * 注意:当前接口应该是一个公共的接口,但是由于去除考虑和代码设计调整等原因,导致代码复用性考虑不周,
	 * 		所以使得代码无法再接下来的接口中复用,且其中包含事务开启等语句也限制了其复用性
	 * 		新接口调整为checkStockout,去掉事务,利用异常层层抛出来实现代码的复用
	 * 		在其他接口需要处理有关出库逻辑的可以参考checkStockout接口,当前接口不再使用
	 *
	 * @param int $id
	 * @param string $handle_type
	 * @return mixed
	 */
	public function submitStockOutOrder($id = 0,$handle_type='other'){
		$return['status'] = 0;
		$return['info'] = '提交成功!';
		try {
			$result = $this->query("SELECT so.status, so.src_order_type,sod.remark, so.src_order_no, so.stockout_no, sod.spec_id, sod.num,sod.price,sod.src_order_detail_id FROM stockout_order AS so LEFT JOIN stockout_order_detail AS sod ON so.stockout_id = sod.stockout_id WHERE so.stockout_id = %d" ,$id);
		} catch (\PDOException $e) {
			$msg = $e->getMessage();
			\Think\Log::write($msg);
			$return['status'] = 1;
			$return['info'] = self::PDO_ERROR;
			return $return;
		}

		$status = $result[0]['status'];
		$stockout_no = $result[0]['stockout_no'];

		if ($status != "48") {
			$return['status'] = 1;
			$return['info'] ="订单状态不正确!";
			return $return;
		}
        if($handle_type=='other'){
            $this->startTrans();
        }
		$operator_id = get_operator_id();
        $type_map = C('stockout_type');
		try {
			$stockout_order_info = D('Stock/StockOutOrder')->getStockoutOrders(array('stockout_id','stockout_no','src_order_type','warehouse_id','src_order_id','src_order_no'),array('stockout_id'=>$id));
			$stockout_order_info = $stockout_order_info[0];
			switch($stockout_order_info['src_order_type']){
				case '2':
				{
					$transfer_info = D('Stock/StockTransfer')->getStockTransOrder(array('to_warehouse_id','from_warehouse_id','type'),array('rec_id'=>$stockout_order_info['src_order_id']));
					//-------更新 stock_transfer_detail 的出库数量 out_num
					$this->execute('UPDATE stock_transfer_detail st,stockout_order_detail sod
									SET st.out_num=st.out_num+sod.num,st.out_cost_total=st.out_cost_total+sod.total_amount
									WHERE st.rec_id=sod.src_order_detail_id AND sod.stockout_id=%d;',$stockout_order_info['stockout_id']);
					//----更新调拨单总出库数量
					$tansfer_goods_count = D('Stock/StockTransferDetail')->getDetailInfo(array('SUM(out_num)  as goods_out_count'),array('transfer_id'=>$stockout_order_info['src_order_id']));
					$update_transfer = D('Stock/StockTransfer')->where(array('rec_id'=>$stockout_order_info['src_order_id']))->save($tansfer_goods_count[0]);
					break;
				}
				case '3':
				{
                    $purchase_return_where = array('return_no'=>$result[0]['src_order_no']);
                    $status = D('Purchase/PurchaseReturn')->field(array('status'))->where($purchase_return_where)->select();
                    if($status[0]['status'] == 20){
                        SE('该出库单对应的采购退货单已驳回审核，请先审核采购退货单或者取消该出库单');
                    }

					$this->execute('UPDATE stock_spec ss,stockout_order_detail sod,purchase_return_detail srd SET
						ss.return_num=IF(srd.out_num+sod.num>srd.num,
						IF(srd.out_num>srd.num,ss.return_num-0,ss.return_num-srd.num+srd.out_num),IF(sod.num>ss.return_num,0,ss.return_num-sod.num))
						WHERE srd.rec_id=sod.src_order_detail_id AND ss.spec_id=sod.spec_id
						AND ss.warehouse_id='.$stockout_order_info['warehouse_id'].' AND sod.src_order_type=3 AND sod.stockout_id='.$stockout_order_info['stockout_id']);
					$this->execute('INSERT INTO purchase_return_detail(return_id,spec_id,num,num2,out_num,unit_id,unit_ratio,base_unit_id,price,out_amount,amount,remark)
							(
								SELECT '.$stockout_order_info['src_order_id'].',spec_id,0 AS num,0 AS num2, num ,unit_id,1 AS unit_ratio,base_unit_id,price ,total_amount,total_amount,CONCAT_WS(remark,"由出库单引入采购退货单") 
								FROM stockout_order_detail WHERE stockout_id = '.$stockout_order_info['stockout_id'].'
							)
							ON DUPLICATE KEY UPDATE purchase_return_detail.out_num = purchase_return_detail.out_num+VALUES(purchase_return_detail.out_num),
							purchase_return_detail.out_amount = purchase_return_detail.out_amount+VALUES(purchase_return_detail.out_amount);
							');
					
					$this->execute('UPDATE stockout_order_detail sod,purchase_return_detail prd SET sod.src_order_detail_id = prd.rec_id 
							WHERE sod.stockout_id = '.$stockout_order_info['stockout_id'].' AND sod.spec_id = prd.spec_id AND prd.return_id = '.$stockout_order_info['src_order_id']);
					$return_info = $this->query('SELECT SUM(IF(num-out_num<0,0,num-out_num)) as v_count,COUNT(DISTINCT spec_id) as v_spec_id,SUM(num) as v_num,SUM(out_num) as v_out_num  
									FROM purchase_return_detail WHERE return_id='.$stockout_order_info['src_order_id']);
					$this->execute('UPDATE purchase_return SET `status`=IF('.$return_info[0]['v_count'].'>0,50,90),goods_type_count = '.$return_info[0]['v_spec_id'].',
						goods_count = '.$return_info[0]['v_num'].',goods_out_count = '.$return_info[0]['v_out_num'].' WHERE return_id='.$stockout_order_info['src_order_id']);
					$this->execute('INSERT INTO purchase_return_log(return_id,operator_id,`type`,remark)
							VALUES('.$stockout_order_info['src_order_id'].','.$operator_id.',80,CONCAT("采购退货对应出库单审核:","'.$stockout_order_info['stockout_no'].'"))');
					}
				case '4':
				{
					$this->execute("UPDATE stock_spec ss,stockout_order_detail sod
									SET ss.last_pd_time=NOW()
									WHERE ss.spec_id=sod.spec_id AND ss.warehouse_id={$stockout_order_info['warehouse_id']} AND sod.stockout_id={$stockout_order_info['stockout_id']};");

					$pd_sod_info = D('Stock/StockoutOrderDetail')->alias('sod')->field("sod.spec_id,{$stockout_order_info['warehouse_id']} as warehouse_id,NOW() as last_pd_time,IFNULL(ssp.position_id,-{$stockout_order_info['warehouse_id']}) as position_id")->join('stock_spec_position ssp on ssp.spec_id = sod.spec_id and ssp.warehouse_id = '.$stockout_order_info['warehouse_id'])->where(array('sod.stockout_id'=>$stockout_order_info['stockout_id']))->select();
					$for_update_data = array('last_pd_time'=>array('exp',"VALUES(last_pd_time)"));
					D('Stock/StockSpecPosition')->addAll($pd_sod_info,array(),$for_update_data);
				}
			}
			$re = $this->query("SELECT warehouse_id FROM stockout_order WHERE stockout_id = %d",$id);
			$warehouse_id = $re[0]['warehouse_id'];
			$this->execute("UPDATE stockout_order AS so SET so.status = 110,consign_time = NOW() WHERE so.stockout_id = %d",$id);

            D('Common/SysProcessBackground')->stockoutChange($id);

			$tmp_data = array(
				"order_type"=>2,
				"order_id"=>$id,
				"operator_id"=>$operator_id,
				"operate_type"=>19,
				"message"=>"完成出库单:".$stockout_no,
			);
			D("StockInoutLog")->add($tmp_data);
			unset($tmp_data);
			$stockout_change_history = array();
			$order_check_no_stock_stockout = get_config_value('order_check_no_stock_stockout',0);
			for ($i = 0; $i < count($result); $i++) {
				$spec_id = $result[$i]['spec_id'];
				$num = floatval($result[$i]['num']);
				$stock_num_sql = "SELECT ss.stock_num,ss.rec_id,ss.cost_price,gs.is_allow_neg_stock FROM stock_spec ss left join goods_spec gs on gs.spec_id=ss.spec_id  WHERE ss.spec_id = {$spec_id} AND ss.warehouse_id = {$warehouse_id}" ;
				$stock_num_result = $this->query($stock_num_sql);
                $stock_spec_id = $stock_num_result[0]['rec_id'];
				$stock_num = floatval($stock_num_result[0]['stock_num']);
				$new_num = $stock_num - $num;

				if(!(int)$stock_num_result[0]['is_allow_neg_stock']&&$order_check_no_stock_stockout!=0){
					if($new_num < 0){
						$return['status'] = 1;
						$return['info'] ="不允许负库存出库！";
						$this->rollback();
						return $return;
					}
				}
                $stock_spec_log_message = $type_map["{$result[0]['src_order_type']}"];
				D('Stock/StockSpecLog')->add(array('operator_type'=>3,'operator_id'=>$operator_id,'stock_spec_id'=>$stock_spec_id,'message'=>$stock_spec_log_message.'-'.$result[0]['stockout_no'],'stock_num'=>$stock_num,'num'=>$num));
				$this->execute("UPDATE `stock_spec` SET `stock_num`='%s' WHERE `spec_id` = %d AND `warehouse_id` = %d",$new_num,$spec_id,$warehouse_id);
			
				$stockout_change_history[] = array(
					'src_order_type' => $stockout_order_info['src_order_type'],
					'stockio_id'   => $id,
					'stockio_no'   =>$stockout_no,
					'src_order_id' => empty($stockout_order_info['src_order_id'])?0:$stockout_order_info['src_order_id'],
					'src_order_no' =>empty($stockout_order_info['src_order_no'])?"":$stockout_order_info['src_order_no'],
					'stockio_detail_id' =>empty($result[$i]['src_order_detail_id'])?0:$result[$i]['src_order_detail_id'],
					'spec_id'  =>$result[$i]['spec_id'],
					'warehouse_id' =>$warehouse_id,
					'type' =>2,
					'cost_price_old' =>$stock_num_result[0]['cost_price'],
					'stock_num_old' =>$stock_num_result[0]['stock_num'],
					'price' =>$result[$i]['price'],
					'num' =>$num,
					'amount' =>$result[$i]['price']*$num,
					'cost_price_new' =>$stock_num_result[0]['cost_price'],
					'stock_num_new' =>$new_num,
					'operator_id' =>$operator_id,
					'created' =>array('exp', 'NOW()'),
					
				);
			}
			D('Stock/StockChangeHistory')->insertStockChangeHistory($stockout_change_history);
			$ss_position_info = D('Stock/StockoutOrderDetail')->alias('sod')->field(array("ss.warehouse_id","sod.spec_id","NOW() as last_inout_time",'cwz.zone_id','IF(ssp.position_id IS NULL,-ss.warehouse_id,ssp.position_id) as position_id','IF(ssp.position_id IS NULL,ss.stock_num,-sod.num) as stock_num','IF(ssp.position_id IS NULL,-ss.warehouse_id,ssp.position_id) as last_position_id'))->join('left join stock_spec ss on ss.spec_id = sod.spec_id and ss.warehouse_id='.$warehouse_id)->join('left join stock_spec_position ssp on ssp.spec_id= sod.spec_id and ssp.warehouse_id='.$warehouse_id)->join('left join cfg_warehouse_position cwp on cwp.rec_id = ssp.position_id')->join('left join cfg_warehouse_zone cwz on cwz.warehouse_id = ss.warehouse_id')->where(array("sod.stockout_id"=>$stockout_order_info['stockout_id']))->select();
			$stock_spec_position_up = array(
				'stock_num'=>array('exp',"stock_num+VALUES(stock_num)"),
				"last_inout_time"=>array('exp','NOW()'),
			);
			$res_ss_p = D('Stock/StockSpecPosition')->addAll($ss_position_info,'',$stock_spec_position_up);
			$res_ss_u = D('Stock/StockSpec')->addAll($ss_position_info,'',array('last_position_id'=>array('exp','VALUES(last_position_id)')));

		}catch (\PDOException $e) {
		    if($handle_type=='other'){
		       $this->rollback();
		    }
			
			$msg = $e->getMessage();
			\Think\Log::write($msg, \Think\Log::ERR, '');
			$return['status'] = 1;
			$return['info'] =self::PDO_ERROR;
			return $return;
		}catch (\Exception $e) {
		    if($handle_type=='other'){
		       $this->rollback();
		    }
		    
			$msg = $e->getMessage();
			\Think\Log::write($msg, \Think\Log::ERR, '');
			$return['status'] = 1;
			$return['info'] =$msg;
			return $return;
		}
		if($handle_type=='other'){
		   $this->commit();
		}
		
		$return['data']=array(
		    'status'=>110,
		    'consign_time'=>date("Y-m-d H:i:s"),
		    'id' =>$id
		
		);
		return $return;
		
	}

	/**
	 * 本接口为重写submitStockOutOrder方法的方法
	 * 1.去除了事务,提高了代码的服用性
	 * 2.去除了返回结果,通过异常层层上抛的来达到异常信息捕获来达到代码的复用性
	 * 3.修改相关sql语句为链式操作
	 * 4.整理相关处理逻辑的顺序,添加盘点处理分支
	 *
	 * @param $stockout_id
	 * @throws BusinessLogicException
	 */
	public function checkStockout($stockout_id)
	{
		try{
			$error_info = '';
			$fields = array('so.status','so.stockout_id', 'so.src_order_type', 'so.src_order_id', 'so.src_order_no', 'so.stockout_no','so.warehouse_id');
			$where 	= array('stockout_id'=>$stockout_id);
			$stockout_info	= $this->alias('so')->field($fields)->where($where)->find();

			$operator_id 	= get_operator_id();
			if(empty($stockout_info))
			{
				SE('出库单不存在!');
			}
			$status 		= $stockout_info['status'];
			$stockout_no 	= $stockout_info['stockout_no'];
            $type_map =  C('stockout_type');
            $stock_spec_log_message =$type_map["{$stockout_info['src_order_type']}"].'-'.$stockout_info['stockout_no'];
			if( 48 != $status )
			{
				SE('出库单状态不正确!');
			}
			//-----------根据出库单类型,处理跟出库类型有关的
			switch($stockout_info['src_order_type'])
			{
				case '2'://调拨
				{
					$transfer_info = D('Stock/StockTransfer')->getStockTransOrder(array('to_warehouse_id','from_warehouse_id','type'),array('rec_id'=>$stockout_info['src_order_id']));
					//-------更新 stock_transfer_detail 的出库数量 out_num
					$this->execute('UPDATE stock_transfer_detail st,stockout_order_detail sod
									SET st.out_num=st.out_num+sod.num,st.out_cost_total=st.out_cost_total+sod.total_amount
									WHERE st.rec_id=sod.src_order_detail_id AND sod.stockout_id=%d;',$stockout_info['stockout_id']);
					//----更新调拨单总出库数量
					$tansfer_goods_count = D('Stock/StockTransferDetail')->getDetailInfo(array('SUM(out_num)  as goods_out_count'),array('transfer_id'=>$stockout_info['src_order_id']));
					$update_transfer = D('Stock/StockTransfer')->where(array('rec_id'=>$stockout_info['src_order_id']))->save($tansfer_goods_count[0]);
					break;
				}
				case '3':
				{
					$this->execute('UPDATE stock_spec ss,stockout_order_detail sod,purchase_return_detail srd SET
						ss.return_num=IF(srd.out_num+sod.num>srd.num,
						IF(srd.out_num>srd.num,ss.return_num-0,ss.return_num-srd.num+srd.out_num),IF(sod.num>ss.return_num,0,ss.return_num-sod.num))
						WHERE srd.rec_id=sod.src_order_detail_id AND ss.spec_id=sod.spec_id
						AND ss.warehouse_id='.$stockout_info['warehouse_id'].' AND sod.src_order_type=3 AND sod.stockout_id='.$stockout_info['stockout_id']);
					$this->execute('INSERT INTO purchase_return_detail(return_id,spec_id,num,num2,out_num,unit_id,unit_ratio,base_unit_id,price,out_amount,amount,remark)
							(
								SELECT '.$stockout_info['src_order_id'].',spec_id,0 AS num,0 AS num2, num ,unit_id,1 AS unit_ratio,base_unit_id,price ,total_amount,total_amount,CONCAT_WS(remark,"由出库单引入采购退货单") 
								FROM stockout_order_detail WHERE stockout_id = '.$stockout_info['stockout_id'].'
							)
							ON DUPLICATE KEY UPDATE purchase_return_detail.out_num = purchase_return_detail.out_num+VALUES(purchase_return_detail.out_num),
							purchase_return_detail.out_amount = purchase_return_detail.out_amount+VALUES(purchase_return_detail.out_amount);
							');
					
					$this->execute('UPDATE stockout_order_detail sod,purchase_return_detail prd SET sod.src_order_detail_id = prd.rec_id 
							WHERE sod.stockout_id = '.$stockout_info['stockout_id'].' AND sod.spec_id = prd.spec_id AND prd.return_id = '.$stockout_info['src_order_id']);
					$return_info = $this->query('SELECT SUM(IF(num-out_num<0,0,num-out_num)) as v_count,COUNT(DISTINCT spec_id) as v_spec_id,SUM(num) as v_num,SUM(out_num) as v_out_num  
									FROM purchase_return_detail WHERE return_id='.$stockout_info['src_order_id']);
					$this->execute('UPDATE purchase_return SET `status`=IF('.$return_info[0]['v_count'].'>0,50,90),goods_type_count = '.$return_info[0]['v_spec_id'].',
						goods_count = '.$return_info[0]['v_num'].',goods_out_count = '.$return_info[0]['v_out_num'].' WHERE return_id='.$stockout_info['src_order_id']);
					$this->execute('INSERT INTO purchase_return_log(return_id,operator_id,`type`,remark)
							VALUES('.$stockout_info['src_order_id'].','.$operator_id.',80,CONCAT("采购退货对应出库单审核:","'.$stockout_info['stockout_no'].'"))');
				}
				case '4'://盘点
				{
					$this->execute("UPDATE stock_spec ss,stockout_order_detail sod
									SET ss.last_pd_time=NOW()
									WHERE ss.spec_id=sod.spec_id AND ss.warehouse_id={$stockout_info['warehouse_id']} AND sod.stockout_id={$stockout_info['stockout_id']};");

					$pd_sod_info = D('Stock/StockoutOrderDetail')->alias('sod')->field("sod.spec_id,{$stockout_info['warehouse_id']} as warehouse_id,NOW() as last_pd_time,IFNULL(ssp.position_id,-{$stockout_info['warehouse_id']}) as position_id")->join('stock_spec_position ssp on ssp.spec_id = sod.spec_id and ssp.warehouse_id = '.$stockout_info['warehouse_id'])->where(array('sod.stockout_id'=>$stockout_info['stockout_id']))->select();
					$for_update_data = array('last_pd_time'=>array('exp',"VALUES(last_pd_time)"));
					D('Stock/StockSpecPosition')->addAll($pd_sod_info,array(),$for_update_data);
				}
			}
			//------------更新出库单状态
			$update_so_data 	= array('status'=>110,'consign_time'=>array('exp','NOW()'));
			$update_so_where 	= array('stockout_id'=>$stockout_id);
			$this->where($update_so_where)->save($update_so_data);

			//------------记录出入库日志
			$stock_inout_data = array(
				"order_type"		=> 2,
				"order_id"			=> $stockout_id,
				"operator_id"		=> $operator_id,
				"operate_type"		=> 19,
				"message"			=> "完成出库单:".$stockout_no,
			);
			D("StockInoutLog")->add($stock_inout_data);

			//-----------更新库存信息
			$detail_fields = array('spec_id','num','spec_no','src_order_detail_id','remark');
			$detail_where = array('stockout_id'=>$stockout_id);
			$detail_info = D('Stock/StockoutOrderDetail')->field($detail_fields)->where($detail_where)->select();
			$stockout_change_history = array();
			$order_check_no_stock_stockout = get_config_value('order_check_no_stock_stockout',0);
			foreach ($detail_info as $detail_item)
			{
				$spec_id 	= $detail_item['spec_id'];
				$num 		= floatval($detail_item['num']);

				$stock_spec_info = D('Stock/StockSpec')->field(array('stock_num,rec_id','cost_price'))->where(array('spec_id'=>$spec_id,'warehouse_id'=>$stockout_info['warehouse_id']))->find();
				$stock_num 	= floatval($stock_spec_info['stock_num']);
				$stock_spec_id 	= floatval($stock_spec_info['rec_id']);
				$new_num 	= $stock_num - $num;
				$is_allow_neg_stock = D('Goods/GoodsSpec')->field('is_allow_neg_stock')->where(array('spec_id'=>$spec_id))->find();
				if(!(int)$is_allow_neg_stock['is_allow_neg_stock']&&$order_check_no_stock_stockout!=0){
					if($new_num < 0)
					{
						SE("商家编码:".$detail_item['spec_no']."--不允许负库存出库！");
					}
				}	
                D('Stock/StockSpecLog')->add(array('operator_type'=>3,'operator_id'=>$operator_id,'stock_spec_id'=>$stock_spec_id,'message'=>$stock_spec_log_message,'stock_num'=>$stock_num,'num'=>$num));

                $update_ss_data 	= array('stock_num'=>$new_num);
				$update_ss_where 	= array('spec_id'=>$spec_id,'warehouse_id'=>$stockout_info['warehouse_id']);
				$res_update_ss = D('Stock/StockSpec')->where($update_ss_where)->save($update_ss_data);
				$stockout_change_history[] = array(
					'src_order_type' => $stockout_info['src_order_type'],
					'stockio_id'   => $stockout_id,
					'stockio_no'   =>$stockout_no,
					'src_order_id' => empty($stockout_info['src_order_id'])?0:$stockout_info['src_order_id'],
					'src_order_no' =>empty($stockout_info['src_order_no'])?"":$stockout_info['src_order_no'],
					'stockio_detail_id' =>empty($detail_item['src_order_detail_id'])?0:$detail_item['src_order_detail_id'],
					'spec_id'  =>$spec_id,
					'warehouse_id' =>$stockout_info['warehouse_id'],
					'type' =>2,
					'cost_price_old' =>$stock_spec_info['cost_price'],
					'stock_num_old' =>$stock_num,
					'price' =>$stock_spec_info['cost_price'],
					'num' =>$num,
					'amount' =>$stock_spec_info['cost_price']*$num,
					'cost_price_new' =>$stock_spec_info['cost_price'],
					'stock_num_new' =>$new_num,
					'operator_id' =>$operator_id,
					'created' =>array('exp', 'NOW()'),
					
				);
				
			
			}
			D('Stock/StockChangeHistory')->insertStockChangeHistory($stockout_change_history);
			//-------更新货位库存
			//-----------查询出库单详情,更新货位数量:如果对应库存货位表里面没有货位信息,则stock_num为stock_spec数量,如果有则为-num数量
			$ss_position_info = D('Stock/StockoutOrderDetail')->alias('sod')->field(array("ss.warehouse_id","sod.spec_id","NOW() as last_inout_time",'cwz.zone_id','IF(ssp.position_id IS NULL,-ss.warehouse_id,ssp.position_id) as position_id','IF(ssp.position_id IS NULL,ss.stock_num,-sod.num) as stock_num','IF(ssp.position_id IS NULL,-ss.warehouse_id,ssp.position_id) as last_position_id'))->join('left join stock_spec ss on ss.spec_id = sod.spec_id and ss.warehouse_id='.$stockout_info['warehouse_id'])->join('left join stock_spec_position ssp on ssp.spec_id= sod.spec_id and ssp.warehouse_id='.$stockout_info['warehouse_id'])->join('left join cfg_warehouse_position cwp on cwp.rec_id = ssp.position_id')->join('left join cfg_warehouse_zone cwz on cwz.warehouse_id = ss.warehouse_id')->where(array("sod.stockout_id"=>$stockout_id))->select();
			$stock_spec_position_up = array(
				'stock_num'			=>array('exp',"stock_num+VALUES(stock_num)"),
				"last_inout_time"	=>array('exp','NOW()'),
			);
			$res_ss_p = D('Stock/StockSpecPosition')->addAll($ss_position_info,'',$stock_spec_position_up);
			$res_ss_u = D('Stock/StockSpec')->addAll($ss_position_info,'',array('last_position_id'=>array('exp','VALUES(last_position_id)')));
			//----------更新货品数量变化信息
			D('Common/SysProcessBackground')->stockoutChange($stockout_id);

		}catch(BusinessLogicException $e){
			$msg = $e->getMessage();
			SE($msg);
		}catch(\PDOException $e){
			$msg = $e->getMessage();
			\Think\Log::write('-checkStockout-'.$msg);
			SE(self::PDO_ERROR);
		}catch(\Exception $e){
			$msg = $e->getMessage();
			\Think\Log::write('-checkStockout-'.$msg);
			SE(self::PDO_ERROR);
		}
	}
	public function getStockoutOrderLock($fields,$conditions = array())
	{
	    try {
	        $res = $this->table('stockout_order')->where($conditions)->field($fields)->lock(true)->find();
	        return $res;
	    } catch (\PDOException $e) {
	        $msg = $e->getMessage();
	        \Think\Log::write($this->name.'-getStockoutInfo-'.$msg);
	        E(self::PDO_ERROR);
	    }
	}
	public function updateStockoutOrder($data,$conditions)
	{
	    try {
	        $res = $this->where($conditions)->save($data);
	        return $res;
	    } catch (\PDOException $e) {
	        $msg = $e->getMessage();
	        \Think\Log::write($this->name.'-updateStockoutOrder-'.$msg);
	        E(self::PDO_ERROR);
	    }
	}
	// 批量添加包裹数
    public function updateBatchStockoutOrder($datas,$conditions)
    {
        try {
            for($i=0;$i<count($datas);$i++) {
                $update_stockout_cond = array('stockout_id'=>$conditions[$i]);
                $update_stockout_data = array('package_count'=>$datas[$i]);
                $res = $this->where($update_stockout_cond)->save($update_stockout_data);
            }
        }catch (\PDOException $e) {
            $msg = $e->getMessage();
            \Think\Log::write($this->name.'-updateBatchStockoutOrder-'.$msg);
            E(self::PDO_ERROR);
        }
    }
    public function getStockoutOrders($fields,$conditions=array())
    {
        try {
            $res = $this->where($conditions)->field($fields)->select();
            return $res;
        } catch (\PDOException $e) {
            $msg = $e->getMessage();
            \Think\Log::write($this->name.'-getStockoutOrders-'.$msg);
            E(self::PDO_ERROR);
        }
    }
	public function saveWaybill($data, $log,$is_force){
		try{
			$ret['status'] = 0;
			$ret['msg'] = "保存物流单号成功";
			$this->startTrans();

			$error_info = array();
			$rec_repeat = array();
			if(!$is_force){
				foreach($data as $key=>$value){
					if(empty($data[$key]['logistics_no'])){
						$tip_info = '物流单号为空';
					}else{
						$tip_info = '物流单号重复';
					}
					$pos_i = in_array($key,$rec_repeat);
					if($pos_i){
						continue;
					}
					$is_equal = false;
					for($j=$key+1; $j<count($data);$j++){
						$pos_j = in_array($j,$rec_repeat);
						if($pos_j){
							continue;
						}
						if($data[$key]['logistics_no'] == $data[$j]['logistics_no']){
							array_push($rec_repeat,$j);
							array_push($error_info,array('stock_id'=>$data[$j]['stockout_id'],'stock_no'=>$data[$j]['src_order_no'],'msg'=>$tip_info));
							$is_equal = true;
						}
					}
					if($is_equal ==true){
						array_push($rec_repeat,$key);
						array_push($error_info,array('stock_id'=>$data[$key]['stockout_id'],'stock_no'=>$data[$key]['src_order_no'],'msg'=>$tip_info));

					}
				}
				if(!empty($error_info)){
					SE('添加失败');
				}
				$this->patchValidate = true;
				foreach($data as $key=>$value){
					if(!$this->create($value)){
						$error_info[] = array('stock_id'=>$value['stockout_id'],'stock_no'=>$value['src_order_no'],'msg'=>implode('--',$this->getError()));
					}
				}

				if(!empty($error_info)){
					SE('添加失败');
				}
			}
			$log_model = M('sales_trade_log');
			$log_model->addAll($log);

			$this->addAll($data, array(), array('logistics_no'));
			$sales_trade_data = array();
			foreach($data as $key =>$value){
			    $sales_trade_data[]  = array('trade_id'=>$value['src_order_id'],'logistics_no'=>$value['logistics_no']);
			}
			D('Trade/Trade')->addAll($sales_trade_data,array(),array('logistics_no'));
			$this->commit();
		}catch(\PDOException $e){
			\Think\Log::write($e->getMessage());
			$this->rollback();
			$ret['status'] = 1;
			$ret['msg'] = "保存物流单号失败";

		}catch(BusinessLogicException $e){
			$this->rollback();
			$ret['status']  = 2;
			$ret['msg'] 	= $e->getMessage();
			$ret['data'] 	= $error_info;
		}
		$this->patchValidate = false;
		return $ret;
	}
	/**
	 * 确认发货获取出库单详情
	 * 
	 * 连接表cfg_logsitics,sales_trade并加锁
	 * 
	 * @author homedown
	 * @param array $fields
	 * @param array $conditions
	 * @return array
	 */
	public function getSalesStockoutOrder($fields,$conditions = array())
	{
	    try {
	        $res = $this->alias('so')->join("left join cfg_logistics cl ON cl.logistics_id=so.logistics_id")->join('left join sales_trade st on st.trade_id = so.src_order_id')->where($conditions)->field($fields)->lock(true)->find();
	        return $res;
	    } catch (\PDOException $e) {
	        $msg = $e->getMessage();
	        \Think\Log::write($this->name."-getStockoutOrderLeftLogistics-".$msg);
	        E(self::PDO_ERROR);
	    }
	}
	public function getSalesStockoutOrderList($fields,$conditions = array())
	{
	    try {
			if(!empty($this->options['where'])){
				\Think\Log::write($this->fetchSql(true)->alias('so')->join("left join cfg_logistics cl ON cl.logistics_id=so.logistics_id")->join('left join sales_trade st on st.trade_id = so.src_order_id')->where($conditions)->field($fields)->lock(true)->select());
				unset($this->options['where']);
			}
			
	        $res = $this->alias('so')->join("left join cfg_logistics cl ON cl.logistics_id=so.logistics_id")->join('left join sales_trade st on st.trade_id = so.src_order_id')->where($conditions)->field($fields)->lock(true)->select();
	        return $res;
	    } catch (\PDOException $e) {
            $msg = $e->getMessage();
	        \Think\Log::write($this->name."-getStockoutOrderLeftLogistics-".$msg.'-condition:-'.print_r($conditions,true));
	        \Think\Log::write($this->fetchSql(true)->alias('so')->join("left join cfg_logistics cl ON cl.logistics_id=so.logistics_id")->join('left join sales_trade st on st.trade_id = so.src_order_id')->where($conditions)->field($fields)->lock(true)->select());
	        E(self::PDO_ERROR);
	    }
	}
	/**
	 * 查询验货单据信息
	 * 
	 * 连接sales_trade stockout_order_detail cfg_shop
	 * 
	 * @author homedown
	 * @param array $fields
	 * @param array $conditions
	 * @return array list
	 */
	public function getSalesStockoutOrderLeftSalesTrade($fields,$conditions=array())
	{
	    try {
	        $res = $this->alias('so')->join("left join sales_trade st on st.trade_id = so.src_order_id")->join('left join cfg_shop cs on cs.shop_id = st.shop_id')->join('left join stockout_order_detail sod on sod.stockout_id = so.stockout_id')->where($conditions)->field($fields)->group('so.stockout_id')->select();
	        return $res;
	    } catch (\PDOException $e) {
	        $msg = $e->getMessage();
	        \Think\Log::write($this->name."-getSalesStockoutOrderLeftSalesTrade-".$msg);
	        SE(self::PDO_ERROR);
	    }
	    
	}
	/**
	 * 销售出库单的验货操作
	 * 
	 * @author homedown
	 * @param int $stockout_id
	 * @param array $check_goods_list
	 * @return boolean
	 */
	public function consignCheckSalesStockoutOrder($stockout_id,$check_rows)
	{
	    
	    try {
	        
	        $this->startTrans();
	        $operator = get_operator_id();
	        $stockout_order_info_fields = array(
	            'src_order_type','src_order_id','`status`','consign_status','freeze_reason','warehouse_id',	'block_reason','warehouse_type','picker_id'
	        );
	        $stockout_order_info_conditions = array(
	            'stockout_id'=>$stockout_id,
	            'status'=>array('neq',5)
	        );
	        $res_stockout_order = $this->getStockoutOrders($stockout_order_info_fields,$stockout_order_info_conditions);
	        if(empty($res_stockout_order))
	        {
	            SE('出库单不存在');
	        }
	        $stockout_order_info = $res_stockout_order[0];
	        if((int)$stockout_order_info['src_order_type']<>1)
	        {
	            SE('出库单不是销售出库单');
	        }
	        if((int)$stockout_order_info['status']<55)
	        {
	            SE('出库单状态不正确');
	        }
	        if((int)$stockout_order_info['status']>=95)
	        {
	            SE('订单已发货');
	        }
	        if((int)$stockout_order_info['consign_status'] & 1)
	        {
	            SE('订单已验货');
	        }
	        if((int)$stockout_order_info['freeze_reason']<>0)
	        {
	            SE('出库单已冻结');
	        }
	        if((int)$stockout_order_info['block_reason']<>0)
	        {
                $block_reason = D('Stock/SalesStockOut')->getBlockReason($stockout_order_info['block_reason']);
                SE("出库单[{$block_reason}]拦截出库");
	        }
	        if((int)$stockout_order_info['warehouse_type']<>1)
	        {
	            SE('委外订单不能验货');
	        }
	        //-----更新出库单详情--------------------------
	        foreach ($check_rows as $key=>$value)
	        {
	            if($check_rows['num']!=$check_rows['check_num']){
	                SE('请确认出库数量正确');
	            }
	            $update_detail_data = array(
	                'scan_type' =>$value['scan_type']
	            );
	            $update_detail_conditions = array(
	                 'stockout_id'=>$stockout_id,
	                 'spec_id'=>$value['spec_id']  
	            );
	            $res_update_detail = D('Stock/StockoutOrderDetail')->updateStockoutOrderDetail($update_detail_data,$update_detail_conditions);
	        }
	        //-------更新出库单状态-------------------
	        $update_order_data = array(
	            'consign_status'=>array('exp','(consign_status|1)'),
	            'examiner_id'=>array('exp',$operator)
	        );
	        $res_update_order = $this->where(array('stockout_id'=>$stockout_id))->save($update_order_data);
	        //--------更新订单信息---------------
	        $update_sales_trade_data = array(
	            'consign_status'=>array('exp','(consign_status|1)'),
	        );
	        $res_update_sales_trade = D('Trade/SalesTrade')->where(array('trade_id'=>$stockout_order_info['src_order_id']))->save($update_sales_trade_data);
	        //--------插入出入库日志---------------------
	        $insert_inout_log_data = array(
	            'order_type'   =>2,
	            'order_id'   =>$stockout_id,
	            'operator_id'  =>$operator,
	            'operate_type' =>1,
	            'message'      =>'订单验货'  
	        );
	        $res_insert_inout_log = D('Stock/StockInoutLog')->insertStockInoutLog($insert_inout_log_data);
	        //--------更新订单日志---------------
	        $insert_sales_log_data = array(
	            'trade_id'=>$stockout_order_info['src_order_id'],
	            'operator_id'=>$operator,
	            'type'=>100,
	            'message'=>'订单验货'  
	        );
	        $res_insert_sales_log = D('Trade/SalesTradeLog')->addTradeLog($insert_sales_log_data);
	        //------------订单全链路--------------
	        D('Trade/TradeCheck')->traceTrade($operator,$stockout_order_info['src_order_id'],10,'');
	        $this->commit();
	    } catch (BusinessLogicException $e) {
	        $msg  = $e->getMessage();
	        $this->rollback();
	        SE($msg);
	    } catch (\PDOException $e) {
	        $msg  = $e->getMessage();
	        \Think\Log::write($this->name.'-consignCheckSalesStockoutOrder-'.$msg);
	        $this->rollback();
	        SE(self::PDO_ERROR);
	    }
	    
	}
	public function insertStockoutOrderForUpdate($data, $update=false, $options='')
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
	        \Think\Log::write($this->name.'-insertStockoutOrderForUpdate-'.$msg);
	        E(self::PDO_ERROR);
	    }
	}
	public function getOrdinaryLogisticsNo($data)
	{
		try{
			$source_logistics_no = trim($data['template_no']);
			$logistics_regex = D('Setting/Logistics')->alias('cl')->field(array('dl.logistics_regex'))->join("left join dict_logistics dl on dl.logistics_type = cl.logistics_type")->where(array('cl.logistics_id'=>$data['logistics_id']))->find();
			/*if(!empty($logistics_regex['logistics_regex'])){
				$is_match = preg_match('/'.$logistics_regex['logistics_regex'].'/',$source_logistics_no,$match_item);
				if(is_numeric($is_match) ){
					if($is_match === 0){
						SE('单号格式不正确!');
					}
				}else{
					SE('单号规则匹配异常');
				}
			}*/

			$match_num = preg_match_all('/\D*(\d+)\D*/',$source_logistics_no,$match,PREG_OFFSET_CAPTURE);
			if(is_numeric($match_num) ){
				if($match_num === 0){
					SE('单号格式不正确!');
				}
			}else{
				SE('单号规则匹配异常');
			}
			if(intval($data['need_num'])<=0){
				SE('至少包含两条订单信息');
			}
			$match_key = 0;
			$match_value = '';
			$match_length = 0;
			foreach($match[1] as $key => $match_item)
			{
				if(is_array($match_item)){
					if(strlen($match_item[0])>$match_length){
						$match_length = strlen($match_item[0]);
						$match_key = $match_item[1];
						$match_value = $match_item[0];
					}
				}
			}
			if($match_value == ''){
				SE('单号格式不正确!');
			}
			$no = gmp_init($match_value,10);
			$increment_num =gmp_init(intval($data['increment']));
			$logistics_no_list = array();

			$inc_no = gmp_init($match_value,10);
			for($i = 0;$i<intval($data['need_num']);$i++){
				$inc_no   	  = gmp_add($inc_no,$increment_num);
				$generate_no   = gmp_abs($inc_no);
				if(gmp_cmp($generate_no,$no)==0){
					$inc_no   	   = gmp_add($inc_no,$increment_num);
					$generate_no   = gmp_abs($inc_no);
				}
				$generate_no  = gmp_strval($generate_no);
				$generate_no = str_pad($generate_no,$match_length,'0',STR_PAD_LEFT);
				$logistics_no  = substr_replace($source_logistics_no,$generate_no,$match_key,$match_length);
				array_push($logistics_no_list,$logistics_no);
			}
			return $logistics_no_list;
		}catch(\PDOException $e){
			\Think\Log::write($e->getMessage());
			SE(self::UNKNOWN_ERROR);
		}catch(BusinessLogicException $e){
			SE($e->getMessage());
		}catch(\Exception $e){
			\Think\Log::write($e->getMessage());
			SE(self::UNKNOWN_ERROR);
		}
	}
	public function importLogisticsNo($logistics_data,$is_force=false)
	{
		try {

			$sql_drop = "CREATE TEMPORARY TABLE IF NOT EXISTS  `tmp_import_detail` (`rec_id` INT(11) NOT NULL AUTO_INCREMENT,`logistics_no` varchar(40),`logistics_name` varchar(64) ,`trade_no` varchar(64),PRIMARY KEY(`rec_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
			$this->execute($sql_drop);
			$operator_id = get_operator_id();
			$error_info = array();
			$log = array();
			$checked_data = array();//校验后的数据
			$result = array('status' => 0, 'msg' => '保存成功!');
			/*log[i] = {};
				log[i].trade_id = selections[i].src_order_id;
				log[i].operator_id = operatorId;
				log[i].type = 21;*/
			foreach($logistics_data as $item)
			{
				$res_insert_temp_import_detail = $this->execute("insert into tmp_import_detail(`logistics_no`,`logistics_name`,`trade_no`) values('{$item['logistics_no']}','".$item['logistics_name']."','{$item['trade_no']}')");
			}
			//---------组合需要的数据
			$detail_info = $this->query('SELECT so.stockout_id,so.warehouse_id,so.stockout_no,so.src_order_type,so.src_order_no,so.src_order_id,\'' . $operator_id . '\'as operator_id,21 as type,cl.bill_type,cl.logistics_type,st.trade_id,st.trade_status,tid.trade_no,tid.logistics_no,tid.logistics_name FROM tmp_import_detail tid LEFT JOIN sales_trade st on st.trade_no = tid.trade_no LEFT JOIN stockout_order so on so.src_order_id = st.trade_id LEFT JOIN cfg_logistics cl on (cl.logistics_name = tid.logistics_name and cl.logistics_id = so.logistics_id)');

			foreach ($detail_info as $key => $value) {
				if (empty($value['trade_id'])) {
					array_push($error_info, array('stock_id' => $value['stockout_id'], 'stock_no' => $value['trade_no'], 'msg' => '订单[' . $value['trade_no'] . ']不存在'));
					continue;
				} elseif (intval($value['trade_status']) < 55) {
					array_push($error_info, array('stock_id' => $value['stockout_id'], 'stock_no' => $value['trade_no'], 'msg' => '订单[' . $value['trade_no'] . ']状态不正确'));
					continue;
				}elseif (intval($value['src_order_type']) != 1) {
					array_push($error_info, array('stock_id' => $value['stockout_id'], 'stock_no' => $value['trade_no'], 'msg' => '订单[' . $value['trade_no'] . ']不是销售出库单'));
					continue;
				}
				if (empty($value['logistics_type'])) {
					array_push($error_info, array('stock_id' => $value['stockout_id'], 'stock_no' => $value['trade_no'], 'msg' => '物流公司[' . $value['logistics_name'] . ']与订单不匹配'));
					continue;
				}elseif (intval($value['bill_type']) != 0) {
					array_push($error_info, array('stock_id' => $value['stockout_id'], 'stock_no' => $value['trade_no'], 'msg' => '物流公司[' . $value['logistics_name'] . ']属电子面单不支持导入物流单号'));
					continue;
				}
				$checked_data[] = $value;
				$log[] = array('trade_id' => $value['trade_id'], 'operator_id' => $value['operator_id'],'message'=>'订单['.$value['trade_no'].']'.'导入['.$value['logistics_name'].']物流单号:'.$value['logistics_no'], 'type' => 21);
			}
			$res = $this->saveWaybill($checked_data, $log, $is_force);
			if ($res['status'] == 1) {
				$result['status'] = 1;
				$result['msg'] = $res['msg'];
			} elseif ($res['status'] == 2) {
				$result['status'] = 2;
				$result['msg'] = '失败';
				foreach ($res['data'] as $error_k => $error_v) {
					array_push($error_info, $error_v);
				}
				$result['data'] = $error_info;
			}elseif(!empty($error_info)){
				$result['status'] = 2;
				$result['msg'] = '失败';
				$result['data'] = $error_info;
			}
			$this->execute("DELETE FROM tmp_import_detail");
		}catch (\PDOException $e){
			Log::write($e->getMessage());
			$result['status'] = 1;
			$result['msg'] = self::PDO_ERROR;
		}catch (\Exception $e){
			Log::write($e->getMessage());
			$result['status'] = 1;
			$result['msg'] = self::PDO_ERROR;
		}
		return $result;
	}

	public function getLogisticsNo($logistics_no){
		try{
			$where['logistics_no'] = $logistics_no;
			$data = $this->field('stockout_id')->where($where)->select();
			if(count($data)>0)
				return true;
			else return false;
		}
		catch(\PDOException $e){
			\Think\Log::write($e->getMessage());
			return true;
		}
	}
	
	
	public function changePrintStatus($logistics_ids,$print_type,$value=1){
		try{
			if($print_type == "logistics")
				$data['logistics_print_status'] = $value;
			else if($print_type == "goods")
				$data['sendbill_print_status'] = $value;
            else if($print_type == "sorting")
                $data['picklist_print_status'] = $value;
			else if($print_type == "together"){
				$data['logistics_print_status'] = $value;
				$data['sendbill_print_status'] = $value;
			}else if($print_type == "logAndPick"){
                $data['logistics_print_status'] = $value;
                $data['picklist_print_status'] = $value;
            }
			//$sql = $this->fetchSql(false) -> where("stockout_id in ( " . $logistics_ids . ")") -> save($data);
			$sql = $this -> where(array('stockout_id'=>array('in',$logistics_ids))) -> save($data);
			//改变档口单物流单打印状态。
            $stockoutOrders = $this -> where(array('stockout_id'=>array('in',$logistics_ids))) ->select();
            foreach ($stockoutOrders as $stockoutOrder){
                $sodModel = D('Purchase/StallsOrderDetail');
                $stallsOrderDetail = $sodModel->where(array('trade_no'=>$stockoutOrder['src_order_no']))->select();
                if(!empty($stallsOrderDetail)){
                    $sodModel->where(array('trade_no'=>$stockoutOrder['src_order_no']))->save(array('logistics_print_status'=>1));
                }
            }
        }catch(\PDOException $e){
			\Think\Log::write($this->name.'-changePrintStatus-'.$e->getMessage());
            SE(self::PDO_ERROR);
		}catch(\Exception $e){
            \Think\Log::write($this->name.'-changePrintStatus-'.$e->getMessage());
            SE(self::PDO_ERROR);
		}
	}
	public function getHasPrintedInfo($ids,$type,$multiIds,&$result,$all_ids){
		$type_map_field = array(
			'logistics'=>'logistics_print_status',
			'goods'=>'sendbill_print_status',
            'sorting'=>'picklist_print_status',
            'multipleLogistics'=>'print_status',
		);
		try {
			$has_printed_info = array(
				'logistics' 		=> array(),
				'goods' 			=> array(),
				'sorting' 			=> array(),
				'multipleLogistics' => array(),
				'together' 			=> array(),
				'logAndPick' 		=> array(),
			);
			$fail_list        = array();
			$stockout_ids  = trim($ids,',');
			$stockout_order_field = array(
				'stockout_id',
				'stockout_no',
				'logistics_print_status',
				'sendbill_print_status',
                'picklist_print_status',
                'status'
			);
			$stockout_order_conditions = array(
				'stockout_id'=>array('in',$stockout_ids)
			);
			$this->startTrans();
			$res_query_stockout = D('Stock/StockOutOrder')->fetchSql(false)->where($stockout_order_conditions)->field($stockout_order_field)->lock(true)->order('stockout_id')->select();
            if($type == 'multipleLogistics'){
				$stockout_ids  = trim($multiIds,',');
				$stockout_order_field = array(
					'rec_id',
					'logistics_id',
					'logistics_no',
					'print_status',
				);
				$stockout_order_conditions = array(
					'rec_id'=>array('in',$stockout_ids)
				);
				$res_query_multilogistics = D('Stock/SalesMultiLogistics')->where($stockout_order_conditions)->field($stockout_order_field)->lock(true)->order('rec_id')->select();
				D('Stock/SalesMultiLogistics')->changeMultiPrintStatus($stockout_ids,1);
			}else{
				$this->newUpdatePrintLog('1',$all_ids,$type);
				$this->changePrintStatus($stockout_ids,$type,1);
			}
			if(empty($res_query_stockout))
			{
				E('没有查询到打印的信息!');
			}else{
				foreach($res_query_stockout as $key => $value)
				{
					if($value['status'] < 55){
						$fail_list[] = array(
							'stock_id' => $value['stockout_id'],
							'stock_no' => $value['stockout_no'],
							'msg'      =>  '当前的订单的状态不正确!,请刷新当前页面',
						);
						continue;
					}
					$field = $type_map_field["{$type}"];
					if($type == 'multipleLogistics') {
						foreach ($res_query_multilogistics as $k => $v) {
							if($v["{$field}"]==1){
								$has_printed_info['multipleLogistics'][] = array(
									'rec_id' => $v['rec_id'],
									'stock_no' => $value['stockout_no'],
									'logistics_no' => $v['logistics_no'],
									'msg' => '该多物流单已打印!',
								);
							}
						}
					}elseif($type == 'together'){
                        if($value['sendbill_print_status']==1){
                            $has_printed_info['together'][] = array(
                                'stock_id' => $value['stockout_id'],
                                'stock_no' => $value['stockout_no'],
                                'msg'      =>  '该发货单已打印!',
                            );
                        }
                        if($value['logistics_print_status']==1){
                            $has_printed_info['together'][] = array(
                                'stock_id' => $value['stockout_id'],
                                'stock_no' => $value['stockout_no'],
                                'msg'      =>  '该物流单已打印!',
                            );
                        }
                    }elseif($type == 'logAndPick'){
                        if($value['picklist_print_status']==1){
                            $has_printed_info['logAndPick'][] = array(
                                'stock_id' => $value['stockout_id'],
                                'stock_no' => $value['stockout_no'],
                                'msg'      =>  '该分拣单已打印!',
                            );
                        }
                        if($value['logistics_print_status']==1){
                            $has_printed_info['logAndPick'][] = array(
                                'stock_id' => $value['stockout_id'],
                                'stock_no' => $value['stockout_no'],
                                'msg'      =>  '该物流单已打印!',
                            );
                        }
                    }else{
						if($value['sendbill_print_status']==1){
							$has_printed_info['goods'][] = array(
								'stock_id' => $value['stockout_id'],
								'stock_no' => $value['stockout_no'],
								'msg'      =>  '该发货单已打印!',
							);
						}
						if($value['logistics_print_status']==1){
							$has_printed_info['logistics'][] = array(
								'stock_id' => $value['stockout_id'],
								'stock_no' => $value['stockout_no'],
								'msg'      =>  '该物流单已打印!',
							);
						}
                        if($value['picklist_print_status']==1){
                            $has_printed_info['sorting'][] = array(
                                'stock_id' => $value['stockout_id'],
                                'stock_no' => $value['stockout_no'],
                                'msg'      =>  '该分拣单已打印!',
                            );
                        }
					}
					continue;
				}
			}
			if(!empty($fail_list)){
				$result['status'] = 2;
			}
			$result['data']['has_printed'] = $has_printed_info;
			$result['data']['fail']        = $fail_list;
			if(!empty($has_printed_info['goods'])&&$type=='goods'||!empty($has_printed_info['logistics'])&&$type=='logistics'||!empty($has_printed_info['sorting'])&&$type=='sorting'||!empty($has_printed_info['multipleLogistics'])&&$type=='multipleLogistics'){$this->rollback();}
			$this->commit();
		}catch (\PDOException $e){
			\Think\Log::write($this->name.'-getHasPrintedInfo-'.$e->getMessage());
			$this->rollback();
			$result['status'] = 1;
			$result['msg'] =  $e->getMessage();;
		}catch (\Exception $e){
			\Think\Log::write($this->name.'-getHasPrintedInfo-'.$e->getMessage());
			$this->rollback();
			$result['status'] = 1;
			$result['msg'] =  $e->getMessage();;
		}
	}

	public function exportStockoutSales($id_list,$search,$type = 'excel',$num)
	{
		$where_stockout_order = array();
		$where_sales_trade_order = array();
		$where_sales_trade = array();
		$where_stockout_order_detail = array();
		$is_empty_search = empty($search);
		$print_batch_order = '';
		//设置店铺权限
		D('Setting/EmployeeRights')->setSearchRights($search,'shop_id',1);
		D('Setting/EmployeeRights')->setSearchRights($search,'warehouse_id',2);
		foreach ($search as $k => $v) {
			if ($v === '') continue;
			switch ($k) {
				case 'stockout_no': {//出库单号  stockout_order
					set_search_form_value($where_stockout_order, $k, $v, 'so');
					break;
				}
				case 'print_batch':{//打印批次
					if($v!='all'){
						set_search_form_value($where_stockout_order,'stockout_id',$v,'so',2);
						$print_batch_order = $v;
					}
					break;
				}
				case 'src_order_no':{//原始单号 stockout_order
					set_search_form_value($where_stockout_order, $k, $v,'so');
					break;
				}
				case 'status':{//出库单状态 stockout_order
					set_search_form_value($where_stockout_order, $k, $v, 'so', 2);
					break;
				}
				case 'warehouse_id'://仓库类型
					set_search_form_value($where_stockout_order, $k, $v, 'so', 2);
					break;
				case 'src_order_type':{//出库单类别 stockout_order
					set_search_form_value($where_stockout_order, $k, $v, 'so', 2);
					break;
				}
				case 'block_reason':{//拦截原因 stockout_order
					if(!check_regex('number',$v))
					{
						break;
					}
					$where_stockout_order['so.block_reason'] = array('exp','&'.$v);
					break;
	// 	                set_search_form_value($where_stockout_order, $k, $v, 'so', 2);
	// 	                break;
				}
				case 'trade_from':{//订单来源 sales_trade
					set_search_form_value($where_sales_trade, $k, $v, 'st', 2);
					break;
				}
				case 'logistics_id':{//物流公司 stockout_order
					set_search_form_value($where_stockout_order, $k, $v, 'so', 2);
					break;
				}
				case 'operator_id':{//经办人  stockout_order
					set_search_form_value($where_stockout_order, $k, $v, 'so', 2);
					break;
				}
				case 'logistics_no':{//物流单号 stockout_order
					set_search_form_value($where_stockout_order, $k, $v, 'so');
					break;
				}
				case 'multi_logistics_no':{//多物流单号 stockout_order
					set_search_form_value($where_stockout_order, 'logistics_no', $v, 'sml');
					break;
				}
				case 'receiver_mobile':{//收件人手机 stockout_order
					set_search_form_value($where_stockout_order, $k, $v, 'so');
					break;
				}
				case 'receiver_province':{//省份 receiver_province
					$v = ','.$v.',';
					if(strpos($v,',0,')!==false){$v = str_replace(',0,','',$v);}
					$v=trim($v,',');
					if($v==0||$v==''){
						break;
					}
					set_search_form_value($where_sales_trade, $k, $v, 'so',2);
					break;
				}
				case 'receiver_city':{//城市 receiver_city
					if($v==0){
						break;
					}
					set_search_form_value($where_sales_trade, $k, $v, 'so');
					break;
				}
				case 'receiver_country':{//区县 receiver_country
					if($v==0){
						break;
					}
					set_search_form_value($where_sales_trade, 'receiver_district', $v, 'so');
					break;
				}
				case 'receiver_name':{//收件人姓名 stockout_order
					set_search_form_value($where_stockout_order, $k, $v, 'so');
					break;
				}
				case 'flag_id':{//出库单标记  stokout_order
					set_search_form_value($where_stockout_order, $k, $v, 'so',2);
					break;
				}
				case 'small_number':{ //货品数量区间小
					set_search_form_value($where_sales_trade['_string'], 'goods_count', $v, 'so',9,' AND ',' >= ');
					break;
				}
				case 'big_number':{ //货品数量区间大
					set_search_form_value($where_sales_trade['_string'], 'goods_count', $v, 'so',9,' AND ',' <= ');
					break;
				}
				case 'small_type':{ //货品种类区间小
					set_search_form_value($where_sales_trade['_string'], 'goods_type_count', $v, 'so',9,' AND ',' >= ');
					break;
				}
				case 'big_type':{ //货品种类区间大
					set_search_form_value($where_sales_trade['_string'], 'goods_type_count', $v, 'so',9,' AND ',' <= ');
					break;
				}
				case 'has_invoice':{//是否包含发票 stockout_order
					set_search_form_value($where_stockout_order, $k, $v, 'so',2);
					break;
				}
				case 'is_block':{//是否被拦截
					set_search_form_value($where_stockout_order,'block_reason',$v,'so',8);
					break;
				}
				case 'logistics_print_status':{ //是否打印物流单
					set_search_form_value($where_stockout_order, $k, $v, 'so', 2);
					break;
				}
				case 'sendbill_print_status':{ //是否打印发货单
					set_search_form_value($where_stockout_order, $k, $v, 'so', 2);
					break;
				}
				case 'src_tids':{//原始订单号  sales_trade
					//set_search_form_value($where_sales_trade, $k, $v, 'st');
					set_search_form_value($where_sales_trade_order, 'src_tid', $v,'sto');
					break;
				}
				case 'shop_id':{//店铺 sales_trade
					set_search_form_value($where_sales_trade, $k, $v, 'st',2);
					break;
				}
				case 'buyer_nick':{//客户网名 sales_trade
					set_search_form_value($where_sales_trade, $k, $v, 'st',6);
					break;
				}
				case 'spec_no':{//商家编码
					set_search_form_value($where_sales_trade_order, $k, $v, 'sto');
					break;
				}
				case 'consign_status':{
					if(!check_regex('number',$v))
					{
						break;
					}
					$where_stockout_order['so.consign_status'] = array('exp','&'.$v);
					break;
				}
				case 'consign_time_start':
					set_search_form_value($where_stockout_order['_string'], 'consign_time', $v,'so', 4,' AND ',' >= ');
					break;
				case 'consign_time_end':
					set_search_form_value($where_stockout_order['_string'], 'consign_time', $v,'so', 4,' AND ',' <= ');
					break;
				case 'multi_logistics':
					;
					break;
				case 'brand_id':
					set_search_form_value($where_stockout_order, $k, $v, 'gg', 2, ' AND ');
					break;
				case 'class_id':
					$join = set_search_form_value($where_stockout_order, $k, $v, 'gg', 7, ' AND ');
					break;
				case 'trade_fc_status':
					set_search_form_value($where_sales_trade, 'check_step', $v, 'st', 2);
					break;
				case 'fast_logis_printed':
					set_search_form_value($where_stockout_order, 'logistics_print_status', 1, 'so', 2);
					break;
				case 'fast_no_logis_printed':
					set_search_form_value($where_stockout_order, 'logistics_print_status', 0, 'so', 2);
					break;
				case 'fast_printed_not_stockout':
					set_search_form_value($where_stockout_order, 'logistics_print_status', 1, 'so', 2);
					set_search_form_value($where_stockout_order, 'status', 55, 'so', 2);
					break;
				case 'fast_stockout_not_printed':
					set_search_form_value($where_stockout_order, 'logistics_print_status', 0, 'so', 2);
					set_search_form_value($where_stockout_order, 'status', 95, 'so',9,'','egt');
					break;
				case 'examine_num':
					set_search_form_value($where_stockout_order,'logistics_print_status',1,'so',2);
					$where_stockout_order['so.consign_status'] = array('exp','&1=0');
					break;

				case 'fast_goods_printed':
					set_search_form_value($where_stockout_order, 'sendbill_print_status', 1, 'so', 2);
					break;
				case 'fast_no_goods_printed':
					set_search_form_value($where_stockout_order, 'sendbill_print_status', 0, 'so', 2);
					break;
				case 'fast_no_stockout':
					set_search_form_value($where_stockout_order, 'status', 55, 'so', 2);
					break;
				case 'fast_is_stockout':
					set_search_form_value($where_stockout_order, 'status', 95, 'so', 2);
					break;
				case 'fast_is_finish':
					set_search_form_value($where_stockout_order, 'status', 110, 'so', 2);
					break;
				case 'fast_is_checked':
					$where_stockout_order['so.consign_status'] = array('exp','& 1');
					break;
				case 'fast_no_checked':
					$where_stockout_order['so.consign_status'] = array('exp','&1=0');
					break;
				case 'fast_is_weighted':
					$where_stockout_order['so.consign_status'] = array('exp','&2');
					break;
				case 'fast_no_weighted':
					$where_stockout_order['so.consign_status'] = array('exp','&2=0');
					break;
				case 'fast_is_blocked':
					set_search_form_value($where_stockout_order,'block_reason',1,'so',8);
					break;
				case 'fast_no_blocked':
					set_search_form_value($where_stockout_order,'block_reason',0,'so',8);
					break;
				case 'fast_has_cliented':
					$where_sales_trade['_string'] .= " AND  st.buyer_message <> ''";
					break;
				case 'fast_no_cliented':
					$where_sales_trade['_string'] .= " AND  st.buyer_message = ''";
					break;
				case 'fast_one_day':
					set_search_form_value($where_sales_trade['_string'], 'trade_time', date('Y-m-d'),'st', 3,' AND ',' >= ');
					break;
				case 'fast_tow_day':
					set_search_form_value($where_sales_trade['_string'], 'trade_time', date('Y-m-d',strtotime('-1 day')),'st', 3,' AND ',' >= ');
					break;
				case 'fast_one_week':
					set_search_form_value($where_sales_trade['_string'], 'trade_time', date('Y-m-d',strtotime('-1 week')),'st', 3,' AND ',' >= ');
					break;
				case 'fast_one_month':
					set_search_form_value($where_sales_trade['_string'], 'trade_time', date('Y-m-d',strtotime('-1 month')),'st', 3,' AND ',' >= ');
					break;
				case 'cs_remark':
					set_search_form_value($where_sales_trade, $k, $v,'st', 6,' AND ');
					break;
				case 'buyer_message':
					set_search_form_value($where_sales_trade, $k, $v,'st', 10,' AND ');
					break;
				case 'remark_id':
					if($v!=''&&$v!='all'){
						if($v==0){//无备注
							$where_sales_trade['_string'].=" AND st.cs_remark = '' AND st.buyer_message = '' ";
						}elseif($v==1){//有备注
							$where_sales_trade['_string'].=" AND (st.cs_remark <> '' OR st.buyer_message <> '' ) ";
						}elseif($v==2){//有客服备注
							$where_sales_trade['_string'].=" AND st.cs_remark <> '' ";
						}elseif($v==3){//有买家留言
							$where_sales_trade['_string'].=" AND st.buyer_message <> '' ";
						}elseif($v==4){//备注+留言
							$where_sales_trade['_string'].=" AND st.cs_remark <> '' AND st.buyer_message <> '' ";
						}
					}
					break;
				case 'one_order_one_good':{//一单一货
					if($v=='1'){
						set_search_form_value($where_sales_trade['_string'], 'goods_count', '1', 'so',9,' AND ',' = ');
					}elseif($v=='0'){
						set_search_form_value($where_sales_trade['_string'], 'goods_count', '1', 'so',9,' AND ',' <> ');
					}
					break;
				}
				case 'goods_name_include':{//品名包含
					set_search_form_value($where_stockout_order_detail['include'], 'goods_name', $v,'sod', 10,' AND ');
					break;
				}
				case 'goods_name_not_include':{//品名不包含
					set_search_form_value($where_stockout_order_detail['not_include'], 'goods_name', $v,'sod', 10,' AND ');
					$where_stockout_order_detail['not_include_page'] = " AND page_1.stockout_id is NULL ";
					break;
				}
				case 'small_paid':{ //实付金额区间小
					$where_sales_trade['_string'].=' AND st.paid >= '.addslashes($v).' ';
					break;
				}
				case 'big_paid':{ //实付金额区间大
					$where_sales_trade['_string'].=' AND st.paid <= '.addslashes($v).' ';
					break;
				}
				case 'small_calc_weight':{ //预估重量区间小
					$where_sales_trade['_string'].=' AND so.calc_weight >= '.addslashes($v).' ';
					break;
				}
				case 'big_calc_weight':{ //预估重量区间大
					$where_sales_trade['_string'].=' AND so.calc_weight <= '.addslashes($v).' ';
					break;
				}
				default:
					\Think\Log::write("unknown field:" . print_r($k, true) . ",value:" . print_r($v, true));
					break;
			}
		}
		if($print_batch_order!=''){
			unset($where_stockout_order['so.sendbill_print_status']);
			unset($where_stockout_order['so.logistics_print_status']);
		}
		$operator_id = get_operator_id();
		if($operator_id == 1 && strpos($search['warehouse_id'],',') != false){
			unset($where_stockout_order['so.warehouse_id']);
		}
		if($operator_id == 1 && strpos($search['shop_id'],',') != false){
			unset($where_sales_trade['st.shop_id']);
		}
		$rows = $num+2;
		$limit="0," . $rows;
		if(isset($where_stockout_order['_string'])){
			$where_stockout_order['_string'] = trim($where_stockout_order['_string'],' AND ');
		}
		if(isset($where_sales_trade['_string'])){
			$where_sales_trade['_string'] = trim($where_sales_trade['_string'],' AND ');
		}
		$ids_condition = '';
		if(!empty($id_list)){
			$ids_condition = 'where so.stockout_id in ('.$id_list.') ';
		}
		$m = $this->alias("so");
		if(isset($search['multi_logistics'])&&$search['multi_logistics']==1 || !empty($search['multi_logistics_no']))
			$m->join('sales_multi_logistics sml on so.stockout_id = sml.stockout_id');
		//$sql = $this->stockoutSales($m, $where_stockout_order, $where_sales_trade, $where_sales_trade_order,$order,$limit);

		$where_stockout_order['so.src_order_type'] = array('eq',1);

		if(isset($where_stockout_order['so.status']))
		{
			$where_stockout_order['so.status'] = array(array('EGT',55),$where_stockout_order['so.status']);
		}else{
			$where_stockout_order['so.status'] = array('EGT',55);
		}
//	if(!empty($where_sales_trade))
//	{
		$m = $m->join('sales_trade st on  so.src_order_id = st.trade_id')->where($where_sales_trade);
//	}
		if(!empty($where_sales_trade_order))
		{
			$m = $m->join('sales_trade_order sto on so.src_order_id = sto.trade_id')->where($where_sales_trade_order);
		}
		if(isset($where_sales_trade['_string'])){
			isset($where_stockout_order['_string'])?$where_stockout_order['_string'] =' ('.$where_stockout_order['_string']. ') AND '.$where_sales_trade['_string']:$where_stockout_order['_string'] = $where_sales_trade['_string'];
		}
		$m = $m->where($where_stockout_order);
		$page = clone $m;
		$sql_page = $page->field('so.stockout_id id')->order('id DESC')->group('so.stockout_id')->limit($limit)->fetchSql(true)->select();
		$cfg_show_telno=get_config_value('show_number_to_star',1);
		$point_number = get_config_value('point_number',0);
		$point_number = intval($point_number);
		$field_right = D('Setting/EmployeeRights')->getFieldsRight('so.');
		$goods_count = 'CAST(sum(so.goods_count) AS DECIMAL(19,'.$point_number.')) goods_count';
		$sql = 'select so.src_order_id,so.error_info,so.operator_id,so.logistics_print_status,so.sendbill_print_status,so.receiver_province,so.receiver_city,so.receiver_district,'
			.'so.logistics_id,'.$field_right['goods_total_cost'].',so.printer_id,so.watcher_id,so.logistics_print_status,so.sendbill_print_status,so.batch_no,so.picklist_no,'//,so.outer_no
			.'so.stockout_id id,so.src_order_no,so.stockout_no,so.src_order_type,so.status,so.consign_status,'.$goods_count.',so.goods_type_count,'//so.picker_id,so.examiner_id,
			.'so.receiver_address,so.receiver_name,so.receiver_area,IF('.$cfg_show_telno.'=0,so.receiver_mobile,INSERT( so.receiver_mobile,4,4,\'****\')) receiver_mobile,IF('.$cfg_show_telno.'=0,so.receiver_telno,INSERT(so.receiver_telno,4,4,\'****\')) receiver_telno,so.receiver_zip,so.calc_post_cost,so.post_cost,so.calc_weight,so.weight,'//
			.'so.has_invoice,so.logistics_no,so.consign_time,so.block_reason,so.flag_id,'
			.'cl.logistics_name,cl.bill_type,cl.logistics_type, cl.logistics_id,so.outer_no,so.error_info,'
			.'st.warehouse_id,cw.type warehouse_type,st.checker_id,st.shop_id,st.src_tids,st.buyer_message,st.receivable,st.salesman_id,st.trade_time,st.pay_time,st.buyer_nick,st.trade_type,st.platform_id,st.paid,st.check_step,'
//                        .'sln.waybill_info,'
			.'he.fullname as checker_name,'
			.'cw.contact,cw.mobile,cw.telno,cw.province,cw.city,cw.district,cw.address,cw.zip,cw.name warehouse_name,'
			.'cs.shop_name,cs.website,'
			//.'IF(so.status <=55,\'尚未确认发货\',IF(als.sync_status is NULL,\'无需物流同步\',IF(sum(IF(als.sync_status<0,1,0))>0,\'请跳转查看\',IF(sum(als.sync_status)=3*count(1),\'物流同步成功\',IF(sum(als.sync_status)=0,\'等待同步\',IF(sum(als.sync_status)=2*count(1),\'同步失败\',\'多条同步信息,单击跳转查看\')))) )) as logistics_sync_info,'
			.'IF(so.consign_status & 128,\'物流同步成功\',IF(so.consign_status & 1024,\'多条同步信息,单击跳转查看\',IF(so.consign_status & 512,\'同步失败\',IF(so.consign_status & 256,\'等待同步\',IF(so.consign_status & 64,\'请跳转查看\',IF(so.consign_status & 32,\'无需物流同步\',\'尚未确认发货\')))) )) as logistics_sync_info,'
			//.'IF(gs.spec_no IS NULL,IFNULL(gst.suite_name,\'多种货品\'),CONCAT(gg.goods_name,\'-\',gs.spec_name)) goods_abstract '
			.'so.single_spec_no goods_abstract '
			. 'FROM stockout_order so '
			. 'JOIN sales_trade st ON so.src_order_id = st.trade_id '
			. 'JOIN cfg_shop cs ON cs.shop_id = st.shop_id '
			//. 'LEFT JOIN goods_spec gs ON gs.spec_no = so.single_spec_no and gs.deleted = 0 '
			//. 'LEFT JOIN goods_goods gg ON gg.goods_id = gs.goods_id '
			//. 'LEFT JOIN goods_suite gst ON gst.suite_no = so.single_spec_no and gst.deleted = 0 '
			. 'LEFT JOIN cfg_logistics cl ON cl.logistics_id = so.logistics_id '
			//. 'LEFT JOIN api_logistics_sync als ON als.stockout_id = so.stockout_id '
			. 'LEFT JOIN cfg_warehouse cw ON cw.warehouse_id = so.warehouse_id '
			//. 'LEFT JOIN stock_logistics_no sln ON sln.logistics_id = so.logistics_id and sln.logistics_no = so.logistics_no '
			// 				. 'LEFT JOIN cfg_oper_reason cor ON cor.reason_id = so.freeze_reason '
			. 'LEFT JOIN hr_employee he ON he.employee_id = st.checker_id '
			. 'JOIN (' . $sql_page . ') page ON page.id = so.stockout_id '
			. $ids_condition
			. ' group by so.stockout_id order by id desc';
//			$sql_total = $m->fetchSql(true)->count('distinct so.stockout_id');
//			$total=$m->query($sql_total);
//			$total = $total[0]['tp_count'];
			$data = $this->query($sql);
			//$data=array('total'=>$total,'rows'=>$list);
			return $data;

		/*$cfg_show_telno=get_config_value('show_number_to_star',1);
		$point_number = get_config_value('point_number',0);
		$goods_count = 'CAST(sum(so.goods_count) AS DECIMAL(19,'.$point_number.')) goods_count';
		$sql = 'select so.src_order_id,so.error_info,so.operator_id,so.logistics_print_status,so.sendbill_print_status,so.receiver_province,so.receiver_city,so.receiver_district,'
				.'so.logistics_id,so.goods_total_cost,so.printer_id,so.watcher_id,so.logistics_print_status,so.sendbill_print_status,so.batch_no,so.picklist_no,'//,so.outer_no
				.'so.stockout_id id,so.src_order_no,so.stockout_no,so.src_order_type,so.status,so.consign_status,'.$goods_count.',so.goods_type_count,'//so.picker_id,so.examiner_id,
				.'so.receiver_address,so.receiver_name,so.receiver_area,IF('.$cfg_show_telno.'=0,so.receiver_mobile,INSERT( so.receiver_mobile,4,4,\'****\')) receiver_mobile,IF('.$cfg_show_telno.'=0,so.receiver_telno,INSERT(so.receiver_telno,4,4,\'****\')) receiver_telno,so.receiver_zip,so.calc_post_cost,so.post_cost,so.calc_weight,so.weight,'//
				.'so.has_invoice,so.logistics_no,so.consign_time,so.block_reason,'//so.flag_id,
				.'cl.logistics_name,cl.bill_type,cl.logistics_type, cl.logistics_id,'
				.'st.warehouse_id,cw.type warehouse_type,st.checker_id,st.shop_id,st.src_tids,st.buyer_message,st.receivable,st.salesman_id,st.trade_time,st.pay_time,st.buyer_nick,st.trade_type,st.platform_id,st.paid,st.check_step,'
	//                        .'sln.waybill_info,'
				.'he.fullname as checker_name,'
				.'cw.contact,cw.mobile,cw.telno,cw.province,cw.city,cw.district,cw.address,cw.zip,cw.name warehouse_name,'
				.'cs.shop_name,cs.website,'
				//.'IF(so.status <=55,\'尚未确认发货\',IF(als.sync_status is NULL,\'无需物流同步\',IF(sum(IF(als.sync_status<0,1,0))>0,\'请跳转查看\',IF(sum(als.sync_status)=3*count(1),\'物流同步成功\',IF(sum(als.sync_status)=0,\'等待同步\',IF(sum(als.sync_status)=2*count(1),\'同步失败\',\'多条同步信息,单击跳转查看\')))) )) as logistics_sync_info,'
				.'IF(so.consign_status & 128,\'物流同步成功\',IF(so.consign_status & 1024,\'多条同步信息,单击跳转查看\',IF(so.consign_status & 512,\'同步失败\',IF(so.consign_status & 256,\'等待同步\',IF(so.consign_status & 64,\'请跳转查看\',IF(so.consign_status & 32,\'无需物流同步\',\'尚未确认发货\')))) )) as logistics_sync_info,'
				//.'IF(gs.spec_no IS NULL,IFNULL(gst.suite_name,\'多种货品\'),CONCAT(gg.goods_name,\'-\',gs.spec_name)) goods_abstract '
				.'so.single_spec_no goods_abstract '
				. 'FROM stockout_order so '
				. 'JOIN sales_trade st ON so.src_order_id = st.trade_id '
				. 'JOIN cfg_shop cs ON cs.shop_id = st.shop_id '
				//. 'LEFT JOIN goods_spec gs ON gs.spec_no = so.single_spec_no and gs.deleted = 0 '
				//. 'LEFT JOIN goods_goods gg ON gg.goods_id = gs.goods_id '
				//. 'LEFT JOIN goods_suite gst ON gst.suite_no = so.single_spec_no and gst.deleted = 0 '
				. 'LEFT JOIN cfg_logistics cl ON cl.logistics_id = so.logistics_id '
				//. 'LEFT JOIN api_logistics_sync als ON als.stockout_id = so.stockout_id '
				. 'LEFT JOIN cfg_warehouse cw ON cw.warehouse_id = so.warehouse_id '
				//. 'LEFT JOIN stock_logistics_no sln ON sln.logistics_id = so.logistics_id and sln.logistics_no = so.logistics_no '
				// 				. 'LEFT JOIN cfg_oper_reason cor ON cor.reason_id = so.freeze_reason '
				. $join_mutti_logis
				. 'LEFT JOIN hr_employee he ON he.employee_id = st.checker_id '
				//. 'where so.stockout_id in ('.$id_list.') '
				. $ids_condition
				. ' group by so.stockout_id order by so.stockout_id';
				$list = $this->query($sql);
				$total = count($list);*/
	}
    public function newUpdatePrintLog($is_print,$stockout_ids,$print_type,$multiIds)
    {
        try{
            $operator_id       = get_operator_id();
            $stockout_id_list  = explode(',', $stockout_ids);
            $is_print_str      = $is_print == 0? '预览了':'打印';  //判断打印还是预览
			$print_types = array(
				'logistics' => '物流单',
				'goods' 	=> '发货单',
				'sorting' 	=> '分拣单',
			);
            $is_logistics_str  = $print_types[$print_type];
			if($print_type == 'multipleLogistics'){
				$is_logistics_str = '多物流单';
				$multilogistic_id_list  = explode(',', $multiIds);
				$stockout_order_field = array(
					'logistics_id',
					'logistics_no',
					'print_status',
				);
				$stockout_order_conditions = array(
					'rec_id'=>array('in',$multiIds)
				);
				$res_query_multilogistics = D('Stock/SalesMultiLogistics')->getMultiPrintStatus($stockout_order_field,$stockout_order_conditions);
			}
            $operator_ip = get_client_ip();
            if(empty($stockout_ids)){
                \Think\Log::write($this->name.'-newUpdatePrintLog-'.$is_print.'-'.print_r($stockout_ids,true).'-'.$print_type,\Think\Log::INFO);
            }
            $stockout_order_info = $this->getSalesStockoutOrderList(array('st.trade_id','so.stockout_id id','st.trade_no','so.receiver_mobile','so.receiver_telno','so.sendbill_print_status','so.logistics_print_status','so.picklist_print_status','cl.logistics_name'),array('so.stockout_id'=>array('in',$stockout_id_list)));
            foreach ($stockout_order_info as $key =>$value)
            {
				if($print_type == 'multipleLogistics') {
					foreach ($res_query_multilogistics as $k => $v) {
						if($v['print_status'] == 1){
							$is_print_str      = $is_print == 0? '预览了':'重复打印了';
						}else{
							$is_print_str      = $is_print == 0? '预览了':'打印';
						}
						$add_logistics_message = '--物流公司：'.$value['logistics_name'];
						$add_multilogistics_message = '--多物流单号：'.$v['logistics_no'];
						$log[] = array(
							'trade_id'         => $value['trade_id'],
							'operator_id'      => $operator_id,
							'type'             => 91,
							'data'             => 2,
							'message'          => '登陆IP:'.$operator_ip.'--'.$is_print_str.$is_logistics_str.'：订单编号--'.$value['trade_no'].$add_multilogistics_message.$add_logistics_message,
						);
					}
				}else{
					if($print_type == 'goods' && $value['sendbill_print_status'] == 1){
						$is_print_str      = $is_print == 0? '预览了':'重复打印了';
					}elseif($print_type == 'logistics' && $value['logistics_print_status'] == 1){
						$is_print_str      = $is_print == 0? '预览了':'重复打印了';
					}elseif($print_type == 'sorting' && $value['picklist_print_status'] == 1){
						$is_print_str      = $is_print == 0? '预览了':'重复打印了';
					}else{
						$is_print_str      = $is_print == 0? '预览了':'打印';
					}
					$add_logistics_message = $print_type == 'logistics'?'--物流公司：'.$value['logistics_name']:'';
					$log[] = array(
						'trade_id'         => $value['trade_id'],
						'operator_id'      => $operator_id,
						'type'             => 91,
						'data'             => $print_type == 'logistics'?2:($print_type == 'goods'?1:3),
						'message'          => '登陆IP:'.$operator_ip.'--'.$is_print_str.$is_logistics_str.'：订单编号--'.$value['trade_no'].$add_logistics_message,
					);
				}
            }
            $res_update_log = D('Trade/SalesTradeLog')->addTradeLog($log);
        }catch (\PDOException $e){
            \Think\Log::write($this->name.'-newUpdatePrintLog-'.$e->getMessage());
            SE(self::PDO_ERROR);
        }catch(\Exception $e){
            \Think\Log::write($this->name.'-newUpdatePrintLog-'.$e->getMessage());
            SE(self::PDO_ERROR);
        }
    }
     public function getFastSearchNum($list)
    {
        try{
			if(empty($list)){
                return array();
            }
            $where = array();
            //设置店铺权限
			$operator_id = get_operator_id();
			if($operator_id > 1){
				D('Setting/EmployeeRights')->setSearchRights($where,'shop_id',1);
				D('Setting/EmployeeRights')->setSearchRights($where,'warehouse_id',2);
				if(empty($where['warehouse_id'])) $where['warehouse_id'] = '0';
				if(empty($where['shop_id'])) $where['shop_id'] = '0';
			}
			$setting_config = get_config_value(array('sales_print_time_range'),array(7));
            $list_num_ar = array();
			foreach ($list as $k =>$v){
                switch ($v) {
//                    case 'fast_logis_printed'://物流单已打印
//                        break;
//                    case 'fast_no_logis_printed'://物流单未打印
//                        break;
                    case 'fast_printed_not_stockout'://已打印物流单待发货
                        $list_num_ar[$v] = ($operator_id > 1)?$this->alias('so')->join('inner join sales_trade st on st.trade_id = so.src_order_id')->where(array('so.src_order_type'=>1,'so.status'=>55,'so.logistics_print_status'=>1,'st.shop_id'=>array('in',$where['shop_id']),'so.warehouse_id'=>array('in',$where['warehouse_id']),'_string'=>"so.consign_time='1000-01-01' or DATEDIFF(NOW(),so.consign_time) <=  ".$setting_config['sales_print_time_range']))->count(1) :
							$this->fetchSql(false)->alias('so')->join('inner join sales_trade st on st.trade_id = so.src_order_id')->where(array('so.src_order_type'=>1,'so.status'=>55,'so.logistics_print_status'=>1,'_string'=>"so.consign_time='1000-01-01' or DATEDIFF(NOW(),so.consign_time) <=  ".$setting_config['sales_print_time_range']))->count(1);
					  break;
                    case 'fast_stockout_not_printed'://已发货未打印物流单
                        $list_num_ar[$v] = ($operator_id > 1)?$this->alias('so')->join('inner join sales_trade st on st.trade_id = so.src_order_id')->where(array('so.src_order_type'=>1,'so.status'=>array('egt','95'),'so.logistics_print_status'=>0,'st.shop_id'=>array('in',$where['shop_id']),'so.warehouse_id'=>array('in',$where['warehouse_id']),'_string'=>"so.consign_time='1000-01-01' or DATEDIFF(NOW(),so.consign_time) <=  ".$setting_config['sales_print_time_range']))->count(1):
						$this->fetchSql(false)->alias('so')->join('inner join sales_trade st on st.trade_id = so.src_order_id')->where(array('so.src_order_type'=>1,'so.status'=>array('egt','95'),'so.logistics_print_status'=>0,'_string'=>"so.consign_time='1000-01-01' or DATEDIFF(NOW(),so.consign_time) <=  ".$setting_config['sales_print_time_range']))->count(1);
						break;
//                    case 'fast_goods_printed'://发货单已打印
//                        break;
//                    case 'fast_no_goods_printed'://发货单未打印
//                        break;
 //                  case 'fast_no_stockout'://待发货
 //                       break;
//                    case 'fast_is_stockout'://已发货未完成
//                        break;
//                    case 'fast_is_finish'://已完成
//                        break;
//                    case 'fast_is_checked'://已验货
//                        break;
//                    case 'fast_no_checked'://未验货
//                        break;
//                    case 'fast_is_weighted'://已称重
//                        break;
//                    case 'fast_no_weighted'://未称重
//                        break;
					   case 'fast_is_blocked'://已拦截订单
						$list_num_ar[$v] = ($operator_id > 1)?$this->alias('so')->join('inner join sales_trade st on st.trade_id = so.src_order_id')->where(array('so.block_reason'=>array('gt',0),'so.src_order_type'=>1,'st.shop_id'=>array('in',$where['shop_id']),'so.warehouse_id'=>array('in',$where['warehouse_id']),'_string'=>"so.consign_time='1000-01-01' or DATEDIFF(NOW(),so.consign_time) <=  ".$setting_config['sales_print_time_range']))->count(1):
						$this->fetchSql(false)->alias('so')->join('inner join sales_trade st on st.trade_id = so.src_order_id')->where(array('so.block_reason'=>array('gt',0),'so.status'=>array('EGT',55),'so.src_order_type'=>1,'_string'=>"so.consign_time='1000-01-01' or DATEDIFF(NOW(),so.consign_time) <=  ".$setting_config['sales_print_time_range']))->count(1);
						   break;
//                    case 'fast_no_blocked'://未拦截订单
//                        break;
//                    case 'fast_has_cliented'://有客户备注
//                        break;
//                    case 'fast_no_cliented'://无客户备注
//                        break;
//                    case 'fast_one_day'://一天内
//                        break;
//                    case 'fast_tow_day'://两天内
//                        break;
//                    case 'fast_one_week'://一周内
//                        break;
//                    case 'fast_one_month'://一月内
//                        break;
//					  case 'alarmperday' :  //预警数量
//						$list_num_ar[$v] = ($operator_id > 1)?D('Stock/StockSpec')->field('ss.spec_id')->alias('ss')->fetchSql(false)->join('LEFT JOIN goods_spec gs ON gs.spec_id=ss.spec_id')->join('LEFT JOIN goods_goods gg ON gg.goods_id=gs.goods_id')->where(array('ss.warehouse_id'=>array('in',$where['warehouse_id']),'gs.deleted'=>0,'gg.deleted'=>0,'_string'=>'ss.stock_num<ss.safe_stock'))->group('ss.spec_id')->select():
//						D('Stock/StockSpec')->field('ss.spec_id')->alias('ss')->fetchSql(false)->join('LEFT JOIN goods_spec gs ON gs.spec_id=ss.spec_id')->join('LEFT JOIN goods_goods gg ON gg.goods_id=gs.goods_id')->where(array('gs.deleted'=>0,'gg.deleted'=>0,'_string'=>'ss.stock_num<ss.safe_stock'))->group('ss.spec_id')->select();
//
//						$list_num_ar[$v] = count($list_num_ar[$v]);
//						break;
//					  case 'print_not_stockout': //打印未发货
//						 $list_num_ar[$v] = ($operator_id > 1)?$this->alias('so')->join('left join sales_trade st on st.trade_id = so.src_order_id')->where(array('so.src_order_type'=>1,'so.status'=>55,'st.shop_id'=>array('in',$where['shop_id']),'so.warehouse_id'=>array('in',$where['warehouse_id']),'_string'=>"(so.logistics_print_status = 1 or so.sendbill_print_status = 1) and (so.consign_time='1000-01-01' or DATEDIFF(NOW(),so.consign_time) <=  ".$setting_config['sales_print_time_range'].")"))->count():
//						 $this->alias('so')->where(array('so.src_order_type'=>1,'so.status'=>55,'_string'=>"(so.logistics_print_status = 1 or so.sendbill_print_status = 1) and (so.consign_time='1000-01-01' or DATEDIFF(NOW(),so.consign_time) <=  ".$setting_config['sales_print_time_range'].")"))->count();
//						  break;
                    default:
                        \Think\Log::write("unknown fast type:" . print_r($k, true) . ",value:" . print_r($v, true));
                        break;
                }
            }
            return $list_num_ar;
        }catch (BusinessLogicException $e){
            SE($e->getMessage());
        }catch(\PDOException $e){
            \Think\Log::write($this->name.'-getFastSearchNum-'.$e->getMessage());
            SE(self::PDO_ERROR);
        }catch (\Exception $e){
            \Think\Log::write($this->name.'-getFastSearchNum-'.$e->getMessage());
            SE(self::PDO_ERROR);
        }
    }
	public function setPromptSound($promptSound){
		try{
            $operator_id = get_operator_id();
            $user_data_model=D('Setting/UserData');
            $where=array('user_id'=>$operator_id,'type'=>1,'code'=>'stock_salesstockoutexamine_promptsound');
            if(empty($promptSound)&&$promptSound != 0){
                $get_cfg = $user_data_model->getUserData('data',$where);
                return $get_cfg;
            }else{
                $get_cfg=$user_data_model->where($where)->find();
                if($get_cfg){
                    $save['data']=$promptSound;
                    $user_data_model->updateUserData($save,$where);
                }else{
                    $add=array('user_id'=>$operator_id,'type'=>1,'code'=>'stock_salesstockoutexamine_promptsound','data'=>$promptSound);
                    $user_data_model->addUserData($add);
                }
            }
		}catch(\PDOException $e){
			$msg = $e->getMessage();
			\Think\Log::write($this->name.'-setPromptSound-'.$msg);
		}catch(\Exception $e){
			$msg = $e->getMessage();
			\Think\Log::write($this->name.'-setPromptSound-'.$msg);
		}
	}
	//获取打印批次数据
	public function getPrintBatchList($page = 1, $rows = 20, $search = array(), $sort = 'rec_id', $order = 'desc',$model ='') {
		try {
			$operator_id = get_operator_id();
			$where = " WHERE true ";
			$where_stockout_id = '';
			$page = intval($page);
			$rows = intval($rows);
			foreach ($search as $k => $v) {
				if ($v === "") continue;
				switch ($k) {
					case "batch_no":
						set_search_form_value($where, 'batch_no', $v, 'spb', 10, 'AND');
						break;
					case "order_num":
						set_search_form_value($where, 'order_num', $v, 'spb', 6, 'AND');
						break;
					case "order_mask":
						set_search_form_value($where, 'order_mask', $v, 'spb', 2, 'AND');
						break;
					case 'create_time_start':
						set_search_form_value($where, 'created', $v,'spb', 4,' AND ',' >= ');
						break;
					case 'create_time_end':
						set_search_form_value($where, 'created', $v,'spb', 4,' AND ',' <= ');
						break;
					case 'src_order_no':
						$result = $this->dealBatchCondition($k,$v,$where_stockout_id);
						if($result!=null){return $result;}
						break;
					case 'src_tid':
						$result = $this->dealBatchCondition($k,$v,$where_stockout_id);
						if($result!=null){return $result;}
						break;
					case 'logistics_no':
						$result = $this->dealBatchCondition($k,$v,$where_stockout_id);
						if($result!=null){return $result;}
						break;
					default:
						continue;
				}
			}
			$limit = ($page - 1) * $rows . "," . $rows;
			$sort  = $sort . " " . $order;
			$sort  = addslashes($sort);
			$where_create_id = '';
			if($operator_id != 1){$where_create_id=' AND creator_id='.$operator_id.' ';}
			$sql="SELECT spb.rec_id as id,spb.batch_no,spb.pick_list_no,spb.order_mask,spb.order_num,spb.queue,spb.created
					FROM stockout_print_batch spb ".$where.$where_create_id.$where_stockout_id." ORDER BY ".$sort." LIMIT ".$limit;
			$sql_count="SELECT count(1) as total FROM stockout_print_batch spb ".$where.$where_create_id.$where_stockout_id;
			$result        = $this->query($sql);
			$data['rows']  = $result;
			$result        = $this->query($sql_count);
			$data["total"] = $result["0"]["total"];
		} catch(\PDOException $e){
			$msg = $e->getMessage();
			\Think\Log::write($this->name.'-getPrintBatchList-'.$msg);
			$data=array('total' => 0, 'rows' => array());
		}catch(\Exception $e){
			$msg = $e->getMessage();
			\Think\Log::write($this->name.'-getPrintBatchList-'.$msg);
			$data=array('total' => 0, 'rows' => array());
		}
		return $data;
	}
	private function dealBatchCondition($key,$value,&$where_stockout_id){
		$where = array($key=>array('eq',$value));
		if($key=='src_tid'){
			$stockout_id = M('stockout_order')->alias('so')->field('so.stockout_id')->join('INNER JOIN sales_trade_order sto ON so.src_order_id = sto.trade_id')->where(array('sto.src_tid'=>array('eq',$value)))->find();
		}else{
			$stockout_id = M('stockout_order')->fetchSql(false)->field('stockout_id')->where($where)->find();
		}
		$stockout_id = $stockout_id['stockout_id'];
		if(!empty($stockout_id)){
			if(!empty($where_stockout_id)){
				$where_stockout_id .= ' AND LOCATE('.$stockout_id.',spb.queue) ';
			}else{
				$where_stockout_id = ' AND LOCATE('.$stockout_id.',spb.queue) ';
			}
		}else{
			return array('rows'=>array(),'total'=>'0');
		}
	}
	//添加打印批次
	public function addPrintBatch($stockout_ids,$print_type,$pick_list_no){
		try{
			$this->startTrans();
			$result = array('batch_no'=>'','picklist_no'=>'');
			$operator_id = get_operator_id();
			$stockout_ids = empty($stockout_ids)?'':$stockout_ids;
			$order_num = substr_count($stockout_ids,',') + 1;
			$print_types = ['1'=>'goods','2'=>'logistics','3'=>'sorting'];
			$print_type = intval(array_search($print_type,$print_types));
			$sql = 'select FN_SYS_NO("print_batch") batch_no';
			$stockout_print_batch_model = M('stockout_print_batch');
			if($print_type != 3){$batch_no = $this->query($sql);}
            $insert_print_batch_data = array(
				'batch_no'   =>empty($batch_no[0]['batch_no'])?'':$batch_no[0]['batch_no'],
				'pick_list_no'   =>'',
				'order_mask'   =>$print_type,
				'queue'   =>$stockout_ids,
				'status'   =>3,
				'order_num'   =>$order_num,
				'creator_id'  =>$operator_id,
			);
            if($print_type == 3){
				if($pick_list_no == ''){
					$pln_sql = 'select FN_SYS_NO("picklist") pick_list_no';
					$pick_list_no = $this->query($pln_sql);
					$pln = $pick_list_no[0]['pick_list_no'];
				}else{
					$pln = $pick_list_no;
				}
                $insert_print_batch_data['pick_list_no'] = $pln;
				$this->fetchSql(false)->where(array('stockout_id'=>array('in',$stockout_ids)))->save(array('picklist_no'=>$pln));
				$result['picklist_no'] = $pln;
                if($stockout_print_batch_model->where(['queue'=>$stockout_ids])->select()){
                    $stockout_print_batch_model->where(['queue'=>$stockout_ids])->setField(['pick_list_no'=>$pln]);
					$ret = $stockout_print_batch_model->where(['queue'=>$stockout_ids,'order_mask'=>3])->order('rec_id desc')->find();
					$result['batch_no'] = $ret['batch_no'];
					$this->commit();
					return $result;
                }else{
					$batch_no = $this->query($sql);
					$insert_print_batch_data['batch_no'] = $batch_no[0]['batch_no'];
					$stockout_print_batch_model->add($insert_print_batch_data);
					$this->fetchSql(false)->where(array('stockout_id'=>array('in',$stockout_ids)))->save(array('batch_no'=>$batch_no[0]['batch_no']));
					$result['batch_no'] = $batch_no[0]['batch_no'];
					$this->commit();
					return $result;
                }
            }else{
                $stockout_print_batch_model->add($insert_print_batch_data);
				$this->fetchSql(false)->where(array('stockout_id'=>array('in',$stockout_ids)))->save(array('batch_no'=>$batch_no[0]['batch_no']));
				$result['batch_no'] = $batch_no[0]['batch_no'];
				$this->commit();
				return $result;
            }
		}catch(\PDOException $e){
			$this->rollback();
			$msg = $e->getMessage();
			\Think\Log::write($this->name.'-addPrintBatch-'.$msg);
		}catch(\Exception $e){
			$this->rollback();
			$msg = $e->getMessage();
			\Think\Log::write($this->name.'-addPrintBatch-'.$msg);
		}
	}
	public function setQuickExamine($quickExamine){
		try{
			$operator_id = get_operator_id();
			$user_data_model=D('Setting/UserData');
			$where=array('user_id'=>$operator_id,'type'=>1,'code'=>'stock_salesstockoutexamine_quickexamine');
			$get_cfg=$user_data_model->where($where)->find();
			if($get_cfg){
				$save['data']=$quickExamine;
				$user_data_model->updateUserData($save,$where);
			}else{
				$add=array('user_id'=>$operator_id,'type'=>1,'code'=>'stock_salesstockoutexamine_quickexamine','data'=>$quickExamine);
				$user_data_model->addUserData($add);
			}
		}catch(\PDOException $e){
			$msg = $e->getMessage();
			\Think\Log::write($this->name.'-setQuickExamine-'.$msg);
		}catch(\Exception $e){
			$msg = $e->getMessage();
			\Think\Log::write($this->name.'-setQuickExamine-'.$msg);
		}
	}
	public function exportToExcel($id_list,$search,$type = 'excel'){
        try {
            D('Setting/EmployeeRights')->setSearchRights($search, 'warehouse_id', 2);

            $creator=session('account');
            $where_limit = $this->searchForm($search);
            $where_limit = ltrim($where_limit, ' AND ');

            if(empty($id_list)){
                $select_ids_sql = ' where so.src_order_type <> 1';
            }else{
                $select_ids_sql = " where so.stockout_id in (".$id_list.") ";
            }

            $point_number = get_config_value('point_number',0);
			$point_number = intval($point_number);
            $num = "CAST(sod.num AS DECIMAL(19,".$point_number.")) num,";
            $sql_pretreatment = $this->fetchSql()->distinct(true)->alias('so')->field('so.stockout_id as id')->join('LEFT JOIN stockout_order_detail sod ON  sod.stockout_id = so.stockout_id LEFT JOIN goods_spec gs ON sod.spec_id = gs.spec_id LEFT JOIN goods_goods gg ON gs.goods_id = gg.goods_id')->where($where_limit)->select();

            $sql = " select so.stockout_id AS id,so.warehouse_id ,so.stockout_no, so.status,so.remark,"
                . " so.modified,so.operator_id, so.src_order_type mode,so.created, "
                . " he.fullname creator_name,cgu.name AS base_unit_id, gb.brand_name AS brand_id,sod.cost_price, ".$num
                . " cw.name warehouse_name,cwp.position_no,"
                . " gs.spec_no,gs.spec_code,gs.barcode,gs.spec_name,gg.goods_no,gg.goods_name"
                . " FROM stockout_order so"
                . " LEFT JOIN stockout_order_detail sod ON so.stockout_id = sod.stockout_id "
                . " LEFT JOIN goods_spec gs ON gs.spec_id = sod.spec_id "
                . " LEFT JOIN goods_goods gg ON gg.goods_id = gs.goods_id "
                . " LEFT JOIN goods_brand gb ON gg.brand_id = gb.brand_id "
                . " LEFT JOIN cfg_goods_unit cgu ON sod.base_unit_id = cgu.rec_id "
                . " LEFT JOIN hr_employee he ON he.employee_id = so.operator_id"
                . " LEFT JOIN cfg_warehouse cw ON cw.warehouse_id = so.warehouse_id"
                . " LEFT JOIN cfg_warehouse_position cwp ON cwp.rec_id = sod.position_id and cwp.warehouse_id = so.warehouse_id"
                . " JOIN (" . $sql_pretreatment . ") page ON page.id = so.stockout_id ".$select_ids_sql." ORDER BY id DESC";

            $data = $this->query($sql);
            $stockout_type=array(
                '2'=>'调拨出库',
                '3'=>'采购退货出库',
                '4'=>'盘亏出库',
                '7'=>'其它出库',
                '11'=>'初始化出库',
                '13'=>'委外出库',
            );
            $stockout_status=array(
                '5'=>'已取消',
                '48'=>'编辑中',
                '57'=>'待推送',
                '53'=>'同步失败',
                '55'=>'已审核',
                '56'=>'推送失败',
                '60'=>'待出库',
                '95'=>'已发货',
                '110'=>'已完成',
            );
            for($i=0;$i<count($data);$i++){
                $data[$i]['mode']=$stockout_type[$data[$i]['mode']];
                $data[$i]['status']=$stockout_status[$data[$i]['status']];
            }
            $num = workTimeExportNum($type);
            if (count($data) > $num) {
                if($type == 'csv'){
                    SE(self::EXPORT_CSV_ERROR);
                }
                SE('导出的详情数据超过设定值，8:00-19:00可以导出1000条，其余时间可以导出4000条!');
            }

            $excel_header = D('Setting/UserData')->getExcelField('Stock/StockOut', 'stockoutexport');
            $title = '出库单';
            $filename = '出库单';
            foreach ($excel_header as $v) {
                $width_list[] = 20;
            }
            if($type == 'csv') {
				$ignore_arr = array('商家编码','货品编号','货品名称','货品简称','规格码','规格名称','条形码','品牌','仓库名称','货位','出库人员');
				ExcelTool::Arr2Csv($data, $excel_header, $filename, $ignore_arr);
            }else {
                ExcelTool::Arr2Excel($data, $title, $excel_header, $width_list, $filename, $creator);
            }
        } catch (\PDOException $e) {
            \Think\Log::write($e->getMessage());
            SE(parent::PDO_ERROR);
        } catch (BusinessLogicException $e) {
            SE($e->getMessage());
        } catch (\Exception $e) {
            \Think\Log::write($e->getMessage());
            SE(parent::PDO_ERROR);
        }
    }
    public function searchForm($search){

        $where_limit = '';
        foreach ($search as $k => $v) {
            if ($v === '') continue;
            switch ($k) {
                case 'stockout_no'://出库单号
                    set_search_form_value($where_limit, $k, $v, 'so', 1, ' AND ');
                    break;
                case 'src_order_no': //来源单号
                    set_search_form_value($where_limit, $k, $v, 'so', 1, ' AND ');
                    break;
                case 'status': //出库单状态
                    set_search_form_value($where_limit, $k, $v, 'so', 2,  ' AND ');
                    break;
                case 'warehouse_id': //仓库
                    set_search_form_value($where_limit, $k, $v, 'so', 2,  ' AND ');
                    break;
                case 'src_order_type'://出库单类别
                    set_search_form_value($where_limit, $k, $v, 'so', 2,  ' AND ');
                    break;
                case 'operator_id'://经办人
                    set_search_form_value($where_limit, $k, $v, 'so', 2,  ' AND ');
                    break;
            }
        }
        return $where_limit;
    }
	public function setExaminePrintLogistics($examinePrintLogistics){
		try{
			$operator_id = get_operator_id();
			$user_data_model=D('Setting/UserData');
			$where=array('user_id'=>$operator_id,'type'=>1,'code'=>'stock_salesstockoutexamine_printlogistics');
			$get_cfg=$user_data_model->where($where)->find();
			if($get_cfg){
				$save['data']=$examinePrintLogistics;
				$user_data_model->updateUserData($save,$where);
			}else{
				$add=array('user_id'=>$operator_id,'type'=>1,'code'=>'stock_salesstockoutexamine_printlogistics','data'=>$examinePrintLogistics);
				$user_data_model->addUserData($add);
			}
		}catch(\PDOException $e){
			$msg = $e->getMessage();
			\Think\Log::write($this->name.'-setExaminePrintLogistics-'.$msg);
		}catch(\Exception $e){
			$msg = $e->getMessage();
			\Think\Log::write($this->name.'-setExaminePrintLogistics-'.$msg);
		}
	}
    public function setExaminePrintTag($examinePrintTag){
        try{
            $operator_id = get_operator_id();
            $user_data_model=D('Setting/UserData');
            $where=array('user_id'=>$operator_id,'type'=>1,'code'=>'stock_salesstockoutexamine_printtag');
            $get_cfg=$user_data_model->where($where)->find();
            if($get_cfg){
                $save['data']=$examinePrintTag;
                $user_data_model->updateUserData($save,$where);
            }else{
                $add=array('user_id'=>$operator_id,'type'=>1,'code'=>'stock_salesstockoutexamine_printtag','data'=>$examinePrintTag);
                $user_data_model->addUserData($add);
            }
        }catch(\PDOException $e){
            $msg = $e->getMessage();
            \Think\Log::write($this->name.'-setExaminePrintTag-'.$msg);
        }catch(\Exception $e){
            $msg = $e->getMessage();
            \Think\Log::write($this->name.'-setExaminePrintTag-'.$msg);
        }
    }
}