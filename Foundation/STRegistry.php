<?
//  STRegistry.php
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

class STRegistry {
    
    private static $_settings;
    
    final private function __construct() {}
    final private function __clone() {}
    
    public static function settingsFromObject($settings) {
        self::$_settings = (array)($settings);
        STCookie::initWithRegistry();
    }
    
    public static function get($key) {
        return self::$_settings[$key];
    }
    
    public static function set($key, $value) {
        self::$_settings[$key] = $value;
    }
    
    public static function keyExists($key) {
        return isset(self::$_settings[$key]);
    }
}

class Registry extends STRegistry {}

?>