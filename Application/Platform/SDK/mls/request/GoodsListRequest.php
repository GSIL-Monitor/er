<?php
/**
 * Created by PhpStorm.
 * User: MLS
 * Date: 15-1-6
 * Time: 下午5:52
 */

class GoodsListRequest{
    private $page;
    private $page_size;
    private $status;


    public function getAppParams()
    {
        $apiParams = array();
        $apiParams['page'] = $this->page;
        $apiParams['status'] = $this->status;
        $apiParams['page_size'] = $this->page_size;


        return $apiParams;
    }

    public function getApiMethod() {
        return "meilishuo.items.list.get";
    }

    public function getRequestMode()
    {
        return "GET";
    }

    public function setPage( $page ) {
        $this->page = $page;
    }

    public function getPage() {
        return $this->page;
    }

    public function setStatus( $status ) {
        $this->status = $status;
    }

    public function getStatus() {
        return $this->status;
    }

    public function setPageSize( $pageSize ) {
        $this->page_size = $pageSize;
    }

    public function getPageSize() {
        return $this->page_size;
    }
}