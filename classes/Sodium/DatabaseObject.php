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
 * Sodium / DatabaseObject class
 *
 * This class provides easy access to objects (data) stored in databases.
 * Contains built in mechanism to manage associations and some related procedures.
 */
class DatabaseObject {
	
	//
	// Database connection
	//
	
	// The database connection to use. If none is specified the default will be used.
	protected static $connection = null;
	
	/**
	 * Return the connection to be used with this class.
	 * @return PDO The PDO class for the connection.
	 */
	public static function getConnection() {
		if (static::$connection == null) {
			return Database::getDefaultConnection();
		} else {
			return Database::getConnection(static::$connection);
		}
	}
	
	// The name of the class if differs in the database
	protected static $name = null;
	
	public static function getName() {
		if (static::$name == null) {
			list(,$name) = explode('\\', __CLASS__);
		} else {
			return static::$name;
		}
	}
	
	// The key attributes that identify an object in the database.
	// Structure: array( 'key' => array(config) );
	protected static $keys = null;
	
	public static function getKeys() {
		return static::$keys;
	}
	
	// All the associations this class has with others
	// Structure: array( 'association alias' => array(config) );
	protected static $associations = null;
	
	public static function getAssociations() {
		return static::$associations;
	}
	
	// The attributes this class has. May be filled automatically if not specified.
	protected static $attributes = null;
	
	/**
	 * Get the attributes for this class
	 * @return mixed The attributes on success, false otherwise.
	 */
	public static function getAttributes() {
		if (static::$attributes == null) {
			// The attributes are not specified, try to get it from the DB
			if (static::retrieveAttributes()) {
				return static::$attributes;
			} else {
				return false;
			}
		} else {
			return static::$attributes;
		}
	}
	
	protected static function retrieveAttributes() {
		// TODO
		return false;
	}


	//
	// Object part, handling an actual instance of the class (a row in the table)
	//
	
	protected $attributes = null;
	
	protected $newAttributes = null;

	/**
	 * Creates a new, empty object or retrieves an existing one
	 * @param type $values
	 */
	public function __construct($values = null) {
		if ($values != null) {
			// This is an existing object
			if (!is_array($values) and count(static::$keys) == 1) {
				// The argument is a value for that one key, convet it to the standard format.
				$values = array(
					array_shift(static::$keys) => $values
				);
			}
			// Check values
			foreach (static::$keys as $key => $options) {
				if (!isset($values[$key])) {
					throw new \Exception(__CLASS__ . '::' . __FUNCTION__ . " : Missing key '{$key}'.");
				}
			}
			$this->attributes = $values;
			// Try to load the object
			if (!$this->load()) {
				throw new \Exception(__CLASS__ . '::' . __FUNCTION__ . " : Could not load object.");
			}
		} else {
			// This is a new object, we do not have to do anything here (I guess)
		}
	}
	
	protected function load() {
		$connection = static::getConnection();
		$query = 'SELECT * FROM ' . static::getName() . ' WHERE ';
		foreach (static::$keys as $key => $options) {
			$where[] = "$key = :$key";
		}
		$query .= '( ' . implode(' AND ',$where) . ');';
		try {
			$statement = $connection->prepare($query);
			if ($statement === false) {
				$errorInfo = $connection::errorInfo();
				trigger_error(__CLASS__ . '::' . __FUNCTION__ . " : Could not load object. Failed to prepare the PDOStatement. SQLSTATE error code is '{$errorInfo[0]}'. Driver said: '{$errorInfo[1]}' => '{$errorInfo[2]}'.", E_USER_WARNING);
				return false;
			}
		} catch (\PDOException $e) {
			$errorInfo = $e->errorInfo;
			trigger_error(__CLASS__ . '::' . __FUNCTION__ . " : Could not load object. Failed to prepare the PDOStatement. SQLSTATE error code is '{$errorInfo[0]}'. Driver said: '{$errorInfo[1]}' => '{$errorInfo[2]}'.", E_USER_WARNING);
			return false;
		}
		foreach (static::$keys as $key => $options) {
			if (!$statement->bindValue(":$key", $this->attributes[$key])) {
				$errorInfo = $statement::errorInfo();
				trigger_error(__CLASS__ . '::' . __FUNCTION__ . " : Could not load object. Failed to bind value. SQLSTATE error code is '{$errorInfo[0]}'. Driver said: '{$errorInfo[1]}' => '{$errorInfo[2]}'.", E_USER_WARNING);
				return false;
			}
		}
		if (!$statement->execute()) {
			$errorInfo = $statement::errorInfo();
			trigger_error(__CLASS__ . '::' . __FUNCTION__ . " : Could not load object. Failed to execute the PDOStatement. SQLSTATE error code is '{$errorInfo[0]}'. Driver said: '{$errorInfo[1]}' => '{$errorInfo[2]}'.", E_USER_WARNING);
			return false;
		}
		$this->attributes = $statement->fetch(PDO::FETCH_ASSOC);
		if ($this->attributes === false) {
			$errorInfo = $statement::errorInfo();
			trigger_error(__CLASS__ . '::' . __FUNCTION__ . " : Could not load object. Failed to fetch the results. SQLSTATE error code is '{$errorInfo[0]}'. Driver said: '{$errorInfo[1]}' => '{$errorInfo[2]}'.", E_USER_WARNING);
			return false;
		}
		return true;
	}
	
	public function save() {
		// TODO
	}
	
	public function __isset($name) {
		return isset($this->attributes[$name]);
	}
	
	public function __get($name) {
		return $this->attributes[$name];
	}
	
	public function __set($name, $value) {
		$this->newAttributes[$name] = $value;
		// NOTE: Automatic save mode is not available.
	}	
	
}
