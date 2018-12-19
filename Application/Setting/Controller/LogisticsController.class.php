<?php
namespace Setting\Controller;

//use Common\Common\Factory;
use Common\Controller\BaseController;
use Common\Common\DatagridExtention;
use Common\Common\UtilDB;
use Think\Exception\BusinessLogicException;

	class LogisticsController extends BaseController {
		public function getLogistics(){
			$idList = DatagridExtention::getRichDatagrid('Logistics','logistics',U("Logistics/search"));
			$arr_tabs=array(
					array('id'=>$idList['id_list']['tab_container'],'url'=>U('Setting/SettingCommon/showTabsView',array('tabs'=>'upon_logistics')).'?tab=upon_logistics&prefix=logistics','title'=>'物流映射'),
			);
			$params = array(
				'add'=>array(
					'id'     =>  $idList['id_list']['add'],
					'title'  =>  '新建物流',
					'url'    =>  U('Setting/Logistics/addLogistics'),
					'width'  =>  '700',
					'height' =>  '250',
					'ismax'	 =>  false
				),
				'edit'=>array(
					'id'     =>  $idList['id_list']['edit'],
					'title'  =>  '编辑物流',
					'url'    =>  U('Setting/Logistics/addLogistics'),
					'width'  =>  '700',
					'height' =>  '250',
					'ismax'	 =>  false

				),
				'waybill_detail'=>array(
					'id'     =>  'flag_set_dialog',
					'title'  =>  '面单使用详情',
					'url'    =>  U('Setting/Logistics/waybillDetail'),
					'width'  =>  '450',
					'height' =>  '500',
					'ismax'	 =>  false

				),
				'datagrid'=>array(
					'id'     =>    $idList['id_list']['datagrid'],
				),
				'logistics_auth' => array(
					'id'     => 'reason_show_dialog',
					'title'  =>'物流授权',
					'width'  => '300',
					'height' => 'auto',
					'url' => U('Setting/Logistics/LogisticsAuth')
				),
				'search'=>array(
					'form_id'    =>   $idList["id_list"]["form"],
				),
				'tabs' => array(
					'id'=>$idList['id_list']['tab_container'],
					'url'=>U('SettingCommon/updateTabsData')
				)
			);
            $faq_url_logistics_question=C('faq_url');
            $this->assign('faq_url_logistics_question',$faq_url_logistics_question['logistics']);
			$this->assign('params', json_encode($params));
			$this->assign('arr_tabs', json_encode($arr_tabs));
			$this->assign('datagrid', $idList['datagrid']);
			$this->assign("id_list", $idList['id_list']);
			$this->display('show');
		}

		public function search($page = 1, $rows = 20, $search = array(), $sort = 'id', $order = 'desc'){
			try{
				$data=D('logistics')->searchLogistics($page,$rows,$search,$sort,$order);
			}catch(BusinessLogicException $e){
				$this->error($e->getMessage());
		    }catch(\Exception $e){
				$msg = $e->getMessage();
				\Think\Log::write($msg);
				$data = array('total' => 0, 'rows' => array());
			}
				$this->ajaxReturn($data);
    }

		public function addLogistics($id = 0){
			$id = (int)$id;
            $appExist = 0;
            $form = "'none'";
            $app_key = json_encode(array(),JSON_FORCE_OBJECT);
            if(0 != $id){
                $this->addNewLogistics($id,$form,$app_key,$appExist);
            }

            0 == $id?$mark = 'add':$mark = 'edit';
			$this->assign('mark', $mark);
			$this->assign('form', $form);
			$this->assign('appkey',$app_key);
			$this->assign('appExist',$appExist);
			$this->display('logistics_edit');
		}
		public function loadSelectedData($id){
			try{
				$data=D('logistics')->loadSelectedData($id);
			}catch(BusinessLogicException $e){
				$this->error($e->getMessage());
			}catch(\Exception $e){
				$msg = $e->getMessage();
				\Think\Log::write($msg);
			}
			return $data;
		}
		public function saveLogistics()
		{
			$arr = I('','',C('JSON_FILTER'));
			$type = $arr['type'];
			$arr = $arr['arr'];
			if(isset($arr['app_key']))
			{
				$arr['app_key'] = json_encode($arr['app_key'],JSON_FORCE_OBJECT);
			}
			$arr['product_type']=set_default_value($arr['product_type'], '');
			if(!empty($arr['logistics_type']))
			{
				$arr['logistics_type']=set_default_value($arr['logistics_type'], 0);
			}
			$arr['province']=set_default_value($arr['province'], 0);
			$arr['city']=set_default_value($arr['city'], 0);
			$arr['district']=set_default_value($arr['district'], 0);
			$result['status'] = 1;
			$result['info'] = '操作成功,物流映射成功';
			$error_list = array();
			try{

				D('logistics')->saveData($arr,$error_list);
				if(count($error_list)>0)
				{
					$result['status'] = 2;
					$result['info'] = array('total'=>count($error_list),'rows'=>$error_list);
				}
				}catch(BusinessLogicException $e){
					$result['status'] = 0;
					$result['info'] =$e->getMessage();
				}catch(\Exception $e){
					\Think\Log::write($e->getMessage());
					$result['status'] = 0;
					$result['info'] = self::PDO_ERROR;
				}
			$result['type'] = $type;
			$this->ajaxReturn($result);
		}
		public function LogisticsAuth($logistics_type,$bill_type,$appExist,$is_show)
		{
			$type = C('logistics_type');
			$logistics = array(
				'logistics_type'=>$logistics_type,
				'bill_type'=>$bill_type,
				'type'=>$type[$logistics_type],
			);
			
			switch ((int)$bill_type)
			{
				case 0:
					{
						break;
					}
				case 1:
					{
					    if((int)$logistics_type == 1311)
					    {
					        $where['shop'] = array(
					            'platform_id' => array('eq','3'),
					        );
					        $list_form = UtilDB::getCfgList(array('shop'),$where);
					        $this->assign("shop_list",$list_form['shop']);
					    }
						break;
					}
				case 2:
					{
					    $where['shop'] = array(
					      'platform_id' => array('in','1,2'),
					    );
					    $list_form = UtilDB::getCfgList(array('shop'),$where);
						$this->assign("shop_list",$list_form['shop']);
						break;
					}
				case 9:
					{
						$where['shop'] = array(
							'platform_id' => array('eq','3'),
						);
						$list_form = UtilDB::getCfgList(array('shop'),$where);
						$this->assign("shop_list",$list_form['shop']);
							
							break;
					}
			}
			$this->assign('logistics',$logistics);
			if(0 == $appExist || $is_show == 0){
				$this->display('dialog_logistics_auth_edit');
			}else{
				$this->display('dialog_logistics_auth_show');
			}
		}

		public function addNewLogistics($id,&$form ,& $app_key,&$appExist){
			$id = (int)$id;
            $data = $this->loadSelectedData($id);
            if ($data == "0") {
                $form = "'err'";
            } else {
                $form_data = $data[0];
                $form = array(
//                     'logistics_no' => $form_data['logistics_no'],
                    'logistics_name'    => $form_data['logistics_name'],
                    'logistics_type'    => $form_data['logistics_type'],
                    'telno'             => $form_data['telno'],
                    'remark'            => $form_data['remark'],
                    'is_disabled'       => $form_data['is_disabled'],
                    'is_support_cod'    => $form_data['is_support_cod'],
//						'is_dtb_no_api' => $form_data['is_dtb_no_api'],
                    'contact'           => $form_data['contact'],
                    'is_manual'         => $form_data['is_manual'],
                    'address'           => $form_data['address'],
                    'bill_type'         => $form_data['bill_type'],
                    'id'                => $form_data['id'],
                    'logistics_id'      => $form_data['id'],
                );
                $form = json_encode($form);
                //app_key 应该有默认的
                $appExist = $form_data['bill_type'] == 0? 0:1;
                $app_key = empty($form_data['app_key'])?json_encode(array(),JSON_FORCE_OBJECT):$form_data['app_key'];
                if (is_null(json_decode($app_key)))
                {
                    $app_key = json_encode(array(),JSON_FORCE_OBJECT);
                }
            }

		}
		public function waybillDetail()
		{
			try{
				$logistics_id = I('get.logistics_id','',C('JSON_FILTER'));
				if(empty($logistics_id)&&$logistics_id!=0)
				{
					SE('物流公司不能为空!');
				}
				$logistics_info = D('Logistics')->field('bill_type')->where(array('logistics_id'=>$logistics_id))->find();
				if($logistics_info['bill_type'] != 2)
				{
					SE('只支持菜鸟电子面单类型的物流查找!');
				}
				$result = array();
				D('Stock/WayBill','Controller')->searchWayBill($logistics_id,$result);
				if($result['status'] == 0)
				{
					if(empty($result['success']))
					{
						SE('未获取到相关信息!请到淘宝平台核对所需信息');
					}
					$this->assign('waybill_detail',$result['success']);
					$this->display('dialog_waybill_detail');
				}else{
					SE($result['msg']);
				}

			}catch(BusinessLogicException $e) {
				$this->assign('message',$e->getMessage());
				$this->display('Common@Exception:dialog');
				exit();
			}catch(\Exception $e) {
				\Think\Log::write($e->getMessage());
				$this->assign('message',self::UNKNOWN_ERROR);
				$this->display('Common@Exception:dialog');
				exit();
			}

		}
		//刷新系统与平台物流的映射
		public function uponLogistics()
		{
			$id = I('post.id');
			$res = array('status'=>0,'info'=>'操作成功');
			$error_list = array();
			try
			{
				D('Logistics')->uponLogistics($id,$error_list);
				if(count($error_list)>0)
				{
					$res['status'] = 2;
					$res['info'] = array('total'=>count($error_list),'rows'=>$error_list);
				}
			}catch (BusinessLogicException $e)
			{
				$res['status'] = 1;
				$res['info'] = $e->getMessage();
			}catch (\Exception $e)
			{
				$res['status'] = 1;
				$res['info'] = $e->getMessage();
			}
			$this->ajaxReturn($res);
		}


	}