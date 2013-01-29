<?php

//
// Sodium 2.0.6-alpha
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
 * Sodium / General helpers' class
 *
 * Contains general purpose helper methods (previously as standalone functions).
 */
class General {

	/**
	 * Get files matching a PCRE pattern from a directory tree.
	 * @param string $directory The directory to start the search in.
	 * @param string $pattern The regular expression to mach.
	 * @param bool $fullPathMatch True to match the whole path, not just the filename.
	 * @return array An array with a 'pathname => SplFileInfo' structure.
	 */
	public static function getFilesPCRE($directory, $pattern, $fullPathMatch = false) {
		$iterator = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator($directory, \FilesystemIterator::FOLLOW_SYMLINKS)
		);
		$result = array();
		foreach ($iterator as $key => $value) {
			if ($fullPathMatch === true) {
				$string = $value->getPathname();
			} else {
				$string = $value->getFilename();
			}
			if (preg_match($pattern, $string) === 1) {
				$result[$key] = $value;
			}
		}
		return $result;
	}
	
	/**
	 * Redirects (sends a Location header) to the given URL,
	 * and forces a redirection (3xx) HTTP status code.
	 * @param string $url The URL to redirect to.
	 * @param int $statusCode The HTTP status code to be sent
	 */
	public static function redirect($url, $statusCode = 303) {
		if ($statusCode < 300 or $statusCode > 399) {
			trigger_error(__CLASS__ . '::' . __FUNCTION__ . " : Invalid HTTP redirection status code '$statusCode'. Using 303 instead.", E_USER_WARNING);
			$statusCode = 303;
		}
		header("Location: $url", true, $statusCode);
		die();
	}

	/**
	 * Relying on not official standards, this method sends a Refresh header
	 * trying to make the browser refresh (or redirect) the given URL after
	 * the given timeout.
	 * @param int $timeOut The timeout in seconds
	 * @param int $url The URL to redirect to after the timeout
	 */
	public static function refresh($timeOut = 5, $url = '') {
		$value = $timeOut;
		if (!empty($url)) {
			$value .= "; url=$url";
		}
		View::setHeader("Refresh", $value);
	}

	/**
	 * Present an error page with the help of TODO
	 * @param int $number
	 */
	public static function errorPage($number) {
		// This method should be called only once.
		static $n = 0;
		$n++;
		if ($n > 1) {
			// If it is called again, we have some serious problem(s)!
			trigger_error(__CLASS__ . '::' . __FUNCTION__ . " : Critical error. This function is called more than once.", E_USER_ERROR);
			// The script should terminate here, but let me be sceptic about that.
			die("500: CRITICAL ERROR");
		}
		// http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html
		$headers = array(
			400 => 'Bad Request',
			401 => 'Unauthorized',
			402 => 'Payment Required',
			403 => 'Forbidden',
			404 => 'Not Found',
			405 => 'Method Not Allowed',
			406 => 'Not Acceptable',
			407 => 'Proxy Authentication Required',
			408 => 'Request Timeout',
			409 => 'Conflict',
			410 => 'Gone',
			411 => 'Length Required',
			412 => 'Precondition Failed',
			413 => 'Request Entity Too Large',
			414 => 'Request-URI Too Long',
			415 => 'Unsupported Media Type',
			416 => 'Requested Range Not Satisfiable',
			417 => 'Expectation Failed',
			500 => 'Internal Server Error',
			501 => 'Not Implemented',
			502 => 'Bad Gateway',
			503 => 'Service Unavailable',
			504 => 'Gateway Timeout',
			505 => 'HTTP Version Not Supported',
		);
		// End and clean all buffers
		while (@ob_end_clean());
		// Send error headers
		if (isset($headers[$number])) {
			header("HTTP/1.1 {$number}", true, $number);
		} else {
			trigger_error(__CLASS__ . '::' . __FUNCTION__ . " : Unknown HTTP error code '$number'.", E_USER_WARNING);
			// We will still try to send the header, but will not include the 'description'
			header("HTTP/1.1 {$number} {$headers[$number]}", true, $number);
		}
		// Start the output buffer again
		ob_start();
		// Serve the error page
		include(BASE_PATH . '/templates/site/errors.php');
		while (@ob_end_flush());
		// Hope the message went through...
	}

}
