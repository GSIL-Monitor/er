<?php
namespace Platform\Wms;
use Platform\Wms\Pheanstalk;
require_once(ROOT_DIR . '/Wms/Pheanstalk.php');
class OuterUtils
{
	static public function sendParamsArrayByPost($url,$paramsArray)
	{
		$postdata = http_build_query($paramsArray);
		$length = strlen($postdata);
		$cl = curl_init($url);
		curl_setopt($cl, CURLOPT_POST, true);
		curl_setopt($cl,CURLOPT_HTTP_VERSION,CURL_HTTP_VERSION_1_1);
		curl_setopt($cl,CURLOPT_HTTPHEADER,array("Content-Type: application/x-www-form-urlencoded","Content-length: ".$length));
		curl_setopt($cl,CURLOPT_POSTFIELDS,$postdata);
		curl_setopt($cl,CURLOPT_RETURNTRANSFER,true);
		$content = curl_exec($cl);
		curl_close($cl);

		$content = html_entity_decode($content);
        $info = array(
            "send"      => $paramsArray,
            "receive"   => $content
            );
		return $info;
	}
	static public function sendXmlByPost($url,$xml)
	{
		$length = strlen($xml);
		$cl = curl_init($url);
		curl_setopt($cl, CURLOPT_POST, true);
		curl_setopt($cl,CURLOPT_HTTP_VERSION,CURL_HTTP_VERSION_1_1);
		curl_setopt($cl,CURLOPT_HTTPHEADER,array("Content-Type: application/x-www-form-urlencoded;charset=UTF-8","Content-length: ".$length));
		curl_setopt($cl,CURLOPT_POSTFIELDS,$xml);
		curl_setopt($cl,CURLOPT_RETURNTRANSFER,true);
		$content = curl_exec($cl);
		curl_close($cl);
		$content = html_entity_decode($content);
		$info = array();
		$info['send'] = $xml;
		$info['receive'] = $content;
		return $info;
	}
	static public function getResultByPost($url,$xml)
	{
		$result = self::sendXmlByPost($url,$xml);
		if($result == false)
		{
			return false;
		}
		$resultItemsArray = self::getItemsArrayFromXMLString($result['receive']);
		$result['resultItemsArray'] = $resultItemsArray;
		return $result;
	}
	static public function sendXmlBySoap($wsdl, $method, $params,&$datagram)
	{
		try
		{
			$soap   = @new SoapClient($wsdl,array('trace' => 1,'connection_timeout' => 30));
			$result = $soap->__soapCall($method, array('parameters'=>$params));

		}catch(Exception $e)
		{
			logx("the server failed:".print_r($e->getMessage(), true));
			$datagram['request']  = $params;
			$datagram['response'] = $e->getMessage();
			return false;
		}
		$datagram['request']  = html_entity_decode($soap->__getLastRequest());
		$datagram['response'] = html_entity_decode($soap->__getLastResponse()) ;
		$info = array();
		$info['send']    = $params;
		$info['receive'] = (array)$result;
		return $info;
	}
	static public function getResultBySoap($wsdl, $method, $params,&$datagram)
	{
		$result = self::sendXmlBySoap($wsdl, $method, $params,$datagram);
		if ($result == false)
		{
			return false;
		}
		return $result;
	}
	static public function getSignCode()
	{
		$SignCode = time().mt_rand()%10000;
		$SignCode .= mt_rand()%10000;
		$SignCode .= mt_rand()%10000;
		$SignCode .= mt_rand()%10000;
		return md5($SignCode);
	}
	static public function checkXMLFormat($xmlStr)//验证xml格式是否正确
	{
		$xml_parser = xml_parser_create();
		if(!xml_parse($xml_parser,$xmlStr,true))
		{ 
			xml_parser_free($xml_parser);
			return false; 
		}
		else
		{ 
			return true;
		} 
	}
	static public function getItemsArrayFromXML($xml)
	{
		if(!self::checkXMLFormat($xml))
		{
			return $xml;
		}
		$dom = new DOMDocument();
		$dom->loadXML($xml);
		$root = $dom->documentElement;

		return  self::getNodeArray($root);
		
		
	}
	static public function getItemsArrayFromXMLString($xmlstring)
	{
		if(!self::checkXMLFormat($xmlstring))
		{
			return false;
		}
		$dom = new DOMDocument();
		$dom->loadXML($xmlstring);
		$root = $dom->documentElement;

		return  self::getNodeArray($root->firstChild);
		
		
	}
	static public function isEndNode($node)
	{
		if($node->childNodes->length ==1 && $node->firstChild->nodeType == XML_TEXT_NODE)
		{
			return true;
		}
		else
		{
			return false;	
		}
	}
	static public function getEndNodeValue($node)
	{
		return $node->firstChild->nodeValue;
	}
	static public function hasSameName($node)
	{
		$sameCount = 0;
		$NodeList = $node->parentNode->getElementsByTagName($node->nodeName);
		foreach($NodeList as $n)
		{
			if($n->parentNode->isSameNode($node->parentNode))
			{
				if(++$sameCount>1)
				{
					return true;
				}
			}
		}
		return false;
	}
	static public function getNodeArray($node)
	{
		$array = false;
		$sameCount = 0;
		if($node->hasChildNodes())
		{
			foreach ($node->childNodes as $childNode) 
			{

				if($childNode->hasChildNodes())
				{
					if(self::isEndNode($childNode))
					{
						$array[$childNode->nodeName] = self::getEndNodeValue($childNode);
					}
					else
					{
						if(self::hasSameName($childNode))
						{
							$array[$childNode->nodeName.$sameCount++] = self::getNodeArray($childNode);
						}
						else
						{
							$array[$childNode->nodeName] = self::getNodeArray($childNode);
						}
					}
				}
				else
				{
					if($childNode->nodeName != '#text')
					$array[$childNode->nodeName] = $childNode->nodeValue;
				}
				
			}
		}
		else
		{
			return $node->nodeValue;
		}
		return $array;
	}
	static public function toGBK($str)
	{
		return	iconv('UTF-8', 'GBK', $str);
	}
	static public function GetXMLBodyFromArray($arr,$xml_header='')
	{
		$str = '';
		$xml = self::Array2Xml($arr);
		foreach($xml->children() as $x)
		{
			$str.=$x->saveXML();
		}
		return $xml_header.$str;

	}
    static public function Xml2Array($xml)
    {
		$xml = self::FormatXmlWithoutHeader($xml);
		$xml = self::FormatXmlForBS($xml);
        $result = @simplexml_load_string($xml);
        if($result === false)
            return false;
		$arr = json_decode(json_encode($result),true);
		if(is_array($arr))
		{
			self::formatEmptyArray($arr);
		}
		return $arr;
	}
	static public function formatEmptyArray(&$arr)
	{
		foreach($arr as $key => $value)
		{
			if(is_array($value))
			{
				if(empty($value))
				{
					$arr["$key"] = '';
				}
				else
				{
					self::formatEmptyArray($arr["$key"]);
				}
			}
		}
	}
    static public function Array2Xml($arr,$root='<xml />')
    {
        $xml = simplexml_load_string($root);
        self::Combine($arr,$xml);
        return $xml;
    }
    static public function Combine($arr,&$xml,$name='')
    {
        foreach($arr as $k=>$v)
        {
            if(is_array($v))
            {
                if(is_int($k))
                    $x = $xml->addChild($name);
                else if(array_key_exists(0,$v))
                    $x = $xml;
                else
                    $x = $xml->addChild($k);
                self::Combine($v,$x,$k);
            }
            else
            {
                if(is_int($k))
                    $k = $name;
                $xml->addChild($k,$v);
            }
        }
    }
    static public function GetTelNO($tel)
    {
        $pos = strpos($tel,' ');
        if(!$pos)
            return $tel;
        return substr($tel,0,$pos);
    }
	static public function sendInfoBySoap($wsdl,$funcName,$params)
	{
		$soap = new SoapClient($wsdl);
		$result = @$soap->__soapCall($funcName,array($params));
		if(empty($result))
		{
			return $result = false;
		}
		$info = array();
		$info['send'] = print_r($params,true);
		$info['receive'] = $result;
		return $info;
	}
	static public function FormatXmlWithoutHeader($xml)
	{
		if(!is_string($xml))
			return $xml;
		$xml = self::EscapeXmlString($xml,'<?xml','?'.'>','');
		return $xml;
	}
	static public function FormatXmlForBS($xml)//去除百世返回的变态xml格式
	{
		if(!is_string($xml))
			return $xml;
		$xml = self::EscapeXmlString($xml,'<loms:','>','<loms>');
		$xml = self::EscapeXmlString($xml,'</loms:','>','</loms>');
		return $xml;
	}
	static public function EscapeXmlString($xml,$str_begin,$str_end,$str_replace) //去除xml中个变态格式,把str_begin到str_end中间的(包含两头)的字符串替换成str_replace
	{
		$start_pos = strpos($xml,$str_begin);
		while($start_pos !== false)
		{
			$end_pos = strpos($xml,$str_end,$start_pos);
			$len = $end_pos+strlen($str_end)-$start_pos;
			$xml_header = substr($xml,$start_pos,$len);
			$xml = str_replace($xml_header,$str_replace,$xml);
			$start_pos = strpos($xml,$str_begin);
		}
		return $xml;
	}

	//处理特殊字符
	static public function ReplaceHtmlSpecialCharacter($string)
	{
		$specialchar = array(
			'&'		=>	'&amp;'	
			);

		return strtr($string,$specialchar);
	}


	//过滤掉4字节字符，如emoji表情、特殊的粤语字等
	//对 "'<>& 进行转义以及 去除ASCII 值在 32 值以下的字符
	static public function escape_xml_string($str)
	{
		//先过滤掉4字节字符
		$str = preg_replace('/[\x{10000}-\x{10FFFF}]/u', '', $str);

		return filter_var($str,FILTER_SANITIZE_SPECIAL_CHARS,FILTER_FLAG_STRIP_LOW);
	}

	//向weblog服务器任务队列发送一条插入任务
	static public function weblogSend2($source, $sid, $wms_type, $method, $interface_name, $order_no, $outer_no, $request_url, $request_body, $response_body, $flag, $response_cost=0, $remark='',$pheanstalk_init_dir='')
	{
		$detail = self::formatWeblog($source, $sid, $wms_type, $method, $interface_name, $order_no, $outer_no, $request_url, $request_body, $response_body, $flag, $response_cost, $remark);
		return self::weblogSend($detail,$pheanstalk_init_dir);
	}

	static public function weblogSend(&$detail, $pheanstalk_init_dir='')
	{//require_once('pheanstalk/pheanstalk_init.php')
		if (!empty($pheanstalk_init_dir))
			require_once($pheanstalk_init_dir);

		$pheanstalk = NULL;
		$g_master_tube = 'tmc_weblog';
		$g_tmc_bt_config =
			array("host"=>'10.24.41.23',
			      "port"=>'11301',
			      "connect_timeout"=>1
			);

		try
		{
			$pheanstalk = new Pheanstalk($g_tmc_bt_config['host'], $g_tmc_bt_config['port'], $g_tmc_bt_config['connect_timeout']);
			$pheanstalk->watch($g_master_tube);
//      logx('Pheanstalk Connected!');

			$data = array(
				'created' =>  date('Y-m-d H:i:s'),
				'detail'  =>  $detail);
			$pheanstalk->put(json_encode($data));
		}
		catch(Pheanstalk_Exception $e)
		{
        logx('Pheanstalk START Failed: ' . $e->getMessage());
			return false;
		}
		return true;
	}

	static public function formatWeblog($source, $sid, $wms_type, $method, $interface_name, $order_no, $outer_no, $request_url, $request_body, $response_body, $flag, $response_cost=0, $remark='')
	{
		$detail = array(
			'source'        => $source,
			'sid'           => $sid,
			'wms_type'      => $wms_type,
			'method'        => $method,
			'interface_name'=> $interface_name,
			'order_no'      => $order_no,
			'outer_no'      => $outer_no,
			'request_url'   => $request_url,
			'request_body'  => $request_body,
			'response_body' => $response_body,
			'response_cost' => $response_cost,
			'remark'        => $remark,
			'flag'          => $flag
		);
		return $detail;
	}

}
?>
