<?php
namespace Platform\Common;
use Platform\Manager;
/**
 * Created by PhpStorm.
 * User: yanfeng
 * Date: 2015/12/18
 * Time: 10:23
 */
define("ROOT_DIR", dirname(__DIR__));
define("TOP_SDK_DIR", ROOT_DIR . "/SDK");

require_once(ROOT_DIR . "/Task/global.php");
require_once(ROOT_DIR . "/Common/utils.php");


class ManagerFactory {

    public static function getManager($name = "") {
        if ($name == "") return false;
        try {
            //require_once(APP_PATH . "/Platform/Manager/{$name}Manager.class.php");
            /*require_once(ROOT_DIR . "/Manager/{$name}Manager.class.php");*/
            $name = $name . "Manager";
            /*$model = new $name();*/
            $class = "Platform\\Manager\\{$name}";
            $model = new $class();
            return $model;
        } catch (\Exception $e) {
            \Think\Log::write($e->getMessage());
            return false;
        }
    }

}
