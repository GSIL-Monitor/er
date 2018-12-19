<?php
/**
 * Created by PhpStorm.
 * User: MLS
 * Date: 15-1-7
 * Time: 下午12:18
 */

class CategoryListRequest{



    public function getAppParams()
    {
        $apiParams = array();

        return $apiParams;
    }

    public function getApiMethod() {
        return "meilishuo.items.category.list";
    }

    public function getRequestMode()
    {
        return "POST";
    }



}