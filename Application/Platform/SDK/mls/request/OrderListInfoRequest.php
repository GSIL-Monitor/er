<?php
/**
 * Created by PhpStorm.
 * User: MLS
 * Date: 15-1-6
 * Time: 下午3:16
 */

class OrderListInfoRequest{

    private $page;
    private $status;
    private $page_size;
    private $ctime_end;
    private $ctime_start;
    private $uptime_start;
    private $uptime_end;

    public function getAppParams(){
        $apiParams = array();
        $apiParams['page'] = $this->page;
        $apiParams['status'] = $this->status;
        $apiParams['page_size'] = $this->page_size;
        $apiParams['ctime_end'] = $this->ctime_end;
        $apiParams['uptime_end'] = $this->uptime_end;
        $apiParams['ctime_start'] = $this->ctime_start;
        $apiParams['uptime_start'] = $this->uptime_start;


        return $apiParams;
    }

    public function getApiMethod() {
        return "meilishuo.order.list.get";
    }

    public function getRequestMode()
    {
        return "GET";
    }

    public function setPage($page) {
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

    public function setCtimeEnd( $ctimeEnd ) {
        $this->ctime_end = $ctimeEnd;
    }

    public function getCtimeEnd() {
        return $this->ctime_end;
    }

    public function setUptimeEnd( $uptimeEnd ) {
        $this->uptime_end = $uptimeEnd;
    }

    public function getUptimeEnd() {
        return $this->uptime_end;
    }

    public function setCtimeStrat( $ctimeStrat ) {
        $this->ctime_start = $ctimeStrat;
    }

    public function getCtimeStrat() {
        return $this->ctime_start;
    }

    public function setUptimeStart( $uptimeStart ) {
        $this->uptime_start = $uptimeStart;
    }

    public function getUptimeStart() {
        return $this->uptime_start;
    }
}