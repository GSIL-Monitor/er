<?php
    namespace Setting\Controller;

	use Common\Common\Factory;
	use Common\Controller\BaseController;
					//use Setting\Model\PrintTemplateModel;

    class PrintTemplateController extends BaseController {
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
		public function getLogisticsTemplate(){
			$model = Factory::getModel('Logistics');
			
			$field = 'logistics_id AS id,logistics_name AS name, logistics_type as type';
			$logistics_list = $model->get($field);
			
			$this->assign('print_menu_id','logistics_print_template_fields');
			$this->assign('logistics_list',$logistics_list);
			$this->display('logistics_print_template');
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
        public function getTemplateList($type) {
            
			$this->getTemplate($type, '');
			
        }
		/*
		public function getTemplateById($id){
			$this->getTemplate($id);
		}
		*/
		private function getTemplate($type, $id) {
			$fields = 'rec_id,title,type,content,logistics_list,is_disabled,modified';
			$model = Factory::getModel('PrintTemplate');
			$result = $model->get($fields, $type, $id);
			
			$data['total'] = count($result);
			$data['rows'] = $result;
            
            $this->ajaxReturn($data);
        }

    }
