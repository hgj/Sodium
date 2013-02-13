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
 * Sodium / Pages controller
 *
 * Responsible for loading (not necessarily static) pages from content/pages.
 * The pages can contain PHP code, and the controller even scans through
 * the query's path for include.php files.
 */

// Contstruct a path from the query
$path = Sodium::getQueryString();

// Get the file requested or (if we have such) a directory with an index.html
// The priority is: .php, .html, .htm or an index.* ending as such
// in the named folder
// TODO: Configuration::getRemovableExtensions() and General::getFilesPCRE()
$files = glob(BASE_PATH . "/content/pages/$path.{php,xhtml,html,htm}", GLOB_BRACE);
if ($files === false) {
	$files = glob(BASE_PATH . "/content/pages/$path/index.{php,xhtml,html,htm}", GLOB_BRACE);
}

// Return 404 if the file does not exist
if ($files === false) {
	// If a default controller is set, other then 'pages', call it.
	if (Sodium::getDefaultController() != 'pages' and Sodium::usePages()) {
		Sodium::callController(Sodium::getDefaultController());
	} else {
		// If we were the last resort, return an error page.
		Sodium\General::errorPage(404);
		return 404;
	}
}

// Return 403 if the file is above the allowed path
if (strpos(realpath($files[0]), BASE_PATH . '/content/pages/') !== 0) {
	// TODO: We might want to log this as a security event.
	// If a default controller is set, other then 'pages', call it.
	if (Sodium::getDefaultController() != 'pages' and Sodium::usePages()) {
		Sodium::callController(Sodium::getDefaultController());
	} else {
		// If we were the last resort, return an error page.
		Sodium\General::errorPage(403);
		return 403;
	}
}

// Scan for include.php files through the path
$includePath = BASE_PATH . '/content/pages';
if (file_exists($includePath . '/include.php')) {
	require($includePath . '/include.php');
}
foreach (explode('/',$path) as $part) {
	$includePath .= "/$part";
	if (file_exists($includePath . '/include.php') and strpos(realpath($includePath), BASE_PATH . '/content/pages/') === 0) {
		require($includePath . '/include.php');
	}
}

// Run the PHP and get the content from the page
ob_start();
require($files[0]);
$content = ob_get_contents();
ob_end_clean();

Sodium\View::setContent($content);

// TODO: Where does this return go? :)
return 200;
