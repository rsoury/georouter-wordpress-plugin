<?php

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Provides static methods as helpers.
 */
class Georouter_Util
{
	public static function debug_log($log)
	{
		if (is_array($log) || is_object($log)) {
			error_log(print_r($log, true));
		} else {
			error_log($log);
		}
	}
}
