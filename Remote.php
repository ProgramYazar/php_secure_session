<?php
namespace SecureSessionNS;

class Remote {


	public static function userAgent() {
		return $_SERVER['HTTP_USER_AGENT'];
	}

	// get found ip address 
	public static function clientIP() {
		if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
			return $_SERVER['HTTP_CLIENT_IP'];
		}
		elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			return $_SERVER['HTTP_X_FORWARDED_FOR'];
		}
		elseif (!empty($_SERVER['REMOTE_ADDR'])) {
			return $_SERVER['REMOTE_ADDR'];
		}
		Logger::warning("I couldnt get remote ip");
		throw new Exception("You couldn't be human; maybe a bot or robot", 1);
	}

}