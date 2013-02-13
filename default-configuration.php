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

/**
 * Sodium / configuration.php
 *
 * Sodium related settings and general PHP configuration.
 *
 * Every aspect of the Sodium framework is configured in this file. Also,
 * PHP configuration should be done here too, prior to the former.
 *
 * NOTE: Not renaming 'default-configuration.php' to 'configuration.php'
 * is not considered to be a mistake. Although, please keep in mind,
 * that the former will be overwritten whenever you update the framework!
 */


//
// PHP and other basic settings
//


// Custom PHP log
ini_set('error_log', 'logs/php.log');

// Error reporting and its level
error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', false);
ini_set('html_errors', false);
ini_set('magic_quotes_qpc', false);

// Domain name
if (isset($_SERVER['HTTP_HOST'])) {
	define('DOMAIN', $_SERVER['HTTP_HOST']);
} else if (isset($_SERVER['SERVER_NAME'])) {
	define('DOMAIN', $_SERVER['SERVER_NAME']);
} else {
	define('DOMAIN', 'example.com');
}

// Base path for all PHP files
// NOTE: You do not have to change this even if you moved index.php!
define('BASE_PATH', realpath(__DIR__));

// Base URL for public content
// If you put your site/application in a subdirectory,
// you should specify the base URL here.
// Example: http://example.com/myapp/index.php --> '/myapp'
// NOTE: Do not end the URL with a slash!
define('BASE_URL', '');

// Class autoloading
// NOTE: There is a reason the function is called here.
require_once(BASE_PATH . '/autoload.php');

// Timezone
// NOTE: A PHP warning is generated prior to PHP 5.4.0, if PHP has to guess
// the timezone by itself.
ini_set('date.timezone', 'Europe/Budapest');


//
// Cron boundary
// The code below is only executed in normal run, cron job will exit here.
//
if (defined('CRON')) {
	// You can put cron related configuration here
	ini_set('error_log', 'logs/cron.log');
	return;
}

	
// Session cookie settings
session_set_cookie_params(0, '/', '.' . DOMAIN);

// Disable compression
// Sodium::setCompression(false);

// Default query
// NOTE: Defaults to 'index' if not set
//Sodium::setDefaultQuery('custom/stuff.html');

// Default Controller
// NOTE: Sodium will still use the 'pages' controller - even if you use the
// method above. You can disable this behaviour by setting the second argument
// to false.
//Sodium::setDefaultController('some-search-controller');

// Routing
// You can add custom URL -> Controller filters here
//Sodium::addCustomRouting('the/url', 'controller-name');

// Custom page extensions
// These extensions will be removed from the query.
//Sodium::addRemovableExtension('.page');
//Sodium::addRemovableExtension(array('.p', '.pa', '.pag', '.page'));

// Redirection
// You can add redirection rules here. You can use perl compatible regular
// expressions with addRedirectPCRE().
/*
Sodium::addRedirect('redirect-from.html', 'http://example.com', 304);
Sodium::addRedirect('redirect-from.html', 'http://example.com');
Sodium::addRedirect('from.html', '/to.html');
Sodium::addRedirect(
	array(
		'bad-location',
		'false-address.html',
		'mistiped.hmlt',
	),
	'/good-location.html'
);
*/

// Headers to be sent
Sodium\View::setHeader('Content-type', 'text/html; charset=utf-8');


//
// MODULE SPECIFIC CONFIGURATION
//


// Default mail headers
Sodium\Mailer::addDefaultHeader('From', 'no-reply@' . DOMAIN);
Sodium\Mailer::addDefaultHeader('Content-type', 'text/plain; charset=utf-8');
