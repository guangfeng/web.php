<?php

/**
* Web::Session
*/
class Session implements arrayaccess
{
	static private $seInstance  = null;
	protected $_debugMessages  	= array();
	protected $_debug           = false;


	function __construct($useCookie = true,$debug = false)
	{
		if($debug) {
			$this->_debug = $debug;
			$this->debugMsg("WebSession Debug Mode : Enable");
		}

		if($useCookie === false)
			ini_set('session.use_cookies',0);
		else {
			ini_set('session.use_cookies',1);
			ini_set('session.cookie_path','/');
		}

		ini_set('session.cookie_domain', strstr(HOSTNAME, '.'));

		$key = ini_get('session.name');
		if( isset( $_GET[$key] )) {
			if( preg_match( '/^[0-9A-Za-z]+$/', $_GET[$key] ) ) {
				session_id( $_GET[$key] );
			}
			if(empty($_GET[$key])) {
				unset($_GET[$key]);
			}
		}

		session_start();
	}

	public static function &Instance($useCookie = true,$debug = false) {
		if (self::$seInstance == null)
			self::$seInstance = new Session($useCookie, $debug);
		return self::$seInstance;
	}

	public function debugMsg($msg) {
		$this->_debugMessages[] = $msg;
	}

	public function offsetSet($offset, $value) {
		$_SESSION[$offset] = $value;
 	}

	public function offsetExists($offset) {
		return isset($_SESSION[$offset]);
	}

	public function offsetUnset($offset) {
		unset($_SESSION[$offset]);
	}

	public function offsetGet($offset) {
		return isset($_SESSION[$offset])?$_SESSION[$offset]:null;
	}

	public static function refresh() {
		session_regenerate_id($deleteOld = true);
	}

	public static function destory() {
		if(isset($_COOKIE[session_name()]))
			setcookie(session_name(), '', time()-42000, '/', ini_get('session.cookie_domain'));
		session_destroy();
		$_SESSION 	= array();
	}
}