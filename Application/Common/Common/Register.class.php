<?php
namespace Common\Common;
/**
 * @author Citying
 *	注册模式  :一个对象创建一次后就不用再创建
 */
class Register{
	protected static $objects;
	
	static public function set($alias, $object)
	{
		self::$objects[$alias] = $object;
	}
	
	static public function get($key)
	{
		if (!isset(self::$objects[$key]))
		{
			return false;
		}
		return self::$objects[$key];
	}
	
	public function _unset($alias)
	{
		unset(self::$objects[$alias]);
	}
}
?>