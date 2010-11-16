<?
//  CFArray.php
//  Sonata/CoreFoundation
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

class CFArray {
	
	final private function __construct() {}
    final private function __clone() {}

	/**
	 * Converts array to an object of given class
	 * 
	 * @param array $array array to convert.
	 * @param string $class output class
	 * @return object of $class class
	 */
    public static function arrayToObject($array, $class = 'stdClass') {
		$object = new $class();
		if (count($array) > 0) {
		   foreach ($array as $name=>$value) {
			  $name = str_replace(" ", "_", $name);
			  if (!empty($name)) {
					if (is_array($value)) $value = self::arrayToObject($value);
					$object->$name = $value;
			  }
		   }
		}
		return $object;
    }
}

// ====================== Addition to PHP array_ functions ===================

/*
 * Splits array
 *
 * @param array $array input array
 * @return array splitted array
 */
function array_split($array) {           
    $end=count($array);
    $half = ($end % 2 )?  ceil($end/2): $end/2;
    return array(array_slice($array,0,$half),array_slice($array,$half));
}

/*
 * Remove empty values from array
 *
 * @param array $array input array
 * @return array updated array
 */
function array_remove_empty($arr){
	$narr = array();
	while(list($key, $val) = each($arr)) {
	    if (is_array($val)){
            $val = array_remove_empty($val);
            if (count($val)!=0) {
                 $narr[$key] = $val;
            }
	    } else {
            if (trim($val) != ""){
               $narr[$key] = $val;
            }
	    }
	}
	unset($arr); 
	return $narr; 
}

?>