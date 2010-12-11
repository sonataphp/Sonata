<?php
//  UIFlashMessage.php
//  Sonata/UI
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

define ("NOTICE", 1);
define ("UIFlashNotice", 1);
define ("UIFlashError", 2);

class UIFlashMessage {
        
        final private function __construct() {}
        final private function __clone() {}
        
        public static function set($msg, $type = UIFlashNotice) {
                $_SESSION['flash'.$type] = $msg;
        }
        
        public static function clear($type) {
                unset($_SESSION['flash'.$type]);
        }
        
        public static function exists($type) {
                return isset($_SESSION['flash'.$type]);
        }
        
        public static function get($type, $before = '', $after = '') {
                if ($_SESSION['flash'.$type]) {
                        $msg = $_SESSION['flash'.$type];
                        unset($_SESSION['flash'.$type]);
                        return $before.$msg.$after;
                }
        }
}

?>