<?php
/**
 * Created by PhpStorm.
 * User: MLS
 * Date: 15-1-7
 * Time: 上午11:54
 */

class GoodUpdateRequest{

    private $num_iid;
    private $twitter_id;
    private $up_status;

    public function getAppParams()
    {
        $apiParams = array();
        $apiParams['num_iid'] = $this->num_iid;
        $apiParams['twitter_id'] = $this->twitter_id;
        $apiParams['up_status'] = $this->up_status;

        return $apiParams;
    }

    public function getApiMethod() {
        return "meilishuo.item.update.delisting";
    }

    public function getRequestMode()
    {
        return "POST";
    }

    public function setNumIid( $numIid ) {
        $this->num_iid = $numIid;
    }

    public function getNumIid() {
        return $this->num_iid;
    }

    public function setTwitterId( $twitterId ) {
        $this->twitter_id = $twitterId;
    }

    public function getTwitterId() {
        return $this->twitter_id;
    }

    public function setUpStatus( $upStatus ) {
        $this->up_status = $upStatus;
    }

    public function getUpStatus() {
        return $this->up_status;
    }



}