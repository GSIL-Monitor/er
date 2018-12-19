<?php
/**
 * TOP API: taobao.wlb.order.jz.query
 *
 * @author auto create
 * @since 1.0, 2013-01-06 16:39:45
 */

class WlbOrderJzQueryRequest
{
	private $tid;
	private $sender_id;
	private $ins_jz_receiver_t_o;
	private $jz_receiver_to;

	private $apiParas = array();

	public function setTid($tid)
	{
		$this->tid = $tid;
		$this->apiParas["tid"] = $tid;
	}

	public function getTid()
	{
		return $this->tid;
	}

	/**
	 * @return mixed
	 */
	public function getSenderId()
	{
		return $this->sender_id;
	}

	/**
	 * @param mixed $sender_id
	 */
	public function setSenderId($sender_id)
	{
		$this->sender_id = $sender_id;
		$this->apiParas["sender_id"] = $sender_id;

	}

	/**
	 * @return mixed
	 */
	public function getInsJzReceiverTO()
	{
		return $this->ins_jz_receiver_t_o;
	}

	/**
	 * @param mixed $ins_jz_receiver_t_o
	 */
	public function setInsJzReceiverTO($ins_jz_receiver_t_o)
	{
		$this->ins_jz_receiver_t_o = $ins_jz_receiver_t_o;
		$this->apiParas["ins_jz_receiver_t_o"] = $ins_jz_receiver_t_o;
	}

	/**
	 * @return mixed
	 */
	public function getJzReceiverTo()
	{
		return $this->jz_receiver_to;
	}

	/**
	 * @param mixed $jz_receiver_to
	 */
	public function setJzReceiverTo($jz_receiver_to)
	{
		$this->jz_receiver_to = $jz_receiver_to;
		$this->apiParas["jz_receiver_to"] = $jz_receiver_to;
	}



	public function getApiMethodName()
	{
		return "taobao.wlb.order.jz.query";
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
