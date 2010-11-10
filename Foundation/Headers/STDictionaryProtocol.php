<?
//
//  STDictionaryProtocol.php
//  Sonata/Foundation
//
//  Created by Roman Efimov on 6/10/2010.
//  Copyright Roman Efimov 2010. All rights reserved.
//

interface STDictionaryProtocol {
    
    public function initWithContentsOfFile($file);
    public function initWithContentsOfString($string);
    public function allValues();
    
}

?>