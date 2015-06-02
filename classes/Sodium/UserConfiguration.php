<?php

//
// Sodium 2.1.0-alpha
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
 * Sodium / UserConfiguration class
 *
 * User related configurations.
 */

abstract class UserConfiguration {
	
	//
	// User class
	//
	
	protected static $userClass = null;
	
	/**
	 * Return the current used User class.
	 * @return string The name of the class.
	 */
	public static function getUserClass() {
		return self::$userClass;
	}
	
	/**
	 * Sets the user class' name to be used for session handling.
	 * @param string $className The name of the User class to use.
	 * @return string The new setting.
	 */
	public static function setUserClass($className) {
		self::$userClass = $className;
		return $className;
	}
	
	//
	// Session name
	//
	
	protected static $sessionName = null;
	
	/**
	 * Return the current session name.
	 * @return string self::$sessionName
	 */
	public static function getSessionName() {
		return self::$sessionName;
	}
	
	/**
	 * Sets the session name.
	 * See http://php.net/manual/en/function.session-name.php
	 * @param string $newName The new name for the session.
	 * @return string The new session name if changed, the old otherwise.
	 */
	public static function setSessionName($newName) {
		if (!empty($newName)) {
			self::$sessionName = $newName;
		} else {
			trigger_error(__CLASS__ . '::' . __FUNCTION__ . " : The session name can not be empty. '{$newName}' is given.", E_USER_WARNING);
		}
		return self::$sessionName;
	}
	
}
