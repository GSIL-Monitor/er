<?php
namespace Think\Session\Driver;

class Memcache {
	// protected $lifeTime     = 3600;
	protected $sessionName  = '';
	protected $handle       = null;
	protected $path = '';
	/*
	// the ___constuct is called when the sesion is initialized, so we cannot get sid which is not set
	public function __construct($lifeTime){
		if (!empty($lifeTime)){
			$expiration = intval($lifeTime);
		} else {
			$expiration = 30;
		}	

		if ($expiration < 5){
			$expiration = 5;
		} else if ($expiration > 2 * 60){
			$expiration = 2 * 60;
		}
		
		$this->lifeTime = $expiration;
    }
	*/
	/**
     * 打开Session 
     * @access public 
     * @param string $savePath 
     * @param mixed $sessName  
     */
	public function open($savePath, $sessName) {
		// $this->lifeTime     = C('SESSION_EXPIRE') ? C('SESSION_EXPIRE') : $this->lifeTime;
		// $this->sessionName  = $sessName;
		$this->path = '/data/erp/source/Application/Runtime/Logs' . DIRECTORY_SEPARATOR . get_sid() . DIRECTORY_SEPARATOR . MODULE_NAME . DIRECTORY_SEPARATOR . date('y_m_d') . '.log';
        $options            = array(
            'timeout'       => C('SESSION_TIMEOUT') ? C('SESSION_TIMEOUT') : 1,
            'persistent'    => C('SESSION_PERSISTENT') ? C('SESSION_PERSISTENT') : 0
        );
		$this->handle       = new \Memcached;
		
        $hosts              = explode(',', C('MEMCACHE_HOST'));
        $ports              = explode(',', C('MEMCACHE_PORT'));
        foreach ($hosts as $i=>$host) {
            $port           = isset($ports[$i]) ? $ports[$i] : $ports[0];
            //$this->handle->addServer($host, $port, true, 1, $options['timeout']);
			$succ = $this->handle->addServer($host, $port);
			if (!$succ){
				\Think\Log::write("session cache crash, host:" . $host . " ,port:" . $port, \Think\Log::ERR, '', $this->path);
			}
        }
		
		return true;
	}

    /**
     * 关闭Session 
     * @access public 
     */
	public function close() {
		$this->gc(ini_get('session.gc_maxlifetime'));
		//$this->handle->close();
		$this->handle->quit();		
		$this->handle       = null;
		return true;
	}

    /**
     * 读取Session 
     * @access public 
     * @param string $sessID 
     */
	public function read($sessID) {
        return $this->handle->get($this->sessionName.$sessID);
	}

    /**
     * 写入Session 
     * @access public 
     * @param string $sessID 
     * @param String $sessData  
     */
	public function write($sessID, $sessData) {
		//return $this->handle->set($this->sessionName.$sessID, $sessData, 0, $this->lifeTime);
		$sid = session('sid');
		if (empty($sid)) {
			$sid = 'default';
		}
		$this->path = '/data/erp/source/Application/Runtime/Logs' . DIRECTORY_SEPARATOR . $sid . DIRECTORY_SEPARATOR . MODULE_NAME . DIRECTORY_SEPARATOR . date('y_m_d') . '.log';
		try{
			// D function cannot be used because the getcwd() is "/" and we cannot use autoload to get the class, 
			$connection = get_db_connection();
			$db = M('cfg_setting', '', $connection, false);
			$interval = $db->query("SELECT `value` FROM cfg_setting WHERE `key`='cfg_login_interval'");
			// autoCheckFields property cause to write fields to the _fields cache directory, but the directory is not correct for the context
			$db->setProperty('autoCheckFields', true);
		} catch (\Exception $e) {
            \Think\Log::write('get login interval error:'.$e->getMessage(), \Think\Log::ERR, '', $this->path);
        }
		
		if (!empty($interval[0]['value'])){
			$expiration = intval($interval[0]['value']);
		} else {
			$expiration = 30;
		}	

		if ($expiration < 5){
			$expiration = 5;
		} else if ($expiration > 2 * 60){
			$expiration = 2 * 60;
		}
		// \Think\Log::write('write session, session id:'.$sessID.',expiration:'.$expiration,\Think\Log::DEBUG, '', $this->path);
		return $this->handle->set($this->sessionName.$sessID, $sessData, $expiration * 60);
	}

    /**
     * 删除Session 
     * @access public 
     * @param string $sessID 
     */
	public function destroy($sessID) {
		return $this->handle->delete($this->sessionName.$sessID);
	}

    /**
     * Session 垃圾回收
     * @access public 
     * @param string $sessMaxLifeTime 
     */
	public function gc($sessMaxLifeTime) {
		return true;
	}
}
