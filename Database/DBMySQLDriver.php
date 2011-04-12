<?php
//  DBMySQLDriver.php
//  Sonata/Database
//
// Copyright 2010 Roman Efimov <romefimov@gmail.com>
//
// Licensed under the Apache License, Version 2.0 (the "License");
// you may not use this file except in compliance with the License.
// You may obtain a copy of the License at
//
//    http://www.apache.org/licenses/LICENSE-2.0
//
// Unless required by applicable law or agreed to in writing, software
// distributed under the License is distributed on an "AS IS" BASIS,
// WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
// See the License for the specific language governing permissions and
// limitations under the License.
//

class DBMySQLDriver extends STObject {
	private static $_instance = null;
	private $_connection = null;
	private $_db_name = null;
	private $_insert_id = null;
	private $_is_logging = true;
	private $_error_message = "";
	private $_debug = false;
	private $_has_transaction_failed = false;
	private $_transaction_starts = 0;
	private $_is_transactions = true;
	private $_error = '';
	private $_query_id = null;
	private $_affected_rows = null;
	private $_cache = array();
      
	/*
	 *  Returns a singleton instance of DBMySQLDriver.
	 *
	 *  @return DBMySQLDriver A singleton instance of DBMySQLDriver.
	 */
	public static function instance() {
		if(!self::$_instance) self::$_instance = new DBMySQLDriver();
		return self::$_instance;
	}
      
	/*
	 *  Escapes a value for a query.
	 *
	 *  @param string $string Value to escape.
	 *  @return string Escaped string.
	 */
	public function escape($string) {
		if(get_magic_quotes_gpc()) $string = stripslashes($string);
		$string = str_replace('\\', '\\\\', $string);
		return $this->_connection->real_escape_string(trim($string));
	}
	
	/*
	 *  Returns current MySQL connection.
	 *
	 *  @return MySQL connection.
	 */
	public function getConnection() {
		return $this->_connection;
	}
	
	/*
	 *  Sets the current database connection.
	 *
	 *  @param MySQL connection.
	 */
	protected function setConnection($connection) {
		$this->_connection = $connection;
	}
	
	/*
	 *  Sees if the default connection has been established.
	 *
	 *  @return  DBMySQLDriver or null
	 */
	protected static function hasDefaultDBConnection() {
		return self::$_instance != null;
	}
	
	/*
	 *  Returns the default database connection.
	 *
	 *  @return DBMySQLDriver An instance of DBMySQLDriver
	 */
	protected static function getDefaultDBConnection() {
		return self::$_instance;
	}
	
	/*
	 *  Sets the default database connection.
	 *
	 *  @param DBMySQLDriver $db DBMySQLDriver object.
	 */
	public static function setDefaultDBConnection($db) {
		self::$_instance = $db;
	}
	
	/*
	 *  Creates and returns DBMySQLDriver object with established database connection.
	 *
	 *  @param   string  $dbHost       Database host.
	 *  @param   string  $dbUser       Database user.
	 *  @param   string  $dbPassword   Database password.
	 *  @param   string  $dbName       Database name.
	 *  @param   boolean $mysqli       Whether use mysqli extension or not.
	 *  @return  DBMySQLDriver DBMySQLDriver object.
	 */
	public static function getDBConnection($dbHost, $dbUser, $dbPassword, $dbName = null, $mysqli = true) {
		if ($mysqli) {
			if (!extension_loaded("mysqli"))
				throw new STDatabaseException("mySQLi extension not loaded");
			if ($dbName !== null)
				$connection = new mysqli($dbHost, $dbUser, $dbPassword, $dbName); else
					$connection = new mysqli($dbHost, $dbUser, $dbPassword);
			if(mysqli_connect_errno()) 
				throw new STDatabaseException("No connection to the database");
			$connection->set_charset('utf8');
			$db = new DBMySQLDriver();			
			$db->setConnection($connection);
		} else {
			if(!extension_loaded("mysql"))
				throw new STDatabaseException("mySQL extension not loaded");				  
			$link = mysql_connect($dbHost, $dbUser, $dbPassword, true);  
			if (!$link)
				throw new STDatabaseException("Connect failed: ".mysql_error());  
			if (!mysql_select_db($dbName, $link))
				throw new STDatabaseException("Failed to select DB: ".mysql_error());
			$db = new DBMySQLDriver();			
			$db->setConnection($link);
		}
		return $db;
	}
	  
	/*
	 *  Frees MySQL results.
	 *
	 *  @param   mixed $query_id  Query ID  
	 */
    private function freeResult($query_id = -1) {
		if (!is_int($query_id)) $this->_query_id = $query_id;
		$this->_query_id->free_result();
    }
	
	/*
	 * 	Fetches array of results.
	 *
	 * 	@param   mixed $query_id  Query ID  
	 * 	@return  array Array of results
	 */
	private function fetchArray($query_id =- 1) {
		if (!is_int($query_id)) $this->_query_id = $query_id;
		if (isset($this->_query_id)) $record = $this->_query_id->fetch_assoc(); else
			throw new STDatabaseException("Invalid query_id: <b>{$this->_query_id}</b>. Records could not be fetched.");
		if ($record) $record = array_map("stripslashes", $record);
		return $record;
	}
	
	/*
	 *  Runs a query.
	 *
	 *  @param   string $sql SQL query.
	 *  @return  mixed Mixed query result.
	 */
	public function query($sql) {
		// Check if Profiler exists, if yes then start logging
		if (class_exists("STProfiler")) STProfiler::start('Query "'.$sql.'"', "SQL query");
		
		$result = $this->_connection->query($sql);
		if (!$result) throw new STDatabaseException("<b>MySQL Query fail:</b> $sql");
		$this->_affected_rows = @$this->_connection->affected_rows;
		if (class_exists("STProfiler")) STProfiler::end();
		return $result;
	}
	  
	  
	/*
	 *  Selects all data from the query results.
	 *
	 *  @param   string   $sql Query String.
	 *  @param   boolean  $internalCacheResults Whether use internal query
	 *  										cache or not (to avoid running
	 *  										the same query multiple times).
	 *  @return  array     Array of the query results.
	 */
	public function selectAll($sql, $internalCacheResults = true) {
		$sql = str_replace("{calc}", "SQL_CALC_FOUND_ROWS", $sql);
		
		if ($internalCacheResults)
			if (isset($this->_cache[md5($sql)]))
				return $this->_cache[md5($sql)];
		
		$_query_id = $this->query($sql);
		$out = array();
	
		while ($row = $this->fetchArray($_query_id, $sql)) {
			$out[] = $row;
		}
	
		$this->freeResult($_query_id);
		$this->_cache[md5($sql)] = $out;
		return $out;
	}
	
	/*
	 *  Selects the first found row from the query results.
	 *
	 *  @param   string   $sql Query String.
	 *  @param   boolean  $internalCacheResults Whether use internal query
	 *  										cache or not (to avoid running
	 *  										the same query multiple times).
	 *  @return  array     Array of the query result.
	 */
	public function selectFirst($sql, $internalCacheResults = true) {
		if ($internalCacheResults)
			if (isset($this->_cache[md5($sql)]))
				return $this->_cache[md5($sql)];
		
		$query_id = $this->query($sql);
		$out = $this->fetchArray($query_id);
		$this->freeResult($query_id);
		$this->_cache[md5($sql)] = $out;
		return $out;
	}
	
	/*
   	 *  Selects a variable from the first found row from the query results.
	 *
	 *  @param   string  $sql  Query string.
	 *  @param   integer $id  Parameter ID
	 *  @param   boolean  $internalCacheResults Whether use internal query
	 *  										cache or not (to avoid running
	 *  										the same query multiple times).
	 *  @return  string   Parameter value.
	 */
	public function getVar($sql, $id = 0, $internalCacheResults = true) {
		if ($internalCacheResults)
			if (isset($this->_cache[md5($sql)]))
				return $this->_cache[md5($sql)];
		
		$query_id = $this->query($sql);
		$out = $this->fetchArray($query_id);
		$this->freeResult($query_id);
		
		if ($out)
		foreach ($out as $i => $res) {
			if ($i != $id) continue;
			$this->_cache[md5($sql)] = $res;
			return $res;
		}
	}
	  
	/*
	 *  Compiles an insert SQL string and runs the query.
	 *
	 *  @param   string $table Table name.
 	 *  @param   array  $data  Array of key/value pairs to insert.
	 *  @return  mixed  Last insterted ID if query was successful, otherwise false.
	 */
	public function insert($table, $data) {
		$q="INSERT INTO `".$this->pre.$table."` ";
		$v='';
		$n='';
	
		foreach($data as $key => $val) {
			$n.="`$key`, ";
			if (strtolower($val) == 'null') $v.="NULL, ";
			elseif (strtolower($val) == 'now()') $v.="NOW(), ";
			else $v.= "'".$this->escape($val)."', ";
		}
	
		$q .= "(". rtrim($n, ', ') .") VALUES (". rtrim($v, ', ') .");";
		if ($this->query($q)) {
			$this->setLastInsertId($this->_connection->insert_id);
			return $this->getLastInsertId();
		}
		else return false;
	}
      
	/*
	 *  Compiles an update SQL string and runs the query.
	 *
	 * @param   string $table Table name
	 * @param   array  $data  Array of key/value pairs to update.
	 * @param   string $where WHERE SQL statement.
	 * @return  mixed  Query result.
	 */
	public function update($table, $data, $where = '1') {
		$q="UPDATE ".$this->pre.$table." SET ";
		foreach ($data as $key => $val) {
			if (strtolower($val) == 'null') $q.= "`$key` = NULL, ";
			elseif (strtolower($val) == 'now()') $q.= "`$key` = NOW(), ";
			else $q .= "`$key`='".$this->escape($val)."', ";
		}
		$q = rtrim($q, ', ') . ' WHERE '.$where.';';
		return $this->query($q);
	}
	  
	/*
	 *  Compiles an insert (with "on duplicate" options) SQL string and runs the query.
	 *
	 *  @param   string $table Table name.
	 *  @param   array  $data  Array of key/value pairs to insert.
	 *  @param   string $duplicate SQL statement "ON DUPLICATE KEY UPDATE
	 *  						   $duplicate" or insert can be ignored by
	 *  						   setting $duplicate to __IGNORE.
	 *  @return  mixed  Last insterted ID if query was successful, otherwise false.
	 */
	public function insertOnDuplicate($table, $data, $duplicate) {
		$q="INSERT ".(($duplicate == '__IGNORE')?"IGNORE":"")." INTO `".$this->pre.$table."` ";
		$v=''; $n='';
	
		foreach($data as $key=>$val) {
			$n.="`$key`, ";
			if (strtolower($val) == 'null') $v.="NULL, ";
			elseif (strtolower($val) == 'now()') $v.="NOW(), ";
			else $v.= "'".$this->escape($val)."', ";
		}
	
		if ($duplicate != '__IGNORE') {
			$update = array();
			foreach ($duplicate as $key => $value) {
				$update[] = '`'.$key."` = '".$this->escape($value)."'";
			}
			$update = implode(", ", $update);
			$q .= "(". rtrim($n, ', ') .") VALUES (". rtrim($v, ', ') .") ON DUPLICATE KEY UPDATE ".$update.";";
		} else {
			$q .= "(". rtrim($n, ', ') .") VALUES (". rtrim($v, ', ') .")";
		}
	
		if ($this->query($q)) {
			$this->setLastInsertId($this->_connection->insert_id);
			return $this->getLastInsertId();
		} else return false;
	}
	  
	/*
	 *  Returns last inserted row's ID.
	 *
	 *  @return  int  Last ID.
	 */
	public function getLastInsertId() {
		return $this->_insert_id;
	}
	
	/*
	 *  Sets last insert id.
	 *
	 *  @param   int   Insert ID
	 */
	private function setLastInsertId($insertId) {
		$this->_insert_id = $insertId;
	}
      
	/*
	 *  A wrapper for SQL "FOUND_ROWS()".
	 *
	 *  @return  int  Result of FOUND_ROWS().
	 */
	public function foundRows() {
		return intval($this->getVar("SELECT FOUND_ROWS();"));
	}
      
	/*
	 *  Sets default charset for the current connection.
	 *
	 *  @param   string  $charset  Charset name (e.g. UTF-8).
	 */
	public function setDefaultCharset($charset) {
		$this->query("SET NAMES '$charset';");
	}
	
	/*
	 * 	Begins transaction.
	 *
	 */
	public function startTransaction() {
		if (!(isset($this) && get_class($this) == __CLASS__))
			return DBMySQLDriver::instance()->startTransaction();
  
		if ($this->_is_transactions)
			if ($this->_transaction_starts==0) {
				$this->query("SET AUTOCOMMIT=0");
				$this->query("BEGIN");
			}
	  
		$this->_transaction_starts++;
	}
      
	/*
	 *  Completes transaction.
	 *
	 */
	public function completeTransaction() {
		if (!(isset($this) && get_class($this) == __CLASS__))
			return DBMySQLDriver::instance()->completeTransaction();
	  
		$this->_transaction_starts--;
	  
		if ($this->_transaction_starts==0) {
			if ($this->_is_transactions) {
				if (!$this->_has_transaction_failed) {
					$this->query("COMMIT");
					$this->query("SET AUTOCOMMIT=1");
				} else $this->failTransaction();
			}
			$this->_has_transaction_failed = false;
		} 
	}

	/*
	 *  Fails transaction.
	 *
	 */
	public function failTransaction() {
		if (!(isset($this) && get_class($this) == __CLASS__))
			return DBMySQLDriver::instance()->failTransaction();
			
		$this->_has_transaction_failed = true;
		
		if ($this->_is_transactions) {
			$this->query("ROLLBACK");
			$this->query("SET AUTOCOMMIT=1");
		}
	}  
	
	/*
	 *  Checks if the transaction has failed.
	 *
	 *  @return  bool true if the transaction has failed, otherwise false.
	 */
	public function hasTransactionFailed() {	
		if(!(isset($this) && get_class($this) == __CLASS__))
			return DBMySQLDriver::instance()->hasTransactionFailed();
	  
		return $this->_has_transaction_failed;
	}

	/*
	 *  Checks if the transaction has succeeded.
	 *
	 *  @return  bool true if the transaction has succeeded, otherwise false.
	 */
	public function hasTransactionSucceeded() {
		if(!(isset($this) && get_class($this) == __CLASS__))
			return DBMySQLDriver::instance()->hasTransactionSucceeded();
	
		return !$this->_has_transaction_failed;
	}
	
	/*
	 *  Disables table keys.
	 *
	 *  @param   string  $tableName  Table name.
	 *  @return  mixed   Query result.
	 */
    public function disableTableKeys($tableName) {
	    return $this->query("ALTER TABLE `".$tableName."` DISABLE KEYS");
    }		
	
	/*
	 *  Enables table keys.
	 *
	 *  @param   string  $tableName  Table name.
	 *  @return  mixed   Query result.
	 */
    public function enableTableKeys($tableName) {
		return $this->query("ALTER TABLE `".$tableName."` ENABLE KEYS");
    }
	
	/*
	 *  Enables unique check.
	 *  
	 *  @return  mixed   Query result.
	 */
	public function enableUniqueCheck() {
	    return $this->query("SET UNIQUE_CHECKS=1");
    }

	/*
	 *  Disables unique check.
	 *  
	 *  @return  mixed   Query result.
	 */
    public function disableUniqueCheck() {
	    return $this->query("SET UNIQUE_CHECKS=0");
    }			
      
	/*
	 *  Enables foreign key checks.
	 *  
	 *  @return  mixed   Query result.
	 */
	public function enableForeignKeys() {
		if(!(isset($this) && get_class($this) == __CLASS__))
			return DBMySQLDriver::instance()->enableForeignKeys();		
  
		return $this->query("SET FOREIGN_KEY_CHECKS=1");
	}
	
	/*
	 *  Disables foreign key checks.
	 *  
	 *  @return  mixed  Query result.
	 */
	public function disableForeignKeys() {
		if(!(isset($this) && get_class($this) == __CLASS__))
			return DBMySQLDriver::instance()->disableForeignKeys();		
  
		return $this->query("SET FOREIGN_KEY_CHECKS=0");
	} 
	
	/*
	 *  Truncates the table.
	 *
	 *  @param   string  $tableName  Table name.
	 *  @return  mixed   Query result.
	 */
	public function truncateTable($tableName) {
		return $this->query("TRUNCATE `".$tableName."`");
	}
      
	/*
	 *  Sees if the table exists in the database.
	 *
	 *  @param   string  $tableName Table name.
	 *  @return  bool    true if the table exists, otherwise false.
	 */
	public function doesTableExist($tableName) {							
		return (bool) $this->selectFirst("SHOW TABLES LIKE '".$tableName."'");			
	}		
	
	/*
	 *  Sees if the database exists in the schema.
	 *
	 *  @param   string  $dbName Table name.
	 *  @return  bool    true if the database exists, otherwise false.
	 */
	public function doesDatabaseExist($dbName) {
		$sql = "SELECT count(*) FROM `information_schema`.`schemata` WHERE `schema_name` = '".$dbName."'";									
		return ($this->getVar($sql));
	}
      
	/*
	 *  Creates a new database.
	 *
	 *  @param   string $dbName  Database name.
	 *  @return  mixed  Query result.
	 */
	public function createDatabase($dbName) {
		return $this->query("CREATE DATABASE ".$dbName);
	}	
	
	/*
	 *  Drops the database.
	 *
	 *  @param   string $dbName  Database name.
	 *  @return  mixed  Query result.
	 */
	public function dropDatabase($dbName) {
		return $this->query("DROP DATABASE ".$dbName);
	}
	
	/*
	 *  Creates a new database user.
	 *
	 *  @param   string $username  Username.
	 *  @param   string $password  Password.
	 *  @param   string $database  Database name.
	 *  @param   string $host      Database host.
	 *  @param   bool   $select    Grant select permission.
	 *  @param   bool   $update    Grant update permission.
	 *  @param   bool   $delete    Grant delete permission.
	 *  @param   bool   $insert    Grant insert permission.
	 *  @return  mixed  Query result.
	 */
	public function createUser($username, $password, $database, $host, $select = true, $update = true, $delete = true, $insert = true) {	
		$privs = array();
		if ($select) $privs[] = "SELECT";
		if ($update) $privs[] = "UPDATE";
		if ($delete) $privs[] = "DELETE";			
		if ($insert) $privs[] = "INSERT";
		$priv_str = implode(",",$privs);
		$sql = "GRANT ".$priv_str." ON ".$database.".* TO '".$username."'@'".$host."' IDENTIFIED BY '".$password."';";
		return $this->query($sql);		
	}
	
	/*
	 *  Drops the user.
	 *
	 *  @param   string $dbUser  Username.
	 *  @param   string $dbHost  Host.
	 *  @return  mixed  Query result.
	 */
	public function dropUser($dbUser, $dbHost) {
		return $this->query("DROP USER `".$dbUser."`@`".$dbHost."`");
	}		
	
	/*
	 *  Sees if the user exists.
	 *
	 *  @param   string  $username Username.
	 *  @param   string  $host  Host.
	 *  @return  bool  true if the user exists, otherwise false.
	 */
	public function doesUserExist($username, $host) {
		$sql = "SELECT count(*) FROM `mysql`.`user` WHERE `User` = '".$username."' AND `Host` = '".$host."'";			
	  
		return $this->getVar($sql)>0;
	}
	
	/*
	 *  Returns the list of tables.
	 *
	 *  @return  array  List of tables.
	 */
	public function getTables() {			
		$tables = $this->select("SHOW TABLES");					
		return $tables;		
	}
	
	/*
	 *  Returns fields from the table.
	 *
	 *  @param  string $tableName Table name.
	 *  @return  array  List of fields.
	 */
	public function getTableFields($tableName) {
		return $this->select("SHOW FULL FIELDS FROM `".$tableName."`");		
	}
	
	/*
	 *  Returns MySQL file import command.
	 *
	 *  @param   string $dbHost  Database host.
	 *  @param   string $dbUser  Username.
	 *  @param   string $dbPassword  Password.
	 *  @param   string $dbName  Database name.
	 *  @param   string $sqlFile  SQL file to import.
	 *  @return  string Command string.
	 */
	public static function getMySQLImportCommand($dbHost, $dbUser, $dbPassword, $dbName, $sqlFile) {
		$cmd = "mysql -h%s -u%s -p%s %s < %s";
		return sprintf($cmd, $dbHost, $dbUser, $dbPassword, $dbName, $sqlFile);
	}
	
	/*
	 *  Returns connection error (if any).
	 *
	 *  @return  mixed  Connection error.
	 */
	public function getConnectionError() {
		return $this->_connection->error;
	}
      
} 

?>