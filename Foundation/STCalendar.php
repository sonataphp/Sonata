<?php
//  STCalendar.php
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

class STCalendar {
    
    private static $defaultTimezone;
    
    final private function __construct() {}
    final private function __clone() {}
    
    public static function setDefaultTimezone($timezone) {
        self::$defaultTimezone = $timezone;
        date_default_timezone_set($timezone);
        putenv("TZ=".$timezone);
    }
    
    public static function switchToTimezone($timezone) {
        putenv("TZ=".$timezone);
    }
    
    public static function defaultTimezone() {
        if (!self::$defaultTimezone)
            throw new Exception('Default timezone is not defined.');
        putenv("TZ=".self::$defaultTimezone);
    }
    
}

?>