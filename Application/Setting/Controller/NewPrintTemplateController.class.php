<?php
    namespace Setting\Controller;
    use Common\Common\UtilDB;
	use Common\Common\Factory;
	use Common\Controller\BaseController;
	use Platform\Manager;
					//use Setting\Model\PrintTemplateModel;

    class NewPrintTemplateController extends BaseController {
		//private $model = new PrintTemplateModel();
		
		public function savePrintTemplate() {
            //$data = I('post.');
			$model = Factory::getModel('PrintTemplate');
			$ret = $model->save(I('post.'), array('type','title', 'content', 'logistics_list', 'is_disabled'));
            $this->ajaxReturn($ret);
        }
        public function saveGoodsPrintTemplate() {
            $data = I('post.');
            $model = Factory::getModel('PrintTemplate');
            if($data['rec_id'] == 0)
            {
                unset($data['rec_id']);
            }
            $ret = $model->save($data, array('title', 'content','type','is_disabled'));
            $this->ajaxReturn($ret);
        }
		public function getNewPrintTemplate(){
			//$model = Factory::getModel('Logistics');
			try{
			$chg_logistics_list = D('Logistics')->field('logistics_id id,logistics_type type,logistics_name name')->where(array('is_disabled'=>0,'IF(LOCATE("shop_id",app_key),1,0)=1','bill_type'=>2))->select();//UtilDB::getCfgList(array('logistics'),array('logistics'=>array('is_disabled'=>0,'IF(LOCATE("shop_id",app_key),1,0)=1','bill_type'=>2)));
			$num = D('PrintTemplate')->field('count(*) AS num')->where(array('type'=>array('GT',3)))->select();
			$num = $num[0]['num'];
			$chg_logistics_list = array_merge(array(array("id"=>"0","name"=>"全部","type"=>'0')),$chg_logistics_list);
			$result_info = array();
			$waybill = \Platform\Common\ManagerFactory::getManager('NewWayBill');
			$waybill->getTemplates($result_info,$chg_logistics_list[1]['id'],true);
			$this->assign("template_list",str_replace("\"","'",json_encode($result_info['success'])));
			$this->assign('templateIndex',$result_info['success'][0]->standard_template_url);
			$this->assign('logisticsCode',json_encode(C('LOGISTICS_CODE')));
			//$field = 'logistics_id AS id,logistics_name AS name, logistics_type as type';
			//$logistics_list = $model->get($field);
			$this->assign('num',$num);
			$this->assign('logistics_list',str_replace("\"","'",json_encode($chg_logistics_list)));
			$this->assign('print_menu_id','logistics_print_template_fields');}
			catch(\Exception $e){
				\Think\Log::write($e->getMessage());
			}
			//$this->assign('logistics_list',$logistics_list);
			$this->display('new_print_template');
		}
		public function getGoodsTemplate(){
		    try {
		        $model = D('Setting/PrintTemplate');
		        $goods_template_info_fields = array('rec_id as id,type,title,content');
		        $goods_template_info_conditions = array('type'=>3);
		        $goods_template_info = $model->get($goods_template_info_fields,$goods_template_info_conditions);
		    } catch (\Exception $e) {
		        $msg = $e->getMessage();
		        \Think\Log::write(__CONTROLLER__.'-getGoodsTemplate-'.$msg);
		        
		    }
		    if(empty($goods_template_info)){
		       $goods_template_info = array(
		           'id' => 0,
		           'type' => 3,
		           'title'=>'默认发货单模板',
		           'content'=>"{}"
		       );
		    }else {
		        $goods_template_info = $goods_template_info[0];
		    }
		    $goods_template_info['content'] = str_replace('\\', '\\\\', $goods_template_info['content']);
		    $goods_template_info['content'] = str_replace('"', '\"', $goods_template_info['content']);
		    $this->assign('goods_template_info',json_encode($goods_template_info));
		    $this->display('goods_print_template');
		}
        public function getTemplateList($type,$logisticsType="") {
        	$logisticsType = $logisticsType == 0 ?"":$logisticsType;
			$this->getTemplate($type,'',$logisticsType);
			
        }
		/*
		public function getTemplateById($id){
			$this->getTemplate($id);
		}
		*/
		private function getTemplate($type, $id,$logisticsType='') {
			$fields = 'rec_id,title,type,content,logistics_list,is_disabled,modified';
			$model = Factory::getModel('PrintTemplate');
			$result = $model->get($fields, $type, $id,$logisticsType);
			$data['total'] = count($result);
			$data['rows'] = $result;
            
            $this->ajaxReturn($data);
        }

         public function getTemplates($shopId,$templateType){
        	$conditions['type'] = 'notWaybillTemplates';
        	$conditions['shopId'] = $shopId;
        	$waybill = \Platform\Common\ManagerFactory::getManager('NewWayBill');
        	if($templateType == 8||$templateType == 9){
        		$result = array("customarea"=>array(),"ISVTemplates"=>array());
        		$conditions['type'] = $templateType==8?'getCustomareas':'getISVTemplates';
        		$waybill -> getNotWaybillTemplates($result['customarea'],$conditions);
        		$conditions['type'] = 'getISVResources';
        		$conditions['get'] = $templateType==8?'CUSTOM_AREA':'TEMPLATE';
        		$waybill -> getNotWaybillTemplates($result['ISVTemplates'],$conditions);
        		$result_info['status'] = $result['customarea']['status'] == 0?0:$result['ISVTemplates']['status'];
        		if($result_info['status'] != 0){
        			$result_info = $result['customarea'];
        		}else {
        			if(isset($result['customarea']['success']))
        				$result_info['success'] =  $result['customarea']['success'];
        			if(isset($result['ISVTemplates']['success']))
        				$result_info['success'] = isset($result['customarea']['success'])?array_merge($result['ISVTemplates']['success'],$result_info['success']):$result['ISVTemplates']['success']; 
        		}
        	}else{
				$waybill -> getNotWaybillTemplates($result_info,$conditions);
			}
			if($result_info['status'] == 1||$result_info['status'] == 2){
				$this->ajaxReturn($result_info);
				return;
			}
			$this->ajaxReturn($result_info['success']);
        }

        public function downloadTemplates(){
			try{
				$shop = UtilDB::getCfgList(array('shop'),array('shop'=>array("platform_id"=>1,'auth_state'=>1)));
				$this -> assign('shop',$shop['shop']);
				$latest = D('PrintTemplate')->field('rec_id,shop_ids,type')->where(array('shop_ids'=>array('NEQ',''),'type'=>array('in','8,9')))->order('shop_ids desc')->limit(1)->find();
				$latest['rec_id'] = isset($latest['rec_id'])?$latest['rec_id']:"";
				$latest['real_shop_id'] = isset($latest['shop_ids'])?$latest['shop_ids']:"";
				$latest['shop_ids'] = isset($latest['shop_ids'])?$latest['shop_ids']:$shop['shop'][0]['id'];
				if(isset($latest['type']))
					$latest['type'] = !!strpos('569',$latest['type'])?9:8;
				else $latest['type'] = 9;
				$this->assign('latest',$latest);
			}catch(\PDOException $e){
				\Think\Log::write($e->getMessage());
			}
			$this->display('downloadTemplates');
	    }

	    public function hasAuthShop(){
	    	$ret = ['status'=>0,'msg'=>''];
	    	try{
		    	$shop = UtilDB::getCfgList(array('shop'),array('shop'=>array("platform_id"=>1,'auth_state'=>1)));
		    	if(empty($shop['shop']))
		    		$ret = ['status'=>1,'msg'=>'没有授权的淘宝店铺，请去店铺页面授权！！'];
		    	else $ret['shopNo'] = count($shop['shop']);
	    	}catch(\Exception $e){
	    		\Think\Log::write($e->getMessage());
	    		$ret = ['status'=>1,'msg'=>self::PDO_ERROR];
	    	}
	    	$this->ajaxReturn($ret);
	    }

	    public function saveTemplates($templates,$templateType){
	    	$shopId = I('post.shopId');
	    	$oldShopId = I('post.oldShopId');
	    	$oldRecId = I('post.oldRecId');
	    	$templates = (array)json_decode($templates);
	    	$logsitics_code = C('LOGISTICS_CODE');
	    	$i = 0;
	    	$template_ids = "";
	    	foreach($templates as $key){
	    		$content['user_std_template_url'] = $key->template_url;
	    		$content['user_std_template_id']  = $key->template_id;
	    		$content['custom_area_id'] = $key->customarea_id;
	    		$content['custom_area_url'] = $key->customarea_url;
	    		$data[$i]['shop_ids'] = $i ==0 ?$shopId:"";
	    		$data[$i]['content'] = json_encode($content);
	    		$data[$i]['title'] = $key->template_name;
	    		$data[$i]['type']  = $templateType;
	    		$data[$i]['logistics_list'] = array_search($key->cp_code, $logsitics_code);
	    		$data[$i]['template_id'] = $key->template_id;
	    		$template_ids .= $key->template_id.",";
	    		$i++;
	    	}
	    	$template_ids = substr($template_ids,0,strlen($template_ids)-1);
	    	try{
	    		$model = D('PrintTemplate');
	    		if(trim($oldRecId) != "" && $oldShopId != $shopId)
					$model->add(array('title'=>'','type'=>'','content'=>'','rec_id'=>$oldRecId,'shop_ids'=>''),array(),'shop_ids');
				$ret = $model->addTemplates($data,$template_ids);
	    	}catch(\PDOException $e){
	    		\Think\Log::write(__CONTROLLER__."--saveTemplates--".$e->getMessage());
	    		$ret['status'] = 1;
	    	}
	    	$this->ajaxReturn($ret);
	    }

	    public function deleteTemplate($rec_id){
	    	$ret['status'] = 0;
	    	try{
	    		$rec_id = D('PrintTemplate') -> deleteTemplate($rec_id);
	    		if($rec_id)
	    			$ret['rec_id'] = $rec_id;
	    		else {
	    			$ret['status'] = 1;
	    			$ret['msg'] = "模板删除失败，请联系管理员！";
	    		}
	    	}catch(\PDOException $e){
	    		$ret['status'] = 1;
	    		$ret['msg'] = "模板删除失败，请联系管理员！";
	    		\Think\Log::write($e->getMessage());
	    	}
	    	$this -> ajaxReturn($ret);
	    }

	    public function getTemplatesOnLogisticsCh($oldId,$newId){
	    	try{
	    		$logistics_info = D('Logistics')->field("logistics_id id,logistics_type type")->where("logistics_id in ($oldId,$newId)")->select();
	    		if($logistics_info[0]['type'] == $logistics_info[1]['type']){
	    			return ;
	    		}else {
	    			$result_info = array();
	    			$waybill = \Platform\Common\ManagerFactory::getManager('NewWayBill');
	   				$waybill->getTemplates($result_info,$newId);
	    		}
	    		$this->ajaxReturn($result_info);
	    	}catch(\PDOException $e){
				\Think\Log::write($e->getMessage());
	    	}
	    }
    }
