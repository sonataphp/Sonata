<?
//  STRequest.php
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

class STRequest extends STObject {
    
    protected static $_data = array();
    protected static $_post = array();
    protected static $_get = array();
    protected static $_cookie = array();
    
    final private function __construct() {}
    final private function __clone() {}
    
    public static function init() {
        self::$_data = array_merge(self::$_data, $_REQUEST);
        self::$_get = array_merge(self::$_get, $_GET);
        self::$_post = array_merge(self::$_post, $_POST);
        self::$_cookie = array_merge(self::$_cookie, $_COOKIE);
        self::_clean();
    }

    
    protected static function _clean() {
        self::$_data = self::_stripSlashes(self::$_data);
        self::$_post = self::_stripSlashes(self::$_post);
        self::$_get = self::_stripSlashes(self::$_get);
        $_GET = self::_stripSlashes($_GET);
        $_POST = self::_stripSlashes($_POST);
    }
    
    public static function getParams() {
        return STArray(self::$_get)->toObject();
    }
    
    public static function postParams() {
        return STArray(self::$_post)->toObject();
    }
    
    public static function cookieParams() {
        return STArray(self::$_cookie)->toObject();
    }
    
    /**
     * Strip slashes
     *
     * @param mixed $value
     * @return array
     */
    protected static function _stripSlashes($value) {
        if(is_array($value)) {
                return array_map(array(self,'_stripSlashes'), $value);
            } else {
                return stripslashes($value);
            }
    }

    public static function isPost(){
            return $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SERVER['CONTENT_LENGTH']) && $_SERVER['CONTENT_LENGTH'];
    }

    public static function isAjax() {
        return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'));
    }
    
}

?>