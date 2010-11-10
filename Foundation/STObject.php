<?
//
//  STObject.php
//  Sonata/Foundation
//
//  Created by Roman Efimov on 6/10/2010.
//

include "Headers/STObjectProtocol.php";
    
/*
 * STObject is the root class of most Sonata class hierarchies.
 * Through STObject, objects inherit a basic interface to the runtime
 * system and the ability to behave as Sonata objects.
 */

class STObject {
    
    public $tag;
    
    public function __get($property) {
        if ($property == 'className') return $this->className();
		$method = "get".ucwords($property);
		if (method_exists($this, $method)) return $this->$method();
    }
	    
    public function __set($property, $value) {
	$method = "set".ucwords($property);
	if (method_exists($this, $method)) return $this->$method($value);
    }
    
    # Identifying Classes 
    
    /*
     * Returns the class name
     * 
     * @return  string   Class Name
     */
    public function className() {
        return get_called_class();
    }
    
    /*
     * Returns the parent class name
     * 
     * @return  string   Parent Class Name
     */
    public function parentClassName() {
        return get_parent_class($this);
    }
    
    /*
     * Returns a Boolean value that indicates whether the receiver is an
     * instance of a given class.
     * 
     * @param   string    Class Name
     * @return  boolean   true if the receiver is an instance of $class_name,
     *                    otherwise false.
     */
    public function isMemberOfClass($className) {
        return (bool) ($this->className() == $className);
    }
    
    /*
     * Returns a Boolean value that indicates whether the receiving class is a
     * subclass of, or identical to, a given class.
     * 
     * @param   string    Class Name
     * @return  boolean   true if the receiving class is a subclass ofÑor
     *                    identical to $class_name, otherwise false.
     */
    public function isSubclassOfClass($className) {
        return (bool) is_subclass_of($this, $className);
    }
    
    /*
     * Returns a Boolean value that indicates whether the receiver is an
     * instance of given class or an instance of any class that
     * inherits from that class.
     * 
     * @param   string   Class Name
     * @return  boolean  
     */
    public function isKindOfClass($className) {
        return (bool) ($this instanceof $className);
    }
    
    # Sending Messages
    
    /*
     * This method is in Beta
     */
    public function performSelector($function, $argv) {
        $argv = (array) func_get_args();
        $function = $argv[0];
        array_shift($argv);
        call_user_func_array($this->className()."::".$function, $argv);
    }
    
}

?>