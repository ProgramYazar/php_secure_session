<?php

namespace SecureSessionNS;

require_once('CookieIO.php');
require_once('Crypt.php');
require_once('Logger.php');
require_once('DB.php');
require_once('Remote.php');


// ESessionHelper --> Engin So Secure Session Helper
class SecureSessionHandler implements \SessionHandlerInterface {
	const SECRET_COOKIE_NAME = 'ga_cli'; # looks like google cookie

	private $sessionDB;
	public static $data;
	private $hasError = false;


	public function __construct() {

		Logger::info('Session handler started');
		$this->sessionDB = new DB();

		session_set_cookie_params(
			time() + Cookie::EXPIREHOUR * 3600,
			Cookie::SITEPATH,
			Cookie::DOMAINNAME,
			Cookie::IS_SECURE,
			Cookie::HTTPONLY);
		// function session_set_save_handler ($open, $close, $read, $write, $destroy, $gc, $create_sid, $validate_sid,  $update_timestamp) {}


	}

	public function __destruct() {
		Logger::info("Custom session handler destroyed");
	}

	public function dbg() {
		/**
		 * var_dump(array(
		 * 'is first time' => $this->firstInit ? 'true' : 'false',
		 * 'session_id'    => $this->session_id,
		 * 'cookie data'   => $this->cookie_data,
		 * ));
		 * */
	}


	public function create_sid() {
		return 'S_' . Crypt::createUniqString(32);
	}

	// Close the session
	public function close() {
		Logger::info('Session closed');
		$this->gc(ini_get('session.gc_maxlifetime'));
		return TRUE;
	}

	// Destroy a session
	public function destroy($session_id) {
		Logger::info("Session destroy for id: $session_id");
		return $this->sessionDB->destroySession($session_id);
	}

	// Cleanup old sessions
	public function gc($maxlifetime) {
		Logger::info("Garbage Collection run in $maxlifetime seconds");
		return $this->sessionDB->deleteExpired(Cookie::EXPIREHOUR);
	}

	private function destroySpecial($sessionID) {
		$sc = new Cookie('PHPSESSID');
		$sc->delete();
		$this->sessionDB->destroySession(session_id());
	}

	// open session
	public function open($save_path, $name) {
		if (session_id() == null) { // first time
			$e_cookie = new Cookie(self::SECRET_COOKIE_NAME);
			$e_cookie->write('browser', Remote::userAgent());
			$e_cookie->write('ip', Remote::clientIP());
			$e_cookie->save();
		}
		else { // cookie started

			if (!Cookie::has(self::SECRET_COOKIE_NAME)) { // it must to be created, something wrong
				$this->hasError = true;
				$this->destroySpecial(session_id());
				Logger::warning('Wrong result 1');
			}
			else {
				$e_cookie = new Cookie(self::SECRET_COOKIE_NAME);
				Logger::info("Cookie val: ".$e_cookie->serialize());
				if (!(
					$e_cookie->read('browser') == Remote::userAgent() &&
					$e_cookie->read('ip') == Remote::clientIP())
				) { // check props are correct
					$this->hasError = true;
					$this->destroySpecial(session_id());
					$e_cookie->delete();
					Logger::warning('Wrong result 2');
				}
				else {
					$browser = $e_cookie->read('browser');
					$ip = $e_cookie->read('ip');
					Logger::info("Browser: $browser, ip: $ip");
				}
			}


			return true;
		}

		Logger::info("Session open with name: $name and session_id: " . session_id() . " " . (session_id() == null ? 'true' : 'false'));
		return true;
	}

	// read serialized data or empty string
	public function read($session_id) {
		if ($this->hasError) {
			Logger::info("Session couldnt read! reason: Error"); // perfect explain
			return '';
		}
		Logger::info("Session read with name: $session_id");
		return $this->sessionDB->read($session_id);

	}

	// write session value
	public function write($session_id, $session_data) {
		if (!$this->hasError) {
			Logger::info("Session write with name: $session_id, value: $session_data");
			return $this->sessionDB->write($session_id, $session_data);
		}
		return true;

	}
}
