<?php
// 现款销售
namespace Trade\Controller;
use Common\Controller\BaseController;
use Common\Common\Factory;
use Common\Common\UtilDB;
use Think\Exception\BusinessLogicException;
use Think\Log;
use Common\Common\DatagridExtention;
class CashSalesTradeController extends BaseController {
    public function getCashSalesTradeList(){
        $user_id = get_operator_id();
        if(IS_POST){
            $arr_form_data=I('post.info','',C('JSON_FILTER'));//表单提交数据
            $arr_orders_data=I('post.orders','',C('JSON_FILTER'));
            $arr_form_data['goods_type_count']=count($arr_orders_data);
            if($arr_form_data['give_paid']<0){$this->error('找零金额不能为负！');}
            if($arr_form_data['receivable']<($arr_form_data['discount']-$arr_form_data['remission'])){$this->error('优惠金额不能大于合计应收');}
			unset($arr_form_data['execute_price']);
			unset($arr_form_data['give_paid']);
			unset($arr_form_data['ck']);//去除--未找到原因 ？？？
            $customer_id=intval($arr_form_data['customer_id']);
            try {
                // 如果有客户信息，进行验证
                if($arr_form_data['flag']){
                    if(empty($arr_form_data['receiver_mobile'])&&empty($arr_form_data['receiver_telno']))
                    {
                        $this->error('手机和电话号码至少需要一个');
                    }
                    if(empty($arr_form_data['buyer_nick'])){//用户没有填写网名的话生成一个
                        !empty($arr_form_data['receiver_mobile'])?$arr_form_data['buyer_nick']='MOB'.$arr_form_data['receiver_mobile']:$arr_form_data['buyer_nick']='TEL'.$arr_form_data['receiver_telno'];
                        $is_buyer_nick=1;//用户填写手机号
                    }
                    // $customer_id==0没有客户
                    if($customer_id==0&&(D('Customer/CustomerFile')->checkCustomer($arr_form_data['buyer_nick'],'nickname'))){
                        if($is_buyer_nick==1){
                            $this->error('该手机号已存在！');                        
                        }
                        $this->error('该网名已存在！');
                    }
                }
               
                $arr_form_data['customer_id']=$customer_id;
                D('CashSalesTrade')->addCashSalesTrade($arr_form_data,$arr_orders_data,$user_id);
            }catch (\Think\Exception $e){
                $this->error($e->getMessage());
            }catch (BusinessLogicException $e){
                $this->error($e->getMessage());
            }
            $this->success('保存成功');
        }else{
            $id_list=array(                
                'id_datagrid'=>strtolower(CONTROLLER_NAME.'_'.ACTION_NAME.'_datagrid'),
                'toolbar'=>'cash_trade_add_toolbar',
                'form_id'=>'cash_trade_add_form',
                'edit'=>'cash_trade_add_edit',
                "fileForm"      => 'cash_trade_add_fileform',
                "fileDialog"    => 'cash_trade_add_dialog',
                'dialog'    => 'cash_trade_dialog',
                'print_dialog'=>'cash_print_dialog'
            );
            $datagrid=array(
                'id'=>$id_list['id_datagrid'],
                'style'=>'',
                'class'=>'easyui-datagrid',
                'options'=> array(
                        'title'=>'',
                        'toolbar' =>"#{$id_list['toolbar']}",
                        'pagination'=>false,
                        'fitColumns'=>false,
                        'methods'=>'onEndEdit:endEditNewTrade,onBeginEdit:beginEditNewTrade',
                        'frozenColumns'=>D('Setting/UserData')->getDatagridField('Trade/CashSalesTrade','trade_manual',1),
                        'singleSelect'=>false,
                        'ctrlSelect'=>true,
                ),
                'fields' => D('Setting/UserData')->getDatagridField('Trade/CashSalesTrade', 'trade_manual'),
            );
            $list_form=UtilDB::getCfgRightList(array('shop','warehouse','employee'),array('warehouse'=>array('is_disabled'=>array('eq',0)),'shop'=>array('is_disabled'=>array('eq',0))));
            for ($i=0;$i<count($list_form['employee']);$i++)
            {
                if ($user_id==$list_form['employee'][$i]['id'])
                {
                    $list_form['employee'][$i]['selected']=true;
                    break;
                }
            }
            $cfg_arr = array(
                'order_limit_real_price',//是否限制手工建单商品价格的修改--0
                'real_price_limit_value',//手工建单时商品价格修改限制值--0
            );
            $res_cfg_val = get_config_value($cfg_arr,array(0,0));
            $limit_price_type = array(
                '0'=>array('id'=>'lowest_price','name'=>'最低价'),
                '1'=>array('id'=>'retail_price','name'=>'零售价'),
                '2'=>array('id'=>'market_price','name'=>'市场价')
                );
            $cfg_val_text = $limit_price_type[$res_cfg_val['real_price_limit_value']]['name'];
            $res_cfg_val['real_price_limit_value'] = $limit_price_type[$res_cfg_val['real_price_limit_value']]['id'];
            // 获取打印模板
            $result = D('Setting/PrintTemplate')->field('rec_id as id,title as name,content')->where(array('type'=>array('in',array(5,6,9)),'title'=>array('LIKE','条码打印_%')))->order('is_default desc')->select();
            foreach($result as $key){
                $contents[$key['id']] = $key['content'];
            }
            $params=array(
                'datagrid'=>array(
                    'id' => $id_list['id_datagrid']
                )
            );
            $where=array('user_id'=>$user_id,'type'=>10,'code'=>array('like','cash_trade_return%'));
            $setting_data = D('Setting/UserData')->field('code,data')->where($where)->select();
            foreach($setting_data as $v){
                $setting[$v['code']] = $v['data'];
            }
            if(empty($setting)){
                $setting['cash_trade_return_ticket_checked']=1;
                $setting['cash_trade_return_is_first_login']=1;
                $data=$setting;
                $data['cash_trade_return_is_first_login']=0;
                foreach ($data as $k => $v) {
                    $add=array('user_id'=>$user_id,'type'=>10,'code'=>$k,'data'=>$v);
                    $result = D('Setting/UserData')->addUserData($add);
                }
            }
            $this->assign('setting',json_encode($setting));
            $this->assign('contents',json_encode($contents));
            $this->assign('goods_template',$result);
            $this->assign('cfg_val',json_encode($res_cfg_val)); 
            $this->assign('params', json_encode($params));  
            $this->assign('list_form', $list_form);  
            $this->assign('datagrid', $datagrid);  
            $this->assign("id_list", $id_list);   
            $this->display('show');
        }
    }

    //页面刷新重置
	public function refresh(){
		$cfg_arr = array(
			'order_limit_real_price',//是否限制商品价格的修改--0
			'real_price_limit_value',//商品价格修改限制值--0
		);
        $res_cfg_val = get_config_value($cfg_arr,array(0,0));
		$limit_price_type = array(
			'0'=>array('id'=>'lowest_price','name'=>'最低价'),
			'1'=>array('id'=>'retail_price','name'=>'零售价'),
			'2'=>array('id'=>'market_price','name'=>'市场价')
			);
		$res_cfg_val['text'] = $limit_price_type[$res_cfg_val['real_price_limit_value']]['name'];//最低价
        $res_cfg_val['real_price_limit_value'] = $limit_price_type[$res_cfg_val['real_price_limit_value']]['id'];
		$this->ajaxReturn($res_cfg_val,'JSON');
    }
    
    //扫描条码添加货品
    public function getBarcodeInfo(){
    	try{
    		$result = array('status'=>0,'info'=>'');
    		$barcode_info = I('post.','',C('JSON_FILTER'));
            $barcode = trim($barcode_info['barcode']);
    		$type = trim($barcode_info['type']);
    		$type == 0?$discount=1:$discount=0;
    		$sql_spec="SELECT gs.goods_id,gg.goods_name,gg.goods_no,gs.spec_id,gs.spec_name,gs.spec_no,gs.spec_code,gb.barcode,gs.weight,
    				gs.is_sn_enable,gs.lowest_price,gs.retail_price,gs.wholesale_price,gs.member_price,gs.market_price,gs.tax_rate,1 AS num,
    				gs.unit AS base_unit_id,0 AS is_suite,'".$type."' AS gift_type,'".$discount."' AS discount,gs.spec_id AS id 
    				FROM goods_spec gs 
    				LEFT JOIN goods_barcode gb ON gs.spec_id=gb.target_id 
    				LEFT JOIN goods_goods gg ON gg.goods_id=gs.goods_id 
    				WHERE gb.barcode='".$barcode."' AND gb.type=1";
    		$res_spec=D('GoodsGoods/GoodsSpec')->query($sql_spec);
    		$sql_suite="SELECT gs.suite_id,gs.suite_name AS goods_name,gs.suite_no AS spec_no,gb.barcode,gs.weight,
    				gs.retail_price,gs.wholesale_price,gs.member_price,gs.market_price,1 AS num,1 AS is_suite,'".$type."' AS gift_type,'".$discount."' AS discount,
    				gs.suite_id AS id,gs.suite_no,gs.suite_name 
    				FROM goods_suite gs 
    				LEFT JOIN goods_barcode gb ON gs.suite_id=gb.target_id  
    				WHERE gb.barcode='".$barcode."' AND gb.type=2";
            $res_suite=D('GoodsGoods/GoodsSuite')->query($sql_suite);
			$res=array_merge_recursive($res_spec,$res_suite);
    		if($res == '' || empty($res)){
    			$result = array('status'=>1,'info'=>'没有该条形码');
    		}else{
    			$result = array('status'=>0,'info'=>$res);
    		}
    	}catch(\Exception $e){
    		\Think\Log::write($e->getMessage());
    		$result = array('status'=>1,'info'=>self::UNKNOWN_ERROR);
    	}
    	$this->ajaxReturn($result);
    }
    //同意条形码对应多个货品，弹窗选择货品
    public function showGoodsList($parent_datagrid_id,$parent_object,$goods_list_dialog){
    	$id_list = array(
    			'datagrid'=>'trade_cash_scan_datagrid',
    			'tool_bar'=>'trade_cash_scan_toolbar'
    	);
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
    			'fields' => get_field('Trade/CashSalesTrade','choose_goods_list'),
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

    // 打印小票（界面）
    public function PrintTicket(){
        $user_id = get_operator_id();
		try{
            $dialog_div = 'only_code_print_dialog';
            // $list = UtilDB::getCfgRightList(array('warehouse'));
            $where=array('user_id'=>$user_id,'type'=>10,'code'=>array('like','cash_trade_return%'));
            $setting_data = D('Setting/UserData')->field('code,data')->where($where)->select();
            foreach($setting_data as $v){
                $setting[$v['code']] = $v['data'];
            }
            // 获取打印模板
            $result = D('Setting/PrintTemplate')->field('rec_id as id,title as name,content')->where(array('type'=>array('in',array(5,6,9)),'title'=>array('LIKE','现款销售小票_%')))->order('is_default desc')->select();
            foreach($result as $key){
                $contents[$key['id']] = $key['content'];
            }
            for($i=0,$len=count($result);$i<$len;$i++){
                if($result[$i]['id']==$setting['cash_trade_return_template_list']){
                    $result[$i]['selected']=true;            
                } 
            }
            $this->assign('setting',json_encode($setting));
            $this->assign('contents',json_encode($contents));
            $this->assign('goods_template',$result);
            $this->assign('dialog_div',$dialog_div);
        }catch(\PDOException $e){
            \Think\Log::write($e->getMessage());
        }
		$this->display('print_sicket');
    }
    
    // 更改用户设置
    public function updateSetting(){
        $val = I("post.data");
        try {
            $M = M();
            $operator_id = get_operator_id();
            $user_data_model=D('Setting/UserData');
            $M->startTrans();
            $res["status"] = 1;
            $res["info"] = "操作成功！";
            foreach ($val as $k => $v) {
                $where=array('user_id'=>$operator_id,'type'=>10,'code'=>$k);
                $get_cfg=$user_data_model->where($where)->find();
                if($get_cfg){
                    $save['data']=$v;
                    $result = $user_data_model->updateUserData($save,$where);
                }else{
                    $add=array('user_id'=>$operator_id,'type'=>10,'code'=>$k,'data'=>$v);
                    $result = $user_data_model->addUserData($add);
                }
                if ($result === false) {
                    $res["status"] = 0;
                    $res["info"] = "操作失败,请联系管理员！";
                    break;
                }
            }
            $M->commit();
        } catch (\Exception $e) {
            $M->rollback();
            \Think\Log::write($e->getMessage());
            $res["status"] = 0;
            $res["info"] = "操作失败";
        }
        $this->ajaxReturn($res);
    }
}