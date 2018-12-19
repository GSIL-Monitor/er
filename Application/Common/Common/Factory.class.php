<?php
namespace Common\Common;
use Think\Model;
/**
 * @author Citying
 *	工厂模式:获取对象
 */
class Factory{
	
	/**
	 * 获取自定义模型Model对象,未找到则实例化Model类;此方法是对D方法的改进(D方法不支持connection)
	 * @param string $name 资源地址
	 * @param string $layer 模型层名称
	 * @param string $tablePrefix 表前缀
	 * @param string $connection 数据库连接信息
	 * @return Model 
	 */
	static public function getModel($name='',$layer='',$tablePrefix='',$connection='')
	{
		$layer = $layer? : C('DEFAULT_M_LAYER');
		$key = $name.$layer;
		$model = Register::get($key);
		if (!$model) {
			$connection=empty($connection)?get_db_connection():$connection;
			$class = parse_res_name($name,$layer);
			if(class_exists($class)) {
				$class='\\'.$class;
				$model=new $class(basename($name),$tablePrefix,$connection);
			}else{
				$path = get_log_path();
				\Think\Log::write('Factory->getModel时没找到模型类'.$class,\Think\Log::WARN, '', $path);//
				$model = new \Think\Model(basename($name),$tablePrefix,$connection);
			}
			Register::set($key, $model);
		}
	    return $model;
	}
}
?>