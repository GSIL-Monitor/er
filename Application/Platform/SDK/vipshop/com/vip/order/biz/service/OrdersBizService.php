<?php


/*
* Copyright (c) 2008-2016 vip.com, All Rights Reserved.
*
* Powered by com.vip.osp.osp-idlc-2.5.11.
*
*/

namespace com\vip\order\biz\service;
interface OrdersBizServiceIf{
	
	
	public function addOrderTransport(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\AddOrderTransportReq $addOrderTransportReq);
	
	public function autoPay(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\AutoPayReq $req);
	
	public function autoPayFail(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\AutoPayFailReq $req);
	
	public function autoTakeInventory(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\AutoTakeInventoryReq $req);
	
	public function b2cSupportSendSms(\com\vip\order\common\pojo\order\request\RequestHeader $header,\com\vip\order\biz\request\B2CSupportSendSmsReq $req);
	
	public function batchGetOrderActiveDetail(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\BatchGetOrderActiveDetailReq $batchGetOrderActiveDetailReq);
	
	public function batchGetOrderList(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\BatchGetOrderListReq $searchOrderReq,\com\vip\order\common\pojo\order\request\ResultFilter $resultFilter);
	
	public function batchGetOrderTransportList(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\BatchGetOrderTransportListReq $batchGetOrderTransportListReq,\com\vip\order\common\pojo\order\request\ResultFilter $resultFilter);
	
	public function batchModifyOrderInvoice(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\BatchModifyOrderInvoiceReq $batchModifyOrderInvoiceReq);
	
	public function batchUpdateWmsFlag(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\BatchUpdateWmsFlagReq $batchUpdateWmsFlagReq);
	
	public function calculateSplitOrderMoney(\com\vip\order\common\pojo\order\request\RequestHeader $header,\com\vip\order\biz\request\CalculateSplitOrderMoneyReq $req);
	
	public function cancelOFixData(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\CancelOrderFixDataReq $cancelOrderFixDataReq);
	
	public function cancelOrder(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\CancelOrderReq $reqParam,\com\vip\order\biz\request\CancelOrderPrivilegeReq $privParam);
	
	public function cancelPresellOrder(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\CancelOrderReq $reqParam,\com\vip\order\biz\request\CancelOrderPrivilegeReq $privParam);
	
	public function checkCashOnDelivery(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\CheckCashOnDeliveryReq $checkCashOnDeliveryReq);
	
	public function checkDeliveryFetchExchange(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\CheckDeliveryFetchExchangeReq $checkDeliveryFetchExchangeReq);
	
	public function checkDeliveryFetchReturn(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\CheckDeliveryFetchReturnReq $checkDeliveryFetchReturnReq);
	
	public function checkOrderReturnVendorAudit(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\CheckOrderReturnVendorAuditReq $checkOrderReturnVendorAuditReq);
	
	public function confirmDelivered(\com\vip\order\common\pojo\order\request\RequestHeader $header,\com\vip\order\biz\request\ConfirmDeliveredReq $req);
	
	public function confirmOrderGroupBuyResult(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\ConfirmOrderGroupBuyReq $req);
	
	public function createOrder(\com\vip\order\biz\request\RequestHeader $requestHeader, $orderCategory, $createOrderParam);
	
	public function createOrderElectronicInvoice(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\CreateOrderElectronicInvoiceReq $createOrderElectronicInvoiceReq);
	
	public function createOrderSnV2(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader, $warehouse, $number);
	
	public function createOrderV2(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader, $orderCategory, $createOrderParam);
	
	public function cscCancelBack(\com\vip\order\common\pojo\order\request\RequestHeader $header,\com\vip\order\biz\request\CSCCancelBackReq $req);
	
	public function displayOrder(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\DisplayOrderReq $req);
	
	public function getAfterSaleOpType(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\GetAfterSaleOpTypeReq $req);
	
	public function getCanAfterSaleOrderListByUserId(\com\vip\order\common\pojo\order\request\RequestHeader $header,\com\vip\order\biz\request\GetCanAfterSaleOrderListReq $req,\com\vip\order\common\pojo\order\request\PageResultFilter $pageResultFilter);
	
	public function getCanRefundOrderCount(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\GetCanRefundOrderCountReq $getCanRefundOrderCountReq);
	
	public function getCanRefundOrderList(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\GetCanRefundOrderListReq $getCanRefundOrderListReq);
	
	public function getEbsGoodsList(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\GetEbsGoodsListReq $getEbsGoodsListReq);
	
	public function getGoodsDispatchWarehouse(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\GetGoodsDispatchWarehouseReq $req);
	
	public function getLimitedOrderGoodsCount(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\GetLimitedOrderGoodsCountReq $getLimitedOrderGoodsCountReq);
	
	public function getMergeOrderList(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\GetMergeOrderReq $getMergeOrderReq);
	
	public function getOrderCounts(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\SearchOrderReq $searchOrderReq);
	
	public function getOrderCountsByUserId(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\GetOrderByUserIdReq $getOrderByUserIdReq);
	
	public function getOrderDeliveryBoxNum(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeaderParam, $orderSn);
	
	public function getOrderDetail(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\SearchOrderDetailReq $searchOrderDetailReq);
	
	public function getOrderElectronicInvoicesV2(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeaderParam,\com\vip\order\biz\request\SearchOrderElectronicInvoicesReq $searchOrderElectronicInvoiceParam,\com\vip\order\biz\request\ResultRequirement $resultRequirement);
	
	public function getOrderFav(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader, $orderSnList);
	
	public function getOrderGoodsCount(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeaderParam,\com\vip\order\biz\request\GetOrderGoodsReq $getOrderGoodsParam);
	
	public function getOrderGoodsList(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeaderParam,\com\vip\order\biz\request\GetOrderGoodsReq $getOrderGoodsParam,\com\vip\order\common\pojo\order\request\ResultFilter $resultFilter);
	
	public function getOrderInstalmentsList(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\GetOrderInstalmentsReq $req,\com\vip\order\common\pojo\order\request\ResultFilter $filter);
	
	public function getOrderInvoicesV2(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeaderParam,\com\vip\order\biz\request\SearchOrderInvoicesReq $searchOrderInvoiceParam);
	
	public function getOrderList(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\SearchOrderReq $searchOrderReq,\com\vip\order\common\pojo\order\request\ResultFilter $resultFilter);
	
	public function getOrderListByPosNo(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\GetOrderListByPosNoReq $req);
	
	public function getOrderListByUserId(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\GetOrderByUserIdReq $getOrderByUserIdReq,\com\vip\order\common\pojo\order\request\ResultFilter $resultFilter);
	
	public function getOrderLogs(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeaderParam,\com\vip\order\biz\request\SearchOrderLogsReq $searchOrderLogsParam,\com\vip\order\biz\request\requirement\GetOrderLogsRequirement $getOrderLogsRequirement);
	
	public function getOrderOpStatus(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\GetOrderOpStatusReq $getOrderOpStatusReq);
	
	public function getOrderPackageList(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\GetOrderPackageListReq $getPackageListReq);
	
	public function getOrderPayType(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\GetOrderPayTypeReq $getOrderPayTypeParam,\com\vip\order\common\pojo\order\request\ResultFilter $resultFilter);
	
	public function getOrderSnByExOrderSn(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader, $exOrderSns);
	
	public function getOrderTransport(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\GetOrderTransportReq $getOrderTransportReq);
	
	public function getOrderTransportDetail(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\GetOrderTransportDetailReq $getOrderTransportDetailReq);
	
	public function getOrderTransportListByCodes(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeaderParam,\com\vip\order\biz\request\GetTransportListByCodesReq $getTransportListByCodesParam);
	
	public function getOrdersBySizeId(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\GetOrdersBySizeIdReq $req,\com\vip\order\common\pojo\order\request\ResultFilter $filter);
	
	public function getPrepayOrderStatus(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\GetPrepayOrderStatusReq $req);
	
	public function getPrepayOrderUnpayMsg(\com\vip\order\common\pojo\order\request\RequestHeader $header,\com\vip\order\biz\request\GetPrepayOrderUnpayMsgReq $req);
	
	public function getRdc(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\GetRdcReq $getRdcReq);
	
	public function getRdcInvoice(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\GetRdcInvoiceReq $req);
	
	public function getReturnOrExchangeGoods(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\GetReturnOrExchangeGoodsReq $req);
	
	public function getSimpleOrderFlowFlag(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeaderParam,\com\vip\order\biz\request\GetSimpleOrderFlowFlagReq $getSimpleOrderFlowFlagParam);
	
	public function getUnpayOrderList(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeaderParam,\com\vip\order\biz\request\GetUnpayOrderReq $getUnpayOrderParam);
	
	public function getUserDeliveryAddress(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\GetUserDeliveryAddressReq $getUserDeliveryAddressReq,\com\vip\order\common\pojo\order\request\ResultFilter $resultFilter);
	
	public function getUserFirstOrder(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\GetUserFirstOrderReq $getUserFirstOrderReq);
	
	public function healthCheck();
	
	public function mergeOrder(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\MergeOrderReq $reqParam);
	
	public function modifyOrderConsignee(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\ModifyOrderConsigneeReq $modifyOrderConsigneeReq);
	
	public function modifyOrderElectronicInvoice(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\ModifyOrderElectronicInvoiceReq $modifyOrderElectronicInvoiceReq);
	
	public function modifyOrderGoods(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\ModifyOrderGoodsReq $orderGoodsReq);
	
	public function modifyOrderPayType(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\common\pojo\order\vo\ModifyPayTypeReq $ModifyPayTypeReq);
	
	public function modifyOrderShipped(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\ModifyOrderShippedReq $modifyOrderShippedReq);
	
	public function modifyPrepayOrderPayType(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\ModifyPrepayOrderPayTypeReq $modifyPrepayOrderPayTypeReq);
	
	public function notifyCustomsDeclarationFailed(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\NotifyCustomsDeclarationFailedReq $req);
	
	public function ofcEntranceGrayControl(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\OfcEntranceGrayControlReq $req);
	
	public function postOrderVMSMessage(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\PostOrderVMSMessageReq $postOrderVMSMessageReq);
	
	public function putIntoSplitQueue(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\PutIntoSplitQueueReq $putIntoSplitQueueReq);
	
	public function putKeyToRollbackQueue(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\PutKeyToRollbackQueueReq $req);
	
	public function receptionConfirmDelivered(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\ReceptionConfirmDeliveredReq $req);
	
	public function refundOrder(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\OrderRefundReq $orderRefundReq);
	
	public function releaseStock(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\ReleaseStockReq $releaseStockReq);
	
	public function rollbackOrder(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\RollbackOrderReq $rollbackOrderReq);
	
	public function searchOrderListByUserId(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\SearchOrderListByUserIdReq $getOrderHistoryReq,\com\vip\order\common\pojo\order\request\ResultFilter $resultFilter);
	
	public function signOrder(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\SignOrderReq $signOrderReq);
	
	public function triggerGroupByAuditOrder(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\GroupByOrderAuditReq $groupByOrderAuditReq);
	
	public function updateAutoPayAuth(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\UpdateAutoPayAuthReq $req);
	
	public function updateOrderPayResult(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\UpdateOrderPayResultReq $updateOrderPayResultReq);
	
	public function updateOrderToReturnVerified(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\UpdateOrderToReturnVerifiedReq $updateOrderToReturnVerifiedReq);
	
	public function updatePayTypeToCOD(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\UpdatePayTypeToCODReq $updatePayTypeToCODReq);
	
	public function updatePrePayToVerified(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\UpdatePrePayToVerifiedReq $updatePrePayToVerifiedReq);
	
	public function updateReservationState(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\UpdateReservationStateReq $req);
	
	public function userDeleteOrder(\com\vip\order\common\pojo\order\request\RequestHeader $header,\com\vip\order\biz\request\UserDeleteOrderReq $req);
	
	public function verifyStockAndGetPayableFlag(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\VerifyStockAndGetPayableFlagReq $req);
	
}

class _OrdersBizServiceClient extends \Osp\Base\OspStub implements \com\vip\order\biz\service\OrdersBizServiceIf{
	
	public function __construct(){
		
		parent::__construct("com.vip.order.biz.service.OrdersBizService", "1.7.2");
	}
	
	
	public function addOrderTransport(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\AddOrderTransportReq $addOrderTransportReq){
		
		$this->send_addOrderTransport( $requestHeader, $addOrderTransportReq);
		return $this->recv_addOrderTransport();
	}
	
	public function send_addOrderTransport(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\AddOrderTransportReq $addOrderTransportReq){
		
		$this->initInvocation("addOrderTransport");
		$args = new \com\vip\order\biz\service\OrdersBizService_addOrderTransport_args();
		
		$args->requestHeader = $requestHeader;
		
		$args->addOrderTransportReq = $addOrderTransportReq;
		
		$this->send_base($args);
	}
	
	public function recv_addOrderTransport(){
		
		$result = new \com\vip\order\biz\service\OrdersBizService_addOrderTransport_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function autoPay(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\AutoPayReq $req){
		
		$this->send_autoPay( $requestHeader, $req);
		return $this->recv_autoPay();
	}
	
	public function send_autoPay(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\AutoPayReq $req){
		
		$this->initInvocation("autoPay");
		$args = new \com\vip\order\biz\service\OrdersBizService_autoPay_args();
		
		$args->requestHeader = $requestHeader;
		
		$args->req = $req;
		
		$this->send_base($args);
	}
	
	public function recv_autoPay(){
		
		$result = new \com\vip\order\biz\service\OrdersBizService_autoPay_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function autoPayFail(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\AutoPayFailReq $req){
		
		$this->send_autoPayFail( $requestHeader, $req);
		return $this->recv_autoPayFail();
	}
	
	public function send_autoPayFail(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\AutoPayFailReq $req){
		
		$this->initInvocation("autoPayFail");
		$args = new \com\vip\order\biz\service\OrdersBizService_autoPayFail_args();
		
		$args->requestHeader = $requestHeader;
		
		$args->req = $req;
		
		$this->send_base($args);
	}
	
	public function recv_autoPayFail(){
		
		$result = new \com\vip\order\biz\service\OrdersBizService_autoPayFail_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function autoTakeInventory(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\AutoTakeInventoryReq $req){
		
		$this->send_autoTakeInventory( $requestHeader, $req);
		return $this->recv_autoTakeInventory();
	}
	
	public function send_autoTakeInventory(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\AutoTakeInventoryReq $req){
		
		$this->initInvocation("autoTakeInventory");
		$args = new \com\vip\order\biz\service\OrdersBizService_autoTakeInventory_args();
		
		$args->requestHeader = $requestHeader;
		
		$args->req = $req;
		
		$this->send_base($args);
	}
	
	public function recv_autoTakeInventory(){
		
		$result = new \com\vip\order\biz\service\OrdersBizService_autoTakeInventory_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function b2cSupportSendSms(\com\vip\order\common\pojo\order\request\RequestHeader $header,\com\vip\order\biz\request\B2CSupportSendSmsReq $req){
		
		$this->send_b2cSupportSendSms( $header, $req);
		return $this->recv_b2cSupportSendSms();
	}
	
	public function send_b2cSupportSendSms(\com\vip\order\common\pojo\order\request\RequestHeader $header,\com\vip\order\biz\request\B2CSupportSendSmsReq $req){
		
		$this->initInvocation("b2cSupportSendSms");
		$args = new \com\vip\order\biz\service\OrdersBizService_b2cSupportSendSms_args();
		
		$args->header = $header;
		
		$args->req = $req;
		
		$this->send_base($args);
	}
	
	public function recv_b2cSupportSendSms(){
		
		$result = new \com\vip\order\biz\service\OrdersBizService_b2cSupportSendSms_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function batchGetOrderActiveDetail(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\BatchGetOrderActiveDetailReq $batchGetOrderActiveDetailReq){
		
		$this->send_batchGetOrderActiveDetail( $requestHeader, $batchGetOrderActiveDetailReq);
		return $this->recv_batchGetOrderActiveDetail();
	}
	
	public function send_batchGetOrderActiveDetail(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\BatchGetOrderActiveDetailReq $batchGetOrderActiveDetailReq){
		
		$this->initInvocation("batchGetOrderActiveDetail");
		$args = new \com\vip\order\biz\service\OrdersBizService_batchGetOrderActiveDetail_args();
		
		$args->requestHeader = $requestHeader;
		
		$args->batchGetOrderActiveDetailReq = $batchGetOrderActiveDetailReq;
		
		$this->send_base($args);
	}
	
	public function recv_batchGetOrderActiveDetail(){
		
		$result = new \com\vip\order\biz\service\OrdersBizService_batchGetOrderActiveDetail_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function batchGetOrderList(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\BatchGetOrderListReq $searchOrderReq,\com\vip\order\common\pojo\order\request\ResultFilter $resultFilter){
		
		$this->send_batchGetOrderList( $requestHeader, $searchOrderReq, $resultFilter);
		return $this->recv_batchGetOrderList();
	}
	
	public function send_batchGetOrderList(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\BatchGetOrderListReq $searchOrderReq,\com\vip\order\common\pojo\order\request\ResultFilter $resultFilter){
		
		$this->initInvocation("batchGetOrderList");
		$args = new \com\vip\order\biz\service\OrdersBizService_batchGetOrderList_args();
		
		$args->requestHeader = $requestHeader;
		
		$args->searchOrderReq = $searchOrderReq;
		
		$args->resultFilter = $resultFilter;
		
		$this->send_base($args);
	}
	
	public function recv_batchGetOrderList(){
		
		$result = new \com\vip\order\biz\service\OrdersBizService_batchGetOrderList_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function batchGetOrderTransportList(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\BatchGetOrderTransportListReq $batchGetOrderTransportListReq,\com\vip\order\common\pojo\order\request\ResultFilter $resultFilter){
		
		$this->send_batchGetOrderTransportList( $requestHeader, $batchGetOrderTransportListReq, $resultFilter);
		return $this->recv_batchGetOrderTransportList();
	}
	
	public function send_batchGetOrderTransportList(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\BatchGetOrderTransportListReq $batchGetOrderTransportListReq,\com\vip\order\common\pojo\order\request\ResultFilter $resultFilter){
		
		$this->initInvocation("batchGetOrderTransportList");
		$args = new \com\vip\order\biz\service\OrdersBizService_batchGetOrderTransportList_args();
		
		$args->requestHeader = $requestHeader;
		
		$args->batchGetOrderTransportListReq = $batchGetOrderTransportListReq;
		
		$args->resultFilter = $resultFilter;
		
		$this->send_base($args);
	}
	
	public function recv_batchGetOrderTransportList(){
		
		$result = new \com\vip\order\biz\service\OrdersBizService_batchGetOrderTransportList_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function batchModifyOrderInvoice(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\BatchModifyOrderInvoiceReq $batchModifyOrderInvoiceReq){
		
		$this->send_batchModifyOrderInvoice( $requestHeader, $batchModifyOrderInvoiceReq);
		return $this->recv_batchModifyOrderInvoice();
	}
	
	public function send_batchModifyOrderInvoice(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\BatchModifyOrderInvoiceReq $batchModifyOrderInvoiceReq){
		
		$this->initInvocation("batchModifyOrderInvoice");
		$args = new \com\vip\order\biz\service\OrdersBizService_batchModifyOrderInvoice_args();
		
		$args->requestHeader = $requestHeader;
		
		$args->batchModifyOrderInvoiceReq = $batchModifyOrderInvoiceReq;
		
		$this->send_base($args);
	}
	
	public function recv_batchModifyOrderInvoice(){
		
		$result = new \com\vip\order\biz\service\OrdersBizService_batchModifyOrderInvoice_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function batchUpdateWmsFlag(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\BatchUpdateWmsFlagReq $batchUpdateWmsFlagReq){
		
		$this->send_batchUpdateWmsFlag( $requestHeader, $batchUpdateWmsFlagReq);
		return $this->recv_batchUpdateWmsFlag();
	}
	
	public function send_batchUpdateWmsFlag(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\BatchUpdateWmsFlagReq $batchUpdateWmsFlagReq){
		
		$this->initInvocation("batchUpdateWmsFlag");
		$args = new \com\vip\order\biz\service\OrdersBizService_batchUpdateWmsFlag_args();
		
		$args->requestHeader = $requestHeader;
		
		$args->batchUpdateWmsFlagReq = $batchUpdateWmsFlagReq;
		
		$this->send_base($args);
	}
	
	public function recv_batchUpdateWmsFlag(){
		
		$result = new \com\vip\order\biz\service\OrdersBizService_batchUpdateWmsFlag_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function calculateSplitOrderMoney(\com\vip\order\common\pojo\order\request\RequestHeader $header,\com\vip\order\biz\request\CalculateSplitOrderMoneyReq $req){
		
		$this->send_calculateSplitOrderMoney( $header, $req);
		return $this->recv_calculateSplitOrderMoney();
	}
	
	public function send_calculateSplitOrderMoney(\com\vip\order\common\pojo\order\request\RequestHeader $header,\com\vip\order\biz\request\CalculateSplitOrderMoneyReq $req){
		
		$this->initInvocation("calculateSplitOrderMoney");
		$args = new \com\vip\order\biz\service\OrdersBizService_calculateSplitOrderMoney_args();
		
		$args->header = $header;
		
		$args->req = $req;
		
		$this->send_base($args);
	}
	
	public function recv_calculateSplitOrderMoney(){
		
		$result = new \com\vip\order\biz\service\OrdersBizService_calculateSplitOrderMoney_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function cancelOFixData(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\CancelOrderFixDataReq $cancelOrderFixDataReq){
		
		$this->send_cancelOFixData( $requestHeader, $cancelOrderFixDataReq);
		return $this->recv_cancelOFixData();
	}
	
	public function send_cancelOFixData(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\CancelOrderFixDataReq $cancelOrderFixDataReq){
		
		$this->initInvocation("cancelOFixData");
		$args = new \com\vip\order\biz\service\OrdersBizService_cancelOFixData_args();
		
		$args->requestHeader = $requestHeader;
		
		$args->cancelOrderFixDataReq = $cancelOrderFixDataReq;
		
		$this->send_base($args);
	}
	
	public function recv_cancelOFixData(){
		
		$result = new \com\vip\order\biz\service\OrdersBizService_cancelOFixData_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function cancelOrder(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\CancelOrderReq $reqParam,\com\vip\order\biz\request\CancelOrderPrivilegeReq $privParam){
		
		$this->send_cancelOrder( $requestHeader, $reqParam, $privParam);
		return $this->recv_cancelOrder();
	}
	
	public function send_cancelOrder(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\CancelOrderReq $reqParam,\com\vip\order\biz\request\CancelOrderPrivilegeReq $privParam){
		
		$this->initInvocation("cancelOrder");
		$args = new \com\vip\order\biz\service\OrdersBizService_cancelOrder_args();
		
		$args->requestHeader = $requestHeader;
		
		$args->reqParam = $reqParam;
		
		$args->privParam = $privParam;
		
		$this->send_base($args);
	}
	
	public function recv_cancelOrder(){
		
		$result = new \com\vip\order\biz\service\OrdersBizService_cancelOrder_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function cancelPresellOrder(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\CancelOrderReq $reqParam,\com\vip\order\biz\request\CancelOrderPrivilegeReq $privParam){
		
		$this->send_cancelPresellOrder( $requestHeader, $reqParam, $privParam);
		return $this->recv_cancelPresellOrder();
	}
	
	public function send_cancelPresellOrder(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\CancelOrderReq $reqParam,\com\vip\order\biz\request\CancelOrderPrivilegeReq $privParam){
		
		$this->initInvocation("cancelPresellOrder");
		$args = new \com\vip\order\biz\service\OrdersBizService_cancelPresellOrder_args();
		
		$args->requestHeader = $requestHeader;
		
		$args->reqParam = $reqParam;
		
		$args->privParam = $privParam;
		
		$this->send_base($args);
	}
	
	public function recv_cancelPresellOrder(){
		
		$result = new \com\vip\order\biz\service\OrdersBizService_cancelPresellOrder_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function checkCashOnDelivery(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\CheckCashOnDeliveryReq $checkCashOnDeliveryReq){
		
		$this->send_checkCashOnDelivery( $requestHeader, $checkCashOnDeliveryReq);
		return $this->recv_checkCashOnDelivery();
	}
	
	public function send_checkCashOnDelivery(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\CheckCashOnDeliveryReq $checkCashOnDeliveryReq){
		
		$this->initInvocation("checkCashOnDelivery");
		$args = new \com\vip\order\biz\service\OrdersBizService_checkCashOnDelivery_args();
		
		$args->requestHeader = $requestHeader;
		
		$args->checkCashOnDeliveryReq = $checkCashOnDeliveryReq;
		
		$this->send_base($args);
	}
	
	public function recv_checkCashOnDelivery(){
		
		$result = new \com\vip\order\biz\service\OrdersBizService_checkCashOnDelivery_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function checkDeliveryFetchExchange(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\CheckDeliveryFetchExchangeReq $checkDeliveryFetchExchangeReq){
		
		$this->send_checkDeliveryFetchExchange( $requestHeader, $checkDeliveryFetchExchangeReq);
		return $this->recv_checkDeliveryFetchExchange();
	}
	
	public function send_checkDeliveryFetchExchange(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\CheckDeliveryFetchExchangeReq $checkDeliveryFetchExchangeReq){
		
		$this->initInvocation("checkDeliveryFetchExchange");
		$args = new \com\vip\order\biz\service\OrdersBizService_checkDeliveryFetchExchange_args();
		
		$args->requestHeader = $requestHeader;
		
		$args->checkDeliveryFetchExchangeReq = $checkDeliveryFetchExchangeReq;
		
		$this->send_base($args);
	}
	
	public function recv_checkDeliveryFetchExchange(){
		
		$result = new \com\vip\order\biz\service\OrdersBizService_checkDeliveryFetchExchange_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function checkDeliveryFetchReturn(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\CheckDeliveryFetchReturnReq $checkDeliveryFetchReturnReq){
		
		$this->send_checkDeliveryFetchReturn( $requestHeader, $checkDeliveryFetchReturnReq);
		return $this->recv_checkDeliveryFetchReturn();
	}
	
	public function send_checkDeliveryFetchReturn(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\CheckDeliveryFetchReturnReq $checkDeliveryFetchReturnReq){
		
		$this->initInvocation("checkDeliveryFetchReturn");
		$args = new \com\vip\order\biz\service\OrdersBizService_checkDeliveryFetchReturn_args();
		
		$args->requestHeader = $requestHeader;
		
		$args->checkDeliveryFetchReturnReq = $checkDeliveryFetchReturnReq;
		
		$this->send_base($args);
	}
	
	public function recv_checkDeliveryFetchReturn(){
		
		$result = new \com\vip\order\biz\service\OrdersBizService_checkDeliveryFetchReturn_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function checkOrderReturnVendorAudit(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\CheckOrderReturnVendorAuditReq $checkOrderReturnVendorAuditReq){
		
		$this->send_checkOrderReturnVendorAudit( $requestHeader, $checkOrderReturnVendorAuditReq);
		return $this->recv_checkOrderReturnVendorAudit();
	}
	
	public function send_checkOrderReturnVendorAudit(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\CheckOrderReturnVendorAuditReq $checkOrderReturnVendorAuditReq){
		
		$this->initInvocation("checkOrderReturnVendorAudit");
		$args = new \com\vip\order\biz\service\OrdersBizService_checkOrderReturnVendorAudit_args();
		
		$args->requestHeader = $requestHeader;
		
		$args->checkOrderReturnVendorAuditReq = $checkOrderReturnVendorAuditReq;
		
		$this->send_base($args);
	}
	
	public function recv_checkOrderReturnVendorAudit(){
		
		$result = new \com\vip\order\biz\service\OrdersBizService_checkOrderReturnVendorAudit_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function confirmDelivered(\com\vip\order\common\pojo\order\request\RequestHeader $header,\com\vip\order\biz\request\ConfirmDeliveredReq $req){
		
		$this->send_confirmDelivered( $header, $req);
		return $this->recv_confirmDelivered();
	}
	
	public function send_confirmDelivered(\com\vip\order\common\pojo\order\request\RequestHeader $header,\com\vip\order\biz\request\ConfirmDeliveredReq $req){
		
		$this->initInvocation("confirmDelivered");
		$args = new \com\vip\order\biz\service\OrdersBizService_confirmDelivered_args();
		
		$args->header = $header;
		
		$args->req = $req;
		
		$this->send_base($args);
	}
	
	public function recv_confirmDelivered(){
		
		$result = new \com\vip\order\biz\service\OrdersBizService_confirmDelivered_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function confirmOrderGroupBuyResult(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\ConfirmOrderGroupBuyReq $req){
		
		$this->send_confirmOrderGroupBuyResult( $requestHeader, $req);
		return $this->recv_confirmOrderGroupBuyResult();
	}
	
	public function send_confirmOrderGroupBuyResult(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\ConfirmOrderGroupBuyReq $req){
		
		$this->initInvocation("confirmOrderGroupBuyResult");
		$args = new \com\vip\order\biz\service\OrdersBizService_confirmOrderGroupBuyResult_args();
		
		$args->requestHeader = $requestHeader;
		
		$args->req = $req;
		
		$this->send_base($args);
	}
	
	public function recv_confirmOrderGroupBuyResult(){
		
		$result = new \com\vip\order\biz\service\OrdersBizService_confirmOrderGroupBuyResult_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function createOrder(\com\vip\order\biz\request\RequestHeader $requestHeader, $orderCategory, $createOrderParam){
		
		$this->send_createOrder( $requestHeader, $orderCategory, $createOrderParam);
		return $this->recv_createOrder();
	}
	
	public function send_createOrder(\com\vip\order\biz\request\RequestHeader $requestHeader, $orderCategory, $createOrderParam){
		
		$this->initInvocation("createOrder");
		$args = new \com\vip\order\biz\service\OrdersBizService_createOrder_args();
		
		$args->requestHeader = $requestHeader;
		
		$args->orderCategory = $orderCategory;
		
		$args->createOrderParam = $createOrderParam;
		
		$this->send_base($args);
	}
	
	public function recv_createOrder(){
		
		$result = new \com\vip\order\biz\service\OrdersBizService_createOrder_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function createOrderElectronicInvoice(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\CreateOrderElectronicInvoiceReq $createOrderElectronicInvoiceReq){
		
		$this->send_createOrderElectronicInvoice( $requestHeader, $createOrderElectronicInvoiceReq);
		return $this->recv_createOrderElectronicInvoice();
	}
	
	public function send_createOrderElectronicInvoice(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\CreateOrderElectronicInvoiceReq $createOrderElectronicInvoiceReq){
		
		$this->initInvocation("createOrderElectronicInvoice");
		$args = new \com\vip\order\biz\service\OrdersBizService_createOrderElectronicInvoice_args();
		
		$args->requestHeader = $requestHeader;
		
		$args->createOrderElectronicInvoiceReq = $createOrderElectronicInvoiceReq;
		
		$this->send_base($args);
	}
	
	public function recv_createOrderElectronicInvoice(){
		
		$result = new \com\vip\order\biz\service\OrdersBizService_createOrderElectronicInvoice_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function createOrderSnV2(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader, $warehouse, $number){
		
		$this->send_createOrderSnV2( $requestHeader, $warehouse, $number);
		return $this->recv_createOrderSnV2();
	}
	
	public function send_createOrderSnV2(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader, $warehouse, $number){
		
		$this->initInvocation("createOrderSnV2");
		$args = new \com\vip\order\biz\service\OrdersBizService_createOrderSnV2_args();
		
		$args->requestHeader = $requestHeader;
		
		$args->warehouse = $warehouse;
		
		$args->number = $number;
		
		$this->send_base($args);
	}
	
	public function recv_createOrderSnV2(){
		
		$result = new \com\vip\order\biz\service\OrdersBizService_createOrderSnV2_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function createOrderV2(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader, $orderCategory, $createOrderParam){
		
		$this->send_createOrderV2( $requestHeader, $orderCategory, $createOrderParam);
		return $this->recv_createOrderV2();
	}
	
	public function send_createOrderV2(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader, $orderCategory, $createOrderParam){
		
		$this->initInvocation("createOrderV2");
		$args = new \com\vip\order\biz\service\OrdersBizService_createOrderV2_args();
		
		$args->requestHeader = $requestHeader;
		
		$args->orderCategory = $orderCategory;
		
		$args->createOrderParam = $createOrderParam;
		
		$this->send_base($args);
	}
	
	public function recv_createOrderV2(){
		
		$result = new \com\vip\order\biz\service\OrdersBizService_createOrderV2_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function cscCancelBack(\com\vip\order\common\pojo\order\request\RequestHeader $header,\com\vip\order\biz\request\CSCCancelBackReq $req){
		
		$this->send_cscCancelBack( $header, $req);
		return $this->recv_cscCancelBack();
	}
	
	public function send_cscCancelBack(\com\vip\order\common\pojo\order\request\RequestHeader $header,\com\vip\order\biz\request\CSCCancelBackReq $req){
		
		$this->initInvocation("cscCancelBack");
		$args = new \com\vip\order\biz\service\OrdersBizService_cscCancelBack_args();
		
		$args->header = $header;
		
		$args->req = $req;
		
		$this->send_base($args);
	}
	
	public function recv_cscCancelBack(){
		
		$result = new \com\vip\order\biz\service\OrdersBizService_cscCancelBack_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function displayOrder(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\DisplayOrderReq $req){
		
		$this->send_displayOrder( $requestHeader, $req);
		return $this->recv_displayOrder();
	}
	
	public function send_displayOrder(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\DisplayOrderReq $req){
		
		$this->initInvocation("displayOrder");
		$args = new \com\vip\order\biz\service\OrdersBizService_displayOrder_args();
		
		$args->requestHeader = $requestHeader;
		
		$args->req = $req;
		
		$this->send_base($args);
	}
	
	public function recv_displayOrder(){
		
		$result = new \com\vip\order\biz\service\OrdersBizService_displayOrder_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function getAfterSaleOpType(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\GetAfterSaleOpTypeReq $req){
		
		$this->send_getAfterSaleOpType( $requestHeader, $req);
		return $this->recv_getAfterSaleOpType();
	}
	
	public function send_getAfterSaleOpType(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\GetAfterSaleOpTypeReq $req){
		
		$this->initInvocation("getAfterSaleOpType");
		$args = new \com\vip\order\biz\service\OrdersBizService_getAfterSaleOpType_args();
		
		$args->requestHeader = $requestHeader;
		
		$args->req = $req;
		
		$this->send_base($args);
	}
	
	public function recv_getAfterSaleOpType(){
		
		$result = new \com\vip\order\biz\service\OrdersBizService_getAfterSaleOpType_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function getCanAfterSaleOrderListByUserId(\com\vip\order\common\pojo\order\request\RequestHeader $header,\com\vip\order\biz\request\GetCanAfterSaleOrderListReq $req,\com\vip\order\common\pojo\order\request\PageResultFilter $pageResultFilter){
		
		$this->send_getCanAfterSaleOrderListByUserId( $header, $req, $pageResultFilter);
		return $this->recv_getCanAfterSaleOrderListByUserId();
	}
	
	public function send_getCanAfterSaleOrderListByUserId(\com\vip\order\common\pojo\order\request\RequestHeader $header,\com\vip\order\biz\request\GetCanAfterSaleOrderListReq $req,\com\vip\order\common\pojo\order\request\PageResultFilter $pageResultFilter){
		
		$this->initInvocation("getCanAfterSaleOrderListByUserId");
		$args = new \com\vip\order\biz\service\OrdersBizService_getCanAfterSaleOrderListByUserId_args();
		
		$args->header = $header;
		
		$args->req = $req;
		
		$args->pageResultFilter = $pageResultFilter;
		
		$this->send_base($args);
	}
	
	public function recv_getCanAfterSaleOrderListByUserId(){
		
		$result = new \com\vip\order\biz\service\OrdersBizService_getCanAfterSaleOrderListByUserId_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function getCanRefundOrderCount(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\GetCanRefundOrderCountReq $getCanRefundOrderCountReq){
		
		$this->send_getCanRefundOrderCount( $requestHeader, $getCanRefundOrderCountReq);
		return $this->recv_getCanRefundOrderCount();
	}
	
	public function send_getCanRefundOrderCount(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\GetCanRefundOrderCountReq $getCanRefundOrderCountReq){
		
		$this->initInvocation("getCanRefundOrderCount");
		$args = new \com\vip\order\biz\service\OrdersBizService_getCanRefundOrderCount_args();
		
		$args->requestHeader = $requestHeader;
		
		$args->getCanRefundOrderCountReq = $getCanRefundOrderCountReq;
		
		$this->send_base($args);
	}
	
	public function recv_getCanRefundOrderCount(){
		
		$result = new \com\vip\order\biz\service\OrdersBizService_getCanRefundOrderCount_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function getCanRefundOrderList(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\GetCanRefundOrderListReq $getCanRefundOrderListReq){
		
		$this->send_getCanRefundOrderList( $requestHeader, $getCanRefundOrderListReq);
		return $this->recv_getCanRefundOrderList();
	}
	
	public function send_getCanRefundOrderList(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\GetCanRefundOrderListReq $getCanRefundOrderListReq){
		
		$this->initInvocation("getCanRefundOrderList");
		$args = new \com\vip\order\biz\service\OrdersBizService_getCanRefundOrderList_args();
		
		$args->requestHeader = $requestHeader;
		
		$args->getCanRefundOrderListReq = $getCanRefundOrderListReq;
		
		$this->send_base($args);
	}
	
	public function recv_getCanRefundOrderList(){
		
		$result = new \com\vip\order\biz\service\OrdersBizService_getCanRefundOrderList_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function getEbsGoodsList(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\GetEbsGoodsListReq $getEbsGoodsListReq){
		
		$this->send_getEbsGoodsList( $requestHeader, $getEbsGoodsListReq);
		return $this->recv_getEbsGoodsList();
	}
	
	public function send_getEbsGoodsList(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\GetEbsGoodsListReq $getEbsGoodsListReq){
		
		$this->initInvocation("getEbsGoodsList");
		$args = new \com\vip\order\biz\service\OrdersBizService_getEbsGoodsList_args();
		
		$args->requestHeader = $requestHeader;
		
		$args->getEbsGoodsListReq = $getEbsGoodsListReq;
		
		$this->send_base($args);
	}
	
	public function recv_getEbsGoodsList(){
		
		$result = new \com\vip\order\biz\service\OrdersBizService_getEbsGoodsList_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function getGoodsDispatchWarehouse(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\GetGoodsDispatchWarehouseReq $req){
		
		$this->send_getGoodsDispatchWarehouse( $requestHeader, $req);
		return $this->recv_getGoodsDispatchWarehouse();
	}
	
	public function send_getGoodsDispatchWarehouse(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\GetGoodsDispatchWarehouseReq $req){
		
		$this->initInvocation("getGoodsDispatchWarehouse");
		$args = new \com\vip\order\biz\service\OrdersBizService_getGoodsDispatchWarehouse_args();
		
		$args->requestHeader = $requestHeader;
		
		$args->req = $req;
		
		$this->send_base($args);
	}
	
	public function recv_getGoodsDispatchWarehouse(){
		
		$result = new \com\vip\order\biz\service\OrdersBizService_getGoodsDispatchWarehouse_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function getLimitedOrderGoodsCount(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\GetLimitedOrderGoodsCountReq $getLimitedOrderGoodsCountReq){
		
		$this->send_getLimitedOrderGoodsCount( $requestHeader, $getLimitedOrderGoodsCountReq);
		return $this->recv_getLimitedOrderGoodsCount();
	}
	
	public function send_getLimitedOrderGoodsCount(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\GetLimitedOrderGoodsCountReq $getLimitedOrderGoodsCountReq){
		
		$this->initInvocation("getLimitedOrderGoodsCount");
		$args = new \com\vip\order\biz\service\OrdersBizService_getLimitedOrderGoodsCount_args();
		
		$args->requestHeader = $requestHeader;
		
		$args->getLimitedOrderGoodsCountReq = $getLimitedOrderGoodsCountReq;
		
		$this->send_base($args);
	}
	
	public function recv_getLimitedOrderGoodsCount(){
		
		$result = new \com\vip\order\biz\service\OrdersBizService_getLimitedOrderGoodsCount_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function getMergeOrderList(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\GetMergeOrderReq $getMergeOrderReq){
		
		$this->send_getMergeOrderList( $requestHeader, $getMergeOrderReq);
		return $this->recv_getMergeOrderList();
	}
	
	public function send_getMergeOrderList(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\GetMergeOrderReq $getMergeOrderReq){
		
		$this->initInvocation("getMergeOrderList");
		$args = new \com\vip\order\biz\service\OrdersBizService_getMergeOrderList_args();
		
		$args->requestHeader = $requestHeader;
		
		$args->getMergeOrderReq = $getMergeOrderReq;
		
		$this->send_base($args);
	}
	
	public function recv_getMergeOrderList(){
		
		$result = new \com\vip\order\biz\service\OrdersBizService_getMergeOrderList_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function getOrderCounts(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\SearchOrderReq $searchOrderReq){
		
		$this->send_getOrderCounts( $requestHeader, $searchOrderReq);
		return $this->recv_getOrderCounts();
	}
	
	public function send_getOrderCounts(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\SearchOrderReq $searchOrderReq){
		
		$this->initInvocation("getOrderCounts");
		$args = new \com\vip\order\biz\service\OrdersBizService_getOrderCounts_args();
		
		$args->requestHeader = $requestHeader;
		
		$args->searchOrderReq = $searchOrderReq;
		
		$this->send_base($args);
	}
	
	public function recv_getOrderCounts(){
		
		$result = new \com\vip\order\biz\service\OrdersBizService_getOrderCounts_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function getOrderCountsByUserId(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\GetOrderByUserIdReq $getOrderByUserIdReq){
		
		$this->send_getOrderCountsByUserId( $requestHeader, $getOrderByUserIdReq);
		return $this->recv_getOrderCountsByUserId();
	}
	
	public function send_getOrderCountsByUserId(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\GetOrderByUserIdReq $getOrderByUserIdReq){
		
		$this->initInvocation("getOrderCountsByUserId");
		$args = new \com\vip\order\biz\service\OrdersBizService_getOrderCountsByUserId_args();
		
		$args->requestHeader = $requestHeader;
		
		$args->getOrderByUserIdReq = $getOrderByUserIdReq;
		
		$this->send_base($args);
	}
	
	public function recv_getOrderCountsByUserId(){
		
		$result = new \com\vip\order\biz\service\OrdersBizService_getOrderCountsByUserId_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function getOrderDeliveryBoxNum(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeaderParam, $orderSn){
		
		$this->send_getOrderDeliveryBoxNum( $requestHeaderParam, $orderSn);
		return $this->recv_getOrderDeliveryBoxNum();
	}
	
	public function send_getOrderDeliveryBoxNum(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeaderParam, $orderSn){
		
		$this->initInvocation("getOrderDeliveryBoxNum");
		$args = new \com\vip\order\biz\service\OrdersBizService_getOrderDeliveryBoxNum_args();
		
		$args->requestHeaderParam = $requestHeaderParam;
		
		$args->orderSn = $orderSn;
		
		$this->send_base($args);
	}
	
	public function recv_getOrderDeliveryBoxNum(){
		
		$result = new \com\vip\order\biz\service\OrdersBizService_getOrderDeliveryBoxNum_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function getOrderDetail(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\SearchOrderDetailReq $searchOrderDetailReq){
		
		$this->send_getOrderDetail( $requestHeader, $searchOrderDetailReq);
		return $this->recv_getOrderDetail();
	}
	
	public function send_getOrderDetail(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\SearchOrderDetailReq $searchOrderDetailReq){
		
		$this->initInvocation("getOrderDetail");
		$args = new \com\vip\order\biz\service\OrdersBizService_getOrderDetail_args();
		
		$args->requestHeader = $requestHeader;
		
		$args->searchOrderDetailReq = $searchOrderDetailReq;
		
		$this->send_base($args);
	}
	
	public function recv_getOrderDetail(){
		
		$result = new \com\vip\order\biz\service\OrdersBizService_getOrderDetail_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function getOrderElectronicInvoicesV2(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeaderParam,\com\vip\order\biz\request\SearchOrderElectronicInvoicesReq $searchOrderElectronicInvoiceParam,\com\vip\order\biz\request\ResultRequirement $resultRequirement){
		
		$this->send_getOrderElectronicInvoicesV2( $requestHeaderParam, $searchOrderElectronicInvoiceParam, $resultRequirement);
		return $this->recv_getOrderElectronicInvoicesV2();
	}
	
	public function send_getOrderElectronicInvoicesV2(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeaderParam,\com\vip\order\biz\request\SearchOrderElectronicInvoicesReq $searchOrderElectronicInvoiceParam,\com\vip\order\biz\request\ResultRequirement $resultRequirement){
		
		$this->initInvocation("getOrderElectronicInvoicesV2");
		$args = new \com\vip\order\biz\service\OrdersBizService_getOrderElectronicInvoicesV2_args();
		
		$args->requestHeaderParam = $requestHeaderParam;
		
		$args->searchOrderElectronicInvoiceParam = $searchOrderElectronicInvoiceParam;
		
		$args->resultRequirement = $resultRequirement;
		
		$this->send_base($args);
	}
	
	public function recv_getOrderElectronicInvoicesV2(){
		
		$result = new \com\vip\order\biz\service\OrdersBizService_getOrderElectronicInvoicesV2_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function getOrderFav(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader, $orderSnList){
		
		$this->send_getOrderFav( $requestHeader, $orderSnList);
		return $this->recv_getOrderFav();
	}
	
	public function send_getOrderFav(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader, $orderSnList){
		
		$this->initInvocation("getOrderFav");
		$args = new \com\vip\order\biz\service\OrdersBizService_getOrderFav_args();
		
		$args->requestHeader = $requestHeader;
		
		$args->orderSnList = $orderSnList;
		
		$this->send_base($args);
	}
	
	public function recv_getOrderFav(){
		
		$result = new \com\vip\order\biz\service\OrdersBizService_getOrderFav_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function getOrderGoodsCount(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeaderParam,\com\vip\order\biz\request\GetOrderGoodsReq $getOrderGoodsParam){
		
		$this->send_getOrderGoodsCount( $requestHeaderParam, $getOrderGoodsParam);
		return $this->recv_getOrderGoodsCount();
	}
	
	public function send_getOrderGoodsCount(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeaderParam,\com\vip\order\biz\request\GetOrderGoodsReq $getOrderGoodsParam){
		
		$this->initInvocation("getOrderGoodsCount");
		$args = new \com\vip\order\biz\service\OrdersBizService_getOrderGoodsCount_args();
		
		$args->requestHeaderParam = $requestHeaderParam;
		
		$args->getOrderGoodsParam = $getOrderGoodsParam;
		
		$this->send_base($args);
	}
	
	public function recv_getOrderGoodsCount(){
		
		$result = new \com\vip\order\biz\service\OrdersBizService_getOrderGoodsCount_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function getOrderGoodsList(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeaderParam,\com\vip\order\biz\request\GetOrderGoodsReq $getOrderGoodsParam,\com\vip\order\common\pojo\order\request\ResultFilter $resultFilter){
		
		$this->send_getOrderGoodsList( $requestHeaderParam, $getOrderGoodsParam, $resultFilter);
		return $this->recv_getOrderGoodsList();
	}
	
	public function send_getOrderGoodsList(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeaderParam,\com\vip\order\biz\request\GetOrderGoodsReq $getOrderGoodsParam,\com\vip\order\common\pojo\order\request\ResultFilter $resultFilter){
		
		$this->initInvocation("getOrderGoodsList");
		$args = new \com\vip\order\biz\service\OrdersBizService_getOrderGoodsList_args();
		
		$args->requestHeaderParam = $requestHeaderParam;
		
		$args->getOrderGoodsParam = $getOrderGoodsParam;
		
		$args->resultFilter = $resultFilter;
		
		$this->send_base($args);
	}
	
	public function recv_getOrderGoodsList(){
		
		$result = new \com\vip\order\biz\service\OrdersBizService_getOrderGoodsList_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function getOrderInstalmentsList(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\GetOrderInstalmentsReq $req,\com\vip\order\common\pojo\order\request\ResultFilter $filter){
		
		$this->send_getOrderInstalmentsList( $requestHeader, $req, $filter);
		return $this->recv_getOrderInstalmentsList();
	}
	
	public function send_getOrderInstalmentsList(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\GetOrderInstalmentsReq $req,\com\vip\order\common\pojo\order\request\ResultFilter $filter){
		
		$this->initInvocation("getOrderInstalmentsList");
		$args = new \com\vip\order\biz\service\OrdersBizService_getOrderInstalmentsList_args();
		
		$args->requestHeader = $requestHeader;
		
		$args->req = $req;
		
		$args->filter = $filter;
		
		$this->send_base($args);
	}
	
	public function recv_getOrderInstalmentsList(){
		
		$result = new \com\vip\order\biz\service\OrdersBizService_getOrderInstalmentsList_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function getOrderInvoicesV2(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeaderParam,\com\vip\order\biz\request\SearchOrderInvoicesReq $searchOrderInvoiceParam){
		
		$this->send_getOrderInvoicesV2( $requestHeaderParam, $searchOrderInvoiceParam);
		return $this->recv_getOrderInvoicesV2();
	}
	
	public function send_getOrderInvoicesV2(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeaderParam,\com\vip\order\biz\request\SearchOrderInvoicesReq $searchOrderInvoiceParam){
		
		$this->initInvocation("getOrderInvoicesV2");
		$args = new \com\vip\order\biz\service\OrdersBizService_getOrderInvoicesV2_args();
		
		$args->requestHeaderParam = $requestHeaderParam;
		
		$args->searchOrderInvoiceParam = $searchOrderInvoiceParam;
		
		$this->send_base($args);
	}
	
	public function recv_getOrderInvoicesV2(){
		
		$result = new \com\vip\order\biz\service\OrdersBizService_getOrderInvoicesV2_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function getOrderList(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\SearchOrderReq $searchOrderReq,\com\vip\order\common\pojo\order\request\ResultFilter $resultFilter){
		
		$this->send_getOrderList( $requestHeader, $searchOrderReq, $resultFilter);
		return $this->recv_getOrderList();
	}
	
	public function send_getOrderList(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\SearchOrderReq $searchOrderReq,\com\vip\order\common\pojo\order\request\ResultFilter $resultFilter){
		
		$this->initInvocation("getOrderList");
		$args = new \com\vip\order\biz\service\OrdersBizService_getOrderList_args();
		
		$args->requestHeader = $requestHeader;
		
		$args->searchOrderReq = $searchOrderReq;
		
		$args->resultFilter = $resultFilter;
		
		$this->send_base($args);
	}
	
	public function recv_getOrderList(){
		
		$result = new \com\vip\order\biz\service\OrdersBizService_getOrderList_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function getOrderListByPosNo(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\GetOrderListByPosNoReq $req){
		
		$this->send_getOrderListByPosNo( $requestHeader, $req);
		return $this->recv_getOrderListByPosNo();
	}
	
	public function send_getOrderListByPosNo(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\GetOrderListByPosNoReq $req){
		
		$this->initInvocation("getOrderListByPosNo");
		$args = new \com\vip\order\biz\service\OrdersBizService_getOrderListByPosNo_args();
		
		$args->requestHeader = $requestHeader;
		
		$args->req = $req;
		
		$this->send_base($args);
	}
	
	public function recv_getOrderListByPosNo(){
		
		$result = new \com\vip\order\biz\service\OrdersBizService_getOrderListByPosNo_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function getOrderListByUserId(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\GetOrderByUserIdReq $getOrderByUserIdReq,\com\vip\order\common\pojo\order\request\ResultFilter $resultFilter){
		
		$this->send_getOrderListByUserId( $requestHeader, $getOrderByUserIdReq, $resultFilter);
		return $this->recv_getOrderListByUserId();
	}
	
	public function send_getOrderListByUserId(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\GetOrderByUserIdReq $getOrderByUserIdReq,\com\vip\order\common\pojo\order\request\ResultFilter $resultFilter){
		
		$this->initInvocation("getOrderListByUserId");
		$args = new \com\vip\order\biz\service\OrdersBizService_getOrderListByUserId_args();
		
		$args->requestHeader = $requestHeader;
		
		$args->getOrderByUserIdReq = $getOrderByUserIdReq;
		
		$args->resultFilter = $resultFilter;
		
		$this->send_base($args);
	}
	
	public function recv_getOrderListByUserId(){
		
		$result = new \com\vip\order\biz\service\OrdersBizService_getOrderListByUserId_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function getOrderLogs(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeaderParam,\com\vip\order\biz\request\SearchOrderLogsReq $searchOrderLogsParam,\com\vip\order\biz\request\requirement\GetOrderLogsRequirement $getOrderLogsRequirement){
		
		$this->send_getOrderLogs( $requestHeaderParam, $searchOrderLogsParam, $getOrderLogsRequirement);
		return $this->recv_getOrderLogs();
	}
	
	public function send_getOrderLogs(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeaderParam,\com\vip\order\biz\request\SearchOrderLogsReq $searchOrderLogsParam,\com\vip\order\biz\request\requirement\GetOrderLogsRequirement $getOrderLogsRequirement){
		
		$this->initInvocation("getOrderLogs");
		$args = new \com\vip\order\biz\service\OrdersBizService_getOrderLogs_args();
		
		$args->requestHeaderParam = $requestHeaderParam;
		
		$args->searchOrderLogsParam = $searchOrderLogsParam;
		
		$args->getOrderLogsRequirement = $getOrderLogsRequirement;
		
		$this->send_base($args);
	}
	
	public function recv_getOrderLogs(){
		
		$result = new \com\vip\order\biz\service\OrdersBizService_getOrderLogs_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function getOrderOpStatus(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\GetOrderOpStatusReq $getOrderOpStatusReq){
		
		$this->send_getOrderOpStatus( $requestHeader, $getOrderOpStatusReq);
		return $this->recv_getOrderOpStatus();
	}
	
	public function send_getOrderOpStatus(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\GetOrderOpStatusReq $getOrderOpStatusReq){
		
		$this->initInvocation("getOrderOpStatus");
		$args = new \com\vip\order\biz\service\OrdersBizService_getOrderOpStatus_args();
		
		$args->requestHeader = $requestHeader;
		
		$args->getOrderOpStatusReq = $getOrderOpStatusReq;
		
		$this->send_base($args);
	}
	
	public function recv_getOrderOpStatus(){
		
		$result = new \com\vip\order\biz\service\OrdersBizService_getOrderOpStatus_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function getOrderPackageList(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\GetOrderPackageListReq $getPackageListReq){
		
		$this->send_getOrderPackageList( $requestHeader, $getPackageListReq);
		return $this->recv_getOrderPackageList();
	}
	
	public function send_getOrderPackageList(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\GetOrderPackageListReq $getPackageListReq){
		
		$this->initInvocation("getOrderPackageList");
		$args = new \com\vip\order\biz\service\OrdersBizService_getOrderPackageList_args();
		
		$args->requestHeader = $requestHeader;
		
		$args->getPackageListReq = $getPackageListReq;
		
		$this->send_base($args);
	}
	
	public function recv_getOrderPackageList(){
		
		$result = new \com\vip\order\biz\service\OrdersBizService_getOrderPackageList_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function getOrderPayType(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\GetOrderPayTypeReq $getOrderPayTypeParam,\com\vip\order\common\pojo\order\request\ResultFilter $resultFilter){
		
		$this->send_getOrderPayType( $requestHeader, $getOrderPayTypeParam, $resultFilter);
		return $this->recv_getOrderPayType();
	}
	
	public function send_getOrderPayType(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\GetOrderPayTypeReq $getOrderPayTypeParam,\com\vip\order\common\pojo\order\request\ResultFilter $resultFilter){
		
		$this->initInvocation("getOrderPayType");
		$args = new \com\vip\order\biz\service\OrdersBizService_getOrderPayType_args();
		
		$args->requestHeader = $requestHeader;
		
		$args->getOrderPayTypeParam = $getOrderPayTypeParam;
		
		$args->resultFilter = $resultFilter;
		
		$this->send_base($args);
	}
	
	public function recv_getOrderPayType(){
		
		$result = new \com\vip\order\biz\service\OrdersBizService_getOrderPayType_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function getOrderSnByExOrderSn(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader, $exOrderSns){
		
		$this->send_getOrderSnByExOrderSn( $requestHeader, $exOrderSns);
		return $this->recv_getOrderSnByExOrderSn();
	}
	
	public function send_getOrderSnByExOrderSn(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader, $exOrderSns){
		
		$this->initInvocation("getOrderSnByExOrderSn");
		$args = new \com\vip\order\biz\service\OrdersBizService_getOrderSnByExOrderSn_args();
		
		$args->requestHeader = $requestHeader;
		
		$args->exOrderSns = $exOrderSns;
		
		$this->send_base($args);
	}
	
	public function recv_getOrderSnByExOrderSn(){
		
		$result = new \com\vip\order\biz\service\OrdersBizService_getOrderSnByExOrderSn_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function getOrderTransport(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\GetOrderTransportReq $getOrderTransportReq){
		
		$this->send_getOrderTransport( $requestHeader, $getOrderTransportReq);
		return $this->recv_getOrderTransport();
	}
	
	public function send_getOrderTransport(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\GetOrderTransportReq $getOrderTransportReq){
		
		$this->initInvocation("getOrderTransport");
		$args = new \com\vip\order\biz\service\OrdersBizService_getOrderTransport_args();
		
		$args->requestHeader = $requestHeader;
		
		$args->getOrderTransportReq = $getOrderTransportReq;
		
		$this->send_base($args);
	}
	
	public function recv_getOrderTransport(){
		
		$result = new \com\vip\order\biz\service\OrdersBizService_getOrderTransport_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function getOrderTransportDetail(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\GetOrderTransportDetailReq $getOrderTransportDetailReq){
		
		$this->send_getOrderTransportDetail( $requestHeader, $getOrderTransportDetailReq);
		return $this->recv_getOrderTransportDetail();
	}
	
	public function send_getOrderTransportDetail(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\GetOrderTransportDetailReq $getOrderTransportDetailReq){
		
		$this->initInvocation("getOrderTransportDetail");
		$args = new \com\vip\order\biz\service\OrdersBizService_getOrderTransportDetail_args();
		
		$args->requestHeader = $requestHeader;
		
		$args->getOrderTransportDetailReq = $getOrderTransportDetailReq;
		
		$this->send_base($args);
	}
	
	public function recv_getOrderTransportDetail(){
		
		$result = new \com\vip\order\biz\service\OrdersBizService_getOrderTransportDetail_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function getOrderTransportListByCodes(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeaderParam,\com\vip\order\biz\request\GetTransportListByCodesReq $getTransportListByCodesParam){
		
		$this->send_getOrderTransportListByCodes( $requestHeaderParam, $getTransportListByCodesParam);
		return $this->recv_getOrderTransportListByCodes();
	}
	
	public function send_getOrderTransportListByCodes(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeaderParam,\com\vip\order\biz\request\GetTransportListByCodesReq $getTransportListByCodesParam){
		
		$this->initInvocation("getOrderTransportListByCodes");
		$args = new \com\vip\order\biz\service\OrdersBizService_getOrderTransportListByCodes_args();
		
		$args->requestHeaderParam = $requestHeaderParam;
		
		$args->getTransportListByCodesParam = $getTransportListByCodesParam;
		
		$this->send_base($args);
	}
	
	public function recv_getOrderTransportListByCodes(){
		
		$result = new \com\vip\order\biz\service\OrdersBizService_getOrderTransportListByCodes_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function getOrdersBySizeId(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\GetOrdersBySizeIdReq $req,\com\vip\order\common\pojo\order\request\ResultFilter $filter){
		
		$this->send_getOrdersBySizeId( $requestHeader, $req, $filter);
		return $this->recv_getOrdersBySizeId();
	}
	
	public function send_getOrdersBySizeId(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\GetOrdersBySizeIdReq $req,\com\vip\order\common\pojo\order\request\ResultFilter $filter){
		
		$this->initInvocation("getOrdersBySizeId");
		$args = new \com\vip\order\biz\service\OrdersBizService_getOrdersBySizeId_args();
		
		$args->requestHeader = $requestHeader;
		
		$args->req = $req;
		
		$args->filter = $filter;
		
		$this->send_base($args);
	}
	
	public function recv_getOrdersBySizeId(){
		
		$result = new \com\vip\order\biz\service\OrdersBizService_getOrdersBySizeId_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function getPrepayOrderStatus(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\GetPrepayOrderStatusReq $req){
		
		$this->send_getPrepayOrderStatus( $requestHeader, $req);
		return $this->recv_getPrepayOrderStatus();
	}
	
	public function send_getPrepayOrderStatus(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\GetPrepayOrderStatusReq $req){
		
		$this->initInvocation("getPrepayOrderStatus");
		$args = new \com\vip\order\biz\service\OrdersBizService_getPrepayOrderStatus_args();
		
		$args->requestHeader = $requestHeader;
		
		$args->req = $req;
		
		$this->send_base($args);
	}
	
	public function recv_getPrepayOrderStatus(){
		
		$result = new \com\vip\order\biz\service\OrdersBizService_getPrepayOrderStatus_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function getPrepayOrderUnpayMsg(\com\vip\order\common\pojo\order\request\RequestHeader $header,\com\vip\order\biz\request\GetPrepayOrderUnpayMsgReq $req){
		
		$this->send_getPrepayOrderUnpayMsg( $header, $req);
		return $this->recv_getPrepayOrderUnpayMsg();
	}
	
	public function send_getPrepayOrderUnpayMsg(\com\vip\order\common\pojo\order\request\RequestHeader $header,\com\vip\order\biz\request\GetPrepayOrderUnpayMsgReq $req){
		
		$this->initInvocation("getPrepayOrderUnpayMsg");
		$args = new \com\vip\order\biz\service\OrdersBizService_getPrepayOrderUnpayMsg_args();
		
		$args->header = $header;
		
		$args->req = $req;
		
		$this->send_base($args);
	}
	
	public function recv_getPrepayOrderUnpayMsg(){
		
		$result = new \com\vip\order\biz\service\OrdersBizService_getPrepayOrderUnpayMsg_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function getRdc(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\GetRdcReq $getRdcReq){
		
		$this->send_getRdc( $requestHeader, $getRdcReq);
		return $this->recv_getRdc();
	}
	
	public function send_getRdc(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\GetRdcReq $getRdcReq){
		
		$this->initInvocation("getRdc");
		$args = new \com\vip\order\biz\service\OrdersBizService_getRdc_args();
		
		$args->requestHeader = $requestHeader;
		
		$args->getRdcReq = $getRdcReq;
		
		$this->send_base($args);
	}
	
	public function recv_getRdc(){
		
		$result = new \com\vip\order\biz\service\OrdersBizService_getRdc_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function getRdcInvoice(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\GetRdcInvoiceReq $req){
		
		$this->send_getRdcInvoice( $requestHeader, $req);
		return $this->recv_getRdcInvoice();
	}
	
	public function send_getRdcInvoice(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\GetRdcInvoiceReq $req){
		
		$this->initInvocation("getRdcInvoice");
		$args = new \com\vip\order\biz\service\OrdersBizService_getRdcInvoice_args();
		
		$args->requestHeader = $requestHeader;
		
		$args->req = $req;
		
		$this->send_base($args);
	}
	
	public function recv_getRdcInvoice(){
		
		$result = new \com\vip\order\biz\service\OrdersBizService_getRdcInvoice_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function getReturnOrExchangeGoods(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\GetReturnOrExchangeGoodsReq $req){
		
		$this->send_getReturnOrExchangeGoods( $requestHeader, $req);
		return $this->recv_getReturnOrExchangeGoods();
	}
	
	public function send_getReturnOrExchangeGoods(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\GetReturnOrExchangeGoodsReq $req){
		
		$this->initInvocation("getReturnOrExchangeGoods");
		$args = new \com\vip\order\biz\service\OrdersBizService_getReturnOrExchangeGoods_args();
		
		$args->requestHeader = $requestHeader;
		
		$args->req = $req;
		
		$this->send_base($args);
	}
	
	public function recv_getReturnOrExchangeGoods(){
		
		$result = new \com\vip\order\biz\service\OrdersBizService_getReturnOrExchangeGoods_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function getSimpleOrderFlowFlag(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeaderParam,\com\vip\order\biz\request\GetSimpleOrderFlowFlagReq $getSimpleOrderFlowFlagParam){
		
		$this->send_getSimpleOrderFlowFlag( $requestHeaderParam, $getSimpleOrderFlowFlagParam);
		return $this->recv_getSimpleOrderFlowFlag();
	}
	
	public function send_getSimpleOrderFlowFlag(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeaderParam,\com\vip\order\biz\request\GetSimpleOrderFlowFlagReq $getSimpleOrderFlowFlagParam){
		
		$this->initInvocation("getSimpleOrderFlowFlag");
		$args = new \com\vip\order\biz\service\OrdersBizService_getSimpleOrderFlowFlag_args();
		
		$args->requestHeaderParam = $requestHeaderParam;
		
		$args->getSimpleOrderFlowFlagParam = $getSimpleOrderFlowFlagParam;
		
		$this->send_base($args);
	}
	
	public function recv_getSimpleOrderFlowFlag(){
		
		$result = new \com\vip\order\biz\service\OrdersBizService_getSimpleOrderFlowFlag_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function getUnpayOrderList(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeaderParam,\com\vip\order\biz\request\GetUnpayOrderReq $getUnpayOrderParam){
		
		$this->send_getUnpayOrderList( $requestHeaderParam, $getUnpayOrderParam);
		return $this->recv_getUnpayOrderList();
	}
	
	public function send_getUnpayOrderList(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeaderParam,\com\vip\order\biz\request\GetUnpayOrderReq $getUnpayOrderParam){
		
		$this->initInvocation("getUnpayOrderList");
		$args = new \com\vip\order\biz\service\OrdersBizService_getUnpayOrderList_args();
		
		$args->requestHeaderParam = $requestHeaderParam;
		
		$args->getUnpayOrderParam = $getUnpayOrderParam;
		
		$this->send_base($args);
	}
	
	public function recv_getUnpayOrderList(){
		
		$result = new \com\vip\order\biz\service\OrdersBizService_getUnpayOrderList_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function getUserDeliveryAddress(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\GetUserDeliveryAddressReq $getUserDeliveryAddressReq,\com\vip\order\common\pojo\order\request\ResultFilter $resultFilter){
		
		$this->send_getUserDeliveryAddress( $requestHeader, $getUserDeliveryAddressReq, $resultFilter);
		return $this->recv_getUserDeliveryAddress();
	}
	
	public function send_getUserDeliveryAddress(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\GetUserDeliveryAddressReq $getUserDeliveryAddressReq,\com\vip\order\common\pojo\order\request\ResultFilter $resultFilter){
		
		$this->initInvocation("getUserDeliveryAddress");
		$args = new \com\vip\order\biz\service\OrdersBizService_getUserDeliveryAddress_args();
		
		$args->requestHeader = $requestHeader;
		
		$args->getUserDeliveryAddressReq = $getUserDeliveryAddressReq;
		
		$args->resultFilter = $resultFilter;
		
		$this->send_base($args);
	}
	
	public function recv_getUserDeliveryAddress(){
		
		$result = new \com\vip\order\biz\service\OrdersBizService_getUserDeliveryAddress_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function getUserFirstOrder(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\GetUserFirstOrderReq $getUserFirstOrderReq){
		
		$this->send_getUserFirstOrder( $requestHeader, $getUserFirstOrderReq);
		return $this->recv_getUserFirstOrder();
	}
	
	public function send_getUserFirstOrder(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\GetUserFirstOrderReq $getUserFirstOrderReq){
		
		$this->initInvocation("getUserFirstOrder");
		$args = new \com\vip\order\biz\service\OrdersBizService_getUserFirstOrder_args();
		
		$args->requestHeader = $requestHeader;
		
		$args->getUserFirstOrderReq = $getUserFirstOrderReq;
		
		$this->send_base($args);
	}
	
	public function recv_getUserFirstOrder(){
		
		$result = new \com\vip\order\biz\service\OrdersBizService_getUserFirstOrder_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function healthCheck(){
		
		$this->send_healthCheck();
		return $this->recv_healthCheck();
	}
	
	public function send_healthCheck(){
		
		$this->initInvocation("healthCheck");
		$args = new \com\vip\order\biz\service\OrdersBizService_healthCheck_args();
		
		$this->send_base($args);
	}
	
	public function recv_healthCheck(){
		
		$result = new \com\vip\order\biz\service\OrdersBizService_healthCheck_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function mergeOrder(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\MergeOrderReq $reqParam){
		
		$this->send_mergeOrder( $requestHeader, $reqParam);
		return $this->recv_mergeOrder();
	}
	
	public function send_mergeOrder(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\MergeOrderReq $reqParam){
		
		$this->initInvocation("mergeOrder");
		$args = new \com\vip\order\biz\service\OrdersBizService_mergeOrder_args();
		
		$args->requestHeader = $requestHeader;
		
		$args->reqParam = $reqParam;
		
		$this->send_base($args);
	}
	
	public function recv_mergeOrder(){
		
		$result = new \com\vip\order\biz\service\OrdersBizService_mergeOrder_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function modifyOrderConsignee(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\ModifyOrderConsigneeReq $modifyOrderConsigneeReq){
		
		$this->send_modifyOrderConsignee( $requestHeader, $modifyOrderConsigneeReq);
		return $this->recv_modifyOrderConsignee();
	}
	
	public function send_modifyOrderConsignee(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\ModifyOrderConsigneeReq $modifyOrderConsigneeReq){
		
		$this->initInvocation("modifyOrderConsignee");
		$args = new \com\vip\order\biz\service\OrdersBizService_modifyOrderConsignee_args();
		
		$args->requestHeader = $requestHeader;
		
		$args->modifyOrderConsigneeReq = $modifyOrderConsigneeReq;
		
		$this->send_base($args);
	}
	
	public function recv_modifyOrderConsignee(){
		
		$result = new \com\vip\order\biz\service\OrdersBizService_modifyOrderConsignee_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function modifyOrderElectronicInvoice(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\ModifyOrderElectronicInvoiceReq $modifyOrderElectronicInvoiceReq){
		
		$this->send_modifyOrderElectronicInvoice( $requestHeader, $modifyOrderElectronicInvoiceReq);
		return $this->recv_modifyOrderElectronicInvoice();
	}
	
	public function send_modifyOrderElectronicInvoice(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\ModifyOrderElectronicInvoiceReq $modifyOrderElectronicInvoiceReq){
		
		$this->initInvocation("modifyOrderElectronicInvoice");
		$args = new \com\vip\order\biz\service\OrdersBizService_modifyOrderElectronicInvoice_args();
		
		$args->requestHeader = $requestHeader;
		
		$args->modifyOrderElectronicInvoiceReq = $modifyOrderElectronicInvoiceReq;
		
		$this->send_base($args);
	}
	
	public function recv_modifyOrderElectronicInvoice(){
		
		$result = new \com\vip\order\biz\service\OrdersBizService_modifyOrderElectronicInvoice_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function modifyOrderGoods(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\ModifyOrderGoodsReq $orderGoodsReq){
		
		$this->send_modifyOrderGoods( $requestHeader, $orderGoodsReq);
		return $this->recv_modifyOrderGoods();
	}
	
	public function send_modifyOrderGoods(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\ModifyOrderGoodsReq $orderGoodsReq){
		
		$this->initInvocation("modifyOrderGoods");
		$args = new \com\vip\order\biz\service\OrdersBizService_modifyOrderGoods_args();
		
		$args->requestHeader = $requestHeader;
		
		$args->orderGoodsReq = $orderGoodsReq;
		
		$this->send_base($args);
	}
	
	public function recv_modifyOrderGoods(){
		
		$result = new \com\vip\order\biz\service\OrdersBizService_modifyOrderGoods_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function modifyOrderPayType(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\common\pojo\order\vo\ModifyPayTypeReq $ModifyPayTypeReq){
		
		$this->send_modifyOrderPayType( $requestHeader, $ModifyPayTypeReq);
		return $this->recv_modifyOrderPayType();
	}
	
	public function send_modifyOrderPayType(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\common\pojo\order\vo\ModifyPayTypeReq $ModifyPayTypeReq){
		
		$this->initInvocation("modifyOrderPayType");
		$args = new \com\vip\order\biz\service\OrdersBizService_modifyOrderPayType_args();
		
		$args->requestHeader = $requestHeader;
		
		$args->ModifyPayTypeReq = $ModifyPayTypeReq;
		
		$this->send_base($args);
	}
	
	public function recv_modifyOrderPayType(){
		
		$result = new \com\vip\order\biz\service\OrdersBizService_modifyOrderPayType_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function modifyOrderShipped(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\ModifyOrderShippedReq $modifyOrderShippedReq){
		
		$this->send_modifyOrderShipped( $requestHeader, $modifyOrderShippedReq);
		return $this->recv_modifyOrderShipped();
	}
	
	public function send_modifyOrderShipped(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\ModifyOrderShippedReq $modifyOrderShippedReq){
		
		$this->initInvocation("modifyOrderShipped");
		$args = new \com\vip\order\biz\service\OrdersBizService_modifyOrderShipped_args();
		
		$args->requestHeader = $requestHeader;
		
		$args->modifyOrderShippedReq = $modifyOrderShippedReq;
		
		$this->send_base($args);
	}
	
	public function recv_modifyOrderShipped(){
		
		$result = new \com\vip\order\biz\service\OrdersBizService_modifyOrderShipped_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function modifyPrepayOrderPayType(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\ModifyPrepayOrderPayTypeReq $modifyPrepayOrderPayTypeReq){
		
		$this->send_modifyPrepayOrderPayType( $requestHeader, $modifyPrepayOrderPayTypeReq);
		return $this->recv_modifyPrepayOrderPayType();
	}
	
	public function send_modifyPrepayOrderPayType(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\ModifyPrepayOrderPayTypeReq $modifyPrepayOrderPayTypeReq){
		
		$this->initInvocation("modifyPrepayOrderPayType");
		$args = new \com\vip\order\biz\service\OrdersBizService_modifyPrepayOrderPayType_args();
		
		$args->requestHeader = $requestHeader;
		
		$args->modifyPrepayOrderPayTypeReq = $modifyPrepayOrderPayTypeReq;
		
		$this->send_base($args);
	}
	
	public function recv_modifyPrepayOrderPayType(){
		
		$result = new \com\vip\order\biz\service\OrdersBizService_modifyPrepayOrderPayType_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function notifyCustomsDeclarationFailed(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\NotifyCustomsDeclarationFailedReq $req){
		
		$this->send_notifyCustomsDeclarationFailed( $requestHeader, $req);
		return $this->recv_notifyCustomsDeclarationFailed();
	}
	
	public function send_notifyCustomsDeclarationFailed(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\NotifyCustomsDeclarationFailedReq $req){
		
		$this->initInvocation("notifyCustomsDeclarationFailed");
		$args = new \com\vip\order\biz\service\OrdersBizService_notifyCustomsDeclarationFailed_args();
		
		$args->requestHeader = $requestHeader;
		
		$args->req = $req;
		
		$this->send_base($args);
	}
	
	public function recv_notifyCustomsDeclarationFailed(){
		
		$result = new \com\vip\order\biz\service\OrdersBizService_notifyCustomsDeclarationFailed_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function ofcEntranceGrayControl(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\OfcEntranceGrayControlReq $req){
		
		$this->send_ofcEntranceGrayControl( $requestHeader, $req);
		return $this->recv_ofcEntranceGrayControl();
	}
	
	public function send_ofcEntranceGrayControl(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\OfcEntranceGrayControlReq $req){
		
		$this->initInvocation("ofcEntranceGrayControl");
		$args = new \com\vip\order\biz\service\OrdersBizService_ofcEntranceGrayControl_args();
		
		$args->requestHeader = $requestHeader;
		
		$args->req = $req;
		
		$this->send_base($args);
	}
	
	public function recv_ofcEntranceGrayControl(){
		
		$result = new \com\vip\order\biz\service\OrdersBizService_ofcEntranceGrayControl_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function postOrderVMSMessage(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\PostOrderVMSMessageReq $postOrderVMSMessageReq){
		
		$this->send_postOrderVMSMessage( $requestHeader, $postOrderVMSMessageReq);
		return $this->recv_postOrderVMSMessage();
	}
	
	public function send_postOrderVMSMessage(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\PostOrderVMSMessageReq $postOrderVMSMessageReq){
		
		$this->initInvocation("postOrderVMSMessage");
		$args = new \com\vip\order\biz\service\OrdersBizService_postOrderVMSMessage_args();
		
		$args->requestHeader = $requestHeader;
		
		$args->postOrderVMSMessageReq = $postOrderVMSMessageReq;
		
		$this->send_base($args);
	}
	
	public function recv_postOrderVMSMessage(){
		
		$result = new \com\vip\order\biz\service\OrdersBizService_postOrderVMSMessage_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function putIntoSplitQueue(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\PutIntoSplitQueueReq $putIntoSplitQueueReq){
		
		$this->send_putIntoSplitQueue( $requestHeader, $putIntoSplitQueueReq);
		return $this->recv_putIntoSplitQueue();
	}
	
	public function send_putIntoSplitQueue(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\PutIntoSplitQueueReq $putIntoSplitQueueReq){
		
		$this->initInvocation("putIntoSplitQueue");
		$args = new \com\vip\order\biz\service\OrdersBizService_putIntoSplitQueue_args();
		
		$args->requestHeader = $requestHeader;
		
		$args->putIntoSplitQueueReq = $putIntoSplitQueueReq;
		
		$this->send_base($args);
	}
	
	public function recv_putIntoSplitQueue(){
		
		$result = new \com\vip\order\biz\service\OrdersBizService_putIntoSplitQueue_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function putKeyToRollbackQueue(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\PutKeyToRollbackQueueReq $req){
		
		$this->send_putKeyToRollbackQueue( $requestHeader, $req);
		return $this->recv_putKeyToRollbackQueue();
	}
	
	public function send_putKeyToRollbackQueue(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\PutKeyToRollbackQueueReq $req){
		
		$this->initInvocation("putKeyToRollbackQueue");
		$args = new \com\vip\order\biz\service\OrdersBizService_putKeyToRollbackQueue_args();
		
		$args->requestHeader = $requestHeader;
		
		$args->req = $req;
		
		$this->send_base($args);
	}
	
	public function recv_putKeyToRollbackQueue(){
		
		$result = new \com\vip\order\biz\service\OrdersBizService_putKeyToRollbackQueue_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function receptionConfirmDelivered(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\ReceptionConfirmDeliveredReq $req){
		
		$this->send_receptionConfirmDelivered( $requestHeader, $req);
		return $this->recv_receptionConfirmDelivered();
	}
	
	public function send_receptionConfirmDelivered(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\ReceptionConfirmDeliveredReq $req){
		
		$this->initInvocation("receptionConfirmDelivered");
		$args = new \com\vip\order\biz\service\OrdersBizService_receptionConfirmDelivered_args();
		
		$args->requestHeader = $requestHeader;
		
		$args->req = $req;
		
		$this->send_base($args);
	}
	
	public function recv_receptionConfirmDelivered(){
		
		$result = new \com\vip\order\biz\service\OrdersBizService_receptionConfirmDelivered_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function refundOrder(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\OrderRefundReq $orderRefundReq){
		
		$this->send_refundOrder( $requestHeader, $orderRefundReq);
		return $this->recv_refundOrder();
	}
	
	public function send_refundOrder(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\OrderRefundReq $orderRefundReq){
		
		$this->initInvocation("refundOrder");
		$args = new \com\vip\order\biz\service\OrdersBizService_refundOrder_args();
		
		$args->requestHeader = $requestHeader;
		
		$args->orderRefundReq = $orderRefundReq;
		
		$this->send_base($args);
	}
	
	public function recv_refundOrder(){
		
		$result = new \com\vip\order\biz\service\OrdersBizService_refundOrder_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function releaseStock(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\ReleaseStockReq $releaseStockReq){
		
		$this->send_releaseStock( $requestHeader, $releaseStockReq);
		return $this->recv_releaseStock();
	}
	
	public function send_releaseStock(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\ReleaseStockReq $releaseStockReq){
		
		$this->initInvocation("releaseStock");
		$args = new \com\vip\order\biz\service\OrdersBizService_releaseStock_args();
		
		$args->requestHeader = $requestHeader;
		
		$args->releaseStockReq = $releaseStockReq;
		
		$this->send_base($args);
	}
	
	public function recv_releaseStock(){
		
		$result = new \com\vip\order\biz\service\OrdersBizService_releaseStock_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function rollbackOrder(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\RollbackOrderReq $rollbackOrderReq){
		
		$this->send_rollbackOrder( $requestHeader, $rollbackOrderReq);
		return $this->recv_rollbackOrder();
	}
	
	public function send_rollbackOrder(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\RollbackOrderReq $rollbackOrderReq){
		
		$this->initInvocation("rollbackOrder");
		$args = new \com\vip\order\biz\service\OrdersBizService_rollbackOrder_args();
		
		$args->requestHeader = $requestHeader;
		
		$args->rollbackOrderReq = $rollbackOrderReq;
		
		$this->send_base($args);
	}
	
	public function recv_rollbackOrder(){
		
		$result = new \com\vip\order\biz\service\OrdersBizService_rollbackOrder_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function searchOrderListByUserId(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\SearchOrderListByUserIdReq $getOrderHistoryReq,\com\vip\order\common\pojo\order\request\ResultFilter $resultFilter){
		
		$this->send_searchOrderListByUserId( $requestHeader, $getOrderHistoryReq, $resultFilter);
		return $this->recv_searchOrderListByUserId();
	}
	
	public function send_searchOrderListByUserId(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\SearchOrderListByUserIdReq $getOrderHistoryReq,\com\vip\order\common\pojo\order\request\ResultFilter $resultFilter){
		
		$this->initInvocation("searchOrderListByUserId");
		$args = new \com\vip\order\biz\service\OrdersBizService_searchOrderListByUserId_args();
		
		$args->requestHeader = $requestHeader;
		
		$args->getOrderHistoryReq = $getOrderHistoryReq;
		
		$args->resultFilter = $resultFilter;
		
		$this->send_base($args);
	}
	
	public function recv_searchOrderListByUserId(){
		
		$result = new \com\vip\order\biz\service\OrdersBizService_searchOrderListByUserId_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function signOrder(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\SignOrderReq $signOrderReq){
		
		$this->send_signOrder( $requestHeader, $signOrderReq);
		return $this->recv_signOrder();
	}
	
	public function send_signOrder(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\SignOrderReq $signOrderReq){
		
		$this->initInvocation("signOrder");
		$args = new \com\vip\order\biz\service\OrdersBizService_signOrder_args();
		
		$args->requestHeader = $requestHeader;
		
		$args->signOrderReq = $signOrderReq;
		
		$this->send_base($args);
	}
	
	public function recv_signOrder(){
		
		$result = new \com\vip\order\biz\service\OrdersBizService_signOrder_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function triggerGroupByAuditOrder(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\GroupByOrderAuditReq $groupByOrderAuditReq){
		
		$this->send_triggerGroupByAuditOrder( $requestHeader, $groupByOrderAuditReq);
		return $this->recv_triggerGroupByAuditOrder();
	}
	
	public function send_triggerGroupByAuditOrder(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\GroupByOrderAuditReq $groupByOrderAuditReq){
		
		$this->initInvocation("triggerGroupByAuditOrder");
		$args = new \com\vip\order\biz\service\OrdersBizService_triggerGroupByAuditOrder_args();
		
		$args->requestHeader = $requestHeader;
		
		$args->groupByOrderAuditReq = $groupByOrderAuditReq;
		
		$this->send_base($args);
	}
	
	public function recv_triggerGroupByAuditOrder(){
		
		$result = new \com\vip\order\biz\service\OrdersBizService_triggerGroupByAuditOrder_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function updateAutoPayAuth(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\UpdateAutoPayAuthReq $req){
		
		$this->send_updateAutoPayAuth( $requestHeader, $req);
		return $this->recv_updateAutoPayAuth();
	}
	
	public function send_updateAutoPayAuth(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\UpdateAutoPayAuthReq $req){
		
		$this->initInvocation("updateAutoPayAuth");
		$args = new \com\vip\order\biz\service\OrdersBizService_updateAutoPayAuth_args();
		
		$args->requestHeader = $requestHeader;
		
		$args->req = $req;
		
		$this->send_base($args);
	}
	
	public function recv_updateAutoPayAuth(){
		
		$result = new \com\vip\order\biz\service\OrdersBizService_updateAutoPayAuth_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function updateOrderPayResult(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\UpdateOrderPayResultReq $updateOrderPayResultReq){
		
		$this->send_updateOrderPayResult( $requestHeader, $updateOrderPayResultReq);
		return $this->recv_updateOrderPayResult();
	}
	
	public function send_updateOrderPayResult(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\UpdateOrderPayResultReq $updateOrderPayResultReq){
		
		$this->initInvocation("updateOrderPayResult");
		$args = new \com\vip\order\biz\service\OrdersBizService_updateOrderPayResult_args();
		
		$args->requestHeader = $requestHeader;
		
		$args->updateOrderPayResultReq = $updateOrderPayResultReq;
		
		$this->send_base($args);
	}
	
	public function recv_updateOrderPayResult(){
		
		$result = new \com\vip\order\biz\service\OrdersBizService_updateOrderPayResult_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function updateOrderToReturnVerified(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\UpdateOrderToReturnVerifiedReq $updateOrderToReturnVerifiedReq){
		
		$this->send_updateOrderToReturnVerified( $requestHeader, $updateOrderToReturnVerifiedReq);
		return $this->recv_updateOrderToReturnVerified();
	}
	
	public function send_updateOrderToReturnVerified(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\UpdateOrderToReturnVerifiedReq $updateOrderToReturnVerifiedReq){
		
		$this->initInvocation("updateOrderToReturnVerified");
		$args = new \com\vip\order\biz\service\OrdersBizService_updateOrderToReturnVerified_args();
		
		$args->requestHeader = $requestHeader;
		
		$args->updateOrderToReturnVerifiedReq = $updateOrderToReturnVerifiedReq;
		
		$this->send_base($args);
	}
	
	public function recv_updateOrderToReturnVerified(){
		
		$result = new \com\vip\order\biz\service\OrdersBizService_updateOrderToReturnVerified_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function updatePayTypeToCOD(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\UpdatePayTypeToCODReq $updatePayTypeToCODReq){
		
		$this->send_updatePayTypeToCOD( $requestHeader, $updatePayTypeToCODReq);
		return $this->recv_updatePayTypeToCOD();
	}
	
	public function send_updatePayTypeToCOD(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\UpdatePayTypeToCODReq $updatePayTypeToCODReq){
		
		$this->initInvocation("updatePayTypeToCOD");
		$args = new \com\vip\order\biz\service\OrdersBizService_updatePayTypeToCOD_args();
		
		$args->requestHeader = $requestHeader;
		
		$args->updatePayTypeToCODReq = $updatePayTypeToCODReq;
		
		$this->send_base($args);
	}
	
	public function recv_updatePayTypeToCOD(){
		
		$result = new \com\vip\order\biz\service\OrdersBizService_updatePayTypeToCOD_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function updatePrePayToVerified(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\UpdatePrePayToVerifiedReq $updatePrePayToVerifiedReq){
		
		$this->send_updatePrePayToVerified( $requestHeader, $updatePrePayToVerifiedReq);
		return $this->recv_updatePrePayToVerified();
	}
	
	public function send_updatePrePayToVerified(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\UpdatePrePayToVerifiedReq $updatePrePayToVerifiedReq){
		
		$this->initInvocation("updatePrePayToVerified");
		$args = new \com\vip\order\biz\service\OrdersBizService_updatePrePayToVerified_args();
		
		$args->requestHeader = $requestHeader;
		
		$args->updatePrePayToVerifiedReq = $updatePrePayToVerifiedReq;
		
		$this->send_base($args);
	}
	
	public function recv_updatePrePayToVerified(){
		
		$result = new \com\vip\order\biz\service\OrdersBizService_updatePrePayToVerified_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function updateReservationState(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\UpdateReservationStateReq $req){
		
		$this->send_updateReservationState( $requestHeader, $req);
		return $this->recv_updateReservationState();
	}
	
	public function send_updateReservationState(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\UpdateReservationStateReq $req){
		
		$this->initInvocation("updateReservationState");
		$args = new \com\vip\order\biz\service\OrdersBizService_updateReservationState_args();
		
		$args->requestHeader = $requestHeader;
		
		$args->req = $req;
		
		$this->send_base($args);
	}
	
	public function recv_updateReservationState(){
		
		$result = new \com\vip\order\biz\service\OrdersBizService_updateReservationState_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function userDeleteOrder(\com\vip\order\common\pojo\order\request\RequestHeader $header,\com\vip\order\biz\request\UserDeleteOrderReq $req){
		
		$this->send_userDeleteOrder( $header, $req);
		return $this->recv_userDeleteOrder();
	}
	
	public function send_userDeleteOrder(\com\vip\order\common\pojo\order\request\RequestHeader $header,\com\vip\order\biz\request\UserDeleteOrderReq $req){
		
		$this->initInvocation("userDeleteOrder");
		$args = new \com\vip\order\biz\service\OrdersBizService_userDeleteOrder_args();
		
		$args->header = $header;
		
		$args->req = $req;
		
		$this->send_base($args);
	}
	
	public function recv_userDeleteOrder(){
		
		$result = new \com\vip\order\biz\service\OrdersBizService_userDeleteOrder_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
	public function verifyStockAndGetPayableFlag(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\VerifyStockAndGetPayableFlagReq $req){
		
		$this->send_verifyStockAndGetPayableFlag( $requestHeader, $req);
		return $this->recv_verifyStockAndGetPayableFlag();
	}
	
	public function send_verifyStockAndGetPayableFlag(\com\vip\order\common\pojo\order\request\RequestHeader $requestHeader,\com\vip\order\biz\request\VerifyStockAndGetPayableFlagReq $req){
		
		$this->initInvocation("verifyStockAndGetPayableFlag");
		$args = new \com\vip\order\biz\service\OrdersBizService_verifyStockAndGetPayableFlag_args();
		
		$args->requestHeader = $requestHeader;
		
		$args->req = $req;
		
		$this->send_base($args);
	}
	
	public function recv_verifyStockAndGetPayableFlag(){
		
		$result = new \com\vip\order\biz\service\OrdersBizService_verifyStockAndGetPayableFlag_result();
		$this->receive_base($result);
		if ($result->success !== null){
			
			return $result->success;
		}
		
	}
	
	
}




class OrdersBizService_addOrderTransport_args {
	
	static $_TSPEC;
	public $requestHeader = null;
	public $addOrderTransportReq = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'requestHeader'
			),
			2 => array(
			'var' => 'addOrderTransportReq'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['requestHeader'])){
				
				$this->requestHeader = $vals['requestHeader'];
			}
			
			
			if (isset($vals['addOrderTransportReq'])){
				
				$this->addOrderTransportReq = $vals['addOrderTransportReq'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->requestHeader = new \com\vip\order\common\pojo\order\request\RequestHeader();
			$this->requestHeader->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->addOrderTransportReq = new \com\vip\order\biz\request\AddOrderTransportReq();
			$this->addOrderTransportReq->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->requestHeader !== null) {
			
			$xfer += $output->writeFieldBegin('requestHeader');
			
			if (!is_object($this->requestHeader)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->requestHeader->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->addOrderTransportReq !== null) {
			
			$xfer += $output->writeFieldBegin('addOrderTransportReq');
			
			if (!is_object($this->addOrderTransportReq)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->addOrderTransportReq->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_autoPay_args {
	
	static $_TSPEC;
	public $requestHeader = null;
	public $req = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'requestHeader'
			),
			2 => array(
			'var' => 'req'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['requestHeader'])){
				
				$this->requestHeader = $vals['requestHeader'];
			}
			
			
			if (isset($vals['req'])){
				
				$this->req = $vals['req'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->requestHeader = new \com\vip\order\common\pojo\order\request\RequestHeader();
			$this->requestHeader->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->req = new \com\vip\order\biz\request\AutoPayReq();
			$this->req->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->requestHeader !== null) {
			
			$xfer += $output->writeFieldBegin('requestHeader');
			
			if (!is_object($this->requestHeader)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->requestHeader->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->req !== null) {
			
			$xfer += $output->writeFieldBegin('req');
			
			if (!is_object($this->req)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->req->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_autoPayFail_args {
	
	static $_TSPEC;
	public $requestHeader = null;
	public $req = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'requestHeader'
			),
			2 => array(
			'var' => 'req'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['requestHeader'])){
				
				$this->requestHeader = $vals['requestHeader'];
			}
			
			
			if (isset($vals['req'])){
				
				$this->req = $vals['req'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->requestHeader = new \com\vip\order\common\pojo\order\request\RequestHeader();
			$this->requestHeader->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->req = new \com\vip\order\biz\request\AutoPayFailReq();
			$this->req->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->requestHeader !== null) {
			
			$xfer += $output->writeFieldBegin('requestHeader');
			
			if (!is_object($this->requestHeader)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->requestHeader->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->req !== null) {
			
			$xfer += $output->writeFieldBegin('req');
			
			if (!is_object($this->req)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->req->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_autoTakeInventory_args {
	
	static $_TSPEC;
	public $requestHeader = null;
	public $req = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'requestHeader'
			),
			2 => array(
			'var' => 'req'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['requestHeader'])){
				
				$this->requestHeader = $vals['requestHeader'];
			}
			
			
			if (isset($vals['req'])){
				
				$this->req = $vals['req'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->requestHeader = new \com\vip\order\common\pojo\order\request\RequestHeader();
			$this->requestHeader->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->req = new \com\vip\order\biz\request\AutoTakeInventoryReq();
			$this->req->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->requestHeader !== null) {
			
			$xfer += $output->writeFieldBegin('requestHeader');
			
			if (!is_object($this->requestHeader)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->requestHeader->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->req !== null) {
			
			$xfer += $output->writeFieldBegin('req');
			
			if (!is_object($this->req)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->req->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_b2cSupportSendSms_args {
	
	static $_TSPEC;
	public $header = null;
	public $req = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'header'
			),
			2 => array(
			'var' => 'req'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['header'])){
				
				$this->header = $vals['header'];
			}
			
			
			if (isset($vals['req'])){
				
				$this->req = $vals['req'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->header = new \com\vip\order\common\pojo\order\request\RequestHeader();
			$this->header->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->req = new \com\vip\order\biz\request\B2CSupportSendSmsReq();
			$this->req->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->header !== null) {
			
			$xfer += $output->writeFieldBegin('header');
			
			if (!is_object($this->header)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->header->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->req !== null) {
			
			$xfer += $output->writeFieldBegin('req');
			
			if (!is_object($this->req)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->req->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_batchGetOrderActiveDetail_args {
	
	static $_TSPEC;
	public $requestHeader = null;
	public $batchGetOrderActiveDetailReq = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'requestHeader'
			),
			2 => array(
			'var' => 'batchGetOrderActiveDetailReq'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['requestHeader'])){
				
				$this->requestHeader = $vals['requestHeader'];
			}
			
			
			if (isset($vals['batchGetOrderActiveDetailReq'])){
				
				$this->batchGetOrderActiveDetailReq = $vals['batchGetOrderActiveDetailReq'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->requestHeader = new \com\vip\order\common\pojo\order\request\RequestHeader();
			$this->requestHeader->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->batchGetOrderActiveDetailReq = new \com\vip\order\biz\request\BatchGetOrderActiveDetailReq();
			$this->batchGetOrderActiveDetailReq->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->requestHeader !== null) {
			
			$xfer += $output->writeFieldBegin('requestHeader');
			
			if (!is_object($this->requestHeader)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->requestHeader->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->batchGetOrderActiveDetailReq !== null) {
			
			$xfer += $output->writeFieldBegin('batchGetOrderActiveDetailReq');
			
			if (!is_object($this->batchGetOrderActiveDetailReq)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->batchGetOrderActiveDetailReq->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_batchGetOrderList_args {
	
	static $_TSPEC;
	public $requestHeader = null;
	public $searchOrderReq = null;
	public $resultFilter = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'requestHeader'
			),
			2 => array(
			'var' => 'searchOrderReq'
			),
			3 => array(
			'var' => 'resultFilter'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['requestHeader'])){
				
				$this->requestHeader = $vals['requestHeader'];
			}
			
			
			if (isset($vals['searchOrderReq'])){
				
				$this->searchOrderReq = $vals['searchOrderReq'];
			}
			
			
			if (isset($vals['resultFilter'])){
				
				$this->resultFilter = $vals['resultFilter'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->requestHeader = new \com\vip\order\common\pojo\order\request\RequestHeader();
			$this->requestHeader->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->searchOrderReq = new \com\vip\order\biz\request\BatchGetOrderListReq();
			$this->searchOrderReq->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->resultFilter = new \com\vip\order\common\pojo\order\request\ResultFilter();
			$this->resultFilter->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->requestHeader !== null) {
			
			$xfer += $output->writeFieldBegin('requestHeader');
			
			if (!is_object($this->requestHeader)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->requestHeader->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->searchOrderReq !== null) {
			
			$xfer += $output->writeFieldBegin('searchOrderReq');
			
			if (!is_object($this->searchOrderReq)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->searchOrderReq->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->resultFilter !== null) {
			
			$xfer += $output->writeFieldBegin('resultFilter');
			
			if (!is_object($this->resultFilter)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->resultFilter->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_batchGetOrderTransportList_args {
	
	static $_TSPEC;
	public $requestHeader = null;
	public $batchGetOrderTransportListReq = null;
	public $resultFilter = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'requestHeader'
			),
			2 => array(
			'var' => 'batchGetOrderTransportListReq'
			),
			3 => array(
			'var' => 'resultFilter'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['requestHeader'])){
				
				$this->requestHeader = $vals['requestHeader'];
			}
			
			
			if (isset($vals['batchGetOrderTransportListReq'])){
				
				$this->batchGetOrderTransportListReq = $vals['batchGetOrderTransportListReq'];
			}
			
			
			if (isset($vals['resultFilter'])){
				
				$this->resultFilter = $vals['resultFilter'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->requestHeader = new \com\vip\order\common\pojo\order\request\RequestHeader();
			$this->requestHeader->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->batchGetOrderTransportListReq = new \com\vip\order\biz\request\BatchGetOrderTransportListReq();
			$this->batchGetOrderTransportListReq->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->resultFilter = new \com\vip\order\common\pojo\order\request\ResultFilter();
			$this->resultFilter->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->requestHeader !== null) {
			
			$xfer += $output->writeFieldBegin('requestHeader');
			
			if (!is_object($this->requestHeader)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->requestHeader->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->batchGetOrderTransportListReq !== null) {
			
			$xfer += $output->writeFieldBegin('batchGetOrderTransportListReq');
			
			if (!is_object($this->batchGetOrderTransportListReq)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->batchGetOrderTransportListReq->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->resultFilter !== null) {
			
			$xfer += $output->writeFieldBegin('resultFilter');
			
			if (!is_object($this->resultFilter)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->resultFilter->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_batchModifyOrderInvoice_args {
	
	static $_TSPEC;
	public $requestHeader = null;
	public $batchModifyOrderInvoiceReq = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'requestHeader'
			),
			2 => array(
			'var' => 'batchModifyOrderInvoiceReq'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['requestHeader'])){
				
				$this->requestHeader = $vals['requestHeader'];
			}
			
			
			if (isset($vals['batchModifyOrderInvoiceReq'])){
				
				$this->batchModifyOrderInvoiceReq = $vals['batchModifyOrderInvoiceReq'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->requestHeader = new \com\vip\order\common\pojo\order\request\RequestHeader();
			$this->requestHeader->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->batchModifyOrderInvoiceReq = new \com\vip\order\biz\request\BatchModifyOrderInvoiceReq();
			$this->batchModifyOrderInvoiceReq->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->requestHeader !== null) {
			
			$xfer += $output->writeFieldBegin('requestHeader');
			
			if (!is_object($this->requestHeader)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->requestHeader->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->batchModifyOrderInvoiceReq !== null) {
			
			$xfer += $output->writeFieldBegin('batchModifyOrderInvoiceReq');
			
			if (!is_object($this->batchModifyOrderInvoiceReq)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->batchModifyOrderInvoiceReq->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_batchUpdateWmsFlag_args {
	
	static $_TSPEC;
	public $requestHeader = null;
	public $batchUpdateWmsFlagReq = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'requestHeader'
			),
			2 => array(
			'var' => 'batchUpdateWmsFlagReq'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['requestHeader'])){
				
				$this->requestHeader = $vals['requestHeader'];
			}
			
			
			if (isset($vals['batchUpdateWmsFlagReq'])){
				
				$this->batchUpdateWmsFlagReq = $vals['batchUpdateWmsFlagReq'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->requestHeader = new \com\vip\order\common\pojo\order\request\RequestHeader();
			$this->requestHeader->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->batchUpdateWmsFlagReq = new \com\vip\order\biz\request\BatchUpdateWmsFlagReq();
			$this->batchUpdateWmsFlagReq->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->requestHeader !== null) {
			
			$xfer += $output->writeFieldBegin('requestHeader');
			
			if (!is_object($this->requestHeader)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->requestHeader->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->batchUpdateWmsFlagReq !== null) {
			
			$xfer += $output->writeFieldBegin('batchUpdateWmsFlagReq');
			
			if (!is_object($this->batchUpdateWmsFlagReq)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->batchUpdateWmsFlagReq->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_calculateSplitOrderMoney_args {
	
	static $_TSPEC;
	public $header = null;
	public $req = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'header'
			),
			2 => array(
			'var' => 'req'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['header'])){
				
				$this->header = $vals['header'];
			}
			
			
			if (isset($vals['req'])){
				
				$this->req = $vals['req'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->header = new \com\vip\order\common\pojo\order\request\RequestHeader();
			$this->header->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->req = new \com\vip\order\biz\request\CalculateSplitOrderMoneyReq();
			$this->req->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->header !== null) {
			
			$xfer += $output->writeFieldBegin('header');
			
			if (!is_object($this->header)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->header->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->req !== null) {
			
			$xfer += $output->writeFieldBegin('req');
			
			if (!is_object($this->req)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->req->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_cancelOFixData_args {
	
	static $_TSPEC;
	public $requestHeader = null;
	public $cancelOrderFixDataReq = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'requestHeader'
			),
			2 => array(
			'var' => 'cancelOrderFixDataReq'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['requestHeader'])){
				
				$this->requestHeader = $vals['requestHeader'];
			}
			
			
			if (isset($vals['cancelOrderFixDataReq'])){
				
				$this->cancelOrderFixDataReq = $vals['cancelOrderFixDataReq'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->requestHeader = new \com\vip\order\common\pojo\order\request\RequestHeader();
			$this->requestHeader->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->cancelOrderFixDataReq = new \com\vip\order\biz\request\CancelOrderFixDataReq();
			$this->cancelOrderFixDataReq->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->requestHeader !== null) {
			
			$xfer += $output->writeFieldBegin('requestHeader');
			
			if (!is_object($this->requestHeader)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->requestHeader->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->cancelOrderFixDataReq !== null) {
			
			$xfer += $output->writeFieldBegin('cancelOrderFixDataReq');
			
			if (!is_object($this->cancelOrderFixDataReq)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->cancelOrderFixDataReq->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_cancelOrder_args {
	
	static $_TSPEC;
	public $requestHeader = null;
	public $reqParam = null;
	public $privParam = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'requestHeader'
			),
			2 => array(
			'var' => 'reqParam'
			),
			3 => array(
			'var' => 'privParam'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['requestHeader'])){
				
				$this->requestHeader = $vals['requestHeader'];
			}
			
			
			if (isset($vals['reqParam'])){
				
				$this->reqParam = $vals['reqParam'];
			}
			
			
			if (isset($vals['privParam'])){
				
				$this->privParam = $vals['privParam'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->requestHeader = new \com\vip\order\common\pojo\order\request\RequestHeader();
			$this->requestHeader->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->reqParam = new \com\vip\order\biz\request\CancelOrderReq();
			$this->reqParam->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->privParam = new \com\vip\order\biz\request\CancelOrderPrivilegeReq();
			$this->privParam->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->requestHeader !== null) {
			
			$xfer += $output->writeFieldBegin('requestHeader');
			
			if (!is_object($this->requestHeader)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->requestHeader->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->reqParam !== null) {
			
			$xfer += $output->writeFieldBegin('reqParam');
			
			if (!is_object($this->reqParam)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->reqParam->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->privParam !== null) {
			
			$xfer += $output->writeFieldBegin('privParam');
			
			if (!is_object($this->privParam)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->privParam->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_cancelPresellOrder_args {
	
	static $_TSPEC;
	public $requestHeader = null;
	public $reqParam = null;
	public $privParam = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'requestHeader'
			),
			2 => array(
			'var' => 'reqParam'
			),
			3 => array(
			'var' => 'privParam'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['requestHeader'])){
				
				$this->requestHeader = $vals['requestHeader'];
			}
			
			
			if (isset($vals['reqParam'])){
				
				$this->reqParam = $vals['reqParam'];
			}
			
			
			if (isset($vals['privParam'])){
				
				$this->privParam = $vals['privParam'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->requestHeader = new \com\vip\order\common\pojo\order\request\RequestHeader();
			$this->requestHeader->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->reqParam = new \com\vip\order\biz\request\CancelOrderReq();
			$this->reqParam->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->privParam = new \com\vip\order\biz\request\CancelOrderPrivilegeReq();
			$this->privParam->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->requestHeader !== null) {
			
			$xfer += $output->writeFieldBegin('requestHeader');
			
			if (!is_object($this->requestHeader)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->requestHeader->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->reqParam !== null) {
			
			$xfer += $output->writeFieldBegin('reqParam');
			
			if (!is_object($this->reqParam)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->reqParam->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->privParam !== null) {
			
			$xfer += $output->writeFieldBegin('privParam');
			
			if (!is_object($this->privParam)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->privParam->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_checkCashOnDelivery_args {
	
	static $_TSPEC;
	public $requestHeader = null;
	public $checkCashOnDeliveryReq = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'requestHeader'
			),
			2 => array(
			'var' => 'checkCashOnDeliveryReq'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['requestHeader'])){
				
				$this->requestHeader = $vals['requestHeader'];
			}
			
			
			if (isset($vals['checkCashOnDeliveryReq'])){
				
				$this->checkCashOnDeliveryReq = $vals['checkCashOnDeliveryReq'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->requestHeader = new \com\vip\order\common\pojo\order\request\RequestHeader();
			$this->requestHeader->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->checkCashOnDeliveryReq = new \com\vip\order\biz\request\CheckCashOnDeliveryReq();
			$this->checkCashOnDeliveryReq->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->requestHeader !== null) {
			
			$xfer += $output->writeFieldBegin('requestHeader');
			
			if (!is_object($this->requestHeader)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->requestHeader->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->checkCashOnDeliveryReq !== null) {
			
			$xfer += $output->writeFieldBegin('checkCashOnDeliveryReq');
			
			if (!is_object($this->checkCashOnDeliveryReq)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->checkCashOnDeliveryReq->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_checkDeliveryFetchExchange_args {
	
	static $_TSPEC;
	public $requestHeader = null;
	public $checkDeliveryFetchExchangeReq = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'requestHeader'
			),
			2 => array(
			'var' => 'checkDeliveryFetchExchangeReq'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['requestHeader'])){
				
				$this->requestHeader = $vals['requestHeader'];
			}
			
			
			if (isset($vals['checkDeliveryFetchExchangeReq'])){
				
				$this->checkDeliveryFetchExchangeReq = $vals['checkDeliveryFetchExchangeReq'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->requestHeader = new \com\vip\order\common\pojo\order\request\RequestHeader();
			$this->requestHeader->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->checkDeliveryFetchExchangeReq = new \com\vip\order\biz\request\CheckDeliveryFetchExchangeReq();
			$this->checkDeliveryFetchExchangeReq->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->requestHeader !== null) {
			
			$xfer += $output->writeFieldBegin('requestHeader');
			
			if (!is_object($this->requestHeader)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->requestHeader->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->checkDeliveryFetchExchangeReq !== null) {
			
			$xfer += $output->writeFieldBegin('checkDeliveryFetchExchangeReq');
			
			if (!is_object($this->checkDeliveryFetchExchangeReq)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->checkDeliveryFetchExchangeReq->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_checkDeliveryFetchReturn_args {
	
	static $_TSPEC;
	public $requestHeader = null;
	public $checkDeliveryFetchReturnReq = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'requestHeader'
			),
			2 => array(
			'var' => 'checkDeliveryFetchReturnReq'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['requestHeader'])){
				
				$this->requestHeader = $vals['requestHeader'];
			}
			
			
			if (isset($vals['checkDeliveryFetchReturnReq'])){
				
				$this->checkDeliveryFetchReturnReq = $vals['checkDeliveryFetchReturnReq'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->requestHeader = new \com\vip\order\common\pojo\order\request\RequestHeader();
			$this->requestHeader->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->checkDeliveryFetchReturnReq = new \com\vip\order\biz\request\CheckDeliveryFetchReturnReq();
			$this->checkDeliveryFetchReturnReq->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->requestHeader !== null) {
			
			$xfer += $output->writeFieldBegin('requestHeader');
			
			if (!is_object($this->requestHeader)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->requestHeader->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->checkDeliveryFetchReturnReq !== null) {
			
			$xfer += $output->writeFieldBegin('checkDeliveryFetchReturnReq');
			
			if (!is_object($this->checkDeliveryFetchReturnReq)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->checkDeliveryFetchReturnReq->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_checkOrderReturnVendorAudit_args {
	
	static $_TSPEC;
	public $requestHeader = null;
	public $checkOrderReturnVendorAuditReq = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'requestHeader'
			),
			2 => array(
			'var' => 'checkOrderReturnVendorAuditReq'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['requestHeader'])){
				
				$this->requestHeader = $vals['requestHeader'];
			}
			
			
			if (isset($vals['checkOrderReturnVendorAuditReq'])){
				
				$this->checkOrderReturnVendorAuditReq = $vals['checkOrderReturnVendorAuditReq'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->requestHeader = new \com\vip\order\common\pojo\order\request\RequestHeader();
			$this->requestHeader->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->checkOrderReturnVendorAuditReq = new \com\vip\order\biz\request\CheckOrderReturnVendorAuditReq();
			$this->checkOrderReturnVendorAuditReq->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->requestHeader !== null) {
			
			$xfer += $output->writeFieldBegin('requestHeader');
			
			if (!is_object($this->requestHeader)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->requestHeader->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->checkOrderReturnVendorAuditReq !== null) {
			
			$xfer += $output->writeFieldBegin('checkOrderReturnVendorAuditReq');
			
			if (!is_object($this->checkOrderReturnVendorAuditReq)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->checkOrderReturnVendorAuditReq->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_confirmDelivered_args {
	
	static $_TSPEC;
	public $header = null;
	public $req = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'header'
			),
			2 => array(
			'var' => 'req'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['header'])){
				
				$this->header = $vals['header'];
			}
			
			
			if (isset($vals['req'])){
				
				$this->req = $vals['req'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->header = new \com\vip\order\common\pojo\order\request\RequestHeader();
			$this->header->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->req = new \com\vip\order\biz\request\ConfirmDeliveredReq();
			$this->req->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->header !== null) {
			
			$xfer += $output->writeFieldBegin('header');
			
			if (!is_object($this->header)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->header->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->req !== null) {
			
			$xfer += $output->writeFieldBegin('req');
			
			if (!is_object($this->req)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->req->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_confirmOrderGroupBuyResult_args {
	
	static $_TSPEC;
	public $requestHeader = null;
	public $req = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'requestHeader'
			),
			2 => array(
			'var' => 'req'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['requestHeader'])){
				
				$this->requestHeader = $vals['requestHeader'];
			}
			
			
			if (isset($vals['req'])){
				
				$this->req = $vals['req'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->requestHeader = new \com\vip\order\common\pojo\order\request\RequestHeader();
			$this->requestHeader->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->req = new \com\vip\order\biz\request\ConfirmOrderGroupBuyReq();
			$this->req->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->requestHeader !== null) {
			
			$xfer += $output->writeFieldBegin('requestHeader');
			
			if (!is_object($this->requestHeader)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->requestHeader->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->req !== null) {
			
			$xfer += $output->writeFieldBegin('req');
			
			if (!is_object($this->req)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->req->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_createOrder_args {
	
	static $_TSPEC;
	public $requestHeader = null;
	public $orderCategory = null;
	public $createOrderParam = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'requestHeader'
			),
			2 => array(
			'var' => 'orderCategory'
			),
			3 => array(
			'var' => 'createOrderParam'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['requestHeader'])){
				
				$this->requestHeader = $vals['requestHeader'];
			}
			
			
			if (isset($vals['orderCategory'])){
				
				$this->orderCategory = $vals['orderCategory'];
			}
			
			
			if (isset($vals['createOrderParam'])){
				
				$this->createOrderParam = $vals['createOrderParam'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->requestHeader = new \com\vip\order\biz\request\RequestHeader();
			$this->requestHeader->read($input);
			
		}
		
		
		
		
		if(true) {
			
			$input->readI32($this->orderCategory); 
			
		}
		
		
		
		
		if(true) {
			
			
			$this->createOrderParam = array();
			$_size0 = 0;
			$input->readListBegin();
			while(true){
				
				try{
					
					$elem0 = null;
					
					$elem0 = new \com\vip\order\biz\request\CreateOrderReq();
					$elem0->read($input);
					
					$this->createOrderParam[$_size0++] = $elem0;
				}
				catch(\Exception $e){
					
					break;
				}
			}
			
			$input->readListEnd();
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->requestHeader !== null) {
			
			$xfer += $output->writeFieldBegin('requestHeader');
			
			if (!is_object($this->requestHeader)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->requestHeader->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldBegin('orderCategory');
		$xfer += $output->writeI32($this->orderCategory);
		
		$xfer += $output->writeFieldEnd();
		
		if($this->createOrderParam !== null) {
			
			$xfer += $output->writeFieldBegin('createOrderParam');
			
			if (!is_array($this->createOrderParam)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeListBegin();
			foreach ($this->createOrderParam as $iter0){
				
				
				if (!is_object($iter0)) {
					
					throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
				}
				
				$xfer += $iter0->write($output);
				
			}
			
			$output->writeListEnd();
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_createOrderElectronicInvoice_args {
	
	static $_TSPEC;
	public $requestHeader = null;
	public $createOrderElectronicInvoiceReq = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'requestHeader'
			),
			2 => array(
			'var' => 'createOrderElectronicInvoiceReq'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['requestHeader'])){
				
				$this->requestHeader = $vals['requestHeader'];
			}
			
			
			if (isset($vals['createOrderElectronicInvoiceReq'])){
				
				$this->createOrderElectronicInvoiceReq = $vals['createOrderElectronicInvoiceReq'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->requestHeader = new \com\vip\order\common\pojo\order\request\RequestHeader();
			$this->requestHeader->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->createOrderElectronicInvoiceReq = new \com\vip\order\biz\request\CreateOrderElectronicInvoiceReq();
			$this->createOrderElectronicInvoiceReq->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->requestHeader !== null) {
			
			$xfer += $output->writeFieldBegin('requestHeader');
			
			if (!is_object($this->requestHeader)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->requestHeader->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->createOrderElectronicInvoiceReq !== null) {
			
			$xfer += $output->writeFieldBegin('createOrderElectronicInvoiceReq');
			
			if (!is_object($this->createOrderElectronicInvoiceReq)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->createOrderElectronicInvoiceReq->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_createOrderSnV2_args {
	
	static $_TSPEC;
	public $requestHeader = null;
	public $warehouse = null;
	public $number = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'requestHeader'
			),
			2 => array(
			'var' => 'warehouse'
			),
			3 => array(
			'var' => 'number'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['requestHeader'])){
				
				$this->requestHeader = $vals['requestHeader'];
			}
			
			
			if (isset($vals['warehouse'])){
				
				$this->warehouse = $vals['warehouse'];
			}
			
			
			if (isset($vals['number'])){
				
				$this->number = $vals['number'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->requestHeader = new \com\vip\order\common\pojo\order\request\RequestHeader();
			$this->requestHeader->read($input);
			
		}
		
		
		
		
		if(true) {
			
			$input->readString($this->warehouse);
			
		}
		
		
		
		
		if(true) {
			
			$input->readI32($this->number); 
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->requestHeader !== null) {
			
			$xfer += $output->writeFieldBegin('requestHeader');
			
			if (!is_object($this->requestHeader)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->requestHeader->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->warehouse !== null) {
			
			$xfer += $output->writeFieldBegin('warehouse');
			$xfer += $output->writeString($this->warehouse);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldBegin('number');
		$xfer += $output->writeI32($this->number);
		
		$xfer += $output->writeFieldEnd();
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_createOrderV2_args {
	
	static $_TSPEC;
	public $requestHeader = null;
	public $orderCategory = null;
	public $createOrderParam = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'requestHeader'
			),
			2 => array(
			'var' => 'orderCategory'
			),
			3 => array(
			'var' => 'createOrderParam'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['requestHeader'])){
				
				$this->requestHeader = $vals['requestHeader'];
			}
			
			
			if (isset($vals['orderCategory'])){
				
				$this->orderCategory = $vals['orderCategory'];
			}
			
			
			if (isset($vals['createOrderParam'])){
				
				$this->createOrderParam = $vals['createOrderParam'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->requestHeader = new \com\vip\order\common\pojo\order\request\RequestHeader();
			$this->requestHeader->read($input);
			
		}
		
		
		
		
		if(true) {
			
			$input->readI32($this->orderCategory); 
			
		}
		
		
		
		
		if(true) {
			
			
			$this->createOrderParam = array();
			$_size0 = 0;
			$input->readListBegin();
			while(true){
				
				try{
					
					$elem0 = null;
					
					$elem0 = new \com\vip\order\biz\request\CreateOrderReqV2();
					$elem0->read($input);
					
					$this->createOrderParam[$_size0++] = $elem0;
				}
				catch(\Exception $e){
					
					break;
				}
			}
			
			$input->readListEnd();
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->requestHeader !== null) {
			
			$xfer += $output->writeFieldBegin('requestHeader');
			
			if (!is_object($this->requestHeader)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->requestHeader->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldBegin('orderCategory');
		$xfer += $output->writeI32($this->orderCategory);
		
		$xfer += $output->writeFieldEnd();
		
		if($this->createOrderParam !== null) {
			
			$xfer += $output->writeFieldBegin('createOrderParam');
			
			if (!is_array($this->createOrderParam)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeListBegin();
			foreach ($this->createOrderParam as $iter0){
				
				
				if (!is_object($iter0)) {
					
					throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
				}
				
				$xfer += $iter0->write($output);
				
			}
			
			$output->writeListEnd();
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_cscCancelBack_args {
	
	static $_TSPEC;
	public $header = null;
	public $req = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'header'
			),
			2 => array(
			'var' => 'req'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['header'])){
				
				$this->header = $vals['header'];
			}
			
			
			if (isset($vals['req'])){
				
				$this->req = $vals['req'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->header = new \com\vip\order\common\pojo\order\request\RequestHeader();
			$this->header->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->req = new \com\vip\order\biz\request\CSCCancelBackReq();
			$this->req->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->header !== null) {
			
			$xfer += $output->writeFieldBegin('header');
			
			if (!is_object($this->header)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->header->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->req !== null) {
			
			$xfer += $output->writeFieldBegin('req');
			
			if (!is_object($this->req)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->req->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_displayOrder_args {
	
	static $_TSPEC;
	public $requestHeader = null;
	public $req = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'requestHeader'
			),
			2 => array(
			'var' => 'req'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['requestHeader'])){
				
				$this->requestHeader = $vals['requestHeader'];
			}
			
			
			if (isset($vals['req'])){
				
				$this->req = $vals['req'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->requestHeader = new \com\vip\order\common\pojo\order\request\RequestHeader();
			$this->requestHeader->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->req = new \com\vip\order\biz\request\DisplayOrderReq();
			$this->req->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->requestHeader !== null) {
			
			$xfer += $output->writeFieldBegin('requestHeader');
			
			if (!is_object($this->requestHeader)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->requestHeader->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->req !== null) {
			
			$xfer += $output->writeFieldBegin('req');
			
			if (!is_object($this->req)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->req->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_getAfterSaleOpType_args {
	
	static $_TSPEC;
	public $requestHeader = null;
	public $req = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'requestHeader'
			),
			2 => array(
			'var' => 'req'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['requestHeader'])){
				
				$this->requestHeader = $vals['requestHeader'];
			}
			
			
			if (isset($vals['req'])){
				
				$this->req = $vals['req'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->requestHeader = new \com\vip\order\common\pojo\order\request\RequestHeader();
			$this->requestHeader->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->req = new \com\vip\order\biz\request\GetAfterSaleOpTypeReq();
			$this->req->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->requestHeader !== null) {
			
			$xfer += $output->writeFieldBegin('requestHeader');
			
			if (!is_object($this->requestHeader)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->requestHeader->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->req !== null) {
			
			$xfer += $output->writeFieldBegin('req');
			
			if (!is_object($this->req)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->req->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_getCanAfterSaleOrderListByUserId_args {
	
	static $_TSPEC;
	public $header = null;
	public $req = null;
	public $pageResultFilter = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'header'
			),
			2 => array(
			'var' => 'req'
			),
			3 => array(
			'var' => 'pageResultFilter'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['header'])){
				
				$this->header = $vals['header'];
			}
			
			
			if (isset($vals['req'])){
				
				$this->req = $vals['req'];
			}
			
			
			if (isset($vals['pageResultFilter'])){
				
				$this->pageResultFilter = $vals['pageResultFilter'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->header = new \com\vip\order\common\pojo\order\request\RequestHeader();
			$this->header->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->req = new \com\vip\order\biz\request\GetCanAfterSaleOrderListReq();
			$this->req->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->pageResultFilter = new \com\vip\order\common\pojo\order\request\PageResultFilter();
			$this->pageResultFilter->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->header !== null) {
			
			$xfer += $output->writeFieldBegin('header');
			
			if (!is_object($this->header)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->header->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->req !== null) {
			
			$xfer += $output->writeFieldBegin('req');
			
			if (!is_object($this->req)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->req->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->pageResultFilter !== null) {
			
			$xfer += $output->writeFieldBegin('pageResultFilter');
			
			if (!is_object($this->pageResultFilter)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->pageResultFilter->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_getCanRefundOrderCount_args {
	
	static $_TSPEC;
	public $requestHeader = null;
	public $getCanRefundOrderCountReq = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'requestHeader'
			),
			2 => array(
			'var' => 'getCanRefundOrderCountReq'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['requestHeader'])){
				
				$this->requestHeader = $vals['requestHeader'];
			}
			
			
			if (isset($vals['getCanRefundOrderCountReq'])){
				
				$this->getCanRefundOrderCountReq = $vals['getCanRefundOrderCountReq'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->requestHeader = new \com\vip\order\common\pojo\order\request\RequestHeader();
			$this->requestHeader->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->getCanRefundOrderCountReq = new \com\vip\order\biz\request\GetCanRefundOrderCountReq();
			$this->getCanRefundOrderCountReq->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->requestHeader !== null) {
			
			$xfer += $output->writeFieldBegin('requestHeader');
			
			if (!is_object($this->requestHeader)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->requestHeader->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->getCanRefundOrderCountReq !== null) {
			
			$xfer += $output->writeFieldBegin('getCanRefundOrderCountReq');
			
			if (!is_object($this->getCanRefundOrderCountReq)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->getCanRefundOrderCountReq->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_getCanRefundOrderList_args {
	
	static $_TSPEC;
	public $requestHeader = null;
	public $getCanRefundOrderListReq = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'requestHeader'
			),
			2 => array(
			'var' => 'getCanRefundOrderListReq'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['requestHeader'])){
				
				$this->requestHeader = $vals['requestHeader'];
			}
			
			
			if (isset($vals['getCanRefundOrderListReq'])){
				
				$this->getCanRefundOrderListReq = $vals['getCanRefundOrderListReq'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->requestHeader = new \com\vip\order\common\pojo\order\request\RequestHeader();
			$this->requestHeader->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->getCanRefundOrderListReq = new \com\vip\order\biz\request\GetCanRefundOrderListReq();
			$this->getCanRefundOrderListReq->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->requestHeader !== null) {
			
			$xfer += $output->writeFieldBegin('requestHeader');
			
			if (!is_object($this->requestHeader)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->requestHeader->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->getCanRefundOrderListReq !== null) {
			
			$xfer += $output->writeFieldBegin('getCanRefundOrderListReq');
			
			if (!is_object($this->getCanRefundOrderListReq)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->getCanRefundOrderListReq->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_getEbsGoodsList_args {
	
	static $_TSPEC;
	public $requestHeader = null;
	public $getEbsGoodsListReq = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'requestHeader'
			),
			2 => array(
			'var' => 'getEbsGoodsListReq'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['requestHeader'])){
				
				$this->requestHeader = $vals['requestHeader'];
			}
			
			
			if (isset($vals['getEbsGoodsListReq'])){
				
				$this->getEbsGoodsListReq = $vals['getEbsGoodsListReq'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->requestHeader = new \com\vip\order\common\pojo\order\request\RequestHeader();
			$this->requestHeader->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->getEbsGoodsListReq = new \com\vip\order\biz\request\GetEbsGoodsListReq();
			$this->getEbsGoodsListReq->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->requestHeader !== null) {
			
			$xfer += $output->writeFieldBegin('requestHeader');
			
			if (!is_object($this->requestHeader)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->requestHeader->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->getEbsGoodsListReq !== null) {
			
			$xfer += $output->writeFieldBegin('getEbsGoodsListReq');
			
			if (!is_object($this->getEbsGoodsListReq)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->getEbsGoodsListReq->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_getGoodsDispatchWarehouse_args {
	
	static $_TSPEC;
	public $requestHeader = null;
	public $req = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'requestHeader'
			),
			2 => array(
			'var' => 'req'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['requestHeader'])){
				
				$this->requestHeader = $vals['requestHeader'];
			}
			
			
			if (isset($vals['req'])){
				
				$this->req = $vals['req'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->requestHeader = new \com\vip\order\common\pojo\order\request\RequestHeader();
			$this->requestHeader->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->req = new \com\vip\order\biz\request\GetGoodsDispatchWarehouseReq();
			$this->req->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->requestHeader !== null) {
			
			$xfer += $output->writeFieldBegin('requestHeader');
			
			if (!is_object($this->requestHeader)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->requestHeader->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->req !== null) {
			
			$xfer += $output->writeFieldBegin('req');
			
			if (!is_object($this->req)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->req->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_getLimitedOrderGoodsCount_args {
	
	static $_TSPEC;
	public $requestHeader = null;
	public $getLimitedOrderGoodsCountReq = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'requestHeader'
			),
			2 => array(
			'var' => 'getLimitedOrderGoodsCountReq'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['requestHeader'])){
				
				$this->requestHeader = $vals['requestHeader'];
			}
			
			
			if (isset($vals['getLimitedOrderGoodsCountReq'])){
				
				$this->getLimitedOrderGoodsCountReq = $vals['getLimitedOrderGoodsCountReq'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->requestHeader = new \com\vip\order\common\pojo\order\request\RequestHeader();
			$this->requestHeader->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->getLimitedOrderGoodsCountReq = new \com\vip\order\biz\request\GetLimitedOrderGoodsCountReq();
			$this->getLimitedOrderGoodsCountReq->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->requestHeader !== null) {
			
			$xfer += $output->writeFieldBegin('requestHeader');
			
			if (!is_object($this->requestHeader)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->requestHeader->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->getLimitedOrderGoodsCountReq !== null) {
			
			$xfer += $output->writeFieldBegin('getLimitedOrderGoodsCountReq');
			
			if (!is_object($this->getLimitedOrderGoodsCountReq)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->getLimitedOrderGoodsCountReq->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_getMergeOrderList_args {
	
	static $_TSPEC;
	public $requestHeader = null;
	public $getMergeOrderReq = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'requestHeader'
			),
			2 => array(
			'var' => 'getMergeOrderReq'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['requestHeader'])){
				
				$this->requestHeader = $vals['requestHeader'];
			}
			
			
			if (isset($vals['getMergeOrderReq'])){
				
				$this->getMergeOrderReq = $vals['getMergeOrderReq'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->requestHeader = new \com\vip\order\common\pojo\order\request\RequestHeader();
			$this->requestHeader->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->getMergeOrderReq = new \com\vip\order\biz\request\GetMergeOrderReq();
			$this->getMergeOrderReq->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->requestHeader !== null) {
			
			$xfer += $output->writeFieldBegin('requestHeader');
			
			if (!is_object($this->requestHeader)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->requestHeader->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->getMergeOrderReq !== null) {
			
			$xfer += $output->writeFieldBegin('getMergeOrderReq');
			
			if (!is_object($this->getMergeOrderReq)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->getMergeOrderReq->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_getOrderCounts_args {
	
	static $_TSPEC;
	public $requestHeader = null;
	public $searchOrderReq = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'requestHeader'
			),
			2 => array(
			'var' => 'searchOrderReq'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['requestHeader'])){
				
				$this->requestHeader = $vals['requestHeader'];
			}
			
			
			if (isset($vals['searchOrderReq'])){
				
				$this->searchOrderReq = $vals['searchOrderReq'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->requestHeader = new \com\vip\order\common\pojo\order\request\RequestHeader();
			$this->requestHeader->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->searchOrderReq = new \com\vip\order\biz\request\SearchOrderReq();
			$this->searchOrderReq->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->requestHeader !== null) {
			
			$xfer += $output->writeFieldBegin('requestHeader');
			
			if (!is_object($this->requestHeader)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->requestHeader->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->searchOrderReq !== null) {
			
			$xfer += $output->writeFieldBegin('searchOrderReq');
			
			if (!is_object($this->searchOrderReq)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->searchOrderReq->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_getOrderCountsByUserId_args {
	
	static $_TSPEC;
	public $requestHeader = null;
	public $getOrderByUserIdReq = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'requestHeader'
			),
			2 => array(
			'var' => 'getOrderByUserIdReq'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['requestHeader'])){
				
				$this->requestHeader = $vals['requestHeader'];
			}
			
			
			if (isset($vals['getOrderByUserIdReq'])){
				
				$this->getOrderByUserIdReq = $vals['getOrderByUserIdReq'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->requestHeader = new \com\vip\order\common\pojo\order\request\RequestHeader();
			$this->requestHeader->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->getOrderByUserIdReq = new \com\vip\order\biz\request\GetOrderByUserIdReq();
			$this->getOrderByUserIdReq->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->requestHeader !== null) {
			
			$xfer += $output->writeFieldBegin('requestHeader');
			
			if (!is_object($this->requestHeader)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->requestHeader->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->getOrderByUserIdReq !== null) {
			
			$xfer += $output->writeFieldBegin('getOrderByUserIdReq');
			
			if (!is_object($this->getOrderByUserIdReq)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->getOrderByUserIdReq->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_getOrderDeliveryBoxNum_args {
	
	static $_TSPEC;
	public $requestHeaderParam = null;
	public $orderSn = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'requestHeaderParam'
			),
			2 => array(
			'var' => 'orderSn'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['requestHeaderParam'])){
				
				$this->requestHeaderParam = $vals['requestHeaderParam'];
			}
			
			
			if (isset($vals['orderSn'])){
				
				$this->orderSn = $vals['orderSn'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->requestHeaderParam = new \com\vip\order\common\pojo\order\request\RequestHeader();
			$this->requestHeaderParam->read($input);
			
		}
		
		
		
		
		if(true) {
			
			$input->readString($this->orderSn);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->requestHeaderParam !== null) {
			
			$xfer += $output->writeFieldBegin('requestHeaderParam');
			
			if (!is_object($this->requestHeaderParam)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->requestHeaderParam->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderSn !== null) {
			
			$xfer += $output->writeFieldBegin('orderSn');
			$xfer += $output->writeString($this->orderSn);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_getOrderDetail_args {
	
	static $_TSPEC;
	public $requestHeader = null;
	public $searchOrderDetailReq = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'requestHeader'
			),
			2 => array(
			'var' => 'searchOrderDetailReq'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['requestHeader'])){
				
				$this->requestHeader = $vals['requestHeader'];
			}
			
			
			if (isset($vals['searchOrderDetailReq'])){
				
				$this->searchOrderDetailReq = $vals['searchOrderDetailReq'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->requestHeader = new \com\vip\order\common\pojo\order\request\RequestHeader();
			$this->requestHeader->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->searchOrderDetailReq = new \com\vip\order\biz\request\SearchOrderDetailReq();
			$this->searchOrderDetailReq->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->requestHeader !== null) {
			
			$xfer += $output->writeFieldBegin('requestHeader');
			
			if (!is_object($this->requestHeader)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->requestHeader->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->searchOrderDetailReq !== null) {
			
			$xfer += $output->writeFieldBegin('searchOrderDetailReq');
			
			if (!is_object($this->searchOrderDetailReq)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->searchOrderDetailReq->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_getOrderElectronicInvoicesV2_args {
	
	static $_TSPEC;
	public $requestHeaderParam = null;
	public $searchOrderElectronicInvoiceParam = null;
	public $resultRequirement = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'requestHeaderParam'
			),
			2 => array(
			'var' => 'searchOrderElectronicInvoiceParam'
			),
			3 => array(
			'var' => 'resultRequirement'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['requestHeaderParam'])){
				
				$this->requestHeaderParam = $vals['requestHeaderParam'];
			}
			
			
			if (isset($vals['searchOrderElectronicInvoiceParam'])){
				
				$this->searchOrderElectronicInvoiceParam = $vals['searchOrderElectronicInvoiceParam'];
			}
			
			
			if (isset($vals['resultRequirement'])){
				
				$this->resultRequirement = $vals['resultRequirement'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->requestHeaderParam = new \com\vip\order\common\pojo\order\request\RequestHeader();
			$this->requestHeaderParam->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->searchOrderElectronicInvoiceParam = new \com\vip\order\biz\request\SearchOrderElectronicInvoicesReq();
			$this->searchOrderElectronicInvoiceParam->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->resultRequirement = new \com\vip\order\biz\request\ResultRequirement();
			$this->resultRequirement->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->requestHeaderParam !== null) {
			
			$xfer += $output->writeFieldBegin('requestHeaderParam');
			
			if (!is_object($this->requestHeaderParam)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->requestHeaderParam->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->searchOrderElectronicInvoiceParam !== null) {
			
			$xfer += $output->writeFieldBegin('searchOrderElectronicInvoiceParam');
			
			if (!is_object($this->searchOrderElectronicInvoiceParam)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->searchOrderElectronicInvoiceParam->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->resultRequirement !== null) {
			
			$xfer += $output->writeFieldBegin('resultRequirement');
			
			if (!is_object($this->resultRequirement)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->resultRequirement->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_getOrderFav_args {
	
	static $_TSPEC;
	public $requestHeader = null;
	public $orderSnList = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'requestHeader'
			),
			2 => array(
			'var' => 'orderSnList'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['requestHeader'])){
				
				$this->requestHeader = $vals['requestHeader'];
			}
			
			
			if (isset($vals['orderSnList'])){
				
				$this->orderSnList = $vals['orderSnList'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->requestHeader = new \com\vip\order\common\pojo\order\request\RequestHeader();
			$this->requestHeader->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->orderSnList = array();
			$_size0 = 0;
			$input->readListBegin();
			while(true){
				
				try{
					
					$elem0 = null;
					$input->readString($elem0);
					
					$this->orderSnList[$_size0++] = $elem0;
				}
				catch(\Exception $e){
					
					break;
				}
			}
			
			$input->readListEnd();
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->requestHeader !== null) {
			
			$xfer += $output->writeFieldBegin('requestHeader');
			
			if (!is_object($this->requestHeader)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->requestHeader->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderSnList !== null) {
			
			$xfer += $output->writeFieldBegin('orderSnList');
			
			if (!is_array($this->orderSnList)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeListBegin();
			foreach ($this->orderSnList as $iter0){
				
				$xfer += $output->writeString($iter0);
				
			}
			
			$output->writeListEnd();
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_getOrderGoodsCount_args {
	
	static $_TSPEC;
	public $requestHeaderParam = null;
	public $getOrderGoodsParam = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'requestHeaderParam'
			),
			2 => array(
			'var' => 'getOrderGoodsParam'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['requestHeaderParam'])){
				
				$this->requestHeaderParam = $vals['requestHeaderParam'];
			}
			
			
			if (isset($vals['getOrderGoodsParam'])){
				
				$this->getOrderGoodsParam = $vals['getOrderGoodsParam'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->requestHeaderParam = new \com\vip\order\common\pojo\order\request\RequestHeader();
			$this->requestHeaderParam->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->getOrderGoodsParam = new \com\vip\order\biz\request\GetOrderGoodsReq();
			$this->getOrderGoodsParam->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->requestHeaderParam !== null) {
			
			$xfer += $output->writeFieldBegin('requestHeaderParam');
			
			if (!is_object($this->requestHeaderParam)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->requestHeaderParam->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->getOrderGoodsParam !== null) {
			
			$xfer += $output->writeFieldBegin('getOrderGoodsParam');
			
			if (!is_object($this->getOrderGoodsParam)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->getOrderGoodsParam->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_getOrderGoodsList_args {
	
	static $_TSPEC;
	public $requestHeaderParam = null;
	public $getOrderGoodsParam = null;
	public $resultFilter = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'requestHeaderParam'
			),
			2 => array(
			'var' => 'getOrderGoodsParam'
			),
			3 => array(
			'var' => 'resultFilter'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['requestHeaderParam'])){
				
				$this->requestHeaderParam = $vals['requestHeaderParam'];
			}
			
			
			if (isset($vals['getOrderGoodsParam'])){
				
				$this->getOrderGoodsParam = $vals['getOrderGoodsParam'];
			}
			
			
			if (isset($vals['resultFilter'])){
				
				$this->resultFilter = $vals['resultFilter'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->requestHeaderParam = new \com\vip\order\common\pojo\order\request\RequestHeader();
			$this->requestHeaderParam->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->getOrderGoodsParam = new \com\vip\order\biz\request\GetOrderGoodsReq();
			$this->getOrderGoodsParam->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->resultFilter = new \com\vip\order\common\pojo\order\request\ResultFilter();
			$this->resultFilter->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->requestHeaderParam !== null) {
			
			$xfer += $output->writeFieldBegin('requestHeaderParam');
			
			if (!is_object($this->requestHeaderParam)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->requestHeaderParam->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->getOrderGoodsParam !== null) {
			
			$xfer += $output->writeFieldBegin('getOrderGoodsParam');
			
			if (!is_object($this->getOrderGoodsParam)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->getOrderGoodsParam->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->resultFilter !== null) {
			
			$xfer += $output->writeFieldBegin('resultFilter');
			
			if (!is_object($this->resultFilter)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->resultFilter->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_getOrderInstalmentsList_args {
	
	static $_TSPEC;
	public $requestHeader = null;
	public $req = null;
	public $filter = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'requestHeader'
			),
			2 => array(
			'var' => 'req'
			),
			3 => array(
			'var' => 'filter'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['requestHeader'])){
				
				$this->requestHeader = $vals['requestHeader'];
			}
			
			
			if (isset($vals['req'])){
				
				$this->req = $vals['req'];
			}
			
			
			if (isset($vals['filter'])){
				
				$this->filter = $vals['filter'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->requestHeader = new \com\vip\order\common\pojo\order\request\RequestHeader();
			$this->requestHeader->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->req = new \com\vip\order\biz\request\GetOrderInstalmentsReq();
			$this->req->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->filter = new \com\vip\order\common\pojo\order\request\ResultFilter();
			$this->filter->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->requestHeader !== null) {
			
			$xfer += $output->writeFieldBegin('requestHeader');
			
			if (!is_object($this->requestHeader)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->requestHeader->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->req !== null) {
			
			$xfer += $output->writeFieldBegin('req');
			
			if (!is_object($this->req)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->req->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->filter !== null) {
			
			$xfer += $output->writeFieldBegin('filter');
			
			if (!is_object($this->filter)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->filter->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_getOrderInvoicesV2_args {
	
	static $_TSPEC;
	public $requestHeaderParam = null;
	public $searchOrderInvoiceParam = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'requestHeaderParam'
			),
			2 => array(
			'var' => 'searchOrderInvoiceParam'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['requestHeaderParam'])){
				
				$this->requestHeaderParam = $vals['requestHeaderParam'];
			}
			
			
			if (isset($vals['searchOrderInvoiceParam'])){
				
				$this->searchOrderInvoiceParam = $vals['searchOrderInvoiceParam'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->requestHeaderParam = new \com\vip\order\common\pojo\order\request\RequestHeader();
			$this->requestHeaderParam->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->searchOrderInvoiceParam = new \com\vip\order\biz\request\SearchOrderInvoicesReq();
			$this->searchOrderInvoiceParam->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->requestHeaderParam !== null) {
			
			$xfer += $output->writeFieldBegin('requestHeaderParam');
			
			if (!is_object($this->requestHeaderParam)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->requestHeaderParam->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->searchOrderInvoiceParam !== null) {
			
			$xfer += $output->writeFieldBegin('searchOrderInvoiceParam');
			
			if (!is_object($this->searchOrderInvoiceParam)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->searchOrderInvoiceParam->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_getOrderList_args {
	
	static $_TSPEC;
	public $requestHeader = null;
	public $searchOrderReq = null;
	public $resultFilter = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'requestHeader'
			),
			2 => array(
			'var' => 'searchOrderReq'
			),
			3 => array(
			'var' => 'resultFilter'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['requestHeader'])){
				
				$this->requestHeader = $vals['requestHeader'];
			}
			
			
			if (isset($vals['searchOrderReq'])){
				
				$this->searchOrderReq = $vals['searchOrderReq'];
			}
			
			
			if (isset($vals['resultFilter'])){
				
				$this->resultFilter = $vals['resultFilter'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->requestHeader = new \com\vip\order\common\pojo\order\request\RequestHeader();
			$this->requestHeader->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->searchOrderReq = new \com\vip\order\biz\request\SearchOrderReq();
			$this->searchOrderReq->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->resultFilter = new \com\vip\order\common\pojo\order\request\ResultFilter();
			$this->resultFilter->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->requestHeader !== null) {
			
			$xfer += $output->writeFieldBegin('requestHeader');
			
			if (!is_object($this->requestHeader)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->requestHeader->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->searchOrderReq !== null) {
			
			$xfer += $output->writeFieldBegin('searchOrderReq');
			
			if (!is_object($this->searchOrderReq)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->searchOrderReq->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->resultFilter !== null) {
			
			$xfer += $output->writeFieldBegin('resultFilter');
			
			if (!is_object($this->resultFilter)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->resultFilter->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_getOrderListByPosNo_args {
	
	static $_TSPEC;
	public $requestHeader = null;
	public $req = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'requestHeader'
			),
			2 => array(
			'var' => 'req'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['requestHeader'])){
				
				$this->requestHeader = $vals['requestHeader'];
			}
			
			
			if (isset($vals['req'])){
				
				$this->req = $vals['req'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->requestHeader = new \com\vip\order\common\pojo\order\request\RequestHeader();
			$this->requestHeader->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->req = new \com\vip\order\biz\request\GetOrderListByPosNoReq();
			$this->req->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->requestHeader !== null) {
			
			$xfer += $output->writeFieldBegin('requestHeader');
			
			if (!is_object($this->requestHeader)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->requestHeader->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->req !== null) {
			
			$xfer += $output->writeFieldBegin('req');
			
			if (!is_object($this->req)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->req->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_getOrderListByUserId_args {
	
	static $_TSPEC;
	public $requestHeader = null;
	public $getOrderByUserIdReq = null;
	public $resultFilter = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'requestHeader'
			),
			2 => array(
			'var' => 'getOrderByUserIdReq'
			),
			3 => array(
			'var' => 'resultFilter'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['requestHeader'])){
				
				$this->requestHeader = $vals['requestHeader'];
			}
			
			
			if (isset($vals['getOrderByUserIdReq'])){
				
				$this->getOrderByUserIdReq = $vals['getOrderByUserIdReq'];
			}
			
			
			if (isset($vals['resultFilter'])){
				
				$this->resultFilter = $vals['resultFilter'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->requestHeader = new \com\vip\order\common\pojo\order\request\RequestHeader();
			$this->requestHeader->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->getOrderByUserIdReq = new \com\vip\order\biz\request\GetOrderByUserIdReq();
			$this->getOrderByUserIdReq->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->resultFilter = new \com\vip\order\common\pojo\order\request\ResultFilter();
			$this->resultFilter->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->requestHeader !== null) {
			
			$xfer += $output->writeFieldBegin('requestHeader');
			
			if (!is_object($this->requestHeader)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->requestHeader->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->getOrderByUserIdReq !== null) {
			
			$xfer += $output->writeFieldBegin('getOrderByUserIdReq');
			
			if (!is_object($this->getOrderByUserIdReq)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->getOrderByUserIdReq->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->resultFilter !== null) {
			
			$xfer += $output->writeFieldBegin('resultFilter');
			
			if (!is_object($this->resultFilter)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->resultFilter->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_getOrderLogs_args {
	
	static $_TSPEC;
	public $requestHeaderParam = null;
	public $searchOrderLogsParam = null;
	public $getOrderLogsRequirement = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'requestHeaderParam'
			),
			2 => array(
			'var' => 'searchOrderLogsParam'
			),
			3 => array(
			'var' => 'getOrderLogsRequirement'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['requestHeaderParam'])){
				
				$this->requestHeaderParam = $vals['requestHeaderParam'];
			}
			
			
			if (isset($vals['searchOrderLogsParam'])){
				
				$this->searchOrderLogsParam = $vals['searchOrderLogsParam'];
			}
			
			
			if (isset($vals['getOrderLogsRequirement'])){
				
				$this->getOrderLogsRequirement = $vals['getOrderLogsRequirement'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->requestHeaderParam = new \com\vip\order\common\pojo\order\request\RequestHeader();
			$this->requestHeaderParam->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->searchOrderLogsParam = new \com\vip\order\biz\request\SearchOrderLogsReq();
			$this->searchOrderLogsParam->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->getOrderLogsRequirement = new \com\vip\order\biz\request\requirement\GetOrderLogsRequirement();
			$this->getOrderLogsRequirement->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->requestHeaderParam !== null) {
			
			$xfer += $output->writeFieldBegin('requestHeaderParam');
			
			if (!is_object($this->requestHeaderParam)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->requestHeaderParam->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->searchOrderLogsParam !== null) {
			
			$xfer += $output->writeFieldBegin('searchOrderLogsParam');
			
			if (!is_object($this->searchOrderLogsParam)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->searchOrderLogsParam->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->getOrderLogsRequirement !== null) {
			
			$xfer += $output->writeFieldBegin('getOrderLogsRequirement');
			
			if (!is_object($this->getOrderLogsRequirement)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->getOrderLogsRequirement->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_getOrderOpStatus_args {
	
	static $_TSPEC;
	public $requestHeader = null;
	public $getOrderOpStatusReq = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'requestHeader'
			),
			2 => array(
			'var' => 'getOrderOpStatusReq'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['requestHeader'])){
				
				$this->requestHeader = $vals['requestHeader'];
			}
			
			
			if (isset($vals['getOrderOpStatusReq'])){
				
				$this->getOrderOpStatusReq = $vals['getOrderOpStatusReq'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->requestHeader = new \com\vip\order\common\pojo\order\request\RequestHeader();
			$this->requestHeader->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->getOrderOpStatusReq = new \com\vip\order\biz\request\GetOrderOpStatusReq();
			$this->getOrderOpStatusReq->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->requestHeader !== null) {
			
			$xfer += $output->writeFieldBegin('requestHeader');
			
			if (!is_object($this->requestHeader)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->requestHeader->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->getOrderOpStatusReq !== null) {
			
			$xfer += $output->writeFieldBegin('getOrderOpStatusReq');
			
			if (!is_object($this->getOrderOpStatusReq)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->getOrderOpStatusReq->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_getOrderPackageList_args {
	
	static $_TSPEC;
	public $requestHeader = null;
	public $getPackageListReq = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'requestHeader'
			),
			2 => array(
			'var' => 'getPackageListReq'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['requestHeader'])){
				
				$this->requestHeader = $vals['requestHeader'];
			}
			
			
			if (isset($vals['getPackageListReq'])){
				
				$this->getPackageListReq = $vals['getPackageListReq'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->requestHeader = new \com\vip\order\common\pojo\order\request\RequestHeader();
			$this->requestHeader->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->getPackageListReq = new \com\vip\order\biz\request\GetOrderPackageListReq();
			$this->getPackageListReq->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->requestHeader !== null) {
			
			$xfer += $output->writeFieldBegin('requestHeader');
			
			if (!is_object($this->requestHeader)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->requestHeader->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->getPackageListReq !== null) {
			
			$xfer += $output->writeFieldBegin('getPackageListReq');
			
			if (!is_object($this->getPackageListReq)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->getPackageListReq->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_getOrderPayType_args {
	
	static $_TSPEC;
	public $requestHeader = null;
	public $getOrderPayTypeParam = null;
	public $resultFilter = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'requestHeader'
			),
			2 => array(
			'var' => 'getOrderPayTypeParam'
			),
			3 => array(
			'var' => 'resultFilter'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['requestHeader'])){
				
				$this->requestHeader = $vals['requestHeader'];
			}
			
			
			if (isset($vals['getOrderPayTypeParam'])){
				
				$this->getOrderPayTypeParam = $vals['getOrderPayTypeParam'];
			}
			
			
			if (isset($vals['resultFilter'])){
				
				$this->resultFilter = $vals['resultFilter'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->requestHeader = new \com\vip\order\common\pojo\order\request\RequestHeader();
			$this->requestHeader->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->getOrderPayTypeParam = new \com\vip\order\biz\request\GetOrderPayTypeReq();
			$this->getOrderPayTypeParam->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->resultFilter = new \com\vip\order\common\pojo\order\request\ResultFilter();
			$this->resultFilter->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->requestHeader !== null) {
			
			$xfer += $output->writeFieldBegin('requestHeader');
			
			if (!is_object($this->requestHeader)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->requestHeader->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->getOrderPayTypeParam !== null) {
			
			$xfer += $output->writeFieldBegin('getOrderPayTypeParam');
			
			if (!is_object($this->getOrderPayTypeParam)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->getOrderPayTypeParam->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->resultFilter !== null) {
			
			$xfer += $output->writeFieldBegin('resultFilter');
			
			if (!is_object($this->resultFilter)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->resultFilter->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_getOrderSnByExOrderSn_args {
	
	static $_TSPEC;
	public $requestHeader = null;
	public $exOrderSns = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'requestHeader'
			),
			2 => array(
			'var' => 'exOrderSns'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['requestHeader'])){
				
				$this->requestHeader = $vals['requestHeader'];
			}
			
			
			if (isset($vals['exOrderSns'])){
				
				$this->exOrderSns = $vals['exOrderSns'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->requestHeader = new \com\vip\order\common\pojo\order\request\RequestHeader();
			$this->requestHeader->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->exOrderSns = array();
			$_size0 = 0;
			$input->readListBegin();
			while(true){
				
				try{
					
					$elem0 = null;
					$input->readString($elem0);
					
					$this->exOrderSns[$_size0++] = $elem0;
				}
				catch(\Exception $e){
					
					break;
				}
			}
			
			$input->readListEnd();
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->requestHeader !== null) {
			
			$xfer += $output->writeFieldBegin('requestHeader');
			
			if (!is_object($this->requestHeader)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->requestHeader->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->exOrderSns !== null) {
			
			$xfer += $output->writeFieldBegin('exOrderSns');
			
			if (!is_array($this->exOrderSns)){
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$output->writeListBegin();
			foreach ($this->exOrderSns as $iter0){
				
				$xfer += $output->writeString($iter0);
				
			}
			
			$output->writeListEnd();
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_getOrderTransport_args {
	
	static $_TSPEC;
	public $requestHeader = null;
	public $getOrderTransportReq = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'requestHeader'
			),
			2 => array(
			'var' => 'getOrderTransportReq'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['requestHeader'])){
				
				$this->requestHeader = $vals['requestHeader'];
			}
			
			
			if (isset($vals['getOrderTransportReq'])){
				
				$this->getOrderTransportReq = $vals['getOrderTransportReq'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->requestHeader = new \com\vip\order\common\pojo\order\request\RequestHeader();
			$this->requestHeader->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->getOrderTransportReq = new \com\vip\order\biz\request\GetOrderTransportReq();
			$this->getOrderTransportReq->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->requestHeader !== null) {
			
			$xfer += $output->writeFieldBegin('requestHeader');
			
			if (!is_object($this->requestHeader)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->requestHeader->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->getOrderTransportReq !== null) {
			
			$xfer += $output->writeFieldBegin('getOrderTransportReq');
			
			if (!is_object($this->getOrderTransportReq)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->getOrderTransportReq->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_getOrderTransportDetail_args {
	
	static $_TSPEC;
	public $requestHeader = null;
	public $getOrderTransportDetailReq = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'requestHeader'
			),
			2 => array(
			'var' => 'getOrderTransportDetailReq'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['requestHeader'])){
				
				$this->requestHeader = $vals['requestHeader'];
			}
			
			
			if (isset($vals['getOrderTransportDetailReq'])){
				
				$this->getOrderTransportDetailReq = $vals['getOrderTransportDetailReq'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->requestHeader = new \com\vip\order\common\pojo\order\request\RequestHeader();
			$this->requestHeader->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->getOrderTransportDetailReq = new \com\vip\order\biz\request\GetOrderTransportDetailReq();
			$this->getOrderTransportDetailReq->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->requestHeader !== null) {
			
			$xfer += $output->writeFieldBegin('requestHeader');
			
			if (!is_object($this->requestHeader)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->requestHeader->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->getOrderTransportDetailReq !== null) {
			
			$xfer += $output->writeFieldBegin('getOrderTransportDetailReq');
			
			if (!is_object($this->getOrderTransportDetailReq)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->getOrderTransportDetailReq->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_getOrderTransportListByCodes_args {
	
	static $_TSPEC;
	public $requestHeaderParam = null;
	public $getTransportListByCodesParam = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'requestHeaderParam'
			),
			2 => array(
			'var' => 'getTransportListByCodesParam'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['requestHeaderParam'])){
				
				$this->requestHeaderParam = $vals['requestHeaderParam'];
			}
			
			
			if (isset($vals['getTransportListByCodesParam'])){
				
				$this->getTransportListByCodesParam = $vals['getTransportListByCodesParam'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->requestHeaderParam = new \com\vip\order\common\pojo\order\request\RequestHeader();
			$this->requestHeaderParam->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->getTransportListByCodesParam = new \com\vip\order\biz\request\GetTransportListByCodesReq();
			$this->getTransportListByCodesParam->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->requestHeaderParam !== null) {
			
			$xfer += $output->writeFieldBegin('requestHeaderParam');
			
			if (!is_object($this->requestHeaderParam)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->requestHeaderParam->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->getTransportListByCodesParam !== null) {
			
			$xfer += $output->writeFieldBegin('getTransportListByCodesParam');
			
			if (!is_object($this->getTransportListByCodesParam)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->getTransportListByCodesParam->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_getOrdersBySizeId_args {
	
	static $_TSPEC;
	public $requestHeader = null;
	public $req = null;
	public $filter = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'requestHeader'
			),
			2 => array(
			'var' => 'req'
			),
			3 => array(
			'var' => 'filter'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['requestHeader'])){
				
				$this->requestHeader = $vals['requestHeader'];
			}
			
			
			if (isset($vals['req'])){
				
				$this->req = $vals['req'];
			}
			
			
			if (isset($vals['filter'])){
				
				$this->filter = $vals['filter'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->requestHeader = new \com\vip\order\common\pojo\order\request\RequestHeader();
			$this->requestHeader->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->req = new \com\vip\order\biz\request\GetOrdersBySizeIdReq();
			$this->req->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->filter = new \com\vip\order\common\pojo\order\request\ResultFilter();
			$this->filter->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->requestHeader !== null) {
			
			$xfer += $output->writeFieldBegin('requestHeader');
			
			if (!is_object($this->requestHeader)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->requestHeader->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->req !== null) {
			
			$xfer += $output->writeFieldBegin('req');
			
			if (!is_object($this->req)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->req->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->filter !== null) {
			
			$xfer += $output->writeFieldBegin('filter');
			
			if (!is_object($this->filter)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->filter->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_getPrepayOrderStatus_args {
	
	static $_TSPEC;
	public $requestHeader = null;
	public $req = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'requestHeader'
			),
			2 => array(
			'var' => 'req'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['requestHeader'])){
				
				$this->requestHeader = $vals['requestHeader'];
			}
			
			
			if (isset($vals['req'])){
				
				$this->req = $vals['req'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->requestHeader = new \com\vip\order\common\pojo\order\request\RequestHeader();
			$this->requestHeader->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->req = new \com\vip\order\biz\request\GetPrepayOrderStatusReq();
			$this->req->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->requestHeader !== null) {
			
			$xfer += $output->writeFieldBegin('requestHeader');
			
			if (!is_object($this->requestHeader)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->requestHeader->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->req !== null) {
			
			$xfer += $output->writeFieldBegin('req');
			
			if (!is_object($this->req)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->req->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_getPrepayOrderUnpayMsg_args {
	
	static $_TSPEC;
	public $header = null;
	public $req = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'header'
			),
			2 => array(
			'var' => 'req'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['header'])){
				
				$this->header = $vals['header'];
			}
			
			
			if (isset($vals['req'])){
				
				$this->req = $vals['req'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->header = new \com\vip\order\common\pojo\order\request\RequestHeader();
			$this->header->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->req = new \com\vip\order\biz\request\GetPrepayOrderUnpayMsgReq();
			$this->req->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->header !== null) {
			
			$xfer += $output->writeFieldBegin('header');
			
			if (!is_object($this->header)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->header->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->req !== null) {
			
			$xfer += $output->writeFieldBegin('req');
			
			if (!is_object($this->req)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->req->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_getRdc_args {
	
	static $_TSPEC;
	public $requestHeader = null;
	public $getRdcReq = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'requestHeader'
			),
			2 => array(
			'var' => 'getRdcReq'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['requestHeader'])){
				
				$this->requestHeader = $vals['requestHeader'];
			}
			
			
			if (isset($vals['getRdcReq'])){
				
				$this->getRdcReq = $vals['getRdcReq'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->requestHeader = new \com\vip\order\common\pojo\order\request\RequestHeader();
			$this->requestHeader->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->getRdcReq = new \com\vip\order\biz\request\GetRdcReq();
			$this->getRdcReq->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->requestHeader !== null) {
			
			$xfer += $output->writeFieldBegin('requestHeader');
			
			if (!is_object($this->requestHeader)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->requestHeader->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->getRdcReq !== null) {
			
			$xfer += $output->writeFieldBegin('getRdcReq');
			
			if (!is_object($this->getRdcReq)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->getRdcReq->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_getRdcInvoice_args {
	
	static $_TSPEC;
	public $requestHeader = null;
	public $req = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'requestHeader'
			),
			2 => array(
			'var' => 'req'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['requestHeader'])){
				
				$this->requestHeader = $vals['requestHeader'];
			}
			
			
			if (isset($vals['req'])){
				
				$this->req = $vals['req'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->requestHeader = new \com\vip\order\common\pojo\order\request\RequestHeader();
			$this->requestHeader->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->req = new \com\vip\order\biz\request\GetRdcInvoiceReq();
			$this->req->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->requestHeader !== null) {
			
			$xfer += $output->writeFieldBegin('requestHeader');
			
			if (!is_object($this->requestHeader)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->requestHeader->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->req !== null) {
			
			$xfer += $output->writeFieldBegin('req');
			
			if (!is_object($this->req)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->req->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_getReturnOrExchangeGoods_args {
	
	static $_TSPEC;
	public $requestHeader = null;
	public $req = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'requestHeader'
			),
			2 => array(
			'var' => 'req'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['requestHeader'])){
				
				$this->requestHeader = $vals['requestHeader'];
			}
			
			
			if (isset($vals['req'])){
				
				$this->req = $vals['req'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->requestHeader = new \com\vip\order\common\pojo\order\request\RequestHeader();
			$this->requestHeader->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->req = new \com\vip\order\biz\request\GetReturnOrExchangeGoodsReq();
			$this->req->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->requestHeader !== null) {
			
			$xfer += $output->writeFieldBegin('requestHeader');
			
			if (!is_object($this->requestHeader)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->requestHeader->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->req !== null) {
			
			$xfer += $output->writeFieldBegin('req');
			
			if (!is_object($this->req)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->req->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_getSimpleOrderFlowFlag_args {
	
	static $_TSPEC;
	public $requestHeaderParam = null;
	public $getSimpleOrderFlowFlagParam = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'requestHeaderParam'
			),
			2 => array(
			'var' => 'getSimpleOrderFlowFlagParam'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['requestHeaderParam'])){
				
				$this->requestHeaderParam = $vals['requestHeaderParam'];
			}
			
			
			if (isset($vals['getSimpleOrderFlowFlagParam'])){
				
				$this->getSimpleOrderFlowFlagParam = $vals['getSimpleOrderFlowFlagParam'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->requestHeaderParam = new \com\vip\order\common\pojo\order\request\RequestHeader();
			$this->requestHeaderParam->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->getSimpleOrderFlowFlagParam = new \com\vip\order\biz\request\GetSimpleOrderFlowFlagReq();
			$this->getSimpleOrderFlowFlagParam->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->requestHeaderParam !== null) {
			
			$xfer += $output->writeFieldBegin('requestHeaderParam');
			
			if (!is_object($this->requestHeaderParam)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->requestHeaderParam->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->getSimpleOrderFlowFlagParam !== null) {
			
			$xfer += $output->writeFieldBegin('getSimpleOrderFlowFlagParam');
			
			if (!is_object($this->getSimpleOrderFlowFlagParam)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->getSimpleOrderFlowFlagParam->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_getUnpayOrderList_args {
	
	static $_TSPEC;
	public $requestHeaderParam = null;
	public $getUnpayOrderParam = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'requestHeaderParam'
			),
			2 => array(
			'var' => 'getUnpayOrderParam'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['requestHeaderParam'])){
				
				$this->requestHeaderParam = $vals['requestHeaderParam'];
			}
			
			
			if (isset($vals['getUnpayOrderParam'])){
				
				$this->getUnpayOrderParam = $vals['getUnpayOrderParam'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->requestHeaderParam = new \com\vip\order\common\pojo\order\request\RequestHeader();
			$this->requestHeaderParam->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->getUnpayOrderParam = new \com\vip\order\biz\request\GetUnpayOrderReq();
			$this->getUnpayOrderParam->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->requestHeaderParam !== null) {
			
			$xfer += $output->writeFieldBegin('requestHeaderParam');
			
			if (!is_object($this->requestHeaderParam)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->requestHeaderParam->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->getUnpayOrderParam !== null) {
			
			$xfer += $output->writeFieldBegin('getUnpayOrderParam');
			
			if (!is_object($this->getUnpayOrderParam)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->getUnpayOrderParam->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_getUserDeliveryAddress_args {
	
	static $_TSPEC;
	public $requestHeader = null;
	public $getUserDeliveryAddressReq = null;
	public $resultFilter = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'requestHeader'
			),
			2 => array(
			'var' => 'getUserDeliveryAddressReq'
			),
			3 => array(
			'var' => 'resultFilter'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['requestHeader'])){
				
				$this->requestHeader = $vals['requestHeader'];
			}
			
			
			if (isset($vals['getUserDeliveryAddressReq'])){
				
				$this->getUserDeliveryAddressReq = $vals['getUserDeliveryAddressReq'];
			}
			
			
			if (isset($vals['resultFilter'])){
				
				$this->resultFilter = $vals['resultFilter'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->requestHeader = new \com\vip\order\common\pojo\order\request\RequestHeader();
			$this->requestHeader->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->getUserDeliveryAddressReq = new \com\vip\order\biz\request\GetUserDeliveryAddressReq();
			$this->getUserDeliveryAddressReq->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->resultFilter = new \com\vip\order\common\pojo\order\request\ResultFilter();
			$this->resultFilter->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->requestHeader !== null) {
			
			$xfer += $output->writeFieldBegin('requestHeader');
			
			if (!is_object($this->requestHeader)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->requestHeader->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->getUserDeliveryAddressReq !== null) {
			
			$xfer += $output->writeFieldBegin('getUserDeliveryAddressReq');
			
			if (!is_object($this->getUserDeliveryAddressReq)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->getUserDeliveryAddressReq->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->resultFilter !== null) {
			
			$xfer += $output->writeFieldBegin('resultFilter');
			
			if (!is_object($this->resultFilter)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->resultFilter->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_getUserFirstOrder_args {
	
	static $_TSPEC;
	public $requestHeader = null;
	public $getUserFirstOrderReq = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'requestHeader'
			),
			2 => array(
			'var' => 'getUserFirstOrderReq'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['requestHeader'])){
				
				$this->requestHeader = $vals['requestHeader'];
			}
			
			
			if (isset($vals['getUserFirstOrderReq'])){
				
				$this->getUserFirstOrderReq = $vals['getUserFirstOrderReq'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->requestHeader = new \com\vip\order\common\pojo\order\request\RequestHeader();
			$this->requestHeader->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->getUserFirstOrderReq = new \com\vip\order\biz\request\GetUserFirstOrderReq();
			$this->getUserFirstOrderReq->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->requestHeader !== null) {
			
			$xfer += $output->writeFieldBegin('requestHeader');
			
			if (!is_object($this->requestHeader)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->requestHeader->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->getUserFirstOrderReq !== null) {
			
			$xfer += $output->writeFieldBegin('getUserFirstOrderReq');
			
			if (!is_object($this->getUserFirstOrderReq)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->getUserFirstOrderReq->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_healthCheck_args {
	
	static $_TSPEC;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			
			);
			
		}
		
		if (is_array($vals)){
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_mergeOrder_args {
	
	static $_TSPEC;
	public $requestHeader = null;
	public $reqParam = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'requestHeader'
			),
			2 => array(
			'var' => 'reqParam'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['requestHeader'])){
				
				$this->requestHeader = $vals['requestHeader'];
			}
			
			
			if (isset($vals['reqParam'])){
				
				$this->reqParam = $vals['reqParam'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->requestHeader = new \com\vip\order\common\pojo\order\request\RequestHeader();
			$this->requestHeader->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->reqParam = new \com\vip\order\biz\request\MergeOrderReq();
			$this->reqParam->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->requestHeader !== null) {
			
			$xfer += $output->writeFieldBegin('requestHeader');
			
			if (!is_object($this->requestHeader)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->requestHeader->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->reqParam !== null) {
			
			$xfer += $output->writeFieldBegin('reqParam');
			
			if (!is_object($this->reqParam)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->reqParam->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_modifyOrderConsignee_args {
	
	static $_TSPEC;
	public $requestHeader = null;
	public $modifyOrderConsigneeReq = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'requestHeader'
			),
			2 => array(
			'var' => 'modifyOrderConsigneeReq'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['requestHeader'])){
				
				$this->requestHeader = $vals['requestHeader'];
			}
			
			
			if (isset($vals['modifyOrderConsigneeReq'])){
				
				$this->modifyOrderConsigneeReq = $vals['modifyOrderConsigneeReq'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->requestHeader = new \com\vip\order\common\pojo\order\request\RequestHeader();
			$this->requestHeader->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->modifyOrderConsigneeReq = new \com\vip\order\biz\request\ModifyOrderConsigneeReq();
			$this->modifyOrderConsigneeReq->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->requestHeader !== null) {
			
			$xfer += $output->writeFieldBegin('requestHeader');
			
			if (!is_object($this->requestHeader)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->requestHeader->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->modifyOrderConsigneeReq !== null) {
			
			$xfer += $output->writeFieldBegin('modifyOrderConsigneeReq');
			
			if (!is_object($this->modifyOrderConsigneeReq)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->modifyOrderConsigneeReq->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_modifyOrderElectronicInvoice_args {
	
	static $_TSPEC;
	public $requestHeader = null;
	public $modifyOrderElectronicInvoiceReq = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'requestHeader'
			),
			2 => array(
			'var' => 'modifyOrderElectronicInvoiceReq'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['requestHeader'])){
				
				$this->requestHeader = $vals['requestHeader'];
			}
			
			
			if (isset($vals['modifyOrderElectronicInvoiceReq'])){
				
				$this->modifyOrderElectronicInvoiceReq = $vals['modifyOrderElectronicInvoiceReq'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->requestHeader = new \com\vip\order\common\pojo\order\request\RequestHeader();
			$this->requestHeader->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->modifyOrderElectronicInvoiceReq = new \com\vip\order\biz\request\ModifyOrderElectronicInvoiceReq();
			$this->modifyOrderElectronicInvoiceReq->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->requestHeader !== null) {
			
			$xfer += $output->writeFieldBegin('requestHeader');
			
			if (!is_object($this->requestHeader)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->requestHeader->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->modifyOrderElectronicInvoiceReq !== null) {
			
			$xfer += $output->writeFieldBegin('modifyOrderElectronicInvoiceReq');
			
			if (!is_object($this->modifyOrderElectronicInvoiceReq)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->modifyOrderElectronicInvoiceReq->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_modifyOrderGoods_args {
	
	static $_TSPEC;
	public $requestHeader = null;
	public $orderGoodsReq = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'requestHeader'
			),
			2 => array(
			'var' => 'orderGoodsReq'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['requestHeader'])){
				
				$this->requestHeader = $vals['requestHeader'];
			}
			
			
			if (isset($vals['orderGoodsReq'])){
				
				$this->orderGoodsReq = $vals['orderGoodsReq'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->requestHeader = new \com\vip\order\common\pojo\order\request\RequestHeader();
			$this->requestHeader->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->orderGoodsReq = new \com\vip\order\biz\request\ModifyOrderGoodsReq();
			$this->orderGoodsReq->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->requestHeader !== null) {
			
			$xfer += $output->writeFieldBegin('requestHeader');
			
			if (!is_object($this->requestHeader)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->requestHeader->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderGoodsReq !== null) {
			
			$xfer += $output->writeFieldBegin('orderGoodsReq');
			
			if (!is_object($this->orderGoodsReq)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->orderGoodsReq->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_modifyOrderPayType_args {
	
	static $_TSPEC;
	public $requestHeader = null;
	public $ModifyPayTypeReq = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'requestHeader'
			),
			2 => array(
			'var' => 'ModifyPayTypeReq'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['requestHeader'])){
				
				$this->requestHeader = $vals['requestHeader'];
			}
			
			
			if (isset($vals['ModifyPayTypeReq'])){
				
				$this->ModifyPayTypeReq = $vals['ModifyPayTypeReq'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->requestHeader = new \com\vip\order\common\pojo\order\request\RequestHeader();
			$this->requestHeader->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->ModifyPayTypeReq = new \com\vip\order\common\pojo\order\vo\ModifyPayTypeReq();
			$this->ModifyPayTypeReq->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->requestHeader !== null) {
			
			$xfer += $output->writeFieldBegin('requestHeader');
			
			if (!is_object($this->requestHeader)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->requestHeader->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->ModifyPayTypeReq !== null) {
			
			$xfer += $output->writeFieldBegin('ModifyPayTypeReq');
			
			if (!is_object($this->ModifyPayTypeReq)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->ModifyPayTypeReq->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_modifyOrderShipped_args {
	
	static $_TSPEC;
	public $requestHeader = null;
	public $modifyOrderShippedReq = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'requestHeader'
			),
			2 => array(
			'var' => 'modifyOrderShippedReq'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['requestHeader'])){
				
				$this->requestHeader = $vals['requestHeader'];
			}
			
			
			if (isset($vals['modifyOrderShippedReq'])){
				
				$this->modifyOrderShippedReq = $vals['modifyOrderShippedReq'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->requestHeader = new \com\vip\order\common\pojo\order\request\RequestHeader();
			$this->requestHeader->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->modifyOrderShippedReq = new \com\vip\order\biz\request\ModifyOrderShippedReq();
			$this->modifyOrderShippedReq->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->requestHeader !== null) {
			
			$xfer += $output->writeFieldBegin('requestHeader');
			
			if (!is_object($this->requestHeader)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->requestHeader->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->modifyOrderShippedReq !== null) {
			
			$xfer += $output->writeFieldBegin('modifyOrderShippedReq');
			
			if (!is_object($this->modifyOrderShippedReq)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->modifyOrderShippedReq->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_modifyPrepayOrderPayType_args {
	
	static $_TSPEC;
	public $requestHeader = null;
	public $modifyPrepayOrderPayTypeReq = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'requestHeader'
			),
			2 => array(
			'var' => 'modifyPrepayOrderPayTypeReq'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['requestHeader'])){
				
				$this->requestHeader = $vals['requestHeader'];
			}
			
			
			if (isset($vals['modifyPrepayOrderPayTypeReq'])){
				
				$this->modifyPrepayOrderPayTypeReq = $vals['modifyPrepayOrderPayTypeReq'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->requestHeader = new \com\vip\order\common\pojo\order\request\RequestHeader();
			$this->requestHeader->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->modifyPrepayOrderPayTypeReq = new \com\vip\order\biz\request\ModifyPrepayOrderPayTypeReq();
			$this->modifyPrepayOrderPayTypeReq->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->requestHeader !== null) {
			
			$xfer += $output->writeFieldBegin('requestHeader');
			
			if (!is_object($this->requestHeader)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->requestHeader->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->modifyPrepayOrderPayTypeReq !== null) {
			
			$xfer += $output->writeFieldBegin('modifyPrepayOrderPayTypeReq');
			
			if (!is_object($this->modifyPrepayOrderPayTypeReq)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->modifyPrepayOrderPayTypeReq->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_notifyCustomsDeclarationFailed_args {
	
	static $_TSPEC;
	public $requestHeader = null;
	public $req = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'requestHeader'
			),
			2 => array(
			'var' => 'req'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['requestHeader'])){
				
				$this->requestHeader = $vals['requestHeader'];
			}
			
			
			if (isset($vals['req'])){
				
				$this->req = $vals['req'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->requestHeader = new \com\vip\order\common\pojo\order\request\RequestHeader();
			$this->requestHeader->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->req = new \com\vip\order\biz\request\NotifyCustomsDeclarationFailedReq();
			$this->req->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->requestHeader !== null) {
			
			$xfer += $output->writeFieldBegin('requestHeader');
			
			if (!is_object($this->requestHeader)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->requestHeader->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->req !== null) {
			
			$xfer += $output->writeFieldBegin('req');
			
			if (!is_object($this->req)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->req->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_ofcEntranceGrayControl_args {
	
	static $_TSPEC;
	public $requestHeader = null;
	public $req = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'requestHeader'
			),
			2 => array(
			'var' => 'req'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['requestHeader'])){
				
				$this->requestHeader = $vals['requestHeader'];
			}
			
			
			if (isset($vals['req'])){
				
				$this->req = $vals['req'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->requestHeader = new \com\vip\order\common\pojo\order\request\RequestHeader();
			$this->requestHeader->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->req = new \com\vip\order\biz\request\OfcEntranceGrayControlReq();
			$this->req->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->requestHeader !== null) {
			
			$xfer += $output->writeFieldBegin('requestHeader');
			
			if (!is_object($this->requestHeader)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->requestHeader->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->req !== null) {
			
			$xfer += $output->writeFieldBegin('req');
			
			if (!is_object($this->req)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->req->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_postOrderVMSMessage_args {
	
	static $_TSPEC;
	public $requestHeader = null;
	public $postOrderVMSMessageReq = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'requestHeader'
			),
			2 => array(
			'var' => 'postOrderVMSMessageReq'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['requestHeader'])){
				
				$this->requestHeader = $vals['requestHeader'];
			}
			
			
			if (isset($vals['postOrderVMSMessageReq'])){
				
				$this->postOrderVMSMessageReq = $vals['postOrderVMSMessageReq'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->requestHeader = new \com\vip\order\common\pojo\order\request\RequestHeader();
			$this->requestHeader->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->postOrderVMSMessageReq = new \com\vip\order\biz\request\PostOrderVMSMessageReq();
			$this->postOrderVMSMessageReq->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->requestHeader !== null) {
			
			$xfer += $output->writeFieldBegin('requestHeader');
			
			if (!is_object($this->requestHeader)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->requestHeader->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->postOrderVMSMessageReq !== null) {
			
			$xfer += $output->writeFieldBegin('postOrderVMSMessageReq');
			
			if (!is_object($this->postOrderVMSMessageReq)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->postOrderVMSMessageReq->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_putIntoSplitQueue_args {
	
	static $_TSPEC;
	public $requestHeader = null;
	public $putIntoSplitQueueReq = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'requestHeader'
			),
			2 => array(
			'var' => 'putIntoSplitQueueReq'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['requestHeader'])){
				
				$this->requestHeader = $vals['requestHeader'];
			}
			
			
			if (isset($vals['putIntoSplitQueueReq'])){
				
				$this->putIntoSplitQueueReq = $vals['putIntoSplitQueueReq'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->requestHeader = new \com\vip\order\common\pojo\order\request\RequestHeader();
			$this->requestHeader->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->putIntoSplitQueueReq = new \com\vip\order\biz\request\PutIntoSplitQueueReq();
			$this->putIntoSplitQueueReq->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->requestHeader !== null) {
			
			$xfer += $output->writeFieldBegin('requestHeader');
			
			if (!is_object($this->requestHeader)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->requestHeader->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->putIntoSplitQueueReq !== null) {
			
			$xfer += $output->writeFieldBegin('putIntoSplitQueueReq');
			
			if (!is_object($this->putIntoSplitQueueReq)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->putIntoSplitQueueReq->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_putKeyToRollbackQueue_args {
	
	static $_TSPEC;
	public $requestHeader = null;
	public $req = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'requestHeader'
			),
			2 => array(
			'var' => 'req'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['requestHeader'])){
				
				$this->requestHeader = $vals['requestHeader'];
			}
			
			
			if (isset($vals['req'])){
				
				$this->req = $vals['req'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->requestHeader = new \com\vip\order\common\pojo\order\request\RequestHeader();
			$this->requestHeader->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->req = new \com\vip\order\biz\request\PutKeyToRollbackQueueReq();
			$this->req->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->requestHeader !== null) {
			
			$xfer += $output->writeFieldBegin('requestHeader');
			
			if (!is_object($this->requestHeader)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->requestHeader->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->req !== null) {
			
			$xfer += $output->writeFieldBegin('req');
			
			if (!is_object($this->req)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->req->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_receptionConfirmDelivered_args {
	
	static $_TSPEC;
	public $requestHeader = null;
	public $req = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'requestHeader'
			),
			2 => array(
			'var' => 'req'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['requestHeader'])){
				
				$this->requestHeader = $vals['requestHeader'];
			}
			
			
			if (isset($vals['req'])){
				
				$this->req = $vals['req'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->requestHeader = new \com\vip\order\common\pojo\order\request\RequestHeader();
			$this->requestHeader->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->req = new \com\vip\order\biz\request\ReceptionConfirmDeliveredReq();
			$this->req->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->requestHeader !== null) {
			
			$xfer += $output->writeFieldBegin('requestHeader');
			
			if (!is_object($this->requestHeader)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->requestHeader->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->req !== null) {
			
			$xfer += $output->writeFieldBegin('req');
			
			if (!is_object($this->req)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->req->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_refundOrder_args {
	
	static $_TSPEC;
	public $requestHeader = null;
	public $orderRefundReq = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'requestHeader'
			),
			2 => array(
			'var' => 'orderRefundReq'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['requestHeader'])){
				
				$this->requestHeader = $vals['requestHeader'];
			}
			
			
			if (isset($vals['orderRefundReq'])){
				
				$this->orderRefundReq = $vals['orderRefundReq'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->requestHeader = new \com\vip\order\common\pojo\order\request\RequestHeader();
			$this->requestHeader->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->orderRefundReq = new \com\vip\order\biz\request\OrderRefundReq();
			$this->orderRefundReq->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->requestHeader !== null) {
			
			$xfer += $output->writeFieldBegin('requestHeader');
			
			if (!is_object($this->requestHeader)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->requestHeader->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->orderRefundReq !== null) {
			
			$xfer += $output->writeFieldBegin('orderRefundReq');
			
			if (!is_object($this->orderRefundReq)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->orderRefundReq->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_releaseStock_args {
	
	static $_TSPEC;
	public $requestHeader = null;
	public $releaseStockReq = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'requestHeader'
			),
			2 => array(
			'var' => 'releaseStockReq'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['requestHeader'])){
				
				$this->requestHeader = $vals['requestHeader'];
			}
			
			
			if (isset($vals['releaseStockReq'])){
				
				$this->releaseStockReq = $vals['releaseStockReq'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->requestHeader = new \com\vip\order\common\pojo\order\request\RequestHeader();
			$this->requestHeader->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->releaseStockReq = new \com\vip\order\biz\request\ReleaseStockReq();
			$this->releaseStockReq->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->requestHeader !== null) {
			
			$xfer += $output->writeFieldBegin('requestHeader');
			
			if (!is_object($this->requestHeader)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->requestHeader->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->releaseStockReq !== null) {
			
			$xfer += $output->writeFieldBegin('releaseStockReq');
			
			if (!is_object($this->releaseStockReq)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->releaseStockReq->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_rollbackOrder_args {
	
	static $_TSPEC;
	public $requestHeader = null;
	public $rollbackOrderReq = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'requestHeader'
			),
			2 => array(
			'var' => 'rollbackOrderReq'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['requestHeader'])){
				
				$this->requestHeader = $vals['requestHeader'];
			}
			
			
			if (isset($vals['rollbackOrderReq'])){
				
				$this->rollbackOrderReq = $vals['rollbackOrderReq'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->requestHeader = new \com\vip\order\common\pojo\order\request\RequestHeader();
			$this->requestHeader->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->rollbackOrderReq = new \com\vip\order\biz\request\RollbackOrderReq();
			$this->rollbackOrderReq->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->requestHeader !== null) {
			
			$xfer += $output->writeFieldBegin('requestHeader');
			
			if (!is_object($this->requestHeader)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->requestHeader->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->rollbackOrderReq !== null) {
			
			$xfer += $output->writeFieldBegin('rollbackOrderReq');
			
			if (!is_object($this->rollbackOrderReq)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->rollbackOrderReq->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_searchOrderListByUserId_args {
	
	static $_TSPEC;
	public $requestHeader = null;
	public $getOrderHistoryReq = null;
	public $resultFilter = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'requestHeader'
			),
			2 => array(
			'var' => 'getOrderHistoryReq'
			),
			3 => array(
			'var' => 'resultFilter'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['requestHeader'])){
				
				$this->requestHeader = $vals['requestHeader'];
			}
			
			
			if (isset($vals['getOrderHistoryReq'])){
				
				$this->getOrderHistoryReq = $vals['getOrderHistoryReq'];
			}
			
			
			if (isset($vals['resultFilter'])){
				
				$this->resultFilter = $vals['resultFilter'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->requestHeader = new \com\vip\order\common\pojo\order\request\RequestHeader();
			$this->requestHeader->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->getOrderHistoryReq = new \com\vip\order\biz\request\SearchOrderListByUserIdReq();
			$this->getOrderHistoryReq->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->resultFilter = new \com\vip\order\common\pojo\order\request\ResultFilter();
			$this->resultFilter->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->requestHeader !== null) {
			
			$xfer += $output->writeFieldBegin('requestHeader');
			
			if (!is_object($this->requestHeader)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->requestHeader->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->getOrderHistoryReq !== null) {
			
			$xfer += $output->writeFieldBegin('getOrderHistoryReq');
			
			if (!is_object($this->getOrderHistoryReq)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->getOrderHistoryReq->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->resultFilter !== null) {
			
			$xfer += $output->writeFieldBegin('resultFilter');
			
			if (!is_object($this->resultFilter)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->resultFilter->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_signOrder_args {
	
	static $_TSPEC;
	public $requestHeader = null;
	public $signOrderReq = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'requestHeader'
			),
			2 => array(
			'var' => 'signOrderReq'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['requestHeader'])){
				
				$this->requestHeader = $vals['requestHeader'];
			}
			
			
			if (isset($vals['signOrderReq'])){
				
				$this->signOrderReq = $vals['signOrderReq'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->requestHeader = new \com\vip\order\common\pojo\order\request\RequestHeader();
			$this->requestHeader->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->signOrderReq = new \com\vip\order\biz\request\SignOrderReq();
			$this->signOrderReq->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->requestHeader !== null) {
			
			$xfer += $output->writeFieldBegin('requestHeader');
			
			if (!is_object($this->requestHeader)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->requestHeader->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->signOrderReq !== null) {
			
			$xfer += $output->writeFieldBegin('signOrderReq');
			
			if (!is_object($this->signOrderReq)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->signOrderReq->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_triggerGroupByAuditOrder_args {
	
	static $_TSPEC;
	public $requestHeader = null;
	public $groupByOrderAuditReq = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'requestHeader'
			),
			2 => array(
			'var' => 'groupByOrderAuditReq'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['requestHeader'])){
				
				$this->requestHeader = $vals['requestHeader'];
			}
			
			
			if (isset($vals['groupByOrderAuditReq'])){
				
				$this->groupByOrderAuditReq = $vals['groupByOrderAuditReq'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->requestHeader = new \com\vip\order\common\pojo\order\request\RequestHeader();
			$this->requestHeader->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->groupByOrderAuditReq = new \com\vip\order\biz\request\GroupByOrderAuditReq();
			$this->groupByOrderAuditReq->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->requestHeader !== null) {
			
			$xfer += $output->writeFieldBegin('requestHeader');
			
			if (!is_object($this->requestHeader)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->requestHeader->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->groupByOrderAuditReq !== null) {
			
			$xfer += $output->writeFieldBegin('groupByOrderAuditReq');
			
			if (!is_object($this->groupByOrderAuditReq)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->groupByOrderAuditReq->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_updateAutoPayAuth_args {
	
	static $_TSPEC;
	public $requestHeader = null;
	public $req = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'requestHeader'
			),
			2 => array(
			'var' => 'req'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['requestHeader'])){
				
				$this->requestHeader = $vals['requestHeader'];
			}
			
			
			if (isset($vals['req'])){
				
				$this->req = $vals['req'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->requestHeader = new \com\vip\order\common\pojo\order\request\RequestHeader();
			$this->requestHeader->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->req = new \com\vip\order\biz\request\UpdateAutoPayAuthReq();
			$this->req->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->requestHeader !== null) {
			
			$xfer += $output->writeFieldBegin('requestHeader');
			
			if (!is_object($this->requestHeader)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->requestHeader->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->req !== null) {
			
			$xfer += $output->writeFieldBegin('req');
			
			if (!is_object($this->req)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->req->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_updateOrderPayResult_args {
	
	static $_TSPEC;
	public $requestHeader = null;
	public $updateOrderPayResultReq = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'requestHeader'
			),
			2 => array(
			'var' => 'updateOrderPayResultReq'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['requestHeader'])){
				
				$this->requestHeader = $vals['requestHeader'];
			}
			
			
			if (isset($vals['updateOrderPayResultReq'])){
				
				$this->updateOrderPayResultReq = $vals['updateOrderPayResultReq'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->requestHeader = new \com\vip\order\common\pojo\order\request\RequestHeader();
			$this->requestHeader->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->updateOrderPayResultReq = new \com\vip\order\biz\request\UpdateOrderPayResultReq();
			$this->updateOrderPayResultReq->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->requestHeader !== null) {
			
			$xfer += $output->writeFieldBegin('requestHeader');
			
			if (!is_object($this->requestHeader)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->requestHeader->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->updateOrderPayResultReq !== null) {
			
			$xfer += $output->writeFieldBegin('updateOrderPayResultReq');
			
			if (!is_object($this->updateOrderPayResultReq)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->updateOrderPayResultReq->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_updateOrderToReturnVerified_args {
	
	static $_TSPEC;
	public $requestHeader = null;
	public $updateOrderToReturnVerifiedReq = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'requestHeader'
			),
			2 => array(
			'var' => 'updateOrderToReturnVerifiedReq'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['requestHeader'])){
				
				$this->requestHeader = $vals['requestHeader'];
			}
			
			
			if (isset($vals['updateOrderToReturnVerifiedReq'])){
				
				$this->updateOrderToReturnVerifiedReq = $vals['updateOrderToReturnVerifiedReq'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->requestHeader = new \com\vip\order\common\pojo\order\request\RequestHeader();
			$this->requestHeader->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->updateOrderToReturnVerifiedReq = new \com\vip\order\biz\request\UpdateOrderToReturnVerifiedReq();
			$this->updateOrderToReturnVerifiedReq->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->requestHeader !== null) {
			
			$xfer += $output->writeFieldBegin('requestHeader');
			
			if (!is_object($this->requestHeader)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->requestHeader->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->updateOrderToReturnVerifiedReq !== null) {
			
			$xfer += $output->writeFieldBegin('updateOrderToReturnVerifiedReq');
			
			if (!is_object($this->updateOrderToReturnVerifiedReq)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->updateOrderToReturnVerifiedReq->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_updatePayTypeToCOD_args {
	
	static $_TSPEC;
	public $requestHeader = null;
	public $updatePayTypeToCODReq = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'requestHeader'
			),
			2 => array(
			'var' => 'updatePayTypeToCODReq'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['requestHeader'])){
				
				$this->requestHeader = $vals['requestHeader'];
			}
			
			
			if (isset($vals['updatePayTypeToCODReq'])){
				
				$this->updatePayTypeToCODReq = $vals['updatePayTypeToCODReq'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->requestHeader = new \com\vip\order\common\pojo\order\request\RequestHeader();
			$this->requestHeader->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->updatePayTypeToCODReq = new \com\vip\order\biz\request\UpdatePayTypeToCODReq();
			$this->updatePayTypeToCODReq->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->requestHeader !== null) {
			
			$xfer += $output->writeFieldBegin('requestHeader');
			
			if (!is_object($this->requestHeader)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->requestHeader->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->updatePayTypeToCODReq !== null) {
			
			$xfer += $output->writeFieldBegin('updatePayTypeToCODReq');
			
			if (!is_object($this->updatePayTypeToCODReq)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->updatePayTypeToCODReq->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_updatePrePayToVerified_args {
	
	static $_TSPEC;
	public $requestHeader = null;
	public $updatePrePayToVerifiedReq = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'requestHeader'
			),
			2 => array(
			'var' => 'updatePrePayToVerifiedReq'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['requestHeader'])){
				
				$this->requestHeader = $vals['requestHeader'];
			}
			
			
			if (isset($vals['updatePrePayToVerifiedReq'])){
				
				$this->updatePrePayToVerifiedReq = $vals['updatePrePayToVerifiedReq'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->requestHeader = new \com\vip\order\common\pojo\order\request\RequestHeader();
			$this->requestHeader->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->updatePrePayToVerifiedReq = new \com\vip\order\biz\request\UpdatePrePayToVerifiedReq();
			$this->updatePrePayToVerifiedReq->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->requestHeader !== null) {
			
			$xfer += $output->writeFieldBegin('requestHeader');
			
			if (!is_object($this->requestHeader)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->requestHeader->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->updatePrePayToVerifiedReq !== null) {
			
			$xfer += $output->writeFieldBegin('updatePrePayToVerifiedReq');
			
			if (!is_object($this->updatePrePayToVerifiedReq)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->updatePrePayToVerifiedReq->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_updateReservationState_args {
	
	static $_TSPEC;
	public $requestHeader = null;
	public $req = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'requestHeader'
			),
			2 => array(
			'var' => 'req'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['requestHeader'])){
				
				$this->requestHeader = $vals['requestHeader'];
			}
			
			
			if (isset($vals['req'])){
				
				$this->req = $vals['req'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->requestHeader = new \com\vip\order\common\pojo\order\request\RequestHeader();
			$this->requestHeader->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->req = new \com\vip\order\biz\request\UpdateReservationStateReq();
			$this->req->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->requestHeader !== null) {
			
			$xfer += $output->writeFieldBegin('requestHeader');
			
			if (!is_object($this->requestHeader)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->requestHeader->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->req !== null) {
			
			$xfer += $output->writeFieldBegin('req');
			
			if (!is_object($this->req)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->req->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_userDeleteOrder_args {
	
	static $_TSPEC;
	public $header = null;
	public $req = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'header'
			),
			2 => array(
			'var' => 'req'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['header'])){
				
				$this->header = $vals['header'];
			}
			
			
			if (isset($vals['req'])){
				
				$this->req = $vals['req'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->header = new \com\vip\order\common\pojo\order\request\RequestHeader();
			$this->header->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->req = new \com\vip\order\biz\request\UserDeleteOrderReq();
			$this->req->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->header !== null) {
			
			$xfer += $output->writeFieldBegin('header');
			
			if (!is_object($this->header)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->header->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->req !== null) {
			
			$xfer += $output->writeFieldBegin('req');
			
			if (!is_object($this->req)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->req->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_verifyStockAndGetPayableFlag_args {
	
	static $_TSPEC;
	public $requestHeader = null;
	public $req = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			1 => array(
			'var' => 'requestHeader'
			),
			2 => array(
			'var' => 'req'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['requestHeader'])){
				
				$this->requestHeader = $vals['requestHeader'];
			}
			
			
			if (isset($vals['req'])){
				
				$this->req = $vals['req'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->requestHeader = new \com\vip\order\common\pojo\order\request\RequestHeader();
			$this->requestHeader->read($input);
			
		}
		
		
		
		
		if(true) {
			
			
			$this->req = new \com\vip\order\biz\request\VerifyStockAndGetPayableFlagReq();
			$this->req->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->requestHeader !== null) {
			
			$xfer += $output->writeFieldBegin('requestHeader');
			
			if (!is_object($this->requestHeader)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->requestHeader->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		if($this->req !== null) {
			
			$xfer += $output->writeFieldBegin('req');
			
			if (!is_object($this->req)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->req->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_addOrderTransport_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \com\vip\order\biz\response\AddOrderTransportResp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_autoPay_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \com\vip\order\biz\response\AutoPayResp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_autoPayFail_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \com\vip\order\biz\response\AutoPayFailResp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_autoTakeInventory_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \com\vip\order\biz\response\AutoTakeInventoryResp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_b2cSupportSendSms_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \com\vip\order\biz\response\B2CSupportSendSmsResp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_batchGetOrderActiveDetail_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \com\vip\order\biz\response\BatchGetOrderActiveDetailResp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_batchGetOrderList_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \com\vip\order\biz\response\BatchGetOrderListResp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_batchGetOrderTransportList_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \com\vip\order\biz\response\BatchGetOrderTransportListResp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_batchModifyOrderInvoice_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \com\vip\order\biz\response\BatchModifyOrderInvoiceResp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_batchUpdateWmsFlag_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \com\vip\order\biz\response\BatchUpdateWmsFlagResp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_calculateSplitOrderMoney_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \com\vip\order\biz\response\CalculateSplitOrderMoneyResp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_cancelOFixData_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \com\vip\order\biz\response\CancelOrderFixDataResp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_cancelOrder_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \com\vip\order\biz\response\CancelOrderResp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_cancelPresellOrder_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \com\vip\order\biz\response\CancelOrderResp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_checkCashOnDelivery_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \com\vip\order\biz\response\CheckCashOnDeliveryResp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_checkDeliveryFetchExchange_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \com\vip\order\biz\response\CheckDeliveryFetchExchangeResp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_checkDeliveryFetchReturn_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \com\vip\order\biz\response\CheckDeliveryFetchReturnResp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_checkOrderReturnVendorAudit_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \com\vip\order\biz\response\CheckOrderReturnVendorAuditResp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_confirmDelivered_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \com\vip\order\biz\response\ConfirmDeliveredResp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_confirmOrderGroupBuyResult_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \com\vip\order\biz\response\ConfirmOrderGroupBuyResp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_createOrder_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \com\vip\order\biz\response\CreateOrderResp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_createOrderElectronicInvoice_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \com\vip\order\biz\response\CreateOrderElectronicInvoiceResp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_createOrderSnV2_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \com\vip\order\biz\response\CreateOrderSnRespV2();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_createOrderV2_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \com\vip\order\biz\response\CreateOrderRespV2();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_cscCancelBack_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \com\vip\order\biz\response\CSCCancelBackResp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_displayOrder_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \com\vip\order\biz\response\DisplayOrderResp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_getAfterSaleOpType_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \com\vip\order\biz\response\GetAfterSaleOpTypeResp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_getCanAfterSaleOrderListByUserId_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \com\vip\order\biz\response\GetCanAfterSaleOrderListResp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_getCanRefundOrderCount_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \com\vip\order\biz\response\GetCanRefundOrderCountResp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_getCanRefundOrderList_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \com\vip\order\biz\response\GetCanRefundOrderListResp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_getEbsGoodsList_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \com\vip\order\biz\response\GetEbsGoodsListResp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_getGoodsDispatchWarehouse_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \com\vip\order\biz\response\GetGoodsDispatchWarehouseResp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_getLimitedOrderGoodsCount_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \com\vip\order\biz\response\GetLimitedOrderGoodsCountResp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_getMergeOrderList_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \com\vip\order\biz\response\GetMergeOrderResp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_getOrderCounts_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \com\vip\order\biz\response\OrderListCountResp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_getOrderCountsByUserId_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \com\vip\order\biz\response\OrderListCountResp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_getOrderDeliveryBoxNum_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \com\vip\order\biz\response\GetOrderDeliveryBoxNumResp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_getOrderDetail_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \com\vip\order\biz\response\SearchOrderDetailResp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_getOrderElectronicInvoicesV2_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \com\vip\order\biz\response\OrderElectronicInvoicesV2Resp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_getOrderFav_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \com\vip\order\biz\response\GetOrderFavResp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_getOrderGoodsCount_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \com\vip\order\biz\response\GetOrderGoodsCountResultResp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_getOrderGoodsList_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \com\vip\order\biz\response\GetOrderGoodsResultResp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_getOrderInstalmentsList_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \com\vip\order\biz\response\GetOrderInstalmentsListResp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_getOrderInvoicesV2_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \com\vip\order\biz\response\OrderInvoicesV2Resp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_getOrderList_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \com\vip\order\biz\response\SearchOrderListResp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_getOrderListByPosNo_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \com\vip\order\biz\response\GetOrderListByPosNoResp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_getOrderListByUserId_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \com\vip\order\biz\response\GetOrderListByUserIdResp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_getOrderLogs_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \com\vip\order\biz\response\GetOrderLogsResp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_getOrderOpStatus_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \com\vip\order\biz\response\GetOrderOpStatusResp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_getOrderPackageList_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \com\vip\order\biz\response\GetOrderPackageListResp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_getOrderPayType_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \com\vip\order\biz\response\GetOrderPayTypeResp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_getOrderSnByExOrderSn_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \com\vip\order\biz\response\GetOrderSnByExOrderSnResp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_getOrderTransport_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \com\vip\order\biz\response\GetOrderTransportResp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_getOrderTransportDetail_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \com\vip\order\biz\response\GetOrderTransportDetailResp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_getOrderTransportListByCodes_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \com\vip\order\biz\response\GetTransportListByCodesResp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_getOrdersBySizeId_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \com\vip\order\biz\response\GetOrdersBySizeIdResp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_getPrepayOrderStatus_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \com\vip\order\biz\response\GetPrepayOrderStatusResp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_getPrepayOrderUnpayMsg_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \com\vip\order\biz\response\GetPrepayOrderUnpayMsgResp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_getRdc_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \com\vip\order\biz\response\GetRdcResp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_getRdcInvoice_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \com\vip\order\biz\response\GetRdcInvoiceResp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_getReturnOrExchangeGoods_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \com\vip\order\biz\response\GetReturnOrExchangeGoodsResp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_getSimpleOrderFlowFlag_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \com\vip\order\biz\response\GetSimpleOrderFlowFlagResp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_getUnpayOrderList_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \com\vip\order\biz\response\GetUnpayOrderResp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_getUserDeliveryAddress_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \com\vip\order\biz\response\GetUserDeliveryAddressResp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_getUserFirstOrder_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \com\vip\order\biz\response\GetUserFirstOrderResp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_healthCheck_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \com\vip\hermes\core\health\CheckResult();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_mergeOrder_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \com\vip\order\biz\response\MergeOrderResp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_modifyOrderConsignee_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \com\vip\order\biz\response\ModifyOrderConsigneeResp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_modifyOrderElectronicInvoice_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \com\vip\order\biz\response\ModifyOrderElectronicInvoiceResp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_modifyOrderGoods_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \com\vip\order\biz\response\ModifyOrderGoodsResp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_modifyOrderPayType_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \com\vip\order\biz\request\ModifyOrderPayTypeRsp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_modifyOrderShipped_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \com\vip\order\biz\response\ModifyOrderShippedResp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_modifyPrepayOrderPayType_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \com\vip\order\biz\request\ModifyOrderPayTypeRsp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_notifyCustomsDeclarationFailed_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \com\vip\order\biz\response\NotifyCustomsDeclarationFailedResp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_ofcEntranceGrayControl_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \com\vip\order\biz\response\OfcEntranceGrayControlResp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_postOrderVMSMessage_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \com\vip\order\biz\response\PostOrderVMSMessageResp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_putIntoSplitQueue_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \com\vip\order\biz\response\PutIntoSplitQueueResp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_putKeyToRollbackQueue_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \com\vip\order\biz\response\PutKeyToRollbackQueueResp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_receptionConfirmDelivered_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \com\vip\order\biz\response\ReceptionConfirmDeliveredResp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_refundOrder_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \com\vip\order\biz\response\OrderRefundResp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_releaseStock_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \com\vip\order\biz\response\ReleaseStockResp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_rollbackOrder_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \com\vip\order\biz\response\RollbackOrderResp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_searchOrderListByUserId_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \com\vip\order\biz\response\SearchOrderListByUserIdResp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_signOrder_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \com\vip\order\biz\response\SignOrderResp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_triggerGroupByAuditOrder_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \com\vip\order\biz\response\GroupByOrderAuditResp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_updateAutoPayAuth_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \com\vip\order\biz\response\UpdateAutoPayAuthResp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_updateOrderPayResult_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \com\vip\order\biz\response\UpdateOrderPayResultResp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_updateOrderToReturnVerified_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \com\vip\order\biz\response\UpdateOrderToReturnVerifiedResp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_updatePayTypeToCOD_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \com\vip\order\biz\response\UpdatePayTypeToCODResp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_updatePrePayToVerified_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \com\vip\order\biz\response\UpdatePrePayToVerifiedResp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_updateReservationState_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \com\vip\order\biz\response\UpdateReservationStateResp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_userDeleteOrder_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \com\vip\order\biz\response\UserDeleteOrderResp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




class OrdersBizService_verifyStockAndGetPayableFlag_result {
	
	static $_TSPEC;
	public $success = null;
	
	public function __construct($vals=null){
		
		if (!isset(self::$_TSPEC)){
			
			self::$_TSPEC = array(
			0 => array(
			'var' => 'success'
			),
			
			);
			
		}
		
		if (is_array($vals)){
			
			
			if (isset($vals['success'])){
				
				$this->success = $vals['success'];
			}
			
			
		}
		
	}
	
	
	public function read($input){
		
		
		
		
		if(true) {
			
			
			$this->success = new \com\vip\order\biz\response\VerifyStockAndGetPayableFlagResp();
			$this->success->read($input);
			
		}
		
		
		
		
		
		
	}
	
	public function write($output){
		
		$xfer = 0;
		$xfer += $output->writeStructBegin();
		
		if($this->success !== null) {
			
			$xfer += $output->writeFieldBegin('success');
			
			if (!is_object($this->success)) {
				
				throw new \Osp\Exception\OspException('Bad type in structure.', \Osp\Exception\OspException::INVALID_DATA);
			}
			
			$xfer += $this->success->write($output);
			
			$xfer += $output->writeFieldEnd();
		}
		
		
		$xfer += $output->writeFieldStop();
		$xfer += $output->writeStructEnd();
		return $xfer;
	}
	
}




?>