<?php
//  STCookie.php
//  Sonata/Foundation
//
// Copyright 2008-2009 Kohana Team
// Based on Kohana Cookies
// Modified to fit Sonata Framework syntax standards by Roman Efimov <romefimov@gmail.com>
//
// License: http://kohanaframework.org/license
//

define("STCookieExpirationHour", 3600);
define("STCookieExpirationDay", 86400);
define("STCookieExpirationWeek", 604800);
define("STCookieExpirationMonth", 2592000);
define("STCookieExpirationYear", 31536000);

class STCookie extends STObject {
        /**
	 * @var  string  Magic salt to add to the cookie
	 */
	public static $salt = 'STCookie';

	/**
	 * @var  integer  Number of seconds before the cookie expires
	 */
	public static $expiration = 0;

	/**
	 * @var  string  Restrict the path that the cookie is available to
	 */
	private static $path = '/';

	/**
	 * @var  string  Restrict the domain that the cookie is available to
	 */
	private static $domain = NULL;

	/**
	 * @var  boolean  Only transmit cookies over secure connections
	 */
	public static $secure = FALSE;

	/**
	 * @var  boolean  Only transmit cookies over HTTP, disabling Javascript access
	 */
	public static $httponly = FALSE;

	/**
	 * Gets the value of a signed cookie. Cookies without signatures will not
	 * be returned. If the cookie signature is present, but invalid, the cookie
	 * will be deleted.
	 *
	 * @param   string  cookie name
	 * @param   mixed   default value to return
	 * @return  string
	 */
	public static function getCookie($key, $default = NULL) {
		if ( ! isset($_COOKIE[$key])) {
			// The cookie does not exist
			return $default;
		}

		// Get the cookie value
		$cookie = $_COOKIE[$key];

		// Find the position of the split between salt and contents
		$split = strlen(STCookie::salt($key, NULL));

		if (isset($cookie[$split]) AND $cookie[$split] === '~') {
			// Separate the salt and the value
			list ($hash, $value) = explode('~', $cookie, 2);

			if (STCookie::salt($key, $value) === $hash) {
				// Cookie signature is valid
				return $value;
			}

			// The cookie signature is invalid, delete it
			STCookie::delete($key);
		}

		return $default;
	}

	/**
	 * Sets a signed cookie. Note that all cookie values must be strings and no
	 * automatic serialization will be performed!
	 *
	 * @param   string   name of cookie
	 * @param   string   value of cookie
	 * @param   integer  lifetime in seconds
	 * @return  boolean
	 */
	public static function cookieWithProperties($name, $value, $expiration = NULL) {
		if ($expiration === NULL) {
			// Use the default expiration
			$expiration = STCookie::$expiration;
		}

		if ($expiration !== 0) {
			// The expiration is expected to be a UNIX timestamp
			$expiration += time();
		}

		// Add the salt to the cookie value
		$value = STCookie::salt($name, $value).'~'.$value;

		return setcookie($name, $value, $expiration, STCookie::$path, STCookie::$domain, STCookie::$secure, STCookie::$httponly);
	}
	
	public static function setPath($path) {
		self::$path = $path;
	}
	
	public static function setDomain($domain) {
		self::$domain = $domain;
	}
	
	public static function initWithRegistry() {
		if (STRegistry::get("Cookie_Path"))
				self::setPath(STRegistry::get("Cookie_Path"));
				
		if (STRegistry::get("Cookie_Domain"))
				self::setDomain(STRegistry::get("Cookie_Domain"));
	}
	
	public static function initWithPathAndDomain($path, $domain) {
		self::$path = $path;
		self::$domain = $domain;
	}

	/**
	 * Deletes a cookie by making the value NULL and expiring it.
	 *
	 * @param   string   cookie name
	 * @return  boolean
	 */
	public static function deleteCookie($name) {
		// Remove the cookie
		unset($_COOKIE[$name]);

		// Nullify the cookie and make it expire
		return STCookie::cookieWithProperties($name, NULL, -86400);
	}

	/**
	 * Generates a salt string for a cookie based on the name and value.
	 *
	 * @param   string   name of cookie
	 * @param   string   value of cookie
	 * @return  string
	 */
	public static function salt($name, $value) {
		// Determine the user agent
		$agent = isset($_SERVER['HTTP_USER_AGENT']) ? strtolower($_SERVER['HTTP_USER_AGENT']) : 'unknown';

		return sha1($agent.$name.$value.STCookie::$salt);
	}

	final private function __construct() {
		// This is a static class
	}
}

?>