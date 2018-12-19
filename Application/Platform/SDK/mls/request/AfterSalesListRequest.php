<?php
/**
 * Created by PhpStorm.
 * User: MLS
 * Date: 15-1-6
 * Time: 下午4:39
 */

class AfterSalesListRequest{

    private $fields;
    private $status;
    private $apply_stime;
    private $apply_etime;
    private $uptime_start;
    private $uptime_end;
    private $page_no;
    private $page_size;

    public function getAppParams()
    {
        $apiParams = array();
        $apiParams['fields'] = $this->fields;
        $apiParams['status'] = $this->status;
        $apiParams['page_no'] = $this->page_no;
        $apiParams['apply_stime'] = $this->apply_stime;
        $apiParams['apply_etime'] = $this->apply_etime;
        $apiParams['uptime_start'] = $this->uptime_start;
        $apiParams['uptime_end'] = $this->uptime_end;
        $apiParams['page_size'] = $this->page_size;


        return $apiParams;
    }

    public function getApiMethod() {
        return "meilishuo.aftersales.list.get";
    }

    public function getRequestMode()
    {
        return "POST";
    }


    public function setFields($fields) {
        $this->fields = $fields;
    }

    public function getFields() {
        return $this->fields;
    }

    public function setStatus($status) {
        $this->status = $status;
    }

    public function getStatus() {
        return $this->status;
    }

    public function setPageNo( $pageNo ) {
        $this->page_no = $pageNo;
    }

    public function getPageNo() {
        return $this->page_no;
    }

    public function setApplyStime( $applyStime ) {
        $this->apply_stime = $applyStime;
    }

    public function getApplyStime() {
        return $this->apply_stime;
    }

    public function setApplyEtime( $applyEtime ) {
        $this->apply_etime = $applyEtime;
    }

    public function getApplyEtime() {
        return $this->apply_etime;
    }

    public function setUptimeStart( $uptimeStart ) {
        $this->uptime_start = $uptimeStart;
    }

    public function getUptimeStart() {
        return $this->uptime_start;
    }

    public function setUptimeEnd( $uptime_end ) {
        $this->uptime_end = $uptime_end;
    }

    public function getUptimeEnd() {
        return $this->uptime_end;
    }

    public function setPageSize( $pageSize ) {
        $this->page_size = $pageSize;
    }

    public function getPageSize() {
        return $this->page_size;
    }
}