<?php
/**
 * Created by PhpStorm.
 * User: MLS
 * Date: 15-1-7
 * Time: 上午10:50
 */

class GoodAddNewRequest{
    private $cid;
    private $shop_categories;
    private $title;
    private $description;
    private $tag;
    private $goods_no;
    private $sku_properties;
    private $sku_quantities;
    private $sku_prices;
    private $sku_outer_ids;
    private $size_details;
    private $color_images;
    private $cover_images;
    private $goods_intro;
    private $goods_intro_images;
    private $goods_detail;
    private $goods_detail_image;
    private $goods_reality_pat;
    private $goods_reality_pat_images;
    private $qualification_cert;
    private $qualification_cert_image;
    private $size_desc;
    private $size_desc_image;
    private $shop_intro;
    private $shop_intro_image;
    private $attributes;


    public function getAppParams()
    {
        $apiParams = array();
        $apiParams['cid'] = $this->cid;
        $apiParams['shop_categories'] = $this->shop_categories;
        $apiParams['title'] = $this->title;
        $apiParams['description'] = $this->description;
        $apiParams['tag'] = $this->tag;
        $apiParams['goods_no'] = $this->goods_no;
        $apiParams['sku_properties'] = $this->sku_properties;
        $apiParams['sku_quantities'] = $this->sku_quantities;
        $apiParams['sku_prices'] = $this->sku_prices;
        $apiParams['sku_outer_ids'] = $this->sku_outer_ids;
        $apiParams['size_details'] = $this->size_details;
        $apiParams['color_images'] = $this->color_images;
        $apiParams['cover_images'] = $this->cover_images;
        $apiParams['goods_intro'] = $this->goods_intro;
        $apiParams['goods_intro_images'] = $this->goods_intro_images;
        $apiParams['goods_detail'] = $this->goods_detail;
        $apiParams['goods_detail_image'] = $this->goods_detail_image;
        $apiParams['goods_reality_pat'] = $this->goods_reality_pat;
        $apiParams['goods_reality_pat_images'] = $this->goods_reality_pat_images;
        $apiParams['qualification_cert'] = $this->qualification_cert;
        $apiParams['qualification_cert_image'] = $this->qualification_cert_image;
        $apiParams['size_desc'] = $this->size_desc;
        $apiParams['size_desc_image'] = $this->size_desc_image;
        $apiParams['shop_intro'] = $this->shop_intro;
        $apiParams['shop_intro_image'] = $this->shop_intro_image;
        $apiParams['attributes'] = $this->attributes;

        return $apiParams;
    }

    public function getApiMethod() {
        return "meilishuo.items.add.new";
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

    public function setShopCategories( $shopCategories ) {
        $this->shop_categories = $shopCategories;
    }

    public function getShopCategories() {
        return $this->shop_categories;
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

    public function setSkuProperties( $skuProperties ) {
        $this->sku_properties = $skuProperties;
    }

    public function getSkuProperties() {
        return $this->sku_properties;
    }

    public function setSkuQuantities( $skuQuantities ) {
        $this->sku_quantities = $skuQuantities;
    }

    public function getSkuQuantities() {
        return $this->sku_quantities;
    }


    public function setSkuPrices( $skuPrices ) {
        $this->sku_prices = $skuPrices;
    }

    public function getSkuPrices() {
        return $this->sku_prices;
    }

    public function setSkuOuterIds( $skuOuterIds ) {
        $this->sku_outer_ids = $skuOuterIds;
    }

    public function getSkuOuterIds() {
        return $this->sku_outer_ids;
    }

    public function setSkuDetails( $sizeDetails ) {
        $this->size_details = $sizeDetails;
    }

    public function getSizeDetails() {
        return $this->size_details;
    }

    public function setColorImages( $colorImages ) {
        $this->color_images = $colorImages;
    }

    public function getColorImages() {
        return $this->color_images;
    }


    public function setCoverImages( $coverImages ) {
        $this->cover_images = $coverImages;
    }

    public function getCoverImages() {
        return $this->cover_images;
    }

    public function setGoodsIntro( $goodsIntro ) {
        $this->goods_intro = $goodsIntro;
    }

    public function getGoodsIntro() {
        return $this->goods_intro;
    }

    public function setGoodsIntroImages( $goodsIntroImages ) {
        $this->goods_intro_images = $goodsIntroImages;
    }

    public function getGoodsIntroImages() {
        return $this->goods_intro_images;
    }

    public function setGoodsDetail( $goodsDetail ) {
        $this->goods_detail = $goodsDetail;
    }

    public function getGoodsDetail() {
        return $this->goods_detail;
    }

    public function setGoodsDetailImage( $goodsDetailImage ) {
        $this->goods_detail_image = $goodsDetailImage;
    }

    public function getGoodsDetailImage() {
        return $this->goods_detail_image;
    }

    public function setGoodsRealityPat( $goodsRealityPat ) {
        $this->goods_reality_pat = $goodsRealityPat;
    }

    public function getGoodsRealityPat() {
        return $this->goods_reality_pat;
    }

    public function setGoodsRealityPatImage( $goodsRealityPatImage ) {
        $this->goods_reality_pat_images = $goodsRealityPatImage;
    }

    public function getGoodsRealityPatImage() {
        return $this->goods_reality_pat_images;
    }


    public function setQualificationCert( $gualificationCert ) {
        $this->qualification_cert = $gualificationCert;
    }

    public function getQualificationCert() {
        return $this->qualification_cert;
    }

    public function setGualificationCertImage( $gualificationCertImage ) {
        $this->qualification_cert_image = $gualificationCertImage;
    }

    public function getGualificationCertImage() {
        return $this->qualification_cert_image;
    }

    public function setSizeDesc( $sizeDesc ) {
        $this->size_desc = $sizeDesc;
    }

    public function getSizeDesc() {
        return $this->size_desc;
    }

    public function setSizeDescImage( $sizeDescImage ) {
        $this->size_desc_image = $sizeDescImage;
    }

    public function getSizeDescImage() {
        return $this->size_desc_image;
    }


    public function setShopIntro( $shopIntro ) {
        $this->shop_intro = $shopIntro;
    }

    public function getShopIntro() {
        return $this->shop_intro;
    }

    public function setShopIntroContext( $shopIntroImage ) {
        $this->shop_intro_image = $shopIntroImage;
    }

    public function getShopIntroImage() {
        return $this->shop_intro_image;
    }


    public function setAttributes( $attributes ) {
        $this->attributes = $attributes;
    }

    public function getAttributes() {
        return $this->attributes;
    }




}