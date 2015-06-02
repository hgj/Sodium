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
 * Sodium / DatabaseObject class
 *
 * This class provides easy access to objects (data) stored in databases.
 * Contains built in mechanism to manage associations and some related procedures.
 */
class DatabaseObject {

	// The database connection to use. If none is specified the default will be used.
	protected static $connection = null;

	/**
	 * Return the connection to be used with this class.
	 * @return \PDO The PDO class for the connection.
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
			list(, $name) = explode('\\', __CLASS__);
			return $name;
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
		static $triedToRetrieve = false;
		if (static::$attributes == null) {
			// The attributes are not specified, try to get it from the DB
			if ($triedToRetrieve == false) {
				if (static::retrieveAttributes()) {
					return static::$attributes;
				} else {
					return false;
				}
			} else {
				// We already tried it
				return false;
			}
		} else {
			return static::$attributes;
		}
	}

	protected static function retrieveAttributes() {
		$connection = static::getConnection();
		$query = 'SHOW COLUMNS FROM ' . static::getName();
		$statement = $connection->query($query);
		if ($statement === false) {
			static::triggerDatabaseError(__CLASS__ . '::' . __FILE__, "Failed to retrieve attributes from database. Failed to run the query.", E_USER_WARNING, $connection->errorInfo(), $query);
			return false;
		}
		$rows = $statement->fetchAll(\PDO::FETCH_ASSOC);
		if ($rows === false) {
			static::triggerDatabaseError(__CLASS__ . '::' . __FUNCTION__, 'Failed to retrieve attributes from database. Failed to fetch the results.', E_USER_WARNING, $statement->errorInfo(), $query);
			return false;
		}
		foreach ($rows as $row) {
			$attributeName = $row['Field'];
			$attributeOptions['type'] = $row['Type'];
			$attributeOptions['notNull'] = ($row['Null'] == "NO");
			if ($row['Default'] !== null) {
				$attributeOptions['default'] = $row['Default'];
			}
			$attributeIsKey = ($row['Key'] == "PRI");
			$attributeExtra = $row['Extra'];
			static::$attributes[$attributeName] = $attributeOptions;
			if ($attributeIsKey) {
				$keyOptions = array();
				if (preg_match('/auto_increment/', $attributeExtra) !== false) {
					$keyOptions['autoIncrement'] = true;
				}
				static::$keys[$attributeName] = $keyOptions;
			}
		}
		return true;
	}

	public static function triggerDatabaseError($source, $message, $level, $errorInfo = null, $query = null) {
		if ($errorInfo != null) {
			$message .= " SQLSTATE error code is '{$errorInfo[0]}'. Driver said: '{$errorInfo[1]}' => '{$errorInfo[2]}'.";
		}
		if ($query != null) {
			$message .= " The query was: '{$query}'.";
		}
		trigger_error($source . ' : ' . $message, $level);
	}

	public static function search($criterias = null) {
		$connection = static::getConnection();
		$query = 'SELECT * FROM ' . static::getName();
		if ($criterias !== null) {
			$validOperators = array('=', '<', '>', '<=', '>=', 'LIKE', 'BETWEEN');
			$where = array();
			foreach ($criterias as $name => $data) {
				if (count($data) == 1) {
					// This is a simple equals
					array_unshift($data, '=');
				}
				$data[0] = strtoupper($data[0]);
				if (array_search($data[0], $validOperators) === false) {
					trigger_error(__CLASS__ . '::' . __FUNCTION__ . " : Could not start search. Invalid operator '{$data[0]}' for search.", E_USER_WARNING);
					return false;
				}
				if ($data[0] == 'BETWEEN') {
					if (count($data) != 3) {
						trigger_error(__CLASS__ . '::' . __FUNCTION__ . " : Could not start search. Operator BETWEEN needs two arguments for search.", E_USER_WARNING);
						return false;
					}
					$where[] = "{$name} BETWEEN :{$name}_1 AND :{$name}_2";
				} else {
					$where[] = "{$name} {$data[0]} :{$name}";
				}
			}
			$query .= ' WHERE (' . implode(' AND ', $where) . ')';
		}
		$statement = $connection->prepare($query);
		if ($statement === false) {
			static::triggerDatabaseError(__CLASS__ . '::' . __FUNCTION__, 'Could not start search. Failed to prepare statement.', E_USER_WARNING, $connection->errorInfo(), $query);
			return false;
		}
		if ($criterias !== null) {
			foreach ($criterias as $name => $data) {
				if ($data[0] == 'BETWEEN') {
					$result = $statement->bindValue("{$name}_1", $data[1]) and $statement->bindValue("{$name}_2", $data[2]);
				} else {
					$result = $statement->bindValue($name, $data[1]);
				}
				if ($result === false) {
					static::triggerDatabaseError(__CLASS__ . '::' . __FUNCTION__, "Could not start search. Failed to bind value(s) for key '{$name}'.", E_USER_WARNING, $statement->errorInfo(), $query);
					return false;
				}
			}
		}
		if ($statement->execute()) {
			return $statement;
		} else {
			static::triggerDatabaseError(__CLASS__ . '::' . __FUNCTION__, 'Could not start search. Failed to execute statement.', E_USER_WARNING, $statement->errorInfo(), $query);
			return false;
		}
	}

	//
	// Object part, handling an actual instance of the class (a row in the table)
	//

	const STATE_NEW = "new";
	const STATE_EXISTING = "existing";

	protected $state = null;

	protected $oldAttributes = array();

	protected $newAttributes = array();

	/**
	 * Creates a new, empty object or retrieves an existing one
	 * @param mixed $values
	 * @throws \Exception if the object can not be retrieved.
	 */
	public function __construct($values = null) {
		if ($values != null) {
			// This is an existing object
			if (!is_array($values) and count(static::getKeys()) == 1) {
				// The argument is a value for that one key, convert it to the standard format.
				$values = array(
					array_keys(static::getKeys())[0] => $values
				);
			}
			// Check values
			foreach (static::getKeys() as $key => $options) {
				if (!isset($values[$key])) {
					throw new \Exception(__CLASS__ . '::' . __FUNCTION__ . " : Missing key '{$key}'.");
				}
			}
			$this->oldAttributes = $values;
			// Try to load the object
			if (!$this->load()) {
				throw new \Exception(__CLASS__ . '::' . __FUNCTION__ . " : Could not load object.");
			}
		} else {
			$this->state = static::STATE_NEW;
		}
	}

	protected function load() {
		$connection = static::getConnection();
		$query = 'SELECT * FROM ' . static::getName();
		$where = array();
		foreach (static::getKeys() as $key => $options) {
			$where[] = "{$key} = :{$key}";
		}
		$query .= ' WHERE (' . implode(' AND ', $where) . ');';
		$statement = $connection->prepare($query);
		if ($statement === false) {
			static::triggerDatabaseError(__CLASS__ . '::' . __FUNCTION__, 'Could not load object. Failed to prepare the PDOStatement.', E_USER_WARNING, $connection->errorInfo(), $query);
			return false;
		}
		foreach (static::$keys as $key => $options) {
			if (!$statement->bindValue(":{$key}", $this->oldAttributes[$key])) {
				static::triggerDatabaseError(__CLASS__ . '::' . __FUNCTION__, "Could not load object. Failed to bind value '{$this->oldAttributes[$key]}' for '{$key}'.", E_USER_WARNING, $statement->errorInfo(), $query);
				return false;
			}
		}
		if (!$statement->execute()) {
			static::triggerDatabaseError(__CLASS__ . '::' . __FUNCTION__, 'Could not load object. Failed to execute the PDOStatement.', E_USER_WARNING, $statement->errorInfo(), $query);
			return false;
		}
		$this->oldAttributes = $statement->fetch(\PDO::FETCH_ASSOC);
		if ($this->oldAttributes === false) {
			static::triggerDatabaseError(__CLASS__ . '::' . __FUNCTION__, 'Could not load object. Failed to fetch the results.', E_USER_WARNING, $statement->errorInfo(), $query);
			return false;
		}
		$this->state = "existing";
		return true;
	}

	protected function insert() {
		if ($this->state != static::STATE_NEW) {
			trigger_error(__CLASS__ . '::' . __FUNCTION__ . ' : Can not insert an existing object.');
			return false;
		}
		// Verify required attributes
		foreach (static::getAttributes() as $name => $options) {
			if (isset($options['notNull']) and $options['notNull'] == true) {
				if (empty($this->newAttributes[$name])) {
					if (isset($options['default'])) {
						// Has a default value, set it
						$this->newAttributes[$name] = $options['default'];
					} else if (
							array_key_exists($name, static::getKeys()) !== false
							and array_key_exists('autoIncrement', static::getKeys()[$name])
							and static::getKeys()[$name]['autoIncrement']
					) {
						// It is a key, that is automatically generated, ignore
						continue;
					} else {
						trigger_error(__CLASS__ . '::' . __FUNCTION__ . ' : Could not insert object.'
								. " Required attribute '{$name}' is missing.", E_USER_WARNING);
						return false;
					}
				}
			}
		}
		$connection = static::getConnection();
		$query = 'INSERT INTO ' . static::getName();
		$fields = $values = array();
		foreach ($this->newAttributes as $name => $value) {
			$fields[] = "{$name}";
			$values[] = ":{$name}";
		}
		$query .= ' (' . implode(', ', $fields) . ') VALUES (' . implode(', ', $values) . ')';
		$statement = $connection->prepare($query);
		if ($statement === false) {
			static::triggerDatabaseError(__CLASS__ . '::' . __FUNCTION__, 'Could not insert object. Failed to prepare statement.', E_USER_WARNING, $connection->errorInfo(), $query);
			return false;
		}
		foreach ($this->newAttributes as $name => $value) {
			if (!$statement->bindValue(":{$name}", $value)) {
				static::triggerDatabaseError(__CLASS__ . '::' . __FUNCTION__, "Could not insert object. Failed to bind value '{$value}' for key '{$name}'.", E_USER_WARNING, $statement->errorInfo(), $query);
				return false;
			}
		}
		if (!$statement->execute()) {
			static::triggerDatabaseError(__CLASS__ . '::' . __FUNCTION__, 'Could not insert object. Failed to execute statement.', E_USER_WARNING, $statement->errorInfo(), $query);
			return false;
		}
		$this->state = static::STATE_EXISTING;
		return true;
	}

	protected function update() {
		if ($this->state !== static::STATE_EXISTING) {
			trigger_error(__CLASS__ . '::' . __FUNCTION__ . ' : Can not update a new object.');
		}
		// Gather changed attributes
		$changedAttributes = array();
		foreach ($this->newAttributes as $name => $value) {
			if ($value !== $this->oldAttributes[$name]) {
				$changedAttributes[$name] = $value;
			}
		}
		if (empty($changedAttributes)) {
			// No changed attributes, no changes, but we return true
			return true;
		}
		$connection = static::getConnection();
		$query = 'UPDATE ' . static::getName();
		$fields = array();
		foreach ($changedAttributes as $name => $value) {
			$fields[] = "{$name} = :{$name}";
		}
		$where = array();
		foreach (static::getKeys() as $key => $options) {
			$where[] = "{$key} = :K_{$key}";
		}
		$query .= ' SET ' . implode(', ', $fields) . ' WHERE (' . implode(' AND ', $where) . ')';
		$statement = $connection->prepare($query);
		if ($statement === false) {
			static::triggerDatabaseError(__CLASS__ . '::' . __FUNCTION__, 'Could not update object. Failed to prepare statement.', E_USER_WARNING, $connection->errorInfo(), $query);
			return false;
		}
		foreach ($changedAttributes as $name => $value) {
			if (!$statement->bindValue(":{$name}", $value)) {
				static::triggerDatabaseError(__CLASS__ . '::' . __FUNCTION__, "Could not update object. Failed to bind value '{$value}' for key '{$name}'.", E_USER_WARNING, $statement->errorInfo(), $query);
				return false;
			}
		}
		foreach (static::getKeys() as $name => $options) {
			if (!$statement->bindValue(":K_{$name}", $this->oldAttributes[$name])) {
				static::triggerDatabaseError(__CLASS__ . '::' . __FUNCTION__, "Could not update object. Failed to bind value '{$this->oldAttributes[$name]}' for key '{$name}'.", E_USER_WARNING, $statement->errorInfo(), $query);
				return false;
			}
		}
		if (!$statement->execute()) {
			static::triggerDatabaseError(__CLASS__ . '::' . __FUNCTION__, 'Could not update object. Failed to execute statement.', E_USER_WARNING, $statement->errorInfo(), $query);
			return false;
		}
		return true;
	}

	public function save() {
		if ($this->state == static::STATE_NEW) {
			// Create the new row
			return $this->insert();
		} else if ($this->state == static::STATE_EXISTING) {
			// Update the existing row
			return $this->update();
		} else {
			throw new \Exception(__CLASS__ . '::' . __FUNCTION__ . " : Could not save object. It is in an unkown state.");
		}
	}

	public function __isset($name) {
		return isset($this->oldAttributes[$name]);
	}

	public function __get($name) {
		return $this->oldAttributes[$name];
	}

	public function __set($name, $value) {
		$this->newAttributes[$name] = $value;
		// NOTE: Automatic save mode is not available.
	}

}
