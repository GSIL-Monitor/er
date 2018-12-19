<?php
//消息类型
define('TMC_TYPE_CONNECT', 0);
define('TMC_TYPE_CONNECTACK', 1);
define('TMC_TYPE_SEND', 2);
define('TMC_TYPE_SENDACK', 3);

//
define('TMC_HEADER_ENDOFHEADERS', 0);
define('TMC_HEADER_CUSTOM', 1);
define('TMC_HEADER_STATUSCODE', 2);
define('TMC_HEADER_STATUSPHRASE', 3);
define('TMC_HEADER_FLAG', 4);
define('TMC_HEADER_TOKEN', 5);

define('TMC_VALUE_FORMAT_VOID', 0);// act as null
define('TMC_VALUE_FORMAT_COUNTEDSTRING', 1);
define('TMC_VALUE_FORMAT_BYTE', 2);
define('TMC_VALUE_FORMAT_INT16', 3);
define('TMC_VALUE_FORMAT_INT32', 4);
define('TMC_VALUE_FORMAT_INT64', 5);
define('TMC_VALUE_FORMAT_DATE', 6);
define('TMC_VALUE_FORMAT_BYTEARRAY', 7);


define('MESSAGE_KIND_NONE', 0);
define('MESSAGE_KIND_PULLREQUEST', 1);
define('MESSAGE_KIND_CONFIRM', 2);
define('MESSAGE_KIND_DATA', 3);

class TmcMessage
{
	public $protocolVersion = 2;
	public $messageType = 0;
	public $statusCode = 0;
	
	public $statusPhase = '';
	public $flag = 0;
	public $token = '';
	
	public $content = array();
	
	function decode($data)
	{
		$arr = unpack('c2h/st', $data);
		
		$this->protocolVersion = $arr['h1'];
		$this->messageType = $arr['h2'];
		
		$headerType = $arr['t'];
		$pos = 4;
		while($headerType != TMC_HEADER_ENDOFHEADERS)
		{
			switch($headerType)
			{
				case TMC_HEADER_CUSTOM:
				{
					$key = self::readString($data, $pos);
					$value = self::readValue($data, $pos);
					$this->content[$key] = $value;
					break;
				}
				case TMC_HEADER_FLAG:
				{
					$arr = unpack('l', self::read($data, 4, $pos));
					$this->flag = $arr[1];
					break;
				}
				case TMC_HEADER_STATUSCODE:
				{
					$arr = unpack('l', self::read($data, 4, $pos));
					$this->statusCode = $arr[1];
					break;
				}
				case TMC_HEADER_STATUSPHRASE:
				{
					$this->statusPhase = self::readString($data, $pos);
					break;
				}
				case TMC_HEADER_TOKEN:
				{
					$this->token = self::readString($data, $pos);
					break;
				}
			}
			
			$arr = unpack('s', self::read($data, 2, $pos));
			$headerType = $arr[1];
		}
	}
	
	function encode()
	{
		$arr = array();
		$data = pack('c2', $this->protocolVersion, $this->messageType);
		$arr[] = $data;
		
		if($this->statusCode>0)
		{
			$data = pack('sl', TMC_HEADER_STATUSCODE, $this->statusCode);
			$arr[] = $data;
		}
		
		$len = strlen($this->statusPhase);
		if($len>0)
		{
			$data = pack('sla'.$len, TMC_HEADER_STATUSPHRASE, $len, $this->statusPhase);
			$arr[] = $data;
		}
		
		if($this->flag>0)
		{
			$data = pack('sl', TMC_HEADER_FLAG, $this->flag);
			$arr[] = $data;
		}
		
		$len = strlen($this->token);
		if($len>0)
		{
			$data = pack('sla'.$len, TMC_HEADER_TOKEN, $len, $this->token);
			$arr[] = $data;
		}
		
		if(count($this->content) > 0)
		{
			foreach($this->content as $key => $val)
			{
				$len = strlen($key);
				$data = pack('sla'.$len, TMC_HEADER_CUSTOM, $len, $key);
				$arr[] = $data;
				
				if(is_array($val))
				{
					switch($val[0])
					{
						case 'byte':
							$data = pack('cc', TMC_VALUE_FORMAT_BYTE, $val[1]);
							break;
						case 'short':
							$data = pack('cs', TMC_VALUE_FORMAT_INT16, $val[1]);
							break;
						case 'int32':
							$data = pack('cl', TMC_VALUE_FORMAT_INT32, $val[1]);
							break;
						case 'int64':
							$data = pack('ca8', TMC_VALUE_FORMAT_INT64, self::packInt64($val[1]));
							break;
						case 'date':
							$data = pack('cq', TMC_VALUE_FORMAT_DATE, strtotime($val[1])*1000);
							break;
						default:
							$len = strlen($val[1]);
							$data = pack('cla'.$len, TMC_VALUE_FORMAT_COUNTEDSTRING, $len, $val[1]);
							break;
					}
				}
				else if($val === NULL)
				{
					$data = pack('c', TMC_VALUE_FORMAT_VOID);
				}
				else if(is_string($val))
				{
					$len = strlen($val);
					$data = pack('cla'.$len, TMC_VALUE_FORMAT_COUNTEDSTRING, $len, $val);
				}
				else if(is_integer($val))
				{
					$data = pack('cl', TMC_VALUE_FORMAT_INT32, $val);
				}
				else
				{
					echo "invalid data type $val\n";
				}
				
				$arr[] = $data;
			}
			
			$arr[] = pack('s', TMC_HEADER_ENDOFHEADERS);
			
			return implode('', $arr);
		}
	}
	
	static function read(&$data, $len, &$pos)
	{
		$part = substr($data, $pos, $len);
		$pos += $len;
		return $part;
	}
	
	static function readString(&$data, &$pos)
	{
		$arr = unpack('l', self::read($data, 4, $pos));
		$size = $arr[1];
		if($size > 0)
		{
			return self::read($data, $size, $pos);
		}
		
		return "";
	}
	
	static function readValue(&$data, &$pos)
	{
		$arr = unpack('c', self::read($data, 1, $pos));
		switch($arr[1])
		{
			case TMC_VALUE_FORMAT_VOID:
				return NULL;
			case TMC_VALUE_FORMAT_BYTE:
				$arr = unpack('c', self::read($data, 1, $pos));
				return $arr[1];
			case TMC_VALUE_FORMAT_INT16:
				$arr = unpack('s', self::read($data, 2, $pos));
				return $arr[1];
			case TMC_VALUE_FORMAT_INT32:
				$arr = unpack('l', self::read($data, 4, $pos));
				return $arr[1];
			case TMC_VALUE_FORMAT_INT64:
				$str = self::unpackInt64(self::read($data, 8, $pos));
				return $str;
			case TMC_VALUE_FORMAT_DATE:
				$str = self::unpackInt64(self::read($data, 8, $pos));
				return date('Y-m-d H:i:s', self::bctrim(bcdiv($str, 1000)));
			case TMC_VALUE_FORMAT_BYTEARRAY:
				return array(self::readString($data, $pos));
			case TMC_VALUE_FORMAT_COUNTEDSTRING:
			default:
				return self::readString($data, $pos);
		}
	}
	
	static function packInt64($numStr) 
	{
		$l = bcdiv($numStr, '65536');
		$l1 = bcsub($numStr, bcmul($l, '65536'));
		
		$numStr = bcdiv($l, '65536');
		$l2 = bcsub($l, bcmul($numStr, '65536'));
		
		$l4 = bcdiv($numStr, '65536');
		$l3 = bcsub($numStr, bcmul($l4, '65536'));
		
		return pack('S4', $l1, $l2, $l3, $l4); 
	}
	
	static function unpackInt64($bin) 
	{
		$arr = unpack('S4', $bin);
		$i = bcadd($arr[3], bcmul($arr[4], 65536));
		$i = bcadd($arr[2], bcmul($i, 65536));
		$i = bcadd($arr[1], bcmul($i, 65536));
		
		return self::bctrim($i); 
	}
	
	static function bctrim($str)
	{
		$pos = strpos($str, '.');
		if($pos === FALSE)
			return $str;
		
		return substr($str, 0, $pos);
	}
}


class TmcClient
{
	private $ws = NULL;
	private $token = '';
	private $pullRequest = NULL;
	private $timeout = 30;
	
	function __construct($timeout = 30)
	{
		$this->timeout = $timeout;
	}
	
	function connect($appkey, $appsecret, $groupname, $url='ws://mc.api.taobao.com/', $ping=true)
	{
		$this->ws = new WebSocketClient($url, array('timeout'=>$this->timeout));
		//ping 
		if($ping) $this->ws->send('', 'ping');
		
		$msg = new TmcMessage();
		$msg->messageType = TMC_TYPE_CONNECT;
		$msg->flag = 1;
		
		list($usec, $sec) = explode(" ", microtime());
		$msec = $sec . sprintf('%04d', intval($usec*1000));
		
		$msg->content = array(
			'app_key' => $appkey,
			'group_name' => $groupname,
			'timestamp' => $msec
		);
		
		$msg->content['sign'] = self::topSign($msg->content, $appsecret);
		$msg->content['sdk'] = 'top-sdk-java-20141112';
		
		$data = $msg->encode();
		$this->ws->send($data, 'binary');
		
		$msg = $this->receive();
		
		if($msg->messageType == TMC_TYPE_CONNECTACK)
		{
			if(self::checkNoError($msg))
			{
				$this->token = $msg->token;
				
				$msg = new TmcMessage();
				$msg->messageType = TMC_TYPE_SEND;
				$msg->token = $this->token;
				
				$msg->content = array(
					'__kind' => array('byte', MESSAGE_KIND_PULLREQUEST)
				);
				
				$this->pullRequest = $msg->encode();
				return true;
			}
		}
		
		return false;
	}
	
	function receive()
	{
		$response = $this->ws->receive();
		
		$msg = new TmcMessage();
		$msg->decode($response);
		
		return $msg;
	}
	
	function send($topic, $content, $session='')
	{
		$msg = new TmcMessage();
		$msg->messageType = TMC_TYPE_SEND;
		$msg->token = $this->token;
		
		$msg->content = array(
			'__kind' => array('byte', MESSAGE_KIND_DATA),
			'topic' => $topic,
			'content' => $content
		);
		
		if(!empty($session))
		{
			$msg->content['session'] = $session;
		}
		$data = $msg->encode();
		$this->ws->send($data, 'binary');
		
		$msg = $this->receive();
		if($msg->messageType == TMC_TYPE_SENDACK)
		{
			return self::checkNoError($msg);
		}
		
		return false;
	}
	
	function pull()
	{
		$this->ws->send($this->pullRequest, 'binary');
		
		return $this->receive();
	}
	
	function confirm($msgId)
	{
		$msg = new TmcMessage();
		$msg->messageType = TMC_TYPE_SEND;
		$msg->token = $this->token;
		
		$msg->content = array(
			'id' => array('int64', $msgId),
			'__kind' => array('byte', MESSAGE_KIND_CONFIRM)
		);
		
		$data = $msg->encode();
		$this->ws->send($data, 'binary');
	}
	
	static function topSign($params, $secret)
	{
		ksort($params);

		$stringToBeSigned = $secret;
		foreach ($params as $k => $v)
		{
			$stringToBeSigned .= "$k$v";
		}
		unset($k, $v);
		$stringToBeSigned .= $secret;
		
		return strtoupper(md5($stringToBeSigned));
	}

	static function checkNoError(&$msg)
	{
		if($msg->statusCode>0 || strlen($msg->statusPhase)>0)
		{
			logx("tmc_response_error code:{$msg->statusCode} phase:{$msg->statusPhase}");
			return false;
		}
		
		return true;
	}
}

?>