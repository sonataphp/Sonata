<?
//
//  STRouter.php
//  Sonata/Foundation
//
//  Created by Dan Sosedoff on 9/20/2009.
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
	
	public static function action() {
		return self::$action;
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
	
	public static function restart() {
		self::$_instance = new Router();
		return self::instance();
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
		self::$routes[$rule] = new Route($rule, self::$request_uri, $target, $conditions);
                
	}
	
	public function defaultRoutes() {
		self::map('/:controller');
		self::map('/:controller/:action');
		self::map('/:controller/:action/:id');
	}
	
	private function setRoute($route) {
		self::$route_found = true;
		$params = $route->params;
		self::$controller = $params['controller']; unset($params['controller']);
		self::$action = $params['action']; unset($params['action']);
		self::$id = $params['id'];
		self::$params = $params;
				
		if (empty(self::$controller)) self::$controller = ROUTER_DEFAULT_CONTROLLER;
		if (empty(self::$action)) self::$action = ROUTER_DEFAULT_ACTION;
		if (empty(self::$id)) self::$id = null;
		
		$w = explode('_', self::$controller);
		foreach($w as $k => $v) $w[$k] = ucfirst($v);
		self::$controller_name = implode('', $w);
	}
	
	public function execute($merge_get = true) {
		
		foreach(self::$routes as $route) {
                    
			if ($route->is_matched) {
				self::setRoute($route, $merge_get);
				break;
			}
		}
	}
}


class Route {
	public $is_matched = false;
	public $params;
	public $url;
	private $conditions;
	
	function __construct($url, $request_uri, $target, $conditions) {
		$this->url = $url;
		$this->params = array();
		$this->conditions = $conditions;
		$p_names = array(); $p_values = array();
		
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
		
		unset($p_names); unset($p_values);
	}
	
	function regex_url($matches) {
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