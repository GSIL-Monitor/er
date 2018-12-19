<?php
/**
 * Created by PhpStorm.
 * User: MLS
 * Date: 15-1-6
 * Time: 下午4:25
 */

class OrderDeliverRequest{

    private $order_id;
    private $oid;
    private $express_company;
    private $express_id;

    public function getAppParams()
    {
        $apiParams = array();
        $apiParams['oid'] = $this->oid;
        $apiParams['order_id'] = $this->order_id;
        $apiParams['express_id'] = $this->express_id;
        $apiParams['express_company'] = $this->express_company;

        return $apiParams;
    }

    public function getApiMethod() {
        return "meilishuo.order.deliver";
    }

    public function getRequestMode()
    {
        return "POST";
    }


    public function setOrderId($orderId) {
        $this->order_id = $orderId;
    }

    public function getOrderId() {
        return $this->order_id;
    }

    public function setOid( $oid ) {
        $this->oid = $oid;
    }

    public function getOid() {
        return $this->oid;
    }

    public function setExpressId( $expressId ) {
        $this->express_id = $expressId;
    }

    public function getExpressId() {
        return $this->express_id;
    }

    public function setExpressCompany( $expressCompany ) {
        $this->express_company = $expressCompany;
    }

    public function getExpressCompany() {
        return $this->express_company;
    }
}