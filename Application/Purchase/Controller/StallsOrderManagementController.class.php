<?php
namespace Purchase\Controller;
use Common\Controller\BaseController;
use Think\Exception\BusinessLogicException;
use Common\Common\UtilDB;
use Common\Common\UtilTool;
use Common\Common\ExcelTool;
use Thinl\Model;
use Stock\StockCommonField;
use Platform\Common\ManagerFactory;

class StallsOrderManagementController extends BaseController{
	
	public function show(){
		try{
			$id_list = array();
			$need_ids = array('form','toolbar','tab_container','hidden_flag','datagrid','more_content','edit','hidden_flag','delete','file_form','file_dialog','split','dialog','add');
			$this->getIDList($id_list,$need_ids,'','');
			$datagrid = array(
				'id'=>$id_list['datagrid'],
				'options'=>array(
					'url'=>U('StallsOrderManagement/search'),
					'toolbar'=>$id_list['toolbar'],
					'fitColumns'=>false,
					"rownumbers" => true,
					"pagination" => true,
					"method"     => "post",
				),
				'fields'=>get_field('StallsOrder','stallsordermanagement'),
			);
			$params = array(
				'datagrid'=>array(
					'id'=>$id_list['datagrid'],
				),
				'search'=>array(
					'form_id'=> $id_list['form'],
				),
				'id_list'=>$id_list,
				'tabs'=>array(
					'id'=>$id_list['tab_container'],
					'url'=>U('Purchase/PurchaseCommon/showTabDatagridData'),
				),
				'edit'=>array('id'=>$id_list['edit'],'url'=>U('Purchase/StallsOrderManagement/edit'),'title'=>'档口编辑'),
				'dialog'=>array('url'=>U('Purchase/StallsOrderManagement/split_order')),
				'add'=>array('id'=>$id_list['add'],'url'=>U('Purchase/StallsOrderManagement/addStallsOrder'),'title'=>'生成档口单'),
				
			);
			$arr_tabs = array(
				array('url'=>U('Purchase/PurchaseCommon/showTabsView',array('tabs'=>"stalls_order_detail")).'?prefix=stallsmanagment&tab=stalls_order_detail&app=Purchase/StallsOrder',"id"=>$id_list['tab_container'],"title"=>"档口单详情"),
				array('url'=>U('Purchase/PurchaseCommon/showTabsView',array('tabs'=>"trade_order_detail")).'?prefix=stallsmanagment&tab=trade_order_detail&app=Purchase/StallsOrder',"id"=>$id_list['tab_container'],"title"=>"订单详情"),
				array('url'=>U('Purchase/PurchaseCommon/showTabsView',array('tabs'=>"stalls_order_log")).'?prefix=stallsmanagment&tab=stalls_order_log&app=Purchase/StallsOrder',"id"=>$id_list['tab_container'],"title"=>"日志"),
			);
			$list = UtilDB::getCfgRightList(array('warehouse','employee','provider'));
			foreach($list['provider'] as $k=>$v){if($v['id']==0) unset($list['provider'][$k]);}
//			$provider = D('Setting/PurchaseProvider')->getALlPurchaseProvider(array('id'=>array('neq','0')));
//			$list['provider'] = $provider['data'];
			$warehouse_default['0'] = array('id' => 'all', 'name' => '全部');
			$warehouse_array = array_merge($warehouse_default, $list['warehouse']);
			$employee_default['0'] = array('id' => 'all','name'=>'全部');
			$employee_array = array_merge($employee_default,$list['employee']);
			$provider_default['0'] = array('id' => 'all','name'=>'全部');
			$provider_array = array_merge($provider_default,$list['provider']);
		}catch(\Exception $e){
			\Think\Log::write($e->getMessage());
		}
		$this->assign('warehouse_array',$warehouse_array);
		$this->assign('employee_array',$employee_array);
		$this->assign('provider_array',$provider_array);
		$this->assign('params',json_encode($params));
		$this->assign('arr_tabs',json_encode($arr_tabs));
		$this->assign('datagrid',$datagrid);
		$this->assign('id_list',$id_list);
		$this->display('show');
		
	}
	public function search($page = 1,$row = 20,$search = array(),$sort = 'id',$order = 'desc'){
			try{
				$result = D('StallsOrder')->search($page,$row,$search,$sort,$order);
			}catch(\Exception $e){
				\Think\Log::write($e->getMessage());
				$result = array('rows'=>array(),'total'=>0);
				
			}
			$this->ajaxReturn($result);
	}
	
	public function edit(){
		
		try{
		$edit_info = I('','',C('JSON_FILTER'));
		$id = $edit_info['id'];
		$parent_info = $edit_info['parent_info'];
		$id_list = array();
		$suffix = 'edit';
		$stalls_tool = CONTROLLER_NAME;
		$need_ids = array('form','tool_bar','datagrid');
		$this->getIDList($id_list,$need_ids,$stalls_tool,$suffix);
		$fields = get_field('StallsOrder','stalls_order_edit');

		$list = UtilDB::getCfgRightList(array('warehouse','employee'));
		$employee_default['0'] = array('id' => 'all','name'=>'全部');
		$employee_array = array_merge($employee_default,$list['employee']);

		$data = D('StallsOrder')->where(array('stalls_id'=>$id))->select();
		$purchaser_id = $data[0]['purchaser_id'];

		$datagrid = array(
			"id" => $id_list["datagrid"],
			"options" => array(
                'url'=>U('StallsOrderManagement/getStallsinfo').'?id='.$id,
                "toolbar" => "#{$id_list['tool_bar']}",
				'fitColumns'   => true,
                'singleSelect'=>true,
                'ctrlSelect'=>false,
                "rownumbers" => true,
                'pagination'=>true,
                "method"    => "post",
            ),
			"fields" => $fields,
		);
		$params = array(
			"datagrid" => array('id'=>$id_list["datagrid"]),
			"form" => $id_list["form"],
			"id_list" => $id_list,
			'dialog_id'=>$parent_info['dialog_id'],
			'datagrid_id'=>$parent_info['datagrid_id'],
			"order_id"=>$id,
		);	
		$this->assign('params',json_encode($params));
        $this->assign('datagrid',$datagrid);
        $this->assign('id_list',$id_list);
        $this->assign('employee_array',$employee_array);
        $this->assign('purchaser_id',$purchaser_id);
        $this->display('edit');
		}
			catch(\PDOException $e){
	        \Think\Log::write($e->getMessage());
	    }
	}
	
	public function getStallsinfo($id,$page = 1,$rows = 20){

        try{
//			$result = array('status'=>0,'info'=>'');
            $result = D('StallsOrderDetail')->showStallsDetail($id,$page,$rows);
            $result['status'] = 0;
		}catch(\Exception $e){
			\Think\Log::write($e->getMessage());
			$result = array('status'=>1,'info'=>$e->getMessage(),'page'=>0,'rows'=>0);
		}
		$this->ajaxReturn($result);
		
	}
	public function cancelStallsOrder($id){
		try{
			$data = array('status'=>0,'info'=>'取消成功');
			$data = D('StallsOrderDetail')->cancelStallsOrder($id);
		}catch(\Exception $e){
			\Think\Log::write($e->getMessage());
			$data = array('status'=>1,'info'=>$e->getMessage());
		}
		$this->ajaxReturn($data);
	}
	
	public function saveProvider(){
		try{
            $result = array('status'=>0,'info'=>'成功','data'=>array());
            $params = I("",'',C('JSON_FILTER'));
			$id = $params['order_id'];
            $purchaser_id = $params['purchaser_id'];
            $rows = $params['rows'];
           $data =  D("StallsOrderDetail")->updateProvider($rows,$id,$purchaser_id);
            
        }catch (BusinessLogicException $e){
            $result['status']=1;
            $result['info']=$e->getMessage();
        }catch(\Exception $e){
            \Think\Log::write(CONTROLLER_NAME.'-saveProvider-'.$e->getMessage());
            $result['status']=1;
            $result['info']=self::UNKNOWN_ERROR;
        }
        $this->ajaxReturn(json_encode($result),'EVAL');
    
	}
	public function getStallsLessGoodsDetailList(){
		try{
			$id_list = array();
			$need_ids = array('form','toolbar','datagrid','dialog');
			$this->getIDList($id_list,$need_ids,'StallsDetail','');
			$datagrid = array(
				'id'=>$id_list['datagrid'],
				'options'=>array(
					'url'=>U('StallsOrderManagement/getStallsLessGoodsDetail'),
					'toolbar'=>$id_list['toolbar'],
					'fitColumns'=>false,
					"rownumbers" => true,
					"pagination" => true,
					'singleSelect'=>false,
					'ctrlSelect'=>true,
					"method"     => "post",
					'pageList'=>[20,50,100,200],
				),
				'fields'=>get_field('StallsOrder','stalls_less_goods_detail'),
			);
			$checkbox=array('field' => 'ck','checkbox' => true);
			array_unshift($datagrid['fields'],$checkbox);
			$params = array(
				'datagrid'=>array(
					'id'=>$id_list['datagrid'],
				),
				'search'=>array(
					'form_id'=> $id_list['form'],
				),
				'delete'   	=> array(
					'url' => U('StallsOrderManagement/delStallsLessGoods')
				),
				'id_list'=>$id_list,
			);
			$list = UtilDB::getCfgRightList(array('shop','warehouse','provider'));
			//$provider = D('Setting/PurchaseProvider')->getALlPurchaseProvider();
			//$list['provider'] = $provider['data'];
		}catch(\Exception $e){
			\Think\Log::write($e->getMessage());
		}
		$this->assign('params',json_encode($params));
		$this->assign('datagrid',$datagrid);
		$this->assign('id_list',$id_list);
		$this->assign('list',$list);
		$this->display('stalls_less_goods_detail_list');
	}
	public function getStallsLessGoodsDetail($page = 1,$rows = 20,$search = array(),$sort = 'id',$order = 'desc'){
		try{
			$type = I('get.type');
			$result = D('StallsOrder')->getStallsLessGoodsDetail($page,$rows,$search,$sort,$order,$type);
		}catch(\Exception $e){
			\Think\Log::write($e->getMessage());
			$result = array('rows'=>array(),'total'=>0);
		}
		$this->ajaxReturn($result);
	}
	public function split_order($id){
		try {	
				$point_number=get_config_value('point_number',0);
				$num = 'CAST(sum(sod.num) AS DECIMAL(19,'.$point_number.')) num';
				$left_num = 'CAST(sum(sod.num) AS DECIMAL(19,'.$point_number.')) left_num';
				$in_num = 'CAST(sum(sod.stockin_status) AS DECIMAL(19,'.$point_number.')) in_num';
				$put_num = 'CAST(sum(sod.pickup_status) AS DECIMAL(19,'.$point_number.')) put_num';
				$where = array('sod.stalls_id'=>$id);
				$fields =  array('sod.rec_id as id','pp.provider_name','pp.id as provider_id','gs.spec_no',$in_num,$put_num,$left_num,'0 as split_num','sod.spec_id','gg.goods_no','gg.goods_name','gb.brand_name','gs.spec_code','gs.spec_name','gs.barcode',$num,'sod.price','count(sod.spec_id)*sod.price as amount','sod.remark','cgu.name as unit_name');
				$data = D('StallsOrderDetail')->fetchsql(false)->alias('sod')->field($fields)->join('LEFT JOIN purchase_provider pp ON pp.id = sod.provider_id')->join('LEFT JOIN goods_spec gs ON gs.spec_id = sod.spec_id')->join('LEFT JOIN goods_goods gg ON gg.goods_id = gs.goods_id')->join('LEFT JOIN goods_brand gb ON gg.brand_id = gb.brand_id')->join('left join cfg_goods_unit cgu on cgu.rec_id = gs.unit')->where($where)->group('sod.spec_id')->select();
				$stalls_no = D('StallsOrder')->field('stalls_no')->where(array('stalls_id'=>$id))->find();
				$datagrid['split_main_stalls']=array(
						'id'=>'main_stalls_split_datagrid',
						'style'=>'',
						'class'=>'easyui-datagrid',
						'options'=> array(
								'title'=>'',
								'toolbar' => "#split_main_stalls_datagrid_toolbar",
								'pagination'=>false,
								'fitColumns'=>false,
								'methods'=>'onEndEdit:endSplitStallsEdit,onSelect:getSplitStallsSelect',
						),
						'fields' => D('Setting/UserData')->getDatagridField('StallsOrder','stalls_order_split')
				);
				$datagrid['split_new_stalls']=array(
						'id'=>'new_stalls_split_datagrid',
						'style'=>'',
						'class'=>'easyui-datagrid',
						'options'=> array(
								'title'=>'',
								'toolbar' => "#new_sub_stalls_datagrid_toolbar",
								'pagination'=>false,
								'fitColumns'=>false,
						),
						'fields' => get_field('StallsOrder','stalls_order_new_split')
				);
				$data=array('total'=>count($data),'rows'=>$data);
				$this->assign('split_stalls_order_data',json_encode($data));
				$this->assign('stalls_no',$stalls_no);
				$this->assign('datagrid',$datagrid['split_main_stalls']);
				$this->assign('datagrid2',$datagrid['split_new_stalls']);
				$this->assign('point_number',$point_number);
				$this->display('dialog_stalls_split');
			} catch (BusinessLogicException $e) {
				\Think\Log::write($e->getMessage());
		
			} catch(\Exception $e){
				\Think\Log::write($e->getMessage());
			}
    
	}
	public function splitStalls($id){
		$user_id=get_operator_id();
		try {
			$data = array('status'=>0,'info'=>'拆分成功');
			if(intval($id)==0)
			{
				SE('档口单不存在');
			}
			$arr_data_main=I('post.main_orders','',C('JSON_FILTER'));
			if(empty($arr_data_main))
			{
				SE('未拆分档口单');
			}
			$list=array();
			$stalls_order = D('StallsOrder')->alias('so')->field(array('so.status','so.goods_count'))->where(array('stalls_id'=>$id))->find();
			if(empty($stalls_order)){
				SE('档口单不存在');
			}
			if($stalls_order['status'] != 20){
				SE('档口单状态不正确，只能拆分编辑中的档口单');
			}
			if($stalls_order['goods_count']<2){
				SE('档口单只有一个货品，不可拆分');
			}
			D('StallsOrder')->splitStalls($id,$arr_data_main,$user_id);
		}catch (BusinessLogicException $e){
			\Think\Log::write($e->getMessage());
			$data = array('status'=>1,'info'=>$e->getMessage());
		}catch (\Think\Exception $e){
			\Think\Log::write($e->getMessage());
			$data = array('status'=>1,'info'=>$e->getMessage());
		}
		$this->ajaxReturn($data);
	}
	public function PrintCode($ids){
		try{
		    $isMulti = I('isMulti');
            $fields = 'rec_id as id,title as name,content';
            $model = D('Setting/PrintTemplate');
            $dialog_div = 'code_print_dialog';
            $result = $model->field($fields)->where(array('type'=>array('in',array(5,6,9)),'title'=>array('LIKE','唯一码打印_%')))->order('is_default desc')->select();
            foreach($result as $key){
                $contents[$key['id']] = $key['content'];
            }
            $list = UtilDB::getCfgRightList(array('warehouse'));
            $this->assign('warehouse_list', $list['warehouse']);
            $this->assign('contents',json_encode($contents));
            $this->assign('dialog_div',$dialog_div);
			$this->assign('code','print_code');
			$this->assign('isMulti',$isMulti);
            $this->assign('goods_template',$result);
        }catch(\PDOException $e){
            \Think\Log::write($e->getMessage());
        }
		$this->display('print_code');
	}
	public function printStallsDetail(){
		try{
			$ids = I('get.ids');
			$fields = 'rec_id as id,title as name,content';
			$model = D('Setting/PrintTemplate');
			$dialog_div = 'stalls_detail_print_dialog';
			$result = $model->field($fields)->where(array('type'=>array('in',array(5,6,9)),'title'=>array('LIKE','档口单_%')))->order('is_default desc')->select();
			foreach($result as $key){
				$contents[$key['id']] = $key['content'];
			}
			$ids_arr = explode(',',$ids);
			foreach($ids_arr as $id){
				$stalls_goods = D('StallsOrderDetail')->getStallsDetail($id);
				foreach($stalls_goods as $v){
					if(!isset($no[$id])){$no[$id] = 0;}
					$v['no'] = ++$no[$id];
					$stalls_detail[$id][] = $v;
				}
			}
			$list = UtilDB::getCfgRightList(array('warehouse'));
			$this->assign('warehouse_list', $list['warehouse']);
			$this->assign('contents',json_encode($contents));
			$this->assign('code','print_stalls_detail');
			$this->assign('dialog_div',$dialog_div);
			$this->assign('stalls_detail',json_encode($stalls_detail));
			$this->assign('goods_template',$result);
		}catch(\PDOException $e){
			\Think\Log::write($e->getMessage());
		}
		$this->display('print_stalls_detail');
	}
	public function PrintHotCode($ids){
		try{
            $fields = 'rec_id as id,title as name,content';
            $model = D('Setting/PrintTemplate');
            $dialog_div = 'hot_code_print_dialog';
            $result = $model->field($fields)->where(array('type'=>array('in',array(5,6,9)),'title'=>array('LIKE','爆款码打印_%')))->order('is_default desc')->select();
            foreach($result as $key){
                $contents[$key['id']] = $key['content'];
            }
            $list = UtilDB::getCfgRightList(array('warehouse'));
            $this->assign('warehouse_list', $list['warehouse']);
            $this->assign('contents',json_encode($contents));
			$this->assign('code','print_hot_code');
            $this->assign('dialog_div',$dialog_div);
            $this->assign('goods_template',$result);
        }catch(\PDOException $e){
            \Think\Log::write($e->getMessage());
        }
		$this->display('print_hot_code');
	}
	
	public function getPrintCode($ids,$num){
		try{
			$result = array('status'=>0,'info'=>array());
			$data = D('Purchase/StallsOrderDetail')->alias('sod')->fetchSql(false)
                ->field(array('if(st.goods_count>1,"多件","单件") package_num','concat(datediff(now(),st.pay_time)+1,"天") as days','concat("[",format(st.goods_count,0),"件]") as package_num_int','ppg.provider_group_name','pp.provider_name','sod.unique_code','so.box_no','cw.name as warehouse_name','pp.contact','if(pp.mobile is null,pp.telno,pp.mobile) as mobile','pp.address','pp.province','pp.city','pp.district','gs.spec_no','gs.spec_code','gs.spec_name','gs.barcode','sod.price','gg.goods_no','gg.goods_name','gg.short_name','gb.brand_name','cl.logistics_name','gc.class_name','st.pay_time'))
                ->join('left join sales_trade st on st.trade_id = sod.trade_id')
                ->join('left join purchase_provider pp on sod.provider_id = pp.id')
                ->join('left join goods_spec gs on gs.spec_id = sod.spec_id')
                ->join('left join goods_goods gg on gg.goods_id = gs.goods_id')
                ->join('left join goods_brand gb on gb.brand_id = gg.brand_id')
                ->join('left join goods_class gc on gc.class_id = gg.class_id')
                ->join('left join cfg_warehouse cw on cw.warehouse_id = sod.warehouse_id')
                ->join('left join purchase_provider_group ppg on ppg.id = pp.provider_group_id ')
                ->join('left join stockout_order so on so.src_order_no = sod.trade_no')
				->join('left join cfg_logistics cl on cl.logistics_id = st.logistics_id')
                ->where(array('sod.stalls_id'=>array('in',$ids)))
                ->order('ppg.provider_group_no,convert(pp.provider_name USING gbk),convert(gs.spec_no USING gbk),st.pay_time')->select();
			if(empty($data)){
				SE('未查询到唯一码');
			}			
			$result['info'] = $data; 
			
		}catch (BusinessLogicException $e){
			\Think\Log::write($e->getMessage());
			$result = array('status'=>1,'info'=>$e->getMessage());
		}catch (\Think\Exception $e){
			\Think\Log::write($e->getMessage());
			$result = array('status'=>1,'info'=>$e->getMessage());
		}
		$this->ajaxReturn($result);
	}
	public function setDefaultPrinter($content,$templateId)
    {
        $data['content'] = $content;
        $data['rec_id']  = $templateId;
        $data['type']    = "";
        $data['title']   = "";
        $ret             = array("status" => 0, "msg" => "成功");
        try {
            $res = D('Setting/PrintTemplate')->save($data, 'content');
        } catch (\Exception $e) {
            $ret['status'] = 1;
            $ret['meg']    = self::UNKNOWN_ERROR;
        }
        $this->ajaxReturn($ret);
    }
	public function update_unique_status($ids,$type=''){
		try{
			$result = array('status'=>0,'info'=>array());
			if($type == 'less'){
				M('stalls_less_goods_detail')->where(array('rec_id'=>array('in',$ids)))->save(array('unique_print_status'=>1));
			}elseif($type == 'detail'){
				D('StallsOrder')->where(array('stalls_id'=>array('in',$ids)))->save(array('detail_print_status'=>1));
			}else{
				D('StallsOrder')->where(array('stalls_id'=>array('in',$ids)))->save(array('unique_print_status'=>1));
				M('stalls_less_goods_detail')->where(array('stalls_id'=>array('in',$ids)))->save(array('unique_print_status'=>1));
			}
		}catch (BusinessLogicException $e){
			\Think\Log::write($e->getMessage());
			$result = array('status'=>1,'info'=>$e->getMessage());
		}catch (\Think\Exception $e){
			\Think\Log::write($e->getMessage());
			$result = array('status'=>1,'info'=>$e->getMessage());
		}
		$this->ajaxReturn($result);
	}
	public function update_hot_status($ids,$hot_print_num,$type=''){
		try{
			$result = array('status'=>0,'info'=>array());
			if($type == 'less'){
				M('stalls_less_goods_detail')->where(array('rec_id'=>array('in',$ids)))->save(array('hot_print_status'=>2));
			}else{
				M('stalls_order')->where(array('stalls_id'=>array('in',$ids)))->save(array('hot_print_status'=>2,'hot_print_num'=>$hot_print_num));
				M('stalls_less_goods_detail')->where(array('stalls_id'=>array('in',$ids)))->save(array('hot_print_status'=>2));
			}
		
		}catch(\PDOException $e){
			\Think\Log::write($e->getMessage());
			$result = array('status'=>1,'info'=>self::PDO_ERROR);
		}catch (BusinessLogicException $e){
			\Think\Log::write($e->getMessage());
			$result = array('status'=>1,'info'=>$e->getMessage());
		}catch (\Think\Exception $e){
			\Think\Log::write($e->getMessage());
			$result = array('status'=>1,'info'=>$e->getMessage());
		}
		$this->ajaxReturn($result);
	}
	public function addStallsOrder(){
		try{
			$id_list = array();
			$need_ids = array('form','toolbar','datagrid');
			$this->getIDList($id_list,$need_ids,'addStallsOrder','');
			$datagrid = array(
				'id'=>$id_list['datagrid'],
				'options'=>array(
					'url'=>U('StallsOrderManagement/getStallsLessGoodsDetail').'?type=add',
					'toolbar'=>$id_list['toolbar'],
					'fitColumns'=>false,
					"rownumbers" => true,
					"pagination" => true,
					'singleSelect'=>false,
					'ctrlSelect'=>true,
					"method"     => "post",
				),
				'fields'=>get_field('StallsOrder','stalls_less_goods_detail'),
			);
			$checkbox=array('field' => 'ck','checkbox' => true);
			array_unshift($datagrid['fields'],$checkbox);
			$params = array(
				'datagrid'=>array(
					'id'=>$id_list['datagrid'],
				),
				'search'=>array(
					'form_id'=> $id_list['form'],
				),
				'id_list'=>$id_list,
			);
			$list = UtilDB::getCfgRightList(array('shop','warehouse','provider'));
//			$provider = D('Setting/PurchaseProvider')->getALlPurchaseProvider();
//			$list['provider'] = $provider['data'];
		}catch(\Exception $e){
			\Think\Log::write($e->getMessage());
		}
		$this->assign('params',json_encode($params));
		$this->assign('datagrid',$datagrid);
		$this->assign('id_list',$id_list);
		$this->assign('list',$list);
		$this->display('add');
	}
	public function postAddOrder(){
		try{
			$result = array('status'=>0,'info'=>'保存成功');
			$info = I('','',C('JSON_FILTER'));
			$data = $info['data'];
			$type = $info['type'];
			D('StallsOrder')->postAddOrder($data,$type);
		}catch (BusinessLogicException $e){
			\Think\Log::write($e->getMessage());
			$result = array('status'=>1,'info'=>$e->getMessage());
		}catch (\Think\Exception $e){
			\Think\Log::write($e->getMessage());
			$result = array('status'=>1,'info'=>$e->getMessage());
		}
		$this->ajaxReturn($result);
	}
	public function delStallsLessGoods($id){
		$result=array('status'=>0,'info'=>"删除成功");
		try{
			$sorting_wall_info=D('StallsOrder')->delStallsLessGoods($id);
		}catch(BusinessLogicException $e){
			$result=array('status'=>1,'info'=>$e->getMessage());
		}catch(\Exception $e){
			\Think\Log::write($e->getMessage());
			$result['status']=1;
			$result['info']=$e->getMessage();
		}
		$this->ajaxReturn($result);
	}
	public function PrintOnlyCode(){
		try{
            $isMulti = I('isMulti');
            $fields = 'rec_id as id,title as name,content';
            $model = D('Setting/PrintTemplate');
            $dialog_div = 'only_code_print_dialog';
            $result = $model->field($fields)->where(array('type'=>array('in',array(5,6,9)),'title'=>array('LIKE','唯一码打印_%')))->order('is_default desc')->select();
            foreach($result as $key){
                $contents[$key['id']] = $key['content'];
            }
            $list = UtilDB::getCfgRightList(array('warehouse'));
            $this->assign('warehouse_list', $list['warehouse']);
            $this->assign('contents',json_encode($contents));
            $this->assign('dialog_div',$dialog_div);
            $this->assign('isMulti',$isMulti);
            $this->assign('goods_template',$result);
        }catch(\PDOException $e){
            \Think\Log::write($e->getMessage());
        }
		$this->display('print_onlycode');
	}
	public function getPrintOnlyCode($ids){
		try{
			$result = array('status'=>0,'info'=>array());
			$data = D('StallsOrderDetail')->alias('sod')->fetchsql(false)
                ->field(array('if(st.goods_count>1,"多件","单件") package_num','concat("[",format(st.goods_count,0),"件]") as package_num_int ','concat(datediff(now(),st.pay_time)+1,"天") as days','ppg.provider_group_name','pp.provider_name','sod.unique_code','so.box_no','cw.name as warehouse_name','pp.contact','if(pp.mobile is null,pp.telno,pp.mobile) as mobile','pp.address','pp.province','pp.city','pp.district','gs.spec_no','gs.spec_code','gs.spec_name','gs.barcode','sod.price','gg.goods_no','gg.goods_name','gg.short_name','gb.brand_name','gc.class_name','st.pay_time','cl.logistics_name'))
                ->join('left join sales_trade st on st.trade_id = sod.trade_id')
                ->join('left join purchase_provider pp on sod.provider_id = pp.id')
                ->join('left join goods_spec gs on gs.spec_id = sod.spec_id')
                ->join('left join goods_goods gg on gg.goods_id = gs.goods_id')
                ->join('left join goods_brand gb on gb.brand_id = gg.brand_id')
                ->join('left join goods_class gc on gc.class_id = gg.class_id')
                ->join('left join cfg_warehouse cw on cw.warehouse_id = sod.warehouse_id')
                ->join('left join purchase_provider_group ppg on ppg.id = pp.provider_group_id ')
                ->join('left join stockout_order so on so.src_order_no = sod.trade_no')
				->join('left join cfg_logistics cl on cl.logistics_id = st.logistics_id')
                ->where(array('sod.rec_id'=>array('in',$ids)))
                ->order('ppg.provider_group_no,convert(pp.provider_name USING gbk),convert(gs.spec_no USING gbk),st.pay_time')->select();
			if(empty($data)){
				SE('未查询到唯一码');
			}			
			$result['info'] = $data; 
			
		}catch (BusinessLogicException $e){
			\Think\Log::write($e->getMessage());
			$result = array('status'=>1,'info'=>$e->getMessage());
		}catch (\Think\Exception $e){
			\Think\Log::write($e->getMessage());
			$result = array('status'=>1,'info'=>$e->getMessage());
		}
		$this->ajaxReturn($result);
	}
	public function getPrintSortingGoods($ids){
		try{
			$result = array('status'=>0,'info'=>array());
			$data = D('StallsOrder')->getPrintSortingGoods($ids);
			$result['info']['goods'] = $data; 
			
		}catch (BusinessLogicException $e){
			\Think\Log::write($e->getMessage());
			$result = array('status'=>1,'info'=>$e->getMessage());
		}catch (\Think\Exception $e){
			\Think\Log::write($e->getMessage());
			$result = array('status'=>1,'info'=>$e->getMessage());
		}
		$this->ajaxReturn($result);
	}
	public function oneSplit(){
		$split_ids = I('post.ids','',C('JSON_FILTER'));
        $result = array(
            'status'=>0,
            'info'=>'success',
            'data'=>array()
        );
        $fail = array();
        $success = array();
        if(empty($split_ids))
        {
            $result['info'] ="请选择档口单";
            $result['status'] = 1;
            $this->ajaxReturn($result);
        }
        foreach ($split_ids as $key=>$id)
        {
            $success[$key] = array();
            D('Purchase/StallsOrder')->oneSplit($id,$fail);
        }
        if (!empty($fail))
        {
            $result['status'] = 2;
        }
        $result['data']=array(
            'fail' => $fail,
        );
        $this->ajaxReturn($result);
	}
	
	public function addHotOrder(){
		try{
			$result = array('status'=>0,'info'=>'');
			D('Purchase/StallsOrder')->addHotOrder();
		}catch(BusinessLogicException $e){
			\Think\Log::write($e->getMessage());
			$result = array('status'=>1,'info'=>$e->getMessage());
		}catch (\Think\Exception $e){
			\Think\Log::write($e->getMessage());
			$result = array('status'=>1,'info'=>$e->getMessage());
		}
		$this->ajaxReturn($result);
	}
	public function checkHotPrintCode($ids){
		try{
			$result = array('status'=>0,'info'=>array());
			if(empty($ids)){
				SE('请选择档口单');
			}
			$refund_data = D('StallsOrder')->alias('so')->field('st.trade_id,"继续打印爆款单" as info,st.trade_no,if(st.refund_status = 1,"申请退款",if(st.refund_status = 2,"部分退款",if(st.refund_status = 3,"全部退款",""))) as  refund_status,st.trade_status')->join('left join sales_trade st on st.stalls_id = so.stalls_id')->where(array('st.refund_status'=>array('gt',0),'so.stalls_id'=>array('in',$ids)))->select();
			if(!empty($refund_data)){
				$result = array('status'=>2,'info'=>$refund_data);
				$this->ajaxReturn($result);
			}
		}catch (BusinessLogicException $e){
			\Think\Log::write($e->getMessage());
			$result = array('status'=>1,'info'=>$e->getMessage());
		}catch (\Think\Exception $e){
			\Think\Log::write($e->getMessage());
			$result = array('status'=>1,'info'=>$e->getMessage());
		}
		$this->ajaxReturn($result);
	}
	public function getPrintHotCode($ids,$num){
		try{
			$result = array('status'=>0,'info'=>array());
			if(empty($ids)){
				SE('请选择档口单');
			}
			$print_time = date('m-d',time());
			$data = D('Purchase/StallsOrder')->alias('so')->fetchSql(false)->field(array('"'.$print_time.'" as print_time','concat("[",format(so.goods_count-'.(int)$num.',0),"件]") as package_num_int','sod.spec_id','so.stalls_id','so.stalls_no','so.warehouse_id','cw.name as warehouse_name','so.purchaser_id','hr.fullname as purchaser_name','so.goods_fee','so.other_fee','so.post_fee','gs.spec_no','gs.spec_code','gs.spec_name','gs.barcode','sod.price','gg.goods_no','gg.goods_name','gg.short_name','gb.brand_name','gc.class_name'))->join('left join stalls_less_goods_detail sod on sod.stalls_id = so.stalls_id')->join('left join goods_spec gs on gs.spec_id = sod.spec_id')->join('left join goods_goods gg on gg.goods_id = gs.goods_id')->join('left join goods_brand gb on gb.brand_id = gg.brand_id')->join('left join goods_class gc on gc.class_id = gg.class_id')->join('left join cfg_warehouse cw on cw.warehouse_id = so.warehouse_id')->join('left join hr_employee hr on hr.employee_id = so.purchaser_id')->where(array('so.stalls_id'=>array('in',$ids)))->group('so.stalls_id')->select();
			
			if(empty($data)){
				SE('未查询到爆款码');
			}			
			$result['info'] = $data; 
			
		}catch (BusinessLogicException $e){
			\Think\Log::write($e->getMessage());
			$result = array('status'=>1,'info'=>$e->getMessage());
		}catch (\Think\Exception $e){
			\Think\Log::write($e->getMessage());
			$result = array('status'=>1,'info'=>$e->getMessage());
		}
		$this->ajaxReturn($result);
	}
	public function clearHotPrintStatus($id){
		try{
			$result = array('status'=>0,'info'=>'');
			D('Purchase/StallsOrder')->clearHotPrintStatus($id);
		}catch(BusinessLogicException $e){
			 $msg = $e->getMessage();
			 \Think\Log::write($this->name.'-clearHotPrintStatus-'.$msg);
			$result = array('status'=>1,'info'=>$e->getMessage());
		}catch (\PDOException $e) {
            $msg = $e->getMessage();
            \Think\Log::write($this->name.'-clearHotPrintStatus-'.$msg);
			$result = array('status'=>1,'info'=>self::PDO_ERROR);
        }catch (\Think\Exception $e){
			 $msg = $e->getMessage();
			 \Think\Log::write($this->name.'-clearHotPrintStatus-'.$msg);
			$result = array('status'=>1,'info'=>$e->getMessage());
		}
		$this->ajaxReturn($result);
	}
	public function exportToExcel(){
		if(!self::ALLOW_EXPORT){
			echo self::EXPORT_MSG;
			return false;
		}
		try{
			$id = I('get.id');
			$type = I('get.type');
			D('StallsOrder')->exportToExcel($type,$id);
		}catch(BusinessLogicException $e){
			$result = array('status'=>1,'info'=>$e->getMessage());
			\Think\Log::write($e->getMessage());
		}catch(\Exception $e){
			$result = array('status'=>1,'info'=>parent::UNKNOWN_ERROR);
			\Think\Log::write(parent::UNKNOWN_ERROR);
		}
		echo $result['info'];
	}
}
