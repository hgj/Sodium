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

/**
 * Sodium / cron.php
 *
 * Responsible for running the cron jobs in each module.
 */

define('START_TIME', microtime(true));

// Define CRON, so we know this is not a "normal" run
define('CRON', true);

// Public access security
// NOTE: cron.php should not be moved to be accessible for the public,
// in contrast with index.php. But if you do move it, here is a little
// protection.
$secretKey = 'YOU HAVE TO CHANGE THIS TO SOMETHING RANDOM (OR DYNAMIC)';
if (PHP_SAPI != 'cli') {
	if ($_POST['secretKey'] != $secretKey and $_GET['secretKey'] != $secretKey) {
		die();
	}
}

// Load configuration file
// NOTE: If you moved this file, modify the path to the configuration file here.
if (!@include_once('configuration.php')) {
	if (!@include_once('default-configuration.php')) {
		trigger_error("Sodium\cron.php : Can not load configuration from 'configuration.php' or 'default-configuration.php'. Please check your installation!", E_USER_ERROR);
	}
}

Sodium\Modules::initialise();

Sodium\Modules::callHooks('sodiumCron');