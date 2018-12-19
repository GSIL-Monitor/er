<?php
/**
 * TOP API: taobao.refund.agree request
 * 
 * @since 1.0, 2015-08-24 09:55:45
 */
class RefundAgreenRequest
{
	/** 
	 * 短信验证码，如果退款金额达到一定的数量，后端会返回调用失败，并同时往卖家的手机发送一条短信验证码。
	 * 接下来用收到的短信验证码再次发起API调用即可完成退款操作
	 **/
	private $code;
	
	/** 
	 * 退款信息，格式：refund_id|amount|version|phase,
	 * refund_id为退款编号
	 * amount为退款金额（以分为单位）
	 * version为退款最后更新时间（时间戳格式）
	 * phase为退款阶段（可选值为：onsale, aftersale，天猫退款必值，淘宝退款不需要传）
	 * 多个退款以半角逗号分隔
	 **/
	private $refund_infos;

	private $apiParas = array();

	public function setCode($code)
	{
		$this->code = $code;
		$this->apiParas["code"] = $code;
	}

	public function getCode()
	{
		return $this->code;
	}

	public function setRefundInfos($refund_infos)
	{
		$this->refund_infos = $refund_infos;
		$this->apiParas["refund_infos"] = $refund_infos;
	}

	public function getRefundInfos()
	{
		return $this->refund_infos;
	}

	public function getApiMethodName()
	{
		return "taobao.rp.refunds.agree";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
	
	public function check(){}
	
	public function putOtherTextParam($key, $value)
	{
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}
}
