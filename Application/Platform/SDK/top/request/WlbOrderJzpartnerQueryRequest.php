<?php
/**
 * TOP API: taobao.wlb.order.jzpartner.query
 *
 * @author auto create
 * @since 1.0, 2013-01-06 16:39:45
 */

class WlbOrderJzpartnerQueryRequest
{
	/**
	 * 商品ID
	 **/
	private $tid;
	private $type;

	private $apiParas = array();

	public function setTid($tid)
	{
		$this->tid = $tid;
		$this->apiParas["taobao_trade_id"] = $tid;
	}

	public function getTid()
	{
		return $this->tid;
	}

	/**
	 * @return mixed
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * @param mixed $type
	 */
	public function setType($type)
	{
		$this->type = $type;
		$this->apiParas["service_type"] = $type;
	}

	public function getApiMethodName()
	{
		return "taobao.wlb.order.jzpartner.query";
	}

	public function getApiParas()
	{
		return $this->apiParas;
	}

	public function check()
	{

		//RequestCheckUtil::checkNotNull($this->tid,"tid");
	}

	public function putOtherTextParam($key, $value) {
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}
}
