<?php
namespace Platform\Manager;
/**
 * Created by PhpStorm.
 * User: yanfeng
 * Date: 2015/12/18
 * Time: 15:52
 */
class Manager {
    /**
     * @param array $shop
     */
    public static function parse_app_key(&$shop) {
        $app_key = json_decode(@$shop['app_key'], true);
        foreach ($app_key as $key => $val) {
            $shop[$key] = $val;
        }
        unset($shop['app_key']);
        $shop = (object)$shop;
    }

    /**
     * @param integer $shopId
     * @param array   $shop
     * @param array   $message
     * @return bool
     */
    public static function sync_auth_check($shopId, &$shop, &$message) {
        $authorization_failure = '该店铺未授权或授权已失效';
        $shop = M('cfg_shop')->alias('csp')->field('csp.shop_id,csp.shop_name,csp.platform_id,csp.sub_platform_id,csp.auth_state,csp.app_key,csp.refresh_token,csp.account_nick,csp.push_rds_id,csp.auth_time,csp.account_id')->where(array('csp.shop_id' => array('eq', intval($shopId))))->find();
        if ($shop['auth_state'] != 1) {
            $message['status'] = 0;
            $message['info']   = $authorization_failure;
            return $message;
        }
        self::parse_app_key($shop);
        $shop->sid   = 0;//用户标识
        $platform_id = intval($shop->platform_id);
        switch ($platform_id) {
            case 1: //淘宝
            case 2: //淘宝分销
            case 3: //京东
            case 5: //亚马逊
            case 6: //一号店
            case 7: //当当
            case 9: //阿里巴巴
            case 13://苏宁
            case 14://唯品会
            case 17:  //kdt
            case 22:  //贝贝网
            case 24:  //zhe800
            case 25:  //融e购            
            case 28:  //微盟
            case 29://卷皮网
            case 31://飞牛网
            case 32://微店
            case 37://速卖通
            case 47://人人店
            case 50://考拉
            {
                //判断授权状态
                if (empty($shop->session)) {
                    $message["status"] = 0;
                    $message["info"]   = $authorization_failure;
                    return $message;
                }
                break;
            }
           /* case 3: {
                //判断授权状态
                if (empty($shop->session)) {
                    $message['status'] = 0;
                    $message['info']   = '该店铺未授权或授权已失效,session值为空';
                    return false;
                }
                break;
            }*/
            case 8: //国美
            {
                if (empty($shop->secret)) {
                    $message['status'] = 0;
                    $message{'info'}   = $authorization_failure;
                    return $message;
                }
                break;
            }  
            case 17:  //kdt
            case 20:  //mls
            case 27:  //楚楚街
            case 34://蜜芽宝贝
            case 33:  //pdd
            case 36: //建行善融商城
            case 53: //楚楚街拼团
            case 56: //小红书
			{
				if(empty($secret['key']) || empty($secret['secret'])){
					return false;
				}
				break;
			}
            case 60://返利网
            {
                if(empty($secret['key']) || empty($secret['secret']))
                    return false;
                break;
            }
            default: {
                $message['status'] = 0;
                $message['info']   = '店铺所在的平台未找到';
                return $message;
            }
        }
        $message['status']=0;
        $message['info'] = '';
        return $message;
    }

}