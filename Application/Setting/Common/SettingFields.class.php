<?php
namespace Setting\Common;

class SettingFields {
    static public function getSettingFields($name) {
        $name = strtolower($name);
        if (isset(self::$fields[$name])) {
            return self::$fields[$name];
        } else {
            \Think\Log::write('unknown field:' . $name);
            return array();
        }
    }

    static private $fields = array(




        "shop" => array(
            "id" => array("field" => "id", "hidden" => true),
            /*"店铺编号" => array("field" => "shop_no", "title" => "店铺编号", "width" => "80", "sortable" => true),*/
            "店铺名称" => array("field" => "shop_name", "title" => "店铺名称", "width" => "80", "sortable" => true),
            "平台" => array("field" => "platform_id", "title" => "平台", "width" => "80", "formatter" => "formatter.platform_id", "sortable" => true),
            "子平台" => array("field" => "sub_platform_id", "title" => "子平台", "width" => "80", "sortable" => true, "formatter" => "formatterShopSubPlatform"),
            "平台账号" => array("field" => "account_nick", "title" => "平台账号", "width" => "80", "sortable" => true),
            "店铺授权状态" => array("field" => "auth_state", "title" => "店铺授权状态", "width" => "80", "sortable" => true, "formatter" => "formatter.auth_state"),
            "子账号授权状态" => array("field" => "sub_auth_state", "title" => "子账号授权状态", "width" => "80", "sortable" => true, "formatter" => "formatter.auth_state"),
            "支付宝授权状态" => array("field" => "pay_auth_state", "title" => "支付宝授权状态", "width" => "80", "sortable" => true, "formatter" => "formatter.auth_state"),
            "店铺授权时间" => array("field" => "auth_time", "title" => "店铺授权时间", "width" => "80", "sortable" => true),
            "店铺授权到期时间" => array("field" => "expire_time", "title" => "授权到期时间", "width" => "80", "sortable" => true),
            "子账号授权到期时间" => array("field" => "sub_expires_time", "title" => "店铺授权到期时间", "width" => "80", "sortable" => true),
        	"自动合并分组"=>array("field"=>"group_id","title"=>"自动合并分组","width"=>"60","sortable"=>true),
            "停用" => array("field" => "is_disabled", "title" => "停用", "width" => "80", "formatter" => "formatter.boolen", "sortable" => true)
        )
    );

}