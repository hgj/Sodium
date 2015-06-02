<?php

//
// Sodium 2.0.11-alpha
//
// This file is part of the Sodium PHP framework, released under the
// Creative Commons Attribution-NonCommercial-ShareAlike licence.
//
// The framework is created and maintaned by Gergely J. Horváth.
// More information should be available at http://hgj.hu
//
// Copyright 2014 by Gergely J. Horváth.
//

namespace Sodium;

/**
 * Sodium / View class
 *
 * View layer of the framework provides template handling with many features
 * like headers, designs and sub-templates, etc.
 */
class View {

	/**
	 * Initialises the View class.
	 */
	public static function initialise() {
		// Nothing...?
	}

	/**
	 * Finalises the View class.
	 */
	public static function finalise() {
		// Nothing...?
	}
	
	//
	// Configuration
	//

	// Possible template extensions
	protected static $templateExtensions = array(
		'php', 'xhtml', 'html', 'htm',
	);
	
	// Redefined template names
	protected static $templates = array();

	/**
	 * Defines (or redefines) a template.
	 * @param string $name The name of the template.
	 * @param string $value The new value/name of the template.
	 */
	public static function setTemplate($name, $value) {
		self::$templates[$name] = $value;
	}

	/**
	 * Returns the value/name of the template $name if defined.
	 * @param string $name The name of the template.
	 * @return string The value/name of the template.
	 */
	public static function getTemplate($name) {
		if (isset(self::$templates[$name])) {
			return self::$templates[$name];
		} else {
			return false;
		}
	}

	// Currently selected design
	protected static $design = '.';

	/**
	 * Sets the currently selected design.
	 * @param string $name The name/path of the design to use.
	 */
	public static function setDesign($name) {
		self::$design = $name;
	}

	/**
	 * Returns the name/path of the currenlty selected design.
	 * @return string The name/path of the design currently selected.
	 */
	public static function getDesign() {
		return self::$design;
	}

	// Configured headers to send.
	protected static $headers = array();

	/**
	 * Returns the list of the currently configured default headers.
	 * @return array Array of headers as 'field => value' pairs.
	 */
	public static function getHeaders() {
		return self::$headers;
	}

	/**
	 * Sets a header to be sent. It replaces any previous values.
	 * @param string $field The header name.
	 * @param string $value The value of the header.
	 */
	public static function setHeader($field, $value) {
		self::$headers[$field] = $value;
	}
	
	/**
	 * Adds a header and does not overwrite previous value(s).
	 * @param string $field The header name.
	 * @param string $value The value of the header.
	 */
	public static function addHeader($field, $value) {
		if (!empty(self::$headers[$field])) {
			if (is_array(self::$headers[$field])) {
				self::$headers[$field][] = $value;
			} else {
				self::$headers[$field] = array(
					self::$headers[$field],
					$value
				);
			}
		} else {
			self::$headers[$field] = array(
				$value
			);
		}
	}

	//
	// Parameters
	//

	// An array for the parameters.
	protected static $parameters = array();

	/**
	 * Returns the requested paramter if it exists.
	 * @param mixed $name Key for the parameter.
	 * @return mixed The value of the parameter, null othervise.
	 */
	public static function getParameter($name) {
		if (isset(self::$parameters[$name])) {
			return self::$parameters[$name];
		} else {
			return null;
		}
	}

	/**
	 * Sets a parameter to the given value.
	 * @param mixed $name The key for the parameter.
	 * @param mixed $value The value of the parameter.
	 */
	public static function setParameter($name, $value) {
		self::$parameters[$name] = $value;
	}

	//
	// Content and attachments
	//

	// The content to display.
	protected static $content = '';

	// Type of the content to be delivered
	protected static $contentType = 'text/html';

	/**
	 * TODO
	 * @return type
	 */
	public static function getContent() {
		return self::$content;
	}

	/**
	 * TODO
	 * @param type $content
	 */
	public static function setContent($content) {
		self::$content = $content;
	}

	/**
	 * TODO
	 * @return type
	 */
	public static function getContentType() {
		return self::$contentType;
	}

	/**
	 * TODO
	 * @param type $contentType
	 */
	public static function setContentType($contentType) {
		self::$contentType = $contentType;
	}

	//
	// Attachments
	//

	// TODO: Attachments.

	//
	// Page rendering
	//

	/**
	 * Renders the page.
	 */
	public static function render() {
		// Set headers
		foreach (self::$headers as $header => $value) {
			header("{$header}: {$value}", true);
		}
		// TODO: Finish the default template, and probably rename this to frame or just main
		self::includeTemplate('site-main');
	}

	/**
	 * Includes a template.
	 * @param string $name The name of the tempalte to include.
	 */
	protected static function includeTemplate($name) {
		// TODO: Maybe a getter for this?
		$extensions = '.{' . implode(',', self::$templateExtensions) . '}';
		if (isset(self::$templates[$name])) {
			$template = self::$templates[$name];
		} else {
			$template = $name;
		}
		$files = array_merge(
			glob(BASE_PATH . '/templates/site/' . self::$design . '/' . $template . $extensions, GLOB_BRACE),
			glob(BASE_PATH . '/templates/site/' . $template . $extensions, GLOB_BRACE)
		);
		if (count($files) == 0) {
			trigger_error(__CLASS__ . '::' . __FUNCTION__ . " : Site template '{$name}' not found.", E_USER_WARNING);
			return false;
		} else {
			include($files[0]);
		}
	}

	// TODO: rewrite all the other methots from legacy

}
