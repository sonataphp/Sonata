<?
//  STObject.php
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
// =========================== Description ==========================
//
// STObject is the root class of most Sonata class hierarchies.
// Through STObject, objects inherit a basic interface to the 
// system and the ability to behave as Sonata objects.
//

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
     *                    identical to $className, otherwise false.
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
    
    private function performSelector($function, $argv) {
        $argv = (array) func_get_args();
        $function = $argv[0];
        array_shift($argv);
        call_user_func_array($this->className()."::".$function, $argv);
    }
    
}

?>