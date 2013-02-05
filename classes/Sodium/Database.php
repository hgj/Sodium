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
 * Sodium / DatabaseObject class
 * 
 * This class manages PDO connections to databases.
 */
class Database {
	
	// The array of stored PDO classes in the format: 'name' => PDO.
	protected static $connections = array();
	
	// The name of the default connection. Can not be null if there are
	// connections defined.
	protected static $defaultConnection = null;
	
	/**
	 * Retrieve a stored connection.
	 * @param string $name The name of the connection to return.
	 * @return mixed The connection (a PDO class) on success, false otherwise.
	 */
	public static function getConnection($name) {
		if (isset(self::$connections[$name])) {
			return self::$connections[$name];
		} else {
			return false;
		}
	}
	
	/**
	 * Retrieve the default connection.
	 * @return mixed The connection (a PDO class) on success, false otherwise.
	 */
	public static function getDefaultConnection() {
		if (count(self::$connections) > 0 and self::$defaultConnection != null) {
			return self::$connections[self::$defaultConnection];
		} else {
			return false;
		}
	}
	
	/**
	 * Add a new connection.
	 * The very first connection added will become the default one.
	 * @param string $name The name of the connection.
	 * @param \PDO $pdo The PDO object for the connection.
	 * @return boolean True on success, false otherwise.
	 */
	public static function addConnection($name, $pdo) {
		if (!empty($name) and $pdo instanceof \PDO) {
			self::$connections[$name] = $pdo;
			if (empty(self::$defaultConnection)) {
				self::$defaultConnection = $name;
			}
			return true;
		} else {
			// Trigger a warning, as this problem may cause some trouble.
			trigger_error(__CLASS__ . '::' . __FUNCTION__ . " : Failed to add connection as the supplied arguments are invalid.", E_USER_WARNING);
			return false;
		}
	}
	
	/**
	 * Set the default connection to the specified one.
	 * @param string $name The name of the connection to set as default.
	 * @return boolean True on success, false otherwise.
	 */
	public static function setDefaultConnection($name) {
		if (isset(self::$connections[$name])) {
			self::$defaultConnection = $name;
			return true;
		} else {
			return false;
		}
	}
	
}