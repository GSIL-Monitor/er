<?php

class GoodsInfoGetRequest_pdd {

    private $apiParas = array();

    public function getApiTypeName(){
        return "pdd.goods.information.get";
    }

    public function getApiParas(){
        return $this->apiParas;
    }

    public function check(){
		if (!isset($this->apiParas['goods_id'])) {
            $errMsg = 'not exists param : goods_id';
            throw new Exception($errMsg,0);
        }
    }

    private $goods_id;

    public function setGoodsId($goods_id){
        $this->goods_id = $goods_id;
        $this->apiParas["goods_id"] = $goods_id;
    }

    public function getGoodsId(){
        return $this->goods_id;
    }

}