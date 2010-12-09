<?php
//  STDatabaseException.php
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

class STDatabaseException extends ErrorException {

      protected $code = E_DATABASE_ERROR;
      
      /*
       * Sets the code for a Database exception.
       */
      public function __construct($message) {
            $this->message = $message."<br/><br/>".DB::getConnectionError();
      }

}

?>