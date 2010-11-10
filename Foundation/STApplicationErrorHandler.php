<?
//
//  STApplicationErrorHandler.php
//  Sonata/Foundation
//
//  Created by Roman Efimov on 6/10/2010.
//

class STApplicationErrorHandler {

    public function criticalException($notification) {
		UIApplication::sharedApplication()->didReceiveCriticalError($notification);
    }
    
    public function standardException($notification) {
		UIApplication::sharedApplication()->didReceiveException($notification);
    }
}

?>