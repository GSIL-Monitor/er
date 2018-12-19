<?php
/**
 * 原始订单
 * author:luyanfeng
 */
namespace Trade\Controller;

use Common\Controller\BaseController;
use Common\Common\Factory;
use Common\Common\UtilDB;
use Common\Common\ExcelTool;
use Think\Exception\BusinessLogicException;
use Trade\Common\TradeFields;
use Common\Common\UtilTool;
use Think\Log;
use Think\Exception;

class OriginalTradeController extends BaseController {

    /**
     * @param int    $page
     * @param int    $rows
     * @param array  $search
     * @param string $sort
     * @param string $order
     * 返回原始订单列表
     * author:luyanfeng
     * table:api_trade
     */
    public function getOriginalTradeList($page = 1, $rows = 20, $search = array(), $sort = 'ate.rec_id', $order =
    'desc') {
        if (IS_POST) {
            $this->ajaxReturn(D("OriginalTrade")->getOriginalTradeList($page, $rows, $search, $sort, $order));
        } else {
            $id_list     = array(
                "datagrid"          => "original_trade_list_datagrid",
                "toolbar"           => "original_trade_list_toolbar",
                "tab_container"     => "original_trade_tab_container",
                "form"              => "original_trade_search_form",
                "response"          => "original_trade_submit_response",
                "response_datagrid" => "original_trade_submit_response_datagrid",
                "fileForm"          => "original_trade_file_form",
                "fileDialog"        => "original_trade_file_dialog",
                'invalid'           => "original_trade_dialog_invalid_goods",
                'invalid_goods'     => "original_trade_invalid_goods",
            );
            //获取配置
            $rows=get_config_value('page_limit',0);
            switch($rows){
                case '0':
                    $rows=20;break;
                case '1':
                    $rows=50;break;
                case '2':
                    $rows=100;break;
                default:
                    $rows=20;break;
            }
            $datagrid    = array(
                "id"      => $id_list["datagrid"],
                "options" => array(
                    "title"        => "",
                    "toolbar"      => $id_list["toolbar"],
                    "url"          => U("OriginalTrade/getOriginalTradeList"),
                    "style"        => "",
                    "class"        => "easyui-datagrid",
                    "pagination"   => true,
                    "singleSelect" => false,
                    "fitColumns"   => false,
                    'frozenColumns'=>D('Setting/UserData')->getDatagridField('Trade/Trade','originalorder',1),
                    "rownumbers"   => true,
                    "ctrlSelect"   => true,
                    'pageSize'     => $rows,
                    "method"       => "post"
                ),
                "fields"  => D('Setting/UserData')->getDatagridField('Trade/Trade','originalorder')
            );
            $arr_tabs    = array(
                array(
                    "id"    => $id_list["tab_container"],
                    "title" => "货品列表",
                    "url"   => U("OriginalTrade/getGoodsListTabs")
                ),
                array(
                    "id"    => $id_list["tab_container"],
                    "title" => "订单",
                    "url"   => U("OriginalTrade/getSalesOrderTabs")
                ),
                array(
                    "id"    => $id_list["tab_container"],
                    "title" => "操作日志",
                    "url"   => U("OriginalTrade/getOperateLog")
                )
            );
            $params      = array(
                "controller" => strtolower(CONTROLLER_NAME),
                "datagrid"   => array("id" => $id_list["datagrid"],),
                "tabs"       => array("id" => $id_list["tab_container"]),
                "search"     => array(
                    "form_id"      => $id_list["form"]
                )
            );
            $rule_url=array(
            		'warehouse_r'=>69,'logistics_m'=>67,'logistics_f'=>61,'area_alias'=>78,'gift'=>57,'remark_e'=>70
            );
            $rule_menu=array();
            $menu=D('Home/Menu')->getMenu();
            $menu=UtilTool::array2dict($menu,'id','');
            foreach ($rule_url as $k => $v)
            {
            	$rule_menu[$k]=$menu[$v];
            }
            //获取货品相关信息
            try
            {               
                $id_list['invalid_goods_total']=D('ApiTradeOrder')->getInvalidGoods();               
            }catch(BusinessLogicException $e)
            {
                $id_list['invalid_goods_total']=0;
            } 
            $shop_list[] = array("id" => "all", "name" => "全部");
            $list        = UtilDB::getCfgRightList(array("shop"));
            $shop_list   = array_merge($shop_list, $list["shop"]);
            $faq_url=C('faq_url');
            $this->assign('faq_url',$faq_url['submit_trade']);
            $this->assign("shop_list", json_encode($shop_list));
            $this->assign("params", json_encode($params));
            $this->assign("id_list", $id_list);
            $this->assign("arr_tabs", json_encode($arr_tabs));
            $this->assign("datagrid", $datagrid);
            $this->assign("rule",$rule_menu);
            $this->display('show');
        }
    }

    /**
     * tabs:货品列表
     * author:luyanfeng
     * table:api_trade_order
     */
    public function getGoodsListTabs() {
        if (IS_POST) {
            $id = I("post.id");
            $this->ajaxReturn(D("OriginalTrade")->getGoodsList($id));
        } else {
            $id_list  = array(
                "datagrid" => "tabs_trade_order_datagrid",
            );
            $datagrid = array(
                "id"      => $id_list["datagrid"],
                "options" => array(
                    "url"          => U('OriginalTrade/getGoodsListTabs'),
                    "singleSelect" => true,
                    "fitColumns"   => false,
                    "pagination"   => false,
                    "rownumbers"   => false
                ),
                "fields"  => TradeFields::getTradeFields("GoodsListTabs")
            );
            $this->assign("datagrid", $datagrid);
            $this->assign("id_list", $id_list);
            $this->display("tabs_trade_order");
        }
    }

    /**
     * tabs:处理单
     * author:luyanfeng
     * table:sales_trade
     */
    public function getSalesOrderTabs() {
        if (IS_POST) {
            $id = I("post.id");
            try {
                $result = D("OriginalTrade")->getOriginalTrade($id, "rec_id", array("rec_id", "tid"));
                $res    = D("OriginalTrade")->getSalesOrderTabs($result["data"][0]["tid"]);
            } catch (\Exception $e) {
                $res["total"] = 0;
                $res["rows"]  = array();
            }
            $this->ajaxReturn($res);
        } else {
            $id_list  = array(
                "datagrid" => "tabs_sales_order_datagrid"
            );
            $datagrid = array(
                "id"      => $id_list["datagrid"],
                "options" => array(
                    "url"          => U('OriginalTrade/getSalesOrderTabs'),
                    "class"        => "easyui-datagrid",
                    "singleSelect" => true,
                    "fitColumns"   => false,
                    "pagination"   => false,
                    "rownumbers"   => false
                ),
                "fields"  => TradeFields::getTradeFields("SalesTradeTabs")
            );
            $this->assign("datagrid", $datagrid);
            $this->assign("id_list", $id_list);
            $this->display("tabs_sales_trade");
        }
    }

    public function getOperateLog() {
        if (IS_POST) {
            $id = I("post.id");
            try {
                $res = D("OriginalTrade")->getOperateLog($id);
            } catch (\Exception $e) {
                $res["total"] = 0;
                $res["rows"]  = array();
            }
            $this->ajaxReturn($res);
        } else {
            $id_list  = array("datagrid" => "tabs_orginal_trade_log");
            $datagrid = array(
                "id"      => $id_list["datagrid"],
                "options" => array(
                    "url"          => U("OriginalTrade/getOperateLog"),
                    "singleSelect" => true,
                    "fitColumns"   => false,
                    "pagination"   => false,
                    "rownumbers"   => false
                ),
                "fields"  => TradeFields::getTradeFields("log")
            );
            $this->assign("datagrid", $datagrid);
            $this->assign("id_list", $id_list);
            $this->display("tabs_operate_log");
        }
    }

    /**
     * 递交
     * author:luyanfeng
     */
    public function submitOriginalTrade() {
        $id = I('post.id', '', C('JSON_FILTER'));
        $this->ajaxReturn(D("OriginalTrade")->submitOriginalTrade($id));
    }

    /**
     * 导入原始订单
     */
    /*public function importTrade() {
        //获取表格相关信息
        $file = $_FILES["file"]["tmp_name"];
        $name = $_FILES["file"]["name"];
        //读取表格数据
        try {
            $excelData = UtilTool::Excel2Arr($name, $file, "OriginalTradeImport");
        } catch (\Exception $e) {
            $res = array("status" => 0, "info" => $e->getMessage());
            $this->ajaxReturn($res);
        }
        //记录数据保存失败的信息
        $err_msg       = array();
        $OriginalTrade = D("OriginalTrade");
        foreach ($excelData as $sheet) {
            for ($k = 1; $k < count($sheet); $k++) {
                $row = $sheet[$k];
                //分类处理数据
                $i                         = 0;
                $data["shop_name"]         = $row[$i++];
                $data["tid"]               = $row[$i++];
                $data["oid"]               = $row[$i++];
                $data["status"]            = $row[$i++];
                $data["pay_status"]        = $row[$i++];
                $data["receiver_name"]     = $row[$i++];
                $data["receiver_province"] = $row[$i++];
                $data["receiver_city"]     = $row[$i++];
                $data["receiver_district"] = $row[$i++];
                $data["receiver_mobile"]   = $row[$i++];
                $data["receiver_telno"]    = $row[$i++];
                $data["receiver_zip"]      = $row[$i++];
                $data["buyer_nick"]        = $row[$i++];
                $data["receiver_address"]  = $row[$i++];
                $data["delivery_term"]     = $row[$i++];
                $data["receivable"]        = $row[$i++];
                $data["post_amount"]       = $row[$i++];
                $data["discount"]          = $row[$i++];
                $data["trade_time"]        = $row[$i++];
                $data["pay_time"]          = $row[$i++];
                $data["buyer_message"]     = $row[$i++];
                $data["remark"]            = $row[$i++];
                $data["invoice_type"]      = $row[$i++];
                $data["invoice_title"]     = $row[$i++];
                $data["invoice_content"]   = $row[$i++];
                $data["pay_method"]        = $row[$i++];
                $data["goods_no"]          = $row[$i++];
                $data["spec_no"]           = $row[$i++];
                $data["num"]               = $row[$i++];
                $data["price"]             = $row[$i++];
                $data["total_amount"]      = $row[$i++];
                $data["gift_type"]         = $row[$i++];
                $data["remark"]            = $row[$i++];
                try {
                    $OriginalTrade->importTrade($data);
                } catch (\Exception $e) {
                    $err_msg[] = array("id" => $k + 1, "message" => $e->getMessage(), "result" => "失败");
                }
            }
        }
        //整理结果并返回
        if (count($err_msg) == 0) $res = array("status" => 1, "info" => "操作成功");
        else $res = array("status" => "2", "info" => $err_msg);
        $this->ajaxReturn(json_encode($res), "EVAL");
    }*/


    /**
     * 导入原始订单的接口
     */
    public function importTrade() {
        //获取Excel表格相关的数据
        $file = $_FILES["file"]["tmp_name"];
        $name = $_FILES["file"]["name"];
        //读取表格数据
        try {
            $excelClass = new ExcelTool();
            $excelClass->checkExcelFile($name,$file);
            $excelClass->uploadFile($file,"GoodsGoodsImport");
            $count = $excelClass->getExcelCount();
            if(workTimeUploadNum()<$count){
                SE(UtilTool::UPPER_UPLOAD);
            }
            $excelData = $excelClass->Excel2Arr($count);
            /*$enable_check_by_template = true;//是否通过模板比对上传文件
            $template_file_name = "订单导入模板.xls";
            $template_file_sub_path = APP_PATH."Runtime/File/";
            $template_file_path = $template_file_sub_path.$template_file_name;
            if(file_exists($template_file_path)){
                $template_excelClass = new ExcelTool();
                $template_count = $template_excelClass->getExcelCount();
                $template_excelData = $template_excelClass->Excel2Arr($template_count);
            }else{
                //模板路径有问题，关闭校验，发送邮件
                $enable_check_by_template = false;
                \Think\Log::write ( '订单导入模板比对失败，订单导入模板路径有误，模板路径：'.$template_file_path ,\Think\Log::ERR);
            }*/
        } catch (\Exception $e) {
            $res = array("status" => 1, "info" => $e->getMessage());
            $this->ajaxReturn(json_encode($res), "EVAL");
        }
        //记录插入数据的错误信息
        $err_msg = array();
        //记录订单数据
        $trade = array();
        //$sheet_index = 0;
        foreach ($excelData as $sheet) {
            //表头校验
            /*if( $enable_check_by_template==true ){
                if($sheet_index==0){
                    for ($t=0;$t<count($template_excelData['Sheet1'][0]);$t++){
                        if(!(trim($template_excelData['Sheet1'][0][$t]) == trim($sheet[0][$t]))){
                            $res['status'] = 1;
                            $res['info']   = '文件第一行数据有误，请参照模板文件';
                            $this->ajaxReturn(json_encode($res), "EVAL");
                        }
                    }
                }else{
                    $sheet_index++;
                    continue;
                }
            }*/
            if($sheet[0][6]!='收件人'){
                $res = array("status" => 1, "info" => '模板已更新，请重新下载模板');
                $this->ajaxReturn(json_encode($res), "EVAL");
            }

            for ($k = 1; $k < count($sheet); $k++) {
                $row = $sheet[$k];
                if (UtilTool::checkArrValue($row)) continue;
                //获取一条订单数据
                $i                 = 0;
                $shop_name         = trim($row[$i++]);//店铺名称
                $tid               = trim($row[$i++]);//原始单号
                $oid               = trim($row[$i++]);//原始订单子订单号
                $status            = empty($row[$i])?'待发货' : $row[$i];//订单状态
                $i++;
                $pay_status        = $row[$i++];//支付状态
                $delivery_term     = $row[$i++];//发货条件
                $receiver_name     = trim($row[$i++]);//收件人
                $receiver_province = trim($row[$i++]);//省
                $receiver_city     = trim($row[$i++]);//市
                $receiver_district = trim($row[$i++]);//区
                $receiver_address  = trim($row[$i++]);//地址
                $goods_name        = $row[$i++];//货品名称
                $spec_name         = $row[$i++];//规格名称
                $goods_no          = trim($row[$i++]);//货品商家编码
                $spec_no           = trim($row[$i++]);//规格商家编码
                $goods_id          = trim($row[$i++]);//货品ID
                $spec_id           = trim($row[$i++]);//规格ID
                $goods_num         = trim($row[$i++]);//货品数量
                $price             = trim($row[$i++]);//货品价格
                $receiver_mobile   = trim($row[$i++]);//手机
                $receiver_telno    = trim($row[$i++]);//固话
                $receiver_zip      = trim($row[$i++]);//邮编
                $nickname          = trim($row[$i++]);//网名
                $trade_time        = trim($row[$i++]);//下单时间
                $pay_time          = trim($row[$i++]);//付款时间
                $buyer_message     = trim($row[$i++]);//买家备注
                $trade_remark      = trim($row[$i++]);//客服备注
                $invoice_type      = trim($row[$i++]);//发票类型
                $invoice_title     = trim($row[$i++]);//发票抬头
                $invoice_content   = trim($row[$i++]);//发票内容
                $pay_method        = trim($row[$i++]);//支付方式
                $share_post        = trim($row[$i++]);//分摊邮费
                $share_discount    = trim($row[$i++]);//分摊优惠
                $share_paid        = trim($row[$i++]);//分摊已付货款
                /*$gift_type         = $row[$i++];*/
                $gift_type    = 0;
                $order_remark = $row[$i++];//备注
                //先构造子订单的数据
                $order = array(
                    "oid"            => $oid,
                    'goods_name'     => $goods_name,
                    'spec_name'      => $spec_name,
                    'goods_id'       => $goods_id,
                    'spec_id'        => $spec_id,
                    "goods_no"       => $goods_no,
                    "spec_no"        => $spec_no,
                    "num"            => $goods_num,
                    "price"          => $price,
                    "share_post"     => $share_post,
                    "share_discount" => $share_discount,
                    "paid"           => $share_paid,
                    "gift_type"      => $gift_type,
                    "remark"         => $order_remark
                );
                //构造原始订单的数据
                if (!is_array($trade[$tid])) {
                    $trade[$tid] = array(
                        "shop_name"         => $shop_name,
                        "tid"               => $tid,
                        "trade_status"      => $status,
                        "pay_status"        => $pay_status,
                        "receiver_name"     => $receiver_name,
                        "receiver_province" => $receiver_province,
                        "receiver_city"     => $receiver_city,
                        "receiver_district" => $receiver_district,
                        "receiver_address"  => $receiver_address,
                        "receiver_mobile"   => $receiver_mobile,
                        "receiver_telno"    => $receiver_telno,
                        "receiver_zip"      => $receiver_zip,
                        "receiver_area"     => $receiver_province . " " . $receiver_city . " " . $receiver_district,
                        "buyer_nick"        => $nickname,
                        "delivery_term"     => $delivery_term,
                        "trade_time"        => $trade_time,
                        "pay_time"          => $pay_time,
                        "buyer_message"     => $buyer_message,
                        "remark"            => $trade_remark,
                        "invoice_type"      => $invoice_type,
                        "invoice_title"     => $invoice_title,
                        "invoice_content"   => $invoice_content,
                        "pay_method"        => $pay_method,
                        "order"             => array()
                    );
                }
                if (isset($trade[$tid]["order"][$oid])) {
                    $err_msg[] = array("tid" => ''.$tid, "result" => "失败", "message" => "该原始订单包含多条重复的 " . $oid . " 子订单");
                } else {
                    $trade[$tid]["order"][$oid] = $order;
                }
            }
        }
        //将订单插入数据库
        try {
            D("OriginalTrade")->importTrade($trade, $err_msg);
            $res = count($err_msg) > 0 ? array("status" => 2, "info" => $err_msg) : array("status" => 0, "info" => "操作成功");
        } catch (BusinessLogicException $e) {
            $res = array("status" => 1, "info" => $e->getMessage());
        } catch (\Exception $e) {
            Log::write($e->getMessage());
            $res = array("status" => 1, "info" => self::UNKNOWN_ERROR);
        }
        try{
            if($res['status']==0 || $res['status']==2){
                $id=array();
                $cof=get_config_value( "order_auto_submit",0);
                if($cof ==1){
                    D('OriginalTrade')->submitOriginalTrade($id);
                }
            }
        }catch (\Exception $e)
        {
            Log::write($e->getMessage());
            $res = array("status" => 1, "info" => self::UNKNOWN_ERROR);
        }
        $this->ajaxReturn(json_encode($res), "EVAL");
    }
    public function downloadTemplet(){
        $file_name = "订单导入模板.xls";
        $file_sub_path = APP_PATH."Runtime/File/";
        try{
            ExcelTool::downloadTemplet($file_name,$file_sub_path);
        } catch (BusinessLogicException $e){
            Log::write($e->getMessage());
            echo '对不起，模板不存在，下载失败！';
        } catch (\Exception $e) {
            Log::write($e->getMessage());
            echo parent::UNKNOWN_ERROR;
        }
    }

    public function deleteUpload(){
    	$ids = I('post.ids', '', C('JSON_FILTER'));
    	try{
    		$data=D('OriginalTrade')->deleteUpload($ids,get_operator_id());
    		$result=array(
    				'check'=>$data['delete'],
    				'status'=>$data['status'],
    				'info'=>array('total'=>count($data['fail']),'rows'=>$data['fail']),//失败提示信息
    				'data'=>array('total'=>count($data['success']),'rows'=>$data['success']),//成功的数据
    		);
    	}catch (BusinessLogicException $e){
    		$this->error($e->getMessage());
    	}catch (Exception $e){
    		$this->error($e->getMessage());
    	}
    	$this->ajaxReturn($result);
    }

    public function exportToExcel(){
        if(!self::ALLOW_EXPORT){
            echo self::EXPORT_MSG;
            return false;
        }
        $id_list = I('get.id_list');
        $type = I('get.type');
        $result = array('status'=>0,'info'=>'');
        try{
            if($id_list==''){
                $search = I('get.search','',C('JSON_FILTER'));
                foreach ($search as $k => $v) {
                    $key=substr($k,7,strlen($k)-8);
                    $search[$key]=$v;
                    unset($search[$k]);
                }
                D('OriginalTrade')->exportToExcel('',$search,$type);
            }
            else{
                D('OriginalTrade')->exportToExcel($id_list,array(),$type);
            }
        }
        catch (BusinessLogicException $e){
            $result = array('status'=>1,'info'=> $e->getMessage());
        }catch (\Exception $e) {
            $result=array('status'=>1,'info'=>parent::UNKNOWN_ERROR);
        }
        echo $result['info'];
    }
}