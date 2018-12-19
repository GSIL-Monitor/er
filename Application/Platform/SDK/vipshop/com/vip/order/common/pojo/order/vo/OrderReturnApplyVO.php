<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\common\pojo\order\vo;

class OrderReturnApplyVO {
	
	static $_TSPEC;
	public $applyId = null;
	public $userId = null;
	public $orderSn = null;
	public $orderId = null;
	public $returnReasonId = null;
	public $returnReasonDetails = null;
	public $resend = null;
	public $returnGoodsTotalMoney = null;
	public $returnGoodsTotalAmount = null;
	public $returnCarriage = null;
	public $reimburseBackCarriage = null;
	public $returnCouponMoney = null;
	public $returnMoney = null;
	public $returnWalletMoney = null;
	public $adjuctMoney = null;
	public $actualBackCarriage = null;
	public $refundType = null;
	public $backTime = null;
	public $orderReturnType = null;
	public $auditStatus = null;
	public $goodsBackWay = null;
	public $returnsWayTime = null;
	public $returnsAreaName = null;
	public $returnsWayTimeInfo = null;
	public $orderAfterStatus = null;
	public $orderAfterStatusUpdtime = null;
	public $remark = null;
	public $backCarriagePayOnCollect = null;
	public $returnVirtualMoney = null;
	public $extra1 = null;
	public $refundFlag = null;
	public $skipAuditFlag = null;
	public $applyTime = null;
	public $bankId = null;
	public $bankName = null;
	public $bankBranch = null;
	public $bankAccountName = null;
	public $bankAccount = null;
	public $bankArea = null;
	public $backAddress = null;
	public $fixCarriage = null;
	public $returnPackageMoney = null;
	public $returnGiftCardType = null;
	public $giftCardToVirtualMoney = null;
	public $backFeeType = null;
	public $cardVirtualMoney = null;
	public $returnSubtotal = null;
	public $returnBankId = null;
	public $returnBankName = null;
	public $returnBankCard = null;
	public $returnUserName = null;
	public $merchantCode = null;
	public $pickupTime = null;
	public $isPremium = null;
	public $vendorAuditFlag = null;
	public $sourceType = null;
	public $returnWarehouse = null;
	public $operatorName = null;
	public $returnRefundType = null;
	public $returnRefundStatus = null;
	public $afterSaleSn = null;
	public $afterSaleStatus = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'applyId'
			),
			2 => array(
			'var' => 'userId'
			),
			3 => array(
			'var' => 'orderSn'
			),
			4 => array(
			'var' => 'orderId'
			),
			5 => array(
			'var' => 'returnReasonId'
			),
			6 => array(
			'var' => 'returnReasonDetails'
			),
			7 => array(
			'var' => 'resend'
			),
			8 => array(
			'var' => 'returnGoodsTotalMoney'
			),
			9 => array(
			'var' => 'returnGoodsTotalAmount'
			),
			10 => array(
			'var' => 'returnCarriage'
			),
			11 => array(
			'var' => 'reimburseBackCarriage'
			),
			12 => array(
			'var' => 'returnCouponMoney'
			),
			13 => array(
			'var' => 'returnMoney'
			),
			14 => array(
			'var' => 'returnWalletMoney'
			),
			15 => array(
			'var' => 'adjuctMoney'
			),
			16 => array(
			'var' => 'actualBackCarriage'
			),
			17 => array(
			'var' => 'refundType'
			),
			18 => array(
			'var' => 'backTime'
			),
			19 => array(
			'var' => 'orderReturnType'
			),
			20 => array(
			'var' => 'auditStatus'
			),
			21 => array(
			'var' => 'goodsBackWay'
			),
			22 => array(
			'var' => 'returnsWayTime'
			),
			23 => array(
			'var' => 'returnsAreaName'
			),
			24 => array(
			'var' => 'returnsWayTimeInfo'
			),
			25 => array(
			'var' => 'orderAfterStatus'
			),
			26 => array(
			'var' => 'orderAfterStatusUpdtime'
			),
			27 => array(
			'var' => 'remark'
			),
			28 => array(
			'var' => 'backCarriagePayOnCollect'
			),
			29 => array(
			'var' => 'returnVirtualMoney'
			),
			30 => array(
			'var' => 'extra1'
			),
			31 => array(
			'var' => 'refundFlag'
			),
			32 => array(
			'var' => 'skipAuditFlag'
			),
			33 => array(
			'var' => 'applyTime'
			),
			34 => array(
			'var' => 'bankId'
			),
			35 => array(
			'var' => 'bankName'
			),
			36 => array(
			'var' => 'bankBranch'
			),
			37 => array(
			'var' => 'bankAccountName'
			),
			38 => array(
			'var' => 'bankAccount'
			),
			39 => array(
			'var' => 'bankArea'
			),
			40 => array(
			'var' => 'backAddress'
			),
			41 => array(
			'var' => 'fixCarriage'
			),
			42 => array(
			'var' => 'returnPackageMoney'
			),
			43 => array(
			'var' => 'returnGiftCardType'
			),
			44 => array(
			'var' => 'giftCardToVirtualMoney'
			),
			45 => array(
			'var' => 'backFeeType'
			),
			46 => array(
			'var' => 'cardVirtualMoney'
			),
			47 => array(
			'var' => 'returnSubtotal'
			),
			48 => array(
			'var' => 'returnBankId'
			),
			49 => array(
			'var' => 'returnBankName'
			),
			50 => array(
			'var' => 'returnBankCard'
			),
			51 => array(
			'var' => 'returnUserName'
			),
			52 => array(
			'var' => 'merchantCode'
			),
			53 => array(
			'var' => 'pickupTime'
			),
			54 => array(
			'var' => 'isPremium'
			),
			55 => array(
			'var' => 'vendorAuditFlag'
			),
			56 => array(
			'var' => 'sourceType'
			),
			57 => array(
			'var' => 'returnWarehouse'
			),
			58 => array(
			'var' => 'operatorName'
			),
			59 => array(
			'var' => 'returnRefundType'
			),
			60 => array(
			'var' => 'returnRefundStatus'
			),
			61 => array(
			'var' => 'afterSaleSn'
			),
			62 => array(
			'var' => 'afterSaleStatus'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['applyId'])){
				
				$this->applyId = $vals['applyId'];
			}
			
			
			if (isset($vals['userId'])){
				
				$this->userId = $vals['userId'];
			}
			
			
			if (isset($vals['orderSn'])){
				
				$this->orderSn = $vals['orderSn'];
			}
			
			
			if (isset($vals['orderId'])){
				
				$this->orderId = $vals['orderId'];
			}
			
			
			if (isset($vals['returnReasonId'])){
				
				$this->returnReasonId = $vals['returnReasonId'];
			}
			
			
			if (isset($vals['returnReasonDetails'])){
				
				$this->returnReasonDetails = $vals['returnReasonDetails'];
			}
			
			
			if (isset($vals['resend'])){
				
				$this->resend = $vals['resend'];
			}
			
			
			if (isset($vals['returnGoodsTotalMoney'])){
				
				$this->returnGoodsTotalMoney = $vals['returnGoodsTotalMoney'];
			}
			
			
			if (isset($vals['returnGoodsTotalAmount'])){
				
				$this->returnGoodsTotalAmount = $vals['returnGoodsTotalAmount'];
			}
			
			
			if (isset($vals['returnCarriage'])){
				
				$this->returnCarriage = $vals['returnCarriage'];
			}
			
			
			if (isset($vals['reimburseBackCarriage'])){
				
				$this->reimburseBackCarriage = $vals['reimburseBackCarriage'];
			}
			
			
			if (isset($vals['returnCouponMoney'])){
				
				$this->returnCouponMoney = $vals['returnCouponMoney'];
			}
			
			
			if (isset($vals['returnMoney'])){
				
				$this->returnMoney = $vals['returnMoney'];
			}
			
			
			if (isset($vals['returnWalletMoney'])){
				
				$this->returnWalletMoney = $vals['returnWalletMoney'];
			}
			
			
			if (isset($vals['adjuctMoney'])){
				
				$this->adjuctMoney = $vals['adjuctMoney'];
			}
			
			
			if (isset($vals['actualBackCarriage'])){
				
				$this->actualBackCarriage = $vals['actualBackCarriage'];
			}
			
			
			if (isset($vals['refundType'])){
				
				$this->refundType = $vals['refundType'];
			}
			
			
			if (isset($vals['backTime'])){
				
				$this->backTime = $vals['backTime'];
			}
			
			
			if (isset($vals['orderReturnType'])){
				
				$this->orderReturnType = $vals['orderReturnType'];
			}
			
			
			if (isset($vals['auditStatus'])){
				
				$this->auditStatus = $vals['auditStatus'];
			}
			
			
			if (isset($vals['goodsBackWay'])){
				
				$this->goodsBackWay = $vals['goodsBackWay'];
			}
			
			
			if (isset($vals['returnsWayTime'])){
				
				$this->returnsWayTime = $vals['returnsWayTime'];
			}
			
			
			if (isset($vals['returnsAreaName'])){
				
				$this->returnsAreaName = $vals['returnsAreaName'];
			}
			
			
			if (isset($vals['returnsWayTimeInfo'])){
				
				$this->returnsWayTimeInfo = $vals['returnsWayTimeInfo'];
			}
			
			
			if (isset($vals['orderAfterStatus'])){
				
				$this->orderAfterStatus = $vals['orderAfterStatus'];
			}
			
			
			if (isset($vals['orderAfterStatusUpdtime'])){
				
				$this->orderAfterStatusUpdtime = $vals['orderAfterStatusUpdtime'];
			}
			
			
			if (isset($vals['remark'])){
				
				$this->remark = $vals['remark'];
			}
			
			
			if (isset($vals['backCarriagePayOnCollect'])){
				
				$this->backCarriagePayOnCollect = $vals['backCarriagePayOnCollect'];
			}
			
			
			if (isset($vals['returnVirtualMoney'])){
				
				$this->returnVirtualMoney = $vals['returnVirtualMoney'];
			}
			
			
			if (isset($vals['extra1'])){
				
				$this->extra1 = $vals['extra1'];
			}
			
			
			if (isset($vals['refundFlag'])){
				
				$this->refundFlag = $vals['refundFlag'];
			}
			
			
			if (isset($vals['skipAuditFlag'])){
				
				$this->skipAuditFlag = $vals['skipAuditFlag'];
			}
			
			
			if (isset($vals['applyTime'])){
				
				$this->applyTime = $vals['applyTime'];
			}
			
			
			if (isset($vals['bankId'])){
				
				$this->bankId = $vals['bankId'];
			}
			
			
			if (isset($vals['bankName'])){
				
				$this->bankName = $vals['bankName'];
			}
			
			
			if (isset($vals['bankBranch'])){
				
				$this->bankBranch = $vals['bankBranch'];
			}
			
			
			if (isset($vals['bankAccountName'])){
				
				$this->bankAccountName = $vals['bankAccountName'];
			}
			
			
			if (isset($vals['bankAccount'])){
				
				$this->bankAccount = $vals['bankAccount'];
			}
			
			
			if (isset($vals['bankArea'])){
				
				$this->bankArea = $vals['bankArea'];
			}
			
			
			if (isset($vals['backAddress'])){
				
				$this->backAddress = $vals['backAddress'];
			}
			
			
			if (isset($vals['fixCarriage'])){
				
				$this->fixCarriage = $vals['fixCarriage'];
			}
			
			
			if (isset($vals['returnPackageMoney'])){
				
				$this->returnPackageMoney = $vals['returnPackageMoney'];
			}
			
			
			if (isset($vals['returnGiftCardType'])){
				
				$this->returnGiftCardType = $vals['returnGiftCardType'];
			}
			
			
			if (isset($vals['giftCardToVirtualMoney'])){
				
				$this->giftCardToVirtualMoney = $vals['giftCardToVirtualMoney'];
			}
			
			
			if (isset($vals['backFeeType'])){
				
				$this->backFeeType = $vals['backFeeType'];
			}
			
			
			if (isset($vals['cardVirtualMoney'])){
				
				$this->cardVirtualMoney = $vals['cardVirtualMoney'];
			}
			
			
			if (isset($vals['returnSubtotal'])){
				
				$this->returnSubtotal = $vals['returnSubtotal'];
			}
			
			
			if (isset($vals['returnBankId'])){
				
				$this->returnBankId = $vals['returnBankId'];
			}
			
			
			if (isset($vals['returnBankName'])){
				
				$this->returnBankName = $vals['returnBankName'];
			}
			
			
			if (isset($vals['returnBankCard'])){
				
				$this->returnBankCard = $vals['returnBankCard'];
			}
			
			
			if (isset($vals['returnUserName'])){
				
				$this->returnUserName = $vals['returnUserName'];
			}
			
			
			if (isset($vals['merchantCode'])){
				
				$this->merchantCode = $vals['merchantCode'];
			}
			
			
			if (isset($vals['pickupTime'])){
				
				$this->pickupTime = $vals['pickupTime'];
			}
			
			
			if (isset($vals['isPremium'])){
				
				$this->isPremium = $vals['isPremium'];
			}
			
			
			if (isset($vals['vendorAuditFlag'])){
				
				$this->vendorAuditFlag = $vals['vendorAuditFlag'];
			}
			
			
			if (isset($vals['sourceType'])){
				
				$this->sourceType = $vals['sourceType'];
			}
			
			
			if (isset($vals['returnWarehouse'])){
				
				$this->returnWarehouse = $vals['returnWarehouse'];
			}
			
			
			if (isset($vals['operatorName'])){
				
				$this->operatorName = $vals['operatorName'];
			}
			
			
			if (isset($vals['returnRefundType'])){
				
				$this->returnRefundType = $vals['returnRefundType'];
			}
			
			
			if (isset($vals['returnRefundStatus'])){
				
				$this->returnRefundStatus = $vals['returnRefundStatus'];
			}
			
			
			if (isset($vals['afterSaleSn'])){
				
				$this->afterSaleSn = $vals['afterSaleSn'];
			}
			
			
			if (isset($vals['afterSaleStatus'])){
				
				$this->afterSaleStatus = $vals['afterSaleStatus'];
			}
			
			
		}
		
	}
	
	
	public function getName(){
		
		return 'OrderReturnApplyVO';
	}
	
	public function read($input){
		
		$input->readStructBegin();
		while(true){
			
			$schemeField = $input->readFieldBegin();
			if ($schemeField == null) break;
			$needSkip = true;
			
			
			if ("applyId" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->applyId); 
				
			}
			
			
			
			
			if ("userId" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->userId); 
				
			}
			
			
			
			
			if ("orderSn" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->orderSn);
				
			}
			
			
			
			
			if ("orderId" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->orderId); 
				
			}
			
			
			
			
			if ("returnReasonId" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->returnReasonId); 
				
			}
			
			
			
			
			if ("returnReasonDetails" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->returnReasonDetails);
				
			}
			
			
			
			
			if ("resend" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->resend); 
				
			}
			
			
			
			
			if ("returnGoodsTotalMoney" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->returnGoodsTotalMoney);
				
			}
			
			
			
			
			if ("returnGoodsTotalAmount" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->returnGoodsTotalAmount); 
				
			}
			
			
			
			
			if ("returnCarriage" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->returnCarriage);
				
			}
			
			
			
			
			if ("reimburseBackCarriage" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->reimburseBackCarriage);
				
			}
			
			
			
			
			if ("returnCouponMoney" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->returnCouponMoney);
				
			}
			
			
			
			
			if ("returnMoney" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->returnMoney);
				
			}
			
			
			
			
			if ("returnWalletMoney" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->returnWalletMoney);
				
			}
			
			
			
			
			if ("adjuctMoney" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->adjuctMoney);
				
			}
			
			
			
			
			if ("actualBackCarriage" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->actualBackCarriage);
				
			}
			
			
			
			
			if ("refundType" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->refundType); 
				
			}
			
			
			
			
			if ("backTime" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->backTime); 
				
			}
			
			
			
			
			if ("orderReturnType" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->orderReturnType); 
				
			}
			
			
			
			
			if ("auditStatus" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->auditStatus); 
				
			}
			
			
			
			
			if ("goodsBackWay" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->goodsBackWay); 
				
			}
			
			
			
			
			if ("returnsWayTime" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->returnsWayTime); 
				
			}
			
			
			
			
			if ("returnsAreaName" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->returnsAreaName);
				
			}
			
			
			
			
			if ("returnsWayTimeInfo" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->returnsWayTimeInfo);
				
			}
			
			
			
			
			if ("orderAfterStatus" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->orderAfterStatus); 
				
			}
			
			
			
			
			if ("orderAfterStatusUpdtime" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->orderAfterStatusUpdtime); 
				
			}
			
			
			
			
			if ("remark" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->remark);
				
			}
			
			
			
			
			if ("backCarriagePayOnCollect" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->backCarriagePayOnCollect); 
				
			}
			
			
			
			
			if ("returnVirtualMoney" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->returnVirtualMoney);
				
			}
			
			
			
			
			if ("extra1" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->extra1);
				
			}
			
			
			
			
			if ("refundFlag" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->refundFlag); 
				
			}
			
			
			
			
			if ("skipAuditFlag" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->skipAuditFlag); 
				
			}
			
			
			
			
			if ("applyTime" == $schemeField){
				
				$needSkip = false;
				$input->readI64($this->applyTime); 
				
			}
			
			
			
			
			if ("bankId" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->bankId);
				
			}
			
			
			
			
			if ("bankName" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->bankName);
				
			}
			
			
			
			
			if ("bankBranch" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->bankBranch);
				
			}
			
			
			
			
			if ("bankAccountName" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->bankAccountName);
				
			}
			
			
			
			
			if ("bankAccount" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->bankAccount);
				
			}
			
			
			
			
			if ("bankArea" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->bankArea);
				
			}
			
			
			
			
			if ("backAddress" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->backAddress);
				
			}
			
			
			
			
			if ("fixCarriage" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->fixCarriage);
				
			}
			
			
			
			
			if ("returnPackageMoney" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->returnPackageMoney);
				
			}
			
			
			
			
			if ("returnGiftCardType" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->returnGiftCardType); 
				
			}
			
			
			
			
			if ("giftCardToVirtualMoney" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->giftCardToVirtualMoney);
				
			}
			
			
			
			
			if ("backFeeType" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->backFeeType); 
				
			}
			
			
			
			
			if ("cardVirtualMoney" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->cardVirtualMoney);
				
			}
			
			
			
			
			if ("returnSubtotal" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->returnSubtotal);
				
			}
			
			
			
			
			if ("returnBankId" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->returnBankId);
				
			}
			
			
			
			
			if ("returnBankName" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->returnBankName);
				
			}
			
			
			
			
			if ("returnBankCard" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->returnBankCard);
				
			}
			
			
			
			
			if ("returnUserName" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->returnUserName);
				
			}
			
			
			
			
			if ("merchantCode" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->merchantCode);
				
			}
			
			
			
			
			if ("pickupTime" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->pickupTime);
				
			}
			
			
			
			
			if ("isPremium" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->isPremium); 
				
			}
			
			
			
			
			if ("vendorAuditFlag" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->vendorAuditFlag); 
				
			}
			
			
			
			
			if ("sourceType" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->sourceType); 
				
			}
			
			
			
			
			if ("returnWarehouse" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->returnWarehouse);
				
			}
			
			
			
			
			if ("operatorName" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->operatorName);
				
			}
			
			
			
			
			if ("returnRefundType" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->returnRefundType); 
				
			}
			
			
			
			
			if ("returnRefundStatus" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->returnRefundStatus); 
				
			}
			
			
			
			
			if ("afterSaleSn" == $schemeField){
				
				$needSkip = false;
				$input->readString($this->afterSaleSn);
				
			}
			
			
			
			
			if ("afterSaleStatus" == $schemeField){
				
				$needSkip = false;
				$input->readI32($this->afterSaleStatus); 
				
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
		
		if($this->applyId !== null) {
			
			$xfer += $output->writeFieldBegin('applyId');
			$xfer += $output->writeI64($this->applyId);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->userId !== null) {
			
			$xfer += $output->writeFieldBegin('userId');
			$xfer += $output->writeI64($this->userId);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderSn !== null) {
			
			$xfer += $output->writeFieldBegin('orderSn');
			$xfer += $output->writeString($this->orderSn);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderId !== null) {
			
			$xfer += $output->writeFieldBegin('orderId');
			$xfer += $output->writeI64($this->orderId);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->returnReasonId !== null) {
			
			$xfer += $output->writeFieldBegin('returnReasonId');
			$xfer += $output->writeI64($this->returnReasonId);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->returnReasonDetails !== null) {
			
			$xfer += $output->writeFieldBegin('returnReasonDetails');
			$xfer += $output->writeString($this->returnReasonDetails);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->resend !== null) {
			
			$xfer += $output->writeFieldBegin('resend');
			$xfer += $output->writeI32($this->resend);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->returnGoodsTotalMoney !== null) {
			
			$xfer += $output->writeFieldBegin('returnGoodsTotalMoney');
			$xfer += $output->writeString($this->returnGoodsTotalMoney);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->returnGoodsTotalAmount !== null) {
			
			$xfer += $output->writeFieldBegin('returnGoodsTotalAmount');
			$xfer += $output->writeI32($this->returnGoodsTotalAmount);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->returnCarriage !== null) {
			
			$xfer += $output->writeFieldBegin('returnCarriage');
			$xfer += $output->writeString($this->returnCarriage);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->reimburseBackCarriage !== null) {
			
			$xfer += $output->writeFieldBegin('reimburseBackCarriage');
			$xfer += $output->writeString($this->reimburseBackCarriage);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->returnCouponMoney !== null) {
			
			$xfer += $output->writeFieldBegin('returnCouponMoney');
			$xfer += $output->writeString($this->returnCouponMoney);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->returnMoney !== null) {
			
			$xfer += $output->writeFieldBegin('returnMoney');
			$xfer += $output->writeString($this->returnMoney);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->returnWalletMoney !== null) {
			
			$xfer += $output->writeFieldBegin('returnWalletMoney');
			$xfer += $output->writeString($this->returnWalletMoney);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->adjuctMoney !== null) {
			
			$xfer += $output->writeFieldBegin('adjuctMoney');
			$xfer += $output->writeString($this->adjuctMoney);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->actualBackCarriage !== null) {
			
			$xfer += $output->writeFieldBegin('actualBackCarriage');
			$xfer += $output->writeString($this->actualBackCarriage);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->refundType !== null) {
			
			$xfer += $output->writeFieldBegin('refundType');
			$xfer += $output->writeI32($this->refundType);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->backTime !== null) {
			
			$xfer += $output->writeFieldBegin('backTime');
			$xfer += $output->writeI64($this->backTime);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderReturnType !== null) {
			
			$xfer += $output->writeFieldBegin('orderReturnType');
			$xfer += $output->writeI32($this->orderReturnType);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->auditStatus !== null) {
			
			$xfer += $output->writeFieldBegin('auditStatus');
			$xfer += $output->writeI32($this->auditStatus);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->goodsBackWay !== null) {
			
			$xfer += $output->writeFieldBegin('goodsBackWay');
			$xfer += $output->writeI32($this->goodsBackWay);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->returnsWayTime !== null) {
			
			$xfer += $output->writeFieldBegin('returnsWayTime');
			$xfer += $output->writeI32($this->returnsWayTime);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->returnsAreaName !== null) {
			
			$xfer += $output->writeFieldBegin('returnsAreaName');
			$xfer += $output->writeString($this->returnsAreaName);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->returnsWayTimeInfo !== null) {
			
			$xfer += $output->writeFieldBegin('returnsWayTimeInfo');
			$xfer += $output->writeString($this->returnsWayTimeInfo);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderAfterStatus !== null) {
			
			$xfer += $output->writeFieldBegin('orderAfterStatus');
			$xfer += $output->writeI32($this->orderAfterStatus);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderAfterStatusUpdtime !== null) {
			
			$xfer += $output->writeFieldBegin('orderAfterStatusUpdtime');
			$xfer += $output->writeI64($this->orderAfterStatusUpdtime);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->remark !== null) {
			
			$xfer += $output->writeFieldBegin('remark');
			$xfer += $output->writeString($this->remark);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->backCarriagePayOnCollect !== null) {
			
			$xfer += $output->writeFieldBegin('backCarriagePayOnCollect');
			$xfer += $output->writeI32($this->backCarriagePayOnCollect);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->returnVirtualMoney !== null) {
			
			$xfer += $output->writeFieldBegin('returnVirtualMoney');
			$xfer += $output->writeString($this->returnVirtualMoney);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->extra1 !== null) {
			
			$xfer += $output->writeFieldBegin('extra1');
			$xfer += $output->writeString($this->extra1);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->refundFlag !== null) {
			
			$xfer += $output->writeFieldBegin('refundFlag');
			$xfer += $output->writeI32($this->refundFlag);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->skipAuditFlag !== null) {
			
			$xfer += $output->writeFieldBegin('skipAuditFlag');
			$xfer += $output->writeI32($this->skipAuditFlag);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->applyTime !== null) {
			
			$xfer += $output->writeFieldBegin('applyTime');
			$xfer += $output->writeI64($this->applyTime);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->bankId !== null) {
			
			$xfer += $output->writeFieldBegin('bankId');
			$xfer += $output->writeString($this->bankId);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->bankName !== null) {
			
			$xfer += $output->writeFieldBegin('bankName');
			$xfer += $output->writeString($this->bankName);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->bankBranch !== null) {
			
			$xfer += $output->writeFieldBegin('bankBranch');
			$xfer += $output->writeString($this->bankBranch);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->bankAccountName !== null) {
			
			$xfer += $output->writeFieldBegin('bankAccountName');
			$xfer += $output->writeString($this->bankAccountName);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->bankAccount !== null) {
			
			$xfer += $output->writeFieldBegin('bankAccount');
			$xfer += $output->writeString($this->bankAccount);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->bankArea !== null) {
			
			$xfer += $output->writeFieldBegin('bankArea');
			$xfer += $output->writeString($this->bankArea);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->backAddress !== null) {
			
			$xfer += $output->writeFieldBegin('backAddress');
			$xfer += $output->writeString($this->backAddress);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->fixCarriage !== null) {
			
			$xfer += $output->writeFieldBegin('fixCarriage');
			$xfer += $output->writeString($this->fixCarriage);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->returnPackageMoney !== null) {
			
			$xfer += $output->writeFieldBegin('returnPackageMoney');
			$xfer += $output->writeString($this->returnPackageMoney);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->returnGiftCardType !== null) {
			
			$xfer += $output->writeFieldBegin('returnGiftCardType');
			$xfer += $output->writeI32($this->returnGiftCardType);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->giftCardToVirtualMoney !== null) {
			
			$xfer += $output->writeFieldBegin('giftCardToVirtualMoney');
			$xfer += $output->writeString($this->giftCardToVirtualMoney);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->backFeeType !== null) {
			
			$xfer += $output->writeFieldBegin('backFeeType');
			$xfer += $output->writeI32($this->backFeeType);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->cardVirtualMoney !== null) {
			
			$xfer += $output->writeFieldBegin('cardVirtualMoney');
			$xfer += $output->writeString($this->cardVirtualMoney);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->returnSubtotal !== null) {
			
			$xfer += $output->writeFieldBegin('returnSubtotal');
			$xfer += $output->writeString($this->returnSubtotal);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->returnBankId !== null) {
			
			$xfer += $output->writeFieldBegin('returnBankId');
			$xfer += $output->writeString($this->returnBankId);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->returnBankName !== null) {
			
			$xfer += $output->writeFieldBegin('returnBankName');
			$xfer += $output->writeString($this->returnBankName);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->returnBankCard !== null) {
			
			$xfer += $output->writeFieldBegin('returnBankCard');
			$xfer += $output->writeString($this->returnBankCard);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->returnUserName !== null) {
			
			$xfer += $output->writeFieldBegin('returnUserName');
			$xfer += $output->writeString($this->returnUserName);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->merchantCode !== null) {
			
			$xfer += $output->writeFieldBegin('merchantCode');
			$xfer += $output->writeString($this->merchantCode);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->pickupTime !== null) {
			
			$xfer += $output->writeFieldBegin('pickupTime');
			$xfer += $output->writeString($this->pickupTime);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->isPremium !== null) {
			
			$xfer += $output->writeFieldBegin('isPremium');
			$xfer += $output->writeI32($this->isPremium);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->vendorAuditFlag !== null) {
			
			$xfer += $output->writeFieldBegin('vendorAuditFlag');
			$xfer += $output->writeI32($this->vendorAuditFlag);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->sourceType !== null) {
			
			$xfer += $output->writeFieldBegin('sourceType');
			$xfer += $output->writeI32($this->sourceType);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->returnWarehouse !== null) {
			
			$xfer += $output->writeFieldBegin('returnWarehouse');
			$xfer += $output->writeString($this->returnWarehouse);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->operatorName !== null) {
			
			$xfer += $output->writeFieldBegin('operatorName');
			$xfer += $output->writeString($this->operatorName);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->returnRefundType !== null) {
			
			$xfer += $output->writeFieldBegin('returnRefundType');
			$xfer += $output->writeI32($this->returnRefundType);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->returnRefundStatus !== null) {
			
			$xfer += $output->writeFieldBegin('returnRefundStatus');
			$xfer += $output->writeI32($this->returnRefundStatus);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->afterSaleSn !== null) {
			
			$xfer += $output->writeFieldBegin('afterSaleSn');
			$xfer += $output->writeString($this->afterSaleSn);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->afterSaleStatus !== null) {
			
			$xfer += $output->writeFieldBegin('afterSaleStatus');
			$xfer += $output->writeI32($this->afterSaleStatus);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}

?>