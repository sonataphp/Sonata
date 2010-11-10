<?
//
//  STCalendar.php
//  Sonata/Foundation
//
//  Created by Roman Efimov on 6/10/2010.
//

class STCalendar {
    
    private static $defaultTimezone;
    
    final private function __construct() {}
    final private function __clone() {}
    
    public static function setDefaultTimezone($timezone) {
        self::$defaultTimezone = $timezone;
        putenv("TZ=".$timezone);
    }
    
    public static function switchToTimezone($timezone) {
        putenv("TZ=".$timezone);
    }
    
    public static function defaultTimezone() {
        putenv("TZ=".self::$defaultTimezone);
    }
    
}

?>