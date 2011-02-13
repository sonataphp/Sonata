<?php
//  UIApplication.php
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

require_once "Headers/UIApplicationSettings.php";

if (!defined("UIApplication404Header")) define("UIApplication404Header", "HTTP/1.0 404 Not Found");

class UIApplication extends STObject {
    
    private static $delegate = null;
    public $arguments;
    /*
     * @var UIApplicationSettings
     */
    public $settings;
    public $hasErrors = false;
	private $isSSL = false;
    private $router;
	
    final private function __clone() { }
    
    protected function __construct() {
		$this->settings = new UIApplicationSettings;
    }
    
    public function __get($property) {
        if ($property == 'className') return $this->className();
		$method = "get".ucwords($property);
		if (method_exists($this, $method)) return $this->$method();
    }
	    
    public function __set($property, $value) {
		$method = "set".ucwords($property);
		if (method_exists($this, $method)) return $this->$method($value);
    }
    
    public function setDelegate($delegate) {
        self::$delegate = $delegate;
    }
    
    public function getDelegate() {
        return self::$delegate;
    }
	
	public function setRouter($router) {
		$this->router = $router;
	}
	
	public function getRouter() {
		return $this->router;
	}
	
	private function getLocation() {
		$v=str_replace("/","", UIApplicationUrl());
		$v=str_replace("http:", "http://", $v);
		$v=str_replace("https:", "https://", $v);
		$v=$v.$_SERVER['REQUEST_URI'];
		$v=explode("?", $v);
		$v=$v[0];
		return $v;
	}
	
	private function setLocation($value) {
		$newLocation = (((strpos($value, "http://") !== FALSE) || (strpos($value, "https://") !== FALSE) )
							 ? $value
							 : UIApplicationUrl().$value);
		header("Location: ".$newLocation);
		die();
	}
    
    public function applicationRun() {
        $this->applicationDidFinishLaunching();
        $this->applicationWillTerminate();
    }
    
    // Getting the Application Instance
    
    public static function sharedApplication() {
        $class = get_called_class();

        if(!self::$delegate) {
            self::setDelegate(new $class());
        }
        return self::getDelegate();
    }
	
	public function isSSL() {
		return $this->isSSL;
	}
	
	public function recreateGetValues() {
		if (!$_GET) return '';
		$result = '';
		foreach ($_GET as $key => $param) {
			$result[] = $key."=".$param;
		}
		return implode("&", $result);
	}
	
	public function forceUsingSSL() {
		$this->isSSL = true;
		$location = UIApplicationLocation();
		if (!$_SERVER['HTTPS']) {
			$get = $this->recreateGetValues();
			if ($get) $get = "?".$get;
			UIApplicationSetLocation(UIApplicationSetSSLProtocol(UIApplicationLocation()).$get);
		}
	}
	
	public function sendHeader($header) {
		if (!is_array($header) && !is_string($header))
			throw new Exception("Unknown type of header is being attempted to send.");
		
		if (is_string($header)) 
			header($header);
		
		if (is_array($header))
			foreach ($header as $headerElement) {
				header($headerElement);
			}
	}
    
    public function settingsFromObject(STDictionary $dictionary) {
		$this->settings->applicationTitle = $dictionary['Application']['Title'];
		$this->settings->applicationEnvironment = $dictionary['Application']['Environment'];
		$this->settings->iconFile = $dictionary['Application']['Icon file'];
		$this->settings->timeZone = $dictionary['Application']['Timezone'];
		$envOptions = new STArray($dictionary['Configurations'][$this->settings->applicationEnvironment]);
		$options = $envOptions->toObject();
		$this->settings->environment = $envOptions->toObject();
		$this->settings->paths = new UIApplicationSettingsPaths();
		$this->settings->paths->javascripts = $this->settings->environment->Resources_URL."static/js/";
		$this->settings->paths->styles = $this->settings->environment->Resources_URL."static/css/";
		$this->settings->paths->images = $this->settings->environment->Resources_URL."static/images/";
		STCalendar::setDefaultTimezone($this->settings->timeZone);
    }
    
    
    /* Notifications */
    
    public function applicationTerminate() {
        CFAutoLoadUnregister("STAutoload", "autoLoadWithClass");
        CFErrorHandlingRestore();
        CFExceptionHandlingRestore();
    }
    
    public function applicationDidFinishLaunching() {
        
    }
    
    public function applicationWillTerminate() {
        UIApplication::sharedApplication()->applicationTerminate();
    }
    
    public function applicationWillRun() {
        
    }
    
    public function didReceiveCriticalError($notification) {
		$this->hasErrors = true;
		$this->applicationWillTerminate();
    }
    
    public function didReceiveException($notification) {
		$this->hasErrors = true;
		$this->applicationWillTerminate();
    }
    
	public static function handleRoutesWithRouter($router) {
        $router::execute();
        $controller = $router::controller();
		UIApplication::sharedApplication()->setRouter($router);
        if (!$controller)
            throw new UIViewControllerException(__("Controller was not specified"));
        $controller = $controller."ViewController";
        require_once $controller.".php";
        $action = $router::action()."Action";
        $params = $router::params();
        $c = new $controller();
		if (isset($params['__secure'])) {
			UIApplication::sharedApplication()->forceUsingSSL();
			unset($params['__secure']);
		}
        $c->params = $params;
		$c->action = $router::action();
		$c->controllerName = $router::controller();
        if (!method_exists($c, $action))
            throw new UIViewControllerException(__(sprintf("Action method '%s' is not present in controller '%s'", $action, $controller)));
		$c->init();
		$c->bindMethod("__execute", "before");
		$c->$action();
		$c->bindMethod("__execute", "after");
    }
}

function UIApplicationCheckProtocol($path) {
	if (UIApplication::sharedApplication()->isSSL()) $path = UIApplicationSetSSLProtocol($path);
	return $path;
}

function UIApplicationSetSSLProtocol($path) {
	return str_replace("http://", "https://", $path);
}

function UIApplicationUrl() {
	return UIApplication::sharedApplication()->settings->environment->Application_URL;
}

function UIApplicationTitle() {
	return UIApplication::sharedApplication()->settings->applicationTitle;
}

function UIApplicationLocation() {
	return UIApplication::sharedApplication()->location;
}

function UIApplicationSetLocation($location = '') {
	UIApplication::sharedApplication()->location = $location;
}

function UIApplicationRefreshLocation() {
	UIApplicationSetLocation(UIApplicationLocation());
}

function UIApplicationSecureLocation($location) {
    UIApplicationSetLocation(UIApplicationSetSSLProtocol(UIApplicationUrl().$location));
}


function linkTo($controllerAction, $params = array()) {
	$maybeAlias = false;
	if (strpos(".", $controllerAction) == NULL) {
		$maybeAlias = true;
	}
	$controllerAction = explode(".", $controllerAction);
	$controller = $controllerAction[0];
	$action = $controllerAction[1];
	$action = $action ? $action : 'index';
	$router = UIApplication::sharedApplication()->getRouter();
	$routes = $router::routes();
	$totalMatches = 0;
	$subdomain = '';
	foreach ($routes as $subdomainName => $subdomains)
	foreach ($subdomains as $path => $route) {
		$c = $route->target['controller'];
		$a = $route->target['action'];
		$a = $a ? $a : 'index';
		if (($c == $controller && $action == $a) || ($maybeAlias && $route->alias == $controller)) {
			if ($params) {
				$matchesCount = 0;
				foreach ($params as $key => $value) {
					if (strpos($path, ":".$key) != NULL)
						$matchesCount++;
				}
				if ($matchesCount > $totalMatches) {
					$totalMatches = $matchesCount;
					$thePath = $path;
					$theRoute = $route;
					$subdomain = $subdomainName;
				}
			} else {
				$thePath = $path;
				$theRoute = $route;
				$subdomain = $subdomainName;
				break;
			}
		}
	}
	if (!$thePath) throw new Exception("Can't find matching controller '$controller' for action '$action'");
	$prefix = ($theRoute->params['__secure']) ? UIApplicationSetSSLProtocol(UIApplicationUrl()) : UIApplicationUrl();
	if ($subdomain != '' && $subdomain != 'www') {
		$prefix = $subdomain.".".STRegistry::get("Base_Domain");
		if ($theRoute->params['__secure']) $prefix = "https://".$prefix."/"; else $prefix = "http://".$prefix."/";
	}
	if (!$params) {
		$thePath = substr($thePath, 1, strlen($thePath));
		$thePath = $prefix.$thePath;
		return $thePath;
	}
	foreach ($params as $key => $value) {
		$thePath = str_replace(":".$key, html($value), $thePath);
	}
	$thePath = substr($thePath, 1, strlen($thePath));
	$thePath = $prefix.$thePath;
	return $thePath;
}


?>