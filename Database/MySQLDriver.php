<?
//  MySQLDriver.php
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

class MySQLDriver extends STObject {
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
      
      # implementation
      
      /*
       * Returns a singleton instance of DB.
       *
       * @param   void
       * @return  DB
       */
      static function instance() {
	    if(!self::$_instance) self::$_instance = new MySQLDriver();
	    return self::$_instance;
      }
      
      /*
       * Escapes a value for a query.
       *
       * @param   mixed   Value to escape
       * @return  string
       */
      public function escape($string) {
	    if(get_magic_quotes_gpc()) $string = stripslashes($string);
	    $string = str_replace('\\', '\\\\', $string);
	    return $this->_connection->real_escape_string(trim($string));
      }
	
      /*
       * Gets current database connection.
       *
       * @param   void
       * @return  mixed   Database connection
       */
      public function getConnection() {
	    return $this->_connection;
      }
	
      /*
       * Gets current database name.
       *
       * @param   void
       * @return  string   Database name
       */
      protected function getDBName() {
	    return $this->_db_name;
      }
      
      /*
       * Sets current database name.
       *
       * @param   string   Database name   
       * @return  null
       */
      protected function setDbName($db_name) {
	    $this->_db_name = $db_name;
      }
	
      /*
       * Sets current database connection.
       *
       * @param   mixed   Database connection
       * @return  null
       */
      protected function setConnection($connection) {
	    $this->_connection = $connection;
      }
	
      /*
       * See if a default connection has been established.
       *
       * @param   mixed   Database connection
       * @return  null
       */
      protected static function hasDefaultDBConnection() {
	    return self::$_instance != null;
      }
	
      /*
       * Gets default database connection.
       *
       * @param   void
       * @return  mixed   Database connection
       */
      protected static function getDefaultDBConnection() {
	    return self::$_instance;
      }
	
      /*
       * Sets default database connection.
       *
       * @param   mixed   Database connection
       * @return  void
       */
      public static function setDefaultDBConnection($db) {
	    self::$_instance = $db;
      }
	
      /*
       * Gets last insert id.
       *
       * @param   void
       * @return  integer   Insert ID
       */
      public function getLastInsertId() {
	    return $this->_insert_id;
      }
	
      /*
       * Sets last insert id.
       *
       * @param   integer   Insert ID
       * @return  null
       */
      private function setLastInsertId($insert_id) {
	    $this->_insert_id = $insert_id;
      }
	
      /*
       * Database connect method to get the database queries up and running.
       *
       * @param   string     Database Host
       * @param   string     Database User
       * @param   string     Database Password
       * @param   string     Database Name
       * @param   boolean    Use MySQLi extension
       * @return  DB         Database Object
       */
      public static function getDBConnection($db_host, $db_user, $db_pass, $db_name = null, $mysqli = true) {
      
	    // If using MySQLi
	    if ($mysqli) {
		  if (!extension_loaded("mysqli"))
			throw new STDatabaseException("mySQLi extension not loaded");

		  if ($db_name!==null)
			$connection = new mysqli($db_host, $db_user, $db_pass, $db_name); else
			      $connection = new mysqli($db_host, $db_user, $db_pass);

		  if(mysqli_connect_errno()) 
			throw new STDatabaseException("No connection to the database");

		  $connection->set_charset('utf8');

		  $db = new MySQLDriver();			
		  $db->setConnection($connection);
		  $db->setDBName($db_name);
			
		} else {
		
		  if(!extension_loaded("mysql"))
			throw new STDatabaseException("mySQL extension not loaded");
				
		  $link = mysql_connect($db_host,$db_user,$db_pass,true);
			
		  if (!$link)
			throw new STDatabaseException("Connect failed: ".mysql_error());
			
		  if (!mysql_select_db($db_name, $link))
			throw new STDatabaseException("Failed to select DB: ".mysql_error());
				
		  $db = new MySQLDriver();			
		  $db->setConnection($link);
		  $db->setDBName($db_name);
	    }

	    return $db;
      }
      
      /*
       * Calculates found rows.
       *
       * @param   void
       * @return  integer   Found rows count.
       */
      public function foundRows() {
	    return intval($this->getVar("SELECT FOUND_ROWS();"));
      }
	
      /*
       * Frees MySQL results.
       *
       * @param   mixed   Query ID  
       * @return  null
       */
      private function freeResult($query_id = -1) {
	    if ($query_id != -1) $this->_query_id=$query_id;
	    $this->_query_id->free_result();
      }
	
      /*
       * Fetches array from results.
       *
       * @param   mixed   Query ID  
       * @return  null
       */
      private function fetchArray($query_id =- 1) {
	    if ($query_id!=-1) $this->_query_id=$query_id;
	
	    if (isset($this->_query_id)) $record = $this->_query_id->fetch_assoc(); else
		  throw new STDatabaseException("Invalid query_id: <b>{$this->_query_id}</b>. Records could not be fetched.");
	
	    if ($record) $record = array_map("stripslashes", $record);
			
	    return $record;
      }
      
      /*
       * Runs a query into the driver and returns the result.
       *
       * @param   string  SQL query to execute
       * @return  array Query Result
       */
      public function query($sql) {
	//		echo $sql."<br/>";
	    // Check if Profiler exists, if yes - start logging
	    if (class_exists("STProfiler")) STProfiler::start('Query "'.$sql.'"', "SQL query");
		
	    $result = $this->_connection->query($sql);
	    if (!$result) throw new STDatabaseException("<b>MySQL Query fail:</b> $sql");
	    $this->_affected_rows = @$this->_connection->affected_rows;
	    if (class_exists("STProfiler")) STProfiler::end();
	    return $result;
      }
      
      /*
       * Compiles an update string and runs the query.
       *
       * @param   string  Table name
       * @param   array   Array of key/value pairs to insert
       * @param   string  Where statement
       * @return  mixed   Query result
       */
      public function update($table, $data, $where='1') {
	    $q="UPDATE ".$this->pre.$table." SET ";
    
	    foreach ($data as $key=>$val) {
		  if (strtolower($val) == 'null') $q.= "`$key` = NULL, ";
		  elseif (strtolower($val) == 'now()') $q.= "`$key` = NOW(), ";
		  else $q.= "`$key`='".$this->escape($val)."', ";
	    }
    
	    $q = rtrim($q, ', ') . ' WHERE '.$where.';';

	    return $this->query($q);
      }
	
      /*
       * Compiles an insert string and runs the query.
       *
       * @param   string  Table name
       * @param   array   Array of key/value pairs to insert
       * @return  mixed   Query result
       */
      public function insert($table, $data) {
	    $q="INSERT INTO `".$this->pre.$table."` ";
	    $v=''; $n='';
	
	    foreach($data as $key=>$val) {
		  $n.="`$key`, ";
		  if (strtolower($val) == 'null') $v.="NULL, ";
		  elseif (strtolower($val) == 'now()') $v.="NOW(), ";
		  else $v.= "'".$this->escape($val)."', ";
	    }
	
	    $q .= "(". rtrim($n, ', ') .") VALUES (". rtrim($v, ', ') .");";
	
	    if ($this->query($q)){
		  $this->setLastInsertId($this->_connection->insert_id);
		  return $this->getLastInsertId();
	    }
	    else return false;
      }
	  
	  /*
       * Compiles an insert string and runs the query.
       *
       * @param   string  Table name
       * @param   array   Array of key/value pairs to insert
       * @return  mixed   Query result
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
	
	    if ($this->query($q)){
		  $this->setLastInsertId($this->_connection->insert_id);
		  return $this->getLastInsertId();
	    }
	    else return false;
      }
      
      /*
       * Selects the first found row from the query results.
       *
       * @param   string    Query String
       * @param   boolean   Use internal query cache
       * @return  array     Query Results.
       */
      function selectFirst($query_string, $internal_cache_result = true) {
	    if ($internal_cache_result) {
		  if (isset($this->_cache[md5(trim($query_string))]))
		      return $this->_cache[md5(trim($query_string))];
	    }
		
	    $query_id = $this->query($query_string);
	    $out = $this->fetchArray($query_id);
	    $this->freeResult($query_id);
	    $this->_cache[md5(trim($query_string))] = $out;
	    return $out;
      }
	
      /*
       * Selects the first variable from the first found row from the query results.
       *
       * @param   string    Query String
       * @param   integer   Parameter ID
       * @param   boolean   Use internal query cache
       * @return  array     Query Results.
       */
      function getVar($query_string, $id = 0, $internal_cache_result = true) {
	    if ($internal_cache_result) {
		  if ($this->_cache[md5($query_string)])
			return $this->_cache[md5($query_string)];
	    }
	    $query_id = $this->query($query_string);
	    $out = $this->fetchArray($query_id);
	    $this->freeResult($query_id);
	    $i=0;
	    
	    if ($out)
	    foreach ($out as $i => $res) {
		  if ($i != $id) continue;
		  $this->_cache[md5($query_string)] = $res;
		  return $res;
	    }
      }
	
      /*
       * Selects all data from the query results.
       *
       * @param   string    Query String
       * @param   boolean   Use internal query cache
       * @return  array     Query Results.
       */
      function selectAll($sql, $internal_cache_result = true) {
	    $sql = str_replace("{calc}", "SQL_CALC_FOUND_ROWS", $sql);
	    
	    if ($internal_cache_result)
		  if ($this->_cache[md5($sql)])
			return $this->_cache[md5($sql)];
	    
	    $_query_id = $this->query($sql);
	    $out = array();
    
	    while ($row = $this->fetchArray($_query_id, $sql)){
		  $out[] = $row;
	    }
	    
    
	    $this->freeResult($_query_id);
	    $this->_cache[md5($sql)] = $out;
	    return $out;
      }
      
      /*
       * Sets default charset.
       *
       * @param   string    Charset
       * @return  null
       */
      function setDefaultCharset($charset) {
	    $this->query("SET NAMES '$charset';");
      }
	
      /*
       * Begins transaction.
       *
       * @param   void
       * @return  null
       */
      function startTransaction() {
	    if(!(isset($this) && get_class($this) == __CLASS__))
		  return MySQLDriver::instance()->startTransaction();
	
	    if($this->_is_transactions) {	

		  if($this->_transaction_starts==0) {
			$this->query("SET AUTOCOMMIT=0");
			$this->query("BEGIN");
		  }
	    }
		
	    $this->_transaction_starts++;
      }
      
      /*
       * Checks if a transaction has failed.
       *
       * @param   void
       * @return  boolean
       */
      function hasTransactionFailed() {	
	    if(!(isset($this) && get_class($this) == __CLASS__))
		  return MySQLDriver::instance()->hasTransactionFailed();
		
	    return $this->_has_transaction_failed;
      }

      /*
       * Checks if a transaction has succeeded.
       *
       * @param   void
       * @return  boolean
       */
      function hasTransactionSuccess() {
	    if(!(isset($this) && get_class($this) == __CLASS__))
		  return MySQLDriver::instance()->hasTransactionSuccess();
	
	    return !$this->has_transaction_failed();
      }
	
      /*
       * Completes transaction.
       *
       * @param   void
       * @return  null
       */
      function completeTransaction() {
	    if(!(isset($this) && get_class($this) == __CLASS__))
			return MySQLDriver::instance()->completeTransaction();
		
	    $this->_transaction_starts--;
		
	    if($this->_transaction_starts==0) {
		  if($this->_is_transactions) {
			if(!$this->_has_transaction_failed) {
			      $this->query("COMMIT");
			      $this->query("SET AUTOCOMMIT=1");
			} else 
			      $this->fail_transaction();
		  }
				
		  $this->_has_transaction_failed = false;
	    } 
      }

      /*
       * Fails transaction.
       *
       * @param   void
       * @return  null
       */
      function failTransaction() {
	    if(!(isset($this) && get_class($this) == __CLASS__))
		  return MySQLDriver::instance()->failTransaction();
			
	    $this->_has_transaction_failed = true;
		
	    if($this->_is_transactions) {
		  $this->query("ROLLBACK");
		  $this->query("SET AUTOCOMMIT=1");
	    }
      }
	
      function disableTableKeys($tablename) {
	    return $this->query("ALTER TABLE `".$tablename."` DISABLE KEYS");
      }		
	

      function enableTableKeys($tablename) {
	    return $this->query("ALTER TABLE `".$tablename."` ENABLE KEYS");
      }
		

      function enableUniqueCheck() {
	    return $this->query("SET UNIQUE_CHECKS=1");
      }

      function disableUniqueCheck() {
	    return $this->query("SET UNIQUE_CHECKS=0");
      }			
      
      /*
       * Truncates a table.
       *
       * @param   string   Table name
       * @return  mixed    Query results
       */
      function truncateTable($tablename) {
	    return $this->query("TRUNCATE `".$tablename."`");
      }
	
      public function disableForeignKeys() {
	    if(!(isset($this) && get_class($this) == __CLASS__))
		  return MySQLDriver::instance()->disableForeignKeys();		
	
	    return $this->query("SET FOREIGN_KEY_CHECKS=0");
      }
	
      public function enableForeignKeys() {
	    if(!(isset($this) && get_class($this) == __CLASS__))
		  return MySQLDriver::instance()->enableForeignKeys();		
	
	    return $this->query("SET FOREIGN_KEY_CHECKS=1");
      }
      
      /*
      * See if a table exists in the database.
      *
      * @param   string   Table name
      * @return  boolean
      */
      public function doesTableExist($tablename) {							
	    return (bool) $this->selectFirst("SHOW TABLES LIKE '".$tablename."'");			
      }		
	
      /*
      * See if a database exists in the schema.
      *
      * @param   string   Table name
      * @return  boolean
      */
      public function doesDatabaseExist($db_name) {
	    $sql = "SELECT count(*) FROM `information_schema`.`schemata` WHERE `schema_name` = '".$db_name."'";									
	    return ($this->getVar($sql));
      }
      
      /*
       * Creates a database.
       *
       * @param   string   Database name
       * @return  mixed    Query results
       */
      public function createDatabase($db_name) {
	    return $this->query("CREATE DATABASE ".$db_name);
      }
	
      public function getMySQLImportCommand($db_host, $db_user, $db_pass, $db_name, $sql_file) {
	    $cmd = "mysql -h%s -u%s -p%s %s < %s";
	    return sprintf($cmd,$db_host,$db_user,$db_pass,$db_name,$sql_file);
      }	
	
      /*
       * Drops a database.
       *
       * @param   string   Database name
       * @return  mixed    Query results
       */
      public function dropDatabase($db_name) {
	    return $this->query("DROP DATABASE ".$db_name);
      }
	
      /*
       * Drops a user.
       *
       * @param   string   Username
       * @param   string   Host
       * @return  mixed    Query results
       */
      public function dropUser($db_user, $db_host) {
	    return $this->query("DROP USER `".$db_user."`@`".$db_host."`");
      }		
	
      public function createUser($username, $password, $database, $host, $select=true, $update=true, $delete=true, $insert=true) {	
	    $privs = array();
	    if ($select) $privs[] = "SELECT";
	    if ($update) $privs[] = "UPDATE";
	    if ($delete) $privs[] = "DELETE";			
	    if ($insert) $privs[] = "INSERT";
		
	    $priv_str = implode(",",$privs);
		
	    $sql = "GRANT ".$priv_str." ON ".$database.".* TO '".$username."'@'".$host."' IDENTIFIED BY '".$password."';";
	
	    return $this->query($sql);		
      }
	
      public function doesUserExist($username, $host) {
	    $sql = "SELECT count(*) FROM `mysql`.`user` WHERE `User` = '".$username."' AND `Host` = '".$host."'";			
		
	    return $this->getVar($sql)>0;
      }
	
      public function getTables() {			
	    $tables = $this->select("SHOW TABLES");					
	    return $tables;		
      }
	
      public function getTableFields($tablename) {
	    return $this->select("SHOW FULL FIELDS FROM `".$tablename."`");		
      }

      public static function getDate($time = null) {
	    $time = func_num_args()==0 ? time() : $time;
	    return self::getFormattedTime("Y-m-d",$time);
      }

      public static function getDateTime($time = null) {
	    $time = func_num_args()==0 ? time() : $time;
	    return self::getFormattedTime("Y-m-d H:i:s", $time);
      }

      public static function getFormattedTime($format, $time) {
	    if(!is_numeric($time))
		  return null;
	    elseif($time<=0)
		  return null;
	    
	    return date($format,$time);
      }
      
      public function getConnectionError() {
	    return $this->_connection->error;
      }
      
} 

?>