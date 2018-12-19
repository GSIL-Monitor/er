<?php
/**
 * Created by PhpStorm.
 * User: MLS
 * Date: 15-1-6
 * Time: 下午4:11
 */

class OrderDetailsRequest{

    private $order_id;

    public function getAppParams()
    {
        $apiParams = array();
        $apiParams['order_id'] = $this->order_id;


        return $apiParams;
    }

    public function getApiMethod() {
        return "meilishuo.order.detail.get";
    }

    public function getRequestMode()
    {
        return "GET";
    }

    public function setOrderId($orderId) {
        $this->order_id = $orderId;
    }

    public function getOrderId() {
        return $this->order_id;
    }

}