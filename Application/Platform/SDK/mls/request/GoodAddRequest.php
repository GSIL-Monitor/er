<?php
/**
 * Created by PhpStorm.
 * User: MLS
 * Date: 15-1-6
 * Time: 下午7:50
 */

class GoodAddRequest{
      private $cid;
      private $shop_category;
      private $title;
      private $description;
      private $tag;
      private $goods_no;
      private $show_image;
      private $attributes;
      private $sku_properties;
      private $sku_stocks;
      private $sku_style_no;
      private $sku_images;
      private $sku_size_detail;
      private $sku_prices;
      private $sku_max_price;
      private $goods_desc;
      private $goods_desc_context;
      private $goods_detail;
      private $goods_detail_context;
      private $goods_shot;
      private $goods_shot_context;
      private $qualification_cert;
      private $qualification_cert_context;
      private $size_desc;
      private $size_desc_context;
      private $shop_intro;
      private $shop_intro_context;
      private $version;

    public function getAppParams()
    {
        $apiParams = array();
        $apiParams['cid'] = $this->cid;
        $apiParams['shop_category'] = $this->shop_category;
        $apiParams['title'] = $this->title;
        $apiParams['description'] = $this->description;
        $apiParams['tag'] = $this->tag;
        $apiParams['goods_no'] = $this->goods_no;
        $apiParams['show_image'] = $this->show_image;
        $apiParams['attributes'] = $this->attributes;
        $apiParams['sku_properties'] = $this->sku_properties;
        $apiParams['sku_stocks'] = $this->sku_stocks;
        $apiParams['sku_style_no'] = $this->sku_style_no;
        $apiParams['sku_images'] = $this->sku_images;
        $apiParams['sku_size_detail'] = $this->sku_size_detail;
        $apiParams['sku_prices'] = $this->sku_prices;
        $apiParams['sku_max_price'] = $this->sku_max_price;
        $apiParams['goods_desc'] = $this->goods_desc;
        $apiParams['goods_desc_context'] = $this->goods_desc_context;
        $apiParams['goods_detail'] = $this->goods_detail;
        $apiParams['goods_detail_context'] = $this->goods_detail_context;
        $apiParams['goods_shot'] = $this->goods_shot;
        $apiParams['goods_shot_context'] = $this->goods_shot_context;
        $apiParams['qualification_cert'] = $this->qualification_cert;
        $apiParams['qualification_cert_context'] = $this->qualification_cert_context;
        $apiParams['size_desc'] = $this->size_desc;
        $apiParams['size_desc_context'] = $this->size_desc_context;
        $apiParams['shop_intro'] = $this->shop_intro;
        $apiParams['shop_intro_context'] = $this->shop_intro_context;
        $apiParams['version'] = $this->version;



        return $apiParams;
    }

    public function getApiMethod() {
        return "meilishuo.items.add";
    }

    public function getRequestMode()
    {
        return "POST";
    }

    public function setCid( $cid ) {
        $this->cid = $cid;
    }

    public function getCid() {
        return $this->cid;
    }

    public function setShopCategory( $shopCategory ) {
        $this->shop_category = $shopCategory;
    }

    public function getShopCategory() {
        return $this->shop_category;
    }

    public function setTitle( $title ) {
        $this->title = $title;
    }

    public function getTitle() {
        return $this->title;
    }

    public function setDescription( $description ) {
        $this->description = $description;
    }

    public function getDescription() {
        return $this->description;
    }

    public function setTag( $tag ) {
        $this->tag = $tag;
    }

    public function getTag() {
        return $this->tag;
    }

    public function setGoodNo( $goodsNo ) {
        $this->goods_no = $goodsNo;
    }

    public function getGoodNo() {
        return $this->goods_no;
    }

    public function setShowImage( $showImage ) {
        $this->show_image = $showImage;
    }

    public function getShowImage() {
        return $this->show_image;
    }

    public function setAttributes( $attributes ) {
        $this->attributes = $attributes;
    }

    public function getAttributes() {
        return $this->attributes;
    }

    public function setSkuProperties( $skuProperties ) {
        $this->sku_properties = $skuProperties;
    }

    public function getSkuProperties() {
        return $this->sku_properties;
    }

    public function setSkuStocks( $skuStocks ) {
        $this->sku_stocks = $skuStocks;
    }

    public function getSkuStocks() {
        return $this->sku_stocks;
    }

    public function setSkuStyleNo( $skuStyleNo ) {
        $this->sku_style_no = $skuStyleNo;
    }

    public function getSkuStyleNo() {
        return $this->sku_style_no;
    }


    public function setSkuImages( $skuImages ) {
        $this->sku_images = $skuImages;
    }

    public function getSkuImages() {
        return $this->sku_images;
    }

    public function setSkuSizeDetail( $skuSizeDetail ) {
        $this->sku_size_detail = $skuSizeDetail;
    }

    public function getSkuSizeDetail() {
        return $this->sku_size_detail;
    }

    public function setSkuPrices( $skuPrices ) {
        $this->sku_prices = $skuPrices;
    }

    public function getSkuPrices() {
        return $this->sku_prices;
    }

    public function setSkuMaxPrice( $skuMaxPrice ) {
        $this->sku_max_price = $skuMaxPrice;
    }

    public function getSkuMaxPrice() {
        return $this->sku_max_price;
    }

    public function setGoodsDesc( $goodsDesc ) {
        $this->goods_desc = $goodsDesc;
    }

    public function getGoodsDesc() {
        return $this->goods_desc;
    }

    public function setGoodsDescContext( $goodsDescContext ) {
        $this->goods_desc_context = $goodsDescContext;
    }

    public function getGoodsDescContext() {
        return $this->goods_desc_context;
    }

    public function setGoodsDetail( $goodsDetail ) {
        $this->goods_detail = $goodsDetail;
    }

    public function getGoodsDetail() {
        return $this->goods_detail;
    }

    public function setGoodsDetailContext( $goodsDetailContext ) {
        $this->goods_detail_context = $goodsDetailContext;
    }

    public function getGoodsDetailContext() {
        return $this->goods_detail_context;
    }

    public function setGoodsShot( $goodsShot ) {
        $this->goods_shot = $goodsShot;
    }

    public function getGoodsShot() {
        return $this->goods_shot;
    }

    public function setGoodsShotContext( $goodsShotContext ) {
        $this->goods_shot_context = $goodsShotContext;
    }

    public function getGoodsShotContext() {
        return $this->goods_shot_context;
    }

    public function setQualificationCert( $gualificationCert ) {
        $this->qualification_cert = $gualificationCert;
    }

    public function getQualificationCert() {
        return $this->qualification_cert;
    }

    public function setGualificationCertContext( $gualificationCertContext ) {
        $this->qualification_cert_context = $gualificationCertContext;
    }

    public function getGualificationCertContext() {
        return $this->qualification_cert_context;
    }

    public function setSizeDesc( $sizeDesc ) {
        $this->size_desc = $sizeDesc;
    }

    public function getSizeDesc() {
        return $this->size_desc;
    }

    public function setSizeDescContext( $sizeDescContext ) {
        $this->size_desc_context = $sizeDescContext;
    }

    public function getSizeDescContext() {
        return $this->size_desc_context;
    }

    public function setShopIntro( $shopIntro ) {
        $this->shop_intro = $shopIntro;
    }

    public function getShopIntro() {
        return $this->shop_intro;
    }

    public function setShopIntroContext( $shopIntroContext ) {
        $this->shop_intro_context = $shopIntroContext;
    }

    public function getShopIntroContext() {
        return $this->shop_intro_context;
    }

    public function setVersion( $version ) {
        $this->version = $version;
    }

    public function getVersion() {
        return $this->version;
    }
}