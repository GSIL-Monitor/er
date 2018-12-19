<?php
/**
 * Created by PhpStorm.
 * User: MLS
 * Date: 15-1-6
 * Time: 下午7:05
 */

class GoodsStocksEditRequest{

    private $twitter_id;
    private $goods_code;
    private $sku_id;
    private $modify_type;
    private $modify_value;

    public function getAppParams()
    {
        $apiParams = array();
        $apiParams['sku_id'] = $this->sku_id;
        $apiParams['twitter_id'] = $this->twitter_id;
        $apiParams['goods_code'] = $this->goods_code;
        $apiParams['modify_type'] = $this->modify_type;
        $apiParams['modify_value'] = $this->modify_value;



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

    public function setGoodCode( $goodCode ) {
        $this->goods_code = $goodCode;
    }

    public function getGoodCode() {
        return $this->goods_code;
    }

    public function setSkuId( $skuId ) {
        $this->sku_id = $skuId;
    }

    public function getSkuId() {
        return $this->sku_id;
    }

    public function setModifyType( $modifyType ) {
        $this->modify_type = $modifyType;
    }

    public function getModifyType() {
        return $this->modify_type;
    }

}