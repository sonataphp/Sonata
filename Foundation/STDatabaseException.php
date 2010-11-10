<?
//
//  STAutoload.php
//  Sonata/Foundation
//
//  Created by Roman Efimov on 6/10/2010.
//

/*
 * Sets the code for a Database exception.
 */
class STDatabaseException extends ErrorException {

      protected $code = E_DATABASE_ERROR;
      
      public function __construct($message) {
            $this->message = $message."<br/><br/>".DB::getConnectionError();
      }

}

?>