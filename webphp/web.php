<?php 

include_once 'exceptions/RequestErrorException.php';
include_once 'lib/input/Inspekt.php';

/**
* Webphp main class
*/

class Web
{   
    protected $_baseURLPath     = '/';
    protected $_urls            = null;
    protected $_requestUrl      = null;
    public    $params           = null;
    protected $_debug           = false;
    protected $_debugMessages   = array();
    protected $_renderedText    = null;
    protected $_tpl             = null;
    static protected $_plugins  = array();
    static protected $_instance = NULL;
    
    public function __construct($baseURLPath = null)
    {                                
        if (!is_null($baseURLPath)) {   
            $this->_baseURLPath = $baseURLPath;
        }                    
    } 
    
    /**
     * call a registered plugin
     * it uses call_user_func_array which can be slow
     * over many iterations 
     *
     * @param string $method 
     * @param string $args 
     * @return void
     * @author Guang Feng
     */
    
    private function __call($method, $args)
    {
        if (in_array($method, self::$_plugins)) {
            array_unshift($args, $this);
            return call_user_func_array($method, $args);
        }
    }   
    
    /**
     * register a plugin to call via __call
     * first argument passed to function is an
     * instance of web.
     *
     * @param string $func 
     * @return void
     * @author Guang Feng
     */
    
    public static function registerPlugin($func)
    {
        self::$_plugins[] = $func;
    }
                    
    /**
     * return _baseURLPath
     * @param string baseURLPath
     * @return string _baseURLPath
     * @author Guang Feng
     */

    function baseURL($baseURLPath=null)
    {                                 
        if (!is_null($baseURLPath)) {
            $this->_baseURLPath = $baseURLPath;
        }
        return $this->_baseURLPath;
    }                              
    
    /**
     * turn on/off debugging. output is printed to screen
     *
     * @param string $onoff 
     * @return void
     * @author Guang Feng
     */
    
    public function debug($onoff = null)    
    {
        if (!is_null($onoff)) {
            $this->_debug = $onoff;
        } else {
            return $this->_debug;
        }        
    }
    
    /**
     * save debug messages to var for displaying later.
     *
     * @param string $msg 
     * @return void
     * @author Guang Feng
     */
    
    public function debugMsg($msg)
    {
        $this->_debugMessages[] = $msg;
    }
    
    /**
     * display debug messages
     *
     * @return void
     * @author Guang Feng
     */
    
    public function debugDisplay()
    {
        if (!$this->_debugMessages 
            || !is_array($this->_debugMessages)) {
            return;
        }          
        
        echo "<h2>Debug Messages</h2>\n<ol>";
        foreach ($this->_debugMessages as $msg) {
            printf("<li>%s</li>", $msg);
        }                                  
        echo "</ol>";
    }
        
    
    /**
     * create instance of Web object. Only use this method
     * to get an instance of Web.
     *
     * @author Guang Feng
     */
    
    public static function &instance()
    {
        if (self::$_instance == NULL) {
            self::$_instance = new Web();
        }
        return self::$_instance; 
    }
    
    /**
     * don't allow cloning of the web instance
     *
     * @return void
     * @author Guang Feng
     */ 
    public final function __clone()
    {
        trigger_error("You can not clone an instance of the web class", E_USER_ERROR);
    }
    
    /**
     * requestUri
     *           
     * inspects $_SERVER['REQUEST_URI'] and returns a sanitized 
     * path without a leading/trailing slashes
     * @return void
     * @author Guang Feng
     */
    
    private function requestUri()
    {
        // have seen apache set either or.
        $uri = isset($_SERVER['REDIRECT_URL']) ? $_SERVER['REDIRECT_URL'] 
                                               : $_SERVER['REQUEST_URI'];
        
        // sanitize it
        $uri = filter_var($uri, FILTER_SANITIZE_URL);
        
        // display URL
        $this->debugMsg('URI is: '.htmlspecialchars($uri));
        
        // kill query string off REQUEST_URI
        if ( strpos($uri,'?') !== false ) {
               $uri = substr($uri,0,strpos($uri,'?'));
        }                                      
        
        // ok knock off the _baseURLPath
        if (strlen($this->_baseURLPath) > 1) {
            $this->debugMsg("baseURLPath is: {$this->_baseURLPath}");
            $uri = str_replace($this->_baseURLPath, '', $uri);        
        }                              
        
        
        $this->request_uri = $uri;        
    }
    
    /**
     * send 303 by default so browser won't cache the 200 or 301 headers
     *
     * @param string $location 
     * @param string $status 
     * @return void
     * @author Guang Feng
     */
    
    public static function redirect($location, $status=303)
    {   
        self::httpHeader($status);
        header("Location: $location");
    }
    
    /**
     * send header code to browser
     *
     * @param string $code 
     * @return void
     * @author Guang Feng
     */
    
    public static function httpHeader($code)
    {
        $http = array (
               100 => "HTTP/1.1 100 Continue",
               101 => "HTTP/1.1 101 Switching Protocols",
               200 => "HTTP/1.1 200 OK",
               201 => "HTTP/1.1 201 Created",
               202 => "HTTP/1.1 202 Accepted",
               203 => "HTTP/1.1 203 Non-Authoritative Information",
               204 => "HTTP/1.1 204 No Content",
               205 => "HTTP/1.1 205 Reset Content",
               206 => "HTTP/1.1 206 Partial Content",
               300 => "HTTP/1.1 300 Multiple Choices",
               301 => "HTTP/1.1 301 Moved Permanently",
               302 => "HTTP/1.1 302 Found",
               303 => "HTTP/1.1 303 See Other",
               304 => "HTTP/1.1 304 Not Modified",
               305 => "HTTP/1.1 305 Use Proxy",
               307 => "HTTP/1.1 307 Temporary Redirect",
               400 => "HTTP/1.1 400 Bad Request",
               401 => "HTTP/1.1 401 Unauthorized",
               402 => "HTTP/1.1 402 Payment Required",
               403 => "HTTP/1.1 403 Forbidden",
               404 => "HTTP/1.1 404 Not Found",
               405 => "HTTP/1.1 405 Method Not Allowed",
               406 => "HTTP/1.1 406 Not Acceptable",
               407 => "HTTP/1.1 407 Proxy Authentication Required",
               408 => "HTTP/1.1 408 Request Time-out",
               409 => "HTTP/1.1 409 Conflict",
               410 => "HTTP/1.1 410 Gone",
               411 => "HTTP/1.1 411 Length Required",
               412 => "HTTP/1.1 412 Precondition Failed",
               413 => "HTTP/1.1 413 Request Entity Too Large",
               414 => "HTTP/1.1 414 Request-URI Too Large",
               415 => "HTTP/1.1 415 Unsupported Media Type",
               416 => "HTTP/1.1 416 Requested range not satisfiable",
               417 => "HTTP/1.1 417 Expectation Failed",
               500 => "HTTP/1.1 500 Internal Server Error",
               501 => "HTTP/1.1 501 Not Implemented",
               502 => "HTTP/1.1 502 Bad Gateway",
               503 => "HTTP/1.1 503 Service Unavailable",
               504 => "HTTP/1.1 504 Gateway Time-out"       
           );
        header($http[$code]);
    }
    
    
	/**
     * input method contains GET,POST,Cookie,Server,Files,Env
     * more about this at http://funkatron.com/inspekt/user_docs/
	 * 
     * @return Inspekt_Supercage
     * @author Guang Feng
     */
	
	public static function input()
	{
		return Inspekt::makeSuperCage();  
	}
		
    /**
     * inspect urls, find matched class and then run requested method
     *
     * @param string $array 
     * @param string $_baseURLPath 
     * @return void
     * @author Guang Feng
     */
    
    public static function run(array $urls, $devMode = false, $baseURLPath = null)
    {
        if (empty($urls)) {
            throw new Exception("You must pass an array of valid urls to web::run()");
            return;
        }                
        
        // get instance of Web 
        $instance = self::instance();
        $instance->baseUrl($baseURLPath);             
        
        // process the request uri
        $instance->requestUri();
        
        // debug
        $instance->debugMsg('START URL matching');
        
        foreach ($urls as $url_path => $options) {
            $instance->debugMsg(htmlspecialchars($url_path) . 
                                ' : '. 
                                htmlspecialchars($instance->request_uri));
			$url_path = '#^'.$url_path.'$#';
			
            if (preg_match($url_path, $instance->request_uri, $matches)) {
                if (is_string($options)) {
                    $saved = $options;
                    $options = array();
                    $options['module'] = $saved;
                    unset($saved);
                }
                
                if ($options) {
                    $route = array_merge($matches, $options);
                } else {
                    $route = $matches;
                }
                unset($matches);
                
                $instance->params = $route;
                $instance->debugMsg('Matched URL: '.htmlspecialchars($url_path));                
                break;
            }
        }
        
        if (!array_key_exists('module', $route)) {
            throw new RequestErrorException("Page not found.", 404); 
            return;
        }
        
        $class_to_load = $route['module'];
        
        // now check that module exists:
        // finds it based on __autoload function
        if (!class_exists($class_to_load)) {                         
            throw new RequestErrorException("Page not found.", 404);
            return;
        }
        
                
        // instantiate class
        $instance->debugMsg("Loading Class: <b>$class_to_load</b>");
        $loaded_class = new $class_to_load();
        
        // see if class has any pre-run hooks
        $instance->debugMsg('Checking for inner PreRun method');
        if (method_exists($loaded_class, '__PreRun')) {
            $instance->debugMsg('Calling for preRun method');
            $retval = $loaded_class->__PreRun();
           
            // if pre-run hook returns false, stop processing.
            if($retval === false) {
                return;
            } 
        }
        
        $method = $_SERVER['REQUEST_METHOD'];
        $instance->debugMsg("About to run method: $method");
        
        // ajax hook
        if ($instance->isAjaxRequest()) {
            $method = "AJAX";
        }
        
        if (!in_array($method, array('GET', 'POST', 'PUT', 'DELETE', 'AJAX'))) {
            throw new RequestErrorException("HTTP Method not supported.", 405);
            return;
        }
        
        if (!method_exists($loaded_class, $method)) {
            throw new RequestErrorException("HTTP Method not supported by class.", 405);
            return;
        }
        
        $back = $loaded_class->$method($route);        
                
        $instance->debugMsg('Checking for PostRun method');
        
        // see if class has any post-run hooks
        if (method_exists($loaded_class, '__PostRun')) {
            $instance->debugMsg('Calling PostRun method');
            $retval = $loaded_class->__PostRun();
           
            // if post-run hook returns false, stop processing.
            if($retval === false) {
                return;
            } 
        }

       echo $back;
       
	   if($devMode === true) {
		$instance->debugDisplay();
	   }
	
	 }
    
    
    /**
     * inspect headers to see if request is of ajax variety
     *
     * @return void
     * @author Guang Feng
     */
    
    private function isAjaxRequest()
	{
	    return ( isset($_SERVER['HTTP_X_REQUESTED_WITH']) 
	             && $_SERVER['HTTP_X_REQUESTED_WITH']  == 'XMLHttpRequest');       
	}
	
	
	/**
	 * getTemplate
	 * creates instance of template object
	 *                                    
	 * @return object tpl instance
	 * @author Guang Feng
	 */ 
	 
	public function &getTemplate()
	{                                          
	    if (!$this->_tpl) {
	        require_once 'lib/smarty/Smarty.class.php';
            $this->_tpl = new Smarty();
	    }	    
        return $this->_tpl;
	}   
    
    /**
     * use savant3 to render template from a file 
     * echo Web::render('template.html', $tplvars);
     * 
     * @param string $file 
     * @param array $tpl_vars  
     * @return void
     * @author Guang Feng
     */
    
    public static function render($file, array $tpl_vars=null)
    {
        $instance = self::instance();
        $_tpl = $instance->getTemplate();                  
        
        if (defined('TEMPLATES_DIR')) {
            $_tpl->template_dir = TEMPLATES_DIR;
            $instance->debugMsg("Added template path: ". TEMPLATES_DIR);
        }
		
		if (defined('TPL_COMPILED_DIR')) {
			$_tpl->compile_dir = TPL_COMPILED_DIR;
			$instance->debugMsg("Added template compiled path: ".TPL_COMPILED_DIR);
		}
		
		if (defined('TPL_PLUGINS_DIR')) {
			$_tpl->plugins_dir = TPL_PLUGINS_DIR;
			$instance->debugMsg("Added template plugins path: ".TPL_PLUGINS_DIR);
		}
		
		if (defined('TPL_CACHE_DIR')) {
			$_tpl->cache_dir = TPL_CACHE_DIR;
			$_tpl->setCaching(true);
			$instance->debugMsg("Enable template cached : True");
			$instance->debugMsg("Added template cached path: ".TPL_CACHE_DIR);
		}
		
		$_tpl->use_sub_dirs = defined('TPL_SUB_DIR') ? TPL_SUB_DIR : false;
		
		

        if($tpl_vars) {       
            $_tpl->assign($tpl_vars);                          
            $instance->debugMsg("Assigned these variables to template: <b>".
                                      implode(", ", array_keys($tpl_vars))."</b>");
        }                                                         
                                                                          
        $instance->debugMsg("Loading template: $file");
        $output = $_tpl->fetch($file);
        
		
        
        return $output; 
    }                                            
	
	/**
     * web.php database (webdb for short) method depended upon ADODB project
	 * $db = Web::database('mysql','hello','root','passwd');
	 * $db->select($table,$where = null ,$what = " * " ,$order = null,$limit =1,$offset = 0,$test = false)
     * or ->insert(),->update(),->delete();
	 * $db->debugDisplay() will display webdb running time messages.
	 * 
     * @param string $dbn   Name of the database system we are connecting to. Eg. odbc or mssql or mysql.
     * @param string $db    Name of the database or to connect to. 
     * @param string $user  Login id to connect to database. Password is not saved for security reasons.
     * @param string $pw    password
     * @param string $host
     * @param string $port
     * @param string $char   set the default charset to use.
     * @param string $debug  debug mode
     * @return Webdb Instance Object
     * @author Guang Feng
     */

    public static function database($dbn, $db, $user, $pw , $host = '127.0.0.1', $port = '3306', $char = 'UTF8', $debug = false)
    {   
        include_once 'lib/db.php';
		return db::Instance($dbn, $db, $user, $pw , $host, $port, $char, $debug);
    }
    
    /**
     * send a request to a url and get the response
     * dependent on allow_url_fopen being turned on
     * more about at http://cn.php.net/manual/en/filesystem.configuration.php#ini.allow-url-fopen
     * if allow_url_fopen is off, it will try curl
     *
     * @param string $url 
     * @param array $data 
     * @param string $method http method
     * @param array $optional_headers 
     * @return string $response
     * @throws RequestErrorException 400 on bad request
     * @author Guang Feng
     */
    
    public function request($url, array $data=null, $method='POST', $timeout = 3, array $optional_headers=null)
    {   
        self::instance()->debugMsg('Sending a request via fopen to: '.$url);
        if (!$on = ini_get('allow_url_fopen')) {
            return self::curlRequest($url, $data, $method, $timeout, $optional_headers);
        }
        $params = array('http'    => array(
                        'method'  => $method,
                        'content' => http_build_query($data),
						'timeout' => $timeout
                        ));

        if ($optional_headers !== null) {
            $params['http']['header'] = $optional_headers;
        }
        $ctx = stream_context_create($params);
        $fp = fopen($url, 'rb', false, $ctx);
        if (!$fp) {
            throw new RequestErrorException("Problem with $url, $php_errormsg", 400);
        }
        $response = stream_get_contents($fp);
        if ($response === false) {
            throw new RequestErrorException("Problem reading data from $url, $php_errormsg", 400);
        }
        return $response;
    }
    
    /**
     *
     * @param string $url 
     * @param array $data
     * @param string $method 
     * @param string $optional_headers 
     * @return string $response
     * @throws RequestErrorException
     * @author Guang Feng
     */
    
    function curlRequest($url, array $data=null, $method='POST', $timeout = 3, array $optional_headers=null)
    {              
        self::instance()->debugMsg('Sending a request via CURL to: '.$url);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CONNECTTIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		
        if (!is_null($optional_headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $optional_headers);
        } 
        
        // check method
        if ($method == 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_URL, $url);
        } else {                                     
            if (!empty($data)) {
                $url .= '?'.http_build_query($data);
            }
            curl_setopt($ch, CURLOPT_URL, $url);
        }
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        if ($response === false) {
            throw new RequestErrorException("$url not reachable", 400);
        }
        return $response;
    }
    
    
    /**
     * params
     *
     * @return stored web params from request_uri
     * @author Guang Feng
     */
    public static function params()
    {
        return self::instance()->params;
    }   

}


