<?php
/**
 * @author Citying
 * 
 */
define("ICBC_REFUND_NO","-");
define("ICBC_REFUND_APPLIED","已申请退款");
define("ICBC_REFUND_PROCESSING","退款处理中");
define("ICBC_REFUND_CLOSE","退款完成");
class icbcApiClient
{
	private $req_sid;
	private $method;
	private $version='1.0';
	private $format='xml';
	private $timestamp;
	private $app_key;
	private $app_secret;
	private $auth_code;
	private $sign;
 	private $base_url='https://ops.mall.icbc.com.cn//icbcrouter';
	//private $base_url='https://218.205.193.39//icbcrouter'; //数据测试服务器
	
	/**
	 * 构造方法
	 */
	public function __construct()
	{
		
	}
	
	
	/**
	 * @return the $format
	 */
	public function getFormat()
	{
		return $this->format;
	}

	/**
	 * @param field_type $req_sid
	 */
	public function setReq_sid($req_sid)
	{
		$this->req_sid = $req_sid;
	}

	/**
	 * @param field_type $method
	 */
	public function setMethod($method)
	{
		$this->method = $method;
	}

	/**
	 * @param field_type $app_key
	 */
	public function setApp_key($app_key)
	{
		$this->app_key = $app_key;
	}

	/**
	 * @param field_type $app_secret
	 */
	public function setApp_secret($app_secret)
	{
		$this->app_secret = $app_secret;
	}

	/**
	 * @param field_type $auth_code
	 */
	public function setAuth_code($auth_code)
	{
		$this->auth_code = $auth_code;
	}
    
	/**
	 * @param string $base_url
	 */
	public function setBase_url($base_url)
	{
		$this->base_url = $base_url;
	}

	/**
	 * @param array $params req_data
	 * @param array $paramArray url_data
	 */
	public function sendByPost($params)
	{
		$paramArray=array();
		$url_data=self::bluidUrl($params, $paramArray);
		$cl=curl_init($url_data);
//		curl_setopt ( $cl, CURLOPT_POST, true );
		curl_setopt($cl, CURLOPT_CUSTOMREQUEST, 'POST');

// 		curl_setopt ( $cl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1 );
// 		curl_setopt ( $cl, CURLOPT_HTTPHEADER, array( "Content-Type: text/plain", "Content-length: " . $length ) );
// 		curl_setopt ( $cl, CURLOPT_POSTFIELDS, $url_data);
		curl_setopt ( $cl, CURLOPT_HEADER, FALSE );
		curl_setopt($cl, CURLOPT_PORT,448);
		
// 		curl_setopt($cl, CURLOPT_SSL_VERIFYHOST, 2);
// 		curl_setopt($cl, CURLOPT_SSL_VERIFYPEER, 1);
// 		curl_setopt($cl, CURLOPT_SSLCERT, WECHAT_CERT);
// 		curl_setopt($cl, CURLOPT_SSLCERTTYPE, 'PEM');
// 		curl_setopt($cl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
		curl_setopt($cl, CURLOPT_SSL_VERIFYPEER, FALSE);
		
		curl_setopt ( $cl, CURLOPT_RETURNTRANSFER, true );
		
		$content = curl_exec ( $cl );
		
		if(!$content)
		{
			return "The icbc response's content is null,and the reason is-->".curl_error($cl);

		}
		curl_close ( $cl );
		$response=self::xml2arr($content);
		if(!array_key_exists('response',$response)){
			logx('icbc error response: '.print_r($response,true));
			return ;
		}
        return $response['response'];
	}
	
	/**
	 * 获取毫秒时间
	 * @return number
	 */
	function microtime_float()
	{
		list($usec, $sec) = explode(" ", microtime());
		return ((float)$usec + (float)$sec);
	}
	
	/**
	 * 格式化毫秒级别的时间
	 * @param string $tag
	 * @param time $time
	 * @return mixed
	 */
	function microtime_format($tag, $time)
	{
		list($usec, $sec) = explode(".", $time);
		$date = date($tag,$usec);
		return str_replace('x', $sec, $date);
	}
	
	function getReq_sid()
	{
		$req_sid=self::microtime_format("YmdHisx", self::microtime_float());
		$req_sid=substr($req_sid, 2).mt_rand(10, 99);
		return $req_sid;
	}
	
	/**
	 * 拼接url
	 * @param array $params
	 */
	public function bluidUrl($params, $paramArray)
	{
		$req_data=self::array2xml($params);
		$paramArray['sign']=self::getSign($req_data);
		$paramArray['timestamp']=self::microtime_format("Y-m-d H:i:s.x", self::microtime_float());
		$paramArray['version']=$this->version;
		$paramArray['app_key']=$this->app_key;
		$paramArray['method']=$this->method;
		$paramArray['format']=$this->format;
		$paramArray['req_sid']=self::getReq_sid();
		$paramArray['auth_code']=$this->auth_code;
		$paramArray['req_data']=$req_data;
		$url=$this->base_url."?".http_build_query($paramArray);
		
		return $url;
	}
	

	/**
	 * 获得签证sign
	 * @param string $req_data
	 * @return string $sign
	 */
	public function getSign($req_data)
	{
		if (empty($this->app_key)||empty($this->app_secret)||empty($this->auth_code))
		{
			echo "some of them (app_key,app_secret,auth_code) is null!!";
			return;
		}
		$sign='';
		$sign_data="app_key=".$this->app_key."&auth_code=".$this->auth_code."&req_data=".$req_data;

		iconv("ASCII", "UTF-8", $sign_data);
		iconv("ASCII", "UTF-8", $this->app_secret);
		
		try {
			$sign=base64_encode(hash_hmac("sha256", $sign_data, $this->app_secret,true));
		} catch (Exception $e) {
			throw new Exception ();
		}
		return $sign;
	}
	
	
	/**
	 * array => xml
	 * @param array $params
	 * @param number $level
	 * @return string
	 */
	public function array2xml($params,$level=0)
	{
		$xml=$attr="";
		if($level==0)
		{
			$xml="<?xml version='1.0' encoding='UTF-8'?><body>";
		}
		foreach ($params as $key => $val)
		{
			if(is_numeric($key))
			{
				$attr=" id='{$key}'";
				$key="item";
			}
			$xml.="<{$key}{$attr}>";
			$xml.=is_array($val)?self::array2xml($val,$level+1):$val;
			$xml.="</{$key}>";
		}
		
		if($level==0)
		{
			$xml.="</body>";
		}
		
		return $xml;
	}
	
	/**
	 * xml => array
	 * @param unknown $xmlstring
	 * @return boolean|Ambigous <void, multitype:string void >
	 */
	public function xml2array($xmlstring)
	{
		if (substr ( $xmlstring, 0, 2 ) != '<?')
			$xmlstring = '<?xml version="1.0" encoding="utf-8"?>' . $xmlstring;
		$dom = simplexml_load_string ( $xmlstring );
		$xmlarray = $this->getXmlArray( $dom );
		if (! is_array ( $xmlarray ))
			return false;
		return $xmlarray;
	}


	public function xml2arr($xml)
	{
		$reg = "/<(\\w+)[^>]*?>([\\x00-\\xFF]*?)<\\/\\1>/";
		if(preg_match_all($reg, $xml, $matches))
		{
			$count = count($matches[0]);
			$arr = array();
			for($i = 0; $i < $count; $i++)
			{
				$key= $matches[1][$i];
				$val = $this->xml2arr( $matches[2][$i] );   //递归
				if(array_key_exists($key, $arr))
				{
					if(is_array($arr[$key]))
					{
						if(!array_key_exists(0,$arr[$key]))
						{
							$arr[$key] = array($arr[$key]);
						}
					}
					else
					{
						$arr[$key] = array($arr[$key]);
					}
					$arr[$key][] = $val;
				}
				else
				{
					$arr[$key] = $val;
				}
			}
			return $arr;
		}
		else
		{
			return $xml;
		}
	}
	
	/**
	 * 节点读取数据放入对应的数组
	 * @param unknown $node
	 * @return void|Ambigous <multitype:string void , void>
	 */
/* 	public function getNodeArray($node)
	{
		$count = 0;
		$array = array();
		if (! $node->children ())
		{
			return;
		}
		foreach ( $node->children () as $child )
		{
			if ($child->children ())
			{
				if (isset ( $array [$child->getName ()] ))
				{
					print_r("debug01======>".$child->getName ()."\n");
					$array [$child->getName ()] [$count] = $this->getNodeArray ( $child );
					$count = $count + 1;
				} else
				{
					print_r("debug02=======>".$child->getName ()."\n");
					$array [$child->getName ()] = $this->getNodeArray ( $child );
				}
				
			} else
			{
				if (isset ( $array [$child->getName ()] ))
				{
					print_r("debug03=======>".$child->getName ()."\n");
					$array [$child->getName ()] [$count] = ( string ) $child;
					$count = $count + 1;
				} else
				{
					print_r("debug04========>".$child->getName ()."\n");
					$array [$child->getName ()] = ( string ) $child;
				}
			}
		}
		return $array;
	} */
	
	/**
	 * @param SimpleXMLElement $xml
	 * @param string $root
	 * @return string|multitype:Ambigous <string, multitype:multitype: Ambigous <string, multitype:unknown , multitype:multitype: Ambigous <string, multitype:unknown > > > |Ambigous <string, multitype:multitype: Ambigous <string, multitype:unknown , multitype:multitype: Ambigous <string, multitype:unknown > > >
	 */
	function getXmlArray($xml, $root = true)
	{
		if (! $xml->children ())
		{
			return ( string ) $xml;
		}
		$array = array();
		foreach ( $xml->children () as $element => $node )
		{
			$totalElement = count ( $xml->{$element} );
			if (! isset ( $array [$element] ))
			{
				$array [$element] = "";
			}
			$attributes = $node->attributes ();
			if ($attributes)//节点node含有属性值attribute
			{
				$data = array(
						'attributes' => array(),
						'value' => (count ( $node ) > 0) ? $this->getXmlArray ( $node, false ) : ( string ) $node 
				);
				foreach ( $attributes as $attr => $value )
				{
					$data ['attributes'] [$attr] = ( string ) $value;
				}
				if ($totalElement > 1)
				{
					$array [$element] [] = $data;
				} else
				{
					$array [$element] = $data;
				}
			} else
			{
				if ($totalElement > 1)
				{
					$array [$element] [] = $this->getXmlArray ( $node, false );
				} else
				{
					$array [$element] = $this->getXmlArray ( $node, false );
				}
			}
		}
		if ($root)
		{
			return array( $xml->getName () => $array );
		} else
		{
			return $array;
		}
	}
	
}

?>