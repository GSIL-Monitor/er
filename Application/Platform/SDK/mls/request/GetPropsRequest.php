<?php
/**
 * Created by PhpStorm.
 * User: MLS
 * Date: 15-1-7
 * Time: 下午12:22
 */

class GetPropsRequest{

    private $sort_id;

    public function getAppParams()
    {
        $apiParams = array();
        $apiParams['sort_id'] = $this->sort_id;

        return $apiParams;
    }

    public function getApiMethod() {
        return "meilishuo.items.get.props";
    }

    public function getRequestMode()
    {
        return "GET";
    }


    public function setSortId( $sortId ) {
        $this->sort_id = $sortId;
    }

    public function getSortId() {
        return $this->sort_id;
    }


}