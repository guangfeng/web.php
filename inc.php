<?php 

error_reporting(E_ALL);
ini_set('display_errors',1);


/**
 * put anything here you want loaded before web.php
 * any config stuff should be in this file
 *
 * @author Guang Feng
 */
define("SITE_BASE", dirname(__FILE__));
define("MODULES_DIR", SITE_BASE."/modules");  
define('TEMPLATES_DIR', SITE_BASE.'/templates');

define('TPL_COMPILED_DIR', SITE_BASE.'/compiled');
define('TPL_PLUGINS_DIR', TEMPLATES_DIR.'/plugins');

//template cache enable if defined this.
//define('TPL_CACHE_DIR', TEMPLATES_DIR.'/cached');


define('TPL_SUB_DIR',false);

// include path
set_include_path( SITE_BASE . '/webphp/lib/' . PATH_SEPARATOR . get_include_path());


/**
 *
 * @return void
 * @author Guang Feng
 */

function __autoload($class_name)
{                                                
	$class = str_replace('_','/',$class_name);
	$file_name = $class.".class.php";
	$file_path = MODULES_DIR.'/'.$file_name;
	if(file_exists($file_path)) 
		include_once $file_path;		
}


include 'webphp/web.php';  
                           
