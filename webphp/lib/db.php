<?php
include_once 'adodb/adodb.inc.php';

/**
* Web.php Database Handler
*/
class db {

	protected $_conn 			= null;
	protected $_debugMessages  	= array();
	protected $_debug           = false;
	static protected $instance  = null;


	public function __construct($dbn, $db, $user, $pw , $host = '127.0.0.1', $port = '3306', $char = 'UTF8', $debug = false) {
		if($debug){
			$this->_debug = $debug;
			$this->debugMsg("WebDB Debug Mode : Enable");
		}

		$this->debugMsg("WebDB class will connect database : $dbn at $host");

		try {

			$this->_conn = &ADONewConnection($dbn);
			$this->_conn->debug = $debug;
			$ret = $this->_conn->Connect($host.':'.$port,$user,$pw,$db);

			if(!$ret) {
				$this->debugMsg("db connect : failed!");
				exit;
			}
			$this->_conn->SetFetchMode(ADODB_FETCH_ASSOC);
			$this->_conn->Execute('SET NAMES '.$char);

		}catch (exception $e) {
			adodb_backtrace($e->gettrace());
		}

	}


	/**
     * create instance of Webdb. Only use this method
     * to get an instance.
     * @param string $dbn   Name of the database system we are connecting to. Eg. odbc or mssql or mysql.
     * @param string $db    Name of the database or to connect to.
     * @param string $user  Login id to connect to database. Password is not saved for security reasons.
     * @param string $pw    password
     * @param string $host
     * @param string $port
     * @param string $char   set the default charset to use.
     * @param string $debug  debug mode
     * @author Guang Feng
     */

    public static function &Instance($dbn, $db, $user, $pw , $host = '127.0.0.1', $port = '3306', $char = 'UTF8', $debug = false) {

		if (self::$instance == null)
			self::$instance = new db($dbn, $db, $user, $pw , $host, $port, $char, $debug);
		return self::$instance;
	}

	/**
     * save debug messages to var for displaying later.
     *
     * @param string $msg
     * @return void
     * @author Guang Feng
     */

	public function debugMsg($msg) {
		$this->_debugMessages[] = $msg;
	}

	/**
     * display debug messages
     *
     * @return void
     * @author Guang Feng
     */

	public function debugDisplay() {
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
     * Execute SQL statement $sql and return derived class of ADORecordSet
     *
	 * @param string $sql
     * @return void
     * @author Guang Feng
     */

	public function query($sql, $vars = false) {

		$this->DebugMsg("database will execute sql : $sql , ".print_r($vars,true));
		$rs = $this->_conn->Execute($sql,$vars);

		$this->DebugMsg("database exceuted result : ".print_r($rs,true));

		if(!$rs) return false;
		else return $rs->GetRows();
	}


	/**
     * Returns a recordset which contains the field name keys and field value successful. Returns false otherwise.
     *
	 * @param string $table
	 * @param string $where
	 * @param string $what
	 * @param string $order
	 * @param string $limit
	 * @param string $offset
	 * @param string $test
     * @return array
     * @author Guang Feng
     */

	public function select($table,$where = null ,$what = " * " ,$order = null,$limit =1,$offset = 0,$test = false) {
		$sql = "SELECT ".strtoupper($what)." FROM ".( is_array($table)?implode(',',$table):$table ).(!empty($where) ?" WHERE $where " : " ")." ".(!empty($order)?
		"ORDER BY ".strtoupper($order):" ")." LIMIT $offset,$limit";

		if ($test) {
			echo "<pre>";
			echo "SQL : ".$sql;
			echo '</pre>';
			return true;
		}

		$this->DebugMsg("database will execute select : $sql");
		return $this->_conn->GetAll($sql);
	}

	public function update($table,$where = null, $update = array(),$test = false) {
		$upstr = array();

		array_walk($update,function ($arr,$key) use (&$upstr) {
			$upstr[] =  " `$key` = ".(is_string($arr) ? "'$arr'" : $arr);
		});

		$this->DebugMsg("database processed update parameters : ".print_r($upstr,true));

		$sql = "UPDATE $table SET ".implode(',',$upstr)." WHERE ".$where;

		if ($test) {
			echo "<pre>";
			echo "SQL : ".$sql;
			echo '</pre>';
			return true;
		}

		$this->DebugMsg("database will execute update : $sql");
		$rs =  $this->_conn->Execute($sql);
		$this->DebugMsg("database executed result : ".print_r($rs,true));

		if(!$rs) return false;
		else return $this->_conn->Affected_Rows();
	}

	public function delete($table,$where,$test = false) {
		$sql = "DELETE FROM $table WHERE $where";

		if ($test) {
			echo "<pre>";
			echo "SQL : ".$sql;
			echo '</pre>';
			return true;
		}

		$this->DebugMsg("database will execute delete : $sql");
		$rs =  $this->_conn->Execute($sql);
		$this->DebugMsg("database executed result : ".print_r($rs,true));

		if(!$rs) return false;
		else return $this->_conn->Affected_Rows();
	}

	public function insert($table,$data,$test = false) {

		$ins = array('key' => array(),'value' => array());

		array_walk($data,function($value,$key) use(&$ins) {
			$ins['key'][] = "`$key`";
			$ins['value'][] = is_string($value)?"'$value'":$value;
		} );

		$this->DebugMsg("database processed insert parameters : ".print_r($ins,true));

		$sql = "INSERT INTO $table (".implode(',',$ins['key']).") VALUES (".implode(',',$ins['value']).") ";

		if ($test) {
			echo "<pre>";
			echo "SQL : ".$sql;
			echo '</pre>';
			return true;
		}

		$this->DebugMsg("database will execute insert : $sql");
		$rs =  $this->_conn->Execute($sql);
		$this->DebugMsg("database executed result : ".print_r($rs,true));

		if(!$rs) return false;
		else return $this->_conn->Insert_ID();
	}

}
