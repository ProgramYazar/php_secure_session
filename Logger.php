<?php

namespace SecureSessionNS;


class Logger {
	const err_format = "[%s] [%s] [%s] %s" . PHP_EOL;

	private static function now() {
		return date("H:M:S Y-M-D");
	}

	public static function error($message) {
		error_log(sprintf(self::err_format, self::now(), __FILE__, 'error', $message));
		die(-1);
	}

	public static function warning($message) {
		error_log(sprintf(self::err_format, self::now(), __FILE__, 'warning', $message));
	}

	public static function debug($message) {
		error_log(sprintf(self::err_format, self::now(), __FILE__, 'debug', $message));
	}

	public static function info($message) {
		error_log(sprintf(self::err_format, self::now(), __FILE__, 'info', $message));
	}


}