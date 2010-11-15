<?
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
            self::$delegate = new $class();
        }
        return self::$delegate;
    }
	
	public function isSSL() {
		return $this->isSSL;
	}
	
	public function forceUsingSSL() {
		$this->isSSL = true;
	}
	
	public function sendHeader($header) {
		header($header);
	}
    
    public function settingsFromObject($data) {
		$this->settings->applicationTitle = $data['Application']['Title'];
		$this->settings->applicationEnvironment = $data['Application']['Environment'];
		$this->settings->iconFile = $data['Application']['Icon file'];
		$this->settings->timeZone = $data['Application']['Timezone'];
		$envOptions = new STArray($data['Configurations'][$this->settings->applicationEnvironment]);
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
        if (!$controller)
            throw new UIViewControllerException(__("Controller was not specified"));
        $controller = $controller."ViewController";
        require_once $controller.".php";
        $action = $router::action()."Action";
        $params = $router::params();
        $c = new $controller();
        $c->params = $params;
		$c->action = $action;
        if (!method_exists($c, $action))
            throw new UIViewControllerException(__(sprintf("Action method '%s' is not present in controller '%s'", $action, $controller)));
        $c->$action();
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

?>