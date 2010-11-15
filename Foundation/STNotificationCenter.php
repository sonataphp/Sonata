<?
//  STNotificationCenter.php
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

class STNotificationCenter {
    
	final private function __construct() {}
	final private function __clone() {}
	
    protected static $observersList; 
    
    public static function addObserver($observerName, $observerClassName, $observerMethod) {
        self::$observersList[$observerName] = array('className' => $observerClassName, 'method' => $observerMethod);
    }
    
    public static function removeObserver($observerName) {
        unset(self::$observersList[$observerName]);
    }
    
    public static function postNotification($observerName, $notificationName = '', $object = null, $userInfo = null) {

        $notification = new STNotification();
        $notification->name = $notificationName;
        $notification->object = $object;
        $notification->userInfo = $userInfo;
        $observer = self::$observersList[$observerName];
        if (!$observer) trigger_error(sprintf(__('Observer "%s" not found'), $observerName));
        if (!class_exists($observer['className'])) return false;
        $object = new $observer['className'];
        if (!method_exists($observer['className'], $observer['method'])) return false;
        $object->$observer['method']($notification);
        return true;
    }

}

?>