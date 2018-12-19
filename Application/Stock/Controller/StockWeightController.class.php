<?php
namespace Stock\Controller;
use Common\Controller\BaseController;
use Think\Exception\BusinessLogicException;
use Common\Common\UtilDB;
use Think\Model;
/**
 *称重类
 *@package Stock\Controller
 */
class StockWeightController extends BaseController{
	public function _initialize(){
			parent::_initialize();
			parent::getIDList($this->id_list,array('form'));
		}
	/**
     *初始化调拨单管理
     */
	public function getWeightList() {
		$src_order_no = I('get.src_order_no');
		$id_list = $this->id_list;   
		$params = array (
			'form' => array (
				'id' => $id_list['form'],
			),
		);
		try
		{
			$data = D('Stock/CfgEscale')->getEscaleInfo();
			//$set = D('Setting/System')->getSystemSetting();
			$setting = get_config_value(array('stock_scan_once','stock_auto_submit_time','overflow_weight_alarm','overflow_weight_small','overflow_weight_big'),array(1,10,0,5,5));

			//$setting['stock_scan_once'] = $set['stock_scan_once'];
			//$setting['stock_auto_submit_time'] = $set['stock_auto_submit_time'];
			$escale = "";
			foreach ($data as $key => $value) {
				$escale = $escale . '{ label:' . ( $value['type'] + $value['bandrate'] ) .',value :\'' .$value['name'] .'\'},';
			}
			$escale = substr($escale, 0, strlen($escale)-1);
		}
		catch(\PDOException $e)
		{
			\Think\Log::write($e->getMessage());
		}
		 $this->assign('src_order_no',$src_order_no);
		$this->assign('setting',$setting);
		//$this->assign('default',$default);
		$this->assign('escale', $escale);
		$this->assign('params', json_encode($params));
		$this->assign("id_list", $id_list);
		$this->display('show');
	}
		public function getStockoutOrderInfo(){
			try{
				$result = array('status'=>0,'info'=>'成功','data'=>array('fail'=>array(),'success'=>array()));
				$scan_no=I('post.scan_no','',C('JSON_FILTER'));
				$res_stockout_order_info=array();
				//----------以物流单号查询
				$isByLogisticsNo = true;
				$weight_info=D('Stock/StockWeight')->getWeightInfoByLogisticsNo($scan_no);
				if(empty($weight_info)){
					//----------以订单号查询
					$weight_info=D('Stock/StockWeight')->getWeightInfoByTradeNo($scan_no);
					$isByLogisticsNo = false;
				}
				if(empty($weight_info)){
					SE('出库单不存在');
				}
                if(count($weight_info) > 1 && $isByLogisticsNo){
                    SE('一个物流单号包含两条或以上订单记录,请扫描订单编号');
                }
				$weight_info = $weight_info[0];
				$error_list = array();
				if($weight_info['src_order_type'] != 1){
					$error_list[] = array('stock_id'=>$weight_info['stockout_id'],'stock_no'=>$weight_info['stockout_no'],'msg'=>'该出库单不是销售出库单');
				}
				if($weight_info['status'] < 55 ){
					$error_list[] = array('stock_id'=>$weight_info['stockout_id'],'stock_no'=>$weight_info['stockout_no'],'msg'=>'出库单状态不正确');
				}
				if(intval($weight_info['consign_status'])&2){
					$error_list[] = array('stock_id'=>$weight_info['stockout_id'],'stock_no'=>$weight_info['stockout_no'],'msg'=>'订单已称重');
				}
				if(intval($weight_info['freeze_reason']) != 0){
					$error_list[] = array('stock_id'=>$weight_info['stockout_id'],'stock_no'=>$weight_info['stockout_no'],'msg'=>'操作失败:出库单被冻结');
				}
				if(intval($weight_info['block_reason']) != 0){
					$reason =  D('SalesStockOut')->getBlockReason($weight_info['block_reason']);
					$error_list[] = array('stock_id'=>$weight_info['stockout_id'],'stock_no'=>$weight_info['stockout_no'],'msg'=>'出库单已经截停:'.$reason);
				}
				if(!empty($error_list)){
					$result['status'] = 2;
					$result['data']['fail']=$error_list;
				}else{
					$result['data']['success'] = $weight_info;
				}

			}catch(BusinessLogicException $e){
				$result['status']=1;
				$result['info']=$e->getMessage();
			}catch(\Exception $e){
				$msg=$e->getMessage();
				\Think\Log::write($msg);
				$result['status']=1;
				$result['info']=self::UNKNOWN_ERROR;
			}

			$this->ajaxReturn($result);
		}
		
		public function submitStockWeight(){
			$weight_info = I('','',C('JSON_FILTER'));
			$result = array('status'=>0,'info'=>'成功','data'=>array('fail'=>array(),'success'=>array()));
			try{
				$error_list = array();
				D('Stock/StockWeight')->consignStockWeight($weight_info,$error_list);
				if(!empty($error_list)){
					$result['status'] = 2;
					$result['data']['fail']= $error_list;
				}else{
                    $auto_consign = get_config_value('stockout_weight_auto_consign',0);
                    $stockout_info =D('Stock/StockOutOrder')->where(array('stockout_id'=>$weight_info['stockout_id']))->field(array('status','consign_status'))->find();
                    $success = array();
                    if($auto_consign && $stockout_info['status']<95){
                        D('Stock/SalesStockOut')->consignStockoutOrder($weight_info['stockout_id'],$error_list,$success);
                        if(!empty($error_list)){
                        	$result['status'] = 1;
                            $result['info']='称重成功，自动发货失败：'.$error_list[0]['msg'];
                        }else{
                            if($success['status'] >=95){
                                $result['info'] = '称重并自动发货成功';
                            }else{
                            	$result['status'] = 1;
                                $result['info'] = '称重成功，发货失败';
                            }
                        }
                    }

                }
			}catch(BusinessLogicException $e){
				$result['status'] = 1;
				$result['info'] = $e->getMessage();
			}catch(\Exception $e){
				\Think\Log::write($e->getMessage());
				$result['status'] = 1;
				$result['info'] = $this::UNKNOWN_ERROR;
			}
			$this->ajaxReturn($result);
		}
		
		public function setEscale($bandrate,$type,$defaultType,$defaultBandrate){
			$ret['status'] = 0;
			try{
				$ret['type'] = D('Stock/CfgEscale')->setEscale($type,$bandrate,$defaultType,$defaultBandrate);
			}
			catch(\PDOException $e)
			{
				$ret['status'] = 1;
				$ret['msg'] = "保存信息出错";
			}
			$this->ajaxReturn($ret);
		}
		
		public function getEscaleList(){
			try{
				$data = D('Stock/CfgEscale')->getEscaleInfo("name,type,reversed,bandrate,pattern","");
				foreach($data as $key => $value){
					if($value['reversed'] == 1){
						$default_escale = $value;
					}
					$type[$value['type']] = $value;
				}
				if(empty($default_escale)){
					$default_escale = $data[0];
				}
				$this->assign('type',json_encode($type));
				$this->assign('defaultEscale',$default_escale);
				$this->assign('data',json_encode($data));
				$this->display("escale_list");
			}catch(\PDOException $e){
				\Think\Log::write($e->getMessage());
			}
		}

		public function downloadEscale(){
			$file_url = APP_PATH."Runtime/File/Ekb.exe";
		    downloadFile($file_url);
		}
 }
