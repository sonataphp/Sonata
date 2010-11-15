<?
//  UIApplicationError.php
//  Sonata/UI
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

class UIApplicationError extends Exception {
	
	public function __construct($delegate, $action = 'indexAction', $errorCode = null, $errorMessage = '') {
		$controller = new $delegate;
		$controller->$action();
		if ($errorCode) STProfiler::exception($errorCode.' Exception Caught');
        
        if ($errorMessage) $errorMessage = '<br/><br/><b>'.$errorMessage.'</b>';
        
        parent::__construct("UIApplicationError: ".$errorCode.", delegateTo: ".$delegate.", action: ".$action.$errorMessage, $code);
	}
	
}

class UI404Error extends UIApplicationError {
	
    public function __construct($e = '') {
        parent::__construct("E404ViewController", "indexAction", 404, $e);
    }
	
}

?>