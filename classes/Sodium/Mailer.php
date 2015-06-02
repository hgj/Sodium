<?php

//
// Sodium 2.1.0-alpha
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
 * Sodium / Mailer class
 *
 * The Mailer class provides template based e-mail sending and basic e-mail
 * related functions (like header encoding and e-mail address verification).
 *
 * Mail templates can receive parameters to customize the letter contents
 * and even modify the headers themselves.
 */
class Mailer {

	//
	// Configuration
	//

	// Default headers
	protected static $defaultHeaders = array();

	/**
	 * Returns the list of the configured default headers for e-mails.
	 * @return array The list of the configured default headers.
	 */
	public static function getDefaultHeaders() {
		return self::$defaultHeaders;
	}

	/**
	 * Adds a header to the default headers.
	 * @param string $field The header field.
	 * @param string $value The value of the field.
	 */
	public static function addDefaultHeader($field, $value) {
		self::$defaultHeaders[$field] = $value;
	}

	/**
	 * Encodes a header in UTF-8 Base64.
	 * @param string $string The string to be encoded.
	 * @return string The encoded string.
	 */
	public static function encode($string) {
		return '=?utf-8?B?' . base64_encode($string) . '?=';
	}

	/**
	 * Validates an e-mail address.
	 * @param string $email The e-mail address to be checked.
	 * @param bool $mxCheck Do a DNS MX entry check on the address?
	 * @return bool True, if the address seems valid, false otherwise.
	 */
	public static function validAddress($email, $mxCheck = false) {
		if (preg_match("/^[^0-9][A-z0-9_-]+[@][A-z0-9_-]+([.][A-z0-9_-]+)*[.][A-z]{2,4}$/i", $email)) {
			list( , $domain) = explode('@', $email);
			if ($mxCheck) {
				return checkdnsrr($domain, 'MX');
			} else {
				return true;
			}
		} else {
			return false;
		}
	}

	// TODO: sendTemplate and others?
}
