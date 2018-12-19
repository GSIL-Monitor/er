 <?php
/**
 * @author autumn
 *
 */
class GoodsStockUpdateRequest
{
	private $twitter_id;
	private $modify_type;
	private $modify_value;
	private $sku_id;
	private $goods_code;
	
	
	/**
	 * @return the $sku_id
	 */
	public function getSku_id() {
		return $this->sku_id;
	}

	/**
	 * @return the $goods_code
	 */
	public function getGoods_code() {
		return $this->goods_code;
	}

	/**
	 * @param field_type $sku_id
	 */
	public function setSku_id($sku_id) {
		$this->sku_id = $sku_id;
	}

	/**
	 * @param field_type $goods_code
	 */
	public function setGoods_code($goods_code) {
		$this->goods_code = $goods_code;
	}

	public function getAppParams()
    {
        $apiParams = array();
        $apiParams['twitter_id'] = $this->twitter_id;
		$apiParams['modify_type'] = $this->modify_type;
		$apiParams['modify_value'] = $this->modify_value;
		$apiParams['sku_id'] = $this->sku_id;
		$apiParams['goods_code'] = $this->goods_code;
        return $apiParams;
    }
	
	public function getApiMethod() 
	{
        return "meilishuo.item.quantity.update";
    }

    public function getRequestMode()
    {
        return "GET";
    }

    public function setTwitterId( $twitterId ) 
	{
        $this->twitter_id = $twitterId;
    }

    public function getTwitterId() 
	{
        return $this->twitter_id;
    }
	

    /**
     * @return the $modify_value
     */
    public function getModify_value() {
    	return $this->modify_value;
    }
    
    /**
     * @param field_type $modify_value
     */
    public function setModify_value($modify_value) {
    	$this->modify_value = $modify_value;
    }
    
    /**
     * @return the $modify_type
     */
    public function getModify_type() {
    	return $this->modify_type;
    }
    
    /**
     * @param field_type $modify_type
     */
    public function setModify_type($modify_type) {
    	$this->modify_type = $modify_type;
    }
    
	
	
}