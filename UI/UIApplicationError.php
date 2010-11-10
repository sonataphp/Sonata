<?


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