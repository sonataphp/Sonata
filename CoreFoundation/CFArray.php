<?
//
//  CFArray.php
//  Sonata/CoreFoundation
//
//  Created by Roman Efimov on 6/10/2010.
//

class CFArray {
	
	final private function __construct() {}
    final private function __clone() {}

    public static function arrayToObject($array) {
		$object = new stdClass();
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

?>