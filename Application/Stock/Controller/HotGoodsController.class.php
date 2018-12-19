<?php
namespace Stock\Controller;
use Common\Controller\BaseController;
use Think\Exception\BusinessLogicException;
use Common\Common\DatagridExtention;
use Common\Common\UtilDB;
use Platform\Common\ManagerFactory;

class HotGoodsController extends BaseController
{
    public function show(){
        $id_list = array();
        $id_list = self::getIDList($id_list,array('datagrid','toolbar','form','form_id','toolbar_bottom','print_dialog','split_order_result','setting_dialog'),'hot_goods');
        $fields = get_field('Purchase/StallsOrder','hot_goods_order');
        $datagrid = array(
            'id'        =>  $id_list['datagrid'],
            'options'   => array(
                'title'         =>  '',
                'toolbar'       =>  "#{$id_list['toolbar']}",
                'fitColumns'    =>  true,
                'singleSelect'  =>  false,
                'ctrlSelect'    =>  true,
                'pagination'    =>  false,
            ),
            'fields'     =>  $fields,
        );

        $this->assign('id_list',$id_list);
        $this->assign('params',json_encode($id_list));
        $this->assign('datagrid', $datagrid);
        $this->display('print_hot_goods');
    }
    public function getHotGoodOrders($hotGoodsCode){

        $result = array();
        $result['status'] = 0;
        $result['msg'] = 'success';
        $result['data'] = array();
        $hot_return_num = 0;
        $goodInfo = array();
        if(empty($hotGoodsCode)){
            $result['status'] = 1;
            $result['msg'] = '请扫描或输入爆款码';
            return $this->ajaxReturn($result);
        }
        try{
            $stallOrder = D('Purchase/StallsOrder')->field('hot_print_status')->where(array('stalls_no'=>$hotGoodsCode))->find();
            if($stallOrder['hot_print_status'] != 2){
                $result['status'] = 1;
                $result['msg'] = '请扫描已打印的爆款单';
                return $this->ajaxReturn($result);
            }
            $hotOrders = D('Purchase/StallsOrder')->getHotOrdersInfo($hotGoodsCode,'desc');
            foreach ($hotOrders as $order){
                if($order['refund_status'] == 5 || $order['refund_status'] == 2){
                    $hot_return_num ++;
                }
            }
            if(!empty($hotOrders)){
                $trade_id = $hotOrders[0]['trade_id'];
                $goodInfo = D('Trade/SalesTradeOrder')->field('spec_id,goods_name,spec_no')->where(array('trade_id'=>$trade_id))->find();
                $goodsSpec = D('Goods/GoodsSpec')->field('img_url')->where(array('spec_id'=>$goodInfo['spec_id']))->find();
                $goodInfo['url'] = $goodsSpec['img_url'];
                $goodInfo['hot_orders_num'] = count($hotOrders);
                $goodInfo['hot_return_num'] = $hot_return_num;
            }
            $result['data'] = $hotOrders;
            $result['goods_data'] = $goodInfo;

        }catch(\PDOException $e){
            $msg = $e->getMessage();
            \Think\Log::write(__CONTROLLER__.'--getHotGoodOrders--'.$e->getMessage());
            $result['status'] = 1;
            $result['msg'] = $msg;
        }catch(BusinessLogicException $e){
            $msg = $e->getMessage();
            \Think\Log::write(__CONTROLLER__.'--getHotGoodOrders--'.$e->getMessage(),\Think\Log::WARN);
            $result['status'] = 1;
            $result['msg'] = $msg;
        }catch(\Exception $e){
            $msg = $e->getMessage();
            \Think\Log::write(__CONTROLLER__.'--getHotGoodOrders--'.$e->getMessage());
            $result['status'] = 1;
            $result['msg'] = $msg;
        }
        $this->ajaxReturn($result);
    }
    public function showNeedSplitOrdersInfo(){
        $id_list = array(
            'tool_bar'                       => 'split_hot_order_datagrid_toolbar',    //tool_bar id
            'datagrid'                       => 'split_hot_order_datagrid',            //当前模块 datagrid id
            'split_hot_order'                => 'split_hot_order',
            'not_split_hot_order'            => 'not_split_hot_order',
        );

        $fields = get_field('Purchase/StallsOrder','split_hot_order_info');

        $datagrid = array(
            'id'=>$id_list['datagrid'],
            'options'=> array(
                'title' => '',
                'toolbar' => "#{$id_list['tool_bar']}",
                'fitColumns'   => false,
                'singleSelect'=>true,
                'ctrlSelect'=>false,
                'methods'=>'loader:getSplitOrdersInfo',
                'pagination'=>false
            ),
            'fields' => $fields,
            'class' => 'easyui-datagrid',
            'style'=>"overflow:scroll",
        );
        $this->assign('tool_bar',$id_list['tool_bar']);
        $this->assign('split_hot_order',$id_list['split_hot_order']);
        $this->assign('not_split_hot_order',$id_list['not_split_hot_order']);
        $this->assign('datagrid', $datagrid);
        $this->display('split_hot_order_info');
    }
    public function splitOrdersInfo(){
        $hotCode = I('post.hotGoodsCode','',C('JSON_FILTER'));
        $printNum = I('post.printNum','',C('JSON_FILTER'));
        $printNum = intval($printNum);
        $errOrders = array();
        $splitOrders = array();
        $tempOrders = array();

        $hotOrders = D('Purchase/StallsOrder')->getHotOrdersInfo($hotCode,'asc');
        foreach ($hotOrders as $po){
            if(($po['refund_status'] == 5)||($po['refund_status'] ==2)){
                $errOrders[] =  $po;
            }
        }
        if($printNum < (count($hotOrders)-count($errOrders))){

            $splitNum = count($hotOrders)-count($errOrders)-$printNum;
            $splitOrders = array_slice($hotOrders,$printNum-1,$splitNum);
            foreach ($splitOrders as $so){
                $tempOrders[] = ['trade_no'=>$so['src_order_no'],'msg'=>'是否需要拆分'];
            }
            $splitOrders = $tempOrders;
        }else{
            $splitOrders = array();
        }
        $this->ajaxReturn($splitOrders);
    }
    public function splitOrder($tradeList,$specNo){

        $result = array();
        $result['status'] = 0;
        $result['msg'] = 'success';
        $result['data'] = array();
        $hotOrders = array();
        try{
            $trade_id_list = '';
            $rec_id_list = '';

            $trade_id_arr = D('Trade/SalesTrade')->field('trade_id')->where(array('trade_no'=>['in',$tradeList]))->select();
            foreach ($trade_id_arr as $trade){
                $trade_id_list = $trade_id_list.$trade['trade_id'].',';
            }
            $rec_id_arr = D('Purchase/StallsOrderDetail')->field('rec_id')->where(array('trade_no'=>['in',$tradeList]))->select();
            foreach ($rec_id_arr as $stalls){
                $rec_id_list = $rec_id_list.$stalls['rec_id'].',';
            }
            $trade_id_list = rtrim($trade_id_list,',');
            $rec_id_list = rtrim($rec_id_list,',');
            $goods_count = count($trade_id_arr);
            $spec_info = D('Goods/GoodsSpec')->field('spec_id')->where(array('spec_no'=>$specNo))->find();
            $hotOrders[0] = array(
                'goods_count'=>$goods_count,
                'rec_id_str'=>$rec_id_list,
                'trade_id_str'=>$trade_id_list,
                'goods_fee'=>'0.000',
                'tax_fee'=>'0.000',
                'spec_id'=>$spec_info['spec_id']
            );
            $res = D('Purchase/StallsOrder')->addHotOrderDeal($hotOrders);
        }catch(\PDOException $e){
            $msg = $e->getMessage();
            \Think\Log::write(__CONTROLLER__.'--splitOrder--'.$e->getMessage());
            $result['status'] = 1;
            $result['msg'] = $msg;
        }catch(BusinessLogicException $e){
            $msg = $e->getMessage();
            \Think\Log::write(__CONTROLLER__.'--splitOrder--'.$e->getMessage(),\Think\Log::WARN);
            $result['status'] = 1;
            $result['msg'] = $msg;
        }catch(\Exception $e){
            $msg = $e->getMessage();
            \Think\Log::write(__CONTROLLER__.'--splitOrder--'.$e->getMessage());
            $result['status'] = 1;
            $result['msg'] = $msg;
        }
        $this->ajaxReturn($result);
    }
    public function printHotOrders($hot_code = '',$trade_no_list = ''){

        $result = array();
        $result['status'] = 0;
        $result['msg'] = 'success';
        $result['data'] = array();
        $hotCode = I('post.hotGoodsCode','',C('JSON_FILTER'));
        $printNum = I('post.printNum','',C('JSON_FILTER'));
        $printNum = intval($printNum);
        $change_detail = '';
        $unique_code = '';
        $errOrders = array();
        $errMsgInfo = array();
        $printOrders = array();
        if($hot_code !=''){
            $hotCode = $hot_code;
            $printNum = 0;
        }

        try{
            $hotOrders = D('Purchase/StallsOrder')->getHotOrdersInfo($hotCode,'asc');
            $errInfo = $this->checkPrintersAndTemplates($hotOrders);
            if(count($errInfo)>0){
                $result['status'] = 1;
                $msg = '请设置物流公司 : '.implode($errInfo,',').' 的打印机和模板';
                $result['msg'] = $msg;
                $this->ajaxReturn($result);
            }
            if($printNum>count($hotOrders)){
                $result['status'] = 1;
                $result['msg'] = '打印数量大于爆款单数量';
                $this->ajaxReturn($result);
            }
            if($hot_code !=''){
                $tradeNoArr = explode(',',$trade_no_list);
                $tradeNoArr = array_unique($tradeNoArr);
                $tradeNoArr = array_merge($tradeNoArr);
                for($i=0;$i<count($hotOrders);$i++){
                    for ($j=0;$j<count($tradeNoArr);$j++){
                        if($hotOrders[$i]['src_order_no'] == $tradeNoArr[$j]){
                            $printOrders[] = $hotOrders[$i];
                        }
                    }
                }
            }else{
                $printOrders = array_slice($hotOrders,0,$printNum);
            }
            foreach ($printOrders as $po){
                if(($po['refund_status'] == 5)||($po['logistics_print_status'] ==1)){
                    $errOrders[] =  $po;
                }
            }
            foreach ($printOrders as $printO){

                $stockout_id = $printO['stockout_id'];
                $logistics_id = $printO['logistics_id'];
                $src_order_no = $printO['src_order_no'];
                D('Purchase/StallsOrder')->hotGoodsPickList($printO);

                $search = ['src_order_no'=>$src_order_no];
                $stockoutOrderData = D('StockOutOrder')->searchStockoutList(1, 20, $search, 'id', 'desc', 'stockoutPrint');
                $row = $stockoutOrderData['rows'][0];
                if($row['status'] == 95){
                    $errMsgInfo[] =  array('trade_no'=>$src_order_no,'msg'=>'订单已发货');
                    continue;
                }
                if($row['block_reason'] &(8|64|128)){
                    if($row['block_reason'] &(8|64)){
                        $tradeInfo = D('SalesTrade')->field('cs_remark as remark,invoice_type as type,invoice_title as title,invoice_content as content')->where(array('trade_no'=>array('eq',$row['src_order_no'])))->find();
                        $change_detail = json_encode($tradeInfo);
                    }
                    if($row['block_reason'] & 128){
                        $stallsOrderDetail = D('Purchase/StallsOrderDetail')->field('unique_code')->where(array('trade_no'=>$src_order_no))->select();
                        $unique_code = $stallsOrderDetail[0]['unique_code'];
                    }
                    $errMsgInfo[] =  array('trade_no'=>$src_order_no,'msg'=>$change_detail,'block_reason'=>$row['block_reason'],'unique_code'=>$unique_code);
                    continue;
                }
                //获取电子面单
                if($printO['bill_type'] == 2){
                    if($printO['refund_status'] ==5){
                        $errMsgInfo[] =  array('trade_no'=>$src_order_no,'msg'=>'订单已退款');
                        continue;
                    }elseif ($printO['refund_status'] ==2){
                        $errMsgInfo[] =  array('trade_no'=>$src_order_no,'msg'=>'订单申请退款');
                        continue;
                    }
                    $res = $this->getWaybill($printO);
                    if($res['status'] !=0){//res 返回三种状态：0->单号获取成功 1->获取单号前校验失败 2->接口调用成功，但是未返回单号
                        if(count($res['data']['fail']) > 0){
                            $result['msg'] = $res['data']['fail'][0]['msg'];
                            $errMsgInfo[] =  array('trade_no'=>$src_order_no,'msg'=>$result['msg']);
                        }else{
                            $result['msg'] = $res['msg'];
                            $errMsgInfo[] =  array('trade_no'=>$src_order_no,'msg'=>$result['msg']);
                        }
                    }
                    $printHotData['waybill_print_info'] = $res['data']['success'][$stockout_id];
                }else{
                    $errMsgInfo[] =  array('trade_no'=>$src_order_no,'msg'=>'非电子面单请到单据打印界面打印');
                    continue;
                }
                //打印
                $print_data = $this->getTemplatesAndPrinters($logistics_id);
                $printHotData['print_data']['logistic_printer'] = $print_data['printer'];

                $printHotData['row'] = $row;
                $printHotData['custom_template_url'] = $print_data['custom_url'];
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
                $printHotData['goods'] = $goods;

                $result['data'][] = $printHotData;
                //改打印状态
                $stockoutOrder = D('StockOutOrder')->field('logistics_no')->where(array('stockout_id'=>$stockout_id))->find();
                if(!empty($stockoutOrder['logistics_no'])){
                    D("StockOutOrder")->changePrintStatus($stockout_id,'logistics');
                }
                //发货
//                $setting_config = get_config_value(array('hot_order_print_auto_consign'));
                $base_set_data = $this->getUsetData();
                $base_set_data = json_decode($base_set_data,true);
                if((isset($base_set_data['hot_auto_consign']))&&($base_set_data['hot_auto_consign'] ==1)){
                    $stockoutOrderRes =  D('StallsPickList','Controller')->consignStockoutOrder($stockout_id);
                    if($stockoutOrderRes['status'] !=0){
                        if($stockoutOrderRes['status'] == 1){
                            $result['msg'] = $stockoutOrderRes['msg'];
                            $errMsgInfo[] =  array('trade_no'=>$src_order_no,'msg'=>$result['msg']);
                        }else{
                            $result['msg'] = $stockoutOrderRes['data']['fail'][0]['msg'];
                            $errMsgInfo[] =  array('trade_no'=>$src_order_no,'msg'=>$result['msg']);
                        }
                    }
                }

            }
            $this->changeStallsOrderStatus($hotOrders,$hotCode);
            $result['order_data'] = D('Purchase/StallsOrder')->getHotOrdersInfo($hotCode,'desc');
            $result['err_msg_info'] = $errMsgInfo;

            }catch(\PDOException $e){
                $msg = $e->getMessage();
                \Think\Log::write(__CONTROLLER__.'--printHotOrders--'.$e->getMessage());
                $result['status'] = 1;
                $result['msg'] = $msg;
            }catch(BusinessLogicException $e){
                $msg = $e->getMessage();
                \Think\Log::write(__CONTROLLER__.'--printHotOrders--'.$e->getMessage(),\Think\Log::WARN);
                $result['status'] = 1;
                $result['msg'] = $msg;
            }catch(\Exception $e){
                $msg = $e->getMessage();
                \Think\Log::write(__CONTROLLER__.'--printHotOrders--'.$e->getMessage());
                $result['status'] = 1;
                $result['msg'] = $msg;
            }
            if($hot_code !=''){
                return $result;
            }else{
                $this->ajaxReturn($result);
            }

    }
    public function continueSort($hotGoodsCode,$tradeNoList){

        $ids = D('Stock/StockoutOrder')->fetchSql(false)->field('stockout_id')->where(array('src_order_no'=>array('in',$tradeNoList)))->select();
        $stockout_ids = array();
        foreach ($ids as $k=>$v){
            $stockout_ids[] = $v['stockout_id'];
        }
        for($i=0;$i<count($stockout_ids);$i++){
            $stockoutInfo = D('StockoutOrder')->field('block_reason')->where(array('stockout_id'=>$stockout_ids[$i]))->select();
            if($stockoutInfo[0]['block_reason'] & (1|2|128)){
                $blockName = D('SalesStockOut')->getBlockReason($stockoutInfo[0]['block_reason']);
                $blockName .= '不能继续分拣';
            }else{
                D('StockoutOrder')->where(array('stockout_id'=>$stockout_ids[$i]))->save(array('block_reason'=>0));
            }
        }
        $result =  $this->printHotOrders($hotGoodsCode,$tradeNoList);
        $this->ajaxReturn($result);
    }
    private function changeStallsOrderStatus($hotOrders,$hotCode){
        $is_finish = 1;
        foreach ($hotOrders as $ho){
            $stalls_detail_order_info = D('Purchase/StallsOrderDetail')->field('sort_status')->where(array('trade_no'=>$ho['src_order_no']))->find();
            if($stalls_detail_order_info['sort_status'] != 3){
                $is_finish = 0;
                break;
            }
        }
        if($is_finish ==1){
            D('Purchase/StallsOrder')->where(array('stalls_no'=>$hotCode))->save(array('status'=>90));
        }
    }
    private function checkPrintersAndTemplates($hotOrders){

        $user_id = get_operator_id();
        $template_data = D('Setting/UserData')->fetchSql(false)->field(array('data'))->where(array('user_id'=>$user_id,'type'=>7,'code'=>''))->select();
        $ltData = json_decode($template_data[0]['data'],true);
        $errInfo = array();
        foreach ($hotOrders as $ho){
            if($ho['bill_type'] !=2){continue;}
            $logisticsId = $ho['logistics_id'];
            if(array_key_exists($logisticsId,$ltData)){
                $template_id = $ltData[$logisticsId];
                $template_info = D('Setting/PrintTemplate')->field('title,content')->where(array('rec_id'=>$template_id))->find();
                $printer_info = json_decode($template_info['content'],true);
                if(empty($printer_info['default_printer'])){
                    if(!in_array($ho['logistics_name'],$errInfo)){
                        $errInfo[] =  $ho['logistics_name'];
                    }
                }
            }else{
                if(!in_array($ho['logistics_name'],$errInfo)){
                    $errInfo[] =  $ho['logistics_name'];
                }
            }
        }
        return $errInfo;
    }
    private function getTemplatesAndPrinters($logisticsId){

        $template_url = '';
        $custom_url = '';
        $printer = '';
        $print_data = array();
        $user_id = get_operator_id();
        $template_data = D('Setting/UserData')->fetchSql(false)->field(array('data'))->where(array('user_id'=>$user_id,'type'=>7,'code'=>''))->select();
        $ltData = json_decode($template_data[0]['data'],true);
        $result_info = array();
        if(array_key_exists($logisticsId,$ltData)){
            $template_id = $ltData[$logisticsId];
            $template_info = D('Setting/PrintTemplate')->field('title,content')->where(array('rec_id'=>$template_id))->find();
            $printe_content = json_decode($template_info['content'],true);
            $custom_url = $printe_content['custom_area_url'];
            $printer = $printe_content['default_printer'];
            if(!empty($printe_content['user_std_template_url'])){
                $template_url = $printe_content['user_std_template_url'];
            }else{
                $waybill = \Platform\Common\ManagerFactory::getManager('NewWayBill');
                $waybill -> getTemplates($result_info,$logisticsId);
                $template_url = $result_info['success'][0]->standard_template_url;
            }
        }
        $print_data['template_url'] = $template_url;
        $print_data['custom_url'] = $custom_url;
        $print_data['printer'] = $printer;
        return $print_data;
    }
    private function getWaybill($printOrder){

        $stockout_id = $printOrder['stockout_id'];
        $logistics_id = $printOrder['logistics_id'];
        $print_data = $this->getTemplatesAndPrinters($logistics_id);
        $standerTemplateUrl = $print_data['template_url'];
        $res = D('Stock/WayBill','Controller')->newGetWayBill($stockout_id,$logistics_id,$standerTemplateUrl,false);

        return $res;
    }
    public function settingDialog(){
        $base_set_data = $this->getUsetData();
        if(empty($base_set_data)){
            $base_set_data = json_encode(['hot_auto_print_logistics'=>'0','hot_auto_consign'=>'0']);
        }
        $this->assign('base_set',$base_set_data);
        $this->display('setting_dialog');
    }
    private function getUsetData(){
        $user_id = get_operator_id();
        $type = 7;
        $base_set_data = '';
        $base_set = D('Setting/UserData')->fetchSql(false)->field(array('code,data'))->where(array('user_id'=>$user_id,'type'=>$type,'code'=>'stalls_base_set'))->select();
        if(!empty($base_set)){
            $base_set_data = $base_set[0]['data'];
        }
        return $base_set_data;
    }
}