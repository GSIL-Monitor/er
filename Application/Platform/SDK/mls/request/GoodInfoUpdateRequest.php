<?php
/**
 * Created by PhpStorm.
 * User: MLS
 * Date: 15-1-7
 * Time: 下午12:04
 */

class GoodInfoUpdateRequest{

    private $twitter_id;
    private $goods_title;
    private $goods_desc;
    private $goods_tags;

    public function getAppParams()
    {
        $apiParams = array();
        $apiParams['twitter_id'] = $this->twitter_id;
        $apiParams['goods_title'] = $this->goods_title;
        $apiParams['goods_desc'] = $this->goods_desc;
        $apiParams['goods_tags'] = $this->goods_tags;



        return $apiParams;
    }

    public function getApiMethod() {
        return "meilishuo.item.update";
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

    public function setGoodsTitle( $goodsTitle ) {
        $this->goods_title = $goodsTitle;
    }

    public function getGoodsTitle() {
        return $this->goods_title;
    }

    public function setGoodsDesc( $goodsDesc ) {
        $this->goods_desc = $goodsDesc;
    }

    public function getGoodsDesc() {
        return $this->goods_desc;
    }

    public function setGoodsTags( $goodsTags ) {
        $this->goods_tags = $goodsTags;
    }

    public function getGoodsTags() {
        return $this->goods_tags;
    }



}