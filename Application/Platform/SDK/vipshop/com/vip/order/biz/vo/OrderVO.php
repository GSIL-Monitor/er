<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\biz\vo;

class OrderVO {
	
	static $_TSPEC;
	public $orderId = null;
	public $orderSn = null;
	public $userId = null;
	public $userName = null;
	public $userIp = null;
	public $unionMark = null;
	public $warehouse = null;
	public $saleType = null;
	public $source = null;
	public $addrWarehouse = null;
	public $isJf = null;
	public $platform = null;
	public $saleArea = null;
	public $vipCard = null;
	public $isPos = null;
	public $carriage = null;
	public $orderSourceType = null;
	public $orderDate = null;
	public $remark = null;
	public $orderUpdateTime = null;
	public $orderStatus = null;
	public $orderStatusUpdateTime = null;
	public $wmsFlag = null;
	public $goodsAmount = null;
	public $orderType = null;
	public $orderSubStatus = null;
	public $orderAddTime = null;
	public $orderModel = null;
	public $auditTime = null;
	public $adminRemark = null;
	public $adminSms = null;
	public $hasAlert = null;
	public $smsSended = null;
	public $opFlag = null;
	public $operator = null;
	public $refuseGoodsType = null;
	public $unpassReason = null;
	public $transportType = null;
	public $specialTransportText = null;
	public $orderFlag = null;
	public $isReturnSurplus = null;
	public $surplusBack = null;
	public $transportName = null;
	public $transportNo = null;
	public $transportId = null;
	public $presentInfo = null;
	public $isSpecial = null;
	public $isLink = null;
	public $isSplit = null;
	public $isHold = null;
	public $isDisplay = null;
	public $vipclub = null;
	public $posTime = null;
	public $giftOrPointFlag = null;
	public $allotTime = null;
	public $allotUser = null;
	public $firstFlag = null;
	public $statusFlag = null;
	public $createTime = null;
	public $posTimeFormat = null;
	public $longTransportId = null;
	public $orderBizFlagsMap = null;
	public $orderBizFlags = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'orderId'
			),
			2 => array(
			'var' => 'orderSn'
			),
			3 => array(
			'var' => 'userId'
			),
			4 => array(
			'var' => 'userName'
			),
			5 => array(
			'var' => 'userIp'
			),
			6 => array(
			'var' => 'unionMark'
			),
			7 => array(
			'var' => 'warehouse'
			),
			8 => array(
			'var' => 'saleType'
			),
			10 => array(
			'var' => 'source'
			),
			11 => array(
			'var' => 'addrWarehouse'
			),
			12 => array(
			'var' => 'isJf'
			),
			13 => array(
			'var' => 'platform'
			),
			14 => array(
			'var' => 'saleArea'
			),
			15 => array(
			'var' => 'vipCard'
			),
			16 => array(
			'var' => 'isPos'
			),
			17 => array(
			'var' => 'carriage'
			),
			18 => array(
			'var' => 'orderSourceType'
			),
			19 => array(
			'var' => 'orderDate'
			),
			20 => array(
			'var' => 'remark'
			),
			21 => array(
			'var' => 'orderUpdateTime'
			),
			22 => array(
			'var' => 'orderStatus'
			),
			23 => array(
			'var' => 'orderStatusUpdateTime'
			),
			24 => array(
			'var' => 'wmsFlag'
			),
			25 => array(
			'var' => 'goodsAmount'
			),
			26 => array(
			'var' => 'orderType'
			),
			27 => array(
			'var' => 'orderSubStatus'
			),
			28 => array(
			'var' => 'orderAddTime'
			),
			29 => array(
			'var' => 'orderModel'
			),
			30 => array(
			'var' => 'auditTime'
			),
			31 => array(
			'var' => 'adminRemark'
			),
			32 => array(
			'var' => 'adminSms'
			),
			33 => array(
			'var' => 'hasAlert'
			),
			34 => array(
			'var' => 'smsSended'
			),
			35 => array(
			'var' => 'opFlag'
			),
			36 => array(
			'var' => 'operator'
			),
			37 => array(
			'var' => 'refuseGoodsType'
			),
			38 => array(
			'var' => 'unpassReason'
			),
			39 => array(
			'var' => 'transportType'
			),
			40 => array(
			'var' => 'specialTransportText'
			),
			41 => array(
			'var' => 'orderFlag'
			),
			42 => array(
			'var' => 'isReturnSurplus'
			),
			43 => array(
			'var' => 'surplusBack'
			),
			44 => array(
			'var' => 'transportName'
			),
			45 => array(
			'var' => 'transportNo'
			),
			46 => array(
			'var' => 'transportId'
			),
			47 => array(
			'var' => 'presentInfo'
			),
			48 => array(
			'var' => 'isSpecial'
			),
			49 => array(
			'var' => 'isLink'
			),
			50 => array(
			'var' => 'isSplit'
			),
			51 => array(
			'var' => 'isHold'
			),
			52 => array(
			'var' => 'isDisplay'
			),
			53 => array(
			'var' => 'vipclub'
			),
			54 => array(
			'var' => 'posTime'
			),
			55 => array(
			'var' => 'giftOrPointFlag'
			),
			56 => array(
			'var' => 'allotTime'
			),
			57 => array(
			'var' => 'allotUser'
			),
			58 => array(
			'var' => 'firstFlag'
			),
			59 => array(
			'var' => 'statusFlag'
			),
			60 => array(
			'var' => 'createTime'
			),
			61 => array(
			'var' => 'posTimeFormat'
			),
			62 => array(
			'var' => 'longTransportId'
			),
			63 => array(
			'var' => 'orderBizFlagsMap'
			),
			64 => array(
			'var' => 'orderBizFlags'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['orderId'])){
				
				$this->orderId = $vals['orderId'];
			}
			
			
			if (isset($vals['orderSn'])){
				
				$this->orderSn = $vals['orderSn'];
			}
			
			
			if (isset($vals['userId'])){
				
				$this->userId = $vals['userId'];
			}
			
			
			if (isset($vals['userName'])){
				
				$this->userName = $vals['userName'];
			}
			
			
			if (isset($vals['userIp'])){
				
				$this->userIp = $vals['userIp'];
			}
			
			
			if (isset($vals['unionMark'])){
				
				$this->unionMark = $vals['unionMark'];
			}
			
			
			if (isset($vals['warehouse'])){
				
				$this->warehouse = $vals['warehouse'];
			}
			
			
			if (isset($vals['saleType'])){
				
				$this->saleType = $vals['saleType'];
			}
			
			
			if (isset($vals['source'])){
				
				$this->source = $vals['source'];
			}
			
			
			if (isset($vals['addrWarehouse'])){
				
				$this->addrWarehouse = $vals['addrWarehouse'];
			}
			
			
			if (isset($vals['isJf'])){
				
				$this->isJf = $vals['isJf'];
			}
			
			
			if (isset($vals['platform'])){
				
				$this->platform = $vals['platform'];
			}
			
			
			if (isset($vals['saleArea'])){
				
				$this->saleArea = $vals['saleArea'];
			}
			
			
			if (isset($vals['vipCard'])){
				
				$this->vipCard = $vals['vipCard'];
			}
			
			
			if (isset($vals['isPos'])){
				
				$this->isPos = $vals['isPos'];
			}
			
			
			if (isset($vals['carriage'])){
				
				$this->carriage = $vals['carriage'];
			}
			
			
			if (isset($vals['orderSourceType'])){
				
				$this->orderSourceType = $vals['orderSourceType'];
			}
			
			
			if (isset($vals['orderDate'])){
				
				$this->orderDate = $vals['orderDate'];
			}
			
			
			if (isset($vals['remark'])){
				
				$this->remark = $vals['remark'];
			}
			
			
			if (isset($vals['orderUpdateTime'])){
				
				$this->orderUpdateTime = $vals['orderUpdateTime'];
			}
			
			
			if (isset($vals['orderStatus'])){
				
				$this->orderStatus = $vals['orderStatus'];
			}
			
			
			if (isset($vals['orderStatusUpdateTime'])){
				
				$this->orderStatusUpdateTime = $vals['orderStatusUpdateTime'];
			}
			
			
			if (isset($vals['wmsFlag'])){
				
				$this->wmsFlag = $vals['wmsFlag'];
			}
			
			
			if (isset($vals['goodsAmount'])){
				
				$this->goodsAmount = $vals['goodsAmount'];
			}
			
			
			if (isset($vals['orderType'])){
				
				$this->orderType = $vals['orderType'];
			}
			
			
			if (isset($vals['orderSubStatus'])){
				
				$this->orderSubStatus = $vals['orderSubStatus'];
			}
			
			
			if (isset($vals['orderAddTime'])){
				
				$this->orderAddTime = $vals['orderAddTime'];
			}
			
			
			if (isset($vals['orderModel'])){
				
				$this->orderModel = $vals['orderModel'];
			}
			
			
			if (isset($vals['auditTime'])){
				
				$this->auditTime = $vals['auditTime'];
			}
			
			
			if (isset($vals['adminRemark'])){
				
				$this->adminRemark = $vals['adminRemark'];
			}
			
			
			if (isset($vals['adminSms'])){
				
				$this->adminSms = $vals['adminSms'];
			}
			
			
			if (isset($vals['hasAlert'])){
				
				$this->hasAlert = $vals['hasAlert'];
			}
			
			
			if (isset($vals['smsSended'])){
				
				$this->smsSended = $vals['smsSended'];
			}
			
			
			if (isset($vals['opFlag'])){
				
				$this->opFlag = $vals['opFlag'];
			}
			
			
			if (isset($vals['operator'])){
				
				$this->operator = $vals['operator'];
			}
			
			
			if (isset($vals['refuseGoodsType'])){
				
				$this->refuseGoodsType = $vals['refuseGoodsType'];
			}
			
			
			if (isset($vals['unpassReason'])){
				
				$this->unpassReason = $vals['unpassReason'];
			}
			
			
			if (isset($vals['transportType'])){
				
				$this->transportType = $vals['transportType'];
			}
			
			
			if (isset($vals['specialTransportText'])){
				
				$this->specialTransportText = $vals['specialTransportText'];
			}
			
			
			if (isset($vals['orderFlag'])){
				
				$this->orderFlag = $vals['orderFlag'];
			}
			
			
			if (isset($vals['isReturnSurplus'])){
				
				$this->isReturnSurplus = $vals['isReturnSurplus'];
			}
			
			
			if (isset($vals['surplusBack'])){
				
				$this->surplusBack = $vals['surplusBack'];
			}
			
			
			if (isset($vals['transportName'])){
				
				$this->transportName = $vals['transportName'];
			}
			
			
			if (isset($vals['transportNo'])){
				
				$this->transportNo = $vals['transportNo'];
			}
			
			
			if (isset($vals['transportId'])){
				
				$this->transportId = $vals['transportId'];
			}
			
			
			if (isset($vals['presentInfo'])){
				
				$this->presentInfo = $vals['presentInfo'];
			}
			
			
			if (isset($vals['isSpecial'])){
				
				$this->isSpecial = $vals['isSpecial'];
			}
			
			
			if (isset($vals['isLink'])){
				
				$this->isLink = $vals['isLink'];
			}
			
			
			if (isset($vals['isSplit'])){
				
				$this->isSplit = $vals['isSplit'];
			}
			
			
			if (isset($vals['isHold'])){
				
				$this->isHold = $vals['isHold'];
			}
			
			
			if (isset($vals['isDisplay'])){
				
				$this->isDisplay = $vals['isDisplay'];
			}
			
			
			if (isset($vals['vipclub'])){
				
				$this->vipclub = $vals['vipclub'];
			}
			
			
			if (isset($vals['posTime'])){
				
				$this->posTime = $vals['posTime'];
			}
			
			
			if (isset($vals['giftOrPointFlag'])){
				
				$this->giftOrPointFlag = $vals['giftOrPointFlag'];
			}
			
			
			if (isset($vals['allotTime'])){
				
				$this->allotTime = $vals['allotTime'];
			}
			
			
			if (isset($vals['allotUser'])){
				
				$this->allotUser = $vals['allotUser'];
			}
			
			
			if (isset($vals['firstFlag'])){
				
				$this->firstFlag = $vals['firstFlag'];
			}
			
			
			if (isset($vals['statusFlag'])){
				
				$this->statusFlag = $vals['statusFlag'];
			}
			
			
			if (isset($vals['createTime'])){
				
				$this->createTime = $vals['createTime'];
			}
			
			
			if (isset($vals['posTimeFormat'])){
				
				$this->posTimeFormat = $vals['posTimeFormat'];
			}
			
			
			if (isset($vals['longTransportId'])){
				
				$this->longTransportId = $vals['longTransportId'];
			}
			
			
			if (isset($vals['orderBizFlagsMap'])){
				
				$this->orderBizFlagsMap = $vals['orderBizFlagsMap'];
			}
			
			
			if (isset($vals['orderBizFlags'])){
				
				$this->orderBizFlags = $vals['orderBizFlags'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'OrderVO';
	}
	
	public function read($input){
		
		$input->readStructBegin();
		while(true){
			
			$schemeField = $input->readFieldBegin();
			if ($schemeField == null) break;
			$needSkip = true;
			
			
			if ("orderId" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->orderId); 
				
			}
			
			
			
			
			if ("orderSn" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->orderSn);
				
			}
			
			
			
			
			if ("userId" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->userId); 
				
			}
			
			
			
			
			if ("userName" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->userName);
				
			}
			
			
			
			
			if ("userIp" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->userIp);
				
			}
			
			
			
			
			if ("unionMark" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->unionMark);
				
			}
			
			
			
			
			if ("warehouse" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->warehouse);
				
			}
			
			
			
			
			if ("saleType" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->saleType); 
				
			}
			
			
			
			
			if ("source" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->source);
				
			}
			
			
			
			
			if ("addrWarehouse" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->addrWarehouse);
				
			}
			
			
			
			
			if ("isJf" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->isJf); 
				
			}
			
			
			
			
			if ("platform" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->platform); 
				
			}
			
			
			
			
			if ("saleArea" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->saleArea); 
				
			}
			
			
			
			
			if ("vipCard" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->vipCard);
				
			}
			
			
			
			
			if ("isPos" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->isPos); 
				
			}
			
			
			
			
			if ("carriage" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->carriage);
				
			}
			
			
			
			
			if ("orderSourceType" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->orderSourceType);
				
			}
			
			
			
			
			if ("orderDate" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->orderDate); 
				
			}
			
			
			
			
			if ("remark" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->remark);
				
			}
			
			
			
			
			if ("orderUpdateTime" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->orderUpdateTime); 
				
			}
			
			
			
			
			if ("orderStatus" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->orderStatus); 
				
			}
			
			
			
			
			if ("orderStatusUpdateTime" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->orderStatusUpdateTime); 
				
			}
			
			
			
			
			if ("wmsFlag" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->wmsFlag); 
				
			}
			
			
			
			
			if ("goodsAmount" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->goodsAmount); 
				
			}
			
			
			
			
			if ("orderType" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->orderType); 
				
			}
			
			
			
			
			if ("orderSubStatus" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->orderSubStatus); 
				
			}
			
			
			
			
			if ("orderAddTime" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->orderAddTime); 
				
			}
			
			
			
			
			if ("orderModel" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->orderModel); 
				
			}
			
			
			
			
			if ("auditTime" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->auditTime); 
				
			}
			
			
			
			
			if ("adminRemark" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->adminRemark);
				
			}
			
			
			
			
			if ("adminSms" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->adminSms);
				
			}
			
			
			
			
			if ("hasAlert" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->hasAlert);
				
			}
			
			
			
			
			if ("smsSended" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->smsSended);
				
			}
			
			
			
			
			if ("opFlag" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->opFlag); 
				
			}
			
			
			
			
			if ("operator" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->operator);
				
			}
			
			
			
			
			if ("refuseGoodsType" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->refuseGoodsType); 
				
			}
			
			
			
			
			if ("unpassReason" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->unpassReason);
				
			}
			
			
			
			
			if ("transportType" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->transportType);
				
			}
			
			
			
			
			if ("specialTransportText" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->specialTransportText);
				
			}
			
			
			
			
			if ("orderFlag" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->orderFlag);
				
			}
			
			
			
			
			if ("isReturnSurplus" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->isReturnSurplus); 
				
			}
			
			
			
			
			if ("surplusBack" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->surplusBack);
				
			}
			
			
			
			
			if ("transportName" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->transportName);
				
			}
			
			
			
			
			if ("transportNo" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->transportNo);
				
			}
			
			
			
			
			if ("transportId" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->transportId); 
				
			}
			
			
			
			
			if ("presentInfo" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->presentInfo);
				
			}
			
			
			
			
			if ("isSpecial" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->isSpecial); 
				
			}
			
			
			
			
			if ("isLink" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->isLink); 
				
			}
			
			
			
			
			if ("isSplit" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->isSplit); 
				
			}
			
			
			
			
			if ("isHold" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->isHold); 
				
			}
			
			
			
			
			if ("isDisplay" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->isDisplay); 
				
			}
			
			
			
			
			if ("vipclub" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->vipclub);
				
			}
			
			
			
			
			if ("posTime" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->posTime); 
				
			}
			
			
			
			
			if ("giftOrPointFlag" == $schemeField){
				
				$needSkip = false;
				$input->readBool($this->giftOrPointFlag);
				
			}
			
			
			
			
			if ("allotTime" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->allotTime); 
				
			}
			
			
			
			
			if ("allotUser" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->allotUser);
				
			}
			
			
			
			
			if ("firstFlag" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->firstFlag); 
				
			}
			
			
			
			
			if ("statusFlag" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->statusFlag); 
				
			}
			
			
			
			
			if ("createTime" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->createTime); 
				
			}
			
			
			
			
			if ("posTimeFormat" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->posTimeFormat);
				
			}
			
			
			
			
			if ("longTransportId" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->longTransportId); 
				
			}
			
			
			
			
			if ("orderBizFlagsMap" == $schemeField){
				
				$needSkip = false;
				
				$this->orderBizFlagsMap = array();
				$input->readMapBegin();
				while(true){
					
					try{
						
						$key1 = '';
						$input->readString($key1);
						
						$val1 = 0;
						$input->readI32($val1); 
						
						$this->orderBizFlagsMap[$key1] = $val1;
					}
					catch(\Exception $e){
						
						break;
					}
				}
				
				$input->readMapEnd();
				
			}
			
			
			
			
			if ("orderBizFlags" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->orderBizFlags); 
				
			}
			
			
			
			if($needSkip){
				
				\Osp\Protocol\ProtocolUtil::skip($input);
			}
			
			$input->readFieldEnd();
		}
		
		$input->readStructEnd();
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->orderId !== null) {
			
			$xfer += $output->writeFieldBegin('orderId');
			$xfer += $output->writeI64($this->orderId);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderSn !== null) {
			
			$xfer += $output->writeFieldBegin('orderSn');
			$xfer += $output->writeString($this->orderSn);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->userId !== null) {
			
			$xfer += $output->writeFieldBegin('userId');
			$xfer += $output->writeI64($this->userId);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->userName !== null) {
			
			$xfer += $output->writeFieldBegin('userName');
			$xfer += $output->writeString($this->userName);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->userIp !== null) {
			
			$xfer += $output->writeFieldBegin('userIp');
			$xfer += $output->writeString($this->userIp);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->unionMark !== null) {
			
			$xfer += $output->writeFieldBegin('unionMark');
			$xfer += $output->writeString($this->unionMark);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->warehouse !== null) {
			
			$xfer += $output->writeFieldBegin('warehouse');
			$xfer += $output->writeString($this->warehouse);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->saleType !== null) {
			
			$xfer += $output->writeFieldBegin('saleType');
			$xfer += $output->writeI32($this->saleType);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->source !== null) {
			
			$xfer += $output->writeFieldBegin('source');
			$xfer += $output->writeString($this->source);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->addrWarehouse !== null) {
			
			$xfer += $output->writeFieldBegin('addrWarehouse');
			$xfer += $output->writeString($this->addrWarehouse);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->isJf !== null) {
			
			$xfer += $output->writeFieldBegin('isJf');
			$xfer += $output->writeI32($this->isJf);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->platform !== null) {
			
			$xfer += $output->writeFieldBegin('platform');
			$xfer += $output->writeI32($this->platform);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->saleArea !== null) {
			
			$xfer += $output->writeFieldBegin('saleArea');
			$xfer += $output->writeI32($this->saleArea);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->vipCard !== null) {
			
			$xfer += $output->writeFieldBegin('vipCard');
			$xfer += $output->writeString($this->vipCard);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->isPos !== null) {
			
			$xfer += $output->writeFieldBegin('isPos');
			$xfer += $output->writeI32($this->isPos);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->carriage !== null) {
			
			$xfer += $output->writeFieldBegin('carriage');
			$xfer += $output->writeString($this->carriage);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderSourceType !== null) {
			
			$xfer += $output->writeFieldBegin('orderSourceType');
			$xfer += $output->writeString($this->orderSourceType);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderDate !== null) {
			
			$xfer += $output->writeFieldBegin('orderDate');
			$xfer += $output->writeI64($this->orderDate);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->remark !== null) {
			
			$xfer += $output->writeFieldBegin('remark');
			$xfer += $output->writeString($this->remark);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderUpdateTime !== null) {
			
			$xfer += $output->writeFieldBegin('orderUpdateTime');
			$xfer += $output->writeI64($this->orderUpdateTime);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderStatus !== null) {
			
			$xfer += $output->writeFieldBegin('orderStatus');
			$xfer += $output->writeI32($this->orderStatus);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderStatusUpdateTime !== null) {
			
			$xfer += $output->writeFieldBegin('orderStatusUpdateTime');
			$xfer += $output->writeI64($this->orderStatusUpdateTime);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->wmsFlag !== null) {
			
			$xfer += $output->writeFieldBegin('wmsFlag');
			$xfer += $output->writeI32($this->wmsFlag);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->goodsAmount !== null) {
			
			$xfer += $output->writeFieldBegin('goodsAmount');
			$xfer += $output->writeI32($this->goodsAmount);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderType !== null) {
			
			$xfer += $output->writeFieldBegin('orderType');
			$xfer += $output->writeI32($this->orderType);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderSubStatus !== null) {
			
			$xfer += $output->writeFieldBegin('orderSubStatus');
			$xfer += $output->writeI32($this->orderSubStatus);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderAddTime !== null) {
			
			$xfer += $output->writeFieldBegin('orderAddTime');
			$xfer += $output->writeI64($this->orderAddTime);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderModel !== null) {
			
			$xfer += $output->writeFieldBegin('orderModel');
			$xfer += $output->writeI32($this->orderModel);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->auditTime !== null) {
			
			$xfer += $output->writeFieldBegin('auditTime');
			$xfer += $output->writeI64($this->auditTime);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->adminRemark !== null) {
			
			$xfer += $output->writeFieldBegin('adminRemark');
			$xfer += $output->writeString($this->adminRemark);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->adminSms !== null) {
			
			$xfer += $output->writeFieldBegin('adminSms');
			$xfer += $output->writeString($this->adminSms);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->hasAlert !== null) {
			
			$xfer += $output->writeFieldBegin('hasAlert');
			$xfer += $output->writeString($this->hasAlert);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->smsSended !== null) {
			
			$xfer += $output->writeFieldBegin('smsSended');
			$xfer += $output->writeString($this->smsSended);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->opFlag !== null) {
			
			$xfer += $output->writeFieldBegin('opFlag');
			$xfer += $output->writeI32($this->opFlag);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->operator !== null) {
			
			$xfer += $output->writeFieldBegin('operator');
			$xfer += $output->writeString($this->operator);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->refuseGoodsType !== null) {
			
			$xfer += $output->writeFieldBegin('refuseGoodsType');
			$xfer += $output->writeI32($this->refuseGoodsType);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->unpassReason !== null) {
			
			$xfer += $output->writeFieldBegin('unpassReason');
			$xfer += $output->writeString($this->unpassReason);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->transportType !== null) {
			
			$xfer += $output->writeFieldBegin('transportType');
			$xfer += $output->writeString($this->transportType);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->specialTransportText !== null) {
			
			$xfer += $output->writeFieldBegin('specialTransportText');
			$xfer += $output->writeString($this->specialTransportText);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderFlag !== null) {
			
			$xfer += $output->writeFieldBegin('orderFlag');
			$xfer += $output->writeString($this->orderFlag);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->isReturnSurplus !== null) {
			
			$xfer += $output->writeFieldBegin('isReturnSurplus');
			$xfer += $output->writeI32($this->isReturnSurplus);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->surplusBack !== null) {
			
			$xfer += $output->writeFieldBegin('surplusBack');
			$xfer += $output->writeString($this->surplusBack);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->transportName !== null) {
			
			$xfer += $output->writeFieldBegin('transportName');
			$xfer += $output->writeString($this->transportName);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->transportNo !== null) {
			
			$xfer += $output->writeFieldBegin('transportNo');
			$xfer += $output->writeString($this->transportNo);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->transportId !== null) {
			
			$xfer += $output->writeFieldBegin('transportId');
			$xfer += $output->writeI32($this->transportId);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->presentInfo !== null) {
			
			$xfer += $output->writeFieldBegin('presentInfo');
			$xfer += $output->writeString($this->presentInfo);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->isSpecial !== null) {
			
			$xfer += $output->writeFieldBegin('isSpecial');
			$xfer += $output->writeI32($this->isSpecial);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->isLink !== null) {
			
			$xfer += $output->writeFieldBegin('isLink');
			$xfer += $output->writeI32($this->isLink);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->isSplit !== null) {
			
			$xfer += $output->writeFieldBegin('isSplit');
			$xfer += $output->writeI32($this->isSplit);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->isHold !== null) {
			
			$xfer += $output->writeFieldBegin('isHold');
			$xfer += $output->writeI32($this->isHold);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->isDisplay !== null) {
			
			$xfer += $output->writeFieldBegin('isDisplay');
			$xfer += $output->writeI32($this->isDisplay);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->vipclub !== null) {
			
			$xfer += $output->writeFieldBegin('vipclub');
			$xfer += $output->writeString($this->vipclub);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->posTime !== null) {
			
			$xfer += $output->writeFieldBegin('posTime');
			$xfer += $output->writeI64($this->posTime);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->giftOrPointFlag !== null) {
			
			$xfer += $output->writeFieldBegin('giftOrPointFlag');
			$xfer += $output->writeBool($this->giftOrPointFlag);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->allotTime !== null) {
			
			$xfer += $output->writeFieldBegin('allotTime');
			$xfer += $output->writeI64($this->allotTime);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->allotUser !== null) {
			
			$xfer += $output->writeFieldBegin('allotUser');
			$xfer += $output->writeString($this->allotUser);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->firstFlag !== null) {
			
			$xfer += $output->writeFieldBegin('firstFlag');
			$xfer += $output->writeI32($this->firstFlag);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->statusFlag !== null) {
			
			$xfer += $output->writeFieldBegin('statusFlag');
			$xfer += $output->writeI32($this->statusFlag);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->createTime !== null) {
			
			$xfer += $output->writeFieldBegin('createTime');
			$xfer += $output->writeI64($this->createTime);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->posTimeFormat !== null) {
			
			$xfer += $output->writeFieldBegin('posTimeFormat');
			$xfer += $output->writeString($this->posTimeFormat);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->longTransportId !== null) {
			
			$xfer += $output->writeFieldBegin('longTransportId');
			$xfer += $output->writeI64($this->longTransportId);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderBizFlagsMap !== null) {
			
			$xfer += $output->writeFieldBegin('orderBizFlagsMap');
			
			if (!is_array($this->orderBizFlagsMap)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeMapBegin();
			foreach ($this->orderBizFlagsMap as $kiter0 => $viter0){
				
				$xfer += $output->writeString($kiter0);
				
				$xfer += $output->writeI32($viter0);
				
			}
			
			$output->writeMapEnd();
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderBizFlags !== null) {
			
			$xfer += $output->writeFieldBegin('orderBizFlags');
			$xfer += $output->writeI32($this->orderBizFlags);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}

?>