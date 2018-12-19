<?php
/**
 * Created by PhpStorm.
 * User: MLS
 * Date: 15-1-6
 * Time: 下午6:44
 */

class SkuStockRequest{

    private $goods_code;

    public function getAppParams()
    {
        $apiParams = array();
        $apiParams['goods_code'] = $this->goods_code;


        return $apiParams;
    }

    public function getApiMethod() {
        return "meilishuo.item.sku.get";
    }

    public function getRequestMode()
    {
        return "GET";
    }

    public function setGoodsCode( $goodsCode ) {
        $this->goods_code = $goodsCode;
    }

    public function getGoodsCode() {
        return $this->goods_code;
    }



}