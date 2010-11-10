<?

define("STUserDefaultsStorageDB", 1);
define("STUserDefaultsStorageFileSystem", 2);

abstract class STUserDefaults extends STObject {
    
    private static $defaults = array();
    private $storageEngine;
    
    public static function standardUserDefaults($userId) {
        if (!is_int($userId)) throw new Exception("userId must be integer");
        $class = get_called_class();

        if(!self::$defaults[$userId]) {
            self::$defaults[$userId] = new $class();
        }
        return self::$defaults[$userId];
    }
    
    public static function resetStandardUserDefaults() {
        
    }
    
    public function setStorageEngine($storageEngine) {
        
    }
}

?>