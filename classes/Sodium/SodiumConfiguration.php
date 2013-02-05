<?php

//
// Sodium 2.0.9-alpha
//
// This file is part of the Sodium PHP framework, released under the
// Creative Commons Attribution-NonCommercial-ShareAlike licence.
//
// The framework is created and maintaned by Gergely J. Horváth.
// More information should be available at http://hgj.hu
//
// Copyright 2013 by Gergely J. Horváth.
//

namespace Sodium;

/**
 * Sodium / SodiumConfiguration class
 *
 * Sodium related configurations.
 */
abstract class SodiumConfiguration {

	//
	// Compression
	//
	
	protected static $compression = true;
	
	public static function getCompression() {
		return self::$compression;
	}
	
	/**
	 * Enable or disable compression.
	 * @param bool $enabled True to enable, false to disable compression.
	 * @return bool The new setting if changed, the old if unchanged.
	 */
	public static function setCompression($enabled) {
		if (is_bool($enabled)) {
			self::$compression = true;
		} else {
			trigger_error(__CLASS__ . '::' . __FUNCTION__ . " : Compression can be set to 'true' (enabled) or 'false' (disabled). '{$enabled}' is given.", E_USER_WARNING);
		}
		return self::$compression;
	}
	
	//
	// Redirection
	//

	// The array of defined redirections.
	protected static $redirects = array();

	/**
	 * Returns the configured redirects.
	 * @return mixed The configured redirects or an empty array.
	 */
	public static function getRedirects() {
		return self::$redirects;
	}

	/**
	 * Adds a redirect to the configuration.
	 * @param mixed $from A string or array of strings with the bad URLs.
	 * @param string $to The URL to redirect to.
	 * @param int $statusCode The HTTP status code to return.
	 */
	public static function addRedirect($from, $to, $statusCode = false) {
		if (!is_array($from)) {
			$from = array($from);
		}
		self::$redirects[] = array(
			'from' => $from,
			'to' => $to,
			'statusCode' => $statusCode,
			'type' => 0,
		);
	}

	/**
	 * Adds a redirect with Perl Compatible Regular Expression
	 * @param mixed $from A PCRE or array of PCRE matching the bad URLs.
	 * @param string $to The URL to redirect to.
	 * @param int $statusCode The HTTP status code to return.
	 */
	public static function addRedirectPCRE($from, $to, $statusCode = false) {
		self::$redirects[] = array(
			'from' => $from,
			'to' => $to,
			'statusCode' => $statusCode,
			'type' => 1,
		);
	}

	//
	// Routing
	//

	// The array of defined custom routing configurations.
	protected static $customRouting = array();

	/**
	 * Returns the defined custom routing configurations.
	 * @return array The defined custom routing configurations.
	 */
	public static function getCustomRouting() {
		return self::$customRouting;
	}

	/**
	 * Adds a custom routing to the configuration.
	 * @param string $filters The string that matches the beginning of the URL.
	 * @param string $controller The controller to call/execute on match.
	 * @return bool True on success, false on any error.
	 */
	public static function addCustomRouting($filters, $controller) {
		if (!is_array($filters)) {
			$filters = array($filters);
		}
		foreach ($filters as $filter) {
			$ft = trim($filter, "\s\\/");
			if (!empty($ft)) {
				self::$customRouting[$ft] = $controller;
				return true;
			} else {
				trigger_error(__CLASS__ . '::' . __FUNCTION__ . " : Skipping invalid filter '{$filter}' for custom routing.", E_USER_WARNING);
				return false;
			}
		}
	}

	//
	// Controllers
	//

	// The configured default controller.
	protected static $defaultController = 'pages';

	// Still use the 'pages' controller?
	protected static $usePages = true;

	/**
	 * Returns the configured default controller.
	 * @return string The name of the default controller.
	 */
	public static function getDefaultController() {
		return self::$defaultController;
	}

	/**
	 * Tells if the 'pages' controller is still enabled.
	 * @return bool True if the 'pages' controller is still enabled.
	 */
	public static function usePages() {
		return self::$usePages;
	}

	/**
	 * Sets the default controller to $controller. You can also disable
	 * the usage of the 'pages' controller.
	 * @param string $controller
	 * @param bool $usePages
	 * @return bool True on success, false on any error.
	 */
	public static function setDefaultController($controller, $usePages = true) {
		self::$usePages = $usePages;
		if (file_exists(BASE_PATH . '/controllers/' . $controller . '.php')) {
			self::$defaultController = $controller;
			return true;
		} else {
			trigger_error(__CLASS__ . '::' . __FUNCTION__ . " : Can not set '$controller' as default controller, as it does not exist.", E_USER_WARNING);
			return false;
		}
	}

	//
	// Query
	//

	// The currently configured default query.
	protected static $defaultQuery = 'index';

	/**
	 * Returns the name of the currently configured default controller.
	 * @return string The name of the controller.
	 */
	public static function getDefaultQuery() {
		return self::$defaultQuery;
	}

	/**
	 * Sets the default query.
	 * @param string $query The query.
	 * @return bool True on success, false on any error.
	 */
	public static function setDefaultQuery($query) {
		$query = trim($query, "\s\\/");
		if (!empty($query)) {
			self::$defaultQuery = $query;
			return true;
		} else {
			trigger_error(__CLASS__ . '::' . __FUNCTION__ . " : Can not set the default query to an empty string.", E_USER_WARNING);
			return false;
		}
	}

	// To be removed page extensions.
	protected static $removableExtensions = array('.xhtml', '.html', '.htm', '.php');

	/**
	 * Returns the list of removable file extensions currently configured.
	 * @return array The array of extensions.
	 */
	public static function getRemovableExtensions() {
		return self::$removableExtensions;
	}

	/**
	 * Adds the given file extension(s) to the configuration.
	 * @param mixed $extension String or array of strings with the extensions.
	 */
	public static function addRemovableExtension($extension) {
		self::$removableExtensions = array_merge(self::$removableExtensions, (array) $extension);
	}

}
