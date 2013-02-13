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
 * Sodium / General helpers' class
 *
 * Contains general purpose helper methods (previously as standalone functions).
 */
class General {

	/**
	 * Get files matching a PCRE pattern from a directory tree.
	 * @param string $directory The directory to start the search in.
	 * @param string $pattern The regular expression to mach.
	 * @param bool $fullPathMatch True to match the whole path, not just the filename.
	 * @return array An array with a 'pathname => SplFileInfo' structure.
	 */
	public static function getFilesPCRE($directory, $pattern, $fullPathMatch = false) {
		$iterator = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator($directory, \FilesystemIterator::FOLLOW_SYMLINKS)
		);
		$result = array();
		foreach ($iterator as $key => $value) {
			if ($fullPathMatch === true) {
				$string = $value->getPathname();
			} else {
				$string = $value->getFilename();
			}
			if (preg_match($pattern, $string) === 1) {
				$result[$key] = $value;
			}
		}
		return $result;
	}
	
	/**
	 * Redirects (sends a Location header) to the given URL,
	 * and forces a redirection (3xx) HTTP status code.
	 * @param string $url The URL to redirect to.
	 * @param int $statusCode The HTTP status code to be sent
	 */
	public static function redirect($url, $statusCode = 303) {
		if ($statusCode < 300 or $statusCode > 399) {
			trigger_error(__CLASS__ . '::' . __FUNCTION__ . " : Invalid HTTP redirection status code '$statusCode'. Using 303 instead.", E_USER_WARNING);
			$statusCode = 303;
		}
		header("Location: $url", true, $statusCode);
		die();
	}

	/**
	 * Relying on not official standards, this method sends a Refresh header
	 * trying to make the browser refresh (or redirect) the given URL after
	 * the given timeout.
	 * @param int $timeOut The timeout in seconds
	 * @param int $url The URL to redirect to after the timeout
	 */
	public static function refresh($timeOut = 5, $url = '') {
		$value = $timeOut;
		if (!empty($url)) {
			$value .= "; url=$url";
		}
		View::setHeader("Refresh", $value);
	}

	/**
	 * Present an error page with the help of TODO
	 * @param int $number
	 */
	public static function errorPage($number) {
		// This method should be called only once.
		static $n = 0;
		$n++;
		if ($n > 1) {
			// If it is called again, we have some serious problem(s)!
			trigger_error(__CLASS__ . '::' . __FUNCTION__ . " : Critical error. This function is called more than once.", E_USER_ERROR);
			// The script should terminate here, but let me be sceptic about that.
			die("500: CRITICAL ERROR");
		}
		// http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html
		$headers = array(
			400 => 'Bad Request',
			401 => 'Unauthorized',
			402 => 'Payment Required',
			403 => 'Forbidden',
			404 => 'Not Found',
			405 => 'Method Not Allowed',
			406 => 'Not Acceptable',
			407 => 'Proxy Authentication Required',
			408 => 'Request Timeout',
			409 => 'Conflict',
			410 => 'Gone',
			411 => 'Length Required',
			412 => 'Precondition Failed',
			413 => 'Request Entity Too Large',
			414 => 'Request-URI Too Long',
			415 => 'Unsupported Media Type',
			416 => 'Requested Range Not Satisfiable',
			417 => 'Expectation Failed',
			500 => 'Internal Server Error',
			501 => 'Not Implemented',
			502 => 'Bad Gateway',
			503 => 'Service Unavailable',
			504 => 'Gateway Timeout',
			505 => 'HTTP Version Not Supported',
		);
		// End and clean all buffers
		while (@ob_end_clean());
		// Send error headers
		if (isset($headers[$number])) {
			header("HTTP/1.1 {$number}", true, $number);
		} else {
			trigger_error(__CLASS__ . '::' . __FUNCTION__ . " : Unknown HTTP error code '$number'.", E_USER_WARNING);
			// We will still try to send the header, but will not include the 'description'
			header("HTTP/1.1 {$number} {$headers[$number]}", true, $number);
		}
		// Start the output buffer again
		ob_start();
		// Serve the error page
		include(BASE_PATH . '/templates/site/errors.php');
		while (@ob_end_flush());
		// Hope the message went through...
	}

	/**
	 * Convert all or just a subset of caracters into HTML entities.
	 * @param string $text The string to be converted.
	 * @param string $pcre The PCRE pattern to match characters to convert.
	 * @param boolean $excludeMatch True if the pattern matches characters not to convert.
	 * @return string The converted string.
	 */
	public static function htmlEntities($text, $pcre = null, $excludeMatch = false) {
		// Standard
		$standardCharacters = array(
			32 => ' ', // Space , Szóköz
			33 => '!', // Exclamation mark, Felkiáltójel
			34 => '"', // Quotation mark, Idézőjel
			35 => '#', // Number sign, Kettőskereszt
			36 => '$', // Dollar sign, Dollár
			37 => '%', // Percent sign, Százalékjel
			38 => '&', // Ampersand, És-jel
			39 => "'", // Apostrophe, Aposztróf
			40 => '(', // Left parenthesis, Nyitó zárójel
			41 => ')', // Right parenthesis, Záró zárójel
			42 => '*', // Asterisk, Csillag
			43 => '+', // Plus sign, Pluszjel
			44 => ',', // Comma, Vessző
			45 => '-', // Hyphen, Minuszjel
			46 => '.', // Period, Pont
			47 => '/', // Slash, Perjel (jobbra dőlő)
			48 => '0', // digit 0, nulla
			49 => '1', // digit 1, egy
			50 => '2', // digit 2, kettő
			51 => '3', // digit 3, három
			52 => '4', // digit 4, négy
			53 => '5', // digit 5, öt
			54 => '6', // digit 6, hat
			55 => '7', // digit 7, hét
			56 => '8', // digit 8, nyolc
			57 => '9', // digit 9, kilenc
			58 => ':', // Colon, Kettőspont
			59 => ';', // Semicolon, Pontosvessző
			60 => '<', // Less-than, Kissebb mint
			61 => '=', // Equals-to, Egyenlőségjel
			62 => '>', // Greater-than, Nagyobb mint
			63 => '?', // Question mark, Kérdőjel
			64 => '@', // At sign, Kukac
			65 => 'A', // uppercase A, nagy A
			66 => 'B', // uppercase B, nagy B
			67 => 'C', // uppercase C, nagy C
			68 => 'D', // uppercase D, nagy D
			69 => 'E', // uppercase E, nagy E
			70 => 'F', // uppercase F, nagy F
			71 => 'G', // uppercase G, nagy G
			72 => 'H', // uppercase H, nagy H
			73 => 'I', // uppercase I, nagy I
			74 => 'J', // uppercase J, nagy J
			75 => 'K', // uppercase K, nagy K
			76 => 'L', // uppercase L, nagy L
			77 => 'M', // uppercase M, nagy M
			78 => 'N', // uppercase N, nagy N
			79 => 'O', // uppercase O, nagy O
			80 => 'P', // uppercase P, nagy P
			81 => 'Q', // uppercase Q, nagy Q
			82 => 'R', // uppercase R, nagy R
			83 => 'S', // uppercase S, nagy S
			84 => 'T', // uppercase T, nagy T
			85 => 'U', // uppercase U, nagy U
			86 => 'V', // uppercase V, nagy V
			87 => 'W', // uppercase W, nagy W
			88 => 'X', // uppercase X, nagy X
			89 => 'Y', // uppercase Y, nagy Y
			90 => 'Z', // uppercase Z, nagy Z
			91 => '[', // Left square bracket, Nyitó szögletes zárójel
			92 => '\\', // Backslash, Visszaperjel (balra dőlő)
			93 => ']', // Right square bracket, Záró szögletes zárójel
			94 => '^', // Caret, Kalap?
			95 => '_', // Underscore, Aláhúzás
			96 => '`', // Grave accent, balradőlő aposztróf?
			97 => 'a', // lowercase a, kis a
			98 => 'b', // lowercase b, kis b
			99 => 'c', // lowercase c, kis c
			100 => 'd', // lowercase d, kis d
			101 => 'e', // owercase e, kis e
			102 => 'f', // lowercase f, kis f
			103 => 'g', // lowercase g, kis g
			104 => 'h', // lowercase h, kis h
			105 => 'i', // lowercase i, kis i
			106 => 'j', // lowercase j, kis j
			107 => 'k', // lowercase k, kis k
			108 => 'l', // lowercase l, kis l
			109 => 'm', // lowercase m, kis m
			110 => 'n', // lowercase n, kis n
			111 => 'o', // lowercase o, kis o
			112 => 'p', // lowercase p, kis p
			113 => 'q', // lowercase q, kis q
			114 => 'r', // lowercase r, kis r
			115 => 's', // lowercase s, kis s
			116 => 't', // lowercase t, kis t
			117 => 'u', // lowercase u, kis u
			118 => 'v', // lowercase v, kis v
			119 => 'w', // lowercase w, kis w
			120 => 'x', // lowercase x, kis x
			121 => 'y', // lowercase y, kis y
			122 => 'z', // lowercase z, kis z
			123 => '{', // Left curly brace, Nyitó kapcsos zárójel
			124 => '|', // Vertical bar, Függőleges vonal
			125 => '}', // Right curly brace, Záró kapcsos zárójel
			126 => '~'  // Tilde, hullámvonal
		);
		// Extended
		$extendedCharacters = array(
			161 => '¡', // inverted exclamation mark, fordított felkiáltójel
			162 => '¢', // cent, cent
			163 => '£', // pound, font
			164 => '¤', // currency, valuta?
			165 => '¥', // yen, jen
			166 => '¦', // broken vertical bar, törött függőleges vonal
			167 => '§', // section, paragrafus-jel
			168 => '¨', // spacing diaeresis / umlaut
			169 => '©', // copyright
			170 => 'ª', // feminine ordinal indicator
			171 => '«', // double-left arrow, dupla-balra nyíl
			172 => '¬', // negation, negáció
			174 => '®', // registered trademark
			175 => '¯', // overline, föléhúzás
			176 => '°', // degree, fok
			177 => '±', // plus-or-minus, plusz-minusz
			178 => '²', // squared, négyzet
			179 => '³', // cubed, köb
			180 => '´', // acute accent, jobbradőlő aposztróf?
			181 => 'µ', // micro, mikro
			182 => '¶', // paragraph, bekezdés
			183 => '·', // middle dot, középső pont
			184 => '¸', // spacing cedilla, íves vessző?
			185 => '¹', // superscript 1, egyes kitevő
			186 => 'º', // masculine ordinal indicator
			187 => '»', // double-right arrow, dupla-jobbra nyíl
			188 => '¼', // 1/4
			189 => '½', // 1/2
			190 => '¾', // 3/4
			191 => '¿', // inverted question mark, fordított kérdőjel
			192 => 'À', // capital a, grave accent
			193 => 'Á', // capital a, acute accent
			194 => 'Â', // capital a, circumflex accent
			195 => 'Ã', // capital a, tilde
			196 => 'Ä', // capital a, umlaut mark
			197 => 'Å', // capital a, ring
			198 => 'Æ', // capital ae
			199 => 'Ç', // capital c, cedilla
			200 => 'È', // capital e, grave accent
			201 => 'É', // e, acute accent
			202 => 'Ê', // capital e, circumflex accent
			203 => 'Ë', // capital e, umlaut mark
			204 => 'Ì', // capital i, grave accent
			205 => 'Í', // capital i, acute accent
			206 => 'Î', // capital i, circumflex accent
			207 => 'Ï', // capital i, umlaut mark
			208 => 'Ð', // capital eth, Icelandic
			209 => 'Ñ', // capital n, tilde
			210 => 'Ò', // capital o, grave accent
			211 => 'Ó', // capital o, acute accent
			212 => 'Ô', // capital o, circumflex accent
			213 => 'Õ', // capital o, tilde
			214 => 'Ö', // capital o, umlaut mark
			215 => '×', // multiplication, szorzás
			216 => 'Ø', // capital o, slash
			217 => 'Ù', // capital u, grave accent
			218 => 'Ú', // capital u, acute accent
			219 => 'Û', // capital u, circumflex accent
			220 => 'Ü', // capital u, umlaut mark
			221 => 'Ý', // capital y, acute accent
			222 => 'Þ', // capital THORN, Icelandic
			223 => 'ß', // small sharp s, German
			224 => 'à', // small a, grave accent
			225 => 'á', // small a, acute accent
			226 => 'â', // small a, circumflex accent
			227 => 'ã', // small a, tilde
			228 => 'ä', // small a, umlaut mark
			229 => 'å', // small a, ring
			230 => 'æ', // small ae
			231 => 'ç', // small c, cedilla
			232 => 'è', // small e, grave accent
			233 => 'é', // small e, acute accent
			234 => 'ê', // small e, circumflex accent
			235 => 'ë', // small e, umlaut mark
			236 => 'ì', // small i, grave accent
			237 => 'í', // small i, acute accent
			238 => 'î', // small i, circumflex accent
			239 => 'ï', // small i, umlaut mark
			240 => 'ð', // small eth, Icelandic
			241 => 'ñ', // small n, tilde
			242 => 'ò', // small o, grave accent
			243 => 'ó', // small o, acute accent
			244 => 'ô', // small o, circumflex accent
			245 => 'õ', // small o, tilde
			246 => 'ö', // small o, umlaut mark
			247 => '÷', // division, osztás
			248 => 'ø', // small o, slash
			249 => 'ù', // small u, grave accent
			250 => 'ú', // small u, acute accent
			251 => 'û', // small u, circumflex accent
			252 => 'ü', // small u, umlaut mark
			253 => 'ý', // small y, acute accent
			254 => 'þ', // small thorn, Icelandic
			255 => 'ÿ'  // small y, umlaut mark
		);
		$entities = $standardCharacters + $extendedCharacters;
		$output = '';
		for ($i = 0; isset($text[$i]); $i++) {
			$key = array_search($text[$i],$entities);
			if ($key !== false) {
				// We can convert
				if ($pcre == null or (preg_match($pcre, $text[$i]) === 1 and $excludeMatch == false) or (preg_match($pcre, $text[$i]) === 0 and $excludeMatch == true)) {
					$output .= '&#'.$key.';';
				} else {
					$output .= $text[$i];
				}
			} else {
				$output .= $text[$i];
			}
		}
		return $output;
	}
	
}
