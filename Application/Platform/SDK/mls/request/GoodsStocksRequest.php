<?php
/**
 * Created by PhpStorm.
 * User: MLS
 * Date: 15-1-6
 * Time: 下午6:26
 */

class GoodsStocksRequest{

      private $twitter_id;

    public function getAppParams()
    {
        $apiParams = array();
        $apiParams['twitter_id'] = $this->twitter_id;


        return $apiParams;
    }

    public function getApiMethod() {
        return "meilishuo.items.inventory.get";
    }

    public function getRequestMode()
    {
        return "GET";
    }

    public function setTwitterId( $twitterId ) {
        $this->twitter_id = $twitterId;
    }

    public function getTwitterId() {
        return $this->twitter_id;
    }



}