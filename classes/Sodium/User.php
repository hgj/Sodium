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

namespace Sodium;

/**
 * Sodium / User class
 *
 * Basic session handling for the users.
 */
class User extends UserConfiguration {

	// A User object for the current user.
	protected static $currentUser = null;
	
	/**
	 * Initialises the User class.
	 * Tries to build a session for the current user.
	 */
	public static function initialise() {
		// Set the session name if overridden
		if (!empty(self::$sessionName)) {
			session_name(self::$sessionName);
		}
		// Set the session ID if a module knows one
		$sessionID = self::getSessionID();
		if ($sessionID !== false) {
			session_id($sessionID);
		}
		// Start the session
		if (!session_start()) {
			trigger_error(__CLASS__ . '::' . __FUNCTION__ . " : Failed to start the session.", E_USER_WARNING);
		}
		// See if the user wants to log in
		$loginData = self::getLogin();
		if ($loginData !== false) {
			$result = self::verifyLogin($loginData);
			if ($result['success']) {
				if (!empty(self::$userClass)) {
					if (class_exists(self::$userClass)) {
						self::$currentUser = new self::$userClass($result['ID']);
					} else {
						trigger_error(__CLASS__ . '::' . __FUNCTION__ . " : Defined User class '" . self::$userClass . "' does not exist. Defaulting to simple ID mode.", E_USER_WARNING);
					}
				}
				if (self::$currentUser == null) {
					self::$currentUser = $result['ID'];
				}
			}
		}
	}
	
	/**
	 * Finalises the User class.
	 */
	public static function finalise() {
		// Nothing?
	}

	/**
	 * Calls the apropriate hook to get the session's ID
	 * @return mixed False on failure or the session ID on success.
	 */
	protected static function getSessionID() {
		$results = Modules::callHooks('sodiumUserGetSessionID');
		foreach ($results as $className => $result) {
			if ($result !== false) {
				if (preg_match('/[^a-zA-Z0-9,-]/', $result) === 0) {
					return $result;
				} else {
					trigger_error(__CLASS__ . '::' . __FUNCTION__ . " : Ignoring invalid '{$result}' session ID returned by module {$className}'.", E_USER_WARNING);
				}
			}
		}
		return false;
	}
	
	/**
	 * Tries to collect login information from POST data,
	 * and calls the 'sodiumUserGetLogin' hook for the same reason.
	 * @return mixed The discovered logins or false.
	 */
	protected static function getLogin() {
		$logins = array();
		// NOTE: This is not moved into a module, to make it faster.
		if (!empty($_POST['login']) and !empty($_POST['password'])) {
			$logins[] = array(
				'type' => 'LOGIN_AND_PASSWORD',
				'login' => $_POST['login'],
				'password' => $_POST['password'],
			);
		}
		// Call modules for login information
		$data = Modules::callHooks('sodiumUserGetLogin');
		if ($data !== null) {
			foreach ($data as $moduleName => $result) {
				if ($result !== null) {
					if (isset($result['type'])) {
						$logins[] = $result;
					} else {
						$logins = array_merge($logins, $result);
					}
				}
			}
		}
		if (count($logins) > 0) {
			return $logins;
		} else {
			return false;
		}
	}
	
	/**
	 * Tries to log in the user by calling the 'sodiumUserLogin' hook
	 * for each login information.
	 * @param mixed $data The login information.
	 * @return array The first successful reply or all the failed replies.
	 */
	protected static function verifyLogin($loginData) {
		foreach ($loginData as $data) {
			$data = Modules::callHooks('sodiumUserVerifyLogin', $loginData);
			if ($data !== null) {
				foreach ($data as $className => $reply) {
					if ($reply === null) {
						unset($data[$className]);
					} else if ($reply['success'] == true) {
						return $reply;
					}
				}
			}
		}
		// No success with verification
		return array('success' => false);
	}

	/**
	 * Log out the current user.
	 * @return bool True on success, false otherwise.
	 */
	public static function logOut() {
		self::$currentUser = null;
		if (!session_destroy()) {
			trigger_error(__CLASS__ . '::' . __FUNCTION__ . " : Failed to destroy the session.", E_USER_WARNING);
			return false;
		} else {
			return true;
		}
	}

}
