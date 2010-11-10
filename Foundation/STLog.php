<?
//
//  STLog.php
//  Sonata/Foundation
//
//  Created by Roman Efimov on 6/10/2010.
//

define("STLogStorageFileSystem", 0);
define("STLogStorageDatabase", 1);

class STLog extends STObject {
  
    private static $_storage = STLogStorageFileSystem;
    private static $_filepath = '';
    private static $_isEnabled = false;
  
    public static function enableWithStorage($storage) {
        self::$_storage = $storage;
        self::enable();
    }
    
    public static function enable() {
        self::$_isEnabled = true;
    }
    
    public static function disable() {
        self::$_isEnabled = false;
    }
    
    public static function setFilePath($filePath = '/tmp/Sonata.log') {
        self::$_filepath = $filePath;
    }
    
    public static function write($data) {
        if (!self::$_isEnabled) return;
        if (self::$_storage == STLogStorageFileSystem)
          self::writeToFile($data);
          
        if (self::$_storage == STLogStorageDatabase)
          self::writeToDatabase($data);
    }
    
    private static function writeToFile($data) {
        $data = date("Y-m-d H:i:s")."      ".$data."\r\n";
        $file = new STFile(self::$_filepath);
        echo self::$_filepath;
        $file->data = $data;
        $file->append();
    }
    
    private static function writeToDatabase($data) {
        $tableData = array("date" => date("Y-m-d H:i:s"), "message" => $data);
        STProfiler::disable();
        DB::insert("log", $tableData);
        STProfiler::resume();
    }
  
}

?>