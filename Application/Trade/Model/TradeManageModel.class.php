<?php
namespace Trade\Model;
use Think\Model;
use Common\Common\ExcelTool;
use Common\Common\UtilTool;
use Common\Common\Factory;
use Think\Exception\BusinessLogicException;

class TradeManageModel extends TradeModel{
	protected $tableName = 'sales_trade';
	protected $pk        = 'trade_id';

	public function exportToExcel($id_list,$search,$type='excel'){
        $user_id = get_operator_id();
        $creator=session('account');
        try{
            if(empty($id_list)){
                $where_sales_trade=' AND st_1.trade_status <120 ';
                $where_sales_trade_order='';
                //设置店铺权限
                D('Setting/EmployeeRights')->setSearchRights($search,'shop_id',1);
                D('Setting/EmployeeRights')->setSearchRights($search,'warehouse_id',2);
                $where_arr = D('Trade/Trade')->searchForm($where_sales_trade,$search);
                $where_sales_trade_order=$where_arr['where_sales_trade_order'];
                $where_goods_goods=$where_arr['where_goods_goods'];
                $where_left_join_goods_class=$where_arr['where_left_join_goods_class'];
                $where_stockout_other=$where_arr['where_stockout_other'];

                $flag=false;
                $sql_where.=' LEFT JOIN goods_goods gg_1 ON gg_1.goods_id=sto_1.goods_id ';
                if(!empty($where_stockout_other)){
                    $sql_where.=' LEFT JOIN stockout_order so_1 ON so_1.src_order_type=1 AND so_1.src_order_id=st_1.trade_id';
                    if(!empty($search['multi_logistics_no'])){
                        $sql_where.=' LEFT JOIN sales_multi_logistics sml on so_1.stockout_id = sml.stockout_id';
                    }else{
                        if(isset($search['multi_logistics'])&&$search['multi_logistics']==1){
                            $sql_where.=' INNER JOIN sales_multi_logistics sml on so_1.stockout_id = sml.stockout_id';
                        }
                    }
                }else{
                    if(isset($search['multi_logistics'])&&$search['multi_logistics']==1){
                        $sql_where.=' LEFT JOIN stockout_order so_1 ON so_1.src_order_type=1 AND so_1.src_order_id=st_1.trade_id INNER JOIN sales_multi_logistics sml on so_1.stockout_id = sml.stockout_id';
                    }
                }
                $sql_where.=$where_left_join_goods_class;
                connect_where_str($sql_where, $where_sales_trade_order, $flag);
                connect_where_str($sql_where, $where_sales_trade, $flag);
                connect_where_str($sql_where, $where_goods_goods, $flag);
                connect_where_str($sql_where, $where_stockout_other, $flag);
                $where="SELECT st_1.trade_id FROM sales_trade st_1  LEFT JOIN sales_trade_order sto_1 ON sto_1.trade_id=st_1.trade_id ".$sql_where."  GROUP BY st_1.trade_id ORDER BY st_1.trade_id desc";
                $rows  = $this->query($where);
                for($i=0;$i<count($rows);$i++){
                    $id_list[$i]=$rows[$i]['trade_id'];
                }
                $where = array('trade_id' => array('in', $id_list));
            }
            else{
                $where = array('trade_id' => array('in', $id_list));
            }
            $num = workTimeExportNum($type);
            if(count($where['trade_id']['1'])>$num){
                if($type == 'csv'){
                    SE(self::EXPORT_CSV_ERROR);
                }
                SE(self::OVER_EXPORT_ERROR);
            }
            $cfg_show_telno=get_config_value('show_number_to_star',1);
            $point_number = get_config_value('point_number',0);
            $goods_count = "CAST(st_2.goods_count AS DECIMAL(19,".$point_number.")) goods_count";
            $raw_goods_count = "CAST(st_2.raw_goods_count AS DECIMAL(19,".$point_number.")) raw_goods_count";
            $trade = $this->alias('st_2')->field("so_1.consign_time,st_2.trade_id,st_2.flag_id, st_2.trade_no, st_2.platform_id, st_2.shop_id ,sh.shop_name,st_2.warehouse_id, sw.name AS warehouse_name, st_2.warehouse_type, st_2.src_tids, st_2.pay_account, st_2.trade_status, st_2.check_step, st_2.consign_status, st_2.trade_from, st_2.trade_type, TO_DAYS(NOW())-TO_DAYS(IF(st_2.delivery_term=2,st_2.trade_time,IF(st_2.pay_time>'1000-01-01 00:00:00',st_2.pay_time,st_2.trade_time))) handle_days, st_2.delivery_term, st_2.freeze_reason, cor.title AS freeze_info,st_2.refund_status, st_2.unmerge_mask, st_2.fenxiao_type, st_2.fenxiao_nick, st_2.trade_time, st_2.pay_time, st_2.delay_to_time, ".$goods_count.", st_2.goods_type_count, st_2.single_spec_no, ".$raw_goods_count.", st_2.raw_goods_type_count, st_2.customer_type, st_2.customer_id, st_2.buyer_nick, st_2.id_card_type, st_2.id_card, st_2.receiver_name, st_2.receiver_country, st_2.receiver_province, st_2.receiver_city, st_2.receiver_district, st_2.receiver_address, IF(".$cfg_show_telno."=0,st_2.receiver_mobile,INSERT( st_2.receiver_mobile,4,4,'****')) receiver_mobile,IF(".$cfg_show_telno."=0,st_2.receiver_telno,INSERT(st_2.receiver_telno,4,4,'****')) receiver_telno, st_2.receiver_zip, st_2.receiver_area, st_2.receiver_ring, st_2.receiver_dtb, st_2.to_deliver_time, st_2.dist_center, st_2.dist_site, st_2.is_prev_notify, clg.logistics_name AS logistics_id, st_2.logistics_no, st_2.buyer_message, st_2.cs_remark, st_2.remark_flag, st_2.print_remark, st_2.note_count, st_2.buyer_message_count, st_2.cs_remark_count, st_2.cs_remark_change_count, st_2.goods_amount, st_2.post_amount, st_2.other_amount, st_2.discount, st_2.receivable, st_2.discount_change, st_2.trade_prepay, st_2.dap_amount, st_2.cod_amount, st_2.pi_amount, st_2.ext_cod_fee, st_2.goods_cost, st_2.post_cost, st_2.other_cost, st_2.profit, st_2.paid, st_2.weight, st_2.volume, st_2.tax, st_2.tax_rate, st_2.commission, st_2.invoice_type, st_2.invoice_title, st_2.invoice_content, st_2.invoice_id, he.fullname AS salesman_id, st_2.sales_score, he_1.fullname AS checker_id, st_2.fchecker_id, st_2.checkouter_id, st_2.allocate_to, st_2.flag_id, st_2.bad_reason, st_2.is_sealed, st_2.gift_mask, st_2.split_from_trade_id, st_2.large_type, st_2.stockout_no, st_2.logistics_template_id, st_2.sendbill_template_id, st_2.revert_reason, st_2.cancel_reason, st_2.is_unpayment_sms, st_2.package_id, IF(st_2.flag_id=0,'无',fg.flag_name) flag_name, st_2.reserve, st_2.version_id, st_2.modified, st_2.created")->where($where)->join('LEFT JOIN cfg_shop sh ON sh.shop_id=st_2.shop_id LEFT JOIN cfg_logistics clg ON clg.logistics_id=st_2.logistics_id LEFT JOIN hr_employee he ON he.employee_id= st_2.salesman_id LEFT JOIN hr_employee he_1 ON he_1.employee_id=st_2.checker_id LEFT JOIN cfg_warehouse sw ON sw.warehouse_id=st_2.warehouse_id LEFT JOIN cfg_flags fg ON fg.flag_id=st_2.flag_id LEFT JOIN cfg_oper_reason cor ON cor.reason_id=st_2.freeze_reason LEFT JOIN stockout_order so_1 ON so_1.src_order_type=1 AND so_1.src_order_id=st_2.trade_id')->order('st_2.trade_id desc')->select();
            //订单状态
            $trade_status=array(
                '5'=>'已取消',
                '10'=>'待付款',
                '12'=>'待尾款',
                '15'=>'等未付',
                '16'=>'延时审核',
                '19'=>'预订单前处理',
                '20'=>'前处理',
                '21'=>'委外前处理',
                '22'=>'抢单前处理',
                '25'=>'预订单',
                '27'=>'待抢单',
                '30'=>'待客审',
                '35'=>'待财审',
                '40'=>'待递交仓库',
                '45'=>'递交仓库中',
                '53'=>'已递交仓库',
                '55'=>'已审核',
                '95'=>'已发货',
                '100'=>'已签收',
                '105'=>'部分打款',
                '110'=>'已完成'
                );

            //发票类别
            $invoice_type=array(
                '0'=>'不需要',
                '1'=>'普通发票',
                '2'=>'增值税发票'
                );

            //订单来源
            $trade_from=array(
                '1'=>'API抓单',
                '2'=>'手工建单',
                '3'=>'excel导入'
                );

            //发货条件
            $delivery_term=array(
                '1'=>'款到发货',
                '2'=>'货到付款'
                );

            //平台信息
            $platform_id=array(
                '0'=>'线下',
                '1'=>'淘宝',
                '2'=>'淘宝分销',
                '3'=>'京东',
                '4'=>'拍拍',
                '5'=>'亚马逊',
                '6'=>'1号店',
                '7'=>'当当网',
                '8'=>'库吧',
                '9'=>'阿里巴巴',
                '10'=>'ECShop',
                '11'=>'麦考林',
                '12'=>'V+',
                '13'=>'苏宁',
                '14'=>'唯品会',
                '15'=>'易迅',
                '16'=>'聚美',
                '17'=>'有赞',
                '19'=>'微铺宝',
                '20'=>'美丽说',
                '21'=>'蘑菇街',
                '22'=>'贝贝网',
                '23'=>'ecstore',
                '24'=>'折800',
                '25'=>'融e购',
                '26'=>'穿衣助手',
                '27'=>'楚楚街',
                '28'=>'微盟旺店',
                '29'=>'卷皮网',
                '30'=>'嘿客',
                '31'=>'飞牛',
                '32'=>'微店',
                '33'=>'拼多多',
                '127'=>'其它'
                );

            //订单类型
            $trade_type=array(
                '1'=>'网店销售',
                '2'=>'线下零售',
                '3'=>'售后换货'
                );

            //仓库类型
            $warehouse_type=array(
                '0'=>'不限',
                '1'=>'普通仓库'
                );

            //发货状态-出库状态
            $consign_status=array(
                '0'=>array('id'=>1,'name'=>'已发货'),
                '1'=>array('id'=>2,'name'=>'已称重'),
                '2'=>array('id'=>4,'name'=>'已出库'),
                '3'=>array('id'=>8,'name'=>'物流同步')
                );
            //退款状态
            $refund_status=array(
                '0'=>'无退款',
                '1'=>'申请退款',
                '2'=>'部分退款',
                '3'=>'全部退款'
                );
            //标旗类别
            $remark_flag = array(
                '0' => '',
                '1' => '红',
                '2' => '黄',
                '3' => '绿',
                '4' => '蓝',
                '5' => '紫'
                );
            for($i=0;$i<count($trade);$i++){
                $trade[$i]['trade_status']=$trade_status[$trade[$i]['trade_status']];
                $trade[$i]['invoice_type']=$invoice_type[$trade[$i]['invoice_type']];
                $trade[$i]['trade_from']=$trade_from[$trade[$i]['trade_from']];
                $trade[$i]['delivery_term']=$delivery_term[$trade[$i]['delivery_term']];
                $trade[$i]['platform_id']=$platform_id[$trade[$i]['platform_id']];
                $trade[$i]['trade_type']=$trade_type[$trade[$i]['trade_type']];
                $trade[$i]['warehouse_type']=$warehouse_type[$trade[$i]['warehouse_type']];
                $trade[$i]['refund_status']=$refund_status[$trade[$i]['refund_status']];
                $trade[$i]['remark_flag']=$remark_flag[$trade[$i]['remark_flag']];
                $str = '';
                for($j=0;$j<count($consign_status);$j++){
                    if($trade[$i]['consign_status'] & $consign_status[$j]['id'])
                        $str = $str . $consign_status[$j]['name'] . ",";
                }
                $str=substr($str,0,strlen($str)-1);
                $trade[$i]['consign_status']=$str;
            }
            $excel_header=D('Setting/UserData')->getExcelField('Trade/Trade','trade_manage');
            if(isset($excel_header['flag'])){
            	unset ($excel_header['flag']);
            }
            $title = '订单管理';
            $filename = '订单管理';
            foreach ($excel_header as $v) 
            {
                $width_list[]=20;
            }
            $trade_log=array();
            for($j=0;$j<count($trade);$j++){
            	$trade_log[]=array(
						'trade_id'=>$trade[$j]['trade_id'],
						'operator_id'=>$user_id,
						'type'=>'55',
						'data'=>'',
						'message'=>'导出订单'.':'.$trade[$j]['trade_no'],
						'created'=>date('y-m-d H:i:s',time())
				);
            }
            D('SalesTradeLog')->addTradeLog($trade_log);
            if($type=='csv'){
                ExcelTool::Arr2Csv($trade, $excel_header, $filename);
            }else{
                ExcelTool::Arr2Excel($trade,$title,$excel_header,$width_list,$filename,$creator);
            }
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
    /**
     * @param $trade
     * @param $err_msg
     * 将导入的订单数据插入数据库
     */
    public function importTrade($trade, &$err_msg) {
        //获取操作用户
        $user_id = get_operator_id();
        //日志表
        $sys_other_log_tb = M("sys_other_log");
        //处理订单数据
        foreach ($trade as $k => $v) {
            try {
                D('SalesTrade')->execute('CALL I_DL_TMP_SUITE_SPEC()');
                $this->startTrans();
                //分别保存处理过后的订单和子订单数据
                $trade_list = array();
                $order_list = array();
                $this->loadTradeImpl($v, $trade_list, $order_list ,$is_history);
                // 判断同一店铺同一子订单号是否存在
                // 存在=>不能保存（如果保存的话递交后合并系统订单会使sales_trade_order的唯一键），不存在=>可以保存
                $same_num=0;
                foreach ($order_list as $o) {
                    $sql_result=M('api_trade_order')->field('shop_id')->where("oid ='".$o['oid']."' AND tid!='".$o['tid']."'")->select();
                    $shop_id=array();
                    foreach ($sql_result as $s) {$shop_id[]=$s['shop_id'];}
                    if (in_array($o['shop_id'], $shop_id)) {
                        $same_num++;
                    }
                }
                if ($same_num!=0) {
                    SE('在同一店铺下存在重复的原始子订单号');
                }
                if($is_history==1){//进入历史订单
                   $trade_table='sales_trade_history';
                   $order_table='sales_trade_order_history';
                }else{//进入订单管理
                    $trade_table='sales_trade';
                    $order_table='sales_trade_order';
                }
                //将订单插入数据库
                $sql_trade = D('OriginalTrade')->putDataToTable($trade_table, $trade_list, $this->update_trade_sql);
                $this->execute($sql_trade);
                //查找插入后的trade_id
                $result=$this->query("SELECT trade_id FROM ".$trade_table." WHERE trade_no='".$trade_list[0]['trade_no']."'");
                $trade_id=$result[0]['trade_id'];
                foreach($order_list as $k=>$v){
                    $order_list[$k]['trade_id']=$trade_id;
                }
                $sql_order = D('OriginalTrade')->putDataToTable($order_table, $order_list, $this->update_order_sql);
                $this->execute($sql_order);
                //刷新订单
                $this->execute("CALL I_DL_REFRESH_TRADE(".$user_id.", ".$trade_id.", 16,0)");
                //记录订单日志
                $trade_log=array(
                    'trade_id'=>$trade_id,
                    'operator_id'=>$user_id,
                    'type'=>50,
                    'message'=>$is_history==1?"导入历史订单{$trade_list[0]['trade_no']}":"订单管理导入订单{$trade_list[0]['trade_no']}",
                    'created'=>date('y-m-d H:i:s',time())
                );
                D('Trade/SalesTradeLog')->addTradeLog($trade_log);
                $this->commit();
            } catch (\PDOException $e) {
                $this->rollback();
                \Think\Log::write($e->getMessage());
                $err_msg[] = array("trade_no" =>''.$k, "result" => "失败", "message" => self::PDO_ERROR);
            } catch (BusinessLogicException $e) {
                $this->rollback();
                $err_msg[] = array("trade_no" => ''.$k, "result" => "失败", "message" => $e->getMessage());
            } catch (\Exception $e) {
                $this->rollback();
                \Think\Log::write($e->getMessage());
                $err_msg[] = array("trade_no" => ''.$k, "result" => "失败", "message" => self::PDO_ERROR);
            }
        }

    }

    public function loadTradeImpl($trade, &$trade_list, &$order_list ,&$is_history) {
        //订单编号
        $trade_no = $trade["trade_no"];
        if($trade_no == ''){
            SE('订单编号不能为空');
        }
        //判断订单编号是否已存在
        $is_exist=$this->query("SELECT 1 FROM sales_trade WHERE trade_no='".$trade['trade_no']."'");
       if(!empty($is_exist)){
           SE('订单编号已存在');
       }
        //限制订单编号不能以JY开头，以防和系统编号冲突
        $str=substr($trade_no , 0 , 2);
        if($str=='JY'){
            SE("导入订单的订单编号不能以JY开头");
        }
        //当前状态
        $current_status = $trade["trade_status"];
        if($current_status==''){
            SE('当前状态不能为空');
        }
        if($current_status!='已完成'&&$current_status!='已取消'){
            SE('订单状态不正确，应为‘已完成’ 或者 ‘已取消');
        }
        switch ($current_status) {
            case "已完成":
                $trade_status = 110;
                break;
            case "已取消":
                $trade_status = 5;
                break;
            default:
                $trade_status = 5;
                break;
        }
        //手机和固话至少一个不为空
        if($trade["receiver_telno"]==''&&$trade['receiver_mobile']==''){
            SE("手机和固话至少一个不能为空");
        }
        //订单类别
        $trade_type=$trade["trade_type"];//'1'=>'网店销售','2'=>'线下零售','3'=>'售后换货'
        switch($trade_type){
            case "网店销售":
                $trade_type = 1;
                break;
            case "线下零售":
                $trade_type = 2;
                break;
            case "售后换货":
                $trade_type = 3;
                break;
            default:
                $trade_type = 1;
                break;
        }
        //订单来源
        $trade_from=$trade["trade_from"];//1API抓单，2手工建单 3excel导入 4现款销售
        switch($trade_from){
            case "API抓单":
                $trade_from = 1;
                break;
            case "手工建单":
                $trade_from = 2;
                break;
            case "excel导入":
                $trade_from = 3;
                break;
            case "现款销售":
                $trade_from = 4;
                break;
            default:
                $trade_from = 3;
                break;
        }
        //获取平台店铺等相关信息
        $shop_name = $trade["shop_name"];
        if ($shop_name == "") {
            SE("店铺不能为空");
        }
        $result = $this->query("SELECT cs.shop_id FROM cfg_shop cs WHERE cs.shop_name='%s'", $shop_name);
        if (count($result) == 0) {
            SE("不存在该店铺");
        }
        $shop_id     = $result[0]["shop_id"];
        $where = array();
        $shop_id_list = D('Setting/EmployeeRights')->setSearchRights($where,'shop_id',1);
        $shop_id_list = explode(',',$shop_id_list);
        if(!in_array($shop_id,$shop_id_list)){
            SE('该员工没有此店铺权限,导入失败');
        }
        //仓库
        $warehouse_name=$trade['warehouse_name'];
        if($warehouse_name==''){
            SE("仓库不能为空");
        }
        $result = $this->query("SELECT cw.warehouse_id,cw.type FROM cfg_warehouse cw WHERE cw.name='%s'", $warehouse_name);
        if (count($result) == 0) {
            SE("不存在该仓库");
        }
        $warehouse_id  = $result[0]["warehouse_id"];
        $where = array();
        $warehouse_id_list = D('Setting/EmployeeRights')->setSearchRights($where,'warehouse_id',1);
        $warehouse_id_list = explode(',',$warehouse_id_list);
        if(!in_array($warehouse_id,$warehouse_id_list)){
            SE('该员工没有此仓库权限,导入失败');
        }
        //仓库类别
        $warehouse_type=$result[0]["type"];
        //物流公司
        $logistics_name=$trade['logistics_name'];
        if($logistics_name==''){
            SE("物流公司不能为空");
        }
        $result = $this->query("SELECT cl.logistics_id FROM cfg_logistics cl WHERE cl.logistics_name='%s'", $logistics_name);
        if (count($result) == 0) {
            SE("不存在该物流公司");
        }
        $logistics_id=$result[0]["logistics_id"];
        //物流单号
        $logistics_no=$trade['logistics_no'];
        if($logistics_no==''){
            SE("物流单号不能为空");
        }
        //业务员、审单员
        $result = $this->query("SELECT IFNULL(he.employee_id,0) salesman_id  FROM hr_employee he WHERE he.account='%s'", $trade['salesman']);
        if(empty($result)){
            $salesman_id=0;
        }else{
            $salesman_id=$result[0]['salesman_id'];
        }
        $result = $this->query("SELECT IFNULL(he.employee_id,0) checker_id  FROM hr_employee he WHERE he.account='%s'", $trade['checker']);
        if(empty($result)){
            $checker_id=0;
        }else{
            $checker_id=$result[0]['checker_id'];
        }
        //发票类型、发票抬头、发票内容
        //发票类型
        $invoice_type = 0;
        switch ($trade["invoice_type"]) {
            case "普通发票":
                $invoice_type = 1;
                break;
            case "增值税发票":
                $invoice_type = 2;
                break;
            default:
                break;
        }
        //发票抬头
        $invoice_title = $trade["invoice_title"];
        //发票内容
        $invoice_content = $trade["invoice_content"];
        //收件人、省、市、区、收货地址
        $faq_url = C('faq_url');
        $faq_url = $faq_url["primitive_import_process"];
        if($trade['receiver_name']==''){
            SE('收件人不能为空');
        }
        if($trade['receiver_address']==''){
            SE('收货地址不能为空');
        }
        //处理买家省市县信息
        if(2 === strlen($trade['receiver_area']) && !empty($trade['receiver_address'])){
            $trade['receiver_address'] = str_replace(array("   ","  "),array(" "," "),$trade['receiver_address']);
            $arr_address = explode(" ",$trade['receiver_address']);
            if(count($arr_address) >= 3){
                $trade['receiver_province'] = $arr_address[0];
                $trade['receiver_city'] = $arr_address[1];
                $trade['receiver_district'] = $arr_address[2];
                $trade['receiver_area'] = $trade['receiver_province']." ".$trade['receiver_city']." ".$trade['receiver_district'];
            }else{
                SE("收货地址请填写省市县(区)信息,并用空格分隔,&lt;a href='{$faq_url}' target='_blank' &gt;点击查看解决办法&lt;/a&gt;");
            }
        }
        $dict_address  = D("DictAddress");
        $trade['receiver_area'] = $trade["receiver_province"] . " " . $trade["receiver_city"] . " " . $trade["receiver_district"];
        $dict_address->trans2no($trade["receiver_province"], $trade["receiver_city"], $trade["receiver_district"]);
        $receiver_hash = md5($trade["receiver_name"] . $trade['receiver_area'] . $trade["receiver_address"] . $trade["receiver_mobile"] . $trade["receiver_telno"] . $trade["receiver_zip"]);
        if($trade['receiver_province']=='' || $trade["receiver_city"]=='' || $trade['receiver_district']==''){
            SE("数据异常,请检查省市县(区)信息,&lt;a href='{$faq_url}' target='_blank' &gt;点击查看解决办法&lt;/a&gt;");
        }
        //买家网名、收件人
        if($trade['buyer_nick']==''){
            if($trade['receiver_mobile']<>''){
                $trade['buyer_nick']='MOB'.$trade['receiver_mobile'];
            }elseif($trade['receiver_telno']<>''){
                $trade['buyer_nick']='TEL'.$trade['receiver_mobile'];
            }else{
                $trade['buyer_nick']=  '未知买家';
            }
        }
        //整理每个子订单的信息
        $orders = $trade["order"];
        $tids=array();
        $trade_amount=0;
        $goods_count=0;//货品总量
        $paid=0;
        $post_amount=0;
        foreach ($orders as $v) {
            $src_oid        = $v["src_oid"];
            if($src_oid == ''){
                SE('源子订单号不能为空');
            }
            if($v['merchant_no']==''){
                SE('商家编码不能为空');
            }
            $result=$this->query("SELECT gmn.target_id,gmn.type FROM goods_merchant_no gmn WHERE gmn.merchant_no='".$v['merchant_no']."'");
            if (empty($result)||$result[0]['target_id']==0) {
                SE("订单：".$trade_no."中商家编码:".$v['merchant_no']."在系统中不存在");
            }
            $target_id=$result[0]['target_id'];
            $goods_type=$result[0]['type'];
            if(intval($v['num'])<=0){
                SE("商品数量必须大于0");
            }
            switch ($v["gift_type"]) {
                case "否":
                    $gift_type = 0;
                    break;
                case "是":
                    $gift_type = 2;
                    break;
                default:
                    $gift_type = 0;
            }
            if($v['gift_type']<>0&&$v['order_price']<>0){
                SE("赠品的收款必须为0");
            }
            if(floatval($v['paid'])<0){
                SE('实收金额不能小于0');
            }
            $tids[]=$v['src_tid'];
            if($goods_type==1){//单品
                $spec_info=$this->query("SELECT gg.goods_name,gg.goods_id,gg.goods_no,gs.spec_name,gs.spec_no,gs.spec_code,gs.large_type
                                            FROM goods_spec gs
                                            LEFT JOIN goods_goods gg USING(goods_id)
                                            WHERE gs.spec_id = %d",$target_id);
                if(empty($spec_info)){
                    SE("没有找到商家编码所对应的系统货品");
                }
                $order_list[] = array(
//                "trade_id"      => $trade_id,
                    "spec_id"         => $target_id,
                    "shop_id"         => $shop_id,
                    "platform_id"     => 0,
                    "src_oid"         => $v['src_oid'],
                    "src_tid"         => $v['src_tid'],
                    "gift_type"       => set_default_value($gift_type, 0),
                    "num"             => set_default_value(intval($v['num']), 0),
                    "actual_num"      => set_default_value(intval($v['num']), 0),
                    "price"           => floatval($v['order_price']),
                    "order_price"     => floatval($v['order_price']),
                    "share_price"     => floatval($v['order_price']),
                    "share_amount"    => floatval($v['order_price'])*intval($v['num']),
                    "share_post"      => floatval($v['share_post']),
                    "paid"            => set_default_value(floatval($v['paid']), 0),
                    "goods_name"      => $spec_info[0]['goods_name'],
                    "goods_id"        => $spec_info[0]['goods_id'],
                    "goods_no"        => $spec_info[0]['goods_no'],
                    "spec_name"       => $spec_info[0]['spec_name'],
                    "spec_no"         => $spec_info[0]['spec_no'],
                    "spec_code"       => $spec_info[0]['spec_code'],
                    "large_type"      => $spec_info[0]['large_type'],
                    "invoice_type"    => $invoice_type,
                    "invoice_content" => $invoice_content,
                    "created"         => date("Y-m-d G:i:s", time()),
                );
                $goods_count+=$v['num'];
            }else{//组合装
                $suite_info=$this->query("SELECT gs.suite_no,gs.suite_name,gs.retail_price,gsd.spec_id,num,gsd.fixed_price,gsd.ratio,gsd.is_fixed_price FROM goods_suite gs
                                LEFT JOIN goods_suite_detail gsd ON  gsd.suite_id = gs.suite_id
                                WHERE gs.suite_id = %d",$target_id);
                if(empty($suite_info)){
                    SE("没有找到商家编码所对应的系统货品");
                }
                foreach($suite_info as $gsd){
                    $goods_count+=$v['num']*$gsd['num'];
                    $paid=$v['paid']*$gsd['ratio'];//支付的价格*金额占比
                    $spec_info=$this->query("SELECT gg.goods_name,gg.goods_id,gg.goods_no,gs.spec_name,gs.spec_no,gs.spec_code,gs.large_type
                                            FROM goods_spec gs
                                            LEFT JOIN goods_goods gg USING(goods_id)
                                            WHERE gs.spec_id = %d",$gsd['spec_id']);
                    $order_list[] = array(
                        "spec_id"         => $gsd['spec_id'],
                        "shop_id"         => $shop_id,
                        "platform_id"     =>0,
                        "src_oid"         => $v['src_oid'],
                        "suite_id"        => $target_id,
                        "src_tid"         => $v['src_tid'],
                        "gift_type"       => set_default_value($gift_type, 0),
                        "num"             => $v['num']*$gsd['num'],
                        "actual_num"      => $v['num']*$gsd['num'],
                        "suite_no"        => $gsd['suite_no'],
                        "suite_name"      => $gsd['suite_name'],
                        "suite_num"       => $v['num'],
                        "price"           => $v['order_price']*$gsd['ratio'],
                        "order_price"     => $v['order_price']*$gsd['ratio'],
                        "paid"            => $paid,
                        "share_price"     => $v['order_price']*$gsd['ratio'],
                        "share_amount"    => $v['order_price']*$gsd['ratio']*$v['num'],
                        "share_post"      => $v['share_post']*$gsd['ratio'],
                        "paid"            => set_default_value($v['paid'], 0),
                        "goods_name"      => $spec_info[0]['goods_name'],
                        "goods_id"        => $spec_info[0]['goods_id'],
                        "goods_no"        => $spec_info[0]['goods_no'],
                        "spec_name"       => $spec_info[0]['spec_name'],
                        "spec_no"         => $spec_info[0]['spec_no'],
                        "spec_code"       => $spec_info[0]['spec_code'],
                        "large_type"      => $spec_info[0]['large_type'],
                        "invoice_type"    => $invoice_type,
                        "invoice_content" => $invoice_content,
                        "created"         => date("Y-m-d G:i:s", time()),
                    );
                }
            }
            $trade_amount+=$v['order_price'];
        }
        $paid+=$v['paid'];
        $post_amount+=$v['share_post'];
        $src_tids=implode(',',array_unique($tids));
        //处理顾客信息
        $customer_id=0;
        $trade['receivable']=$trade['paid'];
        $result=$this->query("SELECT customer_id FROM crm_platform_customer cpc WHERE cpc.account='".$trade['buyer_nick']."'");
        if(!empty($result)&&$result[0]['customer_id']!=0){
            $customer_id=$result[0]['customer_id'];
        }
        if($customer_id!=0){
            $result=$this->query("SELECT 1 FROM crm_customer_telno WHERE customer_id=".$customer_id." AND telno IN('".$trade['receiver_mobile']."','".$trade['receiver_telno']."')");
            if(empty($result)){
                $result=$this->query("SELECT 1 FROM crm_customer_address WHERE customer_id=".$customer_id." AND `name`='".$trade['receiver_name']."'");
                if(empty($result)){$customer_id=0;}
            }
        }
        if($customer_id==0){//增加顾客信息
             $customer_id =D('Customer/Customer')->addCustomerByTrade($trade);
        }else{
            $this->execute("INSERT IGNORE INTO crm_platform_customer(platform_id,account,customer_id,created) VALUES(0,'".$trade['buyer_nick']."',".$customer_id.",NOW());");
            $this->execute("UPDATE crm_customer SET trade_count=trade_count+1, trade_amount=trade_amount+".$trade_amount." WHERE customer_id=%d",$customer_id);
        }
        $trade['customer_id']=$customer_id;
        D('Customer/CustomerAddress')->addAddressByTrade($trade);
        D('Customer/CustomerTelno')->addTelnoByTrade($trade);
        //订单数据
        $trade_list[] = array(
            "trade_no"          =>$trade_no,
            "platform_id"       => 0,
            "shop_id"           => $shop_id,
            'warehouse_id'      => $warehouse_id,
            'warehouse_type'    => $warehouse_type,
            "src_tids"          => $src_tids,
            "trade_status"      => $trade_status,
            "trade_from"        => $trade_from,
            "trade_type"        => $trade_type,
            "trade_time"        => '1000-01-01 00:00:00',
            "pay_time"          => set_default_value($trade["pay_time"], '1000-01-01 00:00:00'),
            "goods_count"       => $goods_count,
            "goods_amount"      => $paid,
            "post_amount"       => $post_amount,
            "receivable"        => set_default_value($paid, 0),
            "dap_amount"        => set_default_value($paid, 0),
            "profit"            => set_default_value($trade['profit'], 0),
            "paid"              => set_default_value($paid, 0),
            "weight"            => set_default_value($trade['weight'], 0),
            "logistics_id"      => $logistics_id,
            "logistics_no"      => set_default_value($trade["logistics_no"], ''),
            "buyer_nick"        => set_default_value($trade['buyer_nick'], ''),
            "buyer_message"     => set_default_value($trade["buyer_message"], ''),
            "cs_remark"         => set_default_value($trade["cs_remark"], ''),
            "customer_id"       => $customer_id,
            "receiver_name"     => $trade["receiver_name"],
            "receiver_province" => set_default_value($trade["receiver_province"], 0),
            "receiver_city"     => set_default_value($trade["receiver_city"], 0),
            "receiver_district" => set_default_value($trade["receiver_district"], 0),
            "receiver_address"  => $trade["receiver_address"],
            "receiver_area"     => set_default_value($trade['receiver_area'], ''),
            "receiver_dtb"      => set_default_value($trade["receiver_dtb"], ''),
            "receiver_mobile"   => set_default_value($trade["receiver_mobile"], ''),
            "receiver_zip"      => set_default_value($trade["receiver_zip"], ''),
            "invoice_type"      => set_default_value($invoice_type, 0),
            "invoice_title"     => set_default_value($invoice_title, ''),
            "invoice_content"   => set_default_value($invoice_content, ''),
            "checker_id"        => set_default_value($checker_id, 0),
            "salesman_id"       => set_default_value($salesman_id, 0),
            "created"           => date("Y-m-d G:i:s", time())
        );
        //判断是历史订单，还是订单(三个月之前的订单直接到历史订单中)
        $start_time= date('Y-m-d',strtotime('-3 months'));//三个月之前的时间
        if($trade["pay_time"]>$start_time){$is_history=0;}else{$is_history=1;}
    }

}