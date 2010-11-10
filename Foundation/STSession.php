<?
//
//  STSession.php
//  Sonata/Foundation
//
//  Created by Roman Efimov on 6/10/2010.
//

class STSession extends STObject {
    
    final private function __construct() {}
    final private function __clone() {}
    
    private static $_session;
    
    public static function create() {
        return session_start();
    }
    
    public static function destroy() {
        session_destroy();
    }
    
    public static function set($name, $value) {
		$_SESSION[$name] = $value;
	}

	public static function get($name) {
		if(isset($_SESSION[$name]))
			return $_SESSION[$name];
		else
			return false;
	}

	public static function remove($name) {
		unset($_SESSION[$name]);
	}
    
    public static function getId() { 
        return(session_id()); 
    }
    
    public static function setId($id) { 
        session_id($id);
    }
    
    public static function regenerateId() {
        session_regenerate_id(true);
    }
    
}

?>