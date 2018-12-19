<?php

class TopClient {
    public $appkey;

    public $secretKey;

    //public $gatewayUrl = "http://gw.api.taobao.com/router/rest";
    public $gatewayUrl = "http://gw.api.tbsandbox.com/router/rest";

    public $format = "xml";

    /** 是否打开入参check**/
    public $checkRequest = true;

    protected $signMethod = "md5";

    protected $apiVersion = "2.0";

    protected $sdkVersion = "top-sdk-php-20130106";

    protected function generateSign($params) {
        ksort($params);

        $stringToBeSigned = $this->secretKey;
        foreach ($params as $k => $v) {
            if ("@" != substr($v, 0, 1)) {
                $stringToBeSigned .= "$k$v";
            }
        }
        unset($k, $v);
        $stringToBeSigned .= $this->secretKey;

        return strtoupper(md5($stringToBeSigned));
    }

    public function curl($url, $postFields = null) {
        global $g_top_gate_url, $g_gate_key, $g_jst_hch_enable;

        $crypt = false;
        if (isset($g_top_gate_url) && isset($g_gate_key) && !empty($g_top_gate_url) && !empty($g_gate_key))
            $crypt = true;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FAILONERROR, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //https 请求
        if (strlen($url) > 5 && strtolower(substr($url, 0, 5)) == "https") {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }

        $postBodyString = "";
        if (is_array($postFields) && 0 < count($postFields)) {
            foreach ($postFields as $k => $v) {
                $postBodyString .= "$k=" . urlencode($v) . "&";
            }
            unset($k, $v);
            curl_setopt($ch, CURLOPT_POST, true);

            $postBodyString = substr($postBodyString, 0, -1);

            if ($crypt) $postBodyString = base64_encode(rc4($g_gate_key, $postBodyString));

            curl_setopt($ch, CURLOPT_POSTFIELDS, $postBodyString);
        }
		/*
        if ($g_jst_hch_enable && !$crypt) {
            $len = strlen($this->gatewayUrl);
            if (substr($url, 0, $len) == $this->gatewayUrl) {
                $params = array('url' => substr($url, $len) . '?' . $postBodyString);
                hchRequest('http://gw.ose.aliyun.com/event/top', $params);
            }
        }
		*/
        $reponse = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new Exception(curl_error($ch), 0);
        } else {
            $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if (200 !== $httpStatusCode) {
                throw new Exception($reponse, $httpStatusCode);
            }
        }
        curl_close($ch);

        if ($crypt) $reponse = rc4($g_gate_key, base64_decode($reponse));

        return $reponse;
    }

    protected function logCommunicationError($apiName, $requestUrl, $errorCode, $responseTxt) {
        $localIp = isset($_SERVER["SERVER_ADDR"]) ? $_SERVER["SERVER_ADDR"] : "CLI";
        /*$logger = new LtLogger;
        $logger->conf["log_file"] = rtrim(TOP_SDK_WORK_DIR, '\\/') . '/' . "top_comm_err_" . $this->appkey . "_" . date("Y-m-d") . ".log";
        $logger->conf["separator"] = "^_^";*/
        $logData = array(
            date("Y-m-d H:i:s"),
            $apiName,
            $this->appkey,
            $localIp,
            PHP_OS,
            $this->sdkVersion,
            $requestUrl,
            $errorCode,
            str_replace("\n", "", $responseTxt)
        );
        logx($logData);
    }

    public function execute($request, $session = null) {
        global $g_top_gate_url, $g_gate_key;

        /*$g_top_gate_url = 'http://121.199.38.85/api/api.php';
        $g_gate_key = 'wdt2212LKkd';*/

        if ($this->checkRequest) {
            try {
                $request->check();
            } catch (Exception $e) {
                @$result->code = $e->getCode();
                @$result->msg = $e->getMessage();
                return $result;
            }
        }
        //组装系统参数
        $sysParams["app_key"] = $this->appkey;
        $sysParams["v"] = $this->apiVersion;
        $sysParams["format"] = $this->format;
        $sysParams["sign_method"] = $this->signMethod;
        $sysParams["method"] = $request->getApiMethodName();
        $sysParams["timestamp"] = date("Y-m-d H:i:s");
        $sysParams["partner_id"] = $this->sdkVersion;
        if (null != $session) {
            $sysParams["session"] = $session;
        }

        //获取业务参数
        $apiParams = $request->getApiParas();

        //签名
        $sysParams["sign"] = $this->generateSign(array_merge($apiParams, $sysParams));

        //系统参数放入GET请求串
        $requestUrl = '';
        foreach ($sysParams as $sysParamKey => $sysParamValue) {
            $requestUrl .= "$sysParamKey=" . urlencode($sysParamValue) . "&";
        }
        $requestUrl = substr($requestUrl, 0, -1);

        if (isset($g_top_gate_url) && !empty($g_top_gate_url)) {
            if (isset($g_gate_key) && !empty($g_gate_key)) $requestUrl = base64_encode(rc4($g_gate_key, $requestUrl));
            $requestUrl = $g_top_gate_url . "?" . $requestUrl;
        } else
            $requestUrl = $this->gatewayUrl . "?" . $requestUrl;


        //发起HTTP请求
        try {
            $resp = $this->curl($requestUrl, $apiParams);
        } catch (Exception $e) {
            logx($sysParams["method"], $requestUrl, "HTTP_ERROR_" . $e->getCode() . " ".$e->getMessage());
            $result->code = -1;
            $result->msg = $e->getMessage();
            return $result;
        }

        //解析TOP返回结果
        $respWellFormed = false;
        if ("json" == $this->format) {
            $respObject = $respObject = json_decode($resp, false, 512, JSON_BIGINT_AS_STRING);
            if (null !== $respObject) {
                $respWellFormed = true;
                foreach ($respObject as $propKey => $propValue) {
                    if ($propKey == 'sign') continue; //alipay
                    $respObject = $propValue;
                }
            }
        } else if ("xml" == $this->format) {
            $respObject = @simplexml_load_string($resp);
            if (false !== $respObject) {
                $respWellFormed = true;
            }
        }

        //返回的HTTP文本不是标准JSON或者XML，记下错误日志
        if (false === $respWellFormed) {
            $this->logCommunicationError($sysParams["method"], $requestUrl, "HTTP_RESPONSE_NOT_WELL_FORMED", $resp);
            $result->code = -1;
            $result->msg = "HTTP_RESPONSE_NOT_WELL_FORMED";
            return $result;
        }

        //如果TOP返回了错误码，记录到业务错误日志中
        if (isset($respObject->code)) {
           /* $logger = new LtLogger;
            $logger->conf["log_file"] = rtrim(TOP_SDK_WORK_DIR, '\\/') . '/' . "logs/top_biz_err_" . $this->appkey . "_" . date("Y-m-d") . ".log";*/
            logx(array(
                             date("Y-m-d H:i:s"),
                             $resp
                         ));
        }
        return $respObject;
    }

    public function exec($paramsArray) {
        if (!isset($paramsArray["method"])) {
            trigger_error("No api name passed");
        }
        $inflector = new LtInflector;
        $inflector->conf["separator"] = ".";
        $requestClassName = ucfirst($inflector->camelize(substr($paramsArray["method"], 7))) . "Request";
        if (!class_exists($requestClassName)) {
            trigger_error("No such api: " . $paramsArray["method"]);
        }

        $session = isset($paramsArray["session"]) ? $paramsArray["session"] : null;

        $req = new $requestClassName;
        foreach ($paramsArray as $paraKey => $paraValue) {
            $inflector->conf["separator"] = "_";
            $setterMethodName = $inflector->camelize($paraKey);
            $inflector->conf["separator"] = ".";
            $setterMethodName = "set" . $inflector->camelize($setterMethodName);
            if (method_exists($req, $setterMethodName)) {
                $req->$setterMethodName($paraValue);
            }
        }
        return $this->execute($req, $session);
    }
}