<?php

//
// Sodium 2.0.10-alpha
//
// This file is part of the Sodium PHP framework, released under the
// Creative Commons Attribution-NonCommercial-ShareAlike licence.
//
// The framework is created and maintaned by Gergely J. Horváth.
// More information should be available at http://hgj.hu
//
// Copyright 2013 by Gergely J. Horváth.
//

/**
 * Sodium / Sodium class
 *
 * Responsible for initialization functions and other general implementations.
 */
class Sodium extends Sodium\SodiumConfiguration {

	// TODO: Settle on a versioning scheme (http://en.wikipedia.org/wiki/Versioning)

	/**
	 * Initialise the framework.
	 */
	public static function initialise() {
		// Initialise here
		self::initialiseHelper();
		// Initialization of other Sodium classes
		Sodium\Modules::initialise();
		// Call the initialization hook for the modules
		Sodium\Modules::callHooks('sodiumInitialise');
		Sodium\User::initialise();
		Sodium\View::initialise();
		// Call the initialised hook for the modules
		Sodium\Modules::callHooks('sodiumInitialised');
	}
	
	/**
	 * Initialisation in this class.
	 */
	protected static function initialiseHelper() {
		// Enable compression if enabled and available
		if (self::getCompression()) {
			if (function_exists('gzcompress')) {
				ini_set('zlib.output_compression', true);
			} else {
				// Disable compression so we'll know it is not active
				self::setCompression(false);
			}
		}
		// Start an output buffer
		ob_start();
	}
	
	/**
	 * Finalise the framework.
	 */
	public static function finalise() {
		// Call the finalisation hook for the modules
		Sodium\Modules::callHooks('sodiumFinalise');
		// Finalisation of other Sodium classes
		Sodium\View::finalise();
		Sodium\User::finalise();
		// Call the finalised hook for the modules
		Sodium\Modules::callHooks('sodiumFinalised');
		Sodium\Modules::finalise();
		// Finalise here
		self::finaliseHelper();
	}
	
	/**
	 * Finalisation in this class.
	 */
	protected static function finaliseHelper() {
		// Flush all buffers
		while (@ob_end_flush());
	}

	/**
	 * Clear all the cache files in the cache directory.
	 */
	public static function clearCache() {
		// TODO: drive into subdirectories
		$files = glob(BASE_PATH . '/cache/*');
		foreach ($files as $file) {
			unlink($file);
		}
	}

	//
	// Redirection methods
	//

	/**
	 * Redirects if a rule/filter matches the requested URL
	 */
	public static function doRedirect() {
		if (count(self::getRedirects()) != 0) {
			$query = self::getQueryString();
			foreach (self::getRedirects() as $redirectData) {
				$goodLocation = $redirectData['to'];
				// If the entry is simple
				if ($redirectData['type'] == 0) {
					foreach ($redirectData['from'] as $badLocation) {
						if (stripos($query, $badLocation) === 0) {
							if ($redirectData['statusCode'] !== false) {
								Sodium\General::redirect($goodLocation, $redirectData['statusCode']);
							} else {
								Sodium\General::redirect($goodLocation);
							}
						}
					}
				} else {
					// or it is a regular expression
					if (preg_match($redirectData['from'], $query) != 0) {
						if ($redirectData['statusCode'] !== false) {
							Sodium\General::redirect($goodLocation, $redirectData['statusCode']);
						} else {
							Sodium\General::redirect($goodLocation);
						}
					}
				}
			}
		}
	}

	// The configured "redirect after"
	static protected $redirectAfter = false;

	/**
	 * Returns the configured "redirect after" URL.
	 * @return string The URL to redirect to.
	 */
	public static function getRedirectAfter() {
		return self::$redirectAfter;
	}

	/**
	 * Sets a new configuration for "redirect after".
	 * @param string $url The URL to redirect to.
	 */
	public static function setRedirectAfter($url) {
		self::$redirectAfter = $url;
	}

	/**
	 * Redirects if a "redirect after" setting is set somewhere.
	 * The priority of these settings is the following:
	 *
	 * 1. Sodium::redirectAfter('URL');
	 *
	 * 2. $_POST['redirect-after']
	 *
	 * 3. Query parameter 'redirect-after'
	 */
	public static function doRedirectAfter() {
		if (self::getRedirectAfter() !== false) {
			Sodium\General::redirect(self::getRedirectAfter());
		} else if (!empty($_POST['redirect-after'])) {
			Sodium\General::redirect($_POST['redirect-after']);
		} else if (self::getParameter('redirect-after') !== null) {
			Sodium\General::redirect(self::getParameter('redirect-after'));
		}
	}

	//
	// Query and parameters
	//

	// The current query
	static protected $query = array();

	// The parameters
	static protected $parameters = array();

	/**
	 * Returns the current query parsed as an array.
	 * @return array The current query.
	 */
	public static function getQuery() {
		return self::$query;
	}

	/**
	 * Returns the current query concatenated into one string,
	 * ready to be used as part of an URL.
	 * @return string The imploded query.
	 */
	public static function getQueryString() {
		return implode('/', self::$query);
	}

	/**
	 * Returns the value for the query parameter $key.
	 * @param string $key The key of the parameter.
	 * @return mixed The value of the parameter or null if not found.
	 */
	public static function getParameter($key) {
		if (isset(self::$parameters[$key])) {
			return self::$parameters[$key];
		} else {
			return null;
		}
	}

	/**
	 * Returns the query parameters.
	 * @return array The array with the query parameters.
	 */
	public static function getParameters() {
		return self::$parameters;
	}

	/**
	 * Returns the query parameters as a formatted string, ready to be used in URLs.
	 * @return string The query parameters as a formatted string.
	 */
	public static function getParametersString() {
		if (!empty(self::$parameters)) {
			$array = array();
			foreach (self::$parameters as $key => $value) {
				$array[] = "{$key}={$value}";
			}
			return implode('/', $array);
		} else {
			return '';
		}
	}

	/**
	 * Parses the query ($_SERVER['REQUEST_URI'] or $_SERVER['argv'])
	 */
	public static function parseQuery() {
		if (PHP_SAPI == 'cli') {
			// Get the query from arguments if run as CLI
			$arguments = $_SERVER['argv'];
			array_shift($arguments);
			$query = implode('/', $arguments);
		} else {
			// Get the query as the request URI, but cut the BASE_URL part
			$query = $_SERVER['REQUEST_URI'];
			// NOTE: From PHP 5.5.0 we can use empty(BASE_URL)
			if (BASE_URL != '') {
				$position = strpos($query, BASE_URL);
				if ($position !== false) {
					$query = substr_replace($query,'',$position,strlen(BASE_URL));
				}
			}
		}
		// Get the query trimmed, or use the default query
		$query = trim($query, "\s\\/");
		// If the query string is empty after the trim, use the default
		if (empty($query)) {
			$query = self::getDefaultQuery();
		}
		// If classic '?variable=value' is used, special care is needed
		if (strstr($query, '?')) {
			$array = parse_url($query);
			$query = trim($array['path'], "\s\\/");
			if (!empty($array['query'])) {
				if (strstr($array['query'], '&')) {
					$parameters = explode('&', $array['query']);
				} else {
					$parameters = array($array['query']);
				}
				foreach ($parameters as $parameter) {
					list($key, $value) = explode('=', $parameter);
					self::$parameters[$key] = $value;
				}
			}
		}
		// Explode the query to an array
		if (strstr($query, '/')) {
			$query = explode('/', $query);
		} else {
			$query = array($query);
		}
		// Parse parameters ( /index.html/paramter=value/parameter2=value2 ... )
		// Clean up .html .htm .php extensions from the query
		foreach ($query as $id => &$q) {
			if (strstr($q, '=')) {
				list($key, $value) = explode('=', $q);
				self::$parameters[$key] = $value;
				unset($query[$id]);
			} else {
				$q = str_replace(self::getRemovableExtensions(), '', $q);
			}
		}
		// $q is a reference to the last element of $query, better unset it
		unset($q);
		// Rearrange the query array
		self::$query = array_merge($query);
	}

	/**
	 * Gets the associated controller for the query, based on the routing
	 * configuration - or guesses the controller's name from the query.
	 * @param array $query The query from Sodium::getQuery().
	 * @return string The controller's name.
	 */
	public static function getController($query) {
		// Assemble the query into a string
		$queryString = implode('/', $query);
		$match = false;
		// See if we have a match among the custom routing filters
		foreach (self::getCustomRouting() as $filter => $controller) {
			if (stripos($queryString, $filter) === 0) {
				$match = $controller;
			}
		}
		// If no match is found, guess the controller from the query
		// TODO: Controller guessing like in pages.php controller
		if ($match == false) {
			$controller = $query[0];
			if (file_exists(BASE_PATH . '/controllers/' . $controller . '.php')) {
				return $controller;
			} else {
				// If we can not guess the controller, return the default controller
				if (self::usePages()) {
					// Although, a default controller may be set other than 'pages',
					// the configuration still says we should use 'pages' before
					// calling the "last resort" default controller.
					return 'pages';
				} else {
					return self::getDefaultController();
				}
			}
		}
		return $match;
	}

	/**
	 * Calls the controller named $controller.
	 * @param string $controller The name of the controller.
	 */
	public static function callController($controller) {
		require_once(BASE_PATH . '/controllers/' . $controller . '.php');
		// TODO: Error handling, logging
	}

}
