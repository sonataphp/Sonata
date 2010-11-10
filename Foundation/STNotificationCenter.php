<?
//
//  STNotificationCenter.php
//  Sonata/Foundation
//
//  Created by Roman Efimov on 6/10/2010.
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