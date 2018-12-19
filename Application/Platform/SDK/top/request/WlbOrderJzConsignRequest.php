 <?php
/**
 * TOP API: taobao.wlb.order.jz.consign
 *
 * @author auto create
 * @since 1.0, 2013-01-06 16:39:45
 */

class WlbOrderJzConsignRequest
{
	private $tid;
	private $sender_id;
	private $jz_receiver_to;
	private $jz_top_args;
	private $lg_tp_dto;
	private $ins_tp_dto;
	private $ins_receiver_to;

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
	}

	/**
	 * @return mixed
	 */
	public function getJzTopArgs()
	{
		return $this->jz_top_args;
	}

	/**
	 * @param mixed $jz_top_args
	 */
	public function setJzTopArgs($jz_top_args)
	{
		$this->jz_top_args = $jz_top_args;
		$this->apiParas["jz_top_args"] = $jz_top_args;

	}

	/**
	 * @return mixed
	 */
	public function getLgTpDto()
	{
		return $this->lg_tp_dto;
	}

	/**
	 * @param mixed $lg_tp_dto
	 */
	public function setLgTpDto($lg_tp_dto)
	{
		$this->lg_tp_dto = $lg_tp_dto;
		$this->apiParas["lg_tp_dto"] = $lg_tp_dto;

	}

	/**
	 * @return mixed
	 */
	public function getInsTpDto()
	{
		return $this->ins_tp_dto;
	}

	/**
	 * @param mixed $ins_tp_dto
	 */
	public function setInsTpDto($ins_tp_dto)
	{
		$this->ins_tp_dto = $ins_tp_dto;
		$this->apiParas["ins_tp_dto"] = $ins_tp_dto;

	}

	/**
	 * @return mixed
	 */
	public function getInsReceiverTo()
	{
		return $this->ins_receiver_to;
	}

	/**
	 * @param mixed $ins_receiver_to
	 */
	public function setInsReceiverTo($ins_receiver_to)
	{
		$this->ins_receiver_to = $ins_receiver_to;
		$this->apiParas["ins_receiver_to"] = $ins_receiver_to;

	}



	public function getApiMethodName()
	{
		return "taobao.wlb.order.jz.consign";
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
