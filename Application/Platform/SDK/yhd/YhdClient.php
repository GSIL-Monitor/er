<?php
class YhdClient
{
	//sign签名算法
	private function sign($allParam, $secret) {
		ksort($allParam);
		reset($allParam);
		
		$sign_str = '';
		foreach ( $allParam as $key => $val) {
			$sign_str .= $key.$val;
		}
		$sign_str = $secret . $sign_str . $secret;

		return md5($sign_str);
	}

	public function sendByPost($url, $paramArray, $filePathArray, $secret) {

		$paramArray['sign'] = $this->sign($paramArray, $secret);//签名生成sign

		$boundary   = md5(time());
		
		//设置参数信息
		$postData = $this->addFormData($paramArray, $boundary);

		unset($paramArray['sign']);
		
		//设置文件信息
		$postData .= $this->addFileInfo($filePathArray, $boundary);
		//设置最后的结束边界符
		$postData .="--".$boundary."--\r\n";

		$cl = curl_init($url);
		curl_setopt($cl,CURLOPT_POST,true);
		curl_setopt($cl,CURLOPT_HTTPHEADER, array("Content-Type: multipart/form-data; boundary=".$boundary));
		curl_setopt($cl,CURLOPT_POSTFIELDS, $postData);
		curl_setopt($cl,CURLOPT_RETURNTRANSFER, true);
		$content = curl_exec($cl);
		curl_close($cl);

		$obj = json_decode_safe($content);
		if (null !== $obj)
		{
			foreach ($obj as $propKey => $propValue)
			{
				$obj = $propValue;
			}
		}
		else
		{
			logx("ERROR yhd_response $content", 'error');
		}
		
		return $obj;
	}


	//把参数添加到post请求流里面
	private function addFormData($paramArray, $boundary) {
		$postInfo  = "";

		if($paramArray == null){
			return $postInfo;
		}
		
		foreach ($paramArray as $key => $value) {
			$postInfo .= "--".$boundary."\r\n";
			$postInfo .= "Content-Disposition: form-data; name=\"" . $key . "\"\r\n\r\n";
			$postInfo .= $value . "\r\n";
		}
		
		return $postInfo;
	}


	//把文件添加到post请求流里面
	private function addFileInfo($filePathArray, $boundary) {
		$postFileInfo = "";

		if(count($filePathArray) == 0){
			return $postFileInfo;
		}
		
		foreach ($filePathArray as $filePath) {
			$uploadFile = file_get_contents($filePath);
			$postFileInfo .="--".$boundary."\r\n";
			$postFileInfo .="Content-Disposition: form-data; name=\"". rawurlencode($filePath) ."\"; filename=\"" . rawurlencode(basename($filePath)) . "\"\r\n";
			$postFileInfo .="Content-Type: application/octet-stream\r\n\r\n";
			$postFileInfo .=$uploadFile."\r\n";
		}
		
		return $postFileInfo;
	}


}
?>