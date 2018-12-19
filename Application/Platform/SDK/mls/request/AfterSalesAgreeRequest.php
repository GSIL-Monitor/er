<?php
/**
 * Created by PhpStorm.
 * User: MLS
 * Date: 15-1-6
 * Time: 下午5:23
 */

class AfterSalesAgreeRequest{

    private $fields;
    private $addr_id;
    private $refund_id;

    public function getAppParams()
    {
        $apiParams = array();
        $apiParams['fields'] = $this->fields;
        $apiParams['addr_id'] = $this->addr_id;
        $apiParams['refund_id'] = $this->refund_id;


        return $apiParams;
    }

    public function getApiMethod() {
        return "meilishuo.aftersales.agree";
    }

    public function getRequestMode()
    {
        return "POST";
    }

    public function setFields( $fields ) {
        $this->fields = $fields;
    }

    public function getFields() {
        return $this->fields;
    }

    public function setRefundId( $refundId ) {
        $this->refund_id = $refundId;
    }

    public function getRefundId() {
        return $this->refund_id;
    }

    public function setAddrId( $addrId ) {
        $this->addr_id = $addrId;
    }

    public function getAddrId() {
        return $this->addr_id;
    }


}