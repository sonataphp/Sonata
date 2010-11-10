<?
//
//  STBuffer.php
//  Sonata/Foundation
//
//  Created by Roman Efimov on 6/10/2010.
//

class STBuffer extends STObject {
    
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