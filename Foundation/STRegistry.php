<?
//
//  STRegistry.php
//  Sonata/Foundation
//
//  Created by Roman Efimov on 6/10/2010.
//

class STRegistry extends STObject {
    
    private static $_settings;
    
    final private function __construct() {}
    final private function __clone() {}
    
    public static function settingsFromObject($settings) {
        self::$_settings = (array)($settings);
        STCookie::initWithRegistry();
    }
    
    public static function get($value) {
        return self::$_settings[$value];
    }
    
    public static function set($key, $value) {
        self::$_settings[$key] = $value;
    }
}

class Registry extends STRegistry {}

?>