<?php
namespace Stock\Controller;

use Common\Controller\BaseController;
use Common\Common\DatagridExtention;
use Think\Exception\BusinessLogicException;
use Stock\Model\StockoutOrderDetailModel;
use Common\Common\UtilTool;
class SalesStockoutExamineController extends BaseController
{
    public function initStockoutExamine()
    {
		$src_order_no = I('get.src_order_no');
        $id_list = DatagridExtention::getIdList(array('tool_bar','form','datagrid','add','edit','check_button','setPrinter','print_dialog'));
        
        $fields = get_field('Stock/SalesStockoutExamine','salesstockoutexamine');
        $operator_id = get_operator_id();
        $user_data_model=D('Setting/UserData');
        //组装查询条件
        $user_data_where = array(
            'quick_examine'             => array('user_id'=>$operator_id,'type'=>1,'code'=>'stock_salesstockoutexamine_quickexamine'),
            'prompt_sound'              => array('user_id'=>$operator_id,'type'=>1,'code'=>'stock_salesstockoutexamine_promptsound'),
            'pic_preview'               => array('user_id'=>$operator_id,'type'=>1,'code'=>'stock_salesstockoutexamine_salesstockoutexamine_query'),
            'examine_print_logistics'   => array('user_id'=>$operator_id,'type'=>1,'code'=>'stock_salesstockoutexamine_printlogistics'),
            'examine_print_tag'         => array('user_id'=>$operator_id,'type'=>1,'code'=>'stock_salesstockoutexamine_printtag'),
        );
        //是否开启快速验货
        $quick_examine = $user_data_model->getUserData('data',$user_data_where['quick_examine']);
        $quick_examine = empty($quick_examine['data'])&&!is_numeric($quick_examine['data'])?'0':$quick_examine['data'];
        //是否开启声音提示
        $prompt_sound = $user_data_model->getUserData('data',$user_data_where['prompt_sound']);
        $prompt_sound = empty($prompt_sound['data'])&&!is_numeric($prompt_sound['data'])?'1':$prompt_sound['data'];
        //是否开启图片预览
        $pic_preview = $user_data_model->getUserData('data',$user_data_where['pic_preview']);
        $pic_preview = json_decode($pic_preview['data']);
        foreach($pic_preview as $key=>$value){if($key == 'pic_name'){$pic_preview = intval($value);break;}}
        if(empty($pic_preview)&&!is_numeric($pic_preview)) $pic_preview = '1';
        if($pic_preview == 0){$fields['图片']['hidden'] = true;}
        //是否开启验货后立即打印物流单
        $examine_print_logistics = $user_data_model->getUserData('data',$user_data_where['examine_print_logistics']);
        $examine_print_logistics = empty($examine_print_logistics['data'])&&!is_numeric($examine_print_logistics['data'])?'0':$examine_print_logistics['data'];
        //是否开启验货后立即打印吊牌
        $examine_print_tag = $user_data_model->getUserData('data',$user_data_where['examine_print_tag']);
        $examine_print_tag = empty($examine_print_tag['data'])&&!is_numeric($examine_print_tag['data'])?'0':$examine_print_tag['data'];
        $datagrid = array(
            'id'=>$id_list['datagrid'],
            'options'=> array(
                'title' => '',
                'url'   => '',
                'toolbar' => "#{$id_list['tool_bar']}",
                'fitColumns'   => true,
                'singleSelect'=>true,
                'ctrlSelect'=>false,
                'pagination'=>false,
            ),
            'fields' => $fields,
            'class' => 'easyui-datagrid',
            'style'=>"overflow:scroll",
        );
        $system_info =M('dict_url')->alias('u')->field("u.url_id AS id, u.name AS text,IF(u.is_leaf=0 OR u.controller IS NULL OR u.controller='','', CONCAT('index.php/',u.module,'/',u.controller,'/',u.action,IF(u.type=2,CONCAT('?dialog=',LOWER(u.controller)),''))) href, u.parent_id,lower(module) module,is_leaf")->where(array('u.url_id'=>8))->order('u.parent_id ASC, u.sort_order DESC')->find();
        $params['datagrid']     = array('url' => U("SalesStockoutExamine/getStockoutOrderDetail"),'id' => $id_list['datagrid']);
        $params['select']       = array('id'  => 'flag_set_dialog', 'url' => U('SalesStockoutExamine/showGoodsList'), 'title' => '条码选择货品');
        $params['set_conf']     = array('title'  => $system_info['text'], 'url' =>$system_info['href']."&tab_type=库存设置&config_name=stockout_examine_auto_consign&info=验货完成是否确认发货");
        $params['get_config']   = array('pic_preview'  => $pic_preview, 'prompt_sound' =>$prompt_sound, 'quick_examine' =>$quick_examine, 'examine_print_logistics' =>$examine_print_logistics, 'examine_print_tag' =>$examine_print_tag);
		$where = array();
		D('Setting/EmployeeRights')->setSearchRights($where,'shop_id',1);
		D('Setting/EmployeeRights')->setSearchRights($where,'warehouse_id',2);
		if(empty($where['warehouse_id'])) $where['warehouse_id'] = '0';
		if(empty($where['shop_id'])) $where['shop_id'] = '0';		
        $setting_config = get_config_value(array('sales_print_time_range'),array(7));
		$operator_id = get_operator_id();
		if($operator_id == 1){
                try {
                    $examine_num = D('Stock/StockOutOrder')->fetchSql(false)->alias('so')->join('inner join sales_trade st on st.trade_id = so.src_order_id')->where(array('so.consign_status' => array('exp', '&1=0'), 'logistics_print_status' => 1, 'so.warehouse_type' => 1, 'so.src_order_type' => 1,'so.status'=> array('between',array('55','90')),  '_string' => "so.consign_time='1000-01-01' or DATEDIFF(NOW(),so.consign_time) <=  " . $setting_config['sales_print_time_range']))->count(1);
                }catch (\PDOException $e){
                    \Think\Log::write( __CONTROLLER__.'-SalesStockOutExamine-'.$e->getMessage());
                    $examine_num=0;
                }
		}else{
                try{
                    $examine_num = D('Stock/StockOutOrder')->fetchSql(false)->alias('so')->join('inner join sales_trade st on st.trade_id = so.src_order_id')->where(array('so.consign_status'=>array('exp','&1=0'),'logistics_print_status'=>1,'so.warehouse_type'=>1,'so.src_order_type'=>1,'so.status'=> array('between',array('55','90')),'st.shop_id'=>array('in',$where['shop_id']),'so.warehouse_id'=>array('in',$where['warehouse_id']),'_string'=>"so.consign_time='1000-01-01' or DATEDIFF(NOW(),so.consign_time) <=  ".$setting_config['sales_print_time_range']))->count(1);
                }catch (\PDOException $e){
                    \Think\Log::write( __CONTROLLER__.'-SalesStockOutExamine-'.$e->getMessage());
                    $examine_num=0;
                }
		}

		$this->assign('examine_num',$examine_num);
		$this->assign('src_order_no',$src_order_no);
		$this->assign('datagrid',$datagrid);
        $this->assign('id_list',$id_list);
        $this->assign('params',json_encode($params));
        $this->display('sales_stockout_examine_edit');
    }
    public function  getTagPrintInfo(){
        $printInfo = array();
        $user_id = get_operator_id();
        $type = 7;
        $code = 'stalls_print_set';
        $stallsPrintInfo = $this->getUserData($type,$code);
        if(empty($stallsPrintInfo)){
            $printInfo = ['tag_printer'=>'','tag_template_url'=>''];
        }else{
            $model = D('Setting/PrintTemplate');
            $fields = 'rec_id as id,title as name,content';

            $template_id = $stallsPrintInfo['tag_template'];
            $hasDefTemp = $model->field($fields)->where(array('rec_id'=>$template_id))->select();
            if(empty($hasDefTemp))  $tag_template_url = '';
            $templateInfo = json_decode($hasDefTemp[0]['content'],true);
            $tag_template_url = $templateInfo['custom_area_url'];
            $tag_printer = $stallsPrintInfo['tag_printer'];
            $printInfo = ['tag_printer'=>$tag_printer,'tag_template_url'=>$tag_template_url];
        }
        $this->ajaxReturn($printInfo);
    }
    public function getStockoutOrderDetail($trade_no){
        try {
            $result = array(
                'status'=>0,
                'stockout_order_info'=>array(),
                'stockout_order_detial_goods_info'=>array() ,
                'msg'=>'成功' 
            );
			if(empty($trade_no)){
				SE('出库单不存在');
			}
			
            $stockout_order_info = array();
            $stockout_order_detail_goods_info = array();
            $sales_stockout_fields = array(
                'cs.shop_name',
                'st.trade_no',
                ' CAST(SUM(sod.num) as DECIMAL(19,0)) order_goods_num',
                'so.stockout_id',
				'so.stockout_no',
                'so.status',
                'so.consign_status',
                'so.src_order_type',
                'so.warehouse_id',
                'so.warehouse_type',
                'so.block_reason'
            );
            $sales_stockout_conditions = array(
                'st.trade_no'=>$trade_no,
				'so.src_order_type'=>1
            );
            $stockout_order_info = D('Stock/StockOutOrder')->getSalesStockoutOrderLeftSalesTrade($sales_stockout_fields,$sales_stockout_conditions);
            $stockout_order_info = isset($stockout_order_info[0])?$stockout_order_info[0]:array();
			$suite_no = D('Trade/Trade')->alias('st')->field('suite_no')->join('left join sales_trade_order sto on sto.trade_id = st.trade_id')->where(array('st.trade_no'=>$trade_no))->group('suite_no')->select();
			if(!empty($suite_no)){
				$result['suite_no'] = $suite_no;
			}else{
				$result['suite_no'] = "";
			}
            if(empty($stockout_order_info)){
				$stockout_order_info = D('Stock/StockOutOrder')->getSalesStockoutOrderLeftSalesTrade($sales_stockout_fields,array('st.logistics_no'=>$trade_no,'so.src_order_type'=>1));
				$stockout_order_info = isset($stockout_order_info[0])?$stockout_order_info[0]:array();
				$suite_no = D('Trade/Trade')->alias('st')->field('suite_no')->join('left join sales_trade_order sto on sto.trade_id = st.trade_id')->where(array('st.logistics_no'=>$trade_no))->group('suite_no')->select();
				if(!empty($suite_no)){
					$result['suite_no'] = $suite_no;
				}else{
					$result['suite_no'] = "";
				}
				if(empty($stockout_order_info)){
					$result['solve_way'] = "订单编号填写错误！";
					SE('出库单不存在');
				}
            }
            if((int)$stockout_order_info['src_order_type']<>1){
				$result['solve_way'] = "只有销售出库单才能验货！";
                SE('出库单不是销售出库单');
            }
            if((int)$stockout_order_info['status']>=95 ){
				$result['solve_way'] = "";
                SE('订单已发货');
            }
            if ((int)$stockout_order_info['status']<55){
				$result['solve_way'] = "只有出库单状态是已审核和部分发货的订单才能验货！";
                SE('出库单状态不正确');
            }
            if((int)$stockout_order_info['consign_status']&1){
				$result['solve_way'] = "不需要再次验货！";
               SE('订单已验货');
            }
            if((int)$stockout_order_info['warehouse_id']<=0){
				$result['solve_way'] = "";
                SE('出库单未指定出库仓库');
            }
            if((int)$stockout_order_info['warehouse_type']<>1){
				$result['solve_way'] = "";
                SE('委外订单不能出库验货');
            }
            if((int)$stockout_order_info['block_reason']<>0){
				$result['solve_way'] = "拦截出库";
                $block_reason = D('Stock/SalesStockOut')->getBlockReason($stockout_order_info['block_reason']);
                SE("出库单[{$block_reason}]拦截出库");
            }
			$point_number = get_config_value('point_number',0);		
			$num = 'CAST(SUM(sod.num) as DECIMAL(19,'.$point_number.')) num';
			$check_num = 'CAST(0 as DECIMAL(19,'.$point_number.')) check_num';
            $stockout_order_detail_fields = array(
                'sod.rec_id',
                'sod.spec_id',
                'sod.spec_name',
                'sod.spec_code',
                'sod.spec_no',
                //'sd.goods_id',
                'sod.goods_no',
                'sod.goods_name',
                'sod.remark',
                //'sd.is_examined',
                //'sd.scan_type',
                $num,
                //'IF(sd.is_examined,SUM(sd.num),0) AS check_num',
                $check_num,
                'gs.barcode',
                'gs.img_url AS pic_name',
                'gs.is_not_need_examine',
                '0 as num_status',
                'sod.scan_type'
                //'gs.is_sn_enable',
                //'gs.is_not_need_examine',
                
            );
            $stockout_order_detail_condtions = array(
                'sod.stockout_id'  => $stockout_order_info['stockout_id']
            );
            $stockout_order_detail_goods_info = D('Stock/StockoutOrderDetail')->getSalesStockoutOrderDetailGoodsinfo($stockout_order_detail_fields,$stockout_order_detail_condtions);
            $stockout_order_detail_goods_info = isset($stockout_order_detail_goods_info[0])?$stockout_order_detail_goods_info:array();
            $order_detail_goods_info = array(
                'total'=>0,
                'rows'=>$stockout_order_detail_goods_info  
            );
            $result['stockout_order_info']=$stockout_order_info;
            $result['stockout_order_detial_goods_info'] = $order_detail_goods_info;
        }catch (BusinessLogicException $e) {
			  $result["status"] = 2;
			  $result["stock"] = $stockout_order_info;
			  $result["msg"] = $e->getMessage();
		} catch (\Exception $e) {
            $msg = $e->getMessage();
            \Think\Log::write(__CONTROLLER__.'-getStockoutOrderDetail-'.$msg);
            $result['status'] = 1;
            $result['msg']='未知错误,请联系管理员';
        }
        $this->ajaxReturn($result);
    }
    public function examineGoodsByBarcode($barcode,$scanType,$tradeNo)
    {
        try {
            $result = array(
                'status'=>0,
                'match_goods_list'=>array(),
                'msg'=>'成功'
            );
            $goods_list = array();
           /*  M()->execute("set @tmp_goods_name='',@tmp_short_name='',@tmp_merchant_no='',@tmp_spec_name='',@tmp_spec_code='',@tmp_goods_id='',@tmp_spec_id='',@tmp_sn_enable=0;");
            $res = M()->query('select @tmp_short_name');
            \Think\Log::write('test---'.print_r($res,true)); */
            $barcode = trim($barcode);
            if($scanType == 'barcode'){
                $goods_info = D('Goods/GoodsBarcode')->getGoodsByBarcode($barcode);
                if(empty($goods_info)){
                    SE('没有查询到有关条码的货品信息');
                }
                $result['match_goods_list']=array(
                    'total'=>0,
                    'rows'=>$goods_info
                );
                $barcode = "'".$barcode."'";
                $sql="SELECT sod.price,gb.brand_name,gs.prop1,gs.prop2,gs.prop3,gs.prop4
                FROM stockout_order so
                LEFT JOIN goods_spec gs ON gs.barcode = {$barcode}
                LEFT JOIN goods_goods gg ON gg.goods_id = gs.goods_id
                LEFT JOIN stockout_order_detail sod ON sod.stockout_id=so.stockout_id AND  sod.spec_no = gs.spec_no
                LEFT JOIN goods_brand gb ON gb.brand_id = gg.brand_id
                WHERE so.src_order_no='".$tradeNo."'";
                $goods_list=M()->query($sql);
            }else{
                $less_good = D('Purchase/StallsOrderDetail')->field('trade_no')->where(array('unique_code'=>$barcode))->find();
                if($less_good['trade_no'] != $tradeNo){
                    SE('该唯一码不属于此订单');
                }else{
                    $goods_info = D('Purchase/StallsOrderDetail')->alias('sod')->field('spec_no')->join('LEFT JOIN goods_spec gs ON gs.spec_id = sod.spec_id')->where(array('unique_code'=>$barcode))->select();
                }
                if(empty($goods_info)){
                    SE('没有查询到有关唯一码的货品信息');
                }
                $result['match_goods_list']=array(
                    'total'=>0,
                    'rows'=>$goods_info
                );
                $unique_code = "'".$barcode."'";
                $sql="SELECT sod.price,gb.brand_name,gs.prop1,gs.prop2,gs.prop3,gs.prop4
                FROM stockout_order so
                LEFT JOIN stalls_less_goods_detail slgd ON slgd.unique_code = {$unique_code}
                LEFT JOIN goods_spec gs ON gs.spec_id = slgd.spec_id
                LEFT JOIN goods_goods gg ON gg.goods_id = gs.goods_id
                LEFT JOIN stockout_order_detail sod ON sod.stockout_id=so.stockout_id AND  sod.spec_no = gs.spec_no
                LEFT JOIN goods_brand gb ON gb.brand_id = gg.brand_id
                WHERE so.src_order_no='".$tradeNo."'";
                $goods_list=M()->query($sql);
            }

            $result['match_goods_list']['rows'][0]['price'] = $goods_list[0]['price'];
            $result['match_goods_list']['rows'][0]['brand_name'] = $goods_list[0]['brand_name'];
            $result['match_goods_list']['rows'][0]['prop1'] = $goods_list[0]['prop1'];
            $result['match_goods_list']['rows'][0]['prop2'] = $goods_list[0]['prop2'];
            $result['match_goods_list']['rows'][0]['prop3'] = $goods_list[0]['prop3'];
            $result['match_goods_list']['rows'][0]['prop4'] = $goods_list[0]['prop4'];

        }catch(BusinessLogicException $e){
			$msg=$e->getMessage();
			$result['status']=1;
			$result['msg']=$msg;
		}catch (\Exception $e) {
            $msg = $e->getMessage();
            \Think\Log::write(__CONTROLLER__.'-examineGoodsByBarcode-'.$msg);
            $result['status'] = 1;
            $result['msg']=$msg;
        }
        $this->ajaxReturn($result);
    }
    public function showGoodsList($parent_datagrid_id,$parent_object,$goods_list_dialog){
        $id_list = DatagridExtention::getIdList(array('datagrid','tool_bar'),$parent_object);
        
        $fields = get_field('Stock/SalesStockoutExamine','goodslist');
        
        $datagrid = array(
            'id'=>$id_list['datagrid'],
            'options'=> array(
                'title' => '',
                'url'   => '',
                'toolbar' => "#{$id_list['tool_bar']}",
                'fitColumns'   => true,
                'singleSelect'=>true,
                'ctrlSelect'=>false,
                'pagination'=>false,
            ),
            'fields' => $fields,
            'class' => 'easyui-datagrid',
            'style'=>"overflow:scroll",
        );
        $params = array(
            'datagrid'=>array(
                'id' =>$id_list['datagrid']
            ),
            'parent_datagrid'=>array(
                'id'=>$parent_datagrid_id  
            ),
            'parent_object'=>$parent_object,
            'goods_list_dialog'=>array('id'=>$goods_list_dialog)
        );
        $this->assign('params',json_encode($params));
        $this->assign("datagrid",$datagrid);
        $this->assign('id_list',$id_list);
        $this->display('goods_list');
    }
    public function consignCheck($stockout_id,$check_goods_list)
    {
        try {
            $result = array(
                'status' =>0,
                'info' =>'成功'
            );
            $user_id = get_operator_id();
            $printerData = D('Setting/UserData')->field(array('data'))->where(array('user_id'=>$user_id,'type'=>7,'code'=>'examine_print_set'))->select();
            $examine_print_logistics = D('Setting/UserData')->getUserData('data',array('user_id'=>$user_id,'type'=>1,'code'=>'stock_salesstockoutexamine_printlogistics'));
            $examine_print_logistics = empty($examine_print_logistics['data'])&&!is_numeric($examine_print_logistics['data'])?'0':$examine_print_logistics['data'];
            if(empty($printerData)&&$examine_print_logistics){
                $result['status'] = 1;
                $result['info'] = '请先选择打印机后再继续验货';
                $this->ajaxReturn($result);
            }
            $check_rows = I('post.check_goods_list','',C('JSON_FILTER'));
//             \Think\Log::write('changtao ex '.print_r($check_rows,true).'---orignal----'.print_r($temp_check,true));
            D('Stock/StockOutOrder')->consignCheckSalesStockoutOrder($stockout_id,$check_rows);
        }catch(BusinessLogicException $e){
			$msg=$e->getMessage();
			$result['status']=1;
			$result['info']=$msg;
		}catch (\Exception $e) {
            $msg = $e->getMessage();
            \Think\Log::write(__CONTROLLER__.'-consignCheck-'.$msg);
            $result['status'] =1;
            $result['info'] =$msg;
        }
        if($result['status'] ==0){
            try{
                $auto_consign = get_config_value('stockout_examine_auto_consign',0);
                $stockout_info =D('Stock/StockOutOrder')->where(array('stockout_id'=>$stockout_id))->field(array('status','consign_status'))->find();
                $success = array();
                $error_list = array();
                if($auto_consign && $stockout_info['status']<95){
                    D('Stock/SalesStockOut')->consignStockoutOrder(intval($stockout_id),$error_list,$success);
                    if(!empty($error_list)){
                        $result['status'] = 2;
                        $result['info']='验货成功，自动发货失败：'.$error_list[0]['msg'];
                    }else{
                        if($success['status'] >=95){
                            $result['info'] = '验货并自动发货成功';
                        }else{
                            $result['status'] = 2;
                            $result['info'] = '验货成功，自动发货失败';
                        }
                    }
                }
            }catch(BusinessLogicException $e){
                $result['status'] = 1;
                $result['info'] = $e->getMessage();
            }catch(\Exception $e){
                \Think\Log::write($e->getMessage());
                $result['status'] = 1;
                $result['info'] = $this::UNKNOWN_ERROR;
            }
        }

        $this->ajaxReturn($result);
    }
	public function getSuiteInfo(){
		try{
			$result = array(
                'status'=>0,
				'info'=>array(),
                'msg'=>'成功' 
            );
			$spec_no = I('post.no','',C('JSON_FILTER'));
			$suite = D('Goods/GoodsSuite')->field('suite_id')->where(array('suite_no'=>$spec_no))->select();
			$suite_info = D('Goods/GoodsSuite')->getGoodsSuiteDetailById($suite[0]['suite_id']);
			$result['info'] = $suite_info;
		}catch(BusinessLogicException $e){
			 $result = array("status" => 1, "info" => $e->getMessage());
		}catch (\Exception $e) {
            $msg = $e->getMessage();
            \Think\Log::write(__CONTROLLER__.'-getStockoutOrderDetail-'.$msg);
            $result['status'] = 1;
            $result['info']='未知错误,请联系管理员';
        }
		$this->ajaxReturn($result);
	}
    public function getFields($mode,$key){
        $fields=get_field($mode,$key);
        foreach ($fields as $k => $v)
        {
            if (isset($v['hidden'])&&$v['hidden']==true)
            {
                unset($fields[$k]);
            }
            //$list[]=array('name'=>$v['field'],'value'=>1,'text'=>$k,'frozen'=>$v['frozen']==1?1:0);
        }
        $this->ajaxReturn($fields);
    }
    public function setPromptSound($promptSound){
        try{
            D('Stock/StockOutOrder')->setPromptSound($promptSound);
        }catch(\Exception $e){
            $msg = $e->getMessage();
            \Think\Log::write(__CONTROLLER__.'-setPromptSound-'.$msg);
        }
    }
    public function setQuickExamine($quickExamine){
        try{
            D('Stock/StockOutOrder')->setQuickExamine($quickExamine);
        }catch(\Exception $e){
            $msg = $e->getMessage();
            \Think\Log::write(__CONTROLLER__.'-setQuickExamine-'.$msg);
        }
    }
	public function getExamineNum(){
		try{
			$result = array('status'=>0,'info'=>'');
            $where = array();
			D('Setting/EmployeeRights')->setSearchRights($where,'shop_id',1);
			D('Setting/EmployeeRights')->setSearchRights($where,'warehouse_id',2);
			if(empty($where['warehouse_id'])) $where['warehouse_id'] = '0';
			if(empty($where['shop_id'])) $where['shop_id'] = '0';		
			$setting_config = get_config_value(array('sales_print_time_range'),array(7));
			$operator_id = get_operator_id();
			if($operator_id == 1){
				$examine_num = D('Stock/StockOutOrder')->fetchSql(false)->alias('so')->join('inner join sales_trade st on st.trade_id = so.src_order_id')->where(array('so.consign_status'=>array('exp','&1=0'),'logistics_print_status'=>1,'so.warehouse_type'=>1,'so.src_order_type'=>1,'so.status'=> array('between',array('55','90')),'_string'=>"so.consign_time='1000-01-01' or DATEDIFF(NOW(),so.consign_time) <=  ".$setting_config['sales_print_time_range']))->count(1);
			}else{
				$examine_num = D('Stock/StockOutOrder')->fetchSql(false)->alias('so')->join('inner join sales_trade st on st.trade_id = so.src_order_id')->where(array('so.consign_status'=>array('exp','&1=0'),'logistics_print_status'=>1,'so.warehouse_type'=>1,'so.src_order_type'=>1,'so.status'=> array('between',array('55','90')),'st.shop_id'=>array('in',$where['shop_id']),'so.warehouse_id'=>array('in',$where['warehouse_id']),'_string'=>"so.consign_time='1000-01-01' or DATEDIFF(NOW(),so.consign_time) <=  ".$setting_config['sales_print_time_range']))->count(1);
			}
			$result['info'] = $examine_num;
        }catch(\Exception $e){
            $msg = $e->getMessage();
			$result['info'] = $msg;
			$result['status'] = 1;
            \Think\Log::write(__CONTROLLER__.'-getExamineNum-'.$msg);
        }
		$this->ajaxReturn($result);
	}
    public function setExaminePrintLogistics($examinePrintLogistics){
        try{
            D('Stock/StockOutOrder')->setExaminePrintLogistics($examinePrintLogistics);
        }catch(\Exception $e){
            $msg = $e->getMessage();
            \Think\Log::write(__CONTROLLER__.'-setExaminePrintLogistics-'.$msg);
        }
    }
    public function setExaminePrintTag($examinePrintTag){
        try{
            D('Stock/StockOutOrder')->setExaminePrintTag($examinePrintTag);
        }catch(\Exception $e){
            $msg = $e->getMessage();
            \Think\Log::write(__CONTROLLER__.'-setExaminePrintTag-'.$msg);
        }
    }
    public function getExaminePrintData($stockout_id){
        $result = array();
        $result['status'] = 0;
        $result['msg'] = 'success';
        $printLogisticsData = array();
        try{
            $printLogisticsData = $this->getLogisticsInfo($stockout_id);
            if($printLogisticsData['status'] !=0){
                $result['status'] = 2;
                $result['msg'] = $printLogisticsData['msg'];
                return $this->ajaxReturn($result);
            }
            $printDataInfo = $printLogisticsData['data'];
            $user_id = get_operator_id();
            $stockout_id = $printDataInfo['stockout_id'];
            $src_order_no = $printDataInfo['src_order_no'];
            $logistics_id = $printDataInfo['logistics_id'];
            $standerTemplateUrl = $printDataInfo['stander_template_url'];
            $printLogisticsData['data']['waybill_print_info'] = -1;
            $printLogisticsData['data']['custom_template_url'] = '';
            if($standerTemplateUrl != ''){
                $res = D('Stock/WayBill','Controller')->newGetWayBill($stockout_id,$logistics_id,$standerTemplateUrl,false);
                if($res['status'] !=0){//res 返回三种状态：0->单号获取成功 1->获取单号前校验失败 2->接口调用成功，但是未返回单号
                    if(count($res['data']['fail']) > 0){
                        $result['msg'] = $res['data']['fail'][0]['msg'];
                    }else{
                        $result['msg'] = $res['msg'];
                    }
                    $result['status'] = 1;
                    return $this->ajaxReturn($result);
                }
                $printLogisticsData['data']['waybill_print_info'] = $res['data']['success'][$stockout_id];
                // 自定义模板
                $userCustomTemplate = D('Setting/UserData')->field(array('data'))->where(array('user_id'=>$user_id,'type'=>7,'code'=>''))->select();
                if(!empty($userCustomTemplate)){
                    $userCustomTemplate = json_decode($userCustomTemplate[0]['data'],true);
                    if(!empty($userCustomTemplate[$logistics_id])){
                        $templateId = $userCustomTemplate[$logistics_id];
                        $customTemplateUrl = D('Setting/PrintTemplate')->where(array("rec_id"=>$templateId))->select();
                        $templateContent = $customTemplateUrl[0]['content'];
                        $printLogisticsData['data']['custom_template_url'] = json_decode($templateContent,true)['custom_area_url'];
                        $logisticsPrinter = json_decode($templateContent,true)['default_printer'];
                        if(!empty($logisticsPrinter)){
                            $printLogisticsData['data']['logistic_printer'] = $logisticsPrinter;
                        }
                    }else{
                        $printLogisticsData['data']['custom_template_url'] = '';
                    }
                }
                // 自定义区数据
                $goods = D('Stock/StockOutOrder')->getStockoutOrderDetailPrintData($stockout_id);
                foreach($goods as $v){
                    if(!isset($no[$v['id']]))
                        $no[$v['id']] = 0;
                    $v['no'] = ++$no[$v['id']];
                    $detail[$v['id']][] = $v;
                    D('StockSalesPrint','Controller')->judgeConditions($detail[$v['id']],$v);
                }
                $goods = $detail;
                D('StockSalesPrint','Controller')->composeSuiteData($goods);
                $printLogisticsData['data']['goods'] = $goods;
            }
            $search = ['src_order_no'=>$src_order_no];
            $stockoutOrderData = D('StockOutOrder')->searchStockoutList(1, 20, $search, 'id', 'desc', 'stockoutPrint');
            $row = $stockoutOrderData['rows'][0];
            $printLogisticsData['data']['row'] = $row;
            $result['data'] = $printLogisticsData['data'];
        }catch(\PDOException $e){
            $msg = $e->getMessage();
            \Think\Log::write(__CONTROLLER__.'--getExaminePrintData--'.$e->getMessage());
            $result['status'] = 1;
            $result['msg'] = $msg;
        }catch(BusinessLogicException $e){
            $msg = $e->getMessage();
            \Think\Log::write(__CONTROLLER__.'--getExaminePrintData--'.$e->getMessage(),\Think\Log::WARN);
            $result['status'] = 1;
            $result['msg'] = $msg;
        }catch(\Exception $e){
            $msg = $e->getMessage();
            \Think\Log::write(__CONTROLLER__.'--getExaminePrintData--'.$e->getMessage());
            $result['status'] = 1;
            $result['msg'] = $msg;
        }
        return $this->ajaxReturn($result);
    }
    private function getLogisticsInfo($stockout_id){
        $result = array();
        $result['status'] = 0;
        $result['msg'] = 'success';
        $result['data'] = array();
        $printerInfo = array();
        try{
            $tagPrinterInfo = $this->getPrintrData();
            if($tagPrinterInfo['status'] !=0){
                $result['status'] = 1;
                $result['msg'] = $tagPrinterInfo['msg'];
                return $result;
            }
            $printerInfo = $tagPrinterInfo['data'];

            $stockoutInfo = D('Stock/StockOutOrder')->where(array('stockout_id'=>$stockout_id))->select();
            if(empty($stockoutInfo)){
                $result['status'] = 1;
                $result['msg'] = '查询到出库单信息';
                return $result;
            }
            $stockout_id = $stockoutInfo[0]['stockout_id'];
            $logistics_id = $stockoutInfo[0]['logistics_id'];
            $src_order_no = $stockoutInfo[0]['src_order_no'];
            $logisticsData = D('Setting/Logistics')->getLogisticsInfo($logistics_id);
            $logisticsInfo = $logisticsData[0];
            if((int)$logisticsInfo['bill_type'] != 2){
                $result['status'] = 2;
                $result['msg'] = '只支持打印菜鸟电子面单，请前往“单据打印”界面打印非电子面单';
                $printerInfo['stockout_id'] = $stockout_id;
                $printerInfo['logistics_id'] = $logistics_id;
                $printerInfo['src_order_no'] = $src_order_no;
                $result['data'] = $printerInfo;
                return $result;
            }
            $fields = array('rec_id as id,type,title,content');
            $templatesData = D('Setting/PrintTemplate')->getTemplateByLogistics($fields,'4,8,7',$logisticsInfo['logistics_type'],false);
            if(empty($templatesData)){
                $result['status'] = 1;
                $result['msg'] = '请前往“打印模板”界面下载"'.$logisticsInfo['logistics_name'].'"物流公司下的模板';
                return $result;
            }
            $templatesInfo = $templatesData[0];
            $standerTemplateUrl = json_decode($templatesInfo['content'],true)['user_std_template_url'];
            $printerInfo['stockout_id'] = $stockout_id;
            $printerInfo['logistics_id'] = $logistics_id;
            $printerInfo['stander_template_url'] = $standerTemplateUrl;
            $printerInfo['src_order_no'] = $src_order_no;
            $result['data'] = $printerInfo;
        }catch(\PDOException $e){
            \Think\Log::write(__CONTROLLER__."--getLogisticsInfo--".$e->getMessage());
        }catch(\Exception $e){
            \Think\Log::write(__CONTROLLER__."--getLogisticsInfo--".$e->getMessage());
        }
        return $result;
    }
    private function getPrintrData(){
        $result = array();
        $result['status'] = 0;
        $result['msg'] = 'success';
        $result['data'] = array();
        try{
            $user_id = get_operator_id();
            $printerData = D('Setting/UserData')->field(array('data'))->where(array('user_id'=>$user_id,'type'=>7,'code'=>'examine_print_set'))->select();
            if(empty($printerData)){
                $result['status'] = 1;
                $result['msg'] = '打印失败,请先选择打印机';
                return $result;
            }
            $printerInfo = json_decode($printerData[0]['data'],true);
            //$tag_template = D('Setting/PrintTemplate')->where(array('rec_id'=>$printerInfo['tag_template']))->select();
    //        $tag_template = json_decode($tag_template[0]['content'],true);
    //        $tag_template = $tag_template['custom_area_url'];
    //        $printerInfo['tag_template'] = $tag_template;
            $printerInfo['stander_template_url'] = ''; //为了区分是否要打印物流单
            $result['data'] = $printerInfo;
        }catch(\PDOException $e){
            \Think\Log::write(__CONTROLLER__."--getPrintrData--".$e->getMessage());
        }catch(\Exception $e){
            \Think\Log::write(__CONTROLLER__."--getPrintrData--".$e->getMessage());
        }
        return $result;
    }
    public function getTemplates(){
        try{
            $fields = 'rec_id as id,title as name,content';
            $model = D('Setting/PrintTemplate');
//            $result = $model->field($fields)->where(array('type'=>array('in',array(5,6,9)),'title'=>array('LIKE','吊牌_%')))->select();
//            foreach($result as $key){
//                $contents[$key['id']] = $key['content'];
//            }
            $defaultTemplates = $model->field('content')->where(['title' => '系统默认自定义区'])->select();
            if(!empty($defaultTemplates)){
                $sysDefaultUrl = $defaultTemplates[0]['content'];
            }else{
                $sysDefaultUrl = '-1';
            }
            $user_id = get_operator_id();
            $type = 7;
            $code = 'examine_print_set';
            $stallsData = D('Setting/UserData')->fetchSql(false)->field(array('data'))->where(array('user_id'=>$user_id,'type'=>$type,'code'=>$code))->select();
            $stallsPrintInfo = json_decode($stallsData[0]['data'],true);
//            $template_id = $stallsPrintInfo['tag_template'];
//            $hasDefTemp = $model->field($fields)->where(array('rec_id'=>$template_id))->select();
//            if(empty($hasDefTemp))  $template_id = '';
//            if(empty($template_id)) $template_id=$result[0]['id'];
//            if(empty($template_id)) $template_id='-1';

            $logistic_printer = $stallsPrintInfo['logistic_printer'];
            if(empty($logistic_printer)) $logistic_printer='-1';
            $printerInfo = ['logistic_printer'=>$logistic_printer];
            $printerInfo = json_encode($printerInfo);
        }catch(\PDOException $e){
            \Think\Log::write(__CONTROLLER__."--getTemplates--".$e->getMessage());
        }catch(\Exception $e){
            \Think\Log::write(__CONTROLLER__."--getTemplates--".$e->getMessage());
        }
//        $this->assign('template_id',$template_id);
//        $this->assign('tag_template',$result);
        $this->assign('sys_default_template',$sysDefaultUrl);
        $this->assign('printerInfo',$printerInfo);
        $this->display('examine_printers_select');
    }
    public function savePrinters($logisticsInfo){
        try{
            $result = ['status'=>0,'info'=>'设置成功'];
            $code = 'examine_print_set';
            $user_id = get_operator_id();
            $stalls_data = array(
                'type'=>7,
                'code'=>$code,
                'data'=>$logisticsInfo,
                'user_id'=>$user_id,
            );
            D('Setting/UserData')->add($stalls_data,'',true);

        }catch(\BusinessLogicException $e){
            \Think\Log::write($e->getMessage());
            $result =  array('status'=>1,'info'=>$e->getMessage(),'data'=>array());
        }catch(\Exception $e){
            \Think\Log::write($e->getMessage());
            $result = array('status'=>1,'info'=>self::UNKNOWN_ERROR,'data'=>array());
        }
        return $this->ajaxReturn($result);
    }
    public function setLogisticsAndTemplatesDialog(){
        $fields = get_field('StallsPickList','set_logistics_templates');
        $id_list = self::getIDList($id_list,array('datagrid'),'','setprinters');
        $params = array(
            'datagrid'=>array('id'=>$id_list['datagrid']),
        );
        $datagrid = array(
            'id' => $id_list['datagrid'],
            'options' => array(
                'title' => '',
                'url' => U('SalesStockoutExamine/getLogisticsAndTemplates'),
                'fitColumns' => false,
                'pagination' => false,
            ),
            'fields' => $fields,
            'class' => 'easyui-datagrid',
        );
        $logistics_info = D('Setting/Logistics')->field('logistics_name,logistics_type')->where(array('bill_type'=>2,'is_disabled'=>0))->select();
        $templates_info = array();
        $template_field = array('rec_id as id,title');

        foreach ($logistics_info as $logistics){
            $model = D('Setting/PrintTemplate');
            $templates = $model->getTemplateByLogistics($template_field,'4,8,7',$logistics['logistics_type']);
            $templates_info[] = $templates;
        }
        $this->assign('id_list',$id_list);
        $this->assign('params',json_encode($params));
        $this->assign('datagrid',$datagrid);
        $this->assign('templates_info',json_encode($templates_info));
        $this->display('dialog_examine_set_printer_template');
    }
    public function getLogisticsAndTemplates(){

        $logisticsInfo = D('Setting/Logistics')->field('logistics_id,logistics_name')->where(array('bill_type'=>2,'is_disabled'=>0))->select();
        $model = D('Setting/PrintTemplate');
        $type = 7;
        $code = '';
        $userData = D('StallsPickList','Controller')->getUserData($type,$code);
        foreach ($logisticsInfo as &$logistics){
            $lid = $logistics['logistics_id'];
            if(!empty($userData[$lid])){
                $defTemp = $model->field('title,content')->where(array('rec_id'=>$userData[$lid]))->find();
                $logistics['title'] = $defTemp['title'];
                $content = json_decode($defTemp['content'],true);
                if(!empty($content['default_printer'])){
                    $logistics['name'] = $content['default_printer'];
                }else{
                    $logistics['name'] = '无';
                }
            }else{
                $logistics['title'] = '无';
                $logistics['name'] = '无';
            }
        }
        $this->ajaxReturn($logisticsInfo);
    }
    public function submitPrintersAndTemplatesSet($printer_template_data){

        $result['info'] = '保存成功';
        try{
            foreach ($printer_template_data as $set_data){

                $template = D('Setting/PrintTemplate')->field('rec_id,content')->where(array('title'=>$set_data['title']))->find();
                if(!empty($template)){
                    $content = json_decode($template['content'],true);
                    $content['default_printer'] = $set_data['name'];
                    D('StockSalesPrint','Controller')->setDefaultPrinter(json_encode($content),$template['rec_id'],false);
                    $type = 7;
                    $code = '';
                    $logistics_id = $set_data['logistics_id'];
                    $template_id = $template['rec_id'];
                    D('StallsPickList','Controller')->saveUserData($type,$code,$logistics_id,$template_id);
                }
            }
        }catch(\BusinessLogicException $e){
            \Think\Log::write($e->getMessage());
            $result =  array('status'=>1,'info'=>$e->getMessage(),'data'=>array());
        }catch(\Exception $e){
            \Think\Log::write($e->getMessage());
            $result = array('status'=>1,'info'=>self::UNKNOWN_ERROR,'data'=>array());
        }
        $this->ajaxReturn($result);
    }
    public function getTagTemplates(){

        try{
            $fields = 'rec_id as id,title as name,content';
            $model = D('Setting/PrintTemplate');
            $result = $model->field($fields)->where(array('type'=>array('in',array(5,6,9)),'title'=>array('LIKE','吊牌_%')))->select();
            foreach($result as $key){
                $contents[$key['id']] = $key['content'];
            }

            $user_id = get_operator_id();
            $type = 7;
            $code = 'stalls_print_set';
            $stallsPrintInfo = $this->getUserData($type,$code);

            $template_id = $stallsPrintInfo['tag_template'];
            $hasDefTemp = $model->field($fields)->where(array('rec_id'=>$template_id))->select();
            if(empty($hasDefTemp))  $template_id = '';
            if(empty($template_id)) $template_id=$result[0]['id'];
            if(empty($template_id)) $template_id='-1';

            $logistic_printer = $stallsPrintInfo['logistic_printer'];
            if(empty($logistic_printer)) $logistic_printer='-1';
            $tag_printer = $stallsPrintInfo['tag_printer'];
            if(empty($tag_printer)) $tag_printer='-1';
            $printerInfo = ['logistic_printer'=>$logistic_printer,'tag_printer'=>$tag_printer];
            $printerInfo = json_encode($printerInfo);
        }catch(\PDOException $e){
            \Think\Log::write(__CONTROLLER__."--getTemplates--".$e->getMessage());
        }catch(\Exception $e){
            \Think\Log::write(__CONTROLLER__."--getTemplates--".$e->getMessage());
        }
        $this->assign('template_id',$template_id);
        $this->assign('tag_template',$result);
        $this->assign('printerInfo',$printerInfo);
        $this->display('examine_tag_printers_select');
    }
    public function getUserData($type,$code){
        $user_id = get_operator_id();
        $templatesData = D('Setting/UserData')->fetchSql(false)->field(array('data'))->where(array('user_id'=>$user_id,'type'=>$type,'code'=>$code))->select();
        $templatesData = json_decode($templatesData[0]['data'],true);
        return $templatesData;
    }
}
