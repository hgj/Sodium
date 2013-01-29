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

/**
 * Sodium / index.php
 *
 * Responsible for general (usual) initialization processes.
 */

define('START_TIME', microtime(true));

// Load configuration file
// DOC: Modify the path here, if you moved index.php. And remember, you will
// have to modify it after every update of the framework!
if (!@include_once('configuration.php')) {
	if (!@include_once('default-configuration.php')) {
		trigger_error("Sodium\index.php : Can not load configuration from 'configuration.php' or 'default-configuration.php'. Please check your installation!", E_USER_ERROR);
	}
}

// Do any initialization neccessary
Sodium::initialise();

// Parse the query string
Sodium::parseQuery();

// Redirect if a rule/filter is matching the requested URL
Sodium::doRedirect();

// Get (or guess) the controller responsible for the request,
// then call the controller and render the page
// TODO: Modules
Sodium::callController(Sodium::getController(Sodium::getQuery()));

// Redirect after the controller/module done its work if needed
Sodium::doRedirectAfter();

// Render the page
Sodium\View::render();

// Finalise the framework
Sodium::finalise();
