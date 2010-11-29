<?
//  STBuffer.php
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

class STBuffer {
    
    protected static $buffer = array();
    
    final private function __construct() {}
    final private function __clone() {}
    
    public static function writeToBuffer($bufferName, $data) {
        self::$buffer[$bufferName] = $data;
    }
    
    public static function bufferFromFileAtPath($path, $bufferName) {
        $file = new STFile($path);
        self::$buffer[$bufferName] = $file->read();
    }
    
    public static function cleanBuffer($bufferName) {
        unset(self::$buffer[$bufferName]);
    }
    
    public static function outputBufferToScreen($bufferName) {
        echo self::$buffer[$bufferName];
    }
    
    public static function outputBufferToString($bufferName) {
        return strval(self::$buffer[$bufferName]);
    }
    
    public static function outputBufferToFileAtPath($path, $bufferName, $append = false) {
        $file = new STFile($path);
        $file->data = self::$buffer[$bufferName];
        if ($append)
            $file->append(); else
                $file->save();
    }
}

?>