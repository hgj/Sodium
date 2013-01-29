<?php

//
// Sodium 2.0.7-alpha
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
	// Constants
	//
	
	const SaveModeManual = 1;
	const SaveModeOnChange = 2;
	
	protected static $keys = null;
	
	protected static $associations = null;
	
	protected static $saveMode = static::SaveModeManual;
	
	public function __construct($values) {
		// Convert $values to array if neccessary
		if (count(static::$keys) == 1 and !is_array($values)) {
			$values = array(
				 array_shift(array_keys(static::$keys)) => $values
			);
		}
	}
	
	//
	// Object part
	//
	
	protected $attributes = null;
	
	protected $newAttributes = null;
	
	public function __isset($name) {
		return isset($this->attributes[$name]);
	}
	
	public function __get($name) {
		return $this->attributes[$name];
	}
	
	public function __set($name, $value) {
		$this->newAttributes[$name] = $value;
		if (static::$saveMode == static::SaveModeOnChange) {
			$this->save();
		}
	}	
	
}
