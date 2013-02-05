<?php

//
// Sodium 2.0.9-alpha
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
 * Sodium / autoload.php
 *
 * Sodium's autoload function.
 */

/**
 * Tries to load the class from the classes directory if it is not available yet.
 * @staticvar null $files The array storing the files in the classes directory.
 * @param string $className The name of the class to load.
 */
function __autoload($className) {
	static $directories = NULL;
	if (!class_exists($className, false)) {
		// Fill the $files array if NULL
		if ($directories === NULL) {
			$iterator = new \AppendIterator();
			$iterator->append(
				new \ParentIterator(
					new \RecursiveDirectoryIterator(BASE_PATH . '/classes',
						\FilesystemIterator::FOLLOW_SYMLINKS)
				)
			);
			$iterator->append(
				new \ParentIterator(
					new \RecursiveDirectoryIterator(BASE_PATH . '/modules',
						\FilesystemIterator::FOLLOW_SYMLINKS)
				)
			);
			$directories[] = BASE_PATH . '/classes';
			$directories[] = BASE_PATH . '/modules';
			foreach ($iterator as $directory) {
				$directories[] = $directory;
			}
		}
		// Search for the class
		$fileName = str_replace('\\', '/', $className) . '.php';
		foreach ($directories as $directory) {
			$file = $directory . '/' . $fileName;
			if (file_exists($file) !== false) {
				require_once($file);
				return;
			}
		}
	}
}
