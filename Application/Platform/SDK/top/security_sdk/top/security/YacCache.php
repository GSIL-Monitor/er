<?php
/**
* yac实现cache
*/
date_default_timezone_set('Asia/Shanghai');

class YacCache implements iCache
{
	public $isEnable = true;
	private $sid;
	private $shopId;

	function __construct($sid, $shopId)
	{
		$this->sid=$sid;
		$this->shopId=$shopId;
	}

	public function getCache($key)
	{
		global $topAuthCacheData;
		if(isset($topAuthCacheData[$key]))
			return $topAuthCacheData[$key];

		return false;
	}

	public function setCache($key, $var)
	{
		global $topAuthCacheData;
		$topAuthCacheData[$key] = $var;
		logx("{$this->sid} {$this->shopId} set",'TopCache');
		//tb_logx("{$this->sid} {$this->shopId} set");
	}

}

?>