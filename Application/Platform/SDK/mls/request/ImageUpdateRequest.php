<?php
/**
 * Created by PhpStorm.
 * User: MLS
 * Date: 15-1-7
 * Time: 下午12:27
 */

class ImageUpdateRequest{

    private $image;
    private $ext;

    public function getAppParams()
    {
        $apiParams = array();
        $apiParams['image'] = $this->image;
        $apiParams['ext'] = $this->ext;

        return $apiParams;
    }

    public function getApiMethod() {
        return "meilishuo.image.upload";
    }

    public function getRequestMode()
    {
        return "POST";
    }

    public function setImage( $image ) {
        $this->image = $image;
    }

    public function getImage() {
        return $this->image;
    }

    public function setExt( $ext ) {
        $this->ext = $ext;
    }

    public function getExt() {
        return $this->ext;
    }


    
}