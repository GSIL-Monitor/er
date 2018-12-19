<?php

include 'PddClient.php';

include 'request/RefundListIncrementGetRequest.php';
include 'request/GoodsListGetRequest.php';
include 'request/OrderNumberListIncrGetRequest.php';
include 'request/LogisticsCompaniesGetRequest.php';
include 'request/OrderInformationGetRequest.php';
include 'request/RefundStatusCheckRequest.php';
include 'request/LogisticsOnlineSendRequest.php';
include 'request/OrderNumberListGetRequest.php';
include 'request/OrderStatusGetRequest.php';
include 'request/GoodsSkuStockGetRequest.php';
include 'request/GoodsSkuStockUpdateRequest.php';
include 'request/GoodsSkuStockIncrUpdateRequest.php';
include 'request/GoodsInfoGetRequest.php';


class PddTest {

    public $pddClient;

    function __construct()
    {
        $pddClient = new PddClient();
        $pddClient->mallId = 53148;
        $pddClient->serverUrl = 'http://172.16.1.205:8787/api/router';
        $pddClient->clientSecret = '187800';
        $pddClient->dataType = 'JSON';
        $this->pddClient = $pddClient;
    }

    public  function refundListIncrementGetTest ()
    {
        $refundListIncrementGetRequest = new RefundListIncrementGetRequest();
        $refundListIncrementGetRequest->setAfterSalesType(1);
        $refundListIncrementGetRequest->setAfterSalesStatus(1);
        $refundListIncrementGetRequest->setStartUpdatedAt(1469980800);
        $refundListIncrementGetRequest->setEndUpdatedAt(1472659200);
        return $this->pddClient->execute($refundListIncrementGetRequest);
    }

    public  function goodsListGetTest ()
    {
        $goodListGetRequest = new GoodsListGetRequest();
        $goodListGetRequest->setGoodsName('乐事薯片');
        $goodListGetRequest->setOuterId(111111);
        $goodListGetRequest->setIsOnsale(0);
        return $this->pddClient->execute($goodListGetRequest);
    }

    public function orderNumberListIncrGetTest ()
    {
        $request = new OrderNumberListIncrGetRequest();
        $request->setOrderStatus(5);
        $request->setRefundStatus(5);
        $request->setIsLuckyFlag(1);
        $request->setStartUpdatedAt(1479891000);
        $request->setEndUpdatedAt(1479891120);
        return $this->pddClient->execute($request);
    }

    public function logisticsCompaniesGetTest()
    {
        $request = new LogisticsCompaniesGetRequest();
        return $this->pddClient->execute($request);
    }

    public function orderInformationGetTest ()
    {
        $request = new OrderInformationGetRequest();
        $request->setOrderSn('170104-38554872000');
        return $this->pddClient->execute($request);
    }

    public  function refundStatusCheckTest ()
    {
        $request = new RefundStatusCheckRequest();
        $request->setOrderSns('20150909-452750051,20150909-452750134');
        return $this->pddClient->execute($request);
    }

    public function logisticsOnlineSendTest ()
    {
        $request = new LogisticsOnlineSendRequest();
        $request->setLogisticsId(1);
        $request->setOrderSn('20150909-452750051');
        $request->setTrackingNumber('121313124');
        return $this->pddClient->execute($request);
    }

    public function orderNumberListGetTest ()
    {
        $request = new OrderNumberListGetRequest();
        $request->setOrderStatus(1);
        return $this->pddClient->execute($request);
    }

    public function orderStatusGetTest ()
    {
        $request = new OrderStatusGetRequest();
        $request->setOrderSns('20150909-452750051,20150909-452750134');
        return $this->pddClient->execute($request);
    }

    public function goodsSkuStockGetTest ()
    {
        $request = new GoodsSkuStockGetRequest();
        $request->setSkuIds('879512');
        return $this->pddClient->execute($request);
    }

    public  function goodsSkuStockUpdateTest ()
    {
        $request = new GoodsSkuStockUpdateRequest();
        $request->setSkuId('879512');
        $request->setQuantity('12');
        return $this->pddClient->execute($request);
    }

    public function goodsSkuStockIncrUpdateTest ()
    {
        $request = new GoodsSkuStockIncrUpdateRequest();
        $request->setSkuId('879512');
        $request->setIncrementQuantity('12');
        return $this->pddClient->execute($request);
    }
	 public function goodsInfoGetTest ()
    {
        $request = new GoodsInfoGetRequest();
        $request->getGoodsId('879512');
        return $this->pddClient->execute($request);
    }
}

$client = new PddTest();
$result = $client->orderInformationGetTest();
var_dump($result);