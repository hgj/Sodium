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

/**
 * Sodium / Example module
 *
 * Implements all available hooks for the framework,
 * and thus servers as an example.
 */
class ExampleModule {
	
	//
	// General hooks
	//
	
	/**
	 * Called right after the Module system has been initialised.
	 * @param array $parameters Empty array.
	 */
	function sodiumInitialise($parameters) {
	}
	
	/**
	 * Called after all Sodium components have been initialised.
	 * @param array $parameters Empty array.
	 */
	function sodiumInitialised($parameters) {
	}
	
	/**
	 * Called first when the finalisation process starts.
	 * @param array $parameters Empty array.
	 */
	function sodiumFinalise($parameters) {
	}
	
	/**
	 * Called after all Sodium components (except the Module system and the core)
	 * have been finalised.
	 * @param array $parameters Empty array.
	 */
	function sodiumFinalised($parameters) {
	}

	//
	// User related hooks
	//
	
	/**
	 * Should try to get the session's ID.
	 * @param array $parameters Empty array.
	 * @return mixed Boolean false on failure, ID otherwise.
	 */
	function sodiumUserGetSessionID($parameters) {
		return false;
	}
	
	/**
	 * Should try to get login information about the user.
	 * @param array $parameters Empty array.
	 * @return array Array with single login information or array of those.
	 */
	function sodiumUserGetLogin($parameters) {
	}
	
	/**
	 * Called when login information is available.
	 * May be called more than once if multiple login information is available.
	 * @param array $parameters Contains the data for authentication.
	 * @return array Array with a 'success' key to identify the result.
	 */
	function sodiumUserVerifyLogin($parameters) {
	}

	/**
	 * Called after the user logged in.
	 * @param array $parameters Contains the user's ID in 'ID'.
	 */
	function sodiumUserAfterLogIn($parameters) {
	}
	
	/**
	 * Called after the user logged out.
	 * @param array $parameters Contains the user's ID in 'ID'.
	 */
	function sodiumUserBeforeLogOut($parameters) {
	}

}
