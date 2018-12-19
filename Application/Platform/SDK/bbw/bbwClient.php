<?php
define("BBW_URL","http://d.beibei.com/outer_api/out_gateway/route");
Class bbwClient
{
	private $app_id;
	private $secret_key;
	private $session;
	private $signName;
	public function __construct($app_id,$secret_key,$session)
	{
		$this->app_id =$app_id;
		$this->secret_key =$secret_key;
		$this->session =$session;
	}
	public function getSign($params,$secret_key)
	{
		unset($params['sign']);
		ksort($params);
		$sign = $secret_key;
		foreach($params as $k => $v)
		{
			if('@'!=substr($v,0,1))
			{
				$sign.="$k$v";
			}
		}
		unset($k,$v);
		$sign.=$secret_key;
		$sign=strtoupper(md5($sign));
		return $sign;
	}	
	public function sendByGet($params)
	{
		$params = http_build_query($params);
		$url=BBW_URL.'?'.$params;
		$cl = curl_init($url);
		curl_setopt($cl, CURLOPT_ENCODING, 'UTF-8');
		curl_setopt($cl,CURLOPT_RETURNTRANSFER, true);
		$content = curl_exec($cl);
		curl_close($cl);
		return $content;
	}
	public function getOrder($status,$page_no,$page_size,$time_range,$start_time,$end_time)
	{
		$timestamp=time();
		$params=array(
			'app_id'=>$this->app_id,
			'sign'=>&$this->signName,
			'timestamp'=>$timestamp,
			'method'=>'beibei.outer.trade.order.get',
			'session'=>$this->session,
			'status'=>$status,
			'page_no'=>$page_no,
			'page_size'=>$page_size,
			'time_range'=>$time_range,
			'start_time'=>$start_time,
			'end_time'=>$end_time
			);
		$this->signName=$this->getSign($params,$this->secret_key);
		$content=$this->sendByGet($params);

		return json_decode($content);
	}
	public function getOrderByNo($orderNo)
	{
		$timestamp=time();
		$params=array(
			'app_id'=>$this->app_id,
			'sign'=>&$this->signName,
			'timestamp'=>$timestamp,
			'method'=>'beibei.outer.trade.order.detail.get',
			'session'=>$this->session,
			'oid'=>$orderNo
			);
		$this->signName=$this->getSign($params,$this->secret_key);
		$content=$this->sendByGet($params);
		return json_decode($content);
	}
	public function getGoods($page_no,$page_size)
	{
		$timestamp=time();
		$params=array(
			'app_id'=>$this->app_id,
			'sign'=>&$this->signName,
			'timestamp'=>$timestamp,
			'method'=>'beibei.outer.item.onsale.get',
			'session'=>$this->session,
			'page_no'=>$page_no,
			'page_size'=>$page_size
			);
		$this->signName=$this->getSign($params,$this->secret_key);
		$content=$this->sendByGet($params);
		return json_decode($content);
	}
	public function get_logistics_companies()
	{
		$timestamp=time();
		$params=array(
			'app_id'=>$this->app_id,
			'sign'=>&$this->signName,
			'timestamp'=>$timestamp,
			'method'=>'beibei.outer.trade.logistics.get',
			'session'=>$this->session,
			);
		$this->signName=$this->getSign($params,$this->secret_key);
		$content=$this->sendByGet($params);
		return json_decode($content);
	}
	public function logistics($oid,$company,$outer_sid)
	{
		$timestamp=time();
		$params=array(
			'app_id'=>$this->app_id,
			'sign'=>&$this->signName,
			'timestamp'=>$timestamp,
			'method'=>'beibei.outer.trade.logistics.ship',
			'session'=>$this->session,
			'oid'=>$oid,
			'company'=>$company,
			'out_sid'=>$outer_sid
			);
		$this->signName=$this->getSign($params,$this->secret_key);
		$content=$this->sendByGet($params);
		return json_decode($content);	
	}
	public function stock_sync($iid,$outer_id,$qty)
	{	
		$timestamp=time();
		$params=array(
			'app_id'=>$this->app_id,
			'sign'=>&$this->signName,
			'timestamp'=>$timestamp,
			'method'=>'beibei.outer.item.qty.update',
			'session'=>$this->session,
			'iid'=>$iid,
			'outer_id'=>$outer_id,
			'qty'=>$qty
			);
		$this->signName=$this->getSign($params,$this->secret_key);
		$content=$this->sendByGet($params);
		return json_decode($content);
	}
}
?>