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
 * Sodium / SetGet class
 *
 * Provides an abstract class with the magic __set, __get, __isset
 * and __unset methods, with the expected functionalities implemented.
 */
abstract class SetGet {

	// The data accessed through magic methods.
    protected $setGetData;

	/**
	 * Sets the inaccessible property $what to $to.
	 * @param string $what The inaccessible property.
	 * @param mixed $to The value to set the property to.
	 */
    public function __set($what, $to) {
		$this->setGetData[$what] = $to;
    }

	/**
	 * Returns the inaccessible property $what.
	 * @param string $what The inaccessible property.
	 * @return mixed The value of the property.
	 */
    public function __get($what) {
		if (isset($this->setGetData[$what])) {
			return $this->setGetData[$what];
		}
    }

	/**
	 * Unsets the inaccessible property $what.
	 * @param string $what The name of the property.
	 */
    public function __unset($what) {
		unset($this->setGetData[$what]);
    }

	/**
	 * Tells if the inaccessible property $what is defined.
	 * @param string $what The name of the inaccessible property.
	 * @return bool True if the property is set, false otherwise.
	 */
    public function __isset($what) {
		return isset($this->setGetData[$what]);
    }

}
