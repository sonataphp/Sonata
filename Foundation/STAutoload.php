<?
//
//  STAutoload.php
//  Sonata/Foundation
//
//  Created by Roman Efimov on 6/10/2010.
//


class STAutoload extends STObject {
    
    final private function __construct() {}
    final private function __clone() {}
    
    public static function autoLoadWithClass($class) {
        include $class.".php";
    }
    
}

CFAutoLoadRegister('STAutoload', 'autoLoadWithClass');
?>