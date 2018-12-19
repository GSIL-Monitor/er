<?php

namespace Trade\Controller;

use Common\Controller\BaseController;
use Common\Common\Factory;
use Common\Common\UtilDB;
use Think\Exception\BusinessLogicException;
use Common\Common\ExcelTool;
use Common\Common\UtilTool;
use Think\Log;


class TradeManualController extends BaseController
{
	public function addTrade()
	{
		$user_id=get_operator_id();
		if(IS_POST)
		{
			$arr_form_data=I('post.info','',C('JSON_FILTER'));
			$arr_orders_data=I('post.orders','',C('JSON_FILTER'));
			$arr_form_data['goods_type_count']=count($arr_orders_data);
			unset($arr_form_data['execute_price']);//去除--执行价格
			$customer_id=intval($arr_form_data['customer_id']);
			$trade_db=D('Trade');
			try {
				/*if ($arr_form_data['receivable_amount']!=$arr_form_data['receivable'])
				{
					E('应收金额必须等于实收金额');
				}*/
				unset($arr_form_data['receivable_amount']);//去除--修改前的应收款
				$trade_db->validateTrade($arr_form_data);
				/*if(!$trade_db->validate($trade_db->getRules())->create($arr_form_data))
				{
					$this->error($trade_db->getError());
				}*/
				unset($trade_db);
				if(empty($arr_form_data['receiver_mobile'])&&empty($arr_form_data['receiver_telno']))
				{
					$this->error('手机和电话号码至少需要一个');
				}
				if(empty($arr_form_data['buyer_nick'])){//用户没有填写网名的话生成一个
					!empty($arr_form_data['receiver_mobile'])?$arr_form_data['buyer_nick']='MOB'.$arr_form_data['receiver_mobile']:$arr_form_data['buyer_nick']='TEL'.$arr_form_data['receiver_telno'];
				}
				if($customer_id==0&&(D('Customer/CustomerFile')->checkCustomer($arr_form_data['buyer_nick'],'nickname')))
				{
					$this->error('该网名已存在！');
				}
				$arr_form_data['customer_id']=$customer_id;
				D('SalesTrade')->manualTrade($arr_form_data,$arr_orders_data,$user_id);				
			}catch (\Think\Exception $e){
				$this->error($e->getMessage());
			}catch (BusinessLogicException $e){
				$this->error($e->getMessage());
			}
			$this->success('保存成功');
		}else
		{
			try {
				$id_list=array(
						'id_datagrid'=>strtolower(CONTROLLER_NAME.'_'.ACTION_NAME.'_datagrid'),
						'toolbar'=>'trade_add_toolbar',
						'form_id'=>'trade_add_form',
						'edit'=>'trade_add_edit',
						"fileForm"      => 'trade_add_fileform',
                		"fileDialog"    => 'trade_add_dialog'
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
								'frozenColumns'=>D('Setting/UserData')->getDatagridField('Trade/TradeManual','trade_manual',1),
								'singleSelect'=>false,
								'ctrlSelect'=>true,
						),
						'fields' => D('Setting/UserData')->getDatagridField('Trade/TradeManual', 'trade_manual'),
				);
				$list_form=UtilDB::getCfgRightList(array('shop','logistics','warehouse','employee','unit'),array('warehouse'=>array('is_disabled'=>array('eq',0)),'logistics'=>array('is_disabled'=>array('eq',0)),'shop'=>array('is_disabled'=>array('eq',0))));
				$cod_logistics=UtilDB::getCfgList(array('logistics'),array('logistics'=>array('is_disabled'=>array('eq',0),'is_support_cod'=>array('eq',1))));
				$logistics['cod_logistics']=$cod_logistics['logistics'];
				$logistics['logistics']=$list_form['logistics'];
				for ($i=0;$i<count($list_form['employee']);$i++)
				{
					if ($user_id==$list_form['employee'][$i]['id'])
					{
						$list_form['employee'][$i]['selected']=true;
						break;
					}
				}
				$list_form['flags']=D('Setting/Flag')->getFlagData(1,'list');
				foreach ($list_form['unit'] as $arr)
				{
					$list_form['unit'][$arr['id']]=$arr['name'];
				}
				$list_form['unit'][0]='无';
				$list_form['unit']=json_encode($list_form['unit']);
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
				$this->assign('cfg_val_text',$cfg_val_text);
				$this->assign('cfg_val',json_encode($res_cfg_val));
				$this->assign('list',$list_form);
				$this->assign('logistics',json_encode($logistics));
				$faq_url=C('faq_url');
				$this->assign('faq_url',$faq_url['manual_question']);
				$this->assign('id_list',$id_list);
				$this->assign('datagrid',$datagrid);
			} catch (BusinessLogicException $e) {
				\Think\Log::write($e->getMessage());
			}
			$this->display('show');
		}
	}
	//页面刷新重置
	public function refresh(){
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
		$res_cfg_val['text'] = $limit_price_type[$res_cfg_val['real_price_limit_value']]['name'];
		$res_cfg_val['real_price_limit_value'] = $limit_price_type[$res_cfg_val['real_price_limit_value']]['id'];
		$this->ajaxReturn($res_cfg_val,'JSON');
	}

	// 下载导入模板
	public function downloadTemplet(){
        $file_name = "手工建单导入货品模板.xls";
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
    // 导入货品信息
     public function importGoods() {
		 if(!self::ALLOW_EXPORT){
			 $res["status"] = 1;
			 $res["info"]   = self::EXPORT_MSG;
			 $this->ajaxReturn(json_encode($res), "EVAL");
		 }
        //获取Excel表格相关的数据
      	$file = $_FILES["file"]["tmp_name"];
        $name = $_FILES["file"]["name"];
      	//读取表格数据
      	 try {      	 	
            $excelData = UtilTool::Excel2Arr($name, $file, "TradeManualImport");         
        } catch (\Exception $e) {
            $res = array("status" => 1, "info" => $e->getMessage());
            $this->ajaxReturn(json_encode($res), "EVAL");
            \Think\Log::write($e->getMessage());    
        }
        $err_msg = array(); // 记录插入数据的错误信息
        $goodsInfo = array();// 记录导入货品信息
        $goods = array();//记录详细的货品信息
        $noGoods = array();//记录仓库中没有的货品
        $import_spec_no = array();//记录导入货品的商家编码
        $spec_no=''; $suite_no='';
        foreach ($excelData as $sheet) { 
        	for ($k = 1; $k < count($sheet); $k++) {
        		 $row = $sheet[$k];
        		 if (UtilTool::checkArrValue($row)) continue;
        		 // 获取一条商品信息
        		 $i 				   		 = 0;        		 
        		 $data[$k]["spec_no"]        = trim($row[$i++]);//商家编码
	             $data[$k]["is_suite"]       = trim($row[$i++]);//是否为组合装
	             $data[$k]["is_gift"]        = trim($row[$i++]);//是否为赠品，赠品类型
	             $data[$k]["num"]            = trim($row[$i++]);//数量
	             $data[$k]["real_price"]     = trim($row[$i++]);//折后价   
	             $data[$k]["cs_remark"]      = $row[$i++];//备注	            
	        }
        }
        foreach ($data as $v) {        	
        	if (in_array($v["spec_no"],$import_spec_no)) {
        		$res["status"] = 1;$res["info"]   = "存在重复商家编码,请修改后重新导入";
        		$this->ajaxReturn(json_encode($res), "EVAL");
        	}
        	if ($v["num"]=="") {$v["num"]=1;}
            $goodsInfo[$v["spec_no"]] = empty($value) ? $v : $v[$value];
            $import_spec_no[] = $v["spec_no"];
            $v['is_suite']=='否'?$spec_no.='"'.$v["spec_no"].'",':$suite_no.='"'.$v['spec_no'].'",';
        }        
        $spec_no=substr($spec_no,0,strlen($spec_no)-1);
        $suite_no=substr($suite_no,0,strlen($suite_no)-1);
        try{
	        if($spec_no){
	        	$available_str  = D('Stock/StockSpec')->getAvailableStrBySetting($sys_available_stock);
	        	$point_number   = get_config_value('point_number',0);
	        	$orderable_num  = "CAST(IFNULL(".$available_str.",0) AS DECIMAL(19,".$point_number.")) orderable_num";
	        	$sql    = "SELECT gs.spec_id as id,gs.spec_id,gs.spec_name,gs.spec_no,gs.market_price,gs.spec_code,gs.is_allow_neg_stock,gs.barcode,gs.lowest_price,gs.wholesale_price,gs.tax_rate,gs.barcode,gs.retail_price,gs.weight,gs.unit as base_unit_id,
	                            gg.short_name,gg.spec_count,gg.goods_no,gg.goods_name,gg.goods_id,gg.brand_id,
	                            ss.stock_num,ss.lock_num,ss.subscribe_num,ss.order_num,ss.sending_num,ss.purchase_arrive_num,ss.transfer_num,ss.unpay_num,".$orderable_num.",ss.status,
	        	                            gb.brand_name,
	        	                            cgu.name as unit_name
	        	                            FROM goods_spec gs
	        	                            LEFT JOIN goods_goods gg ON(gs.goods_id=gg.goods_id)
	        	                            LEFT JOIN goods_brand gb ON(gg.brand_id=gb.brand_id)
	        	                            LEFT JOIN stock_spec ss ON(gs.spec_id=ss.spec_id)
	        	                            LEFT JOIN cfg_goods_unit cgu ON(gs.unit = cgu.rec_id)
	        	                            WHERE gs.spec_no IN (".$spec_no.")";
	        	$result = M("GoodsSpec")->query($sql);
	        }
	        if($suite_no){
	        	$sql_result = "SELECT DISTINCT gs.suite_id FROM goods_suite gs WHERE gs.deleted=0 AND gs.suite_no IN (".$suite_no.")";
	        			//再构造SQL查询完整的信息
	        	$sql           = "SELECT gs_1.suite_id AS id,gs_1.suite_name,gs_1.suite_no,gs_1.barcode,gs_1.retail_price,
	                    gs_1.market_price,gb.brand_name,gc.class_name,gs_1.weight,gs_1.remark
	                    FROM goods_suite gs_1
	                    INNER JOIN( " . $sql_result . " )gs_2 ON(gs_1.suite_id=gs_2.suite_id)
	        	                    LEFT JOIN goods_brand gb ON(gs_1.brand_id=gb.brand_id)
	        	                    LEFT JOIN goods_class gc ON(gs_1.class_id=gc.class_id) ";
	        	$suite_result = M("GoodsSpec")->query($sql);
	        }
        }catch (\PDOException $e) {		
         	\Think\Log::write($e->getMessage()); 
	    } catch (\Exception $e) {
	        $res = array("status" => 1, "info" => '未知错误，请联系管理员');
	        \Think\Log::write($e->getMessage());
	    }
	    $result 		= UtilTool::array2dict($result,"spec_no","");
	    $suite_result 	= UtilTool::array2dict($suite_result,"suite_no","");	    
	   	foreach ($goodsInfo as $k => $v) {
	   		if ($result[$k]) {
	    		$id = intval($result[$k]["id"]);
	    		$goods[$id]=array_merge($result[$k],$goodsInfo[$k]);
				// 判断是否允许负库存出库,现有库存为负数时不显示这条数据
             	if ($goods[$id]["is_allow_neg_stock"]==0&&$goods[$id]["stock_num"]<0) {
             		$err_msg[] = array("result" => "失败", "message" => "商家编码:{$v['spec_no']},系统中该商品库存不足"); unset($goods[$id]);
             	}
			}	
			if ($suite_result[$k]) {
	    		$id = intval($suite_result[$k]["id"]);
				$goods[$id]=array_merge($suite_result[$k],$goodsInfo[$k]);
				$goods[$id]["id"]            = $suite_result[$k]["id"];;//商家编码
             	$goods[$id]["goods_name"]    = $suite_result[$k]["suite_name"];//货品名称
             	$goods[$id]["weight"]        = $suite_result[$k]["weight"];//重量
             	$goods[$id]["unit_name"]     = "无";// 单位
             	$goods[$id]["orderable_num"] = 0;// 可订购量 
	    	}
	 	}
	 	// 判断是否存在该商品
	    unset($goods[0]);
	    $noGoods=$result+$suite_result;$noGoods = array_diff_key($goodsInfo,$noGoods);
	   	foreach ($noGoods as $k => $v) {
	   		if ($v["spec_no"]=="") {	             	
	            $err_msg[] = array("result" => "失败", "message" => "导入的商家编码不能为空");
	        }else if($v["is_suite"]==""){
	        	$err_msg[] = array("result" => "失败", "message" => "商家编码:{$v['spec_no']},请设置是否为组合装");
	        }else{
	   			$err_msg[] = array("result" => "失败", "message" => "商家编码:{$v['spec_no']},系统中该商品不存在"); 
	   		}
	   	}
        $k=0;foreach($goods as $value){ $newGoods[$k] = $value;$k++; }
   		if (count($data) == 0) {
            $res["status"] = 1;$res["info"]   = "没有任何数据被导入";
        }elseif(count($err_msg) > 0){
        	$res["status"] = 2;$res["info"]   = $err_msg;
        	$res['data']   = $newGoods;
        }else {
            $res["status"] = 0;$res["info"]   = "操作成功";
            $res['data']   = $newGoods;
        }
        $this->ajaxReturn(json_encode($res), "EVAL");
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
    			'datagrid'=>'trade_manual_scan_datagrid',
    			'tool_bar'=>'trade_manual_scan_toolbar'
    	);
    	$fields = get_field('Trade/TradeManual','choose_goods_list');
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
}