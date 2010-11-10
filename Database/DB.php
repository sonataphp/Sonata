<?
//
//  DB.php
//  Sonata/Database
//
//  Created by Roman Efimov on 6/10/2010.
//

define("IGNORE", "__IGNORE");

class DB extends STObject {
    
    private static $driver;
    
    final private function __construct() { }
    final private function __clone() {}
    
    private static $_outputFormatObject = true;
    
    private static function preprocessResults($results) {
        return $results;
    }
    
    public static function useDriver($driver) {
        $driver = $driver.'Driver';
        self::$driver = new $driver();
        return $this;
    }
    
    public static function returnsObject($yes = true) {
        self::$_outputFormatObject = $yes;
    }
    
    public static function defaultConnectionFromObject($data) {
        $driver = self::$driver;
        $driver::setDefaultDBConnection($driver::getDBConnection($data->host,
                                                                 $data->user,
                                                                 $data->password,
                                                                 $data->database));
    }
    
    public static function defaultConnection($host, $user, $password, $database) {
        $driver = self::$driver;
        $driver::setDefaultDBConnection($driver::getDBConnection($host,
                                                                 $user,
                                                                 $password,
                                                                 $database));
    }
    
    public static function escape($string) {
        $driver = self::$driver;
        return $driver::instance()->escape($string);
    }
    
    public static function getLastInsertId() {
        $driver = self::$driver;
        return $driver::instance()->getLastInsertId();
    }
    
    public static function foundRows() {
        $driver = self::$driver;
        return $driver::instance()->foundRows();
    }
    
    public static function query($sql) {
        $driver = self::$driver;
        return $driver::instance()->query($sql);
    }
    
    public static function insert($table, $data) {
        $driver = self::$driver;
        return $driver::instance()->insert($table, $data);
    }
    
    public static function insertOnDuplicate($table, $data, $duplicate) {
        $driver = self::$driver;
        return $driver::instance()->insertOnDuplicate($table, $data, $duplicate);
    }
    
    public static function update($table, $data, $where = '1') {
        $driver = self::$driver;
        return $driver::instance()->update($table, $data, $where);
    }
    
    public static function all($sql, $internalCacheResults = true) {
        $driver = self::$driver;
        $data = $driver::instance()->selectAll($sql, $internalCacheResults);
        if (!$data) return null;
        return self::preprocessResults($data);
    }
    
    public static function first($sql, $internalCacheResults = true) {
        $driver = self::$driver;
        $data = $driver::instance()->selectFirst($sql, $internalCacheResults);
        if (!$data) return null;
        return self::preprocessResults($data);
    }
    
    public static function getVar($sql, $id = 0,  $internalCacheResults = true) {
        $driver = self::$driver;
        $data = $driver::instance()->getVar($sql, $id, $internalCacheResults);
        return $data;
    }
    
    public static function charset($charset = '') {
        $driver = self::$driver;
        return $driver::instance()->setDefaultCharset($charset);
    }
    
    public static function charsetUTF8() {
        $driver = self::$driver;
        return $driver::instance()->setDefaultCharset('utf-8');
    }
    
    public static function startTransaction() {
        $driver = self::$driver;
        return $driver::instance()->startTransaction();
    }
    
    public static function hasTransactionFailed() {
        $driver = self::$driver;
        return $driver::instance()->hasTransactionFailed();
    }
    
    public static function hasTransactionSuccess() {
        $driver = self::$driver;
        return $driver::instance()->hasTransactionSuccess();
    }
    
    public static function completeTransaction() {
        $driver = self::$driver;
        return $driver::instance()->completeTransaction();
    }
    
    public static function failTransaction() {
        $driver = self::$driver;
        return $driver::instance()->failTransaction();
    }
    
    public static function disableTableKeys($table) {
        $driver = self::$driver;
        return $driver::instance()->disableTableKeys($table);
    }
    
    public static function enableTableKeys($table) {
        $driver = self::$driver;
        return $driver::instance()->enableTableKeys($table);
    }
    
    public static function enableUniqueCheck() {
        $driver = self::$driver;
        return $driver::instance()->enableUniqueCheck();
    }
    
    public static function disableUniqueCheck() {
        $driver = self::$driver;
        return $driver::instance()->disableUniqueCheck();
    }
    
    public static function truncateTable($table) {
        $driver = self::$driver;
        return $driver::instance()->truncateTable($table);
    }
    
    public static function disableForeignKeys() {
        $driver = self::$driver;
        return $driver::instance()->disableUniqueCheck();
    }
    
    public static function enableForeignKeys() {
        $driver = self::$driver;
        return $driver::instance()->enableForeignKeys();
    }
    
    public static function doesTableExist($table) {
        $driver = self::$driver;
        return $driver::instance()->doesTableExist($table);
    }
    
    public static function doesDatabaseExist($table) {
        $driver = self::$driver;
        return $driver::instance()->doesDatabaseExist($table);
    }
    
    public static function createDatabase($db) {
        $driver = self::$driver;
        return $driver::instance()->createDatabase($db);
    }
    
    public static function dropDatabase($db) {
        $driver = self::$driver;
        return $driver::instance()->dropDatabase($db);
    }
    
    public static function dropUser($user) {
        $driver = self::$driver;
        return $driver::instance()->dropUser($user);
    }
    
    public static function createUser($username, $password, $database, $host, $select = true, $update = true, $delete = true, $insert = true) {
        $driver = self::$driver;
        return $driver::instance()->createUser($username, $password, $database, $host, $select, $update, $delete, $insert);
    }
    
    public static function doesUserExist($user, $host) {
        $driver = self::$driver;
        return $driver::instance()->dropUser($user, $host);
    }
    
    public static function getTables() {
        $driver = self::$driver;
        return $driver::instance()->getTables();
    }
    
    public static function getTableFields($table) {
        $driver = self::$driver;
        return $driver::instance()->getTableFields($table);
    }
    
    public static function getDate($time = null) {
        $driver = self::$driver;
        return $driver::instance()->getDate($time = null);
    }
    
    public static function getDateTime($time = null) {
        $driver = self::$driver;
        return $driver::instance()->getDateTime($time = null);
    }
    
    public static function getFormattedTime($format, $time) {
        $driver = self::$driver;
        return $driver::instance()->getFormattedTime($format, $time);
    }
    
    public static function getConnectionError() {
        $driver = self::$driver;
        return $driver::instance()->getConnectionError();
    }
}

?>