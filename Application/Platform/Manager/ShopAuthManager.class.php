<?php

namespace Platform\Manager;

use Setting\Model\ShopModel;

class ShopAuthManager {

    static public function authorize($shop_id, $platform_id, $sub_platform_id,$authorize_type='') {
        $secret = md5(time());
        $data['shop_id'] = intval($shop_id);
        $data['sh_secret'] = $secret;
        $ret = D('Setting/Shop')->updateShop($data);
        if ($ret["status"] != 1) {
            E($ret["info"]);
        }
        $wdt_redirect_url = urlencode('http://erp.wangdian.cn/auth2.php');
		$ekb_redirect_url = urlencode('http://ekb.wangdian.cn/auth.php');
        $sid = get_sid();
        $sign = md5($shop_id . $sid . $secret . $platform_id);
        $state = empty($authorize_type)?$sid . '-' . $shop_id . '-' . $platform_id . '-' . $sub_platform_id . '-' . $sign . '-ekb':$sid . '-' . $shop_id . '-' . $platform_id . '-' . $sub_platform_id . '-' . $sign . '-' . $authorize_type .'-ekb';
		$params = json_encode($state);
		$platform_id = intval($platform_id);
        switch (intval($platform_id)) {
            case 1:
			case 2:
                $url = "https://oauth.taobao.com/authorize?response_type=code&client_id=23305776&redirect_uri=";
                break;
			case 3:
                $url = "https://oauth.jd.com/oauth/authorize?response_type=code&client_id=5DF7C7B419EA5A377DAE9BABCDB3030B&redirect_uri=";
                break;
			case 6: //一号店
				$url = "https://member.yhd.com/login/authorize.do?client_id=10210016042000003957&response_type=code&redirect_uri=";
				break;
			case 7://当当网
				$url ="http://oauth.dangdang.com/authorize?appId=2100006529&responseType=code&redirectUrl=";
				break;
			case 9: //阿里巴巴
				$params = array();
				$params['state'] = $state;
				$params['client_id'] = '7630861';
				//$params['response_type'] = 'code';
				//$params['need_refresh_token'] = 'true';
				$params['site'] = 'china';
				$params['redirect_uri'] = "http://ekb.wangdian.cn/auth.php";

				$sign = '';
				ksort($params);
				foreach ($params as $key => $val)
				{
					$sign .= $key . $val;
				}
				//$sign = 'param/1/system.oauth2/startAuth/' . '1004535' . $sign;
				$code_sign = strtoupper(bin2hex(hash_hmac("sha1", $sign, 'fo9rlKXYD0eT', true)));

				/*$url = 'https://gw.open.1688.com/openapi/param/1/system.oauth2/startAuth/' . '1004535' . '?' .
						http_build_query($params) . '&_aop_signature=' . $code_sign;*/
				$url = "https://auth.1688.com/auth/authorize.htm?client_id=7630861&site=china&redirect_uri=$ekb_redirect_url&state=$state&_aop_signature=$code_sign";
				break;
			case 13: //sos
				$url = "http://open.suning.com/api/oauth/authorize?response_type=code&client_id=f4d002e1fda38b9e786b95aa89ef1640&itemcode=4519&redirect_uri=";
				break;
			case 14: //vip
				$url = "https://auth.vip.com/oauth2/authorize?client_id=58fe6cf4&response_type=code&redirect_uri=";
				break;
            case 17: //有赞(kdt)
                $url = "https://open.youzan.com/oauth/authorize?client_id=419021b25a14a5094b&response_type=code&redirect_uri=";
                break;
			case 20: //mls
				if(intval($sub_platform_id) == 1){
					$url = "https://oauth.mogujie.com/authorize?response_type=code&app_key=100377&redirect_uri=";
				}else {
					$url = "https://oauth.meilishuo.com/authorize?response_type=code&app_key=100377&redirect_uri=";
				}
				break;
			case 22: //bbw
				$url = "http://api.open.beibei.com/outer/oauth/app.html?app_id=eiii&redirect_uri=" ;
				break;
            case 24: //zhe800
                $url = "https://openapi.zhe800.com/oauth/code?response_type=code&client_id=NDQwMzk2MzEtNzUw&redirect_uri=";
                break;
            case 28: //weimo
                $url = "https://dopen.weimob.com/fuwu/b/oauth2/authorize?enter=wm&response_type=code&client_id=542091285D59B0CC7489F9E97F94E7ED&scope=default&redirect_uri=";
                break;
			case 31://飞牛网
				$url ="https://oauth.feiniu.com/authorize?response_type=code&client_id=0153368237767445&redirect_uri=";
				break;
            case 32://微店
                $url ="https://oauth.open.weidian.com/oauth2/authorize?response_type=code&appkey=690383&redirect_uri=";
                break;
			case 33: //拼多多
				$url = "http://mms.pinduoduo.com/open.html?response_type=code&client_id=c7ca670b585c4579ab69b50408aa9006&redirect_uri=";
				break;
			case 37://速卖通
				//跟阿里巴巴平台一样，需要构建签名
				$params = array();
				$params['state'] = $state;
				$params['client_id'] = '3364191';
				$params['site'] = 'aliexpress';
				$params['redirect_uri'] = "http://ekb.wangdian.cn/auth.php";

				$sign = '';
				ksort($params);
				foreach ($params as $key => $val)
				{
					$sign .= $key . $val;
				}
				$code_sign = strtoupper(bin2hex(hash_hmac("sha1", $sign, 'LvpGpZNyAU0', true)));

				$url = "http://authhz.alibaba.com/auth/authorize.htm?".http_build_query($params) . "&_aop_signature=" . $code_sign;
				break;
			case 47://人人店
				$url = "http://apis.wxrrd.com/authorize?appid=276dd3cbac353859&response_type=code&redirect_uri=";
				break;
			case 50://网易考拉海购
				$url = "http://oauth.kaola.com/oauth/authorize?response_type=token&client_id=2015c793bf5fffe1bda73ca46396e946&redirect_uri=";
				break;
            default:
                E('不支持的平台类型');
                break;
        }
//美丽说平台的回调地址不允许编码处理。
		if (20 == $platform_id || 33==$platform_id){
			$url .= 'http://ekb.wangdian.cn/auth.php'. "&state=" . $state;
		}else if($platform_id == 22){ //贝贝网自定义参数需要使用prams 并且是json格式
			$url .= $ekb_redirect_url."&params=".$params;
		}else if ($platform_id != 9 && $platform_id!=37) {
			$url .= $ekb_redirect_url . "&state=" . $state;
		}
		if($platform_id ==50) $url='https://oauth.kaola.com/login?redirectURL='.urlencode($url);
        return $url;
    }

	public static function alipayAuthorize($shop_id,$platform_id)
	{
		$secret = md5(time());
		$data['shop_id'] = intval($shop_id);
		$data['sh_secret'] = $secret;
		$ret = D('Setting/Shop')->updateShop($data);
		if ($ret["status"] != 1) {
			E($ret["info"]);
		}
		$ekb_redirect_url = urlencode('http://ekb.wangdian.cn/auth.php');
		$type = 'alipay';
		$sid = get_sid();
		$sign = md5($shop_id . $sid . $secret . $platform_id);
		$state = $sid . '-' . $shop_id . '-' . $platform_id . '-'.'0-' . $sign . '-ekb' . '-' . $type ;
		$url = "https://openauth.alipay.com/oauth2/appToAppAuth.htm?app_id=2017121100553603&redirect_uri=";
		$url .= $ekb_redirect_url."&state=" . $state;
		return $url;

	}

}

?>