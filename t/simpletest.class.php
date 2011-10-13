<?php
require_once '../webphp/web.php';

class Simpletest
{
	public $stack = array();
	
	public $result = array();
	
	public $runer = array();
	
	function __construct()
	{
		$this->result =  array('total'   => 0,
								'pass'   => 0,
								'fail' => 0,);
								
		$this->runner = array('pass' => array(),'fail' => array());
	}
	
	public function get($url,$data = null)
	{
		array_push($this->stack,Web::request($url,$data,'GET'));
	}
	
	public function ajax($url,$data = null)
	{
		array_push($this->stack,Web::request($url,$data,'GET',3,array('HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest')));
	}
	
	public function post($url,$data)
	{
		array_push($this->stack,Web::request($url,$data));
	}
	
	public function assertTrue() 
	{
		if (func_num_args()) {
			$result = func_get_arg(0);
			$message = func_get_arg(1);
			
			if(!$message)
				$message = 'True assertion got ' . ($result ? 'True' : 'False');
				
			if($result) {
				$this->pass($message);
				return true;
			} else {
				$this->fail($message);
				return false;
			}
		} else {
			$result = array_pop($this->stack);
			$message = 'True assertion got from stack ' . ($result ? 'True' : 'False');
			if($result) {
				$this->pass($message);
				return true;
			} else {
				$this->fail($message);
				return false;
			}
		}
	}
	
	public function assertEqual() 
	{
		if (func_num_args() == 2) {
			$src = func_get_arg(0);
			$to = func_get_arg(1);
			
			$message = 'Equal assertion got ' . ($src === $to ? 'True' : 'False');
				
			if($src === $to) {
				$this->pass($message);
				return true;
			} else {
				$this->fail($message);
				return false;
			}
		} else {
			$src = array_pop($this->stack);
			$to = func_get_arg(0);
			
			$message = 'Equal assertion got from stack ' . ($src === $to ? 'True' : 'False');
			
			if($src === $to) {
				$this->pass($message);
				return true;
			} else {
				$this->fail($message);
				return false;
			}
		}
	}
	
	public function assertFalse()
	{
		if(func_num_args()) {
			$result = func_get_arg(0);
			$message = func_get_arg(1);
			
			if(!$message)
				$message = 'False assertion got ' . ($result ? 'True' : 'False');
				
			return $this->assertTrue(!$result,$message);
		} else {
			$result = array_pop($this->stack);
			$message = 'False assertion got from stack ' . ($result ? 'True' : 'False');
			
			return $this->assertTrue(!$result,$message);
		}
	}
	
	public function pass($message = 'pass')
	{
		$this->result['total']++;
		$this->result['pass']++;
		$bt = debug_backtrace();
		$bt = $bt[1];
		array_push($this->runner['pass'],$bt['function']."() passed ! at File:".$bt['file']." Line:".$bt['line']);
		
	}
	
	public function fail($value='')
	{
		$this->result['total']++;
		$this->result['fail']++;
		$bt = debug_backtrace();
		$bt = $bt[1];
		array_push($this->runner['fail'],$bt['function']."() failed ! at File:".$bt['file']." Line:".$bt['line']);
		
	}
	
	public function run()
	{
		# code...
	}
}
