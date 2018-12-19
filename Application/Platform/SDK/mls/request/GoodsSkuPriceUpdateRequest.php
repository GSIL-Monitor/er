<?php
/**
 * Created by PhpStorm.
 * User: MLS
 * Date: 15-1-6
 * Time: 下午6:51
 */

class GoodsSkuPriceUpdateRequest{

    private $twitter_id;
    private $goods_code;
    private $sku_price;

    public function getAppParams()
    {
        $apiParams = array();
        $apiParams['twitter_id'] = $this->twitter_id;
        $apiParams['goods_code'] = $this->goods_code;
        $apiParams['sku_price'] = $this->sku_price;


        return $apiParams;
    }

    public function getApiMethod() {
        return "meilishuo.items.inventory.get";
    }

    public function getRequestMode()
    {
        return "POST";
    }

    public function setTwitterId( $twitterId ) {
        $this->twitter_id = $twitterId;
    }

    public function getTwitterId() {
        return $this->twitter_id;
    }

    public function setGoodsCode( $goodsCode ) {
        $this->goods_code = $goodsCode;
    }

    public function getGoodsCode() {
        return $this->goods_code;
    }

    public function setSkuPrice( $skuPrice ) {
        $this->sku_price = $skuPrice;
    }

    public function getSkuPrice() {
        return $this->sku_price;
    }

}