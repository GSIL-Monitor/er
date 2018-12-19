<?php
/**
 * TOP API: tmall.item.outerid.update request
 * 
 * @author auto create
 * @since 1.0, 2015.09.09
 */
class TmallItemOuteridUpdateRequest
{
	/** 
	 * 商品ID
	 **/
	private $itemId;
	
	/** 
	 * 商品维度商家编码，如果不修改可以不传；清空请设置空串
	 **/
	private $outerId;
	
	/** 
	 * 商品SKU更新OuterId时候用的数据
	 **/
	private $skuOuters;
	
	private $apiParas = array();
	
	public function setItemId($itemId)
	{
		$this->itemId = $itemId;
		$this->apiParas["item_id"] = $itemId;
	}

	public function getItemId()
	{
		return $this->itemId;
	}

	public function setOuterId($outerId)
	{
		$this->outerId = $outerId;
		$this->apiParas["outer_id"] = $outerId;
	}

	public function getOuterId()
	{
		return $this->outerId;
	}

	public function setSkuOuters($skuOuters)
	{
		$this->skuOuters = $skuOuters;
		$this->apiParas["sku_outers"] = $skuOuters;
	}

	public function getSkuOuters()
	{
		return $this->skuOuters;
	}

	public function getApiMethodName()
	{
		return "tmall.item.outerid.update";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
	
	public function check()
	{
		
		RequestCheckUtil::checkNotNull($this->itemId,"itemId");
	}
	
	public function putOtherTextParam($key, $value) {
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}
}

/**
 * 商品SKU更新OuterId时候用的数据
 * @author auto create
 */
class UpdateSkuOuterId
{
	
	/** 
	 * 被更新的Sku的商家外部id；如果清空，传空串
	 **/
	public $outerId;
	
	/** 
	 * Sku属性串。格式:pid:vid;pid:vid,如: 1627207:3232483;1630696:3284570,表示机身颜色:军绿色;手机套餐:一电一充
	 **/
	public $properties;
	
	/** 
	 * SKU的ID
	 **/
	public $skuId;	
}
