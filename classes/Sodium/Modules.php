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
 * Sodium / Modules class
 *
 * Initialises and loads neccessary modules, plugins.
 */
class Modules {

	// Available hooks in the framework. (See the example module for details.)
	protected static $hooks = array(
		// General Sodium
		'sodiumInitialise',
		'sodiumInitialised',
		'sodiumFinalise',
		'sodiumFinalised',
		'sodiumCron',
		// User related
		'sodiumUserGetSessionID',
		'sodiumUserGetLogin',
		'sodiumUserVerifyLogin',
		'sodiumUserAfterLogIn',
		'sodiumUserBeforeLogOut',
	);
	// One instance of the configuration.
	protected static $instance = false;
	// One instance of each module registered.
	protected static $moduleInstances = array();

	/**
	 * Initialises the configuration of the modules.
	 */
	public static function initialise() {
		// Create the configuration if missing
		if (self::$instance == null) {
			// Load it from cache if possible
			$cacheFile = BASE_PATH . '/cache/modules.cache';
			if (file_exists($cacheFile)) {
				// NOTE: unserialize() would also issue an E_NOTICE on failure.
				self::$instance = @unserialize(file_get_contents($cacheFile));
			}
			if (self::$instance === false) {
				// Or generate it now, and save it for later use
				self::$instance = new Modules();
				$files = new \RecursiveIteratorIterator(
					new \RecursiveDirectoryIterator(BASE_PATH . '/modules', \FilesystemIterator::FOLLOW_SYMLINKS)
				);
				foreach ($files as $file) {
					$className = str_replace('.php', '', basename($file));
					include_once($file);
					if (class_exists($className, false)) {
						// Hook the module's methods, if available
						foreach (self::$hooks as $hookName) {
							// TODO: Performance check: method_exists vs ReflectionClass::hasMethod
							if (method_exists($className, $hookName)) {
								self::$instance->registerHook($className, $hookName);
							}
						}
					} else {
						// Skip the file as it is not a valid Sodium module
						trigger_error(__CLASS__ . '::' . __FUNCTION__ . " : Skipping '" . str_replace(BASE_PATH . '/modules/', '', $file). "' as it does not contain the class '$className'.", E_USER_NOTICE);
					}
				}
				// Cache the configuration if not disabled
				if (!file_exists(BASE_PATH . '/cache/modules.nocache')) {
					file_put_contents($cacheFile, serialize(self::$instance), LOCK_EX);
				}
			}
		}
	}
	
	/**
	 * Finalises the Modules class.
	 */
	public static function finalise() {
		// Nothing?
	}

	/**
	 * Calls all the modules that are registered for the specific hook.
	 * @param string $hookName The name of the hook.
	 * @param array $parameters An array with the parameters that every modules receives.
	 * @return array Array of results on success, false otherwise.
	 */
	public static function callHooks($hookName, $parameters = array()) {
		if (array_search($hookName, self::$hooks) !== false) {
			if (!empty(self::$instance->registeredHooks[$hookName])) {
				return self::$instance->callAllHooks($hookName, $parameters);
			} else {
				// NOTE: No warning/notice is triggered here, we do not want to flood the log with not used hooks
				return null;
			}
		} else {
			trigger_error(__CLASS__ . '::' . __FUNCTION__ . " : Trying to call a not existing hook '{$hookName}'.", E_USER_WARNING);
			return false;
		}
	}
	
	/**
	 * Calls a specific module that is registered for the specified hook.
	 * @param string $className The class to call the hook on.
	 * @param string $hookName The name of the hook.
	 * @param array $parameters An array with the parameters that the modules receives.
	 */
	public static function callHook($className, $hookName, $parameters = array()) {
		if (array_search($hookName, self::$hooks) !== false) {
			if (empty(self::$instance->registeredHooks[$hookName]) or array_search($className, self::$instance->registeredHooks[$hookName]) === false) {
				trigger_error(__CLASS__ . '::' . __FUNCTION__ . " : Trying to call hook '{$hookName}' on class '{$className}', but it is not registered for that hook.", E_USER_WARNING);
				return null;
			} else {
				return array($className => self::$instance->callMethod($className, $hookName, $parameters));
			}
		} else {
			trigger_error(__CLASS__ . '::' . __FUNCTION__ . " : Trying to call a not existing hook '{$hookName}'.", E_USER_WARNING);
			return false;
		}
	}

	//
	// Object part, handling the loaded configuration
	//

	// The list of modules for each registered hook.
	protected $registeredHooks = false;

	/**
	 * Register a module for the specified hook.
	 * @param string $className The name of the module.
	 * @param string $hookName The name of the hook.
	 * @return bool True on success, false otherwise.
	 */
	protected function registerHook($className, $hookName) {
		if (array_search($hookName, self::$hooks) !== false) {
			$this->registeredHooks[$hookName][] = $className;
			return true;
		} else {
			trigger_error(__CLASS__ . '::' . __FUNCTION__ . " : Trying to register '{$className}' for a not existing hook '{$hookName}'.", E_USER_WARNING);
			return false;
		}
	}

	/**
	 * Call the specified hook on all modules.
	 * @param string $hookName The name of the hook.
	 * @param array $parameters An array with the parameters that every modules receives.
	 * @return array An array with the results of each call or null if no hooks are registered.
	 */
	protected function callAllHooks($hookName, $parameters = array()) {
		$results = array();
		foreach ($this->registeredHooks[$hookName] as $className) {
			$results[$className] = $this->callMethod($className, $hookName, $parameters);
		}
		return $results;
	}
	
	/**
	 * Call a method of a module.
	 * @param string $className The name of the class to call the method on.
	 * @param string $method The name of the method to call.
	 * @param array $parameters The only parameter passed to the method.
	 * @return mixed The return value of the called method (call_user_func)
	 */
	protected function callMethod($className, $method, $parameters) {
		if (!isset(self::$moduleInstances[$className])) {
			self::$moduleInstances[$className] = new $className();
		}
		// TODO: Performance check: Reflection vs $class->{$method}($parameter) vs call_user_func()
		return call_user_func(array(self::$moduleInstances[$className], $method), $parameters);
	}

}
