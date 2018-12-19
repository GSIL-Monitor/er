<?php
namespace Stock\Controller;
use Common\Controller\BaseController;
use Think\Exception\BusinessLogicException;
use Common\Common\DatagridExtention;
use Common\Common\UtilDB;
//use Common\Common\UtilTool;
//use Common\Common\ExcelTool;
//use Thinl\Model;
//use Stock\StockCommonField;
//use Stock\StockPurchaseManagementField;
use Platform\Common\ManagerFactory;

class StallsPickListController  extends BaseController
{
    public function show(){
        try{
            $id_list = array();
            $need_ids = array('form','toolbar','tab_container','hidden_flag','datagrid','more_content','edit','hidden_flag','delete','file_form','file_dialog');
            $this->getIDList($id_list,$need_ids,'','');
            $datagrid = array(
                'id'        =>  $id_list['datagrid'],
                'style'=>'',
                'class'=>'easyui-datagrid',
                'options'   => array(
                    'title'         =>  '',
                    'toolbar'       =>  "#{$id_list['toolbar']}",
                    'fitColumns'    =>  true,
                    'singleSelect'  =>  false,
                    'ctrlSelect'    =>  true,
                    'pagination'    =>  false,
                ),
                'fields' => D('Setting/UserData')->getDatagridField('Stock/StallsPickList','purchasepick'),
            );
            $params = array(
                'datagrid'=>array(
                    'id'=>$id_list['datagrid'],
                ),
                'search'=>array(
                    'form_id'=> $id_list['form'],
                ),
                'select'=>array(
                    'id'        =>'flag_set_dialog',
                    'url' => U('StallsPickList/showGoodsList'),
                    'title' => '条码选择货品',
                ),
            );
            $user_id = get_operator_id();
            $type = 7;
            $base_set = D('Setting/UserData')->fetchSql(false)->field(array('code,data'))->where(array('user_id'=>$user_id,'type'=>$type,'code'=>'stalls_base_set'))->select();
            $base_set_data = $base_set[0]['data'];
            if(empty($base_set)){
                $base_set_data = json_encode(['voice_alert'=>'0','print_logistics'=>'0','print_tag'=>'0','stalls_mode'=>'0','stockout_sort_auto_consign'=>'0']);
            }
            $print_set = D('Setting/UserData')->fetchSql(false)->field(array('data'))->where(array('user_id'=>$user_id,'type'=>$type,'code'=>'stalls_print_set'))->select();
            $show_dialog = '-1';
            if(empty($print_set)){
                $show_dialog = '1';
            }
        }catch(\PDOException $e){
            \Think\Log::write($e->getMessage());
        }
        $this->assign('base_set',$base_set_data);
        $this->assign('show_dialog',$show_dialog);
        $this->assign('params',json_encode($params));
        $this->assign('id_list',$id_list);
        $this->assign('datagrid', $datagrid);
        $this->display('show');
    }
    public function getTemplates(){

        try{
            $fields = 'rec_id as id,title as name,content';
            $model = D('Setting/PrintTemplate');
            $result = $model->field($fields)->where(array('type'=>array('in',array(5,6,9)),'title'=>array('LIKE','吊牌_%')))->select();
            foreach($result as $key){
                $contents[$key['id']] = $key['content'];
            }
            $defaultTemplates = $model->field('content')->where(['title' => '系统默认自定义区'])->select();
            if(!empty($defaultTemplates)){
                $sysDefaultUrl = $defaultTemplates[0]['content'];
            }else{
                $sysDefaultUrl = '-1';
            }
            $user_id = get_operator_id();
            $type = 7;
            $code = 'stalls_print_set';
            $stallsPrintInfo = $this->getUserData($type,$code);
			//$stallsData = D('Setting/UserData')->fetchSql(false)->field(array('data'))->where(array('user_id'=>$user_id,'type'=>$type,'code'=>$code))->select();
            //$stallsPrintInfo = json_decode($stallsData[0]['data'],true);
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
        $this->assign('sys_default_template',$sysDefaultUrl);
        $this->assign('printerInfo',$printerInfo);
        $this->display('printers_select');
    }
    public function setLogisticsAndTemplatesDialog(){

        $type = I('get.type');
        $fields = get_field('StallsPickList','set_logistics_templates');
        $id_list = self::getIDList($id_list,array('datagrid'),'','setprinters');
        $params = array(
            'datagrid'=>array('id'=>$id_list['datagrid']),
        );
        $datagrid = array(
            'id' => $id_list['datagrid'],
            'options' => array(
                'title' => '',
                'url' => U('StallsPickList/getLogisticsAndTemplates'),
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
        $this->assign('type',$type);
        $this->assign('templates_info',json_encode($templates_info));
        $this->display('dialog_set_printer_template');
    }
    public function getLogisticsAndTemplates(){

        $logisticsInfo = D('Setting/Logistics')->field('logistics_id,logistics_name')->where(array('bill_type'=>2,'is_disabled'=>0))->select();
        $model = D('Setting/PrintTemplate');
        $type = 7;
        $code = '';
        $userData = $this->getUserData($type,$code);
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
                    $this->saveUserData($type,$code,$logistics_id,$template_id);
                }
            }
        }catch(BusinessLogicException $e){
            \Think\Log::write($e->getMessage());
            $result =  array('status'=>1,'info'=>$e->getMessage(),'data'=>array());
        }catch(\Exception $e){
            \Think\Log::write($e->getMessage());
            $result = array('status'=>1,'info'=>self::UNKNOWN_ERROR,'data'=>array());
        }
        $this->ajaxReturn($result);
    }
    public function savePrintersAndTemplates($templatesAndLogisticsInfo){
        try{
            $result = ['status'=>0,'info'=>'设置成功'];
            $code = 'stalls_print_set';
            $user_id = get_operator_id();
            $stalls_data = array(
                'type'=>7,
                'code'=>$code,
                'data'=>$templatesAndLogisticsInfo,
                'user_id'=>$user_id,
            );
            D('Setting/UserData')->add($stalls_data,'',true);

        }catch(BusinessLogicException $e){
            \Think\Log::write($e->getMessage());
            $result =  array('status'=>1,'info'=>$e->getMessage(),'data'=>array());
        }catch(\Exception $e){
            \Think\Log::write($e->getMessage());
            $result = array('status'=>1,'info'=>self::UNKNOWN_ERROR,'data'=>array());
        }
        return $this->ajaxReturn($result);
    }
    public function saveBaseSetting($data){

        try{
            $data=I('post.data','',C('JSON_FILTER'));
            $result = ['status'=>0,'info'=>'设置成功'];
            $code = 'stalls_base_set';
            $setType = 7;
            foreach($data as $type=>$checkVal){
                 $this->saveUserData($setType,$code,$type,$checkVal);
            }
        }catch(BusinessLogicException $e){
            \Think\Log::write($e->getMessage());
            $result =  array('status'=>1,'info'=>$e->getMessage(),'data'=>array());
        }catch(\Exception $e){
            \Think\Log::write($e->getMessage());
            $result = array('status'=>1,'info'=>self::UNKNOWN_ERROR,'data'=>array());
        }
        return $this->ajaxReturn($result);
    }
    public function saveUserData($type,$code,$k,$v){

        $jsonData = [];
        $user_id = get_operator_id();
        $data = D('Setting/UserData')->fetchSql(false)->field(array('data'))->where(array('user_id'=>$user_id,'type'=>$type,'code'=>$code))->select();
        $data = $data[0]['data'];
        if(is_object(json_decode($data))){
            $jsonData = json_decode($data,true);
            $jsonData[$k] = $v;
        }else{
            $jsonData[$k] = $v;
        }

        $stalls_data = array(
            'type'=>$type,
            'code'=>$code,
            'data'=>json_encode($jsonData),
            'user_id'=>$user_id,
        );
        D('Setting/UserData')->add($stalls_data,'',true);
    }
    public function getUserData($type,$code){
        $user_id = get_operator_id();
        $templatesData = D('Setting/UserData')->fetchSql(false)->field(array('data'))->where(array('user_id'=>$user_id,'type'=>$type,'code'=>$code))->select();
        $templatesData = json_decode($templatesData[0]['data'],true);
        return $templatesData;
    }
    public function getGoodsList($sortData){

        $trade_no=$sortData['data']['sort_data']['trade_no'];
        $search = ['src_order_no'=>$trade_no];
        $stockoutOrderData = D('StockOutOrder')->searchStockoutList(1, 20, $search, 'id', 'desc', 'stockoutPrint');
        $row = $stockoutOrderData['rows'][0];
        $sortData['data']['row'] = $row;

        $sql="SELECT SUM(IF(slgd.sort_status>0,1,0)) as sorted_num FROM stalls_less_goods_detail WHERE trade_no='".$trade_no."'";

        //查找订单对应的货品信息
        $sql="SELECT sto.spec_id,sto.goods_name,sto.spec_name,sto.spec_no,sto.goods_no,sto.actual_num,sto.order_price,sto.share_amount,sto.share_post,sto.paid,sto.weight,st.goods_count,so.box_no
                FROM sales_trade_order sto
                LEFT JOIN sales_trade st ON st.trade_id=sto.trade_id
                LEFT JOIN stockout_order so ON so.src_order_id=st.trade_id
                WHERE st.trade_no='".$trade_no."'";
        $goods_list=M()->query($sql);

        //考虑到动态分配分拣框的情况，box_no从订单中去获取
        $box_no=$goods_list[0]['box_no'];
        $box_id=0;
        if($box_no!=''){//找到id
            $box_id=M('sorting_wall_detail')->field('box_id')->where(array('box_no'=>$box_no))->find();
        }
        $sortData['data']['sort_data']['box_id']=$box_id['box_id'];
        $sortData['data']['sort_data']['box_no']=$box_no;

        $sorted_num=0;$unsorted_num=0;
        foreach($goods_list as $k=>$v){
            $spec_sql =  M('box_goods_detail')->fetchSql(true)->field('num')->where(array('spec_id'=>array('eq',$v['spec_id']),'trade_no'=>array('eq',$trade_no),))->find();
            $spec_id_num =  M('box_goods_detail')->field('num')->where(array('spec_id'=>array('eq',$v['spec_id']),'trade_no'=>array('eq',$trade_no),))->find();

            $goods_list[$k]['sorted_num'] = number_format($spec_id_num['num'],4);
            $sorted_num+=$goods_list[$k]['sorted_num'];
        }
		$sorted_num = $sorted_num?$sorted_num:1;
        $sortData['data']['goods_list']=json_encode($goods_list);
        $unsorted_num=$goods_list[0]['goods_count']-$sorted_num;
        $sortData['data']['row']['trade_goods_count']=$goods_list[0]['goods_count'];
        $sortData['data']['row']['sorted_num']=$sorted_num;
        $sortData['data']['row']['unsorted_num']=$unsorted_num;

        $result['status'] = 4;
        $result['msg'] = $sortData['msg'];
        $result['data'] = $sortData['data'];
        return $result;
    }
    public function pickList($uniqueCode,$goodInfo = '',$sorting,$sortingCode){

        $result = array();
        $result['status'] = 0;
        $result['msg'] = 'success';
        $sortData = array();
        $orderType = 'stalls';
        if(empty($uniqueCode)){
            $result['status'] = 1;
            $result['msg'] = '请扫描或输入唯一码';
            return $this->ajaxReturn($result);
        }
        try{
            $uniqueCode = trim($uniqueCode);
            $goodsBarcodeModel = D('Goods/GoodsBarcode');
            $barCodeInfo = $goodsBarcodeModel->alias('gb')->field('barcode')->fetchSql(false)->where(array('trim(barcode)' => $uniqueCode))->select();
            $model = D('Purchase/StallsOrderDetail');

            if(!($barCodeInfo == '' || empty($barCodeInfo))){
                /******************非档口单分拣******************/
                $orderType = 'normal';
                $goodsBarcodeModel->execute("set @tmp_goods_name='',@tmp_short_name='',@tmp_merchant_no='',@tmp_spec_name='',@tmp_spec_code='',@tmp_goods_id='',@tmp_spec_id='',@tmp_sn_enable=0;");
                $fields = array(
                    '(`type`=2) AS is_suite',
                    'target_id',
                    'FN_GOODS_NO(`type`,target_id) goods_no',
                    '@tmp_merchant_no spec_no',
                    '@tmp_goods_name goods_name',
                    '@tmp_short_name short_name',
                    '@tmp_spec_name spec_name',
                    '@tmp_spec_code spec_code',
                    '@tmp_spec_id spec_id',
                    '@tmp_sn_enable is_sn_enable'
                );
                $barCodeInfo = $goodsBarcodeModel->alias('gb')->field($fields)->fetchSql(false)->where(array('trim(barcode)' => $uniqueCode))->select();
                if($barCodeInfo == '' || empty($barCodeInfo)){
                    $result = array('status'=>1,'msg'=>'没有该条形码');
                    return $this->ajaxReturn($result);
                }elseif (count($barCodeInfo)>1){
                    if($goodInfo == ''){
                        $result = array('status'=>3,'msg'=>'该条形码对应多个货品');
                        $result['data'] = $barCodeInfo;
                        return $this->ajaxReturn($result);
                    }else{
                        $selectedGoodInfo = json_decode($goodInfo,true);
						if($sorting == 1){
							$sortData = $model->pickListOrder($selectedGoodInfo,$sortingCode);
						}else{
							$sortData = $model->pickListNotStallsOrder($selectedGoodInfo);
						}
                        if($sortData['status'] ==2){
                            $result['status'] = $sortData['status'];
                            $result['msg'] = $sortData['msg'];
                        }
                    }
                }elseif (count($barCodeInfo) == 1){
					if($sorting == 1){
						$sortData = $model->pickListOrder($barCodeInfo[0],$sortingCode);
					}else{
						$sortData = $model->pickListNotStallsOrder($barCodeInfo[0]);
					}
                    if($sortData['status'] ==2){
                        $result['status'] = $sortData['status'];
                        $result['msg'] = $sortData['msg'];
                    }
                }else{
                    $result = array('status'=>1,'msg'=>'未知错误，请联系管理员');
                    return $this->ajaxReturn($result);
                }

            }else{
                /******************档口单分拣********************/
                $sortData = $model->pickListStallsOrder($uniqueCode);
                if($sortData['status'] ==2){
                    $result['status'] = $sortData['status'];
                    $result['msg'] = $sortData['msg'];
                }elseif ($sortData['status'] ==3){
                    $result = $this->getGoodsList($sortData);
                    $this->ajaxReturn($result);
                }
            }
            $sortData['data']['print_data']['order_type'] = $orderType;
            $printDataInfo = $sortData['data']['print_data'];
            $user_id = get_operator_id();
            $stockout_id = $printDataInfo['stockout_id'];
            $src_order_no = $printDataInfo['src_order_no'];
            $logistics_id = $printDataInfo['logistics_id'];
            $standerTemplateUrl = $printDataInfo['stander_template_url'];

            //一单多货完成分拣时才打印物流单。
            $sortData['data']['waybill_print_info'] = -1;
            $sortData['data']['custom_template_url'] = '';

            //打印物流单
			$set_data = $this->getUserData(7,'stalls_base_set');
		    if($standerTemplateUrl != '' && isset($set_data['print_logistics']) && $set_data['print_logistics'] == 1){
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
                $sortData['data']['waybill_print_info'] = $res['data']['success'][$stockout_id];

                // 自定义模板
                $userCustomTemplate = D('Setting/UserData')->field(array('data'))->where(array('user_id'=>$user_id,'type'=>7,'code'=>''))->select();
                if(!empty($userCustomTemplate)){
                    $userCustomTemplate = json_decode($userCustomTemplate[0]['data'],true);
                    if(!empty($userCustomTemplate[$logistics_id])){
                        $templateId = $userCustomTemplate[$logistics_id];
                        $customTemplateUrl = D('Setting/PrintTemplate')->where(array("rec_id"=>$templateId))->select();
                        $templateContent = $customTemplateUrl[0]['content'];
                        $sortData['data']['custom_template_url'] = json_decode($templateContent,true)['custom_area_url'];
                        $logisticsPrinter = json_decode($templateContent,true)['default_printer'];
                        if(!empty($logisticsPrinter)){
                            $sortData['data']['print_data']['logistic_printer'] = $logisticsPrinter;
                        }
                    }else{
                        $sortData['data']['custom_template_url'] = '';
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
                $sortData['data']['goods'] = $goods;

            }
		    // 发货
			//$setting_config = get_config_value(array('stockout_sort_auto_consign'));
			if(($set_data['stockout_sort_auto_consign'] ==1 && $sortData['data']['status'] == 3) || ($sortData['data']['status'] == 0 && $set_data['stockout_sort_auto_consign'] == 1 && $set_data['stalls_goods_auto_consign'] == 1) || ($sortData['data']['status'] == 1 && $set_data['stockout_sort_auto_consign'] == 1 && empty($set_data['stalls_goods_auto_consign']))){
				$stockoutOrderRes = $this->consignStockoutOrder($stockout_id,2);
				if($stockoutOrderRes['status'] !=0){
					if($stockoutOrderRes['status'] == 1){
						$result['msg'] = $stockoutOrderRes['msg'];
					}else{
						$result['msg'] = $stockoutOrderRes['data']['fail'][0]['msg'];
					}
					$result['status'] = 1;
					return $this->ajaxReturn($result);
				}
			}

            $trade_no=$sortData['data']['sort_data']['trade_no'];
            $search = ['src_order_no'=>$trade_no];
            $stockoutOrderData = D('StockOutOrder')->searchStockoutList(1, 20, $search, 'id', 'desc', 'stockoutPrint');
            $row = $stockoutOrderData['rows'][0];
            $sortData['data']['row'] = $row;

            //$sql="SELECT SUM(IF(slgd.sort_status>0,1,0)) as sorted_num FROM stalls_less_goods_detail WHERE trade_no='".$trade_no."'";

            //查找订单对应的货品信息
            $sql="SELECT sto.spec_id,sto.goods_name,sto.spec_name,sto.spec_no,sto.goods_no,sto.actual_num,sto.order_price,sto.share_amount,sto.share_post,sto.paid,sto.weight,st.goods_count,so.box_no
                FROM sales_trade_order sto
                LEFT JOIN sales_trade st ON st.trade_id=sto.trade_id
                LEFT JOIN stockout_order so ON so.src_order_id=st.trade_id
                WHERE st.trade_no='".$trade_no."'";
            $goods_list=M()->query($sql);

            //考虑到动态分配分拣框的情况，box_no从订单中去获取
            $box_no=$goods_list[0]['box_no'];
            $box_id=0;
            if($box_no!=''){//找到id
                $box_id=M('sorting_wall_detail')->field('box_id')->where(array('box_no'=>$box_no))->find();
            }
            $sortData['data']['sort_data']['box_id']=$box_id['box_id'];

            $sorted_num=0;$unsorted_num=0;
            foreach($goods_list as $k=>$v){
                $spec_sql =  M('box_goods_detail')->fetchSql(true)->field('num')->where(array('spec_id'=>array('eq',$v['spec_id']),'trade_no'=>array('eq',$trade_no),))->find();
                $spec_id_num =  M('box_goods_detail')->field('num')->where(array('spec_id'=>array('eq',$v['spec_id']),'trade_no'=>array('eq',$trade_no),))->find();

                $goods_list[$k]['sorted_num'] = number_format($spec_id_num['num'],4);
                $sorted_num+=$goods_list[$k]['sorted_num'];
            }
			$sorted_num = $sorted_num?$sorted_num:1;
            $sortData['data']['goods_list']=json_encode($goods_list);
            $unsorted_num=$goods_list[0]['goods_count']-$sorted_num;
			
            $sortData['data']['row']['trade_goods_count']=$goods_list[0]['goods_count'];
            $sortData['data']['row']['sorted_num']=$sorted_num;
            $sortData['data']['row']['unsorted_num']=$unsorted_num;
            $sortData['data']['stalls_base_set'] = $set_data;
            $result['data'] = $sortData['data'];
        }catch(\PDOException $e){
            $msg = $e->getMessage();
            \Think\Log::write(__CONTROLLER__.'--pickList--'.$e->getMessage());
            $result['status'] = 1;
            $result['msg'] = $msg;
        }catch(BusinessLogicException $e){
            $msg = $e->getMessage();
            \Think\Log::write(__CONTROLLER__.'--pickList--'.$e->getMessage(),\Think\Log::WARN);
            $result['status'] = 1;
            $result['msg'] = $msg;
        }catch(\Exception $e){
            $msg = $e->getMessage();
            \Think\Log::write(__CONTROLLER__.'--pickList--'.$e->getMessage());
            $result['status'] = 1;
            $result['msg'] = $msg;
        }
        return $this->ajaxReturn($result);
    }
    public function consignStockoutOrder($id,$is_force=0){

        $result = array(
            'status'=>0,
            'msg'=>'success',
            'data'=>array()
        );
        $fail = array();
        $success = array();
        if($id =='')
        {
            $result['msg'] ="该订单没有生成对应的出库单";
            $result['status'] = 1;
            return $result;
        }
        D('Stock/SalesStockOut')->consignStockoutOrder($id,$fail,$success,$is_force);

        if(!empty($fail))
        {
            $result['status']=2;
        }
        $result['data']=array(
            'fail' => $fail,
            'success' => $success
        );
        return $result;
    }
    public function chgPrintStatus($uniqueCode,$orderNo,$taskId){

        $result = array(
            'status'=>0,
            'msg'=>'success',
            'data'=>array()
        );
        try{
            D('Purchase/StallsOrderDetail')->changePrintStatus($uniqueCode,$orderNo,$taskId);
        }catch(BusinessLogicException $e){
            \Think\Log::write($e->getMessage());
            $result =  array('status'=>1,'info'=>$e->getMessage(),'data'=>array());
        }catch(\Exception $e){
            \Think\Log::write($e->getMessage());
            $result = array('status'=>1,'info'=>self::UNKNOWN_ERROR,'data'=>array());
        }
        return $this->ajaxReturn($result);
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
        $this->display('barcode_goods_list');
    }
	public function sorting_list($sorting_list){
		try{
			$result = array('status'=>0,'info'=>'');
			$lessGoodsOrder =M('stockout_print_batch')->field('queue')->where(array('pick_list_no'=>$sorting_list))->find();      
            if(empty($lessGoodsOrder) || empty($lessGoodsOrder['queue'])){
                SE('分拣单号不正确');
            }
		}catch(BusinessLogicException $e){
            \Think\Log::write($e->getMessage());
            $result =  array('status'=>1,'info'=>$e->getMessage());
        }catch(\Exception $e){
            \Think\Log::write($e->getMessage());
            $result = array('status'=>1,'info'=>self::UNKNOWN_ERROR);
        }
		$this->ajaxReturn($result);
	}
	public function cancelBlock($unique_code){

        try{
            $result = array('status'=>0,'info'=>'');
            $stallsOrder =  D('Purchase/StallsOrderDetail')->field('trade_no')->where(array('unique_code'=>$unique_code))->find();
            if(empty($stallsOrder)){
                SE('缺货明细不存在');
            }
            D('Stock/SalesStockOut')->unblockStockoutAndStallsOrder($stallsOrder['trade_no']);

        }catch(BusinessLogicException $e){
            \Think\Log::write($e->getMessage());
            $result =  array('status'=>1,'info'=>$e->getMessage());
        }catch(\Exception $e){
            \Think\Log::write($e->getMessage());
            $result = array('status'=>1,'info'=>self::UNKNOWN_ERROR);
        }
        $this->ajaxReturn($result);
    }
    //换货
    public function exchangeOrder($unique_code){
        $unique_code=is_json($unique_code);
        if(IS_POST){
            $result=array();
            try{
                $id=I('post.id');
                $old_orders=I('post.order');
                $new_orders=I('post.spec');
                D('Trade/SalesTrade')->execute('CALL I_DL_TMP_SUITE_SPEC()');
                $result=D('Purchase/StallsOrderDetail')->pickExchangeOrder($id,$old_orders,$new_orders,get_operator_id());
            }catch (BusinessLogicException $e){
                $result['status']=1;
                $result['info']=$e->getMessage();
            }catch (Exception $e){
                $result['status']=1;
            }
            $this->ajaxReturn($result);
        }else{
            $list=array();
            try{
                //根据唯一码找出对应的订单信息
                $res_trades_arr=D('Trade/TradeCheck')->getSalesTradeList(
                    'st.trade_id,st.trade_no,st.platform_id,st.shop_id,sh.shop_name,st.warehouse_id,st.warehouse_type,
						 st.trade_status,st.check_step,st.delivery_term,st.freeze_reason,st.refund_status,st.unmerge_mask,
						 st.customer_id,st.buyer_nick,st.receiver_name,st.receiver_address,st.receiver_mobile,st.receiver_telno,
						 st.receiver_zip,st.receiver_area,st.receiver_ring,st.to_deliver_time,st.dist_center,st.dist_site,
						 st.logistics_id,clg.logistics_name,st.buyer_message,st.cs_remark,st.print_remark,st.salesman_id,
						 he.fullname,st.checker_id,st.checkouter_id,st.flag_id,st.bad_reason,st.is_sealed,st.split_from_trade_id,
						 st.stockout_no,st.revert_reason,st.cancel_reason,st.trade_mask,st.reserve,st.modified,st.created',
                    array('slgd.unique_code'=>array('eq',$unique_code)),
                    'st',
                    'LEFT JOIN stalls_less_goods_detail slgd ON slgd.trade_id=st.trade_id
                     LEFT JOIN cfg_shop sh ON sh.shop_id=st.shop_id
					 LEFT JOIN hr_employee he ON st.salesman_id=he.employee_id
					 LEFT JOIN cfg_logistics clg ON clg.logistics_id=st.logistics_id'
                );
                $trade_id=$res_trades_arr[0]['trade_id'];
                $trade_no=$res_trades_arr[0]['trade_no'];
                $warehouse=$res_trades_arr[0]['warehouse_id'];
                $trade_info = D('Trade/SalesTrade')->field('stalls_id')->where(array('trade_id'=>$trade_id))->select();
                $stalls_id = $trade_info[0]['stalls_id'];
                $id_list=array(
                    'toolbar'=>'pick_trade_exchange_toolbar',
                    'form_id'=>'pick_trade_edit_form',
                    'toolbar_order'=>'pick_order_toolbar',
                    'exchange_dialog'=>$stalls_id ==0?'pick_dialog_exchange_order':'hot_pick_dialog_exchange_order',
                );
                $datagrid['spec']=array(
                    'id'=>$stalls_id ==0?'pick_exchange_spec':'hot_pick_exchange_spec',
                    'style'=>'',
                    'class'=>'easyui-datagrid',
                    'options'=> array(
                        'title'=>'',
                        'url'   =>U('StallsPickList/getExchangeSpecList', array('id'=>$trade_id)),
                        'toolbar' => "#{$id_list['toolbar']}",
                        'pagination'=>false,
                        'fitColumns'=>true,
                    ),
                    'fields' => get_field('Purchase/StallsOrder','exchange')
                );
                $datagrid['order']=array(
                    'id'=>$stalls_id ==0?'pick_exchange_order':'hot_pick_exchange_order',
                    'style'=>'',
                    'class'=>'easyui-datagrid',
                    'options'=> array(
                        'title'=>'',
                        'url'   =>U('StallsPickList/getExchangeOrderList', array('id'=>$trade_id)),
                        'toolbar' => "#{$id_list['toolbar_order']}",
                        'pagination'=>false,
                        'fitColumns'=>false,
                    ),
                    'fields' => get_field('Purchase/StallsOrder','exchange')
                );
                $this->assign('warehouse',$warehouse);
                $this->assign('id',$trade_id);
                $this->assign('unique_code',$unique_code);
                $this->assign('id_list',$id_list);
                $this->assign('datagrid',$datagrid);
                if($stalls_id ==0){
                    $this->display('dialog_trade_exchange');
                }else{
                    $this->display('hot_dialog_trade_exchange');
                }
            }catch(BusinessLogicException $e){
                $list[]=array('trade_no'=>$trade_no,'result_info'=>$e->getMessage());
                $data=array('total'=>count($list),'rows'=>$list);
                $this->assign('sales_trade_result_info',json_encode($data));
                $this->display('dialog_result_info');
            }

        }
    }
    //获取需要换货的货品
    public function getExchangeOrderList($id){
        $total=array();
        $list=array();
        try{
            $list=D('Trade/SalesTradeOrder')->getSalesTradeOrderList('sto.spec_id as id,sto.spec_id,sto.spec_no,sto.goods_name,sto.spec_name,sto.order_price as price,sto.actual_num as num',
                array('sto.trade_id'=>array('eq',$id),'ato.other_flags'=>array('eq',1)),
                'sto',
                ' LEFT JOIN sales_trade st ON st.trade_id=sto.trade_id
                  LEFT JOIN api_trade_order ato ON sto.src_tid=ato.tid AND sto.src_oid=ato.oid');
            $total=count($list);
            $data=array('total'=>$total,'rows'=>$list);
        }catch (\PDOException $e) {
            \Think\Log::write('search_trades:'.$e->getMessage());
            $data=array('total'=>0,'rows'=>array());
        }
        $this->ajaxReturn($data);
    }
    //获取换回的货品
    public function getExchangeSpecList($id){
        $total=array();
        $list=array();
        try{
            $trade_order_arr=D('Trade/SalesTradeOrder')->getSalesTradeOrderList('sto.shop_id,sto.src_oid,sto.src_tid',
                array('sto.trade_id'=>array('eq',$id),'ato.other_flags'=>array('eq',1)),
                'sto',
                ' LEFT JOIN sales_trade st ON st.trade_id=sto.trade_id
                  LEFT JOIN api_trade_order ato ON sto.src_tid=ato.tid AND sto.src_oid=ato.oid');
            $shop_id=$trade_order_arr[0]['shop_id'];
            //找到对应的原始子订单
            $str_oids='(';
            foreach($trade_order_arr as $k=>$v){
                $str_oids.="'".$v['src_oid']."',";
            }
            $str_oids=rtrim($str_oids,',').')';
            if($str_oids=='('){
                $data=array('total'=>0,'rows'=>array());
                $this->ajaxReturn($data);
            }
            $match_info=M('api_trade_order')->query('SELECT match_target_id,match_target_type,ags.is_manual_match FROM api_trade_order ato LEFT JOIN api_goods_spec ags ON ags.shop_id=ato.shop_id AND ags.goods_id=ato.goods_id AND ags.spec_id=ato.spec_id
                                                      WHERE ato.shop_id='.$shop_id.' AND ato.oid IN '.$str_oids .'');
            if(empty($match_info)){
                $data=array('total'=>0,'rows'=>array());
                $this->ajaxReturn($data);
            }
            $sum_list=array();
            $spec_ids='(';
            $suite_ids='(';
            foreach($match_info as $info){
                if(empty($info['match_target_type'])||$info['match_target_type']==0){
                    continue;
                }
                if($info['match_target_type']==1){//单品
                    $spec_ids.="'".$info['match_target_id']."',";
                }elseif($info['match_target_type']==2){
                    $suite_ids.="'".$info['match_target_id']."',";
                }
            }
            if($spec_ids!='('){
                $spec_ids=rtrim($spec_ids,',').')';
                $spec_list=M('goods_spec')->query('SELECT gs.spec_id as id,gs.spec_id,gs.spec_no,gg.goods_name,gs.spec_name,gs.retail_price as price,1 as num
                                                FROM goods_spec gs LEFT JOIN goods_goods gg ON gg.goods_id=gs.goods_id
                                                WHERE gs.spec_id IN '.$spec_ids.' AND gs.deleted=0');
            }
            if($suite_ids!='('){
                $suite_ids=rtrim($suite_ids,',').')';
                $suite_list=M('goods_suite_detail')->query("SELECT gsd.spec_id as id,gsd.spec_id,gs.spec_no,gs.spec_name,gg.goods_name,gs.retail_price as price,gsd.num,gs.deleted
                    FROM goods_suite_detail gsd LEFT JOIN goods_spec gs ON (gsd.spec_id=gs.spec_id)
                    LEFT JOIN goods_goods gg ON gs.goods_id=gg.goods_id
                    WHERE gsd.suite_id IN ".$suite_ids." AND gsd.num>0");
                //判断是否存在已经删除的单品
                $deleted=0;
                foreach($list as $v){
                    if($v['deleted']==1){
                        $deleted=1;
                    }
                }
                if($deleted==1){
                    \Think\Log::write('新货品组合装包含已删除单品');$list=array();
                }
            }
            if(empty($suite_list)){
                if(empty($spec_list)){
                    $data_list=array();
                }else{
                    $data_list=$spec_list;
                }
            }else{
                if(empty($spec_list)){
                    $data_list=$suite_list;
                }else{
                    $data_list=$spec_list;
                    foreach($suite_list as $suite){
                        $data_list[]=$suite;
                    }
                }
            }
            $total=count($data_list);
            $data=array('total'=>$total,'rows'=>$data_list);
        }catch (\PDOException $e) {
            \Think\Log::write($e->getMessage());
            $data=array('total'=>0,'rows'=>array());
        }
        $this->ajaxReturn($data);
    }

    //获取设置页面
    public function openPickSetting(){
        try{
            $user_id = get_operator_id();
            $type = 7;
            $base_set = D('Setting/UserData')->fetchSql(false)->field(array('code,data'))->where(array('user_id'=>$user_id,'type'=>$type,'code'=>'stalls_base_set'))->select();
            $base_set_data = $base_set[0]['data'];
            if(empty($base_set)){
                $base_set_data = json_encode(['voice_alert'=>'0','print_logistics'=>'0','print_tag'=>'0','stalls_mode'=>'0']);
            }
        }catch(\PDOException $e){
            \Think\Log::write($e->getMessage());
        }
        $this->assign('base_set',$base_set_data);
        $this->display('pick_setting');
    }

    //一键拆分
    public function  oneSplit($id){
        $id=intval($id);
        $result = array(
            'status'=>0,
            'info'=>'success',
            'data'=>array()
        );
        $fail = array();
        $success = array();
        $info = array();
        if(empty($id)||$id<=0)
        {
            $result['info'] ="没有找到对应的分拣框";
            $result['status'] = 1;
            $this->ajaxReturn($result);
        }
        $success[$key] = array();
        $info[$key] = D('Purchase/StallsOrderDetail')->oneSplit($id,$fail);
        if (!empty($fail))
        {
            $result['status'] = 2;
        }
        $result['stock_id'] = $info;
        $result['data']=array(
            'fail' => $fail,
        );
        $this->ajaxReturn($result);
    }
	public function newOrderPrintData($ids){
		try{
			$result = array('status'=>0,'msg'=>'','data'=>array());
			$stockoutInfo = D('Stock/StockOutOrder')->field('stockout_id,logistics_id,src_order_no')->where(array('stockout_id'=>$ids[0]))->select();
			if(empty($stockoutInfo)){
				$result['status'] = 1;
				$result['msg'] = '没有对应的出库单';
				return $this->ajaxReturn($result);
			}
			$stockout_id = $stockoutInfo[0]['stockout_id'];
			$logistics_id = $stockoutInfo[0]['logistics_id'];
			$logisticsData = D('Setting/Logistics')->getLogisticsInfo($logistics_id);
			$logisticsInfo = $logisticsData[0];
			if((int)$logisticsInfo['bill_type'] != 2){
				$result['status'] = 1;   
				$result['msg'] = '自动打印物流单只支持打印菜鸟电子面单，请前往“单据打印”界面打印物流单';
				return $this->ajaxReturn($result);
			}
			$fields = array('rec_id as id,type,title,content');
			$template_info = M('cfg_user_data')->field('data')->where(array('type'=>7,'code'=>'','user_id'=>get_operator_id()))->find();
			$template_info = json_decode($template_info['data'],true);
			$templatesData = D('Setting/PrintTemplate')->field($fields)->where(array('rec_id'=>$template_info[$logistics_id]))->select();
			//getTemplateByLogistics($fields,'4,8,7',$logisticsInfo['logistics_type'],false);
			if(empty($templatesData)){
				$result['status'] = 1;
				$result['msg'] = '请前往“打印模板”界面下载"'.$logisticsInfo['logistics_name'].'"物流公司下的模板';
				return $this->ajaxReturn($result);
			}
			$templatesInfo = $templatesData[0];
			$standerTemplateUrl = json_decode($templatesInfo['content'],true)['user_std_template_url'];

			$printerInfo['stockout_id'] = $stockout_id;
			$printerInfo['logistics_id'] = $logistics_id;
			$printerInfo['stander_template_url'] = $standerTemplateUrl;
			$printerInfo['src_order_no'] = $src_order_no;

			$result['data']['print_data'] = $printerInfo;
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
			$result['data']['waybill_print_info'] = $res['data']['success'][$stockout_id];

			// 自定义模板
			$userCustomTemplate = D('Setting/UserData')->field(array('data'))->where(array('user_id'=>$user_id,'type'=>7,'code'=>''))->select();
			if(!empty($userCustomTemplate)){
				$userCustomTemplate = json_decode($userCustomTemplate[0]['data'],true);
				if(!empty($userCustomTemplate[$logistics_id])){
					$templateId = $userCustomTemplate[$logistics_id];
					$customTemplateUrl = D('Setting/PrintTemplate')->where(array("rec_id"=>$templateId))->select();
					$templateContent = $customTemplateUrl[0]['content'];
					$result['data']['custom_template_url'] = json_decode($templateContent,true)['custom_area_url'];
					$logisticsPrinter = json_decode($templateContent,true)['default_printer'];
					if(!empty($logisticsPrinter)){
						$result['data']['print_data']['logistic_printer'] = $logisticsPrinter;
					}
				}else{
					$result['data']['custom_template_url'] = '';
				}
			}
			// 自定义区数据
			$goods = D('Stock/StockOutOrder')->getStockoutOrderDetailPrintData($stockout_id);
			foreach($goods as $v){
				if(!isset($no[$v['id']]))
					$no[$v['id']] = 0;
				$v['no'] = ++$no[$v['id']];
				$detail[$v['id']][] = $v;
				D('Stock/StockSalesPrint','Controller')->judgeConditions($detail[$v['id']],$v);
			}
			$goods = $detail;
			D('Stock/StockSalesPrint','Controller')->composeSuiteData($goods);
			$result['data']['goods'] = $goods;
			$search = ['src_order_no'=>$stockoutInfo[0]['src_order_no']];
            $stockoutOrderData = D('Stock/StockOutOrder')->searchStockoutList(1, 20, $search, 'id', 'desc', 'stockoutPrint');
            $row = $stockoutOrderData['rows'][0];
            $result['data']['row'] = $row;
			
	
		}catch(\PDOException $e){
            \Think\Log::write($e->getMessage());
			$result = array('status'=>1,'msg'=>$e->getMessage(),'data'=>array());
        }catch(BusinessLogicException $e){
            \Think\Log::write($e->getMessage());
            $result = array('status'=>1,'msg'=>$e->getMessage(),'data'=>array());
        }catch(\Exception $e){
            \Think\Log::write($e->getMessage());
            $result = array('status'=>1,'msg'=>self::PDO_ERROR,'data'=>array());
        }
		return $this->ajaxReturn($result);
	}
	public function mandatory_print($id){
		try{
			$id = intval($id);
			$set_data = $this->getUserData(7,'stalls_base_set');
			if((int)$set_data['stalls_mode'] == 0){
				SE('该功能只支持档口模式!');
			}
			$result = array('status'=>0,'msg'=>'','data'=>array());
			$box_no = M('sorting_wall_detail')->field('box_no')->where(array('box_id'=>$id))->find();
		    $order_info = M('box_goods_detail')->alias('bgd')->fetchSql(false)->field('sum(bgd.num) num,bgd.trade_id,bgd.trade_no')->where(array('bgd.box_no'=>$box_no['box_no'],'bgd.sort_status'=>0))->select();
		   if(empty($order_info) || (int)$order_info[0]['num'] == 0){
			   SE('分拣框尚未分拣货品');
		   }
		   $pick_goods_info = M('stalls_less_goods_detail')->field('unique_code')->where(array('trade_id'=>$order_info[0]['trade_id'],'sort_status'=>0))->select();
		   if(empty($pick_goods_info)){
			   SE('没有要分拣的档口货品');
		   }
		   $sortData = array();
		   $model = D('Purchase/StallsOrderDetail');
		   foreach($pick_goods_info as $v){
				$sortData = $model->pickListStallsOrder($v['unique_code']);
		   }
			if($sortData['status'] ==2){
					$result['status'] = $sortData['status'];
					$result['msg'] = $sortData['msg'];
			}
			
		   /* if($sortData['data']['status'] == 0){
			   $sortData = $this->mandatoryPickNotStalls($sortData['data']['sort_data']['trade_id']);
		   } */
		   
            $sortData['data']['print_data']['order_type'] = $orderType;
            $printDataInfo = $sortData['data']['print_data'];
            $user_id = get_operator_id();
            $stockout_id = $printDataInfo['stockout_id'];
            $src_order_no = $printDataInfo['src_order_no'];
            $logistics_id = $printDataInfo['logistics_id'];
            $standerTemplateUrl = $printDataInfo['stander_template_url'];

            //一单多货完成分拣时才打印物流单。
            $sortData['data']['waybill_print_info'] = -1;
            $sortData['data']['custom_template_url'] = '';
			
			$trade_no=$sortData['data']['sort_data']['trade_no'];
            $sql="SELECT SUM(IF(slgd.sort_status>0,1,0)) as sorted_num FROM stalls_less_goods_detail WHERE trade_no='".$trade_no."'";

            //查找订单对应的货品信息
            $sql="SELECT sto.spec_id,sto.goods_name,sto.spec_name,sto.spec_no,sto.goods_no,sto.actual_num,sto.order_price,sto.share_amount,sto.share_post,sto.paid,sto.weight,st.goods_count,so.box_no
                FROM sales_trade_order sto
                LEFT JOIN sales_trade st ON st.trade_id=sto.trade_id
                LEFT JOIN stockout_order so ON so.src_order_id=st.trade_id
                WHERE st.trade_no='".$trade_no."'";
            $goods_list=M()->query($sql);
			$sorted_num=0;$unsorted_num=0;
            foreach($goods_list as $k=>$v){
                 $spec_id_num =  M('box_goods_detail')->field('num')->where(array('spec_id'=>array('eq',$v['spec_id']),'trade_no'=>array('eq',$trade_no),))->find();
                $goods_list[$k]['sorted_num'] = number_format($spec_id_num['num'],4);
                $sorted_num+=$goods_list[$k]['sorted_num'];
            }
			$sortData['data']['goods_list']=json_encode($goods_list);
            //打印物流单
		    if($standerTemplateUrl != '' && isset($set_data['print_logistics']) && $set_data['print_logistics'] == 1){
                $res = D('Stock/WayBill','Controller')->newGetWayBill($stockout_id,$logistics_id,$standerTemplateUrl,false);
                if($res['status'] !=0){//res 返回三种状态：0->单号获取成功 1->获取单号前校验失败 2->接口调用成功，但是未返回单号
                    if(count($res['data']['fail']) > 0){
                        $result['msg'] = $res['data']['fail'][0]['msg'];
                    }else{
                        $result['msg'] = $res['msg'];
                    }
                    $result['status'] = 1;
					$result['data']['goods_list'] = $sortData['data']['goods_list'];
					$result['data']['sort_data'] = $sortData['data']['sort_data']['sort_finish'];
					
                    return $this->ajaxReturn($result);
                }
                $sortData['data']['waybill_print_info'] = $res['data']['success'][$stockout_id];

                // 自定义模板
                $userCustomTemplate = D('Setting/UserData')->field(array('data'))->where(array('user_id'=>$user_id,'type'=>7,'code'=>''))->select();
                if(!empty($userCustomTemplate)){
                    $userCustomTemplate = json_decode($userCustomTemplate[0]['data'],true);
                    if(!empty($userCustomTemplate[$logistics_id])){
                        $templateId = $userCustomTemplate[$logistics_id];
                        $customTemplateUrl = D('Setting/PrintTemplate')->where(array("rec_id"=>$templateId))->select();
                        $templateContent = $customTemplateUrl[0]['content'];
                        $sortData['data']['custom_template_url'] = json_decode($templateContent,true)['custom_area_url'];
                        $logisticsPrinter = json_decode($templateContent,true)['default_printer'];
                        if(!empty($logisticsPrinter)){
                            $sortData['data']['print_data']['logistic_printer'] = $logisticsPrinter;
                        }
                    }else{
                        $sortData['data']['custom_template_url'] = '';
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
                $sortData['data']['goods'] = $goods;

            }
		    // 发货
			//$setting_config = get_config_value(array('stockout_sort_auto_consign'));
			if($set_data['stockout_sort_auto_consign'] ==1 && $sortData['data']['status'] == 3){
				$stockoutOrderRes = $this->consignStockoutOrder($stockout_id);
				if($stockoutOrderRes['status'] !=0){
					if($stockoutOrderRes['status'] == 1){
						$result['msg'] = $stockoutOrderRes['msg'];
					}else{
						$result['msg'] = $stockoutOrderRes['data']['fail'][0]['msg'];
					}
					$result['status'] = 1;
					$result['data']['sort_data'] = $sortData['data']['sort_data']['sort_finish'];
					$result['data']['goods_list'] = $sortData['data']['goods_list'];
					return $this->ajaxReturn($result);
				}
			}

            $search = ['src_order_no'=>$src_order_no];
            $stockoutOrderData = D('StockOutOrder')->searchStockoutList(1, 20, $search, 'id', 'desc', 'stockoutPrint');
            $row = $stockoutOrderData['rows'][0];
            $sortData['data']['row'] = $row;

            //考虑到动态分配分拣框的情况，box_no从订单中去获取
            $box_no=$goods_list[0]['box_no'];
            $box_id=0;
            if($box_no!=''){//找到id
                $box_id=M('sorting_wall_detail')->field('box_id')->where(array('box_no'=>$box_no))->find();
            }
            $sortData['data']['sort_data']['box_id']=$box_id['box_id'];

            
			$sorted_num = $sorted_num?$sorted_num:1;
            $unsorted_num=$goods_list[0]['goods_count']-$sorted_num;
            $sortData['data']['row']['trade_goods_count']=$goods_list[0]['goods_count'];
            $sortData['data']['row']['sorted_num']=$sorted_num;
            $sortData['data']['row']['unsorted_num']=$unsorted_num;
            $sortData['data']['stalls_base_set'] = $set_data;
            $result['data'] = $sortData['data'];
			
		}catch(\PDOException $e){
            \Think\Log::write($e->getMessage());
			$result = array('status'=>1,'msg'=>$e->getMessage(),'data'=>array());
        }catch(BusinessLogicException $e){
            \Think\Log::write($e->getMessage());    
			$result = array('status'=>1,'msg'=>$e->getMessage(),'data'=>array());
        }
		return  $this->ajaxReturn($result);
	}
	public function mandatoryPickNotStalls($trade_id){
		try{
			if(empty($trade_id)){
				SE('订单号为空');
			}
			$box_goods_detail_model = M('box_goods_detail');
			$trade_info = M('sales_trade')->alias('st')->field('sum(sto.actual_num) as num,sto.spec_id,gs.barcode,st.trade_no,st.revert_reason,gs.spec_name,gs.spec_no')->join('left join sales_trade_order sto on sto.trade_id = st.trade_id')->join('left join goods_spec gs on gs.spec_id = sto.spec_id')->where(array('st.trade_id'=>$trade_id))->group('sto.spec_id')->select();
			if(empty($trade_info)){
				SE('订单详情为空');
			}
			if($trade_info[0]['revert_reason'] != 0){
				SE('货品对应的订单已被驳回');
			}
			$trade_no = $trade_info[0]['trade_no'];
			$box_goods_detail = $box_goods_detail_model->field('sum(num) as num,spec_id,box_no')->where(array('trade_id'=>$trade_id,'sort_status'=>0))->group('spec_id')->select();
			if(empty($box_goods_detail)){
				SE('订单已分拣完成');
			}
			$box_no = $box_goods_detail[0]['box_no'];
			$pick_info = array();
			foreach($trade_info as $v){
				$is_exist = 0;
				foreach($box_goods_detail as $val){
					if($v['spec_id'] == $val['spec_id']){
						if((int)$v['num'] > (int)$val['num']){
							$pick_info[] = array('spec_id'=>$v['spec_id'],'num'=>(int)$v['num'] - (int)$val['num']);
						}
						$is_exist = 1;
						break;
					}
				}
				if($is_exist == 0){
					$pick_info[] = array('spec_id'=>$v['spec_id'],'num'=>(int)$v['num']);
				}
			}
			if(empty($pick_info)){
				SE('没有需要分拣的非档口货品');
			}
			foreach($pick_info as $info){
				D('Purchase/StallsOrderDetail')->putBox($box_no,$trade_no,$trade_id,$info['spec_id'],$box_goods_detail_model,$info['num']);
			}
			$sortData = array();
			$sortData['trade_no'] = $trade_no;
			$pick_status = 0;
			$sorting_wall_detail_model = M('sorting_wall_detail');
			D('Purchase/StallsOrderDetail')->checkPick(1,$trade_no,$box_no,$trade_id,$box_goods_detail_model,$sorting_wall_detail_model,$sortData,$pick_status);
			
			$user_id = get_operator_id();
			$base_set = D('Setting/UserData')->fetchSql(false)->field(array('code,data'))->where(array('user_id'=>$user_id,'type'=>7,'code'=>'stalls_base_set'))->select();
			$base_set_data = json_decode($base_set[0]['data'],true);
			if(empty($base_set_data) || empty($base_set_data['stalls_print_logistics'])){
				$cfg_print_logistics = 0;
			}else{
				$cfg_print_logistics = 1;
			}
			if($pick_status == 1 && $cfg_print_logistics == 0){
				// 打印信息
                $printData = D('Purchase/StallsOrderDetail')->getLogisticsAndTagPrintData($trade_no);
                if($printData['status'] ==1){  // status = 2,非电子面单，不抛异常，下面的流程正常执行。
                    SE($printData['msg']);
                }else if(($printData['status'] ==2)){
                    $result['status'] = 2;
                    $result['msg'] = $printData['msg'];
                }
			}else{
				$printData = D('Purchase/StallsOrderDetail')->getTagPrintData();
			}
			
            $stockout_order = D('Stock/StockOutOrder')->where(array('src_order_no'=>$trade_no))->find();
            $printData['data']['stockout_id'] = $stockout_order['stockout_id'];
			if($printData['status'] !=0){
				SE($printData['msg']);
			}
            $goodsData = array('goods_name'=>$trade_info[0]['goods_name'],'spec_no'=>$trade_info[0]['spec_no']);
            $result['data'] = ["status"=>$pick_status,"print_data"=>$printData['data'],"sort_data"=>$sortData,"goods_data"=>$goodsData];
			
		}catch(\PDOException $e){
            \Think\Log::write($e->getMessage());
			SE(self::PDO_ERROR);
        }catch(BusinessLogicException $e){
            \Think\Log::write($e->getMessage());   		
			SE($e->getMessage());
        }catch(\Exception $e){
            \Think\Log::write($e->getMessage());
			SE(self::PDO_ERROR);
        }
		return $result;
	}
}