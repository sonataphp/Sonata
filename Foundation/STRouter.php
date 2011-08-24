<?php
//  STRouter.php
//  Sonata/Foundation
//
// Copyright 2010 Dan Sosedoff <http://blog.sosedoff.com>
//                Roman Efimov <romefimov@gmail.com>
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

define('ROUTER_DEFAULT_CONTROLLER', 'Home');
define('ROUTER_DEFAULT_ACTION', 'index');

class STRouter extends STObject {
    
	private static $request_uri;
	private static $routes;
	private static $controller, $controller_name;
	private static $action, $id;
	private static $params;
	private static $route_found = false;
	private static $subdomain;
	
	public static function controller() {
		return self::$controller;
	}
	
	public static function setController($controller) {
		self::$controller = $controller;
	}
	
	public static function setSubdomain($subdomain) {
		self::$subdomain = $subdomain;
	}
	
	public static function getSubdomain() {
		return self::$subdomain;
	}
	
	public static function action() {
		return self::$action;
	}
	
	public static function setAction($action) {
		self::$action = $action;
	}
	
	public static function params($param = null) {
		if ($param) return self::$params[$param];
		return self::$params;
	}
	
	public static function setParam($param, $value) {
		self::$params[$param] = $value;
	}
	
	public static function routes() {
		return self::$routes;
	}
	
	public static function routeFound() {
		return self::$route_found;
	}
	
	public static function init() {
		$request = $_SERVER['REQUEST_URI'];
		
		$pos = strpos($request, '?');
		if ($pos) {
		    $request = substr($request, 0, $pos);
		} else {
			$pos = strpos($request, '&');
			if ($pos) $request = substr($request, 0, $pos);	
		}
		
		$request .= (substr($request, strlen($request)-1, 1) != '/') ? '/' : '';
		self::$request_uri = $request;
		self::$routes = array();
		
		self::map("/minify/js/:files", array('controller' => 'UIMinifier', 'action' => "scriptsGenerator"));
		self::map("/minify/css/:type/:files", array('controller' => 'UIMinifier', 'action' => 'stylesGenerator'));
	}
	
	public static function map($rule, $target=array(), $conditions=array()) {
		$subdomain = self::$subdomain;
		if ($subdomain == '') $subdomain = 'www';
		self::$routes[$subdomain][$rule] = new Route($rule, self::$request_uri, $target, $conditions);
		return self::$routes[$subdomain][$rule];
	}
	
	public static function mapSecure($rule, $target=array(), $conditions=array()) {
		$conditions["__secure"] = true;
		$subdomain = self::$subdomain;
		if ($subdomain == '') $subdomain = 'www';
		self::$routes[$subdomain][$rule] = new Route($rule, self::$request_uri, $target, $conditions);
		return self::$routes[$subdomain][$rule];
	}
	
	public static function defaultRoutes() {
		self::map('/:controller');
		self::map('/:controller/:action');
		self::map('/:controller/:action/:id');
	}
	
	private function setRoute($route) {
		self::$route_found = true;
		$params = $route->params;
		self::$controller = $params['controller']; unset($params['controller']);
		self::$action = (isset($params['action']))?$params['action']:""; unset($params['action']);
		self::$id = (isset($params['id']))?$params['id']:"";
		self::$params = $params;
				
		if (empty(self::$controller)) self::$controller = ROUTER_DEFAULT_CONTROLLER;
		if (empty(self::$action)) self::$action = ROUTER_DEFAULT_ACTION;
		if (empty(self::$id)) self::$id = null;
		
		$w = explode('_', self::$controller);
		foreach($w as $k => $v) $w[$k] = ucfirst($v);
		self::$controller_name = implode('', $w);
	}
	
	public function execute() {
		//print_r(self::$routes);
		//exit();
		$subdomain = STServer::subdomain();
		if ($subdomain == '') $subdomain = 'www';
		if (self::$subdomain == '') self::$subdomain = 'www';
		foreach(self::$routes[self::$subdomain] as $route)
			if ($route->is_matched) {
					self::setRoute($route);
				break;
			}
	}
}

class Route {
	public $is_matched = false;
	public $params;
	public $url;
	public $target;
	public $alias;
	private $conditions;
	
	public function __construct($url, $request_uri, $target, $conditions) {
		$this->url = $url;
		$this->params = array();
		$this->conditions = $conditions;
		$p_names = array(); $p_values = array();
		
		if (is_string($target)) {
			$t = explode("#", $target);
			$target = array('controller' => $t[0], 'action' => $t[1] ? $t[1] : 'index');
		}
		
		preg_match_all('@:([\w]+)@', $url, $p_names, PREG_PATTERN_ORDER);
		$p_names = $p_names[0];
		
		$url_regex = preg_replace_callback('@:[\w]+@', array($this, 'regex_url'), $url);
		$url_regex .= '/?';
							
		if (preg_match('@^'.$url_regex.'$@', $request_uri, $p_values)) {
			array_shift($p_values);
			foreach($p_names as $index => $value) $this->params[substr($value,1)] = urldecode($p_values[$index]);
			foreach($target as $key => $value) $this->params[$key] = $value;
			$this->is_matched = true;
		}
		if (isset($this->conditions['__secure']))
			$this->params['__secure'] = true;
			
		
		$this->target = $target;
		
		unset($p_names); unset($p_values);
	}
	
	public function alias($alias) {
		$this->alias = $alias;
	}
	
	
	public function regex_url($matches) {
		$key = str_replace(':', '', $matches[0]);
		if (array_key_exists($key, $this->conditions)) {
			return '('.$this->conditions[$key].')';
		} 
		else {
			return '([a-zA-Z0-9_\+\-%\.]+)';
		}
	}
}

?>