<?php
/**
 * Created by PhpStorm.
 * User: MLS
 * Date: 15-1-6
 * Time: 下午5:00
 */

class AfterSalesDetailsRequest{

    private $fields;
    private $refund_id;

    public function getAppParams()
    {
        $apiParams = array();
        $apiParams['fields'] = $this->fields;
        $apiParams['refund_id'] = $this->refund_id;


        return $apiParams;
    }

    public function getApiMethod() {
        return "meilishuo.aftersales.detail.get";
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
}