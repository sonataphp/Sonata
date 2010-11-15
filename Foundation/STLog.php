<?
//  STLog.php
//  Sonata/Foundation
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